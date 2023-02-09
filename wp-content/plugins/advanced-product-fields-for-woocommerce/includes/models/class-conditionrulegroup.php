<?php

namespace SW_WAPF\Includes\Models {

    if (!defined('ABSPATH')) {
        die;
    }

        class ConditionRuleGroup
        {
            public $rules;

            public function __construct()
            {
                $this->rules = [];
            }
        }
}