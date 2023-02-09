<?php
/* @var $model array */
$pro = isset($model['pro']) && $model['pro'] === true;
?>

<div class="wapf-field__setting" data-pro="<?php echo $pro ? 'true':'false'; ?>" data-setting="<?php echo $model['id']; ?>">
    <div class="wapf-setting__label">
        <label><?php _e($model['label'],'advanced-product-fields-for-woocommerce');?> <?php if($pro) _e('(Pro only)','advanced-product-fields-for-woocommerce'); ?></label>
        <?php if(isset($model['description'])) { ?>
            <p class="wapf-description">
                <?php _e($model['description'],'advanced-product-fields-for-woocommerce');?>
            </p>
        <?php } ?>
    </div>
    <div class="wapf-setting__input">
        <div class="wapf-toggle" rv-unique-checkbox>
            <input <?php echo $pro ? 'disabled':'';?> rv-on-change="onChange" rv-checked="<?php echo $model['is_field_setting'] ? 'field' : 'settings'; ?>.<?php echo $model['id']; ?>" type="checkbox" >
            <label class="wapf-toggle__label" for="wapf-toggle-">
                <span class="wapf-toggle__inner" data-true="<?php _e('Yes','advanced-product-fields-for-woocommerce'); ?>" data-false="<?php _e('No','advanced-product-fields-for-woocommerce'); ?>"></span>
                <span class="wapf-toggle__switch"></span>
            </label>
        </div>

    </div>
</div>