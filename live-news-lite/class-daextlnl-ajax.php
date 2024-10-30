<?php
/**
 * File used to include ajax actions.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextlnl_Ajax {

	/**
	 * The single instance of the class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The single instance of the shared class.
	 *
	 * @var Daextlnl_Shared|null
	 */
	private $shared = null;

	/**
	 * Return an instance of this class.
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
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextlnl_Shared::get_instance();

		// Ajax requests --------------------------------------------------------.
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

		// Check the referer.
		check_ajax_referer( 'live-news-lite', 'security' );

		// Sanitize data.
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : null;

		// Save the current status ( open/closed ) in a cookie.
		if ( 'open' === $status ) {

			setcookie( 'live_news_status', 'open', 0, '/' );

		} else {

			setcookie( 'live_news_status', 'closed', 0, '/' );

		}

		echo 'success';

		die();
	}

	/**
	 * Generate an XML response with included all the data of the ticker. The data are generated based on the options
	 *  defined for the specific ticker.
	 *
	 * @return void
	 */
	public function get_ticker_data() {

		// Check the referer.
		check_ajax_referer( 'live-news-lite', 'security' );

		// Get the ticker id.
		$ticker_id = isset( $_POST['ticker_id'] ) ? intval( $_POST['ticker_id'], 10 ) : null;

		// Get the ticker information.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ticker_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextlnl_tickers WHERE id = %d", $ticker_id )
		);

		// If there isn't a ticker associated with this ticker_id die().
		if ( null === $ticker_obj ) {
			die( 'Invalid Ticker ID.' );
		}

		// Get the transient with included the data of the ticker if available.
		$ticker_data = get_transient( 'daextlnl_ticker_' . $ticker_obj->id );

		// Generate the data of the ticker only if the transient with the data is not available.
		if ( false === $ticker_data ) {

			$ticker_data = array(
				'featured_news' => array(),
				'sliding_news'  => array(),
				'time'          => '',
			);

			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT id, news_title, news_excerpt, url FROM {$wpdb->prefix}daextlnl_featured_news WHERE ticker_id = %d ORDER BY id DESC LIMIT 1", $ticker_obj->id ),
				ARRAY_A
			);

			if ( count( $results ) > 0 ) {

				$ticker_data['featured_news'] = array(
					'newstitle'   => $this->shared->strlen_no_truncate( stripslashes( $results[0]['news_title'] ), $ticker_obj->featured_title_maximum_length ),
					'newsexcerpt' => $this->shared->strlen_no_truncate( stripslashes( $results[0]['news_excerpt'] ), $ticker_obj->featured_excerpt_maximum_length ),
					'url'         => stripslashes( $results[0]['url'] ),
				);

			}

			// Generate sliding news ------------------------------------------------------------------------------.

			// Get number of sliding news from the option.
			$number_of_sliding_news = intval( $ticker_obj->number_of_sliding_news, 10 );

			/*
			 * Set the offset based on the "Hide Featured News" option. If the featured news is hidden then offset is 0,
			 * if the featured news is shown the offset is 1.
			 */
			if ( 2 === $ticker_obj->hide_featured_news ) {
				$offset = 0;
			} else {
				$offset = 1;
			}

			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT id, news_title, url, text_color, text_color_hover, background_color, background_color_opacity, image_before, image_after FROM {$wpdb->prefix}daextlnl_sliding_news WHERE ticker_id = %d ORDER BY id DESC LIMIT %d", $ticker_obj->id, $number_of_sliding_news ),
				ARRAY_A
			);

			if ( count( $results ) > 0 ) {
				foreach ( $results as $result ) {

					$ticker_data['sliding_news'][] = array(
						'newstitle'                => $this->shared->strlen_no_truncate( stripslashes( $result['news_title'] ), $ticker_obj->sliding_news_maximum_length ),
						'url'                      => stripslashes( $result['url'] ),
						'text_color'               => stripslashes( $result['text_color'] ),
						'text_color_hover'         => stripslashes( $result['text_color_hover'] ),
						'background_color'         => stripslashes( $result['background_color'] ),
						'background_color_opacity' => $result['background_color_opacity'],
						'image_before'             => stripslashes( $result['image_before'] ),
						'image_after'              => stripslashes( $result['image_after'] ),
					);

				}
			}

			// generate current time XML ------------------------------------------------------------------------------.
			$ticker_data['time'] = time() + $ticker_obj->clock_offset;

			if ( $ticker_obj->transient_expiration > 0 ) {

				set_transient( 'daextlnl_ticker_' . $ticker_obj->id, $ticker_data, $ticker_obj->transient_expiration );

			}
		}

		// Return response.
		echo wp_json_encode( $ticker_data );

		// Terminate the current script.
		die();
	}

	/**
	 * Retrieve the "Sliding News Color", the "Sliding News Color Hover, and the "Sliding News Background Color" from
	 * the tickers to initialize the values of the three fields in the "Sliding News" menu.
	 *
	 * @return void
	 */
	public function update_default_colors() {

		// Check the referer.
		check_ajax_referer( 'live-news-lite', 'security' );

		// Check the capability.
		if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_sliding_news_menu_capability' ) ) ) {
			die();
		}

		// Get the missing word id.
		$ticker_id = isset( $_POST['ticker_id'] ) ? intval( $_POST['ticker_id'], 10 ) : null;

		// Get the ticker data.
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ticker_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT sliding_news_color, sliding_news_color_hover, sliding_news_background_color FROM {$wpdb->prefix}daextlnl_tickers WHERE id = %d ", $ticker_id )
		);

		// Remove the slashes before sending the json response.
		$response                                = new stdClass();
		$response->sliding_news_color            = stripslashes( $ticker_obj->sliding_news_color );
		$response->sliding_news_color_hover      = stripslashes( $ticker_obj->sliding_news_color_hover );
		$response->sliding_news_background_color = stripslashes( $ticker_obj->sliding_news_background_color );

		// Return the data with json.
		echo wp_json_encode( $response );

		die();
	}
}
