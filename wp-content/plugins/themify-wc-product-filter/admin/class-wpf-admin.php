<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WPF
 * @subpackage WPF/admin
 * @author     Themify <wpf@themify.me>
 */
class WPF_Admin {

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
    private $columns = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name
     * @param string $version
     *
     * @private param string $plugin_name The name of this plugin.
     * @private param string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 11);
        add_action('wp_ajax_wpf_get_list',array($this,'get_templates'));
        add_action('wp_ajax_wpf_add',array($this,'add_template'));
        add_action('wp_ajax_wpf_edit',array($this,'add_template'));
        add_action('wp_ajax_wpf_get_tax',array($this,'get_tax_template'));
        add_action('wp_ajax_wpf_delete',array($this,'delete_template'));
        add_action('wp_ajax_wpf_ajax_themes_save',array($this,'save_themplate'));
        add_action('wp_ajax_wpf_import',array($this,'import_form'));
        add_action('wp_ajax_wpf_import_file',array($this,'import_template'));
        add_action('themify_after_demo_import',array($this,'demo_import'));
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     * This function called from WPF main class and registered with 'admin_menu' hook.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_menu_page(
                __('Product Filters', 'wpf'), __('Product Filters', 'wpf'), 'manage_options','wpf_search', array($this, 'display_search_forms'), 'dashicons-welcome-write-blog', '58.896427'
        );
        $this->plugin_about_page();
    }
    /**
     * @since 1.0.3
     *
     * @param None
     *
     * @return Nothing
     */
	public function plugin_about_page() {
		add_submenu_page(
			'wpf_search',
			__( 'About', 'wpf' ),
			__( 'About', 'wpf' ),
			'manage_options',
			'wpf_about',
			array( $this, 'create_about_page' )
		);
	}
	
	/**
     * @since 1.0.3
     *
     * @param None
     *
     * @return Nothing
     */
	public function create_about_page() {
		include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'doc/about.html' );
	}
	
	/**
     * @since 1.0.3
     *
     * @param None
     *
     * @return Nothing
     */
	public function activation_redirect() {
		if( get_option( 'themify_WPF_activation_redirect', false ) ) {
			delete_option( 'themify_WPF_activation_redirect' );
			wp_redirect( admin_url( 'admin.php?page=wpf_about' ) );
		}
	}
	
     /**
     * Export template.
     *
     * @since    1.0.0
     */
    public function init() {
        if (!empty($_GET['slug']) && isset($_GET['action']) && $_GET['action'] === 'wpf_export' && current_user_can('manage_options')) {
            $tid = utf8_uri_encode($_GET['slug']);
            $option = WPF_Options::get_option($this->plugin_name, $this->version);
            $forms = $option->get();
            if (!empty($forms[$tid])) {
                $data = array();
                $data[$tid] = $forms[$tid];
                ignore_user_abort(true);
                nocache_headers();
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Disposition: attachment; filename=wpf-filter-' .$tid. '-export-' . date('m-d-Y') . '.json');
                header("Expires: 0");
                header("Pragma: no-cache");
                echo wp_json_encode($data);
                exit;
            }
        }
    }
    
    /**
     * Import form.
     *
     * @since    1.0.0
     */
    public function import_form() {
        check_ajax_referer($this->plugin_name .'_import', 'nonce', true);
        if (current_user_can('manage_options')) {
           include_once( 'partials/import.php' );
        }
        wp_die();
    }
    
    /**
     * Import ajax File.
     *
     * @since    1.0.0
     */
    public function import_template(){
         check_ajax_referer($this->plugin_name .'_import_file', 'nonce', true);
         
         if(isset($_FILES['import']) && current_user_can('manage_options')){
             echo wp_json_encode($this->import($_FILES['import']));
         }
         
        wp_die();
    }
    
    /**
     * Hook Demo Import
     *
     * @since    1.0.0
     */
    public function demo_import(){
      
        if(!empty($_POST['skin']) && $_POST['skin']==='ecommerce'){
            $fname = 'product-filter.zip';
            $path = get_template_directory().'/skins/' . $_POST['skin'].'/'.$fname;
            if (is_file($path)) {
                $file_info = array('tmp_name'=>$path,'name'=>$fname);
                $this->import($file_info,false);
            } 
        }
    }

    /**
     * Import File.
     *
     * @since    1.0.0
     */
    public function import($file,$remove=true) {

        $allow_extensions = array('json', 'zip');
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (in_array($ext, $allow_extensions)) {
            $result = array();
            WP_Filesystem();
            global $wp_filesystem;
            // Retrieve the settings from the file and convert the json object to an array.
            if ($ext == 'json') {
                $form = json_decode($wp_filesystem->get_contents($file['tmp_name']), true);
                $k = key($form);
                if (!empty($form[$k]['layout']) && !empty($form[$k]['data'])) {
                    $result[$k] = $form[$k];
                }
                if($remove){
                    $wp_filesystem->delete($file['tmp_name'], true);
                }
            } else {
                $path = sys_get_temp_dir() . '/wpf/';
                if (!$wp_filesystem->is_dir($path)) {
                    $wp_filesystem->mkdir($path, 0755);
                } 
                chmod ($path, 0755);
                if (!unzip_file($file['tmp_name'], $path)) {
                    return array('error' => sprintf(__("Couldn't unzip %s", 'wpf'), $file['name']));
                } elseif ($dh = opendir($path)) {
                    while (($f = readdir($dh)) !== false) {
                        if($f && $f!='.' && $f!='..'){
                            $ext = pathinfo($f, PATHINFO_EXTENSION);
                            if ($ext == 'json') {
                                $form = json_decode($wp_filesystem->get_contents($path . $f), true);
                                $k = key($form);
                                if (!empty($form[$k]['layout']) && !empty($form[$k]['data'])) {
                                    $result[$k] = $form[$k];
                                }
                            }
                            $wp_filesystem->delete($path . $f, true);
                        }
                    }
                    closedir($dh);
                    if($remove){
                        $wp_filesystem->delete($file['tmp_name'], true);
                    }
                }
            }
            if (empty($result)) {
               return array('error' => __("Data could not be loaded", 'wpf'));
            } else {
                $option = WPF_Options::get_option($this->plugin_name, $this->version);
                $forms = $option->get();
                foreach ($result as $slug=>$v) {
                    if (!empty($v['layout']) && !empty($v['data'])) {
                        $forms[$slug] = $v;
                    }
                }
                $option->set($forms);
                return array('success' => 1);
            }
        } else {
            return array('error' => sprintf(__('You can import files only with extensions %s', 'wpf'), implode(',', $allow_extensions)));
        }
        
    }

    /**
     * Render the custom post types page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_search_forms() {

       include_once( 'partials/list.php' );
    }

    /**
     * Register the JavaScript/Stylesheets for the dashboard.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook ) {
		if( ! in_array( $hook, array( 'toplevel_page_wpf_search', 'product-filters_page_wpf_about' ) ) ) return;

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * in that particular class.
         *
         * between the defined hooks and the functions defined in this
         * class.
         */
        $screen = get_current_screen();
        if ($screen->id != 'customize') {
            $plugin_dir = plugin_dir_url(__FILE__);
            wp_register_script($this->plugin_name, $plugin_dir . 'js/wpf-admin.js', array('jquery'), $this->version, false);
          
            
            if (!wp_style_is('themify-colorpicker')) {
                wp_enqueue_style('themify-colorpicker', $plugin_dir . 'css/jquery/jquery.minicolors.css', array(), $this->version, 'all');
            }

            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('plupload-all');
            if( ! wp_script_is( 'themify-colorpicker' ) ) {
                wp_enqueue_script('themify-colorpicker-js', $plugin_dir . 'js/jquery/jquery.minicolors.min.js', array('jquery'), $this->version, false);
            }
            $translation_array = array(
                'template_delete' => __('Do you want to delete this?', 'wpf'),
                'module_delete' => __('Do you want to delete this module?', 'wpf')
            );
            wp_localize_script($this->plugin_name, 'wpf_js', $translation_array);
            wp_enqueue_script($this->plugin_name);
           
            wp_enqueue_script($this->plugin_name.'-template', $plugin_dir . 'js/wpf-themplate.js', array('jquery'), $this->version, false);
            wp_enqueue_style($this->plugin_name, $plugin_dir . 'css/wpf-themplate.css', array(), $this->version, 'all');
            global $sitepress;
            if(isset($sitepress)){
                wp_enqueue_style($this->plugin_name, $plugin_dir . 'css/wpf-language.css', array(), $this->version, 'all');
            }
        }
    }
    
    
    public function save_themplate(){
        check_ajax_referer($this->plugin_name .'_them_ajax', $this->plugin_name .'_nonce', true);
        $form = new WPF_Form($this->plugin_name,$this->version);
        $result = $form->save_themplate($_POST);
        if($result){
           echo  wp_json_encode($result);
        }
        wp_die();
    }

    public function add_template(){
        check_ajax_referer($this->plugin_name . '_edit', 'nonce', true);
        if (current_user_can('manage_options')) {
            if($_REQUEST['action']==='wpf_edit' && !empty($_REQUEST['slug'])){
                global $cpt_id;
                $cpt_id = sanitize_key($_REQUEST['slug']);
                
            }
            include_once 'partials/form.php';
        }
        wp_die();
    }
    
    public function delete_template(){
        if(!empty($_REQUEST['slug'])){
            check_ajax_referer($this->plugin_name . '_delete', 'nonce', true);
            if (current_user_can('manage_options')) {
                $slug = sanitize_text_field($_REQUEST['slug']);
                $option = WPF_Options::get_option($this->plugin_name, $this->version);
                $forms = $option->get();
                if(isset($forms[$slug])){
                    unset($forms[$slug]);
                    $option->set($forms);
                    die(wp_json_encode(array('status'=>'1')));
                }
            }
        }
        wp_die();
    }
    
    public function get_templates(){
        if (current_user_can('manage_options')) {
            $cptListTable = new WPF_List_Table($this->plugin_name, $this->version);
            $cptListTable->prepare_items();
            $cptListTable->display();
        }
        wp_die();
    }

    

    public function add_sort($columns) {
        foreach ($this->columns as $col => $name) {
            $columns[$col] = $col;
        }
        return array_merge($this->columns, $columns);
    }

    public function get_tax_template(){
        check_ajax_referer($this->plugin_name . '_get_tax', 'nonce', true);
        if($_REQUEST['action']==='wpf_get_tax' && !empty($_REQUEST['tax']) && !empty($_REQUEST['slug'])){
            include_once 'partials/tax.php';
        }
        wp_die();
    }


}
