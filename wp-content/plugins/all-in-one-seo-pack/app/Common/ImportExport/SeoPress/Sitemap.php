<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Sitemap Settings.
 *
 * @since 4.1.4
 */
class Sitemap {
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
		$this->options = get_option( 'seopress_xml_sitemap_option_name' );
		if ( empty( $this->options ) ) {
			return;
		}

		$this->migratePostTypesInclude();
		$this->migrateTaxonomiesInclude();

		$settings = [
			'seopress_xml_sitemap_general_enable' => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'enable' ] ],
			'seopress_xml_sitemap_author_enable'  => [ 'type' => 'boolean', 'newOption' => [ 'sitemap', 'general', 'author' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}

	/**
	 * Migrates the post types to include in sitemap settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function migratePostTypesInclude() {
		$postTypesMigrate = $this->options['seopress_xml_sitemap_post_types_list'];
		$postTypesInclude = [];

		foreach ( $postTypesMigrate as $postType => $options ) {
			$postTypesInclude[] = $postType;
		}

		aioseo()->options->sitemap->general->postTypes->included = $postTypesInclude;
	}

	/**
	 * Migrates the taxonomies to include in sitemap settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	public function migrateTaxonomiesInclude() {
		$taxonomiesMigrate = $this->options['seopress_xml_sitemap_taxonomies_list'];
		$taxonomiesInclude = [];

		foreach ( $taxonomiesMigrate as $taxonomy => $options ) {
			$taxonomiesInclude[] = $taxonomy;
		}

		aioseo()->options->sitemap->general->taxonomies->included = $taxonomiesInclude;
	}
}