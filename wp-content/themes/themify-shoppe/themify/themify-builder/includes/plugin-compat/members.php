<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * @link https://wordpress.org/plugins/members/
 */
class Themify_Builder_Plugin_Compat_Members {

	static function init() {
		add_filter( 'themify_builder_display', array( __CLASS__, 'members_themify_builder_display' ), 10, 2 );
	}

	/**
	 * Members compatibility
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public static function members_themify_builder_display( $display, $post_id ) {
		return ! members_can_current_user_view_post( $post_id ) ? false : $display;
	}
}