<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package live-news-lite
 */

/*
 * This class should be used to work with the public side of WordPress.
 */
class Daextlnl_Public {

	// general class properties.
	protected static $instance = null;
	private $shared            = null;
	private $apply_ticker      = true;

	/**
	 * create an instance of this class
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {

		// assign an instance of the shared class.
		$this->shared = Daextlnl_Shared::get_instance();

		// Write in the front-end head.
		add_action( 'wp_head', array( $this, 'generate_ticker' ) );

		// Load public css and js.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'public/assets/css/general.css', array(), $this->shared->get( 'ver' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->shared->get( 'slug' ) . '-momentjs', $this->shared->get( 'url' ) . 'public/assets/js/inc/momentjs/moment.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-mobile-detect-js', $this->shared->get( 'url' ) . 'public/assets/js/inc/mobile-detect-js/mobile-detect.min.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'public/assets/js/general.js', array( 'jquery', $this->shared->get( 'slug' ) . '-momentjs', $this->shared->get( 'slug' ) . '-mobile-detect-js' ), $this->shared->get( 'ver' ), true );
	}

	/**
	 * This method generates in the <head> section of the page:
	 *
	 * - The window.DAEXTLNL_DATA JavaScript object used by general.js to generate the news ticker
	 * - The CSS of the ticker
	 */
	function generate_ticker() {

		$current_url = $this->shared->get_current_url();
		$ticker_obj  = $this->shared->get_ticker_with_target_url( $current_url );

		/*
		 * If there isn't a ticker associated with this url use the ticker associated with the website if exists.
		 */
		if ( $ticker_obj === false ) {

			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
			$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE target = %d", 1 );
			$ticker_obj = $wpdb->get_row( $safe_sql );

			// if there is no ticker set the class property $apply_ticker to true and return.
			if ( $ticker_obj === null ) {
				$this->apply_ticker = false;
			}
		}

		/**
		 * Do not display the ticker if the "Enable Ticker" flag is set to no.
		 */
		if ( $ticker_obj === null or intval( $ticker_obj->enable_ticker, 10 ) == 0 ) {
			$this->apply_ticker = false;
		}

		if ( $this->apply_ticker ) {

			$data = array();

			/**
			 * Flag used to verify if the ticker should be appended by javascript (in general.js) before the ending
			 * body tag.
			 */
			$data['apply_ticker'] = 'true';

			// nonce used for the ajax requests.
			$data['nonce'] = "'" . esc_js( wp_create_nonce( 'live-news' ) ) . "'";

			// set the ajax url variable in javascript.
			$data['ajax_url'] = "'" . esc_js( admin_url( 'admin-ajax.php' ) ) . "'";

			// set the target attribute of the links.
			if ( intval( $ticker_obj->open_links_new_tab, 10 ) == 1 ) {
				$data['target_attribute'] = "'_blank'";
			} else {
				$data['target_attribute'] = "'_self'";
			}

			// set the number of cached cycles.
			$data['rtl_layout'] = intval( $ticker_obj->enable_rtl_layout, 10 );

			// set the "Enable with Mobile Devices" option value.
			if ( intval( $ticker_obj->enable_with_mobile_devices, 10 ) === 1 ) {
				$data['enable_with_mobile_devices'] = 'true';
			} else {
				$data['enable_with_mobile_devices'] = 'false';
			}

			// set the "Hide Featured News" option value.
			$data['hide_featured_news'] = intval( $ticker_obj->hide_featured_news, 10 );

			// set the ticker_id.
			$data['ticker_id'] = intval( $ticker_obj->id, 10 );

			// enable_links.
			if ( intval( $ticker_obj->enable_links, 10 ) === 1 ) {
				$data['enable_links'] = 'true';
			} else {
				$data['enable_links'] = 'false';
			}

			// clock offset.
			$data['clock_offset'] = intval( $ticker_obj->clock_offset, 10 );

			// clock format.
			$data['clock_format'] = "'" . esc_js( stripslashes( $ticker_obj->clock_format ) ) . "'";

			// clock source.
			$data['clock_source'] = intval( $ticker_obj->clock_source, 10 );

			// clock autoupdate.
			$data['clock_autoupdate'] = intval( $ticker_obj->clock_autoupdate, 10 );

			// clock autoupdate time.
			$data['clock_autoupdate_time'] = intval( $ticker_obj->clock_autoupdate_time, 10 );

			// cached cycles.
			$data['cached_cycles'] = intval( $ticker_obj->cached_cycles, 10 );

			/*
			 * If the transient exists generate the daextlnl_ticker_transient JavaScript variable. Which is a string
			 * that includes the ticker XML.
			 */
			$ticker_transient = get_transient( 'daextlnl_ticker_' . $ticker_obj->id );
			if ( $ticker_transient !== false ) {

				/**
				 * Save the XML string in a JavaScript variable.
				 *
				 * Note that json_encode() is only used to avoid errors and escape the JavaScript variable, not
				 * to perform a conversion to json. The resulting daextlnl_ticker_transient JavaScript variable is
				 * an XML string. (that will be converted to an actual XML Document by jQuery.parseXML() in
				 * general.js)
				 */
				$data['ticker_transient'] = json_encode( $ticker_transient );

			} else {

				$data['ticker_transient'] = 'null';

			}

			// Generate the JavaScript object that includes the data.
			echo '<script>';
			echo 'window.DAEXTLNL_DATA = {';
			echo 'apply_ticker:' . $data['apply_ticker'] . ',';
			echo 'nonce:' . $data['nonce'] . ',';
			echo 'ajax_url:' . $data['ajax_url'] . ',';
			echo 'target_attribute:' . $data['target_attribute'] . ',';
			echo 'rtl_layout:' . $data['rtl_layout'] . ',';
			echo 'enable_with_mobile_devices:' . $data['enable_with_mobile_devices'] . ',';
			echo 'hide_featured_news:' . $data['hide_featured_news'] . ',';
			echo 'ticker_id:' . $data['ticker_id'] . ',';
			echo 'enable_links:' . $data['enable_links'] . ',';
			echo 'clock_offset:' . $data['clock_offset'] . ',';
			echo 'clock_format:' . $data['clock_format'] . ',';
			echo 'clock_source:' . $data['clock_source'] . ',';
			echo 'clock_autoupdate:' . $data['clock_autoupdate'] . ',';
			echo 'clock_autoupdate_time:' . $data['clock_autoupdate_time'] . ',';
			echo 'cached_cycles:' . $data['cached_cycles'] . ',';
			echo 'ticker_transient:' . $data['ticker_transient'] . ',';
			echo '};';
			echo '</script>';

			// Generate custom CSS based on the plugin options.
			echo '<style type="text/css">';

				/**
				* If in "Hide the featured news" is selected "No" or if is selected "Only with Mobile Devices" and
				* the current device is not a mobile device use the "live_news_status" cookie to determine the
				* status of the news ticker (open or closed).
				*/
				$live_news_status = isset( $_COOKIE['live_news_status'] ) ? sanitize_key( $_COOKIE['live_news_status'] ) : null;
			if ( $live_news_status !== null ) {

				// If the live_news_status cookie exists set the news ticker status based on this cookie
				if ( $live_news_status === 'open' ) {
					$current_status = 'open';
				} else {
					$current_status = 'closed';
				}
			} else {

				/**
				 * If the "live_news_status" cookie doesn't exist set the gallery status based on the
				 * "Open news as default" option.
				 */
				if ( intval( $ticker_obj->open_news_as_default, 10 ) == 1 ) {
					$current_status = 'open';
				} else {
					$current_status = 'closed';
				}
			}

				/**
				 * Use the status to set the proper CSS.
				 */
			if ( $current_status == 'open' ) {

				echo '#daextlnl-container{ display: block; }';
				echo '#daextlnl-open{ display: none; }';

			} else {

				echo '#daextlnl-container{ display: none; }';
				echo '#daextlnl-open{ display: block; }';

			}

				// set the font family based on the plugin option.
				echo '#daextlnl-featured-title, #daextlnl-featured-title a,#daextlnl-featured-excerpt, #daextlnl-featured-excerpt a, #daextlnl-clock, #daextlnl-close, .daextlnl-slider-single-news, .daextlnl-slider-single-news a{ font-family: ' . htmlentities( stripslashes( $ticker_obj->font_family ), ENT_COMPAT ) . ' !important; }';

				// set the sliding news background color.
				$color_a = $this->shared->rgb_hex_to_dec( str_replace( '#', '', $ticker_obj->featured_news_background_color ) );
				echo '#daextlnl-featured-container{ background: ' . 'rgba(' . $color_a['r'] . ',' . $color_a['g'] . ',' . $color_a['b'] . ', ' . floatval( $ticker_obj->featured_news_background_color_opacity ) . ')' . '; }';

				// set the sliding news background color.
				$color_a = $this->shared->rgb_hex_to_dec( str_replace( '#', '', $ticker_obj->sliding_news_background_color ) );
				echo '#daextlnl-slider{ background: ' . 'rgba(' . $color_a['r'] . ',' . $color_a['g'] . ',' . $color_a['b'] . ', ' . floatval( $ticker_obj->sliding_news_background_color_opacity ) . ')' . '; }';

				// set the font size of the textual elements.
				echo '#daextlnl-featured-title{ font-size: ' . intval( $ticker_obj->featured_title_font_size, 10 ) . 'px; }';
				echo '#daextlnl-featured-excerpt{ font-size: ' . intval( $ticker_obj->featured_excerpt_font_size, 10 ) . 'px; }';
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{ font-size: ' . intval( $ticker_obj->sliding_news_font_size, 10 ) . 'px; }';
				echo '#daextlnl-clock{ font-size: ' . intval( $ticker_obj->clock_font_size, 10 ) . 'px; }';

				// hide the clock if this options is set in the plugin option
			if ( $ticker_obj->hide_clock === '1' ) {
				echo '#daextlnl-clock{ display: none; }';
			}

				// set news css for the rtl layout
			if ( intval( $ticker_obj->enable_rtl_layout, 10 ) == 1 ) {
				echo '#daextlnl-featured-title-container, #daextlnl-featured-title, #daextlnl-featured-title a{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
				echo '#daextlnl-featured-excerpt-container, #daextlnl-featured-excerpt a{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
				echo '#daextlnl-slider, #daextlnl-slider-floating-content, .daextlnl-slider-single-news{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
			}

				// set the open button image url.
				echo "#daextlnl-open{background: url( '" . esc_attr( stripslashes( $ticker_obj->open_button_image ) ) . "');}";

				// set the close button image url.
				echo "#daextlnl-close{background: url( '" . esc_attr( stripslashes( $ticker_obj->close_button_image ) ) . "');}";

				// set the clock background image url.
				echo "#daextlnl-clock{background: url( '" . esc_attr( stripslashes( $ticker_obj->clock_background_image ) ) . "');}";

				// set the featured news title color.
				echo '#daextlnl-featured-title a{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_title_color ) ) . ';}';

				// set the featured news title color hover.
				echo '#daextlnl-featured-title a:hover{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_title_color_hover ) ) . ';}';

				// set the featured news excerpt color.
				echo '#daextlnl-featured-excerpt{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_excerpt_color ) ) . ';}';

				// set the sliding news color.
				echo '.daextlnl-slider-single-news, .daextlnl-slider-single-news a{color: ' . esc_attr( stripslashes( $ticker_obj->sliding_news_color ) ) . ';}';

				// set the sliding news color hover.
				echo '.daextlnl-slider-single-news a:hover{color: ' . esc_attr( stripslashes( $ticker_obj->sliding_news_color_hover ) ) . ';}';

				// set the clock text color.
				echo '#daextlnl-clock{color: ' . esc_attr( stripslashes( $ticker_obj->clock_text_color ) ) . ';}';

				// set the sliding news margin.
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{margin-right: ' . intval( $ticker_obj->sliding_news_margin, 10 ) . 'px !important; }';

				// set the sliding news padding.
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{padding: 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px !important; }';
				echo '#daextlnl-container .daextlnl-image-before{margin: 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px 0 0 !important; }';
				echo '#daextlnl-container .daextlnl-image-after{margin: 0 0 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px !important; }';

			echo '</style>';

			// embed google fonts if selected.
			if ( mb_strlen( trim( $ticker_obj->google_font ) ) > 0 ) {
				echo '<link rel="preconnect" href="https://fonts.gstatic.com">';
				echo '<link href="' . esc_url( stripslashes( $ticker_obj->google_font ) ) . '" rel="stylesheet">';
			}
		}
	}
}
