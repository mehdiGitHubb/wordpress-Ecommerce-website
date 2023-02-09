<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Testimonial Posts
 * Description: Display testimonial custom post type
 */

class TB_Testimonial_Module extends Themify_Builder_Component_Module {//deprecated

    public function __construct() {
	parent::__construct('testimonial');
	add_filter( 'themify_metabox/fields/themify-meta-boxes', array($this, 'cpt_meta_boxes'), 100 ); // requires low priority so that it loads after theme's metaboxes
	if (!shortcode_exists('themify_' . $this->slug . '_posts')) {
	    add_shortcode('themify_' . $this->slug . '_posts', array($this, 'do_shortcode'));
	}
    }
	
    public function get_name(){
	return __('Testimonial Posts', 'themify');
    }
    
    public function get_icon(){
	return 'clipboard';
    }
    
    public function get_assets() {
	    return array(
		    'css'=>1
	    );
    }

    public function get_title($module) {
	$type = isset($module['mod_settings']['type_query_testimonial']) ? $module['mod_settings']['type_query_testimonial'] : 'category';
	$category = isset($module['mod_settings']['category_testimonial']) ? $module['mod_settings']['category_testimonial'] : '';
	$slug_query = isset($module['mod_settings']['query_slug_testimonial']) ? $module['mod_settings']['query_slug_testimonial'] : '';

	if ('category' === $type) {
	    return sprintf('%s : %s', __('Category', 'themify'), $category);
	} else {
	    return sprintf('%s : %s', __('Slugs', 'themify'), $slug_query);
	}
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_testimonial',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_testimonial',
		'type' => 'layout',
		'label' => __('Layout', 'themify'),
		'mode' => 'sprite',
		'options' => array(
			array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'themify')),
			array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'themify')),
		    array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
		    array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
		    array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
		    array('img' => 'fullwidth', 'value' => 'fullwidth', 'label' => __('fullwidth', 'themify'))
		),
		'control'=>array(
		    'classSelector'=>'.builder-posts-wrap'
		)
	    ),
	    array(
		'type' => 'query_posts',
		'term_id' => 'category_testimonial',
		'slug_id'=>'query_slug_testimonial',
		'taxonomy'=>'testimonial-category',
		'help' => sprintf(__('Add more <a href="%s" target="_blank">testimonials</a>', 'themify'), admin_url('post-new.php?post_type=testimonial')),
	    ),
	    array(
		'id' => 'post_per_page_testimonial',
		'type' => 'number',
		'label' => __('Number of Posts', 'themify'),
		'help' => __('number of posts to show', 'themify')
	    ),
	    array(
		'id' => 'offset_testimonial',
		'type' => 'number',
		'label' => __('Offset', 'themify'),
		'help' => __('number of post to displace or pass over', 'themify')
	    ),
	    array(
		'id' => 'order_testimonial',
		'type' => 'select',
		'label' => __('Order', 'themify'),
		'help' => __('Sort posts in ascending or descending order.', 'themify'),
		'order' =>true
	    ),
	    array(
		'id' => 'orderby_testimonial',
		'type' => 'select',
		'label' => __('Order By', 'themify'),
		'orderBy'=>true,
		'binding' => array(
		    'select' => array('hide' =>'meta_key_testimonial'),
		    'meta_value' => array('show' =>'meta_key_testimonial'),
		    'meta_value_num' => array('show' =>'meta_key_testimonial')
		)
	    ),
	    array(
		'id' => 'meta_key_testimonial',
		'type' => 'text',
		'label' => __('Custom Field Key', 'themify')
	    ),
	    array(
		'id' => 'display_testimonial',
		'type' => 'select',
		'label' => __('Display', 'themify'),
		'options' => array(
		    'content' => __('Content', 'themify'),
		    'excerpt' => __('Excerpt', 'themify'),
		    'none' => __('None', 'themify')
		)
	    ),
	    array(
		'id' => 'hide_feat_img_testimonial',
		'type' => 'select',
		'label' => __('Hide Featured Image', 'themify'),
		'echoose' => true
	    ),
	    array(
		'id' => 'image_size_testimonial',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'img_width_testimonial',
		'type' => 'number',
		'label' => __('Image Width', 'themify')
	    ),
	    array(
		'id' => 'img_height_testimonial',
		'type' => 'number',
		'label' => __('Image Height', 'themify')
	    ),
	    array(
		'id' => 'hide_post_title_testimonial',
		'type' => 'select',
		'label' => __('Hide Post Title', 'themify'),
		'echoose' => true
	    ),
	    array(
		'id' => 'hide_page_nav_testimonial',
		'type' => 'select',
		'label' => __('Hide Pagination', 'themify'),
		'echoose' => true
	    ),
	    array( 'type' => 'custom_css_id', 'custom_css' => 'css_testimonial' ),
	);
    }

    public function get_styling() {
	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .post', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .post', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array(' .post-title', ' .post-title a')),
			    self::get_color(array(' .post', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6', ' .post-title', ' .post-title a'), 'font_color'),
			    self::get_font_size(' .post'),
			    self::get_line_height(' .post'),
			    self::get_letter_spacing(' .post'),
			    self::get_text_align(' .post'),
			    self::get_text_transform(' .post'),
			    self::get_font_style(' .post'),
			    self::get_text_decoration(' .post', 'text_decoration_regular'),
				self::get_text_shadow(array(' .post-title', ' .post-title a')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(' .post-title', ' .post-title a'), 'f_f', 'h'),
			    self::get_color(array(':hover .post', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6', ':hover .post-title', ':hover .post-title a'), 'f_c_h'),
			    self::get_font_size(' .post', 'f_s', '', 'h'),
			    self::get_font_style(' .post', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .post', 't_d_r', 'h'),
				self::get_text_shadow(array(' .post-title', ' .post-title a'),'t_sh','h'),
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
			    self::get_padding(' .post')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .post', 'p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .post')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .post', 'm', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .post')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .post', 'b', 'h')
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
						self::get_height('.post'),
						self::get_min_height('.post'),
						self::get_max_height('.post')
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

	$testimonial_title = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array(' .post-title', ' .post-title a'), 'font_family_title'),
			self::get_font_size(' .post-title', 'font_size_title'),
			self::get_line_height(' .post-title', 'line_height_title'),
			self::get_letter_spacing(' .post-title', 'letter_spacing_title'),
			self::get_text_transform(' .post-title', 't_t_t'),
			self::get_font_style(' .post-title', 'f_sy_t', 'f_b_t'),
			self::get_text_shadow(array(' .post-title', ' .post-title a'), 't_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array(' .post-title', ' .post-title a'), 'f_f_t', 'h'),
			self::get_color(array(' .post-title', ' .post-title a'), 'f_c_t',null,null,'h'),
			self::get_font_size(' .post-title', 'f_s_t', '', 'h'),
			self::get_font_style(' .post-title', 'f_sy_t', 'f_b_t', 'h'),
				self::get_text_shadow(array(' .post-title', ' .post-title a'), 't_sh_t','h'),
		    )
		)
	    ))
	);

	$testimonial_content = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .testimonial-post .post-content', 'font_family_content'),
			self::get_color(' .testimonial-post .post-content', 'font_color_content'),
			self::get_font_size(' .testimonial-post .post-content', 'font_size_content'),
			self::get_line_height(' .testimonial-post .post-content', 'line_height_content'),
			self::get_text_shadow(' .testimonial-post .post-content', 't_sh_c'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .testimonial-post .post-content', 'f_f_c', 'f_f', 'h'),
			self::get_color(' .testimonial-post .post-content', 'f_c_c', null, null, 'h'),
			self::get_font_size(' .testimonial-post .post-content', 'f_s_c', '', 'h'),
			self::get_text_shadow(' .testimonial-post .post-content', 't_sh_c','h'),
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
		    'label' => __('Testimonial Title', 'themify'),
		    'options' => $testimonial_title
		),
		'c' => array(
		    'label' => __('Testimonial Content', 'themify'),
		    'options' => $testimonial_content
		)
	    )
	);
    }


    function get_metabox() {
	// Testimonial Meta Box Options
	$meta_box = array(
	    // Featured Image Size
	    Themify_Builder_Model::$featured_image_size,
	    // Image Width
	    Themify_Builder_Model::$image_width,
	    // Image Height
	    Themify_Builder_Model::$image_height,
	    // Testimonial Author Name
	    array(
		'name' => '_testimonial_name',
		'title' => __('Testimonial Author Name', 'themify'),
		'description' => '',
		'type' => 'textbox',
		'meta' => array()
	    ),
	    // Testimonial Author Link
	    array(
		'name' => '_testimonial_link',
		'title' => __('Testimonial Author Link', 'themify'),
		'description' => '',
		'type' => 'textbox',
		'meta' => array()
	    ),
	    // Testimonial Author Company
	    array(
		'name' => '_testimonial_company',
		'title' => __('Testimonial Author Company', 'themify'),
		'description' => '',
		'type' => 'textbox',
		'meta' => array()
	    ),
	    // Testimonial Author Position
	    array(
		'name' => '_testimonial_position',
		'title' => __('Testimonial Author Position', 'themify'),
		'description' => '',
		'type' => 'textbox',
		'meta' => array()
	    )
	);
	return $meta_box;
    }

    function do_shortcode($atts) {

	$atts=shortcode_atts(array(
	    'id' => '',
	    'title' => 'no', // no
	    'image' => 'yes', // no
	    'image_w' => 80,
	    'image_h' => 80,
	    'display' => 'content', // excerpt, none
	    'more_link' => false, // true goes to post type archive, and admits custom link
	    'more_text' => __('More &rarr;', 'themify'),
	    'limit' => 4,
	    'category' => 0, // integer category ID
	    'order' => 'DESC', // ASC
	    'orderby' => 'date', // title, rand
	    'style' => 'grid2', // grid3, grid4, list-post
	    'show_author' => 'yes', // no
	    'section_link' => false // true goes to post type archive, and admits custom link
			), $atts);

	$module = array(
	    'module_ID' => $this->slug . '-' . rand(0, 10000),
	    'mod_name' => $this->slug,
	    'mod_settings' => array(
                'mod_title_testimonial' => '',
                'layout_testimonial' => $atts['style'],
                'category_testimonial' => $atts['category'],
                'post_per_page_testimonial' => $atts['limit'],
                'offset_testimonial' => '',
                'order_testimonial' => $atts['order'],
                'orderby_testimonial' => $atts['orderby'],
                'display_testimonial' => $atts['display'],
                'hide_feat_img_testimonial' => '',
                'image_size_testimonial' => '',
                'img_width_testimonial' => $atts['image_w'],
                'img_height_testimonial' => $atts['image_h'],
                'unlink_feat_img_testimonial' => 'no',
                'hide_post_title_testimonial' => $atts['title'] === 'yes' ? 'no' : 'yes',
                'unlink_post_title_testimonial' => 'no',
                'hide_post_date_testimonial' => 'no',
                'hide_post_meta_testimonial' => 'no',
                'hide_page_nav_testimonial' => 'yes',
                'animation_effect' => '',
                'css_testimonial' => ''
            )
	);

	return self::retrieve_template('template-' . $this->slug . '.php', $module, THEMIFY_BUILDER_TEMPLATES_DIR, '', false);
    }

    /**
     * Render plain content for static content.
     * 
     * @param array $module 
     * @return string
     */
    public function get_plain_content($module) {
	return ''; // no static content for dynamic content
    }

}
///////////////////////////////////////
// Module Options
///////////////////////////////////////

new TB_Testimonial_Module();//deprecated
