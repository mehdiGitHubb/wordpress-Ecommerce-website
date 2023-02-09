<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the Publish Panel in the Block Editor.
 *
 * @since 4.2.0
 */
class PublishPanel {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
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
		if ( ! aioseo()->helpers->isScreenBase( 'post' ) ) {
			return;
		}

		aioseo()->core->assets->load( 'src/vue/standalone/publish-panel/main.js' );
	}
}