<?php

namespace InstagramFeed;

use InstagramFeed\Builder\SBI_Db;
use SB_Instagram_Connected_Account;
use SB_Instagram_Data_Manager;

/**
 * Class Platform_Data
 *
 * Handles all data related to the platform.
 *
 * @since 6.0.6
 *
 * @package InstagramFeed
 */
class Platform_Data {

	/**
	 * Option key for app statuses.
	 *
	 * @var string
	 */
	const SBI_STATUSES_OPTION_KEY = 'sbi_statuses';

	/**
	 * Option key for the revoke platform data.
	 *
	 * @var string
	 */
	const REVOKE_PLATFORM_DATA_OPTION_KEY = 'sbi_revoke_platform_data';

	/**
	 * Array key for the app permission status key on `sbi_statuses`.
	 *
	 * @var string
	 */
	const APP_PERMISSION_REVOKED_STATUS_KEY = 'app_permission_revoked';

	/**
	 * Array key for the warning email flag for unused feed status key on `sbi_statuses`.
	 */
	const UNUSED_FEED_WARNING_EMAIL_SENT_STATUS_KEY = 'unused_feed_warning_email_sent';

	/**
	 * Register the hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'sbi_api_connect_response', [ $this, 'handle_platform_data_on_api_response' ], 10, 2 );
		add_action( 'sbi_before_display_instagram', [ $this, 'handle_app_permission_error' ], 10 );
		add_action( 'sbi_app_permission_revoked', [ $this, 'handle_app_permission_status' ], 10, 1 );
		add_action( 'sbi_before_delete_old_data', [ $this, 'handle_event_before_delete_old_data' ], 10 );

		// Ajax Hooks
		add_action( 'wp_ajax_sbi_reset_unused_feed_usage', [ $this, 'handle_unused_feed_usage' ], 10 );
	}

	/**
	 * Handle the platform data on the API response.
	 *
	 * @param array $response The response from the API.
	 * @param string $url The URL of the request.
	 *
	 * @return void
	 */
	public function handle_platform_data_on_api_response( $response, $url ) {
		global $sb_instagram_posts_manager;

		if ( is_wp_error( $response ) ) {
			return;
		}

		if ( empty( $response['response'] ) || empty( $response['response']['code'] ) ) {
			return;
		}

		if ( $response['response']['code'] !== 200 ) {
			return;
		}

		// Remove the platform data deletion notice.
		$sb_instagram_posts_manager->remove_error( 'platform_data_deleted' );

		$sbi_statuses_option = get_option( self::SBI_STATUSES_OPTION_KEY, [] );

		if ( empty( $sbi_statuses_option[ self::APP_PERMISSION_REVOKED_STATUS_KEY ] ) ) {
			return;
		}

		$sbi_revoke_platform_data = get_option( self::REVOKE_PLATFORM_DATA_OPTION_KEY, [] );
		$revoked_account_username = isset( $sbi_revoke_platform_data['connected_account']['username'] ) ? $sbi_revoke_platform_data['connected_account']['username'] : '';

		if ( empty( $revoked_account_username ) ) {
			return;
		}

		$api_response_username = json_decode( $response['body'] )->username;

		if ( $revoked_account_username !== $api_response_username ) {
			return;
		}

		// Cleanup the revoked platform status and revoke account data.
		$this->cleanup_revoked_account( $sbi_statuses_option );

		$sb_instagram_posts_manager->reset_api_errors();
	}

	/**
	 * Handle the app permission error.
	 *
	 * @return void
	 */
	public function handle_app_permission_error() {
		global $sb_instagram_posts_manager;

		$sbi_statuses_option = get_option( self::SBI_STATUSES_OPTION_KEY, [] );

		if ( empty( $sbi_statuses_option[ self::APP_PERMISSION_REVOKED_STATUS_KEY ] ) ) {
			return;
		}

		$sbi_revoke_platform_data = get_option( self::REVOKE_PLATFORM_DATA_OPTION_KEY, [] );

		$revoke_platform_data_timestamp = isset( $sbi_revoke_platform_data['revoke_platform_data_timestamp'] ) ? $sbi_revoke_platform_data['revoke_platform_data_timestamp'] : 0;
		$connected_account              = isset( $sbi_revoke_platform_data['connected_account'] ) ? $sbi_revoke_platform_data['connected_account'] : [];

		if ( ! $revoke_platform_data_timestamp ) {
			return;
		}

		$current_timestamp = current_time( 'timestamp', true );

		// Check if current timestamp is less than revoke platform data timestamp, if so, return.
		if ( $current_timestamp < $revoke_platform_data_timestamp ) {
			return;
		}

		// Revoke platform data.
		$this->delete_platform_data( $connected_account );
		$this->send_platform_data_delete_notification_email();

		// Cleanup the revoked platform status and revoke account data.
		$this->cleanup_revoked_account( $sbi_statuses_option );
		$sb_instagram_posts_manager->reset_api_errors();

		// Adding a notice to the admin page to inform the admin that platform data has been deleted.
		$sb_instagram_posts_manager->add_error( 'platform_data_deleted', __( 'An account admin has deauthorized the Smash Balloon app used to power the Instagram Feed plugin. The page was not reconnected within the 7 day limit and all Instagram data was automatically deleted on your website due to Facebook data privacy rules.', 'instagram-feed' ) );
	}

	/**
	 * Handle the app permission status.
	 *
	 * @param array $connected_account The connected account data.
	 *
	 * @return void
	 */
	public function handle_app_permission_status( $connected_account ) {
		$sbi_statuses_option = get_option( self::SBI_STATUSES_OPTION_KEY, [] );

		if ( isset( $sbi_statuses_option['app_permission_revoked'] ) && true === $sbi_statuses_option['app_permission_revoked'] ) {
			return;
		}

		$this->update_app_permission_revoked_status( $sbi_statuses_option, true );

		// Calculate the grace period for revoking platform data.
		$current_timestamp              = current_time( 'timestamp', true );
		$revoke_platform_data_timestamp = strtotime( '+7 days', $current_timestamp );

		update_option( self::REVOKE_PLATFORM_DATA_OPTION_KEY, [
			'revoke_platform_data_timestamp' => $revoke_platform_data_timestamp,
			'connected_account'              => $connected_account,
		] );

		$this->send_revoke_notification_email();
	}

	/**
	 * Delete any data associated with the Instagram API and the
	 * connected account being deleted.
	 *
	 * @param $to_delete_connected_account
	 *
	 * @return void
	 */
	protected function delete_platform_data( $to_delete_connected_account ) {
		$are_other_business_accounts = false;
		$all_connected_accounts      = SB_Instagram_Connected_Account::get_all_connected_accounts();

		$to_update = [];
		foreach ( $all_connected_accounts as $connected_account ) {
			if ( (int) $connected_account['user_id'] !== (int) $to_delete_connected_account['user_id'] ) {
				$to_update[ $connected_account['user_id'] ] = $connected_account;

				if ( isset( $connected_account['type'] ) && $connected_account['type'] === 'business' ) {
					$are_other_business_accounts = true;
				}
			}
		}

		SB_Instagram_Connected_Account::update_connected_accounts( $to_update );

		SBI_Db::delete_source_by_account_id( $to_delete_connected_account['user_id'] );

		$manager = new SB_Instagram_Data_Manager();

		$manager->delete_caches();
		$manager->delete_comments_data();

		if ( empty( $to_update ) || ! $are_other_business_accounts ) {
			$manager->delete_hashtag_data();
		} else {
			$manager->delete_non_hashtag_sbi_instagram_posts( $to_delete_connected_account['username'] );
		}
	}

	/**
	 * Update the app permission revoked status.
	 *
	 * @param array $sbi_statuses_option The option value.
	 * @param bool $is_revoked The revoke status.
	 *
	 * @return void
	 */
	protected function update_app_permission_revoked_status( $sbi_statuses_option, $is_revoked ) {
		if ( $is_revoked ) {
			$sbi_statuses_option[ self::APP_PERMISSION_REVOKED_STATUS_KEY ] = true;
		} else {
			unset( $sbi_statuses_option[ self::APP_PERMISSION_REVOKED_STATUS_KEY ] );
		}
		update_option( self::SBI_STATUSES_OPTION_KEY, $sbi_statuses_option );
	}

	/**
	 * Handles events before the deletion of old data.
	 *
	 * @param array $statuses
	 *
	 * @return void
	 */
	public function handle_event_before_delete_old_data( $statuses ) {
		global $sb_instagram_posts_manager;

		$sbi_statuses_option = get_option( self::SBI_STATUSES_OPTION_KEY, [] );

		if ( ! empty( $sbi_statuses_option[ self::UNUSED_FEED_WARNING_EMAIL_SENT_STATUS_KEY ] ) ) {
			return;
		}

		if ( $statuses['last_used'] < sbi_get_current_time() - ( 14 * DAY_IN_SECONDS ) ) {
			$sb_instagram_posts_manager->add_error( 'unused_feed', __( 'Your Instagram feed has been not viewed in the last 14 days. Due to Instagram data privacy rules, all data for this feed will be deleted in 7 days time. To avoid automated data deletion, simply view the Instagram feed on your website within the next 7 days.', 'instagram-feed' ) );

			$this->send_unused_feed_usage_notification_email();

			// Setting the flag to true so that the warning email is not sent again.
			$sbi_statuses_option[ self::UNUSED_FEED_WARNING_EMAIL_SENT_STATUS_KEY ] = true;
			update_option( self::SBI_STATUSES_OPTION_KEY, $sbi_statuses_option );
		}
	}

	/**
	 * Handles the reset of unused feed data for deletion.
	 *
	 * @return void
	 */
	public function handle_unused_feed_usage() {
		//Security Checks
		check_ajax_referer( 'sbi_nonce', 'sbi_nonce' );

		if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
			wp_send_json_error();
		}

		global $sb_instagram_posts_manager;

		$sb_instagram_posts_manager->remove_error('unused_feed');

		$manager = new \SB_Instagram_Data_Manager();
		$manager->update_last_used();

		$sbi_statuses_option = get_option( self::SBI_STATUSES_OPTION_KEY, [] );

		// Unset the flag to allow the warning email to be sent again.
		unset( $sbi_statuses_option[ self::UNUSED_FEED_WARNING_EMAIL_SENT_STATUS_KEY ] );
		update_option( self::SBI_STATUSES_OPTION_KEY, $sbi_statuses_option );

		wp_send_json_success( [
			'message' => '<div style="margin-top: 10px;">' . esc_html__( 'Success! Your Instagram Feeds will continue to work normally.', 'instagram-feed' ) . '</div>'
		] );
	}

	/**
	 * Cleanup revoked account data.
	 *
	 * @param array $sbi_statuses_option
	 *
	 * @return void
	 */
	public function cleanup_revoked_account( $sbi_statuses_option ) {
		$this->update_app_permission_revoked_status( $sbi_statuses_option, false );
		delete_option( self::REVOKE_PLATFORM_DATA_OPTION_KEY );
	}

	/**
	 * Sends a notification email to the admin when the app permission is revoked.
	 *
	 * @return void
	 */
	protected function send_revoke_notification_email() {
		$link = admin_url( 'admin.php?page=sbi-settings');

		$title         = __( 'There has been a problem with your Instagram Feed.', 'instagram-feed' );
		$bold          = __( 'Action Required Within 7 Days', 'instagram-feed' );
		$site_url      = sprintf( '<a href="%s">%s<a/>', esc_url( home_url() ), __( 'your website', 'instagram-feed' ) );
		$details       = '<p>' . sprintf( __( 'An account admin has deauthorized the Smash Balloon app used to power the Instagram Feed plugin on %s. If the Instagram source is not reconnected within 7 days then all Instagram data will be automatically deleted on your website  due to Facebook data privacy rules.', 'instagram-feed' ), $site_url ) . '</p>';
		$settings_page = sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html__( 'Settings Page', 'instagram-feed' ) );
		$details       .= '<p>' . sprintf( __( 'To prevent the automated deletion of data for the account, please reconnect your source for the plugin %s within 7 days.', 'instagram-feed' ), $settings_page ). '</p>';
		$details       .= '<p><a href="https://smashballoon.com/doc/action-required-within-7-days/?instagram&utm_campaign=instagram-free&utm_source=permissionerror&utm_medium=email&utm_content=More Information" target="_blank" rel="noopener">' . __( 'More Information', 'instagram-feed' ) . '</a></p>';

		Email_Notification::send( $title, $bold, $details );
	}

	/**
	 * Sends a notification email to the admin when the feed has not been used for a while.
	 *
	 * @return void
	 */
	protected function send_unused_feed_usage_notification_email() {
		$title    = __( 'There has been a problem with your Instagram Feed.', 'instagram-feed' );
		$bold     = __( 'Action Required Within 7 Days', 'instagram-feed' );
		$site_url = sprintf( '<a href="%s">%s<a/>', esc_url( home_url() ), __( 'your website', 'instagram-feed' ) );
		$details  = '<p>' . sprintf( __( 'An Instagram feed on %s has been not viewed in the last 14 days. Due to Instagram data privacy rules, all data for this feed will be deleted in 7 days time.', 'instagram-feed' ), $site_url ) . '</p>';
		$details  .= '<p>' . __( 'To avoid automated data deletion, simply view the Instagram feed on your website within the next 7 days.', 'instagram-feed' ) . '</p>';

		Email_Notification::send( $title, $bold, $details );
	}

	/**
	 * Sends a notification email to the admin when the platform data has been deleted.
	 *
	 * @return void
	 */
	protected function send_platform_data_delete_notification_email() {
		$link = admin_url( 'admin.php?page=sbi-settings');

		$title         = __( 'All Instagram Data has Been Removed', 'instagram-feed' );
		$bold          = __( 'An account admin has deauthorized the Smash Balloon app used to power the Instagram Feed plugin.', 'instagram-feed' );
		$site_url      = sprintf( '<a href="%s">%s<a/>', esc_url( home_url() ), __( 'your website', 'instagram-feed' ) );
		$details       = '<p>' . sprintf( __( 'The page was not reconnected within the 7 day limit and all Instagram data was automatically deleted on %s due to Facebook data privacy rules.', 'instagram-feed' ), $site_url ) . '</p>';
		$settings_page = sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html__( 'Settings Page', 'instagram-feed' ) );
		$details       .= '<p>' . sprintf( __( 'To fix your feeds, reconnect all accounts that were in use on the Settings page.', 'instagram-feed' ), $settings_page ) . '</p>';

		Email_Notification::send( $title, $bold, $details );
	}

}
