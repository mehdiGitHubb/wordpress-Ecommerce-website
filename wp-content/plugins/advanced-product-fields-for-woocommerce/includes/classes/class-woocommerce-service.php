<?php

namespace SW_WAPF\Includes\Classes {

    class Woocommerce_Service {

        public static function find_tags_by_name($term) {

            if(empty($term))
                return [];

            $tag_args = [
                'taxonomy'   => 'product_tag',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
                'name__like' => $term
            ];

            $product_tags = get_terms( $tag_args );

            if(!is_array($product_tags))
                return [];

            return Enumerable::from($product_tags)->select(function($term){
                return ['id' => $term->term_id, 'name' => $term->name];
            })->toArray();

        }

        public static function find_category_by_name($term) {
            if(empty($term))
                return [];

            $tag_args = [
                'taxonomy'   => 'product_cat',
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
                'name__like' => $term
            ];

            $product_tags = get_terms( $tag_args );

            if(!is_array($product_tags))
                return [];

            return Enumerable::from($product_tags)->select(function($term){
                return ['id' => $term->term_id, 'name' => $term->name];
            })->toArray();
        }

        public static function find_products_by_name($term)
        {

            if(empty($term))
                return [];

            $ds = new \WC_Product_Data_Store_CPT();
            $product_ids = $ds->search_products($term, '', false, true, 10);

            $products = [];

            foreach($product_ids as $pid) {
                if($pid === 0)
                    continue;

                $product = wc_get_product($pid);;
                if(empty($product))
                    continue;

                $products[] = [
                    'name' => $product->get_title(),
                    'id' => $product->get_id()
                ];

            }

            return $products;
        }

        public static function find_variations_by_name($term) {

            if(empty($term))
                return [];

            $args = [
                'posts_per_page'    => -1,
                'post_type'         => 'product_variation',
                'post_status'       => ['publish', 'pending', 'draft', 'future', 'private', 'inherit'],
                'fields'            => 'ids',
                's'                 => $term
            ];

            $variable_product_ids = get_posts($args);

            $products = [];

            foreach($variable_product_ids as $id) {

                $product = self::get_product($id);
                if($product === null)
                    continue;

                $attributes = $product->get_variation_attributes();

                foreach ($attributes as $key => $attribute) {
                    if ($attribute === '')
                        $attributes[$key] = __('any', 'advanced-product-fields-for-woocommerce') . ' ' .  strtolower(wc_attribute_label(str_replace('attribute_', '', $key)));
                }

                $products[] = [
                    'name'  => sprintf('%s (%s)', $product->get_title(), join(', ',$attributes)),
                    'id'    => $id
                ];

            }

            return $products;

        }

        public static function get_product($id)
        {
            $product = wc_get_product($id);
            if($product)
                return $product;
            return null;
        }

        public static function get_current_page_type() {
            if(is_product())
                return 'product';
            if(is_checkout())
                return 'checkout';
            if(is_shop())
                return 'shop';
            if(is_cart())
                return 'cart';

            return 'other';
        }

        public static function get_price_display_options() {

            return [
                'format'      => get_woocommerce_price_format(),
                'symbol'   => get_woocommerce_currency_symbol(),
                'decimals'          => wc_get_price_decimals(),
                'decimal'       => wc_get_price_decimal_separator(),
                'thousand'      => wc_get_price_thousand_separator()
            ];

        }
    }
}
