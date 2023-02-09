<?php
/**
 * Custom functions specific to the Craft skin
 *
 * @package Themify Shoppe
 */

/**
 * Load Google web fonts required for the Craft skin
 *
 * @since 1.4.9
 * @return array
 */
function themify_theme_craft_google_fonts( $fonts ) {
	/* translators: If there are characters in your language that are not supported by Lato Web, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'themify' ) ) {
		$fonts['Lato'] = 'Lato:300,400,700,900';
	}
	/* translators: If there are characters in your language that are not supported by Karla Web, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Karla font: on or off', 'themify' ) ) {
		$fonts['Karla'] = 'Karla:400,700';
	}

	return $fonts;
}
add_filter( 'themify_google_fonts', 'themify_theme_craft_google_fonts' );