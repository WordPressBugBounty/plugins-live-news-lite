<?php
/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 *
 * @package live-news-lite
 */

/**
 * Class used to implement the back-end functionalities of the "Maintenance" menu.
 */
class Daextlnl_Maintenance_Menu_Elements extends Daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'maintenance';
		$this->slug_plural    = 'maintenance';
		$this->label_singular = __( 'Maintenance', 'live-news-lite');
		$this->label_plural   = __( 'Maintenance', 'live-news-lite');
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

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		if ( isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_execute_task', 'daextlnl_execute_task_nonce' );

			// Sanitization ---------------------------------------------------------------------------------------------------.
			$data['task'] = isset( $_POST['task'] ) ? intval( $_POST['task'], 10 ) : null;

			// Validation -----------------------------------------------------------------------------------------------------.

			$invalid_data_message = '';
			$invalid_data         = false;

			if ( false === $invalid_data ) {

				switch ( $data['task'] ) {

					// Reset Plugin.
					case 0:
						// Delete the records of all the database tables of the plugin.
						$this->shared->reset_plugin_database_tables();

						// Set the default values of the options.
						$this->shared->reset_plugin_options();

						// Add the dismissible notice message.
						$this->shared->save_dismissible_notice(
							__( 'The plugin data have been successfully deleted.', 'live-news-lite'),
							'updated'
						);

						break;

					// Reset Options.
					case 1:
						// Delete all the transients associated with the tickers.
						global $wpdb;

						// phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$results = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}daextlnl_tickers", ARRAY_A );
						foreach ( $results as $result ) {
							delete_transient( 'daextlnl_ticker_' . $result['id'] );
						}

						// Add the dismissible notice message.
						$this->shared->save_dismissible_notice(
							__( 'The transients have been successfully deleted.', 'live-news-lite'),
							'updated'
						);

						break;

				}
			}
		}
	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="daextlnl-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->display_dismissible_notices();

			?>

			<div class="daextlnl-main-form">

				<form id="form-maintenance" method="POST"
						action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-maintenance"
						autocomplete="off">

					<div class="daextlnl-main-form__daext-form-section">

						<div class="daextlnl-main-form__daext-form-section-body">

							<input type="hidden" value="1" name="form_submitted">

							<?php wp_nonce_field( 'daextlnl_execute_task', 'daextlnl_execute_task_nonce' ); ?>

							<?php

							// Task.
							$this->select_field(
								'task',
								'Task',
								__( 'The task that should be performed.', 'live-news-lite'),
								array(
									'0' => __( 'Reset Plugin', 'live-news-lite'),
									'1' => __( 'Delete Transients', 'live-news-lite'),
								),
								null,
								'main'
							);

							?>

							<!-- submit button -->
							<div class="daext-form-action">
								<input id="execute-task" class="daextlnl-btn daextlnl-btn-primary" type="submit"
										value="<?php esc_attr_e( 'Execute Task', 'live-news-lite'); ?>">
							</div>

						</div>

					</div>

				</form>

			</div>

		</div>

		<!-- Dialog Confirm -->
		<div id="dialog-confirm" title="<?php esc_attr_e( 'Maintenance Task', 'live-news-lite'); ?>" class="daext-display-none">
			<p><?php esc_html_e( 'Do you really want to proceed?', 'live-news-lite'); ?></p>
		</div>

		<?php
	}
}
