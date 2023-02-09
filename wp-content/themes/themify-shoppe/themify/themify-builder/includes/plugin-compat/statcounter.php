<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * StatCounter – Free Real Time Visitor Stats
 * @link https://wordpress.org/plugins/official-statcounter-plugin-for-wordpress/
 */
class Themify_Builder_Plugin_Compat_statcounter {

	static function init() {
		/* fix plugin breaking Ajax requests */
		if ( is_admin() && themify_is_ajax() ) {
			remove_action( 'wp_head', 'add_statcounter' );
			remove_action( 'wp_footer', 'add_statcounter' );
		}
	}
}