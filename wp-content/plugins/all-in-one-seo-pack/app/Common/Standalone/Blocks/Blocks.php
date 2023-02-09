<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads core classes.
 *
 * @since 4.2.3
 */
abstract class Blocks {
	/**
	 * Class constructor.
	 *
	 * @since 4.2.3
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initializes our blocks.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function init() {
		$this->register();
	}

	/**
	 * Registers the block. This is a wrapper to be extended in the child class.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function register() {}
}