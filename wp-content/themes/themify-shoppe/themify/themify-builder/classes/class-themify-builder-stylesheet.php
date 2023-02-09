<?php

final class Themify_Builder_Stylesheet {

    public static $generateStyles = array();
    private static $isLoaded = array();
    /**
     * Constructor
     * 
     * @access public
     * @param object Themify_Builder $builder 
     */
    public static function init() {
	if (themify_is_ajax()) {
	    add_action('wp_ajax_tb_slider_live_styling', array(__CLASS__, 'slider_live_styling'), 10);
	    add_action('wp_ajax_nopriv_tb_generate_on_fly', array(__CLASS__, 'save_builder_css'), 10);
	    add_action('wp_ajax_tb_generate_on_fly', array(__CLASS__, 'save_builder_css'), 10);
	    // Regenerate CSS Files
	    add_action('wp_ajax_themify_regenerate_css_files_ajax', array(__CLASS__, 'regenerate_css_files_ajax'));
	} 
	elseif (defined('THEMIFY_BUILDER_REGENERATE_CSS') && THEMIFY_BUILDER_REGENERATE_CSS !== false && current_user_can('manage_options')) {
	    add_action('admin_head', array(__CLASS__, 'auto_regenerate_css'));
	}
	if (!is_admin()) {
	    add_action('tf_load_styles', array(__CLASS__, 'load_styles'), 9); //10 is customizer
	}
    }

    /**
     * Checks if the builder stylesheet exists and enqueues it.
     * 
     * @since 2.2.5
     * 
     * @return bool True if enqueue was successful, false otherwise.
     */
    public static function enqueue_stylesheet($return = false, $post_id = null) {
	$isActive = Themify_Builder::$frontedit_active === true || Themify_Builder_Model::is_front_builder_activate();
	if (($isActive === false && isset(self::$isLoaded[$post_id])) || themify_is_rest()) {
	    return false;
	}
	$stylesheet_path = self::get_stylesheet('basedir', $post_id);
	if (!self::is_readable_and_not_empty($stylesheet_path['url'])) {
	    if (!is_admin() && !isset(self::$generateStyles[$post_id]) && !Themify_Filesystem::is_file(self::getTmpPath($stylesheet_path['url']))) {

		self::$generateStyles[$post_id] = true;
		global $ThemifyBuilder;
		$post_data = ThemifyBuilder_Data_Manager::get_data($post_id);
		if (!empty($post_data)) {
		    static $strucutre = null;
		    if ($strucutre === null) {
			$strucutre = true;
			Themify_Builder_Component_Module::load_modules();
			themify_enque_script('tb_builder_js_style', THEMIFY_URI . '/js/generate-style.js',THEMIFY_VERSION,array('themify-main-script'));

			add_filter('themify_google_fonts_full_list', '__return_true');
			wp_localize_script('tb_builder_js_style', 'ThemifyBuilderStyle', array(
			    'builder_url' => THEMIFY_BUILDER_URI,
			    'styles' => Themify_Builder::getComponentJson(true),
			    'gutters' => Themify_Builder_Model::get_gutters(),
			    'points' => themify_get_breakpoints(),
			    'nonce' => wp_create_nonce('tf_nonce'),
			    'ajaxurl' => admin_url('admin-ajax.php'),
			    'google' => themify_get_google_web_fonts_list(),
			    'cf' => Themify_Custom_Fonts::get_list(),
			    'cf_api_url' => Themify_Custom_Fonts::$api_url
			));
			remove_filter('themify_google_fonts_full_list', '__return_true');
		    }
		    $js_data = array('data'=>$post_data);
		    $gs = Themify_Global_Styles::used_global_styles($post_id);
		    if (!empty($gs)) {
			$js_data['gs'] = $gs;
		    }
		    $custom_css = get_post_meta($post_id, 'tbp_custom_css', true);
		    if (!empty($custom_css)) {
			$js_data['custom_css'] = $custom_css;
		    }
		    wp_localize_script( 'tb_builder_js_style', 'themify_builder_data_' . $post_id, $js_data );
		    $gs = $post_data = $custom_css = null;
		    TFCache::$stopCache = true;
		    $ThemifyBuilder->get_builder_stylesheet('', false, true);
		}
	    }
	} elseif(!is_admin() || themify_is_ajax()) {
	    $handler = pathinfo($stylesheet_path['url']);
	    $handlerId = $handler['filename'];
	    $version = filemtime($stylesheet_path['url']);
	    $url = self::get_stylesheet('baseurl', $post_id);
	    $url = themify_https_esc($url['url']);
	    self::$isLoaded[$post_id] = array(
		'handler' => $handlerId,
		'url' => $url,
		'v' => $version
	    );
	    if ($return === true) {
		return self::$isLoaded[$post_id];
	    } 
	    else {
		$fonts = self::getFonts($post_id);
		if (!empty($fonts)) {
		    Themify_Enqueue_Assets::addGoogleFont($fonts);
		}
		// custom fonts
		$custom_fonts = Themify_Custom_Fonts::load_fonts($post_id);
		if (!empty($custom_fonts)) {
		    echo '<style id="' , $handlerId , '-cffonts">' , $custom_fonts , '</style>' ,PHP_EOL;
		}
		unset($custom_fonts, $fonts);
		global $ThemifyBuilder;
		if ($isActive === true && $ThemifyBuilder->in_the_loop === true && $post_id != Themify_Builder::$builder_active_id) {
		    echo '<link class="themify-builder-generated-css" id="' , $handlerId , '" rel="stylesheet" href="' , $url , '?ver=' , $version , '" type="text/css">';
		}
	    }
	    return true;
	}
	return false;
    }

    public static function load_styles() {
	foreach (self::$isLoaded as $post_id => $v) {
	    if (Themify_Builder::$builder_active_id === $post_id && Themify_Builder_Model::is_front_builder_activate()) {
		continue;
	    }

	    themify_enque_style($v['handler'], $v['url'], null, $v['v']);
	}
	self::$isLoaded = array();
    }

    /**
     * This function check the CSS generated files version and if
     * it requires to update or not
     */
    public static function auto_regenerate_css() {
	$css_version = get_option('tb_css_version', false);
	if ($css_version !== THEMIFY_VERSION && Themify_Filesystem::is_dir(self::get_stylesheet_dir()) && 'finished' === self::regenerate_css_files()) {
	    update_option('tb_css_version', THEMIFY_VERSION, false);
	}
    }


    /**
     * Generate css file on the fly, if builder doesn't have style created an empty file,
     * which will help to detect there is no need to create css file.
     * @return void
     */
    public static function save_builder_css($echo = false) {
	check_ajax_referer('tf_nonce', 'nonce');
	if (!empty($_POST['bid'])) {
	    $id = (int) $_POST['bid'];
	    if(!empty($_FILES['css'])) {
			$data = json_decode(file_get_contents($_FILES['css']['tmp_name']), true);
	    }
	    elseif (isset($_POST['css'])) {//don`t use empty 
			$data = !empty($_POST['css'])?json_decode(stripslashes_deep($_POST['css']), true):array();
	    }
	    if(isset($data)){
		if (!is_array($data)) {
		    $data = array();
		}
		$custom_css = !empty($_POST['custom_css']) ? stripcslashes($_POST['custom_css']) : '';
		$res = self::write_stylesheet($id, $data, $custom_css);
		if (empty($res['css_file']) && empty($_POST['delete_css'])) {
		    $stylesheet_path = self::get_stylesheet('basedir', $id);
		    if (!Themify_Filesystem::is_file($stylesheet_path['url'])) {
			Themify_Filesystem::put_contents(self::getTmpPath($stylesheet_path['url']), 'done');
		    }
		}
		if ($echo === true) {
		    echo json_encode($res);
		}
		if(!empty($_POST['images'])){
		    $images= json_decode(stripslashes_deep($_POST['images']),true);
		    if(!empty($images) && is_array($images)){
			foreach($images as $img){
			    themify_get_image_size($img);
			    themify_get_placeholder($img);
			    themify_createWebp($img);
			}
		    }
		    unset($images);
		}
	    }
	}
	wp_die();
    }

    /* Return tmp path of original file */

    private static function getTmpPath($path) {
	return str_replace('.css', '-tmp.css', $path);
    }

    /**
     * Regenerate CSS files Ajax
     *
     */
    public static function regenerate_css_files_ajax() {
	check_ajax_referer('tf_nonce', 'nonce');
	$type = !empty($_POST['all']) && is_multisite() ? 'all' : false;
	Themify_Enqueue_Assets::clearConcateCss($type);
	$result = self::regenerate_css_files($type);
	if($result==='finished'){
	    wp_send_json_success();
	}
	else{
	    wp_send_json_error($result);
	}
    }

    /**
     * Find for old URLs in generated CSS files and regenerates them base on new URLs
     *
     */
    public static function regenerate_css_files($type = false) {
	if (current_user_can('manage_options')) {
	    $path = self::get_stylesheet_dir();
	    $error = true;
	    if ($type === 'all' && is_multisite()) {
		$generatedDir = '/themify-css/';
		$path = dirname(dirname($path));
		if (strpos($path, '/blogs.dir/') !== false) {
		    $path = dirname($path);
		    $generatedDir = '/files' . $generatedDir;
		}
		$path = rtrim($path, '/') . '/';
		if (Themify_Filesystem::is_dir($path) && ($handle = opendir($path))) {
		    $error = false;
		    set_time_limit(0);
		    while (false !== ($f = readdir($handle))) {
			if ($f !== '.' && $f !== '..') {
			    $f = $path . $f . $generatedDir;
			    if (Themify_Filesystem::is_dir($f) && !Themify_Filesystem::delete($f)) {
				$error = true;
				break;
			    }
			}
		    }
		    closedir($handle);
		}
	    } else {
		$error = Themify_Filesystem::is_dir($path) && !Themify_Filesystem::delete($path);
	    }

	    return $error ? __('Something goes wrong. Please check the if the upload folder is writtable.') : 'finished';
	}
	return false;
    }

    /**
     * Find for old URLs in generated CSS files and delete them
     *
     */
    public static function remove_css_files($post_id) {
	$css_file = self::get_stylesheet('basedir', $post_id);
	$css_file = $css_file['url'];
	$tmp_path = self::getTmpPath($css_file);
	Themify_Filesystem::delete($css_file, 'f');
	Themify_Filesystem::delete($tmp_path, 'f');
	$tmp_path = $css_file = null;
    }

    /**
     * Write stylesheet file.
     * 
     * @since 2.2.5
     * 
     * @return array
     */
    public static function write_stylesheet($style_id, $data, $custom_css = '') {
	// Information about how writing went.

	$results = array();
	$css = '';
	if (!empty($data)) {
	    $breakpoints = themify_get_breakpoints();
	    $fonts = array('fonts' => array(), 'cf_fonts' => array());
	    $breakpoints = array('desktop' => '') + $breakpoints;
	    if (!empty($data['gs'])) {
		$css .= '/*Builder GLOBAL CSS START*/' . PHP_EOL;
		if (!empty($data['gs']['used'])) {
		    $css .= '/*GS: ' . $data['gs']['used'] . '*/' . PHP_EOL;
		    unset($data['gs']['used']);
		}
		foreach ($breakpoints as $b => $bpoint) {
		    if (!empty($data['gs'][$b])) {
			$styles = '';
			foreach ($data['gs'][$b] as $selector => $arr) {
			    $styles .= $selector . '{' . implode('', $arr) . '}';
			    if($selector[0]==='@'){
				$styles.='}';
			    }
			    $styles.=PHP_EOL;
			}
			if ($b !== 'desktop') {
			    $max = is_array($bpoint) ? $bpoint[1] : $bpoint;
			    $styles = PHP_EOL . sprintf('@media(max-width:%spx){', $max) . PHP_EOL . $styles . '}';
			}
			$css .= $styles;
		    }
		}
		unset($data['gs']);
		$css .= '/*Builder GLOBAL CSS END*/' . PHP_EOL;
	    }
	    foreach ($breakpoints as $b => $bpoint) {
		if (!empty($data[$b])) {
		    $styles = '';
		    foreach ($data[$b] as $selector => $arr) {
			$styles .= $selector . '{' . implode('', $arr) . '}';
			if($selector[0]==='@'){
			    $styles.='}';
			}
			$styles.=PHP_EOL;
		    }
		    if ($b !== 'desktop') {
			$max = is_array($bpoint) ? $bpoint[1] : $bpoint;
			$styles = PHP_EOL . sprintf('@media(max-width:%spx){', $max) . PHP_EOL . $styles . '}';
		    }
		    $css .= $styles;
		    unset($data[$b]);
		}
	    }
	    foreach (array_keys($fonts) as $ftype) {
		if (!empty($data[$ftype])) {
		    foreach ($data[$ftype] as $f => $w) {
			$v = 'fonts' === $ftype ? str_replace(' ', '+', $f) : $f;
			if (!empty($w)) {
			    $v .= ':' . implode(',', $w);
			}
			$fonts[$ftype][] = $v;
		    }
		}
	    }
	    if (!empty($data['bg'])) {
		$created = array();
		foreach ($data['bg'] as $bg) {
		    if (!isset($created[$bg])) {
			$created[$bg] = true;
			themify_generateWebp($bg);
		    }
		}
		$created = null;
	    }
	}
	$data = null;
	self::remove_css_files($style_id);
	if (!empty($custom_css)) {
	    $css .= '/*Builder Custom CSS START*/' . PHP_EOL . $custom_css . PHP_EOL . '/*Builder Custom CSS END*/';
	}
	unset($custom_css);
	if (!empty($css)) {
	    $css_file = self::get_stylesheet('basedir', $style_id);
	    $css_file = $css_file['url'];
	    $css = apply_filters('themify_builder_stylesheet_css', $css, $style_id, $css_file);
	    $css = '/* Generated from ' . get_post_type($style_id) . ': ' . get_post_field('post_name', $style_id) . " */\r\n" . $css;
	    $write = Themify_Filesystem::put_contents($css_file, $css);
	    unset($css_file, $css);
	    if ($write) {
		// Add information about writing.
		$tmp = self::get_stylesheet('baseurl', $style_id);
		$results['css_file'] = $tmp['url'];
		$results['write'] = $write;
		unset($tmp);
		// Save Fonts
		if (!empty($fonts)) {
		    foreach (array_keys($fonts) as $ftype) {
			$option_key = 'fonts' === $ftype ? 'themify_builder_google_fonts' : 'themify_builder_cf_fonts';
			$builder_fonts = get_option($option_key);
			if (!empty($fonts[$ftype])) {
			    $fonts[$ftype] = implode('|', $fonts[$ftype]);
			    if (!is_array($builder_fonts)) {
				$builder_fonts = array();
			    }
			    if (isset($builder_fonts[$style_id])) {
				$builder_fonts[$style_id] = $fonts[$ftype];
				$entry_fonts = $builder_fonts;
			    } else {
				$entry_fonts = array($style_id => $fonts[$ftype]) + $builder_fonts;
			    }
			    update_option($option_key, $entry_fonts);
			} elseif (isset($builder_fonts[$style_id])) {
			    unset($builder_fonts[$style_id]);
			    update_option($option_key, $builder_fonts);
			}
		    }
		}
	    } else {
		$results['write'] = esc_html__('Styles can`t be written.Please check permission of uploading folder', 'themify');
	    }
	} else {
	    // Add information about writing.
	    $results['write'] = esc_html__('Nothing written. Empty CSS.', 'themify');
	}
	return $results;
    }

    public static function get_stylesheet_dir($mode = 'basedir') {
	return themify_upload_dir($mode) . '/themify-css';
    }

    /**
     * Return the URL or the directory path for a template, template part or content builder styling stylesheet.
     * 
     * @since 2.2.5
     *
     * @param string $mode Whether to return the directory or the URL. Can be 'basedir' or 'baseurl' correspondingly. 
     * @param int $single ID of layout, layour part or entry that we're working with.
     *
     * @return string
     */
    private static function get_stylesheet($mode = 'basedir', $id = null) {
	if ($id === null) {
	    $id = Themify_Builder_Model::get_ID();
	}
	$path = self::get_stylesheet_dir($mode);
	if ('basedir' === $mode && !Themify_Filesystem::is_dir($path)) {
	    wp_mkdir_p($path);
	}

	/**
	 * Filters the return URL or directory path including the file name.
	 *
	 * @param string $stylesheet Path or URL for the global styling stylesheet.
	 * @param string $mode What was being retrieved, 'basedir' or 'baseurl'.
	 * @param int $id ID of the template, template part or content builder that we're fetching.
	 *
	 */
	return array('id' => $id, 'url' => apply_filters('themify_builder_get_stylesheet', "$path/themify-builder-$id-generated.css", $mode, $id));
    }

    /**
     * Enqueues Google Fonts
     * 
     * @since 2.2.6
     */
    public static function getFonts($post_id = null) {
	if (defined('THEMIFY_GOOGLE_FONTS') && THEMIFY_GOOGLE_FONTS === false) {
	    return false;
	}
	$entry_google_fonts = get_option('themify_builder_google_fonts');
	if (!empty($entry_google_fonts) && is_array($entry_google_fonts)) {
		$gsFonts=Themify_Global_Styles::used_global_styles($post_id);
		$gsFonts[]=array('id'=> $post_id ? $post_id : Themify_Builder_Model::get_ID());
		$str='';
		foreach($gsFonts as $gs){
			if (isset($entry_google_fonts[$gs['id']])) {
				$str.='|'.$entry_google_fonts[$gs['id']];
			}
		}
		return explode('|', $str);
	    
	}
	return false;
    }

    /**
     * Checks whether a file exists, can be loaded and is not empty.
     * 
     * @since 2.2.5
     * 
     * @param string $file_path Path in server to the file to check.
     * 
     * @return bool
     */
    private static function is_readable_and_not_empty($file_path = '') {
	return empty($file_path) ? false : is_readable($file_path) && 0 !== filesize($file_path);
    }

    public static function slider_live_styling() {
	check_ajax_referer('tf_nonce', 'nonce');
	$bg_slider_data = json_decode(stripslashes($_POST['tb_background_slider_data']), true);
	$row_or_col = array(
	    'styling' => array(
		'background_slider' => urldecode($bg_slider_data['shortcode']),
		'background_type' => 'slider',
		'background_slider_mode' => $bg_slider_data['mode'],
		'background_slider_speed' => $bg_slider_data['speed'],
		'background_slider_size' => $bg_slider_data['size'],
	    )
	);
	Themify_Builder_Component_Base::do_slider_background($row_or_col, $bg_slider_data['type']);
	wp_die();
    }

    /**
     * Converts color in hexadecimal format to RGB format.
     *
     * @since 1.9.6
     *
     * @param string $hex Color in hexadecimal format.
     * @return string Color in RGB components separated by comma.
     */
    private static function hex2rgb($hex) {
	$hex = str_replace('#', '', $hex);

	if (strlen($hex) === 3) {
	    $r = substr($hex, 0, 1);
	    $g = substr($hex, 1, 1);
	    $b = substr($hex, 2, 1);
	    $r = hexdec($r . $r);
	    $g = hexdec($g . $g);
	    $b = hexdec($b . $b);
	} else {
	    $r = hexdec(substr($hex, 0, 2));
	    $g = hexdec(substr($hex, 2, 2));
	    $b = hexdec(substr($hex, 4, 2));
	}
	return implode(',', array($r, $g, $b));
    }

    /**
     * Get RGBA color format from hex color
     *
     * @return string
     */
    public static function get_rgba_color($color) {
	if (strpos($color, 'rgba') !== false) {
	    return $color;
	}
	$color = explode('_', $color);
	$opacity = isset($color[1]) && $color[1] !== '' ? $color[1] : '1';
	return $opacity >= 0 && $opacity !== '1' && $opacity !== '1.00' && $opacity !== '0.99' ? 'rgba(' . self::hex2rgb($color[0]) . ', ' . $opacity . ')' : ($color[0] !== '' ? ('#' . str_replace('#', '', $color[0])) : false);
    }

}
