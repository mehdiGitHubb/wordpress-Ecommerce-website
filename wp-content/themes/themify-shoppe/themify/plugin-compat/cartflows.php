<?php
/**
 * Themify Compatibility Code
 *
 * @package Themify
 */

/**
 * Funnel Builder by CartFlows
 * @link https://wordpress.org/plugins/cartflows/
 */
class Themify_Compat_cartflows {

	static function init() {
		add_filter( 'cartflows_remove_theme_scripts', '__return_false' );
	}
}