<?php
/**
* Twitter Summary Card.
*
* @package  Infinity News
*/



add_action( 'wp_head', 'infinity_news_twitter_summary_card',1 );
/**
 * Open Graph Meta.
 *
 * @since  1.0.0
 *
 * @return void
 */
function infinity_news_twitter_summary_card() {
    
    $default = infinity_news_get_default_theme_options();

   $twp_ed_twitter_summary = get_theme_mod( 'twp_ed_twitter_summary' );
    if( $twp_ed_twitter_summary ){

        global $post;
        
        $twp_twitter_summary_title = esc_html( get_theme_mod( 'twp_twitter_summary_title',$default['twp_twitter_summary_title'] ) );
        $twp_twitter_summary_desc =  esc_html( get_theme_mod( 'twp_twitter_summary_desc',$default['twp_twitter_summary_desc'] ) );
        $twp_twitter_summary_site_type =  esc_html( get_theme_mod( 'twp_twitter_summary_site_type' ) );
        $twp_twitter_summary_home_default_image =  esc_url( get_theme_mod('twp_twitter_summary_home_default_image') );
        $twp_twitter_summary_custom_meta = infinity_news_meta_sanitize_metabox( get_theme_mod('twp_twitter_summary_custom_meta') );
        $twp_twittwer_summary_user =  esc_html( get_theme_mod('twp_twittwer_summary_user' ) );

        $twp_ts_ed = '';
        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            $post_id = $post->ID;
            $twp_ts_ed = esc_html( get_post_meta( $post->ID, 'twp_ts_ed', true ) );
            $twp_twitter_summary_title_metabox = esc_html( get_post_meta( $post->ID, 'twp_twitter_summary_title', true ) );
            $twp_twitter_summary_desc_metabox = esc_html( get_post_meta( $post->ID, 'twp_twitter_summary_desc', true ) );
            $twp_twitter_summary_username_metabox = esc_html( get_post_meta( $post->ID, 'twp_twitter_summary_username', true ) );
            $twp_twitter_summary_type_metabox = esc_html( get_post_meta( $post->ID, 'twp_twitter_summary_type', true ) );
            $twp_twitter_summary_custom_meta_metabox = infinity_news_meta_sanitize_metabox( get_post_meta( $post->ID, 'twp_twitter_summary_custom_meta', true ) );
            $twp_twitter_summary_image_metabox = esc_url( get_post_meta( $post->ID, 'twp_twitter_summary_image', true ) );

        }

        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            if( $twp_twitter_summary_username_metabox ){
                $twp_twittwer_summary_user = $twp_twitter_summary_username_metabox;
            }

            if( $twp_twittwer_summary_user ){
                echo '<meta property="twitter:site" content="'. esc_attr( $twp_twittwer_summary_user ).'">', "\n";
                echo '<meta property="twitter:creator" content="'. esc_attr( $twp_twittwer_summary_user ).'">', "\n";
            }

        }else{

            if( $twp_twittwer_summary_user ){
                echo '<meta property="twitter:site" content="'. esc_attr( $twp_twittwer_summary_user ).'">', "\n";
                echo '<meta property="twitter:creator" content="'. esc_attr( $twp_twittwer_summary_user ).'">', "\n";
            }

        }

        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            if( $twp_twitter_summary_type_metabox ){
                $twp_twitter_summary_site_type = $twp_twitter_summary_type_metabox;
            }
            if( $twp_twitter_summary_site_type ){
                echo '<meta property="twitter:card" content="'. esc_attr( $twp_twitter_summary_site_type ).'">', "\n";
            }
        }else{

            if( $twp_twitter_summary_site_type ){
                echo '<meta property="twitter:card" content="'. esc_attr( $twp_twitter_summary_site_type ).'">', "\n";
            }

        }

        if( is_single() || is_page() || is_archive() ){

            if( !$twp_ts_ed && ( is_single() || is_page() ) ){

                $twp_twitter_summary_title = get_the_title( $post_id );

                if( $twp_twitter_summary_title_metabox ){
                    $twp_twitter_summary_title = $twp_twitter_summary_title_metabox;
                }
                echo '<meta property="twitter:title" content="'. esc_attr( $twp_twitter_summary_title ).'">', "\n";
             
            }else{

                $twp_twitter_summary_title = get_the_archive_title( $before = '', $after = '' );
                echo '<meta property="twitter:title" content="'. esc_attr( $twp_twitter_summary_title ).'">', "\n";
            
            }

        }else{

            if( $twp_twitter_summary_title ){
                echo '<meta property="twitter:title" content="'. esc_attr( $twp_twitter_summary_title ).'">', "\n";
            
            }

        }

        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            if( has_excerpt() ){
              $twp_twitter_summary_desc = esc_html( get_the_excerpt() );
            }else{
                
                $content_post = get_post($post_id);
                $content = $content_post->post_content;
                if( $content ){
                    $twp_twitter_summary_desc = esc_html( wp_trim_words( $content,20,'...') );
                }

            }

            if( $twp_twitter_summary_desc_metabox ){
                $twp_twitter_summary_desc = $twp_twitter_summary_desc_metabox;
            }
            if( $twp_twitter_summary_desc ){
            echo '<meta property="twitter:description" content="'. esc_attr( $twp_twitter_summary_desc ).'">', "\n";
            }

        }else{
            if( $twp_twitter_summary_desc ){
            echo '<meta property="twitter:description" content="'. esc_attr( $twp_twitter_summary_desc ).'">', "\n";
            }
        }

        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'large' );

            if( $featured_image[0] ){

                $twp_twitter_summary_home_default_image = $featured_image[0];

            }
            if( $twp_twitter_summary_image_metabox ){
                $twp_twitter_summary_home_default_image = $twp_twitter_summary_image_metabox;
            }
            if( $twp_twitter_summary_home_default_image ){
                
            echo '<meta property="twitter:image" content="'. esc_attr( $twp_twitter_summary_home_default_image ).'">', "\n";
            }

        }else{

            if( $twp_twitter_summary_home_default_image ){
                echo '<meta property="twitter:image" content="'. esc_attr( $twp_twitter_summary_home_default_image ).'">', "\n";
            }

        }

        if( !$twp_ts_ed && ( is_single() || is_page() ) ){

            if( $twp_twitter_summary_custom_meta_metabox ){

                echo infinity_news_meta_sanitize_metabox( $twp_twitter_summary_custom_meta_metabox );
            }

        }

        if( $twp_twitter_summary_custom_meta ){

            echo infinity_news_meta_sanitize_metabox( $twp_twitter_summary_custom_meta );
        }

    }

}