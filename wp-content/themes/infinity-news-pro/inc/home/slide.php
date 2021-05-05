<?php
/**
* Slide Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_slide_posts' ) ):

    // Header Grid Post.
    function infinity_news_slide_posts( $infinity_news_home_section ){

        $section_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $slide_full_width = isset( $infinity_news_home_section->slide_full_width ) ? $infinity_news_home_section->slide_full_width : '' ;
        $ed_instagram_arrow = isset( $infinity_news_home_section->ed_instagram_arrow ) ? $infinity_news_home_section->ed_instagram_arrow : '' ;
        $ed_instagram_autoplay = isset( $infinity_news_home_section->ed_instagram_autoplay ) ? $infinity_news_home_section->ed_instagram_autoplay : '' ;
        $ed_instagram_dot = isset( $infinity_news_home_section->ed_instagram_dot ) ? $infinity_news_home_section->ed_instagram_dot : '' ;
        $slide_layout = isset( $infinity_news_home_section->slide_layout ) ? $infinity_news_home_section->slide_layout : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
        $ed_excerpt_content = isset( $infinity_news_home_section->ed_excerpt_content ) ? $infinity_news_home_section->ed_excerpt_content : '' ;

        if( $posts_category ){
            $idObj = get_category_by_slug( $posts_category ); 
            $id1 = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id1 );
        }

        if ( $ed_instagram_autoplay == 'yes' ) {
            $autoplay = 'true';
        }else{
            $autoplay = 'false';
        }
        if( $ed_instagram_dot == 'yes' ) {
            $dots = 'true';
        }else {
            $dots = 'false';
        }
        if( is_rtl() ) {
            $rtl = 'true';
        }else{
            $rtl = 'false';
        }

        if( $slide_layout == 'carousel-layout' ){
            $layout_class = 'homepage-layout-carousal';
        }else{
            $layout_class = 'homepage-layout-slider';
        }
        $grid_slide_posts = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => -1, 'category_name' => esc_html( $posts_category ) ) );

        ?>
        <div class="homepage-slider <?php if( $slide_full_width == 'yes' ){ echo 'full-width-slider'; }else{  echo 'boxed-width-slider'; } ?> twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?> >

            <div class="wrapper">
                <div class="twp-row">
                    <div class="column">
                        <header class="block-title-wrapper">

                            <div class="hr-line"></div>

                            <?php if( $section_title ){ ?>
                                <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg"><?php echo esc_html( $section_title ); ?></h2>
                            <?php } ?>

                            <?php if( $ed_instagram_arrow == 'yes' && $slide_layout == 'carousel-layout' ){ ?>
                                <div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="title-controls title-controls-bg">
                                    <div class="twp-slide-prev slide-icon-1 slide-prev-3 slick-arrow">
                                        <i class="ion-ios-arrow-back slick-arrow"></i>
                                    </div>
                                    <div class="twp-slide-next slide-icon-1 slide-next-3 slick-arrow">
                                        <i class="ion-ios-arrow-forward slick-arrow"></i>
                                    </div>
                                </div>
                            <?php } ?>

                        </header>
                    </div>
                </div>
            </div>

            <?php if( $grid_slide_posts->have_posts() ){ ?>

                <div class="homepage-slider-wrapper">

                    <div class="<?php echo esc_attr( $layout_class ); ?>" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>

                        <?php while( $grid_slide_posts->have_posts() ){
                            $grid_slide_posts->the_post();
                            $featured_image_1 = wp_get_attachment_image_src( get_post_thumbnail_id(),'full' ); ?>

                            <div class="single-slide block-bg-alt">
                                <a href="" class="data-bg data-bg-large" data-background="<?php echo esc_url( $featured_image_1[0] ); ?>">
                                    <span class="data-bg-overlay"></span>
                                </a>
                                <div class="slide-details">
                                    <div class="entry-content">
                                        
                                        <div class="entry-meta entry-meta-category">
                                            <?php if( $ed_relevant_cat == 'yes' && $posts_category ){ ?>

                                                <span class="cat-links">
                                                    <a class="twp_cat_<?php echo esc_attr( $grid_post_2_category_1 ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                </span>

                                            <?php
                                            }else{

                                                infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                            } ?>
                                        </div>

                                        <h3 class="entry-title entry-title-full">
                                            <a href="<?php the_permalink(); ?>" tabindex="-1"><?php the_title(); ?></a>
                                        </h3>

                                        <div class="entry-meta">
                                            <?php
                                            infinity_news_posted_by();
                                            echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
                                            infinity_news_posted_on();
                                            ?>
                                        </div><!-- .entry-meta -->

                                        <?php if( $ed_excerpt_content == 'yes' ){ ?>
                                            <div class="entry-description">
                                                <?php
                                                if( has_excerpt() ){
                                                  the_excerpt();
                                                }else{
                                                  echo wp_kses_post( wp_trim_words( get_the_content(),50,'...') );
                                                } ?>
                                            </div>
                                        <?php } ?>
                                    
                                    </div>
                                </div>
                            </div>

                        <?php }
                        wp_reset_postdata(); ?>

                    </div>

                    <?php if( $slide_layout != 'carousel-layout' ){ ?>
                        <div class="twp-slidesnav">
                            <div class="slider-nav carousel-space">

                                <?php while( $grid_slide_posts->have_posts() ){
                                    $grid_slide_posts->the_post();
                                    $featured_image_2 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

                                    <div class="slider-nav-item">
                                        <figure class="slider-article">
                                            <div class="post-thumb">
                                                <span class="slider-nav-image bg-image">
                                                    <img src="<?php echo esc_url( $featured_image_2[0] ); ?>">
                                                </span>
                                            </div>
                                            <div class="entry-content">
                                                <h3 class="entry-title entry-title-small"><?php the_title(); ?></h3>
                                            </div>
                                        </figure>
                                    </div>
                                <?php }
                                wp_reset_postdata(); ?>

                            </div>
                        </div>
                    <?php } ?>

                </div>

            <?php } ?>

        </div>
        <?php
    }
endif;