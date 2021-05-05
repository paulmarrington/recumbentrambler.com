<?php
/**
* Webmasters options.
*
* @package Infinity News
*/


// Webmaster Panel.
$wp_customize->add_panel( 'infinity_news_webmaster_panel',
	array(
		'title'      => esc_html__( 'Webmasters Tools', 'infinity-news' ),
		'priority'   => 200,
		'capability' => 'edit_theme_options',
	)
);

$wp_customize->add_section( 'twp_webmasters_tools', array(
        'title'    	=> __( 'Addtional Scripts', 'infinity-news' ),
        'panel'		=> 'infinity_news_webmaster_panel',
        
) );

$wp_customize->add_setting( 'twp_header_script', array(
        'type' => 'option',
        'transport'=>'postMessage',
) );
$wp_customize->add_control( new WP_Customize_Code_Editor_Control( $wp_customize, 'header_script', array(
        'label'     => __( 'Before Header Script', 'infinity-news' ),
        'code_type' => 'javascript',
        'settings'  => 'twp_header_script',
        'section'   => 'twp_webmasters_tools',
        
) ) );


$wp_customize->add_setting( 'twp_footer_script', array(
        'type' => 'option',
        'transport'=>'postMessage',

) );
$wp_customize->add_control( new WP_Customize_Code_Editor_Control( $wp_customize, 'footer_script', array(
        'label'     => __( 'Aftere Footer Script', 'infinity-news' ),
        'code_type' => 'javascript',
        'settings'  => 'twp_footer_script',
        'section'   => 'twp_webmasters_tools',
        
) ) );

$wp_customize->add_section( 'twp_site_verification', array(
        'title'    	=> __( 'SIte Verification', 'infinity-news' ),
        'panel'		=> 'infinity_news_webmaster_panel',
        
) );

$wp_customize->add_setting( 'twp_verification_code_google',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	'transport'=>'postMessage',
	)
);
$wp_customize->add_control( 'twp_verification_code_google',
	array(
	'label'    => esc_html__( 'Google Webmaster Tools', 'infinity-news' ),
	'section'  => 'twp_site_verification',
	'type'     => 'text',
	)
);

$wp_customize->add_setting( 'twp_verification_code_bing',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	'transport'=>'postMessage',
	)
);
$wp_customize->add_control( 'twp_verification_code_bing',
	array(
	'label'    => esc_html__( 'Bing Webmaster Tools', 'infinity-news' ),
	'section'  => 'twp_site_verification',
	'type'     => 'text',
	)
);

$wp_customize->add_setting( 'twp_verification_code_pinterest',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	'transport'=>'postMessage',
	)
);
$wp_customize->add_control( 'twp_verification_code_pinterest',
	array(
	'label'    => esc_html__( 'Pinterest Site Verification', 'infinity-news' ),
	'section'  => 'twp_site_verification',
	'type'     => 'text',
	)
);

$wp_customize->add_setting( 'twp_verification_code_alexa',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	'transport'=>'postMessage',
	)
);
$wp_customize->add_control( 'twp_verification_code_alexa',
	array(
	'label'    => esc_html__( 'Alexa Verification ID', 'infinity-news' ),
	'section'  => 'twp_site_verification',
	'type'     => 'text',
	)
);

$wp_customize->add_setting( 'twp_verification_code_yandex',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	'transport'=>'postMessage',
	)
);
$wp_customize->add_control( 'twp_verification_code_yandex',
	array(
	'label'    => esc_html__( 'Yandex Webmaster Tools', 'infinity-news' ),
	'section'  => 'twp_site_verification',
	'type'     => 'text',
	)
);