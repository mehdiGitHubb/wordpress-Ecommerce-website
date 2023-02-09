<?php
/**
 * Class SB_Instagram_Posts_Manager
 *
 * Set as a global object to record and report errors as well
 * as control aspects of image resizing
 *
 * @since 2.0/4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Posts_Manager {

	/**
	 * @var mixed|void
	 */
	var $sbi_options;

	/**
	 * @var int
	 */
	var $limit;

	/**
	 * @var array
	 */
	var $errors;

	/**
	 * @var array
	 */
	var $display_error;

	/**
	 * @var bool
	 */
	var $resizing_tables_exist;

	/**
	 * SB_Instagram_Posts_Manager constructor.
	 */
	public function __construct() {
		$this->sbi_options = get_option( 'sb_instagram_settings' );
		$this->errors      = get_option( 'sb_instagram_errors', array() );
		if ( ! isset( $this->errors['connection'] ) ) {
			$this->errors = array(
				'connection'      => array(),
				'hashtag'         => array(),
				'resizing'        => array(),
				'database_create' => array(),
				'upload_dir'      => array(),
				'accounts'        => array(),
				'error_log'       => array(),
				'action_log'      => array(),
				'revoked'         => array(),
			);
		}

		$this->display_error = array();
		if ( $this->does_resizing_tables_exist() ) {
			$this->resizing_tables_exist = true;
		}

		require_once( trailingslashit( dirname( __FILE__ ) ) . '/Platform_Data.php' );
		$platform_data_manager = new \InstagramFeed\Platform_Data();
		$platform_data_manager->register_hooks();
	}

	/**
	 * Stores information about an encountered error related to a connected account
	 *
	 * @param $connected_account array
	 * @param $error_type string
	 * @param $details mixed/array/string
	 *
	 * @since 2.7/5.10
	 */
	public function add_connected_account_error( $connected_account, $error_type, $details ) {
		$account_id = $connected_account['user_id'];
		$this->errors['accounts'][ $account_id ][ $error_type ] = $details;

		if ( $error_type === 'api' ) {
			$this->errors['accounts'][ $account_id ][ $error_type ]['clear_time'] = time() + 60 * 3;
		}

		if ( isset( $details['error']['code'] )
		     && (int) $details['error']['code'] === 18 ) {
			$this->errors['accounts'][ $account_id ][ $error_type ]['clear_time'] = time() + 60 * 15;
		}
		\InstagramFeed\Builder\SBI_Source::add_error( $account_id, $details );
	}

	/**
	 * Stores errors so they can be retrieved and explained to users
	 * in messages as well as temporarily disable certain features
	 *
	 * @param string $type
	 * @param array $details
	 * @param mixed/bool/array $connected_account_term
	 *
	 * @since 2.7/5.10
	 */
	public function add_error( $type, $details, $connected_account_term = false ) {
		$connected_account = false;

		$log_item = date( 'm-d H:i:s' ) . ' - ';
		if ( $connected_account_term ) {
			if ( ! is_array( $connected_account_term ) ) {
				$connected_account = SB_Instagram_Connected_Account::lookup( $connected_account_term );
			} else {
				$connected_account = $connected_account_term;
			}

			$this->add_connected_account_error( $connected_account, $type, $details );
		}

		// is it connection? or what type?
		if ( $type === 'api'
		     || $type === 'wp_remote_get' ) {
			$connection_details = array(
				'error_id' => '',
			);

			$connection_details['critical'] = false;
			if ( isset( $details['error']['code'] ) ) {
				$connection_details['error_id'] = $details['error']['code'];

				if ( $this->is_critical_error( $details ) ) {
					$connection_details['critical'] = true;
				}

				if ( $this->is_app_permission_related( $details ) ) {
					if ( ! in_array( $connected_account['user_id'], $this->errors['revoked'], true ) ) {
						$this->errors['revoked'][] = $connected_account['user_id'];
					}

					/**
					 * Fires when an app permission related error is encountered
					 *
					 * @param array $connected_account The connected account that encountered the error
					 *
					 * @since 6.0.6
					 */
					do_action( 'sbi_app_permission_revoked', $connected_account );
				}
			} elseif ( isset( $details['response'] ) && is_wp_error( $details['response'] ) ) {
				foreach ( $details['response']->errors as $key => $item ) {
					$connection_details['error_id'] = $key;
				}
				$connection_details['critical'] = true;
			}
			if ( get_the_ID() !== 0 ) {
				$connection_details['post_id'] = get_the_ID();
			}
			$connection_details['error_message'] = $this->generate_error_message( $details, $connected_account );
			$log_item                           .= $connection_details['error_message']['admin_only'];
			$this->maybe_set_display_error( 'connection', $connection_details );
			$this->errors['connection'] = $connection_details;
		}

		if ( $type === 'hashtag' ) {
			$hashtag_details = array(
				'error_id' => '',
				'hashtag'  => isset( $details['hashtag'] ) ? $details['hashtag'] : '',
			);
			if ( isset( $details['error']['code'] ) ) {
				if ( (int) $details['error']['code'] === 24 ) {
					$hashtag_details['clear_time'] = time() + 60 * 5;
				}
			}

			if ( isset( $details['error']['code'] ) ) {
				$hashtag_details['error_id'] = $details['error']['code'];
			} elseif ( isset( $details['response'] ) && is_wp_error( $details['response'] ) ) {
				foreach ( $details['response']->errors as $key => $item ) {
					$hashtag_details['error_id'] = $key;
				}
			}
			if ( get_the_ID() !== 0 ) {
				$hashtag_details['post_id'] = get_the_ID();
			}
			$hashtag_details['error_message'] = $this->generate_error_message( $details, $connected_account );
			$log_item                        .= $hashtag_details['error_message']['admin_only'];
			$this->maybe_set_display_error( 'hashtag', $hashtag_details );

			$found = false;
			if ( isset( $details['hashtag'] ) ) {
				foreach ( $this->errors['hashtag'] as $hashtag_error_item ) {
					if ( isset( $hashtag_error_item['hashtag'] )
					     && strtolower( $hashtag_error_item['hashtag'] ) === strtolower( $details['hashtag'] )
					     && $hashtag_error_item['error_id'] === $details['error_id'] ) {
						$found = true;
					}
				}
			}

			if ( ! $found ) {
				$this->errors['hashtag'][] = $hashtag_details;
			}
		}

		if ( $type === 'image_editor'
		     || $type === 'storage' ) {

			$this->errors['resizing'] = $details;
			$log_item                .= $details;
		}

		if ( $type === 'database_create' ) {
			$this->errors['database_create'] = $details;
			$log_item                       .= $details;
		}

		if ( $type === 'upload_dir' ) {
			$this->errors['upload_dir'] = $details;
			$log_item                  .= $details;
		}

		if ( $type === 'unused_feed' ) {
			$this->errors['unused_feed'] = $details;
			$log_item                    .= $details;
		}

		if ( $type === 'platform_data_deleted' ) {
			$this->errors['platform_data_deleted'] = $details;
			$log_item                              .= $details;
		}

		$current_log = $this->errors['error_log'];
		if ( is_array( $current_log ) && count( $current_log ) >= 10 ) {
			reset( $current_log );
			unset( $current_log[ key( $current_log ) ] );
		}
		$current_log[]             = $log_item;
		$this->errors['error_log'] = $current_log;
		update_option( 'sb_instagram_errors', $this->errors, false );
	}

	/**
	 * Stores a time stamped string of information about
	 * actions that might lead to correcting an error
	 *
	 * @param string $log_item
	 *
	 * @since 2.7/5.10
	 */
	public function add_action_log( $log_item ) {
		$current_log = $this->errors['action_log'];

		if ( is_array( $current_log ) && count( $current_log ) >= 10 ) {
			reset( $current_log );
			unset( $current_log[ key( $current_log ) ] );
		}
		$current_log[] = date( 'm-d H:i:s' ) . ' - ' . $log_item;

		$this->errors['action_log'] = $current_log;
		update_option( 'sb_instagram_errors', $this->errors, false );
	}

	/**
	 * @return mixed
	 *
	 * @since 2.7/5.10
	 */
	public function get_error_log() {
		return $this->errors['error_log'];
	}

	/**
	 * @return mixed
	 *
	 * @since 2.7/5.10
	 */
	public function get_action_log() {
		return $this->errors['action_log'];
	}

	/**
	 * Certain API errors are considered critical and will trigger
	 * the various notifications to users to correct them.
	 *
	 * @param $details
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function is_critical_error( $details ) {
		$error_code = (int) $details['error']['code'];

		$critical_codes = array(
			803, // ID doesn't exist
			100, // access token or permissions
			190, // access token or permissions
			10, // app permissions or scopes
		);

		return in_array( $error_code, $critical_codes, true );
	}

	/**
	 * Should clear platform data
	 *
	 * @param $details
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function is_app_permission_related( $details ) {
		$error_code    = (int) $details['error']['code'];
		$error_subcode = isset( $details['error']['error_subcode'] ) ? (int) $details['error']['error_subcode'] : 0;

		$critical_codes = array(
			190, // access token or permissions
		);

		$critical_subcodes = array(
			458, // access token or permissions
		);

		if ( in_array( $error_code, $critical_codes, true ) ) {
			if ( strpos( $details['error']['message'], 'user has not authorized application' ) !== false ) {
				return true;
			}
			return in_array( $error_subcode, $critical_subcodes, true );
		}

		return false;
	}

	/**
	 * Creates an array of information for easy display of API errors
	 *
	 * @param $response
	 * @param array $connected_account
	 *
	 * @return array
	 *
	 * @since 2.7/5.10
	 */
	public function generate_error_message( $response, $connected_account = array( 'username' => '' ) ) {

		$error_message_return = array(
			'error_message'       => '',
			'admin_only'          => '',
			'frontend_directions' => '',
			'backend_directions'  => '',
			'time'                => time(),
		);
		$hash                 = isset( $response['error']['code'] ) ? '#' . (int) $response['error']['code'] : '';

		if ( isset( $response['response'] ) && is_wp_error( $response['response'] ) ) {
			$error_message_return['error_message'] = __( 'HTTP Error. Unable to connect to the Instagram API.', 'instagram-feed' ) . ' ' . __( 'Feed will not update.', 'instagram-feed' );
			$error_message_return['admin_only']    = sprintf( __( 'Error connecting to %s.', 'instagram-feed' ), $response['url'] );

			$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';

			if ( isset( $response['response'] ) && isset( $response['response']->errors ) ) {
				$num = count( $response['response']->errors );
				$i   = 1;
				foreach ( $response['response']->errors as $key => $item ) {
					$error_message_return['admin_only'] .= ' ' . $key . ' - ' . $item[0];
					if ( $i < $num ) {
						$error_message_return['admin_only'] .= ',';
					}
					$num++;
				}
			}

			return $error_message_return;
		}
		$hash = '#' . (int) $response['error']['code'];
		$link = admin_url( 'admin.php?page=sbi-settings' );

		if ( isset( $response['error']['message'] ) ) {
			if ( (int) $response['error']['code'] === 100 ) {
				$error_message_return['error_message']       = __( 'Error: Access Token is not valid or has expired.', 'instagram-feed' ) . ' ' . __( 'Feed will not update.', 'instagram-feed' );
				$error_message_return['admin_only']          = sprintf( __( 'API error %s:', 'instagram-feed' ), $response['error']['code'] ) . ' ' . $response['error']['message'];
				$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';
			} elseif ( (int) $response['error']['code'] === 18 ) {
				$error_message_return['error_message']       = __( 'Error: Hashtag limit of 30 unique hashtags per week has been reached.', 'instagram-feed' );
				$error_message_return['admin_only']          = __( 'If you need to display more than 30 hashtag feeds on your site, consider connecting an additional business account from a separate Instagram Identity and Facebook page. Connecting an additional Instagram business account from the same Facebook page will not raise the limit.', 'instagram-feed' );
				$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';
			} elseif ( (int) $response['error']['code'] === 10 ) {
				$error_message_return['error_message']       = sprintf( __( 'Error: Connected account for the user %s does not have permission to use this feed type.', 'instagram-feed' ), $connected_account['username'] );
				$error_message_return['admin_only']          = __( 'Try using the big blue button on the "Configure" tab to reconnect the account and update its permissions.', 'instagram-feed' );
				$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';
			} elseif ( (int) $response['error']['code'] === 24 ) {
				$error_message_return['error_message']       = __( 'Error: Cannot retrieve posts for this hashtag.', 'instagram-feed' );
				$error_message_return['admin_only']          = $response['error']['error_user_msg'];
				$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';
			} else {
				$error_message_return['error_message']       = __( 'There has been a problem with your Instagram Feed.', 'instagram-feed' );
				$error_message_return['admin_only']          = sprintf( __( 'API error %s:', 'instagram-feed' ), $response['error']['code'] ) . ' ' . $response['error']['message'];
				$error_message_return['frontend_directions'] = '<a href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>';
			}
		} else {
			$error_message_return['error_message'] = __( 'An unknown error has occurred.', 'instagram-feed' );
			$error_message_return['admin_only']    = json_encode( $response );
		}
		return $error_message_return;
	}

	/**
	 * Display errors are saved with the feed cache so they will still be displayed
	 * on the frontend
	 *
	 * @param string $type
	 * @param array $error
	 *
	 * @since 2.7/5.10
	 */
	public function maybe_set_display_error( $type, $error ) {
		if ( $type === 'connection' ) {
			if ( empty( $this->display_error['connection'] ) ) {
				$this->display_error['connection'] = $error;
			}
		} elseif ( $type === 'configuration' ) {
			if ( empty( $this->display_error['configuration'] ) ) {
				$this->display_error['configuration'] = $error;
			}
		} elseif ( $type === 'hashtag' ) {
			$this->display_error['hashtag'][] = $error;
		} elseif ( $type === 'hashtag_limit' ) {
			if ( empty( $this->display_error['connection'] ) ) {
				$this->display_error['hashtag_limit'] = $error;
			}
		}

	}

	/**
	 * @param string $type
	 *
	 * @since 2.7/5.10
	 */
	public function maybe_remove_display_error( $type ) {
		if ( isset( $this->display_error[ $type ] ) ) {
			unset( $this->display_error[ $type ] );
		}
	}

	/**
	 * The plugin has a limit on how many post records can be stored and
	 * images resized to avoid overloading servers. This function deletes the post that
	 * has the longest time passed since it was retrieved.
	 *
	 * @since 2.0/4.0
	 */
	public function delete_least_used_image() {
		global $wpdb;
		$table_name             = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS );

		$max = isset( $this->limit ) && $this->limit > 1 ? $this->limit : 1;

		$oldest_posts = $wpdb->get_results( "SELECT id, media_id FROM $table_name ORDER BY last_requested ASC LIMIT $max", ARRAY_A );

		$upload        = wp_upload_dir();
		$file_suffixes = array( 'thumb', 'low', 'full' );

		foreach ( $oldest_posts as $post ) {

			foreach ( $file_suffixes as $file_suffix ) {
				$file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $post['media_id'] . $file_suffix . '.jpg';
				if ( is_file( $file_name ) ) {
					unlink( $file_name );
				}
			}

			$wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $post['id'] ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $feeds_posts_table_name WHERE record_id = %d", $post['id'] ) );
		}

	}

	/**
	 * Calculates how many records are in the database and whether or not it exceeds the limit
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function max_total_records_reached() {
		global $wpdb;
		$table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;

		$num_records = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		if ( ! isset( $this->limit ) && (int) $num_records > SBI_MAX_RECORDS ) {
			$this->limit = (int) $num_records - SBI_MAX_RECORDS;
		}

		return ( (int) $num_records > SBI_MAX_RECORDS );
	}

	/**
	 * The plugin caps how many new images are created in a 15 minute window to
	 * avoid overloading servers
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function max_resizing_per_time_period_reached() {
		global $wpdb;
		$table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;

		$fifteen_minutes_ago = date( 'Y-m-d H:i:s', time() - 15 * 60 );

		$num_new_records = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE created_on > '$fifteen_minutes_ago'" );

		return ( (int) $num_new_records > 100 );
	}

	/**
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function image_resizing_disabled( $data = false ) {
		$options          = sbi_get_database_settings();
		$disable_resizing = isset( $options['sb_instagram_disable_resize'] ) ? $options['sb_instagram_disable_resize'] === 'on' || $options['sb_instagram_disable_resize'] === true : false;
		$disable_resizing = apply_filters( 'sbi_image_resizing_disabled', $disable_resizing, $data );

		if ( ! $disable_resizing ) {
			$disable_resizing = isset( $this->resizing_tables_exist ) ? ! $this->resizing_tables_exist : ! $this->does_resizing_tables_exist();
		}

		return $disable_resizing;
	}

	/**
	 * Used to skip image resizing if the tables were never successfully
	 * created
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function does_resizing_tables_exist() {
		global $wpdb;

		$table_name   = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS );
		$resizing_key = 'sbi_resizing_exists';

		$sbi_resizing_cache = wp_cache_get( $resizing_key );

		if ( false === $sbi_resizing_cache ) {

			if ( $wpdb->get_var( "show tables like '$table_name'" ) == $table_name ) {
				wp_cache_set( $resizing_key, true );
			} else {
				wp_cache_set( $resizing_key, false );
			}
		}

		return $sbi_resizing_cache;

	}

	/**
	 * Resets the custom tables and deletes all image files
	 *
	 * @since 2.0/4.0
	 */
	public function delete_all_sbi_instagram_posts() {
		$upload = wp_upload_dir();

		global $wpdb;

		$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;

		$image_files = glob( trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . '*' ); // get all file names
		foreach ( $image_files as $file ) { // iterate files
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}

		$connected_accounts =  SB_Instagram_Connected_Account::get_all_connected_accounts();

		foreach ( $connected_accounts as $account_id => $data ) {
			if ( isset( $data['local_avatar'] ) ) {
				unset( $connected_accounts[ $account_id ]['local_avatar'] );
			}
		}

		$options['connected_accounts'] = $connected_accounts;

		update_option( 'sb_instagram_settings', $options );

		//Delete tables
		$wpdb->query( "DROP TABLE IF EXISTS $posts_table_name" );

		$feeds_posts_table_name = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS );
		$wpdb->query( "DROP TABLE IF EXISTS $feeds_posts_table_name" );

		$table_name = $wpdb->prefix . 'options';

		$wpdb->query(
			"
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_\$sbi\_%')
			        "
		);
		$wpdb->query(
			"
			        DELETE
			        FROM $table_name
			        WHERE `option_name` LIKE ('%\_transient\_timeout\_\$sbi\_%')
			        "
		);
		delete_option( 'sbi_hashtag_ids' );
		delete_option( 'sbi_local_avatars' );

		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = trailingslashit( $upload_dir ) . SBI_UPLOADS_NAME;
		if ( ! file_exists( $upload_dir ) ) {
			$created = wp_mkdir_p( $upload_dir );
			if ( $created ) {
				$this->remove_error( 'upload_dir' );
			} else {
				$this->add_error( 'upload_dir', __( 'There was an error creating the folder for storing resized images.', 'instagram-feed' ) . ' ' . $upload_dir );
			}
		} else {
			$this->remove_error( 'upload_dir' );
		}

		sbi_create_database_table();
	}


	/**
	 * Whether or not the one time request to the "top posts" endpoint for the hashtag
	 * was made
	 *
	 * @param string $hashtag
	 *
	 * @return bool
	 */
	public static function top_post_request_already_made( $hashtag ) {
		$list_of_top_hashtags = get_option( 'sbi_top_api_calls', array() );

		return in_array( $hashtag, $list_of_top_hashtags, true );
	}

	/**
	 * @param $hashtag
	 */
	public static function maybe_update_list_of_top_hashtags( $hashtag ) {
		$list_of_top_hashtags = get_option( 'sbi_top_api_calls', array() );

		if ( ! in_array( $hashtag, $list_of_top_hashtags, true ) ) {
			$list_of_top_hashtags[] = $hashtag;
			update_option( 'sbi_top_api_calls', $list_of_top_hashtags );
		}

	}

	/**
	 * @return array
	 *
	 * @since 2.0/4.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * @param string $type
	 * @param mixed/array/bool $connected_account
	 *
	 * @since 2.0/4.0
	 */
	public function remove_error( $type, $connected_account = false ) {
		$update = false;

		if ( ! empty( $this->errors[ $type ] ) ) {
			$this->errors[ $type ] = array();
			$this->add_action_log( 'Cleared ' . $type . ' error.' );
			$update = true;
		}

		if ( ! empty( $connected_account ) ) {
			if ( $this->remove_connected_account_error( $connected_account, $type, false ) ) {
				$this->add_action_log( 'Cleared connected account error ' . $connected_account['username'] . '.' );
			}

			if ( $type === 'connection' ) {
				if ( $this->remove_connected_account_error( $connected_account, 'api', false ) ) {
					$this->add_action_log( 'Cleared connected account error ' . $connected_account['username'] . '.' );
				}
			}

			if ( ! empty( $this->errors['revoked'] ) ) {
				if ( ( $key = array_search( $connected_account['user_id'], $this->errors['revoked'] ) ) !== false ) {
					unset( $this->errors['revoked'][ $key ] );
				}
			}

			$update = true;
		}

		if ( $update ) {
			update_option( 'sb_instagram_errors', $this->errors, false );
		}
	}

	/**
	 * @param array $clearing_account
	 * @param string $clearing_error_type
	 * @param bool $update
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function remove_connected_account_error( $clearing_account, $clearing_error_type = 'all', $update = true ) {
		$cleared = false;
		if ( isset( $this->errors['accounts'] ) ) {
			if ( ! isset( $clearing_account['user_id'] ) ) {
				return $cleared;
			}
			$clearing_account_id = $clearing_account['user_id'];

			foreach ( $this->errors['accounts'] as $account_id => $error_types ) {

				if ( ! SB_Instagram_Connected_Account::lookup( $account_id ) ) {
					unset( $this->errors['accounts'][ $account_id ] );
				} else {
					foreach ( $error_types as $error_type => $details ) {
						if ( (string) $account_id === (string) $clearing_account_id ) {
							if ( $error_type === $clearing_error_type || $clearing_error_type === 'all' ) {
								unset( $this->errors['accounts'][ $account_id ][ $error_type ] );
								$cleared = true;
							}
						} else {
							if ( isset( $details['username'] ) ) {
								if ( $details['username'] === $clearing_account['username'] ) {
									if ( $error_type === $clearing_error_type || $clearing_error_type === 'all' ) {
										unset( $this->errors['accounts'][ $account_id ][ $error_type ] );
										$cleared = true;
									}
								}
							}
							if ( isset( $this->errors['accounts'][ $account_id ] ) && isset( $details['access_token'] ) ) {
								if ( $details['access_token'] === $clearing_account['access_token'] ) {
									if ( $error_type === $clearing_error_type || $clearing_error_type === 'all' ) {
										unset( $this->errors['accounts'][ $account_id ][ $error_type ] );
										$cleared = true;
									}
								}
							}
						}
					}

					if ( empty( $this->errors['accounts'][ $account_id ] ) ) {
						unset( $this->errors['accounts'][ $account_id ] );
					}
				}
			}

			if ( $update ) {
				update_option( 'sb_instagram_errors', $this->errors, false );
			}
		}

		return $cleared;
	}

	/**
	 *
	 * @since 2.7/5.10
	 */
	public function remove_all_errors() {
		delete_option( 'sb_instagram_errors' );

		sb_instagram_cron_clear_cache();
	}

	/**
	 * When an account is used to make a successful connection
	 *
	 * @since 2.7/5.10
	 */
	public function reset_api_errors() {
		$this->errors['connection'] = array();
		$this->errors['accounts']   = array();

		update_option( 'sb_instagram_errors', $this->errors, false );
		sb_instagram_cron_clear_cache();
	}

	/**
	 * @deprecated
	 */
	public function update_error_page( $id ) {
		if ( $id !== 0 ) {
			update_option( 'sb_instagram_error_page', $id, false );
		}
	}

	/**
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function get_error_page() {
		if ( isset( $this->errors['connection']['post_id'] ) ) {
			return $this->errors['connection']['post_id'];
		}
		return false;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_frontend_errors( $instagram_feed = false ) {
		if ( $instagram_feed ) {
			$cached_errors = $instagram_feed->get_cached_feed_error();
			if ( ! empty( $cached_errors ) ) {
				return $cached_errors;
			}
		}

		$error_messages = array();
		if ( ! empty( $this->display_error['connection']['error_message'] ) ) {
			$error_messages[] = $this->display_error['connection']['error_message'];
		}
		if ( ! empty( $this->display_error['configuration'] ) ) {
			$error_messages[] = $this->display_error['configuration'];
		}
		if ( ! empty( $this->display_error['hashtag'][0] ) ) {
			$error_24         = array();
			$error_24_message = array();
			foreach ( $this->display_error['hashtag'] as $hashtag_error ) {
				if ( $hashtag_error['error_id'] === 24 ) {
					if ( ! in_array( $hashtag_error['hashtag'], $error_24, true ) ) {
						$error_24[] = $hashtag_error['hashtag'];
					}
					if ( empty( $error_24_message ) ) {
						$error_24_message               = $hashtag_error['error_message'];
						$error_24_message['admin_only'] = str_replace( $hashtag_error['hashtag'], '###', $error_24_message['admin_only'] );
					}
				} else {
					$error_messages[] = $hashtag_error['error_message'];
				}
			}
			if ( ! empty( $error_24_message ) ) {
				$hashtag_string                 = count( $error_24 ) > 1 ? implode( '", "', $error_24 ) : $error_24[0];
				$error_24_message['admin_only'] = str_replace( '###', $hashtag_string, $error_24_message['admin_only'] );
				$error_messages[]               = $error_24_message;
			}
		}
		if ( ! empty( $this->display_error['hashtag_limit'] ) ) {
			$response = array(
				'error' => $this->display_error['hashtag_limit']['error'],
			);

			$error_messages[] = $this->generate_error_message( $response );
		}

		return $error_messages;
	}

	/**
	 * @param $account
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function account_over_hashtag_limit( $account ) {
		if ( ! isset( $this->errors['accounts'][ $account['user_id'] ] ) ) {
			return false;
		}
		if ( isset( $this->errors['accounts'][ $account['user_id'] ]['hashtag_limit'] ) ) {
			if ( $this->errors['accounts'][ $account['user_id'] ]['hashtag_limit']['clear_time'] < time() ) {
				$this->remove_connected_account_error( $account, 'hashtag_limit', true );

				return false;
			} else {
				$this->maybe_set_display_error( 'hashtag_limit', $this->errors['accounts'][ $account['user_id'] ]['hashtag_limit'] );
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $connected_account
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function connected_account_has_error( $connected_account ) {
		if ( ! isset( $connected_account['user_id'] ) ) {
			return false;
		}
		if ( empty( $this->errors['accounts'] ) ) {
			return false;
		}

		$account_id = $connected_account['user_id'];

		if ( ! empty( $this->errors['accounts'][ $account_id ] ) ) {
			foreach ( $this->errors['accounts'][ $account_id ] as $error_key => $error_info ) {
				if ( strpos( $error_key, 'hashtag' ) === false ) {
					if ( $this->is_critical_error( $error_info ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Whether or not the hashtag is unvailable in the API for some reason
	 *
	 * @param $hashtag
	 *
	 * @return bool
	 *
	 * @since 2.7/5.10
	 */
	public function hashtag_has_error( $hashtag ) {
		if ( ! isset( $this->errors['hashtag'][0] ) ) {
			return false;
		}

		$to_save = array();
		$changed = false;
		$return  = false;

		foreach ( $this->errors['hashtag'] as $hashtag_error ) {
			if ( ! empty( $hashtag_error['hashtag'] )
			     && strtolower( $hashtag_error['hashtag'] ) === strtolower( $hashtag ) ) {
				if ( ! empty( $hashtag_error['clear_time'] ) ) {

					if ( $hashtag_error['clear_time'] < time() ) {
						$changed = true;
						$return  = false;
						// clear the error, return false
					} else {
						$to_save[] = $hashtag_error;
						$this->maybe_set_display_error( 'hashtag', $hashtag_error );
						$return = true;
					}
				}
			} else {
				if ( ! empty( $hashtag_error['clear_time'] ) ) {
					if ( $hashtag_error['clear_time'] < time() ) {
						$changed = true;
					} else {
						$to_save[] = $hashtag_error;
					}
				} else {
					$to_save[] = $hashtag_error;
				}
			}
		}

		if ( $changed ) {
			$this->errors['hashtag'] = $to_save;
			update_option( 'sb_instagram_errors', $this->errors, false );
		}

		return $return;
	}

	/**
	 * Only some errors should cause the user to be notified using email and site health
	 *
	 * @return string
	 */
	public function get_critical_errors() {
		if ( ! $this->are_critical_errors() ) {
			return '';
		}
		$accounts_revoked_string = '';
		$accounts_revoked = '';

		if ( $this->was_app_permission_related_error() ) {
			$accounts_revoked = $this->get_app_permission_related_error_ids();
			if ( count( $accounts_revoked ) > 1 ) {
				$accounts_revoked = implode( ', ', $accounts_revoked );
			} else {
				$accounts_revoked = $accounts_revoked[0];
			}
			$accounts_revoked_string = sprintf( __( 'Instagram Feed related data for the account(s) %s was removed due to permission for the Smash Balloon App on Facebook or Instagram being revoked. <br><br> To prevent the automated data deletion for the account, please reconnect your account within 7 days.', 'instagram-feed' ), $accounts_revoked );
		}

		if ( isset( $this->errors['connection']['critical'] ) ) {
			$errors        = $this->get_errors();
			$error_message = '';

			if ( $errors['connection']['error_id'] === 190 ) {
				$error_message .=  '<strong>' .  __( 'Action Required Within 7 Days', 'instagram-feed' ) . '</strong><br>';
				$error_message .= __( 'An account admin has deauthorized the Smash Balloon app used to power the Instagram Feed plugin.', 'instagram-feed' );
				$error_message .= ' ' . sprintf( __( 'If the Instagram source is not reconnected within 7 days then all Instagram data will be automatically deleted on your website for this account (ID: %s) due to Facebook data privacy rules.', 'instagram-feed' ), $accounts_revoked );
				$error_message .= __( '<br><br>To prevent the automated data deletion for the source, please reconnect your source within 7 days.', 'instagram-feed' );
				$error_message .= '<br><br><a href="https://smashballoon.com/doc/action-required-within-7-days/?instagram&utm_campaign=instagram-free&utm_source=permissionerror&utm_medium=notice&utm_content=More Information" target="_blank" rel="noopener">' . __( 'More Information', 'instagram-feed' ) . '</a>';
			} else {
				$error_message_array = $errors['connection']['error_message'];
				$error_message      .= '<strong>' . $error_message_array['error_message'] . '</strong><br>';
				$error_message      .= $error_message_array['admin_only'] . '<br><br>';
				if ( ! empty( $accounts_revoked_string ) ) {
					$error_message .= $accounts_revoked_string . '<br><br>';
				}
				if ( ! empty( $error_message_array['backend_directions'] ) ) {
					$error_message .= $error_message_array['backend_directions'];
				} else {
					$retry = '';
					if ( is_admin() ) {
						$retry = '<button data-url="'.get_the_permalink( $this->errors['connection']['post_id'] ).'" class="sbi-clear-errors-visit-page sbi-space-left sbi-btn sbi-notice-btn sbi-btn-grey">' . __( 'View Feed and Retry', 'instagram-feed' )  . '</button>';
					}
					$hash           = isset( $errors['connection']['error_id'] ) ? '#' . (int) $errors['connection']['error_id'] : '';
					$error_message .= '<div class="license-action-btns"><p class="sbi-error-directions"><a class="sbi-license-btn sbi-btn-blue sbi-notice-btn" href="https://smashballoon.com/instagram-feed/docs/errors/' . $hash . '" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>' . $retry. '</p></div>';
				}
			}
		} else {
			$connected_accounts = SB_Instagram_Connected_Account::get_all_connected_accounts();
			foreach ( $connected_accounts as $connected_account ) {
				if ( isset( $connected_account['private'] )
				     && sbi_private_account_near_expiration( $connected_account ) ) {
					$link_1              = '<a href="https://help.instagram.com/116024195217477/In">';
					$link_2              = '</a>';
					$error_message_array = array(
						'error_message'       => __( 'Error: Private Instagram Account.', 'instagram-feed' ),
						'admin_only'          => sprintf( __( 'It looks like your Instagram account is private. Instagram requires private accounts to be reauthenticated every 60 days. Refresh your account to allow it to continue updating, or %1$smake your Instagram account public%2$s.', 'instagram-feed' ), $link_1, $link_2 ),
						'frontend_directions' => '<a href="https://smashballoon.com/instagram-feed/docs/errors/#10">' . __( 'Click here to troubleshoot', 'instagram-feed' ) . '</a>',
						'backend_directions'  => '',
					);
				}

				if ( isset( $this->errors['accounts'][ $connected_account['user_id'] ]['api'] ) ) {
					if ( isset( $this->errors['accounts'][ $connected_account['user_id'] ]['api']['error'] ) ) {
						if ( $this->is_critical_error( $this->errors['accounts'][ $connected_account['user_id'] ]['api'] ) ) {
							$error_message_array = $this->generate_error_message( $this->errors['accounts'][ $connected_account['user_id'] ]['api'], $connected_account );
						}
					}
				}

				if ( ! isset( $error_message ) && isset( $error_message_array ) ) {
					$error_message = $error_message_array['admin_only'] . '<br><br>';
					if ( ! empty( $error_message_array['backend_directions'] ) ) {
						$error_message .= $error_message_array['backend_directions'];
					} else {
						$retry = '';
						if ( is_admin() ) {
							$retry = '<button data-url="'.get_the_permalink( $this->errors['connection']['post_id'] ).'" class="sbi-clear-errors-visit-page sbi-space-left sbi-btn sbi-notice-btn sbi-btn-grey">' . __( 'View Feed and Retry', 'instagram-feed' )  . '</button>';
						}
						$error_message .= '<p class="sbi-error-directions"><a class="sbi-license-btn sbi-btn-blue sbi-notice-btn" href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a>' . $retry. '</p>';
					}
				}
			}
		}
		if ( isset( $error_message ) ) {
			$error_message = str_replace( 'Please read the Graph API documentation at https://developers.facebook.com/docs/graph-api', '', $error_message );

		} else {
			$error_message = '';
		}

		// remove link to FB docs

		return $error_message;
	}

	/**
	 * @since 2.0/5.0
	 */
	public function reset_frontend_errors() {
		$this->display_error = array();
	}

	/**
	 * Remove all API request delays, triggered after saving settings
	 *
	 * @since 2.7/5.10
	 */
	public function clear_api_request_delays() {
		if ( empty( $this->errors['accounts'] )
		     && empty( $this->errors['hashtag'] ) ) {
			return;
		}

		$changed = false;
		foreach ( $this->errors['accounts'] as $account_id => $account_error ) {
			if ( ! empty( $account_error['api']['clear_time'] ) ) {
				$this->errors['accounts'][ $account_id ]['api']['clear_time'] = 0;
			}
			$changed = true;
		}

		foreach ( $this->errors['hashtag'] as $key => $hashtag_error ) {
			if ( ! empty( $hashtag_error['hashtag'] ) ) {
				if ( ! empty( $hashtag_error['clear_time'] ) ) {
					$this->errors['hashtag'][ $key ]['clear_time'] = 0;
					$changed                                       = true;
				}
			}
		}

		if ( $changed ) {
			update_option( 'sb_instagram_errors', $this->errors, false );
		}
	}

	/**
	 * @since 2.0/5.1.2
	 */
	public function are_current_api_request_delays( $connected_account ) {
		if ( empty( $this->errors['accounts'] ) ) {
			return false;
		}
		$account_id = $connected_account['user_id'];

		$is_delay = false;
		if ( isset( $this->errors['accounts'][ $account_id ]['api'] ) ) {
			if ( ! empty( $this->errors['accounts'][ $account_id ]['api']['clear_time'] ) ) {

				if ( $this->errors['accounts'][ $account_id ]['api']['clear_time'] < time() ) {
					$is_delay = false;
					return apply_filters( 'sbi_is_api_delay', $is_delay );
				} else {
					$is_delay = true;
					return apply_filters( 'sbi_is_api_delay', $is_delay );
				}
			}
		}

		return apply_filters( 'sbi_is_api_delay', $is_delay );
	}

	/**
	 * Whether or not there is at least one critical error
	 *
	 * @return bool
	 */
	public function are_critical_errors() {
		if ( isset( $this->errors['connection']['critical'] ) ) {
			return true;
		} else {
			$connected_accounts =  SB_Instagram_Connected_Account::get_all_connected_accounts();
			foreach ( $connected_accounts as $connected_account ) {
				if ( isset( $connected_account['private'] )
				     && sbi_private_account_near_expiration( $connected_account ) ) {
					return true;
				}

				$user_id = ! empty( $connected_account['user_id'] ) ? $connected_account['user_id'] : 0;
				$user_id = empty( $user_id ) && ! empty( $connected_account['account_id'] ) ? $connected_account['account_id'] : 0;

				if ( isset( $this->errors['accounts'][ $user_id ]['api'] ) ) {
					if ( isset( $this->errors['accounts'][ $user_id ]['api']['error'] ) ) {
						return $this->is_critical_error( $this->errors['accounts'][ $user_id ]['api'] );
					}
				}
			}
		}

		return false;
	}

	/**
	 * Whether or not there was a platform data clearing error
	 *
	 * @return bool
	 */
	public function was_app_permission_related_error() {
		return ! empty( $this->errors['revoked'] );
	}

	public function get_app_permission_related_error_ids() {
		return $this->errors['revoked'];
	}


	/**
	 * Delete any data associated with the Instagram API and the
	 * connected account being deleted.
	 *
	 * @param $to_delete_connected_account
	 */
	public function delete_platform_data( $to_delete_connected_account ) {

		$are_other_business_accounts = false;
		$all_connected_accounts      = SB_Instagram_Connected_Account::get_all_connected_accounts();

		$to_update = array();
		foreach ( $all_connected_accounts as $connected_account ) {
			if ( (int) $connected_account['user_id'] !== (int) $to_delete_connected_account['user_id'] ) {
				$to_update[ $connected_account['user_id'] ] = $connected_account;

				if ( isset( $connected_account['type'] )
				     && $connected_account['type'] === 'business' ) {
					$are_other_business_accounts = true;
				}
			}
		}

		SB_Instagram_Connected_Account::update_connected_accounts( $to_update );

		\InstagramFeed\Builder\SBI_Db::delete_source_by_account_id( $to_delete_connected_account['user_id'] );

		$manager = new SB_Instagram_Data_Manager();

		$manager->delete_caches();
		$manager->delete_comments_data();

		if ( empty( $to_update ) || ! $are_other_business_accounts ) {
			$manager->delete_hashtag_data();
		} else {
			$manager->delete_non_hashtag_sbi_instagram_posts( $to_delete_connected_account['username'] );
		}
	}


}
