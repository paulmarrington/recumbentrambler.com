<?php
/**
* Open Graph options.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();
// Webmaster Panel.
$wp_customize->add_panel( 'infinity_news_og_panel',
	array(
		'title'      => esc_html__( 'Open Graph', 'infinity-news' ),
		'priority'   => 200,
		'capability' => 'edit_theme_options',
	)
);

$wp_customize->add_section( 'twp_open_graph_ed_sec', array(
        'title'    	=> __( 'Open Graph Enable Disable', 'infinity-news' ),
        'panel'		=> 'infinity_news_og_panel',
        
) );

// Enable Disable Open Graph.
$wp_customize->add_setting('twp_ed_open_graph',
    array(
        'default' => '',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('twp_ed_open_graph',
    array(
        'label' => esc_html__('Enable Open Graph', 'infinity-news'),
        'description' => esc_html__('Add meta on head for Open Graph.', 'infinity-news'),
        'section' => 'twp_open_graph_ed_sec',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

$wp_customize->add_section( 'twp_open_graph_home_sec', array(
        'title'    	=> __( 'Homepage Setting', 'infinity-news' ),
        'panel'		=> 'infinity_news_og_panel',
        
) );


// Open Graph Title.
$wp_customize->add_setting( 'twp_open_graph_title',
	array(
	'default'           => $default['twp_open_graph_title'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_open_graph_title',
	array(
	'label'    => esc_html__( 'Title', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'text',
	)
);

// Open Graph Description.
$wp_customize->add_setting( 'twp_open_graph_desc',
	array(
	'default'           => $default['twp_open_graph_desc'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_open_graph_desc',
	array(
	'label'    => esc_html__( 'Description', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'text',
	)
);

// Open Graph Description.
$wp_customize->add_setting( 'twp_open_graph_site_name',
	array(
	'default'           => $default['twp_open_graph_title'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_open_graph_site_name',
	array(
	'label'    => esc_html__( 'Sitename', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'textarea',
	)
);

// Open Graph Description.
$wp_customize->add_setting( 'twp_open_graph_site_type',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'twp_open_graph_site_type',
	array(
	'label'    => esc_html__( 'Type', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'select',
	'choices'	=> array(
			'' => esc_html__('--select--','infinity-news'),
			'website' => esc_html__('Website','infinity-news'),
			'video.episode' => esc_html__('video.episode','infinity-news'),
			'music.radio_station' => esc_html__('music.radio_station','infinity-news'),
			'music.song' => esc_html__('music.song','infinity-news'),
			'music.playlist' => esc_html__('music.playlist','infinity-news'),
			'video.movie' => esc_html__('video.movie','infinity-news'),
			'music.album' => esc_html__('music.album','infinity-news'),
			'video.tv_show' => esc_html__('video.tv_show','infinity-news'),
			'article' => esc_html__('Article','infinity-news'),
			'video.other' => esc_html__('video.other','infinity-news'),
			'profile' => esc_html__('Profile','infinity-news'),
			'book' => esc_html__('Book','infinity-news'),

		),
	)
);

// Open Graph URL.
$wp_customize->add_setting( 'twp_open_graph_url',
	array(
	'default'           => $default['twp_open_graph_url'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'esc_url_raw',
	)
);
$wp_customize->add_control( 'twp_open_graph_url',
	array(
	'label'    => esc_html__( 'URL', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'text',
	)
);


// Header Advertise Image
$wp_customize->add_setting('twp_open_graph_home_default_image',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'twp_open_graph_home_default_image',
    	array(
        	'label'      => esc_html__( 'Image for Home and Default Image.', 'infinity-news' ),
           	'section'    => 'twp_open_graph_home_sec',
           	'priority' => 10,
       	)
   	)
);

// Open Graph Description.
$wp_customize->add_setting( 'twp_open_graph_locole',
	array(
	'default'           => $default['twp_open_graph_locole'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'twp_open_graph_locole',
	array(
	'label'    => esc_html__( 'Locale', 'infinity-news' ),
	'description'    => esc_html__( 'eg: en_US', 'infinity-news' ),
	'section'  => 'twp_open_graph_home_sec',
	'type'     => 'text',
	)
);


$wp_customize->add_section( 'twp_open_graph_custom_meta_sec', array(
        'title'    	=> __( 'Custom Meta', 'infinity-news' ),
        'panel'		=> 'infinity_news_og_panel',
        
) );

// Open Graph Custom Meta.
$wp_customize->add_setting( 'twp_open_graph_custom_meta',
	array(
	'default'           => '',
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_meta_sanitize',
	)
);
$wp_customize->add_control( 'twp_open_graph_custom_meta',
	array(
	'label'    => esc_html__( 'Custom Meta', 'infinity-news' ),
	'description'    => esc_html__( 'For example: <meta name="twitter:card" content="summary" />', 'infinity-news' ),
	'section'  => 'twp_open_graph_custom_meta_sec',
	'type'     => 'textarea',
	)
);