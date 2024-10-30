/**
 * This file is used to handle initialize Select2, update the default colors, and initialize the color picker in the
 * Sliding News menu.
 *
 * @package live-news-lite
 */

jQuery( document ).ready(
	function ($) {

		$( '#ticker_id' ).select2();

		initWpColorPickerFields();

		$( '#ticker_id' ).change(
			function () {

				daextlnl_update_default_colors();

			}
		);

		/*
		* Update the default 'Text Color', 'Text Color Hover' and 'Background Color' based on the values available on the
		* related ticker
		*/
		function daextlnl_update_default_colors(){

			var ticker_id = parseInt( $( '#ticker_id' ).val(), 10 );

			// Prepare input for the ajax request.
			var data = {
				"action": "update_default_colors",
				"security": window.DAEXTLNL_PARAMETERS.nonce,
				"ticker_id": ticker_id
			};

			// Ajax.
			$.post(
				window.DAEXTLNL_PARAMETERS.ajaxUrl,
				data,
				function (result_json) {

					var data_obj = $.parseJSON( result_json );

					$( '#text_color' ).iris( 'color', data_obj.sliding_news_color );
					$( '#text_color_hover' ).iris( 'color', data_obj.sliding_news_color_hover );
					$( '#background_color' ).iris( 'color', data_obj.sliding_news_background_color );

				}
			);

		}

		/**
		 * Initialize the wp color picker fields.
		 */
		function initWpColorPickerFields(){

			'use strict';

			const config = {
				'palettes': []
			};

			$( '#text_color' ).wpColorPicker( config );
			$( '#text_color_hover' ).wpColorPicker( config );
			$( '#background_color' ).wpColorPicker( config );

		}

	}
);