<?php
/**
 * Class wishlist for Woocomerce
 * @package themify
 * @since 1.0.0
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!themify_is_woocommerce_active()) {
    return;
}

class Themify_Wishlist {

    private static $cookie_name = 'themify_wishlist';
    private static $wishilist_page_slug = 'wishlist';
    private static $key = 'setting-wishlist_page';
    public static $is_wishlist_page = false;
    public static $page_id;

    private function __construct() {
        
    }

    public static function button($id = FALSE) {
        if (!self::is_enabled()) {
            return;
        }
        if (!$id) {
            global $product;
            $id = $product->get_id();
        }
        $wishlist = self::get();
        ?>
		<div class="wishlist-wrap tf_inline_b tf_vmiddle">
			<a data-id="<?php echo $id ?>" onclick="javascript:void(0)" class="<?php echo!self::$is_wishlist_page ? 'wishlist-button tf_inline_b tf_textc' : 'wishlist-remove tf_close' ?><?php if (in_array($id, $wishlist)): ?> wishlisted<?php endif; ?>" href="#" rel="nofollow">
				<?php echo !self::$is_wishlist_page ? themify_get_icon(themify_get('setting-ic-wishlist','ti-heart',true),false,false,false,array('aria-label'=>__('Wishlist','themify'))).'<span class="tooltip">' . __('Wishlist', 'themify') . '</span>' :'' ?>
			</a>
		</div> 
        <?php
    }

    public static function activation() {
        if (!self::get_wishlist_page(false)) {
            self::create_wishlist_page();
        }
    }
    
    private static function create_wishlist_page(){
	$post_id=0;
	$args = array(
	    'name' => self::$wishilist_page_slug,
	    'post_type' => 'page',
	    'post_status' => 'publish',
	    'no_found_rows'=>true,
	    'numberposts' => 1
	);
	$wishlist_page = get_posts($args);
	if (empty($wishlist_page)) {
	    $post_id = wp_insert_post(array(
		'post_name' => self::$wishilist_page_slug,
		'post_title' => __('Wishlist', 'themify'),
		'post_status' => 'publish',
		'post_type' => 'page'
	    ));
	} else {
	    $wishlist_page = current($wishlist_page);
	    if(is_object($wishlist_page)){
		$post_id = $wishlist_page->ID;
	    }
	}
	if ($post_id > 0) {
	    $data = themify_get_data();
	    $data[self::$key] = $post_id;
	    themify_set_data($data);
	}
	wp_reset_postdata();
	return $post_id;
    }

    public static function get_wishlist_page($url = true) {
        static $is = null;
        if ($is===null) {
            $is = themify_get(self::$key,false,true);
	    if($is===false){
		$is=self::create_wishlist_page();
	    }
        }
        if ($is>0 && $url===true) {
            $is = get_the_permalink($is);
        }
        return $is;
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_current_language_code() {

        static $language_code = false;
        if ($language_code) {
            return $language_code;
        }
        if (defined('ICL_LANGUAGE_CODE')) {

            $language_code = ICL_LANGUAGE_CODE;
        } elseif (function_exists('qtrans_getLanguage')) {

            $language_code = qtrans_getLanguage();
        }
        if (!$language_code) {
            $language_code = substr(get_bloginfo('language'), 0, 2);
        }
        $language_code = strtolower(trim($language_code));
        return $language_code;
    }

    /**
     * Returns the current language code
     *
     * @since 1.0.0
     *
     * @return string the language code, e.g. "en"
     */
    public static function get_default_language_code() {
        static $language_code = false;
        if ($language_code === false) {
            global $sitepress;
            if (isset($sitepress)) {
                $language_code = $sitepress->get_default_language();
            }

            $language_code = empty($language_code) ? substr(get_bloginfo('language'), 0, 2) : $language_code;
            $language_code = strtolower(trim($language_code));
        }
        return $language_code;
    }

    /**
     * Returns the site languages
     *
     * @since 1.0.0
     *
     * @return array the languages code, e.g. "en",name e.g English
     */
    public static function get_all_languages() {

        static $languages = array();
        if (!empty($languages)) {
            return $languages;
        }
        if (defined('ICL_LANGUAGE_CODE')) {
            $lng = self::get_current_language_code();
            if ($lng == 'all') {
                $lng = self::get_default_language_code();
            }
            $all_lang = icl_get_languages('skip_missing=0&orderby=KEY&order=DIR&link_empty_to=str');
            foreach ($all_lang as $key => $l) {
                if ($lng == $key) {
                    $languages[$key]['selected'] = true;
                }
                $languages[$key]['name'] = $l['native_name'];
            }
        } elseif (function_exists('qtrans_getLanguage')) {
            $languages = qtrans_getSortedLanguages();
        }
        if(empty($languages)) {
            $all_lang = self::get_default_language_code();
            $languages[$all_lang]['name'] = '';
            $languages[$all_lang]['selected'] = true;
        }
        return $languages;
    }

	/**
	 * Gets an object ID (term, post) and returns a list of IDs for that object in all languages
	 *
	 * @return array
	 */
	public static function get_object_id_in_all_languages( $object_id, $type ) {
		$ids = array();
		$languages = self::get_all_languages();
		foreach ( $languages as $code => $language ) {
			$id = apply_filters( 'wpml_object_id', $object_id, $type, false, $code );
			if ( $id ) {
				$ids[ $code ] = $id;
			}
		}

		return $ids;
	}

    public static function wishlist_page() {
		if ( ! is_page() ) {
			return;
		}
        self::$page_id = get_the_ID();
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$page_ids = self::get_object_id_in_all_languages( (int) self::get_wishlist_page(false), 'page' );
			self::$is_wishlist_page = array_search( self::$page_id, $page_ids ) !== false;
        } else {
			self::$is_wishlist_page = self::$page_id == self::get_wishlist_page(false);
		}
        if (self::$is_wishlist_page) {
			add_action('wp_head',array(__CLASS__,'head'));
        }
    }
	
    public static function head(){
	add_filter('the_content', array(__CLASS__, 'wishlist_result'), 100, 1);
	add_filter('body_class', array(__CLASS__, 'body_class'), 10, 1);
    }

    public static function body_class($classes) {
        $classes[] = 'wishlist-page';
        $classes[] = 'woocommerce';
        $classes[] = ' woocommerce-page';
        return $classes;
    }

    public static function wishlist_result($content='') {
		if(!themify_is_ajax()){
			WC_Frontend_Scripts::load_scripts();
			themify_get_icon('heart-broken','ti');
			return $content;
        }
		self::$is_wishlist_page=true;
		Themify_WC::set_wc_vars();
		$items = self::get(true);
        if (!empty($items)) {
            add_filter( 'woocommerce_show_page_title', '__return_false', 100 );
            remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 8 );
            global $wp_query,$woocommerce_loop;
            $woocommerce_loop['total']=count($items);
            $woocommerce_loop['is_filtered']=true;
            $wp_query = new WP_Query( array( 'post__in' => $items,'post_type'=>'product' ) );
            ob_start();
			echo woocommerce_content();
			$result = ob_get_contents();
			ob_end_clean();
        } else {
            $result = '<p class="themify_wishlist_no_items">'. themify_get_icon('heart-broken','ti') . __("There is no wishlist item.", 'themify') . '</p>';
        }
        $result = '<div id="wishlist-wrapper">' . $result . '</div>';
        echo $result;
		wp_die();
    }

    private static function get_expiration() {
        static $time = false;
        if (!$time) {
            $time = time() + apply_filters('themify_wishlist_cookie_expiration_time', 60 * 60 * 24 * 30); // 30 days
        }
        return $time;
    }

    public static function get_total() {
        $wishlist = self::get(true);
        return count(array_unique($wishlist));
    }

    public static function removeItem($id = false) {
        if (!$id) {
            $id = get_the_ID();
        }
        $wishlist = self::get();
        $index = array_search($id, $wishlist);
        if ($index !== false) {
            unset($wishlist[$index]);
            self::setCookies($wishlist);
        }
    }

    public static function destroy() {
        wc_setcookie(self::$cookie_name, array(), time() - 3600, false);
    }

    public static function setValue($id) {
        $wishlist = self::get();
        $wishlist[] = $id;
        self::setCookies($wishlist);
    }

    public static function setCookies($wishlist) {

        $wishlist = json_encode(stripslashes_deep(array_unique($wishlist)));
        $time = self::get_expiration();
        $_COOKIE[self::$cookie_name] = $wishlist;
        wc_setcookie(self::$cookie_name, $wishlist, $time, false);
    }

    public static function get($recalculate = false) {
        static $wishlist = null;
        if (is_null($wishlist) || $recalculate) {
            $wishlist = !empty($_COOKIE[self::$cookie_name]) ? json_decode(stripslashes($_COOKIE[self::$cookie_name]), true) : array();
        }
        return $wishlist;
    }

    public static function ajax_add() {
        if (!empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $wishlist = self::get();
            $action = !empty($_GET['type']) && $_GET['type'] === 'remove' ? 'remove' : 'add';
            $is_add = in_array($id, $wishlist);
            $event = false;
            if ($action === 'add' && !$is_add) {
                $post = get_post($id);
                if ($post->post_type === 'product') {
                    self::setValue($id);
                    $event = true;
                }
            } elseif ($action === 'remove' && $is_add) {
                $post = get_post($id);
                if ($post->post_type === 'product') {
                    self::removeItem($id);
                    $event = true;
                }
            }
            if ($event) {
                $total = self::get_total();
                die("$total");
            }
        }
        wp_die();
    }

    public static function enqueue_settings($settings) {

        $settings['wishlist'] = array(
            'no_items' => __('There is no wishlist item.', 'themify'),
            'cookie' => self::$cookie_name,
            'expiration' => self::get_expiration(),
            'cookie_path' => COOKIEPATH ? COOKIEPATH : '/',
            'domain' => COOKIE_DOMAIN ? COOKIE_DOMAIN : ''
        );

        return $settings;
    }

    public static function config_setup($themify_theme_config) {
        $config = array();
        foreach ($themify_theme_config['panel']['settings']['tab']['shop_settings']['custom-module'] as $index => $val) {
            if ($index === 2) {
                $config[] = array(
                    'title' => __('Wishlist Settings', 'themify'),
                    'function' => array(__CLASS__, 'config_view')
                );
            }
            $config[] = $val;
        }
        $themify_theme_config['panel']['settings']['tab']['shop_settings']['custom-module'] = $config;

        return $themify_theme_config;
    }

    public static function config_view() {
        $key = 'setting-wishlist_disable';

        $html = '<p><span class="label">' . __('Wishlist', 'themify') . '</span>
                <label for="' . $key . '"><input type="checkbox" id="' . $key . '" name="' . $key . '" ' . checked(themify_get($key), 'on', false) . ' /> ' . __('Disable Wishlist', 'themify') . '</label></p>';


        $page_wishlist = themify_get(self::$key,false,true);
        $front = get_option('page_on_front');

        $args = array(
            'sort_order' => 'asc',
            'sort_column' => 'post_title',
            'post_type' => 'page',
            'post_status' => 'publish',
            'nopaging' => 1
        );

        $pages = new WP_Query($args);

        $html.= '<p data-show-if-element="[name=setting-wishlist_disable]" data-show-if-value=' . '["false"]' . '><span class="label">' . __('Wishlist Page', 'themify') . ' </span>';
        $html.='<select name="' . self::$key . '">';

        while ($pages->have_posts()) {
            $pages->the_post();
            $id = get_the_ID();
            if ($id != $front) {
                $selected = $page_wishlist == $id ? 'selected="selected"' : '';
                $html .= '<option ' . $selected . ' value="' . $id . '">';
                $html .= get_the_title();
                $html .= '</option>';
            }
        }
        $html .= '</select></p>';
        return $html;
    }

    public static function is_enabled() {
        static $is_enabled = null;
        if (is_null($is_enabled)) {
            $is_enabled = !themify_get('setting-wishlist_disable',false,true);
        }
        return $is_enabled;
    }

}

if (Themify_Wishlist::is_enabled()) {
    //Enqueue wishlist settigs
    add_filter('themify_shop_js_vars', array('Themify_Wishlist', 'enqueue_settings'), 10, 1);

    //Add to cart
    add_action('wp_ajax_themify_add_wishlist', array('Themify_Wishlist', 'ajax_add'));
    add_action('wp_ajax_nopriv_themify_add_wishlist', array('Themify_Wishlist', 'ajax_add'));

    //Wishlist Page
    add_action('template_redirect', array('Themify_Wishlist', 'wishlist_page'), 9);

    add_action('after_switch_theme', array('Themify_Wishlist', 'activation'));

	//load wishlist page
	add_action('wp_ajax_themify_load_wishlist_page', array('Themify_Wishlist', 'wishlist_result'));
	add_action('wp_ajax_nopriv_themify_load_wishlist_page', array('Themify_Wishlist', 'wishlist_result'));
}

//Settings Page
add_filter('themify_theme_config_setup', array('Themify_Wishlist', 'config_setup'), 14, 1);
