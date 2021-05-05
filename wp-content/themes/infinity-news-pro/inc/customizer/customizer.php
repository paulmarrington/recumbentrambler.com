<?php
/**
 * Infinity News Theme Customizer
 *
 * @package Infinity_News
 */

/** Sanitize Functions. **/
	require get_template_directory() . '/inc/customizer/default.php';

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function infinity_news_customize_register( $wp_customize ) {

	/** Sanitize Functions. **/
	require get_template_directory() . '/inc/customizer/custom-control.php';

	/** Sanitize Functions. **/
	require get_template_directory() . '/inc/customizer/sanitize.php';

	/** Top Header Options. **/
	require get_template_directory() . '/inc/customizer/top-header.php';

	/** Header Options. **/
	require get_template_directory() . '/inc/customizer/header.php';

	/** Repeater Sections Options. **/
	require get_template_directory() . '/inc/customizer/home/repeater-sections.php';

	/** Layout Options. **/
	require get_template_directory() . '/inc/customizer/layout.php';

	/** Single Post Options. **/
	require get_template_directory() . '/inc/customizer/single.php';

	/** Category Control. **/
	require get_template_directory() . '/inc/customizer/cat-color.php';

	/** Footer Options. **/
	require get_template_directory() . '/inc/customizer/footer.php';

	/** Typography. **/
	require get_template_directory() . '/inc/customizer/typography.php';

	/** Color. **/
	require get_template_directory() . '/inc/customizer/color.php';

	/** Breadcrumb. **/
	require get_template_directory() . '/inc/customizer/breadcrumb.php';

	/** Mailchimp Newsletter Options. **/
	require get_template_directory() . '/inc/customizer/mailchimp-newsletter.php';

	if( class_exists( 'Booster_Extension_Class') ){
		/** Booster Extension Options. **/
		require get_template_directory() . '/inc/customizer/booster-options.php';
	}

	if ( class_exists( 'WooCommerce' ) ) {
		/** Woocommerce. **/
		require get_template_directory() . '/inc/customizer/woocommerce.php';
	}

	/** Webmasters. **/
	require get_template_directory() . '/inc/customizer/webmasters.php';

	/** Open Graph. **/
	require get_template_directory() . '/inc/customizer/open-graph.php';

	/** Twitter Card. **/
	require get_template_directory() . '/inc/customizer/twitter-card.php';


	/** Theme Options. **/
	require get_template_directory() . '/inc/customizer/theme-option.php';

	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector'        => '.site-title a',
			'render_callback' => 'infinity_news_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector'        => '.site-description',
			'render_callback' => 'infinity_news_customize_partial_blogdescription',
		) );
	}

}
add_action( 'customize_register', 'infinity_news_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function infinity_news_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function infinity_news_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function infinity_news_customize_preview_js() {
	wp_enqueue_script( 'infinity-news-customizer', get_template_directory_uri() . '/assets/lib/default/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'infinity_news_customize_preview_js' );
