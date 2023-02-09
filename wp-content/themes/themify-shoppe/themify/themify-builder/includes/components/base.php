<?php

class Themify_Builder_Component_Base {

    public static $disable_inline_edit = false;

    public function __construct() {

    }

    public function get_type() {
	return 'component';
    }

    protected function get_animation() {
	return true;
    }

    public function get_name() {

    }

    public final function get_class_name() {
	return get_class($this);
    }

    public static function get_tab(array $options,$fullwidth=false,$cl='') {
	$opt= array(
	    'type' => 'tabs',
	    'options' => $options
	);
	if($fullwidth===true){
	    if($cl!==''){
		$cl.=' tb_tabs_fullwidth';
	    }
	    else{
		$cl='tb_tabs_fullwidth';
	    }
	}
	if($cl!==''){
	    $opt['class']=$cl;
	}
	return $opt;
    }

    public function get_settings() {
	return [
	    [
		'id' => 'custom_css_' . $this->get_name(),
		'type' => 'custom_css'
	    ],
	    ['type' => 'tooltip'],
	    $this->get_clickable_component_settings(),
	];
    }

    public function get_styling() {
	$type = $this->get_name();
	$margin_fields = array(
	    'margin' => self::get_margin()
	);
	$margin_hover_fields = array(
	    'margin' => self::get_margin('', 'm', 'h')
	);
	$inner = __('Inner Container', 'themify');
	if ($type === 'row' || $type === 'column') {
	    $margin_fields['margin_top'] = self::get_margin_top_bottom_opposity('', 'margin-top', 'margin-bottom');
	    $margin_hover_fields['margin_top'] = self::get_margin_top_bottom_opposity('', 'm_t', 'm_b', 'h');

	    if ($type === 'row') {
		$overlay = __('Row Overlay', 'themify');
		$inner_selector = 'row_inner';
	    } else {
		$overlay = __('Column Overlay', 'themify');
		$inner_selector = 'tb-column-inner';
	    }
	    unset($margin_fields['margin'], $margin_hover_fields['margin']);
	} else {
	    $overlay = __('Subrow Overlay', 'themify');
	    $inner_selector = 'subrow_inner';
	}

	$inner_selector = array('>div.' . $inner_selector);

	if ($type === 'column') {
	    $inner_selector[] = '>.tb_holder';
	}

	// Image size
	$inner_selector_hover = array();
	foreach ($inner_selector as $item) {
	    $inner_selector_hover[] = $item . ':hover';
	}
	$options = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    array(
				'id' => 'background_type',
				'label' => __('Background Type', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'image', 'name' => __('Image', 'themify')),
				    array('value' => 'gradient', 'name' => __('Gradient', 'themify')),
				    array('value' => 'video', 'name' => __('Video', 'themify'), 'class' => 'tb_responsive_disable'),
				    array('value' => 'slider', 'name' => __('Slider', 'themify'), 'class' => 'tb_responsive_disable')
				),
				'is_background' => true,
				'wrap_class' => 'tb_compact_radios',
				'option_js' => true
			    ),
			    // Background Color
			    array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image tb_group_element_slider tb_group_element_video',
				'prop' => 'background-color',
				'selector' => ''
			    ),
			    array(
				'type' => 'group',
				'wrap_class' => 'tb_group_element_slider',
				'options' => array(
				    // Background Slider
				    array(
					'id' => 'background_slider',
					'type' => 'gallery',
					'label' => __('Background Slider', 'themify'),
					'is_responsive' => false
				    ),
				    // Background Slider Image Size
				    array(
					'id' => 'background_slider_size',
					'label' => '',
					'after' => __('Image Size', 'themify'),
					'type' => 'select',
					'image_size' => true,
					'default' => 'large',
					'is_responsive' => false
				    ),
				    // Background Slider Mode
				    array(
					'id' => 'background_slider_mode',
					'label' => '',
					'after' => __('Background Slider Mode', 'themify'),
					'type' => 'select',
					'options' => array(
					    'fullcover' => __('Fullcover', 'themify'),
					    'best-fit' => __('Best Fit', 'themify'),
					    'kenburns-effect' => __('Ken Burns Effect', 'themify')
					),
					'is_responsive' => false
				    ),
				    array(
					'id' => 'background_slider_speed',
					'label' => '',
					'after' => __('Slider Speed', 'themify'),
					'type' => 'select',
					'default' => '2000',
					'options' => array(
					    '3500' => __('Slow', 'themify'),
					    '2000' => __('Normal', 'themify'),
					    '500' => __('Fast', 'themify')
					),
					'is_responsive' => false
				    )
				)
			    ),
			    // Video Background
			    array(
				'id' => 'background_video',
				'type' => 'video',
				'label' => __('Background Video', 'themify'),
				'help' => __('Video format: mp4, YouTube, and Vimeo. Note: video background does not play on some mobile devices, background image will be used as fallback. Audio should be disabled to have auto play.', 'themify'),
				'is_responsive' => false,
				'wrap_class' => 'tb_group_element_video'
			    ),
			    array(
				'id' => 'background_video_options',
				'type' => 'checkbox',
				'label' => '',
				'options' => array(
				    array('name' => 'unloop', 'value' => __('Disable looping', 'themify')),
				    array('name' => 'mute', 'value' => __('Disable audio', 'themify'), 'help' => __('Audio must be disabled in order to auto play in most browsers.', 'themify')),
				    array('name' => 'playonmobile', 'value' => __('Mobile support', 'themify'), 'help' => __('Video must be mp4 format (YouTube or Vimeo video is not supported).', 'themify'))
				),
				'default' => 'mute',
				'is_responsive' => false,
				'wrap_class' => 'tb_group_element_video'
			    ),
			    // Background Image
			    array(
				'id' => 'background_image',
				'type' => 'image',
				'label' => 'b_i',
				'wrap_class' => 'tb_group_element_image tb_group_element_video',
				'prop' => 'background-image',
				'selector' => '',
				'binding' => array(
				    'empty' => array(
					'hide' => array('tb_image_options', 'resp_no_bg')
				    ),
				    'not_empty' => array(
					'show' => array('tb_image_options', 'resp_no_bg')
				    )
				)
			    ),
			    array(
				'id' => 'background_gradient',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_gradient',
				'prop' => 'background-image',
				'selector' => ''
			    ),
			    // No Background Image
			    array(
				'id' => 'resp_no_bg',
				'label' => '',
				'origId' => 'background_image',
				'type' => 'checkbox',
				'prop' => 'background-image',
				'options' => array(
				    array('value' => __('No background image', 'themify'), 'name' => 'none')
				),
				'binding' => array(
				    'checked' => array('hide' => array('tb_image_options', 'background_image')),
				    'not_checked' => array('show' => array('tb_image_options', 'background_image')),
				),
				'wrap_class' => 'tb_group_element_image tf_hide'
			    ),
			    array(
				'type' => 'group',
				'wrap_class' => 'tb_group_element_image tb_image_options',
				'options' => array(
				    // Background repeat
				    array(
					'id' => 'background_repeat',
					'label' => '',
					'type' => 'select',
					'after' => __('Background Mode', 'themify'),
					'prop' => 'background-mode',
					'origId' => 'background_image',
					'selector' => '',
					'options' => array(
					    'repeat' => __('Repeat All', 'themify'),
					    'repeat-x' => __('Repeat Horizontally', 'themify'),
					    'repeat-y' => __('Repeat Vertically', 'themify'),
					    'repeat-none' => __('Do not repeat', 'themify'),
					    'fullcover' => __('Fullcover', 'themify'),
					    'best-fit-image' => __('Best Fit', 'themify'),
					    'builder-parallax-scrolling' => __('Parallax Scrolling', 'themify'),
					    'builder-zoom-scrolling' => __('Zoom Scrolling', 'themify'),
					    'builder-zooming' => __('Zooming', 'themify')
					),
					'binding' => array(
					    'repeat-none' => array(
						'show' => array('background_zoom', 'background_position')
					    ),
					    'builder-parallax-scrolling' => array(
						'hide' => array('background_attachment', 'background_zoom')
					    ),
					    'builder-zoom-scrolling' => array(
						'hide' => array('background_attachment', 'background_zoom'),
						'show' => 'background_position'
					    ),
					    'builder-zooming' => array(
						'hide' => array('background_attachment', 'background_zoom'),
						'show' => 'background_position'
					    ),
					    'select' => array(
						'value' => 'repeat-none',
						'hide' => 'background_zoom',
						'show' => array('background_attachment', 'background_position')
					    )
					)
				    ),
				    // Background attachment
				    array(
					'id' => 'background_attachment',
					'label' => '',
					'type' => 'select',
					'origId' => 'background_image',
					'after' => __('Background Attachment', 'themify'),
					'options' => array(
					    'scroll' => __('Scroll', 'themify'),
					    'fixed' => __('Fixed', 'themify')
					),
					'prop' => 'background-attachment',
					'selector' => ''
				    ),
				    // Background Zoom
				    array(
					'id' => 'background_zoom',
					'label' => '',
					'origId' => 'background_image',
					'type' => 'checkbox',
					'options' => array(
					    array('value' => __('Zoom on hover', 'themify'), 'name' => 'zoom')
					),
					'is_responsive' => false
				    ),
				    // Background position
				    array(
					'id' => 'background_position',
					'label' => '',
					'origId' => 'background_image',
					'type' => 'position_box',
					'position' => true,
					'prop' => 'background-position',
					'selector' => ''
				    ),
				)
			    )
			)
		    ),
		    'h' => array(
			'options' => array(
			    array(
				'id' => 'b_t_h',
				'label' => __('Background Type', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'image', 'name' => __('Image', 'themify')),
				    array('value' => 'gradient', 'name' => __('Gradient', 'themify'))
				/*  array('value' => 'video', 'name' => __('Video', 'themify')'class' => 'tb_responsive_disable'), */
				/* array('value' => 'slider', 'name' => __('Slider', 'themify'), 'class' => 'tb_responsive_disable') */
				),
				'is_background' => true,
				'wrap_class' => 'tb_compact_radios',
				'option_js' => true
			    ),
			    // Background Image
			    array(
				'id' => 'bg_i_h',
				'type' => 'image',
				'label' => 'b_i',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-image',
				'selector' => ':hover',
				'binding' => array(
				    'empty' => array(
					'hide' => 'tb_image_options'
				    ),
				    'not_empty' => array(
					'show' => 'tb_image_options'
				    )
				)
			    ),
			    array(
				'id' => 'b_g_h',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_gradient',
				'prop' => 'background-image',
				'selector' => ':hover'
			    ),
			    array(
				'type' => 'group',
				'wrap_class' => 'tb_group_element_image tb_image_options',
				'options' => array(
				    // Background repeat
				    array(
					'id' => 'b_r_h',
					'label' => '',
					'type' => 'select',
					'origId' => 'bg_i_h',
					'after' => __('Background Mode', 'themify'),
					'prop' => 'background-mode',
					'selector' => ':hover',
					'options' => array(
					    'repeat' => __('Repeat All', 'themify'),
					    'repeat-x' => __('Repeat Horizontally', 'themify'),
					    'repeat-y' => __('Repeat Vertically', 'themify'),
					    'repeat-none' => __('Do not repeat', 'themify'),
					    'fullcover' => __('Fullcover', 'themify'),
					    'best-fit-image' => __('Best Fit', 'themify')
					)
				    ),
				    // Background attachment
				    array(
					'id' => 'b_a_h',
					'label' => '',
					'origId' => 'bg_i_h',
					'type' => 'select',
					'after' => __('Background Attachment', 'themify'),
					'options' => array(
					    'scroll' => __('Scroll', 'themify'),
					    'fixed' => __('Fixed', 'themify')
					),
					'prop' => 'background-attachment',
					'selector' => ':hover'
				    ),
				    // Background position
				    array(
					'id' => 'b_p_h',
					'label' => '',
					'origId' => 'bg_i_h',
					'type' => 'position_box',
					'position' => true,
					'prop' => 'background-position',
					'selector' => ':hover'
				    )
				)
			    ),
			    // Background Color
			    array(
				'id' => 'b_c_h',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-color',
				'selector' => ':hover'
			    )
			)
		    )
		))
	    )),
	    // Overlay Color
	    self::get_expand($overlay, array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    array(
				'id' => 'cover_color-type',
				'label' => __('Overlay', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'color', 'name' => __('Color', 'themify')),
				    array('value' => 'cover_gradient', 'name' => __('Gradient', 'themify'))
				),
				'option_js' => true,
				'is_overlay' => true
			    ),
			    array(
				'id' => 'cover_color',
				'type' => 'color',
				'label' => '',
				'wrap_class' => 'tb_group_element_color',
				'is_overlay' => true,
				'prop' => 'background-color',
				'selector' => '>.builder_row_cover::before'
			    ),
			    array(
				'id' => 'cover_gradient',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_cover_gradient',
				'is_overlay' => true,
				'prop' => 'background-image',
				'selector' => '>.builder_row_cover::before'
			    )
			)
		    ),
		    'h' => array(
			'options' => array(
			    array(
				'id' => 'cover_color_hover-type',
				'label' => __('Overlay', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'hover_color', 'name' => __('Color', 'themify')),
				    array('value' => 'hover_gradient', 'name' => __('Gradient', 'themify'))
				),
				'option_js' => true,
				'is_overlay' => true
			    ),
			    array(
				'id' => 'cover_color_hover',
				'type' => 'color',
				'label' => '',
				'wrap_class' => 'tb_group_element_hover_color',
				'is_overlay' => true,
				'prop' => 'background-color',
				'selector' => ':hover>.builder_row_cover::before'
			    ),
			    array(
				'id' => 'cover_gradient_hover',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_hover_gradient',
				'is_overlay' => true,
				'prop' => 'background-image',
				'selector' => '>.builder_row_cover::after'
			    ),
			)
		    )
		))
	    )),
	    // Inner Container
	    self::get_expand($inner, array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    // Background Image
			    array(
				'id' => 'background_image_inner',
				'type' => 'image',
				'label' => 'b_i',
				'prop' => 'background-image',
				'selector' => $inner_selector,
				'binding' => array(
				    'empty' => array(
					'hide' => 'tb_image_inner_options'
				    ),
				    'not_empty' => array(
					'show' => 'tb_image_inner_options'
				    )
				)
			    ),
			    // Background repeat
			    array(
				'id' => 'background_repeat_inner',
				'label' => '',
				'type' => 'select',
				'origId' => 'background_image_inner',
				'after' => __('Background Mode', 'themify'),
				'prop' => 'background-mode',
				'selector' => $inner_selector,
				'options' => array(
				    'repeat' => __('Repeat All', 'themify'),
				    'repeat-x' => __('Repeat Horizontally', 'themify'),
				    'repeat-y' => __('Repeat Vertically', 'themify'),
				    'repeat-none' => __('Do not repeat', 'themify'),
				    'fullcover' => __('Fullcover', 'themify'),
				    'best-fit-image' => __('Best Fit', 'themify'),
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
			    ),
			    // Background attachment
			    array(
				'id' => 'background_attachment_inner',
				'label' => '',
				'type' => 'select',
				'origId' => 'background_image_inner',
				'after' => __('Background Attachment', 'themify'),
				'options' => array(
				    'scroll' => __('Scroll', 'themify'),
				    'fixed' => __('Fixed', 'themify')
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-attachment',
				'selector' => $inner_selector
			    ),
			    // Background position
			    array(
				'id' => 'background_position_inner',
				'label' => '',
				'type' => 'position_box',
				'origId' => 'background_image_inner',
				'position' => true,
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-position',
				'selector' => $inner_selector
			    ),
			    // Background Color
			    array(
				'id' => 'background_color_inner',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-color',
				'selector' => $inner_selector
			    ),
			    self::get_padding($inner_selector, 'padding_inner'),
			    self::get_border($inner_selector, 'border_inner')
			)
		    ),
		    'h' => array(
			'options' => array(
			    // Background Image
			    array(
				'id' => 'b_i_i_h',
				'type' => 'image',
				'label' => 'b_i',
				'prop' => 'background-image',
				'selector' => $inner_selector_hover,
				'binding' => array(
				    'empty' => array(
					'hide' => 'tb_image_inner_options'
				    ),
				    'not_empty' => array(
					'show' => 'tb_image_inner_options'
				    )
				)
			    ),
			    // Background repeat
			    array(
				'id' => 'b_r_i_h',
				'label' => '',
				'origId' => 'b_i_i_h',
				'type' => 'select',
				'after' => __('Background Mode', 'themify'),
				'prop' => 'background-mode',
				'selector' => $inner_selector_hover,
				'options' => array(
				    'repeat' => __('Repeat All', 'themify'),
				    'repeat-x' => __('Repeat Horizontally', 'themify'),
				    'repeat-y' => __('Repeat Vertically', 'themify'),
				    'repeat-none' => __('Do not repeat', 'themify'),
				    'fullcover' => __('Fullcover', 'themify'),
				    'best-fit-image' => __('Best Fit', 'themify'),
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
			    ),
			    // Background attachment
			    array(
				'id' => 'b_a_i_h',
				'label' => '',
				'origId' => 'b_i_i_h',
				'type' => 'select',
				'after' => __('Background Attachment', 'themify'),
				'options' => array(
				    'scroll' => __('Scroll', 'themify'),
				    'fixed' => __('Fixed', 'themify')
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-attachment',
				'selector' => $inner_selector_hover
			    ),
			    // Background position
			    array(
				'id' => 'b_p_i_h',
				'label' => '',
				'origId' => 'b_i_i_h',
				'type' => 'position_box',
				'position' => true,
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-position',
				'selector' => $inner_selector_hover
			    ),
			    // Background Color
			    array(
				'id' => 'b_c_i_h',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-color',
				'selector' => $inner_selector_hover
			    ),
			    self::get_padding($inner_selector, 'p_i', 'h'),
			    self::get_border($inner_selector, 'b_i', 'h')
			)
		    )
		))
	    )),
	    //frame
	    self::get_expand(__('Frame', 'themify'), array(
		self::get_frame_tabs()
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6')),
			    self::get_color(array('', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6'), 'font_color'),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(),
			    self::get_text_decoration('', 'text_decoration_regular'),
			    self::get_text_shadow(array('', ' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(':hover', ':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 'f_f_h'),
			    self::get_color(array(':hover', ':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 'f_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_line_height('', 'l_h', 'h'),
			    self::get_letter_spacing('', 'l_s', 'h'),
			    self::get_text_align('', 't_a', 'h'),
			    self::get_text_transform('', 't_t', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
			    self::get_text_shadow(array(':hover', ':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 't_sh', 'h'),
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
			    self::get_color(' a', 'l_c', null, null, 'h'),
			    self::get_text_decoration(' a', 't_d', 'h')
			)
		    )
		))
		    )
	    ),
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
		    )
	    )
	);
	// Margin
	$options[] = self::get_expand('m', array(
		    self::get_tab(array(
			'n' => array(
			    'options' => $margin_fields
			),
			'h' => array(
			    'options' => $margin_hover_fields
			)
		    ))
	));
	// Border
	$options[] = self::get_expand('b', array(
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
	));
	// Filter
	$options[] = self::get_expand('f_l',
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
	);

	// Height & Min Height
	if ($type === 'row' || $type === 'column' || $type === 'subrow') {
	    $options[] = self::get_expand('ht', array(
			self::get_height(),
			self::get_min_height(),
			self::get_max_height()
			    )
	    );
	}
	// Rounded Corners
	$options[] = self::get_expand('r_c', array(
		    self::get_tab(array(
			'n' => array(
			    'options' => array(
				self::get_border_radius(array('', '>.builder_row_cover::before'))
			    )
			),
			'h' => array(
			    'options' => array(
				self::get_border_radius(array(':hover', '>.builder_row_cover:hover::before'), 'r_c')
			    )
			)
		    ))
			)
	);

	// Shadow
	$options[] = self::get_expand('sh', array(
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
	);

	// Position
	if ($type === 'row' || $type === 'subrow' || $type === 'column') {
	    $options[] = self::get_expand('po', array(
			self::get_css_position()
	    ));
	}

	// Add z-index filed to Rows and Columns
	$options[] = self::get_expand('zi',
			array(
			    self::get_zindex()
			)
	);
	// Add Transform filed to Rows and Columns
	$options[] = self::get_expand('tr', array(
		    self::get_tab(array(
			'n' => array('options' => self::get_transform()),
			'h' => array('options' => self::get_transform('', 'tr', 'h'))
		    ))
	));
	if ($type === 'row' || $type === 'subrow') {
	    $options[] = array(
		'hide' => true,
		'id' => 'grid', //reserved id
		'type' => 'grid',
		'selector' => '>.' . $type . '_inner'
	    );
	    $options[] = array(
		'hide' => true,
		'id' => 'gutter', //reserved id
		'prop' => '--colG',
		'type' => 'grid',
		'selector' => '>.' . $type . '_inner'
	    );
	    $options[] = array(
		'hide' => true,
		'id' => 'rgutter', //reserved id
		'prop' => '--rowG',
		'type' => 'grid',
		'selector' => '>.' . $type . '_inner'
	    );
	}
	return apply_filters('themify_builder_' . $type . '_fields_styling', $options);
    }

    protected static function get_responsive_cols(array $row) {
	$cl = array();
	$isFullpage=function_exists('themify_theme_is_fullpage_scroll') && themify_theme_is_fullpage_scroll();
	if (!empty($row['sizes'])) {
	    $_arr = array('align', 'gutter', 'auto_h', 'dir');
	    foreach ($_arr as $k) {
		if (!empty($row['sizes']['desktop_' . $k])) {
		    $v = $row['sizes']['desktop_' . $k];
		    if ($k === 'align') {
			if ($v === 'center') {
			    $v = 'col_align_middle';
			} elseif ($v === 'end') {
			    $v = 'col_align_bottom';
			} else {
			    $v = 'col_align_top';
			}
		    } elseif ($k === 'gutter') {
			$v = $v === 'narrow' || $v === 'none' ? 'gutter-' . $v : '';
		    } elseif ($k === 'auto_h') {
			$v = $v == '1' ? 'col_auto_height' : '';
		    } elseif ($k === 'auto_h') {
			$v = $v == '1' ? 'col_auto_height' : '';
		    } elseif ($k === 'dir') {
			$v = $v === 'rtl' ? 'direction_rtl' : '';
		    }
		    if ($v !== '') {
			$cl[] = $v;
		    }
		}
		elseif($k==='align'){
		    $cl[] = $isFullpage===true? 'col_align_middle' : 'col_align_top';
		}
	    }
	} else {
	    if (!empty($row['column_h'])) {
		$cl[] = 'col_auto_height';
	    }
	    if (!empty($row['gutter']) && $row['gutter'] !== 'gutter-default') {
		$cl[] = $row['gutter'];
	    }
	    if (isset($row['desktop_dir']) && $row['desktop_dir'] === 'rtl') {
		$cl[] = 'direction_rtl';
	    }
	    $cl[] = !empty($row['column_alignment']) ? $row['column_alignment'] : ($isFullpage===true? 'col_align_middle' : 'col_align_top');
	}

	$cl[] = 'tb_col_count_' . count($row['cols']);

	return $cl;
    }

    protected static function get_frame_tabs($selector = '') {

	return self::get_tab(
			array(
			    'top' => array(
				'label' => 'top',
				'options' => self::get_frame_props($selector),
			    ),
			    'bottom' => array(
				'label' => 'bottom',
				'options' => self::get_frame_props($selector, 'bottom')
			    ),
			    'left' => array(
				'label' => 'left',
				'options' => self::get_frame_props($selector, 'left')
			    ),
			    'right' => array(
				'label' => 'right',
				'options' => self::get_frame_props($selector, 'right')
			    )
			)
	);
    }

    private static function get_frame_props($selector = '', $id = 'top') {
	return array(
	    array(
		'id' => $id . '-frame_type',
		'type' => 'radio',
		'options' => array(
		    /**
		     * @note the value in this option is prefixed with $id, this is to ensure option_js works properly
		     */
		    array('value' => $id . '-presets', 'name' => __('Presets', 'themify')),
		    array('value' => $id . '-custom', 'name' => __('Custom', 'themify')),
		),
		'prop' => 'frame-custom',
		'wrap_class' => 'tb_frame',
		/**
		 * the second selector is for themes with Builder Section Scrolling feature
		 * @ref #7241
		 */
		'selector' => $selector . '>.tb_row_frame_wrap .tb_row_frame_' . $id,
		'option_js' => true
	    ),
	    array(
		'id' => $id . '-frame_layout',
		'type' => 'frame',
		'prop' => 'frame',
		'wrap_class' => 'frame_tabs tb_group_element_' . $id . '-presets',
		'selector' => $selector . '>.tb_row_frame_wrap .tb_row_frame_' . $id
	    ),
	    array('id' => $id . '-frame_custom',
		'type' => 'image',
		'label'=>'',
		'class' => 'tb_frame',
		'wrap_class' => 'tb_group_element_' . $id . '-custom'
	    ),
	    array('id' => $id . '-frame_color',
		'type' => 'color',
		'label' => 'c',
		'class' => 'tb_frame small',
		'wrap_class' => 'tb_group_element_' . $id . '-presets'
	    ),
	    array(
		'type' => 'multi',
		'label' => __('Dimension', 'themify'),
		'options' => array(
		    array('id' => $id . '-frame_width',
			'type' => 'range',
			'class' => 'tb_frame xsmall',
			'label' => 'w',
			'select_class' => 'tb_frame_unit',
			'units' => array(
			    '%' => array(
				'max' => 1000
			    ),
			    'px' => array(
				'max' => 10000
			    ),
			    'em' => array(
				'max' => 50
			    )
			)
		    ),
		    array('id' => $id . '-frame_height',
			'type' => 'range',
			'label' => '',
			'class' => 'tb_frame xsmall',
			'label' => 'ht',
			'select_class' => 'tb_frame_unit',
			'units' => array(
			    '%' => array(
				'max' => 1000
			    ),
			    'px' => array(
				'max' => 10000
			    ),
			    'em' => array(
				'max' => 50
			    )
			)
		    ),
		    array(
			'id' => $id . '-frame_repeat',
			'type' => 'range',
			'label' => 'r',
			'class' => 'tb_frame'
		    )
		)
	    ),
	    array(
		'type' => 'multi',
		'label' => __('Shadow', 'themify'),
		'options' => array(
		    array(
			'id' => $id . '-frame_sh_x',
			'type' => 'range',
			'class' => 'tb_frame xsmall',
			'tooltip' => __('Left Offset', 'themify'),
		    ),
		    array(
			'id' => $id . '-frame_sh_y',
			'type' => 'range',
			'class' => 'tb_frame xsmall',
			'tooltip' => __('Top Offset', 'themify'),
		    ),
		    array(
			'id' => $id . '-frame_sh_b',
			'type' => 'range',
			'class' => 'tb_frame',
			'tooltip' => 'blur'
		    ),
		    array(
			'id' => $id . '-frame_sh_c',
			'type' => 'color',
			'class' => 'tb_frame',
			'tooltip' => 'color'
		    ),
		)
	    ),
	    array(
		'type' => 'multi',
		'label' => __('Animation', 'themify'),
		'options' => array(
		    array(
				'id' => $id . '-frame_ani_dur',
				'type' => 'range',
				'units' => array(
					'' => array(
						'increment' => .1
					)
				),
				'class' => 'tb_frame xsmall',
				'tooltip' => __('Duration', 'themify'),
		    ),
		    array(
				'id' => $id . '-frame_ani_rev',
				'type' => 'toggle_switch',
				'options' => [
					'on' => array( 'name' => '1', 'value' => __('Reverse', 'themify') ),
					'off' => array( 'name' => '0', 'value' => __('Reverse', 'themify') )
				],
				'wrap_class' => 'tb_frame',
		    ),
		)
	    ),
	    array(
		'id' => $id . '-frame_location',
		'label'=>'',
		'type' => 'select',
		'is_responsive' => false,
		'class' => 'tb_frame',
		'options' => array(
		    'in_bellow' => __('Display below content', 'themify'),
		    'in_front' => __('Display above content', 'themify')
		)
	    ),
	);
    }

    /**
     * Return the correct animation css class name
     * @param string $effect
     * @return string
     */
    public static function parse_animation_effect($settings, array $attr = array()) {
	/* backward compatibility for addons */
	if (!is_array($settings)) {
	    return '';
	}
	static $has = null;
	if ($has === null) {
	    $has = Themify_Builder_Model::is_animation_active();
	}
	if ($has !== false) {
	    if (!empty($settings['hover_animation_effect'])) {
		$attr['data-tf-animation_hover'] = $settings['hover_animation_effect'];
		if (isset($attr['class'])) {
		    $attr['class'] .= ' hover-wow';
		} else {
		    $attr['class'] = 'hover-wow';
		}
		if ($has !== 'done') {
		    $has = 'load';
		}
	    }
	    if (!empty($settings['animation_effect'])) {
		$attr['data-tf-animation'] = $settings['animation_effect'];
		if (!in_array($settings['animation_effect'], array('fade-in', 'fly-in', 'slide-up'), true)) {
		    if (isset($attr['class'])) {
			$attr['class'] .= ' wow';
		    } else {
			$attr['class'] = 'wow';
		    }
		}
		if (!empty($settings['animation_effect_delay'])) {
		    $attr['data-tf-animation_delay'] = $settings['animation_effect_delay'];
		}
		if (!empty($settings['animation_effect_repeat'])) {
		    $attr['data-tf-animation_repeat'] = $settings['animation_effect_repeat'];
		}
		if ($has !== 'done') {
		    $has = 'load';
		}
	    }
	    if ($has === 'load') {
		$has = 'done';
		Themify_Enqueue_Assets::preFetchAnimtion();
	    }
	}
	return $attr;
    }

    /**
     * Retrieve builder templates
     * @param $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     * @param bool $echo
     * @return string
     */
    public static function retrieve_template($template_name, $args = array(), $template_path = '', $default_path = '', $echo = true) {
	if ($echo === false) {
	    ob_start();
	}
	self::get_template($template_name, $args, $template_path, $default_path);
	if ($echo === false) {
	    return ob_get_clean();
	}
    }

    /**
     * Get template builder
     * @param $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     */
    public static function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
	static $paths = array();
	$key = $template_path . $template_name;
	if (!isset($paths[$key])) {
	    $paths[$key] = self::locate_template($template_name, $template_path, $default_path);
	}
	if (isset($paths[$key]) && $paths[$key]!=='') {
	    global $ThemifyBuilder;
	    include($paths[$key]);
	}
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     * 		yourtheme		/	$template_path	/	$template_name
     * 		$default_path	/	$template_name
     */
    public static function locate_template($template_name, $template_path = '', $default_path = '') {
		static $theme_dir = null;
		static $child_dir = null;
		$template = '';

		$DS = DIRECTORY_SEPARATOR;
		if ($theme_dir === null) {
		    $builderDir = $DS . 'themify-builder' . $DS;
		    $theme_dir = get_template_directory() . $builderDir;
		    if(!is_dir($theme_dir)){
			$theme_dir=false;
		    }
		    if (is_child_theme()) {
			$child_dir = get_stylesheet_directory() . $builderDir;
			if(!is_dir($child_dir)){
				$child_dir=false;
			}
		    }
		    $builderDir = null;
		}
		if($theme_dir!==false || $child_dir!==null || $child_dir!==false){
		    $templates = array();
		    if ($child_dir !== null && $child_dir !== false) {
			$templates[] = $child_dir;
		    }
		    if($theme_dir!==false){
			$templates[] = $theme_dir;
		    }
		    foreach ($templates as $dir) {//is theme file
			if (is_file($dir . $template_name)) {
				$template = $dir . $template_name;
				break;
			}
		    }
		    unset($templates);
		}
		if ($template === '') {
		    if ($template_path === '') {
			$modulesPath = Themify_Builder_Model::get_directory_path();
			if (strpos($template_name, 'template-') === 0) {
				$module = str_replace(array('template-', '.php'), '', $template_name);
				if (isset($modulesPath[$module])) {
				$template = pathinfo($modulesPath[$module], PATHINFO_DIRNAME) . $DS . 'templates' . $DS . $template_name;
				}
			}
			if ($template === '') {
			    $dir = rtrim(THEMIFY_BUILDER_TEMPLATES_DIR, $DS) . $DS;
			    if (is_file($dir . $template_name)) {
			    $template = $dir . $template_name;
			    }
			    // Get default template
			    if ($template === '') {
				foreach ($modulesPath as $m) {//backward
					$dir = pathinfo($m, PATHINFO_DIRNAME) . $DS . 'templates' . $DS . $template_name;
					if (is_file($dir)) {
					$template = $dir;
					break;
					}
				}
				if ($template === '') {
					$template = $default_path . $template_name;
					if(is_file($template)){
						$template='';
					}
				}
			    }
			}
		    }
		    else {
			$template = rtrim($template_path, $DS) . $DS . $template_name;
		    }
		}
		// Return what we found
		return apply_filters('themify_builder_locate_template', $template, $template_name, $template_path);
	}

    /**
     * Get checkbox data
     * @param $setting
     * @return string
     */
    public static function get_checkbox_data($setting) {
	return implode(' ', explode('|', $setting));
    }

    /**
     * Return only value setting
     * @param $string
     * @return string
     */
    public static function get_param_value($string) {
	$val = explode('|', $string);
	return $val[0];
    }

    /**
     * Helper to get element attributes return as string.
     *
     * @access public
     * @param array $props
     * @return string
     */
    public static function get_element_attributes($props) {
	return themify_get_element_attributes($props);
    }

    /**
     * Get query page
     */
    public static function get_paged_query() {
	global $wp;
	if(isset($_GET['tf-page']) && is_numeric($_GET['tf-page'])){
	    $page=(int)$_GET['tf-page'];
	}
	else{
	    $page = 1;
	    $qpaged = get_query_var('paged');
	    if (!empty($qpaged)) {
		$page = $qpaged;
	    } else {
		$qpaged = wp_parse_args($wp->matched_query);
		if (isset($qpaged['paged']) && $qpaged['paged'] > 0) {
		    $page = $qpaged['paged'];
		}
	    }
	}
	return $page;
    }


    public static function query($args){
	$isPaged= isset($args['paged']) && $args['paged']>1;
	$hasSticky=isset($args['ignore_sticky_posts']) && $args['ignore_sticky_posts']===false;
	$maxPage=(int)$args['posts_per_page'];
	if($hasSticky===true){
	    $sticky_posts = get_option( 'sticky_posts' );
	    if(empty($sticky_posts)){
		$hasSticky=false;
	    }
	    else{
		$sticky_posts=array_slice($sticky_posts,0,$maxPage);
	    }
	}
	if($hasSticky===true && $isPaged===false){
	    $params=array(
		'post_status'=> 'publish',
		'post_type'  => $args['post_type'],
		'post__in'=>$sticky_posts,
		'orderby'=>'post__in',
		'posts_per_page'=>$maxPage,
		'ignore_sticky_posts'=>true
	    );
	    if(isset($args['tax_query'])){
		$params['tax_query']=$args['tax_query'];
	    }
	    if(isset($args['meta_key'])){
		$params['meta_key']=$args['meta_key'];
	    }
	    if(isset($args['post__not_in'])){
		$params['post__not_in']=$args['post__not_in'];
	    }
	    $the_query=new WP_Query($params);
	    if($the_query->post_count<$maxPage){
		if(isset($args['post__not_in'])){
		    $args['post__not_in']+=$sticky_posts;
		}
		else{
		    $args['post__not_in']=$sticky_posts;
		}
		$args['ignore_sticky_posts']=true;
		$args['posts_per_page']=$maxPage-$the_query->post_count;
		$q=new WP_Query($args);
		$the_query->found_posts=$q->found_posts;
		$the_query->posts = array_merge( $the_query->posts, $q->posts );
		$the_query->post_count=count($the_query->posts);
		$the_query->max_num_pages=ceil( $the_query->found_posts / $maxPage );
		unset($q,$args);
	    }
	}
	else{
	    if($isPaged===true && $hasSticky===true){
		if(isset($args['post__not_in'])){
		    $args['post__not_in']+=$sticky_posts;
		}
		else{
		    $args['post__not_in']=$sticky_posts;
		}
	    }
	    $the_query=new WP_Query($args);
	}

	return $the_query;
    }
    /**
     * Returns Pagination
     * @param string Markup to show before pagination links
     * @param string Markup to show after pagination links
     * @param object WordPress query object to use
     * @param original_offset number of posts configured to skip over
     * @return string
     */
    public static function get_pagination($before = '', $after = '', $query = false, $original_offset = 0, $max_page = 0, $paged = 0) {
	if (false == $query) {
	    global $wp_query;
	    $query = $wp_query;
	}
	if ($paged === 0) {
	    $paged = (int) self::get_paged_query();
	}
	if ($max_page === 0) {
	    $numposts = $query->found_posts;
	    $original_offset = (int) $original_offset;
	    // $query->found_posts does not take offset into account, we need to manually adjust that
	    if ($original_offset > 0) {
		$numposts -= $original_offset;
	    }
	    $max_page = ceil($numposts / $query->query_vars['posts_per_page']);
	}
	if ($max_page > 1) {
	    Themify_Builder_Model::loadCssModules('pagenav', THEMIFY_BUILDER_CSS_MODULES . 'pagenav.css', THEMIFY_VERSION);
	    if(!is_string($query) && is_single()){
		$query = 'tf-page';
	    }
	 }
	return themify_get_pagenav($before, $after, $query, $max_page, $paged);
    }

    public static function get_pagenav($before = '', $after = '', $query = false, $original_offset = 0) {//backward compatibility for addons,deprecated use get_pagination
	return self::get_pagination($before, $after, $query, $original_offset);
    }

    public static function get_seperator($label = false) {
	$opt = array(
	    'type' => 'separator'
	);
	if ($label !== false) {
	    $opt['label'] = $label;
	}
	return $opt;
    }

    public static function get_expand($label, array $options) {
	return array(
	    'type' => 'expand',
	    'label' => $label,
	    'options' => $options
	);
    }

    protected static function get_font_family($selector = '', $id = 'font_family', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'font_select',
	    'label' => 'f_f',
	    'prop' => 'font-family',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_element_font_weight($selector = '', $id = 'element_font_weight', $state = '') {//backward compatibility
    }

    protected static function get_font_size($selector = '', $id = 'font_size', $label = '', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if ($label === '') {
	    $label = 'f_s';
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => $label,
	    'selector' => $selector,
	    'prop' => 'font-size',
	    'units' => array(
		'px' => array(
		    'min' => 6,
		    'max' => 900
		),
		'em' => array(
		    'min' => .5,
		    'max' => 50
		),
		'%' => array(
		    'min' => 70,
		    'max' => 4000
		),
		'vw' => array(
		    'min' => 1,
		    'max' => 100
		),
		'rem' => array(
		    'min' => .5,
		    'max' => 50
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_line_height($selector = '', $id = 'line_height', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'l_h',
	    'selector' => $selector,
	    'prop' => 'line-height',
	    'units' => array(
		'px' => array(
		    'min' => -400,
		    'max' => 400
		),
		'em' => array(
		    'min' => -50,
		    'max' => 50
		),
		'%' => array(
		    'min' => 100,
		    'max' => 4000
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_letter_spacing($selector = '', $id = 'letter_spacing', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'l_s',
	    'selector' => $selector,
	    'prop' => 'letter-spacing',
	    'units' => array(
		'px' => array(
		    'min' => -50,
		    'max' => 500
		),
		'em' => array(
		    'min' => -3,
		    'max' => 50
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }
    
    protected static function get_flex_align($selector = '', $id = 'align', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_a',
	    'type' => 'icon_radio',
	    'falign' => true,
	    'prop' => 'align-content',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_flex_align_items($selector = '', $id = 'align', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_a',
	    'type' => 'icon_radio',
	    'falign' => true,
	    'prop' => 'align-items',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_flex_align_content($selector = '', $id = 'align', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_a',
	    'type' => 'icon_radio',
	    'falign' => true,
	    'prop' => 'align-content',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_align($selector = '', $id = 'text_align', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_a',
	    'type' => 'icon_radio',
	    'aligment' => true,
	    'prop' => 'text-align',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_transform($selector = '', $id = 'text_transform', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_t',
	    'type' => 'icon_radio',
	    'text_transform' => true,
	    'prop' => 'text-transform',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_decoration($selector = '', $id = 'text_decoration', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'icon_radio',
	    'label' => 't_d',
	    'text_decoration' => true,
	    'prop' => 'text-decoration',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_font_style($selector = '', $id = 'font_style', $id2 = 'font_weight', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	    $id2 .= '_' . $state;
	}
	$res = array(
	    'type' => 'multi',
	    'wrap_class' => 'tb_multi_fonts',
	    'label' => 'f_st',
	    'options' => array(
		array(
		    'id' => $id . '_regular',
		    'type' => 'icon_radio',
		    'font_style' => true,
		    'prop' => 'font-style',
		    'selector' => $selector
		),
		array(
		    'id' => $id2,
		    'type' => 'icon_radio',
		    'font_weight' => true,
		    'prop' => 'font-weight',
		    'selector' => $selector
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    foreach ($res['options'] as $k => $v) {
		$res['options'][$k]['ishover'] = true;
	    }
	}
	return $res;
    }

    protected static function get_color($selector = '', $id = '', $label = null, $prop = 'color', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if ($prop === null) {
	    $prop = 'color';
	}
	if ($label === null) {
	    $label = 'c';
	}
	$color = array(
	    'id' => $id,
	    'type' => 'color',
	    'prop' => $prop,
	    'selector' => $selector
	);
	if ($label) {
	    $color['label'] = $label;
	}
	if ($state === 'h' || $state === 'hover') {
	    $color['ishover'] = true;
	}
	return $color;
    }

    protected static function get_image($selector = '', $id = 'background_image', $colorId = 'background_color', $repeatId = 'background_repeat', $posId = 'background_position', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	    if ($colorId !== '') {
		$colorId .= '_' . $state;
	    }
	    if ($repeatId !== '') {
		$repeatId .= '_' . $state;
	    }
	    if ($posId !== '') {
		$posId .= '_' . $state;
	    }
	}
	$res = array(
	    'id' => $id,
	    'type' => 'imageGradient',
	    'label' => 'bg',
	    'prop' => 'background-image',
	    'selector' => $selector,
	    'option_js' => true,
	    'origId' => $id,
	    'colorId' => $colorId,
	    'repeatId' => $repeatId,
	    'posId' => $posId,
	    'binding' => array(
		'empty' => array(
		    'hide' => 'tb_image_options'
		),
		'not_empty' => array(
		    'show' => 'tb_image_options'
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    // CSS Filters
    protected static function get_blend($selector = '', $id = 'bl_m', $state = '', $filters_id = 'css_f') {
	if ($state !== '') {
	    $id .= '_' . $state;
	    $filters_id .= '_' . $state;
	}
	$res = array();
	$res[] = array('id' => $id,
	    'label' => 'b_m',
	    'type' => 'select',
	    'prop' => 'mix-blend-mode',
	    'is_responsive' => false,
	    'selector' => $selector,
	    'blend' => true
	);
	$res[] = array(
	    'id' => $filters_id,
	    'type' => 'filters',
	    'is_responsive' => false,
	    'prop' => 'filter',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res[0]['ishover'] = true;
	    $res[1]['ishover'] = true;
	}
	return $res;
    }

    protected static function get_repeat($selector = '', $id = 'background_repeat', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 'b_r',
	    'type' => 'select',
	    'repeat' => true,
	    'prop' => 'background-mode',
	    'selector' => $selector,
	    'wrap_class' => 'tb_group_element_image tb_image_options'
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_position($selector = '', $id = 'background_position', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 'b_p',
	    'type' => 'position_box',
	    'position' => true,
	    'prop' => 'background-position',
	    'selector' => $selector,
	    'wrap_class' => 'tb_group_element_image tb_image_options'
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_padding($selector = '', $id = 'padding', $state = '') {
	if ($id === '') {
	    $id = 'padding';
	}
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'padding',
	    'label' => 'p',
	    'prop' => 'padding',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_margin($selector = '', $id = 'margin', $state = '') {
	if ($id === '') {
	    $id = 'margin';
	}
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'margin',
	    'label' => 'm',
	    'prop' => 'margin',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_gap($selector = '', $id = 'gap',$prop='gap', $state = '',$percent=false,$label='') {
	if ($id === '') {
	    $id = 'gap';
	}
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$units=array(
	    'px' => array(
		'max' => 1000
	    ),
	    'em' => array(
		'max' => 50
	    )
	);
	if($percent!==false && $percent!==''){
	    $units['%']=$percent===true?'':$percent;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => $label===''?($prop==='column-gap'?'ng':($prop==='row-gap'?'rg':'gap')):$label,
	    'prop' => $prop,
	    'selector' => $selector,
	    'grid_gap'=>1,
	    'units' => $units
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_column_gap($selector = '', $id = 'cgap', $state = '',$percent=false,$label='') {
	if ($id === '') {
	    $id = 'cgap';
	}
	return self::get_gap($selector,$id,'column-gap',$state,$percent,$label);
    }

    protected static function get_row_gap($selector = '',$id = 'rgap',$state = '',$percent=false,$label='') {
	if ($id === '') {
	    $id = 'rgap';
	}
	return self::get_gap($selector,$id,'row-gap',$state,$percent,$label);
    }




    protected static function get_margin_top($selector = '', $id = 'margin-top', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'm',
	    'prop' => 'margin-top',
	    'selector' => $selector,
	    'description' => '<span class="tb_range_after">' . __('Top', 'themify') . '</span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -50,
		    'max' => 50
		),
		'%' => array(
		    'min' => -100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_margin_bottom($selector = '', $id = 'margin-bottom', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => '',
	    'prop' => 'margin-bottom',
	    'selector' => $selector,
	    'description' => '<span class="tb_range_after">' . __('Bottom', 'themify') . '</span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -50,
		    'max' => 50
		),
		'%' => array(
		    'min' => -100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_margin_top_bottom_opposity($selector = '', $topId = 'margin-top', $bottomId = 'margin-bottom', $state = '') {
	if ($state !== '') {
	    $topId .= '_' . $state;
	    $bottomId .= '_' . $state;
	}
	$res = array(
	    'topId' => $topId,
	    'bottomId' => $bottomId,
	    'type' => 'margin_opposity',
	    'label' => 'm',
	    'prop' => '',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -50,
		    'max' => 50
		),
		'%' => array(
		    'min' => -100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_border($selector = '', $id = 'border', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'border',
	    'label' => 'b',
	    'prop' => 'border',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_outline($selector = '', $id = 'o', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'outline',
	    'label' => 'o',
	    'prop' => 'outline',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_width($selector = '', $id = 'width', $state = '') {

	if ($state !== '') {
	    $id .= '_' . $state;
	}

	$res = array(
	    'id' => $id,
	    'type' => 'width',
	    'prop' => 'width',
	    'label' => '',
	    'selector' => $selector,
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_multi_columns_count($selector = '', $id = 'column', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id . '_count',
	    'type' => 'multiColumns',
	    'label' => 'c_c',
	    'prop' => 'column-count',
	    'binding' => array(
		'empty' => array(
		    'hide' => 'tb_multi_columns_wrap'
		),
		'not_empty' => array(
		    'show' => 'tb_multi_columns_wrap'
		)
	    ),
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_multi_columns_gap($selector = '', $id = 'column', $state = '') {//backward compatibility
    }

    protected static function get_multi_columns_divider($selector = '', $id = 'column', $state = '') {//backward compatibility
    }

    protected static function get_heading_margin_multi_field($selector = '', $h_level = 'h1', $margin_side = 'top', $state = '', $id = '') {
	$id = $id === '' ? $h_level : $id;
	if ($h_level === '') {
	    $h_level .= ' ';
	}
	$id = $id . '_margin_' . $margin_side;
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if ($selector !== '' && is_array($selector)) {
	    foreach ($selector as $key => $val) {
		$selector[$key] = $val . ' ' . $h_level;
	    }
	} else {
	    $selector .= ' ' . $h_level;
	}
	$res = array(
	    'label' => ('top' === $margin_side ? 'm' : ''),
	    'id' => $id,
	    'type' => 'range',
	    'prop' => 'margin-' . $margin_side,
	    'selector' => $selector,
	    'description' => '<span class="tb_range_after">' . sprintf(__('%s', 'themify'), $margin_side) . '</span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -50,
		    'max' => 50
		),
		'%' => ''
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_color_type($selector = '', $state = '', $id = '', $solid_id = '', $gradient_id = '') {
	if ($state !== '') {
	    if ($id === '') {
		$id = 'f_c_t';
	    }
	    if ($solid_id === '') {
		$solid_id = 'f_c';
	    }
	    if ($gradient_id === '') {
		$gradient_id = 'f_g_c';
	    }
	    $id .= '_' . $state;
	    $solid_id .= '_' . $state;
	    $gradient_id .= '_' . $state;
	} else {
	    if ($id === '') {
		$id = 'font_color_type';
	    }
	    if ($solid_id === '') {
		$solid_id = 'font_color';
	    }
	    if ($gradient_id === '') {
		$gradient_id = 'font_gradient_color';
	    }
	}

	$res = array(
	    'id' => $id,
	    'type' => 'fontColor',
	    'selector' => $selector,
	    'prop' => 'radio',
	    'label' => 'f_c',
	    's' => $solid_id,
	    'g' => $gradient_id
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    /**
     * Computes and returns data for Builder row or column video background.
     *
     * @since 2.3.3
     *
     * @param array $styling The row's or column's styling array.
     *
     * @return bool|string Return video data if row/col has a background video, else return false.
     */
    protected static function get_video_background($styling) {
	if (!( isset($styling['background_type']) && 'video' === $styling['background_type'] && !empty($styling['background_video']) )) {
	    return false;
	}
	$video_data = 'data-tbfullwidthvideo="' . esc_url(themify_https_esc($styling['background_video'])) . '"';

	// Will only be written if they exist, for backwards compatibility with global JS variable tbLocalScript.backgroundVideoLoop

	if (isset($styling['background_video_options'])) {
	    if (is_array($styling['background_video_options'])) {
		$video_data .= in_array('mute', $styling['background_video_options'], true) ? '' : ' data-mutevideo="unmute"';
		$video_data .= in_array('unloop', $styling['background_video_options'], true) ? ' data-unloopvideo="unloop"' : '';
		$video_data .= in_array('playonmobile', $styling['background_video_options'], true) ? ' data-playonmobile="play"' : '';
	    } else {
		$video_data .= ( false !== stripos($styling['background_video_options'], 'mute') ) ? '' : ' data-mutevideo="unmute"';
		$video_data .= ( false !== stripos($styling['background_video_options'], 'unloop') ) ? ' data-unloopvideo="unloop"' : '';
		$video_data .= ( false !== stripos($styling['background_video_options'], 'playonmobile') ) ? ' data-playonmobile="play"' : '';
	    }
	}
	return apply_filters('themify_builder_row_video_background', $video_data, $styling);
    }

    /**
     * Sticky Element props attributes
     * @param array $props
     * @param array $fields_args
     * @param string $mod_name
     * @param string $module_ID
     * @return array
     */
    public static function sticky_element_props($props, $fields_args) {
	if (!empty($fields_args['stick_at_check']) || !empty($fields_args['stick_at_check_t']) || !empty($fields_args['stick_at_check_tl']) || !empty($fields_args['stick_at_check_m'])) {
	    static $is_sticky = null;
	    if ($is_sticky === null) {
		$is_sticky = Themify_Builder_Model::is_sticky_scroll_active();
	    }
	    if ($is_sticky !== false) {
		$_arr = array('d', 'tl', 't', 'm');
		$settings = array();
		foreach ($_arr as $v) {
		    $key = $v === 'd' ? '' : '_' . $v;
		    if (($key === '' && !empty($fields_args['stick_at_check'])) || ($key !== '' && isset($fields_args['stick_at_check' . $key]) && $fields_args['stick_at_check' . $key] !== '')) {
			$settings[$v] = array();
			if ($key !== '' && $fields_args['stick_at_check' . $key] !== '1') {
			    $settings[$v] = 0;
			} else {
			    if (isset($fields_args['stick_at_position' . $key]) && $fields_args['stick_at_position' . $key] === 'bottom') {
				$settings[$v]['stick'] = array();
				$settings[$v]['stick']['p'] = $fields_args['stick_at_position' . $key];
			    }
			    if (!empty($fields_args['stick_at_pos_val' . $key])) {
				if (!isset($settings[$v]['stick'])) {
				    $settings[$v] = array('stick' => array());
				}
				$settings[$v]['stick']['v'] = $fields_args['stick_at_pos_val' . $key];
				if (isset($fields_args['stick_at_pos_val_unit' . $key]) && $fields_args['stick_at_pos_val_unit' . $key] !== 'px') {
				    $settings[$v]['stick']['u'] = $fields_args['stick_at_pos_val_unit' . $key];
				}
			    }

			    if (!empty($fields_args['unstick_when_check' . $key])) {
				$unstick = array();
				if (isset($fields_args['unstick_when_element' . $key]) && $fields_args['unstick_when_element' . $key] !== 'builder_end') {
				    if (isset($fields_args['unstick_when_condition' . $key]) && $fields_args['unstick_when_condition' . $key] !== 'hits') {
					$unstick['r'] = $fields_args['unstick_when_condition' . $key];
				    }
				    $unstick['type'] = $fields_args['unstick_when_element' . $key];
				    if ($unstick['type'] === 'row' && isset($fields_args['unstick_when_el_row_id' . $key]) && $fields_args['unstick_when_el_row_id' . $key] !== 'row') {
					$unstick['el'] = $fields_args['unstick_when_el_row_id' . $key];
				    } elseif ($unstick['type'] === 'module' && !empty($fields_args['unstick_when_el_mod_id' . $key])) {
					$unstick['el'] = $fields_args['unstick_when_el_mod_id' . $key];
				    } else {
					continue;
				    }

				    if (isset($fields_args['unstick_when_pos' . $key]) && $fields_args['unstick_when_pos' . $key] !== 'this') {
					$unstick['cur'] = $fields_args['unstick_when_pos' . $key];
					if (!empty($fields_args['unstick_when_pos_val' . $key])) {
					    $unstick['v'] = $fields_args['unstick_when_pos_val' . $key];
					    if (isset($fields_args['unstick_when_pos_val_unit' . $key]) && $fields_args['unstick_when_pos_val_unit' . $key] !== 'px') {
						$unstick['u'] = $fields_args['unstick_when_pos_val_unit' . $key];
					    }
					}
				    }
				} else {
				    $unstick['type'] = 'builder';
				}
				if (!empty($unstick)) {
				    $settings[$v]['unstick'] = $unstick;
				}
			    }
			}
		    }
		}
		if (!empty($settings)) {
		    unset($_arr);
		    $props['data-sticky-active'] = json_encode($settings);
		    if ($is_sticky !== 'done') {
			$is_sticky = 'done';
			Themify_Enqueue_Assets::addPrefetchJs(THEMIFY_BUILDER_JS_MODULES . 'sticky.js', THEMIFY_VERSION);
		    }
		}
	    }
	}//Add custom attributes html5 data to module container div to show parallax options.
	elseif (Themify_Builder::$frontedit_active === false && (!empty($fields_args['motion_effects']) || !empty($fields_args['custom_parallax_scroll_speed']) )) {
	    static $is_lax = null;
	    if ($is_lax === null) {
		$is_lax = Themify_Builder_Model::is_scroll_effect_active();
	    }
	    if ($is_lax !== false) {
		$has_lax = false; /* validate Lax settings */
		// Check settings from Floating tab to apply them to Lax library
		if (!empty($fields_args['custom_parallax_scroll_speed'])) {
		    $has_lax = true;
		    $props['data-parallax-element-speed'] = $fields_args['custom_parallax_scroll_speed'];

		    $speed = self::map_animation_speed($fields_args['custom_parallax_scroll_speed']);

		    if (!isset($fields_args['custom_parallax_scroll_reverse']) || $fields_args['custom_parallax_scroll_reverse'] === '|') {
			$speed = '-' . $speed;
		    }
		    $props['data-lax-translate-y'] = 'vh 1,0 ' . $speed;
		    if (!empty($fields_args['custom_parallax_scroll_fade']) && $fields_args['custom_parallax_scroll_fade'] !== '|') {
			$props['data-lax-opacity'] = 'vh 1,0 0';
		    }
		}
		if (!isset($fields_args['motion_effects']['t'])) {
		    $props['data-lax-optimize'] = 'true';
		}
		// Add motion effects from Motion tab
		// Vertical
		if (isset($fields_args['motion_effects']['v'], $fields_args['motion_effects']['v']['val']['v_dir']) && $fields_args['motion_effects']['v']['val']['v_dir'] !== '') {
		    $has_lax = true;
		    $v_speed = isset($fields_args['motion_effects']['v']['val']['v_speed']) ? $fields_args['motion_effects']['v']['val']['v_speed'] : 1;
		    $v_speed = self::map_animation_speed($v_speed);
		    $viewport = isset($fields_args['motion_effects']['v']['val']['v_vp']) ? explode(',', $fields_args['motion_effects']['v']['val']['v_vp']) : array(0, 100);
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $props['data-lax-translate-y'] = $fields_args['motion_effects']['v']['val']['v_dir'] === 'up' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $v_speed : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $v_speed;
		}
		// Horizontal
		if (isset($fields_args['motion_effects']['h'], $fields_args['motion_effects']['h']['val']['h_dir']) && $fields_args['motion_effects']['h']['val']['h_dir'] !== '') {
		    $has_lax = true;
		    $h_speed = isset($fields_args['motion_effects']['h']['val']['h_speed']) ? self::map_animation_speed($fields_args['motion_effects']['h']['val']['h_speed']) : 600;
		    $viewport = isset($fields_args['motion_effects']['h']['val']['h_vp']) ? explode(',', $fields_args['motion_effects']['h']['val']['h_vp']) : array(0, 100);
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $props['data-lax-translate-x'] = $fields_args['motion_effects']['h']['val']['h_dir'] === 'toleft' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $h_speed : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $h_speed;
		}
		// Opacity
		if (isset($fields_args['motion_effects']['t'], $fields_args['motion_effects']['t']['val']['t_dir']) && $fields_args['motion_effects']['t']['val']['t_dir'] !== '') {
		    $has_lax = true;
		    $viewport = isset($fields_args['motion_effects']['t']['val']['t_vp']) ? explode(',', $fields_args['motion_effects']['t']['val']['t_vp']) : array(0, 100);
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $center = ( $bottom - ( ( $bottom - $top ) / 2 ) );
		    if ($fields_args['motion_effects']['t']['val']['t_dir'] === 'fadein') {
			$props['data-lax-opacity'] = '(vh*' . $bottom . ') 0,(vh*' . $top . ') 1';
		    } elseif ($fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeout') {
			$props['data-lax-opacity'] = '(vh*' . $bottom . ') 1,(vh*' . $top . ') 0';
		    } elseif ($fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeoutin') {
			$props['data-lax-opacity'] = '(vh*' . $bottom . ') 1,(vh*' . $center . ') 0,(vh*' . $top . ') 1';
		    } elseif ($fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeinout') {
			$props['data-lax-opacity'] = '(vh*' . $bottom . ') 0,(vh*' . $center . ') 1,(vh*' . $top . ') 0';
		    }
		} elseif (!isset($fields_args['animation_effect_delay'])) {
		    unset($props['data-lax-opacity'], $props['data-lax-optimize']);
		}
		// Blur
		if (isset($fields_args['motion_effects']['b'], $fields_args['motion_effects']['b']['val']['b_dir']) && $fields_args['motion_effects']['b']['val']['b_dir'] !== '') {
		    $has_lax = true;
		    $b_level = isset($fields_args['motion_effects']['b']['val']['b_level']) ? self::map_animation_speed($fields_args['motion_effects']['b']['val']['b_level'], 'blur') : 10;
		    $viewport = isset($fields_args['motion_effects']['b']['val']['b_vp']) ? explode(',', $fields_args['motion_effects']['b']['val']['b_vp']) : array(0, 100);
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $props['data-lax-blur'] = $fields_args['motion_effects']['b']['val']['b_dir'] === 'fadein' ? '(vh*' . $bottom . ') ' . $b_level . ',(vh*' . $top . ') 0' : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $b_level;
		}
		// Rotate
		if (isset($fields_args['motion_effects']['r'], $fields_args['motion_effects']['r']['val']['r_dir']) && $fields_args['motion_effects']['r']['val']['r_dir'] !== '') {
		    $has_lax = true;
		    $viewport = isset($fields_args['motion_effects']['r']['val']['r_vp']) ? explode(',', $fields_args['motion_effects']['r']['val']['r_vp']) : array(0, 100);
		    $rotates = isset($fields_args['motion_effects']['r']['val']['r_num']) ? (float) $fields_args['motion_effects']['r']['val']['r_num'] * 360 : 360;
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $props['data-lax-rotate'] = $fields_args['motion_effects']['r']['val']['r_dir'] === 'toleft' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $rotates : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $rotates;
		    if (isset($fields_args['motion_effects']['r']['val']['r_origin'])) {
			$props['data-box-position'] = self::map_transform_origin($fields_args['motion_effects']['r']['val']['r_origin']);
		    }
		}
		// Scale
		if (isset($fields_args['motion_effects']['s'], $fields_args['motion_effects']['s']['val']['s_dir']) && $fields_args['motion_effects']['s']['val']['s_dir'] !== '') {
		    $has_lax = true;
		    $viewport = isset($fields_args['motion_effects']['s']['val']['s_vp']) ? explode(',', $fields_args['motion_effects']['s']['val']['s_vp']) : array(0, 100);
		    $ratio = isset($fields_args['motion_effects']['s']['val']['s_ratio']) ? (float) $fields_args['motion_effects']['s']['val']['s_ratio'] : 3;
		    $bottom = 1 - ( (int) $viewport[0] / 100 );
		    $top = 1 - ( (int) $viewport[1] / 100 );
		    $props['data-lax-scale'] = $fields_args['motion_effects']['s']['val']['s_dir'] === 'up' ? '(vh*' . $bottom . ') 1,(vh*' . $top . ') ' . $ratio : '(vh*' . $bottom . ') 1,(vh*' . $top . ') ' . number_format(1 / $ratio, 3);
		    if (isset($fields_args['motion_effects']['s']['val']['s_origin'])) {
			$props['data-box-position'] = self::map_transform_origin($fields_args['motion_effects']['s']['val']['s_origin']);
		    }
		}
		if ($has_lax === true) {
		    $props['data-lax'] = 'true';
		}
		if ($is_lax !== 'done') {
		    $is_lax = 'done';
		    Themify_Enqueue_Assets::addPrefetchJs(THEMIFY_URI . '/js/modules/lax.js', THEMIFY_VERSION);
		}
	    }
	}
	if (isset($fields_args['custom_css_id'])) {
	    $props['id'] = $fields_args['custom_css_id'];
	}
	return $props;
    }

    /**
     * Map animation speed parameter and returns new speed
     *
     * @param string $val Initial speed value
     * @param string $attr attribute name
     *
     * @return float|int Returns speed of element based on initial value
     */
    private static function map_animation_speed($val, $attr = 'no') {
	switch ($val) {
	    case 10:
		$speed = ($attr === 'blur') ? 20 : 670;
		break;

	    case 9:
		$speed = ($attr === 'blur') ? 18 : 600;
		break;

	    case 8:
		$speed = ($attr === 'blur') ? 16 : 530;
		break;

	    case 7:
		$speed = ($attr === 'blur') ? 14 : 460;
		break;

	    case 6:
		$speed = ($attr === 'blur') ? 12 : 390;
		break;

	    case 4:
		$speed = ($attr === 'blur') ? 8 : 250;
		break;

	    case 3:
		$speed = ($attr === 'blur') ? 6 : 200;
		break;

	    case 2:
		$speed = ($attr === 'blur') ? 4 : 140;
		break;

	    case 1:
		$speed = ($attr === 'blur') ? 2 : 70;
		break;

	    default:
		$speed = ($attr === 'blur') ? 10 : 320;
	}

	return $speed;
    }

    /**
     * Map initial origin value and returns transform origin property
     *
     * @param string $props Initial origin value
     *
     * @return string Returns transform origin value of element based on initial value
     */
    private static function map_transform_origin($props) {
	switch ($props) {
	    case '0,0':
		$output = 'top left';
		break;

	    case '50,0':
		$output = 'top center';
		break;

	    case '100,0':
		$output = 'top right';
		break;

	    case '0,50':
		$output = 'left center';
		break;

	    case '50,50':
		$output = 'center center';
		break;

	    case '100,50':
		$output = 'right center';
		break;

	    case '0,100':
		$output = 'bottom left';
		break;

	    case '50,100':
		$output = 'bottom center';
		break;

	    case '100,100':
		$output = 'bottom right';
		break;

	    default:
		$perc = explode(',', $props);
		$output = $perc[0] . '% ' . $perc[1] . '%';
	}

	return $output;
    }

    /**
     * Computes and returns the HTML a color overlay.
     *
     * @since 2.3.3
     *
     * @param array $styling The row's or column's styling array.
     *
     * @return bool Returns false if $styling doesn't have a color overlay. Otherwise outputs the HTML;
     */
    private static function do_color_overlay($styling) {

	$type = !isset($styling['cover_color-type']) || $styling['cover_color-type'] === 'color' ? 'color' : 'gradient';
	$is_empty = $type === 'color' ? empty($styling['cover_color']) : empty($styling['cover_gradient-gradient']);

	if ($is_empty === true) {
	    $hover_type = !isset($styling['cover_color_hover-type']) || $styling['cover_color_hover-type'] === 'hover_color' ? 'color' : 'gradient';
	    $is_empty_hover = $hover_type === 'color' ? empty($styling['cover_color_hover']) : empty($styling['cover_gradient_hover-gradient']);
	}
	if ($is_empty === false || $is_empty_hover === false) {
	    echo '<div class="builder_row_cover tf_abs"></div>';
	    return true;
	}
	return false;
    }

    protected static function show_frame($styles, $printed = array()) {
	$breakpoints = array('desktop' => '') + themify_get_breakpoints();
	$sides = array('top', 'bottom', 'left', 'right');
	$output = '';
	foreach ($sides as $side) {
	    if (!isset($printed[$side])) {
		foreach ($breakpoints as $bp => $v) {
		    $settings = 'desktop' === $bp ? $styles : (!empty($styles['breakpoint_' . $bp]) ? $styles['breakpoint_' . $bp] : array() );
		    if (!empty($settings) && Themify_Builder_Model::get_frame($settings, $side)) {
			$printed[$side] = true;
			$frame_location = ( isset($settings["{$side}-frame_location"]) && $settings["{$side}-frame_location"] === 'in_front' ) ? $settings["{$side}-frame_location"] : '';
			$cl = $side === 'left' || $side === 'right' ? 'tf_h' : 'tf_w';
			$output .= '<div class="tb_row_frame tb_row_frame_' . $side . ' ' . $frame_location . ' tf_abs tf_hide tf_overflow ' . $cl . '"></div>';
			break;
		    }
		}
	    }
	}
	if (!empty($output)) {
	    Themify_Builder_Model::loadCssModules('fr', THEMIFY_BUILDER_CSS_MODULES . 'frames.css', THEMIFY_VERSION);
	    echo '<div class="tb_row_frame_wrap tf_overflow tf_abs" data-lazy="1">', $output, '</div>';
	}
	return $printed;
    }

    /**
     * Computes and returns the HTML for a background slider.
     *
     * @since 2.3.3
     *
     * @param array  $row_or_col   Row or column definition.
     * @param string $order        Order of row/column (e.g. 0 or 0-1-0-1 for sub columns)
     * @param string $type Accepts 'row', 'col', 'sub-col'
     *
     * @return bool Returns false if $row_or_col doesn't have a bg slider. Otherwise outputs the HTML for the slider.
     */
    public static function do_slider_background($row_or_col, $type = 'row') {
	if (!isset($row_or_col['styling']['background_type']) || 'slider' !== $row_or_col['styling']['background_type'] || empty($row_or_col['styling']['background_slider'])) {
	    return false;
	}
	$images = themify_get_gallery_shortcode($row_or_col['styling']['background_slider']);
	if (!empty($images)) :

	    $size = isset($row_or_col['styling']['background_slider_size']) ? $row_or_col['styling']['background_slider_size'] : false;
	    if (!$size) {
		$size = themify_get_gallery_shortcode_params($row_or_col['styling']['background_slider'], 'size');
		if (!$size) {
		    $size = 'large';
		}
	    }
	    $bgmode = !empty($row_or_col['styling']['background_slider_mode']) ? $row_or_col['styling']['background_slider_mode'] : 'fullcover';
	    $slider_speed = !empty($row_or_col['styling']['background_slider_speed']) ? $row_or_col['styling']['background_slider_speed'] : '2000';
	    ?>
	    <div class="tf_hide <?php echo $type; ?>-slider tb_slider" data-bgmode="<?php echo $bgmode; ?>" data-sliderspeed="<?php echo $slider_speed ?>">
	        <ul class="tf_abs row-slider-slides tf_clearfix">
	    <?php
	    foreach ($images as $i => $img) {
		$img_data = wp_get_attachment_image_src($img->ID, $size);
		?>
			<li data-bg="<?php echo esc_url(themify_https_esc($img_data[0])); ?>" data-bg-alt="<?php echo esc_attr(get_post_meta($img->ID, '_wp_attachment_image_alt', TRUE)); ?>" class="normal">
			    <a href="javascript:;" rel="nofollow" class="row-slider-dot" data-index="<?php echo $i; ?>"><span class="screen-reader-text">&bull;</span></a>
			</li>
		<?php
	    }
	    ?>
	        </ul>
	        <div class="row-slider-nav tf_abs_t tf_w">
	    	<a href="javascript:;" rel="nofollow" class="row-slider-arrow row-slider-prev tf_hidden tf_abs_t"><span class="screen-reader-text">&larr;</span></a>
	    	<a href="javascript:;" rel="nofollow" class="row-slider-arrow row-slider-next tf_hidden tf_abs_t"><span class="screen-reader-text">&rarr;</span></a>
	        </div>
	    </div>
	    <!-- /.row-bgs -->
	    <?php
	    return true;
	endif; // images
	return false;
    }

    public static function background_styling($row, $type, $builder_id) {
	// Background cover color
	if (!empty($row['styling'])) {
	    $hasOverlay = false;
	    if (!self::do_color_overlay($row['styling'])) {
		$breakpoints = themify_get_breakpoints();
		foreach ($breakpoints as $bp => $v) {
		    if (!empty($row['styling']['breakpoint_' . $bp]) && self::do_color_overlay($row['styling']['breakpoint_' . $bp])) {
			$hasOverlay = true;
			break;
		    }
		}
	    } else {
		$hasOverlay = true;
	    }
	    // Background Slider
	    self::do_slider_background($row, $type);
	    $frames = array();
	    $framesCount = 0;
	    if (!empty($row['styling']['global_styles'])) {
		$used_gs = Themify_Global_Styles::get_used_gs($builder_id);
		if (!empty($used_gs)) {
		    $global_styles = explode(' ', $row['styling']['global_styles']);
		    if ($hasOverlay === false) {
			$breakpoints = array('desktop' => '') + themify_get_breakpoints();
		    }
		    foreach ($global_styles as $cl) {
			if (isset($used_gs[$cl])) {
			    if ($hasOverlay === false) {
				foreach ($breakpoints as $bp => $v) {

				    if (($bp === 'desktop' && self::do_color_overlay($used_gs[$cl])) || ($bp !== 'desktop' && !empty($used_gs[$cl]['breakpoint_' . $bp]) && self::do_color_overlay($used_gs[$cl]['breakpoint_' . $bp]))) {
					$hasOverlay = true;
					break;
				    }
				}
			    }
			    if ($framesCount !== 4) {
				$frames = self::show_frame($used_gs[$cl], $frames);
				$framesCount = count($frames);
			    }
			}
			if ($hasOverlay === true && $framesCount === 4) {
			    break;
			}
		    }
		}
	    }
	    if ($framesCount !== 4) {
		self::show_frame($row['styling'], $frames);
	    }
	    if ($hasOverlay === true) {
		Themify_Builder_Model::loadCssModules('cover', THEMIFY_BUILDER_CSS_MODULES . 'cover.css', THEMIFY_VERSION);
	    }
	}
    }

    // Get Height Options plus Auto Height
    protected static function get_height($selector = '', $id = 'ht') {
	return array(
	    'id' => $id,
	    'label' => 'ht',
	    'type' => 'height',
	    'selector' => $selector
	);
    }

    // Get Min Height Option
    protected static function get_min_height($selector = '', $id = 'mi_h') {
	return array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'm_ht',
	    'prop' => 'min-height',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'max' => 3500
		),
		'vh' => '',
		'%' => '',
		'em' => array(
		    'max' => 200
		)
	    )
	);
    }

    // Get Max Height Option
    protected static function get_max_height($selector = '', $id = 'mx_h') {
	return array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'mx_ht',
	    'prop' => 'max-height',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'max' => 3500
		),
		'vh' => '',
		'%' => '',
		'em' => array(
		    'max' => 200
		)
	    )
	);
    }

    // Get Rounded Corners
    protected static function get_border_radius($selector = '', $id = 'b_ra', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'border_radius',
	    'label' => 'bo_r',
	    'wrap_class' => 'border-radius-options',
	    'prop' => 'border-radius',
	    'selector' => $selector,
	    'border_radius' => true
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    // Get Box Shadow
    protected static function get_box_shadow($selector = '', $id = 'b_sh', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'box_shadow',
	    'label' => 'b_s',
	    'prop' => 'box-shadow',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    // Get Text Shadow
    protected static function get_text_shadow($selector = '', $id = 'text-shadow', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'text_shadow',
	    'label' => 't_sh',
	    'prop' => 'text-shadow',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    // Get z-index
    protected static function get_zindex($selector = '', $id = 'zi') {
	return array(
	    'id' => $id,
	    'label' => 'zi',
	    'prop' => 'z-index',
	    'selector' => $selector,
	    'type' => 'range',
	    'min'=>-99999,
	    'help' => __('Module with greater stack order is always in front of an module with a lower stack order', 'themify')
	);
    }

    protected static function get_min_width($selector = '', $id = 'mi_w') {
	return array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'mi_wd',
	    'prop' => 'min-width',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'min'=>0,
		    'max' => 3500
		),
		'%' => '',
		'em' => array(
		    'min'=>0,
		    'max' => 200
		)
	    )
	);
    }

    protected static function get_max_width($selector = '', $id = 'ma_w') {
	return array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'ma_wd',
	    'prop' => 'max-width',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'min'=>0,
		    'max' => 3500
		),
		'%' => '',
		'em' => array(
		    'min'=>0,
		    'max' => 200
		)
	    )
	);
    }

    // Get CSS Position
    protected static function get_css_position($selector = '', $id = 'po', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'position',
	    'label' => 'po',
	    'prop' => 'position',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    // CSS Display
    protected static function get_display($selector = '', $id = 'disp') {
	$va_id = $id . '_va';
	$res = array();
	$res[] = array('id' => $id,
	    'label' => 'disp',
	    'type' => 'select',
	    'prop' => 'display',
	    'selector' => $selector,
	    'binding' => array(
		'empty' => array('hide' => $va_id),
		'block' => array('hide' => $va_id),
		'none' => array('hide' => $va_id),
		'inline-block' => array('show' => $va_id)
	    ),
	    'display' => true
	);
	$res[] = array(
	    'id' => $va_id,
	    'label' => __('Vertical Align', 'themify'),
	    'type' => 'select',
	    'prop' => 'vertical-align',
	    'selector' => $selector,
	    'origID' => $id,
	    'va_display' => true
	);
	return $res;
    }

    // Get z-index
    protected static function get_transform($selector = '', $id = 'tr', $state = '') {
	if ($state !== '') {
	    $id .= '-' . $state;
	}
	$tr = array(
	    'id' => $id,
	    'type' => 'transform',
	    'prop' => 'transform',
	    'class' => 'tb_transform_field',
	    'selector' => $selector
	);
	$pos = array(
	    'id' => $id . '_position',
	    'label' => __('Origin', 'themify'),
	    'type' => 'position_box',
	    'position' => true,
	    'prop' => 'transform-origin',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $tr['ishover'] = $pos['ishover'] = true;
	}
	return array($tr, $pos);
    }

    //add inline editing fields
    public static function add_inline_edit_fields($name, $condition = true, $hasEditor = false, $repeat = false, $index = -1, $echo = true) {
	if (!self::$disable_inline_edit && $condition === true && (Themify_Builder::$frontedit_active === true || (class_exists('Tbp_Utils') && Tbp_Utils::$isActive === true))) {
	    $res = ' data-name="' . $name . '" contenteditable="false"';
	    if ($repeat !== false) {
		$res .= ' data-repeat="' . $repeat . '"';
		if ($index !== -1) {
		    $res .= ' data-index="' . $index . '"';
		}
	    }
	    if ($hasEditor === true) {
		$res .= ' data-hasEditor';
	    }
	    if ($echo === false) {
		return $res;
	    }
	    echo $res;
	}
	return '';
    }

    //will be need later
    public static function add_inline_edit_icon() {

    }

    public function get_clickable_component_settings() {
	return [
	    'type' => 'group',
	    'label' => __('Link', 'themify'),
	    'display' => 'accordion',
	    'options' => [
		[
		    'type' => 'url',
		    'id' => '_link',
		    'label' => __('Clickable Link', 'themify'),
		    'binding' => [
			'empty' => ['hide' => '_link_o'],
			'not_empty' => ['show' => '_link_o'],
		    ]
		],
		[
		    'type' => 'toggle_switch',
		    'id' => '_link_o',
		    'label' => __('Hover Outline', 'themify'),
		],
	    ],
	];
    }

    public static function clickable_component($settings, $attributes) {
	if (!empty($settings['_link'])) {
	    $attributes['data-tb_link'] = esc_url($settings['_link']);
	    if (isset($settings['_link_o']) && $settings['_link_o'] !== 'yes') {
		if (!isset($attributes['class'])) {
		    $attributes['class'] = '';
		}
		$attributes['class'] .= ' tb_link_outline';
	    }
	    themify_enque_style('tf_clickableComponent', THEMIFY_BUILDER_CSS_MODULES . 'clickableComponent.css', null, THEMIFY_VERSION);
	}

	return $attributes;
    }

    protected static function setBgMode($attr, $styling) {
	$breakpoints = array('desktop' => '') + themify_get_breakpoints();
	foreach ($breakpoints as $bp => $v) {
	    $bg = '';
	    if ($bp === 'desktop') {
		if (isset($styling['background_repeat'])) {
		    $bg = $styling['background_repeat'];
		}
	    } elseif (isset($styling['breakpoint_' . $bp]['background_repeat'])) {
		$bg = $styling['breakpoint_' . $bp]['background_repeat'];
	    }
	    if ($bg === 'builder-parallax-scrolling' || $bg === 'builder-zoom-scrolling' || $bg === 'builder-zooming') {
		$bg = explode('-', $bg);
		$attr['data-' . $bg[1] . '-bg'] = $bp;
	    }
	}
	return $attr;
    }
}
