<?php
namespace AIOSEO\Plugin\Common\ThirdParty\Cache;

use stdClass;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-party class for plugin WP Fastest Cache.
 *
 * @since 4.2.5
 */
final class WpFastestCache {
	/**
	 * All current custom excluded pages.
	 *
	 * @since 4.2.5
	 *
	 * @var object[]
	 */
	private $exclusionRules = [];

	/**
	 * The relative file path.
	 *
	 * @since 4.2.7
	 *
	 * @var string
	 */
	private $relativeFilePath = '';

	/**
	 * The WPFastestCache instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Object
	 */
	private $wpFastestCache = null;

	/**
	 * Class constructor.
	 *
	 * @since 4.2.5
	 *
	 * @param string $relativeFilePath Path to the plugin file relative to the plugins directory.
	 */
	public function __construct( $relativeFilePath ) {
		$this->relativeFilePath = trim( (string) $relativeFilePath );

		if ( ! empty( $GLOBALS['wp_fastest_cache'] ) ) {
			$this->wpFastestCache = $GLOBALS['wp_fastest_cache'];
		}
	}

	/**
	 * Force URI to be excluded from this third-party
	 * (@link https://wordpress.org/plugins/wp-fastest-cache/) plugin cache.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $uri Request URI.
	 * @return bool        Returns false if something goes wrong and the URI is not excluded.
	 */
	public function excludeUri( $uri ) {
		// Bail if there's no instance of WP Fastest Cache anymore.
		if ( ! isset( $this->wpFastestCache ) ) {
			return false;
		}

		// Bail if the current version of this plugin is below 0.8.6.6.
		if ( version_compare( $this->getVersion(), '0.8.6.6', '<' ) ) {
			return false;
		}

		// Bail if method `modify_htaccess_for_exclude()` doesn't exist anymore.
		if ( ! method_exists( $this->wpFastestCache, 'modify_htaccess_for_exclude' ) ) {
			return false;
		}

		// Build new rule.
		$newRule          = new stdClass();
		$newRule->prefix  = 'contain';
		$newRule->content = $uri;
		$newRule->type    = 'page';

		if ( ! $this->exclusionRuleExists( $newRule ) ) {
			$this->addExclusionRule( $newRule );
		}

		return true;
	}

	/**
	 * Check if a custom "exclusion rule" already exists within a set of rules.
	 *
	 * @since 4.2.5
	 *
	 * @param  object $rule The new rule set as an object.
	 * @return bool         Returns true only if the needle is found within the haystack of rules.
	 */
	private function exclusionRuleExists( $rule ) {
		$needleAsArray = (array) $rule;

		asort( $needleAsArray );

		foreach ( $this->getExclusionRules() as $exclusionRule ) {
			$ruleAsArray = (array) $exclusionRule;

			asort( $ruleAsArray );

			if ( $needleAsArray === $ruleAsArray ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get this third-party plugin version.
	 *
	 * @since 4.2.5
	 *
	 * @return string Returns the current plugin version or '0.0' if it's not found.
	 */
	private function getVersion() {
		$version = '0.0';

		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			$pluginFile = trailingslashit( WP_PLUGIN_DIR ) . $this->relativeFilePath;
			$data       = get_plugin_data( $pluginFile, false, false );

			if ( ! empty( $data['Version'] ) ) {
				$version = (string) $data['Version'];
			}
		}

		return $version;
	}

	/**
	 * Get and keep all current custom excluded pages from this third-party plugin.
	 *
	 * @since 4.2.5
	 *
	 * @return object[] An array containing the rules (each rule is an object).
	 */
	private function getExclusionRules() {
		if ( empty( $this->exclusionRules ) ) {
			$this->exclusionRules = json_decode( get_option( 'WpFastestCacheExclude' ) ) ?: [];
		}

		return $this->exclusionRules;
	}

	/**
	 * Save the new rule in the database.
	 *
	 * @since 4.2.5
	 *
	 * @param  object $rule The new rule set as an object.
	 * @return void
	 */
	private function addExclusionRule( $rule ) {
		$this->exclusionRules = array_merge( $this->getExclusionRules(), [ $rule ] );

		update_option( 'WpFastestCacheExclude', wp_json_encode( $this->exclusionRules ) );

		// Write this URI exception to .htaccess to make sure WP is loaded cache-less (works on Apache).
		$this->wpFastestCache->modify_htaccess_for_exclude();
	}
}