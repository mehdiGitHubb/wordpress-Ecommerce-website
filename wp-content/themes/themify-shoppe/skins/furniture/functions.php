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
function themify_theme_furniture_google_fonts( $fonts ) {
	/* translators: If there are characters in your language that are not supported by Lato Web, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Montserrat font: on or off', 'themify' ) ) {
		$fonts['montserrat'] = 'Montserrat:ital,wght@0,300;0,400;0,600;1,700';
	}
	if ( 'off' !== _x( 'on', 'Frank Ruhl Libre font: on or off', 'themify' ) ) {
		$fonts['Frank+Ruhl+Libre'] = 'Frank+Ruhl+Libre:wght@400;0,500;0,700&display=swap';
	}
	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_furniture_google_fonts' );
