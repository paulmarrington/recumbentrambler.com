<?php
/**
 * Template for Footer Components
 * @since infinity-news 1.0.0
 */
?>

<?php if ( is_active_sidebar('infinity-news-offcanvas-widget') ): ?>
    <div id="sidr-nav">
        <div class="sidr-area">
            <div class="sidr-close-holder">
                <a class="sidr-class-sidr-button-close" href="#sidr-nav">
                    <?php echo esc_html__('Close', 'infinity-news');?><i class="ion ion-ios-close"></i>
                </a>
            </div>
            <?php dynamic_sidebar('infinity-news-offcanvas-widget'); ?>
        </div>
    </div>
<?php endif; ?>


<?php if( is_singular('post') ):
    // Single Posts Related Posts.
    infinity_news_related_posts();
endif; ?>


<?php
// Footer Ticker Posts
infinity_news_ticker_posts(); 

$default = infinity_news_get_default_theme_options();
$ed_mailchimp_newsletter = get_theme_mod( 'ed_mailchimp_newsletter',$default['ed_mailchimp_newsletter'] );

if( $ed_mailchimp_newsletter ){

    $ed_mailchimp_newsletter_home_only = get_theme_mod( 'ed_mailchimp_newsletter_home_only',$default['ed_mailchimp_newsletter_home_only'] );
    $ed_mailchimp_newsletter_first_loading_only = get_theme_mod( 'ed_mailchimp_newsletter_first_loading_only',$default['ed_mailchimp_newsletter_first_loading_only'] );
    $twp_mailchimp_shortcode = get_theme_mod( 'twp_mailchimp_shortcode' );
    $twp_newsletter_title = get_theme_mod( 'twp_newsletter_title',$default['twp_newsletter_title'] );
    $twp_newsletter_desc = get_theme_mod( 'twp_newsletter_desc',$default['twp_newsletter_desc'] );
    $twp_newsletter_image = get_theme_mod( 'twp_newsletter_image' );

    if( $ed_mailchimp_newsletter_home_only){
        if( is_home() || is_front_page() ){

            $load_pages = true;

        }else{
            $load_pages = false;
        }
    }else{
        $load_pages = true;
    }

    if( $load_pages ){ ?>

    <div class="twp-modal <?php if( $ed_mailchimp_newsletter_first_loading_only ){ echo 'single-load'; }else{ echo 'always-load'; } ?>">

        <div class="twp-modal-overlay twp-modal-toggle"></div>

        <div class="twp-modal-wrapper twp-modal-transition">
            <div class="twp-modal-body">
                <div class="newsletter-content-wrapper">
                    <div class="newsletter-image">
                        <div class="data-bg data-bg-large"
                             data-background="<?php echo esc_url( $twp_newsletter_image ); ?>">
                        </div>
                    </div>
                    <div class="newsletter-content">
                        <button class="twp-modal-close twp-modal-toggle">
                            <i class="ion ion-ios-close"></i>
                        </button>
                        <div class="newsletter-content-details">

                            <?php if( $twp_newsletter_title ){ ?>
                                <h3><?php echo esc_html( $twp_newsletter_title ); ?></h3>
                            <?php } ?>

                            <?php if( $twp_newsletter_desc ){ ?>
                                <div class="newsletter-content-excerpt"><?php echo esc_html( $twp_newsletter_desc ); ?></div>
                            <?php } ?>

                            <?php if( $twp_mailchimp_shortcode ){ ?>
                                <div class="mailchimp-form-wrapper">
                                    <?php echo do_shortcode($twp_mailchimp_shortcode); ?>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php }

} ?>