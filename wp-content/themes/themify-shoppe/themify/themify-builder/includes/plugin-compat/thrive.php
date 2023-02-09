<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * Thrive Architect
 * @link https://thrivethemes.com/architect/
 */
class Themify_Builder_Plugin_Compat_Thrive {

	static function init() {
		add_filter( 'themify_builder_is_frontend_editor', array( __CLASS__, 'thrive_compat' ) );
	}

	/**
	 * Compatibility with Thrive Builder and Thrive Leads plugins
	 * Disables Builder's frontend editor when Thrive editor is active
	 *
	 * @return bool
	 */
	public static function thrive_compat( $enabled ) {
		return isset( $_GET['tve'] ) && $_GET['tve'] === 'true' && function_exists( 'tve_editor_content' ) ? false : $enabled;
	}
}