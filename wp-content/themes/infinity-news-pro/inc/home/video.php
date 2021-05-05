<?php
/**
* Grid Posts Function.
*
* @package Infinity News
*/

if ( !function_exists( 'infinity_news_video_posts' ) ):

    // Header Grid Post.
    function infinity_news_video_posts( $infinity_news_home_section ){

        infinity_news_booster_like_dislike_disable();
        $section_title = isset( $infinity_news_home_section->section_title ) ? $infinity_news_home_section->section_title : '' ;
        $posts_category = isset( $infinity_news_home_section->post_category ) ? $infinity_news_home_section->post_category : '' ;
        $ed_relevant_cat = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
        $ed_title_control = isset( $infinity_news_home_section->ed_title_control ) ? $infinity_news_home_section->ed_title_control : '' ;
        $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
        $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;

        $post_category_post_num = isset( $infinity_news_home_section->post_category_post_num ) ? $infinity_news_home_section->post_category_post_num : '' ;

        if( empty( $post_category_post_num ) ){ $post_category_post_num = 9; }

        if( $posts_category ){
            $idObj = get_category_by_slug( $posts_category ); 
            $id1 = $idObj->term_id;
            $cat_name = $idObj->name;
            $cat_link = get_category_link( $id1 );
        }


        $video_posts_post_query = new WP_Query(
            array(
                'post_type' => 'post',
                'posts_per_page' => absint( $post_category_post_num ),
                'category_name' => esc_html( $posts_category ),
                'tax_query' => array(
                   array(
                     'taxonomy' => 'post_format',
                     'field' => 'slug',
                     'terms' => 'post-format-video'
                   )
                ),
            )
        );

        $video_posts_array = array();

        if( $video_posts_post_query->have_posts() ):
            while( $video_posts_post_query->have_posts() ){
                $video_posts_post_query->the_post();
                
                $video_posts_array[] = get_the_ID();

            }
        wp_reset_postdata();
        endif; ?>

        <div class="home-videos twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
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

                <?php
                if( $video_posts_array ){

                    $video_posts_array = array_chunk( $video_posts_array, 3 ); ?>
                    <div class="twp-row twp-row-sm">

                        <?php
                        foreach( $video_posts_array as $video_posts_id ){

                            $video_post_query = new WP_Query(
                                    array(
                                        'post_type' => 'post',
                                        'posts_per_page' => 3,
                                        'post__in' => $video_posts_id,
                                    )
                            ); ?>

                            <div class="column column-three-1 column-full-sm">
                                <div class="twp-row twp-row-sm">

                                    <?php
                                    $i = 1;
                                    while( $video_post_query->have_posts() ){
                                        $video_post_query->the_post(); 

                                        $video_url = 'javascript:void(0)';
                                        $content = apply_filters( 'the_content', get_the_content() );
                                        $video = false;

                                        // Only get video from the content if a playlist isn't present.
                                        if ( false === strpos( $content, 'wp-playlist-script' ) ) {
                                            $video = get_media_embedded_in_content( $content, array( 'video', 'object', 'embed', 'iframe' ) );
                                        }
                                        if ( ! empty( $video ) ) {

                                            $j = 1;
                                            foreach ( $video as $video_html ) {

                                                if( $j == 1 ){

                                                    $video_html =  esc_html( $video_html );

                                                    if( strpos( $video_html, 'youtube') != false ){

                                                        $video_html = explode( '/embed/',$video_html );
                                                        $video_html = explode( '?feature=oembed',$video_html[1] );
                                                        $video_url = esc_url('https://www.youtube.com/watch?v='.$video_html[0]);

                                                    }elseif( strpos( $video_html, 'vimeo') != false ){

                                                        $video_html = explode( '/video/',$video_html );
                                                        $video_html = explode( '?dnt=',$video_html[1] );
                                                        $video_url = esc_url('https://vimeo.com/'.$video_html[0]);

                                                    }
                                                }
                                                $j++;
                                            }

                                        }; 

                                        if( $i == 1 ){

                                            $featured_image_1 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

                                            <div class="column">
                                                <article class="story-leader story-leader-jumbotron story-leader-videos">
                                                    <div class="post-panel block-bg-alt">
                                                        
                                                        <div class="post-thumb">
                                                            <a href="<?php the_permalink(); ?>" class="data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_1[0] ); ?>">
                                                                <span class="data-bg-overlay"></span>
                                                            </a>

                                                            <a href="<?php if( $video_url ){ echo esc_attr( $video_url ); } ?>" class="popup-video" tabindex="0">
                                                                <span class="format-icon playback-animation">
                                                                    <i class="ion ion-ios-play"></i>
                                                                </span>
                                                            </a>

                                                        </div>

                                                        <div class="entry-content">
                                                            
                                                            <div class="entry-meta entry-meta-category">
                                                                <?php if( $ed_relevant_cat == 'yes' && $posts_category ){ ?>

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
                                            </div>
                                        <?php }else{
                                            
                                            $featured_image_2 = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                                            <div class="column column-five">
                                                <article class="story-list-videos">
                                                    <div class="post-panel block-bg">
                                                        
                                                        <div class="post-thumb">
                                                            <a href="<?php the_permalink(); ?>" class="data-bg data-bg-small" data-background="<?php echo esc_url( $featured_image_2[0] ); ?>">
                                                                <span class="data-bg-overlay"></span>
                                                            </a>

                                                            <?php if( $video_url != 'javascript:void(0)' ){ ?>
                                                                <a href="<?php if( $video_url ){ echo esc_attr( $video_url ); } ?>" class="popup-video" tabindex="0">
                                                                    <span class="format-icon playback-animation">
                                                                        <i class="ion ion-ios-play"></i>
                                                                    </span>
                                                                </a>
                                                            <?php } ?>

                                                        </div>

                                                        <div class="entry-content">
                                                            <h3 class="entry-title entry-title-small">
                                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                            </h3>
                                                        </div>

                                                    </div>
                                                </article>
                                            </div>

                                        <?php }
                                    $i++; }
                                    wp_reset_postdata(); ?>

                                </div>
                            </div>

                        <?php } ?>

                    </div>

                <?php } ?>

            </div>
        </div>
        <?php
    }
endif;