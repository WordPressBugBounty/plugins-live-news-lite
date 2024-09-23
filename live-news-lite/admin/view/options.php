<?php
/**
 * Options page.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient capabilities to access this page.', $this->shared->get( 'text_domain' ) ) );
}

// Sanitization -------------------------------------------------------------------------------------------------
$data['settings_updated'] = isset( $_GET['settings-updated'] ) ? sanitize_key( $_GET['settings-updated'], 10 ) : null;
$data['active_tab']       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

?>

<div class="wrap">

	<h2><?php esc_html_e( 'Live News - Options', $this->shared->get( 'text_domain' ) ); ?></h2>

	<?php

	// settings errors.
	if ( ! is_null( $data['settings_updated'] ) and $data['settings_updated'] == 'true' ) {
		settings_errors();
	}

	?>

	<div id="daext-options-wrapper">

		<div class="nav-tab-wrapper">
			<a href="?page=daextlnl-options&tab=general"
				class="nav-tab <?php echo $data['active_tab'] === 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', $this->shared->get( 'text_domain' ) ); ?></a>
		</div>

		<form method='post' action='options.php'>

			<?php

			if ( $data['active_tab'] === 'general' ) {

				settings_fields( $this->shared->get( 'slug' ) . '_general_options' );
				do_settings_sections( $this->shared->get( 'slug' ) . '_general_options' );

			}

			?>

			<div class="daext-options-action">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Save Changes', $this->shared->get( 'text_domain' ) ); ?>">
			</div>

		</form>

	</div>

</div>