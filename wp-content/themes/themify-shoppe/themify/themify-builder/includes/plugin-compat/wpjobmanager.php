<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * @link https://wordpress.org/plugins/wp-job-manager/
 */
class Themify_Builder_Plugin_Compat_WPJobManager {

	static function init() {
		add_filter( 'themify_builder_query_post', array( __CLASS__, 'themify_builder_query_post' ) );
	}

	/**
	 * Fix missing Job Types taxonomy selector
	 *
	 * @access public
	 */
	public static function themify_builder_query_post( $types ) {
		$types['job_listing']['options'] = array(
			'job_listing_type' => array( 'name' => __( 'Job Types', 'themify' ) ),
		);

		return $types;
	}
}