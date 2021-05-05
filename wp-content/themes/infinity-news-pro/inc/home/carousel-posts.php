<?php
/**
* Carousel Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_carousel_posts' ) ):

    // Header Carousel Post.
    function infinity_news_carousel_posts( $infinity_news_home_section ){
        
        $carousel_section_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
		$carousel_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
		$ed_carouser_overlay_layout = isset( $infinity_news_home_section->ed_carouser_overlay_layout ) ? $infinity_news_home_section->ed_carouser_overlay_layout : '' ;
		$ed_relevant_cat_carousel = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
		$ed_title_control_grid = isset( $infinity_news_home_section->ed_title_control ) ? $infinity_news_home_section->ed_title_control : '' ;
		$ed_arrows_carousel = isset( $infinity_news_home_section->ed_arrows_carousel ) ? $infinity_news_home_section->ed_arrows_carousel : '' ;
		$ed_dots_carousel = isset( $infinity_news_home_section->ed_dots_carousel ) ? $infinity_news_home_section->ed_dots_carousel : '' ;
		$ed_autoplay_carousel = isset( $infinity_news_home_section->ed_autoplay_carousel ) ? $infinity_news_home_section->ed_autoplay_carousel : '' ;
		$section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
        $post_category_post_num = isset( $infinity_news_home_section->post_category_post_num ) ? $infinity_news_home_section->post_category_post_num : '' ;
        if( empty( $post_category_post_num ) ){ $post_category_post_num = 12; }

        $default = infinity_news_get_default_theme_options();
        $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
        $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );

        $carousel_post_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => absint( $post_category_post_num ), 'category_name' => esc_html( $carousel_category ) ) );

        if( $carousel_category ){
            $idObj = get_category_by_slug( $carousel_category ); 
            $id = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id );
        }
        
        if ( $ed_autoplay_carousel == 'yes' ) {
            $autoplay = 'true';
        }else{
            $autoplay = 'false';
        }
        if( $ed_dots_carousel == 'yes' ) {
            $dots = 'true';
        }else {
            $dots = 'false';
        }
        if( is_rtl() ) {
            $rtl = 'true';
        }else{
            $rtl = 'false';
        }

        if ( $carousel_post_query->have_posts() ): ?>

            <div class="home-carousel <?php if( $ed_carouser_overlay_layout == 'yes' ){ echo 'home-carousel-overlay'; } ?> twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
			    <div class="wrapper">

			    	<?php if( $carousel_section_title || $ed_arrows_carousel != 'no' ){ ?>
				        <div class="twp-row">
				            <div class="column">
				                <header class="block-title-wrapper">
				                    <div class="hr-line"></div>
				                	<?php if( $carousel_section_title ){ ?>
					                    <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg">
					                        <?php echo esc_html( $carousel_section_title ); ?>
					                    </h2>
					                <?php } ?>

					                <?php if( $ed_arrows_carousel != 'no' ){ ?>
					                    <div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="title-controls title-controls-bg">
					                        <div class="twp-slide-prev slide-icon-1 slide-prev-1 slick-arrow">
					                            <i class="ion-ios-arrow-back slick-arrow"></i>
					                        </div>
					                        <div class="twp-slide-next slide-icon-1 slide-next-1 slick-arrow">
					                            <i class="ion-ios-arrow-forward slick-arrow"></i>
					                        </div>
					                    </div>
					                <?php } ?>

				                </header>
				            </div>
				        </div>
				    <?php } ?>

			        <div class="twp-row">
			            <div class="column">
			                <div class="twp-carousel carousel-space" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
				                <?php while( $carousel_post_query->have_posts() ){ 
				                	$carousel_post_query->the_post();
				                	$featured_image_big = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>
				                    <div class="twp-carousel-item">
				                        <article class="story-carousel">
				                            <div class="post-panel block-bg" data-mh="carousel-item">
				                                <div class="post-thumb">
				                                    <a href="<?php the_permalink(); ?>" class="data-bg data-bg-medium" data-background="<?php echo esc_url( $featured_image_big[0] ); ?>">
				                                    	<?php if( $ed_carouser_overlay_layout == 'yes' ){ ?>
	                                                        <span class="data-bg-overlay"></span>
	                                                    <?php } ?>
				                                    </a>

			                                        <?php
			                                        if( class_exists( 'Booster_Extension_Class') && $ed_carouser_overlay_layout != 'yes' && $ed_like_dislike ){
			                                            do_action('booster_extension_like_dislike','allenable');
			                                        } ?>
				                                </div>
				                                <div class="entry-content">
				                                    
				                                    <div class="entry-meta entry-meta-category">
                                                        <?php if( $ed_relevant_cat_carousel == 'yes' && $carousel_category ){ ?>

                                                            <span class="cat-links">
                                                                <a class="twp_cat_<?php echo esc_attr( $carousel_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                            </span>

                                                        <?php
                                                        }else{

                                                            infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                                        } ?>
                                                    </div>

				                                    <h3 class="entry-title entry-title-big">
				                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				                                    </h3>

				                                    <?php
			                                        if( class_exists( 'Booster_Extension_Class') && $ed_carouser_overlay_layout != 'yes' && $ed_social_icon ){

			                                            echo "<div class='archive-like-share'>";
			                                            $args = array('layout'=>'layout-2','status'=>'enable');
			                                            do_action('booster_extension_social_icons',$args);
			                                            echo "</div>";
			                                        } ?>
                                        
				                                </div>
				                            </div>
				                        </article>
				                    </div>
				                <?php } ?>
			                </div>
			            </div>
			        </div>
			    </div>
			</div>

        <?php
        wp_reset_postdata();
        endif;

    }

endif;