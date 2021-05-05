<?php
/**
* Grid Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_grid_posts' ) ):

    // Header Grid Post.
    function infinity_news_grid_posts( $infinity_news_home_section ){

        $banner_1_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $grid_posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat_grid_1 = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '';
        $ed_title_control_grid = isset( $infinity_news_home_section->ed_title_control ) ? $infinity_news_home_section->ed_title_control : '';
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '';
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '';
        $post_category_post_num = isset( $infinity_news_home_section->post_category_post_num ) ? $infinity_news_home_section->post_category_post_num : '';
        if( empty( $post_category_post_num ) ){ $post_category_post_num = 13; }

        $grid_post_query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => absint( $post_category_post_num ), 'category_name' => esc_html( $grid_posts_category ) ) );

        if( $grid_posts_category ){
            $idObj = get_category_by_slug( $grid_posts_category ); 
            $id = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id );
        }

        if ( $grid_post_query->have_posts() ): ?>

            <div class="home-main twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
                <div class="wrapper">

                    <?php if( $banner_1_title ){ ?>
                        <div class="twp-row">
                            <div class="column">
                                <header class="block-title-wrapper">
                                    <div class="hr-line"></div>

                                    <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg">
                                        <?php echo esc_html( $banner_1_title ); ?>
                                    </h2>

                                    <?php if( $grid_posts_category && $ed_title_control_grid == 'yes' ){ ?>
                                        <div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="title-controls title-controls-bg">
                                            <a href="<?php echo esc_url( $cat_link ); ?>">
                                                <?php esc_html_e( 'View More','infinity-news' ); ?>
                                            </a>
                                        </div>
                                    <?php } ?>

                                </header>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="twp-row twp-row-sm">

                        <?php 
                        $i = 1;
                        while( $grid_post_query->have_posts() ):
                        $grid_post_query->the_post();

                            if( $i == 1){

                                $featured_image_big = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

                                <article class="story-leader">
                                    <div class="column column-three-1 column-full-md column-full-sm">
                                        <div class="post-panel block-bg-alt">
                                            <div class="post-thumb">
                                                <a href="<?php the_permalink(); ?>" class="data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_big[0]); ?>"></a>
                                                <?php $format = get_post_format( get_the_ID() ) ? : 'standard';
                                                $icon = infinity_news_post_formate_icon( $format );
                                                if( !empty( $icon ) ){ ?>
                                                    <span class="format-icon">
                                                            <i class="ion <?php echo esc_attr( $icon ); ?>"></i>
                                                        </span>
                                                <?php } ?>
                                            </div>
                                            <div class="entry-content">
                                                
                                                <div class="entry-meta entry-meta-category">
                                                    <?php if( $ed_relevant_cat_grid_1 == 'yes' && $grid_posts_category ){ ?>

                                                        <span class="cat-links">
                                                            <a class="twp_cat_<?php echo esc_attr( $grid_posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                        </span>

                                                    <?php
                                                    }else{

                                                        infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                                    } ?>
                                                </div>

                                                <h3 class="entry-title entry-title-large">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h3>

                                                <div class="entry-meta">
                                                    <?php
                                                    infinity_news_posted_by();
                                                    echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
                                                    infinity_news_posted_on();
                                                    ?>
                                                </div><!-- .entry-meta -->
                                            </div>
                                        </div>
                                    </div>
                                </article>

                            <?php }else{

                                $featured_image_small = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                                <article class="story-list">
                                    <div class="column column-three-1 column-five-md column-five-sm">
                                        <div class="post-panel block-bg">
                                            <div class="post-thumb">
                                                <a href="<?php the_permalink(); ?>" class="data-bg data-bg-small" data-background="<?php echo esc_url( $featured_image_small[0]); ?>">

                                                </a>
                                            </div>
                                            <div class="entry-content">
                                                
                                                <div class="entry-meta entry-meta-category">
                                                    <?php if( $ed_relevant_cat_grid_1 == 'yes' && $grid_posts_category ){ ?>

                                                        <span class="cat-links">
                                                            <a class="twp_cat_<?php echo esc_attr( $grid_posts_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                        </span>

                                                    <?php
                                                    }else{

                                                        infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                                    } ?>
                                                </div>

                                                <h3 class="entry-title entry-title-medium">
                                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                </h3>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </article>

                            <?php }
                            $i++;
                        endwhile; ?>

                    </div>
                </div>
            </div>

        <?php
        wp_reset_postdata();
        endif;

    }

endif;