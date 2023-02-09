<?php
namespace AIOSEO\Plugin\Lite\Options;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Options as CommonOptions;
use AIOSEO\Plugin\Lite\Traits;

/**
 * Class that holds all internal options for AIOSEO.
 *
 * @since 4.0.0
 */
class InternalOptions extends CommonOptions\InternalOptions {
	use Traits\Options;

	/**
	 * Defaults options for Lite.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	private $liteDefaults = [
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		'internal' => [
			'activated'      => [ 'type' => 'number', 'default' => 0 ],
			'firstActivated' => [ 'type' => 'number', 'default' => 0 ],
			'installed'      => [ 'type' => 'number', 'default' => 0 ],
			'connect'        => [
				'key'     => [ 'type' => 'string' ],
				'time'    => [ 'type' => 'number', 'default' => 0 ],
				'network' => [ 'type' => 'boolean', 'default' => false ],
				'token'   => [ 'type' => 'string' ]
			]
		]
		// phpcs:enable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
	];
}