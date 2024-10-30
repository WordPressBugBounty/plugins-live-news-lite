<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daextlnl_Shared {

	/**
	 * Regex.
	 *
	 * @var string
	 */
	public $hex_rgb_regex = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';

	/**
	 * Regex.
	 *
	 * @var string
	 */
	public $font_family_regex = '/^([A-Za-z0-9-\'", ]*)$/';

	/**
	 * Regex.
	 *
	 * @var string
	 */
	public $url_regex = '/^(http|https):\/\/[-A-Za-z0-9+&@#\/%?=~_|$!:,.;]+$/';


	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextlnl_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->data['slug'] = 'daextlnl';
		$this->data['ver']  = '1.09';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, - 7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, - 7 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database Version (not available in the options UI).
			$this->get( 'slug' ) . '_database_version' => '0',

			// Options version. (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'  => '0',

			// General ------------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_detect_url_mode'  => 'wp_request',
			$this->get( 'slug' ) . '_load_momentjs'    => '1',
			$this->get( 'slug' ) . '_tickers_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_featured_news_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_sliding_news_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_tools_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_maintenance_menu_capability' => 'manage_options',
			$this->get( 'slug' ) . '_assets_mode'      => '1',

		);
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Daextlnl_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieve data.
	 *
	 * @param int $index The index.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Generate a short version of a string without truncating words
	 *
	 * @param String $str The string.
	 * @param Int    $length The maximum length of the string.
	 *
	 * @return string The short version of the string
	 */
	public function strlen_no_truncate( $str, $length ) {

		if ( mb_strlen( $str ) > $length ) {
			$str = wordwrap( $str, $length );
			$str = mb_substr( $str, 0, mb_strpos( $str, "\n" ) );
			$str = $str . ' ...';
		}

		return $str;
	}

	/**
	 * Returns true if the ticker is used in sliding news or in featured news
	 *
	 * @param Int $ticker_id The ticker id.
	 *
	 * @return bool True if the ticker is used or False if the ticker is not used
	 */
	public function ticker_is_used( $ticker_id ) {

		global $wpdb;

		// Verify if the ticker is used in the featured news.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$number_of_uses = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextlnl_featured_news WHERE ticker_id = %d", $ticker_id )
		);
		if ( $number_of_uses > 0 ) {
			return true;
		}

		// Verify if the ticker is used in the sliding news.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$number_of_uses = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextlnl_sliding_news WHERE ticker_id = %d", $ticker_id )
		);
		if ( $number_of_uses > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Given a hexadecimal rgb color an array with the 3 components converted in decimal is returned
	 *
	 * @param string $hex The hexadecimal rgb color.
	 *
	 * @return array An array with the 3 component of the color converted in decimal
	 */
	public function rgb_hex_to_dec( $hex ) {

		if ( 3 === mb_strlen( $hex ) ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		return array(
			'r' => $r,
			'g' => $g,
			'b' => $b,
		);
	}

	/**
	 * Get the current URL with the method specified with the "Detect URL Mode" option.
	 *
	 * @return string
	 */
	public function get_current_url() {

		if (
				0 === intval( $this->get( 'slug' ) . '_detect_url_mode', 10 ) &&
				isset( $_SERVER['HTTP_HOST'] ) &&
				isset( $_SERVER['REQUEST_URI'] )
		) {

			// Detect the URL using the "Server Variable" method.
			if ( is_ssl() ) {
				$protocol = 'https';
			} else {
				$protocol = 'http';
			}
			return esc_url_raw( $protocol . '://' . wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] ) );

		} else {

			// Detect the URL using the "WP Request" method.
			global $wp;
			return trailingslashit( home_url( add_query_arg( array(), $wp->request ) ) );

		}
	}

	/**
	 * Returns the object of the first ticker with the target equal to 2 (url) that can be displayed with the current
	 * url.
	 *
	 * Note that to determine if a ticker can be displayed with the current url the following fields are considered:
	 *
	 * - Target
	 * - Target URL
	 * - Target URL Mode
	 *
	 * @param String $current_url The url that should be searched in the target url field of the tickers.
	 *
	 * @return mixed The object with the data of the news ticker of false.
	 */
	public function get_ticker_with_target_url( $current_url ) {

		$found      = false;
		$ticker_id  = null;
		$ticker_obj = false;

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ticker_a = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}daextlnl_tickers WHERE target = 2 ORDER BY id ASC",
			ARRAY_A
		);

		foreach ( $ticker_a as $key => $ticker ) {

			$url_a = preg_split( '/\r\n|[\r\n]/', $ticker['url'] );

			if ( intval( $ticker['url_mode'], 10 ) === 0 ) {

				// Include.

				// Get the ticker_id of the first news ticker that includes the current url.
				if ( null !== $ticker_id ) {
					break;
				}

				foreach ( $url_a as $key2 => $url ) {
					if ( $url === $current_url ) {
						$found = true;
					}
				}

				if ( $found ) {
					$ticker_id = $ticker['id'];
					break;
				}
			} else {

				// Exclude.

				// Get the ticker_id of the first news ticker that doesn't include the current url.
				foreach ( $url_a as $key2 => $url ) {
					if ( $url === $current_url ) {
						$found = true;
					}
				}

				if ( ! $found ) {
					$ticker_id = $ticker['id'];
					break;
				}
			}
		}

		// Get the object of the news ticker that includes the current url.
		if ( null !== $ticker_id ) {

			global $wpdb;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$ticker_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextlnl_tickers WHERE id = %d", $ticker_id )
			);

		}

		return $ticker_obj;
	}

	/**
	 * Generates the XML version of the data of the table.
	 *
	 * @param string $db_table_name The name of the db table without the prefix.
	 * @param string $db_table_primary_key The name of the primary key of the table.
	 */
	public function convert_db_table_to_xml( $db_table_name, $db_table_primary_key ) {

		// Get the data from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $db_table_name is sanitized.
		$data_a = $wpdb->get_results(
			'SELECT * FROM ' . $wpdb->prefix . 'daextlnl_' . sanitize_key( $db_table_name ) . ' ORDER BY ' . sanitize_key( $db_table_primary_key ) . ' ASC',
			ARRAY_A
		);
		// phpcs:enable

		// Generate the data of the db table.
		foreach ( $data_a as $record ) {

			echo '<' . esc_attr( $db_table_name ) . '>';

			// Get all the indexes of the $data array.
			$record_keys = array_keys( $record );

			// Cycle through all the indexes of the single record and create all the XML tags.
			foreach ( $record_keys as $key ) {
				echo '<' . esc_attr( $key ) . '>' . esc_attr( $record[ $key ] ) . '</' . esc_attr( $key ) . '>';
			}

			echo '</' . esc_attr( $db_table_name ) . '>';

		}
	}

	/**
	 * Objects as a value are set to empty strings. This prevent generating notices with the methods of the wpdb class.
	 *
	 * @param array $data An array which includes objects that should be converted to a empty strings.
	 * @return mixed An array where the objects have been replaced with empty strings.
	 */
	public function replace_objects_with_empty_strings( $data ) {

		foreach ( $data as $key => $value ) {
			if ( gettype( $value ) === 'object' ) {
				$data[ $key ] = '';
			}
		}

		return $data;
	}

	/**
	 * Delete all the records of the database tables used by the plugin.
	 *
	 * @return void
	 */
	public function reset_plugin_database_tables() {

		global $wpdb;

		// Delete data in 'tickers' table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DELETE FROM {$wpdb->prefix}daextlnl_tickers" );

		// Delete data in 'featured_news' table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DELETE FROM {$wpdb->prefix}daextlnl_featured_news" );

		// Delete data in 'sliding_news' table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DELETE FROM {$wpdb->prefix}daextlnl_sliding_news" );
	}

	/**
	 * Reset the plugin options.
	 *
	 * Set the initial value to all the plugin options.
	 */
	public function reset_plugin_options() {

		$options = $this->get( 'options' );
		foreach ( $options as $option_name => $default_option_value ) {
			update_option( $option_name, $default_option_value );
		}
	}

	/**
	 * Returns an array with the data used by React to initialize the options.
	 *
	 * @return array[]
	 */
	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type,
			);
		}

		$font_weight_select_options = array(
			array(
				'value' => '100',
				'text'  => __( '100', 'live-news-lite'),
			),
			array(
				'value' => '200',
				'text'  => __( '200', 'live-news-lite'),
			),
			array(
				'value' => '300',
				'text'  => __( '300', 'live-news-lite'),
			),
			array(
				'value' => '400',
				'text'  => __( '400 (Normal)', 'live-news-lite'),
			),
			array(
				'value' => '500',
				'text'  => __( '500', 'live-news-lite'),
			),
			array(
				'value' => '600',
				'text'  => __( '600', 'live-news-lite'),
			),
			array(
				'value' => '700',
				'text'  => __( '700 (Bold)', 'live-news-lite'),
			),
			array(
				'value' => '800',
				'text'  => __( '800', 'live-news-lite'),
			),
			array(
				'value' => '900',
				'text'  => __( '900', 'live-news-lite'),
			),
		);

		$configuration = array(
			array(
				'title'       => __( 'General', 'live-news-lite'),
				'description' => __( 'Manage behavior settings and user capabilities.', 'live-news-lite'),
				'cards'       => array(
					array(
						'title'   => __( 'Behavior', 'live-news-lite'),
						'options' => array(
							array(
								'name'          => 'daextlnl_detect_url_mode',
								'label'         => __( 'Detect URL Mode', 'live-news-lite'),
								'type'          => 'select',
								'tooltip'       => __(
									'The method used to detect the URL of the page.',
									'live-news-lite'
								),
								'help'          => __( 'Select the method used to detect the URL of the page.', 'live-news-lite'),
								'selectOptions' => array(
									array(
										'value' => 'server_variable',
										'text'  => __( 'Server Variable', 'live-news-lite'),
									),
									array(
										'value' => 'wp_request',
										'text'  => __( 'WP Request', 'live-news-lite'),
									),
								),
							),
							array(
								'name'    => 'daextlnl_load_momentjs',
								'label'   => __( 'Load Moment.js', 'live-news-lite'),
								'type'    => 'toggle',
								'tooltip' => __(
									'Please note that this library is necessary for the plugin. Disable this option only if other components of your website are already loading it.',
									'live-news-lite'
								),
								'help'    => __( 'Load the Moment.js library in the front end of the site.', 'live-news-lite'),
							),
							array(
								'name'          => 'daextlnl_assets_mode',
								'label'         => __( 'Assets Mode', 'live-news-lite'),
								'type'          => 'select',
								'tooltip'       => __(
									'With "Development" selected the development version of the JavaScript files used by the plugin will be loaded on the front-end. With "Production" selected the minified version of the JavaScript file used by the plugin will be loaded on the front-end.',
									'live-news-lite'
								),
								'help'          => __( 'Select the type of assets loaded on the site front end.', 'live-news-lite'),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Development', 'live-news-lite'),
									),
									array(
										'value' => '1',
										'text'  => __( 'Production', 'live-news-lite'),
									),
								),
							),
						),
					),
					array(
						'title'   => __( 'Capabilities', 'live-news-lite'),
						'options' => array(
							array(
								'name'    => 'daextlnl_tickers_menu_capability',
								'label'   => __( 'Tickers Menu', 'live-news-lite'),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Tickers" menu.',
									'live-news-lite'
								),
								'help'    => __( 'Enter the capability required to get access on the "Tickers" menu.', 'live-news-lite'),
							),
							array(
								'name'    => 'daextlnl_featured_news_menu_capability',
								'label'   => __( 'Featured News Menu', 'live-news-lite'),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Featured News" menu.',
									'live-news-lite'
								),
								'help'    => __( 'Enter the capability required to get access on the "Featured News" menu.', 'live-news-lite'),
							),
							array(
								'name'    => 'daextlnl_sliding_news_menu_capability',
								'label'   => __( 'Sliding News Menu', 'live-news-lite'),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Sliding News" menu.',
									'live-news-lite'
								),
								'help'    => __( 'The capability required to get access on the "Sliding News" menu.', 'live-news-lite'),
							),
							array(
								'name'    => 'daextlnl_tools_menu_capability',
								'label'   => __( 'Tools Menu', 'live-news-lite'),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Tools" menu.',
									'live-news-lite'
								),
								'help'    => __( 'Enter the capability required to get access on the "Tools" menu.', 'live-news-lite'),
							),
							array(
								'name'    => 'daextlnl_maintenance_menu_capability',
								'label'   => __( 'Maintenance Menu', 'live-news-lite'),
								'type'    => 'text',
								'tooltip' => __(
									'The capability required to get access on the "Maintenance" menu.',
									'live-news-lite'
								),
								'help'    => __( 'Enter the capability required to get access on the "Maintenance" menu.', 'live-news-lite'),
							),
						),
					),
				),
			),
		);

		return $configuration;
	}

	/**
	 * Echo the SVG icon specified by the $icon_name parameter.
	 *
	 * @param string $icon_name The name of the icon to echo.
	 *
	 * @return void
	 */
	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'refresh-ccw-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 18.8746C19.1213 17.329 20.5 14.8255 20.5 12C20.5 7.30555 16.6944 3.49998 12 3.49998H11.5M12 20.5C7.30558 20.5 3.5 16.6944 3.5 12C3.5 9.17444 4.87867 6.67091 7 5.12537M11 22.4L13 20.4L11 18.4M13 5.59998L11 3.59998L13 1.59998" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'database-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M14 20C14 21.1046 13.1046 22 12 22C10.8954 22 10 21.1046 10 20M14 20C14 18.8954 13.1046 18 12 18M14 20H21M10 20C10 18.8954 10.8954 18 12 18M10 20H3M12 18V14M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V11C21 12.66 17 14 12 14M3 5V11C3 12.66 7 14 12 14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'passcode':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 12H12.005M17 12H17.005M7 12H7.005M5.2 7H18.8C19.9201 7 20.4802 7 20.908 7.21799C21.2843 7.40973 21.5903 7.71569 21.782 8.09202C22 8.51984 22 9.0799 22 10.2V13.8C22 14.9201 22 15.4802 21.782 15.908C21.5903 16.2843 21.2843 16.5903 20.908 16.782C20.4802 17 19.9201 17 18.8 17H5.2C4.0799 17 3.51984 17 3.09202 16.782C2.71569 16.5903 2.40973 16.2843 2.21799 15.908C2 15.4802 2 14.9201 2 13.8V10.2C2 9.0799 2 8.51984 2.21799 8.09202C2.40973 7.71569 2.71569 7.40973 3.09202 7.21799C3.51984 7 4.0799 7 5.2 7ZM12.25 12C12.25 12.1381 12.1381 12.25 12 12.25C11.8619 12.25 11.75 12.1381 11.75 12C11.75 11.8619 11.8619 11.75 12 11.75C12.1381 11.75 12.25 11.8619 12.25 12ZM17.25 12C17.25 12.1381 17.1381 12.25 17 12.25C16.8619 12.25 16.75 12.1381 16.75 12C16.75 11.8619 16.8619 11.75 17 11.75C17.1381 11.75 17.25 11.8619 17.25 12ZM7.25 12C7.25 12.1381 7.13807 12.25 7 12.25C6.86193 12.25 6.75 12.1381 6.75 12C6.75 11.8619 6.86193 11.75 7 11.75C7.13807 11.75 7.25 11.8619 7.25 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'award-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7 15.0903V22L11.7029 20.1188C11.8126 20.0749 11.8675 20.053 11.9242 20.0443C11.9744 20.0366 12.0256 20.0366 12.0758 20.0443C12.1325 20.053 12.1874 20.0749 12.2971 20.1188L17 22V15.0903M19.5 9.5C19.5 13.6421 16.1421 17 12 17C7.85786 17 4.5 13.6421 4.5 9.5C4.5 5.35786 7.85786 2 12 2C16.1421 2 19.5 5.35786 19.5 9.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'align-right-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 10C18.9319 10 19.3978 10 19.7654 9.84776C20.2554 9.64477 20.6448 9.25542 20.8478 8.76537C21 8.39782 21 7.93188 21 7C21 6.06812 21 5.60218 20.8478 5.23463C20.6448 4.74458 20.2554 4.35523 19.7654 4.15224C19.3978 4 18.9319 4 18 4L10 4C9.06812 4 8.60218 4 8.23463 4.15224C7.74458 4.35523 7.35523 4.74458 7.15224 5.23463C7 5.60218 7 6.06812 7 7C7 7.93188 7 8.39782 7.15224 8.76537C7.35523 9.25542 7.74458 9.64477 8.23463 9.84776C8.60218 10 9.06812 10 10 10L18 10Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18 20C18.9319 20 19.3978 20 19.7654 19.8478C20.2554 19.6448 20.6448 19.2554 20.8478 18.7654C21 18.3978 21 17.9319 21 17C21 16.0681 21 15.6022 20.8478 15.2346C20.6448 14.7446 20.2554 14.3552 19.7654 14.1522C19.3978 14 18.9319 14 18 14H6C5.06812 14 4.60218 14 4.23463 14.1522C3.74458 14.3552 3.35523 14.7446 3.15224 15.2346C3 15.6022 3 16.0681 3 17C3 17.9319 3 18.3978 3.15224 18.7654C3.35523 19.2554 3.74458 19.6448 4.23463 19.8478C4.60218 20 5.06812 20 6 20L18 20Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'notification-text':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M11 4H7.8C6.11984 4 5.27976 4 4.63803 4.32698C4.07354 4.6146 3.6146 5.07354 3.32698 5.63803C3 6.27976 3 7.11984 3 8.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V13M13 17H7M15 13H7M20.1213 3.87868C21.2929 5.05025 21.2929 6.94975 20.1213 8.12132C18.9497 9.29289 17.0503 9.29289 15.8787 8.12132C14.7071 6.94975 14.7071 5.05025 15.8787 3.87868C17.0503 2.70711 18.9497 2.70711 20.1213 3.87868Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'line-chart-up-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 21H4.6C4.03995 21 3.75992 21 3.54601 20.891C3.35785 20.7951 3.20487 20.6422 3.10899 20.454C3 20.2401 3 19.9601 3 19.4V3M21 7L15.5657 12.4343C15.3677 12.6323 15.2687 12.7313 15.1545 12.7684C15.0541 12.8011 14.9459 12.8011 14.8455 12.7684C14.7313 12.7313 14.6323 12.6323 14.4343 12.4343L12.5657 10.5657C12.3677 10.3677 12.2687 10.2687 12.1545 10.2316C12.0541 10.1989 11.9459 10.1989 11.8455 10.2316C11.7313 10.2687 11.6323 10.3677 11.4343 10.5657L7 15M21 7H17M21 7V11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'database-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V19C21 20.66 17 22 12 22C7 22 3 20.66 3 19V5M21 9.72021C21 11.3802 17 12.7202 12 12.7202C7 12.7202 3 11.3802 3 9.72021M21 14.44C21 16.1 17 17.44 12 17.44C7 17.44 3 16.1 3 14.44" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'marker-pin-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 14.2864C3.14864 15.1031 2 16.2412 2 17.5C2 19.9853 6.47715 22 12 22C17.5228 22 22 19.9853 22 17.5C22 16.2412 20.8514 15.1031 19 14.2864M18 8C18 12.0637 13.5 14 12 17C10.5 14 6 12.0637 6 8C6 4.68629 8.68629 2 12 2C15.3137 2 18 4.68629 18 8ZM13 8C13 8.55228 12.5523 9 12 9C11.4477 9 11 8.55228 11 8C11 7.44772 11.4477 7 12 7C12.5523 7 13 7.44772 13 8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'layout-alt-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 9H21M9 9L9 21M7.8 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11984 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V7.8C3 6.11984 3 5.27976 3.32698 4.63803C3.6146 4.07354 4.07354 3.6146 4.63803 3.32698C5.27976 3 6.11984 3 7.8 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'brush-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M20 10V3.6C20 3.03995 20 2.75992 19.891 2.54601C19.7951 2.35785 19.6422 2.20487 19.454 2.10899C19.2401 2 18.9601 2 18.4 2H5.6C5.03995 2 4.75992 2 4.54601 2.10899C4.35785 2.20487 4.20487 2.35785 4.10899 2.54601C4 2.75992 4 3.03995 4 3.6V10M20 10H4M20 10V10.2C20 11.8802 20 12.7202 19.673 13.362C19.3854 13.9265 18.9265 14.3854 18.362 14.673C17.7202 15 16.8802 15 15.2 15H8.8C7.11984 15 6.27976 15 5.63803 14.673C5.07354 14.3854 4.6146 13.9265 4.32698 13.362C4 12.7202 4 11.8802 4 10.2V10M14.5 15V19.5C14.5 20.8807 13.3807 22 12 22C10.6193 22 9.5 20.8807 9.5 19.5V15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'share-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M21 6H17.8C16.1198 6 15.2798 6 14.638 6.32698C14.0735 6.6146 13.6146 7.07354 13.327 7.63803C13 8.27976 13 9.11984 13 10.8V12M21 6L18 3M21 6L18 9M10 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-code-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 18.5C5 18.9644 5 19.1966 5.02567 19.3916C5.2029 20.7378 6.26222 21.7971 7.60842 21.9743C7.80337 22 8.03558 22 8.5 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V9.98822C21 9.25445 21 8.88757 20.9171 8.5423C20.8436 8.2362 20.7224 7.94356 20.5579 7.67515C20.3724 7.3724 20.113 7.11296 19.5941 6.59411L16.4059 3.40589C15.887 2.88703 15.6276 2.6276 15.3249 2.44208C15.0564 2.27759 14.7638 2.15638 14.4577 2.08289C14.1124 2 13.7455 2 13.0118 2H8.5C8.03558 2 7.80337 2 7.60842 2.02567C6.26222 2.2029 5.2029 3.26222 5.02567 4.60842C5 4.80337 5 5.03558 5 5.5M9 14.5L11.5 12L9 9.5M5 9.5L2.5 12L5 14.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'palette':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M2 12C2 17.5228 6.47715 22 12 22C13.6569 22 15 20.6569 15 19V18.5C15 18.0356 15 17.8034 15.0257 17.6084C15.2029 16.2622 16.2622 15.2029 17.6084 15.0257C17.8034 15 18.0356 15 18.5 15H19C20.6569 15 22 13.6569 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M7 13C7.55228 13 8 12.5523 8 12C8 11.4477 7.55228 11 7 11C6.44772 11 6 11.4477 6 12C6 12.5523 6.44772 13 7 13Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M16 9C16.5523 9 17 8.55228 17 8C17 7.44772 16.5523 7 16 7C15.4477 7 15 7.44772 15 8C15 8.55228 15.4477 9 16 9Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M10 8C10.5523 8 11 7.55228 11 7C11 6.44772 10.5523 6 10 6C9.44772 6 9 6.44772 9 7C9 7.55228 9.44772 8 10 8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Save a dismissible notice in the "daextlnl_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $element_class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daextlnl_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daextlnl_dismissible_notice_a" WordPress option.
		update_option( 'daextlnl_dismissible_notice_a', $dismissible_notice_a );
	}

	/**
	 * Display the dismissible notices stored in the "daextlnl_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * The dismissable notice is first displayed (only to the same user with which has been generated) and then it is
	 * removed from the "daextlnl_dismissible_notice_a" option.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daextlnl_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->echo_icon_svg( 'x' ); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daextlnl_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daextlnl_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}

	/**
	 * Sanitize the data of an uploaded file.
	 *
	 * @param array $file The data of the uploaded file.
	 *
	 * @return array
	 */
	public function sanitize_uploaded_file( $file ) {

		return array(
			'name'     => sanitize_file_name( $file['name'] ),
			'type'     => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'error'    => intval( $file['error'], 10 ),
			'size'     => intval( $file['size'], 10 ),
		);
	}

	/**
	 * Returns true if there are exportable data or false if here are no exportable data.
	 */
	public function exportable_data_exists() {

		$exportable_data = false;
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextlnl_tickers" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		return $exportable_data;
	}

	/**
	 * Get the name of a ticker.
	 *
	 * @param int $ticker_id The id of the ticker.
	 *
	 * @return string|null
	 */
	public function get_ticker_name( $ticker_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ticker_obj = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT name FROM {$wpdb->prefix}daextlnl_tickers WHERE id = %d",
				$ticker_id
			)
		);

		if ( null !== $ticker_obj ) {
			return $ticker_obj->name;
		} else {
			return __( 'None', 'live-news-lite');
		}
	}
}
