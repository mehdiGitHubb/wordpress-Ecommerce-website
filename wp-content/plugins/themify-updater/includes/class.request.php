<?php

/**
 * Utility class of various static functions
 *
 * @since      1.1.4
 * @package    Themify_Updater_Requests
 * @author     Themify
 */
if ( !class_exists('Themify_Updater_Requests') ) :
	
class Themify_Updater_Requests {

    private $cache = false;

	function __construct () {
		$this->cache = new Themify_Updater_Cache();
	}

	function pre( $string ) {
        $temp = explode('?', $string);
        $key = Themify_Updater_utils::get_hash( $temp[0] );

        return $key;
    }

    /**
     * @param $url
     * @return WP_Error|string
     */
    function get($url ) {
        $key = $this->pre( $url );
        $cache = $this->cache->get($key);

        if ( $cache !== false ) return '';

        $content = wp_remote_get( $url );
		if ( is_wp_error( $content ) ) {
			return $content;
		}

        if ( ! isset($content['response']) || !isset($content['response']['code']) || $content['response']['code'] != 200) {
            $content = array('body' => '');
            $this->cache->set($key, $content, 2 * HOUR_IN_SECONDS);
        }

        return $content['body'];
	}

    /**
     * @param $url
     * @param string $header
     * @return string
     */
    function head($url, $header = 'all' ) {
        $key = $this->pre( $url );
        $cache = $this->cache->get($key);

        if ( $cache !== false ) return '';

        $content = wp_remote_head( $url );

        if( is_wp_error( $content ) || !isset($content['response']) || !isset($content['response']['code']) || $content['response']['code'] != 200) {
            $content = array('headers' => '');
            $this->cache->set($key, $content, 2 * HOUR_IN_SECONDS);
        }

        if ( empty( $content['headers'] ) ) return '';

        if ($header === 'all') {
            return $content['headers']->getAll();
        } else {
            $temp = $content['headers']->offsetGet($header);
            if (  $temp !== null ) {
                return $temp;
            }
        }
        return '';
	}
}
endif;