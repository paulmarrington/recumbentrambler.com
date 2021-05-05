<?php
/**
 * Template for Off canvas Menu
 * @since infinity-news 1.0.0
 */
?>
<div id="offcanvas-menu">
    <div class="close-offcanvas-menu offcanvas-item">
        <a href="javascript:void(0)" class="skip-link-offcanvas-start"></a>
        <a href="javascript:void(0)" class="offcanvas-close">
            <span>
               <?php echo esc_html__('Close', 'infinity-news'); ?>
            </span>
        </a>
    </div>

   <div class="offcanvas-item">
        <div class="search-bar-offcanvas">
            <?php get_search_form(); ?>
        </div>
   </div>

    <?php if (has_nav_menu('twp-primary-menu')) { ?>
        <div id="primary-nav-offcanvas" class="offcanvas-navigation offcanvas-item">
            <div class="offcanvas-title">
                <?php esc_html_e('Menu', 'infinity-news'); ?>
            </div>
            <?php wp_nav_menu(array(
                'theme_location' => 'twp-primary-menu',
                'menu_id' => 'primary-menu',
                'container' => 'div',
                'container_class' => 'menu'
            )); ?>
        </div>
    <?php } ?>
    <?php if (has_nav_menu('twp-social-menu')) { ?>
        <div class="offcanvas-social offcanvas-item">
            <div class="offcanvas-title">
                <?php esc_html_e('Social profiles', 'infinity-news'); ?>
            </div>
            <div class="social-icons">
                <?php
                wp_nav_menu(
                    array('theme_location' => 'twp-social-menu',
                        'link_before' => '<span class="screen-reader-text">',
                        'link_after' => '</span>',
                        'menu_id' => 'social-menu',
                        'fallback_cb' => false,
                        'menu_class' => false
                    )); ?>
            </div>
        </div>
    <?php } ?>
    <button type="button" class="twp-off-focus screen-reader-text"></button>
</div>