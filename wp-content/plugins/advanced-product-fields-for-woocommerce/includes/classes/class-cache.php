<?php

namespace SW_WAPF\Includes\Classes {

    class Cache
    {
        protected static $cache = [];

        public static function set($key, $item) {
            self::$cache[$key] = $item;
        }

        public static function get($key) {

            if(!isset(self::$cache[$key]))
                return false;

            return self::$cache[$key];
        }

        public static function clear() {
            self::$cache = [];
        }

    }
}
