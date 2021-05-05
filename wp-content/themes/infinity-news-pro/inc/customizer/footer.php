<?php
/**
* Footer Settings.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();
$infinity_news_post_category_list = infinity_news_post_category_list();

// Footer Section.
$wp_customize->add_section( 'footer_setting',
	array(
	'title'      => esc_html__( 'Footer Settings', 'infinity-news' ),
	'priority'   => 200,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Ticker Post Enable Disable.
$wp_customize->add_setting('ed_ticker_post',
    array(
        'default' => $default['ed_ticker_post'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post',
    array(
        'label' => esc_html__('Enable Ticker Posts', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

$wp_customize->add_setting( 'footer_ticker_post_category',
    array(
    'default'           => '',
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'infinity_news_sanitize_select',
    )
);
$wp_customize->add_control( 'footer_ticker_post_category',
    array(
    'label'       => esc_html__( 'Ticker Post Category', 'infinity-news' ),
    'section'     => 'footer_setting',
    'type'        => 'select',
    'choices'     => $infinity_news_post_category_list,
    'priority'    => 1,
    )
);

// Header Image Ad Link.
$wp_customize->add_setting( 'ticker_posts_per_page',
    array(
    'default'           => $default['ticker_posts_per_page'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'absint',
    )
);
$wp_customize->add_control( 'ticker_posts_per_page',
    array(
    'label'    => esc_html__( 'Ticker Posts Per Page', 'infinity-news' ),
    'section'  => 'footer_setting',
    'type'     => 'text',
    )
);

// Ticker Post Slider Arrow Enable Disable.
$wp_customize->add_setting('ed_ticker_post_arrow',
    array(
        'default' => $default['ed_ticker_post_arrow'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_arrow',
    array(
        'label' => esc_html__('Enable Ticker Posts Slider Arrows', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

// Ticker Post Slider Dots Enable Disable.
$wp_customize->add_setting('ed_ticker_post_dots',
    array(
        'default' => $default['ed_ticker_post_dots'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_dots',
    array(
        'label' => esc_html__('Enable Ticker Posts Slider Dots', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

// Ticker Post Slider Autoplay Enable Disable.
$wp_customize->add_setting('ed_ticker_post_autoplay',
    array(
        'default' => $default['ed_ticker_post_autoplay'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_ticker_post_autoplay',
    array(
        'label' => esc_html__('Enable Ticker Posts Slider Autoplay', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

// Footer Layout.
$wp_customize->add_setting( 'footer_column_layout',
	array(
	'default'           => $default['footer_column_layout'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'infinity_news_sanitize_select',
	)
);
$wp_customize->add_control( 'footer_column_layout',
	array(
	'label'       => esc_html__( 'Top Footer Column Layout', 'infinity-news' ),
	'section'     => 'footer_setting',
	'type'        => 'select',
	'choices'               => array(
		'1' => esc_html__( 'One Column', 'infinity-news' ),
		'2' => esc_html__( 'Two Column', 'infinity-news' ),
		'3' => esc_html__( 'Three Column', 'infinity-news' ),
	    ),
	)
);

// Enable Disable Search.
$wp_customize->add_setting('ed_footer_social_icon',
    array(
        'default' => $default['ed_footer_social_icon'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_footer_social_icon',
    array(
        'label' => esc_html__('Enable Social Icons', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

// Enable Disable Search.
$wp_customize->add_setting('ed_footer_search',
    array(
        'default' => $default['ed_footer_search'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_footer_search',
    array(
        'label' => esc_html__('Enable Search', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);

// Header Image Ad Link.
$wp_customize->add_setting( 'footer_copyright_text',
	array(
	'default'           => $default['footer_copyright_text'],
	'capability'        => 'edit_theme_options',
	'sanitize_callback' => 'sanitize_text_field',
	)
);
$wp_customize->add_control( 'footer_copyright_text',
	array(
	'label'    => esc_html__( 'Footer Copyright Text', 'infinity-news' ),
	'section'  => 'footer_setting',
	'type'     => 'text',
	)
);

// Footer Credit Link Enable Disable.
$wp_customize->add_setting('ed_footer_credit_link',
    array(
        'default' => $default['ed_footer_credit_link'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_footer_credit_link',
    array(
        'label' => esc_html__('Enable Footer Credit Link', 'infinity-news'),
        'section' => 'footer_setting',
        'type' => 'checkbox',
    )
);