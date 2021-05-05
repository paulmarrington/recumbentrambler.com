<?php
/**
* Mailchimp Newsletter Settings.
*
* @package Infinity News
*/

$default = infinity_news_get_default_theme_options();

// Mailchimp Newsletter Section.
$wp_customize->add_section( 'twp_mailchimp_newsletter',
	array(
	'title'      => esc_html__( 'Popup Mailchimp', 'infinity-news' ),
	'priority'   => 200,
	'capability' => 'edit_theme_options',
	'panel'      => 'theme_option_panel',
	)
);

// Newsletter Enable Disable.
$wp_customize->add_setting('ed_mailchimp_newsletter',
    array(
        'default' => $default['ed_mailchimp_newsletter'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_mailchimp_newsletter',
    array(
        'label' => esc_html__('Enable Popup Newsletter', 'infinity-news'),
        'section' => 'twp_mailchimp_newsletter',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

// Newsletter Enable Disable.
$wp_customize->add_setting('ed_mailchimp_newsletter_home_only',
    array(
        'default' => $default['ed_mailchimp_newsletter_home_only'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_mailchimp_newsletter_home_only',
    array(
        'label' => esc_html__('Popup Newsletter On Home Page Only', 'infinity-news'),
        'section' => 'twp_mailchimp_newsletter',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

// Newsletter Enable Disable.
$wp_customize->add_setting('ed_mailchimp_newsletter_first_loading_only',
    array(
        'default' => $default['ed_mailchimp_newsletter_first_loading_only'],
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'infinity_news_sanitize_checkbox',
    )
);
$wp_customize->add_control('ed_mailchimp_newsletter_first_loading_only',
    array(
        'label' => esc_html__('One Time Load Popup Newsletter', 'infinity-news'),
        'section' => 'twp_mailchimp_newsletter',
        'type' => 'checkbox',
        'priority' => 1,
    )
);

// Newsletter Image
$wp_customize->add_setting('twp_newsletter_image',
    array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    )
);
$wp_customize->add_control( new WP_Customize_Image_Control(
    $wp_customize,
    'twp_newsletter_image',
        array(
            'label'      => esc_html__( 'Newsletter Image', 'infinity-news' ),
            'section'    => 'twp_mailchimp_newsletter',
            'priority' => 10,
        )
    )
);

// Newsletter Title.
$wp_customize->add_setting( 'twp_newsletter_title',
    array(
    'default'           => $default['twp_newsletter_title'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'twp_newsletter_title',
    array(
    'label'    => esc_html__( 'Newsletter Title', 'infinity-news' ),
    'section'  => 'twp_mailchimp_newsletter',
    'type'     => 'text',
    )
);

// Newsletter Description.
$wp_customize->add_setting( 'twp_newsletter_desc',
    array(
    'default'           => $default['twp_newsletter_desc'],
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'twp_newsletter_desc',
    array(
    'label'    => esc_html__( 'Newsletter Description', 'infinity-news' ),
    'section'  => 'twp_mailchimp_newsletter',
    'type'     => 'text',
    )
);

// Mailchimp Shortcode.
$wp_customize->add_setting( 'twp_mailchimp_shortcode',
    array(
    'default'           => '',
    'capability'        => 'edit_theme_options',
    'sanitize_callback' => 'sanitize_text_field',
    )
);
$wp_customize->add_control( 'twp_mailchimp_shortcode',
    array(
    'label'    => esc_html__( 'Mailchimp Shortcode', 'infinity-news' ),
    'section'  => 'twp_mailchimp_newsletter',
    'type'     => 'textarea',
    )
);