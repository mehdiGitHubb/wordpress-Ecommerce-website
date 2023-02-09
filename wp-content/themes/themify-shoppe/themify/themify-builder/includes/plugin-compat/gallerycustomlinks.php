<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_GalleryCustomLinks {

	static function init() {
		add_filter( 'themify_builder_image_link_before', array( __CLASS__, 'wp_gallery_custom_links' ), 10, 3 );
	}

	/**
	 * Compatibility with WP Gallery Custom Links plugin
	 * @link https://wordpress.org/plugins/wp-gallery-custom-links
	 * Apply Link and Target fields to gallery images in Grid layout
	 *
	 * @return string
	 */
	public static function wp_gallery_custom_links( $link_before, $image, $settings ) {
		$attachment_meta = get_post_meta( $image->ID, '_gallery_link_url', true );
		if( $attachment_meta ) {
			$link_before = preg_replace( '/href="(.*)"/', 'href="' . $attachment_meta . '"', $link_before );
		}
		$attachment_meta = get_post_meta( $image->ID, '_gallery_link_target', true );
		if( $attachment_meta ) {
			$link_before = str_replace( '>', ' target="' . $attachment_meta . '">', $link_before );
		}

		return $link_before;
	}
}