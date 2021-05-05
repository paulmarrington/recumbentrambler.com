<?php
/**
* Grid Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_block_tiles_posts' ) ):

    // Header Grid Post.
    function infinity_news_block_tiles_posts( $infinity_news_home_section ){

        $section_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $ed_title_control = isset( $infinity_news_home_section->ed_title_control ) ? $infinity_news_home_section->ed_title_control : '' ;
        $banner_tiles_layout = isset( $infinity_news_home_section->banner_tiles_layout ) ? $infinity_news_home_section->banner_tiles_layout : '' ;
        $ed_excerpt_content = isset( $infinity_news_home_section->ed_excerpt_content ) ? $infinity_news_home_section->ed_excerpt_content : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;

        $block_tiles_query = new WP_Query(
            array(
                'post_type' => 'post',
                'posts_per_page' => 5,
                'category_name' => esc_html( $posts_category ),
                'post__not_in' => get_option("sticky_posts"),
            )
        );

        $block_tiles_post_array_1 = array();
        $block_tiles_post_array_2 = array();
        $block_tiles_post_array_3 = array();

        if( $block_tiles_query->have_posts() ){

            $i = 1;

            while( $block_tiles_query->have_posts() ){
                $block_tiles_query->the_post();

                if( $i == 1 || $i == 2 ){
                    $block_tiles_post_array_1[] = get_the_ID();
                }elseif( $i == 3 ){
                    $block_tiles_post_array_2[] = get_the_ID();
                }else{
                    $block_tiles_post_array_3[] = get_the_ID();
                }

            $i++;
            }

            wp_reset_postdata();

        }

        if( $block_tiles_post_array_1 ){

            $block_tiles_query_1 = new WP_Query(
                array(
                    'post_type' => 'post',
                    'posts_per_page' => 2,
                    'post__in' => $block_tiles_post_array_1,
                    'post__not_in' => get_option("sticky_posts"),
                )
            );

        }

        if( $block_tiles_post_array_2 ){

            $block_tiles_query_2 = new WP_Query(
                array(
                    'post_type' => 'post',
                    'posts_per_page' => 1,
                    'post__in' => $block_tiles_post_array_2,
                    'post__not_in' => get_option("sticky_posts"),
                )
            );

        }

        if( $block_tiles_post_array_3 ){

            $block_tiles_query_3 = new WP_Query(
                array(
                    'post_type' => 'post',
                    'posts_per_page' => 2,
                    'post__in' => $block_tiles_post_array_3,
                    'post__not_in' => get_option("sticky_posts"),
                )
            );

        } ?>

        <div class="home-main-3 twp-blocks <?php echo 'banner-tiles-'.esc_attr( $banner_tiles_layout ); ?> <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
            <div class="wrapper">

                <div class="twp-row">
                    <div class="column">
                        <header class="block-title-wrapper">

                            <div class="hr-line"></div>
                            <?php if( $section_title ){ ?>
                                <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg"><?php echo esc_html( $section_title ); ?></h2>
                            <?php } ?>

                            <?php if( $posts_category && $ed_title_control == 'yes' ){ ?>
                                <div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="title-controls title-controls-bg">
                                    <a href="<?php echo esc_url( $cat_link ); ?>">
                                        <?php esc_html_e( 'View More','infinity-news' ); ?>
                                    </a>
                                </div>
                            <?php } ?>

                        </header>
                    </div>
                </div>

                <div class="twp-row twp-row-sm">

                    <?php if( $banner_tiles_layout == 'layout-2' ){

                        
                        if( $block_tiles_post_array_2 ){ infinity_news_banner_tile_block_2( $block_tiles_query_2,$ed_relevant_cat,$posts_category,$ed_excerpt_content ); }
                        if( $block_tiles_post_array_3 ){ infinity_news_banner_tile_block_3( $block_tiles_query_3,$ed_relevant_cat,$posts_category ); }
                        if( $block_tiles_post_array_1 ){ infinity_news_banner_tile_block_1( $block_tiles_query_1,$ed_relevant_cat,$posts_category ); }

                    }elseif( $banner_tiles_layout == 'layout-3' ){

                        if( $block_tiles_post_array_1 ){ infinity_news_banner_tile_block_1( $block_tiles_query_1,$ed_relevant_cat,$posts_category ); }
                        if( $block_tiles_post_array_3 ){ infinity_news_banner_tile_block_3( $block_tiles_query_3,$ed_relevant_cat,$posts_category ); }
                        if( $block_tiles_post_array_2 ){ infinity_news_banner_tile_block_2( $block_tiles_query_2,$ed_relevant_cat,$posts_category,$ed_excerpt_content ); }

                    }else{

                        if( $block_tiles_post_array_1 ){ infinity_news_banner_tile_block_1( $block_tiles_query_1,$ed_relevant_cat,$posts_category ); }
                        if( $block_tiles_post_array_2 ){ infinity_news_banner_tile_block_2( $block_tiles_query_2,$ed_relevant_cat,$posts_category,$ed_excerpt_content ); }
                        if( $block_tiles_post_array_3 ){ infinity_news_banner_tile_block_3( $block_tiles_query_3,$ed_relevant_cat,$posts_category ); }

                    } ?>

                </div>
            </div>
        </div>

    <?php
    }
endif;

function infinity_news_banner_tile_block_1( $block_tiles_query_1,$ed_relevant_cat,$posts_category ){

    ?>
    <div class="column column-quarter">

        <?php
        if( $posts_category ){
            $idObj = get_category_by_slug( $posts_category ); 
            $id = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id );
        }

        while( $block_tiles_query_1->have_posts() ){
            $block_tiles_query_1->the_post(); 
             $featured_image_1 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

            <article class="story-leader story-leader-jumbotron story-leader-tiles">
                <div class="post-panel block-bg-alt">
                    <div class="post-thumb">
                        <a href="" class="data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_1[0] ); ?>">
                            <span class="data-bg-overlay"></span>
                        </a>
                    </div>
                    <div class="entry-content">

                        <div class="entry-meta entry-meta-category">
                            <?php if( $ed_relevant_cat == 'yes' ){ ?>

                                <span class="cat-links">
                                    <a class="twp_cat_<?php echo esc_attr( $posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                </span>

                            <?php
                            }else{

                                infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                            } ?>
                        </div>

                        <h3 class="entry-title entry-title-big">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                    </div>
                </div>
            </article>

        <?php }
        wp_reset_postdata(); ?>

    </div>
    <?php
}

function infinity_news_banner_tile_block_2( $block_tiles_query_2,$ed_relevant_cat,$posts_category,$ed_excerpt_content ){

    if( $posts_category ){
        $idObj = get_category_by_slug( $posts_category ); 
        $id = $idObj->term_id;
        $cat_name = $idObj->name;
        $cat_link = get_category_link( $id );
    }
    while( $block_tiles_query_2->have_posts() ){
        $block_tiles_query_2->the_post();
        $featured_image_2 = wp_get_attachment_image_src( get_post_thumbnail_id(),'large' ); ?>
        
        <div class="column column-five">
            <article class="story-leader story-leader-jumbotron story-leader-tiles">
                <div class="post-panel block-bg-alt">
                    <div class="post-thumb">
                        <a href="" class="data-bg data-bg-large" data-background="<?php echo esc_url( $featured_image_2[0] ); ?>">
                            <span class="data-bg-overlay"></span>
                        </a>
                    </div>
                    <div class="entry-content">
                        
                        <div class="entry-meta entry-meta-category">
                            <?php if( $ed_relevant_cat == 'yes' ){ ?>

                                <span class="cat-links">
                                    <a class="twp_cat_<?php echo esc_attr( $posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                </span>

                            <?php
                            }else{

                                infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                            } ?>
                        </div>

                        <h3 class="entry-title entry-title-full">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
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
                                  echo wp_kses_post( wp_trim_words( get_the_content(),10,'...') );
                                } ?>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </article>
        </div>
        <?php
    }
    wp_reset_postdata();

}

function infinity_news_banner_tile_block_3( $block_tiles_query_3,$ed_relevant_cat,$posts_category ){ ?>
        
        <div class="column column-quarter">

            <?php
            if( $posts_category ){
                $idObj = get_category_by_slug( $posts_category ); 
                $id = $idObj->term_id;
                $cat_name = $idObj->name;
                $cat_link = get_category_link( $id );
            }
            while( $block_tiles_query_3->have_posts() ){
                $block_tiles_query_3->the_post(); 
                $featured_image_3 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' );?>

                <article class="story-leader story-leader-jumbotron story-leader-tiles">
                    <div class="post-panel block-bg-alt">
                        <div class="post-thumb">
                            <a href="" class="data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_3[0] ); ?>">
                                <span class="data-bg-overlay"></span>
                            </a>
                        </div>
                        <div class="entry-content">
                            
                            <div class="entry-meta entry-meta-category">
                                <?php if( $ed_relevant_cat == 'yes' ){ ?>

                                    <span class="cat-links">
                                        <a class="twp_cat_<?php echo esc_attr( $posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                    </span>

                                <?php
                                }else{

                                    infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                } ?>
                            </div>

                            <h3 class="entry-title entry-title-big">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                        </div>
                    </div>
                </article>
            <?php }
            wp_reset_postdata(); ?>

        </div>
    <?php

}