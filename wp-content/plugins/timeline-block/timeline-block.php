<?php
/**
  * Plugin Name:Timeline Block
  * Plugin URI:https://cooltimeline.com
  * Description:Responsive timeline block for Gutenberg editor.
  * Version:1.0
  * Author:Cool Plugins
  * Author URI:https://coolplugins.net
  * License:GPLv2 or later
  * License URI:https://www.gnu.org/licenses/gpl-2.0.html
  * Domain Path:/languages
  * Text Domain:timeline-block
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('Timeline_Block_File', __FILE__);
define('Timeline_Block_Url', plugin_dir_url(Timeline_Block_File));
define('Timeline_Block_Dir', plugin_dir_path(__FILE__));
 
//  define("GCTL_TIMELINE", __DIR__);
/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
	
function create_timeline_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "cooltimeline/timeline-block" block first.'
		);
	}
	// Scripts.
	wp_register_script(
	'create-block-timeline-block-editor', // Handle.
	Timeline_Block_Url.'/build/blocks.build.js', // Block.build.js: We register the block here. Built with Webpack.
	array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ), // Dependencies, defined above.
	true // Enqueue the script in the footer.
	);
	// Styles.
	wp_register_style(
	'create-block-timeline-block-editor-css', // Handle.
	Timeline_Block_Url.'build/blocks.editor.build.css', // Block editor CSS.
	array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
	// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: filemtime — Gets file modification time.
	);
	wp_register_style(
	'gctl-timeline-styles-css',
	Timeline_Block_Url.'build/blocks.style.build.css',
	array()
	);

	if( ! WP_Block_Type_Registry::get_instance()->is_registered( 'essential-blocks/timeline-block' ) ) {
		register_block_type('cooltimeline/timeline-block', array(
			'style' => 'gctl-timeline-styles-css',
			'editor_style' => 'create-block-timeline-block-editor-css',
			'editor_script' => 'create-block-timeline-block-editor',
		) );
	}
}
add_action( 'init', 'create_timeline_block_init' );
