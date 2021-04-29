<?php
// default variables for later use
$ctl_options_arr = get_option('cool_timeline_options');

$ctl_html='';
$ctl_format_html='';
$display_s_date='';
$same_day_post='';
$dates_li='';
$s_styles='';
$ctl_slideshow ='';

$ctl_content_length ? $ctl_content_length : 100;
$itcls='';
$i=0;

if($attribute['designs'])
{
    $design_cls='ht-'.$attribute['designs'];
    $design=$attribute['designs'];
    }else{
   $design_cls='ht-default';
    $design='default';
}
 $r_more=isset($ctl_options_arr['display_readmore'])?$ctl_options_arr['display_readmore']:"yes";
 
 
// dynamic class based upon design
if(in_array($active_design,array("design-2","design-3","design-4","design-5","design-6"))){
    $items = $attribute['items'] ? $attribute['items'] : "3";
	$itcls='hori-items-'.$items;
	
}else if($active_design=="design-7"){
    $items = $attribute['items'] ? $attribute['items'] : "6";
}else{
    $items ='0';
	$itcls='hori-items-1';
}

// main query
$ctl_loop = new WP_Query(apply_filters( 'ctl_stories_query',$args));

if ($ctl_loop->have_posts()) {

    while ($ctl_loop->have_posts()) : $ctl_loop->the_post();
         global $post;
        $post_id=get_the_ID();

        $posted_date='';
        $ctl_format_html='';
        $slink_s='';
        $slink_e='';

        // grabing values 
        $story_format = get_post_meta( $post_id, 'story_format', true);
        $img_cont_size = get_post_meta( $post_id, 'img_cont_size', true);
        $ctl_story_date = get_post_meta( $post_id, 'ctl_story_date', true);
        $ctl_story_color = get_post_meta( $post_id, 'ctl_story_color', true);
        $ctl_story_date_lbl = get_post_meta($post_id, 'ctl_story_date_lbl', true);
        $posted_date=ctl_get_story_date($post_id,$date_formats);
        $custom_link = get_post_meta($post_id, 'story_custom_link', true);
    
        $container_cls=isset($img_cont_size)?$img_cont_size:"full";
        $story_id="story-id-".$post_id;
        $i++;

        // genrating dynamic styles
        $s_styles.=CooltimelineStyles::ctl_h_story_styles($post_id ,$layout,$design,$timeline_skin);
       
        //read more link html
          if($r_more=="yes"){
            if(isset($custom_link)&& !empty($custom_link)){
            $slink_s='<a target="_blank" title="'.esc_attr(get_the_title()).'" href="'.esc_url($custom_link).'">';
             $slink_e='</a>';
            }else{
            $slink_s='<a title="'.esc_attr(get_the_title()).'" href="'.esc_url(get_the_permalink()).'">';
             $slink_e='</a>';
                }
          } 
       
          // on load first active story
        $selected='';
        if($i==1){
            $selected='selected';
        }

        // grabing html and generating html for later use
        $clt_icon=''; 
        $icon='';
        if ($icons == "YES") {
            $icon=ctl_post_icon($post_id,$default_icon);
            $clt_icon .='<span class="icon-placeholder">'.$icon.'</span> ';
         }
       
   // if stories based upon custom order      
 if($based=="custom"){
        $ctl_story_lbl = get_post_meta( $post_id, 'ctl_story_lbl',true);
        $ctl_story_lbl2 = get_post_meta( $post_id, 'ctl_story_lbl_2',true);
        $lb1= '<span class="custom_story_lbl">'.__($ctl_story_lbl,'cool-timeline').'</span>';
        $lb2= '<span class="custom_story_lbl_2">'.__($ctl_story_lbl2,'cool-timeline'). '</span>';
       
        if($active_design=='design-7'){
             $dates_li .='<li id="' . esc_attr($story_id ). '" class="ht-dates-'.esc_attr($design).'">'.$clt_icon;
              if(  $r_more=="yes"){
                $dates_li.='<a ref="prettyPhoto" href="#ctl-'.esc_attr($story_id).'">';
                 }
            $dates_li.='<span class="ctl-main-story-date ' . esc_attr($selected ). '">'. $lb1.$lb2.'</span>
            <div class="ctl-main-story-title">'.get_the_title().'</div>';
             if(  $r_more=="yes"){
                $dates_li.='</a>';
                }
            $dates_li.='</li>';

        }else{
        $dates_li .='<li id="' . esc_attr($story_id ). '" class="ht-dates-'.esc_attr($design).'" data-date="' . esc_attr($story_id ). '">'.$clt_icon.'<span class="ctl-story-time ' . esc_attr($selected ). '"  data-date="' .esc_attr($story_id). '" >'. $lb1.$lb2.'</span></li>';
        }
}else{
// if date based stories
    // horizontal tm dates navigation html
     if($active_design=='design-3'||$active_design=='design-4'||$active_design=='design-6') {
            $dates_li .= ' <li class="ht-dates-'.esc_attr($design).'" id="' . esc_attr($story_id ). '" data-date="' . esc_attr($story_id ). '">'.$clt_icon.'<span class="ctl-story-time ' . esc_attr($selected ). '"  data-date="' .esc_attr($story_id). '" ><div class="ctl-tooltips"><span>'. apply_filters('ctl_story_dates',$posted_date).'</span></div></span></li>';
       }else if($active_design=='design-7'){
                $dates_li .= ' <li  id="' . esc_attr($story_id ). '" class="ht-dates-'.esc_attr($design).'" >'.$clt_icon;
             if(  $r_more=="yes"){
                    $dates_li.='<a ref="prettyPhoto" href="#ctl-'.esc_attr($story_id).'">';
               }
                $dates_li.='<div class="ctl-main-story-date"><span class="minimal-date">'.$posted_date.'</span></div>';
                $dates_li.='<div class="ctl-main-story-title">';
                $dates_li.=esc_html(get_the_title());
                $dates_li.='</div>';
                if(  $r_more=="yes"){
                    $dates_li.='</a>';
                }
                $dates_li .= '</li>';
        } else{
             $dates_li .= ' <li  id="' . esc_attr($story_id ). '" class="ht-dates-'.esc_attr($design).'" data-date="' . esc_attr($story_id ). '">'.$clt_icon.'<span class="ctl-story-time ' . esc_attr($selected ). '"  data-date="' .esc_attr($story_id). '" >'. apply_filters('ctl_story_dates',$posted_date).'</span></li>';
            }
         }

    // horizontal timeline story content HTML
    if($active_design=='design-7'){
    $ctl_html.='<div id="ctl-'.esc_attr($story_id).'" class="ctl_hide"><div class="ctl-popup-content">';
    }else{
     $ctl_html .= '<li id="' . esc_attr($story_id ). '-content"  class="ht-'.esc_attr($design).'">';
    }

     $ctl_html .= '<div class="timeline-post '.esc_attr($post_skin_cls).' ht-content-'.esc_attr($design).'">';
       
     // above title for default and design 2
     if($active_design=="default" || $active_design=="design-2") { 
            $ctl_html .= '<h2 class="content-title">'.$slink_s . get_the_title() .$slink_e.'</h2>';
      }else if($active_design=="design-7"){

          
         if($based=="custom"){
            $ctl_html .='<div class="popup-sublabels">'. $lb1 .' - '.$lb2.'</div>';
         }else{
            $ctl_html .='<div class="popup-posted-date">'. apply_filters('ctl_story_dates',$posted_date).'</div>';
            }
            $ctl_html .= '<h2 class="popup-content-title">' . get_the_title() .'</h2>';
        }
     $ctl_html .= '<div class="ctl_info event-description '.esc_attr($container_cls) .'">';

     // dynamic content based upon story type
        if ($story_format == "video") {
             $ctl_html .=clt_story_video($post_id);
         } elseif ($story_format == "slideshow") {  
           
              $ctl_html .=clt_story_slideshow($post_id,$layout,$ctl_options_arr,$active_design);

         }else{
                if($active_design=="design-7"){
                 $ctl_html .=ctl_minimal_featured_img($post_id,$img_cont_size);
                }else{
                $ctl_html .=clt_story_featured_img($post_id,$ctl_options_arr);
                }
             }
             // below title for design 3 and 4
         if($active_design=='design-3'|| $active_design=='design-4') {
            $ctl_html .= '<h2 class="content-title-simple">'.$slink_s. get_the_title() .$slink_e.'</h2>';
        }

        if($active_design!='design-4') {
            $ctl_html .= '<div class="content-details">';
           
             if($active_design=='design-5'|| $active_design=='design-6') {
            $ctl_html .= '<h2 class="content-title-simple">'.$slink_s. get_the_title() .$slink_e.'</h2>';
                 }

        // story content for all desgins
              if ($story_content=="full") {
             $ctl_html .= apply_filters('the_content', $post->post_content);
            } else {
            $ctl_html .= "<p>" .apply_filters('ctl_story_excerpt',get_the_excerpt()) . "</p>";
             }
           $ctl_html.='</div>';
        }
      
          $ctl_html .= '</div></div>';

         if($active_design=='design-7'){
            $ctl_html .= '</div></div>';
         }else{
        $ctl_html .='</li>';
         }
        $post_content = '';
        // dynamic content end
    endwhile;
    wp_reset_postdata();
    // lopp end

// main wrapper classes
$timeline_id=uniqid();
// $category= $attribute['category'] ?$attribute['category']:'all-cats';
$ctl_category = $attribute['category'];
$timeline_wrp_id="ctl-horizontal-slider-".$timeline_id;
$sl_dir=is_rtl() ? "rtl":"";
$rtl=is_rtl()?"true":"false";

     $main_wrp_id='tm-'.$attribute['layout'].'-'.$attribute['designs'].'-'.rand(1,20);
     $main_wrp_cls=array();
    $main_wrp_cls[]="cool-timeline-horizontal";
    $main_wrp_cls[]=esc_attr($wrp_cls);
    if(isset($ctl_category)){
    $main_wrp_cls[]=esc_attr($ctl_category);
    }
    $main_wrp_cls[]=esc_attr($design_cls);
    $main_wrp_cls=apply_filters('ctl_wrapper_clasess',$main_wrp_cls);  

$clt_hori_view ='<!-- ========= Cool Timeline PRO '.CTLPV.' ========= -->';
// HT Settings

$clt_hori_view .='<div class="clt_preloader"><img alt="Preloader" src="'.CTP_PLUGIN_URL.'assets/images/preloader.gif"></div>';

$clt_hori_view .= '<div  style="opacity:0"
class="'.implode(" ",$main_wrp_cls).'"  
id="'.esc_attr($timeline_wrp_id).'" 
data-rtl="'.$rtl.'"
date-slider="ctl-h-slider-'.esc_attr($timeline_id).'" 
data-nav="nav-slider-'.esc_attr($timeline_id).'" 
data-items="'.esc_attr($items).'" 
data-start-on="'.esc_attr($attribute['start-on']).'" 
data-autoplay="'.esc_attr($attribute['autoplay']).'"
data-autoplay-speed="'.esc_attr($attribute['autoplay-speed']).'">

<div   class="timeline-wrapper '.esc_attr($wrapper_cls).' '.esc_attr($itcls).'" >';

// wrapper for  design 4
if($active_design=="design-4") {
    $clt_hori_view .= '<div  class="wrp-desgin-4" dir="'.esc_attr($sl_dir).'">';
}else{
    $clt_hori_view .= '<div class="clt_carousel_slider"  dir="'.esc_attr($sl_dir).'">';
}
     
// dates navigation for all designs
if($active_design=='design-7'){
    $clt_hori_view .= '<ul class="ctl_minimal_cont" id="nav-slider-'.esc_attr($timeline_id). '" >';
    $clt_hori_view .= $dates_li;
    $clt_hori_view .= '</ul>';
}else if($active_design!='design-4') {
    $clt_hori_view .= '<ul class="ctl_h_nav" id="nav-slider-'.esc_attr($timeline_id). '">';
    $clt_hori_view .= $dates_li;
    $clt_hori_view .= '</ul></div>';
}  


// stories content for all designs
if($active_design=='design-7') {
        $clt_hori_view .=$ctl_html;
         $clt_hori_view .='</div>';
}else{
$clt_hori_view .= '<div  class="clt_caru_slider"  dir="'.esc_attr($sl_dir).'">';
$clt_hori_view .= '<ul class="ctl_h_slides"  id="ctl-h-slider-'.esc_attr($timeline_id).'">';
$clt_hori_view .=$ctl_html;
$clt_hori_view .= '</ul></div>';
}
// Dates navigation for desgin 4
if($active_design=='design-4') {
    $clt_hori_view .= '<ul class="ctl_h_nav" id="nav-slider-' .esc_attr($timeline_id). '">';
    $clt_hori_view .= $dates_li;
    $clt_hori_view .= '</ul></div>';
}
// dynamic styles
$stories_styles='<style type="text/css">'.$s_styles.'</style>';
$clt_hori_view .='</div></div>'.$stories_styles;
$clt_hori_view .='<!-- end  ================================================== -->';

} else {
    $clt_hori_view .= '<div class="no-content"><h4>';
    //$ctl_html_no_cont.=$ctl_no_posts;
    $clt_hori_view .= __('Sorry,You have not added any story yet', 'cool-timeline');
    $clt_hori_view .= '</h4></div>';
}
