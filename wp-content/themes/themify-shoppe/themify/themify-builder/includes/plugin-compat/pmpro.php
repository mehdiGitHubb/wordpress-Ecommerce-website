<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * @link https://wordpress.org/plugins/paid-memberships-pro/
 */
class Themify_Builder_Plugin_Compat_PMPro {

	static function init() {
		add_filter( 'themify_builder_display', array( __CLASS__, 'pmpro_themify_builder_display' ), 10, 2 );
	}

	/**
	 * Paid Membership Pro
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public static function pmpro_themify_builder_display( $display, $post_id ) {
		$hasaccess = pmpro_has_membership_access( NULL, NULL, true );
		if( is_array( $hasaccess ) ) {
			//returned an array to give us the membership level values
			$post_membership_levels_ids = $hasaccess[1];
			$post_membership_levels_names = $hasaccess[2];
			$hasaccess = $hasaccess[0];
		}
		return ! $hasaccess?false:$display;
	}
}