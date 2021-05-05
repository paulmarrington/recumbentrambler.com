<?php
/**
* Color Options.
*
* @package Infinity News
*/
$default = infinity_news_get_default_theme_options();

$wp_customize->add_setting(
    'infinity_news_primary_color',
    array(
        'default'           => $default['infinity_news_primary_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_primary_color',
        array(
            'settings'      => 'infinity_news_primary_color',
            'section'       => 'colors',
            'label'         => esc_html__(' Primary Color ', 'infinity-news' ),
        )
    )
);


$wp_customize->add_setting(
    'infinity_news_secondary_color',
    array(
        'default'           => $default['infinity_news_secondary_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'priority' => 1
    )
);

$wp_customize->add_control(
    new WP_Customize_Color_Control(
        $wp_customize,
        'infinity_news_secondary_color',
        array(
            'settings'      => 'infinity_news_secondary_color',
            'section'       => 'colors',
            'label'         => esc_html__(' Secondary Color ', 'infinity-news' ),
        )
    )
);