<?php
/**
 * This file contains the class Daextlnl_Ajax, used to include ajax actions.
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextlnl_Ajax {

	protected static $instance = null;
	private $shared            = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {

		// assign an instance of the plugin info.
		$this->shared = Daextlnl_Shared::get_instance();

		// ajax requests --------------------------------------------------------.
		add_action( 'wp_ajax_set_status_cookie', array( $this, 'set_status_cookie' ) );
		add_action( 'wp_ajax_nopriv_set_status_cookie', array( $this, 'set_status_cookie' ) );

		add_action( 'wp_ajax_get_ticker_data', array( $this, 'get_ticker_data' ) );
		add_action( 'wp_ajax_nopriv_get_ticker_data', array( $this, 'get_ticker_data' ) );

		add_action( 'wp_ajax_update_default_colors', array( $this, 'update_default_colors' ) );
	}

	/**
	 * Set the cookie used to determine the status (open or closed) of the news ticker. This request is triggered when
	 * the used clicks on the open or close button.
	 *
	 * @return void
	 */
	public function set_status_cookie() {

		// check the referer.
		check_ajax_referer( 'live-news', 'security' );

		// Save the current status ( open/closed ) in a cookie.
		$status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
		if ( $status === 'open' ) {

			setcookie( 'live_news_status', 'open', 0, '/' );

		} else {

			setcookie( 'live_news_status', 'closed', 0, '/' );

		}

		echo 'success';

		die();
	}

	/**
	 * Generate an XML response with included all the data of the ticker. The data are generated based on the options
	 * defined for the specific ticker.
	 *
	 * @return void
	 */
	public function get_ticker_data() {

		// check the referer.
		check_ajax_referer( 'live-news', 'security' );

		// get the ticker id.
		$ticker_id = intval( $_POST['ticker_id'], 10 );

		// get the ticker information.
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $ticker_id );
		$ticker_obj = $wpdb->get_row( $safe_sql );

		// if there isn't a ticker associated with this ticker_id die().
		if ( $ticker_obj === null ) {
			die( 'Invalid Ticker ID.' );}

		// START OUTPUT.

		// generate the xml header.
		header( 'Content-type: text/xml' );
		header( 'Pragma: public' );
		header( 'Cache-control: private' );
		header( 'Expires: -1' );

		// Get the transient with included the data of the ticker if available.
		$outstr = get_transient( 'daextlnl_ticker_' . $ticker_obj->id );

		// Generate the data of the ticker only if the transient with the data is not available.
		if ( $outstr === false ) {

			$outstr = '<?xml version="1.0" encoding="UTF-8" ?>';

			$outstr .= '<ticker>';

			// generate featured news XML -----------------------------------------------------------------------------.
			$outstr .= '<featurednews>';

			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
			$results    = $wpdb->get_results( "SELECT id, news_title, news_excerpt, url FROM $table_name WHERE ticker_id = $ticker_obj->id ORDER BY id DESC LIMIT 1", ARRAY_A );

			if ( count( $results ) > 0 ) {
				foreach ( $results as $result ) {

					$outstr .= '<news>';
					$outstr .= '<newstitle>' . esc_attr( $this->shared->strlen_no_truncate( stripslashes( $result['news_title'] ), $ticker_obj->featured_title_maximum_length ) ) . '</newstitle>';
					$outstr .= '<newsexcerpt>' . esc_attr( $this->shared->strlen_no_truncate( stripslashes( $result['news_excerpt'] ), $ticker_obj->featured_excerpt_maximum_length ) ) . '</newsexcerpt>';
					$outstr .= '<url>' . esc_attr( stripslashes( $result['url'] ) ) . '</url>';
					$outstr .= '</news>';

				}
			}

			$outstr .= '</featurednews>';

			// generate sliding news XML ------------------------------------------------------------------------------.
			$outstr .= '<slidingnews>';

			// get number of sliding news from the option.
			$number_of_sliding_news = intval( $ticker_obj->number_of_sliding_news, 10 );

			/*
			 * Set the offset based on the "Hide Featured News" option. If the featured news is hidden then offset is 0,
			 * if the featured news is shown the offset is 1.
			 */
			if ( $ticker_obj->hide_featured_news == 2 ) {
				$offset = 0;
			} else {
				$offset = 1;
			}

			global $wpdb;
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
			$results    = $wpdb->get_results( "SELECT id, news_title, url, text_color, text_color_hover, background_color, background_color_opacity, image_before, image_after FROM $table_name WHERE ticker_id = $ticker_obj->id ORDER BY id DESC LIMIT $number_of_sliding_news", ARRAY_A );

			if ( count( $results ) > 0 ) {
				foreach ( $results as $result ) {

					$outstr .= '<news>';
					$outstr .= '<newstitle>' . esc_attr( $this->shared->strlen_no_truncate( stripslashes( $result['news_title'] ), $ticker_obj->sliding_news_maximum_length ) ) . '</newstitle>';
					$outstr .= '<url>' . esc_attr( stripslashes( $result['url'] ) ) . '</url>';
					$outstr .= '<text_color>' . esc_attr( stripslashes( $result['text_color'] ) ) . '</text_color>';
					$outstr .= '<text_color_hover>' . esc_attr( stripslashes( $result['text_color_hover'] ) ) . '</text_color_hover>';
					$outstr .= '<background_color>' . esc_attr( stripslashes( $result['background_color'] ) ) . '</background_color>';
					$outstr .= '<background_color_opacity>' . esc_attr( $result['background_color_opacity'] ) . '</background_color_opacity>';
					$outstr .= '<image_before>' . esc_attr( stripslashes( $result['image_before'] ) ) . '</image_before>';
					$outstr .= '<image_after>' . esc_attr( stripslashes( $result['image_after'] ) ) . '</image_after>';
					$outstr .= '</news>';

				}
			}

			$outstr .= '</slidingnews>';

			// generate current time XML ------------------------------------------------------------------------------.
			$current_time = current_time( 'timestamp' ) + $ticker_obj->clock_offset;

			$outstr .= '<time>' . esc_attr( stripslashes( $current_time ) ) . '</time>';

			$outstr .= '</ticker>';

			if ( $ticker_obj->transient_expiration > 0 ) {
				set_transient( 'daextlnl_ticker_' . $ticker_obj->id, $outstr, $ticker_obj->transient_expiration );
			}
		}

		echo $outstr;

		die();
	}

	/**
	 * Retrieve the "Sliding News Color", the "Sliding News Color Hover, and the "Sliding News Background Color" from
	 * the tickers to initialize the values of the three fields in the "Sliding News" menu.
	 *
	 * @return void
	 */
	public function update_default_colors() {

		// check the referer.
		check_ajax_referer( 'live-news', 'security' );

		// check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_sliding_menu_capability' ) ) ) {
			die();}

		// get the missing word id.
		$ticker_id = intval( $_POST['ticker_id'], 10 );

		// get the ticker data.
		global $wpdb;
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare( "SELECT sliding_news_color, sliding_news_color_hover, sliding_news_background_color FROM $table_name WHERE id = %d ", $ticker_id );
		$ticker_obj = $wpdb->get_row( $safe_sql );

		// remove the slashes before sending the json response.
		$response                                = new stdClass();
		$response->sliding_news_color            = stripslashes( $ticker_obj->sliding_news_color );
		$response->sliding_news_color_hover      = stripslashes( $ticker_obj->sliding_news_color_hover );
		$response->sliding_news_background_color = stripslashes( $ticker_obj->sliding_news_background_color );

		// return the data with json.
		echo json_encode( $response );

		die();
	}
}
