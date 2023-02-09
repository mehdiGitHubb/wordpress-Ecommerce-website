<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Accordion
 * Description: Display Accordion content
 */

class TB_Accordion_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('accordion');
    }
    
    public function get_name(){
	return __('Accordion', 'themify');
    }
    
    public function get_icon(){
	return 'layout-accordion-merged';
    }
    
    public function get_title($module) {
	return isset($module['mod_settings']['mod_title_accordion']) ? wp_trim_words($module['mod_settings']['mod_title_accordion'], 100) : '';
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
			'id' => 'mod_title_accordion',
			'type' => 'title'
			),
			array(
			'id' => 'content_accordion',
			'type' => 'builder',
			'options' => array(
				array(
				'id' => 'title_accordion',
				'type' => 'text',
				'label' => __('Accordion Title', 'themify'),
				'class' => 'large',
				'control' => array(
					'selector' => '.accordion-title span'
				)
				),
				array(
				'id' => 'text_accordion',
				'type' => 'wp_editor',
				'rows' => 6,
				'control' => array(
					'selector' => '.tb_text_wrap'
				)
				),
				array(
				'id' => 'default_accordion',
				'type' => 'radio',
				'label' => __('Default', 'themify'),
				'options' => array(
					array('value' => 'closed','name' => __('Closed', 'themify')),
					array('value' => 'open','name' => __('Open', 'themify'))
				)
				)
			)
			),
            array(
                'id' => 'schema',
                'type' => 'toggle_switch',
                'label' => __( 'FAQ Schema', 'themify' ),
                'options' => array( 'on' => array( 'name' => 'yes' ) ),
                'help' => __( 'Only enable this schema data option if you are using Accordion for FAQ.', 'themify' ),
            ),
			array(
				'type' => 'group',
				'label' => __( 'Accordion Layout', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
					'id' => 'layout_accordion',
					'type' => 'layout',
					'mode' => 'sprite',
					'label' => __('Layout', 'themify'),
					'options' => array(
						array('img' => 'accordion_default', 'value' => 'default', 'label' => __('Continuous Panels', 'themify')),
						array('img' => 'accordion_separate', 'value' => 'separate', 'label' => __('Separated Panels', 'themify'))
					),
					'control'=>array(
						'classSelector'=>'.ui'
					)
					),
					array(
						'id' => 'expand_collapse_accordion',
						'type' => 'radio',
						'label' => __('Toggle Mode', 'themify'),
						'options' => array(
							array('value' => 'toggle','name' => __('Toggle', 'themify')),
							array('value' => 'accordion','name' => __('Accordion', 'themify') )
						),
						'new_line' => true,
						'help'=>__('Toggle means only clicked is toggled. Accordion will collapse all, but keep clicked item expanded.', 'themify')
					),
					array(
					    'id' => 'color_accordion',
					    'type' => 'layout',
					    'mode' => 'sprite',
					    'class' => 'tb_colors',
					    'label' =>'c',
					    'color' => true,
					    'transparent'=>true
					),
					array(
					    'id' => 'accordion_appearance_accordion',
					    'type' => 'checkbox',
					    'label' => __('Appearance', 'themify'),
					    'appearance' => true
					),
					array(
					'type' => 'multi',
					'label' => __('Icon', 'themify'),
					'options' => array(
						array(
						'id' => 'icon_accordion',
						'type' => 'icon',
						'label' => __('Closed Icon', 'themify')
						),
						array(
						'id' => 'icon_active_accordion',
						'type' => 'icon',
						'label' => __('Opened Icon', 'themify')
						),
					)
					),
                    array(
                        'id' => 'hashtag',
                        'type' => 'toggle_switch',
                        'label' => __('URL Hashtag', 'themify'),
                        'options'=>'simple'
                    ),
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_accordion' ),
		);
    }

    public function get_live_default() {
	return array(
	    'content_accordion' => array(
		array('title_accordion' => __('Accordion Title', 'themify'), 'text_accordion' => __('Accordion content', 'themify'))
	    )
	);
    }

    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(''),
			    self::get_color_type(array(' .tb_title_accordion',' .tb_text_wrap')),
			    self::get_font_size(''),
			    self::get_line_height(''),
			    self::get_letter_spacing(''),
			    self::get_text_align(''),
			    self::get_text_transform(''),
			    self::get_font_style(''),
			    self::get_text_decoration('', 't_d_r'),
			    self::get_text_shadow('')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('', 'f_f', 'h'),
			    self::get_color_type(array(':hover .tb_title_accordion',':hover .tb_text_wrap'),'','f_c_t_h', 'f_c_h', 'f_g_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
			    self::get_text_shadow('', 't_sh', 'h')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array(' .tb_title_accordion',' a'), 'link_color'),
			    self::get_text_decoration(' a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array(' .tb_title_accordion',' a'), 'link_color', null,null,'hover'),
			    self::get_text_decoration(' a', 't_d', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('')
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
			    self::get_margin('')
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
			    self::get_border('', 'border_accordion')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('', 'b_a', 'h')
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
				self::get_width('', 'w')
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
		// Display
		self::get_expand('disp', self::get_display())
	);

	$accordion_title = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image(array(' .ui.module-accordion .accordion-title', ' .ui.module-accordion>li'),'bg_i', 'background_color_title', 'bg_r', 'bg_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			     self::get_image(array(' .ui.module-accordion .accordion-title', ' .ui.module-accordion>li'),'bg_i', 'b_c_t', 'bg_r', 'bg_p','h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .ui.module-accordion .accordion-title', 'font_family_title'),
			    self::get_color(' .ui.module-accordion .tb_title_accordion', 'font_color_title'),
			    self::get_font_size(' .ui.module-accordion .accordion-title', 'font_size_title'),
			    self::get_line_height(' .ui.module-accordion .accordion-title', 'line_height_title'),
			    self::get_letter_spacing(' .ui.module-accordion .accordion-title', 'l_s_t'),
			    self::get_text_transform(' .ui.module-accordion .accordion-title', 't_t_t'),
			    self::get_font_style(' .ui.module-accordion .accordion-title', 'f_s_t', 'f_t_b'),
			    self::get_text_decoration(' .accordion-title', 't_d_t'),
			    self::get_text_shadow(' .accordion-title', 't_sh_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .ui.module-accordion .accordion-title', 'f_f_t', 'h'),
			    self::get_color(' .ui.module-accordion .accordion-title:hover .tb_title_accordion', 'f_c_t', null, null, ''),
			    self::get_font_size(' .ui.module-accordion .accordion-title', 'f_s_t', '', 'h'),
			    self::get_font_style(' .ui.module-accordion .accordion-title', 'f_st_t', 'f_t_b', 'h'),
			    self::get_text_decoration(' .accordion-title', 't_d_t', 'h'),
			    self::get_text_shadow(' .accordion-title', 't_sh_t', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .ui.module-accordion .accordion-title', 'b_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .ui.module-accordion .accordion-title', 'b_a_t', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .ui.module-accordion .accordion-title a', 'p_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .ui.module-accordion .accordion-title a', 'p_a_t', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion .accordion-title', 'r_c_t')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion .accordion-title', 'r_c_t', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(array(' .ui.module-accordion .accordion-title', ' .ui.module-accordion>li'), 'sh_t')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(array(' .ui.module-accordion .accordion-title', ' .ui.module-accordion>li'), 'sh_t', 'h')
					)
				)
			))
		))
	);
	$accordion_icon = array(
	    // Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .ui.module-accordion .accordion-title .accordion-active-icon', 'b_c_i', __('Open Background Color', 'themify'), 'background-color'),
					self::get_color(' .ui.module-accordion .accordion-title .accordion-icon', 'b_c_i_cd', __('Closed Background Color', 'themify'), 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .ui.module-accordion .accordion-title:hover .accordion-active-icon', 'b_c_i_h', __('Open Background Color', 'themify'), 'background-color'),
					self::get_color(' .ui.module-accordion .accordion-title:hover .accordion-icon', 'b_c_i_cd_h', __('Closed Background Color', 'themify'), 'background-color')
				)
				)
			))
	    )),
	    self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
					self::get_color(' .ui.module-accordion .accordion-title .accordion-active-icon', 'icon_color', __('Open Icon Color', 'themify')),
					self::get_color(' .ui.module-accordion .accordion-title .accordion-icon', 'icon_active_color', __('Closed Icon Color', 'themify')),
					self::get_font_size(' .ui.module-accordion .accordion-title i', 'icon_size', __('Icon Size', 'themify'))
					)
				),
				'h' => array(
					'options' => array(
					self::get_color(' .ui.module-accordion .accordion-title:hover .accordion-active-icon', 'i_c_h', __('Open Icon Color', 'themify'), null, ''),
					self::get_color(' .ui.module-accordion .accordion-title:hover .accordion-icon', 'i_a_c_h', __('Closed Icon Color', 'themify'), null,  ''),
					self::get_font_size(' .ui.module-accordion .accordion-title:hover i', 'i_s_h', __('Icon Size', 'themify'), null, '')
					)
				)
			)),
	    )),
	    // Padding
	    self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(array(' .ui.module-accordion .accordion-title .accordion-icon', ' .ui.module-accordion .accordion-title .accordion-active-icon'), 'p_a_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(array(' .ui.module-accordion .accordion-title:hover .accordion-icon', ' .ui.module-accordion .accordion-title:hover .accordion-active-icon'), 'p_a_i_h', '')
				)
				)
			))
	    )),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(array(' .ui.module-accordion .accordion-title .accordion-icon', ' .ui.module-accordion .accordion-title .accordion-active-icon'), 'm_a_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(array(' .ui.module-accordion .accordion-title:hover .accordion-icon', ' .ui.module-accordion .accordion-title:hover .accordion-active-icon'), 'm_a_i_h', '')
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(array(' .ui.module-accordion .accordion-title .accordion-icon', ' .ui.module-accordion .accordion-title .accordion-active-icon'), 'b_a_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(array(' .ui.module-accordion .accordion-title:hover .accordion-icon', ' .ui.module-accordion .accordion-title:hover .accordion-active-icon'), 'b_a_i_h', '')
				)
				)
			))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(array(' .ui.module-accordion .accordion-title .accordion-icon', ' .ui.module-accordion .accordion-title .accordion-active-icon'), 'r_c_a_in')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(array(' .ui.module-accordion .accordion-title:hover .accordion-icon', ' .ui.module-accordion .accordion-title:hover .accordion-active-icon'), 'r_c_a_ic_h', '')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(array(' .ui.module-accordion .accordion-title .accordion-icon', ' .ui.module-accordion .accordion-title .accordion-active-icon'), 'sh_a_in')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(array(' .ui.module-accordion .accordion-title:hover .accordion-icon', ' .ui.module-accordion .accordion-title:hover .accordion-active-icon'), 'sh_a_ic_h', '')
					)
				)
			))
		))
	);

	$accordion_content = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .ui.module-accordion .accordion-content', 'background_color_content', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .ui.module-accordion .accordion-content', 'b_c_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .ui.module-accordion .tb_text_wrap', 'font_family_content'),
			    self::get_color(' .ui.module-accordion .tb_text_wrap', 'font_color_content'),
			    self::get_font_size(' .ui.module-accordion .accordion-content', 'font_size_content'),
			    self::get_font_style(' .ui.module-accordion .tb_text_wrap', 'f_fs_c', 'f_fw_c'),
			    self::get_line_height(' .ui.module-accordion .accordion-content', 'line_height_content'),
			    self::get_text_shadow(' .ui.module-accordion .accordion-content', 't_sh_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .ui.module-accordion .tb_text_wrap', 'f_f_c', 'h'),
			    self::get_color(' .ui.module-accordion:hover .tb_text_wrap', 'f_c_c_h'),
			    self::get_font_size(' .ui.module-accordion .accordion-content', 'f_s_c', '', 'h'),
				self::get_font_style(' .ui.module-accordion .tb_text_wrap', 'f_fs_c', 'f_fw_c', 'h'),
			    self::get_text_shadow(' .ui.module-accordion .accordion-content', 't_sh_c', 'h')
			)
		    )
		))
	    )),
	    // Multi columns
	    self::get_expand('col', array(
		self::get_multi_columns_count(' .accordion-content')
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .ui.module-accordion .accordion-content', 'b_a_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .ui.module-accordion .accordion-content', 'b_a_c', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .ui.module-accordion .accordion-content', 'p_a_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .ui.module-accordion .accordion-content', 'p_a_c', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion .accordion-content', 'r_c_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion .accordion-content', 'r_c_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .ui.module-accordion .accordion-content', 'sh_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .ui.module-accordion .accordion-content', 'sh_c', 'h')
					)
				)
			))
		))
	);
	
	$accordion_container = array(
	    // Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .ui.module-accordion > li', 'b_c_ct', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .ui.module-accordion > li', 'b_c_ct', 'bg_c', 'background-color',  'h')
				)
				)
			))
	    )),
	    // Padding
	    self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .ui.module-accordion > li', 'p_ct')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .ui.module-accordion > li', 'p_ct', 'h')
				)
				)
			))
	    )),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .ui.module-accordion > li', 'm_ct')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .ui.module-accordion > li', 'm_ct', 'h')
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .ui.module-accordion > li', 'b_ct')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .ui.module-accordion > li', 'b_ct', 'h')
				)
				)
			))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion > li', 'r_c_ct')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .ui.module-accordion > li', 'r_c_ct', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .ui.module-accordion > li', 'sh_ct')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .ui.module-accordion > li', 'sh_ct', 'h')
					)
				)
			))
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
		'ct' => array(
		    'label' => __('Container', 'themify'),
		    'options' => $accordion_container
		),
		't' => array(
		    'label' => __('Title', 'themify'),
		    'options' => $accordion_title
		),
		'i' => array(
		    'label' => __('Icon', 'themify'),
		    'options' => $accordion_icon
		),
		'c' => array(
		    'label' => __('Content', 'themify'),
		    'options' => $accordion_content
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_accordion');
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.css_accordion }}" data-behavior="{{ data.expand_collapse_accordion }}">
	    <# if ( data.mod_title_accordion ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_accordion }}}<?php echo $module_args['after_title']; ?>
	    <# }

	    const color= undefined === data.color_accordion || 'default' == data.color_accordion ? 'tb_default_color' : data.color_accordion,
		tabId=data.cid.replace('tb_',''),
		arr=data.content_accordion || [];
	    #>
	    <ul class="module-<?php echo $this->slug; ?> ui {{ data.layout_accordion }} {{ color}} <# data.accordion_appearance_accordion? print( data.accordion_appearance_accordion.split('|').join(' ') ) : ''; #>">
		<# for(var i=0,len=arr.length;i<len;++i){
		    let item=arr[i],
			isOpen=item.default_accordion=='open'; #>
		    <li<# if ( isOpen ) { #> class="builder-accordion-active"<# } #>>
			<div class="accordion-title tf_rel">
				    <a href="#acc-{{ tabId }}-{{ i }}" aria-controls="acc-{{ data.cid }}-{{ i }}-content" aria-expanded="{{ isOpen }}">
					    <# if ( data.icon_accordion ) { #>
					    <i class="accordion-icon<# if ( isOpen ) { #> tf_hide<# } #>"><# print(api.Helper.getIcon(data.icon_accordion).outerHTML)#></i>
					    <# } 

					    if ( data.icon_active_accordion ) { #>
					    <i class="accordion-active-icon<# if ( !isOpen ) { #> tf_hide<# } #>"><# print(api.Helper.getIcon(data.icon_active_accordion).outerHTML)#></i>
					    <# } #>

					    <span class="tb_title_accordion tf_w" data-name="title_accordion" data-repeat="content_accordion" contenteditable="false">{{{ item.title_accordion }}}</span>
				    </a>
			</div>
			<div id="acc-{{ tabId }}-{{ i }}-content" data-id="acc-{{ tabId }}-{{ i }}" aria-hidden="{{!isOpen}}" class="accordion-content<# if ( !isOpen ) { #> tf_hide<# } #> tf_clearfix">
				<div contenteditable="false" data-name="text_accordion" data-repeat="content_accordion" data-hasEditor class="tb_text_wrap">{{{ item.text_accordion }}}</div>
			</div>
		    </li>
		<# } #>
	    </ul>
	</div>
	<?php
    }

    /**
     * Render plain content for static content.
     * 
     * @param array $module 
     * @return string
     */
    public function get_plain_content($module) {
	$mod_settings = wp_parse_args($module['mod_settings'], array(
	    'mod_title_accordion' => '',
	    'content_accordion' => array()
	));
	$text = '';

	if ('' !== $mod_settings['mod_title_accordion'])
	    $text = sprintf('<h3>%s</h3>', $mod_settings['mod_title_accordion']);

	if (!empty($mod_settings['content_accordion'])) {
	    $text .= '<ul>';
	    foreach ($mod_settings['content_accordion'] as $accordion) {
		$accordion = wp_parse_args($accordion, array(
		    'title_accordion' => '',
		    'text_accordion' => '',
		));
		$text .= sprintf('<li><h4>%s</h4>%s</li>', $accordion['title_accordion'], $accordion['text_accordion']);
	    }
	    $text .= '</ul>';
	}
	return $text;
    }

}

new TB_Accordion_Module();
