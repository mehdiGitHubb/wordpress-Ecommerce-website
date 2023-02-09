<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Overlay Content
 * Description: Overlay Content Module
 */
class TB_Overlay_Content_Module extends Themify_Builder_Component_Module{
    
	public function __construct() {
            parent::__construct('overlay-content');
	}
	
        public function get_name(){
            return __('Overlay Content', 'themify');
        }
        
	public function get_icon(){
	    return 'new-window';
	}
	
	
	public function get_assets() {
            return array(
                'css'=>1,
                'js'=>1
            );
        }
	
	public function get_options() {
		return array(
			array(
				'id' => 'selected_layout_part',
				'type' => 'layoutPart',
				'label' => __( 'Layout Part', 'themify' ),
				'required' => array(
					'message' => __( "Please select a Layout Part. If you don't have any, add a new Layout Part", 'themify' )
				),
				'add_url' => add_query_arg('post_type', Themify_Builder_Layouts::LAYOUT_PART_SLUG, admin_url('post-new.php')),
				'edit_url' => add_query_arg('post_type', Themify_Builder_Layouts::LAYOUT_PART_SLUG, admin_url('edit.php'))
			),
			array(
				'type' => 'multi',
				'label' => __('Overlay Dimension', 'themify'),
				'options' => array(
					array(
						'id' => 'overlay_width',
						'label' => 'w',
						'type' => 'range',
						'units' => array(
							'%' => '',
							'vw' => '',
							'px' => array(
								'max' => 5000
							)
						)
					),
					array(
						'id' => 'overlay_height',
						'label' => 'ht',
						'type' => 'range',
						'units' => array(
							'%' =>'',
							'vh' =>'',
							'px' => array(
								'max' => 5000
							)
						)
					)
				)
			),
			array(
				'id' => 'overlay_type',
				'type' => 'radio',
				'label' => __( 'Overlay Style', 'themify' ),
				'options' => array(
					array('value' => 'overlay', 'name' => __('Overlay', 'themify')),
					array('value' => 'expandable', 'name' => __('Expandable', 'themify'))
				),
				'option_js' => true,
				'wrap_class' => 'tb_group_element_overlay tb_group_element_expandable'
			),
			array(
				'id' => 'style',
				'label' => '',
				'type' => 'select',
				'options' => array(
					'overlay' => __( 'Fade In', 'themify' ),
					'slide_down' => __( 'Slide Down', 'themify' ),
					'slide_left' => __( 'Slide Left', 'themify' ),
					'slide_right' => __( 'Slide Right', 'themify' ),
					'slide_up' => __( 'Slide Up', 'themify' )
				),
				'wrap_class' => 'tb_group_element_overlay'
			),
			array(
				'id' => 'expand_mode',
				'label' => __( 'Open As', 'themify' ),
				'type' => 'select',
				'options' => array(
					'overlap' => __( 'Overlap', 'themify' ),
					'below' => __( 'Below', 'themify' )
				),
				'wrap_class' => 'tb_group_element_expandable'
			),
            array(
                'id' => 'icon',
                'type' => 'icon',
                'label' => __('Icon', 'themify'),
                'class' => 'large'
            ),
			array(
				'id' => 'icon_title',
				'type' => 'text',
				'class' => 'large',
				'label' => __('Icon Text', 'themify'),
				'control' => array(
				    'selector' => '.tb_ov_co_icon_title'
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'add_css_layout_part' ),
		);
	}
	public function get_styling() {
		$general = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_image()
						)
					),
					'h' => array(
						'options' => array(
							self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
						)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_font_family(' .tb_ov_co_icon_wrapper', 'g_f_f'),
						self::get_color(' .tb_ov_co_icon_wrapper', 'g_c'),
						self::get_font_size(' .tb_ov_co_icon_wrapper', 'g_f_s'),
						self::get_line_height(' .tb_ov_co_icon_wrapper', 'g_l_h'),
						self::get_letter_spacing(' .tb_ov_co_icon_wrapper', 'g_l_s'),
						self::get_text_align('', 'g_t_a'),
						self::get_text_transform(' .tb_ov_co_icon_wrapper', 'g_t_t'),
						self::get_font_style(' .tb_ov_co_icon_wrapper', 'g_f_st', 'g_f_w'),
						self::get_text_shadow(' .tb_ov_co_icon_title', 'g_t_sh'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family(' .tb_ov_co_icon_wrapper:hover', 'g_f_f', 'h'),
						self::get_color(' .tb_ov_co_icon_wrapper:hover', 'g_c_h', null, null, ''),
						self::get_font_size(' .tb_ov_co_icon_wrapper:hover', '_g_f_s_h', '', ''),
						self::get_font_style(' .tb_ov_co_icon_wrapper:hover', 'g_f_st_h', 'g_f_w_h'),
						self::get_text_shadow(' .tb_ov_co_icon_title:hover', 'g_t_sh_h'),
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding('', 'p', 'h')
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
							self::get_border()
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
				self::get_width('', 'g_w'),
			)),
			// Height & Min Height
			self::get_expand('ht', array(
					self::get_height(),
					self::get_min_height(),
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
			self::get_expand('po', array(
				self::get_css_position(),
			)),
			// Display
			self::get_expand('disp', self::get_display())
		);
		$overlay = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_oc_overlay_layer', 'o_b_c', 'bg_c', 'background-color'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_oc_overlay_layer', 'o_b_c_h', 'bg_c', 'background-color','hover')
						)
					)
				))
			)),
		);

		$container = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_image( array(' .tb_oc_overlay',' .tb_overlay_content_lp',), 'ctr_b_i', 'o_bg_c', '', 'o_b_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image(' .tb_oc_overlay, .tb_overlay_content_lp', 'ctr_b_i_h','o_bg_c_h','','o_b_p_h', 'h')
						)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_padding(' .tb_oc_overlay', 'ctr_p')
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding(' .tb_oc_overlay', 'ctr_p_h', 'h')
						)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_margin(' .tb_oc_overlay', 'ctr_m')
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin(' .tb_oc_overlay', 'ctr_m', 'h')
						)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border(' .tb_oc_overlay', 'ctr_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border(' .tb_oc_overlay', 'ctr_b', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tb_oc_overlay', 'ctr_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tb_oc_overlay', 'ctr_sh', 'h')
						)
					)
				))
			)),
		);

		$burger_icon = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_ov_co_icon_outer', 'bi_b_c', 'bg_c', 'background-color'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_ov_co_icon_outer', 'bi_b_c_h', 'bg_c', 'background-color','hover')
						)
					)
				))
			)),
			// Color
			self::get_expand('c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_ov_co_icon_outer', 'bi_c'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_ov_co_icon_outer:hover', 'bi_c_h', null, null, 'h'),
						)
					)
				))
			)),
			// Size
			self::get_expand('Size', array(
				self::get_width(' .tb_ov_co_icon', 'bi_w'),
				self::get_height(' .tb_ov_co_icon','bi_h'),
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border(' .tb_ov_co_icon_outer', 'bi_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border(' .tb_ov_co_icon', 'bi_b', 'h')
						)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_padding(' .tb_ov_co_icon_outer', 'bi_p'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding(' .tb_ov_co_icon_outer', 'bi_p', 'h')
						)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_margin(' .tb_ov_co_icon_outer', 'bi_m'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin(' .tb_ov_co_icon_outer', 'bi_m', 'h')
						)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tb_ov_co_icon_outer', 'bi_r_c'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tb_ov_co_icon_outer', 'bi_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tb_ov_co_icon_outer', 'bi_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tb_ov_co_icon_outer', 'bi_sh', 'h')
						)
					)
				))
			)),
		);

		$close_icon = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_ov_close', 'c_i_b_c', 'bg_c', 'background-color'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_ov_close', 'c_i_b_c_h', 'bg_c', 'background-color','hover')
						)
					)
				))
			)),
			// Color
			self::get_expand('c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_ov_close', 'c_i_c'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_ov_close:hover', 'c_i_c_h', null, null, 'h'),
						)
					)
				))
			)),
			// Size
			self::get_expand('Size', array(
				self::get_width(' .tb_ov_close_inner', 'c_i_w'),
				self::get_height(' .tb_ov_close_inner','c_i_h'),
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_padding(' .tb_ov_close', 'c_i_p'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding(' .tb_ov_close', 'c_i_p', 'h')
						)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tb_ov_close', 'c_i_r_c'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tb_ov_close', 'c_i_r_c', 'h')
						)
					)
				))
			)),
			// Position
			self::get_expand('po', array(
				self::get_css_position(' .tb_ov_close', 'c_i_css_p'),
			)),
		);
		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'ovl' => array(
					'label' => __('Overlay', 'themify'),
					'options' => $overlay
				),
				'ctn' => array(
					'label' => __('Container', 'themify'),
					'options' => $container
				),
				'bicon' => array(
					'label' => __('Burger Icon', 'themify'),
					'options' => $burger_icon
				),
				'cls' => array(
					'label' => __('Overlay Close Button', 'themify'),
					'options' => $close_icon
				)
			)
		);
	}
	
	public function get_animation() {
		return false;
	}
	public function get_live_default() {
		return array(
			'overlay_type' => 'overlay'
		);
	}
}

new TB_Overlay_Content_Module();
