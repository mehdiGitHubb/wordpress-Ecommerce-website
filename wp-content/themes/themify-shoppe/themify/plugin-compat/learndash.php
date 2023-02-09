<?php
/**
 * Themify Compatibility Code
 *
 * @package Themify
 */

/**
 * LearnDash LMS
 * @link http://www.learndash.com/
 */
class Themify_Compat_learndash {

	static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Fix video display in Focus Mode
	 *
	 * @access public
	 */
	public static function template_redirect() {
		if ( is_singular( 'sfwd-lessons' ) ) {
			remove_filter( 'wp_video_shortcode_library', array( 'Themify_Enqueue_Assets', 'media_shortcode_library' ), 10, 1 );
		}
	}
}