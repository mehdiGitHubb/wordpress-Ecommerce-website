<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Highlight
 * Description: Display highlight custom post type
 */

class TB_Highlight_Module extends Themify_Builder_Component_Module {//deprecated

    public function __construct() {
	parent::__construct('highlight');
	add_filter( 'themify_metabox/fields/themify-meta-boxes', array($this, 'cpt_meta_boxes'), 100 ); // requires low priority so that it loads after theme's metaboxes
	if (!shortcode_exists('themify_highlight_posts')) {
		add_shortcode('themify_highlight_posts', array($this, 'do_shortcode'));
	}
    }
	
    public function get_name(){
	return __('Highlight', 'themify');
    }
    
    public function get_icon(){
	return 'view-list-alt';
    }
	
	public function get_assets() {
		return array(
			'css'=>array('post'=>'post',$this->slug=>1)
		);
	}
    public function get_title($module) {
	$type = isset($module['mod_settings']['type_query_highlight']) ? $module['mod_settings']['type_query_highlight'] : 'category';
	$category = isset($module['mod_settings']['category_highlight']) ? $module['mod_settings']['category_highlight'] : '';
	$slug_query = isset($module['mod_settings']['query_slug_highlight']) ? $module['mod_settings']['query_slug_highlight'] : '';

	return 'category' === $type ? sprintf('%s : %s', __('Category', 'themify'), $category) : sprintf('%s : %s', __('Slugs', 'themify'), $slug_query);
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_highlight',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_highlight',
		'type' => 'layout',
		'label' => __('Highlight Layout', 'themify'),
		'mode' => 'sprite',
		'options' => array(
			array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'themify')),
			array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'themify')),
		    array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
		    array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
		    array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
		    array('img' => 'fullwidth', 'value' => 'fullwidth', 'label' => __('fullwidth', 'themify'))
		)
	    ),
		array(
			'type' => 'query_posts',
			'term_id' => 'category_highlight',
			'slug_id'=>'query_slug_highlight',
			'taxonomy'=>'highlight-category',
			'label' => __('Category', 'themify'),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">highlight posts</a>', 'themify'), admin_url('post-new.php?post_type=highlight')),
			//'wrap_class' => 'tb_group_element_category'
		),
	    array(
		'id' => 'post_per_page_highlight',
		'type' => 'number',
		'label' => __('Number of Posts', 'themify'),
		'help' => __('number of posts to show', 'themify')
	    ),
	    array(
		'id' => 'offset_highlight',
		'type' => 'number',
		'label' => __('Offset', 'themify'),
		'help' => __('number of post to displace or pass over', 'themify')
	    ),
	    array(
		'id' => 'order_highlight',
		'type' => 'select',
		'label' => __('Order', 'themify'),
		'help' => __('Descending = show newer posts first', 'themify'),
		'order' =>true
	    ),
	    array(
		'id' => 'orderby_highlight',
		'type' => 'select',
		'label' => __('Order By', 'themify'),
		'orderBy'=>true,
		'binding' => array(
		    'select' => array('hide' => 'meta_key_highlight'),
		    'meta_value' => array('show' => 'meta_key_highlight'),
		    'meta_value_num' => array('show' =>'meta_key_highlight')
		)
	    ),
	    array(
		'id' => 'meta_key_highlight',
		'type' => 'text',
		'label' => __('Custom Field Key', 'themify'),
	    ),
	    array(
		'id' => 'display_highlight',
		'type' => 'select',
		'label' => __('Display', 'themify'),
		'options' => array(
		    'content' => __('Content', 'themify'),
		    'excerpt' => __('Excerpt', 'themify'),
		    'none' => __('None', 'themify')
		)
	    ),
	    array(
		'id' => 'hide_feat_img_highlight',
		'type' => 'select',
		'label' => __('Hide Featured Image', 'themify'),
		'echoose' => true
	    ),
	    array(
		'id' => 'image_size_highlight',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'img_width_highlight',
		'type' => 'number',
		'label' => __('Image Width', 'themify')
	    ),
	    array(
		'id' => 'img_height_highlight',
		'type' => 'number',
		'label' => __('Image Height', 'themify')
	    ),
	    array(
		'id' => 'hide_post_title_highlight',
		'type' => 'select',
		'label' => __('Hide Post Title', 'themify'),
		'echoose' => true
	    ),
	    array(
		'id' => 'hide_page_nav_highlight',
		'type' => 'select',
		'label' => __('Hide Pagination', 'themify'),
		'echoose' => true
	    ),
	    array( 'type' => 'custom_css_id',  'custom_css' => 'css_highlight' )
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

	$highlight_title = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array('.module .post-title', '.module .post-title a'), 'font_family_title'),
			self::get_color(array('.module .post-title', '.module .post-title a'), 'font_color_title'),
			self::get_font_size('.module .post-title', 'font_size_title'),
			self::get_line_height('.module .post-title', 'line_height_title'),
			self::get_letter_spacing('.module .post-title', 'letter_spacing_title'),
			self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_h_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array('.module .post-title', '.module .post-title a'), 'f_f_t', 'h'),
			self::get_color(array('.module .post-title', '.module .post-title a'), 'f_c_t', null, null, 'h'),
			self::get_font_size('.module .post-title', 'f_s_t', '', 'h'),
			self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_h_t','h'),
		    )
		)
	    ))
	);

	$highlight_content = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .highlight-post .post-content', 'font_family_content'),
			self::get_color(' .highlight-post .post-content', 'font_color_content'),
			self::get_font_size(' .highlight-post .post-content', 'font_size_content'),
			self::get_line_height(' .highlight-post .post-content', 'line_height_content'),
			self::get_text_shadow(' .highlight-post .post-content', 't_sh_h_c'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .highlight-post .post-content', 'f_f_c', 'h'),
			self::get_color(' .highlight-post .post-content', 'f_c_c', null, null, 'h'),
			self::get_font_size(' .highlight-post .post-content', 'f_s_c', '', 'h'),
			self::get_text_shadow(' .highlight-post .post-content', 't_sh_h_c','h'),
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
		    'label' => __('Highlight Title', 'themify'),
		    'options' => $highlight_title
		),
		'c' => array(
		    'label' => __('Highlight Content', 'themify'),
		    'options' => $highlight_content
		)
	    )
	);
    }


    function get_metabox() {
	// Highlight Meta Box Options
	return array(
	    // Featured Image Size
	    Themify_Builder_Model::$featured_image_size,
	    // Image Width
	    Themify_Builder_Model::$image_width,
	    // Image Height
	    Themify_Builder_Model::$image_height,
	    // External Link
	    Themify_Builder_Model::$external_link,
	    // Lightbox Link
	    Themify_Builder_Model::$lightbox_link
	);
    }

    function do_shortcode($atts) {
	$atts=shortcode_atts(array(
	    'id' => '',
	    'title' => 'yes', // no
	    'image' => 'yes', // no
	    'image_w' => 68,
	    'image_h' => 68,
	    'display' => 'content', // excerpt, none
	    'more_link' => false, // true goes to post type archive, and admits custom link
	    'more_text' => __('More &rarr;', 'themify'),
	    'limit' => 6,
	    'category' => 0, // integer category ID
	    'order' => 'DESC', // ASC
	    'orderby' => 'date', // title, rand
	    'style' => 'grid3', // grid4, grid2, list-post
	    'section_link' => false // true goes to post type archive, and admits custom link
			), $atts);

	$module = array(
	    'module_ID' => $this->slug . '-' . rand(0, 10000),
	    'mod_name' => $this->slug,
	    'mod_settings' => array(
                'mod_title_highlight' => '',
                'layout_highlight' => $atts['style'],
                'category_highlight' => $atts['category'],
                'post_per_page_highlight' => $atts['limit'],
                'offset_highlight' => '',
                'order_highlight' => $atts['order'],
                'orderby_highlight' => $atts['orderby'],
                'display_highlight' => $atts['display'],
                'hide_feat_img_highlight' => $atts['image'] === 'yes' ? 'no' : 'yes',
                'image_size_highlight' => '',
                'img_width_highlight' => $atts['image_w'],
                'img_height_highlight' => $atts['image_h'],
                'hide_post_title_highlight' => $atts['title'] === 'yes' ? 'no' : 'yes',
                'hide_post_date_highlight' => '',
                'hide_post_meta_highlight' => '',
                'hide_page_nav_highlight' => 'yes',
                'animation_effect' => '',
                'css_highlight' => ''
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
new TB_Highlight_Module();//deprecated
