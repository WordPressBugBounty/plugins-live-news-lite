jQuery(document).ready(function($) {

  'use strict';

    //.button_add_media click event handler
    $(document.body).on('click', '.button_add_media' , function( event ){

      'use strict';

      //will be used to store the wp.media object
      let file_frame;

        //prevent the default behavior of this event
        event.preventDefault();

        //save this in a variable
        let da_media_button = $(this);

        if($(this).attr('data-set-remove') == "set"){

            //reopen the media frame if already exists
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            //extend the wp.media object
            file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'Insert image' ),
                button: {
                  text: $( this ).data( 'Insert image' ),
                },
                multiple: false//false -> allows single file | true -> allows multiple files
            });

            //run a callback when an image is selected
            file_frame.on( 'select', function() {

              'use strict';

              //get the attachment from the uploader
              let attachment = file_frame.state().get('selection').first().toJSON();

              //change the da_media_button label
              da_media_button.text(da_media_button.attr('data-remove'));

              //change the da_media_button current status
              da_media_button.attr('data-set-remove', 'remove');

              //assign the attachment.url ( or attachment.id ) to the DOM element ( an input text ) that comes just before the "Add Media" button
              da_media_button.prev().val(attachment.url);

              //assign the attachment.url to the src of the image two times before the "Add Media" button
              da_media_button.prev().prev().attr("src",attachment.url);

              //show the image
              da_media_button.prev().prev().show();

            });

            //open the modal window
            file_frame.open();

        }else{

            //change the da_media_button label
            da_media_button.html(da_media_button.attr('data-set'))

            //change the da_media_button current status
            da_media_button.attr('data-set-remove', 'set');

            //hide the game image
            da_media_button.prev().prev().hide();

            //set empty to the hidden field
            da_media_button.prev().val("");

        }

    });

});