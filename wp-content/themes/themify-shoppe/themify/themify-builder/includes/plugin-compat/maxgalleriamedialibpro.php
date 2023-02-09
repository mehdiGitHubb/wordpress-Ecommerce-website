<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * Media Library Folders Pro For WordPress
 * @link https://maxgalleria.com/downloads/media-library-plus-pro/
 */
class Themify_Builder_Plugin_Compat_MaxGalleriaMediaLibPro {

	static function init() {
		add_filter( 'themify_styles_top_frame', array( __CLASS__, 'themify_styles_top_frame' ) );
	}

	/**
	 * Fix frontend media picker styles missing with the Media Library Folders Pro plugin
	 *
	 * @return array
	 */
	public static function themify_styles_top_frame( $styles ) {
		$styles[ MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.min.css' ] = 1;
		$styles[ MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/mlfp-media.css' ] = 1;
		$styles[ MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/maxgalleria-media-library.css' ] = 1;

		return $styles;
	}
}