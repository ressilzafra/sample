<!-- 
  Please take note that this is just a part of my script, so it will not work. For reviewing purpose only.
  The script below is the one that renders Just Listed, Just Reduced, Just Sold and Open House Postcards of F. C. Tucker Company. 
 -->
<template>
  <div>
    <div class="loader-wrapper"
         v-if="please_wait == true">
        <div class="loader"></div>
    </div>

    <b-modal ref="vuemodal"
             static
             no-close-on-backdrop
             hide-footer
             :size="modalsize"
             :title="modaltitle"
             style="width: 1024px;">
        <pcmodal :prop="prop" :tpl="seltpln" :file="listingid" :chkid_img="chkid_img" :agentid="agentId" :modalmode="modalmode" :recentpc="modalpc" @submit="submit_modal" @close="modalmode = ''"></pcmodal>
    </b-modal>

    <b-modal ref="officemodal"
             static
             hide-footer
             no-close-on-backdrop
             size="sm"
             title="Office Login Detected">
        <div>
          <p>This tool will not work while logged in as the office. Please select an agent to emulate by clicking the menu located in the top right or click on the image below to choose an agent.</p>
          <a :href="base_url + 'office'">
            <center><img :src="base_url + 'assets/images/emulate-agent.png'" class="img-thumbnail" alt=""></center>
          </a>
        </div>
        <div align="right">
          <b-button class="btn btn-primary mt-5" @click="hide_office_modal">Okay</b-button>
        </div>
    </b-modal>

    <h3>Enter Listing Information</h3>
    <div class="row">
      <div class="col-xs-12 col-sm-2">
        <label for="">Template</label>
        <select class="form-control" v-model="pcup">
          <option value="2up">2 up</option>
          <option value="">4 up</option>
        </select>
      </div>
      <div class="col-xs-12 col-sm-2">
        <label for="">Type</label>
        <select class="form-control" v-model="pc_type">
          <option value="listed">Just Listed</option>
          <option value="reduced">Just Reduced</option>
          <option value="sold">Just Sold</option>
          <option value="open_house">Open House</option>
          <option value="pending">Pending</option>
        </select>
      </div>
      <div class="col-xs-12 col-sm-2">
        <label for="">&nbsp;</label>
        <input type="text" v-model="listingid" class="form-control" placeholder="Enter Listing ID" required>
      </div>
      <div class="col-xs-12 col-sm-6">
        <label for="">&nbsp;</label><br>
        <input type="button" class="btn btn-primary mb-1 btn-xs-block" :value="validate_label" @click="validate_listingid" v-if="!validated">
        <input type="button" class="btn btn-danger mb-1 btn-xs-block" @click="start_over" value="Clear and Start Over" v-else>
        <input type="button" class="btn btn-primary mb-1 btn-xs-block" value="View Saved Postcards" @click="view_savedpc" v-if="savedpc.length > 0">
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12" v-html="infomsg"></div>
    </div>

    <hr class="thesep">

    <div class="row" v-if="validated">
      <div class="col-sm-7">
        <b-tabs content-class="mt-3">
          <b-tab title="Choose Design" active>
            <div class="row">
              <div class="col-sm-3 tpls" v-for="(tpl, i) in templates">
                <img :src="base_url + 'assets/materials/postcardvue/' + tpl.name + '-thumb.jpg'" 
                     :alt="tpl.name"
                     :class="seltpln.name === tpl.name ? 'active' : ''"
                     @click="select_template(i)" v-if="tpl.show_only_in == ''">
                    
                <img :src="base_url + 'assets/materials/postcardvue/' + tpl.name + '-thumb.jpg'" 
                     :alt="tpl.name"
                     :class="seltpln.name === tpl.name ? 'active' : ''"
                     @click="select_template(i)" v-else-if="tpl.show_only_in != '' && tpl.show_only_in == pc_type">
              </div>
            </div>
            <div><br>
              <input type="button" @click="modalmode = 'choose'" class="btn btn-warning" value="SELECT A POSTCARD PHOTO">
            </div>
          </b-tab>
          <b-tab title="Postcard Information">
            <div class="row">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Front Title</label>
                <small class="title-ctr">{{ title_maxlen - pc_title.length }} / {{ title_maxlen }}</small>
                <input type="text" class="form-control" v-model="pc_title" v-on:input="content_changed" :maxlength="title_maxlen">
              </div>
              <div class="col-12 col-sm-6 col-lg-3 mb-1">
                <label for="">Font Style</label>
                <select-font :default-font="seltpln.text_props.title.font" @fontChanged="setFont($event, 'title')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-2 mb-1">
                <label for="">Font Size</label>
                <select id="titleopt" class="form-control" v-model="seltpln.text_props.title.size" v-on:input="content_changed" v-if="seltpln.text_props.title.range[2] == 2">
                  <option :value="(i + seltpln.text_props.title.range[0]) * 5" 
                          v-for="i in (seltpln.text_props.title.range[1] - seltpln.text_props.title.range[0] + 1)" 
                          v-if="i % 2 == 0">
                    {{ i + seltpln.text_props.title.range[0] }}
                  </option>
                </select>
                <select id="titleopt" class="form-control" v-model="seltpln.text_props.title.size" v-on:input="content_changed" v-if="seltpln.text_props.title.range[2] == 1">
                  <option :value="(i + seltpln.text_props.title.range[0]) * 5" 
                          v-for="i in (seltpln.text_props.title.range[1] - seltpln.text_props.title.range[0] + 1)">
                    {{ i + seltpln.text_props.title.range[0] }}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-1 mb-1" >
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.title.color" v-on:input="content_changed">
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Back Title</label>
                <small class="title-ctr">{{ title_maxlen - pc_title_back.length }} / {{ title_maxlen }}</small>
                <input type="text" class="form-control" v-model="pc_title_back" v-on:input="content_changed" :maxlength="title_maxlen">
              </div>
              <div class="col-12 col-sm-6 col-lg-3 mb-1">
                <label for="">Font Style</label>
                <select-font :default-font="seltpln.text_props.back_title.font" @fontChanged="setFont($event, 'back_title')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-2 mb-1">
                <label for="">Font Size</label>
                <select class="form-control" v-model="seltpln.text_props.back_title.size" v-on:input="content_changed" v-if="seltpln.text_props.back_title.range[2] == 2">
                  <option :value="(i + seltpln.text_props.back_title.range[0]) * 5" 
                          v-for="i in (seltpln.text_props.back_title.range[1] - seltpln.text_props.back_title.range[0] + 1)" 
                          v-if="i % 2 == 0">
                    {{ i + seltpln.text_props.back_title.range[0] }}
                  </option>
                </select>
                <select class="form-control" v-model="seltpln.text_props.back_title.size" v-on:input="content_changed" v-else>
                  <option :value="(i + seltpln.text_props.back_title.range[0])" 
                          v-for="i in (seltpln.text_props.back_title.range[1] - seltpln.text_props.back_title.range[0] + 1)" 
                          v-if="i % 2 == 0">
                    {{ i + seltpln.text_props.back_title.range[0] }}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-1 mb-1" >
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.back_title.color" v-on:input="content_changed">
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Address</label>
                <input type="text" class="form-control" v-model="address_only" v-on:input="content_changed" v-if="seltpln.name == 'moxi'">
                <input type="text" class="form-control" v-model="address_prop" v-on:input="content_changed" v-else>
                </br>
                <input type="text" class="form-control" v-model="address_city" v-on:input="content_changed" v-if="seltpln.name == 'moxi'">
              </div>
              <div class="col-12 col-sm-6 col-lg-3 mb-1">
                <label for="">Font Style</label>
                <select-font :default-font="seltpln.text_props.address.font" @fontChanged="setFont($event, 'address')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-2 mb-1">
                <label for="">Font Size</label>
                <select class="form-control" v-model="seltpln.text_props.address.size" v-on:input="content_changed">
                  <option :value="i + seltpln.text_props.address.plus" v-for="i in 32" v-if="i % 2 == 0">
                    {{ i + 8 }}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-1 mb-1" >
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.address.color" v-on:input="content_changed">
              </div>
            </div>

            <div class="row" v-if="pc_type == 'sold' && seltpln.name != 'elegant'">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Price By-Line:</label>
                <input type="text" class="form-control" v-model="open_house_datetime" v-on:input="content_changed">
              </div>
              <div class="col-12 col-sm-6 col-lg-3 mb-">
                <label for="">Font Style</label>
                <select-font :default-font="seltpln.text_props.open_house_dt.font" @fontChanged="setFont($event, 'open_house_dt')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-2 mb-1">
                <label for="">Font Size</label>
                <select class="form-control" v-model="seltpln.text_props.open_house_dt.size" v-on:input="content_changed">
                  <option :value="i + 48" v-for="i in 24" v-if="i % 2 == 0">
                    {{ i + 8  }}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-1 mb-1" >
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.open_house_dt.color" v-on:input="content_changed">
              </div>
            </div>
            <div class="row" v-if="pc_type == 'open_house' && seltpln.name != 'elegant'">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                  <label for="">Open House</label>
                  <input type="text" class="form-control" v-model="open_house_datetime" v-on:input="content_changed">
              </div>
              <div class="col-12 col-sm-6 col-lg-3 mb-1">
                <label for="">Font Style</label>
                <select-font :default-font="seltpln.text_props.open_house_dt.font" @fontChanged="setFont($event, 'open_house_dt')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-2 mb-1">
                <label for="">Font Size</label>
                <select class="form-control" v-model="seltpln.text_props.open_house_dt.size" v-on:input="content_changed">
                  <option :value="i + seltpln.text_props.open_house_dt.plus" v-for="i in 24" v-if="i % 2 == 0">
                    {{ i + 8}}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-1 mb-1">
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.open_house_dt.color" v-on:input="content_changed">
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-12">
                <label for="">Message</label>
                <textarea class="form-control" rows="5" v-model="pc_message" :maxlength="msg_limit"></textarea>
                <small><span>{{ msg_ctr }} of {{ msg_limit }} chars</span></small>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Message Font Style</label>
                <select-font :default-font="seltpln.text_props.back_msg.font" @fontChanged="setFont($event, 'back_msg')" ></select-font>
              </div>
              <div class="col-12 col-sm-6 col-lg-6 mb-1">
                <label for="">Message Font Size</label>
                <select class="form-control" v-model="seltpln.text_props.back_msg.size" v-on:input="content_changed" v-if="seltpln.text_props.back_msg.range[2] == 1">
                  <option :value="(i + seltpln.text_props.back_msg.range[0])" 
                          v-for="i in (seltpln.text_props.back_msg.range[1] - seltpln.text_props.back_msg.range[0] + 1)">
                    {{ i + seltpln.text_props.back_msg.range[0] }}
                  </option>
                </select>
              </div>
              <div class="col-12 col-sm-6 col-lg-6 mb-1" >
                <label for="">Color</label>
                <input class="form-control inputcolor" type="color" v-model="seltpln.text_props.back_msg.color" v-on:input="content_changed">
              </div>
            </div>
          </b-tab>
          <b-tab title="Agent Information">
            <div class="row">
              <div class="col-sm-4">
                <label for="">First Name</label>
                <input type="text" class="form-control" v-model="firstName" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Last Name</label>
                <input type="text" class="form-control" v-model="lastName" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Designation</label>
                <input type="text" class="form-control" v-model="designation" v-on:input="content_changed">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <label for="">Mobile</label>
                <input type="text" class="form-control" v-model="phoneNumber" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Email</label>
                <input type="text" class="form-control" v-model="email" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Website</label>
                <input type="text" class="form-control" v-model="agentwebsite" v-on:input="content_changed">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <label for="">Include Photo</label>
                <select class="form-control" v-model="inc_photo" v-on:input="content_changed">
                  <option value="photo">Profile Photo</option>
                  <option value="team" v-if="is_team == true">Team Photo</option>
                  <option value="nophoto">Hide Photo</option>
                </select>
              </div>
            </div>
          </b-tab>
          <b-tab title="Office Information">
            <div class="row">
              <div class="col-sm-6">
                <label for="">Company Name</label>
                <input type="text" class="form-control" v-model="companyName" v-on:input="content_changed">
              </div>
              <div class="col-sm-6">
                <label for="">Office Address</label>
                <input type="text" class="form-control" v-model="address" v-on:input="content_changed">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <label for="">Office City</label>
                <input type="text" class="form-control" v-model="city" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Office State</label>
                <input type="text" class="form-control" v-model="state" v-on:input="content_changed">
              </div>
              <div class="col-sm-4">
                <label for="">Office Zip</label>
                <input type="text" class="form-control" v-model="zip" v-on:input="content_changed">
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <label for="">Office Number</label>
                <input type="text" class="form-control" v-model="officePhoneNumber" v-on:input="content_changed">
              </div>
              <div class="col-sm-4" v-if="is_team == true">
                <label for="">Team Name</label>
                <input type="text" class="form-control" v-model="team_name" v-on:input="content_changed">
              </div>
            </div>
          </b-tab>
        </b-tabs>
        <div v-if="fpreview != ''">
          <br><hr><br>
          <div class="row">
            <div class="col-sm-6">
              <label for="">Name</label>
              <input class="form-control" type="text" v-model="savefname" placeholder="Add File Name">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <input type="button" class="btn btn-danger btn-xs-block mb-1" @click="save_postcard" value="Save and Continue Later">
              <input type="button" class="btn btn-primary btn-xs-block mb-1" @click="download_postcard('yes')" value="Mark Done and Download" v-if="loadpc">
              <input type="button" class="btn btn-primary btn-xs-block mb-1" @click="download_postcard" value="Download Postcard" v-else>
              <div v-html="alertmsg"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-5">
        <div class="row">
          <div class="col-sm-12 prev-holder">
            <h5 class="pc-headr">Front Preview</h5>
            <div class="prev-imgh">
              <div v-if="fpreview != ''">
                <div class="previmg">
                  <button id="croptip">                    
                    <img :src="fpreview" alt="Postcard Front Preview" class="rs-preview img-crop" width="100%" @click="modalmode = 'crop'" @close="modalmode = ''" />
                    <img :src="base_url + 'assets/images/pc-preview-bg.png'" width="100%" alt="" class="pcoverlay" v-if="generating == true">
                  </button>
                  <b-tooltip target="croptip" triggers="hover" variant="primary" noninteractive="true">
                    If you wish to crop the photo, please click on this preview image.
                  </b-tooltip>
                </div>
                <small>If you wish to crop the photo, please click on the above preview.</small>
              </div>
              <div class="loader-pc" v-else>Loading...</div>
            </div>
            <br>
            <h5 class="pc-headr">Back Preview</h5>
            <div class="prev-imgh">
              <div class="previmg" v-if="bpreview != ''">
                <img :src="bpreview" alt="Postcard Back Preview" class="rs-preview" width="100%" />
                <img :src="base_url + 'assets/images/pc-preview-bg.png'" width="100%" alt="" class="pcoverlay" v-if="generating == true">
              </div>
              <div class="loader-pc" v-else>Loading...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <img :src="base_url + 'assets/images/pc-preview.jpg'" alt="" width="100%">      
    </div>
    <saved-pc :savedpc="savedpc" @subsaved="process_savedpc" v-if="savedpc.length > 0"></saved-pc>
  </div>
</template>

<script>
  import axios from 'axios';
  import {mapGetters} from 'vuex'
  import {AgentMixin} from '../mixins/AgentMixin';
  
  var delay;

  export default {
      mixins: [AgentMixin],

      components : {
        'pcmodal' : () => import('../components/Postcard/PcModal'),
        'select-font' : () => import('../components/Materials/SelectFont'),
        'saved-pc' : () => import('../components/Postcard/SavedPc'),
      },

      data() {
        return {
          site : site,
          infomsg : '',
          alertmsg : '',
          please_wait : true,
          listingid : '', // remove value on launched 21734105
          validate_label : 'Generate Postcard',
          validated : false,
          pc_type : 'listed',
          pc_title : '',
          title_maxlen : 30,
          // pc_title_show : true,
          pc_title_back : '',

          pcup : '',
          templates : [],
          seltpln : [],
          prop : [],
          checked_img : [],
          chkid_img : [],
          max_img : 1,
          temp_img2 : '',

          address_only : '',
          address_prop : '',
          address_city : '',
          inc_photo : 'photo',
          actual_size : false,
          price_only : '',
          price_to_sell : '',
          beds_only : '',
          baths_only : '',
          beds_baths : '',
          back_sqft : '',
          include_price : true,
          open_house_datetime : '',
          pc_message : '',
          msg_ctr : 0,
          msg_limit : 250,
          agentwebsite : 'www.talktotucker.com',

          modalmode : '',
          modaltitle : '',
          modalsize : 'xl',
          cropped_photo : '',

          fpreview : '',
          bpreview : '',
          axirequest : 0,
          generating : false,

          savefname : '',
          recentpc : [],
          savedpc : [],
          modalpc : [],
          startover : false,
          justloadpc : false,
          loadpc : false,
        }
      },

      computed:{
        ...mapGetters({
          base_url: 'global/get_base_url',
          agent_dpn: 'agents/get_dpn'
        }),
        contact_line() {
          let cell = (this.phoneNumber != '') ? 'Cell: ' + this.phoneNumber + ' ' : '';
          let offc = (this.officePhoneNumber != '') ? 'Office: ' + this.officePhoneNumber : '';
          return cell + offc;
        },
      },

      created() {
        setTimeout(() => {
          if (this.dpn == '') {
            this.validate_if_office();
          }

          // override commpany name for metro
          this.companyName = (this.site == 'metro') ? 'F.C. Tucker Company' : this.companyName;

          this.please_wait = false;

          const urlSearchParams = new URLSearchParams(window.location.search);
          const params = Object.fromEntries(urlSearchParams.entries());

          // loads the json templates when listing id exists
          let targjson = (this.pcup == '') ? 'postcard' : 'postcard' + this.pcup;
          axios.get(base_url + 'assets/json/' + targjson + '.json?v='+ Math.random())
            .then(resp => {
              this.templates = resp.data;
              this.seltpln = resp.data[0];
            });


          // fetch saved postcard
          axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', { pro : 'savedpc' })
            .then(res => {
              this.recentpc = res.data.recent;
              this.savedpc  = res.data.current;

              if (this.recentpc != '') {
                this.modalpc = res.data.recent;
                this.modalmode = 'recent';
                this.$refs.vuemodal.show();
              }
            });

            let aw = this.agent_website;

            if (aw == '')
              aw = this.agentUrl;
            
            if (aw == '')
              aw = this.website;

            this.agentwebsite = this.agent_website

            if (params.mls != null) {
              this.listingid = params.mls;
            }
        }, 2000)
      },

      watch: {
        agent_dpn() {
          this.getAgentByDpn(this.agent_dpn);
        },
        pcup() {
          this.start_over();
        },
        pc_type() {
          this.validated      = (this.justloadpc == false) ? false : true;
          this.validate_label = 'Generate Postcard';

          if (this.fpreview != '') {   
            this.fpreview       = '';
            this.bpreview       = '';

            this.select_template(0);
          } else {
            this.check_pc_type();            
          }
        },
        seltpln() {
          this.select_template();
        },
        'seltpln.text_props.title.size': function(val, old) {
          setTimeout(() => {            
            let len = document.getElementById('titleopt');
            let ran = this.seltpln.text_props.title.yrange
            let dif = ran[1] - ran[0]
            
            if (len) {
              let div = dif / len.length;
              let opt = len.selectedIndex + 1;
              let mar = ran[0] + (opt * div);
  
              // console.log(opt + ' ' + val)
              // console.log(mar)
  
              this.seltpln.text_props.title.ypos = mar;
            }
          }, 500);
        },
        'seltpln.text_props.back_msg.size': function(v, o) {
          this.seltpln.text_props.back_msg.linespace = v + 6;
        },
        actual_size() {
          if (this.actual_size == false) {
            this.back_sqft = (this.prop.SquareFeet > 2500) ? 'Over 2500' : this.prop.SquareFeet + ' sq. ft.';
          } else {
            this.back_sqft = this.prop.SquareFeet + ' sq. ft.';
          }

          this.content_changed();
        },
        pc_message() {
          this.msg_ctr = this.pc_message.length;
          this.content_changed();
        },
        modalmode() {
          switch(this.modalmode) {
            case 'choose': this.modaltitle = 'Choose a photo you want to appear'; this.modalsize = 'xl'; break;
            case 'crop': this.modaltitle = 'Crop Image'; this.modalsize = 'xl'; break;
            case 'recent': this.modaltitle = 'Load Saved Postcard'; this.modalsize = 'sm'; break;
            case 'delsaved': this.modaltitle = 'Delete Saved Postcard'; this.modalsize = 'sm'; break;
          }

          this.infomsg = '';

          if (this.modalmode == '') {
            this.$refs.vuemodal.hide();
          } else {            
            this.$refs.vuemodal.show();
          }
        }
      },

      methods : {
        validate_if_office() {
          axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', {
            office_login : this.agent_dpn,
            pro : 'checkifofc'
          })
          .then(res => {
            if (res.data == 'Yes') {
              this.$refs.officemodal.show();
            }
          });
        },

        hide_office_modal() {
          this.$refs.officemodal.hide();
        },

        start_over() {
          this.pc_type    = 'listed';
          this.fpreview   = '';
          this.bpreview   = '';
          this.listingid  = '';
          this.savefname  = '';
          this.recentpc   = [];
          this.justloadpc = false;
          this.validated  = false;
          this.startover  = true;

          let targjson = (this.pcup == '') ? 'postcard' : 'postcard' + this.pcup;
          axios.get(base_url + 'assets/json/' + targjson + '.json?v='+ Math.random())
            .then(resp => {
              this.templates = resp.data;
              this.seltpln = resp.data[0];
            });
        },

        view_savedpc() {
          let elmnt = document.getElementById('savedpc');
          elmnt.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'start' });
        },

        setFont(f, targ) {
          this.seltpln.text_props[targ].font = f;

          if (this.justloadpc == false) {
            this.process();
          }
        },

        select_template(i) {
          if (i != null) {
            this.seltpln = this.templates[i];
          }

          if (!this.justloadpc) {
            this.cropped_photo = '';            
          }

          this.max_img = this.seltpln.max_img;
          
          this.check_pc_type();

          if (this.seltpln.name == 'moxi') {
            if (this.checked_img[1]) {
              this.temp_img2 = this.checked_img[1];
            } else {
              this.temp_img2 = this.prop.images[1].main_img;
            }
            
            this.pc_title      = this.pc_title.toLowerCase().replace(/(^\w{1})|(\s{1}\w{1})/g, match => match.toUpperCase());
            this.pc_title_back = this.pc_title.toLowerCase().replace(/(^\w{1})|(\s{1}\w{1})/g, match => match.toUpperCase()) + ': ' + this.address_only;

            console.log(this.seltpln.name + ' ' + this.pc_title_back)
            
            this.checked_img[1] = this.temp_img2;

            this.pc_message = 'If you or someone you know is thinking of making a move, now or in the near future, call me today! I will work hard to get you results.';
          } else if (this.seltpln.name == 'twilight') {
            this.pc_message = 'See this home in a new light! Join me for a special evening open house.';
          }

          
          if (i != null && this.justloadpc == false) {
            this.process();
          }
        },

        validate_listingid() {
          var that = this;

          if (this.listingid.length >= 8) {
              that.please_wait = true;
              that.infomsg = '';

              axios.get(sa_url + 'search/get-detail/' + this.listingid, {validateStatus: false})
                .then(response => {
                  if (response.data != 'No Data Found') {
                    let prop = response.data._source;
                    that.prop = prop;
                    that.validated = true;

                    let addr1 = '';
                    addr1  = prop.StreetAddress;
                    addr1 += (prop.StreetName != '') ? ' ' + prop.StreetName : '';
                    addr1 += (prop.StreetSuffix != '') ? ' ' + prop.StreetSuffix : '';

                    that.address_only   = addr1;
                    that.address_prop   = addr1 + ', ' + prop.City;
                    that.address_city   = prop.City;
                    that.checked_img[0] = prop.images[0].main_img;
                    that.checked_img[1] = prop.images[1].main_img;

                    let halfs           = (prop.HalfBathrooms != '' && prop.HalfBathrooms > 0)  ? ' / ' + prop.HalfBathrooms + ' Half Baths' : '';
                    
                    that.price_only     = Number(prop.ListingPrice).toLocaleString();
                    that.price_to_sell  = 'Priced to sell $' + Number(prop.ListingPrice).toLocaleString();
                    that.beds_only      = prop.Bedrooms;
                    that.baths_only     = prop.FullBathrooms;
                    that.beds_baths     = prop.Bedrooms + ' Bed, ' + prop.FullBathrooms + ' Full' + halfs;
                    that.back_sqft      = (prop.SquareFeet > 2500) ? 'Over 2500' : prop.SquareFeet + ' sq. ft.';

                    if (that.pc_type == 'sold') {
                      that.pc_message = 'If you or someone you know is thinking of making a move, now or in the near future, call me today! I will work hard to get you results.';
                    } else {
                      that.pc_message = '• ' + that.price_to_sell + '\r\n• ' + that.beds_baths + '\r\n• ' + that.back_sqft + '\r\nIf you know someone who might be interested in this home, please ask them to give me a call. I am also available for any of your real estate needs. Thank you.';
                    }

                    that.msg_ctr = that.pc_message.length - that.msg_limit;

                    // that.savefname = 'Postcard-' + that.listingid + '-' + that.dpn;
                    
                    // that.validate_label = 'Validate another Listing ID';
                  } else {
                    that.validated = false;
                    that.please_wait = false;
                    that.infomsg = '<div class="alert alert-danger"><b>Error</b>: The Listing ID you entered does not exists.</div>';                    
                  }
                    
                  that.please_wait = false;

                  if (that.startover == true) {
                    this.select_template(0);
                    that.startover = false;
                  }
                })
                .catch(error => {
                  that.validated = false;
                  that.please_wait = false;
                  that.infomsg = '<div class="alert alert-danger"><b>Error</b>: The Listing ID you entered does not exists.</div>';
                });
          } else {
              that.infomsg = '<div class="alert alert-danger"><b>Error</b>: You need to enter a valid Listing ID before you can proceed.</div>';
          }
        },

        check_pc_type() {
          if (this.justloadpc == false) {
            switch(this.pc_type) {
              case 'reduced' : 
                this.pc_title      = 'JUST REDUCED';
                this.pc_title_back = 'JUST REDUCED';
                break;
              case 'sold' : 
                this.pc_title      = 'JUST SOLD';
                this.pc_title_back = 'JUST SOLD';
                this.seltpln.text_props.back_msg.ypos = 250;
                break;
              case 'open_house' : 
                let date = new Date();
                let resultDate = new Date(date.getTime());
                resultDate.setDate(date.getDate() + (7 + 0 - date.getDay() - 1) % 7 + 1);
                let month = resultDate.toLocaleString('default', { month: 'long' });

                this.open_house_datetime = 'Sunday, ' + month + ' ' + resultDate.getDate() + ' from 12-2pm';
                this.pc_title      = (this.seltpln.name == 'elegant') ? this.open_house_datetime : 'OPEN HOUSE'; 
                this.pc_title_back = 'OPEN HOUSE'; 
                break;
              case 'pending':
                this.pc_title      = 'PENDING';
                this.pc_title_back = 'PENDING';
                break;
              default : 
                this.pc_title      = 'JUST LISTED';
                this.pc_title_back = 'JUST LISTED';
            }
          }
        },

        submit_modal(params) {
          switch (params.pro) {
            case 'update' : 
              this.cropped_photo = '';
              this.checked_img   = params.output;
              this.process();
              break;

            case 'crop' : 
              this.cropped_photo = params.output;
              this.process();
              break;

            case 'loadrecent':
              this.load_recent_pc_vars();
              break;

            case 'delsaved':
              this.please_wait = true;
              
              axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', {
                  id : this.modalpc.id,
                  pro : 'delsaved'
                })
                .then(res => {
                  this.refresh_savedpc();
                });
              
              break;
          }

          this.modalmode = ''
          this.$refs.vuemodal.hide();
        },

        content_changed() {
          clearTimeout(delay);
          let ms = 1000;

          delay = setTimeout(() => {
            this.infomsg  = '';
            this.$refs.vuemodal.hide();

            if (this.justloadpc == false) {
              this.process();
            }
          }, ms);
        },

        save_postcard() {
          if (this.savefname != '') {
            this.please_wait = true;

            if (this.recentpc.name != null && this.recentpc.name == this.savefname) {
              this.process('save');
            } else {            
              axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', {
                  savefname : this.savefname,
                  pro : 'checkexist'
                })
                .then(res => {
                  this.please_wait = false;
                  
                  if (res.data.current.length > 0) {
                    let ans = confirm('The postcard name already exist, do you want to overwrite instead?');

                    if (ans == true) {
                      this.process('save');
                    }
                  } else {
                    this.process('save');
                  }
                })
                .catch(err => console.log(err));
            }
            
            this.refresh_savedpc();
          } else {
            this.alertmsg = '<div class="alert alert-danger mt-3">Please enter a postcard name.</div>';
          }
        },

        async process(pro = 'preview') {
          if(pro == 'done' || pro == 'save') {
            this.please_wait = true;
            this.alertmsg    = '';
          } else {
            this.axirequest++;
            this.generating  = true;
            let status       = '';
          }

          if (this.loadpc) {
            this.validated = true;
          }

          // extract address prop and separate city
          let save_status = pro == 'done' ? 'Done' : 'Ongoing';
          let indxof      = this.address_prop.lastIndexOf(',');
          let gcity       = this.address_prop.substring(indxof + 1);

          let params = {
            tpl : this.seltpln,
            listingid : this.listingid,
            pc_type : this.pc_type,
            pc_title : this.pc_title,
            open_house_datetime : this.open_house_datetime,
            pc_title_back : this.pc_title_back,
            listing_price : this.listing_price,
            include_price : this.include_price,
            address_prop : this.address_prop.substring(0, indxof).trim(),
            city : gcity.trim(),
            price_only : this.price_only,
            price_to_sell : this.price_to_sell,
            beds_only : this.beds_only,
            baths_only : this.baths_only,
            beds_baths : this.beds_baths,
            back_sqft : this.back_sqft,
            firstname : this.firstName,
            lastname : this.lastName,
            designation : this.designation,
            contact_line : this.contact_line,
            phoneNumber : this.phoneNumber,
            officePhoneNumber : this.officePhoneNumber,
            email : this.email,
            agentwebsite : this.agentwebsite,
            company_name : this.companyName,
            office_address : this.address,
            office_city : this.city,
            office_state : this.state,
            office_zip : this.zip,
            team_name : this.team_name,
            inc_photo : this.inc_photo,
            pc_message : this.pc_message,
            checked_img : this.checked_img,
            cropped_photo : this.cropped_photo,
            savefname : this.savefname,
            save_status : save_status,
            loadpc : this.loadpc,
            pcup : this.pcup,
            pro : pro,
          }

          let resp = await axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', params);

          if (pro == 'save') {
            this.please_wait = false;
            this.alertmsg = '<div class="alert alert-success mt-3">Postcard has been saved.</div>';
          } else {
            this.axirequest--;

            if (this.axirequest <= 0 && resp.data.front != undefined) {
              this.axirequest = 0;
              this.justloadpc = false;
              this.please_wait = false;
              
              this.fpreview = 'data:image/gif;base64,' + resp.data.front;
              this.bpreview = 'data:image/gif;base64,' + resp.data.back;
              
              setTimeout(() => {
                this.generating = false;
              }, 500);
            }
          }
        },

        download_postcard(done = 'no') {
          let that = this;
          let params = {
            recentpc : this.recentpc,
            done : done,
            pcup : this.pcup,
            pro : 'generate'
          }

          that.please_wait = true;

          axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', params)
            .then(resp => {
              document.location.href = base_url + 'marketing_materials/postcard_vue/download/' + resp.data.file;

              // clear loaded pc
              if (done == 'yes') {
                this.process('done');

                this.recentpc = [];
                this.savefname = '';
                this.loadpc = false;

                this.refresh_savedpc();
              } else {
                this.please_wait = false;
              }
            });
        },

        process_savedpc(params) {
          this.modalpc = params.pc;

          if (params.pro == 'load') {
            this.please_wait = true;
            this.recentpc = params.pc;
            this.load_recent_pc_vars();
          }

          if (params.pro == 'delete') {
            this.modalmode = 'delsaved';
          }
        },

        load_recent_pc_vars() {
          this.justloadpc = true;
          
          axios.get(base_url + 'assets/temp/postcardvue/' + this.recentpc.datafile + '.json?v='+ Math.random())
            .then(resp => {
              let data = resp.data;

              axios.get(sa_url + 'search/get-detail/' + data.listingid, {validateStatus: false})
                .then(response => {
                  if (response.data != 'No Data Found') {
                    let prop = response.data._source;
                    this.prop = prop;
                    this.validated = true;

                    this.seltpln = data.tpl;
                    this.listingid = data.listingid;
                    this.pc_type = data.pc_type;
                    this.pc_title = data.pc_title;
                    this.open_house_datetime = data.open_house_datetime;
                    this.pc_title_back = data.pc_title_back;
                    this.listing_price = data.listing_price;
                    this.include_price = data.include_price;
                    this.address_prop = data.address_prop + ', ' + data.city;
                    this.price_only = data.price_only;
                    this.price_to_sell = data.price_to_sell;
                    this.beds_only = data.beds_only;
                    this.baths_only = data.baths_only;
                    this.beds_baths = data.beds_baths;
                    this.back_sqft = data.back_sqft;
                    this.firstName = data.firstname;
                    this.lastName = data.lastname;
                    this.designation = data.designation;
                    this.phoneNumber = data.phoneNumber;
                    this.officePhoneNumber = data.officePhoneNumber;
                    this.email = data.email;
                    this.agentwebsite = data.agentwebsite;
                    this.companyName = data.company_name;
                    this.address = data.office_address;
                    this.city = data.office_city;
                    this.state = data.office_state;
                    this.zip = data.office_zip;
                    this.team_name = data.team_name;
                    this.inc_photo = data.inc_photo;
                    this.pc_message = data.pc_message;
                    this.checked_img = data.checked_img;
                    this.cropped_photo = data.cropped_photo;
                    this.savefname = data.savefname;
                    this.pcup = data.pcup;

                    this.loadpc = true;

                    this.process();
                  }
                });
            });
        },

        refresh_savedpc() {
          axios.post(base_url + 'marketing_materials/postcard_vue/ajaxes', { pro : 'savedpc' })
          .then(res => {
            this.savedpc = res.data.current;
            this.please_wait = false;
          });
        },
        
      },
  }
</script>

<style>
.tpls {
  }
  .tpls img {
    /* border: 1px solid #ccc; */
    width: 100%;
    }
  .tpls img:hover {
    cursor: pointer;
    }
    .tpls img.active {
      border: 1px solid #e69302 !important;
      }
.card-header div.btn-link {
  color: #e69302 !important;
  font-weight: bold;
  text-decoration: none;
  }
.modal-dialog {
  /* max-width: 1024px !important; */
  }
  .prop-img {
    height: 615px;
    overflow: auto;
    width: 100%;
    }
    .prop-img div {
      text-align: center;
      }
      .prop-img div label input {
        height: 25px;
        margin: 10px;
        width: 25px;
        }
.modal-header .close {
  display: none !important;
  }

.prev-holder img:first-child {
  padding: 2px;
}

.previmg {
  border: 1px solid #ccc;
  position: relative;
  }

.pcoverlay {
  position: absolute; 
  top: 0px;
  left: 0px;
}

.img-crop {
  cursor: pointer;
}

.nav-link {
  font-weight: bold;
}

.loader-pc {
  color: #e69302;
  font-size: 20px;
  margin: 100px auto;
  width: 1em;
  height: 1em;
  border-radius: 50%;
  position: relative;
  text-indent: -9999em;
  -webkit-animation: loadpc 1.3s infinite linear;
  animation: loadpc 1.3s infinite linear;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
  left: 0px !important;
  background: none !important;
}

.thesep {
  width: 80%;
  border: 1px solid #ccc;
  margin-bottom: 25px;
  }

.title-ctr {
  position: absolute;
  right: 10px;
  top: 3px;
}

.inputcolor {
  height: 38px;
  padding: 2px;
}

#croptip {
  background: none;
  border: none;
  outline: none;
  width: 100%;
}


@-webkit-keyframes loadpc {
  0%,
  100% {
    box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
  }
  12.5% {
    box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
  }
  25% {
    box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
  }
  37.5% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
  }
  50% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
  }
  62.5% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
  }
  75% {
    box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
  }
  87.5% {
    box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
  }
}
@keyframes loadpc {
  0%,
  100% {
    box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
  }
  12.5% {
    box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
  }
  25% {
    box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
  }
  37.5% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
  }
  50% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
  }
  62.5% {
    box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
  }
  75% {
    box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
  }
  87.5% {
    box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
  }
}
</style>