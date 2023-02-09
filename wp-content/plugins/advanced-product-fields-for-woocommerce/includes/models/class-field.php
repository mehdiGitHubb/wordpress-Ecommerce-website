<?php

namespace SW_WAPF\Includes\Models {

    use SW_WAPF\Includes\Classes\Enumerable;

    if (!defined('ABSPATH')) {
        die;
    }

    class Field
    {

        public $id;

        public $key;

        public $label;

        public $description;

        public $type;

        public $required;

        public $options;

        public $conditionals;

        public $class;

        public $width;

        public $pricing;

        public function __construct()
        {
            $this->label = '';
            $this->options = [];
            $this->conditionals = [];
            $this->pricing = new FieldPricing();
        }

	    public function from_array($a) {

		    $this->id = $a['id'];
		    $this->label = $a['label'];
		    $this->description = $a['description'];
		    $this->type = $a['type'];
		    $this->required = $a['required'];
		    $this->class = $a['class'];
		    $this->width = $a['width'];
		    $this->options = $a['options'];
		    $p = new FieldPricing();
		    $p->type = $a['pricing']['type'];
		    $p->enabled = $a['pricing']['enabled'];
		    $p->amount = $a['pricing']['amount'];
		    $this->pricing = $p;

		    foreach($a['conditionals'] as $c) {
			    $cond = new Conditional();
			    foreach($c['rules'] as $r) {
				    $rule = new ConditionalRule();
				    $rule->condition = $r['condition'];
				    $rule->value = $r['value'];
				    $rule->field = $r['field'];
				    $cond->rules[] = $rule;
			    }
			    $this->conditionals[] = $cond;
		    }

		    return $this;

	    }

	    public function to_array() {
		    $a = [
			    'id'                => $this->id,
			    'label'             => $this->label,
			    'description'       => $this->description,
			    'type'              => $this->type,
			    'required'          => $this->required,
			    'class'             => $this->class,
			    'width'             => $this->width,
			    'options'           => $this->options,
			    'conditionals'      => [],
			    'pricing'           => [
				    'type'          => $this->pricing->type,
				    'amount'        => $this->pricing->amount,
				    'enabled'       => $this->pricing->enabled
			    ]
		    ];

		    foreach ($this->conditionals as $conditional) {
			    $c = ['rules' => [] ];

			    foreach ($conditional->rules as $rule) {
				    $r = [
					    'condition' => $rule->condition,
					    'value'     => $rule->value,
					    'field'     => $rule->field
				    ];
				    $c['rules'][] = $r;
			    }

			    $a['conditionals'][] = $c;

		    }

		    return $a;

	    }

        public function is_choice_field() {
            return in_array( $this->type, ['select','checkboxes','radio'] );
        }

	    public function has_conditionals() {
		    return count($this->conditionals) > 0;
	    }

        public function pricing_enabled() {

            if($this->is_choice_field() && !empty($this->options['choices']))
                return Enumerable::from($this->options['choices'])->any(function($choice){
                    return isset($choice['pricing_type']) && $choice['pricing_type'] !== 'none';
                });

            return $this->pricing->enabled;

        }

    }
}