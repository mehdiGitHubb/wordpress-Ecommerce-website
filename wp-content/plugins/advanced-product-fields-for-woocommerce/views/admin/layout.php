<?php
/* @var $model array */
use SW_WAPF\Includes\Classes\Helper;
use SW_WAPF\Includes\Classes\Html;
use SW_WAPF\Includes\Classes\Field_Groups;
?>

<div rv-controller="LayoutCtrl" data-layout-options="<?php echo Helper::thing_to_html_attribute_string($model['layout']); ?>"
>

    <input type="hidden" name="wapf-layout" rv-value="layoutJson" />

    <div class="wapf-layout-list">

        <div class="wapf-conditions-list__body">

            <?php

                Html::setting([
                    'type'              => 'select',
                    'id'                => 'labels_position',
                    'label'             => __('Label position','advanced-product-fields-for-woocommerce'),
                    'description'       => __('Where should the label be positioned in relation to the field?','advanced-product-fields-for-woocommerce'),
                    'options'           => [
                        'above'         => __('Above the field', 'advanced-product-fields-for-woocommerce'),
                        'below'         => __('Below the field', 'advanced-product-fields-for-woocommerce'),
                    ],
                    'is_field_setting'  => false
                ]);

                Html::setting([
                    'type'              => 'select',
                    'id'                => 'instructions_position',
                    'label'             => __('Instruction position','advanced-product-fields-for-woocommerce'),
                    'description'       => __('Where should the instructions be positioned?','advanced-product-fields-for-woocommerce'),
                    'options'           => [
                        'label'         => __('Below the label', 'advanced-product-fields-for-woocommerce'),
                        'field'         => __('Below the field', 'advanced-product-fields-for-woocommerce'),
                    ],
                    'is_field_setting'  => false
                ]);

                Html::setting([
                    'type'              => 'true-false',
                    'id'                => 'mark_required',
                    'label'             => __('Mark required fields','advanced-product-fields-for-woocommerce'),
                    'description'       => __('Add a *-symbol next to required fields.','advanced-product-fields-for-woocommerce'),
                    'is_field_setting'  => false
                ]);

                Html::setting([
                    'type'              => 'true-false',
                    'id'                => 'product_image',
                    'label'             => __('Change product image','advanced-product-fields-for-woocommerce'),
                    'description'       => __('Should the product image change when options are selected?','advanced-product-fields-for-woocommerce'),
                    'is_field_setting'  => true,
                    'pro'               => true
                ]);

            ?>

        </div>

    </div>
</div>