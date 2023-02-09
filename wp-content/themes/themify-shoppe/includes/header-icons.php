<div class="header-icons">
    <div class="top-icon-wrap">
	<?php
	if ($show_menu_navigation === true) {
		add_filter( 'themify_menu_icon', 'themify_theme_menu_icon', 10, 4 );
	    themify_menu_nav(array(
			'theme_location' => 'icon-menu',
			'fallback_cb' => '',
			'container' => '',
			'menu_id' => 'icon-menu',
			'menu_class' => 'icon-menu'
	    ));
		remove_filter( 'themify_menu_icon', 'themify_theme_menu_icon' );
	}
	?>

	<?php if ( themify_is_woocommerce_active() || themify_theme_is_dark_mode() === 'user' ) : ?>
		<ul class="icon-menu">

			<?php if ( themify_theme_is_dark_mode() === 'user' ) : ?>
			<li class="theme_darkmode">
			    <a class="tf_darkmode_toggle" href="#" aria-hidden="true">
				<em class="icon-moon"><?php echo themify_get_icon( 'fas moon', 'fa' ); ?></em>
				<span class="tooltip"><?php _e('Darkmode', 'themify') ?></span>
				</a>
			</li>
			<?php endif; ?>

			<?php if ( themify_is_woocommerce_active() ) : ?>
				<?php if (!themify_check('setting-exclude_wishlist', true) && Themify_Wishlist::is_enabled() && themify_theme_show_area('wishlist')) : ?>
				<li class="wishlist">
					<a class="tools_button" href="<?php echo Themify_Wishlist::get_wishlist_page(); ?>">
					<em class="icon-heart"><?php echo themify_get_icon(themify_get('setting-ic-wishlist','ti-heart',true),false,false,false,array('aria-label'=>__('Whishlist','themify'))); ?></em>
					<span class="icon-menu-count wishlist_empty"></span>
					<span class="tooltip"><?php _e('Wishlist', 'themify') ?></span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ($show_cart === true) : ?>
				<?php $cart_is_dropdown = $cart_style === 'dropdown'; ?>
				<li id="cart-icon-count" class="<?php echo $total > 0 ? 'cart' : 'cart empty-cart'; ?>">
					<a <?php if ($cart_is_dropdown === false && $cart_style !== 'link_to_cart'): ?>id="cart-link"<?php endif; ?> href="<?php echo $cart_is_dropdown === true?'javascript:;':($cart_style === 'link_to_cart' ? wc_get_cart_url() : '#slide-cart'); ?>">
					<em class="icon-shopping-cart"><?php echo themify_get_icon(themify_get('setting-ic-cart','ti-shopping-cart',true),false,false,false,array('aria-label'=>__('Shopping Cart','themify'))); ?></em>
					<span class="icon-menu-count<?php if ($total <= 0): ?> cart_empty<?php endif; ?>"><?php echo $total; ?></span>
					<span class="tooltip"><?php _e('Cart', 'themify') ?></span>
					</a>
					<?php
					if ($cart_is_dropdown === true) {
					themify_get_ecommerce_template('includes/shopdock');
					}
					?>

				</li>
				<?php endif; ?>
			<?php endif; ?>

	    </ul><!-- .icon-menu -->
	<?php endif; ?>

    </div>
    <?php if (themify_theme_show_area('search_button')) : ?>
        <a data-lazy="1" class="search-button tf_search_icon tf_box" href="#"><?php echo themify_get_icon(themify_get('setting-ic-search','ti-search',true) ,false,false,false,array('aria-label'=>__('Search','themify'))); ?><span class="screen-reader-text"><?php _e('Search','themify'); ?></span></a>
        <!-- /search-button -->
    <?php endif; ?>
</div>
