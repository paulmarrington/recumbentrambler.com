<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Infinity News
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
}

$default = infinity_news_get_default_theme_options();
$ed_preloader = absint( get_theme_mod( 'ed_preloader',$default['ed_preloader'] ) );
if( $ed_preloader && !is_customize_preview() ){ ?>
<div class="preloader">
    <div class="preloader-background"></div>
    <div class="preloader-status-wrapper">
        <span>
            <span class="loader-circle loader-animation"></span>
            <span class="loader-circle loader-animation"></span>
            <span class="loader-circle loader-animation"></span>
        </span>
        <div class="preloader-status">
	        <span>
	            <span class="loader-circle loader-animation"></span>
	            <span class="loader-circle loader-animation"></span>
	            <span class="loader-circle loader-animation"></span>
	        </span>
        </div>
    </div>
</div>
<?php } ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'infinity-news'); ?></a>

    <?php $header_image = esc_url( get_header_image() ); ?>

    <header id="masthead" class="site-header <?php if( $header_image ){ ?>data-bg<?php } ?>" <?php if( $header_image ){ echo 'data-background="'.esc_url( $header_image ).'"'; } ?>>

        <?php $ed_top_header_social_icon = absint( get_theme_mod( 'ed_top_header_social_icon',$default['ed_top_header_social_icon'] ) );
        $ed_mid_header_search = absint( get_theme_mod( 'ed_mid_header_search', $default['ed_mid_header_search'] ) );
        $ed_top_header_current_date = absint( get_theme_mod( 'ed_top_header_current_date', $default['ed_top_header_current_date'] ) );

        if( $ed_top_header_social_icon || $ed_mid_header_search ){ ?>

            <div class="site-topbar hidden-mobile flex-block">
                <div class="wrapper">
                    <div class="topbar-items flex-block-items">
                        <div class="topbar-items-left">
                            <?php if ($ed_top_header_current_date) { ?>
                                <div class="twp-current-date">
                                    <?php echo esc_html( current_time('l, M d, Y') ); ?>
                                </div>
                            <?php } ?>

                            <?php if ( $ed_top_header_social_icon && has_nav_menu('twp-social-menu') ) { ?>
                                <div class="social-icons">
                                    <?php wp_nav_menu( array(
                                        'theme_location' => 'twp-social-menu',
                                        'link_before' => '<span class="screen-reader-text">',
                                        'link_after' => '</span>',
                                        'menu_id' => 'social-menu',
                                        'fallback_cb' => false,
                                        'menu_class' => false
                                    ) ); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <?php if ($ed_mid_header_search) { ?>
                            <div class="search-bar">
                                <?php get_search_form(); ?>
                            </div>
                        <?php } ?>

                    </div>

                </div>
            </div>
        
        <?php
        }

        $header_logo_position = esc_attr( get_theme_mod( 'header_logo_position',$default['header_logo_position'] ) ); ?>

        <div class="site-middlebar flex-block <?php echo 'twp-align-'.esc_attr( $header_logo_position ); ?>">
            <div class="wrapper">
                <div class="middlebar-items flex-block-items">
                    <div class="site-branding">
                        <?php
                        the_custom_logo();

                        $twp_dark_mod_logo = esc_url( get_theme_mod( 'twp_dark_mod_logo' ) );
                        if( $twp_dark_mod_logo ){ ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link custom-logo-link-dark" rel="home">
                                <img src="<?php echo esc_url( $twp_dark_mod_logo ); ?>" class="custom-logo" alt="<?php bloginfo('name'); ?>">
                            </a>
                        <?php
                        }

                        if ( is_front_page() && is_home() ) : ?>
                            <h1 class="site-title">
                                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                            </h1>
                        <?php
                        else :
                            ?>
                            <p class="site-title">
                                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                            </p>
                        <?php
                        endif;
                        $infinity_news_description = get_bloginfo('description', 'display');
                        if ($infinity_news_description || is_customize_preview()) :
                            ?>
                            <p class="site-description">
                               <span><?php echo esc_html( $infinity_news_description ); /* WPCS: xss ok. */ ?></span>
                            </p>
                        <?php endif; ?>
                    </div><!-- .site-branding -->

                    <?php
                    $header_advertise_image = esc_url( get_theme_mod( 'header_advertise_image' ) );
                    $header_advertise_link = esc_url( get_theme_mod( 'header_advertise_link' ) );
                    $header_advertise_script = get_theme_mod( 'header_advertise_script' );

                    if( $header_advertise_script ){ ?>

                        <div class="site-header-banner">
                            <?php echo $header_advertise_script; ?>
                        </div>
                        
                    <?php
                    }else{

                        if( $header_advertise_image ){ ?>
                            <div class="site-header-banner">
                                <?php if( $header_advertise_link) { ?><a target="_blank" href="<?php echo esc_url( $header_advertise_link ) ?>"><?php } ?>
                                    <img src="<?php echo esc_url( $header_advertise_image ) ?>" title="<?php esc_attr_e('Header Advertise','infinity-news'); ?>" alt="<?php esc_attr_e('Header Advertise','infinity-news'); ?>">
                                <?php if( $header_advertise_link) { ?></a><?php } ?>
                            </div>
                        <?php
                        }
                    } ?>

                </div>
            </div>
        </div>
        <nav id="site-navigation" class="main-navigation">
            <div class="wrapper">
                <div class="navigation-area">
                    <?php if (is_active_sidebar('infinity-news-offcanvas-widget')): ?>
                        <div id="widgets-nav" class="icon-sidr">
                            <div id="hamburger-one">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="toggle-menu" aria-controls="primary-menu" aria-expanded="false">
                        <a class="offcanvas-toggle" href="#">
                            <div class="trigger-icon">
                               <span class="menu-label">
                                    <?php esc_html_e('Menu', 'infinity-news'); ?>
                                </span>
                            </div>
                        </a>
                    </div>

                    <?php wp_nav_menu(array(
                        'theme_location' => 'twp-primary-menu',
                        'menu_id' => 'primary-menu',
                        'container' => 'div',
                        'container_class' => 'menu'
                    )); ?>

                    <div class="nav-right">

                        <?php
                        $ed_display_mode = get_theme_mod( 'ed_display_mode',$default['ed_display_mode'] );
                        if( $ed_display_mode ){ ?>
                            <div class="twp-color-scheme">
                                <div id="night-mode">
                                    <a role="button" href="#" class="colour-switcher-btn">
                                        <span class="twp-toggle-tooltip"><span class="twp-tooltip-wrapper"></span></span> <i class=""></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if( class_exists( 'WooCommerce' ) ){ ?>
                            <span class="twp-minicart">
                                 <?php infinity_news_woocommerce_header_cart(); ?>
                            </span>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </nav><!-- #site-navigation -->
    </header><!-- #masthead -->

    <?php if( empty( infinity_news_check_woocommerce_page() ) && !is_home() ){ do_action('infinity_news_header_banner_x'); } ?>

    <?php if ( !is_front_page() || ( is_front_page() && class_exists( 'WooCommerce' ) && is_shop() ) ): ?>
        <div id="content" class="site-content">
    <?php endif; ?>