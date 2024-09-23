<?php
/**
 * The "Sliding News" menu of the plugin.
 *
 * @package live-news-lite
 */

if ( ! current_user_can( get_option( $this->shared->get( 'slug' ) . '_sliding_menu_capability' ) ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->shared->get( 'text_domain' ) ) );
}

?>

		<!-- process data -->

		<?php

		// Initialize variables -------------------------------------------------------------------------------------------------.
		$dismissible_notice_a = array();

		// Preliminary operations -----------------------------------------------------------------------------------------------.
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

		if ( ! is_null( $data['update_id'] ) or ! is_null( $data['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_create_update_sliding_news', 'daextlnl_create_update_sliding_news_nonce' );

			// Sanitization -----------------------------------------------------------------------------------------------------
			$news_title               = isset( $_POST['news_title'] ) ? sanitize_text_field( $_POST['news_title'] ) : null;
			$url                      = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : null;
			$ticker_id                = isset( $_POST['ticker_id'] ) ? intval( $_POST['ticker_id'], 10 ) : null;
			$text_color               = isset( $_POST['text_color'] ) ? sanitize_text_field( $_POST['text_color'] ) : null;
			$text_color_hover         = isset( $_POST['text_color_hover'] ) ? sanitize_text_field( $_POST['text_color_hover'] ) : null;
			$background_color         = isset( $_POST['background_color'] ) ? sanitize_text_field( $_POST['background_color'] ) : null;
			$background_color_opacity = isset( $_POST['background_color_opacity'] ) ? floatval( $_POST['background_color_opacity'] ) : null;
			$image_before             = isset( $_POST['image_before'] ) ? esc_url_raw( $_POST['image_before'] ) : null;
			$image_after              = isset( $_POST['image_after'] ) ? esc_url_raw( $_POST['image_after'] ) : null;

			// Validation -------------------------------------------------------------------------------------------------------.
			$invalid_data_message = '';

			// validation on "Title"
			if ( mb_strlen( trim( $news_title ) ) == 0 or mb_strlen( $news_title ) > 1000 ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid value in the "Title" field.', $this->shared->get( 'text_domain' ) ),
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

			// validation on "Text Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $text_color ) ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid color in the "Text Color" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// validation on "Text Color Hover"
			if ( ! preg_match( $this->shared->hex_rgb_regex, $text_color_hover ) ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid color in the "Text Color Hover" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// validation on "Background Color".
			if ( ! preg_match( $this->shared->hex_rgb_regex, $background_color ) ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid color in the "Background Color" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// validation on "Background Color Opacity".
			if ( $background_color_opacity < 0 or $background_color_opacity > 1 ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a value included between 0 and 1 in the "Background Color Opacity" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// validation on "Image Before".
			if ( mb_strlen( $image_before ) > 2083 ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid URL in the "Image Left" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}

			// validation on "Image After".
			if ( mb_strlen( $image_after ) > 2083 ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'Please enter a valid URL in the "Image Right" field.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'error',
				);
				$invalid_data           = true;
			}
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) and ! isset( $invalid_data ) ) {

			// update the database.
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
			$safe_sql   = $wpdb->prepare(
				"UPDATE $table_name SET
                news_title = %s,
                url = %s,
                ticker_id = %d,
                text_color = %s,
                text_color_hover = %s,
                background_color = %s,
                background_color_opacity = %f,
                image_before = %s,
                image_after = %s
                WHERE id = %d",
				$news_title,
				$url,
				$ticker_id,
				$text_color,
				$text_color_hover,
				$background_color,
				$background_color_opacity,
				$image_before,
				$image_after,
				$data['update_id']
			);

			$query_result = $wpdb->query( $safe_sql );

			if ( $query_result !== false ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'The sliding news has been successfully updated.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'updated',
				);
			}
		} else {

			// add ------------------------------------------------------------------.
			if ( ! is_null( $data['form_submitted'] ) and ! isset( $invalid_data ) ) {

				// insert into the database
				$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
				$safe_sql   = $wpdb->prepare(
					"INSERT INTO $table_name SET
                    news_title = %s,
                    url = %s,
                    ticker_id = %d,
                    text_color = %s,
                    text_color_hover = %s,
                    background_color = %s,
                    background_color_opacity = %f,
                    image_before = %s,
                    image_after = %s",
					$news_title,
					$url,
					$ticker_id,
					$text_color,
					$text_color_hover,
					$background_color,
					$background_color_opacity,
					$image_before,
					$image_after
				);

				$query_result = $wpdb->query( $safe_sql );

				if ( $query_result !== false ) {
					$dismissible_notice_a[] = array(
						'message' => __( 'The sliding news has been successfully added.', $this->shared->get( 'text_domain' ) ),
						'class'   => 'updated',
					);
				}
			}
		}

		// delete a sliding news.
		if ( ! is_null( $data['delete_id'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextlnl_delete_sliding_news_' . $data['delete_id'], 'daextlnl_delete_sliding_news_nonce' );

			// delete this game.
			$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
			$safe_sql   = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d ", $data['delete_id'] );

			$query_result = $wpdb->query( $safe_sql );

			if ( $query_result !== false ) {
				$dismissible_notice_a[] = array(
					'message' => __( 'The sliding news has been successfully deleted.', $this->shared->get( 'text_domain' ) ),
					'class'   => 'updated',
				);
			}
		}

		// get the sliding news data.
		$display_form = true;
		if ( ! is_null( $data['edit_id'] ) ) {
			$table_name       = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
			$safe_sql         = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d ", $data['edit_id'] );
			$sliding_news_obj = $wpdb->get_row( $safe_sql );
			if ( $sliding_news_obj === null ) {
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

			<?php if ( $this->shared->get_number_of_sliding_news() > 0 ) : ?>

				<div id="daext-header-wrapper" class="daext-clearfix">

					<h2><?php esc_html_e( 'Live News - Sliding News', $this->shared->get( 'text_domain' ) ); ?></h2>

					<!-- Search Form -->

					<form action="admin.php" method="get" id="daext-search-form">

						<input type="hidden" name="page" value="daextlnl-sliding">

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

						<input type="hidden" name="page" value="<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-sliding">

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

					<h2><?php esc_attr_e( 'Live News - Sliding News', $this->shared->get( 'text_domain' ) ); ?></h2>

				</div>

			<?php endif; ?>

			<?php

			// do not display the menu if in the 'cf' url parameter is applied a filter based on a ticker that doesn't exist.
			if ( $data['cf'] !== null ) {
				if ( $data['cf'] !== 'all' and ! $this->shared->ticker_exists( $data['cf'] ) ) {
					echo '<p>' . esc_html__( "The filter can't be applied because this sliding news doesn't exist.", $this->shared->get( 'text_domain' ) ) . '</p>';
					return;
				}
			}

			// retrieve the url parameter that should be used in the linked URLs.
			if ( $this->shared->ticker_exists( $data['cf'] ) ) {
				$ticker_url_parameter = '&cf=' . intval( $data['cf'], 10 );
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
						$filter .= $wpdb->prepare( ' AND (news_title LIKE %s OR url LIKE %s)', '%' . $data['s'] . '%', '%' . $data['s'] . '%' );
					} else {
						$filter = $wpdb->prepare( 'WHERE (news_title LIKE %s OR url LIKE %s)', '%' . $data['s'] . '%', '%' . $data['s'] . '%' );
					}
				}
			}

			// retrieve the total number of sliding news.
			$table_name  = $wpdb->prefix . $this->shared->get( 'slug' ) . '_sliding_news';
			$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $filter" );

			// Initialize the pagination class.
			require_once $this->shared->get( 'dir' ) . '/admin/inc/class-daextlnl-pagination.php';
			$pag = new daextlnl_pagination();
			$pag->set_total_items( $total_items );// Set the total number of items.
			$pag->set_record_per_page( 10 ); // Set records per page.
			$pag->set_target_page( 'admin.php?page=' . $this->shared->get( 'slug' ) . '-sliding' );// Set target page.
			$pag->set_current_page();// set the current page number.

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
								<div class="help-icon" title="<?php esc_attr_e( 'The title of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</th>
							<th>
								<div><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></div>
								<div class="help-icon" title="<?php esc_attr_e( 'The news ticker associated with the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>

					<?php foreach ( $results as $result ) : ?>
						<tr>
							<td><?php echo esc_html( stripslashes( $result['news_title'] ) ); ?></td>
							<td><?php echo '<a href="admin.php?page=daextlnl-tickers&edit_id=' . esc_attr( $result['ticker_id'] ) . '">' . esc_html( stripslashes( $this->shared->get_textual_ticker( $result['ticker_id'] ) ) ) . '</a>'; ?></td>
							<td class="icons-container">

								<a class="menu-icon edit" href="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-sliding&edit_id=<?php echo esc_attr( $result['id'] ); ?><?php echo esc_html( $ticker_url_parameter ); ?>"></a>
								<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-sliding">
									<?php wp_nonce_field( 'daextlnl_delete_sliding_news_' . $result['id'], 'daextlnl_delete_sliding_news_nonce' ); ?>
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

			<div id="sliding-news-form-container">

				<?php if ( $display_form ) : ?>

					<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-sliding<?php echo esc_attr( $ticker_url_parameter ); ?>" autocomplete="off">

						<input type="hidden" value="1" name="form_submitted">
						<?php wp_nonce_field( 'daextlnl_create_update_sliding_news', 'daextlnl_create_update_sliding_news_nonce' ); ?>

						<?php if ( ! is_null( $data['edit_id'] ) ) : ?>

							<!-- Edit a sliding news -->

							<div class="daext-form-container">

								<h3 class="daext-form-title"><?php esc_html_e( 'Edit Sliding News', $this->shared->get( 'text_domain' ) ); ?> <?php echo esc_html( $sliding_news_obj->id ); ?></h3>

								<table class="daext-form">

									<input type="hidden" name="update_id" id="update-id" value="<?php echo esc_html( $sliding_news_obj->id ); ?>" />

									<!-- title -->
									<tr valign="top">
										<th scope="row"><label for="news-title"><?php esc_html_e( 'Title', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->news_title ) ); ?>" type="text" id="news-title" maxlength="1000" size="30" name="news_title" />
											<div class="help-icon" title="<?php esc_attr_e( 'Enter the title of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- URL -->
									<tr valign="top">
										<th scope="row"><label for="url"><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->url ) ); ?>" type="text" id="url" maxlength="2083" size="30" name="url" />
											<div class="help-icon" title="<?php esc_attr_e( 'Enter the URL of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- Ticker -->
									<tr>
										<th scope="row"><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></th>
										<td>
											<select id="ticker-id" name="ticker_id" class="daext-display-none">

												<?php

												$table_name = $wpdb->prefix . $this->shared->get( 'slug' ) . '_tickers';
												$safe_sql   = "SELECT id, name FROM $table_name ORDER BY id DESC";
												$tickers_a  = $wpdb->get_results( $safe_sql, ARRAY_A );

												foreach ( $tickers_a as $key => $ticker ) {

													echo '<option value="' . esc_attr( $ticker['id'] ) . '" ' . selected( $sliding_news_obj->ticker_id, $ticker['id'] ) . '>' . esc_html( stripslashes( $ticker['name'] ) ) . '</option>';

												}

												?>

											</select>
											<div class="help-icon" title='<?php esc_attr_e( 'Select the news ticker associated with this sliding news.', $this->shared->get( 'text_domain' ) ); ?>'></div>
										</td>
									</tr>

									<!-- Text Color -->
									<tr valign="top">
										<th scope="row"><label for="text-color"><?php esc_html_e( 'Text Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->text_color ) ); ?>" class="wp-color-picker" type="text" id="text-color" maxlength="7" size="30" name="text_color"/>
											<div class="help-icon" title="<?php esc_attr_e( 'Select the color used to display the text of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- Text Color Hover -->
									<tr valign="top">
										<th scope="row"><label for="text-color-hover"><?php esc_html_e( 'Text Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->text_color_hover ) ); ?>" class="wp-color-picker" type="text" id="text-color-hover" maxlength="7" size="30" name="text_color_hover"/>
											<div class="help-icon" title="<?php esc_attr_e( 'Select the color used to display the text of this sliding news in hover state.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- Background Color -->
									<tr valign="top">
										<th scope="row"><label for="background-color"><?php esc_html_e( 'Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->background_color ) ); ?>" class="wp-color-picker" type="text" id="background-color" maxlength="7" size="30" name="background_color"/>
											<div class="help-icon" title="<?php esc_attr_e( 'Select the background color of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- Background Color Opacity -->
									<tr>
										<th scope="row"><label for="background-color-opacity"><?php esc_html_e( 'Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>
											<input value="<?php echo floatval( $sliding_news_obj->background_color_opacity ); ?>" type="text" id="background-color-opacity" maxlength="3" size="30" name="background_color_opacity" />
											<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
										</td>
									</tr>

									<!-- Image Before -->
									<tr>
										<th scope="row"><label for="image-before"><?php esc_html_e( 'Image Left', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>

											<div class="image-uploader">
												<img class="selected-image" src="<?php echo esc_attr( stripslashes( $sliding_news_obj->image_before ) ); ?>" <?php echo mb_strlen( trim( $sliding_news_obj->image_before ) ) == 0 ? 'style="display: none;"' : ''; ?>>
												<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->image_before ) ); ?>" type="hidden" id="image-before" maxlength="2083" name="image_before">
												<a class="button_add_media" data-set-remove="<?php echo mb_strlen( trim( $sliding_news_obj->image_before ) ) == 0 ? 'set' : 'remove'; ?>" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php echo mb_strlen( trim( $sliding_news_obj->image_before ) ) == 0 ? esc_attr__( 'Set image', $this->shared->get( 'text_domain' ) ) : esc_attr__( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?></a>
												<p class="description"><?php esc_html_e( "Select the image displayed on the left of the sliding news. It's recommended to use an image with an height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
											</div>

										</td>
									</tr>

									<!-- Image After -->
									<tr>
										<th scope="row"><label for="image-after"><?php esc_html_e( 'Image Right', $this->shared->get( 'text_domain' ) ); ?></label></th>
										<td>

											<div class="image-uploader">
												<img class="selected-image" src="<?php echo esc_attr( stripslashes( $sliding_news_obj->image_after ) ); ?>" <?php echo mb_strlen( trim( $sliding_news_obj->image_after ) ) == 0 ? 'style="display: none;"' : ''; ?>>
												<input value="<?php echo esc_attr( stripslashes( $sliding_news_obj->image_after ) ); ?>" type="hidden" id="image-after" maxlength="2083" name="image_after">
												<a class="button_add_media" data-set-remove="<?php echo mb_strlen( trim( $sliding_news_obj->image_after ) ) == 0 ? 'set' : 'remove'; ?>" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php echo mb_strlen( trim( $sliding_news_obj->image_after ) ) == 0 ? esc_attr__( 'Set image', $this->shared->get( 'text_domain' ) ) : esc_attr__( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?></a>
												<p class="description"><?php esc_html_e( "Select the image displayed on the right of the sliding news. It's recommended to use an image with an height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
											</div>

										</td>
									</tr>

								</table>

								<!-- submit button -->
								<div class="daext-form-action">
									<input class="button" type="submit" value="<?php esc_attr_e( 'Update Sliding News', $this->shared->get( 'text_domain' ) ); ?>" >
								</div>

						<?php else : ?>

							<!-- Create New Sliding News -->

							<div class="daext-form-container">

								<div class="daext-form-title"><?php esc_html_e( 'Create a Sliding News', $this->shared->get( 'text_domain' ) ); ?></div>

									<table class="daext-form">

										<!-- Title -->
										<tr valign="top">
											<th scope="row"><label for="news-title"><?php esc_html_e( 'Title', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input type="text" id="news-title" maxlength="1000" size="30" name="news_title" />
												<div class="help-icon" title="<?php esc_attr_e( 'Enter the title of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- URL -->
										<tr valign="top">
											<th scope="row"><label for="url"><?php esc_html_e( 'URL', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input type="text" id="url" maxlength="2083" size="30" name="url" />
												<div class="help-icon" title="<?php esc_attr_e( 'Enter the URL of the sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- Ticker -->
										<tr>
											<th scope="row"><?php esc_html_e( 'Ticker', $this->shared->get( 'text_domain' ) ); ?></th>
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
												<div class="help-icon" title='<?php esc_attr_e( 'Select the news ticker associated with this sliding news.', $this->shared->get( 'text_domain' ) ); ?>'></div>
											</td>
										</tr>

										<!-- Text Color -->
										<tr valign="top">
											<th scope="row"><label for="text-color"><?php esc_html_e( 'Text Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input class="wp-color-picker" type="text" id="text-color" maxlength="7" size="30" name="text_color"/>
												<div class="help-icon" title="<?php esc_attr_e( 'Select the color used to display the text of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- Text Color Hover -->
										<tr valign="top">
											<th scope="row"><label for="text-color-hover"><?php esc_html_e( 'Text Color Hover', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input class="wp-color-picker" type="text" id="text-color-hover" maxlength="7" size="30" name="text_color_hover"/>
												<div class="help-icon" title="<?php esc_attr_e( 'Select the color used to display the text of this sliding news in hover state.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- Background Color -->
										<tr valign="top">
											<th scope="row"><label for="background-color"><?php esc_html_e( 'Background Color', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input class="wp-color-picker" type="text" id="background-color" maxlength="7" size="30" name="background_color"/>
												<div class="help-icon" title="<?php esc_attr_e( 'Select the background color of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- Background Color Opacity -->
										<tr>
											<th scope="row"><label for="background-color-opacity"><?php esc_html_e( 'Background Color Opacity', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>
												<input value="1" type="text" id="background-color-opacity" maxlength="3" size="30" name="background_color_opacity" />
												<div class="help-icon" title="<?php esc_attr_e( 'The background color opacity of this sliding news.', $this->shared->get( 'text_domain' ) ); ?>"></div>
											</td>
										</tr>

										<!-- Image Before -->
										<tr>
											<th scope="row"><label for="image-before"><?php esc_html_e( 'Image Left', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>

												<div class="image-uploader">
													<img class="selected-image" src="" style="display: none">
													<input type="hidden" id="image-before" maxlength="2083" name="image_before">
													<a class="button_add_media" data-set-remove="set" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php esc_html_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?></a>
													<p class="description"><?php esc_html_e( "Select the image displayed on the left of the sliding news. It's recommended to use an image with an height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
												</div>

											</td>
										</tr>

										<!-- Image After -->
										<tr>
											<th scope="row"><label for="image-after"><?php esc_html_e( 'Image Right', $this->shared->get( 'text_domain' ) ); ?></label></th>
											<td>

												<div class="image-uploader">
													<img class="selected-image" src="" style="display: none">
													<input type="hidden" id="image-after" maxlength="2083" name="image_after">
													<a class="button_add_media" data-set-remove="set" data-set="<?php esc_attr_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?>" data-remove="<?php esc_attr_e( 'Remove Image', $this->shared->get( 'text_domain' ) ); ?>"><?php esc_html_e( 'Set image', $this->shared->get( 'text_domain' ) ); ?></a>
													<p class="description"><?php esc_attr_e( "Select the image displayed on the right of the sliding news. It's recommended to use an image with an height of 40 pixels.", $this->shared->get( 'text_domain' ) ); ?></p>
												</div>

											</td>
										</tr>

									</table>

									<!-- submit button -->
									<div class="daext-form-action">
										<input class="button" type="submit" value="<?php esc_attr_e( 'Add Sliding News', $this->shared->get( 'text_domain' ) ); ?>" >
									</div>

								<?php endif; ?>

							</div>

					</form>

				<?php endif; ?>

			</div>

		</div>

	</div>