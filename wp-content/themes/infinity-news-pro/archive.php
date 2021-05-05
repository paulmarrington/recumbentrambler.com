<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Infinity_News
 */

get_header();
$default = infinity_news_get_default_theme_options();
?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

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
$global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout', $default['global_sidebar_layout'] ) );
if( $global_sidebar_layout != 'no-sidebar' ):
	get_sidebar();
endif;
get_footer();
