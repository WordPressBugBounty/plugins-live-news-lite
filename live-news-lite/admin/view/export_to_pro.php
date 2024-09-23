<?php
/**
 * The view for the Export to Pro page.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php esc_html_e( 'Live News - Export to Pro', $this->shared->get( 'text_domain' ) ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php esc_html_e( 'Click the Export button to generate an XML file that includes all the plugin data.', $this->shared->get( 'text_domain' ) ); ?></p>
		<p><?php esc_html_e( 'Note that you can import the resulting file in the Import menu of the ', $this->shared->get( 'text_domain' ) ); ?>
			<a href="https://daext.com/live-news/"><?php esc_html_e( 'Pro Version', $this->shared->get( 'text_domain' ) ); ?></a>.</p>

		<!-- the data sent through this form are handled by the export_xml_controller() method called with the WordPress init action -->
		<form method="POST" action="admin.php?page=daextlnl_export_to_pro">

			<div class="daext-widget-submit">
				<input name="daextlnl_export" class="button button-primary" type="submit"
						value="<?php esc_attr_e( 'Export', $this->shared->get( 'text_domain' ) ); ?>" 
												<?php
												if ( ! $this->shared->plugin_has_data() ) {
													echo 'disabled="disabled"';
												}
												?>
				>
			</div>

		</form>

	</div>

</div>