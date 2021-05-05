<?php
/**
 * Default theme options.
 *
 * @package Infinity News
 */

if (!function_exists('infinity_news_get_default_theme_options')) :

    /**
     * Get default theme options
     *
     * @since 1.0.0
     *
     * @return array Default theme options.
     */
    function infinity_news_get_default_theme_options()
    {

        $defaults = array();

        $infinity_news_post_category_list = infinity_news_post_category_list( $select_cat = false );
        
        $cat_color_array = array();
        $cat_color_array[] = array(
                'category' => '',
                'category_color' => '#787878',
            );

        if( $infinity_news_post_category_list ){
        foreach( $infinity_news_post_category_list as $key => $twp_Category ){

                $cat_color = get_theme_mod( 'twp_cat_color_'.esc_html( $key ) );
                if( $cat_color ){

                    $cat_color_array[] = array(

                        'category' => $key,
                        'category_color' => $cat_color,

                    );

                }
            }
        }

        $defaults['infinity_news_category_colories'] = $cat_color_array;
        
        // Home options.
        $defaults['home_sidebar_layout'] = 'right-sidebar';
        $defaults['twp_infinity_news_home_sections'] = array(
            array(
                'home_section_type' => 'grid-posts',
                'section_title' => esc_html__('Banner Block 1', 'infinity-news'),
                'post_category' => '',
                'section_ed' => 'yes',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_relevant_cat' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'post_category_post_num' => 13,
                'first_block_bg_color' => '#133b5a',
                'first_block_text_color' => '#fff',
            ),
            array(
                'home_section_type' => 'grid-posts-2',
                'section_title' => esc_html__('Banner Block 2', 'infinity-news'),
                'post_category' => '',
                'post_category_1' => '',
                'post_category_2' => '',
                'section_ed' => 'yes',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_arrows_carousel' => 'yes',
                'ed_dots_carousel' => 'no',
                'ed_autoplay_carousel' => 'no',
                'ed_relevant_cat' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'first_block_bg_color' => '#f75454',
                'first_block_text_color' => '#fff',
                'post_category_post_num_1' => 3,
                'post_category_post_num_2' => 4,
            ),
            array(
                'home_section_type' => 'carousel-posts',
                'section_title' => esc_html__('Carousel Posts', 'infinity-news'),
                'post_category' => '',
                'ed_carouser_overlay_layout' => '',
                'section_ed' => 'yes',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_arrows_carousel' => 'yes',
                'ed_dots_carousel' => 'no',
                'ed_autoplay_carousel' => 'no',
                'ed_relevant_cat' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'post_category_post_num' => 12,
            ),
            array(
                'home_section_type' => 'jumbotron-block',
                'section_title' => esc_html__('Jumbotron Posts', 'infinity-news'),
                'section_vertical' => esc_html__('Recent Posts', 'infinity-news'),
                'post_category' => '',
                'post_category_1' => '',
                'post_category_2' => '',
                'section_ed' => 'yes',
                'add_image_1' => '',
                'add_image_1_link' => '',
                'add_image_2' => '',
                'add_image_2_link' => '',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_arrows_carousel' => 'yes',
                'ed_autoplay_carousel' => 'yes',
                'ed_relevant_cat' => 'no',
                'switch_left_right' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'post_category_post_num_1' => 13,
            ),
            array(
                'home_section_type' => 'multiple-category-posts',
                'section_title' => '',
                'post_category' => '',
                'post_category_1' => '',
                'post_category_2' => '',
                'post_category_3' => '',
                'section_ed' => 'yes',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_excerpt_content' => 'no',
                'ed_relevant_cat' => 'no',
                'block_1_title' => '',
                'block_2_title' => '',
                'block_3_title' => '',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'post_category_post_num_3' => 5,
            ),
            array(
                'home_section_type' => 'latest-post',
                'section_title' => '',
                'section_ed' => 'yes',
                'slider_ed' => 'yes',
                'slider_category' => '',
                'slider_autoplay' => 'yes',
                'slider_dots' => 'no',
                'slider_arrows' => 'yes',
                'sidebar_layout' => 'right-sidebar',
                'latest_post_layout' => 'index-layout-1',
                'section_bg_image' => '',
                'section_bg_color' => '',
            ),
            array(
                'home_section_type' => 'recommended-posts',
                'section_title' => esc_html__('Recommended Posts', 'infinity-news'),
                'post_category' => '',
                'section_ed' => 'yes',
                'advertise_link' => 'no',
                'ed_title_control' => 'yes',
                'ed_relevant_cat' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
            ),
            array(
                'home_section_type' => 'advertise-area',
                'advertise_link' => '',
                'advertise_image' => '',
                'section_ed' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
            ),
            array(
                'home_section_type' => 'mailchimp',
                'section_title' => esc_html__('Subscribe to our Newsletter', 'infinity-news'),
                'mailchimp_shortcode' => '',
                'mailchimp_image' => '',
                'mailchimp_description' => '',
                'section_ed' => 'no',
                'mailchimp_text_color' => '#000',
                'section_bg_image' => '',
                'section_bg_color' => '',
            ),
            array(
                'home_section_type' => 'video',
                'section_title' => esc_html__('Banner Block videos', 'infinity-news'),
                'section_ed' => 'yes',
                'post_category' => '',
                'ed_title_control' => 'yes',
                'ed_relevant_cat' => 'no',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'post_category_post_num' => -1,
            ),
            array(
                'home_section_type' => 'banner-block-tiles',
                'section_title' => esc_html__('Banner Block tiles', 'infinity-news'),
                'section_ed' => 'yes',
                'post_category' => '',
                'ed_title_control' => 'yes',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'banner_tiles_layout' => 'layout-1',
                'ed_relevant_cat' => 'no',
                'ed_excerpt_content' => 'no',
            ),
            array(
                'home_section_type' => 'slide',
                'section_title' => esc_html__('Popular This Week', 'infinity-news'),
                'section_ed' => 'yes',
                'post_category' => '',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'slide_layout' => 'slide-layout',
                'ed_relevant_cat' => 'no',
                'slide_full_width' => 'no',
                'ed_instagram_arrow' => 'no',
                'ed_instagram_dot' => 'no',
                'ed_instagram_autoplay' => 'yes',
                'ed_excerpt_content'   => 'yes',
            ),
            array(
                'home_section_type' => 'tab',
                'section_title' => esc_html__('News With Tab', 'infinity-news'),
                'section_ed' => 'yes',
                'post_category' => '',
                'section_bg_image' => '',
                'section_bg_color' => '',
                'banner_tiles_layout' => 'slide-layout',
                'ed_relevant_cat' => 'no',
                'ed_title_control' => 'yes',
                'tab_no_of_posts' => 5,
                'tab_enable_desc' => 'yes',
                'tab_image_size' => 'medium',
                'excerpt_length' => 10,
                'tab_no_of_recent_posts' => 5,
                'tab_no_of_comments_posts' => 5,
            ),
        );

        // Theme Options
        $defaults['ed_top_header_current_date'] = 1;
        $defaults['ed_mid_header_search'] = 1;
        $defaults['breadcrumb_layout'] = 'simple';
        $defaults['pagination_layout'] = 'numeric';
        $defaults['ed_preloader'] = 1;
        $defaults['header_logo_position'] = 'left';
        $defaults['ed_top_header_social_icon'] = 1;
        $defaults['ed_display_mode'] = 1;
        $defaults['ed_cat_color_setting'] = 1;
        $defaults['infinity_news_primary_color'] = '#479fc6';
        $defaults['infinity_news_secondary_color'] = '#f75454';
        $defaults['infinity_news_breadcrumb_bg_color'] = '#133b5a';
        $defaults['infinity_news_breadcrumb_text_color'] = '#fff';
        $defaults['infinity_news_like_button_thumb_color'] = '#fff';
        $defaults['infinity_news_like_count_bg_color'] = '#FFEB3B';
        $defaults['infinity_news_like_count_text_color'] = '#000000';

        // Single Posts Option.
        $defaults['ed_related_post'] = 1;
        $defaults['related_post_title'] = esc_html__('Related Post', 'infinity-news');

        // Layout Options.
        $defaults['global_sidebar_layout'] = 'right-sidebar';
        $defaults['infinity_news_archive_layout'] = 'archive-layout-1';
        $defaults['ed_aos_animation'] = 1;
        $defaults['ed_sticky_sidebar'] = 1;

        // Footer Options.
        $defaults['footer_column_layout'] = 3;
        $defaults['ed_ticker_post'] = 1;
        $defaults['ticker_posts_per_page'] = 12;
        $defaults['ed_ticker_post_arrow'] = 1;
        $defaults['ed_ticker_post_dots'] = '';
        $defaults['ed_ticker_post_autoplay'] = 1;
        $defaults['ed_footer_social_icon'] = 1;
        $defaults['ed_footer_search'] = 1;
        $defaults['footer_copyright_text'] = esc_html__('Copyright All rights reserved', 'infinity-news');
        $defaults['ed_footer_credit_link'] = 1;

        // Booster Extensions Opotions
        $defaults['ed_social_icon'] = 1;
        $defaults['ed_like_dislike'] = 1;
        $defaults['ed_social_share_on_single_page'] = 1;
        $defaults['ed_social_share_on_archive_page'] = 1;
        $defaults['ed_like_dislike_archive'] = 1;
        $defaults['ed_floating_next_previous_nav'] = 1;

        // Woocommerce.
        $defaults['product_sidebar_layout'] = 'no-sidebar';

        // Typography.
        $defaults['twp_primary_font'] = 'Roboto';
        $defaults['twp_primary_font_weight'] = '300';
        $defaults['twp_secondary_font'] = 'Fira Sans';
        $defaults['twp_secondary_font_weight'] = '400';

        $defaults['twp_full_font_size'] = '32';
        $defaults['twp_title_font_size_large'] = '22';
        $defaults['twp_title_font_size_big'] = '18';
        $defaults['twp_title_font_size_medium'] = '16';
        $defaults['twp_title_font_size_small'] = '14';

        // Newsletter
        $defaults['ed_mailchimp_newsletter'] = '';
        $defaults['ed_mailchimp_newsletter_home_only'] = '';
        $defaults['ed_mailchimp_newsletter_first_loading_only'] = '';
        $defaults['twp_newsletter_title'] = esc_html__('Sign Up to Our Newsletter', 'infinity-news');
        $defaults['twp_newsletter_desc'] = esc_html__('Get notified about exclusive offers every week!', 'infinity-news');

        // Open Graph
        $defaults['twp_open_graph_title'] = get_bloginfo('name');
        $defaults['twp_open_graph_desc'] = get_bloginfo('description');
        $defaults['twp_open_graph_url'] = home_url();
        $defaults['twp_open_graph_locole'] = 'en_US';

        // Twitter Summary
        $defaults['twp_twitter_summary_title'] = get_bloginfo('name');
        $defaults['twp_twitter_summary_desc'] = get_bloginfo('description');
        $defaults['twp_twitter_summary_url'] = home_url();
        $defaults['twp_twitter_summary_locole'] = 'en_US';

        // Pass through filter.
        $defaults = apply_filters('infinity_news_filter_default_theme_options', $defaults);

        return $defaults;

    }

endif;
