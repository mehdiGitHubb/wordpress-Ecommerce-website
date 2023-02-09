<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WPF
 * @subpackage WPF/public
 * @author     Themify
 */
class WPF_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	private static $result = '';
	private static $result_page = null;
	private $shortcode_id = '';
	private $pagination = false;
	private $post_count;
	private $append = false;
	private $not_found = false;
	private $custom_cols;

	/**
	 * List of templates that are nullified, so they will show no output.
	 *
	 * @type array
	 */
	private $nulled_templates = array();

    /**
     * Creates or returns an instance of this class.
     *
     * @return    A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        if ( $instance === null ) {
            $instance = new self;
        }
        return $instance;
    }

	private function __construct() {

		$this->plugin_name = WPF::get_instance()->get_plugin_name();
		$this->version = WPF::get_instance()->get_version();
		if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
			add_filter( 'script_loader_tag', array( $this, 'defer_js' ), 11, 3 );
			add_action( 'wp_head', array( $this, 'result_page' ), 1 );
		} else {
			add_action( 'wp_ajax_wpf_autocomplete', array( $this, 'autocomplete' ) );
			add_action( 'wp_ajax_nopriv_wpf_autocomplete', array( $this, 'autocomplete' ) );
		}

		add_filter( 'widget_text', array( $this, 'widget_text' ), 10, 2 );
		add_filter( 'widget_text_content', array( $this, 'get_shortcode' ), 12 );

		add_shortcode( 'searchandfilter', array( $this, 'shortcode' ) );

		if ( ! empty( $_GET['wpf'] ) ) {
			add_filter( 'woocommerce_shortcode_products_query', array( $this, 'woocommerce_shortcode_products_query' ) );
			add_filter( 'shortcode_atts_products', array( $this, 'shortcode_atts_products' ) );

			add_action( 'pre_get_posts', array( $this, 'change_shop_query' ), 99 );
			add_filter( 'wc_get_template', array( $this, 'hide_templates' ), 100, 5 );
		}
	}

	/**
	 * Process plugin's shortcode in Text widget
	 *
	 * @since 1.0.8
	 * @return string $text
	 */
	function widget_text( $text, $instance = array() ) {
		global $wp_widget_factory;

		/* check for WP 4.8.1+ widget */
		if ( isset( $wp_widget_factory->widgets['WP_Widget_Text'] ) && method_exists( $wp_widget_factory->widgets['WP_Widget_Text'], 'is_legacy_instance' ) && !$wp_widget_factory->widgets['WP_Widget_Text']->is_legacy_instance( $instance ) ) {
			return $text;
		}

		/*
		 * if $instance['filter'] is set to "content", this is a WP 4.8 widget,
		 * leave it as is, since it's processed in the widget_text_content filter
		 */
		if ( isset( $instance['filter'] ) && 'content' === $instance['filter'] ) {
			return $text;
		}

		$text = $this->get_shortcode( $text );
		return $text;
	}

	public function get_shortcode( $text ) {
		if ( $text && has_shortcode( $text, 'searchandfilter' ) ) {
			$text = WPF_Utils::format_text( $text );
			$text = do_shortcode( $text );
		}
		return $text;
	}

	/**
	 * Register the Javascript/Stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_assets() {
		global $wp_version;

		$plugin_url = plugin_dir_url( __FILE__ );
		$translation_ = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'url' => trailingslashit( $plugin_url ),
			'ver' => $this->version,
			'rtl' => is_rtl(),
			'includes_url' => trailingslashit( includes_url() ),
			'load_jquery_ui_widget' => version_compare( $wp_version, '5.6', '<' ),
		);
		wp_register_style( $this->plugin_name . '-select', $plugin_url . 'css/select2.min.css', false, $this->version, false );

		wp_register_script( $this->plugin_name, $plugin_url . 'js/wpf-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'wpf', $translation_ );
		wp_register_style( $this->plugin_name . 'ui-css', $plugin_url . 'css/jquery-ui/jquery-ui.min.css', false, $this->version, false );
		wp_register_style( $this->plugin_name, $plugin_url . 'css/wpf-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueues an scripts, and then adds it to the list of dependencies of wpf-public.js script,
	 * this ensures the script loads "before" wpf-public script.
	 *
	 * @return void
	 */
	public function enqueue_script( $handle ) {
		wp_enqueue_script( $handle );

		global $wp_scripts;
		if ( ! isset( $wp_scripts->registered['wpf'] ) ) {
			return;
		}
		$wp_scripts->registered['wpf']->deps[] = $handle;
	}

	/**
	 * Load plugin's assets using defer method
	 *
	 * Hooked to "script_loader_tag"
	 *
	 * @return string
	 */
	function defer_js( $tag, $handle, $src ) {
		if ( in_array( $handle, array(
			$this->plugin_name,
			$this->plugin_name . '-select',
		) ) ) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );
		}

		return $tag;
	}

	/**
	 * Enqueues the JS/CSS for frontend
	 *
	 * @since 1.2.8
	 */
	public function enqueue_assets() {
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( $this->plugin_name );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $atts
	 *
	 * @return string|void
	 */
	public function shortcode( $atts ) {
		if ( empty( $atts['id'] ) ) {
			return;
		}
		$id = sanitize_text_field( $atts['id'] );
		$option = WPF_Options::get_option( $this->plugin_name, $this->version );
		$forms = $option->get();
		if ( empty( $forms[ $id ] ) ) {
			return;
		}
		if ( !wp_script_is( $this->plugin_name ) ) {
			wp_enqueue_script( $this->plugin_name );
			self::load_wc_scripts();
		}
		$request = array();
		$this->shortcode_id = $id;
		if ( ! empty( $_REQUEST['wpf'] ) ) {
			$option = WPF_Options::get_option( $this->plugin_name, $this->version );
			$forms = $option->get();
			if ( !empty( $forms[ $_REQUEST['wpf'] ] ) ) {
				$this->shortcode_id = $_REQUEST['wpf'];
				$request = $this->parse_query( $_REQUEST, $forms[ $_REQUEST['wpf'] ], false );
			}
		}
		$wpf_form = new WPF_Form( $this->plugin_name, $this->version, $id );
		return $wpf_form->public_themplate( $forms[ $id ], self::$result_page, $request );
	}

	public function result_page() {
		if ( ! empty( $_POST['wpf'] ) ) {
		    $option = WPF_Options::get_option( $this->plugin_name, $this->version );
			$forms = $option->get();

			if ( !empty( $forms[ $_REQUEST['wpf'] ] ) ) {
				self::$result_page = WPF_Utils::get_current_page();
				$data = $forms[ $_REQUEST['wpf'] ]['data'];
				if ( ( !empty( $data['result_type'] ) && $data['result_type'] === 'same_page' ) || self::$result_page == $data['page'] ) {
					self::load_wc_scripts();
					add_filter( 'body_class', array( $this, 'body_class' ), 10, 1 );
					self::$result = $this->get_result( $_REQUEST, $forms[ $_REQUEST['wpf'] ] );
					if ( is_singular( 'product' ) ) {
						add_filter( 'wc_get_template', array( $this, 'filter_not_found' ), 30, 5 );
					} elseif ( is_woocommerce() ) {
						global $wp_query;
						$this->post_count = $wp_query->post_count;
						$wp_query->post_count = 0;
						add_action( 'woocommerce_after_main_content', array( $this, 'refresh_post_count' ), 1 );
						add_filter( 'wc_get_template', array( $this, 'filter_not_found' ), 30, 5 );
					}
				}
			}
		}
		if ( is_woocommerce() ) {
			add_action( 'woocommerce_before_main_content', array( $this, 'result_container' ), 100 );
			add_action( 'woocommerce_after_main_content', array( $this, 'close_div' ), 1 );
		}
	}

	public function result_container( $content = '' ) {
		global $wp_current_filter;
		if ( !in_array( 'wpseo_head', $wp_current_filter ) ) {//fix conflict with wpseo(calling the content in the header)
			remove_filter( 'the_content', array( $this, 'result_container' ), 20, 1 );
		}
		$slug = !empty( $_REQUEST['wpf'] ) ? sanitize_key( $_REQUEST['wpf'] ) : $this->shortcode_id;
		$option = WPF_Options::get_option( $this->plugin_name, $this->version );
		$forms = $option->get();
		$is_infinity = '';
		if ( !empty( $forms[ $slug ] ) ) {
			$template = $forms[ $slug ];
			$is_result_page = ( !empty( $template['data']['result_type'] ) && $template['data']['result_type'] === 'same_page' ) || self::$result_page == $template['data']['page'];
			$show_result_in_same_page = isset( $template['data']['result_type'] ) && $template['data']['result_type'] === 'same_page';
			$show_form_in_results = !isset( $template['data']['show_form_in_results'] ) || $template['data']['show_form_in_results'] !== 'show_form_in_results' ? false : true;
			$is_infinity = isset( $template['data']['pagination_type'] ) && $template['data']['pagination_type'] !== 'pagination' ? ' wpf_infinity_container' : '';
			if ( $is_result_page && !$show_result_in_same_page && $show_form_in_results ) {
				$request = array();
				$this->shortcode_id = $slug;
				if ( ! empty( $_REQUEST['wpf'] ) ) {
					$option = WPF_Options::get_option( $this->plugin_name, $this->version );
					$forms = $option->get();
					if ( !empty( $forms[ $_REQUEST['wpf'] ] ) ) {
						$this->shortcode_id = $_REQUEST['wpf'];
						$request = $this->parse_query( $_REQUEST, $forms[ $_REQUEST['wpf'] ], false );
					}
				}
				$wpf_form = new WPF_Form( $this->plugin_name, $this->version, $slug );
				echo $wpf_form->public_themplate( $forms[ $slug ], self::$result_page, $request );
			}
		}
		if ( is_woocommerce() ) {
			echo '<div data-slug="' . $slug . '" class="wpf-search-container' . $is_infinity . '">';
			if ( !empty( self::$result ) ) {
				ob_start();
			}
		} else {
			return $content . '<div data-slug="' . $slug . '" class="wpf-search-container' . $is_infinity . '">' . self::$result . '</div>';
		}
	}

	public function close_div() {
		if ( !empty( self::$result ) ) {
			ob_end_clean();
		}
		echo self::$result . '</div>';
	}

	public function wrap_pagination() {
		remove_action( 'woocommerce_after_shop_loop', array( $this, 'wrap_pagination' ), 1 );
		$cl = 'wpf-pagination';
		if ( $this->pagination === false || $this->pagination !== 'pagination' ) {
			$cl .= ' wpf-hide-pagination';
		}
		echo '<div class="' . $cl . '">';
	}

	public function refresh_post_count() {
		global $wp_query;
		$wp_query->post_count = $this->post_count;
	}

	public function filter_not_found( $located, $template_name, $args, $template_path, $default_path ) {
		return $template_name === 'loop/no-products-found.php' ? plugin_dir_path( __FILE__ ) . 'templates/no-products-found.php' : $located;
	}

	public function hide_templates( $located, $template_name, $args, $template_path, $default_path ) {

		return in_array( $template_name, $this->nulled_templates, true ) ? $this->filter_not_found( $located, 'loop/no-products-found.php', $args, $template_path, $default_path ) : $located;
	}

	public function get_result( array $data, array $form ) {
		$query_args = $this->parse_query( $data, $form );
		$result = false;

		if ( !empty( $query_args ) ) {
			if ( !empty( $this->templates ) ) {
				add_filter( 'wc_get_template', array( $this, 'hide_templates' ), 100, 5 );
			}
			add_action( 'woocommerce_after_shop_loop', array( $this, 'wrap_pagination' ), 1 );
			if ( !WPF_Utils::is_ajax() || ( isset( $_POST['wpf_page_id'] ) && wc_get_page_id( 'shop' ) != $_POST['wpf_page_id'] ) ) {
				add_filter( 'woocommerce_show_page_title', '__return_false', 99, 1 );
			} else {
				add_filter( 'woocommerce_page_title', array( $this, 'get_page_title' ) );
			}

			if ( ( ( isset( $data['price-to'] ) && (int)$data['price-to'] !== $data['price-to'] ) || ( isset( $data['price-from'] ) && (int)$data['price-from'] !== $data['price-from'] ) ) &&
				( ( ( $data['price-from'] >= 0 && $data['price-from'] <= 1 ) || ( !empty( $form['layout']['price']['step'] ) && $form['layout']['price']['step'] <= 1 ) ) ) ) {
				add_filter( 'get_meta_sql', array( $this, 'themify_add_decimal_params' ) );
			}

			add_filter( 'post_class', array( $this, 'post_classes' ), 10, 1 );
			$sort_bar = !is_woocommerce() && ( empty( $form['data']['result'] ) || empty( $form['data']['sort'] ) );
			$query_args = apply_filters( 'wpf_query', $query_args );
			if ( !empty( $data['s'] ) ) {
				$query_args['s'] = $data['s'];
			}
			if ( $sort_bar ) {
				global $wp_query;
				$is_post_type_archive = $wp_query->is_post_type_archive;
				$wp_query->is_post_type_archive = true;
			}
			query_posts( $query_args );
			if ( WPF_Utils::is_ajax() ) {
				global $wp_filter, $themify;
				unset( $wp_filter['woocommerce_archive_description'] );
				if ( function_exists( 'themify_check' ) ) {
					$themify->post_layout = themify_check( 'setting-products_layout' ) ? themify_get( 'setting-products_layout' ) : 'list-post';
				}
			}
			$this->custom_cols = !empty( $data['wpf_cols'] ) && (int)$data['wpf_cols'] > 0 ? (int)$data['wpf_cols'] : false;
			if ( $this->custom_cols ) {
				add_filter( 'loop_shop_columns', array( $this, 'loop_columns' ) );
				if(!empty($GLOBALS['themify'])) {
					$temp = $GLOBALS['themify']->post_layout;
					$GLOBALS['themify']->post_layout = 'grid'.$this->custom_cols;
				}
			}
			ob_start();
			woocommerce_content();
			$result = ob_get_contents();
			ob_end_clean();
			if ( $this->custom_cols ) {
				remove_filter( 'loop_shop_columns', array( $this, 'loop_columns' ) );
				if(!empty($GLOBALS['themify'])) {
					$GLOBALS['themify']->post_layout = $temp;
				}
			}
			wp_reset_query();
			remove_action( 'woocommerce_after_shop_loop', array( $this, 'wrap_pagination' ), 1 );
			if ( !WPF_Utils::is_ajax() ) {
				remove_filter( 'woocommerce_show_page_title', '__return_false', 99, 1 );
			} else {
				remove_filter( 'woocommerce_page_title', array( $this, 'get_page_title' ) );
			}
			remove_filter( 'post_class', array( $this, 'post_classes' ), 10, 1 );
			if ( $sort_bar ) {
				$wp_query->is_post_type_archive = $is_post_type_archive;
			}
			remove_filter( 'wc_get_template', array( $this, 'hide_templates' ), 100, 5 );
		}
		// Check for Divi theme and add woocommerce container
		$active_theme = wp_get_theme();
		$result = 'Divi' === $active_theme->get('Name') ? '<div class="woocommerce">'.$result.'</div>' : $result;
		$result = '<div class="wpf-search-wait"></div>' . $result;
		if ( !$this->not_found ) {
			$result .= '</div>';
		}
		return $result;
	}

	function pagination( $query ) {
		/* fix issue with SEO plugins running the main loop in the <head> */
		if ( ! did_action( 'wp_body_open' ) ) {
			return;
		}

		if ( ! WPF_Utils::is_wpf_query( $query ) ) {
			return;
		}

		remove_action( 'loop_end', array( $this, 'pagination' ) );
		wc_set_loop_prop( 'total_pages', $query->max_num_pages );
		wc_set_loop_prop( 'current_page', WPF_Utils::get_paged() );

		remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 20 );
		remove_action( 'woocommerce_after_shop_loop', 'themify_theme_shop_pagination', 10 );
		// disables pagination links
		$query->found_posts = 1;
		$query->max_num_pages = 0;

		WPF_Utils::pagination( $this->pagination );

		$this->nullify_template( 'loop/pagination.php' );
	}

	public function parse_query( array $post, array $form, $build = true ) {
		$layout = $form['layout'];
		$data = $form['data'];
		if ( $build ) {
			$query_args = array(
				'post_type' => [ 'product' ],
				'post_status' => 'publish',
				'wc_query' => 1,
				'is_paginated' => 1,
				'meta_query' => array(),
				'tax_query' => array(),
				'post__not_in' => array(),
				'posts_per_page' => !empty( $data['posts_per_page'] ) ? (int)$data['posts_per_page'] : apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ),
				'paged' => WPF_Utils::get_paged(),
			);
			if ( ! empty( $data['variations'] ) ) {
				$query_args['post_type'][] = 'product_variation';
			}

			$query_args['offset'] = ( $query_args['paged'] - 1 ) * $query_args['posts_per_page'];

			$this->pagination = $data['pagination_type'];
			if ( ! empty( $data['pagination'] ) ) {
				$this->pagination = false;
			} else {
				add_action( 'loop_end', array( $this, 'pagination' ) );
			}


			if ( !empty( $post['orderby'] ) ) {
				$this->set_order( $post['orderby'], $query_args );
			} else {
				$this->set_order( get_option( 'woocommerce_default_catalog_orderby','menu_order title' ), $query_args );
			}
			$query_args['order'] = !empty( $query_args['order'] ) ? $query_args['order'] : 'ASC';
			if ( $this->append || !empty( $data['sort'] ) ) {
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 30 );
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
				$this->nullify_template( 'loop/orderby.php' );
			}
			if ( $this->append || !empty( $data['result'] ) ) {
				remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
				$this->nullify_template( 'loop/result-count.php' );
			}
			add_action( 'woocommerce_no_products_found', array( $this, 'not_found_product' ), 10, 1 );
		}
		$args = array();
		if ( empty( $layout['wpf_cat'] ) && is_product_category() ) {
		    $layout['wpf_cat'] = [ 'logic' => 'in' ];
        }
		foreach ( $layout as $type => $item ) {
			if ( $type !== 'submit' ) {
				$key = WPF_Utils::strtolower( WPF_Utils::get_field_name( $item, $type ) );
				$key = urldecode( $key );

				if ( $type === 'price' && ( empty( $item['price_type'] ) || $item['price_type'] === 'slider' ) ) {
					$key .= '-from';
				}
				if ( !empty( $post[ $key ] ) || ( isset( $post[ $key ] ) && $post[ $key ] === '0' ) ) {

					if ( $type === 'price' ) {
						if ( empty( $item['price_type'] ) || $item['price_type'] === 'slider' ) {
							$key2 = WPF_Utils::strtolower( WPF_Utils::get_field_name( $item, $type ) );
							$key2 = urldecode( $key2 );
							$val = array( 'from' => intval( $post[ $key ] ), 'to' => intval( $post[ $key2 . '-to' ] ) );
						} else {
							$tmp_v = explode( '-', $post[ $key ] );
							$val = array( 'from' => floatval( $tmp_v[0] ), 'to' => floatval( $tmp_v[1] ) );
						}

						// WooCommerce Multilingual plugin support
						global $woocommerce_wpml;
						if ( isset( $woocommerce_wpml ) && ! empty( $woocommerce_wpml->settings['enable_multi_currency'] ) ) {
							$currency = $woocommerce_wpml->multi_currency->get_client_currency();
							if ( $currency !== wcml_get_woocommerce_currency_option() ) {
								$exchange_rates = $woocommerce_wpml->multi_currency->get_exchange_rates();
								$exchange_rate = $exchange_rates[ $currency ];

								/* update prices to match the value stored in DB */
								foreach ( $val as $i => $v ) {
									$val[ $i ] /= $exchange_rate;
									if ( in_array( $currency, $woocommerce_wpml->multi_currency->get_currencies_without_cents() ) ) {
										$val[ $i ] = $woocommerce_wpml->multi_currency->round_up( $val[ $i ] );
									}
								}
							}
						}

					} else {
						$val = is_array( $post[ $key ] ) ? $post[ $key ] : sanitize_text_field( urldecode( $post[ $key ] ) );
					}
					if ( $build ) {
						$this->build_query( $type, $val, $query_args, $item );
					} else {
						$args[ $type ] = $val;
					}
				}
			}
		}

		if ( $build ) {
			if ( !empty( $query_args['tax_query'] ) ) {
				$query_args['tax_query']['relation'] = isset( $data['tax_relation'] ) ? $data['tax_relation'] : 'OR';
			}

			if ( !empty( $data['out_of_stock'] ) ) {
				$query_args['meta_query'][] = array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => 'NOT IN'
				);
				if ( !empty( $query_args['tax_query'] ) ) {
					$this->filter_variable_outofstock_product( $query_args );
				}
			}

			if ( !empty( $query_args['post__not_in'] ) ) {
				$query_args['post__not_in'] = array_unique( $query_args['post__not_in'] );
			}
		}

		return $build ? $query_args : $args;
	}

	public function not_found_product( $wc_no_products_found ) {
		$this->not_found = true;
	}

	public function build_query( $type, $value, &$query_args, $data = false ) {

		if ( !isset( $data['logic'] ) ) {
			$data['logic'] = false;
		}
		switch ( $type ) {
			case 'title':
				$query_args['s'] = sanitize_text_field( $value );
				break;
			case 'sku':
				$query_args['meta_query'][] = array(
					'key' => '_sku',
					'value' => sanitize_text_field( $value ),
					'compare' => 'LIKE'
				);
				break;
			case 'price':
				$query_args['meta_query'][] = array(
					'key' => '_price',
					'value' => array( $value['from'], $value['to'] ),
					'compare' => 'BETWEEN',
					'type' => 'DECIMAL'
				);
				break;

			case 'onsale':
				$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
				break;

			case 'instock':
				$query_args['meta_query'][] = array(
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '=',
				);
				break;

			case 'wpf_tag':
			case 'wpf_cat':
				$query_args['tax_query'][] = array(
					'taxonomy' => str_replace( 'wpf', 'product', $type ),
					'field' => 'slug',
					'terms' => is_array( $value ) ? $value : explode( ',', $value ),
					'operator' => $data['logic'] === 'and' ? 'AND' : 'IN',
					'include_children' => !isset( $data['include'] ) || $data['include'] !== 'no'
				);
				break;

			default:
				$taxes = WPF_Utils::get_wc_taxonomies();

				if ( isset( $taxes[ $type ] ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => $type,
						'field' => 'slug',
						'terms' => is_array( $value ) ? $value : explode( ',', $value ),
						'operator' => $data['logic'] === 'and' ? 'AND' : 'IN'
					);
				}

				break;
		}
		return $query_args;
	}


	private function filter_variable_outofstock_product( &$query_args ) {
		global $wpdb;

		$conditions = array();
		$opt = !empty( $query_args['tax_query']['relation'] ) ? strtoupper( $query_args['tax_query']['relation'] ) : 'AND';
		$joins = array(); // for AND Operator only.

		foreach ( $query_args['tax_query'] as $k => $tax_q ) {
			if ( strtolower( $k ) === 'relation' ) continue;
			$meta_key = 'attribute_' . $tax_q['taxonomy'];
			$value = $tax_q['terms'];

			foreach ( $value as $k => $tmp ) {
				$value[ $k ] = '"' . $tmp . '"';
			}
			$value = implode( ',', $value );
			$temp = "(%s.meta_key = '$meta_key' AND %s.meta_value IN ($value))";

			array_push( $conditions, $temp );
		}

		if ( count( $conditions ) > 1 ) {
			$i = 1;
			foreach ( $conditions as $k => $condition ) {
				if ( $opt === 'AND' ) {
					if ( $k === 0 ) {
						$conditions[ $k ] = sprintf( $condition, 'l', 'l' );
					} else {
						$key = 'pa' . $i;
						$conditions[ $k ] = sprintf( $condition, $key, $key );
						$i++;
						$temp = "LEFT JOIN `{$wpdb->postmeta}` AS " . $key . " ON " . $key . ".post_id = l.post_id";
						array_push( $joins, $temp );
					}
				} else {
					$conditions[ $k ] = sprintf( $condition, 'l', 'l' );
				}
			}
		} else {
			$conditions[0] = sprintf( $conditions[0], 'l', 'l' );
		}

		$conditions = implode( ' ' . $opt . ' ', $conditions );
		$joins = implode( ' ', $joins );
		$get_products = $wpdb->get_results( "SELECT DISTINCT l.post_id, r.meta_value FROM `{$wpdb->postmeta}` AS l LEFT JOIN `{$wpdb->postmeta}` AS r ON r.post_id = l.post_id AND r.meta_key = '_stock_status' " . $joins . " WHERE " . $conditions, ARRAY_A );
		if ( !empty( $get_products ) ) {
			$instock = array();
			$outofstock = array();
			foreach ( $get_products as $p ) {
				if ( !empty( $p['meta_value'] ) ) {
					if ( $p['meta_value'] === 'instock' ) array_push( $instock, $p['post_id'] );

					if ( $p['meta_value'] === 'outofstock' ) array_push( $outofstock, $p['post_id'] );
				}
			}
			$stock = array_merge( $instock, $outofstock );
			$stock = array_unique( $stock );
			if ( !empty( $stock ) ) {
				$get_products = $wpdb->get_results( "SELECT `post_parent`, `ID` FROM `{$wpdb->posts}` WHERE `post_parent` <> 0 AND `post_status` = 'publish' AND `ID` IN (" . implode( ',', $stock ) . ")" );
				$n_outofstock = array();
				$n_instock = array();
				if ( !empty( $get_products ) ) {
					foreach ( $get_products as $p ) {
						if ( in_array( $p->ID, $instock ) ) array_push( $n_instock, $p->post_parent );

						if ( in_array( $p->ID, $outofstock ) ) array_push( $n_outofstock, $p->post_parent );
					}
					unset( $instock, $outofstock, $stock );
					$n_instock = array_unique( $n_instock );
					$n_outofstock = array_unique( $n_outofstock );
					foreach ( $n_outofstock as $k => $s ) {
						if ( in_array( $s, $n_instock ) ) unset( $n_outofstock[ $k ] );
					}
					foreach ( $query_args['post__not_in'] as $k => $s ) {
						if ( in_array( $s, $n_instock ) ) unset( $query_args['post__not_in'][ $k ] );
					}
					$query_args['post__not_in'] = array_merge( $query_args['post__not_in'], $n_outofstock );
				}
			}
		}
	}

	public function get_page_title( $title ) {
		if ( !empty( $_POST['wpf_page_id'] ) ) {
			$p = get_post( $_POST['wpf_page_id'] );
			if ( !empty( $p ) ) {
				$title = $p->post_title;
			}
		}
		return $title;
	}

	public function post_classes( $classes ) {
		$classes[] = 'product';
		return $classes;
	}

	public function body_class( $classes ) {
		$classes[] = 'woocommerce';
		return $classes;
	}

	private static function load_wc_scripts() {

		static $script_loaded = false;
		if ( !$script_loaded ) {
			$script_loaded = true;
			WC_Frontend_Scripts::load_scripts();
		}
	}

	private function set_order( $order, &$query ) {

		$_GET['orderby'] = $order;

		switch ( $order ) {
			case 'rand' :
				$query['orderby'] = 'rand';
				break;
			case 'title' :
			case 'title-desc' :
				$query['orderby'] = 'title';
				$query['order'] = $order == 'title-desc' ? 'DESC' : 'ASC';
				break;
			case 'price':
			case 'price-desc':
				$query['meta_key'] = '_price';
				$query['orderby'] = "meta_value_num ID";
				$query['order'] = $order === 'price' ? 'asc' : 'desc';
				break;
			case 'date':
				$query['orderby'] = 'date';
				$query['order'] = 'desc';
				break;
			case 'popularity':
				$query['meta_key'] = 'total_sales';
				// Sorting handled later though a hook
				add_filter( 'posts_clauses', array( $this, 'order_by_popularity' ), 10, 1 );
				break;
			case 'rating' :
				// Sorting handled later though a hook
				add_filter( 'posts_clauses', array( $this, 'order_by_rating' ), 10, 1 );
				break;
			case 'menu_order' :
			case 'menu_order title' :
				$query['orderby'] = 'menu_order title';
				$query['order'] = 'asc';
				break;
            default:
	            $query['orderby'] = $order;
	            break;
		}
	}

	/**
	 * WP Core doens't let us change the sort direction for invidual orderby params
	 *
	 * This lets us sort by meta value desc, and have a second orderby param.
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 */
	public function order_by_popularity( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->posts.menu_order ASC, $wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
		remove_filter( 'posts_clauses', array( $this, 'order_by_popularity' ), 10, 1 );
		return $args;
	}

	/**
	 * Order by rating post clauses.
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 */
	public function order_by_rating( $args ) {
		global $wpdb;

		$args['fields'] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
		$args['where'] .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
		$args['join'] .= "
                    LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
                    LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
            ";
		$args['orderby'] = "$wpdb->posts.menu_order ASC, average_rating DESC, $wpdb->posts.post_date DESC";
		$args['groupby'] = "$wpdb->posts.ID";
		remove_filter( 'posts_clauses', array( $this, 'order_by_rating' ), 10, 1 );
		return $args;
	}

	public function autocomplete() {
		if ( !empty( $_POST['key'] ) && in_array( $_POST['key'], array( 'sku', 'title' ) ) && !empty( $_POST['term'] ) && strlen( $_POST['term'] ) > 0 ) {
			$args = array(
				'post_type' => array( 'product', 'product_variation' ),
				'post_status' => 'publish',
				'posts_per_page' => 10,
				'no_found_rows' => true
			);

			if ( isset( $_POST['variation'] ) && $_POST['variation'] === 'no' ) {
				unset( $args['post_type'][1] );
			}

			$term = sanitize_text_field( $_POST['term'] );
			$by_title = $_POST['key'] === 'title';
			if ( $by_title ) {
				$args['s'] = $term;
			} else {
				$args['meta_query'] = array( array(
					'key' => '_sku',
					'value' => $term,
					'compare' => 'LIKE',
				) );
			}

			$posts = new WP_Query( $args );
			$options = array();
			while ( $posts->have_posts() ) {
				$posts->the_post();
				$id = get_the_ID();
				$label = $by_title ? get_the_title() : get_post_meta( $id, '_sku', true );

				if ( get_post_type() === 'product_variation' ) {
					// for product variations, visitor is redirected to the product's page
					$parent = get_post( $GLOBALS['post']->post_parent );
					$value = get_permalink( $parent->ID );
				} else {
					// IMPORTANT: use the unfiltered post title as the search query; so it matches the value in DB
					$value = $by_title ? $GLOBALS['post']->post_title : get_post_meta( $id, '_sku', true );
				}

				$options[ $id ] = array( 'id' => $id, 'label' => $label, 'value' => $value );
			}
			echo wp_json_encode( $options );
		}
		wp_die();
	}

	function themify_add_decimal_params( $sqlarr ) {
		remove_filter( 'get_meta_sql', 'themify_add_decimal_params' );

		$sqlarr['where'] = str_replace( 'DECIMAL', 'DECIMAL(8,5)', $sqlarr['where'] );
		return $sqlarr;
	}

	/**
	 * Override default specification for product # per row
	 */
	public function loop_columns() {
		return $this->custom_cols;
	}

	public function get_form( $id ) {
		$option = WPF_Options::get_option( $this->plugin_name, $this->version );
		$forms = $option->get();
		if ( isset( $forms[ $id ] ) ) {
			return $forms[ $id ];
		}

		return false;
	}

	function change_query( $query ) {
		if ( $form = $this->get_form( sanitize_key( $_GET['wpf'] ) ) ) {
			$args = $this->parse_query( $_GET, $form );
			foreach ( $args as $k => $v ) {
				// Don't override the var that is empty and has default value #8913
			    if(empty($args[$k]) && !empty($query->get($k))){
                    continue;
                }
				$query->set( $k, $v );
			}
		}
	}

	/**
	 * Disable default pagination links in [products] shortcode
	 *
	 * @return array
	 */
	function shortcode_atts_products( $atts ) {
	    if('true'===$atts['paginate'] || true===$atts['paginate']){
	        add_action( 'woocommerce_shortcode_before_products_loop', array( $this, 'shortcode_result_count_ordering' ),10,1);
            add_action( 'woocommerce_shortcode_before_products_loop', 'woocommerce_result_count', 20 );
            add_action( 'woocommerce_shortcode_before_products_loop', 'woocommerce_catalog_ordering', 30 );
        }
	    $atts['paginate'] = false;
		$atts['cache'] = false;

		return $atts;
	}

    public function shortcode_result_count_ordering($a){
        wc_set_loop_prop( 'is_paginated', true );
    }

	/**
	 * Modifications to [products] shortcode query.
	 *
	 * @return array
	 */
	function woocommerce_shortcode_products_query( $query ) {
		/* add a bogus random parameter to the [products] shortcode and prevent it from caching the posts */
		$query['wpf_rand'] = uniqid();

		/* force calculate total posts matching, required for pagination */
		unset( $query['no_found_rows'] );

		/* render pagination links from WPF */
		add_action( 'woocommerce_shortcode_after_products_loop', array( $this, 'products_shortcode_pagination' ) );

		return $query;
	}

	/**
	 * Show pagination links for [products]
	 *
	 * @hooked to "woocommerce_shortcode_after_products_loop"
	 */
	function products_shortcode_pagination() {
		remove_action( 'woocommerce_shortcode_after_products_loop', array( $this, 'products_shortcode_pagination' ) ); // deactivate self
		WPF_Utils::pagination( $this->pagination );
	}

	function change_shop_query( $query ) {
		if ( WPF_Utils::is_wpf_query( $query ) ) {
			$this->change_query( $query );
			if ( is_product_category() ) {
				$query->set( 'product_cat', false );
			}
		}
    }

	/**
	 * Adds a template file to list of nullified templates
	 *
	 * @param string $template_name
	 */
	public function nullify_template( $template_name ) {
		$this->nulled_templates[ $template_name ] = $template_name;
	}

	/**
	 * Removes a template file from list of nullified templates
	 *
	 * @param string $template_name
	 */
	public function unnullify_template( $template_name ) {
		unset( $this->nulled_templates[ $template_name ] );
	}
}
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination' );