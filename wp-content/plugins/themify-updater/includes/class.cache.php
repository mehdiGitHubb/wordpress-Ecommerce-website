<?php

/**
 * Cache class for caching functionality.
 *
 * @since      1.1.4
 * @package    Themify_Updater_Cache
 * @author     Themify
 */
if ( !class_exists('Themify_Updater_Cache') ) :

class Themify_Updater_Cache {

	const CACHE_TYPE = 'db';
	const DBKEY = 'themify_updater_cache';
	private static $cache = array();

	function __construct () {

	    if ( defined('THEMIFY_UPDATER_DEBUG') && THEMIFY_UPDATER_DEBUG) {
	        $this->clear_all();
        }
		
		$this->load_db_cache();

		$this->clean();
	}

	private function clear_all() {
        delete_option(self::DBKEY);
    }

	private function load_db_cache() {
		$options = get_option( self::DBKEY, array());
		self::$cache['db'] = isset($options['db']) ? $options['db'] : array();
	}

    private function update_db_cache() {

        $this->clean();

        $options = get_option( self::DBKEY, array());

        $options['db'] = self::$cache[ self::CACHE_TYPE];
		
		if ( isset( $options['h'] ) ) unset($options['h']);		// remove old mix cache. created in version 1.1.4 - 1.2.0

        delete_option( self::DBKEY );
        add_option( self::DBKEY, $options);
    }

	public function get($key) {
            return isset( self::$cache[ self::CACHE_TYPE ][$key] )?self::$cache[ self::CACHE_TYPE ][$key]['value']:false;
	}

	private function _set($key , $value, $time) {
	    self::$cache[ self::CACHE_TYPE ][ $key ] = array( 'value' => $value, 'expire' => $time);
	}

	public function set($key, $value, $time = 3600) {
		if(!$key) {
			$key = Themify_Updater_utils::get_hash( $value );
		}
		$this->_set($key , $value, time() + $time);

		$this->update_db_cache();

		return $key;
	}

	function remove( $key ) {
	    if ( isset( self::$cache[ self::CACHE_TYPE ][$key] ) )
	        unset( self::$cache[ self::CACHE_TYPE ][$key] );

	    $this->update_db_cache();
	}
	
	private function clean( $cache_type = '') {
		if ( empty(self::$cache) ) return;

		if ( !empty($cache_type) ) {
            $this->_clean($cache_type);
        } else {
            foreach ( self::$cache as $type => $cache) {
                $this->_clean($type);
            }
        }
	}

    private function _clean( $type ) {
        $time=time();
        foreach ( self::$cache[ $type ] as $key => $value ) {
            if ( $time > (int)$value['expire'] ) {
                unset(self::$cache[$type][$key]);
            }
        }
    }
}
endif;
