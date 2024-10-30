/**
 * This file is used handle the media uploader in the Live News admin menus.
 *
 * @package live-news-lite
 */

jQuery( document ).ready(
	function ($) {

		// Will be used to store the wp.media object.
		var file_frame;

		// .button_add_media click event handler
		$( document.body ).on(
			'click',
			'.button_add_media' ,
			function ( event ) {

				// Prevent the default behavior of this event.
				event.preventDefault();

				// Save this in a variable.
				da_media_button = $( this );

				if ($( this ).attr( 'data-set-remove' ) === "set") {

					// Reopen the media frame if already exists.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Extend the wp.media object.
					file_frame = wp.media.frames.file_frame = wp.media(
						{
							title: $( this ).data( 'Insert image' ),
							button: {
								text: $( this ).data( 'Insert image' ),
							},
							multiple: false// false -> allows single file | true -> allows multiple files.
						}
					);

					// Run a callback when an image is selected.
					file_frame.on(
						'select',
						function () {

							// Get the attachment from the uploader.
							attachment = file_frame.state().get( 'selection' ).first().toJSON();

							// Change the da_media_button label.
							da_media_button.text( da_media_button.attr( 'data-remove' ) );

							// Change the da_media_button current status.
							da_media_button.attr( 'data-set-remove', 'remove' );

							// assign the attachment.url ( or attachment.id ) to the DOM element ( an input text ) that comes just before the "Add Media" button.
							da_media_button.prev().val( attachment.url );

							// Assign the attachment.url to the src of the image two times before the "Add Media" button.
							da_media_button.prev().prev().attr( "src",attachment.url );

							// Show the image.
							da_media_button.prev().prev().show();

						}
					);

					// Open the modal window.
					file_frame.open();

				} else {

					// Change the da_media_button label.
					da_media_button.html( da_media_button.attr( 'data-set' ) )

					// Change the da_media_button current status.
					da_media_button.attr( 'data-set-remove', 'set' );

					// Hide the game image.
					da_media_button.prev().prev().hide();

					// Set empty to the hidden field.
					da_media_button.prev().val( "" );

				}

			}
		);

	}
);