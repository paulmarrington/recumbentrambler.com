<?php
/**
* Single Post Options.
*
* @package Infinity News
*/

$infinity_news_post_category_list = infinity_news_post_category_list();
$default = infinity_news_get_default_theme_options();

// Single Post Section.
$wp_customize->add_section( 'single_post_setting',
	array(
	'title'      => esc_html__( 'Single Post Settings', 'infinity-news' ),
	'priority'   => 70,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Related Posts Enable Disable.
$wp_customize->add_setting('ed_related_post',
    array(
        'default' => $default['ed_related_post'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_related_post',
    array(
        'label' => esc_html__('Enable Related Posts', 'infinity-news'),
        'section' => 'single_post_setting',
        'type' => 'checkbox',
        'priority' => 10,
    )
);

// Related Posts Section Title.
$wp_customize->add_setting( 'related_post_title',
    array(
    'default'           => $default['related_post_title'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'related_post_title',
    array(
    'label'    => esc_html__( 'Section Title', 'infinity-news' ),
    'section'  => 'single_post_setting',
    'type'     => 'text',
    'priority' => 20,
    )
);

$wp_customize->add_setting('ed_floating_next_previous_nav',
    array(
        'default' => $default['ed_floating_next_previous_nav'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_floating_next_previous_nav',
    array(
        'label' => esc_html__('Enable Hoverable Sidenav Next/Previous Buttons', 'infinity-news'),
        'section' => 'single_post_setting',
        'type' => 'checkbox',
    )
);