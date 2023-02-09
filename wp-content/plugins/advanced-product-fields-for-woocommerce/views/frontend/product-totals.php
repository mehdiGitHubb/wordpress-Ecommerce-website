<?php
 /** @var string $product_type */
 /** @var string $product_price*/
 /** @var string $product_id */
 ?>
<div class="wapf-product-totals" data-product-type="<?php echo $product_type; ?>" data-product-price="<?php echo $product_price; ?>" data-product-id="<?php echo $product_id; ?>">
    <div class="wapf--inner">
        <div>
            <span><?php _e('Product total','advanced-product-fields-for-woocommerce'); ?></span>
            <span class="wapf-product-total price amount"></span>
        </div>
        <div>
            <span><?php _e('Options total','advanced-product-fields-for-woocommerce'); ?></span>
            <span class="wapf-options-total price amount"></span>
        </div>
        <div>
            <span><?php _e('Grand total','advanced-product-fields-for-woocommerce'); ?></span>
            <span class="wapf-grand-total price amount"></span>
        </div>
    </div>
</div>