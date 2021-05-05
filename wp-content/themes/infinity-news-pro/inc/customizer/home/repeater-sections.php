<?php
/**
* Sections Repeater Options.
*
* @package Infinity News
*/

$infinity_news_post_category_list = infinity_news_post_category_list();
$default = infinity_news_get_default_theme_options();
$home_sections = array(
        
        'grid-posts' => esc_html__('Banner Block 1','infinity-news'),
        'grid-posts-2' => esc_html__('Banner Block 2','infinity-news'),
        'carousel-posts' => esc_html__('Carousel Block','infinity-news'),
        'jumbotron-block' => esc_html__('Jumbotron Block','infinity-news'),
        'multiple-category-posts' => esc_html__('Multiple Category Block','infinity-news'),
        'latest-post' => esc_html__('Latest Blog Block','infinity-news'),
        'recommended-posts' => esc_html__('Recommended Block','infinity-news'),
        'advertise-area' => esc_html__('Advertisement Block','infinity-news'),
        'mailchimp' => esc_html__('Mailclimp Block','infinity-news'),
        'video' => esc_html__('Video Posts Block','infinity-news'),
        'banner-block-tiles' => esc_html__('Banner Block tiles','infinity-news'),
        'slide' => esc_html__('Slide Block','infinity-news'),
        'tab' => esc_html__('Tab Block','infinity-news'),
        'seperator' => esc_html__('Separator Block','infinity-news'),
    );

$home_sidebar = array(
            'right-sidebar' => esc_html__( 'Right Sidebar', 'infinity-news' ),
            'left-sidebar'  => esc_html__( 'Left Sidebar', 'infinity-news' ),
            'no-sidebar'    => esc_html__( 'No Sidebar', 'infinity-news' ),
            );

$tab_widget_image_size = array(
            'thumbnail' => esc_html__( 'Thumbnail', 'infinity-news' ),
            'medium'  => esc_html__( 'Medium', 'infinity-news' ),
            'large'    => esc_html__( 'Large', 'infinity-news' ),
            'full'    => esc_html__( 'Full', 'infinity-news' ),
            );

$banner_tiles_layout = array(
            'layout-1' => esc_html__( 'Layout 1', 'infinity-news' ),
            'layout-2'  => esc_html__( 'Layout 2', 'infinity-news' ),
            'layout-3'    => esc_html__( 'Layout 3', 'infinity-news' ),
            );

$slide_block_layout = array(
            'slide-layout' => esc_html__( 'Slide Layout', 'infinity-news' ),
            'carousel-layout'  => esc_html__( 'Carousel Layout', 'infinity-news' ),
            );

$home_layout = array(
            'index-layout-1' => esc_html__( 'Grid Layout', 'infinity-news' ),
            'index-layout-2'  => esc_html__( 'Full Width Layout', 'infinity-news' ),
            );

// Slider Section.
$wp_customize->add_section( 'home_sections_repeater',
	array(
	'title'      => esc_html__( 'Homepage Content', 'infinity-news' ),
	'priority'   => 150,
	'capability' => 'edit_theme_options',
	)
);


// Recommended Posts Enable Disable.
$wp_customize->add_setting( 'twp_infinity_news_home_sections_55', array(
    'sanitize_callback' => 'infinity_news_sanitize_repeater',
    'default' => json_encode( $default['twp_infinity_news_home_sections'] ),
));

$wp_customize->add_control(  new Infinity_News_Repeater_Controler( $wp_customize, 'twp_infinity_news_home_sections_55', 
    array(
        'section' => 'home_sections_repeater',
        'settings' => 'twp_infinity_news_home_sections_55',
        'infinity_news_box_label' => esc_html__('New Section','infinity-news'),
        'infinity_news_box_add_control' => esc_html__('Add New Section','infinity-news'),
    ),
        array(
            'section_ed' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Section', 'infinity-news' ),
                'class'       => 'home-section-ed'
            ),
            'home_section_type' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Section Type', 'infinity-news' ),
                'options'     => $home_sections,
                'class'       => 'home-section-type'
            ),
            'slider_ed' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Slider', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'slider_category' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Slider Category', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list,
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'slider_autoplay' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Autoplay', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'slider_dots' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Dots', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'slider_arrows' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Arrows', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
             'sidebar_layout' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Sidebar Layout', 'infinity-news' ),
                'options'     => $home_sidebar,
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'latest_post_layout' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Latest Posts Layout', 'infinity-news' ),
                'options'     => $home_layout,
                'class'       => 'home-repeater-fields-hs latest-post-fields'
            ),
            'section_title' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Section Title', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields carousel-posts-fields grid-posts-fields grid-posts-2-fields recommended-posts-fields mailchimp-fields video-fields banner-block-tiles-fields slide-fields tab-fields'
            ),
            'section_vertical' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Recent Post Title', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'post_category_1' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Post Category One', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list,
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields grid-posts-2-fields multiple-category-posts-fields'
            ),
            'post_category_post_num_1' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-2-fields jumbotron-block-fields y',
            ),
            'block_1_title' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Block One Title', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs multiple-category-posts-fields'
            ),
            'post_category_2' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Post Category Two', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list,
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields grid-posts-2-fields multiple-category-posts-fields'
            ),
            'post_category_post_num_2' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-2-fields',
            ),
            'block_2_title' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Block Two Title', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs multiple-category-posts-fields'
            ),
            'post_category' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Post Category', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list,
                'class'       => 'home-repeater-fields-hs carousel-posts-fields grid-posts-fields recommended-posts-fields video-fields banner-block-tiles-fields slide-fields tab-fields'
            ),
            'post_category_post_num' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields carousel-posts-fields video-fields',
            ),
            'post_category_3' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Post Category Three', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list,
                'class'       => 'home-repeater-fields-hs multiple-category-posts-fields'
            ),
            'block_3_title' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Block Three Title', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs multiple-category-posts-fields'
            ),
            'post_category_post_num_3' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-2-fields multiple-category-posts-fields',
            ),
            'switch_left_right' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Switch Left & Right Position', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'ed_arrows_carousel' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Arrows', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs carousel-posts-fields grid-posts-2-fields jumbotron-block-fields'
            ),
            'ed_dots_carousel' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Dot', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs carousel-posts-fields grid-posts-2-fields'
            ),
            'ed_autoplay_carousel' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Autoplay', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs carousel-posts-fields grid-posts-2-fields jumbotron-block-fields'
            ),
            'ed_title_control' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Title Control', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields recommended-posts-fields multiple-category-posts-fields video-fields banner-block-tiles-fields  tab-fields'
            ),
            'ed_relevant_cat' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Show Relevant Category Only', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields carousel-posts-fields grid-posts-fields grid-posts-2-fields recommended-posts-fields multiple-category-posts-fields video-fields banner-block-tiles-fields slide-fields  tab-fields'
            ),
            'ed_excerpt_content' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Excerpt Content', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs multiple-category-posts-fields banner-block-tiles-fields slide-fields'
            ),
            'add_image_1' => array(
                'type'        => 'upload',
                'label'       => esc_html__( 'Advertise Image 1', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'add_image_1_link' => array(
                'type'        => 'link',
                'label'       => esc_html__( 'Advertise Image Link 1', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'add_image_2' => array(
                'type'        => 'upload',
                'label'       => esc_html__( 'Advertise Image 2', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'add_image_2_link' => array(
                'type'        => 'link',
                'label'       => esc_html__( 'Advertise Image Link 2', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs jumbotron-block-fields'
            ),
            'ed_carouser_overlay_layout' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Overlay Layout', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs carousel-posts-fields'
            ),
            'advertise_image' => array(
                'type'        => 'upload',
                'label'       => esc_html__( 'Advertise Image', 'infinity-news' ),
                'description' => esc_html__( 'Recommended Image Size is 970x250 PX.', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs advertise-area-fields'
            ),
            'advertise_link' => array(
                'type'        => 'link',
                'label'       => esc_html__( 'Advertise Image Link', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs advertise-area-fields'
            ),
            'advertise_script' => array(
                'type'        => 'textarea',
                'label'       => esc_html__( 'Advertise Script', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs advertise-area-fields'
            ),
            'slide_layout' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Layout', 'infinity-news' ),
                'options'     => $slide_block_layout,
                'class'       => 'home-repeater-fields-hs slide-fields'
            ),
            'slide_full_width' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Full Width', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs slide-fields'
            ),
            'ed_instagram_autoplay' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Slide Autoplay', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs slide-fields'
            ),
            'ed_instagram_dot' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Slide Dots', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs slide-fields'
            ),
            'ed_instagram_arrow' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Slide Arrow', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs slide-fields'
            ),
            'mailchimp_shortcode' => array(
                'type'        => 'textarea',
                'label'       => esc_html__( 'Mailchimp Shortcode', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs mailchimp-fields'
            ),
            'mailchimp_description' => array(
                'type'        => 'textarea',
                'label'       => esc_html__( 'Mailchimp Description', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs mailchimp-fields'
            ),
            'mailchimp_image' => array(
                'type'        => 'upload',
                'label'       => esc_html__( 'Mailchimp Image', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs mailchimp-fields'
            ),
            'mailchimp_text_color' => array(
                'type'        => 'colorpicker',
                'label'       => esc_html__( 'Text Color', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs mailchimp-fields'
            ),
            'banner_tiles_layout' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Layout', 'infinity-news' ),
                'options'     => $banner_tiles_layout,
                'class'       => 'home-repeater-fields-hs banner-block-tiles-fields tab-fields',
                'default'     => 'layout-1',
            ),
            'seperator_text_popular' => array(
                'type'        => 'seperator',
                'label'       =>'',
                'seperator_text'       => esc_html__( 'Popular', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields'
            ),
            'tab_no_of_posts' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Popular Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'        => 5,
            ),
            'tab_enable_desc' => array(
                'type'        => 'checkbox',
                'label'       => esc_html__( 'Enable Description:', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'        => 'yes',
            ),
            'tab_image_size' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Select Image Size Featured Post:', 'infinity-news' ),
                'options'     => $tab_widget_image_size,
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'        => 'medium',
            ),
            'excerpt_length' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'Excerpt Length:', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'     => 10,
            ),
            'seperator_text_recent' => array(
                'type'        => 'seperator',
                'label'       =>'',
                'seperator_text'       => esc_html__( 'Recent', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields'
            ),
            'tab_no_of_recent_posts' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Posts', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'     => 5,
            ),
            'seperator_text_comment' => array(
                'type'        => 'seperator',
                'label'       =>'',
                'seperator_text'       => esc_html__( 'Comment', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields'
            ),
            'tab_no_of_comments_posts' => array(
                'type'        => 'text',
                'label'       => esc_html__( 'No. Of Comment', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs tab-fields',
                'default'     => 5,
            ),
            'first_block_bg_color' => array(
                'type'        => 'colorpicker',
                'label'       => esc_html__( 'First Posts Highlight Color', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields grid-posts-2-fields'
            ),
            'first_block_text_color' => array(
                'type'        => 'colorpicker',
                'label'       => esc_html__( 'First Posts Texts Color', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields grid-posts-2-fields'
            ),
            'section_bg_image' => array(
                'type'        => 'upload',
                'label'       => esc_html__( 'Background Image', 'infinity-news' ),
                'description' => esc_html__( 'Recommended Image Size is 970x250 PX.', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields grid-posts-2-fields carousel-posts-fields multiple-category-posts-fields jumbotron-block-fields recommended-posts-fields mailchimp-fields video-fields banner-block-tiles-fields slide-fields  tab-fields'
            ),

            'section_bg_color' => array(
                'type'        => 'colorpicker',
                'label'       => esc_html__( 'Background Color', 'infinity-news' ),
                'class'       => 'home-repeater-fields-hs grid-posts-fields grid-posts-2-fields carousel-posts-fields multiple-category-posts-fields jumbotron-block-fields recommended-posts-fields mailchimp-fields video-fields banner-block-tiles-fields slide-fields  tab-fields seperator-fields'
            ),
            
    )
));

// Info.
$wp_customize->add_setting(
    'infinity_news_notiece_info',
    array(
        'default'           => '',
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'sanitize_text_field'
    )
);
$wp_customize->add_control(
    new Infinity_News_Info_Notiece_Control( 
        $wp_customize,
        'infinity_news_notiece_info',
        array(
            'settings' => 'infinity_news_notiece_info',
            'section'       => 'home_sections_repeater',
            'label'         => esc_html__( 'Info', 'infinity-news' ),
        )
    )
);