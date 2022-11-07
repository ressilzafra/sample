<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/** 
 * Please take note that this is just a part of my script, so it will not work. For reviewing purpose only.
 * The script below is the one that renders Just Listed, Just Reduced, Just Sold and Open House Postcards of F. C. Tucker Company. 
 */

class Postcard_vue extends CI_Controller {

    private $alignment = [
        'center'    => Imagick::ALIGN_CENTER,
        'left'      => Imagick::ALIGN_LEFT,
        'right'     => Imagick::ALIGN_RIGHT,
    ];
    
    private $font_path = FCPATH . 'assets/fonts/';
    private $postv = null;

    function __construct() {
        parent::__construct();

        set_time_limit(0);

        require(APPPATH . 'libraries/fpdf/fpdf.php');

        # folders
        $this->template = FCPATH . 'assets/materials/postcardvue/';
        $this->tmpfolder = FCPATH . 'assets/temp/postcardvue/';
        $this->webfolder = 'assets/temp/postcardvue/';

        $this->filename = strtolower($this->session->userdata('sa_agent_id'));

        $this->last = $this->uri->segment($this->uri->total_segments());
        $this->slug = $this->uri->uri_string();
        $this->pc_table = 'saved_postcards';

        // remove this once published
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
    }

    public function index() {
        $this->native_session->auth_both();

        $vars = generic_tpl($this->slug);

        $vars['sidebar'] = false;
        $this->system_hooks->add_js(base_url('assets/modern/js/manifest.js'));
        $this->system_hooks->add_js(base_url('assets/modern/js/vendor.js'));
        $this->system_hooks->add_js(base_url('assets/modern/js/app.js'));

        $this->system_hooks->render($vars, 'marketing-materials/postcards/thepostcard');
    }
    
    public function ajaxes($curl = false) {
        if ($curl) {
            $pro    = $_POST['pro'];
        } else {            
            $data   = json_decode(file_get_contents("php://input"), true);
            $pro    = $data['pro'];
            
            $this->postv = $data;
        }

        switch ($pro) {
            case 'savedpc': 
                $this->__check_exist(1);
                break;

            case 'delsaved':
                $udata = [
                    'status' => 'Deleted',
                    'date_updated' => date('Y-m-d H:i:s')
                ];

                $this->db->update($this->pc_table, $udata, array('id' => $data['id']));
                break;

            case 'checkexist':
                $this->__check_exist();
                break;

            case 'save':
            case 'done':
                $this->__save();
                break;

            case 'preview':
                $this->__preview();
                break;

            case 'generate':
                if ($data['done'] == 'yes') {
                    $udata = [
                        'status' => 'Done',
                        'date_updated' => date('Y-m-d H:i:s')
                    ];

                    $this->db->update($this->pc_table, $udata, array('id' => $data['recentpc']['id']));
                }

                $this->__generate($data['pcup']);
                break;

            case '_alert' :
                $this->filename = strtolower($_POST['sa_agent_id']);
                $this->postv    = $_POST;

                $this->__preview(true);
                $this->__generate();
                break;

            case 'checkifofc' :
                $this->__check_if_office($data);
                break;
        }
    }

    private function __check_exist($all = false) {
        $rec = [];
        $cur = [];

        if ($all) {
            # get recently saved pc
            $this->db->where('sa_agent_id', $this->session->userdata('sa_agent_id'));
            $this->db->where('status', 'Ongoing');
            $this->db->order_by('date_entry', 'desc');
            $recent = $this->db->get($this->pc_table, 1, 0);

            if ($recent->num_rows() > 0) {
                $rec = $recent->row(1);
            }

            $this->db->where('status <>', 'Deleted');
            $this->db->where('sa_agent_id', $this->session->userdata('sa_agent_id'));
        } else {
            $savefile = $this->_slugify($this->postv['savefname']);
            $this->db->where('datafile', $savefile);
        }

        $curr = $this->db->get($this->pc_table);

        if ($curr->num_rows() > 0) {
            $cur = $curr->result();
        }

        $rs = [
            'recent'  => $rec,
            'current' => $cur,
        ];

        echo json_encode((object) $rs);
    }

    private function __save() {
        $post     = $this->postv;
        $savefile = $this->_slugify($post['savefname']);

        $json_dir = FCPATH . "assets/temp/postcardvue/" . $savefile . '.json';
        $myfile   = fopen($json_dir, "w+") or die("Unable to open file!");
        $post['pro'] = 'preview';

        fwrite($myfile, json_encode($post));
        fclose($myfile);
        
        $dbdata = [
            'sa_agent_id'   => $this->session->userdata('sa_agent_id'),
            'listing_id'    => $post['listingid'],
            'name'          => $post['savefname'],
            'datafile'      => $savefile,
            'postvars'      => json_encode($post),
            'date_entry'    => date('Y-m-d H:i:s'),
            'status'        => $post['save_status']
        ];

        $this->db->where('datafile', $savefile);
        $curr  = $this->db->get($this->pc_table);

        if ($curr->num_rows() > 0) {
            unset($dbdata['date_entry']);
            $dbdata['date_updated'] = date('Y-m-d H:i:s');
            $this->db->update($this->pc_table, $dbdata, array('datafile' => $savefile));

            if ($this->db->affected_rows() > 0) {
                $code = 200;
                $msg  = 'Ok';
            } else {
                $code = 400;
                $msg  = 'Error';
            }
        } else {
            $this->db->insert($this->pc_table, $dbdata);
            
            if ($this->db->affected_rows() > 0) {
                $code = 200;
                $msg  = 'Ok';
            } else {
                $code = 400;
                $msg  = 'Error';
            }
        }

        $rs = [
            'code' => $code,
            'msg'  => $msg
        ];

        echo json_encode($rs);
    }

    private function __preview($curl = false) {
        extract($this->postv);

        if ($pro == '_alert') {
            $this->__save_photo_from_url($agent_photo, $this->tmpfolder . $this->filename . '-photo.jpg');
        } else {
            # download of agent photos
            $this->__save_photo_from_url($_SESSION['agent_photo'], $this->tmpfolder . $this->filename . '-photo.jpg');

            if ($_SESSION['is_team_member']) {
                if ($_SESSION['team_photo'] != '') 
                    $this->__save_photo_from_url($_SESSION['team_photo'], $this->tmpfolder . $this->filename . '-team.jpg');

                if ($_SESSION['leader_photo'] != '')
                    $this->__save_photo_from_url($_SESSION['leader_photo'], $this->tmpfolder . $this->filename . '-leader.jpg');
            }
            # download of agent photos
        }

        # FRONT :: the magic starts
        if (isset($pcup) && !empty($pcup)) {
            $this->template = $this->template . $pcup  . '/';
            $agentdp_xpos = 600;
        } else {
            $agentdp_xpos = 500;
        }
        
        // overwrite DP x position
        if (isset($tpl['dp_xpos'])) {
            $agentdp_xpos = $tpl['dp_xpos'];
        }
        
        $ftpl       = $this->template . $tpl['name'] . '.jpg';
        $this->im   = new Imagick($ftpl);
        $this->draw = new ImagickDraw();
    
        for ($i = 0; $i < count($tpl['img_prop']['image_w']); $i++) {
            $raw = $this->tmpfolder . $this->filename . '-raw' . $i . '.jpg';

            if (file_exists($raw)) {
                unlink($raw);
            }
            
            $result = $this->__save_photo_from_url($checked_img[$i], $raw);

            $main   = new Imagick($raw);

            $main->resizeImage($tpl['img_prop']['image_w'][$i], 0, Imagick::RESOLUTION_UNDEFINED, 1);
            $main->cropThumbnailImage($tpl['img_prop']['image_w'][$i], $tpl['img_prop']['image_h'][$i]);

            $this->im->compositeImage($main, $main->getImageCompose(), $tpl['img_prop']['xpos'][$i], $tpl['img_prop']['ypos'][$i]);
            $main->resizeImage($tpl['img_prop']['image_w'][$i], 0, Imagick::RESOLUTION_UNDEFINED, 1);
            $main->cropThumbnailImage($tpl['img_prop']['image_w'][$i], $tpl['img_prop']['image_h'][$i]);
            
            $this->im->compositeImage($main, $main->getImageCompose(), $tpl['img_prop']['xpos'][$i], $tpl['img_prop']['ypos'][$i]);
        }
        
        // use cropped main photo
        if (isset($cropped_photo) && !empty($cropped_photo)) {
            $blob = base64_decode(str_replace('data:image/jpeg;base64,', '', $cropped_photo));

            $main = new Imagick();
            $main->readImageBlob($blob);
            $main->resizeImage($tpl['img_prop']['image_w'][0], 0, Imagick::RESOLUTION_UNDEFINED, 1);

            $this->im->compositeImage($main, $main->getImageCompose(), $tpl['img_prop']['xpos'][0], $tpl['img_prop']['ypos'][0]);
        }

        if ($tpl['badge'] != '') {
            $badge = new Imagick($this->template . $tpl['badge'] . '.png');
            $this->im->compositeImage($badge, $badge->getImageCompose(), 0, 0);
        }

        # add text content
        if (isset($tpl['text_props'])) {
            if ($tpl['name'] == 'tucker-color') {
                if ($price_to_sell != '') {
                    $addr = $address_prop . ', ' . $city . ' • ' . $price_to_sell . ' • ' . 'BLC#:' . $listingid;
                } else {
                    $addr = $address_prop . ', ' . $city . ' • ' . 'BLC#:' . $listingid;                    
                }
            } else if ($tpl['name'] == 'moxi') {
                $addr = $address_prop . "\n" . $city;

                // # added in moxi
                $this->__add_text_content('beds', $beds_only);
                $this->__add_text_content('baths', $baths_only);
                $this->__add_text_content('price', '$' . $price_only);
            } else {
                $addr = $address_prop . ', ' . $city;
            }

            $this->__add_text_content('title', $pc_title);
            $this->__add_text_content('address', $addr);

            if ($open_house_datetime != '') {
                $this->__add_text_content('open_house_dt', $open_house_datetime);
            }

            if ($this->session->userdata('site') == 'statewide') {
                $this->__add_text_content('indie_line_shadow', INDIE_LINE);
                $this->__add_text_content('indie_line', INDIE_LINE);
            }
        }

        $front = $this->__output('front');

        # BACK :: the magic starts
        $btpl     = $this->template . $tpl['back'] . '.jpg';
        $this->im = new Imagick($btpl);

        # add text content
        if (isset($tpl['text_props'])) {
            if ($tpl['name'] == 'tucker-color') {
                $addr = $address_prop . ', ' . $city . ' • ' . $price_to_sell . ' • ' . 'BLC#:' . $listingid;
            } else {
                $addr = $address_prop . ', ' . $city;
            }

            $this->__add_text_content('back_title', $pc_title_back);
            $this->__add_text_content('back_prop', '');
            $this->__add_text_content('back_msg', $this->__wordwrap('back_msg', str_replace('<br />', '[br]', nl2br($pc_message))));
            $this->__add_text_content('company_name', $company_name);
            $this->__add_text_content('office_addr', $office_address . "\n" . $office_city . ", " . $office_state . " " . $office_zip);
            $this->__add_text_content('agent_info', $this->__build_multiline('agent_info'));

            if ($tpl['name'] == 'tucker-color') {
                $this->__add_text_content('agent_fname', $firstname);
                $this->__add_text_content('agent_lname', $lastname);
            } else {
                $this->__add_text_content('agent_name', $firstname . ' ' . $lastname);
            }
        }

        // profile photo
        if ($inc_photo != 'nophoto') {
            $dp  = 'photo';
            $dp2 = '';

            switch ($inc_photo) {
                case 'profile_lead': 
                    $dp2 = 'leader'; 
                    $with_name = $this->session->userdata('leader_first_name') . ' ' . $this->session->userdata('leader_last_name');
                    break;

                case 'profile_team': 
                    $dp2 = 'team'; 
                    $with_name = $team_name;
                    break;
                    
                case 'team': $dp = 'team'; break;
            }
            
            $photo   = new Imagick($this->tmpfolder . $this->filename . '-' . $dp . '.jpg');
            $photo->resizeImage(300, 0, Imagick::RESOLUTION_UNDEFINED, 1);
          
            if (isset($tpl['agent_dp'])) {
                $pro_w = $tpl['agent_dp']['width'];
                $pro_h = $tpl['agent_dp']['height'];
                $pro_x = $tpl['agent_dp']['xpos'];
                $pro_y = $tpl['agent_dp']['ypos'];
            } else {
                $pro_w = 200;
                $pro_h = 250;
                $pro_x = 70;
                $pro_y = 500;
            }
    
            $photo->cropThumbnailImage($pro_w, $pro_h);
    
            if  ($photo->getimagecolorspace() == Imagick::COLORSPACE_CMYK) {
                $this->im->transformImageColorspace(Imagick::COLORSPACE_CMYK);
            }
    
            $this->im->compositeImage($photo, $photo->getImageCompose(), $pro_x, $pro_y);

            if ($dp2 != '') {
                $photo   = new Imagick($this->tmpfolder . $this->filename . '-' . $dp2 . '.jpg');
                $photo->resizeImage(300, 0, Imagick::RESOLUTION_UNDEFINED, 1);
                $photo->cropThumbnailImage(200, 250);

                # removes inverted cmyk image
                if ($photo->getImageColorspace() == \Imagick::COLORSPACE_CMYK)
                    $photo->transformimagecolorspace(\Imagick::COLORSPACE_SRGB);

                $this->im->compositeImage($photo, $photo->getImageCompose(), 1380, $agentdp_xpos);

                $this->__add_text_content('leader_name', $with_name);
            }
        }

        $back = $this->__output('back');

        $rs = [
            'front' => $front,
            'back'  => $back,
        ];
        
        echo json_encode($rs);
    }

    private function __build_multiline($type) {
        $props = $this->postv['tpl']['text_props'];

        foreach ($props[$type]['indexes'] as $v) {
            if (isset($this->postv[$v]) && $this->postv[$v] != '') {
                $multi[] = (isset($props[$type]['bulleted']) ? $props[$type]['bulleted'] : '') . $this->postv[$v];
            }
        }

        return $multi;
    }

    private function __wordwrap($type, $text) {        
        $exp = explode('[br]', $text);

        foreach ($exp as $ek => $ev) {
            $props   = $this->postv['tpl']['text_props'];
            $max_w   = $props[$type]['width'];
            
            $words   = explode(" ", $ev);
            $lines[] = [];
            $i       = 0;
            $line_h  = 0;
            
            while ($i < count($words)) {
                $currentLine = $words[$i];

                if ($i + 1 >= count($words)) {
                    $lines[] = $currentLine;
                    break;
                }

                $metrics = $this->im->queryFontMetrics($this->draw, $currentLine . ' ' . $words[$i + 1]);

                while ($metrics['textWidth'] <= $max_w) {
                    $currentLine .= ' ' . $words[++$i];            

                    if ($i + 1 >= count($words)) {
                        break;
                    }

                    $metrics = $this->im->queryFontMetrics($this->draw, $currentLine . ' ' . $words[$i + 1]);
                }

                $lines[] = trim(preg_replace('/\s\s+/', ' ', $currentLine));
                $i++;
            }
        }

        $rs = array_values(array_filter($lines));

        return $rs;
    }

    private function __add_text_content($type, $text) {
        $seltpl = $this->postv['tpl'];
        $props  = $this->postv['tpl']['text_props'];

        if (isset($props[$type]['alignment'])) {
            $align = $props[$type]['alignment'];
        } else {
            $align = $props['alignment'];
        }

        $this->draw->setTextAlignment($this->alignment[$align]);

        # font setting
        if (isset($props[$type]['font'])) {
            $font = $this->font_path . $props[$type]['font'];
        } else {
            $font = $this->font_path . $props['font'];
        }

        $this->draw->setFont($font . '.ttf');

        # font size 
        if (isset($props[$type]['size'])) {
            $fontsize = $props[$type]['size'];
        } else {
            $fontsize = $props['size'];
        }

        $this->draw->setFontSize($fontsize);

        # set color
        if (isset($props[$type]['color'])) {
            $color = $props[$type]['color'];
        } else {
            $color = $props['color'];
        }

        # set x position
        if (isset($props[$type]['xpos'])) {
            $x = $props[$type]['xpos'];
        } else {
            $x = $props['xpos'];
        }

        # set y position
        $ypos = $props[$type]['ypos'];

        # creates resize for longer text
        if (isset($props[$type]['resize'])) {
            $resize = $props[$type]['resize'];

            if (strlen($text) > $resize['min']) {
                $this->draw->setFontSize($resize['size']);
                $ypos = $resize['ypos'];
            }
        }

        if (isset($props[$type]['linespace'])) {
            $linespace = $props[$type]['linespace'];
        } else {
            $linespace = 0;
        }

        $this->draw->setFillColor($color);

        if (is_array($text)) {
            for ($i = 0; $i < count($text); $i++) {
                if (isset($props[$type]['styled_words'])) {
                    $words = explode(" ", $text[$i]);

                    $x = $props['xpos'];

                    foreach ($words as $w) {
                        if (in_array(strtolower($w), $props[$type]['styled_words']['words'])) {
                            if (isset($props[$type]['styled_words']['font']))
                                $this->draw->setFont($this->font_path . $props[$type]['styled_words']['font'] . '.ttf');

                            if (isset($props[$type]['styled_words']['color']))
                                $this->draw->setFillColor($props[$type]['styled_words']['color']);

                            if (isset($props[$type]['styled_words']['transform'])) {
                                switch ($props[$type]['styled_words']['transform']) {
                                    case 'upper' : $w = strtoupper($w); break;
                                    case 'lower' : $w = strtolower($w); break;
                                    default : $w;
                                }
                            }
                        } else {
                            $this->draw->setFont($font . '.ttf');
                            $this->draw->setFillColor($color);
                        }

                        $metrics = $this->im->queryFontMetrics($this->draw, $w, FALSE);
                        $width = $metrics['textWidth'];
                        $this->im->annotateImage($this->draw, $x, $ypos + $i * $linespace, 0, $w);
                        $x += $width + 8;
                    }
                } else {                    
                    $this->im->annotateImage($this->draw, $x, $ypos + $i * $linespace, 0, $text[$i]);
                }
            }
        } else {
            $this->im->annotateImage($this->draw, $x, $ypos, 0, $text);
        }
    }

    private function __output($side) {
        extract($this->postv);

        $fyl = $this->filename . '-' . $side . '.jpg';

        $this->im->setImageFormat('jpeg');
        $this->im->writeImage($this->tmpfolder . $fyl);

        $outi = $this->webfolder . $fyl;

        $data = fopen($outi, 'rb');
        $size = filesize($outi);
        $contents = fread($data, $size);
        fclose($data);

        $encoded = base64_encode($contents);

        return $encoded;
    }

    private function __generate($pcup = '') {
        if (!empty($pcup)) {
            $outputw = 2550;
            $outputh = 3300;
            $pagew   = 216;
            $pageh   = 279;
            $orientation = 'P';
        } else {
            $outputw = 3300;
            $outputh = 2550;
            $pagew   = 279;
            $pageh   = 216;
            $orientation = 'L';
        }

        $f   = $this->tmpfolder . $this->filename . '-front.jpg';
        $b   = $this->tmpfolder . $this->filename . '-back.jpg';
        $ff  = $this->tmpfolder . $this->filename . '-final-front.jpg';
        $fb  = $this->tmpfolder . $this->filename . '-final-back.jpg';

        # build the front
        $im = new Imagick();
        $im->newImage($outputw, $outputh, new ImagickPixel('white'));

        if (!empty($pcup)) {
            $fr = new Imagick($f);
            $im->compositeImage($fr, $fr->getImageCompose(), 0, 0);
            $im->compositeImage($fr, $fr->getImageCompose(), 0, 1651);
    
            $im->setImageFormat('jpeg');
            $im->writeImage($ff);
    
            $br = new Imagick($b);
            $im->compositeImage($br, $br->getImageCompose(), 0, 0);;
            $im->compositeImage($br, $br->getImageCompose(), 0, 1651);
        } else {
            $fr = new Imagick($f);
            $im->compositeImage($fr, $fr->getImageCompose(), 0, 0);
            $im->compositeImage($fr, $fr->getImageCompose(), 1651, 0);
            $im->compositeImage($fr, $fr->getImageCompose(), 1651, 1275);
            $im->compositeImage($fr, $fr->getImageCompose(), 0, 1275);
    
            $im->setImageFormat('jpeg');
            $im->writeImage($ff);
    
            $br = new Imagick($b);
            $im->compositeImage($br, $br->getImageCompose(), 0, 0);
            $im->compositeImage($br, $br->getImageCompose(), 1651, 0);
            $im->compositeImage($br, $br->getImageCompose(), 1651, 1275);
            $im->compositeImage($br, $br->getImageCompose(), 0, 1275);
        }

        $im->setImageFormat('jpeg');
        $im->writeImage($fb);

        $pdf = new FPDF();
        $pageSize = [$pagew, $pageh];
        $imageAttachmentW = $pageSize[0];
        $imageAttachmentH = $pageSize[1];

        $pdf->AddPage($orientation, $pageSize);

        $pdf->Image($ff, 0, 0, $imageAttachmentW, $imageAttachmentH);

        $pdf->AddPage($orientation, $pageSize);
        $pdf->Image($fb, 0, 0, $imageAttachmentW, $imageAttachmentH);

        if (isset($_POST['team_brand'])) {
            $filename = 'team-' . $this->filename . '.pdf';            
        } else {
            $filename = $this->filename . '.pdf';
        }

        $pdf->Output(FCPATH . 'assets/temp/postcardvue/' . $filename, 'F');

        $result = [
            "success" => TRUE, 
            "file"    => $filename
        ];

        // header("Content-Type: javascript/json");
        echo json_encode($result);
    }

    private function __save_photo_from_url($url, $imgdir) {
        $im  = $imgdir;
        $url = str_replace(' ', '%20', $url);

        $ch  = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 0);
        $rs  = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 404) {
            $msg = false;
        } else {
            $fp = fopen($im, 'w');
            fwrite($fp, $rs);
            fclose($fp);

            $msg = true;
        }

        curl_close ($ch);

        return $msg;
    }

    private function _slugify($text) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function download($file) {
        $pdf  = FCPATH . 'assets/temp/postcardvue/' . $file;
        $data = file_get_contents($pdf);

        $dlname = 'Postcard-' . $this->session->userdata('agent_dpn') . '.pdf';

        header("Cache-Control: private");
        header("Content-type: application/octet-stream");
        header("Content-disposition: inline;filename=" . $dlname);

        echo $data;
    }

    private function __check_if_office($data) {
        $this->db->where('office_login', $data['office_login']);
        $sql = $this->db->get('offices');

        if ($sql->num_rows() > 0) {
            echo 'Yes';
        } else {
            echo 'No';
        }
    }
}