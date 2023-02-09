<?php
namespace AIOSEO\Plugin\Common\Standalone;

use AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the SEO Preview feature on the front-end.
 *
 * @since 4.2.8
 */
class SeoPreview {
	/**
	 * Whether this feature is allowed on the current page or not.
	 *
	 * @since 4.2.8
	 *
	 * @var bool
	 */
	private $enable = false;

	/**
	 * Class constructor.
	 *
	 * @since 4.2.8
	 */
	public function __construct() {
		// Hook into `wp` in order to have access to the WP queried object.
		add_action( 'wp', [ $this, 'init' ] );
	}

	/**
	 * Initialize the feature.
	 * Hooked into `wp` action hook.
	 *
	 * @since 4.2.8
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		$allow = [
			'archive',
			'attachment',
			'author',
			'date',
			'dynamic_home',
			'page',
			'search',
			'single',
			'taxonomy',
		];

		if ( ! in_array( aioseo()->helpers->getTemplateType(), $allow, true ) ) {
			return;
		}

		$this->enable = true;

		aioseo()->core->assets->load( 'src/vue/standalone/seo-preview/main.js', [], $this->getVueData() );
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.2.8
	 *
	 * @return array The data.
	 */
	private function getVueData() {
		$queriedObject = get_queried_object();
		$templateType  = aioseo()->helpers->getTemplateType();

		if (
			'taxonomy' === $templateType ||
			'single' === $templateType ||
			'page' === $templateType ||
			'attachment' === $templateType
		) {
			if ( is_a( $queriedObject, 'WP_Term' ) ) {
				$wpObject      = $queriedObject;
				$labels        = get_taxonomy_labels( get_taxonomy( $queriedObject->taxonomy ) );
				$editObjectUrl = get_edit_term_link( $queriedObject, $queriedObject->taxonomy );
			} else {
				$wpObject      = aioseo()->helpers->getPost();
				$labels        = get_post_type_labels( get_post_type_object( $wpObject->post_type ) );
				$editObjectUrl = get_edit_post_link( $wpObject, 'url' );

				if (
					! aioseo()->helpers->isSpecialPage( $wpObject->ID ) &&
					'attachment' !== $templateType
				) {
					$aioseoPost   = Models\Post::getPost( $wpObject->ID );
					$pageAnalysis = ! empty( $aioseoPost->page_analysis ) ? json_decode( $aioseoPost->page_analysis ) : [ 'analysis' => [] ];
					$keyphrases   = Models\Post::getKeyphrasesDefaults( $aioseoPost->keyphrases );
				}
			}

			// Translators: 1 - The singular label for the current post type.
			$editObjectBtnText      = sprintf( esc_html__( 'Edit %1$s', 'all-in-one-seo-pack' ), $labels->singular_name );
			$editGoogleSnippetUrl   = $this->getEditSnippetUrl( $templateType, 'google', $wpObject );
			$editFacebookSnippetUrl = $this->getEditSnippetUrl( $templateType, 'facebook', $wpObject );
			$editTwitterSnippetUrl  = $this->getEditSnippetUrl( $templateType, 'twitter', $wpObject );
		} elseif (
			'archive' === $templateType ||
			'author' === $templateType ||
			'date' === $templateType ||
			'search' === $templateType
		) {
			if ( is_a( $queriedObject, 'WP_User' ) ) {
				$editObjectUrl     = get_edit_user_link( $queriedObject->ID );
				$editObjectBtnText = esc_html__( 'Edit User', 'all-in-one-seo-pack' );
			}

			$editGoogleSnippetUrl = $this->getEditSnippetUrl( $templateType, 'google' );
		} elseif ( 'dynamic_home' === $templateType ) {
			$editGoogleSnippetUrl   = $this->getEditSnippetUrl( $templateType, 'google' );
			$editFacebookSnippetUrl = $this->getEditSnippetUrl( $templateType, 'facebook' );
			$editTwitterSnippetUrl  = $this->getEditSnippetUrl( $templateType, 'twitter' );
		}

		return [
			'currentPost' => [
				'editGoogleSnippetUrl'   => isset( $editGoogleSnippetUrl ) ? $editGoogleSnippetUrl : '',
				'editFacebookSnippetUrl' => isset( $editFacebookSnippetUrl ) ? $editFacebookSnippetUrl : '',
				'editTwitterSnippetUrl'  => isset( $editTwitterSnippetUrl ) ? $editTwitterSnippetUrl : '',
				'editObjectBtnText'      => isset( $editObjectBtnText ) ? $editObjectBtnText : '',
				'editObjectUrl'          => isset( $editObjectUrl ) ? $editObjectUrl : '',
				'keyphrases'             => isset( $keyphrases ) ? $keyphrases : '',
				'page_analysis'          => isset( $pageAnalysis ) ? $pageAnalysis : '',
			],
			'urls'        => [
				'domain'      => aioseo()->helpers->getSiteDomain(),
				'mainSiteUrl' => aioseo()->helpers->getSiteUrl(),
			],
		];
	}

	/**
	 * Get the URL to the place where the snippet details can be edited.
	 *
	 * @since 4.2.8
	 *
	 * @param  string                 $templateType The WP template type {@see WpContext::getTemplateType}.
	 * @param  string                 $snippet      'google', 'facebook' or 'twitter'.
	 * @param  \WP_Post|\WP_Term|null $object       Post or term object.
	 * @return string                               The URL. Returns an empty string if nothing matches.
	 */
	private function getEditSnippetUrl( $templateType, $snippet, $object = null ) {
		$url = '';

		// Bail if `$snippet` doesn't fit requirements.
		if ( ! in_array( $snippet, [ 'google', 'facebook', 'twitter' ], true ) ) {
			return $url;
		}

		// If we're in a post/page/term (not an attachment) we'll have a URL directly to the meta box.
		if (
			'single' === $templateType ||
			'page' === $templateType ||
			'taxonomy' === $templateType
		) {
			$url = in_array( $templateType, [ 'single', 'page' ], true )
				? get_edit_post_link( $object, 'url' ) . '#aioseo-settings'
				: get_edit_term_link( $object, $object->taxonomy ) . '#aioseo-term-settings-field';

			// Default `$queryArgs` for 'google' snippet.
			$queryArgs = [ 'aioseo-tab' => 'general' ];
			if ( in_array( $snippet, [ 'facebook', 'twitter' ], true ) ) {
				$queryArgs = [
					'aioseo-tab' => 'social',
					'social-tab' => $snippet
				];
			}

			return add_query_arg( $queryArgs, $url );
		}

		// If we're in any sort of archive let's point to the global archive editing.
		if (
			'archive' === $templateType ||
			'author' === $templateType ||
			'date' === $templateType ||
			'search' === $templateType
		) {
			return admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/archives';
		}

		// If homepage is set to show the latest posts let's point to the global home page editing.
		if ( 'dynamic_home' === $templateType ) {
			// Default `$url` for 'google' snippet.
			$url = add_query_arg(
				[ 'aioseo-scroll' => 'home-page-settings' ],
				admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/global-settings'
			);

			if ( in_array( $snippet, [ 'facebook', 'twitter' ], true ) ) {
				$url = admin_url( 'admin.php?page=aioseo-social-networks' ) . '#/' . $snippet;
			}

			return $url;
		}

		return $url;
	}

	/**
	 * Returns the "SEO Preview" submenu item data ("node" as WP calls it).
	 *
	 * @since 4.2.8
	 *
	 * @return array The admin bar menu item data or an empty array if this feature is disabled.
	 */
	public function getAdminBarMenuItemNode() {
		if ( ! $this->enable ) {
			return [];
		}

		$title = esc_html__( 'SEO Preview', 'all-in-one-seo-pack' );

		// @TODO Remove 'NEW' after a couple months.
		$title .= '<span class="aioseo-menu-new-indicator">';
		$title .= esc_html__( 'NEW', 'all-in-one-seo-pack' ) . '!';
		$title .= '</span>';

		return [
			'id'     => 'aioseo-seo-preview',
			'parent' => 'aioseo-main',
			'title'  => $title,
			'href'   => '#',
		];
	}
}