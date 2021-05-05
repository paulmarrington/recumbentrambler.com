<?php
/**
* Top Header Options.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Header Advertise Area Section.
$wp_customize->add_section( 'top_header_header_bar',
	array(
	'title'      => esc_html__( 'Top Header Settings', 'infinity-news' ),
	'priority'   => 20,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Enable Disable Current Date.
$wp_customize->add_setting('ed_top_header_current_date',
    array(
        'default' => $default['ed_top_header_current_date'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_top_header_current_date',
    array(
        'label' => esc_html__('Enable Current Date', 'infinity-news'),
        'section' => 'top_header_header_bar',
        'type' => 'checkbox',
        'priority' => 25,
    )
);

// Enable Disable Search.
$wp_customize->add_setting('ed_top_header_social_icon',
    array(
        'default' => $default['ed_top_header_social_icon'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_top_header_social_icon',
    array(
        'label' => esc_html__('Enable Social Icons', 'infinity-news'),
        'section' => 'top_header_header_bar',
        'type' => 'checkbox',
        'priority' => 25,
    )
);

// Enable Disable Search.
$wp_customize->add_setting('ed_mid_header_search',
    array(
        'default' => $default['ed_mid_header_search'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_mid_header_search',
    array(
        'label' => esc_html__('Enable Search', 'infinity-news'),
        'section' => 'top_header_header_bar',
        'type' => 'checkbox',
        'priority' => 30,
    )
);