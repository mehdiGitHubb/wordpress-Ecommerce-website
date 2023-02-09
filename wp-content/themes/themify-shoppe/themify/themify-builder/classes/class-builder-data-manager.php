<?php
/**
 * Builder Data Manager API
 *
 * ThemifyBuilder_Data_Manager class provide API
 * to get Builder Data, Save Builder Data to Database.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Builder Data Manager class.
 *
 * This class provide API to get and update builder data.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class ThemifyBuilder_Data_Manager {

	/**
	 * Builder Meta Key
	 * 
	 * @access public
	 * @const string META_KEY
	 */
	 
	const OLD_META_KEY = '_themify_builder_settings';
	
	const META_KEY = '_themify_builder_settings_json';



	/**
	 * Constructor
	 * 
	 * @access public
	 */
	public static function init() {
		add_action( 'import_post_meta', array( __CLASS__, 'import_post_meta' ), 10, 3 );
		add_action( 'wp_ajax_tb_dismiss_data_updater_notice', array(__CLASS__, 'dismiss_data_updater_notice'), 10);
	}


	/**
	 * Get Builder Data
	 * 
	 * @access public
	 * @param int $post_id 
	 * @return array
	 */
	public static function get_data( $post_id,$plain_return=false ) {
		$data = get_post_meta( $post_id, self::META_KEY, true );	
		if(!empty($data)){	
		    if($plain_return!==true){
			$isArray = is_array($data);
			if($isArray===false){
			    $data =json_decode( $data, true );
			}
			if($isArray===true || json_last_error()!==JSON_ERROR_NONE || (!empty($data) && isset($data[0]) && !isset($data[0]['element_id']))){//is old data?
			    $res=self::update_builder_meta($post_id,$data);
			    $data=json_decode($res['builder_data'],true);
			}
		    }
		}
		else{
		    $data = get_post_meta( $post_id, self::OLD_META_KEY, true);
		    if(!empty($data)){
			$res=self::update_builder_meta($post_id,stripslashes_deep(maybe_unserialize( $data )));
			$data=$plain_return!==true?json_decode($res['builder_data'],true):$res['builder_data'];
		    } 
		}
		$data = !empty($data)?$data:($plain_return===true?'':array());

		return apply_filters( 'themify_builder_data', $data, $post_id );
	}
	
	/**
	 * Save Builder Data.
	 * 
	 * @access public
	 * @param string|array $builder_data 
	 * @param int $post_id 
	 * @param string $action 
	 */
	public static function save_data($builder_data, $post_id, $action = 'frontend') {
	    /* save the data in json format */
	    $result=self::update_builder_meta($post_id,$builder_data);
	    unset($builder_data);
	    if(!empty($result['mid']) && !wp_is_post_revision($post_id)){     
		if ( 'backend' === $action ) {
		    $plain_text = self::_get_all_builder_text_content( json_decode($result['builder_data'],true) );
		    if ( ! empty( $plain_text ) ){ 
			    $result['static_content'] = self::add_static_content_wrapper( $plain_text );
		    }
		    unset($plain_text);
		}
		if(class_exists('Themify_Builder_Revisions')){
		    Themify_Builder_Revisions::create_revision($post_id,$result['builder_data'],$action);
		}  
		// Save used GS
		Themify_Global_Styles::save_used_global_styles($result['builder_data'], $post_id);
		
		// update the post modified date time, to indicate the post has been modified
		self::update_post($post_id,array('post_modified'=>current_time('mysql'),'post_modified_gmt'=>current_time('mysql', 1)));
		/**
		 * Fires After Builder Saved.
		 * @param int $post_id
		 */		
		do_action( 'themify_builder_save_data', $post_id );
	    }     
	    return $result;
	}
	
	
	public static function json_escape(array $arr){
		foreach($arr as $k=>$v){
		    if(is_string($v)){
			if(trim($v)===''){
			    unset($arr[$k]);
			}
			elseif(isset($v[0]) && ($v[0]==='{' || $v[0]==='[')){//is json?
			    $data=$v;
			    $json=json_decode($data,true);
			    if(json_last_error()!==JSON_ERROR_NONE){
				$data=stripslashes_deep($data);
				$json=json_decode($data,true);
				if(json_last_error()!==JSON_ERROR_NONE){
				    $data=stripslashes_deep($data);
				    $json=json_decode($data,true);
				    if(json_last_error()!==JSON_ERROR_NONE){
					$data=stripslashes_deep($data);
					$json=json_decode($data,true);
				    }
				}
			    }
			    if($k==='background_image-css'){
				unset($arr[$k]);
			    }
			    else{
				$arr[$k]=is_array($json)?self::json_escape($json):$v;
			    }
			}
		    }
		    elseif(is_array($v)){
			$arr[$k]=self::json_escape($v);
		    }
		    elseif($v===null){
			unset($arr[$k]);
		    }
		}
		return $arr;
	}
	
	/**
	 * Remove unicode sequences back to original character
	 * 
	 * @access public
	 * @param array $data 
	 * @return json
	 */
	public static function json_remove_unicode( $data ) {
	    return json_encode( $data, JSON_UNESCAPED_UNICODE );
	}

	/**
	 * fix importing Builder contents using WP_Import
	 * 
	 * @access public
	 */
	public static function import_post_meta( $post_id, $key, $value ) {
	    if( $key === self::META_KEY) {
		self::update_builder_meta($post_id, $value);
	    }
	}
	
	

	/**
	 * Check if content has static content
	 * @param string $content 
	 */
	public static function has_static_content( $content ) {
		$start=strpos($content,'<!--themify_builder_static-->');
		if($start===false){
			return false;
		}
		$end=strpos($content,'<!--/themify_builder_static-->');
		return ($end!==false && ($start<$end));
	}


	/**
	 * Update static content string in the string.
	 * 
	 * @param string $replace_string 
	 * @param string $content 
	 * @param bool $first_instance True: replace only the first instance of Builder placeholder and remove the rest; False: replace all instances with $replace_string
	 * @return string
	 */
	public static function update_static_content_string( $replace_string, $content) {
		if ( self::has_static_content( $content ) ) {
						
			$arr = explode('<!--themify_builder_static-->',$content);
			unset($content);
			$html='';
			foreach($arr as $v){
				if($v!=='' && strpos($v,'<!--/themify_builder_static-->')!==false){
					$tmp = explode('<!--/themify_builder_static-->',$v);
					$html.=$replace_string.$tmp[1];
					if(isset($tmp[2])){
						$html.=$tmp[2];
					}
				}
				else{
					$html.=$v;
				}
			}
			unset($arr,$replace_string);
			return self::remove_empty_p($html);
		}
		return $content;
	}


	/**
	 * Add static content wrapper
	 * @param string $string 
	 * @return string
	 */
	public static function add_static_content_wrapper( $string ) {
		return '<!--themify_builder_static-->' . $string . '<!--/themify_builder_static-->';
	}

	/**
	 * Save the builder plain content into post_content
	 * 
	 * @param int $post_id
	 * @param mixed $data 
	 */
	private static function save_builder_text_only( $post_id, $data ) {
		if(wp_is_post_revision( $post_id )){
		    return false;
		}
		$post = get_post($post_id); 
		if(!empty($post)){
		    if(!is_array($data)){
			$data=json_decode($data,true);
		    }
		    $text_only =!empty($data)?self::_get_all_builder_text_content($data ):array();
		    if(empty($text_only)){
			$text_only='';
		    }
		    $post_content = $post->post_content;
		    if ( self::has_static_content( $post_content ) ) {
			$post_content = self::update_static_content_string( self::add_static_content_wrapper( $text_only ), $post_content );
		    } else {
			    /* add new lines before the static wrapper, in case there are Embeds in the post content */
			$post_content = $post_content . "\n\n" . self::add_static_content_wrapper( $text_only );
		    }
		    self::update_post($post_id,array('post_content'=>$post_content));
		    return true;
		}
		return false;
	}
	
	
	private static function removeTags($text){
	    

	    // Remove unnecessary tags.
	    $text = preg_replace( '/<\/?div[^>]*\>/i', '', $text );
	    $text = preg_replace( '/<\/?span[^>]*\>/i', '', $text );
	    $text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text );
	    $text = preg_replace( '/<i [^>]*><\\/i[^>]*>/', '', $text );
	    $text = preg_replace( '/ class=".*?"/', '', $text );
	    $text = preg_replace( '/<!--(.|\s)*?-->/' , '' , $text );

	    // Remove line breaks
	    $text = preg_replace( '/(^|[^\n\r])[\r\n](?![\n\r])/', '$1 ', $text );
	    return normalize_whitespace( $text );
	}
	/**
	 * Get all module output plain content.
	 * 
	 * @param array $data 
	 * @return string
	 */
	public static function _get_all_builder_text_content(array $data ) {
		global $ThemifyBuilder;
		$data = $ThemifyBuilder->get_flat_modules_list( null, $data );
		$text = array();
		if( is_array( $data ) ) {
			foreach( $data as $module ) {
				if(isset($module['mod_name']) ) {
					if(!isset(Themify_Builder_Model::$modules[ $module['mod_name'] ])){
						Themify_Builder_Component_Module::load_modules($module['mod_name']);
					}
					if(isset(Themify_Builder_Model::$modules[ $module['mod_name'] ])){
						$t=Themify_Builder_Model::$modules[ $module['mod_name'] ]->get_plain_content( $module );
						if($t!==''){
							$text[] = self::removeTags($t);
						}
					}
				}
			}
		}
		$data=null;
		return implode( "\n", $text );
	}



	/**
	 * Remove empty paragraph
	 * 
	 * @access public
	 * @param string $content 
	 * @return string
	 */
	public static function remove_empty_p( $content ) {
		return str_replace(array(PHP_EOL .'<!--themify_builder_content-->','<!--/themify_builder_content-->'.PHP_EOL,'<p><!--themify_builder_content-->','<!--/themify_builder_content--></p>'),array('<!--themify_builder_content-->','<!--/themify_builder_content-->','<!--themify_builder_content-->','<!--/themify_builder_content-->'),trim($content));
	}

	/**
	 * Dismiss builder data updater static content.
	 * 
	 * @access public
	 */
	public static function dismiss_data_updater_notice() {
		$_key='tb-data-updater-notice-dismissed';
		delete_option($_key);
		add_option($_key,1, '', false );
		wp_send_json_success();
	}

	/**
	 * Save the builder in post meta
	 * 
	 * @param int $post_id 
	 * @param json mixed $data 
	 */
	public static function update_builder_meta($post_id,$data,$static_text=true){
	    if(is_array($data)){
		$builder=self::json_escape($data);
	    }
	    elseif(is_string ($data)){
		$builder=self::json_escape(array($data));
		if(!empty($builder)){
		    $builder=$builder[0];
		}
	    }
	    else{
		$builder=array();
	    }
	    unset($data);
	    
	    
	    if(!empty($builder) ){
		$json=json_encode($builder);
		if(strpos($json,'modules')===false && strpos($json,'styling')===false){
		    $builder=array();
		}
		$json=null;
		if(isset($builder[0]) && !isset($builder[0]['element_id'])){
		    $builder = Themify_Builder_Model::generateElementsIds($builder);
		    return self::update_builder_meta($post_id,$builder,$static_text);
		}
	    }
	    $isNotEmpty=!empty($builder);
	    $builder = apply_filters( 'tb_data_before_save', $builder, $post_id );
	    $builder=self::json_remove_unicode(self::cleanCss($builder));
	    $isRevision=wp_is_post_revision( $post_id );
	    $mid=false;

	    if($isRevision || $static_text===false || self::save_builder_text_only($post_id, $builder)){
		global $wpdb;
		$meta_id = $wpdb->get_row( $wpdb->prepare( "SELECT `meta_id` FROM $wpdb->postmeta WHERE `post_id` = %d AND `meta_key` = %s LIMIT 1", $post_id,self::META_KEY ),ARRAY_A);
		$isUpdate=!empty($meta_id) && !empty($meta_id['meta_id']);
		if($isNotEmpty===true){
		    if($isUpdate===true){
			$meta_id= (int)$meta_id['meta_id'];
			//fires wp hooks
			do_action( 'update_post_meta', $meta_id, $post_id, self::META_KEY, $builder );
			do_action( 'update_postmeta', $meta_id, $post_id, self::META_KEY, $builder );
			$result = $wpdb->update(
				$wpdb->postmeta,
				array(
				    'meta_value' =>$builder
				),
				array(
				    'meta_id'      => $meta_id
				),
				array('%s'),
				array('%d')
			);
			$mid = $result===false?false:$meta_id;
		    }
		    else{
			//fires wp hooks
			do_action( 'add_post_meta', $post_id, self::META_KEY, $builder);
			$result = $wpdb->insert(
				$wpdb->postmeta,
				array(
					'post_id'      => $post_id,
					'meta_key'   =>  self::META_KEY,
					'meta_value' =>$builder
				),
				array('%d','%s','%s')
			);
			$mid = $result!==false?((int) $wpdb->insert_id):false;
		    }
		}
		else{
		    //Don't use delete_post_meta will remove revision parent builder data
		    $deleted=delete_metadata( 'post', $post_id,self::META_KEY,'',false);//if post meta doesn't exist return false it's a bug,but should return 0 to detect if query is successed or not
		    $mid=$deleted===false && $isUpdate===true?false:-1;
		}
		if($mid!==false){
		    if(!$isRevision){
			//Remove the old data format,Don't use delete_post_meta will remove revision parent builder data
			delete_metadata( 'post', $post_id,self::OLD_META_KEY,'',false);
			wp_cache_delete( $post_id,'post_meta' );
			TFCache::remove_cache($post_id);
			themify_clear_menu_cache();
			TFCache::clear_3rd_plugins_cache($post_id);
		    }
		    if($mid!==-1){//fires wp hooks
			if($isUpdate===true){
			    do_action( 'updated_post_meta', $meta_id, $post_id, self::META_KEY, $builder );

			    do_action( 'update_postmeta', $meta_id, $post_id, self::META_KEY, $builder );
			}
			else{
			    do_action( 'added_post_meta', $mid, $post_id, self::META_KEY, $builder );
			}
		    }
		}
	    }
	    return array('mid'=>$mid,'builder_data'=>$builder);
	}
	
	private static function update_post($post_id,$data){
		global $wpdb;
		return $wpdb->update( $wpdb->posts, $data,array('ID'=>$post_id),null,array('%d'));

	}
	
	/**
	 * Clean duplicate css e.g if mobile has color #fff and tablet also has it,leave only tablet value
	 * @param array $builder_data 
	 */
	public static function cleanCss($builder_data){
	    return $builder_data;
		if(is_array($builder_data)){
			foreach($builder_data as &$r){
				if(!empty($r['styling'])){
					$r['styling']=self::cleanDuplicateCss($r['styling']);
}
				if (!empty($r['cols'])) {
						foreach($r['cols'] as &$c){
							if(!empty($c['styling'])){
								$c['styling']=self::cleanDuplicateCss($c['styling']);
							}
							if (!empty($c['modules'])) {
								foreach($c['modules'] as &$m){
									if(!empty($m['mod_settings'])){
										$m['mod_settings']=self::cleanDuplicateCss($m['mod_settings']);
									}
									else if(!empty($m['styling'])){
										$m['styling']=self::cleanDuplicateCss($m['styling']);
									}
									if (!empty($m['cols'])) {
										foreach ($m['cols'] as &$sub_col) {
											if(!empty($sub_col['styling'])){
												$sub_col['styling']=self::cleanDuplicateCss($sub_col['styling']);
											}
											if (!empty($sub_col['modules'])) {
												foreach ($sub_col['modules'] as &$sub_m) {
													if(!empty($sub_col['mod_settings'])){
														$sub_col['mod_settings']=self::cleanDuplicateCss($sub_col['mod_settings']);
													}
												}
											}
										}
									}
								}
							}
						}
				}
			}
		}
		return $builder_data;
	}
	
	/**
	 * Clean duplicate css e.g if mobile has color #fff and tablet also has it,leave only tablet value
	 * @param object $styles 
	 */
	private static function cleanDuplicateCss($styles){
		$breakpoints = array_keys(themify_get_breakpoints());
		array_unshift($breakpoints,'desktop');
		$count = count($breakpoints);
		for($i=$count-1;$i>0;--$i){
                    $bp=$breakpoints[$i];
                    if(isset($styles['breakpoint_'.$bp])){
                            foreach($styles['breakpoint_'.$bp] as $k=>$v){
                                if($v!=='px' && strpos($k,'_unit',1)!==false && substr( $k, -5 ) === '_unit'){//because in very old version px wasn't saved and we can't detect after removing it was px value or not
                                    continue;
                                }
                                for($j=$i-1;$j>-1;--$j){
                                        if($breakpoints[$j]==='desktop'){
                                            $st=$styles;
                                        }
                                        else if(isset($styles['breakpoint_'.$breakpoints[$j]])){
                                            $st=$styles['breakpoint_'.$breakpoints[$j]];
                                        }
                                        else{
                                            continue;
                                        }
                                        if(isset($st[$k])){
                                            if($st[$k]===$v){
                                                unset($styles['breakpoint_'.$bp][$k]);
                                            }
                                            break;
                                        }
                                }
                            }
                            if(empty($styles['breakpoint_'.$bp])){
                                unset($styles['breakpoint_'.$bp]);
                            }
                    }
		}
		return $styles;
	}
	
}
ThemifyBuilder_Data_Manager::init();