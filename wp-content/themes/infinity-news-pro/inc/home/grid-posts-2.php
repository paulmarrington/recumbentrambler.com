<?php
/**
* Grid Posts 2 Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_grid_posts_2' ) ):

    // Header Grid Post.
    function infinity_news_grid_posts_2( $infinity_news_home_section ){
        
        $banner_2_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $grid_post_2_category_1 = isset( $infinity_news_home_section->post_category_1 ) ? $infinity_news_home_section->post_category_1 : '' ;
        $grid_post_2_category_2 = isset( $infinity_news_home_section->post_category_2 ) ? $infinity_news_home_section->post_category_2 : '' ;
        $ed_relevant_cat_grid_2 = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $ed_arrows_grid_2 = isset( $infinity_news_home_section->ed_arrows_carousel ) ? $infinity_news_home_section->ed_arrows_carousel : '' ;
        $ed_dots_grid_2 = isset( $infinity_news_home_section->ed_dots_carousel ) ? $infinity_news_home_section->ed_dots_carousel : '' ;
        $ed_autoplay_grid_2 = isset( $infinity_news_home_section->ed_autoplay_carousel ) ? $infinity_news_home_section->ed_autoplay_carousel : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;

        $post_category_post_num_1 = isset( $infinity_news_home_section->post_category_post_num_1 ) ? $infinity_news_home_section->post_category_post_num_1 : '' ;
        if( empty( $post_category_post_num_1 ) ){ $post_category_post_num = 3; }

        $post_category_post_num_2 = isset( $infinity_news_home_section->post_category_post_num_2 ) ? $infinity_news_home_section->post_category_post_num_2 : '' ;
        if( empty( $post_category_post_num_2 ) ){ $post_category_post_num = 4; }

        $default = infinity_news_get_default_theme_options();
        $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
        $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );

        $grid_post_2_query_1 = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => absint( $post_category_post_num_1 ), 'category_name' => esc_html( $grid_post_2_category_1 ) ) );
        $grid_post_2_query_2 = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => absint( $post_category_post_num_2 ), 'category_name' => esc_html( $grid_post_2_category_2 ) ) );
        
        if( $grid_post_2_category_1 ){
            $idObj1 = get_category_by_slug( $grid_post_2_category_1 ); 
            $id1 = $idObj1->term_id;
            $cat_name1 = $idObj1->name;
            $cat_link1 = get_category_link( $id1 );
        }

        if( $grid_post_2_category_2 ){
            $idObj2 = get_category_by_slug( $grid_post_2_category_2 ); 
            $id2 = $idObj2->term_id;
            $cat_name2 = $idObj2->name;
            $cat_link2 = get_category_link( $id2 );
        }

        if ( $ed_autoplay_grid_2 == 'yes' ) {
            $autoplay = 'true';
        }else{
            $autoplay = 'false';
        }
        if( $ed_dots_grid_2 == 'yes' ) {
            $dots = 'true';
        }else {
            $dots = 'false';
        }
        if( $ed_arrows_grid_2 == 'yes' ) {
            $arrows = 'true';
        }else {
            $arrows = 'false';
        }
        if( is_rtl() ) {
            $rtl = 'true';
        }else{
            $rtl = 'false';
        }
        ?>
        <div class="home-main-2 twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
            <div class="wrapper">

                <?php if( $banner_2_title ){ ?>
                    <div class="twp-row">
                        <div class="column">
                            <header class="block-title-wrapper">
                                <div class="hr-line"></div>

                                <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg">
                                    <?php echo esc_html( $banner_2_title ); ?>
                                </h2>

                            </header>
                        </div>
                    </div>
                <?php } ?>

                <div class="twp-row twp-row-sm">
                    <?php if( $grid_post_2_query_1->have_posts() ){ ?>
                        <div class="column column-six-1 column-full-sm">
                            <div class="block-bg-alt block-bg-alt-1 twp-match-height">
                                <div class="banner-slider" data-slick='{"autoplay": <?php echo esc_attr( $autoplay ); ?>, "dots": <?php echo esc_attr( $dots ); ?>, "arrows": <?php echo esc_attr( $arrows ); ?>, "rtl": <?php echo esc_attr( $rtl ); ?>}'>
                                    <?php while( $grid_post_2_query_1->have_posts() ){
                                        $grid_post_2_query_1->the_post();
                                        $featured_image_1 = wp_get_attachment_image_src( get_post_thumbnail_id(),'large' ); ?>
                                        <div class="banner-slider-item">
                                            <article class="story-leader story-leader-block">
                                                <div class="post-panel">
                                                    <div class="post-thumb">
                                                        <a href="<?php the_permalink(); ?>" class="data-bg data-bg-large" data-background="<?php echo esc_url( $featured_image_1[0] ); ?>">
                                                            <span class="data-bg-overlay"></span>
                                                        </a>
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
                                                            <?php if( $ed_relevant_cat_grid_2 == 'yes' && $grid_post_2_category_1 ){ ?>

                                                                <span class="cat-links">
                                                                    <a class="twp_cat_<?php echo esc_attr( $grid_post_2_category_1 ); ?>" href="<?php echo esc_url( $cat_link1 ); ?>" rel="category tag"><?php echo esc_html( $cat_name1 ); ?></a>
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

                                                        <div class="entry-description">
                                                            <?php
                                                            if( has_excerpt() ){
                                                              the_excerpt();
                                                            }else{
                                                              echo wp_kses_post( wp_trim_words( get_the_content(),50,'...') );
                                                            } ?>
                                                        </div>

                                                        <?php
                                                        if( class_exists( 'Booster_Extension_Class') && ( $ed_social_icon || $ed_like_dislike ) ){

                                                            echo "<div class='archive-like-share'>";
                                                            $args = array('layout'=>'layout-2','status'=>'enable');
                                                            if( $ed_social_icon ){
                                                                do_action('booster_extension_social_icons',$args);
                                                            }
                                                            if( $ed_like_dislike ){
                                                                do_action('booster_extension_like_dislike','allenable');
                                                            }
                                                            echo "</div>";
                                                        } ?>
                                                        
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php wp_reset_postdata(); } ?>

                    <?php if( $grid_post_2_query_2->have_posts() ){
                        while( $grid_post_2_query_2->have_posts() ){
                        $grid_post_2_query_2->the_post();
                        $featured_image_2 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>
                        <div class="column column-three-1 column-full-sm">
                            <article class="story-leader block-bg twp-match-height">
                                <div class="post-panel">
                                    <div class="post-thumb">
                                        <a href="<?php the_permalink(); ?>" class="data-bg data-bg-big" data-background="<?php echo esc_url($featured_image_2[0]); ?>"></a>

                                        <?php
                                        if( class_exists( 'Booster_Extension_Class') && $ed_like_dislike ){
                                            do_action('booster_extension_like_dislike','allenable');
                                        } ?>

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
                                            <?php if( $ed_relevant_cat_grid_2 == 'yes' && $grid_post_2_category_2 ){ ?>

                                                <span class="cat-links">
                                                    <a class="twp_cat_<?php echo esc_attr( $grid_post_2_category_2 ); ?>" href="<?php echo esc_url( $cat_link2 ); ?>" rel="category tag"><?php echo esc_html( $cat_name2 ); ?></a>
                                                </span>

                                            <?php
                                            }else{

                                                infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                            } ?>
                                        </div>

                                        <h3 class="entry-title entry-title-big">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h3>

                                        <div class="entry-meta">
                                            <?php
                                            infinity_news_posted_by();
                                            echo "<span class='sep-date-author'><i class='ion ion-ios-remove'></i></span>";
                                            infinity_news_posted_on();
                                            ?>
                                        </div><!-- .entry-meta -->

                                        <div class="entry-description">
                                            <?php
                                            if( has_excerpt() ){
                                              the_excerpt();
                                            }else{
                                              echo wp_kses_post( wp_trim_words( get_the_content(),30,'...') );
                                            } ?>
                                        </div>

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
                        <?php
                        }
                        wp_reset_postdata();
                    } ?>
                </div>
            </div>
        </div>
    <?php
    }

endif;