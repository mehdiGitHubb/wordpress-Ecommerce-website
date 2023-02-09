<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Menu
 * Description: Display Custom Menu
 */

class TB_Menu_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('menu');
    }
    
    public function get_name(){
        return __('Menu', 'themify');
    }
    
    public function get_group() {
        return array('general','site');
    }
    
    public function get_icon(){
	return 'view-list';
    }
	
    public function get_assets() {
	return array(
            'css'=>1,
            'js'=>1
	);
    }
    public function get_title($module) {
	return isset($module['mod_settings']['custom_menu']) ? $module['mod_settings']['custom_menu'] : '';
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_menu',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_menu',
		'type' => 'layout',
		'label' => __('Menu Layout', 'themify'),
		'mode' => 'sprite',
		'options' => array(
		    array('img' => 'menu_bar', 'value' => 'menu-bar', 'label' => __('Menu Bar', 'themify')),
		    array('img' => 'menu_fullbar', 'value' => 'fullwidth', 'label' => __('Menu Fullbar', 'themify')),
		    array('img' => 'menu_vertical', 'value' => 'vertical', 'label' => __('Menu Vertical', 'themify'))
		),
            'binding' => array(
            'not_empty' => array(
                'hide' =>'accordion'
            ),
            'vertical' => array(
                'show' => 'accordion'
            )
        )
	    ),
        array(
            'id' => 'accordion',
            'label' =>  __('Accordion Style', 'themify'),
            'type' => 'toggle_switch',
            'options' => array(
                'on' => array('name'=>'allow_menu','value' =>'en'),
                'off' => array('name'=>'', 'value' =>'dis')
            )
        ),
	    array(
		'id' => 'custom_menu',
		'type' => 'select',
		'dataset'=>'menu',
		'description' => sprintf(__('Add more <a href="%s" target="_blank">%s</a>', 'themify'), admin_url('nav-menus.php'), __('menu', 'themify')),
		'label' => __('Custom Menu', 'themify'),
		'options'=>array()
	    ),
	    array(
		'id' => 'allow_menu_fallback',
		'label' => '',
		'type' => 'checkbox',
		'options' => array(
		    array('name' => 'allow_fallback', 'value' => __('List all pages as fallback', 'themify'))
		)
	    ),
	    array(
		'id' => 'allow_menu_breakpoint',
		'label' =>  __('Mobile Menu', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name'=>'allow_menu','value' =>'en'),
		    'off' => array('name'=>'', 'value' =>'dis')
		),
		'binding' => array(
			'checked' => array(
				'show' => array('menu_breakpoint', 'menu_slide_direction', 'mobile_menu_style')
			),
			'not_checked' => array(
				'hide' => array('menu_breakpoint', 'menu_slide_direction', 'mobile_menu_style')
			)
		)
	    ),
	    array(
			'id' => 'menu_breakpoint',
			'label' => '',
			'type' => 'number',
			'after' => __('Breakpoint (px)', 'themify'),
			'binding' => array(
				'empty' => array(
				'hide' =>'menu_slide_direction'
				),
				'not_empty' => array(
				'show' =>'menu_slide_direction'
				)
			),
			'wrap_class' => 'tb_checkbox_element_allow_menu'
	    ),
        array(
            'id'         => 'mobile_menu_style',
            'label' => '',
            'type' => 'select',
            'after'      => __( 'Style', 'themify' ),
            'options' => array(
                'slide'    => __( 'Slide', 'themify' ),
                'overlay'  => __( 'Overlay', 'themify' ),
                'dropdown' => __( 'Dropdown', 'themify' )
            ),
            'binding' => array(
                'slide' => array('show' => array('menu_slide_direction')),
                'overlay' => array('show' => array('menu_slide_direction')),
                'dropdown' => array('hide' => array('menu_slide_direction'))
            ),
            'wrap_class' => 'tb_checkbox_element_allow_menu'
        ),
	    array(
				'id'         => 'menu_slide_direction',
			'label' => '',
			'type' => 'select',
				'after'      => __( 'Slide Direction', 'themify' ),
			'options' => array(
					'right' => __( 'Right', 'themify' ),
					'left'  => __( 'Left', 'themify' )
			),
			'wrap_class' => 'tb_checkbox_element_allow_menu'
	    ),
	    array(
			'id' => 'tooltips',
			'type' => 'toggle_switch',
			'label' => __('Menu Tooltips', 'themify'),
			'default' => 'off',
			'options' => 'simple',
	    ),
	    array(
		'id' => 'color_menu',
		'type' => 'layout',
		'label' => 'c',
		'class' => 'tb_colors',
		'mode' => 'sprite',
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'according_style_menu',
		'type' => 'checkbox',
		'label' => __('Appearance', 'themify'),
		'appearance' => true
	    ),
	    array( 'type' => 'custom_css_id', 'custom_css' => 'css_menu' )
	);
    }

	public function get_live_default() {
		return array(
			'custom_menu' => '',
			'mobile_menu_style' => 'slide',
			'allow_menu_fallback' => 'allow_fallback',
		);
	}


    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .nav', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .nav', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .nav li'),
			    self::get_color(' .nav li a', 'font_color'),
			    self::get_font_size(' .nav li'),
			    self::get_line_height(' .nav li'),
			    self::get_letter_spacing(' .nav li'),
			    self::get_text_align('.module'),
			    self::get_text_transform(' .nav li'),
			    self::get_font_style(' .nav li'),
			    self::get_text_shadow(' .nav li'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .nav li', 'f_f', 'h'),
			    self::get_color(' .nav li a', 'f_c',  null, null, 'h'),
			    self::get_font_size(' .nav li', 'f_s', '', 'h'),
			    self::get_font_style(' .nav li:hover', 'f_st_h', 'f_w_h', 'h'),
			    self::get_text_shadow(' .nav li','t_sh','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('.module')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('.module', 'p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin()
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('', 'm', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('', 'b', 'h')
			)
		    )
		))
	    )),
		// Filter
		self::get_expand('f_l',
			array(
				self::get_tab(array(
					'n' => array(
						'options' => self::get_blend()

					),
					'h' => array(
						'options' => self::get_blend('', '', 'h')
					)
				))
			)
		),
	    // Width
	    self::get_expand('w', array(
			self::get_width(array('', ' .vertical'), 'w')
	    )),
				// Height & Min Height
				self::get_expand('ht', array(
						self::get_height(),
						self::get_min_height(),
						self::get_max_height()
					)
				),
		// Rounded Corners
		self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('', 'r_c', 'h')
						)
					)
				))
			)
		),
		// Shadow
		self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow()
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('', 'sh', 'h')
						)
					)
				))
			)
		),
		// Position
		self::get_expand('po', array( self::get_css_position())),
		// Display
		self::get_expand('disp', self::get_display())
	);

	$menu_links = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .nav > li > a', 'link_background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .nav > li > a', 'link_background_color', 'bg_c', 'background-color','hover')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.module .nav > li > a', 'link_color'),
			    self::get_text_decoration('.module .nav > li > a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.module .nav > li > a', 'link_color',null, null, 'hover'),
			    self::get_text_decoration('.module .nav > li > a', 't_d', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .nav > li > a', 'p_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .nav > li > a', 'p_m_l', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .nav > li > a', 'm_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .nav > li > a', 'm_m_l', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .nav > li > a', 'b_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .nav > li > a', 'b_m_l', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .nav > li > a', 'r_c_m_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .nav > li > a', 'r_c_m_l', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .nav > li > a', 'sh_m_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .nav > li > a', 'sh_m_l', 'h')
					)
				)
			))
		)),
	);

	$current_menu_links = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'current-links_background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.module .nav li.current_page_item > a:hover', '.module .nav li.current-menu-item > a:hover'), 'current-links_hover_background_color', 'bg_c', 'background-color')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'current-links_color'),
			    self::get_text_decoration(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'current-links_text_decoration')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.module li.current_page_item > a:hover', '.module li.current-menu-item > a:hover'), 'current-links_color_hover'),
			    self::get_text_decoration(array('.module li.current_page_item > a', '.module li.current-menu-item > a'), 'c-l_t_d','h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'b_m_c_l')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'b_m_c_l', 'h')
				)
				)
			))
	    )),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'sh_m_c_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(array('.module .nav li.current_page_item > a', '.module .nav li.current-menu-item > a'), 'sh_m_c_l', 'h')
					)
				)
			))
		)),
	);

	$menu_dropdown_links = array(
		// Container Background
	    self::get_expand('Container', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' li > ul', 'd_l_ctn_b_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' li > ul:hover', 'd_l_ctn_b_c_h', __('Background Hover', 'themify'), 'background-color')
				)
				)
			))
	    )),
		// Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' li > ul a', 'dropdown_links_background_color', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' li > ul a:hover', 'dropdown_links_hover_background_color', __('Background Hover', 'themify'), 'background-color')
				)
				)
			))
	    )),
	    // Font
	    self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .nav li > ul a', 'font_family_menu_dropdown_links'),
					self::get_color(' .nav li > ul a', 'dropdown_links_color'),
					self::get_font_size(' .nav li > ul a', 'font_size_menu_dropdown_links'),
					self::get_line_height(' .nav li > ul a', 'l_h_m_d_l'),
					self::get_letter_spacing(' .nav li > ul a', 'l_s_m_d_l'),
					self::get_text_align(' .nav li > ul a', 't_a_m_d_l'),
					self::get_text_transform(' .nav li > ul a', 't_t_m_d_l'),
					self::get_font_style(' .nav li > ul a', 'f_d_l', 'f_d_b'),
					self::get_text_decoration(' .nav li > ul a', 't_d_m_d_l'),
					self::get_text_shadow(' .nav li > ul a', 't_sh_l'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .nav li > ul a', 'f_f_m_d_l', 'h'),
					self::get_color(' .nav li > ul a:hover', 'dropdown_links_hover_color', __('Color Hover', 'themify')),
					self::get_font_size(' .nav li > ul a', 'f_s_m_d_l', '', null,null, 'h'),
					self::get_font_style(' .nav li > ul a', 'f_d_l', 'f_d_b', 'h'),
					self::get_text_decoration(' .nav li > ul a', 't_d_m_d_l', 'h'),
					self::get_text_shadow(' .nav li > ul a', 't_sh_l', 'h'),
				)
				)
			))
	    )),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' li > ul a', 'd_l_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' li > ul a', 'd_l_p_h', 'h')
				)
				)
			))
		)),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' li > ul a', 'd_l_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' li > ul a', 'd_l_m_h', 'h')
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' li > ul a', 'd_l_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' li > ul a', 'd_l_b_h', 'h')
				)
				)
			))
	    ))
	);

	$menu_mobile = array(
	    // Background
	    self::get_expand('Panel', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'mobile_menu_background_color', 'bg_c', 'background-color'),
					self::get_padding(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'p_m_m_ct'),
					self::get_border(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'b_m_m_ct'),
					self::get_box_shadow(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'sh_m_m_ct'),
					self::get_width(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'wh_m_m_ct')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'm_m_b_c', 'bg_c', 'background-color', null, 'h'),
					self::get_padding(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'p_m_m_ct', 'h'),
					self::get_border(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'b_m_m_ct', 'h'),
					self::get_box_shadow(array('.mobile-menu-module', '.mobile-menu-dropdown.module-menu-mobile-active .nav'), 'sh_m_m_ct', 'h')
				)
				)
			))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_f_m_m'),
			    self::get_color(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'm_c_m_m'),
			    self::get_font_size(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_s_m_m'),
			    self::get_line_height(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'l_h_m_m'),
			    self::get_letter_spacing(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'l_s_m_m'),
			    self::get_text_align(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_a_m_m'),
			    self::get_text_transform(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_t_m_m'),
			    self::get_font_style(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_sy_m_m', 'f_b_m_m'),
			    self::get_text_decoration(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_d_m_m'),
				self::get_text_shadow(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_sh_m'),
			    self::get_color(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'bg_c', 'background-color'),
				self::get_padding(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'p_m_m'),
				self::get_margin(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'm_m_m'),
				self::get_border(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'b_m_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_f_m_m', 'h'),
			    self::get_color(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'm_c_h_m_m',null, null, 'h'),
			    self::get_font_size(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_s_m_m', 'h'),
			    self::get_font_style(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'f_sy_m_m', 'f_b_m_m', 'h'),
			    self::get_text_decoration(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_d_m_m', 'h'),
				self::get_text_shadow(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 't_sh_m','h'),
				self::get_color(array('.mobile-menu-module li a:hover', '.mobile-menu-dropdown.module-menu-mobile-active li a:hover'), 'b_c_m_m_h', 'bg_c', 'background-color', null, 'h'),
				self::get_padding(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'p_m_m', 'h'),
				self::get_margin(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'm_m_m', 'h'),
				self::get_border(array('.mobile-menu-module li a', '.mobile-menu-dropdown.module-menu-mobile-active li a'), 'b_m_m', 'h')
			)
		    )
		))
	    )),
	    // current Menu Link
	    self::get_expand('Current Menu Link', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.mobile-menu-module li.current_page_item > a', '.mobile-menu-module li.current-menu-item > a'), 'mm_c_l_bg_c', 'bg_c', 'background-color'),
			    self::get_color(array('.mobile-menu-module li.current_page_item > a', '.mobile-menu-module li.current-menu-item > a'), 'mm_c_l_c'),
				self::get_border(array('.mobile-menu-module li.current_page_item > a', '.mobile-menu-module li.current-menu-item > a'), 'mm_c_l_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.mobile-menu-module li.current_page_item > a:hover', '.mobile-menu-module li.current-menu-item > a:hover'), 'mm_c_l_bg_c_h', 'bg_c', 'background-color'),
			    self::get_color(array('.mobile-menu-module li.current_page_item > a:hover', '.mobile-menu-module li.current-menu-item > a:hover'), 'mm_c_l_c_h',null, null, 'h'),
				self::get_border(array('.mobile-menu-module li.current_page_item > a:hover', '.mobile-menu-module li.current-menu-item > a:hover'), 'mm_c_l_b_h', 'h')
			)
		    )
		))
	    )),
	    // Overlay
	    self::get_expand('Overlay', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .body-overlay', 'b_c_m_m_o', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .body-overlay:hover', 'b_c_m_m_o', 'bg_c', 'background-color', null, 'h')
				)
				)
			))
	    )),
	    // Burger Icon
	    self::get_expand(__('Burger Icon', 'themify'), array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .menu-module-burger', 'b_c_m_m_i', 'bg_c', 'background-color'),
					self::get_color(' .menu-module-burger', 'c_m_m_i'),
					self::get_padding(' .menu-module-burger', 'p_m_m_i'),
					self::get_margin(' .menu-module-burger', 'm_m_m_i'),
					self::get_border(' .menu-module-burger', 'b_m_m_i'),
					self::get_width(array(' .menu-module-burger', ' .menu-module-burger-inner'), 'w_m_m_i'),
					self::get_height(' .menu-module-burger-inner', 'h_m_m_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .menu-module-burger:hover', 'b_c_m_m_i_h', 'bg_c', 'background-color', null, 'h'),
					self::get_color(' .menu-module-burger', 'c_m_m_i_h',null, null, 'h'),
					self::get_padding(' .menu-module-burger', 'p_m_m_i', 'h'),
					self::get_margin(' .menu-module-burger', 'm_m_m_i', 'h'),
					self::get_border(' .menu-module-burger', 'b_m_m_i', 'h')
				)
				)
			)),
		)),
	    // Close Button
	    self::get_expand(__('Close Button', 'themify'), array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.mobile-menu-module .menu-close', 'b_c_m_m_cb', 'bg_c', 'background-color'),
					self::get_color('.mobile-menu-module .menu-close', 'c_m_m_cb'),
					self::get_padding('.mobile-menu-module .menu-close', 'p_m_m_cb'),
					self::get_margin('.mobile-menu-module .menu-close', 'm_m_m_cb'),
					self::get_border('.mobile-menu-module .menu-close', 'b_m_m_cb'),
					self::get_width(array('.mobile-menu-module .menu-close'), 'w_m_m_cb'),
					self::get_height('.mobile-menu-module .menu-close', 'h_m_m_cb'),
					self::get_border_radius('.mobile-menu-module .menu-close', 'r_c_m_m_cb'),
					self::get_box_shadow('.mobile-menu-module .menu-close', 'sh_m_m_cb')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.mobile-menu-module .menu-close:hover', 'b_c_m_m_cb_h', 'bg_c', 'background-color', null, 'h'),
					self::get_color('.mobile-menu-module .menu-close', 'c_m_m_cb_h',null, null, 'h'),
					self::get_padding('.mobile-menu-module .menu-close', 'p_m_m_cb', 'h'),
					self::get_margin('.mobile-menu-module .menu-close', 'm_m_m_cb', 'h'),
					self::get_border('.mobile-menu-module .menu-close', 'b_m_m_cb', 'h'),
					self::get_border_radius('.mobile-menu-module .menu-close', 'r_c_m_m_cb', 'h'),
					self::get_box_shadow('.mobile-menu-module .menu-close', 'sh_m_m_cb', 'h')
				)
				)
			)),
		))
	);
	return array(
	    'type' => 'tabs',
	    'options' => array(
		'g' => array(
		    'options' => $general
		),
		'm_t' => array(
		    'options' => $this->module_title_custom_style()
		),
		'l' => array(
		    'label' => __('Menu Links', 'themify'),
		    'options' => $menu_links
		),
		'c' => array(
		    'label' => __('Current Links', 'themify'),
		    'options' => $current_menu_links
		),
		'dl' => array(
		    'label' => __('Dropdown Links', 'themify'),
		    'options' => $menu_dropdown_links
		),
		'm' => array(
		    'label' => __('Mobile Menu', 'themify'),
		    'options' => $menu_mobile
		)
	    )
	);
    }

}

new TB_Menu_Module();
