<?php /* @var $model array */ ?>

<div class="wapf-field__setting" data-setting="<?php echo $model['id']; ?>">
    <div class="wapf-setting__label">
        <label><?php _e($model['label'],'advanced-product-fields-for-woocommerce');?></label>
        <p class="wapf-description">
            <?php _e($model['description'],'advanced-product-fields-for-woocommerce');?>
        </p>
    </div>
    <div class="wapf-setting__input">

        <div style="width:100%;"  class="wapf-field__conditionals">

            <div class="wapf-field__conditionals__container">
                <div rv-if="fields | hasLessThan 2" class="wapf-lighter">
                    <?php _e('You need atleast 2 fields to create conditional rules. Add another field first.','advanced-product-fields-for-woocommerce');?>
                </div>
                <div rv-if="fields | hasMoreThan 1">
                    <div rv-each-conditional="field.conditionals">
                        <table style="padding-bottom:10px;width:100%;" class="wapf-field__conditional">
                            <tr rv-each-rule="conditional.rules">
                                <td>
                                    <select rv-on-change="onConditionalFieldChange" rv-value="rule.field">
                                        <option  rv-each-fieldobj="fieldsForSelect | remove 'value' field.id" rv-value="fieldobj.value">{fieldobj.label}</option>
                                    </select>
                                </td>
                                <td>
                                    <select rv-on-change="onChange" rv-value="rule.condition">
                                        <option rv-disabled="condition.pro" rv-each-condition="availableConditions | filterConditions rule.field fields" rv-value="condition.value">{ condition.label }</option>
                                    </select>
                                </td>
                                <td>
                                    <input rv-if="rule.condition | conditionNeedsValue availableConditions 'text' fields rule.field" rv-on-keyup="onChange" type="text" rv-value="rule.value" />
                                    <input rv-if="rule.condition | conditionNeedsValue availableConditions 'number' fields rule.field" step="any" rv-on-change="onChange" rv-on-keyup="onChange" type="number" rv-value="rule.value" />
                                    <select rv-if="rule.condition | conditionNeedsValue availableConditions 'options' fields rule.field" rv-on-change="onChange" rv-value="rule.value">
                                        <option rv-each-v="fields | getChoices rule.field" rv-value="v.slug">{v.label}</option>
                                    </select>
                                </td>
                                <td style="width: 125px;">
                                    <a href="#" rv-on-click="deleteRule" class="button button-small">- <?php _e('Delete','advanced-product-fields-for-woocommerce'); ?></a>
                                    <a href="#" rv-show="conditional.rules | isLastIteration $index " rv-on-click="addRule" class="button button-small">+ <?php _e('And','advanced-product-fields-for-woocommerce'); ?></a>
                                </td>
                            </tr>
                        </table>
                        <div rv-if="$index | lt field.conditionals"><b><?php _e('Or','advanced-product-fields-for-woocommerce');?></b></div>
                    </div>
                    <div style="padding-top: 5px;">
                        <a href="#" rv-on-click="addConditional" class="button button-small"><?php _e('Add new rule group','advanced-product-fields-for-woocommerce'); ?></a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>