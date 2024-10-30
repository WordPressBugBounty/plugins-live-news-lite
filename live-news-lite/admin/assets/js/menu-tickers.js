/**
 * This file is used to handle initialize Select2 and initialize the color picker in the Tickers menu.
 *
 * @package live-news-lite
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

			initWpColorPickerFields();

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		$( '#target' ).select2();
		$( '#source' ).select2();
		$( '#category' ).select2();
		$( '#open-news-as-default' ).select2();
		$( '#hide_featured_news' ).select2();
		$( '#clock_source' ).select2();
		$( '#hide_featured_news' ).select2();

	}

	/**
	 * Initialize the wp color picker fields.
	 */
	function initWpColorPickerFields(){

		'use strict';

		const config = {
			'palettes': []
		};

		$( '#featured_news_title_color' ).wpColorPicker( config );
		$( '#featured_news_title_color_hover' ).wpColorPicker( config );
		$( '#featured_news_excerpt_color' ).wpColorPicker( config );
		$( '#sliding_news_color' ).wpColorPicker( config );
		$( '#sliding_news_color_hover' ).wpColorPicker( config );
		$( '#clock_text_color' ).wpColorPicker( config );
		$( '#featured_news_background_color' ).wpColorPicker( config );
		$( '#sliding_news_background_color' ).wpColorPicker( config );

	}

}(window.jQuery));