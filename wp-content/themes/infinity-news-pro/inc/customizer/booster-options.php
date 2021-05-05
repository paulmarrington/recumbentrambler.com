<?php
/**
* Booster Extension Settings.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();
$infinity_news_post_category_list = infinity_news_post_category_list();

// Footer Section.
$wp_customize->add_section( 'twp_booster_settings',
	array(
	'title'      => esc_html__( 'Booster Options', 'infinity-news' ),
	'priority'   => 200,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Enable Disable Social Share.
$wp_customize->add_setting('ed_social_icon',
    array(
        'default' => $default['ed_social_icon'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_social_icon',
    array(
        'label' => esc_html__('Enable Social Share On Home Sections.', 'infinity-news'),
        'section' => 'twp_booster_settings',
        'type' => 'checkbox',
    )
);

// Enable Disable Like Dislike button.
$wp_customize->add_setting('ed_like_dislike',
    array(
        'default' => $default['ed_like_dislike'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_like_dislike',
    array(
        'label' => esc_html__('Enable Like/Dislike On Home Sections.', 'infinity-news'),
        'section' => 'twp_booster_settings',
        'type' => 'checkbox',
    )
);

// Enable Disable Social Share On Single Post.
$wp_customize->add_setting('ed_social_share_on_single_page',
    array(
        'default' => $default['ed_social_share_on_single_page'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_social_share_on_single_page',
    array(
        'label' => esc_html__('Enable Social Share On Single Post.', 'infinity-news'),
        'section' => 'twp_booster_settings',
        'type' => 'checkbox',
    )
);

// Enable Disable Social Share On Archive Page.
$wp_customize->add_setting('ed_social_share_on_archive_page',
    array(
        'default' => $default['ed_social_share_on_archive_page'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_social_share_on_archive_page',
    array(
        'label' => esc_html__('Enable Social Share on Archive Post.', 'infinity-news'),
        'section' => 'twp_booster_settings',
        'type' => 'checkbox',
    )
);

// Enable Disable Like Dislike button.
$wp_customize->add_setting('ed_like_dislike_archive',
    array(
        'default' => $default['ed_like_dislike_archive'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_like_dislike_archive',
    array(
        'label' => esc_html__('Enable Like/Dislike On Archive Page.', 'infinity-news'),
        'section' => 'twp_booster_settings',
        'type' => 'checkbox',
    )
);

$wp_customize->add_setting(
    'infinity_news_like_button_bg_color',
    array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_like_button_bg_color',
        array(
            'settings'      => 'infinity_news_like_button_bg_color',
            'section'       => 'twp_booster_settings',
            'label'         => esc_html__(' Like Background Color ', 'infinity-news' ),
        )
    )
);

$wp_customize->add_setting(
    'infinity_news_like_button_thumb_color',
    array(
        'default'           => $default['infinity_news_like_button_thumb_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_like_button_thumb_color',
        array(
            'settings'      => 'infinity_news_like_button_thumb_color',
            'section'       => 'twp_booster_settings',
            'label'         => esc_html__(' Like Thumbsup Color ', 'infinity-news' ),
        )
    )
);

$wp_customize->add_setting(
    'infinity_news_like_count_bg_color',
    array(
        'default'           => $default['infinity_news_like_count_bg_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_like_count_bg_color',
        array(
            'settings'      => 'infinity_news_like_count_bg_color',
            'section'       => 'twp_booster_settings',
            'label'         => esc_html__(' Like Count Background Color ', 'infinity-news' ),
        )
    )
);

$wp_customize->add_setting(
    'infinity_news_like_count_text_color',
    array(
        'default'           => $default['infinity_news_like_count_text_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_like_count_text_color',
        array(
            'settings'      => 'infinity_news_like_count_text_color',
            'section'       => 'twp_booster_settings',
            'label'         => esc_html__(' Like Count Text Color ', 'infinity-news' ),
        )
    )
);