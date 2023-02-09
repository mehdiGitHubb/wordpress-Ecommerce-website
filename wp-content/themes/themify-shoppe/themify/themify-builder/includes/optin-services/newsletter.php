<?php
defined('ABSPATH') || exit;

/**
 * Newsletter plugin
 * @link https://wordpress.org/plugins/newsletter/
 */
class Builder_Optin_Service_Newsletter extends Builder_Optin_Service {

    public function is_available() {
	return defined('NEWSLETTER_VERSION');
    }

    public function get_id() {
	return 'newsletter';
    }

    public function get_label() {
	return __('Newsletter plugin', 'themify');
    }

    public function get_options() {
	if ($this->is_available()) {
	    $lists = $this->get_lists();
	    if (empty($lists)) {
		return array(); // no options to show
	    } else {
		return array(
		    array(
			'id' => 'newsletter_list',
			'type' => 'select',
			'label' => __('List', 'themify'),
			'options' => $lists
		    ),
		);
	    }
	} else {
	    return array(
		array(
		    'type' => 'message',
		    'label'=>'',
		    'class'=>'tb_field_error_msg',
		    'comment' =>  sprintf(__('<a href="%s" target="_blank">Newsletter plugin</a> is not installed or active.', 'themify'), 'https://wordpress.org/plugins/newsletter/')
		)
	    );
	}
    }

    public function get_global_options() {
	return array();
    }

    /**
     * Get list of Lists (/wp-admin/admin.php?page=newsletter_subscription_lists)
     *
     * @return WP_Error|Array
     */
    protected function get_lists($key='') {
	$lists = Newsletter::instance()->get_lists_for_subscription();
	if (empty($lists)) {
	    return array();
	} else {
	    $_lists = array();
	    foreach ($lists as $list) {
		$_lists[$list->id] = $list->name;
	    }
	    return $_lists;
	}
    }

    /**
     * Gets data from module and validates API key
     *
     * @return bool|WP_Error
     */
    public function validate_data($fields_args) {
	return $this->is_available() ? true : new WP_Error('missing_plugin', __('Newsletter plugin is missing or is not active.', 'themify'));
    }

    /**
     * Subscribe action
     *
     * Based on NewsletterSubscription::hook_newsletter_action() method
     */
    public function subscribe($args) {
	$instance = NewsletterSubscription::instance();

	$subscription = $instance->get_default_subscription();
	$data = $subscription->data;
	$data->email = $instance->normalize_email($args['email']);
	$data->name = $instance->normalize_name($args['fname']);
	$data->surname = $instance->normalize_name($args['lname']);
	if (isset($args['newsletter_list'])) {
	    $data->lists[$args['newsletter_list']] = 1;
	}

	$result = $instance->subscribe2($subscription);
	return is_wp_error($result) ? $result : true;
    }

}
