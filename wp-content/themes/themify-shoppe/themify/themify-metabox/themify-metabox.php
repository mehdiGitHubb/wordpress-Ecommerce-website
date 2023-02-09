<?php
/*
Plugin Name:  Themify Metabox API
Version:      1.0.4 
Author:       Themify
Author URI:   https://themify.me/
Description:  Generate custom metaboxes for admin pages easily and efficiently.
Text Domain:  themify
Domain Path:  /languages
License:      GNU General Public License v2.0
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) || exit;

if( ! defined( 'THEMIFY_METABOX_DIR' ) ) {
	define( 'THEMIFY_METABOX_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if( ! defined( 'THEMIFY_METABOX_URI' ) ) {
	define( 'THEMIFY_METABOX_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if( ! function_exists( 'themify_metabox_bootstrap' ) ) :
/**
 * Load and bootstrap Themify Metabox API
 *
 * @since 1.0
 */
function themify_metabox_bootstrap() {
	if( ! class_exists( 'Themify_Metabox' ) ) {
		require_once( THEMIFY_METABOX_DIR . 'includes/themify-metabox-core.php' );
	}
}
endif;
add_action( 'after_setup_theme', 'themify_metabox_bootstrap', 20 );