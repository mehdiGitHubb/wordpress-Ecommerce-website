<?php

namespace SW_WAPF\Includes\Classes {


    use SW_WAPF\Includes\Models\Field;
	use SW_WAPF\Includes\Models\FieldGroup;

	class Fields
    {

		public static function get_field_types() {
			$types = [
				[
					'id'    => 'text',
					'title' => __('Text','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'textarea',
					'title' => __('Text Area','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'number',
					'title' => __('Number','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'email',
					'title' => __('E-mail','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'url',
					'title' => __('URL','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'select',
					'title' => __('Select','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'true-false',
					'title' => __('True/False','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'checkboxes',
					'title' => __('Checkboxes','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'radio',
					'title' => __('Radio buttons','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'content',
					'title' => __('Content: paragraph','advanced-product-fields-for-woocommerce'),
					'pro'   => false,
				],
				[
					'id'    => 'file',
					'title' => __('File upload','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'date',
					'title' => __('Date','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'image-swatch',
					'title' => __('Image swatches','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'color-swatch',
					'title' => __('Color swatches','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'text-swatch',
					'title' => __('Text swatches','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'img',
					'title' => __('Content: image','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
				[
					'id'    => 'section',
					'title' => __('Layout: section','advanced-product-fields-for-woocommerce'),
					'pro'   => true,
				],
			];

			$types = apply_filters('wapf/field_types', $types);

			return $types;

		}

        public static function get_field_options($type = 'wapf_product') {

	        $options =  [

		        'true-false' => [
			        [
				        'type'          => 'text',
				        'id'            => "message",
				        'label'         => __('Message','advanced-product-fields-for-woocommerce'),
				        'description'   => __('Displays text alongside the checkbox.','advanced-product-fields-for-woocommerce'),
			        ],
			        [
				        'type'          => 'select',
				        'options'       => [
					        'checked'   => __('Checked','advanced-product-fields-for-woocommerce'),
					        'unchecked' => __('Unchecked', 'advanced-product-fields-for-woocommerce')
                        ],
                        'default'       => 'unchecked',
                        'id'            => "default",
                        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
                        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
                    ],
                    [
	                    'type'          => 'pricing',
	                    'id'            => "pricing",
	                    'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
	                    'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
                    ],
                ],

                'text'      => [
		        [
			        'type'          => 'text',
			        'id'            => 'default',
			        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
			        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
		        ],
		        [
			        'type'          => 'text',
			        'id'            => 'placeholder',
			        'label'         => __('Placeholder text','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Appears within the input field','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'pricing',
			        'id'            => "pricing",
			        'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
		        ],
	        ],

                'textarea'      => [
		        [
			        'type'          => 'textarea',
			        'id'            => 'default',
			        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
			        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
		        ],
		        [
			        'type'          => 'text',
			        'id'            => 'placeholder',
			        'label'         => __('Placeholder text','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Appears within the input field','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'pricing',
			        'id'            => "pricing",
			        'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
		        ],
	        ],

                'number'      => [
		        [
			        'type'          => 'number',
			        'id'            => 'default',
			        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
			        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
		        ],
		        [
			        'type'          => 'text',
			        'id'            => 'placeholder',
			        'label'         => __('Placeholder text','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Appears within the input field','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'number',
			        'id'            => 'minimum',
			        'label'         => __('Minimum value','advanced-product-fields-for-woocommerce'),
			        'placeholder'   => __('No minimum','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'number',
			        'id'            => 'maximum',
			        'label'         => __('Maximum value','advanced-product-fields-for-woocommerce'),
			        'placeholder'   => __('No maximum','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'pricing',
			        'id'            => "pricing",
			        'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
		        ],
	        ],

                'email'     => [
		        [
			        'type'          => 'email',
			        'id'            => 'default',
			        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
			        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
		        ],
		        [
			        'type'          => 'text',
			        'id'            => 'placeholder',
			        'label'         => __('Placeholder text','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Appears within the input field','advanced-product-fields-for-woocommerce')
		        ],
		        $type === 'wapf_product' ?
			        [
				        'type'          => 'pricing',
				        'id'            => "pricing",
				        'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
				        'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
			        ] : [],
	        ],

                'url'       => [
		        [
			        'type'          => 'url',
			        'id'            => 'default',
			        'label'         => __('Default value','advanced-product-fields-for-woocommerce'),
			        'description'   => __('The pre-set value of the field when the page loads.','advanced-product-fields-for-woocommerce'),
		        ],
		        [
			        'type'          => 'text',
			        'id'            => 'placeholder',
			        'label'         => __('Placeholder text','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Appears within the input field','advanced-product-fields-for-woocommerce')
		        ],
		        [
			        'type'          => 'pricing',
			        'id'            => "pricing",
			        'label'         => __('Adjust pricing','advanced-product-fields-for-woocommerce'),
			        'description'   => __('Should the price of the product or cart change when the user interacts with this field?','advanced-product-fields-for-woocommerce'),
		        ],
	        ],

                'select'    => [
		        [
			        'type'                  => 'options',
			        'id'                    => 'options',
			        'label'                 => __('Options','advanced-product-fields-for-woocommerce'),
			        'description'           => __('Add the options for this select list.','advanced-product-fields-for-woocommerce'),
			        'multi_option'          => false,
			        'show_pricing_options'  => true
		        ]
	        ],

                'checkboxes'  => [
		        [
			        'type'                  => 'options',
			        'id'                    => 'options',
			        'label'                 => __('Options','advanced-product-fields-for-woocommerce'),
			        'description'           => __('Each option is a checkbox.','advanced-product-fields-for-woocommerce'),
			        'multi_option'          => true,
			        'show_pricing_options'  => true
		        ],

	        ],

            'radio'  => [
		        [
			        'type'                  => 'options',
			        'id'                    => 'options',
			        'label'                 => __('Options','advanced-product-fields-for-woocommerce'),
			        'description'           => __('Each option is a radio button.','advanced-product-fields-for-woocommerce'),
			        'multi_option'          => false,
			        'show_pricing_options'  => true
		        ],

	        ],
	        'paragraph' => [
		        [
			        'type'                  => 'textarea',
			        'id'                    => 'p_content',
			        'label'                 => __("Content",'sw-wapf'),
			        'description'           => __('Enter your text here. Do you need to add shortcodes & HTML? Consider upgrading to our Pro version.', 'sw-wapf')
		        ]
	        ],
            'content' => [
                    [
                        'type'                  => 'textarea',
                        'id'                    => 'p_content',
                        'label'                 => __("Content",'sw-wapf'),
                        'description'           => __('Enter your text here. Do you need to add shortcodes & HTML? Consider upgrading to our Pro version.', 'sw-wapf')
                    ]
                ],
            ];

            $options = apply_filters('wapf/field_options', $options);

            foreach($options as &$group) {
                foreach($group as &$option) {
                    $option['is_field_setting'] = true;
                }
            }

            return $options;

        }

	    public static function should_field_be_filled_out(FieldGroup $group, Field $field) {

		    if(!$field->required)
			    return false;

		    if(!$field->has_conditionals())
			    return true;

		    foreach ($field->conditionals as $conditional) {
			    if(self::validate_rules($group,$conditional->rules)) 
				    return true;
		    }

		    return false;

	    }

		private static function validate_rules(FieldGroup $group, $rules) {

			foreach ($rules as $rule) {
				if(!self::is_valid_rule($group,$rule->field,$rule->condition,$rule->value))
					return false;
			}

			return true;
		}


		private static function is_valid_rule(FieldGroup $group, $field_id, $condition, $rule_value) {

			$field = Enumerable::from($group->fields)->firstOrDefault(function($x) use($field_id) {
				return $x->id === $field_id;
			});

			if(!$field)
				return false;

			$value = Fields::get_raw_field_value_from_request($field, 0, true);

			if($value === null)
				return false;

			$value = self::sanitize_raw_value($field, $value);

			switch($condition) {
				case "check"     : return $value === 'true';
				case "!check"    : return $value === 'false';
				case '=='        : return in_array($rule_value, (array) $value);
				case '!='        : return !in_array($rule_value, (array) $value);
				case 'empty'     : return empty($value);
				case '!empty'    : return !empty($value);
				case 'lt'        : return floatval($value) < floatval($rule_value);
				case 'gt'        : return floatval($value) > floatval($rule_value);
			}

			return false;
		}

        public static function get_pricing_options() {

            $options = [
                'fixed'     => [ 'label' => __('Flat fee (not quantity-based)', 'advanced-product-fields-for-woocommerce'), 'pro' => false ],
                'qt'        => [ 'label' => __('Quantity based flat fee (Pro only)', 'advanced-product-fields-for-woocommerce'), 'pro' => true ],
                'fx'        => [ 'label' => __('Formula (Pro only)', 'advanced-product-fields-for-woocommerce'), 'pro' => true ],
                'percent'   => [ 'label' => __('Percentage based (Pro only)', 'advanced-product-fields-for-woocommerce'), 'pro' => true ],
                'nr'        => [ 'label' => __('Amount &times; field value (Pro only)', 'advanced-product-fields-for-woocommerce'), 'pro' => true ],
                'char'      => [ 'label' => __('Amount &times; character count (Pro only)', 'advanced-product-fields-for-woocommerce'), 'pro' => true ],
            ];

            return $options;
        }

        public static function sanitize_raw_value(Field $field,$value) {
	        switch($field->type) {
		        case 'checkboxes'   :
		        case 'radio'        :
		        case 'select'       :
		        	return Enumerable::from((array)$value)->select(function($x){
		        		return sanitize_text_field($x);
			        })->toArray();
		        case 'textarea'     : return sanitize_textarea_field(trim($value));
		        case 'number'       : return filter_var(Helper::normalize_string_decimal($value),FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		        case 'true-false'   : return $value == '1' ? __('true','advanced-product-fields-for-woocommerce') : __('false','advanced-product-fields-for-woocommerce');
		        case 'email'        : return sanitize_email(trim($value));
		        default             : return self::sanitize_value($field,$value);
	        }
        }

        public static function sanitize_value(Field $field,$value) {

            switch($field->type) {
                case 'textarea'     : return sanitize_textarea_field(trim($value));
                case 'number'       : return filter_var(Helper::normalize_string_decimal($value),FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
                case 'true-false'   : return $value == '1' ? __('true','advanced-product-fields-for-woocommerce') : __('false','advanced-product-fields-for-woocommerce');
                case 'email'        : return sanitize_email(trim($value));
	            case 'checkboxes'   :
	            case 'radio'        :
	            case 'select'       :
		            return join(', ', Enumerable::from((array) $value)->select(function($v) use ($field) {
			            $choice = Enumerable::from($field->options['choices'])->firstOrDefault(function($choice) use($v) {
				            return $choice['slug'] === $v;
			            });
			            if($choice)
				            return esc_html($choice['label']);

			            return '';
		            })->toArray());
                default             : return sanitize_text_field(trim($value));
            }
        }

	    public static function get_raw_field_value_from_request(Field $for_field, $clone_index = 0, $return_null = false) {

		    $field_name = 'field_' . $for_field->id . ($clone_index > 0 ? ('_clone_'.$clone_index):'');

		    if(!isset($_REQUEST['wapf']) || !isset($_REQUEST['wapf'][$field_name]))
			    return $return_null ? null : '';

		    return is_string($_REQUEST['wapf'][$field_name]) ? stripslashes($_REQUEST['wapf'][$field_name]) : $_REQUEST['wapf'][$field_name];
	    }

        public static function pricing_value(Field $field, $raw_value) {

            if(empty($raw_value))
                return [];

            if($field->type === 'true-false' && $raw_value == '0')
                return [];

            $pricing = [];

            if( $field->is_choice_field() ) {

                foreach ((array) $raw_value as $rv) {

                    $choice = Enumerable::from($field->options['choices'])->firstOrDefault(function($choice) use($rv) {
                        return $choice['slug'] === $rv;
                    });

                    if(!$choice || $choice['pricing_type'] === 'none')
                        continue;

                    $pricing[] = [ 'value' => $choice['pricing_amount'], 'type' => $choice['pricing_type'] ];

                }
                return $pricing;

            }

            $pricing[] = ['value' => $field->pricing->amount, 'type' => $field->pricing->type];

            return $pricing;
        }

        public static function value_to_string(Field $field, $raw_value, $include_price_label = true, $product = null, $for_page = 'shop') {

            if($include_price_label) {

                if(!empty($field->options['choices'])) {
                    $labels = [];

                    foreach ((array) $raw_value as $rv) {

                        $choice = Enumerable::from($field->options['choices'])->firstOrDefault(function($choice) use($rv) {
                            return $choice['slug'] === $rv;
                        });

                        if(!$choice)
                            continue;

                        if($choice['pricing_type'] === 'none')
                            $labels[] = $choice['label'];
                        else $labels[] = sprintf('%s (%s)', esc_html($choice['label']), Helper::format_pricing_hint($choice['pricing_type'],$choice['pricing_amount'],$product,$for_page));

                    }

                    return join(', ', $labels);
                }

                return $field->pricing_enabled() ?
	                sprintf('%s (%s)', self::sanitize_value($field,$raw_value), Helper::format_pricing_hint($field->pricing->type,$field->pricing->amount,$product,$for_page))
	                : self::sanitize_value($field,$raw_value) ;
            }

            return self::sanitize_value($field,$raw_value);

        }

        public static function do_pricing($amount, $qty) {
            return (float) $amount/$qty;
        }

	    public static function is_field_value_valid(Field $field, $value = null) {

		    if($field->required) {

			    if($value === null)
				    return true;

			    if(empty($value))
				    return false;

		    }

		    return true;
	    }

    }
}