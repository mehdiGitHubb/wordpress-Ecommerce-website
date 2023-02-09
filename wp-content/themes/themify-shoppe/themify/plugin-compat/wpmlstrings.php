<?php
/**
 * Themify Compatibility Code
 *
 * @package Themify
 */

/**
 * WPML String Translation
 * @link https://wpml.org/
 */
class Themify_Compat_wpmlstrings {

	static function init() {
		if ( function_exists( 'icl_register_string' ) ) {
			self::register_wpml_strings( 'Themify', 'Themify Option', themify_get_data() );
		}
	}

    /**
     * Make dynamic strings in Themify theme available for translation with WPML String Translation
     * @param $context
     * @param $name
     * @param $value
     * @since 1.5.3
     */
    public static function register_wpml_strings( $context, $name, $value ) {
		$value = maybe_unserialize( $value );
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				self::register_wpml_strings( $context, $k, $v );
			}
		} else {
			$translatable = array(
				'setting-footer_text_left',
				'setting-footer_text_right',
				'setting-homepage_welcome',
				'setting-action_text',
				'setting-default_more_text',
			);
			foreach ( array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten') as $option ) {
				$translatable[] = 'setting-slider_images_' . $option . '_title';
				$translatable[] = 'setting-header_slider_images_' . $option . '_title';
				$translatable[] = 'setting-footer_slider_images_' . $option . '_title';
			}
			if (stripos( $name, 'title_themify-link' ) || in_array( $name, $translatable, true ) ) {
				icl_register_string( $context, $name, $value );
			}
		}
    }
}