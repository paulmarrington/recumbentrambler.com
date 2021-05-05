<?php
/**
 *
 * @package Themeinwp
 */

// Includes the files needed for the theme updater
if ( !class_exists( 'EDD_Theme_Updater_Admin' ) ) {
	include( dirname( __FILE__ ) . '/theme-updater-admin.php' );
}

// Loads the updater classes
$updater = new EDD_Theme_Updater_Admin(

	// Config settings
	$config = array(
		'remote_api_url' => 'http://themeinwp.com', // Site where EDD is hosted
		'item_name'      => 'Infinity News Pro', // Name of theme
		'theme_slug'     => 'infinity-news', // Theme slug
		'version'        => '1.2.6', // The current version of this theme
		'author'         => 'ThemeInWP', // The author of this theme
		'download_id'    => '', // Optional, used for generating a license renewal link
		'renew_url'      => 'http://themeinwp.com/my-account' // Optional, allows for a custom license renewal link
	),

	// Strings
	$strings = array(
		'theme-license'             => __( 'Theme License', 'infinity-news' ),
		'enter-key'                 => __( 'Enter your theme license key.', 'infinity-news' ),
		'license-key'               => __( 'License Key', 'infinity-news' ),
		'license-action'            => __( 'License Action', 'infinity-news' ),
		'deactivate-license'        => __( 'Deactivate License', 'infinity-news' ),
		'activate-license'          => __( 'Activate License', 'infinity-news' ),
		'status-unknown'            => __( 'License status is unknown.', 'infinity-news' ),
		'renew'                     => __( 'Renew?', 'infinity-news' ),
		'unlimited'                 => __( 'unlimited', 'infinity-news' ),
		'license-key-is-active'     => __( 'License key is active.', 'infinity-news' ),
		'expires%s'                 => __( 'Expires %s.', 'infinity-news' ),
		'%1$s/%2$-sites'            => __( 'You have %1$s / %2$s sites activated.', 'infinity-news' ),
		'license-key-expired-%s'    => __( 'License key expired %s.', 'infinity-news' ),
		'license-key-expired'       => __( 'License key has expired.', 'infinity-news' ),
		'license-keys-do-not-match' => __( 'License keys do not match.', 'infinity-news' ),
		'license-is-inactive'       => __( 'License is inactive.', 'infinity-news' ),
		'license-key-is-disabled'   => __( 'License key is disabled.', 'infinity-news' ),
		'site-is-inactive'          => __( 'Site is inactive.', 'infinity-news' ),
		'license-status-unknown'    => __( 'License status is unknown.', 'infinity-news' ),
		'update-notice'             => __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", 'infinity-news' ),
		'update-available'          => __('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', 'infinity-news' )
	)

);
