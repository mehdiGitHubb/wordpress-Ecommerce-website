<?php
/**
 * Custom functions specific to the Furniture skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the Furniture skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_artist_google_fonts( $fonts ) {
	if ( 'off' !== _x( 'on', 'Jost font: on or off', 'themify' ) ) {
		$fonts['Jost'] = 'Jost:400,400i,500,600,700';
	}
	if ( 'off' !== _x( 'on', 'Playfair Display font: on or off', 'themify' ) ) {
		$fonts['playfair-display'] = 'Playfair+Display:400,500,600,700';
	}
	if ( 'off' !== _x( 'on', 'Playfair Display SC font: on or off', 'themify' ) ) {
		$fonts['Playfair+Display+SC'] = 'Playfair+Display+SC:400,700';
	}
	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_artist_google_fonts' );