jQuery(document).ready(function($) {
	
	/** Radio Image **/
    $('.radio-image-buttenset').each(function(){
        
        var id = $(this).attr('id');
        $( '[id='+id+']' ).buttonset();
    });


    // Primary Font Wgight Active on font select.
    jQuery('#_customize-input-twp_primary_font').on('change',function(){

        var family = $(this).val();
        var ajaxurl = infinity_news_customizer.ajax_url;
        var data = {
            'action': 'infinity_news_fonts_ajax',
            'family': family,
        };
 
        $.post(ajaxurl, data, function( response ) {
            var select = $('#_customize-input-twp_primary_font_weight');
            select.empty().append(response);
             wp.customize( 'twp_primary_font_weight', function ( obj ) {
                obj.set( 400 );
            } );

        });

    });
    
    // Secondary Font Wgight Active on font select.
    jQuery('#_customize-input-twp_secondary_font').on('change',function(){

        var family = $(this).val();
        var ajaxurl = infinity_news_customizer.ajax_url;
        var data = {
            'action': 'infinity_news_fonts_ajax',
            'family': family,
        };
 
        $.post(ajaxurl, data, function( response ) {
            var select = $('#_customize-input-twp_secondary_font_weight');
            select.empty().append(response);
             wp.customize( 'twp_secondary_font_weight', function ( obj ) {
                obj.set( 400 );
            } );

        });

    });
    
});