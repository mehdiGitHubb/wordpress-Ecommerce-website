<?php
namespace AIOSEO\Plugin\Common\ThirdParty\Cache;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles third-party caching plugins.
 *
 * @since 4.2.5
 */
class Cache {
	/**
	 * The third-party plugins we have rules for.
	 *
	 * @since 4.2.5
	 *
	 * @var string[] The key contains the class name prefixed with its namespace.
	 *               The value contains the "{plugin_folder}/{plugin_main_file}".
	 */
	private $plugins = [
		'AIOSEO\Plugin\Common\ThirdParty\Cache\WpFastestCache' => 'wp-fastest-cache/wpFastestCache.php'
	];

	/**
	 * List of active plugins and their instances.
	 *
	 * @since 4.2.7
	 *
	 * @var array[Object]
	 */
	private $activePlugins = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.2.5
	 */
	public function __construct() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->activePlugins = [];
		foreach ( $this->plugins as $class => $relativeFilePath ) {
			if ( is_plugin_active( $relativeFilePath ) ) {
				$this->activePlugins[] = new $class( $relativeFilePath );
			}
		}
	}

	/**
	 * Takes a request URI, e.g. "sitemap.xml", and prevent it from being cached.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $uri Request URI.
	 * @return void
	 */
	public function excludeUri( $uri ) {
		foreach ( $this->activePlugins as $activePlugin ) {
			if ( method_exists( $activePlugin, 'excludeUri' ) ) {
				$activePlugin->excludeUri( $uri );
			}
		}
	}
}