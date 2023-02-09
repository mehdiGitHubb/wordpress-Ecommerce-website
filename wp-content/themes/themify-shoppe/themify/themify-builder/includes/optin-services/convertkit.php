<?php
defined('ABSPATH') || exit;

class Builder_Optin_Service_ConvertKit extends Builder_Optin_Service {

    /**
     * ConvertKit API URL
     *
     * @var string
     */
    const API_URL_BASE = 'https://api.convertkit.com/v3/';

    public function get_id() {
	return 'convertkit';
    }

    public function get_label() {
	return __('ConvertKit', 'themify');
    }

    public function get_options() {
	$lists = $this->get_lists();
	if (!is_wp_error($lists)) {
	    return array(
		array(
		    'id' => 'ck_form',
		    'type' => 'select',
		    'label' => __('Form', 'themify'),
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
		'id' => 'convertkit_key',
		'type' => 'text',
		'label' => __('ConvertKit API Key', 'themify'),
		'description' => sprintf(__('<a href="%s" target="_blank">Get an API key</a>', 'themify'), 'https://app.convertkit.com/account/edit'),
	    ),
	);
    }

    private function request($request, $method = 'GET', $args = array()) {
	$args = wp_parse_args($args, array(
	    'api_key' => $this->get_api_key(),
		));
	$url = self::API_URL_BASE . $request . '?' . http_build_query($args);
	$results = wp_remote_request($url, array('method' => $method));

	if (!is_wp_error($results)) {
	    if (200 == wp_remote_retrieve_response_code($results)) {
		return json_decode(wp_remote_retrieve_body($results), true);
	    } else {
		$body = wp_remote_retrieve_body($results);
		if (is_string($body) && is_object($json = json_decode($body, true))) {
		    $body = (array) $json;
		}

		if (!empty($body['error'])) {
		    return new WP_Error('error', $body['error']);
		} elseif (!empty($body['message'])) {
		    return new WP_Error('error', $body['message']);
		} else {
		    return new WP_Error('error', sprintf(__('Error code: %s', 'themify'), wp_remote_retrieve_response_code($results)));
		}
	    }
	} else {
	    return $results;
	}
    }

    protected function request_list() {
	if (is_wp_error(( $data = $this->request('forms')))) {
	    return $data;
	}
	if (is_array($data['forms']) && !empty($data['forms'])) {
	    $list = array();
	    foreach ($data['forms'] as $v) {
		$list[$v['id']] = $v['name'];
	    }
	    return $list;
	}

	return new WP_Error('list_error', __('Error retrieving forms.', 'themify'));
    }

    /**
     * Gets data from module and validates API key
     *
     * @return bool|WP_Error
     */
    public function validate_data($fields_args) {
	return isset($fields_args['ck_form']) ? true : (new WP_Error('missing_api_key', __('No form is selected.', 'themify')));
    }

    /**
     *
     * @doc https://developers.convertkit.com/#forms
     */
    public function subscribe($args) {
	return $this->request(sprintf('forms/%s/subscribe', $args['ck_form']), 'POST', array(
		    'email' => $args['email'],
		    'first_name' => $args['fname'],
		));
    }

}
