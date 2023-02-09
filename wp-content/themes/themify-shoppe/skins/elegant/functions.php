<?php
/**
 * Custom functions specific to the Elegant skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the Elegant skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_elegant_google_fonts( $fonts ) {
	/* translators: If there are characters in your language that are not supported by SortsMillGoudy, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'SortsMillGoudy font: on or off', 'themify' ) ) {
		$fonts['SortsMillGoudy'] = 'Sorts+Mill+Goudy:400,400i:';
	}
	/* translators: If there are characters in your language that are not supported by Prata, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Prata font: on or off', 'themify' ) ) {
		$fonts['prata'] = 'Prata:400';
	}
	/* translators: If there are characters in your language that are not supported by Poppins, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Poppins font: on or off', 'themify' ) ) {
		$fonts['poppins'] = 'Poppins:400,500,600,700';
	}

	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_elegant_google_fonts' );