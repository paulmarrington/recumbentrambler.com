<?php
/**
* Breadcrumb.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Breadcrumb Section.
$wp_customize->add_section( 'breadcrumb_section',
	array(
	'title'      => esc_html__( 'Breadcrumb Settings', 'infinity-news' ),
	'priority'   => 50,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Breadcrumb Layout.
$wp_customize->add_setting( 'breadcrumb_layout',
	array(
	'default'           => $default['breadcrumb_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'breadcrumb_layout',
	array(
	'label'       => esc_html__( 'Breadcrumb Layout', 'infinity-news' ),
	'description' => sprintf( esc_html__( 'Advanced: Requires %1$sBreadcrumb NavXT%2$s plugin.', 'infinity-news' ), '<a href="https://wordpress.org/plugins/breadcrumb-navxt/" target="_blank">','</a>' ),
	'section'     => 'breadcrumb_section',
	'type'        => 'select',
	'choices'               => array(
		'disable' => esc_html__( 'Disabled', 'infinity-news' ),
		'simple' => esc_html__( 'Simple', 'infinity-news' ),
		'advanced' => esc_html__( 'Advanced', 'infinity-news' ),
	    ),
	'priority'    => 10,
	)
);

$wp_customize->add_setting(
    'infinity_news_breadcrumb_bg_color',
    array(
        'default'           => $default['infinity_news_breadcrumb_bg_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_breadcrumb_bg_color',
        array(
            'settings'      => 'infinity_news_breadcrumb_bg_color',
            'section'       => 'breadcrumb_section',
            'label'         => esc_html__(' Background Color ', 'infinity-news' ),
        )
    )
);

$wp_customize->add_setting(
    'infinity_news_breadcrumb_text_color',
    array(
        'default'           => $default['infinity_news_breadcrumb_text_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_breadcrumb_text_color',
        array(
            'settings'      => 'infinity_news_breadcrumb_text_color',
            'section'       => 'breadcrumb_section',
            'label'         => esc_html__(' Text Color ', 'infinity-news' ),
        )
    )
);