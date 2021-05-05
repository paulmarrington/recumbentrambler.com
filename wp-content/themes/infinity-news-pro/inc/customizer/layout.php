<?php
/**
* Layouts Settings.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Layout Section.
$wp_customize->add_section( 'layout_setting',
	array(
	'title'      => esc_html__( 'Layout Settings', 'infinity-news' ),
	'priority'   => 60,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Global Sidebar Layout.
$wp_customize->add_setting( 'global_sidebar_layout',
	array(
	'default'           => $default['global_sidebar_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'global_sidebar_layout',
	array(
	'label'       => esc_html__( 'Global Sidebar Layout', 'infinity-news' ),
	'section'     => 'layout_setting',
	'type'        => 'select',
	'choices'     => array(
		'right-sidebar' => esc_html__( 'Right Sidebar', 'infinity-news' ),
		'left-sidebar'  => esc_html__( 'Left Sidebar', 'infinity-news' ),
		'no-sidebar'    => esc_html__( 'No Sidebar', 'infinity-news' ),
	    ),
	)
);

// Archive Layout.
$wp_customize->add_setting(
    'infinity_news_archive_layout',
    array(
        'default' 			=> $default['infinity_news_archive_layout'],
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_select'
    )
);
$wp_customize->add_control(
    new Infinity_News_Custom_Radio_Image_Control( 
        $wp_customize,
        'infinity_news_archive_layout',
        array(
            'settings'      => 'infinity_news_archive_layout',
            'section'       => 'layout_setting',
            'label'         => esc_html__( 'Archive Layout', 'infinity-news' ),
            'choices'       => array(
                'archive-layout-1'  => get_template_directory_uri() . '/assets/images/Layout-style-1.png',
                'archive-layout-2'  => get_template_directory_uri() . '/assets/images/Layout-style-2.png',
            )
        )
    )
);

// Enable Disable Appearing Animation
$wp_customize->add_setting('ed_aos_animation',
    array(
        'default' => $default['ed_aos_animation'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_aos_animation',
    array(
        'label' => esc_html__( 'Enable AOS Animation', 'infinity-news' ),
        'section' => 'layout_setting',
        'type' => 'checkbox',
    )
);

// Enable Disable Appearing Animation
$wp_customize->add_setting('ed_sticky_sidebar',
    array(
        'default' => $default['ed_sticky_sidebar'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_sticky_sidebar',
    array(
        'label' => esc_html__( 'Enable Sticky Sidebar', 'infinity-news' ),
        'section' => 'layout_setting',
        'type' => 'checkbox',
    )
);