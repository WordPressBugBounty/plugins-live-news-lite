<?php
/**
 * The Shared class is used to stores properties and methods shared by the admin and public side of WordPress.
 *
 * @package live-news-lite
 */

/**
 * this class should be used to stores properties and methods shared by the
 * admin and public side of WordPress
 */
class Daextlnl_Shared {


	// regex.
	public $hex_rgb_regex     = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';
	public $font_family_regex = '/^([A-Za-z0-9-\'", ]*)$/';

	protected static $instance = null;

	private $data = array();

	private function __construct() {

		// Set plugin textdomain.
		load_plugin_textdomain( 'live-news-lite', false, 'live-news-lite/lang/' );

		$this->data['slug']        = 'daextlnl';
		$this->data['ver']         = '1.08';
		$this->data['dir']         = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']         = substr( plugin_dir_url( __FILE__ ), 0, -7 );
		$this->data['text_domain'] = 'live-news-lite';
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// retrieve data.
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Convert a numeric target to a textual target
	 *
	 * @param $target_id int
	 *
	 * @return string|void|null
	 */
	public function get_textual_target( $target_id ) {

		switch ( $target_id ) {

			case 1:
				return __( 'Website', $this->get( 'text_domain' ) );
				break;

			case 2:
				return __( 'URL', $this->get( 'text_domain' ) );
				break;

		}
	}

	/**
	 * Retrieve the ticker name from the ticker id
	 *
	 * @param $ticker_id int
	 *
	 * @return string|null
	 */
	public function get_textual_ticker( $ticker_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare( "SELECT name FROM $table_name WHERE id = %d ", $ticker_id );
		$ticker_obj = $wpdb->get_row( $safe_sql );

		if ( $ticker_obj !== null ) {
			return $ticker_obj->name;
		} else {
			return __( 'Invalid Ticker ID', $this->get( 'text_domain' ) );
		}
	}

	/**
	 * Generate a short version of a string without truncating words
	 *
	 * @param $str The string
	 * @param $length The maximum length of the string
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
	 * @param $ticker_id int
	 * @return bool True if the ticker is used or False if the ticker is not used
	 */
	public function ticker_is_used( $ticker_id ) {

		global $wpdb;

		// verify if the ticker is used in the featured news
		$table_name     = $wpdb->prefix . $this->get( 'slug' ) . '_featured_news';
		$safe_sql       = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE ticker_id = %d", $ticker_id );
		$number_of_uses = $wpdb->get_var( $safe_sql );
		if ( $number_of_uses > 0 ) {
			return true;}

		// verify if the ticker is used in the sliding news
		$table_name     = $wpdb->prefix . $this->get( 'slug' ) . '_sliding_news';
		$safe_sql       = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE ticker_id = %d", $ticker_id );
		$number_of_uses = $wpdb->get_var( $safe_sql );
		if ( $number_of_uses > 0 ) {
			return true;}

		return false;
	}

	/**
	 * Given a ticker id returns true if the ticker exists or false if the ticker doesn't exist
	 *
	 * @param $ticker_id int
	 * @return bool True if the ticker exists or False if the ticker doesn't exists
	 */
	public function ticker_exists( $ticker_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $ticker_id );
		$ticker_obj = $wpdb->get_row( $safe_sql );
		if ( $ticker_obj !== null ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Given a hexadecimal rgb color an array with the 3 components converted in decimal is returned
	 *
	 * @param string The hexadecimal rgb color
	 * @return array An array with the 3 component of the color converted in decimal
	 */
	public function rgb_hex_to_dec( $hex ) {

		if ( mb_strlen( $hex ) == 3 ) {
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
	 * Get the number of tickers
	 *
	 * @return int The number of tickers
	 */
	public function get_number_of_tickers() {

		global $wpdb;
		$table_name        = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
		$number_of_tickers = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		return $number_of_tickers;
	}

	/**
	 * Get the number of featured news
	 *
	 * @return int The number of featured news
	 */
	public function get_number_of_featured_news() {

		global $wpdb;
		$table_name              = $wpdb->prefix . $this->get( 'slug' ) . '_featured_news';
		$number_of_featured_news = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		return $number_of_featured_news;
	}

	/*
	 * Get the number of sliding news
	 *
	 * @return int The number of sliding news
	 */
	public function get_number_of_sliding_news() {

		global $wpdb;
		$table_name             = $wpdb->prefix . $this->get( 'slug' ) . '_sliding_news';
		$number_of_sliding_news = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		return $number_of_sliding_news;
	}

	/**
	 * Get the current URL with the method specified with the "Detect URL Mode" option
	 */
	public function get_current_url() {

		if ( get_option( $this->get( 'slug' ) . '_detect_url_mode' ) === 'server_variable' ) {

			// Detect the URL using the "Server Variable" method
			return is_ssl() ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		} else {

			// Detect the URL using the "WP Request" method
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
	 * @param $current_url The url that should be searched in the target url field of the tickers
	 * @return mixed The object with the data of the news ticker of false.
	 */
	public function get_ticker_with_target_url( $current_url ) {

		$found      = false;
		$ticker_id  = null;
		$ticker_obj = false;

		global $wpdb;
		$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
		$safe_sql   = "SELECT * FROM $table_name WHERE target = 2 ORDER BY id ASC";
		$ticker_a   = $wpdb->get_results( $safe_sql, ARRAY_A );

		foreach ( $ticker_a as $key => $ticker ) {

			$url_a = preg_split( '/\r\n|[\r\n]/', $ticker['url'] );

			if ( intval( $ticker['url_mode'], 10 ) === 0 ) {

				// Include.

				// Get the ticker_id of the first news ticker that includes the current url.
				if ( $ticker_id !== null ) {
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
		if ( $ticker_id !== null ) {
			global $wpdb;
			$table_name = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
			$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $ticker_id );
			$ticker_obj = $wpdb->get_row( $safe_sql );
		}

		return $ticker_obj;
	}

	/**
	 * Generates the XML version of the data of the table.
	 *
	 * @param db_table_name The name of the db table without the prefix.
	 * @param db_table_primary_key The name of the primary key of the table
	 * @return String The XML version of the data of the db table
	 */
	public function convert_db_table_to_xml( $db_table_name, $db_table_primary_key ) {

		$out = '';

		// Get the data from the db table.
		global $wpdb;
		$table_name = $wpdb->prefix . $this->get( 'slug' ) . "_$db_table_name";
		$data_a     = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY $db_table_primary_key ASC", ARRAY_A );

		// Generate the data of the db table.
		foreach ( $data_a as $record ) {

			$out .= "<$db_table_name>";

			// Get all the indexes of the $data array.
			$record_keys = array_keys( $record );

			// Cycle through all the indexes of the single record and create all the XML tags.
			foreach ( $record_keys as $key ) {
				$out .= '<' . $key . '>' . esc_attr( $record[ $key ] ) . '</' . $key . '>';
			}

			$out .= "</$db_table_name>";

		}

		return $out;
	}

	/**
	 * Objects as a value are set to empty strings. This prevent generating notices with the methods of the wpdb class.
	 *
	 * @param $data An array which includes objects that should be converted to a empty strings.
	 * @return string An array where the objects have been replaced with empty strings.
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
	 * Verifies the number of records available in the database tables of the plugin. If there is at least one record
	 * returns true. Otherwise, returns false.
	 *
	 * @return bool
	 */
	public function plugin_has_data() {

		global $wpdb;

		$table_name    = $wpdb->prefix . $this->get( 'slug' ) . '_tickers';
		$tickers_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		$table_name    = $wpdb->prefix . $this->get( 'slug' ) . '_featured_news';
		$featured_news = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		$table_name   = $wpdb->prefix . $this->get( 'slug' ) . '_sliding_news';
		$sliding_news = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		$total_items = intval( $tickers_items, 10 ) +
			intval( $featured_news, 10 ) +
			intval( $sliding_news, 10 );

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}
}
