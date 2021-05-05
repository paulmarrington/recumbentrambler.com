<?php
/**
* Recommended Posts Function.
*
* @package Infinity News
*/

if( !function_exists('infinity_news_recommended_posts') ):

	// Recommended Posts Functions.
	function infinity_news_recommended_posts( $infinity_news_home_section ){

        $recommended_post_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $recommended_posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat_recommend = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
		$default = infinity_news_get_default_theme_options();

        $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
        $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );

        global $c_paged;
		$c_paged = ( get_query_var( 'page' ) ) ? absint( get_query_var( 'page' ) ) : 1;
        $recommended_post_query = new WP_Query( array( 'post_type' => 'post','posts_per_page' => 8, 'category_name' => esc_html( $recommended_posts_category ), 'c_paged'=>$c_paged ) );
        if( $recommended_posts_category ){
            $idObj = get_category_by_slug( $recommended_posts_category ); 
            $id = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id );
        }

        if ( $recommended_post_query->have_posts() ): ?>

        	<div class="site-recommended twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
			    <div class="wrapper">
			        
			         <?php if( $recommended_post_title ){ ?>
                        <div class="twp-row">
                            <div class="column">
                                <header class="block-title-wrapper">
                                    <div class="hr-line"></div>

                                    <h2 class="block-title">
                                        <?php echo esc_html( $recommended_post_title ); ?>
                                    </h2>

                                    <?php if( $recommended_posts_category ){ ?>
                                        <div class="title-controls">
                                            <a href="<?php echo esc_url( $cat_link ); ?>">
                                                <?php esc_html_e( 'View More','infinity-news' ); ?>
                                            </a>
                                        </div>
                                    <?php } ?>

                                </header>
                            </div>
                        </div>
                    <?php } ?>

			        <div class="twp-row recommended-post-wraper">
			        	<?php while( $recommended_post_query->have_posts() ):
			        		$recommended_post_query->the_post();

			        		$format = get_post_format( get_the_ID() ) ? : 'standard';
                            $icon = infinity_news_post_formate_icon( $format );
                            $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

				            <div class="column column-quarter column-five-sm recommended-load" data-mh="recommended-item">
				                <article class="recommended-article">
                                    <div class="post-panel block-bg-rev" data-mh="recommended-panel">
                                        <div class="post-thumb">
                                            <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="data-bg data-bg-medium" data-background="<?php echo esc_url( $featured_image[0] ); ?>">
                                            </a>

                                            <?php
                                            if( class_exists( 'Booster_Extension_Class') && $ed_like_dislike ){
                                                do_action('booster_extension_like_dislike','allenable');
                                            } ?>
                                            
                                            <?php if( !empty( $icon ) ){ ?>
                                                <span class="format-icon">
                                                    <i class="ion <?php echo esc_attr( $icon ); ?>"></i>
                                                </span>
                                            <?php } ?>
                                        </div>

                                        <div class="entry-content">
                                            
                                            <div class="entry-meta entry-meta-category">
                                                <?php if( $ed_relevant_cat_recommend == 'yes' && $recommended_posts_category ){ ?>

                                                    <span class="cat-links">
                                                        <a class="twp_cat_<?php echo esc_attr( $recommended_posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                    </span>

                                                <?php
                                                }else{

                                                    infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                                } ?>
                                            </div>

                                            <h3 class="entry-title entry-title-medium">
                                                <a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
                                            </h3>

                                            <?php
                                            if( class_exists( 'Booster_Extension_Class') && $ed_social_icon ){

                                                echo "<div class='archive-like-share'>";
                                                $args = array('layout'=>'layout-2','status'=>'enable');
                                                do_action('booster_extension_social_icons',$args);
                                                echo "</div>";
                                            } ?>
                                        </div>
                                    </div>
				                </article>
				            </div>
			        	<?php endwhile; ?>
			        </div>

			        <a href="javascript:void(0)" class="infinity-btn">
                        <span class="loadmore"><?php echo esc_html('Load More Posts','infinity-news'); ?></span>
                    </a>

			    </div>
			</div>

		<?php
		wp_reset_postdata();
        endif;

	}

endif;