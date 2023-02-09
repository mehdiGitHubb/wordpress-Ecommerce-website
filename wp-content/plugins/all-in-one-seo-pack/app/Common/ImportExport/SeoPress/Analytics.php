<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Analytics Settings.
 *
 * @since 4.1.4
 */
class Analytics {
	/**
	 * List of options.
	 *
	 * @since 4.2.7
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Class constructor.
	 *
	 * @since 4.1.4
	 */
	public function __construct() {
		$this->options = get_option( 'seopress_google_analytics_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$settings = [
			'seopress_google_analytics_other_tracking' => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'miscellaneousVerification' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}
}