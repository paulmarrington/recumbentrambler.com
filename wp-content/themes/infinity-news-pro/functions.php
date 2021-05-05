<?php
/**
 * Infinity News functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Infinity_News
 */

if (!function_exists('infinity_news_setup')) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function infinity_news_setup()
    {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on Infinity News, use a find and replace
         * to change 'infinity-news' to the name of your theme in all the template files.
         */
        load_theme_textdomain('infinity-news', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'twp-primary-menu' => esc_html__('Primary Menu', 'infinity-news'),
            'twp-footer-menu' => esc_html__('Footer Menu', 'infinity-news'),
            'twp-social-menu' => esc_html__('Social Menu', 'infinity-news'),
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        // Set up the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('infinity_news_custom_background_args', array(
            'default-color' => 'f5f5f5',
            'default-image' => '',
        )));

        /*
         * Posts Formate.
         *
         * https://wordpress.org/support/article/post-formats/
         */
        add_theme_support( 'post-formats', array(
            'video',
            'audio',
            'gallery',
            'quote',
            'image'
        ) );

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support('custom-logo', array(
            'height' => 250,
            'width' => 250,
            'flex-width' => true,
            'flex-height' => true,
        ));

        if( is_admin() ) {
            require( get_template_directory() . '/updater/theme-updater.php' );
        }

        /**
         * Add theme support for gutenberg block
         *
         */
        add_theme_support( 'align-wide' );

    }
endif;
add_action('after_setup_theme', 'infinity_news_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function infinity_news_content_width()
{
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters('infinity_news_content_width', 810);
}

add_action('after_setup_theme', 'infinity_news_content_width', 0);

/**
 * Enqueue scripts and styles.
 */
function infinity_news_scripts()
{
    wp_enqueue_style('ionicons', get_template_directory_uri() . '/assets/lib/ionicons/css/ionicons.min.css');
    wp_enqueue_style('slick', get_template_directory_uri() . '/assets/lib/slick/css/slick.min.css');
    wp_enqueue_style('magnific-popup', get_template_directory_uri() . '/assets/lib/magnific-popup/magnific-popup.css');
    wp_enqueue_style('sidr-nav', get_template_directory_uri() . '/assets/lib/sidr/css/jquery.sidr.dark.css');
    wp_enqueue_style('aos', get_template_directory_uri() . '/assets/lib/aos/css/aos.css');
    wp_enqueue_style('infinity-news-style', get_stylesheet_uri());

    wp_enqueue_script('infinity-news-skip-link-focus-fix', get_template_directory_uri() . '/assets/lib/default/js/skip-link-focus-fix.js', array(), '20151215', true);
    wp_enqueue_script('jquery-slick', get_template_directory_uri() . '/assets/lib/slick/js/slick.min.js', array('jquery'), '', true);
    wp_enqueue_script('jquery-magnific-popup', get_template_directory_uri() . '/assets/lib/magnific-popup/jquery.magnific-popup.min.js', array('jquery'), '', true);
    wp_enqueue_script('jquery-sidr', get_template_directory_uri() . '/assets/lib/sidr/js/jquery.sidr.min.js', array('jquery'), '', true);
    wp_enqueue_script('theiaStickySidebar', get_template_directory_uri() . '/assets/lib/theiaStickySidebar/theia-sticky-sidebar.min.js', array('jquery'), '', true);
    wp_enqueue_script('match-height', get_template_directory_uri() . '/assets/lib/jquery-match-height/js/jquery.matchHeight.min.js', array('jquery'), '', true);
    wp_enqueue_script('aos', get_template_directory_uri() . '/assets/lib/aos/js/aos.js', array('jquery'), '', true);
    wp_enqueue_script('infinity-news-script', get_template_directory_uri() . '/assets/lib/twp/js/script.js', array('jquery'), '20201213', true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    wp_enqueue_script( 'infinity-news-ajax', get_template_directory_uri() . '/assets/lib/twp/js/ajax.js', array('jquery'), '', true );

    $default = infinity_news_get_default_theme_options();
    $ed_aos_animation = get_theme_mod('ed_aos_animation',$default['ed_aos_animation']);
    $ed_sticky_sidebar = get_theme_mod('ed_sticky_sidebar',$default['ed_sticky_sidebar']);

    wp_localize_script( 
        'infinity-news-script', 
        'infinity_news_script',
        array(
            'daymod'   => esc_html__( 'Light Mode', 'infinity-news' ),
            'nightmod' => esc_html__( 'Dark Mode', 'infinity-news' ),
            'ed_aos_animation'    => $ed_aos_animation,
            'ed_sticky_sidebar'   => $ed_sticky_sidebar,
         )
    );

    wp_localize_script( 
        'infinity-news-ajax', 
        'infinity_news_ajax',
        array(
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
            'loadmore'   => esc_html__( 'Load More', 'infinity-news' ),
            'nomore'     => esc_html__( 'No More Posts', 'infinity-news' ),
            'loading'    => esc_html__( 'Loading...', 'infinity-news' ),
         )
    );

}

add_action('wp_enqueue_scripts', 'infinity_news_scripts');

/**
 * Admin enqueue scripts and styles.
 */
function infinity_news_admin_scripts()
{

    wp_enqueue_style('infinity-news-admin', get_template_directory_uri() . '/assets/lib/twp/css/admin.css');
    wp_enqueue_media();
    $current_screen = get_current_screen();
    if( $current_screen->id === "widgets" ) {

        // Enqueue Script Only On Widget Page.
        
        wp_enqueue_script('infinity-news-widgets', get_template_directory_uri() . '/assets/lib/twp/js/widget.js', array('jquery'), '1.0.0', true);

    }

    if( $current_screen->id != "customize" ) {
        wp_enqueue_script('infinity-news-admin', get_template_directory_uri() . '/assets/lib/twp/js/admin.js', array('jquery'), '1.0.0', true);
    }

    wp_localize_script( 
        'infinity-news-admin', 
        'infinity_news_admin',
        array(
             'upload_image'   =>  esc_html__('Choose Image','infinity-news'),
             'use_imahe'   =>  esc_html__('Select','infinity-news'),
         )
    );

}

add_action('admin_enqueue_scripts', 'infinity_news_admin_scripts');

/**
 * Customizer Enqueue scripts and styles.
 */
function infinity_news_customizer_scripts()
{   
    wp_enqueue_script('jquery-ui-button');
    wp_enqueue_script('infinity-news-customizer', get_template_directory_uri() . '/assets/lib/twp/js/customizer.js', array('jquery','customize-controls'), '', 1);
    wp_enqueue_script('infinity-news-repeater', get_template_directory_uri() . '/assets/lib/twp/js/repeater.js', array('jquery','customize-controls'), '', 1);
    wp_enqueue_style('infinity-news-customizer', get_template_directory_uri() . '/assets/lib/twp/css/customizer.css');

    $infinity_news_post_category_list = infinity_news_post_category_list();

    $cat_option = '';

    if( $infinity_news_post_category_list ){
        foreach( $infinity_news_post_category_list as $key => $cats ){
            $cat_option .= "<option value='". esc_attr( $key )."'>". esc_html( $cats )."</option>";
        }
    }

    wp_localize_script( 
        'infinity-news-repeater', 
        'infinity_news_repeater',
        array(
            'optionns'   =>  "<option selected='selected' value='grid-posts'>". esc_html__('Banner Block 1','infinity-news')."</option><option value='grid-posts-2'>". esc_html__('Banner Block 2','infinity-news')."</option><option value='carousel-posts'>". esc_html__('Carousel Block','infinity-news'). "</option><option value='jumbotron-block'>". esc_html__('Jumbotron Block','infinity-news')."</option><option value='multiple-category-posts'>". esc_html__('Multiple Category Block','infinity-news')."</option><option value='advertise-area'>". esc_html__('Advertisement Block','infinity-news')."</option><option value='video'>". esc_html__('Video Post Block','infinity-news')."</option><option value='banner-block-tiles'>". esc_html__('Banner Block tiles','infinity-news')."</option><option value='slide'>". esc_html__('Slide Block','infinity-news')."</option><option value='tab'>". esc_html__('Tab Block','infinity-news')."</option><option value='seperator'>". esc_html__('Separator Block','infinity-news')."</option>",
             'new_section'   =>  esc_html__('New Section','infinity-news'),
             'upload_image'   =>  esc_html__('Choose Image','infinity-news'),
             'use_imahe'   =>  esc_html__('Select','infinity-news'),
             'categories'   => $cat_option,
         )
    );

    wp_localize_script( 
        'infinity-news-customizer', 
        'infinity_news_customizer',
        array(
            'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
         )
    );
}

add_action('customize_controls_enqueue_scripts', 'infinity_news_customizer_scripts');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Custom Functions.
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Grid Posts Functions.
 */
require get_template_directory() . '/inc/home/grid-posts.php';

/**
 * Grid Posts Two Functions.
 */
require get_template_directory() . '/inc/home/grid-posts-2.php';

/**
 * Jumbotron Functions.
 */
require get_template_directory() . '/inc/home/jumbotron.php';

/**
 * Carousel Posts Functions.
 */
require get_template_directory() . '/inc/home/carousel-posts.php';

/**
 * Multiple Category Posts Functions.
 */
require get_template_directory() . '/inc/home/multiple-cat.php';

/**
 * Slider Functions.
 */
require get_template_directory() . '/inc/home/slider.php';

/**
 * Tab Functions.
 */
require get_template_directory() . '/inc/home/tab.php';

/**
 * Ticker Posts
 */
require get_template_directory() . '/inc/ticker.php';

/**
 * Recommended Posts Functions.
 */
require get_template_directory() . '/inc/home/recommended-posts.php';

/**
 * Advertise Image Functions.
 */
require get_template_directory() . '/inc/home/advertise.php';

/**
 * Mailchimp Functions.
 */
require get_template_directory() . '/inc/home/mailchimp.php';

/**
 * Video Posts Functions.
 */
require get_template_directory() . '/inc/home/video.php';

/**
 * Block Tiles Posts Functions.
 */
require get_template_directory() . '/inc/home/block-tiles.php';

/**
 * Slide Posts Functions.
 */
require get_template_directory() . '/inc/home/slide.php';

/**
 * Recommended Posts Functions.
 */
require get_template_directory() . '/inc/ajax.php';

/**
 * Related Posts Functions.
 */
require get_template_directory() . '/inc/single/related-posts.php';

/**
 * Metabox.
 */
require get_template_directory() . '/inc/metabox/metabox.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';

/**
 * Breadcrumb Trail
 */
require get_template_directory() . '/inc/breadcrumbs.php';

/**
 * Widget Register
 */
require get_template_directory() . '/inc/widgets/widgets.php';

/**
 * Typography
 */
require get_template_directory() . '/inc/typography.php';

/**
 * Webmasters Tools
 */
require get_template_directory() . '/inc/webmasters.php';

/**
 * Open Graph
 */
require get_template_directory() . '/inc/open-graph.php';

/**
 * Twitter SUmmary
 */
require get_template_directory() . '/inc/twitter-card.php';

/**
 * TGM Plugin Recommendation.
 */
require get_template_directory() . '/inc/tgmpa/recommended-plugins.php';

/**
 * Dynamic Style.
 */
require get_template_directory() . '/assets/lib/twp/css/style.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined('JETPACK__VERSION') ) {
    require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Woocommerce Plugin SUpport.
 */
if ( class_exists( 'WooCommerce' ) ) {
    require get_template_directory() . '/inc/woocommerce.php';
}

add_filter( 'walker_nav_menu_start_in', 'infinity_news_add_description', 10, 2 );
function infinity_news_add_description( $item_output, $item ) {
    $description = $item->post_content;
    if (('' !== $description) && (' ' !== $description) ) {
        return preg_replace( '/(<a.*)</', '$1' . '<span class="menu-description">' . $description . '</span><', $item_output) ;
    }
    else {
        return $item_output;
    };
}

add_filter('wp_nav_menu_items', 'infinity_news_add_admin_link', 1, 2);
function infinity_news_add_admin_link($items, $args){
    if( $args->theme_location == 'twp-primary-menu' ){
        $item = '<li class="brand-home"><a title="Home" href="'. esc_url( home_url() ) .'">' . "<span class='icon ion-ios-home'></span>" . '</a></li>';
        $items = $item . $items;
    }
    return $items;
}