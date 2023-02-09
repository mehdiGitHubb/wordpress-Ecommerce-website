<?php
/**
 * The file that defines File System class
 *
 * Themify_Filesystem class provide instance of Filesystem Api to do some file operation.
 * Based on WP_Filesystem the class method will remain same.
 * 
 *
 * @package    Themify
 * @subpackage Filesystem
 */

if ( ! class_exists( 'Themify_Filesystem' ) ) {

	/**
	 * The Themify_Filesystem class.
	 *
	 * This is used to initialize WP_Filesytem Api instance
	 * check for filesytem method and return correct filesystem method
	 *
	 * @package    Themify
	 * @subpackage Filesystem
	 * @author     Themify
	 */
	class Themify_Filesystem {

		/**
		 * Instance of WP_Filesytem api class.
		 * 
		 * @access public
		 * @var $execute Store the instance of WP_Filesystem class being used.
		 */
		public $execute = null;

		/**
		 * Class constructor.
		 * 
		 * @access public
		 */
		private function __construct() {
			$this->initialize();
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {
		    static $instance=null;
		    if($instance===null){
			$instance=new self;
		    }
		    return $instance;
		}

		/**
		 * Initialize filesystem method.
		 */
		private function initialize() {
			if(!defined( 'FS_METHOD' )){
			    define( 'FS_METHOD','direct');
			}
			// Load WP Filesystem
			if ( ! function_exists('WP_Filesystem') ) {
			    require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			global $wp_filesystem;
			if(is_a($wp_filesystem,'WP_Filesystem_Direct')){
			    $this->execute = $wp_filesystem;
			}
			else{
			     $this->execute = self::load_direct();
			}
		}

		/**
		 * Initialize Filesystem direct method.
		 */
		private static function load_direct() {
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
			return new WP_Filesystem_Direct( array() );
		}
		
		public static function get_file_content($file,$check=false){
		    static $site_url=null;
			static $content_url=null;
		    if($site_url===null){
				$site_url=rtrim(site_url(),'/').'/';
		    }
                    if($content_url===null){
                            $content_url=rtrim(content_url(),'/');
                    }
                    $upload_dir = themify_upload_dir();
		    if(strpos($file,$site_url)!==false || strpos($file,$upload_dir['baseurl'])!==false || strpos($file,$content_url)!==false){
					$file = urldecode( $file );
                        if(strpos($file,$upload_dir['baseurl'])!==false){
                                $dir = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file );
                        }
                        elseif(strpos($file,'/'.WPINC.'/')!==false || strpos($file,'/wp-admin/')!==false){
                                $dir = str_replace($site_url,ABSPATH,$file);
                        }
                        elseif(strpos($file,$site_url)!==false){
                                $dir = str_replace($site_url,rtrim(dirname(WP_CONTENT_DIR),'/').'/',$file);
                        }
                        else{
                                $dir = str_replace($content_url,rtrim(WP_CONTENT_DIR,'/'),$file);
                        }
                        unset($upload_dir);
                        $dir =strtok($dir,'?');
		    }
		    else{
                        return null;
		    }
		    if($check===true){
				return self::is_readable($dir);
		    }
		    $data=self::get_contents($dir);
		    return !empty($data)?$data:null;
		}
		
		public static function is_dir($dir){
		    return is_dir($dir);
		}
		
		public static function is_file($file){
		    return is_file($file);
		}
		
		public static function is_readable($dir){
		    return is_readable($dir);
		}
		
		public static function is_writable($dir){
		    return is_writable($dir);
		}
		
		public static function exists($file){
		    return file_exists($file);
		}
		
		public static function size($file){
		    return filesize($file);
		}
		
		public static function delete($dir,$type=false){
		    try{
			set_error_handler(array(__CLASS__, 'errorHandler'), E_WARNING);
			$removed=self::remove($dir,$type);
			restore_error_handler();
			if($removed){
			    return true;
			}
			return false;
		    }
		    catch(ErrorException $e){
			restore_error_handler();
			return true;
		    }
		}
		
		private static function remove($dir,$type=false){
		    if($type!=='f' && self::is_dir($dir)){
			    $dirHandle = opendir($dir);
			    if(empty($dirHandle)){
				    return false;
			    }
			    $sep=DIRECTORY_SEPARATOR;
			    while(false!==($f = readdir($dirHandle))){
				if($f!=='.' && $f!=='..'){
				    $item = rtrim($dir,$sep) . $sep . $f;
				    if(self::is_dir($item)){
					self::remove($item);
				    }
				    elseif(self::is_file($item) || is_link($item)){
					unlink($item);
				    }
				}
			    }
			closedir($dirHandle);
			$dirHandle=null;
			return rmdir($dir);
		    }
		    elseif(self::is_file($dir) || is_link($dir)){
			    return unlink($dir);
		    }
		    return true;
		}
		
		public static function put_contents($file,$contents, $mode=false){
		    $instance=self::get_instance();
		    if($mode===false){
			$mode=FS_CHMOD_FILE;
		    }
		    return $instance->execute->put_contents($file,$contents, $mode);
		}
		
		public static function get_contents($file){
		    return self::is_file($file)?file_get_contents($file):'';
		}
		
		public static function errorHandler($errno, $errstr, $errfile, $errline){
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		}
		
		public static function mkdir($dir,$r=false,$perm=0777){
		    if(!self::is_dir($dir)){
			try{
			    set_error_handler(array(__CLASS__, 'errorHandler'), E_WARNING);
			    $check=mkdir($dir,$perm,$r);
			    restore_error_handler();
			    if($check){
				return true;
			    }
			    return false;
			}
			catch(ErrorException $e){
			    restore_error_handler();
			    return self::is_dir($dir);
			}
		    }
		    return true;
		}
		
		public static function rename($from,$to){
		    try{
			set_error_handler(array(__CLASS__, 'errorHandler'), E_WARNING);
			$check=rename($from,$to);
			restore_error_handler();
			if($check){
			    return true;
			}
			return false;
		    }
		    catch(ErrorException $e){
			restore_error_handler();
			return self::is_dir($to) && !self::is_dir($from);
		    }
		}
	}
	
}