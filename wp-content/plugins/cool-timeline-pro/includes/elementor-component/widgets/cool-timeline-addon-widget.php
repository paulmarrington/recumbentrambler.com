<?php
namespace CoolTimelineAddonWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor CoolTimelineAddonWidget
 *
 * Elementor widget for CoolTimelineAddonWidget
 *
 * @since 1.0.0
 */
class CoolTimelineAddonWidget extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'ctl-addon';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Cool Stroy Timeline', 'cool-timeline' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-time-line';
	}


	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'cool-timeline' ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'ctla' ];
	}

	/**
	 * Check for empty values and return provided default value if required
	 */
	protected function set_default( $value, $default ){
		if( isset($value) && $value!="" ){
			return $value;
		}else{
			return $default;
		}
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _register_controls() {
		
		$terms = get_terms(array(
			'taxonomy' => 'ctl-stories',
			'hide_empty' => false,
		));
		$ctl_categories=array();
		$ctl_categories[''] = __('All Categories','cool-timeline');

		if (!empty($terms) || !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$ctl_categories[$term->slug] =$term->name ;
			}
		}


		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Cool Story Timeline Settings', 'cool-timeline' ),
			]
		);

		$this->add_control(
			'cool_timeline_notice',
			[
				'label' => __( '', 'cool-timeline' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<strong style="color:red">It is only a shortcode builder. Kindly update/publish the page and check the actually cool timeline on front-end</strong>',
				'content_classes' => 'cool_timeline_notice',
			]
		);

		$this->add_control(
			'timeline_layout',
			[
				'label' => __( 'Timeline Layout', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Vertical Layout', 'cool-timeline' ),
					'horizontal' => __( 'Horizontal Layout', 'cool-timeline' ),
					'one-side' => __( 'One Side Layout', 'cool-timeline' ),
					'compact' => __( 'Compact Layout', 'cool-timeline' ),
				]
				
			]
		);

		 $this->add_control(
			'timeline_design',
			[
				'label' => __( 'Timeline Design', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( "Default",'cool-timeline' ) ,
					'design-2' => __( "Flat Design",'cool-timeline') ,
					'design-3' => __( "Classic Design",'cool-timeline') ,
					'design-4' => __( "Elegant Design",'cool-timeline') ,
					'design-5' => __( "Clean Design",'cool-timeline') ,
					'design-6' => __( "Modern Design",'cool-timeline'), 
					'design-7' => __( "Minimal Design", 'cool-timeline')	
				]
				
			]
		);

		$this->add_control(
			'timeline_categories',
			[
				'label' => __( 'Timeline Categories', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'timeline-stories',
				'options' => $ctl_categories
				
			]
		);

		$this->add_control(
			'skin',
			[
				'label' => __( 'Timeline Skin', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'cool-timeline' ),
					'dark' => __( 'Dark', 'cool-timeline' ),
					'light' => __( 'Light', 'cool-timeline' ),
				],
				
			]
		);
		
		$this->add_control(
			'date_format',
			[
				'label' => __( 'Timeline Date Format', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'F j',
				'options' => [
					'F j' => date_i18n( 'F j' ) . '( F j )',
					'F j Y' => date_i18n( 'F j Y' ).'( F j Y )',
					'Y-m-d' => date_i18n( 'Y-m-d' ).'( Y-m-d )',
					'm/d/Y' => date_i18n( 'm/d/Y' ).'( m/d/Y )',
					'd/m/Y' => date_i18n( 'd/m/Y' ).'( d/m/Y )',
					'F j Y g:i A' => date_i18n( 'F j Y g:i A' ).'( F j Y g:i A )',
					'Y' => date_i18n( 'Y' ).'( Y )',
					'custom' => __( 'Custom', 'cool-timeline' ),
				],
			]
		);

		$this->add_control(
			'timeline_pagination',
			[
				'label' => __( 'Pagination ?', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( "Default",'cool-timeline' ) ,
					'ajax_load_more' => __( "Ajax Load More",'cool-timeline')
				],
				'condition'   => [
					'timeline_layout!'   => 'horizontal',
				]
				
			]
		);
		
		$this->add_control(
			'animations',
			[
				'label' => __( 'Animation Effect', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bounceInUp',
				'options' => [
				 			 "none" =>"none",
                            "fade" =>"fade",
                            "zoom-in" =>"zoom-in",
                            "flip-right" =>"flip-right",
                            "zoom-out" =>"zoom-out",
                            "fade-up" =>"fade-up",
                            "fade-down" =>"fade-down",
                            "fade-left" =>"fade-left",
                            "fade-right" =>"fade-right",
                            "fade-up-right" =>"fade-up-right",
                            "fade-up-left" =>"fade-up-left",
                            "fade-down-right" =>"fade-down-right",
                            "fade-down-left" =>"fade-down-left",
                            "flip-up" =>"flip-up",
                            "flip-down" =>"flip-down",
                            "flip-left" =>"flip-left",
                            "slide-up" =>"slide-up",
                            "slide-left" =>"slide-left",
                            "slide-right" =>"slide-right",
                            "zoom-in-up" =>"zoom-in-up",
                            "zoom-in-down" =>"zoom-in-down",
                            "slide-down" =>"slide-down",
                            "zoom-in-left" =>"zoom-in-left",
                            "zoom-in-right" =>"zoom-in-right",
                            "zoom-out-up" =>"zoom-out-up",
                            "zoom-out-down" =>"zoom-out-down",
                            "zoom-out-left" =>"zoom-out-left",
                            "zoom-out-right" =>"zoom-out-right"
				],
				'condition'   => [
					'timeline_layout!'   =>	'horizontal',
				]
				
			]
		);

		$this->add_control(
			'story_order',
			[
				'label' => __( 'Stories Order', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'DESC' => __( 'DESC', 'cool-timeline' ),
					'ASC' => __( 'ASC', 'cool-timeline' ),
				]
				
			]
		);	

		$this->add_control(
			'timeline_based',
			[
				'label' => __( 'Timeline based on', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( "Default (Date Based)",'cool-timeline' ) ,
					'custom' => __( "Custom Order Number",'cool-timeline') ,
				],
			]
		);

		
		$this->add_control(
			'story_start_on',
			[
				'label' => __( 'Starts From', 'cool-timeline' ),
				'description' => __('Timeline Starting from Story. ex: 2','cool-timeline'),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'condition'   => [
					'timeline_layout'   => 'horizontal',
				]
			]
		);

		$this->add_control(
			'timeline_items',
			[
				'label' => __( 'Display Stories', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 2,
				'description'=> __( "Select number of items",'cool-timeline' )  ,
				'options' => [
						1 => __(1,'cool-timeline') ,
						2 => __(2,'cool-timeline') ,
						3 => __(3,'cool-timeline') ,
						4 => __(4,'cool-timeline') 
				],
				'condition'   => [
					'timeline_layout'   =>	'horizontal',
				]
				
			]
		);

		$this->add_control(
			'timeline_autoplay',
			[
				'label' => __( 'Autoplay', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'false',
				'options' => [
					'false' => __( 'False', 'cool-timeline' ),
					'true' => __( 'True', 'cool-timeline' ),
				],
				'condition'   => [
                    'timeline_layout'   => 'horizontal',
                ],
			]
		);

		$this->add_control(
			'timeline_compact_el_pos',
			[
				'label' => __( 'Date & Title positon', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'main-date',
				'options' => [
					'main-date' => __( "On top date/label below title",'cool-timeline' ) ,
                    'main-title' => __( "On top title below date/label",'cool-timeline') 
				],
				'condition'   => [
                    'timeline_layout'   => 'compact',
                ],
			]
		);

		$this->add_control(
			'story_content',
			[
				'label' => __( 'Stories Description?', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'short',
				'options' => [
					'short' => __( "Summary",'cool-timeline' ) ,
					'full'=>__('Full Text','cool-timeline' ),
				]
			]
		);

		$this->add_control(
			'ctl_icons',
			[
				'label' => __( 'Story Icon', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'NO',
				'options' => [
					'NO' => __( 'No', 'cool-timeline' ),
					'YES' => __( 'Yes', 'cool-timeline' ),
				],
				'condition'   => [
					'timeline_layout!'   => 'horizontal',
				]
				
			]
		);

		$this->add_control(
			'number_of_posts',
			[
				
					'label' => __( 'Show number of Stories', 'cool-timeline' ),
					'type' => Controls_Manager::TEXT,
					'default' => '10'
				,
				'condition'   => [
					'timeline_layout'   => 'one-side',
				]
			]
		);

		$this->add_control(
			'number_of_slides',
			[
				
					'label' => __( 'Slides to show', 'cool-timeline' ),
					'type' => Controls_Manager::TEXT,
					'default' => '10'
				,
				'condition'   => [
					'timeline_layout'   => 'horizontal',
				]
			]
		);

		$this->add_control(
			'number_of_posts_per_page',
			[
				
					'label' => __( 'Show stories per page', 'cool-timeline' ),
					'type' => Controls_Manager::TEXT,
					'default' => '10'
				,
				'condition'   => [
					'timeline_layout'   => ['default','compact']
					]
			]
		);

		$this->add_control(
			'timeline_filters',
			[
				'label' => __( 'Category Filters ?', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'description' => 'Enable category filters ?(Add value in Taxonomy field before using it)',
				'options' => [
					'no' => __( "No",'cool-timeline' ) ,
					'yes' => __( "Yes",'cool-timeline')
				],
				'condition'   => [
					'timeline_layout!'   => 'horizontal',
				]
				
			]
		);

		$this->add_control(
			'filters_categories',
			[
				'label' => __( 'Add categories slug', 'cool-timeline' ),
				'description'=> __('Add categories slug for filters eg(stories,our-history)','cool-timeline'),
				'type' => Controls_Manager::TEXT		
			]
		);

		$this->end_controls_section();

		
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings();
		//echo"<pre>";
	
		$layout=isset($settings['timeline_layout'])?$settings['timeline_layout']:"default";
		$design = $settings['timeline_design'];
		$autoplay = $this->set_default( $settings['timeline_autoplay'],'false');
		$date_format=isset($settings['date_format'])?$settings['date_format']:"F j";
		$cat = $settings['timeline_categories'];
		$order = $this->set_default($settings['story_order'],'DESC');
		$skin=isset($settings['skin'])?$settings['skin']:"default";
		$ctl_icons=isset($settings['ctl_icons'])?$settings['ctl_icons']:"NO";
		
		if( $layout == "one-side" ){
			$number_of_posts=isset($settings['number_of_posts'])?$settings['number_of_posts']:"10";
		}else if( $layout == "horizontal" ){
			$number_of_posts=isset($settings['number_of_slides'])?$settings['number_of_slides']:"10";
		}else{
				$number_of_posts=isset($settings['number_of_posts_per_page'])?$settings['number_of_posts_per_page']:"10";
		}
		$animations=isset($settings['animations'])?$settings['animations']:"none";
		$content = $this->set_default($settings['story_content'],'short');
		$based = $this->set_default( $settings['timeline_based'],'default' );
		$start = $this->set_default($settings['story_start_on'], 0 );
		$items = $this->set_default( $settings['timeline_items'],'2' );
		$pagination = $this->set_default( $settings['timeline_pagination'], 'default'  );
		$filter = $this->set_default( $settings['timeline_filters'], 'no'  );
		$comp_el_pos = $this->set_default( $settings['timeline_compact_el_pos'], 'main-date'  );
		$filters_categories = $settings['filters_categories'];


		$vertical	=	'[cool-timeline layout="'.$layout.'" designs="'.$design.'" skin="'.$skin.'" category="'.$cat.'" show-posts="'.$number_of_posts.'" order="'.$order.'" icons="'.$ctl_icons.'" animations="'.$animations.'" date-format="'.$date_format.'" story-content="'.$content.'" based="'.$based.'" compact-ele-pos="'.$comp_el_pos.'" pagination="'.$pagination.'" filters="'.$filter.'" filter-categories="'.$filters_categories.'"]';

		$horizontal	=	'[cool-timeline layout="'.$layout.'" category="'.$cat.'" skin="'.$skin.'" designs="'.$design.'" show-posts="'.$number_of_posts.'" order="'.$order.'" items="'.$items.'" icons="'.$ctl_icons.'" story-content="'.$content.'" date-format="'.$date_format.'" based="'.$based.'" autoplay="'.$autoplay.'" start-on="'.$start.'"]';

		$timeline = $layout!='horizontal'?$vertical:$horizontal;

		 echo'<div class="elementor-shortcode cool-timeline-free-addon">';
		/*  if( is_admin() ){
			 echo "<strong>It is only a shortcode builder. Kindly update/publish the page and check the actually cool timeline on front-end</strong><br/>";
		 } */
		 echo do_shortcode( $timeline );
 		 echo'</div>';
	}

	
	
}
