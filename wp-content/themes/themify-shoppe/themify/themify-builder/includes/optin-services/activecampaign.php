<?php
defined('ABSPATH') || exit;

class Builder_Optin_Service_ActiveCampaign extends Builder_Optin_Service {

    public function get_id() {
	return 'activecampaign';
    }

    public function get_label() {
	return __('ActiveCampaign', 'themify');
    }

    public function get_options() {
	$lists = $this->get_lists();
	if (!is_wp_error($lists)) {
	    return array(
		array(
		    'id' => 'ac_list',
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
	$help = __('To get your API credentials go to your ActiveCampaign dashboard, you can find both API URL and Key in Settings > Developer section.');
	$label = themify_is_themify_theme() ? __('ActiveCampaign API Key' . themify_help($help), 'themify') : __('ActiveCampaign API Key', 'themify');
	return array(
	    array(
		'id' => 'activecampaign_url',
		'type' => 'text',
		'label' => __('ActiveCampaign API URL', 'themify'),
	    ),
	    array(
		'id' => 'activecampaign_key',
		'type' => 'text',
		'help' => $help,
		'label' => $label
	    ),
	);
    }

    /**
     * Get the API key from Settings page
     *
     * @return array|false
     */
    private function get_api_tokens() {
	if ($this->get('activecampaign_url') && $this->get('activecampaign_key')) {
	    return array(
		'key' => $this->get('activecampaign_key'),
		'url' => $this->get('activecampaign_url'),
	    );
	} else {
	    return false;
	}
    }

    private function request($request, $method = 'GET', $args = array()) {
	$tokens = $this->get_api_tokens();
	$url = $tokens['url'] . '/api/3/';
	$url .= $request;
	$args = wp_parse_args($args, array(
	    'method' => $method,
	    'headers' => array(
		'Api-Token' => $tokens['key']
	    ),
		));

	$response = wp_remote_request($url, $args);
	if (is_wp_error($response)) {
	    return $response;
	} else {
	    return json_decode(wp_remote_retrieve_body($response), true);
	}
    }

    /**
     * Get list of Lists (/admin/main.php?action=list)
     *
     * @return WP_Error|Array
     */
    protected function get_lists($key='') {
	$tokens = $this->get_api_tokens();
	return parent::get_lists((empty($tokens) ? false : $tokens['key']));
    }

    protected function request_list() {
	if (is_wp_error(( $data = $this->request('lists')))) {
	    return $data;
	}
	if (is_array($data) && isset($data['lists'])) {
	    $lists = array();
	    foreach ($data['lists'] as $v) {
		$lists[$v['id']] = $v['name'];
	    }
	    return $lists;
	}

	return new WP_Error('list_error', __('Error retrieving lists.', 'themify'));
    }

    /**
     * Gets data from module and validates API key
     *
     * @return bool|WP_Error
     */
    public function validate_data($fields_args) {
	return isset($fields_args['ac_list']) ? true : (new WP_Error('missing_api_key', __('No list is selected.', 'themify')));
    }

    public function clear_cache() {
	$tokens = $this->get_api_tokens();
	if (!empty($tokens)) {
	    delete_transient('tb_optin_activecampaign_' . md5($tokens['key']));
	    Themify_Storage::delete('tb_optin_activecampaign_' . $tokens['key']);
	}
    }

    /**
     *
     *
     * @doc: https://developers.activecampaign.com/v3/reference#update-list-status-for-contact
     */
    public function subscribe($args) {
	// create the contact
	$contact = $this->request('contacts', 'POST', array(
	    'body' => json_encode(array(
		'contact' => array(
		    'email' => $args['email'],
		    'firstName' => $args['fname'],
		    'lastName' => $args['lname']
		)
	    )),
		));
	if (is_wp_error($contact)) {
	    return $contact;
	} elseif (isset($contact['contact']['id'])) {
	    /* everything is good, continue on */
	    $user_id = $contact['contact']['id'];
	} elseif (isset($contact['errors'][0]['code']) && $contact['errors'][0]['code'] === 'duplicate') {
	    /**
	     * @todo: user already exists, try retrieving it
	     */
	    return new WP_Error('error', $contact['errors'][0]['title']);
	} elseif (isset($contact['errors'][0]['title'])) {
	    return new WP_Error('error', $contact['errors'][0]['title']);
	}

	$list = $this->request('contactLists', 'POST', array(
	    'body' => json_encode(array(
		'contactList' => array(
		    'list' => $args['ac_list'],
		    'contact' => $user_id,
		    'status' => 1
		)
	    )),
		));
	if (is_wp_error($list)) {
	    return $list;
	} elseif (isset($list['contacts'], $list['contactList'])) {
	    return true;
	}
    }

}
