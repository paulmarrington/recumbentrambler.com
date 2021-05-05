<?php

if ( ! defined( 'ABSPATH' ) )
    exit;



/**
 *  Elementor
 *  @TODO: temporary solution
 *
 *  @since    2.5
 *  @created  28/01/18
 */

add_action( 'elementor/editor/after_enqueue_scripts', 'wpuxss_eml_elementor_scripts' );

if ( ! function_exists( 'wpuxss_eml_elementor_scripts' ) ) {

    function wpuxss_eml_elementor_scripts() {

        global $wpuxss_eml_dir;


        wp_enqueue_style( 'common' );
        wp_enqueue_style(
            'wpuxss-eml-elementor-media-style',
            $wpuxss_eml_dir . 'css/eml-admin-media.css'
        );
    }
}




if ( wpuxss_eml_enhance_media_shortcodes() ) {

    /**
     *  Enfold Theme
     *  for [av_masonry_gallery] shortcode
     *
     *  Use Default Layout and choose the shortcode Media Elements > Masonry Gallery 
     *  to make theme gallery shows images from the specific category.
     *
     *  @since    2.8
     *  @created  9/10/20
     */

	add_filter( 'shortcode_atts_av_masonry_entries', 'wpuxss_eml_shortcode_atts', 10, 3 );


    /**
     *  FooGallery
     *
     *  @since    2.8.4
     *  @created  08/04/21
     */

    add_filter( 'foogallery_shortcode_atts', 'wpuxss_eml_foogallery_shortcode_atts' );
}



if ( ! function_exists( 'wpuxss_eml_foogallery_shortcode_atts' ) ) {

    function wpuxss_eml_foogallery_shortcode_atts( $atts ) {

        $id = isset( $atts['id'] ) ? intval( $atts['id'] ) : 0;
        unset( $atts['id'] );

        $atts = wpuxss_eml_shortcode_atts( array(), array(), $atts );
        $atts['id'] = $id;

        if ( isset( $atts['ids'] ) ) {
            $atts['attachment_ids'] = $atts['ids'];
            unset( $atts['ids'] );
        }

        return $atts;
    }
}
