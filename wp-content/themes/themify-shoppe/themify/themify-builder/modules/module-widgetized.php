<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Widgetized
 * Description: Display any registered sidebar
 */

class TB_Widgetized_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        parent::__construct('widgetized');
    }
    
    public function get_name(){
        return __('Widgetized', 'themify');
    }
    
    public function get_options() {
        return array(
            array(
                'id' => 'mod_title_widgetized',
                'type' => 'title'
            ),
            array(
                'id' => 'sidebar_widgetized',
                'type' => 'select',
                'label' => __('Widgetized Area', 'themify'),
		'sidebars'=>true
            ),
			array( 'type' => 'custom_css_id', 'custom_css' => 'custom_css_widgetized' ),
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
                        self::get_font_family(),
                        self::get_color('', 'font_color'),
                        self::get_font_size(),
                        self::get_line_height(),
                        self::get_letter_spacing(),
                        self::get_text_align(),
                        self::get_text_transform(),
                        self::get_font_style(),
                        self::get_text_decoration('', 'text_decoration_regular'),
						self::get_text_shadow(),
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family('', 'f_f', 'h'),
                        self::get_color('', 'f_c',  null, null, 'h'),
                        self::get_font_size('', 'f_s', '', 'h'),
                        self::get_font_style('', 'f_st', 'f_w', 'h'),
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
			// Display
			self::get_expand('disp', self::get_display())
        );

        $widgetized_container = array(
            // Background
               self::get_expand('bg', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_color(' .widget', 'b_c_c', 'bg_c', 'background-color')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_color(' .widget', 'b_c_c', 'bg_c', 'background-color', 'h')
                    )
                )
            ))
		   )),
            // Padding
               self::get_expand('p', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_padding(' .widget', 'p_c')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_padding(' .widget', 'p_c', 'h')
                    )
                )
            ))
		   )),
            // Margin
               self::get_expand('m', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_margin(' .widget', 'm_c')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_margin(' .widget', 'm_c', 'h')
                    )
                )
            ))
		   )),
            // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .widget', 'b_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .widget', 'b_c', 'h')
			)
		    )
		))
	    ))
        );

        $widgetized_title = array(
            // Font
            self::get_seperator('f'),
            self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family('.module .widgettitle', 'f_f_w_t'),
			self::get_color('.module .widgettitle', 'f_c_w_t'),
			self::get_font_size('.module .widgettitle', 'f_s_w_t'),
			self::get_line_height('.module .widgettitle', 'l_h_w_t'),
			self::get_letter_spacing('.module .widgettitle', 'l_s_w_t'),
			self::get_text_align('.module .widgettitle', 't_a_w_t'),
			self::get_text_transform('.module .widgettitle', 't_t_w_t'),
			self::get_font_style('.module .widgettitle', 'f_sy_w_t', 'f_b_w_t'),
			self::get_text_decoration('.module .widgettitle', 't_d_w_t'),
			self::get_text_shadow('.module .widgettitle', 't_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family('.module .widgettitle', 'f_f_w_t','h'),
			self::get_color('.module .widgettitle', 'f_c_w_t',  null, null, 'h'),
			self::get_font_size('.module .widgettitle', 'f_s_w_t','','h'),
			self::get_font_style('.module .widgettitle', 'f_sy_w_t', 'f_b_w_t','h'),
			self::get_text_decoration('.module .widgettitle', 't_d_w_t','h'),
			self::get_text_shadow('.module .widgettitle', 't_sh_t','h'),
		    )
		)
	    ))
        );
        return array(
            'type' => 'tabs',
            'options' =>array(
                'g' => array(
                    'options' => $general
                ),
                'm_t' => array(
                    'options' => $this->module_title_custom_style()
                ),
                'wt' => array(
                    'label' => __('Widgetized Title', 'themify'),
                    'options' => $widgetized_title
                ),
                'wc' => array(
                    'label' => __('Widgetized Container', 'themify'),
                    'options' => $widgetized_container
                )
            )
        );
    }

}

new TB_Widgetized_Module();
