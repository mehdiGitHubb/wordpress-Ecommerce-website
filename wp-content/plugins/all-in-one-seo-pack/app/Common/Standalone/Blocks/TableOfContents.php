<?php
namespace AIOSEO\Plugin\Common\Standalone\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table of Contents Block.
 *
 * @since 4.2.3
 */
class TableOfContents extends Blocks {
	/**
	 * Register the block.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock( 'aioseo/table-of-contents' );
	}
}