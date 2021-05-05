<?php
add_action('wp_enqueue_scripts', 'infinity_news_dynamic_style', 100);
function infinity_news_dynamic_style()
{


    $infinity_news_dynamic_style = '';
    $default = infinity_news_get_default_theme_options();
    $not_google_font = array('Helvetica', 'Palatino', 'Tahoma', 'Georgia', 'Trebuchet', 'Verdana', 'Arial');
    $infinity_news_post_category_list = infinity_news_post_category_list($select_cat = false);

    $background_color = get_theme_mod('background_color','f5f5f5');
    $background_image = get_theme_mod('background_image');

    $infinity_news_primary_color = get_theme_mod('infinity_news_primary_color', $default['infinity_news_primary_color']);
    $infinity_news_secondary_color = get_theme_mod('infinity_news_secondary_color', $default['infinity_news_secondary_color']);

    $twp_primary_font = esc_html(get_theme_mod('twp_primary_font', $default['twp_primary_font']));
    $twp_primary_font_weight = esc_html(get_theme_mod('twp_primary_font_weight', $default['twp_primary_font_weight']));

    $twp_secondary_font = esc_html(get_theme_mod('twp_secondary_font', $default['twp_secondary_font']));
    $twp_secondary_font_weight = esc_html(get_theme_mod('twp_secondary_font_weight', $default['twp_secondary_font_weight']));

    $twp_full_font_size = absint(get_theme_mod('twp_full_font_size', $default['twp_full_font_size']));
    $twp_title_font_size_large = absint(get_theme_mod('twp_title_font_size_large', $default['twp_title_font_size_large']));
    $twp_title_font_size_big = absint(get_theme_mod('twp_title_font_size_big', $default['twp_title_font_size_big']));
    $twp_title_font_size_medium = absint(get_theme_mod('twp_title_font_size_medium', $default['twp_title_font_size_medium']));
    $twp_title_font_size_small = absint(get_theme_mod('twp_title_font_size_small', $default['twp_title_font_size_small']));
    $infinity_news_breadcrumb_bg_color = get_theme_mod('infinity_news_breadcrumb_bg_color', $default['infinity_news_breadcrumb_bg_color']);
    $infinity_news_breadcrumb_text_color = get_theme_mod('infinity_news_breadcrumb_text_color', $default['infinity_news_breadcrumb_text_color']);

    $infinity_news_like_button_bg_color = get_theme_mod( 'infinity_news_like_button_bg_color' );
    $infinity_news_like_button_thumb_color = get_theme_mod( 'infinity_news_like_button_thumb_color', $default['infinity_news_like_button_thumb_color'] );
    $infinity_news_like_count_bg_color = get_theme_mod( 'infinity_news_like_count_bg_color', $default['infinity_news_like_count_bg_color'] );
    $infinity_news_like_count_text_color = get_theme_mod( 'infinity_news_like_count_text_color', $default['infinity_news_like_count_text_color'] );

    if ($infinity_news_like_button_bg_color) {
        $infinity_news_dynamic_style .= ".site .twp-like-dislike-button .twp-post-like-dislike{background:" . esc_html($infinity_news_like_button_bg_color) . "}";
    }

    if ($infinity_news_like_button_thumb_color) {
        $infinity_news_dynamic_style .= ".site .twp-like-dislike-button .twp-post-like-dislike{color:" . esc_html($infinity_news_like_button_thumb_color) . "}";
    }

    if ($infinity_news_like_count_bg_color) {
        $infinity_news_dynamic_style .= ".site .twp-like-dislike-button .twp-like-count, .site .twp-like-dislike-button .twp-dislike-count{background:" . esc_html($infinity_news_like_count_bg_color) . "}";
    }

    if ($infinity_news_like_count_text_color) {
        $infinity_news_dynamic_style .= ".site .twp-like-dislike-button .twp-like-count, .site .twp-like-dislike-button .twp-dislike-count{color:" . esc_html($infinity_news_like_count_text_color) . "}";
    }

    if ($infinity_news_breadcrumb_bg_color) {
        $infinity_news_dynamic_style .= ".breadcrumbs{background:" . esc_html($infinity_news_breadcrumb_bg_color) . "}";
    }

    if ($infinity_news_breadcrumb_text_color) {
        $infinity_news_dynamic_style .= ".breadcrumbs ul .trail-item span{color:" . esc_html($infinity_news_breadcrumb_text_color) . "}";
    }

    if ($infinity_news_primary_color) {
        $infinity_news_dynamic_style .= "a:hover,a:focus,.nav-links .page-numbers.current, .nav-links .page-numbers:hover, .nav-links .page-numbers:focus, .woocommerce nav.woocommerce-pagination ul li a:focus, .woocommerce nav.woocommerce-pagination ul li a:hover, .woocommerce nav.woocommerce-pagination ul li span.current,.latest-category-post{color:" . esc_html($infinity_news_primary_color) . "}";
        $infinity_news_dynamic_style .= ".home-carousel-overlay .entry-meta-category a, .block-bg-alt .entry-meta-category a,.slide-icon-1{background-color:" . esc_html($infinity_news_primary_color) . "}";
        $infinity_news_dynamic_style .= "#comments .comment-list .bypostauthor .comment-author img {border-color:" . esc_html($infinity_news_primary_color) . "}";
        $infinity_news_dynamic_style .= ".entry-title a {background-image: linear-gradient(180deg, transparent 90%," . esc_html($infinity_news_primary_color) . " 0)}";
    }

    if ($infinity_news_secondary_color) {
        $infinity_news_dynamic_style .= ".single-post .twp-post-content .entry-content a{color:" . esc_html($infinity_news_secondary_color) . "}";
        $infinity_news_dynamic_style .= ".post-wrapper .post-thumbnail .format-icon, .trend-item, .format-icon{background-color:" . esc_html($infinity_news_secondary_color) . "}";
        $infinity_news_dynamic_style .= ".twp-single-affix .twp-social-email .twp-icon-holder:hover .share-media-nocount:after, .twp-single-affix .twp-social-email .twp-icon-holder:focus .twp-social-count:after{border-top-color:" . esc_html($infinity_news_secondary_color) . "}";
    }

    if ($twp_primary_font) {

        if (!in_array($twp_primary_font, $not_google_font)) {
            $variants_lists_1 = infinity_news_fonts_variants( $twp_primary_font );
            wp_register_style('infinity-news-primary-font', '//fonts.googleapis.com/css?family=' . esc_attr($twp_primary_font).":".esc_html( $variants_lists_1 ) );
            wp_enqueue_style('infinity-news-primary-font');
        }

        $infinity_news_dynamic_style .= "body, .site button, input, select, optgroup, textarea, .primary-font{font-family:" . esc_html($twp_primary_font) . "}";
    }

    if ($twp_primary_font_weight) {
        $infinity_news_dynamic_style .= "body, button, input, select, optgroup, textarea{font-weight:" . esc_html($twp_primary_font_weight) . "}";
    }

    if ($twp_secondary_font) {

        if (!in_array($twp_secondary_font, $not_google_font)) {
            $variants_lists_2 = infinity_news_fonts_variants( $twp_secondary_font );
            wp_register_style('infinity-news-secondary-font', '//fonts.googleapis.com/css?family=' . esc_attr($twp_secondary_font).":".esc_html( $variants_lists_2 ) );
            wp_enqueue_style('infinity-news-secondary-font');
        }

        $infinity_news_dynamic_style .= ".site h1, .site h2, .site h3, .site h4, .site h5, .site h6, .site .secondary-font, .site .secondary-font a, .site .category-widget-header .post-count{font-family:" . esc_html($twp_secondary_font) . "}";
    }

    if ($twp_secondary_font_weight) {
        $infinity_news_dynamic_style .= ".site h1, .site h2, .site h3, .site h4, .site h5, .site h6{font-weight:" . esc_html($twp_secondary_font_weight) . "}";
    }

    if ($twp_full_font_size) {
        $infinity_news_dynamic_style .= ".site .entry-title-full{font-size:" . esc_html($twp_full_font_size) . "px}";
    }

    if ($twp_title_font_size_large) {
        $infinity_news_dynamic_style .= ".site .entry-title-large{font-size:" . esc_html($twp_title_font_size_large) . "px}";
    }

    if ($twp_title_font_size_big) {
        $infinity_news_dynamic_style .= ".site .entry-title-big{font-size:" . esc_html($twp_title_font_size_big) . "px}";
    }

    if ($twp_title_font_size_medium) {
        $infinity_news_dynamic_style .= ".site .entry-title-medium{font-size:" . esc_html($twp_title_font_size_medium) . "px}";
    }

    if ($twp_title_font_size_small) {
        $infinity_news_dynamic_style .= ".site .entry-title-small{font-size:" . esc_html($twp_title_font_size_small) . "px}";
    }

    $twp_infinity_news_home_sections = get_theme_mod('twp_infinity_news_home_sections_55', json_encode($default['twp_infinity_news_home_sections']));

    $twp_infinity_news_home_sections = json_decode($twp_infinity_news_home_sections);

    foreach ($twp_infinity_news_home_sections as $infinity_news_home_section) {
        
        $home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;

        switch ($home_section_type) {
            case 'grid-posts':
            $ed_grid_1 = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

            if ($ed_grid_1 == 'yes') {

                $first_block_bg_color_1 = isset( $infinity_news_home_section->first_block_bg_color ) ? $infinity_news_home_section->first_block_bg_color : '' ;

                if ($first_block_bg_color_1) {
                    $infinity_news_dynamic_style .= ".block-bg-alt{background:" . esc_html($first_block_bg_color_1) . "}";
                }
            }
            break;

            case 'grid-posts-2':
            $ed_grid_2 = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
            if ($ed_grid_2 == 'yes') {

                $first_block_bg_color_2 = isset( $infinity_news_home_section->first_block_bg_color ) ? $infinity_news_home_section->first_block_bg_color : '' ;
                $first_block_text_color_2 = isset( $infinity_news_home_section->first_block_text_color ) ? $infinity_news_home_section->first_block_text_color : '' ;

                if ($first_block_bg_color_2) {
                    $infinity_news_dynamic_style .= ".block-bg-alt.block-bg-alt-1{background:" . esc_html($first_block_bg_color_2) . "}";
                }
            }
            break;
        }
    }

    $infinity_news_category_colories = get_theme_mod( 'infinity_news_category_colories', json_encode( $default['infinity_news_category_colories'] ) );
    $infinity_news_category_colories = json_decode( $infinity_news_category_colories );

    foreach( $infinity_news_category_colories as $infinity_news_category_color ){

        if( isset( $infinity_news_category_color->category ) && $infinity_news_category_color->category ){

            if( isset( $infinity_news_category_color->category_color ) && $infinity_news_category_color->category_color ){
                    $infinity_news_dynamic_style .= ".entry-meta-category .twp_cat_".esc_html( $infinity_news_category_color->category )."{color:".esc_html( $infinity_news_category_color->category_color )."}";
                    $infinity_news_dynamic_style .= ".entry-meta-category .twp_cat_".esc_html( $infinity_news_category_color->category )."{border-color:".esc_html( $infinity_news_category_color->category_color )."}";
                    $infinity_news_dynamic_style .= ".block-bg-alt .entry-meta-category .twp_cat_".esc_html( $infinity_news_category_color->category )."{background:".esc_html( $infinity_news_category_color->category_color )."}";
                    $infinity_news_dynamic_style .= ".home-carousel-overlay .entry-meta-category .twp_cat_".esc_html( $infinity_news_category_color->category )."{background:".esc_html( $infinity_news_category_color->category_color )."}";
                }

        }
    }

    if( $background_color && !$background_image ){

        $infinity_news_dynamic_style .= ".block-title-wrapper .block-title-bg, .block-title-wrapper .title-controls-bg{background: #".esc_html( $background_color )."}";

    }

    wp_add_inline_style('infinity-news-style', $infinity_news_dynamic_style);
}
