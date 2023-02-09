<?php
/**
 * Custom functions specific to the Gadget skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_bakery_google_fonts( $fonts ) {
	if ( 'off' !== _x( 'on', 'Poppins: on or off', 'themify' ) ) {
		$fonts['poppins'] = 'Poppins:400,400i,500,600,700';
	}
	if ( 'off' !== _x( 'on', 'Nunito font: on or off', 'themify' ) ) {
		$fonts['Nunito'] = 'Nunito:400,700';
	}
	if ( 'off' !== _x( 'on', 'OoohBaby font: on or off', 'themify' ) ) {
		$fonts['OoohBaby'] = 'Oooh+Baby:400,700';
	}
	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_bakery_google_fonts' );

