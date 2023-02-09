<?php
/**
 * Custom template tags used in template files
 *
 * @package Themify
 */
if (!function_exists('themify_logo_image')) :

    /**
     * Returns logo image.
     * Available filters:
     * 'themify_'.$location.'_logo_tag': filter the HTML tag used to wrap the text or image.
     * 'themify_logo_home_url': filter the home URL linked.
     * 'themify_'.$location.'_logo_html': filter the final HTML output to page.
     * @param string $location Logo location used as key to get theme setting values
     * @param string $cssid ID attribute for the logo tag.
     * @return string logo markup
     */
    function themify_logo_image($location = 'site_logo', $cssid = 'site-logo') {
	global $themify_customizer;
	if($location==='site_logo'){
	    Themify_Enqueue_Assets::loadThemeStyleModule('site-logo');
	}
	$logo_tag = apply_filters('themify_' . $location . '_logo_tag', 'div');
	$logo_is_image = themify_get('setting-' . $location, false, true) === 'image' && themify_check('setting-' . $location . '_image_value', true);
	$logo_mod = $logo_is_image === true ? get_theme_mod($cssid . '_image') : false;

	$html = '<' . $logo_tag . ' id="' . esc_attr($cssid) . '">';
	if ($logo_is_image === true && empty($logo_mod)) {
	    $site_name = esc_html(get_bloginfo('name'));
	    $html .= '<a href="' . esc_url(apply_filters('themify_logo_home_url', themify_home_url())) . '" title="' . esc_attr($site_name) . '">';
	    $type = $logo_is_image === true ? 'image' : 'text';
	    if ('image' === $type) {
            $html .= themify_get_image(
                array(
                    'src' => themify_get('setting-' . $location . '_image_value', '', true),
		    'preload'=>Themify_Enqueue_Assets::$isFooter === false,
                    'w'=>themify_get('setting-' . $location . '_width', '', true),
                    'h'=>themify_get('setting-' . $location . '_height', '', true) ,
                    'alt'=> $site_name,
		    'lazy_load'=>Themify_Enqueue_Assets::$isFooter === false,
		    'attr'=>Themify_Enqueue_Assets::$isFooter === false?array('importance'=>'high'):null,
                    'class'=> 'site-logo-image'
                ));
		$html .= is_customize_preview() ? '<span style="display: none;">' . $site_name . '</span>' : '';
	    } else {
		$html .= isset($themify_customizer) ? '<span>' . $site_name . '</span>' : $site_name;
	    }
	    $html .= '</a>';
	} else {
	    $type = 'customizer';
	    $html .= $themify_customizer->site_logo($cssid);
	}

	$html .= '</' . $logo_tag . '>';

	return apply_filters('themify_' . $location . '_logo_html', $html, $location, $logo_tag, $type);
    }

endif;

if (!function_exists('themify_site_description')) :

    /**
     * Returns site description markup.
     *
     * @since 1.3.2
     *
     * @return string
     */
    function themify_site_description() {

		global $themify_customizer;
        $html = $themify_customizer->site_description(get_bloginfo('description'));
        if(!empty($html)){
			Themify_Enqueue_Assets::loadThemeStyleModule('site-description');
			$html = '<div id="site-description" class="site-description">'.$html.'</div>';
        }
        /**
         * Filters description markup before it's returned.
         *
         * @param string $html
         */
        return apply_filters('themify_site_description', $html);
    }

endif;

if (!function_exists('themify_zoom_icon')) :

    /**
     * Returns zoom icon markup for lightboxed featured image
     *
     * @param bool $echo
     *
     * @return mixed|void
     */
    function themify_zoom_icon($echo = true) {
	$zoom = apply_filters('themify_zoom_icon', themify_check('lightbox_icon') ? '<span class="zoom">'.themify_get_icon('search','ti',false,false,array('aria-label'=>__('Zoom','themify'))).'</span>' : '');
	if ($echo !== true) {
	    return $zoom;
	}
	echo $zoom;
    }

endif;

if (!function_exists('themify_register_grouped_widgets')) :

    /**
     * Registers footer sidebars.
     *
     * @param array  $columns Sets of sidebars that can be created.
     * @param array  $widget_attr General markup for widgets.
     * @param string $widgets_key
     * @param string $default_set
     */
    function themify_register_grouped_widgets($columns = array(), $widget_attr = array(), $widgets_key = 'setting-footer_widgets', $default_set = 'footerwidget-3col') {
	if (empty($columns)) {
	    $columns = array(
		'footerwidget-4col' => 4,
		'footerwidget-3col' => 3,
		'footerwidget-2col' => 2,
		'footerwidget-1col' => 1,
		'none' => 0
	    );
	}
	$option = themify_get($widgets_key,$default_set,true);
	if (isset($columns[$option])) {
	    if (empty($widget_attr)) {
		$widget_attr = array(
		    'sidebar_name' => __('Footer Widget', 'themify'),
		    'sidebar_id' => 'footer-widget',
		    'before_widget' => '<div id="%1$s" class="widget %2$s">',
		    'after_widget' => '</div>',
		    'before_title' => '<h4 class="widgettitle">',
		    'after_title' => '</h4>',
		);
	    }
	    for ($x = 1; $x <= $columns[$option]; ++$x) {
		register_sidebar(array(
		    'name' => $widget_attr['sidebar_name'] . ' ' . $x,
		    'id' => $widget_attr['sidebar_id'] . '-' . $x,
		    'before_widget' => $widget_attr['before_widget'],
		    'after_widget' => $widget_attr['after_widget'],
		    'before_title' => $widget_attr['before_title'],
		    'after_title' => $widget_attr['after_title']
		));
	    }
	}
    }

endif;

if (!function_exists('themify_the_footer_text')) :

    /**
     * Outputs footer text
     *
     * @param string $area Footer text area
     * @param bool   $wrap Class to add to block
     * @param string $block The block of text this is
     * @param string $date_fmt Date format for year shown
     * @param bool   $echo Whether to echo or return the markup
     *
     * @return mixed|string|void
     * @internal param string $key The footer text to show. Default: left
     */
    function themify_the_footer_text($area = 'left', $wrap = true, $block = '', $date_fmt = 'Y', $echo = true) {
	if (themify_check('setting-footer_text_' . $area . '_hide', true)) {
	    return;
	}
	// Prepare variables
	if ('' == $block) {
	    if ('left' === $area) {
		$block = 'one';
	    } elseif ('right' === $area) {
		$block = 'two';
	    }
	}
	$text_block = '';
	if ('one' === $block) {
	    $text_block = '&copy; <a href="' . esc_url(home_url()) . '">' . esc_html(get_bloginfo('name')) . '</a> ' . date($date_fmt);
	} elseif ('two' === $block) {
	    $text_block = __('Powered by <a href="http://wordpress.org">WordPress</a> &bull; <a href="https://themify.me">Themify WordPress Themes</a>', 'themify');
	}
	$key = 'setting-footer_text_' . $area;
	$text = themify_get($key, '', true);
	// Get definitive text to display, parse through WPML if available
	if ('' != $text) {
	    if (function_exists('icl_t')) {
		$text = icl_t('Themify', $key, $text);
	    }
	} else {
	    $text = $text_block;
	}
	// Start markup
	$html = apply_filters('themify_footer_text' . $block, $text);
	if (true === is_bool($wrap) && true === $wrap) {
	    $html = '<div class="' . esc_attr($block) . '">' . $html . '</div>';
	} elseif (!is_bool($wrap)) {
	    $html = '<div class="' . esc_attr($wrap) . '">' . $html . '</div>';
	}
	$html = apply_filters('themify_the_footer_text_' . $area, $html);

	$html = str_replace( '%year%', wp_date( 'Y' ), $html );

	if ($echo)
	    echo $html;
	return $html;
    }

endif;

if (!function_exists('themify_get_author_link')) :

    /**
     * Builds the markup for the entry author with microdata information.
     * @return string
     * @since 1.7.4
     */
    function themify_get_author_link() {
	return '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '" rel="author">' . get_the_author() . '</a></span>';
    }

endif;

if (!function_exists('themify_pagenav')) :

    /**
     * Echoes Pagination
     *
     * @param string $before
     * @param string $after
     * @param bool   $query
     */
    function themify_pagenav($before = '', $after = '', $query = false) {
	echo themify_get_pagenav($before, $after, $query);
    }

endif;

if (!function_exists('themify_get_pagenav')){

    /**
     * Returns Pagination
     * @param string $before Markup to show before pagination links
     * @param string $after Markup to show after pagination links
     * @param object $query WordPress query object to use
     * @param original_offset number of posts configured to skip over
     * @return string
     * @since 1.2.4
     */
    function themify_get_pagenav($before = '', $after = '', $query = false,$max_page = 0,$paged=0) {
	$key=false;
	if ($max_page===0 ) {
	    if(empty( $query )){
		global $wp_query;
		$query = $wp_query;
	    }
	    $max_page = (int)$query->max_num_pages;
	}
	elseif(!empty( $query ) && is_string($query)){
	    $key=$query;
	}
	if ($max_page > 1) {
	    if($paged===0){
			$paged = get_query_var('paged',1);
			if (empty($paged)) {
				$paged=get_query_var( 'page',1 );
			}
	    }
		$paged = (int)$paged;
        $paged=$paged<1?1:$paged;
	    $pages_to_show = apply_filters('themify_filter_pages_to_show', 4);
	    $pages_to_show_minus_1 = $pages_to_show - 1;
	    $half_page_start = floor($pages_to_show_minus_1 / 2);
	    $half_page_end = ceil($pages_to_show_minus_1 / 2);
	    $start_page = (int)($paged - $half_page_start);
	    if ($start_page <= 0) {
			$start_page = 1;
	    }
	    $end_page = (int)($paged + $half_page_end);
	    if (($end_page - $start_page) !== $pages_to_show_minus_1) {
			$end_page = (int)($start_page + $pages_to_show_minus_1);
	    }
	    if ($end_page > $max_page) {
			$start_page = (int)($max_page - $pages_to_show_minus_1);
			$end_page = (int)$max_page;
	    }
	    $prefetch = '';
	    if(Themify_Enqueue_Assets::$themeVersion!==null){
			Themify_Enqueue_Assets::loadThemeStyleModule('pagenav');
	    }
	    $out = $before . '<div class="pagenav tf_clear tf_box tf_textr tf_clearfix">';
	    if($start_page <= 0){
			$start_page=1;
	    }
	    elseif ($start_page >= 2 && $pages_to_show < $max_page) {
			$first_page_text = '&laquo;';
			$link=$key!==false?add_query_arg( array($key=> 1 ) ):get_pagenum_link();
			$out .= '<a href="' . $link . '" title="' . $first_page_text . '" class="number firstp">&laquo;</a>';
	    }
	    if ($paged > 1 && $pages_to_show < $max_page) {

				$link=$key!==false?add_query_arg( array($key=> ($paged - 1) ) ):get_pagenum_link($paged -1);
				$prefetch = '<link rel="prefetch" as="document" href="' . $link . '"/>';
				$attr = apply_filters( 'previous_posts_link_attributes', '' );
				$out .= '<a href="' . $link. '" '.$attr.' class="number prevp">&lsaquo;</a>';
	    }

	    for ($i = $start_page; $i <= $end_page; ++$i) {
			if ($i === $paged) {
				$out .= ' <span class="number current">' . $i . '</span>';
			}
			else {
				$link=$key!==false?add_query_arg( array($key=> $i ) ):get_pagenum_link($i);
				$out .= ' <a href="' . $link. '" class="number">' . $i . '</a>';
			}
	    }
	    if (($paged + 1) < $max_page && $pages_to_show < $max_page) {
			$link=$key!==false?add_query_arg( array($key=> ($paged + 1) ) ):get_pagenum_link($paged + 1);
			$prefetch .= '<link rel="prefetch" as="document" href="' . $link . '"/>';
			$attr = apply_filters( 'next_posts_link_attributes', '' );
			$out .= '<a href="' . $link. '" '.$attr.' class="number nextp">&rsaquo;</a>';
	    }
	    if ($end_page < $max_page) {
			$last_page_text = '&raquo;';
			$link=$key!==false?add_query_arg( array($key=> $max_page ) ):get_pagenum_link($max_page);
			$out .= '<a href="' . $link. '" title="' . $last_page_text . '" class="number lastp">&raquo;</a>';
	    }
	    $out .= '</div>' . $after;
	    return $prefetch . $out;
	}
	return '';
    }

}

if (!function_exists('themify_has_post_video')){

    /**
     * Check if current post has featured video
     * Must be used inside the loop
     *
     * @since 2.7.3
     */
    function themify_has_post_video() {
	return themify_check('video_url');
    }

}

if (!function_exists('themify_area_design')){

    /**
     * Checks the area design setting and returns 'none' or a design option.
     *
     * @since 2.1.3
     *
     * @param string $key Main prefix for setting and field.
     * @param array $args
     *
     * @return mixed
     */
    function themify_area_design($key = 'header', $args = array()) {
	if (!isset($args['setting'])) {
	    $args['setting'] = 'setting-' . $key . '_design';
	}
	if ( ! isset($args['field']) && ! ( is_singular() || themify_is_shop() ) ) {
		$args['field'] = $args['setting'];
	}
	if (!isset($args['field'])) {
	    $args['field'] = $key . '_design';
	}
	if (!isset($args['default'])) {
	    $args['default'] = $key . '-horizontal';
	}
	if (!isset($args['values'])) {
	    $args['values'] = array('header-horizontal', 'header-block', 'none');
	}
	$design = themify_get_both($args['field'],$args['setting'], $args['default']);
	if ($design!=='default' && !in_array($design, $args['values'],true)) {
	    $design='default';
	}
	return $design;
    }

}

/**
 * Returns current active theme skin name, false if no skin is active
 *
 * @return string|bool
 */
function themify_get_skin() {
    static $skin = null;

    if ( $skin === null ) {
		$value = themify_get( 'skin', 'default', true );
		/* backward compatibility, "skin" used to be saved as a URL */
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$parsed_skin = parse_url( $value, PHP_URL_PATH );
			$value = basename( dirname( $parsed_skin ) );
		}

		if ( 'default' !== $value && is_file( get_template_directory() . "/skins/{$value}/style.css" ) ) {
			$skin = $value;
		} else {
			$skin = false;
		}
    }

    return $skin;
}

/**
 * Enqueues the chosen skin, if there was one selected and custom_style.css if it exists
 * 1. themify-skin
 * 2. custom-style
 * 3. fontawesome - added 1.7.8
 * @since 1.7.4
 */
function themify_enqueue_framework_assets() {
    // Skin stylesheet
	do_action('themify_before_skin_css');
    if ($skin = themify_get_skin()) {
		$url = THEME_URI. '/skins/' . $skin;
		$dir = THEME_DIR. '/skins/' . $skin;
		themify_enque_style('themify-skin', $url . '/style.css', null, THEMIFY_VERSION);
		if (is_file($dir . '/media//mobile-menu.css')) {
			Themify_Enqueue_Assets::addMobileMenuCss('mobile-menu-' . $skin, $url . '/media/mobile-menu.css');
		}
		if (is_rtl() && is_file($dir . '/rtl.min.css')) {
			themify_enque_style('themify-skin-rtl', $url . '/rtl.min.css', null, THEMIFY_VERSION);
		}
    }
	do_action('themify_after_skin_css');
}

if (!function_exists('themify_get_term_description')){

    /**
     * Returns term description
     * @return string
     * @since 1.5.6
     */
    function themify_get_term_description() {
	$term_description = term_description();
	$output = !empty($term_description) ? '<div class="category-description">' . $term_description . '</div>' : '';
	return apply_filters('themify_get_term_description', $output);
    }

}

if (!function_exists('themify_permalink_attr')){

    /**
     * Returns escaped URL for post
     * @return string
     * @since 1.3.5
     */
    function themify_permalink_attr($args=array(),$echo=true) {
	    $cl='';
	    $rel='';
	    $isLightbox=false;
	    if(!is_array($args)){
		$args=array();
	    }
	    if(isset($_GET['post_in_lightbox'])){
			$link = get_permalink();
	    }
	    else{
			$link = themify_get('external_link', '');
			if ($link === '') {
				$isIframe=false;
				if(isset($args['use_video_link']) && $args['use_video_link']===true){
				$link = themify_get( 'video_url','' );
				}
				if($link===''){
					$link = themify_get('lightbox_link', '');
					if ($link === '') {
						$link = themify_get('link_url', '');
						if ($link==='' && (!isset($args['no_permalink']) || $args['no_permalink']===false)) {
						$link = get_permalink();
						}
						$isLightbox = !is_single() && themify_check('setting-open_inline', true);
					} else {
						$isLightbox = true;
						$cl='themify_lightbox';
					}
				}
				else{
				$isLightbox=$isIframe=true;
				}
				if ($isLightbox === true && (!isset($args['disable_lightbox']) || $args['disable_lightbox']===false) && $cl!=='themify_lightbox') {
				$queryArgs=array('post_in_lightbox' => 1);
				$cl='themify_lightbox';
				if ($isIframe===true || themify_check( 'iframe_url' ) ) {
					$queryArgs['iframe'] = 1;
				}
				$link = add_query_arg($queryArgs, $link);
				}
			}
			elseif(!isset($args['new_tab']) || $args['new_tab']===true){
				$rel=true;
			}
	    }
	    $link = apply_filters('themify_get_permalink', $link);
	    if (!empty($args['link_class']) && $args['link_class']!==$cl) {
		if($isLightbox===false){
		    $args['link_class'] = str_replace('themify_lightbox','',$args['link_class']);
		}
		if($cl!=='' && stripos($args['link_class'],$cl)!==false){
		    $cl=$args['link_class'];
		}
		else{
		    $cl.=' '.$args['link_class'];
		}
	    }
	    $rel=$rel===true && $isLightbox===false?'target="_blank" rel="noopener"':'';
		$result=array('href'=>$link,'cl'=>trim($cl),'r'=>$rel,'l'=>$isLightbox);

		if($echo===false){
			return $result;
		}
		echo 'href="'.$result['href'].'"';
		if($result['cl']!==''){
			echo ' class="'.$result['cl'].'"';
		}
		if($result['r']!==''){
			echo ' '.$result['r'];
		}
	}
}

if (!function_exists('themify_theme_feed_link')){

    /**
     * Returns the feed link, usually RSS
     * @param string $setting
     * @param bool $echo
     * @return mixed|void
     * @since 1.5.2
     */
    function themify_theme_feed_link($setting = 'setting-custom_feed_url', $echo = true) {
	$out=themify_get($setting,'',true);
	if($out===''){
	    $out=get_bloginfo('rss2_url');
	}
	$out = esc_url(apply_filters('themify_theme_feed_link', $out));
	if ($echo) {
	    echo $out;
	}
	return $out;
    }

}

if (!function_exists('themify_theme_feed')){

    /**
     * Returns the feed html
     * @param array $args
     * @return void
     * @since 1.5.2
     */
    function themify_theme_feed($args=array()) {
	$key=isset($args['key'])?$args['key']:'setting-exclude_rss';
	if(!themify_check($key,true)){
	    $key=isset($args['feed_key'])?$args['feed_key']:'setting-custom_feed_url';
	    $text=isset($args['text'])?$args['text']:__('RSS','themify');
	    ?>
	    <div class="rss<?php if(isset($args['class'])):?> <?php echo $args['class']?><?php endif;?>"><a<?php if(isset($args['link_class'])):?> class="<?php echo $args['link_class']?>"<?php endif;?> href="<?php themify_theme_feed_link($key) ?>"><?php if(isset($args['icon'])):?><?php echo themify_get_icon($args['icon'],false,false,false,array('aria-label'=>__('RSS','themify')))?><?php endif;?><?php echo !empty($text)?$text:'<span class="screen-reader-text">'.__('RSS','themify').'</span>';?></a></div>
	    <?php
	}
    }
}
if (!function_exists('themify_is_query_page')){

    /**
     * Checks if current page is a query category page
     * @return bool
     * @since 1.3.8
     */
    function themify_is_query_page() {
	static $is = NULL;
	if ($is === null) {
	    global $themify;
	    $is = isset($themify->query_category) && '' !== $themify->query_category;
	}

	return $is;
    }

}

if (!function_exists('themify_post_media')){

    /**
     * Display post video or the featured image
     *
     * @since 2.7.7
     */
    function themify_post_media($args = array()) {
		global $themify;
		if ($themify->hide_image !== 'yes') {
			$isImage = 'yes' !== $themify->unlink_image || isset( $_GET['post_in_lightbox']) || (isset($args['unlink']) && false===$args['unlink']);
			if(isset($args['image'])){
			    $post_image=$args['image'];
			}
			else{
			    $post_image = isset($args['use_video_link']) && $args['use_video_link']===true?'':themify_post_video(false, false);
			    //check if there is a video url in the custom field
			    if ($post_image==='') {
					$params=array('w'=>$themify->width,'h'=>$themify->height);
					if(isset($args['lazy_load'])){
					    $params['lazy_load']=$args['lazy_load'];
					}
					if(isset($args['preload'])){
					    $params['preload']=$args['preload'];
					}
					if(isset($args['prefetch'])){
					    $params['prefetch']=$args['prefetch'];
					}
					if(isset($args['alt'])){
					    $params['alt']=$args['alt'];
					}
					if(isset($args['title'])){
					    $params['title']=$args['title'];
					}
					if(isset($args['image_class'])){
						$params['image_class']=$args['image_class'];
					}
					if(Themify_Builder::$frontedit_active===true && isset($themify->builder_post_module) && false){
						$params['attr']=array('data-w'=>'img_width_'.$themify->builder_post_module, 'data-h'=>'img_height_'.$themify->builder_post_module,'data-repeat'=>'');
					}
				    $post_image = themify_get_image($params);
					unset($params);
				    if($post_image==='' && isset($args['use_video_link']) && $args['use_video_link']===true){
					    $post_image=themify_fetch_video_image(themify_get( 'video_url' ) );
				    }
			    } else {
				    $isImage = false;
			    }
			}
			if($isImage===true){
			    $link_attr=themify_permalink_attr($args,false);
			}
			if (isset($args['before'])) {
			    echo $args['before'];
			}
            if ($post_image!=='') {
				if ( ! isset( $args['no_hook'] ) ) {
                    themify_before_post_image(); // Hook
                }
			?>
			<figure class="<?php echo isset($args['class']) ? $args['class'] : 'post-image tf_clearfix'?><?php if($isImage===false):?> is_video<?php endif;?>">
			    <?php
				if (isset($args['before_image'])) {
				    echo $args['before_image'];
				}
			    ?>
				<?php if ($isImage === true): ?>
				<a href="<?php echo $link_attr['href']?>"<?php if($link_attr['cl']!==''):?> class="<?php echo $link_attr['cl']?>"<?php endif;?><?php if($link_attr['r']!==''):?> <?php echo $link_attr['r']?><?php endif;?>>
				<?php endif; ?>
				<?php echo $post_image; ?>
				<?php if ($isImage === true): ?>
				    <?php if($link_attr['l']===true):?>
					<?php themify_zoom_icon(); ?>
				    <?php endif;?>
				</a>
				<?php endif; ?>
			    <?php
				if (isset($args['after_image'])) {
				    echo $args['after_image'];
				}
			    ?>
			</figure>
			<?php
                if ( ! isset( $args['no_hook'] ) ) {
                    themify_after_post_image(); // Hook
                }
			}
			if (isset($args['after'])) {
			    echo $args['after'];
			}
		}
    }

    function themify_fetch_video_image($video_url,$url_only=false) {
		if ( empty( $video_url ) ) {
			return '';
		}
	$image_url=$title='';
	if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match ) ) {
		$video_id = $match[1];
		$image_url = 'http://img.youtube.com/vi/'.$video_id.'/hqdefault.jpg';
		$title=get_the_title();
	}
	elseif ( false !== stripos( $video_url, 'vimeo' ) ) {
		$video_url=trim($video_url);
		$key='tf_vimeo_'.$video_url;
		$data = Themify_Storage::get($key);
		if(!$data){
		    $request = wp_remote_get( 'http://vimeo.com/api/oembed.json?url='.urlencode( $video_url ) );
		    $response_body = wp_remote_retrieve_body( $request );
		    if ( '' != $response_body ) {
			$vimeo = json_decode( $response_body );
			$image_url = $vimeo->thumbnail_url;
			$title = str_replace('"',"'",$vimeo->title);
			Themify_Storage::set( $key,array('t'=>$title,'u'=>$image_url),MONTH_IN_SECONDS);
		    }
		}
		else{
			$data = json_decode( $data, true );
		    $image_url = $data['u'];
		    $title=$data['t'];
		}
	}
	if($url_only===true){
	    return $image_url;
	}
	global $themify;
	$title=esc_attr($title);
	return '<img alt="' . $title . '"   title="' . $title . '" src="' . themify_https_esc( $image_url ) . '" width="' . $themify->width . '">';

    }
}
if (!function_exists('themify_get_featured_image_link')){//deprecated from 2020.06.02
    function themify_get_featured_image_link(){}
}

if (!function_exists('themify_post_content')){

    function themify_post_content($showedit = true) {

	    global $themify;
		$iseditable=false && Themify_Builder::$frontedit_active===true && $themify->display_content !== 'none';//temprorary disable
		$more_text = themify_get( 'setting-default_more_text', __('More &rarr;', 'themify'), true );
		if ( function_exists( 'icl_t' ) ) {
			$more_text = icl_t( 'Themify', 'setting-default_more_text', $more_text );
		}
	?>
	<div class="entry-content<?php if($iseditable===true && $themify->display_content==='content'):?> tb_editor_enable<?php endif;?>"<?php if($iseditable===true):?> data-type="<?php echo $themify->display_content?>" contenteditable="false"<?php endif;?>>

        <?php if ('excerpt' === $themify->display_content && (!is_attachment() || isset($themify->post_module_hook))) : ?>

		<?php
                themify_before_post_content();
		the_excerpt();
		themify_after_post_content();
                ?>

		<?php if (themify_check('setting-excerpt_more', true) && (!is_single() || isset($themify->post_module_hook))) : ?>

		    <p><a href="<?php $link=themify_permalink_attr(array(),false); echo $link['href']; ?>" class="more-link"><?php echo $more_text; ?></a></p>

		<?php endif; ?>

	    <?php elseif ($themify->display_content !== 'none'): ?>
		<?php
		    if(!is_single()){
			global $more; $more = 0; //enable more link
		    }
		?>
		<?php
                themify_before_post_content();
		the_content( $more_text );
                themify_after_post_content(); ?>

	    <?php endif; //display content ?>

	</div><!-- /.entry-content -->
	<?php
	if ($showedit === true) {
	    themify_edit_link();
	}
    }

}



if (!function_exists('themify_post_title_tag')){

    /**
     * Get the HTML tag to be used for post titles
     *
     * @since 2.7.7
     * @return string
     */
    function themify_post_title_tag() {
	global $themify;
	$tag = !empty($themify->themify_post_title_tag)?$themify->themify_post_title_tag:((empty($themify->is_shortcode) && empty($themify->is_builder_loop) && is_singular()) ? 'h1' : 'h2');

	return apply_filters('themify_post_title_tag', $tag);
    }

}

if (!function_exists('themify_post_title')){

    /**
     * Template tag to display the post title
     *
     * uses themify_parse_args to filter the $args
     */
    function themify_post_title($args = array()) {
	global $themify;
	if ($themify->hide_title !== 'yes' || (isset($args['show_title']) && $args['show_title']===true)) {
	    $posfix = !empty($themify->post_module_hook) ? '_module' : '';
	    $html = '';
	    if (!isset($args['unlink'])) {
			$args['unlink'] = isset( $_GET['post_in_lightbox'])?false:(isset($themify->unlink_title) && $themify->unlink_title === 'yes');
	    }
	    if (!isset($args['tag'])) {
		$args['tag'] = themify_post_title_tag();
	    }
	    if (!isset($args['class'])) {
		$args['class'] = 'post-title entry-title';
	    }
	    $args=apply_filters( 'themify_post_title_args', $args );
        if(!isset($args['no_hook'])){
			ob_start();
            themify_before_post_title($posfix); // Hook
			$html= ob_get_clean();
        }
	    $html .= '<' . $args['tag'];
	    if($args['class']!==''){
			$html.=' class="' . $args['class'] . '"';
	    }
		if(false && Themify_Builder::$frontedit_active===true && $args['unlink'] === true){//temprorary disable
			$html .=' contenteditable="false" data-type="title"';
		}
	    $html.='>';
	    if (isset($args['before_title'])) {
			$html .= $args['before_title'];
	    }
	    if ($args['unlink'] !== true) {
			$link=themify_permalink_attr($args,false);
			$html .= '<a href="' . $link['href'] . '"';
			if($link['cl']!==''){
				$html .= ' class="' . $link['cl'] . '" ';
			}
			if($link['r']!==''){
				$html .= $link['r'];
			}
			if(false && Themify_Builder::$frontedit_active===true){//temprorary disable
				$html .=' contenteditable="false" data-type="title"';
			}
			$html .= '>';
	    }
	    $html .= isset($args['title'])?$args['title']:the_title('', '', false);
	    if ($args['unlink'] !== true) {
			if($link['l']===true && isset($args['zoom']) && $args['zoom']===true){
				$html.= themify_zoom_icon(false);
			}
			$html .= '</a>';
	    }
	    if (isset($args['after_title'])) {
		$html .= $args['after_title'];
	    }
	    $html .= '</' . $args['tag'] . '>';
	    if (isset($args['before'])) {

		$html = $args['before'] . $html;
	    }
	    if (isset($args['after'])) {
			$html = $html . $args['after'];
	    }

        if(!isset($args['no_hook'])){
			ob_start();
            themify_after_post_title($posfix); // Hook
			$html.=ob_get_clean();
        }

	    $html=apply_filters( 'themify_post_title_html',$html, $args );
	    if (isset($args['echo']) && $args['echo'] !== true) {
		return $html;
	    }
	    echo $html;
	}
    }

}


if (!function_exists('themify_comments_popup_link')){

    /**
     * Generate the popup comment link
     *
     * @since 2.9.9
     */
    function themify_comments_popup_link(array $args=array()){
	    global $themify;
		if($themify->hide_meta!=='yes' && ( !isset( $themify->hide_meta_comment ) || $themify->hide_meta_comment!=='yes' ) && comments_open()){
			$post_type=get_post_type();
			if(( $post_type==='post' && themify_check( 'setting-comments_posts',true ) ) || ( $post_type==='portfolio' && !themify_check( 'setting-portfolio_comments',true ) )){
				return;
			}
		}
	    else{
		return;
	    }
	    $args = array_merge(array(
		    'zero' => __( '0','themify' ),
		    'one' => __( '1','themify' ),
		    'more' => __( '%','themify' ),
		    'class' => 'post-comment',
		    'icon' => '',
	    ),$args);
		?>
        <span class="<?php echo $args['class']; ?>">
	       <?php comments_popup_link($args['zero'],$args['one'],$args['more'] );
		   if($args['icon']!==''){
			   echo themify_get_icon( $args['icon'] );
		   }
		   ?>
	    </span>
		<?php
	}

}

if (!function_exists('themify_author_bio')) {

    /**
     * Display author biography, used in author archive pages
     *
     * @since 3.0.8
     */
    function themify_author_bio() {
	global $author, $author_name;

	$curauth = ( isset($_GET['author_name']) ) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
	$author_url = $curauth->user_url;
	?>
	<div class="author-bio tf_clearfix">
	    <p class="author-avatar"><?php echo get_avatar($curauth->user_email, 200); ?></p>
	    <h2 class="author-name">
		<?php printf(__('About <span>%s</span>', 'themify'), $curauth->display_name); ?></span>
	    </h2>
	    <?php if ($author_url != '') : ?>
	        <p class="author-url">
	    	<a href="<?php echo esc_attr($author_url); ?>"><?php echo esc_html($author_url); ?></a>
	        </p>
	    <?php endif; //author url ?>
	    <div class="author-description">
		<?php echo $curauth->user_description; ?>
	    </div><!-- /.author-description -->
	</div><!-- /.author bio -->

	<h2 class="author-posts-by"><?php printf(__('Posts by %s %s', 'themify'), $curauth->first_name, $curauth->last_name); ?>:</h2>
	<?php
    }

}

if (!function_exists('themify_loop_get_context')){

    /**
     * Get current context, where the template file is being rendered in.
     * Used mainly in loop.php template file, since this template file can be called to render inside itself
     *
     * @return string|NULL
     * @since 3.1.1
     */
    function themify_loop_get_context($type = 'post') {
		global $themify;

		if (!empty($themify->is_shortcode)) {
			return 'shortcode';
		}
		if (!empty($themify->is_builder_loop)) {
			return 'builder';
		}
		if (is_singular($type)) {
			return 'single';
		}
    }

}

if (!function_exists('themify_loop_is_singular')){

    /**
     * Check if current context is "single post page" and not inside a shortcode or Builder module
     * Note: this may return false even if is_singular( $type ) returns true, using this function context matters.
     *
     * @return bool
     * @since 3.1.1
     */
    function themify_loop_is_singular($type = 'post') {
	return 'single' === themify_loop_get_context($type);
    }

}

if (!function_exists('themify_open_link')){

    /**
     * Create the opening <a> tag, links to the permalink by default
     *
     * @return string
     */
    function themify_open_link($args = array()) {
	$args = wp_parse_args($args, array(
	    'no_permalink' => false, // if there is no lightbox link, don't return a link
	    'class' => '', // additional classes to attach to link
	    'echo' => false,
	    'link' => '',
	    'lightbox' => false,
	));
	$attr = array(
	    'class' => $args['class']
	);
	$link = $args['link'];
	if (empty($link)) {
	    if (themify_get('external_link') != '') {
		$link = esc_url(themify_get('external_link'));
	    } elseif (themify_get('lightbox_link') != '') {
		$link = esc_url(themify_get('lightbox_link'));
		$args['lightbox'] = true;
	    } elseif (themify_check('link_url')) {
		$link = themify_get('link_url');
	    } elseif ($args['no_permalink']) {
		$link = '';
	    } else {
		$link = get_permalink();
		if (current_theme_supports('themify-post-in-lightbox')) {
		    if (!is_single() && '' != themify_get('setting-open_inline')) {
			$args['lightbox'] = true;
		    }
		    if (themify_is_query_page()) {
			if ('no' === themify_get('post_in_lightbox')) {
			    $link = get_permalink();
			} else {
			    $args['lightbox'] = true;
			}
		    }
		    if ($args['lightbox'] === true) {
			$link = add_query_arg(array('post_in_lightbox' => 1), get_permalink());
		    }
		}
	    }
	}
	$attr['href'] = $link;
	if ($args['lightbox'] === true) {
	    $attr['class'] .= ' themify_lightbox';
	}

	$link = sprintf('<a%s>', themify_get_element_attributes($attr));

	if ($args['echo'])
	    echo $link;

	return $link;
    }

}

if (!function_exists('themify_get_categories_as_classes')){

    /**
     * Returns a CSS-formatted string of categories assigned to current post
     *
     * @return string
     */
    function themify_get_categories_as_classes($post_id) {
	$categories = wp_get_post_categories($post_id);
	$class = '';
	foreach ($categories as $cat)
	    $class .= ' cat-' . $cat;

	return $class;
    }

}

if (!function_exists('themify_the_terms')){

    /**
     * Retrieve a post's terms as a list with specified format.
     * Based on get_the_term_list()
     *
     * @since 3.4.7
     *
     * @param int $id Post ID.
     * @param string $taxonomy Taxonomy name.
     * @param string $before Optional. Before list.
     * @param string $sep Optional. Separate items using this.
     * @param string $after Optional. After list.
     * @return false|void False on WordPress error.
     */
    function themify_the_terms($id, $taxonomy, $before = '', $sep = '', $after = '') {
	$terms = get_the_terms($id, $taxonomy);

	if (is_wp_error($terms) || empty($terms)){
	    return $terms;
	}


	$links = array();

	foreach ($terms as $term) {
	    $link = get_term_link($term, $taxonomy);
	    if (is_wp_error($link)) {
		return $link;
	    }
	    $links[] = '<a href="' . esc_url($link) . '" rel="tag" class="term-' . $term->slug . '">' . $term->name . '</a>';
	}

	/**
	 * Filters the term links for a given taxonomy.
	 *
	 * The dynamic portion of the filter name, `$taxonomy`, refers
	 * to the taxonomy slug.
	 *
	 * @param array $links An array of term links.
	 */
	$term_links = apply_filters("term_links-{$taxonomy}", $links);

	$term_links = $before . implode($sep, $term_links) . $after;
	/**
	 * Filters the list of terms to display.
	 *
	 * @param array  $term_list List of terms to display.
	 * @param string $taxonomy  The taxonomy name.
	 * @param string $before    String to use before the terms.
	 * @param string $sep       String to use between the terms.
	 * @param string $after     String to use after the terms.
	 */
	echo apply_filters('the_terms', $term_links, $taxonomy, $before, $sep, $after);
    }
}


if (!function_exists('themify_meta_taxonomies')) :

    function themify_meta_taxonomies($taxonomy='',$sep='',$before='',$after=''){
	global $themify;
	if($themify->hide_meta !== 'yes' && (!isset($themify->hide_meta_category ) || $themify->hide_meta_category !== 'yes')){
	    if($taxonomy===''){
		$taxonomy=get_post_type();
		if($taxonomy==='post'){
		    $taxonomy='category';
		}
		elseif($taxonomy==='product'){
		    $taxonomy='product_cat';
		}
		else{
		    $taxonomy.='-category';
		}

	    }
	    if($sep===''){
		$sep= ', ';
	    }
	    if($before===''){
		$before='<span class="post-category">';
	    }
	    if($after===''){
		$after='</span>';
	    }
	    themify_the_terms( get_the_ID(), $taxonomy,$before, $sep, $after);
	}
    }

endif;

if (!function_exists('themify_page_description')) :

    /**
     * Display page description depending on context
     *
     * @since 4.2.2
     * @return string
     */
    function themify_page_description() {
	if (is_author()) {
	    themify_author_bio();
	} elseif (is_category() || is_tag() || is_tax()) {
	    echo themify_get_term_description();
	}
    }

endif;

if(!function_exists('themify_get_title')){
    /**
    * Display page title depending on context
    *
    * @since 4.2.2
    * @return string
    */
    function themify_get_title($args = array()) {

       $title = '';

	if(themify_is_page() && !is_404()){
	     $title=get_the_title();
	}
	elseif (is_search()) {
	     $title = sprintf(__('<span class="page_title_prefix">Search Results for: </span><em>%s</em>', 'themify'), get_search_query());
	} elseif (is_category() || is_tag() || is_tax()) {
	    $title = single_cat_title('', false);
	} elseif (is_author()) {
	    $title = get_the_author();
	}
	elseif(is_date()){
	    $useLabel=isset($args['use_date_labels']) && $args['use_date_labels']===true;
	    $label='';
	    if (is_year()) {
		$title = _x('Y', 'yearly archives date format', 'themify');
		if($useLabel===true || isset($args['label_year'])){
		    $label=isset($args['label_year'])?$args['label_year']:__( 'Yearly Archives: %s', 'themify' );
		}
	    } elseif (is_month()) {
		$title = get_the_date(_x('F Y', 'monthly archives date format', 'themify'));
		if($useLabel===true || isset($args['label_month'])){
		    $label=isset($args['label_month'])?$args['label_month']:__( 'Monthly Archives: %s', 'themify' );
		}
	    } else{
		$title = get_the_date(_x('F j, Y', 'daily archives date format', 'themify'));
		if($useLabel===true || isset($args['day'])){
		    $label=isset($args['label_day'])?$args['label_day']:__( 'Daily Archives: %s', 'themify' );
		}
	    }
	    if($label!==''){
		$title=sprintf($label,$title);
	    }
	}
        elseif (is_post_type_archive()) {
	   $title = post_type_archive_title('', false);
	} elseif (is_404()) {
	     $title = __('404', 'themify');
	}

       if (!empty($title)) {
	   $args = themify_parse_args($args, array(
	       'tag' => 'h1',
	       'class' => 'page-title',
	       'before' => '',
	       'after' => '',
	       'before_title' => '',
	       'after_title' => '',
	    ), 'page_title');
	   $title= "{$args['before']} <{$args['tag']} itemprop=\"name\" class=\"{$args['class']}\">{$args['before_title']}{$title}{$args['after_title']} </{$args['tag']}>{$args['after']}";
       }
       return $title;
    }
}
if ( ! function_exists( 'themify_page_output' ) ) :
    function themify_page_output($args = array()) {

	$args = themify_parse_args($args, array(
	    'disable' => false, // whether page output should be disabled entirely
		), 'page_output');

	if ($args['disable'] !== false) {
	    themify_content_start();
	    themify_content_end();
	    return;
	}

	themify_content_start(); // hook
	if ((!isset($args['hide_page_content']) || $args['hide_page_content'] === false)) {
	    themify_page_content($args);
	}
	if (!is_404() && (!isset($args['hide_loop']) || $args['hide_loop'] === false)) {
	    global $themify;
	    $isPage = themify_is_page();
	    if ($isPage === false || $themify->query_category !== '') {
		if ($isPage === true) {
		    $themify->page_id = get_the_ID();
		}
		if ($isPage === true && $themify->query_category !== '' && themify_get('section_categories') === 'yes') {
		    if (!isset($args['hide_filter']) || $args['hide_filter'] === false) {
			themify_masonry_filter();
		    }
		    get_template_part('includes/category-section');
		} else {
		    // Query posts action based on global $themify options
		    do_action('themify_custom_query_posts', (isset($args['query_args']) ? $args['query_args'] : array()));
		    if (have_posts()) {
			if ((!isset($args['hide_filter']) || $args['hide_filter'] === false) && themify_masonry_filter()) {
			    $args['loop_class'] = !isset($args['loop_class']) ? array() : $args['loop_class'];
			    $args['loop_class'][] = 'masonry';
			} 
			if ($themify->query_category !== '' && current_user_can('edit_post', $themify->page_id)) {
				$post_type=$themify->query_post_type && $themify->query_post_type!=='page'?$themify->query_post_type:'post';
				$tabSlug=$post_type==='post' || $post_type==='product'?$post_type.'s':$post_type; 
				?>
			    <a class="tf_query_edit_link" href="<?php echo get_edit_post_link($themify->page_id) ?>#query-<?php echo $tabSlug?>t"><?php echo sprintf(__('Edit Query %s', 'themify'),ucfirst($post_type.'s')) ?></a>
			    <?php
			}
			themify_loop_output($args);
		    } 
		    elseif (is_search() || ($isPage === true && $themify->query_category !== '')) {
			echo '<p>', __('Sorry, nothing found.', 'themify'), '</p>';
		    }
		}
		//Reset query posts if it exist
		do_action('themify_reset_query');
		if ($isPage === true) {
		    unset($themify->page_id);
		}
	    }
	}
	themify_content_end();
    }

endif;

if(!function_exists('themify_masonry_filter')){
    function themify_masonry_filter($args=array()){
        global $themify;
        if (isset($themify) && ((isset($themify->post_layout) && 'slider' === $themify->post_layout) || empty($themify->post_filter) || $themify->post_filter === 'no')) {
            return false;
        }
        elseif(empty($args)){
            $args['query_category']=$themify->query_category;
            if(isset($themify->query_taxonomy)){
                $args['query_taxonomy']=$themify->query_taxonomy;
            }
        }
        themify_get_template( 'includes/filter',null,$args );
        return true;
        }
    }

function themify_set_loop_args(array $class,$post_type,$layout,$type='main'){
    $class=apply_filters( 'themify_loops_wrapper_class', $class,$post_type,$layout,$type);
    array_unshift($class,'loops-wrapper');
	$_args=array(
		'id' => 'loops-wrapper',
		'class' => $class
    );
	if(Themify_Builder::$frontedit_active===false){
		$_args['data-lazy']=1;
	}
    $container_props = apply_filters('themify_container_props', $_args, $post_type, $layout,$type);
    unset($_args);
    global $woocommerce_loop;
    if($type!=='main' || ($post_type==='product' && ((isset($woocommerce_loop['name']) && ($woocommerce_loop['name']==='related' || $woocommerce_loop['name']==='up-sells') )|| wc_get_loop_prop( 'is_shortcode' )))){
		unset($container_props['id']);
		$index=array_search('masonry',$container_props['class'],true);
		if($index!==false){
			unset($container_props['class'][$index]);
		}
		$index=array_search('infinite',$container_props['class'],true);
		if($index!==false){
			unset($container_props['class'][$index]);
		}
		$index=array_search('no-gutter',$container_props['class'],true);
		if($index!==false){
			unset($container_props['class'][$index]);
		}
		unset($container_props['data-layout'],$container_props['data-gutter']);
    }
    $container_props['class'][]='tf_clear tf_clearfix';
    $container_props['class']=implode(' ', $container_props['class']);
    return $container_props;
}


if(!function_exists('themify_loop_output')){
    function themify_loop_output($args=array()){
		global $themify;
		$post_type='';
		if($themify->query_category !== ''){
			if($themify->query_post_type!=='post' && $themify->query_post_type!=='page'){
				$post_type=$themify->query_post_type;
			}
		}
		elseif(is_tax() || is_post_type_archive()){
			if(is_post_type_archive( 'portfolio' ) || is_tax('portfolio-category')){
				$post_type='portfolio';
			}
			else{
				if(is_post_type_archive()){
					if(!themify_is_shop()){
						$post_type = get_query_var( 'post_type' );
						if ( is_array( $post_type ) ) {
							$post_type = reset( $post_type );
						}
					}
					else{
					    $post_type='product';
					}
				}
				elseif(is_tax()){
					$post_type=get_post_type();
				}
			}
		}
		if($post_type==='product'){
			themify_product_loop_output($args);
			return;
		}
		$class = isset($args['loop_class'])?$args['loop_class']:array();
		if($post_type!=='' && $post_type!=='category'&& $post_type!=='post_tag'){
			$class[] = $post_type;
		}
		else{
			$post_type='';
		}
		$templates=array();//we need array ,because get_post_type() can be different(e.g multiple post types attached to taxonomy
		$slug=isset($args['loop_template'])?$args['loop_template']:'includes/loop';
		do_action('themify_before_loop_output');
		?>
		    <div <?php echo themify_get_element_attributes(themify_set_loop_args($class, $post_type, $themify->post_layout));?>>
			<?php
			while (have_posts()) : the_post(); ?>
				<?php
				$post_type=get_post_type();
				do_action( "get_template_part_{$slug}", $slug, $post_type );
				if(!isset($templates[$post_type])){
				$templates[$post_type]=locate_template(array(
					$slug.'-'.$post_type.'.php',
					$slug.'.php'
				),false);
				}
				if($templates[$post_type]!==''){
				require $templates[$post_type];
				}
				do_action( 'get_template_part', $slug, $post_type, array());
				?>
			<?php endwhile; ?>
		    </div>
		<?php
		$templates=null;
		if ($themify->page_navigation !== 'yes' || !themify_is_page()) {
			get_template_part('includes/pagination');
		}
		do_action('themify_after_loop_output');
    }
}

function themify_product_loop_output($args=array()){
	global $wp_query,$woocommerce_loop;
	$woocommerce_loop=array_merge($args,array(
		'is_search'    => $wp_query->is_search(),
		'is_filtered'  => is_filtered(),
		'total'        => $wp_query->found_posts,
		'total_pages'  => $wp_query->max_num_pages,
		'per_page'     => $wp_query->get( 'posts_per_page' ),
		'current_page' => max( 1, $wp_query->get( 'paged', 1 ) ),
	));
	if (wc_get_loop_prop( 'total' ) ) {
		echo '<div class="woocommerce">';
		do_action( 'woocommerce_before_shop_loop' );
		woocommerce_product_loop_start();
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
		echo '</div>';
	}
	$woocommerce_loop=null;
}

if(!function_exists('themify_404_page_content')){
    function themify_404_page_content(){
	?>
	<p><?php _e( 'Page not found.', 'themify' ); ?></p>
	<?php if( current_user_can('administrator') ): ?>
	    <p><?php _e( '@admin Learn how to create a <a href="https://themify.me/docs/custom-404" target="_blank">custom 404 page</a>.', 'themify' ); ?></p>
	<?php endif;
    }
}

if(!function_exists('themify_page_title')){
    function themify_page_title($args=array()){
	global $themify;
	do_action('themify_before_page_title');
	if (isset($themify->page_title) && $themify->page_title !== 'yes'){
	?>
	    <!-- page-title -->
	    <time datetime="<?php the_time('o-m-d'); ?>"></time>
	    <?php echo themify_get_title($args);
	}
	do_action('themify_after_page_title');
    }
}

if ( ! function_exists( 'themify_page_image' ) ) {
function themify_page_image(){
	global $themify;

	do_action( 'themify_before_page_image' );

	if ( has_post_thumbnail() ) {
		if ( ! isset( $themify->hide_page_image ) || $themify->hide_page_image !== 'yes' ) { ?>
			<figure class="post-image">

			<?php themify_image( array(
				'w' => isset( $themify->image_page_single_width ) ? $themify->image_page_single_width : $themify->width,
				'h' => isset( $themify->image_page_single_height ) ? $themify->image_page_single_height : $themify->height,
			) ); ?>

			</figure>
		<?php
		}
	}

	do_action( 'themify_after_page_image' );
}
}

if(!function_exists('themify_page_content')){
    function themify_page_content($args=array()){
		if(is_404()){
			if(!isset($args['hide_title']) || $args['hide_title']===false){
				echo themify_get_title($args);
			}
			if(!isset($args['hide_desc']) || $args['hide_desc']===false){
				themify_404_page_content();
			}
		}
		elseif(themify_is_page()){
			if (have_posts()) {
				the_post();
				do_action('themify_before_page_content');
				?>
				<div id="page-<?php the_ID(); ?>" class="type-page">
					<?php
					if(!isset($args['hide_title']) || $args['hide_title']===false){
						themify_page_title($args);
					}
					if(!isset($args['hide_page_entry']) || $args['hide_page_entry']===false){
						themify_page_entry_content();
					}
					?>
				</div>
				<!-- /.type-page -->
				<?php
				do_action('themify_after_page_content');
			 }
		}
		else{
			if(!isset($args['hide_title']) || $args['hide_title']===false){
				echo themify_get_title($args);
			}
			if(!isset($args['hide_desc']) || $args['hide_desc']===false){
				themify_page_description();
			}
		}
    }
}

if(!function_exists('themify_page_entry_content')){
    function themify_page_entry_content(){
	global $themify;
	do_action('themify_before_entry_content');
	?>
	<div class="page-content entry-content">
	    <?php
	    themify_page_image();
	    the_content();
	    wp_link_pages(array('before' => '<p class="post-pagination tf_block"><strong>' . __('Pages:', 'themify') . '</strong> ', 'after' => '</p>', 'next_or_number' => 'number'));
	    themify_edit_link();
	    if ($themify->query_category === '' && !themify_check('setting-comments_pages', true)) {
		comments_template();
	    }
	    ?>
	    <!-- /comments -->
	</div>
	<!-- /.post-content -->
	<?php
	do_action('themify_after_entry_content');
    }
}

function themify_is_page(){
	static $is=null;
	if($is===null){
		global $themify;
		$is=(isset($themify->isPage) && $themify->isPage===true) || is_page();
	}
	return $is;
}

if (!function_exists('themify_comment_list')) {

    /**
     * Themify Comment
     *
     * @since 1.0.0
     *
     * @param object $comment Current comment.
     * @param array $args Parameters for comment reply link.
     * @param int $depth Maximum comment nesting depth.
     */
    function themify_comment_list($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;
	static $size = null;
	static $date = null;
	static $time = null;
	if ($size === null) {
	    $size = apply_filters('themify_comment_avatar_size', 48);
	    $date = apply_filters('themify_comment_date', '');
	    $time = apply_filters('themify_comment_time', '');
	}
	?>
	<li id="comment-<?php comment_ID() ?>" <?php comment_class(); ?>>
	    <p class="comment-author">
		<?php echo get_avatar($comment, $size); ?>
		<cite><?php echo themify_get_icon('bookmark','fa',false,false,array('aria-label'=>__('Bookmark','themify'))); ?><?php echo get_comment_author_link(); ?></cite>
		<br/>
		<small class="comment-time">
		    <?php comment_date($date); ?>
		    @
		    <?php
		    comment_time($time);
		    edit_comment_link(__('Edit', 'themify'), ' [', ']');
		    ?>
		</small>
	    </p>
	    <div class="commententry">
		<?php if ($comment->comment_approved == '0') : ?>
		<p><em><?php _e('Your comment is awaiting moderation.', 'themify') ?></em></p>
		<?php endif; ?>
		<?php comment_text(); ?>
	    </div>
	    <p class="reply">
		<?php comment_reply_link(array_merge($args, array('add_below' => 'comment', 'depth' => $depth, 'reply_text' => __('Reply', 'themify'), 'max_depth' => $args['max_depth']))) ?>
	    </p>
	    <?php
	}
}

if (!function_exists('themify_comments')) {

    function themify_comments() {
	    $post_type=get_post_type();
	    if(($post_type==='post' && themify_check( 'setting-comments_posts',true)) || ($post_type==='portfolio' && !themify_check( 'setting-portfolio_comments',true))){
		    return;
	    }
	    themify_comment_before(); //hook
	    $hasComment = have_comments();
	    $commentOpen = comments_open();
	    ?>
	    <?php if ($hasComment === true || $commentOpen === true) : ?>

		<div id="comments" class="commentwrap tf_clearfix">

		    <?php themify_comment_start(); //hook   ?>

		    <?php if ($hasComment === true): ?>
			<?php if (post_password_required()) : ?>
			<p class="nopassword"><?php _e('This post is password protected. Enter the password to view any comments.', 'themify'); ?></p>
			<?php else: ?>
			    <?php
			    $callback = function_exists('themify_theme_comment') ? 'themify_theme_comment' : 'themify_comment_list';

			    $total = get_comment_pages_count();
			    $pagination = $total > 1 ? paginate_comments_links(array('prev_text' => '', 'next_text' => '', 'echo' => false, 'total' => $total)) : false;
			    ?>
			<h4 class="comment-title"><?php comments_number(__('No Comments', 'themify'), __('1 Comment', 'themify'), __('% Comments', 'themify')); ?></h4>

			    <?php if ($pagination !== false) : ?>
				<nav class="pagenav top tf_clearfix">
				    <?php echo $pagination ?>
				</nav>
				<!-- /.pagenav -->
			    <?php endif; ?>

			<ol class="commentlist">
				<?php wp_list_comments('callback=' . $callback); ?>
			</ol>

			    <?php if ($pagination !== false) : ?>
				<nav class="pagenav bottom tf_clearfix">
				    <?php echo $pagination ?>
				</nav>
				<!-- /.pagenav -->
			    <?php endif; ?>

			<?php endif; ?>
		    <?php endif; ?>
		    <?php
		    if ($commentOpen === true) {
				comment_form( themify_comment_form_args() );
		    }
                    if(themify_is_themify_theme() && Themify_Enqueue_Assets::has_theme_support_css('comments')){
                      Themify_Enqueue_Assets::loadThemeStyleModule('comments');
                    }
		    themify_comment_end(); //hook
		    ?>
		</div>
		<!-- /.commentwrap -->
	    <?php endif; ?>

	    <?php
	    themify_comment_after(); //hook

    }
}

/**
 * Comment form args
 * Derived from comment_form() core function, with all the texts translatable
 *
 * @return array
 */
function themify_comment_form_args() {
	$req  = get_option( 'require_name_email' );
	$post = get_post();

	$post_id       = $post->ID;
	$commenter     = wp_get_current_commenter();
	$user          = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';

	// Identify required fields visually and create a message about the indicator.
	$required_indicator = ' ' . wp_required_field_indicator();
	$required_text      = ' ' . wp_required_field_message();

	return [
		'fields' => array(
			'author' => sprintf(
				'<p class="comment-form-author">%s %s</p>',
				sprintf(
					'<label for="author">%s%s</label>',
					__( 'Name', 'themify' ),
					( $req ? $required_indicator : '' )
				),
				sprintf(
					'<input id="author" name="author" type="text" value="%s" size="30" maxlength="245" autocomplete="name"%s />',
					esc_attr( $commenter['comment_author'] ),
					( $req ? ' required' : '' )
				)
			),
			'email'  => sprintf(
				'<p class="comment-form-email">%s %s</p>',
				sprintf(
					'<label for="email">%s%s</label>',
					__( 'Email', 'themify' ),
					( $req ? $required_indicator : '' )
				),
				sprintf(
					'<input id="email" name="email" type="email" value="%s" size="30" maxlength="100" aria-describedby="email-notes" autocomplete="email"%s />',
					esc_attr( $commenter['comment_author_email'] ),
					( $req ? ' required' : '' )
				)
			),
			'url'    => sprintf(
				'<p class="comment-form-url">%s %s</p>',
				sprintf(
					'<label for="url">%s</label>',
					__( 'Website', 'themify' )
				),
				sprintf(
					'<input id="url" name="url" type="url" value="%s" size="30" maxlength="200" autocomplete="url" />',
					esc_attr( $commenter['comment_author_url'] )
				)
			),
		),
		'comment_field'        => sprintf(
			'<p class="comment-form-comment">%s %s</p>',
			sprintf(
				'<label for="comment">%s%s</label>',
				_x( 'Comment', 'noun', 'themify' ),
				$required_indicator
			),
			'<textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required></textarea>'
		),
		'must_log_in'          => sprintf(
			'<p class="must-log-in">%s</p>',
			sprintf(
				/* translators: %s: Login URL. */
				__( 'You must be <a href="%s">logged in</a> to post a comment.', 'themify' ),
				/** This filter is documented in wp-includes/link-template.php */
				wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
			)
		),
		'logged_in_as'         => sprintf(
			'<p class="logged-in-as">%s%s</p>',
			sprintf(
				/* translators: 1: User name, 2: Edit user link, 3: Logout URL. */
				__( 'Logged in as %1$s. <a href="%2$s">Edit your profile</a>. <a href="%3$s">Log out?</a>', 'themify' ),
				$user_identity,
				get_edit_user_link(),
				/** This filter is documented in wp-includes/link-template.php */
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ), $post_id ) )
			),
			$required_text
		),
		'comment_notes_before' => sprintf(
			'<p class="comment-notes">%s%s</p>',
			sprintf(
				'<span id="email-notes">%s</span>',
				__( 'Your email address will not be published.', 'themify' )
			),
			$required_text
		),
		'title_reply'          => __( 'Leave a Reply', 'themify' ),
		/* translators: %s: Author of the comment being replied to. */
		'title_reply_to'       => __( 'Leave a Reply to %s', 'themify' ),
		'cancel_reply_link'    => __( 'Cancel reply', 'themify' ),
		'label_submit'         => __( 'Post Comment', 'themify' ),
	];
}

if(!function_exists('themify_single_comments_template')) {
    function themify_comments_template() {
        $post_type=get_post_type();
        if(($post_type==='post' && themify_check('setting-comments_posts', true)) || ($post_type==='portfolio' && !themify_check('setting-portfolio_comments', true))) {
            return;
        }
        comments_template();
    }
}
/**
* Parses the arguments given as category to see if they are category IDs or slugs and returns a proper tax_query
* @param $category
* @param $taxonomy
* @return array
*/
function themify_parse_category_args($category, $taxonomy){
    $tax_query = array();
    if ('all' !== $category && $category!='0') {
	$terms = explode(',', $category);
	$ids_in = $ids_not_in = $slugs_in = $slugs_not_in = array();
	$isAnd=0;
	foreach($terms as $c){
		$c=trim($c);
		if($c){
		     if('-' !== $c[0]){
			if(is_numeric( $c ) ){
			    $ids_in[] = (int)$c;
			    ++$isAnd;
			}
			else{
			    $slugs_in[] = $c;
			    ++$isAnd;
			}
		    }
		    elseif(is_numeric( $c )){
			$ids_not_in[] = $c*(-1);
			++$isAnd;
		    }
		    else{
			$slugs_not_in[] = ltrim($c, '-'); // remove the minus sign (first character)
			++$isAnd;
		    }
		}
	}
	if ($isAnd>1) {
		$tax_query = array(
		    'relation' => 'AND'
		);
	}
	$terms=$isAnd=null;
	if ( ! empty( $ids_in ) ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field' => 'id',
			'terms' => $ids_in
		);
	}
	if ( ! empty( $ids_not_in ) ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field' => 'id',
			'terms' => $ids_not_in,
			'operator' => 'NOT IN'
		);
	}
	if ( ! empty( $slugs_in ) ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field' => 'slug',
			'terms' => $slugs_in
		);
	}
	if ( ! empty( $slugs_not_in ) ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field' => 'slug',
			'terms' => $slugs_not_in,
			'operator' => 'NOT IN'
		);
	}
    }
    return $tax_query;
}

/**
* Get Theme Sidebar
* @param void
* @return void
*/
if(!function_exists('themify_get_sidebar')){
    function themify_get_sidebar(){
		global $themify;
		if ($themify->layout !== 'sidebar-none' && !isset($_GET['post_in_lightbox']) && !post_password_required()){
			if(is_file(Themify_Enqueue_Assets::$THEME_CSS_MODULES_DIR.'sidebar.css')){
				Themify_Enqueue_Assets::loadThemeStyleModule('sidebar');
			}
			get_sidebar();
		}
    }
}


if( ! function_exists( 'themify_menu_nav' ) ) :
/**
 * Display main navigation menu
 *
 * @param $args array customize the arguments sent to wp_nav_menu
 * @return string|null output of the wp_nav_menu if $args['echo'] == false, otherwise null
 */
function themify_menu_nav( $args = array(),$reinit=false ) {

    $args=themify_parse_args( $args, array(
		'theme_location' => 'main-nav',
		'fallback_cb' => 'themify_default_main_nav',
		'container'   => '',
		'menu'=>'',
		'menu_id'     => 'main-nav',
		'menu_class'  => 'main-nav tf_clearfix tf_box'
    ));
    $cacheDisable=$reinit===true  || themify_is_dev_mode() || TFCache::get_cache_plugins('any') || themify_check('setting-cache-menu',true) || themify_check('setting-cache-html',true) || defined( 'POLYLANG_VERSION' );
    $isEcho =!isset($args['echo']) || $args['echo']===true;
    $menu=null;
    if($args['menu_id'] === 'main-nav' && empty($args['menu']) && themify_is_themify_theme() && (is_singular() || themify_is_shop())){
	    $menu = themify_get( 'custom_menu' );
	    if (! empty( $menu )){
			$args['menu']=$menu;
	    }
    }
    if($cacheDisable===false){
	    $transient_key = 'tf_menu_'.$args['theme_location'];
	    if(!$menu){
		    if(empty($args['menu'])){
			    $locations = get_nav_menu_locations();
			    if (!empty($locations) && isset( $locations[ $args['theme_location'] ] ) ) {
				    $menu = wp_get_nav_menu_object( $locations[ $args['theme_location'] ] );
				    if(isset($menu,$menu->term_id)){
					    $menu=$menu->term_id;
				    }
			    }
			    $locations=null;
		    }
		    else{
			    $menu = wp_get_nav_menu_object($args['menu']);
			    if(isset($menu,$menu->term_id)){
				    $menu=$menu->term_id;
			    }
		    }
	    }
	    global $wp_filter;
	    if ( $menu && isset($wp_filter['wp_get_nav_menu_items'],$wp_filter['wp_get_nav_menu_items']->callbacks)&& class_exists( 'WPML_LS_Render' ) ) {
		    /* disable WPML_LS_Render::wp_get_nav_menu_items_filter(), located in wpml/classes/language-switcher/class-wpml-ls-render.php */

		    $wpml_ls_render = null;
		    foreach ( $wp_filter['wp_get_nav_menu_items']->callbacks[10] as $i => $filter ) {
			    if ( is_array( $filter['function'] ) && is_a( $filter['function'][0], 'WPML_LS_Render' ) ) {
				    $wpml_ls_render = $filter;
				    unset( $wp_filter['wp_get_nav_menu_items']->callbacks[10][ $i ] );
				    break;
			    }
		    }
		    /* render the LS menu items */
		    if ( $wpml_ls_render!==null ) {
			    $dummy = new stdClass(); /* dummy element, added to check where LS menu items should be displayed */
			    $dummy->menu_order = null;
			    $ls_menu_items = $wpml_ls_render['function'][0]->wp_get_nav_menu_items_filter( array( $dummy ), wp_get_nav_menu_object( $menu ) );
			    $ls_dummy_position = array_search( $dummy, $ls_menu_items );
			    unset( $ls_menu_items[ $ls_dummy_position ] );
			    _wp_menu_item_classes_by_context( $ls_menu_items );
			    $args = wp_parse_args( $args, array(
				    'before'               => '',
				    'after'                => '',
				    'link_before'          => '',
				    'link_after'           => '',
			    ) );
			    $ls_output = walk_nav_menu_tree( $ls_menu_items, 0, (object) $args );
		    }
	    }

	    if(defined('ICL_LANGUAGE_CODE')){
		    $transient_key.='_'.ICL_LANGUAGE_CODE;
	    }
	    if (! empty( $menu )) {
		$transient_key.='_'.$menu;
		$menu=null;
	    }
	    $args = apply_filters( 'wp_nav_menu_args', $args );
	    $transient_key.='_'.json_encode($args);
	    $transient = Themify_Storage::get( $transient_key,'tf_menu_');

	    //current page selection
	    if($transient!==false && $transient!==''){
		    if(class_exists('Themify_Mega_Menu_Walker') && (strpos($transient,'has-mega-column',10)!==false || strpos($transient,'has-mega-dropdown',10)!==false)){
			    Themify_Mega_Menu_Walker::preloadCssJs();
		    }
		    global $wp_query;
		    $queried_object = $wp_query->get_queried_object();
		    if(!empty($queried_object)){
				if(themify_is_shop()){
					$object_id = themify_shop_pageId();
					$type='page';
				}
				else{
					$object_id = (int)$wp_query->queried_object_id;
					$type=!empty($queried_object->taxonomy)?$queried_object->taxonomy:(!empty($queried_object->post_type)?$queried_object->post_type:'');
				}
				if($type!==''){
					$transient = str_replace(array('current-menu-item','current-menu-parent','current_page_item','current-menu-parent','current_page_parent','current_page_ancestor','current-menu-ancestor'),'',$transient);

					$replace = 'current-menu-item menu-item-'.$type.'-'.$object_id;
					if($type==='page'){
						$replace.=' current_page_item';
					}

					$transient = str_replace('menu-item-'.$type.'-'.$object_id.' ',$replace.' ',$transient);
					$transient=themify_set_current_menu_nav($transient,$type,$object_id);
				}
		    }

			if(!empty($transient) && strpos($transient,'tf_fa',10)!==false){
				preg_match_all('/tf_fa\s(.+?)\"/m', $transient,$match);
				if(!empty($match[1])){
					foreach($match[1] as $m){//generate svg
						$m=explode('-',str_replace('tf-','',trim($m)));
						$prefix=false;
						if($m[0]==='fas' || $m[0]==='far' || $m[0]==='fab'){
							$pre=$m[0];
							unset($m[0]);
							$m=$pre.' '.implode('-',$m);
							$prefix='fa';
						}
						else{
							$m=implode('-',$m);
						}
						themify_get_icon($m,$prefix);
					}
				}
			}
	    }
    }
    else{
	    $transient=false;
    }
    if($transient===false){
		$args['echo']=false;
		// Render the menu
		add_filter( 'nav_menu_css_class', 'themify_class_to_nav_menu', 100, 2 );
		if(empty($args['walker'])){
			add_filter('nav_menu_item_args', 'themify_menu_child_arrow',100,2);
			add_filter('nav_menu_link_attributes', 'themify_menu_filter_href',100,1);
		}
		$menu= wp_nav_menu($args , 'menu_nav' );
		unset($args);
		if($menu){
			$menu=themify_make_lazy($menu,false);
		}
		if($cacheDisable===false){
			Themify_Storage::set($transient_key, !$menu?'':$menu,MONTH_IN_SECONDS,'tf_menu_');
		}
		remove_filter( 'nav_menu_css_class', 'themify_class_to_nav_menu', 100 );
		remove_filter('nav_menu_item_args', 'themify_menu_child_arrow',100);
		remove_filter('nav_menu_link_attributes', 'themify_menu_filter_href',100);
    }
    else{
		$menu=$transient;
    }

	if ( isset( $ls_output ) ) {
		if ( $ls_dummy_position === 0 ) {
			$menu = themify_str_replace_last( '</ul>', $ls_output . '</ul>', $menu ); /* append as the very last item in the menu */
		} else {
			$menu = themify_str_replace_first( '<li', $ls_output . '<li', $menu ); /* prepend the LS to before the very first menu item */
		}
		$wp_filter['wp_get_nav_menu_items']->callbacks[10][] = $wpml_ls_render;
	}

    if($isEcho===false){
		return $menu;
    }
    echo $menu;
}
endif;

function themify_set_current_menu_nav($text,$type,$object_id){
	//parent id
	preg_match('/menu-'.$type.'-'.$object_id.'-parent-(\d+)/i',$text,$match);

	if(!empty($match[1])){
		$pid=(int)$match[1];
		$menuId=get_post_meta( $pid, '_menu_item_object_id', true );
		if($menuId){
			$object = get_post_meta( $pid, '_menu_item_object', true );
			if($object==='taxonomy'){
				$type = get_post_meta( $pid, '_menu_item_type', true );
			}
			else{
				$type=$object;
			}
			$replace = 'current-menu-ancestor current-menu-parent menu-item-'.$type.'-'.$menuId;
			if($object==='page'){
				$replace.=' current_page_parent current_page_ancestor';
			}
			$text = str_replace('menu-item-'.$type.'-'.$menuId.' ',$replace.' ',$text);
			if($menuId!=$object_id){
				$text=themify_set_current_menu_nav($text,$type,$menuId);
			}
		}
	}

	return $text;
}

function themify_class_to_nav_menu( $classes, $item ){
	if ( ! isset( $item->object_id ) ) {
		return $classes;
	}

	array_unshift($classes,'menu-item-'.$item->object.'-'.$item->object_id);//need to be first, to have space between classes
	if($item->menu_item_parent!=0){
		$classes[] = 'menu-'.$item->object.'-'.$item->object_id.'-parent-'.$item->menu_item_parent;
	}
	// Clean up active menu item from the links with hashtag
	$hash = strpos($item->url,'#');
	if($hash!==false && $hash!==0){
		$key = array_search('current-menu-item', $classes);
		if(false!==$key){
			unset($classes[$key]);
		}
		$key = array_search('current_page_item', $classes);
		if(false!==$key){
			unset($classes[$key]);
		}
	}
	return $classes;
}

function themify_menu_filter_href($atts){
	if($atts['href']==='#'){
		$atts['href']='';
		$atts['role']='button';
		$atts['tabindex']='0';

	}
	return $atts;
}
function themify_menu_child_arrow( $args, $item){
	$args->link_after=in_array('menu-item-has-children', $item->classes,true)?'<span class="child-arrow closed" tabindex="-1"></span>':'';
	return $args;
}
if ( ! function_exists( 'themify_default_main_nav' ) ) {
	/**
	 * Default Main Nav Function
	 * @since 1.0.0
	 */
	function themify_default_main_nav() {
		echo '<ul id="main-nav" class="main-nav tf_clearfix tf_box">';
			wp_list_pages( 'title_li=' );
		echo '</ul>';
	}
}

if ( ! function_exists( 'themify_theme_menu_nav' ) ) {
    function themify_theme_menu_nav($args=array()){//deprecated from 2020.06.02,instead of use themify_menu_nav
	themify_menu_nav($args);
    }
}

/**
 * Validates $string to only contain HTML links
 *
 * Shortcut to wp_kses
 *
 * @since 4.5.7
 * @return string
 */
function themify_kses_link( $string ) {
	return wp_kses( $string, array(
		'a' => array(
			'href' => array(),
			'title' => array()
		) )
	);
}