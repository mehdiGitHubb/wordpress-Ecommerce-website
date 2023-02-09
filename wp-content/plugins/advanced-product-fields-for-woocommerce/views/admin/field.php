<?php
use SW_WAPF\Includes\Classes\Html;
/* @var $field array */
/* @var $type string */
?>

<div class="wapf-field" rv-data-field-id="field.id" rv-class-wapf--active="activeField | equalIds field">
    <div class="wapf-field__header">
        <div class="wapf-field__icon">
            <div rv-show="field.type | eq 'url'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M14.9 1.1c-1.4-1.4-3.7-1.4-5.1 0l-4.4 4.3c-1.4 1.5-1.4 3.7 0 5.2 0.1 0.1 0.3 0.2 0.4 0.3l1.5-1.5c-0.1-0.1-0.3-0.2-0.4-0.3-0.6-0.6-0.6-1.6 0-2.2l4.4-4.4c0.6-0.6 1.6-0.6 2.2 0s0.6 1.6 0 2.2l-1.3 1.3c0.4 0.8 0.5 1.7 0.4 2.5l2.3-2.3c1.5-1.4 1.5-3.7 0-5.1z" ></path><path d="M10.2 5.1l-1.5 1.5c0 0 0.3 0.2 0.4 0.3 0.6 0.6 0.6 1.6 0 2.2l-4.4 4.4c-0.6 0.6-1.6 0.6-2.2 0s-0.6-1.6 0-2.2l1.3-1.3c-0.4-0.8-0.1-1.3-0.4-2.5l-2.3 2.3c-1.4 1.4-1.4 3.7 0 5.1s3.7 1.4 5.1 0l4.4-4.4c1.4-1.4 1.4-3.7 0-5.1-0.2-0.1-0.4-0.3-0.4-0.3z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'email'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M0 3h16v2.4l-8 4-8-4z" ></path><path d="M0 14l5.5-4.8 2.5 1.4 2.5-1.4 5.5 4.8z" ></path><path d="M4.6 8.8l-4.6-2.3v6.5z" ></path><path d="M11.4 8.8l4.6-2.3v6.5z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'textarea'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M2 2h1v4h-1v-4z" ></path><path d="M1 0c-0.6 0-1 0.4-1 1v14c0 0.6 0.4 1 1 1h15v-16h-15zM13 15h-12v-14h12v14zM15 15v0h-1v-1h1v1zM15 13h-1v-10h1v10zM15 2h-1v-1h1v1z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'text'">
                <svg height="18" width="18" viewBox="0 0 16 16"><path d="M16 5c0-0.6-0.4-1-1-1h-14c-0.6 0-1 0.4-1 1v6c0 0.6 0.4 1 1 1h14c0.6 0 1-0.4 1-1v-6zM15 11h-14v-6h14v6z" ></path><path d="M2 6h1v4h-1v-4z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'number'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M15 6v-2h-2.6l0.6-2.8-2-0.4-0.7 3.2h-3l0.7-2.8-2-0.4-0.7 3.2h-3.3v2h2.9l-0.9 4h-3v2h2.6l-0.6 2.8 2 0.4 0.7-3.2h3l-0.7 2.8 2 0.4 0.7-3.2h3.3v-2h-2.9l0.9-4h3zM9 10h-3l1-4h3l-1 4z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'true-false'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M14 6.2v7.8h-12v-12h10.5l1-1h-12.5v14h14v-9.8z" ></path><path d="M7.9 10.9l-4.2-4.2 1.5-1.4 2.7 2.8 6.7-6.7 1.4 1.4z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'checkboxes'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M14 6.2v7.8h-12v-12h10.5l1-1h-12.5v14h14v-9.8z" ></path><path d="M7.9 10.9l-4.2-4.2 1.5-1.4 2.7 2.8 6.7-6.7 1.4 1.4z" ></path></svg>
            </div>
            <div rv-show="field.type | eq 'select'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M15 4h-14c-0.6 0-1 0.4-1 1v6c0 0.6 0.4 1 1 1h14c0.6 0 1-0.4 1-1v-6c0-0.6-0.4-1-1-1zM10 11h-9v-6h9v6zM13 8.4l-2-1.4h4l-2 1.4z"></path></svg>
            </div>
            <div rv-show="field.type | eq 'radio'">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M8 4c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4z"></path><path d="M8 1c3.9 0 7 3.1 7 7s-3.1 7-7 7-7-3.1-7-7 3.1-7 7-7zM8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8v0z"></path></svg>
            </div>
            <div rv-show="field.type | eq 'content'">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 179 179"><path d="M0,3.416h143.082v36.929l-22.313-0.064v-9.083c0-4.645-3.609-8.447-8.235-8.699H84.061V145.17	c0.199,5.258,4.458,9.365,9.727,9.365h7.896l0.065,20.299h-60.38l0.067-20.299h7.894c5.192,0,9.442-4.038,9.72-9.21V22.498h-28.5c-4.626,0.252-8.235,4.055-8.235,8.699v9.083L0,40.345V3.416z M163.541,175.125h15V3.416h-15V175.125z"></path></svg>
            </div>
        </div>
        <div class="wapf-field__label" rv-on-click="setActiveField">
            {field.label} <span class="wapf-field__type"><span rv-text="field.type | fieldType"></span>&nbsp;&nbsp;&nbsp;&nbsp;ID: {field.id}</span>
        </div>

        <div class="wapf-field__actions">
            <div class="wapf__action_icon" rv-on-click="deleteField">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M13 3s0-0.51-2-0.8v-0.7c-0.017-0.832-0.695-1.5-1.53-1.5-0 0-0 0-0 0h-3c-0.815 0.017-1.47 0.682-1.47 1.5 0 0 0 0 0 0v0.7c-0.765 0.068-1.452 0.359-2.007 0.806l-0.993-0.006v1h12v-1h-1zM6 1.5c0.005-0.274 0.226-0.495 0.499-0.5l3.001-0c0 0 0.001 0 0.001 0 0.282 0 0.513 0.22 0.529 0.499l0 0.561c-0.353-0.042-0.763-0.065-1.178-0.065-0.117 0-0.233 0.002-0.349 0.006-0.553-0-2.063-0-2.503 0.070v-0.57z" ></path><path d="M2 5v1h1v9c1.234 0.631 2.692 1 4.236 1 0.002 0 0.003 0 0.005 0h1.52c0.001 0 0.003 0 0.004 0 1.544 0 3.002-0.369 4.289-1.025l-0.054-8.975h1v-1h-12zM6 13.92q-0.51-0.060-1-0.17v-6.75h1v6.92zM9 14h-2v-7h2v7zM11 13.72c-0.267 0.070-0.606 0.136-0.95 0.184l-0.050-6.904h1v6.72z" ></path></svg>
            </div>
            <div class="wapf__action_icon" rv-on-click="duplicateField">
                <svg height="16" width="16" viewBox="0 0 16 16"><path d="M6 0v3h3z"></path><path d="M9 4h-4v-4h-5v12h9z" ></path><path d="M13 4v3h3z" ></path><path d="M12 4h-2v9h-3v3h9v-8h-4z" ></path></svg>
            </div>
            <div class="wapf__action_icon wapf-field__sort" title="Drag & drop">
                <svg width="21" height="21" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M458.667 192H352c-5.888 0-10.667 4.779-10.667 10.667s4.779 10.667 10.667 10.667h106.667c17.643 0 32 14.357 32 32v213.333c0 17.643-14.357 32-32 32H245.333c-17.643 0-32-14.357-32-32v-85.333c0-5.888-4.779-10.667-10.667-10.667S192 367.445 192 373.333v85.333C192 488.064 215.936 512 245.333 512h213.333C488.064 512 512 488.064 512 458.667V245.333C512 215.936 488.064 192 458.667 192zM160 298.667h-10.667c-5.888 0-10.667 4.779-10.667 10.667S143.445 320 149.333 320H160c5.888 0 10.667-4.779 10.667-10.667s-4.779-10.666-10.667-10.666zM62.741 0h-9.408C48.235 0 43.2.725 38.357 2.133c-5.653 1.664-8.896 7.595-7.253 13.248 1.365 4.651 5.632 7.659 10.24 7.659 1.003 0 2.005-.128 3.008-.427a31.745 31.745 0 0 1 8.981-1.28h9.408c5.909 0 10.667-4.779 10.667-10.667S68.629 0 62.741 0zM21.333 257.216v-21.568c0-5.888-4.779-10.667-10.667-10.667S0 229.76 0 235.648v21.568c0 5.888 4.779 10.667 10.667 10.667s10.666-4.779 10.666-10.667zM10.667 203.179c5.888 0 10.667-4.779 10.667-10.667v-21.568c0-5.888-4.779-10.667-10.667-10.667S0 165.056 0 170.944v21.568c0 5.888 4.779 10.667 10.667 10.667zM10.667 73.792c5.888 0 10.667-4.779 10.667-10.667v-9.792c0-2.965.405-5.888 1.195-8.704 1.6-5.675-1.685-11.563-7.36-13.163-5.632-1.685-11.563 1.664-13.163 7.36A53.735 53.735 0 0 0 0 53.333v9.792c0 5.888 4.779 10.667 10.667 10.667zM10.667 138.496c5.888 0 10.667-4.779 10.667-10.667v-21.568c0-5.888-4.779-10.667-10.667-10.667S0 100.373 0 106.261v21.568c0 5.888 4.779 10.667 10.667 10.667zM44.587 297.451a32.104 32.104 0 0 1-13.781-8.085c-4.16-4.16-10.923-4.117-15.083.043-4.16 4.181-4.139 10.944.043 15.083a53.268 53.268 0 0 0 22.997 13.483 10.74 10.74 0 0 0 2.923.405c4.629 0 8.917-3.051 10.24-7.744 1.621-5.676-1.665-11.585-7.339-13.185zM307.605 51.968c.981 0 1.984-.128 2.987-.448 5.653-1.664 8.917-7.573 7.253-13.227-2.517-8.619-7.253-16.533-13.675-22.891-4.181-4.139-10.944-4.117-15.083.085-4.139 4.181-4.096 10.944.085 15.083 3.84 3.84 6.699 8.576 8.192 13.717a10.683 10.683 0 0 0 10.241 7.681zM256.832 0h-21.568c-5.888 0-10.667 4.779-10.667 10.667s4.779 10.667 10.667 10.667h21.568c5.888 0 10.667-4.779 10.667-10.667S262.72 0 256.832 0zM106.219 298.667H84.651c-5.888 0-10.667 4.779-10.667 10.667S78.763 320 84.651 320h21.568c5.888 0 10.667-4.779 10.667-10.667s-4.779-10.666-10.667-10.666zM309.333 73.6c-5.888 0-10.667 4.779-10.667 10.667v21.568c0 5.888 4.779 10.667 10.667 10.667S320 111.723 320 105.835V84.267c0-5.91-4.779-10.667-10.667-10.667zM192.149 0h-21.568c-5.909 0-10.667 4.779-10.667 10.667s4.779 10.667 10.667 10.667h21.547c5.909 0 10.688-4.779 10.688-10.667S198.037 0 192.149 0zM127.445 0h-21.568C99.989 0 95.21 4.779 95.21 10.667s4.779 10.667 10.667 10.667h21.568c5.888 0 10.667-4.779 10.667-10.667S133.333 0 127.445 0z"/><g><path d="M309.333 138.667c-5.888 0-10.667 4.779-10.667 10.667V160c0 5.888 4.779 10.667 10.667 10.667S320 165.888 320 160v-10.667c0-5.888-4.779-10.666-10.667-10.666z"/></g><path d="M349.952 185.515l-33.067-33.067c-12.971-12.971-34.112-12.971-47.083 0a33.135 33.135 0 0 0-8.171 13.419c-11.627-4.053-25.536-1.045-34.496 7.915-4.885 4.885-7.915 10.923-9.131 17.237-10.176-1.429-20.8 2.027-28.203 9.429-7.083 7.083-10.304 16.576-9.664 25.856a33.12 33.12 0 0 0-19.029 9.451c-6.293 6.293-9.749 14.656-9.749 23.552s3.456 17.28 9.749 23.552l9.557 9.557v38.251C170.667 360.064 194.603 384 224 384h43.733C331.84 384 384 331.84 384 267.733c0-31.04-12.096-60.245-34.048-82.218zm-82.219 177.152H224c-17.643 0-32-14.357-32-32V313.75l24.469 24.469c4.16 4.16 10.923 4.16 15.083 0a10.716 10.716 0 0 0 0-15.104l-55.36-55.36c-2.261-2.24-3.499-5.269-3.499-8.448 0-3.179 1.237-6.187 3.499-8.448 4.523-4.523 12.395-4.523 16.917 0l23.36 23.36c4.16 4.16 10.923 4.16 15.083 0a10.716 10.716 0 0 0 3.115-7.552c0-2.731-1.045-5.461-3.115-7.531l-26.667-26.667c-4.672-4.672-4.672-12.245 0-16.917 4.523-4.523 12.395-4.523 16.917 0l16 16c4.16 4.16 10.923 4.16 15.083 0 2.069-2.091 3.115-4.821 3.115-7.552s-1.045-5.461-3.115-7.531l-10.667-10.667c-4.672-4.672-4.672-12.245 0-16.917 4.523-4.523 12.395-4.523 16.917 0l10.667 10.667c4.16 4.16 10.923 4.16 15.083 0 2.069-2.091 3.115-4.821 3.115-7.552s-1.045-5.461-3.136-7.552c-2.261-2.24-3.499-5.248-3.499-8.448s1.259-6.208 3.52-8.448c4.672-4.672 12.245-4.672 16.917 0l33.067 33.067c17.92 17.92 27.797 41.771 27.797 67.115.001 52.351-42.581 94.933-94.933 94.933z"/></svg>
            </div>
        </div>
    </div>

    <div class="wapf-field__body" style="display: none;">

        <?php
        Html::setting([
            'type'              => 'types',
            'id'                => 'type',
            'label'             => __('Type','advanced-product-fields-for-woocommerce'),
            'description'       => __('What type of field should this be?','advanced-product-fields-for-woocommerce'),
            'options'           => \SW_WAPF\Includes\Classes\Fields::get_field_types(),
            'is_field_setting'  => true
        ]);

        Html::setting([
            'type'              => 'text',
            'id'                => 'label',
            'label'             => __('Label','advanced-product-fields-for-woocommerce'),
            'description'       => __('This is the label that is shown next to the field.','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true
        ]);

        echo '<div rv-if="field.type | neq \'content\'">';

        Html::setting([
            'type'              => 'textarea',
            'id'                => 'description',
            'label'             => __('Instructions','advanced-product-fields-for-woocommerce'),
            'description'       => __('Instructions can be used to display extra information near the field. Keep it short.','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true
        ]);

        Html::setting([
            'type'              => 'true-false',
            'id'                => 'required',
            'label'             => __('Required','advanced-product-fields-for-woocommerce'),
            'description'       => __('Select "yes" if the field should require input from the user.','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true
        ]);

        echo '</div>';

        foreach(\SW_WAPF\Includes\Classes\Fields::get_field_options($type) as $field_type => $options) {?>
            <div rv-if="field.type | eq '<?php echo $field_type; ?>'" class="wapf_field__options">
                <?php
                    foreach($options as $option) {
                        if(!empty($option) && isset($option['id']) && isset($option['type']))
                            Html::setting($option);
                    }
                ?>
            </div>
        <?php
        }

        echo '<div rv-if="field.type | neq \'content\'">';

        Html::setting([
            'type'              => 'true-false',
            'id'                => 'qty_based',
            'label'             => __('Repeat field','advanced-product-fields-for-woocommerce'),
            'description'       => __('Should this field be repeated multiple times either by a button or quantity input box?','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true,
            'pro'               => true
        ]);


        Html::setting([
            'type'              => 'conditionals',
            'id'                => 'conditionals',
            'label'             => __('Conditionals','advanced-product-fields-for-woocommerce'),
            'description'       => __('Only show this field when conditional rules are true.','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true
        ]);

        echo '</div>';

        Html::setting([
            'type'              => 'attributes',
            'id'                => 'attributes',
            'label'             => __('Wrapper attributes','advanced-product-fields-for-woocommerce'),
            'is_field_setting'  => true
        ]);
        ?>

    </div>

</div>