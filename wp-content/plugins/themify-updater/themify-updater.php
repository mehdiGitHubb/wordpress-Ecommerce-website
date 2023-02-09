<?php

/**
 * Plugin Name:       Themify Updater
 * Plugin URI:        https://themify.me/docs/themify-updater-documentation
 * Description:       This plugin allows you to auto update all Themify themes and plugins with a license key.
 * Version:           1.4.3
 * Author:            Themify
 * Author URI:        https://themify.me
 * Text Domain:       themify-updater
 * Domain Path:       /languages
 */
 
 // If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * The code that runs during plugin activation.
 */
function activate_themify_updater() {
    delete_transient('themify_updater_cache');

	/* auto load config */
	$path = plugin_dir_path( __FILE__ );
	if ( file_exists( $path . 'config.json' ) ) {
		if ( ! get_option( 'themify_updater_licence' ) ) {
			$settings = file_get_contents( $path . 'config.json' );
			update_option( 'themify_updater_licence', $settings );
		}
		@unlink( $path . 'config.json' );
	}
}
register_activation_hook(__FILE__, 'activate_themify_updater');

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_themify_updater() {
    delete_transient('themify_updater_cache');
}
register_deactivation_hook(__FILE__, 'deactivate_themify_updater');

function themify_updater_init() {
	load_plugin_textdomain( 'themify-updater', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * dashboard-specific hooks, and public-facing site hooks.
	 */
	$path = plugin_dir_path(__FILE__);
	require $path . 'includes/class.utils.php';
	require $path . 'includes/class.cache.php';
	require $path . 'includes/class.request.php';
	require $path . 'includes/class.notifications.php';
	require $path . 'includes/class.version.php';
	require $path . 'includes/class.license.php';
	require $path . 'includes/class.promotion.php';
	require $path . 'includes/themify.updater.php';
	require $path . 'includes/class.auto.update.php';

	if( !function_exists('get_plugin_data') || !function_exists('is_plugin_active_for_network') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$themify_updater_data = get_plugin_data( __FILE__ );

	define('THEMIFY_UPDATER_DIR_PATH', dirname( __FILE__ ) );
	define('THEMIFY_UPDATER_VERSION', $themify_updater_data['Version'] );
	define('THEMIFY_UPDATER_DIR_URL', plugin_dir_url(__FILE__));
	define('THEMIFY_UPDATER_NETWORK_ENABLED', is_plugin_active_for_network(basename(dirname(__FILE__)).'/'.basename(__FILE__)));

	unset($themify_updater_data);

	$themify_updater = Themify_Updater::get_instance();
}
add_action( 'plugins_loaded', 'themify_updater_init' );

function themify_updater_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
		  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify-updater' ) . '">' . esc_html__( 'View Changelogs', 'themify-updater' ) . '</a>'
		);

		return array_merge( $links, $row_meta );
	}
	return (array) $links;
}
add_filter( 'plugin_row_meta', 'themify_updater_plugin_row_meta', 10, 2 );

function themify_updater_action_links( $links ) {
	$tlinks = array(
	 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'themify-updater') .'</a>',
	 );
	return array_merge( $links, $tlinks );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'themify_updater_action_links' );