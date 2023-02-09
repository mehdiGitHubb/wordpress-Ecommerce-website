<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains i18n and language (code) helper methods.
 *
 * @since 4.1.4
 */
trait Language {
	/**
	 * Returns the language of the current response.
	 *
	 * @since 4.1.4
	 *
	 * @return string The language code.
	 */
	public function currentLanguageCode() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.0', '<' ) ) {
			return get_locale();
		}

		return determine_locale(); // phpcs:ignore AIOSEO.WpFunctionUse.NewFunctions.determine_localeFound
	}

	/**
	 * Returns the language of the current response in BCP 47 format.
	 *
	 * @since 4.1.4
	 *
	 * @return string The language code in BCP 47 format.
	 */
	public function currentLanguageCodeBCP47() {
		return str_replace( '_', '-', $this->currentLanguageCode() );
	}
}