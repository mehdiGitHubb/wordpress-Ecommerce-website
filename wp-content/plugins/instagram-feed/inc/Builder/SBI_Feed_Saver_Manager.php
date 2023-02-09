<?php
/**
 * Instagram Feed Saver Manager
 *
 * @since 6.0
 */

namespace InstagramFeed\Builder;
class SBI_Feed_Saver_Manager {

	/**
	 * AJAX hooks for various feed data related functionality
	 *
	 * @since 6.0
	 */
	public static function hooks() {
		add_action( 'wp_ajax_sbi_feed_saver_manager_builder_update', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'builder_update' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_get_feed_settings', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'get_feed_settings' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_get_feed_list_page', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'get_feed_list_page' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_get_locations_page', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'get_locations_page' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_delete_feeds', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'delete_feed' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_duplicate_feed', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'duplicate_feed' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_clear_single_feed_cache', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'clear_single_feed_cache' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_importer', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'importer' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_fly_preview', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'feed_customizer_fly_preview' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_retrieve_comments', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'retrieve_comments' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_clear_comments_cache', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'clear_comments_cache' ) );
		add_action( 'wp_ajax_sbi_feed_saver_manager_delete_source', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'delete_source' ) );
		add_action( 'wp_ajax_sbi_update_personal_account', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'sbi_update_personal_account' ) );

		//Detect Leaving the Page
		add_action( 'wp_ajax_sbi_feed_saver_manager_recache_feed', array( 'InstagramFeed\Builder\SBI_Feed_Saver_Manager', 'recache_feed' ) );

	}

	/**
	 * Used in an AJAX call to update settings for a particular feed.
	 * Can also be used to create a new feed if no feed_id sent in
	 * $_POST data.
	 *
	 * @since 6.0
	 */
	public static function builder_update() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		$settings_data = $_POST;

		$feed_id = false;
		$is_new_feed = isset($settings_data['new_insert'] ) ? true : false;
		if ( ! empty( $settings_data['feed_id'] ) ) {
			$feed_id = sanitize_key( $settings_data['feed_id'] );
			unset( $settings_data['feed_id'] );
		} elseif ( isset( $settings_data['feed_id'] ) ) {
			unset( $settings_data['feed_id'] );
		}
		unset( $settings_data['action'] );

		if ( ! isset( $settings_data['feed_name'] ) ) {
			$settings_data['feed_name'] = '';
		}

		$update_feed = isset( $settings_data['update_feed'] ) ? true : false;
		unset( $settings_data['update_feed'] );

		//Check if New
		if ( isset( $settings_data['new_insert'] ) && $settings_data['new_insert'] == 'true' && isset($settings_data['sourcename'])) {
			$settings_data['order'] = sanitize_text_field( $_POST['order'] );
			if ( $_POST['type'] === 'hashtag' ) {
				$settings_data['feed_name'] = sanitize_text_field( implode( ' ', $_POST['hashtag'] ) );
			} else {
				$settings_data['feed_name'] = SBI_Db::feeds_query_name($settings_data['sourcename']);
			}
		}
		unset( $settings_data['new_insert'] );
		unset( $settings_data['sourcename'] );
		if ( isset( $settings_data['customizer'] ) ) {
			unset( $settings_data['customizer'] );
		}
		$feed_name = '';
		if ( $update_feed ) {
			$settings_data['settings']['sources'] = $_POST['sources'];
			$feed_name = $settings_data['feed_name'];
			$settings_data = $settings_data['settings'];
			$settings_data['shoppablelist'] = isset( $_POST['shoppablelist'] ) ? json_encode($_POST['shoppablelist'])  : [];
			$settings_data['moderationlist'] = isset( $_POST['moderationlist'] ) ? json_encode($_POST['moderationlist'])  : [];
		}

		$source_ids = $_POST['sources'];
		$args = array( 'id' => $source_ids );
		$source_query = SBI_Db::source_query( $args );

		$sources = array();
		if ( ! empty( $source_query ) ) {
			foreach ( $source_query as $source ) {
				$sources[] = $source['account_id'];
			}
		}

		$settings_data['sources'] = $sources;
		if ( $feed_id !== 'legacy' ) {
			unset( $settings_data['sources'] );
			$settings_data['id'] = implode(',', $sources );
		} else {
			if ( isset( $settings_data['feed'] ) ) {
				unset( $settings_data['feed'] );
			}
			$settings_data['id'] = implode(',', $source_ids );
			\SB_Instagram_Cache::clear_legacy();
		}
		$feed_saver = new SBI_Feed_Saver( $feed_id );
		$feed_saver->set_feed_name( $feed_name );
		$settings_data = self::filter_save_data( $settings_data );
		$feed_saver->set_data( $settings_data );

		$return = array(
			'success' => false,
			'feed_id' => false
		);

		if ( $feed_saver->update_or_insert() ) {
			$return = array(
				'success' => true,
				'feed_id' => $feed_saver->get_feed_id()
			);
			if($is_new_feed){
				echo wp_json_encode( $return );
				wp_die();
			} else{
				$feed_cache = new \SB_Instagram_Cache( $feed_id );
				$feed_cache->clear( 'all' );
				$feed_cache->clear( 'posts' );
				echo wp_json_encode( $return );
				wp_die();
			}
		}
	}

	/**

	 * Retrieve comments AJAX call
	 *
	 * @since 6.0
	 */
	public static function retrieve_comments() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Clear comments cache AJAX call
	 *
	 * @since 6.0
	*/
	public static function clear_comments_cache() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		$manager = new \SB_Instagram_Data_Manager();
		$manager->delete_comments_data();
		echo "success";
		wp_die();
	}

	/**
	 * Used in an AJAX call to delete feeds from the Database
	 * $_POST data.
	 *
	 * @since 6.0
	 */
	public static function delete_feed() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['feeds_ids'] ) && is_array( $_POST['feeds_ids'] )) {
			SBI_Db::delete_feeds_query( $_POST['feeds_ids'] );
		}
	}


	/**
	 * Used in an AJAX call to delete Soureces from the Database
	 * $_POST data.
	 *
	 * @since 6.0
	 */
	public static function delete_source() {
		check_ajax_referer( 'sbi_admin_nonce' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['source_id'] ) ) {
			if ( isset( $_POST['username'] ) && ! empty( $_POST['username'] ) ) {
				$username = sanitize_text_field( $_POST['username'] );
				\SB_Instagram_Connected_Account::delete_local_avatar( $username );
			}
			$source_id = absint( $_POST['source_id'] );
			SBI_Db::delete_source_query( $source_id );
		}
	}

	public static function recache_feed() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		$feed_id = sanitize_key( $_POST['feedID'] );
		$feed_cache = new \SB_Instagram_Cache( $feed_id );
		$feed_cache->clear( 'all' );
		$feed_cache->clear( 'posts' );
	}

	/**
	 * Used in an AJAX call to delete a feed cache from the Database
	 * $_POST data.
	 *
	 * @since 6.0
	 */
	public static function clear_single_feed_cache() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		$feed_id = sanitize_key( $_POST['feedID'] );

		if ( $feed_id === 'legacy' ) {
			\SB_Instagram_Cache::clear_legacy( true );
		} else {
			$feed_cache = new \SB_Instagram_Cache( $feed_id );
			$feed_cache->clear( 'all' );
			$feed_cache->clear( 'posts' );
		}

		SBI_Feed_Saver_Manager::feed_customizer_fly_preview();
		wp_die();

	}

	/**
	 * Used in an AJAX call to duplicate a Feed
	 * $_POST data.
	 *
	 * @since 6.0
	 */
	public static function duplicate_feed() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['feed_id'] ) ) {
			SBI_Db::duplicate_feed_query( sanitize_key( $_POST['feed_id'] ) );
		}
	}


	/**
	 * Import a feed from JSON data
	 *
	 * @since 6.0
	 */
	public static function importer() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['feed_json'] ) && strpos( $_POST['feed_json'], '{' ) === 0 ) {
			echo json_encode( SBI_Feed_Saver_Manager::import_feed( stripslashes( $_POST['feed_json'] ) ) );
		} else {
			echo json_encode(  array( 'success' => false, 'message' => __( 'Invalid JSON. Must have brackets "{}"', 'instagram-feed' ) ) );
		}
		wp_die();
	}


	/**
	 * Used To check if it's customizer Screens
	 * Returns Feed info or false!
	 *
	 * @param bool $include_comments
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public static function maybe_feed_customizer_data( $include_comments = false ) {
		if ( isset( $_GET['feed_id'] ) ){
			$feed_id  = sanitize_key( $_GET['feed_id'] );
			$feed_saver = new SBI_Feed_Saver( $feed_id );
			$settings = $feed_saver->get_feed_settings();
			$feed_db_data = $feed_saver->get_feed_db_data();

			if ( $settings !== false ){
				$return = array(
					'feed_info' => $feed_db_data,
					'headerData' => $feed_db_data,
					'settings' => $settings,
					'posts' => array()
				);
				if ( intval( $feed_id ) > 0 ) {
					$instagram_feed_settings = new \SB_Instagram_Settings( array( 'feed' => $feed_id, 'customizer' => true ), sbi_defaults() );
				} else {
					$instagram_feed_settings = new \SB_Instagram_Settings( array(), sbi_get_database_settings() );
				}

				$instagram_feed_settings->set_feed_type_and_terms();
				$instagram_feed_settings->set_transient_name();
				$transient_name = $instagram_feed_settings->get_transient_name();
				$settings = $instagram_feed_settings->get_settings();

				$feed_type_and_terms = $instagram_feed_settings->get_feed_type_and_terms();
				if ( $feed_id === 'legacy' ) {
					$transient_name = 'sbi_*legacy';
				}

				$instagram_feed = new \SB_Instagram_Feed( $transient_name );

				$instagram_feed->set_cache( $instagram_feed_settings->get_cache_time_in_seconds(), $settings );

				if ( $instagram_feed->regular_cache_exists() ) {
					$instagram_feed->set_post_data_from_cache();

					if ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
						while ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
							$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
						}

						$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
					}
				} else {
					while ( $instagram_feed->need_posts( $settings['num'] ) && $instagram_feed->can_get_more_posts() ) {
						$instagram_feed->add_remote_posts( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
					}

					if ( ! $instagram_feed->should_use_backup() ) {
						$instagram_feed->cache_feed_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );
					} elseif ( $instagram_feed->should_cache_error() ) {
						$cache_time = min( $instagram_feed_settings->get_cache_time_in_seconds(), 15 * 60 );
						$instagram_feed->cache_feed_data( $cache_time, false );
					}
				}
				$return['posts'] = $instagram_feed->get_post_data();


				$instagram_feed->set_remote_header_data( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
				$header_data = $instagram_feed->get_header_data();
				if ( sbi_is_pro_version() && $settings['stories'] && ! empty( $header_data ) ) {
					$instagram_feed->set_remote_stories_data( $settings, $feed_type_and_terms, $instagram_feed_settings->get_connected_accounts_in_feed() );
				}
				$instagram_feed->cache_header_data( $instagram_feed_settings->get_cache_time_in_seconds(), $settings['backup_cache_enabled'] );


				if ( ! empty( $header_data ) && \SB_Instagram_Connected_Account::local_avatar_exists( $header_data['username'] ) ) {
					$header_data['local_avatar_url'] = \SB_Instagram_Connected_Account::get_local_avatar_url( $header_data['username'] );
					$header_data['local_avatar'] = \SB_Instagram_Connected_Account::get_local_avatar_url( $header_data['username'] );
				} else {
					$header_data['local_avatar'] = false;
				}
				$header_data['local_avatar'] = false;

				$return['header'] = $header_data;
				$return['headerData'] = $header_data;

				return $return;

			}
		}
		return false;
	}


	/**
	 * Used to retrieve Feed Posts for preview screen
	 * Returns Feed info or false!
	 *
	 *
	 *
	 * @since 6.0
	 */
	public static function feed_customizer_fly_preview() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if( isset( $_POST['feedID'] ) &&  isset( $_POST['previewSettings'] ) ){
			$feed_id = sanitize_key( $_POST['feedID'] );
			$preview_settings = $_POST['previewSettings'];
			$feed_name = sanitize_text_field( wp_unslash( $_POST['feedName'] ) );

			if(isset($_POST['moderationShoppableMode']) && $_POST['moderationShoppableMode'] == true){
				$preview_settings['num'] = 10;
				$preview_settings['layout'] = 'grid';
				$preview_settings['cols'] = 4;
				$preview_settings['offset'] = intval($_POST['offset']) * 10;

				$preview_settings['enablemoderationmode'] = false;
				$preview_settings['shoppablelist'] = isset( $preview_settings['shoppablelist'] ) ? json_encode($preview_settings['shoppablelist'])  : [];
				$preview_settings['moderationlist'] = isset( $preview_settings['moderationlist'] ) ? json_encode($preview_settings['moderationlist'])  : [];

			}


			if ( $feed_id === 'legacy' ) {
				\SB_Instagram_Cache::clear_legacy( true );
			} else {
				$feed_cache = new \SB_Instagram_Cache( $feed_id );
				$feed_cache->clear( 'all' );
				$feed_cache->clear( 'posts' );
			}

			$feed_saver = new SBI_Feed_Saver( $feed_id );
			$feed_saver->set_feed_name( $feed_name );
			$feed_saver->set_data( $preview_settings );

			$atts = SBI_Feed_Builder::add_customizer_att( [ 'feed' => $feed_id, 'customizer' => true ] );
			if ( ! empty( $preview_settings['id'] ) ) {
				$preview_settings['id'] = implode( ',', $preview_settings['id'] );
				if ( isset( $preview_settings['user'] ) ) {
					unset( $preview_settings['user'] );
				}
			}
			$return['feed_html'] = display_instagram( $atts, $preview_settings );
			echo $return['feed_html'];

		}
		wp_die();
	}

	/**
	 * Used in AJAX call to return settings for an existing feed.
	 *
	 * @since 6.0
	 */
	public static function get_feed_settings() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		$feed_id = ! empty( $_POST['feed_id'] ) ? sanitize_key( $_POST['feed_id'] ) : false;

		if ( ! $feed_id ) {
			wp_die( 'no feed id' );
		}

		$feed_saver = new SBI_Feed_Saver( $feed_id );
		$settings = $feed_saver->get_feed_settings();

		$return = array(
			'settings' => $settings,
			'feed_html' => ''
		);

		if ( isset( $_POST['include_post_set'] ) &&
			! empty( $_POST['include_post_set'] ) ) {
			$atts = SBI_Feed_Builder::add_customizer_att( [ 'feed' => $return['feed_id'] ] );
			$return['feed_html'] = display_instagram( $atts );
		}

		echo sbi_json_encode( $return );
		wp_die();
	}

	/**
	 * Get a list of feeds with a limit and offset like a page
	 *
	 * @since 6.0
	 */
	public static function get_feed_list_page() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}
		$args = array( 'page' => (int)$_POST['page'] );
		$feeds_data = SBI_Feed_Builder::get_feed_list($args);

		echo sbi_json_encode( $feeds_data );

		wp_die();
	}

	/**
	 * Get a list of locations with a limit and offset like a page
	 *
	 * @since 6.0
	 */
	public static function get_locations_page() {
		check_ajax_referer( 'sbi-admin' , 'nonce');

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}
		$args = array( 'page' => (int)$_POST['page'] );

		if ( ! empty( $_POST['is_legacy'] ) ) {
			$args['feed_id'] = sanitize_key( $_POST['feed_id'] );
		} else {
			$args['feed_id'] = '*' . (int)$_POST['feed_id'];
		}
		$feeds_data = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

		if ( count( $feeds_data ) < SBI_Db::RESULTS_PER_PAGE ) {
			$args['html_location'] = array( 'footer', 'sidebar', 'header' );
			$args['group_by'] = 'html_location';
			$args['page'] = 1;
			$non_content_data = \SB_Instagram_Feed_Locator::instagram_feed_locator_query( $args );

			$feeds_data = array_merge( $feeds_data, $non_content_data );
		}

		echo sbi_json_encode( $feeds_data );

		wp_die();
	}

	/**
	 * Return a single JSON string for importing a feed
	 *
	 * @param int $feed_id
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public static function get_export_json( $feed_id ) {
		$feed_saver = new SBI_Feed_Saver( $feed_id );
		$settings = $feed_saver->get_feed_settings();

		return sbi_json_encode( $settings );
	}

	/**
	 * All export strings for all feeds on the first 'page'
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_all_export_json() {
		$args = array( 'page' => 1 );

		$feeds_data = SBI_Db::feeds_query( $args );

		$return = array();
		foreach ( $feeds_data as $single_feed ) {
			$return[ $single_feed['id'] ] = SBI_Feed_Saver_Manager::get_export_json( $single_feed['id'] );
		}

		return $return;
	}

	/**
	 * Use a JSON string to import a feed with settings and sources. The return
	 * is whether or not the import was successful
	 *
	 * @param string $json
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function import_feed( $json ) {
		$settings_data = json_decode( $json, true );

		$return = array(
			'success' => false,
			'message' => ''
		);

		if ( empty( $settings_data['sources'] ) ) {
			$return['message'] = __( 'No feed source is included. Cannot upload feed.', 'instagram-feed' );
			return $return;
		}

		$sources = $settings_data['sources'];

		unset( $settings_data['sources'] );

		$settings_source = array();
		foreach ( $sources as $source ) {

			if ( isset( $source['user_id'] ) ) {
				$source['account_id'] = $source['user_id'];
				$source['id'] = $source['user_id'];
			}
			if ( isset( $source['account_id'] ) ) {
				if ( isset( $source['record_id'] ) ) {
					unset( $source['record_id'] );
				}

				$settings_source[] = $source['account_id'];

				// don't update or insert the access token if there is an API error
				if ( ! isset( $header_details->error ) ) {
					SBI_Source::update_or_insert( $source );
				}
			}

		}
		$settings_data['sources'] = $settings_source;


		/* unset pro features if exists */
		$settings_data = self::filter_save_data( $settings_data );
		$feed_saver = new SBI_Feed_Saver( false );
		$feed_saver->set_data( $settings_data );

		if ( $feed_saver->update_or_insert() ) {
			$return = array(
				'success' => true,
				'feed_id' => $feed_saver->get_feed_id()
			);

			return $return;
		} else {
			$return['message'] = __( 'Could not import feed. Please try again', 'instagram-feed' );
		}
		return $return;
	}

	/**
	 * Determines what table and sanitization should be used
	 * when handling feed setting data.
	 *
	 * TODO: Add settings that need something other than sanitize_text_field
	 *
	 * @param string $key
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_data_type( $key ) {
		switch ( $key ) {
			case 'sources' :
			$return = array(
				'table' => 'feed_settings',
				'sanitization' => 'sanitize_text_field'
			);
			break;
			case 'feed_title' :
			$return = array(
				'table' => 'feeds',
				'sanitization' => 'sanitize_text_field'
			);
			break;
			case 'feed_name' :
			$return = array(
				'table' => 'feeds',
				'sanitization' => 'sanitize_text_field'
			);
			break;
			case 'status' :
			$return = array(
				'table' => 'feeds',
				'sanitization' => 'sanitize_text_field'
			);
			break;
			case 'author' :
			$return = array(
				'table' => 'feeds',
				'sanitization' => 'int'
			);
			break;
			default:
			$return = array(
				'table' => 'feed_settings',
				'sanitization' => 'sanitize_text_field'
			);
			break;
		}

		return $return;
	}

	/**
	 * Uses the appropriate sanitization function and returns the result
	 * for a value
	 *
	 * @param string $type
	 * @param int|string $value
	 *
	 * @return int|string
	 *
	 * @since 6.0
	 */
	public static function sanitize( $type, $value ) {
		switch ( $type ) {
			case 'int' :
				$return = intval( $value );
			break;
			case 'boolean' :
				$return = self::cast_boolean($value);
			break;
			default:
				$return = sanitize_text_field( $value );
			break;
		}

		return $return;
	}

	/**
	 * Check if boolean
	 * for a value
	 *
	 * @param string $type
	 * @param int|string $value
	 *
	 * @return int|string
	 *
	 * @since 6.0
	 */
	public static function is_boolean( $value ) {
		return ( $value === 'true' || $value === 'false' || is_bool($value)  ) ? true : false;
	}

	public static function cast_boolean( $value ) {
		if($value === 'true' || $value === true || $value === 'on')
			return true;
		return false;
	}

	public static function filter_save_data( $save_data ) {
		if ( sbi_is_pro_version() ) {
			return $save_data;
		}
		$unsets = array(
			'hoverdisplay',
			'igtvposts',
			'lightboxcomments',
			'numcomments',
			'stories',
			'videosposts',
			'videotypes',
			'disablelightbox'
		);

		foreach ( $unsets as $unset ) {
			if ( isset( $save_data[ $unset ] ) ) {
				unset( $save_data[ $unset ] );
			}
		}

		return $save_data;
	}

	/**
	 * Update Personal Account Info
	 * Setting Avatar + Bio
	 *
	 * @return json
	 *
	 * @since 6.0.8
	 */
	public static function sbi_update_personal_account(){

		check_ajax_referer( 'sbi-admin' , 'nonce');
		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		if( isset( $_FILES['avatar']['tmp_name'] ) && isset( $_POST['username'] ) ) {
			$account_avatar = sanitize_text_field( $_FILES['avatar']['tmp_name'] );
			$username = sanitize_text_field( $_POST['username'] );
			$created = \SB_Instagram_Connected_Account::create_local_avatar( $username, $account_avatar );
			\SB_Instagram_Connected_Account::update_local_avatar_status( $username, $created );
		}

		if( isset( $_POST['bio'] ) && isset( $_POST['id'] ) ){
			$account_bio = sanitize_text_field( stripslashes( $_POST['bio'] ) );
			$id     = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			SBI_Source::update_personal_account_bio( $id, $account_bio );
		}
		$response = array(
			'success' => true,
			'sourcesList' => SBI_Feed_Builder::get_source_list()
		);
		echo sbi_json_encode( $response );
		wp_die();
	}

}
