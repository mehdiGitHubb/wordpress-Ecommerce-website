<?php

defined( 'ABSPATH' ) || exit;

/*DON`T MODIFY THIS FILE IT CAN BREAK ALL SITES. USE THEMIFY SETTINGS TO DISABLE IT*/

/*Themify start*/
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']==='GET' && !function_exists('themify_cache_loaded') && !empty($_SERVER['REQUEST_URI']) && empty( preg_grep( '/wordpress_logged_in_/', array_keys( $_COOKIE ) ) )){
	
    if(!((defined( 'DOING_AJAX' ) && DOING_AJAX) || (defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST) || (defined( 'DOING_CRON' ) && DOING_CRON) || (defined( 'REST_REQUEST' ) && REST_REQUEST) || (defined('APP_REQUEST') && APP_REQUEST) || (defined( 'SHORTINIT' ) && SHORTINIT) || strpos( $_SERVER['REQUEST_URI'], 'robots.txt' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'wp-admin' )!==false || strpos( $_SERVER['REQUEST_URI'], 'wp-login' )!==false || strpos( $_SERVER['REQUEST_URI'], 'wp-json' )!==false)){
		function themify_cache_loaded(){
			$dir = rtrim(WP_CONTENT_DIR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'tf_cache_config'.DIRECTORY_SEPARATOR.'site';
			if(is_multisite()){
				$dir.='-'.get_current_blog_id();
			}
			$dir.='.php';
			if(is_file($dir)){
			    include_once $dir;
			    if(defined('TF_CACHE_FW') && TF_CACHE_FW && TF_CACHE_FW!=='#TF_CACHE_FW#'){
				$dir = rtrim(TF_CACHE_FW,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'class-themify-cache.php';
				if(is_file($dir)){
				    include_once $dir;
				    TFCache::run();
				}
			    }
			}
		}
		if(is_multisite()){
                    add_action('ms_loaded','themify_cache_loaded',5);
		}
		else{
                    add_action('init','themify_cache_loaded',0);
		}
    }
}
/*Themify End*/