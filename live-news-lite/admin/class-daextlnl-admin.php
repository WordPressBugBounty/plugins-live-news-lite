<?php
/**
 * This file includes the class Daextlnl_Admin.
 *
 * @package live-news-lite
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextlnl_Admin {

	/**
	 * Instance of the singleton class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Store an instance of the shared class.
	 *
	 * @var Daextlnl_Shared|null
	 */
	private $shared = null;

	/**
	 * Screen id for the tickers page.
	 *
	 * @var null
	 */
	private $screen_id_tickers = null;

	/**
	 * Screen id for the featured page.
	 *
	 * @var null
	 */
	private $screen_id_featured_news = null;

	/**
	 * Screen id for the sliding page.
	 *
	 * @var null
	 */
	private $screen_id_sliding_news = null;

	/**
	 * Screen id for the tools page.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

	/**
	 * Screen id for the maintenance page.
	 *
	 * @var null
	 */
	private $screen_id_maintenance = null;

	/**
	 * Screen id for the options page.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Construct.
	 */
	private function __construct() {

		// Assign an instance of the shared class.
		$this->shared = Daextlnl_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Tickers', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-tickers' ),
							'icon'      => 'notification-text',
							'menu_slug' => 'daextlnl-ticker',
						),
						array(
							'link_text' => __( 'Featured News', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-featured-news' ),
							'icon'      => 'award-02',
							'menu_slug' => 'daextlnl-featured-news',
						),
						array(
							'link_text' => __( 'Sliding News', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-sliding-news' ),
							'icon'      => 'passcode',
							'menu_slug' => 'daextlnl-sliding-news',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'Tools', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-tools' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Maintenance', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-maintenance' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Options', 'live-news-lite'),
							'link_url'  => admin_url( 'admin.php?page=daextlnl-options' ),
							'pro_badge' => false,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daextlnl-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'daextlnl-tickers' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-tickers-menu-elements.php';
				$this->menu_elements = new Daextlnl_Tickers_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextlnl-featured-news' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-featured-news-menu-elements.php';
				$this->menu_elements = new Daextlnl_Featured_News_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextlnl-sliding-news' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-sliding-news-menu-elements.php';
				$this->menu_elements = new Daextlnl_Sliding_News_Menu_Elements( $this->shared, $page_query_param, $config );
			}

			if ( 'daextlnl-tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-tools-menu-elements.php';
				$this->menu_elements = new Daextlnl_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextlnl-maintenance' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-maintenance-menu-elements.php';
				$this->menu_elements = new Daextlnl_Maintenance_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextlnl-options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextlnl-options-menu-elements.php';
				$this->menu_elements = new Daextlnl_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}
	}

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
	 * Enqueue admin-specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu tickers.
		if ( $screen->id === $this->screen_id_tickers ) {

			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu featured.
		if ( $screen->id === $this->screen_id_featured_news ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu sliding.
		if ( $screen->id === $this->screen_id_sliding_news ) {

			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific javascript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$wp_localize_script_data = array(
			'deleteText' => esc_html__( 'Delete', 'live-news-lite'),
			'cancelText' => esc_html__( 'Cancel', 'live-news-lite'),
		);

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu tickers.
		if ( $screen->id === $this->screen_id_tickers ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-tickers', $this->shared->get( 'url' ) . 'admin/assets/js/menu-tickers.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'wp-color-picker' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-tickers', 'objectL10n', $wp_localize_script_data );

			wp_enqueue_media();
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-media-uploader', $this->shared->get( 'url' ) . 'admin/assets/js/media-uploader.js', 'jquery', $this->shared->get( 'ver' ), true );

		}

		// Menu featured.
		if ( $screen->id === $this->screen_id_featured_news ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-featured', $this->shared->get( 'url' ) . 'admin/assets/js/menu-featured.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-featured', 'objectL10n', $wp_localize_script_data );

		}

		// Menu sliding.
		if ( $screen->id === $this->screen_id_sliding_news ) {

			// Store the JavaScript parameters in the window.DAEXTDAEXTLNL_PARAMETERS object.
			$initialization_script  = 'window.DAEXTLNL_PARAMETERS = {';
			$initialization_script .= 'ajaxUrl: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'nonce: "' . wp_create_nonce( 'live-news-lite' ) . '"';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-sliding', $this->shared->get( 'url' ) . 'admin/assets/js/menu-sliding.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'wp-color-picker' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-sliding', 'objectL10n', $wp_localize_script_data );

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-sliding', $initialization_script, 'before' );

			wp_enqueue_media();
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-media-uploader', $this->shared->get( 'url' ) . 'admin/assets/js/media-uploader.js', 'jquery', $this->shared->get( 'ver' ), true );

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Maintenance.
		if ( $screen->id === $this->screen_id_maintenance ) {

			// Select2.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			// Maintenance Menu.
			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-maintenance',
				$this->shared->get( 'url' ) . 'admin/assets/js/menu-maintenance.js',
				array( 'jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2' ),
				$this->shared->get( 'ver' ),
				true
			);
			wp_localize_script(
				$this->shared->get( 'slug' ) . '-menu-maintenance',
				'objectL10n',
				$wp_localize_script_data
			);

		}

		// Menu options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTDAEXTLNL_PARAMETERS object.
			$initialization_script  = 'window.DAEXTLNL_PARAMETERS = {';
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $this->shared->menu_options_configuration() );
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public static function ac_activate( $networkwide ) {

		/**
		 * Create options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// If this is a "Network Activation" create the options and tables for each blog.
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// Iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// Switch to the iterated blog.
					switch_to_blog( $blog_id );

					// Create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {
				/*
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {
			/*
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
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		// If the plugin is "Network Active" create the options and tables for this new blog.
		if ( is_plugin_active_for_network( 'uberchart/init.php' ) ) {

			// Get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// Switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// Create options and database tables for the new blog.
			self::ac_initialize_options();
			self::ac_create_database_tables();

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// Get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// Switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// Create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// Switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 *
	 * @return void
	 */
	public static function ac_initialize_options() {

		if ( intval( get_option( 'daextlnl_options_version' ), 10 ) < 1 ) {

			// Assign an instance of Daextlnl_Shared.
			$shared = Daextlnl_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextlnl_options_version', '1' );

		}
	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		// Assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// Check database version and create the database.
		if ( intval( get_option( $shared->get( 'slug' ) . '_database_version' ), 10 ) < 2 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Create *prefix*_daextlnl_tickers.
			global $wpdb;
			$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_tickers';
			$sql        = "CREATE TABLE $table_name (
                  `name` varchar(100) NOT NULL DEFAULT '',
                  `description` TEXT NOT NULL DEFAULT '',
                  `id` BIGINT UNSIGNED AUTO_INCREMENT,
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
                  `url_mode` tinyint(1) DEFAULT '0',
                  PRIMARY KEY  (id)
            ) $charset_collate";

			dbDelta( $sql );

			// Create *prefix*_daextlnl_featured_news.
			global $wpdb;
			$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_featured_news';
			$sql        = "CREATE TABLE $table_name (
                  `id` BIGINT UNSIGNED AUTO_INCREMENT,
                  `news_title` varchar(1000) NOT NULL DEFAULT '',
                  `news_excerpt` varchar(1000) NOT NULL DEFAULT '',
                  `url` varchar(2083) NOT NULL DEFAULT '',
                  `ticker_id` bigint(20) NOT NULL,
                  PRIMARY KEY  (id)
            ) $charset_collate";

			dbDelta( $sql );

			// Create *prefix*_daextlnl_sliding_news.
			global $wpdb;
			$table_name = $wpdb->prefix . $shared->get( 'slug' ) . '_sliding_news';
			$sql        = "CREATE TABLE $table_name (
                  `id` BIGINT UNSIGNED AUTO_INCREMENT,
                  `news_title` varchar(1000) NOT NULL DEFAULT '',
                  `url` varchar(2083) NOT NULL DEFAULT '',
                  `ticker_id` bigint(20) NOT NULL,
                  `text_color` varchar(7) DEFAULT NULL,
                  `text_color_hover` varchar(7) DEFAULT NULL,
                  `background_color` varchar(7) DEFAULT NULL,
                  `background_color_opacity` float DEFAULT NULL,
                  `image_before` varchar(2083) NOT NULL DEFAULT '',
                  `image_after` varchar(2083) NOT NULL DEFAULT '',
                  PRIMARY KEY  (id)
            ) $charset_collate";

			dbDelta( $sql );

			// Update database version.
			update_option( $shared->get( 'slug' ) . '_database_version', '2' );

		}
	}

	/**
	 * Plugin delete.
	 *
	 * @return void
	 */
	public static function un_delete() {
		/*
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// Get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// Create an array with all the blog ids.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// Iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// Switch to the iterated blog.
				switch_to_blog( $blog_id );

				// Create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		} else {
			/*
			 * if this is not a multisite installation delete options and
			 * tables only for the current blog
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	public static function un_delete_options() {

		// Assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 *
	 * @return void
	 */
	public static function un_delete_database_tables() {

		// Assign an instance of Daextlnl_Shared.
		$shared = Daextlnl_Shared::get_instance();

		global $wpdb;

		// Delete transients associated with the table prefix '_tickers'.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}daextlnl_tickers", ARRAY_A );
		foreach ( $results as $result ) {
			delete_transient( 'daextlnl_ticker_' . $result['id'] );
		}

		// Delete table prefix + '_tickers'.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextlnl_tickers" );

		// Delete table prefix + '_featured_news'.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextlnl_featured_news" );

		// Delete table prefix + '_sliding_news'.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextlnl_sliding_news" );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		$icon_svg = '
		<svg id="globe" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 40 40">
		  <defs>
		    <style>
		      .cls-1 {
		        fill: #fff;
		        stroke-width: 0;
		      }
		    </style>
		  </defs>
		  <path class="cls-1" d="M38,20c0-9.4-7.3-17.2-16.5-17.9-.5,0-1,0-1.5,0s-1,0-1.5,0C9.3,2.8,2,10.6,2,20s7.3,17.2,16.5,17.9c.5,0,1,0,1.5,0s1,0,1.5,0c9.2-.8,16.5-8.5,16.5-17.9ZM30,19c-.1-2.7-.7-5.2-1.6-7.6,1.3-.5,2.6-1.1,3.8-1.9,2.2,2.6,3.6,5.8,3.9,9.4h-6ZM21,4.4c1.8,1.7,3.4,3.6,4.6,5.8-1.5.4-3,.7-4.6.7v-6.6ZM19,11c-1.6,0-3.1-.3-4.6-.7,1.2-2.2,2.7-4.2,4.6-5.8v6.6ZM19,13v6h-7c.1-2.4.6-4.8,1.5-6.9,1.7.5,3.6.8,5.4.9ZM19,21v6c-1.9,0-3.7.4-5.4.9-.9-2.2-1.4-4.5-1.5-6.9h7ZM19,29v6.6c-1.8-1.7-3.4-3.6-4.6-5.8,1.5-.4,3-.7,4.6-.7ZM21,29c1.6,0,3.1.3,4.6.7-1.2,2.2-2.7,4.2-4.6,5.8v-6.6ZM21,27v-6h7c-.1,2.4-.6,4.8-1.5,6.9-1.7-.5-3.6-.8-5.4-.9ZM21,19v-6c1.9,0,3.7-.4,5.4-.9.9,2.2,1.4,4.5,1.5,6.9h-7ZM27.5,9.6c-.9-1.8-2.1-3.5-3.5-5.1,2.5.6,4.8,1.9,6.6,3.5-1,.6-2,1.1-3.1,1.5ZM12.5,9.6c-1.1-.4-2.1-.9-3.1-1.5,1.9-1.7,4.1-2.9,6.6-3.5-1.4,1.5-2.6,3.2-3.5,5.1ZM11.7,11.4c-.9,2.4-1.5,4.9-1.6,7.6h-6c.2-3.6,1.6-6.9,3.9-9.4,1.2.7,2.4,1.4,3.8,1.9ZM10,21c.1,2.7.7,5.2,1.6,7.6-1.3.5-2.6,1.1-3.8,1.9-2.2-2.6-3.6-5.8-3.9-9.4h6ZM12.5,30.4c.9,1.8,2.1,3.5,3.5,5.1-2.5-.6-4.8-1.9-6.6-3.5,1-.6,2-1.1,3.1-1.5ZM27.5,30.4c1.1.4,2.1.9,3.1,1.5-1.9,1.7-4.1,2.9-6.6,3.5,1.4-1.5,2.6-3.2,3.5-5.1ZM28.3,28.6c.9-2.4,1.5-4.9,1.6-7.6h6c-.2,3.6-1.6,6.9-3.9,9.4-1.2-.7-2.4-1.4-3.8-1.9Z"/>
		</svg>';

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Base64 encoding is used to embed the SVG in the HTML.
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( $icon_svg );

		add_menu_page(
			'LN',
			esc_attr__( 'Live News', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_tickers_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tickers',
			array( $this, 'me_display_menu_tickers' ),
			$icon_svg
		);

		$this->screen_id_tickers = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Tickers', 'live-news-lite'),
			esc_attr__( 'Tickers', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_tickers_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tickers',
			array( $this, 'me_display_menu_tickers' )
		);

		$this->screen_id_featured_news = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Featured News', 'live-news-lite'),
			esc_attr__( 'Featured News', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_featured_news_menu_capability' ),
			$this->shared->get( 'slug' ) . '-featured-news',
			array( $this, 'me_display_menu_featured_news' )
		);

		$this->screen_id_sliding_news = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Sliding News', 'live-news-lite'),
			esc_attr__( 'Sliding News', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_sliding_news_menu_capability' ),
			$this->shared->get( 'slug' ) . '-sliding-news',
			array( $this, 'me_display_menu_sliding_news' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Tools', 'live-news-lite'),
			esc_attr__( 'Tools', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_tools_menu_capability' ),
			$this->shared->get( 'slug' ) . '-tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_maintenance = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Maintenance', 'live-news-lite'),
			esc_attr__( 'Maintenance', 'live-news-lite'),
			get_option( $this->shared->get( 'slug' ) . '_maintenance_menu_capability' ),
			$this->shared->get( 'slug' ) . '-maintenance',
			array( $this, 'me_display_menu_maintenance' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_attr__( 'Options', 'live-news-lite'),
			esc_attr__( 'Options', 'live-news-lite'),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '-tickers',
			esc_html__( 'Help & Support', 'live-news-lite'),
			esc_html__( 'Help & Support', 'live-news-lite') . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/doc/live-news/',
		);
	}

	/**
	 * Includes the tickers view.
	 *
	 * @return void
	 */
	public function me_display_menu_tickers() {
		include_once 'view/tickers.php';
	}

	/**
	 * Includes the featured view.
	 *
	 * @return void
	 */
	public function me_display_menu_featured_news() {
		include_once 'view/featured-news.php';
	}

	/**
	 * Includes the sliding view.
	 *
	 * @return void
	 */
	public function me_display_menu_sliding_news() {
		include_once 'view/sliding-news.php';
	}

	/**
	 * Includes the tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
	}

	/**
	 * Includes the maintenance view.
	 *
	 * @return void
	 */
	public function me_display_menu_maintenance() {
		include_once 'view/maintenance.php';
	}

	/**
	 * Includes the options view.
	 *
	 * @return void
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

}
