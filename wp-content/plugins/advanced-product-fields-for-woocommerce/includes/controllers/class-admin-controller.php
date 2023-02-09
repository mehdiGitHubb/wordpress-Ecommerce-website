<?php

namespace SW_WAPF\Includes\Controllers {

    use SW_WAPF\Includes\Classes\Cache;
    use SW_WAPF\Includes\Classes\Conditions;
    use SW_WAPF\Includes\Classes\Field_Groups;
	use SW_WAPF\Includes\Classes\Helper;
	use SW_WAPF\Includes\Classes\Html;
    use SW_WAPF\Includes\Classes\wapf_List_Table;
    use SW_WAPF\Includes\Classes\Woocommerce_Service;
    use SW_WAPF\Includes\Models\ConditionRule;
    use SW_WAPF\Includes\Models\ConditionRuleGroup;
    use SW_WAPF\Includes\Models\FieldGroup;

    if (!defined('ABSPATH')) {
        die;
    }

    class Admin_Controller{

        public function __construct()
        {
            add_action( 'admin_enqueue_scripts',                                [$this, 'register_assets']);
            add_action('admin_menu',                                            [$this, 'admin_menus']);
            add_filter('plugin_action_links_' . wapf_get_setting('basename'),   [$this, 'add_plugin_action_links']);

            add_action('current_screen',                                        [$this, 'setup_screen']);
            add_action('admin_notices',                                         [$this, 'display_preloader']);
            foreach(wapf_get_setting('cpts') as $cpt) {
                add_action('save_post_' . $cpt,                                 [$this, 'save_post'], 10, 3);
            }

            add_filter('woocommerce_settings_tabs_array',                       [$this,'woocommerce_settings_tab'], 100);
            add_action('woocommerce_settings_tabs_wapf_settings',               [$this,'woocommerce_settings_screen']);
            add_action( 'woocommerce_update_options_wapf_settings',             [$this, 'update_woo_settings']);

            add_filter( 'woocommerce_product_data_tabs',                        [$this, 'add_product_tab']);
            add_action( 'woocommerce_product_data_panels',                      [$this, 'customfields_options_product_tab_content']);
            add_action( 'woocommerce_process_product_meta_simple',              [$this, 'save_fieldgroup_on_product']);
            add_action( 'woocommerce_process_product_meta_variable',            [$this, 'save_fieldgroup_on_product']);

            add_action('wp_ajax_wapf_search_products',                          [$this, 'search_woo_products']);
            add_action('wp_ajax_wapf_search_tags',                              [$this, 'search_woo_tags']);
            add_action('wp_ajax_wapf_search_cat',                               [$this, 'search_woo_categories']);
            add_action('wp_ajax_wapf_search_variations',                        [$this, 'search_woo_variations']);

        }

        #region Basics

        public function register_assets() {

            if(
                (isset($_GET['page']) && $_GET['page'] === 'wapf-field-groups') ||
                $this->is_screen(wapf_get_setting('cpts')) ||
                $this->is_screen('product')
            ) {

                $url =  trailingslashit(wapf_get_setting('url')) . 'assets/';
                $version = wapf_get_setting('version');

                wp_enqueue_style('wapf-admin-css', $url . 'css/admin.min.css', [], $version);
                wp_enqueue_script('wapf-admin-js', $url . 'js/admin.min.js', ['jquery','wp-color-picker'], $version, false); 
                wp_enqueue_media();
                wp_enqueue_style( 'wp-color-picker' );

                wp_localize_script( 'wapf-admin-js', 'wapf_language', [
                    'title_required'        => __("Please add a field group title first.", 'advanced-product-fields-for-woocommerce'),
                    'fields_required'       => __("Please add some fields first.", 'advanced-product-fields-for-woocommerce'),
                    'fieldgroup_limit'      => __("You've reached the amount of field groups you can create in the free version. Please consider upgrading to premium to add unlimited field groups. Thank you!",'advanced-product-fields-for-woocommerce')
                ]);

                $localize_array = [
	                'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
	                'isWooProductScreen'    => $this->is_screen('product')
                ];

                wp_localize_script('wapf-admin-js', 'wapf_config', $localize_array);

                wp_dequeue_script('autosave');
            }

        }

        public function admin_menus() {

            $cap = wapf_get_setting('capability');

            add_submenu_page(
                'woocommerce',
                __('Product Fields','advanced-product-fields-for-woocommerce'),
                __('Product Fields','advanced-product-fields-for-woocommerce'),
                $cap,
                'wapf-field-groups',
                [$this,'render_field_group_list']
            );
        }

        public function add_plugin_action_links($links) {
            $links = array_merge( [
                '<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=wapf_settings' ) ) . '">' . __( 'Settings', 'advanced-product-fields-for-woocommerce' ) . '</a>',
                '<a href="' . esc_url( admin_url( '/admin.php?page=wapf-field-groups' ) ) . '">' . __( 'Global fields', 'advanced-product-fields-for-woocommerce' ) . '</a>'
            ], $links );

            return $links;
        }

        public function maybe_duplicate() {

            if(empty($_GET['wapf_duplicate']))
                return false;

            $post_id = intval($_GET['wapf_duplicate']);
            if($post_id === 0)
                return false;

            $post = get_post($post_id);
            if(!$post)
                return false;

            $fg = Field_Groups::get_by_id($post_id);
	        if(empty($fg))
		        return false;

            $this->make_unique($fg);

            foreach(wapf_get_setting('cpts') as $cpt) {
                remove_action('save_post_' . $cpt, [$this, 'save_post'], 10);
            }

            Field_Groups::save($fg,$post->post_type,null,$post->post_title . ' - '. __('Copy','advanced-product-fields-for-woocommerce'), 'publish' );

            foreach(wapf_get_setting('cpts') as $cpt) {
                remove_action( 'save_post_' . $cpt, [$this, 'save_post'],10 );
            }

            return true;
        }

        #endregion

        #region WooCommerce product backend

        public function add_product_tab($tabs) {
            $tabs['customfields'] = [
                'label'		=> __( 'Custom fields', 'advanced-product-fields-for-woocommerce' ),
                'target'	=> 'customfields_options',
                'class'		=> ['show_if_simple', 'show_if_variable'],
            ];
            return $tabs;
        }

        public function customfields_options_product_tab_content() {

            echo '<div id="customfields_options" class="panel woocommerce_options_panel">';

            echo '<h4 class="wapf-product-admin-title">' .  __('Fields','advanced-product-fields-for-woocommerce') .' &mdash; <span style="opacity:.5;">'.__('Add some custom fields to this group.','advanced-product-fields-for-woocommerce').'</span>' . '</h4>';

            $this->display_field_group_fields(true);

            echo '<div style="display:none;">';
            $this->display_field_group_conditions(true);
            echo '</div>';

            echo '<h4 class="wapf-product-admin-title">' .  __('Layout','advanced-product-fields-for-woocommerce') .' &mdash; <span style="opacity:.5;">'.__('Field group layout settings','advanced-product-fields-for-woocommerce').'</span>' . '</h4>';
            $this->display_field_group_layout(true);

            echo '</div>';
        }

        public function save_fieldgroup_on_product($post_id) {
            if(empty($_POST['wapf-fields']) ||
                empty($_POST['wapf-conditions']) ||
                empty($_POST['wapf-layout'])) {
                delete_post_meta($post_id,'_wapf_fieldgroup');
                return;
            }

            $this->save($post_id, false);

        }

        #endregion

        #region WooCommerce setting page configuration
        public function update_woo_settings() {
            woocommerce_update_options( $this->get_settings() );
        }

        public function woocommerce_settings_screen() {
            woocommerce_admin_fields( $this->get_settings() );
        }

        public function get_settings() {
            $settings = [];

            $settings[] = [
                'name'      => __( 'Product field settings', 'advanced-product-fields-for-woocommerce' ),
                'type'      => 'title',
            ];

            $settings[] = [
                'name'      => __( 'Show in cart', 'advanced-product-fields-for-woocommerce' ),
                'id'        => 'wapf_settings_show_in_cart',
                'type'      => 'checkbox',
                'default'   => 'yes',
                'desc'      => __( "Show on customer's cart page.", 'advanced-product-fields-for-woocommerce' ),
                'desc_tip'  => __('When a user has filled out your fields, should they be summarized on their cart page after adding the product to their cart?', 'advanced-product-fields-for-woocommerce')
            ];

            $settings[] = [
                'name'      => __( 'Show on checkout', 'advanced-product-fields-for-woocommerce' ),
                'id'        => 'wapf_settings_show_in_checkout',
                'type'      => 'checkbox',
                'default'   => 'yes',
                'desc'      => __( "Show on the checkout page.", 'advanced-product-fields-for-woocommerce' ),
                'desc_tip'  => __('When a user has filled out your fields, should they be summarized on their checkout page?', 'advanced-product-fields-for-woocommerce')
            ];

            $settings[] = [
                'name'      => __( '"Add to cart" button text', 'advanced-product-fields-for-woocommerce' ),
                'type'      => 'text',
                'id'        => 'wapf_add_to_cart_text',
                'desc_tip'  => __( 'When a product has custom fields, what should the "add to cart" button say?.', 'advanced-product-fields-for-woocommerce' ),
                'default'   => __('Select options','advanced-product-fields-for-woocommerce')
            ];

            $settings[] = [
                'type'      => 'sectionend',
            ];

            return $settings;
        }

        public function woocommerce_settings_tab($tabs) {
            $tabs['wapf_settings'] = __( 'Product fields', 'advanced-product-fields-for-woocommerce' );
            return $tabs;
        }
        #endregion

        #region Ajax Functions

        public function search_woo_categories() {

            if( !current_user_can(wapf_get_setting('capability')) ) {
                echo json_encode([]);
                wp_die();
            }

            echo json_encode(Woocommerce_Service::find_category_by_name($_POST['q']));
            wp_die();
        }

        public function search_woo_tags() {

            if( !current_user_can(wapf_get_setting('capability')) ) {
                echo json_encode([]);
                wp_die();
            }

            echo json_encode(Woocommerce_Service::find_tags_by_name($_POST['q']));
            wp_die();
        }

        public function search_woo_variations() {

            if( !current_user_can(wapf_get_setting('capability')) ) {
                echo json_encode([]);
                wp_die();
            }

            echo json_encode(Woocommerce_Service::find_variations_by_name($_POST['q']));
            wp_die();
        }

        public function search_woo_products() {

            if( !current_user_can(wapf_get_setting('capability')) ) {
                echo json_encode([]);
                wp_die();
            }

            echo json_encode(Woocommerce_Service::find_products_by_name($_POST['q']));
            wp_die();
        }

        #endregion

        #region Save to Backend

        public function save_post($post_id, $post, $update) {

            if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_autosave($post)) || is_int(wp_is_post_revision($post))) {
                return;
            }

            if (defined('DOING_AJAX') && DOING_AJAX) {
                return;
            }

            if (isset($post->post_status) && $post->post_status === 'auto-draft')
                return;

            if( !current_user_can(wapf_get_setting('capability')) ) {
                return;
            }

            if(wp_verify_nonce($_POST['_wpnonce'],'update-post_' . $post_id) === false)
                return;

            $this->save($post_id, true);

        }

        private function save($post_id, $saving_cpt = true) {
            Cache::clear();

            $raw = [
                'id'            => $post_id,
                'fields'        => [],
                'conditions'    => [],
                'type'          => $_REQUEST['wapf-fieldgroup-type']
            ];

            if(isset($_POST['wapf-fields']))
                $raw['fields'] = json_decode(wp_unslash($_POST['wapf-fields']), true);

            if(isset($_POST['wapf-conditions']))
                $raw['conditions'] = json_decode(wp_unslash($_POST['wapf-conditions']), true);

            if(isset($_POST['wapf-layout']))
                $raw['layout'] = json_decode(wp_unslash($_POST['wapf-layout']), true);

            $fg = Field_Groups::raw_json_to_field_group($raw);

            if($saving_cpt) {
                foreach(wapf_get_setting('cpts') as $cpt) {
                    remove_action('save_post_' . $cpt, [$this, 'save_post'], 10);
                }

                Field_Groups::save($fg,$_REQUEST['wapf-fieldgroup-type'], $post_id);

                foreach(wapf_get_setting('cpts') as $cpt) {
                    remove_action( 'save_post_' . $cpt, [$this, 'save_post'],10 );
                }
            } else {
                $fg->id = 'p_' . $fg->id; 
                update_post_meta( $post_id, '_wapf_fieldgroup', Helper::wp_slash($fg->to_array()));
            }


        }

        #endregion

        #region Display functions

        public function display_preloader() {

            $cpts = wapf_get_setting('cpts');
            if(!$this->is_screen($cpts))
                return;

            echo '<div class="wapf-preloader" style="position: absolute;z-index: 2000;top:0;left:-20px;right: 0;height: 100%;background-color: rgba(0,0,0,.65);">';
            echo '<svg style="position: fixed;z-index:3000;top:30%;left:50%;margin-left:-23px;" width="45" height="45" viewBox="0 0 45 45" xmlns="http://www.w3.org/2000/svg" stroke="#fff"><g fill="none" fill-rule="evenodd" transform="translate(1 1)" stroke-width="2"><circle cx="22" cy="22" r="6" stroke-opacity="0"><animate attributeName="r" begin="1.5s" dur="3s" values="6;22" calcMode="linear" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="1.5s" dur="3s" values="1;0" calcMode="linear" repeatCount="indefinite" /><animate attributeName="stroke-width" begin="1.5s" dur="3s" values="2;0" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="22" cy="22" r="6" stroke-opacity="0"> <animate attributeName="r" begin="3s" dur="3s" values="6;22" calcMode="linear" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="3s" dur="3s" values="1;0" calcMode="linear" repeatCount="indefinite" /><animate attributeName="stroke-width" begin="3s" dur="3s" values="2;0" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="22" cy="22" r="8"><animate attributeName="r" begin="0s" dur="1.5s" values="6;1;2;3;4;5;6" calcMode="linear" repeatCount="indefinite" /></circle></g></svg>';
            echo '</div>';
        }

        public function setup_screen() {

            if($this->is_screen('woocommerce_page_wapf-field-groups')) {
               $this->maybe_duplicate();
            }

            $cpts = wapf_get_setting('cpts');
            if($this->is_screen($cpts)) {

                add_meta_box(
                    'wapf-field-list',
                    __('Fields','advanced-product-fields-for-woocommerce') .' &mdash; <span style="opacity:.5;">'.__('Add some custom fields to this group.','advanced-product-fields-for-woocommerce').'</span>',
                    [$this, 'display_field_group_fields'],
                    $cpts,
                    'normal',
                    'high'
                );

                add_meta_box(
                    'wapf-field-group-conditions',
                    __('Conditions','advanced-product-fields-for-woocommerce') .' &mdash; <span style="opacity:.5;">'.__('When should this field group be displayed?','advanced-product-fields-for-woocommerce').'</span>',
                    [$this, 'display_field_group_conditions'],
                    $cpts,
                    'normal',
                    'high'
                );

                add_meta_box(
                    'wapf-field-group-layout',
                    __('Layout','advanced-product-fields-for-woocommerce') .' &mdash; <span style="opacity:.5;">'.__('Field group layout settings','advanced-product-fields-for-woocommerce').'</span>',
                    [$this, 'display_field_group_layout'],
                    $cpts,
                    'normal',
                    'high'
                );

            }

        }

        public function display_field_group_layout($for_product_admin = false) {

            $model = $this->create_layout_model($for_product_admin);
            echo Html::view("admin/layout", $model);

        }

        public function display_field_group_conditions($for_product_admin = false) {

            $model = $this->create_conditions_model($for_product_admin);
            echo Html::view("admin/conditions", $model);
        }

        public function display_field_group_fields($for_product_admin = false) {

            $model = $this->create_field_group_model($for_product_admin);
            echo Html::view("admin/field-list", $model);

        }

        private function create_layout_model($for_product_admin = false) {

           $fg = new FieldGroup();
           $model = [
               'layout' => $fg->layout,
               'type'   => $fg->type
           ];

            global $post;
            if(is_bool($for_product_admin) && $for_product_admin)
                $field_group = Field_Groups::process_data(get_post_meta($post->ID, '_wapf_fieldgroup', true));
            else $field_group = Field_Groups::get_by_id($post->ID);

            if(isset($field_group->layout)) {
                $model['layout'] = $field_group->layout;
                $model['type'] = $field_group->type;
            }

            return $model;
        }

        private function create_conditions_model($for_product_admin = false) {

            $model = [
                'condition_options' => Conditions::get_fieldgroup_visibility_conditions(),
                'conditions'        => [],
                'post_type'         => isset($_GET['post_type']) ? $_GET['post_type'] : 'wapf_product'
            ];

            global $post;

            if(is_bool($for_product_admin) && $for_product_admin) {

                $field_group_raw = get_post_meta($post->ID, '_wapf_fieldgroup', true);

                if(empty($field_group_raw)) {
                    $model['post_type'] = 'wapf_product';
                    $field_group = $this->prepare_fieldgroup_for_product($post->ID);
                } else {
                    $field_group = Field_Groups::process_data($field_group_raw);
                }
            } else
                $field_group = Field_Groups::get_by_id($post->ID);

            if(!empty($field_group)) {
                $model['type']          = $field_group->type;
                $model['conditions']    = $field_group->rules_groups;
                $model['post_type']     = $field_group->type;
            }

            return $model;

        }

        private function create_field_group_model($for_product_admin = false) {

            $model = [
                'fields'            => [],
                'condition_options' => Conditions::get_field_visibility_conditions(),
                'type'              => 'wapf_product'
            ];

            global $post;

            if(is_bool($for_product_admin) && $for_product_admin)
                $field_group = Field_Groups::process_data(get_post_meta($post->ID, '_wapf_fieldgroup', true));
            else $field_group = Field_Groups::get_by_id($post->ID);

	        if(!empty($field_group)) {
		        $model['fields']    = Field_Groups::field_group_to_raw_fields_json($field_group);
		        $model['type']      = $field_group->type;
            }

            return $model;

        }

        public function render_field_group_list() {

            $cap = wapf_get_setting('capability');

            $list = new Wapf_List_Table();
            $list->prepare_items();

            $model = [
                'title'         => __('Product Field Groups', 'advanced-product-fields-for-woocommerce'),
                'can_create'    => current_user_can($cap),
                'count'         =>  Helper::get_fieldgroup_counts()['publish']
            ];

            Html::wp_list_table('cpt-list-table',$model,$list);

        }
        #endregion

        #region Private Helpers

        private function is_screen( $id = '', $action = '' ) {

            if( !function_exists('get_current_screen') ) {
                return false;
            }

            $current_screen = get_current_screen();

            if( !$current_screen )
                return false;

            if( !empty($action) ) {

                if(!isset($current_screen->action))
                    return false;

                if(is_array($action) && !in_array($current_screen->action, $action))
                    return false;

                if(!is_array($action) && $action !== $current_screen->action)
                    return false;
            }

            if(!empty($id)) {

                if(is_array($id) && !in_array($current_screen->id,$id))
                    return false;

                if(!is_array($id) && $id !== $current_screen->id)
                    return false;
            }

           return true;
        }

        private function prepare_fieldgroup_for_product($post_id) {

            $rule_group = new ConditionRuleGroup();
            $rule = new ConditionRule();
            $rule->subject = 'product';
            $rule->condition = 'product';
            $rule->value = [ ['id' => $post_id, 'text' => ''] ];
            $rule_group->rules[] = $rule;

            $field_group = new FieldGroup();
            $field_group->type = 'wapf_product';
            $field_group->rules_groups[] = $rule_group;

            return $field_group;
        }

        private function make_unique(&$fg) {

            $fg->id = null;


            foreach($fg->fields as $f) {

                $old_id = $f->id;
                $f->id = uniqid();

                foreach ($fg->fields as $f2){
                    if($f2->has_conditionals()){
                        foreach($f2->conditionals as $c) {
                            foreach ($c->rules as $r) {
                                if($r->field === $old_id)
                                    $r->field = $f->id;
                            }
                        }
                    }
                }

            }

        }

        #endregion

    }

}