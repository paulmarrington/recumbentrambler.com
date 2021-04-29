<?php

$clt_vars=array();
$ctl_options_arr= get_option('cool_timeline_options');

$disable_months= isset($ctl_options_arr['disable_months']) ? $ctl_options_arr['disable_months'] : "no";
$title_alignment=  isset($ctl_options_arr['title_alignment']) ? $ctl_options_arr['title_alignment'] : "center";

/*
 * Style options
 */

$background_type= isset($ctl_options_arr['background']['enabled']) ? $ctl_options_arr['background']['enabled'] : '';
   $bg_color='';
if ($background_type == 'on') {
   $bg_color=isset($ctl_options_arr['background']['bg_color']) ? $ctl_options_arr['background']['bg_color'] : 'none';
}

$first_post_color= isset($ctl_options_arr['first_post'])?$ctl_options_arr['first_post'] : "#02c5be";

$second_post_color= isset($ctl_options_arr['second_post'])?$ctl_options_arr['second_post'] : "#f12945";


$content_bg_color= isset($ctl_options_arr['content_bg_color'])?$ctl_options_arr['content_bg_color'] : '#f9f9f9';

$content_color= isset($ctl_options_arr['content_color'])?$ctl_options_arr['content_color'] : '#666666';

$title_color= isset($ctl_options_arr['title_color'])?$ctl_options_arr['title_color'] : '#fff';

$circle_border_color= isset($ctl_options_arr['circle_border_color'])?$ctl_options_arr['circle_border_color'] : '#333333';

$main_title_color= isset($ctl_options_arr['main_title_color'])?$ctl_options_arr['main_title_color'] : '#000';


/*
 * Typography options
 */

$ctl_main_title_typo =isset($ctl_options_arr['main_title_typo'])?$ctl_options_arr['main_title_typo']:"";
$ctl_post_title_typo =isset($ctl_options_arr['post_title_typo'])?$ctl_options_arr['post_title_typo']:"";
$ctl_post_content_typo =isset($ctl_options_arr['post_content_typo'])?$ctl_options_arr['post_content_typo']:"";

$ctl_date_typo = isset($ctl_options_arr['ctl_date_typo'])?$ctl_options_arr['ctl_date_typo']:"";
$custom_date_style = isset($ctl_options_arr['custom_date_style'])?$ctl_options_arr['custom_date_style']:'';
$custom_date_color= isset($ctl_options_arr['custom_date_color'])?$ctl_options_arr['custom_date_color']:'';

$post_title_text_style= isset($ctl_options_arr['post_title_text_style'] )? $ctl_options_arr['post_title_text_style'] : 'capitalize';

$main_title_f= isset($ctl_main_title_typo['face']) ? $ctl_main_title_typo['face'] : 'inherit';
$main_title_w= isset($ctl_main_title_typo['weight']) ? $ctl_main_title_typo['weight'] : 'inherit';
$main_title_s= isset($ctl_main_title_typo['size']) ? $ctl_main_title_typo['size'] : '22px';


$events_body_f= isset($ctl_post_content_typo['face']) ? $ctl_post_content_typo['face'] : 'inherit';
$events_body_w= isset($ctl_post_content_typo['weight']) ? $ctl_post_content_typo['weight'] : 'inherit';
$events_body_s= isset($ctl_post_content_typo['size']) ? $ctl_post_content_typo['size'] : 'inherit';

$post_title_f= isset($ctl_post_title_typo['face']) ? $ctl_post_title_typo['face'] : 'inherit';
$post_title_w= isset($ctl_post_title_typo['weight']) ? $ctl_post_title_typo['weight'] : 'inherit';
$post_title_s= isset($ctl_post_title_typo['size']) ? $ctl_post_title_typo['size'] : '20px';

$post_content_f= isset($ctl_post_content_typo['face']) ? $ctl_post_content_typo['face'] : 'inherit';
$post_content_w= isset($ctl_post_content_typo['weight']) ? $ctl_post_content_typo['weight'] : 'inherit';
$post_content_s= isset($ctl_post_content_typo['size']) ? $ctl_post_content_typo['size'] : 'inherit';

$ctl_date_f='';$ctl_date_w=''; $ctl_date_s='';
if ($custom_date_style == "yes") {
   $ctl_date_f= isset($ctl_date_typo['face']) ? $ctl_date_typo['face'] : 'inherit';
   $ctl_date_w= isset($ctl_date_typo['weight']) ? $ctl_date_typo['weight'] : 'inherit';
   $ctl_date_s= isset($ctl_date_typo['size']) ? $ctl_date_typo['size'] : 'inherit';

}
 $ctl_date_color='';
if ($custom_date_color == "yes") {
   $ctl_date_color= isset($ctl_options_arr['ctl_date_color'])?$ctl_options_arr['ctl_date_color'] : '#fff';
}

$disable_r_stories = isset($ctl_options_arr['disable_r_stories']) ? $ctl_options_arr['disable_r_stories'] : 'no';

$line_color= isset($ctl_options_arr['line_color'])? $ctl_options_arr['line_color'] : '#000';

$custom_styles=isset($ctl_options_arr['custom_styles']) ? $ctl_options_arr['custom_styles'] : '';