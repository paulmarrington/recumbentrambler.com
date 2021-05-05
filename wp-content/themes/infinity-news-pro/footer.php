<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Infinity_News
 */

if ( !is_front_page() || ( is_front_page() && class_exists( 'WooCommerce' ) && is_shop() ) ): ?>
</div><!-- #content -->
<?php endif; ?>

<?php get_template_part('template-parts/header/offcanvas', 'menu'); ?>
<?php get_template_part('template-parts/footer/footer', 'component');

$default = infinity_news_get_default_theme_options();
$ed_footer_social_icon = absint( get_theme_mod( 'ed_footer_social_icon',$default['ed_footer_social_icon'] ) );
$ed_footer_search = absint( get_theme_mod( 'ed_footer_search',$default['ed_footer_search'] ) );
?>

<footer id="colophon" class="site-footer">

    <?php if( $ed_footer_social_icon || $ed_footer_search ){ ?>
        <div class="footer-top flex-block">
            <div class="wrapper">
                <div class="footer-items flex-block-items">

                    <?php if ( $ed_footer_social_icon && has_nav_menu('twp-social-menu') ) { ?>
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

                    <?php if( $ed_footer_search ){ ?>
                        <div class="footer-right">
                            <div class="footer-items-right search-bar">
                                <?php get_search_form(); ?>
                            </div>

                            <div class="footer-items-right scroll-up">
                                <i class="ion ion-ios-arrow-round-up"></i>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>
        <?php
    }
    
    if ( is_active_sidebar('infinity-news-footer-widget-0') || is_active_sidebar('infinity-news-footer-widget-1') || is_active_sidebar('infinity-news-footer-widget-2') ):


        $footer_column_layout = absint( get_theme_mod('footer_column_layout', $default['footer_column_layout'] ) ); ?>

        <div class="footer-middle <?php echo 'footer-column-' . absint($footer_column_layout); ?>">
            <div class="wrapper">
                <div class="footer-grid twp-row">
                    <?php if ( is_active_sidebar('infinity-news-footer-widget-0') ): ?>
                        <div class="column column-1">
                            <?php dynamic_sidebar('infinity-news-footer-widget-0'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( is_active_sidebar('infinity-news-footer-widget-1') ): ?>
                        <div class="column column-2">
                            <?php dynamic_sidebar('infinity-news-footer-widget-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( is_active_sidebar('infinity-news-footer-widget-2') ): ?>
                        <div class="column column-3">
                            <?php dynamic_sidebar('infinity-news-footer-widget-2'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>


    <div class="footer-bottom">
        <?php if ( has_nav_menu('twp-footer-menu') ) { ?>
            <div class="footer-menu">
                <div class="wrapper">
                    <?php wp_nav_menu( array(
                        'theme_location' => 'twp-footer-menu',
                        'menu_id' => 'footer-menu',
                        'container' => 'div',
                        'container_class' => 'menu',
                        'depth' => 1,
                    ) ); ?>
                </div>
            </div>
        <?php } ?>
        <div class="site-copyright">
            <div class="wrapper">
                <div class="site-info">
                    <?php
                    $footer_copyright_text = wp_kses_post( get_theme_mod( 'footer_copyright_text',$default['footer_copyright_text'] ) );
                    if (!empty( $footer_copyright_text ) ) {
                        echo wp_kses_post( $footer_copyright_text );
                    }

                    $ed_footer_credit_link = absint( get_theme_mod( 'ed_footer_credit_link',$default['ed_footer_credit_link'] ) );
                    if( $ed_footer_credit_link ){
                        ?>
                        <span class="sep"> | </span>
                        <?php
                        /* translators: 1: Theme name, 2: Theme author. */
                        printf(esc_html__('Theme: %1$s by %2$s.', 'infinity-news'), '<strong>Infinity News</strong>', '<a href="https://www.themeinwp.com/">Themeinwp</a>');
                    }
                    ?>
                </div>
            </div><!-- .site-info -->
        </div>
    </div>
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
