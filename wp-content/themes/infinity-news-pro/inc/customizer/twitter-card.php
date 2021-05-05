<?php
/**
* Twitter options.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();
// Webmaster Panel.
$wp_customize->add_panel( 'infinity_news_twitter',
	array(
		'title'      => esc_html__( 'Twitter Summary Card', 'infinity-news' ),
		'priority'   => 200,
		'capability' => 'edit_theme_options',
	)
);

$wp_customize->add_section( 'twp_twitter_summary_ed_sec', array(
        'title'    	=> __( 'Twitter Summary Enable Disable', 'infinity-news' ),
        'panel'		=> 'infinity_news_twitter',
        
) );

// Enable Disable Twitter Summary.
$wp_customize->add_setting('twp_ed_twitter_summary',
    array(
        'default' => '',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('twp_ed_twitter_summary',
    array(
        'label' => esc_html__('Enable Twitter Summary', 'infinity-news'),
        'description' => esc_html__('Add meta on head for Twitter Summary.', 'infinity-news'),
        'section' => 'twp_twitter_summary_ed_sec',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

$wp_customize->add_section( 'twp_twitter_summary_home_sec', array(
        'title'    	=> __( 'Homepage Setting', 'infinity-news' ),
        'panel'		=> 'infinity_news_twitter',
        
) );


// Twitter Summary Title.
$wp_customize->add_setting( 'twp_twitter_summary_title',
	array(
	'default'           => $default['twp_twitter_summary_title'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_twitter_summary_title',
	array(
	'label'    => esc_html__( 'Title', 'infinity-news' ),
	'section'  => 'twp_twitter_summary_home_sec',
	'type'     => 'text',
	)
);

// Twitter Summary Description.
$wp_customize->add_setting( 'twp_twitter_summary_desc',
	array(
	'default'           => $default['twp_twitter_summary_desc'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_twitter_summary_desc',
	array(
	'label'    => esc_html__( 'Description', 'infinity-news' ),
	'section'  => 'twp_twitter_summary_home_sec',
	'type'     => 'text',
	)
);

// Twitter Summary Description.
$wp_customize->add_setting( 'twp_twitter_summary_user',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_twitter_summary_user',
	array(
	'label'    => esc_html__( 'Username', 'infinity-news' ),
	'section'  => 'twp_twitter_summary_home_sec',
	'type'     => 'text',
	)
);

// Twitter Summary Description.
$wp_customize->add_setting( 'twp_twitter_summary_site_type',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'twp_twitter_summary_site_type',
	array(
	'label'    => esc_html__( 'Twitter Card', 'infinity-news' ),
	'section'  => 'twp_twitter_summary_home_sec',
	'type'     => 'select',
	'choices'	=> array(
			'' => esc_html__('--select--','infinity-news'),
			'summary' => esc_html__('summary','infinity-news'),
			'summary_large_image' => esc_html__('Summary Large Image','infinity-news'),
			'app' => esc_html__('APP','infinity-news'),
			'player' => esc_html__('Player','infinity-news'),
			'lead_generation' => esc_html__('Lead Generation','infinity-news'),
			
		),
	)
);


// Header Advertise Image
$wp_customize->add_setting('twp_twitter_summary_home_default_image',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'twp_twitter_summary_home_default_image',
    	array(
        	'label'      => esc_html__( 'Image for Home and Default Image.', 'infinity-news' ),
           	'section'    => 'twp_twitter_summary_home_sec',
           	'priority' => 10,
       	)
   	)
);


$wp_customize->add_section( 'twp_twitter_summary_custom_meta_sec', array(
        'title'    	=> __( 'Custom Meta', 'infinity-news' ),
        'panel'		=> 'infinity_news_twitter',
        
) );

// Twitter Summary Suctom Meta.
$wp_customize->add_setting( 'twp_twitter_summary_custom_meta',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_meta_sanitize',
	)
);
$wp_customize->add_control( 'twp_twitter_summary_custom_meta',
	array(
	'label'    => esc_html__( 'Custom Meta', 'infinity-news' ),
	'description'    => esc_html__( 'For example: <meta name="twitter:card" content="summary" />', 'infinity-news' ),
	'section'  => 'twp_twitter_summary_custom_meta_sec',
	'type'     => 'textarea',
	)
);