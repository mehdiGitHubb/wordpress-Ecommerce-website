<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Alert
 * Description: Display Alert content
 */

class TB_alert_Module extends Themify_Builder_Component_Module {

    public function __construct() {
            parent::__construct('alert');
    }
    
    public function get_name(){
	return __('Alert', 'themify');
    }
    
    public function get_assets() {
	$_arr= array(
	    'css'=>1
        );
        if(!Themify_Builder_Model::is_front_builder_activate()){
            $_arr['js']=1;
        }
        return $_arr;
    }
    public function get_plain_text($module) {
	$text = isset($module['heading_alert']) ? $module['heading_alert'] : '';
	if (isset($module['text_alert'])) {
	    $text .= $module['text_alert'];
	}
	return $text;
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_alert',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_alert',
		'type' => 'layout',
		'mode' => 'sprite',
		'label' => __('Alert Style', 'themify'),
		'options' => array(
		    array('img' => 'callout_button_right', 'value' => 'button-right', 'label' => __('Button Right', 'themify')),
		    array('img' => 'callout_button_left', 'value' => 'button-left', 'label' => __('Button Left', 'themify')),
		    array('img' => 'callout_button_bottom', 'value' => 'button-bottom', 'label' => __('Button Bottom', 'themify')),
		    array('img' => 'callout_button_bottom_center', 'value' => 'button-bottom-center', 'label' => __('Button Bottom Center', 'themify'))
		)
	    ),
	    array(
		'id' => 'heading_alert',
		'type' => 'text',
		'label' => __('Alert Heading', 'themify'),
		'control' => array(
		    'selector' => '.alert-heading'
		)
	    ),
	    array(
			'id' => 'title_tag',
			'type' => 'select',
			'label' => __('Alert Title Tag', 'themify'),
			'h_tags' => true,
			'default' => 'h3'
		),
	    array(
		'id' => 'text_alert',
		'type' => 'textarea',
		'label' =>  __('Alert Text', 'themify'),
		'control' => array(
		    'selector' => '.alert-content .tb_text_wrap'
		)
	    ),
	    array(
		'id' => 'color_alert',
		'type' => 'layout',
		'mode' => 'sprite',
		'class' => 'tb_colors',
		'label' => __('Alert Color', 'themify'),
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'appearance_alert',
		'type' => 'checkbox',
		'label' => __('Alert Appearance', 'themify'),
		'appearance' => true
	    ),
		array(
			'type' => 'group',
			'display' => 'accordion',
			'label' => __('Action Button', 'themify'),
			'options' => array(
				array(
				'id' => 'action_btn_text_alert',
				'type' => 'text',
				'label' => __('Action Button', 'themify'),
				'class' => 'medium',
				'control' => array(
					'selector' => '.tb_alert_text'
				)
				),
				array(
				'id' => 'alert_button_action',
				'type' => 'select',
				'label' => __('Click Action', 'themify'),
				'options' => array(
					'close' => __('Close alert box', 'themify'),
					'message' => __('Display a message', 'themify'),
					'url' => __('Go to URL', 'themify'),
				),
				'binding' => array(
					'close' => array('hide' => array('alert_message_text', 'action_btn_link_alert', 'open_link_new_tab_alert', 'lb_size_alert')),
					'message' => array('show' => 'alert_message_text', 'hide' => array('action_btn_link_alert', 'open_link_new_tab_alert', 'lb_size_alert')),
					'url' => array('show' => array('action_btn_link_alert', 'open_link_new_tab_alert', 'lb_size_alert'), 'hide' =>'alert_message_text')
				)
				),
				array(
				'id' => 'alert_message_text',
				'type' => 'textarea',
				'label' => __('Message text', 'themify')
				),
				array(
				'id' => 'action_btn_link_alert',
				'type' => 'url',
				'label' => __('Action Link', 'themify'),
				),
				array(
				'id' => 'open_link_new_tab_alert',
				'type' => 'radio',
				'label' => __('Open Link', 'themify'),
				'options' => array(
					array('value' => 'no', 'name' => __('Same Window', 'themify')),
					array('value' => 'yes', 'name' => __('New Window', 'themify')),
					array('value' => 'lightbox', 'name' => __('Lightbox', 'themify')),
				),
				'option_js' => true,
				),
				array(
					'type' => 'multi',
					'id' => 'lb_size_alert',
					'label' => __('Lightbox Dimension', 'themify'),
					'options' => array(
						array(
							'id' => 'lightbox_width',
							'label' => 'w', 
							'type' => 'range',
							'control'=>false,
							'units' => array(
								'px' => array(
									'max' => 3500
								),
								'em' => array(
									'min' => -50,
									'max' => 50
								),
								'%' =>''
							)
						),
						array(
							'id' => 'lightbox_height',
							'label' => 'ht',
							'type' => 'range',
							'control'=>false,
							'units' => array(
								'px' => array(
									'max' => 3500
								),
								'em' => array(
									'min' => -50,
									'max' => 50
								),
								'%' => ''
							)
						)
					),
					'wrap_class' => 'tb_group_element_lightbox lightbox_size'
				),
				array(
				    'id' => 'action_btn_color_alert',
				'type' => 'layout',
				'class' => 'tb_colors',
				'mode' => 'sprite',
				'label' => __('Button Color', 'themify'),
				'color' => true,
				'transparent'=>true
				),
				array(
				'id' => 'action_btn_appearance_alert',
				'type' => 'checkbox',
				'label' => __('Appearance', 'themify'),
				'appearance' => true
				),
			),
		),
		array(
			'type' => 'group',
			'label' => __( 'Alert Options', 'themify' ),
			'display' => 'accordion',
			'options' => array(
				array(
				'id' => 'alert_no_date_limit',
				'type' => 'toggle_switch',
				'label' => __('Schedule Alert', 'themify'),
				'options' => array(
					'on' => array('name'=>'alert_schedule','value' =>'en'),
					'off' => array('name'=>'', 'value' =>'dis'),
				),
				'binding' => array(
					'checked' => array(
					'show' => array('alert_start_at', 'alert_end_at')
					),
					'not_checked' => array(
					'hide' => array('alert_start_at', 'alert_end_at')
					)
				)
				),
				array(
				'id' => 'alert_start_at',
				'type' => 'date',
				'label' => __('Start at', 'themify')
				),
				array(
				'id' => 'alert_end_at',
				'type' => 'date',
				'label' => __('End at', 'themify')
				),
				array(
				'id' => 'alert_show_to',
				'type' => 'select',
				'label' => __('Guest/Logged Users', 'themify'),
				'options' => array(
					'' => __('Show to all users', 'themify'),
					'guest' => __('Show only to guest visitors', 'themify'),
					'user' => __('Show only to logged-in users', 'themify')
				)
				),
				array(
					'id' => 'alert_limit_count',
					'type' => 'number',
					'label' => __('Limit Display', 'themify'),
					'help' => __('Enter the number of times that this alert should show.', 'themify'),
				),
				array(
				'id' => 'alert_auto_close',
				'label' => __('Auto Close', 'themify'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'alert_close_auto','value' =>__( 'Enable', 'themify' )),
					'off' => array('name'=>'', 'value' =>__( 'Disable', 'themify' ))
				),
				'binding' => array(
					'checked' => array(
					'show' =>'alert_auto_close_delay'
					),
					'not_checked' => array(
					'hide' =>'alert_auto_close_delay'
					)
				)
				),
				array(
				'id' => 'alert_auto_close_delay',
				'type' => 'number',
				'label' => __('Close Alert After', 'themify'),
				'after' => __(' Seconds', 'themify')
				),
			),
		),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_alert' ),
	);
    }

    public function get_live_default() {
	return array(
	    'heading_alert' => esc_html__('Alert Heading', 'themify'),
	    'title_tag' => 'h3',
	    'text_alert' => esc_html__('Alert Text', 'themify'),
	    'action_btn_text_alert' => esc_html__('Action button', 'themify'),
	    'action_btn_link_alert' => 'https://themify.me/',
	    'alert_auto_close_delay' => 5,
	    'action_btn_color_alert'=>'blue'
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
			    self::get_font_family(array(' .alert-content')),
			    self::get_color_type(array(' .tb_text_wrap',' .alert-heading')),
			    self::get_font_size(' .alert-content'),
			    self::get_line_height(' .alert-content'),
			    self::get_letter_spacing(' .alert-content'),
			    self::get_text_align(' .alert-content'),
			    self::get_text_transform(' .alert-content'),
			    self::get_font_style(' .alert-content'),
			    self::get_text_decoration(array(' .alert-content',' a'), 'text_decoration_regular'),
			    self::get_text_shadow(' .alert-content'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(' .alert-content:hover'), 'f_f', null, ''),
			    self::get_color_type(array(':hover .tb_text_wrap',':hover .alert-heading'), 'f_c_t_h', 'f_c_h', 'f_g_c_h'),
			    self::get_font_size(' .alert-content', 'f_s', '', 'h'),
			    self::get_font_style(' .alert-content:hover', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(array(' .alert-content',' a'), 't_d_r', 'h'),
			    self::get_text_shadow(' .alert-content', 't_sh', 'h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_alert_text'), 'link_color'),
			    self::get_text_decoration(array('.module .alert-content',' a')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_alert_text'), 'link_color', null,null, 'hover'),
			    self::get_text_decoration(array('.module .alert-content',' a'), 't_d', 'h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('', 'p', 'h'),
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('', 'm', 'h'),
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
		// Display
		self::get_expand('disp', self::get_display())
	);

	$alert_title = array(
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.module .alert-heading', 'f_f_a_t'),
			    self::get_color('.module .alert-heading', 'f_c_a_t'),
			    self::get_font_size('.module .alert-heading', 'f_s_a_t'),
			    self::get_line_height('.module .alert-heading', 'l_h_a_t'),
			    self::get_letter_spacing('.module .alert-heading', 'l_s_a_t'),
			    self::get_text_transform('.module .alert-heading', 't_t_a_t'),
			    self::get_font_style('.module .alert-heading', 'f_st_a_t', 'f_s_a_b'),
			    self::get_text_shadow('.module .alert-heading', 't_sh_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.module .alert-heading', 'f_f_a_t', 'h'),
			    self::get_color('.module .alert-heading', 'f_c_a_t', null, null, 'h'),
			    self::get_font_size('.module .alert-heading', 'f_s_a_t', '', 'h'),
			    self::get_font_style('.module .alert-heading', 'f_st_a_t', 'f_s_a_b', 'h'),
			    self::get_text_shadow('.module .alert-heading', 't_sh_a_t', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('.module .alert-heading', 'm_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('.module .alert-heading', 'm_a_t', 'h')
			)
		    )
		))
	    ))
	);

	$alert_button = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .alert-button a', 'background_color_button', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .alert-button a', 'background_color_button', 'bg_c', 'background-color','hover')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .alert-button a', 'font_family_button'),
			    self::get_color('.module .alert-button a', 'font_color_button'),
			    self::get_font_size(' .alert-button a', 'font_size_button'),
			    self::get_font_style(' .alert-button a', 'f_fs_a', 'f_fw_a'),
			    self::get_line_height(' .alert-button a', 'line_height_button'),
			    self::get_text_shadow(' .alert-button a', 't_sh_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(':hover .alert-button a', 'f_f_b_h', ''),
			    self::get_color('.module .alert-button a', 'font_color_button',null, null, 'hover'),
			    self::get_font_size(':hover .alert-button a', 'f_s_b_h', '', ''),
				self::get_font_style(':hover .alert-button a', 'f_fs_a', 'f_fw_a', 'h'),
			    self::get_text_shadow(':hover .alert-button a', 't_sh_a_b_h', '')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .alert-button a', 'p_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .alert-button a', 'p_a_b', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .alert-button a', 'm_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .alert-button a', 'm_a_b', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .alert-button a', 'b_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .alert-button a', 'b_a_b', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .alert-button a', 'a_b_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .alert-button a', 'a_b_r_c', 'h')
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
							self::get_box_shadow(' .alert-button a', 'a_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .alert-button a', 'a_b_sh', 'h')
						)
					)
				))
			)
		)
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
		't' => array(
		    'label' => __('Alert Title', 'themify'),
		    'options' => $alert_title
		),
		'b' => array(
		    'label' => __('Alert Button', 'themify'),
		    'options' => $alert_button
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_alert');
	?>
    <# 
	var tag = data.title_tag?data.title_tag:'h3',
	color_alert = undefined === data.color_alert || 'default' == data.color_alert ? 'tb_default_color' : data.color_alert,
	layout=data.layout_alert || 'button-right',
	btn_color_alert = undefined === data.action_btn_color_alert || 'default' === data.action_btn_color_alert ? 'tb_default_color' : data.action_btn_color_alert; #>
	<div class="module module-<?php echo $this->slug; ?> ui {{ layout }} {{ color_alert }} {{ data.css_alert }} <# data.appearance_alert ? print( data.appearance_alert.split('|').join(' ') ) : ''; #>">
	    <# if ( data.mod_title_alert ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_alert }}}<?php echo $module_args['after_title']; ?>
	    <# } #>
	    <div class="alert-inner">
		<div class="alert-content">
		    <{{tag}} class="alert-heading" contenteditable="false" data-name="heading_alert">{{{ data.heading_alert }}}</{{tag}}>
		    <div class="tb_text_wrap" contenteditable="false" data-name="text_alert">{{{ data.text_alert }}}</div>
		</div>
		<# if ( data.action_btn_text_alert ) { #>
		<div class="alert-button">
            <a href="{{ data.action_btn_link_alert }}" class="ui builder_button {{ btn_color_alert }} <# data.action_btn_appearance_alert  ? print( data.action_btn_appearance_alert.split('|').join(' ') ) : ''; #><# 'url'!==data.alert_button_action ? print( ' alert-close' ) : ''; #>">
			<span class="tb_alert_text" contenteditable="false" data-name="action_btn_text_alert">{{{ data.action_btn_text_alert }}}</span>
		    </a>
		</div>
		<# } #>
	    </div>
	    <div class="alert-close tf_close"></div>
	</div>
	<?php
    }

}

new TB_alert_Module();
