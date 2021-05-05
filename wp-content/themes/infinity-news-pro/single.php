<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Infinity_News
 */

get_header();
$default = infinity_news_get_default_theme_options();
global $post;
?>

    <div id="primary" class="content-area">

        <?php
        while (have_posts()) :
            the_post();
        ?>

            <div class="twp-banner-details">

                <?php

                if ( 'post' === get_post_type() ){
                    echo '<div class="entry-meta entry-meta-category">';
                    infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );
                    echo '</div>';
                }

                echo '<header class="entry-header">';
                
                echo '<h1 class="entry-title entry-title-full">';
                    the_title();
                echo '</h1>';  
                echo "</header>";

                ?>

            </div>

            <main id="main" class="site-main">

                    <?php get_template_part('template-parts/content', get_post_type()); ?>

                    <div class="twp-navigation-wrapper"><?php

                        // Previous/next post navigation.
                        the_post_navigation(array(
                            'next_text' => '<h2 class="entry-title entry-title-big" aria-hidden="true">' . esc_html__('Next', 'infinity-news') . '</h2> ' .
                                '<span class="screen-reader-text">' . esc_html__('Next post:', 'infinity-news') . '</span> ' .
                                '<h3 class="entry-title entry-title-medium">%title</h3>',
                            'prev_text' => '<h2 class="entry-title entry-title-big" aria-hidden="true">' . esc_html__('Previous', 'infinity-news') . '</h2> ' .
                                '<span class="screen-reader-text">' . esc_html__('Previous post:', 'infinity-news') . '</span> ' .
                                '<h3 class="entry-title entry-title-medium">%title</h3>',
                        )); ?>

                    </div><?php

                    /**
                     * Navigation
                     *
                     * @hooked infinity_news_post_floating_nav - 10
                    */
                    if ('post' === get_post_type()) {
                        do_action('infinity_news_navigation_action');
                    }
                    
                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;

                ?>

            </main><!-- #main -->

        <?php endwhile; ?>

    </div><!-- #primary -->

<?php
$global_sidebar_layout = esc_html( get_theme_mod( 'global_sidebar_layout', $default['global_sidebar_layout'] ) );
$infinity_news_post_sidebar_option = esc_html( get_post_meta( $post->ID, 'infinity_news_post_sidebar_option', true ) );

if ( $infinity_news_post_sidebar_option == 'global-sidebar' || empty( $infinity_news_post_sidebar_option ) ) {
    $infinity_news_post_sidebar_option = $global_sidebar_layout;
}

if ( $infinity_news_post_sidebar_option != 'no-sidebar' ):

    get_sidebar();

endif;

get_footer();
