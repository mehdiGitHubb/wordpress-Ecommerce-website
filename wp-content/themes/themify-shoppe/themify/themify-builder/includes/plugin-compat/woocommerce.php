<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_WooCommerce {

	static function init() {
        $description_hook = themify_get( 'setting-product_description_type','long',true );
	    if ( ! is_admin() ) {
			$description_hook = $description_hook==='long' || !$description_hook? 'the_content' : 'woocommerce_short_description';
			add_filter( $description_hook, array( __CLASS__, 'single_product_builder_content') );
			if('woocommerce_short_description'===$description_hook){
				add_action( 'woocommerce_variable_add_to_cart', array( __CLASS__, 'remove_builder_content_variation' ) );
			}

			// Single Variations Plugin compatibility
			if( class_exists( 'Iconic_WSSV_Query' ) ) {
				add_filter( 'pre_get_posts', array( __CLASS__, 'add_variations_to_product_query' ), 50, 1 );
			}
		}
                elseif('short'===$description_hook){
                    global $pagenow;
                    if(($pagenow === 'post-new.php' && isset($_GET['post_type']) && 'product'===$_GET['post_type']) || ('post.php' === $pagenow && isset($_GET['post']) && 'product'===get_post_type( $_GET['post']))){
                        add_filter( 'themify_builder_active_vars', array( __CLASS__, 'short_desc_builder_badge' ) );
                    }
                }
		add_action( 'woocommerce_archive_description', array( __CLASS__, 'wc_builder_shop_page' ), 11 );
		add_action( 'woocommerce_before_template_part', array( __CLASS__, 'before_woocommerce_templates' ) );
		add_action( 'woocommerce_after_template_part', array( __CLASS__, 'after_woocommerce_templates' ) );
		add_filter( 'woocommerce_short_description', array( __CLASS__, 'fix_static_content_on_shop_page' ) );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'woocommerce_product_tabs' ) );

		/**
		 * disable Builder on Shop page
		 * self::wc_builder_shop_page() handles this
		 */
		add_action( 'woocommerce_before_main_content', array( __CLASS__, 'woocommerce_before_main_content' ) );
	}

    /*
     * Localize a value to add builder static content to short description
     * */
    public static function short_desc_builder_badge( $vars ) {
        $vars['short_badge'] = true;
        return $vars;
    }

	public static function woocommerce_before_main_content() {
		if ( themify_is_shop() ) {
			$GLOBALS['ThemifyBuilder']->reset_builder_query( 'reset' );
			add_action( 'woocommerce_after_main_content', array( __CLASS__, 'woocommerce_after_main_content' ) );
		}
	}

	public static function woocommerce_after_main_content() {
		$GLOBALS['ThemifyBuilder']->reset_builder_query( 'restore' );
	}

	/**
	 * Remove builder content filter from variation short description
	 */
	public static function remove_builder_content_variation() {
		global $post;
		if ( $post->post_type === 'product' && is_product()) {
			remove_filter( 'woocommerce_short_description', array( __CLASS__, 'single_product_builder_content') );
		}
	}

	/**
	 * Show builder on Shop page.
	 *
	 * @access public
	 */
	public static function wc_builder_shop_page() {
		if ( themify_is_shop() ) {
			echo self::show_builder_content( Themify_Builder_Model::get_ID() );
		}
	}

	/**
	 * Avoid render buider content in WooCommerce content
	 */

	public static function before_woocommerce_templates() {
		if( Themify_Builder_Model::is_front_builder_activate() ) {
			global $ThemifyBuilder;
			remove_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front'), 11 );
		}
	}

	public static function after_woocommerce_templates() {
		if( Themify_Builder_Model::is_front_builder_activate() ) {
			global $ThemifyBuilder;
			add_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
		}
	}

	/**
	 * Removes Builder static content from Shop page
	 *
	 * @return string
	 */
	public static function fix_static_content_on_shop_page( $content ) {
		if ( is_post_type_archive( 'product' ) ) {
			$content = ThemifyBuilder_Data_Manager::update_static_content_string( '', $content );
		}

		return $content;
	}

	/**
	 * Ensure "Description" product tab is visible on frontend even if there are no description,
	 * so that Builder frontend editor can be used.
	 *
	 * Hooked to "woocommerce_product_tabs"
	 *
	 * @return array
	 */
	public static function woocommerce_product_tabs( $tabs ) {
		if ( is_singular( 'product' ) && ! isset( $tabs['description'] ) && Themify_Builder_Model::is_frontend_editor_page() ) {
			$tabs['description'] = array(
				'title' => __( 'Description', 'themify' ),
				'priority' => 10,
				'callback' => 'woocommerce_product_description_tab',
			);
		}

		return $tabs;
	}

	private static function show_builder_content($id,$content=''){
		global $ThemifyBuilder;
		return $ThemifyBuilder->get_builder_output( $id, $content );
	}

	/**
	 * Render builder content for Single products
	 *
	 * @access public
	 * @return string
	 */
	public static function single_product_builder_content( $content ) {
		global $post;
		if ( isset( $post->post_type ) && $post->post_type === 'product' && is_product()) {
			$content = self::show_builder_content( $post->ID, $content );
		}

		return $content;
	}

	public static function add_variations_to_product_query($q){
		if ('product' !== $q->get('post_type') && !$q->is_search ) {
			return $q;
		}

		// Add product variations to the query
		$post_type   = (array) $q->get( 'post_type' );
		$post_type[] = 'product_variation';
		if ( ! in_array( 'product', $post_type ) ) {
			$post_type[] = 'product';
		}
		$q->set( 'post_type', array_filter( $post_type ) );

		// Don't get variations with unpublished parents
		$unpublished_variable_product_ids = Iconic_WSSV_Query::get_unpublished_variable_product_ids();
		if ( ! empty( $unpublished_variable_product_ids ) ) {
			$post_parent__not_in = (array) $q->get( 'post_parent__not_in' );
			$q->set( 'post_parent__not_in', array_merge( $post_parent__not_in, $unpublished_variable_product_ids ) );
		}

		// Don't get variations with missing parents :(
		$variation_ids_with_missing_parent = Iconic_WSSV_Query::get_variation_ids_with_missing_parent();
		if ( ! empty( $variation_ids_with_missing_parent ) ) {
			$post__not_in = (array) $q->get( 'post__not_in' );
			$q->set( 'post__not_in', array_merge( $post__not_in, $variation_ids_with_missing_parent ) );
		}

		if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
			// update the meta query to include our variations
			$meta_query = (array) $q->get( 'meta_query' );
			$meta_query = Iconic_WSSV_Query::update_meta_query( $meta_query );
			$q->set( 'meta_query', $meta_query );
		} else {
			// update the tax query to include our variations
			$tax_query = (array) $q->get( 'tax_query' );
			$tax_query = Iconic_WSSV_Query::update_tax_query( $tax_query );
			$q->set( 'tax_query', $tax_query );
		}

		return $q;
	}
}