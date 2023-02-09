<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Twitter
 * Description: Twitter.com integration
 */
class TB_Twitter_Module extends Themify_Builder_Component_Module {

	public function __construct() {
            parent::__construct('twitter');
	}
        
	public function get_name(){
            return __('Twitter', 'themify');
        }
    
	public function get_icon(){
		return 'twitter';
	}

	public function get_assets() {
		return array(
		    'css' => 1
		);
	}

	public function get_options() {
		return array(
			array(
				'id' => 'title',
				'type' => 'title'
			),
			array(
				'id' => 'username',
				'type' => 'text',
				'label' => __( 'Username', 'themify' ),
				'required' => [
					'message' => __( 'Please enter username.', 'themify' )
				],
			),
			array(
				'id' => 'limit',
				'type' => 'number',
				'label' => __( 'Number of Posts', 'themify' ),
			),
			array(
				'id'      => 'time',
				'type'    => 'toggle_switch',
				'label'   => __( 'Timestamps', 'tbp'),
				'options'   => array(
					'off' => array( 'name' => '0', 'value' => 'hi' ),
					'on'  => array( 'name' => '1', 'value' => 's' ),
				),
			),
			array(
				'id'      => 'show_follow',
				'type'    => 'toggle_switch',
				'label'   => __( 'Follow Link', 'tbp'),
				'options'   => array(
					'off' => array( 'name' => '0', 'value' => 'hi' ),
					'on'  => array( 'name' => '1', 'value' => 's' ),
				),
				'binding' => [
					'0' => [ 'hide' => 'follow' ],
					'1' => [ 'show' => 'follow' ],
				],
			),
			array(
				'id'      => 'follow',
				'type'    => 'text',
				'label'   => __( 'Follow Text', 'tbp'),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'custom_css' ),
		);
	}

	public function get_live_default() {
		return array(
			'limit' => 5,
			'time' => '1',
			'follow' => __( '&rarr; Follow me', 'themify' ),
		);
	}

	public function get_styling() {
		$general = array(
		    //background
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
				    self::get_color_type(),
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
				    self::get_color_type('','h'),
				    self::get_font_size('', 'f_s', '', 'h'),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
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
		);

		$items = array(
			// Background
			self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' li', 'i_bg', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' li:hover', 'i_bg_h', 'bg_c', 'background-color')
				)
				)
			))
			)),
			// Font
			self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' li', 'i_f'),
					self::get_color(' li', 'i_c'),
					self::get_font_size(' li', 'i_fs'),
					self::get_line_height(' li', 'i_l'),
					self::get_text_transform(' li', 'i_lt'),
					self::get_font_style(' li', 'i_s', 'i_s_b'),
					self::get_text_decoration(' li', 'i_td'),
					self::get_text_shadow(' li', 'i_ts')
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' li:hover', 'i_f_h'),
					self::get_color(' li:hover', 'i_c_h'),
					self::get_font_size(' li:hover', 'i_fs_h'),
					self::get_font_style(' li:hover', 'i_s_h', 'i_s_b_h'),
					self::get_text_decoration(' li:hover', 'i_td_h'),
					self::get_text_shadow(' li:hover', 'i_ts_h')
				)
				)
			))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' li', 'i_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' li:hover', 'i_p_h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' li', 'i_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' li:hover', 'i_m_h')
				)
				)
			))
			
			)),
			// Border
			self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' li', 'i_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' li:hover', 'i_b_h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' li', 'i_rc')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' li:hover', 'i_rc_h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' li', 'i_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' li:hover', 'i_sh_h')
						)
					),
				))
			))
		);

		$follow = array(
			// Background
			self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .tb_twitter_follow', 'f_bg', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .tb_twitter_follow:hover', 'f_bg_h', 'bg_c', 'background-color')
				)
				)
			))
			)),
			// Font
			self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .tb_twitter_follow', 'f_f'),
					self::get_color(' .tb_twitter_follow', 'f_c'),
					self::get_font_size(' .tb_twitter_follow', 'f_fs'),
					self::get_line_height(' .tb_twitter_follow', 'f_l'),
					self::get_text_transform(' .tb_twitter_follow', 'f_lt'),
					self::get_font_style(' .tb_twitter_follow', 'f_s', 'f_s_b'),
					self::get_text_decoration(' .tb_twitter_follow', 'f_td'),
					self::get_text_shadow(' .tb_twitter_follow', 'f_ts')
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .tb_twitter_follow:hover', 'f_f_h'),
					self::get_color(' .tb_twitter_follow:hover', 'f_c_h'),
					self::get_font_size(' .tb_twitter_follow:hover', 'f_fs_h'),
					self::get_font_style(' .tb_twitter_follow:hover', 'f_s_h', 'f_s_b_h'),
					self::get_text_decoration(' .tb_twitter_follow:hover', 'f_td_h'),
					self::get_text_shadow(' .tb_twitter_follow:hover', 'f_ts_h')
				)
				)
			))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .tb_twitter_follow', 'f_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .tb_twitter_follow:hover', 'f_p_h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .tb_twitter_follow', 'f_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .tb_twitter_follow:hover', 'f_m_h')
				)
				)
			))
			
			)),
			// Border
			self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .tb_twitter_follow', 'f_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .tb_twitter_follow:hover', 'f_b_h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tb_twitter_follow', 'f_rc')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tb_twitter_follow:hover', 'f_rc_h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tb_twitter_follow', 'f_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tb_twitter_follow:hover', 'f_sh_h')
						)
					),
				))
			))
		);

		return [
			'type' => 'tabs',
			'options' => [
				'g' => [
					'options' => $general
				],
				'm_t' => array(
					'options' => $this->module_title_custom_style()
				),
				'i' => [
					'label' => __( 'Items', 'themify' ),
					'options' => $items,
				],
				'f' => [
					'label' => __( 'Follow Link', 'themify' ),
					'options' => $follow,
				],
			],
		];
	}

	

	public function get_category() {
	    return array( 'general' );
	}
}

new TB_Twitter_Module();