/**
 * This file is used to handle initialize Select2 in the Featured News menu.
 *
 * @package live-news-lite
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		$( '#ticker_id' ).select2();

	}

}(window.jQuery));