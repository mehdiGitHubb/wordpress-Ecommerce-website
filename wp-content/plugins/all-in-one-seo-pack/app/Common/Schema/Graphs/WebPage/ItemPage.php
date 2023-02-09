<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs\WebPage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ItemPage graph class.
 *
 * @since 4.0.0
 */
class ItemPage extends WebPage {
	/**
	 * The graph type.
	 *
	 * This value can be overridden by WebPage child graphs that are more specific.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $type = 'ItemPage';
}