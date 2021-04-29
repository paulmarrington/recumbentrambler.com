<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//register_activation_hook( __FILE__, 'ctla_activate_required_plugins' );

/**
 *
 * Load the plugin after cool-timeline-pro (and other plugins) are loaded.
 *
 * @since 1.0.0
 */

add_action( 'plugins_loaded', 'ctla_load' );
function ctla_load() {
	// Load localization file
	load_plugin_textdomain( 'cool-timeline' );
	// Require the main plugin file
	require( __DIR__ . '/class-cool-timeline-addon.php' );
}	// end of ctla_load()
