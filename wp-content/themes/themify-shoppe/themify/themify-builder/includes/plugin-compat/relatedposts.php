<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_RelatedPosts {

	static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'wp_related_posts' ) );
	}

	/**
	 * WordPress Related Posts plugin compatibility
	 * @link https://wordpress.org/plugins/wordpress-23-related-posts-plugin/
	 * Display related posts after the Builder content
	 */
	public static function wp_related_posts() {
		remove_filter( 'the_content', 'wp_rp_add_related_posts_hook', 10 );
		add_filter( 'the_content', 'wp_rp_add_related_posts_hook', 12 );
	}
}