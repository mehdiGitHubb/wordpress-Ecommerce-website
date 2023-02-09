<?php
/**
 * importing/erasing contents of the theme
 *
 * @package Themify
 */

defined( 'ABSPATH' ) || exit;


class Themify_Import_Helper {
	
	const DEMO_KEY='tf_demo';


	public static function init(){
	    add_action( 'wp_ajax_themify_import_terms', array( __CLASS__, 'import_ajax_terms' ) );
	    add_action( 'wp_ajax_themify_import_posts', array( __CLASS__, 'import_ajax_posts' ) );
	    add_action( 'wp_ajax_themify_import_theme_data', array( __CLASS__, 'import_ajax_theme_data' ) );
	    add_action( 'wp_ajax_themify_upload_image', array( __CLASS__, 'import_ajax_image' ) );
	    add_action( 'wp_ajax_themify_import_gallery', array( __CLASS__, 'import_ajax_post_gallery' ) );
	    add_action( 'wp_ajax_themify_erase_content', array( __CLASS__, 'erase_demo' ) );
	}
	
	
	
	public static function import_ajax_posts(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(current_user_can('import')){
		if ( isset( $_POST['data'] ) ) {
		    $posts = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $posts = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(!empty($posts)){
		    self::set_time_limit();
		    self::raise_memory_limit();
		    $posts = json_decode( $posts, true );
		    $skinId=!empty($_POST['id'])?$_POST['id']:'default';
		    $ids=array();
		    foreach($posts as $post){
			$id=self::import_post($post,$skinId);
			if(is_numeric($id) && $post['post_type']!=='tbp_template'){
			    self::set_import_id($post['ID'],$id,'post',$skinId);
			}
			$ids[$post['ID']]=$id;
		    }
		    if(!empty($ids)){
			wp_send_json_success($ids);
		    }
		}
	    }
	    wp_send_json_error();
	}
	
	public static function import_ajax_terms(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(current_user_can('import')){
		if ( isset( $_POST['data'] ) ) {
		    $terms = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $terms = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(!empty($terms)){
		    self::set_time_limit();
		    self::raise_memory_limit();
		    $terms = json_decode( $terms, true );
		    $skinId=!empty($_POST['id'])?$_POST['id']:'default';
		    $ids=array();
		    foreach($terms as $term){
			$id=self::import_term($term);
			if(is_numeric($id)){
			    self::set_import_id($term['term_id'],$id,'taxonomy',$skinId);
			}
			$ids[$term['term_id']]=$id;
		    }
		    if(!empty($ids)){
			wp_send_json_success($ids);
		    }
		}
	    }
	    wp_send_json_error();
	}
	
	
	public static function import_ajax_theme_data(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(current_user_can('manage_options')){
		if ( isset( $_POST['data'] ) ) {
		    $settings = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $settings = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(!empty($settings)){
		    self::set_time_limit();
		    self::raise_memory_limit();
		    $settings = json_decode( $settings, true );
		    $skinId=!empty($_POST['id'])?$_POST['id']:'default';
		    $id=self::import_settings($settings,$skinId);
		    if($id!==false){
			wp_send_json_success($id);
		    }
		}
	    }
	    wp_send_json_error();
	}
	
	public static function import_ajax_image(){
	    if(!empty($_POST['postData']) && current_user_can( 'import' )){
		check_ajax_referer('tf_nonce', 'nonce');
		$postData=json_decode(stripslashes_deep($_POST['postData']),true);
		$response=array();
		if(!empty($postData)){
		    self::set_time_limit();
		    self::raise_memory_limit('image');
		    $skinId=!empty($_POST['save_id'])?$_POST['save_id']:'';
		    $webp=empty($_POST['stop_webp'])?true:false;
		    foreach($postData as $key=>$arrPost){
			if(!empty($arrPost['thumb'])){
				$blob=!empty($_FILES[$key]) && $_FILES[$key]!='1'?$_FILES[$key]:null;
				$res=self::import_image($arrPost,$blob,$webp);
				if($skinId!=='' && is_array($res) && isset($res['id'])){
				    self::set_import_id(null,$res['id'],'attachment',$skinId);
				}
				$response[$arrPost['thumb']]=$res;
			}
		    }
		}
		
		
		wp_send_json_success($response);
	    }
	    wp_send_json_error();
	}
	
	public static function import_ajax_post_gallery(){
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(current_user_can( 'import' )){
		if ( isset( $_POST['data'] ) ) {
		    $data = stripslashes_deep( $_POST['data'] );
		} 
		elseif ( isset( $_FILES['data'] ) ) {
		    $data = file_get_contents( $_FILES['data']['tmp_name'] );
		}
		if(!empty($data)){
		    $data=json_decode( $data, true );
		    if(!empty($data)){
			self::set_time_limit();
			self::raise_memory_limit();
			foreach($data as $post_id=>$gallery){
			    self::import_post_gallery($post_id,$gallery);
			}
			wp_send_json_success();
		    }
		}
	    }
	    wp_send_json_error();
	}
	
	protected static function set_time_limit($limit=0){
	    ignore_user_abort(true);
	    if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
		@set_time_limit( $limit ); // @codingStandardsIgnoreLine
	    }
	}
	
	protected static function raise_memory_limit($context='admin',$size='512MB'){
	    if(!defined('WP_MAX_MEMORY_LIMIT')){
		define('WP_MAX_MEMORY_LIMIT',$size);
	    }
	    return wp_raise_memory_limit($context);
	}

	public static function import_product_attribute( $name, $slug ) {
		if ( function_exists( 'wc_create_attribute' ) && ! empty( $name ) && ! empty( $slug ) && current_user_can( 'import' ) ) {
			wc_create_attribute( array(
				'name' => $name,
				'slug' => $slug,
			) );
			$slug = wc_attribute_taxonomy_name( $slug );
			register_taxonomy( $slug, array( 'product' ), array(
				'labels' => array(
					'name' => sprintf( _x( 'Product %s', 'Product Attribute', 'woocommerce' ), $name ),
					'singular_name' => $name,
				)
			) );
		}
	}

	protected static function get_term_id_by_slug( $slug, $tax,$importId=0 ) {
		$term = get_term_by( 'slug', $slug, $tax );
		if($importId>0 && (!$term || is_wp_error($term))){
		    $term= get_term((int)$importId, $tax );
		}
		if(!empty($term) && !is_wp_error($term) && $term->taxonomy===$tax){
		    return (int)$term->term_id;
		}
		return false;
	}

	/**
	 * Removes all content marked as demo
	 *
	 * @return void
	 */
	public static function erase_demo() {
	    check_ajax_referer('tf_nonce', 'nonce');
	    if(!current_user_can('delete_pages')){
		wp_send_json_error(__('You are not allowed to delete pages on this site','themify'));
	    }
	    if ( isset( $_POST['data'] ) ) {
		$data = json_decode(stripslashes_deep( $_POST['data'] ),true);
	    } 
	    elseif ( isset( $_FILES['data'] ) ) {
		$data = file_get_contents( $_FILES['data']['tmp_name'] );
	    }
	    else{
		$data=array();
	    }
	    self::set_time_limit();
	    self::raise_memory_limit();
	    $keepModified=!empty($data['keep_modify']) && $data['keep_modify']!=='0';
	    $isWc= themify_is_woocommerce_active();
	    $wc_pages=array();
	    if($isWc===true){
		foreach ( array( 'myaccount', 'shop', 'cart', 'checkout', 'view_order', 'terms' ) as $wc_page ) {
		    $page_id = wc_get_page_id( $wc_page );
		    if ( $page_id > 0 ) {
			$wc_pages[ $page_id ] = $wc_page;
		    }
		}
	    }
	    $terms = get_terms( [ 
		'number' => 0,
		'meta_key' => '_'.static::DEMO_KEY, 
		'meta_value' => 1 ,
		'no_found_rows'=>true,
		] 
	    );	 
	    if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
		    wp_delete_term( $term->term_id, $term->taxonomy );
		}
	    }   

	    $posts = get_posts( [
		    'post_type' => get_post_types(),
		    'post_status' => 'any',
		    'meta_key' => '_'.static::DEMO_KEY,
		    'meta_value' => 1,
		    'no_found_rows'=>true,
		    'posts_per_page' => -1,
	    ] );
	    foreach ( $posts as $post ) {
		$delete = $keepModified===true?!(strtotime($post->post_modified)>strtotime($post->post_date)):true;
		if($delete===true){
		    if ( $post->post_type === 'attachment' ) {
			self::erase_image($post,$keepModified);
		    } 
		    elseif($isWc===true && $post->post_type==='product'){
			self::erase_product($post->ID,$keepModified);
		    }
		    else {
			wp_delete_post( $post->ID, true );
			if(isset($wc_pages[$post->ID])){
			    delete_option( 'woocommerce_' . $post->post_name . '_page_id');
			}
		    }
		}
	    }
	    $items=Themify_Storage::get(static::DEMO_KEY,null,'db');	
	    $isBreak=false;    
	    if(!empty($items)){
		$items=json_decode($items,true);
		$memory=(int)(wp_convert_hr_to_bytes(WP_MEMORY_LIMIT)*MB_IN_BYTES);
		$limit=$memory>128?100:($memory<64?60:80);
		$count=0;
		foreach($items as $skin=>$el){
			if(!empty($el['taxonomy'])){
			    foreach($el['taxonomy'] as $oldId=>$newId){
				$term=get_term($newId);
				$delete=true;
				if(!empty($term) && !is_wp_error($term)){
				    $success=current_user_can('delete_term',$newId)?wp_delete_term($newId, $term->taxonomy):false;
				    if($success===true){
					++$count;
				    }
				    elseif($success===false){
					$delete=false;
				    }
				    elseif(is_wp_error($success) && $success===0){
					unset($items[$skin]['taxonomy'][$oldId]);
				    }
				}
				if($delete===true){
				    unset($items[$skin]['taxonomy'][$oldId]);
				    if($count>=$limit){
					$isBreak=true;
					break;
				    }
				}
			    }
			}
			if(!empty($el['post']) && $count<$limit){
			    foreach($el['post'] as $oldId=>$newId){
				$post = get_post( $newId );
				$delete=true;
				if(!empty($post)){
				    $delete = $keepModified===true?!(strtotime($post->post_modified)>strtotime($post->post_date)):true;
				    if($delete===true){
					$post_type=$post->post_type;		
					if(($post_type!=='page' && current_user_can('delete_post',$newId)) || ($post_type==='page' && current_user_can('delete_page',$newId))){
					    if ( $post_type === 'attachment' ) {
						$el['attachment'][]=$newId;
					    }
					    else{
						if($isWc===true && $post_type==='product'){
						   $deletedItems=self::erase_product($post->ID,$keepModified);
						   $delete=false;
						   foreach($deletedItems['posts'] as $product_id=>$is_deleted){
							if($is_deleted===true){
							    ++$count; 
							    unset($items[$skin]['post'][$product_id]);
							}
						   }
						   if(!empty($deletedItems['attachment'])){
							foreach($deletedItems['attachment'] as $image_id=>$is_deleted){
							    if($is_deleted===true){
								++$count; 
								if(isset($items[$skin]['attachment'])){
								    unset($items[$skin]['attachment'][$image_id],$el['attachment'][$image_id]);
								}
							    }
							}
						   }
						}
						else {
						    $success=wp_delete_post( $post->ID, true );
						    $delete=!empty($success);
						    if($delete===true){
							++$count;
						    }
						}
					    }
					}
					else{
					    $delete=false;
					}
				    }
				}
				if($delete===true){
				    unset($items[$skin]['post'][$oldId]);
				    if(!empty($post) && isset($wc_pages[$post->ID])){
					delete_option( 'woocommerce_' . str_replace('-','',$post->post_name) . '_page_id');
				    }
				}
				if($count>=$limit){
				    $isBreak=true;
				    break;
				}
			    }
			}
			if(!empty($el['attachment']) && $count<$limit){
			    foreach($el['attachment'] as $oldId=>$newId){
				$post = get_post( $newId );
				$delete=true;
				if(!empty($post) && $post->post_type === 'attachment'){
				    $delete = $keepModified===true?!(strtotime($post->post_modified)>strtotime($post->post_date)):true;
				    if($delete===true){
					$delete=current_user_can('delete_post',$newId)?self::erase_image($post,$keepModified):false;
					if($delete===true){
					    ++$count;
					}
				    }
				}
				if($delete===true){
				    unset($items[$skin]['attachment'][$oldId]);
				    if($count>=$limit){
					$isBreak=true;
					break;
				    }
				}
			    }
			}
			
			if(empty($items[$skin]['taxonomy'])){
			    unset($items[$skin]['taxonomy']);
			}
			if(empty($items[$skin]['post'])){
			    unset($items[$skin]['post']);
			}
			if(empty($items[$skin]['attachment'])){
			    unset($items[$skin]['attachment']);
			}
			if(empty($items[$skin])){
			    unset($items[$skin]);
			}
			if($isBreak===true){
			    break;
			}
		}	 
		if(!empty($items)){	
		    Themify_Storage::set(static::DEMO_KEY,$items,null,null,'db');
		}
		else{
		    Themify_Storage::delete(static::DEMO_KEY);
		}
	    }
	    if($isBreak===true){
		$response='repeat';
	    }
	    else{
		$response=self::has_demo_content()?'hasdemo':0;
	    }
	    wp_send_json_success($response);
	}
	
	
	public static function erase_product($id,$keepModified=false){
	    $product = themify_is_woocommerce_active()? wc_get_product($id):null;
	    $res=array('posts'=>array());
	    if(!empty($product)){
		$isVariable=$product->is_type('variable');
		$isGroup=$product->is_type('grouped');
		if($isVariable || $isGroup){
		    foreach ($product->get_children() as $child_id){
		       $child = wc_get_product($child_id);
		       $delete=true;
		       if(!empty($child)){
			    if($isVariable){
				$delete=$child->delete(true);
			    }
			    else{
				$child->set_parent_id(0);
				$delete=$child->save();
				$delete=$delete>0?true:false;
			    }
		       }
		       $res['posts'][$child_id]=$delete;
		   }
		}
		$image_galleries_id = $product->get_gallery_image_ids();

		if( !empty( $image_galleries_id ) ) {
		    $res['attachment']=array();
		    foreach( $image_galleries_id as $image_id ) {
			$post = get_post( $image_id );
			$delete=true;
			if(!empty($post) && $post->post_type === 'attachment'){
			    $delete = $keepModified===true?!(strtotime($post->post_modified)>strtotime($post->post_date)):true;
			    if($delete===true){
				$delete= self::erase_image($image_id,$keepModified);
			    }
			}
			$res['attachment'][$image_id]=$delete;
		    }
		}
	       $res['posts'][$id]=$product->delete(true);
	       // Delete parent product transients.
	       if ($parent_id = wp_get_post_parent_id($id)){
		   wc_delete_product_transients($parent_id);
	       }
	   }
	   else{
	       $res['posts'][$id]= wp_delete_post( $id, true );
	   }
	   return $res;
	}
	
	public static function erase_image($post,$force=false){
	    $post=get_post($post);
	    if(!empty($post) && $post->post_type==='attachment'){
		$delete=$force===true || $post->post_parent===0 || get_post_status($post->post_parent)===false;
		if($delete===true){
		    if($force===true){
			global $wpdb;
			$file_name = wp_basename( wp_get_attachment_url($post->ID));
			$path=pathinfo($file_name);
			$file_name=esc_sql($path['basename']);
			$size_name=esc_sql($path['filename']).'-';	
			$sql="'%$file_name%' OR '%$size_name%'";
			unset($path,$file_name,$size_name);
			$exist=$wpdb->query("SELECT 1 FROM $wpdb->postmeta WHERE `meta_value` LIKE $sql LIMIT 1");
			if(!empty($exist)){
			    return false;
			}
			$exist=$wpdb->query("SELECT 1 FROM $wpdb->posts WHERE `post_content` LIKE $sql LIMIT 1");
			if(!empty($exist)){
			    return false;
			}
			$exist=$wpdb->query("SELECT 1 FROM $wpdb->termmeta WHERE `meta_value` LIKE $sql LIMIT 1");
			if(!empty($exist)){
			    return false;
			}		
		    }
		    if($delete===true){
			return wp_delete_attachment( $post->ID, true );
		    }
		}
	    }
	    return true;
	}

	/**
	 * Returns true only if there are any demo contents installed on this site
	 *
	 * @return bool
	 */
	public static function has_demo_content() {
		$content=Themify_Storage::get(static::DEMO_KEY,null,'db');
		if(!empty($content)){
		    return true;
		}
		$terms = get_terms( [ 
		    'number' => 1, 
		    'meta_key' => '_'.static::DEMO_KEY,
		    'meta_value' => 1,
		    'no_found_rows'=>true, ] 
		);
		if ( ! empty( $terms ) ) {
			return true;
		}

		$posts = get_posts( [
			'post_type' => get_post_types(),
			'post_status' => 'any',
			'meta_key' => '_'.static::DEMO_KEY,
			'meta_value' => 1,
                        'no_found_rows'=>true,
			'posts_per_page' => 1,
		] );
		if ( ! empty( $posts ) ) {
			return true;
		}
		return false;
	}
	
	protected static function set_import_id($oldId,$newId,$type,$skinId='default'){
	    $newId=(int)$newId;
	    if($newId>0){
		$items=Themify_Storage::get(static::DEMO_KEY,null,'db');
		$items=empty($items)?array():json_decode($items,true);
		if(empty($items[$skinId])){
		    $items[$skinId]=array();
		}
		if(empty($items[$skinId][$type])){
		    $items[$skinId][$type]=array();
		}
		if(!$oldId){
		    $items[$skinId][$type][]=$newId;
		}
		else{
		    $items[$skinId][$type][$oldId]=$newId;
		}
		return Themify_Storage::set(static::DEMO_KEY,$items,null,null,'db');
	    }
	    return false;
	}
	
	
	
	protected static function get_import_id( $old_id,$skinId='default',$import_type=null ) {
	    global $wpdb;
	    $type='post';
	    $key='post_id';
	    $table=$wpdb->postmeta;
	    if($import_type==='taxonomy'){
		$type='taxonomy';
		$key='term_id';
		$table=$wpdb->termmeta;
	    }    
	    $items=Themify_Storage::get(static::DEMO_KEY,null,'db');
	    $items=empty($items)?array():json_decode($items,true);
	    if(isset($items[$skinId])){
		$items=$items[$skinId];
	    }
	    if(!empty($items) && !empty($items[$type][$old_id])){
		return $items[$type][$old_id];
	    }
	    
	    //backward
	    $result = $wpdb->get_row( $wpdb->prepare(
		    "SELECT {$key} FROM {$table} WHERE meta_key = '_".static::DEMO_KEY."_id' AND meta_value = %d LIMIT 1",
		    $old_id
	    ), ARRAY_A );

	    if ( isset( $result[$key] ) ) {
		if(self::set_import_id($old_id,$result[$key],$type,$skinId)){
		    if($type==='taxonomy'){
			delete_term_meta($result[$key],'_'.static::DEMO_KEY.'_id');
		    }
		    else{
			delete_post_meta($result[$key],'_'.static::DEMO_KEY.'_id');
		    }
		}
		return $result[$key];
	    }

	    return false;
	}
	
	
	public static function import_term(array $term,$skinId='default',$update=false){
	    if(!empty($term)){
		if(taxonomy_exists( $term['taxonomy'] )){
		    if($term['taxonomy']==='nav_menu'){
			return self::import_menu($term,$update);
		    }
		    $term_id = term_exists( $term['slug'], $term['taxonomy'] );
		    if ( empty( $term['parent'] ) || empty($term['parent_slug']) ) {
			$parent = 0;
		    } 
		    else {
			$parentTerm = self::get_term_id_by_slug($term['parent_slug'],$term['taxonomy'],self::get_import_id($term['parent'],$skinId,'taxonomy') );
			$parent =$parentTerm!==false?$parentTerm:0;
			unset($parentTerm);
		    }
		    if ( empty($term_id) ) {

			$term_id = wp_insert_term( $term['name'], $term['taxonomy'], array(
				'parent' => $parent,
				'slug' => $term['slug'],
				'description' =>isset($term['description'])?$term['description']:''
			) );
		    }
		    elseif($update===true){
			$term_id=wp_update_term($term_id,$term['taxonomy'],array(
			    'name'=>$term['name'],
			    'slug'=>$term['slug'],
			    'parent' => $parent,
			    'description' =>isset($term['description'])?$term['description']:''
			));
		    }
		    if ($term_id && ! is_wp_error( $term_id ) ) {
			if ( is_array( $term_id ) ) {
			    $term_id = $term_id['term_id'];
			}
			return self::get_import_id($term_id,$skinId,'taxonomy')?true:(int)$term_id;
		    }
		    else{
			return false;
		    }
		}
		else{
		    return array('msg'=>sprintf(__("Taxonomy %s dosen't exist",'themify'),$term['taxonomy']),'skip'=>$term['taxonomy']);
		}
	    }
	    return false;
	}
	

	public static function import_post(array $post,$skinId='default',$update=false){
	    if(!empty($post)){
		    	    
		if (post_type_exists( $post['post_type'] ) ) {
		    $post['post_author'] = (int) get_current_user_id();
		    $post['post_status'] = 'publish';
		    if(!isset($post['post_parent'])){
			$post['post_parent']=0;
		    }
		    if(!isset($post['menu_order'])){
			$post['menu_order']=0;
		    }
		    if(!isset($post['post_content'])){
			$post['post_content']='';
		    }
		    if(!isset($post['post_excerpt'])){
			$post['post_excerpt']='';
		    }
		    if(isset($post['post_date'])){
			$post['post_modified']=$post['post_date'];
		    }
		    if ( $post['post_type'] === 'nav_menu_item' ) {
			return self::import_menu_item($post,$skinId,$update);
		    }
		    $old_id = $post['ID'];
		    $isWcPage=false;
		    unset( $post['ID'] );
		    /* Menu items don't have reliable post_title, skip the post_exists check */
		    /* With tbp_template, different Themes can have duplicate templates so skip post_exists */
		    if ( $post['post_type'] !=='tbp_template' ) {
			$isWcPage=!empty($post['is_wc_page']) && themify_is_woocommerce_active();
			$post_id = isset($post['post_date'],$post['post_title'])?post_exists( $post['post_title'], '', $post['post_date'],$post['post_type'] ):0;
			if($isWcPage===true && !$post_id && isset($post['post_name'])){
			    $post_id = wc_get_page_id(str_replace('-','',$post['post_name']));//wc doesn't check if a post exist
			}
			if(!$post_id){
			    $post_id=self::get_import_id($old_id,$skinId);
			}
			if(!$post_id || $post_id<=0 || get_post_type($post_id)!==$post['post_type']){
			    $post_id=0;
			}
			if ( $post_id>0) {
			    if($update===true){
				$post['ID']=$post_id;
			    }
			    else{
				return $post_id;
			    }
			}
		
			if($post['post_parent']>0){
			    $parent=self::get_import_id($post['post_parent'],$skinId);
			    if(!$parent || get_post_type($parent)!==$post['post_type']){
				$parent=0;
			    }
			    $post['post_parent']=$parent;
			}
			/**
			 * for hierarchical taxonomies, IDs must be used so wp_set_post_terms can function properly
			 * convert term slugs to IDs for hierarchical taxonomies
			 */
			if ( ! empty( $post['tax_input'] ) ) {
			    foreach( $post['tax_input'] as $tax => $terms ) {
				if ( ! taxonomy_exists( $tax ) ) {
				    unset( $post['tax_input'][ $tax ] );
				}
				elseif( is_taxonomy_hierarchical( $tax ) ) {
				    $post['tax_input'][ $tax ]=array();
				    $terms = explode( ', ', $terms );
				    foreach($terms as $t){
					$tid=self::get_term_id_by_slug($t,$tax);
					if($tid!==false){
					    $post['tax_input'][ $tax ][]=$tid;
					}
				    }
				    if(empty($post['tax_input'][ $tax ])){
					unset($post['tax_input'][ $tax ]);
				    }
				}
			    }
			}
		    }
		    $builderKey=ThemifyBuilder_Data_Manager::META_KEY;
		    if(!empty($post['meta_input'][$builderKey])){			 
			$builder=$post['meta_input'][$builderKey];
		    }
		    $isHome=!empty($post['is_home']);
		    $productGallery=!empty($post['_product_image_gallery'])?$post['_product_image_gallery']:null;
		    unset($post['meta_input'][$builderKey],$post['is_home'],$post['is_wc_page'],$post['_product_image_gallery']);
		    $post_id = wp_insert_post( $post, true );

		    if (!$post_id || is_wp_error( $post_id ) ) {
			return false;
		    } 
		    $post_id=(int)$post_id;
		    if(!empty($builder)){
			$res=ThemifyBuilder_Data_Manager::update_builder_meta($post_id,$builder,false);
			if(!empty($res['mid'])){
			    Themify_Global_Styles::save_used_global_styles($res['builder_data'], $post_id);
			}
			unset($builder,$res);
		    }
		    if(isset($post['post_date'])){
			global $wpdb;
			$wpdb->update( $wpdb->posts, array( 'post_modified' => $post['post_date'],'post_modified_gmt' => get_gmt_from_date($post['post_date']) ), array('id'=>$post_id) );
		    }
		    // Home page
		    if ( $isHome===true) {
			    update_option( 'show_on_front', 'page' );
			    update_option( 'page_on_front', $post_id );
		    }
		    if($isWcPage===true){
			$post_name=str_replace('-','',$post['post_name']);
			$wc_page = wc_get_page_id($post_name);
			if($wc_page<=0 || get_post_type($wc_page)!==$post['post_type']){
			    update_option( 'woocommerce_' . $post_name . '_page_id',$post_id,true );
			}
		    }
		    if($productGallery!==null){
			self::import_post_gallery($post_id, $productGallery);
		    }
		    return $post_id;
		}
		else{
		    return array('msg'=>sprintf(__("Post Type %s dosen't exist",'themify'),$post['post_type']),'skip'=>$post['post_type']);
		}
	    }
	}
	public static function import_menu_item(array $post,$skinId='default',$update=false){
	    if ( $post['post_type'] === 'nav_menu_item' && isset( $post['tax_input']['nav_menu'] )) {
		    $oldId = (!empty($post['post_title']) && !empty($post['post_date']))?post_exists( $post['post_title'], '', $post['post_date'],$post['post_type'] ):false;
		    if(!$oldId){
			$oldId=self::get_import_id($post['ID'],$skinId);	
		    }
		    if(!$oldId || get_post_type($oldId)!==$post['post_type']){
			$oldId=0;
		    }
		    elseif($update===false){
			return true;
		    }
		    $m=wp_get_nav_menu_object( $post['tax_input']['nav_menu']);
		    if(empty($m) || is_wp_error($m)){
			return array('msg'=>sprintf(__("Menu %s dosen't exist",'themify'),$post['tax_input']['nav_menu']));
		    }
		    $menuId=$m->term_id;
		    if(empty($post['meta_input']) || empty($post['meta_input']['_menu_item_type'])  || empty($post['meta_input']['_menu_item_object'])){
			return false;
		    }
		    $meta=$post['meta_input'];
		    $menuType=$meta['_menu_item_type'];
		    $args=array(
			'menu-item-title'=>isset($post['post_title'])?$post['post_title']:'',
			'menu-item-position'=>isset($post['menu_order'])?$post['menu_order']:0,
			'menu-item-classes'=>isset($meta['_menu_item_classes'])?(is_array($meta['_menu_item_classes'])?implode(' ',$meta['_menu_item_classes']):$meta['_menu_item_classes']):'',
			'menu-item-xfn'=>isset($meta['_menu_item_xfn'])?(is_array($meta['_menu_item_xfn'])?implode(' ',$meta['_menu_item_xfn']):$meta['_menu_item_xfn']):'',
			'menu-item-type'=>$menuType,
			'menu-item-object'=>$meta['_menu_item_object'],
			'menu-item-target'=>isset($meta['_menu_item_target'])?$meta['_menu_item_target']:'',
			'menu-item-description'=>isset($post['post_content'])?$post['post_content']:'',
			'menu-item-post-date'=>isset($post['post_date'])?$post['post_date']:'',
			'menu-item-post-date-gmt'=>isset($post['post_date_gmt'])?$post['post_date_gmt']:'',
			'menu-item-status'=>isset($post['post_status'])?$post['post_status']:'publish',
			'menu-item-object-id'=>0
		    );
		    unset($m);
		    if($args['menu-item-post-date-gmt']===''){
			$args['menu-item-post-date-gmt']=$args['menu-item-post-date'];
		    }
		    if($menuType!=='custom'){
			$objectType=$meta['_menu_item_object'];
			if(($menuType==='post_type_archive' || $menuType==='post_type') && !post_type_exists($objectType)){
			    return array('msg'=>sprintf(__("Post Type %s dosen't exist",'themify'),$objectType));
			}
			elseif($menuType==='taxonomy' && ! taxonomy_exists( $objectType )){
			    return array('msg'=>sprintf(__("Taxonomy %s dosen't exist",'themify'),$objectType));
			}
			if($menuType!=='post_type_archive'){
			    $menu_item_id=$meta['_menu_item_object_id'];
			    $newId=null;
			    if ( ! is_numeric( $menu_item_id ) && function_exists( 'wc_get_page_id' )) {
				$newId = wc_get_page_id( $menu_item_id );//wc doesn't check if a post exist
				if($newId<=0 || get_post_type($newId)!==$objectType){
				    $newId=null;
				}
			    }
			    else{
				if(is_numeric( $menu_item_id )){
				    $menu_item_id=(int)$menu_item_id;
				    $newId = self::get_import_id( $menu_item_id,$skinId,$menuType);
				}
				if($menuType==='post_type'){
				    if(isset($meta['slug']) && (!$newId || get_post_type($newId)!==$objectType)){
					$newId=null;
					if($objectType==='page'){
					    $newId=get_page_by_path($meta['slug']);
					}
					else{
					    $tmp=array(
						'name' => $meta['slug'],
						'post_type' => $objectType,
						'post_status' => 'any',
						'no_found_rows'=>true,
						'posts_per_page' => 1
					    );
					    $newId = get_posts( $tmp );
					    if(!empty($newId)){
						$newId=$newId[0];
					    }
					    unset($tmp);
					}
				    }
				    $newId=!empty($newId)?(is_numeric($newId)?$newId:$newId->ID):null;
				}
				elseif($menuType==='taxonomy' && isset($meta['slug']) && (!$newId || !term_exists($newId,$objectType))){
				    $newId=self::get_term_id_by_slug($meta['slug'], $objectType,$newId);
				}
			    }
			    if(empty($newId)){
				return false;
			    }
			    $args['menu-item-object-id']=$newId;
			}
		    }
		    else{
			$args['menu-item-url'] =  isset($meta['_menu_item_url'])?$meta['_menu_item_url']:'';
		    }
		    $parent =  isset($meta['_menu_item_menu_item_parent'] ) ?(int)$meta['_menu_item_menu_item_parent'] :0;
		    if($parent>0){
			$parent=self::get_import_id($parent,$skinId);
			if(!$parent || get_post_type($parent)!==$post['post_type']){
			    $parent=0;
			}
		    }
		    unset($post);
		    $args['menu-item-parent-id']=$parent;
		    $post_id=wp_update_nav_menu_item($menuId,$oldId,$args);
		    if (!$post_id || is_wp_error( $post_id ) ) {
			    return false;
		    } 
		    unset($args,
			$parent,
			$meta['slug'],
			$meta['is_home'],
			$meta['_menu_item_object'],
			$meta['_menu_item_object_id'],
			$meta['_menu_item_url'],
			$meta['_menu_item_menu_item_parent'],
			$meta['_menu_item_xfn'],
			$meta['_menu_item_classes'],
			$meta['_menu_item_target'],
			$meta['_menu_item_type']);
		    if(!empty($meta)){
			foreach($meta as $k=>$v){
			    if($v!=='' && $v!==null){
				update_post_meta($post_id,$k,sanitize_key($v));
			    }
			}
		    }
		    return $post_id;
	    }
	    return false;
	}
	
	public static function import_menu(array $menu,$update=false){
	    $m = wp_get_nav_menu_object( $menu['slug'] );
	    if(empty($m) || is_wp_error($m)){
		$m = wp_get_nav_menu_object( sanitize_title($menu['name']) );
	    }
	    $m=empty($m) || is_wp_error($m)?0:$m->term_id;
	    if($m===0 || $update===true){
		$m=wp_update_nav_menu_object($m,array(
		    'menu-name'=>$menu['name'],
		    'parent'=>isset($menu['parent'])?$menu['parent']:0,
		    'description'=>isset($menu['description'])?$menu['description']:''
		));
	    }
	    return $m && !is_wp_error($m)?$m:false;
	}
	
	public static function import_settings(array $settings,$skinId='default',$update=false){
	    if(!empty($settings)){
		if(!empty($settings['theme_mods'])){
		    $theme = get_option( 'stylesheet' );
		    update_option( 'theme_mods_'.$theme, $settings['theme_mods'] );
		    unset($theme);
		}
		if(!empty($settings['menu'])){
		    foreach($settings['menu'] as $menu){
			$m=self::import_menu($menu,$update);
			if($m!==false){
			    self::set_import_id($menu['term_id'],$m,'taxonomy',$skinId);
			}
		    }
		}
		if(!empty($settings['menu_locations'])){
		    $locations=$settings['menu_locations'];
		    $themeLocations = get_theme_mod('nav_menu_locations');
		    foreach($locations as $location=>$menu){
			$m=self::import_menu($menu,$update);
			if($m!==false){
			    $themeLocations[$location]=$m;
			    self::set_import_id($menu['term_id'],$m,'taxonomy',$skinId);
			}
		    }
		    set_theme_mod( 'nav_menu_locations', $themeLocations );
		    unset($themeLocations,$locations);
		}
		if(!empty($settings['product_filter']) && method_exists('WPF','get_instance') && method_exists('WPF_Options','get_option')){
		    $instance=WPF::get_instance();
		    if(method_exists($instance,'get_plugin_name') && method_exists($instance,'get_version')){
			$instance = WPF_Options::get_option($instance->get_plugin_name(),$instance->get_version());
			if(method_exists($instance,'get') && method_exists($instance,'set')){
			    $instance->set(array_merge($settings['product_filter'],$instance->get()));
			}
		    }
		}
		if(!empty($settings['product_attribute'])){
		    foreach($settings['product_attribute'] as $slug=>$label){
			self::import_product_attribute($label, $slug);
		    }
		}
		if(!empty($settings['homepage'])){
		    $homepage=get_page_by_path($settings['homepage']);
		    if(!empty($homepage) && !is_wp_error($homepage)){
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $homepage->ID );
		    }
		    unset($homepage);
		}
		if(!empty($settings['widgets'])){
		    $importWidgets=$settings['widgets'];
		    $sidebars_widgets=array();
		    $index=1001;
		    foreach($importWidgets as $wid=>$widgets){
			$sidebars_widgets[$wid]=array();
			foreach($widgets as $widget){
			    if(is_array($widget['options']) && isset($widget['options']['nav_menu'])){
				$menuId=self::get_term_id_by_slug($widget['options']['nav_menu'], 'nav_menu');
				if($menuId!==false){
				    $widget['options']['nav_menu']=$menuId;
				}
				else{
				    continue;
				}
			    }
			    ++$index;
			    $id= 'widget_'.$widget['id'];
			    $themeWidget = get_option($id );
			    if(empty($themeWidget)){
				$themeWidget=array();
			    }
			    $themeWidget[$index]=$widget['options'];
			    update_option($id, $themeWidget );
			    $sidebars_widgets[$wid][]=$widget['id'].'-'.$index;
			}
		    }
		    update_option('sidebars_widgets', $sidebars_widgets );
		    unset($sidebars_widgets,$importWidgets);
		}
		if(!empty($settings['themify_settings'])){
		    $skinSettings=array_merge(themify_get_data(),$settings['themify_settings']);
		    themify_set_data($skinSettings);
		}
		return true;
	    }
	    return false;
	}
	

	
	public static function import_post_gallery($post_id,$gallery){
	    if(!empty($gallery)){
		if(is_array($gallery)){
		    $gallery=implode( ',', $gallery );
		}
		return update_post_meta( $post_id, '_product_image_gallery', $gallery );
	    }
	    return false;
	}
	
	public static function compare_files($f1,$f2,$size=4096){
	    $fh1 = fopen($f1, 'rb');
	    $fh2 = fopen($f2, 'rb');
	    if($fh1!==false && $fh2!==false){
		try {
		    while (!feof($fh1) && !feof($fh2)){
			yield fread($fh1, $size) =>fread($fh2, $size);
		    }
		} 
		finally {
		    fclose($fh1);
		    fclose($fh2);
		}
	    }
	    else{
		yield false;
	    }
	}
	
	public static function import_image(array $post,$blob=null,$generate=true){	 
	    if (!empty($post['thumb'])) {
		if(!current_user_can( 'upload_files' )){
		    $error=__('You aren`t allowed to upload file', 'themify');
		}
		else{
		    
		    $error='';
		    $thumb=sanitize_text_field( $post['thumb'] );
		    $file_name = sanitize_file_name(wp_basename( $thumb));
		    global $wpdb;
		    $sql= sprintf('post_name="%1$s" OR post_name="%1$s-1" OR post_name="%1$s-2" OR post_name="%1$s-3"',esc_sql(pathinfo($file_name,PATHINFO_FILENAME)));
		    $query = $wpdb->get_row("SELECT ID,post_mime_type FROM {$wpdb->prefix}posts WHERE ({$sql}) AND post_type='attachment' LIMIT 1",ARRAY_A );
		    $attach_id=!empty($query) ? $query['ID'] : null;
		    if($attach_id!==null){
			$duplicate=get_attached_file($attach_id);
			if($duplicate){
			    $size= (int)filesize($duplicate);
			    $mimeType=$query['post_mime_type'];
			    $ext= pathinfo($duplicate,PATHINFO_EXTENSION);
			}  
		    }
		    unset($query,$sql);
		    require_once ABSPATH . 'wp-admin/includes/image.php';
		    require_once ABSPATH . 'wp-admin/includes/file.php';
		    require_once ABSPATH . 'wp-admin/includes/media.php';
		    if(!empty( $blob ) ){
			if($blob['error']>0){
			    $error=__('Uploading error', 'themify');
			}
			else{
			    $file = array(
				'type'     => $blob['type'],
				'tmp_name' => $blob['tmp_name'],
				'error'    => 0,
				'size'     => $blob['size']
			    );
			}
		    }
		    else{
			$tmp = download_url( $thumb );
			if(is_wp_error( $tmp )){
			    $error= $tmp->get_error_message();
			}
			else{	
			    $file = array(
				'size'     => filesize($tmp),
				'error'=>0,
				'tmp_name' => $tmp
			    );
			}
		    }
		    if($error===''){

			$checked =wp_check_filetype_and_ext($file['tmp_name'], $file_name);
			if(!empty($checked['type'])){
			    $file['name']=!empty($checked['proper_filename'])?$checked['proper_filename']:$file_name;
			    $isFileExist=isset($size) && $mimeType===$checked['type'] && $ext=== $checked['ext'] && abs($size-intval($file['size']))<15;
			    if($isFileExist===true){
				$mbSize=(float)$size/MB_IN_BYTES;
				if($mbSize<4){//is below 4mb
				    $isFileExist= sha1_file($duplicate)===sha1_file($file['tmp_name']);
				}
				else{
				    $chunkSize=4096;
				    $arr=self::compare_files($duplicate,$file['tmp_name'],$chunkSize);
				    if($arr!==false){
					$maxCheck=4*MB_IN_BYTES;//check only first 4mb
					$i=(int)$maxCheck/$chunkSize;
					foreach($arr as $k=>$v){
					    if($k===false || $v===false || $v!==$k){
						$isFileExist=false;
						break;
					    }
					    if($i<=0){
						break;
					    }
					    --$i;
					}
					unset($maxCheck,$chunkSize,$i);
				    }
				    else{
					$isFileExist=false;
				    }
				    unset($arr);
				}
				unset($duplicate,$mbSize);
			    }

			    $post_id = !empty($post['post_id'])?(int) $post['post_id']:0;
			    $term_id=(!$post_id && !empty($post['term_id']))?(int) $post['term_id']:0;
			    if($isFileExist===false){
				$check= function_exists('check_upload_size')?check_upload_size($file):array('error'=>0);
				if($check['error']===0){
				    $attach_id = media_handle_sideload( $file, $post_id );
				    if(!$attach_id){
					$error=__('Uploading Error', 'themify');
				    }
				    elseif(is_wp_error($attach_id)){
					$error=$attach_id->get_error_messages();
				    }
				}
				else{
				    $error=$check['error'];
				}
				unset($check);
			    }
			}
			else{
			    $error=__('Invalid file type', 'themify');
			}
		    }
		    if(isset($file)){
			if(is_file($file['tmp_name'])){
			    unlink($file['tmp_name']);
			}
			unset($file,$thumb);
			if(isset($tmp)){
			    unset($tmp);
			}
		    }
		    if($error==='' && !empty($attach_id)){
			if($post_id>0){
			    set_post_thumbnail( $post_id, $attach_id );
			}
			elseif($term_id>0){
			    update_term_meta( $term_id, 'thumbnail_id', $attach_id );
			}
			unset($post_id,$term_id);
			$imageMeta=wp_get_attachment_metadata($attach_id);
			if(empty($imageMeta['file'])){
			    $data=wp_get_attachment_image_src($attach_id,'full');
			    $src=$data[0];
			    $w=$h='';
			    if(empty($data[1])){
				$size=themify_get_image_size($src);	
				if($size!==false){
				    $w=$size['w'];
				    $h=$size['h']; 
				}
			    }
			    else{
				$w=$data[1];
				$h=$data[2];
			    }
			    $imageSizes['_orig_']=array('file'=> $src,$w,'height'=>$h);
			    unset($data);
			}
			else{
			    $imageSizes=!empty($imageMeta['sizes'])?$imageMeta['sizes']:array();
			    $imageSizes['_orig_']=array('file'=> basename($imageMeta['file']),'width'=>$imageMeta['width'],'height'=>$imageMeta['height']);
			    $folder='/'.dirname($imageMeta['file']).'/';
			    $baseUrl= themify_upload_dir('baseurl').$folder;
			    unset($imageMeta);
			    if($generate===true){
				foreach($imageSizes as $v){
				    themify_get_image_size($baseUrl.$v['file']);
				    themify_createWebp($baseUrl.$v['file']);
				    themify_get_placeholder($baseUrl.$v['file']);
				}
			    }
			    $src=$baseUrl.$imageSizes['_orig_']['file'];
			}
			$img='<img src="'.$src.'" class="wp-image-'.$attach_id.'"';
			if(!empty($imageSizes['_orig_']['width'])){
			    $img.=' width="'.$imageSizes['_orig_']['width'].'" height="'.$imageSizes['_orig_']['height'].'"';
			}
			$img.='>';
			unset($imageSizes);
			$img=function_exists('wp_filter_content_tags') ? wp_filter_content_tags($img): wp_make_content_images_responsive($img);
			return array('html'=>$img,'id'=>$attach_id,'src'=>$src);
		    }
		}
		return $error;
	    }
	}
}
if(themify_is_ajax()){
    Themify_Import_Helper::init();
}