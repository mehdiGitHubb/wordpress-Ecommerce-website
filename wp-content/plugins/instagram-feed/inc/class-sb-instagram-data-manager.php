<?php
/**
 * Class SB_Instagram_Data_Manager
 *
 * @since 2.9.4/5.12.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Data_Manager {

	/**
	 * Key and salt to use for remote encryption.
	 *
	 * @var string
	 *
	 * @since 2.9.4/5.12.4
	 */
	private $key_salt;

	/**
	 * Start manager
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function init() {
		$this->hooks();
	}

	/**
	 * Hook into certain features of the plugin and AJAX calls
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function hooks() {
		add_action( 'sbi_before_display_instagram', array( $this, 'update_last_used' ) );
		add_action( 'sbi_before_display_instagram', array( $this, 'check' ) );
		add_action( 'sbi_before_display_instagram', array( $this, 'maybe_update_legacy_sources' ) );
		add_action( 'sb_instagram_twicedaily', array( $this, 'maybe_delete_old_data' ) );
	}

	/**
	 * To avoid a database update every page load, the check
	 * is done once a day
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function update_last_used() {
		$statuses = $this->get_statuses();

		// if this hasn't been updated in the last hour
		if ( $statuses['last_used'] < sbi_get_current_time() - 3600 ) {
			// update the last used time
			$statuses['last_used'] = sbi_get_current_time();

			$this->update_statuses( $statuses );
		}
	}

	/**
	 * Check for plain text instagram data in posts table
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function check() {
		$this->encrypt_json_in_sbi_instagram_posts();
	}

	/**
	 * Updates legacy sources if some are left in the queue from an update
	 */
	public function maybe_update_legacy_sources() {
		if ( \InstagramFeed\Builder\SBI_Source::should_do_source_updates() ) {
			\InstagramFeed\Builder\SBI_Source::batch_process_legacy_source_queue();
		}
	}

	/**
	 * Delete unused data after a period
	 *
	 * @return bool
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function maybe_delete_old_data() {
		global $sb_instagram_posts_manager;

		$statuses = $this->get_statuses();

		$data_was_deleted = false;

		do_action( 'sbi_before_delete_old_data', $statuses );

		if ( $statuses['last_used'] < sbi_get_current_time() - ( 21 * DAY_IN_SECONDS ) ) {
			$this->delete_caches();
			$this->delete_comments_data();
			$this->delete_hashtag_data();

			$sb_instagram_posts_manager->add_action_log( 'Deleted all platform data.' );

			$data_was_deleted = true;
		}

		if ( $statuses['last_used'] < sbi_get_current_time() - ( 90 * DAY_IN_SECONDS ) ) {
			SB_Instagram_Connected_Account::update_connected_accounts( array() );
			\InstagramFeed\Builder\SBI_Db::clear_sbi_sources();
			global $sb_instagram_posts_manager;

			$sb_instagram_posts_manager->add_action_log( 'Deleted all connected accounts.' );

			$data_was_deleted = true;
		}

		return $data_was_deleted;
	}

	/**
	 * Delete all data related to hashtags
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function delete_hashtag_data() {
		global $sb_instagram_posts_manager;

		$sb_instagram_posts_manager->delete_all_sbi_instagram_posts();

		delete_option( 'sbi_top_api_calls' );
		delete_option( 'sbi_local_avatars' );
	}

	/**
	 * Delete all non hashtag related data for an account
	 *
	 * @param string $username
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function delete_non_hashtag_sbi_instagram_posts( $username ) {
		global $wpdb;
		$table_name             = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;

		$non_hashtag_posts = $wpdb->get_results(
			"SELECT p.id, p.media_id FROM $table_name as p
					INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
					WHERE f.hashtag = '';",
			ARRAY_A
		);

		$upload        = wp_upload_dir();
		$file_suffixes = array( 'thumb', 'low', 'full' );

		foreach ( $non_hashtag_posts as $post ) {
			foreach ( $file_suffixes as $file_suffix ) {
				$file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $post['media_id'] . $file_suffix . '.jpg';
				if ( is_file( $file_name ) ) {
					unlink( $file_name );
				}
			}
		}

		$file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $username . '.jpg';
		if ( is_file( $file_name ) ) {
			unlink( $file_name );
		}

		$non_hashtag_posts_deleted = $wpdb->query(
			"DELETE p, f FROM $table_name as p
					INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
					WHERE f.hashtag = '';"
		);

	}

	/**
	 * Delete post data in non-hashtag related posts
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function update_json_non_hashtag_sbi_instagram_posts() {
		global $wpdb;
		$table_name             = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;

		$updated = $wpdb->query(
			"UPDATE $table_name as p
				INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
				SET p.json_data = ''
				WHERE f.hashtag = '';"
		);

	}

	/**
	 * Encrypt a set of 50 posts if this has been attempted
	 * less than 30 times.
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function encrypt_json_in_sbi_instagram_posts() {
		$statuses = $this->get_statuses();
		// if this hasn't been updated in the last hour
		if ( $statuses['num_db_updates'] > 30 ) {
			return;
		}

		$statuses['num_db_updates'] = $statuses['num_db_updates'] + 1;
		$this->update_statuses( $statuses );

		global $wpdb;
		$encryption             = new SB_Instagram_Data_Encryption();
		$table_name             = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;

		$plaintext_posts = $wpdb->get_results(
			"SELECT * FROM $table_name as p
					INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
					WHERE p.json_data LIKE '%{%'
					ORDER BY p.time_stamp DESC
					LIMIT 50;",
			ARRAY_A
		);

		if ( empty( $plaintext_posts ) ) {
			$statuses['num_db_updates'] = 31;
			$this->update_statuses( $statuses );
		}

		foreach ( $plaintext_posts as $post ) {
			$json_data = $encryption->encrypt( $post['json_data'] );
			$updated   = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $table_name as p
					INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
					SET p.json_data = %s
					WHERE p.id = %d;",
					$json_data,
					$post['id']
				)
			);
		}
	}

	/**
	 * Delete all comments data
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function delete_comments_data() {
		/* Comment Cache */
		delete_transient( 'sbinst_comment_cache' );
	}

	/**
	 * Delete feed caches
	 *
	 * @param bool $include_backup
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function delete_caches( $include_backup = true ) {
		/* Backup Caches */
		global $wpdb;
		$table_name = $wpdb->prefix . 'options';

		if ( $include_backup ) {
			$wpdb->query(
				"
		    DELETE
		    FROM $table_name
		    WHERE `option_name` LIKE ('%!sbi\_%')
		    "
			);
			$wpdb->query(
				"
		    DELETE
		    FROM $table_name
		    WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
		    "
			);
			$wpdb->query(
				"
		    DELETE
		    FROM $table_name
		    WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
        "
			);
		}

		/* Regular Caches */
		//Delete all transients
		$wpdb->query(
			"
		        DELETE
		        FROM $table_name
		        WHERE `option_name` LIKE ('%\_transient\_sbi\_%')
		        "
		);
		$wpdb->query(
			"
		        DELETE
		        FROM $table_name
		        WHERE `option_name` LIKE ('%\_transient\_timeout\_sbi\_%')
		        "
		);
		$wpdb->query(
			"
		        DELETE
		        FROM $table_name
		        WHERE `option_name` LIKE ('%\_transient\_&sbi\_%')
		        "
		);
		$wpdb->query(
			"
		        DELETE
		        FROM $table_name
		        WHERE `option_name` LIKE ('%\_transient\_timeout\_&sbi\_%')
		        "
		);
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

		delete_option( 'sbi_single_cache' );

		\InstagramFeed\Builder\SBI_Db::clear_sbi_feed_caches();
	}

	/**
	 * Update all parts of the database for FB platform guidelines
	 *
	 * @throws Exception
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function update_db_for_dpa() {
		global $wpdb;
		$encryption = new SB_Instagram_Data_Encryption();
		$table_name = $wpdb->prefix . 'options';

		$permanent_caches = $wpdb->get_results(
			"
		    SELECT *
		    FROM $table_name
		    WHERE option_name LIKE ('%!sbi\_%')
		    ",
			ARRAY_A
		);

		if ( count( $permanent_caches ) < 10 ) {
			foreach ( $permanent_caches as $permanent_cache ) {
				$value = $permanent_cache['option_value'];
				if ( strpos( $value, '{' ) === 0 ) {
					$value = $encryption->encrypt( $value );
					update_option( $permanent_cache['option_name'], $value, false );
				}
			}

			$this->delete_caches( false );
		} else {
			$this->delete_caches( true );
		}

		SB_Instagram_Connected_Account::encrypt_all_access_tokens();

		$this->encrypt_json_in_sbi_instagram_posts();

		$stored_option = get_option( 'sbi_single_cache', array() );
		if ( ! is_array( $stored_option ) ) {
			$stored_option = json_decode( $encryption->decrypt( $stored_option ), true );
		}
		update_option( 'sbi_single_cache', $encryption->encrypt( sbi_json_encode( $stored_option ) ), false );

		if ( sbi_is_pro_version() ) {
			$comment_cache_transient = get_transient( 'sbinst_comment_cache' );

			$maybe_decrypted = $encryption->decrypt( $comment_cache_transient );
			if ( ! empty( $maybe_decrypted ) ) {
				$comment_cache_transient = $maybe_decrypted;
			}

			$comment_cache = $comment_cache_transient ? json_decode( $comment_cache_transient, true ) : array();

			set_transient( 'sbinst_comment_cache', $encryption->encrypt( sbi_json_encode( $comment_cache ) ), 0 );
			$ids = get_option( 'sbi_hashtag_ids', array() );
			if ( ! is_array( $ids ) ) {
				$encryption = new SB_Instagram_Data_Encryption();
				$ids        = json_decode( $encryption->decrypt( $ids ), true );
			}

			update_option( 'sbi_hashtag_ids', $encryption->encrypt( sbi_json_encode( $ids ) ), false );
		}
	}

	/**
	 * Data manager statuses
	 *
	 * @return array
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function get_statuses() {
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		$return = isset( $sbi_statuses_option['data_manager'] ) ? $sbi_statuses_option['data_manager'] : $this->defaults();
		return $return;
	}

	/**
	 * Update data manager status
	 *
	 * @param array $statuses
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function update_statuses( $statuses ) {
		$sbi_statuses_option                 = get_option( 'sbi_statuses', array() );
		$sbi_statuses_option['data_manager'] = $statuses;

		update_option( 'sbi_statuses', $sbi_statuses_option );
	}

	/**
	 * Encrypt using Smash Balloon's support key and salt
	 *
	 * @param string $encrypted_value
	 *
	 * @return bool|string
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function remote_encrypt( $encrypted_value ) {
		$local_encrypt = new SB_Instagram_Data_Encryption();
		$raw_value     = $local_encrypt->decrypt( $encrypted_value );
		if ( $this->key_salt === null ) {
			$url  = 'https://secure.smashballoon.com/';
			$args = array(
				'timeout' => 20,
			);
			$response = wp_remote_get( $url, $args );

			if ( ! is_wp_error( $response ) ) {
				$this->key_salt = $response['body'];
			}
		}

		$key  = substr( $this->key_salt, 0, 64 );
		$salt = substr( $this->key_salt, 64, 64 );

		$args = array(
			'key'  => $key,
			'salt' => $salt,
		);

		$remote_encrypt = new SB_Instagram_Data_Encryption( $args );

		return $remote_encrypt->encrypt( $raw_value );
	}

	public function remote_decrypt( $encrypted_value ) {
		if ( $this->key_salt === null ) {
			$url  = 'https://secure.smashballoon.com/';
			$args = array(
				'timeout' => 20,
			);
			$response = wp_remote_get( $url, $args );

			if ( ! is_wp_error( $response ) ) {
				$this->key_salt = $response['body'];
			}
		}

		$key  = substr( $this->key_salt, 0, 64 );
		$salt = substr( $this->key_salt, 64, 64 );

		$args = array(
			'key'  => $key,
			'salt' => $salt,
		);

		$remote_encrypt = new SB_Instagram_Data_Encryption( $args );

		return $remote_encrypt->decrypt( $encrypted_value );
	}

	/**
	 * Reset the data manager
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function reset() {
		$sbi_statuses_option                 = get_option( 'sbi_statuses', array() );
		$sbi_statuses_option['data_manager'] = $this->defaults();

		update_option( 'sbi_statuses', $sbi_statuses_option );
		update_option( 'sbi_db_version', 1.9 );
	}

	/**
	 * Default values for manager
	 *
	 * @return array
	 *
	 * @since 2.9.4/5.12.4
	 */
	public function defaults() {
		return array(
			'last_used'      => sbi_get_current_time() - DAY_IN_SECONDS,
			'num_db_updates' => 0,
		);
	}
}
