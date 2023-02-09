<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: WooCommerce Product Categories
 */
class TB_Product_Categories_Module extends Themify_Builder_Component_Module {
	public function __construct() {
            if(method_exists('Themify_Builder_Model', 'add_module')){
                parent::__construct('product-categories');
            }
            else{//backward
                 parent::__construct(array(
                    'name' =>$this->get_name(),
                    'slug' => 'product-categories',
                    'category' =>$this->get_group()
                ));
            }
	}
        
        public function get_name(){
            return  __('Product Categories', 'builder-wc');
        }
        
        public function get_icon(){
	    return 'shopping-cart';
	}
        
	public function get_group() {
            return array('addon');
        }
        
	public function get_assets() {
		return array(
			'css' => Builder_Woocommerce::$url . 'assets/'.$this->slug,
			'ver' => Builder_Woocommerce::$version
		);
	}
        
	public function get_options() {
		return array(
			array(
				'id' => 'mod_title',
				'type' => 'title'
			),
			array(
				'id' => 'columns',
				'type' => 'layout',
				'mode'=>'sprite',
				'label' => __('Layout', 'builder-wc'),
				'options' => array(
					array('img' => 'list_post', 'value' => 1, 'label' => __('1 Column', 'builder-wc')),
					array('img' => 'grid2', 'value' => 2, 'label' => __('2 Columns', 'builder-wc')),
					array('img' => 'grid3', 'value' => 3, 'label' => __('3 Columns', 'builder-wc')),
					array('img' => 'grid4', 'value' => 4, 'label' => __('4 Columns', 'builder-wc')),
					array('img' => 'grid5', 'value' => 5, 'label' => __('5 Columns', 'builder-wc')),
					array('img' => 'grid6', 'value' => 6, 'label' => __('6 Columns', 'builder-wc'))
				),
				'control'=>array(
				//	'classSelector'=>'.loops-wrapper.products'
			    )
			),
			array(
				'id' => 'child_of',
				'type' => 'product_categories',
				'label' => __('Categories', 'builder-wc'),
				'options'=>array(
				    array(
					'0'=>__( 'All Categories', 'builder-wc' ),
				    ),
				    array(
					'label'=>__( 'Only Top Level', 'builder-wc' ),
					'options'=>array(
					    'top-level'=>__( 'Only Top Level Categories', 'builder-wc' ),
					)
				    ),
				    array(
					'label'=>__( 'Category', 'builder-wc' ),
					'options'=>array(
					    
					)
				    )
				    
				)
			),
			array(
				'id' => 'exclude',
				'type' => 'text',
				'label' => __('Exclude Categories', 'builder-wc'),
				'class' => 'large',
				'help' => __('Comma-separated list of product category IDs to exclude.', 'builder-wc'),
			),
			array(
				'id' => 'orderby',
				'type' => 'select',
				'label' => __('Order By', 'builder-wc'),
				'options' => array(
					'name' => __('Name', 'builder-wc'),
					'id' => __('ID', 'builder-wc'),
					'count' => __('Product Count', 'builder-wc'),
					'menu_order' => __('Menu Order', 'builder-wc'),
				)
			),
			array(
				'id' => 'order',
				'type' => 'select',
				'label' => __('Order', 'builder-wc'),
				'help' => __('Descending = show newer posts first', 'builder-wc'),
				'order' =>true
			),
			array(
				'id' => 'number',
				'type' => 'number',
				'label' => __('Posts Per Page', 'builder-wc'),
				'help' => __('The maximum number of terms to show. Leave empty to show all.', 'builder-wc'),
			),
            array(
                'id' => 'image_w',
                'type' => 'number',
                'label' => __('Image Width', 'builder-wc')
            ),
            array(
                'id' => 'image_h',
                'type' => 'number',
                'label' => __('Image Height', 'builder-wc')
            ),
			array(
				'id' => 'hide_empty',
				'type' => 'toggle_switch',
				'label' => __('Empty Categories', 'builder-wc')
			),
			array(
				'id' => 'pad_counts',
				'type' => 'toggle_switch',
				'label' => __('Product Counts', 'builder-wc'),
				'options' => array(
				    'on' => array('name'=>'yes', 'value' =>'s'),
				    'off' => array('name'=>'no', 'value' =>'hi')
				),
			),
			array(
				'id' => 'cat_desc',
				'type' => 'toggle_switch',
				'label' => __('Category Description', 'builder-wc'),
				'options' => array(
				    'on' => array('name'=>'yes', 'value' =>'s'),
				    'off' => array('name'=>'no', 'value' =>'hi')
				),
			),
			array(
				'id' => 'display',
				'type' => 'radio',
				'label' => __('Display Inside Category', 'builder-wc'),
				'options' => array(
				    array('value'=>'products','name'=>__('Latest Products', 'builder-wc')),
				    array('value'=>'subcategories','name'=>__('Subcategories', 'builder-wc')),
				    array('value'=>'none','name'=>__('None', 'builder-wc'))
				),
				'wrap_class' => 'tb_compact_radios',
				'option_js' => true,
			),
			array(
				'id' => 'latest_products',
				'type' => 'select',
				'label' => __('Latest Products', 'builder-wc'),
				'options' => array(
					'1' => 1,
					'2' => 2,
					'3' => 3,
					'4' => 4,
					'5' => 5,
					'6' => 6,
					'7' => 7,
					'8' => 8,
					'9' => 9,
					'10' => 10
				),
				'wrap_class' => 'tb_group_element_products',
			),
			array(
				'id' => 'subcategories_number',
				'type' => 'number',
				'label' => __('Subcategories Limit', 'builder-wc'),
				'help' => __('The maximum number of subcategories to show. Leave empty to show all.', 'builder-wc'),
				'wrap_class' => 'tb_group_element_subcategories',
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_products' ),
		);
	}

	public function get_live_default() {
		return array(
			'latest_products' => 3,
			'hide_empty'=>'yes',
			'pad_counts'=>'yes',
			'cat_desc'=>'no',
			'columns' => '4'
		);
	}

	public function get_animation() {
		return array();
	}
        

	public function get_styling() {
		$general = array(
		    //bacground
		    self::get_expand('bg', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_color('', 'background_color','bg_c','background-color')
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
					self::get_color(' .products .product a','font_color'),
					self::get_font_size(),
					self::get_line_height(),
					self::get_text_align(' .products .product'),
					self::get_text_transform(' .products .product h3', 'text_transform_title'),
					self::get_font_style('', 'font_style_title'),
					self::get_text_shadow(),
				)
			    ),
			    'h' => array(
				'options' => array(
					self::get_font_family('','f_f','h'),
					self::get_color(' .products .product a','f_c',null,null,'h'),
					self::get_font_size('','f_s','','h'),
					self::get_line_height('','l_h','h'),
					self::get_text_align(' .products .product','t_a','h'),
					self::get_text_transform(' .products .product h3', 't_t_t','h'),
					self::get_font_style('', 'f_st','f_w','h'),
					self::get_text_shadow('','t_sh','h'),
				)
			    )
			))
		    )),
		    self::get_expand('l', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
					self::get_color(' a h3','link_color'),
					self::get_text_decoration(' a h3')
				)
			    ),
			    'h' => array(
				'options' => array(
					self::get_color(' a h3','link_color',null,null,'hover'),
					self::get_text_decoration(' a h3','t_a','h')
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
				    self::get_margin('','m','h')
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
				    self::get_border('','b','h')
				)
			    )
			))
		    )),
			// Width
			self::get_expand('w', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_width('', 'w')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width('', 'w', 'h')
						)
					)
				))
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
		$category_container = array(
			//bacground
		    self::get_expand('bg', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_color('.module .product-category', 'b_c_c_cn','bg_c','background-color')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_color('.module .product-category', 'b_c_c_cn','bg_c','background-color','h')
				)
			    )
			))
		    )),
			// Font
			self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family('.module .product-category', 'f_f_c_cn'),
					self::get_color('.module .product-category', 'f_c_c_cn'),
					self::get_font_size('.module .product-category', 'f_s_c_cn'),
					self::get_line_height('.module .product-category', 'l_h_c_cn'),
					self::get_letter_spacing('.module .product-category', 'l_s_c_cn'),
					self::get_text_align('.module .product-category', 't_a_c_cn'),
					self::get_text_transform('.module .product-category', 't_t_c_cn'),
					self::get_font_style('.module .product-category', 'f_sy_c_cn'),
					self::get_text_decoration('.module .product-category', 't_d_r_c_cn'),
					self::get_text_shadow('.module .product-category','t_sh_c_cn'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family('.module .product-category', 'f_f_c_cn', 'h'),
					self::get_color('.module .product-category','f_c_c_cn', null,null, 'h'),
					self::get_font_size('.module .product-category', 'f_s_c_cn', '', 'h'),
					self::get_line_height('.module .product-category', 'l_h_c_cn', 'h'),
					self::get_letter_spacing('.module .product-category', 'l_s_c_cn', 'h'),
					self::get_text_align('.module .product-category', 't_a_c_cn', 'h'),
					self::get_text_transform('.module .product-category', 't_t_c_cn', 'h'),
					self::get_font_style('.module .product-category', 'f_sy_c_cn', 'h'),
					self::get_text_decoration('.module .product-category', 't_d_r_c_cn', 'h'),
					self::get_text_shadow('.module .product-category','t_sh_c_cn', 'h'),
				)
				)
			))
			)),
			// Link
			self::get_expand('l', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.module .product-category a', 'l_c_cn'),
					self::get_text_decoration('.module .product-category a', 't_d_cn')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.module .product-category a', 'l_c_cn', 'h'),
					self::get_text_decoration('.module .product-category a', 't_d_cn', 'h')
				)
				)
			))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding('.module .product-category', 'p_cn')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding('.module .product-category', 'p_cn', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin('.module .product-category', 'm_cn')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin('.module .product-category', 'm_cn', 'h')
				)
				)
			))
			)),
			// Border
			self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border('.module .product-category', 'b_cn')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border('.module .product-category', 'b_cn', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius('.module .product-category', 'c_cn_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('.module .product-category', 'c_cn_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow('.module .product-category', 'c_cn_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('.module .product-category', 'c_cn_b_sh', 'h')
						)
					)
				))
			))
		);
		$category_image = array(
			// Background
			self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.module .product-category img', 'b_c_c_i', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.module .product-category img', 'b_c_c_i', 'bg_c', 'background-color', 'h')
				)
				)
			))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding('.module .product-category img', 'p_c_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding('.module .product-category img', 'p_c_i', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin('.module .product-category img', 'm_c_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin('.module .product-category img', 'm_c_i', 'h')
				)
				)
			))
			)),
			// Border
			self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border('.module .product-category img', 'b_c_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border('.module .product-category img', 'b_c_i', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius('.module .product-category img', 'c_i_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('.module .product-category img', 'c_i_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow('.module .product-category img', 'c_i_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('.module .product-category img', 'c_i_b_sh', 'h')
						)
					)
				))
			))
		);
		$category_title = array(
			// Font
			self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family('.module .product-category h3', 'f_f_c_t'),
					self::get_color('.module .product-category h3', 'f_c_c_t'),
					self::get_font_size('.module .product-category h3', 'f_s_c_t'),
					self::get_line_height('.module .product-category h3', 'l_h_c_t'),
					self::get_letter_spacing('.module .product-category h3', 'l_s_c_t'),
					self::get_text_transform('.module .product-category h3', 't_t_c_t'),
					self::get_font_style('.module .product-category h3', 'f_sy_c_t', 'f_w_c_t'),
					self::get_text_decoration('.module .product-category h3', 't_d_r_c_t'),
					self::get_text_shadow('.module .product-category h3', 't_sh_c_t'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family('.module .product-category h3', 'f_f_c_t', 'h'),
					self::get_color('.module .product-category h3', 'f_c_c_t', null, null, 'h'),
					self::get_font_size('.module .product-category h3', 'f_s_c_t', '', 'h'),
					self::get_line_height('.module .product-category h3', 'l_h_c_t', 'h'),
					self::get_letter_spacing('.module .product-category h3', 'l_s_c_t', 'h'),
					self::get_text_transform('.module .product-category h3', 't_t_c_t', 'h'),
					self::get_font_style('.module .product-category h3', 'f_sy_c_t', 'f_w_c_t', 'h'),
					self::get_text_decoration('.module .product-category h3', 't_d_r_c_t', 'h'),
					self::get_text_shadow('.module .product-category h3', 't_sh_c_t','h'),
				)
				)
			))
			)),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding('.module .product-category h3', 'p_c_t')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding('.module .product-category h3', 'p_c_t', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin('.module .product-category h3', 'm_c_t'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin('.module .product-category h3', 'm_c_t', 'h'),
				)
				)
			))
			)),
			// Border
			self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border('.module .product-category h3', 'b_c_t')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border('.module .product-category h3', 'b_c_t', 'h')
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
				'c' => array(
					'label' => __('Category Container', 'themify'),
					'options' => $category_container
				),
				'ci' => array(
					'label' => __('Category Image', 'themify'),
					'options' => $category_image
				),
				't' => array(
					'label' => __('Category Title', 'themify'),
					'options' => $category_title
				)
			)
		);

	}

    public static function set_category_image_size($size){
        $sizes = wp_get_additional_image_sizes();
        if(isset($sizes['themify_wc_category_thumbnail'])){
            $size = array_values($sizes['themify_wc_category_thumbnail']);
        }
        return $size;
    }

    public static function change_category_image_size($image, $attachment_id, $size, $icon){
        // Make sure it's called from WC category thumbnail
        if($size===apply_filters( 'subcategory_archive_thumbnail_size', 'woocommerce_thumbnail' )){
            $sizes = wp_get_additional_image_sizes();
            if(isset($sizes['themify_wc_category_thumbnail'])){
                $image[0]=themify_get_image(array('urlonly'=>true,'src'=>$image[0],'w'=>$sizes['themify_wc_category_thumbnail']['width'],'h'=>$sizes['themify_wc_category_thumbnail']['height']));
            }
        }
        return $image;
    }
}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Product_Categories_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Product_Categories_Module');
}
