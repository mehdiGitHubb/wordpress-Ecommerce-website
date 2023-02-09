<?php /* @var $model array */ ?>

<div class="wapf-field__setting" data-setting="<?php echo $model['id']; ?>">
    <div class="wapf-setting__label">
        <label><?php _e($model['label'],'advanced-product-fields-for-woocommerce');?></label>
        <?php if(isset($model['description'])) { ?>
            <p class="wapf-description">
                <?php _e($model['description'],'advanced-product-fields-for-woocommerce');?>
            </p>
        <?php } ?>
    </div>
    <div class="wapf-setting__input">
        <div style="width:48%;">
            <div class="wapf-input-prepend"><?php _e('Width','advanced-product-fields-for-woocommerce'); ?></div>
            <div class="wapf-input-append">%</div>
            <div class="wapf-input-with-prepend-append">
                <input
                    rv-on-keyup="onChange" min="0" max="100"
                    rv-value="<?php echo $model['is_field_setting'] ? 'field' : 'settings'; ?>.width"
                    type="number"
                />
            </div>
        </div>
        <div style="width:48%; padding-left:2%;">
            <div class="wapf-input-prepend"><?php _e('Class','advanced-product-fields-for-woocommerce'); ?></div>
            <div class="wapf-input-with-prepend-append">
                <input
                    rv-on-keyup="onChange"
                    rv-value="<?php echo $model['is_field_setting'] ? 'field' : 'settings'; ?>.class"
                    type="text"
                />
            </div>
        </div>
    </div>
</div>