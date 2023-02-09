<?php

defined( 'ABSPATH' ) || exit;

if (!class_exists('Themify_Builder')) :

    /**
     * Main Themify Builder class
     *
     * @package default
     */
    class Themify_Builder {


	/**
	 * @var array
	 */
	public $registered_post_types = array('post', 'page');

	/**
	 * Define builder grid active or not
	 * @var bool
	 */
	public static $frontedit_active = false;

	/**
	 * Define builder grid active id
	 * @var int
	 */
	public static $builder_active_id = null;


	/**
	 * Get status of builder content whether inside builder content or not
	 */
	public $in_the_loop = false;

	/**
	 * A list of posts which have been rendered by Builder
	 */
	private static $post_ids = array();


	public static $builder_is_saving=false;

	private static $tooltips = [];

	/**
	 * Themify Builder Constructor
	 */
	public function __construct() {

	}

	/**
	 * Class Init
	 */
	public function init() {
            add_action( 'init', array( $this, 'register_deprecated_cpt' ) );
	    // Include required files
	    self::includes_always();
	    new Themify_Builder_Layouts();
	    Themify_Global_Styles::init();

	    // Plugin compatibility
	    self::plugins_compatibility();


	    if(is_admin() || themify_is_rest()){
			add_action('wp_loaded',array($this,'wp_loaded'));
	    }
	    else{
			add_action('wp',array($this,'wp_loaded'),100);
	    }

	    if(function_exists('tbp_run') && !method_exists('Tbp','run')){//just need for backward compatibility with old version of PRO 26.08.20,can be removed after 1-2 year
			tbp_run();
	    }
	    // Login module action for failed login
	    add_action( 'wp_login_failed', array( $this, 'wp_login_failed' ) );
	}

	function register_deprecated_cpt() {
		Themify_Builder_Model::builder_cpt_check();
		$post_types = array(
			'portfolio' => array(
				'plural' => __('Portfolios', 'themify'),
				'singular' => __('Portfolio', 'themify'),
				'rewrite' => apply_filters('themify_portfolio_rewrite', 'project'),
				'menu_icon' => 'dashicons-portfolio'
			),
			'highlight' => array(
				'plural' => __('Highlights', 'themify'),
				'singular' => __('Highlight', 'themify'),
				'menu_icon' => 'dashicons-welcome-write-blog'
			),
			'slider' => array(
				'plural' => __('Slides', 'themify'),
				'singular' => __('Slide', 'themify'),
				'supports' => array('title', 'editor', 'author', 'thumbnail', 'custom-fields'),
				'menu_icon' => 'dashicons-slides'
			),
			'testimonial' => array(
				'plural' => __('Testimonials', 'themify'),
				'singular' => __('Testimonial', 'themify'),
				'menu_icon' => 'dashicons-testimonial'
			),
		);

		foreach ( $post_types as $key => $args ) {
			if ( Themify_Builder_Model::is_cpt_active( $key ) && Themify_Builder_Model::is_module_active( $key ) ) {

				if ( ! post_type_exists( $key ) ) {
					$options = array(
						'labels' => array(
							'name' => $args['plural'],
							'singular_name' => $args['singular'],
							'add_new' => __('Add New', 'themify'),
							'add_new_item' => sprintf(__('Add New %s', 'themify'), $args['singular']),
							'edit_item' => sprintf(__('Edit %s', 'themify'), $args['singular']),
							'new_item' => sprintf(__('New %s', 'themify'), $args['singular']),
							'view_item' => sprintf(__('View %s', 'themify'), $args['singular']),
							'search_items' => sprintf(__('Search %s', 'themify'), $args['plural']),
							'not_found' => sprintf(__('No %s found', 'themify'), $args['plural']),
							'not_found_in_trash' => sprintf(__('No %s found in Trash', 'themify'), $args['plural']),
							'menu_name' => $args['plural']
						),
						'supports' => isset($args['supports']) ? $args['supports'] : array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
						'hierarchical' => false,
						'public' => true,
						'show_ui' => true,
						'show_in_menu' => true,
						'show_in_nav_menus' => false,
						'publicly_queryable' => true,
						'rewrite' => array('slug' => isset($args['rewrite']) ? $args['rewrite'] : strtolower($args['singular'])),
						'query_var' => true,
						'can_export' => true,
						'capability_type' => 'post',
						'menu_icon' => isset($args['menu_icon']) ? $args['menu_icon'] : ''
					);

					register_post_type( $key, $options );
					$this->push_post_types( $key );
				}
				if ( ! taxonomy_exists( $key . '-category' ) ) {
					$options = array(
						'labels' => array(
							'name' => sprintf(__('%s Categories', 'themify'), $args['singular']),
							'singular_name' => sprintf(__('%s Category', 'themify'), $args['singular']),
							'search_items' => sprintf(__('Search %s Categories', 'themify'), $args['singular']),
							'popular_items' => sprintf(__('Popular %s Categories', 'themify'), $args['singular']),
							'all_items' => sprintf(__('All Categories', 'themify'), $args['singular']),
							'parent_item' => sprintf(__('Parent %s Category', 'themify'), $args['singular']),
							'parent_item_colon' => sprintf(__('Parent %s Category:', 'themify'), $args['singular']),
							'edit_item' => sprintf(__('Edit %s Category', 'themify'), $args['singular']),
							'update_item' => sprintf(__('Update %s Category', 'themify'), $args['singular']),
							'add_new_item' => sprintf(__('Add New %s Category', 'themify'), $args['singular']),
							'new_item_name' => sprintf(__('New %s Category', 'themify'), $args['singular']),
							'separate_items_with_commas' => sprintf(__('Separate %s Category with commas', 'themify'), $args['singular']),
							'add_or_remove_items' => sprintf(__('Add or remove %s Category', 'themify'), $args['singular']),
							'choose_from_most_used' => sprintf(__('Choose from the most used %s Category', 'themify'), $args['singular']),
							'menu_name' => sprintf(__('%s Category', 'themify'), $args['singular']),
						),
						'public' => true,
						'show_in_nav_menus' => false,
						'show_ui' => true,
						'show_admin_column' => true,
						'show_tagcloud' => true,
						'hierarchical' => true,
						'rewrite' => true,
						'query_var' => true
					);

					register_taxonomy( $key . '-category', $key, $options );
				}
			}
		}
	}

        public function wp_loaded() {
            $is_admin=is_admin();
            $is_active=Themify_Builder_Model::is_front_builder_activate();
            add_filter('themify_builder_module_content', array('Themify_Builder_Model', 'format_text'));
            if($is_active===false) {
                $func=function_exists('wp_filter_content_tags') ? 'wp_filter_content_tags' : 'wp_make_content_images_responsive';
                add_filter('themify_builder_module_content', $func);
                add_filter('themify_image_make_responsive_image', $func);
                add_action( 'themify_builder_background_styling', array(__CLASS__, 'display_tooltip'), 10, 3 );
            }
            elseif(isset($_GET['tb-id']) || isset($_COOKIE['tb_active'])) {
                self::$builder_active_id=isset($_GET['tb-id'])?(int)$_GET['tb-id']:(int)$_COOKIE['tb_active'];
                themify_disable_other_lazy();
            }
            // Actions
            $this->setup();
			if(did_action('wp')>0){
				$this->wp_hook();
			}
			else{
				add_action('wp',array($this,'wp_hook'));
			}
            if(($is_admin===true && Themify_Access_Role::check_access_backend()) || Themify_Builder_Model::is_frontend_editor_page()) {

                self::includes_editable();
                if($is_active===true) {
                    // load module panel frontend
                    add_action('wp_footer', array($this, 'load_javascript_template_front'), 1);
                    add_filter('show_admin_bar', '__return_false');// Custom CSS
                    add_filter('themify_dev_mode','__return_false');
		    global $wp_actions;
		    $wp_actions['wp_enqueue_media']=true;
                } else {
                    if($is_admin===true){
                        global $pagenow;
                        if($pagenow === 'edit.php' || $pagenow === 'post-new.php'){
                                $post_type=isset($_GET['post_type'])?$_GET['post_type']:'post';
                        }
                        elseif( 'post.php' === $pagenow && isset($_GET['post']) ){
                                $post_type=get_post_type( $_GET['post'] );
                        }
                    }
                    else{
                        $post_type=get_post_type();
                    }
                    // Ajax Actions
                    if(themify_is_ajax()) {
                        add_action('wp_ajax_tb_load_editor', array($this, 'load_editor'), 10);

                        add_action('wp_ajax_tb_load_module_partial', array($this, 'load_module_partial_ajaxify'), 10);
                        add_action('wp_ajax_tb_render_element', array($this, 'render_element_ajaxify'), 10);
                        add_action('wp_ajax_tb_get_post_types', array($this, 'get_ajax_post_types'), 10);
                        add_action('wp_ajax_tb_render_element_shortcode', array($this, 'render_element_shortcode_ajaxify'), 10);
                        // Builder Save Data
                        add_action('wp_ajax_tb_save_data', array($this, 'save_data_builder'), 10);
                        add_action('wp_ajax_tb_save_css', array($this, 'save_builder_css'), 10);
                        // AJAX Action Save Module Favorite Data
                        add_action('wp_ajax_tb_module_favorite', array($this, 'save_module_favorite_data'));
                        //AJAX Action Get Visual Templates
                        add_action('wp_ajax_tb_load_visual_templates', array($this, 'load_visual_templates'));
                        //AJAX Action Get Form Templates
                        add_action('wp_ajax_tb_load_form_templates', array($this, 'load_form_templates'));
                        //AJAX Action update ticks and TakeOver
                        add_action('wp_ajax_tb_update_tick', array(__CLASS__, 'update_tick'));
                        add_action('wp_ajax_tb_help', array($this, 'help'));
                        // Replace URL
                        add_action('wp_ajax_tb_get_ajax_builder_posts', array(__CLASS__, 'get_ajax_builder_posts'));
                        add_action('wp_ajax_tb_save_ajax_builder_mutiple_posts', array(__CLASS__, 'save_ajax_builder_mutiple_posts'));
                        add_action('wp_ajax_tb_get_ajax_data', array($this, 'get_ajax_data'));

                    } elseif ( empty( $post_type ) || ! Themify_Builder_Model::is_builder_disabled_for_post_type( $post_type )) {
                        // Builder write panel
                        if ( $is_admin === true ) {
                            // Filtered post types
                            add_filter('themify_post_types', array($this, 'extend_post_types'));
                            add_filter('themify_do_metaboxes', array($this, 'builder_write_panels'), 11);
                            add_action('themify_builder_metabox', array($this, 'add_builder_metabox'), 10);
                            add_action('admin_enqueue_scripts', array($this, 'check_admin_interface'), 10);
                            // Switch to frontend
                            add_action('save_post', array($this, 'switch_frontend'), 999, 1);
                            // Disable WP Editor
                            add_filter('edit_form_after_title', array($this, 'disable_wp_editor'), 99);
                            add_filter( 'is_protected_meta', array( $this, 'is_protected_meta' ), 10, 3 );
                        }
                    }
                    if ( $is_admin === false || themify_is_ajax() ) {
							add_action('admin_bar_menu', array($this, 'builder_admin_bar_menu'), 100);
						}
                    if($is_active===true || $is_admin!==true) {
                        add_action('wp_footer', array($this, 'async_footer'));
                    }
                    // Import Export
                    Themify_Builder_Import_Export::init($this);
                }

                // Library Module, Rows and Layout Parts
                Themify_Builder_Library_Items::init();

                // Themify Builder Revisions
                Themify_Builder_Revisions::init();

                // Fix security restrictions
                add_filter('user_can_richedit', '__return_true');
            }

            // Script Loader
            add_action('wp_enqueue_scripts', array($this, 'register_js_css'), 9);

            // Hook to frontend
            add_action('wp_head', array($this, 'inline_css'), -1001);
            add_filter( 'the_content', array( $this, 'clear_static_content' ), 1 );
            add_filter('the_content', array($this, 'builder_show_on_front'), 11);
            add_filter('body_class', array($this, 'body_class'), 10);
            // Add extra protocols like skype: to WordPress allowed protocols.
            if(!has_filter('kses_allowed_protocols', 'themify_allow_extra_protocols') && function_exists('themify_allow_extra_protocols')) {
                add_filter('kses_allowed_protocols', 'themify_allow_extra_protocols');
            }

            Themify_Builder_Stylesheet::init();
            // Visibility controls
            Themify_Builder_Visibility_Controls::init();

			//convert old data to new grid data,can be removed after updates

			add_action('wp_ajax_nopriv_tb_update_old_data', array($this, 'convert_data'), 10);
			add_action('wp_ajax_tb_update_old_data', array($this, 'convert_data'), 10);
        }
	/**
	 * Return Builder data for a post
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_builder_data($post_id) {//deprecated use ThemifyBuilder_Data_Manager
	    return  ThemifyBuilder_Data_Manager::get_data($post_id);
	}

	/**
	 * Return all modules for a post as a two-dimensional array
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_flat_modules_list($post_id = null, $builder_data = null, $only_check = false) {
	    if ($builder_data === null) {
		$builder_data = ThemifyBuilder_Data_Manager::get_data($post_id);
	    }
	    if ($only_check !== false) {
		return strpos(json_encode($builder_data), 'mod_settings') !== false;
	    }
	    $_modules = array();
	    // loop through modules in Builder
	    if (is_array($builder_data)) {
		foreach ($builder_data as $row) {
		    if (!empty($row['cols'])) {
			foreach ($row['cols'] as $col) {
			    if (!empty($col['modules'])) {
				foreach ($col['modules'] as $mod) {
				    if (isset($mod['mod_name'])) {
					$_modules[] = $mod;
				    }
				    // Check for Sub-rows
				    if (!empty($mod['cols'])) {
					foreach ($mod['cols'] as $sub_col) {
					    if (!empty($sub_col['modules'])) {
						foreach ($sub_col['modules'] as $sub_module) {
						    $_modules[] = $sub_module;
						}
					    }
					}
				    }
				}
			    }
			}
		    }
		}
	    }

	    return $_modules;
	}

	/**
	 * Return first not empty text module
	 *
	 * @since 1.4.2
	 * @return string
	 */
	public function get_first_text($post_id = null, $builder_data = null) {
	    if ($builder_data === null) {
		$builder_data = ThemifyBuilder_Data_Manager::get_data($post_id);
	    }
	    // loop through modules in Builder
	    if (is_array($builder_data)) {
		foreach ($builder_data as $row) {
		    if (!empty($row['cols'])) {
			foreach ($row['cols'] as $col) {
			    if (!empty($col['modules'])) {
				foreach ($col['modules'] as $mod) {
				    if (isset($mod['mod_name']) && $mod['mod_name'] === 'text' && !empty($mod['mod_settings']['content_text'])) {
					return $mod['mod_settings']['content_text'];
				    }
				    // Check for Sub-rows
				    if (!empty($mod['cols'])) {
					foreach ($mod['cols'] as $sub_col) {
					    if (!empty($sub_col['modules'])) {
						foreach ($sub_col['modules'] as $sub_module) {
						    if (isset($sub_module['mod_name']) && $sub_module['mod_name'] === 'text' && !empty($sub_module['mod_settings']['content_text'])) {
							return $sub_module['mod_settings']['content_text'];
						    }
						}
					    }
					}
				    }
				}
			    }
			}
		    }
		}
	    }

	    return '';
	}

	/**
	 * Load JS and CSs for async loader.
	 *
	 * @since 2.1.9
	 */
	public function async_footer() {
	    wp_deregister_script('wp-embed');
	    $editorUrl=THEMIFY_BUILDER_URI.'/css/editor/';
	    themify_enque_style('themify-builder-loader', $editorUrl . 'themify.builder.loader.css', null, THEMIFY_VERSION,'all',true);
	    themify_enque_script('themify-builder-loader', THEMIFY_BUILDER_URI . '/js/editor/frontend/themify.builder.loader.js', THEMIFY_VERSION, array('jquery'));

            $st=array(
                THEMIFY_URI . '/css/base.min.css',
                $editorUrl . 'workspace.css',
                $editorUrl . 'modules/lightbox.css'
            );
            $styles=array();
            foreach($st as $s){
                $styles[themify_enque($s)]=themify_get_asset_version($s,THEMIFY_VERSION);
            }
            $st=null;
            wp_localize_script('themify-builder-loader', 'tbLoaderVars', array(
                    'styles' => apply_filters('themify_styles_top_frame', array_reverse($styles)),
                    'turnOnBuilder' => __('Turn On Builder', 'themify'),
                    'turnOnLpBuilder' => __('Edit Layout Part', 'themify'),
                    'editTemplate'=>__('Edit Template','themify'),
                    'isGlobalStylePost' => Themify_Global_Styles::$isGlobalEditPage
            ));
            $styles=null;
	}

	/**
	 * Init function
	 */
	public function setup() {
	    do_action('themify_builder_setup_modules', $this);
	    if ((!empty($_REQUEST['action']) && !in_array($_REQUEST['action'], array('tb_update_tick', 'tb_load_module_partial', 'tb_render_element', 'tb_module_favorite', 'themify_regenerate_css_files_ajax',  'render_element_shortcode_ajaxify', 'get_ajax_post_types', 'tb_help'), true) && themify_is_ajax()) || Themify_Builder_Model::is_front_builder_activate()) {
		Themify_Builder_Component_Module::load_modules();
	    }
	}

	public function wp_hook(){
		do_action('themify_builder_run', $this);
	}

	private static function includes_always() {
	    if ( Themify_Builder_Model::is_gutenberg_active() ) {
		include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-gutenberg.php';
	    }
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-data-manager.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-stylesheet.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-widgets.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-visibility-controls.php';
            if (  current_user_can( 'publish_pages' ) ) {
		include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-page.php';
            }

		include THEMIFY_BUILDER_INCLUDES_DIR . '/components/base.php';
		include THEMIFY_BUILDER_INCLUDES_DIR . '/components/row.php';
		include THEMIFY_BUILDER_INCLUDES_DIR . '/components/subrow.php';
		include THEMIFY_BUILDER_INCLUDES_DIR . '/components/column.php';
		include THEMIFY_BUILDER_INCLUDES_DIR . '/components/module.php';
	}

	private static function includes_editable() {
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-revisions.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-library-item.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-duplicate-page.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-import-export.php';
	}

	/**
	 * List of post types that support the editor
	 *
	 * @since 2.4.8
	 */
	public function builder_post_types_support() {
	    $public_post_types = get_post_types(array(
		'public' => true,
		'_builtin' => false,
		'show_ui' => true,
	    ));
	    $post_types = array_merge($public_post_types, array('post', 'page'));
	    foreach ($post_types as $key => $type) {
		if (!post_type_supports($type, 'editor')) {
		    unset($post_types[$key]);
		}
	    }

	    return apply_filters('themify_builder_post_types_support', $post_types);
	}

	/**
	 * Builder write panels
	 *
	 * @param $meta_boxes
	 *
	 * @return array
	 */
	public function builder_write_panels($meta_boxes) {
	    if (Themify_Builder_Model::is_gutenberg_editor()){
			return $meta_boxes;
		}
	    // Page builder Options
	    $page_builder_options = apply_filters('themify_builder_write_panels_options', array(
		array(
		    'name' => 'page_builder',
		    'title' => __('Themify Builder', 'themify'),
		    'description' => '',
		    'type' => 'page_builder'
		)
	    ));

	    $types = $this->builder_post_types_support();
	    $all_meta_boxes = array();
	    foreach ($types as $type) {
		$all_meta_boxes[] = apply_filters('themify_builder_write_panels_meta_boxes', array(
		    'name' => __('Themify Builder', 'themify'),
		    'id' => 'page-builder',
		    'options' => $page_builder_options,
		    'pages' => $type
		));
	    }

	    return array_merge($meta_boxes, $all_meta_boxes);
	}

	/**
	 * Add builder metabox
	 */
	public function add_builder_metabox() {
	    include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-meta.php';
	}


	/**
	 * Load admin js and css
	 * @param $hook
	 */
	public function check_admin_interface($hook) {
	    if (in_array($hook, array('post-new.php', 'post.php'), true) && in_array(get_post_type(), themify_post_types(), true) && Themify_Access_Role::check_access_backend()) {
		add_action('admin_footer', array($this, 'load_javascript_template_admin'), 10);
		add_filter('admin_body_class', array($this, 'admin_body_class'), 10, 1);
		add_filter('mce_css', array($this, 'builder_static_badge_css'));
	    }
	}

	/**
	 * Load interface js and css
	 *
	 * @since 2.1.9
	 */
	private function load_frontend_interface() {

	    // load only when builder is turn on
	    $editorUrl=THEMIFY_BUILDER_URI.'/css/editor/';
	    themify_enque_style('themify-builder-admin-ui', $editorUrl . 'themify-builder-admin-ui.css', false, THEMIFY_VERSION,null,true);
	    $grids=array_diff(scandir(THEMIFY_DIR.'/css/grids/'), array('..', '.'));
	    foreach($grids as $g){
		Themify_Enqueue_Assets::loadGridCss(str_replace('.css','',$g),true);
	    }
	    unset($grids);

	    $editorUrl=THEMIFY_BUILDER_URI . '/js/editor/';
	    $frontendUrl=$editorUrl . 'frontend/';

            Themify_Icon_Font::enqueue();

	    themify_enque_script('themify-colorpicker', THEMIFY_METABOX_URI . 'js/themify.minicolors.js');
	    themify_enque_script('themify-combobox', $editorUrl . 'themify.combobox.min.js');

	    themify_enque_script('themify-builder-js', THEMIFY_BUILDER_URI . '/js/themify.builder.script.js');
	    themify_enque_script('tb_builder_js_style', THEMIFY_URI . '/js/generate-style.js');
	    themify_enque_script('themify-builder-app-js', $editorUrl . 'themify-builder-app.js', THEMIFY_VERSION, array('tb_builder_js_style'));

	    themify_enque_script('themify-builder-front-ui-js', $frontendUrl . 'themify-builder-visual.js', THEMIFY_VERSION, array('themify-builder-app-js'));
	    themify_enque_script('themify-builder-inline-editing', $frontendUrl . 'themify-builder-inline-editing.js', THEMIFY_VERSION, array('themify-builder-front-ui-js'));


	    do_action('themify_builder_active_enqueue','visual');
	    do_action('themify_builder_frontend_enqueue');//deprecated use themify_builder_frontend_enqueue


	    global $shortcode_tags;
	    $builderData=$this->get_active_builder_vars();
	    $builderData['upload_url']=themify_upload_dir('baseurl');
	    $builderData['available_shortcodes']=array_keys($shortcode_tags);

	    wp_localize_script( 'themify-colorpicker', 'themifyCM', Themify_Metabox::themify_localize_cm_data() );
	    wp_localize_script('themify-builder-app-js', 'themifyBuilder', $builderData);
	}

	private function load_admin_interface() {
	    $editorUrl=THEMIFY_BUILDER_URI . '/css/editor/';
	    themify_enque_style( 'tf_base', THEMIFY_URI . '/css/base.min.css', null, THEMIFY_VERSION,null,true);
            themify_enque_style('themify-builder-loader', $editorUrl . 'themify.builder.loader.css', null, THEMIFY_VERSION,null,true);
	    themify_enque_style('themify-builder-style', THEMIFY_BUILDER_URI . '/css/themify-builder-style.css', null, THEMIFY_VERSION,null,true);
	    themify_enque_style('themify-builder-lightbox', $editorUrl . 'modules/lightbox.css', null, THEMIFY_VERSION,null,true);
	    themify_enque_style('themify-builder-admin-ui', $editorUrl . 'themify-builder-admin-ui.css', null, THEMIFY_VERSION,null,true);
            themify_enque_style('themify-backend-ui', $editorUrl . 'backend/backend-ui.css', null, THEMIFY_VERSION,null,true);
	    $editorUrl=THEMIFY_BUILDER_URI . '/js/editor/';

	    Themify_Enqueue_Assets::loadMainScript();

	    themify_enque_script('themify-combobox', $editorUrl . 'themify.combobox.min.js');

	    themify_enque_script('tb_builder_js_style', THEMIFY_URI . '/js/generate-style.js');

	    themify_enque_script('themify-builder-app-js', $editorUrl . 'themify-builder-app.js');

	    themify_enque_script('themify-builder-backend-js', $editorUrl . 'backend/themify-builder-backend.js', THEMIFY_VERSION, array('themify-builder-app-js'));

            themify_enque_script('themify-static-badge', THEMIFY_BUILDER_URI . '/js/editor/backend/themify-builder-static-badge.js', THEMIFY_VERSION, array('mce-view','themify-builder-backend-js'));


	    do_action('themify_builder_active_enqueue','admin');
	    do_action('themify_builder_admin_enqueue');//deprecated use themify_builder_frontend_enqueue


	    $builderData=$this->get_active_builder_vars();
	    $builderData['post_ID']=get_the_ID();
	    $builderData['is_gutenberg_editor']=Themify_Builder_Model::is_gutenberg_editor();

	    wp_localize_script('themify-builder-app-js', 'themifyBuilder', $builderData);
	}

	/**
	 * Register styles and scripts necessary for Builder template output.
	 * These are enqueued when user initializes Builder or from a template output.
	 *
	 * Registered style handlers:
	 *
	 * Registered script handlers:
	 * themify-builder-module-plugins-js
	 * themify-builder-script-js
	 *
	 * @since 2.1.9
	 */
	public function register_js_css() {
	    add_action('wp_footer', array($this, 'footer_js'));
	}

	public function footer_js() {
	    $args = array(
		'breakpoints' => themify_get_breakpoints(),
		'fullwidth_support'=>Themify_Builder_Model::is_fullwidth_layout_supported(),
		'is_sticky' => Themify_Builder_Model::is_sticky_scroll_active(),
		'is_animation'=>Themify_Builder_Model::is_animation_active(),
		'is_parallax'=>Themify_Builder_Model::is_parallax_active(),
		'scrollHighlight'=>apply_filters('themify_builder_scroll_highlight_vars', array()) //Inject variable values in Scroll-Highlight script
	    );
	    if(Themify_Builder_Model::is_front_builder_activate()){
                foreach (Themify_Builder_Model::$modules as $m) {
                        $assets = $m->get_assets();
                        if (!empty($assets)) {
			    Themify_Builder_Component_Module::add_modules_assets($m->slug, $assets);
                        }
                }
	    }
            elseif( ! empty( self::$tooltips ) ) {
                Themify_Enqueue_Assets::addLocalization( 'builder_tooltips', self::$tooltips );
                self::$tooltips = null;
            }
	    $offset=themify_builder_get( 'setting-scrollto_offset', 'builder_scrollTo' );
	    if(!empty($offset) || $offset==='0'){
                $args['scrollHighlight']['offset']=(int)$offset;
	    }
	    $speed = themify_builder_get( 'scrollto_speed', 'builder_scrollTo_speed' );
		if ( $speed === null ) {
			$speed = .9;
		}
		$args['scrollHighlight']['speed'] = ( (float) $speed * 1000 ) + .01; /* .01 second is added in case user enters 0 to disable the animation */
	    $args['addons']=Themify_Builder_Component_Module::get_modules_assets();

	    $args=apply_filters('themify_builder_script_vars', $args);
            if(true===$args['is_animation']){
                unset($args['is_animation']);
	    }
	    if(true===$args['is_parallax']){
                unset($args['is_parallax']);
	    }
	    if(true===$args['is_sticky']){
                unset($args['is_sticky']);
	    }
            if($args['fullwidth_support']===true){
		unset($args['fullwidth_support']);
	    }
	    else{
		$args['fullwidth_support']=1;
	    }
	    Themify_Enqueue_Assets::localize_script('themify-main-script', 'tbLocalScript',$args);
            $args=null;
	}


	public static function defer_js($tag, $handle, $src) {
	    if (in_array($handle,  array('word-count','shortcode'), true)) {
			return str_replace(' src', ' defer="defer" src', $tag);
	    }
	    return $tag;
	}

	public function get_ajax_post_types() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    if (isset($_POST['type'])) {
			$result = array();
			$post_types = false;
			if ($_POST['type'] === 'post_types') {
				if(!empty($_POST['all']) && 'true' === $_POST['all']){
					$result['any'] = array('name' => __('All','themify'),'options'=>'');
				}
				$taxes = Themify_Builder_Model::get_public_taxonomies();
				$post_types = Themify_Builder_Model::get_public_post_types();
				foreach ($post_types as $k => $v) {
					$result[$k] = array('name' => $v);
					$post_type_tax = get_object_taxonomies($k);
					foreach ($post_type_tax as $t) {
						if (isset($taxes[$t])) {
							if (!isset($result[$k]['options'])) {
								$result[$k]['options'] = array();
							}
							$result[$k]['options'][$t] = array('name' => $taxes[$t]);
						}
					}
				}
				unset($taxes,$exclude);
			}
			elseif ($_POST['type'] === 'terms' && !empty($_POST['v'])) {
				$tax = get_taxonomy($_POST['v']);
				$args=array(
					'hide_empty' => true,
					'no_found_rows' => true,
					'orderby' => 'name',
					'order' => 'ASC',
					'taxonomy' => $tax->name
				);
				if(!empty($_POST['s'])){
					$args['name__like']=sanitize_text_field($_POST['s']);
				}else{
					$args['number']=50;
				}
				$terms_by_tax = get_terms($args);
				unset($args);
				$result['0'] = $tax->labels->all_items;
				foreach ($terms_by_tax as $v) {
					$result[$v->slug] = $v->name;
				}
				unset($tax);

			}
			wp_send_json(apply_filters('themify_builder_query_post', $result, $_POST['type'], $post_types));
	    }
	    wp_die();
	}




	/**
	 * Load module partial when update live content
	 */
	public function load_module_partial_ajaxify() {
	    check_ajax_referer('tf_nonce', 'nonce');
            themify_disable_other_lazy();
	    self::$frontedit_active = true;
            self::$builder_active_id= $_POST['bid'];
	    $new_modules = array(
		'mod_name' => $_POST['tb_module_slug'],
		'mod_settings' => json_decode(stripslashes($_POST['tb_module_data']), true),
		'element_id'=>$_POST['element_id']
	    );
		$new_modules=apply_filters('themify_builder_load_module_partial',$new_modules);

	    Themify_Builder_Component_Module::template($new_modules, self::$builder_active_id);
	    $css=Themify_Enqueue_Assets::get_css();
	    if(!empty($css)){
		echo '<script type="text/template" id="tb_module_styles">',json_encode($css),'</script>';
	    }
	    wp_die();
	}

	public function render_element_ajaxify() {
	    check_ajax_referer( 'tf_nonce', 'nonce' );
		themify_disable_other_lazy();
		$response = array();
		$batch = json_decode( stripslashes( $_POST['batch'] ), true );
		self::$frontedit_active = true;
		$batch=apply_filters('themify_builder_load_module_partial',$batch);
		self::$builder_active_id = $_POST['bid'];
		if ( !empty( $_POST['tmpGS'] ) ) {
		    Themify_Global_Styles::$used_styles[self::$builder_active_id]=Themify_Global_Styles::addGS(self::$builder_active_id ,json_decode(stripslashes($_POST['tmpGS']),true));
		}
		if ( !empty( $batch ) ) {
			$used_gs = array();
			foreach ( $batch as $b ) {
				$type = $b['elType'];
				$element_id=$b['element_id'];
				switch ( $type ) {
					case 'module':
						if(isset($_POST['element_id'])){
						    $element_id=$b['element_id'] = $_POST['element_id'];
						}
						$markup = Themify_Builder_Component_Module::template( $b, self::$builder_active_id,false);
						break;

					case 'subrow':
						unset( $b['cols'] );
						$markup = Themify_Builder_Component_SubRow::template(  $b, self::$builder_active_id ,false);
						break;

					case 'column':
						unset( $b['modules'] );
						$markup = Themify_Builder_Component_Column::template($b, self::$builder_active_id ,false);
						break;

					case 'row':
						unset( $b['cols'] );
						$markup = Themify_Builder_Component_Row::template( $b,self::$builder_active_id,false);
						break;
				}
				$response[ $element_id] = $markup;
				if(!empty($b['attached_gs'])){
				    $used_gs = array_merge($used_gs,$b['attached_gs']);
                }
			}
		}
		$batch=null;
		if ( !empty( $used_gs ) ) {
			$used_gs = array_unique($used_gs);
		    // Return used gs if used
			$args = array(
			    'exclude' => empty($_POST['loadedGS']) ? array() : $_POST['loadedGS'],
			    'include' => $used_gs,
			    'limit' => -1,
			    'data' => true
			);
			$used_gs = Themify_Global_Styles::get_global_styles($args);
			if(!empty($used_gs)){
			    $response['gs'] = $used_gs;
			}
		}
		$css=Themify_Enqueue_Assets::get_css();
		if(!empty($css)){
			$response['tb_module_styles']=$css;
		}
                //don't use wp_send_json it's very heavy,this array can be very big
                header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                die(json_encode($response));
	}

	public function render_element_shortcode_ajaxify() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    $shortcodes = $styles = array();
	    $shortcode_data = json_decode(stripslashes_deep($_POST['shortcode_data']), true);

	    if (is_array($shortcode_data)) {
		foreach ($shortcode_data as $shortcode) {
		    $shortcodes[] = array('key' => $shortcode, 'html' => Themify_Builder_Model::format_text($shortcode));
		}
	    }

	    global $wp_styles;
	    if (isset($wp_styles) && !empty($shortcodes)) {
		ob_start();
		$tmp = $wp_styles->do_items();
		ob_end_clean();
		foreach ($tmp as $handler) {
		    if (isset($wp_styles->registered[$handler])) {
			$src=$wp_styles->registered[$handler]->src;
			if (strpos($src, 'http') === false) {
			    $src = home_url($src);
			}
			$styles[] = array(
			    's' => $src,
			    'v' => $wp_styles->registered[$handler]->ver,
			    'm' => isset($wp_styles->registered[$handler]->args) ? $wp_styles->registered[$handler]->args : 'all'
			);
		    }
		}
		unset($tmp);
	    }

	    wp_send_json_success(array(
		'shortcodes' => $shortcodes,
		'styles' => $styles
	    ));
	}

	/**
	 * Save builder main data
	 */
	public function save_data_builder() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(!empty($_POST['bid'])){
		// Information about writing process.
		$results = array();
		if ( isset( $_POST['data'] ) ) {
		    $data = stripslashes_deep( $_POST['data'] );
		}
		elseif ( isset( $_FILES['data'] ) ) {
		    $data = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(isset($data)){//don't use empty, when builder is empty need to remove builder data
		    $post_id = (int) $_POST['bid'];
		    if(current_user_can('edit_post',$post_id)){
			if(!empty($_POST['images'])){
			    $images= json_decode(stripslashes_deep($_POST['images']),true);
			    if(!empty($images) && is_array($images)){
				foreach($images as $img){
				    themify_get_image_size($img);
				    themify_get_placeholder($img);
				    themify_createWebp($img);
				}
			    }
			    unset($images);
			}
			$data = !empty($data)?json_decode( $data, true ):array();
			if(empty($data) || !is_array($data)){
			    $data=array();
			}
			self::$builder_is_saving = true;
			$results = ThemifyBuilder_Data_Manager::save_data($data, $post_id, $_POST['sourceEditor']);
			if(!empty($results['mid'])){
			    if ( isset( $_POST['custom_css'] ) ) {
				if(!empty($_POST['custom_css'])){
					update_post_meta( $post_id, 'tbp_custom_css', $_POST['custom_css'] );
					$results['custom_css'] = $_POST['custom_css'];
				}
				else{
					delete_post_meta( $post_id, 'tbp_custom_css');
				}
				do_action( 'themify_builder_custom_css_updated', $post_id,$_POST['sourceEditor'] );
			    }
			    $data= $post_id=null;
			    $results['builder_data'] = json_decode($results['builder_data'], true);
			}
			else{
			    wp_send_json_error(__('Can`t Save Builder Data','themify'));
			}
			self::$builder_is_saving = null;
		    }
		    else{
			wp_send_json_error(__('You Don`t have permission to edit this post','themify'));
		    }
		}
		wp_send_json_success($results);
	    }
	}

	/**
	 * Only need to save converted old css col padding to new grid css padding
	 */
	public function convert_data(){
		check_ajax_referer('tf_nonce', 'nonce');
		if(!empty($_POST['data']) && !empty($_POST['bid'])){
			$id=(int) $_POST['bid'];
			$builder_data=ThemifyBuilder_Data_Manager::get_data($id);
			if(is_array($builder_data)){
				$convert=json_decode(stripslashes_deep($_POST['data']), true);
                                $update=false;
				$breakpints= array_reverse(array_keys(themify_get_breakpoints()));
				$breakpints[]='desktop';
				$bpLength=count($breakpints);
				$allowed=array('padding','padding_top','padding_bottom','padding_left','padding_right','padding_left_unit','padding_right_unit','padding_top_unit','padding_bottom_unit','margin-bottom','margin-top','margin-bottom_unit','margin-top_unit');
				foreach ($builder_data as &$row) {
					if (!empty($row['cols'])) {
						foreach ($row['cols'] as &$col) {
							if(isset($convert[$col['element_id']])){
								$hasChange=false;
								foreach($convert[$col['element_id']] as $bp=>$props){
								    if(in_array($bp,$breakpints,true)){
									foreach($props as $prop=>$v){
									    if(in_array($prop,$allowed,true)){
										if($bp==='desktop'){
                                                                                    if($v==='%'){//unit proop
                                                                                        $col['styling'][$prop]=$v;
                                                                                    }
										    else{
											if(!isset($col['styling'][$prop])){
											    $col['styling'][$prop]='';
											}
											if(strpos($col['styling'][$prop],',')===false && is_numeric($v)){
											    //the first value is old value of v5(if user will try to downgrade FW),the second after converting
											    $v = (int)$v;
											    $col['styling'][$prop].=','.$v;
											    $update=true;
											}
										    }
										}
										else{
										    if(!isset($col['styling']['breakpoint_'.$bp])){
											$col['styling']['breakpoint_'.$bp]=array();
										    }
                                                                                    if($v==='%'){//unit proop
                                                                                        $col['styling']['breakpoint_'.$bp][$prop]=$v;
                                                                                    }
										    else{
											if(!isset($col['styling']['breakpoint_'.$bp][$prop])){
											    $col['styling']['breakpoint_'.$bp][$prop]='';
											}
											if(strpos($col['styling']['breakpoint_'.$bp][$prop],',')===false && is_numeric($v)){
											    $v = (int)$v;
											    $col['styling']['breakpoint_'.$bp][$prop].=','.$v;
											    $update=$hasChange=true;
											}
										    }
										}
									    }
									}
								    }
								}
								if($hasChange===true){
								    for($i=0;$i<$bpLength-1;++$i){
									$bp=$breakpints[$i];
									if(!empty($col['styling']['breakpoint_'.$bp])){
									    $st=$col['styling']['breakpoint_'.$bp];
									    foreach($allowed as $p){
										if(isset($st[$p])){
										    $parentSt=null;
										    for($j=$i+1;$j<$bpLength;++$j){
											$parentBp=$breakpints[$j];
											$parentSt=$parentBp==='desktop'?$col['styling']:(isset($col['styling']['breakpoint_'.$parentBp])?$col['styling']['breakpoint_'.$parentBp]:null);
											if(isset($parentSt) && isset($parentSt[$p])){
											    break;
											}
										    }
										    if(isset($parentSt) && ($parentSt[$p]==$st[$p] || strpos($parentSt[$p],$st[$p])!==false)){
											unset($col['styling']['breakpoint_'.$bp][$p]);
										    }
										}
									    }
									    if(empty($col['styling']['breakpoint_'.$bp])){
										unset($col['styling']['breakpoint_'.$bp]);
									    }
									}
								    }
								}
							}
							if (!empty($col['modules'])) {
								foreach ($col['modules'] as &$mod) {
									// Check for Sub-rows
									if (!empty($mod['cols'])) {
										foreach ($mod['cols'] as &$sub_col) {
											if(isset($convert[$sub_col['element_id']])){
												$hasChange=false;
												foreach($convert[$sub_col['element_id']] as $bp=>$props){
												    if(in_array($bp,$breakpints,true)){
													foreach($props as $prop=>$v){
													    if(in_array($prop,$allowed,true)){
                                                                                                                if($bp==='desktop'){
                                                                                                                    if($v==='%'){//unit proop
                                                                                                                        $sub_col['styling'][$prop]=$v;
                                                                                                                    }
														    else{
															if(!isset($sub_col['styling'][$prop])){
															    $sub_col['styling'][$prop]='';
															}
															if(strpos($sub_col['styling'][$prop],',')===false && is_numeric($v)){
															    $v = (int)$v;
															    $sub_col['styling'][$prop].=','.$v;
															    $update=true;
															}
														    }
                                                                                                                }
                                                                                                                else{
														    if(!isset($sub_col['styling']['breakpoint_'.$bp])){
															$sub_col['styling']['breakpoint_'.$bp]=array();
														    }
                                                                                                                    if($v==='%'){//unit proop
                                                                                                                        $sub_col['styling']['breakpoint_'.$bp][$prop]=$v;
                                                                                                                    }
														    else{
															if(!isset($sub_col['styling']['breakpoint_'.$bp][$prop])){
															    $sub_col['styling']['breakpoint_'.$bp][$prop]='';
															}
															if(strpos($sub_col['styling']['breakpoint_'.$bp][$prop],',')===false && is_numeric($v)){
															    $v = (int)$v;
															    $sub_col['styling']['breakpoint_'.$bp][$prop].=','.$v;
															    $update=$hasChange=true;
															}
														    }
                                                                                                                }
													    }
													}
												    }
												}
												if($hasChange===true){
												    for($i=0;$i<$bpLength-1;++$i){
													$bp=$breakpints[$i];
													if(!empty($sub_col['styling']['breakpoint_'.$bp])){
													    $st=$sub_col['styling']['breakpoint_'.$bp];
													    foreach($allowed as $p){
														if(isset($st[$p])){
														    $parentSt=null;
														    for($j=$i+1;$j<$bpLength;++$j){
															$parentBp=$breakpints[$j];
															$parentSt=$parentBp==='desktop'?$sub_col['styling']:(isset($sub_col['styling']['breakpoint_'.$parentBp])?$sub_col['styling']['breakpoint_'.$parentBp]:null);
															if(isset($parentSt) && isset($parentSt[$p])){
															    break;
															}
														    }
														    if(isset($parentSt) && ($parentSt[$p]==$st[$p] || strpos($parentSt[$p],$st[$p])!==false)){
															unset($sub_col['styling']['breakpoint_'.$bp][$p]);
														    }
														}
													    }
													    if(empty($sub_col['styling']['breakpoint_'.$bp])){
														unset($sub_col['styling']['breakpoint_'.$bp]);
													    }
													}
												    }
												}
											}
										}
									}
								}
							}
						}
					}
				}
				unset($convert,$allowed,$breakpints);
                                if($update===true){
                                    self::$builder_is_saving = true;
                                    ThemifyBuilder_Data_Manager::update_builder_meta($id,$builder_data,false);
                                }
			}
		}
		die;
	}

	public function save_builder_css() {
	    Themify_Builder_Stylesheet::save_builder_css(true);
	}

	/**
	 * Remove Builder static content, leaving an empty shell to inject Builder output in later.
	 *
	 * @return string
	 */
	public function clear_static_content( $content ) {
            //skip for excerpt hook
            global $wp_current_filter;

            if (!in_array( 'get_the_excerpt', $wp_current_filter, true ) && !Themify_Builder_Model::is_builder_disabled_for_post_type( get_post_type() ) && ThemifyBuilder_Data_Manager::has_static_content( $content ) ) {
                    $empty_placeholder = ThemifyBuilder_Data_Manager::add_static_content_wrapper( '' );
                    $content = ThemifyBuilder_Data_Manager::update_static_content_string( $empty_placeholder, $content );
            }

            return $content;
	}

	/**
	 * Hook to content filter to show builder output
	 * @param $content
	 * @return string
	 */
	public function builder_show_on_front($content) {

	    global $post;
	    $post_id = get_the_id();
	    $is_gs_admin_page = isset($_GET['page']) && 'themify-global-styles' === $_GET['page']  && is_admin();
        // Exclude builder output in admin post list mode excerpt, Dont show builder on product single description
	    if (
            ($is_gs_admin_page===false
			&& ( ! is_object( $post )
			|| ( is_admin() && !themify_is_ajax() )
			|| (!Themify_Builder_Model::is_front_builder_activate() && false === apply_filters( 'themify_builder_display', true, $post_id ) )
			|| post_password_required()
		)) || (themify_is_woocommerce_active() && (themify_is_shop() || is_singular( 'product' )))/* disable Builder display on WC pages. Those are handled in Themify_Builder_Plugin_Compat */
		) {
			return $content;
	    }

	    //the_excerpt
	    global $wp_current_filter;
	    if (in_array('get_the_excerpt', $wp_current_filter, true)) {
		return $content?$content:$this->get_first_text($post_id);
	    }

		if ( strpos( $post->post_content, '<!--more-->' ) !==false && ! is_single( $post->ID ) && !is_page( $post->ID ) ) {
			return $content;
		}

	    return $this->get_builder_output( $post_id, $content );

	}

	/**
	 * Renders Builder data for a given $post_id
	 *
	 * If $content is sent, the function will attempt to find the proper place
	 * where Builder content should be injected to. Otherwise, raw output is returned.
	 *
	 * @return string
	 * @since 4.6.2
	 */
	function get_builder_output( $post_id, $content = '' ) {
	    if ( !Themify_Builder_Model::is_builder_disabled_for_post_type( get_post_type($post_id) ) ){
		/* in frontend editor, render only a container and set the frontend_builder_ids[] property */
		if ($post_id == self::$builder_active_id && Themify_Builder_Model::is_front_builder_activate()) {
			Themify_Builder_Stylesheet::enqueue_stylesheet(false,$post_id);
			    $builder_output = sprintf('<div id="themify_builder_content-%1$d" data-postid="%1$d" class="tf_clear themify_builder_content themify_builder_content-%1$d themify_builder"></div>', $post_id);
			    $this->get_builder_stylesheet('', $post_id);
		    } else {
			    /* Infinite-loop prevention */
			    if ( in_array( $post_id, self::$post_ids, true ) ) {
				    /* we have already rendered this, go back. */
				    return $content;
			    }
			    self::$post_ids[] = $post_id;

			    $builder_data = ThemifyBuilder_Data_Manager::get_data($post_id);
			    $template_args = array();
			    // Check For page break module
			    $page_breaks = $this->count_page_break_modules($post_id);
			    if ($page_breaks > 0) {
				    $pb_result = $this->load_current_inner_page_content($builder_data, $page_breaks);
				    $builder_data = $pb_result['builder_data'];
				    $template_args['pb_pagination'] = $pb_result['pagination'];
				    $pb_result = null;
			    }
			    $template_args['builder_output'] = $builder_data;
			    $template_args['builder_id'] = $post_id;
			    $template = $this->in_the_loop===true ? 'builder-layout-part-output.php' : 'builder-output.php';
			    $builder_output = Themify_Builder_Component_Base::retrieve_template($template, $template_args, THEMIFY_BUILDER_TEMPLATES_DIR, '', false);
			    if (strpos($builder_output, 'module_row') !== false) {
				    do_action('themify_builder_before_template_content_render');
			    }
			    if($this->in_the_loop===false){
			    Themify_Builder_Stylesheet::enqueue_stylesheet(false,$post_id);
		}
			    $this->get_builder_stylesheet($builder_output, $post_id);

			    /* render finished, make the Builder content of this particular post available to be rendered again */
			    array_pop( self::$post_ids );
		    }
		    /* if $content parameter is empty, simply return the builder output, no need to replace anything */
		    if ( $content==='' ) {
			    return $builder_output;
		    }

		    /* find where Builder output should be injected to inside $content */
		// Start builder block replacement
		if ( Themify_Builder_Model::is_gutenberg_active() && Themify_Builder_Gutenberg::has_builder_block( $content ) ) {
			    $content = ThemifyBuilder_Data_Manager::update_static_content_string( '', $content ); // remove static content tag
			    $content = Themify_Builder_Gutenberg::replace_builder_block_tag( $builder_output, $content );
		}
		    elseif ( ThemifyBuilder_Data_Manager::has_static_content( $content ) ) {
			    $content = ThemifyBuilder_Data_Manager::update_static_content_string( $builder_output, $content );
		}
		    else {
			    $display_position = apply_filters('themify_builder_display_position', 'below', $post_id);
			    if ('above' === $display_position) {
				    $content = $builder_output . $content;
			    } else {
				    $content .= $builder_output;
			    }
		}
	    }

	    return $content;
	}

	/**
	 * Load stylesheet for Builder if necessary.
	 * @return void
	 */
	public function get_builder_stylesheet($builder_output, $post_id = false,$force=false) {
	    /* in RSS feeds and REST API endpoints, do not output the scripts */
	    if (self::$frontedit_active===true || is_feed() || themify_is_rest()) {
			return;
	    }
	    if (isset($_GET['tf-scroll']) && $_GET['tf-scroll'] === 'yes' && themify_is_ajax()) {
			return '';
	    }
	    static $is = null;
	    if ($is === null && ($force===true || Themify_Builder_Model::is_front_builder_activate() || strpos($builder_output, 'module_row') !== false )) { // check if builder has any content
			$is = true;
			Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/themify.builder.script.js',THEMIFY_VERSION);
			if(!themify_is_themify_theme() || !Themify_Enqueue_Assets::addCssToFile('builder-styles-css',THEMIFY_BUILDER_URI . '/css/themify-builder-style.css',THEMIFY_VERSION,'themify_common')){
				themify_enque_style('builder-styles-css', THEMIFY_BUILDER_URI . '/css/themify-builder-style.css', null, THEMIFY_VERSION);
			}
			if(is_rtl() && !Themify_Enqueue_Assets::addCssToFile('builder-styles-rtl',THEMIFY_BUILDER_URI . '/css/themify-builder-style-rtl.css',THEMIFY_VERSION,'builder-styles-css')){
				themify_enque_style( 'builder-styles-rtl', themify_enque( THEMIFY_BUILDER_URI . '/css/themify-builder-style-rtl.css' ), null, THEMIFY_VERSION );
			}
			Themify_Enqueue_Assets::addLocalization('done','tb_style',true);
	    }
		return '';
	}

	/**
	 * Loads JS templates for front-end editor.
	 */
	public function load_javascript_template_front() {
	    add_filter('script_loader_tag', array(__CLASS__, 'defer_js'), 11, 3);
	    $this->load_frontend_interface();
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-common.php' );
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-front.php' );
	}

	/**
	 * Loads JS templates for WordPress admin dashboard editor.
	 */
	public function load_javascript_template_admin() {
	    $this->load_admin_interface();
	    self::print_static_content_badge_templates();
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-common.php' );
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-admin.php' );
	}

        /**
        * Check Builder can edit current post or not
         * If yes return the post id, otherwise return false
		 */
        public static function builder_is_available() {
                    $is=true;
		    $post_id=false;
                    if(themify_is_shop()){
                        $post_id=themify_shop_pageId();
                    }
                    elseif(!is_archive() && !is_home() && !is_search() && !is_404()){
			$p=get_queried_object(); //get_the_ID can back wrong post id
			$post_id=isset( $p->ID ) ? $p->ID : false;
			unset( $p );
                    }
		    else{
			$is=false;
		    }
		    if(!empty($post_id)){
			$is=Themify_Builder_Model::is_frontend_editor_page($post_id);
			if($is===true){
			    $is=!Themify_Builder_Model::is_builder_disabled_for_post_type( get_post_type($post_id) );
			}
		    }
                    $is = apply_filters( 'themify_builder_admin_bar_is_available',$is  );
                    return $is===true?$post_id:false;
		}

	/**
	 * Display Toggle themify builder
	 * wp admin bar
	 */
	public function builder_admin_bar_menu($wp_admin_bar) {
		if(!is_admin_bar_showing()){
			return;
		}
		$post_id=self::builder_is_available();
		$isAvailable = $post_id!==false;
		$args=array(
			array(
				'id'=>'themify_builder',
				'title'=>'',
				'href'=>'#',
				'meta'=>array(
				    'class'=>'toggle_tb_builder',
				    'onclick'=>'javascript:;'
				)
			)
		);
		$args=apply_filters( 'themify_builder_admin_bar_menu',$args,$isAvailable );
		if($isAvailable===false){
			if(isset($args[1])){
				foreach($args as $b){
					if(isset($b['id']) && is_numeric($b['id'])){
						$post_id=$b['id'];
						break;
					}
				}
			}
			if(!$post_id){
			    $args[0]['meta']['class'].=' tb_disabled_turn_on';
			}
			else{
			    $isAvailable=true;
			}
		}
		$args[0]['title']='<span data-id="'.($post_id?$post_id:'').'" class="tb_front_icon">'.themify_get_icon('ti-themify-favicon-alt','ti',false,false,array('style'=>'width:18px;height:18px;color:#ffcc08')).'</span>';
		$args[0]['title'].='<span class="tb_tooltip tf_hide">'.__( 'Builder is not available on this page','themify' ).'</span>'.esc_html__( 'Turn On Builder','themify' );
		foreach($args as $arg){
			$wp_admin_bar->add_node( $arg );
		}
	}

	/**
	 * Switch to frontend
	 * @param int $post_id
	 */
	public function switch_frontend($post_id) {
	    //verify post is not a revision
	    if (isset($_POST['tb_switch_frontend']) && $_POST['tb_switch_frontend'] === 'yes' && self::$builder_is_saving!==true && !wp_is_post_revision($post_id)) {
		$post_url = get_permalink($post_id);
		wp_redirect(themify_https_esc($post_url) . '#builder_active');
		exit;
	    }
	}

	/**
	 * Disable WP Editor
	 */
	public function disable_wp_editor() {
	    if (Themify_Builder_Model::isWpEditorDisable() && themify_builder_get('setting-page_builder_is_active') !== 'disable') {
		$module_list = $this->get_flat_modules_list(get_the_ID(), null, true);

		echo '<div class="tb_wp_editor_holder' . (!empty($module_list) ? ' tb_active_holder' : '' ) . '">
                                <a href="' . get_permalink() . '#builder_active">' . esc_html__('Edit With Themify Builder', 'themify') . '</a>
                        </div>';
		unset($module_list);
	    }
	}

	/**
	 * Add Builder body class
	 * @param $classes
	 * @return mixed|void
	 */
	public function body_class($classes) {
	    if (Themify_Builder_Model::is_frontend_editor_page()) {
		if (Themify_Builder_Model::is_front_builder_activate()) {
		    $classes[] = 'themify_builder_active builder-breakpoint-desktop';
		}
		if(Themify_Global_Styles::$isGlobalEditPage===true){
			$classes[]='gs_post';
		}
	    }
	    if(Themify_Builder_Model::is_animation_active()){
		    $classes[]='tb_animation_on';
		}
	    return apply_filters('themify_builder_body_class', $classes);
	}

	public function inline_css(){
	    $is_animation=Themify_Builder_Model::is_animation_active();
	    $is_parallax=Themify_Builder_Model::is_parallax_active();
	    $is_lax=Themify_Builder_Model::is_scroll_effect_active();
	    $is_sticky=Themify_Builder_Model::is_sticky_scroll_active();
	    $is_builder_active=Themify_Builder_Model::is_front_builder_activate();
	    $bp=themify_get_breakpoints();
	    $mobile=$bp['mobile'];
	    $tablet=$bp['tablet'][1];
	    $st=$noscript='';
	    if($is_animation!==false){
		    $st='.tb_animation_on{overflow-x:hidden}.themify_builder .wow{visibility:hidden;animation-fill-mode:both}[data-tf-animation]{will-change:transform,opacity,visibility}';
		    if($is_animation==='m'){
			    $st='@media(min-width:'.$tablet.'px){'.$st.'}';
		    }
		    if($is_builder_active===true){
			    $st.='.hover-wow.tb_hover_animate{animation-delay:initial!important}';
		    }
		    else{
			    $noscript='.themify_builder .wow,.wow .tf_lazy{visibility:visible!important}';
		    }
	    }
	    if($is_parallax!==true){
		    $p='.themify_builder .builder-parallax-scrolling{background-position-y:0!important}';
		    if($is_parallax==='m'){
			    $p='@media(max-width:'.$tablet.'px){'.$p.'}';
		    }
		    $st.=$p;
	    }
	    if($is_lax!==false){
		    $p='.themify_builder .tf_lax_done{transition-duration:.8s;transition-timing-function:cubic-bezier(.165,.84,.44,1)}';
		    if($is_lax==='m'){
			    $p='@media(min-width:'.$tablet.'px){'.$p.'}';
			    $p.='@media(max-width:'.($tablet+2).'px){.themify_builder .tf_lax_done{opacity:unset!important;transform:unset!important;filter:unset!important}}';
		    }
		    $st.=$p;
	    }
	    if($is_sticky!==false){
		    $p='[data-sticky-active].tb_sticky_scroll_active{z-index:1}[data-sticky-active].tb_sticky_scroll_active .hide-on-stick{display:none}';
		    if($is_sticky==='m'){
			    $p='@media(min-width:'.$tablet.'px){'.$p.'}';
		    }
		    $st.=$p;
	    }
	    $bp=array('desktop'=>($bp['tablet_landscape'][1]+1))+$bp;
	    $visiblity_st='';
	    $p=$is_builder_active===true?'display:none!important':'width:0!important;height:0!important;padding:0!important;visibility:hidden!important;margin:0!important;display:table-column!important;background:0!important';
	    foreach($bp as $k=>$v){
		$visiblity_st.='@media(';
		if(is_array($v)){
		    $visiblity_st.='min-width:'.$v[0].'px) and (max-width:'.$v[1].'px)';
		}else{
		    $visiblity_st.=$k==='desktop'?'min':'max';
		    $visiblity_st.='-width:'.$v.'px)';
		}
		$visiblity_st.='{.hide-'.$k.'{'.$p.'}}';
	    }
	    unset($bp);
	    $st.=$visiblity_st;
	    $gutters=Themify_Builder_Model::get_gutters(false);
	    if(!empty($gutters)){
		$gutter_st='';
		foreach($gutters as $k=>$v){
			$gutter_st.='--'.$k.':'.$v.'%;';
		}
		if($gutter_st!==''){
			$st.='div.row_inner,div.subrow_inner{'.$gutter_st.'}';
		}
	    }
	    $st.='@media(max-width:'.$mobile.'px){
		    .themify_map.tf_map_loaded{width:100%!important}
		    .ui.builder_button,.ui.nav li a{padding:.525em 1.15em}
		    .fullheight>.row_inner:not(.tb_col_count_1){min-height:0}
	    }';
	    echo '<style id="tb_inline_styles" data-no-optimize="1">',$st,'</style>';

	    if($noscript!==''){
		    echo '<noscript><style>',$noscript,'</style></noscript>';
	    }
	}

	public function admin_body_class($classes) {
	    return $classes.' builder-breakpoint-desktop tb_panel_closed';
	}

	/**
	 * Includes this custom post to array of cpts managed by Themify
	 * @param Array
	 * @return Array
	 */
	public function extend_post_types($types) {
	    static $post_types = null;
	    if ($post_types === null) {
		$post_types = array_unique(array_merge(
				$this->registered_post_types, array_values(get_post_types(array(
		    'public' => true,
		    '_builtin' => false,
		    'show_ui' => true,
				)))
		));
	    }
	    return array_unique(array_merge($types, $post_types));
	}

	/**
	 * Push the registered post types to object class
	 * @param $type
	 */
	public function push_post_types($type) {
	    $this->registered_post_types[] = $type;
	}

	/**
	 * Reset builder query
	 * @param $action
	 */
	public function reset_builder_query($action = 'reset') {
	    if ('reset' === $action) {
		remove_filter('the_content', array($this, 'builder_show_on_front'), 11);
	    } elseif ('restore' === $action) {
		add_filter('the_content', array($this, 'builder_show_on_front'), 11);
	    }
	}

	/**
	 * Get google fonts
	 */
	public function get_custom_google_fonts() {
	    global $themify;
	    $fonts = array();
	    if (!empty($themify->builder_google_fonts)) {
		$themify->builder_google_fonts = substr($themify->builder_google_fonts, 0, -1);
		$fonts = explode('|', $themify->builder_google_fonts);
	    }
	    return $fonts;
	}

	/**
	 * Static badge js template
	 */
	private static function print_static_content_badge_templates() {
	    ?>
	    <script type="text/html" id="tmpl-tb-static-badge">
	        <div class="tb_static_badge_box">
		    <?php if (!Themify_Builder_Model::is_gutenberg_editor()): ?>
			<h4><?php esc_html_e('Themify Builder Placeholder', 'themify'); ?></h4>
			<p><?php esc_html_e('This badge represents where the Builder content will append on the frontend. You can move this placeholder anywhere within the editor or add content before or after.', 'themify'); ?></p>
			<p><?php echo sprintf('%s <a href="#" class="tb_mce_view_frontend_btn">%s</a> | <a href="#" class="tb_mce_view_backend_btn">%s</a>', esc_html__('Edit Builder:', 'themify'), esc_html__('Frontend', 'themify'), esc_html__('Backend', 'themify')); ?></p>
		    <?php endif; ?>
	        </div>
	    </script>
	    <?php if (Themify_Builder_Model::is_gutenberg_editor()): ?>
		<div style="display: none;"><?php wp_editor(' ', 'tb_lb_hidden_editor'); ?></div>
	    <?php endif; ?>
	    <?php
	}

	/**
	 * Register css in tinymce editor.
	 * @param string $mce_css
	 * @return string
	 */
	public function builder_static_badge_css($mce_css) {
	    $mce_css .= ', ' . themify_enque(THEMIFY_BUILDER_URI . '/css/editor/backend/themify-builder-static-badge.css');
	    return $mce_css;
	}

	/**
	 * Save Module Favorite Data
	 *
	 * @return void
	 */
	public function save_module_favorite_data() {
            check_ajax_referer('tf_nonce', 'nonce');
            if(isset($_POST['module_state'],$_POST['module_name'])){
                $module = $_POST['module_name'];
                $module_state =  (int)$_POST['module_state'];
                $user_id=get_current_user_id();
                $key='themify_module_favorite';
                $user_favorite_modules = Themify_Builder_Model::get_favorite_modules();
                if($module_state===1){
                    $user_favorite_modules[]=$module;
                    $user_favorite_modules= array_unique($user_favorite_modules);
                }
                elseif(!empty($user_favorite_modules)){
                    $index=array_search($module, $user_favorite_modules);
                    if($index!==false){
                        array_splice($user_favorite_modules, $index, 1);
                    }
                }
                if(!empty($user_favorite_modules)){
                    update_user_option($user_id,$key, json_encode($user_favorite_modules));
                }
                else{
                    delete_user_option($user_id, $key);
                }
            }
	    die('1');
	}

	public function load_visual_templates() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    $response = array();
            self::$frontedit_active=true;
	    foreach (Themify_Builder_Model::$modules as $module) {
		$template = $module->print_template();
		if ($template) {
		    $response[$module->slug] = preg_replace('!\s+!', ' ', $template);
		}
	    }
            //don't use wp_send_json it's very heavy,this array can be very big
            header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	    echo json_encode($response);
	    wp_die();
	}

	public static function getComponentJson($onlyStyle = false,$offset=-1,$limit=1000) {
	    $return = array();
	    $i=0;
	    foreach ( array( 'Row', 'Subrow', 'Column' ) as $slug ) {
		if($i>=$offset && $i<$limit){
		    $class_name = 'Themify_Builder_Component_' . $slug;
		    $c=new $class_name();
		    $return[ $c->get_name() ] =  $c->get_form_settings($onlyStyle);
		}
		++$i;
	    }
	    foreach (Themify_Builder_Model::$modules as $k => $c) {
		if($i>=$offset && $i<$limit){
		    $return[$k] = $c->get_form_settings($onlyStyle);
		}
		    ++$i;
	    }
	    return $return;
	}

	public function load_form_templates() {
	    check_ajax_referer('tf_nonce', 'nonce');
            $limit=35;
            $page=isset($_POST['page'])?((int)$_POST['page']):1;
            $offset = ($page-1)*$limit;

            //don't use wp_send_json it's very heavy,this array can be very big
            header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	    echo json_encode(self::getComponentJson(false,$offset,$page*$limit));
	    wp_die();
	}

	public static function update_tick() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    self::$frontedit_active= true;
	    if (!empty($_POST['bid'])) {
		if(!empty($_POST['count'])){
		    global $wp_roles;
		    $roles = $wp_roles->get_names();
		    unset($roles['subscriber'] );
		    $users = get_users( array( 'role_in' => array_keys($roles), 'orderby' =>'ID','number' => 2,'fields'=>array('ID') ) );
		    if(count($users)<2){
			wp_die('cancel');
		    }
		}
		$id = (int) $_POST['bid'];
		$uid = Themify_Builder_Model::get_edit_transient($id);
		$current = get_current_user_id();
		//print_r($roles);
		if (!$uid || $uid == $current || !empty($_POST['take'])) {
		    Themify_Builder_Model::set_edit_transient($id, $current);
		}
		elseif ($uid && $uid != $current) {
		    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-locked.php' );
		}
	    }
	    wp_die();
	}

	public function help() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    include THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-help.php';
	    wp_die();
	}

	/**
	 * Load content of only current inner page
	 * @param $builder_data
	 * @param $page_breaks count of page break modules
	 * @return array
	 */
	public function load_current_inner_page_content($builder_data, $page_breaks) {
	    $p = !empty($_GET['tb-page'])?(int)$_GET['tb-page']:1;
	    $temp_data = array();
	    $page_num = 1;
	    foreach ($builder_data as $row) {
			if (isset($row['styling']['custom_css_row']) && strpos($row['styling']['custom_css_row'], 'tb-page-break') !== false) {
				++$page_num;
			}
			else{
				$temp_data[$page_num][] = $row;
			}
	    }
	    unset($builder_data,$page_num);
	    ++$page_breaks;
	    $p = ($p > $page_breaks || $p < 1) ? 1 : $p;
	    return array(
		'pagination'=>Themify_Builder_Component_Base::get_pagination('','','tb-page',0,$page_breaks,$p),
		'builder_data'=>isset($temp_data[$p]) ? $temp_data[$p] : $builder_data
	    );
	}

	/**
	 * Check builder content for page break module
	 * @param $post_id
	 * @return int count of page break modules
	 */
	private function count_page_break_modules($post_id) {
	    $data = ThemifyBuilder_Data_Manager::get_data($post_id,true);
	    return preg_match_all('/"mod_name":"page-break"/', $data, $modules);
	}


	/**
	 * Get all posts from all post type that has builder data as post meta
	 * @return array posts id
	 * @since 4.1.2
	 */
	public static function get_ajax_builder_posts() {
	    check_ajax_referer( 'tf_nonce', 'nonce' );
	    if(current_user_can('edit_pages')){
		$result = array();
		$page=!empty($_POST['page'])?(int)$_POST['page']:1;
		$limit=8;
		$args = array(
		    'post_type' =>get_post_types(),
		    'posts_per_page' =>$limit,
		    'fields'=>'ids',
		    'post_status'=>'any',
		    'paged' => $page,
		    'order'=>'ASC',
		    'orderby'=>'ID',
		    'update_post_term_cache' => false,
		    'ignore_sticky_posts'=>true,
		    'update_post_meta_cache' => false,
		    'lazy_load_term_meta'=>false,
		    'cache_results' => false,
		    'meta_query' => array(
			array(
			    'key' => ThemifyBuilder_Data_Manager::META_KEY,
			    'compare_key'=>'=',
			    'compare' => 'EXISTS'
			)
		    ),
		);
		$query = new WP_Query($args);
		if($page===1){
		    $result['pages'] = $query->max_num_pages;
		    $result['total']=$query->found_posts;
		    $result['labels']=array(
			'same_url'=>__('Same Url','themify'),
			'searching'=>__('Searching "%find%" in posts(%count%/%total%): %posts%','themify'),
			'found'=>__('Found "%find%" in posts(%count%): %posts%','themify'),
			'saving'=>__('Saving posts(%count%/%total%): %posts%','themify'),
			'no_found'=>__('There is no builder containg "%find%"','themify'),
			'error'=>__('There are some errors: %posts%','themify'),
			'wrong_url'=>__('Please type url','themify'),
			'done'=>__('Done','themify')
		    );
		}
		if(!empty($query->posts)){
		    foreach($query->posts as $id){
			$result['posts'][] = array('data'=>ThemifyBuilder_Data_Manager::get_data($id),'title'=> get_the_title($id),'id'=>$id);
		    }
		}
		wp_send_json_success($result);
	    }
	    else{
		wp_send_json_error(__('You Don`t have permission to edit pages','themify'));
	    }
	    wp_send_json_error();
	}

	public static function save_ajax_builder_mutiple_posts(){
	    check_ajax_referer( 'tf_nonce', 'nonce' );
	    if(current_user_can('edit_pages')){
		if ( isset( $_POST['data'] ) ) {
		    $data =stripslashes_deep( $_POST['data'] );
		}
		elseif ( isset( $_FILES['data'] ) ) {
		    $data = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(!empty($data)){//don't use empty, when builder is empty need to remove builder data
		    $data =json_decode( $data, true );
		    $results = array();
		    foreach($data as $post_id=>$data){
			if(current_user_can('edit_post',$post_id)){
			    $res=ThemifyBuilder_Data_Manager::save_data($data,$post_id);
			    $results[$post_id]=!empty($res['mid'])?1:sprintf(__('Can`t save builder data of post "%s"','themify'), get_the_title($post_id));
			}
			else{
			    $results[$post_id]=sprintf(__('You don`t have permission to edit post "%s"','themify'), get_the_title($post_id));
			}
		    }
		    wp_send_json_success($results);
		}
	    }
	    else{
		wp_send_json_error(__('You don`t have permission to edit pages','themify'));
	    }
	    wp_send_json_error();
	}

	/**
	 * Actions to perform when login via Login module fails
	 *
	 * @since 4.5.4
	 */
	function wp_login_failed( $username ) {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return;
		}
		$referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
		// if there's a valid referrer, and it's not the default log-in screen
		if ( isset( $_POST['tb_login'], $_POST['tb_redirect_fail'] )  && (int) $_POST['tb_login'] === 1 && ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
				wp_redirect( $_POST['tb_redirect_fail'] );
				exit;
			}
	}

	/**
	 * Handles Ajax request to get dynamic values 
	 *
	 * Calls "tb_select_dataset_{$dataset}" filter
	 *
	 * @since 4.6.5
	 */
	public function get_ajax_data() {
		check_ajax_referer('tf_nonce', 'nonce');
		if ( empty( $_POST['dataset']) && empty($_POST['mode']) )  {
			wp_send_json_error();
		}
		$pid = (int) $_POST['bid'];
		$dataset = isset($_POST['dataset'])?sanitize_text_field( $_POST['dataset'] ):'';
		$mode=isset($_POST['mode'])?$_POST['mode']:null;

		if($dataset==='taxonomy'){
		    $result=Themify_Builder_Model::get_public_taxonomies();
		}
		elseif($dataset==='menu'){
		    $menu= get_terms(array( 'taxonomy'=>'nav_menu','hide_empty' => false));
		    $result=array(''=> __('Select a Menu...', 'themify'));
		    foreach($menu as $m){
			$result[$m->slug]=$m->name;
		    }
		    unset($menu);
		}
		elseif($dataset==='gallery_shortcode'){
		    if(empty($_POST['val'])){
			wp_send_json_error();
		    }
		    $images = themify_get_gallery_shortcode(sanitize_text_field($_POST['val']));
		    $result = array();
		    if (!empty($images)) {
			foreach ($images as $image) {
			    $img_data = wp_get_attachment_image_src($image->ID, 'thumbnail');
			    $result[] = array('id'=>$image->ID,'url'=>$img_data[0]);
			}
		    }
		    unset($images);
		}
		elseif($mode==='autocomplete'){
		    if(empty($_POST['value'] )){
			wp_send_json_error();
		    }
		    $value = sanitize_text_field( $_POST['value'] );
		    if($dataset==='authors'){
			$users = get_users( array(
			    'search' => '*' . $value . '*',
			    'number' => 50,
			    'search_columns' => [ 'user_login' ],
			    'fields' => [ 'user_login' ],
			) );
			$logins = wp_list_pluck( $users, 'user_login' );
			$result= array_combine( $logins, $logins );
		    }
		    elseif($dataset==='custom_fields'){
			global $wpdb;
			$arr = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DISTINCT BINARY meta_key FROM {$wpdb->prefix}postmeta WHERE meta_key like %s LIMIT 50",
					esc_sql($value) . '%'
				),
			OBJECT );
			$result= wp_list_pluck( $arr, 'BINARY meta_key', 'BINARY meta_key' );
		    }
		    elseif($dataset!==''){
			$result = apply_filters( "tb_autocomplete_dataset_{$dataset}", array(), $value, $pid );
		    }
		}
		else{
		    $result = apply_filters( "tb_select_dataset_{$dataset}", array(), $pid );
		}
		/**
		 * The return value should be in the format of:
		 *
		 *     array(
		 *         'options' => array(
		 *             {value} => {label},
		 *         )
		 *     )
		 *
		 * Or for select fields with multiple groups:
		 *
		 *     array(
		 *         'optgroup' => true,
		 *         'options' => array(
		 *             {group_key} => array(
		 *                 'label' => {group_label},
		 *                 'options' => array(
		 *                     {option_value} => {option_label},
		 *                 )
		 *             ),
		 *         )
		 *     )
		 *
		 */
		wp_send_json_success( $result );
	}



	public function load_editor(){
	    global $wp_scripts, $wp_styles,$concatenate_scripts,$wp_actions;
	    if(!defined('CONCATENATE_SCRIPTS')){
		define( 'CONCATENATE_SCRIPTS', false );
	    }
	    $concatenate_scripts=false;

	    /* ensure $wp_scripts and $wp_styles globals have been initialized */
	    wp_styles();
	    wp_scripts();

		/* force load uncompressed TinyMCE script, fix script issues on frontend editor */
	    $wp_scripts->remove( 'wp-tinymce' );
	    wp_register_tinymce_scripts( $wp_scripts, true ); // true: $force_uncompressed

	    //store original
	    if(is_object($wp_styles)){
	    	$tmp_styles=clone $wp_styles;
	    }
	    $tmp_scripts=clone $wp_scripts;
	    $new = array();

	    foreach ($tmp_scripts->registered as $k => $v) {
			$new[$k] = clone $v;
	    }

	    $scripts_registered=$new;
		if(isset($tmp_styles)){
			$new=array();
			foreach ($tmp_styles->registered as $k => $v) {
				$new[$k] = clone $v;
			}
			$styles_registered=$new;
		}
	    $new=null;
	    //don't allow to load wp_enqueue_media thirdy party plugins
	    $wp_actions[ 'wp_enqueue_media' ]=true;

	    //for thirdy party plugins maybe they use wp_enqueue_scripts hook to add localize data in tinymce,the js files we don't need to check by wp standart they should use mce_external_plugins
	    do_action('wp_enqueue_scripts');
	    $data=!empty($wp_scripts->registered['editor']->extra['data'])?$wp_scripts->registered['editor']->extra['data']:null;

	    //restore original because we only need the js/css related with wp editor only,otherwise they will be loaded in wp_footer
	    if(isset($tmp_styles)){
			$tmp_styles->registered=$styles_registered;
			$wp_styles=clone $tmp_styles;
			$tmp_styles=$styles_registered=null;
		}
	    $tmp_scripts->registered=$scripts_registered;

	    $wp_scripts=clone $tmp_scripts;
	    $tmp_scripts=$scripts_registered=null;

	    $wp_scripts->done[]='jquery';
	    $wp_scripts->done[]='jquery-core';
	    unset($wp_actions[ 'wp_enqueue_media' ]);
	    echo '<div id="tb_tinymce_wrap">';
		if (current_user_can('upload_files')) {
		    wp_enqueue_media();
		}
		echo '<div style="display:none;">';
		    wp_editor(' ', 'tb_lb_hidden_editor');
		echo '</div>';
		if(!empty($wp_scripts->registered['editor']->deps)){
		    $wp_scripts->registered['editor']->deps[]='wp-tinymce-root';
		}
		else{
		    $wp_scripts->registered['editor']->deps=array('wp-tinymce-root');
		}
		//wp is returing is_admin() true on the ajax call,that is why we have to add these hooks manually
		add_action( 'wp_print_footer_scripts', array( '_WP_Editors', 'editor_js' ), 50 );
		add_action( 'wp_print_footer_scripts', array( '_WP_Editors', 'force_uncompressed_tinymce' ), 1 );
		add_action( 'wp_print_footer_scripts', array( '_WP_Editors', 'enqueue_scripts' ), 1 );
		wp_footer();

		if($data!==null){
		    echo '<script>',$data,'</script>';
		}
	    echo '</div>';
	    die;
	}


	private static function plugins_compatibility(){
		$plugins=array(
			'autooptimize' => 'autoptimize/autoptimize.php',
			'bwpminify' => 'bwp-minify/bwp-minify.php',
			'cachepress' => 'sg-cachepress/sg-cachepress.php',
			'dokan' => 'dokan-pro/dokan-pro.php',
			'duplicateposts' => 'duplicate-post/duplicate-post.php',
			'enviragallery' => 'envira-gallery/envira-gallery.php',
			'eventscalendar' => 'the-events-calendar/the-events-calendar.php',
			'gallerycustomlinks' => 'wp-gallery-custom-links/wp-gallery-custom-links.php',
			'maxgalleriamedialibpro' => class_exists( 'MaxGalleriaMediaLibPro' ),
			'members' => 'members/members.php',
			'pmpro' => 'paid-memberships-pro/paid-memberships-pro.php',
			'rankmath' => 'seo-by-rank-math/rank-math.php',
			'relatedposts' => 'wordpress-23-related-posts-plugin/wp_related_posts.php',
			'smartcookie' => 'smart-cookie-kit/plugin.php',
			'thrive' => 'thrive-visual-editor/thrive-visual-editor.php',
			'wcmembership' => 'woocommerce-membership/woocommerce-membership.php',
			'woocommerce' => 'woocommerce/woocommerce.php',
			'wpml' => 'sitepress-multilingual-cms/sitepress.php',
			'wpjobmanager' => 'wp-job-manager/wp-job-manager.php',
			'eventsmadeeasy' => 'events-made-easy/events-manager.php',
			'essentialgrid' => 'essential-grid/essential-grid.php',
			'armember' => 'armember/armember.php',
			'statcounter' => 'official-statcounter-plugin-for-wordpress/StatCounter-Wordpress-Plugin.php',
			'eventtickets' => 'event-tickets/event-tickets.php',
            'wpcourseware' => 'wp-courseware/wp-courseware.php',
            'facetwp' => 'facetwp/index.php',
		);
		foreach ($plugins as $plugin => $active_check ) {
			if ( $active_check === true || ( is_string( $active_check ) && Themify_Builder_Model::is_plugin_active( $active_check ) ) ) {
				include( THEMIFY_BUILDER_INCLUDES_DIR . '/plugin-compat/' . $plugin . '.php' );
				$classname = "Themify_Builder_Plugin_Compat_{$plugin}";
				$classname::init();
			}
		}
		unset($plugins);
	}

	/**
	 * Hide Builder meta fields from Custom Fields admin panel
	 *
	 * @hooked to "is_protected_meta"
	 * @return bool
	 */
	function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( $meta_key === 'tbp_custom_css' ) {
			$protected = true;
		}

		return $protected;
	}

    public static function display_tooltip($builder_id, $options, $type){
        if (isset($options['element_id']) && !empty( $options['styling']['_tooltip'] )) {
            $element_id=$options['element_id'];
            if(!isset(self::$tooltips[$builder_id])){
                self::$tooltips[$builder_id]=array();
            }
            self::$tooltips[$builder_id][$element_id]=array('t'=>esc_html( $options['styling']['_tooltip'] ));
            if ( ! empty( $options['styling']['_tooltip_bg'] ) ) {
                self::$tooltips[$builder_id][$element_id]['bg']= Themify_Builder_Stylesheet::get_rgba_color( $options['styling']['_tooltip_bg'] );
            }
            if ( ! empty( $options['styling']['_tooltip_w'] ) ) {
                $unit = empty( $options['styling']['_tooltip_w_unit'] ) ? 'px' : $options['styling']['_tooltip_w_unit'];
                self::$tooltips[$builder_id][$element_id]['w'] = $options['styling']['_tooltip_w'] . $unit;
            }
            if ( ! empty( $options['styling']['_tooltip_c'] ) ) {
                self::$tooltips[$builder_id][$element_id]['c']= Themify_Builder_Stylesheet::get_rgba_color( $options['styling']['_tooltip_c'] );
            }
        }
    }


    public static function get_i18n() {
	    global $wp_registered_sidebars;
	    $sidebars = array();
	    $sidebars[''] = '';
	    foreach ($wp_registered_sidebars as $v) {
		$sidebars[$v['id']] = $v['name'];
	    }
	    $aligment=Themify_Builder_Model::get_text_aligment();
	    $exclude_justify=array_slice($aligment,0,3);
	    $y = __('Yes', 'themify');
	    $n = __('No', 'themify');
	    $options = array(
                'convertingOldData'=>__('Coverting old data. Please wait...', 'themify'),
                'oldDataConverted'=>__('Old data has been converted successfully', 'themify'),
                'gridChanged'=>__('The columns have been set to "auto" in %s.', 'themify'),
                'gutterChanged'=>__('The gutter has been changed from %from to %to in %bp.', 'themify'),
                'postBuilderNotExist'=>__('The post doesn&apos;t contain Builder data', 'themify'),
                'importBuilderNotExist'=>__('Import file doesn&apos;t contain Builder data', 'themify'),
                'importWrongFormat'=>__('Import file should be .zip or .txt file', 'themify'),
                'zipFileEmpty'=>__('Zip file doesn&apos;t contain files', 'themify'),
                'importSelectPost'=>__('Please select a post to import', 'themify'),
                'convertExportUrls' => __('Converting shortcodes URLs. Please wait...', 'themify'),
                'createZip' => __('Trying to create the zip file. Please wait...', 'themify'),
                'createJson' => __('Trying to create the JSON file. Please wait...', 'themify'),
                'lightBoxRequiredFields'=>__('Please enter the required fields', 'themify'),
		'confirmRestoreRev' => __('Save the current state as a revision before replacing?', 'themify'),
		'dialog_import_page_post' => __('Would you like to replace or append the layout?', 'themify'),
		'confirm_on_duplicate_page' => __('Save before duplicating this page?', 'themify'),
		'confirm_template_selected' => __('Would you like to replace or append the layout?', 'themify'),
                'repeatRowDeleteConfirm' => __('Confirm to delete this?', 'themify'),
		'enterRevComment' => __('Add optional revision comment:', 'themify'),
		'confirmDeleteRev' =>  __('Confirm to delete this revision?', 'themify'),
		'revSaved' =>  __('Revision %rev_title% has been created for %post_title%', 'themify'),
		'builderSaved' =>  __('%post_title% has been saved', 'themify'),
		'builderClose' => __('Would you like to save before closing?', 'themify'),
		'saveClose'=>__('Save & Close','themify'),
		'saveRevisionClose'=>__('Save as Revision and close','themify'),
                'savedLayoutTitle'=>__( 'Saved', 'themify' ),
		'layoutEditConfirm' => __('Your changes will be lost. Are you sure?', 'themify'),
		'layoutError'=>__( 'There was an error loading layouts. Please make sure your internet is connected and check if Themify site is available.', 'themify' ),
		'layoutPartSaved' => __('Layout Part has been saved', 'themify'),
                'switchToFrontendLabel' => __('Themify Builder', 'themify'),
		'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify'),
                'text_alert_sub_in_sub' => __('Error: Subrow can&apos;t contain subrow.', 'themify'),
		'text_import_layout_button' => __('Import Layout', 'themify'),
		'rowLibraryDeleteConfirm' =>  __('Confirm to delete this saved row?', 'themify'),
		'multiSelected'=>__('Multiple Selected', 'themify'),
		'moduleLibraryDeleteConfirm' => __('Confirm to delete this saved module?', 'themify'),
		'partLibraryDeleteConfirm' => __('Confirm to delete this? It can not be undone. Once the Layout Part is deleted, all pages with this Layout Part will be gone.', 'themify'),
		'incorrectImageURL' => __('Incorrect image URL. Please enter a valid image URL.', 'themify'),
		'enterGlobalStyleName' => __('Please enter Global Style name.', 'themify'),
		'errorSaveBuilder'=>__('There was an error while saving. Please try again.', 'themify'),
		'has_gs' => __('This module is using a Global Style. Adding styling to this module will override the Global Style. Click here to add styling.', 'themify'),
		'img_help'=>__('Image can not be cropped because it is on an external site', 'themify'),
		'img_help2'=>__('Image can not be cropped because it is in the content', 'themify'),
		'img_big'=>__('Your uploaded image is too large (%w x %hpx). Server will not able to resize it. Please resize it below 1600px and re-upload it.', 'themify'),
		'empty_modules'=>__('Module/addon(s) deactivated: %s', 'themify'),
		'preview' => __('Preview', 'themify'),
		'add_module' => __('Add module', 'themify'),
		'not_empty' => __('Please enter the required field.', 'themify'),
		'addSavedGS' => __('Would you like to apply the saved Global Style?', 'themify'),
		'no_op_module'=>__('There is no option for this module. Click "Styling" tab to style it.', 'themify'),
		's_v' => __('Sticky Visibility', 'themify'),
		'h_a' => __('Hide All', 'themify'),
		'de' => __('Desktop', 'themify'),
		'mo' => __('Mobile', 'themify'),
		'ta' => __('Tablet', 'themify'),
		'ta_l' => __('Tablet Landscape', 'themify'),
                'replace_builder' => __('Replace Layout', 'themify'),
                'append_builder' => __('Append to Layout', 'themify'),
                'layout_replace' => __( 'Replace Layout', 'themify' ),
                'layout_append' => __( 'Append to Layout', 'themify' ),
                'layout_error' => __( 'There was an error loading the layout. Please try again later or you can download the file: ({FILE}) and then import it manually (https://themify.me/docs/builder#import-export).', 'themify' ),
		'layoutEditUrl'=>admin_url('edit.php?post_type=' . Themify_Builder_Layouts::LAYOUT_SLUG),
                'layoutAddUrl'=>admin_url('post-new.php?post_type=' . Themify_Builder_Layouts::LAYOUT_SLUG),
                'layoutEditText'=>__('Manage Layouts', 'themify'),
                'layoutAddText'=>__('Add New', 'themify'),
		'offline'=>__('You seem to be offline. Changes may not be saved. Click <a href="#">export</a> to save the current changes.','themify'),
		'img_menu'=>array(
		    'hflip'=>__('Flip Horizontal', 'themify'),
		    'vflip'=>__('Flip Vertical', 'themify'),
		    'rotate'=>__('Rotate', 'themify'),
		    'pallete'=>__('Adjust','themify'),
		    'undo'=>__('Undo','themify'),
		    'redo'=>__('Redo','themify'),
		),
		'uploading'=>array(
		    'download_fail'=> __( 'Download failed: %post%.', 'themify' ),
		    'upload_fail'=> __( 'Upload failed (%msg%): %post%', 'themify' ),
		    'upload_images'=> __( 'Uploading image (%from%/%to%): ', 'themify' ),
		    'download_images'=> __( 'Downloading image (%from%/%to%): ', 'themify' ),
		),
		'broken_code'=>__('Found errors in Custom %s code. Save anyway?','themify'),
                'label' => array(
		    'up'=>__('Move Up', 'themify'),
		    'down'=>__('Move Down', 'themify'),
                    'cancel'=>__('Cancel', 'themify'),
                    'ok'=>__('OK', 'themify'),
		    'rmeta'=>__('Confirm to delete this metabox?', 'themify'),
		    'smodule' => __('Save Module', 'themify'),
		    'srow' => __('Save Row', 'themify'),
		    'g_s' => __('Global Style', 'themify'),
		    'query_by' => __('Query by', 'themify'),
		    'query_id' => __('Post Type', 'themify'),
		    'query_term_id' => __('Categories', 'themify'),
		    'query_tax_id' => __('Taxonomy', 'themify'),
		    'query_tag_id' => __('Tags', 'themify'),
		    'query_desc' => __('Enter multiple category IDs (eg. 2,5,8) or slug (eg. news,blog,featured) or exclude category IDs (eg. -2,-5,-8).', 'themify'),
		    'slug_desc' => __('Insert post slug(s) - separate multiple slugs with commas.', 'themify'),
		    'errorId' => __('This ID has been used on the page, please enter another one.', 'themify'),
		    'slug_label' => __('Post Slugs or IDs', 'themify'),
                    'all_posts' => __( 'All Posts', 'themify' ),
                    'sticky_first' => __( 'Show Sticky Posts First', 'themify' ),
		    'slayout_part' => __('Save as Layout Part', 'themify'),
		    'nlayout' => __('New Layout Part', 'themify'),
		    'mlayout' => __('Manage Layout Part', 'themify'),
		    'import_label' => __('%s data', 'themify'),
		    'import_data' => __('Paste %s data here', 'themify'),
		    'import_tab' => __('Import %s', 'themify'),
		    'export_tab' => __('Export %s', 'themify'),
		    'export_data' => __('You can copy & paste this data to another Builder site', 'themify'),
		    'revision' => __('Revisions', 'themify'),
		    'solid' => __('Solid', 'themify'),
		    'upload' => __('Upload', 'themify'),
		    'upload_image' => __('Upload an Image', 'themify'),
		    'insert_image' => __('Insert File URL', 'themify'),
		    'browse_image' => __('Browse Library', 'themify'),
		    'add_media' => __('Add Media', 'themify'),
		    'add_gallery' => __('Insert Gallery', 'themify'),
		    'image_preview' => __('Image Preview', 'themify'),
		    'css' => __('CSS', 'themify'),
		    'cus_css_m' => __('Custom CSS entered here will add to this page only.', 'themify'),
		    'save_as_layout' => __('Save as Layout', 'themify'),
		    'title' => __('Title', 'themify'),
		    'or' => __('or', 'themify'),
		    'slug' => __('Slug', 'themify'),
		    'google_fonts' => __('Google Fonts', 'themify'),
		    'cf_fonts' => __('Custom Fonts', 'themify'),
		    'safe_fonts' => __('Web Safe Fonts', 'themify'),
		    'font_preview' => __('Font Preview', 'themify'),
		    'visual' => __('Visual', 'themify'),
		    'text' => __('Text', 'themify'),
		    'new_row' => __('Add new', 'themify'),
		    'table_landscape' => __('Tablet Landscape', 'themify'),
		    'duplicate' => __('Duplicate', 'themify'),
		    'delete' => __('Delete', 'themify'),
		    'insert_icon' => __('Insert Icon', 'themify'),
		    'linear' => __('Linear', 'themify'),
		    'radial' => __('Radial', 'themify'),
		    'rotation' => __('Rotation', 'themify'),
		    'image' => __('Image', 'themify'),
		    'gradient' => __('Gradient', 'themify'),
		    'clear_gradient' => __('Clear', 'themify'),
		    'save_gradient' => __('Save', 'themify'),
		    'ie_gradient' => __('Import/Export', 'themify'),
		    'circle_radial' => __('Circle Radial', 'themify'),
		    'all' => __('All', 'themify'),
		    'top' => __('Top', 'themify'),
		    'top_left' => __('Top-Left', 'themify'),
		    'left' => __('Left', 'themify'),
		    'bottom' => __('Bottom', 'themify'),
		    'right' => __('Right', 'themify'),
		    'styling' => __('Styling', 'themify'),
		    'visibility' => __('Visibility', 'themify'),
		    'animation' => __('Animation', 'themify'),
		    'custom_css' => __('CSS Class', 'themify'),
		    'id_name' => __('ID Name', 'themify'),
		    'id_help' => __('ID name is used to identify element in un-stick option for "Sticky Scrolling" feature.', 'themify'),
		    'transparent' => __('Transparent', 'themify'),
		    'id_desc' => __('ID name should be unique (it is used to identify the element for Sticky Scrolling).', 'themify'),
		    'custom_css_help' => __('Add custom CSS class(es) for custom styling (<a href="https://themify.me/docs/builder#additional-css-class" target="_blank">learn more</a>).', 'themify'),
		    'reset_style' => __('Reset Styling', 'themify'),
		    'reset_effect' => __('Reset All Effects', 'themify'),
		    'ctr_save' => __('Ctrl + S', 'themify'),
		    'save' => __('Save', 'themify'),
		    'done' => __('Done', 'themify'),
		    's_s' => __('Save Style', 'themify'),
		    'search' => __('Search', 'themify'),
                    'search_widget' => __('Search widgets…', 'themify'),
		    'widget_validate' => __('Please select the Widget', 'themify'),
		    'o' => __('Outline', 'themify'),
		    'p' => __('Padding', 'themify'),
		    'm' => __('Margin', 'themify'),
		    'gap' => __('Gap', 'themify'),
		    'ng' => __('Column Gap', 'themify'),
		    'rg' => __('Row Gap', 'themify'),
		    'b' => __('Border', 'themify'),
		    'f' => __('Font', 'themify'),
		    'f_l' => __('Filters', 'themify'),
		    'f_f' => __('Font Family', 'themify'),
		    'f_w' => __('Font Weight', 'themify'),
		    'f_st' => __('Font Style', 'themify'),
		    'f_s' => __('Font Size', 'themify'),
		    'l_h' => __('Line Height', 'themify'),
		    'l_s' => __('Letter Spacing', 'themify'),
		    't_a' => __('Text Align', 'themify'),
		    't_t' => __('Text Transform', 'themify'),
		    't_d' => __('Text Decoration', 'themify'),
		    'bg' => __('Background', 'themify'),
		    'bg_c' => __('Background Color', 'themify'),
		    'b_i' => __('Background Image', 'themify'),
		    'b_r' => __('Background Repeat', 'themify'),
		    'b_p' => __('Background Position', 'themify'),
		    'r' => __('Repeat', 'themify'),
		    'f_c' => __('Font Color', 'themify'),
		    'l' => __('Link', 'themify'),
		    'o_l'=>__('Open Link In','themify'),
		    'lg'=>__('Lightbox','themify'),
		    'cl'=>__('Custom Link','themify'),
		    'c' => __('Color', 'themify'),
		    'c_t' => __('Color Type', 'themify'),
		    'col' => __('Multi-columns', 'themify'),
		    'g' => __('General', 'themify'),
		    'm_t' => __('Module Title', 'themify'),
		    'head' => __('Heading', 'themify'),
		    'n' => __('Normal', 'themify'),
		    'h' => __('Hover', 'themify'),
		    'w' => __('Width', 'themify'),
		    'ht' => __('Height', 'themify'),
		    'a_ht' => __('Auto Height', 'themify'),
		    'm_ht' => __('Min Height', 'themify'),
		    'mx_ht' => __('Max Height', 'themify'),
		    'c_c' => __('Column Count', 'themify'),
		    'c_g' => __('Column Gap', 'themify'),
		    'c_d' => __('Column Divider', 'themify'),
		    'req' => __('Required', 'themify'),
		    'b_m' => __('Blend Mode', 'themify'),
		    'r_c' => __('Corners', 'themify'),
		    'bo_r' => __('Corners', 'themify'),
		    'sh' => __('Shadow', 'themify'),
		    'b_s' => __('Box Shadow', 'themify'),
		    'h_o' => __('Horizontal Offset', 'themify'),
		    'v_o' => __('Vertical Offset', 'themify'),
		    'bl' => __('Blur', 'themify'),
		    'spr' => __('Spread', 'themify'),
		    'in_sh' => __('Inset Shadow', 'themify'),
		    't_sh' => __('Text Shadow', 'themify'),
		    'h_sh' => __('Horizontal Shadow', 'themify'),
		    'v_sh' => __('Vertical Shadow', 'themify'),
		    's_e_f' => __('Scroll Effects', 'themify'),
		    's_e_s' => __('Sticky', 'themify'),
		    's_e_m' => __('Motion', 'themify'),
		    'y' => $y,
		    'no' => $n,
		    'en'=> __('Enable', 'themify'),
		    'dis'=> __('Disable', 'themify'),
		    'hi'=>__('Hide', 'themify'),
		    's'=>__('Show', 'themify'),
		    'cus'=>__('Custom', 'themify'),
		    'F_j_Y' => __('August 18, 2019 (F j, Y)', 'themify'),
		    'Y_m_d' => __('2019-08-18 (Y-m-d)', 'themify'),
		    'm_d_Y' => __('08/18/2019 (m/d/Y)', 'themify'),
		    'd_m_Y' => __('18/08/2019 (d/m/Y)', 'themify'),
		    'def' => __('Default', 'themify'),
		    'cus_f' => __('Custom Format', 'themify'),
		    'cus_fd_h' => __('Enter date format with these letters: l D d j S F m M n Y y', 'themify'),
		    'cus_ft_h' =>  __('Enter time format with these letters: g G H i a A', 'themify'),
		    'icon' => __('Icon', 'themify'),
		    'b_t' => __('Before Text', 'themify'),
		    'a_t' => __('After Text', 'themify'),
		    'd_f' => __('Date Format', 'themify'),
		    't_f'=>__('Time Format', 'themify'),
		    'g_i_a'=> __('6:28 pm (g:i a)', 'themify'),
		    'g_i_A'=> __('6:28 PM (g:i A)', 'themify'),
		    'H_i'=>__('18:28 (H:i)', 'themify'),
		    'no_c'=>__('No Comments', 'themify'),
		    'one_c'=>__('One Comment', 'themify'),
		    'comments'=> __('Comments', 'themify'),
		    'a_p'=>__('Author Picture', 'themify'),
		    'p_s'=>__('Picture Size', 'themify'),
		    'sep'=>__('Separator', 'themify'),
			'save' => __( 'Save', 'themify' ),
			'save_no' => __( "Don't Save", 'themify' ),
			'rows_fetch_error' => __( 'Failed to load pre-designed rows from Themify server.', 'themify' ),
			'row_fetch_error' => __( 'Failed to load the row template.', 'themify' ),
			'hue' => __( 'Hue', 'themify' ),
			'sat' => __( 'Saturation', 'themify' ),
			'bri' => __( 'Brightness', 'themify' ),
			'con' => __( 'Contrast', 'themify' ),
			'inv' => __( 'Invert', 'themify' ),
			'se' => __( 'Sepia', 'themify' ),
			'op' => __( 'Opacity', 'themify' ),
			'zi' => __( 'Z-Index', 'themify' ),
			'a_wd' => __('Auto Width', 'themify'),
			'mi_wd' => __('Min Width', 'themify'),
			'ma_wd' => __('Max Width', 'themify'),
			'po' => __('Position', 'themify'),
			'auto' => __('Auto', 'themify'),
			'abs' => __('Absolute', 'themify'),
			'fi' => __('Fixed', 'themify'),
			're' => __('Relative', 'themify'),
			'st' => __('Static', 'themify'),
			'disp' => __('Display', 'themify'),
			'i_g' => __('Image Gutter', 'themify'),
			'tr' => __('Transform', 'themify'),
			'sc' => __('Scale', 'themify'),
			'tl' => __('Translate', 'themify'),
			'sk' => __('Skew', 'themify'),
			'ro' => __('Rotate', 'themify'),
			'aq' => array( /* Advanced Query */
				__( 'Date Query', 'themify' ),
				__( 'From', 'themify' ),
				__( 'To', 'themify' ),
				__( 'Custom Field Query', 'themify' ),
				__( 'Key', 'themify' ),
				__( 'Value', 'themify' ),
				__( 'Comparison', 'themify' ),
				__( 'Author(s) Query', 'themify' ),
				__( 'Add Query Filter', 'themify' ),
				__( 'Enter a comma-separated list of author IDs or usernames.', 'themify' ),
			),
			'hc' => array(
				__( 'Hook Content', 'themify' ),
				__( 'Hook Location', 'themify' ),
			),
			't' => __( 'Tooltip', 'themify' ),
			'tt' => __( 'Tooltip Text', 'themify' ),
			'cc' => __( 'CSS Class & ID', 'themify' ),
		),
		'options' => array(
		    'border_radius'=>Themify_Builder_Model::get_border_radius_styles(),
		    'border' => Themify_Builder_Model::get_border_styles(),
		    'appearance' => Themify_Builder_Model::get_appearance(),
		    'aligment' => $aligment,
		    'aligment2'=>$exclude_justify,
		    'repeat' => Themify_Builder_Model::get_repeat(),
		    'position' => Themify_Builder_Model::get_position(),
		    'text_decoration' => Themify_Builder_Model::get_text_decoration(),
		    'font_style' => Themify_Builder_Model::get_font_style(),
		    'font_weight' => Themify_Builder_Model::get_font_weight(),
		    'text_transform' => Themify_Builder_Model::get_text_transform(),
		    'frame' => Themify_Builder_Model::get_frame_layout(),
		    'preset_animation' => Themify_Builder_model::get_preset_animation(),
		    'animation' => Themify_Builder_model::get_animation(),
		    'visibility' => Themify_Builder_model::get_visibility(),
		    'color' => Themify_Builder_model::get_colors(),
		    'blend' => Themify_Builder_model::get_blend_mode(),
		    'slider_options'=>Themify_Builder_model::get_slider_options(),
		    'sidebars' => $sidebars,
		    'img_appearance'=>array(
			array('name' => 'rounded', 'value' => __('Rounded', 'themify')),
			array('name' => 'drop-shadow', 'value' => __('Drop Shadow', 'themify')),
			array('name' => 'bordered', 'value' => __('Bordered', 'themify')),
			array('name' => 'circle', 'value' => __('Circle', 'themify'), 'help' => __('Circle style works better for square image ratio.', 'themify'))
		    ),
		    'link_to'=>array(
			array( 'name' => __( 'Permalink', 'themify' ), 'value' => 'permalink' ),
			array( 'name' => __( 'Media File', 'themify' ), 'value' => 'media' ),
			array( 'name' => __( 'Custom', 'themify' ), 'value' => 'custom' ),
			array( 'name' => __( 'None', 'themify' ), 'value' => 'none' )
		    ),
		    'link_type' => array(
			array('value' => 'regular', 'name' => __('Same window', 'themify')),
			array('value' => 'lightbox', 'name' => __('Lightbox', 'themify')),
			array('value' => 'newtab', 'name' => __('New tab', 'themify'))
		    ),
		    'echoose' => array(
			'' => '',
			'yes' => $y,
			'no' => $n
		    ),
		    'choose' => array(
			'yes' => $y,
			'no' => $n
		    ),
		    'rchoose' => array(
			'no' => $n,
			'yes' => $y
		    ),
		    'order' => array(
			'desc' => __('Descending', 'themify'),
			'asc' => __('Ascending', 'themify')
		    ),
		    'orderBy' => array(
			'date' => __('Date', 'themify'),
			'ID' => __('ID', 'themify'),
			'author' => __('Author', 'themify'),
			'title' => __('Title', 'themify'),
			'name' => __('Name', 'themify'),
			'modified' => __('Modified', 'themify'),
			'rand' => __('Random', 'themify'),
			'comment_count' => __('Comment Count', 'themify'),
			'meta_value' => __('Custom Field', 'themify'),
		    ),
                    'display'=> array(
                        '' => '',
                        'block' => __('Block', 'themify'),
                        'inline-block' => __('Inline', 'themify'),
                        'none' => __('None', 'themify')
                    ),
                    'va_display'=> array(
                        '' => '',
                        'top' => __('Top', 'themify'),
                        'middle' => __('Middle', 'themify'),
                        'bottom' => __('Bottom', 'themify')
                    ),
                    'h_tags'=> Themify_Builder_Model::get_heading_tags()
		)
	    );
	    unset($sidebars);
	    $image_size = themify_get_image_sizes_list(false);
	    if (!empty($image_size)) {
			$options['options']['image_size'] = array_merge(
				array( '' => '' ),
				$image_size
			);
	    }
	    $image_size=null;
	    for ( $i = 1; $i <= 6; ++$i ) {
		$options['label']['h'.$i.'_f'] =  sprintf( __( 'Heading %s Font', 'themify' ), $i );
	    }
	    return $options;
    }

    private function get_active_builder_vars(){
	Themify_Builder_Component_Module::load_modules();
        global $wp_styles;
	$id = is_admin() ? get_the_ID() : self::$builder_active_id;
        return apply_filters('themify_builder_active_vars', array(
            'builder_data'=>ThemifyBuilder_Data_Manager::get_data( $id ),
            'includes_url' => includes_url(),
            'nonce' => wp_create_nonce('tf_nonce'),
            'disableShortcuts' => themify_builder_get('setting-page_builder_disable_shortcuts', 'builder_disable_shortcuts'),
            'widget_css' => array(home_url($wp_styles->registered['widgets']->src), home_url($wp_styles->registered['customize-widgets']->src)),
            'modules' => Themify_Builder_Model::get_modules_localize_settings(),
            'favorite'=>Themify_Builder_Model::get_favorite_modules(),
            'gutters'=>Themify_Builder_Model::get_gutters(),
            'cache_data'=>apply_filters('themify_builder_additional_cache',''),
            'i18n' => self::get_i18n(),
            'paths' => Themify_Builder_Model::get_paths(),
            'custom_css'=>get_post_meta(  $id , 'tbp_custom_css', true ),
            'post_title'=> get_the_title(  $id  ),
            'debug' => defined('THEMIFY_DEBUG') && THEMIFY_DEBUG,
            'breakpoints' => themify_get_breakpoints(),
            'cf_api_url' => Themify_Custom_Fonts::$api_url,
            'safe' => themify_get_web_safe_font_list(),
            'google'=>themify_get_google_web_fonts_list(),
            'cf'=>Themify_Custom_Fonts::get_list(),
	    'ticks'=>Themify_Builder_Model::get_transient_time(),
	    'memory'=>(int)(wp_convert_hr_to_bytes(WP_MEMORY_LIMIT)*MB_IN_BYTES)
        ));
    }

}
endif;