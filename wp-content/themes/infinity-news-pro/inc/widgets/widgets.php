<?php
/**
 * Widget FUnctions.
 *
 * @package Infinity News
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */

function infinity_news_widgets_init()
{
    $default = infinity_news_get_default_theme_options();

    register_sidebar(array(
        'name' => esc_html__('Sidebar', 'infinity-news'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here.', 'infinity-news'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => esc_html__('Offcanvas Widget', 'infinity-news'),
        'id' => 'infinity-news-offcanvas-widget',
        'description' => esc_html__('Add widgets here.', 'infinity-news'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    $footer_column_layout = absint(get_theme_mod('footer_column_layout', $default['footer_column_layout']));

    for ($i = 0; $i < $footer_column_layout; $i++) {

        if ($i == 0) {
            $count = esc_html__('One', 'infinity-news');
        }
        if ($i == 1) {
            $count = esc_html__('Two', 'infinity-news');
        }
        if ($i == 2) {
            $count = esc_html__('Three', 'infinity-news');
        }

        register_sidebar(array(
            'name' => esc_html__('Footer Widget ', 'infinity-news') . $count,
            'id' => 'infinity-news-footer-widget-' . $i,
            'description' => esc_html__('Add widgets here.', 'infinity-news'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        ));
    }

}

add_action('widgets_init', 'infinity_news_widgets_init');

/**
 * Widget Base Class.
 */
require get_template_directory() . '/inc/widgets/widget-base-class.php';

/**
 * Recent Post Widget.
 */
require get_template_directory() . '/inc/widgets/recent-post.php';

/**
 * Social Link Widget.
 */
require get_template_directory() . '/inc/widgets/social-link.php';

/**
 * Author Widget.
 */
require get_template_directory() . '/inc/widgets/author.php';

/**
 * Author Widget.
 */
require get_template_directory() . '/inc/widgets/tab-posts.php';

/**
 * Category Widget.
 */
require get_template_directory() . '/inc/widgets/category.php';