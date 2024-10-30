<?php
/**
 * Class used to implement the back-end functionalities of the "Sliding" menu.
 *
 * @package live-news-lite
 */

/**
 * Class used to implement the back-end functionalities of the "Sliding" menu.
 */
class Daextlnl_Sliding_News_Menu_Elements extends Daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'sliding-news';
		$this->slug_plural        = 'sliding-news';
		$this->label_singular     = __( 'Sliding News', 'live-news-lite');
		$this->label_plural       = __( 'Sliding News', 'live-news-lite');
		$this->primary_key        = 'id';
		$this->db_table           = 'sliding_news';
		$this->list_table_columns = array(
			array(
				'db_field' => 'news_title',
				'label'    => __( 'Title', 'live-news-lite'),
			),
			array(
				'db_field'                => 'ticker_id',
				'label'                   => __( 'Ticker', 'live-news-lite'),
				'prepare_displayed_value' => array( $shared, 'get_ticker_name' ),
			),
		);
		$this->searchable_fields  = array(
			'news_title',
			'url',
		);

		$this->default_values = array(
			'news_title'               => '',
			'url'                      => '',
			'ticker_id'                => '0',
			'text_color'               => '#eeeeee',
			'text_color_hover'         => '#aaaaaa',
			'background_color'         => '#000000',
			'background_color_opacity' => '1',
			'image_before'             => '',
			'image_after'              => '',
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

			$data['news_title']               = isset( $_POST['news_title'] ) ? sanitize_text_field( wp_unslash( $_POST['news_title'] ) ) : null;
			$data['url']                      = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : null;
			$data['ticker_id']                = isset( $_POST['ticker_id'] ) ? intval( $_POST['ticker_id'], 10 ) : null;
			$data['text_color']               = isset( $_POST['text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['text_color'] ) ) : null;
			$data['text_color_hover']         = isset( $_POST['text_color_hover'] ) ? sanitize_text_field( wp_unslash( $_POST['text_color_hover'] ) ) : null;
			$data['background_color']         = isset( $_POST['background_color'] ) ? sanitize_text_field( wp_unslash( $_POST['background_color'] ) ) : null;
			$data['background_color_opacity'] = isset( $_POST['background_color_opacity'] ) ? floatval( $_POST['background_color_opacity'] ) : null;
			$data['image_before']             = isset( $_POST['image_before'] ) ? esc_url_raw( wp_unslash( $_POST['image_before'] ) ) : null;
			$data['image_after']              = isset( $_POST['image_after'] ) ? esc_url_raw( wp_unslash( $_POST['image_after'] ) ) : null;

		}

		// Validation.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Validation -----------------------------------------------------------------------------------------------------.

			// Validation on "Title".
			if ( mb_strlen( trim( $data['news_title'] ) ) === 0 || mb_strlen( $data['news_title'] ) > 1000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Title" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "URL".
			if ( mb_strlen( $data['url'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "URL" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Text Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['text_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Text Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Text Color Hover".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['text_color_hover'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Text Color Hover" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Background Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $data['background_color'] ) ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid color in the "Background Color" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Background Color Opacity".
			if ( $data['background_color_opacity'] < 0 || $data['background_color_opacity'] > 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a value included between 0 and 1 in the "Background Color Opacity" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Image Before".
			if ( mb_strlen( $data['image_before'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "Image Left" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Image After".
			if ( mb_strlen( $data['image_after'] ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid URL in the "Image Right" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextlnl_sliding_news SET 
                news_title = %s,
                url = %s,
                ticker_id = %d,
                text_color = %s,
                text_color_hover = %s,
                background_color = %s,
                background_color_opacity = %f,
                image_before = %s,
                image_after = %s
                WHERE id = %d",
					$data['news_title'],
					$data['url'],
					$data['ticker_id'],
					$data['text_color'],
					$data['text_color_hover'],
					$data['background_color'],
					$data['background_color_opacity'],
					$data['image_before'],
					$data['image_after'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The sliding news has been successfully updated.', 'live-news-lite'),
					'updated'
				);
			}

			// Add record to database ------------------------------------------------------------------.
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

				// Insert into the database.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextlnl_sliding_news SET 
                    news_title = %s,
                    url = %s,
                    ticker_id = %d,
                    text_color = %s,
                    text_color_hover = %s,
                    background_color = %s,
                    background_color_opacity = %f,
                    image_before = %s,
                    image_after = %s",
						$data['news_title'],
						$data['url'],
						$data['ticker_id'],
						$data['text_color'],
						$data['text_color_hover'],
						$data['background_color'],
						$data['background_color_opacity'],
						$data['image_before'],
						$data['image_after']
					)
				);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The sliding news has been successfully added.', 'live-news-lite'),
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

		$ticker_options = array();

		$ticker_options = array(
			'0' => __( 'None', 'live-news-lite'),
		);

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$tickers_a = $wpdb->get_results(
			"SELECT id, name FROM {$wpdb->prefix}daextlnl_tickers ORDER BY id DESC",
			ARRAY_A
		);

		foreach ( $tickers_a as $key => $ticker ) {
			$ticker_options[ $ticker['id'] ] = $ticker['name'];
		}

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'icon_id'        => 'dots-grid',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'news_title',
						'label'       => __( 'Title', 'live-news-lite'),
						'description' => __( 'Enter the title of the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['news_title'] : null,
						'maxlength'   => 1000,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'url',
						'label'       => __( 'URL', 'live-news-lite'),
						'description' => __( 'Enter the URL of the sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['url'] : null,
						'maxlength'   => 2083,
						'required'    => false,
					),
					array(
						'type'        => 'select',
						'name'        => 'ticker_id',
						'label'       => __( 'Ticker', 'live-news-lite'),
						'description' => __( 'Select the news ticker associated with this sliding news.', 'live-news-lite'),
						'options'     => $ticker_options,
						'value'       => isset( $item_obj ) ? $item_obj['ticker_id'] : null,
						'required'    => true,
					),
				),
			),
			array(
				'label'          => 'Style',
				'section_id'     => 'style',
				'icon_id'        => 'brush-03',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'text_color',
						'label'       => __( 'Text Color', 'live-news-lite'),
						'description' => __( 'Select the color used to display the text of this sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['text_color'] : null,
						'maxlength'   => 7,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'text_color_hover',
						'label'       => __( 'Text Color Hover', 'live-news-lite'),
						'description' => __( 'Select the color used to display the text of this sliding news in hover state.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['text_color_hover'] : null,
						'maxlength'   => 7,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'background_color',
						'label'       => __( 'Background Color', 'live-news-lite'),
						'description' => __( 'Select the background color of this sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['background_color'] : null,
						'maxlength'   => 7,
						'required'    => true,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'background_color_opacity',
						'label'       => __( 'Background Color Opacity', 'live-news-lite'),
						'description' => __( 'Select the background color opacity of this sliding news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['background_color_opacity'] : null,
						'required'    => true,
						'min'         => 0,
						'max'         => 1,
						'step'        => 0.01,
					),
					array(
						'type'        => 'media_upload',
						'name'        => 'image_before',
						'label'       => __( 'Image Left', 'live-news-lite'),
						'description' => __( "Select the image displayed on the left of the sliding news. It's recommended to use an image with an height of 40 pixels.", 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['image_before'] : null,
						'required'    => true,
					),
					array(
						'type'        => 'media_upload',
						'name'        => 'image_after',
						'label'       => __( 'Image Left', 'live-news-lite'),
						'description' => __( "Select the image displayed on the left of the sliding news. It's recommended to use an image with an height of 40 pixels.", 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['image_after'] : null,
						'required'    => true,
					),
				),
			),
		);

			$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The item id.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		return array(
			'is_deletable'               => true,
			'dismissible_notice_message' => null,
		);
	}
}
