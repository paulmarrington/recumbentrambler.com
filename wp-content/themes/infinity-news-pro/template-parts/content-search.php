<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Infinity_News
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-aos="fade-up" data-aos-delay="300">

	<div class="post-wrapper">

		<?php infinity_news_post_thumbnail(); ?>

		<div class="article-details">

			<header class="entry-header">

				<?php
				if ( 'post' === get_post_type() ){
                    echo '<div class="entry-meta entry-meta-category">';
                    infinity_news_entry_footer( $cats = true,$tags = false );
                    echo '</div>';
                }

				the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

				<?php if ( 'post' === get_post_type() ) : ?>
				<div class="entry-meta">
                    <?php
                    infinity_news_posted_by();
                    echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
                    infinity_news_posted_on();
                    ?>
                </div><!-- .entry-meta -->
				<?php endif; ?>
			</header><!-- .entry-header -->

			<div class="entry-summary">
				<?php 
				if( has_excerpt() ){
					
					the_excerpt();

				}else{

					echo esc_html( wp_trim_words( get_the_content(),30,'...' ) );

				}
				?>
			</div><!-- .entry-summary -->
			
		</div>
		
	</div>

</article><!-- #post-<?php the_ID(); ?> -->
