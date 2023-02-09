<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media class.
 *
 * @since 4.0.0
 */
class Media {
	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'attachmentRedirect' ], 1 );
	}

	/**
	 * If the user wants to redirect attachment pages, this is where we do it.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function attachmentRedirect() {
		if ( ! is_attachment() ) {
			return;
		}

		if (
			! aioseo()->dynamicOptions->searchAppearance->postTypes->has( 'attachment' )
		) {
			return;
		}

		$redirect = aioseo()->dynamicOptions->searchAppearance->postTypes->attachment->redirectAttachmentUrls;
		if ( 'disabled' === $redirect ) {
			return;
		}

		if ( 'attachment' === $redirect ) {
			$url = wp_get_attachment_url( get_queried_object_id() );
			if ( empty( $url ) ) {
				return;
			}

			return wp_safe_redirect( $url, 301, AIOSEO_PLUGIN_SHORT_NAME );
		}

		global $post;
		if ( ! empty( $post->post_parent ) ) {
			wp_safe_redirect( urldecode( get_permalink( $post->post_parent ) ), 301 );
		}
	}
}