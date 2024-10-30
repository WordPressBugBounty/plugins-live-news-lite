<?php
/**
 * File used to uninstall Live News plugin.
 *
 * @package live-news-lite
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die(); }

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextlnl-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextlnl-admin.php';

// Delete options and tables.
Daextlnl_Admin::un_delete();
