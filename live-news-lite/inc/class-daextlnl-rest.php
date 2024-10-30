<?php
/**
 * Here the REST API endpoint of the plugin are registered.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to work with the REST API endpoints of the plugin.
 */
class Daextlnl_Rest {

	/**
	 * The singleton instance of the class.
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
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Daextlnl_Shared::get_instance();

		/**
		 * Add custom routes to the Rest API.
		 */
		add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );
	}

	/**
	 * Create a singleton instance of the class.
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
	 * Add custom routes to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_route() {

		// Add the GET 'live-news-lite/v1/options' endpoint to the Rest API.
		register_rest_route(
			'live-news-lite/v1',
			'/read-options/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_live_news_read_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_live_news_read_options_callback_permission_check' ),
			)
		);

		// Add the POST 'live-news-lite/v1/options' endpoint to the Rest API.
		register_rest_route(
			'live-news-lite/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_live_news_update_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_live_news_update_options_callback_permission_check' ),

			)
		);
	}

	/**
	 * Callback for the GET 'live-news/v1/options' endpoint of the Rest API.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_daext_live_news_read_options_callback() {

		// Generate the response.
		$response = array();
		foreach ( $this->shared->get( 'options' ) as $key => $value ) {
			$response[ $key ] = get_option( $key );
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_live_news_read_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to read the Live News options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'live-news/v1/options' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 *  - To update the plugin options in the "Options" menu.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_daext_live_news_update_options_callback( $request ) {

		$options = array();

		// Get and sanitize data --------------------------------------------------------------------------------------.

		// General - Tab -----------------------------------------------------------------------------------------------.

		// Card 1 - Section --------------------------------------------------------------------------------------.
		$options['daextlnl_detect_url_mode']               = $request->get_param( 'daextlnl_detect_url_mode' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_detect_url_mode' ) ) : null;
		$options['daextlnl_load_momentjs']                 = $request->get_param( 'daextlnl_load_momentjs' ) !== null ? intval( $request->get_param( 'daextlnl_load_momentjs' ), 10 ) : null;
		$options['daextlnl_assets_mode']                   = $request->get_param( 'daextlnl_assets_mode' ) !== null ? intval( $request->get_param( 'daextlnl_assets_mode' ), 10 ) : null;
		$options['daextlnl_tickers_menu_capability']       = $request->get_param( 'daextlnl_tickers_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_tickers_menu_capability' ) ) : null;
		$options['daextlnl_featured_news_menu_capability'] = $request->get_param( 'daextlnl_featured_news_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_featured_news_menu_capability' ) ) : null;
		$options['daextlnl_sliding_news_menu_capability']  = $request->get_param( 'daextlnl_sliding_news_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_sliding_news_menu_capability' ) ) : null;
		$options['daextlnl_tools_menu_capability']         = $request->get_param( 'daextlnl_tools_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_tools_menu_capability' ) ) : null;
		$options['daextlnl_import_menu_capability']        = $request->get_param( 'daextlnl_import_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_import_menu_capability' ) ) : null;
		$options['daextlnl_export_menu_capability']        = $request->get_param( 'daextlnl_export_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_export_menu_capability' ) ) : null;
		$options['daextlnl_maintenance_menu_capability']   = $request->get_param( 'daextlnl_maintenance_menu_capability' ) !== null ? sanitize_key( $request->get_param( 'daextlnl_maintenance_menu_capability' ) ) : null;

		// Update the options -----------------------------------------------------------------------------------------.
		foreach ( $options as $key => $option ) {
			if ( null !== $option ) {
				update_option( $key, $option );
			}
		}

		$response = new WP_REST_Response( 'Data successfully added.', '200' );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_live_news_update_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
