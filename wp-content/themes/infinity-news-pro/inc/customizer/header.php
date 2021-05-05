<?php
/**
* Header Options.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Dark Mode Logo
$wp_customize->add_setting('twp_dark_mod_logo',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'twp_dark_mod_logo',
    	array(
        	'label'      => esc_html__( 'Dark Mode Logo', 'infinity-news' ),
           	'section'    => 'title_tagline',
           	'priority' => 8,
       	)
   	)
);

// Logo Position Layout.
$wp_customize->add_setting( 'header_logo_position',
	array(
	'default'           => $default['header_logo_position'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'header_logo_position',
	array(
	'label'       => esc_html__( 'Logo Position', 'infinity-news' ),
	'section'     => 'title_tagline',
	'type'        => 'select',
	'choices'               => array(
		'left' => esc_html__( 'Left', 'infinity-news' ),
		'center' => esc_html__( 'Center', 'infinity-news' ),
	    ),
	'priority'    => 10,
	)
);

// Header Advertise Area Section.
$wp_customize->add_section( 'header_mid_header_bar',
	array(
	'title'      => esc_html__( 'Header Settings', 'infinity-news' ),
	'priority'   => 20,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Header Advertise Image
$wp_customize->add_setting('header_advertise_image',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'header_advertise_image',
    	array(
        	'label'      => esc_html__( 'Header Advertise Image', 'infinity-news' ),
           	'section'    => 'header_mid_header_bar',
           	'priority' => 10,
       	)
   	)
);

// Header Image Ad Link.
$wp_customize->add_setting( 'header_advertise_link',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'esc_url_raw',
	)
);
$wp_customize->add_control( 'header_advertise_link',
	array(
	'label'    => esc_html__( 'Header Advertise Image Link', 'infinity-news' ),
	'section'  => 'header_mid_header_bar',
	'type'     => 'text',
	'priority' => 20,
	)
);

// Advertise Script
$wp_customize->add_setting( 'header_advertise_script',
  array(
  'default'           => '',
  'capability'        => 'edit_theme_options',
  'sanitize_callback' => '',
  )
);
$wp_customize->add_control( 'header_advertise_script',
  array(
  'label'    => esc_html__( 'Header Advertise Script', 'infinity-news' ),
  'section'  => 'header_mid_header_bar',
  'type'     => 'textarea',
  'priority' => 20,
  )
);

// Ticker Post Enable Disable.
$wp_customize->add_setting('ed_display_mode',
    array(
        'default' => $default['ed_display_mode'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_display_mode',
    array(
        'label' => esc_html__('Enable Display Mode Switcher', 'infinity-news'),
        'section' => 'header_mid_header_bar',
        'type' => 'checkbox',
        'priority' => 1,
    )
);