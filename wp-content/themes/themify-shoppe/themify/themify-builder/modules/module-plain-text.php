<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: HTML / Text / Shortcode
 * Description: Display plain text
 */

class TB_Plain_Text_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('plain-text');
    }
    
    public function get_name(){
        return __('HTML / Text / Shortcode', 'themify');
    }

    public function get_icon(){
	return 'text';
    }

    public function get_plain_text($module) {
	return isset($module['plain_text']) ? $module['plain_text'] : '';
    }

    public function get_options() {
		return array(
			array(
				'id' => 'plain_text',
				'type' => 'textarea',
				'codeeditor' => 'htmlmixed',
				'control' => array(
				    'selector' => '.tb_text_wrap'
				)
			),
			array(
				'id' => 'formatting',
				'type' => 'toggle_switch',
				'label' => __( 'Content Formatting', 'themify' ),
				'options' => array(
					'on' => array( 'name' => '', 'value' => 'en' ),
					'off' => array( 'name' => '1', 'value' => 'dis' ),
				),
				'default' => 'on',
				'help' => sprintf( __( 'Enable for shortcodes, <a href="%s">embeds</a> and other text formatting. Disable for custom HTML, <code>&lt;script&gt;</code>, <code>&lt;style&gt;</code> tags.', 'themify' ), 'https://wordpress.org/support/article/embeds/' ),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'add_css_text' ),
		);
    }

    public function get_styling() {
	return array(
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
			    self::get_text_decoration('', 'text_decoration_regular'),
				self::get_text_shadow(' .tb_text_wrap'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(':hover .tb_text_wrap', 'f_f_h'),
			    self::get_color_type(':hover .tb_text_wrap','','f_c_t_h','f_c_h', 'f_g_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_font_style(':hover .tb_text_wrap', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
				self::get_text_shadow(' .tb_text_wrap','t_sh','h'),
			)
		    )
		))
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
    }

    protected function _visual_template() {
	?>
	<div class="module module-<?php echo $this->slug; ?> {{ data.add_css_text }}">
	    <div contenteditable="false" data-name="plain_text" data-hasEditor class="tb_text_wrap">{{{ data.plain_text }}}</div>
	</div>
	<?php
    }

}

new TB_Plain_Text_Module();
