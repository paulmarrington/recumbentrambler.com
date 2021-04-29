<?php
namespace CoolTimelineAddonWidget;

use CoolTimelineAddonWidget\Widgets\CoolTimelineAddonWidget;
use CoolTimelineAddonWidget\Widgets\CoolContentTimelineAddonWidget;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// Add a custom category for panel widgets
add_action( 'elementor/init', function() {
   \Elementor\Plugin::$instance->elements_manager->add_category( 
   	'cool-timeline',                 // the name of the category
   	[
   		'title' => esc_html__( 'Cool Timeline Pro', 'cool-timeline' ),
   		'icon' => 'fa fa-header', //default icon
   	],
   	1 // position
   );
} );

/**
 * Main Plugin Class
 *
 * Register new elementor widget.
 *
 * @since 1.0.0
 */
class CoolTimelineAddon {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		$this->clta_add_actions();
	}

	/**
	 * Add Actions
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function clta_add_actions() {
		add_action( 'elementor/widgets/widgets_registered', array($this, 'ctla_on_widgets_registered' ));

		add_action( 'elementor/frontend/after_register_scripts', function() {
			wp_register_script( 'ctla', plugins_url( '/assets/js/hello-world.js', __FILE__ ), array( 'jquery'), false, true );
		} );
	}

	/**
	 * On Widgets Registered
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function ctla_on_widgets_registered() {
		$this->clta_includes();
		$this->ctla_register_widget();
	}

	/**
	 * Includes
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function clta_includes() {
		require __DIR__ . '/widgets/cool-timeline-addon-widget.php';
		require __DIR__ . '/widgets/cool-content-timeline-addon-widget.php';
	}

	/**
	 * Register Widget
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function ctla_register_widget() {
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CoolTimelineAddonWidget() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new CoolContentTimelineAddonWidget() );

	}
}

new CoolTimelineAddon();
