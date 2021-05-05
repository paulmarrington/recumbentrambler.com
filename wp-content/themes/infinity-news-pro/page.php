<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Infinity News
 */

get_header();
$default = infinity_news_get_default_theme_options();
global $post;
	
	if( is_front_page() ){ ?>
		<div id="content" class="site-content">
	<?php } ?>

		<div id="primary" class="content-area">

			<?php
			while (have_posts()) :
            	the_post(); ?>
            	
				<div class="twp-banner-details">

				    <?php
		            echo '<header class="entry-header">';
		            
		                echo '<h1 class="entry-title entry-title-full">';
		                the_title();
		                echo '</h1>';

		            echo "</header>";

		            infinity_news_post_thumbnail();
					?>

				</div>

				<main id="main" class="site-main">

					<?php

			        if( is_front_page() && !is_home() ){ ?>
			        	<header class="entry-header"><h1 class="entry-title entry-title-full"><?php the_title(); ?></h1></header>
			        <?php }

					get_template_part( 'template-parts/content', 'page' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

					?>

				</main><!-- #main -->
			
			<?php endwhile; ?>

		</div><!-- #primary -->

		<?php
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
			

		endif; ?>
	<?php if( is_front_page() ){ ?>
		</div>
	<?php } ?>
	<?php
get_footer();
