<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Infinity News
 */

get_header();
$default = infinity_news_get_default_theme_options();
$twp_infinity_news_home_sections = get_theme_mod( 'twp_infinity_news_home_sections_55', json_encode( $default['twp_infinity_news_home_sections'] ) );
$paged_active = false;
if ( !is_paged() ) {
	$paged_active = true;
}
$twp_infinity_news_home_sections = json_decode( $twp_infinity_news_home_sections );

foreach( $twp_infinity_news_home_sections as $infinity_news_home_section ){
	
	$home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;
	switch( $home_section_type ){

		case 'grid-posts':

		$ed_grid_1 = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

		if( $ed_grid_1 == 'yes' && $paged_active ){
			infinity_news_grid_posts( $infinity_news_home_section );
		}

        break;

        case 'grid-posts-2':

		$ed_grid_2 = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

		if( $ed_grid_2 == 'yes' && $paged_active ){
			infinity_news_grid_posts_2( $infinity_news_home_section );
		}

        break;

        case 'carousel-posts':
		
        $ed_carousel_posts = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		if( $ed_carousel_posts == 'yes' && $paged_active ){
	        infinity_news_carousel_posts( $infinity_news_home_section );
	    }

        break;

        case 'jumbotron-block':
		
        $ed_jumbotron = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

		if( $ed_jumbotron == 'yes' && $paged_active ){
			infinity_news_jumbotron_posts( $infinity_news_home_section );
		}

        break;

        case 'multiple-category-posts':
		
		$ed_multiple_cat_posts = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

		if( $ed_multiple_cat_posts == 'yes' && $paged_active ){
	        infinity_news_multipal_cat_posts( $infinity_news_home_section );
	    }

        break;

        case 'latest-post':
        $latest_post_ed = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
        $sidebar_layout = isset( $infinity_news_home_section->sidebar_layout ) ? $infinity_news_home_section->sidebar_layout : '' ;
        if( $latest_post_ed == 'yes'){ ?>
			<div id="content" class="site-content <?php if ( is_paged() ){ echo 'twp-frontpage-paged'; } ?>">
				<div id="primary" class="content-area">
					<main id="main" class="site-main">
						
						<?php 
				        if( is_front_page() ):
				            infinity_news_slider( $infinity_news_home_section );
				        endif;
					    
						if ( have_posts() ) :

							if ( is_home() && ! is_front_page() ) :
								?>
								<header>
									<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
								</header>
								<?php
							endif; ?>

							<div class="article-wraper">
								<?php
								/* Start the Loop */
								while ( have_posts() ) :
									the_post();

									/*
									 * Include the Post-Type-specific template for the content.
									 * If you want to override this in a child theme, then include a file
									 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
									 */
									get_template_part( 'template-parts/content', get_post_type() );

								endwhile; ?>
							</div>

							<?php infinity_news_posts_navigation();

						else :

							get_template_part( 'template-parts/content', 'none' );

						endif;
						?>

					</main><!-- #main -->
				</div><!-- #primary -->

				<?php
				if( $sidebar_layout != 'no-sidebar' ):
					get_sidebar();
				endif; ?>

				<?php
				if ( !is_home() && is_front_page() ) :

					$global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout',$default['global_sidebar_layout'] ) );
					$infinity_news_post_sidebar_option = esc_html( get_post_meta( $post->ID, 'infinity_news_post_sidebar_option', true ) );

					if( $infinity_news_post_sidebar_option == 'global-sidebar' || empty( $infinity_news_post_sidebar_option ) ){
						$infinity_news_post_sidebar_option = $global_sidebar_layout;
					}

					if( $infinity_news_post_sidebar_option != 'no-sidebar' ):

						if( infinity_news_check_woocommerce_page() ){

							if ( is_active_sidebar( 'infinity-news-woocommerce-widget' ) ) { ?>

								<aside id="secondary" class="widget-area">
									<?php dynamic_sidebar( 'infinity-news-woocommerce-widget' ); ?>
								</aside><!-- #secondary -->
								
							<?php
							}
							
						}else{
							get_sidebar();
						}
						

					endif;

				endif; ?>

			</div><?php
			}
        break;

        case 'recommended-posts':
		
		$ed_recommended_posts = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		
		if( $ed_recommended_posts == 'yes' && $paged_active ){
	        infinity_news_recommended_posts( $infinity_news_home_section );
	    }

        break;

        case 'advertise-area':
		
		$ed_advertise = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		
		if( $ed_advertise == 'yes' && $paged_active ){
	        infinity_news_advertise( $infinity_news_home_section );
	    }

        break;

        case 'mailchimp':
		
		$ed_mailchimp = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		
		if( $ed_mailchimp == 'yes' && $paged_active ){
		    infinity_news_mailchimp( $infinity_news_home_section );
		}

        break;

        case 'video':
		$ed_video = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		if( $ed_video == 'yes' && $paged_active ){
		    infinity_news_video_posts( $infinity_news_home_section );
		}

        break;

        case 'banner-block-tiles':
		$ed_blog_tiles = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		if( $ed_blog_tiles == 'yes' && $paged_active ){
		    infinity_news_block_tiles_posts( $infinity_news_home_section );
		}

        break;

        case 'slide':
		$ed_slide_posts = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		if( $ed_slide_posts == 'yes' && $paged_active ){
		    infinity_news_slide_posts( $infinity_news_home_section );
		}

        break;

        case 'tab':
		$ed_tab_posts = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		if( $ed_tab_posts == 'yes' && $paged_active ){
		    infinity_news_tab_posts( $infinity_news_home_section );
		}

        break;

        case 'seperator':
		$sep_ed = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;
		$section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
		if( $sep_ed == 'yes' && $paged_active ){
		   ?><div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class='twp-sep-block'></div> <?php
		}

        break;

        default:

		$ed_grid_1 = isset( $infinity_news_home_section->section_ed ) ? $infinity_news_home_section->section_ed : '' ;

		if( $ed_grid_1 == 'yes' && $paged_active ){
			infinity_news_grid_posts( $infinity_news_home_section );
		}

		break;

	}

}

get_footer();
