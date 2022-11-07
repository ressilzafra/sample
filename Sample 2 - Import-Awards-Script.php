<?php

################################# IMPORTING #################################

/** 
 * Please take note that this is just a part of my script, so it will not work. For reviewing purpose only. 
 * All contents and nested content of this page https://awards.talktotucker.com/awards
 * was built by this script
 */

class Awards {

	public function import_awards($data) {
		foreach ($data as $k => $v) { 
			if ($v['full_name'] != '') {
				$slug = _slugify($v['full_name']);				
				$date = date('Y-m-d H:i:s');
				$data = [
					'slug'          => $slug,
					'full_name'     => $v['full_name'],
					'sort_name'     => $v['sort_name'],
					'tuckerid'      => $v['tuckerid'],
					'email'         => $v['email'],
					'company'       => $v['company'],
					'office'        => $this->get_office_id($v['office']),
					'video_url'     => $v['video_url'],
					'agent_url'     => $v['agent_url'],
					'team'          => $v['display_team'],
					'team_status'   => $v['team_status'],
					'closed_prod'   => $v['closed_prod'],
					'years_in_pc'   => $v['years_in_pc'],
					'congrats_site' => (empty($v['congrats_site']) ? 'y' : $v['congrats_site']),
				];

				if ($v['display_team'] == 'y') {
					$data['team_photo']  = $v['photo'];
				} else {
					$data['agent_photo'] = $v['photo'];
				}

				$this->db->where('slug', $slug);

				$exist = $this->db->get(TABLE_AWARDEES);

				# importing awardees
				if ($exist->num_rows() > 0) {
					$data['last_modified'] = $date;

					$this->db->update(TABLE_AWARDEES, $data, ['slug' => $slug]);

					$a          = $exist->row(0);
					$awardee_id = $a->id;
				} else {
					$data['date_entry'] = $date;
					
					$this->db->insert(TABLE_AWARDEES, $data);

					$awardee_id = $this->db->insert_id();
				}

				# importing award
				$sub_child_awards = ['Rising Star', 'Top 10', 'Marketing Excellence'];
				$no_child_awards  = ['$100 Million Club', 'Commercial Marketing Excellence', 'Bud Tucker Volunteer of the Year', 'Fred C. Tucker Sr. Award'];

				if ($awardee_id != '') {
					$year = ($v['year'] != '') ? $v['year'] : date("Y");

					# get award id
					$awards = explode('|', $v['awards_joined']);
					
					foreach ($awards as $ak => $av) {
						$award = explode('-', $av);

						$ptitle  = addslashes(trim($award[0]));
						$stitle  = $v['company'];
						$ttitle  = isset($award[1]) ? addslashes(trim($award[1])) : '';
						$tblnavs = TABLE_NAVS;

						if (in_array(trim($award[0]), $sub_child_awards)) {							
							$atitle = $this->db->query("SELECT id, parent, (SELECT id FROM $tblnavs WHERE title = '$ptitle') AS pid, 
								(SELECT id FROM $tblnavs WHERE parent = pid AND title = '$stitle') AS subp 
								FROM $tblnavs 
								WHERE title LIKE '%$ttitle%'
								HAVING parent = subp");
						} else if (in_array(trim($award[0]), $no_child_awards)) {
							$this->db->where('title', trim($award[0]));
							$atitle = $this->db->get(TABLE_NAVS);
						} else {
							if (isset($award[1])) {
								$atitle = $this->db->query("SELECT id, parent, 
									(SELECT id FROM $tblnavs WHERE title = '$ptitle') AS pid 
									FROM $tblnavs 
									WHERE title LIKE '%$ttitle%' 
									HAVING parent = pid");
							} else {
								$cod   = 1;
					            $msg   = 'Invalid award/s format for ' . $v['full_name'];
					            $icon  = 'add_alert';
					            $alert = 'danger';
								$rs = ['cod' => $cod, 'msg' => $msg, 'icon' => $icon, 'alert' => $alert];

								die(json_encode($rs));
							}
						}

						if ($atitle->num_rows() > 0) {
							$gtitle = $atitle->row(0);

							$adata = [
								'awardee_id'    => $awardee_id,
								'award_id'      => $gtitle->id,
								'year'          => $year,
								'date_entry'    => $date,
							];

							$this->db->where('awardee_id', $awardee_id);
							$this->db->where('award_id', $gtitle->id);
							$this->db->where('year', $year);
							$aexist = $this->db->get(TABLE_AWARDS);

							if ($aexist->num_rows() == 0) {
								$this->db->insert(TABLE_AWARDS, $adata);
							}
						}
					}
				}
			}
		}
	}

}