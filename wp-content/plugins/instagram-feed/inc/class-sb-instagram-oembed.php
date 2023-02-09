<?php
/**
 * Class SB_Instagram_Oembed
 *
 * Replaces the native WordPress functionality for Instagram oembed
 * to allow authenticated oembeds
 *
 * @since 2.5/5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Oembed
{
	/**
	 * SB_Instagram_Oembed constructor.
	 *
	 * If an account has been connected, hooks are added
	 * to change how Instagram links are handled for oembeds
	 *
	 * @since 2.5/5.8
	 */
	public function __construct() {
		if ( SB_Instagram_Oembed::can_do_oembed() ) {
			if ( SB_Instagram_Oembed::can_check_for_old_oembeds() ) {
				add_action( 'the_post', array( 'SB_Instagram_Oembed', 'check_page_for_old_oembeds' ) );
			}
			add_filter( 'oembed_providers', array( 'SB_Instagram_Oembed', 'oembed_providers' ), 10, 1 );
			add_filter( 'oembed_fetch_url', array( 'SB_Instagram_Oembed', 'oembed_set_fetch_url' ), 10, 3 );
			add_filter( 'oembed_result', array( 'SB_Instagram_Oembed', 'oembed_result' ), 10, 3 );
		}
		if ( SB_Instagram_Oembed::should_extend_ttl() ) {
			add_filter( 'oembed_ttl', array( 'SB_Instagram_Oembed', 'oembed_ttl' ), 10, 4 );
		}
	}

	/**
	 * Check to make sure there is a connected account to
	 * enable authenticated oembeds
	 *
	 * @return bool
	 *
	 * @since 2.5/5.8
	 */
	public static function can_do_oembed() {
		$oembed_token_settings = get_option( 'sbi_oembed_token', array() );

		if ( isset( $oembed_token_settings['disabled'] ) && $oembed_token_settings['disabled'] ) {
			return false;
		}

		$access_token = SB_Instagram_Oembed::last_access_token();
		if ( ! $access_token ) {
			return false;
		}

		return true;
	}

	/**
	 * The "time to live" for Instagram oEmbeds is extended if the access token expires.
	 * Even if new oEmbeds will not use the Instagram Feed system due to an expired token
	 * the time to live should continue to be extended.
	 *
	 * @return bool
	 *
	 * @since 2.5/5.8
	 */
	public static function should_extend_ttl() {
		$oembed_token_settings = get_option( 'sbi_oembed_token', array() );

		if ( isset( $oembed_token_settings['disabled'] ) && $oembed_token_settings['disabled'] ) {
			return false;
		}

		$will_expire = SB_Instagram_Oembed::oembed_access_token_will_expire();
		if ( $will_expire ) {
			return true;
		}

		return false;
	}

	/**
	 * Checking for old oembeds makes permanent changes to posts
	 * so we want the user to turn it off and on
	 *
	 * @return bool
	 *
	 * @since 2.5/5.8
	 */
	public static function can_check_for_old_oembeds() {
		/**
		 * TODO: if setting is enabled
		 */
		return true;
	}

	/**
	 * Filters the WordPress list of oembed providers to
	 * change what url is used for remote requests for the
	 * oembed data
	 *
	 * @param array $providers
	 *
	 * @return mixed
	 *
	 * @since 2.5/5.8
	 */
	public static function oembed_providers( $providers ) {
		$oembed_url = SB_Instagram_Oembed::oembed_url();
		if ( $oembed_url ) {
			$providers['#https?://(www\.)?instagr(\.am|am\.com)/(p|tv|reel)/.*#i'] = array( $oembed_url, true );
			// for WP 4.9
			$providers['#https?://(www\.)?instagr(\.am|am\.com)/p/.*#i'] = array( $oembed_url, true );
		}

		return $providers;
	}

	/**
	 * Add the access token from a connected account to make an authenticated
	 * call to get oembed data from Instagram
	 *
	 * @param string $provider
	 * @param string $url
	 * @param array $args
	 *
	 * @return string
	 *
	 * @since 2.5/5.8
	 */
	public static function oembed_set_fetch_url( $provider, $url, $args ) {
		$access_token = SB_Instagram_Oembed::last_access_token();
		if ( ! $access_token ) {
			return $provider;
		}

		if ( strpos( $provider, 'instagram_oembed' ) !== false ) {
			if ( strpos( $url, '?' ) !== false ) {
				$exploded = explode( '?', $url );
				if ( isset( $exploded[1] ) ) {
					$provider = str_replace( urlencode( '?' . $exploded[1] ), '', $provider );
				}
			}
			$provider = add_query_arg( 'access_token', $access_token, $provider );
		}

		return $provider;
	}

	/**
	 * New oembeds are wrapped in a div for easy detection of older oembeds
	 * that will need to be updated
	 *
	 * @param string $html
	 * @param string $url
	 * @param array $args
	 *
	 * @return string
	 *
	 * @since 2.5/5.8
	 */
	public static function oembed_result( $html, $url, $args ) {
		if ( preg_match( '#https?://(www\.)?instagr(\.am|am\.com)/(p|tv|reel)/.*#i', $url ) === 1 ) {
			if ( strpos( $html, 'class="instagram-media"' ) !== false ) {
				$html = '<div class="sbi-embed-wrap">' . str_replace( 'class="instagram-media"', 'class="instagram-media sbi-embed"', $html ) . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Extend the "time to live" for oEmbeds created with access tokens that expire
	 *
	 * @param $ttl
	 * @param $url
	 * @param $attr
	 * @param $post_ID
	 *
	 * @return float|int
	 *
	 * @since 2.5/5.8
	 */
	public static function oembed_ttl( $ttl, $url, $attr, $post_ID ) {
		if ( preg_match( '#https?://(www\.)?instagr(\.am|am\.com)/(p|tv|reel)/.*#i', $url ) === 1 ) {
			$ttl = 30 * YEAR_IN_SECONDS;
		}

		return $ttl;
	}

	/**
	 * Depending on whether a business or personal account is connected,
	 * a different oembed endpoint is used
	 *
	 * @return bool|string
	 *
	 * @since 2.5/5.8
	 */
	public static function oembed_url() {
		return 'https://graph.facebook.com/v8.0/instagram_oembed';
	}

	/**
	 * Any access token will work for oembeds so the most recently connected account's
	 * access token is returned
	 *
	 * @return bool|string
	 *
	 * @since 2.5/5.8
	 */
	public static function last_access_token() {
		$oembed_token_settings = get_option( 'sbi_oembed_token', array() );
		$will_expire = SB_Instagram_Oembed::oembed_access_token_will_expire();
		if ( ! empty( $oembed_token_settings['access_token'] )
		     && (! $will_expire || $will_expire > time()) ) {
			$return = sbi_maybe_clean( $oembed_token_settings['access_token'] );
			return $return;
		} else {
			$if_database_settings = sbi_get_database_settings();

			if ( isset( $if_database_settings['connected_accounts'] ) ) {
				$connected_accounts = $if_database_settings['connected_accounts'];
				foreach ( $connected_accounts as $connected_account ) {
					if ( empty( $oembed_token_settings['access_token'] ) ) {
						if ( isset( $connected_account['type'] ) && $connected_account['type'] === 'business' ) {
							$oembed_token_settings['access_token'] = $connected_account['access_token'];
						}
					}

				}
			}

			if ( ! empty( $oembed_token_settings['access_token'] ) ) {
				$return = sbi_maybe_clean( $oembed_token_settings['access_token'] );
				return $return;
			}

			if ( class_exists( 'CFF_Oembed' ) ) {
				$cff_oembed_token_settings = get_option( 'cff_oembed_token', array() );
				if ( ! empty( $cff_oembed_token_settings['access_token'] ) ) {
					return $cff_oembed_token_settings['access_token'];
				}
			}
		}

		return false;
	}

	/**
	 * Access tokens created from FB accounts not connected to an
	 * FB page expire after 60 days.
	 *
	 * @return bool|int
	 */
	public static function oembed_access_token_will_expire() {
		$oembed_token_settings = get_option( 'sbi_oembed_token', array() );
		$will_expire = isset( $oembed_token_settings['expiration_date'] ) && (int)$oembed_token_settings['expiration_date'] > 0 ? (int)$oembed_token_settings['expiration_date'] : false;

		return $will_expire;
	}

	/**
	 * Before links in the content are processed, old oembed post meta
	 * records are deleted so new oembed data will be retrieved and saved.
	 * If this check has been done and no old oembeds are found, a flag
	 * is saved as post meta to skip the process.
	 *
	 * @since 2.5/5.8
	 */
	public static function check_page_for_old_oembeds() {
		if ( is_admin() ) {
			return;
		}

		$post_ID = get_the_ID();
		$done_checking = (int)get_post_meta( $post_ID, '_sbi_oembed_done_checking', true ) === 1;

		if ( ! $done_checking ) {

			$num_found = SB_Instagram_Oembed::delete_instagram_oembed_caches( $post_ID );
			if ( $num_found === 0 ) {
				update_post_meta( $post_ID, '_sbi_oembed_done_checking', 1 );
			}
		}
	}

	/**
	 * Loop through post meta data and if it's an oembed and has content
	 * that looks like an Instagram oembed, delete it
	 *
	 * @param $post_ID
	 *
	 * @return int number of old oembed caches found
	 *
	 * @since 2.5/5.8
	 */
	public static function delete_instagram_oembed_caches( $post_ID ) {
		$post_metas = get_post_meta( $post_ID );
		if ( empty( $post_metas ) ) {
			return 0;
		}

		$total_found = 0;
		foreach ( $post_metas as $post_meta_key => $post_meta_value ) {
			if ( '_oembed_' === substr( $post_meta_key, 0, 8 ) ) {
				if ( strpos( $post_meta_value[0], 'class="instagram-media"' ) !== false
				     && strpos( $post_meta_value[0], 'sbi-embed-wrap' ) === false ) {
					$total_found++;
					delete_post_meta( $post_ID, $post_meta_key );
					if ( '_oembed_time_' !== substr( $post_meta_key, 0, 13 ) ) {
						delete_post_meta( $post_ID, str_replace( '_oembed_', '_oembed_time_', $post_meta_key ) );
					}
				}
			}
		}

		return $total_found;
	}

	/**
	 * Used for clearing the oembed update check flag for all posts
	 *
	 * @since 2.5/5.8
	 */
	public static function clear_checks() {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . "postmeta" );
		$result = $wpdb->query("
		    DELETE
		    FROM $table_name
		    WHERE meta_key = '_sbi_oembed_done_checking';");
	}
}

function sbiOembedInit() {
	return new SB_Instagram_Oembed();
}
sbiOembedInit();