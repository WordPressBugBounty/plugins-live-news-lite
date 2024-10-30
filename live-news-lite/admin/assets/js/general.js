/**
 * This file is used loaded in all WordPress back-end pages, and it's used to add the target="_blank" attribute to the
 * "Help & Support" link available as the last item in the Live News admin menu.
 *
 * @package live-news-lite
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			const lastMenuItemLink = $( '#toplevel_page_daextlnl-tickers.toplevel_page_daextlnl-tickers .wp-submenu li:last-of-type a' );
			lastMenuItemLink.attr( 'target', '_blank' );

		}
	);

}(window.jQuery));
