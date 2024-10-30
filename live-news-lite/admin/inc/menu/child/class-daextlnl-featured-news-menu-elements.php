<?php
/**
 * Class used to implement the back-end functionalities of the "Featured" menu.
 *
 * @package live-news-lite
 */

/**
 * Class used to implement the back-end functionalities of the "Featured" menu.
 */
class Daextlnl_Featured_News_Menu_Elements extends Daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param Daextlnl_Shared $shared An instance of the shared class.
	 * @param string      $page_query_param The query parameter used to identify the current page.
	 * @param array       $config The configuration array.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'featured-news';
		$this->slug_plural        = 'featured-news';
		$this->label_singular     = __( 'Featured News', 'live-news-lite');
		$this->label_plural       = __( 'Featured News', 'live-news-lite');
		$this->primary_key        = 'id';
		$this->db_table           = 'featured_news';
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
			'news_excerpt',
			'url',
		);

		$this->default_values = array(
			'news_title'   => '',
			'news_excerpt' => '',
			'url'          => '',
			'ticker_id'    => '0',
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

			// Main Form data.
			$data['news_title']   = isset( $_POST['news_title'] ) ? sanitize_text_field( wp_unslash( $_POST['news_title'] ) ) : null;
			$data['news_excerpt'] = isset( $_POST['news_excerpt'] ) ? sanitize_text_field( wp_unslash( $_POST['news_excerpt'] ) ) : null;
			$data['url']          = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : null;
			$data['ticker_id']    = isset( $_POST['ticker_id'] ) ? intval( wp_unslash( $_POST['ticker_id'] ), 10 ) : null;

		}

		// Validation.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Validation on "Title".
			if ( 0 === mb_strlen( trim( $data['news_title'] ) ) || mb_strlen( $data['news_title'] ) > 1000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Title" field.', 'live-news-lite'),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Excerpt".
			if ( 0 === mb_strlen( trim( $data['news_excerpt'] ) ) || mb_strlen( $data['news_excerpt'] ) > 1000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Excerpt" field.', 'live-news-lite'),
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
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextlnl_featured_news SET 
                news_title = %s,
                news_excerpt = %s,
                url = %s,
                ticker_id = %d
                WHERE id = %d",
					$data['news_title'],
					$data['news_excerpt'],
					$data['url'],
					$data['ticker_id'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The featured news has been successfully updated.', 'live-news-lite'),
					'updated'
				);
			}

			// Add record to database ------------------------------------------------------------------.

		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

				// Insert into the database.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextlnl_featured_news SET 
                    news_title = %s,
                    news_excerpt = %s,
                    url = %s,
                    ticker_id = %d",
						$data['news_title'],
						$data['news_excerpt'],
						$data['url'],
						$data['ticker_id']
					)
				);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The featured news has been successfully added.', 'live-news-lite'),
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
						'description' => __( 'Enter the title of the featured news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['news_title'] : null,
						'maxlength'   => 1000,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'news_excerpt',
						'label'       => __( 'Excerpt', 'live-news-lite'),
						'description' => __( 'Enter the excerpt of the featured news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['news_excerpt'] : null,
						'maxlength'   => 1000,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'url',
						'label'       => __( 'URL', 'live-news-lite'),
						'description' => __( 'Enter the URL of the featured news.', 'live-news-lite'),
						'value'       => isset( $item_obj ) ? $item_obj['url'] : null,
						'maxlength'   => 2083,
						'required'    => false,
					),
					array(
						'type'        => 'select',
						'name'        => 'ticker_id',
						'label'       => __( 'Ticker', 'live-news-lite'),
						'description' => __( 'Select the news ticker associated with this featured news.', 'live-news-lite'),
						'options'     => $ticker_options,
						'value'       => isset( $item_obj ) ? $item_obj['ticker_id'] : null,
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
