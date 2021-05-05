<?php
/**
* Slide Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_tab_posts' ) ):

    // Header Grid Post.
    function infinity_news_tab_posts( $infinity_news_home_section ){

        $section_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $banner_tiles_layout = isset( $infinity_news_home_section->banner_tiles_layout ) ? $infinity_news_home_section->banner_tiles_layout : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;
        $ed_title_control = isset( $infinity_news_home_section->ed_title_control ) ? $infinity_news_home_section->ed_title_control : '' ;

        $default = infinity_news_get_default_theme_options();
        $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );
        $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );

        $block_tab_posts_query = new WP_Query(
            array(
                'post_type' => 'post',
                'posts_per_page' => 2,
                'category_name' => esc_html( $posts_category ),
                'post__not_in' => get_option("sticky_posts"),
            )
        );

        if( $posts_category ){
            $idObj = get_category_by_slug( $posts_category ); 
            $id = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id );
        }
        ?>
        <div class="banner-tiles-layout twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
            <div class="wrapper">
                <?php if( $section_title || $ed_title_control == 'yes' ){ ?>
                    <div class="twp-row">
                        <div class="column">
                            <header class="block-title-wrapper">
                                <div class="hr-line"></div>

                                <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg">
                                    <?php echo esc_html( $section_title ); ?>
                                </h2>

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
                <?php } ?>
            </div>

            <?php if( $block_tab_posts_query->have_posts() ){ ?>

                <div class="wrapper">
                    <div class="twp-row">

                        <?php if( $banner_tiles_layout == 'layout-2' ){
                            infinity_news_tab_1( $infinity_news_home_section );
                        } ?>

                        <?php
                        $i = 1;
                        while( $block_tab_posts_query->have_posts() ){
                            $block_tab_posts_query->the_post();
                            $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(),'largr' ); ?>

                            <div class="column column-three-1 column-full-sm">
                                <article class="tabbed-post-article">
                                    <div class="post-panel block-bg" data-mh="tabbed-post-panel">

                                        <div class="post-thumb">
                                            <a href="<?php the_permalink(); ?>" class="data-bg data-bg-large data-bg-altlarge" data-background="<?php echo esc_url( $featured_image[0] ); ?>"></a>

                                            <?php
                                            if( class_exists( 'Booster_Extension_Class') && $ed_like_dislike ){
                                                do_action('booster_extension_like_dislike','allenable');
                                            } ?>

                                        </div>

                                        <div class="entry-content">

                                            <div class="entry-meta entry-meta-category">
                                                <?php if( $ed_relevant_cat == 'yes' && $posts_category ){ ?>

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

                            <?php if( $i == 1 && $banner_tiles_layout == 'layout-3' ){
                                infinity_news_tab_1( $infinity_news_home_section );
                            } ?>

                        <?php $i++; }
                        wp_reset_postdata(); ?>

                        <?php if( $banner_tiles_layout == 'layout-1' || $banner_tiles_layout == '' ){
                            infinity_news_tab_1( $infinity_news_home_section );
                        } ?>

                    </div>
                </div>
            <?php } ?>

        </div>
        <?php
       
    }
endif;

function infinity_news_tab_1( $infinity_news_home_section ){ ?>

    <div class="column column-three-1 column-full-sm">
        <div class="tabbed-widget-block block-bg">
            <?php
            $tab_enable_desc = isset( $infinity_news_home_section->tab_enable_desc ) ? $infinity_news_home_section->tab_enable_desc : '' ;
            $posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
            if( $tab_enable_desc == 'yes' ){
                $tab_enable_desc = 'on';
            }else{
                $tab_enable_desc = '';
            }
            $tab_no_of_posts = isset( $infinity_news_home_section->tab_no_of_posts ) ? $infinity_news_home_section->tab_no_of_posts : '' ;

            $tab_image_size = isset( $infinity_news_home_section->tab_image_size ) ? $infinity_news_home_section->tab_image_size : '' ;
            $excerpt_length = isset( $infinity_news_home_section->excerpt_length ) ? $infinity_news_home_section->excerpt_length : '' ;
            $tab_no_of_recent_posts = isset( $infinity_news_home_section->tab_no_of_recent_posts ) ? $infinity_news_home_section->tab_no_of_recent_posts : '' ;
            $tab_no_of_comments_posts = isset( $infinity_news_home_section->tab_no_of_comments_posts ) ? $infinity_news_home_section->tab_no_of_comments_posts : '' ;

            $instance = array( 'popular_number' => $tab_no_of_posts,'enable_discription' => $tab_enable_desc,'select_image_size' => $tab_image_size,'excerpt_length' => $excerpt_length,'recent_number' => $tab_no_of_recent_posts,'comments_number' => $tab_no_of_comments_posts,'change_structure_img' => 'change','tab_cat' => $posts_category  );

            the_widget( 'Infinity_News_Tab_Posts_Widget', $instance );
            ?>
        </div>
    </div>
<?php
}