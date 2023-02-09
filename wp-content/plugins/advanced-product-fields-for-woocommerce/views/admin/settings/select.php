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
        <select rv-default="<?php echo $model['is_field_setting'] ? 'field' : 'settings'; ?>.<?php echo $model['id']; ?>" data-default="<?php echo isset($model['default']) ? esc_attr($model['default']) : ''; ?>" rv-on-change="<?php echo $model['id'] === 'type' ? 'onChangeType' : 'onChange'; ?>" rv-value="<?php echo $model['is_field_setting'] ? 'field' : 'settings'; ?>.<?php echo $model['id']; ?>">
            <?php
                foreach($model['options'] as $value => $label) {
                    /*$selected = false;
                    if(isset($model['default']) && $model['default'] === $value)
                        $selected = true;
*/
                    echo '<option value="'.$value.'">'.$label.'</option>';
                }
            ?>
        </select>
    </div>
</div>