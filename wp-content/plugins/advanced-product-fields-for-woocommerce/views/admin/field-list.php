<?php
/* @var $model array */
use SW_WAPF\Includes\Classes\Helper;
?>
<div rv-controller="FieldListCtrl"
     data-raw-fields="<?php echo Helper::thing_to_html_attribute_string($model['fields']); ?>"
     data-field-conditions="<?php echo Helper::thing_to_html_attribute_string($model['condition_options']); ?>"
>

    <input type="hidden" name="wapf-fields" rv-value="fieldsJson" />

    <div class="wapf-field-list">

        <div class="wapf-field-list__body">
            <span rv-show="fields | isEmpty" class="wapf-list--empty" style="display: <?php echo empty($model['fields']) ? 'block' : 'none';?>;">
                <a href="#" class="button button-primary button-large" rv-on-click="addField">Add your 1st Field</a>
            </span>

            <div rv-each-field="fields" rv-cloak>
                <?php \SW_WAPF\Includes\Classes\Html::admin_field([], $model['type']) ?>
            </div>

        </div>

        <div rv-cloak>
            <div rv-show="fields | isNotEmpty" class="wapf-field-list__footer">
                <a href="#" class="button button-primary button-large" rv-on-click="addField">Add a Field</a>
            </div>
        </div>

    </div>

</div>