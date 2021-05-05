<?php
/**
* Theme Options.
*
* @package Blog Prime
*/

$default = infinity_news_get_default_theme_options();
$google_fonts = infinity_news_google_fonts();
$google_fonts_array = infinity_news_font_array();
$variants = array();

$twp_primary_font = get_theme_mod( 'twp_primary_font',$default['twp_primary_font'] );
$twp_primary_font_key = array_search( $twp_primary_font, array_column( $google_fonts_array, 'family') );
$twp_primary_font_variants = $google_fonts_array[$twp_primary_font_key]['variants'];

$twp_secondary_font = get_theme_mod( 'twp_secondary_font',$default['twp_secondary_font'] );
$twp_secondary_font_key = array_search( $twp_secondary_font, array_column( $google_fonts_array, 'family') );
$twp_secondary_font_variants = $google_fonts_array[$twp_secondary_font_key]['variants'];

// Typography Panel.
$wp_customize->add_panel( 'twp_typography_panel',
	array(
		'title'      => esc_html__( 'Typography', 'infinity-news' ),
		'priority'   => 50,
		'capability' => 'edit_theme_options',
	)
);

// Primary Font Section.
$wp_customize->add_section( 'twp_primary_typography',
	array(
	'title'      => esc_html__( 'Primary Font', 'infinity-news' ),
	'priority'   => 50,
	'capability' => 'edit_theme_options',
	'panel'		 => 'twp_typography_panel',
	)
);

// Primary Font.
$wp_customize->add_setting( 'twp_primary_font',
	array(
	'default'           => $default['twp_primary_font'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_primary_font',
	array(
	'label'       => esc_html__( 'Primary Font', 'infinity-news' ),
	'section'     => 'twp_primary_typography',
	'type'        => 'select',
	'choices'     => $google_fonts,
	'priority'    => 10,
	)
);

// Primary Font Weight.
$wp_customize->add_setting( 'twp_primary_font_weight',
	array(
	'default'           => $default['twp_primary_font_weight'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_primary_font_weight',
	array(
	'label'       => esc_html__( 'Primary Font Weight', 'infinity-news' ),
	'section'     => 'twp_primary_typography',
	'type'        => 'select',
	'choices'     => $twp_primary_font_variants,
	'priority'    => 20,
	)
);

// Secondary Font Section.
$wp_customize->add_section( 'twp_secondary_typography',
	array(
	'title'      => esc_html__( 'Secondary Font', 'infinity-news' ),
	'priority'   => 60,
	'capability' => 'edit_theme_options',
	'panel'		 => 'twp_typography_panel',
	)
);

// Secondary Font.
$wp_customize->add_setting( 'twp_secondary_font',
	array(
	'default'           => $default['twp_secondary_font'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_secondary_font',
	array(
	'label'       => esc_html__( 'Secondary Font', 'infinity-news' ),
	'section'     => 'twp_secondary_typography',
	'type'        => 'select',
	'choices'     => $google_fonts,
	'priority'    => 10,
	)
);

// Secondary Font Weight.
$wp_customize->add_setting( 'twp_secondary_font_weight',
	array(
	'default'           => $default['twp_secondary_font_weight'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_secondary_font_weight',
	array(
	'label'       => esc_html__( 'Secondary Font Weight', 'infinity-news' ),
	'section'     => 'twp_secondary_typography',
	'type'        => 'select',
	'choices'     => $twp_secondary_font_variants,
	'priority'    => 20,
	)
);

// Font Size Section.
$wp_customize->add_section( 'twp_font_size_section',
	array(
	'title'      => esc_html__( 'Font Size', 'infinity-news' ),
	'description'      => esc_html__( 'All Font Size are on Pixels (PX).', 'infinity-news' ),
	'priority'   => 70,
	'capability' => 'edit_theme_options',
	'panel'		 => 'twp_typography_panel',
	)
);

// General Font.
$wp_customize->add_setting( 'twp_full_font_size',
	array(
	'default'           => $default['twp_full_font_size'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control( 'twp_full_font_size',
	array(
	'label'       => esc_html__( 'Title Font Size ( Full )', 'infinity-news' ),
	'section'     => 'twp_font_size_section',
	'type'        => 'text',
	'priority'    => 10,
	)
);

// Title Font Size Big.
$wp_customize->add_setting( 'twp_title_font_size_large',
	array(
	'default'           => $default['twp_title_font_size_large'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control( 'twp_title_font_size_large',
	array(
	'label'       => esc_html__( 'Title Font Size ( Large )', 'infinity-news' ),
	'section'     => 'twp_font_size_section',
	'type'        => 'text',
	'priority'    => 10,
	)
);

// Title Font Size Medium.
$wp_customize->add_setting( 'twp_title_font_size_big',
	array(
	'default'           => $default['twp_title_font_size_big'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control( 'twp_title_font_size_big',
	array(
	'label'       => esc_html__( 'Title Font Size ( Big )', 'infinity-news' ),
	'section'     => 'twp_font_size_section',
	'type'        => 'text',
	'priority'    => 10,
	)
);

// Title Font Size Medium.
$wp_customize->add_setting( 'twp_title_font_size_medium',
	array(
	'default'           => $default['twp_title_font_size_medium'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control( 'twp_title_font_size_medium',
	array(
	'label'       => esc_html__( 'Title Font Size (Medium)', 'infinity-news' ),
	'section'     => 'twp_font_size_section',
	'type'        => 'text',
	'priority'    => 10,
	)
);

// Title Font Size Medium.
$wp_customize->add_setting( 'twp_title_font_size_small',
	array(
	'default'           => $default['twp_title_font_size_small'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'absint',
	)
);
$wp_customize->add_control( 'twp_title_font_size_small',
	array(
	'label'       => esc_html__( 'Title Font Size (Small)', 'infinity-news' ),
	'section'     => 'twp_font_size_section',
	'type'        => 'text',
	'priority'    => 10,
	)
);