<?php
/**
 * This class is used to work with the administrative side of WordPress.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextlnl_Admin {

	protected static $instance = null;
	private $shared            = null;

	private $screen_id_tickers       = null;
	private $screen_id_featured      = null;
	private $screen_id_sliding       = null;
	private $screen_id_export_to_pro = null;
	private $screen_id_help          = null;
	private $screen_id_pro_version   = null;
	private $screen_id_options       = null;

	private function __construct() {

		// assign an instance of the shared class.
		$this->shared = Daextlnl_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Write in back-end head.
		add_action( 'admin_head', array( $this, 'wr_admin_head' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Load the options API registrations and callbacks.
		add_action( 'admin_init', array( $this, 'op_register_options' ) );

		// this hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// this hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Export XML controller.
		add_action( 'init', array( $this, 'export_xml_controller' ) );

		// Change the WordPress footer text on all the plugin menus.
		add_filter( 'admin_footer_text', array( $this, 'change_footer_text' ) );
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Write in the admin head.
	 */
	public function wr_admin_head() {

		echo '<script type="text/javascript">';
		echo 'var daextlnl_ajax_url = "' . admin_url( 'admin-ajax.php' ) . '";';
		echo 'var daextlnl_nonce = "' . wp_create_nonce( 'live-news' ) . '";';
		echo 'var daextlnl_admin_url ="' . get_admin_url() . '";';
		echo '</script>';
	}

	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// menu tickers.
		if ( $screen->id == $this->screen_id_tickers ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-sliding', $this->shared->get( 'url' ) . 'admin/assets/css/menu-tickers.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen-custom', $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get( 'ver' ) );
		}

		// menu featured.
		if ( $screen->id == $this->screen_id_featured ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-featured', $this->shared->get( 'url' ) . 'admin/assets/css/menu-featured.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen-custom', $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get( 'ver' ) );
		}

		// menu sliding.
		if ( $screen->id == $this->screen_id_sliding ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-menu-sliding', $this->shared->get( 'url' ) . 'admin/assets/css/menu-sliding.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework/menu.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen-custom', $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get( 'ver' ) );
		}

		// menu help.
		if ( $screen->id == $this->screen_id_help ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-menu-help',
				$this->shared->get( 'url' ) . 'admin/assets/css/menu-help.css',
				array(),
				$this->shared->get( 'ver' )
			);
		}

		// menu pro version
		if ( $screen->id == $this->screen_id_pro_version ) {
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-menu-pro-version',
				$this->shared->get( 'url' ) . 'admin/assets/css/menu-pro-version.css',
				array(),
				$this->shared->get( 'ver' )
			);
		}

		// menu options.
		if ( $screen->id == $this->screen_id_options ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-options', $this->shared->get( 'url' ) . 'admin/assets/css/framework/options.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-tooltip.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.css', array(), $this->shared->get( 'ver' ) );
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-chosen-custom', $this->shared->get( 'url' ) . 'admin/assets/css/chosen-custom.css', array(), $this->shared->get( 'ver' ) );
		}
	}

	/**
	 * Enqueue admin-specific javascript.
	 */
	public function enqueue_admin_scripts() {

		$screen = get_current_screen();

		// menu tickers.
		if ( $screen->id == $this->screen_id_tickers ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-tickers', $this->shared->get( 'url' ) . 'admin/assets/js/menu-tickers.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-wp-color-picker-init', $this->shared->get( 'url' ) . 'admin/assets/js/wp-color-picker-init.js', array( 'wp-color-picker' ), false, true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-chosen-init-tickers', $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init-tickers.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_media();
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-media-uploader', $this->shared->get( 'url' ) . 'admin/assets/js/media-uploader.js', 'jquery', $this->shared->get( 'ver' ) );
		}

		// menu featured.
		if ( $screen->id == $this->screen_id_featured ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-chosen-init-featured', $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init-featured.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-featured', $this->shared->get( 'url' ) . 'admin/assets/js/menu-featured.js', 'jquery', $this->shared->get( 'ver' ) );
		}

		// menu sliding.
		if ( $screen->id == $this->screen_id_sliding ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-chosen-init-sliding', $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init-sliding.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-wp-color-picker-init', $this->shared->get( 'url' ) . 'admin/assets/js/wp-color-picker-init.js', array( 'wp-color-picker' ), false, true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-sliding', $this->shared->get( 'url' ) . 'admin/assets/js/menu-sliding.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_media();
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-media-uploader', $this->shared->get( 'url' ) . 'admin/assets/js/media-uploader.js', 'jquery', $this->shared->get( 'ver' ) );
		}

		// menu options.
		if ( $screen->id == $this->screen_id_options ) {
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-jquery-ui-tooltip-init', $this->shared->get( 'url' ) . 'admin/assets/js/jquery-ui-tooltip-init.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-chosen', $this->shared->get( 'url' ) . 'admin/assets/inc/chosen/chosen-min.js', 'jquery', $this->shared->get( 'ver' ) );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-chosen-init-options', $this->shared->get( 'url' ) . 'admin/assets/js/chosen-init-options.js', 'jquery', $this->shared->get( 'ver' ) );
		}
	}

	/**
	 * plugin activation.
	 */
	static public function ac_activate( $networkwide ) {

		/**
		 * Create options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) and is_multisite() ) {

			/**
			 * If this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// create an array with all the blog ids.
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// switch to the iterated blog.
					switch_to_blog( $blog_id );

					// create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * if this is not a "Network Activation" create options and
				 * tables only for the current blog
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * If this is not a multisite installation create options and
			 * tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param $blog_id
	 * @param $user_id
	 * @param $domain
	 * @param $path
	 * @param $site_id
	 * @param $meta
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		global $wpdb;

		/**
		 * If the plugin is "Network Active" create the options and tables for
		 * this new blog.
		 */
		if ( is_plugin_active_for_network( 'uberchart/init.php' ) ) {

			// get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param $blog_id
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 */
	static private function ac_initialize_options() {

		// assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		// database version -----------------------------------------------------.
		add_option( $shared->get( 'slug' ) . '_database_version', '0' );

		// general --------------------------------------------------------------.
		add_option( $shared->get( 'slug' ) . '_detect_url_mode', 'wp_request' );
		add_option( $shared->get( 'slug' ) . '_tickers_menu_capability', 'manage_options' );
		add_option( $shared->get( 'slug' ) . '_featured_menu_capability', 'manage_options' );
		add_option( $shared->get( 'slug' ) . '_sliding_menu_capability', 'manage_options' );
	}

	/**
	 * Create the plugin database tables.
	 */
	static private function ac_create_database_tables() {

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// Check database version and create the database.
		if ( intval( get_option( 'daextlnl_database_version' ), 10 ) < 1 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Create *prefix*_daextlnl_tickers.
			global $wpdb;
			$table_name = $wpdb->prefix . 'daextlnl_tickers';
			$sql        = "CREATE TABLE $table_name (
                  `name` varchar(100) NOT NULL DEFAULT '',
                  `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `target` int(11) NOT NULL DEFAULT '1',
                  `url` TEXT NOT NULL DEFAULT '',
                  `open_links_new_tab` tinyint(1) DEFAULT '0',
                  `clock_offset` int(11) NOT NULL DEFAULT '0',
                  `clock_format` varchar(40) NOT NULL DEFAULT 'HH:mm',
                  `clock_source` int(11) NOT NULL DEFAULT '2',
                  `clock_autoupdate` tinyint(1) DEFAULT '1',
                  `clock_autoupdate_time` int(11) NOT NULL DEFAULT '10',
                  `number_of_sliding_news` int(11) NOT NULL DEFAULT '10',
                  `featured_title_maximum_length` int(11) NOT NULL DEFAULT '255',
                  `featured_excerpt_maximum_length` int(11) NOT NULL DEFAULT '255',
                  `sliding_news_maximum_length` int(11) NOT NULL DEFAULT '255',
                  `open_news_as_default` tinyint(1) DEFAULT '1',
                  `hide_featured_news` int(11) NOT NULL DEFAULT '1',
                  `hide_clock` tinyint(1) DEFAULT '0',
                  `enable_rtl_layout` tinyint(1) DEFAULT '0',
                  `cached_cycles` int(11) NOT NULL DEFAULT '0',
                  `featured_news_background_color` varchar(7) DEFAULT NULL,
                  `sliding_news_background_color` varchar(7) DEFAULT NULL,
                  `sliding_news_background_color_opacity` float DEFAULT NULL,
                  `font_family` varchar(255) DEFAULT NULL,
                  `google_font` varchar(255) DEFAULT NULL,
                  `featured_title_font_size` int(11) NOT NULL DEFAULT '38',
                  `featured_excerpt_font_size` int(11) NOT NULL DEFAULT '28',
                  `sliding_news_font_size` int(11) NOT NULL DEFAULT '28',
                  `clock_font_size` int(11) NOT NULL DEFAULT '28',
                  `enable_with_mobile_devices` tinyint(1) DEFAULT '0',
                  `open_button_image` varchar(2083) NOT NULL DEFAULT '',
                  `close_button_image` varchar(2083) NOT NULL DEFAULT '',
                  `clock_background_image` varchar(2083) NOT NULL DEFAULT '',
                  `featured_news_title_color` varchar(7) DEFAULT NULL,
                  `featured_news_title_color_hover` varchar(7) DEFAULT NULL,
                  `featured_news_excerpt_color` varchar(7) DEFAULT NULL,
                  `sliding_news_color` varchar(7) DEFAULT NULL,
                  `sliding_news_color_hover` varchar(7) DEFAULT NULL,
                  `clock_text_color` varchar(7) DEFAULT NULL,
                  `featured_news_background_color_opacity` float DEFAULT NULL,
                  `enable_ticker` tinyint(1) DEFAULT '1',
                  `enable_links` tinyint(1) DEFAULT '1',
                  `transient_expiration` int(11) NOT NULL DEFAULT '0',
                  `sliding_news_margin` int(11) NOT NULL DEFAULT '84',
                  `sliding_news_padding` int(11) NOT NULL DEFAULT '28',
                  `url_mode` tinyint(1) DEFAULT '0'
            ) $charset_collate";

			dbDelta( $sql );

			// Create *prefix*_daextlnl_featured_news.
			global $wpdb;
			$table_name = $wpdb->prefix . 'daextlnl_featured_news';
			$sql        = "CREATE TABLE $table_name (
                  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `news_title` varchar(1000) NOT NULL DEFAULT '',
                  `news_excerpt` varchar(1000) NOT NULL DEFAULT '',
                  `url` varchar(2083) NOT NULL DEFAULT '',
                  `ticker_id` bigint(20) NOT NULL
            ) $charset_collate";

			dbDelta( $sql );

			// create *prefix*_daextlnl_sliding_news.
			global $wpdb;
			$table_name = $wpdb->prefix . 'daextlnl_sliding_news';
			$sql        = "CREATE TABLE $table_name (
                  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `news_title` varchar(1000) NOT NULL DEFAULT '',
                  `url` varchar(2083) NOT NULL DEFAULT '',
                  `ticker_id` bigint(20) NOT NULL,
                  `text_color` varchar(7) DEFAULT NULL,
                  `text_color_hover` varchar(7) DEFAULT NULL,
                  `background_color` varchar(7) DEFAULT NULL,
                  `background_color_opacity` float DEFAULT NULL,
                  `image_before` varchar(2083) NOT NULL DEFAULT '',
                  `image_after` varchar(2083) NOT NULL DEFAULT ''
            ) $charset_collate";

			dbDelta( $sql );

			// Update database version.
			update_option( 'daextlnl_database_version', '1' );

		}
	}

	/**
	 * Plugin delete.
	 */
	public static function un_delete() {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) and is_multisite() ) {

			// get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// create an array with all the blog ids.
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// switch to the iterated blog.
				switch_to_blog( $blog_id );

				// create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			/**
			 * If this is not a multisite installation delete options and
			 * tables only for the current blog.
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 */
	public static function un_delete_options() {

		// assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		// database version -----------------------------------------------------.
		delete_option( $shared->get( 'slug' ) . '_database_version' );

		// general --------------------------------------------------------------.
		delete_option( $shared->get( 'slug' ) . '_detect_url_mode' );
		delete_option( $shared->get( 'slug' ) . '_tickers_menu_capability' );
		delete_option( $shared->get( 'slug' ) . '_featured_menu_capability' );
		delete_option( $shared->get( 'slug' ) . '_sliding_menu_capability' );
	}

	/**
	 * Delete plugin database tables.
	 */
	public static function un_delete_database_tables() {

		// assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		global $wpdb;

		// delete transients associated with the table prefix '_tickers'.
		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_tickers';
		$results    = $wpdb->get_results( "SELECT id FROM $table_name", ARRAY_A );
		foreach ( $results as $result ) {
			delete_transient( 'daextlnl_ticker_' . $result['id'] );
		}

		// delete table prefix + '_tickers'.
		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_tickers';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql );

		// delete table prefix + '_featured_news'.
		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_featured_news';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql );

		// delete table prefix + '_sliding_news'.
		$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_sliding_news';
		$sql        = "DROP TABLE $table_name";
		$wpdb->query( $sql );
	}

	/**
	 * Register the admin menu.
	 */
	public function me_add_admin_menu() {

		add_menu_page(
			esc_html__( 'LN', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Live News', $this->shared->get( 'text_domain' ) ),
			get_option( $this->shared->get( 'slug' ) . '_tickers_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tickers',
			array( $this, 'me_display_menu_tickers' ),
			'dashicons-admin-site'
		);

		$this->screen_id_tickers = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'News Tickers', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'News Tickers', $this->shared->get( 'text_domain' ) ),
			get_option( $this->shared->get( 'slug' ) . '_tickers_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tickers',
			array( $this, 'me_display_menu_tickers' )
		);

		$this->screen_id_featured = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Featured News', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Featured News', $this->shared->get( 'text_domain' ) ),
			get_option( $this->shared->get( 'slug' ) . '_featured_menu_capability' ),
			$this->shared->get( 'slug' ) . '-featured',
			array( $this, 'me_display_menu_featured' )
		);

		$this->screen_id_sliding = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Sliding News', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Sliding News', $this->shared->get( 'text_domain' ) ),
			get_option( $this->shared->get( 'slug' ) . '_sliding_menu_capability' ),
			$this->shared->get( 'slug' ) . '-sliding',
			array( $this, 'me_display_menu_sliding' )
		);

		$this->screen_id_export_to_pro = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Export to Pro', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Export to Pro', $this->shared->get( 'text_domain' ) ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-export-to-pro',
			array( $this, 'me_display_menu_export_to_pro' )
		);

		$this->screen_id_help = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Help', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Help', $this->shared->get( 'text_domain' ) ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-help',
			array( $this, 'me_display_menu_help' )
		);

		$this->screen_id_pro_version = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Pro Version', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Pro Version', $this->shared->get( 'text_domain' ) ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-pro-version',
			array( $this, 'me_display_menu_pro_version' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Options', $this->shared->get( 'text_domain' ) ),
			esc_html__( 'Options', $this->shared->get( 'text_domain' ) ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);
	}

	/**
	 * Includes the tickers view.
	 */
	public function me_display_menu_tickers() {
		include_once 'view/tickers.php';
	}

	/**
	 * Includes the featured view.
	 */
	public function me_display_menu_featured() {
		include_once 'view/featured.php';
	}

	/**
	 * Includes the sliding view.
	 */
	public function me_display_menu_sliding() {
		include_once 'view/sliding.php';
	}

	/**
	 * Includes the export to pro view.
	 */
	public function me_display_menu_export_to_pro() {
		include_once 'view/export_to_pro.php';
	}

	/**
	 * Includes the help view.
	 */
	public function me_display_menu_help() {
		include_once 'view/help.php';
	}

	/**
	 * Includes the pro version view.
	 */
	public function me_display_menu_pro_version() {
		include_once 'view/pro_version.php';
	}

	/**
	 * Includes the options view.
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	/**
	 * Register options.
	 */
	public function op_register_options() {

		// section general ----------------------------------------------------------.
		add_settings_section(
			'daextlnl_general_settings_section',
			null,
			null,
			'daextlnl_general_options'
		);

		add_settings_field(
			'detect_url_mode',
			esc_html__( 'Detect URL Mode', $this->shared->get( 'text_domain' ) ),
			array( $this, 'detect_url_mode_callback' ),
			'daextlnl_general_options',
			'daextlnl_general_settings_section'
		);

		register_setting(
			'daextlnl_general_options',
			'daextlnl_detect_url_mode',
			array( $this, 'detect_url_mode_validation' )
		);

		add_settings_field(
			'tickers_menu_capability',
			esc_html__( 'Tickers Menu Capability', $this->shared->get( 'text_domain' ) ),
			array( $this, 'tickers_menu_capability_callback' ),
			'daextlnl_general_options',
			'daextlnl_general_settings_section'
		);

		register_setting(
			'daextlnl_general_options',
			'daextlnl_tickers_menu_capability',
			array( $this, 'tickers_menu_capability_validation' )
		);

		add_settings_field(
			'featured_menu_capability',
			esc_html__( 'Featured News Menu Capability', $this->shared->get( 'text_domain' ) ),
			array( $this, 'featured_menu_capability_callback' ),
			'daextlnl_general_options',
			'daextlnl_general_settings_section'
		);

		register_setting(
			'daextlnl_general_options',
			'daextlnl_featured_menu_capability',
			array( $this, 'featured_menu_capability_validation' )
		);

		add_settings_field(
			'sliding_menu_capability',
			esc_html__( 'Sliding News Menu Capability', $this->shared->get( 'text_domain' ) ),
			array( $this, 'sliding_menu_capability_callback' ),
			'daextlnl_general_options',
			'daextlnl_general_settings_section'
		);

		register_setting(
			'daextlnl_general_options',
			'daextlnl_sliding_menu_capability',
			array( $this, 'sliding_menu_capability_validation' )
		);
	}


	public function detect_url_mode_callback( $args ) {

		$html  = '<select id="daextlnl-detect-url-mode" name="daextlnl_detect_url_mode" class="daext-display-none">';
		$html .= '<option ' . selected( get_option( 'daextlnl_detect_url_mode' ), 'server_variable', false ) . ' value="server_variable">' . esc_attr__( 'Server Variable', $this->shared->get( 'text_domain' ) ) . '</option>';
		$html .= '<option ' . selected( get_option( 'daextlnl_detect_url_mode' ), 'wp_request', false ) . ' value="wp_request">' . esc_attr__( 'WP Request', $this->shared->get( 'text_domain' ) ) . '</option>';
		$html .= '</select>';
		$html .= '<div class="help-icon" title="' . esc_attr__( 'Select the method used to detect the URL of the page.', $this->shared->get( 'text_domain' ) ) . '"></div>';

		echo $html;
	}

	public function detect_url_mode_validation( $input ) {

		if ( $input === 'server_variable' or $input === 'wp_request' ) {
			$output = $input;
		} else {
			$output = 'server_variable';
		}

		return $output;
	}

	public function tickers_menu_capability_callback( $args ) {

		$html  = '<input autocomplete="off" type="text" id="daextlnl-tickers-menu-capability" name="daextlnl_tickers_menu_capability" class="regular-text" value="' . esc_attr( get_option( 'daextlnl_tickers_menu_capability' ) ) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__( 'The capability required to get access on the "News Tickers" menu.', $this->shared->get( 'text_domain' ) ) . '"></div>';

		echo $html;
	}

	public function tickers_menu_capability_validation( $input ) {

		return sanitize_key( $input );
	}

	public function featured_menu_capability_callback( $args ) {

		$html  = '<input autocomplete="off" type="text" id="daextlnl-featured-menu-capability" name="daextlnl_featured_menu_capability" class="regular-text" value="' . esc_attr( get_option( 'daextlnl_featured_menu_capability' ) ) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__( 'The capability required to get access on the "Featured News" menu.', $this->shared->get( 'text_domain' ) ) . '"></div>';

		echo $html;
	}

	public function featured_menu_capability_validation( $input ) {

		return sanitize_key( $input );
	}

	public function sliding_menu_capability_callback( $args ) {

		$html  = '<input autocomplete="off" type="text" id="daextlnl-sliding-menu-capability" name="daextlnl_sliding_menu_capability" class="regular-text" value="' . esc_attr( get_option( 'daextlnl_sliding_menu_capability' ) ) . '" />';
		$html .= '<div class="help-icon" title="' . esc_attr__( 'The capability required to get access on the "Sliding News" menu.', $this->shared->get( 'text_domain' ) ) . '"></div>';

		echo $html;
	}

	public function sliding_menu_capability_validation( $input ) {

		return sanitize_key( $input );
	}

	/**
	 * Echo all the dismissible notices based on the values of the $notices array.
	 *
	 * @param $notices
	 */
	public function dismissible_notice( $notices ) {

		foreach ( $notices as $key => $notice ) {
			echo '<div class="' . esc_attr( $notice['class'] ) . ' settings-error notice is-dismissible below-h2"><p>' . esc_html( $notice['message'] ) . '</p></div>';
		}
	}

	/*
	 * The click on the "Export" button available in the "Export to Pro" menu is intercepted and the method that
	 * generates the downloadable XML file is called.
	 */
	public function export_xml_controller() {

		/*
		 * Intercept requests that come from the "Export" button of the "Export" menu and generate the downloadable XML
		 * file.
		 */
		if ( isset( $_POST['daextlnl_export'] ) ) {

			// verify capability
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
			}

			// generate the header of the XML file
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/xml; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=live-news-' . time() . '.xml' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// generate initial part of the XML file
			$out  = '<?xml version="1.0" encoding="UTF-8" ?>';
			$out .= '<root>';

			// Generate the XML of the various db tables
			$out .= $this->shared->convert_db_table_to_xml( 'tickers', 'id' );
			$out .= $this->shared->convert_db_table_to_xml( 'featured_news', 'id' );
			$out .= $this->shared->convert_db_table_to_xml( 'sliding_news', 'id' );

			// generate the final part of the XML file
			$out .= '</root>';

			echo $out;
			die();

		}
	}


	/**
	 * Change the WordPress footer text on all the plugin menus.
	 */
	public function change_footer_text() {

		$screen = get_current_screen();

		if ( $screen->id == $this->screen_id_tickers or
			$screen->id == $this->screen_id_featured or
			$screen->id == $this->screen_id_sliding or
			$screen->id == $this->screen_id_export_to_pro or
			$screen->id == $this->screen_id_help or
			$screen->id == $this->screen_id_pro_version or
			$screen->id == $this->screen_id_options ) {

			echo '<a target="_blank" href="http://wordpress.org/support/plugin/live-news-lite#postform">' . esc_attr__(
				'Contact Support',
				$this->shared->get( 'text_domain' )
			) . '</a> | ' .
				'<a target="_blank" href="https://translate.wordpress.org/projects/wp-plugins/live-news-lite/">' . esc_attr__(
					'Translate',
					$this->shared->get( 'text_domain' )
				) . '</a> | ' .
				str_replace(
					array( '[stars]', '[wp.org]' ),
					array(
						'<a target="_blank" href="https://wordpress.org/support/plugin/live-news-lite/reviews/?filter=5">&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
						'<a target="_blank" href="http://wordpress.org/plugins/live-news-lite/" >wordpress.org</a>',
					),
					__( 'Add your [stars] on [wp.org] to spread the love.', $this->shared->get( 'text_domain' ) )
				);

		}
	}
}
