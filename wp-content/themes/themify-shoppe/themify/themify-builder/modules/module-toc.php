<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Box
 * Description: Display box content
 */

class TB_Toc_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        parent::__construct('toc');
    }

    public function get_name(){
	return __('Table of Content', 'themify');
    }

    public function get_icon(){
	return 'layout-width-full';
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
                'id' => 'm_t',
                'type' => 'title'
            ),
            self::get_tab(array(
		'in'=>array(
		    'label'=>__('Include','themify'),
		    'options'=>array(
			array(
			    'id'=>'in_tags',
			    'label'=>__('Anchors By Tags','themify'),
			    'type'=>'checkbox',
			    'options'=>array(
				array('name' => 'h1', 'value' =>'H1'),
				array('name' => 'h2', 'value' =>'H2'),
				array('name' => 'h3', 'value' =>'H3'),
				array('name' => 'h4', 'value' =>'H4'),
				array('name' => 'h5', 'value' =>'H5'),
				array('name' => 'h6', 'value' =>'H6'),
			    )
			),
			array(
			    'id'=>'in_cont',
			    'label'=>__('Container','themify'),
			    'type'=>'select',
			    'options'=>array(
				'b'=>__('Current Builder','themify'),
				'b_c'=>__('Builder Parent Container','themify'),
				'r'=>__('In Nested Row','themify'),
				'c'=>__('In Nested Column','themify'),
				'doc'=>__('All Document','themify'),
				'cust'=>__('Custom','themify')
			    ),
			    'binding' => array(
				'select' => array('hide' =>'tb_custom_toc'),
				'cust' => array('show' =>'tb_custom_toc')
			    )
			),
			array(
			    'id'=>'in_custom',
			    'label'=>__('Custom Container','themify'),
			    'wrap_class'=>'tb_custom_toc',
			    'type'=>'text',
			    'control'=>array(
				'event'=>'change'
			    ),
			    'help'=>__('Enter ID (eg. "#content") or CSS class (eg. ".wrapper") of the container','themify')
			),
			array(
			    'id' => 'min',
			    'type' => 'number',
			    'min'=>2,
			    'label' => __('Minimum Tags', 'themify'),
			    'help' => __('Only show when the minimum number of tags are present', 'themify'),
			),
			array(
			    'id' => 'maxt',
			    'type' => 'number',
			    'label' => __('Max Anchor Text Length', 'themify')
			),
			array(
			    'id' => 'maxh',
			    'type' => 'number',
			    'min'=>5,
			    'label' => __('Generated Hash Length', 'themify'),
			     'help' => __('Maximum characters of hashtag (anchor)', 'themify'),
			),
		    )
		),
		'ex'=>array(
				'label'=>__('Exclude','themify'),
				'options'=>array(
				array(
					'id' => 'ex_m_t',
					'label'=>__('Exclude Builder Modules Title','themify'),
					'type' => 'toggle_switch',
					'options' => 'simple'
				),
				array(
					'id'=>'ex_tags',
					'label'=>__('Exclude Headings','themify'),
					'type'=>'text',
					'control'=>array(
						'event'=>'change'
					),
					'help'=>__('Enter CSS class (eg. ".exclude-heading") of the heading tag (h1 - h6)"','themify')
				)
		    )
		)
	    ),true),
	    array(
		'type'=>'separator',
		'label' => __('Advanced','themify')
	    ),
	    array(
		'id' => 'mark',
		'type' => 'select',
		'label'=>__('List Bullets','themify'),
		'options'=>array(
		    'none'=>__('None','themify'),
		    'b'=>__('Bullets','themify'),
		    'c'=>__('Circle','themify'),
		    's'=>__('Square','themify'),
		    'ur'=>__('Upper Roman','themify'),
		    'lr'=>__('Lower Roman','themify'),
		    'ic'=>__('Icon','themify'),
		),
		'binding' => array(
		    'select' => array(
                        'hide' => 'tb_toc_ic'
                    ),
		    'ic' => array(
                        'show' => 'tb_toc_ic'
                    )
		)
	    ),
	    array(
		'id'=> 'ic',
		'label'=>__('Icon','themify'),
		'type'=>'icon',
		'wrap_class' => 'tb_toc_ic'
	    ),
	    array(
		'id' => 'num',
		'type' => 'toggle_switch',
		'label' => __('Show Numbers', 'themify'),
		'options' => 'simple'
	    ),
	    array(
		'id' => 'tree',
		'type' => 'toggle_switch',
		'label' => __('Hierarchical', 'themify'),
		'options' => 'simple',
		'binding' => array(
		    'checked' => array( 'show' => 'tb_toc_colapse' ) ,
		    'not_checked' => array( 'hide' => 'tb_toc_colapse' ),
		)
	    ),
	    array(
		'id' => 'minimize',
		'type' => 'toggle_switch',
		'label'   => __('Minimize', 'themify'),
		'options' => 'simple',
		'binding' => array(
		    'checked' => array( 'show' => 'tb_toc_min_ic' ) ,
		    'not_checked' => array( 'hide' => 'tb_toc_min_ic' ),
		)
	    ),
	    array(
		'type'=>'group',
		'wrap_class' => 'tb_toc_min_ic',
		'options'=>array(
		    array(
			'id'=> 'mic',
			'label'=>__('Icon','themify'),
			'type'=>'icon'
		    ),
		    array(
			'id'=> 'mmic',
			'label'=>__('Minimize Icon','themify'),
			'type'=>'icon'
		    ),
		    array(
			'id'=> 'bp',
			'label'=>__('Minimized On','themify'),
			'type'=>'select',
			'options'=>array(
			    'tl'=>__('Tablet Landscape','themify'),
			    't'=>__('Tablet','themify'),
			    'm'=>__('Mobile','themify'),
			    'n'=>__('None','themify'),
			),
		    ),
		)
	    ),
	    array(
		'type'=>'group',
		'wrap_class' => 'tb_toc_colapse',
		'options'=>array(
		    array(
			'id' => 'colapse',
			'type' => 'toggle_switch',
			'label' => __('Collapse SubItems', 'themify'),
			'options' => 'simple',
			'binding' => array(
			    'checked' => array( 'show' => 'tb_toc_colapse_ic' ) ,
			    'not_checked' => array( 'hide' => 'tb_toc_colapse_ic' ),
			)
		    ),
		    array(
			'type'=>'group',
			'wrap_class' => 'tb_toc_colapse_ic',
			'options'=>array(
			    array(
				'id'=> 'cic',
				'label'=>__('Child Icon','themify'),
				'type'=>'icon'
			    ),
			    array(
				'id'=> 'cmic',
				'label'=>__('Child Minimize Icon','themify'),
				'type'=>'icon'
			    ),
			)
		    )
		)
	    ),
	    array( 'type' => 'custom_css_id', 'custom_css' => 'css' ),
        );
    }

    public function get_live_default() {
        return array(
            'in_tags' =>'h1|h2|h3|h4|h5|h6',
	    'tree'=>'yes',
	    'colapse'=>'yes',
	    'minimize'=>'no',
	    'mic'=>'ti-angle-up',
	    'mmic'=>'ti-angle-down',
	    'bp'=>'n',
	    'min'=>2,
	    'maxh'=>32
        );
    }

    public function get_styling() {
        $general = array(
            //bacground
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
			    self::get_color(' a','c'),
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
			    self::get_font_family(':hover', 'f_f_h'),
			    self::get_color(' a', 'c_h', null,null,'hover'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
			    self::get_text_shadow('', 't_sh', 'h')
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
						self::get_height(''),
						self::get_min_height(''),
						self::get_max_height('')
					)
				),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius('')
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
								self::get_box_shadow('')
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

		$list_container = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' > ul','b_c_toc_li_cn', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ':hover > ul', 'b_c_toc_li_cn_h', 'bg_c', 'background-color' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( ' > ul', 'f_f_toc_li_cn' ),
							self::get_color( ' ul a', 'f_c_toc_li_cn' ),
							self::get_font_size( ' > ul', 'f_sz_toc_li_cn' ),
							self::get_font_style( ' > ul', 'f_sy_toc_li_cn', 'f_w_toc_li_cn' ),
							self::get_line_height( ' > ul', 'lh_toc_li_cn' ),
							self::get_letter_spacing( ' > ul', 'l_s_toc_li_cn' ),
							self::get_text_align( ' > ul', 't_a_toc_li_cn' ),
							self::get_text_transform( ' > ul', 't_t_toc_li_cn' ),
							self::get_text_decoration(' > ul', 't_d_toc_li_cn'),
							self::get_text_shadow( ' > ul', 't_sh_toc_li_cn' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' > ul', 'f_f_toc_li_cn', 'h' ),
							self::get_color( ' ul a', 'f_c_toc_li_cn', null, null, 'h' ),
							self::get_font_size( ' > ul', 'f_sz_toc_li_cn', '', 'h' ),
							self::get_font_style( ' > ul', 'f_sy_toc_li_cn', 'f_sy_toc_li_cn', 'h' ),
							self::get_text_decoration(' > ul', 't_d_toc_li_cn', 'h'),
							self::get_text_shadow( ' > ul', 't_sh_toc_li_cn', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' > ul', 'p_toc_li_cn' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' > ul', 'p_toc_li_cn', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' > ul', 'm_toc_li_cn' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' > ul', 'm_toc_li_cn', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' > ul', 'b_toc_li_cn' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' > ul', 'b_toc_li_cn', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' > ul', 'rc_toc_li_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' > ul', 'rc_toc_li_cn', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' > ul', 'sh_toc_li_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' > ul', 'sh_toc_li_cn', 'h')
						)
					)
				))
			))
		);

		$list_items = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' li','b_c_toc_li', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ':hover li', 'b_c_toc_li_h', 'bg_c', 'background-color' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_text_transform( ' li', 't_t_toc_li' ),
							self::get_text_decoration(' li', 't_d_toc_li'),
							self::get_text_shadow( ' li', 't_sh_toc_li' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_text_decoration(' li', 't_d_toc_li', 'h'),
							self::get_text_shadow( ' li', 't_sh_toc_li', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' li', 'p_toc_li' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' li', 'p_toc_li', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
						     self::get_row_gap(' ul')
						)
					),
					'h' => array(
						'options' => array(
							self::get_row_gap( ' ul', 'm_toc_li', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' ul li', 'b_toc_li' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' ul li', 'b_toc_li', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' li', 'rc_toc_li')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' li', 'rc_toc_li', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' li', 'sh_toc_li')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' li', 'sh_toc_li', 'h')
						)
					)
				))
			))
		);

		$child_list_container = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' ul ul','b_c_toc_cli_cntr', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ':hover ul ul', 'b_c_toc_cli_cntr_h', 'bg_c', 'background-color' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_text_transform( ' ul ul', 't_t_toc_cli_cntr' ),
							self::get_text_decoration(' ul ul', 't_d_toc_cli_cntr'),
							self::get_text_shadow( ' ul ul', 't_sh_toc_cli_cntr' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_text_decoration(' ul ul', 't_d_toc_cli_cntr', 'h'),
							self::get_text_shadow( ' ul ul', 't_sh_toc_cli_cntr', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' ul ul', 'p_toc_cli_cntr' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' ul ul', 'p_toc_cli_cntr', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' ul ul', 'm_toc_cli_cntr' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' ul ul', 'm_toc_cli_cntr', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' ul ul', 'b_toc_cli_cntr' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' ul ul', 'b_toc_cli_cntr', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' ul ul', 'rc_toc_cli_cntr')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' ul ul', 'rc_toc_cli_cntr', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' ul ul', 'sh_toc_cli_cntr')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' ul ul', 'sh_toc_cli_cntr', 'h')
						)
					)
				))
			))
		);

		$child_list_items = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' ul ul li','b_c_toc_cli', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ':hover ul ul li', 'b_c_toc_cli_h', 'bg_c', 'background-color' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_text_transform( ' ul ul li', 't_t_toc_cli' ),
							self::get_text_decoration(' ul ul li', 't_d_toc_cli'),
							self::get_text_shadow( ' ul ul li', 't_sh_toc_cli' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_text_decoration(' ul ul li', 't_d_toc_cli', 'h'),
							self::get_text_shadow( ' ul ul li', 't_sh_toc_cli', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' ul ul li', 'p_toc_cli' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' ul ul li', 'p_toc_cli', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_row_gap( ' ul ul', 'm_toc_cli' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_row_gap( ' ul ul', 'm_toc_cli', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' ul ul li', 'b_toc_cli' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' ul ul li', 'b_toc_cli', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' ul ul li', 'rc_toc_cli')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' ul ul li', 'rc_toc_cli', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' ul ul li', 'sh_toc_cli')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' ul ul li', 'sh_toc_cli', 'h')
						)
					)
				))
			))
		);

		$min_icon = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' .tb_toc_head svg','b_c_toc_mni', 'bg_c', 'background-color' ),
							self::get_color(' .tb_toc_head svg', 'f_c_toc_mni' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color(' .tb_toc_head svg', 'b_c_toc_mni_h', 'bg_c', 'background-color', 'h'),
							self::get_color(' .tb_toc_head svg', 'f_c_toc_mni_h', null, null, 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .tb_toc_head svg', 'p_toc_mni' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .tb_toc_head svg', 'p_toc_mni', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' .tb_toc_head svg', 'm_toc_mni' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' .tb_toc_head svg', 'm_toc_mni', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .tb_toc_head svg', 'b_toc_mni' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .tb_toc_head svg', 'b_toc_mni', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .tb_toc_head svg', 'rc_toc_mni')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .tb_toc_head svg', 'rc_toc_mni', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .tb_toc_head svg', 'sh_toc_mni')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .tb_toc_head svg', 'sh_toc_mni', 'h')
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
                'toc-li-cntr' => array(
					'label' => __( 'List Container', 'themify' ),
                    'options' => $list_container
                ),
                'toc-li' => array(
		    'label' => __( 'List Items', 'themify' ),
                    'options' => $list_items
                ),
                'c-toc-li-cntr' => array(
					'label' => __( 'Child-list Container', 'themify' ),
                    'options' => $child_list_container
                ),
                'c-toc-li' => array(
		    'label' => __( 'Child-list Items', 'themify' ),
                    'options' => $child_list_items
                ),
                'min-icon' => array(
		    'label' => __( 'Minimize Icon', 'themify' ),
                    'options' => $min_icon
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args('m_t');
        ?>
	<#
	    let mark=data.mark || 'none',
		classes='tb_toc_'+mark,
		down=data.mic || 'ti-angle-down',
		up=data.mmic || 'ti-angle-up',
		bp= data.minimize=='yes'?data.bp:'',
		colapsedown=data.cic || '-',
		colapseup=data.cmic ||'+',
		min=data.min>1?data.min:2,
		max=data.maxt || '',
		maxh=data.maxh>2?data.maxh:32;
	    if(data.num=='yes'){
		classes+=' tb_toc_show_num';
	    }
	    if(data.tree=='yes'){
		classes+=' tb_toc_tree';
	    }
	#>
        <div class="module module-<?php echo $this->slug; ?> {{classes}}"<#if(data.ex_m_t=='yes'){#> data-ex_m="1"<#}#> data-min="{{min}}" data-bp="{{bp}}" data-maxh="{{maxh}}" data-maxt="{{max}}" data-tags="{{data.in_tags}}" data-excl="{{data.ex_tags}}" data-cont="{{data.in_cont}}" data-sel="{{data.in_custom}}">
            <div class="tb_toc_head tf_clearfix">
		<#if ( data.m_t ) { #>
		    <?php echo $module_args['before_title']; ?>{{{ data.m_t }}}<?php echo $module_args['after_title']; ?>
		<# }
		 if(data.minimize=='yes'){
		    print( api.Helper.getIcon( down,'tb_toc_mic_close' ).outerHTML );
		    print( api.Helper.getIcon( up,'tb_toc_mic tf_hide' ).outerHTML )
		}
		#>
	    </div>
	    <# if(mark=='ic' && data.ic){#>
		<template class="tpl_toc_ic">
		    <# print( api.Helper.getIcon( data.ic,'tb_toc_ic' ).outerHTML )#>
		</template>
	    <# }
	    if(data.tree=='yes' && data.colapse=='yes'){#>
		<template class="tpl_toc_cic">
		    <# print( (colapsedown=='-'?'<span class="tf_fa tb_toc_cic"></span>':api.Helper.getIcon( colapsedown,'tb_toc_cic' ).outerHTML) )#>
		</template>
		<template class="tpl_toc_cic_close">
		    <# print( (colapseup=='+'?'<span class="tf_fa tb_toc_cic_close tf_hide"></span>':api.Helper.getIcon( colapseup,'tb_toc_cic_close tf_hide' ).outerHTML) )#>
		</template>
	    <#}#>
        </div>
        <?php
    }

}

new TB_Toc_Module();
