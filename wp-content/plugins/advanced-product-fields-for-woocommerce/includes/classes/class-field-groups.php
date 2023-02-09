<?php

namespace SW_WAPF\Includes\Classes {

    use SW_WAPF\Includes\Models\Conditional;
    use SW_WAPF\Includes\Models\ConditionalRule;
    use SW_WAPF\Includes\Models\ConditionRule;
    use SW_WAPF\Includes\Models\ConditionRuleGroup;
    use SW_WAPF\Includes\Models\Field;
    use SW_WAPF\Includes\Models\FieldGroup;

    class Field_Groups
    {

        private static $all_groups_cache_key = 'field-groups-';
        private static $field_group_cache_key = 'field-group-';
        public static $allowed_html_minimal = [
            'a'         => [ 'href'  => [], 'title' => [], 'target'=> [], 'class' => [] ],
            'b'         => ['class' => []],
            'em'        => ['class' => [] ],
            'strong'    => ['class' => [] ],
            'i'         => ['class' => [] ],
            'span'      => ['style' => [],'class' => [] ],
            'ul'        => ['class' => [] ],
            'ol'        => ['class' => [] ],
            'li'        => ['class' => [] ],
            'br'        => []
        ];

        public static function field_group_to_raw_fields_json(FieldGroup $fg) {
            $json_array = json_decode(json_encode($fg->fields),true);

            foreach($json_array as &$field) {
                if (!empty($field["options"])) {
                    foreach ($field["options"] as $k => $v) {
                        $field[$k] = $v;
                    }
                    unset($field['options']);
                }

                if( $field['type'] === 'paragraph') $field['type'] = 'content';
            }



            return $json_array;
        }

        public static function raw_json_to_field_group($raw) {

            $fg = new FieldGroup();

            $fg->id = sanitize_text_field($raw['id']);
            $fg->type = sanitize_text_field($raw['type']);

            if(isset($raw['layout'])) {

                if(isset($raw['layout']['labels_position']))
                    $fg->layout['labels_position'] = sanitize_text_field($raw['layout']['labels_position']);

                if(isset($raw['layout']['instructions_position']))
                    $fg->layout['instructions_position'] = sanitize_text_field($raw['layout']['instructions_position']);

                if(isset($raw['layout']['mark_required']))
                    $fg->layout['mark_required'] = $raw['layout']['mark_required'] == 'true' ? true : false;

                if(isset($raw['layout']['position']))
                    $fg->layout['position'] = sanitize_text_field($raw['layout']['position']);

            }

            foreach($raw['fields'] as $raw_field) {

                $field = new Field();
                $field->id = sanitize_text_field($raw_field['id']);
                if(isset($raw_field['key']))
                    $field->key = sanitize_text_field($raw_field['key']);
                if(isset($raw_field['label']))
                    $field->label = wp_kses($raw_field['label'], self::$allowed_html_minimal);
	            if(isset($raw_field['description']))
                    $field->description = wp_kses($raw_field['description'], self::$allowed_html_minimal);
                $field->type = sanitize_text_field($raw_field['type']);
                $field->required = isset($raw_field['required']) && $raw_field['required'] == 'true' ? true : false;
                if(isset($raw_field['class']))
                    $field->class = sanitize_html_class($raw_field['class']);
                if(isset($raw_field['width']))
                    $field->width = intval($raw_field['width']);

                if(isset($raw_field['choices'])) {
                    $field->options['choices'] = [];
                    foreach ($raw_field['choices'] as $raw_choice) {
                        $choice = [
                            'slug'      => sanitize_text_field($raw_choice['slug']),
                            'label'     => wp_kses($raw_choice['label'], self::$allowed_html_minimal),
                            'selected'  =>  $raw_choice['selected'] == 'true' ? true : false
                        ];

                        if(isset($raw_choice['pricing_type']))
                            $choice['pricing_type'] = sanitize_text_field($raw_choice['pricing_type']);
                        if(isset($raw_choice['pricing_amount']))
                            $choice['pricing_amount'] = floatval($raw_choice['pricing_amount']);

                        $field->options['choices'][] = $choice;
                    }
                }

                if(isset($raw_field['placeholder']))
                    $field->options['placeholder'] = sanitize_text_field($raw_field['placeholder']);
                if(isset($raw_field['default']))
                    $field->options['default'] = sanitize_text_field($raw_field['default']);
	            if(isset($raw_field['p_content']))
		            $field->options['p_content'] = sanitize_textarea_field($raw_field['p_content'] );
                foreach($raw_field as $k => $v) {
                    if( in_array($k, ['id','key','label','description','default','placeholder','p_content','choices','conditionals','type','required','options','class','width','pricing','qty_based']) )
                        continue;
                    $field->options[sanitize_text_field($k)] = sanitize_textarea_field($v);
                }

                if(isset($raw_field['pricing'])) {
                    $field->pricing->enabled = $raw_field['pricing']['enabled'] == 'true' ? true : false;
                    $field->pricing->amount = (float) floatval(Helper::normalize_string_decimal($raw_field['pricing']['amount']));
                    $field->pricing->type = sanitize_text_field($raw_field['pricing']['type']);
                }

                foreach($raw_field['conditionals'] as $raw_conditional) {

                    $conditional = new Conditional();

                    foreach($raw_conditional['rules'] as $raw_rule){
                        $rule = new ConditionalRule();
                        $rule->field = sanitize_text_field($raw_rule['field']);
                        $rule->value = sanitize_text_field($raw_rule['value']);
                        $rule->condition = sanitize_text_field($raw_rule['condition']); 

                        $conditional->rules[] = $rule;
                    }

                    $field->conditionals[] = $conditional;

                }

                $fg->fields[] = $field;

            }

            if(!empty($raw['conditions'])) {
	            foreach ( $raw['conditions'] as $raw_condition ) {
		            $condition = new ConditionRuleGroup();

		            foreach ( $raw_condition['rules'] as $raw_rule ) {
			            $rule = new ConditionRule();

			            $rule->condition = sanitize_text_field( $raw_rule['condition'] );
			            if(isset($raw_rule['value']))
				            $rule->value     = is_string( $raw_rule['value'] ) ? sanitize_text_field( $raw_rule['value'] ) : Enumerable::from( $raw_rule['value'] )->select( function ( $value ) {
					            return [
						            'id'   => sanitize_text_field( $value['id'] ),
						            'text' => sanitize_text_field( $value['text'] )
					            ];
				            } )->toArray();
			            $rule->subject   = sanitize_text_field( $raw_rule['subject'] );

			            $condition->rules[] = $rule;
		            }

		            $fg->rules_groups[] = $condition;

	            }
            }

            return $fg;

        }

        public static function get_all($of_type = 'product') {

            $cache_key = self::$all_groups_cache_key . $of_type;

            $cached = Cache::get($cache_key);

            if($cached === false) {

				$args = [
					'numberposts'               => -1,
					'post_type'                 => 'wapf_' . $of_type,
					'posts_per_page'            => -1,
					'post_status'               => 'publish',
					'update_post_meta_cache'    => false    
				];

	            if(function_exists('icl_get_languages')) {
		            $args['suppress_filters'] = false;
	            }

                $posts = get_posts($args);

                $groups = [];

                foreach ($posts as $post) {
                    $groups[] = self::process_data($post->post_content);
                }

                $cached = $groups;

                Cache::set($cache_key,$groups);

            }

            return $cached;
        }

        public static function get_by_id($id) {

            global $post;

            if($post && $post->ID == $id && in_array($post->post_type, wapf_get_setting('cpts')))
                return self::process_data($post->post_content);

            $cache_key = self::$field_group_cache_key . $id;

            $cached = Cache::get($cache_key );
            if($cached !== false) {
                return $cached;
            }

            if(strpos($id, 'p_') !== false) {
                $the_group = self::process_data(get_post_meta(intval(str_replace('p_','',$id)),'_wapf_fieldgroup', true));
                Cache::set($cache_key,$the_group);
                return $the_group;
            }

            $types = ['product'];

            foreach($types as $type) {
                $all_groups_cached = Cache::get(self::$all_groups_cache_key . $type);

                if($all_groups_cached !== false) {

                    $the_group = Enumerable::from($all_groups_cached)->firstOrDefault(function($x) use($id) {
                        return $x->id === $id;
                    });

                    if($the_group) {
                        Cache::set($cache_key, $the_group);
                        return $the_group;
                    }
                }
            }

            $post = get_post(intval($id));

            if(!$post || !in_array($post->post_type,wapf_get_setting('cpts')))
                return null;

            $cached = self::process_data($post->post_content);
            Cache::set($cache_key,$cached);

            return $cached;

        }

        public static function get_by_ids(array $ids) {

            $field_groups = [];

            foreach($ids as $id) {

                $field_group = self::get_by_id($id);
                if($field_group)
                    $field_groups[] = $field_group;
            }

            return $field_groups;

        }

        public static function get_valid_field_groups($of_type) {

            $field_groups = Field_Groups::get_all($of_type);
            $valid_field_groups = [];

            foreach ($field_groups as $field_group) {
                if(Conditions::is_field_group_valid($field_group))
                    $valid_field_groups[] = $field_group;
            }

            return $valid_field_groups;

        }

        public static function get_valid_rule_groups(FieldGroup $field_group) {

            $valids = [];

            foreach ($field_group->rules_groups as $rules_group) {
                if(Conditions::is_rule_group_valid($rules_group))
                    $valids[] = $rules_group;
            }

            return $valids;

        }

	    public static function get_field_groups_of_product($product) {

		    if(is_int($product))
			    $product = wc_get_product($product);

		    $field_groups_of_product = [];
		    $field_group_on_product = self::process_data(get_post_meta($product->get_id(),'_wapf_fieldgroup', true));

		    if($field_group_on_product)
			    array_push($field_groups_of_product, $field_group_on_product);

		    $all_field_groups = self::get_all();

		    foreach ($all_field_groups as $fg) {
			    if(Conditions::is_field_group_valid_for_product($fg, $product))
				    $field_groups_of_product[] = $fg;
		    }

		    return $field_groups_of_product;

	    }

        public static function product_has_field_group($product) {

            if(is_int($product))
                $product = wc_get_product($product);

            $field_group_on_product = get_post_meta($product->get_id(),'_wapf_fieldgroup', true);

            if(!empty($field_group_on_product))
                return true;

            $field_groups = Field_Groups::get_all('product');

            foreach ($field_groups as $group) {

            	if(empty($group->fields))
            		return false;

                if(Conditions::is_field_group_valid_for_product($group,$product))
                    return true;

            }

            return false;

        }

        public static function save(FieldGroup $fg, $post_type = 'wapf_product', $post_id = null, $post_title = null, $status = null) {

            $post_type = strtolower($post_type);
            $fg->type = $post_type;

            $save = [
                'post_type' => $post_type
            ];

            if($post_id != null) {
                $save['ID'] = $post_id;
                $fg->id = $post_id;
            }

            if($status != null)
                $save['post_status'] = $status;

            if($post_title != null)
                $save['post_title'] = sanitize_text_field($post_title);

            $save['post_content'] = Helper::wp_slash(serialize($fg->to_array()));

            if($post_id)
                $id = wp_update_post($save);
            else {
                $id = wp_insert_post($save);

                $fg->id = $id;
                $update_data = [
                    'ID'            => $id,
                    'post_content'  => Helper::wp_slash(serialize($fg->to_array()))
                ];
                wp_update_post($update_data);
            }

            return $id;
        }

        public static function has_pricing_logic($groups) {
            return Enumerable::from($groups)->any(function($group){
                return Enumerable::from($group->fields)->any(function($field) {
                    return $field->pricing_enabled();
                });
            });
        }

	    public static function process_data($data) {

			$unserialized = maybe_unserialize($data);

			if(is_array($unserialized)) {
				$fg = new FieldGroup();
				return $fg->from_array($unserialized);
			}

			return $unserialized;

	    }

    }

}