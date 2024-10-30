<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package live-news-lite
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Daextlnl_Tools_Menu_Elements extends Daextlnl_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'tool';
		$this->slug_plural    = 'tools';
		$this->label_singular = __( 'Tool', 'live-news-lite');
		$this->label_plural   = __( 'Tools', 'live-news-lite');
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 *  1. Sanitization
	 *  2. Validation
	 *  3. Database update
	 *
	 * @return false|void
	 */
	public function process_form() {

		// process the export button click. (export) ------------------------------------------------------------------.

		/**
		 * Intercept requests that come from the "Export" button of the "Tools -> Export" menu and generate the
		 * downloadable XML file
		 */
		if ( isset( $_POST['daextlnl_export'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_tools_export', 'daextlnl_tools_export' );

			// Verify capability.
			if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tools_menu_capability' ) ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'live-news-lite') );
			}

			// Generate the header of the XML file.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/xml; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=live-news-' . time() . '.xml' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// Generate initial part of the XML file.
			echo '<?xml version="1.0" encoding="UTF-8" ?>';
			echo '<root>';

			// Generate the XML of the various db tables.
			$this->shared->convert_db_table_to_xml( 'tickers', 'id' );
			$this->shared->convert_db_table_to_xml( 'featured_news', 'id' );
			$this->shared->convert_db_table_to_xml( 'sliding_news', 'id' );

			// Generate the final part of the XML file.
			echo '</root>';

			die();

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

			<div class="daextlnl-tools-menu">

				<div class="daextlnl-main-form">

				<div class="daextlnl-main-form__wrapper-half">

					<div class="daextlnl-main-form__daext-form-section">

						<div class="daextlnl-main-form__section-header">
							<div class="daextlnl-main-form__section-header-title">
								<?php $this->shared->echo_icon_svg( 'log-out-04' ); ?>
								<div class="daextlnl-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'live-news-lite'); ?></div>
							</div>
						</div>

						<div class="daextlnl-main-form__daext-form-section-body">

							<!-- Export form -->

							<div>
								<?php
								esc_html_e(
									'Click the Export button to generate an XML file that includes the feedback data.',
									'live-news-lite'
								);
								?>
							</div>

							<div>
								<?php esc_html_e( 'Note that you can import the resulting file in the Tools menu of the ', 'live-news-lite' ); ?>
								<a href="https://daext.com/live-news/" target="_blank"><?php esc_html_e( 'Pro Version', 'live-news-lite' ); ?></a> <?php esc_html_e( 'to quickly transition between the two plugin editions.', 'live-news-lite' ); ?>
							</div>

							<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->
							<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>">

								<div class="daext-widget-submit">
									<?php wp_nonce_field( 'daextlnl_tools_export', 'daextlnl_tools_export' ); ?>
									<input name="daextlnl_export" class="daextlnl-btn daextlnl-btn-primary" type="submit"
											value="<?php esc_attr_e( 'Export', 'live-news-lite'); ?>"
										<?php
										if ( ! $this->shared->exportable_data_exists() ) {
											echo 'disabled="disabled"';
										}
										?>
									>
								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

			</div>

		</div>

		<?php
	}
}
