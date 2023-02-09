<?php
namespace AIOSEO\Plugin\Common\ThirdParty;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treat everything related to third-party plugins.
 *
 * @since 4.2.5
 */
class ThirdParty {
	/**
	 * Cache class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Cache\Cache
	 */
	public $cache = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.2.5
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'initCache' ] );
	}

	/**
	 * Instantiates @see \AIOSEO\Plugin\Common\ThirdParty\Cache\Cache.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	public function initCache() {
		$this->cache = new Cache\Cache();
	}
}