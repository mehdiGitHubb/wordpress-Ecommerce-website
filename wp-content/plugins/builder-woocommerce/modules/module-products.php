<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: WooCommerce
 */
class TB_Products_Module extends Themify_Builder_Component_Module {
	
	private static $Actions=array();
	private static $args=array();
	private static $isLoop=false;
	protected static $post_filter;

	public function __construct() {
            if(method_exists('Themify_Builder_Model', 'add_module')){
                parent::__construct('products');
            }
            else{//backward
                 parent::__construct(array(
                    'name' =>$this->get_name(),
                    'slug' => 'products',
                    'category' =>$this->get_group()
                ));
            }
	}
        
        public function get_name(){
            return  __('Woo Products', 'builder-wc');
        }
        
        public function get_icon(){
	    return 'shopping-cart';
	}
        
	public function get_group() {
            return array('addon');
        }

	public function get_assets() {
		$arr= array(
			'css' => Builder_Woocommerce::$url . 'assets/'.$this->slug,
			'js' => Builder_Woocommerce::$url . 'assets/scripts',
			'ver' => Builder_Woocommerce::$version
		);
		if(is_rtl()){
			$arr['css']=array(
				$this->slug=>$arr['css'],
				$this->slug.'_rtl'=>Builder_Woocommerce::$url . 'assets/modules/rtl'
			);
		}
		return $arr;
	}
        
	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_products',
				'type' => 'title'
			),
			array(
				'id' => 'query_products',
				'type' => 'radio',
				'label' => __('Type', 'builder-wc'),
				'options' => array(
				    array('value'=>'all','name'=>__('All Products', 'builder-wc')),
				    array('value'=>'featured','name'=>__('Featured Products', 'builder-wc')),
				    array('value'=>'onsale','name'=>__('On Sale', 'builder-wc')),
				    array('value'=>'toprated','name'=>__('Top Rated', 'builder-wc'))
				),
				'wrap_class' => 'tb_compact_radios',
			),
			array(
				'type' => 'products_query',
			),
			array(
				'id' => 'hide_child_products',
				'type' => 'toggle_switch',
				'label' => __('Parent Category Products Only', 'builder-wc'),
				'options' =>'simple',
			),
			array(
				'id' => 'hide_free_products',
				'type' => 'toggle_switch',
				'label' => __('Free Products', 'builder-wc')
			),
			array(
 				'id' => 'hide_outofstock_products',
				'type' => 'toggle_switch',
 				'label' => __('Out of Stock Products', 'builder-wc')
 			),
			array(
 				'id' => 'hidden_products',
				'type' => 'toggle_switch',
 				'label' => __('Hidden Products', 'builder-wc'),
				'default' => '',
				'options' => array(
					'on' => array( 'name' => 'on', 'value' => __( 'Show', 'builder-wc' ) ),
					'off' => array( 'name' => '', 'value' => __( 'Hide', 'builder-wc' ) ),
				),
 			),
			array(
				'id' => 'post_per_page_products',
				'type' => 'number',
				'label' => __('Products Per Page', 'builder-wc'),
				'help' => __('Number of posts to show.', 'builder-wc')
			),
			array(
				'id' => 'offset_products',
				'type' => 'number',
				'label' => __('Offset', 'builder-wc'),
				'help' => __('Number of post to displace or pass over.', 'builder-wc')
			),
			array(
				'id' => 'orderby_products',
				'type' => 'select',
				'label' => __('Order By', 'builder-wc'),
				'options' => array(
					'date' => __('Date', 'builder-wc'),
					'price' => __('Price', 'builder-wc'),
					'sales' => __('Sales', 'builder-wc'),
					'id' => __('ID', 'builder-wc'),
					'title' => __('Title', 'builder-wc'),
					'rand' => __('Random', 'builder-wc'),
					'menu_order' => __('Custom', 'builder-wc')
				)
			),
			array(
				'id' => 'order_products',
				'type' => 'select',
				'label' => __('Order', 'builder-wc'),
				'help' => __('Sort products in ascending or descending order.', 'builder-wc'),
				'order' =>true
			),
			array(
				'id' => 'template_products',
				'type' => 'radio',
				'label' => __('Display as', 'builder-wc'),
				'options' => apply_filters( 'builder_products_templates', array(
				    array('value'=>'list','name'=>__('List', 'builder-wc')),
				    array('value'=>'slider','name'=>__('Slider', 'builder-wc'))
				) ),
				'binding' => array(
					'list' => array(
						'show' => 'post_filter'
					),
					'slider' => array(
						'hide' => 'post_filter'
					)
				),
				'option_js' => true
			),
			array(
			    'id' => 'layout_products',
			    'type' => 'layout',
			    'mode'=>'sprite',
			    'wrap_class' => 'tb_group_element_list',
			    'label' => __('Layout', 'builder-wc'),
			    'options' => array(
				    array('img' => 'list_post', 'value' => 'list-post', 'label' => __('List Post', 'builder-wc')),
				    array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'builder-wc')),
				    array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'builder-wc')),
				    array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'builder-wc')),
					array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'builder-wc')),
					array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'builder-wc')),
				    array('img' => 'list_thumb_image', 'value' => 'list-thumb-image', 'label' => __('List Thumb Image', 'builder-wc')),
				    array('img' => 'grid2_thumb', 'value' => 'grid2-thumb', 'label' => __('Grid 2 Thumb', 'builder-wc'))
			    ),
			    'control'=>array(
					'classSelector'=>'.wc-products'
			    )
			),
			array(
				'type' => 'group',
				'options' => array(
					array(
						'id' => 'layout_slider',
						'type' => 'layout',
                                                'mode'=>'sprite',
						'label' => __('Slider Layout', 'builder-wc'),
						'options' => array(
							array('img' => 'slider_default', 'value' => 'slider-default', 'label' => __('Slider Default', 'builder-wc')),
							array('img' => 'slider_image_top', 'value' => 'slider-overlay', 'label' => __('Slider Overlay', 'builder-wc')),
							array('img' => 'slider_caption_overlay', 'value' => 'slider-caption-overlay', 'label' => __('Slider Caption Overlay', 'builder-wc')),
							array('img' => 'slider_agency', 'value' => 'slider-agency', 'label' => __('Agency', 'builder-wc'))
						),
						'control'=>array(
						    'classSelector'=>' .wc-products'
						)
					),
					array(
						'id' => 'slider_option_slider',
						'type' => 'slider',
						'label' => __('Slider Options', 'builder-wc'),
						'slider_options' => true,
					)
				),
				'wrap_class' => 'tb_group_element_slider'
			),
			array(
				'id' => 'description_products',
				'type' => 'select',
				'label' => __('Product Description', 'builder-wc'),
				'options' => array(
					'none' => __('None', 'builder-wc'),
					'short' => __('Short Description', 'builder-wc'),
					'full' => __('Full Description', 'builder-wc'),
				)
			),
			array(
				'id' => 'hide_feat_img_products',
				'type' => 'toggle_switch',
				'label' => __('Product Image', 'builder-wc'),
				'binding' => array(
					'checked' => array(
						'show' => array('image_size_products', 'img_width_products','img_height_products','unlink_feat_img_products')
					),
					'not_checked' => array(
						'hide' => array('image_size_products', 'img_width_products','img_height_products','unlink_feat_img_products')
					)
				)
			),
			array(
				'id' => 'hover_image',
				'type' => 'toggle_switch',
				'default' => 'off',
				'label' => __('Product Image Hover', 'builder-wc'),
				'options' => array(
					'on' => array('name'=>'yes', 'value' =>'s'),
					'off' => array('name'=>'', 'value' =>'hi')
				),
				'help' => __( 'On hover, show the first product gallery image.', 'builder-wc' ),
			),
			array(
				'id' => 'image_size_products',
				'type' => 'select',
				'label' =>__('Image Size', 'builder-wc'),
				'hide' => !Themify_Builder_Model::is_img_php_disabled(),
				'image_size' => true
			),
			array(
				'id' => 'img_width_products',
				'type' => 'number',
				'label' => __('Image Width', 'builder-wc')
			),
			array(
				'id' => 'img_height_products',
				'type' => 'number',
				'label' => __('Image Height', 'builder-wc')
			),
			array(
				'id' => 'unlink_feat_img_products',
				'type' => 'toggle_switch',
				'label' => __('Unlink Product Image', 'builder-wc'),
				'options' =>'simple'
			),
			array(
				'id' => 'hide_post_title_products',
				'type' => 'toggle_switch',
				'label' => __('Product Title', 'builder-wc'),
				'binding' => array(
					'checked' => array(
						'show' => array('unlink_post_title_products','title_tag')
					),
					'not_checked' => array(
						'hide' => array('unlink_post_title_products','title_tag')
					)
				)
			),
            array(
                'id' => 'title_tag',
                'type' => 'select',
                'label' => __('Product Title Tag', 'builder-wc'),
                'h_tags' => true,
                'default' => 'h3'
            ),
            array(
                'id' => 'unlink_post_title_products',
                'type' => 'toggle_switch',
                'label' => __('Unlink Product Title', 'builder-wc'),
                'options' =>'simple'
            ),
			array(
				'id' => 'show_product_categories',
				'type' => 'toggle_switch',
				'label' => __('Product Categories', 'builder-wc'),
				'options' => array(
				    'on' => array('name'=>'yes', 'value' =>'s'),
				    'off' => array('name'=>'no', 'value' =>'hi')
				)
			),
			array(
				'id' => 'show_product_tags',
				'type' => 'toggle_switch',
				'label' => __('Product Tags', 'builder-wc'),
				'options' => array(
					'on' => array('name'=>'yes', 'value' =>'s'),
					'off' => array('name'=>'no', 'value' =>'hi')
				)
			),
			array(
				'id' => 'hide_price_products',
				'type' => 'toggle_switch',
				'label' => __('Price', 'builder-wc')
			),
			array(
				'id' => 'hide_add_to_cart_products',
				'type' => 'toggle_switch',
				'label' => __('Add to Cart', 'builder-wc')
			),
			array(
				'id' => 'hide_rating_products',
				'type' => 'toggle_switch',
				'label' => __('Rating', 'builder-wc'),
				'binding' => array(
					'checked' => array('show' => 'show_empty_rating'),
					'not_checked' => array('hide' => 'show_empty_rating')
				)
			),
			array(
				'id' => 'show_empty_rating',
				'label' => '',
				'type' => 'checkbox',
				'options' => array(
					array('value' => __('Show empty rating too', 'builder-wc'), 'name' => 'show')
				)
			),
			array(
				'id' => 'hide_sales_badge',
				'type' => 'toggle_switch',
				'label' => __('Sales Badge', 'builder-wc')
			),
			array(
				'id' => 'hide_page_nav_products',
				'type' => 'select',
				'label' => __('Pagination', 'builder-wc'),
				'options' => array(
					'yes' => __( 'Hide', 'builder-wc' ),
					'no' => __( 'Pagination Links', 'builder-wc' ),
					'loadmore' => __( 'Load More', 'builder-wc' ),
					'infinite' => __( 'Infinite Scroll', 'builder-wc' ),
				),
			),
			array(
				'id' => 'hide_empty',
				'type' => 'toggle_switch',
				'label' => __('Hide Empty Module', 'builder-wc'),
				'help' => __('Hide the module when there is no posts.', 'builder-wc'),
				'options' => 'simple',
				'binding' => [
					'checked' => [ 'hide' => 'no_posts_group' ],
					'not_checked' => [ 'show' => 'no_posts_group' ],
				],
			),
			array(
				'id' => 'no_posts_group',
				'options' => array(
					array(
						'id' => 'no_posts',
						'type' => 'toggle_switch',
						'label' => __( 'No Posts Message', 'builder-wc' ),
						'options'   => array(
							'off' => array( 'name' => '', 'value' => 'dis' ),
							'on'  => array( 'name' => '1', 'value' => 'en' ),
						),
						'binding' => array(
							'checked' => array( 'show' => 'no_posts_msg' ),
							'not_checked' => array( 'hide' => 'no_posts_msg' ),
						)
					),
					array(
						'id' => 'no_posts_msg',
						'type' => 'textarea',
						'label' => ' ',
					),
				),
				'type' => 'group',
			),
			array(
				'type' => 'hook_content',
				'options' => self::get_hooks(),
			),
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_products' ),
		);
	}

	public static function get_hooks() {
		return [
			'before_image' => __( 'Before Product Image', 'builder-wc' ),
			'after_image'  => __( 'After Product Image', 'builder-wc' ),
			'before_title' => __( 'Before Product Title', 'builder-wc' ),
			'after_title'  => __( 'After Product Title', 'builder-wc' ),
			'before_price' => __( 'Before Price', 'builder-wc' ),
			'after_price'  => __( 'After Price', 'builder-wc' ),
			'before_cart'  => __( 'Before Add To Cart', 'builder-wc' ),
			'after_cart'   => __( 'After Add To Cart', 'builder-wc' ),
		];
	}

	public function get_live_default() {
		return array(
			'post_per_page_products' => 6,
			'hide_page_nav_products'=>'yes',
			'pause_on_hover_slider'=>'resume',
			'layout_products' => 'grid3',
		);
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
				    self::get_color('', 'background_color','bg_c','background-color','h')
				)
			    )
			))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
					self::get_font_family(),
					self::get_color('','font_color'),
					self::get_font_size(),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g' ),
					self::get_line_height(),
					self::get_text_align(),
					self::get_text_shadow(),
				)
			    ),
			    'h' => array(
				'options' => array(
					self::get_font_family('','f_f','h'),
					self::get_color('','f_c',null,null,'h'),
					self::get_font_size('','f_s','','h'),
					self::get_font_style( '', 'f_fs_g', 'f_fw_g', 'h' ),
					self::get_line_height('','l_h','h'),
					self::get_text_align('','t_a','h'),
					self::get_text_shadow('','t_sh','h'),
				)
			    )
			))
		    )),
		    self::get_expand('l', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
					self::get_color(' a:not(.add_to_cart_button)','link_color'),
					self::get_text_decoration(' a:not(.add_to_cart_button)')
				)
			    ),
			    'h' => array(
				'options' => array(
					self::get_color(' a:not(.add_to_cart_button)','link_color',null,null,'hover'),
					self::get_text_decoration(' a:not(.add_to_cart_button)','t_a','h')
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
		$product_container = array(
			    // Background
			    self::get_expand('bg', array(
				self::get_tab(array(
				    'n' => array(
					'options' => array(
					    self::get_color(' .product', 'b_c_p_ctr','bg_c','background-color')
					)
				    ),
				    'h' => array(
					'options' => array(
					    self::get_color(' .product', 'b_c_p_ctr','bg_c','background-color','h')
					)
				    )
				))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .product','p_p_ctr')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .product', 'p_p_ctr', 'h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
				       self::get_margin(' .product','m_p_ctr')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .product','m_p_ctr','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .product','b_p_ctr')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .product','b_p_ctr','h')
				    )
				)
			    ))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .product','b_sh_ctr')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .product','b_sh_ctr', 'h')
						)
					)
				))
			))
		);
		$product_content = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
				    'n' => array(
					'options' => array(
					    self::get_color(' .post-content', 'b_c_p_ct','bg_c','background-color')
					)
				    ),
				    'h' => array(
					'options' => array(
					     self::get_color(' .post-content', 'b_c_p_ct','bg_c','background-color','h')
					)
				    )
				))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					    self::get_font_family( ' .post-content', 'f_f_p_ct'),
					    self::get_color( ' .post-content','f_c_p_ct'),
					    self::get_font_size(' .post-content','f_s_p_ct'),
					    self::get_line_height(' .post-content','l_h_p_ct'),
					    self::get_text_align(' .post-content','t_a_p_ct'),
					    self::get_text_transform(' .post-content','t_t_p_ct'),
					    self::get_font_style(' .post-content', 'f_sy_p_ct', 'f_w_p_ct'),
						self::get_text_shadow(' .post-content', 't_sh_p_c'),
				    )
				),
				'h' => array(
				    'options' => array(
					    self::get_font_family(' .post-content', 'f_f_p_ct','h'),
					    self::get_color(' .post-content','f_c_p_ct',null,null,'h'),
					    self::get_font_size(' .post-content','f_s_p_ct','','h'),
					    self::get_line_height(' .post-content','l_h_p_ct','h'),
					    self::get_text_align( ' .post-content','t_a_p_ct','h'),
					    self::get_text_transform(' .post-content','t_t_p_ct','h'),
					    self::get_font_style(' .post-content', 'f_sy_p_ct', 'f_w_p_ct','h'),
						self::get_text_shadow(' .post-content', 't_sh_p_c','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .post-content','p_p_ct')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .post-content','p_p_ct','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
				       self::get_margin(' .post-content','m_p_ct')
				    )
				),
				'h' => array(
				    'options' => array(
					 self::get_margin(' .post-content','m_p_ct','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .post-content','b_p_ct')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .post-content','b_p_ct','h')
				    )
				)
			    ))
			))
                        
		);
		$product_title = array(
			// font
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					    self::get_font_family(array( '.module .product h3', '.module .product h3 a' ), 'f_f_p_t'),
					    self::get_color(array( '.module .product h3', '.module .product h3 a'),'f_c_p_t'),
					    self::get_font_size('.module .product h3','f_s_p_t'),
					    self::get_line_height('.module .product h3','l_h_p_t'),
					    self::get_text_align('.module .product h3','t_a_p_t'),
					    self::get_text_transform('.module .product h3','t_t_p_t'),
					    self::get_font_style(array( '.module .product h3', '.module .product h3 a' ), 'f_sy_p_t', 'f_w_p_t'),
						self::get_text_shadow(array( '.module .product h3', '.module .product h3 a'), 't_sh_p_t'),
				    )
				),
				'h' => array(
				    'options' => array(
					    self::get_font_family(array( '.module .product h3', '.module .product h3 a'), 'f_f_p_t','h'),
					    self::get_color(array( '.module .product h3', '.module .product h3 a'),'f_c_p_t',null,null,'h'),
					    self::get_font_size('.module .product h3','f_s_p_t','','h'),
					    self::get_line_height('.module .product h3','l_h_p_t','h'),
					    self::get_text_align('.module .product h3','t_a_p_t','h'),
					    self::get_text_transform('.module .product h3','t_t_p_t','h'),
					    self::get_font_style(array( '.module .product h3', '.module .product h3 a'), 'f_sy_p_t', 'f_w_p_t','h'),
						self::get_text_shadow(array( '.module .product h3', '.module .product h3 a'), 't_sh_p_t'.'h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding('.module .product .woocommerce-loop-product__title','p_p_t')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding('.module .product .woocommerce-loop-product__title','p_p_t','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
				       self::get_margin('.module .product .woocommerce-loop-product__title','m_p_t')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin('.module .product .woocommerce-loop-product__title','m_p_t','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border('.module .product .woocommerce-loop-product__title','b_p_t')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border('.module .product .woocommerce-loop-product__title','b_p_t','h')
				    )
				)
			    ))
			))
                        
		);
		$image = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .post-image img', 'p_i_bg_c', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .post-image img', 'p_i_bg_c', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding(' .post-image img', 'p_i_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding(' .post-image img', 'p_i_p', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin(' .post-image img', 'p_i_m')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin(' .post-image img', 'p_i_m', 'h')
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border(' .post-image img', 'p_i_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border(' .post-image img', 'p_i_b', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .post-image img', 'p_i_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .post-image img', 'p_i_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .post-image img', 'p_i_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .post-image img', 'p_i_b_sh', 'h')
						)
					)
				))
			))
		
		);
		$price = array(
			// Font
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .product .price', 'f_f_p_p'),
					self::get_color(' .product .price','f_c_p_p'),
					self::get_font_size(' .product .price','f_s_p_p'),
					self::get_line_height(' .product .price','l_h_p_p'),
					self::get_text_align(' .product .price','t_a_p_p'),
					self::get_font_style(' .product .price', 'f_sy_p_p', 'f_w_p_p'),
					self::get_text_shadow(' .product .price', 't_sh_p'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .product .price', 'f_f_p_p','h'),
					self::get_color(' .product .price','f_c_p_p',null,null,'h'),
					self::get_font_size(' .product .price','f_s_p_p','','h'),
					self::get_line_height(' .product .price','l_h_p_p','h'),
					self::get_text_align(' .product .price','t_a_p_p','h'),
					self::get_font_style(' .product .price', 'f_sy_p_p', 'f_w_p_p','h'),
					self::get_text_shadow(' .product .price', 't_sh_p','h'),
				    )
				)
			    ))
			)),
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .product .price','p_p_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .product .price','p_p_p','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
				       self::get_margin(' .product .price','m_p_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .product .price','m_p_p','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .product .price','b_p_p')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .product .price','b_p_p','h')
				    )
				)
			    ))
			))
		);
		$button = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
				    'n' => array(
					'options' => array(
					    self::get_color(' .product .add-to-cart-button .button', 'b_c_p_b','bg_c','background-color'),
					)
				    ),
				    'h' => array(
					'options' => array(
					     self::get_color(' .product .add-to-cart-button .button:hover', 'b_c_h_p_b','bg_c','background-color')
					)
				    )
				))
			)),
			// Font
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .product .add-to-cart-button .button', 'f_f_p_b'),
					self::get_color(' .product .add-to-cart-button .button','f_c_p_b'),
					self::get_font_size(' .product .add-to-cart-button .button','f_s_p_b'),
					self::get_font_style(' .product .add-to-cart-button .button','f_fs_p_b', 'f_fw_p_b'),
					self::get_line_height(' .product .add-to-cart-button .button','l_h_p_b'),
					self::get_text_align(' .product .add-to-cart-button','t_a_p_b'),
					self::get_text_shadow(' .product .add-to-cart-button .button', 't_sh_b'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .product .add-to-cart-button .button:hover', 'f_f_h_p_b'),
					self::get_color(' .product .add-to-cart-button .button:hover','f_c_h_p_b'),
					self::get_font_size(' .product .add-to-cart-button .button','f_s_p_b','','h'),
					self::get_font_style(' .product .add-to-cart-button .button','f_fs_p_b', 'f_fw_p_b', 'h'),
					self::get_line_height(' .product .add-to-cart-button .button','l_h_p_b','h'),
					self::get_text_align(' .product .add-to-cart-button','t_a_p_b','h'),
					self::get_text_shadow(' .product .add-to-cart-button .button', 't_sh_b','h'),
				    )
				)
			    ))
			)),
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .product .add-to-cart-button .button','p_p_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .product .add-to-cart-button .button','p_p_b','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
				       self::get_margin(' .product .add-to-cart-button .button','m_p_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .product .add-to-cart-button .button','m_p_b','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .product .add-to-cart-button .button','b_p_b')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .product .add-to-cart-button .button','b_p_b','h')
				    )
				)
			    ))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .product .add-to-cart-button .button', 'r_c_p_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .product .add-to-cart-button .button', 'r_c_p_b', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .product .add-to-cart-button .button', 'sh_p_b')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .product .add-to-cart-button .button', 'sh_p_b', 'h')
						)
					)
				))
			))
		);
		
		$controls = array(
			// Arrows
			self::get_expand(__('Arrows', 'builder-wc'), array(
			self::get_tab(array(
					'n' => array(
						'options' => array(
						   self::get_width(array(' .tf_carousel_nav_wrap .carousel-prev',' .tf_carousel_nav_wrap .carousel-next'), 'w_ctrl'),
						   self::get_height(array(' .tf_carousel_nav_wrap .carousel-prev',' .tf_carousel_nav_wrap .carousel-next'), 'h_ctrl')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width(array(' .tf_carousel_nav_wrap .carousel-prev:hover',' .tf_carousel_nav_wrap .carousel-next:hover'), 'w_ctrl_h'),
							self::get_height(array(' .tf_carousel_nav_wrap .carousel-prev:hover',' .tf_carousel_nav_wrap .carousel-next:hover'), 'h_ctrl_h')
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
					'c' => array(
						'label' => __('Container', 'builder-wc'),
						'options' => $product_container
					),
					'co' => array(
						'label' => __('Description', 'builder-wc'),
						'options' => $product_content
					),
					'i' => array(
						'label' => __('Image', 'builder-wc'),
						'options' => $image
					),
					't' => array(
						'label' => __('Title', 'builder-wc'),
						'options' => $product_title
					),
					'p' => array(
						'label' => __('Price', 'builder-wc'),
						'options' => $price
					),
					'b' => array(
						'label' => __('Button', 'builder-wc'),
						'options' => $button
					),
					'ctrl' => array(
						'label' => __('Controls', 'builder-wc'),
						'options' => $controls
					)
					)
			
		    );
	}
	
	public static function set_filters($args){
	    self::$args=$args;
	    self::remove_filters();
	    $priority=$args['hide_feat_img_products']==='yes'?1000:10;
	    add_filter( 'woocommerce_product_get_image', array(__CLASS__,'loop_image'),$priority,6);
	    
	    if( $args['hide_rating_products'] !== 'yes' ) {
		add_filter('option_woocommerce_enable_review_rating', array(__CLASS__,'enable'),100);
		// Always show rating even for 0 rating
		if ($args['show_empty_rating']==='show') {
			add_filter('woocommerce_product_get_rating_html', array(__CLASS__, 'product_get_rating_html'), 100, 3);
		}
	    }
	}
	
	public static function loop_image($image, $product, $size, $attr, $placeholder, $orig_image ){
	    if(self::$args['hide_feat_img_products']==='yes'){
		return '';
	    }
	    return self::retrieve_template('partials/image.php', self::$args,dirname(__DIR__).'/templates','',false);
	}
	
	private static function remove_filters(){
	  if(self::$isLoop===false){
		    self::$isLoop=true;
		    $actions=array(
			    'woocommerce_before_shop_loop_item'=>array(
				    'woocommerce_template_loop_product_link_open'=>10
			    ),
			    'woocommerce_before_shop_loop_item_title'=>array(
				    'woocommerce_show_product_loop_sale_flash'=>10,
				    'woocommerce_template_loop_product_thumbnail'=>10
			    ),
			    'woocommerce_shop_loop_item_title'=>array(
				    'woocommerce_template_loop_product_title'=>10
			    ),
			    'woocommerce_after_shop_loop_item_title'=>array(
				    'woocommerce_template_loop_rating'=>5,
				    'woocommerce_template_loop_price'=>10
			    ),
			    'woocommerce_after_shop_loop_item'=>array(
				    'woocommerce_template_loop_product_link_close'=>5,
				    'woocommerce_template_loop_add_to_cart'=>10
			    )
		    );
		    foreach($actions as $ev=>$functions){
			    foreach($functions as $func=>$priority){
				if(has_action($ev,$func)){
					remove_action($ev,$func,$priority);
					if(!isset(self::$Actions[$ev])){
					    self::$Actions[$ev]=array();
					}
					self::$Actions[$ev][$func]=$priority;
				}
			    }
		    }
		}	
	}
	
	public static function revert_filters(){
		if(self::$isLoop===true){
		    foreach(self::$Actions as $ev=>$functions){
			if(!empty($functions)){
			    foreach($functions as $func=>$priority){
				add_action($ev,$func,$priority);
			    }
			}
		    }
		    $priority=self::$args['hide_feat_img_products']==='yes'?1000:10;
		    remove_filter( 'woocommerce_product_get_image', array(__CLASS__,'loop_image'),$priority,6);
		    remove_filter('option_woocommerce_enable_review_rating', array(__CLASS__,'enable'),100);
		    remove_filter('woocommerce_product_get_rating_html', array(__CLASS__, 'product_get_rating_html'), 100, 3);
		    self::$args=self::$Actions=array();
		    self::$isLoop=false;
		}
	}
	
	public static function enable(){
	    return 'yes';
	}
	
	public static function disable(){
	    return 'no';
	}
	
	/*
 	* Always display rating in archive product page
 	*/
	public static function product_get_rating_html( $rating_html, $rating, $count ) {
		if('0' === $rating){
			/* translators: %s: rating */
			$label = __( 'Rated 0 out of 5', 'builder-wc' );
			$rating_html  = '<div class="star-rating" role="img" aria-label="' . $label . '">' . wc_get_star_rating_html( $rating, $count ) . '</div>';
		}
		return $rating_html;
	}

	/**
	 * Renders the content added by Hook Content feature. This doesn't use WP's hook system
	 * for faster performance.
	 *
	 * @return null
	 */
	public static function display_hook( $location, $hook_content ) {
		if ( isset( $hook_content[ $location ] ) ) {
			foreach ( $hook_content[ $location ] as $content ) {
				echo do_shortcode( $content );
			}
		}
	}
}
if(method_exists('Themify_Builder_Model', 'add_module')){
    new TB_Products_Module();
}
else{
    Themify_Builder_Model::register_module('TB_Products_Module');
}
