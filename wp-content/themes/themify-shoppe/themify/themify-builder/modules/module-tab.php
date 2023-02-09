<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Tab
 * Description: Display Tab content
 */

class TB_Tab_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('tab');
    }
    
    public function get_name(){
        return __('Tab', 'themify');
    }

    public function get_icon(){
	return 'layout-tab';
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
		'id' => 'mod_title_tab',
		'type' => 'title'
	    ),
	    array(
		'id' => 'tab_content_tab',
		'type' => 'builder',
		'options' => array(
		    array(
				'id' => 'title_tab',
				'type' => 'text',
				'label' => __('Tab Title', 'themify'),
				'control' => array(
				    'selector' => '.tab-nav span'
				)
			    ),
			    array(
				'id' => 'icon_tab',
				'type' => 'icon',
				'label' => __('Icon', 'themify')
			    ),
		    array(
			'id' => 'text_tab',
			'type' => 'wp_editor',
			'control' => array(
			    'selector' => '.tb_text_wrap'
			)
		    )
		)
	    ),
			array(
				'type' => 'group',
				'label' => __( 'Tab Layout', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
					'id' => 'layout_tab',
					'type' => 'layout',
					'label' => __('Tab Layout', 'themify'),
					'mode' => 'sprite',
					'options' => array(
						array('img' => 'tab_frame', 'value' => 'tab-frame', 'label' => __('Tab Frame', 'themify')),
						array('img' => 'tab_window', 'value' => 'panel', 'label' => __('Tab Window', 'themify')),
						array('img' => 'tab_vertical', 'value' => 'vertical', 'label' => __('Tab Vertical', 'themify')),
						array('img' => 'tab_minimal', 'value' => 'minimal', 'label' => __('Tab Minimal', 'themify'))
					)
					),
					array(
					'id' => 'style_tab',
					'type' => 'select',
					'label' => __('Tab Icon', 'themify'),
					'options' => array(
						'default' => __('Icon beside the title', 'themify'),
						'icon-top' => __('Icon above the title', 'themify'),
						'icon-only' => __('Just icons', 'themify'),
					)
					),
					array(
					'id' => 'allow_tab_breakpoint',
					'label' => __('Mobile Tab', 'themify'),
					'type' => 'toggle_switch',
					'options' => array(
						'on' => array('name'=>'allow_tab','value' =>'en'),
						'off' => array('name'=>'', 'value' =>'dis')
					),	
					'binding' => array(
						'checked' => array(
						'show' => 'tab_breakpoint'
						),
						'not_checked' => array(
						'hide' => 'tab_breakpoint'
						)
					)
					),
					array(
					'id' => 'tab_breakpoint',
					'label' => '',
					'type' => 'number',
					'after' => __('Breakpoint (px)', 'themify'),
					'wrap_class' => 'tb_checkbox_element_allow_tab'
					),
					array(
					    'id' => 'color_tab',
					'type' => 'layout',
					'mode' => 'sprite',
					'label' => __('Tab Color', 'themify'),
					'class' => 'tb_colors',
					'color' => true,
					'transparent' => true
					),
					array(
					'id' => 'tab_appearance_tab',
					'type' => 'checkbox',
					'label' => __('Tab Appearance', 'themify'),
					'appearance' => true
					),
                    array(
                        'id' => 'hashtag',
                        'type' => 'toggle_switch',
                        'label' => __('URL Hashtag', 'themify'),
                        'options'=>'simple'
                    ),
				)
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_tab' ),
	);
    }

    public function get_live_default() {
	return array(
	    'tab_content_tab' => array(
		array('title_tab' => __('Tab Title', 'themify'), 'text_tab' => __('Tab Content', 'themify'))
	    ),
	    'layout_tab' => 'minimal'
	);
    }

    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.ui', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.ui', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.ui'),
			    self::get_color_type(array(' .tb_text_wrap',' .tab-nav span')),
			    self::get_font_size('.ui'),
			    self::get_line_height('.ui'),
			    self::get_letter_spacing('.ui'),
			    self::get_text_align('.ui'),
			    self::get_text_transform('.ui'),
			    self::get_font_style('.ui'),
			    self::get_text_decoration('.ui', 'text_decoration_regular'),
			    self::get_text_shadow('.ui'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.ui', 'f_f', 'h'),
			    self::get_color_type(array(':hover .tb_text_wrap',':hover .tab-nav span'),'', 'f_c_t_h','f_c_h', 'f_g_c_h'),
			    self::get_font_size('.ui', 'f_s', '', 'h'),
			    self::get_font_style('.ui', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('.ui', 't_d_r', 'h'),
			    self::get_text_shadow('.ui','t_sh','h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.ui a','.ui .tab-nav span'), 'link_color'),
			    self::get_text_decoration('.ui a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.ui a','.ui .tab-nav span'), 'link_color',null, null, 'hover'),
			    self::get_text_decoration('.ui a', 't_d', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('.ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('.ui', 'p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('.ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('.ui', 'm', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('.ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('.ui', 'b', 'h')
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
						self::get_height('.ui'),
						self::get_min_height('.ui'),
						self::get_max_height('.ui')
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

	$title = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image('.ui .tab-nav li','bg_i', 'background_color_title', 'bg_r', 'bg_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_image('.ui .tab-nav li','bg_i', 'b_c_t', 'bg_r', 'bg_p', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.ui .tab-nav li a', 'font_family_title'),
			    self::get_color('.ui .tab-nav li span', 'font_color_title', 'f_c'),
			    self::get_font_size('.ui .tab-nav li a', 'font_size_title'),
			    self::get_line_height('.ui .tab-nav li a', 'line_height_title'),
			    self::get_letter_spacing('.ui .tab-nav li a', 'letter_spacing_title'),
			    self::get_text_align(array('.ui .tab-nav', '.ui .tab-nav li'), 'title_text_align'),
			    self::get_text_transform('.ui .tab-nav li a', 't_t_t'),
			    self::get_font_style('.ui .tab-nav li a', 'f_sy_t', 'f_t_b'),
				self::get_text_shadow('.ui .tab-nav li a', 't_sh_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.ui .tab-nav li a', 'f_f_t', 'h'),
			    self::get_color('.ui .tab-nav li:hover span', 'f_c_t_h', null, null, ''),
			    self::get_font_size('.ui .tab-nav li a', 'f_s_t', '', 'h'),
			    self::get_font_style('.ui .tab-nav li a', 'f_sy_t', 'f_t_b', 'h'),
			    self::get_text_shadow('.ui .tab-nav li a', 't_sh_t','h'),
			)
		    )
		))
	    )),
	    // Active Tab
	    self::get_expand(__('Active Tab', 'themify'), array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li.current span', 'active_font_color_title', __('Color Active', 'themify')),
			    self::get_image('.ui .tab-nav li.current','bg_i', 'active_background_color_title','bg_r', 'bg_p'),
			    self::get_border('.ui .tab-nav li.current', 'active_tab_border')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li.current span:hover', 'active_hover_font_color_title', __('Color Active', 'themify')),
			    self::get_image('.ui .tab-nav li.current:hover','bg_i_h', 'active_hover_background_color_title', 'bg_r_h', 'bg_i_h'),
			    self::get_border('.ui .tab-nav li.current:hover', 't_b_h' )
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('.ui .tab-nav li', 'title_border')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('.ui .tab-nav li', 't_b' ,'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius('.ui .tab-nav li', 't_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius('.ui .tab-nav li', 't_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow('.ui .tab-nav li', 't_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow('.ui .tab-nav li', 't_b_sh', 'h')
					)
				)
			))
		))
	);

	$icon = array(
	    self::get_expand('c', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li em', 'icon_color'),
			    self::get_font_size('.ui .tab-nav li em', 'icon_size')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li em', 'i_c', null, null, 'h'),
			    self::get_font_size('.ui .tab-nav li em', 'i_s', '', 'h')
			)
		    )
		))
	    )),
	    // Active Tab
	    self::get_expand(__('Active Tab', 'themify'), array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li.current em', 'active_tab_icon_color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.ui .tab-nav li.current em', 'a_t_i_c',null, null, 'h')
			)
		    )
		))
	    ))
	);

	$content = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.ui .tab-content', 'background_color_content', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.ui .tab-content', 'b_c_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.ui .tab-content', 'font_family_content'),
			    self::get_color('.ui .tb_text_wrap', 'font_color_content'),
			    self::get_font_size('.ui .tab-content', 'font_size_content'),
			    self::get_font_style('.ui .tab-content', 'f_fs_c', 'f_fw_c'),
			    self::get_line_height('.ui .tab-content', 'line_height_content'),
				self::get_text_shadow('.ui .tab-content', 't_sh_c'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.ui .tab-content', 'f_f_c', 'h'),
			    self::get_color('.ui .tb_text_wrap:hover', 'f_c_c_h'),
			    self::get_font_size('.ui .tab-content', 'f_s_c', '', 'h'),
				self::get_font_style('.ui .tab-content', 'f_fs_c', 'f_fw_c', 'h'),
				self::get_text_shadow('.ui .tab-content', 't_sh_c','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('.ui .tab-content', 'p_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('.ui .tab-content', 'p_c', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('.ui .tab-content', 'b_c')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('.ui .tab-content', 'b_c', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius('.ui .tab-content', 't_c_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius('.ui .tab-content', 't_c_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow('.ui .tab-content', 't_c_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow('.ui .tab-content', 't_c_b_sh', 'h')
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
		't' => array(
		    'label' => __('Title', 'themify'),
		    'options' => $title
		),
		'i' => array(
		    'label' => __('Icon', 'themify'),
		    'options' => $icon
		),
		'c' => array(
		    'label' => __('Content', 'themify'),
		    'options' => $content
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = self::get_module_args('mod_title_tab');
	?>
	<#  const color = undefined === data.color_tab || 'default' == data.color_tab ? 'tb_default_color' : data.color_tab, 
	    tabId=data.cid.replace('tb_',''),
	    arr=data.tab_content_tab || [];
	#>
	<div class="module module-<?php echo $this->slug; ?> ui tab-style-{{ data.style_tab }} {{ data.layout_tab }} {{ color }} {{ data.css_tab }} <# data.tab_appearance_tab  ? print( data.tab_appearance_tab.split('|').join(' ') ) : ''; #>" <# ( 'allow_tab' == data.allow_tab_breakpoint && '' != data.tab_breakpoint ) ? print( "data-tab-breakpoint='"+ data.tab_breakpoint +"'" ) : ""; #>  >
	    <# if ( data.mod_title_tab ) { #>
		    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_tab }}}<?php echo $module_args['after_title']; ?>
	    <# }#>
	    <div class="builder-tabs-wrap tf_rel">
		<div class="tab-nav-current-active tf_hide">
			<span class="tab_burger_icon tf_hide tf_rel"></span>
			<span class="tb_tab_title" contenteditable="false" data-name="title_tab" data-repeat="tab_content_tab" data-index="0">{{{ (arr[0]?arr[0].title_tab:'') }}}</span>
		</div>
		<ul class="tab-nav tf_clearfix">
		    <# for(var i=0,len=arr.length;i<len;++i){#>
			<li class="<# i == 0 && print('current') #>" aria-expanded="{{i == 0}}">
				<a href="#tab-{{ tabId }}-{{ i }}">
				    <#  let item=arr[i];
					if ( item ) {
					    if ( item.icon_tab ) { #><em><# print(api.Helper.getIcon(item.icon_tab).outerHTML)#></em><# }
					    if ( item.title_tab ) { #><span contenteditable="false" data-name="title_tab" data-repeat="tab_content_tab" class="tb-tab-span">{{{ item.title_tab }}}</span><# } 
					} 
				    #>
				</a>
			</li>
		    <#  } #>
		</ul>
		<# for(var i=0;i<len;++i){#>
		    <div data-id="tab-{{ tabId }}-{{ i }}" class="tab-content tf_overflow tf_clear" aria-hidden="{{i != 0}}">
			<div class="tb_text_wrap" contenteditable="false" data-name="text_tab" data-index="{{i}}" data-hasEditor data-repeat="tab_content_tab"><# arr[i] && arr[i].text_tab && print( arr[i].text_tab ) #></div>
		    </div>
		<# } #>
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
	$mod_settings = wp_parse_args($module['mod_settings'], array(
	    'mod_title_tab' => '',
	    'tab_content_tab' => array()
	));
	$text = '';

	if ('' !== $mod_settings['mod_title_tab'])
	    $text = sprintf('<h3>%s</h3>', $mod_settings['mod_title_tab']);

	if (!empty($mod_settings['tab_content_tab'])) {
	    $text .= '<ul>';
	    foreach ($mod_settings['tab_content_tab'] as $content) {
		$content = wp_parse_args($content, array(
		    'title_tab' => '',
		    'text_tab' => '',
		));
		$text .= sprintf('<li><h4>%s</h4>%s</li>', $content['title_tab'], $content['text_tab']);
	    }
	    $text .= '</ul>';
	}
	return $text;
    }

}

new TB_Tab_Module();
