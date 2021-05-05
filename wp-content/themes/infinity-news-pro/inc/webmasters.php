<?php
/**
* Webmasters Tools.
*
* @package Infinity News
*/


add_action( 'wp_head', 'infinity_news_header_scripts',100 );
/**
 * Outputs Additional JS to site header.
 *
 * @since  1.0.0
 *
 * @return void
 */
function infinity_news_header_scripts() {
 
    $addtional_js_head = get_option( 'twp_header_script', '' );

    if ( '' === $addtional_js_head ) {

        return;

    }

    echo $addtional_js_head . "\n";

}

add_action( 'wp_footer', 'infinity_news_footer_scripts',100 );
/**
 * Outputs Additional JS to site footer.
 *
 * @since  1.0.0
 *
 * @return void
 */
function infinity_news_footer_scripts() {
 
    $addtional_js_footer = get_option( 'twp_footer_script', '' );

    if ( '' === $addtional_js_footer ) {

        return;

    }

    echo $addtional_js_footer . "\n";

}

add_action( 'wp_head', 'infinity_news_verification_meta' );
/**
 * Verification Meta.
 *
 * @since  1.0.0
 *
 * @return void
 */
function infinity_news_verification_meta() {
    
    $twp_verification_code_google = get_theme_mod('twp_verification_code_google');
    $twp_verification_code_bing = get_theme_mod('twp_verification_code_bing');
    $twp_verification_code_pinterest = get_theme_mod('twp_verification_code_pinterest');
    $twp_verification_code_alexa = get_theme_mod('twp_verification_code_alexa');
    $twp_verification_code_yandex = get_theme_mod('twp_verification_code_yandex');
    
    if( $twp_verification_code_google ){ ?>
    <meta name="google-site-verification" content="<?php echo esc_attr( $twp_verification_code_google ); ?>">
    <?php }

    if( $twp_verification_code_bing ){ ?>
    <meta name="msvalidate.01" content="<?php echo esc_attr( $twp_verification_code_bing ); ?>">
    <?php }

    if( $twp_verification_code_pinterest ){ ?>
    <meta name="p:domain_verify" content="<?php echo esc_attr( $twp_verification_code_pinterest ); ?>">
    <?php }

    if( $twp_verification_code_alexa ){ ?>
    <meta name="alexaVerifyID" content="<?php echo esc_attr( $twp_verification_code_alexa ); ?>">
    <?php }

    if( $twp_verification_code_yandex ){ ?>
    <meta name="yandex-verification" content="<?php echo esc_attr( $twp_verification_code_yandex ); ?>">
    <?php }

}