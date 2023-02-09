<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Service Menu
 * Description: Display a Service item
 */

class TB_Service_Menu_Module extends Themify_Builder_Component_Module {

    public function __construct() {
	parent::__construct('service-menu');
    }
    
    public function get_name(){
        return __('Service Menu', 'themify');
    }
    
    public function get_icon(){
	return 'menu-alt';
    }
	
    public function get_assets() {
	    return array(
		    'css'=>1
	    );
    }
	
    public function get_options() {
	return array(
	    array(
		'id' => 'style_service_menu',
		'type' => 'layout',
		'label' => __('Menu Style', 'themify'),
		'mode' => 'sprite',
		'options' => array(
		    array('img' => 'image_top', 'value' => 'image-top', 'label' => __('Image Top', 'themify')),
		    array('img' => 'image_left', 'value' => 'image-left', 'label' => __('Image Left', 'themify')),
		    array('img' => 'image_center', 'value' => 'image-center', 'label' => __('Image Center', 'themify')),
		    array('img' => 'image_right', 'value' => 'image-right', 'label' => __('Image Right', 'themify')),
		    array('img' => 'image_overlay', 'value' => 'image-overlay', 'label' => __('Image Overlay', 'themify')),
		    array('img' => 'image_horizontal', 'value' => 'image-horizontal', 'label' => __('Horizontal Image', 'themify'))
		)
	    ),
	    array(
		'id' => 'title_service_menu',
		'type' => 'text',
		'label' => __('Menu Title', 'themify'),
		'class' => 'large',
		'control' => array(
		    'selector' => '.tb-menu-title'
		)
	    ),
	    array(
            'id' => 'title_tag',
            'type' => 'select',
            'label' => __('Title HTML Tag', 'themify'),
            'h_tags' => true,
            'default' => 'h4'
        ),
	    array(
		'id' => 'description_service_menu',
		'type' => 'textarea',
		'label' => __('Description', 'themify'),
		'control' => array(
		    'selector' => '.tb-menu-description'
		)
	    ),
	    array(
		'id' => 'price_service_menu',
		'type' => 'text',
		'label' =>  __('Price', 'themify'),
		'class' => 'small',
		'control' => array(
		    'selector' => '.tb-menu-price'
		)
	    ),
	    array(
		    'id' => 'add_price_check',
		    'type' => 'checkbox',
		    'label'=>'',
		    'options' => array(
			    array( 'name' => 'yes', 'value' => __('Enable price options', 'themify')),
		    ),
		    'binding' => array(
			    'checked' => array(
				    'show' => '#price_fields_holder' ,
				    'hide' => 'price_service_menu' 
			    ),
			    'not_checked' => array(
				    'hide' => '#price_fields_holder',
				    'show' => 'price_service_menu'
			    )
		    ),
	    ),
	    array(
		'id' => 'price_fields_holder',
		'type' => 'builder',
		'label' => __('Price', 'themify'),
		'options' => array(
		    array(
			'id' => 'label',
			'type' => 'text',
			'label' => __('Label', 'themify'),
			'control' => array(
			    'selector' => '.tb-price-title'
			)
		    ),
		    array(
			'id' => 'price',
			'type' => 'text',
			'label' => __( 'Price', 'themify' ),
			'control' => array(
			    'selector' => '.tb-price-value'
			)
		    )
		)

	    ),
	    array(
		'id' => 'image_service_menu',
		'type' => 'image',
		'label' => __('Image URL', 'themify')
	    ),
	    array(
		'id' => 'appearance_image_service_menu',
		'type' => 'checkbox',
		'label' => __('Image Appearance', 'themify'),
		'img_appearance'=>true
	    ),
	    array(
		'id' => 'image_size_service_menu',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'width_service_menu',
		'type' => 'number',
		'label' => 'w',
		'after' => 'px'
	    ),
	    array(
		'id' => 'height_service_menu',
		'type' => 'number',
		'label' => 'ht',
		'after' => 'px'
	    ),
	    array(
		'id' => 'link_service_menu',
		'type' => 'url',
		'label' => __('Image Link', 'themify'),
		'binding' => array(
		    'empty' => array(
			'hide' => array('link_options', 'image_zoom_icon', 'lightbox_size')
		    ),
		    'not_empty' => array(
			'show' => array('link_options', 'image_zoom_icon', 'lightbox_size')
		    )
		)
	    ),
	    array(
		'id' => 'link_options',
		'type' => 'radio',
		'label' => 'o_l',
		'options' => array(
		    array('value' => 'regular', 'name' => __('Same window', 'themify')),
		    array('value' => 'lightbox', 'name' => __('Lightbox', 'themify')),
		    array('value' => 'newtab', 'name' => __('New tab', 'themify'))
		),
		'option_js' => true
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
		'type' => 'multi',
		'label' => __('Lightbox Dimension', 'themify'),
		'options' => array(
		    array(
			'id' => 'lightbox_width',
			'type' => 'number',
			'label' => 'w',
			'control' => false
		    ),
		    array(
			'id' => 'lightbox_size_unit_width',
			'type' => 'select',
			'label' => __('Units', 'themify'),
			'options' => array(
			    'pixels' => __('px ', 'themify'),
			    'percents' => __('%', 'themify')
			),
			'control' => false
		    ),
		    array(
			'id' => 'lightbox_height',
			'type' => 'number',
			'label' =>'ht',
			'control' => false
		    ),
		    array(
			'id' => 'lightbox_size_unit_height',
			'type' => 'select',
			'label' => __('Units', 'themify'),
			'options' => array(
			    'pixels' => __('px ', 'themify'),
			    'percents' => __('%', 'themify')
			),
			'control' => false
		    )
		),
		'wrap_class' => 'tb_group_element_lightbox'
	    ),
	    array(
		'id' => 'highlight_service_menu',
		'type' => 'checkbox',
		'label' => __('Highlight', 'themify'),
		'options' => array(
		    array('name' => 'highlight', 'value' => __('Highlight this item', 'themify'))
		),
		'binding' => array(
		    'checked' => array(
			'show' => array('highlight_text_service_menu', 'highlight_color_service_menu')
		    ),
		    'not_checked' => array(
			'hide' => array('highlight_text_service_menu', 'highlight_color_service_menu')
		    )
		)
	    ),
	    array(
		'id' => 'highlight_text_service_menu',
		'type' => 'text',
		'label' => '',
		'after' => __('Highlight Text', 'themify'),
		'class' => 'large',
		'control' => array(
		    'selector' => '.tb-highlight-text'
		)
	    ),
	    array(
		'id' => 'highlight_color_service_menu',
		'type' => 'layout',
		'label' => '',
		'mode' => 'sprite',
		'class' => 'tb_colors',
		'color' => true,
		'transparent'=>true
	    ),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_service_menu' ),
	);
    }

    public function get_live_default() {
	return array(
	    'title_tag' => 'h4',
	    'title_service_menu' => esc_html__('Menu title', 'themify'),
	    'description_service_menu' => esc_html__('Description', 'themify'),
	    'price_service_menu' => '$200',
	    'style_service_menu' => 'image-left',
	    'image_service_menu' => 'https://themify.me/demo/themes/wp-content/uploads/addon-samples/menu-pizza.png',
	    'width_service_menu' => 100
	);
    }

    public function get_styling() {
	$general = array(
	    // Background
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
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb-image-content'),
			    self::get_color_type(array(' .tb-menu-title', ' .tb-menu-price', ' .tb-menu-description')),
			    self::get_font_size(' .tb-image-content'),
			    self::get_line_height(' .tb-image-content'),
			    self::get_letter_spacing(' .tb-image-content'),
			    self::get_text_align(' .tb-image-content'),
			    self::get_text_transform(array(' .tb-image-content', ' .tb-price-item .tb-price-title')),
			    self::get_font_style(' .tb-image-content'),
			    self::get_text_decoration(' .tb-image-content', 'text_decoration_regular'),
			    self::get_text_shadow(' .tb-image-content'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tb-image-content', 'f_f', 'h'),
			    self::get_color_type(array(' .tb-menu-title', ' .tb-menu-price', ' .tb-menu-description'),'h'),
			    self::get_font_size(' .tb-image-content', 'f_s', '', 'h'),
			    self::get_font_style(' .tb-image-content', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .tb-image-content', 't_d_r', 'h'),
			    self::get_text_shadow(' .tb-image-content','t_sh','h'),
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

	$menu_title = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family('.module .tb-menu-title', 'font_family_title'),
			self::get_color('.module .tb-menu-title', 'font_color_title'),
			self::get_font_size('.module .tb-menu-title', 'font_size_title'),
			self::get_line_height('.module .tb-menu-title', 'line_height_title'),
			self::get_letter_spacing('.module .tb-menu-title', 'letter_spacing_title'),
			self::get_text_transform('.module .tb-menu-title', 'text_transform_title'),
			self::get_font_style('.module .tb-menu-title', 'font_style_title'),
			self::get_text_shadow('.module .tb-menu-title', 't_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family('.module .tb-menu-title', 'f_f_t', 'h'),
			self::get_color('.module .tb-menu-title', 'f_c_t', null, null, 'h'),
			self::get_font_size('.module .tb-menu-title', 'f_s_t', '', 'h'),
			self::get_font_style('.module .tb-menu-title', 'f_st_t', '','h'),
			self::get_text_shadow('.module .tb-menu-title', 't_sh_t','h'),
		    )
		)
	    ))
	);

	$image = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.module-service-menu .tb-image-wrap img', 'i_bg_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.module-service-menu .tb-image-wrap img', 'i_bg_c', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding('.module-service-menu .tb-image-wrap img', 'i_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding('.module-service-menu .tb-image-wrap img', 'i_p', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin('.module-service-menu .tb-image-wrap img', 'i_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin('.module-service-menu .tb-image-wrap img', 'i_m', 'h')
				)
				)
			))
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border('.module-service-menu .tb-image-wrap img', 'i_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border('.module-service-menu .tb-image-wrap img', 'i_b', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius('.module-service-menu .tb-image-wrap img', 'i_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius('.module-service-menu .tb-image-wrap img', 'i_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow('.module-service-menu .tb-image-wrap img', 'i_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow('.module-service-menu .tb-image-wrap img', 'i_b_sh', 'h')
					)
				)
			))
		))
	
	);

	$menu_description = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .tb-menu-description', 'font_family_description'),
			self::get_color('.module .tb-menu-description', 'font_color_description'),
			self::get_font_size(' .tb-menu-description', 'font_size_description'),
			self::get_font_style(' .tb-menu-description', 'f_fs_m', 'f_fw_m'),
			self::get_line_height(' .tb-menu-description', 'line_height_description'),
			self::get_text_shadow(' .tb-menu-description', 't_sh_d'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .tb-menu-description', 'f_f_d', 'h'),
			self::get_color('.module .tb-menu-description', 'f_c_d',  null, null, 'h'),
			self::get_font_size(' .tb-menu-description', 'f_s_d', '', 'h'),
			self::get_font_style(' .tb-menu-description', 'f_fs_m', 'f_fw_m', 'h'),
			self::get_text_shadow(' .tb-menu-description', 't_sh_d','h'),
		    )
		)
	    ))
	);

	$price = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .tb-menu-price', 'font_family_price'),
			self::get_color('.module .tb-menu-price', 'font_color_price'),
			self::get_font_size(' .tb-menu-price', 'font_size_price'),
			self::get_font_style(' .tb-menu-price', 'f_fs_p', 'f_fw_p'),
			self::get_line_height(' .tb-menu-price', 'line_height_price'),
			self::get_text_shadow(' .tb-menu-price', 't_sh_p'),
			// Margin
			self::get_heading_margin_multi_field(' .tb-menu-price', '', 'top', 't_price'),
			self::get_heading_margin_multi_field(' .tb-menu-price', '', 'bottom', 'b_price')
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .tb-menu-price', 'f_f_p', 'h'),
			self::get_color('.module .tb-menu-price', 'f_c_p',  null, null, 'h'),
			self::get_font_size(' .tb-menu-price', 'f_s_p', '', 'h'),
			self::get_font_style(' .tb-menu-price', 'f_fs_p', 'f_fw_p', 'h'),
			self::get_text_shadow(' .tb-menu-price', 't_sh_p','h'),
			self::get_heading_margin_multi_field(' .tb-menu-price:hover', '', 'top', 't_p', 'h'),
			self::get_heading_margin_multi_field(' .tb-menu-price:hover', '', 'bottom', 'b_p', 'h')
		    )
		)
	    ))
	);

	$highlight_text = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .tb-highlight-text', 'background_color_highlight_text', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .tb-highlight-text', 'b_c_h_t', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb-highlight-text', 'font_family_highlight_text'),
			    self::get_color(' .tb-highlight-text', 'font_color_highlight_text'),
			    self::get_font_size(' .tb-highlight-text', 'font_size_highlight_text'),
			    self::get_font_style(' .tb-highlight-text', 'f_fs_h', 'f_fw_h'),
			    self::get_line_height(' .tb-highlight-text', 'line_height_highlight_text'),
			    self::get_text_shadow(' .tb-highlight-text', 't_sh_h_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .tb-highlight-text', 'f_f_h_t', 'h'),
			    self::get_color(' .tb-highlight-text', 'f_c_h_t',null, null, 'h'),
			    self::get_font_size(' .tb-highlight-text', 'f_s_h_t', '', 'h'),
				self::get_font_style(' .tb-highlight-text', 'f_fs_h', 'f_fw_h', 'h'),
			    self::get_text_shadow(' .tb-highlight-text', 't_sh_h_t','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .tb-highlight-text', 'h_t_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .tb-highlight-text', 'h_t_p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .tb-highlight-text', 'h_t_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .tb-highlight-text', 'h_t_m', 'h')
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
		'm' => array(
		    'label' => __('Menu Title', 'themify'),
		    'options' => $menu_title
		),
		'i' => array(
			'label' => __('Image', 'themify'),
			'options' => $image
		),
		'c' => array(
		    'label' => __('Description', 'themify'),
		    'options' => $menu_description
		),
		'p' => array(
		    'label' => __('Price', 'themify'),
		    'options' => $price
		),
		'h' => array(
		    'label' => __('Highlight Text', 'themify'),
		    'options' => $highlight_text
		)
	    )
	);
    }

	protected function _visual_template() {
		?>
        <# var color = undefined === data.highlight_color_service_menu || 'default' == data.highlight_color_service_menu ? 'tb_default_color' : data.highlight_color_service_menu; #>
        <div class="module module-<?php echo $this->slug; ?> <# data.appearance_image_service_menu ? print( data.appearance_image_service_menu.split('|').join(' ') ) : ''; #> {{ data.style_service_menu }} {{ data.css_service_menu }} <# data.highlight_service_menu ? print( 'has-highlight ',color ) : print('no-highlight'); #>">
	    <# if (data.highlight_service_menu && data.highlight_text_service_menu !== '') { #>
                <div class="tb-highlight-text" contenteditable="false" data-name="highlight_text_service_menu">{{ data.highlight_text_service_menu}}</div>
	    <# }
			const tag = data.title_tag?data.title_tag:'h4';
			let image='';
            if (data.image_service_menu){ #>
            <div class="tb-image-wrap tf_left">
                <# 
			const icon=data.link_options == 'newtab'?'fa-external-link':'fa-search',
				tag = data.title_tag?data.title_tag:'h4',
				alt = '' !== data.title_service_menu ? data.title_service_menu : data.description_service_menu,
				w=data.width_service_menu || '',
				h=data.height_service_menu || '';
				image ='<img src="'+ data.image_service_menu +'" class="tb_menu_image" data-w="height_service_menu" data-h="height_service_menu" alt="'+alt+'" width="'+w+'" height="'+h+'">';
			 
		    if (data.link_service_menu !== '') {
			let link_attrs = data.link_options == 'lightbox' ? 'class="lightbox-builder themify_lightbox" ' : '';
			link_attrs += data.link_options == 'newtab' ? 'rel="noopener" target="_blank" ' : ''; 
			if(data.link_options == 'lightbox' && (data.lightbox_width !== '' || data.lightbox_height !== '')){
				const lightbox_settings = [],
					units = {pixels: 'px',percents: '%'};
				lightbox_settings.push(data.lightbox_width !== '' ? data.lightbox_width + units[data.lightbox_size_unit_width] : '');
				lightbox_settings.push(data.lightbox_height !== '' ? data.lightbox_height + units[data.lightbox_size_unit_height] : '');
				link_attrs += 'data-zoom-config="'+lightbox_settings.join("|");+'" ';
			}
		    #>
                <a href="{{ data.link_service_menu }}" {{ link_attrs }} >
                    <# if (data.image_zoom_icon == 'zoom' && data.link_options != 'regular') { #>
                    <span class="zoom"><# print(api.Helper.getIcon(icon).outerHTML) #></span>
                    <# } #>
					{{{ image }}}
                </a>
                <# }else{ #>
					{{{ image }}}
                <# } #>
            </div>
            <# } #>
            <div class="tb-image-content tf_overflow">
                <div class="tb-menu-title-wrap">
                    <# if (data.title_service_menu !== '') { #>
                    <{{tag}} class="tb-menu-title" contenteditable="false" data-name="title_service_menu">{{ data.title_service_menu }}</{{tag}}>
                    <# }
                    if (data.description_service_menu !== '') { #>
                    <div class="tb-menu-description" contenteditable="false" data-name="description_service_menu">{{ data.description_service_menu }}</div>
                    <# } #>
                </div>
                <!-- /tb-menu-title-wrap -->
                    <# if (data.price_service_menu !== ''  || data.add_price_check !== undefined ) { #>
                <div class="tb-menu-price"<# if(data.price_service_menu !== '' && data.add_price_check != 'yes'){#> data-name="price_service_menu" contenteditable="false"<#}#>>
		    <# if( data.price_service_menu !== '' && data.add_price_check !== 'yes' ){
				print(data.price_service_menu);
		    }
		    else if( data.add_price_check != undefined && data.add_price_check == 'yes'){
			const arr=data.price_fields_holder || [];
			for(var i=0,len=arr.length;i<len;++i){#>
                            <div class="tb-price-item">
                                <# if(arr[i].label !== ''){ #>
                                    <div class="tb-price-title" contenteditable="false" data-name="label" data-repeat="price_fields_holder" data-index="{{i}}">{{ arr[i].label }}</div>
                                <# }
                                if(arr[i].price !== ''){ #>
                                    <div class="tb-price-value" contenteditable="false" data-name="price" data-repeat="price_fields_holder" data-index="{{i}}">{{arr[i].price}}</div>
                                <# } #>
                            </div>
                        <# }
		    } #>
                </div>
                <# } #>
            </div>
        </div>

		<?php
	}

}

new TB_Service_Menu_Module();