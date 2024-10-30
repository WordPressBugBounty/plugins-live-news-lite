<?php
/**
 * This file includes features used in the public side of WordPress.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to work with the public side of WordPress.
 */
class Daextlnl_Public {

	/**
	 * The singleton instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextlnl_Shared|null
	 */
	private $shared = null;

	/**
	 * Boolean used to determine if the ticker should be applied or not.
	 *
	 * @var bool
	 */
	private $apply_ticker = true;

	/**
	 * Create an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Daextlnl_Shared::get_instance();

		// Write in the front-end head.
		add_action( 'wp_head', array( $this, 'generate_ticker' ) );

		// Load public css and js.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/*
		 * This hook is triggered right after a category is deleted with the Posts -> Category menu. It's placed here
		 * because doesn't work if used in the Daextlnl_Admin class.
		 */
		add_action( 'delete_category', array( $this, 'ticker_delete_category' ) );
	}

	/**
	 * Enqueue styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		if ( 0 === intval( get_option( $this->shared->get( 'slug' ) . '_assets_mode' ), 10 ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'public/assets/css/dev/main.css', array(), $this->shared->get( 'ver' ) );
		} else {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'public/assets/css/main.css', array(), $this->shared->get( 'ver' ) );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		// Store the JavaScript parameters in the window.DAEXTDAEXTLNL_PARAMETERS object.
		$initialization_script  = 'window.DAEXTLNL_PARAMETERS = {';
		$initialization_script .= 'ajaxUrl: "' . admin_url( 'admin-ajax.php' ) . '",';
		$initialization_script .= 'nonce: "' . wp_create_nonce( 'live-news-lite' ) . '"';
		$initialization_script .= '};';

		if ( 0 === intval( get_option( $this->shared->get( 'slug' ) . '_assets_mode' ), 10 ) ) {

			if ( intval( get_option( 'daextlnl_load_momentjs' ), 10 ) === 1 ) {
				wp_enqueue_script(
					$this->shared->get( 'slug' ) . '-momentjs',
					$this->shared->get( 'url' ) . 'public/assets/js/momentjs/moment.js',
					array( 'jquery' ),
					$this->shared->get( 'ver' ),
					array(
						'strategy' => 'defer',
					)
				);
				$general_js_dependencies = array( 'jquery', $this->shared->get( 'slug' ) . '-momentjs' );
			} else {
				$general_js_dependencies = array( 'jquery' );
			}

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . 'general',
				$this->shared->get( 'url' ) . 'public/assets/js/dev/main.js',
				$general_js_dependencies,
				$this->shared->get( 'ver' ),
				array(
					'strategy' => 'defer',
				)
			);

		} else {

			if ( intval( get_option( 'daextlnl_load_momentjs' ), 10 ) === 1 ) {
				wp_enqueue_script(
					$this->shared->get( 'slug' ) . '-momentjs',
					$this->shared->get( 'url' ) . 'public/assets/js/momentjs/moment.min.js',
					array( 'jquery' ),
					$this->shared->get( 'ver' ),
					array(
						'strategy' => 'defer',
					)
				);
				$general_js_dependencies = array( 'jquery', $this->shared->get( 'slug' ) . '-momentjs' );
			} else {
				$general_js_dependencies = array( 'jquery' );
			}

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . 'general',
				$this->shared->get( 'url' ) . 'public/assets/js/main.js',
				$general_js_dependencies,
				$this->shared->get( 'ver' ),
				array(
					'strategy' => 'defer',
				)
			);

		}

		wp_add_inline_script( $this->shared->get( 'slug' ) . 'general', $initialization_script, 'before' );
	}

	/**
	 * This method generates in the <head> section of the page:
	 *
	 *  - All the javascript variables used by main.js to generate the news ticker
	 *  - The CSS of the ticker
	 *
	 * @return void
	 */
	public function generate_ticker() {

		$current_url = $this->shared->get_current_url();
		$ticker_obj  = $this->shared->get_ticker_with_target_url( $current_url );

		/*
		 * If there isn't a ticker associated with this url use the ticker associated with the website if exists.
		 */
		if ( false === $ticker_obj ) {

			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$ticker_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextlnl_tickers WHERE target = %d", 1 )
			);

			// if there is no ticker set the class property $apply_ticker to true and return.
			if ( null === $ticker_obj ) {
				$this->apply_ticker = false;
			}
		}

		/*
		 * Do not display the ticker if the "Enable Ticker" flag is set to no
		 */
		if ( null === $ticker_obj || intval( $ticker_obj->enable_ticker, 10 ) === 0 ) {
			$this->apply_ticker = false;
		}

		/**
		 * Create an instance of the Mobile Detect class only if the "Enable with Mobile Devices" option is set to "No"
		 * or if the "Hide the featured news" option is set to "Only with Mobile Devices". In these two situations the
		 * instance should be created to check if the current device is a mobile device.
		 */
		if ( isset( $ticker_obj->enable_with_mobile_devices ) && isset( $ticker_obj->hide_featured_news ) &&
			( 0 === intval( $ticker_obj->enable_with_mobile_devices, 10 ) ||
			3 === intval( $ticker_obj->hide_featured_news, 10 ) ) ) {

			require_once $this->shared->get( 'dir' ) . '/vendor/autoload.php';
			$detect = new Detection\MobileDetect();

		}

		/*
		 * Do not display the ticker if the "Mobile Detect" class detects that the current devices is a mobile device
		 * and at the same time the "Enable with Mobile Devices" option is set to "No" (0). Do not even load the
		 * Mobile Detect class and perform this check if the ticker should not be applied.
		 */
		if ( $this->apply_ticker && 0 === intval( $ticker_obj->enable_with_mobile_devices, 10 ) ) {
			if ( $detect->isMobile() ) {
				$this->apply_ticker = false;
			}
		}

		if ( $this->apply_ticker ) {

			// Apply the ticker.
			echo '<script type="text/javascript">';

				/*
				 * Flag used to verify if the ticker should be appended by javascript (in main.js) before the ending
				 * body tag
				 */
				echo 'var daextlnl_apply_ticker = true;';

				// Set the target attribute of the links.
			if ( 1 === intval( $ticker_obj->open_links_new_tab, 10 ) ) {
				echo "var daextlnl_target_attribute = '_blank';";
			} else {
				echo "var daextlnl_target_attribute = '_self';";
			}

				// Set the rtl layout option in a javscript variable.
				echo 'var daextlnl_rtl_layout = ' . intval( $ticker_obj->enable_rtl_layout, 10 ) . ';';

				// Set the number of cached cycles.
				echo 'var daextlnl_cached_cycles = ' . wp_json_encode( abs( intval( $ticker_obj->cached_cycles, 10 ) ) ) . ';';

				// Set the ticker_id.
				echo 'var daextlnl_ticker_id = ' . intval( $ticker_obj->id, 10 ) . ';';

				// Enable_links.
			if ( 1 === intval( $ticker_obj->enable_links, 10 ) ) {
				$enable_links_javascript_value = 'true';
			} else {
				$enable_links_javascript_value = 'false';
			}
				echo 'var daextlnl_enable_links = ' . wp_json_encode( $enable_links_javascript_value ) . ';';

				// Clock offset.
				echo 'var daextlnl_clock_offset = ' . intval( $ticker_obj->clock_offset, 10 ) . ';';

				// Clock format.
				echo 'var daextlnl_clock_format = ' . wp_json_encode( stripslashes( $ticker_obj->clock_format ) ) . ';';

				// Clock source.
				echo 'var daextlnl_clock_source = ' . intval( $ticker_obj->clock_source, 10 ) . ';';

				// Clock autoupdate.
				echo 'var daextlnl_clock_autoupdate = ' . intval( $ticker_obj->clock_autoupdate, 10 ) . ';';

				// Clock autoupdate time.
				echo 'var daextlnl_clock_autoupdate_time = ' . intval( $ticker_obj->clock_autoupdate_time, 10 ) . ';';

				/*
				 * If the transient exists generate the daextlnl_ticker_transient JavaScript variable. Which is a string
				 * that includes the ticker XML.
				 */
				$ticker_transient = get_transient( 'daextlnl_ticker_' . $ticker_obj->id );
			if ( false !== $ticker_transient ) {

				/**
				 * Save the XML string in a JavaScript variable.
				 *
				 *  Note that json_encode() is only used to avoid errors and escape the JavaScript variable, not
				 *  to perform a conversion to json. The resulting daextlnl_ticker_transient JavaScript variable is
				 *  an XML string. (that will be converted to an actual XML Document by jQuery.parseXML() in
				 *  main.js)
				 */
				echo 'var daextlnl_ticker_transient = ' . wp_json_encode( $ticker_transient ) . ';';

			}

			echo '</script>' . "\n";

			echo '<style type="text/css">';

			if ( 2 === intval( $ticker_obj->hide_featured_news, 10 ) || ( 3 === intval( $ticker_obj->hide_featured_news, 10 ) && $detect->isMobile() ) ) {

				/**
				 * If in "Hide the featured news" is selected "Yes" or if is selected "Only with Mobile Devices" and
				 * the current device is a mobile device hide the featured news area remove the button used to open
				 * and close the featured news area.
				 */
				echo '#daextlnl-container{ min-height: 40px; }';
				echo '#daextlnl-featured-container{ display: none; }';
				echo '#daextlnl-open{ display: none; }';

			}

				/*
				* If in "Hide the featured news" is selected "No" or if is selected "Only with Mobile Devices" and
				* the current device is not a mobile device use the "live_news_status" cookie to determine the
				* status of the news ticker (open or closed).
				*/
			if ( isset( $_COOKIE['live_news_status'] ) ) {

				// If the live_news_status cookie exists set the gallery status based on this cookie.
				if ( 'open' === $_COOKIE['live_news_status'] ) {
					$current_status = 'open';
				} else {
					$current_status = 'closed';
				}
			} else {

				/**
				 * If the "live_news_status" cookie doesn't exist set the gallery status based on the
				 *  "Open news as default" option.
				 */
				if ( 1 === intval( $ticker_obj->open_news_as_default, 10 ) ) {
					$current_status = 'open';
				} else {
					$current_status = 'closed';
				}
			}

				/*
				 * Use the status to set the proper CSS
				 */
			if ( 'open' === $current_status ) {

				echo '#daextlnl-container{ display: block; }';
				echo '#daextlnl-open{ display: none; }';

			} else {

				echo '#daextlnl-container{ display: none; }';
				echo '#daextlnl-open{ display: block; }';

			}

				// Set the font family based on the plugin option.
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Necessary to output the font family.
				echo '#daextlnl-featured-title, #daextlnl-featured-title a,#daextlnl-featured-excerpt, #daextlnl-featured-excerpt a, #daextlnl-clock, #daextlnl-close, .daextlnl-slider-single-news, .daextlnl-slider-single-news a{ font-family: ' . htmlentities( stripslashes( $ticker_obj->font_family ), ENT_COMPAT ) . ' !important; }';

				// Set the sliding news background color.
				$color_a = $this->shared->rgb_hex_to_dec( str_replace( '#', '', $ticker_obj->featured_news_background_color ) );
				echo '#daextlnl-featured-container{ background: rgba(' . esc_attr( $color_a['r'] ) . ',' . esc_attr( $color_a['g'] ) . ',' . esc_attr( $color_a['b'] ) . ', ' . floatval( $ticker_obj->featured_news_background_color_opacity ) . '); }';

				// Set the sliding news background color.
				$color_a = $this->shared->rgb_hex_to_dec( str_replace( '#', '', $ticker_obj->sliding_news_background_color ) );
				echo '#daextlnl-slider{ background: rgba(' . esc_attr( $color_a['r'] ) . ',' . esc_attr( $color_a['g'] ) . ',' . esc_attr( $color_a['b'] ) . ', ' . floatval( $ticker_obj->sliding_news_background_color_opacity ) . '); }';

				// Set the font size of the textual elements.
				echo '#daextlnl-featured-title{ font-size: ' . intval( $ticker_obj->featured_title_font_size, 10 ) . 'px; }';
				echo '#daextlnl-featured-excerpt{ font-size: ' . intval( $ticker_obj->featured_excerpt_font_size, 10 ) . 'px; }';
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{ font-size: ' . intval( $ticker_obj->sliding_news_font_size, 10 ) . 'px; }';
				echo '#daextlnl-clock{ font-size: ' . intval( $ticker_obj->clock_font_size, 10 ) . 'px; }';

				// Hide the clock if this options is set in the plugin option.
			if ( '1' === $ticker_obj->hide_clock ) {
				echo '#daextlnl-clock{ display: none; }';
			}

				// Set news css for the rtl layout.
			if ( 1 === intval( $ticker_obj->enable_rtl_layout, 10 ) ) {
				echo '#daextlnl-featured-title-container, #daextlnl-featured-title, #daextlnl-featured-title a{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
				echo '#daextlnl-featured-excerpt-container, #daextlnl-featured-excerpt a{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
				echo '#daextlnl-slider, #daextlnl-slider-floating-content, .daextlnl-slider-single-news{ text-align: right !important; direction: rtl !important; unicode-bidi: embed !important; }';
			}

				// Set the open button image url.
				echo "#daextlnl-open{background: url( '" . esc_attr( stripslashes( $ticker_obj->open_button_image ) ) . "');}";

				// Set the close button image url.
				echo "#daextlnl-close{background: url( '" . esc_attr( stripslashes( $ticker_obj->close_button_image ) ) . "');}";

				// Set the clock background image url.
				echo "#daextlnl-clock{background: url( '" . esc_attr( stripslashes( $ticker_obj->clock_background_image ) ) . "');}";

				// set the featured news title color.
				echo '#daextlnl-featured-title a{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_title_color ) ) . ';}';

				// set the featured news title color hover.
				echo '#daextlnl-featured-title a:hover{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_title_color_hover ) ) . ';}';

				// set the featured news excerpt color.
				echo '#daextlnl-featured-excerpt{color: ' . esc_attr( stripslashes( $ticker_obj->featured_news_excerpt_color ) ) . ';}';

				// Set the sliding news color.
				echo '.daextlnl-slider-single-news, .daextlnl-slider-single-news a{color: ' . esc_attr( stripslashes( $ticker_obj->sliding_news_color ) ) . ';}';

				// Set the sliding news color hover.
				echo '.daextlnl-slider-single-news a:hover{color: ' . esc_attr( stripslashes( $ticker_obj->sliding_news_color_hover ) ) . ';}';

				// Set the clock text color.
				echo '#daextlnl-clock{color: ' . esc_attr( stripslashes( $ticker_obj->clock_text_color ) ) . ';}';

				// set the sliding news margin.
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{margin-right: ' . intval( $ticker_obj->sliding_news_margin, 10 ) . 'px !important; }';

				// Set the sliding news padding.
				echo '#daextlnl-slider-floating-content .daextlnl-slider-single-news{padding: 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px !important; }';
				echo '#daextlnl-container .daextlnl-image-before{margin: 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px 0 0 !important; }';
				echo '#daextlnl-container .daextlnl-image-after{margin: 0 0 0 ' . intval( $ticker_obj->sliding_news_padding, 10 ) . 'px !important; }';

			echo '</style>';

			// Embed google fonts if selected.
			if ( mb_strlen( trim( $ticker_obj->google_font ) ) > 0 ) {
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Necessary because the ticker data are not available in the "wp_enqueue_style" hook.
				echo "<link href='" . esc_url( stripslashes( $ticker_obj->google_font ) ) . "' rel='stylesheet' type='text/css'>";
			}
		}
	}

	/**
	 * The purpose of this method is to prevent to have tickers associated with categories that no longer exist.
	 *  This method is called by the 'delete_category' action hook, which is triggered when a category is deleted from
	 *  the Posts -> Category menu. If the deleted category is included in a ticker the 'category' value of the ticker
	 *  will be set to 0, which is the 'All' value used to show all the categories of a ticker.
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return void
	 */
	public function ticker_delete_category( $term_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query(
			$wpdb->prepare( "UPDATE {$wpdb->prefix}daextlnl_tickers SET category = 0 WHERE category = %d", $term_id )
		);
	}
}
