<?php
/**
 * Class used to implement the back-end functionalities of the "Tickers" menu.
 *
 * @package live-news-lite
 */

/**
 * Class used to implement the back-end functionalities of the "Tickers" menu.
 */
class Daextlnl_Tickers_Menu_Elements extends Daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param Daextlnl_Shared $shared An instance of the shared class.
	 * @param string      $page_query_param The query parameter used to identify the current page.
	 * @param array       $config The configuration array.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'ticker';
		$this->slug_plural        = 'tickers';
		$this->label_singular     = __( 'Ticker', 'live-news-lite');
		$this->label_plural       = __( 'Tickers', 'live-news-lite');
		$this->primary_key        = 'id';
		$this->db_table           = 'tickers';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => __( 'Name', 'live-news-lite'),
			),
			array(
				'db_field' => 'description',
				'label'    => __( 'Description', 'live-news-lite'),
			),
		);
		$this->searchable_fields  = array(
			'name',
			'description',
		);

		$this->default_values = array(
			'name'                                   => '',
			'description'                            => '',
			'target'                                 => '1',
			'url'                                    => '',
			'enable_ticker'                          => '1',

			// Source.
			'clock_source'                           => '2',
			'clock_offset'                           => '0',
			'clock_format'                           => 'HH:mm',

			// Behavior.
			'enable_rtl_layout'                      => '0',
			'enable_with_mobile_devices'             => '0',
			'hide_featured_news'                     => '1',
			'open_news_as_default'                   => '1',
			'enable_links'                           => '1',
			'open_links_new_tab'                     => '0',
			'hide_clock'                             => '0',
			'clock_autoupdate'                       => '1',
			'clock_autoupdate_time'                  => '10',
			'number_of_sliding_news'                 => '10',

			// Performance.
			'cached_cycles'                          => '5',
			'transient_expiration'                   => '10',

			// Style.
			'featured_title_maximum_length'          => '280',
			'featured_excerpt_maximum_length'        => '280',
			'sliding_news_maximum_length'            => '280',
			'featured_title_font_size'               => '38',
			'featured_excerpt_font_size'             => '28',
			'sliding_news_font_size'                 => '28',
			'clock_font_size'                        => '28',
			'sliding_news_margin'                    => '84',
			'sliding_news_padding'                   => '28',
			'font_family'                            => "'Open Sans', sans-serif",
			'google_font'                            => 'https://fonts.googleapis.com/css?family=Open+Sans:400,600,700',
			'featured_news_title_color'              => '#eee',
			'featured_news_title_color_hover'        => '#111',
			'featured_news_excerpt_color'            => '#eee',
			'sliding_news_color'                     => '#eee',
			'sliding_news_color_hover'               => '#aaa',
			'clock_text_color'                       => '#111',
			'featured_news_background_color'         => '#C90016',
			'featured_news_background_color_opacity' => '1',
			'sliding_news_background_color'          => '#000000',
			'sliding_news_background_color_opacity'  => '1',
			'open_button_image'                      => esc_url( $this->shared->get( 'url' ) . 'public/assets/img/open-button.svg' ),
			'close_button_image'                     => esc_url( $this->shared->get( 'url' ) . 'public/assets/img/close-button.svg' ),
			'clock_background_image'                 => esc_url( $this->shared->get( 'url' ) . 'public/assets/img/clock.svg' ),

			// Advanced.
			'url_mode'                               => '0',

		);
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		if ( isset( $_POST['update_id'] ) ||
			isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_create_update_' . $this->menu_slug, 'daextlnl_create_update_' . $this->menu_slug . '_nonce' );

		}

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization -------------------------------------------------------------------------------------------------------.

		$data = array();

		// Actions.
		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		// Sanitization.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Main.
			$data['name']          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;
			$data['description']   = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;
			$data['target']        = isset( $_POST['target'] ) ? intval( wp_unslash( $_POST['target'] ), 10 ) : null;
			$data['url']           = isset( $_POST['url'] ) ? sanitize_textarea_field( wp_unslash( $_POST['url'] ) ) : null;
			$data['enable_ticker'] = isset( $_POST['enable_ticker'] ) ? 1 : 0;

			// Source.
			$data['clock_source'] = isset( $_POST['clock_source'] ) ? intval( $_POST['clock_source'], 10 ) : null;
			$data['clock_offset'] = isset( $_POST['clock_offset'] ) ? intval( $_POST['clock_offset'], 10 ) : null;
			$data['clock_format'] = isset( $_POST['clock_format'] ) ? sanitize_text_field( wp_unslash( $_POST['clock_format'] ) ) : null;

			// Behavior.
			$data['enable_rtl_layout']          = isset( $_POST['enable_rtl_layout'] ) ? 1 : 0;
			$data['enable_with_mobile_devices'] = isset( $_POST['enable_with_mobile_devices'] ) ? 1 : 0;
			$data['hide_featured_news']         = isset( $_POST['hide_featured_news'] ) ? intval( $_POST['hide_featured_news'], 10 ) : null;
			$data['open_news_as_default']       = isset( $_POST['open_news_as_default'] ) ? 1 : 0;
			$data['enable_links']               = isset( $_POST['enable_links'] ) ? 1 : 0;
			$data['open_links_new_tab']         = isset( $_POST['open_links_new_tab'] ) ? 1 : 0;
			$data['hide_clock']                 = isset( $_POST['hide_clock'] ) ? 1 : 0;
			$data['clock_autoupdate']           = isset( $_POST['clock_autoupdate'] ) ? 1 : 0;
			$data['clock_autoupdate_time']      = isset( $_POST['clock_autoupdate_time'] ) ? intval( $_POST['clock_autoupdate_time'], 10 ) : null;
			$data['number_of_sliding_news']     = isset( $_POST['number_of_sliding_news'] ) ? intval( $_POST['number_of_sliding_news'], 10 ) : null;
			// Performance.
			$data['cached_cycles']        = isset( $_POST['cached_cycles'] ) ? intval( $_POST['cached_cycles'], 10 ) : null;
			$data['transient_expiration'] = isset( $_POST['transient_expiration'] ) ? intval( $_POST['transient_expiration'], 10 ) : null;

			// Style.
			$data['featured_title_maximum_length']          = isset( $_POST['featured_title_maximum_length'] ) ? intval( $_POST['featured_title_maximum_length'], 10 ) : null;
			$data['featured_excerpt_maximum_length']        = isset( $_POST['featured_excerpt_maximum_length'] ) ? intval( $_POST['featured_excerpt_maximum_length'], 10 ) : null;
			$data['sliding_news_maximum_length']            = isset( $_POST['sliding_news_maximum_length'] ) ? intval( $_POST['sliding_news_maximum_length'], 10 ) : null;
			$data['featured_title_font_size']               = isset( $_POST['featured_title_font_size'] ) ? intval( $_POST['featured_title_font_size'], 10 ) : null;
			$data['featured_excerpt_font_size']             = isset( $_POST['featured_excerpt_font_size'] ) ? intval( $_POST['featured_excerpt_font_size'], 10 ) : null;
			$data['sliding_news_font_size']                 = isset( $_POST['sliding_news_font_size'] ) ? intval( $_POST['sliding_news_font_size'], 10 ) : null;
			$data['clock_font_size']                        = isset( $_POST['clock_font_size'] ) ? intval( $_POST['clock_font_size'], 10 ) : null;
			$data['sliding_news_margin']                    = isset( $_POST['sliding_news_margin'] ) ? intval( $_POST['sliding_news_margin'], 10 ) : null;
			$data['sliding_news_padding']                   = isset( $_POST['sliding_news_padding'] ) ? intval( $_POST['sliding_news_padding'], 10 ) : null;
			$data['font_family']                            = isset( $_POST['font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['font_family'] ) ) : null;
			$data['google_font']                            = isset( $_POST['google_font'] ) ? sanitize_text_field( wp_unslash( $_POST['google_font'] ) ) : null;
			$data['featured_news_title_color']              = isset( $_POST['featured_news_title_color'] ) ? sanitize_text_field( wp_unslash( $_POST['featured_news_title_color'] ) ) : null;
			$data['featured_news_title_color_hover']        = isset( $_POST['featured_news_title_color_hover'] ) ? sanitize_text_field( wp_unslash( $_POST['featured_news_title_color_hover'] ) ) : null;
			$data['featured_news_excerpt_color']            = isset( $_POST['featured_news_excerpt_color'] ) ? sanitize_text_field( wp_unslash( $_POST['featured_news_excerpt_color'] ) ) : null;
			$data['sliding_news_color']                     = isset( $_POST['sliding_news_color'] ) ? sanitize_text_field( wp_unslash( $_POST['sliding_news_color'] ) ) : null;
			$data['sliding_news_color_hover']               = isset( $_POST['sliding_news_color_hover'] ) ? sanitize_text_field( wp_unslash( $_POST['sliding_news_color_hover'] ) ) : null;
			$data['clock_text_color']                       = isset( $_POST['clock_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['clock_text_color'] ) ) : null;
			$data['featured_news_background_color']         = isset( $_POST['featured_news_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['featured_news_background_color'] ) ) : null;
			$data['featured_news_background_color_opacity'] = isset( $_POST['featured_news_background_color_opacity'] ) ? floatval( $_POST['featured_news_background_color_opacity'] ) : null;
			$data['sliding_news_background_color']          = isset( $_POST['sliding_news_background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['sliding_news_background_color'] ) ) : null;
			$data['sliding_news_background_color_opacity']  = isset( $_POST['sliding_news_background_color_opacity'] ) ? floatval( $_POST['sliding_news_background_color_opacity'] ) : null;
			$data['open_button_image']                      = isset( $_POST['open_button_image'] ) ? esc_url_raw( wp_unslash( $_POST['open_button_image'] ) ) : null;
			$data['close_button_image']                     = isset( $_POST['close_button_image'] ) ? esc_url_raw( wp_unslash( $_POST['close_button_image'] ) ) : null;
			$data['clock_background_image']                 = isset( $_POST['clock_background_image'] ) ? esc_url_raw( wp_unslash( $_POST['clock_background_image'] ) ) : null;

			// Advanced.
			$data['url_mode'] = isset( $_POST['url_mode'] ) ? 1 : 0;

		}

		// Validation.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Validation -----------------------------------------------------------------------------------------------------.

			// validation on "Name".
			if ( mb_strlen( trim( $data['name'] ) ) === 0 || mb_strlen( $data['name'] ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Name" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// validation on "Description".
			if ( mb_strlen( trim( $data['description'] ) ) === 0 || mb_strlen( $data['description'] ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Description" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured Title Maximum Length".
			if ( intval( $data['featured_title_maximum_length'], 10 ) < 1 || intval( $data['featured_title_maximum_length'], 10 ) > 1000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 1000 in the "Featured Title Maximum Length" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured Excerpt Maximum Length".
			if ( intval( $data['featured_excerpt_maximum_length'], 10 ) < 1 || intval( $data['featured_excerpt_maximum_length'], 10 ) > 1000 ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a value included between 1 and 1000 in the "Featured Excerpt Maximum Length" field.', 'live-news-lite'),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// Validation on "Sliding News Maximum Length".
			if ( intval( $data['sliding_news_maximum_length'], 10 ) < 1 || intval( $data['sliding_news_maximum_length'], 10 ) > 1000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 1000 in the "Sliding News Maximum Length" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Title Font Size".
			if ( intval( $data['featured_title_font_size'], 10 ) < 1 || intval( $data['featured_title_font_size'], 10 ) > 38 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 38 in the "Featured News Title Font Size" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Excerpt Font Size".
			if ( intval( $data['featured_excerpt_font_size'], 10 ) < 1 || intval( $data['featured_excerpt_font_size'], 10 ) > 28 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 28 in the "Featured News Excerpt Font Size" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Font Size".
			if ( intval( $data['sliding_news_font_size'], 10 ) < 1 || intval( $data['sliding_news_font_size'], 10 ) > 28 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 28 in the "Sliding News Font Size" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Clock Font Size".
			if ( intval( $data['clock_font_size'], 10 ) < 1 || intval( $data['clock_font_size'], 10 ) > 28 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 1 and 28 in the "Clock Font Size" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Margin".
			if ( intval( $data['sliding_news_margin'], 10 ) < 0 || intval( $data['sliding_news_margin'], 10 ) > 999 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 999 in the "Sliding News Margin" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Padding".
			if ( intval( $data['sliding_news_padding'], 10 ) < 0 || intval( $data['sliding_news_padding'], 10 ) > 999 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 999 in the "Sliding News Padding" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Cached Cycles".
			if ( intval( $data['cached_cycles'], 10 ) < 0 || intval( $data['cached_cycles'], 10 ) > 1000000000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 1000000000 in the "Cached Cycles" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Transient Expiration".
			if ( intval( $data['transient_expiration'], 10 ) < 0 || intval( $data['transient_expiration'], 10 ) > 1000000000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 1000000000 in the "Transient Expiration" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Background Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['featured_news_background_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Featured News Background Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Background Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['sliding_news_background_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Sliding News Background Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Background Color Opacity".
			if ( $data['sliding_news_background_color_opacity'] < 0 || $data['sliding_news_background_color_opacity'] > 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 1 in the "Sliding News Background Color Opacity" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Font Family".
			if ( ! preg_match( $this->shared->font_family_regex, stripslashes( $data['font_family'] ) ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Font Family" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Gooogle Font".
			if ( mb_strlen( trim( $data['google_font'] ) ) > 0 && ! preg_match( $this->shared->url_regex, trim( $data['google_font'] ) ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Google Font" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Open Button Image".
			if ( ! preg_match( $this->shared->url_regex, trim( $data['open_button_image'] ) ) || mb_strlen( $data['open_button_image'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "Open Button Image" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Close Button Image".
			if ( ! preg_match( $this->shared->url_regex, trim( $data['close_button_image'] ) ) || mb_strlen( $data['close_button_image'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "Close Button Image" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Clock Background Image".
			if ( ! preg_match( $this->shared->url_regex, trim( $data['clock_background_image'] ) ) || mb_strlen( $data['clock_background_image'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "Clock Background Image" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Title Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['featured_news_title_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Featured News Title Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Title Color Hover".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['featured_news_title_color_hover'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Featured News Title Color Hover" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Excerpt Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['featured_news_excerpt_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Featured News Excerpt Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['sliding_news_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Sliding News Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Sliding News Color Hover".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['sliding_news_color_hover'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Sliding News Color Hover" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Clock Text Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['clock_text_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Clock Text Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Featured News Background Color Opacity".
			if ( $data['featured_news_background_color_opacity'] < 0 || $data['featured_news_background_color_opacity'] > 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 1 in the "Featured News Background Color Opacity" field..', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Do not save (and leave an error message) if a ticker with "Website" as a target already exists.
			if ( 1 === $data['target'] ) {

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$row_obj = $wpdb->get_row( "SELECT * from {$wpdb->prefix}daextlnl_tickers WHERE target = 1" );
				if ( null !== $row_obj ) {

					if ( is_null( $data['update_id'] ) || ( ! is_null( $data['update_id'] ) && intval( $row_obj->id, 10 ) !== $data['update_id'] ) ) {
						$this->shared->save_dismissible_notice(
							__( 'A news ticker with "Website" as a target already exists.', 'live-news-lite'),
							'error'
						);
						$invalid_data = true;
					}
				}
			}
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextlnl_tickers SET 
                name = %s,
                description = %s,
                target = %d,
                url = %s,
                open_links_new_tab = %d,
                clock_offset = %d,
                clock_format = %s,
                clock_source = %d,
                clock_autoupdate = %d,
                clock_autoupdate_time = %d,
                number_of_sliding_news = %d,
                featured_title_maximum_length = %d,
                featured_excerpt_maximum_length = %d,
                sliding_news_maximum_length = %d,
                open_news_as_default = %d,
                hide_featured_news = %d,
                hide_clock = %d,
                enable_rtl_layout = %d,
                cached_cycles = %d,
                featured_news_background_color = %s,
                sliding_news_background_color = %s,
                sliding_news_background_color_opacity = %f,
                font_family = %s,
                google_font = %s,
                featured_title_font_size = %d,
				featured_excerpt_font_size = %d,
				sliding_news_font_size = %d,
				clock_font_size = %d,
				sliding_news_margin = %d,
				sliding_news_padding = %d,
                enable_with_mobile_devices = %d,
                open_button_image = %s,
                close_button_image = %s,
                clock_background_image = %s,
                featured_news_title_color = %s,
                featured_news_title_color_hover = %s,
                featured_news_excerpt_color = %s,
                sliding_news_color = %s,
                sliding_news_color_hover = %s,
                clock_text_color = %s,
                featured_news_background_color_opacity = %s,
                enable_ticker = %d,
                enable_links = %d,
                transient_expiration = %d,
                url_mode = %d
                WHERE id = %d",
					$data['name'],
					$data['description'],
					$data['target'],
					$data['url'],
					$data['open_links_new_tab'],
					$data['clock_offset'],
					$data['clock_format'],
					$data['clock_source'],
					$data['clock_autoupdate'],
					$data['clock_autoupdate_time'],
					$data['number_of_sliding_news'],
					$data['featured_title_maximum_length'],
					$data['featured_excerpt_maximum_length'],
					$data['sliding_news_maximum_length'],
					$data['open_news_as_default'],
					$data['hide_featured_news'],
					$data['hide_clock'],
					$data['enable_rtl_layout'],
					$data['cached_cycles'],
					$data['featured_news_background_color'],
					$data['sliding_news_background_color'],
					$data['sliding_news_background_color_opacity'],
					$data['font_family'],
					$data['google_font'],
					$data['featured_title_font_size'],
					$data['featured_excerpt_font_size'],
					$data['sliding_news_font_size'],
					$data['clock_font_size'],
					$data['sliding_news_margin'],
					$data['sliding_news_padding'],
					$data['enable_with_mobile_devices'],
					$data['open_button_image'],
					$data['close_button_image'],
					$data['clock_background_image'],
					$data['featured_news_title_color'],
					$data['featured_news_title_color_hover'],
					$data['featured_news_excerpt_color'],
					$data['sliding_news_color'],
					$data['sliding_news_color_hover'],
					$data['clock_text_color'],
					$data['featured_news_background_color_opacity'],
					$data['enable_ticker'],
					$data['enable_links'],
					$data['transient_expiration'],
					$data['url_mode'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The news ticker has been successfully updated.', 'live-news-lite'),
					'updated'
				);
			}

			// Add record to database ------------------------------------------------------------------.
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

				// Insert into the database.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextlnl_tickers SET 
                    name = %s,
                    description = %s,
                    target = %d,
                                                  url = %s,
                open_links_new_tab = %d,
                clock_offset = %d,
                clock_format = %s,
                clock_source = %d,
                clock_autoupdate = %d,
                clock_autoupdate_time = %d,
                number_of_sliding_news = %d,
                featured_title_maximum_length = %d,
                featured_excerpt_maximum_length = %d,
                sliding_news_maximum_length = %d,
                open_news_as_default = %d,
                hide_featured_news = %d,
                hide_clock = %d,
                enable_rtl_layout = %d,
                cached_cycles = %d,
                featured_news_background_color = %s,
                sliding_news_background_color = %s,
                sliding_news_background_color_opacity = %f,
                font_family = %s,
                google_font = %s,
                featured_title_font_size = %d,
				featured_excerpt_font_size = %d,
				sliding_news_font_size = %d,
				clock_font_size = %d,
				sliding_news_margin = %d,
				sliding_news_padding = %d,
                enable_with_mobile_devices = %d,
                open_button_image = %s,
                close_button_image = %s,
                clock_background_image = %s,
                featured_news_title_color = %s,
                featured_news_title_color_hover = %s,
                featured_news_excerpt_color = %s,
                sliding_news_color = %s,
                sliding_news_color_hover = %s,
                clock_text_color = %s,
                featured_news_background_color_opacity = %s,
                enable_ticker = %d,
                enable_links = %d,
                transient_expiration = %d,
                url_mode = %d",
						$data['name'],
						$data['description'],
						$data['target'],
						$data['url'],
						$data['open_links_new_tab'],
						$data['clock_offset'],
						$data['clock_format'],
						$data['clock_source'],
						$data['clock_autoupdate'],
						$data['clock_autoupdate_time'],
						$data['number_of_sliding_news'],
						$data['featured_title_maximum_length'],
						$data['featured_excerpt_maximum_length'],
						$data['sliding_news_maximum_length'],
						$data['open_news_as_default'],
						$data['hide_featured_news'],
						$data['hide_clock'],
						$data['enable_rtl_layout'],
						$data['cached_cycles'],
						$data['featured_news_background_color'],
						$data['sliding_news_background_color'],
						$data['sliding_news_background_color_opacity'],
						$data['font_family'],
						$data['google_font'],
						$data['featured_title_font_size'],
						$data['featured_excerpt_font_size'],
						$data['sliding_news_font_size'],
						$data['clock_font_size'],
						$data['sliding_news_margin'],
						$data['sliding_news_padding'],
						$data['enable_with_mobile_devices'],
						$data['open_button_image'],
						$data['close_button_image'],
						$data['clock_background_image'],
						$data['featured_news_title_color'],
						$data['featured_news_title_color_hover'],
						$data['featured_news_excerpt_color'],
						$data['sliding_news_color'],
						$data['sliding_news_color_hover'],
						$data['clock_text_color'],
						$data['featured_news_background_color_opacity'],
						$data['enable_ticker'],
						$data['enable_links'],
						$data['transient_expiration'],
						$data['url_mode'],
					)
				);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The news ticker has been successfully added.', 'live-news-lite'),
					'updated'
				);
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param object $item_obj The item object.
	 *
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		$args            = array(
			'type' => 'post',
		);
		$categories      = get_categories( $args );
		$category_option = array(
			'0' => __( 'All', 'live-news-lite'),
		);
		foreach ( $categories as $category ) {
			$category_option[ $category->term_id ] = $category->name;
		}

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => __('Main', 'live-news-lite'),
				'section_id'     => 'main',
				'icon_id'        => 'dots-grid',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'name',
						'label'       => __( 'Name', 'live-news-lite'),
						'description' => __( 'Enter the name of the news ticker.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['name'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'description',
						'label'       => __( 'Description', 'live-news-lite'),
						'description' => __( 'Enter the description of the news ticker.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['description'] : null,
						'maxlength'   => 255,
						'required'    => true,
					),
				),
			),
			array(
				'label'          => __('Placement', 'live-news-lite'),
				'section_id'     => 'placement',
				'icon_id'        => 'marker-pin-04',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'toggle',
						'name'        => 'enable_ticker',
						'label'       => __( 'Enable News Ticker', 'live-news-lite'),
						'description' => __( 'Apply the news ticker on the site.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['enable_ticker'] : null,
						'required'    => true,
					),
					array(
						'type'        => 'select',
						'name'        => 'target',
						'label'       => __( 'Placement', 'live-news-lite'),
						'description' => __( 'Select where the news ticker should appear. You can choose to display it across the entire website or limit its visibility to specific URLs.', 'live-news-lite'),
						'options'     => array(
							'1' => __( 'Entire Website', 'live-news-lite'),
							'2' => __( 'Specific URLs', 'live-news-lite'),
						),
						'value'       => isset( $item_obj ) ? $item_obj['target'] : null,
						'required'    => true,
					),
					array(
						'type'        => 'textarea',
						'name'        => 'url',
						'label'       => __( 'Placement URLs', 'live-news-lite'),
						'description' => __( 'Enter one or more URLs. This option applies only when the news ticker is set to be displayed on specific URLs.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['url'] : null,
						'maxlength'   => 20830000,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'url_mode',
						'label'       => __( 'Placement URLs Mode', 'live-news-lite'),
						'description' => __( 'Select whether to include or exclude the URLs defined with the "Placement URLs" option.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['url_mode'] : null,
						'required'    => false,
					),
				),
			),

			array(
				'label'          => __('Layout', 'live-news-lite'),
				'section_id'     => 'behavior',
				'icon_id'        => 'layout-alt-01',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'toggle',
						'name'        => 'enable_rtl_layout',
						'label'       => __( 'Enable RTL Layout', 'live-news-lite'),
						'description' => __( 'Enable the RTL (right-to-left) layout.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['enable_rtl_layout'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'enable_with_mobile_devices',
						'label'       => __( 'Enable with Mobile Devices', 'live-news-lite'),
						'description' => __( 'Display the news ticker on mobile devices.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['enable_with_mobile_devices'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'select',
						'name'        => 'hide_featured_news',
						'label'       => __( 'Hide Featured News', 'live-news-lite'),
						'description' => __( 'Configure the presence of the featured news area of the news ticker.', 'live-news-lite'),
						'options'     => array(
							'1' => __( 'No', 'live-news-lite'),
							'2' => __( 'Yes', 'live-news-lite'),
							'3' => __( 'Only with Mobile Devices', 'live-news-lite'),
						),
						'value'       => isset( $item_obj ) ? $item_obj['hide_featured_news'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'open_news_as_default',
						'label'       => __( 'Open News as Default', 'live-news-lite'),
						'description' => __( 'Set the news ticker to be displayed in an open status by default.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['open_news_as_default'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'enable_links',
						'label'       => __( 'Enable Links', 'live-news-lite'),
						'description' => __( 'Apply the links associated with the news on the featured news title and on the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['enable_links'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'open_links_new_tab',
						'label'       => __( 'Open Links in New Tab', 'live-news-lite'),
						'description' => __( 'Open in a new tab the links available in the news ticker.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['open_links_new_tab'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'hide_clock',
						'label'       => __( 'Hide Clock', 'live-news-lite'),
						'description' => __( 'Hide the clock element from the news ticker.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['hide_clock'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'number_of_sliding_news',
						'label'       => __( 'Number of Sliding News', 'live-news-lite'),
						'description' => __( 'Set the number of sliding news that you want to display in a single cycle of news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['number_of_sliding_news'] : null,
						'required'    => false,
						'min'         => 1,
						'max'         => 1000,
						'step'        => 1,
					),
				),
			),

			array(
				'label'          => __('Style', 'live-news-lite'),
				'section_id'     => 'style',
				'icon_id'        => 'brush-03',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'input_range',
						'name'        => 'featured_title_maximum_length',
						'label'       => __( 'Featured News Title Maximum Length', 'live-news-lite'),
						'description' => __( 'Specify the maximum length, in pixels, for the featured news title.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_title_maximum_length'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 280,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'featured_excerpt_maximum_length',
						'label'       => __( 'Featured News Excerpt Maximum Length', 'live-news-lite'),
						'description' => __( 'The maximum length of the featured news excerpt.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_excerpt_maximum_length'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 280,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'sliding_news_maximum_length',
						'label'       => __( 'Sliding News Maximum Length', 'live-news-lite'),
						'description' => __( 'Specify the maximum length, in pixels, for sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_maximum_length'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 280,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'featured_title_font_size',
						'label'       => __( 'Featured News Title Font Size', 'live-news-lite'),
						'description' => __( 'Set the font size, in pixels, for the featured news title.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_title_font_size'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 38,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'featured_excerpt_font_size',
						'label'       => __( 'Featured News Excerpt Font Size', 'live-news-lite'),
						'description' => __( 'Set the font size, in pixels, for the featured news excerpt.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_excerpt_font_size'] : null,
						'maxlength'   => 2,
						'required'    => false,
						'min'         => 0,
						'max'         => 28,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'sliding_news_font_size',
						'label'       => __( 'Sliding News Font Size', 'live-news-lite'),
						'description' => __( 'Set the font size, in pixels, for the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_font_size'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 28,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'clock_font_size',
						'label'       => __( 'Clock Font Size', 'live-news-lite'),
						'description' => __( 'Set the font size, in pixels, for the text in the clock.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_font_size'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 28,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'sliding_news_margin',
						'label'       => __( 'Sliding News Margin', 'live-news-lite'),
						'description' => __( 'Set the margin, in pixels, between the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_margin'] : null,
						'maxlength'   => 3,
						'required'    => false,
						'min'         => 0,
						'max'         => 84,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'sliding_news_padding',
						'label'       => __( 'Sliding News Horizontal Padding', 'live-news-lite'),
						'description' => __( 'Set the horizontal padding, in pixels, applied on the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_padding'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 28,
						'step'        => 1,
					),
					array(
						'type'        => 'text',
						'name'        => 'font_family',
						'label'       => __( 'Font Family', 'live-news-lite'),
						'description' => __( 'Set the font family used for all the textual elements displayed in the news ticker.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['font_family'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'google_font',
						'label'       => __( 'Google Font', 'live-news-lite'),
						'description' => __( 'Load a font from Google Fonts on the front end of your site by entering the embed code URL.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['google_font'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'featured_news_title_color',
						'label'       => __( 'Featured News Title Color', 'live-news-lite'),
						'description' => __( 'Select the color of the featured news title.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_news_title_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'featured_news_title_color_hover',
						'label'       => __( 'Featured News Title Color Hover', 'live-news-lite'),
						'description' => __( 'Select the color of the featured news title in hover status.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_news_title_color_hover'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'featured_news_excerpt_color',
						'label'       => __( 'Featured News Excerpt Color', 'live-news-lite'),
						'description' => __( 'Select the color of the featured news excerpt.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_news_excerpt_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'sliding_news_color',
						'label'       => __( 'Sliding News Color', 'live-news-lite'),
						'description' => __( 'Select the color of the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'sliding_news_color_hover',
						'label'       => __( 'Sliding News Color Hover', 'live-news-lite'),
						'description' => __( 'Select the color of the sliding news in hover status.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_color_hover'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'clock_text_color',
						'label'       => __( 'Clock Text Color', 'live-news-lite'),
						'description' => __( 'Select the color of the text displayed in the clock.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_text_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'featured_news_background_color',
						'label'       => __( 'Featured News Background Color', 'live-news-lite'),
						'description' => __( 'Select the background color of the featured news area.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_news_background_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'featured_news_background_color_opacity',
						'label'       => __( 'Featured News Background Color Opacity', 'live-news-lite'),
						'description' => __( 'Select the background color opacity of the featured news area.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['featured_news_background_color_opacity'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 1,
						'step'        => 0.01,
					),
					array(
						'type'        => 'text',
						'name'        => 'sliding_news_background_color',
						'label'       => __( 'Sliding News Background Color', 'live-news-lite'),
						'description' => __( 'Select the background color of the sliding news area.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_background_color'] : null,
						'maxlength'   => 7,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'sliding_news_background_color_opacity',
						'label'       => __( 'Sliding News Background Color Opacity', 'live-news-lite'),
						'description' => __( 'Select the background color opacity of the sliding news area.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['sliding_news_background_color_opacity'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 1,
						'step'        => 0.01,
					),
					array(
						'type'        => 'media_upload',
						'name'        => 'open_button_image',
						'label'       => __( 'Open Button Image', 'live-news-lite'),
						'description' => __( "Select the image of the button used to open the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['open_button_image'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'media_upload',
						'name'        => 'close_button_image',
						'label'       => __( 'Close Button Image', 'live-news-lite'),
						'description' => __( "Select the image of the button used to close the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['close_button_image'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'media_upload',
						'name'        => 'clock_background_image',
						'label'       => __( 'Clock Background Image', 'live-news-lite'),
						'description' => __( "Select the background image of the clock. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels", 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_background_image'] : null,
						'required'    => false,
					),
				),
			),

			array(
				'label'          => __('Performance', 'live-news-lite'),
				'section_id'     => 'performance',
				'icon_id'        => 'line-chart-up-02',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'input_range',
						'name'        => 'cached_cycles',
						'label'       => __( 'Cached Cycles', 'live-news-lite'),
						'description' => __( 'Set the number of cycles performed by the news ticker without updating the news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['cached_cycles'] : null,
						'required'    => false,
						'min'         => 0,
						'max'         => 1000,
						'step'        => 1,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'transient_expiration',
						'label'       => __( 'Transient Expiration', 'live-news-lite'),
						'description' => __( 'Configure the transient expiration time in seconds. Set to 0 to disable the use of transients.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['transient_expiration'] : null,
						'maxlength'   => 10,
						'required'    => false,
						'min'         => 0,
						'max'         => 3600,
						'step'        => 1,
					),
				),
			),
			array(
				'label'          => __('Advanced', 'live-news-lite'),
				'section_id'     => 'advanced',
				'icon_id'        => 'settings-01',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'select',
						'name'        => 'clock_source',
						'label'       => __( 'Clock Source', 'live-news-lite'),
						'description' => __( "Select whether the time displayed should be based on the server time or the user's local time.", 'live-news-lite'),
						'options'     => array(
							'1' => __( 'Server Time', 'live-news-lite'),
							'2' => __( 'User Time', 'live-news-lite'),
						),
						'value'       => isset( $item_obj ) ? $item_obj['clock_source'] : null,
						'required'    => true,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'clock_offset',
						'label'       => __( 'Clock Offset', 'live-news-lite'),
						'description' => __( 'Set the clock offset in seconds. Positive or negative values are allowed.', 'live-news-lite'),
						'options'     => array(
							'1' => __( 'Server Time', 'live-news-lite'),
							'2' => __( 'User Time', 'live-news-lite'),
						),
						'value'       => isset( $item_obj ) ? $item_obj['clock_offset'] : null,
						'required'    => true,
						'min'         => -86400,
						'max'         => 86400,
						'step'        => 1,
					),
					array(
						'type'        => 'text',
						'name'        => 'clock_format',
						'label'       => __( 'Clock Format', 'live-news-lite'),
						'description' => __( 'Specify the displayed time format using Moment.js tokens.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_format'] : null,
						'maxlength'   => 30,
						'required'    => true,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'clock_autoupdate',
						'label'       => __( 'Clock Autoupdate', 'live-news-lite'),
						'description' => __( 'Automatically update the clock independently from the cycles of news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_autoupdate'] : null,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'clock_autoupdate_time',
						'label'       => __( 'Clock Autoupdate Time', 'live-news-lite'),
						'description' => __( 'Set how frequent should be the clock automatic updates in seconds.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['clock_autoupdate_time'] : null,
						'required'    => false,
						'min'         => 1,
						'max'         => 3600,
						'step'        => 1,
					),
				),
			),

		);

			$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The ID of the item to be checked.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		if ( $this->shared->ticker_is_used( $item_id ) ) {
			$is_deletable               = false;
			$dismissible_notice_message = __( "This news ticker is associated with one or more news and can't be deleted.", 'live-news-lite');
		} else {
			$is_deletable               = true;
			$dismissible_notice_message = null;
		}

		return array(
			'is_deletable'               => $is_deletable,
			'dismissible_notice_message' => $dismissible_notice_message,
		);
	}
}
