<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Post
 * Description: Display Posts
 */

class TB_Post_Module extends Themify_Builder_Component_Module {

    protected static $post_filter;

    public function __construct() {
	parent::__construct('post');
    }
    
    public function get_name(){
        return __('Post', 'themify');
    }
    
    public function get_icon(){
	return 'layers';
    }

    public function get_title($module) {
	$type = isset($module['mod_settings']['type_query_post']) ? $module['mod_settings']['type_query_post'] : 'category';
	$category = isset($module['mod_settings']['category_post']) ? $module['mod_settings']['category_post'] : '';
	$slug_query = isset($module['mod_settings']['query_slug_post']) ? $module['mod_settings']['query_slug_post'] : '';

	if ('category' === $type) {
	    return sprintf('%s : %s', __('Category', 'themify'), $category);
	} else {
	    return sprintf('%s : %s', __('Slugs', 'themify'), $slug_query);
	}
    }
	public function get_assets() {
		return array(
			'css'=>1
		);
	}

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_post',
		'type' => 'title'
	    ),
		array(
			'type' => 'query_posts',
			'id' => 'post_type_post',
			'tax_id'=>'type_query_post',
			'term_id'=>'#tmp_id#_post',//backward compatibility
			'slug_id'=>'query_slug_post',
			'sticky_id' => 'sticky_post',
			'query_filter' => true,
		),
	    array(
		'id' => 'layout_post',
		'type' => 'layout',
		'label' => __('Post Layout', 'themify'),
		'mode' => 'sprite',
		'control'=>array(
		    'classSelector'=>'.builder-posts-wrap'
		),
		'options' => array(
		    array('img' => 'list_post', 'value' => 'list-post', 'label' => __('List Post', 'themify')),
		    array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
		    array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
		    array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
			array('img' => 'grid5', 'value' => 'grid5', 'label' => __('Grid 5', 'themify')),
			array('img' => 'grid6', 'value' => 'grid6', 'label' => __('Grid 6', 'themify')),
		    array('img' => 'list_thumb_image', 'value' => 'list-thumb-image', 'label' => __('List Thumb Image', 'themify')),
		    array('img' => 'grid2_thumb', 'value' => 'grid2-thumb', 'label' => __('Grid 2 Thumb', 'themify'))
		)
	    ),
	    array(
		'id' => 'post_per_page_post',
		'type' => 'number',
		'label' => __('Number of Posts', 'themify'),
		'help' => __('Enter the number of posts to display.', 'themify')
	    ),
	    array(
		'id' => 'offset_post',
		'type' => 'number',
		'label' => __('Offset', 'themify'),
		'help' => __('Enter number of posts to pass over (skip).', 'themify')
	    ),
	    array(
		'id' => 'order_post',
		'type' => 'select',
		'label' => __('Order', 'themify'),
		'help' => __('Sort posts in ascending or descending order.', 'themify'),
		'order' =>true
	    ),
	    array(
		'id' => 'orderby_post',
		'type' => 'orderby_post',
		'label' => __('Order By', 'themify'),
		'orderBy'=>true,
		'binding' => array(
		    'select' => array('hide' => 'meta_order'),
		    'meta_value' => array('show' =>'meta_order'),
		)
	    ),
		array(
			'id' => 'meta_order',
			'type' => 'multi',
			'label' => '',
			'options' => array(
				array(
					'id' => 'meta_key_post',
					'type' => 'text',
					'label' => __('Custom Field Key', 'themify'),
					'wrap_class' => 'tb_disable_dc'
				),
				array(
					'id' => 'meta_key_type',
					'type' => 'select',
					'label' => __('Type', 'themify'),
					'options' => [
						'CHAR' => __( 'Character', 'themify' ),
						'NUMERIC' => __( 'Numeric', 'themify' ),
						'DATETIME' => __( 'Datetime', 'themify' ),
						'DATE' => __( 'Date', 'themify' ),
						'TIME' => __( 'Time', 'themify' ),
					]
				),
			),
		),
	    array(
            'id' => 'display_post',
            'type' => 'select',
            'label' => __('Display', 'themify'),
            'options' => array(
                'content' => __('Content', 'themify'),
                'excerpt' => __('Excerpt', 'themify'),
                'none' => __('None', 'themify')
            ),
            'binding' => array(
                    'excerpt' => array('show' => 'excerpt_length_post' ),
                    'content' => array('hide' => 'excerpt_length_post' ),
                    'none' => array('hide' => 'excerpt_length_post' )
                )
	    ),
        array(
            'id' => 'excerpt_length_post',
            'type' => 'number',
            'label' => __('Excerpt Length', 'themify'),
            'help' => __('Enter excerpt length word count (optional)', 'themify')
        ),
	    array(
		'id' => 'hide_feat_img_post',
		'type' => 'toggle_switch',
		'label' => __('Featured Image', 'themify'),
		'binding' => array(
			'checked' => array(
				'show' => array('image_size_post', 'img_width_post','auto_fullwidth_post','img_height_post','unlink_feat_img_post')
			),
			'not_checked' => array(
				'hide' => array('image_size_post', 'img_width_post','auto_fullwidth_post','img_height_post','unlink_feat_img_post')
			)
		)
	    ),
	    array(
		'id' => 'image_size_post',
		'type' => 'select',
		'label' => __('Image Size', 'themify'),
		'hide' => !Themify_Builder_Model::is_img_php_disabled(),
		'image_size' => true
	    ),
	    array(
		'id' => 'img_width_post',
		'type' => 'number',
		'label' => __('Image Width', 'themify')
	    ),
		array(
			'id' => 'auto_fullwidth_post',
			'type' => 'checkbox',
			'label' => '',
			'options' => array(array('name' => '1', 'value' => __('Auto fullwidth image', 'themify'))),
			'wrap_class' => 'auto_fullwidth'
		),
	    array(
		'id' => 'img_height_post',
		'type' => 'number',
		'label' => __('Image Height', 'themify')
	    ),
	    array(
		'id' => 'unlink_feat_img_post',
		'type' => 'toggle_switch',
		'label' => __('Unlink Featured Image', 'themify'),
		'options' => 'simple'
	    ),
	    array(
		'id' => 'hide_post_title_post',
		'type' => 'toggle_switch',
		'label' => __('Post Title', 'themify'),
		'binding' => array(
		    'checked' => array(
			    'show' => array('unlink_post_title_post','title_tag_post')
		    ),
		    'not_checked' => array(
			    'hide' =>array('unlink_post_title_post','title_tag_post')
		    )
		)
	    ),
        array(
            'id' => 'title_tag_post',
            'type' => 'select',
            'label' => __('Title HTML Tag', 'themify'),
            'h_tags' => true,
            'default' => 'h2'
        ),
	    array(
		'id' => 'unlink_post_title_post',
		'type' => 'toggle_switch',
		'label' => __('Unlink Post Title', 'themify'),
		'options' => 'simple'
	    ),
	    array(
		'id' => 'hide_post_date_post',
		'type' => 'toggle_switch',
		'label' => __('Post Date', 'themify'),
	    ),
	    array(
            'id' => 'hide_post_meta_post',
            'type' => 'toggle_switch',
            'label' => __('Post Meta', 'themify'),
            'binding' => array(
                'checked' => array(
                    'show' => array('hide_author_post','hide_category_post','hide_comment_post')
                ),
                'not_checked' => array(
                    'hide' =>array('hide_author_post','hide_category_post','hide_comment_post')
                )
            )
	    ),
        array(
            'id' => 'hide_author_post',
            'type' => 'checkbox',
            'label' => '',
            'options' => array(array('name' => 'yes', 'value' => __('Hide Author', 'themify')))
        ),
        array(
            'id' => 'hide_category_post',
            'type' => 'checkbox',
            'label' => '',
            'options' => array(array('name' => 'yes', 'value' => __('Hide Category', 'themify')))
        ),
        array(
            'id' => 'hide_comment_post',
            'type' => 'checkbox',
            'label' => '',
            'options' => array(array('name' => 'yes', 'value' => __('Hide Comment', 'themify')))
        ),
	    array(
		'id' => 'hide_page_nav_post',
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
					'label' => __( 'No Posts Message', 'themify' ),
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
			array( 'type' => 'custom_css_id', 'custom_css' => 'css_post' ),
	);
    }

	public static function get_hooks() {
		return array(
			'themify_post_start_module' => 'post_start',
			'themify_before_post_image_module' => 'before_post_image',
			'themify_after_post_image_module' => 'after_post_image',
			'themify_before_post_title_module' => 'before_post_title',
			'themify_after_post_title_module' => 'after_post_title',
			'themify_before_post_content_module' => 'before_post_content',
			'themify_after_post_content_module' => 'after_post_content',
			'themify_post_end_module' => 'post_end',
		);
	}

    public function get_live_default() {
	return array(
	    'layout_post' => 'grid4',
	    'post_per_page_post' => 4,
	    'display_post' => 'excerpt',
	    'hide_page_nav_post'=>'yes',
	    'post_type_post' => 'post',
	);
    }


    public function get_styling() {

	$general = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('', 'background_color_general', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('', 'b_c_g', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('', 'font_family_general'),
			    self::get_color_type(array(' span', ' a:not(.post-edit-link)', ' p')),
			    self::get_font_size('', 'font_size_general'),
			    self::get_line_height('', 'line_height_general'),
			    self::get_letter_spacing(' .post'),
			    self::get_text_align(' .post', 'text_align_general'),
			    self::get_text_transform('', 'text_transform_general'),
			    self::get_font_style('', 'font_general', 'font_bold'),
				self::get_text_shadow(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('', 'f_f_g', 'h'),
			    self::get_color_type(array(' span', ' a:not(.post-edit-link)', ' p'), 'h'),
			    self::get_font_size('', 'f_s_g', '', 'h'),
			    self::get_font_style('', 'f_g', 'f_b', 'h'),
				self::get_text_shadow('','t_sh','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('', 'general_padding')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('', 'g_p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('', 'general_margin')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('', 'g_m', 'h')
			)
		    )
		)),
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border('', 'general_border')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border('', 'g_b', 'h')
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

	$post_container = array(
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

	$post_title = array(
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('.module .post-title', '.module .post-title a'), 'font_family_title'),
			    self::get_color(array('.module .post-title', '.module .post-title a'), 'font_color_title'),
			    self::get_font_size(array('.module .post-title', '.module .post-title a'), 'font_size_title'),
				self::get_line_height('.module .post-title', 'line_height_title'),
				self::get_letter_spacing('.module .post-title', 'letter_spacing_title'),
			    self::get_text_transform('.module .post-title', 'text_transform_title'),
			    self::get_font_style(array('.module .post-title', '.module .post-title a'), 'font_style_title', 'font_weight_title'),
			    self::get_text_decoration('.module .post-title', 'text_decoration_regular_title'),
				self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array('.module .post-title', '.module .post-title a'), 'f_f_t', 'h'),
			    self::get_color(array('.module .post-title', '.module .post-title a'), 'font_color_title', null, null, 'hover'),
			    self::get_font_size(array('.module .post-title', '.module .post-title a'), 'f_s_t', '', 'h'),
			    self::get_font_style(array('.module .post-title', '.module .post-title a'), 'f_st_t', 'f_w_t', 'h'),
			    self::get_text_decoration('.module .post-title', 't_d_r_t', 'h'),
				self::get_text_shadow(array('.module .post-title', '.module .post-title a'), 't_sh_t','h'),
			)
		    )
		))
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


	$post_meta = array(
	    // Font
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'font_family_meta'),
			self::get_color(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'font_color_meta'),
			self::get_font_size(array(' .post-content .post-meta', ' .post-date'), 'font_size_meta'),
			self::get_font_style(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'f_fs_m', 'f_fw_m'),
			self::get_line_height(array(' .post-content .post-meta', ' .post-date'), 'line_height_meta'),
			self::get_text_decoration(array(' .post-content .post-meta', ' .post-date'), 't_d_m'),
			self::get_text_shadow(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 't_sh_m'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'f_f_m', 'h'),
			self::get_color(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'font_color_meta',null,null,'hover'),
			self::get_font_size(array(' .post-content .post-meta', ' .post-date'), 'f_s_m', '', 'h'),
			self::get_font_style(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 'f_fs_m', 'f_fw_m', 'h'),
			self::get_text_decoration(array(' .post-content .post-meta', ' .post-date'), 't_d_m', 'h'),
			self::get_text_shadow(array(' .post-content .post-meta', ' .post-content .post-meta a', ' .post-date'), 't_sh_m','h'),
		    )
		)
	    ))
	);

	$post_date = array(
		// Background
		self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .post .post-date', 'pd_b_c', 'bg_c', 'background-color'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .post .post-date', 'pd_b_c', 'bg_c', 'background-color', 'h'),
				)
				)
			))
		)),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array(' .post .post-date', ' .post .post-date a'), 'font_family_date'),
			    self::get_color(array(' .post .post-date', ' .post .post-date a', ' .post .post-date span'), 'font_color_date'),
			    self::get_font_size('.module .post .post-date', 'font_size_date'),
			    self::get_font_style(array(' .post .post-date', ' .post .post-date a'), 'f_fs_d', 'f_fw_d'),
			    self::get_line_height('.module .post .post-date', 'line_height_date'),
				self::get_text_shadow(array(' .post .post-date', ' .post .post-date a'), 't_sh_d'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(' .post .post-date', ' .post .post-date a'), 'f_f_d', 'h'),
			    self::get_color(array(' .post .post-date', ' .post .post-date a', ' .post .post-date span'), 'f_c_d',null,null,'h'),
			    self::get_font_size('.module .post .post-date', 'f_s_d', '', 'h'),
				self::get_font_style(array(' .post .post-date', ' .post .post-date a'), 'f_fs_d', 'f_fw_d', 'h'),
				self::get_text_shadow(array(' .post .post-date', ' .post .post-date a'), 't_sh_d','h'),
			)
		    )
		))
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

	$post_content = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .post-content .entry-content', 'background_color_content', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .post-content .entry-content', 'b_c_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .post-content .entry-content', 'font_family_content'),
			    self::get_color(array(' .post-content .entry-content', ' .post-content .entry-content p'), 'font_color_content'),
			    self::get_font_size(' .post-content .entry-content', 'font_size_content'),
			    self::get_font_style(' .post-content .entry-content', 'f_fs_c', 'f_fw_c'),
			    self::get_line_height(' .post-content .entry-content', 'line_height_content'),
			    self::get_text_align(' .post-content .entry-content', 't_a_c'),
				self::get_text_shadow(' .post-content .entry-content', 't_sh_c'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .post-content .entry-content', 'f_f_c','h'),
			    self::get_color(array(' .post-content .entry-content', ' .post-content .entry-content p'), 'f_c_c', null,null, 'h'),
			    self::get_font_size(' .post-content .entry-content', 'f_s_c', '', 'h'),
				self::get_font_style(' .post-content .entry-content', 'f_fs_c', 'f_fw_c', 'h'),
				self::get_text_shadow(' .post-content .entry-content', 't_sh_c','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .post-content .entry-content', 'c_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .post-content .entry-content', 'c_p', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .post-content .entry-content', 'c_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .post-content .entry-content', 'c_m', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .post-content .entry-content', 'c_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .post-content .entry-content', 'c_b', 'h')
			)
		    )
		))
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
			)
		),
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
			)
		)
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

	$load_more_button = array(
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .load-more-button', 'b_c_l_m_b', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			   self::get_color(' .load-more-button', 'b_c_l_m_b', 'bg_c', 'background-color','h')
			)
		    )
		))
	    )),
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .load-more-button', 'f_f_l_m_b'),
			    self::get_color(' .load-more-button', 'f_c_l_m_b'),
			    self::get_font_size(' .load-more-button', 'f_s_l_m_b'),
			    self::get_font_style(' .load-more-button', 'f_st_l_m_b', 'f_fw_l_m_b'),
				self::get_line_height(' .load-more-button', 'l_h_l_m_b'),
				self::get_letter_spacing(' .load-more-button', 'l_s_l_m_b'),
				self::get_text_transform(' .load-more-button', 't_t_l_m_b'),
			    self::get_text_align(' .tf_load_more', 't_a_l_m_b'),
				self::get_text_shadow(' .load-more-button', 't_sh_l_b'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .load-more-button', 'f_f_l_m_b','h'),
			    self::get_color(' .load-more-button', 'f_c_l_m_b',null,null,'h'),
			    self::get_font_size(' .load-more-button', 'f_s_l_m_b','','h'),
				self::get_font_style(' .load-more-button', 'f_st_l_m_b', 'f_fw_l_m_b', 'h'),
				self::get_text_shadow(' .load-more-button', 't_sh_l_b','h'),
			)
		    )
		))
	    )),
		// Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .load-more-button', 'l_m_b_p')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .load-more-button', 'l_m_b_p','h'),
			)
		    )
		))
		)),
		// Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .load-more-button', 'l_m_b_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .load-more-button', 'l_m_b_m','h')
			)
		    )
		))
	    )),
		// Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .load-more-button', 'l_m_b_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .load-more-button', 'l_m_b_b','h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .load-more-button', 'l_m_b_r_c')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .load-more-button', 'l_m_b_r_c', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .load-more-button', 'l_m_b_b_sh')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .load-more-button', 'l_m_b_b_sh', 'h')
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
		'co' => array(
		    'label' => __('Container', 'themify'),
		    'options' => $post_container
		),
		't' => array(
		    'label' => __('Title', 'themify'),
		    'options' => $post_title
		),
		'f' => array(
		    'label' => __('Featured Image', 'themify'),
		    'options' => $featured_image
		),
		'm' => array(
		    'label' => __('Meta', 'themify'),
		    'options' => $post_meta
		),
		'd' => array(
		    'label' => __('Date', 'themify'),
		    'options' => $post_date
		),
		'c' => array(
		    'label' => __('Content', 'themify'),
		    'options' => $post_content
		),
		'r' => array(
			'label' => __('Read More', 'themify'),
			'options' => $read_more
		),
		'l_m_b' => array(
		    'label' => __('Load More Button', 'themify'),
		    'options' => $load_more_button
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

new TB_Post_Module();

