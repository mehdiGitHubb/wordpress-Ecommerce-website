<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aioseo_lite_just_activated' ) ) {
	/**
	 * Store temporarily that the Lite version of the plugin was activated.
	 * This is needed because WP does a redirect after activation and
	 * we need to preserve this state to know whether user activated Lite or not.
	 *
	 * @since 4.0.0
	 */
	function aioseo_lite_just_activated() {
		aioseo()->core->cache->update( 'lite_just_activated', true );
	}
}

if ( ! function_exists( 'aioseo_lite_just_deactivated' ) ) {
	/**
	 * Store temporarily that Lite plugin was deactivated.
	 * Convert temporary "activated" value to a global variable,
	 * so it is available through the request. Remove from the storage.
	 *
	 * @since 4.0.0
	 */
	function aioseo_lite_just_deactivated() {
		global $aioseoLiteJustActivated, $aioseoLiteJustDeactivated;

		$aioseoLiteJustActivated   = (bool) aioseo()->core->cache->get( 'lite_just_activated' );
		$aioseoLiteJustDeactivated = true;

		aioseo()->core->cache->delete( 'lite_just_activated' );
	}
}

if ( ! function_exists( 'aioseo_pro_just_activated' ) ) {
	/**
	 * Store temporarily that the Pro version of the plugin was activated.
	 * This is needed because when we activate the Pro version on top
	 * of the Lite version, it does not trigger the activation hook in Pro.
	 *
	 * @since 4.0.0
	 */
	function aioseo_pro_just_activated() {
		$liteActivated = is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );
		if ( $liteActivated ) {
			// Add capabilities for the current user on upgrade so that the menu is visible on the first request.
			aioseo()->activate->addCapabilitiesOnUpgrade();

			aioseo()->core->cache->update( 'pro_just_deactivated_lite', true );
		}
	}
}

// If we detect that V3 is active, let's deactivate it now.
if ( defined( 'AIOSEOP_VERSION' ) && defined( 'AIOSEO_PLUGIN_FILE' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	deactivate_plugins( plugin_basename( AIOSEO_PLUGIN_FILE ) );
}