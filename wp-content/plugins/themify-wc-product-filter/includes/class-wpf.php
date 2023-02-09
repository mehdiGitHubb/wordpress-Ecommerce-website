<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WPF
 * @subpackage WPF/includes
 * @author     Themify <wpf@themify.me>
 */
class WPF {


    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    private static $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of the plugin.
     */
    private static $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the Dashboard and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    private static $options = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return    A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        if ( $instance === null ) {
            $instance = new self;
        }
        return $instance;
    }

    private function __construct() {
    }

	public function init() {
		self::$plugin_name = 'wpf';
        $this->load_dependencies();
        $this->set_locale();

        if ( is_admin() ) {
			$this->define_admin_hooks();
        }
        if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$this->define_public_hooks();
        }
	}

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        $plugindir = plugin_dir_path(dirname(__FILE__));

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $plugindir . 'includes/class-wpf-i18n.php';
        require_once $plugindir . 'includes/class-wpf-utils.php';
        require_once $plugindir . 'includes/class-wpf-options.php';
        require_once $plugindir . 'includes/class-wpf-form.php';
        require_once $plugindir . 'includes/class-wpf-widget.php';
        
        if(is_admin()){
            require_once $plugindir . 'includes/class-wpf-list.php';
            require_once $plugindir . 'admin/class-wpf-admin.php';
        }
        if(!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)){
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once $plugindir . 'public/class-wpf-public.php';
        }
        do_action('wpf_loaded');
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WPF_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new WPF_i18n();
        $plugin_i18n->set_domain($this->get_plugin_name());
        add_action('plugins_loaded', array($plugin_i18n, 'load_plugin_textdomain'));
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return self::$plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return self::$version;
    }

    public function set_version( $version ) {
        self::$version = $version;
    }

    /**
     * Register all of the hooks related to the dashboard functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        new WPF_Admin(self::$plugin_name, self::$version);
       
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
		WPF_Public::get_instance();
    }

    public static function get_option() {
        if (!isset(self::$options)) {
            self::$options = new WPF_Options(self::$plugin_name, self::$version);
        }
        return self::$options;
    }

    /**
     * Returns current plugin version.
     * 
     * @return string Plugin version
     */
    public static function get_plugin_version($plugin_url) {
        $plugin_data = get_file_data($plugin_url, array('ver' => 'Version'));
        return $plugin_data['ver'];
    }

}
