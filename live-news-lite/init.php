<?php
/**
 * Plugin Name: Live News
 * Description: The Live News plugin generates a fixed news ticker that you can use to communicate the latest news, financial news, weather warnings, election results, sports results, etc. (Lite version)
 * Version: 1.09
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: live-news-lite
 * License: GPLv3
 *
 * @package live-news-lite
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// Set constants.
define( 'DAEXTLNL_EDITION', 'FREE' );

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextlnl-shared.php';

// Public.
require_once plugin_dir_path( __FILE__ ) . 'public/class-daextlnl-public.php';
add_action( 'plugins_loaded', array( 'Daextlnl_Public', 'get_instance' ) );

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextlnl-rest.php';
add_action( 'plugins_loaded', array( 'Daextlnl_Rest', 'get_instance' ) );

// Admin.
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextlnl-admin.php';

// If it's the admin area and this is not an AJAX request, create a new singleton instance of the admin class.
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	add_action( 'plugins_loaded', array( 'daextlnl_Admin', 'get_instance' ) );
}

// Activate.
register_activation_hook( __FILE__, array( 'Daextlnl_Admin', 'ac_activate' ) );

// Update the plugin db tables and options if they are not up-to-date.
Daextlnl_Admin::ac_create_database_tables();
Daextlnl_Admin::ac_initialize_options();

// Register AJAX actions.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	require_once plugin_dir_path( __FILE__ ) . 'class-daextlnl-ajax.php';
	add_action( 'plugins_loaded', array( 'Daextlnl_Ajax', 'get_instance' ) );
}

/**
 * Customize the action links in the "Plugins" menu.
 *
 * @param array $actions An array of plugin action links.
 *
 * @return mixed
 */
function daextlnl_customize_action_links( $actions ) {
	$actions[] = '<a href="https://daext.com/live-news/" target="_blank">' . esc_html__( 'Buy the Pro Version', 'live-news-lite' ) . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextlnl_customize_action_links' );
