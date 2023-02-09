<?php

defined( 'ABSPATH' ) || exit;

if (current_user_can('manage_options')) {
	remove_action('admin_init', array('WP_Customize', 'admin_init'));
}

if (!class_exists('Themify_Customizer')) :

	/**
	 * Themify customizer controls and settings.
	 */
	class Themify_Customizer {

		/**
		 * Settings to build controls in Theme Customizer
		 * @var array
		 */
		public $settings = null;

		/**
		 * List of selector/property/property value to build CSS rules.
		 * @var array
		 */
		public $styles = array();

		/**
		 * Customizer mode, initially unset, later set to 'basic' or 'advanced'.
		 * @var string
		 */
		public $mode;

		/**
		 * Accordion identified
		 * @var string
		 */
		public $current_accordion_slug = 0;

		/**
		 * Flag to know if we're in the middle of saving the stylesheet.
		 * @var string
		 */
		public $saving_stylesheet = false;

		/**
		 * Flag to know if we're in the middle of saving the stylesheet.
		 * @var string
		 */
		public $customizer_fonts = array();

		/**
		 * Array of custom fonts
		 * @var array
		 */
		public $customizer_cf_fonts = array();

		/**
		 * CSS without media queries
		 * @see #5796
		 */
		public $global_css = '';

		/**
		 * Initialize class
		 */
		function __construct() {

			define('THEMIFY_CUSTOMIZER_URI', THEMIFY_URI . '/customizer');
			define('THEMIFY_CUSTOMIZER_DIR', THEMIFY_DIR . '/customizer');

			$config_file = array('customizer-config.php');
			if (!$this->is_advanced_mode()) {
				array_unshift($config_file, 'customizer-basic-config.php');
			}
			locate_template($config_file, true);

			// Build list of settings for live preview and CSS generation
			add_action('customize_register', array($this, 'build_settings_and_styles'), 12);

			// Initialize Theme Customizer
			add_action('customize_register', array($this, 'customize_register'), 14);

            // Load required icons
            add_action('customize_controls_print_styles', array($this, 'load_icons'));

			// Enqueue Javascript for Theme Customizer control
			add_action('customize_controls_enqueue_scripts', array($this, 'customize_control_scripts'));

			// Enqueue Javascript for Theme Customizer live preview
			add_action('customize_preview_init', array($this, 'live_preview_scripts'));

			add_action('wp_ajax_themify_customizer_save_option', array($this, 'save_option'));
			add_action('wp_ajax_themify_customizer_get_option', array($this, 'get_option'));

			// Build CSS and save stylesheet
			add_action('customize_save_after', array($this, 'write_stylesheet'));

			// Output custom styling
			add_action('wp_enqueue_scripts', array($this, 'enqueue_stylesheet'), 15);

			// Remove and/or recreate the stylesheet upon switching themes
			add_action('switch_theme', array($this, 'delete_stylesheet'));
			add_action( 'after_switch_theme', array( $this, 'write_stylesheet' ) );

			// Add another breakpoints
			add_filter('customize_previewable_devices', array($this, 'add_devices'));

			// Download Customizer Settings
			add_action('after_setup_theme', array($this, 'customizer_export'), 10);

			// Ajax hook for Customizer import file
			add_action('wp_ajax_themify_plupload_customizer', array($this, 'customizer_import'));

			add_action( 'init', array( $this, 'disable_menus_and_widgets' ) ); 
		}

		/**
		 * Build list of styling settings with:
		 * - controls for live preview
		 * - selectors/properties for CSS rules generation
		 *
		 * @since 1.0.0
		 */
		function build_settings_and_styles() {
			if ($this->settings === null) {
				////////////////////////
				// Build Controls
				////////////////////////
				$this->settings = apply_filters('themify_customizer_settings', array());

				////////////////////////
				// Rest/Import/Export Buttons
				////////////////////////
				$this->settings['tools'] = array(
					'control' => array(
						'type' => 'Themify_Tools_Control',
						'label' => __('Tools', 'themify'),
					),
					'selector' => 'tools',
					'prop' => 'tools',
				);

				////////////////////////
				// Build CSS Styling
				////////////////////////
				foreach ($this->settings as $key => $setting) {
					if (isset($setting['selector'])) {
						$this->styles[$setting['selector']][] = array(
							'prop' => isset($setting['prop']) ? $setting['prop'] : '',
							'key' => isset($key) ? $key : '',
							'prefix' => isset($setting['prefix']) ? $setting['prefix'] : '',
							'global' => isset( $setting['global'] ) ? $setting['global'] : false,
						);
					}
				}
			}
		}

		/**
		 * Parameters for accordion start.
		 *
		 * @param string $label
		 * @param string $section
		 * @return array
		 */
		function accordion_start( $label = '', $section = 'themify_options', $active_callback = null ) {
			return array(
				'control' => array(
					'type' => 'Themify_Sub_Accordion_Start',
					'label' => $label,
					'section' => $section,
					'active_callback' => $active_callback
				),
			);
		}

		/**
		 * Parameters for accordion end.
		 *
		 * @param string $label
		 * @param string $section
		 * @return array
		 */
		function accordion_end($label = '', $section = 'themify_options') {
			return array();
		}

		/**
		 * Enqueue script for custom control.
		 */
		function customize_control_scripts() {

			themify_enque_style( 'tf_base', THEMIFY_URI . '/css/base.min.css', null, THEMIFY_VERSION);

			// Font Icon CSS
			themify_enque_style('themify-icons', THEMIFY_URI . '/themify-icons/themify-icons.css', null, '2.0',null,true);

			// Minicolors
			themify_enque_style('themify-colorpicker', THEMIFY_METABOX_URI . 'css/themify.minicolors.css', null, THEMIFY_VERSION,null,true);

			// Enqueue media scripts
			wp_enqueue_media();

			// Controls CSS
			themify_enque_style('themify-customize-control', THEMIFY_CUSTOMIZER_URI . '/css/themify.customize-control.css', null, THEMIFY_VERSION,null,true);

			// Minicolors JS
			themify_enque_script('themify-colorpicker-js', THEMIFY_METABOX_URI . 'js/themify.minicolors.js', THEMIFY_VERSION, array('jquery'));
			wp_localize_script( 'themify-colorpicker-js', 'themifyCM', Themify_Metabox::themify_localize_cm_data() );

			// Plupload
			Themify_Metabox::get_instance()->admin_enqueue_scripts();
			Themify_Metabox::get_instance()->enqueue();

			//Combobox JS
			themify_enque_style('themify-combobox', THEMIFY_CUSTOMIZER_URI . '/css/jquery.scombobox.css', null, THEMIFY_VERSION,null,true);
			themify_enque_script('themify-combobox', THEMIFY_CUSTOMIZER_URI . '/js/jquery.scombobox.min.js', THEMIFY_VERSION, array('jquery'));


			// Controls JS
			themify_enque_script('themify-customize-control', THEMIFY_CUSTOMIZER_URI . '/js/themify.customize-control.js', THEMIFY_VERSION, array('jquery', 'customize-controls', 'underscore', 'backbone'));
			$controls = array(
				'nonce' => wp_create_nonce('tf_nonce'),
				'clearMessage' => __('This will reset all styling and customization. Do you want to proceed?', 'themify'),
				'exportMessage' => __('You have un-saved settings. If proceed then export will not have them. Do you want to proceed?', 'themify'),
				'confirm_on_unload' => __('You have unsaved data.', 'themify'),
				'header_transparnet' => $this->get_header_transparent() ? __('Transparent header is being selected in header background option, thus header background does not reflect on preview but it will show on pages with regular header background mode.', 'themify') : false,
				'responsiveBreakpoints' => array(
					'tablet_landscape' => themify_get('setting-customizer_responsive_design_tablet_landscape', 1024, true),
					'tablet' => themify_get('setting-customizer_responsive_design_tablet', 768, true),
					'mobile' => themify_get('setting-customizer_responsive_design_mobile', 600, true)
				),
				'mobile_menu_trigger_point' =>Themify_Enqueue_Assets::$mobileMenuActive,
				'cf_api_url' => Themify_Custom_Fonts::$api_url,
				'invalid_css' => __('You might have invalid custom CSS code. Please double check.', 'themify')
			);

			// Pass JS variables to controls
			wp_localize_script('themify-customize-control', 'themifyCustomizerControls', $controls);
		}

        /**
         * Load and print required icons
         */
        function load_icons() {
            themify_get_icon( 'ti-import','ti' );
            themify_get_icon( 'ti-export','ti' );
            Themify_Enqueue_Assets::loadIcons();
        }

		private function get_header_transparent() {
			static $header_wrap = null;
			if ($header_wrap===null && !empty($_GET['url'])) {
				$current_id = url_to_postid(esc_url($_GET['url']));
				$header_wrap = get_post_meta($current_id, 'header_wrap', true) === 'transparent';
			}
			return $header_wrap;
		}

		/**
		 * Enqueue script for live preview.
		 */
		function live_preview_scripts() {
			// Live preview JS
			themify_enque_script('themify-customize-preview', THEMIFY_CUSTOMIZER_URI . '/js/themify.customize-preview.js', THEMIFY_VERSION, array('jquery', 'customize-preview', 'underscore', 'backbone'));
			$controls = array(
				'nonce' => wp_create_nonce('tf_nonce'),
				'ajaxurl' => admin_url('admin-ajax.php'),
				'isRTL' => is_rtl(),
				'breakpoints' => $this->get_breakpoints(),
				'cf_api_url' => Themify_Custom_Fonts::$api_url
			);
			foreach ($this->settings as $key => $params) {
				if (!isset($params['selector'])) {
					continue;
				}
				if ($this->endsWith($key, '_font')) {
					$controls['fontControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_background') || $this->endsWith($key, '_background_color') ) {
					$controls['backgroundControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_position')) {
					$controls['positionControls'][$key] = $params['selector'];
				} elseif (false !== stripos($key, '-logo_')) {
					if ( $params['prop'] === 'margin' ) {
						$controls['marginControls'][$key] = $params['selector'];
					} else {
						$controls['logoControls'][$key] = $params['selector'];
						$controls['fontControls'][$key] = $params['selector'];
					}
				} elseif ($this->endsWith($key, '-tagline')) {
					$controls['taglineControls'][$key] = $params['selector'];
					$controls['fontControls'][$key] = $params['selector'];
					$controls['marginControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_border')) {
					$controls['borderControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_margin')) {
					$controls['marginControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_padding')) {
					$controls['paddingControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_width')) {
					$controls['widthControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_height')) {
					$controls['heightControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, 'border_color')) {
					$controls['borderColorControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_color')) {
					if(isset($params['prop']) && 0===strpos($params['prop'],'--')){
                        $controls['colorControls'][$key] = array('id'=>$params['selector'],'v'=>$params['prop']);
                    }else{
                        $controls['colorControls'][$key] = $params['selector'];
                    }
				} elseif ($this->endsWith($key, 'customcss')) {
					$controls['customcssControls'][$key] = $params['selector'];
				} elseif ($this->endsWith($key, '_imageselect')) {
					$controls['imageselectControls'][$key] = $params['selector'];
				}
			}

			// Pass JS variables to live preview scripts
			wp_localize_script('themify-customize-preview', 'themifyCustomizer', $controls);
		}

		/**
		 * Checks if the string ends with a certain substring.
		 * 
		 * @since 2.1.9
		 * 
		 * @param string $haystack Main string to search in.
		 * @param string $needle Substring that must be found at the end of main string.
		 * 
		 * @return bool Whether the substring is found at the end of the main string.
		 */
		function endsWith($haystack, $needle) {
			$needle_length = strlen($needle);
			$offset = strlen($haystack) - $needle_length;
			$length = $needle_length;
			return @substr_compare($haystack, $needle, $offset, $length) === 0;
		}

		/**
		 * Save a blog option.
		 *
		 * @since 1.0.0
		 */
		function save_option() {
			check_ajax_referer('tf_nonce', 'nonce');
			if (isset($_POST['option']) && isset($_POST['value'])) {
				update_option($_POST['option'], stripslashes($_POST['value']));
				echo 'saved';
			} else {
				echo 'notsaved';
			}
			die();
		}

		/**
		 * Get a blog option.
		 *
		 * @since 1.0.0
		 */
		function get_option() {
			check_ajax_referer('tf_nonce', 'nonce');
			if (!empty($_POST['option'])) {
				switch ($_POST['option']) {
					case 'blogname':
						echo preg_replace_callback("/(&#[0-9]+;)/", array($this, 'decode_entities'), html_entity_decode(get_bloginfo('name')));
						break;
					case 'blogdescription':
						echo preg_replace_callback("/(&#[0-9]+;)/", array($this, 'decode_entities'), html_entity_decode(get_bloginfo('description')));
						break;
					default:
						echo get_option($_POST['option']);
						break;
				}
			} else {
				echo 'notfound';
			}
			die;
		}

		/**
		 * Checks if the user enabled advanced or basic mode.
		 * Saves option to database to fetch when customizer mode is not indicated by $_GET var.
		 *
		 * @since 2.0.6
		 *
		 * @return bool
		 */
		function is_advanced_mode() {
			if (!isset($this->mode)) {
				$key = 'themify_customizer';
				// Check that var set is only 'advanced' or 'basic' since it will be saved to db
				if (isset($_GET[$key]) && ( 'advanced' === $_GET[$key] || 'basic' === $_GET[$key] )) {
				    $this->mode =$_GET[$key];
				    delete_option($key);
				    add_option($key,$this->mode, '', false );
				} else {
					$this->mode = 'advanced' === get_option($key, 'basic')? 'advanced' : 'basic';
				}
			}
			return 'advanced' === $this->mode;
		}

		/**
		 * Converts encoding for HTML entities not catched by html_entity_decode.
		 * @param array $matches
		 * @return string
		 */
		function decode_entities( $matches ) {
			if ( function_exists( 'mb_convert_encoding' ) ) {
				$content = mb_convert_encoding( $matches[1], 'UTF-8', 'HTML-ENTITIES' );
			} else {
				$content = htmlspecialchars_decode( html_entity_decode( html_entity_decode( $matches[1], ENT_QUOTES | ENT_XML1, 'UTF-8' ) ) );
			}
			return $content;
		}

		/**
		 * Add customizer controls.
		 * @param \WP_Customize_Manager $wp_customize
		 */
		function customize_register($wp_customize) {

			foreach (array(
		'themify-control',
		'fonts-control',
		'image-select-control',
		'text-decoration-control',
		'background-control',
		'border-control',
		'margin-control',
		'padding-control',
		'color-control',
		'color-transparent-control',
		'width-control',
		'height-control',
		'position-control',
		'customcss-control',
		'image-control',
		'logo-control',
		'tagline-control',
		'tools-control',
		'sub-accordion',) as $control) {
				require_once THEMIFY_CUSTOMIZER_DIR . "/class-$control.php";
			}

			/**
			 * Fires before the main Themify Options section has been added.
			 * 
			 * @param object $wp_customize
			 */
			do_action('themify_customizer_before_add_section', $wp_customize);

			$wp_customize->add_section('themify_options', array(
				'title' => __('Themify Options', 'themify'),
				'description' => sprintf('
				<span class="themify-customizer-switcher">
					<a %s class="themify-customizer-switch basic %s"><strong>%s</strong>%s</a><a %s class="themify-customizer-switch advanced %s"><strong>%s</strong>%s</a>
				</span>', $this->is_advanced_mode() ? 'href="' . esc_url(admin_url('customize.php?themify_customizer=basic&themify=1')) . '"' : '', $this->is_advanced_mode() ? 'switchto' : 'selected', __('Basic', 'themify'), __('Less Options', 'themify'), $this->is_advanced_mode() ? '' : 'href="' . esc_url(admin_url('customize.php?themify_customizer=advanced&themify=1')) . '"', $this->is_advanced_mode() ? 'selected' : 'switchto', __('Advanced', 'themify'), __('More Options', 'themify')
				),
			));

			/**
			 * Fires after the main Themify Options section has been added.
			 * 
			 * @param object $wp_customize
			 */
			do_action('themify_customizer_after_add_section', $wp_customize);

			$priority = 10;

			foreach ($this->settings as $setting_id => $field) {

				$setting = isset($field['setting']) ? $field['setting'] : array('default' => '');
				$wp_customize->add_setting(
						$setting_id, // serialized solo cuando type es 'option'
						array(
					'default' => isset($setting['default']) ? $setting['default'] : '',
					'type' => isset($setting['type']) ? $setting['type'] : 'theme_mod',
					'capability' => isset($setting['capability']) ? $setting['capability'] : 'edit_theme_options',
					'transport' => isset($setting['transport']) ? $setting['transport'] : 'postMessage',
					'sanitize_callback' => isset($setting['sanitize']) ? $setting['sanitize'] : false,
						)
				);

				if (isset($field['control'])) {
					if ('Themify_Sub_Accordion_Start' === $field['control']['type']) {
						$this->set_accordion_id();
					}

					$control = $field['control'];
					$class = $control['type'];
					if (class_exists($class)) {
						$wp_customize->add_control(new $class
								(
								$wp_customize, $setting_id . '_ctrl', array(
							'label' => isset($control['label']) ? $control['label'] : '',
							'show_label' => isset($control['show_label']) ? $control['show_label'] : true,
							'color_label' => isset($control['color_label']) ? $control['color_label'] : __('Color', 'themify'),
							'image_options' => isset($control['image_options']) ? $control['image_options'] : array(),
							'font_options' => isset($control['font_options']) ? $control['font_options'] : array(),
							'section' => isset($control['section']) ? $control['section'] : 'themify_options',
							'settings' => isset($control['settings']) ? $control['settings'] : $setting_id,
							'priority' => $priority,
							'accordion_id' => $this->get_accordion_id(),
							'active_callback' => isset($control['active_callback']) ? $control['active_callback'] : null,
                                'hr' => isset($control['hr'])
								)
						));
					} elseif ('nav_menu' === $class) {
						$this->add_nav_menu_control($wp_customize, $control['location'], array(
							'priority' => $priority,
							'accordion_id' => $this->get_accordion_id(),
						));
					} else {
						$options = array(
							'label' => isset($control['label']) ? $control['label'] : '',
							'show_label' => isset($control['show_label']) ? $control['show_label'] : true,
							'color_label' => isset($control['color_label']) ? $control['color_label'] : __('Color', 'themify'),
							'image_options' => isset($control['image_options']) ? $control['image_options'] : array(),
							'font_options' => isset($control['font_options']) ? $control['font_options'] : array(),
							'section' => isset($control['section']) ? $control['section'] : 'themify_options',
							'settings' => isset($control['settings']) ? $control['settings'] : $setting_id,
							'priority' => $priority,
							'type' => $class,
							'accordion_id' => $this->get_accordion_id(),
                            'hr' => isset($control['hr'])
						);
						if ('select' === $class) {
							$options['choices'] = $control['choices'];
						}
						$wp_customize->add_control($setting_id . '_ctrl', $options);
					}
				} elseif (isset($field['builtin'])) {
					
				}
				++$priority;
			}

			// Remove Nav Menus Section
			$wp_customize->remove_section('nav');

			// Remove title and tagline section
			$wp_customize->remove_setting('blogname');
			$wp_customize->remove_setting('blogdescription');
			$wp_customize->remove_control('blogname');
			$wp_customize->remove_control('blogdescription');

			// Remove control for Posts Page in Static Front Page section
			$wp_customize->remove_control('page_for_posts');
		}

		/**
		 * Sets the current accordion being rendered. Used to identify controls that are nested inside it.
		 *
		 * @return number
		 */
		function set_accordion_id() {
			$this->current_accordion_slug++;
		}

		/**
		 * Returns the current accordion being rendered. Set initially when accordion_start() is called.
		 *
		 * @return number
		 */
		function get_accordion_id() {
			return $this->current_accordion_slug;
		}

		/**
		 * Add the control to render a navigation menu selector
		 *
		 * @param object $wp_customize Customizer instance.
		 * @param string $menu_location Location to add menu to.
		 * @param array $args Extra arguments to setup the control.
		 */
		function add_nav_menu_control($wp_customize, $menu_location = '', $args = array()) {
			$locations = get_registered_nav_menus();
			$menus = wp_get_nav_menus();

			if ($menus) {
				$choices = array(0 => __('&mdash; Select &mdash;', 'themify'));
				foreach ($menus as $menu) {
					$choices[$menu->term_id] = wp_html_excerpt($menu->name, 40, '&hellip;');
				}

				foreach ($locations as $location => $description) {
					if ($location == $menu_location) {
						$menu_setting_id = "nav_menu_locations[{$location}]";

						$wp_customize->add_setting($menu_setting_id, array(
							'sanitize_callback' => 'absint',
							'theme_supports' => 'menus',
						));

						$wp_customize->add_control($menu_setting_id, array(
							'label' => $description,
							'section' => 'themify_options',
							'type' => 'select',
							'choices' => $choices,
							'priority' => $args['priority'],
						));
						break;
					}
				}
			}
		}

		/**
		 * Return the URL or the directory path for the global styling stylesheet.
		 * 
		 * @since 2.2.5
		 *
		 * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
		 *
		 * @return string
		 */
		function get_stylesheet($mode = 'bydir') {
			$upload_dir = themify_upload_dir();
			$before=$mode==='bydir'?$upload_dir['basedir']:$upload_dir['baseurl'];
			$stylesheet = "$before/themify-customizer-".THEMIFY_VERSION.'-'.Themify_Enqueue_Assets::$themeVersion.'.css';
			/**
			 * Filters the return URL or directory path including the file name.
			 *
			 * @since 2.2.5
			 *
			 * @param string $stylesheet Path or URL for the global styling stylesheet.
			 * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
			 */
			return apply_filters('themify_customizer_stylesheet', $stylesheet, $mode);
		}

		/**
		 * Write stylesheet file.
		 * 
		 * @since 2.2.5
		 * 
		 * @return bool
		 */
		function write_stylesheet($delete_empty = true) {
			$this->saving_stylesheet = true;

			$this->build_settings_and_styles();
			$css_file = $this->get_stylesheet();

			$css_to_save = $this->generate_css();
			$css_to_save .= $this->generate_responsive_css();
			$css_to_save .= $this->global_css;
			$css_to_save .= $this->generate_custom_css();
			$css_to_save = apply_filters( 'themify_customizer_css_output', $css_to_save, $this );

			if (!empty($css_to_save)) {
				Themify_Filesystem::delete($css_file,'f');
				Themify_Filesystem::put_contents($css_file, $css_to_save);
				update_option('themify_custom_fonts', !empty($this->customizer_fonts) ? $this->customizer_fonts : array() );
                $cf=array();
                foreach($this->customizer_cf_fonts as $k=>$v){
                    $cf[]=$k.':'.$v;
                }
                update_option('themify_custom_cf_fonts', $cf );
			} elseif ($delete_empty) {
				Themify_Filesystem::delete($css_file,'f');
			}

			$this->saving_stylesheet = false;
		}

		/**
		 * Checks if the customize stylesheet exists and enqueues it. Otherwise hooks an action to wp_head to build the CSS and output it.
		 * 
		 * @since 2.2.5
		 */
		function delete_stylesheet() {
			$css_file = $this->get_stylesheet();
			Themify_Filesystem::delete($css_file,'f');
		}

		/**
		 * Checks whether a file exists, can be loaded and is not empty.
		 * 
		 * @since 2.2.5
		 * 
		 * @param string $file_path Path in server to the file to check.
		 * 
		 * @return bool
		 */
		function is_readable_and_not_empty($file_path = '') {
			if (empty($file_path)) {
				return false;
			}
			return is_readable($file_path) && 0 !== filesize($file_path);
		}

		/**
		 * Tries to enqueue stylesheet. If it's not possible, it hooks an action to wp_head to build the CSS and output it.
		 * 
		 * @since 2.2.5
		 */
		function enqueue_stylesheet() {
			
			if($this->test_stylesheet()){
				add_action('tf_load_styles',array($this,'load_css'));
			    add_filter('themify_google_fonts', array($this, 'enqueue_fonts'));
			    add_filter('themify_custom_fonts', array($this, 'enqueue_custom_fonts'));
			}
		}
		
		function load_css(){
			global $wp_customize;
			$url = themify_https_esc( $this->get_stylesheet( 'byurl' ) );
			$v = filemtime( $this->get_stylesheet() );

			if ( ! apply_filters( 'themify_customizer_enqueue_stylesheet', true )  || ( ( $wp_customize instanceof WP_Customize_Manager ) && $wp_customize->is_preview() ) ) {
				// in Customizer preview window, add the generated CSS as inline <style> tag
				add_action( 'wp_footer', array( $this, 'output_css' ), 1000 );
			} else {
				themify_enque_style( 'themify-customize', $url, null, $v );
			}
		}

		/**
		 * Hooked to "wp_footer"[1000]
		 *
		 * Must load after Themify_Enqueue_Assets::wp_footer() to ensure customizer styles overrides all others.
		 *
		 * @return void
		 */
		function output_css() {
			echo $this->get_css();
		}

		/**
		 * Checks if the customize stylesheet exists and enqueues it.
		 * 
		 * @since 2.2.5
		 * 
		 * @return bool True if enqueue was successful, false otherwise.
		 */
		function test_stylesheet() {
			if ($this->is_readable_and_not_empty($this->get_stylesheet())) {
				return true;
			}

			// so try to generate stylesheet...
			$this->write_stylesheet(false);

			// retest
			if ($this->is_readable_and_not_empty($this->get_stylesheet())) {
				return true;
			}

			return false;
		}


		/**
		 * Builds the CSS and outputs it.
		 * 
		 * @since 2.2.5
		 */
		function get_css() {
			$output = '';
			$this->build_settings_and_styles();

			$css = $this->generate_css();
			$css .= $this->generate_responsive_css();
			$css .= $this->global_css;
			$css = apply_filters( 'themify_customizer_css_output', $css, $this );

			$custom_css = $this->custom_css();
			if (!empty($css)) {
				$output= "<!--Themify Customize Styling-->\n<style id=\"themify-customize\" type=\"text/css\">\n$css\n</style>\n<!--/Themify Customize Styling-->";
			}
			if (!empty($custom_css)) {
				$output .= "<!--Themify Custom CSS-->\n<style id=\"themify-customize-customcss\" type=\"text/css\">\n$custom_css\n</style>\n<!--/Themify Custom CSS-->";
			}

			return $output;
		}

		/**
		 * Enqueues Google Fonts
		 * 
		 * @since 2.2.6
		 * @since 2.2.7 Fonts are enqueued in a single call.
		 */
		function enqueue_fonts($fonts) {
			$custom_fonts = !empty($this->customizer_fonts) ?$this->customizer_fonts : ( get_option('themify_custom_fonts') ? get_option('themify_custom_fonts') : array());

			if (!empty($custom_fonts)) {
				foreach ($custom_fonts as $font) {
					$fonts[] = urlencode( $font );
				}
			}

			return $fonts;
		}

		/**
		 * Enqueues Custom Fonts
		 *
		 * @since 4.5.6
		 */
		function enqueue_custom_fonts($fonts) {
			$custom_fonts = !empty($this->customizer_cf_fonts) ? $this->customizer_cf_fonts : ( get_option('themify_custom_cf_fonts') ? get_option('themify_custom_cf_fonts') : array());

			if (!empty($custom_fonts)) {
				foreach ($custom_fonts as $font) {
					$fonts[] = $font;
				}
			}

			return $fonts;
		}

		function dd($debug) {
			echo '<pre>';
			print_r($debug);
			echo '</pre>';
		}

		/**
		 * Export Customizer Settings to zip file and prompt to download
		 * @since 3.0.2
		 */
		function customizer_export() {
			if (isset($_GET['export']) && 'themify-customizer' === $_GET['export']) {
				check_admin_referer('themify_customizer_export_nonce');
				$theme = wp_get_theme();
				$mods = get_theme_mods();
				$mods['theme'] = strtolower($theme->display('Name'));
				$mods['timestamp'] = dechex(time());

				if (!function_exists('WP_Filesystem')) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
				global $wp_filesystem;

				if (class_exists('ZipArchive')) {
					$datafile = 'customizer_export.txt';
                    Themify_Filesystem::put_contents($datafile, serialize($mods));
					$files_to_zip = array($datafile);
					$file = $mods['theme'] . '_themify_customizer_export_' . date('Y_m_d') . '.zip';
					$result = themify_create_zip($files_to_zip, $file, true);
				}
				if (isset($result) && $result) {
					if (( isset($file) ) && ( $wp_filesystem->exists($file) )) {
						ob_start();
						header('Pragma: public');
						header('Expires: 0');
						header("Content-type: application/force-download");
						header('Content-Disposition: attachment; filename="' . $file . '"');
						header("Content-Transfer-Encoding: Binary");
						header("Content-length: " . filesize($file));
						header('Connection: close');
						ob_clean();
						flush();
						echo $wp_filesystem->get_contents($file);
						$wp_filesystem->delete($datafile);
						$wp_filesystem->delete($file);
						exit();
					} else {
						return false;
					}
				} else {
					if (ini_get('zlib.output_compression')) {
						ini_set('zlib.output_compression', 'Off');
					}
					ob_start();
					header('Content-Type: application/force-download');
					header('Pragma: public');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Cache-Control: private', false);
					header('Content-Disposition: attachment; filename="' . $mods['theme'] . '_themify_customizer_export_' . date("Y_m_d") . '.txt"');
					header('Content-Transfer-Encoding: binary');
					ob_clean();
					flush();
					echo serialize($mods);
					exit();
				}
			}
			return false;
		}

		/**
		 * AJAX - Plupload execution routines for customizer import file
		 * @since 3.0.2
		 * @package themify
		 */
		function customizer_import() {
			$imgid = $_POST['imgid'];

			! empty( $_POST[ '_ajax_nonce' ] ) && check_ajax_referer($imgid . 'themify-plupload');

			/** Handle file upload storing file|url|type. @var Array */
			$file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_plupload_customizer'));

			// if $file returns error, return it and exit the function
			if (isset($file['error']) && !empty($file['error'])) {
				echo json_encode($file);
				exit;
			}

			//let's see if it's an image, a zip file or something else
			$ext = explode('/', $file['type']);

			// Import routines
			if ('zip' === $ext[1] || 'rar' === $ext[1] || 'plain' === $ext[1]) {

				$url = wp_nonce_url('customize.php');

				if (false === ($creds = request_filesystem_credentials($url) )) {
					return true;
				}
				if (!WP_Filesystem($creds)) {
					request_filesystem_credentials($url, '', true);
					return true;
				}

				global $wp_filesystem;
				$base_path = themify_upload_dir();
				$base_path = trailingslashit($base_path['path']);

				if ('zip' === $ext[1] || 'rar' === $ext[1]) {
					unzip_file($file['file'], $base_path);
					if ($wp_filesystem->exists($base_path . 'customizer_export.txt')) {
						$data = $wp_filesystem->get_contents($base_path . 'customizer_export.txt');
						$this->set_data(unserialize($data),true);
						$wp_filesystem->delete($base_path . 'customizer_export.txt');
						$wp_filesystem->delete($file['file']);
					} else {
						$file['error'] = __('Data could not be loaded1', 'themify');
					}
				} else {
					if ($wp_filesystem->exists($file['file'])) {
						$data = $wp_filesystem->get_contents($file['file']);
						$this->set_data(unserialize($data),true);
						$wp_filesystem->delete($file['file']);
					} else {
						$file['error'] = __('Data could not be loaded2', 'themify');
					}
				}
			}
			$file['type'] = $ext[1];
			// send the uploaded file url in response
			echo json_encode($file);
			exit;
		}

		private function set_data($data,$is_import=false) {
			$this->build_settings_and_styles();
			$mods = get_theme_mods();
			foreach ($data as $key => $mod) {
				if ($is_import===true || isset($this->settings[$key])) {
					$mods[$key] = $data[$key];
				}
			}
			$theme = get_option('stylesheet');
			$mods = apply_filters("pre_set_{$theme}_mods", $mods);
			update_option("theme_mods_$theme", $mods);
			do_action('customize_save_after');
		}

		/**
		 * Generate CSS rules and output them.
		 * @uses filter 'themify_theme_styling' over output.
		 * 
		 * @param bool $device generate responsive styles
		 *
		 * @return string
		 */
		function generate_css($device = null) {
			global $wp_customize;
			$is_customize = isset($wp_customize);

			// Styles are saved by selector to later output them all at once
			$css = array();

			foreach ($this->styles as $selector => $style) {
				if (!isset($css[$selector])) {
					$css[$selector] = '';
				}

				if ('customcss' === $selector && (!$is_customize || $this->saving_stylesheet ) && is_null($device)) {
					continue;
				}

				if (isset($style[0])) {
					if (is_array($style[0])) {
						foreach (array_map('unserialize', array_unique(array_map('serialize', $style))) as $mstyle) {
							if ('tagline' === $mstyle['prop'] || 'sticky-logo' === $mstyle['prop']) {
								if ($logo_props = $this->build_image_size_rule($mstyle['key'])) {
									$css[$selector . ' img'] = $logo_props;
								}
							}
							if ('logo' === $mstyle['prop']) {
								if ($logo_props = $this->build_image_size_rule($mstyle['key'],$device)) {
									$css[$selector . ' img'] = $logo_props;
								}
							}

							$rule = $this->build_css_rule($selector, $mstyle['prop'], $mstyle['key'], isset($mstyle['prefix']) ? $mstyle['prefix'] : '', isset($mstyle['suffix']) ? $mstyle['suffix'] : '', $device);
							if( ! empty( $rule ) ) {
								if( $mstyle['global'] == true ) {
									/**
									 * Rules that are marked with "global" attribute (customizer-config.php)
									 * are added only in Desktop mode.
									 */
									if ( ! isset( $device ) ) {
										$this->global_css .= $selector . ' { ' . $rule . ' }';
									}
								} else {
									$css[$selector] .= $rule;
								}
							}
						}
					} else {
						if ('logo' === $style['prop'] || 'tagline' === $style['prop'] || 'sticky-logo' === $style['prop']) {
							
							if ('logo' === $style['prop']) {
								$logo_props = $this->build_image_size_rule($style['key'], $device);
							} else {
								$logo_props = $this->build_image_size_rule($style['key']);
							}

							if ($logo_props) {
								$css[$selector . ' img'] = $logo_props;
							}

							if ($logo_props = $this->build_image_size_rule($style['key'], $device)) {
								$css[$selector . ' img'] = $logo_props;
							}
							if ('logo' === $style['prop']) {
								$css[$selector . ' a'] = $this->build_color_rule($style['key']);
							}

							if ('sticky-logo' === $style['prop']) {
								$bg = $this->build_css_rule($selector, 'background', $style['key'], isset($style['prefix']) ? $style['prefix'] : '', isset($style['suffix']) ? $style['suffix'] : '', $device);
								if (!empty($bg)) {
									$css[$selector . ' > *'] = 'display: none;';
									$css[$selector] = 'display: inline-block; background-size: contain; background-repeat: no-repeat;'
											. $this->build_image_size_rule($style['key'])
											. $bg;
								}
							}
						}

						$rule = $this->build_css_rule($selector, $style['prop'], $style['key'], isset($style['prefix']) ? $style['prefix'] : '', isset($style['suffix']) ? $style['suffix'] : '', $device);
						if( ! empty( $rule ) ) {
							if( $style['global'] == true ) {
								if ( ! isset( $device ) ) {
									$this->global_css .= $selector . ' { ' . $rule . ' }';
								}
							} else {
								$css[$selector] .= $rule;
							}
						}
					}
				}
			}
			$out = '';

			if (!empty($css)) {
				foreach ($css as $selector => $properties) {
					if ($selector === 'body' && strpos($properties, 'background-attachment: fixed') != false) {
						preg_match_all("/background.+?;/", $properties, $bg_before);
						$bg_before = ! empty( $bg_before ) ? implode("\n\t", $bg_before[0]) : '';
						
						if( ! empty( $bg_before ) ) {
							$bg_before = str_replace( 'background-attachment: fixed;', '', $bg_before );
							$bg_before .= 'position:fixed;top:0;left:0;width:100%;height:100vh;pointer-events:none;z-index:-1;';
							$out .= "body:before {\n\tcontent: '';\n\t$bg_before \n}\n";
						}
					}
					$out .= '' != $properties ? "$selector {\t$properties \n}\n" : '';
				}
				if (!empty($out)) {
					$out = "/* Themify Customize Styling */\n" . apply_filters('themify_customizer_styling', $out);
				}
			}

			return $out;
		}

		function generate_custom_css() {
			$custom_css = $this->custom_css();

			return ! empty( $custom_css ) ? "\n/* Themify Custom CSS */\n" . apply_filters( 'themify_customizer_custom_css', $custom_css ) : '';
		}

		/**
		 * Generate responsive css.
		 * 
		 * @access public
		 * @return type
		 */
		public function generate_responsive_css() {
			$breakpoints = $this->get_breakpoints();

			$out = '';
			foreach ($breakpoints as $device => $width) {
				$css = $this->generate_css($device);
				if (!empty($css)) {
					$out .= sprintf('@media(max-width: %spx) { %s }', $width, $css);
				}
			}
			return $out;
		}

		/**
		 * Get breakpoints settings.
		 * 
		 * @access public
		 * @return array
		 */
		public function get_breakpoints() {
			$breakpoints = themify_get_breakpoints();

			// Check if there any custom breakpoint value
			foreach ($breakpoints as $bp => $value) {
			    $v=themify_get('setting-customizer_responsive_design_' . $bp,'',true);
				if ('' != $v) {
				    $breakpoints[$bp] = $v;
				}
			}

			return $breakpoints;
		}

		/**
		 * Outputs image width and height for the logo/description image if they are available.
		 *
		 * @param string $mod_name
		 * @return string
		 */
		function build_image_size_rule($mod_name, $device=null) {
			$element = json_decode($this->get_cached_mod($mod_name),true);
			$element_props = '';
			if ($device && isset($element[$device])) {
				$element = $element[$device];
			}
			// if (isset($element->mode) && $element->mode == 'image') {
				if (!empty($element['imgwidth'])) {
					$element_props = "\twidth: {$element['imgwidth']}px;";
				}
				if (!empty($element['imgheight'])) {
					$element_props .= "\n\theight: {$element['imgheight']}px;";
				}
			// }
			return $element_props;
		}

		/**
		 * Outputs color for the logo in text mode since it's needed for the <a>.
		 *
		 * @param string $mod_name
		 * @return string
		 */
		function build_color_rule($mod_name) {
			$element = json_decode($this->get_cached_mod($mod_name));
			$element_props = '';
			if (isset($element->mode) && $element->mode === 'image' && !empty($element->imgwidth)) {
				$element_props = "\twidth: {$element->imgwidth}px;";
			}
			if (isset($element->color) && '' != $element->color) {
				if(0===strpos($element->color,'--')){
					$element_props .= "\n\tcolor: var($element->color);";
				}else{
					$opacity=( isset( $element->opacity ) && ''!=$element->opacity ) ? $element->opacity : 1;
					$element_props.=$opacity<1 ? "\n\tcolor: rgba(".$this->hex2rgb( $element->color ).','.$opacity.');' : "\n\tcolor: #$element->color;";
				}
			}
			return $element_props;
		}

		/**
		 * Checks if there's a Custom CSS text stored, formats it and returns it to be output.
		 *
		 * @return string
		 */
		function custom_css() {
			$mod = $this->get_cached_mod('customcss');
			$customcss = json_decode($mod, true);
			return is_array($customcss) && isset($customcss['css']) && '' != $customcss['css'] ? str_replace(array('{', '}'), array("{\n\t", "\n}\n"), trim($customcss['css'])) : $mod;
		}

		/**
		 * Return theme mod using cached static var if possible.
		 *
		 * @param      $name
		 * @param bool $default
		 *
		 * @return mixed|void
		 */
		function get_cached_mod($name, $default = false, $device = null) {
			static $mods;

			if (!is_null($device)) {
				$mod = $this->get_cached_mod($name, $default);
				$to_array = json_decode($mod, true);
				$return = array();
				if (is_array($to_array) && isset($to_array[$device])) {
					$return = $to_array[$device];
				}
				return json_encode($return);
			}

			if (!isset($mods)) {
				$mods = get_theme_mods();
			}

			if (isset($mods[$name])) {
				/**
				 * Filter the theme modification, or 'theme_mod', value.
				 *
				 * The dynamic portion of the hook name, $name, refers to
				 * the key name of the modification array. For example,
				 * 'header_textcolor', 'header_image', and so on depending
				 * on the theme options.
				 *
				 * @since 2.2.0
				 *
				 * @param string $current_mod The value of the current theme modification.
				 */
				return apply_filters("theme_mod_{$name}", $mods[$name]);
			}

			if (is_string($default))
				$default = sprintf($default, get_template_directory_uri(), get_stylesheet_directory_uri());

			/** This filter is documented in wp-includes/theme.php */
			return apply_filters("theme_mod_{$name}", $default);
		}

		/**
		 * Build a CSS rule.
		 *
		 * @param string $selector CSS selector.
		 * @param string $style CSS property to write.
		 * @param string $mod_name The 'theme_mod' option to fetch.
		 * @param string $prefix Prefix for CSS property value.
		 * @param string $suffix Suffix for CSS property value.
		 * @return string CSS rule: selector, property and property value. Empty if 'theme_mod' option specified is empty.
		 */
		function build_css_rule($selector, $style, $mod_name, $prefix = '', $suffix = '', $device = null) {
			$mod = $this->get_cached_mod($mod_name, false, $device);
			$out = '';
			if (!empty($mod)) {
				if ('font' === $style || 'logo' === $style || 'tagline' === $style || 'decoration' === $style) {
					// Font Rule
					$font = json_decode($mod);
					if (isset($font->family->name) && '' != $font->family->name) {

						if (isset($font->family->fonttype) && ( 'google' === $font->family->fonttype || 'cf' === $font->family->fonttype ) ) {
							$font_family =$font->family->name;
                            if('google' === $font->family->fonttype) {
                                if(!empty($font->weight) && $font->weight!=='normal') {
                                    $font_family.=':normal,' . $font->weight;
                                    if(!empty($font->italic)) {
                                        $font_family.=',' . $font->italic;
                                    }
                                } elseif(!empty($font->italic)) {
                                    $font_family.=':normal,' . $font->italic;
                                }
                                $this->customizer_fonts[] = $font_family;
                            }else{
                                $this->customizer_cf_fonts[] = $font_family;
                                if(!isset($this->customizer_cf_fonts[$font_family])){
                                    $this->customizer_cf_fonts[$font_family]=$font->weight;
                                }else{
                                    $cf=explode(',',$this->customizer_cf_fonts[$font_family]);
                                    if(!in_array($font->weight,$cf)){
                                        $this->customizer_cf_fonts[$font_family] .= ','.$font->weight;
                                    }
                                }
							}
						}
						$fonName=explode(',',$font->family->name);
						$res='';
						$count = count($fonName);
						
						for($i=0;$i<$count;++$i){
						    $f = trim($fonName[$i]);
						    if($f!=='serif' && $f!=='sans-serif' && $f!=='monospace' && $f!=='fantasy' && $f!=='cursive' && $f[0]!=='"' && $f[0]!=="'"){
							$res.='"'.$f.'"';
						    }
						    else{
							$res.=$f;
						    }
						    if($i!==($count-1)){
							$res.=', ';
						    }
						}
						$out.= sprintf("\n\tfont-family: %s;", $prefix . $res . $suffix);
					}
					if (!isset($font->nostyle) || '' == $font->nostyle) {
						if (!empty($font->italic)) {
							$out .= sprintf("\tfont-style:%s;\n", $prefix . $font->italic . $suffix);
						}
						if (!empty($font->bold)) {
							$out .= sprintf("\tfont-weight:%s;\n", $prefix . $font->bold . $suffix);
						}
						if (!empty($font->underline)) {
							$out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->underline . $suffix);
						} elseif (!empty($font->linethrough)) {
							$font->linethrough = 'linethrough' == $font->linethrough ? 'line-through' : $font->linethrough;
							$out .= sprintf("\ttext-decoration:%s;\n", $prefix . $font->linethrough . $suffix);
						}
					} else {
						$out .= sprintf("\tfont-style:%s;\n", $prefix . 'normal' . $suffix);
						$out .= sprintf("\tfont-weight:%s;\n", $prefix . 'normal' . $suffix);
						$out .= sprintf("\ttext-decoration:%s;\n", $prefix . 'none' . $suffix);
					}
					if (!empty($font->weight)) {
						$out .= sprintf("\tfont-weight:%s;\n", $prefix . $font->weight . $suffix);
					}
					if ( isset( $font->letterspacing ) && ''!==$font->letterspacing ) {
						$letterspacing_unit = ! empty( $font->letterspacingunit ) ? $font->letterspacingunit : 'px';
						$out .= sprintf("\tletter-spacing:%s;\n", $prefix . $font->letterspacing . $letterspacing_unit . $suffix);
					}
					if (empty($font->normal)) {
						if (isset($font->italic) && '' != $font->italic) {
							$out .= sprintf("\tfont-style:%s;\n", $prefix . $font->italic . $suffix);
						} elseif (isset($font->bold) && '' != $font->bold) {
							$out .= sprintf("\tfont-weight:%s;\n", $prefix . $font->bold . $suffix);
						}
					} else {
						$out .= sprintf("\tfont-style:%s;\n", $prefix . $font->normal . $suffix);
					}

					if (isset($font->sizenum) && ''!==$font->sizenum) {
						$unit = isset($font->sizeunit) && '' != $font->sizeunit ? $font->sizeunit : 'px';
						$out .= sprintf("\tfont-size:%s;\n", $prefix . $font->sizenum . $unit . $suffix);
					}
					if (isset($font->linenum) && ''!==$font->linenum) {
						$unit = isset($font->lineunit) && '' != $font->lineunit ? $font->lineunit : 'px';
						$out .= sprintf("\tline-height:%s;\n", $prefix . $font->linenum . $unit . $suffix);
					}
					if (!empty($font->texttransform)) {
						if ('notexttransform' === $font->texttransform) {
							$out .= sprintf("\ttext-transform:%s;", $prefix . 'none' . $suffix);
						} else {
							$out .= sprintf("\ttext-transform:%s;", $prefix . $font->texttransform . $suffix);
						}
					}
					if (!empty($font->align)) {
						if ('noalign' !== $font->align) {
							$out .= sprintf("\ttext-align:%s;", $prefix . $font->align . $suffix);
						} else {
							if ('' == is_rtl()) {
								$out .= sprintf("\ttext-align:%s;", $prefix . 'left' . $suffix);
							} else {
								$out .= sprintf("\ttext-align:%s;", $prefix . 'right' . $suffix);
							}
						}
					}
					if (!empty($font->color)) {
						$opacity =  !empty($font->opacity)? $font->opacity : 1;
						$out .= $opacity < 1 ? "\n\tcolor: rgba(" . $this->hex2rgb($font->color) . ',' . $opacity . ');' : "\n\tcolor: #$font->color;";
					}
				}

				if ('logo' === $style || 'tagline' === $style) {
					// Logo/description Rule
					$element = json_decode($mod);
					if (isset($element->mode)) {
						if ('none' === $element->mode) {
							$out .= 'display: none;';
						}
					}
                } elseif ('color' === $style || 0===strpos($style,'--')) {
					// Color Rule
					$color = json_decode($mod);
					if (!empty($color->color) ) {
						if(0===strpos($color->color,'--')){
							$out .= "\n\t$style: var($color->color);";
						}else{
							$opacity = ( isset($color->opacity) && '' != $color->opacity ) ? $color->opacity : 1;
							$out .= $opacity < 1 ? "\n\t$style: rgba(" . $this->hex2rgb($color->color) . ',' . $opacity . ');' : "\n\t$style: #$color->color;";
						}
					}
				} elseif ('border-color' === $style) {
					// Border Color Rule
					$color = json_decode($mod);
					if (!empty($color->color)) {
						if(0===strpos($color->color,'--')){
							$out .= "\n\tborder-color: var($color->color);";
						}else{
							$opacity=( isset( $color->opacity ) && ''!=$color->opacity ) ? $color->opacity : 1;
							$out.=$opacity<1 ? "\n\tborder-color: rgba(".$this->hex2rgb( $color->color ).','.$opacity.');' : "\n\tborder-color: #$color->color;";
						}
					}
				} elseif ('background' === $style) {
					// Background Rule
					$bg = json_decode($mod);

					if (!empty($bg->transparent)) {
						$out .= "\n\tbackground-color: $bg->transparent;";
					} elseif (!empty($bg->color)) {
						$opacity = !empty($bg->opacity)? $bg->opacity : 1;
						$out .= 'background: none;';
						if(0===strpos($bg->color,'--')){
							$out .= "\n\tbackground-color: var($bg->color);";
						}else{
							$out.=$opacity<1 ? "\n\tbackground-color: rgba(".$this->hex2rgb( $bg->color ).','.$opacity.');' : "\n\tbackground-color: #$bg->color;";
						}
					}
					if (isset($bg->noimage) && 'noimage' === $bg->noimage) {
						$out .= 'background-image: none;';
					} elseif (!empty($bg->src)) {
					    themify_generateWebp($prefix . $bg->src . $suffix);
					    $out .= sprintf("background-image: url(%s);", $prefix . $bg->src . $suffix);
					}
					if (!empty($bg->style)) {
						$out .= 'fullcover' === $bg->style ? "\n\tbackground-size: cover;" : "\n\tbackground-repeat: {$bg->style};";
					}
					if (!empty($bg->position)) {
						$out .= "\n\tbackground-position: {$bg->position};";
					}
					if( isset($bg->fixedbg) && 'fixed' === $bg->fixedbg ) {
						$out .= 'background-attachment: fixed;';
					}
				} elseif ('border' === $style) {
					// Border Rule
					$border = json_decode($mod);
					if (isset($border->disabled) && 'disabled' === $border->disabled) {
						$out .= 'border: none;';
					} else {
						$same = ( isset($border->same) && '' != $border->same ) ? 'same' : '';
						if ('' == $same) {
							foreach (array('top', 'right', 'bottom', 'left') as $side) {
								if (isset($border->{$side})) {
									$border_side = $border->{$side};
									$out .= $this->setBorder($border_side, 'border-' . $side);
								}
							}
						} else {
							$out .= $this->setBorder($border);
						}
					}
				} elseif ('margin' === $style || 'padding' === $style) {
					// Margin/Padding Rule
					$marginpadding = json_decode($mod);

					$same = !empty($marginpadding->same)? 'same' : '';
					if ('' == $same) {
						foreach (array('top', 'right', 'bottom', 'left') as $side) {
							if (isset($marginpadding->{$side})) {
								if ('margin' === $style && isset($marginpadding->{$side}->auto) && 'auto' === $marginpadding->{$side}->auto) {
									$out .= $style . '-' . $side . ': auto;';
								} else {
									$this_side = $marginpadding->{$side};
									$out .= $this->setDimension($this_side, $style . '-' . $side);
								}
							}
						}
					} else {
						$out .= 'margin' === $style && isset($marginpadding->auto) && 'auto' === $marginpadding->auto ? $style . ': auto;' : $this->setDimension($marginpadding, $style);
					}
				} elseif ('width' === $style || 'height' === $style) {
					// Width/Height Rule
					$widthheight = json_decode($mod);
					$out .= isset($widthheight->auto) && 'auto' === $widthheight->auto ? $style . ': auto;' : $this->setDimension($widthheight, $style);
				} elseif ('position' === $style) {
					// Position Rule
					$position = json_decode($mod);

					if (!empty($position->position)) {
						$out .= sprintf("\tposition:%s;\n", $prefix . $position->position . $suffix);
					}

					foreach (array('top', 'right', 'bottom', 'left') as $side) {
						if (isset($position->{$side})) {
							if (isset($position->{$side}->auto) && 'auto' === $position->{$side}->auto) {
								$out .= $side . ': auto;';
							} else {
								$this_side = $position->{$side};
								$out .= $this->setDimension($this_side, $side);
							}
						}
					}
				}
			}
			// Build rule to return

			return $out;
		}

		/**
		 * Generate border properties.
		 *
		 * @uses hex2rgb()
		 *
		 * @param object $border Object with all the necessary values.
		 * @param string $property Property to set, can be border or border-left for example
		 * @return string
		 */
		function setBorder($border, $property = 'border') {
			$out = '';
			if (isset($border->style) && 'none' !== $border->style && isset($border->width) && $border->width > 0 && '' != $border->style) {
				$out = "\n\t$property: {$border->width}px $border->style";
				if (isset($border->color) && '' != $border->color) {
					$opacity = ( isset($border->opacity) && '' != $border->opacity ) ? $border->opacity : 1;
					$out .= $opacity < 1 ? " rgba(" . $this->hex2rgb($border->color) . ',' . $opacity . ');' : " #$border->color";
				}
				$out.=';';
			} else if( isset( $border->style ) && 'none' === $border->style ) {
				$out .= "\n\t$property: none !important;";
			}
			return $out;
		}

		/**
		 * Generate dimension properties for cases like padding or margin.
		 *
		 * @param object $object Object with all the necessary values.
		 * @param string $property Property to set, can be margin or padding-left for example
		 * @return string
		 */
		function setDimension($object, $property = 'margin') {
			$out = '';
			$unit = isset($object->unit) && 'px' !== $object->unit ? $object->unit : 'px';
			if (isset($object->width) && '' != $object->width) {
				$out = "\n\t$property: {$object->width}$unit;";
			}
			return $out;
		}

		/**
		 * Converts color in hexadecimal format to RGB format.
		 *
		 * @param string $hex Color in hexadecimal format.
		 * @return string Color in RGB components separated by comma.
		 */
		function hex2rgb($hex) {
		    $hex = str_replace('#', '', $hex);
		    if (strlen($hex) === 3) {
			$r = substr($hex, 0, 1);
			$g = substr($hex, 1, 1);
			$b = substr($hex, 2, 1);
			$r = hexdec($r . $r);
			$g = hexdec($g . $g);
			$b = hexdec($b. $b);
		    } else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		    }
		    return implode(',', array($r, $g, $b));
		}

		/**
		 * Inserts logo image in site title or hides it.
		 *
		 * @param string $location
		 * @return string
		 */
		function site_logo($location = '') {
			$site_name = get_bloginfo('name');
			$logo = json_decode($this->get_cached_mod($location . '_image'));
			$is_image_mode = isset($logo->mode) && 'image' === $logo->mode && !empty($logo->src);
			$url = apply_filters('themify_customizer_logo_home_url', !empty($logo->link) ? $logo->link : themify_home_url() );
			$html = '<a href="' . esc_url($url) . '" title="' . esc_attr($site_name) . '">';
			if ( $is_image_mode===true) {
				$attr = array(
					'src'    => esc_url( themify_https_esc( $logo->src ) ),
					'alt'    => ( isset($logo->id) ? get_post_meta( $logo->id, '_wp_attachment_image_alt', true ) : '' ) ?: $site_name,
					'title'  => ( isset($logo->id) ? get_the_title( $logo->id ) : '' ) ?: $site_name,
					'width'  => ! empty( $logo->imgwidth ) ? $logo->imgwidth : '',
					'height' => ! empty( $logo->imgheight ) ? $logo->imgheight : '',
					'class' => 'site-logo-image',
				);
                                unset($logo);
				if(Themify_Enqueue_Assets::$isFooter === false){
					$attr['data-tf-not-load']=1;
					$attr['importance']='high';
				}
				themify_generateWebp( $attr['src'] );
                                /* temp disable need to discuss
                                if($attr['width']==='' || $attr['height']===''){
                                    $size=themify_get_image_size($attr['src']);
                                    if($size!==false){
                                        if($attr['width']===''){
                                            $attr['width']=$size['w'];
                                        }
                                        if($attr['height']===''){
                                            $attr['height']=$size['h'];
                                        }
                                    }
                                    unset($size);
                                }*/
				$html .= '<img ' . themify_get_element_attributes( $attr ) . '>';
				if ( is_customize_preview() ) {
				    $html .= '<span style="display:none">' . esc_html($site_name) . '</span>' ;
				}
				else{
				    Themify_Enqueue_Assets::addPreLoadMedia( $attr['src'] );
				}
				
			} else {
				$html .= '<span>' . html_entity_decode($site_name) . '</span>';
			}
			$html .= '</a>';
			return $html;
		}

		/**
		 * Inserts image in site description or hides it.
		 *
		 * @param string $site_desc Site tagline.
		 * @param string $location
		 * @return string
		 */
		function site_description($site_desc = '', $location = 'site-tagline') {
			$desc = json_decode($this->get_cached_mod($location));
			$is_image_mode = isset($desc->mode) && 'image' === $desc->mode && !empty($desc->src);
			$url = apply_filters('themify_customizer_tagline_url', !empty($desc->link) ? $desc->link : home_url() );
			$html = !empty($desc->link) ? '<a class="site-taglink" href="' . esc_attr($url) .'" >' : '';
			if ( $is_image_mode===true) {
				$alt = get_post_meta( $desc->id, '_wp_attachment_image_alt', true ) ?: $site_desc;
				$title = get_the_title( $desc->id ) ?: $site_desc;
				$html .= '<img src="' . esc_url(themify_https_esc($desc->src)) . '" alt="' . esc_attr( $alt ) . '" title="' . esc_attr( $title ) . '">';
				if(is_customize_preview()){
				    $html .= '<span style="display: none;">' . esc_html($site_desc) . '</span>' ;
				}
				
			} else {
				$html .= '<span>' . html_entity_decode($site_desc) . '</span>';
			}
			$html .= ( !empty($desc->link) ) ? '</a>' : '';
			return $html;
		}

		/**
		 * Add more devices.
		 * 
		 * @param array $devices 
		 * @return array
		 */
		public function add_devices( $devices ) {
			/* add Tablet landscape before the "tablet" option */
			if ( $tablet_position = array_search( 'tablet', array_keys( $devices ) ) ) {
				$devices =  array_slice( $devices, 0, $tablet_position, true ) +
							array( 'tablet_landscape' => array( 'label' => esc_html__( 'Enter tablet landscape preview mode', 'themify' ) ) ) +
							array_slice( $devices, $tablet_position, count( $devices ) - 1, true );
			}

			return $devices;
		}

		/**
		 * Disables some options in Customizer screen in order to lighten the load
		 *
		 * Currently both Widgets and Menus admin screens are disabled if Themify > Customize menu is used.
		 */
		function disable_menus_and_widgets() {
			global $wp_customize;
			if ( isset( $wp_customize ) && isset( $_GET['themify'] ) && $_GET['themify'] === '1' ) {
				/**
				 * Disable widgets manager in Customizer
				 * original hooks located in wp-includes/class-wp-customize-widgets.php
				 */
				remove_filter( 'customize_dynamic_setting_args',          array( $wp_customize->widgets, 'filter_customize_dynamic_setting_args' ), 10, 2 );
				remove_action( 'widgets_init',                            array( $wp_customize->widgets, 'register_settings' ), 95 );
				remove_action( 'customize_register',                      array( $wp_customize->widgets, 'schedule_customize_register' ), 1 );
				remove_action( 'wp_loaded',                               array( $wp_customize->widgets, 'override_sidebars_widgets_for_theme_switch' ) );
				remove_action( 'customize_controls_init',                 array( $wp_customize->widgets, 'customize_controls_init' ) );
				remove_action( 'customize_controls_enqueue_scripts',      array( $wp_customize->widgets, 'enqueue_scripts' ) );
				remove_action( 'customize_controls_print_styles',         array( $wp_customize->widgets, 'print_styles' ) );
				remove_action( 'customize_controls_print_scripts',        array( $wp_customize->widgets, 'print_scripts' ) );
				remove_action( 'customize_controls_print_footer_scripts', array( $wp_customize->widgets, 'print_footer_scripts' ) );
				remove_action( 'customize_controls_print_footer_scripts', array( $wp_customize->widgets, 'output_widget_control_templates' ) );
				remove_action( 'customize_preview_init',                  array( $wp_customize->widgets, 'customize_preview_init' ) );
				remove_filter( 'customize_refresh_nonces',                array( $wp_customize->widgets, 'refresh_nonces' ) );
				remove_action( 'dynamic_sidebar',                         array( $wp_customize->widgets, 'tally_rendered_widgets' ) );
				remove_filter( 'is_active_sidebar',                       array( $wp_customize->widgets, 'tally_sidebars_via_is_active_sidebar_calls' ), 10, 2 );
				remove_filter( 'dynamic_sidebar_has_widgets',             array( $wp_customize->widgets, 'tally_sidebars_via_dynamic_sidebar_calls' ), 10, 2 );
				remove_filter( 'customize_dynamic_partial_args',          array( $wp_customize->widgets, 'customize_dynamic_partial_args' ), 10, 2 );
				remove_action( 'customize_preview_init',                  array( $wp_customize->widgets, 'selective_refresh_init' ) );

				/**
				 * Disable Menus manager in Customizer
				 * original hooks located in wp-includes/class-wp-customize-nav-menus.php
				 */
				remove_action( 'customize_register', array( $wp_customize->nav_menus, 'customize_register' ), 11 );
				remove_filter( 'customize_dynamic_setting_args', array( $wp_customize->nav_menus, 'filter_dynamic_setting_args' ), 10, 2 );
				remove_filter( 'customize_dynamic_setting_class', array( $wp_customize->nav_menus, 'filter_dynamic_setting_class' ), 10, 3 );
				remove_action( 'customize_save_nav_menus_created_posts', array( $wp_customize->nav_menus, 'save_nav_menus_created_posts' ) );
				remove_filter( 'customize_refresh_nonces', array( $wp_customize->nav_menus, 'filter_nonces' ) );
				remove_action( 'wp_ajax_load-available-menu-items-customizer', array( $wp_customize->nav_menus, 'ajax_load_available_items' ) );
				remove_action( 'wp_ajax_search-available-menu-items-customizer', array( $wp_customize->nav_menus, 'ajax_search_available_items' ) );
				remove_action( 'wp_ajax_customize-nav-menus-insert-auto-draft', array( $wp_customize->nav_menus, 'ajax_insert_auto_draft_post' ) );
				remove_action( 'customize_controls_enqueue_scripts', array( $wp_customize->nav_menus, 'enqueue_scripts' ) );
				remove_action( 'customize_controls_print_footer_scripts', array( $wp_customize->nav_menus, 'print_templates' ) );
				remove_action( 'customize_controls_print_footer_scripts', array( $wp_customize->nav_menus, 'available_items_template' ) );
				remove_action( 'customize_preview_init', array( $wp_customize->nav_menus, 'customize_preview_init' ) );
				remove_action( 'customize_preview_init', array( $wp_customize->nav_menus, 'make_auto_draft_status_previewable' ) );
				remove_filter( 'customize_dynamic_partial_args', array( $wp_customize->nav_menus, 'customize_dynamic_partial_args' ), 10, 2 );
			}
		}
	}

endif;
$GLOBALS['themify_customizer'] = new Themify_Customizer;
