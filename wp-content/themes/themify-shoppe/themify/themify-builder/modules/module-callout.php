<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Callout
 * Description: Display Callout content
 */

class TB_Callout_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('callout');
    }
    
    public function get_name(){
        return __('Callout', 'themify');
    }
    
    public function get_icon(){
	return 'announcement';
    }

    public function get_assets() {
	return array(
		'css'=>1
	);
    }
    public function get_plain_text($module) {
	$text = isset($module['heading_callout']) ? $module['heading_callout'] : '';
	if (isset($module['text_callout'])) {
	    $text .= $module['text_callout'];
	}
	return $text;
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_callout',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_callout',
		'type' => 'layout',
		'mode' => 'sprite',
		'label' => __('Callout Style', 'themify'),
		'options' => array(
		    array('img' => 'callout_button_right', 'value' => 'button-right', 'label' => __('Button Right', 'themify')),
		    array('img' => 'callout_button_left', 'value' => 'button-left', 'label' => __('Button Left', 'themify')),
		    array('img' => 'callout_button_bottom', 'value' => 'button-bottom', 'label' => __('Button Bottom', 'themify')),
		    array('img' => 'callout_button_bottom_center', 'value' => 'button-bottom-center', 'label' => __('Button Bottom Center', 'themify'))
		)
	    ),
	    array(
		'id' => 'heading_callout',
		'type' => 'text',
		'label' =>  __('Callout Heading', 'themify'),
		'control' => array(
		    'selector' => '.callout-heading'
		)
	    ),
	    array(
			'id' => 'title_tag',
			'type' => 'select',
			'label' => __('Callout Title Tag', 'themify'),
			'h_tags' => true,
			'default' => 'h3'
		),
	    array(
		'id' => 'text_callout',
		'type' => 'textarea',
		'label' => __('Callout Text', 'themify'),
		'control' => array(
		    'selector' => '.tb_text_wrap'
		)
	    ),
	    array(
		'id' => 'color_callout',
		'type' => 'layout',
		'mode' => 'sprite',
		'class' => 'tb_colors',
		'label' => 'c',
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'appearance_callout',
		'type' => 'checkbox',
		'label' => __('Appearance', 'themify'),
		'appearance' => true
	    ),
		array(
			'type' => 'group',
			'label' => __('Action Button', 'themify'),
			'display' => 'accordion',
			'options' => array(
				array(
				'id' => 'action_btn_link_callout',
				'type' => 'url',
				'label' => __('Action Link', 'themify'),
				'control' => array(
					'selector' => '.tb_callout_text'
				)
				),
				array(
				'id' => 'open_link_new_tab_callout',
				'type' => 'radio',
				'label' => __('Open Link', 'themify'),
				'options' => array(
					array('value'=>'no','name'=>__('Same window', 'themify')),
					array('value'=>'yes','name'=>__('New window', 'themify'))
				)
				),
				array(
				'id' => 'action_btn_text_callout',
				'type' => 'text',
				'label' => __('Action Button', 'themify'),
				'class' => 'medium'
				),
				array(
				    'id' => 'action_btn_color_callout',
				'type' => 'layout',
				'class' => 'tb_colors',
				'mode' => 'sprite',
				'label' => __('Button Color', 'themify'),
				'color' => true,
				'transparent'=>true
				),
				array(
				'id' => 'action_btn_appearance_callout',
				'type' => 'checkbox',
				'label' => __('Appearance', 'themify'),
				'appearance' => true
				),
			),
		),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_callout' ),
	);
    }

    public function get_live_default() {
	return array(
	    'heading_callout' => esc_html__('Callout Heading', 'themify'),
		'title_tag' => 'h3',
	    'text_callout' => esc_html__('Callout Text', 'themify'),
	    'action_btn_text_callout' => esc_html__('Action button', 'themify'),
	    'action_btn_link_callout' => 'https://themify.me/',
	    'action_btn_color_callout' => 'blue'
	);
    }

    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image('.module')
			)
		    ),
		    'h' => array(
			'options' => array(
			     self::get_image('.module', 'b_i','bg_c','b_r','b_p', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .callout-content'),
			    self::get_color_type(array(' .tb_text_wrap',' .callout-heading')),
			    self::get_font_size(' .callout-content'),
			    self::get_line_height(' .callout-content'),
			    self::get_letter_spacing(array(' .callout-content', ' .callout-heading')),
			    self::get_text_align(array(' .callout-content', ' .callout-heading')),
			    self::get_text_transform(array(' .callout-content', ' .callout-heading')),
			    self::get_font_style(array(' .callout-content', ' .callout-heading')),
			    self::get_text_decoration(array(' .callout-content'), 'text_decoration_regular'),
			    self::get_text_shadow(' .callout-content')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .callout-content', 'f_f', 'h'),
			    self::get_color_type(array(':hover .tb_text_wrap',':hover .callout-heading'),'',  'f_c_t_h',  'f_c_h', 'f_g_c_h'),
			    self::get_font_size(' .callout-content', 'f_s', '', 'h'),
			    self::get_font_style(array(' .callout-content', ' .callout-heading'), 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(array(' .callout-content'), 't_d_r', 'h'),
			    self::get_text_shadow(' .callout-content','t_sh','h')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_callout_text'), 'link_color'),
			    self::get_text_decoration(array('.module a',  '.module .callout-content'))
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_callout_text'), 'link_color', null,null, 'hover'),
			    self::get_text_decoration(array('.module a',  '.module .callout-content'), 't_d', 'h')
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
							self::get_box_shadow('.module')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('.module', 'sh', 'h')
						)
					)
				))
			)
		),
		// Display
		self::get_expand('disp', self::get_display())
	);

	$callout_title = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family('.module .callout-heading', 'font_family_alert_title'),
			self::get_color('.module .callout-heading', 'font_color_alert_title'),
			self::get_font_size('.module .callout-heading', 'font_size_alert_title'),
			self::get_line_height('.module .callout-heading', 'line_height_alert_title'),
			self::get_letter_spacing('.module .callout-heading', 'letter_spacing_alert_title'),
			self::get_text_transform('.module .callout-heading', 'text_transform_title'),
			self::get_font_style('.module .callout-heading', 'font_style_title', 'font_title_bold'),
			self::get_text_shadow('.module .callout-heading', 't_sh_c_t')
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family('.module .callout-heading', 'f_f_a_t','h'),
			self::get_color('.module .callout-heading', 'font_color_alert_title',null,null,'h'),
			self::get_font_size('.module .callout-heading', 'f_s_a_t','', 'h'),
			self::get_font_style('.module .callout-heading', 'f_s_t', 'f_t_b','h'),
            self::get_text_shadow('.module .callout-heading', 't_sh_c_t','h')
		    )
		)
	    ))
	);

	$callout_button = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .callout-button a', 'background_color_button', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .callout-button a', 'background_color_button', 'bg_c', 'background-color', 'hover')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .callout-button a', 'font_family_button'),
			    self::get_color('.module .callout-button a', 'font_color_button'),
			    self::get_font_size(' .callout-button a', 'font_size_button'),
			    self::get_font_style(' .callout-button a', 'f_fs_c', 'f_fw_c'),
			    self::get_line_height(' .callout-button a', 'line_height_button'),
			    self::get_text_shadow(' .callout-button a', 't_sh_c_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .callout-button a', 'f_f_b', 'h'),
			    self::get_color('.module .callout-button a', 'font_color_button', null, null, 'hover'),
			    self::get_font_size(' .callout-button a', 'f_s_b', '', 'h'),
				self::get_font_style(' .callout-button a', 'f_fs_c', 'f_fw_c', 'h'),
				self::get_text_shadow(' .callout-button a', 't_sh_c_b','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .callout-button a', 'c_b_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .callout-button a', 'c_b_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .callout-button a', 'c_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .callout-button a', 'c_b_sh', 'h')
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
		'c' => array(
		    'label' => __('Callout Heading', 'themify'),
		    'options' => $callout_title
		),
		'b' => array(
		    'label' => __('Callout Button', 'themify'),
		    'options' => $callout_button
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_callout');
	?>
    <#  var tag = data.title_tag?data.title_tag:'h3',
	    color_callout = undefined === data.color_callout || 'default' == data.color_callout ? 'tb_default_color' : data.color_callout,
	    btn_color_callout = undefined === data.action_btn_color_callout || 'default' == data.action_btn_color_callout ? 'tb_default_color' : data.action_btn_color_callout; #>
	<div class="module module-<?php echo $this->slug; ?> ui {{ data.layout_callout }} {{ color_callout }} {{ data.css_callout }} {{ data.background_repeat }} <# data.appearance_callout? print( data.appearance_callout.split('|').join(' ') ) : ''; #>">
	    <# if ( data.mod_title_callout ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_callout }}}<?php echo $module_args['after_title']; ?>
	    <# } #>

	    <div class="callout-inner">
		<div class="callout-content tf_left">
		    <{{tag}} class="callout-heading" contenteditable="false" data-name="heading_callout">{{{ data.heading_callout }}}</{{tag}}>
		    <div class="tb_text_wrap" contenteditable="false" data-name="text_callout">{{{ data.text_callout }}}</div>
		</div>

		<# if ( data.action_btn_text_callout ) { #>
		<div class="callout-button tf_right tf_textr">
		    <a href="{{ data.action_btn_link_callout }}" class="ui builder_button {{ btn_color_callout }} <# data.action_btn_appearance_callout ? print( data.action_btn_appearance_callout.split('|').join(' ') ) : ''; #>">
			<span contenteditable="false" data-name="action_btn_text_callout" class="tb_callout_text">{{{ data.action_btn_text_callout }}}</span>
		    </a>
		</div>
		<# } #>
	    </div>			
	</div>
	<?php
    }

}

new TB_Callout_Module();