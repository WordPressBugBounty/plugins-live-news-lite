<?php
/**
 * Parent class used to create the admin pages.
 *
 * @package live-news-lite
 */

/**
 * Parent class used to create the admin pages.
 */
class Daextlnl_Menu_Elements {

	/**
	 * The capability required to access the menu.
	 *
	 * @var string
	 */
	public $capability = null;

	/**
	 * The context of the menu.
	 *
	 * @var string
	 */
	public $context = null;

	/**
	 * Array with general menu data, like toolbar menu items.
	 *
	 * @var self
	 */
	public $config = null;

	/**
	 * An instance of the shared class.
	 *
	 * @var Daextlnl_Shared
	 */
	public $shared = null;

	/**
	 * The menu slug.
	 *
	 * @var null
	 */
	public $menu_slug = null;

	/**
	 * The plural version of the slug.
	 *
	 * @var null
	 */
	public $slug_plural = null;

	/**
	 * The singular version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_singular = null;

	/**
	 * The plural version of the displayed menu label.
	 *
	 * @var null
	 */
	public $label_plural = null;

	/**
	 * The primary key of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $primary_key = null;

	/**
	 * The name of the database table associated with the managed back-end page.
	 *
	 * @var null
	 */
	public $db_table = null;

	/**
	 * The list of columns to display in the table.
	 *
	 * @var null
	 */
	public $list_table_columns = null;

	/**
	 * The list of database table fields that can be searched using the menu search field.
	 *
	 * @var null
	 */
	public $searchable_fields = null;

	/**
	 * The default values of the echoed form fields.
	 *
	 * @var null
	 */
	public $default_values = null;

	/**
	 * The instance of the class.
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * The constructor.
	 *
	 * @param Daextlnl_Shared $shared An instance of the shared class.
	 * @param string      $page_query_param The query parameter used to identify the current page.
	 * @param array       $config The configuration array.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		// assign an instance of the plugin info.
		$this->shared = $shared;

		$this->config = $config;

		add_action( 'admin_init', array( $this, 'handle_duplicate' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_delete' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_clear_cache' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_bulk_actions' ), 10 );

		// check if this instance has the method "process_form".
		if ( method_exists( $this, 'process_form' ) ) {
			add_action( 'admin_init', array( $this, 'process_form' ), 10 );
		}
	}

	/**
	 * Get the singleton instance of the class.
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
	 * Generate the table with the list of items for the provided database table.
	 *
	 * @param string $db_table The name of the database table.
	 * @param array  $columns The list of columns to display in the table.
	 * @param array  $searchable_fields The list of database table fields that can be searched using the menu search field.
	 * @param string $item_name_plural The plural version of the displayed menu label.
	 * @param string $page_slug The slug of the menu page.
	 * @param string $db_primary_key The primary key of the database table associated with the managed back-end page.
	 *
	 * @return void
	 */
	public function display_list_table(
		$db_table,
		$columns,
		$searchable_fields,
		$item_name_plural,
		$page_slug,
		$db_primary_key
	) {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
		$post_search_input = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null;

		// Table -----------------------------------------------------------------------------------------------.

			global $wpdb;

			$filter = '';

			// create the query part used to filter the results when a search is performed.
		if ( ! is_null( $post_search_input ) && mb_strlen( trim( $post_search_input ) ) > 0 ) {

			foreach ( $searchable_fields as $key => $searchable_field ) {

				if ( 0 === $key ) {
					$filter .= 'WHERE (';
				} else {
					$filter .= ' OR ';
				}

				// Sanitize the field name.
				$searchable_field = sanitize_key( $searchable_field );

				// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $searchable_field is sanitized above.
				$filter .= $wpdb->prepare(
					$searchable_field . ' LIKE %s',
					'%' . $post_search_input . '%'
				);

				if ( count( $searchable_fields ) - 1 === $key ) {
					$filter .= ')';
				}
			}
		}

			// Retrieve the total number of items.

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is already prepared.
			$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextlnl_$db_table $filter" );

			// Initialize the pagination class.
			require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextlnl-pagination.php';
			$pag = new Daextlnl_Pagination( $this->shared );
			$pag->set_total_items( $total_items );// Set the total number of items.
			$pag->set_record_per_page( 10 ); // Set records per page.
			$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-' . $page_slug );// Set target page.
			$pag->set_current_page();// set the current page number from $_GET.

		?>

			<!-- Query the database -->
			<?php
			$query_limit = $pag->query_limit();

			// Sanitize the field name.
			$db_primary_key = sanitize_key( $db_primary_key );

			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_results(
				"SELECT * FROM {$wpdb->prefix}daextlnl_$db_table $filter ORDER BY $db_primary_key DESC $query_limit",
				ARRAY_A
			);
			// phpcs:enable

			?>

			<?php if ( count( $results ) > 0 ) : ?>

				<?php $this->display_crud_menu_search_form( $post_search_input ); ?>

				<input type="hidden" name="items-filter" value="1">

				<div class="daextlnl-crud-table">

					<!-- list of tables -->
					<table class="daextlnl-crud-table__daext-items">
						<thead>
						<tr>
							<th>
								<input type="checkbox" class="daextlnl-cb-select-all">
							</th>
							<?php
							foreach ( $columns as $column ) {
								?>
								<th>
									<div><?php echo esc_html( $column['label'] ); ?></div>
									<div class="help-icon"></div>
								</th>
								<?php
							}
							?>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $results as $result ) : ?>
							<tr>
								<td><input type="checkbox"
											class="daextlnl-bulk-action-checkbox"
											id="cb-select-<?php echo intval( $result[ $db_primary_key ], 10 ); ?>"
											value="<?php echo intval( $result[ $db_primary_key ], 10 ); ?>" name="post[]"></td>
								<?php

								foreach ( $columns as $key => $column ) {
									?>
									<td>

										<?php if ( 0 === $key ) : ?>
											<a class="daextlnl-crud-table__item-name" href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>&edit_id=<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
												<?php echo esc_html( stripslashes( $result[ $column['db_field'] ] ) ); ?>
											</a>
											<div class="daextlnl-crud-table__row-actions">
											<div class="daextlnl-crud-table__row-actions-single-action">
												<a href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>&edit_id=<?php echo esc_attr( $result[ $db_primary_key ] ); ?>"><?php esc_html_e( 'Edit', 'live-news-lite'); ?></a>
											</div>
											<div>&nbsp|&nbsp</div>
											<div class="daextlnl-crud-table__row-actions-single-action">
												<form method="POST"
													action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
													id="clone-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
													<?php wp_nonce_field( 'daextlnl_clone_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextlnl_clone_' . $this->menu_slug . '_nonce' ); ?>
													<button type="submit" name="clone_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>"><?php esc_html_e( 'Duplicate', 'live-news-lite'); ?></button>
												</form>
											</div>
											<div>&nbsp|&nbsp</div>
											<div class="daextlnl-crud-table__row-actions-single-action daextlnl-crud-table__row-actions-single-action-delete">
												<form method="POST"
														action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
														id="delete-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
													<?php wp_nonce_field( 'daextlnl_delete_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextlnl_clone_' . $this->menu_slug . '_nonce' ); ?>
													<input type="hidden" name="delete_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>">
													<button type="submit" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>"><?php esc_html_e( 'Delete', 'live-news-lite'); ?></button>
												</form>
											</div>
											<?php
											if (
													'ticker' === $this->menu_slug
													&& false !== get_transient( 'daextlnl_ticker_' . intval( $result[ $db_primary_key ], 10 ) )
											) :
												?>
												<div>&nbsp|&nbsp</div>
												<div class="daextlnl-crud-table__row-actions-single-action daextlnl-crud-table__row-actions-single-action-delete">
													<form method="POST"
															action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $page_slug ); ?>"
															id="clear-cache-item-<?php echo esc_attr( $result[ $db_primary_key ] ); ?>">
																					<?php wp_nonce_field( 'daextlnl_clear_cache_' . $this->menu_slug . '_' . intval( $result[ $db_primary_key ], 10 ), 'daextlnl_clear_cache_' . $this->menu_slug . '_nonce' ); ?>
														<input type="hidden" name="clear_cache_id" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>">
														<button type="submit" value="<?php echo esc_html( $result[ $db_primary_key ] ); ?>"><?php esc_html_e( 'Clear Cache', 'live-news-lite'); ?></button>
													</form>
												</div>
											<?php endif; ?>


										</div>

										<?php else : ?>
											<?php

											// Custom output function.
											if ( isset( $column['custom_output_function'] ) ) {
												$this->shared->{$column['custom_output_function']}( $result[ $db_primary_key ] );
											} elseif ( isset( $column['prepare_displayed_value'] ) && null !== $column['prepare_displayed_value'] ) {
												echo esc_html( $column['prepare_displayed_value']( $result[ $column['db_field'] ] ) );
											} else {
												echo esc_html( stripslashes( $result[ $column['db_field'] ] ) );
											}

											?>
										<?php endif; ?>
									</td>
									<?php
								}

								?>
							</tr>
						<?php endforeach; ?>

						</tbody>

						<tfoot>
						<tr>
							<th>
								<input type="checkbox" class="daextlnl-cb-select-all">
							</th>
							<?php
							foreach ( $columns as $column ) {
								?>
								<th>
									<div><?php echo esc_html( $column['label'] ); ?></div>
									<div class="help-icon"></div>
								</th>
								<?php
							}
							?>
						</tr>
						</tfoot>

					</table>

				</div>

				<!-- Bulk Actions -->
				<div class="daextlnl-crud-table-controls">

					<div class="daextlnl-crud-table-controls__bulk-actions">
						<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>">
							<select name="bulk_action" id="bulk_action">
								<option value=""><?php esc_html_e( 'Bulk actions', 'live-news-lite'); ?></option>
								<option value="delete"><?php esc_html_e( 'Delete', 'live-news-lite'); ?></option>
							</select>
							<?php wp_nonce_field( 'daextlnl_bulk_action_' . $this->menu_slug, 'daextlnl_bulk_action_' . $this->menu_slug . '_nonce' ); ?>
							<input id="bulk-action-selected-items" type="hidden" name="bulk-action-selected-items" value="">
							<input id="daextlnl-submit-bulk-action" type="submit" class="button daextlnl-admin-page-button" value="<?php esc_html_e( 'Apply', 'live-news-lite'); ?>">
						</form>
					</div>

					<div class="daextlnl-crud-table-controls__pagination-container">
						<!-- Display the pagination -->
						<?php if ( $pag->total_items > 0 ) : ?>
							<div class="daextlnl-crud-table-controls__daext-tablenav">
									<span class="daextlnl-crud-table-controls__daext-displaying-num"><?php echo esc_html( $pag->total_items ); ?>&nbsp<?php esc_html_e( 'items', 'live-news-lite'); ?></span>
									<?php $pag->show(); ?>
							</div>
						<?php endif; ?>
					</div>

				</div>

			<?php else : ?>

				<?php

				if ( mb_strlen( trim( $filter ) ) > 0 ) {
					$this->shared->save_dismissible_notice(
						__( 'There are no results that match your filter.', 'live-news-lite'),
						'updated'
					);
				}

				// Display the dismissible notices.
				$this->shared->display_dismissible_notices();

				// Display the search form of the CRUD menu.
				$this->display_crud_menu_search_form( $post_search_input );

				// If the filters are not applied and there are no items, display a message.
				if ( 0 === mb_strlen( trim( $filter ) ) ) {
					echo '<div class="daextlnl-crud-table__no-items-found-message">' . esc_html__( 'Nothing to show yet! Add some items by clicking the Add New button.', 'live-news-lite') . '</div>';
				}

				?>

			<?php endif; ?>

			<div>

		</div>

		<?php
	}

	/**
	 * Display the header bar.
	 *
	 * @return void
	 */
	public function header_bar() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
		$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
		$edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : null;
		// phpcs:enable

		if ( 'new' === $action ) {
			$page_title = __( 'Add New', 'live-news-lite') . ' ' . $this->label_singular;
		} elseif ( null !== $edit_id ) {
			$page_title = __( 'Edit', 'live-news-lite') . ' ' . $this->label_singular;
		} else {
			$page_title = $this->label_plural;
		}

		?>

		<div class="daextlnl-header-bar">

			<div class="daextlnl-header-bar__left">
				<div class="daextlnl-header-bar__page-title"><?php echo esc_html( $page_title ); ?></div>
				<?php if ( 'list' === $action && 'crud' === $this->context && null === $edit_id ) : ?>
					<a href="<?php echo esc_url( get_admin_url() . 'admin.php?page=daextlnl-' . $this->slug_plural . '&action=new' ); ?>"
						class="daextlnl-button daextlnl-header-bar__add-new-button">
						<?php $this->shared->echo_icon_svg( 'plus' ); ?>
						<div class="daextlnl-header-bar__add-new-button-text"><?php esc_html_e( 'Add New', 'live-news-lite'); ?></div>
					</a>
				<?php endif; ?>
			</div>

			<div class="daextlnl-header-bar__right">
				<?php if ( 'new' === $action || null !== $edit_id ) : ?>
					<a href="#" onclick="document.getElementById('form1').submit()" class="daextlnl-btn daextlnl-btn-primary"><?php esc_html_e( 'Save Changes', 'live-news-lite'); ?></a>
				<?php endif; ?>
			</div>

		</div>

		<?php
	}

	/**
	 * Generate a form for the creation of a new item.
	 */
	public function new_item() {

		?>

		<div class="daextlnl-main-form">

			<form id="form1" name="form1" method="POST"
					action="admin.php?page=daextlnl-<?php echo esc_attr( $this->slug_plural ); ?>"
					autocomplete="off">

				<input type="hidden" value="1" name="form_submitted">

				<?php

				wp_nonce_field( 'daextlnl_create_update_' . $this->menu_slug, 'daextlnl_create_update_' . $this->menu_slug . '_nonce' );

				$this->print_form_fields( $this->default_values );

				?>

			</form>

		</div>

		<?php
	}

	/**
	 * Generate an edit form for the provided database table.
	 *
	 * @param int $edit_id The id of the edited item.
	 *
	 * @return void
	 */
	public function edit_item( $edit_id ) {

		?>

		<div class="daextlnl-main-form">

			<form id="form1" name="form1" method="POST"
					action="admin.php?page=daextlnl-<?php echo esc_attr( $this->slug_plural ); ?>"
					autocomplete="off">

				<input type="hidden" value="1" name="form_submitted">

				<?php

				// Get the object from the db table using $wpdb.
				global $wpdb;

				// Sanitize the field name.
				$db_table_name = $wpdb->prefix . 'daextlnl_' . $this->db_table;

				// Sanitize the field name.
				$primary_key = sanitize_key( $this->primary_key );

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $db_table_name and $primary_key are sanitized above.
				$item_obj = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM $db_table_name WHERE $primary_key = %d",
						$edit_id
					),
					ARRAY_A
				);
				// phpcs:enable

				wp_nonce_field( 'daextlnl_create_update_' . $this->menu_slug, 'daextlnl_create_update_' . $this->menu_slug . '_nonce' );

				echo '<input type="hidden" name="update_id" value="' . esc_attr( $item_obj[ $this->primary_key ] ) . '"/>';

				$this->print_form_fields( $item_obj );

				?>

			</form>

		</div>

		<?php
	}

	/**
	 * Display the header of a section of the menu. The header includes the section name and a toggle to open and close
	 * the caption.
	 *
	 * @param string $label The displayed name of the section.
	 * @param string $section_id The alphanumeric id of the section.
	 * @param string $icon_id The id of the icon to display.
	 *
	 * @return void
	 */
	public function section_header( $label, $section_id, $icon_id = null ) {

		?>

		<div class="daextlnl-main-form__section-header group-trigger" data-trigger-target="<?php echo esc_attr( $section_id ); ?>">
			<div class="daextlnl-main-form__section-header-title">
				<?php $this->shared->echo_icon_svg( $icon_id ); ?>
				<div class="daextlnl-main-form__section-header-title-text"><?php echo esc_html( $label ); ?></div>
			</div>
			<div class="daextlnl-main-form__section-header-toggle">
				<?php $this->shared->echo_icon_svg( 'chevron-down' ); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $placeholder The placeholder of the field.
	 * @param string $value The value of the field.
	 * @param int    $maxlength The maximum length of the field.
	 * @param bool   $required If the field is required.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function input_field(
		$name = '',
		$label = '',
		$description = '',
		$placeholder = '',
		$value = null,
		$maxlength = null,
		$required = false,
		$section_id = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span class="daextlnl-required">*</span>' : ''; ?></label></div>
			<div>
				<input type="text" id="<?php echo esc_attr( $name ); ?>" maxlength="<?php echo esc_attr( $maxlength ); ?>" size="30"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
					<?php
					if ( ! is_null( $value ) ) {
						echo 'value="' . esc_attr( $value ) . '"';
					}
					?>
					/>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML select field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $options The options of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function select_field(
		$name = '',
		$label = '',
		$description = '',
		$options = null,
		$value = null,
		$section_id = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<select id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( $name ); ?>">
					<?php
					foreach ( $options as $key => $option ) {

						/**
						 * Convert the key and value to integers if they are numeric. This prevents data types
						 * comparison issues in the next if statement that should use the identical operator.
						 */
						if ( is_numeric( $key ) && is_numeric( $value ) ) {
							$key   = intval( $key, 10 );
							$value = intval( $value, 10 );
						}

						echo '<option value="' . esc_attr( $key ) . '"';
						if ( $value === $key ) {
							echo 'selected';}
						echo '>' . esc_html( $option ) . '</option>';
					}
					?>
				</select>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML textarea field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $placeholder The placeholder of the field.
	 * @param string $value The value of the field.
	 * @param int    $maxlength The maximum length of the field.
	 * @param bool   $required If the field is required.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function textarea_field(
		$name = '',
		$label = '',
		$description = '',
		$placeholder = '',
		$value = null,
		$maxlength = null,
		$required = false,
		$section_id = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span class="daextlnl-required">*</span>' : ''; ?></label></div>
			<textarea type="text" id="<?php echo esc_attr( $name ); ?>" maxlength="<?php echo esc_attr( $maxlength ); ?>" size="30"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
			><?php
			if ( ! is_null( $value ) ) {
				echo esc_html( $value ); }
			?></textarea>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML select multiple field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $options The options of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function select_multiple_field(
		$name = '',
		$label = '',
		$description = '',
		$options = null,
		$value = null,
		$section_id = null
	) {

		$value_a = maybe_unserialize( $value );

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<select id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( $name ); ?>[]" multiple>
					<?php
					foreach ( $options as $key => $option ) {

						/**
						 * Convert the key and value to integers if they are numeric. This prevents data types
						 * comparison issues in the next if statement that should use the identical operator.
						 */
						if ( is_array( $value_a ) ) {
							foreach ( $value_a as $value_a_value ) {
								if ( is_numeric( $value_a_value ) && is_numeric( $key ) ) {
									$value_a_value = intval( $value_a_value, 10 );
									$key           = intval( $key, 10 );
								}
								if ( $value_a_value === $key ) {
									$selected = 'selected';
									break;
								} else {
									$selected = '';
								}
							}
						} else {
							$selected = '';
						}

						echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected );
						echo '>' . esc_html( $option ) . '</option>';
					}
					?>
				</select>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function toggle_field(
		$name = '',
		$label = '',
		$description = '',
		$value = null,
		$section_id = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div class="switch-container">
				<div class="switch-left">
					<label class="switch">
						<input id="<?php echo esc_attr( $name ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" <?php checked( intval( $value, 10 ), 1 ); ?>>
						<span class="slider round"></span>
					</label>
				</div>
				<div class="switch-right">
					<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
				</div>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $value The value of the field.
	 * @param string $section_id The id of the section.
	 * @param string $min The minimum value of the range.
	 * @param string $max The maximum value of the range.
	 * @param string $step The step of the range.
	 *
	 * @return void
	 */
	public function input_range_field(
		$name = '',
		$label = '',
		$description = '',
		$value = null,
		$section_id = null,
		$min = null,
		$max = null,
		$step = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top" data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div><label for="title"><?php echo esc_html( $label ); ?></label></div>
			<div>
				<input
						type="range"
						id="<?php echo esc_attr( $name ); ?>"
						maxlength="100"
						size="30"
						name="<?php echo esc_attr( $name ); ?>"
						min="<?php echo esc_attr( $min ); ?>"
						max="<?php echo esc_attr( $max ); ?>"
						step="<?php echo esc_attr( $step ); ?>"
						data-range-sync-id="<?php echo esc_attr( $name ); ?>"
					<?php
					if ( ! is_null( $value ) ) {
						echo 'value="' . esc_attr( $value ) . '"';
					}
					?>
				/>
				<input
						class="inputNumber"
						type="number"
						min="<?php echo esc_attr( $min ); ?>"
						max="<?php echo esc_attr( $max ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						data-range-sync-id="<?php echo esc_attr( $name ); ?>"
				/>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Generate an HTML input field.
	 *
	 * @param string $name The HTML element name.
	 * @param string $label The displayed name of the field.
	 * @param string $description The displayed description of the field.
	 * @param string $value The value of the field.
	 * @param bool   $required If the field is required.
	 * @param string $section_id The id of the section.
	 *
	 * @return void
	 */
	public function media_upload(
		$name = '',
		$label = '',
		$description = '',
		$value = null,
		$required = false,
		$section_id = null
	) {

		?>

		<div class="daextlnl-main-form__daext-form-field" valign="top"
			data-section-id="<?php echo esc_attr( $section_id ); ?>">
			<div>
				<label for="title"><?php echo esc_html( $label ); ?><?php echo $required ? ' <span class="daextlnl-required">*</span>' : ''; ?></label>
			</div>
			<div class="image-uploader">
				<img class="selected-image" src="<?php echo esc_attr( $value ); ?>" <?php echo mb_strlen( trim( $value ) ) === 0 ? 'style="display: none"' : ''; ?>>
				<input type="hidden" id="<?php echo esc_attr( $name ); ?>" maxlength="2083"
						name="<?php echo esc_attr( $name ); ?>"
					<?php
					if ( ! is_null( $value ) ) {
						echo 'value="' . esc_attr( $value ) . '"';
					}
					?>
				/>
				<a class="button_add_media"
					data-set-remove="<?php echo mb_strlen( trim( $value ) ) === 0 ? 'set' : 'remove'; ?>"
					data-set="<?php esc_attr_e( 'Set image', 'live-news-lite'); ?>"
					data-remove="<?php esc_attr_e( 'Remove Image', 'live-news-lite'); ?>">
					<?php echo mb_strlen( trim( $value ) ) === 0 ? esc_attr__( 'Set image', 'live-news-lite' ) : esc_attr__( 'Remove Image', 'live-news-lite'); ?>
				</a>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			</div>
		</div>

		<?php
	}

	/**
	 * Display the admin toolbar. Which is the top section of the plugin admin menus.
	 *
	 * @return void
	 */
	public function display_admin_toolbar() {

		?>

		<div class="daextlnl-admin-toolbar">
			<div class="daextlnl-admin-toolbar__left-section">
				<div class="daextlnl-admin-toolbar__menu-items">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=daextlnl-tickers' ) ); ?>" class="daextlnl-admin-toolbar__plugin-logo">
						<img src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/plugin-logo.svg' ); ?>" alt="Live News" />
					</a>
					<?php

					foreach ( $this->config['admin_toolbar']['items'] as $key => $item ) {

						?>

						<a href="<?php echo esc_attr( $item['link_url'] ); ?>" class="daextlnl-admin-toolbar__menu-item <?php echo 'daextlnl-' . $this->menu_slug === $item['menu_slug'] ? 'is-active' : ''; ?>">
							<div class="daextlnl-admin-toolbar__menu-item-wrapper">
								<?php $this->shared->echo_icon_svg( $item['icon'] ); ?>
								<div class="daextlnl-admin-toolbar__menu-item-text"><?php echo esc_html( $item['link_text'] ); ?></div>
							</div>
						</a>

						<?php

					}

					?>

					<div class="daextlnl-admin-toolbar__menu-item daextlnl-admin-toolbar__menu-item-more">
						<div class="daextlnl-admin-toolbar__menu-item-wrapper">
							<?php $this->shared->echo_icon_svg( 'grid-01' ); ?>
							<div class="daextlnl-admin-toolbar__menu-item-text"><?php esc_html_e( 'More', 'live-news-lite'); ?></div>
							<?php $this->shared->echo_icon_svg( 'chevron-down' ); ?>
						</div>
						<ul class="daextlnl-admin-toolbar__pop-sub-menu">

							<?php

							foreach ( $this->config['admin_toolbar']['more_items'] as $key => $more_item ) {

								?>

								<li>
									<a href="<?php echo esc_attr( $more_item['link_url'] ); ?>" <?php echo 1 === intval( $more_item['pro_badge'], 10 ) ? 'target="_blank"' : ''; ?>>
										<?php echo '<div class="daextlnl-admin-toolbar__more-item-item-text">' . esc_html( $more_item['link_text'] ) . '</div>'; ?>
										<?php

										if ( true === isset( $more_item['pro_badge'] ) && $more_item['pro_badge'] ) {
											echo '<div class="daextlnl-admin-toolbar__pro-badge">' . esc_html__( 'PRO', 'live-news-lite') . '</div>';
										}

										?>
									</a>
								</li>

								<?php

							}

							?>

						</ul>
					</div>
				</div>
			</div>
			<div class="daextlnl-admin-toolbar__right-section">
				<!-- Display the upgrade button in the Free version. -->
				<?php if ( constant( 'DAEXTLNL_EDITION' ) === 'FREE' ) : ?>
				<a href="https://daext.com/live-news/" target="_blank" class="daextlnl-admin-toolbar__upgrade-button">
					<?php $this->shared->echo_icon_svg( 'diamond-01' ); ?>
					<div class="daextlnl-admin-toolbar__upgrade-button-text"><?php esc_html_e( 'Unlock Extra Features with LN Pro', 'live-news-lite'); ?></div>
				</a>
				<?php endif; ?>
				<a href="https://daext.com" target="_blank" class="daextlnl-admin-toolbar__daext-logo-container">
				<img class="daextlnl-admin-toolbar__daext-logo" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'admin/assets/img/daext-logo.svg' ); ?>" alt="DAEXT" />
				</a>
			</div>
		</div>

		<?php
	}

	/**
	 * Display a section with that includes information on the Pro version. Note that the Pro Features section is
	 * displayed only in the free version.
	 *
	 * @return void
	 */
	public function display_pro_features() {

		if ( constant( 'DAEXTLNL_EDITION' ) !== 'FREE' ) {
			return;
		}

		?>

		<div class="daextlnl-admin-body">

			<div class="daextlnl-pro-features">

				<div class="daextlnl-pro-features__wrapper">

					<div class="daextlnl-pro-features__left">
						<div class="daextlnl-pro-features__title">
							<div class="daextlnl-pro-features__title-text"><?php esc_html_e( 'Unlock Advanced Features with Live News Pro', 'live-news-lite'); ?></div>
							<div class="daextlnl-pro-features__pro-badge"><?php esc_html_e( 'PRO', 'live-news-lite'); ?></div>
						</div>
						<div class="daextlnl-pro-features__description">
							<?php
							esc_html_e(
								'Automatically generate news from your posts, pull updates from any RSS feed (e.g., major news sites, TV channels, or radio stations), control advanced options like the displayed news speed and delay, and more!',
								'live-news-lite'
							);
							?>
						</div>
						<div class="daextlnl-pro-features__buttons-container">
							<a class="daextlnl-pro-features__button-1" href="https://daext.com/live-news/" target="_blank">
								<div class="daextlnl-pro-features__button-text">
									<?php esc_html_e( 'Learn More', 'live-news-lite'); ?>
								</div>
								<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
							</a>
							<a class="daextlnl-pro-features__button-2" href="https://daext.com/live-news/#pricing" target="_blank">
								<div class="daextlnl-pro-features__button-text">
									<?php esc_html_e( 'View Pricing & Upgrade', 'live-news-lite'); ?>
								</div>
								<?php
								$this->shared->echo_icon_svg( 'arrow-up-right' );
								?>
							</a>
						</div>
					</div>
					<div class="daextlnl-pro-features__right">

						<?php

						$pro_features_data_a = array(
							array(
								'icon'        => 'refresh-ccw-04',
								'name_part_1' => __( 'Live', 'live-news-lite'),
								'name_part_2' => __( 'Updates', 'live-news-lite'),
							),
							array(
								'icon'        => 'database-03',
								'name_part_1' => __( 'Multiple Data', 'live-news-lite'),
								'name_part_2' => __( 'Sources', 'live-news-lite'),
							),
							array(
								'icon'        => 'align-right-02',
								'name_part_1' => __( 'RTL', 'live-news-lite'),
								'name_part_2' => __( 'Support', 'live-news-lite'),
							),
							array(
								'icon'        => 'palette',
								'name_part_1' => __( 'Customizable', 'live-news-lite'),
								'name_part_2' => __( 'Style', 'live-news-lite'),
							),
							array(
								'icon'        => 'share-05',
								'name_part_1' => __( 'Exportable', 'live-news-lite'),
								'name_part_2' => __( 'Data', 'live-news-lite'),
							),
							array(
								'icon'        => 'file-code-02',
								'name_part_1' => __( 'Optimized', 'live-news-lite'),
								'name_part_2' => __( 'Implementation', 'live-news-lite'),
							),
						);

						foreach ( $pro_features_data_a as $key => $pro_feature_data ) {

							?>

							<div class="daextlnl-pro-features__single-feature">
								<div class="daextlnl-pro-features__single-feature-wrapper">
									<?php $this->shared->echo_icon_svg( $pro_feature_data['icon'] ); ?>
									<div class="daextlnl-pro-features__single-feature-name">
										<?php echo esc_html( $pro_feature_data['name_part_1'] ); ?>
										<br>
										<?php echo esc_html( $pro_feature_data['name_part_2'] ); ?>
									</div>
								</div>
							</div>

							<?php

						}

						?>

					</div>

				</div>

				<div class="daextlnl-pro-features__footer-wrapper">
					<div class="daextlnl-pro-features__footer-wrapper-inner">
						<div class="daextlnl-pro-features__footer-wrapper-left">
							<?php esc_html_e( 'Built for WordPress creators by the DAEXT team', 'live-news-lite'); ?>
						</div>
						<a class="daextlnl-pro-features__footer-wrapper-right" href="https://daext.com/products/" target="_blank">
							<div class="daextlnl-pro-features__footer-wrapper-right-text">
								<?php esc_html_e( 'More Tools from DAEXT', 'live-news-lite'); ?>
							</div>
							<?php $this->shared->echo_icon_svg( 'arrow-up-right' ); ?>
						</a>
					</div>
				</div>

			</div>

		</div>

		<?php
	}

	/**
	 * Handle the duplication of an item.
	 *
	 * In details, when the $_POST['clone_id'] is set, the method will duplicate the corresponding item in the
	 * database.
	 *
	 * @return void
	 */
	public function handle_duplicate() {

		$data             = array();
		$data['clone_id'] = isset( $_POST['clone_id'] ) ? intval( $_POST['clone_id'], 10 ) : null;

		// clone an item.
		if ( ! is_null( $data['clone_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_clone_' . $this->menu_slug . '_' . $data['clone_id'], 'daextlnl_clone_' . $this->menu_slug . '_nonce' );

			$this->duplicate_record( $this->db_table, $this->primary_key, $data['clone_id'] );

			$this->shared->save_dismissible_notice(
				__( 'The item has been successfully duplicated.', 'live-news-lite'),
				'updated'
			);

		}
	}

	/**
	 * Handle the deletion of an item.
	 *
	 * In details, when the $_POST['delete_id'] is set, the method will delete the corresponding item from the
	 * database.
	 *
	 * @return void
	 */
	public function handle_delete() {

		$data              = array();
		$data['delete_id'] = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;

		// delete an item.
		if ( ! is_null( $data['delete_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_delete_' . $this->menu_slug . '_' . $data['delete_id'], 'daextlnl_clone_' . $this->menu_slug . '_nonce' );

			// Check deletion conditions.
			$result = $this->item_is_deletable( $data['delete_id'] );

			// prevent deletion if the item is not deletable.
			if ( ! $result['is_deletable'] ) {

				$this->shared->save_dismissible_notice(
					$result['dismissible_notice_message'],
					'error'
				);

			} else {

				global $wpdb;

				// Sanitize the db table name.
				$db_table_name = $wpdb->prefix . 'daextlnl_' . $this->db_table;

				// Sanitize the field name.
				$primary_key = sanitize_key( $this->primary_key );

				// phpcs:disable WordPress.DB.DirectDatabaseQuery
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $db_table_name and $primary_key are sanitized above.
				$query_result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM $db_table_name WHERE $primary_key = %d", $data['delete_id'] )
				);
				// phpcs:enable

				if ( false !== $query_result ) {
					$this->shared->save_dismissible_notice(
						__( 'The item has been successfully deleted.', 'live-news-lite'),
						'updated'
					);
				}
			}
		}
	}

	/**
	 * Handle the deletion of an item.
	 *
	 * In details, when the $_POST['delete_id'] is set, the method will delete the corresponding item from the
	 * database.
	 *
	 * @return void
	 */
	public function handle_clear_cache() {

		$data                   = array();
		$data['clear_cache_id'] = isset( $_POST['clear_cache_id'] ) ? intval( $_POST['clear_cache_id'], 10 ) : null;

		// Delete the transient of an item.
		if ( ! is_null( $data['clear_cache_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_clear_cache_' . $this->menu_slug . '_' . $data['clear_cache_id'], 'daextlnl_clear_cache_' . $this->menu_slug . '_nonce' );

			// Delete the transient of the specified ticker.
			$deletion_result = delete_transient( 'daextlnl_ticker_' . $data['clear_cache_id'] );

			if ( false !== $deletion_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The cache of the selected news ticker has been successfully deleted.', 'live-news-lite'),
					'updated',
				);
			}
		}
	}

	/**
	 * Handles the processing of bulk actions.
	 *
	 * @return void
	 */
	public function handle_bulk_actions() {

		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : null;

		if ( 'delete' === $bulk_action ) {

			$delete_id = ( isset( $_POST['bulk-action-selected-items'] ) && ! empty( $_POST['bulk-action-selected-items'] ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['bulk-action-selected-items'] ) ) ) : null;

			if ( ! is_array( $delete_id ) ) {
				return;
			}

			// Convert all the $delete_id values to numeric values with base 10 using intval.
			$delete_id = array_map(
				function ( $value ) {
					return intval( $value, 10 );
				},
				$delete_id
			);

			if ( ! is_null( $delete_id ) ) {

				// Nonce verification.
				check_admin_referer( 'daextlnl_bulk_action_' . $this->menu_slug, 'daextlnl_bulk_action_' . $this->menu_slug . '_nonce' );

				$delete_id_deletable     = array();
				$delete_id_non_deletable = array();

				// Create two new array that includes deletable and non-deletable items.
				foreach ( $delete_id as $key => $value ) {

					$result = $this->item_is_deletable( $value );

					if ( $result['is_deletable'] ) {
						$delete_id_deletable[] = $value;
					} else {
						$delete_id_non_deletable[] = $value;
					}
				}

				// Delete the items.
				global $wpdb;
				$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_' . $this->db_table;

				if ( count( $delete_id_deletable ) > 0 ) {

					// Sanitize the db table name.
					$table_name = sanitize_key( $table_name );

					// Sanitize the field name.
					$primary_key_name = sanitize_key( $this->primary_key );

					// phpcs:disable WordPress.DB.DirectDatabaseQuery
					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $variables are sanitized above.
					// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
					$query_result = $wpdb->query(
						"DELETE FROM $table_name WHERE $primary_key_name IN (" . implode( ',', $delete_id_deletable ) . ')'
					);
					// phpcs:enable

				}

				if ( isset( $query_result ) && false !== $query_result ) {

					// Get the number of deleted items with $wpdb.
					$deleted_items_count = $wpdb->rows_affected;

					$this->shared->save_dismissible_notice(
						$deleted_items_count . ' ' . __( 'items have been successfully deleted.', 'live-news-lite'),
						'updated'
					);

				}

				if ( count( $delete_id_non_deletable ) > 0 ) {

					switch ( $this->menu_slug ) {

						case 'section':
							$this->shared->save_dismissible_notice(
								__( 'The', 'live-news-lite' ) . ' ' . strtolower( $this->label_plural ) . ' ' . __( "with the following IDs are used in one or more categories and can't be deleted:", 'live-news-lite') . ' ' . implode( ', ', $delete_id_non_deletable ) . '.',
								'error'
							);

							break;

						case 'category':
							$this->shared->save_dismissible_notice(
								__( 'The', 'live-news-lite' ) . ' ' . strtolower( $this->label_plural ) . ' ' . __( "with the following IDs are used in one or more cookies and can't be deleted:", 'live-news-lite') . ' ' . implode( ', ', $delete_id_non_deletable ) . '.',
								'error'
							);

							break;

					}
				}
			}
		}
	}

	/**
	 * Verify the provided user capability.
	 *
	 * Die with a message if the user does not have the required capability.
	 *
	 * @return void
	 */
	public function verify_user_capability() {

		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'live-news-lite') );
		}
	}

	/**
	 * Displays in the admin area the elements of a CRUD menu.
	 *
	 * The elements can be one of the following:
	 *
	 * - The table with the list of items.
	 * - The form to add an item.
	 * - The form to edit an item.
	 */
	public function display_crud_menu() {

		?>

		<div class="daextlnl-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required for data visualization.
			$action  = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
			$edit_id = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : null;
			// phpcs:enable

			if ( 'new' === $action ) {
				$this->new_item();
			} elseif ( null !== $edit_id ) {
				$this->edit_item( $edit_id );
			} else {

				$this->display_list_table(
					$this->db_table,
					$this->list_table_columns,
					$this->searchable_fields,
					$this->label_plural,
					$this->slug_plural,
					$this->primary_key
				);

			}

			?>

		</div>

		<?php
	}

	/**
	 * Provided the db table name and the primary_key name, and the primary_key value of the record to duplicate, the method will duplicate the record in the
	 *  database. Note that this method is generic and should work with any database table.
	 *
	 * @param string $table_name The name of the database table.
	 * @param string $primary_key_name The name of the primary key.
	 * @param string $primary_key_value The value of the primary key.
	 *
	 * @return void
	 */
	public function duplicate_record( $table_name, $primary_key_name, $primary_key_value ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_' . $table_name;

		// retrieve the record to duplicate.

		// Sanitize the db table name.
		$table_name = sanitize_key( $table_name );

		// Sanitize the field name.
		$primary_key_name = sanitize_key( $primary_key_name );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table_name and $primary_key are sanitized above.
		$record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE $primary_key_name = %d",
				$primary_key_value
			),
			ARRAY_A
		);
		// phpcs:enable

		// remove the primary key from the record.
		unset( $record[ $primary_key_name ] );

		// insert the record into the database.

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert( $table_name, $record );
	}

	/**
	 * This method echos the HTML of the form fields based on the provided form field data provided as an array with the
	 * $sections parameter.
	 *
	 * @param array $sections The sections of the form.
	 *
	 * @return void
	 */
	public function print_form_fields_from_array( $sections ) {

		// Iterate over the $data array.
		foreach ( $sections as $section ) {

			// Print the opening tag of the section.
			echo '<div class="daextlnl-main-form__daext-form-section" data-id="' . esc_attr( $section['section_id'] ) . '">';

			// If the section has a visible header, display it.
			if ( false !== $section['display_header'] ) {

				$this->section_header(
					$section['label'],
					$section['section_id'],
					$section['icon_id']
				);

			}

			// Print the opening tag of the section body.
			echo '<div class="daextlnl-main-form__daext-form-section-body" data-section-id="' . esc_attr( $section['section_id'] ) . '">';

			// Iterate over the fields of the section.
			foreach ( $section['fields'] as $field ) {

				// If the field is a text input, display it.
				if ( 'text' === $field['type'] ) {

					$this->input_field(
						$field['name'],
						$field['label'],
						$field['description'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$field['value'],
						$field['maxlength'],
						$field['required'],
						$section['section_id']
					);

				}

				// If the field is a textarea, display it.
				if ( 'textarea' === $field['type'] ) {

					$this->textarea_field(
						$field['name'],
						$field['label'],
						$field['description'],
						isset( $field['placeholder'] ) ? $field['placeholder'] : '',
						$field['value'],
						$field['maxlength'],
						$field['required'],
						$section['section_id']
					);

				}

				// If the field is a select input, display it.
				if ( 'select' === $field['type'] ) {

					$this->select_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['options'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is a select multiple input, display it.
				if ( 'select_multiple' === $field['type'] ) {

					$this->select_multiple_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['options'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is a toggle, display it.
				if ( 'toggle' === $field['type'] ) {

					$this->toggle_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['value'],
						$section['section_id']
					);

				}

				// If the field is an input range, display it.
				if ( 'input_range' === $field['type'] ) {

					$this->input_range_field(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['value'],
						$section['section_id'],
						$field['min'],
						$field['max'],
						$field['step']
					);

				}

				// If the field is a media upload, display it.
				if ( 'media_upload' === $field['type'] ) {

					$this->media_upload(
						$field['name'],
						$field['label'],
						$field['description'],
						$field['value'],
						$field['required'],
						$section['section_id'],
					);

				}
			}

			// Print the closing tag of the section body.
			echo '</div>';

			// Print the closing tag of the section.
			echo '</div>';

		}
	}

	/**
	 * Displays the content of the admin menu.
	 *
	 * @return void
	 */
	public function display_menu_content() {

		// Verify user capability.
		$this->verify_user_capability();

		// Display the Admin Toolbar.
		$this->display_admin_toolbar();

		// Display the Header Bar.
		$this->header_bar();

		// Display the main content of the menu.
		if ( 'crud' === $this->context ) {

			// Body for CRUD menus defined in the parent class.
			$this->display_crud_menu();

		} else {

			// Custom body content defined in the menu child class.
			$this->display_custom_content();

		}

		// Display the Pro features section.
		$this->display_pro_features();
	}

	/**
	 * Display the search form of the CRUD menu.
	 *
	 * @param String $post_search_input The search input value.
	 *
	 * @return void
	 */
	public function display_crud_menu_search_form( $post_search_input ) {

		// Search Form -------------------------------------------------------------------------------------------------------.

		?>

		<input type="hidden" name="items-filter" value="1">

		<form action="admin.php" method="GET" id="items-filter">

			<div class="daextlnl-crud-table-search-form">

				<div id="daext-search-form">

					<input type="hidden" name="page" value="daextlnl-<?php echo esc_attr( $this->slug_plural ); ?>">
					<input class="daextlnl-crud-table-search-form__post-search-input" type="text" name="s"
							value="<?php echo null !== $post_search_input ? esc_attr( stripslashes( $post_search_input ) ) : ''; ?>" autocomplete="off" maxlength="255">
					<input class="button daextlnl-admin-page-button" type="submit" value="Search <?php echo esc_attr( $this->label_plural ); ?>">

				</div>

			</div>

		</form>

		<?php
	}
}
