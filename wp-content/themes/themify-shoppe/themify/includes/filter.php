<?php
/**
 * Partial template that displays an entry filter.
 *
 * Created by themify
 * @since 1.0.0
 */
$cats=is_array( $args['query_category'] ) ? implode( ',',$args['query_category'] ) : $args['query_category'];
$cat_args="hide_title_if_empty=true&show_option_none=0&echo=0&hierarchical=0&show_count=0&title_li=&include=$cats";
$taxonomy=empty( $args['query_taxonomy'] ) ? 'category' : $args['query_taxonomy'];
$cat_args.='&taxonomy='.$taxonomy;
$cat_filter = !empty($args['ajax_filter_include']) ? 'include' : 'exclude';
if(!empty( $args['ajax_filter_'.$cat_filter] ) ){
    $value=array_map( 'intval',explode( ',',$args['ajax_filter_'.$cat_filter] ) );
    if(!empty( $value )){
        $cat_args.='&'.$cat_filter.'='.implode( ',',$value );
    }
}
if(is_category() && themify_check( 'setting-filter-category',true )){
	$category=get_queried_object();
	if(!empty( $category )){
		$cat_args.='&child_of='.$category->term_id;
	}
}
$list_categories=wp_list_categories( $cat_args );
if(!empty( $list_categories )){
	$attrs='';
	if(isset( $args['hash_tag'] )){
		$list_categories=preg_replace( '/cat-item-(\d+)"/','$0 data-id="'.$args['el_id'].':$1"',$list_categories );
		$attrs.=' data-hash="'.esc_attr( $args['el_id'] ).'"';
	}
	if(!has_filter( 'post_class','themify_post_filter_class' )){
		//add category id class in post loop for masonry filter
		add_filter( 'post_class','themify_post_filter_class',10,3 );
	}
	Themify_Enqueue_Assets::preFetchMasonry();
	Themify_Enqueue_Assets::add_css( 'tf_post_filter',Themify_Enqueue_Assets::$THEMIFY_CSS_MODULES_URI.'post-filter.css',null,THEMIFY_VERSION );
	if(themify_is_themify_theme() && Themify_Enqueue_Assets::has_theme_support_css( 'post-filter' )){
		Themify_Enqueue_Assets::loadThemeStyleModule( 'post-filter' );
	}
	if(isset( $args['ajax_filter'] )){
		$attrs.=' data-id="'.esc_attr( $args['ajax_filter_id'] ).'" data-el="'.esc_attr( $args['el_id'] ).'" data-limit="'.esc_attr( $args['ajax_filter_limit'] ).'" data-ajax="1"';
		if(isset( $args['ajax_sort'] )){
			$attrs.=' data-sort="true"';
		}
		$attrs.=' data-taxonomy="'.$taxonomy.'"';
	}
	?>
    <ul class="post-filter tf_textc tf_opacity"<?php echo $attrs ?>>
		<?php echo $list_categories ?>
		<?php if(isset( $args['ajax_sort'] )): ?>
            <li data-init="1" data-p="2" class="cat-item cat-item-all active"><?php _e( 'All','themify' ) ?></li>
            <li class="tf_ajax_sort tf_rel">
                <a href="#"
                   class="tf_ajax_sort_icon"><?php echo themify_get_icon( 'menu-alt','ti',false,false,array('aria-label'=>__( 'sort','themify' )) ); ?></a>
                <div class="tf_ajax_sort_dropdown tf_abs tf_hide tf_box">
                    <div class="tf_ajax_sort_title">
                        <span><?php _e( 'Sort by:','themify' ); ?></span>
                        <div>
                            <span class="tf_ajax_sort_order tf_inline_b tf_vmiddle<?php echo 'asc'===$args['ajax_sort_order'] ? ' active' : ''; ?>"
                                  data-type="asc"><?php echo themify_get_icon( 'arrow-up','ti',false,false,array('aria-label'=>__( 'sort','themify' )) ); ?></span>
                            <span class="tf_ajax_sort_order tf_inline_b tf_vmiddle<?php echo 'desc'===$args['ajax_sort_order'] ? ' active' : ''; ?>"
                                  data-type="desc"><?php echo themify_get_icon( 'arrow-down','ti',false,false,array('aria-label'=>__( 'sort','themify' )) ); ?></span>
                        </div>
                    </div>
                    <ul class="tf_ajax_sort_order_by tf_textl">
						<?php
						$orders=array(
							'date'=>__( 'Date','themify' ),
							'title'=>__( 'Title','themify' )
						);
						if(isset( $args['ajax_filter_wc'] )){
							$orders['price']=__( 'Price','themify' );
							$orders['rate']=__( 'Rating','themify' );
						}
						foreach($orders as $order=>$title):?>
                            <li data-order-by="<?php echo $order; ?>"<?php echo $order===$args['ajax_sort_order_by'] ? ' class="active"' : ''; ?>><?php echo $title; ?></li>
						<?php endforeach; ?>
                    </ul>
                </div>
            </li>
		<?php endif; ?>
    </ul>
	<?php
}
