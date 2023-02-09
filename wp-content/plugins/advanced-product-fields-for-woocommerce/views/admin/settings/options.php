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
        <div style="width: 100%;" rv-show="field.choices | isNotEmpty">
            <div class="wapf-options__header">
                <div class="wapf-option__sort"></div>
                <div class="wapf-option__flex"><?php _e('Option label','advanced-product-fields-for-woocommerce'); ?></div>
                <!--<td style="font-weight:500;text-align: left;"><?php _e('Unique key','advanced-product-fields-for-woocommerce'); ?></td>-->
                <?php if(isset($model['show_pricing_options']) && $model['show_pricing_options']) { ?>
                    <div class="wapf-option__flex"><?php _e('Adjust pricing','advanced-product-fields-for-woocommerce'); ?></div>
                    <div class="wapf-option__flex"><?php _e('Pricing amount','advanced-product-fields-for-woocommerce'); ?></div>
                <?php } ?>
                <div class="wapf-option__selected"><?php _e('Selected', 'advanced-product-fields-for-woocommerce'); ?></div>
                <div  class="wapf-option__delete"></div>
            </div>
            <div rv-sortable-options="field.choices" class="wapf-options__body">
                <div class="wapf-option" rv-each-choice="field.choices" rv-data-option-slug="choice.slug">
                    <div class="wapf-option__sort"><span rv-sortable-option class="wapf-option-sort">☰</span></div>
                    <div class="wapf-option__flex"><input class="choice-label" rv-on-keyup="onChange" type="text" rv-value="choice.label"/></div>
                    <?php if(isset($model['show_pricing_options']) && $model['show_pricing_options']) { ?>
                        <div class="wapf-option__flex">
                            <select rv-on-change="onChange" rv-value="choice.pricing_type">
                                <option value="none"><?php _e('No price change','advanced-product-fields-for-woocommerce'); ?></option>
                                <?php
                                foreach(\SW_WAPF\Includes\Classes\Fields::get_pricing_options() as $k => $v) {
                                    echo '<option ' . ($v['pro'] === true ? 'disabled' : '') . ' value="'.$k.'">'.$v['label'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="wapf-option__flex">
                            <input rv-on-change="onChange" type="number" min="0" step="any" rv-value="choice.pricing_amount" />
                        </div>
                    <?php } ?>
                    <div class="wapf-option__selected" style="text-align: right;"><input data-multi-option="<?php echo isset($model['multi_option']) ? $model['multi_option'] : '0' ;?>" rv-on-change="field.checkSelected" rv-checked="choice.selected" type="checkbox" /></div>
                    <div class="wapf-option__delete"><a href="#" rv-on-click="field.deleteChoice" class="wapf-button--tiny-rounded">&times;</a></div>
                </div>
            </div>
        </div>

        <div style="padding-top:12px;text-align: right;width: 100%;">
            <a href="#" rv-on-click="field.addChoice" class="button button-small"><?php _e('Add option','advanced-product-fields-for-woocommerce'); ?></a>
        </div>
        <div style="text-align: right;width: 100%">
	        <?php \SW_WAPF\Includes\Classes\Html::help_modal(__("<p>please note the pricing option in the free version has some limitations:</p><ul style='list-style: disc;margin-left:30px;'><li>Add-on pricing is not quantity-based. This means if the user changes the product quantity, the price does not multiply, but stays the same.</li><li>You can only use the \"flat fee\" option.</li></ul><p><a target=\"_blank\" href=\"https://www.studiowombat.com/knowledge-base/all-pricing-options-explained/?utm_source=apffree&utm_medium=plugin&utm_campaign=info\">See which other pricing options</a> are available in the premium verion.</p>",'advanced-product-fields-for-woocommerce'), __('Important note about pricing','advanced-product-fields-for-woocommerce'), __('Important note about pricing','advanced-product-fields-for-woocommerce')); ?>
        </div>
    </div>
</div>