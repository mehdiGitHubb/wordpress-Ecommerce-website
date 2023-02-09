<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the notifications standalone.
 *
 * @since 4.2.0
 */
class Notifications {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScript' ] );
	}

	/**
	 * Enqueues the script.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public function enqueueScript() {
		aioseo()->core->assets->load( 'src/vue/standalone/notifications/main.js', [], [
			'newNotifications' => count( Models\Notification::getNewNotifications() )
		], 'aioseoNotifications' );
	}
}