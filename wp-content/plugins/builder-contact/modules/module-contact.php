<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Contact
 */
class TB_Contact_Module extends Themify_Builder_Component_Module {

        public function __construct() {
            if(method_exists('Themify_Builder_Model', 'add_module')){
                parent::__construct('contact');
            }
            else{//backward
                 parent::__construct(array(
                    'name' =>$this->get_name(),
                    'slug' => 'contact',
                    'category' =>$this->get_group()
                ));
            }
        }

	public function get_assets() {
		return array(
			'css' =>Builder_Contact::$url . 'assets/style',
			'js' => Builder_Contact::$url . 'assets/scripts',
			'ver' => Builder_Contact::$version
		);
	}



        public function get_name(){
            return  __('Contact', 'builder-contact');
        }

	public function get_icon(){
	    return 'email';
	}

        public function get_group() {
            return array('addon');
        }

	public function get_options() {
        $url = Builder_Contact::$url;
		return array(
			array(
			    'id' => 'mod_title_contact',
			    'type' => 'title'
			),
			array(
				'id' => 'layout_contact',
				'type' => 'layout',
				'label' => __('Layout', 'builder-contact'),
				'options' => array(
					array('img' => $url . 'assets/style1.svg', 'value' => 'style1', 'label' => __('Style 1', 'builder-contact')),
					array('img' => $url . 'assets/style2.svg', 'value' => 'style2', 'label' => __('Style 2', 'builder-contact')),
					array('img' => $url . 'assets/style3.svg', 'value' => 'style3', 'label' => __('Style 3', 'builder-contact')),
					array('img' => $url . 'assets/style4.svg', 'value' => 'animated-label', 'label' => __('Animated Label', 'builder-contact'))
				)
			),
			array(
				'id' => 'mail_contact',
				'type' => 'text',
				'label' => __('Recipient(s)', 'builder-contact'),
				'help' => __( 'To send to multiple recipients, comma-separate the mail addresses.', 'builder-contact' ),
				'control' => false,
				'required' => [
					'message' => __( 'Please enter valid recipient(s) email address.', 'themify' ),
					'rule' => 'tb_contact_recipient',
				],
			),
			array(
				'type' => 'multi',
				'label' => '',
				'options' => array(
						array(
							'id' => 'send_to_admins',
							'type' => 'checkbox',
							'options' => array(
								array( 'name' => 'true', 'value' =>  __( 'Send to:', 'builder-contact'))
							),
							'binding' => array(
								'checked' => array( 'hide' =>'mail_contact' ),
								'not_checked' => array( 'show' =>'mail_contact' )
							)
						),
						array(
							'id' => 'user_role',
							'type' => 'select',
							'options' => array(
								'admin'=>__("Admin's email", 'builder-contact'),
								'author'=>__("Author's email", 'builder-contact'),
								'sr'=>__('Selective Recipients', 'builder-contact'),
							),
						),
				)
            ),
			array(
				'id' => 'specify_from_address',
				'label' => __('Specify From Address', 'builder-contact'),
				'help' => __( 'Use a custom "from" address in the mail header instead of sender&#39;s address', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' =>'specify_email_address' ),
					'not_checked' => array( 'hide' => 'specify_email_address' )
				),
				'control' => false
			),
			array(
				'id' => 'specify_email_address',
				'type' => 'text',
				'label' => __('From Address', 'builder-contact'),
				'control' => false
			),
			array(
				'id' => 'bcc_mail',
				'label' => __('Send to BCC', 'builder-contact'),
				'help' => __( 'Send mail as BCC (blind carbon copy), recipients do not see each other email address.', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => 'bcc_mail_contact' ),
					'not_checked' => array( 'hide' => 'bcc_mail_contact' )
				),
				'control' => false
			),
			array(
				'id' => 'bcc_mail_contact',
				'type' => 'text',
				'label' => __('BCC Addresses', 'builder-contact'),
				'help' => __( 'To send to multiple recipients, comma-separate the mail addresses.', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'post_type',
				'label' => __('Contact Posts', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => 'post_author' ),
					'not_checked' => array( 'hide' => 'post_author' )
				),
				'help'=>__('Enable this will create a copy of message as contact post in admin area.','builder-contact'),
				'control' => false
			),
			array(
				'id' => 'post_author',
				'type' => 'checkbox',
				'label' => '',
				'wrap_class' => '_tf-hide',
				'options' => array(
					array( 'name' => 'add', 'value' => __("Assign sender's email as post author", 'builder-contact') )
				),
				'control' => false
			),
			array(
				'id' => 'gdpr',
				'label' => __('GDPR', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'accept','value' =>'en'),
					'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' =>'gdpr_label' ),
					'not_checked' => array( 'hide' =>'gdpr_label' )
				)
			),
			array(
				'id' => 'gdpr_label',
				'type' => 'textarea',
				'label' => __( 'GDPR Message', 'builder-contact' )
			),
			array(
				'id' => 'success_url',
				'type' => 'url',
				'label' => __( 'Success URL', 'builder-contact' ),
				'help' =>  __( 'Redirect to this URL when the form is successfully sent.', 'builder-contact' ),
				'control' => false,
				'binding' => array(
				    'empty' => array( 'hide' => 'nw' ),
				    'not_empty' => array( 'show' => 'nw' ),
				)
			),
			array(
				'id' => 'nw',
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'1','value' =>'en'),
					'off' => array('name'=>'', 'value' =>'dis')
				),
				'control' => false,
				'label' => __( 'Open in New Window', 'builder-contact' )
			),
			array(
				'id' => 'success_message_text',
				'type' => 'textarea',
				'label' => __( 'Success Message', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'auto_respond',
				'label' => __('Auto Responder', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'help'=>__('Send an auto reply message when user submits the contact form.','builder-contact'),
				'binding' => array(
				    'checked' => array( 'show' => array( 'auto_respond_message', 'auto_respond_subject' ) ),
				    'not_checked' => array( 'hide' => array( 'auto_respond_message', 'auto_respond_subject' ) ),
				),
				'control' => false
			),
			array(
				'id' => 'auto_respond_subject',
				'type' => 'text',
				'label' => __( 'Auto Respond Subject', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'auto_respond_message',
				'type' => 'textarea',
				'label' => __( 'Auto Respond Message', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'default_subject',
				'type' => 'text',
				'label' => __( 'Default Subject', 'builder-contact' ),
				'help' =>  __( 'This will be used as the subject of the mail if the Subject field is not shown on the contact form.', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'contact_sent_from',
				'type' => 'checkbox',
				'label' => __( 'Sent From', 'builder-contact' ),
				'options' => array(
					array( 'name' => 'enable', 'value' => __( 'Include sent from (URL) in message', 'builder-contact' ) )
				),
				'default'=>'enable',
				'control' => false
			),
			array(
				'id' => 'include_name_mail',
				'type' => 'checkbox',
				'label' => __( 'Name & Email', 'builder-contact' ),
				'options' => array(
					array( 'name' => 'enable', 'value' => __( 'Include name and email in message', 'builder-contact' ) )
				)
			),
			array(
				'id' => 'fields_contact',
				'type' => 'contact_fields',
				'options'=>array(
				    'head'=>array(
						'f'=>__( 'Field', 'builder-contact' ),
						'l'=>__( 'Label', 'builder-contact' ),
						'sh'=>__( 'Show', 'builder-contact' ),
				    ),
				    'body'=>array(
						'name'=>__( 'Name', 'builder-contact' ),
						'email'=>__( 'Email', 'builder-contact' ),
						'subject'=>__( 'Subject', 'builder-contact' ),
						'message'=>__( 'Message', 'builder-contact' ),
						'recipients' => __( 'Recipients', 'builder-contact' ),
				    ),
				    'foot'=>array(
						'captcha'=>__( 'Captcha', 'builder-contact' ),
						'sendcopy'=>__( 'Send Copy', 'builder-contact' ),
						'optin' => __( 'Newsletter', 'builder-contact' ),
						'send'=>__( 'Send Button', 'builder-contact' ),
						'align'=>array(
							'id'=>'field_send_align',
							'label'=>__( 'Button Alignment', 'builder-contact' ),
							'options'=>array(
							'left'=>__( 'Left', 'builder-contact' ),
							'right'=>__( 'Right', 'builder-contact' ),
							'center'=>__( 'Center', 'builder-contact' )
							)
						)
				    )
				),
				'new_row'=>__( 'Add Field', 'builder-contact' )
			),
			array(
			    'id'=>'field_extra',
			    'type'=>'hidden'
			),
			array(
			    'id'=>'field_order',
			    'type'=>'hidden'
			),
			array(
				'id' => 'custom_template',
				'label' => __('Email Template', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array( 'name' => 'enable','value' => 'en' ),
				    'off' => array( 'name' => '', 'value' => 'dis' )
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'template', 'template_fields' ), 'hide' => array( 'include_name_mail', 'contact_sent_from' ) ),
					'not_checked' => array( 'hide' => array( 'template', 'template_fields' ), 'show' => array( 'include_name_mail', 'contact_sent_from' ) )
				),
				'control' => false,
				'help' => __( 'Create a custom email content template. Basic HTML tags are supported.', 'builder-contact' ),
			),
			array(
				'id' => 'template',
				'type' => 'textarea',
				'control' => false,
			),
			array(
				'id' => 'template_fields',
				'type' => 'separator',
				'html' =>
					'<div class="template_fields">
						<h4>' . __( 'Available Fields' ) . '</h5>
						<span>%name%</span>
						<span>%email%</span>
						<span>%subject%</span>
						<span>%message%</span>
						<span>%referer%</span>
						<div class="tb_contact_custom_tags"></div>
					</div>',
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_class_contact' ),
		);
	}

	public function get_live_default() {
		return array(
			'layout_contact' =>'style1',
			'field_name_label' =>__( 'Name', 'builder-contact' ),
			'field_email_label' => __( 'Email', 'builder-contact' ),
			'field_subject_label' => __( 'Subject', 'builder-contact' ),
			'field_message_label' => __( 'Message', 'builder-contact' ),
			'field_sendcopy_label' => __( 'Send a copy to myself', 'builder-contact' ),
			'field_sendcopy_subject' => __( 'COPY:', 'builder-contact' ),
			'field_send_label' => __( 'Send', 'builder-contact' ),
			'gdpr_label' => __( 'I consent to my submitted data being collected and stored', 'builder-contact' ),
			'field_name_require' => 'yes',
			'field_email_require' => 'yes',
			'field_name_active' => 'yes',
			'field_email_active' => 'yes',
			'field_subject_active' => 'yes',
			'field_subject_require' => 'yes',
			'field_message_active' => 'yes',
			'field_send_align' => 'left',
			'field_extra' => '{"fields":[]}',
			'field_order' => '{}',
			'field_optin_label' => __( 'Subscribe to my newsletter.', 'builder-contact' )
		);
	}

	public function get_styling() {
		$general = array(
		    //bacground
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
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(),
				    self::get_color_type(' label'),
				    self::get_font_size(),
				    self::get_font_style( '', 'f_fs_g', 'f_fw_g' ),
				    self::get_line_height(),
				    self::get_text_align(),
					self::get_text_shadow(),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family('', 'f_f', 'h'),
				    self::get_color_type(' label','h'),
				    self::get_font_size('', 'f_s', '', 'h'),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
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
				    self::get_font_family(array(' .control-label',' .tb_contact_label'),'font_family_labels'),
				    self::get_color(array(' .control-label',' .tb_contact_label'), 'font_color_labels'),
				    self::get_font_size(array(' .control-label',' .tb_contact_label'),'font_size_labels'),
				    self::get_font_style(array(' .control-label',' .tb_contact_label'),'f_fs_l', 'f_fw_l'),
					self::get_text_shadow(array(' .control-label',' .tb_contact_label'),'t_sh_l'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array(' .control-label',' .tb_contact_label'),'f_f_l','h'),
				    self::get_color(array(' .control-label',' .tb_contact_label'), 'f_c_l',null,null,'h'),
				    self::get_font_size(array(' .control-label',' .tb_contact_label'),'f_s_l','','h'),
					self::get_font_style(array(' .control-label:hover',' .tb_contact_label:hover'),'f_fs_l_h', 'f_fw_l_h',null,null,''),
					self::get_text_shadow(array(' .control-label',' .tb_contact_label'),'t_sh_l','h'),
				)
			    )
			))
		);

		$inputs = array(
		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'background_color_inputs', 'bg_c', 'background-color'),
				   )
			       ),
			       'h' => array(
				   'options' => array(
				         self::get_color(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'b_c_i', 'bg_c', 'background-color','h'),
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'font_family_inputs'),
				    self::get_color(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'font_color_inputs'),
				    self::get_font_size(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'font_size_inputs'),
				    self::get_font_style(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'f_fs_i', 'f_fw_i'),
					self::get_text_shadow(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'t_sh_i'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'f_f_i','h'),
				    self::get_color(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'f_c_i',null,null,'h'),
				    self::get_font_size(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'f_s_i','','h'),
					self::get_font_style(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'f_fs_i', 'f_fw_i', 'h'),
					self::get_text_shadow(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'t_sh_i','h'),
				)
			    )
			))
		    )),
		    self::get_expand('Placeholder', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(array(' input[type="text"]::placeholder', ' input[type="email"]::placeholder', ' input[type="number"]::placeholder', ' textarea::placeholder', ' select::placeholder', ' input[type="tel"]::placeholder' ),'f_f_in_ph'),
				    self::get_color(array(' input[type="text"]::placeholder', ' input[type="email"]::placeholder', ' input[type="number"]::placeholder', ' textarea::placeholder', ' select::placeholder', ' input[type="tel"]::placeholder' ), 'f_c_in_ph'),
				    self::get_font_size(array(' input[type="text"]::placeholder', ' input[type="email"]::placeholder', ' input[type="number"]::placeholder', ' textarea::placeholder', ' select::placeholder', ' input[type="tel"]::placeholder' ),'f_s_in_ph'),
				    self::get_font_style(array(' input[type="text"]::placeholder', ' input[type="email"]::placeholder', ' input[type="number"]::placeholder', ' textarea::placeholder', ' select::placeholder', ' input[type="tel"]::placeholder' ),'f_fs_in_ph', 'f_fw_in_ph'),
					self::get_text_shadow(array(' input[type="text"]::placeholder', ' input[type="email"]::placeholder', ' input[type="number"]::placeholder', ' textarea::placeholder', ' select::placeholder', ' input[type="tel"]::placeholder' ),'t_sh_in_ph'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]:hover::placeholder', ' input[type="email"]:hover::placeholder', ' input[type="number"]:hover::placeholder', ' textarea:hover::placeholder', ' select:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'f_f_in_ph_h',''),
				    self::get_color(array( ' input[type="text"]:hover::placeholder', ' input[type="email"]:hover::placeholder', ' input[type="number"]:hover::placeholder', ' textarea:hover::placeholder', ' select:hover::placeholder', ' input[type="tel"]:hover::placeholder' ), 'f_c_in_ph_h',null,null,''),
				    self::get_font_size(array( ' input[type="text"]:hover::placeholder', ' input[type="email"]:hover::placeholder', ' input[type="number"]:hover::placeholder', ' textarea:hover::placeholder', ' select:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'f_s_in_ph_h','',''),
					self::get_font_style(array( ' input[type="text"]:hover::placeholder', ' input[type="email"]:hover::placeholder', ' input[type="number"]:hover::placeholder', ' textarea:hover::placeholder', ' select:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'f_fs_in_ph', 'f_fw_in_ph', 'h'),
					self::get_text_shadow(array( ' input[type="text"]:hover::placeholder', ' input[type="email"]:hover::placeholder', ' input[type="number"]:hover::placeholder', ' textarea:hover::placeholder', ' select:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'t_sh_in_ph_h',''),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'border_inputs')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ),'b_i','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_p', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_m', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(array( ' input[type="text"]', ' input[type="email"]', ' input[type="number"]', ' textarea', ' select', ' input[type="tel"]', ' input[type="file"]' ), 'in_b_sh', 'h')
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
						self::get_margin(' input[type="checkbox"]', 'm_cb')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' input[type="checkbox"]', 'm_cb', 'h')
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

		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(' .builder-contact-field-send button', 'background_color_send', 'bg_c', 'background-color')
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .builder-contact-field-send button', 'background_color_send', 'bg_c', 'background-color','h')
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(' .builder-contact-field-send button' ,'font_family_send'),
				    self::get_color( ' .builder-contact-field-send button', 'font_color_send'),
				    self::get_font_size( ' .builder-contact-field-send button','font_size_send'),
				    self::get_font_style( ' .builder-contact-field-send button','f_fs_s', 'f_fw_s'),
					self::get_text_shadow(' .builder-contact-field-send button' ,'t_sh_b'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(' .builder-contact-field-send button' ,'f_f_s','h'),
				    self::get_color( ' .builder-contact-field-send button', 'f_c_s',null,null,'h'),
				    self::get_font_size( ' .builder-contact-field-send button','f_s_s','','h'),
					self::get_font_style( ' .builder-contact-field-send button','f_fs_s', 'f_fw_s', 'h'),
					self::get_text_shadow(' .builder-contact-field-send button' ,'t_sh_b','h'),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(' .builder-contact-field-send button','border_send')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(' .builder-contact-field-send button','b_s','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .builder-contact-field-send button', 'p_sd')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .builder-contact-field-send button', 'p_sd', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .builder-contact-field-send button', 'r_c_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .builder-contact-field-send button', 'r_c_sd', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .builder-contact-field-send button', 's_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .builder-contact-field-send button', 's_sd', 'h')
						)
					)
				))
			))
		);

		$success_message = array(
			self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
					self::get_color(' .contact-success', 'background_color_success_message','bg_c', 'background-color')
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .contact-success', 'b_c_s_m','bg_c', 'background-color','h')
				   )
			       )
			   ))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .contact-success','font_family_success_message'),
					self::get_color(' .contact-success', 'font_color_success_message'),
					self::get_font_size(' .contact-success','font_size_success_message'),
					self::get_font_style(' .contact-success','f_fs_m', 'f_fw_m'),
					self::get_line_height(' .contact-success','line_height_success_message'),
					self::get_text_align(' .contact-success','text_align_success_message'),
                    self::get_text_shadow(' .contact-success','t_sh_m'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .contact-success','f_f_s_m','h'),
					self::get_color(' .contact-success', 'f_c_s_m',null,null,'h'),
					self::get_font_size(' .contact-success','f_s_s_m','','h'),
					self::get_font_style(' .contact-success','f_fs_m', 'f_fw_m', 'h'),
                    self::get_text_shadow(' .contact-success','t_sh_m','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .contact-success','padding_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .contact-success','p_s_m','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin(' .contact-success','margin_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .contact-success','m_s_m','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .contact-success','border_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .contact-success','b_s_m','h')
				    )
				)
			    ))
			))

		);

		$error_message = array(

			self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
					self::get_color(' .contact-error', 'background_color_error_message', 'bg_c', 'background-color'),
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .contact-error', 'b_c_e_m', 'bg_c', 'background-color','h'),
				   )
			       )
			   ))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .contact-error','font_family_error_message'),
					self::get_color(' .contact-error', 'font_color_error_message'),
					self::get_font_size(' .contact-error','font_size_error_message'),
					self::get_font_style(' .contact-error','f_fs_e', 'f_fw_e'),
					self::get_line_height(' .contact-error','line_height_error_message'),
					self::get_text_align(' .contact-error','text_align_error_message'),
                    self::get_text_shadow(' .contact-error','t_sh_e_m'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .contact-error','f_f_e_m'),
					self::get_color(' .contact-error', 'f_c_e_m',null,null,'h'),
					self::get_font_size(' .contact-error','f_s_e_m','','h'),
					self::get_font_style(' .contact-error','f_fs_e', 'f_fw_e', 'h'),
                    self::get_text_shadow(' .contact-error','t_sh_e_m','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .contact-error','padding_error_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .contact-error','p_e_m','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin(' .contact-error','margin_error_message'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .contact-error','m_e_m','h'),
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .contact-error','border_error_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .contact-error','b_e_m','h')
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
					'label' => __('Field Labels', 'builder-contact'),
					'options' => $labels
				),
				'i' => array(
					'label' => __('Input Fields', 'builder-contact'),
					'options' => $inputs
				),
				'cb' => array(
					'label' => __('Checkbox', 'builder-contact'),
					'options' => $checkbox
				),
				's_b' => array(
					'label' => __('Send Button', 'builder-contact'),
					'options' => $send_button
				),
				's_m' => array(
					'label' => __('Success Message', 'builder-contact'),
					'options' => $success_message
				),
				'e_m' => array(
					'label' => __('Error Message', 'builder-contact'),
					'options' => $error_message
				)
			)
		);

	}

	protected function _visual_template() {
		$module_args = self::get_module_args('mod_title_contact');?>
		<#
		const def ={
		    field_email_active:'yes',
		    field_name_active:'yes',
		    field_message_active:'yes'
		},
		orders={},
		isAnimated=data.layout_contact=='animated-label',
		id=data.cid,
		vals=Object.assign(def,data);
		try{
		    field_extra =typeof vals.field_extra=='string'? JSON.parse(vals.field_extra):vals.field_extra;
		    field_extra=field_extra.fields;
		} catch( e ){
			field_extra = {};
		}
		try{
			field_order =typeof vals.field_order=='string'? JSON.parse(vals.field_order):vals.field_order;
		} catch( e ){
			field_order = {}
		}
		if(!field_extra){
		    field_extra={};
		}
		if(!field_order){
		    field_order={};
		}
		/* set default order */
		if ( Object.keys( field_order ).length === 0 ) {
			field_order = { field_name_label: 0, field_email_label: 1, field_subject_label: 2, field_message_label: 3 };
		}

		[ 'name', 'email', 'subject', 'message' ].forEach( function( v, i ) {
			if ( vals['field_' + v + '_active'] == 'yes' ) {
				orders[ field_order['field_' + v + '_label' ] ] = v;
			}
		} );
		const selective_recipient = data.send_to_admins && data.send_to_admins === 'true' && data.user_role === 'sr';
		if ( selective_recipient ) {
			orders[ field_order['field_recipients_label' ] ] = 'recipients';
		}

		for (let i in field_extra ) {
			orders[ field_extra[i].order ] = i;
		}
		#>
		<div class="module module-<?php echo $this->slug; ?> {{ vals.css_class_contact }} contact-{{ vals.layout_contact }}">
			<# if( vals.mod_title_contact ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ vals.mod_title_contact }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<form class="builder-contact" method="post">
				<div class="contact-message"></div>

				<div class="builder-contact-fields tf_rel">
				    <# for(i in orders){
						let k=orders[i];
						if ( k=='name' || k=='email' || k=='subject' || k=='message' || k === 'recipients' ){
							if ( k === 'recipients' && ! selective_recipient ) {
								continue;
							}
							let prefix ='field_'+k,
								required = k === 'recipients' || ('yes' == vals[prefix+'_active'] && 'yes' == vals[prefix+'_require'])?' required':'',
								inputName='contact-'+k,
								inputId=id+'-'+inputName,
								placeholder=!isAnimated?vals[prefix+'_placeholder']:' ';
						#>
							<div class="builder-contact-field builder-contact-field-{{k}}<#print((k=='message'?' builder-contact-textarea-field':' builder-contact-text-field'))#>">
								<label class="control-label" for="{{inputId}}">
									<# if ( vals[ k + '_icon'] ){ #><em><# print( api.Helper.getIcon( vals[ k + '_icon'] ).outerHTML ) #></em><# } #>
									<span class="tb-label-span" contenteditable="false" data-name="{{prefix}}_label"><# if (vals[prefix+'_label'] !== ''){#>{{vals[prefix+'_label']}}</span><#if ( required ){ #><span class="required">*</span><# }} #>
								</label>
								<div class="control-input tf_rel">
									<# if ( k === 'recipients' ) { #>
										<# if ( data.sr_display === 'select' ) { #><select><# } #>
											<# for ( let i in data.sr ) { #>
												<# if ( data.sr_display === 'select' ) { #>
													<option>{{ data.sr[ i ]['label'] }}</option>
												<# } else { #>
													<label><input type="radio">{{ data.sr[ i ]['label'] }}</label>
												<# } #>
											<# } #>
										<# if ( data.sr_display === 'select' ) { #></select><# } #>
									<# } else if ( k == 'message' ) { #>
										<textarea name="{{inputName}}" placeholder="{{placeholder}}" id="{{inputId}}" class="form-control" required></textarea>
								   <# } else{#>
										<input type="text" name="{{inputName}}" placeholder="{{placeholder}}" id="{{inputId}}" class="form-control"<#{{required}}#>>
									<# }
									if(isAnimated){#>
										<span class="tb_contact_label">
											<span class="tb-label-span" contenteditable="false" data-name="{{prefix}}_label"><# if (vals[prefix+'_label'] !== ''){ #>{{vals[prefix+'_label']}}</span><# if ( required ){ #><span class="required">*</span><# }} #>
										</span>
									<#}#>
								</div>
							</div>
						<#
						}
						else{
							let field = field_extra[ k ];

							if(!field){
								continue;
							}
							let type=field.type,
							value=(field.value!==undefined && field.value!=='')?(typeof field.value=='string'?field.value.replace(/\\\\"/g,'"').replace(/\\\\n/g,'\n'):field.value):'',
							label=(field.label!==undefined && field.label!=='')?field.label:'',
							inputName='field_extra_'+k,
							inputId='field_extra_'+id+'_'+k,
							required=true === field.required?' required':'',
							placeholder=!isAnimated && 'upload' != type?value:' ';
							field.icon = field.icon || '';

						#>
							<div class="builder-contact-field builder-contact-field-extra<# if(type=='tel'){#> builder-contact-text-field<#}#> builder-contact-{{type}}-field">
								<label class="control-label" for="{{inputId}}">
									<# if ( field.icon ){ #><em><# print( api.Helper.getIcon( field.icon ).outerHTML ) #></em><# } #>
									<span contenteditable="false" data-name="1">{{label}}</span>
									<# if(required){#>
										<span class="required">*</span>
									<#}#>
								</label>
								<div class="control-input tf_rel">
									<# if( 'textarea' == type ){#>
										<textarea name="{{inputName}}" id="{{inputId}}" placeholder="{{placeholder}}" class="form-control"{{required}}></textarea>
									<#
									}
									else if( 'text' == type || 'tel' == type || 'upload' == type || 'email' === type || 'number' === type ){#>
										<input type="<#print((type=='upload'?'file':type))#>" name="{{inputName}}" id="{{inputId}}" placeholder="{{placeholder}}" class="form-control"{{required}}>
									<#
									}
									else if( 'static' == type ){
										 print(value);
									}
									else if(value){
										if( 'radio' == type  || type=='checkbox'){
											let count=value.length;
											for(let j in value){ #>
												<label>
													<input type="{{type}}" name="{{inputName}}<#if(type=='checkbox'){#>[]<#}#>" value="{{value[j]}}" class="form-control" <# if(required!='' && (type=='radio' || count===1)){#> {{required}} <#}#>>
													<span contenteditable="false" data-name="1">{{value[j]}}</span>
												</label>
										<#	}
										}
										else if( 'select' == type ){#>
											<select id="{{inputId}}" name="{{inputName}}" class="form-control tf_scrollbar"{{required}}>
												<# if ( ! required ) { #>
													<option></option>
												<#}
												for(let j in value){#>
													<option value="{{value[j]}}">{{value[j]}}</option>
												<#}#>
											</select>
										<# }
									}
									if(isAnimated && ('text' == type || 'tel' == type || 'textarea' == type)){#>
										<span class="tb_contact_label">
											<span contenteditable="false" data-name="1">{{label}}</span>
											<#if( required){#>
												<span class="required">*</span>
											<#}#>
										</span>
									<#}#>
								</div>
							</div>
						<#
						}
					}

					if( vals.field_captcha_active == 'yes' ) { #>
                        <?php $recaptcha_version = Builder_Contact::get_option('version'); ?>
                        <# let recaptcha_version = '<?php echo esc_attr($recaptcha_version,'v2'); ?>'; #>
						<div class="builder-contact-field builder-contact-field-captcha">
                            <# if( '' !== vals.field_captcha_label && undefined !== vals.field_captcha_label ) { #>
                            <label class="control-label">
								<span contenteditable="false" data-name="field_captcha_label">{{{ vals.field_captcha_label }}}</span>
								<span class="required">*</span>
							</label>
                            <# } #>
                            <div class="control-input tf_rel">
								 <div class="themify_captcha_field<?php echo 'v2'===$recaptcha_version?' g-recaptcha':''; ?>" data-sitekey="<?php echo esc_attr( Builder_Contact::get_option( 'public_key' ) ); ?>" data-ver="<?php echo esc_attr($recaptcha_version); ?>"></div>
							</div>
						</div>
					<# }
					if( vals.field_sendcopy_active=='yes' ) { #>
					<div class="builder-contact-field builder-contact-field-sendcopy">
						<div class="control-label">
							<div class="control-input tf_rel">
								<label class="send-copy">
									<input type="checkbox" name="send-copy" value="1">
									<span contenteditable="false" data-name="field_sendcopy_label">{{{vals.field_sendcopy_label}}}</span>
								</label>
							</div>
						</div>
					</div>
					<# }
					if ( vals.field_optin_active == 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-optin">
						<div class="control-label">
							<div class="control-input tf_rel">
								<label class="optin">
									<input type="checkbox" name="optin" value="1">
									<span contenteditable="false" data-name="field_optin_label">{{{vals.field_optin_label}}}</span>
								</label>
							</div>
						</div>
					</div>
					<# }
					if( 'accept' ==vals.gdpr ) { #>
					<div class="builder-contact-field builder-contact-field-gdpr">
						<div class="control-label">
							<div class="control-input tf_rel">
								<label class="field-gdpr">
									<input type="checkbox" name="gdpr" value="1" required>
									<span contenteditable="false" data-name="gdpr_label">{{{vals.gdpr_label}}}</span>
									<span class="required">*</span>
								</label>
							</div>
						</div>
					</div>
					<# }
					const send_align=undefined===data.field_send_align?'l':vals.field_send_align; #>
					<div class="builder-contact-field builder-contact-field-send control-input tf_text{{ send_align[0] }} tf_rel tf_clear">
                        <button type="submit" class="btn btn-primary" contenteditable="false" data-name="field_send_label"><span class="tf_submit_icon"><# if ( vals.send_icon ){ print( api.Helper.getIcon( vals.send_icon ).outerHTML ) } #></span> {{{ vals.field_send_label }}}</button>
					</div>
				</div>
			</form>
		</div>
	<?php
	}
}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Contact_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Contact_Module');
}
