<?php

include THEME_DIR.'/admin/panel/settings.php';

/**
 * Theme Appearance Tab for Themify Custom Panel
 * @since 1.0.0
 * @return array
 */
function themify_theme_design_meta_box() {
    /**
    * Options for header design
    * @since 1.0.0
    * @var array
    */
    $header_design_options = themify_theme_header_design_options();

   /**
    * Options for footer design
    * @since 1.0.0
    * @var array
    */
    $footer_design_options = themify_theme_footer_design_options();
   
    $states=themify_ternary_states(array(
	'icon_no' => THEMIFY_URI . '/img/ddbtn-check.svg',
	'icon_yes' => THEMIFY_URI . '/img/ddbtn-cross.svg'
    ));
    $opt=themify_ternary_options();
    $background_mode =isset( $_GET['post'] )?get_post_meta( intval($_GET['post']),'background_mode', true ):false;
    if (!$background_mode) {
	$background_mode = 'fullcover';
    }
    return array(
	// Header Group
	array(
	    'name' => 'header_design_group',
	    'title' => __('Header', 'themify'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Header Design
		array(
		    'name' => 'header_design',
		    'title' => __('Header Design', 'themify'),
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $header_design_options,
		    'hide' => 'none header-left-pane header-minbar-left header-minbar-right header-boxed-content header-right-pane',
		    'default' => 'default',
		),
		// Sticky Header
		array(
		    'name' => 'fixed_header',
		    'title' => __('Sticky Header', 'themify'),
		    'description' => '',
		    'type' => 'radio',
		    'meta' => $opt,
		    'class' => 'hide-if none header-overlay header-slide-left header-slide-right header-boxed-content header-left-pane header-right-pane header-minbar-left header-minbar-right',
		    'default' => 'default',
		),
		// Header Elements
		array(
		    'name' => '_multi_header_elements',
		    'title' => __('Header Elements', 'themify'),
		    'description' => '',
		    'type' => 'multi',
		    'class' => 'hide-if none',
		    'meta' => array(
			'fields' => array(
			    // Show Site Logo
			    array(
				'name' => 'exclude_site_logo',
				'description' => '',
				'title' => __('Site Logo', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none header-menu-split',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Site Tagline
			    array(
				'name' => 'exclude_site_tagline',
				'description' => '',
				'title' => __('Site Tagline', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Search Button
			    array(
				'name' => 'exclude_search_button',
				'description' => '',
				'title' => __('Search Button', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Cart Icon
			    array(
				'name' => 'exclude_cart',
				'description' => '',
				'title' => __('Cart Icon', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
				'display_callback' => 'themify_is_woocommerce_active'
			    ),
			    // Show Wishlist Icon
			    array(
				'name' => 'exclude_wishlist',
				'description' => '',
				'title' => __('Wishlist Icon', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
				'display_callback' => 'themify_is_woocommerce_active'
			    ),
			    // Show Icon Menu Links
			    array(
				'name' => 'exclude_icon_menu_links',
				'description' => '',
				'title' => __('Icon Menu Links', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Menu Navigation
			    array(
				'name' => 'exclude_menu_navigation',
				'description' => '',
				'title' => __('Menu Navigation', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none header-menu-split',
				'after' => '<div class="clear"></div>',
				'enable_toggle' => true
			    ),
			    // Show Top Bar Widgets
			    array(
				'name' => 'exclude_top_bar_widgets',
				'description' => '',
				'title' => __('Top Bar Widgets', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			),
			'description' => '',
			'before' => '',
			'after' => '<div class="clear"></div>',
			'separator' => ''
		    )
		),
		array(
		    'name' => 'mobile_menu_styles',
		    'title' => __('Mobile Menu Style', 'themify'),
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => __('Default', 'themify'), 'value' => 'default'),
			array('name' => __('Boxed', 'themify'), 'value' => 'boxed'),
			array('name' => __('Dropdown', 'themify'), 'value' => 'dropdown'),
			array('name' => __('Fade Overlay', 'themify'), 'value' => 'fade-overlay'),
			array('name' => __('Fadein Down', 'themify'), 'value' => 'fadein-down'),
			array('name' => __('Flip Down', 'themify'), 'value' => 'flip-down'),
			array('name' => __('FlipIn Left', 'themify'), 'value' => 'flipin-left'),
			array('name' => __('FlipIn Right', 'themify'), 'value' => 'flipin-right'),
			array('name' => __('Flip from Left', 'themify'), 'value' => 'flip-from-left'),
			array('name' => __('Flip from Right', 'themify'), 'value' => 'flip-from-right'),
			array('name' => __('Flip from Top', 'themify'), 'value' => 'flip-from-top'),
			array('name' => __('Flip from Bottom', 'themify'), 'value' => 'flip-from-bottom'),
			array('name' => __('Morphing', 'themify'), 'value' => 'morphing'),
			array('name' => __('Overlay ZoomIn', 'themify'), 'value' => 'overlay-zoomin'),
			array('name' => __('Overlay ZoomIn Right', 'themify'), 'value' => 'overlay-zoomin-right'),
			array('name' => __('Rotate ZoomIn', 'themify'), 'value' => 'rotate-zoomin'),
			array('name' => __('Slide Down', 'themify'), 'value' => 'slide-down'),
			array('name' => __('SlideIn Left', 'themify'), 'value' => 'slidein-left'),
			array('name' => __('SlideIn Right', 'themify'), 'value' => 'slidein-right'),
			array('name' => __('Split', 'themify'), 'value' => 'split'),
			array('name' => __('Swing Left to Right', 'themify'), 'value' => 'swing-left-to-right'),
			array('name' => __('Swing Right to Left', 'themify'), 'value' => 'swing-right-to-left'),
			array('name' => __('Swing Top to Bottom', 'themify'), 'value' => 'swing-top-to-bottom'),
			array('name' => __('Swipe Left', 'themify'), 'value' => 'swipe-left'),
			array('name' => __('Swipe Right', 'themify'), 'value' => 'swipe-right'),
			array('name' => __('Zoom Down', 'themify'), 'value' => 'zoomdown'),
		    ),
		),
		// Cart Style
		array(
		    'name' => 'cart_style',
		    'title' => __('Ajax Cart Style', 'themify'),
		    'description' => '',
		    'type' => 'radio',
		    'show_title' => true,
		    'meta' => array(
			array(
			    'value' => '',
			    'name' => __('Default', 'themify'),
			    'selected' => true
			),
			array(
			    'value' => 'dropdown',
			    'name' => __('Dropdown cart', 'themify'),
			),
			array(
			    'value' => 'slide-out',
			    'name' => __('Slide-out cart', 'themify'),
			),
			array(
			    'value' => 'link_to_cart',
			    'name' => __('Link to cart page', 'themify'),
			),
		    ),
		    'display_callback' => 'themify_is_woocommerce_active',
		    'enable_toggle' => true,
		    'class' => 'hide-if none clear',
		    'default' => '',
		),
		// Header Wrap
		array(
		    'name' => 'header_wrap',
		    'title' => __('Header Background Type', 'themify'),
		    'description' => '',
		    'type' => 'radio',
		    'show_title' => true,
		    'meta' => array(
			array(
			    'value' => 'solid',
			    'name' => __('Solid Color/Image', 'themify'),
			    'selected' => true
			),
			array(
			    'value' => 'transparent',
			    'name' => __('Transparent Header', 'themify'),
			),
			array(
			    'value' => 'video',
			    'name' => __('Video Background', 'themify'),
			),
		    ),
		    'enable_toggle' => true,
		    'class' => 'hide-if none clear',
		    'default' => 'solid',
		),
		// Background Mode
		array(
		    'name' => 'background_mode',
		    'title' => __('Slider Mode', 'themify'),
		    'type' => 'radio',
		    'meta' => array(
			array('value' => 'fullcover', 'selected' => $background_mode === 'fullcover', 'name' => __('Full Cover', 'themify')),
			array('value' => 'best-fit', 'selected' => $background_mode === 'best-fit', 'name' => __('Best Fit', 'themify'))
		    ),
		    'enable_toggle' => true,
		    'toggle' => 'enable_toggle_child slider-toggle',
		    'class' => 'hide-if none',
		    'default' => 'fullcover',
		),
		// Background Position
		array(
		    'name' => 'background_position',
		    'title' => __('Slider Image Position', 'themify'),
		    'type' => 'dropdown',
		    'meta' => array(
			array('value' => '', 'name' => '', 'selected' => true),
			array('value' => 'left-top', 'name' => __('Left Top', 'themify')),
			array('value' => 'left-center', 'name' => __('Left Center', 'themify')),
			array('value' => 'left-bottom', 'name' => __('Left Bottom', 'themify')),
			array('value' => 'right-top', 'name' => __('Right Top', 'themify')),
			array('value' => 'right-center', 'name' => __('Right Center', 'themify')),
			array('value' => 'right-bottom', 'name' => __('Right Bottom', 'themify')),
			array('value' => 'center-top', 'name' => __('Center Top', 'themify')),
			array('value' => 'center-center', 'name' => __('Center Center', 'themify')),
			array('value' => 'center-bottom', 'name' => __('Center Bottom', 'themify'))
		    ),
		    'toggle' => 'fullcover-toggle slider-toggle',
		    'class' => 'hide-if none',
		),
		array(
		    'type' => 'multi',
		    'name' => '_video_select',
		    'title' => __('Header Video', 'themify'),
		    'meta' => array(
			'fields' => array(
			    // Video File
			    array(
				'name' => 'video_file',
				'title' => __('Video File', 'themify'),
				'description' => '',
				'type' => 'video',
				'meta' => array(),
			    ),
			),
			'description' => __('Video format: mp4. Note: video background does not play on some mobile devices, background image will be used as fallback.', 'themify'),
			'before' => '',
			'after' => '',
			'separator' => ''
		    ),
		    'toggle' => 'video-toggle',
		    'class' => 'hide-if none',
		),
		// Background Color
		array(
		    'name' => 'background_color',
		    'title' => __('Header Background', 'themify'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'toggle' => array('solid-toggle', 'slider-toggle', 'video-toggle'),
		    'class' => 'hide-if none',
		    'format' => 'rgba',
		),
		// Background image
		array(
		    'name' => 'background_image',
		    'title' => '',
		    'type' => 'image',
		    'description' => '',
		    'meta' => array(),
		    'before' => '',
		    'after' => '',
		    'toggle' => array('solid-toggle', 'video-toggle'),
		    'class' => 'hide-if none',
		),
		// Background repeat
		array(
		    'name' => 'background_repeat',
		    'title' => '',
		    'description' => __('Background Image Mode', 'themify'),
		    'type' => 'dropdown',
		    'meta' => array(
			array(
			    'value' => 'fullcover',
			    'name' => __('Fullcover', 'themify')
			),
			array(
			    'value' => 'repeat',
			    'name' => __('Repeat all', 'themify')
			),
			array(
			    'value' => 'no-repeat',
			    'name' => __('No repeat', 'themify')
			),
			array(
			    'value' => 'repeat-x',
			    'name' => __('Repeat horizontally', 'themify')
			),
			array(
			    'value' => 'repeat-y',
			    'name' => __('Repeat vertically', 'themify')
			),
		    ),
		    'toggle' => array('solid-toggle', 'video-toggle'),
		    'class' => 'hide-if none',
		    'default' => 'fullcover',
		),
		// Header wrap text color
		array(
		    'name' => 'headerwrap_text_color',
		    'title' => __('Header Text Color', 'themify'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'class' => 'hide-if none',
		    'format' => 'rgba',
		),
		// Header wrap link color
		array(
		    'name' => 'headerwrap_link_color',
		    'title' => __('Header Link Color', 'themify'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'class' => 'hide-if none',
		    'format' => 'rgba',
		)
	    ),
	    'default' => '',
	),
	// Footer Group
	array(
	    'name' => 'footer_design_group',
	    'title' => __('Footer', 'themify'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Footer Design
		array(
		    'name' => 'footer_design',
		    'title' => __('Footer Design', 'themify'),
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $footer_design_options,
		    'hide' => 'none',
		    'default' => 'default',
		),
		// Footer Elements
		array(
		    'name' => '_multi_footer_elements',
		    'title' => __('Footer Elements', 'themify'),
		    'description' => '',
		    'type' => 'multi',
		    'class' => 'hide-if none',
		    'meta' => array(
			'fields' => array(
			    // Show Site Logo
			    array(
				'name' => 'exclude_footer_site_logo',
				'description' => '',
				'title' => __('Site Logo', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Footer Widgets
			    array(
				'name' => 'exclude_footer_widgets',
				'description' => '',
				'title' => __('Footer Widgets', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Texts
			    array(
				'name' => 'exclude_footer_texts',
				'description' => '',
				'title' => __('Footer Text', 'themify'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Back to Top
			    array(
				'name' => 'exclude_footer_back',
				'description' => '',
				'title' => __('Back to Top Arrow', 'themify'),
				'type' => 'dropdownbutton',
				'states' =>$states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			),
			'description' => '',
			'before' => '',
			'after' => '<div class="clear"></div>',
			'separator' => ''
		    )
		),
		// Footer widget position
		array(
		    'name' => 'footer_widget_position',
		    'title' => __('Footer Widgets Position', 'themify'),
		    'class' => 'hide-if none',
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array(
			    'value' => '',
			    'name' => __('Default', 'themify')
			),
			array(
			    'value' => 'bottom',
			    'name' => __('After Footer Text', 'themify')
			),
			array(
			    'value' => 'top',
			    'name' => __('Before Footer Text', 'themify')
			)
		    ),
		)
	    ),
	    'default' => '',
	),
	// Image Filter Group
	array(
	    'name' => 'image_design_group',
	    'title' => __('Image Filter', 'themify'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Image Filter
		array(
		    'name' => 'imagefilter_options',
		    'title' => __('Image Filter', 'themify'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => '', 'value' => 'initial'),
			array('name' => __('None', 'themify'), 'value' => 'none'),
			array('name' => __('Grayscale', 'themify'), 'value' => 'grayscale'),
			array('name' => __('Sepia', 'themify'), 'value' => 'sepia'),
			array('name' => __('Blur', 'themify'), 'value' => 'blur'),
		    ),
		    'default' => 'initial',
		),
		// Image Hover Filter
		array(
		    'name' => 'imagefilter_options_hover',
		    'title' => __('Image Hover Filter', 'themify'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => '', 'value' => 'initial'),
			array('name' => __('None', 'themify'), 'value' => 'none'),
			array('name' => __('Grayscale', 'themify'), 'value' => 'grayscale'),
			array('name' => __('Sepia', 'themify'), 'value' => 'sepia'),
			array('name' => __('Blur', 'themify'), 'value' => 'blur'),
		    ),
		    'default' => 'initial',
		),
		// Image Filter Apply To
		array(
		    'name' => 'imagefilter_applyto',
		    'title' => __('Apply Filter To', 'themify'),
		    'description' => sprintf(__('Image filters can be set site-wide at <a href="%s" target="_blank">Themify > Settings > Theme Settings</a>', 'themify'), admin_url('admin.php?page=themify#setting-theme_settings')),
		    'type' => 'radio',
		    'meta' => array(
			array('value' => 'initial', 'name' => __('Theme Default', 'themify')),
			array('value' => 'all', 'name' => __('All Images', 'themify'), 'selected' => true),
			array('value' => 'featured-only', 'name' => __('Featured Images Only', 'themify'),)
		    ),
		    'default' => 'all',
		)
	    ),
	    'default' => ''
	)
    );
}

function themify_theme_setup_metaboxes($meta_boxes=array(), $post_type='all') {
    $supportedTypes=array('post', 'page','product');
    $dir=THEME_DIR . '/admin/pages/';
    if($post_type==='all'){
		add_action('themify_settings_panel_end', 'themify_admin_script_style');
		foreach($supportedTypes as $s){
			require_once( $dir . "$s.php" );
		}
		return $meta_boxes;
    }

	$post_type_object = get_post_type_object( $post_type );
    if(null!==$post_type_object){
        $excluded_post_types = array( 'tbuilder_layout', 'tbuilder_layout_part', 'themify_popup' );
        if ( $post_type_object->public && ! in_array( $post_type_object->name, $excluded_post_types ) ) {
            $meta_boxes = array_merge( array(
                array(
                    'name' => __( 'Page Appearance', 'themify' ),
                    'id' => "{$post_type}-theme-design",
                    'options' => themify_theme_design_meta_box(),
                    'pages' => $post_type,
                )
            ), $meta_boxes );
        }
    }

    if (!in_array($post_type,$supportedTypes , true)) {
		return $meta_boxes;
    }
    themify_admin_script_style();
    require_once( $dir . "$post_type.php" );
    $theme_metaboxes = call_user_func_array( "themify_theme_get_{$post_type}_metaboxes", array( array(), &$meta_boxes ) );

    return array_merge($theme_metaboxes, $meta_boxes);
}

if ( ! function_exists( 'themify_theme_setup_CPT_metaboxes' ) ) {
	/*
	* Enable Sticky Sidebar and other such metaboex for custom post types.
	*/
	function themify_theme_setup_CPT_metaboxes($metabox_opt) {
		$sticky_sidebar = array(
			'name' 		=> 'post_sticky_sidebar',
			'title' 		=> __('Sticky Sidebar', 'themify'),
			'description' => '',
			'type' 		=> 'dropdown',
			'show_title' => true,
			'enable_toggle' => true,
			'class'		=> 'hide-if sidebar-none',
			'meta'		=> array(
				array( 'value' => '', 'name' => '', 'selected' => true ),
				array( 'value' => 1, 'name' => __( 'Enable', 'themify' ) ),
				array( 'value' => 0, 'name' => __( 'Disable', 'themify' ) )
			)
		);
		array_splice( $metabox_opt, 1, 0, array($sticky_sidebar) );

		return $metabox_opt;
	}
}

/**
 * Register plugins required for the theme
 *
 *
 */
function themify_theme_register_required_plugins( $plugins = array()) {
	array_push($plugins ,
        array(
            'name'               => __( ' Themify Product Filter', 'themify' ),
            'slug'               => 'themify-wc-product-filter',
            'source'             => 'https://downloads.wordpress.org/plugin/themify-wc-product-filter.zip',
            'required'           => false,
            'version'            => '1.1.9',
            'force_activation'   => false,
            'force_deactivation' => false,
	    ),
		array(
			'name'               => __( ' Themify Popup', 'themify' ),
			'slug'               => ' themify-popup',
			'source'             => 'https://downloads.wordpress.org/plugin/themify-popup.zip',
			'required'           => false,
			'version'            => '1.1.4',
			'force_activation'   => false,
			'force_deactivation' => false,
		)
    );
	return $plugins;
}
if ( ! themify_is_woocommerce_active() ) {
    /**
    * Check in admin if Woocommerce is enabled and show a notice otherwise.
    * @since 1.3.0
    */
    function themify_check_ecommerce_environment_admin() {
	    $warning = 'installwoocommerce9';
	    if ( ! get_option( 'themify_warning_' . $warning ) ) {
		     wp_enqueue_script( 'themify-admin-warning', themify_enque(THEME_URI . '/admin/js/themify.admin.warning.js'), array('jquery'), Themify_Enqueue_Assets::$themeVersion, true );
		    echo '<div class="update-nag">'.__('Remember to install and activate WooCommerce plugin to enable the shop.', 'themify'). ' <a href="#" class="themify-close-warning" data-warning="' . $warning . '" data-nonce="' . wp_create_nonce( 'themify-warning' ) . '">' . __("Got it, don't remind me again.", 'themify') . '</a></div>';
	    }
    }
    add_action( 'admin_notices', 'themify_check_ecommerce_environment_admin' );
}
function themify_admin_script_style() {
    wp_enqueue_script('themify-admin-script', themify_enque(THEME_URI . '/admin/js/admin-script.js'),null,Themify_Enqueue_Assets::$themeVersion,true);
}


if ( ! function_exists( 'themify_dismiss_warning' ) ) {
	function themify_dismiss_warning() {
		check_ajax_referer( 'themify-warning', 'nonce' );
		$result = false;
		if ( isset( $_POST['warning'] ) ) {
			$result = update_option( 'themify_warning_' . $_POST['warning'], true );
		}
		if ( $result ) {
			echo 'true';
		} else {
			echo 'false';
		}
		die;
	}
	add_action( 'wp_ajax_themify_dismiss_warning', 'themify_dismiss_warning' );
}

/**
* Customize the skins list in the admin screen
*
* @since 1.0.0
*/
function themify_theme_skins_list( $skins ) {
	unset( $skins[0] ); // remove No Skin option

	// set Default as the first choice
	$default = $skins['default'];
	unset( $skins['default'] );

	return array_merge( array( 'default' => $default ), $skins );
}

/**
 * Allow updating bonus addons for Shoppe
 *
 * @since 1.0.2
 */
function themify_theme_bonus_addons_update( $match, $subs ) {
	$theme = wp_get_theme();
	$theme_name = ( is_child_theme() ) ? $theme->parent()->Name : $theme->display('Name');
	$theme_name = preg_replace( '/^Themify\s/', '', $theme_name );
	foreach ( $subs as $value ) {
		if ( ( stripos( $value['title'], $theme_name ) !== false || stripos( $value['title'], 'Standard Club' ) !== false ) && isset( $_POST['nicename_short'] ) && in_array( $_POST['nicename_short'], array( 'Slider Pro', 'Pricing Table', 'Maps Pro', 'Typewriter', 'Image Pro', 'Timeline', 'WooCommmerce', 'Contact', 'Counter', 'Progress Bar', 'Countdown', 'Audio' ),true ) ) {
			$match = 'true';
			break;
		}
	}

	return $match;
}
if(isset( $_GET['page'] ) && $_GET['page']==='themify'){
	add_theme_support( 'themify-skins-and-demos' );
	add_filter( 'themify_theme_skins', 'themify_theme_skins_list' ); 
    themify_theme_setup_metaboxes();
}
else{
    add_filter('themify_metabox/fields/themify-meta-boxes', 'themify_theme_setup_metaboxes', 10, 2);
	add_filter('themify_post_type_default_options', 'themify_theme_setup_CPT_metaboxes');
}

add_filter( 'themify_theme_required_plugins', 'themify_theme_register_required_plugins' );
add_filter( 'themify_builder_validate_login', 'themify_theme_bonus_addons_update', 10, 2 );
