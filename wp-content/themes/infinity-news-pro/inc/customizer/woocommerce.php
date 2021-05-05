<?php
/**
* Layouts Settings.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Woocommerce Setting For Theme.
$wp_customize->add_section( 'infinity_news_woocommerce_setting',
	array(
	'title'      => esc_html__( 'Theme Settings', 'infinity-news' ),
	'priority'   => 60,
	'capability' => 'edit_theme_options',
	'panel'      => 'woocommerce',
	)
);

// Product Single Sidebar Layout.
$wp_customize->add_setting( 'product_sidebar_layout',
	array(
	'default'           => $default['product_sidebar_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'product_sidebar_layout',
	array(
	'label'       => esc_html__( 'Single Product Sidebar Layout', 'infinity-news' ),
	'section'     => 'infinity_news_woocommerce_setting',
	'type'        => 'select',
	'choices'     => array(
		'right-sidebar' => esc_html__( 'Right Sidebar', 'infinity-news' ),
		'left-sidebar'  => esc_html__( 'Left Sidebar', 'infinity-news' ),
		'no-sidebar'    => esc_html__( 'No Sidebar', 'infinity-news' ),
	    ),
	)
);
