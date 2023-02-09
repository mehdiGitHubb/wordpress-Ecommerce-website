<?php

namespace SW_WAPF\Includes\Classes
{

    use SW_WAPF\Includes\Models\Field;
    use SW_WAPF\Includes\Models\FieldGroup;

    class Html
    {
	    private static $minimal_allowed_html = [
		    'br'        => [],
		    'hr'        => ['class' => [], 'style' => []],
		    'a'         => ['href' => [], 'target' => [], 'class' => [], 'style' => []],
		    'i'         => ['class' => [], 'style' => []],
		    'em'        => ['class' => [], 'style' => []],
		    'strong'    => ['class' => [], 'style' => []],
		    'b'         => ['class' => [], 'style' => []],
		    'span'      => ['class' => [], 'style' => []],
		    'div'       => ['class' => [], 'style' => []],
		    'h1'      => ['class' => [], 'style' => []],
		    'h2'      => ['class' => [], 'style' => []],
		    'h3'      => ['class' => [], 'style' => []],
		    'h4'      => ['class' => [], 'style' => []],
		    'h5'      => ['class' => [], 'style' => []],
		    'h6'      => ['class' => [], 'style' => []],
	    ];

	    #region General views
        public static function partial($view, $model = null)
        {
            ob_start();
            $dir = trailingslashit(wapf_get_setting('path')) . 'views/' . $view;
            include $dir . '.php';
            echo ob_get_clean();
        }

        public static function view($view, $model = null)
        {
            ob_start();
            $dir = trailingslashit(wapf_get_setting('path')) . 'views/' . $view;

            include $dir . '.php';
            return ob_get_clean();
        }

        #endregion

        #region Admin Functions
	    public static function help_modal($text,$title = '', $button_text = '') {
		    $model = [
			    'content'   => $text,
			    'title'     => $title,
			    'button'    => $button_text
		    ];
		    ob_start();
		    $path = trailingslashit(wapf_get_setting('path')) . 'views/admin/help-modal.php';
		    include $path;
		    echo ob_get_clean();
	    }

        public static function setting($model = []) {

            if(!isset($model['type']))
                return;

            ob_start();
            $dir = trailingslashit(wapf_get_setting('path')) . 'views/admin/settings/' . $model['type'];
            include $dir . '.php';
            echo ob_get_clean();
        }

        public static function admin_field($field = [], $type = 'wapf_product') {
            ob_start();
            $path = trailingslashit(wapf_get_setting('path')) . 'views/admin/field.php';
            include $path;
            echo ob_get_clean();
        }

        public static function wp_list_table($view_name,$model,$list) {
            ob_start();
            $path = trailingslashit(wapf_get_setting('path')) . 'views/admin/'.$view_name.'.php';
            include $path;
            echo ob_get_clean();
        }
        #endregion

        #region Product-related Functions
        public static function product_totals($product) {

            $product_id = $product->get_id();
	        $product_type = $product->get_type() === 'variation' ? 'variable' : $product->get_type();
            $product_price = wc_get_price_to_display($product);

            ob_start();
            $path = trailingslashit(wapf_get_setting('path')) . 'views/frontend/product-totals.php';
            include $path;
            $totals_html = ob_get_clean();

            echo apply_filters('wapf/html/product_totals',$totals_html, $product);

        }
        #endregion

        #region Field Groups and Fields

        public static function field_group($product, FieldGroup $field_group, $data = [] ) {

            if(empty($field_group) || empty($field_group->fields))
                return '';

            ob_start();
            $dir = trailingslashit(wapf_get_setting('path')) . 'views/frontend/field-group.php';
            include $dir;
            return ob_get_clean();

        }

        public static function field($product,Field $field, $fieldgroup_id) {

            $model = [
            	'product'           => $product,
                'field'             => $field,
                'field_value'       => self::field_value($field),
                'field_attributes'  => self::field_attributes($product,$field,$fieldgroup_id)
            ];

            $file_name =  $field->type === 'paragraph' ? 'content' : $field->type;

            return self::view('frontend/fields/' . $file_name, $model);
        }

        public static function field_description(Field $field) {
            if(!empty($field->description)) {
                return '<div class="wapf-field-description">'.wp_kses( $field->description, self::$minimal_allowed_html).'</div>';
            }

            return '';
        }

        public static function field_container_classes(Field $field) {

            $extra_classes = apply_filters('wapf/field_classes/' . $field->key, [] );
            $classes = ['wapf-field-container','wapf-field-' . $field->type];

            if(!empty($field->class))
                $classes[] = $field->class;

            if(!empty($field->conditionals))
                $classes[] = 'wapf-hide';

            return implode(' ', array_merge(array_map('sanitize_html_class', $extra_classes), $classes));
        }

        public static function field_container_attributes(Field $field){

            $attributes = ['for' => $field->id];

            return Enumerable::from($attributes)->join(function($value,$key){
                if($value)
                    return $key . '="' . esc_attr($value) .'"';
                else return $key;
            },' ');
        }

        public static function field_label(Field $field, $product, $show_required_symbol = true) {

            $label = '<span>' . wp_kses($field->label, self::$minimal_allowed_html) .'</span>';

            if($show_required_symbol && $field->required)
                $label .= ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';

            if($field->pricing_enabled() && $field->type !== 'true-false' && !$field->is_choice_field())
                $label .= ' <span class="wapf-pricing-hint">('. Helper::format_pricing_hint($field->pricing->type, $field->pricing->amount,$product,'shop') .')</span>';

            return $label;
        }

        private static function field_attributes($product,Field $field, $field_group_id) {

            $extra_classes = apply_filters('wapf/field_classes/' . $field->key, [] );
            $classes = ['wapf-input'];

            $field_attributes = [
                'name'              => 'wapf[field_'.$field->id.']',
                'class'             => implode(' ',array_merge(array_map('sanitize_html_class',$extra_classes,$classes))),
                'data-is-required'  => $field->required,
                'data-field-id'     => $field->id
            ];

            if($field->required)
                $field_attributes['required'] = '';

            if($field->type !== 'select' && $field->pricing_enabled() ) { 
                $field_attributes['data-wapf-price'] = Helper::adjust_addon_price( $product,$field->pricing->amount, $field->pricing->type, 'shop' );

                $field_attributes['data-wapf-pricetype'] = $field->pricing->type;
            }

            if(!empty($field->conditionals))
                $field_attributes['data-dependencies'] = Helper::thing_to_html_attribute_string($field->conditionals);

            if(isset($field->options['placeholder']))
                $field_attributes['placeholder'] = $field->options['placeholder'];

            if(isset($field->options['minimum']))
                $field_attributes['min'] = $field->options['minimum'];

            if(isset($field->options['maximum']))
                $field_attributes['max'] = $field->options['maximum'];

            if($field->type === 'true-false' && isset($field->options['default']) && $field->options['default'] === 'checked')
                $field_attributes['checked'] = '';

            return Enumerable::from($field_attributes)->join(function($value,$key){
                if($value)
                    return $key . '="' . esc_attr($value) .'"';
                else return $key;
            },' ');

        }

        private static function field_value(Field $field) {

	        if( $field->type === 'paragraph' || $field->type === 'content' ) {
		        return empty($field->options['p_content']) ?
			        '' :
			        esc_html( $field->options['p_content'] );
	        }

            $value = empty($field->options['default']) ? '' : esc_html($field->options['default']);

            return $value;
        }

        #endregion
    }
}