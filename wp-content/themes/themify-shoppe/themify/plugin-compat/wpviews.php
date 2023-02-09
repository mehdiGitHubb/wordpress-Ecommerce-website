<?php
/**
 * Themify Compatibility Code
 *
 * @package Themify
 */

/**
 * Toolset Views
 * @link https://toolset.com
 */
class Themify_Compat_wpviews {

	static function init() {
		if ( function_exists( 'has_blocks' ) ) {
			add_filter( 'themify_deq_css', [ __CLASS__, 'themify_deq_css' ] );
		}
	}

	/*
	 * @ref #9540
	 */
	public static function themify_deq_css( $css ) {
        if ( ( $key = array_search( 'wp-block-library', $css ) ) !== false ) {
            global $wp_query, $WPV_settings;
            $queried_term = $wp_query->get_queried_object();
            if ( has_blocks( $WPV_settings[ 'view_taxonomy_loop_' . $queried_term->taxonomy ] ) ) {
                unset( $css[ $key ] );
            }
        }

        return $css;
	}
}