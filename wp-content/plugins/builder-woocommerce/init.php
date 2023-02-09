<?php
/*
Plugin Name:     Builder WooCommerce
Plugin URI:      https://themify.me/addons/woocommerce
Version:         3.0.1
Author:          Themify
Author URI:  	 https://themify.me
Description:     Show WooCommerce products anywhere with the Builder. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:     builder-wc
Domain Path:     /languages
WC tested up to: current
Compatibility: 7.0.0
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Woocommerce {

	public static $url;
	public static $version;

	 /**
     * Init Builder Tiles
     */
    public static function init() {
		self::constants();
		add_action( 'plugins_loaded', array( __CLASS__, 'setup' ), 1 );
		add_action( 'init', array( __CLASS__, 'i18n' ) );
		if(is_admin()){
		    add_filter( 'plugin_row_meta', array( __CLASS__, 'themify_plugin_meta'), 10, 2 );
		    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( __CLASS__, 'action_links') );
		}
	}

	public static function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		self::$version = $data[0];
		self::$url = trailingslashit( plugin_dir_url( __FILE__ ) );
	}

	public static function setup() {
		if( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'themify_builder_setup_modules', array( __CLASS__, 'register_module' ) );
		if(is_admin()){
		    add_action( 'wp_ajax_builder_wc_get_terms', array( __CLASS__, 'get_terms' ), 15 );
		}
		add_action( 'themify_builder_active_enqueue', array(__CLASS__, 'admin_enqueue'), 15);
	}

	public static function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
			  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify' ) . '">' . esc_html__( 'View Changelogs', 'themify' ) . '</a>'
			);
	 
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	public static function action_links( $links ) {
		if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
			$tlinks = array(
			 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'themify') .'</a>',
			 );
		} else {
			$tlinks = array(
			 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">'. __('Themify Updater', 'themify') .'</a>',
			 );
		}
		return array_merge( $links, $tlinks );
	}
	public static function i18n() {
		load_plugin_textdomain( 'builder-wc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}


	public static function register_module() {
		$dir = trailingslashit( plugin_dir_path( __FILE__ ) );
                if(method_exists('Themify_Builder_Model', 'add_module')){
                    Themify_Builder_Model::add_module($dir . 'modules/module-products.php' );
                    Themify_Builder_Model::add_module($dir . 'modules/module-product-categories.php' );
                }
                else{
                    Themify_Builder_Model::register_directory('templates', $dir . 'templates');
                    Themify_Builder_Model::register_directory('modules', $dir . 'modules');
                }
               
	}

	public static function admin_enqueue(){
	    themify_enque_script( 'themify-builder-wc-admin', self::$url . 'assets/admin.js',self::$version, array('themify-builder-app-js'));
	    wp_localize_script( 'themify-builder-wc-admin', 'builderWc', array(
			'css' => self::$url . 'assets/admin.css'
	    ));

	}

	public static function get_terms(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    wp_dropdown_categories( array(
		'taxonomy' => 'product_cat',
		'show_option_all' => false,
		'show_count'=>1,
		'hierarchical'=>1,
		'hide_empty' => 1,
		'selected' =>'',
		'id'=>'',
		'value_field' => 'slug'
	    ) );
            wp_die();
	}
}
Builder_Woocommerce::init();
