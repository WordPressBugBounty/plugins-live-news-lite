<?php
/**
 * Plugin Name: Live News
 * Description: The Live News plugin generates a fixed news ticker that you can use to communicate the latest news, financial news, weather warnings, election results, sports results, etc. (Lite version)
 * Version: 1.08
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: live-news-lite
 *
 * @package live-news-lite
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die(); }

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextlnl-shared.php';

// Public.
require_once plugin_dir_path( __FILE__ ) . 'public/class-daextlnl-public.php';
add_action( 'plugins_loaded', array( 'Daextlnl_Public', 'get_instance' ) );

//Admin
if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-daextlnl-admin.php' );

	// If this is not an AJAX request, create a new singleton instance of the admin class.
	if(! defined( 'DOING_AJAX' ) || ! DOING_AJAX ){
		add_action( 'plugins_loaded', array( 'Daextlnl_Admin', 'get_instance' ) );
	}

	// Activate the plugin using only the class static methods.
	register_activation_hook( __FILE__, array( 'Daextlnl_Admin', 'ac_activate' ) );

}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

	// Admin.
	require_once plugin_dir_path( __FILE__ ) . 'class-daextlnl-ajax.php';
	add_action( 'plugins_loaded', array( 'Daextlnl_Ajax', 'get_instance' ) );

}

/**
 * Customize the action links in the "Plugins" menu.
 *
 * @param $actions
 *
 * @return mixed
 */
function daextlnl_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/live-news/">' . esc_html__( 'Buy the Pro Version', 'live-news-lite' ) . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextlnl_customize_action_links' );
