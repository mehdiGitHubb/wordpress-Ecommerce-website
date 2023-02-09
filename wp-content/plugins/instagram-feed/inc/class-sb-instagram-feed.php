<?php
/**
 * Class SB_Instagram_Feed
 *
 * Retrieves data and generates the html for each feed. The
 * "display_instagram" function in the if-functions.php file
 * is where this class is primarily used.
 *
 * @since 2.0/4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Feed
{
	/**
	 * @var string
	 */
	private $regular_feed_transient_name;

	/**
	 * @var string
	 */
	private $header_transient_name;

	/**
	 * @var string
	 */
	private $backup_feed_transient_name;

	/**
	 * @var string
	 */
	private $backup_header_transient_name;

	/**
	 * @var array
	 */
	private $post_data;

	/**
	 * @var
	 */
	private $header_data;

	/**
	 * @var array
	 */
	protected $next_pages;

	/**
	 * @var array
	 */
	private $transient_atts;

	/**
	 * @var int
	 */
	private $last_retrieve;

	/**
	 * @var bool
	 */
	private $should_paginate;

	/**
	 * @var int
	 */
	private $num_api_calls;

	/**
	 * @var array
	 */
	private $image_ids_post_set;

	/**
	 * @var bool
	 */
	private $should_use_backup;

	/**
	 * @var array
	 */
	private $report;

	/**
	 * @var array
	 *
	 * @since 2.1.1/5.2.1
	 */
	private $resized_images;

	/**
	 * @var array
	 *
	 * @since 2.7/5.10
	 */
	private $cached_feed_error;

	/**
	 * @var object|SB_Instagram_Cache
	 */
	private $cache;

	/**
	 * @var int
	 *
	 * @since 5.10.1
	 */
	protected $pages_created;

	/**
	 * @var array
	 *
	 * @since 2.1.3/5.2.3
	 */
	protected $one_post_found;

	/**
	* @var object|SB_Instagram_Data_Encryption
	*
	* @since 5.14.5
	*/
	protected $encryption;


	/**
	 * SB_Instagram_Feed constructor.
	 *
	 * @param string $transient_name ID of this feed
	 *  generated in the SB_Instagram_Settings class
	 */
	public function __construct( $transient_name ) {
		$this->regular_feed_transient_name = $transient_name;
		$this->backup_feed_transient_name = SBI_BACKUP_PREFIX . $transient_name;

		$sbi_header_transient_name = str_replace( 'sbi_', 'sbi_header_', $transient_name );
		$sbi_header_transient_name = substr($sbi_header_transient_name, 0, 44);
		$this->header_transient_name = $sbi_header_transient_name;
		$this->backup_header_transient_name = SBI_BACKUP_PREFIX . $sbi_header_transient_name;

		$this->post_data = array();
		$this->next_pages = array();
		$this->cached_feed_error = array();
		$this->pages_created = 0;
		$this->should_paginate = true;

		// this is a count of how many api calls have been made for each feed
		// type and term.
		// By default the limit is 10
		$this->num_api_calls = 0;
		$this->max_api_calls = apply_filters( 'sbi_max_concurrent_api_calls', 10 );
		$this->should_use_backup = false;

		// used for errors and the sbi_debug report
		$this->report = array();

		$this->resized_images = array();

		$this->one_post_found = false;
	}

	public function get_feed_id() {
		return str_replace( '*', '', $this->regular_feed_transient_name );
	}

	public function set_cache( $cache_seconds, $settings ) {
		$feed_id = $this->regular_feed_transient_name;

		$feed_page = 1;
		$this->encryption = new SB_Instagram_Data_Encryption();
		$this->cache      = new SB_Instagram_Cache( $feed_id, $feed_page, $cache_seconds );

		$this->cache->retrieve_and_set();
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_post_data() {
		return $this->post_data;
	}

	/**
	 * @return array
	 *
	 * @since 2.7/5.10
	 */
	public function get_cached_feed_error() {
		return $this->cached_feed_error;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_next_pages() {
		return $this->next_pages;
	}

	/**
	 * @return array
	 *
	 * @since 2.1.1/5.2.1
	 */
	public function get_resized_images() {
		return $this->resized_images;
	}

	/**
	 * @since 2.0/5.0
	 */
	public function set_post_data( $post_data ) {
		$this->post_data = $post_data;
	}

	/**
	 * @since 2.1.1/5.2.1
	 */
	public function set_resized_images( $resized_image_data ) {
		$this->resized_images = $resized_image_data;
	}

	public function set_pages_created( $num ) {
		$this->pages_created = $num;
	}

	/**
	 * Checks the database option related the transient expiration
	 * to ensure it will be available when the page loads
	 *
	 * @return bool
	 *
	 * @since 2.0/4.0
	 */
	public function regular_cache_exists() {
		return ! $this->cache->is_expired( 'posts' );
	}

	/**
	 * Checks the database option related the header transient
	 * expiration to ensure it will be available when the page loads
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function regular_header_cache_exists() {
		return ! $this->cache->is_expired( 'header' );
	}

	/**
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_use_backup() {
		return $this->should_use_backup || empty( $this->post_data );
	}

	public function should_cache_error() {
		global $sb_instagram_posts_manager;

		$error_messages = $sb_instagram_posts_manager->get_frontend_errors();
		if ( ! empty( $error_messages ) ) {
			$this->cached_feed_error = $error_messages;

			return true;
		}

		return false;
	}

	/**
	 * The header is only displayed when the setting is enabled and
	 * an account has been connected
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $settings settings specific to this feed
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_header( $settings, $feed_types_and_terms ) {
		$customizer = $settings['customizer'];
		if( $customizer ){
			return true;
		}else{
			$showheader = ($settings['showheader'] === 'on' || $settings['showheader'] === 'true' || $settings['showheader'] === true);
			return ($showheader && isset( $feed_types_and_terms['users'] ));
		}
	}

	/**
	 * Use the transient name to retrieve cached data for header
	 *
	 * @since 2.0/5.0
	 */
	public function set_header_data_from_cache() {
		$header_json = $this->cache->get( 'header' );

		$header_cache = json_decode( $header_json, true );

		if ( ! empty( $header_cache ) ) {
			$this->header_data = $header_cache;
		}
	}

	public function set_header_data( $header_data ) {
		$this->header_data = $header_data;
	}

	/**
	 * @since 2.0/5.0
	 */
	public function get_header_data() {
		return $this->header_data;
	}

	/**
	 * Sets the post data, pagination data, shortcode atts used (cron cache),
	 * and timestamp of last retrieval from transient (cron cache)
	 *
	 * @param array $atts available for cron caching
	 *
	 * @since 2.0/5.0
	 */
	public function set_post_data_from_cache( $atts = array() ) {
		$posts_json = $this->cache->get( 'posts' );

		$posts_data = json_decode( $posts_json, true );

		if ( $posts_data ) {
			$post_data = isset( $posts_data['data'] ) ? $posts_data['data'] : array();
			$this->post_data = $post_data;
			$this->next_pages = isset( $posts_data['pagination'] ) ? $posts_data['pagination'] : array();
			$this->pages_created = isset( $posts_data['pages_created'] ) ? $posts_data['pages_created'] : 0;

			if ( isset( $posts_data['atts'] ) ) {
				$this->transient_atts = $posts_data['atts'];
				$this->last_retrieve = $posts_data['last_retrieve'];
			}

			if ( isset( $posts_data['errors'] ) ) {
				$this->cached_feed_error = $posts_data['errors'];
			}

			$this->add_report( 'pages created: ' . $this->pages_created .', next pages exist: ' . ! empty( $this->next_pages ) );
		}
	}

	/**
	 * Sets post data from a permanent database backup of feed
	 * if it was created
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1.2 if backup feed data used, header data also set from backup
	 */
	public function maybe_set_post_data_from_backup() {
		$backup_data = 	$this->cache->get( 'posts_backup' );

		if ( $backup_data ) {
			$backup_data = json_decode( $backup_data, true );

			$post_data = isset( $backup_data['data'] ) ? $backup_data['data'] : array();
			$this->post_data = $post_data;
			$this->next_pages = isset( $backup_data['pagination'] ) ? $backup_data['pagination'] : array();

			if ( isset( $backup_data['atts'] ) ) {
				$this->transient_atts = $backup_data['atts'];
				$this->last_retrieve = $backup_data['last_retrieve'];
			}

			$this->maybe_set_header_data_from_backup();

			return true;
		} else {
			$this->add_report( 'no backup post data found' );

			return false;
		}
	}

	/**
	 * Sets header data from a permanent database backup of feed
	 * if it was created
	 *
	 * @since 2.0/5.0
	 */
	public function maybe_set_header_data_from_backup() {
		$backup_header_data = 	$this->cache->get( 'header_backup' );

		if ( ! empty( $backup_header_data ) ) {
			$backup_header_data = json_decode( $backup_header_data, true );
			$this->header_data = $backup_header_data;

			return true;
		} else {
			$this->add_report( 'no backup header data found' );

			return false;
		}
	}

	/**
	 * Returns recorded image IDs for this post set
	 * for use with image resizing
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_image_ids_post_set() {
		return $this->image_ids_post_set;
	}

	/**
	 * Retrieves data related to resized images from custom
	 * tables using either a number, offset, and transient name
	 * or the ids of the posts.
	 *
	 * Retrieving by offset and transient name not used currently
	 * but may be needed in future updates.
	 *
	 * @param array/int $num_or_array_of_ids post ids from the Instagram
	 *  API
	 * @param int $offset number of records to skip
	 * @param string $transient_name ID of the feed
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public static function get_resized_images_source_set( $num_or_array_of_ids, $offset = 0, $transient_name = '', $should_cache = true ) {
		global $sb_instagram_posts_manager;

		if ( $sb_instagram_posts_manager->image_resizing_disabled( $transient_name ) ) {
			return array();
		}

		$feed_id = $transient_name;

		$feed_page = 1;
		$cache_obj = new SB_Instagram_Cache( $feed_id, $feed_page, HOUR_IN_SECONDS );

		$cache_obj->retrieve_and_set();
		$cache = $offset === 0 ? $cache_obj->get( 'resized_images' ) : false;
		if ( $cache ) {
			$return = json_decode( $cache, true );
		} else {

			global $wpdb;

			$offset = max( 0, $offset );

			$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
			$feeds_posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_FEEDS_POSTS;

			$feed_id_array = explode( '#', $transient_name );
			$feed_id = $feed_id_array[0];

			if ( is_array( $num_or_array_of_ids ) ) {
				$ids = $num_or_array_of_ids;

				$id_string = "'" . implode( "','", $ids ) . "'";
				$results = $wpdb->get_results( "
			SELECT p.media_id, p.instagram_id, p.aspect_ratio, p.sizes
			FROM $posts_table_name AS p
			INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
			WHERE p.instagram_id IN($id_string)
		  	AND p.images_done = 1", ARRAY_A );

				$return = array();
				if ( !empty( $results ) && is_array( $results ) ) {

					foreach ( $results as $result ) {
						$sizes = maybe_unserialize( $result['sizes'] );
						if ( ! is_array( $sizes ) ) {
							$sizes = array( 'full' => 640 );
						}
						$return[ $result['instagram_id'] ] = array(
							'id' => $result['media_id'],
							'ratio' => $result['aspect_ratio'],
							'sizes' => $sizes
						);
					}

				}

			} else {
				$num = $num_or_array_of_ids;

				$results = $wpdb->get_results( $wpdb->prepare( "
			SELECT p.media_id, p.instagram_id, p.aspect_ratio, p.sizes
			FROM $posts_table_name AS p
			INNER JOIN $feeds_posts_table_name AS f ON p.id = f.id
			WHERE f.feed_id = %s
		  	AND p.images_done = 1
			ORDER BY p.time_stamp
			DESC LIMIT %d, %d", $feed_id, $offset, (int)$num ), ARRAY_A );

				$return = array();
				if ( !empty( $results ) && is_array( $results ) ) {

					foreach ( $results as $result ) {
						$sizes = maybe_unserialize( $result['sizes'] );
						if ( ! is_array( $sizes ) ) {
							$sizes = array( 'full' => 640 );
						}
						$return[ $result['instagram_id'] ] = array(
							'id' => $result['media_id'],
							'ratio' => $result['aspect_ratio'],
							'sizes' => $sizes
						);
					}

				}

			}

			if ( $offset === 0 && $should_cache ) {
				$cache_obj->update_or_insert( 'resized_images', sbi_json_encode( $return ) );
			}
		}

		return $return;
	}

	/**
	 * The plugin tracks when a post was last requested so only the most
	 * recently displayed posts are kept in the database.
	 * This function updates the timestamp for a set of posts
	 * on the page.
	 *
	 * @param $array_of_ids
	 *
	 * @since 2.0/5.0
	 */
	public static function update_last_requested( $array_of_ids ) {
		if ( empty( $array_of_ids ) ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
		$id_string =  "'" . implode( "','", $array_of_ids ) . "'";

		$query = $wpdb->query( $wpdb->prepare( "UPDATE $table_name
		SET last_requested = %s
		WHERE instagram_id IN ({$id_string});", date( 'Y-m-d H:i:s' ) ) );
	}

	/**
	 * Cron caching needs additional data saved in the transient
	 * to work properly. This function checks to make sure it's present
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_to_start_cron_job() {
		return (( ! empty( $this->post_data ) && ! isset( $this->transient_atts )) || (empty( $this->post_data ) && empty( $this->cached_feed_error )));
	}

	/**
	 * Checks to see if there are enough posts available to create
	 * the current page of the feed
	 *
	 * @param int $num
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function need_posts( $num, $offset = 0, $page = 0 ) {
		$num_existing_posts = is_array( $this->post_data ) ? count( $this->post_data ) : 0;
		$num_needed_for_page = (int)$num + (int)$offset;
		$this->add_report( 'pages created ' .$this->pages_created . ' page on' . $page );

		if ( $this->pages_created < $page ) {
			$this->add_report( 'need another page' );
			return true;
		}

		($num_existing_posts < $num_needed_for_page) ? $this->add_report( 'need more posts ' . $num_existing_posts . ' ' . $num_needed_for_page ) : $this->add_report( 'have enough posts' );

		return ($num_existing_posts < $num_needed_for_page);
	}

	/**
	 * Checks to see if there are additional pages available for any of the
	 * accounts in the feed and that the max conccurrent api request limit
	 * has not been reached
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function can_get_more_posts() {
		$one_type_and_term_has_more_ages = $this->next_pages !== false;
		$max_concurrent_api_calls_not_met = $this->num_api_calls < $this->max_api_calls;
		$max_concurrent_api_calls_not_met ? $this->add_report( 'max conccurrent requests not met' ) : $this->add_report( 'max concurrent met' );
		$one_type_and_term_has_more_ages ? $this->add_report( 'more pages available' ) : $this->add_report( 'no next page' );

		return ($one_type_and_term_has_more_ages && $max_concurrent_api_calls_not_met);
	}

	/**
	 * Appends one filtered API request worth of posts for each feed term
	 *
	 * @param $settings
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 added logic to make a second attempt at an API connection
	 * @since 2.0/5.1.2 remote posts only retrieved if API requests are not
	 *  delayed, terms shuffled if there are more than 5
	 * @since 2.2/5.3 added logic to refresh the access token for basic display
	 *  accounts if needed before using it in an API request
	 */
	public function add_remote_posts( $settings, $feed_types_and_terms, $connected_accounts_for_feed ) {
		$this->pages_created ++;

		$new_post_sets = array();
		$next_pages = $this->next_pages;
		global $sb_instagram_posts_manager;

		/**
		 * Number of posts to retrieve in each API call
		 *
		 * @param int               Minimum number of posts needed in each API request
		 * @param array $settings   Settings for this feed
		 *
		 * @since 2.0/5.0
		 */
		$num = apply_filters( 'sbi_num_in_request', $settings['minnum'], $settings );
		$num = max( $num, (int)$settings['apinum'] );
		$params = array(
			'num' => $num
		);

		$one_successful_connection = false;
		$one_post_found = false;
		$next_page_found = false;
		$one_api_request_delayed = false;

		foreach ( $feed_types_and_terms as $type => $terms ) {
			if ( is_array( $terms ) && count( $terms ) > 5 ) {
				shuffle( $terms );
			}
			foreach ( $terms as $term_and_params ) {

				if ( isset( $term_and_params['one_time_request'] ) ) {
					$params['num'] = 13;
				}

				$term = $term_and_params['term'];
				$params = array_merge( $params, $term_and_params['params'] );
				if ( ! isset( $term_and_params['error'] )
					 && (! isset( $next_pages[ $term . '_' . $type ] ) || $next_pages[ $term . '_' . $type ] !== false) ) {
					$connected_account_for_term = isset( $connected_accounts_for_feed[ $term ] ) ? $connected_accounts_for_feed[ $term ] : array();
					$account_type = isset( $connected_account_for_term['type'] ) ? $connected_account_for_term['type'] : 'personal';

					// basic account access tokens need to be refreshed every 60 days
					// normally done using WP Cron but can be done here as a fail safe
					if ( $account_type === 'basic' ) {
						if ( SB_Instagram_Token_Refresher::refresh_time_has_passed_threshold( $connected_account_for_term )
							 && SB_Instagram_Token_Refresher::minimum_time_interval_since_last_attempt_has_passed( $connected_account_for_term ) ) {
							$refresher = new SB_Instagram_Token_Refresher( $connected_account_for_term );
							$refresher->attempt_token_refresh();
							if ( $refresher->get_last_error_code() === 10 ) {
								sbi_update_connected_account( $connected_accounts_for_feed[ $term ]['user_id'], array( 'private' => true ) );
								$this->add_report( 'token needs refreshing ' . $term . '_' . $type );
							} else {
								$this->add_report( 'trying to refresh token ' . $term . '_' . $type );
							}
						}

					}

					if ( ! empty( $next_pages[ $term . '_' . $type ] ) ) {
						$next_page_term = $next_pages[ $term . '_' . $type ];
						if ( strpos( $next_page_term, 'https://' ) !== false ) {
							$connection = $this->make_api_connection( $next_page_term );
						} else {
							$params['cursor'] = $next_page_term;
							$connection = $this->make_api_connection( $connected_account_for_term, $type, $params );
						}
					} else {
						$connection = $this->make_api_connection( $connected_account_for_term, $type, $params );
					}
					$this->add_report( 'api call made for ' . $term . ' - ' . $type );

					$connection->connect();
					$this->num_api_calls++;

					if ( ! $connection->has_encryption_error() && ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
						$one_successful_connection = true;

						if ( $type === 'hashtags_top' ) {
							SB_Instagram_Posts_Manager::maybe_update_list_of_top_hashtags( $term_and_params['hashtag_name'] );
						}

						$sb_instagram_posts_manager->remove_error( 'connection', $connected_account_for_term );

						$data = $connection->get_data();

						if ( !$connected_account_for_term['is_valid'] ) {
							$this->add_report( 'clearing invalid token' );
							$this->clear_expired_access_token_notice( $connected_account_for_term );
						}

						if ( isset( $data[0]['id'] ) ) {
							$one_post_found = true;

							$post_set = $this->filter_posts( $data, $settings );
							$post_set['term'] = $this->get_account_term( $term_and_params );
							$new_post_sets[] = $post_set;
						}

						$next_page = $connection->get_next_page( $type );
						if ( ! empty( $next_page ) ) {
							$next_pages[ $term . '_' . $type ] = $next_page;
							$next_page_found = true;
						} else {
							$next_pages[ $term . '_' . $type ] = false;
						}

						// One time requests are broken into smaller API requests
						// to avoid an API error "1" due to too much data

						if ( isset( $term_and_params['one_time_request'] ) && ! empty( $next_pages[ $term . '_' . $type ] ) ) {
							for ( $k = 1; $k <= 3; $k++ ) {

								if ( ! empty( $next_pages[ $term . '_' . $type ] ) ) {
									$next_page_term = $next_pages[ $term . '_' . $type ];
									if ( strpos( $next_page_term, 'https://' ) !== false ) {
										$additional_connection = $this->make_api_connection( $next_page_term );
									} else {
										$params['cursor'] = $next_page_term;
										$additional_connection = $this->make_api_connection( $connected_account_for_term, $type, $params );
									}
									$additional_connection->connect();
								}

								if ( isset( $additional_connection )
									 && ! $additional_connection->is_wp_error()
									 && ! $additional_connection->is_instagram_error() ) {
									$additional_data = $additional_connection->get_data();

									if ( isset( $additional_data[0]['id'] ) ) {
										$one_post_found = true;

										$post_set         = $this->filter_posts( $additional_data, $settings );
										$post_set['term'] = $this->get_account_term( $term_and_params );
										$new_post_sets[]  = $post_set;

										$this->add_report( 'additional posts sets found in loop ' . $k );

									}

									$next_page = $additional_connection->get_next_page( $type );
									if ( ! empty( $next_page ) ) {
										$next_pages[ $term . '_' . $type ] = $next_page;
										$next_page_found                   = true;
									} else {
										$next_pages[ $term . '_' . $type ] = false;
									}
								}
							}
						}
					} else {

						if ( $this->can_try_another_request( $type, $connected_accounts_for_feed[ $term ] ) ) {
							$this->add_report( 'trying other accounts' );
							$i = 0;
							$attempted = array( $connected_accounts_for_feed[ $term ]['access_token'] );
							$success = false;
							$different = true;
							$error = false;

							while ( $different
									&& ! $success
									&& $this->can_try_another_request( $type, $connected_accounts_for_feed[ $term ], $i ) ) {
								$different = $this->get_different_connected_account( $type, $attempted );
								$this->add_report( 'trying the account ' . $different['user_id'] );

								if ( $different ) {
									$connected_accounts_for_feed[ $term ] = $this->get_different_connected_account( $type, $attempted );
									$attempted[] = $connected_accounts_for_feed[ $term ]['user_id'];

									if ( ! empty( $next_pages[ $term . '_' . $type ] ) ) {
										$new_connection = $this->make_api_connection( $next_pages[ $term . '_' . $type ] );
									} else {
										$new_connection = $this->make_api_connection( $connected_accounts_for_feed[ $term ], $type, $params );
									}

									$this->num_api_calls++;
									if ( ! $new_connection->is_wp_error() && ! $new_connection->is_instagram_error() ) {

										$one_successful_connection = true;
										$success = true;
										$sb_instagram_posts_manager->maybe_remove_display_error( 'hashtag_limit' );

										$data = $new_connection->get_data();
										if ( isset( $data[0]['id'] ) ) {
											$one_post_found = true;
											$post_set = $this->filter_posts( $data, $settings );
											$post_set['term'] = $this->get_account_term( $term_and_params );

											$new_post_sets[] = $post_set;
										}
										$next_page = $new_connection->get_next_page( $type );
										if ( ! empty( $next_page ) ) {
											$next_pages[ $term . '_' . $type ] = $next_page;
											$next_page_found = true;
										} else {
											$next_pages[ $term . '_' . $type ] = false;
										}
									} else {
										if ( $new_connection->is_wp_error() ) {
											$error = $new_connection->get_wp_error();
										} else {
											$error = $new_connection->get_data();
										}
									}
									$i++;
								} else {
									$error = $connection->get_data();
								}
							}

							if ( ! $success && $error ) {

								if ( $connection->is_wp_error() ) {
									SB_Instagram_API_Connect::handle_wp_remote_get_error( $error );
								} else {
									SB_Instagram_API_Connect::handle_instagram_error( $error, $connected_accounts_for_feed[ $term ], $type );
								}
								$next_pages[ $term . '_' . $type ] = false;

							}
						} else {
							if ( $connection->is_wp_error() ) {
								SB_Instagram_API_Connect::handle_wp_remote_get_error( $connection->get_wp_error() );
							} elseif ( $connection->has_encryption_error() ) {
								$error = array(
									'error' => array(
										'code'    => '999',
										'message' => __( 'Your access token could not be decrypted on this website. Reconnect this account or go to our website to learn how to prevent this.', 'instagram-feed' )
									)
								);
								SB_Instagram_API_Connect::handle_instagram_error( $error, $connected_accounts_for_feed[ $term ], $type );
							} else {
								SB_Instagram_API_Connect::handle_instagram_error( $connection->get_data(), $connected_accounts_for_feed[ $term ], $type );
							}

							$next_pages[ $term . '_' . $type ] = false;
						}
					}
				}

			}
		}

		if ( ! $one_successful_connection || ($one_api_request_delayed && empty( $new_post_sets )) ) {
			$this->should_use_backup = true;
		}
		$posts = $this->merge_posts( $new_post_sets, $settings );

		if ( ! $this->should_merge_after( $settings ) ) {
			if ( ! empty( $this->post_data ) && is_array( $this->post_data ) ) {
				$posts = array_merge( $this->post_data, $posts );
			}
		}

		$posts = $this->sort_posts( $posts, $settings );

		if ( $this->should_merge_after( $settings ) ) {
			if ( ! empty( $this->post_data ) && is_array( $this->post_data ) ) {
				$posts = array_merge( $this->post_data, $posts );
			}
		}

		if ( $one_post_found ) {
			$this->one_post_found = true;
		}

		$this->post_data = $posts;

		if ( isset( $next_page_found ) && $next_page_found ) {
			$this->next_pages = $next_pages;
		} else {
			$this->next_pages = false;
		}
	}

	/**
	 * Connects to the Instagram API and records returned data
	 *
	 * @param $settings
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @since 2.0/5.0
	 * @since 2.2/5.3 added logic to append bio data from the related
	 *  connected account if not available in the API response
	 */
	public function set_remote_header_data( $settings, $feed_types_and_terms, $connected_accounts_for_feed ) {
		$first_user = $this->get_first_user( $feed_types_and_terms );
		if ( ! empty( $settings['headersource'] ) ) {
			foreach ( $connected_accounts_for_feed as $connected_account ) {
				if ( $connected_account['username'] === $settings['headersource'] ) {
					$first_user = $connected_account['user_id'];
				} elseif ( $connected_account['user_id'] === $settings['headersource'] ) {
					$first_user = $connected_account['user_id'];
				}
			}
		}
		$this->header_data = false;
		global $sb_instagram_posts_manager;

		$api_requests_delayed = isset( $connected_accounts_for_feed[ $first_user ] ) ? $sb_instagram_posts_manager->are_current_api_request_delays( $connected_accounts_for_feed[ $first_user ] ) : false;

		if ( isset( $connected_accounts_for_feed[ $first_user ] ) && ! $api_requests_delayed ) {
			$connection = new SB_Instagram_API_Connect( $connected_accounts_for_feed[ $first_user ], 'header', array() );

			$connection->connect();

			if ( ! $connection->has_encryption_error() && ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
				$this->header_data = $connection->get_data();
				$this->header_data['local_avatar'] = false;
				$sb_instagram_posts_manager->remove_error( 'connection', $connected_accounts_for_feed[ $first_user ] );

				if ( ! empty( $connected_accounts_for_feed[ $first_user ]['local_avatar_url'] ) ) {
					$this->header_data['local_avatar'] = $connected_accounts_for_feed[ $first_user ]['local_avatar_url'];
				}
				if ( empty( $this->header_data['bio'] )
					 && isset( $connected_accounts_for_feed[ $first_user ]['bio'] ) ) {

					$this->header_data['bio'] = sbi_decode_emoji( $connected_accounts_for_feed[ $first_user ]['bio'] );
				}
			} else {
				$this->should_use_backup = true;

				if ( $connection->is_wp_error() ) {
					SB_Instagram_API_Connect::handle_wp_remote_get_error( $connection->get_wp_error() );
				} else {
					SB_Instagram_API_Connect::handle_instagram_error( $connection->get_data(), $connected_accounts_for_feed[ $first_user ], 'header' );
				}
			}
		}
	}

	/**
	 * Stores feed data in a transient for a specified time
	 *
	 * @param int $cache_time
	 * @param bool $save_backup
	 * @param bool $force_cache
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 duplicate posts removed
	 */
	public function cache_feed_data( $cache_time, $save_backup = true, $force_cache = false ) {
		if ( ! empty( $this->post_data ) || ! empty( $this->next_pages ) || ! empty( $this->cached_feed_error ) || $force_cache ) {
			$this->remove_duplicate_posts();
			$this->trim_posts_to_max();

			$to_cache = array(
				'data' => $this->post_data,
				'pagination' => $this->next_pages,
				'pages_created' => $this->pages_created
			);

			global $sb_instagram_posts_manager;

			$error_messages = $sb_instagram_posts_manager->get_frontend_errors();

			if ( ! empty( $error_messages ) ) {
				$to_cache['errors'] = $error_messages;
			}

			$this->cache->update_or_insert( 'posts', sbi_json_encode( $to_cache ) );

			if ( $save_backup ) {
				if ( isset( $to_cache['errors'] ) ) {
					unset( $to_cache['errors'] );
				}
				$this->cache->update_or_insert( 'posts_backup', sbi_json_encode( $to_cache ) );
			}

		} else {
			$this->add_report( 'no data not caching' );
		}
	}

	/**
	 * Stores feed data with additional data specifically for cron caching
	 *
	 * @param array $to_cache feed data with additional things like the shortcode
	 *  settings, when the cache was last requested, when new posts were last retrieved
	 * @param int $cache_time how long the cache will last
	 * @param bool $save_backup whether or not to also save this as a permanent cache
	 *
	 * @since 2.0/5.0
	 * @since 2.0/5.1 duplicate posts removed, cache set trimmed to a maximum
	 */
	public function set_cron_cache( $to_cache, $cache_time, $save_backup = true ) {
		if ( ! empty( $this->post_data )
			 || ! empty( $this->next_pages )
			 || ! empty( $to_cache['data'] )
			 || $this->should_cache_error() ) {
			$this->remove_duplicate_posts();
			$this->trim_posts_to_max();

			$to_cache['data'] = isset( $to_cache['data'] ) ? $to_cache['data'] : $this->post_data;
			$to_cache['pagination'] = isset( $to_cache['next_pages'] ) ? $to_cache['next_pages'] : $this->next_pages;
			$to_cache['atts'] = isset( $to_cache['atts'] ) ? $to_cache['atts'] : $this->transient_atts;
			$to_cache['last_requested'] = isset( $to_cache['last_requested'] ) ? $to_cache['last_requested'] : time();
			$to_cache['last_retrieve'] = isset( $to_cache['last_retrieve'] ) ? $to_cache['last_retrieve'] : $this->last_retrieve;

			global $sb_instagram_posts_manager;

			$error_messages = $sb_instagram_posts_manager->get_frontend_errors();

			if ( ! empty( $error_messages ) ) {
				$to_cache['errors'] = $error_messages;
			} else {
				$to_cache['errors'] = array();
			}

			$this->cache->update_or_insert( 'posts', sbi_json_encode( $to_cache ) );

			if ( $save_backup ) {
				if ( ! empty( $this->post_data )
					 || ! empty( $this->next_pages )
					 || ! empty( $to_cache['data'] ) ) {
					if ( isset( $to_cache['errors'] ) ) {
						unset( $to_cache['errors'] );
					}
					$this->cache->update_or_insert( 'posts_backup', sbi_json_encode( $to_cache ) );
				}
			}
		} else {
			$this->add_report( 'no data not caching' );
		}

	}

	/**
	 * Stores header data for a specified time as a transient
	 *
	 * @param int $cache_time
	 * @param bool $save_backup
	 *
	 * @since 2.0/5.0
	 */
	public function cache_header_data( $cache_time, $save_backup = true ) {
		if ( $this->header_data ) {
			$this->cache->update_or_insert( 'header', sbi_json_encode( $this->header_data ) );

			if ( $save_backup ) {
				if ( isset( $this->header_data['errors'] ) ) {
					unset( $this->header_data['errors'] );
				}
				$this->cache->update_or_insert( 'header_backup', sbi_json_encode( $this->header_data ) );
			}
		}
	}

	/**
	 * Used to randomly trigger an updating of the last requested data for cron caching
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_update_last_requested() {
		return (rand( 1, 20 ) === 20);
	}

	/**
	 * Determines if pagination can and should be used based on settings and available feed data
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function should_use_pagination( $settings, $offset = 0 ) {
		if ( $settings['minnum'] < 1 ) {
			return false;
		}
		$posts_available = count( $this->post_data ) - ($offset + $settings['num']);
		$show_loadmore_button_by_settings = ($settings['showbutton'] == 'on' || $settings['showbutton'] == 'true' || $settings['showbutton'] == true ) && $settings['showbutton'] !== 'false';

		if ( $show_loadmore_button_by_settings ) {
			// used for permanent and whitelist feeds
			if ( $this->feed_is_complete( $settings, $offset ) ) {
				$this->add_report( 'no pagination, feed complete' );
				return false;
			}
			if ( $posts_available > 0 ) {
				$this->add_report( 'do pagination, posts available' );
				return true;
			}
			$pages = $this->next_pages;

			if ( $pages && ! $this->should_use_backup() ) {
				foreach ( $pages as $page ) {
					if ( ! empty( $page ) ) {
						return true;
					}
				}
			}

		}


		$this->add_report( 'no pagination, no posts available' );

		return false;
	}

	/**
	 * Generates the HTML for the feed if post data is available. Although it seems
	 * some of the variables ar not used they are set here to hide where they
	 * come from when used in the feed templates.
	 *
	 * @param array $settings
	 * @param array $atts
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @return false|string
	 *
	 * @since 2.0/5.0
	 */
	public function get_the_feed_html( $settings, $atts, $feed_types_and_terms, $connected_accounts_for_feed ) {
		global $sb_instagram_posts_manager;

		if ( empty( $this->post_data ) && ! empty( $connected_accounts_for_feed ) && $settings['minnum'] > 0 ) {
			$this->handle_no_posts_found( $settings, $feed_types_and_terms );
		}
		$posts = array_slice( $this->post_data, 0, $settings['minnum'] );
		$header_data = ! empty( $this->header_data ) ? $this->header_data : false;

		$first_user = ! empty( $feed_types_and_terms['users'][0] ) ? $feed_types_and_terms['users'][0]['term'] : false;
		$first_username = false;
		if ( $first_user ) {
			$first_username = isset( $connected_accounts_for_feed[ $first_user ]['username'] ) ? $connected_accounts_for_feed[ $first_user ]['username'] : $first_user;
		} elseif ( $header_data ) { // in case no connected account for feed
			$first_username = SB_Instagram_Parse::get_username( $header_data );
		} elseif ( isset( $feed_types_and_terms['users'] ) && isset( $this->post_data[0] ) ) { // in case no connected account and no header
			$first_username = SB_Instagram_Parse::get_username( $this->post_data[0] );
		}
		$use_pagination = $this->should_use_pagination( $settings, 0 );

		$feed_id = $this->regular_feed_transient_name;
		$shortcode_atts = ! empty( $atts ) ? sbi_json_encode( $atts ) : '{}';

		$settings['header_outside'] = false;
		$settings['header_inside'] = false;
		if ( $header_data && $settings['showheader'] ) {
			$settings['header_inside'] = true;
		}

		$other_atts = '';

		$additional_classes = $this->get_feed_container_css_classes( $settings );


		$other_atts .= ' data-postid="' . esc_attr( get_the_ID() ) . '"';
		$other_atts .= ' data-locatornonce="' . esc_attr( wp_create_nonce( 'sbi-locator-nonce-' . get_the_ID() . '-' . $this->regular_feed_transient_name ) ) . '"';

		$other_atts = $this->add_other_atts( $other_atts, $settings );

		$flags = array();

		if ( $sb_instagram_posts_manager->image_resizing_disabled( $feed_types_and_terms ) || $settings['isgutenberg'] ) {
			$flags[] = 'resizeDisable';
		} elseif ( $settings['favor_local'] ) {
			$flags[] = 'favorLocal';
		}

		if ( $settings['disable_js_image_loading'] ) {
			$flags[] = 'imageLoadDisable';
		}
		if ( $settings['ajax_post_load'] ) {
			$flags[] = 'ajaxPostLoad';
		}
		if ( SB_Instagram_GDPR_Integrations::doing_gdpr( $settings ) ) {
			$flags[] = 'gdpr';
			if ( ! SB_Instagram_GDPR_Integrations::blocking_cdn( $settings ) ) {
				$flags[] = 'overrideBlockCDN';
			}
		}
		if ( ! $settings['isgutenberg']
			 && SB_Instagram_Feed_Locator::should_do_ajax_locating( $this->regular_feed_transient_name, get_the_ID() ) ) {
			$this->add_report( 'doing feed locating' );
			$flags[] = 'locator';
		}
		if ( isset( $_GET['sbi_debug'] ) || isset( $_GET['sb_debug'] ) ) {
			$flags[] = 'debug';
		}

		$flags = apply_filters( 'sbi_flags', $flags, $settings );

		if ( ! empty( $flags ) ) {
			$other_atts .= ' data-sbi-flags="' . implode(',', $flags ) . '"';
		}

		if ( $settings['customizer'] ) {
			$settings['vue_args'] = [
				'condition' => ' && $parent.valueIsEnabled($parent.customizerFeedData.settings.headeroutside)'
			];
		}

		ob_start();
		include sbi_get_feed_template_part( 'feed', $settings );
		$html = ob_get_contents();
		ob_get_clean();

		if ( $settings['ajaxtheme'] ) {
			$html .= $this->get_ajax_page_load_html();
		}


		return $html;
	}

	/**
	 * Generates HTML for individual sbi_item elements
	 *
	 * @param array $settings
	 * @param int $offset
	 * @param array $feed_types_and_terms organized settings related to feed data
	 *  (ex. 'user' => array( 'smashballoon', 'custominstagramfeed' )
	 * @param array $connected_accounts_for_feed connected account data for the
	 *  feed types and terms
	 *
	 * @return false|string
	 *
	 * @since 2.0/5.0
	 */
	public function get_the_items_html( $settings, $offset, $feed_types_and_terms, $connected_accounts_for_feed ) {
		if ( empty( $this->post_data ) ) {
			ob_start();
			$html = ob_get_contents();
			ob_get_clean();		?>
			<p><?php _e( 'No posts found.', 'instagram-feed' ); ?></p>
			<?php
			$html = ob_get_contents();
			ob_get_clean();
			return $html;
		}

		$posts = array_slice( $this->post_data, $offset, $settings['num'] );

		ob_start();

		$this->posts_loop( $posts, $settings, $offset );

		$html = ob_get_contents();
		ob_get_clean();

		return $html;
	}

	/**
	 * Overwritten in the Pro version
	 *
	 * @return object
	 */
	public function make_api_connection( $connected_account_or_page, $type = NULL, $params = NULL ) {
		return new SB_Instagram_API_Connect( $connected_account_or_page, $type, $params );
	}

	/**
	 * When the feed is loaded with AJAX, the JavaScript for the plugin
	 * needs to be triggered again. This function is a workaround that adds
	 * the file and settings to the page whenever the feed is generated.
	 *
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public static function get_ajax_page_load_html() {
		if ( SB_Instagram_Blocks::is_gb_editor() ) {
			return '';
		}
		$sbi_options = sbi_get_database_settings();
		$font_method = 'svg';
		$upload = wp_upload_dir();
		$resized_url = trailingslashit( $upload['baseurl'] ) . trailingslashit( SBI_UPLOADS_NAME );

		$js_options = array(
			'font_method' => $font_method,
			'placeholder' => trailingslashit( SBI_PLUGIN_URL ) . 'img/placeholder.png',
			'resized_url' => $resized_url,
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
		);

		$encoded_options = sbi_json_encode( $js_options );

		$js_option_html = '<script type="text/javascript">var sb_instagram_js_options = ' . $encoded_options . ';</script>';
		$js_option_html .= "<script type='text/javascript' src='" . trailingslashit( SBI_PLUGIN_URL ) . 'js/sbi-scripts.min.js?ver=' . SBIVER . "'></script>";

		return $js_option_html;
	}

	/**
	 * Overwritten in the Pro version
	 *
	 * @param $feed_types_and_terms
	 *
	 * @return string
	 *
	 * @since 2.1/5.2
	 */
	public function get_first_user( $feed_types_and_terms ) {
		if ( isset( $feed_types_and_terms['users'][0] ) ) {
			return $feed_types_and_terms['users'][0]['term'];
		} if ( isset( $feed_types_and_terms['tagged'][0] ) ) {
			return $feed_types_and_terms['tagged'][0]['term'];
		} else {
			return '';
		}
	}

	/**
	 * Adds recorded strings to an array
	 *
	 * @param $to_add
	 *
	 * @since 2.0/5.0
	 */
	public function add_report( $to_add ) {
		$this->report[] = $to_add;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	public function get_report() {
		return $this->report;
	}

	/**
	 * Additional options/settings added to the main div
	 * for the feed
	 *
	 * Overwritten in the Pro version
	 *
	 * @param $other_atts
	 * @param $settings
	 *
	 * @return string
	 */
	protected function add_other_atts( $other_atts, $settings ) {
		return $other_atts;
	}

	/**
	 * Used for filtering a single API request worth of posts
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $post_set a single set of post data from the api
	 *
	 * @return mixed|array
	 *
	 * @since 2.0/5.0
	 */
	protected function filter_posts( $post_set, $settings = array() ) {
		// array_unique( $post_set, SORT_REGULAR);

		if ( $settings['media'] === 'all' ) {
			return $post_set;
		}

		$media_filter = $settings['media'] !== 'all' ? $settings['media'] : false;
		if ( $media_filter ) {
			$media_filter = is_array( $media_filter ) ? $media_filter : array( $media_filter );
		}
		$video_types    = ! empty( $settings['videotypes'] ) ? explode( ',', str_replace( ' ', '', strtolower( $settings['videotypes'] ) ) ) : array( 'igtv', 'regular', 'reels' );
		$filtered_posts = array();
		foreach ( $post_set as $post ) {
			$keep_post = false;
			$is_hidden = false;
			$passes_media_filter = true;

			if ( $media_filter ) {
				$media_type = SB_Instagram_Parse::get_media_type( $post );

				if ( $media_type === 'video' && in_array( 'videos', $media_filter, true ) ) {
					if ( ! empty( $video_types ) ) {
						$video_type = SB_Instagram_Parse::get_media_product_type( $post );
						$video_type = 'feed' === $video_type ? 'regular' : $video_type;

						if ( ! in_array( $video_type, $video_types, true ) ) {
							$passes_media_filter = false;
						}
					}
				} elseif ( $media_type === 'video' && ! in_array( 'videos', $media_filter, true ) ) {
					$passes_media_filter = false;
				} elseif ( $media_type === 'image' && ! in_array( 'photos', $media_filter, true ) ) {
					$passes_media_filter = false;
				} elseif ( $media_type === 'carousel' && ! in_array( 'photos', $media_filter, true ) ) {
					$passes_media_filter = false;
				}

			}

			if ( ! $is_hidden && $passes_media_filter ) {
				$keep_post = true;
			}

			$keep_post = apply_filters( 'sbi_passes_filter', $keep_post, $post, $settings );
			if ( $keep_post ) {
				$filtered_posts[] = $post;
			}
		}

		return $filtered_posts;
	}

	protected function handle_no_posts_found( $settings = array(), $feed_types_and_terms = array() ) {
		global $sb_instagram_posts_manager;

		$error_message_return = array(
			'error_message' => __( 'Error: No posts found.', 'instagram-feed' ),
			'admin_only' => __( 'Make sure this account has posts available on instagram.com.', 'instagram-feed' ),
			'frontend_directions' => '<a href="https://smashballoon.com/instagram-feed/docs/errors/">' . __( 'Click here to troubleshoot', 'instagram-feed' ) . '</a>',
			'backend_directions' => '<a href="https://smashballoon.com/instagram-feed/docs/errors/">' . __( 'Click here to troubleshoot', 'instagram-feed' ) . '</a>'
		);
		$sb_instagram_posts_manager->maybe_set_display_error( 'configuration', $error_message_return );
	}

	protected function remove_duplicate_posts() {
		$posts = $this->post_data;
		$ids_in_feed = array();
		$non_duplicate_posts = array();
		$removed = array();

		foreach ( $posts as $post ) {
			$post_id = SB_Instagram_Parse::get_post_id( $post );
			if ( ! in_array( $post_id, $ids_in_feed, true ) ) {
				$ids_in_feed[] = $post_id;
				$non_duplicate_posts[] = $post;
			} else {
				$removed[] = $post_id;
			}
		}

		$this->add_report( 'removed duplicates: ' . implode(', ', $removed ) );
		$this->set_post_data( $non_duplicate_posts );
	}

	/**
	 * Used for limiting the cache size
	 *
	 * @since 2.0/5.1.1
	 */
	protected function trim_posts_to_max() {
		if ( ! is_array( $this->post_data ) ) {
			return;
		}

		$max = apply_filters( 'sbi_max_cache_size', 500 );
		$this->set_post_data( array_slice( $this->post_data , 0, $max ) );

	}

	/**
	 * Used for permanent feeds or white list feeds to
	 * stop pagination if all posts are already added
	 *
	 * Overwritten in the Pro version
	 *
	 * @param array $settings
	 * @param int $offset
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	protected function feed_is_complete( $settings, $offset = 0 ) {
		return false;
	}

	/**
	 * @param $connected_account_for_term
	 *
	 * @since 2.0/5.1.2
	 */
	private function clear_expired_access_token_notice( $connected_account_for_term ) {
		InstagramFeed\Builder\SBI_Source::clear_error( $connected_account_for_term['user_id'] );
	}

	/**
	 * Iterates through post data and tracks the index of the current post.
	 * The actual post ids of the posts are stored in an array so the plugin
	 * can search for local images that may be available.
	 *
	 * @param array $posts final filtered post data for the feed
	 * @param array $settings
	 * @param int $offset
	 *
	 * @since 2.0/5.0
	 */
	private function posts_loop( $posts, $settings, $offset = 0 ) {

		$image_ids = array();
		$post_index = $offset;
		$icon_type = 'svg';
		$resized_images = $this->get_resized_images();

		foreach ( $posts as $post ) {
			$image_ids[] = SB_Instagram_Parse::get_post_id( $post );
			$account_type = SB_Instagram_Parse::get_account_type( $post );
			include sbi_get_feed_template_part( 'item', $settings );
			$post_index++;
		}

		$this->image_ids_post_set = $image_ids;
	}

	private function get_account_term( $term_and_params ) {

		if ( isset( $term_and_params['hashtag_name'] ) ) {
			return '#' . $term_and_params['hashtag_name'];
		} else {
			return '';
		}
	}

	/**
	 * Uses array of API request results and merges them based on how
	 * the feed should be sorted. Mixed feeds are always sorted alternating
	 * since there is no post date for hashtag feeds.
	 *
	 *
	 * @param array $post_sets an array of single API request worth
	 *  of posts
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 2.0/5.0
	 */
	private function merge_posts( $post_sets, $settings ) {

		$merged_posts = array();
		if ( $settings['sortby'] === 'alternate'
			 || $settings['sortby'] === 'api' && isset( $post_sets[1] ) ) {
			// don't bother merging posts if there is only one post set
			if ( isset( $post_sets[1] ) ) {
				$min_cycles = $settings['sortby'] === 'api' ? min( 200 / count( $post_sets ) + 5, 50 ) : max( 1, (int)$settings['minnum'] );
				$terms = array();
				for( $i = 0; $i <= $min_cycles; $i++ ) {
					$ii = 0;
					foreach ( $post_sets as $post_set ) {
						if ( isset( $post_sets[ $ii ]['term'] ) ) {
							$term = $post_sets[ $ii ]['term'];
							unset( $post_sets[ $ii ]['term'] );
							if ( ! isset( $terms[ $ii ] ) ) {
								$terms[ $ii ] = $term;
								//
							}
							if ( strpos( $term, '#' ) !== false ) {
								$post_index = 0;
								foreach ( $post_sets[ $ii ] as $post ) {
									$post_sets[ $ii ][ $post_index ]['term'] = $term;
									$post_index++;
								}
							}
						}
						if ( isset( $post_set[ $i ] ) && isset( $post_set[ $i ]['id'] ) ) {
							$post_set[ $i ]['term'] = $terms[ $ii ];
							$merged_posts[] = $post_set[ $i ];
						}
						$ii++;
					}
				}
			} else {
				if ( isset( $post_sets[0]['term'] ) ) {
					$term = $post_sets[0]['term'];

					unset( $post_sets[0]['term'] );
					if ( strpos( $term, '#' ) !== false ) {
						$post_index = 0;
						foreach ( $post_sets[0] as $post ) {
							$post_sets[0][ $post_index ]['term'] = $term;
							$post_index++;
						}
					}
				}
				$merged_posts = isset( $post_sets[0] ) ? $post_sets[0] : array();
			}
		} elseif ( $settings['sortby'] === 'api' ) {
			if ( isset( $post_sets[0] ) ) {
				if ( isset( $post_sets[0]['term'] ) ) {
					$term = $post_sets[0]['term'];
					unset( $post_sets[0]['term'] );
					if ( strpos( $term, '#' ) !== false ) {
						$post_index = 0;
						foreach ( $post_sets[0] as $post ) {
							$post_sets[0][ $post_index ]['term'] = $term;
							$post_index++;
						}
					}
				}
				$post_set_index = 0;
				foreach ( $post_sets as $post_set ) {
					if ( isset( $post_sets[ $post_set_index ]['term'] ) ) {
						$term = $post_sets[ $post_set_index ]['term'];
						unset( $post_sets[ $post_set_index ]['term'] );
						if ( strpos( $term, '#' ) !== false ) {
							$post_index = 0;
							foreach ( $post_sets[0] as $post ) {
								$post_sets[ $post_set_index ][ $post_index ]['term'] = $term;
								$post_index++;
							}
						}
					}
					$merged_posts = array_merge( $merged_posts, $post_set );
					$post_set_index ++;
				}
			}
		} else {
			// don't bother merging posts if there is only one post set
			if ( isset( $post_sets[1] ) ) {

				$terms = array();
				$ii = 0;
				foreach ( $post_sets as $post_set ) {
					if ( isset( $post_set[0]['id'] ) ) {
						if ( isset( $post_sets[ $ii ]['term'] ) ) {
							if ( ! isset( $terms[ $ii ] ) ) {
								$terms[ $ii ] = $post_set['term'];
							}
							unset( $post_sets[ $ii ]['term'] );
							$iii = 0;
							foreach ( $post_sets[ $ii ] as $post ) {
								$post_sets[ $ii ][ $iii ]['term'] = $terms[ $ii ];
								$iii++;
							}
						}
						$merged_posts = array_merge( $merged_posts, $post_sets[ $ii ] );
						$ii++;
					}
				}
			} else {
				if ( isset( $post_sets[0]['term'] ) ) {
					$term = $post_sets[0]['term'];
					unset( $post_sets[0]['term'] );
					if ( strpos( $term, '#' ) !== false ) {
						$post_index = 0;
						foreach ( $post_sets[0] as $post ) {
							$post_sets[0][ $post_index ]['term'] = $term;
							$post_index++;
						}
					}
				}
				$merged_posts = isset( $post_sets[0] ) ? $post_sets[0] : array();
			}
		}

		if ( isset( $merged_posts['term'] ) ) {
			unset( $merged_posts['term'] );
		}

		return $merged_posts;
	}

	/**
	 * Sorting by date will be more accurate for multi-term
	 * feeds if posts are merged before sorting.
	 *
	 * @param array $settings
	 *
	 * @return bool
	 *
	 * @since 5.10.1
	 */
	protected function should_merge_after( $settings ) {
		if ( ! isset( $settings['sortby'] ) ) {
			return false;
		}

		$merge_befores = array(
			'alternate',
			'api',
			'random',
			'likes'
		);

		if ( ! in_array( $settings['sortby'], $merge_befores, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sorts a post set based on sorting settings. Sorting by "alternate"
	 * is done when merging posts for efficiency's sake so the post set is
	 * just returned as it is.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param array $post_set
	 * @param array $settings
	 *
	 * @return mixed|array
	 *
	 * @since 2.0/5.0
	 * @since 2.1/5.2 added filter hook for applying custom sorting
	 */
	protected function sort_posts( $post_set, $settings ) {
		if ( empty( $post_set ) ) {
			return $post_set;
		}

		// sorting done with "merge_posts" to be more efficient
		if ( $settings['sortby'] === 'alternate' || $settings['sortby'] === 'api' ) {
			$return_post_set = $post_set;
		} elseif ( $settings['sortby'] === 'random' ) {
			/*
             * randomly selects posts in a random order. Cache saves posts
             * in this random order so paginating does not cause some posts to show up
             * twice or not at all
             */
			usort($post_set, 'sbi_rand_sort' );
			$return_post_set = $post_set;

		} else {
			// compares posted on dates of posts
			usort($post_set, 'sbi_date_sort' );
			$return_post_set = $post_set;
		}

		/**
		 * Apply a custom sorting of posts
		 *
		 * @param array $return_post_set    Ordered set of filtered posts
		 * @param array $settings           Settings for this feed
		 *
		 * @since 2.1/5.2
		 */

		return apply_filters( 'sbi_sorted_posts', $return_post_set, $settings );
	}

	/**
	 * Can trigger a second attempt at getting posts from the API
	 *
	 * Overwritten in the Pro version
	 *
	 * @param string $type
	 * @param array $connected_account_with_error
	 * @param int $attempts
	 *
	 * @return bool
	 *
	 * @since 2.0/5.1.1
	 */
	protected function can_try_another_request( $type, $connected_account_with_error, $attempts = 0 ) {
		return false;
	}

	/**
	 * returns a second connected account if it exists
	 *
	 * Overwritten in the Pro version
	 *
	 * @param string $type
	 * @param array $attempted_connected_accounts
	 *
	 * @return bool
	 *
	 * @since 2.0/5.1.1
	 */
	protected function get_different_connected_account( $type, $attempted_connected_accounts ) {
		return false;
	}


	/**
	 * Generates The Feed Container CSS classes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 6.0
	 */

	function get_feed_container_css_classes( $settings ){
		$customizer = $settings['customizer'];
		if($customizer){


		}else{
			$classes = array();
			if ( empty( $settings['widthresp'] ) || $settings['widthresp'] == 'on' || $settings['widthresp'] == 'true' || $settings['widthresp'] === true ) {
				if ( $settings['widthresp'] !== 'false' ) {
					$classes[] = 'sbi_width_resp';
				}
			}
			if ( ! empty( $settings['class'] ) ) {
				$classes[] = esc_attr( $settings['class'] );
			}
			if ( ! empty( $settings['height'] )
				 && (((int)$settings['height'] < 100 && $settings['heightunit'] === '%') || $settings['heightunit'] === 'px') ) {
				$classes[] = 'sbi_fixed_height';
			}
			if ( ! empty( $settings['disablemobile'] )
				 && ($settings['disablemobile'] == 'on' || $settings['disablemobile'] == 'true' || $settings['disablemobile'] == true) ) {
				if ( $settings['disablemobile'] !== 'false' ) {
					$classes[] = 'sbi_disable_mobile';
				}
			}

			$additional_classes = '';
			if ( ! empty( $classes ) ) {
				$additional_classes = ' ' . implode( ' ', $classes );
			}
			return $additional_classes;
		}
		return '';
	}


}
