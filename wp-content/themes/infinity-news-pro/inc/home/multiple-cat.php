<?php
/**
* Multiple Category Posts Function.
*
* @package Infinity News
*/

if( !function_exists('infinity_news_multipal_cat_posts') ):

  // Multiple Category Posts Functions.
  function infinity_news_multipal_cat_posts( $infinity_news_home_section ){ 

    $multipal_category_lists = array();
    $multipal_category_lists[]  = isset( $infinity_news_home_section->post_category_1 ) ? $infinity_news_home_section->post_category_1 : '' ;
    $multipal_category_lists[]  = isset( $infinity_news_home_section->post_category_2 ) ? $infinity_news_home_section->post_category_2 : '' ;
    $multipal_category_lists[]  = isset( $infinity_news_home_section->post_category_3 ) ? $infinity_news_home_section->post_category_3 : '' ;
    $ed_relevant_cat_multiple = isset( $infinity_news_home_section->ed_relevant_cat ) ? $infinity_news_home_section->ed_relevant_cat : '' ;
    $ed_excerpt_content = isset( $infinity_news_home_section->ed_excerpt_content ) ? $infinity_news_home_section->ed_excerpt_content : '' ;
    $block_1_title = isset( $infinity_news_home_section->block_1_title ) ? $infinity_news_home_section->block_1_title : '' ;
    $block_2_title = isset( $infinity_news_home_section->block_2_title ) ? $infinity_news_home_section->block_2_title : '' ;
    $block_3_title = isset( $infinity_news_home_section->block_3_title ) ? $infinity_news_home_section->block_3_title : '' ;
    $section_bg_image = isset( $infinity_news_home_section->section_bg_image ) ? $infinity_news_home_section->section_bg_image : '' ;
    $section_bg_color = isset( $infinity_news_home_section->section_bg_color ) ? $infinity_news_home_section->section_bg_color : '' ;

    $post_category_post_num_3 = isset( $infinity_news_home_section->post_category_post_num_3 ) ? $infinity_news_home_section->post_category_post_num_3 : '' ;
    if( empty( $post_category_post_num_3 ) ){ $post_category_post_num_3 = 5; }
    ?>

    <div class="home-list-panel twp-blocks <?php if( $section_bg_image ){ echo 'data-bg'; } ?>" <?php if( $section_bg_image ){ echo 'data-background="'.esc_url( $section_bg_image ).'"'; }else{ if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; } } ?>>
        <div class="wrapper">
            <div class="twp-row">

              <?php
              $default = infinity_news_get_default_theme_options();
              $ed_like_dislike = absint( get_theme_mod( 'ed_like_dislike',$default['ed_like_dislike'] ) );

              $ed_social_icon = absint( get_theme_mod( 'ed_social_icon',$default['ed_social_icon'] ) );
              if( $multipal_category_lists ){
                $j = 1;
                foreach( $multipal_category_lists as $multipal_category ){
                    $multipal_category_query_1 = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 1, 'category_name' => esc_html( $multipal_category ) ) );
                    $multipal_category_query_2 = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => absint( $post_category_post_num_3 ), 'category_name' => esc_html( $multipal_category ) ) );

                    if( $multipal_category ){
                        $idObj = get_category_by_slug( $multipal_category ); 
                        $id = $idObj->term_id;
                        $cat_name = $idObj->name;
                        $cat_link = get_category_link( $id );
                    }
                    ?>

                      <div class="column column-three-1 column-full-sm">

                          <?php
                          $block_title = '';
                          if( $multipal_category ){

                              $term = get_term_by('slug', $multipal_category, 'category');
                              $block_title = $term->name;
                              $cat_link = get_category_link( $term->term_id ); ?>

                          <?php }

                          if( $j == 1 ){
                            if( $block_1_title ){
                              $block_title = $block_1_title;
                            }
                          }elseif( $j == 2 ){
                            if( $block_2_title ){
                              $block_title = $block_2_title;
                            }
                          }elseif( $j == 3 ){
                            if( $block_3_title ){
                             $block_title = $block_3_title;
                            }
                          } ?>
                            <header class="block-title-wrapper">

                              <?php if( $block_title || $multipal_category ){ ?>
                                <div class="hr-line"></div>
                              <?php } ?>
                               

                                <h2 <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="block-title block-title-bg">
                                    <?php echo esc_html( $block_title ); ?>
                                </h2>

                                <?php if( $multipal_category ){ ?>
                                  <div <?php if( $section_bg_color){ echo 'style="background-color:'.esc_attr( $section_bg_color ).'"'; }  ?> class="title-controls title-controls-bg">
                                      <a href="<?php echo esc_url( $cat_link ); ?>">
                                          <?php echo esc_html__('View More','infinity-news'); ?>
                                      </a>
                                  </div>
                                <?php } ?>

                            </header>

                          <div class="story-panel-wrapper block-bg">

                              <?php if( $multipal_category_query_1->have_posts() ){ ?>
                                <div class="story-list-leader">

                                    <?php while( $multipal_category_query_1->have_posts() ){
                                      $multipal_category_query_1->the_post();
                                      $featured_image_big = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium_large' ); ?>

                                      <article class="story-panel">
                                          <div class="post-panel">
                                              
                                              <div class="post-thumb">
                                                  <a href="<?php the_permalink(); ?>" class="data-bg data-bg-big" data-background="<?php echo esc_url( $featured_image_big[0] ); ?>"></a>

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

                                                      <?php if( $ed_relevant_cat_multiple == 'yes' && $multipal_category ){ ?>

                                                          <span class="cat-links">
                                                              <a class="twp_cat_<?php echo esc_attr( $multipal_category ); ?>" href="<?php echo esc_url( $cat_link ); ?>" rel="category tag"><?php echo esc_html( $cat_name ); ?></a>
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

                                                <?php if( $ed_excerpt_content == 'yes' ){ ?>
                                                  <div class="entry-content">
                                                    <?php 
                                                    if( has_excerpt() ){
                                                      the_excerpt();
                                                    }else{
                                                      echo wp_kses_post( wp_trim_words( get_the_content(),30,'...') );
                                                    } ?>
                                                  </div>
                                                <?php } ?>

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

                                    <?php } ?>

                                </div>
                              <?php wp_reset_postdata(); } ?>

                              <?php if( $multipal_category_query_2->have_posts() ){ ?>
                                <div class="story-list-group">

                                  <?php
                                  $i = 1;
                                  while( $multipal_category_query_2->have_posts() ){
                                      $multipal_category_query_2->the_post();
                                      if( $i != 1 ){ 

                                          $featured_image_big = wp_get_attachment_image_src( get_post_thumbnail_id(),'medium' ); ?>

                                          <article class="story-list">
                                             <div class="post-panel">
                                                 
                                                 <div class="post-thumb">
                                                     <a href="" class="data-bg data-bg-xs" data-background="<?php echo esc_url( $featured_image_big[0] ); ?>"></a>
                                                 </div>

                                                 <div class="entry-content">
                                                     <h3 class="entry-title entry-title-medium">
                                                         <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                                     </h3>
                                                 </div>

                                             </div>
                                          </article>

                                   <?php }
                                   $i++;
                                  } ?>

                                </div>
                              <?php wp_reset_postdata(); } ?>

                          </div>
                      </div>

                  <?php
                  $j++;
                  }
                
              } ?>

            </div>
        </div>
    </div>

  <?php }

endif;