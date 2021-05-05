jQuery(document).ready(function($) {

    const cats = [];
    var ccat;
    var ccat1;
    var scat;


    function infinitynewsCheckValue(value,arr){
      var status = 'hasnot';
     
      for(var i=0; i<arr.length; i++){
        var name = arr[i];
        if(name == value){
          status = 'has';
          break;
        }
      }

      return status;
    }

    function InfinityNewsCurrent_select(cval){

        cats1 = [];
        $('.infinity-news-custom-cat-color').each(function(){

            ccat1 = $(this).find('select option:selected').val();
            if( ccat1 ){

                cats1.push(ccat1);

            }

        });

        $('.infinity-news-custom-cat-color').each(function(){

            cscat = $(this).find('select option:selected').val();

            $(this).find('select').empty().append( infinity_news_repeater.categories);

            $(this).find('select option').each( function(){
                
                if(   $(this).val() != cscat ){
                    
                    if ( $(this).val() == cval || ( infinitynewsCheckValue($(this).val(),cats1) == 'has' && $(this).val() != cscat ) ) {
                        
                        $(this).remove();
                    }
                    
                }

                if(  $(this).val() == cscat ){
                    $(this).attr("selected","selected");
                }

            });

        });

    }

    
    // Show Title Sections While Loadiong.
    $('.infinity-news-repeater-field-control').each(function(){

        ccat = $(this).find('.infinity-news-custom-cat-color select option:selected').val();
        if( ccat ){

            cats.push(ccat);

        }
        
    });

    $('.infinity-news-custom-cat-color select').change(function(){

        optionSelected = $("option:selected", this);
        var ckey = optionSelected.val();
        $("option", this).removeAttr("selected");
        $(this).val(ckey).find("option[value=" + ckey +"]").attr('selected', true);
        
        InfinityNewsCurrent_select(ckey);
    });

    // Show Title Sections While Loadiong.
    $('.infinity-news-repeater-field-control').each(function(){
    	var title = $(this).find('.home-section-type option:selected').text();
    	$(this).find('.infinity-news-repeater-field-title').text(title);
        var title_key = $(this).find('.home-section-type option:selected').val();
        if( title_key == 'latest-post' || title_key == 'recommended-posts' || title_key == 'mailchimp' ){

            $(this).find('.infinity-news-repeater-field-remove').text('');
            $(this).find('.home-section-type select option[value="grid-posts"]').remove();
            $(this).find('.home-section-type select option[value="grid-posts-2"]').remove();
            $(this).find('.home-section-type select option[value="carousel-posts"]').remove();
            $(this).find('.home-section-type select option[value="jumbotron-block"]').remove();
            $(this).find('.home-section-type select option[value="multiple-category-posts"]').remove();
            $(this).find('.home-section-type select option[value="advertise-area"]').remove();

        }else{

            $(this).find('.home-section-type select option[value="latest-post"]').remove();
            $(this).find('.home-section-type select option[value="recommended-posts"]').remove();
            $(this).find('.home-section-type select option[value="mailchimp"]').remove();

        }

        if( title_key == 'recommended-posts' ){

            $(this).find('.home-section-type select option[value="latest-post"]').remove();
            $(this).find('.home-section-type select option[value="mailchimp"]').remove();

        }
        
        if( title_key == 'latest-post' ){

            $(this).find('.home-section-type select option[value="recommended-posts"]').remove();
            $(this).find('.home-section-type select option[value="mailchimp"]').remove();

        }

         if( title_key == 'mailchimp' ){

            $(this).find('.home-section-type select option[value="recommended-posts"]').remove();
            $(this).find('.home-section-type select option[value="latest-post"]').remove();

        }

        $(this).find('.home-repeater-fields-hs').hide();
        $(this).find('.'+title_key+'-fields').show();

        scat = $(this).find('.infinity-news-custom-cat-color select option:selected').val();

        $(this).find('.infinity-news-custom-cat-color select option').each( function(){
            
            if ( infinitynewsCheckValue($(this).val(),cats) == 'has' && $(this).val() != scat ) {
              $(this).remove();
            }

        });
        
    });

    $('.infinity-news-custom-cat-color').each(function(){

        var catTitle = $(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-custom-cat-color option:selected').text();
        $(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-repeater-field-title').text( catTitle );

    });

    $('.infinity-news-custom-cat-color select').change(function(){

        var optionSelected = $("option:selected", this);
        var textSelected   = optionSelected.text();
        var title_key = optionSelected.val();

        $(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-repeater-field-title').text( textSelected );

    });

    // Show Title After Secect Section Type.
    $('.home-section-type select').change(function(){

    	var optionSelected = $("option:selected", this);
     	var textSelected   = optionSelected.text();
        var title_key = optionSelected.val();

        $(this).closest('.infinity-news-repeater-field-control').find('.home-repeater-fields-hs').hide();
        $(this).closest('.infinity-news-repeater-field-control').find('.'+title_key+'-fields').show();

    	$(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-repeater-field-title').text( textSelected );

    });

    // Save Value.
    function infinity_news_refresh_repeater_values(){

        $(".infinity-news-repeater-field-control-wrap").each(function(){
            
            var values = []; 
            var $this = $(this);
            
            $this.find(".infinity-news-repeater-field-control").each(function(){
            var valueToPush = {};   

            $(this).find('[data-name]').each(function(){
                var dataName = $(this).attr('data-name');
                var dataValue = $(this).val();
                valueToPush[dataName] = dataValue;
            });

            values.push(valueToPush);
            });

            $this.next('.infinity-news-repeater-collector').val( JSON.stringify( values ) ).trigger('change');
        });

    }

    $("body").on("click",'.infinity-news-add-control-field', function(){

        var $this = $(this).parent();
        if(typeof $this != 'undefined') {

            var field = $this.find(".infinity-news-repeater-field-control:first").clone();


            if(typeof field != 'undefined'){
                
                field.find("input[type='text'][data-name]").each(function(){
                    var defaultValue = $(this).attr('data-default');
                    $(this).val(defaultValue);
                });

                field.find("textarea[data-name]").each(function(){
                    var defaultValue = $(this).attr('data-default');
                    $(this).val(defaultValue);
                });

                field.find("select[data-name]").each(function(){
                    var defaultValue = $(this).attr('data-default');
                    $(this).val(defaultValue);
                });


                field.find(".selector-labels label").each(function(){
                    var defaultValue = $(this).closest('.selector-labels').next('input[data-name]').attr('data-default');
                    var dataVal = $(this).attr('data-val');
                    $(this).closest('.selector-labels').next('input[data-name]').val(defaultValue);

                    if(defaultValue == dataVal){
                        $(this).addClass('selector-selected');
                    }else{
                        $(this).removeClass('selector-selected');
                    }
                });
                
                field.find('.infinity-news-fields').show();

                $this.find('.infinity-news-repeater-field-control-wrap').append(field);
                $('.accordion-section-content').animate({ scrollTop: $this.height() }, 1000);
                infinity_news_refresh_repeater_values();
            }

            // Show Title After Secect Section Type.
    	    $('.home-section-type select').change(function(){
    	    	var optionSelected = $("option:selected", this);
    	     	var textSelected   = optionSelected.text();
                var title_key = optionSelected.val();

                $(this).closest('.infinity-news-repeater-field-control').find('.home-repeater-fields-hs').hide();
                $(this).closest('.infinity-news-repeater-field-control').find('.'+title_key+'-fields').show();

    	    	$(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-repeater-field-title').text(textSelected);

    	    });

            $('.infinity-news-custom-cat-color select').change(function(){
                var optionSelected = $("option:selected", this);
                var textSelected   = optionSelected.text();
                var title_key = optionSelected.val();

                $(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-repeater-field-title').text(textSelected);

            });

            $('.infinity-news-repeater-field-control-wrap li:last-child').find('.home-repeater-fields-hs').hide();
            $('.infinity-news-repeater-field-control-wrap li:last-child').find('.grid-posts-fields').show();

            $('.infinity-news-repeater-field-control-wrap li').removeClass('twp-sortable-active');
            $('.infinity-news-repeater-field-control-wrap li:last-child').addClass('twp-sortable-active');
            $('.infinity-news-repeater-field-control-wrap li:last-child .infinity-news-repeater-fields').addClass('twp-sortable-active extended');
            $('.infinity-news-repeater-field-control-wrap li:last-child .infinity-news-repeater-fields').show();

            $('.infinity-news-repeater-field-control.twp-sortable-active .title-rep-wrap').click(function(){
                $(this).next('.infinity-news-repeater-fields').slideToggle();
            }); 

            $('.infinity-news-repeater-field-control-wrap li:last-child .infinity-news-repeater-field-title').text(infinity_news_repeater.new_section);
            $this.find(".infinity-news-repeater-field-control:last .home-section-type select").empty().append( infinity_news_repeater.optionns);

            field.find('.customizer-color-picker').each(function(){

                if( $(this).closest('.infinity-news-repeater-field-control').hasClass('twp-sortable-active') ){
                    
                    $(this).closest('.infinity-news-repeater-field-control').find('.wp-picker-container').addClass('old-one');
                    $(this).closest('.infinity-news-repeater-field-control').find('.infinity-news-type-colorpicker .description.customize-control-description').after('<input data-default="" class="customizer-color-picker" data-alpha="true" data-name="category_color" type="text" value="#787878">');
                    
                    $(this).closest('.infinity-news-repeater-field-control').find('.customizer-color-picker').wpColorPicker({
                        defaultColor: '#d0021b',
                        change: function(event, ui){
                            setTimeout(function(){
                            infinity_news_refresh_repeater_values();
                            }, 100);
                        }
                    }).parents('.customizer-type-colorpicker').find('.wp-color-result').first().remove();

                    $(this).closest('.infinity-news-repeater-field-control').find('.old-one').remove();

                }
            });
            

            var cats2 = '';
            $('.infinity-news-custom-cat-color').each(function(){

                cats2 = $(this).find('select option:selected').val();
                if(cats2) {
                    return false; // breaks
                }

            });

            // Category Color Code Start
            field.val(cats2).find("select option[value=" + cats2 +"]").remove();

            field.find('.infinity-news-custom-cat-color select').change(function(){

                optionSelected1 = $("option:selected", this);
                var ckey1 = optionSelected1.val();
                $(this).val(ckey1).find("option[value=" + ckey1 +"]").attr('selected', true);
                
                InfinityNewsCurrent_select(ckey1);
            });

            // Category Color Code end

        }
        return false;
    });
    
    $('.infinity-news-repeater-field-control .title-rep-wrap').click(function(){
        $(this).next('.infinity-news-repeater-fields').slideToggle().toggleClass('extended');
    });

    //MultiCheck box Control JS
    $( 'body' ).on( 'change', '.infinity-news-type-multicategory input[type="checkbox"]' , function() {
        var checkbox_values = $( this ).parents( '.infinity-news-type-multicategory' ).find( 'input[type="checkbox"]:checked' ).map(function(){
            return $( this ).val();
        }).get().join( ',' );
        $( this ).parents( '.infinity-news-type-multicategory' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
        infinity_news_refresh_repeater_values();
    });

    //Checkbox Multiple Control
    $( '.customize-control-checkbox-multiple input[type="checkbox"]' ).on( 'change', function() {
        checkbox_values = $( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
            function() {
                return this.value;
            }
        ).get().join( ',' );

        $( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
    });

    
    $('.customizer-color-picker').each(function(){
        $(this).wpColorPicker({
            change: function(event, ui){
                setTimeout(function(){
                infinity_news_refresh_repeater_values();
                }, 100);
            }
        }).parents('.customizer-type-colorpicker').find('.wp-color-result').first().remove();
    });

    // ADD IMAGE LINK
    $('.customize-control-repeater').on( 'click', '.twp-img-upload-button', function( event ){
        event.preventDefault();

        var imgContainer = $(this).closest('.twp-img-fields-wrap').find( '.thumbnail-image'),
        placeholder = $(this).closest('.twp-img-fields-wrap').find( '.placeholder'),
        imgIdInput = $(this).siblings('.upload-id');

        // Create a new media frame
        frame = wp.media({
            title: infinity_news_repeater.upload_image,
            button: {
            text: infinity_news_repeater.use_imahe
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {

        // Get media attachment details from the frame state
        var attachment = frame.state().get('selection').first().toJSON();

        // Send the attachment URL to our custom image input field.
        imgContainer.html( '<img src="'+attachment.url+'" style="max-width:100%;"/>' );
        placeholder.addClass('hidden');

        // Send the attachment id to our hidden input
        imgIdInput.val( attachment.url ).trigger('change');

        });

        // Finally, open the modal on click
        frame.open();
    });
    // DELETE IMAGE LINK
    $('.customize-control-repeater').on( 'click', '.twp-img-delete-button', function( event ){

        event.preventDefault();
        var imgContainer = $(this).closest('.twp-img-fields-wrap').find( '.thumbnail-image'),
        placeholder = $(this).closest('.twp-img-fields-wrap').find( '.placeholder'),
        imgIdInput = $(this).siblings('.upload-id');

        // Clear out the preview image
        imgContainer.find('img').remove();
        placeholder.removeClass('hidden');

        // Delete the image id from the hidden input
        imgIdInput.val( '' ).trigger('change');

    });

    $("#customize-theme-controls").on("click", ".infinity-news-repeater-field-remove",function(){
        if( typeof  $(this).parent() != 'undefined'){
            $(this).closest('.infinity-news-repeater-field-control').slideUp('normal', function(){
                $(this).remove();
                infinity_news_refresh_repeater_values();
            });
            
        }
        return false;
    });

    $('.wp-picker-clear').click(function(){
         infinity_news_refresh_repeater_values();
    });

    $('#customize-theme-controls').on('click', '.infinity-news-repeater-field-close', function(){
        $(this).closest('.infinity-news-repeater-fields').slideUp();
        $(this).closest('.infinity-news-repeater-field-control').toggleClass('expanded');
    });

    /*Drag and drop to change order*/
    $(".infinity-news-repeater-field-control-wrap").sortable({
        axis: 'y',
        orientation: "vertical",
        update: function( event, ui ) {
            infinity_news_refresh_repeater_values();
        }
    });

    $("#customize-theme-controls").on('keyup change', '[data-name]',function(){
         infinity_news_refresh_repeater_values();
         return false;
    });

    $("#customize-theme-controls").on('change', 'input[type="checkbox"][data-name]',function(){
        if($(this).is(":checked")){
            $(this).val('yes');
        }else{
            $(this).val('no');
        }
        infinity_news_refresh_repeater_values();
        return false;
    });

});