<?php
namespace AIOSEO\Plugin\Common\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits;
use AIOSEO\Plugin\Common\Utils;

/**
 * Class that holds all internal network options for AIOSEO.
 *
 * @since 4.2.5
 */
class InternalNetworkOptions {
	use Traits\Options;
	use Traits\NetworkOptions;

	/**
	 * Holds the helpers class.
	 *
	 * @since 4.2.5
	 *
	 * @var Utils\Helpers
	 */
	protected $helpers;

	/**
	 * All the default options.
	 *
	 * @since 4.2.5
	 *
	 * @var array
	 */
	protected $defaults = [];

	/**
	 * The Construct method.
	 *
	 * @since 4.2.5
	 *
	 * @param string $optionsName The options name.
	 */
	public function __construct( $optionsName = 'aioseo_options_network_internal' ) {
		$this->helpers     = new Utils\Helpers();
		$this->optionsName = $optionsName;

		$this->init();

		add_action( 'shutdown', [ $this, 'save' ] );
	}
}