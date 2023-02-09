<?php
/**
 * Class SB_Instagram_Token_Refresher
 *
 * Various methods for efficiently refreshing basic display access
 * tokens which expire every 60 days if not refreshed.
 *
 * @since 2.2/5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Token_Refresher {


	/**
	 * @var array
	 */
	private $connected_account;

	/**
	 * @var array
	 */
	private $report;

	public function __construct( $connected_account ) {
		$this->connected_account = $connected_account;
		$this->report            = array();
	}

	public function get_report() {
		return $this->report;
	}

	/**
	 * The plugin will attempt to refresh the token well
	 * before it expires. This function determines if the
	 * minimum amount of time has passed before the token
	 * can be refreshed
	 *
	 * @param $connected_account
	 *
	 * @return bool
	 */
	public static function refresh_time_has_passed_threshold( $connected_account ) {
		$expiration_timestamp = isset( $connected_account['expires_timestamp'] ) ? $connected_account['expires_timestamp'] : time();
		$current_time         = sbi_get_current_timestamp();

		$refresh_threshold = $expiration_timestamp - SBI_REFRESH_THRESHOLD_OFFSET;

		if ( $refresh_threshold < $current_time ) {
			return true;
		}
		return false;
	}

	/**
	 * Instagram will automatically reject API calls if
	 * done too frequently. This method returns true if
	 * there has been a minimum amount of time since the last
	 * API connection was attemplted
	 *
	 * @param $connected_account
	 *
	 * @return bool
	 */
	public static function minimum_time_interval_since_last_attempt_has_passed( $connected_account ) {
		$last_attempt = isset( $connected_account['last_refresh_attempt'] ) ? (int) $connected_account['last_refresh_attempt'] : 0;
		$current_time = sbi_get_current_timestamp();
		if ( $current_time > $last_attempt + SBI_MINIMUM_INTERVAL ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns true if the minimum time has passed since the last
	 * successfull access token refresh and the minimum time has passed
	 * since the last attempt.
	 *
	 * @return bool
	 */
	public function should_attempt_refresh() {
		if ( self::refresh_time_has_passed_threshold( $this->connected_account ) ) {

			if ( self::minimum_time_interval_since_last_attempt_has_passed( $this->connected_account ) ) {
				$this->report['should_do_update'] = true;
				$this->report['reason']           = '';
				return true;
			} else {
				$this->report['should_do_update'] = false;
				$this->report['reason']           = 'has not been enough time since last attempt';
			}
		} else {
			$this->report['should_do_update'] = false;
			$this->report['reason']           = 'token expiration date not close enough';
		}

		return false;
	}

	/**
	 * Attempts to refresh the token by connecting to the
	 * Instagram API. Logs information about the error if unsuccessful.
	 *
	 * @return bool
	 */
	public function attempt_token_refresh() {
		$this->update_last_attempt_timestamp();

		$connection = new SB_Instagram_API_Connect( $this->connected_account, 'access_token', array() );

		$connection->connect();

		if ( ! $connection->is_wp_error() && ! $connection->is_instagram_error() ) {
			$access_token_data = $connection->get_data();

			if ( ! empty( $access_token_data ) && ! empty( $access_token_data['expires_in'] ) ) {
				$this->report['did_update'] = true;
				$this->add_renewal_data( $access_token_data );

				return true;
			} else {
				$this->report['did_update'] = false;
				$this->report['reason']     = 'successful connection but no data returned';
			}
		} else {
			$this->report['did_update'] = false;
			$this->report['reason']     = 'could not connect to Instagram';
			$this->report['error_log']  = $connection;
		}

		return false;
	}

	/**
	 * Updates data related to when the last attempt was made to refresh
	 * the access token for a connected account and saves it in the database.
	 */
	public function update_last_attempt_timestamp() {
		sbi_update_connected_account( $this->connected_account['user_id'], array( 'last_updated' => time() ) );
	}

	/**
	 * Helps determine if an access token is from a private
	 * account which can't be refreshed
	 *
	 * @return bool
	 *
	 * @since 2.4.7/5.7.1
	 */
	public function get_last_error_code() {
		if ( isset( $this->report['error_log'] ) ) {
			if ( ! is_wp_error( $this->report['error_log'] ) ) {
				$error = $this->report['error_log']->get_data();
				return $error['error']['code'];
			}
		}
		return false;
	}

	/**
	 * Updates data related to the renewed access token
	 * for a connected account and saves it in the database.
	 *
	 * @param $token_data
	 */
	private function add_renewal_data( $token_data ) {
		$expires_in        = $token_data['expires_in'];
		$expires_timestamp = sbi_get_current_timestamp() + $expires_in;

		$to_update = array(
			'access_token' => $token_data['access_token'],
			'expires'      => date( 'Y-m-d H:i:s', $expires_timestamp ),
		);
		sbi_update_connected_account( $this->connected_account['user_id'], $to_update );
	}

}
