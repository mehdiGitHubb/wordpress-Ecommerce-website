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
function themify_theme_resume_google_fonts( $fonts ) {
	if ( 'off' !== _x( 'on', 'Jost font: on or off', 'themify' ) ) {
		$fonts['jost'] = 'Jost:100,300,400,500,700,900';
	}	
	if ( 'off' !== _x( 'on', 'DMSerifDisplay font: on or off', 'themify' ) ) {
		$fonts['DMSerifDisplay'] = 'DM+Serif+Display:400,400i';
	}
	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_resume_google_fonts' );
