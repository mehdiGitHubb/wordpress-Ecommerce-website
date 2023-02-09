<?php
/**
 * Class SB_Instagram_API_Connect
 *
 * Connect to the Instagram API and return the results. It's possible
 * to build the url from a connected account (includes access token,
 * account id, account type), endpoint and parameters (hashtag, etc..)
 * as well as a full url such as from the pagination data from some Instagram API requests.
 *
 * Errors from either the Instagram API or from the HTTP request are detected
 * and can be handled.
 *
 * Primarily used in the SB_Instagram_Feed class to collect posts and data for
 * the header. Can also be used for comments in the Pro version
 *
 * @since 2.0/5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_API_Connect
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var object
	 */
	protected $response;

	/**
	 * @var bool
	 */
	protected $encryption_error;

	/**
	 * SB_Instagram_API_Connect constructor.
	 *
	 * @param mixed|array|string $connected_account_or_url either the connected account
	 *  data for this request or the complete url for the request
	 * @param string $endpoint (optional) is optional only if the complete url is provided
	 *  otherwise is they key for the endpoint needed for the request (ex. "header")
	 * @param array $params (optional) used with the connected account and endpoint to add
	 *  additional query parameters to the url if needed
	 *
	 * @since 2.0/5.0
	 */
	public function __construct( $connected_account_or_url, $endpoint = '', $params = array() ) {
		if ( is_array( $connected_account_or_url ) && isset( $connected_account_or_url['access_token'] ) ) {
			$this->set_url( $connected_account_or_url, $endpoint, $params );
		} elseif ( ! is_array( $connected_account_or_url ) && strpos( $connected_account_or_url, 'https' ) !== false ) {
			$this->url = $connected_account_or_url;
		} else {
			$this->url = '';
		}
	}

	/**
	 * Returns the response from Instagram
	 *
	 * @return array|object
	 *
	 * @since 2.0/5.0
	 */
	public function get_data() {
		if ( $this->is_wp_error() ) {
			return array();
		}
		if ( ! empty($this->response['data'] ) ) {
			return $this->response['data'];
		} else {
			return $this->response;
		}
	}

	/**
	 * Returns the error response and the url that was trying to be connected to
	 * or false if no error
	 *
	 * @return array|bool
	 *
	 * @since 2.0/5.0
	 */
	public function get_wp_error() {
		if ( $this->is_wp_error() ) {
			return array( 'response' => $this->response, 'url' => $this->url );
		} else {
			return false;
		}
	}

	/**
	 * Certain endpoints don't include the "next" URL so
	 * this method allows using the "cursors->after" data instead
	 *
	 * @param $type
	 *
	 * @return bool
	 *
	 * @since 2.2.2/5.3.3
	 */
	public function type_allows_after_paging( $type ) {
		return false;
	}

	/**
	 * Returns the full url for the next page of the API request
	 *
	 * @param $type
	 *
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public function get_next_page( $type = '' ) {
		if ( ! empty( $this->response['pagination']['next_url'] ) ) {
			return $this->response['pagination']['next_url'];
		} elseif ( ! empty( $this->response['paging']['next'] ) ) {
			return $this->response['paging']['next'];
		} else {
			if ( $this->type_allows_after_paging( $type ) ) {
				if ( isset( $this->response['paging']['cursors']['after'] ) ) {
					return $this->response['paging']['cursors']['after'];
				}
			}
			return '';
		}
	}

	/**
	 * If url needs to be generated from the connected account, endpoint,
	 * and params, this function is used to do so.
	 *
	 * @param $url
	 */
	public function set_url_from_args( $url ) {
		$this->url = $url;
	}

	/**
	 * @return string
	 *
	 * @since 2.0/5.0
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * If the server is unable to connect to the url, returns true
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function is_wp_error() {
		return is_wp_error( $this->response );
	}

	/**
	 * If the server can connect but Instagram returns an error, returns true
	 *
	 * @return bool
	 *
	 * @since 2.0/5.0
	 */
	public function is_instagram_error( $response = false ) {

		if ( ! $response ) {
			$response = $this->response;
		}

		return (isset( $response['error'] ));
	}

	/**
	 * Connect to the Instagram API and record the response
	 *
	 * @since 2.0/5.0
	 */
	public function connect() {
		if ( empty( $this->url ) ) {
			$this->response = array();
			return;
		}
		$args = array(
			'timeout' => 20
		);
		$response = wp_remote_get( $this->url, $args );

		/**
		 * Api response for instagram connection
		 *
		 * @since 6.0.6
		 */
		do_action( 'sbi_api_connect_response', $response, $this->url );

		if ( ! is_wp_error( $response ) ) {
			// certain ways of representing the html for double quotes causes errors so replaced here.
			$response = json_decode( str_replace( '%22', '&rdquo;', $response['body'] ), true );

			if ( empty( $response ) ) {
				$response = array(
					'error' => array(
						'code' => 'unknown',
						'message' => __( "An unknown error occurred when trying to connect to Instagram's API.", 'instagram-feed' )
					)
				);
			}
		}

		$this->response = $response;
	}

	/**
	 * Determines how and where to record an error from Instagram's API response
	 *
	 * @param array $response response from the API request
	 * @param array $error_connected_account the connected account that is associated
	 *  with the error
	 * @param string $request_type key used to determine the endpoint (ex. "header")
	 *
	 * @since 2.0/5.0
	 */
	public static function handle_instagram_error( $response, $error_connected_account, $request_type ) {
		global $sb_instagram_posts_manager;
		delete_option( 'sbi_dismiss_critical_notice' );

		$type = isset( $response['error']['code'] ) && (int)$response['error']['code'] === 18 ? 'hashtag_limit' : 'api';

		$sb_instagram_posts_manager->add_error( $type, $response, $error_connected_account );

		if ( $type === 'hashtag_limit' ) {
			$sb_instagram_posts_manager->maybe_set_display_error( $type, $response );
		}
	}

	/**
	 * Determines how and where to record an error connecting to a specified url
	 *
	 * @param $response
	 *
	 * @since 2.0/5.0
	 */
	public static function handle_wp_remote_get_error( $response ) {
		global $sb_instagram_posts_manager;
		delete_option( 'sbi_dismiss_critical_notice' );

		$sb_instagram_posts_manager->add_error( 'wp_remote_get', $response );
	}

	/**
	 * Determines how and where to record an error connecting to a specified url
	 *
	 * @since 2.0/5.0
	 */
	public function has_encryption_error() {
		return isset( $this->encryption_error ) && $this->encryption_error;
	}

	/**
	 * Sets the url for the API request based on the account information,
	 * type of data needed, and additional parameters.
	 *
	 * Overwritten in the Pro version.
	 *
	 * @param array $connected_account connected account to be used in the request
	 * @param string $endpoint_slug header or user
	 * @param array $params additional params related to the request
	 *
	 * @since 2.0/5.0
	 * @since 2.2/5.3 added endpoints for the basic display API
	 */
	protected function set_url( $connected_account, $endpoint_slug, $params ) {
		$account_type = ! empty( $connected_account['type'] ) ? $connected_account['type'] : 'personal';
		$num          = ! empty( $params['num'] ) ? (int) $params['num'] : 33;

		if ( $account_type === 'basic' || $account_type === 'personal' ) {
			$access_token = sbi_maybe_clean( $connected_account['access_token'] );
			if ( strpos( $access_token, 'IG' ) !== 0 ) {
				$this->encryption_error = true;

				$url = '';
			} else {
				if ( $endpoint_slug === 'access_token' ) {
					$url = 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $access_token;
				} elseif ( $endpoint_slug === 'header' ) {
					$url = 'https://graph.instagram.com/me?fields=id,username,media_count,account_type&access_token=' . $access_token;
				} else {
					$num = min( $num, 200 );
					$url = 'https://graph.instagram.com/' . $connected_account['user_id'] . '/media?fields=media_url,thumbnail_url,caption,id,media_type,media_product_type,timestamp,username,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D&limit=' . $num . '&access_token=' . $access_token;
				}
			}

		} else {
			$access_token = sbi_maybe_clean( $connected_account['access_token'] );
			if ( strpos( $access_token, 'EA' ) !== 0 ) {
				$this->encryption_error = true;

				$url = '';
			} else {
				if ( 'header' === $endpoint_slug ) {
					$url = 'https://graph.facebook.com/' . $connected_account['user_id'] . '?fields=biography,id,username,website,followers_count,media_count,profile_picture_url,name&access_token=' . sbi_maybe_clean( $connected_account['access_token'] );
				} else {
					$num = min( $num, 200 );
					$url = 'https://graph.facebook.com/v10.0/' . $connected_account['user_id'] . '/media?fields=media_url,media_product_type,video_title,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children%7Bmedia_url,id,media_type,timestamp,permalink,thumbnail_url%7D&limit=' . $num . '&access_token=' . sbi_maybe_clean( $connected_account['access_token'] );
				}
			}

		}

		$this->set_url_from_args( $url );
	}

}
