<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_BWPMinify {

	static function init() {
		// Only apply the filter when WP Multisite with subdirectory install.
		if ( defined( 'SUBDOMAIN_INSTALL' ) && ! SUBDOMAIN_INSTALL ) {
			add_filter( 'bwp_minify_get_src', array( __CLASS__, 'bwp_minify_get_src' ) );
		}
	}

	/**
	 * Modify the src for builder stylesheet.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public static function bwp_minify_get_src( $string ) {
		$split_string = explode( ',', $string );
		$found_src = array();
		foreach( $split_string as $src ) {
			if ( preg_match( '/^files\/themify-css/', $src ) ) {
							$found_src[] = $src;
			}
		}
		if ( !empty( $found_src )) {
			$upload_dir = themify_upload_dir();
			$base_path = substr( $upload_dir['basedir'], strpos( $upload_dir['basedir'], 'wp-content' ) );
			foreach ( $found_src as $replace_src ) {
				$key = array_search( $replace_src, $split_string );
				if ( $key !== false ) {
					$split_string[ $key ] = trailingslashit( $base_path ) . str_replace( 'files/themify-css', 'themify-css', $split_string[ $key ] );
				}
			}
			$string = implode( ',', $split_string );
		}
		return $string;
	}
}