<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_CachePress {

	static function init() {
		add_filter( 'sgo_css_combine_exclude', array( __CLASS__, 'sg_css_combine_exclude' ), 99 );
	}

	/**
	 * Compatibility with SG optimizer
	 */
	public static function sg_css_combine_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'wp-mediaelement';
		$exclude_list[] = 'media-views';
		$exclude_list[] = 'buttons';
		$exclude_list[] = 'dashicons';
		return $exclude_list;
	}
}