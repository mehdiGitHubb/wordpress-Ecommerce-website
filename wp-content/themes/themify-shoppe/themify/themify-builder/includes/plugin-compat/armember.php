<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * ARMember - Complete Membership Plugin
 * @link https://www.armemberplugin.com/
 */
class Themify_Builder_Plugin_Compat_ARMember {

	static function init() {
		add_filter( 'themify_builder_display', array( __CLASS__, 'themify_builder_display' ), 10, 2 );
	}

	/**
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public static function themify_builder_display( $display, $post_id ) {
		global $arm_pay_per_post_feature;
		if ( ! empty( $arm_pay_per_post_feature->isPayPerPostFeature ) ) {
			$result = $arm_pay_per_post_feature->arm_paid_post_content_check_restriction( '' ); // this function returns a message string if content is restricted, returns original string if not.
			if ( ! empty( $result ) ) {
				$display = false;
			}
		}

		return $display;
	}
}