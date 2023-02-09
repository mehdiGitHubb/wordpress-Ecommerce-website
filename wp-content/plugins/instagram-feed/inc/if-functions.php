<?php
/**
 * Contains functions used primarily on the frontend but some also used in the
 * admin area.
 *
 * - Function for the shortcode that displays the feed
 * - AJAX call for pagination
 * - All AJAX calls for image resizing triggering
 * - Clearing page caches for caching plugins
 * - Starting cron caching
 * - Getting settings from the database
 * - Displaying frontend errors
 * - Enqueueing CSS and JS files for the feed
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'widget_text', 'do_shortcode' );

/**
 * The main function the creates the feed from a shortcode.
 * Can be safely added directly to templates using
 * 'echo do_shortcode( "[instagram-feed]" );'
 */
add_shortcode('instagram-feed', 'display_instagram');
function display_instagram( $atts = array(), $preview_settings = false ) {
	do_action( 'sbi_before_display_instagram' );

	$database_settings = sbi_get_database_settings();

	if ( $database_settings['sb_instagram_ajax_theme'] !== 'on'
		 && $database_settings['sb_instagram_ajax_theme'] !== 'true'
		 && $database_settings['sb_instagram_ajax_theme'] !== '1'
		 && $database_settings['sb_instagram_ajax_theme'] !== true ) {
		wp_enqueue_script( 'sbi_scripts' );
	}

	if ( $database_settings['enqueue_css_in_shortcode'] === 'on' || $database_settings['enqueue_css_in_shortcode'] === 'true' || $database_settings['enqueue_css_in_shortcode'] === true ) {
		wp_enqueue_style( 'sbi_styles' );
	}

	$instagram_feed_settings = new SB_Instagram_Settings( $atts, $database_settings, $preview_settings );

	$early_settings = $instagram_feed_settings->get_settings();;
	if ( empty( $early_settings ) && ! sbi_doing_customizer( $atts ) ) {
		$style = current_user_can( 'manage_instagram_feed_options' ) ? ' style="display: block;"' : '';
		$id = isset( $atts['feed'] ) ? (int)$atts['feed'] : false;
		if ( $id ) {
			$message = sprintf( __( 'Error: No feed with the ID %s found.', 'instagram-feed' ), $id  );
		} else {
			$message = __( 'Error: No feed found.', 'instagram-feed' );
		}
		ob_start(); ?>
		<div id="sbi_mod_error" <?php echo $style; ?>>
			<span><?php esc_html_e('This error message is only visible to WordPress admins', 'instagram-feed' ); ?></span><br />
			<p><strong><?php echo esc_html( $message ); ?></strong>
			<p><?php esc_html_e( 'Please go to the Instagram Feed settings page to create a feed.', 'instagram-feed' ); ?></p>
		</div>
		<?php
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}

	$instagram_feed_settings->set_feed_type_and_terms();
	$instagram_feed_settings->set_transient_name();
	$transient_name = $instagram_feed_settings->get_transient_name();
	$settings = $instagram_feed_settings->get_settings();
	$feed_type_and_terms = $instagram_feed_settings->get_feed_type_and_terms();

	$instagram_feed = new SB_Instagram_Feed( $transient_name );

	$instagram_feed->set_cache( $instagram_feed_settings->get_cache_time_in_seconds(), $settings );

	if ( $settings['caching_type'] === 'background' ) {
		$instagram_feed->add_report( 'background caching used' );
		if ( $instagram_feed->regular_cache_exists() ) {
			$instagram_feed->add_report( 'setting posts from cache' );
			$instagram_feed->set_post_data_from_cache();
		}

		if ( $instagram_feed->need_to_start_cron_job() ) {
			$instagram_feed->add_report( 'setting up feed for cron cache' );
			$to_cache = array(
                'atts' => $atts,
                'last_requested' => time(),
		    );

			$instagram_feed->set_cron_cache( $to_cache, $instagram_feed_settings->get_cache_time_in_seconds() );

			SB_Instagram_Cron_Updater::do_single_feed_cron_update( $instagram_feed_settings, $to_cache, $atts, false );
			$instagram_feed->set_cache( $instagram_feed_settings->get_cache_time_in_seconds(), $settings );
			$instagram_feed->set_post_data_from_cache();
		} elseif ( $instagram_feed->should_update_last_requested() ) {
			$instagram_feed->add_report( 'updating last requested' );
			$to_cache = array(
				'last_requested' => time(),
			);

			$instagram_feed->set_cron_cache( $to_cache, $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}

    } elseif ( $instagram_feed->regular_cache_exists() ) {
		$instagram_feed->add_report( 'page load caching used and regular cache exists' );
		$instagram_feed->set_post_data_from_cache();

        if ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
	        while ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
				$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
			}
			$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}

	} else {
		$instagram_feed->add_report( 'no feed cache found' );

		while ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
			$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
		}

		if ( ! $instagram_feed->should_use_backup() ) {
			$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}

	}

	if ( $instagram_feed->should_use_backup() ) {
		$instagram_feed->add_report( 'trying to use backup' );
		$instagram_feed->maybe_set_post_data_from_backup();
		$instagram_feed->maybe_set_header_data_from_backup();
	}

	// if need a header
	if ( $instagram_feed->need_header( $settings, $feed_type_and_terms ) ) {
		if ( $instagram_feed->should_use_backup() && $settings['minnum'] > 0 ) {
			$instagram_feed->add_report( 'trying to set header from backup' );
			$header_cache_success = $instagram_feed->maybe_set_header_data_from_backup();
		} elseif ( $instagram_feed->regular_header_cache_exists() ) {
			// set_post_data_from_cache
			$instagram_feed->add_report( 'page load caching used and regular header cache exists' );
			$instagram_feed->set_header_data_from_cache();
		} else {
			$instagram_feed->add_report( 'no header cache exists' );
			$instagram_feed->set_remote_header_data( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
			$instagram_feed->cache_header_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}
	} else {
		$instagram_feed->add_report( 'no header needed' );
	}

	if ( $settings['resizeprocess'] === 'page' ) {
		$instagram_feed->add_report( 'resizing images for post set' );
		$post_data = $instagram_feed->get_post_data();
		$post_data = array_slice( $post_data, 0, $settings['num'] );

		$post_set = new SB_Instagram_Post_Set( $post_data, $transient_name );

		$post_set->maybe_save_update_and_resize_images_for_posts();
    }

	if ( $settings['disable_js_image_loading'] || $settings['imageres'] !== 'auto' ) {
		global $sb_instagram_posts_manager;
		$post_data = $instagram_feed->get_post_data();

		if ( ! $sb_instagram_posts_manager->image_resizing_disabled() ) {
			$image_ids = array();
			foreach ( $post_data as $post ) {
				$image_ids[] = SB_Instagram_Parse::get_post_id( $post );
			}
			$resized_images = SB_Instagram_Feed::get_resized_images_source_set( $image_ids, 0, $transient_name );

			$instagram_feed->set_resized_images( $resized_images );
		}
	}

	return $instagram_feed->get_the_feed_html( $settings, $atts, $instagram_feed_settings->get_feed_type_and_terms(), $instagram_feed_settings->get_connected_accounts_in_feed() );
}

/**
 * For efficiency, local versions of image files available for the images actually displayed on the page
 * are added at the end of the feed.
 *
 * @param object $instagram_feed
 * @param string $feed_id
 */
function sbi_add_resized_image_data( $instagram_feed, $feed_id ) {
	global $sb_instagram_posts_manager;

	if ( ! $sb_instagram_posts_manager->image_resizing_disabled() ) {
		if ( $instagram_feed->should_update_last_requested() ) {
			SB_Instagram_Feed::update_last_requested( $instagram_feed->get_image_ids_post_set() );
		}
	}
	?>
    <span class="sbi_resized_image_data" data-feed-id="<?php echo esc_attr( $feed_id ); ?>" data-resized="<?php echo esc_attr( sbi_json_encode( SB_Instagram_Feed::get_resized_images_source_set( $instagram_feed->get_image_ids_post_set(), 0, $feed_id ) ) ); ?>">
	</span>
	<?php
}
add_action( 'sbi_before_feed_end', 'sbi_add_resized_image_data', 10, 2 );

/**
 * Called after the load more button is clicked using admin-ajax.php.
 * Resembles "display_instagram"
 */
function sbi_get_next_post_set() {
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options') ) {
		if ( ! isset( $_POST['feed_id'] ) || (strpos( $_POST['feed_id'], 'sbi' ) === false && strpos( $_POST['feed_id'], '*' ) === false ) ) {
			die( 'invalid feed ID');
		}
	}

	$feed_id = sanitize_text_field( wp_unslash( $_POST['feed_id'] ) );

	$atts_raw = isset( $_POST['atts'] ) ? json_decode( stripslashes( $_POST['atts'] ), true ) : array();
	if ( is_array( $atts_raw ) ) {
		$atts_raw = SB_Instagram_Settings::sanitize_raw_atts( $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

	$offset = isset( $_POST['offset'] ) ? (int)$_POST['offset'] : 0;
	$page = isset( $_POST['page'] ) ? (int)$_POST['page'] : 1;

	$database_settings = sbi_get_database_settings();
	$instagram_feed_settings = new SB_Instagram_Settings( $atts, $database_settings );

	$instagram_feed_settings->set_feed_type_and_terms();
	$instagram_feed_settings->set_transient_name();
	$transient_name = $instagram_feed_settings->get_transient_name();

	if ( $transient_name !== $feed_id ) {
		die( 'id does not match' );
	}

	$settings = $instagram_feed_settings->get_settings();

	$location = isset( $_POST['location'] ) && in_array( $_POST['location'], array( 'header', 'footer', 'sidebar', 'content' ), true ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : 'unknown';
	$post_id = isset( $_POST['post_id'] ) && $_POST['post_id'] !== 'unknown' ? (int)$_POST['post_id'] : 'unknown';
	$feed_details = array(
		'feed_id' => $transient_name,
		'atts' => $atts,
		'location' => array(
			'post_id' => $post_id,
			'html' => $location
		)
	);

	$can_do_background_tasks = false;
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		$nonce = isset( $_POST['locator_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['locator_nonce'] ) ) : '';
		if ( wp_verify_nonce( $nonce, esc_attr( 'sbi-locator-nonce-' . $post_id . '-' . $transient_name ) ) ) {
		    $can_do_background_tasks = true;
		}
	} else {
		$can_do_background_tasks = true;
	}

	if ( $can_do_background_tasks ) {
		sbi_do_background_tasks( $feed_details );
	}

	$feed_type_and_terms = $instagram_feed_settings->get_feed_type_and_terms();

	$instagram_feed = new SB_Instagram_Feed( $transient_name );
	$instagram_feed->set_cache( $instagram_feed_settings->get_cache_time_in_seconds(), $settings );

	if ( $settings['caching_type'] === 'background' ) {
		$instagram_feed->add_report( 'background caching used' );
		if ( $instagram_feed->regular_cache_exists() ) {
			$instagram_feed->add_report( 'setting posts from cache' );
			$instagram_feed->set_post_data_from_cache();
		}

		if ( $instagram_feed->need_posts( $settings['minnum'], $offset, $page ) && $instagram_feed->can_get_more_posts() ) {
			while ( $instagram_feed->need_posts( $settings['minnum'], $offset, $page ) && $instagram_feed->can_get_more_posts() ) {
				$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
			}

			$normal_method = true;
			if ( $instagram_feed->need_to_start_cron_job() ) {
				$instagram_feed->add_report( 'needed to start cron job' );
				$to_cache = array(
					'atts' => $atts,
					'last_requested' => time(),
				);
				$normal_method = false;

			} else {
				$instagram_feed->add_report( 'updating last requested and adding to cache' );
				$to_cache = array(
					'last_requested' => time(),
				);
			}

			if ( $normal_method ) {
				$instagram_feed->set_cron_cache( $to_cache, $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
			} else {
				$instagram_feed->set_cron_cache( $to_cache, $instagram_feed_settings->get_cache_time_in_seconds() );
			}
		}

	} elseif ( $instagram_feed->regular_cache_exists() ) {
		$instagram_feed->add_report( 'regular cache exists' );
		$instagram_feed->set_post_data_from_cache();

        if ( $instagram_feed->need_posts( $settings['minnum'], $offset, $page ) && $instagram_feed->can_get_more_posts() ) {
	        while ( $instagram_feed->need_posts( $settings['minnum'], $offset, $page ) && $instagram_feed->can_get_more_posts() ) {
				$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
			}

			$instagram_feed->add_report( 'adding to cache' );
			$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}


	} else {
		$instagram_feed->add_report( 'no feed cache found' );

		while ( $instagram_feed->need_posts( $settings['num'], $offset ) && $instagram_feed->can_get_more_posts() ) {
			$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
		}

		if ( $instagram_feed->should_use_backup() ) {
			$instagram_feed->add_report( 'trying to use a backup cache' );
			$instagram_feed->maybe_set_post_data_from_backup();
		} else {
			$instagram_feed->add_report( 'transient gone, adding to cache' );
			$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
		}

	}

	if ( $settings['disable_js_image_loading'] || $settings['imageres'] !== 'auto' ) {
		global $sb_instagram_posts_manager;
		$post_data = array_slice( $instagram_feed->get_post_data(), $offset, $settings['minnum'] );

		if ( ! $sb_instagram_posts_manager->image_resizing_disabled() ) {
			$image_ids = array();
			foreach ( $post_data as $post ) {
				$image_ids[] = SB_Instagram_Parse::get_post_id( $post );
			}
			$resized_images = SB_Instagram_Feed::get_resized_images_source_set( $image_ids, 0, $feed_id );

			$instagram_feed->set_resized_images( $resized_images );
		}
	}

	$feed_status = array( 'shouldPaginate' => $instagram_feed->should_use_pagination( $settings, $offset ) );

	$return = array(
		'html' => $instagram_feed->get_the_items_html( $settings, $offset, $instagram_feed_settings->get_feed_type_and_terms(), $instagram_feed_settings->get_connected_accounts_in_feed() ),
		'feedStatus' => $feed_status,
		'report' => $instagram_feed->get_report(),
        'resizedImages' => SB_Instagram_Feed::get_resized_images_source_set( $instagram_feed->get_image_ids_post_set(), 1, $feed_id )
	);

	header( 'Content-Type: application/json; charset=utf-8' );
	echo sbi_json_encode( $return );

	die();
}
add_action( 'wp_ajax_sbi_load_more_clicked', 'sbi_get_next_post_set' );
add_action( 'wp_ajax_nopriv_sbi_load_more_clicked', 'sbi_get_next_post_set' );

/**
 * Posts that need resized images are processed after being sent to the server
 * using AJAX
 *
 * @return string
 */
function sbi_process_submitted_resize_ids() {
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options') ) {
		if ( ! isset( $_POST['feed_id'] ) || (strpos( $_POST['feed_id'], 'sbi' ) === false && strpos( $_POST['feed_id'], '*' ) === false ) ) {
			die( 'invalid feed ID');
		}
	}

	$feed_id = sanitize_text_field( $_POST['feed_id'] );
	$images_need_resizing_raw = isset( $_POST['needs_resizing'] ) ? $_POST['needs_resizing'] : array();
	if ( is_array( $images_need_resizing_raw ) ) {
		array_map( 'sbi_sanitize_instagram_ids', $images_need_resizing_raw );
	} else {
		$images_need_resizing_raw = array();
	}
	$images_need_resizing = $images_need_resizing_raw;

	$atts_raw = isset( $_POST['atts'] ) ? json_decode( wp_unslash( $_POST['atts'] ), true ) : array();
	if ( is_array( $atts_raw ) ) {
		$atts_raw = SB_Instagram_Settings::sanitize_raw_atts( $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

	$offset = isset( $_POST['offset'] ) ? (int)$_POST['offset'] : 0;
	$cache_all = isset( $_POST['cache_all'] ) ? $_POST['cache_all'] === 'true' : false;

	$database_settings = sbi_get_database_settings();
	$instagram_feed_settings = new SB_Instagram_Settings( $atts, $database_settings );

	$instagram_feed_settings->set_feed_type_and_terms();
	$instagram_feed_settings->set_transient_name();
	$transient_name = $instagram_feed_settings->get_transient_name();
	$settings = $instagram_feed_settings->get_settings();

	$location = isset( $_POST['location'] ) && in_array( $_POST['location'], array( 'header', 'footer', 'sidebar', 'content' ), true ) ? sanitize_text_field( $_POST['location'] ) : 'unknown';
	$post_id = isset( $_POST['post_id'] ) && $_POST['post_id'] !== 'unknown' ? (int)$_POST['post_id'] : 'unknown';
	$feed_details = array(
		'feed_id' => $transient_name,
		'atts' => $atts,
		'location' => array(
			'post_id' => $post_id,
			'html' => $location
		)
	);

	$can_do_background_tasks = false;
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		$nonce = isset( $_POST['locator_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['locator_nonce'] ) ) : '';
		if ( wp_verify_nonce( $nonce, esc_attr( 'sbi-locator-nonce-' . $post_id . '-' . $transient_name ) ) ) {
			$can_do_background_tasks = true;
		}
	} else {
		$can_do_background_tasks = true;
	}

	if ( $can_do_background_tasks ) {
		sbi_do_background_tasks( $feed_details );
	}

	if ( $cache_all ) {
		$settings['cache_all'] = true;
	}

	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		if ( $transient_name !== $feed_id ) {
			die( 'id does not match' );
		}
	}

	sbi_resize_posts_by_id( $images_need_resizing, $transient_name, $settings );
	sbi_delete_image_cache( $transient_name );

	global $sb_instagram_posts_manager;

	if ( ! $sb_instagram_posts_manager->image_resizing_disabled( $transient_name ) ) {
		$num = $settings['minnum'] * 2 + 5;

		header( 'Content-Type: application/json; charset=utf-8' );
		echo sbi_json_encode( SB_Instagram_Feed::get_resized_images_source_set( $num, $offset - $settings['minnum'], $feed_id, false ) );
		die();
	}

	die( 'resizing success' );
}
add_action( 'wp_ajax_sbi_resized_images_submit', 'sbi_process_submitted_resize_ids' );
add_action( 'wp_ajax_nopriv_sbi_resized_images_submit', 'sbi_process_submitted_resize_ids' );

function sbi_do_locator() {
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options') ) {
		if ( ! isset( $_POST['feed_id'] ) || (strpos( $_POST['feed_id'], 'sbi' ) === false && strpos( $_POST['feed_id'], '*' ) === false ) ) {
			die( 'invalid feed ID');
		}
	}

	$feed_id = sanitize_text_field( wp_unslash( $_POST['feed_id'] ) );

	$atts_raw = isset( $_POST['atts'] ) ? json_decode( wp_unslash( $_POST['atts'] ), true ) : array();
	if ( is_array( $atts_raw ) ) {
		$atts_raw = SB_Instagram_Settings::sanitize_raw_atts( $atts_raw );
	} else {
		$atts_raw = array();
	}
	$atts = $atts_raw; // now sanitized

	$database_settings = sbi_get_database_settings();
	$instagram_feed_settings = new SB_Instagram_Settings( $atts, $database_settings );

	$instagram_feed_settings->set_feed_type_and_terms();
	$instagram_feed_settings->set_transient_name();
	$transient_name = $instagram_feed_settings->get_transient_name();

	$location = isset( $_POST['location'] ) && in_array( $_POST['location'], array( 'header', 'footer', 'sidebar', 'content' ), true ) ? sanitize_text_field( $_POST['location'] ) : 'unknown';
	$post_id = isset( $_POST['post_id'] ) && $_POST['post_id'] !== 'unknown' ? (int)$_POST['post_id'] : 'unknown';
	$feed_details = array(
		'feed_id' => $feed_id,
		'atts' => $atts,
		'location' => array(
			'post_id' => $post_id,
			'html' => $location
		)
	);

	$can_do_background_tasks = false;
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		$nonce = isset( $_POST['locator_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['locator_nonce'] ) ) : '';
		if ( wp_verify_nonce( $nonce, esc_attr( 'sbi-locator-nonce-' . $post_id . '-' . $transient_name ) ) ) {
			$can_do_background_tasks = true;
		}
	} else {
		$can_do_background_tasks = true;
	}

	if ( $can_do_background_tasks ) {
		sbi_do_background_tasks( $feed_details );

		wp_die( 'locating success' );
	}

	wp_die( 'skipped locating' );
}
add_action( 'wp_ajax_sbi_do_locator', 'sbi_do_locator' );
add_action( 'wp_ajax_nopriv_sbi_do_locator', 'sbi_do_locator' );

function sbi_do_background_tasks( $feed_details ) {
	if ( is_admin()
		 && isset( $_GET['page'] )
		 && $_GET['page'] === 'sbi-feed-builder') {
		return;
	}
	$locator = new SB_Instagram_Feed_Locator( $feed_details );
	$locator->add_or_update_entry();
	if ( $locator->should_clear_old_locations() ) {
		$locator->delete_old_locations();
	}
}

/**
 * Outputs an organized error report for the front end.
 * This hooks into the end of the feed before the closing div
 *
 * @param object $instagram_feed
 * @param string $feed_id
 */
function sbi_error_report( $instagram_feed, $feed_id ) {
	global $sb_instagram_posts_manager;
	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		$sb_instagram_posts_manager->reset_frontend_errors();
		return;
	}

	$error_messages = $sb_instagram_posts_manager->get_frontend_errors( $instagram_feed );

	if ( ! empty( $error_messages ) ) {?>
        <div id="sbi_mod_error">
            <span><?php esc_html_e('This error message is only visible to WordPress admins', 'instagram-feed' ); ?></span><br />
			<?php foreach ( $error_messages as $error_message ) {

				echo '<div><strong>' . esc_html( $error_message['error_message'] )  . '</strong>';
				if ( sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
					echo '<br>' . $error_message['admin_only'];
					echo '<br>' . $error_message['frontend_directions'];
				}
				echo '</div>';
			} ?>
        </div>
		<?php
	}

	$sb_instagram_posts_manager->reset_frontend_errors();
}
add_action( 'sbi_before_feed_end', 'sbi_error_report', 10, 2 );

function sbi_delete_image_cache( $transient_name ) {
	$cache = new SB_Instagram_Cache( $transient_name );

	$cache->clear( 'resized_images' );
}

function sbi_current_user_can( $cap ) {
	if ( $cap === 'manage_instagram_feed_options' ) {
		$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';
	}
	$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

	return current_user_can( $cap );
}

function sbi_doing_openssl() {
	return extension_loaded( 'openssl' );
}

/**
 * Debug report added at the end of the feed when sbi_debug query arg is added to a page
 * that has the feed on it.
 *
 * @param object $instagram_feed
 * @param string $feed_id
 */
function sbi_debug_report( $instagram_feed, $feed_id ) {

	if ( ! isset( $_GET['sbi_debug'] ) && ! isset( $_GET['sb_debug'] ) ) {
		return;
	}
	global $sb_instagram_posts_manager;

	$feed = $instagram_feed->get_feed_id();
	$atts = array();
	if ( ! empty( $feed ) ) {
		$atts = array( 'feed' => 1 );
	}

	$settings_obj = new SB_Instagram_Settings( $atts, sbi_get_database_settings() );

	$settings = $settings_obj->get_settings();

	$public_settings_keys = SB_Instagram_Settings::get_public_db_settings_keys();
	?>

	<p>Status</p>
	<ul>
		<li>Time: <?php echo esc_html( date( "Y-m-d H:i:s", time() ) ); ?></li>
		<?php foreach ( $instagram_feed->get_report() as $item ) : ?>
			<li><?php echo esc_html( $item ); ?></li>
		<?php endforeach; ?>

	</ul>
	<p>Settings</p>
	<ul>
		<?php foreach ( $public_settings_keys as $key ) : if ( isset( $settings[ $key ] ) ) : ?>
			<li>
				<small><?php echo esc_html( $key ); ?>:</small>
				<?php if ( ! is_array( $settings[ $key ] ) ) :
					echo esc_html( $settings[ $key ] );
				else : ?>
					<ul>
						<?php foreach ( $settings[ $key ] as $sub_key => $value ) {
							echo '<li><small>' . esc_html( $sub_key ). ':</small> '. esc_html( $value ) . '</li>';
						} ?>
					</ul>
				<?php endif; ?>
			</li>

		<?php endif; endforeach; ?>
	</ul>
	<p>GDPR</p>
	<ul>
		<?php
		$statuses = SB_Instagram_GDPR_Integrations::statuses();
		foreach ( $statuses as $status_key => $value) : ?>
			<li>
				<small><?php echo esc_html( $status_key ); ?>:</small>
				<?php if ( $value == 1 ) { echo 'success'; } else {  echo 'failed'; } ?>
			</li>

		<?php endforeach; ?>
		<li>
			<small>Enabled:</small>
			<?php echo SB_Instagram_GDPR_Integrations::doing_gdpr( $settings ); ?>
		</li>
	</ul>
	<?php
}
add_action( 'sbi_before_feed_end', 'sbi_debug_report', 11, 2 );

function sbi_maybe_palette_styles( $posts, $settings ) {
	$custom_palette_class = trim(SB_Instagram_Display_Elements::get_palette_class( $settings ));
	if ( SB_Instagram_Display_Elements::palette_type( $settings ) !== 'custom' ) {
		return;
	}

	$feed_selector = '.' . $custom_palette_class;
	$header_selector = '.' . trim(SB_Instagram_Display_Elements::get_palette_class( $settings, '_header' ));
	$custom_colors = array(
		'bg1' => $settings['custombgcolor1'],
		'text1' => $settings['customtextcolor1'],
		'text2' => $settings['customtextcolor2'],
		'link1' => $settings['customlinkcolor1'],
		'button1' => $settings['custombuttoncolor1'],
		'button2' => $settings['custombuttoncolor2']
	);
	?>
	<style type="text/css">
		<?php if ( ! empty( $custom_colors['bg1'] ) ) : ?>
		<?php echo $header_selector; ?>,
		#sb_instagram<?php echo $feed_selector; ?>,
		#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer,
		#sbi_lightbox .sbi_lightbox_tooltip,
		#sbi_lightbox .sbi_share_close{
			background: <?php echo esc_html( $custom_colors['bg1'] ); ?>;
		}
		<?php endif; ?>
		<?php if ( ! empty( $custom_colors['text1'] ) ) : ?>
		#sb_instagram<?php echo $feed_selector; ?> .sbi_caption,
		#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-details .sbi_lb-caption,
		#sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-number,
		#sbi_lightbox.sbi_lb-comments-enabled .sbi_lb-commentBox p{
			color: <?php echo esc_html( $custom_colors['text1'] ); ?>;
		}
		<?php endif; ?>
		<?php if ( ! empty( $custom_colors['text2'] ) ) : ?>
		<?php echo $header_selector; ?> .sbi_bio,
									   #sb_instagram<?php echo $feed_selector; ?> .sbi_meta {
										   color: <?php echo esc_html( $custom_colors['text2'] ); ?>;
									   }
		<?php endif; ?>
		<?php if ( ! empty( $custom_colors['link1'] ) ) : ?>
		<?php echo $header_selector; ?> a,
									   #sb_instagram<?php echo $feed_selector; ?> .sbi_expand a,
									   #sbi_lightbox .sbi_lb-outerContainer .sbi_lb-dataContainer .sbi_lb-details a,
									   #sbi_lightbox.sbi_lb-comments-enabled .sbi_lb-commentBox .sbi_lb-commenter {
										   color: <?php echo esc_html( $custom_colors['link1'] ); ?>;
									   }
		<?php endif; ?>
		<?php if ( ! empty( $custom_colors['button1'] ) ) : ?>
		#sb_instagram<?php echo $feed_selector; ?> #sbi_load .sbi_load_btn {
			background: <?php echo esc_html( $custom_colors['button1'] ); ?>;
		}
		<?php endif; ?>
		<?php if ( ! empty( $custom_colors['button2'] ) ) : ?>
		#sb_instagram<?php echo $feed_selector; ?> #sbi_load .sbi_follow_btn a {
			background: <?php echo esc_html( $custom_colors['button2'] ); ?>;
		}
		<?php endif; ?>
	</style>
	<?php
}
add_action( 'sbi_after_feed', 'sbi_maybe_palette_styles', 10, 2 );

function sbi_maybe_button_hover_styles( $posts, $settings ) {
	$follow_hover_color = str_replace( '#', '', SB_Instagram_Display_Elements::get_follow_hover_color( $settings ) );
	$load_hover_color = str_replace( '#', '', SB_Instagram_Display_Elements::get_load_button_hover_color( $settings ) );

	if ( empty( $load_hover_color ) && empty( $follow_hover_color ) ) {
		return;
	}

	?>
	<style type="text/css">
		<?php if ( ! empty( $load_hover_color ) ) : ?>
		#sb_instagram #sbi_load .sbi_load_btn:hover{
			outline: none;
			box-shadow: inset 0 0 20px 20px <?php echo sanitize_hex_color( '#' . $load_hover_color ); ?>;
		}
		<?php endif; ?>
		<?php if ( ! empty( $follow_hover_color ) ) : ?>

		#sb_instagram .sbi_follow_btn a:hover,
		#sb_instagram .sbi_follow_btn a:focus{
			outline: none;
			box-shadow: inset 0 0 10px 20px <?php echo sanitize_hex_color( '#' . $follow_hover_color ); ?>;
		}
		<?php endif; ?>
	</style>
	<?php
}
add_action( 'sbi_after_feed', 'sbi_maybe_button_hover_styles', 10, 2 );


/**
 * Uses post IDs to process images that may need resizing
 *
 * @param array $ids
 * @param string $transient_name
 * @param array $settings
 * @param int $offset
 */
function sbi_resize_posts_by_id( $ids, $transient_name, $settings, $offset = 0 ) {
	$instagram_feed = new SB_Instagram_Feed( $transient_name );

	$instagram_feed->set_cache( MONTH_IN_SECONDS, $settings );

	if ( $instagram_feed->regular_cache_exists() ) {
		// set_post_data_from_cache
		$instagram_feed->set_post_data_from_cache();

		$cached_post_data = $instagram_feed->get_post_data();
	} elseif ( sbi_current_user_can( 'manage_instagram_feed_options' ) && is_admin() ) {
		$customizer_cache = new SB_Instagram_Cache( $transient_name, 1, MONTH_IN_SECONDS );

		$cached_post_data = $customizer_cache->get_customizer_cache();
	} else {
		return array();
	}

	if ( ! isset( $settings['cache_all'] ) || ! $settings['cache_all'] ) {
		$num_ids = count( $ids );
		$found_posts = array();
		$i = 0;
		while ( count( $found_posts) < $num_ids && isset( $cached_post_data[ $i ] ) ) {
			if ( ! empty( $cached_post_data[ $i ]['id'] ) && in_array( $cached_post_data[ $i ]['id'], $ids, true ) ) {
				$found_posts[] = $cached_post_data[ $i ];
			}
			$i++;
		}
	} else {
		$found_posts = array_slice( $cached_post_data, 0, 50 );
	}


	$fill_in_timestamp = date( 'Y-m-d H:i:s', time() + 120 );

	if ( $offset !== 0 ) {
		$fill_in_timestamp = date( 'Y-m-d H:i:s', strtotime( $instagram_feed->get_earliest_time_stamp( $transient_name ) ) - 120 );
	}

	$image_sizes = array(
		'personal' => array( 'full' => 640, 'low' => 320, 'thumb' => 150 ),
		'business' => array( 'full' => 640, 'low' => 320, 'thumb' => 150 )
	);

	$post_set = new SB_Instagram_Post_Set( $found_posts, $transient_name, $fill_in_timestamp, $image_sizes );

	$post_set->maybe_save_update_and_resize_images_for_posts();
}

function sbi_create_local_avatar( $username, $file_name ) {
	return SB_Instagram_Connected_Account::create_local_avatar( $username, $file_name );
}

/**
 * Get the settings in the database with defaults
 *
 * @return array
 */
function sbi_get_database_settings() {
	$defaults = array(
		'sb_instagram_at'                   => '',
		'sb_instagram_user_id'              => '',
		'sb_instagram_preserve_settings'    => '',
		'sb_instagram_ajax_theme'           => false,
		'sb_instagram_disable_resize'       => false,
		'sb_instagram_cache_time'           => 1,
		'sb_instagram_cache_time_unit'      => 'hours',
		'sbi_caching_type'                  => 'background',
		'sbi_cache_cron_interval'           => '12hours',
		'sbi_cache_cron_time'               => '1',
		'sbi_cache_cron_am_pm'              => 'am',
		'sb_instagram_width'                => '100',
		'sb_instagram_width_unit'           => '%',
		'sb_instagram_feed_width_resp'      => false,
		'sb_instagram_height'               => '',
		'sb_instagram_num'                  => '20',
		'sb_instagram_height_unit'          => '',
		'sb_instagram_cols'                 => '4',
		'sb_instagram_disable_mobile'       => false,
		'sb_instagram_image_padding'        => '5',
		'sb_instagram_image_padding_unit'   => 'px',
		'sb_instagram_sort'                 => 'none',
		'sb_instagram_background'           => '',
		'sb_instagram_show_btn'             => true,
		'sb_instagram_btn_background'       => '',
		'sb_instagram_btn_text_color'       => '',
		'sb_instagram_btn_text'             => __( 'Load More...', 'instagram-feed' ),
		'sb_instagram_image_res'            => 'auto',
		//Header
		'sb_instagram_show_header'          => true,
		'sb_instagram_header_size'  => 'small',
		'sb_instagram_header_color'         => '',
		//Follow button
		'sb_instagram_show_follow_btn'      => true,
		'sb_instagram_folow_btn_background' => '',
		'sb_instagram_follow_btn_text_color' => '',
		'sb_instagram_follow_btn_text'      => __( 'Follow on Instagram', 'instagram-feed' ),
		//Misc
		'sb_instagram_custom_css'           => '',
		'sb_instagram_custom_js'            => '',
		'sb_instagram_cron'                 => 'no',
		'sb_instagram_backup' => true,
		'sb_ajax_initial'    => false,
		'enqueue_css_in_shortcode' => false,
		'sb_instagram_disable_mob_swipe' => false,
		'sb_instagram_disable_awesome'      => false
	);
	$sbi_settings = get_option( 'sb_instagram_settings', array() );

	return array_merge( $defaults, $sbi_settings );
}

/**
 * May include support for templates in theme folders in the future
 *
 * @since 2.1 custom templates supported
 */
function sbi_get_feed_template_part( $part, $settings = array() ) {
	$file = '';

	$using_custom_templates_in_theme = apply_filters( 'sbi_use_theme_templates', $settings['customtemplates'] );
	$generic_path = trailingslashit( SBI_PLUGIN_DIR ) . 'templates/';

	if ( $using_custom_templates_in_theme ) {
		$custom_header_template = locate_template( 'sbi/header.php', false, false );
		$custom_item_template = locate_template( 'sbi/item.php', false, false );
		$custom_footer_template = locate_template( 'sbi/footer.php', false, false );
		$custom_feed_template = locate_template( 'sbi/feed.php', false, false );
	} else {
		$custom_header_template = false;
		$custom_item_template = false;
		$custom_footer_template = false;
		$custom_feed_template = false;
	}

	if ( $part === 'header' ) {
        if ( $custom_header_template ) {
            $file = $custom_header_template;
        } else {
            $file = $generic_path . 'header.php';
        }
	} elseif ( $part === 'item' ) {
		if ( $custom_item_template ) {
			$file = $custom_item_template;
		} else {
			$file = $generic_path . 'item.php';
		}
	} elseif ( $part === 'footer' ) {
		if ( $custom_footer_template ) {
			$file = $custom_footer_template;
		} else {
			$file = $generic_path . 'footer.php';
		}
	} elseif ( $part === 'feed' ) {
		if ( $custom_feed_template ) {
			$file = $custom_feed_template;
		} else {
			$file = $generic_path . 'feed.php';
		}
	}

	return $file;
}

/**
 * Triggered by a cron event to update feeds
 */
function sbi_cron_updater() {

	$cron_updater = new SB_Instagram_Cron_Updater();

	$cron_updater->do_feed_updates();

	sbi_do_background_tasks( array() );
}
add_action( 'sbi_feed_update', 'sbi_cron_updater' );

/**
 * @param $maybe_dirty
 *
 * @return string
 */
function sbi_maybe_clean( $maybe_dirty ) {
	$encryption = new SB_Instagram_Data_Encryption();

	$decrypted = $encryption->decrypt( $maybe_dirty );
	if ( $decrypted ) {
		$maybe_dirty = $decrypted;
	}
	if ( substr_count ( $maybe_dirty , '.' ) < 3 ) {
		return str_replace( '634hgdf83hjdj2', '', $maybe_dirty );
	}

	$parts = explode( '.', trim( $maybe_dirty ) );
	$last_part = $parts[2] . $parts[3];
	$cleaned = $parts[0] . '.' . base64_decode( $parts[1] ) . '.' . base64_decode( $last_part );

	return $cleaned;
}

/**
 * If there are more feeds than a single batch
 */
function sbi_process_additional_batch() {
	$sbi_settings = sbi_get_database_settings();

	$args = array(
		'cron_update' => true,
		'additional_batch' => true,
	);
	$cron_records = \InstagramFeed\Builder\SBI_Db::feed_caches_query( $args );

	$num = count( $cron_records );
	if ( $num === \InstagramFeed\Builder\SBI_Db::RESULTS_PER_CRON_UPDATE ) {
		wp_schedule_single_event( time() + 120, 'sbi_cron_additional_batch' );
	}

	SB_Instagram_Cron_Updater::update_batch( $cron_records );

	sbi_do_background_tasks( array() );
}
add_action( 'sbi_cron_additional_batch', 'sbi_process_additional_batch' );

/**
 * @param $whole
 *
 * @return string
 */
function sbi_get_parts( $whole ) {
	if ( substr_count ( $whole , '.' ) !== 2 ) {
		return $whole;
	}

	$parts = explode( '.', trim( $whole ) );
	$return = $parts[0] . '.' . base64_encode( $parts[1] ). '.' . base64_encode( $parts[2] );

	return substr( $return, 0, 40 ) . '.' . substr( $return, 40, 100 );
}

/**
 * @param $a
 * @param $b
 *
 * @return false|int
 */
function sbi_date_sort( $a, $b ) {
	$time_stamp_a = SB_Instagram_Parse::get_timestamp( $a );
	$time_stamp_b = SB_Instagram_Parse::get_timestamp( $b );

	if ( isset( $time_stamp_a ) ) {
		return $time_stamp_b - $time_stamp_a;
	} else {
		return rand ( -1, 1 );
	}
}

function sbi_code_check( $code ) {
	if ( strpos( $code, '634hgdf83hjdj2') !== false ) {
		return true;
	}
	return false;
}

function sbi_fixer( $code ) {
	if ( strpos( $code, '634hgdf83hjdj2') !== false ) {
		return $code;
	} else {
		return substr_replace( $code , '634hgdf83hjdj2', 15, 0 );
	}
}

/**
 * @param $a
 * @param $b
 *
 * @return false|int
 */
function sbi_rand_sort( $a, $b ) {
    return rand ( -1, 1 );
}

/**
 * @return string
 *
 * @since 2.1.1
 */
function sbi_get_resized_uploads_url() {
	$upload = wp_upload_dir();

	$base_url = $upload['baseurl'];
	$home_url = home_url();

	if ( strpos( $home_url, 'https:' ) !== false ) {
		$base_url = str_replace( 'http:', 'https:', $base_url );
	}

	$resize_url = apply_filters( 'sbi_resize_url', trailingslashit( $base_url ) . trailingslashit( SBI_UPLOADS_NAME ) );

	return $resize_url;
}

/**
 * Converts a hex code to RGB so opacity can be
 * applied more easily
 *
 * @param $hex
 *
 * @return string
 */
function sbi_hextorgb( $hex ) {
	// allows someone to use rgb in shortcode
	if ( strpos( $hex, ',' ) !== false ) {
		return $hex;
	}

	$hex = str_replace( '#', '', $hex );

	if ( strlen( $hex ) === 3 ) {
		$r = hexdec( substr( $hex,0,1 ).substr( $hex,0,1 ) );
		$g = hexdec( substr( $hex,1,1 ).substr( $hex,1,1 ) );
		$b = hexdec( substr( $hex,2,1 ).substr( $hex,2,1 ) );
	} else {
		$r = hexdec( substr( $hex,0,2 ) );
		$g = hexdec( substr( $hex,2,2 ) );
		$b = hexdec( substr( $hex,4,2 ) );
	}
	$rgb = array( $r, $g, $b );

	return implode( ',', $rgb ); // returns the rgb values separated by commas
}

function sbi_is_url( $input ) {
	return (bool) filter_var( $input, FILTER_VALIDATE_URL );
}


/**
 * Added to workaround MySQL tables that don't use utf8mb4 character sets
 *
 * @since 2.2.1/5.3.1
 */
function sbi_sanitize_emoji( $string ) {
	$encoded = array(
		'jsonencoded' => $string
	);
	return sbi_json_encode( $encoded );
}

/**
 * Added to workaround MySQL tables that don't use utf8mb4 character sets
 *
 * @since 2.2.1/5.3.1
 */
function sbi_decode_emoji( $string ) {
	if ( strpos( $string, '{"' ) !== false ) {
		$decoded = json_decode( $string, true );
		return $decoded['jsonencoded'];
	}
	return $string;
}

function sbi_sanitize_instagram_ids( $raw_id ) {
	return preg_replace( '/[^0-9_]/', '', $raw_id );
}

function sbi_sanitize_alphanumeric_and_equals( $value ) {
	return preg_replace( '/[^A-Za-z0-9=]/', '', $value );
}

function sbi_sanitize_username( $value ) {
	return preg_replace( '/[^A-Za-z0-9_.]/', '', $value );
}

/**
 * @return int
 */
function sbi_get_utc_offset() {
	return get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
}

/**
 * Deletes any cache or setting that may contain Instagram platform data
 */
function sbi_delete_all_platform_data() {
	global $sb_instagram_posts_manager;
	$manager = new SB_Instagram_Data_Manager();
	$sb_instagram_posts_manager->add_action_log( 'Deleted all platform data.' );
	$sb_instagram_posts_manager->reset_api_errors();
	$manager->delete_caches();
	$manager->delete_comments_data();
	$manager->delete_hashtag_data();
	SB_Instagram_Connected_Account::update_connected_accounts( array() );
}

function sbi_get_current_timestamp() {
	$current_time = time();

	//$current_time = strtotime( 'November 25, 2022' ) + 1;

	return $current_time;
}

function sbi_get_current_time() {
	return sbi_get_current_timestamp();
}

function sbi_is_after_deprecation_deadline() {
	return true;
}

function sbi_json_encode( $thing ) {
    if ( function_exists( 'wp_json_encode' ) ) {
        return wp_json_encode( $thing );
    } else {
        return json_encode( $thing );
    }
}

function sbi_private_account_near_expiration( $connected_account ) {
	$expires_in = max( 0, floor( ($connected_account['expires_timestamp'] - time()) / DAY_IN_SECONDS ) );
	return $expires_in < 10;
}


function sbi_update_connected_account( $account_id, $to_update ) {
	$args = [
		'id' => $account_id
	];
	$results = InstagramFeed\Builder\SBI_Db::source_query( $args );

	if ( ! empty( $results ) ) {
		$source = $results[0];
		$info = ! empty( $source['info'] ) ? json_decode( $source['info'], true ) : array();

		if ( isset( $to_update['private'] ) ) {
			$info['private'] = $to_update['private'];
		}

		foreach ( $to_update as $key => $value ) {
			if ( isset( $source[ $key ] ) ) {
				$source[ $key ] = $value;
			}
		}

		$source['id'] = $account_id;

		InstagramFeed\Builder\SBI_Source::update_or_insert( $source );
	}
}

/**
 * Used to clear caches when transients aren't working
 * properly
 */
function sb_instagram_cron_clear_cache() {

}

function sbi_clear_caches() {
	global $wpdb;

	$cache_table_name = $wpdb->prefix . 'sbi_feed_caches';

	$sql = "
		UPDATE $cache_table_name
		SET cache_value = ''
		WHERE cache_key NOT IN ( 'posts_backup', 'header_backup' );";
	$wpdb->query( $sql );
}

/**
 * When certain events occur, page caches need to
 * clear or errors occur or changes will not be seen
 */
function sb_instagram_clear_page_caches() {

    $clear_page_caches = apply_filters( 'sbi_clear_page_caches', true );
    if ( ! $clear_page_caches ) {
        return;
    }

	if ( isset( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ){
		/* Clear WP fastest cache*/
		$GLOBALS['wp_fastest_cache']->deleteCache();
	}

	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}

	if ( class_exists('W3_Plugin_TotalCacheAdmin') ) {
		$plugin_totalcacheadmin = & w3_instance('W3_Plugin_TotalCacheAdmin');

		$plugin_totalcacheadmin->flush_all();
	}

	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}

	if ( class_exists( 'autoptimizeCache' ) ) {
		/* Clear autoptimize */
		autoptimizeCache::clearall();
	}

	// Litespeed Cache
	if ( method_exists( 'LiteSpeed_Cache_API', 'purge' ) ) {
		LiteSpeed_Cache_API::purge( 'esi.instagram-feed' );
    }
}

/**
 * Makes the JavaScript file available and enqueues the stylesheet
 * for the plugin
 */
function sb_instagram_scripts_enqueue( $enqueue = false ) {
	//Register the script to make it available

	//Options to pass to JS file
	$sb_instagram_settings = get_option( 'sb_instagram_settings' );

	$js_file = 'js/sbi-scripts.min.js';
	if ( isset( $_GET['sbi_debug'] ) ) {
		$js_file = 'js/sbi-scripts.js';
	}

	if ( isset( $sb_instagram_settings['enqueue_js_in_head'] ) && $sb_instagram_settings['enqueue_js_in_head'] ) {
		wp_enqueue_script( 'sbi_scripts', trailingslashit( SBI_PLUGIN_URL ) . $js_file, array('jquery'), SBIVER, false );
	} else {
		wp_register_script( 'sbi_scripts', trailingslashit( SBI_PLUGIN_URL ) . $js_file, array('jquery'), SBIVER, true );
	}

	if ( isset( $sb_instagram_settings['enqueue_css_in_shortcode'] ) && $sb_instagram_settings['enqueue_css_in_shortcode'] ) {
		wp_register_style( 'sbi_styles', trailingslashit( SBI_PLUGIN_URL ) . 'css/sbi-styles.min.css', array(), SBIVER );
	} else {
		wp_enqueue_style( 'sbi_styles', trailingslashit( SBI_PLUGIN_URL ) . 'css/sbi-styles.min.css', array(), SBIVER );
	}


	$data = array(
		'font_method' => 'svg',
		'resized_url' => sbi_get_resized_uploads_url(),
		'placeholder' => trailingslashit( SBI_PLUGIN_URL ) . 'img/placeholder.png',
		'ajax_url'  => admin_url( 'admin-ajax.php' ),
	);
	//Pass option to JS file
	wp_localize_script('sbi_scripts', 'sb_instagram_js_options', $data );

	if ( $enqueue || SB_Instagram_Blocks::is_gb_editor() ) {
		wp_enqueue_style( 'sbi_styles' );
		wp_enqueue_script( 'sbi_scripts' );
	}
}
add_action( 'wp_enqueue_scripts', 'sb_instagram_scripts_enqueue', 2 );

/**
 * Adds the ajax url and custom JavaScript to the page
 */
function sb_instagram_custom_js() {
	$options = get_option('sb_instagram_settings');
	isset($options[ 'sb_instagram_custom_js' ]) ? $sb_instagram_custom_js = trim($options['sb_instagram_custom_js']) : $sb_instagram_custom_js = '';

	echo '<!-- Instagram Feed JS -->';
	echo "\r\n";
	echo '<script type="text/javascript">';
	echo "\r\n";
	echo 'var sbiajaxurl = "' . admin_url('admin-ajax.php') . '";';

	if ( !empty( $sb_instagram_custom_js ) ) {
		echo "\r\n";
		echo "jQuery( document ).ready(function($) {";
		echo "\r\n";
		echo "window.sbi_custom_js = function(){";
		echo "\r\n";
		echo stripslashes($sb_instagram_custom_js);
		echo "\r\n";
		echo "}";
		echo "\r\n";
		echo "});";
    }

	echo "\r\n";
	echo '</script>';
	echo "\r\n";
}
add_action( 'wp_footer', 'sb_instagram_custom_js' );

//Custom CSS
add_action( 'wp_head', 'sb_instagram_custom_css' );
function sb_instagram_custom_css() {
	$options = get_option( 'sb_instagram_settings', array() );

	isset($options[ 'sb_instagram_custom_css' ]) ? $sb_instagram_custom_css = trim($options['sb_instagram_custom_css']) : $sb_instagram_custom_css = '';

	//Show CSS if an admin (so can see Hide Photos link), if including Custom CSS or if hiding some photos
	( current_user_can( 'edit_posts' ) || !empty($sb_instagram_custom_css) ) ? $sbi_show_css = true : $sbi_show_css = false;

	if( $sbi_show_css ) echo '<!-- Instagram Feed CSS -->';
	if( $sbi_show_css ) echo "\r\n";
	if( $sbi_show_css ) echo '<style type="text/css">';

	if( !empty($sb_instagram_custom_css) ){
		echo "\r\n";
		echo wp_strip_all_tags( stripslashes( $sb_instagram_custom_css ) );
	}

	if( current_user_can( 'edit_posts' ) ){
		echo "\r\n";
		echo "#sbi_mod_link, #sbi_mod_error{ display: block !important; width: 100%; float: left; box-sizing: border-box; }";
	}

	if( $sbi_show_css ) echo "\r\n";
	if( $sbi_show_css ) echo '</style>';
	if( $sbi_show_css ) echo "\r\n";
}

/**
 * Used to change the number of posts in the api request. Useful for filtered posts
 * or special caching situations.
 *
 * @param int $num
 * @param array $settings
 *
 * @return int
 */
function sbi_raise_num_in_request( $num, $settings ) {
    if ( $settings['sortby'] === 'random' ) {
        if ( $num > 5 ) {
	        return min( $num * 4, 100 );
        } else {
            return 20;
        }
    }
    return $num;
}
add_filter( 'sbi_num_in_request', 'sbi_raise_num_in_request', 5, 2 );

/**
 * Load the critical notice for logged in users.
 */
function sbi_critical_error_notice() {
	// Don't do anything for guests.
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Only show this to users who are not tracked.
	if ( ! current_user_can( 'manage_instagram_feed_options' ) ) {
		return;
	}

	global $sb_instagram_posts_manager;
    if ( ! $sb_instagram_posts_manager->are_critical_errors() ) {
        return;
    }


	// Don't show if already dismissed.
	if ( get_option( 'sbi_dismiss_critical_notice', false ) ) {
		return;
	}

	$db_settings = sbi_get_database_settings();
	if ( isset( $db_settings['disable_admin_notice'] ) && ($db_settings['disable_admin_notice'] === 'on' || $db_settings['disable_admin_notice'] === true) ) {
		return;
	}

	?>
    <div class="sbi-critical-notice sbi-critical-notice-hide">
        <div class="sbi-critical-notice-icon">
            <img src="<?php echo esc_url( SBI_PLUGIN_URL . 'img/insta-logo.png' ); ?>" width="45" alt="Instagram Feed icon" />
        </div>
        <div class="sbi-critical-notice-text">
            <h3><?php esc_html_e( 'Instagram Feed Critical Issue', 'instagram-feed' ); ?></h3>
            <p>
				<?php
				$doc_url = admin_url( 'admin.php?page=sbi-settings' );
				// Translators: %s is the link to the article where more details about critical are listed.
				printf( esc_html__( 'An issue is preventing your Instagram Feeds from updating. %1$sResolve this issue%2$s.', 'instagram-feed' ), '<a href="' . esc_url( $doc_url ) . '" target="_blank">', '</a>' );
				?>
            </p>
        </div>
        <div class="sbi-critical-notice-close">&times;</div>
    </div>
    <style type="text/css">
        .sbi-critical-notice {
            position: fixed;
            bottom: 20px;
            right: 15px;
            font-family: Arial, Helvetica, "Trebuchet MS", sans-serif;
            background: #fff;
            box-shadow: 0 0 10px 0 #dedede;
            padding: 10px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 325px;
            max-width: calc( 100% - 30px );
            border-radius: 6px;
            transition: bottom 700ms ease;
            z-index: 10000;
        }

        .sbi-critical-notice h3 {
            font-size: 13px;
            color: #222;
            font-weight: 700;
            margin: 0 0 4px;
            padding: 0;
            line-height: 1;
            border: none;
        }

        .sbi-critical-notice p {
            font-size: 12px;
            color: #7f7f7f;
            font-weight: 400;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            border: none;
        }

        .sbi-critical-notice p a {
            color: #7f7f7f;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            text-decoration: underline;
            font-weight: 400;
        }

        .sbi-critical-notice p a:hover {
            color: #666;
        }

        .sbi-critical-notice-icon img {
            height: auto;
            display: block;
            margin: 0;
        }

        .sbi-critical-notice-icon {
            padding: 0;
            border-radius: 4px;
            flex-grow: 0;
            flex-shrink: 0;
            margin-right: 12px;
            overflow: hidden;
        }

        .sbi-critical-notice-close {
            padding: 10px;
    		margin: -12px -9px 0 0;
            border: none;
            box-shadow: none;
            border-radius: 0;
            color: #7f7f7f;
            background: transparent;
            line-height: 1;
            align-self: flex-start;
            cursor: pointer;
            font-weight: 400;
        }
        .sbi-critical-notice-close:hover,
        .sbi-critical-notice-close:focus{
        	color: #111;
        }

        .sbi-critical-notice.sbi-critical-notice-hide {
            bottom: -200px;
        }
    </style>
	<?php

	if ( ! wp_script_is( 'jquery', 'queue' ) ) {
		wp_enqueue_script( 'jquery' );
	}
	?>
    <script>
        if ( 'undefined' !== typeof jQuery ) {
            jQuery( document ).ready( function ( $ ) {
                /* Don't show the notice if we don't have a way to hide it (no js, no jQuery). */
                $( document.querySelector( '.sbi-critical-notice' ) ).removeClass( 'sbi-critical-notice-hide' );
                $( document.querySelector( '.sbi-critical-notice-close' ) ).on( 'click', function ( e ) {
                    e.preventDefault();
                    $( this ).closest( '.sbi-critical-notice' ).addClass( 'sbi-critical-notice-hide' );
                    $.ajax( {
                        url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                        method: 'POST',
                        data: {
                            action: 'sbi_dismiss_critical_notice',
                            nonce: '<?php echo esc_js( wp_create_nonce( 'sbi-critical-notice' ) ); ?>',
                        }
                    } );
                } );
            } );
        }
    </script>
	<?php
}

add_action( 'wp_footer', 'sbi_critical_error_notice', 300 );

/**
 * Ajax handler to hide the critical notice.
 */
function sbi_dismiss_critical_notice() {

	check_ajax_referer( 'sbi-critical-notice', 'nonce' );

	update_option( 'sbi_dismiss_critical_notice', 1, false );

	wp_die();

}

add_action( 'wp_ajax_sbi_dismiss_critical_notice', 'sbi_dismiss_critical_notice' );

function sbi_schedule_report_email() {
	$options = get_option( 'sb_instagram_settings', array() );

	$input = isset( $options[ 'email_notification' ] ) ? $options[ 'email_notification' ] : 'monday';
	$timestamp = strtotime( 'next ' . $input );
	$timestamp = $timestamp + (3600 * 24 * 7);

	$six_am_local = $timestamp + sbi_get_utc_offset() + (6*60*60);

	wp_schedule_event( $six_am_local, 'sbiweekly', 'sb_instagram_feed_issue_email' );
}

function sbi_send_report_email() {
	$options = get_option('sb_instagram_settings' );

	$to_string = ! empty( $options['email_notification_addresses'] ) ? str_replace( ' ', '', $options['email_notification_addresses'] ) : get_option( 'admin_email', '' );

	$to_array_raw = explode( ',', $to_string );
	$to_array = array();

	foreach ( $to_array_raw as $email ) {
		if ( is_email( $email ) ) {
			$to_array[] = $email;
		}
	}

	if ( empty( $to_array ) ) {
		return false;
	}
	$from_name = esc_html( wp_specialchars_decode( get_bloginfo( 'name' ) ) );
	$email_from = $from_name . ' <' . get_option( 'admin_email', $to_array[0] ) . '>';
	$header_from  = "From: " . $email_from;

	$headers = array( 'Content-Type: text/html; charset=utf-8', $header_from );

	$header_image = SBI_PLUGIN_URL . 'img/balloon-120.png';

	$link = admin_url( 'admin.php?page=sbi-settings' );
	//&tab=customize-advanced
	$footer_link = admin_url('admin.php?page=sbi-settings&flag=emails');

	$is_expiration_notice = false;

	if ( isset( $options['connected_accounts'] ) ) {
		foreach ( $options['connected_accounts'] as $account ) {
			if ( $account['type'] === 'basic'
			     && isset( $account['private'] )
			     && sbi_private_account_near_expiration( $account ) ) {
				$is_expiration_notice = true;
			}
		}
	}

	if ( ! $is_expiration_notice ) {
		$title = sprintf( __( 'Instagram Feed Report for %s', 'instagram-feed' ), str_replace( array( 'http://', 'https://' ), '', home_url() ) );
		$bold = __( 'There\'s an Issue with an Instagram Feed on Your Website', 'instagram-feed' );
		$details = '<p>' . __( 'An Instagram feed on your website is currently unable to connect to Instagram to retrieve new posts. Don\'t worry, your feed is still being displayed using a cached version, but is no longer able to display new posts.', 'instagram-feed' ) . '</p>';
		$details .= '<p>' . sprintf( __( 'This is caused by an issue with your Instagram account connecting to the Instagram API. For information on the exact issue and directions on how to resolve it, please visit the %sInstagram Feed settings page%s on your website.', 'instagram-feed' ), '<a href="' . esc_url( $link ) . '">', '</a>' ). '</p>';
	} else {
		$title = __( 'Your Private Instagram Feed Account Needs to be Reauthenticated', 'instagram-feed' );
		$bold = __( 'Access Token Refresh Needed', 'instagram-feed' );
		$details = '<p>' . __( 'As your Instagram account is set to be "Private", Instagram requires that you reauthenticate your account every 60 days. This a courtesy email to let you know that you need to take action to allow the Instagram feed on your website to continue updating. If you don\'t refresh your account, then a backup cache will be displayed instead.', 'instagram-feed' ) . '</p>';
		$details .= '<p>' . sprintf( __( 'To prevent your account expiring every 60 days %sswitch your account to be public%s. For more information and to refresh your account, click here to visit the %sInstagram Feed settings page%s on your website.', 'instagram-feed' ), '<a href="https://help.instagram.com/116024195217477/In">', '</a>', '<a href="' . esc_url( $link ) . '">', '</a>' ). '</p>';
	}
	$message_content = '<h6 style="padding:0;word-wrap:normal;font-family:\'Helvetica Neue\',Helvetica,Arial,sans-serif;font-weight:bold;line-height:130%;font-size: 16px;color:#444444;text-align:inherit;margin:0 0 20px 0;Margin:0 0 20px 0;">' . $bold . '</h6>' . $details;
	include_once SBI_PLUGIN_DIR . 'inc/class-sb-instagram-education.php';
	$educator = new SB_Instagram_Education();
	$dyk_message = $educator->dyk_display();
	ob_start();
	include SBI_PLUGIN_DIR . 'inc/email.php';
	$email_body = ob_get_contents();
	ob_get_clean();

	$sent = wp_mail( $to_array, $title, $email_body, $headers );

	return $sent;
}

function sbi_maybe_send_feed_issue_email() {
	global $sb_instagram_posts_manager;
	if ( ! $sb_instagram_posts_manager->are_critical_errors() ) {
		return;
	}
	$options = get_option('sb_instagram_settings' );

	if ( isset( $options['enable_email_report'] ) && empty( $options['enable_email_report'] ) ) {
		return;
	}

	sbi_send_report_email();
}
add_action( 'sb_instagram_feed_issue_email', 'sbi_maybe_send_feed_issue_email' );

function sbi_update_option( $option_name, $option_value, $autoload = true ) {
	return update_option( $option_name, $option_value, $autoload = true );
}

function sbi_get_option( $option_name, $default ) {
	return get_option( $option_name, $default );
}

function sbi_is_pro_version() {
	return ! defined( 'SBI_PLUGIN_NAME' ) || SBI_PLUGIN_NAME !== 'Instagram Feed Free';
}

function sbi_defaults() {
	$defaults = array(
		'sb_instagram_at'                   => '',
		'sb_instagram_user_id'              => '',
		'sb_instagram_preserve_settings'    => '',
		'sb_instagram_ajax_theme'           => false,
		'sb_instagram_disable_resize'       => false,
		'sb_instagram_cache_time'           => 1,
		'sb_instagram_cache_time_unit'      => 'hours',
		'sbi_caching_type'                  => 'background',
		'sbi_cache_cron_interval'           => '12hours',
		'sbi_cache_cron_time'               => '1',
		'sbi_cache_cron_am_pm'              => 'am',
		'sb_instagram_width'                => '100',
		'sb_instagram_width_unit'           => '%',
		'sb_instagram_feed_width_resp'      => false,
		'sb_instagram_height'               => '',
		'sb_instagram_num'                  => '20',
		'sb_instagram_height_unit'          => '',
		'sb_instagram_cols'                 => '4',
		'sb_instagram_disable_mobile'       => false,
		'sb_instagram_image_padding'        => '5',
		'sb_instagram_image_padding_unit'   => 'px',
		'sb_instagram_sort'                 => 'none',
		'sb_instagram_background'           => '',
		'sb_instagram_show_btn'             => true,
		'sb_instagram_btn_background'       => '',
		'sb_instagram_btn_text_color'       => '',
		'sb_instagram_btn_text'             => __( 'Load More...', 'instagram-feed' ),
		'sb_instagram_image_res'            => 'auto',
		'sb_instagram_lightbox_comments'    => true,
		'sb_instagram_num_comments'         => 20,
		'sb_instagram_show_bio' => true,
		'sb_instagram_show_followers' => true,
		//Header
		'sb_instagram_show_header'          => true,
		'sb_instagram_header_size'  => 'small',
		'sb_instagram_header_color'         => '',
		'sb_instagram_stories' => true,
		'sb_instagram_stories_time' => 5000,
		//Follow button
		'sb_instagram_show_follow_btn'      => true,
		'sb_instagram_folow_btn_background' => '',
		'sb_instagram_follow_btn_text_color' => '',
		'sb_instagram_follow_btn_text'      => __( 'Follow on Instagram', 'instagram-feed' ),
		//Misc
		'sb_instagram_custom_css'           => '',
		'sb_instagram_custom_js'            => '',
		'sb_instagram_cron'                 => 'no',
		'sb_instagram_backup' => true,
		'sb_ajax_initial'    => false,
		'enqueue_css_in_shortcode' => false,
		'enqueue_js_in_head' => false,
		'disable_js_image_loading' => false,
		'disable_admin_notice' => false,
		'enable_email_report' => true,
		'email_notification' => 'monday',
		'email_notification_addresses' => get_option( 'admin_email' ),

		'sb_instagram_disable_mob_swipe' => false,
		'sb_instagram_disable_awesome'      => false,
		'sb_instagram_disable_font'      => false,
		'gdpr'      => 'auto'
	);

	return $defaults;
}

function sbi_doing_customizer( $settings ) {
    return ! empty( $settings['customizer'] ) && $settings['customizer'] == true;
}

function sbi_header_html( $settings, $header_data, $location = 'inside' ) {
	$customizer = sbi_doing_customizer( $settings );
	if ( ! $customizer && ($location === 'inside' && $settings['headeroutside']) ) {
		return;
	} elseif ( ! $customizer && ($location === 'outside' && ! $settings['headeroutside']) ) {
		return;
	} elseif ( ! $customizer && empty( $header_data ) ) {
		return;
	}

	if ( $location === 'inside' ) {
		$settings['vue_args'] =  [
			'condition' => ' && !$parent.valueIsEnabled($parent.customizerFeedData.settings.headeroutside)'
		];
	} else {
		$settings['vue_args'] = [
			'condition' => ' && $parent.valueIsEnabled($parent.customizerFeedData.settings.headeroutside)'
		];
	}
	include sbi_get_feed_template_part( 'header', $settings );
}
