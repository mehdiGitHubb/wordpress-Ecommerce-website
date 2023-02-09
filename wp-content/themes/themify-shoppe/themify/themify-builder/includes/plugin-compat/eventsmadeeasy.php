<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * @link https://wordpress.org/plugins/events-made-easy/
 */
class Themify_Builder_Plugin_Compat_eventsmadeeasy {

	static function init() {
		if ( Themify_Builder_Model::is_front_builder_activate() ) {
			remove_action( 'wp_enqueue_scripts', 'eme_register_scripts' );
		}
	}
}