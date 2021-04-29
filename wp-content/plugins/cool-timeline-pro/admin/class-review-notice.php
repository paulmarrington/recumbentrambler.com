<?php

if (!class_exists('ReviewNotice')) {
    class ReviewNotice {
        /**
         * The Constructor
         */
        public function __construct() {
            // register actions
            if(is_admin()){
                add_action( 'admin_notices',array($this,'admin_notice_for_reviews'));
                add_action( 'admin_print_scripts', array($this, 'load_script' ) );
                add_action( 'wp_ajax_ctl_dismiss_notice',array($this,'dismiss_review_notice' ) );
            }
        }

    /**
	 * Load script to dismiss notices.
	 *
	 * @return void
	 */
	public function load_script() {
		wp_register_script( 'feedback-notice-script', CTP_PLUGIN_URL. 'assets/js/ctl-admin-feedback-notice.js', array( 'jquery' ),null, true );
        wp_enqueue_script( 'feedback-notice-script' );
        wp_register_style( 'feedback-notice-styles',CTP_PLUGIN_URL.'assets/css/ctl-admin-feedback-notice.css' );
        wp_enqueue_style( 'feedback-notice-styles' );
    }

    public function dismiss_review_notice(){
        $rs=update_option( 'cool-timelne-pro-ratingDiv','yes' );
        echo  json_encode( array("success"=>"true") );
        exit;
    }
    
    public function admin_notice_for_reviews(){

        if( !current_user_can( 'update_plugins' ) ){
            return;
         }
         // get installation dates and rated settings
         $installation_date = get_option( 'cool-timelne-pro-installDate' );
         $alreadyRated =get_option( 'cool-timelne-pro-ratingDiv' )!=false?get_option( 'cool-timelne-pro-ratingDiv'):"no";

         // check user already rated 
         if( $alreadyRated=="yes") {
            return;
            }

            // grab plugin installation date and compare it with current date
            $display_date = date( 'Y-m-d h:i:s' );
            $install_date= new DateTime( $installation_date );
            $current_date = new DateTime( $display_date );
            $difference = $install_date->diff($current_date);
            $diff_days= $difference->days;
          
            // check if installation days is greator then week
         if (isset($diff_days) && $diff_days>=3) {
                echo $this->create_notice_content();
               }
       }  

       // generated review notice HTML
       function create_notice_content(){
        $ajax_url=admin_url( 'admin-ajax.php' );
        $ajax_callback='ctl_dismiss_notice';
        $wrap_cls="notice notice-info is-dismissible";
        $img_path=CTP_PLUGIN_URL.'assets/images/cool-timeline-logo.png';
        $p_name="Cool Timeline PRO";
        $like_it_text='Rate Now! ★★★★★';
        $already_rated_text=esc_html__( 'I already rated it', 'cool-timeline' );
        $not_like_it_text=esc_html__( 'No, not good enough, i do not like to rate it!', 'cool-timeline' );
        $not_interested=esc_html__( 'Not Interested', 'cool-timeline2' );
        $p_link=esc_url('https://codecanyon.net/item/cool-timeline-pro-wordpress-timeline-plugin/reviews/17046256?utf8=%E2%9C%93&reviews_controls%5Bsort%5D=ratings_descending');
        $wp_link=esc_url('https://wordpress.org/support/plugin/cool-timeline/reviews/?filter=5');
       
        $message="Thanks for using <b>$p_name</b> WordPress plugin. We hope it meets your expectations! Please share a few minutes from your valuable time to add a review on <a href='$p_link' target='_blank'><strong>codecanyon</strong><a/> or <a href='$wp_link' target='_blank'><strong>WP.org</strong></a>, it works as a boost for us to keep working on more <a href='https://coolplugins.net' target='_blank'><strong>cool plugins</strong></a>!<br/>";
      
        $html='<div data-ajax-url="%8$s"  data-ajax-callback="%9$s" class="cool-timeline-feedback-notice-wrapper %1$s" style="display:table;max-width:870px;">
        <div class="logo_container" style="display:table-cell;vertical-align:top;"><a href="%5$s"><img src="%2$s" alt="%3$s"></a></div>
        <div class="message_container" style="display:table-cell;vertical-align:top;">%4$s
        <div class="callto_action">
        <ul>
            <li class="love_it" style="float:left;"><a href="%5$s" class="like_it_btn button button-primary" target="_new" title="I Love It!">%6$s</a></li>
            <li class="already_rated" style="float:left;"><a href="javascript:void(0);" class="already_rated_btn button ctl_dismiss_notice" title="I have already rated, close this notice now!">%7$s</a></li>
            <li class="already_rated" style="float:left;"><a href="javascript:void(0);" class="already_rated_btn button ctl_dismiss_notice" title="I have no time, please close this notice!">%10$s</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';

 return sprintf($html,
        $wrap_cls,
        $img_path,
        $p_name,
        $message,
        $p_link,
        $like_it_text,
        $already_rated_text,
        $ajax_url,// 8
        $ajax_callback,//9
        $not_interested//10
        );
        
       }

    } //class end

} 



