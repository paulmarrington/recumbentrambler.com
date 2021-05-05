<?php
/**
* Open Graph.
*
* @package Infinity News
*/



add_action( 'wp_head', 'infinity_news_opengraph',1 );
/**
 * Open Graph Meta.
 *
 * @since  1.0.0
 *
 * @return void
 */
function infinity_news_opengraph() {
    
    $default = infinity_news_get_default_theme_options();

    $twp_ed_open_graph = get_theme_mod( 'twp_ed_open_graph' );
    if( $twp_ed_open_graph ){

        global $post;
        
        $twp_open_graph_title = get_theme_mod( 'twp_open_graph_title',$default['twp_open_graph_title'] );
        $twp_open_graph_desc = get_theme_mod( 'twp_open_graph_desc',$default['twp_open_graph_desc'] );
        $twp_open_graph_site_name = get_theme_mod( 'twp_open_graph_site_name',$default['twp_open_graph_title'] );
        $twp_open_graph_site_type = get_theme_mod( 'twp_open_graph_site_type' );
        $twp_open_graph_url = get_theme_mod( 'twp_open_graph_url',$default['twp_open_graph_url'] );
        $twp_open_graph_home_default_image = get_theme_mod('twp_open_graph_home_default_image');
        $twp_open_graph_locole = get_theme_mod( 'twp_open_graph_locole',$default['twp_open_graph_locole'] );
        $twp_open_graph_custom_meta = infinity_news_meta_sanitize_metabox( get_theme_mod('twp_open_graph_custom_meta') );

        $twp_og_ed = '';
        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            $post_id = $post->ID;
            $twp_og_ed = esc_attr( get_post_meta( $post->ID, 'twp_og_ed', true ) );
            $twp_og_title = esc_attr( get_post_meta( $post->ID, 'twp_og_title', true ) );
            $twp_og_desc = esc_attr( get_post_meta( $post->ID, 'twp_og_desc', true ) );
            $twp_og_url = esc_attr( get_post_meta( $post->ID, 'twp_og_url', true ) );
            $twp_og_type = esc_attr( get_post_meta( $post->ID, 'twp_og_type', true ) );
            $twp_og_custom_meta = infinity_news_meta_sanitize_metabox( get_post_meta( $post->ID, 'twp_og_custom_meta', true ) );
            $twp_og_image = esc_attr( get_post_meta( $post->ID, 'twp_og_image', true ) );
        
        }

        if( $twp_open_graph_locole ){
            echo '<meta property="og:locale" content="'. esc_attr( $twp_open_graph_locole ).'">',"\n";
        }

        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            if( $twp_og_type ){
                $twp_open_graph_site_type = $twp_og_type;
            }
            if( $twp_open_graph_site_type ){
                echo '<meta property="og:type" content="'. esc_attr( $twp_open_graph_site_type ).'">',"\n";
            }

        }else{

            if( $twp_open_graph_site_type ){
                echo '<meta property="og:type" content="'. esc_attr( $twp_open_graph_site_type ).'">',"\n";
            }

        }

        if( $twp_open_graph_site_name ){
            echo '<meta property="og:site_name" content="'. esc_attr( $twp_open_graph_site_name ).'">',"\n";
        }

        if( is_single() || is_page() || is_archive() ){

            if( !$twp_og_ed && ( is_single() || is_page() ) ){

                $twp_open_graph_title = get_the_title( $post_id );
                if( $twp_og_title ){
                    $twp_open_graph_title = $twp_og_title;
                }
                echo '<meta property="og:title" content="'. esc_attr( $twp_open_graph_title ).'">',"\n";
            }else{
                $twp_open_graph_title = get_the_archive_title( $before = '', $after = '' );
                echo '<meta property="og:title" content="'. esc_attr( $twp_open_graph_title ).'">',"\n";
            }

        }else{

            if( $twp_open_graph_title ){
                echo '<meta property="og:title" content="'. esc_attr( $twp_open_graph_title ).'">',"\n";
            }

        }

        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            if( has_excerpt() ){
              $twp_open_graph_desc = esc_html( get_the_excerpt() );
            }else{
                
                $content_post = get_post($post_id);
                $content = $content_post->post_content;
                if( $content ){
                    $twp_open_graph_desc = esc_html( wp_trim_words( $content,10,'...') );
                }

            }
            if( $twp_og_desc ){
                $twp_open_graph_desc = $twp_og_desc;
            }

            if( $twp_open_graph_desc ){ 
                echo '<meta property="og:description" content="'. esc_attr( $twp_open_graph_desc ).'">',"\n";
            }

        }else{
            if( $twp_open_graph_desc ){
                echo '<meta property="og:description" content="'. esc_attr( $twp_open_graph_desc ).'">',"\n";
            }
        }

        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            $twp_open_graph_url = get_the_permalink();
            if( $twp_og_url ){
                $twp_open_graph_url = $twp_og_url;
            }
            echo '<meta property="og:url" content="'. esc_attr( $twp_open_graph_url ).'">',"\n";
        }else{

            if( $twp_open_graph_url ){
            echo '<meta property="og:url" content="'. esc_attr( $twp_open_graph_url ).'">',"\n";
            }

        }

        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'large' );

            if( $featured_image[0] ){

                $twp_open_graph_home_default_image = $featured_image[0];

            }
            if( $twp_og_image ){
                $twp_open_graph_home_default_image = $twp_og_image;
            }
            if( $twp_open_graph_home_default_image ){
                
                echo '<meta property="og:image" content="'. esc_attr( $twp_open_graph_home_default_image ).'">',"\n";
                echo '<meta property="og:image:secure_url" content="'. esc_attr( $twp_open_graph_home_default_image ).'" />',"\n";
                echo '<meta property="og:image:alt" content="'. esc_attr( $twp_open_graph_title ).'" />',"\n";
            }

        }else{

            if( $twp_open_graph_home_default_image ){
                
                echo '<meta property="og:image" content="'. esc_attr( $twp_open_graph_home_default_image ).'">',"\n";
                echo '<meta property="og:image:secure_url" content="'. esc_attr( $twp_open_graph_home_default_image ).'" />',"\n";
                echo '<meta property="og:image:alt" content="'. esc_attr( $twp_open_graph_title ).'" />',"\n";
            }

        }

        if( !$twp_og_ed && ( is_single() || is_page() ) ){

            if( $twp_og_custom_meta ){

                echo infinity_news_meta_sanitize_metabox( $twp_og_custom_meta );
            }
        }

        if( $twp_open_graph_custom_meta ){

            echo $twp_open_graph_custom_meta;
        }

    }

}