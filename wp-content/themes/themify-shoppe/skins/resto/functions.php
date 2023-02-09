<?php
/**
 * Custom functions specific to the Coffee skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_resto_google_fonts( $fonts ) {
	if ( 'off' !== _x( 'on', 'Public Sans: on or off', 'themify' ) ) {
		$fonts['public-sans'] = 'Public+Sans:400,700';
	}
	if ( 'off' !== _x( 'on', 'Playfair Display font: on or off', 'themify' ) ) {
		$fonts['playfair-display'] = 'Playfair+Display:400,400i,500,500i,700,700i,900,900i';
	}
	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_resto_google_fonts' );
