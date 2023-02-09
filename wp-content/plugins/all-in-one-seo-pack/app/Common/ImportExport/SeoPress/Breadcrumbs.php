<?php
namespace AIOSEO\Plugin\Common\ImportExport\SeoPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound

/**
 * Migrates the Breadcrumb settings.
 *
 * @since 4.1.4
 */
class Breadcrumbs {
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

		$this->migrate();
	}

	/**
	 * Migrates the Breadcrumbs settings.
	 *
	 * @since 4.1.4
	 *
	 * @return void
	 */
	private function migrate() {
		if ( ! empty( $this->options['seopress_breadcrumbs_i18n_search'] ) ) {
			aioseo()->options->breadcrumbs->searchResultFormat = sprintf( '%1$s #breadcrumb_archive_post_type_name', $this->options['seopress_breadcrumbs_i18n_search'] );
		}

		if ( ! empty( $this->options['seopress_breadcrumbs_remove_blog_page'] ) ) {
			aioseo()->options->breadcrumbs->showBlogHome = false;
		}

		$settings = [
			'seopress_breadcrumbs_enable'    => [ 'type' => 'boolean', 'newOption' => [ 'breadcrumbs', 'enable' ] ],
			'seopress_breadcrumbs_separator' => [ 'type' => 'string', 'newOption' => [ 'breadcrumbs', 'separator' ] ],
			'seopress_breadcrumbs_i18n_home' => [ 'type' => 'string', 'newOption' => [ 'breadcrumbs', 'homepageLabel' ] ],
			'seopress_breadcrumbs_i18n_here' => [ 'type' => 'string', 'newOption' => [ 'breadcrumbs', 'breadcrumbPrefix' ] ],
			'seopress_breadcrumbs_i18n_404'  => [ 'type' => 'string', 'newOption' => [ 'breadcrumbs', 'errorFormat404' ] ],
		];

		aioseo()->importExport->seoPress->helpers->mapOldToNew( $settings, $this->options );
	}
}