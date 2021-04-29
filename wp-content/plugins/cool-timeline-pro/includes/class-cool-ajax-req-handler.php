<?php
    class CoolAjaxReqHandler{
         /**
         * The Constructor
         */
        public function __construct() {
            /*
                Story timeline ajax load more ajax request handler hooks
            */
            add_action( 'wp_ajax_ctl_ajax_load_more', array($this,'ctl_stories_load_more_handler'));
            add_action( 'wp_ajax_nopriv_ctl_ajax_load_more',  array($this,'ctl_stories_load_more_handler'));

            // category based filter hooks
            add_action( 'wp_ajax_st_cat_filters', array($this,'storytm_cat_filters_handler'));
            add_action( 'wp_ajax_nopriv_st_cat_filters',  array($this,'storytm_cat_filters_handler'));

            /*
                Content timeline ajax load more ajax request handler hooks
            */
            add_action( 'wp_ajax_ct_ajax_load_more', array($this, 'ctl_content_load_more_handler'));
            add_action( 'wp_ajax_nopriv_ct_ajax_load_more', array($this, 'ctl_content_load_more_handler'));
            // category based filter hooks
            add_action( 'wp_ajax_ct_cat_filters', array($this, 'contenttm_cat_filters_handler'));
            add_action( 'wp_ajax_nopriv_ct_cat_filters',  array($this,'contenttm_cat_filters_handler'));

        }


    // stories timeline ajax load more handler
    function ctl_stories_load_more_handler() {

         // Check for nonce security
        $nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'ctl-ajax-nonce' ) ){
             die ( 'Please refresh your window and try again');
            }
            
        // get incomming vars in ajax request
        $last_year = isset( $_POST['last_year'] )? $_POST['last_year']:0;
        $alternate = isset( $_POST['alternate'] )? $_POST['alternate']:0; 
        $attribute = isset( $_POST['attribute'] ) ? array_map( 'esc_attr', $_POST['attribute'] ) : array();
    
        // It's load dynamic styles
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/common/class-cool-timeline-custom-styles.php');
        // for grabing dynamic icon
        include CTP_PLUGIN_DIR .'fa-icons/includes/template-tags.php';

        // create classes based on design 
        if($attribute['designs'])
        {
            $design_cls='main-'.$attribute['designs'];
            $design=$attribute['designs'];
        }else{
            $gn_cls='main-default';
            $design='default';
        }
        // set default var for later use
        $output = ''; $ctl_html = '';$ctl_format_html = ''; $ctl_animation='';
        $ctl_avtar_html = '';  $timeline_id = ''; $cls_icons='';
        $layout=$attribute['layout'] ?$attribute['layout']:'default';
        $ctl_animation=$attribute['animations'] ?$attribute['animations']:'none';
        $pagination=$attribute['pagination'];
        if (isset($attribute['icons']) && $attribute['icons']=="YES"){
            $cls_icons='icons_yes';
        }else{
            $cls_icons='icons_no';
        }

        // build quries based on incomming vers
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/story-timeline/ctl-build-query.php');
        $args['paged']=esc_attr( $_POST['page'] );
        //load main vertical story timeline story content
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/story-timeline/layouts/story-vertical-layout.php');
        // send back in json format
        
        wp_send_json_success( $ctl_html );
        // stop processes
        wp_die();
    }

    // content timeline load more ajax handler
    function ctl_content_load_more_handler() {
        $ctl_html='';
        $output='';
        
         // Check for nonce security
         $nonce = $_POST['nonce'];
         if ( ! wp_verify_nonce( $nonce, 'ctl-ajax-nonce' ) ){
              die ( 'Please refresh your window and try again');
             }

        // grabing incomming var in ajax request
        $last_year = isset( $_POST['last_year'] )? $_POST['last_year']:0;
        $alternate = isset( $_POST['alternate'] )? $_POST['alternate']:0;
        $args['paged']=esc_attr( $_POST['page'] );
        $attribute = isset( $_POST['attribute'] ) ? array_map( 'esc_attr', $_POST['attribute'] ) : array();
        $layout=$attribute['layout']?$attribute['layout']:'default';
        $ctl_animation=$attribute['animations'] ?$attribute['animations']:'none';
        $pagination=$attribute['pagination'];
        // set dynamic classes based on design type
        if ($attribute['designs']) {
                        $design_cls = 'main-' . $attribute['designs'];
                        $design = $attribute['designs'];
                    } else {
                        $design_cls = 'main-default';
                        $design = 'default';
                    }
        
        // load content timeline content 
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/content-timeline/layouts/loop-content-timeline.php');
    // send back in json format
        wp_send_json_success( $ctl_html );
        wp_die();
        }

        // content timeline cateogry based filter ajax request handler
    function contenttm_cat_filters_handler() {
        $ctl_html='';
        $output='';
         // Check for nonce security
         $nonce = $_POST['nonce'];
         if ( ! wp_verify_nonce( $nonce, 'ctl-ajax-nonce' ) ){
              die ( 'Please refresh your window and try again');
             }
         $term_slug=esc_attr( $_POST['termslug'] );
        $attribute = isset( $_POST['attribute'] ) ? array_map( 'esc_attr', $_POST['attribute'] ) : array();
        $layout=$attribute['layout']?$attribute['layout']:'default';
        $pagination=$attribute['pagination'];
        $ctl_animation=$attribute['animations'] ?$attribute['animations']:'none';
        $last_year = isset( $_POST['last_year'] )? $_POST['last_year']:0;
        $alternate = isset( $_POST['alternate'] )? $_POST['alternate']:0;
        if($term_slug!="all"){
        $attribute['post-category']=$term_slug;
        }
        if ($attribute['designs']) {
                        $design_cls = 'main-' . $attribute['designs'];
                        $design = $attribute['designs'];
                    } else {
                        $design_cls = 'main-default';
                        $design = 'default';
                    }
        // loading content file
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/content-timeline/layouts/loop-content-timeline.php');
        wp_send_json_success( $ctl_html );
        wp_die();
        }

    // stories timeline category fitler ajax request handler
   function storytm_cat_filters_handler() {
      
     // Check for nonce security
     $nonce = $_POST['nonce'];
     if ( ! wp_verify_nonce( $nonce, 'ctl-ajax-nonce' ) ){
          die ( 'Please refresh your window and try again');
         }
         
        $term_slug=esc_attr( $_POST['termslug'] );
        $attribute = isset( $_POST['attribute'] ) ? array_map( 'esc_attr', $_POST['attribute'] ) : array();
        $ctl_category=$term_slug;
        $alternate = isset( $_POST['alternate'] )? $_POST['alternate']:0; 
        $last_year = isset( $_POST['last_year'] )? $_POST['last_year']:0;
        $pagination=$attribute['pagination'];
            if($attribute['designs'])
            {
                $design_cls='main-'.$attribute['designs'];
                $design=$attribute['designs'];
            }else{
                $gn_cls='main-default';
                $design='default';
            }
            // loads dynamic styles
        require(CTP_PLUGIN_DIR.'/includes/shortcodes/common/class-cool-timeline-custom-styles.php');
        // for grabing dynamic icon
        include CTP_PLUGIN_DIR .'fa-icons/includes/template-tags.php';

            // set default vars for later use 
        $output = ''; $ctl_html = '';$ctl_format_html = ''; $ctl_animation='';
            $ctl_avtar_html = '';  $timeline_id = ''; $cls_icons='';
            $layout=$attribute['layout'] ?$attribute['layout']:'default';
            $ctl_animation=$attribute['animations'] ?$attribute['animations']:'none';
            if (isset($attribute['icons']) && $attribute['icons']=="YES"){
                $cls_icons='icons_yes';
            }else{
                $cls_icons='icons_no';
            }
            // generate custom query according the request
            require(CTP_PLUGIN_DIR.'/includes/shortcodes/story-timeline/ctl-build-query.php');
            $args['paged']=1;
            // load contents
            require(CTP_PLUGIN_DIR.'/includes/shortcodes/story-timeline/layouts/story-vertical-layout.php');
            
            wp_send_json_success( $ctl_html );
            wp_die();
        }

    } // class end

 