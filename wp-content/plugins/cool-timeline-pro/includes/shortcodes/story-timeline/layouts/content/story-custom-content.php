<?php
/*
  If user has created custom order based layout
*/

// custom order based timeline layout

$ctl_story_lbl = get_post_meta($post_id, 'ctl_story_lbl',true);
$ctl_story_lbl2 = get_post_meta($post_id, 'ctl_story_lbl_2',true);


$ctl_html .= '<!-- .timeline-post-start-->';
  $ctl_html .= '<div data-alternate="'.esc_attr($i).'" id="story-'.esc_attr($post_id).'" class="'.implode(" ",$p_cls).'">';
   // show label if layout is not a compact
   if($layout!="compact"){
      $ctl_html .= '<div class="timeline-meta" data-aos="'.esc_attr($ctl_animation).'"><div class="meta-details">';
      $ctl_html .= '<span class="custom_story_lbl">'.__($ctl_story_lbl,'cool-timeline').'</span>';
      $ctl_html .= '<span class="custom_story_lbl_2">'.__($ctl_story_lbl2,'cool-timeline'). '</span></div></div>';
    }

   if($active_design=="design-6") {
     $ctl_html .= '<div data-aos="'.esc_attr($ctl_animation).'" class="timeline-icon icon-dot-full '.esc_attr($design).'-dot"><div class="timeline-bar"></div></div>';
    }else{
  if ( $icons == "YES") {
      $icon=ctl_post_icon($post_id,$default_icon);
     $ctl_html .='<div data-aos="'.esc_attr($ctl_animation).'" class="timeline-icon icon-larger iconbg-turqoise icon-color-white '.esc_attr($design).'-icon">
      <div class="icon-placeholder">'.$icon.'</div><div class="timeline-bar"></div></div>';

    }else {
      $ctl_html .= '<div data-aos="'.esc_attr($ctl_animation).'" class="timeline-icon icon-dot-full '.esc_attr($design).'-dot"><div class="timeline-bar"></div></div>';
     }
   }

     $ctl_html .= '<div  data-aos="'.esc_attr($ctl_animation).'"   class="timeline-content  clearfix ' .esc_attr($even_odd) . '  ' . esc_attr($container_cls) .' '.esc_attr($design).'-content '.$stop_ani.'">';

     if(in_array($active_design,array("design-2","default","design-4","design-5","design-6","design-7"))){ 
        if($layout=="compact" && $attribute['compact-ele-pos']=="main-date" ){
                if($active_design=="design-7"){
                  $popup_link_open='';
                  $popup_link_close='';
                  if($r_more=="yes"){
                    $popup_link_open='<a ref="prettyPhoto" href="#ctl-'.esc_attr($post_id).'">';
                    $popup_link_close='</a>';
                  }
                  $ctl_html .='<h2 class="content-title"><div class="clt-cstm-lbl-f">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></div><br/>'.$popup_link_open.get_the_title().$popup_link_close.'</h2>';
                }else{
                 $ctl_html .='<h2 class="content-title clt-cstm-lbl-f">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></h2>';
                }

              }else{
                if($active_design=="design-6"){
                    $ctl_html .='<h2 class="story-date clt-meta-date">'.apply_filters('ctl_story_dates',$posted_date).'</h2>';
                }else if($active_design=="design-7"){
                  $popup_link_open='';
                  $popup_link_close='';
                  if($r_more=="yes"){
                    $popup_link_open='<a ref="prettyPhoto" href="#ctl-'.esc_attr($post_id).'">';
                    $popup_link_close='</a>';
                  }
                  $ctl_html .='<h2 class="content-title">'.$popup_link_open. get_the_title() .$popup_link_close.'<br/><div class="clt-cstm-lbl-f">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></div></h2>';
                }else{
                    $ctl_html .='<h2 class="content-title">'.$slink_s. get_the_title() .$slink_e.'</h2>';
                }
          
            }
        }
if($active_design=="design-7"){
          $ctl_html.='<div id="ctl-'.esc_attr($post_id).'" class="ctl_hide"><div class="ctl-popup-content">';
          $ctl_html .='<div class="popup-posted-date"><div class="clt-cstm-lbl-f">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></div></div>';
          $ctl_html .= '<h2 class="popup-content-title">' . get_the_title() .'</h2>';
          $ctl_html .= '<div class="ctl_info event-description '.esc_attr($container_cls) .'">';

      // dynamic content based upon story type
      if ($story_format == "video") {
        $ctl_html .=clt_story_video($post_id);
      } elseif ($story_format == "slideshow") {  
        $ctl_html .=clt_story_slideshow($post_id,$layout,$ctl_options_arr,$active_design);
       }else{
            $ctl_html .=ctl_minimal_featured_img($post_id,$img_cont_size);
        }
         // story content for all desgins
         if ($story_content=="full") {
          $ctl_html .= apply_filters('the_content', $post->post_content);
         } else {
         $ctl_html .= "<p>" .apply_filters('ctl_story_excerpt',get_the_excerpt()) . "</p>";
          }

         $ctl_html .='</div></div></div>';
}else{
        // story dynamic content based on type
         $ctl_html .= '<div class="ctl_info event-description '.esc_attr($container_cls). '">';
           if ($story_format == "video") {
             $ctl_html .=clt_story_video($post_id);
         } elseif ($story_format == "slideshow") {  
              $ctl_html .=clt_story_slideshow($post_id,$attribute['type'],$ctl_options_arr,$active_design);
         }else{
             $ctl_html .=clt_story_featured_img($post_id,$ctl_options_arr);
          }

  $ctl_html .= '<div class="content-details">';
      // if compact layout and title on the top 
      if($layout=="compact"){
         if($attribute['compact-ele-pos']=="main-title" ){
          if($active_design=="design-3" || $active_design=="design-6") {
                $ctl_html .='<h2 class="compact-content-title">'.$slink_s. get_the_title() .$slink_e.'</h2>';
                  }
           
            $ctl_html .='<h2 class="clt-compact-date">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></h2>';
              
         }else if($attribute['compact-ele-pos']=="main-date" ){
              if($active_design=="design-3") {
              $ctl_html .='<h2 class="clt-compact-date">'.$ctl_story_lbl.' <small class="clt-cstm-lbl-s">'.$ctl_story_lbl2.'</small></h2>';
               }

              $ctl_html .='<h2 class="compact-content-title">'.$slink_s. get_the_title() .$slink_e.'</h2>';
           }
    }else{
            if($active_design=="design-3"||$active_design=="design-6") {
            $ctl_html .= '<h2 class="content-title-2">'.$slink_s. get_the_title() .$slink_e.'</h2>';
             }
           } 

             if ($story_content=="full") {
             $ctl_html .= apply_filters('the_content', $post->post_content);
            } else {
            $ctl_html .= "<p>" . apply_filters('ctl_story_excerpt',get_the_excerpt()). "</p>";
             }
         $ctl_html .='</div></div>';

      }
        $ctl_html .= '</div><!-- timeline content --></div>
        <!-- .timeline-post-end -->';
