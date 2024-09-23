<?php
/**
 * The "Tickers" menu page.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_tickers_menu_capability' ) ) ) {
	wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

<!-- process data -->

<?php

// Initialize variables -------------------------------------------------------------------------------------------------.
$dismissible_notice_a = array();

// Preliminary operations -----------------------------------------------------------------------------------------------.
global $wpdb;

// Sanitization ---------------------------------------------------------------------------------------------------------.
$data['edit_id']             = isset( $_GET['edit_id'] ) ? intval( $_GET['edit_id'], 10 ) : null;
$data['delete_id']           = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;
$data['update_id']           = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
$data['form_submitted']      = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;
$data['delete_transient_id'] = isset( $_POST['delete_transient_id'] ) ? intval( $_POST['delete_transient_id'], 10 ) : null;

// Filter and search data.
$data['s'] = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;

if ( ! is_null( $data['update_id'] ) or ! is_null( $data['form_submitted'] ) ) {

	// Nonce verification.
	check_admin_referer( 'daextlnl_create_update_ticker', 'daextlnl_create_update_ticker_nonce' );

	// Sanitization -----------------------------------------------------------------------------------------------------.
	$name          = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : null;
	$target        = isset( $_POST['target'] ) ? intval( $_POST['target'], 10 ) : null;
	$url           = isset( $_POST['url'] ) ? sanitize_textarea_field( $_POST['url'] ) : null;
	$enable_ticker = isset( $_POST['enable_ticker'] ) ? intval( $_POST['enable_ticker'], 10 ) : null;

	$clock_source = isset( $_POST['clock_source'] ) ? intval( $_POST['clock_source'], 10 ) : null;
	$clock_offset = isset( $_POST['clock_offset'] ) ? intval( $_POST['clock_offset'], 10 ) : null;
	$clock_format = isset( $_POST['clock_format'] ) ? sanitize_text_field( $_POST['clock_format'] ) : null;

	$enable_rtl_layout          = isset( $_POST['enable_rtl_layout'] ) ? intval( $_POST['enable_rtl_layout'], 10 ) : null;
	$enable_with_mobile_devices = isset( $_POST['enable_with_mobile_devices'] ) ? intval( $_POST['enable_with_mobile_devices'], 10 ) : null;
	$hide_featured_news         = isset( $_POST['hide_featured_news'] ) ? intval( $_POST['hide_featured_news'], 10 ) : null;
	$open_news_as_default       = isset( $_POST['open_news_as_default'] ) ? intval( $_POST['open_news_as_default'], 10 ) : null;
	$enable_links               = isset( $_POST['enable_links'] ) ? intval( $_POST['enable_links'], 10 ) : null;
	$open_links_new_tab         = isset( $_POST['open_links_new_tab'] ) ? intval( $_POST['open_links_new_tab'], 10 ) : null;
	$hide_clock                 = isset( $_POST['hide_clock'] ) ? intval( $_POST['hide_clock'], 10 ) : null;
	$clock_autoupdate           = isset( $_POST['clock_autoupdate'] ) ? intval( $_POST['clock_autoupdate'], 10 ) : null;
	$clock_autoupdate_time      = isset( $_POST['clock_autoupdate_time'] ) ? intval( $_POST['clock_autoupdate_time'], 10 ) : null;
	$number_of_sliding_news     = isset( $_POST['number_of_sliding_news'] ) ? intval( $_POST['number_of_sliding_news'], 10 ) : null;

	$cached_cycles        = isset( $_POST['cached_cycles'] ) ? intval( $_POST['cached_cycles'], 10 ) : null;
	$transient_expiration = isset( $_POST['transient_expiration'] ) ? intval( $_POST['transient_expiration'], 10 ) : null;

	$featured_title_maximum_length          = isset( $_POST['featured_title_maximum_length'] ) ? intval( $_POST['featured_title_maximum_length'], 10 ) : null;
	$featured_excerpt_maximum_length        = isset( $_POST['featured_excerpt_maximum_length'] ) ? intval( $_POST['featured_excerpt_maximum_length'], 10 ) : null;
	$sliding_news_maximum_length            = isset( $_POST['sliding_news_maximum_length'] ) ? intval( $_POST['sliding_news_maximum_length'], 10 ) : null;
	$featured_title_font_size               = isset( $_POST['featured_title_font_size'] ) ? intval( $_POST['featured_title_font_size'], 10 ) : null;
	$featured_excerpt_font_size             = isset( $_POST['featured_excerpt_font_size'] ) ? intval( $_POST['featured_excerpt_font_size'], 10 ) : null;
	$sliding_news_font_size                 = isset( $_POST['featured_excerpt_font_size'] ) ? intval( $_POST['sliding_news_font_size'], 10 ) : null;
	$clock_font_size                        = isset( $_POST['clock_font_size'] ) ? intval( $_POST['clock_font_size'], 10 ) : null;
	$sliding_news_margin                    = isset( $_POST['sliding_news_margin'] ) ? intval( $_POST['sliding_news_margin'], 10 ) : null;
	$sliding_news_padding                   = isset( $_POST['sliding_news_padding'] ) ? intval( $_POST['sliding_news_padding'], 10 ) : null;
	$font_family                            = isset( $_POST['font_family'] ) ? sanitize_text_field( $_POST['font_family'] ) : null;
	$google_font                            = isset( $_POST['google_font'] ) ? esc_url_raw( $_POST['google_font'] ) : null;
	$featured_news_title_color              = isset( $_POST['featured_news_title_color'] ) ? sanitize_text_field( $_POST['featured_news_title_color'] ) : null;
	$featured_news_title_color_hover        = isset( $_POST['featured_news_title_color_hover'] ) ? sanitize_text_field( $_POST['featured_news_title_color_hover'] ) : null;
	$featured_news_excerpt_color            = isset( $_POST['featured_news_excerpt_color'] ) ? sanitize_text_field( $_POST['featured_news_excerpt_color'] ) : null;
	$sliding_news_color                     = isset( $_POST['sliding_news_color'] ) ? sanitize_text_field( $_POST['sliding_news_color'] ) : null;
	$sliding_news_color_hover               = isset( $_POST['sliding_news_color_hover'] ) ? sanitize_text_field( $_POST['sliding_news_color_hover'] ) : null;
	$clock_text_color                       = isset( $_POST['clock_text_color'] ) ? sanitize_text_field( $_POST['clock_text_color'] ) : null;
	$featured_news_background_color         = isset( $_POST['featured_news_background_color'] ) ? sanitize_text_field( $_POST['featured_news_background_color'] ) : null;
	$featured_news_background_color_opacity = isset( $_POST['featured_news_background_color_opacity'] ) ? floatval( $_POST['featured_news_background_color_opacity'] ) : null;
	$sliding_news_background_color          = isset( $_POST['sliding_news_background_color'] ) ? sanitize_text_field( $_POST['sliding_news_background_color'] ) : null;
	$sliding_news_background_color_opacity  = isset( $_POST['sliding_news_background_color_opacity'] ) ? floatval( $_POST['sliding_news_background_color_opacity'] ) : null;
	$open_button_image                      = isset( $_POST['open_button_image'] ) ? esc_url_raw( $_POST['open_button_image'] ) : null;
	$close_button_image                     = isset( $_POST['close_button_image'] ) ? esc_url_raw( $_POST['close_button_image'] ) : null;
	$clock_background_image                 = isset( $_POST['clock_background_image'] ) ? esc_url_raw( $_POST['clock_background_image'] ) : null;

	$url_mode = isset( $_POST['url_mode'] ) ? intval( $_POST['url_mode'], 10 ) : null;

	// Validation -------------------------------------------------------------------------------------------------------.

	$invalid_data_message = '';

	// validation on "Name".
	if ( mb_strlen( trim( $name ) ) == 0 or mb_strlen( $name ) > 100 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid value in the "Name" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured Title Maximum Length".
	if ( intval( $featured_title_maximum_length, 10 ) < 1 or intval( $featured_title_maximum_length, 10 ) > 1000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 1000 in the "Featured Title Maximum Length" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured Excerpt Maximum Length".
	if ( intval( $featured_excerpt_maximum_length, 10 ) < 1 or intval( $featured_excerpt_maximum_length, 10 ) > 1000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 1000 in the "Featured Excerpt Maximum Length" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Maximum Length".
	if ( intval( $sliding_news_maximum_length, 10 ) < 1 or intval( $sliding_news_maximum_length, 10 ) > 1000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 1000 in the "Sliding News Maximum Length" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Title Font Size".
	if ( intval( $featured_title_font_size, 10 ) < 1 or intval( $featured_title_font_size, 10 ) > 38 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 38 in the "Featured News Title Font Size" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
	}

	// validation on "Featured News Excerpt Font Size".
	if ( intval( $featured_excerpt_font_size, 10 ) < 1 or intval( $featured_excerpt_font_size, 10 ) > 28 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 28 in the "Featured News Excerpt Font Size" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Font Size"
	if ( intval( $sliding_news_font_size, 10 ) < 1 or intval( $sliding_news_font_size, 10 ) > 28 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 28 in the "Sliding News Font Size" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Clock Font Size".
	if ( intval( $clock_font_size, 10 ) < 1 or intval( $clock_font_size, 10 ) > 28 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 1 and 28 in the "Clock Font Size" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Margin".
	if ( intval( $sliding_news_margin, 10 ) < 0 or intval( $sliding_news_margin, 10 ) > 999 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 999 in the "Sliding News Margin" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Padding".
	if ( intval( $sliding_news_padding, 10 ) < 0 or intval( $sliding_news_padding, 10 ) > 999 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 999 in the "Sliding News Padding" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Cached Cycles".
	if ( intval( $cached_cycles, 10 ) < 0 or intval( $cached_cycles, 10 ) > 1000000000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 1000000000 in the "Cached Cycles" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Transient Expiration".
	if ( intval( $transient_expiration, 10 ) < 0 or intval( $transient_expiration, 10 ) > 1000000000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 1000000000 in the "Transient Expiration" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Background Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $featured_news_background_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Featured News Background Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Background Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $sliding_news_background_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Sliding News Background Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Background Color Opacity".
	if ( $sliding_news_background_color_opacity < 0 or $sliding_news_background_color_opacity > 1 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 1 in the "Sliding News Background Color Opacity" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Font Family".
	if ( ! preg_match( $this->shared->font_family_regex, stripslashes( $font_family ) ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid value in the "Font Family" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Google Font".
	if ( mb_strlen( $google_font ) > 2083 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid value in the "Google Font" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Open Button Image".
	if ( mb_strlen( $open_button_image ) > 2083 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid URL in the "Open Button Image" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Close Button Image".
	if ( mb_strlen( $close_button_image ) > 2083 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid URL in the "Close Button Image" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Clock Background Image".
	if ( mb_strlen( $clock_background_image ) > 2083 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid URL in the "Clock Background Image" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Title Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $featured_news_title_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Featured News Title Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Title Color Hover".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $featured_news_title_color_hover ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Featured News Title Color Hover" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Excerpt Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $featured_news_excerpt_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Featured News Excerpt Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $sliding_news_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Sliding News Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Sliding News Color Hover".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $sliding_news_color_hover ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Sliding News Color Hover" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Clock Text Color".
	if ( ! preg_match( $this->shared->hex_rgb_regex, $clock_text_color ) ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid color in the "Clock Text Color" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Featured News Background Color Opacity".
	if ( $featured_news_background_color_opacity < 0 or $featured_news_background_color_opacity > 1 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a value included between 0 and 1 in the "Featured News Background Color Opacity" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// do not save (and leave an error message) if a ticker with "Website" as a target already exists.
	if ( $target == 1 ) {

		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$row_obj    = $wpdb->get_row( "SELECT * from $table_name WHERE target = 1" );
		if ( $row_obj !== null ) {

			if ( is_null( $data['update_id'] ) or ( ! is_null( $data['update_id'] ) and intval( $row_obj->id, 10 ) !== $data['update_id'] ) ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'A news ticker with "Website" as a target already exists.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}
		}
	}
}

// update ---------------------------------------------------------------.
if ( ! is_null( $data['update_id'] ) and ! isset( $invalid_data ) ) {

	// update the database
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
	$safe_sql   = $wpdb->prepare(
		"UPDATE $table_name SET
                name = %s,
                target = %d,
                url = %s,
                open_links_new_tab = %d,
                clock_offset = %d,
                clock_format = %s,
                clock_source = %d,
                clock_autoupdate = %d,
                clock_autoupdate_time = %d,
                number_of_sliding_news = %d,
                featured_title_maximum_length = %d,
                featured_excerpt_maximum_length = %d,
                sliding_news_maximum_length = %d,
                open_news_as_default = %d,
                hide_featured_news = %d,
                hide_clock = %d,
                enable_rtl_layout = %d,
                cached_cycles = %d,
                featured_news_background_color = %s,
                sliding_news_background_color = %s,
                sliding_news_background_color_opacity = %f,
                font_family = %s,
                google_font = %s,
                featured_title_font_size = %d,
				featured_excerpt_font_size = %d,
				sliding_news_font_size = %d,
				clock_font_size = %d,
				sliding_news_margin = %d,
				sliding_news_padding = %d,
                enable_with_mobile_devices = %d,
                open_button_image = %s,
                close_button_image = %s,
                clock_background_image = %s,
                featured_news_title_color = %s,
                featured_news_title_color_hover = %s,
                featured_news_excerpt_color = %s,
                sliding_news_color = %s,
                sliding_news_color_hover = %s,
                clock_text_color = %s,
                featured_news_background_color_opacity = %s,
                enable_ticker = %d,
                enable_links = %d,
                transient_expiration = %d,
                url_mode = %d
                WHERE id = %d",
		$name,
		$target,
		$url,
		$open_links_new_tab,
		$clock_offset,
		$clock_format,
		$clock_source,
		$clock_autoupdate,
		$clock_autoupdate_time,
		$number_of_sliding_news,
		$featured_title_maximum_length,
		$featured_excerpt_maximum_length,
		$sliding_news_maximum_length,
		$open_news_as_default,
		$hide_featured_news,
		$hide_clock,
		$enable_rtl_layout,
		$cached_cycles,
		$featured_news_background_color,
		$sliding_news_background_color,
		$sliding_news_background_color_opacity,
		$font_family,
		$google_font,
		$featured_title_font_size,
		$featured_excerpt_font_size,
		$sliding_news_font_size,
		$clock_font_size,
		$sliding_news_margin,
		$sliding_news_padding,
		$enable_with_mobile_devices,
		$open_button_image,
		$close_button_image,
		$clock_background_image,
		$featured_news_title_color,
		$featured_news_title_color_hover,
		$featured_news_excerpt_color,
		$sliding_news_color,
		$sliding_news_color_hover,
		$clock_text_color,
		$featured_news_background_color_opacity,
		$enable_ticker,
		$enable_links,
		$transient_expiration,
		$url_mode,
		$data['update_id']
	);

	$query_result = $wpdb->query( $safe_sql );

	if ( $query_result !== false ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'The news ticker has been successfully updated.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'updated',
		);
	}
} else {

	// add ------------------------------------------------------------------.
	if ( ! is_null( $data['form_submitted'] ) and ! isset( $invalid_data ) ) {

		// insert into the database
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare(
			"INSERT INTO $table_name SET
                    name = %s,
                    target = %d,
                    url = %s,
                    open_links_new_tab = %d,
                    clock_offset = %d,
                    clock_format = %s,
                    clock_source = %d,
                    clock_autoupdate = %d,
                    clock_autoupdate_time = %d,
                    number_of_sliding_news = %d,
                    featured_title_maximum_length = %d,
                    featured_excerpt_maximum_length = %d,
                    sliding_news_maximum_length = %d,
                    open_news_as_default = %d,
                    hide_featured_news = %d,
                    hide_clock = %d,
                    enable_rtl_layout = %d,
                    cached_cycles = %d,
                    featured_news_background_color = %s,
                    sliding_news_background_color = %s,
                    sliding_news_background_color_opacity = %f,
                    font_family = %s,
                    google_font = %s,
					featured_title_font_size = %d,
					featured_excerpt_font_size = %d,
					sliding_news_font_size = %d,
					clock_font_size = %d,
					sliding_news_margin = %d,
					sliding_news_padding = %d,
                    enable_with_mobile_devices = %d,
                    open_button_image = %s,
                    close_button_image = %s,
                    clock_background_image = %s,
                    featured_news_title_color = %s,
                    featured_news_title_color_hover = %s,
                    featured_news_excerpt_color = %s,
                    sliding_news_color = %s,
                    sliding_news_color_hover = %s,
                    clock_text_color = %s,
                    featured_news_background_color_opacity = %s,
                    enable_ticker = %d,
                    enable_links = %d,
                    transient_expiration = %d,
                    url_mode = %d",
			$name,
			$target,
			$url,
			$open_links_new_tab,
			$clock_offset,
			$clock_format,
			$clock_source,
			$clock_autoupdate,
			$clock_autoupdate_time,
			$number_of_sliding_news,
			$featured_title_maximum_length,
			$featured_excerpt_maximum_length,
			$sliding_news_maximum_length,
			$open_news_as_default,
			$hide_featured_news,
			$hide_clock,
			$enable_rtl_layout,
			$cached_cycles,
			$featured_news_background_color,
			$sliding_news_background_color,
			$sliding_news_background_color_opacity,
			$font_family,
			$google_font,
			$featured_title_font_size,
			$featured_excerpt_font_size,
			$sliding_news_font_size,
			$clock_font_size,
			$sliding_news_margin,
			$sliding_news_padding,
			$enable_with_mobile_devices,
			$open_button_image,
			$close_button_image,
			$clock_background_image,
			$featured_news_title_color,
			$featured_news_title_color_hover,
			$featured_news_excerpt_color,
			$sliding_news_color,
			$sliding_news_color_hover,
			$clock_text_color,
			$featured_news_background_color_opacity,
			$enable_ticker,
			$enable_links,
			$transient_expiration,
			$url_mode
		);

		$query_result = $wpdb->query( $safe_sql );

		if ( $query_result !== false ) {
			$dismissible_notice_a[] = array(
				'message' => __( 'The news ticker has been successfully added.', $this->shared->get( 'text_domain' ) ),
				'class'   => 'updated',
			);
		}
	}
}

// delete a transient.
if ( ! is_null( $data['delete_transient_id'] ) ) {

	// Nonce verification.
	check_admin_referer(
		'daextlnl_delete_ticker_transient_' . $data['delete_transient_id'],
		'daextlnl_delete_ticker_transient_nonce'
	);

	$deletion_result = delete_transient( 'daextlnl_ticker_' . intval( $data['delete_transient_id'], 10 ) );

	if ( $deletion_result !== false ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'The transient has been successfully deleted.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'updated',
		);
	}
}

// delete a ticker.
if ( ! is_null( $data['delete_id'] ) ) {

	// Nonce verification.
	check_admin_referer(
		'daextlnl_delete_ticker_' . $data['delete_id'],
		'daextlnl_delete_ticker_nonce'
	);

	// delete the ticker only if it's not used by sliding news or featured news.
	if ( $this->shared->ticker_is_used( $data['delete_id'] ) ) {

		$dismissible_notice_a[] = array(
			'message' => __( "This news ticker is associated with one or more news and can't be deleted.", $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);

	} else {

		// delete the transient of the ticker.
		delete_transient( 'daextlnl_ticker_' . $data['delete_id'] );

		// delete this ticker.
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d ", $data['delete_id'] );

		$query_result = $wpdb->query( $safe_sql );

		if ( $query_result !== false ) {

			$dismissible_notice_a[] = array(
				'message' => __( 'The news ticker has been successfully deleted.', $this->shared->get( 'text_domain' ) ),
				'class'   => 'updated',
			);

		}
	}
}

// get the tickers data.
$display_form = true;
if ( ! is_null( $data['edit_id'] ) ) {
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
	$safe_sql   = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d ", $data['edit_id'] );
	$ticker_obj = $wpdb->get_row( $safe_sql );
	if ( $ticker_obj === null ) {
		$display_form = false;
	}
}

?>

<!-- output -->

<div class="wrap">

	<?php if ( $this->shared->get_number_of_tickers() > 0 ) : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_html_e( 'Live News - News Tickers', $this->shared->get( 'text_domain' ) ); ?></h2>

			<!-- Search Form -->

			<form action="admin.php" method="get" id="daext-search-form">

				<input type="hidden" name="page" value="daextlnl-tickers">

				<p><?php esc_html_e( 'Perform your Search', $this->shared->get( 'text_domain' ) ); ?></p>

				<?php
				if ( ! is_null( $data['s'] ) ) {
					if ( mb_strlen( trim( $data['s'] ) ) > 0 ) {
						$search_string = $data['s'];
					} else {
						$search_string = '';
					}
				} else {
					$search_string = '';
				}

				?>

				<input type="text" name="s" name="s"
						value="<?php echo esc_attr( stripslashes( $search_string ) ); ?>" autocomplete="off" maxlength="255">
				<input type="submit" value="">

			</form>

		</div>

	<?php else : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_html_e( 'Live News - News Tickers', $this->shared->get( 'text_domain' ) ); ?></h2>

		</div>

	<?php endif; ?>

	<div id="daext-menu-wrapper">

		<?php $this->dismissible_notice( $dismissible_notice_a ); ?>

		<!-- table -->

		<?php

		// create the query part used to filter the results when a search is performed.
		if ( ! is_null( $data['s'] ) ) {
			if ( mb_strlen( trim( $data['s'] ) ) > 0 ) {
				$filter = $wpdb->prepare( 'WHERE (name LIKE %s)', '%' . $data['s'] . '%' );
			} else {
				$filter = '';
			}
		} else {
			$filter = '';
		}

		// retrieve the total number of sliding news.
		$table_name  = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $filter" );

		// Initialize the pagination class.
		require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextlnl-pagination.php';
		$pag = new daextlnl_pagination();
		$pag->set_total_items( $total_items );// Set the total number of items
		$pag->set_record_per_page( 10 ); // Set records per page
		$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-tickers' );// Set target page
		$pag->set_current_page();// set the current page number

		?>

		<!-- Query the database -->
		<?php
		$query_limit = $pag->query_limit();
		$results     = $wpdb->get_results( "SELECT * FROM $table_name $filter ORDER BY id DESC $query_limit ", ARRAY_A );
		?>

		<?php if ( count( $results ) > 0 ) : ?>

			<div class="daext-items-container">

				<!-- list of tables -->
				<table class="daext-items">
					<thead>
					<tr>
						<th>
							<div><?php esc_html_e( 'Name', $this->shared->get( 'text_domain' ) ); ?></div>
							<div class="help-icon" title="<?php esc_attr_e( 'The name of the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
						</th>
						<th>
							<div><?php esc_html_e( 'Target', $this->shared->get( 'text_domain' ) ); ?></div>
							<div class="help-icon" title="<?php esc_attr_e( 'The target of the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
						</th>
						<th></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $results as $result ) : ?>
						<tr>
							<td><?php echo esc_html( stripslashes( $result['name'] ) ); ?></td>
							<td><?php echo esc_html( $this->shared->get_textual_target( $result['target'] ) ); ?></td>
							<td class="icons-container">
								<?php if ( get_transient( 'daextlnl_ticker_' . $result['id'] ) !== false ) : ?>
									<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tickers">
										<?php
										wp_nonce_field(
											'daextlnl_delete_ticker_transient_' . $result['id'],
											'daextlnl_delete_ticker_transient_nonce'
										);
										?>
										<input type="hidden" value="<?php echo esc_attr( $result['id'] ); ?>" name="delete_transient_id" >
										<input class="menu-icon update" type="submit" value="">
									</form>
								<?php else : ?>
									<div class="empty-icon-container"></div>
								<?php endif; ?>
								<a class="menu-icon edit" href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tickers&edit_id=<?php echo esc_attr( $result['id'] ); ?>"></a>
								<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tickers">
									<?php wp_nonce_field( 'daextlnl_delete_ticker_' . $result['id'], 'daextlnl_delete_ticker_nonce' ); ?>
									<input type="hidden" value="<?php echo esc_attr( $result['id'] ); ?>" name="delete_id" >
									<input class="menu-icon delete" type="submit" value="">
								</form>
							</td>
						</tr>
					<?php endforeach; ?>

					</tbody>

				</table>

			</div>

			<!-- Display the pagination -->
			<?php if ( $pag->total_items > 0 ) : ?>
				<div class="daext-tablenav daext-clearfix">
					<div class="daext-tablenav-pages">
						<span class="daext-displaying-num"><?php echo esc_html( $pag->total_items ); ?> <?php esc_html_e( 'items', $this->shared->get( 'text_domain' ) ); ?></span>
						<?php $pag->show(); ?>
					</div>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( $display_form ) : ?>

			<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-tickers" autocomplete="off">

				<input type="hidden" value="1" name="form_submitted">
				<?php wp_nonce_field( 'daextlnl_create_update_ticker', 'daextlnl_create_update_ticker_nonce' ); ?>

				<?php if ( ! is_null( $data['edit_id'] ) ) : ?>

				<!-- Edit a Ticker -->

				<div class="daext-form-container">

					<h3 class="daext-form-title"><?php esc_html_e( 'Edit News Ticker', $this->shared->get( 'text_domain' ) ); ?> <?php echo esc_html( $ticker_obj->id ); ?></h3>

					<table class="daext-form">

						<input type="hidden" name="update_id" value="<?php echo esc_attr( $ticker_obj->id ); ?>" />

						<!-- Name -->
						<tr valign="top">
							<th scope="row"><label for="name"><?php esc_html_e( 'Name', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->name ) ); ?>" type="text" id="name" maxlength="100" size="30" name="name"/>
								<div class="help-icon" title="<?php esc_attr_e( 'The name of the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Target -->
						<tr valign="top">
							<th scope="row"><label for="target"><?php esc_html_e( 'Target', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<select id="target" name="target" class="daext-display-none">
									<option value="1" <?php selected( $ticker_obj->target, 1 ); ?>><?php esc_html_e( 'Website', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="2" <?php selected( $ticker_obj->target, 2 ); ?>><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'This selection determines if the news ticker should be applied to the entire website or to a specific URL. Note that a news ticker associated with an URL has the priority over the news ticker associated with the entire website.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- URL -->
						<tr valign="top">
							<th scope="row"><label for="url"><?php esc_html_e( 'Target URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<textarea type="text" id="url" maxlength="20830000" size="30" name="url"><?php echo esc_html( stripslashes( $ticker_obj->url ) ); ?></textarea>
								<div class="help-icon" title="<?php esc_attr_e( 'Enter one or more URLs. (one URL per line) This option is used only if the target of the news ticker is "URL".', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Enable Ticker -->
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable Ticker', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="enable-ticker" name="enable_ticker" class="daext-display-none">
									<option value="0" <?php selected( $ticker_obj->enable_ticker, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->enable_ticker, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Use this option to enable or disable the news ticker on the front-end.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<tr class="group-trigger" data-trigger-target="source-chart-configuration">
							<th scope="row" class="group-title"><?php esc_html_e( 'Source', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<div class="expand-icon"></div>
							</td>
						</tr>

						<!-- Clock Source -->
						<tr class="source-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Clock Source', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="clock-source" name="clock_source">
									<option value="1" <?php selected( $ticker_obj->clock_source, 1 ); ?>><?php esc_html_e( 'Server Time', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="2" <?php selected( $ticker_obj->clock_source, 2 ); ?>><?php esc_html_e( 'User Time', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select if the time should be based on the server time or on the user time.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Clock Offset -->
						<tr class="source-chart-configuration">
							<th scope="row"><label for="clock-offset"><?php esc_html_e( 'Clock Offset', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo intval( $ticker_obj->clock_offset, 10 ); ?>" type="text" id="clock-offset" maxlength="6" size="30" name="clock_offset" />
								<div class="help-icon" title="<?php esc_attr_e( 'The clock offset in seconds. Positive or negative values are allowed.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Clock Format -->
						<tr class="source-chart-configuration">
							<th scope="row"><label for="clock-format"><?php esc_html_e( 'Clock Format', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->clock_format ) ); ?>" type="text" id="clock-format" maxlength="40" size="30" name="clock_format" />
								<div class="help-icon" title="<?php esc_attr_e( 'Use this field to specify the clock format. The tokens supported by Moment.js should be used.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<tr class="group-trigger" data-trigger-target="behavior-chart-configuration">
							<th scope="row" class="group-title"><?php esc_html_e( 'Behavior', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<div class="expand-icon"></div>
							</td>
						</tr>

						<!-- Enable RTL Layout -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Enable RTL Layout', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="enable-rtl-layout" name="enable_rtl_layout">
									<option value="0" <?php selected( $ticker_obj->enable_rtl_layout, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->enable_rtl_layout, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select whether to enable or not the RTL layout.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Enable with Mobile Devices -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Enable with Mobile Devices', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="enable-with-mobile-devices" name="enable_with_mobile_devices">
									<option value="0" <?php selected( $ticker_obj->enable_with_mobile_devices, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->enable_with_mobile_devices, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select whether to display or not the news ticker with mobile devices. The user-agent string combined with specific HTTP headers are used to determine the device.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Hide Featured News -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Hide Featured News', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="hide-featured-news" name="hide_featured_news">
									<option value="1" <?php selected( $ticker_obj->hide_featured_news, 1 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="2" <?php selected( $ticker_obj->hide_featured_news, 2 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="3" <?php selected( $ticker_obj->hide_featured_news, 3 ); ?>><?php esc_html_e( 'Only with Mobile Devices', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select if the featured news area of the news ticker should be displayed.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Open News as Default -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Open News as Default', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="open-news-as-default" name="open_news_as_default">
									<option value="0" <?php selected( $ticker_obj->open_news_as_default, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->open_news_as_default, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select if the news ticker should be presented in the open status (with the featured news area visible) to the users. If the user opens or closes the news ticker the new status will be saved in a cookie and used to determine the default status of the news ticker for that specific user.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Enable Links -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Enable Links', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="enable-links" name="enable_links">
									<option value="0" <?php selected( $ticker_obj->enable_links, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->enable_links, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Whether to apply or not the links associated with the news on the featured news title and on the sliding news.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Open Links New Tab -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Open Links in New Tab', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="open-links-new-tab" name="open_links_new_tab">
									<option value="0" <?php selected( $ticker_obj->open_links_new_tab, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->open_links_new_tab, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select if the links availble in the news ticker should be opened in a new tab.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Hide Clock -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Hide Clock', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="hide-clock" name="hide_clock">
									<option value="0" <?php selected( $ticker_obj->hide_clock, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->hide_clock, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select whether to display or not the clock.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Clock Autoupdate -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><?php esc_html_e( 'Clock Autoupdate', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="clock-autoupdate" name="clock_autoupdate">
									<option value="0" <?php selected( $ticker_obj->clock_autoupdate, 0 ); ?>><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->clock_autoupdate, 1 ); ?>><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select whether to autoupdate or not the clock independently from the cycles of news received. This option is applied only if the source of the clock is "User Time".', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

						<!-- Clock Autoupdate Time -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><label for="clock-autoupdate-time"><?php esc_html_e( 'Clock Autoupdate Time', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo intval( $ticker_obj->clock_autoupdate_time, 10 ); ?>" type="text" id="clock-autoupdate-time" maxlength="10" size="30" name="clock_autoupdate_time" />
								<div class="help-icon" title="<?php esc_attr_e( 'This option determines how frequent should be the clock autoupdate in seconds.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Number of Sliding News -->
						<tr class="behavior-chart-configuration">
							<th scope="row"><label for="number-of-sliding-news"><?php esc_html_e( 'Number of Sliding News', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->number_of_sliding_news, 10 ) ); ?>" type="text" id="number-of-sliding-news" maxlength="2" size="30" name="number_of_sliding_news" />
								<div class="help-icon" title="<?php esc_attr_e( 'Enter the number of sliding news that you want to display in a single cycle of news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<tr class="group-trigger" data-trigger-target="performance-chart-configuration">
							<th scope="row" class="group-title"><?php esc_html_e( 'Performance', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<div class="expand-icon"></div>
							</td>
						</tr>

						<!-- Cached Cycled -->
						<tr class="performance-chart-configuration">
							<th scope="row"><label for="cached-cycles"><?php esc_html_e( 'Cached Cycles', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->cached_cycles, 10 ) ); ?>" type="text" id="cached-cycles" maxlength="10" size="30" name="cached_cycles" />
								<div class="help-icon" title="<?php esc_attr_e( 'This value determines the number of cycles performed by the news ticker without updating the news. Set an high value to improve the news ticker performance and to avoid an excessive load on the web server. Set a low value to have frequent updates of the news. Set 0 to update the news at every cycle.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Transient Expiration -->
						<tr class="performance-chart-configuration">
							<th scope="row"><label for="clock-background-image"><?php esc_html_e( 'Transient Expiration', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo intval( $ticker_obj->transient_expiration, 10 ); ?>" type="text" id="transient-expiration" maxlength="10" size="30" name="transient_expiration"/>
								<div class="help-icon" title="<?php esc_attr_e( 'Enter the transient expiration in seconds. Set an high value to improve the news ticker performance and to avoid an excessive load on the web server. Set a low value to have frequent updates of the news. Set 0 to not use a transient.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<tr class="group-trigger" data-trigger-target="style-chart-configuration">
							<th scope="row" class="group-title"><?php esc_html_e( 'Style', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<div class="expand-icon"></div>
							</td>
						</tr>

						<!-- Featured Title Maximum Length -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-title-maximum-length"><?php esc_html_e( 'Featured News Title Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->featured_title_maximum_length, 10 ) ); ?>" type="text" id="featured-title-maximum-length" maxlength="4" size="30" name="featured_title_maximum_length" />
								<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured Excerpt Maximum Length -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-excerpt-maximum-length"><?php esc_html_e( 'Featured News Excerpt Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->featured_excerpt_maximum_length, 10 ) ); ?>" type="text" id="featured-excerpt-maximum-length" maxlength="4" size="30" name="featured_excerpt_maximum_length" />
								<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Maximum Length -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-maximum-length"><?php esc_html_e( 'Sliding News Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->sliding_news_maximum_length, 10 ) ); ?>" type="text" id="sliding-news-maximum-length" maxlength="4" size="30" name="sliding_news_maximum_length" />
								<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured Title Font Size -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-title-font-size"><?php esc_html_e( 'Featured News Title Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->featured_title_font_size, 10 ) ); ?>" type="text" id="featured-title-font-size" maxlength="2" size="30" name="featured_title_font_size" />
								<div class="help-icon" title="<?php esc_attr_e( 'The font size of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured Excerpt Font Size -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-excerpt-font-size"><?php esc_html_e( 'Featured News Excerpt Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->featured_excerpt_font_size, 10 ) ); ?>" type="text" id="featured-excerpt-font-size" maxlength="2" size="30" name="featured_excerpt_font_size" />
								<div class="help-icon" title="<?php esc_attr_e( 'The font size of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Font Size -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-font-size"><?php esc_html_e( 'Sliding News Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->sliding_news_font_size, 10 ) ); ?>" type="text" id="sliding-news-font-size" maxlength="2" size="30" name="sliding_news_font_size" />
								<div class="help-icon" title="<?php esc_attr_e( 'The font size of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Clock Font Size -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="clock-font-size"><?php esc_html_e( 'Clock Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->clock_font_size, 10 ) ); ?>" type="text" id="clock-font-size" maxlength="2" size="30" name="clock_font_size" />
								<div class="help-icon" title="<?php esc_attr_e( 'The font size of the text in the clock.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Margin -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-margin"><?php esc_html_e( 'Sliding News Margin', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->sliding_news_margin, 10 ) ); ?>" type="text" id="sliding-news-margin" maxlength="3" size="30" name="sliding_news_margin" />
								<div class="help-icon" title="<?php esc_attr_e( 'The margin between the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Padding -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-padding"><?php esc_html_e( 'Sliding News Padding', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo abs( intval( $ticker_obj->sliding_news_padding, 10 ) ); ?>" type="text" id="sliding-news-padding" maxlength="3" size="30" name="sliding_news_padding" />
								<div class="help-icon" title="<?php esc_attr_e( 'This option determines the padding on the left and on the right of each sliding news and also the distance between the sliding news text and the sliding news left and right images.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Font Family -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="font-family"><?php esc_html_e( 'Font Family', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->font_family ) ); ?>" type="text" id="font-family" maxlength="255" size="30" name="font_family" />
								<div class="help-icon" title="<?php esc_attr_e( 'The font family used for all the text displayed in the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Google Font -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="google-font"><?php esc_html_e( 'Google Font', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_url( stripslashes( $ticker_obj->google_font ) ); ?>" type="text" id="google-font" maxlength="255" size="30" name="google_font" />
								<div class="help-icon" title="<?php esc_attr_e( 'This option allows you to load a specific Google Font.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured News Title Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-news-title-color"><?php esc_html_e( 'Featured News Title Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->featured_news_title_color ) ); ?>" class="wp-color-picker" type="text" id="featured-news-title-color" maxlength="7" size="30" name="featured_news_title_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured News Title Color Hover -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-news-title-color-hover"><?php esc_html_e( 'Featured News Title Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->featured_news_title_color_hover ) ); ?>" class="wp-color-picker" type="text" id="featured-news-title-color-hover" maxlength="7" size="30" name="featured_news_title_color_hover" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news title in hover status.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured News Excerpt Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-news-excerpt-color"><?php esc_html_e( 'Featured News Excerpt Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->featured_news_excerpt_color ) ); ?>" class="wp-color-picker" type="text" id="featured-news-excerpt-color" maxlength="7" size="30" name="featured_news_excerpt_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-color"><?php esc_html_e( 'Sliding News Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->sliding_news_color ) ); ?>" class="wp-color-picker" type="text" id="sliding-news-color" maxlength="7" size="30" name="sliding_news_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Color Hover -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-color-hover"><?php esc_html_e( 'Sliding News Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->sliding_news_color_hover ) ); ?>" class="wp-color-picker" type="text" id="sliding-news-color-hover" maxlength="7" size="30" name="sliding_news_color_hover" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the sliding news in hover status.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Clock Text Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="clock-text-color"><?php esc_html_e( 'Clock Text Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->clock_text_color ) ); ?>" class="wp-color-picker" type="text" id="clock-text-color" maxlength="7" size="30" name="clock_text_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The color of the text displayed in the clock.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured News Background Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-news-background-color"><?php esc_html_e( 'Featured News Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->featured_news_background_color ) ); ?>" class="wp-color-picker" type="text" id="featured-news-background-color" maxlength="7" size="30" name="featured_news_background_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The background color of the featured news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Featured News Background Color Opacity -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="featured-news-background-color-opacity"><?php esc_html_e( 'Featured News Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo floatval( $ticker_obj->featured_news_background_color_opacity ); ?>" type="text" id="featured-news-background-color-opacity" maxlength="3" size="30" name="featured_news_background_color_opacity" />
								<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of the featured news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Background Color -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-background-color"><?php esc_html_e( 'Sliding News Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo esc_attr( stripslashes( $ticker_obj->sliding_news_background_color ) ); ?>" class="wp-color-picker" type="text" id="sliding-news-background-color" maxlength="7" size="30" name="sliding_news_background_color" />
								<div class="help-icon" title="<?php esc_attr_e( 'The background color of the sliding news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Sliding News Background Color Opacity -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="sliding-news-background-color-opacity"><?php esc_html_e( 'Sliding News Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>
								<input value="<?php echo floatval( stripslashes( $ticker_obj->sliding_news_background_color_opacity ) ); ?>" type="text" id="sliding-news-background-color-opacity" maxlength="3" size="30" name="sliding_news_background_color_opacity" />
								<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of the sliding news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</td>
						</tr>

						<!-- Open Button Image -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="open-button-image"><?php esc_html_e( 'Open Button Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>

								<div class="image-uploader">
									<img class="selected-image" src="<?php echo esc_attr( stripslashes( $ticker_obj->open_button_image ) ); ?>" <?php echo mb_strlen( trim( $ticker_obj->open_button_image ) ) == 0 ? 'style="display: none;"' : ''; ?>>
									<input value="<?php echo esc_attr( stripslashes( $ticker_obj->open_button_image ) ); ?>" type="hidden" id="open-button-image" maxlength="2083" name="open_button_image">
									<a class="button_add_media" data-set-remove="<?php echo mb_strlen( trim( $ticker_obj->open_button_image ) ) == 0 ? 'set' : 'remove'; ?>" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php echo mb_strlen( trim( $ticker_obj->open_button_image ) ) == 0 ? esc_attr__( 'Set image', $this->shared->get( 'text_domain' ) ) : esc_attr__( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?></a>
									<p class="description"><?php esc_attr_e( "Select the image of the button used to open the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
								</div>

							</td>
						</tr>

						<!-- Close Button Image -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="open-button-image"><?php esc_html_e( 'Close Button Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>

								<div class="image-uploader">
									<img class="selected-image" src="<?php echo esc_attr( stripslashes( $ticker_obj->close_button_image ) ); ?>" <?php echo mb_strlen( trim( $ticker_obj->close_button_image ) ) == 0 ? 'style="display: none;"' : ''; ?>>
									<input value="<?php echo esc_attr( stripslashes( $ticker_obj->close_button_image ) ); ?>" type="hidden" id="close-button-image" maxlength="2083" name="close_button_image">
									<a class="button_add_media" data-set-remove="<?php echo mb_strlen( trim( $ticker_obj->close_button_image ) ) == 0 ? 'set' : 'remove'; ?>" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php echo mb_strlen( trim( $ticker_obj->close_button_image ) ) == 0 ? esc_attr__( 'Set image', $this->shared->get( 'text_domain' ) ) : esc_attr__( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?></a>
									<p class="description"><?php esc_attr_e( "Select the image of the button used to close the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
								</div>

							</td>
						</tr>

						<!-- Clock Background Image -->
						<tr class="style-chart-configuration">
							<th scope="row"><label for="clock-background-image"><?php esc_html_e( 'Clock Background Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
							<td>

								<div class="image-uploader">
									<img class="selected-image" src="<?php echo esc_attr( stripslashes( $ticker_obj->clock_background_image ) ); ?>" <?php echo mb_strlen( trim( $ticker_obj->clock_background_image ) ) == 0 ? 'style="display: none;"' : ''; ?>>
									<input value="<?php echo esc_attr( stripslashes( $ticker_obj->clock_background_image ) ); ?>" type="hidden" id="clock-background-image" maxlength="2083" name="clock_background_image">
									<a class="button_add_media" data-set-remove="<?php echo mb_strlen( trim( $ticker_obj->clock_background_image ) ) == 0 ? 'set' : 'remove'; ?>" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php echo mb_strlen( trim( $ticker_obj->clock_background_image ) ) == 0 ? esc_attr__( 'Set image', $this->shared->get( 'text_domain' ) ) : esc_attr__( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?></a>
									<p class="description"><?php esc_attr_e( "Select the background image of the clock. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
								</div>

							</td>
						</tr>

						<tr class="group-trigger" data-trigger-target="section-advanced">
							<th scope="row" class="group-title"><?php esc_html_e( 'Advanced', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<div class="expand-icon"></div>
							</td>
						</tr>

						<!-- Target URL Mode -->
						<tr class="section-advanced">
							<th scope="row"><?php esc_html_e( 'Target URL Mode', $this->shared->get( 'text_domain' ) ); ?></th>
							<td>
								<select id="url-mode" name="url_mode">
									<option value="0" <?php selected( $ticker_obj->url_mode, 0 ); ?>><?php esc_html_e( 'Include', $this->shared->get( 'text_domain' ) ); ?></option>
									<option value="1" <?php selected( $ticker_obj->url_mode, 1 ); ?>><?php esc_html_e( 'Exclude', $this->shared->get( 'text_domain' ) ); ?></option>
								</select>
								<div class="help-icon" title='<?php esc_attr_e( 'Select whether to include or exclude the URLs defined with the "Target URL" option.', $this->shared->get( 'text_domain' ) ); ?>'></div>
							</td>
						</tr>

					</table>

					<!-- submit button -->
					<div class="daext-form-action">
						<input class="button" type="submit" value="<?php esc_attr_e( 'Update News Ticker', $this->shared->get( 'text_domain' ) ); ?>" >
					</div>

					<?php else : ?>

					<!-- Create New Ticker -->

					<div class="daext-form-container">

						<div class="daext-form-title"><?php esc_html_e( 'Create a News Ticker', $this->shared->get( 'text_domain' ) ); ?></div>

						<table class="daext-form">

							<!-- Name -->
							<tr valign="top">
								<th scope="row"><label for="name"><?php esc_html_e( 'Name', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input type="text" id="name" maxlength="100" size="30" name="name" />
									<div class="help-icon" title="<?php esc_attr_e( 'The name of the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Target -->
							<tr valign="top">
								<th scope="row"><label for="target"><?php esc_html_e( 'Target', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<select id="target" name="target" class="daext-display-none">
										<option value="1"><?php esc_html_e( 'Website', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="2"><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'This selection determines if the news ticker should be applied to the entire website or to a specific URL. Note that a news ticker associated with an URL has the priority over the news ticker associated with the entire website.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- URL -->
							<tr valign="top">
								<th scope="row"><label for="url"><?php esc_html_e( ' Target URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<textarea type="text" id="url" maxlength="20830000" size="30" name="url"></textarea>
									<div class="help-icon" title="<?php esc_attr_e( 'Enter one or more URLs. (one URL per line) This option is used only if the target of the news ticker is "URL".', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Enable Ticker -->
							<tr>
								<th scope="row"><?php esc_html_e( 'Enable Ticker', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="enable-ticker" name="enable_ticker" class="daext-display-none">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1" selected="selected"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Use this option to enable or disable the news ticker on the front-end.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<tr class="group-trigger" data-trigger-target="source-chart-configuration">
								<th scope="row" class="group-title"><?php esc_html_e( 'Source', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Clock Source -->
							<tr class="source-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Clock Source', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="clock-source" name="clock_source">
										<option value="1"><?php esc_html_e( 'Server Time', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="2" selected="selected"><?php esc_html_e( 'User Time', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select if the time should be based on the server time or on the user time. Please note that by selecting "Server Time" the clock will be updated only when the news are retrieved from the server, therefore this option should not be used if you are caching cycles of news or if you are using transients.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Clock Offset -->
							<tr class="source-chart-configuration">
								<th scope="row"><label for="clock-offset"><?php esc_html_e( 'Clock Offset', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="0" type="text" id="clock-offset" maxlength="6" size="30" name="clock_offset" />
									<div class="help-icon" title="<?php esc_attr_e( 'The clock offset in seconds. Positive or negative values are allowed.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Clock Format -->
							<tr class="source-chart-configuration">
								<th scope="row"><label for="clock-format"><?php esc_html_e( 'Clock Format', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="HH:mm" type="text" id="clock-format" maxlength="40" size="30" name="clock_format" />
									<div class="help-icon" title="<?php esc_attr_e( 'Use this field to specify the clock format. The tokens supported by Moment.js should be used.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<tr class="group-trigger" data-trigger-target="behavior-chart-configuration">
								<th scope="row" class="group-title"><?php esc_html_e( 'Behavior', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Enable RTL Layout -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Enable RTL Layout', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="enable-rtl-layout" name="enable_rtl_layout">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select whether to enable or not the RTL layout.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Enable with Mobile Devices -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Enable with Mobile Devices', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="enable-with-mobile-devices" name="enable_with_mobile_devices">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select whether to display or not the news ticker with mobile devices. The user-agent string combined with specific HTTP headers are used to determine the device.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Hide Featured News -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Hide Featured News', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="hide-featured-news" name="hide_featured_news">
										<option value="1"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="2"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="3"><?php esc_html_e( 'Only with Mobile Devices', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select if the featured news area of the news ticker should be displayed.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Open News as Default -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Open News as Default', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="open-news-as-default" name="open_news_as_default">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1" selected="selected"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select if the news ticker should be presented in the open status (with the featured news area visible) to the users. If the user opens or closes the news ticker the new status will be saved in a cookie and used to determine the default status of the news ticker for that specific user.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Enable Links -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Enable Links', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="enable-links" name="enable_links">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1" selected="selected"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Whether to apply or not the links associated with the news on the featured news title and on the sliding news.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Open Links New Tab -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Open Links in New Tab', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="open-links-new-tab" name="open_links_new_tab">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select if the links availble in the news ticker should be opened in a new tab.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Hide Clock -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Hide Clock', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="hide-clock" name="hide_clock">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select whether to display or not the clock.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Clock Autoupdate -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><?php esc_html_e( 'Clock Autoupdate', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="clock-autoupdate" name="clock_autoupdate">
										<option value="0"><?php esc_html_e( 'No', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1" selected="selected"><?php esc_html_e( 'Yes', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select whether to autoupdate or not the clock independently from the cycles of news received. This option is applied only if the source of the clock is "User Time".', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

							<!-- Clock Autoupdate Time -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><label for="clock-autoupdate-time"><?php esc_html_e( 'Clock Autoupdate Time', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="10" type="text" id="clock-autoupdate-time" maxlength="10" size="30" name="clock_autoupdate_time" />
									<div class="help-icon" title="<?php esc_attr_e( 'This option determines how frequent should be the clock autoupdate in seconds.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Number of Sliding News -->
							<tr class="behavior-chart-configuration">
								<th scope="row"><label for="number-of-sliding-news"><?php esc_html_e( 'Number of Sliding News', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="10" type="text" id="number-of-sliding-news" maxlength="2" size="30" name="number_of_sliding_news" />
									<div class="help-icon" title="<?php esc_attr_e( 'Enter the number of sliding news that you want to display in a single cycle of news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<tr class="group-trigger" data-trigger-target="performance-chart-configuration">
								<th scope="row" class="group-title"><?php esc_html_e( 'Performance', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Cached Cycles -->
							<tr class="performance-chart-configuration">
								<th scope="row"><label for="cached-cycles"><?php esc_html_e( 'Cached Cycles', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="5" type="text" id="cached-cycles" maxlength="10" size="30" name="cached_cycles" />
									<div class="help-icon" title="<?php esc_attr_e( 'This value determines the number of cycles performed by the news ticker without updating the news. Set an high value to improve the news ticker performance and to avoid an excessive load on the web server. Set a low value to have frequent updates of the news. Set 0 to update the news at every cycle.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Transient Expiration -->
							<tr class="performance-chart-configuration">
								<th scope="row"><label for="transient-expiration"><?php esc_html_e( 'Transient Expiration', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="0" type="text" id="transient-expiration" maxlength="10" size="30" name="transient_expiration"/>
									<div class="help-icon" title="<?php esc_attr_e( 'Enter the transient expiration in seconds. Set an high value to improve the news ticker performance and to avoid an excessive load on the web server. Set a low value to have frequent updates of the news. Set 0 to not use a transient.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<tr class="group-trigger" data-trigger-target="style-chart-configuration">
								<th scope="row" class="group-title"><?php esc_html_e( 'Style', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Featured Title Maximum Length -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-title-maximum-length"><?php esc_html_e( 'Featured News Title Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="280" type="text" id="featured-title-maximum-length" maxlength="4" size="30" name="featured_title_maximum_length" />
									<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured Excerpt Maximum Length -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-excerpt-maximum-length"><?php esc_html_e( 'Featured News Excerpt Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="280" type="text" id="featured-excerpt-maximum-length" maxlength="4" size="30" name="featured_excerpt_maximum_length" />
									<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Maximum Length -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-maximum-length"><?php esc_html_e( 'Sliding News Maximum Length', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="280" type="text" id="sliding-news-maximum-length" maxlength="4" size="30" name="sliding_news_maximum_length" />
									<div class="help-icon" title="<?php esc_attr_e( 'The maximum length of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured Title Font Size -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-title-font-size"><?php esc_html_e( 'Featured News Title Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="38" type="text" id="featured-title-font-size" maxlength="2" size="30" name="featured_title_font_size" />
									<div class="help-icon" title="<?php esc_attr_e( 'The font size of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured Excerpt Font Size -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-excerpt-font-size"><?php esc_html_e( 'Featured News Excerpt Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="28" type="text" id="featured-excerpt-font-size" maxlength="2" size="30" name="featured_excerpt_font_size" />
									<div class="help-icon" title="<?php esc_attr_e( 'The font size of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Font Size -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-font-size"><?php esc_html_e( 'Sliding News Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="28" type="text" id="sliding-news-font-size" maxlength="2" size="30" name="sliding_news_font_size" />
									<div class="help-icon" title="<?php esc_attr_e( 'The font size of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Clock Font Size -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="clock-font-size"><?php esc_html_e( 'Clock Font Size', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="28" type="text" id="clock-font-size" maxlength="2" size="30" name="clock_font_size" />
									<div class="help-icon" title="<?php esc_attr_e( 'The font size of the text in the clock.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Margin -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-margin"><?php esc_html_e( 'Sliding News Margin', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="84" type="text" id="sliding-news-margin" maxlength="3" size="30" name="sliding_news_margin" />
									<div class="help-icon" title="<?php esc_attr_e( 'The margin between the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Padding -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-padding"><?php esc_html_e( 'Sliding News Padding', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="28" type="text" id="sliding-news-padding" maxlength="3" size="30" name="sliding_news_padding" />
									<div class="help-icon" title="<?php esc_attr_e( 'This option determines the padding on the left and on the right of each sliding news and also the distance between the sliding news text and the sliding news left and right images.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Font Family -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="font-family"><?php esc_html_e( 'Font Family', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="'Open Sans', sans-serif" type="text" id="font-family" maxlength="255" size="30" name="font_family" />
									<div class="help-icon" title="<?php esc_attr_e( 'The font family used for all the text displayed in the news ticker.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Google Font -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="google-font"><?php esc_html_e( 'Google Font', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" type="text" id="google-font" maxlength="255" size="30" name="google_font" />
									<div class="help-icon" title="<?php esc_attr_e( 'This option allows you to load a specific Google Font.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured News Title Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-news-title-color"><?php esc_html_e( 'Featured News Title Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#eee" class="wp-color-picker" type="text" id="featured-news-title-color" maxlength="7" size="30" name="featured_news_title_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news title.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured News Title Color Hover -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-news-title-color-hover"><?php esc_html_e( 'Featured News Title Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#111" class="wp-color-picker" type="text" id="featured-news-title-color-hover" maxlength="7" size="30" name="featured_news_title_color_hover" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news title in hover status.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured News Excerpt Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-news-excerpt-color"><?php esc_html_e( 'Featured News Excerpt Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#eee" class="wp-color-picker" type="text" id="featured-news-excerpt-color" maxlength="7" size="30" name="featured_news_excerpt_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the featured news excerpt.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-color"><?php esc_html_e( 'Sliding News Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#eee" class="wp-color-picker" type="text" id="sliding-news-color" maxlength="7" size="30" name="sliding_news_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Color Hover -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-color-hover"><?php esc_html_e( 'Sliding News Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#aaa" class="wp-color-picker" type="text" id="sliding-news-color-hover" maxlength="7" size="30" name="sliding_news_color_hover" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the sliding news in hover status.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Clock Text Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="clock-text-color"><?php esc_html_e( 'Clock Text Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#111" class="wp-color-picker" type="text" id="clock-text-color" maxlength="7" size="30" name="clock_text_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The color of the text displayed in the clock.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured News Background Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-news-background-color"><?php esc_html_e( 'Featured News Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#C90016" class="wp-color-picker" type="text" id="featured-news-background-color" maxlength="7" size="30" name="featured_news_background_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The background color of the featured news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Featured News Background Color Opacity -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="featured-news-background-color-opacity"><?php esc_html_e( 'Featured News Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="1" type="text" id="featured-news-background-color-opacity" maxlength="3" size="30" name="featured_news_background_color_opacity" />
									<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of the featured news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Background Color -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-background-color"><?php esc_html_e( 'Sliding News Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="#000000" class="wp-color-picker" type="text" id="sliding-news-background-color" maxlength="7" size="30" name="sliding_news_background_color" />
									<div class="help-icon" title="<?php esc_attr_e( 'The background color of the sliding news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Sliding News Background Color Opacity -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="sliding-news-background-color-opacity"><?php esc_html_e( 'Sliding News Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="1" type="text" id="sliding-news-background-color-opacity" maxlength="3" size="30" name="sliding_news_background_color_opacity" />
									<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of the sliding news area.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Open Button Image -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="open-button-image"><?php esc_html_e( 'Open Button Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>

									<div class="image-uploader">
										<img class="selected-image" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/open-button.png' ); ?>" >
										<input value="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/open-button.png' ); ?>" type="hidden" id="open-button-image" maxlength="2083" name="open_button_image">
										<a class="button_add_media" data-set-remove="remove" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php esc_attr_e( 'Remove image', $this->shared->get( 'text_domain' ) ); ?></a>
										<p class="description"><?php esc_html_e( "Select the image of the button used to open the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
									</div>

								</td>
							</tr>

							<!-- Close Button Image -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="close-button-image"><?php esc_html_e( 'Close Button Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>

									<div class="image-uploader">
										<img class="selected-image" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/close-button.png' ); ?>" >
										<input value="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/close-button.png' ); ?>" type="hidden" id="close-button-image" maxlength="2083" name="close_button_image">
										<a class="button_add_media" data-set-remove="remove" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php esc_html_e( 'Remove image', $this->shared->get( 'text_domain' ) ); ?></a>
										<p class="description"><?php esc_html_e( "Select the image of the button used to close the news ticker. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
									</div>

								</td>
							</tr>

							<!-- Clock Background Image -->
							<tr class="style-chart-configuration">
								<th scope="row"><label for="clock-background-image"><?php esc_html_e( 'Clock Background Image', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>

									<div class="image-uploader">
										<img class="selected-image" src="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/clock.png' ); ?>" >
										<input value="<?php echo esc_url( $this->shared->get( 'url' ) . 'public/assets/img/clock.png' ); ?>" type="hidden" id="clock-background-image" maxlength="2083" name="clock_background_image">
										<a class="button_add_media" data-set-remove="remove" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php esc_html_e( 'Remove image', $this->shared->get( 'text_domain' ) ); ?></a>
										<p class="description"><?php esc_html_e( "Select the background image of the clock. It's recommended to use an image with a width of 80 pixels and a height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
									</div>

								</td>
							</tr>

							<tr class="group-trigger" data-trigger-target="section-advanced">
								<th scope="row" class="group-title"><?php esc_html_e( 'Advanced', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<div class="expand-icon"></div>
								</td>
							</tr>

							<!-- Target URL Mode -->
							<tr class="section-advanced">
								<th scope="row"><?php esc_html_e( 'Target URL Mode', $this->shared->get( 'text_domain' ) ); ?></th>
								<td>
									<select id="url-mode" name="url_mode">
										<option value="0"><?php esc_html_e( 'Include', $this->shared->get( 'text_domain' ) ); ?></option>
										<option value="1"><?php esc_html_e( 'Exclude', $this->shared->get( 'text_domain' ) ); ?></option>
									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'Select whether to include or exclude the URLs defined with the "Target URL" option.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

						</table>

						<!-- submit button -->
						<div class="daext-form-action">
							<input class="button" type="submit" value="<?php esc_attr_e( 'Add News Ticker', $this->shared->get( 'text_domain' ) ); ?>" >
						</div>

						<?php endif; ?>

					</div>

			</form>

		<?php endif; ?>

	</div>

</div>