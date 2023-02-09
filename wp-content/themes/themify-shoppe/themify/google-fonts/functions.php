<?php

if( ! function_exists( 'themify_get_google_font_lists' ) ) :

/**
 * Get google font lists
 * @return array
 */
function themify_get_google_font_lists() {
	return (defined( 'THEMIFY_GOOGLE_FONTS' ) && THEMIFY_GOOGLE_FONTS != true)?array():include( themify_get_google_fonts_file() );
}

/**
 * Return file to use depending if user selected Recommended or Full list in theme settings.
 *
 * @since 2.1.7
 *
 * @return string
 */
function themify_get_google_fonts_file() {
	static $url=null;
	if($url===null){
		$url = 'google-fonts';
		if(!apply_filters( 'themify_google_fonts_full_list', ('full' === themify_get( 'setting-webfonts_list',false,true )) ) ){
			$url.='-recommended';
		}

		/**
		 * Filters the file loaded.
		 * Useful for recovery in case user loaded Full List and their server can't manage it.
		 * @param string $fonts
		 */
		$url= apply_filters( 'themify_google_fonts_file',dirname( __FILE__ ).'/'. $url.'.php' );
	}
	return $url;
}

/**
 * Returns a list of Google Web Fonts
 * @return array
 * @since 1.5.6
 */
function themify_get_google_web_fonts_list() {
	$google_fonts_list = array(
		array( 'value' => '', 'name' => '' ),
		array(
			'value' => '',
			'name' => '--- ' . __( 'Google Fonts', 'themify' ) . ' ---'
		)
	);
	$fonts = themify_get_google_font_lists();
	foreach ( $fonts as $k=>$f ) {
	    $google_fonts_list[] = array(
		    'value' => $k,
		    'name' => $k,
		    'variant' => is_array( $f ) ? $f[1] : array()
	    );
	}

	return apply_filters( 'themify_get_google_web_fonts_list', $google_fonts_list );
}

/**
 * Returns a list of web safe fonts
 * @param bool $only_names Whether to return only the array keys or the values as well
 * @return mixed|void
 * @since 1.0.0
 */
function themify_get_web_safe_font_list( $only_names = false ) {
	$web_safe_font_names = array(
		"Arial, Helvetica, sans-serif",
		"Verdana, Geneva, sans-serif",
		"Georgia, 'Times New Roman', Times, serif",
		"'Times New Roman', Times, serif",
		"Tahoma, Geneva, sans-serif",
		"'Trebuchet MS', Arial, Helvetica, sans-serif",
		"Palatino, 'Palatino Linotype', 'Book Antiqua', serif",
		"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"
	);

	if( ! $only_names ) {
		$web_safe_fonts = array(
			array( 'value' => 'default', 'name' => '', 'selected' => true ),
			array( 'value' => '', 'name' => '--- '.__( 'Web Safe Fonts', 'themify' ) . ' ---' )
		);

		foreach( $web_safe_font_names as $font ) {
			$web_safe_fonts[] = array(
				'value' => $font,
				'name' => $font
			);
		}
	} else {
		$web_safe_fonts = $web_safe_font_names;
	}

	return apply_filters( 'themify_get_web_safe_font_list', $web_safe_fonts );
}

endif;