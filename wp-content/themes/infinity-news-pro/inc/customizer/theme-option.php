<?php
/**
* Theme Options.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Theme Options Panel.
$wp_customize->add_panel( 'theme_option_panel',
	array(
		'title'      => esc_html__( 'Theme Options', 'infinity-news' ),
		'priority'   => 200,
		'capability' => 'edit_theme_options',
	)
);

// Homepage Options Panel.
$wp_customize->add_panel( 'home_page_panel',
	array(
		'title'      => esc_html__( 'Homepage Options', 'infinity-news' ),
		'priority'   => 150,
		'capability' => 'edit_theme_options',
	)
);

// Pagination Section.
$wp_customize->add_section( 'pagination_section',
	array(
	'title'      => esc_html__( 'Pagination Settings', 'infinity-news' ),
	'priority'   => 80,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Pagination Layout.
$wp_customize->add_setting( 'pagination_layout',
	array(
	'default'           => $default['pagination_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'pagination_layout',
	array(
	'label'       => esc_html__( 'Pagination Layout', 'infinity-news' ),
	'section'     => 'pagination_section',
	'type'        => 'select',
	'choices'               => array(
		'classic' => esc_html__( 'Classic(Previous/Next)', 'infinity-news' ),
		'numeric' => esc_html__( 'Numeric', 'infinity-news' ),
	    ),
	'priority'    => 10,
	)
);

// Preloader Section.
$wp_customize->add_section( 'preloader_section',
	array(
	'title'      => esc_html__( 'Preloader Settings', 'infinity-news' ),
	'priority'   => 5,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Enable Disable Preloader.
$wp_customize->add_setting('ed_preloader',
    array(
        'default' => $default['ed_preloader'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_preloader',
    array(
        'label' => esc_html__('Enable Preloader', 'infinity-news'),
        'description' => esc_html__('Enable/Disable Loading Animation.', 'infinity-news'),
        'section' => 'preloader_section',
        'type' => 'checkbox',
        'priority' => 10,
    )
);