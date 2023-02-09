<?php
namespace AIOSEO\Plugin\Common\Main;

use AIOSEO\Plugin\Common\Models as Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
abstract class Filters {
	/**
	 * The plugin we are checking.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * ID of the WooCommerce product that is being duplicated.
	 *
	 * @since 4.1.4
	 *
	 * @var integer
	 */
	private static $originalProductId;

	/**
	 * Construct method.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_filter( 'plugin_row_meta', [ $this, 'pluginRowMeta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . AIOSEO_PLUGIN_BASENAME, [ $this, 'pluginActionLinks' ], 10, 2 );

		// Genesis theme compatibility.
		add_filter( 'genesis_detect_seo_plugins', [ $this, 'genesisTheme' ] );

		// WeGlot compatibility.
		if ( preg_match( '#(/default\.xsl)$#i', $_SERVER['REQUEST_URI'] ) ) {
			add_filter( 'weglot_active_translation_before_treat_page', '__return_false' );
		}

		if ( preg_match( '#(\.xml)$#i', $_SERVER['REQUEST_URI'] ) ) {
			add_filter( 'jetpack_boost_should_defer_js', '__return_false' );
		}

		// GoDaddy CDN compatibility.
		add_filter( 'wpaas_cdn_file_ext', [ $this, 'goDaddySitemapXml' ] );

		// Duplicate Post integration.
		add_action( 'dp_duplicate_post', [ $this, 'duplicatePost' ], 10, 2 );
		add_action( 'dp_duplicate_page', [ $this, 'duplicatePost' ], 10, 2 );
		add_action( 'woocommerce_product_duplicate_before_save', [ $this, 'scheduleDuplicateProduct' ], 10, 2 );

		add_action( 'init', [ $this, 'resetUserBBPress' ], -1 );

		// Bypass the JWT Auth plugin's unnecessary restrictions. https://wordpress.org/plugins/jwt-auth/
		add_filter( 'jwt_auth_default_whitelist', [ $this, 'allowRestRoutes' ] );

		// Clear the site authors cache.
		add_action( 'profile_update', [ $this, 'clearAuthorsCache' ] );
		add_action( 'user_register', [ $this, 'clearAuthorsCache' ] );

		add_filter( 'aioseo_public_post_types', [ $this, 'removeInvalidPublicPostTypes' ] );
		add_filter( 'aioseo_public_taxonomies', [ $this, 'removeInvalidPublicTaxonomies' ] );

		// Disable Jetpack sitemaps module.
		if ( aioseo()->options->sitemap->general->enable ) {
			add_filter( 'jetpack_get_available_modules', [ $this, 'disableJetpackSitemaps' ] );
		}
	}

	/**
	 * Resets the current user if bbPress is active.
	 * We have to do this because our calls to wp_get_current_user() set the current user early and this breaks core functionality in bbPress.
	 *
	 *
	 * @since 4.1.5
	 *
	 * @return void
	 */
	public function resetUserBBPress() {
		if ( function_exists( 'bbpress' ) ) {
			global $current_user;
			$current_user = null;
		}
	}

	/**
	 * Duplicates the model when duplicate post is triggered.
	 *
	 * @since 4.1.1
	 *
	 * @param  integer $newPostId    The new post ID.
	 * @param  WP_Post $originalPost The original post object.
	 * @return void
	 */
	public function duplicatePost( $newPostId, $originalPost ) {
		$originalPostId     = is_object( $originalPost ) ? $originalPost->ID : $originalPost;
		$originalAioseoPost = Models\Post::getPost( $originalPostId );
		if ( ! $originalAioseoPost->exists() ) {
			return;
		}

		$newAioseoPost = Models\Post::getPost( $newPostId );
		$columns       = $originalAioseoPost->getColumns();
		foreach ( $columns as $column => $value ) {
			// Skip the ID column.
			if ( 'id' === $column ) {
				continue;
			}

			if ( 'post_id' === $column ) {
				$newAioseoPost->$column = $newPostId;
				continue;
			}

			$newAioseoPost->$column = $originalAioseoPost->$column;
		}
		$newAioseoPost->save();
	}

	/**
	 * Schedules an action to duplicate our meta after the duplicated WooCommerce product has been saved.
	 *
	 * @since 4.1.4
	 *
	 * @param  \WP_Product $newProduct      The new, duplicated product.
	 * @param  \WP_Product $originalProduct The original product.
	 * @return void
	 */
	public function scheduleDuplicateProduct( $newProduct, $originalProduct ) {
		self::$originalProductId = $originalProduct->get_id();
		add_action( 'wp_insert_post', [ $this, 'duplicateProduct' ], 10, 2 );
	}

	/**
	 * Duplicates our meta for the new WooCommerce product.
	 *
	 * @since 4.1.4
	 *
	 * @param  integer  $postId The new post ID.
	 * @param  \WP_Post $post   The new post object.
	 * @return void
	 */
	public function duplicateProduct( $postId, $post ) {
		if ( ! self::$originalProductId || 'product' !== $post->post_type ) {
			return;
		}

		$this->duplicatePost( $postId, self::$originalProductId );
	}

	/**
	 * Disable SEO inside the Genesis theme if it's running.
	 *
	 * @since 4.0.3
	 *
	 * @param  array $array An array of checks.
	 * @return array        An array with our function added.
	 */
	public function genesisTheme( $array ) {
		if ( empty( $array ) || ! isset( $array['functions'] ) ) {
			return $array;
		}

		$array['functions'][] = 'aioseo';

		return $array;
	}

	/**
	 * Remove XML from the GoDaddy CDN so our urls remain intact.
	 *
	 * @since 4.0.5
	 *
	 * @param  array $extensions The original extensions list.
	 * @return array             The extensions list without xml.
	 */
	public function goDaddySitemapXml( $extensions ) {
		$key = array_search( 'xml', $extensions, true );
		unset( $extensions[ $key ] );

		return $extensions;
	}

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginRowMeta( $actions, $pluginFile );

	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @return array The array of actions.
	 */
	abstract public function pluginActionLinks( $actions, $pluginFile );

	/**
	 * Parse the action links.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions
	 * @param  string $pluginFile
	 * @param
	 * @return array
	 */
	protected function parseActionLinks( $actions, $pluginFile, $actionLinks = [], $position = 'after' ) {
		if ( empty( $this->plugin ) ) {
			$this->plugin = AIOSEO_PLUGIN_BASENAME;
		}

		if ( $this->plugin === $pluginFile && ! empty( $actionLinks ) ) {
			foreach ( $actionLinks as $key => $value ) {
				$link = [
					$key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>'
				];

				$actions = 'after' === $position ? array_merge( $actions, $link ) : array_merge( $link, $actions );
			}
		}

		return $actions;
	}

	/**
	 * Add our routes to this plugins allow list.
	 *
	 * @since 4.1.4
	 *
	 * @param  array $allowList The original list.
	 * @return array            The modified list.
	 */
	public function allowRestRoutes( $allowList ) {
		return array_merge( $allowList, [
			'/aioseo/'
		] );
	}

	/**
	 * Clear the site authors cache when user is updated or registered.
	 *
	 * @since 4.1.8
	 *
	 * @return void
	 */
	public function clearAuthorsCache() {
		aioseo()->core->cache->delete( 'site_authors' );
	}

	/**
	 * Filters out post types that aren't really public when getPublicPostTypes() is called.
	 *
	 * @since 4.1.9
	 *
	 * @param  array[Object]|array[string] $postTypes The post types.
	 * @return array[Object]|array[string]            The filtered post types.
	 */
	public function removeInvalidPublicPostTypes( $postTypes ) {
		$elementorEnabled = isset( aioseo()->standalone->pageBuilderIntegrations['elementor'] ) &&
			aioseo()->standalone->pageBuilderIntegrations['elementor']->isPluginActive();

		if ( ! $elementorEnabled ) {
			return $postTypes;
		}

		$postTypesToRemove = [
			'elementor_library'
		];

		foreach ( $postTypes as $index => $postType ) {
			if ( is_string( $postType ) && in_array( $postType, $postTypesToRemove, true ) ) {
				unset( $postTypes[ $index ] );
				continue;
			}

			if ( is_array( $postType ) && in_array( $postType['name'], $postTypesToRemove, true ) ) {
				unset( $postTypes[ $index ] );
			}
		}

		return array_values( $postTypes );
	}

	/**
	 * Filters out taxonomies that aren't really public when getPublicTaxonomies() is called.
	 *
	 * @since 4.2.4
	 *
	 * @param  array[Object]|array[string] $taxonomies The taxonomies.
	 * @return array[Object]|array[string]             The filtered taxonomies.
	 */
	public function removeInvalidPublicTaxonomies( $taxonomies ) {
		// Check if the Avada Builder plugin is enabled.
		if ( ! defined( 'FUSION_BUILDER_VERSION' ) ) {
			return $taxonomies;
		}

		$taxonomiesToRemove = [
			'fusion_tb_category',
			'element_category',
			'template_category'
		];

		foreach ( $taxonomies as $index => $taxonomy ) {
			if ( is_string( $taxonomy ) && in_array( $taxonomy, $taxonomiesToRemove, true ) ) {
				unset( $taxonomies[ $index ] );
				continue;
			}

			if ( is_array( $taxonomy ) && in_array( $taxonomy['name'], $taxonomiesToRemove, true ) ) {
				unset( $taxonomies[ $index ] );
			}
		}

		return array_values( $taxonomies );
	}

	/**
	 * Disable Jetpack sitemaps module.
	 *
	 * @since 4.2.2
	 */
	public function disableJetpackSitemaps( $active ) {
		unset( $active['sitemaps'] );

		return $active;
	}
}