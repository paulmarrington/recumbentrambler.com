<?php
/**
* Recommended Posts Function.
*
* @package Infinity News
*/

add_action('wp_ajax_infinity_news_recommended_posts', 'infinity_news_recommended_posts_callback');
add_action('wp_ajax_nopriv_infinity_news_recommended_posts', 'infinity_news_recommended_posts_callback');

// Recommendec Post Ajax Call Function.
function infinity_news_recommended_posts_callback() {

    if( isset( $_POST['page'] ) && absint( wp_unslash( $_POST['page'] ) ) ){

        $paged = absint( wp_unslash( $_POST['page'] ) );
        $default = infinity_news_get_default_theme_options();
        $twp_infinity_news_home_sections = get_theme_mod( 'twp_infinity_news_home_sections_55', json_encode( $default['twp_infinity_news_home_sections'] ) );
        $twp_infinity_news_home_sections = json_decode( $twp_infinity_news_home_sections );
        $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
        $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );

        foreach( $twp_infinity_news_home_sections as $infinity_news_home_section ){

            $home_section_type = isset( $infinity_news_home_section->home_section_type ) ? $infinity_news_home_section->home_section_type : '' ;
            switch( $home_section_type ){

                case 'recommended-posts':
                    
                    $recommended_posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
                    $ed_relevant_cat_recommend = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;

                    if( $recommended_posts_category ){
                        $idObj = get_category_by_slug( $recommended_posts_category ); 
                        $id = $idObj->term_id;
                        $cat_name = $idObj->name;
                        $cat_link = get_category_link( $id );
                    }

                    $recommended_post_query = new WP_Query( array( 'post_type' => 'post','posts_per_page' => 8, 'category_name' => esc_html( $recommended_posts_category ), 'paged'=> absint( $paged ) ) );

                    if ( $recommended_post_query->have_posts() ) :
                        while ( $recommended_post_query->have_posts() ) : $recommended_post_query->the_post();

                            $format = get_post_format( get_the_ID() ) ? : 'standard';
                            $icon = infinity_news_post_formate_icon( $format );
                            $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                            <div class="column column-quarter column-five-sm recommended-load after-load-<?php echo esc_attr( $paged ); ?>" data-mh="recommended-item">
                                <article class="recommended-article">
                                    <div class="post-panel block-bg-rev">
                                        <div class="post-thumb">
                                            
                                            <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="data-bg data-bg-medium" data-background="<?php echo esc_url( $featured_image[0] ); ?>">
                                            </a>

                                            <?php
                                            if( class_exists( 'Booster_Extension_Class') && $ed_like_dislike ){
                                                do_action('booster_extension_like_dislike','allenable');
                                            } ?>

                                            <?php if( !empty( $icon ) ){ ?>
                                                <span class="format-icon">
                                                        <i class="ion <?php echo esc_attr( $icon ); ?>"></i>
                                                    </span>
                                            <?php } ?>

                                        </div>

                                        <div class="entry-content">
                                            
                                            <div class="entry-meta entry-meta-category">
                                                <?php if( $ed_relevant_cat_recommend == 'yes' && $recommended_posts_category ){ ?>

                                                    <span class="cat-links">
                                                        <a href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
                                                    </span>

                                                <?php
                                                }else{

                                                    infinity_news_entry_footer( $cats = true,$tags = false,$edits = false );

                                                } ?>
                                            </div>

                                            <h3 class="entry-title entry-title-medium">
                                                <a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a>
                                            </h3>

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
                    endwhile;

                    wp_reset_postdata();

                    endif;

                break;

            }

        }

    }

    wp_die();
}