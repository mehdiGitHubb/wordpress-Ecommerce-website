<?php
defined('ABSPATH') || exit;

class Builder_Optin_Service_MailChimp extends Builder_Optin_Service {

    public function get_id() {
	return 'mailchimp';
    }

    public function get_label() {
	return __('MailChimp', 'themify');
    }

    public function get_options() {
	$lists = $this->get_lists();
	if (!is_wp_error($lists)) {
	    return array(
		array(
		    'id' => 'mailchimp_list',
		    'type' => 'select',
		    'label' => __('List', 'themify'),
		    'options' => $lists
		),
		array(
		    'id' => 'mailchimp_db_opt',
		    'type' => 'toggle_switch',
		    'label' => __('Double opt-in', 'themify'),
		    'options' => array(
			'on' => array('name' => 'on', 'value' => 'en'),
			'off' => array('name' => '', 'value' => 'dis')
		    )
		)
	    );
	} else {
	    return array(
		array(
		    'type' => 'message',
		    'label'=>'',
		    'class'=>'tb_field_error_msg',
		    'comment' =>  $lists->get_error_message()
		)   
	    );
	}
    }

    public function get_global_options() {
	return array(
	    array(
		'id' => 'mailchimp_key',
		'type' => 'text',
		'label' => __('MailChimp API Key', 'themify'),
		'description' => sprintf(__('<a href="%s" target="_blank">Get an API key</a>', 'themify'), 'https://admin.mailchimp.com/account/api/'),
	    ),
	);
    }

    private function request($request, $method = 'GET', $args = array()) {
	$api_key = $this->get_api_key();
	$api_key_pieces = explode('-', $api_key);
	$server = $api_key_pieces[1];
	$url = sprintf('https://%s.api.mailchimp.com/3.0/', $server);
	$url .= $request;
	$args = wp_parse_args($args, array(
	    'method' => $method,
	    'headers' => array(
		'Authorization' => 'Basic ' . base64_encode('key' . ':' . $api_key)
	    ),
		));

	$response = wp_remote_request($url, $args);
	if (is_wp_error($response)) {
	    return $response;
	} else {
	    return json_decode(wp_remote_retrieve_body($response), true);
	}
    }

    protected function request_list() {
	if (is_wp_error(( $data = $this->request('lists')))) {
	    return $data;
	}
	if (is_array($data) && isset($data['lists'])) {
	    $list = array();
	    foreach ($data['lists'] as $v) {
		$list[$v['id']] = $v['name'];
	    }
	    return $list;
	}

	return new WP_Error('list_error', __('Error retrieving lists.', 'themify'));
    }

    /**
     * Gets data from module and validates API key
     *
     * @return bool|WP_Error
     */
    public function validate_data($fields_args) {
	if (isset($fields_args['mailchimp_list'])) {
	    $mc_lists = $this->get_lists();
	    if (is_wp_error($mc_lists)) {
		return $mc_lists;
	    }
	    if (isset($mc_lists[$fields_args['mailchimp_list']])) {
		return true;
	    }
	    return new WP_Error('list_not_found', __('Selected list not found.', 'themify'));
	} else {
	    return new WP_Error('missing_api_key', __('No list is selected.', 'themify'));
	}
    }

    /**
     * Subscribe action
     *
     * @doc https://developer.mailchimp.com/documentation/mailchimp/guides/manage-subscribers-with-the-mailchimp-api/#subscribe-an-address
     */
    public function subscribe($args) {
	$response = $this->request(sprintf('lists/%s/members', $args['mailchimp_list']), 'POST', array(
	    'body' => json_encode(array(
		'email_address' => $args['email'],
		'status' => isset($args['mailchimp_db_opt']) && $args['mailchimp_db_opt'] === 'on' ? 'pending' : 'subscribed',
		'merge_fields' => array(
		    'FNAME' => $args['fname'],
		    'LNAME' => $args['lname']
		),
	    ))
		));
	if (is_wp_error($response)) {
	    return $response;
	} elseif (isset($response['status'])) {
	    if ('pending' === $response['status'] || 'subscribed' === $response['status']
		    /* this user is already subscribed, no need to show any errors */ || ( 400 === $response['status'] && $response['title'] === 'Member Exists' )
	    ) {
		return true;
	    } elseif (isset($response['errors'])) {
		return new WP_Error('error', $response['errors'][0]->message);
	    }
	}
    }

}
