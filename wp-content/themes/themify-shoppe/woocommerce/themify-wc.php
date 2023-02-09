<?php

add_theme_support( 'woocommerce' );

class Themify_WC{
	
	
	public static $singleImageSize='shop_single';
	public static $loopImageSize='shop_catalog';
	public static $thumbImageSize='shop_thumbnail';
	private static $themify_save=null;
	
	public static function before_init(){
		// Alter or remove success message after adding to cart with ajax.
		add_filter( 'wc_add_to_cart_message_html',array(__CLASS__,'add_to_cart_message'));
		add_filter( 'woocommerce_notice_types',array(__CLASS__,'add_to_cart_message') );
		add_filter('woocommerce_add_to_cart_quantity',array(__CLASS__,'add_to_cart_message'));
		
		add_filter( 'woocommerce_add_to_cart_fragments', array(__CLASS__,'add_to_cart_fragments'));//Adding cart total and shopdock markup to the fragments
		
		//Ajax of Product Slider
		add_action('wp_ajax_themify_product_slider',array(__CLASS__,'loop_slider'));
		add_action('wp_ajax_nopriv_themify_product_slider',array(__CLASS__,'loop_slider'));
		
		
		add_filter('loop_shop_per_page', array(__CLASS__,'products_per_page'), 100 );// Set number of products shown in product archive pages
		
		add_action('template_redirect', array(__CLASS__,'set_wc_vars'), 12);
		add_action('woocommerce_before_template_part',array(__CLASS__,'load_wc_styles'),10,5);
		

		// Hide products in shop page
		if(!is_admin() && themify_check('setting-hide_shop_products',true)) {
			add_action( 'woocommerce_before_main_content', array(__CLASS__,'hide_shop_products') );
		}
		if(themify_check( 'setting-quantity_button', true )){
			add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		}
	}

	public static function set_wc_vars(){
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open' );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination');
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title');
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash' );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash');
		// Set WC image sizes
		add_image_size('cart_thumbnail', 40, 40, true);
		
		//Product Wrapper
		add_action( 'woocommerce_before_shop_loop_item_title', array(__CLASS__,'loop_wrapper_start'),11);
		add_action('woocommerce_after_shop_loop_item',array(__CLASS__,'loop_wrapper_end'), 100);
		add_action('woocommerce_shop_loop_item_title',array(__CLASS__,'loop_product_title'));
		add_filter('woocommerce_product_loop_title_classes',array(__CLASS__,'product_title_class'), 100,1);
		add_action('tf_wc_loop_start',array(__CLASS__,'before_loop'), 100,1);
		
		// Wrap product description
		add_filter( 'woocommerce_short_description', array(__CLASS__,'description_wrap'),10,1);
		
		add_action( 'woocommerce_review_before', array(__CLASS__,'load_comment_review_css') );
		
		//load pagination styles
		add_filter('woocommerce_pagination_args', array(__CLASS__,'load_pagination_styles'));
		add_filter('woocommerce_comment_pagination_args', array(__CLASS__,'load_pagination_styles'));
		add_action('woocommerce_before_account_orders_pagination', array(__CLASS__,'load_pagination_styles'));
		
		//Variable Product link 
		if(themify_get('setting-product_archive_hide_cart_button',false,true) === 'yes' ){
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
		}
		elseif(!themify_check( 'setting-disable_product_lightbox',true )){
			add_filter('woocommerce_loop_add_to_cart_args',  array(__CLASS__,'loop_add_to_cart'), 100, 2);
		}

        global $themify;
        $themify->products_hover_image=themify_get_both('product_slider_hover','setting-products_slider',$themify->products_hover_image);
		if(is_woocommerce() || Themify_Wishlist::$is_wishlist_page===true || (is_page() && ($product_query= themify_get( 'product_query_category','' ))!=='')){
			
			$isShop=themify_is_shop();
			if(is_product()) {
				
				$themify->image_size=self::$singleImageSize;
				list($themify->width,$themify->height)=self::getSingleImageSize();
				$themify->layout = themify_get_both('layout','setting-single_product_layout','sidebar1');
				$themify->hide_title='no';
				$themify->display_content='content';
				
				if(!themify_check( 'setting-hide_shop_single_breadcrumbs',true )){
					add_action( 'woocommerce_single_product_summary', 'woocommerce_breadcrumb',1 );
				}
			
				add_action('woocommerce_before_quantity_input_field',array(__CLASS__,'field_minus'));
				add_action('woocommerce_after_quantity_input_field',array(__CLASS__,'field_plus'));
				
				
				//related Limit
				if (themify_check( 'setting-related_products',true) ) {
					remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
				} 
				else{
					add_filter( 'woocommerce_output_related_products_args', array(__CLASS__,'related_limit'), 100 );
				}
				
				//review tabs
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating');
				
				if(themify_check('setting-product_reviews',true)){
					add_filter( 'woocommerce_product_tabs', array(__CLASS__,'product_reviews'),100,1);
				}
				elseif(themify_check('setting-product_reviews_empty',true)){
					add_action('woocommerce_single_product_summary',array(__CLASS__,'show_product_rating'), 15 );// Always show Rating
				}
				else{
					add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating',15 );//Change position of rating
				}
				
				add_action('woocommerce_after_add_to_cart_button',array(__CLASS__,'share_items') );
				
				add_filter( 'woocommerce_available_variation',array(__CLASS__,'variation_image_size'));//Set variation image sizes
				self::singleProductSlider();
				
				//Change OnSale Position
				add_action('woocommerce_product_thumbnails','woocommerce_show_product_sale_flash',99);
				//Increase variation limit
				add_filter( 'woocommerce_ajax_variation_threshold', array(__CLASS__,'variation_limit'));
				
                if(themify_get_gallery_type()!=='default'){
                    Themify_Enqueue_Assets::loadThemeWCStyleModule( 'single/slider' );
                    Themify_Enqueue_Assets::addLocalization('done','theme_single_slider_css',true);
                    Themify_Enqueue_Assets::addPreLoadJs(THEME_URI . '/js/modules/wc/single-slider.js',Themify_Enqueue_Assets::$themeVersion);
                    Themify_Enqueue_Assets::preLoadSwiperJs();
                }
			}
			else{
				$themify->query_category=$isShop===false && isset($product_query) && $product_query!==''?$product_query:'';
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
				if(!is_search() && ! themify_check_both('product_show_sorting_bar', 'setting-hide_shop_sorting')){
					add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 8 );
				}
				if($themify->query_category!==''){// Query Products
					$themify->posts_per_page = themify_get_both('product_posts_per_page', 'setting-shop_products_per_page',get_option( 'posts_per_page' ) );
					$themify->query_post_type ='product';
					$themify->query_taxonomy='product_cat';
					$themify->post_layout =themify_get('product_layout','list-post' ); 
					$themify->page_navigation = themify_get('product_hide_navigation', 'no' );
					$themify->display_content=themify_get('product_archive_show_short','excerpt');
					$themify->order = !empty( $_GET['order'] )?esc_attr($_GET['order']):themify_get('product_order', 'desc' );
					$themify->orderby =!empty( $_GET['orderby'] )?esc_attr($_GET['orderby']): themify_get('product_orderby', 'date' );
					$themify->post_layout_type = themify_get_both( 'product_content_layout','setting-product_content_layout','');
					$themify->hide_title=themify_get_both('product_hide_title','setting-product_archive_hide_title','no');	
					if( $themify->orderby==='meta_value' || $themify->orderby==='meta_value_num' ) {
						$themify->order_meta_key = themify_get( 'product_meta_key');
					}
					add_filter('themify_query_posts_page_args',array(__CLASS__,'product_query'),20,1);
				}
				else{
					$themify->post_layout_type = themify_get( 'setting-product_content_layout','',true);
					$themify->post_layout =themify_get('setting-products_layout','list-post' ,true); 
					$themify->display_content=themify_get('setting-product_archive_show_short','none',true);
					$themify->hide_title=themify_get('setting-product_archive_hide_title','no',true);	
				}
				if(in_array( $themify->post_layout, array('list-large-image', 'list-thumb-image','grid2-thumb','auto_tiles'),true)){
					$themify->post_layout_type ='';
				}
				$themify->image_size=self::$loopImageSize;
				list($themify->width,$themify->height)=self::getLoopImageSize();
				
				$sidebar_layout = themify_get('page_layout','default');
				if ('default' === $sidebar_layout ) {
					$key = $isShop===true?'setting-shop_layout':'setting-shop_archive_layout';
					$sidebar_layout = themify_get($key,'default',true);
					if($sidebar_layout==='default'){
						$sidebar_layout = themify_get('setting-default_layout','sidebar1',true);
					}
				}
				
				$themify->layout=$sidebar_layout;
				
				//Archive Result Count
				if(themify_check( 'setting-hide_shop_count',true  )){
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
				}
				//Archive breadcrumbs
				if(!themify_check( 'setting-hide_shop_breadcrumbs',true )){
					add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
				}
				
			}
			//Product price
			if(themify_get_both('product_hide_price','setting-product_archive_hide_price',false)=== 'yes'){
				remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price');// No product price in product archive pages
			}
			
			//product rating 
			if(themify_check( 'setting-hide_product_rating_stars',true  )){
				remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			}
			elseif(themify_check('setting-products_reviews_empty',true)){
				add_filter( 'woocommerce_product_get_rating_html', array(__CLASS__,'loop_rating_html'), 100, 3 );// Always show rating even for 0 rating
			}

		}
		// Disable product summary in cross sell in cart page
		if(is_cart()){
		    global $themify;
			$themify->display_content='none';
        }
		//Product lightbox
		self::product_lightbox();
	}
	
	public static function loop_wrapper_start(){
	    ?>
	    <div class="product-content">
		    <div class="product-content-inner-wrapper">
			    <div class="product-content-inner">
	    <?php
	}
	
	public static function loop_wrapper_end(){
		self::loop_description();
			self::share_items();
			themify_edit_link();
		?>
					
				</div>
			</div>
		</div>
		<?php
	}
	
	public static function product_title_class($class){
	    return 'product_title';
	}
	
	public static function share_items(){
		global $woocommerce_loop;
		if( empty($woocommerce_loop['name']) && is_product()){
			$showQuick=false;
			$showSocial=! themify_check('setting-single_hide_shop_share',true);
		}
		else{
			static $showQuick=null;
			static $showSocial=null;
			if($showQuick===null){
				$showQuick = !themify_check( 'setting-disable_product_lightbox',true ) && !themify_check_both('product_quick_look', 'setting-hide_shop_more_info');
				$showSocial = !themify_check_both('product_social_share', 'setting-hide_shop_share',true );
			}
		}
		?>
		<div class="product-share-wrap tf_inline_b tf_vmiddle">
				<?php Themify_Wishlist::button() ?>
				<?php if($showQuick===true):?>
					 <a onclick="return false;" class="quick-look themify-lightbox" href="<?php echo get_permalink() ?>" rel="nofollow"><?php echo themify_get_icon(themify_get('setting-ic-quick','ti-zoom-in',true),false,false,false,array('aria-label'=>__('Quick Look','themify')))?><span class="tooltip"><?php _e('Quick Look', 'themify'); ?></span></a>
				<?php endif;?>
				<?php if ($showSocial===true): ?>
					<?php get_template_part('includes/social-share', 'product'); ?>
				<?php endif; ?>
			</div>
		<?php
	}
	
	public static function loop_image($image, $product, $size, $attr, $placeholder, $orig_image ){
		
		if(is_cart() || is_checkout()){
			return $image;
		}
		global $themify;
		$hoverMode=$themify->products_hover_image;
		$alt=get_the_post_thumbnail_caption();
		if($alt===''){
		    $alt=$product->get_title();
		}
		$src=$product->get_image_id();
		if(!$src){
			$p=$product->get_parent_id();
			if ( $p ) {
				$parent_product = wc_get_product( $p );
				if ( $parent_product ) {
					$src = $parent_product->get_image_id();
				}
				unset($parent_product,$p);
			}
			if(!$src){
				$src=wc_placeholder_img_src();
			}
		}
		$gallery = $hoverMode==='enable' || $hoverMode==='first_image'?$product->get_gallery_image_ids():false;
		ob_start();
		$link=$themify->unlink_image==='yes'|| !$product->is_visible()?'':$product->get_permalink();
		?>
		<figure <?php if($hoverMode==='enable'):?>data-product-slider="<?php the_ID()?>" data-w="<?php echo $themify->width?>" data-h="<?php echo $themify->height?>" data-link="<?php echo $link?>"<?php endif;?> class="post-image product-image<?php if($hoverMode==='enable'):?> product-slider<?php if(empty($gallery)):?> slider_attached<?php endif;?><?php endif;?>">
			<?php if($product->is_on_sale()):?>
				<?php woocommerce_show_product_loop_sale_flash();?>
			<?php endif?>
			<?php if($link!==''):?>
            <a href="<?php echo $link;?>">
            <?php endif;?>
				<?php echo themify_get_image(array('alt'=>$alt,'w'=>$themify->width,'h'=>$themify->height,'image_size'=>$themify->image_size,'src'=>$src));?>
			
			<?php if($hoverMode==='first_image' && !empty($gallery)):?>
				<?php 
					$second_alt=wp_get_attachment_caption($gallery[0]);
					if(!$second_alt){
						$second_alt=$alt;
					}
					echo themify_get_image(array('alt'=>$second_alt,'w'=>$themify->width,'h'=>$themify->height,'image_size'=>$themify->image_size,'src'=>$gallery[0],'class'=>'themify_product_second_image tf_bs tf_opacity'));
				?>
			<?php endif;?>
			<?php if($link!==''):?>
            </a>
		    <?php endif;?>
		</figure>
		<?php
		return ob_get_clean();
	}
	
	public static function variation_limit(){
		return 200;
	}
	
	public static function related_limit($args){
		$args['posts_per_page']=themify_get('setting-related_products_limit',3,true);
		return $args;
	}
		
	public static function field_minus(){
		?>
		<input type="button" value="-" id="minus1" class="minus">
		<?php
	}

	public static function field_plus(){
		?>
		<input type="button" value="+" id="add1" class="plus">
		<?php
	}
	
	public static function load_comment_review_css($post_id){
		remove_action( 'woocommerce_review_before',array(__CLASS__,'load_comment_review_css'));
		Themify_Enqueue_Assets::loadThemeWCStyleModule('review');
	}
	
	
	public static function product_reviews($tabs){
		unset($tabs['reviews']);
		return $tabs;
	}
	
	
	/**
	 * Override WooCommerce single-product/rating template user want to always show the rating
	 */
	public static function show_product_rating(){
		global $product;

		if ( ! wc_review_ratings_enabled() ) {
			return;
		}

		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();

		if ( $rating_count >= 0 ) : ?>

            <div class="woocommerce-product-rating">
				<?php echo $rating_count > 0 ? wc_get_rating_html( $average, $rating_count ) : self::loop_rating_html('',"0",''); ?>
				<?php if ( comments_open() ) : ?>
                    <a href="#reviews" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'themify' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a>
				<?php endif ?>
            </div>

		<?php
		endif;
	}
	
	
	public static function loop_product_title(){
		global $themify;
		if($themify->hide_title!=='yes'){
			themify_post_title(array('unlink'=>false,'tag'=>'h3','class'=>esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) )));
		}
	}
	
	
	
	public static function loop_add_to_cart($args,$product){
		$type=$product->get_type();
		if($type==='variable' || $type==='grouped'){
			$args['class'].=' variable-link themify-lightbox';
		}
		return $args;
	}
	
	public static function loop_rating_html( $rating_html, $rating, $count){
		if('0' == $rating){
			/* translators: %s: rating */
			$label = __( 'Rated 0 out of 5', 'themify' );
			$rating_html  = '<div class="star-rating" role="img" aria-label="' . $label . '">' . wc_get_star_rating_html( $rating, $count ) . '</div>';
		}
		return $rating_html;
	}
	

	/**
	 * Set number of products shown in shop
	 * @return int Number of products based on user choice
	 */
	public static function products_per_page($limit){
		return themify_get('setting-shop_products_per_page',$limit,true);
	}
	
	
	/**
	 * Outputs product short description or full content depending on the setting.
	 */
	public static function loop_description(){
		global $themify, $ThemifyBuilder;

		if ( $themify->display_content==='none' || wc_get_loop_prop( 'is_shortcode' ) || ! empty( $ThemifyBuilder->in_the_loop ) ) {
			return;
		}
		?>
		<div class="product-description">
			<?php $themify->display_content==='excerpt'?the_excerpt():the_content();?>
		</div>
		<?php
	}
	
	public static function description_wrap($desc){
		return '<div class="product-description">' . $desc . '</div><!-- /.product-description -->';
	}
	
	/**
	* Single Product Slider/Zoom
	* @param void
	* @return void
	*/
	private static function singleProductSlider(){
		
			$galType=themify_get_gallery_type();
			if ( $galType === 'zoom' || $galType==='disable-zoom' ) {
				// Remove default gallery
				remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
				remove_theme_support( 'wc-product-gallery-zoom' );
				remove_theme_support( 'wc-product-gallery-lightbox' );
				remove_theme_support( 'wc-product-gallery-slider' );
				add_filter('woocommerce_single_product_image_thumbnail_html','__return_empty_string',100);
				add_action( 'woocommerce_product_thumbnails',array(__CLASS__,'swipe_slider'), 20 );
				
				// Dynamic Gallery Plugin
				if ( is_plugin_active( 'woocommerce-dynamic-gallery/wc_dynamic_gallery_woocommerce.php' ) ) {
					remove_action( 'themify_single_product_image', 'woocommerce_show_product_images', 20);
					remove_action( 'themify_single_product_image', 'woocommerce_show_product_thumbnails', 20);
				}
				if( is_plugin_active( 'woocommerce-additional-variation-images/woocommerce-additional-variation-images.php' ) ) {
					add_filter( 'wc_additional_variation_images_custom_swap', '__return_true' );
				}
			}
	}
	

	/**
	 * Add swipe slider 
	 */
	public static function swipe_slider() {
		get_template_part('woocommerce/single-product/swiper');
	}
		

	/**
	 * Single post lightbox
	 **/
	public static function product_lightbox() {
		// locate template single page in lightbox
		if (!empty( $_GET['post_in_lightbox'] )  && is_product() ) {
			add_filter('woocommerce_product_tabs', '__return_false',100);
			remove_action( 'woocommerce_single_product_summary',  'woocommerce_template_single_title',5);
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
			
			add_action( 'woocommerce_single_product_summary', array(__CLASS__,'lightbox_product_title'),5);
			$return_template = locate_template( 'woocommerce/single-product/lightbox.php' );
			wc_get_template( 'single-product/add-to-cart/variation.php' );

			if ( have_posts() ) {
				include( $return_template );
                if(!empty( $_GET['load_wc'] )){
                    WC_Frontend_Scripts::load_scripts();
                    wp_enqueue_script( 'wc-add-to-cart-variation' );//load tmpl files
                    wp_enqueue_script( 'wc-single-product' );
                    wp_enqueue_script('jquery-blockui');
                    WC_Frontend_Scripts::localize_printed_scripts();
                    global $wp_scripts;
                    $js=array('js-cookie','wc-add-to-cart','wc-add-to-cart-variation','wc-cart-fragments','woocommerce','wc_additional_variation_images_script','wc-single-product');
                    $arr=array();
                    $scripts='';
                    $wc_ver=WC()->version;
                    foreach ($js as $v) {
                        if (isset($wp_scripts->registered[$v]) && wp_script_is($v)) {
                            if(!empty($wp_scripts->registered[$v]->extra['data'])){
                                $scripts.=$wp_scripts->registered[$v]->extra['data'];
                            }
                            $arr[$v]=$wp_scripts->registered[$v]->src;
                            if($wc_ver!==$wp_scripts->registered[$v]->ver){
                                $arr[$v].='?ver='.$wp_scripts->registered[$v]->ver;
                            }
                        }
                    }
                    echo '<script>',$scripts,';themify_vars["wc_js"]=',json_encode($arr),'</script>';
                }
				die();
			} 
			else {
				global $wp_query;
				$wp_query->is_404 = true;
			}
		}
	}
	
	public static function lightbox_product_title(){
		themify_post_title(array('tag'=>'h1','class'=>'product_title entry-title'));
	}
	
	
	/**
	* Remove (unnecessary) success message after a product was added to cart through theme's AJAX method.
	* @since 1.5.5
	* @param string/int $message
	* @return string
	*/
	public static function add_to_cart_message( $message ){
		if ( isset( $_REQUEST['wc-ajax'] ) && 'theme_add_to_cart' === $_REQUEST['wc-ajax'] ) {
			//Adding cart ajax on single product page
			add_action( 'wc_ajax_theme_add_to_cart',array(__CLASS__,'ajax_add_to_cart_refresh') );
			add_action( 'wc_ajax_nopriv_theme_add_to_cart', array(__CLASS__,'ajax_add_to_cart_refresh') );//when Redirect to the cart page isn`t checked
			add_filter('woocommerce_add_to_cart_redirect',array(__CLASS__,'ajax_add_to_cart_refresh'),1,100);//when Redirect to the cart page is checked
			
			if(current_filter()!=='woocommerce_add_to_cart_quantity'){
			    $message = '';
			}
		}
		return $message;
		
	}
		/**
	 * Add to cart ajax on single product page
	 * @return json
	 */
	public static function ajax_add_to_cart_refresh() {
	    $errors=wc_get_notices('error');	
	    wc_clear_notices();    
	    if(!empty($errors)){
		$data=array();
		foreach($errors as $e){
		    $data[]=$e['notice'];
		}
		wp_send_json_error($data);
	    }
	    WC_AJAX::get_refreshed_fragments();
	}
	
		
		
	/**
	 * Add cart total and shopdock cart to the WC Fragments
	 * @param array $fragments 
	 * @return array
	 */
	public static function add_to_cart_fragments( $fragments ) {
		// cart list
		ob_start();
		get_template_part( 'includes/shopdock' );
		$fragments['.shopdock'] =  ob_get_clean();
		$total = WC()->cart->get_cart_contents_count();
		$cl= $total>0?'icon-menu-count':'icon-menu-count cart_empty';
		$fragments['#cart-icon-count .icon-menu-count, #cart-link-mobile .icon-menu-count'] = '<span class="'.$cl.'">' . $total. '</span>';
		return $fragments;
	}
	
	
		
	/**
	 * Specific for infinite scroll themes
	 */
	public static function pagination() {
		if ( wc_get_loop_prop( 'is_shortcode' ) ) {
            $name=wc_get_loop_prop( 'name' );
            if(in_array($name,array('products','recent_products','sale_products','best_selling_products','top_rated_products','featured_products'))){
                woocommerce_pagination();
            }
		} else {
			get_template_part( 'includes/pagination');
		}
	}
	
	/**
	 * Handler of Ajax Product Slider
	 */
	public static function loop_slider(){
		if(!empty($_POST['slider'])){
			$product=wc_get_product($_POST['slider']);
			if(!empty($product)){
				$attachment_ids = $product->get_gallery_image_ids();
				$result = array('big'=>array(),'thumbs'=>array());
				$width = $height = '';
				if(!empty($_POST['width']) && !empty($_POST['height'])){
					if(is_numeric($_POST['width'])){
						$width = intval($_POST['width']);
					}
					if(is_numeric($_POST['height'])){
						$height = intval($_POST['height']);
					}
				}
				else{
					list($width,$height)=self::getLoopImageSize();
				}

				$is_disabled = themify_is_image_script_disabled();
				$base_size = themify_get( 'setting-img_php_base_size', 'large', true );
                $main_img = $product->get_image_id();
				if(!empty($main_img)){
                    $src = wp_get_attachment_image_url( $main_img, $base_size );
                    if ( $is_disabled ) {
                        $result['big'][] = wp_get_attachment_image_url( $main_img, self::$loopImageSize );
                        $result['thumbs'][] = wp_get_attachment_image_url( $main_img, self::$thumbImageSize );
                    } else {
                        $result['big'][] = themify_get_image(array('urlonly'=>1,'w'=>$width,'h'=>$height,'src'=>$src));
                        $result['thumbs'][]=themify_get_image(array('urlonly'=>1,'w'=>28,'h'=>28,'src'=>$src));
                    }
                }
				foreach ( $attachment_ids as $attachment_id ) {
					$src = wp_get_attachment_image_url( $attachment_id, $base_size );
					if ( ! $src ) {
						continue;
					}
					if ( $is_disabled ) {
						$result['big'][] = wp_get_attachment_image_url( $attachment_id, self::$loopImageSize );
						$result['thumbs'][] = wp_get_attachment_image_url( $attachment_id, self::$thumbImageSize );
					} else {
						$result['big'][] = themify_get_image(array('urlonly'=>1,'w'=>$width,'h'=>$height,'src'=>$src));
						$result['thumbs'][]=themify_get_image(array('urlonly'=>1,'w'=>28,'h'=>28,'src'=>$src));
					}
				}
				echo wp_json_encode($result);
			}
		}
		wp_die();
	}
	
	
	public static function getSingleImageSize(){
		$width=themify_get_both('image_width','setting-default_product_single_image_post_width',false);
		$height=themify_get_both('image_height','setting-default_product_single_image_post_height',false);
		if($width===false && $height===false){
			$size = wc_get_image_size( self::$singleImageSize );
			$width= $size['width'];
			$height= $size['height'];
		}
		return array($width,$height);
	}
	
	
	public static function getLoopImageSize(){
		$width=themify_get_both('product_image_width','setting-default_product_index_image_post_width',false);
		$height=themify_get_both('product_image_height', 'setting-default_product_index_image_post_height',false);
		if($width===false && $height===false){
			$size = wc_get_image_size(self::$loopImageSize );
			$width= $size['width'];
			$height= $size['height'];
		}
		return array($width,$height);
	}
	
	
	public static function variation_image_size($data ){
		if( ! empty( $data[ 'image' ] ) ) {
			list($data[ 'image' ][ 'src_w' ],$data[ 'image' ][ 'src_h' ])=self::getSingleImageSize();
			$data[ 'image' ][ 'src' ]=themify_get_image( array('src'=>$data[ 'image' ][ 'src' ],'w'=>$data[ 'image' ][ 'src_w' ],'h'=>$data[ 'image' ][ 'src_h' ],'urlonly'=>true,'image_size'=>self::$singleImageSize) );
			
		}
		return $data;
	}
	
	
	// Query Products
	public static function product_query($args){
		remove_filter('themify_query_posts_page_args',array(__CLASS__,'product_query'));
		$type=themify_get('product_query_type', 'all' );
		if($args['orderby']==='price' || $args['orderby']==='price-desc' || $args['orderby']==='sales' || $args['orderby']==='popularity' || $args['orderby']==='rating'){
			/* use the order set by WC sorting bar if available, overriding options configured in Query Products */
			$orderby = isset( $_GET['orderby'] ) ? '' : $args['orderby'];
			$order = isset( $_GET['orderby'] ) ? '' : $args['order'];
			$args = array_merge( $args, WC()->query->get_catalog_ordering_args( $orderby, $order ) );
			add_action('themify_query_after_posts_page_args',array(__CLASS__,'reset_query_order'));
		}
		//Query modifiers
		$args['meta_query']=array();
		if( $type === 'onsale' ) {
			$args['post__in'] = wc_get_product_ids_on_sale();
			$args['post__in'][] = 0;
		} elseif( $type === 'featured' ) {
			$args['meta_query'][] = array(
				'key'	=> '_featured',
				'value' => 'yes'
			);
		} elseif( $type === 'free' ) {
			$args['meta_query'][] = array(
				'key'		=> '_price',
				'value'		=> 0,
				'compare'	=> '=',
				'type'		=> 'DECIMAL'
			);
		}
		return $args;
	}
	
	public static function reset_query_order(){
		remove_action('themify_query_after_posts_page_args',array(__CLASS__,'reset_query_order'));
		WC()->query->remove_ordering_args();
	}

	public static function hide_shop_products( $q){
		if ( themify_is_shop() ) {
		    query_posts( array( 'post__in' => array( 0 ) ) );
		    remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
		}
	}
	
	public static function load_wc_styles($template_name, $template_path, $located, $args){
		if($template_name==='loop/orderby.php'){
			Themify_Enqueue_Assets::loadThemeWCStyleModule( 'orderby' );
		}
		elseif($template_name==='loop/result-count.php'){
			Themify_Enqueue_Assets::loadThemeWCStyleModule( 'result-count' );
		}
		elseif($template_name==='single-product/tabs/tabs.php'){
		    Themify_Enqueue_Assets::loadThemeWCStyleModule( 'tabs' );
		}
		elseif($template_name==='loop/pagination.php'){
			self::load_pagination_styles();
		}
		elseif(($template_name==='single-product/related.php' && !empty($args['related_products'])) || ($template_name==='single-product/up-sells.php' && !empty($args['upsells']))){
			Themify_Enqueue_Assets::loadThemeWCStyleModule('related');	
			Themify_Enqueue_Assets::loadThemeStyleModule('builder/fancy-heading');
			$width = themify_get( 'setting-product_related_image_width',false,true );
			$height = themify_get( 'setting-product_related_image_height',false,true );
			if($height===false && $width===false){
				list($width,$height)=self::getLoopImageSize();
			}
			global $themify;
			self::$themify_save = clone $themify;
			$themify->width=$width;
			$themify->height=$height;
			$themify->display_content=themify_get_both('product_archive_show_short','setting-product_archive_show_short','none');
			add_action( 'woocommerce_after_template_part',array(__CLASS__,'reset_themify_property'),10,5);

		}
	}
	
	public static function reset_themify_property($template_name, $template_path, $located, $args){
		if(($template_name==='single-product/related.php' && !empty($args['related_products'])) || ($template_name==='single-product/up-sells.php' && !empty($args['upsells']))){
			remove_action( 'woocommerce_after_template_part',array(__CLASS__,'reset_themify_property'),10,5);
			if(self::$themify_save!==null){
				global $themify;
				$themify = clone self::$themify_save;
				self::$themify_save=null;
			}
		}
	}
	
	public static function load_pagination_styles($args=array()){
	    remove_filter('woocommerce_pagination_args', array(__CLASS__,'load_pagination_styles'));
	    remove_filter('woocommerce_comment_pagination_args', array(__CLASS__,'load_pagination_styles'));
	    remove_action('woocommerce_before_account_orders_pagination', array(__CLASS__,'load_pagination_styles'));
	    Themify_Enqueue_Assets::loadThemeWCStyleModule( 'pagination' );
	    return $args;
	}
	
	
	public static function before_loop(){
		add_action('tf_wc_loop_end',array(__CLASS__,'after_loop'));
		add_filter( 'woocommerce_product_get_image', array(__CLASS__,'loop_image'),100,6);
	}
	
	public static function after_loop(){
	    remove_action('tf_wc_loop_end',array(__CLASS__,'after_loop'));
	    remove_filter( 'woocommerce_product_get_image', array(__CLASS__,'loop_image'),100,6);
	}

	/**
	 * Hooked to "body_class"
	 *
	 * @return array
	 */
	public static function body_class( $classes ) {
		$classes[] = 'woo_qty_btn';
		return $classes;
	}
}
add_action('woocommerce_init', array('Themify_WC','before_init') );
