<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Image
 * Description: Display Image content
 */

class TB_Image_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('image');
    }
    
    public function get_name(){
        return __('Image', 'themify');
    }
    
    public function get_assets() {
	return array(
		'css'=>1
	);
    }
    public function get_options() {

	return array(
	    array(
		'id' => 'mod_title_image',
		'type' => 'title'
	    ),
	    array(
		'id' => 'style_image',
		'type' => 'layout',
		'label' => __('Image Style', 'themify'),
		'mode' => 'sprite',
		'options' => array(
		    array('img' => 'image_top', 'value' => 'image-top', 'label' => __('Image Top', 'themify')),
		    array('img' => 'image_left', 'value' => 'image-left', 'label' => __('Image Left', 'themify')),
		    array('img' => 'image_center', 'value' => 'image-center', 'label' => __('Image Center', 'themify')),
		    array('img' => 'image_right', 'value' => 'image-right', 'label' => __('Image Right', 'themify')),
		    array('img' => 'image_overlay', 'value' => 'image-overlay', 'label' => __('Partial Overlay', 'themify')),
		    array('img' => 'image_card_layout', 'value' => 'image-card-layout', 'label' => __('Card Layout', 'themify')),
		    array('img' => 'image_centered_overlay', 'value' => 'image-full-overlay', 'label' => __('Full Overlay', 'themify'))
		),
		'binding' => array(
		    'not_empty' => array(
			'hide' =>'caption_on_overlay'
		    ),
		    'image-overlay' => array(
			'show' => 'caption_on_overlay'
		    ),
		    'image-full-overlay' => array(
			'show' =>'caption_on_overlay'
		    )
		)
	    ),
	    array(
		'id' => 'caption_on_overlay',
		'type' => 'checkbox',
		'label' => '',
		'options' => array(
		    array('name' => 'yes', 'value' => __('Show overlay on hover', 'themify'))
		)
	    ),
	    array(
		'id' => 'url_image',
		'type' => 'image',
		'label' => __('Image URL', 'themify')
	    ),
	    array(
		'id' => 'appearance_image',
		'type' => 'checkbox',
		'label' => __('Appearance', 'themify'),
		'img_appearance'=>true
	    ),
	    array(
		'id' => 'image_size_image',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'width_image',
		'label' => 'w',
		'type' => 'number',
		'after' => 'px'
	    ),
	    array(
                'id' => 'auto_fullwidth',
                'type' => 'checkbox',
                'label' => '',
                'options' => array(array('name' => '1', 'value' => __('Auto fullwidth image', 'themify'))),
			    'wrap_class' => 'auto_fullwidth'
	    ),
	    array(
		'id' => 'height_image',
		'type' => 'number',
		'label' => 'ht',
		'after' => 'px'
	    ),
        array(
	    'id' => 'title_image',
            'type' => 'text',
            'label' => __('Image Title', 'themify'),
	    'control' => array(
			'selector' => '.image-title'
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
		'id' => 'link_image',
		'type' => 'url',
		'label' => __('Image Link', 'themify'),
		'binding' => array(
		    'empty' => array(
			'hide' => array('param_image', 'image_zoom_icon', 'lightbox_size')
		    ),
		    'not_empty' => array(
			'show' => array('param_image', 'image_zoom_icon', 'lightbox_size')
		    )
		)
	    ),
	    array(
		'id' => 'param_image',
		'type' => 'radio',
		'label' => 'o_l',
		'link_type' => true,
		'option_js' => true,
		'wrap_class' => 'link_options tb_compact_radios',
		'default' => 'regular',
		'binding' => array(
		    'regular' => array(
			'hide' =>'lightbox_size'
		    ),
		    'newtab' => array(
			'hide' => 'lightbox_size'
		    ),
		    'lightbox' => array(
			'show' => 'lightbox_size'
		    )
		)
	    ),
	    array(
		'type' => 'multi',
		'label' => __('Lightbox Dimension', 'themify'),
		'id' => 'multi_lightbox',
		'options' => array(
		    array(
			'id' => 'lightbox_width',
			'type' => 'range',
			'label' => 'w',
			'control' => false,
			'units' => array(
			    'px' => array(
					'max' => 3500,
			    ),
			    '%' => ''
			)
		    ),
		    array(
			'id' => 'lightbox_height',
			'type' => 'range',
			'label' => 'ht',
			'control' => false,
			'units' => array(
			    'px' => array(
					'max' => 3500
			    ),
			    '%' => ''
			)
		    )
		),
		'wrap_class' => 'tb_group_element_lightbox'
	    ),
	    array(
		'id' => 'image_zoom_icon',
		'type' => 'checkbox',
		'label' => '',
		'options' => array(
		    array('name' => 'zoom', 'value' => __('Show zoom icon', 'themify'))
		),
		'wrap_class' => 'tb_group_element_lightbox tb_group_element_newtab'
	    ),
	    array(
            'id' => 'caption_image',
            'type' => 'textarea',
            'label' =>__('Image Caption', 'themify'),
            'control' => array(
                'selector' => '.image-caption'
            )
	    ),
	    array(
		'id' => 'alt_image',
		'type' => 'text',
		'label' => __('Alt Tag', 'themify'),
					'help'=> __('Optional: Image alt is the image "alt" attribute. Primarily used for SEO describing the image.','themify'),
		'control' => false
	    ),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_image' ),
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
			    self::get_font_family(array(' .image-content', ' .image-title')),
			    self::get_color_type(array(' .tb_text_wrap', ' .image-title')),
			    self::get_font_size(' .image-content'),
			    self::get_line_height(' .image-content'),
			    self::get_letter_spacing(array(' .image-content', ' .image-title')),
			    self::get_text_align(' .image-content'),
			    self::get_text_transform(' .image-content'),
			    self::get_font_style(array(' .image-content', ' .image-title')),
			    self::get_text_decoration(' .image-content', 'text_decoration_regular'),
			    self::get_text_shadow(array(' .image-content', ' .image-title')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(' .image-content', ' .image-title'), 'f_f', 'h'),
			    self::get_color_type(array(':hover .tb_text_wrap', ':hover .image-title'),'','f_c_t_h', 'f_c_h', 'f_g_c_h'),
			    self::get_font_size(' .image-content', 'f_s', '', 'h'),
			    self::get_font_style(array(' .image-content', ' .image-title'), 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .image-content', 't_d_r', 'h'),
			    self::get_text_shadow(array(' .image-content', ' .image-title'),'t_sh','h'),
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
			    self::get_color(' a', 'link_color', null, null, 'hover'),
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
			    self::get_padding('','','',true)
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
			    self::get_margin('','','',true)
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
		self::get_expand('disp', self::get_display()),
		// Position
		self::get_expand('po', array( self::get_css_position()))
	);

	$image_title = array(
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('.module .image-title', '.module .image-title a'), 'font_family_title'),
			    self::get_color(array('.module .image-title','.module .image-title a'), 'font_color_title'),
			    self::get_font_size('.module .image-title', 'font_size_title'),
			    self::get_line_height('.module .image-title', 'line_height_title'),
			    self::get_letter_spacing('.module .image-title', 'letter_spacing_title'),
			    self::get_text_transform('.module .image-title', 'text_transform_title'),
			    self::get_font_style(array('.module .image-title', '.module .image-title a'), 'font_style_title'),
			    self::get_text_shadow('.module .image-title', 't_sh_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array('.module .image-title', '.module .image-title a'), 'f_f_t', 'h'),
			    self::get_color(array('.module .image-title','.module .image-title a'), 'f_c_t',  null, null, 'h'),
			    self::get_font_size('.module .image-title', 'f_s_t', '', 'h'),
			    self::get_font_style(array('.module .image-title', '.module .image-title a'), 'f_st_t', 'f_w_t', 'h'),
			    self::get_text_shadow('.module .image-title', 't_sh_t','h'),
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('.module .image-title', 'title_margin')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('.module .image-title', 't_m', 'h')
			)
		    )
		))
	    ))
	);

	$image_tab = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			   self::get_color(' .image-wrap img', 'i_t_b_c', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .image-wrap img', 'i_t_b_c', 'bg_c', 'background-color','h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			   self::get_padding(' .image-wrap img', 'i_t_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .image-wrap img', 'i_t_p','h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			   self::get_margin(' .image-wrap img', 'i_t_m','',true)
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .image-wrap img', 'i_t_m','h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .image-wrap img', 'i_t_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .image-wrap img', 'i_t_b','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(array(' .image-wrap img', '.image-full-overlay .image-content'), 'i_t_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(array(' .image-wrap img', '.image-full-overlay .image-content'), 'i_t_r_c', 'h')
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
							self::get_box_shadow(' .image-wrap img', 'i_t_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .image-wrap img', 'i_t_sh', 'h')
						)
					)
				))
			)
		)
	);
	$image_caption = array(
	    // Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
				   self::get_color(' .image-content', 'c_b_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .image-content', 'c_b_c', 'bg_c', 'background-color','h')
				)
				)
			))
	    )),	    // Background
	    self::get_expand(__('Caption Overlay', 'themify'), array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			self::get_color(array('.image-overlay .image-content',  '.image-full-overlay .image-content::before', '.image-card-layout .image-content'), 'b_c_c', __('Overlay', 'themify'), 'background-color'),

			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.image-overlay:hover .image-content', '.image-full-overlay:hover .image-content::before', '.image-card-layout:hover .image-content'), 'b_c_c_h', __('Overlay', 'themify'), 'background-color'),
			    self::get_color(array('.image-overlay:hover .image-title', '.image-overlay:hover .image-caption', '.image-full-overlay:hover .image-title',  '.image-full-overlay:hover .image-caption','.image-card-layout:hover .image-content', '.image-card-layout:hover .image-title'), 'f_c_c_h', __('Overlay Font Color', 'themify'))
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.module .image-caption', 'font_family_caption'),
			    self::get_color('.module .image-caption', 'font_color_caption'),
			    self::get_font_size('.module .image-caption', 'font_size_caption'),
			    self::get_font_style('.module .image-caption', 'f_fs_c', 'f_fw_c'),
			    self::get_line_height('.module  .image-caption', 'line_height_caption'),
				self::get_text_shadow('.module .image-caption', 't_sh_c'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.module .image-caption', 'f_f_c', 'h'),
			     self::get_color(array('.module:hover .image-caption', '.module:hover .image-title'), 'f_c_c_h', NULL, NULL, ''),
			    self::get_font_size('.module .image-caption', 'f_s_c', '', 'h'),
				self::get_font_style('.module .image-caption', 'f_fs_c', 'f_fw_c', 'h'),
			    self::get_text_shadow('.module .image-caption', 't_sh_c','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .image-content','c_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .image-content','c_p','h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			   self::get_margin(' .image-content', 'c_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .image-content', 'c_m','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(array(' .image-content', '.module.image-full-overlay .image-content'), 'c_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(array(' .image-content', '.module.image-full-overlay .image-content'), 'c_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .image-content', 'c_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .image-content', 'c_sh', 'h')
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
		    'label' => __('Image Title', 'themify'),
		    'options' => $image_title
		),
		'i' => array(
		    'label' => __('Image', 'themify'),
		    'options' => $image_tab
		),
		'c' => array(
		    'label' => __('Image Caption', 'themify'),
		    'options' => $image_caption
		)
	    )
	);
    }

	protected function _visual_template() {
		$module_args = self::get_module_args('mod_title_image');
		?>
		<#
		const fullwidth = data.auto_fullwidth == '1' ? 'auto_fullwidth' : '',
		    titleTag=data.title_tag?data.title_tag:'h3';
		let classWrap = data.style_image,
		    image='';
		    if (data.caption_on_overlay == 'yes' && ('image-overlay' == classWrap || 'image-full-overlay' == classWrap)){
			classWrap += ' active-caption-hover';
		    }
		#>
		<div class="module module-<?php echo $this->slug; ?> {{ fullwidth }} {{ classWrap }} {{ data.css_image }} <# data.appearance_image ? print( data.appearance_image.split('|').join(' ') ) : ''; #> tf_mw">
			<# if ( data.mod_title_image ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_image }}}<?php echo $module_args['after_title']; ?>
			<# } 
			if(data.url_image){
				const w=data.width_image || '',
					h=data.height_image || '';
				image ='<img data-w="width_image" data-h="height_image" width="'+w+'" height="'+h+'" data-name="url_image" src="'+ data.url_image +'">';
			}
			#>
			<div class="image-wrap tf_rel tf_mw">
			<# if ( data.link_image ) { #>
			<a href="{{ data.link_image }}">
				<# if( data.image_zoom_icon == 'zoom' ) {
					icon=data.param_image == 'lightbox' ? 'ti-search' : 'ti-new-window';
				#>
				<span class="zoom"><# print(api.Helper.getIcon(icon).outerHTML)#></span>
				<# } #>
				{{{ image }}}
			</a>
			<# } else { #>
			{{{ image }}}
			<# } 
			if ( 'image-overlay' != data.style_image ) { #>
			</div>
			<# } 
			if( data.title_image || data.caption_image ) { #>
			<div class="image-content">
			<# if ( data.title_image ) { #>
			<{{titleTag}} class="image-title"<# if(!data.link_image){#> contenteditable="false" data-name="title_image"<#}#>>
				<# if ( data.link_image ) { #>
				<a contenteditable="false" data-name="title_image" href="{{ data.link_image }}">{{{ data.title_image }}}</a>
				<# } else { #>
				{{{ data.title_image }}}
				<# } #>
			</{{titleTag}}>
			<# } 
			if( data.caption_image ) { #>
			<div contenteditable="false" data-name="caption_image" class="image-caption tb_text_wrap">{{{ data.caption_image }}}</div>
			<# } #>
			</div>
			<# } 
			if ( 'image-overlay' == data.style_image ) { #>
		</div>
		<# } #>
		</div>
		<?php
    }

}
new TB_Image_Module();