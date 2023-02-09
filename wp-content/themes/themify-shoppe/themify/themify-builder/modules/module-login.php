<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Login
 * Description: Displays login form
 */

class TB_Login_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        parent::__construct('login');
    }
    
    public function get_name(){
        return __('Login', 'themify');
    }
    
    public function get_icon(){
	return 'unlock';
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
    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title',
		'type' => 'title'
	    ),
	    array(
		'id' => 'content_text',
		'type' => 'textarea',
		'label' => __('Logged In Message', 'themify'),
		'help' => __('Message to show if visitor is logged in', 'themify'),
	    ),
	    array(
            'id' => 'alignment',
            'type' => 'icon_radio',
            'label' => __('Form Alignment', 'themify'),
            'aligment2' => true
        ),
			array(
				'type' => 'group',
				'label' => __( 'Login Form', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
					'id' => 'redirect_to',
					'type' => 'url',
					'label' => __('Redirect URL', 'themify'),
					'help' => __('Redirect to this URL after successful login', 'themify'),
					'control'=>false
					),
					array(
						'id' => 'fail_action',
						'type' => 'select',
						'label' => __('When Login Fails', 'themify'),
						'options' => array(
							'r' => __( 'Redirect to WP login page', 'themify' ),
							'c' => __( 'Redirect to custom page', 'themify' ),
							'm' => __( 'Show message', 'themify' ),
						),
						'binding' => array(
							'r' => array( 'hide' => array( 'redirect_fail', 'msg_fail' ) ),
							'c' => array( 'show' => 'redirect_fail', 'hide' =>  'msg_fail' ),
							'm' => array( 'hide' => 'redirect_fail', 'show' =>  'msg_fail' ),
						)
					),
					array(
						'id' => 'redirect_fail',
						'type' => 'url',
						'label' => __('Redirect URL on Error', 'themify'),
						'help' => __('Redirect to this URL after unsuccessful login.', 'themify'),
						'control' => false
					),
					array(
						'id' => 'msg_fail',
						'type' => 'textarea',
						'label' => __('Fail Message', 'themify'),
						'after' => __('Message to show when login fails.', 'themify'),
						'control' => array(
							'selector' => '.tb_login_error'
						)
					),
					array(
						'type' => 'multi',
						'label' => __('Labels', 'themify'),
						'options' => array(
							array(
								'id' => 'icon_username',
								'type' => 'icon',
							),
							array(
								'id' => 'label_username',
								'type' => 'text',
								'after' => __('Username', 'themify'),
								'control' => array(
									'selector' => '.tb_login_username_text'
								)
							),
						)
					),
					array(
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'icon_password',
								'type' => 'icon',
							),
							array(
								'id' => 'label_password',
								'type' => 'text',
								'after' => __('Password', 'themify'),
								'control' => array(
									'selector' => '.tb_login_password_text'
								)
							),
						)
					),
					array(
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'icon_remember',
								'type' => 'icon',
							),
							array(
								'id' => 'label_remember',
								'type' => 'text',
								'after' => __('Remember Me', 'themify'),
								'control' => array(
									'selector' => '.tb_login_remember_text'
								)
							),
						)
					),
					array(
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'icon_log_in',
								'type' => 'icon',
							),
							array(
								'id' => 'label_log_in',
								'type' => 'text',
								'after' => __('Log In', 'themify'),
								'control' => array(
									'selector' => '.tb_login_submit button'
								)
							),
						)
					),
					array(
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'icon_forgotten_password',
								'type' => 'icon',
							),
							array(
								'id' => 'label_forgotten_password',
								'type' => 'text',
								'after' => __('Forgotten Password Link', 'themify'),
								'control' => array(
									'selector' => '.tb_login_form .tb_login_links a'
								)
							),
						)
					),
					array(
					'id' => 'remember_me_display',
					'type' => 'toggle_switch',
					'label' => __('Remember Me', 'themify'),
					'options' => array(
						'on'=>array('name'=>'show','value' =>'s'),
						'off'=>array('name'=>'hide','value' =>'hi')
					)
					),
				)
			),
			array(
				'type' => 'group',
				'label' => __( 'Reset Password Form', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
					'id' => 'lostpasswordform_redirect_to',
					'type' => 'url',
					'label' => __('Redirect URL', 'themify'),
					'help' =>__('Redirect to this URL after password reset form submission', 'themify'),
					'control'=>false
					),
					array(
						'type' => 'multi',
						'label' => __('Labels', 'themify'),
						'options' => array(
							array(
								'id' => 'lostpasswordform_icon_username',
								'type' => 'icon',
							),
							array(
								'id' => 'lostpasswordform_label_username',
								'type' => 'text',
								'after' => __('Username', 'themify'),
								'control' => array(
									'selector' => '.tb_lostpassword_username_text'
								)
							),
						)
					),
					array(
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'lostpasswordform_icon_reset',
								'type' => 'icon',
							),
							array(
								'id' => 'lostpasswordform_label_reset',
								'type' => 'text',
								'after' => __('Reset Password Button', 'themify'),
								'control' => array(
									'selector' => '.tb_lostpassword_submit button'
								)
							),
						)
					),
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
	);
    }

    public function get_live_default() {
	return array(
	    'label_username' => __('Username or Email', 'themify'),
	    'label_password' => __('Password', 'themify'),
	    'label_remember' => __('Remember Me', 'themify'),
	    'label_log_in' => __('Log In', 'themify'),
	    'remember_me_display'=>'show',
	    'label_forgotten_password' => __('Forgotten Password?', 'themify'),
	    'lostpasswordform_label_username' => __('Username or Email', 'themify'),
	    'lostpasswordform_label_reset' => __('Reset Password', 'themify'),
	    'msg_fail' => __('Username or password is incorrect. Please try again.', 'themify'),
	);
    }


    public function get_styling() {
	$general = array(
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image('', 'b_i','bg_c','b_r','b_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
			)
		    )
		))
	    )),
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('', 'f_f'),
			    self::get_color_type(array(' label', ' a'),'','f_c_t','f_c', 'f_g_c'),
			    self::get_font_size('', 'f_s'),
			    self::get_line_height('', 'l_h'),
			    self::get_letter_spacing('', 'l_s'),
			    self::get_text_align('', 't_a'),
			    self::get_text_transform('', 't_t'),
			    self::get_font_style('', 'f_st','f_w'),
			    self::get_text_decoration(array('', ' .tb_login_remember_text'), 't_d_r'),
				self::get_text_shadow(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('', 'f_f', 'h'),
			    self::get_color_type(array(' label', ' a'),'h'),
			    self::get_font_size('', 'f_s','', 'h'),
			    self::get_font_style('', 'f_st','f_w', 'h'),
			    self::get_text_decoration(array('', ' .tb_login_remember_text'), 't_d_r', 'h'),
				self::get_text_shadow('','t_sh','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('','p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('','p','h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('','m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('','m','h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('','b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('','b','h')
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
			self::get_width(array(' .tb_login_form', ' .tb_lostpassword_form'), 'g_w')
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

	$labels = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' label', 'f_f_l'),
			self::get_color(' label', 'f_c_l'),
			self::get_font_size(' label', 'f_s_l'),
			self::get_font_style(' label', 'f_st_l', 'f_fw_l'),
			self::get_text_shadow(' label', 't_sh_l'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' label', 'f_f_l','h'),
			self::get_color(' label', 'f_c_l',null,null,'h'),
			self::get_font_size(' label', 'f_s_l','','h'),
			self::get_font_style(' label', 'f_st_l', 'f_fw_l', 'h'),
			self::get_text_shadow(' label', 't_sh_l','h'),
		    )
		)
	    ))
	);

	$inputs = array(
	    //bacground
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' input', 'bg_c_i', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' input', 'bg_c_i', 'bg_c', 'background-color','h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' input', 'f_f_i'),
			    self::get_color(' input', 'f_c_i'),
			    self::get_font_size(' input', 'f_s_i'),
			    self::get_font_style(' input', 'f_st_i', 'f_fw_i'),
				self::get_text_shadow(' input', 't_sh_i'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' input', 'f_f_i','h'),
			    self::get_color(' input', 'f_c_i',null,null,'h'),
			    self::get_font_size(' input', 'f_s_i','','h'),
				self::get_font_style(' input', 'f_st_i', 'f_fw_i', 'h'),
				self::get_text_shadow(' input', 't_sh_i','h'),
			)
		    )
		))
	    )),
		// Placeholder
		self::get_expand('Placeholder', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_font_family(' input::placeholder', 'f_f_in_ph'),
						self::get_color(' input::placeholder', 'f_c_in_ph'),
						self::get_font_size(' input::placeholder', 'f_s_in_ph'),
						self::get_font_style(' input::placeholder', 'f_st_in_ph', 'f_fw_in_ph'),
						self::get_text_shadow(' input::placeholder', 't_sh_in_ph'),
					)
				),
				'h' => array(
					'options' => array(
						self::get_font_family(' input:hover::placeholder', 'f_f_in_ph_h',''),
						self::get_color(' input:hover::placeholder', 'f_c_in_ph_h',null,null,''),
						self::get_font_size(' input:hover::placeholder', 'f_s_in_ph_h','',''),
						self::get_font_style(' input:hover::placeholder', 'f_st_in_ph', 'f_fw_in_ph', 'h'),
						self::get_text_shadow(' input:hover::placeholder', 't_sh_in_ph_h',''),
					)
				)
			))
		)),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' input', 'b_in')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' input', 'b_in','h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' input', 'in_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' input', 'in_p', 'h')
			)
		    )
		))
	    )),
	    // Width
	    self::get_expand('w', array(
			self::get_width(array(' input[type="text"]',' input[type="password"]'), 'in_w')
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' input', 'in_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' input', 'in_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' input', 'in_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' input', 'in_b_sh', 'h')
					)
				)
			))
		))
	);

	$send_button = array(
	    //bacground
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .tb_login_submit button, .tb_lostpassword_submit button', 'bg_c_s', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'bg_c_s_h', 'bg_c', 'background-color','')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb_login_submit button, .tb_lostpassword_submit button', 'f_f_s'),
			    self::get_color(' .tb_login_submit button, .tb_lostpassword_submit button', 'f_c_s'),
			    self::get_font_size(' .tb_login_submit button, .tb_lostpassword_submit button', 'f_s_s'),
			    self::get_font_style(' .tb_login_submit button, .tb_lostpassword_submit button', 'f_st_s', 'f_fw_s'),
				self::get_text_shadow(' .tb_login_submit button, .tb_lostpassword_submit button', 't_sh_b'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'f_f_s_h',''),
			    self::get_color(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'f_c_s_h',null,null,''),
			    self::get_font_size(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'f_s_s_h','',''),
				self::get_font_style(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'f_st_s', 'f_fw_s', 'h'),
				self::get_text_shadow(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 't_sh_b_h',''),
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .tb_login_submit button, .tb_lostpassword_submit button', 'b_s')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'b_s_s','')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .tb_login_submit button, .tb_lostpassword_submit button', 'bt_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'bt_p', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .tb_login_submit button, .tb_lostpassword_submit button', 'bt_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'bt_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .tb_login_submit button, .tb_lostpassword_submit button', 'bt_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .tb_login_submit button:hover, .tb_lostpassword_submit button:hover', 'bt_sh', 'h')
					)
				)
			))
		))
	);

	$login_error = array(
	    //background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .tb_login_error', 'bg_c_e', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .tb_login_error:hover', 'bg_c_e_h', 'bg_c', 'background-color','')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb_login_error', 'f_f_e'),
			    self::get_color(' .tb_login_error', 'f_c_e'),
			    self::get_font_size(' .tb_login_error', 'f_s_e'),
			    self::get_font_style(' .tb_login_error', 'f_st_e', 'f_fw_e'),
				self::get_text_shadow(' .tb_login_error', 't_sh_e'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' tb_login_error:hover', 'f_f_e_h',''),
			    self::get_color(' .tb_login_error:hover', 'f_c_e_h',null,null,''),
			    self::get_font_size(' .tb_login_error:hover', 'f_s_e_h','',''),
				self::get_font_style(' .tb_login_error:hover', 'f_st_e', 'f_fw_e', 'h'),
				self::get_text_shadow(' .tb_login_error:hover', 't_sh_e_h',''),
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .tb_login_error', 'b_e')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .tb_login_error:hover', 'b_e_s','')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .tb_login_error', 'e_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .tb_login_error:hover', 'e_p_h', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .tb_login_error', 'e_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .tb_login_error:hover', 'e_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .tb_login_error', 'e_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .tb_login_error:hover', 'e_sh', 'h')
					)
				)
			))
		))
	);

	return 
	    array(
		'type' => 'tabs',
		'options' => array(
		    'g' => array(
			'options' => $general
		    ),
		    'm_t' => array(
			'options' => $this->module_title_custom_style()
		    ),
		    'labels' => array(
			'label' => __('Labels', 'themify'),
			'options' => $labels
		    ),
		    'inputs' => array(
			'label' => __('Input Fields', 'themify'),
			'options' => $inputs
		    ),
		    'send_button' => array(
			'label' => __('Submit Button', 'themify'),
			'options' => $send_button
		    ),
		    'login_error' => array(
			'label' => __('Login Error Message', 'themify'),
			'options' => $login_error
		    ),
		)
	    );
    }
    
    protected function _visual_template() {
	$module_args = self::get_module_args();
	?>
	<# var alignment = !data.alignment ||'left' == data.alignment ? '' : ('center' == data.alignment?' tb_login_c':' tf_right'); #>
	<div class="module module-<?php echo $this->slug; ?> {{ data.css }}">
        <div class="tb_login_wrap{{ alignment }}">
	    <# if ( data.mod_title ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.mod_title }}}<?php echo $module_args['after_title']; ?>
	    <# } #>

	    <form class="tb_login_form" name="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')) ?>" method="post">

		<# if ( data.fail_action == 'm' ) { #>
			<div class="tb_login_error" contenteditable="false" data-name="msg_fail">{{ data.msg_fail }}</div>
	    <# } #>

		<p class="tb_login_username">
		    <label>
			<# if ( data.icon_username ) { #><em><# print( api.Helper.getIcon(data.icon_username).outerHTML ) #></em><# } #>
			<span class="tb_login_username_text" contenteditable="false" data-name="label_username">{{ data.label_username }}</span>
			<input type="text" name="log" class="input">
		    </label>
		</p>
		<p class="tb_login_password">
		    <label>
			<# if ( data.icon_password ) { #><em><# print( api.Helper.getIcon(data.icon_password).outerHTML ) #></em><# } #>
			<span class="tb_login_password_text" contenteditable="false" data-name="label_password">{{ data.label_password }}</span>
			<input type="password" name="pwd" class="input">
		    </label>
		</p>
		<div class="tb_login_links">
			<# if ( data.icon_forgotten_password ) { #><em><# print( api.Helper.getIcon(data.icon_forgotten_password).outerHTML ) #></em><# } #>
		    <a href="<?php echo esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" contenteditable="false" data-name="label_forgotten_password">{{ data.label_forgotten_password }}</a>
		</div>
		<# if ( data.remember_me_display == 'show' ) { #>
		<p class="tb_login_remember tf_left tf_box tf_clear">
		    <label>
			<input name="rememberme" type="checkbox" value="forever"> 
			<# if ( data.icon_remember ) { #><em><# print( api.Helper.getIcon(data.icon_remember).outerHTML ) #></em><# } #>
			<span class="tb_login_remember_text" contenteditable="false" data-name="label_remember">{{ data.label_remember }}</span>
		    </label>
		</p>
		<# } #>
		<p class="tb_login_submit tf_right">
		    <button contenteditable="false" data-name="label_log_in"><# if ( data.icon_log_in ) { #><em><# print( api.Helper.getIcon(data.icon_log_in).outerHTML ) #></em><# } #> {{ data.label_log_in }}</button>
		    <input type="hidden" name="redirect_to">
		</p>
	    </form>

	    <form class="tb_lostpassword_form" name="lostpasswordform" action="<?php echo esc_url(network_site_url('wp-login.php?action=lostpassword', 'login_post')); ?>" method="post" style="display:none">
		<p class="tb_lostpassword_username">
		    <label>
			<# if ( data.lostpasswordform_icon_username ) { #><em><# print( api.Helper.getIcon(data.lostpasswordform_icon_username).outerHTML ) #></em><# } #>
			<span class="tb_lostpassword_username_text" contenteditable="false" data-name="lostpasswordform_label_username">{{ data.lostpasswordform_label_username }}</span>
			<input type="text" name="user_login" class="input">
		    </label>
		</p>
		<p class="tb_lostpassword_submit tf_right">
		    <button contenteditable="false" data-name="lostpasswordform_label_reset"><# if ( data.lostpasswordform_icon_reset ) { #><em><# print( api.Helper.getIcon(data.lostpasswordform_icon_reset).outerHTML ) #></em><# } #>{{ data.lostpasswordform_label_reset }}</button>
		    <input type="hidden" name="redirect_to">
		</p>

		<div class="tb_login_links">
		    <a href="<?php echo esc_url(site_url('wp-login.php')); ?>" contenteditable="false" data-name="label_log_in">{{ data.label_log_in }}</a>
		</div>
	    </form>
	</div>
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
	return '';
    }

}

new TB_Login_Module();
