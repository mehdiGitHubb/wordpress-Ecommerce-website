<?php
/**
 * Custom functions specific to the Sport skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the Sport skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_sport_google_fonts( $fonts ) {
	/* translators: If there are characters in your language that are not supported by Titillium Web, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'TitilliumWeb font: on or off', 'themify' ) ) {
		$fonts['TitilliumWeb'] = 'Titillium+Web:400,600,700:';
	}
	/* translators: If there are characters in your language that are not supported by Open Sans, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off', 'themify' ) ) {
		$fonts['OpenSans'] = 'Open+Sans:300,400,700';
	}

	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_sport_google_fonts' );

/**
 * Register custom script for the Sport skin
 *
 * @since 1.1
 */
function themify_theme_sport_custom_script() {
	wp_enqueue_script( 'themify-sport-script', themify_enque(THEME_URI . '/skins/sport/js/script.js'), array( 'jquery' ), wp_get_theme()->display( 'Version' ), true );	
}
add_action( 'wp_enqueue_scripts', 'themify_theme_sport_custom_script' );