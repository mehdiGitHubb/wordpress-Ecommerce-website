<?php
defined('ABSPATH') || exit;

class Builder_Optin_Service_MailerLite extends Builder_Optin_Service {

    /**
     * API URL
     *
     * @var string
     */
    const API_URL_BASE = 'https://api.mailerlite.com/api/v2/';

    public function get_id() {
	return 'mailerlite';
    }

    public function get_label() {
	return __('MailerLite', 'themify');
    }

    public function get_options() {
	$lists = $this->get_lists();
	if (!is_wp_error($lists)) {
	    return array(
		array(
		    'id' => 'ml_form',
		    'type' => 'select',
		    'label' => __('Groups', 'themify'),
		    'options' => $lists,
		),
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
		'id' => 'mailerlite_key',
		'type' => 'text',
		'label' => __('MailerLite API Key', 'themify'),
		'description' => sprintf(__('<a href="%s" target="_blank">Get an API key</a>', 'themify'), 'https://app.mailerlite.com/integrations/api/'),
	    ),
	);
    }

    private function request($request = 'groups', $method = 'GET', $args = array()) {
	$args = wp_parse_args($args, array(
	    'method' => $method,
	    'headers' => array(
		'X-MailerLite-ApiKey' => $this->get_api_key(),
		'Content-Type' => 'application/json',
	    ),
		));
	$url = self::API_URL_BASE . $request;
	$results = wp_remote_request($url, $args);

	if (!is_wp_error($results)) {
	    $response = json_decode(wp_remote_retrieve_body($results), true);
	    if (empty($response)) {
		return new WP_Error('empty_response', __('Empty response.', 'themify'));
	    }
	    if (isset($response['error'])) {
		return new WP_Error('error-' . $response['error']['code'], $response['error']['message']);
	    }

	    // all good!
	    return $response;
	} else {
	    return $results;
	}
    }

    protected function request_list() {
	if (is_wp_error(( $data = $this->request('groups')))) {
	    return $data;
	}
	if (!empty($data) && is_array($data)) {
	    $lists = array();
	    foreach ($data as $v) {
		$lists[$v['id']] = $v['name'];
	    }
	    return $lists;
	}

	return new WP_Error('list_error', __('Error retrieving forms.', 'themify'));
    }

    /**
     * Gets data from module and validates API key
     *
     * @return bool|WP_Error
     */
    public function validate_data($fields_args) {
	return isset($fields_args['ml_form']) ? true : (new WP_Error('missing_campaign', __('No campaign is selected.', 'themify')));
    }

    /**
     *
     * @doc https://developers.mailerlite.com/reference#add-single-subscriber
     */
    public function subscribe($args) {
	return $this->request(sprintf('groups/%s/subscribers', $args['ml_form']), 'POST', array(
		    'body' => json_encode(array(
			'email' => $args['email'],
			'name' => sprintf('%s %s', $args['fname'], $args['lname']),
		    )),
		));
    }

}
