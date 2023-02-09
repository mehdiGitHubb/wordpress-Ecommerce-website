<?php

/**
 * Page Meta Box Options
 * @var array Options for Themify Custom Panel
 * @since 1.0.0
 */
if (!function_exists('themify_theme_page_meta_box')) {

    function themify_theme_page_meta_box() {
	return array(
	    // Page Layout
	    array(
		'name' => 'page_layout',
		'title' => __('Page Layout', 'themify'),
		'description' => '',
		'type' => 'page_layout',
		'show_title' => true,
		'meta' => array(
		    array('value' => 'default', 'img' => 'themify/img/default.svg', 'selected' => true, 'title' => __('Default', 'themify')),
		    array('value' => 'full_width', 'img' => 'themify/img/fullwidth.svg', 'title' => __('Fullwidth (Builder Page)', 'themify')),
		    array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
		    array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
		    array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify')),
		),
		'default' => 'default',
	    ),
		array(
			'name' => 'content_width',
			'type' => 'hidden',
		),
	    // Stiky Sidebar
	    array(
		'name' => 'post_sticky_sidebar',
		'title' => __('Sticky Sidebar', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'show_title' => true,
		'meta' => array(
		    array('value' => '', 'name' => '', 'selected' => true),
		    array('value' => 1, 'name' => __('Enable', 'themify')),
		    array('value' => 0, 'name' => __('Disable', 'themify'))
		),
	    ),
	    // Sidebar Type
	    array(
		'name' => 'sidebar_type',
		'title' => '',
		'description' => '',
		'type' => 'radio',
		'meta' => array(
		    array('value' => 'main', 'name' => __('Display default sidebar', 'themify'), 'selected' => true),
		    array('value' => 'shop', 'name' => __('Display Shop sidebar', 'themify'),)
		),
		'display_callback' => 'themify_is_woocommerce_active',
		'enable_toggle' => true,
		'class' => 'hide-if sidebar-none default',
		'default' => 'main',
	    ),
	    // Hide page title
	    array(
		'name' => 'hide_page_title',
		'title' => __('Hide Page Title', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => array(
		    array('value' => 'default', 'name' => '', 'selected' => true),
		    array('value' => 'yes', 'name' => __('Yes', 'themify')),
		    array('value' => 'no', 'name' => __('No', 'themify'))
		),
		'default' => 'default'
	    ),
	    // Custom menu
	    array(
		'name' => 'custom_menu',
		'title' => __('Custom Menu', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => themify_get_available_menus()
	    ),
	);
    }

}

/**
 * Default Page Layout Module
 * @param array $data Theme settings data
 * @return string Markup for module.
 * @since 1.0.0
 */
function themify_default_page_layout($data = array()) {
    $data = themify_get_data();

    /**
     * Theme Settings Option Key Prefix
     * @var string
     */
    $prefix = 'setting-default_page_';

    /**
     * Sidebar placement options
     * @var array
     */
    $sidebar_location_options = themify_sidebar_location_options();

    /**
     * Tertiary options <blank>|yes|no
     * @var array
     */
    $default_options = array(
	array('name' => '', 'value' => ''),
	array('name' => __('Yes', 'themify'), 'value' => 'yes'),
	array('name' => __('No', 'themify'), 'value' => 'no')
    );

    /**
     * Module markup
     * @var string
     */
    $output = '';

    /**
     * Page sidebar placement
     */
    $output .= '<p>
					<span class="label">' . __('Page Sidebar Option', 'themify') . '</span>';
    $val = isset($data[$prefix . 'layout']) ? $data[$prefix . 'layout'] : '';
    foreach ($sidebar_location_options as $option) {
	if (( '' == $val || !$val || !isset($val) ) && ( isset($option['selected']) && $option['selected'] )) {
	    $val = $option['value'];
	}
	if ($val == $option['value']) {
	    $class = "selected";
	} else {
	    $class = "";
	}
	$output .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }
    $output .= '<input type="hidden" name="' . $prefix . 'layout" class="val" value="' . $val . '" /></p>';

    /**
     * Sticky Sidebar for All Pages
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=' . esc_attr($prefix) . 'layout]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="' . esc_attr($prefix) . 'sticky_sidebar">
							<input type="checkbox" id="' . esc_attr($prefix) . 'sticky_sidebar" name="' . esc_attr($prefix) . 'sticky_sidebar" value="1"
							' . checked(themify_get(esc_attr($prefix) . 'sticky_sidebar'), 1, false) . '
							/>' . __('Enable sticky sidebar', 'themify') . '
						</label>
					</p>';
    /**
     * Hide Title in All Pages
     */
    $output .= '<p>
					<span class="label">' . __('Hide Title in All Pages', 'themify') . '</span>
					<select name="setting-hide_page_title">' .
	    themify_options_module($default_options, 'setting-hide_page_title') . '
					</select>
				</p>';

    /**
     * Hide Feauted images in All Pages
     */
    $output .= '<p>
					<span class="label">' . __('Hide Featured Image', 'themify') . '</span>
					<select name="setting-hide_page_image">' .
	    themify_options_module($default_options, 'setting-hide_page_image') . '
					</select>
				</p>';

    /**
     * Page Comments
     */
    $pre = 'setting-comments_pages';
    $output .= '<p><span class="label">' . __('Page Comments', 'themify') . '</span><label for="' . $pre . '"><input type="checkbox" id="' . $pre . '" name="' . $pre . '" ' . checked(themify_get($pre), 'on', false) . ' /> ' . __('Disable comments in all Pages', 'themify') . '</label></p>';

    return $output;
}
/**
 * Default Custom Post Layout Module
 * @param array $data Theme settings data
 * @return string Markup for module.
 * @since 1.0.0
 */
if(!function_exists('themify_shoppe_custom_post_type_layouts')) {
	function themify_shoppe_custom_post_type_layouts($data = array()){
		$data = themify_get_data();

		/**
		 * Theme Settings Option Key Prefix
		 * @var string
		 */
		$prefix = 'setting-custom_post_';

		/**
		 * Module markup
		 * @var string
		*/

		$output = '';

		$custom_posts = null;

		$post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
        $excluded_types = apply_filters( 'themify_exclude_CPT_for_sidebar', array('post', 'page', 'attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section', 'portfolio'));
		if ( themify_is_woocommerce_active() ) {
			$excluded_types[] = 'product';
		}

		foreach ($post_types as $key => $value) {
			if (!in_array($key, $excluded_types)) {
				$custom_posts[$key] =  array( 'name' => $value->labels->singular_name, 'archive' => $value->has_archive );
			}
		}

		$custom_posts = apply_filters('themify_get_public_post_types', $custom_posts);

		/**
		 * Sidebar placement options
		 * @var array
		 */
		$sidebar_location_options = themify_sidebar_location_options();

		/**
		 * Page sidebar placement
		 */

		if(is_array($custom_posts)){
			foreach($custom_posts as $key => $cPost){
				$output .= sprintf('<h4>%s %s</h4>', $cPost['name'], __('Post Type', 'themify'));

				if ($cPost['archive']) {

					$output .= '<p>'. sprintf('<span class="label">%s %s</span>', $cPost['name'], __('Archive Sidebar', 'themify'));
					$val = isset( $data[$prefix.$key.'_archive'] ) ? $data[$prefix.$key.'_archive'] : '';

					foreach ( $sidebar_location_options as $option ) {
						if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
							$val = $option['value'];
						}
						if ( $val == $option['value'] ) {
							$class = "selected";
						} else {
							$class = "";
						}
						$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
					}

					$output .= '<input type="hidden" name="'.($prefix.$key).'_archive" class="val" value="'.$val.'" /></p>';
					$output .= '<p class="pushlabel" data-show-if-element="[name=' . ($prefix.$key) . '_archive]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="'.esc_attr($prefix.$key).'_archive_post_sticky_sidebar">
							<input type="checkbox" id="'.esc_attr($prefix.$key).'_archive_post_sticky_sidebar" name="'.esc_attr($prefix.$key).'_archive_post_sticky_sidebar" value="1"
							'.checked( themify_get( esc_attr($prefix.$key).'_archive_post_sticky_sidebar' ),1, false ) .'
							/>'.__('Enable sticky sidebar', 'themify').'
						</label>
					</p>';
				}

				$output .= '<p>'. sprintf('<span class="label">%s %s</span>', ucfirst($cPost['name']), __('Single Sidebar', 'themify'));
				$val = isset( $data[$prefix.$key.'_single'] ) ? $data[$prefix.$key.'_single'] : '';

				foreach ( $sidebar_location_options as $option ) {
					if ( ( '' == $val || ! $val || ! isset( $val ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
						$val = $option['value'];
					}
					if ( $val == $option['value'] ) {
						$class = "selected";
					} else {
						$class = "";
					}
					$output .= '<a href="#" class="preview-icon '.$class.'" title="'.$option['title'].'"><img src="'.THEME_URI.'/'.$option['img'].'" alt="'.$option['value'].'"  /></a>';
				}
				$output .= '<input type="hidden" name="'.($prefix.$key).'_single" class="val" value="'.$val.'" /></p>';
				$output .= '<p class="pushlabel" data-show-if-element="[name=' . ($prefix.$key) . '_single]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="'.esc_attr($prefix.$key).'_single_post_sticky_sidebar">
							<input type="checkbox" id="'.esc_attr($prefix.$key).'_single_post_sticky_sidebar" name="'.esc_attr($prefix.$key).'_single_post_sticky_sidebar" value="1"
							'.checked( themify_get( esc_attr($prefix.$key).'_single_post_sticky_sidebar' ),1, false ) .'
							/>'.__('Enable sticky sidebar', 'themify').'
						</label>
					</p>';

			}
		}

		return $output;
	}
}

// Query Post Meta Box Options
function themify_theme_query_post_meta_box() {
    return array(
	// Notice
	array(
	    'name' => '_query_posts_notice',
	    'title' => '',
	    'description' => '',
	    'type' => 'separator',
	    'meta' => array(
		'html' => '<div class="themify-info-link">' . sprintf(__('<a href="%s">Query Posts</a> allows you to query WordPress posts from any category on the page. To use it, select a Query Category.', 'themify'), 'https://themify.me/docs/query-posts') . '</div>'
	    ),
	),
	// Query Category
	array(
	    'name' => 'query_category',
	    'title' => __('Query Category', 'themify'),
	    'description' => __('Select a category or enter multiple category IDs (eg. 2,5,6). Enter 0 to display all category.', 'themify'),
	    'type' => 'query_category',
	    'meta' => array()
	),
	// Query All Post Types
	array(
	    'name' => 'query_all_post_types',
	    'type' => 'dropdown',
	    'title' => __('Query All Post Types', 'themify'),
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		),
		array(
		    'value' => 'yes',
		    'name' => 'Yes',
		),
		array(
		    'value' => 'no',
		    'name' => 'No',
		),
	    )
	),
	// Descending or Ascending Order for Posts
	array(
	    'name' => 'order',
	    'title' => __('Order', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
		array('name' => __('Ascending', 'themify'), 'value' => 'asc')
	    ),
	    'default' => 'desc'
	),
	// Criteria to Order By
	array(
	    'name' => 'orderby',
	    'title' => __('Order By', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('Date', 'themify'), 'value' => 'date', 'selected' => true),
		array('name' => __('Random', 'themify'), 'value' => 'rand'),
		array('name' => __('Author', 'themify'), 'value' => 'author'),
		array('name' => __('Post Title', 'themify'), 'value' => 'title'),
		array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
		array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
		array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
		array('name' => __('Post ID', 'themify'), 'value' => 'ID'),
		array('name' => __('Custom Field String', 'themify'), 'value' => 'meta_value'),
		array('name' => __('Custom Field Numeric', 'themify'), 'value' => 'meta_value_num')
	    ),
	    'default' => 'date',
	    'hide' => 'date|rand|author|title|comment_count|modified|name|ID field-meta-key'
	),
	array(
	    'name' => 'meta_key',
	    'title' => __('Custom Field Key', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'medium'),
	    'class' => 'field-meta-key'
	),
	// Post Layout
	array(
	    'name' => 'layout',
	    'title' => __('Query Post Layout', 'themify'),
	    'description' => '',
	    'type' => 'layout',
	    'show_title' => true,
	    'meta' => array(
		array(
		    'value' => 'list-post',
		    'img' => 'images/layout-icons/list-post.png',
		    'selected' => true
		),
		array(
		    'value' => 'grid2',
		    'img' => 'images/layout-icons/grid2.png',
		    'title' => __('Grid 2', 'themify')
		),
		array(
		    'value' => 'grid3',
		    'img' => 'images/layout-icons/grid3.png',
		    'title' => __('Grid 3', 'themify')
		),
		array(
		    'value' => 'grid4',
		    'img' => 'images/layout-icons/grid4.png',
		    'title' => __('Grid 4', 'themify')
		),
		array(
			'value' => 'grid5', 
			'img' => 'images/layout-icons/grid5.png',
			'title' => __('Grid 5', 'themify')
		),
		array(
			'value' => 'grid6',
			'img' => 'images/layout-icons/grid6.png',
			'title' => __('Grid 6', 'themify')
		),
		array(
		    'value' => 'list-large-image',
		    'img' => 'images/layout-icons/list-large-image.png',
		    'title' => __('List Large Image', 'themify')
		),
		array(
		    'value' => 'auto_tiles',
		    'img' => 'images/layout-icons/auto-tiles.png',
		    'title' => __('Tiles', 'themify')
		)
	    ),
	    'default' => 'list-post',
	),
	// Post Content Style
	array(
	    'name' => 'post_content_layout',
	    'title' => __('Post Content Style', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'overlay',
		    'name' => __('Overlay', 'themify'),
		),
		array(
		    'value' => 'polaroid',
		    'name' => __('Polaroid', 'themify'),
		),
		array(
		    'value' => 'boxed',
		    'name' => __('Boxed', 'themify'),
		),
		array(
		    'value' => 'flip',
		    'name' => __('Flip', 'themify'),
		)
	    )
	),
	// Post Masonry
	array(
	    'name' => 'post_masonry',
	    'title' => __('Post Masonry', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'yes',
		    'name' => __('Enable', 'themify'),
		),
		array(
		    'value' => 'no',
		    'name' => __('Disable', 'themify'),
		)
	    )
	),
	// Post Gutter
	array(
	    'name' => 'post_gutter',
	    'title' => __('Post Gutter', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'no-gutter',
		    'name' => __('No gutter', 'themify'),
		)
	    )
	),
	// Infinite Scroll
	array(
	    'name' => 'more_posts',
	    'title' => __('Infinite Scroll', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'infinite',
		    'name' => __('Enable', 'themify'),
		),
		array(
		    'value' => 'pagination',
		    'name' => __('Disable', 'themify'),
		)
	    )
	),
	// Posts Per Page
	array(
	    'name' => 'posts_per_page',
	    'title' => __('Posts Per Page', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'small')
	),
	// Display Content
	array(
	    'name' => 'display_content',
	    'title' => __('Display Content', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('Full Content', 'themify'), 'value' => 'content'),
		array('name' => __('Excerpt', 'themify'), 'value' => 'excerpt', 'selected' => true),
		array('name' => __('None', 'themify'), 'value' => 'none')
	    ),
	    'default' => 'excerpt',
	),
	// Featured Image Size
	array(
	    'name' => 'feature_size_page',
	    'title' => __('Image Size', 'themify'),
	    'description' => sprintf(__('Image sizes can be set at <a href="%s">Media Settings</a> and <a href="%s" target="_blank">Regenerated</a>', 'themify'), 'options-media.php', 'https://wordpress.org/plugins/regenerate-thumbnails/'),
	    'type' => 'featimgdropdown',
	    'display_callback' => 'themify_is_image_script_disabled'
	),
	// Multi field: Image Dimension
	themify_image_dimensions_field(),
	// Hide Title
	array(
	    'name' => 'hide_title',
	    'title' => __('Hide Post Title', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Unlink Post Title
	array(
	    'name' => 'unlink_title',
	    'title' => __('Unlink Post Title', 'themify'),
	    'description' => __('Unlink post title (it will display the post title without link)', 'themify'),
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Hide Post Date
	array(
	    'name' => 'hide_date',
	    'title' => __('Hide Post Date', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Hide Post Meta
	themify_multi_meta_field(),
	// Hide Post Image
	array(
	    'name' => 'hide_image',
	    'title' => __('Hide Featured Image', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Unlink Post Image
	array(
	    'name' => 'unlink_image',
	    'title' => __('Unlink Featured Image', 'themify'),
	    'description' => __('Display the Featured Image without link', 'themify'),
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Pagination Visibility
	array(
	    'name' => 'hide_navigation',
	    'title' => __('Hide Pagination', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
    );
}

/**
 * Query Product Options
 * @return array
 * @since 1.0.0
 */
function themify_theme_query_product_meta_box() {
    return array(
	// Query Category
	array(
	    'name' => 'product_query_category',
	    'title' => __('Query Category', 'themify'),
	    'description' => __('Select a category or enter multiple category IDs (eg. 2,5,6). Enter 0 to display all category.', 'themify'),
	    'type' => 'query_category',
	    'meta' => array('taxonomy' => 'product_cat')
	),
	array(
	    'name' => 'product_query_type',
	    'title' => __('Type', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('All', 'themify'), 'value' => 'all', 'selected' => true),
		array('name' => __('On Sale', 'themify'), 'value' => 'onsale'),
		array('name' => __('Free products', 'themify'), 'value' => 'free'),
		array('name' => __('Featured Products', 'themify'), 'value' => 'featured'),
	    )
	),
	// Descending or Ascending Order for Posts
	array(
	    'name' => 'product_order',
	    'title' => __('Order', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
		array('name' => __('Ascending', 'themify'), 'value' => 'asc')
	    ),
	    'default' => 'desc'
	),
	// Criteria to Order By
	array(
	    'name' => 'product_orderby',
	    'title' => __('Order By', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('name' => __('Date', 'themify'), 'value' => 'date', 'selected' => true),
		array('name' => __('Price', 'themify'), 'value' => 'price'),
		array('name' => __('Sales', 'themify'), 'value' => 'sales'),
		array('name' => __('Random', 'themify'), 'value' => 'rand'),
		array('name' => __('Author', 'themify'), 'value' => 'author'),
		array('name' => __('Post Title', 'themify'), 'value' => 'title'),
		array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
		array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
		array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
		array('name' => __('Post ID', 'themify'), 'value' => 'ID'),
		array('name' => __('Custom Field String', 'themify'), 'value' => 'meta_value'),
		array('name' => __('Custom Field Numeric', 'themify'), 'value' => 'meta_value_num')
	    ),
	    'default' => 'date',
	    'hide' => 'date|rand|author|title|comment_count|modified|name|ID field-product-meta-key'
	),
	array(
	    'name' => 'product_meta_key',
	    'title' => __('Custom Field Key', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'medium'),
	    'class' => 'field-product-meta-key'
	),
	// Posts Per Page
	array(
	    'name' => 'product_posts_per_page',
	    'title' => __('Products Per Page', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'small')
	),
	// Post Layout
	array(
	    'name' => 'product_layout',
	    'title' => __('Product Layout', 'themify'),
	    'description' => '',
	    'type' => 'layout',
	    'show_title' => true,
	    'meta' => array(
		array(
		    'value' => 'list-post',
		    'img' => 'images/layout-icons/list-post.png',
		    'selected' => true
		),
		array(
		    'value' => 'grid2',
		    'img' => 'images/layout-icons/grid2.png',
		    'title' => __('Grid 2', 'themify')
		),
		array(
		    'value' => 'grid3',
		    'img' => 'images/layout-icons/grid3.png',
		    'title' => __('Grid 3', 'themify')
		),
		array(
		    'value' => 'grid4',
		    'img' => 'images/layout-icons/grid4.png',
		    'title' => __('Grid 4', 'themify')
		),
		array(
			'value' => 'grid5', 
			'img' => 'images/layout-icons/grid5.png',
			'title' => __('Grid 5', 'themify')
		),
		array(
			'value' => 'grid6',
			'img' => 'images/layout-icons/grid6.png',
			'title' => __('Grid 6', 'themify')
		),
		array(
		    'value' => 'auto_tiles',
		    'img' => 'images/layout-icons/auto-tiles.png',
		    'title' => __('Tiles', 'themify')
		)
	    ),
	    'default' => 'list-post',
	),
	// Product Content Style
	array(
	    'name' => 'product_content_layout',
	    'title' => __('Product Content Style', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'overlay',
		    'name' => __('Overlay', 'themify'),
		),
		array(
		    'value' => 'polaroid',
		    'name' => __('Polaroid', 'themify'),
		),
		array(
		    'value' => 'boxed',
		    'name' => __('Boxed', 'themify'),
		),
		array(
		    'value' => 'flip',
		    'name' => __('Flip', 'themify'),
		)
	    )
	),
	// Post Masonry
	array(
	    'name' => 'product_masonry',
	    'title' => __('Masonry Layout', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'yes',
		    'name' => __('Enable', 'themify'),
		),
		array(
		    'value' => 'no',
		    'name' => __('Disable', 'themify'),
		)
	    )
	),
	// Product Slider Hover
	array(
	    'name' => 'product_slider_hover',
	    'title' => __('Product Hover Gallery', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'enable',
		    'name' => __('Enable', 'themify'),
		),
		array(
		    'value' => 'disable',
		    'name' => __('Disable', 'themify'),
		)
	    )
	),
	// Post Gutter
	array(
	    'name' => 'product_gutter',
	    'title' => __('Gutter Spacing', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'no-gutter',
		    'name' => __('No gutter', 'themify'),
		)
	    )
	),
	// Infinite Scroll
	array(
	    'name' => 'product_more_posts',
	    'title' => __('Infinite Scroll', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array(
		    'value' => '',
		    'name' => '',
		    'selected' => true,
		),
		array(
		    'value' => 'infinite',
		    'name' => __('Enable', 'themify'),
		),
		array(
		    'value' => 'pagination',
		    'name' => __('Disable', 'themify'),
		)
	    )
	),
	array(
	    'name' => 'product_archive_show_short',
	    'title' => __('Product Description', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'excerpt', 'name' => __('Short Description', 'themify'), 'selected' => true),
		array('value' => 'content', 'name' => __('Full Content', 'themify')),
		array('value' => 'none', 'name' => __('None', 'themify'))
	    )
	),
	// Multi field: Image Dimension
	themify_image_dimensions_field(array(), 'product_image'),
	// Featured Image Size
	array(
	    'name' => 'product_feature_size_page',
	    'title' => __('Image Size', 'themify'),
	    'description' => sprintf(__('Image sizes can be set at <a href="%s">Media Settings</a> and <a href="%s" target="_blank">Regenerated</a>', 'themify'), 'options-media.php', 'https://wordpress.org/plugins/regenerate-thumbnails/'),
	    'type' => 'featimgdropdown',
	    'display_callback' => 'themify_is_image_script_disabled'
	),
	array(
	    'name' => 'product_show_sorting_bar',
	    'title' => __('Show Sorting Bar', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => '', 'name' => '', 'selected' => true),
		array('value' => 'no', 'name' => __('No', 'themify')),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
	    )
	),
	// Pagination Visibility
	array(
	    'name' => 'product_hide_navigation',
	    'title' => __('Hide Pagination', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'no', 'name' => __('No', 'themify'), 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
	    )
	),
	// Hide Title
	array(
	    'name' => 'product_hide_title',
	    'title' => __('Hide Product Title', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Hide Price
	array(
	    'name' => 'product_hide_price',
	    'title' => __('Hide Product Price', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Hide Quick Look
	array(
	    'name' => 'product_quick_look',
	    'title' => __('Hide Quick Look', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	),
	// Hide Product Share
	array(
	    'name' => 'product_social_share',
	    'title' => __('Hide Product Share', 'themify'),
	    'description' => '',
	    'type' => 'dropdown',
	    'meta' => array(
		array('value' => 'default', 'name' => '', 'selected' => true),
		array('value' => 'yes', 'name' => __('Yes', 'themify')),
		array('value' => 'no', 'name' => __('No', 'themify'))
	    ),
	    'default' => 'default',
	)
    );
}

/**
 * Disable Query Posts feature if it's not already in use.
 *
 * @since 7.0.8
 * @return bool
 */
function themify_query_posts_check() {
    if ( themify_is_woocommerce_active() && wc_get_page_id('shop') == get_the_id() ) {
		return false;
	}

	return themify_get( 'query_category', '' ) !== '';
}

/**
 * Disable Query Products feature if it's not already in use.
 *
 * @since 7.0.8
 * @return bool
 */
function themify_query_product_check() {
	return themify_get( 'product_query_category', '' ) !== '';
}

if (!function_exists('themify_theme_get_page_metaboxes')) {

    function themify_theme_get_page_metaboxes(array $args, &$meta_boxes) {
	return array(
	    array(
		'name' => __('Page Options', 'themify'),
		'id' => 'page-options',
		'options' => themify_theme_page_meta_box(),
		'pages' => 'page'
	    ),
	    array(
		'name' => __('Query Posts', 'themify'),
		'id' => 'query-posts',
		'options' => themify_theme_query_post_meta_box(),
		'display_callback' => 'themify_query_posts_check',
		'pages' => 'page'
	    ),
	    array(
		'name' => __('Query Products', 'themify'),
		'id' => 'query-products',
		'options' => themify_theme_query_product_meta_box(),
		'display_callback' => 'themify_query_product_check',
		'pages' => 'page'
	    )
	);
    }

}
