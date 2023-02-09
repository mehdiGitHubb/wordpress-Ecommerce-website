<?php
/*
 * Plugin Name: Advanced Product Fields for WooCommerce
 * Plugin URI: https://www.studiowombat.com/plugin/advanced-product-fields-for-woocommerce/?utm_source=apffree&utm_medium=plugin&utm_campaign=plugins
 * Description: Customize WooCommerce product pages with powerful and intuitive fields ( = product add-ons).
 * Version: 1.5.8
 * Author: StudioWombat
 * Author URI: https://www.studiowombat.com/?utm_source=apffree&utm_medium=plugin&utm_campaign=plugins
 * Text Domain: advanced-product-fields-for-woocommerce
 * WC requires at least: 3.6.0
 * WC tested up to: 7.3
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function SW_WAPF_auto_loader ($class_name) {
    // Not loading a class from our plugin.
    if ( !is_int(strpos( $class_name, 'SW_WAPF')) )
        return;
    // Remove root namespace as we don't have that as a folder.
    $class_name = str_replace('SW_WAPF\\','',$class_name);
    $class_name = str_replace('\\','/',strtolower($class_name)) .'.php';
    // Get only the file name.
    $pos =  strrpos($class_name, '/');
    $file_name = is_int($pos) ? substr($class_name, $pos + 1) : $class_name;
    // Get only the path.
    $path = str_replace($file_name,'',$class_name);
    // Append 'class-' to the file name and replace _ with -
    $new_file_name = 'class-'.str_replace('_','-',$file_name);
    // Construct file path.
    $file_path = plugin_dir_path(__FILE__)  . str_replace('\\', DIRECTORY_SEPARATOR, $path . strtolower($new_file_name));

    if (file_exists($file_path))
        require_once($file_path);
}

spl_autoload_register('SW_WAPF_auto_loader');

function wapf() {

    // version
    $version = '1.5.8';

    // globals
    global $wapf;

    // initialize
    if( !isset($wapf) ) {
        $wapf = new \SW_WAPF\WAPF();
        $wapf->initialize($version, __FILE__);
    }

    return $wapf;

}

// initialize
wapf();
