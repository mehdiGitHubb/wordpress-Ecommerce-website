<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Optin Forms
 * Description: Displays Optin form
 */
class TB_Optin_Module extends Themify_Builder_Component_Module {

	public function __construct() {
		parent::__construct('optin');

		include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
		add_action( 'wp_ajax_tb_optin_get_settings', array( __CLASS__, 'ajax_tb_optin_get_settings' ) );
	}
	
        public function get_name(){
            return __('Optin Form', 'themify');
        }

	public function get_icon(){
	    return 'email';
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
	public function get_title( $module ) {
		return isset( $module['mod_settings']['mod_title'] ) ? wp_trim_words( $module['mod_settings']['mod_title'], 100 ) : '';
	}

	/**
	 * Handles Ajax request to get the options for providers
	 *
	 * @since 4.2.3
	 */
	public static function ajax_tb_optin_get_settings() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$providers = Builder_Optin_Service::get_providers();
		$providers_settings = $providers_list = $providers_binding = array();
		foreach ( $providers as $id => $instance ) {
			$providers_list[ $id ] = $instance->get_label();

			$providers_settings[] = array(
				'type' => 'group',
				'options' => $instance->get_options(),
				'wrap_class' => $id
			);

			$providers_binding[ $id ] = array(
				'hide' => array_values(array_diff( array_keys( $providers ), array( $id ) )),
				'show' =>  $id ,
			);
		}

		$options = array(
			array(
				'id' => 'provider',
				'type' => 'select',
				'options' => $providers_list,
				'binding' => $providers_binding
			),
			array(
				'type' => 'group',
				'id' => 'provider_settings',
				'options' => $providers_settings
			),
            array(
                'id' => 'captcha',
                'type' => 'toggle_switch',
                'label' => __( 'reCaptcha', 'themify' ),
                'help' => sprintf(__('Requires Captcha keys entered at: <a target="_blank" href="%s">reCAPTCHA settings</a>.', 'themify'), admin_url('admin.php?page=themify#setting-integration-api')),
                'options' => array(
                    'on' => array( 'name' => 'on', 'value' => 'en' ),
                    'off' => array( 'name' => '', 'value' => 'dis' )
                )
            )
		);
		echo json_encode( $options );
		die;
	}

	public function get_options() {

		return array(
			array(
				'id' => 'mod_title',
				'type' => 'title'
			),
			array(
				'id' => 'layout',
				'type' => 'layout',
				'label' => __('Layout', 'themify'),
				'mode' => 'sprite',
				'options' => array(
					array( 'img' => 'optin_inline_block', 'value' => 'tb_optin_inline_block', 'label' => __( 'Inline Block', 'themify' ) ),
					array( 'img' => 'optin_horizontal', 'value' => 'tb_optin_horizontal', 'label' => __( 'Horizontal', 'themify' ) ),
					array( 'img' => 'optin_block', 'value' => 'tb_optin_block', 'label' => __( 'Block', 'themify' ) ),
				)
			),
			/* provider settings are retrieved via Ajax to prevent caching */
			array(
				'id' => 'provider',
				'label' => __( 'Optin Providers', 'themify' ),
				'type' => 'optin_provider',
			),
			array(
				'type' => 'group',
				'label' => __( 'Labels', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
						'type' => 'multi',
						'label' => __('First Name', 'themify'),
						'options' => array(
							array(
								'id' => 'label_firstname',
								'type' => 'text',
								'help' => __('First Name label', 'themify'),
								'control' => array(
									'selector' => '.tb_optin_fname_text'
								)
							),
							array(
								'id' => 'fname_hide',
								'type' => 'checkbox',
								'options' => array(
									array( 'name' => '1', 'value' => __( 'Hide', 'themify' ) )
								),
								'binding' => array(
									'checked' => array(
										'show' => 'default_fname' 
									),
									'not_checked' => array(
										'hide' => 'default_fname' 
									)
								)
							),
						),
					),
					array(
						'id' => 'fn_placeholder',
						'type' => 'text',
						'label' => __('Placeholder', 'themify'),
					),
					array(
						'id' => 'default_fname',
						'type' => 'text',
						'label' => __('Default First Name', 'themify'),
						'help' =>__( 'Default name is recommended as some newsletter providers do not subscribe email address without a name.', 'themify' ),
					),
					array(
						'type' => 'multi',
						'label' => __('Last Name', 'themify'),
						'options' => array(
							array(
								'id' => 'label_lastname',
								'type' => 'text',
								'help' => __('Last Name label', 'themify'),
								'control' => array(
									'selector' => '.tb_option_lastname_text'
								)
							),
							array(
								'id' => 'lname_hide',
								'type' => 'checkbox',
								'options' => array(
									array( 'name' => '1', 'value' => __( 'Hide', 'themify' ) )
								),
								'binding' => array(
									'checked' => array(
										'show' =>'default_lname' 
									),
									'not_checked' => array(
										'hide' => 'default_lname' 
									)
								)
							),
						),
					),
					array(
						'id' => 'ln_placeholder',
						'type' => 'text',
						'label' => __('Placeholder', 'themify'),
					),
					array(
						'id' => 'default_lname',
						'type' => 'text',
							'label' => __('Default Last Name', 'themify'),
					),
					array(
						'id' => 'label_email',
						'type' => 'text',
						'label' => __('Email Address', 'themify'),
						'control' => array(
							'selector' => '.tb_option_email_text'
						)
					),
					array(
						'id' => 'email_placeholder',
						'type' => 'text',
						'label' => __('Placeholder', 'themify'),
					),
					array(
						'id' => 'label_submit',
						'type' => 'text',
						'label' => __('Submit', 'themify'),
						'control' => array(
							'selector' => '.tb_option_submit button'
						)
					),
					array(
						'id' => 'button_icon',
						'type' => 'icon',
						'label' => __( 'Button Icon', 'themify' )
					),
					array(
						'id' => 'gdpr',
						'label' => __('GDPR', 'builder-contact'),
						'type' => 'toggle_switch',
						'options' => array(
							'on' => array( 'name' => 'on', 'value' => 'en' ),
							'off' => array( 'name' => '', 'value' => 'dis' )
						),
						'binding' => array(
							'checked' => array( 'show' => 'gdpr_label'),
							'not_checked' => array( 'hide' =>'gdpr_label' ),
						)
					),
					array(
						'id' => 'gdpr_label',
						'type' => 'textarea',
						'label' => __( 'GDPR Message', 'builder-contact' )
					),
				)
			),
			array(
				'type' => 'group',
				'label' => __( 'Success Action', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
						'id' => 'success_action',
						'type' => 'radio',
						'label' => __( 'Success Action', 'themify' ),
						'options' => array(
							array( 'value' => 's2', 'name' => __( 'Show Message', 'themify' ) ),
							array( 'value' => 's1', 'name' => __( 'Redirect to URL', 'themify' ) ),
						),
						'option_js' => true,
					),
					array(
						'id' => 'redirect_to',
						'type' => 'url',
						'label' => __('Redirect URL', 'themify'),
						'wrap_class' => 'tb_group_element_s1',
					),
					array(
						'id' => 'message',
						'type' => 'wp_editor',
						'wrap_class' => 'tb_group_element_s2',
					),
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
		);
	}

	public function get_live_default() {
		return array(
			'label_firstname' => __( 'First Name', 'themify' ),
			'default_fname' => __( 'John', 'themify' ),
			'label_lastname' => __( 'Last Name', 'themify' ),
			'default_lname' => __( 'Doe', 'themify' ),
			'label_email' => __( 'Email', 'themify' ),
			'label_submit' => __( 'Subscribe', 'themify' ),
			'message' => __( 'Success!', 'themify' ),
			'layout' => 'tb_optin_inline_block',
			'button_icon' => '',
			'gdpr_label' => __( 'I consent to my submitted data being collected and stored', 'themify' ),
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
							self::get_image('', 'b_i','bg_c','b_r','b_p','h')
						)
					)
				))
			)),
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_font_family('', 'f_f'),
							self::get_color_type(array(' label',' .tb_text_wrap'),'','f_c_t','f_c', 'f_g_c'),
							self::get_font_size('', 'f_s'),
							self::get_line_height('', 'l_h'),
							self::get_letter_spacing('', 'l_s'),
							self::get_text_align('', 't_a'),
							self::get_text_transform('', 't_t'),
							self::get_font_style('', 'f_st','f_w'),
							self::get_text_decoration('', 't_d_r'),
							self::get_text_shadow(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family('', 'f_f', 'h'),
							self::get_color_type(array(' label',' .tb_text_wrap'),'h'),
							self::get_font_size('', 'f_s','', 'h'),
							self::get_font_style('', 'f_st','f_w', 'h'),
							self::get_text_decoration('', 't_d_r', 'h'),
							self::get_text_shadow('','t_sh','h'),
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

		$labels = array(
			// Font
			self::get_seperator('f'),
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_font_family(' label', 'f_f_l'),
						self::get_color('.module label', 'f_c_l'),
						self::get_font_size(' label', 'f_s_l'),
						self::get_font_style(' label', 'f_st_l', 'f_fw_l'),
						self::get_text_shadow(' label', 't_sh_l'),
					)
				),
				'h' => array(
					'options' => array(
						self::get_font_family(' label', 'f_f_l','h'),
						self::get_color('.module label', 'f_c_l',null,null,'h'),
						self::get_font_size(' label', 'f_s_l','','h'),
						self::get_font_style(' label', 'f_st_l', 'f_fw_l', 'h'),
						self::get_text_shadow(' label', 't_sh_l','h'),
					)
				)
			))
		);

		$inputs = array(
			//background
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
							self::get_font_style(' input::placeholder', 'f_st_in_ph', 'f_fw_in_ph', 'h'),
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
					self::get_padding(' input', 'p_in')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' input', 'p_in', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' input', 'm_in')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' input', 'm_in', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' input', 'r_c_in')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' input', 'r_c_in', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' input', 'b_sh_in')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' input', 'b_sh_in', 'h')
						)
					)
				))
			))
		);
		
		$checkbox = array(
		    self::get_expand('bg', array(
			   self::get_tab(array(
				   'n' => array(
				   'options' => array(
						self::get_color(' input[type="checkbox"]', 'b_c_cb', 'bg_c', 'background-color'),
						self::get_color(' input[type="checkbox"]', 'f_c_cb'),
				   )
				   ),
				   'h' => array(
				   'options' => array(
						self::get_color(' input[type="checkbox"]', 'b_c_cb', 'bg_c', 'background-color','h'),
						self::get_color(' input[type="submit"]', 'f_c_cb',null,null,'h'),
				   )
				   )
			   ))
		    )),
		    // Border
		    self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' input[type="checkbox"]','b_cb')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' input[type="checkbox"]','b_cb','h')
					)
					)
				))
		    )),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' input[type="checkbox"]', 'p_cb')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' input[type="checkbox"]', 'p_cb', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' #commentform input[type="checkbox"]', 'm_cb')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' #commentform input[type="checkbox"]', 'm_cb', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' input[type="checkbox"]', 'r_c_cb')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' input[type="checkbox"]', 'r_c_cb', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' input[type="checkbox"]', 's_cb')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' input[type="checkbox"]', 's_cb', 'h')
						)
					)
				))
			))
		);

		$send_button = array(
			//background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_optin_submit button', 'bg_c_s', 'bg_c', 'background-color')
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_optin_submit button', 'bg_c_s', 'bg_c', 'background-color','h')
						)
					)
				))
			)),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_font_family(' .tb_optin_submit button', 'f_f_s'),
							self::get_color(' .tb_optin_submit button', 'f_c_s'),
							self::get_font_size(' .tb_optin_submit button', 'f_s_s'),
							self::get_font_style(' .tb_optin_submit button', 'f_s_st','f_s_w'),
							self::get_text_shadow(' .tb_optin_submit button', 't_sh_s_b'),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family(' .tb_optin_submit button', 'f_f_s','h'),
							self::get_color(' .tb_optin_submit button', 'f_c_s',null,null,'h'),
							self::get_font_size(' .tb_optin_submit button', 'f_s_s','','h'),
							self::get_font_style(' .tb_optin_submit button', 'f_s_st','f_s_w', 'h'),
							self::get_text_shadow(' .tb_optin_submit button', 't_sh_s_b','h'),
						)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border(' .tb_optin_submit button', 'b_s')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border(' .tb_optin_submit button', 'b_s','h')
						)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .tb_optin_submit button', 'p_sb')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .tb_optin_submit button', 'p_sb', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .tb_optin_submit button', 'm_sb')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .tb_optin_submit button', 'm_sb', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tb_optin_submit button', 'r_c_sb')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tb_optin_submit button', 'r_c_sb', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tb_optin_submit button', 'b_sh_sb')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tb_optin_submit button', 'b_sh_sb', 'h')
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
				'l' => array(
					'label' => __('Labels', 'themify'),
					'options' => $labels
				),
				'i' => array(
					'label' => __('Input Fields', 'themify'),
					'options' => $inputs
				),
				'cb' => array(
					'label' => __('Checkbox', 'themify'),
					'options' => $checkbox
				),
				's_b' => array(
					'label' => __('Subscribe Button', 'themify'),
					'options' => $send_button
				),
			)
		);
	}

	protected function _visual_template() {
		$module_args = self::get_module_args();
		?>
		<div class="module module-<?php echo $this->slug; ?> {{ data.css }} {{ data.layout }}">
			<# if ( data.mod_title ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title }}}<?php echo $module_args['after_title']; ?>
			<# } #>

			<form class="tb_optin_form" name="tb_optin" method="post">
				<# if ( ! data.fname_hide ) { #>
					<div class="tb_optin_fname">
						<label class="tb_optin_fname_text" contenteditable="false" data-name="label_firstname">{{{ data.label_firstname }}}</label>
                        <input type="text" name="tb_optin_fname" required="required" <# if(data.fn_placeholder){ #> placeholder="{{ data.fn_placeholder }}" <# } #>>
					</div>
				<# } #>
				<# if ( ! data.lname_hide ) { #>
					<div class="tb_optin_lname">
						<label class="tb_optin_lname_text" contenteditable="false" data-name="label_lastname">{{{ data.label_lastname }}}
							<input type="text" name="tb_optin_lname" required="required" <# if(data.ln_placeholder){ #> placeholder="{{ data.ln_placeholder }}" <# } #>>
						</label>
					</div>
				<# } #>
				<div class="tb_optin_email">
					<label class="tb_optin_email_text" contenteditable="false" data-name="label_email">{{{ data.label_email }}}
						<input type="email" name="tb_optin_email" required="required" <# if(data.email_placeholder){ #> placeholder="{{ data.email_placeholder }}" <# } #>>
					</label>
				</div>

				<# if ( data.gdpr == 'on' ) { #>
				<div class="tb_optin_gdpr">
					<label class="tb_optin_gdpr_text" contenteditable="false" data-name="gdpr_label">
						<input type="checkbox" name="tb_optin_gdpr" required="required">
						{{{ data.gdpr_label }}}
					</label>
				</div>
				<# } #>

				<div class="tb_optin_submit">
					<button>
                        <# if ( data.button_icon ) { #><em><# print(api.Helper.getIcon(data.button_icon).outerHTML)#></em><# } #>
                        <span contenteditable="false" data-name="label_submit">{{{ data.label_submit }}}</span>
                    </button>
				</div>
			</form>

		</div>
		<?php
	}

	/**
	 * Render plain content for static content.
	 * 
	 * @param array $module 
	 * @return string
	 */
	public function get_plain_content( $module ) {
		return '';
	}
}

new TB_Optin_Module();
