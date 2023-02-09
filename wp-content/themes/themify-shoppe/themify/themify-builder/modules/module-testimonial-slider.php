<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Testimonials
 * Description: Display testimonial custom post type
 */
class TB_Testimonials_Module extends Themify_Builder_Component_Module {

	public function __construct() {
		parent::__construct('testimonial-slider');
	}

        public function get_name(){
            return __('Testimonials', 'themify');
        }

        public function get_assets() {
            return array(
                'css'=>1,
		'async'=>1
            );
        }

	public function get_title( $module ){
		$type = isset( $module['mod_settings']['type_query_testimonial'] ) ? $module['mod_settings']['type_query_testimonial'] : 'category';
		$category = isset( $module['mod_settings']['category_testimonial'] ) ? $module['mod_settings']['category_testimonial'] : '';
		$slug_query = isset( $module['mod_settings']['query_slug_testimonial'] ) ? $module['mod_settings']['query_slug_testimonial'] : '';

		if ( 'category' === $type ) {
			return sprintf( '%s : %s', __( 'Category', 'themify' ), $category );
		} else {
			return sprintf( '%s : %s', __( 'Slugs', 'themify' ), $slug_query );
		}
	}

	public function get_icon(){
	    return 'clipboard';
	}

	public function get_options() {
	    return array(

		array(
		    'id' => 'mod_title_testimonial',
		    'type' => 'title'
		),
		array(
		    'id' => 'tab_content_testimonial',
		    'type' => 'builder',
		    'options' => array(
			array(
			    'id' => 'title_testimonial',
			    'type' => 'text',
			    'label' => __('Testimonial Title', 'themify'),
			    'control' => array(
					'selector' => '.testimonial-title'
			    )
			),
			array(
			    'id' => 'content_testimonial',
			    'type' => 'wp_editor',
				'control' => array(
					'selector' => '.testimonial-entry-content'
			    )
			),
			array(
			    'id' => 'person_picture_testimonial',
			    'type' => 'image',
			    'label' => __('Person Picture', 'themify')
			),
			array(
			    'id' => 'person_name_testimonial',
			    'type' => 'text',
			    'label' => __('Person Name', 'themify'),
			    'control' => array(
					'selector' => '.person-name'
			    )
			),
			array(
			    'id' => 'person_position_testimonial',
			    'type' => 'text',
			    'label' => __('Person Position', 'themify'),
			    'control' => array(
					'selector' => '.person-position'
			    )
			),
			array(
			    'id' => 'company_testimonial',
			    'type' => 'text',
			    'label' => __('Company', 'themify'),
			    'control' => array(
					'selector' => '.person-company'
			    )
			),
			array(
			    'id' => 'company_website_testimonial',
			    'type' => 'text',
			    'label' => __('Company Website', 'themify'),
			),
			array(
			    'type'=>'multi',
			    'label'=>__('Rating Star','themify'),
			    'options'=>array(
				array(
				    'type'=>'icon',
				    'label'=>'icon',
				    'id'=>'ic',
				    'binding' => array(
					'empty' => array(
						'hide' =>'tb_rate_wrap'
					),
					'not_empty' => array(
						'show' => 'tb_rate_wrap'
					)
				    )
				),
				array(
					    'id' => 'count',
					    'type' => 'range',
					    'wrap_class'=>'tb_rate_wrap',
					    'label' => __('Stars Count', 'themify'),
					    'min' => 1,
					    'max' => 20
					),
					array(
					    'id' => 'rating',
					    'type' => 'slider_range',
					    'wrap_class'=>'tb_rate_wrap',
					    'label' => __('Rating', 'themify'),
					    'options' => array(
						'max' => 20,
						'step'=>.1,
						'unit'=>'',
						'inputRange'=>true,
						'range' => false
					    )
					),
			    )
			)
		    )
		),
		array(
		    'id' => 'type_testimonial',
		    'type' => 'radio',
		    'label' => __('Type', 'themify'),
		    'options' => array(
			array('value' => 'slider', 'name' => __('Slider', 'themify')),
			array('value' => 'grid', 'name' => __('Grid', 'themify'))
		    ),
		    'option_js' => true
		),
		array(
		    'id' => 'grid_layout_testimonial',
		    'type' => 'layout',
		    'label' => __('Grid Layout', 'themify'),
		    'mode' => 'sprite',
		    'options' => array(
				array('img' => 'list_post', 'value' => 'list-post', 'label' => __('List Post', 'themify')),
				array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
				array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
				array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
				array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'themify')),
				array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'themify')),
		    ),
			'binding' => array(
				'grid4' => array('show' => 'masonry'),
				'grid3' => array('show' =>'masonry'),
				'grid2' => array('show' => 'masonry'),
				'list-post' => array('hide' =>'masonry')
			),
		    'control' => array(
			'classSelector' => '.themify_builder_testimonial'
		    ),
		    'wrap_class' => 'tb_group_element_grid'
		),
			array(
				'id' => 'masonry',
				'type' => 'toggle_switch',
				'label' => __('Masonry view', 'themify'),
				'options' => array(
					'on' => array('name'=>'enable', 'value' =>'en'),
					'off' => array('name'=>'disable', 'value' =>'dis')
				),
				'wrap_class' => 'tb_group_element_grid'
			),
		array(
		    'id' => 'layout_testimonial',
		    'type' => 'layout',
		    'label' => __('Layout', 'themify'),
		    'mode' => 'sprite',
		    'options' => array(
			array('img' => 'testimonials_image_top', 'value' => 'image-top', 'label' => __('Image Top', 'themify')),
			array('img' => 'testimonials_image_bottom', 'value' => 'image-bottom', 'label' => __('Image Bottom', 'themify')),
			array('img' => 'testimonials_image_bubble', 'value' => 'image-bubble', 'label' => __('Image Bubble', 'themify'))
		    ),
		    'control' => array(
			'classSelector' => '.themify_builder_slider_wrap'
		    )
		),
		array(
		    'id' => 'img_w_slider',
		    'type' => 'number',
		    'label' => __('Image Width', 'themify'),
		    'after' => 'px'
		),
		array(
		    'id' => 'img_h_slider',
		    'type' => 'number',
		    'label' => __('Image Height', 'themify'),
		    'after' => 'px'
		),
		array(
		    'id' => 'slider_option_testimonial',
		    'type' => 'slider',
		    'label' => __('Slider Options', 'themify'),
		    'wrap_class' => 'tb_group_element_slider',
		    'slider_options' => true
		),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_testimonial' ),
	    );
	}

	public function get_live_default(){
		return array(
			'layout_testimonial' => 'image-top',
			'img_h_slider' => 100,
			'img_w_slider' => 100,
			'visible_opt_slider' => 1,
			'auto_scroll_opt_slider' => 'off',
			'tab_content_testimonial' => array(
				array(
					'title_testimonial' => esc_html__( 'Optional Title', 'themify' ),
					'content_testimonial' => esc_html__( 'Testimonial content', 'themify' ),
					'person_name_testimonial' => 'John Smith',
					'person_position_testimonial' => 'CEO',
					'company_testimonial' => 'X-corporation',
					'ic'=>'fas fullstar',
					'count'=>5,
					'rating'=>5
				)
			)
		);
	}



	public function get_styling(){

		$general = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( '', 'background_color', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( '', 'bg_c', 'bg_c', 'background-color', 'h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family(),
							self::get_color_type( array( ' .tb_text_wrap', '.module .testimonial-title', ' .person-name', ' .person-position', ' .person-company', ' .person-company a' ) ),
							self::get_font_size(array(' .post',' .testimonial-title',' .testimonial-entry-content',' .testimonial-author .person-name',' .testimonial-author .person-position',' .testimonial-author .person-company')),
							self::get_line_height(' .post'),
							self::get_letter_spacing(' .post'),
							self::get_text_align(' .post'),
							self::get_text_transform(' .post'),
							self::get_font_style(),
							self::get_text_decoration( '', 'text_decoration_regular' ),
							self::get_text_shadow(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '', 'f_f', 'h' ),
							self::get_color_type( array( ':hover .tb_text_wrap', '.module:hover .testimonial-title', ':hover .person-name', ':hover .person-position', ':hover .person-company', ':hover .person-company a' ), '', 'f_c_t_h', 'f_c_h', 'f_g_c_h' ),
							self::get_font_size(array(' .post',' .testimonial-title',' .testimonial-entry-content',' .testimonial-author .person-name',' .testimonial-author .person-position',' .testimonial-author .person-company'), 'f_s', 'h' ),
							self::get_font_style( '', 'f_st', 'f_w', 'h' ),
							self::get_text_decoration( '', 't_d_r', 'h' ),
							self::get_text_shadow( '', 't_sh', 'h' ),
						)
					)
				) )
			) ),
			// Link
			self::get_expand( 'l', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' a', 'link_color' ),
							self::get_text_decoration( ' a' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' a', 'link_color', null, null, 'hover' ),
							self::get_text_decoration( ' a', 't_d', 'h' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( '', 'p', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin()
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( '', 'm', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '', 'b', 'h' )
						)
					)
				) )
			) ),
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
			// Rounded Corners
			self::get_expand( 'r_c', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius( '', 'r_c', 'h' )
							)
						)
					) )
				)
			),
			// Shadow
			self::get_expand( 'sh', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow( '', 'sh', 'h' )
							)
						)
					) )
				)
			),
			// Display
			self::get_expand('disp', self::get_display())
		);

		$testimonial_title = array(
			// Font
			self::get_seperator( 'f' ),
			self::get_tab( array(
				'n' => array(
					'options' => array(
						self::get_font_family( '.module .testimonial-title', 'font_family_title' ),
						self::get_color( '.module .testimonial-title', 'font_color_title' ),
						self::get_font_size( '.module .testimonial-title', 'font_size_title' ),
						self::get_line_height( '.module .testimonial-title', 'line_height_title' ),
						self::get_letter_spacing( '.module .testimonial-title', 'letter_spacing_title' ),
						self::get_text_transform( '.module .testimonial-title', 'text_transform_title' ),
						self::get_font_style( '.module .testimonial-title', 'font_style_title', 'font_title_bold' ),
						self::get_text_shadow( '.module .testimonial-title', 't_sh_t' ),
					)
				),
				'h' => array(
					'options' => array(
						self::get_font_family( '.module .testimonial-title', 'f_f_t', 'h' ),
						self::get_color( '.module .testimonial-title', 'f_c_t', null, null, 'h' ),
						self::get_font_size( '.module .testimonial-title', 'f_s_t', '', 'h' ),
						self::get_font_style( '.module .testimonial-title', 'f_st_t', 'f_t_b', 'h' ),
						self::get_text_shadow( '.module .testimonial-title', 't_sh_t', 'h' ),
					)
				)
			) )
		);

		$image = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .testimonial-image img', 'i_bg_c', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .testimonial-image img', 'i_bg_c', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .testimonial-image img', 'i_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .testimonial-image img', 'i_p', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .testimonial-image img', 'i_m')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .testimonial-image img', 'i_m', 'h')
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' .testimonial-image img', 'i_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' .testimonial-image img', 'i_b', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .testimonial-image img', 'i_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .testimonial-image img', 'i_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .testimonial-image img', 'i_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .testimonial-image img', 'i_b_sh', 'h')
						)
					)
				))
			))

		);

		$testimonial_content = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .testimonial-content', 'background_color_content', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' .testimonial-content', 'b_c_c', 'bg_c', 'background-color', 'h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( ' .testimonial-content', 'font_family_content' ),
							self::get_color( ' .testimonial-content', 'font_color_content' ),
							self::get_font_size( ' .testimonial-content', 'font_size_content' ),
							self::get_font_style( ' .testimonial-content', 'f_fs_c', 'f_fw_c' ),
							self::get_line_height( ' .testimonial-content', 'line_height_content' ),
							self::get_text_shadow( ' .testimonial-content', 't_sh_c' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' .testimonial-content', 'f_f_c', 'h' ),
							self::get_color(' .testimonial-content', 'f_c_c', null, null, 'h' ),
							self::get_font_size( ' .testimonial-content', 'f_s_c', '', 'h' ),
							self::get_font_style( ' .testimonial-content', 'f_fs_c', 'f_fw_c', 'h' ),
							self::get_text_shadow( ' .testimonial-content', 't_sh_c', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .testimonial-content', 'content_padding' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .testimonial-content', 'c_p', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .testimonial-content', 'content_border' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .testimonial-content', 'c_b', 'h' )
						)
					)
				) )
			) )
		);

		$testimonial_container = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color(' .testimonial-item','b_c_container', 'bg_c', 'background-color' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ':hover .testimonial-item', 'b_c_co', 'bg_c', 'background-color' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .testimonial-item', 'p_container' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .testimonial-item', 'p_c', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .testimonial-item', 'b_container' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .testimonial-item', 'b_co', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .testimonial-item', 'r_c_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .testimonial-item', 'r_c_cn', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .testimonial-item', 'b_sh_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .testimonial-item', 'b_sh_cn', 'h')
						)
					)
				))
			))
		);

		$person_info = array(
			// Font
			self::get_expand( __( 'Name', 'themify' ), array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( '.module .person-name', 'font_family_person_name' ),
							self::get_color( '.module .person-name', 'font_color_person_name' ),
							self::get_font_size( '.module .person-name', 'font_size_person_name' ),
							self::get_line_height( '.module .person-name', 'line_height_person_name' ),
							self::get_text_transform( '.module .person-name', 'text_transform_person_name' ),
							self::get_font_style( '.module .person-name', 'font_style_person_name', 'f_w_p_n' ),
							self::get_text_shadow( '.module .person-name', 't_sh_i' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '.module .person-name', 'f_f_p_n', 'h' ),
							self::get_color( '.module .person-name', 'f_c_p_n', null, null, 'h' ),
							self::get_font_size( '.module .person-name', 'f_s_p_n', '', 'h' ),
							self::get_font_style( '.module .person-name', 'f_st_p_n', 'f_w_p_n', 'h' ),
							self::get_text_shadow( '.module .person-name', 't_sh_i', 'h' ),
						)
					)
				) )
			) ),
			// Font
			self::get_expand( __( 'Position', 'themify' ), array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( '.module .person-position', 'font_family_person_position' ),
							self::get_color( '.module .person-position', 'font_color_person_position' ),
							self::get_font_size( '.module .person-position', 'font_size_person_position' ),
							self::get_line_height( '.module .person-position', 'line_height_person_position' ),
							self::get_text_transform( '.module .person-position', 'text_transform_person_position' ),
							self::get_font_style( '.module .person-position', 'font_style_person_position', 'f_w_p_p' ),
							self::get_text_shadow( '.module .person-position', 't_sh_p_i' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '.module .person-position', 'f_f_p_p', 'h' ),
							self::get_color( '.module .person-position', 'f_c_p_p', null, null, 'h' ),
							self::get_font_size( '.module .person-position', 'f_s_p_p', '', 'h' ),
							self::get_font_style( '.module .person-position', 'f_st_p_p', 'f_w_p_p', 'h' ),
							self::get_text_shadow( '.module .person-position', 't_sh_p_i', 'h' ),
						)
					)
				) )
			) ),
			// Font
			self::get_expand( __( 'Company', 'themify' ), array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family( '.module .person-company', 'font_family_company' ),
							self::get_color( array( '.module .person-company', '.module .person-company a'), 'font_color_company' ),
							self::get_font_size( '.module .person-company', 'font_size_company' ),
							self::get_line_height( '.module .person-company', 'line_height_company' ),
							self::get_text_transform( '.module .person-company', 'text_transform_company' ),
							self::get_font_style( '.module .person-company', 'font_style_company', 'f_w_c' ),
							self::get_text_shadow( '.module .person-company', 't_sh_p_c' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '.module .person-company', 'f_f_c', 'h' ),
							self::get_color( array( '.module .person-company', '.module .person-company a'), 'f_c_c', null, null, 'h' ),
							self::get_font_size( '.module .person-company', 'f_s_c', '', 'h' ),
							self::get_font_style( '.module .person-company', 'f_st_c', 'f_w_c', 'h' ),
							self::get_text_shadow( '.module .person-company', 't_sh_p_c', 'h' ),
						)
					)
				) )
			) )
		);

		$controls = array(
			// Arrows
			self::get_expand( __( 'Arrows', 'themify' ), array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( array( ' .carousel-prev', ' .carousel-next' ), 'background_color_arrows_controls', 'bg_c', 'background-color' ),
							self::get_color( array( ' .carousel-prev::before', ' .carousel-next::before' ), 'font_color_arrows_controls' ),
							self::get_width( array( ' .carousel-prev', ' .carousel-next' ), 'w_arr_ctrl'),
							self::get_height( array( ' .carousel-prev', ' .carousel-next' ), 'h_arr_ctrl' ),
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( array( ' .carousel-prev:hover', ' .carousel-next:hover' ), 'background_color_hover_arrows_controls', 'bg_c', 'background-color' ),
							self::get_color( array( ' .carousel-prev:hover::before', ' .carousel-next:hover::before' ), 'font_color_arrows_controls_hover' )
						)
					)
				) )
			) ),
			// Pager
			self::get_expand( __( 'Pager', 'themify' ), array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' .carousel-pager a', 'font_color_pager_controls' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( array( ' .carousel-pager a:hover', ' .carousel-pager a.selected' ), 'font_color_hover_pager_controls' )
						)
					)
				) )
			) )
		);
		$rating_star = array(
			//Gaps
			self::get_expand('m', array(
				self::get_margin(' .tb_rating_wrap .tf_fa ', 'r_m')
			)),
			// Star Base Color
			self::get_expand('Star Base Color', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .tb_rating_wrap', 'st_c')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .tb_rating_wrap', 'st_c',null, null, 'hover')
				)
				)
			))
			)),
			// Star Highlight Color
			self::get_expand('Star Highlight Color', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(array(' .tb_rating_fill'), 'st_h_c')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(array(' .tb_rating_fill'), 'st_h_c',null, null, 'hover')
				)
				)
			))
			)),
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
				'co' => array(
					'label' => __( 'Container', 'themify' ),
					'options' => $testimonial_container
				),
				't' => array(
					'label' => __( 'Title', 'themify' ),
					'options' => $testimonial_title
				),
				'i' => array(
					'label' => __('Image', 'themify'),
					'options' => $image
				),
				'c' => array(
					'label' => __( 'Content', 'themify' ),
					'options' => $testimonial_content
				),
				'p' => array(
					'label' => __( 'Person Info', 'themify' ),
					'options' => $person_info
				),
				'a' => array(
					'label' => __( 'Slider Controls', 'themify' ),
					'options' => $controls
				),
				'r_s' => array(
					'label' => __('Rating Star', 'themify'),
					'options' => $rating_star
				),
			)
		);

	}

	protected function _visual_template() {
	    $module_args = self::get_module_args('mod_title_slider');
	    ?>
	    <#
		const arr=data.tab_content_testimonial || [],
		    isSlider=!data.type_testimonial || data.type_testimonial=='slider',
		    sliderCl=isSlider?' tf_swiper-slide':'';
		    let margin='',
			arrow_vertical='',
			fullwidth_image='';
		    if(isSlider){
			if (data.left_margin_slider>0) {
			    margin+='margin-left:'+data.left_margin_slider+'px;';
			}
			if (data.right_margin_slider>0) {
			    margin+='margin-right:'+data.right_margin_slider+'px;';
			}
			if(margin!==''){
			    margin=' style:"'+margin+'"';
			}
			if(data.show_arrow_buttons_vertical=='vertical' && (!data.show_arrow_slider || data.show_arrow_slider=='yes')){
			    arrow_vertical=' themify_builder_slider_vertical';
			}
			if(data.img_fullwidth_slider=='fullwidth'){
			    fullwidth_image=' themify_builder_slider_vertical';
			}
		    }
	    #>
	    <div class="module module-<?php echo $this->slug; ?><# isSlider && print(' themify_builder_slider_wrap') #> {{data.layout_slider}}{{arrow_vertical}}{{fullwidth_image}} {{ data.css_slider }} tf_clearfix">
		<# if ( data.mod_title_slider ) { #>
		    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_slider }}}<?php echo $module_args['after_title']; ?>
		<# }
		if(!isSlider){ #>
		    <div class="themify_builder_testimonial loops-wrapper builder-posts-wrap<# data.masonry=='enable' && print(' masonry') #> {{data.grid_layout_testimonial}} tf_rel tf_clear">
		<# }
		else{
		    const opt=Object.assign({
			open_link_new_tab_slider:'no',
			auto_scroll_opt_slider:'off',
			effect_slider:'scroll',
			pause_on_hover_slider:'resume',
			play_pause_control:'no',
			wrap_slider:'yes',
			show_nav_slider:'yes',
			show_arrow_slider:'yes',
			unlink_feat_img_slider:'no',
			unlink_post_title_slider:'no',
			height_slider: 'variable'
		    },data),
		    speed = opt.speed_opt_slider == 'slow' ? 4 : (opt.speed_opt_slider == 'fast' ? .5 : 1);

		#>
		   <div class="themify_builder_slider tf_carousel tf_swiper-container tf_rel tf_overflow"
			data-tbreakpoints="{{api.Utils.getBPWidth('tablet_landscape')}}"
			data-mbreakpoints="{{api.Utils.getBPWidth('mobile')}}"
			data-visible="<# print(opt.horizontal== 'yes' ? 4 : opt.visible_opt_slider)#>"
			data-tab-visible="{{opt.tab_visible_opt_slider}}"
			data-mob-visible="{{opt.mob_visible_opt_slider}}"
			data-scroll="{{opt.scroll_opt_slider}}"
			<# arrow_vertical && print(' data-nav_out="1"')#>
			<# if(opt.auto_scroll_opt_slider!='off'){#>
				data-auto="{{opt.auto_scroll_opt_slider*1000}}"
				data-controller="<# print( opt.play_pause_control== 'yes'?1:0)#>"
				data-pause_hover="<# print( opt.pause_on_hover_slider== 'resume'?1:0)#>"
			<#}#>
			data-speed="{{speed}}"
			data-wrapvar="<# print(opt.wrap_slider!= 'no'?1:0) #>"
			data-slider_nav="<# print(opt.show_arrow_slider== 'yes'?1:0)#>"
			data-pager="<# print(opt.show_nav_slider== 'yes'?1:0)#>"
			data-effect="{{opt.effect_slider}}"
			data-height="<# print(opt.horizontal== 'yes' ? 'variable' : opt.height_slider) #>"
			data-horizontal="<# print(opt.horizontal== 'yes'?1:0)#>"
			data-css_url="{{ThemifyBuilderModuleJs.cssUrl}}sliders/carousel,{{ThemifyBuilderModuleJs.cssUrl}}sliders/slider"
			<# opt.touch_swipe && print(' data-touch_swipe="'+opt.touch_swipe+'"')#>
		    >
			<div class="tf_swiper-wrapper tf_lazy tf_rel tf_w tf_h tf_textc">
		<# }

		for(var i=0,len=arr.length;i<len;++i){
			    let item=arr[i],
			    image = '';
			    if (item.person_picture_testimonial) {
				image='<img data-name="person_picture_testimonial" data-repeat="tab_content_testimonial" data-index="'+i+'" data-w="img_w_slider" data-h="img_h_slider" src="'+item.person_picture_testimonial+'"';
				if(item.title_testimonial){
				    image+=' title="'+item.title_testimonial+'"';
				}
				if(data.img_w_slider>0){
				    image+=' width="'+data.img_w_slider+'"';
				}
				if(data.img_h_slider>0){
				    image+=' height="'+data.img_h_slider+'"';
				}
				image+='/>';
			    }
			#>
			<div class="post{{sliderCl}}">
			    <div class="testimonial-item"{{margin}}>
				<div class="testimonial-content">
				    <# if (item.title_testimonial){#>
					<h3 class="testimonial-title" data-name="title_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}">{{{item.title_testimonial}}}</h3>
				    <#}
				    if(item.ic){#>
					<div class="tb_rating_wrap">
					    <# let count =parseInt(item.count) || 5,
						    rating = parseFloat(parseFloat(item.rating || count).toFixed(2)),
						    defaultIcon=api.Helper.getIcon(item.ic),
						    fillIcon=defaultIcon,
						    halfIcon=defaultIcon;

						defaultIcon=defaultIcon.outerHTML;

						fillIcon.classList.add('tb_rating_fill');
						fillIcon=fillIcon.outerHTML;

						for(var j=0;j<count;++j){
						    if((rating-j)>=1){
							print(fillIcon)
						    }
						    else if(rating>j){
							let gid='tb_'+data.cid+i,
							    decimal =(rating-parseInt(rating)).toFixed(2),
							    cl=halfIcon.classList;
							    cl.add('tb_rating_half');
							    cl.remove('tb_rating_fill');
							    halfIcon.style.setProperty('--tb_rating_half','url(#'+gid+')');
							    halfIcon=halfIcon.outerHTML;
							#>
							    <svg width="0" height="0" aria-hidden="true" style="visibility:hidden;position:absolute">
								<defs>
								    <linearGradient id="{{gid}}">
									<stop offset="{{decimal*100}}%" class="tb_rating_fill"/>
									<stop offset="{{decimal*100}}%" stop-color="currentColor"/>
								    </linearGradient>
								</defs>
							    </svg>
							<#
							print(halfIcon)
						    }
						    else{
							print(defaultIcon)
						    }
						}#>
					</div>
				    <#}
				    if (item.content_testimonial){#>
					<div class="testimonial-entry-content" data-name="content_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}" data-haseditor>
					    {{{item.content_testimonial}}}
					</div>
				    <# }
				    if (image){#>
					<figure class="testimonial-image tf_rel">
					    <#print(image)#>
					</figure>
				    <# }
				    if (item.person_name_testimonial){ #>
					<div class="testimonial-author">
					    <div class="person-name" data-name="person_name_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}">{{item.person_name_testimonial}}</div>
					    <# if (item.person_position_testimonial){ #>
						<span class="person-position" data-name="person_position_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}">{{item.person_position_testimonial}}</span>
					    <# }
					     if (item.company_testimonial){#>
						<div class="person-company" data-name="company_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}">
						    <# if (item.company_website_testimonial){ #>
							<a href="{{item.company_website_testimonial}}" data-name="company_testimonial" contenteditable="false" data-repeat="tab_content_testimonial" data-index="{{i}}">{{item.company_testimonial}}</a>
						    <#}else{
							{{item.company_testimonial}}
						    }#>
						</div>
					    <#}#>
					</div>
				    <# } #>
				</div>
			    </div>
			</div>
		    <#}
		    if(isSlider){ #>
			</div>
		    <#}#>
		</div>
	    </div>
	    <?php
	}
}

new TB_Testimonials_Module();

