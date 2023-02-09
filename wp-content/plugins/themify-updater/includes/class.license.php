<?php

/**
 * License class for various license functionality.
 *
 * @since      1.1.4
 * @package    Themify_Updater_License
 * @author     Themify
 */
if ( !class_exists('Themify_Updater_License') ) :

class Themify_Updater_License {

	private $cache;
	private $status = array( 'code' => '', 'message' => '', 'notice' => '');
	private $credentials = array();
	private $reCheck = false;
	private $products = array();
	private $notice = false;
	private $registered_calls = array();

	const OK = 'ok';
	const LICENSE_EMPTY = 'license_empty';
	const LICENSE_NOT_FOUND = 'license_not_found';
	const LICENSE_DISABLED = 'license_disabled';
	const LICENSE_EXPIRED = 'license_expired';

	function __construct($username, $license)
	{
		$this->cache = new Themify_Updater_Cache();
		$this->credentials['username'] = Themify_Updater_utils::preg_replace($username, 'username');
		$this->credentials['license'] = Themify_Updater_utils::preg_replace($license, 'key');
		$this->hooks();
		$this->checkStatus();
	}

	private function hooks() {
		add_action('admin_enqueue_scripts', array($this, 'enqueue_script'), 15); // priority is low to ensure that enqueue_script of Themify_Updater class run's first.
		add_action('themify_verify_license', array($this, 'license_check_action'));
		add_filter('upgrader_package_options', array($this, 'register_call_listener') );
	}

	/**
	 * @return bool
	 */
	public function license_check_action(){
		$this->reCheck = true;
		$temp = $this->license_check();
		return $temp;
	}

	/**
	 * @param string $username
	 * @param string $license
	 */
	public function update_credentials($username, $license){
		$this->credentials['username'] = Themify_Updater_utils::preg_replace($username, 'username');
		$this->credentials['license'] = Themify_Updater_utils::preg_replace($license, 'key');
	}

	/**
	 * @return bool
	 */
	private function license_check( $content = false) {

		$time = 2*HOUR_IN_SECONDS;
		
		if ( empty($this->credentials['username']) || empty($this->credentials['license']) ) {
			$this->status['message'] = __('License key or username is missing.','themify-updater');
			$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: license key or username is missing. Please enter ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-license' ) ), __('Themify License', 'themify-updater'), __(' credentials.', 'themify-updater'));
			$this->status['code'] = self::LICENSE_EMPTY;
			$this->products = array();
		} else {
			if ( $content === false) {
				$request = new Themify_Updater_Requests();
				$content = $request->get( $this->apiRequestPath() );

				if ( is_wp_error( $content ) || empty( $content ) ) {
					// try alternate server
					$content2 = $request->get( $this->apiRequestPath( 'check', '', '', true ) );
					if ( ! is_wp_error( $content2 ) && ! empty( $content2 ) ) {
						$content = $content2;
						unset( $content2 );
						/* enable the proxy server for 2 hours */
						set_transient( 'tf_updater_proxy', 'y', HOUR_IN_SECONDS * 2 );
					}
				}
			}

			if ( ! is_wp_error( $content ) && ! empty( $content ) ) {
				$content = @json_decode($content, true); // suppress php warning for unknown json parser error
			}

			if ( ! is_array( $content ) ) {
				$content = array(
					'code' => 'failed_to_check',
					'message' => __( 'Themify Updater: Failed to check license key.', 'themify-updater' ) . ( is_wp_error( $content ) ? ' ' . $content->get_error_message() : '' ),
				);
			}

			$expires = isset($content['license_expires']) ? strtotime($content['license_expires']) : 0;
			if ( $content['code'] !== self::OK) {
				$this->products = array();
				$time = YEAR_IN_SECONDS;
				switch ($content['code']) {
					case 'usernameMismatch':
						$this->status['message'] = __('Username and license key doesn\'t match.','themify-updater');
						$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>.', __("Themify Updater: username and license key doesn't match. Please ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-license' ) ), __('correct it', 'themify-updater'));
						break;
					case self::LICENSE_EMPTY:
						$this->status['message'] = __('License key is missing.','themify-updater');
						$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: license key is missing. Please enter ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-license' ) ), __('Themify License', 'themify-updater'), __(' key.', 'themify-updater'));
						break;
					case self::LICENSE_NOT_FOUND:
						$this->status['message'] = __('License key is invalid','themify-updater');
						$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: ", 'themify-updater'), esc_attr( admin_url( 'index.php?page=themify-license' ) ), __('license key', 'themify-updater'), __(' is invalid. Please enter a valid license key.', 'themify-updater'));
						break;
					case self::LICENSE_EXPIRED:
						$this->status['message'] = __('Your license key is expired.','themify-updater');
						$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: your license key is expired. Please renew your membership or ", 'themify-updater'), esc_attr('https://themify.me/contact'), __('contact Themify', 'themify-updater'), __(' for more details.', 'themify-updater'));
						break;
					case self::LICENSE_DISABLED:
						$this->status['message'] = __('Your license key is disabled.','themify-updater');
						$this->status['notice'] = sprintf('%s <a href="%s" class="">%s</a>%s', __("Themify Updater: your license key is disabled. Please ", 'themify-updater'), esc_attr('https://themify.me/contact'), __('contact Themify', 'themify-updater'), __(' for more details.', 'themify-updater'));
						break;
					case 'failed_to_check':
						$this->status['message'] = $content['message'];
						$this->status['notice'] = $content['message'];
						$time = 3 * HOUR_IN_SECONDS;
						break;
					default:
						$this->status['notice'] = $content['message'];
				}
			} else {
				$this->status['message'] = $this->status['notice'] = '';
				$this->products = isset($content['products']) && is_array($content['products']) ? $content['products'] : array();
				if ( time() > $expires ) $time = 2 * DAY_IN_SECONDS; // This is due to some unknown error. we already checked for expired license. re-check a day later.
				else $time = $expires - time();
			}
			$this->status['code'] = $content['code'];
			$this->cache->set('tu_license_expires', $expires, $expires - time() ); // this is only for license with status OK. 
			$this->reNotice( true );
		}
		$this->cache->set('tu_license_error', $this->status, $time );
		$this->cache->set('tu_license_products', $this->products, $time );
		if ( $this->status['code'] === self::OK ) return true;
		return false;
	}

	private function load_products(){
		$cache = $this->cache->get('tu_license_products');
		if ( $cache !== false ){
			$this->products = $cache;
		} else {
			$this->reCheck = true;
		}
	}

	private function checkStatus() {
		$cache = $this->cache->get('tu_license_error');
		if ( $cache !== false ) {
			$this->status = $cache;
			if ( ! $this->cache->get('tu_license_expires') && $this->status['code'] === self::OK ) {
				$this->license_check();
			}
			$this->load_products();
		} else {
			$this->reCheck = true;
			$this->license_check();
		}
		
		$this->reNotice();
	}
	
	private function reNotice( $reNotice = false ) {
		$notification = Themify_Updater_Notifications::get_instance();
		if ( $this->notice !== false ) $notification->remove_notice( $this->notice );

		if ( !empty($this->status['code']) && $this->status['code'] !== self::OK ) {

			if ( $reNotice ) $notification->reAdd_notice('tu_license_err');

			$this->notice = $notification->add_notice($this->status['notice'], 'error', array(), true, 'tu_license_err');
		}
	}

	/**
	 * @return array
	 */
	public function get_products() {
		return $this->products;
	}

	/**
	 * @param string $product
	 * @return bool
	 */
	public function has_product_access($product ) {
		if ( in_array($product, $this->products) )
			return true;
		else
			return false;
	}

	/**
	 * @return bool
	 */
	public function has_error(){
		if ( empty( $this->status['notice'] ) ) return false;
		return true;
	}

	/**
	 * @return mixed
	 */
	public function get_error_message() {
		return $this->status['message'];
	}

	/**
	 * @return mixed
	 */
	public function get_error_code() {
		return $this->status['code'];
	}
	
	public function listen_calls( $response, $parsed_args, $url) {
		if ( in_array($url, $this->registered_calls) && !empty( $response['filename'] ) && !empty( $response['headers'] ) && strpos($response['headers']['content-type'], 'application/json;') > -1 ) {
			$this->license_check( file_get_contents($response['filename']) );
		} else {
	        return $response;
		}
	}
	
	public function register_call_listener( $options ) {
		if ( !empty( $this->registered_calls ) ) {
			add_filter( 'http_response', array( $this, 'listen_calls'), 10, 3 );
			remove_filter('upgrader_package_options', array($this , 'register_call_listener'));
		}
		
		return $options;
	}

	public function enqueue_script() {
		wp_localize_script('themify-upgrader', 'themify_upgrader_license', array(
			'error_message' => $this->status['notice']
		) );
   }

	/**
	 * @param string $request
	 * @param string $product
	 * @param string $version
	 * @return string
	 */
	public function apiRequestPath($request = 'check', $product = '', $version = '', $force_proxy = false ) {
		
	   if ( $this->has_error() && $request === 'get') return '';

	   $key = array();
	   $key['key'] = $this->credentials['license'];
	   $key['product'] = !empty($product) ? $product : '';
	   $key['version'] = !empty($version) ? $version : '';
	   $key['u'] = $this->credentials['username']; $key['n'] = 1;
	   $key = gzcompress(json_encode($key));
	   $key = str_replace(array('+', '/'), array('-', '_'), base64_encode($key));
	   $key = '?s=' . urlencode($key);
	   switch ($request) {
		   case 'get':
			   $action = 'get-themify';
			   break;
		   default :
			   $action = 'check-license';
	   }

		if ( $force_proxy || Themify_Updater_utils::use_proxy() ) {
			$link = Themify_Updater_utils::$proxy_uri . '/' . $key . '&action=' . $action;
		} else {
			$domain = Themify_Updater_utils::$uri;
			$path = '/member/softsale/api/';
			$link = $domain . $path . $action . $key;
		}
	   array_push( $this->registered_calls, $link);
	   return $link;
   }

	/**
	 * @param string $product
	 * @param string $version
	 * @return string
	 */
	public function get_product_link ($product, $version = '' ){
		return $this->apiRequestPath( 'get', $product, $version );
   }
}
endif;