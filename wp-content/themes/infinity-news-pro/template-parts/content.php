<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Infinity_News
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> <?php if( is_archive() || ( is_home() && is_front_page() ) ){ ?> data-aos="fade-up" data-aos-delay="300" <?php } ?>
>

	<?php
	$default = infinity_news_get_default_theme_options();
    $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );
    $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
    $ed_social_share_on_single_page = absint( get_theme_mod( 'ed_social_share_on_single_page',$default['ed_social_share_on_single_page'] ) );
    $infinity_news_archive_layout = esc_html( get_theme_mod( 'infinity_news_archive_layout',$default['infinity_news_archive_layout'] ) );
    $ed_social_share_on_archive_page = esc_html( get_theme_mod( 'ed_social_share_on_archive_page',$default['ed_social_share_on_archive_page'] ) );
    $ed_like_dislike_archive = esc_html( get_theme_mod( 'ed_like_dislike_archive',$default['ed_like_dislike_archive'] ) );

	if( is_home() || is_archive() || is_front_page() ){
	?>
		<div class="post-wrapper">
	<?php 
		infinity_news_post_thumbnail();
	} ?>
		
		<div class="article-details <?php if( is_single() ){ echo 'single-article-details'; } ?>">
			
			<?php if ( 'post' === get_post_type() && is_single() ){ ?>
				<div class="twp-single-affix">

					<div class="entry-meta">
			            <?php
			            infinity_news_posted_by();
			            echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
			            infinity_news_posted_on();
			            ?>
		            </div>

		            <?php
		            if( $ed_social_share_on_single_page && class_exists( 'Booster_Extension_Class') ){
			            $args = array('status' => 'enable');
				        do_action('booster_extension_social_icons',$args);
				    }
			        ?>

		        </div><!-- .entry-meta -->
		    <?php } ?>

		    <div class="twp-post-content">

		    	<?php if ( 'post' === get_post_type() && is_single() ){ infinity_news_post_thumbnail(); } ?>

				<?php if( is_archive() || is_home() || is_front_page() ) :  ?>

					<header class="entry-header">

						<?php

						if ( 'post' === get_post_type() ){
		                    echo '<div class="entry-meta entry-meta-category">';
		                    infinity_news_entry_footer( $cats = true,$tags = false );
		                    echo '</div>';
		                }

						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>

						<div class="entry-meta">
	                        <?php
	                        infinity_news_posted_by();
	                        echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
	                        infinity_news_posted_on();
	                        ?>
	                    </div><!-- .entry-meta -->

					</header><!-- .entry-header -->

				<?php endif; ?>

				<div class="entry-content">
					<?php
					if( is_single() ):

						the_content();

					else:

						if( has_excerpt() ){

							the_excerpt();

						}else{
							
							echo esc_html( wp_trim_words( get_the_content(),30,'...') );
							
						}

						if( $infinity_news_archive_layout == 'archive-layout-2' && ( is_archive() || ( is_home() && !is_front_page() ) ) ){

                                if( class_exists( 'Booster_Extension_Class') && ( $ed_social_share_on_archive_page || $ed_like_dislike_archive ) ){
                            	
	                                echo "<div class='archive-like-share'>";
	                                if( $ed_social_share_on_archive_page ){
			                            $args = array('layout'=>'layout-2','status' => 'enable');
	                                	do_action('booster_extension_social_icons',$args);
	                                }
	                                if( $ed_like_dislike_archive ){
		                                do_action('booster_extension_like_dislike','allenable');
		                            }
	                                echo "</div>";
	                            }

						}else{

							if( class_exists( 'Booster_Extension_Class') && ( is_home() || is_archive() || is_front_page() ) ){

								echo "<div class='archive-like-share'>";

								if(  is_archive() || ( is_home() && !is_front_page() ) ){
									if( $ed_social_share_on_archive_page ){
										$args = array('layout'=>'layout-2','status' => 'enable');
										do_action('booster_extension_social_icons',$args);
									}
									
								}else{
									if( $ed_social_icon ){
										$args = array('layout'=>'layout-2','status' => 'enable');
										do_action('booster_extension_social_icons',$args);
									}
								}
						        
						        

						        if( is_front_page() && is_home() ){

							        $default = infinity_news_get_default_theme_options();
							        $twp_infinity_news_home_sections = get_theme_mod( 'twp_infinity_news_home_sections_55', json_encode( $default['twp_infinity_news_home_sections'] ) );
							        $twp_infinity_news_home_sections = json_decode( $twp_infinity_news_home_sections );

							        foreach( $twp_infinity_news_home_sections as $infinity_news_home_section ){

							            $home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;
										switch( $home_section_type ){

							                case 'latest-post':
							                $latest_post_layout = isset( $infinity_news_home_section->latest_post_layout ) ? $infinity_news_home_section->latest_post_layout : '' ;

									        if( class_exists( 'Booster_Extension_Class') && $ed_like_dislike && $latest_post_layout == 'index-layout-2' ){
							                    do_action('booster_extension_like_dislike','allenable');
							                }

							                break;

							            }

							        }
							    }

						        echo "</div>";
							}
						}

					endif;

					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'infinity-news' ),
						'after'  => '</div>',
					) );
					?>
				</div><!-- .entry-content -->

				<?php
					if( is_single() ){
						$tags = true;
					}else{
						$tags = false;
					}
				?>
				<footer class="entry-footer">
					<?php infinity_news_entry_footer( $cats = false,$tags ); ?>
				</footer><!-- .entry-footer -->
			</div>

		</div>

	<?php if( is_home() || is_archive() || is_front_page() ){ ?>
		</div>
	<?php } ?>

</article><!-- #post-<?php the_ID(); ?> -->