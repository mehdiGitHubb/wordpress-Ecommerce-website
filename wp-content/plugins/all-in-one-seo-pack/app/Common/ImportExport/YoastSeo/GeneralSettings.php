<?php
namespace AIOSEO\Plugin\Common\ImportExport\YoastSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the General Settings.
 *
 * @since 4.0.0
 */
class GeneralSettings {
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
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->options = get_option( 'wpseo' );
		if ( empty( $this->options ) ) {
			return;
		}

		$settings = [
			'googleverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'google' ] ],
			'msverify'           => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'bing' ] ],
			'yandexverify'       => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'yandex' ] ],
			'baiduverify'        => [ 'type' => 'string', 'newOption' => [ 'webmasterTools', 'baidu' ] ],
			'enable_xml_sitemap' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'enable' ] ]
		];

		aioseo()->importExport->yoastSeo->helpers->mapOldToNew( $settings, $this->options );
	}
}