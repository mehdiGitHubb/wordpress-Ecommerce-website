<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) :
    themify_enque_style('themify-wc-accordion-tabs', Themify_Enqueue_Assets::$THEMIFY_CSS_MODULES_URI . 'wc-accordion-tabs.css', null, THEMIFY_VERSION,'all',true);
?>

    <div class="woocommerce-tabs wc-tabs-wrapper">
        <ul class="tf_wc_accordion tf_overflow tf_rel">
            <?php foreach ( $product_tabs as $key => $product_tab ) : ?>
                <li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                    <a class="tf_wc_acc_title" href="#tab-<?php echo esc_attr( $key ); ?>" aria-controls="tab-<?php echo esc_attr( $key ); ?>" aria-expanded="false">
                        <span class="tf_wc_acc_icon"><?php echo themify_get_icon('ti-plus','ti'); ?></span>
                        <span class="tf_wc_acc_icon accordion-active-icon tf_hide"><?php echo themify_get_icon('ti-minus','ti'); ?></span>
                        <span><?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?></span>
                    </a>
                    <div class="tf_wc_acc_content tf_hide woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> entry-content" id="tab-<?php echo esc_attr( $key ); ?>" aria-hidden="true" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
                        <?php
                        if ( isset( $product_tab['callback'] ) ) {
                            call_user_func( $product_tab['callback'], $key, $product_tab );
                        }
                        ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php do_action( 'woocommerce_product_after_tabs' ); ?>
    </div>

<?php endif; ?>
