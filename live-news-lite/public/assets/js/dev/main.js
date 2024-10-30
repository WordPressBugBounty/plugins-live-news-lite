jQuery( document ).ready(
	function ($) {

		let daextlnl_archived_ticker_data = '';
		let daextlnl_ticker_cycles        = 0;

		/**
		 * Append the ticker in the DOM if the daextlnl_apply_ticker flag is defined.
		 */
		if ( typeof daextlnl_apply_ticker != 'undefined' && daextlnl_apply_ticker ) {

			// Append the ticker before the ending body tag.
			daextlnl_append_html();

			// Refresh the news only if the news ticker is in "open" status.
			if ( "block" === ( $( "#daextlnl-container" ).css( "display" ) ) ) {

				// Refresh the news.
				daextlnl_refresh_news();

			}

			/**
			 * If the clock is based on the user time and the clock_autoupdate option enabled set the interval used to
			 * update the clock.
			 */
			if (daextlnl_clock_source === 2 && daextlnl_clock_autoupdate === 1) {
				window.setInterval( daextlnl_set_clock_based_on_user_time, (daextlnl_clock_autoupdate_time * 1000) );
			}

		}

		/**
		 * This function is used to refresh all the data displayed in the ticker and to animate the sliding news from
		 * the initial to the final destination. It's called in the following situations:
		 *
		 * - When the document is ready
		 * - When a cycle of sliding news has finished its animation
		 * - When the news ticker is opened with the open button
		 */
		function daextlnl_refresh_news(){

			if (typeof daextlnl_ticker_transient != 'undefined' && daextlnl_ticker_transient !== null) {

				// Convert the string to a JSON object.
				try {
					daextlnl_archived_ticker_data = JSON.parse( daextlnl_ticker_transient );
				} catch (error) {
					daextlnl_archived_ticker_data = false;
				}

				// Set the transient to null so it won't be used multiple times.
				daextlnl_ticker_transient = null;

			}

			if ( daextlnl_archived_ticker_data === '' || daextlnl_archived_ticker_data === false || daextlnl_ticker_cycles >= daextlnl_cached_cycles ) {

				// Retrieve the news with ajax and refresh the news ---------------------------------------------------.

				daextlnl_ticker_cycles = 0;

				// Set ajax in synchronous mode.
				jQuery.ajaxSetup( {async:false} );

				// Prepare input for the ajax request.
				const data = {
					"action": "get_ticker_data",
					"security": window.DAEXTLNL_PARAMETERS.nonce,
					"ticker_id": daextlnl_ticker_id
				};

				// Ajax.
				$.post(
					window.DAEXTLNL_PARAMETERS.ajaxUrl,
					data,
					function (ticker_data) {

						daextlnl_archived_ticker_data = ticker_data;

						try {
							ticker_data = JSON.parse( ticker_data );
						} catch (error) {
							ticker_data = false;
						}

						daextlnl_update_the_clock( ticker_data );

						daextlnl_refresh_featured_news( ticker_data );

						daextlnl_refresh_sliding_news( ticker_data );

						daextlnl_slide_the_news();

					}
				);

				// Set ajax in asynchronous mode.
				jQuery.ajaxSetup( {async:true} );

			} else {

				// Use the current ticker data to refresh the news ----------------------------------------------------.

				daextlnl_ticker_cycles++;

				try {
					ticker_data = JSON.parse( daextlnl_archived_ticker_data );
				} catch (error) {
					ticker_data = false;
				}

				daextlnl_update_the_clock( ticker_data );

				daextlnl_refresh_featured_news( ticker_data );

				daextlnl_refresh_sliding_news( ticker_data );

				daextlnl_slide_the_news();

			}

		}

		/**
		 * Update the clock.
		 *
		 * @param ticker_data
		 */
		function daextlnl_update_the_clock(ticker_data){

			if (daextlnl_clock_source == 2) {

				// Update the clock based on the user time ------------------------------------------------------------.
				daextlnl_set_clock_based_on_user_time();

			} else {

				// Update the clock based on the server time ----------------------------------------------------------.
				const timestamp = moment.unix( ticker_data.time ).utc();
				$( "#daextlnl-clock" ).text( timestamp.format( daextlnl_clock_format ) );

			}

		}

		/**
		 * Remove the featured news title and excerpt from the DOM and uses the ticker data data to append the
		 * news featured news title and excerpt.
		 *
		 * @param ticker_data
		 */
		function daextlnl_refresh_featured_news(ticker_data){

			const single_featured_news = ticker_data.featured_news;

			const news_title   = typeof single_featured_news.newstitle !== 'undefined' ? single_featured_news.newstitle : '';
			const news_excerpt = typeof single_featured_news.newsexcerpt !== 'undefined' ? single_featured_news.newsexcerpt : '';
			const url          = typeof single_featured_news.url !== 'undefined' ? single_featured_news.url : '';

			// Delete the featured title.
			$( '#daextlnl-featured-title' ).html( "" );

			// Delete the featured excerpt.
			$( '#daextlnl-featured-excerpt' ).html( "" );

			if ( typeof url !== 'undefined' && url.length > 0 && daextlnl_enable_links ) {

				// Append the new featured title.
				$( '#daextlnl-featured-title' ).html( '<a target="' + daextlnl_target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' );

				// Append the new featured excerpt.
				$( '#daextlnl-featured-excerpt' ).html( daextlnl_htmlEscape( news_excerpt ) );

			} else {

				// Append the new featured title.
				$( '#daextlnl-featured-title' ).html( daextlnl_htmlEscape( news_title ) );

				// Append the new featured excerpt.
				$( '#daextlnl-featured-excerpt' ).html( daextlnl_htmlEscape( news_excerpt ) );

			}

		}

		/**
		 * Deletes all the sliding news from the DOM and uses the ticker data to append the news sliding news.
		 *
		 * @param ticker_data
		 */
		function daextlnl_refresh_sliding_news(ticker_data){

			// Delete the previous sliding news.
			$( '#daextlnl-slider-floating-content' ).empty();

			// Iterate over the sliding news.
			ticker_data.sliding_news.forEach(
				function (single_sliding_news) {

					const news_title               = single_sliding_news.newstitle;
					const url                      = single_sliding_news.url;
					const text_color               = single_sliding_news.text_color;
					const text_color_hover         = single_sliding_news.text_color_hover;
					const background_color         = single_sliding_news.background_color;
					const background_color_opacity = single_sliding_news.background_color_opacity;
					const image_before             = single_sliding_news.image_before;
					const image_after              = single_sliding_news.image_after;
					let style_text_color           = null;
					let style_background_color     = null
					let image_before_html          = null;
					let image_after_html           = null;

					// Generate the style for the text color.
					if ( typeof text_color !== 'undefined' && text_color.trim().length > 0 ) {
						style_text_color = 'style="color: ' + text_color + ';"';
					} else {
						style_text_color = '';
					}

					// Generate the style for the background color.
					if ( typeof background_color !== 'undefined' && background_color.trim().length > 0 ) {
						const color_a          = rgb_hex_to_dec( background_color );
						style_background_color = 'style="background: rgba(' + color_a['r'] + ',' + color_a['g'] + ',' + color_a['b'] + ',' + parseFloat( background_color_opacity ) + ');"';
					} else {
						style_background_color = '';
					}

					// Generate the image_before html.
					if (typeof image_before !== 'undefined' && image_before.trim().length > 0) {
						image_before_html = '<img class="daextlnl-image-before" src="' + image_before + '">';
					} else {
						image_before_html = '';
					}

					// Generate the image_after html.
					if (typeof image_after !== 'undefined' && image_after.trim().length > 0) {
						image_after_html = '<img class="daextlnl-image-after" src="' + image_after + '">';
					} else {
						image_after_html = '';
					}

					// Check if is set the RTL layout option.
					if ( daextlnl_rtl_layout === 0 ) {

						// LTR layout ---------------------------------------------------------------------------------.
						if ( url.length > 0 && daextlnl_enable_links ) {
							$( '#daextlnl-slider-floating-content' ).append( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<a data-text-color="' + text_color + '" onmouseout=\'jQuery(this).css("color", jQuery(this).attr("data-text-color"))\' onmouseover=\'jQuery(this).css("color", "' + text_color_hover + '" )\' ' + style_text_color + ' target="' + daextlnl_target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' + image_after_html + '</div>' );
						} else {
							$( '#daextlnl-slider-floating-content' ).append( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<span ' + style_text_color + ' >' + daextlnl_htmlEscape( news_title ) + '</span>' + image_after_html + '</div>' );
						}

					} else {

						// RTL layout ---------------------------------------------------------------------------------.
						if ( url.length > 0 && daextlnl_enable_links ) {
							$( '#daextlnl-slider-floating-content' ).prepend( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<a  data-text-color="' + text_color + '" onmouseout=\'jQuery(this).css("color", jQuery(this).attr("data-text-color"))\' onmouseover=\'jQuery(this).css("color", "' + text_color_hover + '" )\' ' + style_text_color + ' target="' + daextlnl_target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' + image_after_html + '</div>' );
						} else {
							$( '#daextlnl-slider-floating-content' ).prepend( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<span ' + style_text_color + ' >' + daextlnl_htmlEscape( news_title ) + '</span>' + image_after_html + '</div>' );
						}

					}
				}
			);

		}

		/**
		 * Slides the news with jQuery animate from the initial to the final position. When the animation is complete calls
		 * daextlnl_refresh_news() which restarts the process from the start.
		 */
		function daextlnl_slide_the_news(){

			// If the news slider is already animated then return.
			if ( ( $( '#daextlnl-slider-floating-content:animated' ).length ) == 1 ) {
				return; };

			// Get browser width.
			const window_width = $( window ).width();

			// Floating news width.
			const floating_news_width = parseInt( $( "#daextlnl-slider-floating-content" ).css( "width" ) );

			// Check if is set the RTL layout option.
			if ( daextlnl_rtl_layout == 0 ) {

				// LTR layout -----------------------------------------------------------------------------------------.

				// Position outside the screen to the left.
				const outside_left = floating_news_width + window_width;

				// Set floating content left position outside the screen.
				$( "#daextlnl-slider-floating-content" ).css( "left", window_width );

				// Start floating the news.
				$( "#daextlnl-slider-floating-content" ).animate(
					{
						left: "-=" + outside_left,
						easing: "linear"
					},
					( outside_left * 10 ),
					"linear",
					function () {

						// Animation complete.
						daextlnl_refresh_news();

					}
				);

			} else {

				// RTL layout -----------------------------------------------------------------------------------------.

				// Position outside the screen to the left.
				const outside_left = floating_news_width + window_width;

				// Set floating content left position outside the screen.
				$( "#daextlnl-slider-floating-content" ).css( "left", - floating_news_width );

				// Start floating the news.
				$( "#daextlnl-slider-floating-content" ).animate(
					{
						left: "+=" + outside_left,
						easing: "linear"
					},
					( outside_left * 10 ),
					"linear",
					function () {

						// Animation complete.
						daextlnl_refresh_news();

					}
				);

			}

		}

		/**
		 * On the click event of the "#daextlnl-close" element closes the news ticker and sends an ajax request used to save
		 * the "closed" status in the "live_news_status" cookie
		 */
		$( "#daextlnl-close" ).click(
			function () {

				// Stop the animation.
				$( "#daextlnl-slider-floating-content" ).stop();

				// Delete the previous sliding news.
				$( '#daextlnl-slider-floating-content' ).empty();

				// Hide the news container.
				$( "#daextlnl-container" ).hide();

				// Show the open button.
				$( "#daextlnl-open" ).show();

				// Prepare input for the ajax request.
				const data = {
					"action": "set_status_cookie",
					"security": window.DAEXTLNL_PARAMETERS.nonce,
					"status": "closed"
				};

				// Ajax.
				$.post(
					window.DAEXTLNL_PARAMETERS.ajaxUrl,
					data,
					function (ajax_response) {

						if ( ajax_response == "success" ) {
							// nothing
						}

					}
				);

				// Set the status hidden field to closed.
				$( "#daextlnl-status" ).attr( "value","closed" );

			}
		);

		/**
		 * On the click event of the "#daextlnl-open" element opens the news ticker and sends an ajax request used to save
		 * the "open" status in the "live_news_status" cookie
		 */
		$( "#daextlnl-open" ).click(
			function () {

				// Show the news container.
				$( "#daextlnl-container" ).show();

				// Show the open button.
				$( "#daextlnl-open" ).hide();

				daextlnl_refresh_news();

				// Prepare input for the ajax request.
				const data = {
					"action": "set_status_cookie",
					"security": window.DAEXTLNL_PARAMETERS.nonce,
					"status": "open"
				};

				// Ajax.
				$.post(
					window.DAEXTLNL_PARAMETERS.ajaxUrl,
					data,
					function (ajax_response) {

						if ( ajax_response == "success" ) {
							// nothing
						}

					}
				);

				// Set the status hidden field to open.
				$( "#daextlnl-status" ).attr( "value","open" );

			}
		);

		/**
		 * Converts certain characters to their HTML entities.
		 *
		 * @param str
		 * @returns {*}
		 */
		function daextlnl_htmlEscape(str) {
			return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#39;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
		}

		/**
		 * Appends the ticker HTML just before the ending body element.
		 */
		function daextlnl_append_html(){

			html_output = '<div id="daextlnl-container">' +

			'<!-- featured news -->' +
			'<div id="daextlnl-featured-container">' +
				'<div id="daextlnl-featured-title-container">' +
					'<div id="daextlnl-featured-title"></div>' +
				'</div>' +
				'<div id="daextlnl-featured-excerpt-container">' +
					'<div id="daextlnl-featured-excerpt"></div>' +
				'</div>' +
			'</div>' +

			'<!-- slider -->' +
			'<div id="daextlnl-slider">' +
				'<!-- floating content -->' +
				'<div id="daextlnl-slider-floating-content"></div>' +
			'</div>' +

			'<!-- clock -->' +
			'<div id="daextlnl-clock"></div>' +

			'<!-- close button -->' +
			'<div id="daextlnl-close"></div>' +

			'</div>' +

			'<!-- open button -->' +
			'<div id="daextlnl-open"></div>';

			$( 'body' ).append( html_output );

		}

		/**
		 * Uses a "Date" object to retrieve the user time and adds the clock offset of this news ticker.
		 */
		function daextlnl_set_clock_based_on_user_time(){

			// Get the current unix timestamp and add the offset.
			const timestamp = moment().unix() + daextlnl_clock_offset;

			// Convert the unix timestamp to the provided format.
			const time = moment.unix( timestamp ).format( daextlnl_clock_format );

			// Update the DOM.
			$( "#daextlnl-clock" ).text( time );

		}

		/**
		 * Given a hexadecimal rgb color an array with the 3 components converted in decimal is returned
		 *
		 * @param string The hexadecimal rgb color
		 * @return array An array with the 3 component of the color converted in decimal
		 */
		function rgb_hex_to_dec(hex){

			let r = null;
			let g = null;
			let b = null;

			// Remove the # character.
			hex = hex.replace( '#', '' );

			// Find the components of the color.
			if ( hex.length == 3 ) {
				r = parseInt( hex.substring( 0, 1 ), 16 );
				g = parseInt( hex.substring( 1, 2 ), 16 );
				b = parseInt( hex.substring( 2, 3 ), 16 );
			} else {
				r = parseInt( hex.substring( 0, 2 ), 16 );
				g = parseInt( hex.substring( 2, 4 ), 16 );
				b = parseInt( hex.substring( 4, 6 ), 16 );
			}

			// Generate the array with the components of the color.
			const color_a = new Array();
			color_a['r']  = r;
			color_a['g']  = g;
			color_a['b']  = b;

			return color_a;

		}

	}
);