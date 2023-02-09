<?php
namespace AIOSEO\Plugin\Lite\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Filters class with methods that are called.
 *
 * @since 4.0.0
 */
class Filters extends CommonMain\Filters {
	/**
	 * Action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions    An array of existing actions.
	 * @param  string $pluginFile The plugin file we are modifying.
	 * @return array              An array of action links.
	 */
	public function pluginRowMeta( $actions, $pluginFile ) {
		$actionLinks = [
			'settings' => [
				// Translators: This is an action link users can click to open a feature request.
				'label' => __( 'Suggest a Feature', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'suggest-a-feature/', 'plugin-row-meta', 'Feature' ),
			]
		];

		return $this->parseActionLinks( $actions, $pluginFile, $actionLinks );
	}

	/**
	 * Action links for the plugins page.
	 *
	 * @param  array  $actions    An array of existing actions.
	 * @param  string $pluginFile The plugin file we are modifying.
	 * @return array              An array of action links.
	 */
	public function pluginActionLinks( $actions, $pluginFile ) {
		$actionLinks = [
			'settings'   => [
				'label' => __( 'SEO Settings', 'all-in-one-seo-pack' ),
				'url'   => get_admin_url( null, 'admin.php?page=aioseo-settings' ),
			],
			'support'    => [
				// Translators: This is an action link users can click to open our premium support.
				'label' => __( 'Support', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'contact/', 'plugin-action-links', 'Support' ),
			],
			'docs'       => [
				// Translators: This is an action link users can click to open our general documentation page.
				'label' => __( 'Documentation', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/', 'plugin-action-links', 'Documentation' ),
			],
			'proupgrade' => [
				// Translators: This is an action link users can click to purchase a license for All in One SEO Pro.
				'label' => __( 'Upgrade to Pro', 'all-in-one-seo-pack' ),
				'url'   => apply_filters( 'aioseo_upgrade_link', aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', 'plugin-action-links', 'Upgrade', false ) ),
			]
		];

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $this->parseActionLinks( $actions, $pluginFile, $actionLinks, 'before' );
	}
}