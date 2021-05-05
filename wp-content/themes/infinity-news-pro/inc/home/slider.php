<?php
/**
* Slider Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_slider' ) ):

    // Header Slider
    function infinity_news_slider( $infinity_news_home_section ){

        $slider_ed  = isset( $infinity_news_home_section->slider_ed ) ? $infinity_news_home_section->slider_ed : '' ;
        $slider_category    = isset( $infinity_news_home_section->slider_category ) ? $infinity_news_home_section->slider_category : '' ;
        $slider_autoplay    = isset( $infinity_news_home_section->slider_autoplay ) ? $infinity_news_home_section->slider_autoplay : '' ;
        $slider_dots    = isset( $infinity_news_home_section->slider_dots ) ? $infinity_news_home_section->slider_dots : '' ;
        $slider_arrows  = isset( $infinity_news_home_section->slider_arrows ) ? $infinity_news_home_section->slider_arrows : '' ;

        if ( $slider_ed == 'yes' ) {
            
            $slider_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'category_name' => esc_html( $slider_category ) ) );

            if ( $slider_query->have_posts() ):

                if ( $slider_autoplay == 'yes' ) {
                    $autoplay = 'true';
                }else{
                    $autoplay = 'false';
                }
                if( $slider_dots == 'yes' ) {
                    $dots = 'true';
                }else {
                    $dots = 'false';
                }
                if( $slider_arrows == 'yes' ) {
                    $arrows = 'true';
                }else {
                    $arrows = 'false';
                }
                if( is_rtl() ) {
                    $rtl = 'true';
                }else{
                    $rtl = 'false';
                }

                ?>
                <div class="latest-post-slider" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>

                    <?php while ($slider_query->have_posts()):

                        $slider_query->the_post();
                        $slider_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'large' ); ?>

                        <div class="slide-item block-bg-alt">
                            <a href="<?php the_permalink(); ?>" class="slide-bg data-bg" data-background="<?php echo esc_url( $slider_image[0] ); ?>">
                                <span class="data-bg-overlay"></span>
                            </a>

                            <div class="slide-details">

                                <div class="entry-content">
                                                
                                    <div class="entry-meta entry-meta-category">
                                        
                                        <?php infinity_news_entry_footer( $cats = true,$tags = false,$edits = false ); ?>

                                    </div>

                                    <h3 class="entry-title entry-title-full">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h3>

                                    <div class="entry-meta">
                                        <?php
                                        infinity_news_posted_by();
                                        echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
                                        infinity_news_posted_on();
                                        ?>
                                    </div><!-- .entry-meta -->

                                    <?php
                                    $default = infinity_news_get_default_theme_options();
                                    $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );
                                    $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
                                    if( class_exists( 'Booster_Extension_Class') && ( $ed_social_icon || $ed_like_dislike ) ){

                                        echo "<div class='archive-like-share'>";
                                        $args = array('layout'=>'layout-2','status'=>'enable');
                                        if( $ed_social_icon ){
                                            do_action('booster_extension_social_icons',$args);
                                        }
                                        if( $ed_like_dislike ){
                                            do_action('booster_extension_like_dislike','allenable');
                                        }
                                        echo "</div>";
                                    } ?>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>
                <?php
                wp_reset_postdata();
            endif;
        }
    }

endif;