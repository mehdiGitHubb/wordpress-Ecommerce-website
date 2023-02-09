<?php
/**
 * Changes to WordPress behavior and interface applied by Themify framework
 *
 * @package Themify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add Themify Settings link to admin bar
 * @since 1.1.2
 */
function themify_admin_bar() {
	if ( !is_super_admin() || !is_admin_bar_showing() ){
		return;
        }
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'id' => 'themify-settings',
		'parent' => 'appearance',
		'title' => __( 'Themify Settings', 'themify' ),
		'href' => admin_url( 'admin.php?page=themify' )
	));
}
add_action( 'wp_before_admin_bar_render', 'themify_admin_bar' );

/**
 * Add different CSS classes to body tag.
 * Outputs:
 * 		skin name
 * 		layout
 * @param Array
 * @return Array
 * @since 1.2.2
 */
function themify_body_classes( $classes ) {
	global $themify;

	// Add skin name
	$skin = themify_get_skin();
	if($skin===false){
	    $skin='default';
	}
	$classes[] = 'skin-'.$skin;
	if ( is_singular() && post_password_required( get_the_ID() )) {
		$classes[] = 'entry-password-required';
	}
	if( themify_is_query_page() ) {
		$classes[] = 'query-page';
		$classes[] = isset($themify->query_post_type) ? 'query-'.$themify->query_post_type: 'query-post';
	}

	// If still empty, set default
	if( apply_filters('themify_default_layout_condition', '' == $themify->layout) ){
		$themify->layout = apply_filters('themify_default_layout', 'sidebar1');
	}
	if ( $themify->layout === 'full_width' ) {
		/* Default Page Layout option is set to fullwidth */
		$content_width = 'full_width';
		$themify->layout = 'sidebar-none';
	} else {
		$content_width = 'default_width';
	}

	$classes[] = $themify->layout;
	if ( $themify->layout === 'sidebar-none' ) {
		$post_type = get_post_type();
		if ( themify_is_shop() && ! is_search() ) {
			$content_width = themify_get_both( 'content_width', 'setting-shop_content_width', $content_width );
		} else if ( is_singular() ) {
			$content_width = themify_get_both( 'content_width', 'setting-custom_post_' . $post_type . '_single_content_width', $content_width );
		} else if ( is_archive() ) {
			$content_width = themify_get( 'setting-custom_post_' . $post_type . '_archive_content_width', $content_width, true );
		}
	}
	$classes[] = $content_width;

	// non-homepage pages
	if( !is_home() && !is_front_page()) {
		$classes[] = 'no-home';
	}

	// if the page is being displayed in lightbox
	if( isset( $_GET['iframe'] ) && $_GET['iframe'] === 'true' ) {
		$classes[] = 'lightboxed';
	}

	// Add Accessibility classes
	$acc_link=themify_get('setting-acc_lfo','',true);
	if('h'===$acc_link || 'n'===$acc_link){
		$classes[] = 'h'===$acc_link?'tf_focus_heavy':'tf_focus_none';
	}
	if('l'===themify_get('setting-acc_fs','',true)){
		$classes[] = 'tf_large_font';
	}
	
	return apply_filters('themify_body_classes', $classes);
}
add_filter( 'body_class', 'themify_body_classes' );

/**
 * Adds classes to .post based on elements enabled for the currenty entry.
 *
 * @since 2.0.4
 *
 * @param $classes
 *
 * @return array
 */
function themify_post_class( $classes ) {
	global $themify;

	$classes[] = ( ! isset($themify->hide_title) || ( $themify->hide_title !== 'yes' ) ) ? 'has-post-title' : 'no-post-title';
	$classes[] = ( ! isset( $themify->hide_date ) || (  $themify->hide_date !== 'yes' ) ) ? 'has-post-date' : 'no-post-date';
	$classes[] = ( ! isset( $themify->hide_meta_category ) || (  $themify->hide_meta_category !== 'yes' ) ) ? 'has-post-category' : 'no-post-category';
	$classes[] = ( ! isset( $themify->hide_meta_tag ) || (  $themify->hide_meta_tag !== 'yes' ) ) ? 'has-post-tag' : 'no-post-tag';
	$classes[] = ( ! isset( $themify->hide_meta_comment ) || (  $themify->hide_meta_comment !== 'yes' ) ) ? 'has-post-comment' : 'no-post-comment';
	$classes[] = ( ! isset( $themify->hide_meta_author ) || (  $themify->hide_meta_author !== 'yes' ) ) ? 'has-post-author' : 'no-post-author';
	$classes[] = ( is_admin() && get_post_type() === 'product' ) ? 'product' : '';

	return apply_filters( 'themify_post_classes', $classes );
}
add_filter( 'post_class', 'themify_post_class' );


/**
 * Add wmode transparent and post-video container for responsive purpose
 * Remove webkitallowfullscreen and mozallowfullscreen for HTML validation purpose
 * @param string $html The embed markup.
 * @param string $url The URL embedded.
 * @return string The modified embed markup.
 */
function themify_parse_video_embed_vars($html, $url) {
	if ( false !== strpos( $url, 'youtube.com' )
		|| false !== strpos( $url, 'youtu.be' )
		|| false !== strpos( $url, 'vimeo.com' )
		|| false !== strpos( $url, 'funnyordie.com' )
		|| false !== strpos( $url, 'dailymotion.com' )
		|| false !== strpos( $url, 'blip.tv' ) ) {
		$html = '<div class="post-video">' . $html . '</div>';
	}

	return $html;
}

add_filter( 'embed_oembed_html', 'themify_parse_video_embed_vars', 10, 2 );

/**
 * Add extra protocols like skype: to list of allowed protocols.
 *
 * @since 2.1.8
 *
 * @param array $protocols List of protocols allowed by default by WordPress.
 *
 * @return array $protocols Updated list including extra protocols added.
 */
function themify_allow_extra_protocols( $protocols ){
	$protocols[] = 'skype';
	$protocols[] = 'sms';
	$protocols[] = 'comgooglemaps';
	$protocols[] = 'comgooglemapsurl';
	$protocols[] = 'comgooglemaps-x-callback';
	$protocols[] = 'viber';
	$protocols[] = 'facetime';
	$protocols[] = 'facetime-audio';
	$protocols[] = 'tg';
	$protocols[] = 'whatsapp';
	$protocols[] = 'ymsgr';
	$protocols[] = 'gtalk';

	return $protocols;
}
add_filter( 'kses_allowed_protocols' , 'themify_allow_extra_protocols' );

if( ! function_exists( 'themify_upload_mime_types' ) ) :
/**
 * Adds .svg and .svgz to list of mime file types supported by WordPress
 * @param array $existing_mime_types WordPress supported mime types
 * @return array Array extended with svg/svgz support
 * @since 1.3.9
 */
function themify_upload_mime_types( $existing_mime_types = array() ) {
	$existing_mime_types['svg'] = 'image/svg+xml';
	$existing_mime_types['svgz'] = 'image/svg+xml';
	$existing_mime_types['zip'] = 'application/zip';
	$existing_mime_types['json'] = 'application/json';
	$existing_mime_types['webp'] = 'image/webp';
	return $existing_mime_types;
}
endif;
add_filter( 'upload_mimes', 'themify_upload_mime_types' );

/**
 * Display an additional column in categories list
 * @since 1.1.8
 */
function themify_custom_category_header( $cat_columns ) {
    $cat_columns['cat_id'] = __( 'ID', 'themify' );
    return $cat_columns;
}
add_filter( 'manage_edit-category_columns', 'themify_custom_category_header', 10, 2 );

/**
 * Display ID in additional column in categories list
 * @since 1.1.8
 */
function themify_custom_category( $null, $column, $termid ) {
	return $termid;
}
add_filter( 'manage_category_custom_column', 'themify_custom_category', 10, 3 );


function themify_favicon_action() {
	$icon = themify_get('setting-favicon',false,true);
	if ( !empty( $icon )) {
            $type=pathinfo($icon,PATHINFO_EXTENSION);
            $icon=esc_attr( themify_https_esc($icon) );
            if($type==='ico'){
                $type='x-icon';
            }
            else{
                if($type==='svg' || $type==='svgz'){
                    echo '<link href="' .$icon . '" rel="mask-icon" color=”#fff" />';
                    $type='svg+xml';
                }
                else{
                    echo '<link type="image/'.$type.'" href="' .$icon . '" rel="apple-touch-icon" />';
                }
            }
            echo '<link type="image/'.$type.'" href="' .$icon . '" rel="icon" />';
	}
}
add_action( 'admin_head', 'themify_favicon_action' );

if ( ! function_exists( 'themify_search_in_category' ) ) :
/**
 * Exclude Custom Post Types from Search - Filter
 *
 * @param $query
 * @return mixed
 */
function themify_search_query_action( $query ) {
	if ( $query->is_search && ! is_admin() && $query->is_main_query() ) {
	    remove_action( 'pre_get_posts', 'themify_search_query_action', 999 );
            $args = apply_filters('themify_search_args',array('post_type'=>$query->get('post_type')));
            if(isset($args['tax_query'])){
                $query->set( 'tax_query', $args['tax_query'] );
            }
	}
}
endif;
if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'themify_search_query_action', 999 );
}

if ( ! function_exists( 'themify_search_in_category' ) ) :
    /**
     * Exclude Custom Post Types from Search - Filter
     *
     * @param $args
     * @return array
     */
function themify_search_in_category_filter( $args ) {
    $cat_search = themify_get( 'setting-search_settings','',true );
    if ( !empty( $cat_search )) {
        $post_type=is_array($args['post_type'])?$args['post_type'][0]:$args['post_type'];
        $taxonomy = 'product'===$post_type ? 'product_cat' : 'category';
        $args['tax_query']=themify_parse_category_args($cat_search, $taxonomy);
    }
    return $args;
}
add_filter( 'themify_search_args', 'themify_search_in_category_filter' );
endif;



/**
 * Exclude post types from search results, per user settings
 *
 * @since 4.6.8
 */
function themify_register_post_type_args( $args, $post_type ) {
	if ( ! isset( $_GET['s'] ) )
		return $args;

	$key = $post_type === 'page' ? 'setting-search_settings_exclude' : 'setting-search_exclude_' . $post_type;
	if ( themify_get( $key,false,true ) ) {
		/**
		 * @note Side effect: removes the post type from WP_Query query when 'post_type' => 'any'
		 * @link https://developer.wordpress.org/reference/classes/wp_query/#post-type-parameters
		 */
		$args['exclude_from_search'] = true;
	}

	return $args;
}
if ( ! is_admin() ) {
	add_filter( 'register_post_type_args', 'themify_register_post_type_args', 10, 2 );
}

function themify_feed_settings_action($query){
	
	if( $query->is_feed ) {
		$v = themify_get('setting-feed_settings',null,true);
		if( !empty( $v ) ) {
			$query->set( 'cat', $v );	
		}
	}
	else{
	    remove_action('pre_get_posts','themify_feed_settings_action');
	}
}
add_action('pre_get_posts','themify_feed_settings_action');

if (! themify_check( 'setting-exclude_img_rss',true ) ) {
	add_filter( 'the_content_feed', 'themify_custom_fields_for_feeds' );
	add_filter( 'the_excerpt_rss', 'themify_custom_fields_for_feeds' );
	function themify_custom_fields_for_feeds( $content ) {
		if ( has_post_thumbnail() ) {
			$content = '<p>' . get_the_post_thumbnail( null, 'full' ) . '</p>' . $content;
		}
		return $content;
	}
}

// Custom Post Types in RSS
function themify_feed_custom_posts( $qv ) {
	if(isset( $qv['feed'] ) && ! isset( $qv['post_type'] ) ){
		$feed_custom_posts = explode( ',', trim( themify_get( 'setting-feed_custom_post',false,true ) ) );
		$feed_custom_posts = array_filter( $feed_custom_posts );
		if( ! empty( $feed_custom_posts )) {
			if( in_array( 'all', $feed_custom_posts,true ) ) {
				$post_types = get_post_types( array('public' => true, 'publicly_queryable' => 'true' ) );
				$qv['post_type'] = array_diff( $post_types, array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section') );
			} else {
				$qv['post_type'] = $feed_custom_posts;
			}
		}
	}
	return $qv;
}
add_filter( 'request', 'themify_feed_custom_posts' );

/**
 * Handle Builder's JavaScript fullwidth rows, forces fullwidth rows if sidebar is disabled
 *
 * @return bool
 */
function themify_fullwidth_layout_support($support){
    if($support!==true) {
        global $themify;
        if($themify->layout!=='sidebar-none' || (function_exists('themify_theme_is_fullpage_scroll') && themify_theme_is_fullpage_scroll())) {
            $support=true;
        } else {
            /* if Content Width option is set to Fullwidth, do not use JavaScript, using sidebar-none layout, force fullwidth rows using JavaScript */
            $support=(is_singular() || themify_is_shop()) ? themify_get('content_width')==='full_width' : false;
        }
    }
    return $support;
}
add_filter( 'themify_builder_fullwidth_layout_support', 'themify_fullwidth_layout_support' );

/**
 * Load current skin's functions file if it exists
 *
 * @since 1.4.9
 */
function themify_theme_load_skin_functions() {
	$skin = themify_get_skin();
	if( $skin!==false && is_file( THEME_DIR . '/skins/' . $skin . '/functions.php' ) ) {
	    include THEME_DIR . '/skins/' . $skin . '/functions.php';
	}
}
add_action( 'after_setup_theme', 'themify_theme_load_skin_functions', 1 );

/**
 * Change order and orderby parameters in the index loop, per options in Themify > Settings > Default Layouts
 *
 * @since 3.1.2
 */
function themify_archive_post_order( $query ) {
	if ( $query->is_main_query() && ( $query->is_home() || $query->is_category() || $query->is_tag() ) ) {
		remove_action( 'pre_get_posts', 'themify_archive_post_order', 999 );
		$query->set( 'order', themify_get( 'setting-index_order','date',true ) );
		$orderBy=themify_get( 'setting-index_orderby' ,'',true);
		$query->set( 'orderby', $orderBy);
		if (($orderBy==='meta_value' || $orderBy==='meta_value_num')) {
			$metaKey=themify_get( 'setting-index_meta_key',false,true );
			if($metaKey){
				$query->set( 'meta_key', $metaKey );
			}
		}
	}
}
if ( ! is_admin() )
	add_action( 'pre_get_posts', 'themify_archive_post_order', 999 );


/**
 * Enable shortcodes in footer text areas
 */
add_filter( 'themify_the_footer_text_left', 'do_shortcode' );
add_filter( 'themify_the_footer_text_right', 'do_shortcode' );

/**
 * Enable shortcode in excerpt
 */
add_filter('the_excerpt', 'do_shortcode');	
add_filter('the_excerpt', 'shortcode_unautop');

function themify_filter_widget_text( $text, $instance = array( ) ) {
	global $wp_widget_factory;

	/* check for WP 4.8.1+ widget */
        /*
	 * if $instance['filter'] is set to "content", this is a WP 4.8 widget,
	 * leave it as is, since it's processed in the widget_text_content filter
	 */
	if( (isset( $instance['filter'] ) && 'content' === $instance['filter'])  || (isset( $wp_widget_factory->widgets['WP_Widget_Text'] ) && method_exists( $wp_widget_factory->widgets['WP_Widget_Text'], 'is_legacy_instance' ) && ! $wp_widget_factory->widgets['WP_Widget_Text']->is_legacy_instance( $instance ) )) {
		return $text;
	}
	return shortcode_unautop( do_shortcode( $text ) );
}
add_filter( 'widget_text', 'themify_filter_widget_text', 10, 2 );
/**
 * Enable shortcodes in Text widget for Wp 4.8+
 */
add_filter( 'widget_text_content', 'do_shortcode', 12 );

/**
 * Registers support for various WordPress features
 *
 * @since 3.2.1
 */
function themify_setup_wp_features() {

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-list',
		'comment-form',
		'search-form',
		'gallery',
		'caption',
		'script',
		'style'
	) );
}
add_filter( 'after_setup_theme', 'themify_setup_wp_features' );


/**
 * Adds Post Options in Themify Custom Panel to custom post types
 * that do not have any options set for it.
 *
 * @return array
 */
function themify_setup_cpt_post_options( $metaboxes ){
	global $typenow;

	/* post types that don't have single page views don't need the Post Options */
	$post_type = get_post_type_object( $typenow );
	if ( empty($typenow) || (is_object($post_type) && ! $post_type->publicly_queryable) ) {
		return $metaboxes;
	}

	/* list of post types that already have defined options */
	$exclude = false;
	foreach ( $metaboxes as $metabox ) {
		if ( $metabox[ 'id' ] === $typenow . '-options' ) {
			$exclude = true;
			break;
		}

		if ( ! empty( $metabox['options'] ) ) {
			foreach( $metabox['options'] as $option ) {
				if( in_array( $typenow . '_layout', $option, true ) ) {
					$exclude = true;
					break 2;
				}
			}
		}
	}

	if ( $exclude ) {
		return $metaboxes;
	}

	/* post types that should not have the CPT Post options */
    $excludes = apply_filters( 'themify_exclude_cpt_post_options', array( 'tbuilder_layout', 'tbuilder_layout_part' ));
	if ( in_array( $typenow, $excludes,true) ) {
		return $metaboxes;
	}

	$name = !empty( $typenow )? 'custom_post_' . $typenow . '_single' : 'page_layout';

	$post_options =  array(
			array(
				'name' => $name,
				'title' => __('Sidebar Option', 'themify'),
				'description' => '',
				'type' => 'layout',
				'show_title' => true,
				'meta' => apply_filters( 'themify_post_type_theme_sidebars', array(
						array('value' => 'default', 'img' => 'themify/img/default.svg', 'selected' => true, 'title' => __('Default', 'themify')),
						array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
						array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
						array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify')),
						array('value' => 'full_width', 'img' => 'themify/img/fullwidth.svg', 'title' => __('Fullwidth (Builder Page)', 'themify')),
					)
				),
				'default' => 'default'
			),
			array(
				'name'=> 'content_width',
				'type' => 'hidden'
			),
		) ;


	return array_merge( array(
		array(
			'name' => __( 'Post Options', 'themify' ),
			'id' => $typenow . '-options',
			'options' =>  apply_filters( 'themify_post_type_default_options', $post_options),
			'pages' => $typenow
		),
	), $metaboxes );
}
add_filter( 'themify_metabox/fields/themify-meta-boxes', 'themify_setup_cpt_post_options', 101 );

/**
 * Set proper sidebar layout for post types' single post view
 *
 * @uses global $themify
 */
function themify_cpt_set_post_options() {
	if ( is_singular() ) {
		$exclude = apply_filters( 'themify_exclude_CPT_for_sidebar', array( 'post', 'page', 'attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section' ) );
		if ( ! in_array( get_post_type(), $exclude,true ) ) {
		    global $themify;
			$cpt_sidebar = 'custom_post_'.get_post_type().'_single';
			$layout=themify_get( $cpt_sidebar ) ;
			if ( $layout!== 'default' && !empty($layout)) {
				$themify->layout = $layout;
			} elseif ( themify_check( 'setting-'.$cpt_sidebar,true) ) {
				$themify->layout = themify_get( 'setting-'.$cpt_sidebar,false,true );
			} else {
				$themify->layout = themify_get( 'page_layout' );
			}
		}
	}
}
add_action( 'template_redirect', 'themify_cpt_set_post_options', 100 );

/**
 * Set default 'large' image size on attachment page
 */
function themify_prepend_attachment() {
	return '<p>' . wp_get_attachment_link( 0, 'large', false ) . '</p>';
}
add_filter( 'prepend_attachment', 'themify_prepend_attachment' );

function themify_theme_post_gallery($output, $attr){
    if(!is_admin() || themify_is_ajax()){
	remove_filter('post_gallery', 'themify_theme_post_gallery', 10, 2);
	Themify_Enqueue_Assets::loadGalleryCss();
    }
    return $output;
}
add_filter( 'post_gallery', 'themify_theme_post_gallery', 10, 2 );


//Make query page
if( ! function_exists( 'themify_custom_query_posts' ) ) {
	function themify_custom_query_posts( $args ) {
		global $themify;
		if ( isset($themify->query_category) && $themify->query_category !== '' && is_page()) {
		    $qpargs = array(
			    'post_type' => !empty($themify->query_post_type)?$themify->query_post_type:'post',
			    'posts_per_page' => ! empty( $themify->posts_per_page ) ? $themify->posts_per_page : get_option( 'posts_per_page' ),
			    'order' => $themify->order,
			    'orderby' => $themify->orderby
		    );
		    if( ! empty( $themify->order_meta_key ) ) {
			    $qpargs['meta_key'] = $themify->order_meta_key;
		    }
		    $taxonomy = isset( $themify->query_taxonomy ) ? $themify->query_taxonomy : 'category';
			$qpargs['tax_query'] = themify_parse_category_args($themify->query_category, $taxonomy);

		    if( ! empty( $args ) ) {
			    $qpargs = wp_parse_args( $args, $qpargs );
		    }
                    unset($args);
		    if(!isset($qpargs['paged'])){
			$qpargs['paged']=!empty($themify->paged)?$themify->paged:get_query_var('paging', 1);
		    }
			
		    do_action('themify_query_before_posts_page_args', $qpargs);
		    $qpargs=apply_filters( 'themify_query_posts_page_args', $qpargs );
		    query_posts( $qpargs );
		    do_action('themify_query_after_posts_page_args', $qpargs);
                    unset($qpargs);
		    if(!has_action('themify_reset_query','wp_reset_query')){
				add_action('themify_reset_query','wp_reset_query',1);
		    }
		}
	}
}

/**
 * Add custom query_posts
 */
add_action( 'themify_custom_query_posts', 'themify_custom_query_posts' );

function themify_custom_except_length($length) {
    global $themify;
    if ( $themify->display_content === 'excerpt' && ! empty( $themify->excerpt_length ) ){
		$length= apply_filters( 'themify_custom_excerpt_length', $themify->excerpt_length );
    }

    return $length;
}
if ( ! is_admin() || themify_is_ajax() )
	add_filter( 'excerpt_length', 'themify_custom_except_length', 999 );

function themify_custom_except($excerpt) {
    if (has_excerpt()) {
        $excerpt = wp_trim_words(get_the_excerpt(), apply_filters("excerpt_length", 55));
    }
    return $excerpt;
}


/**
 * Change the default Read More link
 * @return string
 */
function themify_modify_read_more_link($link,$more_link_text='') {
	return '<a class="more-link" href="' . get_permalink() . '">'.$more_link_text.'</a>';
}
add_filter( 'the_content_more_link', 'themify_modify_read_more_link', 10, 2 );


add_filter('script_loader_tag', 'themify_defer_js', 11, 3);
add_filter('wp_get_attachment_image_src', 'themify_generate_src_webp', 100,1);

//deprecated code 07.06.2020
if(is_child_theme()){
    function themify_set_deprecated_values(){
	global $themify;
	$themify->image_setting=$themify->image_align=$themify->allow_sorting=$themify->is_builder_loop=$themify->is_isotop='';
    }
    add_action('after_setup_theme', 'themify_set_deprecated_values',15);
    if(!function_exists('themify_theme_comment')){
	function themify_theme_comment($comment, $args, $depth){
	    themify_comment_list($comment, $args, $depth);
	}
    }
    if(!function_exists('themify_theme_query_classes')){
	function themify_theme_query_classes(){return '';}
    }
}
function themify_set_is_shop($query){
	if($query && $query->is_main_query()){
		remove_filter('parse_query','themify_set_is_shop',100,1);
		$id=false;
			if($query->is_page()){
				$id=!empty($query->query_vars['page_id'])?$query->query_vars['page_id']:(!empty($query->queried_object->ID)?$query->queried_object->ID:-1);
				if($id>0){
					$id=(int)$id;
				}
			}
		themify_is_shop($id);
	}
	return $query;
}
if(!is_admin() || themify_is_ajax()){
    add_filter('parse_query','themify_set_is_shop',100,1);
}

// Portfolio comments filter
function portfolio_comments_open( $open, $post_id ) {
    return 'portfolio' === get_post_type($post_id) && themify_check( 'setting-portfolio_comments',true )?true:$open;
}
if(themify_is_themify_theme()){
    add_filter( 'comments_open', 'portfolio_comments_open', 10, 2 );
}

/**
 * Disable builder in page option modal
 */
if(!empty($_GET['tf-meta-opts'])){
    function themify_theme_disable_builder_page_opts() {
        add_filter('themify_enable_builder','themify_theme_filter_page_options',99);
    }
    function themify_theme_filter_page_options(){
        return 'disable';
    }
    add_action( 'after_setup_theme', 'themify_theme_disable_builder_page_opts', 1 );
}