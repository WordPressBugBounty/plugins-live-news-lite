<?php
/**
 * The file used to display the "Featured News" menu in the admin area.
 *
 * @package live-news-lite
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_featured_news_menu_capability' );
$this->menu_elements->context    = 'crud';
$this->menu_elements->display_menu_content();
