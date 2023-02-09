<?php
/**
 * Class SB_Instagram_Connected_Account
 *
 * Used for parsing data from connected accounts and getting
 * data related to an account using searches.
 *
 * @since 5.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Connected_Account {

	/**
	 * @var array
	 *
	 * @since 5.10
	 */
	public $account;

	public function __construct( $search_term_or_account, $search_type = 'user' ) {
		if ( is_array( $search_term_or_account ) ) {
			$this->account = $search_term_or_account;
		} else {
			$this->account = self::lookup( $search_term_or_account, $search_type );
		}
	}

	/**
	 * @return array
	 *
	 * @since 5.10
	 */
	public function get_account_data() {
		return $this->account;
	}

	/**
	 * Returns data for a connected account based on a search by term
	 * or type (business, user)
	 *
	 * @param $search_term string
	 * @param string $search_type string
	 *
	 * @return array|bool|mixed
	 *
	 * @since 5.10
	 */
	public static function lookup( $search_term, $search_type = 'user' ) {
		if ( is_array( $search_term ) ) {
			return false;
		}

		if ( $search_type === 'business' ) {
			if ( $search_term === '' ) {

				$args    = array(
					'all_businesses' => true,
				);
				$sources = \InstagramFeed\Builder\SBI_Db::source_query( $args );

				if ( empty( $sources ) ) {
					$sources = \InstagramFeed\Builder\SBI_Db::source_query();
				}

				$connected_accounts = \InstagramFeed\Builder\SBI_Source::convert_sources_to_connected_accounts( $sources );

				$business_accounts   = array();
				$access_tokens_found = array();
				foreach ( $connected_accounts as $connected_account ) {
					if ( isset( $connected_account['type'] )
					     && $connected_account['type'] === 'business'
					     && ! in_array( $connected_account['access_token'], $access_tokens_found, true ) ) {
						$business_accounts[]   = $connected_account;
						$access_tokens_found[] = $connected_account['access_token'];
					}
				}

				return $business_accounts;
			} else {
				$connected_accounts = self::get_all_connected_accounts();

				foreach ( $connected_accounts as $connected_account ) {
					if ( isset( $connected_account['type'] )
					     && $connected_account['type'] === 'business' ) {
						return $connected_account;
					}
				}
			}
		} else {
			$connected_accounts = self::get_all_connected_accounts();

			if ( isset( $connected_accounts[ $search_term ] ) ) {
				return $connected_accounts[ $search_term ];
			} else {
				foreach ( $connected_accounts as $connected_account ) {
					if ( strpos( $connected_account['access_token'], '.' ) === false ) {
						if ( strtolower( $connected_account['username'] ) === trim( strtolower( $search_term ) ) ) {
							return $connected_account;
						} elseif ( $connected_account['access_token'] === trim( strtolower( $search_term ) ) ) {
							return $connected_account;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Generates a local version of the avatar image file
	 * and stores related information for easy retrieval and
	 * management
	 *
	 * @param $username string
	 * @param $file_name string
	 *
	 * @return bool
	 *
	 * @since 5.10
	 */
	public static function create_local_avatar( $username, $file_name ) {
		if ( empty( $file_name ) ) {
			return false;
		}
		$options = sbi_get_database_settings();
		if ( ! $options['sb_instagram_disable_resize'] ) {

			$image_editor   = wp_get_image_editor( $file_name );
			$upload         = wp_upload_dir();
			$full_file_name = trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $username . '.jpg';

			if ( ! is_wp_error( $image_editor ) ) {

				$image_editor->set_quality( 80 );

				$image_editor->resize( 150, null );

				$saved_image = $image_editor->save( $full_file_name );

				if ( ! $saved_image ) {
					global $sb_instagram_posts_manager;

					$sb_instagram_posts_manager->add_error( 'image_editor', __( 'Error saving edited image.', 'instagram-feed' ) . ' ' . $full_file_name );
				} else {
					return true;
				}
			} else {
				if ( ! function_exists( 'download_url' ) ) {
					include_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$timeout_seconds = 5;

				// Download file to temp dir.
				$temp_file = download_url( $file_name, $timeout_seconds );
				if ( is_wp_error( $image_editor ) ) {
					return false;
				}

				$image_editor = wp_get_image_editor( $temp_file );

				global $sb_instagram_posts_manager;
				$details = __( 'Using backup editor method.', 'instagram-feed' ) . ' ' . $file_name;
				$sb_instagram_posts_manager->add_error( 'image_editor', $details );
				// not uncommon for the image editor to not work using it this way
				if ( ! is_wp_error( $image_editor ) ) {
					$image_editor->set_quality( 80 );

					$image_editor->resize( 150, null );

					$saved_image = $image_editor->save( $full_file_name );

					if ( ! $saved_image ) {
						global $sb_instagram_posts_manager;
						$details = __( 'Error saving edited image.', 'instagram-feed' ) . ' ' . $full_file_name;
						$sb_instagram_posts_manager->add_error( 'image_editor', $details );
					} else {
						return true;
					}
				} else {

					$message = __( 'Error editing image.', 'instagram-feed' );
					if ( isset( $image_editor ) && isset( $image_editor->errors ) ) {
						foreach ( $image_editor->errors as $key => $item ) {
							$message .= ' ' . $key . ' - ' . $item[0] . ' |';
						}
						if ( isset( $image_editor ) && isset( $image_editor->error_data ) ) {
							$message .= ' ' . sbi_json_encode( $image_editor->error_data ) . ' |';
						}
					}

					global $sb_instagram_posts_manager;
					$sb_instagram_posts_manager->add_error( 'image_editor', $message );
				}

				if ( ! empty( $temp_file ) ) {
					@unlink( $temp_file );
				}

			}
		}
		return false;
	}

	/**
	 * Whether or not we should attempt to create a local avatar
	 *
	 * @param string $username
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public static function should_create_local_avatar( $username ) {
		$options = sbi_get_database_settings();
		if ( ! $options['sb_instagram_disable_resize'] ) {
			$avatars = get_option( 'sbi_local_avatars', array() );

			return ! isset( $avatars[ $username ] ) || $avatars[ $username ] !== false;
		}
		return false;
	}

	/**
	 * Delete and update the status of a local avatar
	 *
	 * @param string $username
	 *
	 * @since 6.0
	 */
	public static function delete_local_avatar( $username ) {
		$upload = wp_upload_dir();

		$image_files = glob( trailingslashit( $upload['basedir'] ) . trailingslashit( SBI_UPLOADS_NAME ) . $username . '.jpg' ); // get all matching images
		foreach ( $image_files as $file ) { // iterate files
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}

		self::update_local_avatar_status( $username, 'unset' );
	}

	/**
	 * Whether or not the local avatar file exists
	 *
	 * @param string $username
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public static function local_avatar_exists( $username ) {
		$avatars = get_option( 'sbi_local_avatars', array() );

		return ! empty( $avatars[ $username ] );
	}

	/**
	 * Full URL to local avatar for username
	 *
	 * @param string $username
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public static function get_local_avatar_url( $username ) {
		$avatar_url = sbi_get_resized_uploads_url() . $username . '.jpg';

		return $avatar_url;
	}

	/**
	 * Store a record of which avatars have been created
	 *
	 * @param string $username
	 * @param string $status
	 *
	 * @since 6.0
	 */
	public static function update_local_avatar_status( $username, $status ) {
		$avatars = get_option( 'sbi_local_avatars', array() );
		if ( $status === 'unset' ) {
			if ( isset( $avatars[ $username ] ) ) {
				unset( $avatars[ $username ] );
			}
		} else {
			$avatars[ $username ] = $status;
		}

		update_option( 'sbi_local_avatars', $avatars );
	}

	/**
	 * If an avatar exists, return the URL otherwise try to create one.
	 * If we can't create one or it was not successful, return false.
	 *
	 * @param string $username
	 * @param string $profile_picture
	 *
	 * @return bool|string
	 *
	 * @since 6.0
	 */
	public static function maybe_local_avatar( $username, $profile_picture ) {
		if ( self::local_avatar_exists( $username ) ) {
			return self::get_local_avatar_url( $username );
		}

		if ( self::should_create_local_avatar( $username ) ) {
			$created = self::create_local_avatar( $username, $profile_picture );
			self::update_local_avatar_status( $username, $created );

			if ( $created ) {
				return self::get_local_avatar_url( $username );
			}
		}

		return false;
	}

	/**
	 * Encrypt string (access token) with an included initialization vector
	 *
	 * @param string $access_token
	 * @param string $initialization_vector
	 *
	 * @return string
	 *
	 * @since 5.12.4
	 */
	public static function encrypt_access_token( $access_token ) {
		$encryption = new SB_Instagram_Data_Encryption();

		return $encryption->encrypt( $access_token );
	}

	/**
	 * Attempt to decrypt access token
	 *
	 * @param string $access_token
	 * @param string $initialization_vector
	 *
	 * @return string
	 *
	 * @since 5.12.4
	 */
	public static function decrypt_access_token( $access_token ) {
		$encryption = new SB_Instagram_Data_Encryption();

		return $encryption->decrypt( $access_token );
	}

	/**
	 * Encrypt access tokens in a connected account and return it
	 *
	 * @param array $connected_account
	 *
	 * @return mixed
	 * @throws Exception
	 *
	 * @since 5.12.4
	 */
	public static function encrypt_connected_account_tokens( $connected_account ) {
		if ( ! self::decrypt_access_token( $connected_account['access_token'] ) ) {
			$encrypted_access_token            = self::encrypt_access_token( $connected_account['access_token'] );
			$connected_account['access_token'] = $encrypted_access_token;

			if ( isset( $connected_account['page_access_token'] ) ) {
				$encrypted_page_access_token = self::encrypt_access_token( $connected_account['page_access_token'] );

				$connected_account['page_access_token'] = $encrypted_page_access_token;
			}

			$connected_account['wp_user'] = get_current_user_id();
		}

		return $connected_account;
	}

	/**
	 * Encrypt all access tokens in all connected accounts. Used for
	 * a one-time update.
	 *
	 * @return array
	 * @throws Exception
	 *
	 * @since 5.12.4
	 */
	public static function encrypt_all_access_tokens() {
		$options            = sbi_get_database_settings();
		$connected_accounts = isset( $options['connected_accounts'] ) ? $options['connected_accounts'] : array();

		$updated = array();
		foreach ( $connected_accounts as $key => $connected_account ) {
			$updated[ $key ] = $connected_account;

			if ( ! self::decrypt_access_token( $connected_account['access_token'] ) ) {
				$encrypted_access_token          = self::encrypt_access_token( $connected_account['access_token'] );
				$updated[ $key ]['access_token'] = $encrypted_access_token;

				if ( isset( $connected_account['page_access_token'] ) ) {
					$encrypted_page_access_token = self::encrypt_access_token( $connected_account['page_access_token'] );

					$updated[ $key ]['page_access_token'] = $encrypted_page_access_token;
				}

				$updated[ $key ]['wp_user'] = get_current_user_id();
			}
		}

		$options['connected_accounts'] = $updated;

		update_option( 'sb_instagram_settings', $options );

		return $connected_accounts;
	}

	/**
	 * Returns all connected accounts
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public static function get_all_connected_accounts() {
		$i                  = 1;
		$empty_sources      = false;
		$connected_accounts = array();
		while ( ! $empty_sources && $i < 20 ) {
			$sources = \InstagramFeed\Builder\SBI_Db::source_query( array( 'page' => $i ) );

			if ( empty( $sources ) ) {
				$empty_sources = true;
			} else {
				$connected_accounts = array_merge( $connected_accounts, \InstagramFeed\Builder\SBI_Source::convert_sources_to_connected_accounts( $sources ) );
			}

			$i++;
		}

		return $connected_accounts;
	}

	/**
	 * Update an array of connected accounts
	 *
	 * @return array
	 *
	 * @since 5.14.4
	 */
	public static function update_connected_accounts( $connected_accounts ) {
		$options = sbi_get_database_settings();

		$options['connected_accounts'] = $connected_accounts;

		update_option( 'sb_instagram_settings', $options );

		return $connected_accounts;
	}
}
