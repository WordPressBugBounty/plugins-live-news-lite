jQuery(document).ready(function($) {

	'use strict';

	let daextlnl_archived_ticker_data_xml = '';
	let daextlnl_ticker_cycles = 0;
	let mobile_detect = new MobileDetect(window.navigator.userAgent);
	let mobile_device = mobile_detect.mobile();

	/*
	 * Append the ticker in the DOM if the window.DAEXTLNL_DATA.apply_ticker flag is defined
	 */
	if ( typeof window.DAEXTLNL_DATA != 'undefined' && window.DAEXTLNL_DATA.apply_ticker ){

		//Do not create the news ticker if the "Enable with Mobile Devices" option is set to "No"
		if(window.DAEXTLNL_DATA.enable_with_mobile_devices === false && mobile_device !== null){
			return;
		}

		/*
    * If in "Hide the featured news" is selected "Yes" or if is selected "Only with Mobile Devices" and
    * the current device is a mobile device hide the featured news area and remove the button used to open
    * and close the featured news area.
    */
		if ( window.DAEXTLNL_DATA.hide_featured_news === 2 || ( window.DAEXTLNL_DATA.hide_featured_news == 3 && mobile_device !== null ) ){

			$("<style>")
			.prop("type", "text/css")
			.html("#daextlnl-container{ min-height: 40px; }#daextlnl-featured-container{ display: none; }")
			.appendTo("head");

		}

		//append the ticker before the ending body tag
		daextlnl_append_html();

		//refresh the news only if the news ticker is in "open" status
		if (( $("#daextlnl-container").css("display") == "block") ){

			//refresh the news
			daextlnl_refresh_news();

		}

		/*
		 * If the clock is based on the user time and the clock_autoupdate option enabled set the interval used to
		 * update the clock
		 */
		if(window.DAEXTLNL_DATA.clock_source == 2 && window.DAEXTLNL_DATA.clock_autoupdate == 1) {
			window.setInterval(daextlnl_set_clock_based_on_user_time, (window.DAEXTLNL_DATA.clock_autoupdate_time * 1000) );
		}

	}

	/*
	 * This function is used to refresh all the data displayed in the ticker and to animate the sliding news from the
	 * initial to the final destination. It's called in the following situations:
	 *
	 * - When the document is ready
	 * - When a cycle of sliding news has finished its animation
	 * - When the news ticker is opened with the open button
	 */
	function daextlnl_refresh_news(){

		'use strict';

		if(typeof window.DAEXTLNL_DATA.ticker_transient != 'undefined' && window.DAEXTLNL_DATA.ticker_transient !== null){

			//Convert the XML string to a JavaScript XML Document
      daextlnl_archived_ticker_data_xml = $.parseXML(window.DAEXTLNL_DATA.ticker_transient);

			//Set the transient to null so it won't be used multiple times
			window.DAEXTLNL_DATA.ticker_transient = null;

		}

		if( $.isXMLDoc( daextlnl_archived_ticker_data_xml) === false || daextlnl_ticker_cycles >= window.DAEXTLNL_DATA.cached_cycles ){

			//retrieve the news with ajax and refresh the news ---------------------------------------------------------

			daextlnl_ticker_cycles = 0;

			//set ajax in synchronous mode
			jQuery.ajaxSetup({async:false});

			//prepare input for the ajax request
			let data = {
				"action": "get_ticker_data",
				"security": window.DAEXTLNL_DATA.nonce,
				"ticker_id": window.DAEXTLNL_DATA.ticker_id
			};

			//ajax
			$.post(window.DAEXTLNL_DATA.ajax_url, data, function(ticker_data_xml) {

				'use strict';

				daextlnl_archived_ticker_data_xml = ticker_data_xml;

				daextlnl_update_the_clock(ticker_data_xml);

				daextlnl_refresh_featured_news(ticker_data_xml);

				daextlnl_refresh_sliding_news(ticker_data_xml);

				daextlnl_slide_the_news();

			});

			//set ajax in asynchronous mode
			jQuery.ajaxSetup({async:true});

		}else{

			//use the current ticker xml data to refresh the news ------------------------------------------------------

			daextlnl_ticker_cycles++;

			daextlnl_update_the_clock(daextlnl_archived_ticker_data_xml);

			daextlnl_refresh_featured_news(daextlnl_archived_ticker_data_xml);

			daextlnl_refresh_sliding_news(daextlnl_archived_ticker_data_xml);

			daextlnl_slide_the_news();

		}

	}

	/*
	 * Update the clock
	 */
	function daextlnl_update_the_clock(ticker_data_xml){

		'use strict';

		if(window.DAEXTLNL_DATA.clock_source == 2){

			//update the clock based on the user time ------------------------------------------------------------------
			daextlnl_set_clock_based_on_user_time();

		}else{

			//update the clock based on the server time ----------------------------------------------------------------
			let currentTime = $(ticker_data_xml).find('time').text();

      let timestamp = moment.unix(currentTime).utc();
			$("#daextlnl-clock").text(timestamp.format(window.DAEXTLNL_DATA.clock_format));

		}

	}

	/*
	 * Remove the featured news title and excerpt from the DOM and uses the ticker data in XML format data to append the
	 * news featured news title and excerpt
	 */
	function daextlnl_refresh_featured_news(ticker_data_xml){

		'use strict';

		//parse the xml string
		$(ticker_data_xml).find("featurednews news").each(function(){

			'use strict';

			let news_title = $(this).find("newstitle").text();
			let news_excerpt = $(this).find("newsexcerpt").text();
			let url = $(this).find("url").text();

			//Delete the featured title
			$('#daextlnl-featured-title').html("");

			//Delete the featured excerpt
			$('#daextlnl-featured-excerpt').html("");

			if( url.length > 0 && window.DAEXTLNL_DATA.enable_links ){

				//Append the new featured title
				$('#daextlnl-featured-title').html( '<a target="' + window.DAEXTLNL_DATA.target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' );

				//Append the new featured excerpt
				$('#daextlnl-featured-excerpt').html( daextlnl_htmlEscape( news_excerpt ) );

			}else{

				//Append the new featured title
				$('#daextlnl-featured-title').html(  daextlnl_htmlEscape( news_title )  );

				//Append the new featured excerpt
				$('#daextlnl-featured-excerpt').html( daextlnl_htmlEscape( news_excerpt ) );

			}

		});

	}

	/*
	 * Deletes all the sliding news from the DOM and uses the ticker data in XML format to append the news sliding news
	 */
	function daextlnl_refresh_sliding_news(ticker_data_xml){

		'use strict';

		//Delete the previous sliding news
		$('#daextlnl-slider-floating-content').empty();

		//parse the xml string
		$(ticker_data_xml).find("slidingnews news").each(function(){

			let news_title = $(this).find("newstitle").text();
			let url = $(this).find("url").text();
			let text_color = $(this).find("text_color").text();
			let text_color_hover = $(this).find("text_color_hover").text();
			let background_color = $(this).find("background_color").text();
			let background_color_opacity = $(this).find("background_color_opacity").text();
			let image_before = $(this).find("image_before").text();
			let image_after = $(this).find("image_after").text();
			let style_text_color = null;
			let style_background_color = null;
			let image_before_html = null;
			let image_after_html = null;

			//generate the style for the text color
			if( text_color.trim().length > 0 ){
				style_text_color = 'style="color: ' + text_color + ';"';
			}else{
				style_text_color = '';
			}

			//generate the style for the background color
			if( background_color.trim().length > 0 ) {
				let color_a = rgb_hex_to_dec(background_color);
				style_background_color = 'style="background: rgba(' + color_a['r'] + ',' + color_a['g'] + ',' + color_a['b'] + ',' + parseFloat(background_color_opacity) + ');"';
			}else{
				style_background_color = '';
			}

			//generate the image_before html
			if(image_before.trim().length > 0){
				image_before_html = '<img class="daextlnl-image-before" src="' + image_before + '">';
			}else{
				image_before_html = '';
			}

			//generate the image_after html
			if(image_after.trim().length > 0){
				image_after_html = '<img class="daextlnl-image-after" src="' + image_after + '">';
			}else{
				image_after_html = '';
			}

			//check if is set the RTL layout option
			if( window.DAEXTLNL_DATA.rtl_layout == 0 ){

				//LTR layout -----------------------------------------------------------------------
				if( url.length > 0 && window.DAEXTLNL_DATA.enable_links ){
					$('#daextlnl-slider-floating-content').append( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<a data-text-color="' + text_color + '" onmouseout=\'jQuery(this).css("color", jQuery(this).attr("data-text-color"))\' onmouseover=\'jQuery(this).css("color", "' + text_color_hover + '" )\' ' + style_text_color + ' target="' + window.DAEXTLNL_DATA.target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' + image_after_html + '</div>' );
				}else{
					$('#daextlnl-slider-floating-content').append( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<span ' + style_text_color + ' >' + daextlnl_htmlEscape( news_title ) + '</span>' + image_after_html + '</div>' );
				}

			}else{

				//RTL layout -----------------------------------------------------------------------
				if( url.length > 0 && window.DAEXTLNL_DATA.enable_links ){
					$('#daextlnl-slider-floating-content').prepend( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<a  data-text-color="' + text_color + '" onmouseout=\'jQuery(this).css("color", jQuery(this).attr("data-text-color"))\' onmouseover=\'jQuery(this).css("color", "' + text_color_hover + '" )\' ' + style_text_color + ' target="' + window.DAEXTLNL_DATA.target_attribute + '" href="' + url + '">' + daextlnl_htmlEscape( news_title ) + '</a>' + image_after_html + '</div>' );
				}else{
					$('#daextlnl-slider-floating-content').prepend( '<div ' + style_background_color + ' class="daextlnl-slider-single-news">' + image_before_html + '<span ' + style_text_color + ' >' + daextlnl_htmlEscape( news_title ) + '</span>' + image_after_html + '</div>' );
				}

			}
		});

	}

	/*
	 * Slides the news with jQuery animate from the initial to the final position. When the animation is complete calls
	 * daextlnl_refresh_news() which restarts the process from the start.
	 */
	function daextlnl_slide_the_news(){

		'use strict';

		let outside_left = null;

		//if the news slider is already animated then return
		if( ( $('#daextlnl-slider-floating-content:animated').length ) == 1 ){ return; };

		//get browser with
		let window_width = $(window).width();

		//floating news width
		let floating_news_width =parseInt( $( "#daextlnl-slider-floating-content" ).css("width"), 10 );

		//check if is set the RTL layout option
		if( window.DAEXTLNL_DATA.rtl_layout == 0 ){

			//LTR layout -----------------------------------------------------------------------

			//position outside the screen to the left
			outside_left = floating_news_width + window_width;

			//set floating content left position outside the screen
			$( "#daextlnl-slider-floating-content" ).css("left", window_width );

			//start floating the news
			$( "#daextlnl-slider-floating-content" ).animate({
				left: "-=" + outside_left,
				easing: "linear"
			}, ( outside_left * 10 ), "linear", function() {

				//animation complete
				daextlnl_refresh_news();

			});

		}else{

			//RTL layout -----------------------------------------------------------------------

			//position outside the screen to the left
			outside_left = floating_news_width + window_width;

			//set floating content left position outside the screen
			$( "#daextlnl-slider-floating-content" ).css("left", - floating_news_width );

			//start floating the news
			$( "#daextlnl-slider-floating-content" ).animate({
				left: "+=" + outside_left,
				easing: "linear"
			}, ( outside_left * 10 ), "linear", function() {

				//animation complete
				daextlnl_refresh_news();

			});

		}

	}

	/*
	 * On the click event of the "#daextlnl-close" element closes the news ticker and sends an ajax request used to save the
	 * "closed" status in the "live_news_status" cookie
	 */
	$(document.body).on('click', '#daextlnl-close' , function(){

		'use strict';

		//Stop the animation
		$("#daextlnl-slider-floating-content").stop();

		//Delete the previous sliding news
		$('#daextlnl-slider-floating-content').empty();

		//Hide the news container
		$("#daextlnl-container").hide();

		//Show the open button
		$("#daextlnl-open").show();

        //prepare input for the ajax request
        let data = {
            "action": "set_status_cookie",
            "security": window.DAEXTLNL_DATA.nonce,
            "status": "closed"
        };

        //ajax
        $.post(window.DAEXTLNL_DATA.ajax_url, data, function(ajax_response) {

			if( ajax_response == "success" ){
				//nothing
			}

        });

		//set the status hidden field to closed
		$("#daextlnl-status").attr("value","closed");

	});

	/*
	 * On the click event of the "#daextlnl-open" element opens the news ticker and sends an ajax request used to save the
	 * "open" status in the "live_news_status" cookie
	 */
	$(document.body).on('click', '#daextlnl-open' , function(){

		'use strict';

		//Show the news container
		$("#daextlnl-container").show();

		//Show the open button
		$("#daextlnl-open").hide();

		daextlnl_refresh_news();

        //prepare input for the ajax request
        let data = {
            "action": "set_status_cookie",
            "security": window.DAEXTLNL_DATA.nonce,
            "status": "open"
        };

        //ajax
        $.post(window.DAEXTLNL_DATA.ajax_url, data, function(ajax_response) {

			if( ajax_response == "success" ){
				//nothing
			}

        });

		//set the status hidden field to open
		$("#daextlnl-status").attr("value","open");

	});

	/*
	 * Converts certain characters to their HTML entities
	 */
	function daextlnl_htmlEscape(str) {

		'use strict';

	    return String(str)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');

	}

	/*
	 * Appends the ticker HTML just before the ending body element
	 */
	function daextlnl_append_html(){

		'use strict';

		let html_output = '<div id="daextlnl-container">' +

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

		$('body').append(html_output);

	}

	/*
	 * Uses a "Date" object to retrieve the user time and adds the clock offset of this news ticker
	 */
	function daextlnl_set_clock_based_on_user_time(){

		'use strict';

		//Get the current unix timestamp and add the offset
		let timestamp = moment().unix() + window.DAEXTLNL_DATA.clock_offset;

		//Convert the unix timestamp to the provided format
		let time = moment.unix(timestamp).format(window.DAEXTLNL_DATA.clock_format);

		//Update the DOM
    $("#daextlnl-clock").text(time);

	}

	/*
	 * Given an hexadecimal rgb color an array with the 3 components converted in decimal is returned
	 *
	 * @param string The hexadecimal rgb color
	 * @return array An array with the 3 component of the color converted in decimal
	 */
	function rgb_hex_to_dec(hex){

		'use strict';
		let r = null;
		let g = null;
		let b = null;
		let color_a = new Array();

		//remove the # character
		hex = hex.replace('#', '');

		//find the component of the color
		if ( hex.length == 3 ) {
			r = parseInt(hex.substring(0, 1), 16);
			g = parseInt(hex.substring(1, 2), 16);
			b = parseInt(hex.substring(2, 3), 16);
		} else {
			r = parseInt(hex.substring(0, 2), 16);
			g = parseInt(hex.substring(2, 4), 16);
			b = parseInt(hex.substring(4, 6), 16);
		}

		//generate the array with the component of the color

		color_a['r'] = r;
		color_a['g'] = g;
		color_a['b'] = b;

		return color_a;

	}

});