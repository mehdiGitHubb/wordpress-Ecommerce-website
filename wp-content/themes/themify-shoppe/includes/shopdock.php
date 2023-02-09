<?php
/**
 * Template for cart
 * @package themify
 * @since 1.0.0
 */
?>

<div class="shopdock">
	<?php
	// check whether cart is not empty
	if (!empty(WC()->cart->get_cart())):
		if(themify_is_ajax()): ?>
			<div id="cart-wrap" class="tf_box">
                <?php
                if(current_user_can( 'manage_woocommerce' ) && 'yes' !== get_option( 'woocommerce_enable_ajax_add_to_cart' )){
                    echo sprintf('<div class="tf_admin_msg">%s <a href="%s">%s</a>.</div>',
                        __('WooCommerce Ajax add to cart option needs to be enabled to use this Ajax cart.','themify'),
                        admin_url('admin.php?page=wc-settings&tab=products'),
                        __('Enable it on WooCommerce settings','themify')
                    );
                }
                ?>
				<div id="cart-list" class="tf_box tf_scrollbar">
					<?php get_template_part('includes/loop-product', 'cart'); ?>
				</div>
				<!-- /cart-list -->

				<div class="cart-total-checkout-wrap">
					<p class="cart-total">
						<span class="amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
						<a id="view-cart" href="<?php echo esc_url( wc_get_cart_url() ) ?>">
							<?php _e('view cart', 'themify') ?>
						</a>
					</p>

					<?php themify_checkout_start(); //hook ?>

					<p class="checkout-button">
						<button type="submit" class="button checkout white flat" onClick="document.location.href = '<?php echo esc_url( wc_get_checkout_url() ); ?>';
									return false;"><?php _e('Checkout', 'themify') ?></button>
					</p>
					<!-- /checkout-botton -->

					<?php themify_checkout_end(); //hook ?>
				</div>

			</div>
		<?php endif;?>
		<!-- /#cart-wrap -->
	<?php elseif(themify_get_cart_style()!=='link_to_cart'):?>
	<?php
		echo '<div class="tf_textc empty-shopdock">';
		printf( __( 'Your cart is empty. Go to <a href="%s">Shop</a>.', 'themify' ), themify_get_shop_permalink() );
		echo '</div>';
	?>
	<?php endif; // cart whether is not empty?>

	<?php themify_shopdock_end(); //hook ?>
</div>
