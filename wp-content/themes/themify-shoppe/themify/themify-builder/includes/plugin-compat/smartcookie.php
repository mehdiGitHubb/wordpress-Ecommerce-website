<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * Smart Cookie Kit
 * @link https://wordpress.org/plugins/smart-cookie-kit/
 */
class Themify_Builder_Plugin_Compat_SmartCookie {

	static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	public static function template_redirect() {
		if ( Themify_Builder_Model::is_front_builder_activate() && class_exists( 'NMOD_SmartCookieKit_Frontend' ) ) {
			remove_action( 'wp_enqueue_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'buffer_set' ), 0 );
			remove_action( 'wp_print_footer_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'buffer_unset' ), 10 );
			remove_action( 'wp_enqueue_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'enqueue_scripts' ), 1 );
			remove_action( 'wp_print_footer_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'run_fontend_kit' ), 99999 );
		}
	}
}