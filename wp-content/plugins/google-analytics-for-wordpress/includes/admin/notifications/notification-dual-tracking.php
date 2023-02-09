<?php


class MonsterInsights_Notification_Dual_Tracking extends MonsterInsights_Notification_Event {
	public $notification_id = 'monsterinsights_notification_dual_tracking';
	public $notification_interval = 30; // in days
	public $notification_type = array( 'basic', 'lite', 'master', 'plus', 'pro' );
	public $notification_category = 'insight';
	public $notification_priority = 1;

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {

		$ua = MonsterInsights()->auth->get_ua();
		$v4 = MonsterInsights()->auth->get_v4_id();

		if ( $ua && ! $v4 ) {
			$is_em          = defined( 'EXACTMETRICS_VERSION' );
			$learn_more_url = $is_em
				? 'https://www.exactmetrics.com/docs/how-to-set-up-dual-tracking/'
				: 'https://www.monsterinsights.com/docs/how-to-set-up-dual-tracking/';

			$plugin_name = $is_em ? 'ExactMetrics' : 'MonsterInsights';

			$notification['title']   = __( 'Enable Dual Tracking and Start Using Google Analytics 4 Today', 'google-analytics-for-wordpress' );
			$notification['content'] = sprintf(
				__( 'On July 1, 2023, Google Analytics will not track any website data for Universal Analytics (GA3). Be prepared for the future by enabling Dual Tracking inside %s to future-proof your website. We\'ve made it easy to upgrade.', 'google-analytics-for-wordpress' ),
				$plugin_name
			);
			$notification['btns']    = array(
				'setup_now'  => array(
					'url'  => $this->get_view_url( 'monsterinsights-dual-tracking-id', 'monsterinsights_settings' ),
					'text' => __( 'Setup now', 'google-analytics-for-wordpress' ),
				),
				'learn_more' => array(
					'url'         => $this->build_external_link( $learn_more_url ),
					'text'        => __( 'How To Enable Dual Tracking', 'google-analytics-for-wordpress' ),
					'is_external' => true,
				),
			);

			return $notification;
		}

		return false;
	}
}

new MonsterInsights_Notification_Dual_Tracking();
