<?php

/**
 * Utility class of various static functions
 *
 * This class helps to manipulate with arrays
 *
 * @since      1.0.0
 * @package    WPF
 * @subpackage WPF/includes
 * @author     Themify
 */
class WPF_Utils {

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
        if ( ! $language_code ) {
            $language_code = substr( get_bloginfo('language'), 0, 2 );
        }
        $language_code = strtolower(trim($language_code));

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
        if ( defined('ICL_LANGUAGE_CODE') ) {
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
     * Returns the default language code
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

    public static function get_label($label) {
        if (!is_array($label)) {
            return esc_attr($label);
        }
        static $lng = false;
        if ($lng === false) {
            $lng = self::get_current_language_code();
        }
        $value = '';
        if (isset($label[$lng]) && $label[$lng]) {
            $value = $label[$lng];
        } else {
            static $default_lng = false;
            if ($default_lng === false) {
                $default_lng = self::get_default_language_code();
            }
            $value = isset($label[$default_lng]) && $label[$default_lng] ? $label[$default_lng] : current($label);
        }
        return esc_attr($value);
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     * @param string $name
     */
    public static function module_multi_text($id, array $data, array $languages, $key, $name, $input = 'text', $placeholder = false) {
        ?>
        <div class="wpf_back_active_module_row">
            <?php if (!$placeholder): ?>
                <div class="wpf_back_active_module_label">
                    <label for="wpf_<?php echo $id ?>_<?php echo $key ?>"><?php echo $name; ?></label>
                </div>
            <?php endif; ?>
            <?php self::module_language_tabs($id, $data, $languages, $key, $input, $placeholder); ?>
        </div>
        <?php
    }

    /**
     * Echo multilanguage html text for template
     *
     * @since 1.0.0
     *
     * @param number $id input id
     * @param array $data saved data
     * @param array $languages languages array
     * @param string $key
     */
    public static function module_language_tabs($id, array $data, array $languages, $key, $input = 'text', $placeholder = false, $as_array = false) {
        ?>
        <?php if (!empty($languages)): ?>
            <div class="wpf_back_active_module_input">
                <?php if (count($languages) > 1): ?>
                    <ul class="wpf_language_tabs">
                        <?php foreach ($languages as $code => $lng): ?>
                            <li <?php if (isset($lng['selected'])): ?>class="wpf_active_tab_lng"<?php endif; ?>>
                                <a class="wpf_lng_<?php echo $code ?>"  title="<?php echo $lng['name'] ?>" href="#"><?php echo $code ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php
                $name = $as_array ? $id : '[' . $id . ']';
                if ( $key ) {
                    $name .= '[' . $key . ']';
					$values = isset( $data[ $key ] ) ? $data[ $key ] : array();
                } else {
					$values = isset( $data[ $id ] ) ? $data[ $id ] : array();
				}
                ?>
                <ul class="wpf_language_fields">
                    <?php foreach ($languages as $code => $lng ) :
						$value = isset( $values[ $code ] ) ? $values[ $code ] : '';
						?>
                        <li data-lng="wpf_lng_<?php echo $code ?>" <?php if (isset($lng['selected'])): ?>class="wpf_active_lng"<?php endif; ?>>
                            <?php
                            switch ($input) {
                                case 'text':
                                    ?>
                                    <input id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> type="text" class="wpf_towidth"
                                           name="<?php echo $name ?>[<?php echo $code ?>]"
                                           <?php if ( $value ) : ?>value="<?php esc_attr_e( $value ) ?>"<?php endif; ?>/>
                                           <?php
                                           break;
                                       case 'textarea':
                                           ?>
                                    <textarea id="wpf_<?php echo $id ?><?php if ($key): ?>_<?php echo $key ?><?php endif; ?>" <?php if ($placeholder): ?>placeholder="<?php echo $placeholder ?>"<?php endif; ?> class="wpf_towidth"
                                              name="<?php echo $name ?>[<?php echo $code ?>]"><?php if ( $value ): ?> <?php echo stripslashes_deep(esc_textarea(trim( $value ))) ?><?php endif; ?></textarea>
                                              <?php
                                              break;
                                          case 'wp_editor':
                                              $id = 'wpf_' . $id;
                                              if ($key) {
                                                  $id.='_' . $key;
                                              }
                                              $tname = $name . '[' . $code . ']';
                                              wp_editor($value, $id, array('textarea_name' => $tname, 'media_buttons' => false));
                                              break;
                                      }
                                      ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php
    }

    public static function get_default_fields() {
        static $labels = array();
        if (empty($labels)) {
            $labels = array(
                'title' => __('Product Title', 'wpf'),
                'sku' => __('SKU', 'wpf'),
                'wpf_cat' => __('Category', 'wpf'),
                'wpf_tag' => __('Tag', 'wpf'),
                'price' => __('Price', 'wpf'),
                'instock' => __('In Stock', 'wpf'),
                'onsale' => __('On Sale', 'wpf'),
                'submit' => __('Submit Button', 'wpf')
            );
        }

        return $labels;
    }

    public static function get_wc_taxonomies() {
		$product_taxonomies = array();
		foreach ( get_object_taxonomies( 'product', 'objects' ) as $tax ) {
			$product_taxonomies[ $tax->name ] = $tax->label ? $tax->label : $tax->name;
		}

		return $product_taxonomies;
	}

	/**
	 * Returns a list of all field types in WPF
	 *
	 * @return array
	 */
    public static function get_all_field_types() {
		$wc_taxonomies = WPF_Utils::get_wc_taxonomies();
		unset( $wc_taxonomies['product_cat'], $wc_taxonomies['product_tag'], $wc_taxonomies['product_type'], $wc_taxonomies['product_visibility'] );
        $sort_cmb = array_merge( $wc_taxonomies, WPF_Utils::get_default_fields() );
		return $sort_cmb;
    }

    public static function get_current_page() {
        static $page = NULL;
        if (is_null($page)) {
            $page = is_shop() ? wc_get_page_id('shop') : (is_page()?get_the_ID():false);
        }
        return $page;
    }

    public static function strtolower( $text, $escape = true ) {
        $text = function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
        if ( $escape ) {
            $text = sanitize_title($text);
        }

		if ( substr( $text, 0, 4 ) !== 'wpf_' ) {
			$text = 'wpf_' . $text;
		}

		return $text;
    }

    public static function get_field_name(array $item, $orig_name) {

        $title = ! empty( $item['field_title'] ) ? WPF_Utils::get_label( $item['field_title'] ) : $orig_name;
        if ( empty( $title ) ) {
            $title = $orig_name;
        }
        return sanitize_text_field( $title );
    }

    public static function format_text($text) {
        global $wp_embed;

        $text = convert_smilies($text);
        $text = convert_chars($text);
        $text = $wp_embed->autoembed($text);
        $text = wptexturize($text);
        $text = wpautop($text);
        $text = shortcode_unautop($text);
        $text = $wp_embed->run_shortcode($text);
        if (!has_shortcode($text, 'searchandfilter')) {
            $text = do_shortcode($text);
        }
        return $text;
    }
    
    public static function format_price($price,$args=array()){
        if($price===''){
            return $price;
        }
        $price = floatval($price);
        if(strpos($price,'.',1)===false){
            $price = intval($price);
            $args['decimals'] =0;
        }
        return wc_price($price,$args);
    }
    
    /**
     * Check if ajax request
     *
     * @param void
     *
     * return boolean
     */
    public static function is_ajax() {
        static $is_ajax = null;
        if(is_null($is_ajax)){
            $is_ajax = defined('DOING_AJAX') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        }
        return $is_ajax;
    }

	/**
	 * Count the number of published posts in a given $post_type
	 *
	 * return int
	 */
	public static function count_posts( $post_type ) {
		global $wpdb, $sitepress;

		if ( function_exists( 'wpml_prepare_in' ) ) {
			$query = $wpdb->get_results(
				$wpdb->prepare( "
					SELECT language_code, COUNT(p.ID) AS c
					FROM {$wpdb->prefix}icl_translations t
					JOIN {$wpdb->posts} p
						ON t.element_id=p.ID
							AND t.element_type = CONCAT('post_', p.post_type)
					WHERE p.post_type=%s
					AND t.language_code IN (" . wpml_prepare_in( array_keys( $sitepress->get_active_languages() ) ) . ")
					AND post_status IN ( 'publish' )
					GROUP BY language_code",
					$post_type
				)
			);
			if ( is_array( $query ) ) {
				$current_language_code = self::get_current_language_code();
				foreach ( $query as $language_count ) {
					if ( $language_count->language_code === $current_language_code ) {
						return $language_count->c;
					}
				}
			}
		} else {
			return wp_count_posts( $post_type )->publish;
		}
	}

	/**
	 * Gets an object ID (term, post) and returns a list of IDs for that object in all languages
	 *
	 * @return array
	 */
	public static function get_object_id_in_all_languages( $object_id, $type ) {
		$ids = array();
		$languages = WPF_Utils::get_all_languages();
		foreach ( $languages as $code => $language ) {
			$id = apply_filters( 'wpml_object_id', $object_id, $type, false, $code );
			if ( $id ) {
				$ids[ $code ] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Returns the URL to WC's Shop page
	 *
	 * @return string
	 */
	public static function get_shop_page_url() {
		$shop_page_id = (int) wc_get_page_id( 'shop' );
		if ( $shop_page_id <= 0 ) {
			$shop_page = get_post_type_archive_link( 'product' );
		} else {
			$shop_page = urldecode( get_permalink( $shop_page_id ) );
		}

		return $shop_page;
	}

	/**
	 * Determine whether $query needs filtering by WPF
	 *
	 * @param WP_Query $query
	 * @return bool
	 */
	public static function is_wpf_query( $query ) {
		$is =
			( is_post_type_archive( 'product' ) && $query->is_main_query() ) // Shop page
			|| ( $query->is_tax( array( 'product_cat', 'product_tag' ) ) ) // product category and tag archive pages. Note that $query->get('post_type') is empty
			|| isset( $query->query['wpf_rand'] ) // [products] shortcode
			|| isset( $query->query['tf_wc_query'] ) // Themify WooCommerce module
			|| ( isset( $query->query['tbp_aap'] ) && $query->query['post_type'] === 'product' ) // Themify Builder Pro modules
		;

		/**
		 * Whether $query should be filtered by WPF or not.
		 *
		 * @since 1.3.2
		 *
		 * @param bool     $is_wpf
		 * @param WP_Query $query
		 */
		$is = apply_filters( 'wpf_is_product_query', $is, $query );

		return $is;
	}

	/**
	 * Outputs pagination links for WPF
	 *
	 * @return void
	 */
	public static function pagination( $type = 'pagination' ) {
		echo '<div class="wpf-pagination">';

		if ( $type === 'infinity_auto' || $type === 'infinity' ) {
			$total_pages = wc_get_loop_prop( 'total_pages' );
			$current_page = isset( $_REQUEST['wpf_page'] ) ? (int)( $_REQUEST['wpf_page'] ) : 1;
			if ( $total_pages > 1 && $total_pages > $current_page ):
				?>
				<div class="wpf_infinity<?php if ( $type === 'infinity_auto' ): ?> wpf_infinity_auto<?php endif; ?>">
					<a data-max="<?php echo $total_pages ?>" data-current="<?php echo( $current_page + 1 ) ?>"
					   href="javascript:void(0);"><?php _e( 'Load More', 'wpf' ) ?></a>
				</div>
			<?php
			endif;
		} else if ( $type === 'pagination' ) {
			$args = array(
				'total'   => wc_get_loop_prop( 'total_pages' ),
				'current' => wc_get_loop_prop( 'current_page' ),
				'base'    => esc_url_raw( add_query_arg( 'wpf_page', '%#%', false ) ),
				'format'  => '?wpf_page=%#%',
			);
			wc_get_template( 'loop/pagination.php', $args );
		}

		echo '</div>';
	}

	/**
	 * Get current page number in a paginated loop
	 *
	 * @return int
	 */
	public static function get_paged() {
		if ( ! empty( $_GET['wpf_page'] ) ) {
			$page = intval( $_GET['wpf_page'] );
		} else {
			if ( is_front_page() ) {
				$page = get_query_var( 'page', 1 );
			} else {
				$page = get_query_var( 'paged', 1 );
			}
		}

		if ( empty( $page ) ) {
			$page = 1;
		}

		return $page;
	}
}