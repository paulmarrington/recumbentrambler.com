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
class CoolContentTimelineAddonWidget extends Widget_Base {

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
		return 'cctl-addon';
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
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Cool Content Timeline', 'cool-timeline' );
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
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Cool Content Timeline Settings', 'cool-timeline' ),
			]
		);
		
		$this->add_control(
			'cool_timeline_content_notice',
			[
				'label' => __( '', 'cool-timeline' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => '<strong style="color:red">It is only a shortcode builder. Kindly update/publish the page and check the actually cool content timeline on front-end</strong>',
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
			'timeline_skin',
			[
				'label' => __( 'Timeline Skin', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'cool-timeline' ),
					'light' => __( 'Light', 'cool-timeline' ),
					'dark' => __( 'Dark', 'cool-timeline' ),
				],
				
			]
		);

		$this->add_control(
			'timeline_design',
			[
				'label' => __( 'Timeline Designs', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default', 'cool-timeline' ),
					'design-2' => __( 'Flat Design', 'cool-timeline' ),
					'design-3' => __( 'Classic Design', 'cool-timeline' ),
					'design-4' => __( 'Elegant Design', 'cool-timeline' ),
					'design-5' => __( 'Clean Design', 'cool-timeline' ),
					'design-6' => __( 'Modern Design', 'cool-timeline' )					
				]
				
			]
		);

		$this->add_control(
			'timeline_post',
			[
				'label' => __( 'Content Post Type', 'cool-timeline' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'post',
			]
		);

		$this->add_control(
			'show_posts',
			[
				'label' => __( 'Show number of posts', 'cool-timeline' ),
				'type' => Controls_Manager::TEXT,
				'default' => '10',
				'condition'   => [
					'timeline_layout!'   => 'default',
				]
			]
		);

		$this->add_control(
			'post_per_page',
			[
				'label' => __( 'Show posts per page', 'cool-timeline' ),
				'type' => Controls_Manager::TEXT,
				'default' => '10',
				'condition'   => [
					'timeline_layout'   => 'default',
				]
			]
		);

		$this->add_control(
			'timeline_items',
			[
				'label' => __( 'Display Stories', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 2,
				'description'=> __( "Select number of slide per view",'cool-timeline' )  ,
				'options' => [
						1 => __(1,'cool-timeline') ,
						2 => __(2,'cool-timeline') ,
						3 => __(3,'cool-timeline') ,
						4 => __(4,'cool-timeline') 
				],
				'condition'   => [
					'timeline_layout'   => 'horizontal',
				]
				
			]
		);

		$this->add_control(
			'timeline_taxonomy',
			[
				'label' => __( 'Taxonomy Name', 'cool-timeline' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'category',
			]
		);

		$this->add_control(
			'timeline_category',
			[
				'label' => __( 'Specific Category', 'cool-timeline' ),
				'description'=> __('Add category(s) slug - comma separated','cool-timeline'),
				'type' => Controls_Manager::TEXT			
			]
		);

		$this->add_control(
			'timeline_tags',
			[
				'label' => __( 'Specific Tags', 'cool-timeline' ),
				'description'=> __('Add Tag(s) slug','cool-timeline'),
				'type' => Controls_Manager::TEXT		
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
			'date_format',
			[
				'label' => __( 'Date Formats', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => __( 'Default','cool-timeline' ) ,
                    'F j' => date_i18n( 'F j' ),
					'F j Y' => date_i18n( 'F j Y' ),
					'Y-m-d' => date_i18n( 'Y-m-d' ),
					'm/d/Y' => date_i18n( 'm/d/Y' ),
					'd/m/Y' => date_i18n( 'd/m/Y' ),
					'custom' => __( 'Custom', 'cool-timeline' ),
					'F j Y g:i A' => date_i18n( 'F j Y g:i A' ),
					'Y' => date_i18n( 'Y' ),
				]
				
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
			'animation_effects',
			[
				'label' => __( 'Animation Effects', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'fade-up',
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
                    'timeline_layout!'   => 'horizontal',
                ],
				
			]
		);

		$this->add_control(
			'timeline_icon',
			[
				'label' => __( 'icon', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => __( "Yes",'cool-timeline' ) ,
					'no'=>__('No','cool-timeline' ),
				],
				'condition'   => [
                    'timeline_layout!'   => 'horizontal',
                ],
			]
		);

		$this->add_control(
			'story_content',
			[
				'label' => __( 'Stories Description', 'cool-timeline' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'short',
				'options' => [
					'short' => __( "Summary",'cool-timeline' ) ,
					'full'=>__('Full Text','cool-timeline' ),
				]
				
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
	
		$post_type = $this->set_default($settings['timeline_post'],'post');
		$taxonomy= $this->set_default($settings['timeline_taxonomy'],"category");
		$cat = $settings['timeline_category'];
		$layout = $this->set_default($settings['timeline_layout'],'default');
		$design = $this->set_default($settings['timeline_design'],'default');
		$skin = $this->set_default($settings['timeline_skin'],'default');
		if( $layout != 'default' ){
			$no_of_post = $settings['show_posts'];
		}else{
			$no_of_post = $settings['post_per_page'];
		}
		$autoplay = $this->set_default( $settings['timeline_autoplay'],'false');
		$animation = $this->set_default( $settings['animation_effects'],'bounceInUp' );
		$order = $this->set_default($settings['story_order'],'DESC');
		$tags = $settings['timeline_tags'];
		$content = $this->set_default($settings['story_content'],'short');
		$items = $this->set_default( $settings['timeline_items'],'2' );
		$start = $this->set_default($settings['story_start_on'], 0 );
		$icon = $this->set_default( $settings['timeline_icon'], 'no' );
		$format = $this->set_default( $settings['date_format'], 'default'  );
		$pagination = $this->set_default( $settings['timeline_pagination'], 'default'  );
		$filter = $this->set_default( $settings['timeline_filters'], 'no'  );
		$filters_categories = $settings['filters_categories'];

		$vertical ='[cool-content-timeline post-type="'.$post_type.'" post-category="'.$cat.'" tags="'.$tags.'" story-content="'.$content.'" taxonomy="'.$taxonomy.'" layout="'.$layout.'" designs="'.$design.'" skin="'.$skin.'" show-posts="'.$no_of_post.'" order="'.$order.'" icons="'.$icon.'" animations="'.$animation.'" date-format="'.$format.'" pagination="'.$pagination.'" filters="'.$filter.'" filter-categories="'.$filters_categories.'"]';

		$horizontal = '[cool-content-timeline post-type="'.$post_type.'" post-category="'.$cat.'" tags="'.$tags.'" autoplay="'.$autoplay.'" story-content="'.$content.'" taxonomy="'.$taxonomy.'" layout="'.$layout.'" designs="'.$design.'" skin="'.$skin.'" show-posts="'.$no_of_post.'" order="'.$order.'" start-on="'.$start.'" icons="'.$icon.'" items="'.$items.'" date-format="'.$format.'"]';

		$timeline = $layout!='horizontal'?$vertical:$horizontal;
		
		 echo'<div class="elementor-shortcode cool-timeline-free-addon">';
		/*  if( is_admin() ){
			echo "<strong>It is only a shortcode builder. Kindly update/publish the page and check the actually cool content timeline on front-end</strong><br/>";
		} */
		 echo do_shortcode($timeline);
 		 echo'</div>';
	}

}