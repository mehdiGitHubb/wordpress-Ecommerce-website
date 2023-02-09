<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_WCMembership {

	static function init() {
		add_filter( 'themify_builder_display', array( __CLASS__, 'wc_memberships_themify_builder_display' ), 10, 2 );
	}

	/**
	 * WooCommerce Membership compatibility
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public static function wc_memberships_themify_builder_display( $display, $post_id ) {
		return
		wc_memberships_is_post_content_restricted() && (
			! current_user_can( 'wc_memberships_view_restricted_post_content', $post_id )
			|| ! current_user_can( 'wc_memberships_view_delayed_post_content', $post_id )
		) ? false : true;
	}
}