<?php
/**
 * Template for search form.
 * @package themify
 * @since 1.0.0
 */
global $tf_isWidget;
?>
<div class="tf_search_form <?php echo !isset($tf_isWidget)?'tf_search_overlay':'tf_s_dropdown'?>" data-lazy="1"<?php if(isset($tf_isWidget) && $tf_isWidget===false):?> data-ajax=""<?php endif;?>>
<form role="search" method="get" id="searchform" class="tf_rel<?php if(!isset($tf_isWidget)):?> tf_hide<?php endif;?>" action="<?php echo home_url('/'); ?>">

	<div class="tf_icon_wrap icon-search"><?php echo themify_get_icon(themify_get('setting-ic-search','ti-search',true),null,false,false,array('aria-label'=>__('Search','themify')))?></div>

	<input type="text" name="s" id="s" title="<?php _e( 'Search', 'themify' ); ?>" placeholder="<?php _e( 'Search', 'themify' ); ?>" value="<?php echo get_search_query(); ?>" />

	<?php if(themify_is_woocommerce_active() && 'product' === themify_get( 'setting-search_post_type','all',true )): ?>
        <input type="hidden" name="post_type" value="product">
	<?php endif; ?>

</form>
</div>
<?php $tf_isWidget=null;?>