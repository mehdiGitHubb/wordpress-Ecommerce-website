<?php

namespace SW_WAPF\Includes\Classes {

    class Helper
    {

	    public static function wp_slash($value) {
		    if ( is_array( $value ) ) {
			    $value = array_map( 'self::wp_slash', $value );
		    }
		    if ( is_string( $value ) ) {
			    return addslashes( $value );
		    }
		    return $value;
	    }

        public static function get_all_roles() {

            $roles = get_editable_roles();

            return Enumerable::from($roles)->select(function($role, $id) {
                return [ 'id' => $id,'text' => $role['name'] ];
            })->toArray();
        }

        public static function cpt_to_string($cpt){

            return __('Product','advanced-product-fields-for-woocommerce');

        }

        public static function get_fieldgroup_counts(){

	        $count_cache = [ 'publish' => 0, 'draft' => 0, 'trash' => 0, 'all' => 0 ];

	        foreach(wapf_get_setting('cpts') as $cpt) {
		        $count = wp_count_posts($cpt);
		        $count_cache['publish'] += $count->publish;
		        $count_cache['trash'] += $count->trash;
		        $count_cache['draft'] += $count->draft;
	        }

	        $count_cache['all'] = $count_cache['publish'] + $count_cache['draft'];

	        return $count_cache;
        }

        public static function thing_to_html_attribute_string($thing){

            $encoded = wp_json_encode($thing);
            return function_exists('wc_esc_json') ? wc_esc_json($encoded) : _wp_specialchars($encoded, ENT_QUOTES, 'UTF-8', true);

        }

        public static function format_pricing_hint($type, $amount, $product, $for_page = 'shop') {

            $price_display_options = Woocommerce_Service::get_price_display_options();

            $price_output = sprintf(
                $price_display_options['format'],
                $price_display_options['symbol'],
                number_format(
	                self::adjust_addon_price($product,empty($amount) ? 0 : $amount,$type,$for_page),
	                $price_display_options['decimals'],
	                $price_display_options['decimal'],
	                $price_display_options['thousand']
                )
            );

            $sign = '+';

            return sprintf('%s%s',$sign,$price_output);

        }

        public static function normalize_string_decimal($number)
        {
            return preg_replace('/\.(?=.*\.)/', '', (str_replace(',', '.', $number)));
        }

	    public static function adjust_addon_price($product, $amount,$type,$for = 'shop') {

		    if($amount === 0)
			    return 0;

		    if($type === 'percent' || $type === 'p')
			    return $amount;

		    $amount = self::maybe_add_tax($product,$amount,$for);

		    return $amount;

	    }

	    public static function maybe_add_tax($product, $price, $for_page = 'shop') {

		    if(empty($price) || $price < 0 || !wc_tax_enabled())
			    return $price;

		    if(is_int($product))
			    $product = wc_get_product($product);

		    $args = [ 'qty' => 1, 'price' => $price ];

		    if($for_page === 'cart') {
			    if(get_option('woocommerce_tax_display_cart') === 'incl')
				    return wc_get_price_including_tax($product, $args);
			    else
				    return wc_get_price_excluding_tax($product, $args);
		    }
		    else
			    return wc_get_price_to_display($product, $args);

	    }

	    public static function get_product_base_price($product) {

		    return floatval($product->get_price());

	    }

    }
}