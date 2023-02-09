<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Layout Part
 * Description: Layout Part Module
 */

class TB_Layout_Part_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        parent::__construct('layout-part');
    }
    
    public function get_name(){
        return __('Layout Part', 'themify');
    }
    
    public function get_icon(){
	return 'layout';
    }
    
    public function get_assets() {
	return array(
	    'css'=>1
	);
    }
    public function get_options() {
        return array(
            array(
                'id' => 'mod_title_layout_part',
                'type' => 'title'
            ),
            array(
                'id' => 'selected_layout_part',
                'type' => 'layoutPart',
                'label' => __('Layout Part', 'themify'),
		'required' => array(
		    'message' => __("Please select a Layout Part. If you don't have any, add a new Layout Part", 'themify')
		),
		'add_url'=>add_query_arg('post_type', Themify_Builder_Layouts::LAYOUT_PART_SLUG, admin_url('post-new.php')),
		'edit_url'=>add_query_arg('post_type', Themify_Builder_Layouts::LAYOUT_PART_SLUG, admin_url('edit.php'))
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
						self::get_image('', 'b_i','bg_c','b_r','b_p', '')
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
						self::get_font_family('', 'f_f_g'),
						self::get_color_type('','','f_c_t_g', 'f_c_g', 'f_g_c_g'),
						self::get_font_size('', 'f_s_g', '', ''),
						self::get_line_height('', 'l_h_g'),
						self::get_letter_spacing('', 'l_s_s'),
						self::get_text_align('', 't_a_g'),
						self::get_text_transform('', 't_t_g'),
						self::get_font_style('', 'f_st_g', 'f_w_g'),
						self::get_text_decoration('', 't_d_g'),
						self::get_text_shadow('', 't_sh_g'),
					)
					),
					'h' => array(
					'options' => array(
						self::get_font_family('', 'f_f_g', 'h'),
						self::get_color_type('','','f_c_t_g_h', 'f_c_g_h', 'f_g_c_g_h'),
						self::get_font_size('', 'f_s_g_h', '', 'h'),
						self::get_font_style('', 'f_st_g', 'f_w_g', 'h'),
						self::get_text_decoration('', 't_d_g', 'h'),
						self::get_text_shadow('','t_sh_g','h'),
					)
					)
				))
			)),
			// Link
			self::get_expand('l', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' a', 'l_c_l'),
						self::get_text_decoration(' a', 't_d_g_l')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' a', 'l_c_l', null, null, 'hover'),
						self::get_text_decoration(' a', 't_d_g_l', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding('','p_g','',true)
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding('', 'p_g', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin('','m_g','',true)
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin('', 'm_g', 'h')
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border('', 'b_g')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border('', 'b_g', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius('', 'r_c_g')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('', 'r_c_g', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow('', 'sh_g')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('', 'sh_g', 'h')
						)
					)
				))
			))
		);
        return array(
            'type' => 'tabs',
            'options' => array(
				'g' => array(
					'label' => __('General', 'themify'),
					'options' => $general
				),
                'm_t' => array(
                    'options' => $this->module_title_custom_style()
                )
            )
        );
    }


    public function get_animation() {
        return false;
    }

}

new TB_Layout_Part_Module();
