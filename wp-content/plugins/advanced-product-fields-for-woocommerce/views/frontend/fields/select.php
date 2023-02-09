<?php
/** @var array $model */
use SW_WAPF\Includes\Classes\Helper;
use SW_WAPF\Includes\Classes\Enumerable;

?>


<select <?php echo $model['field_attributes']; ?>>
    <?php
        if(isset($model['field']->options['choices'])) {
	        if(!$model['field']->required || ($model['field']->required && !Enumerable::from($model['field']->options['choices'])->any(function($x){
				        return isset($x['selected']) && $x['selected'] === true;
			        })))
		        echo '<option value="">' . __( 'Choose an option','advanced-product-fields-for-woocommerce') . '</option>';

            foreach($model['field']->options['choices'] as $option) {
                $attributes = [
                    'value' => $option['slug']
                ];
                $has_pricing = isset($option['pricing_type']) && $option['pricing_type'] !== 'none';
                if($has_pricing) {
	                $attributes['data-wapf-price'] = Helper::adjust_addon_price( $model['product'], $option['pricing_amount'], $option['pricing_type'], 'shop' );
                    $attributes['data-wapf-pricetype'] = $option['pricing_type'];
                }
                if( isset($option['selected']) && $option['selected'] === true )
                    $attributes['selected'] = '';

                echo sprintf(
                    '<option %s>%s</option>',
                    Enumerable::from($attributes)->join(function($value,$key) {
                        if($value)
                            return $key . '="' . esc_attr($value) .'"';
                        else return $key;
                    },' '),
                    esc_html($option['label']) . ( $has_pricing ? ' <span class="wapf-pricing-hint">('. Helper::format_pricing_hint($option['pricing_type'], $option['pricing_amount'],$model['product'],'shop') .')</span>' : '')
                );
            }
        }
    ?>
</select>