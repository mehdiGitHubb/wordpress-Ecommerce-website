<?php

/**
 * Class for interact with DB or data resource and state.
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */
final class Themify_Builder_Model {

	/**
	 * Feature Image Size
	 * @var array
	 */
	public static $featured_image_size = array();

	/**
	 * Image Width
	 * @var array
	 */
	public static $image_width = array();

	/**
	 * Image Height
	 * @var array
	 */
	public static $image_height = array();

	/**
	 * External Link
	 * @var array
	 */
	public static $external_link = array();

	/**
	 * Lightbox Link
	 * @var array
	 */
	public static $lightbox_link = array();
	public static $modules = array();
	const LAYOUT_NAME = 'tbuilder_layouts_version';
	const TRANSIENT_NAME = 'tb_edit_';

	/**
	 * Active custom post types registered by Builder.
	 *
	 * @var array
	 */
	public static $builder_cpt = array();

	/**
	 * Directory Registry
	 */
	private static $modules_registry = array();

	/**
	 * Hook Content cache, used by Post modules
	 */
	private static $hook_contents=array();

	private function __construct() {

	}

	/**
	 * Get favorite option to module instance
	 * @return object
	 */
	public static function get_favorite_modules() {
            $fv = get_user_option('themify_module_favorite', get_current_user_id());
            if(!empty($fv)){
                $fv=json_decode($fv,true);
                if(!array_key_exists(0, $fv)){
                    $fv=array_keys($fv);
                }
            }
            else{
                $fv=array();
            }
            return $fv;
	}

        /**
	 * Check whether builder is active or not
	 * @return bool
	 */
	public static function builder_check() {
		static $is = NULL;
		if ($is === null) {
			$is = apply_filters('themify_enable_builder', themify_builder_get('setting-page_builder_is_active', 'builder_is_active'));
			$is = !( 'disable' === $is );
		}
		return $is;
	}

	/**
	 * Check whether module is active
	 * @param $name
	 * @return boolean
	 */
	public static function check_module_active($name) {
		return isset(self::$modules[$name]);
	}

	/**
	 * Check is frontend editor page
	 */
	public static function is_frontend_editor_page($post_id = null) {
		$post_id = $post_id === null ? self::get_ID() : $post_id;
		return apply_filters('themify_builder_is_frontend_editor', Themify_Access_Role::check_access_frontend($post_id), $post_id);
	}

	/**
	 * Check if builder frontend edit being invoked
	 */
	public static function is_front_builder_activate() {
		static $is = NULL;
		if ($is === null) {
			$is = Themify_Builder::$frontedit_active === true || ((isset($_GET['tb-preview']) || (isset($_COOKIE['tb_active']) && !is_admin() && !themify_is_ajax()) && self::is_frontend_editor_page()));
			if ($is === true) {
				add_filter('lazyload_is_enabled', '__return_false', 1, 100); //disable jetpack lazy load
				add_filter('rocket_use_native_lazyload', '__return_false', 1, 100);
			}
		}
		return $is;
	}

	/**
	 * Load general metabox fields
	 */
	public static function load_general_metabox() {
		// Featured Image Size
		self::$featured_image_size = apply_filters('themify_builder_metabox_featured_image_size', array(
			'name' => 'feature_size',
			'title' => __('Image Size', 'themify'),
			'description' => sprintf(__('Image sizes can be set at <a href="%s">Media Settings</a>', 'themify'), admin_url('options-media.php')),
			'type' => 'featimgdropdown'
		));
		// Image Width
		self::$image_width = apply_filters('themify_builder_metabox_image_width', array(
			'name' => 'image_width',
			'title' => __('Image Width', 'themify'),
			'description' => '',
			'type' => 'textbox',
			'meta' => array('size' => 'small')
		));
		// Image Height
		self::$image_height = apply_filters('themify_builder_metabox_image_height', array(
			'name' => 'image_height',
			'title' => __('Image Height', 'themify'),
			'description' => '',
			'type' => 'textbox',
			'meta' => array('size' => 'small'),
			'class' => self::is_img_php_disabled() ? 'builder_show_if_enabled_img_php' : '',
		));
		// External Link
		self::$external_link = apply_filters('themify_builder_metabox_external_link', array(
			'name' => 'external_link',
			'title' => __('External Link', 'themify'),
			'description' => __('Link Featured Image and Post Title to external URL', 'themify'),
			'type' => 'textbox',
			'meta' => array()
		));
		// Lightbox Link
		self::$lightbox_link = apply_filters('themify_builder_metabox_lightbox_link', array(
			'name' => 'lightbox_link',
			'title' => __('Lightbox Link', 'themify'),
			'description' => __('Link Featured Image to lightbox image, video or external iframe', 'themify'),
			'type' => 'textbox',
			'meta' => array()
		));
	}

	/**
	 * Get module name by slug
	 * @param string $slug
	 * @return string
	 */
	public static function get_module_name($slug) {
		return isset(self::$modules[$slug]) ? self::$modules[$slug]->name : $slug;
	}


	/**
	 * Return frame layout
	 */
	public static function get_frame_layout() {
		$path = THEMIFY_BUILDER_URI . '/img/row-frame/';
		return array(
			array('value' => 'none', 'label' => __('None', 'themify'), 'img' => $path . 'none.png'),
			array('value' => 'slant1', 'label' => __('Slant 1', 'themify'), 'img' => $path . 'slant1.svg'),
			array('value' => 'slant2', 'label' => __('Slant 2', 'themify'), 'img' => $path . 'slant2.svg'),
			array('value' => 'arrow1', 'label' => __('Arrow 1', 'themify'), 'img' => $path . 'arrow1.svg'),
			array('value' => 'arrow2', 'label' => __('Arrow 2', 'themify'), 'img' => $path . 'arrow2.svg'),
			array('value' => 'arrow3', 'label' => __('Arrow 3', 'themify'), 'img' => $path . 'arrow3.svg'),
			array('value' => 'arrow4', 'label' => __('Arrow 4', 'themify'), 'img' => $path . 'arrow4.svg'),
			array('value' => 'arrow5', 'label' => __('Arrow 5', 'themify'), 'img' => $path . 'arrow5.svg'),
			array('value' => 'arrow6', 'label' => __('Arrow 6', 'themify'), 'img' => $path . 'arrow6.svg'),
			array('value' => 'cloud1', 'label' => __('Cloud 1', 'themify'), 'img' => $path . 'cloud1.svg'),
			array('value' => 'cloud2', 'label' => __('Cloud 2', 'themify'), 'img' => $path . 'cloud2.svg'),
			array('value' => 'curve1', 'label' => __('Curve 1', 'themify'), 'img' => $path . 'curve1.svg'),
			array('value' => 'curve2', 'label' => __('Curve 2', 'themify'), 'img' => $path . 'curve2.svg'),
			array('value' => 'mountain1', 'label' => __('Mountain 1', 'themify'), 'img' => $path . 'mountain1.svg'),
			array('value' => 'mountain2', 'label' => __('Mountain 2', 'themify'), 'img' => $path . 'mountain2.svg'),
			array('value' => 'mountain3', 'label' => __('Mountain 3', 'themify'), 'img' => $path . 'mountain3.svg'),
			array('value' => 'wave1', 'label' => __('Wave 1', 'themify'), 'img' => $path . 'wave1.svg'),
			array('value' => 'wave2', 'label' => __('Wave 2', 'themify'), 'img' => $path . 'wave2.svg'),
			array('value' => 'wave3', 'label' => __('Wave 3', 'themify'), 'img' => $path . 'wave3.svg'),
			array('value' => 'wave4', 'label' => __('Wave 4', 'themify'), 'img' => $path . 'wave4.svg'),
			array('value' => 'ink-splash1', 'label' => __('Ink Splash 1', 'themify'), 'img' => $path . 'ink-splash1.svg'),
			array('value' => 'ink-splash2', 'label' => __('Ink Splash 2', 'themify'), 'img' => $path . 'ink-splash2.svg'),
			array('value' => 'zig-zag', 'label' => __('Zig Zag', 'themify'), 'img' => $path . 'zig-zag.svg'),
			array('value' => 'grass', 'label' => __('Grass', 'themify'), 'img' => $path . 'grass.svg'),
			array('value' => 'melting', 'label' => __('Melting', 'themify'), 'img' => $path . 'melting.svg'),
			array('value' => 'lace', 'label' => __('Lace', 'themify'), 'img' => $path . 'lace.svg'),
		);
	}

	/**
	 * Return animation presets
	 */
	public static function get_preset_animation() {
		return array(
			__('Attention Seekers', 'themify') => array(
				'bounce' => __('bounce', 'themify'),
				'flash' => __('flash', 'themify'),
				'pulse' => __('pulse', 'themify'),
				'rubberBand' => __('rubberBand', 'themify'),
				'shake' => __('shake', 'themify'),
				'swing' => __('swing', 'themify'),
				'tada' => __('tada', 'themify'),
				'wobble' => __('wobble', 'themify'),
				'jello' => __('jello', 'themify')
			),
			__('Bouncing Entrances', 'themify') => array(
				'bounceIn' => __('bounceIn', 'themify'),
				'bounceInDown' => __('bounceInDown', 'themify'),
				'bounceInLeft' => __('bounceInLeft', 'themify'),
				'bounceInRight' => __('bounceInRight', 'themify'),
				'bounceInUp' => __('bounceInUp', 'themify')
			),
			__('Bouncing Exits', 'themify') => array(
				'bounceOut' => __('bounceOut', 'themify'),
				'bounceOutDown' => __('bounceOutDown', 'themify'),
				'bounceOutLeft' => __('bounceOutLeft', 'themify'),
				'bounceOutRight' => __('bounceOutRight', 'themify'),
				'bounceOutUp' => __('bounceOutUp', 'themify')
			),
			__('Fading Entrances', 'themify') => array(
				'fadeIn' => __('fadeIn', 'themify'),
				'fadeInDown' => __('fadeInDown', 'themify'),
				'fadeInDownBig' => __('fadeInDownBig', 'themify'),
				'fadeInLeft' => __('fadeInLeft', 'themify'),
				'fadeInLeftBig' => __('fadeInLeftBig', 'themify'),
				'fadeInRight' => __('fadeInRight', 'themify'),
				'fadeInRightBig' => __('fadeInRightBig', 'themify'),
				'fadeInUp' => __('fadeInUp', 'themify'),
				'fadeInUpBig' => __('fadeInUpBig', 'themify')
			),
			__('Fading Exits', 'themify') => array(
				'fadeOut' => __('fadeOut', 'themify'),
				'fadeOutDown' => __('fadeOutDown', 'themify'),
				'fadeOutDownBig' => __('fadeOutDownBig', 'themify'),
				'fadeOutLeft' => __('fadeOutLeft', 'themify'),
				'fadeOutLeftBig' => __('fadeOutLeftBig', 'themify'),
				'fadeOutRight' => __('fadeOutRight', 'themify'),
				'fadeOutRightBig' => __('fadeOutRightBig', 'themify'),
				'fadeOutUp' => __('fadeOutUp', 'themify'),
				'fadeOutUpBig' => __('fadeOutUpBig', 'themify')
			),
			__('Flippers', 'themify') => array(
				'flip' => __('flip', 'themify'),
				'flipInX' => __('flipInX', 'themify'),
				'flipInY' => __('flipInY', 'themify'),
				'flipOutX' => __('flipOutX', 'themify'),
				'flipOutY' => __('flipOutY', 'themify')
			),
			__('Lightspeed', 'themify') => array(
				'lightSpeedIn' => __('lightSpeedIn', 'themify'),
				'lightSpeedOut' => __('lightSpeedOut', 'themify')
			),
			__('Rotating Entrances', 'themify') => array(
				'rotateIn' => __('rotateIn', 'themify'),
				'rotateInDownLeft' => __('rotateInDownLeft', 'themify'),
				'rotateInDownRight' => __('rotateInDownRight', 'themify'),
				'rotateInUpLeft' => __('rotateInUpLeft', 'themify'),
				'rotateInUpRight' => __('rotateInUpRight', 'themify')
			),
			__('Rotating Exits', 'themify') => array(
				'rotateOut' => __('rotateOut', 'themify'),
				'rotateOutDownLeft' => __('rotateOutDownLeft', 'themify'),
				'rotateOutDownRight' => __('rotateOutDownRight', 'themify'),
				'rotateOutUpLeft' => __('rotateOutUpLeft', 'themify'),
				'rotateOutUpRight' => __('rotateOutUpRight', 'themify')
			),
			__('Specials', 'themify') => array(
				'hinge' => __('hinge', 'themify'),
				'rollIn' => __('rollIn', 'themify'),
				'rollOut' => __('rollOut', 'themify')
			),
			__('Zoom Entrances', 'themify') => array(
				'zoomIn' => __('zoomIn', 'themify'),
				'zoomInDown' => __('zoomInDown', 'themify'),
				'zoomInLeft' => __('zoomInLeft', 'themify'),
				'zoomInRight' => __('zoomInRight', 'themify'),
				'zoomInUp' => __('zoomInUp', 'themify')
			),
			__('Zoom Exits', 'themify') => array(
				'zoomOut' => __('zoomOut', 'themify'),
				'zoomOutDown' => __('zoomOutDown', 'themify'),
				'zoomOutLeft' => __('zoomOutLeft', 'themify'),
				'zoomOutRight' => __('zoomOutRight', 'themify'),
				'zoomOutUp' => __('zoomOutUp', 'themify')
			),
			__('Slide Entrance', 'themify') => array(
				'slideInDown' => __('slideInDown', 'themify'),
				'slideInLeft' => __('slideInLeft', 'themify'),
				'slideInRight' => __('slideInRight', 'themify'),
				'slideInUp' => __('slideInUp', 'themify')
			),
			__('Slide Exit', 'themify') => array(
				'slideOutDown' => __('slideOutDown', 'themify'),
				'slideOutLeft' => __('slideOutLeft', 'themify'),
				'slideOutRight' => __('slideOutRight', 'themify'),
				'slideOutUp' => __('slideOutUp', 'themify')
			)
		);
	}

	/**
	 * Get Post Types which ready for an operation
	 * @return array
	 */
	public static function get_post_types() {

		// If it's not a product search, proceed: retrieve the post types.
		$types = get_post_types(array('exclude_from_search' => false));
		if (themify_is_themify_theme()) {
			// Exclude pages /////////////////
			$exclude_pages = themify_builder_get('setting-search_settings_exclude');
			if (!empty($exclude_pages)) {
				unset($types['page']);
			}
			// Exclude posts /////////////////
			$exclude_posts = themify_builder_get('setting-search_exclude_post');
			if (!empty($exclude_posts)) {
				unset($types['post']);
			}
			// Exclude custom post types /////
			$exclude_types = apply_filters('themify_types_excluded_in_search', get_post_types(array(
				'_builtin' => false,
				'public' => true,
				'exclude_from_search' => false
			)));

			foreach (array_keys($exclude_types) as $type) {
				$check = themify_builder_get('setting-search_exclude_' . $type);
				if (!empty($check)) {
					unset($types[$type]);
				}
			}
		}
		// Exclude Layout and Layout Part custom post types /////
		unset($types['section'], $types['tbuilder_layout'], $types['tbuilder_layout_part'], $types['elementor_library']);

		return $types;
	}

	/**
	 * Check whether builder animation is active
	 * @return boolean
	 */
	public static function is_animation_active() {
		static $is = NULL;
		if ($is === null) {
			$val = themify_builder_get('setting-page_builder_animation_appearance', 'builder_animation_appearance');
			$is = $val === 'all' || self::is_front_builder_activate()? false : ('mobile' === $val ? 'm' : true);
		}
		return $is;
	}

	/**
	 * Check whether builder parallax is active
	 * @return boolean
	 */
	public static function is_parallax_active() {
		static $is = NULL;
		if ($is === null) {
		    $val = themify_builder_get('setting-page_builder_animation_parallax_bg', 'builder_animation_parallax_bg');
		    $is = self::is_front_builder_activate() ? true : ($val === 'all'? false : ('mobile' === $val ? 'm' : true));
		}
		return $is;
	}

	/**
	 * Check whether builder scroll effect is active
	 * @return boolean
	 */
	public static function is_scroll_effect_active() {
		static $is = NULL;
		if ($is === null) {
			// check if mobile exclude disabled OR disabled all transition
		    $val = themify_builder_get('setting-page_builder_animation_scroll_effect', 'builder_animation_scroll_effect');
		    $is = $val === 'all' || self::is_front_builder_activate()? false : ('mobile' === $val ? 'm' : true);
		}
		return $is;
	}

	/**
	 * Check whether builder sticky scroll is active
	 * @return boolean
	 */
	public static function is_sticky_scroll_active() {
		static $is = NULL;
		if ($is === null) {
			$val = themify_builder_get('setting-page_builder_animation_sticky_scroll', 'builder_animation_sticky_scroll');
			$is = $val === 'all' || self::is_front_builder_activate()? false : ('mobile' === $val ? 'm' : true);
			$is = apply_filters('tb_sticky_scroll_active', $is);
		}
		return $is;
	}

	/**
	 * Get Grid Settings
	 * @return array
	 */
	public static function get_grid_settings($key = 'all') {
                $globalGutters= self::get_gutters();
                $vals=array(
                    'grid'=>array(
					// Grid FullWidth
                                    array('grid' => 1,'name'=>__('Full Width','themify')),
					// Grid 2
                                     array('grid' => 2,'name'=>__('2 Cols','themify')),
					// Grid 3
                                    array('grid' => 3,'name'=>__('3 Cols','themify')),
					// Grid 4
                                    array('grid' => 4,'name'=>__('4 Cols','themify')),
					// Grid 5
                                    array('grid' => 5,'name'=>__('5 Cols','themify')),
					// Grid 6
                                    array('grid' => 6,'name'=>__('6 Cols','themify')),

                                    array('grid' =>'1_3','name'=>__('2 Cols (25/75)','themify')),
                                    array('grid' =>'1_1_2','name'=>__('3 Cols (25/25/50)','themify')),
                                    array('grid' =>'1_2_1', 'name'=>__('3 Cols (25/50/25)','themify')),
                                    array('grid' =>'2_1_1','name'=>__('3 Cols (50/25/25)','themify')),
                                    array('grid' =>'3_1','name'=>__('2 Cols (75/25)','themify')),
                                    array('grid' =>'1_2','name'=>__('2 Cols (35/65)','themify')),
                                    array('grid' =>'2_1','name'=>__('2 Cols (65/35)','themify')),
                                    array('grid' =>'user','name'=>__('Custom','themify'))
				),
                    'alignment'=>array(
                        array('value' => 'start', 'name' => __('Align Top', 'themify')),
                        array('value' => 'center', 'name' => __('Align Middle', 'themify')),
                        array('value' => 'end', 'name' => __('Align Bottom', 'themify'))
                    ),
                    'height'=>array(
                        array('img' => 'stretch', 'value' => -1, 'name' => __('Stretch', 'themify')),
                        array('img' => 'stretch_auto', 'value' => 1, 'name' => __('Auto height', 'themify'))
                    ),
                    'gutter'=>array(
                            array('name' => sprintf(__('Normal Gutter(%s%%)', 'themify'),$globalGutters['gutter']), 'value' => 'gutter'),
                            array('name' => sprintf(__('Narrow Gutter(%s%%)', 'themify'),$globalGutters['narrow']), 'value' => 'narrow'),
                            array('name' => sprintf(__('No Gutter(%s%%)', 'themify'),$globalGutters['none']), 'value' => 'none')
                    )
				);
                return $key==='all'?$vals:$vals[$key];
					}

	/**
	 * Returns list of colors and thumbnails
	 *
	 * @return array
	 */
	public static function get_gutters($def=true) {
		    $gutters=array(
			'gutter'=>3.2,
			'narrow'=>1.6,
			'none'=>0
		    );
		    foreach($gutters as $k=>$v){
			$val=themify_builder_get( 'setting-'.$k,'setting-'.$k);
			if($val!==null && $val!==''){
			    if($v!=$val){
				$gutters[$k]=$val;
			    }
			    elseif($def===false){
				unset($gutters[$k]);
			    }
			}
		    }
		    return $gutters;
		}

	/**
	 * Returns list of colors and thumbnails
	 *
	 * @return array
	 */
	public static function get_colors() {
		return apply_filters('themify_builder_module_color_presets', array(
			array('img' => 'default', 'value' => 'default', 'label' => __('default', 'themify')),
			array('img' => 'black', 'value' => 'black', 'label' => __('black', 'themify')),
			array('img' => 'grey', 'value' => 'gray', 'label' => __('gray', 'themify')),
			array('img' => 'blue', 'value' => 'blue', 'label' => __('blue', 'themify')),
			array('img' => 'light-blue', 'value' => 'light-blue', 'label' => __('light-blue', 'themify')),
			array('img' => 'green', 'value' => 'green', 'label' => __('green', 'themify')),
			array('img' => 'light-green', 'value' => 'light-green', 'label' => __('light-green', 'themify')),
			array('img' => 'purple', 'value' => 'purple', 'label' => __('purple', 'themify')),
			array('img' => 'light-purple', 'value' => 'light-purple', 'label' => __('light-purple', 'themify')),
			array('img' => 'brown', 'value' => 'brown', 'label' => __('brown', 'themify')),
			array('img' => 'orange', 'value' => 'orange', 'label' => __('orange', 'themify')),
			array('img' => 'yellow', 'value' => 'yellow', 'label' => __('yellow', 'themify')),
			array('img' => 'red', 'value' => 'red', 'label' => __('red', 'themify')),
			array('img' => 'pink', 'value' => 'pink', 'label' => __('pink', 'themify'))
		));
	}

	/**
	 * Returns list of appearance
	 *
	 * @return array
	 */
	public static function get_appearance() {
		return array(
			array('name' => 'rounded', 'value' => __('Rounded', 'themify')),
			array('name' => 'gradient', 'value' => __('Gradient', 'themify')),
			array('name' => 'glossy', 'value' => __('Glossy', 'themify')),
			array('name' => 'embossed', 'value' => __('Embossed', 'themify')),
			array('name' => 'shadow', 'value' => __('Shadow', 'themify'))
		);
	}

	/**
	 * Returns list of border styles
	 *
	 * @return array
	 */
	public static function get_border_styles() {
		return array(
			'solid' => __('Solid', 'themify'),
			'dashed' => __('Dashed', 'themify'),
			'dotted' => __('Dotted', 'themify'),
			'double' => __('Double', 'themify'),
			'none' => __('None', 'themify')
		);
	}

	/**
	 * Returns list of border styles
	 *
	 * @return array
	 */
	public static function get_border_radius_styles() {
		return array(
			array('id' => 'top', 'label' => __('Top Left', 'themify')),
			array('id' => 'right', 'label' => __('Top right', 'themify')),
			array('id' => 'left', 'label' => __('Bottom Left', 'themify')),
			array('id' => 'bottom', 'label' => __('Bottom Right', 'themify'))
		);
	}

	/**
	 * Returns list of text_aligment
	 *
	 * @return array
	 */
	public static function get_text_aligment() {
		return array(
			array('value' => 'left', 'name' => __('Left', 'themify'), 'icon' => themify_get_icon('align-left', 'ti')),
			array('value' => 'center', 'name' => __('Center', 'themify'), 'icon' => themify_get_icon('align-center', 'ti')),
			array('value' => 'right', 'name' => __('Right', 'themify'), 'icon' => themify_get_icon('align-right', 'ti')),
			array('value' => 'justify', 'name' => __('Justify', 'themify'), 'icon' => themify_get_icon('align-justify', 'ti'))
		);
	}

	/**
	 * Returns list of flex_aligment_items
	 *
	 * @return array
	 */
	public static function get_flex_aligment_items() {
		return array(
			array('value' => 'start', 'name' => __('Start', 'themify'), 'icon' => themify_get_icon('align-left', 'ti')),
			array('value' => 'center', 'name' => __('Center', 'themify'), 'icon' => themify_get_icon('align-center', 'ti')),
			array('value' => 'end', 'name' => __('End', 'themify'), 'icon' => themify_get_icon('align-right', 'ti')),
			array('value' => 'normal', 'name' => __('Normal', 'themify'), 'icon' => themify_get_icon('align-justify', 'ti'))
		);
	}
	
	/**
	 * Returns list of flex_aligment_content
	 *
	 * @return array
	 */
	public static function get_flex_aligment_content() {
		return array(
			array('value' => 'start', 'name' => __('Start', 'themify'), 'icon' => themify_get_icon('align-left', 'ti')),
			array('value' => 'center', 'name' => __('Center', 'themify'), 'icon' => themify_get_icon('align-center', 'ti')),
			array('value' => 'end', 'name' => __('End', 'themify'), 'icon' => themify_get_icon('align-right', 'ti')),
			array('value' => 'normal', 'name' => __('Normal', 'themify'), 'icon' => themify_get_icon('align-justify', 'ti'))
		);
	}
	/**
	 * Returns list of background repeat values
	 *
	 * @return array
	 */
	public static function get_repeat() {
		return array(
			'repeat' => __('Repeat All', 'themify'),
			'repeat-x' => __('Repeat Horizontally', 'themify'),
			'repeat-y' => __('Repeat Vertically', 'themify'),
			'no-repeat' => __('Do not repeat', 'themify'),
			'fullcover' => __('Fullcover', 'themify')
		);
	}

	/**
	 * Returns list of background position values
	 *
	 * @return array
	 */
	public static function get_position() {
		return array(
			'left-top' => __('Left Top', 'themify'),
			'left-center' => __('Left Center', 'themify'),
			'left-bottom' => __('Left Bottom', 'themify'),
			'right-top' => __('Right top', 'themify'),
			'right-center' => __('Right Center', 'themify'),
			'right-bottom' => __('Right Bottom', 'themify'),
			'center-top' => __('Center Top', 'themify'),
			'center-center' => __('Center Center', 'themify'),
			'center-bottom' => __('Center Bottom', 'themify')
		);
	}

	/**
	 * Returns list of text_decoration
	 *
	 * @return array
	 */
	public static function get_text_decoration() {
		return array(
			array('value' => 'underline', 'name' => __('Underline', 'themify'), 'label_class' => 'tb_text_underline', 'icon' => 'U'),
			array('value' => 'overline', 'name' => __('Overline', 'themify'), 'label_class' => 'tb_text_overline', 'icon' => 'O'),
			array('value' => 'line-through', 'name' => __('Line through', 'themify'), 'label_class' => 'tb_text_through', 'icon' => 'S'),
			array('value' => 'none', 'name' => __('None', 'themify'), 'label_class' => 'tb_text_none', 'icon' => '-')
		);
	}

	/**
	 * Returns list of font style option
	 *
	 * @return array
	 */
	public static function get_font_style() {
		return array(
			array('value' => 'italic', 'name' => __('Italic', 'themify'), 'icon' => '<span class="tb_font_italic">I</span>'),
			array('value' => 'normal', 'name' => __('Normal', 'themify'), 'icon' => 'N')
		);
	}

	/**
	 * Returns list of font weight option
	 *
	 * @return array
	 */
	public static function get_font_weight() {
		return array(
			array('value' => 'bold', 'name' => __('Bold', 'themify'), 'icon' => '<span class="tb_font_bold">B</span>'),
		);
	}

	/**
	 * Returns list of text transform options
	 *
	 * @return array
	 */
	public static function get_text_transform() {
		return array(
			array('value' => 'uppercase', 'name' => __('Uppercase', 'themify'), 'icon' => 'AB'),
			array('value' => 'lowercase', 'name' => __('Lowercase', 'themify'), 'icon' => 'ab'),
			array('value' => 'capitalize', 'name' => __('Capitalize', 'themify'), 'icon' => 'Ab'),
			array('value' => 'none', 'name' => __('None', 'themify'), 'icon' => '–')
		);
	}

	/**
	 * Returns list of blend mode options
	 *
	 * @return array
	 */
	public static function get_blend_mode() {
		return array(
			'normal' => __('Normal', 'themify'),
			'multiply' => __('Multiply', 'themify'),
			'screen' => __('Screen', 'themify'),
			'overlay' => __('Overlay', 'themify'),
			'darken' => __('Darken', 'themify'),
			'lighten' => __('Lighten', 'themify'),
			'color-dodge' => __('Color Dodge', 'themify'),
			'color-burn' => __('Color Burn', 'themify'),
			'difference' => __('Difference', 'themify'),
			'exclusion' => __('Exclusion', 'themify'),
			'hue' => __('Hue', 'themify'),
			'saturation' => __('Saturation', 'themify'),
			'color' => __('Color', 'themify'),
			'luminosity' => __('Luminosity', 'themify')
		);
	}

	/**
	 * Check whether image script is use or not
	 *
	 * @since 2.4.2 Check if it's a Themify theme or not. If it's not, it's Builder standalone plugin.
	 *
	 * @return boolean
	 */
	public static function is_img_php_disabled() {
		static $is = NULL;
		if ($is === null) {
			$is = themify_builder_get('setting-img_settings_use', 'image_setting-img_settings_use') ? true : false;
		}
		return $is;
	}

	public static function is_fullwidth_layout_supported() {
	    return apply_filters('themify_builder_fullwidth_layout_support', false);
	}

	/**
	 * Get alt text defined in WP Media attachment by a given URL
	 *
	 * @since 2.2.5
	 *
	 * @param string $image_url
	 *
	 * @return string
	 */
	public static function get_alt_by_url($image_url) {
		$attachment_id = themify_get_attachment_id_from_url($image_url);
		if ($attachment_id && ($alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true))) {
			return $alt;
		}
		return '';
	}

	/**
	 * Get all modules settings for used in localize script.
	 *
	 * @access public
	 * @return array
	 */
	public static function get_modules_localize_settings() {
		$return = array();
		foreach (self::$modules as $module) {
			$default = $module->get_live_default();
			$icon = $module->get_icon();
			$return[$module->slug]['name'] = $module->name;
			$return[$module->slug]['category'] = isset($module->category)?$module->category:$module->get_group();
			if ($icon !== false) {
				if ($icon === '') {
					$icon = $module->slug;
				}
				$return[$module->slug]['icon'] = $icon;
				themify_get_icon($icon, 'ti');
			}
			if ($default) {
				$return[$module->slug]['defaults'] = $default;
			}
		}

		return $return;
	}


	public static function format_text($content) {
		global $wp_embed, $ThemifyBuilder;

		$isLoop = $ThemifyBuilder->in_the_loop === true;
		$ThemifyBuilder->in_the_loop = true;
		$content = wptexturize($content);

		$pattern = '|<p>\s*(https?://[^\s"]+)\s*</p>|im'; // pattern to check embed url
		$to = '<p>' . PHP_EOL . '$1' . PHP_EOL . '</p>'; // add line break
		$content = $wp_embed->run_shortcode($content);
		$content = preg_replace($pattern, $to, $content);
		$content = $wp_embed->autoembed($content);
		$content = do_shortcode(shortcode_unautop($content));
		$ThemifyBuilder->in_the_loop = $isLoop;
		$content = convert_smilies($content);
		return self::generate_read_more($content);

	}

	/*
	 * Generate read more link for text module
	 *
	 * @param string $content
	 * @return string generated load more link in the text.
	 */

	public static function generate_read_more($content) {
		if (!empty($content) && strpos($content, '!--more') !== false && preg_match('/(<|&lt;)!--more(.*?)?--(>|&gt;)/', $content, $matches)) {
			$text = trim($matches[2]);
			$read_more_text = !empty($text) ? $text : apply_filters('themify_builder_more_text', __('More ', 'themify'));
			$content = str_replace($matches[0], '<div class="more-text" style="display: none">', $content);
			$content .= '</div><a href="#" class="module-text-more">' . $read_more_text . '</a>';
		}
		return $content;
	}


	public static function is_module_active($mod_name) {
		if (themify_is_themify_theme()) {
                    $data = themify_get_data();
                    $pre = 'setting-page_builder_exc_';
		} else {
                    $pre = 'builder_exclude_module_';
                    $data = self::get_builder_settings();
		}
		return empty($data[$pre . $mod_name]);
	}

	/**
	 * Get module php files data
	 * @param string $select
	 * @return array
	 */
	public static function get_modules($select = 'active') {
		$modules = array();
		$directories = self::$modules_registry;
                $defaultModules=array(
                    'accordion',
                    'alert',
                    'box',
                    'buttons',
                    'callout',
		    'code',
                    'divider',
                    'fancy-heading',
                    'feature',
                    'gallery',
                    'icon',
                    'image',
                    'layout-part',
                    'link-block',
                    'login',
		//    'lottie',
                    'map',
                    'menu',
                    'optin',
                    'overlay-content',
                    'page-break',
                    'plain-text',
                    'post',
                    'service-menu',
                    'signup-form',
                    'slider',
                    'social-share',
		    'star',
                    'tab',
                    'testimonial-slider',
                    'text',
		    'toc',
                    'twitter',
                    'video',
                    'widget',
                    'widgetized'
                );
		$deprecated=array(
		    'highlight',
		    'portfolio',
		    'testimonial'
		);
		foreach($deprecated as $id){
		    if(Themify_Builder_Model::is_cpt_active( $id )){
			$defaultModules[]=$id;
		    }
		}
		unset($deprecated);
                foreach($defaultModules as $id){
                    if ($select === 'active' && !self::is_module_active($id)) {
                        continue;
                    }
                    $modules[$id] = true;
                }
                unset($defaultModules);
		foreach ($directories as $id=>$dir) {
                    if ($select === 'active' && !self::is_module_active($id)) {
                            continue;
                    }
                    $modules[$id] = $dir;
		}
		return ($select === 'active' || $select === 'all')?$modules:(isset($modules[$select])?array($select=>$modules[$select]):array());
	}

	/**
	 * Check whether theme loop template exist
	 * @param string $template_name
	 * @param string $template_path
	 * @return boolean
	 */
	public static function is_loop_template_exist($template_name, $template_path) {
		return !locate_template(array(trailingslashit($template_path) . $template_name)) ? false : true;
	}
        public static function add_module($path) {

            if(strpos($path, '.php') !== false){
                $path_info = pathinfo($path);
                $id = str_replace('module-', '', $path_info['filename']);
                self::$modules_registry[$id]=$path_info['dirname'];
            }
            elseif (is_dir($path)) {//backward
                $d = dir($path);
                while (( false !== ( $entry = $d->read() ))) {
                    if ($entry !== '.' && $entry !== '..' && $entry !== '.svn' && strpos($entry, 'module-') === 0) {
                        $id = str_replace(array('module-','.php'), '', $entry);
                        self::$modules_registry[$id] =rtrim($d->path,'/');
                    }
                }
            }
        }

	public static function get_directory_path($context='') {
            return self::$modules_registry;
	}


	public static function is_cpt_active($post_type) {
		return apply_filters("builder_is_{$post_type}_active", in_array($post_type, self::$builder_cpt, true));
	}

	public static function builder_cpt_check() {
		static $done = null;
		if ($done === null) {
			$done = true;
			foreach (array('slider', 'highlight', 'testimonial') as $cpt) {
				if (post_type_exists($cpt)) {
					self::$builder_cpt[] = $cpt;
				} else {
					$posts = get_posts(array(
						'post_type' => $cpt,
						'posts_per_page' => 1,
						'post_status' => 'any',
						'no_found_rows' => true,
						'ignore_sticky_posts' => true,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,
						'cache_results' => false,
						'orderby' => 'none'
					));
					if (!empty($posts)) {
						self::$builder_cpt[] = $cpt;
					}
				}
			}
		}
	}

	/**
	 * Get a list of post types that can be accessed publicly
	 *
	 * does not include attachments, Builder layouts and layout parts,
	 * and also custom post types in Builder that have their own module.
	 *
	 * @return array of key => label pairs
	 */
	public static function get_public_post_types() {

		$post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
		$excluded_types = array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section', 'tglobal_style', 'tb_cf', 'elementor_library');
		foreach ($post_types as $key => $value) {
			if (!in_array($key, $excluded_types, true)) {
				$result[$key] = $value->labels->singular_name;
			}
		}
		return apply_filters('builder_get_public_post_types', $result);
	}

	/**
	 * Get a list of taxonomies that can be accessed publicly
	 *
	 * does not include post formats, section categories (used by some themes),
	 * and also custom post types in Builder that have their own module.
	 *
	 * @return array of key => label pairs
	 */
	public static function get_public_taxonomies() {
		$taxonomies = get_taxonomies(array('public' => true), 'objects');
		$excludes = array('post_format', 'section-category','product_shipping_class');
		foreach ($taxonomies as $key => $value) {
			if (!in_array($key, $excludes, true)) {
				$result[$key] = $value->labels->name;
			}
		}

		return apply_filters('builder_get_public_taxonomies', $result);
	}

	public static function parse_slug_to_ids($slug_string, $post_type = 'post') {
		$slug_arr = explode(',', $slug_string);
		$return = array();
		if (!empty($slug_arr)) {
			foreach ($slug_arr as $slug) {
				$return[] = is_numeric( $slug ) ? $slug : self::get_id_by_slug(trim($slug), $post_type);
			}
		}
		return $return;
	}

	public static function get_id_by_slug($slug, $post_type = 'post') {
		$args = array(
			'name' => $slug,
			'post_type' => $post_type,
			'post_status' => 'publish',
			'numberposts' => 1,
			'no_found_rows' => true,
			'cache_results' => false,
			'ignore_sticky_posts' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby' => 'none'
		);
		$my_posts = get_posts($args);
		return $my_posts ? $my_posts[0]->ID : null;
	}

	public static function getMapKey() {
		return themify_builder_get('setting-google_map_key', 'builder_settings_google_map_key');
	}

	/**
	 * Get Builder Settings
	 */
	public static function get_builder_settings() {
		static $data = null;
		if ($data === null) {
			$data = get_option('themify_builder_setting');
			if (is_array($data) && !empty($data)) {
				foreach ($data as $name => $value) {
					$data[$name] = stripslashes($value);
				}
			} else {
				$data = array();
			}
		}
		return $data;
	}

	/**
	 * Get ID
	 */
	public static function get_ID() {
		return themify_is_shop() ? themify_shop_pageId() : get_the_id();
	}


	public static function get_transient_time() {
		return apply_filters('themify_builder_ticks', MINUTE_IN_SECONDS / 2);
	}

	public static function set_edit_transient($post_id, $value) {
	    return Themify_Storage::set(self::TRANSIENT_NAME . $post_id, $value, self::get_transient_time());
	}

	public static function get_edit_transient($post_id) {
	    return Themify_Storage::get(self::TRANSIENT_NAME . $post_id);
	}

	public static function remove_edit_transient($post_id) {
	    return Themify_Storage::delete(self::TRANSIENT_NAME . $post_id);
	}


	/**
	 * Check if gutenberg active
	 * @return boolean
	 */
	public static function is_gutenberg_active() {
		static $is = null;
		if ($is === null) {
			$is = !self::is_plugin_active('disable-gutenberg/disable-gutenberg.php') && !self::is_plugin_active('classic-editor/classic-editor.php');
		}
		return $is;
	}


	/**
	 * Check if we are gutenberg editor
         * !IMPORTANT can be used only after action "get_current_screen"
	 * @return boolean
	 */
	public static function is_gutenberg_editor() {
		static $is = null;
		if ($is === null) {
                    $is = !isset($_GET['classic-editor']) && is_admin() && self::is_gutenberg_active() && get_current_screen()->is_block_editor();
		}
		return $is;
	}

	public static function isWpEditorDisable() {
		return themify_builder_get('setting-page_builder_disable_wp_editor', 'builder_disable_wp_editor');
	}

	/**
	 * Plugin Active checking
	 *
	 * @access public
	 * @param string $plugin
	 * @return bool
	 */
	public static function is_plugin_active($plugin) {
		static $plugins = null;
		static $active_plugins = array();
		if ($plugins === null) {
			$plugins = is_multisite() ? get_site_option('active_sitewide_plugins') : false;
			$active_plugins = (array) apply_filters('active_plugins', get_option('active_plugins'));
		}
		return ( $plugins !== false && isset($plugins[$plugin]) ) || in_array($plugin, $active_plugins, true);
	}



	/**
	 * Get frame type
	 * @return string|boolean
	 */
	public static function get_frame($settings, $side) {
		if (isset($settings["{$side}-frame_type"]) && $settings["{$side}-frame_type"] === $side . '-presets' && !empty($settings["{$side}-frame_layout"])) {
			return $settings["{$side}-frame_layout"] !== 'none' ? 'presets' : false;
		}
                elseif (isset($settings["{$side}-frame_type"]) && $settings["{$side}-frame_type"] === $side . '-custom' && !empty($settings["{$side}-frame_custom"])) {
			return 'custom';
		}
                else {
			return false;
		}
	}

	public static function get_animation() {

		$bp = array('desktop' => '') + themify_get_breakpoints();
		$sticky = array();
		foreach ($bp as $b => $v) {
			if ($b === 'desktop') {
				$postfix = '';
				$checkbox = array(
					'id' => 'stick_at_check',
					'type' => 'checkbox',
					'options' => array(
						array('name' => 'stick_at_check', 'value' => __('Stick at', 'themify'))
					),
					'binding' => array(
						'not_checked' => array(
							'hide' => array('unstick_wr', 'stick_wr')
						),
						'checked' => array(
							'show' => array('unstick_wr', 'stick_wr')
						)
					)
				);
			} else {
				$postfix = $b === 'tablet_landscape' ? '_tl' : '_' . $b[0];
				$checkbox = array(
					'id' => 'stick_at_check' . $postfix,
					'type' => 'radio',
					'option_js' => true,
					'options' => array(
						array('value' => '', 'name' => __('Inherit', 'themify')),
						array('value' => '1', 'name' => __('Enable', 'themify')),
						array('value' => '0', 'name' => __('Disable', 'themify'))
					)
				);
			}
			$sticky[$b] = array(
				'icon' => 'ti-' . ($b === 'tablet_landscape' ? 'tablet' : $b),
				'title' => $b === 'tablet_landscape' ? __('Tablet Landscape', 'themify') : ucfirst($b),
				'class' => $b === 'tablet_landscape' ? 'tab_tablet_landscape' : '',
				'label' => '',
				'options' => array(
					array(
						'type' => 'group',
						'wrap_class' => 'stick_middle_wrapper',
						'options' => array(
							$checkbox,
							array(
								'type' => 'group',
								'wrap_class' => 'stick_wr tb_group_element_1',
								'options' => array(
									array(
										'id' => 'stick_at_position' . $postfix,
										'type' => 'select',
										'options' => array(
											'top' => __('Top Position', 'themify'),
											'bottom' => __('Bottom Position', 'themify')
										)
									),
									array(
										'id' => 'stick_at_pos_val' . $postfix,
										'type' => 'range',
										'units' => array(
											'px' => array(
												'max' => 1000000
											),
											'%' => ''
										)
									)
								)
							)
						)
					),
					array(
						'type' => 'group',
						'wrap_class' => 'stick_middle_wrapper unstick_wr tb_group_element_1',
						'options' => array(
							array(
								'id' => 'unstick_when_check' . $postfix,
								'type' => 'checkbox',
								'options' => array(
									array('name' => 'unstick_when_check', 'value' => __('Un-stick when', 'themify'))
								),
								'binding' => array(
									'not_checked' => array(
										'hide' => 'unstick_when_wr'
									),
									'checked' => array(
										'show' => 'unstick_when_wr'
									)
								)
							),
							array(
								'type' => 'group',
								'wrap_class' => 'unstick_when_wr',
								'options' => array(
									array(
										'id' => 'unstick_when_element' . $postfix,
										'type' => 'select',
										'options' => array(
											'builder_end' => __('Builder Content End', 'themify'),
											'row' => __('Row', 'themify'),
											'module' => __('Module', 'themify')
										),
										'binding' => array(
											'builder_end' => array(
												'hide' => 'unstick_opt_wr'
											),
											'row' => array(
												'show' => array('unstick_opt_wr', 'unstick_row'),
												'hide' => 'unstick_module'
											),
											'module' => array(
												'show' => array('unstick_opt_wr', 'unstick_module'),
												'hide' => 'unstick_row'
											)
										)
									),
									array(
										'type' => 'group',
										'wrap_class' => 'unstick_opt_wr tf_inline_b',
										'options' => array(
											array(
												'id' => 'unstick_when_el_row_id' . $postfix,
												'type' => 'sticky',
												'wrap_class' => 'unstick_row',
												'key' => 'row'
											),
											array(
												'id' => 'unstick_when_el_mod_id' . $postfix,
												'type' => 'sticky',
												'wrap_class' => 'unstick_module',
												'key' => 'module'
											),
											array(
												'id' => 'unstick_when_condition' . $postfix,
												'type' => 'select',
												'options' => array(
													'hits' => __('Hits', 'themify'),
													'passes' => __('Passes', 'themify')
												)
											),
											array(
												'id' => 'unstick_when_pos' . $postfix,
												'type' => 'select',
												'options' => array(
													'this' => __('This element', 'themify'),
													'top' => __('Viewport Top', 'themify'),
													'bottom' => __('Viewport Bottom', 'themify')
												),
												'binding' => array(
													'this' => array(
														'hide' => 'unstick_when_pos_val' . $postfix
													),
													'top' => array(
														'show' => 'unstick_when_pos_val' . $postfix
													),
													'bottom' => array(
														'show' => 'unstick_when_pos_val' . $postfix
													)
												)
											),
											array(
												'id' => 'unstick_when_pos_val' . $postfix,
												'type' => 'range',
												'units' => array(
													'px' => array(
														'max' => 100000
													),
													'%' => ''
												)
											)
										)
									)
								)
							)
						)
					)
				)
			);
		}
		unset($bp);
		return apply_filters('themify_builder_animation_settings_fields', array(
			//Animation
			array(
				'type' => 'separator',
				'label' => __('Animation', 'themify')
			),
			array(
				'type' => 'multi',
				'label' => __('Entrance Animation', 'themify'),
				'options' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select'
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'number',
						'after' => __('Delay', 'themify'),
						'step' => .1
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'number',
						'after' => __('Repeat', 'themify')
					)
				)
			),
			array(
				'type' => 'animation_select',
				'label' => __('Hover Animation', 'themify'),
				'id' => 'hover_animation_effect'
			),
			//Float Scrolling
			array(
				'type' => 'separator',
				'label' => __('Scroll Effects', 'themify')
			),
			array(
				'type' => 'tabs',
				'isRadio' => true,
				'id' => 'animation_effect_tab',
				'options' => array(
					's_e_m' => array(
						'options' => array(
							array(
								'id' => 'motion_effects',
								'type' => 'accordion',
								'options' => array(
									'v' => array(
										'label' => __('Vertical Scroll', 'themify'),
										'options' => array(
											array(
												'id' => 'v_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'up' => __('Up', 'themify'),
													'down' => __('Down', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => array('v_speed', 'v_vp')
													),
													'not_empty' => array(
														'show' => array('v_speed', 'v_vp')
													)
												)
											),
											array(
												'id' => 'v_speed',
												'type' => 'slider_range',
												'label' => __('Speed', 'themify'),
												'options' => array(
													'min' => 1,
													'max' => 10,
													'unit' => '',
													'range' => false,
													'default' => 1
												)
											),
											array(
												'id' => 'v_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											)
										)
									),
									'h' => array(
										'label' => __('Horizontal Scroll', 'themify'),
										'options' => array(
											array(
												'id' => 'h_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'toleft' => __('To Left', 'themify'),
													'toright' => __('To Right', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => array('h_speed', 'h_vp')
													),
													'not_empty' => array(
														'show' => array('h_speed', 'h_vp')
													)
												)
											),
											array(
												'id' => 'h_speed',
												'type' => 'slider_range',
												'label' => __('Speed', 'themify'),
												'options' => array(
													'min' => 1,
													'max' => 10,
													'unit' => '',
													'range' => false,
													'default' => 1
												)
											),
											array(
												'id' => 'h_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											)
										)
									),
									't' => array(
										'label' => __('Transparency', 'themify'),
										'options' => array(
											array(
												'id' => 't_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'fadein' => __('Fade In', 'themify'),
													'fadeout' => __('Fade Out', 'themify'),
													'fadeoutin' => __('Fade Out In', 'themify'),
													'fadeinout' => __('Fade In Out', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => 't_vp'
													),
													'not_empty' => array(
														'show' => 't_vp'
													)
												)
											),
											array(
												'id' => 't_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											)
										)
									),
									'b' => array(
										'label' => __('Blur', 'themify'),
										'options' => array(
											array(
												'id' => 'b_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'fadein' => __('Fade In', 'themify'),
													'fadeout' => __('Fade Out', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => array('b_level', 'b_vp')
													),
													'not_empty' => array(
														'show' => array('b_level', 'b_vp')
													)
												)
											),
											array(
												'id' => 'b_level',
												'type' => 'slider_range',
												'label' => __('Level', 'themify'),
												'options' => array(
													'min' => 1,
													'max' => 10,
													'unit' => '',
													'range' => false,
													'default' => 1
												)
											),
											array(
												'id' => 'b_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											),
										)
									),
									'r' => array(
										'label' => __('Rotate', 'themify'),
										'options' => array(
											array(
												'id' => 'r_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'toleft' => __('To Left', 'themify'),
													'toright' => __('To Right', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => array('r_num', 'r_origin', 'r_vp')
													),
													'not_empty' => array(
														'show' => array('r_num', 'r_origin', 'r_vp')
													)
												)
											),
											array(
												'id' => 'r_num',
												'type' => 'range',
												'label' => __('Number of Spins', 'themify'),
												'units' => array(
													'' => array(
														'min' => .05,
														'increment' => .1
													)
												)
											),
											array(
												'id' => 'r_origin',
												'type' => 'position_box',
												'label' => __('Transform Origin', 'themify')
											),
											array(
												'id' => 'r_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											),
										)
									),
									's' => array(
										'label' => __('Scale', 'themify'),
										'options' => array(
											array(
												'id' => 's_dir',
												'type' => 'select',
												'label' => __('Direction', 'themify'),
												'options' => array(
													'' => '',
													'up' => __('Scale Up', 'themify'),
													'down' => __('Scale Down', 'themify')
												),
												'binding' => array(
													'empty' => array(
														'hide' => array('s_ratio', 's_origin', 's_vp')
													),
													'not_empty' => array(
														'show' => array('s_ratio', 's_origin', 's_vp')
													)
												)
											),
											array(
												'id' => 's_ratio',
												'type' => 'range',
												'label' => __('Scale Ratio', 'themify'),
												'units' => array(
													'' => array(
														'min' => 1,
														'max' => 30,
														'increment' => .1
													)
												)
											),
											array(
												'id' => 's_origin',
												'type' => 'position_box',
												'label' => __('Transform Origin', 'themify')
											),
											array(
												'id' => 's_vp',
												'type' => 'slider_range',
												'label' => __('Viewport', 'themify')
											)
										)
									)
								)
							)
						)
					),
					's_e_s' => array(
						'options' => array(
							array(
								'type' => 'tabs',
								'options' => $sticky
							)
						)
					)
				)
			)
				));
	}

	/**
	 * Append visibility controls to row/modules.
	 * @access    public
	 * @return    array
	 */
	public static function get_visibility() {
		$options = array(
			'on' => array('name' => '', 'value' => 's'),
			'off' => array('name' => 'hide', 'value' => 'hi')
		);
		return array(
			array(
				'type' => 'separator',
				'label' => 'visibility',
			),
			array(
				'id' => 'visibility_desktop',
				'label' => __('Desktop', 'themify'),
				'type' => 'toggle_switch',
				'default' => 'on',
				'options' => $options,
				'wrap_class' => 'tb_module_visibility_control'
			),
			array(
				'id' => 'visibility_tablet_landscape',
				'label' => __('Tablet Landscape', 'themify'),
				'type' => 'toggle_switch',
				'default' => 'on',
				'options' => $options,
				'wrap_class' => 'tb_module_visibility_control'
			),
			array(
				'id' => 'visibility_tablet',
				'label' => __('Tablet Portrait', 'themify'),
				'type' => 'toggle_switch',
				'default' => 'on',
				'options' => $options,
				'wrap_class' => 'tb_module_visibility_control'
			),
			array(
				'id' => 'visibility_mobile',
				'label' => __('Mobile', 'themify'),
				'type' => 'toggle_switch',
				'default' => 'on',
				'options' => $options,
				'wrap_class' => 'tb_module_visibility_control'
			),
			array(
				'id' => 'sticky_visibility',
				'label' => __('Sticky Visibility', 'themify'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name' => 'hide')
				),
				'wrap_class' => 'tb_module_visibility_control',
				'help' => __('Hide this when parent row\'s sticky scrolling is active', 'themify'),
			),
			array(
				'id' => 'visibility_all',
				'label' => __('Hide All', 'themify'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name' => 'hide_all')
				),
				'binding' => array(
					'not_checked' => array(
						'show' => 'tb_module_visibility_control'
					),
					'checked' => array(
						'hide' => 'tb_module_visibility_control'
					)
				),
				'help' => __('Hide this in all devices', 'themify')
			)
		);
	}

	public static function checkUniqId($id){
		static $ids=array();
		if($id!==null && !isset($ids[$id])){
			$ids[$id] = true;
			return $id;
		}
		$id=self::generateID();
		if(isset($ids[$id])){
			while(isset($ids[$id])){
				$id = self::generateID();
			}
		}
	    $ids[$id] = true;
	    return $id;
	}

    public static function generateID() {
		$hash = '';
		$alpha_numeric = 'abcdefghijklmnopqrstuvwxyz0123456789';
		for ($i = 0; $i < 4; ++$i) {
			$hash .= '' . $alpha_numeric[rand(0, 35)];
		}
		$m = microtime();
		$len = strlen($m);
		if ($len > 10) {
			$len = floor($len / 2);
		}
		--$len;
		for ($i = 0; $i < 3; ++$i) {
			$h = $m[rand(2, $len)];
			if ($h === '') {
			$h = $m[rand(2, ( $len - 1))];
			}
			$hash .= $h;
		}
		return $hash;
    }

	public static function get_slider_options() {

		$visible_slides_options = array_combine( range( 1, 20 ), range( 1, 20 ) );
		return array(
			array(
				'id' => 'effect_slider',
				'type' => 'select',
				'options' => array(
					'scroll' => __('Slide', 'themify'),
					'fade' => __('Fade', 'themify'),
					'cube' => __('Cube', 'themify'),
					'flip' => __('Flip', 'themify'),
					'coverflow' => __('Coverflow', 'themify'),
					'continuously' => __('Continuously', 'themify')
				),
				'binding' => array(
					'flip' => array('hide' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'scroll_opt_slider'), 'show' => 'auto_scroll_opt_slider'),
					'fade' => array('hide' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'scroll_opt_slider'), 'show' => 'auto_scroll_opt_slider'),
					'cube' => array('hide' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'scroll_opt_slider'), 'show' => 'auto_scroll_opt_slider'),
					'coverflow' => array('show' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'auto_scroll_opt_slider', 'scroll_opt_slider')),
					'scroll' => array('show' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'auto_scroll_opt_slider', 'scroll_opt_slider')),
					'continuously' => array('show' => array('visible_opt_slider', 'tab_visible_opt_slider', 'mob_visible_opt_slider', 'scroll_opt_slider'), 'hide' => 'auto_scroll_opt_slider')
				),
				'label' => __('Effect', 'themify')
			),
			array(
				'id' => 'visible_opt_slider',
				'type' => 'select',
				'options' => $visible_slides_options,
				'label' => __('Visible Slides', 'themify')
			),
			array(
				'id' => 'tab_visible_opt_slider',
				'type' => 'select',
				'options' => $visible_slides_options,
				'label' => __('Tablet Visible Slides', 'themify')
			),
			array(
				'id' => 'mob_visible_opt_slider',
				'type' => 'select',
				'options' => $visible_slides_options,
				'label' => __('Mobile Visible Slides', 'themify')
			),
			array(
				'id' => 'auto_scroll_opt_slider',
				'type' => 'select',
				'options' => array(
					'off' => __('Off', 'themify'),
					1 => __('1 sec', 'themify'),
					2 => __('2 sec', 'themify'),
					3 => __('3 sec', 'themify'),
					4 => __('4 sec', 'themify'),
					5 => __('5 sec', 'themify'),
					6 => __('6 sec', 'themify'),
					7 => __('7 sec', 'themify'),
					8 => __('8 sec', 'themify'),
					9 => __('9 sec', 'themify'),
					10 => __('10 sec', 'themify'),
					15 => __('15 sec', 'themify'),
					20 => __('20 sec', 'themify')
				),
				'binding' => array(
					'off' => array(
						'hide' => array('pause_on_hover_slider', 'play_pause_control')
					),
					'select' => array(
						'value' => range(1, 20),
						'show' => array('pause_on_hover_slider', 'play_pause_control')
					)
				),
				'label' => __('Auto Scroll', 'themify')
			),
			array(
				'id' => 'scroll_opt_slider',
				'type' => 'select',
				'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7),
				'label' => __('Scroll', 'themify')
			),
			array(
				'id' => 'speed_opt_slider',
				'type' => 'select',
				'options' => array(
					'normal' => __('Normal', 'themify'),
					'fast' => __('Fast', 'themify'),
					'slow' => __('Slow', 'themify')
				),
				'label' => __('Speed', 'themify')
			),
			array(
				'id' => 'pause_on_hover_slider',
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name' => 'resume', 'value' => 'y'),
					'off' => array('name' => 'false', 'value' => 'no'),
				),
				'label' => __('Pause On Hover', 'themify'),
				'default' => 'on',
			),
			array(
				'id' => 'play_pause_control',
				'type' => 'toggle_switch',
				'options' => 'simple',
				'label' => __('Play/Pause Button', 'themify'),
				'default' => 'off',
			),
			array(
				'id' => 'wrap_slider',
				'type' => 'toggle_switch',
				'options' => 'simple',
				'label' => __('Wrap', 'themify'),
				'default' => 'on',
			),
			array(
				'id' => 'show_nav_slider',
				'type' => 'toggle_switch',
				'options' => 'simple',
				'label' => __('Pagination', 'themify'),
				'default' => 'on',
			),
			array(
				'id' => 'show_arrow_slider',
				'type' => 'toggle_switch',
				'label' => __('Slider Arrows', 'themify'),
				'options' => 'simple',
				'binding' => array(
					'no' => array(
						'hide' => 'show_arrow_buttons_vertical'
					),
					'select' => array(
						'value' => 'no',
						'show' => 'show_arrow_buttons_vertical'
					)
				),
				'default' => 'on',
			),
			array(
				'id' => 'touch_swipe',
				'type' => 'select',
				'label' => __( 'Touch Swipe', 'themify' ),
				'options' => [
					'' => __( 'All devices', 'themify' ),
					'touch' => __( 'Touch devices only', 'themify' ),
					'no' => __( 'Disabled', 'themify' ),
				],
			),
			array(
				'id' => 'show_arrow_buttons_vertical',
				'type' => 'checkbox',
				'options' => array(
					array('name' => 'vertical', 'value' => __('Display arrows middle', 'themify'))
				)
			),
			array(
				'id' => 'left_margin_slider',
				'type' => 'number',
				'label' => __('Left Margin', 'themify'),
				'after' => 'px'
			),
			array(
				'id' => 'right_margin_slider',
				'type' => 'number',
				'label' => __('Right Margin', 'themify'),
				'after' => 'px'
			),
			array(
				'id' => 'height_slider',
				'type' => 'select',
				'options' => array(
					'variable' => __('Variable', 'themify'),
					'auto' => __('Auto', 'themify')
				),
				'label' => __('Height', 'themify'),
				'help' => __('"Auto" measures the highest slide and all other slides will be set to that size. "Variable" makes every slide has it\'s own height.', 'themify')
			)
		);
	}

	public static function removeElementIds(array $data) {
		//save sticky/unsticky ids
		$elementIds=$sticky=array();
		foreach ($data as $i=>&$r) {
			if(isset($r['element_id'])){
				$elementIds[$r['element_id']]=$i;
			}
			if(isset($r['styling']['unstick_when_el_row_id'])){
				$sticky[$i.'r']=$r['styling']['unstick_when_el_row_id'];
			}
			if(isset($r['styling']['unstick_when_el_mod_id'])){
				$sticky[$i.'m']=$r['styling']['unstick_when_el_mod_id'];
			}
			unset($r['cid'], $r['element_id']);

			if (!empty($r['cols'])) {

				foreach ($r['cols'] as $j=>&$c) {

					unset($c['cid'], $c['element_id']);

					if (!empty($c['modules'])) {

						foreach ($c['modules'] as $mk=>&$m) {
							if(isset($m['element_id'])){
								$elementIds[$m['element_id']]=$i.'-'.$j.'-'.$mk;
								unset($m['element_id']);
							}
							if (isset($m['mod_settings']['cid'])) {
								unset($m['mod_settings']['cid']);
							}
							if(isset($m['mod_settings']['unstick_when_el_row_id'])){
								$sticky[$i.'-'.$j.'-'.$mk.'r']=$m['mod_settings']['unstick_when_el_row_id'];
							}
							if(isset($m['mod_settings']['unstick_when_el_mod_id'])){
								$sticky[$i.'-'.$j.'-'.$mk.'m']=$m['mod_settings']['unstick_when_el_mod_id'];
							}
							if (!empty($m['cols'])) {
								if(isset($m['styling']['unstick_when_el_row_id'])){
									$sticky[$i.'-'.$j.'-'.$mk.'r']=$m['styling']['unstick_when_el_row_id'];
								}
								if(isset($m['styling']['unstick_when_el_mod_id'])){
									$sticky[$i.'-'.$j.'-'.$mk.'m']=$m['styling']['unstick_when_el_mod_id'];
								}
								foreach ($m['cols'] as $sb=>&$sub_col) {

									unset($sub_col['cid'], $sub_col['element_id']);

									if (!empty($sub_col['modules'])) {

										foreach ($sub_col['modules'] as $sm=>&$sub_m) {
											if(isset($sub_m['element_id'])){
												$elementIds[$sub_m['element_id']]=$i.'-'.$j.'-'.$mk.'-'.$sb.'-'.$sm;
												unset($sub_m['element_id']);
											}
											if(isset($sub_m['mod_settings']['unstick_when_el_row_id'])){
												$sticky[$i.'-'.$j.'-'.$mk.'-'.$sb.'-'.$sm.'r']=$sub_m['mod_settings']['unstick_when_el_row_id'];
											}
											if(isset($sub_m['mod_settings']['unstick_when_el_mod_id'])){
												$sticky[$i.'-'.$j.'-'.$mk.'-'.$sb.'-'.$sm.'m']=$sub_m['mod_settings']['unstick_when_el_mod_id'];
											}
											if (isset($sub_m['mod_settings']['cid'])) {
												unset($sub_m['mod_settings']['cid']);
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
		if(!empty($sticky)){
			foreach($sticky as $v=>$id){
				if(isset($elementIds[$id])){
					$newId=self::generateID();
					$path1=explode('-',$elementIds[$id]);
					$key=strpos($v,'r',1)!==false?'unstick_when_el_row_id':'unstick_when_el_mod_id';
					$path2=explode('-',strtr($v,array('r'=>'','m'=>'')));
					if(isset($path1[4])){
						if(!isset($data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['cols'][$path1[3]]['modules'][$path1[4]]['element_id'])){
							$data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['cols'][$path1[3]]['modules'][$path1[4]]['element_id']=$newId;
						}
						else{
							$newId=$data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['cols'][$path1[3]]['modules'][$path1[4]]['element_id'];
						}
					}
					elseif(isset($path1[1])){
						if(!isset($data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['element_id'])){
							$data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['element_id']=$newId;
						}
						else{
							$newId=$data[$path1[0]]['cols'][$path1[1]]['modules'][$path1[2]]['element_id'];
						}
					}
					else{
						if(!isset($data[$path1[0]]['element_id'])){
							$data[$path1[0]]['element_id']=$newId;
						}
						else{
							$newId=$data[$path1[0]]['element_id'];
						}
					}

					if(isset($path2[4])){
						$data[$path2[0]]['cols'][$path2[1]]['modules'][$path2[2]]['cols'][$path2[3]]['modules'][$path2[4]]['mod_settings'][$key]=$newId;
					}
					elseif(isset($path2[1])){
						$mKey=!empty($data[$path2[0]]['cols'][$path2[1]]['modules'][$path2[2]]['cols'])?'styling':'mod_settings';
						$data[$path2[0]]['cols'][$path2[1]]['modules'][$path2[2]][$mKey][$key]=$newId;
					}
					else{
						$data[$path2[0]]['styling'][$key]=$newId;
					}
				}
			}
		}
		unset($sticky,$elementIds);
		return $data;
	}


	/**
	 * Generate an unique Id for each component if it doesn't have and check unique in the builder
	 *
	 * @return array
	 */
	public static function generateElementsIds($data) {
	    foreach ($data as &$r) {
		$r['element_id'] = self::checkUniqId((isset($r['element_id']) ? $r['element_id'] : null));
		unset($r['row_order'],$r['cid']);
		if (!empty($r['cols'])) {
		    foreach ($r['cols'] as &$c) {
			$c['element_id'] = self::checkUniqId((isset($c['element_id']) ? $c['element_id'] : null));
			unset($c['column_order'],$c['cid']);
			if (!empty($c['modules'])) {
			    foreach ($c['modules'] as &$m) {
				if (!is_array($m)) {
				    continue;
				}
				$m['element_id'] = self::checkUniqId((isset($m['element_id']) ? $m['element_id'] : null));
				unset($m['row_order']);
				if (isset($m['mod_settings']['cid'])) {
				    unset($m['mod_settings']['cid']);
				}
				if (!empty($m['cols'])) {
				    foreach ($m['cols'] as &$sub_col) {
					$sub_col['element_id'] = self::checkUniqId((isset($sub_col['element_id']) ? $sub_col['element_id'] : null));
					unset($sub_col['column_order'],$sub_col['cid']);
					if (!empty($sub_col['modules'])) {
					    foreach ($sub_col['modules'] as &$sub_m) {
						$sub_m['element_id'] = self::checkUniqId((isset($sub_m['element_id']) ? $sub_m['element_id'] : null));
						if (isset($sub_m['mod_settings']['cid'])) {
						    unset($sub_m['mod_settings']['cid']);
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
	    return $data;
	}

	public static function parseTerms($terms, $taxonomy) {
		$include_by_id = $exclude_by_id = $include_by_slug = $exclude_by_slug = array();
		// deal with how category fields are saved
		$terms = preg_replace('/\|[multiple|single]*$/', '', $terms);

		if ( $terms === '0' || $terms === '' ) {
			return false;
		}

		$temp_terms = explode(',', $terms);

		foreach ($temp_terms as $t) {
			$t = trim($t);
			$isNumeric = is_numeric($t);
			$exclude = $t[0] === '-';
			if ($isNumeric === false) {
				if ($exclude===true) {
					$exclude_by_slug[] = ltrim($t, '-');
				} else {
					$include_by_slug[] = $t;
				}
			} else {
				if ($exclude===true) {
					$exclude_by_id[] = ltrim($t, '-');
				} else {
					$include_by_id[] = $t;
				}
			}
		}
		return array_filter(compact('include_by_id', 'exclude_by_id', 'include_by_slug', 'exclude_by_slug'));
	}

	public static function parseTermsQuery(&$args, $terms, $taxonomy) {
		$terms = self::parseTerms($terms, $taxonomy);
		if ( empty( $terms ) ) {
			return;
		} else {
			$args['tax_query'] = array();
		}
		if (!empty($terms['include_by_id']) && !in_array('0', $terms['include_by_id'])) {
			$args['tax_query'][] = array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $terms['include_by_id']
				)
			);
		}
		if (!empty($terms['include_by_slug']) && !in_array('0', $terms['include_by_slug'])) {
			$args['tax_query'][] = array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $terms['include_by_slug']
				)
			);
		}
		if (!empty($terms['exclude_by_id'])) {
			$args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'id',
				'terms' => $terms['exclude_by_id'],
				'operator' => 'NOT IN'
			);
		}
		if (!empty($terms['exclude_by_slug'])) {
			$args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $terms['exclude_by_slug'],
				'operator' => 'NOT IN'
			);
		}
	}

	public static function get_popular_devices($device = 'all') {
		$result = array();
		if ('all' === $device || 'mobile' === $device) {
			$result['mobile'] = array(
				__('iPhone 7 Plus', 'themify') => array(414, 736),
				__('iPhone XR', 'themify') => array(414, 896),
				__('iPhone XS', 'themify') => array(375, 812),
				__('iPhone 8', 'themify') => array(375, 667),
				__('Galaxy S9+', 'themify') => array(412, 846),
				__('Galaxy S8+', 'themify') => array(360, 740),
				__('Galaxy S7', 'themify') => array(360, 640),
				__('Huawei P20', 'themify') => array(360, 748),
				__('Huawei P10', 'themify') => array(360, 640),
			);
		}
		if ('all' === $device || 'tablet' === $device) {
			$result['tablet'] = array(
				__('iPad Air', 'themify') => array(768, 1024),
				__('Nexus 9', 'themify') => array(768, 1024),
				__('iPad Mini', 'themify') => array(768, 1024),
				__('Galaxy Tab 10', 'themify') => array(800, 1280),
				__('iPad Pro', 'themify') => array(1024, 1366),
			);
		}
		return $result;
	}

	public static function load_appearance_css($data) {
	    static $is=null;
	    if ($is===null && $data !== '' && $data != 'bordered' && $data !== 'circle') {
		if(Themify_Builder::$frontedit_active === true){
		    $is=true;
		    return;
		}
		$data=trim($data);
		if($data!==''){
		    $arr=array('glossy','rounded','shadow','gradient','embossed');
		    foreach($arr as $v){
			if(strpos($data,$v)!==false){
			    $is=true;
			    self::loadCssModules('app',THEMIFY_BUILDER_CSS_MODULES . 'appearance.css');
			    break;
			}
		    }
		}
	    }
	}

	public static function load_color_css($color) {
		static $is=null;
		if ($is===null && $color != '' && $color !== 'tb_default_color' && $color !== 'default' && $color !== 'transparent'  && $color !== 'white' && $color !== 'outline' && Themify_Builder::$frontedit_active === false) {
		    $is=true;
		    self::loadCssModules('color',THEMIFY_BUILDER_CSS_MODULES.'colors.css');
		}
	}

	public static function load_module_self_style($slug, $css, $alter_url = false,$media='all') {
		if (Themify_Builder::$frontedit_active === false) {
			$key = $slug . '_' . str_replace('/', '_', $css);
			if ($alter_url === false) {
				$alter_url = THEMIFY_BUILDER_CSS_MODULES . $slug . '_styles/' . $css;
			}
			self::loadCssModules($key, $alter_url . '.css',THEMIFY_VERSION,$media);
		}
	}

	public static function loadCssModules($key, $url, $v=THEMIFY_VERSION, $media = 'all') {
            if(!is_admin() || themify_is_ajax()){
		$key='tb_' . $key;
		themify_enque_style($key, $url, null, $v, $media);
		Themify_Enqueue_Assets::addLocalization('done', $key, true);
            }
	}

	public static function getTranslate($data) {//need later
	}


	public static function check_plugins_compatible() {//check compatible of plugins
		if (isset($_GET['page']) && $_GET['page'] === 'themify-license') {
			return;
		}
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		$plugin_root = WP_PLUGIN_DIR;
		$needUpdate = false;
		$hasUpdater = $updaterUrl = null;
		$_messages = array();
		$fw = THEMIFY_VERSION;
		$dependceFW = array('announcement-bar', 'themify-audio-dock', 'themify-builder-pro', 'themify-icons', 'themify-shortcodes', 'themify-popup', 'themify-portfolio-post', 'themify-event-post', 'themify-store-locator', 'themify-tiles');
		foreach ($plugins as $k => &$p) {
			if (isset($p['Author']) && $p['Author'] === 'Themify') {
				$slug = dirname($k);
				if (strpos($slug, 'builder-') === 0 || $slug === 'themify-updater' || in_array($slug, $dependceFW, true)) {
					if ($slug === 'themify-updater') {
						if ($hasUpdater === null) {
							$hasUpdater = is_plugin_active($k);
							$updaterUrl = $k;
						}
					} else {
						if (!isset($p['Compatibility'])) {
							$data = get_file_data($plugin_root . '/' . $k, array('v' => 'Compatibility'), false);
							$v = $p['Compatibility'] = $data['v'];
							$needUpdate = true;
						} else {
							$v = $p['Compatibility'];
						}
						$up = '';
						if (!$v) { // Compatibility header missing, plugin is older than FW 5.0.0 update
                                                    $up = 'plugin';
						}
                                                elseif ($v && version_compare($v, $fw, '>')){ // plugin requires a higher version of FW

                                                    $up = 'theme';
						}
						if ($up !== '') {
							if (!isset($_messages[$up])) {
								$_messages[$up] = array();
							}
							$_messages[$up][] = $p['Name'];
						}
					}
				}
			}
		}
		if ($needUpdate === true) {
			wp_cache_set('plugins', $plugins, 'plugins');
		}
		if ($hasUpdater === false && $updaterUrl !== null && !empty($_GET['tf-activate-updater'])) {
			$tab = !empty($_messages['theme']) ? 1 : 2;
			$hasUpdater = activate_plugins($updaterUrl, add_query_arg(array('page' => 'themify-license', 'promotion' => $tab), admin_url()));
		}
		unset($needUpdate, $plugins, $dependceFW);

		if (!empty($_messages)) {
			foreach ($_messages as $k => $msg):
				?>
				<div class="notice notice-error tf_compatible_erros tf_<?php echo $k ?>_erros">
					<p><strong><?php echo $k === 'plugin' ? __('The following plugin(s) are not compatible with the activated theme. Please update your plugins:', 'themify') : __('Please update your activated Themify theme or Builder plugin. The following plugin(s) are incompatible:', 'themify'); ?></strong></p>
					<p><?php echo implode(', ', $msg); ?></p>
					<p>
				<?php if ($hasUpdater === true): ?>
					<?php $tab = $k === 'plugin' ? 2 : 1; ?>
							<a role="button" class="button button-primary" href="<?php echo add_query_arg(array('page' => 'themify-license', 'promotion' => $tab), admin_url()) ?>"><?php _e('Update them', 'themify') ?></a>
				<?php elseif ($hasUpdater === false): ?>
					<?php printf(__('%s', 'themify'), '<a role="button" class="button" href="' . add_query_arg(array('tf-activate-updater' => 1)) . '">' . __('Activate Themify Updater', 'themify') . '</a>') ?></a>
				<?php else: ?>
					<?php printf(__('Install %s plugin to auto update them.', 'themify'), '<a href="' . add_query_arg(array('page' => 'themify-install-plugins'), admin_url('admin.php')) . '">' . __('Themify Updater', 'themify') . '</a>') ?></a>
				<?php endif; ?>
					</p>
				</div>
				<?php
			endforeach;
		}
	}

	public static function get_heading_tags() {//check compatible of plugins
		$options = array();
		for ($i = 1; $i <= 6; ++$i) {
			$options['h' . $i] = 'H' . $i;
		}
		return $options;
	}

	/**
	 * Checks if Builder is disabled for a given post type
	 *
	 * @return bool
	 */
	public static function is_builder_disabled_for_post_type( $post_type ) {
		static $cache =array();
		if ( ! isset( $cache[ $post_type ] ) ) {
			$cache[ $post_type ] = themify_builder_check( 'setting-page_builder_disable_' . $post_type, 'builder_disable_tb_' . $post_type );
		}

		return $cache[ $post_type ];
	}

	/**
	 * Parses the settings from a module and applies Advanced Query settings on the $query_arg
	 *
	 * @return void
	 */
	public static function parse_query_filter( $module_settings, &$query_args ) {
		if ( ! empty( $module_settings['query_date_to'] ) ) {
			$query_args['date_query']['inclusive'] = true;
			$query_args['date_query']['before'] = $module_settings['query_date_to'];
		}
		if ( ! empty( $module_settings['query_date_from'] ) ) {
			$query_args['date_query']['inclusive'] = true;
			$query_args['date_query']['after'] = $module_settings['query_date_from'];
		}

		if ( ! empty( $module_settings['query_authors'] ) ) {
			$authors_ids = [];
			$authors = array_map( 'trim', explode( ',', $module_settings['query_authors'] ) );
			foreach ( $authors as $author ) {
				if ( is_numeric( $author ) ) {
					$authors_ids[] = (int) $author;
				}
                                elseif ( $user = get_user_by( 'login', $author ) ) {
                                    $authors_ids[] = $user->ID;
				}
			}
			if ( ! empty( $authors_ids ) ) {
				$query_args['author__in'] = $authors_ids;
			}
		}

		if ( ! empty( $module_settings['query_cf_key'] ) ) {
			$compare = empty( $module_settings['query_cf_c'] ) ? 'LIKE' : $module_settings['query_cf_c'];
			$query_args['meta_key'] = $module_settings['query_cf_key'];
			$query_args['meta_compare'] = $compare;
			if ( $compare !== 'NOT EXISTS' && $compare !== 'EXISTS' && ! empty( $module_settings['query_cf_value'] ) ) {
				$query_args['meta_value'] = $module_settings['query_cf_value'];
			}
		}
	}

	/**
	 * Setup Hook Content feature for the module
	 *
	 * @return void
	 */
	public static function hook_content_start( $settings ) {
	    if (!empty( $settings['hook_content'] ) ) {
		foreach ( $settings['hook_content'] as $hook ) {
                    if (!empty( $hook['c'] ) ) {
                        if(!isset(self::$hook_contents[ $hook['h'] ])){
                            self::$hook_contents[ $hook['h'] ]=array();
			}
			self::$hook_contents[ $hook['h'] ][] = $hook['c'];
			add_action( $hook['h'], array( __CLASS__, 'hook_content_output' ) );
		    }
		}
	    }
	}

	/**
	 * Remove hooks added by self::hook_content_start and reset cache
	 *
	 * @return void
	 */
	public static function hook_content_end( $settings ) {
		self::$hook_contents = null;
		if (!empty( $settings['hook_content'] ) ) {
		    foreach ( $settings['hook_content'] as $hook ) {
			if (!empty( $hook['c'] ) ) {
			    remove_action( $hook['h'], array( __CLASS__, 'hook_content_output' ) );
			}
		    }
		}
	}

	/**
	 * Display the contents of a hook added in Post modules
	 *
	 * @return void
	 */
	public static function hook_content_output() {
		$current_filter = current_filter();
		if ( isset( self::$hook_contents[ $current_filter ] ) ) {
			foreach ( self::$hook_contents[ $current_filter ] as $content ) {
				echo '<!-- post hook: ' , $current_filter , ' -->' , do_shortcode( $content ) , '<!-- /post hook: ' , $current_filter , ' -->';
			}
		}
	}


        /**
	 * Returns an array containing paths to different assets loade by Builder editor
	 *
	 * @return array
	 */
	public static function get_paths() {
            $themeLayoutsPath=get_parent_theme_file_path('builder-layouts/layouts.php');
            $arr= array(
                    // Pre-designed layouts
                    'predesigned' => array(
                        'title'=>__( 'Pre-designed', 'themify' ),
                        'url'=>'https://themify.me/public-api/builder-layouts/index.json',
			// URL to file containing Builder data for layout {SLUG}
			'single' => 'https://themify.me/public-api/builder-layouts/{SLUG}.txt',
                    ),
                    // Pre-designed rows
                    'rows_index' => 'https://themify.me/public-api/predesigned-rows/index.json',
                    // row template
                    'row_template' => 'https://themify.me/public-api/predesigned-rows/{SLUG}.txt'
		);
            if(is_file($themeLayoutsPath)){
                $themeLayouts=include $themeLayoutsPath;
                $arr['theme']=array(
                    'title'=>__( 'Theme', 'themify' ),
                    'data'=>$themeLayouts
                );
            }
            return $arr;
        }

        public static function getReCaptchaOption($name,$default=''){
            if($name==='version'){
                $contact_key='recapthca_version';
                $builder_key='builder_settings_recaptcha_version';
                $tf_name='setting-recaptcha_version';
            }
            elseif($name==='public_key'){
                $contact_key='recapthca_public_key';
                $builder_key='builder_settings_recaptcha_site_key';
                $tf_name='setting-recaptcha_site_key';
            }
            elseif($name==='private_key'){
                $contact_key='recapthca_private_key';
                $builder_key='builder_settings_recaptcha_secret_key';
                $tf_name='setting-recaptcha_secret_key';
            }
            if(isset($tf_name)){
                $val=themify_builder_get($tf_name, $builder_key,true );
                if(!empty($val)){
                    return $val;
                }
            }
            $options = class_exists('Builder_Contact')?get_option('builder_contact'):array();
            return isset($options[$contact_key]) ? $options[$contact_key] : $default;
        }



	//deprecated functions


	/**
	 * Set Pre-built Layout version
	 */
	public static function set_current_layouts_version($version) {//deprecated
		delete_transient(self::LAYOUT_NAME);
		return Themify_Storage::set(self::LAYOUT_NAME,$version);
	}

	/**
	 * Get current Pre-built Layout version
	 */
	public static function get_current_layouts_version() {//deprecated
		delete_transient(self::LAYOUT_NAME);
		$current_layouts_version = Themify_Storage::get(self::LAYOUT_NAME);
		if (false === $current_layouts_version) {
			self::set_current_layouts_version('0');
			$current_layouts_version = '0';
		}
		return $current_layouts_version;
	}

	/**
	 * Check whether layout is pre-built layout or custom
	 */
	public static function is_prebuilt_layout($id) {//deprecated
		$protected = get_post_meta($id, '_themify_builder_prebuilt_layout', true);
		return isset($protected) && 'yes' === $protected;
	}


	public static function get_images_from_gallery_shortcode($shortcode) {//deprecated from 2020.06.02,instead of use themify_get_gallery_shortcode
		return themify_get_gallery_shortcode($shortcode);
	}


	public static function get_icon($icon) {//deprecated
		return $icon;
	}


	public static function register_directory($context, $path) {//deprecated use add_module
            if($context==='modules'){

                self::add_module($path);
            }
	}

	public static function remove_cache($post_id, $tag = false, array $args = array()) {//deprecated
		//TFCache::remove_cache($tag, $post_id, $args);
	}


	public static function register_module($module_class) {//deprecated
		$instance = new $module_class();
		self::$modules[$instance->slug] = $instance;
	}


	public static function hasAccess() {//deprecated
            return  Themify_Access_Role::check_access_backend();
        }

	public static function localize_js($object_name, $l10n) {//deprecated
		foreach ((array) $l10n as $key => $value) {
			if (is_scalar($value)) {
				$l10n[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
			}
		}
		$l10n = apply_filters("tb_localize_js_{$object_name}", $l10n);

		return $l10n ? "var $object_name = " . wp_json_encode($l10n) . ';' : '';
	}
}
