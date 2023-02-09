<?php
/**
 * Instagram Feed Database
 *
 * @since 6.0
 */

namespace InstagramFeed\Builder;

class SBI_Db {

	const RESULTS_PER_PAGE = 20;

	const RESULTS_PER_CRON_UPDATE = 14;

	/**
	 * Query the sbi_sources table
	 *
	 * @param array $args
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public static function source_query( $args = array() ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$feeds_table_name   = $wpdb->prefix . 'sbi_feeds';

		$page = 0;
		if ( isset( $args['page'] ) ) {
			$page = (int) $args['page'] - 1;
			unset( $args['page'] );
		}

		$offset = max( 0, $page * 400 );

		if ( empty( $args ) ) {

			$limit = 400;
			$sql   = "SELECT s.id, s.account_id, s.account_type, s.privilege, s.access_token, s.username, s.info, s.error, s.expires, count(f.id) as used_in
				FROM $sources_table_name s
				LEFT JOIN $feeds_table_name f ON f.settings LIKE CONCAT('%', s.account_id, '%')
				GROUP BY s.id, s.account_id
				LIMIT $limit
				OFFSET $offset;
				";

			$results = $wpdb->get_results( $sql, ARRAY_A );

			if ( empty( $results ) ) {
				return array();
			}

			$i = 0;
			foreach ( $results as $result ) {
				if ( (int) $result['used_in'] > 0 ) {
					$results[ $i ]['instances'] = $wpdb->get_results( $wpdb->prepare(
						"SELECT *
						FROM $feeds_table_name
						WHERE settings LIKE CONCAT('%', %s, '%')
						GROUP BY id
						LIMIT 100;
						", $result['account_id'] ), ARRAY_A );
				}
				$i++;
			}

			return $results;
		}

		if ( ! empty( $args['expiring'] ) ) {
			$sql = $wpdb->prepare(
				"
			SELECT * FROM $sources_table_name
			WHERE account_type = 'personal'
			AND expires < %s
			AND last_updated < %s
			ORDER BY expires ASC
			LIMIT 5;
		 ",
				gmdate( 'Y-m-d H:i:s', time() + SBI_REFRESH_THRESHOLD_OFFSET ),
				gmdate( 'Y-m-d H:i:s', time() - SBI_MINIMUM_INTERVAL )
			);

			return $wpdb->get_results( $sql, ARRAY_A );
		}

		if ( ! empty( $args['username'] ) ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"
			SELECT * FROM $sources_table_name
			WHERE username = %s;
		 ",
					$args['username']
				),
				ARRAY_A
			);
		}

		if ( isset( $args['access_token'] ) && ! isset( $args['id'] ) ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"
			SELECT * FROM $sources_table_name
			WHERE access_token = %s;
		 ",
					$args['access_token']
				),
				ARRAY_A
			);
		}

		if ( ! isset( $args['id'] ) ) {
			return false;
		}

		if ( is_array( $args['id'] ) ) {
			$id_array = array();
			foreach ( $args['id'] as $id ) {
				$id_array[] = esc_sql( $id );
			}
		} elseif ( strpos( $args['id'], ',' ) !== false ) {
			$id_array = explode( ',', str_replace( ' ', '', esc_sql( $args['id'] ) ) );
		}
		if ( isset( $id_array ) ) {
			$id_string = "'" . implode( "' , '", array_map( 'esc_sql', $id_array ) ) . "'";
		}

		if ( ! empty( $args['all_business'] ) ) {
			$id_string = empty( $id_string ) ? '0' : $id_string;
			$sql       = "
			SELECT * FROM $sources_table_name
			WHERE account_id IN ($id_string)
			OR account_type = 'business'
		 ";

			return $wpdb->get_results( $sql, ARRAY_A );
		}

		$privilege = '';

		if ( ! empty( $privilege ) ) {
			if ( isset( $id_string ) ) {
				$sql = $wpdb->prepare(
					"
			SELECT * FROM $sources_table_name
			WHERE account_id IN ($id_string)
			AND privilege = %s;
		 ",
					$privilege
				);

			} else {
				$sql = $wpdb->prepare(
					"
			SELECT * FROM $sources_table_name
			WHERE account_id = %s
			AND privilege = %s;
		 ",
					$args['id'],
					$privilege
				);
			}
		} else {
			if ( isset( $id_string ) ) {
				$sql = "
				SELECT * FROM $sources_table_name
				WHERE account_id IN ($id_string);
				";

			} else {
				$sql = $wpdb->prepare(
					"
				SELECT * FROM $sources_table_name
				WHERE account_id = %s;
			    ",
					$args['id']
				);
			}
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Update a source (connected account)
	 *
	 * @param array $to_update
	 * @param array $where_data
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function source_update( $to_update, $where_data ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$encryption         = new \SB_Instagram_Data_Encryption();

		$data         = array();
		$where        = array();
		$format       = array();
		$where_format = array();
		if ( isset( $to_update['type'] ) ) {
			$data['account_type'] = $to_update['type'];
			$format[]             = '%s';
		}
		if ( isset( $to_update['privilege'] ) ) {
			$data['privilege'] = $to_update['privilege'];
			$format[]          = '%s';
		}
		if ( isset( $to_update['id'] ) ) {
			$where['account_id'] = $to_update['id'];
			$where_format[]      = '%s';
		}
		if ( isset( $to_update['access_token'] ) ) {
			$data['access_token'] = $encryption->maybe_encrypt( $to_update['access_token'] );
			$format[]             = '%s';
		}
		if ( isset( $to_update['username'] ) ) {
			$data['username'] = $to_update['username'];
			$format[]         = '%s';
		}
		if ( isset( $to_update['info'] ) ) {
			$data['info'] = $encryption->maybe_encrypt( $to_update['info'] );
			$format[]     = '%s';
		}
		if ( isset( $to_update['error'] ) ) {
			$data['error'] = $to_update['error'];
			$format[]      = '%s';
		}
		if ( isset( $to_update['expires'] ) ) {
			$data['expires'] = $to_update['expires'];
			$format[]        = '%s';
		}
		if ( isset( $to_update['last_updated'] ) ) {
			$data['last_updated'] = $to_update['last_updated'];
			$format[]             = '%s';
		}
		if ( isset( $to_update['author'] ) ) {
			$data['author'] = $to_update['author'];
			$format[]       = '%d';
		}

		if ( isset( $where_data['type'] ) ) {
			$where['account_type'] = $where_data['type'];
			$where_format[]        = '%s';
		}
		if ( isset( $where_data['privilege'] ) ) {
			$where['privilege'] = $where_data['privilege'];
			$where_format[]     = '%s';
		}
		if ( isset( $where_data['author'] ) ) {
			$where['author'] = $where_data['author'];
			$where_format[]  = '%d';
		}
		if ( isset( $where_data['id'] ) ) {
			$where['account_id'] = $where_data['id'];
			$where_format[]      = '%s';
		}
		if ( isset( $where_data['record_id'] ) ) {
			$where['id']    = $where_data['record_id'];
			$where_format[] = '%d';
		}
		$affected = $wpdb->update( $sources_table_name, $data, $where, $format, $where_format );

		return $affected;
	}

	/**
	 * New source (connected account) data is added to the
	 * sbi_sources table and the new insert ID is returned
	 *
	 * @param array $to_insert
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function source_insert( $to_insert ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$encryption         = new \SB_Instagram_Data_Encryption();

		$data   = array();
		$format = array();
		if ( isset( $to_insert['id'] ) ) {
			$data['account_id'] = $to_insert['id'];
			$format[]           = '%s';
		}
		if ( isset( $to_insert['type'] ) ) {
			$data['account_type'] = $to_insert['type'];
			$format[]             = '%s';
		} else {
			$data['account_type'] = 'page';
			$format[]             = '%s';
		}
		if ( isset( $to_insert['privilege'] ) ) {
			$data['privilege'] = $to_insert['privilege'];
			$format[]          = '%s';
		}
		if ( isset( $to_insert['access_token'] ) ) {
			$data['access_token'] = $encryption->maybe_encrypt( $to_insert['access_token'] );
			$format[]             = '%s';
		}
		if ( isset( $to_insert['username'] ) ) {
			$data['username'] = $to_insert['username'];
			$format[]         = '%s';
		}
		if ( isset( $to_insert['info'] ) ) {
			$data['info'] = $encryption->maybe_encrypt( $to_insert['info'] );
			$format[]     = '%s';
		}
		if ( isset( $to_insert['error'] ) ) {
			$data['error'] = $to_insert['error'];
			$format[]      = '%s';
		}
		if ( isset( $to_insert['expires'] ) ) {
			$data['expires'] = $to_insert['expires'];
			$format[]        = '%s';
		} else {
			$data['expires'] = '2100-12-30 00:00:00';
			$format[]        = '%s';
		}
		$data['last_updated'] = gmdate( 'Y-m-d H:i:s' );
		$format[]             = '%s';
		if ( isset( $to_insert['author'] ) ) {
			$data['author'] = $to_insert['author'];
			$format[]       = '%d';
		} else {
			$data['author'] = get_current_user_id();
			$format[]       = '%d';
		}

		return $wpdb->insert( $sources_table_name, $data, $format );
	}

	/**
	 * Query the to get feeds list for Elementor
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function elementor_feeds_query() {
		global $wpdb;
		$feeds_elementor  = array();
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$feeds_list       = $wpdb->get_results(
			"
			SELECT id, feed_name FROM $feeds_table_name;
			"
		);
		if ( ! empty( $feeds_list ) ) {
			foreach ( $feeds_list as $feed ) {
				$feeds_elementor[ $feed->id ] = $feed->feed_name;
			}
		}
		return $feeds_elementor;
	}


	/**
	 * Count the sbi_feeds table
	 *
	 * @return int
	 *
	 * @since 6.0
	 */
	public static function feeds_count() {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$results          = $wpdb->get_results(
			"SELECT COUNT(*) AS num_entries FROM $feeds_table_name",
			ARRAY_A
		);
		return isset( $results[0]['num_entries'] ) ? (int) $results[0]['num_entries'] : 0;
	}


	/**
	 * Query the sbi_feeds table
	 *
	 * @param array $args
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public static function feeds_query( $args = array() ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$page             = 0;
		if ( isset( $args['page'] ) ) {
			$page = (int) $args['page'] - 1;
			unset( $args['page'] );
		}

		$offset = max( 0, $page * self::RESULTS_PER_PAGE );

		if ( isset( $args['id'] ) ) {
			$sql = $wpdb->prepare(
				"
			SELECT * FROM $feeds_table_name
			WHERE id = %d;
		 ",
				$args['id']
			);
		} else {
			$sql = $wpdb->prepare(
				"
			SELECT * FROM $feeds_table_name
			LIMIT %d
			OFFSET %d;",
				self::RESULTS_PER_PAGE,
				$offset
			);
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Update feed data in the sbi_feed table
	 *
	 * @param array $to_update
	 * @param array $where_data
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function feeds_update( $to_update, $where_data ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';

		$data   = array();
		$where  = array();
		$format = array();
		foreach ( $to_update as $single_insert ) {
			if ( $single_insert['key'] ) {
				$data[ $single_insert['key'] ] = $single_insert['values'][0];
				$format[]                      = '%s';
			}
		}

		if ( isset( $where_data['id'] ) ) {
			$where['id']  = $where_data['id'];
			$where_format = array( '%d' );
		} elseif ( isset( $where_data['feed_name'] ) ) {
			$where['feed_name'] = $where_data['feed_name'];
			$where_format       = array( '%s' );
		} else {
			return false;
		}

		$data['last_modified'] = gmdate( 'Y-m-d H:i:s' );
		$format[]              = '%s';

		$affected = $wpdb->update( $feeds_table_name, $data, $where, $format, $where_format );

		return $affected;
	}

	/**
	 * New feed data is added to the sbi_feeds table and
	 * the new insert ID is returned
	 *
	 * @param array $to_insert
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function feeds_insert( $to_insert ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';

		$data   = array();
		$format = array();
		foreach ( $to_insert as $single_insert ) {
			if ( $single_insert['key'] ) {
				$data[ $single_insert['key'] ] = $single_insert['values'][0];
				$format[]                      = '%s';
			}
		}

		$data['last_modified'] = gmdate( 'Y-m-d H:i:s' );
		$format[]              = '%s';

		$data['author'] = get_current_user_id();
		$format[]       = '%d';

		$wpdb->insert( $feeds_table_name, $data, $format );
		return $wpdb->insert_id;
	}

	/**
	 * Query the sbi_feeds table
	 * Porcess to define the name of the feed when adding new
	 *
	 * @param string $sourcename
	 *
	 * @return array|bool
	 *
	 * @since 6.0
	 */
	public static function feeds_query_name( $sourcename ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$sql              = $wpdb->prepare(
			"SELECT * FROM $feeds_table_name
			WHERE feed_name LIKE %s;",
			$wpdb->esc_like( $sourcename ) . '%'
		);
		$count            = count( $wpdb->get_results( $sql, ARRAY_A ) );
		return ( $count === 0 ) ? $sourcename : $sourcename . ' (' . ( $count + 1 ) . ')';
	}



	/**
	 * Query to Remove Feeds from Database
	 *
	 * @param array $feed_ids_array
	 *
	 * @since 6.0
	 */
	public static function delete_feeds_query( $feed_ids_array ) {
		global $wpdb;
		$feeds_table_name       = $wpdb->prefix . 'sbi_feeds';
		$feed_caches_table_name = $wpdb->prefix . 'sbi_feed_caches';
		$feed_ids_array         = implode( ',', array_map( 'absint', $feed_ids_array ) );
		$wpdb->query(
			"DELETE FROM $feeds_table_name WHERE id IN ($feed_ids_array)"
		);
		$wpdb->query(
			"DELETE FROM $feed_caches_table_name WHERE feed_id IN ($feed_ids_array)"
		);

		echo sbi_json_encode( SBI_Feed_Builder::get_feed_list() );
		wp_die();
	}

	/**
	 * Query to Remove Source from Database
	 *
	 * @param array $source_id
	 *
	 * @since 6.0.6
	 */
	public static function delete_source( $source_id ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $sources_table_name WHERE id = %d; ",
				$source_id
			)
		);

	}

	/**
	 * Query to Remove Source from Database
	 *
	 * @param array $source_id
	 *
	 * @since 6.0
	 */
	public static function delete_source_query( $source_id ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $sources_table_name WHERE id = %d; ",
				$source_id
			)
		);

		echo sbi_json_encode( SBI_Feed_Builder::get_source_list() );
		wp_die();
	}

	/**
	 * Query to Remove Source from Database
	 *
	 * @param array $source_id
	 *
	 * @since 6.0
	 */
	public static function delete_source_by_account_id( $source_id ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $sources_table_name WHERE account_id = %s; ",
				$source_id
			)
		);
	}

	/**
	 * Query to Duplicate a Single Feed
	 *
	 * @param int $feed_id
	 *
	 * @since 6.0
	 */
	public static function duplicate_feed_query( $feed_id ) {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $feeds_table_name (feed_name, settings, author, status)
				SELECT CONCAT(feed_name, ' (copy)'), settings, author, status
				FROM $feeds_table_name
				WHERE id = %d; ",
				$feed_id
			)
		);

		echo sbi_json_encode( SBI_Feed_Builder::get_feed_list() );
		wp_die();
	}


	/**
	 * Get cache records in the sbi_feed_caches table
	 *
	 * @param array $args
	 *
	 * @return array|object|null
	 */
	public static function feed_caches_query( $args ) {
		global $wpdb;
		$feed_cache_table_name = $wpdb->prefix . 'sbi_feed_caches';

		if ( ! isset( $args['cron_update'] ) ) {
			$sql = "
			SELECT * FROM $feed_cache_table_name;";
		} else {
			if ( ! isset( $args['additional_batch'] ) ) {
				$sql = $wpdb->prepare(
					"
					SELECT * FROM $feed_cache_table_name
					WHERE cron_update = 'yes'
					ORDER BY last_updated ASC
					LIMIT %d;",
					self::RESULTS_PER_CRON_UPDATE
				);
			} else {
				$sql = $wpdb->prepare(
					"
					SELECT * FROM $feed_cache_table_name
					WHERE cron_update = 'yes'
					AND last_updated < %s
					ORDER BY last_updated ASC
					LIMIT %d;",
					gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS ),
					self::RESULTS_PER_CRON_UPDATE
				);
			}
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}
   /**
    * Creates all database tables used in the new admin area in
    * the 6.0 update.
    *
    * TODO: Add error reporting
    *
    * @since 6.0
    */
	public static function create_tables( $include_charset_collate = true ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		}

		global $wpdb;
		$max_index_length = 191;
		$charset_collate  = '';
		if ( $include_charset_collate && method_exists( $wpdb, 'get_charset_collate' ) ) { // get_charset_collate introduced in WP 3.5
			$charset_collate = $wpdb->get_charset_collate();
		}

		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';

		if ( $wpdb->get_var( "show tables like '$feeds_table_name'" ) !== $feeds_table_name ) {
			$sql = "
			CREATE TABLE $feeds_table_name (
			 id bigint(20) unsigned NOT NULL auto_increment,
			 feed_name text NOT NULL default '',
			 feed_title text NOT NULL default '',
			 settings longtext NOT NULL default '',
			 author bigint(20) unsigned NOT NULL default '1',
			 status varchar(255) NOT NULL default '',
			 last_modified datetime NOT NULL,
			 PRIMARY KEY  (id),
			 KEY author (author)
			) $charset_collate;
			";
			$wpdb->query( $sql );
		}
		$error     = $wpdb->last_error;
		$query     = $wpdb->last_query;
		$had_error = false;
		if ( $wpdb->get_var( "show tables like '$feeds_table_name'" ) !== $feeds_table_name ) {
			$had_error = true;
		}

		if ( ! $had_error ) {
		}

		$feed_caches_table_name = $wpdb->prefix . 'sbi_feed_caches';

		if ( $wpdb->get_var( "show tables like '$feed_caches_table_name'" ) !== $feed_caches_table_name ) {
			$sql = '
				CREATE TABLE ' . $feed_caches_table_name . " (
				id bigint(20) unsigned NOT NULL auto_increment,
				feed_id varchar(255) NOT NULL default '',
                cache_key varchar(255) NOT NULL default '',
                cache_value longtext NOT NULL default '',
                cron_update varchar(20) NOT NULL default 'yes',
                last_updated datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY feed_id (feed_id($max_index_length))
            ) $charset_collate;";
			$wpdb->query( $sql );
		}
		$error     = $wpdb->last_error;
		$query     = $wpdb->last_query;
		$had_error = false;
		if ( $wpdb->get_var( "show tables like '$feed_caches_table_name'" ) !== $feed_caches_table_name ) {
			$had_error = true;
		}

		if ( ! $had_error ) {
		}

		$sources_table_name = $wpdb->prefix . 'sbi_sources';

		if ( $wpdb->get_var( "show tables like '$sources_table_name'" ) !== $sources_table_name ) {
			$sql = '
			CREATE TABLE ' . $sources_table_name . " (
				id bigint(20) unsigned NOT NULL auto_increment,
				account_id varchar(255) NOT NULL default '',
                account_type varchar(255) NOT NULL default '',
                privilege varchar(255) NOT NULL default '',
                access_token varchar(1000) NOT NULL default '',
                username varchar(255) NOT NULL default '',
                info text NOT NULL default '',
                error text NOT NULL default '',
                expires datetime NOT NULL,
                last_updated datetime NOT NULL,
                author bigint(20) unsigned NOT NULL default '1',
                PRIMARY KEY  (id),
                KEY account_type (account_type($max_index_length)),
                KEY author (author)
            ) $charset_collate;";
			$wpdb->query( $sql );
		}
		$error     = $wpdb->last_error;
		$query     = $wpdb->last_query;
		$had_error = false;
		if ( $wpdb->get_var( "show tables like '$sources_table_name'" ) !== $sources_table_name ) {
			$had_error = true;
		}

		if ( ! $had_error ) {
		}
	}

	public static function create_sources_database() {
		// not needed
	}

	public static function clear_sbi_feed_caches() {
		global $wpdb;
		$feed_caches_table_name = $wpdb->prefix . 'sbi_feed_caches';

		if ( $wpdb->get_var( "show tables like '$feed_caches_table_name'" ) === $feed_caches_table_name ) {
			$wpdb->query( "DELETE FROM $feed_caches_table_name" );
		}
	}

	public static function clear_sbi_sources() {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';

		if ( $wpdb->get_var( "show tables like '$sources_table_name'" ) === $sources_table_name ) {
			$wpdb->query( "DELETE FROM $sources_table_name" );
		}
	}

	public static function reset_tables() {
		global $wpdb;
		$feeds_table_name = $wpdb->prefix . 'sbi_feeds';

		$wpdb->query( "DROP TABLE IF EXISTS $feeds_table_name" );
		$feed_caches_table_name = $wpdb->prefix . 'sbi_feed_caches';

		$wpdb->query( "DROP TABLE IF EXISTS $feed_caches_table_name" );

		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$wpdb->query( "DROP TABLE IF EXISTS $sources_table_name" );
	}

	public static function reset_db_update() {
		update_option( 'sbi_db_version', 1.9 );
		delete_option( 'sbi_legacy_feed_settings' );

		// are there existing feeds to toggle legacy onboarding?
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		if ( isset( $sbi_statuses_option['legacy_onboarding'] ) ) {
			unset( $sbi_statuses_option['legacy_onboarding'] );
		}
		if ( isset( $sbi_statuses_option['support_legacy_shortcode'] ) ) {
			unset( $sbi_statuses_option['support_legacy_shortcode'] );
		}

		global $wpdb;

		$table_name = $wpdb->prefix . 'usermeta';
		$wpdb->query(
			"
        DELETE
        FROM $table_name
        WHERE `meta_key` LIKE ('sbi\_%')
        "
		);

		$feed_locator_table_name = esc_sql( $wpdb->prefix . SBI_INSTAGRAM_FEED_LOCATOR );

		$results = $wpdb->query(
			"
			DELETE
			FROM $feed_locator_table_name
			WHERE feed_id LIKE '*%';"
		);

		update_option( 'sbi_statuses', $sbi_statuses_option );
	}

	public static function reset_legacy() {

		//Settings
		delete_option( 'sbi_statuses' );
		delete_option( 'sb_instagram_settings' );
		delete_option( 'sbi_ver' );
		delete_option( 'sb_expired_tokens' );
		delete_option( 'sbi_cron_report' );
		delete_option( 'sb_instagram_errors' );
		delete_option( 'sb_instagram_ajax_status' );
		delete_option( 'sbi_db_version' );

		// Clear backup caches
		global $wpdb;
		$table_name = $wpdb->prefix . 'options';
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
		$wpdb->query(
			"
    DELETE
    FROM $table_name
    WHERE `option_name` LIKE ('%sb_wlupdated_%')
    "
		);

		//image resizing
		$upload                 = wp_upload_dir();
		$posts_table_name       = $wpdb->prefix . 'sbi_instagram_posts';
		$feeds_posts_table_name = esc_sql( $wpdb->prefix . 'sbi_instagram_feeds_posts' );

		$image_files = glob( trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . '*' ); // get all file names
		foreach ( $image_files as $file ) { // iterate files
			if ( is_file( $file ) ) {
				unlink( $file );
			} // delete file
		}

		//Delete tables
		$wpdb->query( "DROP TABLE IF EXISTS $posts_table_name" );
		$wpdb->query( "DROP TABLE IF EXISTS $feeds_posts_table_name" );
		$locator_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEED_LOCATOR;
		$wpdb->query( "DROP TABLE IF EXISTS $locator_table_name" );

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
		delete_option( 'sb_instagram_errors' );
		delete_option( 'sbi_usage_tracking_config' );
		delete_option( 'sbi_usage_tracking' );
		delete_option( 'sbi_oembed_token' );
		delete_option( 'sbi_top_api_calls' );
		delete_option( 'sbi_rating_notice' );
		delete_option( 'sbi_refresh_report' );
		delete_option( 'sbi_welcome_seen' );
		delete_option( 'sbi_notifications' );
		delete_option( 'sbi_newuser_notifications' );

		global $wp_roles;
		$wp_roles->remove_cap( 'administrator', 'manage_instagram_feed_options' );
		wp_clear_scheduled_hook( 'sbi_feed_update' );
		wp_clear_scheduled_hook( 'sbi_usage_tracking_cron' );
	}

	/**
	 * Query to Get Single source
	 *
	 * @param array $source_id
	 *
	 * @since 6.0.8
	 */
	public static function get_source_by_account_id( $source_id ) {
		global $wpdb;
		$sources_table_name = $wpdb->prefix . 'sbi_sources';
		$sql = $wpdb->prepare(
				"SELECT * FROM $sources_table_name WHERE account_id = %s; ",
				$source_id
			);
		return $wpdb->get_row( $sql, ARRAY_A );
	}
}
