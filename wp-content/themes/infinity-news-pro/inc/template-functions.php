<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Infinity_News
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function infinity_news_body_classes($classes)
{   
    $default = infinity_news_get_default_theme_options();
    global $post;
    // Adds a class of hfeed to non-singular pages.
    if ( !is_singular() ) {
        $classes[] = 'hfeed';
    }

    // Adds a class of no-sidebar when there is no sidebar present.
    if ( !is_active_sidebar( 'sidebar-1' ) ) {
        $classes[] = 'no-sidebar';
    }

    $global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout',$default['global_sidebar_layout'] ) );
    
    if ( ! is_active_sidebar( 'sidebar-1' ) ) { $global_sidebar_layout = 'no-sidebar'; }
    if( infinity_news_check_woocommerce_page() ){ if ( ! is_active_sidebar( 'infinity-news-woocommerce-widget' ) ) { $global_sidebar_layout = 'no-sidebar'; } }
    
    if( ( is_home() && is_front_page() ) || is_single() || is_page() ){

        if( ( is_front_page() && !is_home() && is_page() ) || ( is_single() || is_page() ) ){

            if( infinity_news_check_woocommerce_page() && is_product() ){

                $infinity_news_post_sidebar = esc_html( get_theme_mod( 'product_sidebar_layout',$default['product_sidebar_layout'] ) );
                
            }else{

                $infinity_news_post_sidebar = esc_html( get_post_meta( $post->ID, 'infinity_news_post_sidebar_option', true ) );
                if( $infinity_news_post_sidebar == 'global-sidebar' || empty( $infinity_news_post_sidebar ) ){ $infinity_news_post_sidebar = $global_sidebar_layout; }
            }
            
            $classes[] = $infinity_news_post_sidebar;

        }else{
            $default = infinity_news_get_default_theme_options();
            $twp_infinity_news_home_sections = get_theme_mod( 'twp_infinity_news_home_sections_55', json_encode( $default['twp_infinity_news_home_sections'] ) );
            $twp_infinity_news_home_sections = json_decode( $twp_infinity_news_home_sections );
            foreach( $twp_infinity_news_home_sections as $infinity_news_home_section ){

                $home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;
                switch( $home_section_type ){
                    case 'latest-post':
                    $global_sidebar_layout = isset( $infinity_news_home_section->sidebar_layout ) ? $infinity_news_home_section->sidebar_layout : '' ;
                    break;
                }
            }
            $classes[] = $global_sidebar_layout;

        }
        
    }else{

        if( is_404() ){

            $classes[] = 'no-sidebar';

        }else{

            $classes[] = $global_sidebar_layout;

        }
    }

    if( is_search() || is_archive() || ( is_home() && !is_front_page() ) ){
        $infinity_news_archive_layout = esc_html( get_theme_mod( 'infinity_news_archive_layout',$default['infinity_news_archive_layout'] ) );
        $classes[] = $infinity_news_archive_layout;
    }

    if( is_front_page() && is_home() ){
        $default = infinity_news_get_default_theme_options();
        $twp_infinity_news_home_sections = get_theme_mod( 'twp_infinity_news_home_sections_55', json_encode( $default['twp_infinity_news_home_sections'] ) );
        $twp_infinity_news_home_sections = json_decode( $twp_infinity_news_home_sections );
        foreach( $twp_infinity_news_home_sections as $infinity_news_home_section ){

            $home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;
            switch( $home_section_type ){
                case 'latest-post':
                $latest_post_layout = isset( $infinity_news_home_section->latest_post_layout ) ? $infinity_news_home_section->latest_post_layout : '' ;
                break;
            }

        }
        $classes[] = $latest_post_layout;
    }

    if( !is_active_sidebar( 'infinity-news-offcanvas-widget' ) ){
        $classes[] = 'no-offcanvas';
    }

    return $classes;
}

add_filter('body_class', 'infinity_news_body_classes');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function infinity_news_pingback_header()
{
    if ( is_singular() && pings_open() ) {
        printf('<link rel="pingback" href="%s">', esc_url( get_bloginfo('pingback_url') ) );
    }
}

add_action('wp_head', 'infinity_news_pingback_header');
