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
        <input
            <?php if($model['id'] === 'label') echo 'rv-on-change="field.updateKey"'; ?>
            rv-on-keyup="onChange"
            rv-value="<?php echo $model['is_field_setting'] ?  'field' : 'settings'; ?>.<?php echo $model['id']; ?>"
            type="email"
        />

    </div>
</div>