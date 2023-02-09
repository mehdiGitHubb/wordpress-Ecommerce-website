<?php
/**
 * Custom functions specific to the Music skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_music_google_fonts( $fonts ) {
	/* translators: If there are characters in your language that are not supported by Poppins, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Poppins font: on or off', 'themify' ) ) {
		$fonts['poppins'] = 'Poppins:400,500,600,700';
	}	
	/* translators: If there are characters in your language that are not supported by Libre Franklin, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Libre Franklin font: on or off', 'themify' ) ) {
		$fonts['libre-franklin'] = 'Libre+Franklin:400,300,500,600,700';
	}

	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_music_google_fonts' );

function themify_theme_custom_excerpt_length( $length ) {
    return 12;
}
add_filter( 'excerpt_length', 'themify_theme_custom_excerpt_length', 999 );