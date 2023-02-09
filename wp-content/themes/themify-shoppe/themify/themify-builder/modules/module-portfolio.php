<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Portfolio
 * Description: Display portfolio custom post type
 */

class TB_Portfolio_Module extends Themify_Builder_Component_Module {//deprecated

    protected static $post_filter;

    public function __construct() {
	parent::__construct('portfolio');
	///////////////////////////////////////
	// Load Post Type
	///////////////////////////////////////
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! Themify_Builder_Model::is_plugin_active( 'themify-portfolio-post/themify-portfolio-post.php' ) ) {
		add_filter( 'themify_metabox/fields/themify-meta-boxes', array($this, 'cpt_meta_boxes'), 100 ); // requires low priority so that it loads after theme's metaboxes
		if ( ! shortcode_exists( 'themify_portfolio_posts' ) ) {
			add_shortcode( 'themify_portfolio_posts', array( $this, 'do_shortcode' ) );
		}
	}
    }
    
    public function get_name(){
	return __('Portfolio', 'themify');
    }
    
    public function get_icon(){
	return 'briefcase';
    }
    
    public function get_assets() {
	    return array(
		    'css'=>array('post'=>'post')
	    );
    }
    
    public function get_title($module) {
	$type = isset($module['mod_settings']['type_query_portfolio']) ? $module['mod_settings']['type_query_portfolio'] : 'category';
	$category = isset($module['mod_settings']['category_portfolio']) ? $module['mod_settings']['category_portfolio'] : '';
	$slug_query = isset($module['mod_settings']['query_slug_portfolio']) ? $module['mod_settings']['query_slug_portfolio'] : '';

	if ('category' === $type) {
	    return sprintf('%s : %s', __('Category', 'themify'), $category);
	} else {
	    return sprintf('%s : %s', __('Slugs', 'themify'), $slug_query);
	}
    }

    public function get_options() {

	return array(
	    array(
		'id' => 'mod_title_portfolio',
		'type' => 'title'
	    ),
		array(
			'type' => 'query_posts',
			'term_id' => 'category_portfolio',
			'slug_id'=>'query_slug_portfolio',
			'taxonomy'=>'portfolio-category',
			'description' => sprintf(__('Add more <a href="%s" target="_blank">portfolio posts</a>', 'themify'), admin_url('post-new.php?post_type=portfolio'))
		),
	    array(
		'id' => 'layout_portfolio',
		'type' => 'layout',
		'label' => __('Portfolio Layout', 'themify'),
		'control'=>array(
		    'classSelector'=>'.builder-posts-wrap'
		),
		'mode' => 'sprite',
		'options' => array(
            array('img' => 'fullwidth', 'value' => 'fullwidth', 'label' => __('fullwidth', 'themify')),
		    array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
            array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
            array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
            array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'themify')),
            array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'themify'))
		)
	    ),
	    array(
		'id' => 'post_per_page_portfolio',
		'type' => 'number',
		'label' => __('Number of Posts', 'themify'),
		'help' => __("Enter the number of posts to show.", 'themify')
	    ),
	    array(
		'id' => 'offset_portfolio',
		'type' => 'number',
		'label' => __('Offset', 'themify'),
		'help' => __("Enter number of post to display or pass over.", 'themify')
	    ),
	    array(
		'id' => 'order_portfolio',
		'type' => 'select',
		'label' => __('Order', 'themify'),
		'help' => __('Descending means show newer posts first. Ascending means show older posts first.', 'themify'),
		'order' =>true
	    ),
	    array(
		'id' => 'orderby_portfolio',
		'type' => 'select',
		'label' => __('Order By', 'themify'),
		'orderBy'=>true,
		'binding' => array(
		    'select' => array('hide' => 'meta_key_portfolio'),
		    'meta_value' => array('show' => 'meta_key_portfolio'),
		    'meta_value_num' => array('show' =>'meta_key_portfolio')
		)
	    ),
	    array(
		'id' => 'meta_key_portfolio',
		'type' => 'text',
		'label' => __('Custom Field Key', 'themify'),
	    ),
	    array(
		'id' => 'display_portfolio',
		'type' => 'select',
		'label' => __('Display', 'themify'),
		'options' => array(
		    'content' => __('Content', 'themify'),
		    'excerpt' => __('Excerpt', 'themify'),
		    'none' => __('None', 'themify')
		)
	    ),
	    array(
		'id' => 'hide_feat_img_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Featured Image', 'themify')
	    ),
	    array(
		'id' => 'image_size_portfolio',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'img_width_portfolio',
		'type' => 'number',
		'label' => __('Image Width', 'themify')
	    ),
	    array(
		'id' => 'auto_fullwidth_portfolio',
		'type' => 'checkbox',
		'label' => '',
		'options' => array(array('name' => '1', 'value' => __('Auto fullwidth image', 'themify'))),
		'wrap_class' => 'auto_fullwidth'
	    ),
	    array(
		'id' => 'img_height_portfolio',
		'type' => 'number',
		'label' => __('Image Height', 'themify')
	    ),
	    array(
		'id' => 'unlink_feat_img_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Unlink Featured Image', 'themify'),
		'options'=>'simple'
	    ),
	    array(
		'id' => 'hide_post_title_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Post Title', 'themify'),
            'binding' => array(
                'checked' => array(
                    'show' => array('unlink_post_title_portfolio','title_tag_portfolio')
                ),
                'not_checked' => array(
                    'hide' =>array('unlink_post_title_portfolio','title_tag_portfolio')
                )
            )
	    ),
        array(
            'id' => 'title_tag_portfolio',
            'type' => 'select',
            'label' => __('Title HTML Tag', 'themify'),
            'h_tags' => true,
            'default' => 'h2'
        ),
	    array(
		'id' => 'unlink_post_title_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Unlink Post Title', 'themify'),
		'options' =>'simple'
	    ),
	    array(
		'id' => 'hide_post_date_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Post Date', 'themify')
	    ),
	    array(
		'id' => 'hide_post_meta_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Post Meta', 'themify')
	    ),
	    array(
		'id' => 'hide_page_nav_portfolio',
		'type' => 'toggle_switch',
		'label' => __('Pagination', 'themify'),
                'binding' => array(
                    'checked' => array(
                        'show' => 'nav_type'
                    ),
                    'not_checked' => array(
                        'hide' =>'nav_type'
                    )
                )
	    ),
		array(
			'id' => 'nav_type',
			'type' => 'select',
			'label' => __('Pagination Type', 'themify'),
			'options' => array(
				'standard' => __('Standard', 'themify'),
				'ajax' => __('Load More', 'themify')
			)
		),
	    array(
			'id' => 'hide_empty',
			'type' => 'toggle_switch',
			'label' => __('Hide Empty Module', 'themify'),
			'help' => __('Hide the module when there is no posts.', 'themify'),
			'options' => 'simple',
	    ),
	    array( 'type' => 'custom_css_id' , 'custom_css' => 'css_portfolio' )
	);
    }

    public function get_live_default() {
	return array(
        'layout_portfolio' => 'grid4',
	    'post_per_page_portfolio' => 4,
	    'hide_page_nav_portfolio'=>'yes',
	    'display_portfolio' => 'excerpt'
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
			    self::get_color(' .post', 'bg_c', 'bg_c', 'background-color', null, 'h')
			)
		    )
		)),
		)),
		// Font
		self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('.module .post-title', '.module .post-title a')),
			    self::get_color(array(' .post', '.module h1', '.module h2', '.module h3:not(.module-title)', '.module h4', '.module h5', '.module h6', '.module .post-title', '.module .post-title a'), 'font_color'),
			    self::get_font_size(' .post'),
			    self::get_line_height(' .post'),
			    self::get_letter_spacing(' .post'),
			    self::get_text_align(' .post'),
			    self::get_text_transform(' .post'),
			    self::get_font_style(' .post'),
			    self::get_text_decoration(' .post', 'text_decoration_regular'),
				self::get_text_shadow(array('.module .post-title', '.module .post-title a')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array('.module .post-title', '.module .post-title a'), 'f_f', 'h'),
			    self::get_color(array(':hover .post', '.module:hover h1', '.module:hover h2', '.module:hover h3:not(.module-title)', '.module:hover h4', '.module:hover h5', '.module:hover h6', '.module:hover .post-title', '.module:hover .post-title a'), 'f_c_h'),
			    self::get_font_size(' .post', 'f_s', '', 'h'),
			    self::get_font_style(' .post', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .post', 't_d_r', 'h'),
				self::get_text_shadow(array('.module .post-title', '.module .post-title a'),'t_sh','h'),
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
			    self::get_color(' a', 'link_color',null, null, 'hover'),
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
			    self::get_margin('')
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
	);

	$portfolio_container = array(
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
			    self::get_heading_margin_multi_field(' .post', '', 'top', '', 'article'),
			    self::get_heading_margin_multi_field(' .post', '', 'bottom', '', 'article')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_heading_margin_multi_field(' .post:hover', '', 'top', '', 'a_h'),
			    self::get_heading_margin_multi_field(' .post:hover', '', 'bottom', '', 'a_h')
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
		// Rounded Corners
		self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .post', 'r_c_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .post', 'r_c_cn', 'h')
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
							self::get_box_shadow(' .post', 'sh_cn')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .post', 'sh_cn', 'h')
						)
					)
				))
			)
		),
	);

	$portfolio_title = array(
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
			self::get_text_transform('.module .post-title', 't_t_title'),
			self::get_font_style('.module .post-title', 'f_sy_t', 'f_b_t'),
			self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array('.module .post-title', '.module .post-title a'), 'f_f_t', 'h'),
			self::get_color(array('.module .post-title', '.module .post-title a'), 'font_color_title', null,null,'hover'),
			self::get_font_size('.module .post-title', 'f_s_t', '', 'h'),
			self::get_font_style('.module .post-title', 'f_sy_t', 'f_b_t', 'f_w_t', 'h'),
			self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_t','h'),
		    )
		)
	    )),
	    // Padding
	    self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding('.module .post-title', 'p_t')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding('.module .post-title', 'p_t', 'h')
				)
				)
			))
	    )),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin('.module .post-title', 'm_t'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin('.module .post-title', 'm_t', 'h'),
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border('.module .post-title', 'b_t')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border('.module .post-title', 'b_t', 'h')
				)
				)
			))
	    ))
	);

	$portfolio_meta = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array(' .post-content .post-meta', ' .post-content .post-meta a'), 'font_family_meta'),
			self::get_color(array(' .post-content .post-meta', ' .post-content .post-meta a'), 'font_color_meta'),
			self::get_font_size(' .post-content .post-meta', 'font_size_meta'),
			self::get_line_height(' .post-content .post-meta', 'line_height_meta'),
			self::get_text_shadow(array(' .post-content .post-meta', ' .post-content .post-meta a'), 't_sh_m'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array(' .post-content .post-meta', ' .post-content .post-meta a'), 'f_f_m', 'h'),
			self::get_color(array(' .post-content .post-meta', ' .post-content .post-meta a'), 'f_c_m', null, null, 'h'),
			self::get_font_size(' .post-content .post-meta', 'f_s_m', '', 'h'),
			self::get_text_shadow(array(' .post-content .post-meta', ' .post-content .post-meta a'), 't_sh_m','h'),
		    )
		)
	    ))
	);

	$portfolio_date = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array(' .post .post-date', ' .post .post-date a'), 'font_family_date'),
			self::get_color(array(' .post .post-date', ' .post .post-date a'), 'font_color_date'),
			self::get_font_size(' .post .post-date', 'font_size_date'),
			self::get_line_height(' .post .post-date', 'line_height_date'),
			self::get_text_shadow(array(' .post .post-date', ' .post .post-date a'), 't_sh_d'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array(' .post .post-date', ' .post .post-date a'), 'f_f_d', 'h'),
			self::get_color(array(' .post .post-date', ' .post .post-date a'), 'font_color_date',null,null,'hover'),
			self::get_font_size(' .post .post-date', 'f_s_d', '', 'h'),
			self::get_text_shadow(array(' .post .post-date', ' .post .post-date a'), 't_sh_d','h'),
		    )
		)
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .post .post-date', 'p_d')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .post .post-date', 'p_d', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .post .post-date', 'm_d'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .post .post-date', 'm_d', 'h'),
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .post .post-date', 'b_d')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .post .post-date', 'b_d', 'h')
			)
		    )
		))
	    ))
	);

	$portfolio_content = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(' .post-content .entry-content', 'font_family_content'),
			self::get_color(' .post-content .entry-content', 'font_color_content'),
			self::get_font_size(' .post-content .entry-content', 'font_size_content'),
			self::get_line_height(' .post-content .entry-content', 'line_height_content'),
			self::get_text_align(' .post-content .entry-content', 't_a_c'),
			self::get_text_shadow(' .post-content .entry-content', 't_sh_c'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(' .post-content .entry-content', 'f_f_c', 'f_f', 'h'),
			self::get_color(' .post-content .entry-content', 'f_c_c', null, null, 'h'),
			self::get_font_size(' .post-content .entry-content', 'f_s_c', '', 'h'),
			self::get_text_shadow(' .post-content .entry-content', 't_sh_c','h'),
		    )
		)
	    ))
	);
	
	$featured_image = array(
	    // Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .post-image', 'b_c_f_i', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .post-image', 'b_c_f_i', 'bg_c', 'background-color', 'h')
				)
				)
			))
	    )),
	    // Padding
	    self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .post-image', 'p_f_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .post-image', 'p_f_i', 'h')
				)
				)
			))
	    )),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .post-image', 'm_f_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .post-image', 'm_f_i', 'h')
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .post-image', 'b_f_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .post-image', 'b_f_i', 'h')
				)
				)
			))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(array(' .post-image',' .post-image img'), 'f_i_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(array(' .post-image',' .post-image img'), 'f_i_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .post-image', 'f_i_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .post-image', 'f_i_sh', 'h')
					)
				)
			))
		))
	);

	$read_more = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .more-link', 'b_c_r_m', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .more-link', 'b_c_r_m', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .more-link', 'f_f_g'),
					self::get_color('.module .more-link', 'f_c_r_m'),
					self::get_font_size(' .more-link', 'f_s_r_m'),
					self::get_line_height(' .more-link', 'l_h_r_m'),
					self::get_letter_spacing(' .more-link', 'l_s_r_m'),
					self::get_text_align(' .more-link', 't_a_r_m'),
					self::get_text_transform(' .more-link', 't_t_r_m'),
					self::get_font_style(' .more-link', 'f_st_r_m', 'f_b_r_m'),
					self::get_text_shadow(' .more-link', 't_sh_r_m'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .more-link', 'f_f_g', 'h'),
					self::get_color('.module .more-link:hover', 'f_c_r_m_h','h'),
					self::get_font_size(' .more-link', 'f_s_r_m', '', 'h'),
					self::get_font_style(' .more-link', 'f_st_r_m', 'f_b_r_m', 'h'),
					self::get_text_shadow(' .more-link','t_sh_r_m','h'),
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .more-link', 'r_m_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .more-link', 'r_m_p', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .more-link', 'r_m_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .more-link', 'r_m_m', 'h')
				)
				)
			)),
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .more-link', 'r_m_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .more-link', 'r_m_b', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .more-link', 'r_c_r_m')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .more-link', 'r_c_r_m', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .more-link', 'sh_r_m')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .more-link', 'sh_r_m', 'h')
					)
				)
			))
		))
	);

	$pg_container = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .pagenav', 'b_c_pg_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .pagenav', 'b_c_pg_c', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .pagenav', 'f_f_pg_c'),
					self::get_color(' .pagenav', 'f_c_pg_c'),
					self::get_font_size(' .pagenav', 'f_s_pg_c'),
					self::get_line_height(' .pagenav', 'l_h_pg_c'),
					self::get_letter_spacing(' .pagenav', 'l_s_pg_c'),
					self::get_text_align(' .pagenav', 't_a_pg_c'),
					self::get_font_style(' .pagenav', 'f_st_pg_c', 'f_b_pg_c'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .pagenav', 'f_f_pg_c', 'h'),
					self::get_color(' .pagenav', 'f_c_pg_c','h'),
					self::get_font_size(' .pagenav', 'f_s_pg_c', '', 'h'),
					self::get_font_style(' .pagenav', 'f_st_pg_c', 'f_b_pg_c', 'h'),
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .pagenav', 'p_pg_c')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .pagenav', 'p_pg_c', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .pagenav', 'm_pg_c')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .pagenav', 'm_pg_c', 'h')
				)
				)
			)),
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .pagenav', 'b_pg_c')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .pagenav', 'b_pg_c', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .pagenav', 'r_c_pg_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .pagenav', 'r_c_pg_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav', 'sh_pg_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav', 'sh_pg_c', 'h')
					)
				)
			))
		))
	);

	$pg_numbers = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .pagenav a', 'b_c_pg_n', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .pagenav a', 'b_c_pg_n', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .pagenav a', 'f_f_pg_n'),
					self::get_color(' .pagenav a', 'f_c_pg_n'),
					self::get_font_size(' .pagenav a', 'f_s_pg_n'),
					self::get_line_height(' .pagenav a', 'l_h_pg_n'),
					self::get_letter_spacing(' .pagenav a', 'l_s_pg_n'),
					self::get_font_style(' .pagenav a', 'f_st_pg_n', 'f_b_pg_n'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .pagenav a', 'f_f_pg_n', 'h'),
					self::get_color(' .pagenav a:hover', 'f_c_pg_n_h',null,null,''),
					self::get_font_size(' .pagenav a', 'f_s_pg_n', '', 'h'),
					self::get_font_style(' .pagenav a', 'f_st_pg_n', 'f_b_pg_n', 'h'),
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .pagenav a', 'p_pg_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .pagenav a', 'p_pg_n', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .pagenav a', 'm_pg_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .pagenav a', 'm_pg_n', 'h')
				)
				)
			)),
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .pagenav a', 'b_pg_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .pagenav a', 'b_pg_n', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .pagenav a', 'r_c_pg_n')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .pagenav a', 'r_c_pg_n', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav a', 'sh_pg_n')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav a', 'sh_pg_n', 'h')
					)
				)
			))
		))
	);

	$pg_a_numbers = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .pagenav .current', 'b_c_pg_a_n', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .pagenav .current', 'b_c_pg_a_n', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .pagenav .current', 'f_f_pg_a_n'),
					self::get_color(' .pagenav .current', 'f_c_pg_a_n'),
					self::get_font_size(' .pagenav .current', 'f_s_pg_a_n'),
					self::get_line_height(' .pagenav .current', 'l_h_pg_a_n'),
					self::get_letter_spacing(' .pagenav .current', 'l_s_pg_a_n'),
					self::get_font_style(' .pagenav .current', 'f_st_pg_a_n', 'f_b_pg_a_n'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .pagenav .current', 'f_f_pg_a_n', 'h'),
					self::get_color(' .pagenav .current', 'f_c_pg_a_n','h'),
					self::get_font_size(' .pagenav .current', 'f_s_pg_a_n', '', 'h'),
					self::get_font_style(' .pagenav .current', 'f_st_pg_a_n', 'f_b_pg_a_n', 'h'),
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .pagenav .current', 'p_pg_a_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .pagenav .current', 'p_pg_a_n', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .pagenav .current', 'm_pg_a_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .pagenav .current', 'm_pg_a_n', 'h')
				)
				)
			)),
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .pagenav .current', 'b_pg_a_n')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .pagenav .current', 'b_pg_a_n', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .pagenav .current', 'r_c_pg_a_n')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .pagenav .current', 'r_c_pg_a_n', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav .current', 'sh_pg_a_n')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .pagenav .current', 'sh_pg_a_n', 'h')
					)
				)
			))
		))
	);

	$pt_filter = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .post-filter li a', 'b_c_pt_f', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .post-filter li a', 'b_c_pt_f', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .post-filter li a', 'f_f_pt_f'),
					self::get_color(' .post-filter li a', 'f_c_pt_f'),
					self::get_font_size(' .post-filter li a', 'f_s_pt_f'),
					self::get_line_height(' .post-filter li a', 'l_h_pt_f'),
					self::get_letter_spacing(' .post-filter li a', 'l_s_pt_f'),
					self::get_font_style(' .post-filter li a', 'f_st_pt_f', 'f_b_pt_f'),
					self::get_text_align(' .post-filter li a','t_a_pt_f'),
					self::get_text_shadow(' .post-filter li a','t_sh_pt_f'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .post-filter li a', 'f_f_pt_f', 'h'),
					self::get_color(' .post-filter li a', 'f_c_pt_f','h'),
					self::get_font_size(' .post-filter li a', 'f_s_pt_f', '', 'h'),
					self::get_font_style(' .post-filter li a', 'f_st_pt_f', 'f_b_pt_f', 'h'),
					self::get_text_shadow(' .more-link','t_sh_pt_f','h'),
				)
				)
			))
		))
	);

	$pt_filter = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .post-filter li a', 'b_c_pt_f', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .post-filter li a', 'b_c_pt_f', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .post-filter li a', 'f_f_pt_f'),
					self::get_color(' .post-filter li a', 'f_c_pt_f'),
					self::get_font_size(' .post-filter li a', 'f_s_pt_f'),
					self::get_line_height(' .post-filter li a', 'l_h_pt_f'),
					self::get_letter_spacing(' .post-filter li a', 'l_s_pt_f'),
					self::get_font_style(' .post-filter li a', 'f_st_pt_f', 'f_b_pt_f'),
					self::get_text_align(' .post-filter','t_a_pt_f'),
					self::get_text_shadow(' .post-filter li a','t_sh_pt_f'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .post-filter li a', 'f_f_pt_f', 'h'),
					self::get_color(' .post-filter li a:hover', 'f_c_pt_f_h',null,null,'h'),
					self::get_font_size(' .post-filter li a', 'f_s_pt_f', '', 'h'),
					self::get_font_style(' .post-filter li a', 'f_st_pt_f', 'f_b_pt_f', 'h'),
					self::get_text_shadow(' .post-filter li a','t_sh_pt_f','h'),
				)
				)
			))
		)),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .post-filter li a', 'p_pt_f')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .post-filter li a', 'p_pt_f', 'h')
				)
				)
			))
		)),
		// Margin
		self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' .post-filter li a', 'm_pt_f')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' .post-filter li a', 'm_pt_f', 'h')
				)
				)
			)),
		)),
		// Border
		self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' .post-filter li a', 'b_pt_f')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' .post-filter li a', 'b_pt_f', 'h')
				)
				)
			))
		)),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .post-filter li a', 'r_c_pt_f')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .post-filter li a', 'r_c_pt_f', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .post-filter li a', 'sh_pt_f')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .post-filter li a', 'sh_pt_f', 'h')
					)
				)
			))
		))
	);

	$pta_filter = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .post-filter li.active a', 'b_c_pta_f', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .post-filter li.active a', 'b_c_pta_f', 'bg_c', 'background-color', 'h')
				)
				)
			))
		)),
		// Font
		self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .post-filter li.active a', 'f_f_pta_f'),
					self::get_color(' .post-filter li.active a', 'f_c_pta_f'),
					self::get_font_size(' .post-filter li.active a', 'f_s_pta_f'),
					self::get_line_height(' .post-filter li.active a', 'l_h_pta_f'),
					self::get_letter_spacing(' .post-filter li.active a', 'l_s_pta_f'),
					self::get_font_style(' .post-filter li.active a', 'f_st_pta_f', 'f_b_pta_f'),
					self::get_text_shadow(' .post-filter li.active a','t_sh_pta_f'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .post-filter li.active a', 'f_f_pta_f', 'h'),
					self::get_color(' .post-filter li.active a:hover', 'f_c_pta_f_h',null,null,'h'),
					self::get_font_size(' .post-filter li.active a', 'f_s_pta_f', '', 'h'),
					self::get_font_style(' .post-filter li.active a', 'f_st_pta_f', 'f_b_pta_f', 'h'),
					self::get_text_shadow(' .post-filter li.active a','t_sh_pta_f','h'),
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
		    'label' => __('Container', 'themify'),
		    'options' => $portfolio_container
		),
		't' => array(
		    'label' => __('Title', 'themify'),
		    'options' => $portfolio_title
		),
		'fi' => array(
			'label' => __('Featured Image', 'themify'),
			'options' => $featured_image
		),
		'm' => array(
		    'label' => __('Meta', 'themify'),
		    'options' => $portfolio_meta
		),
		'd' => array(
		    'label' => __('Date', 'themify'),
		    'options' => $portfolio_date
		),
		'c' => array(
		    'label' => __('Content', 'themify'),
		    'options' => $portfolio_content
		),
		'r' => array(
			'label' => __('Read More', 'themify'),
			'options' => $read_more
		),
		'pg_c' => array(
			'label' => __('Pagination Container', 'themify'),
			'options' => $pg_container
		),
		'pg_n' => array(
			'label' => __('Pagination Numbers', 'themify'),
			'options' => $pg_numbers
		),
		'pg_a_n' => array(
			'label' => __('Pagination Active', 'themify'),
			'options' => $pg_a_numbers
		),
		'p_f' => array(
			'label' => __('Post Filter', 'themify'),
			'options' => $pt_filter
		),
		'p_f_a' => array(
			'label' => __('Post Filter Active', 'themify'),
			'options' => $pta_filter
		)
	    )
	);
    }

    function get_metabox() {
	/** Portfolio Meta Box Options */
	return array(
	    // Featured Image Size
	    Themify_Builder_Model::$featured_image_size,
	    // Image Width
	    Themify_Builder_Model::$image_width,
	    // Image Height
	    Themify_Builder_Model::$image_height,
	    // Hide Title
	    array(
		'name' => 'hide_post_title',
		'title' => __('Hide Post Title', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // Unlink Post Title
	    array(
		'name' => 'unlink_post_title',
		'title' => __('Unlink Post Title', 'themify'),
		'description' => __('Unlink post title (it will display the post title without link)', 'themify'),
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // Hide Post Date
	    array(
		'name' => 'hide_post_date',
		'title' => __('Hide Post Date', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // Hide Post Meta
	    array(
		'name' => 'hide_post_meta',
		'title' => __('Hide Post Meta', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // Hide Post Image
	    array(
		'name' => 'hide_post_image',
		'title' => __('Hide Featured Image', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // Unlink Post Image
	    array(
		'name' => 'unlink_post_image',
		'title' => __('Unlink Featured Image', 'themify'),
		'description' => __('Display the Featured Image without link', 'themify'),
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		)
	    ),
	    // External Link
	    Themify_Builder_Model::$external_link,
	    // Lightbox Link
	    Themify_Builder_Model::$lightbox_link
	);
    }

    function do_shortcode($atts) {

	$atts=shortcode_atts(array(
	    'id' => '',
	    'title' => 'yes',
	    'unlink_title' => 'no',
	    'image' => 'yes', // no
	    'image_w' => '',
	    'image_h' => '',
	    'display' => 'none', // excerpt, content
	    'post_meta' => 'yes', // yes
	    'post_date' => 'yes', // yes
	    'more_link' => false, // true goes to post type archive, and admits custom link
	    'more_text' => __('More &rarr;', 'themify'),
	    'limit' => 4,
	    'category' => 0, // integer category ID
	    'order' => 'DESC', // ASC
	    'orderby' => 'date', // title, rand
	    'style' => '', // grid3, grid2
	    'sorting' => 'no', // yes
	    'page_nav' => 'no', // yes
	    'paged' => '0', // internal use for pagination, dev: previously was 1
	    // slider parameters
	    'autoplay' => '',
	    'effect' => '',
	    'timeout' => '',
	    'speed' => ''
			), $atts);

	$module = array(
	    'module_ID' => $this->slug . '-' . rand(0, 10000),
	    'mod_name' => $this->slug,
	    'mod_settings' => array(
                'mod_title_portfolio' => '',
                'layout_portfolio' => $atts['style'],
                'category_portfolio' => $atts['category'],
                'post_per_page_portfolio' => $atts['limit'],
                'offset_portfolio' => '',
                'order_portfolio' => $atts['order'],
                'orderby_portfolio' => $atts['orderby'],
                'display_portfolio' => $atts['display'],
                'hide_feat_img_portfolio' => $atts['image'] === 'yes' ? 'no' : 'yes',
                'image_size_portfolio' => '',
                'img_width_portfolio' => $atts['image_w'],
                'img_height_portfolio' => $atts['image_h'],
                'unlink_feat_img_portfolio' => 'no',
                'hide_post_title_portfolio' => $atts['title'] === 'yes' ? 'no' : 'yes',
                'unlink_post_title_portfolio' => $atts['unlink_title'],
                'hide_post_date_portfolio' => $atts['post_date'] === 'yes' ? 'no' : 'yes',
                'hide_post_meta_portfolio' => $atts['post_meta'] === 'yes' ? 'no' : 'yes',
                'hide_page_nav_portfolio' => $atts['page_nav'] === 'no' ? 'yes' : 'no',
                'animation_effect' => '',
                'css_portfolio' => ''
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

 new TB_Portfolio_Module();//deprecated
