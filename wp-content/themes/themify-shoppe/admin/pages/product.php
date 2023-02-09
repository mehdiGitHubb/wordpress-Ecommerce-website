<?php

/**
 * Product Meta Box Options
 * @var array Options for Themify Custom Panel
 * @since 1.0.0
 */
if (!function_exists('themify_theme_product_meta_box')) {

    function themify_theme_product_meta_box() {
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
	    // Content Width
	    array(
		'name' => 'content_width',
		'title' => __('Content Width', 'themify'),
		'description' => '',
		'type' => 'layout',
		'show_title' => true,
		'meta' => array(
		    array(
			'value' => 'default_width',
			'img' => 'themify/img/default.svg',
			'selected' => true,
			'title' => __('Default', 'themify')
		    ),
		    array(
			'value' => 'full_width',
			'img' => 'themify/img/fullwidth.svg',
			'title' => __('Fullwidth (Builder Page)', 'themify')
		    )
		)
	    ),
	    // Product Image Layout
	    array(
		'name' => 'image_layout',
		'title' => __('Product Image Layout', 'themify'),
		'description' => '',
		'type' => 'layout',
		'show_title' => true,
		'meta' => array(
		    array('value' => '', 'img' => 'themify/img/default.svg', 'title' => __('Default', 'themify'), 'selected' => true),
		    array('value' => 'img-left', 'img' => 'images/layout-icons/image-left.png', 'title' => __('Product Image Left', 'themify')),
		    array('value' => 'img-center', 'img' => 'images/layout-icons/image-center.png', 'title' => __('Product Image Center', 'themify')),
		    array('value' => 'img-right', 'img' => 'images/layout-icons/image-right.png', 'title' => __('Product Image Right', 'themify'))
		)
	    ),
	    // Multi field: Image Dimension
	    themify_image_dimensions_field(array('title' => __('Product Image Dimension', 'themify')))
	);
    }

}

/**
 * Creates module for general shop layout and settings
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_shop_layout($data = array()) {
    $data = themify_get_data();

    $sidebar_options = array(
	array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
	array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
	array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'), 'selected' => true)
    );
    /**
     * Entries layout options
     * @var array
     */
    $default_entry_layout_options = array(
	array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'title' => __('List Post', 'themify')),
	array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
	array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
	array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify'), 'selected' => true),
	array('value' => 'grid5', 'img' => 'images/layout-icons/grid5.png','title' => __('Grid 5', 'themify')),
	array('value' => 'grid6','img' => 'images/layout-icons/grid6.png','title' => __('Grid 6', 'themify')),
	array('value' => 'list-large-image', 'img' => 'images/layout-icons/list-large-image.png', 'title' => __('List Large Image', 'themify')),
	array('value' => 'auto_tiles', 'img' => 'images/layout-icons/auto-tiles.png', 'title' => __('Tiles', 'themify'))
    );
    $default_options = array(
	array('name' => '', 'value' => ''),
	array('name' => __('Yes', 'themify'), 'value' => 'yes'),
	array('name' => __('No', 'themify'), 'value' => 'no')
    );
    $content_options = array(
	array('name' => __('Short Description', 'themify'), 'value' => 'excerpt'),
	array('name' => __('Full Content', 'themify'), 'value' => 'content'),
	array('name' => __('None', 'themify'), 'value' => ''),
    );

    $val = isset($data['setting-shop_layout']) ? $data['setting-shop_layout'] : '';

    /**
     * Modules output
     * @var String
     * @since 1.0.0
     */
    $output = '';

    /**
     * Sidebar option
     */
    $output .= '<p><span class="label">' . __('Shop Page Sidebar', 'themify') . '</span>';
    foreach ($sidebar_options as $option) {
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
    $output .= '<input type="hidden" name="setting-shop_layout" class="val" value="' . $val . '" /></p>';

	$content_width = isset( $data['setting-shop_content_width'] ) ? $data['setting-shop_content_width'] : 'default_width';
	$output .=
		'<p data-show-if-element="[name=setting-shop_layout]" data-show-if-value=\'["sidebar-none"]\'>
			<span class="label">' . __( 'Shop Page Content Width', 'themify' ) . '</span>
			<a href="#" class="preview-icon' . ( $content_width === 'default_width' ? ' selected' : '' ) . '" title="' . __( 'Default Width', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/default.svg" alt="default_width"></a>
			<a href="#" class="preview-icon' . ( $content_width === 'full_width' ? ' selected' : '' ) . '" title="' . __( 'Fullwidth (Builder Page)', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/fullwidth.svg" alt="full_width"></a>
			<input type="hidden" name="setting-shop_content_width" value="' . esc_attr( $content_width ) . '" class="val">
		</p>';

    /**
     * Sticky Sidebar for Shop Page
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=setting-shop_layout]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="setting-shop_sticky_sidebar">
							<input type="checkbox" id="setting-shop_sticky_sidebar" name="setting-shop_sticky_sidebar" value="1"
							' . checked(themify_get('setting-shop_sticky_sidebar'), 1, false) . '
							/>' . __('Enable sticky sidebar', 'themify') . '
						</label>
					</p>';


    $output .= themify_shop_archive_layout();
    /**
     * Entries Layout
     */
    $output .= '<p>
					<span class="label">' . __('Product Layout', 'themify') . '</span>';
    $val = isset($data['setting-products_layout']) ? $data['setting-products_layout'] : '';
    foreach ($default_entry_layout_options as $option) {
	if (( '' == $val || !$val || !isset($val) ) && ( isset($option['selected']) && $option['selected'] )) {
	    $val = $option['value'];
	}
	if ($val == $option['value']) {
	    $class = 'selected';
	} else {
	    $class = '';
	}
	$output .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }

    $output .= '	<input type="hidden" name="setting-products_layout" class="val" value="' . $val . '" />
				</p>';


    $output .= '<p data-show-if-element="[name=setting-products_layout]" data-show-if-value=' . '["grid2","grid3","grid4","grid5","grid6"]' . '><span class="label">' . __('Masonry Layout', 'themify') . '</span>
				<label for="setting-shop_masonry_disabled"><input type="checkbox" id="setting-shop_masonry_disabled" name="setting-shop_masonry_disabled" ' . checked(themify_get('setting-shop_masonry_disabled'), 'on', false) . ' /> ' . __('Disable masonry layout', 'themify') . '</label></p>';

    $output .= '<p data-show-if-element="[name=setting-products_layout]" data-show-if-value=' . '["grid2","grid3","grid4","grid5","grid6"]' . '><span class="label">' . __('Align Posts', 'themify') . '</span>
				<label><input type="checkbox" name="setting-shop_masonry_align" ' . checked(themify_check('setting-shop_masonry_align',true), true, false) . ' /></label></p>';


    /**
     * Product Content Style
     */
    $output .= '<p data-show-if-element="[name=setting-products_layout]" data-show-if-value=' . '["grid2","grid3","grid4","grid5","grid6","list-post"]' . '>
					<span class="label">' . __('Product Content Style', 'themify') . '</span>
					<select name="setting-product_content_layout">' .
	    themify_options_module(array(
		array('name' => __('Default', 'themify'), 'value' => ''),
		array('name' => __('Overlay', 'themify'), 'value' => 'overlay'),
		array('name' => __('Polaroid', 'themify'), 'value' => 'polaroid'),
		array('name' => __('Boxed', 'themify'), 'value' => 'boxed'),
		array('name' => __('Flip', 'themify'), 'value' => 'flip')
		    ), 'setting-product_content_layout') . '
					</select>
				</p>';
    /**
     * Product Gutter
     */
    $output .= '<p><span class="label">' . __('Product Gutter', 'themify') . '</span>
					<select name="setting-product_post_gutter">' .
	    themify_options_module(array(
		array('name' => __('Default', 'themify'), 'value' => 'gutter'),
		array('name' => __('No gutter', 'themify'), 'value' => 'no-gutter')
		    ), 'setting-product_post_gutter') . '
					</select>
				</p>';
    /**
     * Product Slider
     */
    $output .= '<p><span class="label">' . __('Product Image Hover', 'themify') . '</span>
					<select name="setting-products_slider">' .
	    themify_options_module(array(
		array('name' => __('Product Gallery Slider', 'themify'), 'value' => 'enable'),
		array('name' => __('First Product Gallery Image', 'themify'), 'value' => 'first_image'),
		array('name' => __('None', 'themify'), 'value' => 'disable')
		    ), 'setting-products_slider') . '
					</select>
				</p>';
    /**
     * Products Per Page
     */
    $output .= '<p><span class="label">' . __('Products Per Page', 'themify') . '</span>
				<input type="text" name="setting-shop_products_per_page" value="' . themify_get('setting-shop_products_per_page') . '" class="width2" /></p>';

    /**
     * Hide Title Options
     * @var String
     * @since 1.0.0
     */
    $output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Product Title', 'themify') . '</span>
					<select name="setting-product_archive_hide_title">
						' . themify_options_module($default_options, 'setting-product_archive_hide_title') . '
					</select>
				</p>';

    /**
     * Hide Price Options
     * @var String
     * @since 1.0.0
     */
    $output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Product Price', 'themify') . '</span>
					<select name="setting-product_archive_hide_price">
						' . themify_options_module($default_options, 'setting-product_archive_hide_price') . '
					</select>
				</p>';

    /**
     * Hide Add to Cart Button
     * @var String
     */
    $output .= '<p class="feature_box_posts">
					<span class="label">' . __('Hide Add to Cart Button', 'themify') . '</span>
					<select name="setting-product_archive_hide_cart_button">
						' . themify_options_module($default_options, 'setting-product_archive_hide_cart_button') . '
					</select>
				</p>';

    /**
     * 
      Disable Product Lightbox
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Lightbox', 'themify') . '</span>
				<label for="setting-disable_product_lightbox"><input type="checkbox" id="setting-disable_product_lightbox" name="setting-disable_product_lightbox" ' . checked(themify_get('setting-disable_product_lightbox'), 'on', false) . ' /> ' . __('Disable Product Lightbox', 'themify') . '</label></p>';

	/**
     * Hide Breadcrumbs
     * @var String
     */
    $output .= '<p><span class="label">' . __('Shop Breadcrumbs', 'themify') . '</span>
				<label for="setting-hide_shop_breadcrumbs"><input type="checkbox" id="setting-hide_shop_breadcrumbs" name="setting-hide_shop_breadcrumbs" ' . checked(themify_get('setting-hide_shop_breadcrumbs'), 'on', false) . ' /> ' . __('Hide shop breadcrumb navigation', 'themify') . '</label></p>';

    /**
     * Hide Product Count
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Count', 'themify') . '</span>
				<label for="setting-hide_shop_count"><input type="checkbox" id="setting-hide_shop_count" name="setting-hide_shop_count" ' . checked(themify_get('setting-hide_shop_count'), 'on', false) . ' /> ' . __('Hide product count', 'themify') . '</label></p>';

    /**
     * Hide Sorting Bar
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Sorting', 'themify') . '</span>
				<label for="setting-hide_shop_sorting"><input type="checkbox" id="setting-hide_shop_sorting" name="setting-hide_shop_sorting" ' . checked(themify_get('setting-hide_shop_sorting'), 'on', false) . ' /> ' . __('Hide product sorting select', 'themify') . '</label></p>';

    /**
     * Hide Shop Page Title
     * @var String
     */
    $output .= '<p><span class="label">' . __('Shop Page Title', 'themify') . '</span>
				<label for="setting-hide_shop_title"><input type="checkbox" id="setting-hide_shop_title" name="setting-hide_shop_title" ' . checked(themify_get('setting-hide_shop_title'), 'on', false) . ' /> ' . __('Hide shop page title', 'themify') . '</label></p>';

	/**
	 * Hide Shop products
	 * @var String
	 */
	$output .= '<p><span class="label">' . __('Shop Page Products', 'themify') . '</span>
				<label for="setting-hide_shop_products"><input type="checkbox" id="setting-hide_shop_products" name="setting-hide_shop_products" '.checked( themify_get( 'setting-hide_shop_products','',true ), 'on', false ).' /> ' . __('Hide products on shop page', 'themify') . '</label>
				<br /><span class="pushlabel"><small>' . __('Warning: no products will show on Shop page', 'themify') . '</small></span>
				</p>';

	/**
     * Hide More Info Button
     * @var String
     */
    $output .= '<p><span class="label">' . __('Quick Look', 'themify') . '</span>
				<label for="setting-hide_shop_more_info"><input type="checkbox" id="setting-hide_shop_more_info" name="setting-hide_shop_more_info" ' . checked(themify_get('setting-hide_shop_more_info'), 'on', false) . ' /> ' . __('Hide product quick look button', 'themify') . '</label></p>';

    /**
     * Show product rating stars
     * @var String
     */
    $output .= '<p><span class="label">' . __('Rating Stars', 'themify') . '</span>
				<label for="setting-hide_product_rating_stars"><input type="checkbox" id="setting-hide_product_rating_stars" name="setting-hide_product_rating_stars" ' . checked(themify_get('setting-hide_product_rating_stars'), 'on', false) . ' /> ' . __('Hide product rating stars', 'themify') . '</label></p>';

    /**
     * Product Reviews (Always show them even empty ones)
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=setting-hide_product_rating_stars]" data-show-if-value="false">
					<label for="setting-products_reviews_empty">
						<input type="checkbox" id="setting-products_reviews_empty" name="setting-products_reviews_empty" value="1" ' . checked(themify_get("setting-products_reviews_empty"), 1, false) . '	/>' . __('Always show rating stars (even when it has no rating)', 'themify') . '
					</label>
				</p>';

    /**
     * Hide Social Share
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Share', 'themify') . '</span>
				<label for="setting-hide_shop_share"><input type="checkbox" id="setting-hide_shop_share" name="setting-hide_shop_share" ' . checked(themify_get('setting-hide_shop_share'), 'on', false) . ' /> ' . __('Hide product share button', 'themify') . '</label></p>';

    /**
     * Show Short Description Options
     * @var String
     * @since 1.0.0
     */
    $output .= '<p class="feature_box_posts">
					<span class="label">' . __('Product Description', 'themify') . '</span>
					<select name="setting-product_archive_show_short">' .
	    themify_options_module($content_options, 'setting-product_archive_show_short') . '
					</select>
				</p>';

    /**
     * Image Dimensions
     */
    $output .= '<p class="show_if_enabled_img_php">
					<span class="label">' . __('Image Size', 'themify') . '</span>  
					<input type="text" class="width2" name="setting-default_product_index_image_post_width" value="' . themify_get('setting-default_product_index_image_post_width') . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
					<input type="text" class="width2" name="setting-default_product_index_image_post_height" value="' . themify_get('setting-default_product_index_image_post_height') . '" /> <span>' . __('height', 'themify') . ' <small>(px)</small></span>
					<br /><span class="pushlabel"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
				</p>';

    return $output;
}

/**
 * Creates module for general archive layout
 * @param array $data
 * @return string
 * @since 1.5.1
 */
function themify_shop_archive_layout($data = array()) {

    $data = themify_get_data();
    /**
     * Sidebar option
     */
    $val = isset($data['setting-shop_archive_layout']) ? $data['setting-shop_archive_layout'] : '';
    $options = array(
	array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
	array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
	array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'selected' => true, 'title' => __('No Sidebar', 'themify'))
    );

    $html = '<p><span class="label">' . __('Product Archive Sidebar', 'themify') . '</span>';
    foreach ($options as $option) {
	if (( '' == $val || !$val || !isset($val) ) && ( isset($option['selected']) && $option['selected'] )) {
	    $val = $option['value'];
	}
	$class = $val == $option['value'] ? "selected" : "";
	$html .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }
    $html .= '<input type="hidden" name="setting-shop_archive_layout" class="val" value="' . $val . '" /></p>';

    /**
     * Sticky Sidebar for Products Archive
     */
    $html .= '<p class="pushlabel" data-show-if-element="[name=setting-shop_archive_layout]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="setting-shop_archive_sticky_sidebar">
							<input type="checkbox" id="setting-shop_archive_sticky_sidebar" name="setting-shop_archive_sticky_sidebar" value="1"
							' . checked(themify_get('setting-shop_archive_sticky_sidebar'), 1, false) . '
							/>' . __('Enable sticky sidebar on product archive', 'themify') . '
						</label>
					</p>';

	$content_width = isset( $data['setting-custom_post_product_archive_content_width'] ) ? $data['setting-custom_post_product_archive_content_width'] : 'default_width';
	$html .=
		'<p data-show-if-element="[name=setting-shop_archive_layout]" data-show-if-value=\'["sidebar-none"]\'>
			<span class="label">' . __( 'Product Archive Content Width', 'themify' ) . '</span>
			<a href="#" class="preview-icon' . ( $content_width === 'default_width' ? ' selected' : '' ) . '" title="' . __( 'Default Width', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/default.svg" alt="default_width"></a>
			<a href="#" class="preview-icon' . ( $content_width === 'full_width' ? ' selected' : '' ) . '" title="' . __( 'Fullwidth (Builder Page)', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/fullwidth.svg" alt="full_width"></a>
			<input type="hidden" name="setting-custom_post_product_archive_content_width" value="' . esc_attr( $content_width ) . '" class="val">
		</p>';

    return $html;
}

/**
 * Creates module for single product settings
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_single_product($data = array()) {
    $data = themify_get_data();

    $options = array(
	array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
	array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
	array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar', 'themify'), 'selected' => true)
    );
    $defaul_image_layout = array(
	array('value' => 'img-left', 'img' => 'images/layout-icons/image-left.png', 'title' => __('Product Image Left', 'themify')),
	array('value' => 'img-center', 'img' => 'images/layout-icons/image-center.png', 'title' => __('Product Image Center', 'themify')),
	array('value' => 'img-right', 'img' => 'images/layout-icons/image-right.png', 'title' => __('Product Image Right', 'themify'))
    );

    $default_options = array(
	array('name' => '', 'value' => ''),
	array('name' => __('Yes', 'themify'), 'value' => 'yes'),
	array('name' => __('No', 'themify'), 'value' => 'no')
    );

    /**
     * Product Sidebar
     */
    $val = isset($data['setting-single_product_layout']) ? $data['setting-single_product_layout'] : '';
    $output = '<p><span class="label">' . __('Product Sidebar Option', 'themify') . '</span>';
    foreach ($options as $option) {
	if (( '' == $val || !$val || !isset($val) ) && ( isset($option['selected']) && $option['selected'] )) {
	    $val = $option['value'];
	}
	if ($val == $option['value']) {
	    $class = 'selected';
	} else {
	    $class = '';
	}
	$output .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }
    $output .= '<input type="hidden" name="setting-single_product_layout" class="val" value="' . $val . '" /></p>';

	$content_width = isset( $data['setting-custom_post_product_single_content_width'] ) ? $data['setting-custom_post_product_single_content_width'] : 'default_width';
	$output .=
		'<p data-show-if-element="[name=setting-single_product_layout]" data-show-if-value=\'["sidebar-none"]\'>
			<span class="label">' . __( 'Default Single Content Width', 'themify' ) . '</span>
			<a href="#" class="preview-icon' . ( $content_width === 'default_width' ? ' selected' : '' ) . '" title="' . __( 'Default Width', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/default.svg" alt="default_width"></a>
			<a href="#" class="preview-icon' . ( $content_width === 'full_width' ? ' selected' : '' ) . '" title="' . __( 'Fullwidth (Builder Page)', 'themify' ) . '"><img src="' . THEME_URI . '/themify/img/fullwidth.svg" alt="full_width"></a>
			<input type="hidden" name="setting-custom_post_product_single_content_width" value="' . esc_attr( $content_width ) . '" class="val">
		</p>';


    /**
     * Sticky Sidebar for All Products
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=setting-single_product_layout]" 
		    data-show-if-value=\'["sidebar1", "sidebar1 sidebar-left"]\'>
						<label for="setting-single_product_sticky_sidebar">
							<input type="checkbox" id="setting-single_product_sticky_sidebar" name="setting-single_product_sticky_sidebar" value="1"
							' . checked(themify_get('setting-single_product_sticky_sidebar'), 1, false) . '
							/>' . __('Enable sticky sidebar', 'themify') . '
						</label>
					</p>';


    /**
     * Product Image Layout
     */
    $val = isset($data['setting-product_image_layout']) ? $data['setting-product_image_layout'] : '';
    $output .= '<p><span class="label">' . __('Product Image Layout', 'themify') . '</span>';
    foreach ($defaul_image_layout as $option) {
	if (( '' == $val || !$val || !isset($val) ) && ( isset($option['selected']) && $option['selected'] )) {
	    $val = $option['value'];
	}
	if ($val == $option['value']) {
	    $class = 'selected';
	} else {
	    $class = '';
	}
	$output .= '<a href="#" class="preview-icon ' . $class . '" title="' . $option['title'] . '"><img src="' . THEME_URI . '/' . $option['img'] . '" alt="' . $option['value'] . '"  /></a>';
    }
    $output .= '<input type="hidden" name="setting-product_image_layout" class="val" value="' . $val . '" /></p>';

    /**
     * Image Dimensions
     */
    $output .= '<p class="show_if_enabled_img_php">
					<span class="label">' . __('Image Size', 'themify') . '</span>  
					<input type="text" class="width2" name="setting-default_product_single_image_post_width" value="' . themify_get('setting-default_product_single_image_post_width') . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
					<input type="text" class="width2" name="setting-default_product_single_image_post_height" value="' . themify_get('setting-default_product_single_image_post_height') . '" /> <span>' . __('height', 'themify') . ' <small>(px)</small></span>
					<br /><span class="pushlabel"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
				</p>';

    /**
     * Gallery Type
     */
    $gallery_type = themify_get('setting-product_gallery_type');
    if (!$gallery_type)
	$gallery_type = 'default';

    $output .= '<p>
					<span class="label">' . __('Product Gallery', 'themify') . '</span>  
					<label><input type="radio" name="setting-product_gallery_type" value="zoom" ' . checked($gallery_type, 'zoom', false) . '/> ' . __('Zoom Image', 'themify') . '</label>
					<label><input type="radio" name="setting-product_gallery_type" value="default" ' . checked($gallery_type, 'default', false) . ' />' . __('Default WooCommerce', 'themify') . '</label>
					<label><input type="radio" name="setting-product_gallery_type" value="disable-zoom" ' . checked($gallery_type, 'disable-zoom', false) . '/> ' . __('Disable Zoom', 'themify') . '</label>
				</p>';

    $output .= '<p><span class="label">' . __('Quantity Buttons', 'themify') . '</span>
				<label for="setting-quantity_button"><input type="checkbox" id="setting-quantity_button" name="setting-quantity_button" ' . checked(themify_get('setting-quantity_button'), 'on', false) . ' /> ' . __('Show quantity increase/decrease buttons', 'themify') . '</label></p>';

    /**
     * Hide Social Share
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Share', 'themify') . '</span>
				<label for="setting-single_hide_shop_share"><input type="checkbox" id="setting-single_hide_shop_share" name="setting-single_hide_shop_share" ' . checked(themify_get('setting-single_hide_shop_share'), 'on', false) . ' /> ' . __('Hide product share button', 'themify') . '</label></p>';

    /**
     * Hide Breadcrumbs
     * @var String
     */
    $output .= '<p><span class="label">' . __('Hide Shop Breadcrumbs', 'themify') . '</span>
				<label for="setting-hide_shop_single_breadcrumbs"><input type="checkbox" id="setting-hide_shop_single_breadcrumbs" name="setting-hide_shop_single_breadcrumbs" ' . checked(themify_get('setting-hide_shop_single_breadcrumbs'), 'on', false) . ' /> ' . __('Hide shop breadcrumbs', 'themify') . '</label></p>';

    /**
     * Hide Product SKU
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product SKU', 'themify') . '</span>
				<label for="setting-hide_shop_single_sku"><input type="checkbox" id="setting-hide_shop_single_sku" name="setting-hide_shop_single_sku" ' . checked(themify_get('setting-hide_shop_single_sku'), 'on', false) . ' /> ' . __('Hide product SKU', 'themify') . '</label></p>';

    /**
     * Hide Product tags
     * @var String
     */
    $output .= '<p><span class="label">' . __('Product Tags', 'themify') . '</span>
				<label for="setting-hide_shop_single_tags"><input type="checkbox" id="setting-hide_shop_single_tags" name="setting-hide_shop_single_tags" ' . checked(themify_get('setting-hide_shop_single_tags'), 'on', false) . ' /> ' . __('Hide product tags', 'themify') . '</label></p>';

    /**
     * Product Reviews
     */
    $output .= '<p><span class="label">' . __('Product Reviews', 'themify') . '</span>
				<label for="setting-product_reviews"><input type="checkbox" id="setting-product_reviews" name="setting-product_reviews" ' . checked(themify_get('setting-product_reviews'), 'on', false) . ' /> ' . __('Disable product reviews', 'themify') . '</label></p>';

    /**
     * Product Reviews (Always show them even empty ones)
     */
    $output .= '<p class="pushlabel" data-show-if-element="[name=setting-product_reviews]" data-show-if-value="false">
					<label for="setting-product_reviews_empty">
						<input type="checkbox" id="setting-product_reviews_empty" name="setting-product_reviews_empty" value="1" ' . checked(themify_get("setting-product_reviews_empty"), 1, false) . '	/>' . __('Always show rating stars (even when it has no rating)', 'themify') . '
					</label>
				</p>';

    /**
     * Related Products
     */
    $output .= '<p><span class="label">' . __('Related Products', 'themify') . '</span>
				<label for="setting-related_products"><input type="checkbox" id="setting-related_products" name="setting-related_products" ' . checked(themify_get('setting-related_products'), 'on', false) . ' /> ' . __('Do not display related products', 'themify') . '</label></p>';

    $output .= '<p data-show-if-element="[name=setting-related_products]" data-show-if-value=' . '["false"]' . '><span class="label">' . __('Related Products Limit', 'themify') . '</span>
					<input type="text" name="setting-related_products_limit" value="' . themify_get('setting-related_products_limit', '3', true) . '" class="width2" /></p>';


    /**
     * Related Image Dimensions
     */
    $output .= '<p class="show_if_enabled_img_php" data-show-if-element="[name=setting-related_products]" data-show-if-value=' . '["false"]' . '>
					<span class="label">' . __('Related Products Image Size', 'themify') . '</span>  
					<input type="text" class="width2" name="setting-product_related_image_width" value="' . themify_get('setting-product_related_image_width') . '" /> ' . __('width', 'themify') . ' <small>(px)</small>
					<input type="text" class="width2" name="setting-product_related_image_height" value="' . themify_get('setting-product_related_image_height') . '" /> <span>' . __('height', 'themify') . ' <small>(px)</small></span>
					<br /><span class="pushlabel"><small>' . __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify') . '</small></span>
				</p>';

    /**
     * Product description Display
     */
    $output .= '<p><span class="label">' . __('Builder Content Display', 'themify') . '</span>';
    $output .= '<label for="setting_product_description_long">';
    $output .= '<input ' . checked(themify_get('setting-product_description_type', 'long'), 'long', false) . ' type="radio" id="setting_product_description_long" name="setting-product_description_type" value="long"  /> ';
    $output .= __('In Long Description', 'themify') . '</label>';
    $output .= '<label for="setting_product_description_short">';
    $output .= '<input ' . checked(themify_get('setting-product_description_type'), 'short', false) . ' type="radio" id="setting_product_description_short" name="setting-product_description_type" value="short"  /> ';
    $output .= __('In Short Description', 'themify') . '</label>';
    $output .= '</p>';

    /**
     * Sticky Buy Button
     */
    $output .= '<p><span class="label">' . __('Sticky Buy Button', 'themify') . '</span>
				<label for="setting-st_add_cart"><input type="checkbox" id="setting-st_add_cart" name="setting-st_add_cart" '.checked( themify_get( 'setting-st_add_cart','',true ), 'on', false ).' /> ' . __('Disable sticky buy button', 'themify') . '</label></p>';

    /**
     * Description & Reviews Layout
     */
    $output .= '<p><span class="label">' . __('Description & Reviews', 'themify') . '</span>';
    $output .= '<label for="setting_product_tabs_regular">';
    $output .= '<input ' . checked(themify_get('setting-product_tabs_layout', 'tab'), 'tab', false) . ' type="radio" id="setting_product_tabs_regular" name="setting-product_tabs_layout" value="tab"  /> ';
    $output .= __('Tabs', 'themify') . '</label>';
    $output .= '<label for="setting_product_tabs_accordion">';
    $output .= '<input ' . checked(themify_get('setting-product_tabs_layout'), 'accordion', false) . ' type="radio" id="setting_product_tabs_accordion" name="setting-product_tabs_layout" value="accordion"  /> ';
    $output .= __('Accordion', 'themify') . '</label>';
    $output .= '<label>';
    $output .= '<input ' . checked(themify_get('setting-product_tabs_layout'), 'none', false) . ' type="radio" name="setting-product_tabs_layout" value="none"  /> ';
    $output .= __('None', 'themify') . '</label>';
    $output .= '</p>';

    return $output;
}

/**
 * Creates module for shop sidebar
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_shop_sidebar($data = array()) {
    $data = themify_get_data();

    $key = 'setting-disable_shop_sidebar';

    $output = '<p><span class="label">' . __('Shop Sidebar', 'themify') .themify_help(__('Shop sidebar is used in all WooCommerce pages such as shop, products, product categories, cart, checkout page, etc. If disabled, the main sidebar will be used.', 'themify')) . '</span>
			<label for="' . $key . '"><input type="checkbox" id="' . $key . '" name="' . $key . '" ' . checked(themify_get($key), 'on', false) . ' /> ' . __('Disable shop sidebar', 'themify') . '</label></p>';

    return $output;
}

/**
 * Creates module for ajax cart style
 * @param array
 * @return string
 * @since 1.0.0
 */
function themify_ajax_cart_style($data = array()) {
    $data = themify_get_data();

    $key = 'setting-cart_style';
    $value = themify_get($key);
    if (!$value) {
	$value = 'dropdown';
    }
    $output = '<p><span class="label">' . __('Cart Style', 'themify') . '</span>
			<label><input type="radio" value="dropdown" name="' . $key . '" ' . checked($value, 'dropdown', false) . ' /> ' . __('Dropdown cart', 'themify') . '</label>';
    $output .= '<label><input type="radio" value="slide-out" name="' . $key . '" ' . checked($value, 'slide-out', false) . ' /> ' . __('Slide-out cart', 'themify') . '</label>';
    $output .= '<label><input type="radio" value="link_to_cart" name="' . $key . '" ' . checked($value, 'link_to_cart', false) . ' /> ' . __('Link to cart page', 'themify') . '</label></p>';

    $key = 'setting-cart_show_seconds';

    $output .= '<p><span class="label">' . __('Show cart', 'themify') . '</span>
					<select name="' . $key . '">' .
	    themify_options_module(array(
			array('name' => __('Off','themify'), 'value' => 'off'),
		array('name' => 1, 'value' => 1000),
		array('name' => 2, 'value' => 2000),
		array('name' => 3, 'value' => 3000),
		array('name' => 4, 'value' => 4000),
		array('name' => 5, 'value' => 5000)
		    ), $key,true,'1000') . '
					</select> ' . esc_html__('seconds', 'themify') . '<br>
					<small class="pushlabel">' . esc_html__('When an item is added, show cart for n second(s)', 'themify') . '</small>
				</p>';

    /**
     * Disable AJAX add to cart
     * @var String
     */
    $output .= '<p>
				<label for="setting-single_ajax_cart" class="pushlabel"><input type="checkbox" id="setting-single_ajax_cart" name="setting-single_ajax_cart" ' . checked(themify_get('setting-single_ajax_cart'), 'on', false) . ' /> ' . __('Disable AJAX cart on single product page', 'themify') . '</label></p>';

    return $output;
}

if (!function_exists('themify_spark_animation')) {

    /**
     * Spark Animation Settings
     * @param array $data
     * @return string
     */
    function themify_spark_animation($data = array()) {
	$pre_color = 'setting-spark_color';
	$pre = 'setting-spark_animation';
	$sparkling_color = themify_get($pre_color);
	$output = '<p><span class="label">' . __('Spark Animation', 'themify') .themify_help(__('Spark animation is the animation effect occurs when user clicks on the add to cart or wishlist button.', 'themify')) . '</span><label for="' . $pre . '"><input type="checkbox" id="' . $pre . '" name="' . $pre . '" ' . checked(themify_get($pre), 'on', false) . ' /> ' . __('Disable add to cart and wishlist spark animation', 'themify');
	$output .= '<br/></label></p>';
	$output .= '<div class="themify_field_row" data-show-if-element="[name=' . $pre . ']" data-show-if-value="false">
					<span class="label">' . __('Spark Icons Color', 'themify') . '</span>
					<div class="themify_field-color">
						<span class="colorSelect" style="' . esc_attr('background:#' . $sparkling_color . ';') . '">
							<span></span>
						</span>
						<input type="text" class="colorSelectInput width4" value="' . esc_attr($sparkling_color) . '" name="' . esc_attr($pre_color) . '" />
					</div>
				</div>';
	return $output;
    }

}

if (!function_exists('themify_theme_get_product_metaboxes')) {

    function themify_theme_get_product_metaboxes(array $args, &$meta_boxes) {
	return array(
	    array(
		'name' => __('Product Options', 'themify'),
		'id' => 'product-options',
		'options' => themify_theme_product_meta_box(),
		'pages' => 'product'
	    ),
	);
    }

}
