<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains WordPress Post Type helpers.
 *
 * @since 4.2.4
 */
trait PostType {
	/**
	 * Returns a post type feature.
	 *
	 * @since 4.2.4
	 *
	 * @param  string|\WP_Post_Type $postType The post type.
	 * @param  string               $feature  The feature to find.
	 * @return mixed|false                    The post type feature or false if not found.
	 */
	public function getPostTypeFeature( $postType, $feature ) {
		if ( is_string( $postType ) ) {
			$postType = get_post_type_object( $postType );
		}

		if ( ! is_a( $postType, 'WP_Post_Type' ) || ! isset( $postType->$feature ) ) {
			return false;
		}

		return $postType->$feature;
	}
}