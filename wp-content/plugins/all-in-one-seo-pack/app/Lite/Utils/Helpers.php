<?php
namespace AIOSEO\Plugin\Lite\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Utils as CommonUtils;

/**
 * Contains helper functions.
 *
 * @since 4.2.4
 */
class Helpers extends CommonUtils\Helpers {
	/**
	 * Get the headers for internal API requests.
	 *
	 * @since 4.2.4
	 *
	 * @return array An array of headers.
	 */
	public function getApiHeaders() {
		return [];
	}

	/**
	 * Get the User Agent for internal API requests.
	 *
	 * @since 4.2.4
	 *
	 * @return string The User Agent.
	 */
	public function getApiUserAgent() {
		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; AIOSEO/Lite/' . AIOSEO_VERSION;
	}
}