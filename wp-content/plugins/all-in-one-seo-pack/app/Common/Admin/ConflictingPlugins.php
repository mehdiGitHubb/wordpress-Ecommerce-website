<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Checks for conflicting plugins.
 *
 * @since 4.0.0
 */
class ConflictingPlugins {
	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		// We don't want to trigger our notices when not in the admin.
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize the conflicting plugins check.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Only do this for users who can install/deactivate plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$conflictingPlugins = $this->getAllConflictingPlugins();

		$notification = Models\Notification::getNotificationByName( 'conflicting-plugins' );
		if ( empty( $conflictingPlugins ) ) {
			if ( ! $notification->exists() ) {
				return;
			}

			Models\Notification::deleteNotificationByName( 'conflicting-plugins' );

			return;
		}

		aioseo()->notices->conflictingPlugins( $conflictingPlugins );
	}

	/**
	 * Get a list of all conflicting plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of conflicting plugins.
	 */
	protected function getAllConflictingPlugins() {
		$conflictingSeoPlugins     = $this->getConflictingPlugins( 'seo' );
		$conflictingSitemapPlugins = [];

		if (
			aioseo()->options->sitemap->general->enable ||
			aioseo()->options->sitemap->rss->enable
		) {
			$conflictingSitemapPlugins = $this->getConflictingPlugins( 'sitemap' );
		}

		return array_merge( $conflictingSeoPlugins, $conflictingSitemapPlugins );
	}

	/**
	 * Get a list of conflicting plugins for AIOSEO.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $type A type to look for.
	 * @return array        An array of conflicting plugins.
	 */
	public function getConflictingPlugins( $type ) {
		$activePlugins = get_option( 'active_plugins' );

		$conflictingPlugins = [];
		switch ( $type ) {
			case 'seo':
				$conflictingPlugins = [
					'Yoast SEO'         => 'wordpress-seo/wp-seo.php',
					'Yoast SEO Premium' => 'wordpress-seo-premium/wp-seo-premium.php',
					'Rank Math SEO'     => 'seo-by-rank-math/rank-math.php',
					'SEOPress'          => 'wp-seopress/seopress.php',
					'The SEO Framework' => 'autodescription/autodescription.php',
				];
				break;
			case 'sitemap':
				$conflictingPlugins = [
					'Google XML Sitemaps'          => 'google-sitemap-generator/sitemap.php',
					'XML Sitemap & Google News'    => 'xml-sitemap-feed/xml-sitemap.php',
					'Google XML Sitemap Generator' => 'www-xml-sitemap-generator-org/www-xml-sitemap-generator-org.php',
					'Sitemap by BestWebSoft'       => 'google-sitemap-plugin/google-sitemap-plugin.php',
				];
				break;
		}

		return array_intersect( $conflictingPlugins, $activePlugins );
	}
}