<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Text
 * Description: Display text content
 */

class TB_Text_Module extends Themify_Builder_Component_Module {

    public function __construct() { 
	parent::__construct('text');
    }
    
    public function get_name(){
        return __('Text', 'themify');
    }

    public function get_title($module) {
	return isset($module['mod_settings']['content_text']) ? wp_trim_words($module['mod_settings']['content_text'], 100) : '';
    }
	public function get_assets() {
		return array(
			'css'=>1
		);
    }
    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_text',
		'type' => 'title'
	    ),
	    array(
		'id' => 'content_text',
		'type' => 'wp_editor'
	    ),
	    array(
		'id' => 'text_drop_cap',
		'label' => __('Drop-Cap', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name'=>'dropcap','value' =>'en'),
		    'off' => array('name'=>'', 'value' =>'dis')
		)
	    ),
			array( 'type' => 'custom_css_id', 'custom_css' => 'add_css_text' ),
	);
    }

    public function get_live_default() {
	return array(
	    'content_text' => sprintf('<p>%s</p>',__('Text content', 'themify'))
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
			    self::get_font_family(' .tb_text_wrap'),
			    self::get_color_type(' .tb_text_wrap'),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(' .tb_text_wrap'),
			    self::get_text_decoration(' .tb_text_wrap', 'text_decoration_regular'),
			    self::get_text_shadow(' .tb_text_wrap'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(':hover .tb_text_wrap', 'f_f_h'),
			    self::get_color_type(':hover .tb_text_wrap','', 'f_c_t_h',  'f_c_h', 'f_g_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_font_style(':hover .tb_text_wrap', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .tb_text_wrap', 't_d_r', 'h'),
			    self::get_text_shadow(':hover .tb_text_wrap','t_sh','h'),
			)
		    )
		))
	    )),
	    // Paragraph
	    self::get_expand(__('Paragraph', 'themify'), array(
		self::get_heading_margin_multi_field('', 'p', 'top'),
		self::get_heading_margin_multi_field('', 'p', 'bottom')
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' a', 'link_color'),
			    self::get_text_decoration(' a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' a', 'link_color',null, null, 'hover'),
			    self::get_text_decoration(' a', 't_d', 'h')
			)
		    )
		))
	    )),
	    // Multi-column
	    self::get_expand('col', array(
		self::get_multi_columns_count()
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
		// Position
		self::get_expand('po', array( self::get_css_position())),
		// Display
		self::get_expand('disp', self::get_display())
	);

	$heading = array();

	for ($i = 1; $i <= 6; ++$i) {
	    $h = 'h' . $i;
	    $selector = $h;
	    if($i === 3){
		$selector.=':not(.module-title)';
	    }
	    $heading = array_merge($heading, array(
		self::get_expand($h.'_f', array(
		    self::get_tab(array(
			'n' => array(
			    'options' => array(
				self::get_font_family('.module .tb_text_wrap ' . $selector, 'font_family_' . $h),
				self::get_color_type('.module .tb_text_wrap ' .$selector,'','font_color_type_' . $h, 'font_color_' . $h, 'font_gradient_color_' . $h),
				self::get_font_size('.module ' . $h, 'font_size_' . $h),
				self::get_line_height('.module ' . $h, 'line_height_' . $h),
				self::get_letter_spacing('.module ' . $h, 'letter_spacing_' . $h),
				self::get_text_transform('.module ' . $h, 'text_transform_' . $h),
				self::get_font_style('.module .tb_text_wrap ' . $selector, 'font_style_' . $h, 'font_weight_' . $h),
				self::get_text_shadow('.module .tb_text_wrap ' .$selector, 't_sh' . $h),
				// Heading  Margin
				self::get_heading_margin_multi_field('.module', $h, 'top'),
				self::get_heading_margin_multi_field('.module', $h, 'bottom')
			    )
			),
			'h' => array(
			    'options' => array(
				self::get_font_family('.module:hover .tb_text_wrap ' . $selector, 'f_f_' . $h.'_h'),
				self::get_color_type('.module:hover .tb_text_wrap ' . $selector,'', 'f_c_t_' . $h.'_h', 'f_c_' . $h.'_h', 'f_g_c_' . $h.'_h'),
				self::get_font_size('.module ' . $h, 'f_s_' . $h, '', 'h'),
				self::get_font_style('.module:hover .tb_text_wrap ' . $selector, 'f_st_' . $h, 'f_w_' . $h, 'h'),
				self::get_text_shadow('.module:hover .tb_text_wrap ' . $selector, 't_sh' . $h,'h'),
				// Heading  Margin
				self::get_heading_margin_multi_field('.module', $h, 'top', 'h'),
				self::get_heading_margin_multi_field('.module', $h, 'bottom', 'h')
			    )
			)
		    ))
		))
	    ));
	}

	$dropcap = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_b_c', 'bg_c', 'background-color')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'font_dropcap_family'),
			    self::get_color('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_font_color'),
			    self::get_font_size('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_font_size'),
			    self::get_line_height('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_line_height'),
			    self::get_text_transform('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_letter_transform'),
			    self::get_font_style('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'font_dropcap', 'font_dropcap_bold'),
			    self::get_text_decoration('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_decoration_regular'),
			    self::get_text_shadow('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 't_sh_dr')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'f_d_f_h'),
			    self::get_color('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_f_c_h'),
			    self::get_font_size('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_f_s_h'),
			    self::get_font_style('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'f_d_h', 'f_d_b_h'),
			    self::get_text_decoration('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_d_r_h'),
			    self::get_text_shadow('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 't_sh_dr_h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_padding')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_p_h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_margin')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_m_h')
			)
		    )
		))
		
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'dropcap_border')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'd_b_h')
			)
		    )
		))
	    )),
	    // Rounded Corners
	    self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'rc_dp')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'rc_dp_h')
					)
				)
			))
	    )),
	    // Shadow
	    self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow('.tb_text_dropcap .tb_text_wrap > :first-child:first-letter', 'sh_dp')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow('.tb_text_dropcap .tb_text_wrap:hover > :first-child:first-letter', 'sh_dp_h')
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
		'head' => array(
		    'options' => $heading
		),
		'd' => array(
		    'label' => __('Drop-Cap', 'themify'),
		    'options' => $dropcap
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_text');
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.add_css_text }}<# data.text_drop_cap == 'dropcap' ? print( ' tb_text_dropcap' ) : ''; #>">
	    <# if ( data.mod_title_text ) { #>
		<?php echo $module_args['before_title']; ?>{{{ data.mod_title_text }}}<?php echo $module_args['after_title']; ?>
	    <# } #>
	    <div contenteditable="false" data-name="content_text" data-hasEditor class="tb_text_wrap">{{{data.content_text?data.content_text.replace(/(<|&lt;)!--more(.*?)?--(>|&gt;)/, '<span class="tb-text-more-link-indicator"><span>'):''}}}</div>
	</div>
	<?php
    }
}

new TB_Text_Module();
