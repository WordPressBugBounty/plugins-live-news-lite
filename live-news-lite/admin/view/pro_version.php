<?php
/**
 * The "Pro Version" menu page.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

<!-- output -->

<div class="wrap">

	<h2><?php esc_html_e( 'Live News - Pro Version', $this->shared->get( 'text_domain' ) ); ?></h2>

	<div id="daext-menu-wrapper">

		<p><?php echo esc_html__( 'For professional users, we distribute a', $this->shared->get( 'text_domain' ) ) . ' <a href="https://daext.com/live-news/">' . esc_html__( 'Pro Version', $this->shared->get( 'text_domain' ) ) . '</a> ' . esc_html__( 'of this plugin.', $this->shared->get( 'text_domain' ) ) . '</p>'; ?>
		<h2><?php esc_html_e( 'Additional Features Included in the Pro Version', $this->shared->get( 'text_domain' ) ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Automatically generate news based on your posts', $this->shared->get( 'text_domain' ) ); ?></li>
			<li><?php esc_html_e( 'Automatically generate the news based on a specified RSS feed (E.g. Your own RSS feed, the RSS feed of a tv channel, the RSS feed of a radio station.)', $this->shared->get( 'text_domain' ) ); ?></li>
			<li><?php esc_html_e( 'Automatically generate the news based on a specified Twitter account', $this->shared->get( 'text_domain' ) ); ?></li>
			<li><?php esc_html_e( 'Control the speed and the delay of the sliding news with advanced options of the news ticker', $this->shared->get( 'text_domain' ) ); ?></li>
		</ul>
		<h2><?php esc_html_e( 'Additional Benefits of the Pro Version', $this->shared->get( 'text_domain' ) ); ?></h2>
		<ul>
			<li><?php esc_html_e( '24 hours support provided seven days a week', $this->shared->get( 'text_domain' ) ); ?></li>
			<li><?php echo esc_html__( '30 day money back guarantee (more information is available on the', $this->shared->get( 'text_domain' ) ) . ' <a href="https://daext.com/refund-policy/">' . esc_html__( 'Refund Policy', $this->shared->get( 'text_domain' ) ) . '</a> ' . esc_html__( 'page', $this->shared->get( 'text_domain' ) ) . ')'; ?></li>
		</ul>
		<h2><?php esc_html_e( 'Get Started', $this->shared->get( 'text_domain' ) ); ?></h2>
		<p><?php echo esc_html__( 'Download the', $this->shared->get( 'text_domain' ) ) . ' <a href="https://daext.com/live-news/">' . esc_html__( 'Pro Version', $this->shared->get( 'text_domain' ) ) . '</a> ' . esc_html__( 'now by selecting one of the available licenses.', $this->shared->get( 'text_domain' ) ); ?></p>
	</div>

</div>