<?php
$default = infinity_news_get_default_theme_options();
$infinity_news_post_category_list_1 = infinity_news_post_category_list( );
$infinity_news_post_category_list = infinity_news_post_category_list( $select_cat = false );

// Category Color Section.
$wp_customize->add_section(
    'twp_category_setting_setion',
    array(
        'title'     => esc_html__( 'Category Settings', 'infinity-news' ),
        'priority'  => 155,
        'panel'      => 'theme_option_panel',
    )
);


// Recommended Posts Enable Disable.
$wp_customize->add_setting( 'infinity_news_category_colories', array(
    'sanitize_callback' => 'infinity_news_sanitize_repeater',
    'default' => json_encode( $default['infinity_news_category_colories'] ),
));

$wp_customize->add_control(  new Infinity_News_Repeater_Controler( $wp_customize, 'infinity_news_category_colories', 
    array(
        'section' => 'twp_category_setting_setion',
        'settings' => 'infinity_news_category_colories',
        'infinity_news_box_label' => esc_html__('New Category','infinity-news'),
        'infinity_news_box_add_control' => esc_html__('Add New Category Color','infinity-news'),
        'infinity_news_box_add_button' => true,
    ),
        array(
            'category' => array(
                'type'        => 'select',
                'label'       => esc_html__( 'Select Category', 'infinity-news' ),
                'options'     => $infinity_news_post_category_list_1,
                'class'       => 'infinity-news-custom-cat-color'
            ),
            'category_color' => array(
                'type'        => 'colorpicker',
                'label'       => esc_html__( 'Category Color', 'infinity-news' ),
                'class'       => ''
            ),
            
    )
));