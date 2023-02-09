<?php

/**
 * Utility class of various static functions
 *
 * @since      1.1.4
 * @package    Themify_Updater_utils
 * @author     Themify
 */

if ( !class_exists('Themify_Updater_utils') ) :

class Themify_Updater_utils {
	public static $uri = 'https://themify.me';
	public static $proxy_uri = 'https://themify.org/updater-proxy';

    public static function get_hash( $string ) {
		return hash( 'crc32', $string, false);
	}
	
	public static function rrmdir( $dir ) {
		if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
                if ($file != "." && $file != "..") self::rrmdir("$dir/$file");
            rmdir($dir);
        }
        else if (file_exists($dir)) unlink($dir);
	}
	
	public static function rcopy( $src, $dst ) {
		if (file_exists ( $dst ))
            self::rrmdir ( $dst );
        if (is_dir ( $src )) {
            mkdir ( $dst );
            $files = scandir ( $src );
            foreach ( $files as $file )
                if ($file != "." && $file != "..")
                    self::rcopy ( "$src/$file", "$dst/$file" );
        } else if (file_exists ( $src ))
            copy ( $src, $dst );
	}
	
	public static function get_previous_versions( $latest_version, $back_limit = 5, $return_html = true, $last_version = '0.0.1', $include_latest = false) {
		$html = '';
        
        $versions = $include_latest ? array($latest_version) : array();
        $i = count($versions);
        while ( $i < $back_limit ) {
            if ( $i === 0 && version_compare($latest_version, $last_version, '>')) {
                $versions[$i] = self::previous_version( $latest_version );
            }
            elseif ( ! empty( $versions[$i-1] ) && version_compare( $versions[$i-1], $last_version, '>') ) {
                $versions[$i] = self::previous_version( $versions[$i-1] );
            }
            else {
                break;
            }
            ++$i;
        }

        if ( $return_html ) {
			foreach ( $versions as $version ) {
				$html .= '<option value="'. $version .'">'. $version .'</option>';
			}
			return $html;
		}
		
		return $versions;
	}
	
	public static function previous_version( $version ) {
		$back_version = '';
        $parts = explode( '.', $version );
        if ( sizeof( $parts ) === 3 ) {
            if ( (int) $parts[2] > 0 ) {
                $parts[2]--;
            }
            elseif ( (int) $parts[1] > 0 ) {
                $parts[2] = '9';
                $parts[1]--;
            }
            elseif ( (int) $parts[0] > 1 ) {
                $parts[2] = '9';
                $parts[1] = '9';
                $parts[0]--;
            }
            else {
                $parts = NULL;
            }
        }
        if ( $parts ) {
            $back_version = implode( '.', $parts );
        }
        return $back_version;
	}
	
	public static function next_version( $version ) {
		$next_version = '';
        $parts = explode( '.', $version );
        if ( sizeof( $parts ) === 3 ) {
            if ( (int) $parts[2] < 9 ) {
                $parts[2]++;
            }
            elseif ( (int) $parts[1] < 9 ) {
                $parts[2] = '0';
                $parts[1]++;
            }
            else {
                $parts[2] = '0';
                $parts[1] = '0';
                $parts[0]++;
            }
        }
        if ( $parts ) {
            $next_version = implode( '.', $parts );
        }
        return $next_version;
	}
	
	public static function enque_min( $url, $check = false ) {
            if(function_exists('themify_enque')){
                return themify_enque($url,$check);
            }
            static $is_disabled = null;
            if ( $is_disabled === null ) {
                $is_disabled =( defined( 'WP_DEBUG' ) &&  WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'THEMIFY_DEBUG' ) && THEMIFY_DEBUG );
            }
            if( $is_disabled ) {
                return $check ? false : $url;
            }
            $f = pathinfo( $url );
            $return = 0;
            if ( strpos( $f['filename'], '.min.', 2 ) === false ) {
                $absolute = str_replace( WP_CONTENT_URL, '', $f['dirname'] );
                $name = $f['filename'] . '.min.' . $f['extension'];
                if ( is_file( trailingslashit( WP_CONTENT_DIR ) . trailingslashit( $absolute ) . $name ) ) {
                    if( $check ) {
                        $return = 1;
                    } else {
                        $url = trailingslashit( $f['dirname'] ) . $name;
                    }
                }
            }

            return $check ? $return : $url;
	}
	
	public static function wp_get_themes( $args = array() ) {
        static $themes = false;

        if ( is_array($themes) ) return $themes;

        if ( defined('THEMIFY_UPDATER_NETWORK_ENABLED') && !THEMIFY_UPDATER_NETWORK_ENABLED ) {
            $themes = wp_get_themes();
            return $themes;
        }

		$theme_names = WP_Theme::get_allowed_on_network();
        $themes = array();
        foreach ($theme_names as $key => $name) {
            $temp = wp_get_theme( $key );
            if ($temp->exists()) {
                $themes[$key] = $temp;
            }
        }

        return $themes;
	}

	public static function is_admin () {

        if ( is_admin() && ! wp_doing_ajax() ) {
            return true;
        }
        return false;
    }

    public static function preg_replace($text, $which, $with = ''){
        $condition = !empty($with) ? '' : '^';
        switch ($which) {
            case 'key':
                $text = preg_replace("/[".$condition."0-9a-zA-Z]/", $with, $text);
                break;
            case 'username':
                $text = preg_replace("/[".$condition."0-9a-zA-Z_-]/", $with, $text);
                break;
        }
        return $text;
    }

	/**
	 * Returns true only if proxy server is enabled, this happens only if Themify_Updater_utils::$uri server fails
	 *
	 * @return bool
	 */
	public static function use_proxy() {
		static $enabled = null;
		if ( $enabled === null ) {
			$enabled = get_transient( 'tf_updater_proxy' ) === 'y';
		}

		return $enabled;
	}

	public static function get_previous_versions_from_changelogs( $name, $count = 5 ) {
		$url = sprintf( 'https://themify.me/changelogs/%s.txt', $name );
		$request = new Themify_Updater_Requests();
        $response = $request->get( $url );
		if ( ! is_wp_error( $response ) ) {
			preg_match_all( '/[\d\.]+ - version ([\d\.]+)/', $response, $matches );
			if ( is_array( $matches[1] ) ) {
				return array_slice( $matches[1], 0, $count );
			}
		}
		return [];
	}
}
endif;
