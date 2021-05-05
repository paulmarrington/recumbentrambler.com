<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Infinity_News
 */

get_header();
$default = infinity_news_get_default_theme_options();
?>

	<section id="primary" class="content-area">

		<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

			<div class="article-wraper">

				<?php
				/* Start the Loop */
				while ( have_posts() ) :
					the_post();

					/**
					 * Run the loop for the search to output the results.
					 * If you want to overload this in a child theme then include a file
					 * called content-search.php and that will be used instead.
					 */
					get_template_part( 'template-parts/content', 'search' );

				endwhile; ?>

			</div>
		
			<?php infinity_news_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
$global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout',$default['global_sidebar_layout'] ) );
if( $global_sidebar_layout != 'no-sidebar' ):
	get_sidebar();
endif;
get_footer();