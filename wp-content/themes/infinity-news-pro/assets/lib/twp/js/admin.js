var custom_theme_file_frame;

jQuery(function($){

      // Uploads.
      jQuery(document).on('click', 'input.select-img', function( event ){

        var $this = $(this);

        event.preventDefault();

        var CustomThemeImage = wp.media.controller.Library.extend({
            defaults :  _.defaults({
                    id:        'custom-theme-insert-image',
                    title:      $this.data( 'uploader_title' ),
                    allowLocalEdits: false,
                    displaySettings: true,
                    displayUserSettings: false,
                    multiple : false,
                    library: wp.media.query( { type: 'image' } )
              }, wp.media.controller.Library.prototype.defaults )
        });

        // Create the media frame.
        custom_theme_file_frame = wp.media.frames.custom_theme_file_frame = wp.media({
          button: {
            text: jQuery( this ).data( 'uploader_button_text' )
          },
          state : 'custom-theme-insert-image',
              states : [
                  new CustomThemeImage()
              ],
          multiple: false
        });

        // When an image is selected, run a callback.
        custom_theme_file_frame.on( 'select', function() {

          var state = custom_theme_file_frame.state('custom-theme-insert-image');
          var selection = state.get('selection');
          var display = state.display( selection.first() ).toJSON();
          var obj_attachment = selection.first().toJSON();
          display = wp.media.string.props( display, obj_attachment );

          var image_field = $this.siblings('.img');
          var imgurl = display.src;

          // Copy image URL.
          image_field.val(imgurl);
          image_field.trigger('change');
          // Show in preview.
          var image_preview_wrap = $this.siblings('.image-preview-wrap');
          var image_html = '<img src="' + imgurl+ '" alt="" style="width:200px;height:200px;" />';
          image_preview_wrap.html( image_html );
          // Show Remove button.
          var image_remove_button = $this.siblings('.btn-image-remove');
          image_remove_button.css('display','inline-block');
        });

        // Finally, open the modal.
        custom_theme_file_frame.open();
      });

      // Remove image.
      jQuery(document).on('click', 'input.btn-image-remove', function( e ) {

        e.preventDefault();
        var $this = $(this);
        var image_field = $this.siblings('.img');
        image_field.val('');
        var image_preview_wrap = $this.siblings('.image-preview-wrap');
        image_preview_wrap.html('');
        $this.css('display','none');
        image_field.trigger('change');

      });

      $('.infinity-img-upload-button').click( function(){
        event.preventDefault();
        var imgContainer = $(this).closest('.infinity-img-fields-wrap').find( '.infinity-thumbnail-image .twp-img-container'),
        removeimg = $(this).closest('.infinity-img-fields-wrap').find( '.infinity-img-delete-button'),
        imgIdInput = $(this).siblings('.upload-id');
        var frame;
        // Create a new media frame
        frame = wp.media({
            title: infinity_news_admin.upload_image,
            button: {
            text: infinity_news_admin.use_imahe
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });
        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            // Send the attachment URL to our custom image input field.
            imgContainer.html( '<img src="'+attachment.url+'" />' );
            removeimg.addClass('twp-img-show');
            // Send the attachment id to our hidden input
            imgIdInput.val( attachment.url ).trigger('change');
        });
        // Finally, open the modal on click
        frame.open();
    });

    // DELETE IMAGE LINK
    $('.infinity-img-delete-button').click( function(){
        event.preventDefault();
        var imgContainer = $(this).closest('.infinity-img-fields-wrap').find( '.infinity-thumbnail-image .twp-img-container');
        var removeimg = $(this).closest('.infinity-img-fields-wrap').find( '.infinity-img-delete-button');
        var imgIdInput = $(this).closest('.infinity-img-fields-wrap').find( '.upload-id');
        // Clear out the preview image
        imgContainer.find('img').remove();
        removeimg.removeClass('twp-img-show');
        // Delete the image id from the hidden input
        imgIdInput.val( '' ).trigger('change');
    });

    // Metabox Tab
    $('.infinity-metabox-tab a').click(function (){
        var tabid = $(this).attr('id');
        $('.infinity-metabox-tab a').removeClass('twp-tab-active');
        $(this).addClass('twp-tab-active');
        $('.infinity-tab-content .infinity-content-wrap').hide();
        $('.infinity-tab-content #'+tabid+'-content').show();
        $('.infinity-tab-content .infinity-content-wrap').removeClass('infinity-tab-content-active');
        $('.infinity-tab-content #'+tabid+'-content').addClass('infinity-tab-content-active');
    });

});
