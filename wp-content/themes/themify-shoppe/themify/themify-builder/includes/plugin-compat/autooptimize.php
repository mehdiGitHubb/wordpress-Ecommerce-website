<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_AutoOptimize {

	static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	public static function template_redirect() {
		if ( Themify_Builder_Model::is_frontend_editor_page() ) {
		    add_filter( 'autoptimize_filter_css_noptimize', '__return_true' );
		    add_filter( 'autoptimize_filter_js_noptimize', '__return_true' );
		}
	}
}