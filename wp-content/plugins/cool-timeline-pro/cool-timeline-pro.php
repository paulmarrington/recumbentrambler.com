<?php 
/*
  Plugin Name:Cool Timeline Pro
  Plugin URI:http://coolplugins.net/
  Description:Cool Timeline Pro, #1 Wordpress timeline plugin to showcase your life story or your company history in a vertical or horizontal timeline format. You can also create a content timeline using your blog-posts or any post-type.
  Version:3.5.2
  Author:Cool Plugins
  Author URI:http://coolplugins.net/
  License:GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages 
  Text Domain:cool-timeline
 */
/** Configuration * */

if (!defined('CTLPV')){
    define('CTLPV', '3.5.2');

}

/*
 Defined constant for later use
 */
define('CTP_FILE', __FILE__ );
define('CTP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CTP_PLUGIN_DIR', plugin_dir_path(__FILE__));
defined( 'CTP_FA_DIR' ) or define( 'CTP_FA_DIR', CTP_PLUGIN_DIR.'/fa-icons/' );
defined( 'CTP_FA_URL' ) or define( 'CTP_FA_URL', CTP_PLUGIN_URL.'/fa-icons/'  );

if (!class_exists('CoolTimelinePro')) {

    class CoolTimelinePro {
         /**
         * Construct the plugin objects
         */
        public function __construct() {
            //set plugin path for later use
            $this->plugin_path = plugin_dir_path(__FILE__);
            // Installation and uninstallation hooks
            register_activation_hook(__FILE__ , array($this,'ctp_activation_before'));
            //included all files
            add_action('plugins_loaded', array($this, 'clt_include_files'));
            // check if elementor is installed
            if( file_exists( plugin_dir_path( __DIR__ ) . "elementor/elementor.php"  ) ){
                include_once( ABSPATH . "wp-admin/includes/plugin.php" );
                // Check if elementor is in the list of active plugins?
                if( is_plugin_active( "elementor/elementor.php" ) ){
                    // initialize elementor-addon for Cool-Timeline-Pro
                    require_once CTP_PLUGIN_DIR . "includes/elementor-component/init.php";
                }                
            }
          
            if(is_admin()){
             //Adding plugin settings link  
            $plugin = plugin_basename(__FILE__);
            add_action( 'admin_init', array($this,'ctlOnInit' ) );
            
            add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));   
             //Fixed bridge theme confliction using this action hook
            add_action( 'wp_print_scripts', array($this,'ctl_deregister_javascript'), 100 );
            add_action('admin_enqueue_scripts',array($this, 'ctl_custom_order_js'));

            // add a tinymce button that generates our shortcode for the user
            add_action('after_setup_theme', array($this, 'ctl_add_tinymce'));
            add_action( 'wp_ajax_ctl_hideRating',array($this,'ctl_pro_HideRating' ));
            }else{
             add_action( 'init', array($this,'fa_icons_tp_tags' ) );
              }

           // Add image size for Avatar image
            add_image_size('ctl_avatar', 250, 250, true); // Hard crop left top
           //Hooked plugin translation function 
            add_action('plugins_loaded', array($this, 'clt_load_plugin_textdomain'));
            /*@since version 2.8
                Author:NS
            */
              // registering custom route for categories
             add_action( 'rest_api_init',array($this,'ctl_register_custom_routes'));
             require_once CTP_PLUGIN_DIR . "includes/gutenberg-block/ctl-block.php";
              // flush_rewrite rules
             add_action( 'init', array($this,'clt_flush_rewrite_rules_after_activation' ) );
     
            add_action( 'init', array($this,'ctl_migrate_old_content' ) );
            add_action( 'save_post',array($this,'save_timeline_story_meta'), 10, 3 );
      
            // integrated gutenberg instant timeline builder
            require CTP_PLUGIN_DIR .'includes/gutenberg-instant-builder/cooltimeline-instant-builder.php';
			CoolTimelineProInstantBuilder::get_instance();
           
        }
         
/**
 * Save post metadata when a story is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function save_timeline_story_meta( $post_id, $post, $update ) {
    $post_type = get_post_type($post_id);
    // If this isn't a 'cool_timeline' post, don't update it.
    if ( "cool_timeline" != $post_type ) return;
    // - Update the post's metadata.
    if ( isset($_POST['ctl_story_date'] ) ) {
         $story_timestamp= ctl_generate_custom_timestamp(sanitize_text_field($_POST['ctl_story_date']));
         update_post_meta($post_id,'ctl_story_timestamp',$story_timestamp );
       
        }
  } 

    // flushed rewrite rules after plugin activations
        function clt_flush_rewrite_rules_after_activation(){
                //flush rewrite rules after activation
            if ( get_option( 'ctl_flush_rewrite_rules_flag' ) ) {
                flush_rewrite_rules();
                delete_option( 'ctl_flush_rewrite_rules_flag' );
            }
        }

         function ctl_custom_order_js($hook) {
             $current_page=ctl_get_ctp();
            if($current_page!="cool_timeline" ) 
                return;
             wp_enqueue_script( 'ctl-admin-js',CTP_PLUGIN_URL.'assets/js/ctl_admin.js',array('jquery'));
             wp_localize_script( 'ctl-admin-js', 'ajax_object',
             array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
         }
         
         // includes files on plugin loaded hook
          public function clt_include_files(){
       
           // Register cooltimeline post type for timeline stories
            require_once CTP_PLUGIN_DIR . 'admin/class-cool-timeline-posttype.php';
            // contain common function for plugin
            require_once CTP_PLUGIN_DIR . 'includes/ctl-helpers.php';
            require_once CTP_PLUGIN_DIR . 'includes/class-cool-ajax-req-handler.php';

         if(is_admin()){

            require_once CTP_PLUGIN_DIR . "admin/init-api.php";                

            // including plugin settings panel class
            require_once( CTP_PLUGIN_DIR ."admin/admin-page-class/admin-page-class.php");
            // including timeline stories meta boxes class 
            require_once CTP_PLUGIN_DIR . "admin/meta-box-class/my-meta-box-class.php";
            // Vc addon for timeline shortcode
            require_once CTP_PLUGIN_DIR . "admin/class-cool-vc-addon.php";
            // included timeline stories icon handler class
            require CTP_PLUGIN_DIR .'fa-icons/fa-icons-class.php';
            require CTP_PLUGIN_DIR .'admin/ctl-meta-fields.php';
           
            require CTP_PLUGIN_DIR .'admin/ctl-settings.php';
            //ask for feedback admin notice 
            require CTP_PLUGIN_DIR .'admin/class-review-notice.php';
            /*
             * Options panel
             */
            ctl_option_panel();
            /*
             *  custom meta boxes 
             */
             clt_pro_meta_boxes();
             new CoolVCAddon();
             new Ctl_Fa_Icons();
             new ReviewNotice();

          } else{

             /*
             * Frontend files
             */
        
            require_once CTP_PLUGIN_DIR . 'includes/shortcodes/story-timeline/cool-timeline-shortcode.php';
            require_once CTP_PLUGIN_DIR . 'includes/shortcodes/content-timeline/cool-content-timeline-shortcode.php';
            require_once CTP_PLUGIN_DIR . 'includes/shortcodes/common/class-cool-timeline-custom-styles.php';
        
             new CoolTimelineShortcode();
             new CoolContentTimeline();
            
          }
             new CoolAjaxReqHandler();
             $cool_timeline_posttype = new CoolTimelinePosttype();
         }

      /*
        Perform some actions on plugin activation time
       */   
    function ctp_activation_before() {

        if (is_plugin_active( 'cool-timeline/cooltimeline.php' ) ) 
            {
            deactivate_plugins( 'cool-timeline/cooltimeline.php' );
           }
          //  update_option("cool-timelne-v",CTLPV);
           // update_option("cool-timelne-type","PRO");
           // for rating notice
            update_option("cool-timelne-pro-installDate",date('Y-m-d h:i:s') );
            add_option("cool-timelne-pro-ratingDiv","no");
            update_option("ctl_flush_rewrite_rules_flag",true);

          $ctl_settings=get_option('cool_timeline_options');
          if(is_array($ctl_settings) && !empty($ctl_settings)){
          if(isset($ctl_settings['enable_navigation']) && in_array('enable_navigation', $ctl_settings)){
             update_option("ctl-can-migrate","no");
            }else{
               update_option("ctl-can-migrate","yes");
            }
           }else{
            update_option("ctl-can-migrate","yes");
           }
    }
        
        /*
            Loading translation files of plugin 
         */

        function clt_load_plugin_textdomain() {
         $rs = load_plugin_textdomain('cool-timeline', FALSE, basename(dirname(__FILE__)) . '/languages/');
        }

        // Add the settings link to the plugins page
        function plugin_settings_link($links) {
            $settings_link = '<a href="options-general.php?page=cool_timeline_page">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }
        /**
         * Include other PHP scripts for the plugin
         * @return void
         *
         **/
        public function fa_icons_tp_tags() {
            // Files specific for the front-ned
            if ( ! is_admin() ) {
                // Load template tags (always last)
                include CTP_PLUGIN_DIR .'fa-icons/includes/template-tags.php';
            }
        }

        /*
        * Fixed Bridge theme confliction
        */
        function ctl_deregister_javascript() {

            if(is_admin()) {
                global $post; 
                if(function_exists('get_current_screen')){
                $screen = get_current_screen();
                if ($screen!=Null && $screen->base == "toplevel_page_cool_timeline_page") {
                    wp_deregister_script('bridge-admin-default');
                    wp_deregister_script('default');
                    wp_deregister_script('subway-admin-default');
                    wp_deregister_script('strata-admin-default');
                    wp_deregister_script('stockholm-admin-default');// for Stockholm Theme
                 }
               }
                if( isset($post) && isset($post->post_type) && $post->post_type =='cool_timeline'){
                    wp_deregister_script('acf-timepicker');
                    wp_deregister_script('acf-input'); // datepicker translaton issue
                    wp_deregister_script('acf'); // datepicker translaton issue
				//	wp_deregister_script('lvca-timepicker-addon'); // datepicker confict Livemesh Addons for WPBakery Page Builder plugin
					//wp_deregister_style('lvca-timepicker-addon-css');// datepicker confict Livemesh Addons for WPBakery Page Builder plugin
                    wp_deregister_script('thrive-admin-datetime-picker'); // datepicker conflict with Rise theme
                    wp_deregister_script( 'et_bfb_admin_date_addon_js'); // datepicker conflict with Divi theme
                    wp_deregister_script( 'zeen-engine-admin-vendors-js'); // datepicker conflict with zeen engine plugin
                }
            }
        }

        /*
            Adding shortcode generator in TinyMCE editor
         */
        public function ctl_add_tinymce() {
         global $typenow;
         if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
              return;
        }

        if ( get_user_option('rich_editing') == 'true' ) {
            add_filter('mce_external_plugins', array(&$this, 'ctl_add_tinymce_plugin'));
            add_filter('mce_buttons', array(&$this, 'ctl_add_tinymce_button'));
          }    

        }

        /*
            Creating TinyMCE plugin for shortcode generator
         */
    
        public function ctl_add_tinymce_plugin($plugin_array) {
            $plugin_array['cool_timeline'] =CTP_PLUGIN_URL.'/assets/js/shortcode-btn.js';
            return $plugin_array;
        }

        // Add the button key for address via JS
        function ctl_add_tinymce_button($buttons) {
            array_push($buttons, 'cool_timeline_shortcode_button');
            return $buttons;
        }
        // end tinymce button functions           

       // run migration from old version since version 3.0
    function ctl_migrate_old_content(){
           
        if(get_option('ctl-migrated-content')!==false){
              return;
          }
          $ctl_version = get_option('cool-timelne-v');
          $ctl_type = get_option('cool-timelne-type');
          if(!empty($ctl_type) && $ctl_type=="FREE"){
                if(version_compare( $ctl_version,'1.7', '<' )){
                  ctl_migrate_from_free($version="old");   
                }else{
                  ctl_migrate_from_free($version="latest");   
                }
              
              update_option('cool-timelne-v',CTLPV);
              update_option('cool-timelne-type','PRO');
              update_option("ctl-can-migrate","no");
          }
          else if(!empty($ctl_type) &&  $ctl_type=="PRO" && version_compare( $ctl_version,'3.0', '<' ) ){
              ctl_migrate_pro_old_stories();
              update_option('cool-timelne-v',CTLPV);
              update_option("ctl-can-migrate","no");
            }else{
                update_option('cool-timelne-v',CTLPV);
                update_option('cool-timelne-type','PRO');
                update_option("ctl-can-migrate","no"); 
            }
         update_option('ctl-migrated-content','yes');
    }
        /*
            Integrated custom route for timeline categories
        */
        function ctl_register_custom_routes() {
            register_rest_route( 'cooltimeline/v1', '/categories', array(
                'methods'  =>'GET',
                'callback' =>array($this,'ctl_route_callback'),
                'permission_callback' => '__return_true'
            ) );
        }
        // endpoint callback handlers
        function ctl_route_callback($request){
            $category=array();
            if(version_compare(get_bloginfo('version'),'4.5.0', '>=') ){
                $terms = get_terms(array(
                'taxonomy' => 'ctl-stories',
                'hide_empty' => false,
                ));
                }else{
                        $terms = get_terms('ctl-stories', array('hide_empty' => false,
                    ) );
                }
                if (!empty($terms) || !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $ctl_terms_l[$term->slug] =$term->name;
                    }
                }
                if (isset($ctl_terms_l) && array_filter($ctl_terms_l) != null) {
                    $category['categories'] =$ctl_terms_l;
                } else {
                    $category['categories'] = array('0' => 'No category');
                }
            return $category;
        }

        // on init add 
        public function ctlOnInit(){
            if(is_admin()){
                foreach (array('post.php','post-new.php','edit-tags.php','term.php') as $hook) {
                    add_action("admin_head-$hook", array($this,'ctl_admin_head'));
                }
            }
        }
        /**
         * Localize Script :- generate category obeject for tinymce editor 
         */
        function ctl_admin_head() {

            $plugin_url = plugins_url('/', __FILE__);
        if(version_compare(get_bloginfo('version'),'4.5.0', '>=') ){
            $terms = get_terms(array(
            'taxonomy' => 'ctl-stories',
            'hide_empty' => false,
            ));
            }else{
                    $terms = get_terms('ctl-stories', array('hide_empty' => false,
                ) );
            }

            if (!empty($terms) || !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if(isset($term->slug)){
                    $ctl_terms_l[$term->slug] =$term->slug;
                    }
                }
            }


            if (isset($ctl_terms_l) && array_filter($ctl_terms_l) != null) {
                $category =json_encode($ctl_terms_l);
            } else {
                $category = json_encode(array('0' => 'No category'));
            }
            ?>
            <!-- TinyMCE Shortcode Plugin -->
        <script type='text/javascript'>
        var ctl_cat_obj = {
                category:'<?php echo $category; ?>'
            };
        </script>
            <style type="text/css">
            .mce-container[aria-label="Add Cool Timeline Shortcode"],
            .mce-container[aria-label="Add Vertical Content Timeline Shortcode"],
            .mce-container[aria-label="Add Horizontal Content Timeline Shortcode"]
            {margin-top:38px;}
            .mce-container[aria-label="Add Cool Timeline Shortcode"], 
            .mce-container[aria-label="Add Horizontal Content Timeline Shortcode"],
            .mce-container[aria-label="Add Vertical Content Timeline Shortcode"]
            {max-height:100%;}
            .mce-container[aria-label="Add Vertical Content Timeline Shortcode"] .mce-reset,
            .mce-container[aria-label="Add Horizontal Content Timeline Shortcode"] .mce-reset
            {
            max-height: calc(100% - 58px);
            overflow-y: scroll;
            overflow-x: hidden;
            margin-top:50px;
                }
        .mce-container[aria-label="Add Cool Timeline Shortcode"] .mce-reset {
            max-height: calc(100% - 58px);
            overflow-y: scroll;
            overflow-x: hidden;
            margin-top:50px;
                }
            .mce-container[aria-label="Add Cool Timeline Shortcode"] 
            .mce-foot .mce-abs-layout, 
            .mce-container[aria-label="Add Vertical Content Timeline Shortcode"] .mce-foot .mce-abs-layout,
            .mce-container[aria-label="Add Horizontal Content Timeline Shortcode"] .mce-foot .mce-abs-layout {
            position: fixed;
            background: #ddd;
            top:0;
                }
            </style>
            <!-- TinyMCE Shortcode Plugin -->
            <?php
        }

}
//end class
}


// instantiate the plugin class
 new CoolTimelinePro();

