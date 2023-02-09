<?php
namespace AIOSEO\Plugin\Common\Breadcrumbs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shortcode.
 *
 * @since 4.1.1
 */
class Shortcode {
	/**
	 * Shortcode constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		add_shortcode( 'aioseo_breadcrumbs', [ $this, 'display' ] );
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 4.1.1
	 *
	 * @return string|void The breadcrumb html.
	 */
	public function display() {
		return aioseo()->breadcrumbs->frontend->display( false );
	}
}