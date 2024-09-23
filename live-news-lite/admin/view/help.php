<?php
/**
 * The Help Page in the admin menu.
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php esc_html_e( 'Live News - Help', $this->shared->get( 'text_domain' ) ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php esc_html_e( 'Visit the resources below to find your answers or to ask questions directly to the plugin developers.', $this->shared->get( 'text_domain' ) ); ?></p>
		<ul>
			<li><a href="https://daext.com/doc/live-news/"><?php esc_html_e( 'Plugin Documentation', $this->shared->get( 'text_domain' ) ); ?></a></li>
			<li><a href="https://daext.com/support/"><?php esc_html_e( 'Support Conditions', $this->shared->get( 'text_domain' ) ); ?></li>
			<li><a href="https://daext.com"><?php esc_html_e( 'Developer Website', $this->shared->get( 'text_domain' ) ); ?></a></li>
			<li><a href="https://daext.com/live-news/"><?php esc_html_e( 'Pro Version', $this->shared->get( 'text_domain' ) ); ?></a></li>
			<li><a href="https://wordpress.org/plugins/live-news-lite/"><?php esc_html_e( 'WordPress.org Plugin Page', $this->shared->get( 'text_domain' ) ); ?></a></li>
			<li><a href="https://wordpress.org/support/plugin/live-news-lite/"><?php esc_html_e( 'WordPress.org Support Forum', $this->shared->get( 'text_domain' ) ); ?></a></li>
		</ul>
		<p>

	</div>

