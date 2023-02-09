<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_EnviraGallery {

	static function init() {
		add_filter( 'themify_builder_post_types_support', array( __CLASS__, 'themify_builder_post_types_support' ), 12, 1 );
		add_filter( 'themify_post_types',array( __CLASS__, 'themify_builder_post_types_support' ), 12, 1 );
	}

	/**
	 * Filter builder post types compatibility
	 *
	 * @access public
	 * @param int $new_id
	 * @param object $post
	 */
	public static function themify_builder_post_types_support($post_types){
		$post_types = array_unique($post_types);
		$exclude = array_search('envira', $post_types,true);
		if($exclude!==false){
			unset($post_types[$exclude]);
		}
		$exclude = array_search('envira_album', $post_types,true);
		if($exclude!==false){
			unset($post_types[$exclude]);
		}
		return $post_types;
	}
}