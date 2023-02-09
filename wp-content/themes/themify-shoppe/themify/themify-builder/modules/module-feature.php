<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Feature
 * Description: Display Feature content
 */

class TB_Feature_Module extends Themify_Builder_Component_Module {

    public function __construct() {
        parent::__construct('feature');
    }
    
    public function get_name(){
        return __('Feature', 'themify');
    }
    
    public function get_icon(){
	return 'layout-media-left';
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
                'id' => 'mod_title_feature',
                'type' => 'title'
            ),
            array(
                'id' => 'title_feature',
                'type' => 'text',
                'label' => __('Feature Title', 'themify'),
                'class' => 'large',
                'control' => array(
                    'selector' => '.module-feature-title a'
                )
            ),
            array(
                'id' => 'title_tag',
                'type' => 'select',
                'label' => __('Title HTML Tag', 'themify'),
                'h_tags' => true,
                'default' => 'h3'
            ),
            array(
                'id' => 'content_feature',
                'type' => 'wp_editor',
                'control' => array(
                    'selector' => '.tb_text_wrap'
                )
            ),
            array(
                'id' => 'link_feature',
                'type' => 'url',
                'label' => __('Link', 'themify'),
                'binding' => array(
                    'empty' => array(
                        'hide' => array('link_options', 'lightbox_size','feature_download_link')
                    ),
                    'not_empty' => array(
                        'show' => array('link_options', 'lightbox_size','feature_download_link')
                    )
                )
            ),
            array(
                'id' => 'feature_download_link',
                'type' => 'checkbox',
                'label' => __('Download-able Link', 'themify'),
                'options' => array(
                    array('name' => 'yes', 'value' => __('Download link as file', 'themify'))
                )
            ),
            array(
                'id' => 'link_options',
                'type' => 'radio',
                'label' => 'o_l',
                'link_type' => true,
		'wrap_class' => 'tb_compact_radios',
                'option_js' => true
            ),
            array(
                'type' => 'multi',
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
                'id' => 'overlap_image_feature',
                'type' => 'image',
                'label' => __('Overlap Image', 'themify'),
                'binding' => array(
                    'empty' => array('hide' => array('overlap_image_width','overlap_image_height')),
                    'not_empty' => array('show' => array('overlap_image_width','overlap_image_height'))
                )
            ),
            array(
                'type' => 'multi',
                'label' => '',
                'options' => array(
                    array(
                        'id' => 'overlap_image_width',
                        'type' => 'text',
                        'label' => 'w'
                    ),
                    array(
                        'id' => 'overlap_image_height',
                        'type' => 'text',
                        'label' => 'ht'
                    )
                ),
            ),
			array(
				'type' => 'group',
				'label' => __( 'Feature Layout', 'themify' ),
				'display' => 'accordion',
				'options' => array(
					array(
						'id' => 'layout_feature',
						'type' => 'layout',
						'label' => __('Layout', 'themify'),
						'mode' => 'sprite',
						'options' => array(
							array('img' => 'icon_left', 'value' => 'icon-left', 'label' => __('Icon Left', 'themify')),
							array('img' => 'icon_right', 'value' => 'icon-right', 'label' => __('Icon Right', 'themify')),
							array('img' => 'icon_top', 'value' => 'icon-top', 'label' => __('Icon Top', 'themify'))
						)
					),
					array(
						'id' => 'layout_mobile',
						'type' => 'layout',
						'label' => __( 'Mobile Layout', 'themify' ),
						'mode' => 'sprite',
						'options' => array(
							array('img' => 'icon_top', 'value' => 'icon-top', 'label' => __('Icon Top', 'themify')),
							array('img' => 'icon_left', 'value' => 'icon-left', 'label' => __('Icon Left', 'themify')),
							array('img' => 'icon_right', 'value' => 'icon-right', 'label' => __('Icon Right', 'themify')),
						)
					),
					array(
						'type' => 'multi',
						'label' => __('Circle', 'themify'),
						'wrap_class'=>'multi_circle_feature',
						'options' => array(
							array(
								'id' => 'circle_percentage_feature',
								'type' => 'range',
								'label' => __('Percentage', 'themify'),
								'class' => 'xsmall',
								'units' => array(
									'%' => ''
								)
							),
							array(
								'id' => 'circle_stroke_feature',
								'type' => 'range',
								'label' => __('Stroke', 'themify'),
								'class' => 'xsmall',
								'units' => array(
									'px' => ''
								)
							),
							array(
								'id' => 'circle_color_feature',
								'type' => 'color',
								'label' => 'c'
							),
							array(
								'id' => 'circle_size_feature',
								'type' => 'select',
								'label' => __('Size', 'themify'),
								'options' => array(
									'small' => __('Small', 'themify'),
									'medium' => __('Medium', 'themify'),
									'large' => __('Large', 'themify'),
									'custom' => __('Custom', 'themify')
								),
								'binding' => array(
									'small' => array('hide' => array('custom_circle_size_feature')),
									'medium' => array('hide' => array('custom_circle_size_feature')),
									'large' => array('hide' => array('custom_circle_size_feature')),
									'custom' => array('show' => array('custom_circle_size_feature')),
								)
							),
							array(
								'id' => 'custom_circle_size_feature',
								'type' => 'number',
								'after' => __('Circle size(px)', 'themify')
							),
						)
					),
					array(
						'id' => 'icon_type_feature',
						'type' => 'radio',
						'label' => __('Icon Type', 'themify'),
						'options' => array(
					array('value'=>'icon','name'=>__('Icon', 'themify')),
					array('value'=>'image_icon','name'=>__('Image', 'themify')),
					array('value'=>'both','name'=>__('Both', 'themify')),
						),
						'option_js' => true
					),
					array(
						'id' => 'image_feature',
						'type' => 'image',
						'label' => __('Image URL', 'themify'),
						'wrap_class' => 'tb_group_element_image_icon tb_group_element_both'
					),
					array(
						'type' => 'multi',
						'label' => '',
						'wrap_class' => 'tb_group_element_icon tb_group_element_both',
						'options' => array(
							array(
								'id' => 'icon_feature',
								'type' => 'icon',
								'label' => __('Icon', 'themify')
							),
							array(
								'id' => 'icon_color_feature',
								'type' => 'color',
								'label' => 'c',
								'class' => 'medium',
							),
							array(
								'id' => 'icon_bg_feature',
								'type' => 'color',
								'label' => 'bg',
								'class' => 'medium'
							),
						)
					),
					array(
						'id' => 'icon_position',
						'type' => 'angle',
						'deg' => true,
						'label' => __( 'Icon Position', 'themify' ),
						'wrap_class' => 'tb_group_element_both',
					),
				),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_feature' ),
        );
    }

    public function get_live_default() {
        return array(
            'title_tag' => 'h3',
            'title_feature' => __('Feature Title', 'themify'),
            'content_feature' => __('Feature content', 'themify'),
            'circle_percentage_feature' => '100',
            'circle_stroke_feature' => '3',
            'icon_feature' => 'fa-home',
            'layout_feature' => 'icon-top',
            'circle_size_feature' => 'small',
            'icon_type_feature'=>'icon',
            'circle_color_feature' => '#de5d5d',
            'custom_circle_size_feature' => 120
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
                        self::get_font_family(array(' .tb_text_wrap',' .module-feature-title')),
                        self::get_color_type(array(' .tb_text_wrap', ' .module-feature-title')),
                        self::get_font_size(),
                        self::get_line_height(),
                        self::get_letter_spacing(),
                        self::get_text_align(array('',' .module-feature-content')),
                        self::get_text_transform(),
                        self::get_font_style(array(' .tb_text_wrap',' .module-feature-title')),
                        self::get_text_decoration('', 'text_decoration_regular'),
						self::get_text_shadow(array(' .tb_text_wrap',' .module-feature-title')),
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array(':hover .tb_text_wrap',':hover .module-feature-title'), 'f_f_h'),
                        self::get_color_type(array(':hover .tb_text_wrap', ':hover .module-feature-title'),'', 'f_c_t_h', 'f_c_h', 'f_g_c_h'),
                        self::get_font_size('', 'f_s', '', 'h'),
                        self::get_font_style(array(':hover .tb_text_wrap',':hover .module-feature-title'), 'f_st', 'f_w', 'h'),
                        self::get_text_decoration('', 't_d_r', 'h'),
						self::get_text_shadow(array(' .tb_text_wrap',' .module-feature-title'),'t_sh','h'),
                    )
                )
            ))
		)),
            // Link
            self::get_expand('l', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_color('.module a', 'link_color'),
                        self::get_text_decoration(' a')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_color('.module a:hover', 'link_color_hover'),
                        self::get_text_decoration(' a', 't_d', 'h')
                    )
                )
            ))
		)),
            // Padding
            self::get_expand('p', array(
            self::get_tab( array(
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

        $feature_title = array(
            // Font
            self::get_seperator('f'),
            self::get_tab(array(
                'n' => array(        
                    'options' => array(
                        self::get_font_family(array('.module .module-feature-title', '.module .module-feature-title a'), 'font_family_title'),
                        self::get_color(array('.module .module-feature-title', '.module .module-feature-title a'), 'font_color_title'),
                        self::get_font_size('.module .module-feature-title', 'font_size_title'),
                        self::get_line_height('.module .module-feature-title', 'line_height_title'),
                        self::get_letter_spacing('.module .module-feature-title', 'l_s_t'),
                        self::get_text_transform('.module .module-feature-title', 't_t_t'),
                        self::get_font_style(array('.module .module-feature-title', '.module .module-feature-title a'), 'f_s_t', 'f_t_b'),
                        self::get_text_shadow('.module .module-feature-title', 't_sh_t')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array('.module .module-feature-title', '.module .module-feature-title a'), 'f_f_t', 'h'),
						self::get_color(array('.module .module-feature-title', '.module .module-feature-title a'), 'font_color_title',  null, null, 'hover'),
                        self::get_font_size('.module .module-feature-title', 'f_s_t', '', 'h'),
                        self::get_font_style(array('.module .module-feature-title', '.module .module-feature-title a'), 'f_st_t', 'f_t_b', 'h'),
						self::get_text_shadow('.module .module-feature-title', 't_sh_t','h')
                    )
                )
            ))
        );

        $feature_content = array(
            // Font
            self::get_expand('f', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_font_family(array('.module .tb_text_wrap', ' .module-feature-content a'), 'font_family_content'),
                        self::get_color(array('.module .tb_text_wrap','.module .module-feature-content a','.module .module-feature-title'), 'f_c_c'),
                        self::get_font_size(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_s_c'),
                        self::get_font_style(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_st_c', 'f_fw_c'),
                        self::get_line_height(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_h_c'),
                        self::get_letter_spacing(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_s_c'),
                        self::get_text_shadow(array('.module .tb_text_wrap', ' .module-feature-content a'), 't_sh_c')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_f_c', 'h'),
                        self::get_color(array('.module .tb_text_wrap','.module .module-feature-content a','.module .module-feature-title'), 'f_c_c', null, null, 'h'),
                        self::get_font_size(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_s_c', '', 'h'),
						self::get_font_style(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_st_c', 'f_fw_c', 'h'),
						self::get_text_shadow(array('.module .tb_text_wrap', ' .module-feature-content a'), 't_sh_c','h')
                    )
                )
            ))
		)),
            // Padding
            self::get_expand('p', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_padding(' .module-feature-content', 'c_p')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_padding(' .module-feature-content', 'c_p', 'h')
                    )
                )
            ))
		)),
            // Margin
            self::get_expand('m', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_margin(' .module-feature-content', 'c_m')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_margin(' .module-feature-content', 'c_m', 'h')
                    )
                )
            ))
		)),
            // Border
            self::get_expand('b', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_border(' .module-feature-content', 'c_b')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_border(' .module-feature-content', 'c_b', 'h')
                    )
                )
            ))
		))
        );

        $featured_icon = array(
            // Font
            self::get_seperator('f'),
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_font_size(' .module-feature-icon', 'f_s_i')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_size(' .module-feature-icon', 'f_s_i', '', 'h')
                    )
                )
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
                    'label' => __('Feature Title', 'themify'),
                    'options' => $feature_title
                ),
                'f' => array(
                    'label' => __('Feature Icon', 'themify'),
                    'options' => $featured_icon
                ),
                'c' => array(
                    'label' => __('Feature Content', 'themify'),
                    'options' => $feature_content
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args('mod_title_feature');
        ?>
        <#
	const args=Object.assign({
	    icon_feature:'',
	    icon_position:'',
	    icon_bg_feature:'',
	    circle_percentage_feature:'',
	    layout_feature:'icon-top',
	    title_tag:'h3'
        },data);
	    args.circle_percentage_feature = args.circle_percentage_feature.replace('%','');
	let w=args.circle_stroke_feature || 0,
	    isEmpty=args.circle_percentage_feature<=0 || w==0,
	    layout_mobile = args.layout_mobile?' data-layout-mobile="' + args.layout_mobile + '" data-layout-desktop="' + args.layout_feature + '"':'',
	    chart_class = 'with-chart',
	    insetColor=args.icon_bg_feature!=='' && args.icon_type_feature=='icon'?api.Helper.toRGBA(args.icon_bg_feature):'',
	    st='';
	    if(w==1){
		w=2;
	    }
	    let r='calc(50% - '+w/2+'px)';
	    if(isEmpty){
			chart_class='no-chart';
	    }
	    else{
		if(args.circle_percentage_feature>100){
			args.circle_percentage_feature = '100';
		}
		if(args.overlap_image_feature){
			chart_class+=' with-overlay-image';
		}
	    }
	    if(args.circle_size_feature=='custom' && args.custom_circle_size_feature){
			st='width:'+args.custom_circle_size_feature+'px;height:'+args.custom_circle_size_feature+'px;';
	    }
	    if(isEmpty && insetColor !== ''){
			st+='background-color:'+insetColor;
	    }
        #>
        <div class="module module-<?php echo $this->slug; ?> {{chart_class}} layout-{{ args.layout_feature }} size-{{args.circle_size_feature}} {{ args.css_feature }}" {{{ layout_mobile }}}>
            <# if( args.mod_title_feature ) { #>
		<?php echo $module_args['before_title']; ?>{{{ args.mod_title_feature }}}<?php echo $module_args['after_title']; ?>
            <# } #>

            <div class="module-feature-image tf_textc tf_rel">
                <# if(args.overlap_image_feature){
					#>
					<img src="{{args.overlap_image_feature}}" data-w="overlap_image_width" data-h="overlap_image_height" data-name="overlap_image_feature" width="<#print(args.overlap_image_width || '')#>" height="<#print(args.overlap_image_height || '')#>">
                <#}
                if(args.link_feature){ #>
		    <a href="{{ args.link_feature }}">
		<#}#>
		<span class="module-feature-chart-html5 tf_box tf_rel tf_inline_b"<# if(st!==''){#> style="{{st}}"<#}#>>
			<# if(!isEmpty){#>
			    <svg class="tf_abs tf_w tf_h">
				<circle class="tb_feature_fill" r="{{r}}" cx="50%" cy="50%" stroke-width="{{w}}"/>
				<circle class="tb_feature_stroke" r="{{r}}" cx="50%" cy="50%" stroke="{{api.Helper.toRGBA(args.circle_color_feature)}}" stroke-width="{{w}}" data-progress="{{args.circle_percentage_feature}}" stroke-dasharray="0,10000"/>
				<# if(insetColor !== '' && args.icon_type_feature=='icon'){#>
				    <circle class="tb_feature_bg" r="calc(50% - <# print((w>1?(w-1):0))#>px)" cx="50%" cy="50%" stroke-width="{{w}}" fill="{{insetColor}}" />
				<# }#>
			    </svg>
			<# }#>
			<span class="chart-html5-circle tf_w tf_h">
			    <# if(args.icon_type_feature=='icon'){
				if(args.icon_feature){#>
				    <em class="module-feature-icon tf_rel"<# if(args.icon_color_feature!==''){ #> style="color:<# print(api.Helper.toRGBA(args.icon_color_feature)) #>"<# } #>><# print(api.Helper.getIcon(args.icon_feature).outerHTML)#></em>
				<# } 
			    }
			    else if(args.image_feature){
			    #>
				<img style="width:calc(100% - {{w*2}}px);height:calc(100% - {{w*2}}px)" src="{{args.image_feature}}">
			    <# } #>
			</span>
				<# if ( args.icon_type_feature == 'both' ) {
					let style = ( args.icon_color_feature !== '' ? 'color:' + api.Helper.toRGBA( args.icon_color_feature ) + ';' : '' ) + ( args.icon_bg_feature !== '' ? 'background-color:' + api.Helper.toRGBA(args.icon_bg_feature) + ';' : '' );
					#>
					<span class="module-feature-icon-wrap tf_abs" style="transform:rotate({{args.icon_position}}deg)">
					    <span class="module-feature-icon" style="transform:translateY(-50%) rotate(-{{args.icon_position}}deg);{{style}}"><# print( api.Helper.getIcon(args.icon_feature).outerHTML ) #></span>
					</span>
				<# } #>
		    </span>
		<# if(args.link_feature){ #>
		    </a>
		<# } #>
            </div>
            <div class="module-feature-content tf_textc">
                <# if(data.title_feature!==''){ #>
                <{{data.title_tag}} class="module-feature-title"<# if(!data.link_feature){#> data-name="title_feature" contenteditable="false"<#}#>>
                    <# if(data.link_feature){ #>
						<a href="{{data.link_feature}}" contenteditable="false" data-name="title_feature" href="{{data.link_feature}}">
					<#}#>
                        {{{data.title_feature}}}
                        <# if(data.link_feature){ #>
                    </a>
                    <#}#>
                </{{data.title_tag}}>
                <# } #>
				<div contenteditable="false" data-name="content_feature" data-hasEditor class="tb_text_wrap">
					{{{ data.content_feature }}}
				</div>
            </div>
        </div>
        <?php
    }

}

new TB_Feature_Module();