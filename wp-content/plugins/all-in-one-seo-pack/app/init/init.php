<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aioseoPluginIsDisabled' ) ) {
	/**
	 * Disable the AIOSEO if triggered externally.
	 *
	 * @since 4.1.5
	 *
	 * @return bool True if the plugin should be disabled.
	 */
	function aioseoPluginIsDisabled() {
		if ( ! defined( 'AIOSEO_DEV_VERSION' ) && ! isset( $_REQUEST['aioseo-dev'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			return false;
		}

		if ( ! isset( $_REQUEST['aioseo-disable-plugin'] ) ) { // phpcs:ignore HM.Security.NonceVerification.Recommended
			return false;
		}

		return true;
	}
}