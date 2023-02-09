<?php

/**
 * The plugin options helper class
 *
 *
 * @package    WPF
 * @subpackage WPF/includes
 * @author     Themify
 */
class WPF_Options {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;
    
    private static $settings_key = '';


    private function __construct($plugin_name,$version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        self::$settings_key = $this->plugin_name.'_template';
    }

    public  function get($recreate=false){
        static $options = null;
        if(is_null($options) || $recreate){
            $options = get_option(self::$settings_key);
        }
        return $options?$options:array();
    }
    
    public function set($value){
        return update_option(self::$settings_key, $value);
    }
    
    public static function get_option($plugin_name,$version){
        static $object = NULL;
        if(is_null($object)){
            $object = new self($plugin_name,$version);
        }
        return $object;
    }
    
    public function unique_name($name){
        
        $name = sanitize_text_field($name);
        $name = str_replace('-', '_', sanitize_title($name));
        $options = $this->get();
        if (isset($options[$name])) {
            $i = 1;
            while (true) {
                if (!isset($options[$name . '_' . $i])) {
                    $name = $name . '_' . $i;
                    break;
                }
                $i++;
            }
        }
        return $name;
    }

}
