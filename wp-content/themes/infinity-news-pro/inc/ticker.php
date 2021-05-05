<?php
/**
* Ticker Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_ticker_posts' ) ):

    // Header Ticker Post.
    function infinity_news_ticker_posts( ){
        $default = infinity_news_get_default_theme_options();
        $ed_ticker_post = absint( get_theme_mod( 'ed_ticker_post',$default['ed_ticker_post'] ) );
        $ed_ticker_post_arrow = absint( get_theme_mod( 'ed_ticker_post_arrow',$default['ed_ticker_post_arrow'] ) );
        $ed_ticker_post_dots = absint( get_theme_mod( 'ed_ticker_post_dots',$default['ed_ticker_post_dots'] ) );
        $ed_ticker_post_autoplay = absint( get_theme_mod( 'ed_ticker_post_autoplay',$default['ed_ticker_post_autoplay'] ) );
        $ticker_posts_per_page = absint( get_theme_mod( 'ticker_posts_per_page',$default['ticker_posts_per_page'] ) );
        if( empty( $ticker_posts_per_page ) || $ticker_posts_per_page == 0 ){ $ticker_posts_per_page = -1; }
        $footer_ticker_post_category = esc_html( get_theme_mod( 'footer_ticker_post_category' ) );
        if( $ed_ticker_post ){

            $footer_ticker_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => $ticker_posts_per_page, 'category_name' => esc_html( $footer_ticker_post_category ) ) );
        ?>
            <div class="drawer-handle">
                <div class="drawer-handle-open">
                    <i class="ion ion-ios-add"></i>
                </div>
            </div>

            <?php 
            if( $footer_ticker_query->have_posts() ){

                if ( $ed_ticker_post_autoplay ) {
                    $autoplay = 'true';
                }else{
                    $autoplay = 'false';
                }
                if( $ed_ticker_post_dots ) {
                    $dots = 'true';
                }else {
                    $dots = 'false';
                }
                if( $ed_ticker_post_arrow ) {
                    $arrows = 'true';
                }else {
                    $arrows = 'false';
                }
                if( is_rtl() ) {
                    $rtl = 'true';
                }else{
                    $rtl = 'false';
                } ?>

                <div class="recommendation-panel-content">
                    <div class="drawer-handle-close">
                        <i class="ion ion-ios-close"></i>
                    </div>
                    <div class="recommendation-panel-slider">
                        <div class="wrapper">
                            <div class="drawer-carousel" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                <?php
                                while( $footer_ticker_query->have_posts() ){
                                    $footer_ticker_query->the_post();
                                    $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                                    <div class="slide-item">
                                        <article class="story-list">
                                            <div class="post-panel">

                                                <div class="post-thumb">
                                                    <a href="<?php the_permalink(); ?>" class="data-bg data-bg-xs" data-background="<?php echo esc_url( $featured_image[0] ); ?>"></a>
                                                </div>

                                                <div class="entry-content">
                                                    <h3 class="entry-title entry-title-small">
                                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                    </h3>
                                                </div>

                                            </div>
                                        </article>
                                    </div>

                                <?php } ?>

                            </div>
                        </div>
                    </div>
                </div>

            <?php
            wp_reset_postdata(); }
        }
    }
endif;