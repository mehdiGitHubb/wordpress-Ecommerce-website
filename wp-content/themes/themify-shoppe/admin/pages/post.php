<?php

/**
 * Post Meta Box Options
 * @var array Options for Themify Custom Panel
 * @since 1.0.0
 */
if (!function_exists('themify_theme_post_meta_box')) {

    function themify_theme_post_meta_box() {
	return array(
	    // Layout
	    array(
		'name' => 'layout',
		'title' => __('Sidebar Option', 'themify'),
		'description' => '',
		'type' => 'page_layout',
		'show_title' => true,
		'meta' => array(
		    array('value' => 'default', 'img' => 'themify/img/default.svg', 'selected' => true, 'title' => __('Default', 'themify')),
		    array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
		    array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
		    array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify')),
		    array('value' => 'full_width', 'img' => 'themify/img/fullwidth.svg', 'title' => __('Fullwidth (Builder Page)', 'themify')),
		),
		'default' => 'default',
	    ),
		array(
			'name' => 'content_width',
			'type' => 'hidden',
		),
	    // Sticky Sidebar
	    array(
		'name' => 'post_sticky_sidebar',
		'title' => __('Sticky Sidebar', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'show_title' => true,
		'class' => 'hide-if sidebar-none',
		'meta' => array(
		    array('value' => '', 'name' => '', 'selected' => true),
		    array('value' => 1, 'name' => __('Enable', 'themify')),
		    array('value' => 0, 'name' => __('Disable', 'themify'))
		),
	    ),
	    // Featured Image Size
	    array(
		'name' => 'feature_size',
		'title' => __('Image Size', 'themify'),
		'description' => sprintf(__('Image sizes can be set at <a href="%s">Media Settings</a> and <a href="%s" target="_blank">Regenerated</a>', 'themify'), 'options-media.php', 'admin.php?page=regenerate-thumbnails'),
		'type' => 'featimgdropdown',
		'display_callback' => 'themify_is_image_script_disabled'
	    ),
	    // Multi field: Image Dimension
	    themify_image_dimensions_field(),
	    // Hide Post Title
	    array(
		'name' => 'hide_post_title',
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
		'name' => 'unlink_post_title',
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
	    // Multi field: Hide Post Meta
	    themify_multi_meta_field(),
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
		),
		'default' => 'default',
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
		),
		'default' => 'default',
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
		),
		'default' => 'default',
	    ),
	    // Video URL
	    array(
		'name' => 'video_url',
		'title' => __('Video URL', 'themify'),
		'description' => __('Replace Featured Image with a video embed URL such as YouTube or Vimeo video url (<a href="https://themify.me/docs/video-embeds">details</a>).', 'themify'),
		'type' => 'textbox',
		'meta' => array()
	    ),
	    // External Link
	    array(
		'name' => 'external_link',
		'title' => __('External Link', 'themify'),
		'description' => __('Link Featured Image and Post Title to external URL', 'themify'),
		'type' => 'textbox',
		'meta' => array()
	    ),
	    // Lightbox Link + Zoom icon
	    themify_lightbox_link_field(),
	    // Custom menu
	    array(
		'name' => 'custom_menu',
		'title' => __('Custom Menu', 'themify'),
		'description' => '',
		'type' => 'dropdown',
		'meta' => themify_get_available_menus()
	    )
	);
    }

}

/**
 * Default Single Post Layout
 * @param array $data Theme settings data
 * @return string Markup for module.
 * @since 1.0.0
 */
function themify_default_post_layout($data = array()) {
    $data = themify_get_data();

    /**
     * Theme Settings Option Key Prefix
     * @var string
     */
    $prefix = 'setting-default_page_';

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
     * Sidebar placement options
     * @var array
     */
    $sidebar_location_options = themify_sidebar_location_options();

    /**
     * Module markup
     * @var string
     */
    $output = '';

    /**
     * Post sidebar placement
     */
    $output .= '<p>
					<span class="label">' . __('Post Sidebar Option', 'themify') . '</span>';
    $val = themify_get($prefix . 'post_layout');
    foreach ($sidebar_location_options as $option) {
	if (($val == '' || !$val || !isset($val)) && $option['selected']) {
	    $val = $option['value'];
	}
	if ($val == $option['value']) {
	    $class = 'selected';
	} else {
	    $class = '';
	}
	$output .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }
    $output .= '	<input type="hidden" name="' . $prefix . 'post_layout" class="val" value="' . $val . '" />
				</p>';

    /**
     * Sticky Sidebar for All Posts
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=' . esc_attr($prefix) . 'post_layout]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="' . esc_attr($prefix) . 'post_sticky_sidebar">
							<input type="checkbox" id="' . esc_attr($prefix) . 'post_sticky_sidebar" name="' . esc_attr($prefix) . 'post_sticky_sidebar" value="1"
							' . checked(themify_get(esc_attr($prefix) . 'post_sticky_sidebar'), 1, false) . '
							/>' . __('Enable sticky sidebar', 'themify') . '
						</label>
					</p>';


    /**
     * Hide Post Title
     */
    $output .= '<p>
					<span class="label">' . __('Hide Post Title', 'themify') . '</span>
					<select name="' . $prefix . 'post_title">' .
	    themify_options_module($default_options, $prefix . 'post_title') . '
					</select>
				</p>';

    /**
     * Unlink Post Title
     */
    $output .= '<p>
					<span class="label">' . __('Unlink Post Title', 'themify') . '</span>
					<select name="' . $prefix . 'unlink_post_title">' .
	    themify_options_module($default_options, $prefix . 'unlink_post_title') . '
					</select>
				</p>';

    /**
     * Hide Post Meta
     */
    $output .= themify_post_meta_options($prefix . 'post_meta', $data);

    /**
     * Hide Post Date
     */
    $output .= '<p>
					<span class="label">' . __('Hide Post Date', 'themify') . '</span>
					<select onchange="jQuery(this).val()===\'yes\'?jQuery(\'#' . $prefix . 'display_post_date_wrap\').fadeOut():jQuery(\'#' . $prefix . 'display_post_date_wrap\').fadeIn();" name="' . $prefix . 'post_date">' .
	    themify_options_module($default_options, $prefix . 'post_date') . '
					</select>
										<br/><br/>
										<span id="' . $prefix . 'display_post_date_wrap" class="pushlabel">
										   <label for="' . $prefix . 'display_date_inline"><input type="checkbox" value="1" id="' . $prefix . 'display_date_inline" name="' . $prefix . 'display_date_inline" ' . checked(themify_get($prefix . 'display_date_inline'), 1, false) . '/>' . __('Display post date as inline text instead of circle style', 'themify') . '
										</span>
				</p>';


    /**
     * Featured Image Position
     */
    $output .= '<p>
				<span class="label">' . __('Featured Image Position', 'themify') . '</span>
				<select name="' . esc_attr($prefix) . 'single_media_position">' .
	    themify_options_module(themify_media_position(), $prefix . 'single_media_position') . '
				</select>
			</p>';

    /**
     * Hide Featured Image
     */
    $output .= '<p>
					<span class="label">' . __('Hide Featured Image', 'themify') . '</span>
					<select name="' . $prefix . 'post_image">' .
	    themify_options_module($default_options, $prefix . 'post_image') . '
					</select>
				</p>';

    /**
     * Unlink Featured Image
     */
    $output .= '<p>
					<span class="label">' . __('Unlink Featured Image', 'themify') . '</span>
					<select name="' . $prefix . 'unlink_post_image">' .
	    themify_options_module($default_options, $prefix . 'unlink_post_image') . '
					</select>
				</p>';
    /**
     * Featured Image Sizes
     */
    $output .= themify_feature_image_sizes_select('image_post_single_feature_size');

    /**
     * Image dimensions
     */
    $output .= '<p>
			<span class="label">' . __('Image Size', 'themify') . '</span>
					<input type="text" class="width2" name="setting-image_post_single_width" value="' . themify_get('setting-image_post_single_width') . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
					<input type="text" class="width2 show_if_enabled_img_php" name="setting-image_post_single_height" value="' . themify_get('setting-image_post_single_height') . '" /> <span class="show_if_enabled_img_php">' . __('height', 'themify') . ' <small>(px)</small></span>
					<br /><span class="pushlabel show_if_enabled_img_php"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
				</p>';

    /**
     * Disable comments
     */
    $pre = 'setting-comments_posts';
    $comments_posts_checked = themify_check($pre) ? 'checked="checked"' : '';
    $output .= '<p><span class="label">' . __('Post Comments', 'themify') . '</span><label for="' . $pre . '"><input type="checkbox" id="' . $pre . '" name="' . $pre . '" ' . $comments_posts_checked . ' /> ' . __('Disable comments in all Posts', 'themify') . '</label></p>';

    /**
     * Show author box
     */
    $pre = 'setting-post_author_box';
    $author_box_checked = themify_check($pre) ? 'checked="checked"' : '';

    $output .= '<p><span class="label">' . __('Show Author Box', 'themify') . '</span><label for="' . $pre . '"><input type="checkbox" id="' . $pre . '" name="' . $pre . '" ' . $author_box_checked . ' /> ' . __('Show author box in all Posts', 'themify') . '</label></p>';

    /**
     * Remove Post Navigation
     */
    $pre = 'setting-post_nav_';
    $output .= '<p>
					<span class="label">' . __('Post Navigation', 'themify') . '</span>
					<label for="' . $pre . 'disable">
						<input type="checkbox" id="' . $pre . 'disable" name="' . $pre . 'disable" ' . checked(themify_get($pre . 'disable'), 'on', false) . '/> ' . __('Remove Post Navigation', 'themify') . '
						</label>
					<span class="pushlabel vertical-grouped">
						<label for="' . $pre . 'same_cat">
							<input type="checkbox" id="' . $pre . 'same_cat" name="' . $pre . 'same_cat" ' . checked(themify_get($pre . 'same_cat'), 'on', false) . '/> ' . __('Show only posts in the same category', 'themify') . '
						</label>
					</span>
				</p>';

    return $output;
}

if (!function_exists('themify_theme_get_post_metaboxes')) {

    function themify_theme_get_post_metaboxes(array $args, &$meta_boxes) {
	return array(
	    array(
		'name' => __('Post Options', 'themify'),
		'id' => 'post-options',
		'options' => themify_theme_post_meta_box(),
		'pages' => 'post'
	    ),
	);
    }

}
