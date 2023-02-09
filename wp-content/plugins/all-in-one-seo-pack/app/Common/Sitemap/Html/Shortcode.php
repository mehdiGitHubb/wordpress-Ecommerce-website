<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the HTML sitemap shortcode.
 *
 * @since 4.1.3
 */
class Shortcode {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.3
	 */
	public function __construct() {
		add_shortcode( 'aioseo_html_sitemap', [ $this, 'render' ] );
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 4.1.3
	 *
	 * @param  array       $attributes The shortcode attributes.
	 * @return string|void             The HTML sitemap.
	 */
	public function render( $attributes ) {
		$attributes = aioseo()->htmlSitemap->frontend->getAttributes( $attributes );

		return aioseo()->htmlSitemap->frontend->output( false, $attributes );
	}
}