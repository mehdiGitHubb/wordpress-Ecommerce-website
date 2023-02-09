<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles user related API routes.
 *
 * @since 4.2.8
 */
class User {
	/**
	 * Get the user image.
	 *
	 * @since 4.2.8
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function getUserImage( $request ) {
		$args = $request->get_params();

		if ( empty( $args['userId'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No user ID was provided.'
			], 400 );
		}

		$url = get_avatar_url( $args['userId'] );

		return new \WP_REST_Response( [
			'success' => true,
			'url'     => is_array( $url ) ? $url[0] : $url,
		], 200 );
	}
}