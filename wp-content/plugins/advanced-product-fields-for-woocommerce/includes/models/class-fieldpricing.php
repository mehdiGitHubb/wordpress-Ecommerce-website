<?php

namespace SW_WAPF\Includes\Models {

    if (!defined('ABSPATH')) {
        die;
    }

    class FieldPricing
    {
        public $enabled;

        public $amount;

        public $type;

        public function __construct()
        {
            $this->enabled = false;
            $this->type = 'fixed';
            $this->amount = 0;
        }
    }
}