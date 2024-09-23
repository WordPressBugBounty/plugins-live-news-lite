<?php
/**
 * The admin featured news page.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_featured_menu_capability' ) ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

<!-- process data -->

<?php

// Initialize variables -----------------------------------------------------------------------------------------------.
$dismissible_notice_a = array();

// Preliminary operations ---------------------------------------------------------------------------------------------.
global $wpdb;

// Sanitization ---------------------------------------------------------------------------------------------.

// Actions
$data['edit_id']        = isset( $_GET['edit_id'] ) ? intval( $_GET['edit_id'], 10 ) : null;
$data['delete_id']      = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;
$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

// Filter and search data.
$data['s']  = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
$data['cf'] = isset( $_GET['cf'] ) ? sanitize_text_field( $_GET['cf'] ) : null;

// Form data.
if ( ! is_null( $data['update_id'] ) or ! is_null( $data['form_submitted'] ) ) {

	// Nonce verification.
	check_admin_referer( 'daextlnl_create_update_featured_news', 'daextlnl_create_update_featured_news_nonce' );

	// Sanitization ---------------------------------------------------------------------------------------------------.
	$news_title   = isset( $_POST['news_title'] ) ? sanitize_text_field( $_POST['news_title'] ) : null;
	$news_excerpt = isset( $_POST['news_excerpt'] ) ? sanitize_text_field( $_POST['news_excerpt'] ) : null;
	$url          = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : null;
	$ticker_id    = isset( $_POST['ticker_id'] ) ? intval( $_POST['ticker_id'], 10 ) : null;

	// Validation -----------------------------------------------------------------------------------------------------.
	$invalid_data_message = '';

	// validation on "Title".
	if ( mb_strlen( trim( $news_title ) ) == 0 or mb_strlen( $news_title ) > 1000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid value in the "Title" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "Excerpt".
	if ( mb_strlen( trim( $news_excerpt ) ) == 0 or mb_strlen( $news_excerpt ) > 1000 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid value in the "Excerpt" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}

	// validation on "URL".
	if ( mb_strlen( $url ) > 2083 ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'Please enter a valid URL in the "URL" field.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'error',
		);
		$invalid_data           = true;
	}
}

// update ---------------------------------------------------------------.
if ( ! is_null( $data['update_id'] ) and ! isset( $invalid_data ) ) {

	// update the database.
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
	$safe_sql   = $wpdb->prepare(
		"UPDATE $table_name SET
                news_title = %s,
                news_excerpt = %s,
                url = %s,
                ticker_id = %d
                WHERE id = %d",
		$news_title,
		$news_excerpt,
		$url,
		$ticker_id,
		$data['update_id']
	);

	$query_result = $wpdb->query( $safe_sql );

	if ( $query_result !== false ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'The featured news has been successfully updated.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'updated',
		);
	}
} else {

	// add ------------------------------------------------------------------.
	if ( ! is_null( $data['form_submitted'] ) and ! isset( $invalid_data ) ) {

		// insert into the database
		$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
		$safe_sql   = $wpdb->prepare(
			"INSERT INTO $table_name SET
                    news_title = %s,
                    news_excerpt = %s,
                    url = %s,
                    ticker_id = %d",
			$news_title,
			$news_excerpt,
			$url,
			$ticker_id
		);

		$query_result = $wpdb->query( $safe_sql );

		if ( $query_result !== false ) {
			$dismissible_notice_a[] = array(
				'message' => __( 'The featured news has been successfully added.', $this->shared->get( 'text_domain' ) ),
				'class'   => 'updated',
			);
		}
	}
}

// delete a featured news.
if ( ! is_null( $data['delete_id'] ) ) {

	// Nonce verification.
	check_admin_referer( 'daextlnl_delete_featured_news_' . $data['delete_id'], 'daextlnl_delete_featured_news_nonce' );

	// delete this featured news.
	$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
	$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d ", $data['delete_id'] );

	$query_result = $wpdb->query( $safe_sql );

	if ( $query_result !== false ) {
		$dismissible_notice_a[] = array(
			'message' => __( 'The featured news has been successfully deleted.', $this->shared->get( 'text_domain' ) ),
			'class'   => 'updated',
		);
	}
}

// get the featured news data.
$display_form = true;
if ( ! is_null( $data['edit_id'] ) ) {
	$table_name        = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
	$safe_sql          = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d ", $data['edit_id'] );
	$featured_news_obj = $wpdb->get_row( $safe_sql );
	if ( $featured_news_obj === null ) {
		$display_form = false;
	}
}

// Get the value of the custom filter.
if ( $data['cf'] !== null ) {
	if ( $data['cf'] !== 'all' ) {
		$ticker_id_in_cf = intval( $data['cf'], 10 );
	} else {
		$ticker_id_in_cf = false;
	}
} else {
	$ticker_id_in_cf = false;
}

?>

<!-- output -->

<div class="wrap">

	<?php if ( $this->shared->get_number_of_featured_news() > 0 ) : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_html_e( 'Live News - Featured News', $this->shared->get( 'text_domain' ) ); ?></h2>

			<!-- Search Form -->

			<form action="admin.php" method="get" id="daext-search-form">

				<input type="hidden" name="page" value="daextlnl-featured">

				<p><?php esc_attr_e( 'Perform your Search', $this->shared->get( 'text_domain' ) ); ?></p>

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

				// Custom Filter.
				if ( $ticker_id_in_cf !== false ) {
					echo '<input type="hidden" name="cf" value="' . esc_attr( $ticker_id_in_cf ) . '">';
				}

				?>

				<input type="text" name="s" name="s"
						value="<?php echo esc_attr( stripslashes( $search_string ) ); ?>" autocomplete="off" maxlength="255">
				<input type="submit" value="">

			</form>

			<!-- Filter Form -->

			<form method="GET" action="admin.php" id="daext-filter-form">

				<input type="hidden" name="page" value="<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-featured">

				<p><?php esc_html_e( 'Filter by News Ticker', $this->shared->get( 'text_domain' ) ); ?></p>

				<select id="cf" name="cf" class="daext-display-none">

					<option value="all" 
					<?php
					if ( $data['cf'] !== null ) {
						selected( $data['cf'], 'all' );}
					?>
					><?php esc_html_e( 'All', $this->shared->get( 'text_domain' ) ); ?></option>

					<?php

					$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
					$safe_sql   = "SELECT id, name FROM $table_name ORDER BY id DESC";
					$tickers_a  = $wpdb->get_results( $safe_sql, ARRAY_A );

					foreach ( $tickers_a as $key => $ticker ) {

						if ( $data['cf'] !== null ) {
							echo '<option value="' . esc_attr( $ticker['id'] ) . '" ' . selected( $data['cf'], $ticker['id'], false ) . '>' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';
						} else {
							echo '<option value="' . esc_attr( $ticker['id'] ) . '">' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';

						}
					}

					?>

				</select>

			</form>

		</div>

	<?php else : ?>

		<div id="daext-header-wrapper" class="daext-clearfix">

			<h2><?php esc_html_e( 'Live News - Featured News', $this->shared->get( 'text_domain' ) ); ?></h2>

		</div>

	<?php endif; ?>

	<?php

	// do not display the menu if in the 'cf' url parameter is applied a filter based on a ticker that doesn't existm
	if ( $data['cf'] !== null ) {
		if ( $data['cf'] !== 'all' and ! $this->shared->ticker_exists( $data['cf'] ) ) {
			echo '<p>' . esc_html__( "The filter can't be applied because this featured news doesn't exist.", $this->shared->get( 'text_domain' ) ) . '</p>';
			return;
		}
	}

	// retrieve the url parameter that should be used in the linked URLs
	if ( $data['cf'] !== null ) {
		if ( $data['cf'] !== 'all' and $this->shared->ticker_exists( $data['cf'] ) ) {
			$ticker_url_parameter = '&cf=' . intval( $data['cf'], 10 );
		} else {
			$ticker_url_parameter = '';
		}
	} else {
		$ticker_url_parameter = '';
	}

	// display a message and not the menu if there are no tickers
	if ( $this->shared->get_number_of_tickers() == 0 ) {
		echo '<p>' . esc_html__( 'There are no news tickers at the moment, please create at least one news ticker with the', $this->shared->get( 'text_domain' ) ) . ' ' . '<a href="admin.php?page=daextlnl-tickers">' . esc_html__( 'News Tickers', $this->shared->get( 'text_domain' ) ) . '</a> menu.' . '</p>';
		return;
	}

	?>

	<div id="daext-menu-wrapper">

		<?php $this->dismissible_notice( $dismissible_notice_a ); ?>

		<!-- table -->

		<?php

		// custom filter.
		if ( $ticker_id_in_cf === false ) {
			$filter = '';
		} else {
			$filter = $wpdb->prepare( 'WHERE ticker_id = %d', $ticker_id_in_cf );
		}

		// create the query part used to filter the results when a search is performed.
		if ( ! is_null( $data['s'] ) ) {

			if ( mb_strlen( trim( $data['s'] ) ) > 0 ) {

				if ( strlen( trim( $filter ) ) > 0 ) {
					$filter .= $wpdb->prepare( ' AND (news_title LIKE %s OR news_excerpt LIKE %s OR url LIKE %s)', '%' . $data['s'] . '%', '%' . $data['s'] . '%', '%' . $data['s'] . '%' );
				} else {
					$filter = $wpdb->prepare( 'WHERE (news_title LIKE %s OR news_excerpt LIKE %s OR url LIKE %s)', '%' . $data['s'] . '%', '%' . $data['s'] . '%', '%' . $data['s'] . '%' );
				}
			}
		}

		// retrieve the total number of featured news.
		$table_name  = $wpdb->prefix . $this->shared->get( 'slug' ) . '_featured_news';
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $filter" );

		// Initialize the pagination class.
		require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextlnl-pagination.php';
		$pag = new daextlnl_pagination();
		$pag->set_total_items( $total_items );// Set the total number of items
		$pag->set_record_per_page( 10 ); // Set records per page
		$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-featured' );// Set target page
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
							<div><?php esc_html_e( 'Title', $this->shared->get( 'text_domain' ) ); ?></div>
							<div class="help-icon" title="<?php esc_attr_e( 'The title of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
						</th>
						<th>
							<div><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></div>
							<div class="help-icon" title="<?php esc_attr_e( 'The news ticker associated with the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
						</th>
						<th></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $results as $result ) : ?>
						<tr>
							<td><?php echo esc_attr( stripslashes( $result['news_title'] ) ); ?></td>
							<td><?php echo '<a href="admin.php?page=daextlnl-tickers&edit_id=' . esc_attr( $result['ticker_id'] ) . '">' . esc_html( stripslashes( $this->shared->get_textual_ticker( $result['ticker_id'] ) ) ) . '</a>'; ?></td>
							<td class="icons-container">
								<a class="menu-icon edit" href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-featured&edit_id=<?php echo esc_attr( $result['id'] ); ?><?php echo esc_html( $ticker_url_parameter ); ?>"></a>
								<form method="POST" action="admin.php?page=<?php echo $this->shared->get( 'slug' ); ?>-featured">
									<?php wp_nonce_field( 'daextlnl_delete_featured_news_' . intval( $result['id'], 10 ), 'daextlnl_delete_featured_news_nonce' ); ?>
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

		<?php else : ?>

			<?php

			if ( strlen( trim( $filter ) ) > 0 ) {
				echo '<div class="error settings-error notice is-dismissible below-h2"><p>' . esc_html__( 'There are no results that match your filter.', $this->shared->get( 'text_domain' ) ) . '</p></div>';
			}

			?>

		<?php endif; ?>

		<div id="featured-news-form-container">

			<?php if ( $display_form ) : ?>

				<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-featured<?php echo esc_attr( $ticker_url_parameter ); ?>" autocomplete="off">

					<input type="hidden" value="1" name="form_submitted">
					<?php wp_nonce_field( 'daextlnl_create_update_featured_news', 'daextlnl_create_update_featured_news_nonce' ); ?>

					<?php if ( ! is_null( $data['edit_id'] ) ) : ?>

					<!-- Edit a featured news -->

					<div class="daext-form-container">

						<h3 class="daext-form-title"><?php esc_attr_e( 'Edit Featured News', $this->shared->get( 'text_domain' ) ); ?> <?php echo esc_html( $featured_news_obj->id ); ?></h3>

						<table class="daext-form">

							<input type="hidden" name="update_id" value="<?php echo esc_attr( $featured_news_obj->id ); ?>" />

							<!-- title -->
							<tr valign="top">
								<th scope="row"><label for="news-title"><?php esc_html_e( 'Title', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $featured_news_obj->news_title ) ); ?>" type="text" id="news-title" maxlength="1000" size="30" name="news_title" />
									<div class="help-icon" title="<?php esc_attr_e( 'Enter the title of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- excerpt -->
							<tr valign="top">
								<th scope="row"><label for="news-excerpt"><?php esc_html_e( 'Excerpt', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $featured_news_obj->news_excerpt ) ); ?>" type="text" id="news-excerpt" maxlength="1000" size="30" name="news_excerpt" />
									<div class="help-icon" title="<?php esc_attr_e( 'Enter the excerpt of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- URL -->
							<tr valign="top">
								<th scope="row"><label for="url"><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<input value="<?php echo esc_attr( stripslashes( $featured_news_obj->url ) ); ?>" type="text" id="url" maxlength="2083" size="30" name="url" />
									<div class="help-icon" title="<?php esc_attr_e( 'Enter the URL of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
								</td>
							</tr>

							<!-- Ticker -->
							<tr>
								<th scope="row"><label for="ticker-id"><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></label></th>
								<td>
									<select id="ticker-id" name="ticker_id" class="daext-display-none">

										<?php

										$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
										$safe_sql   = "SELECT id, name FROM $table_name ORDER BY id DESC";
										$tickers_a  = $wpdb->get_results( $safe_sql, ARRAY_A );

										foreach ( $tickers_a as $key => $ticker ) {
											echo '<option value="' . esc_attr( $ticker['id'] ) . '" ' . selected( $featured_news_obj->ticker_id, $ticker['id'] ) . '>' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';
										}

										?>

									</select>
									<div class="help-icon" title='<?php esc_attr_e( 'The news ticker associated with this featured news.', $this->shared->get( 'text_domain' ) ); ?>'></div>
								</td>
							</tr>

						</table>

						<!-- submit button -->
						<div class="daext-form-action">
							<input class="button" type="submit" value="<?php esc_attr_e( 'Update Featured News', $this->shared->get( 'text_domain' ) ); ?>" >
						</div>

						<?php else : ?>

						<!-- Create New featured News -->

						<div class="daext-form-container">

							<div class="daext-form-title"><?php esc_html_e( 'Create a Featured News', $this->shared->get( 'text_domain' ) ); ?></div>

							<table class="daext-form">

								<!-- Title -->
								<tr valign="top">
									<th scope="row"><label for="news-title"><?php esc_html_e( 'Title', $this->shared->get( 'text_domain' ) ); ?></label></th>
									<td>
										<input type="text" id="news-title" maxlength="1000" size="30" name="news_title" />
										<div class="help-icon" title="<?php esc_attr_e( 'Enter the title of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
									</td>
								</tr>

								<!-- Excerpt -->
								<tr valign="top">
									<th scope="row"><label for="news-excerpt"><?php esc_html_e( 'Excerpt', $this->shared->get( 'text_domain' ) ); ?></label></th>
									<td>
										<input type="text" id="news-excerpt" maxlength="1000" size="30" name="news_excerpt" />
										<div class="help-icon" title="<?php esc_attr_e( 'Enter the excerpt of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
									</td>
								</tr>

								<!-- URL -->
								<tr valign="top">
									<th scope="row"><label for="url"><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
									<td>
										<input type="text" id="url" maxlength="2083" size="30" name="url" />
										<div class="help-icon" title="<?php esc_attr_e( 'Enter the URL of the featured news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
									</td>
								</tr>

								<!-- Ticker -->
								<tr>
									<th scope="row"><label for="ticker-id"><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></label></th>
									<td>
										<select id="ticker-id" name="ticker_id" class="daext-display-none">

											<?php

											$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
											$safe_sql   = "SELECT id, name FROM $table_name ORDER BY id DESC";
											$tickers_a  = $wpdb->get_results( $safe_sql, ARRAY_A );

											if ( $ticker_id_in_cf === false ) {

												foreach ( $tickers_a as $key => $ticker ) {
													echo '<option value="' . esc_attr( $ticker['id'] ) . '">' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';
												}
											} else {

												foreach ( $tickers_a as $key => $ticker ) {
													echo '<option value="' . esc_attr( $ticker['id'] ) . '" ' . selected( $ticker_id_in_cf, $ticker['id'], false ) . '>' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';
												}
											}

											?>

										</select>
										<div class="help-icon" title='<?php esc_attr_e( 'The news ticker associated with this featured news', $this->shared->get( 'text_domain' ) ); ?>'></div>
									</td>
								</tr>

							</table>

							<!-- submit button -->
							<div class="daext-form-action">
								<input class="button" type="submit" value="<?php esc_attr_e( 'Add Featured News', $this->shared->get( 'text_domain' ) ); ?>" >
							</div>

							<?php endif; ?>

						</div>

				</form>

			<?php endif; ?>

		</div>

	</div>

</div>