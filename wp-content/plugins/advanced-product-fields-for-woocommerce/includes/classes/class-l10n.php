<?php

namespace SW_WAPF\Includes\Classes {

    if (!defined('ABSPATH')) {
        die;
    }

    class l10n
    {

        protected $language_folder = 'languages';

        public function __construct()
        {
            add_action('plugins_loaded', [$this, 'load_text_domain']);

	        add_filter( 'pll_get_post_types', [$this, 'add_cpt_to_polylang'], 10, 2);
        }

        public function load_text_domain()
        {
            load_plugin_textdomain(
                'advanced-product-fields-for-woocommerce',
                false,
                trailingslashit(wapf_get_setting('slug')) . trailingslashit($this->language_folder)
            );
        }

	    public function add_cpt_to_polylang($post_types, $is_settings) {

		    if ( $is_settings ) {
			    unset( $post_types['wapf_product'] );
		    } else {
			    $post_types['wapf_product'] = 'wapf_product';
		    }

		    return $post_types;
	    }
    }
}
