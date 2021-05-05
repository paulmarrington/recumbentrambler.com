<?php
/**
* Sanitization Functions.
*
* @package Infinity News
*/


if ( ! function_exists( 'infinity_news_sanitize_select' ) ) :

	/**
	 * Sanitize select.
	 */
	function infinity_news_sanitize_select( $input, $setting ) {

		// Ensure input is a slug.
		$input = sanitize_text_field( $input );

		// Get list of choices from the control associated with the setting.
		$choices = $setting->manager->get_control( $setting->id )->choices;

		// If the input is a valid key, return it; otherwise, return the default.
		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );

	}

endif;

if ( ! function_exists( 'infinity_news_sanitize_checkbox' ) ) :

	/**
	 * Sanitize checkbox.
	 */
	function infinity_news_sanitize_checkbox( $checked ) {

		return ( ( isset( $checked ) && true === $checked ) ? true : false );

	}

endif;


if ( ! function_exists( 'infinity_news_sanitize_repeater' ) ) :
	
	/**
	* Sanitise Repeater Field
	*/
	function infinity_news_sanitize_repeater($input){
	    $input_decoded = json_decode( $input, true );  
	    
	    if(!empty($input_decoded)) {
	        foreach ($input_decoded as $boxes => $box ){
	            foreach ($box as $key => $value){
	                $input_decoded[$boxes][$key] = $value;
	            }
	        }
	        return json_encode($input_decoded);
	    }    
	    return $input;
	}
endif;


if ( ! function_exists( 'infinity_news_meta_sanitize' ) ) :

	/**
	 * Sanitize Meta Bozes.
	 */
	function infinity_news_meta_sanitize( $input ) {

		$allowed_html = array(
						    'meta' => array(
						        'property' => array(),
						        'content' => array(),
						    ),
						);

		return wp_kses( $input, $allowed_html );

	}

endif;