<?php
/** @var array $model */
use SW_WAPF\Includes\Classes\Helper;
use SW_WAPF\Includes\Classes\Enumerable;

if(!empty($model['field']->conditionals))
    $dependencies = Helper::thing_to_html_attribute_string($model['field']->conditionals);

if(!empty($model['field']->options['choices'])) {

    echo '<div class="wapf-radios">';

    foreach ($model['field']->options['choices'] as $option) {
        $unique = mt_rand(10000,99999);
        $attributes = [
            'id'            => $unique, //'wapf_field_' . $model['field']->id .'_' . $option['slug'],
            'name'          => sprintf('wapf[field_%s]', $model['field']->id),
            'class'         => 'wapf-input',
            'type'          => 'radio',
            'data-field-id' => $model['field']->id,
            'value'         => $option['slug'],
        ];
        if($model['field']->required)
            $attributes['required'] = '';
        if(isset($dependencies))
            $attributes['data-dependencies'] = $dependencies;
        if(isset($option['selected']) && $option['selected'] === true)
            $attributes['checked'] = '';
        $has_pricing = isset($option['pricing_type']) && $option['pricing_type'] !== 'none';
        if($has_pricing) {
	        $attributes['data-wapf-price'] = Helper::adjust_addon_price( $model['product'], $option['pricing_amount'], $option['pricing_type'], 'shop' );
            $attributes['data-wapf-pricetype'] = $option['pricing_type'];
        }

        $wrapper_classes = ['wapf-checkable'];
        if(isset($attributes['checked']))
            $wrapper_classes[] = 'wapf-checked';

        echo sprintf(
            '<div class="%s"><label for="%s" class="wapf-input-label"><input %s /><span class="wapf-label-text">%s</span></label></div>',
            join(' ',$wrapper_classes),
            $unique, //'wapf_field_' . $model['field']->id .'_' . $option['slug'],
            Enumerable::from($attributes)->join(function($value,$key) {
                if($value)
                    return $key . '="' . esc_attr($value) .'"';
                else return $key;
            },' '),
            esc_html($option['label']) . (isset($option['pricing_type']) && $option['pricing_type'] === 'none' ? '' : ' <span class="wapf-pricing-hint">('. Helper::format_pricing_hint($option['pricing_type'], $option['pricing_amount'],$model['product'],'shop') .')</span>')
        );

    }

    echo '</div>';

}