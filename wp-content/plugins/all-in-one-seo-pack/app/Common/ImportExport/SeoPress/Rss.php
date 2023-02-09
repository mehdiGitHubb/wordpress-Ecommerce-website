<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the RSS settings.
 *
 * @since 4.1.4
 */
class Rss {
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
		$this->options = get_option( 'seopress_pro_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migrateRss();
	}

	/**
	 * Migrates the RSS settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function migrateRss() {
		if ( ! empty( $this->options['seopress_rss_before_html'] ) ) {
			aioseo()->options->rssContent->before = esc_html( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $this->options['seopress_rss_before_html'] ) );
		}

		if ( ! empty( $this->options['seopress_rss_after_html'] ) ) {
			aioseo()->options->rssContent->after = esc_html( aioseo()->importExport->seoPress->helpers->macrosToSmartTags( $this->options['seopress_rss_after_html'] ) );
		}
	}
}