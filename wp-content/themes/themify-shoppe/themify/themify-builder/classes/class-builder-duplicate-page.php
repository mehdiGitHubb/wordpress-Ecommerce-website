<?php
/**
 * Builder Duplicate API
 *
 * This class provide api to duplicate post or page including the builder data.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Duplicate builder class.
 *
 * Main class to handle duplicate post/page with it's builder data.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Duplicate_Page {
	
        
	/**
	 * Class Constructor.
	 * 
	 * @access public
	 */
	public static function init() {
		// Actions
		add_action( 'themify_builder_duplicate', array( __CLASS__, 'duplicate_data' ), 10, 2 );
		add_action( 'wp_ajax_tb_duplicate_page', array( __CLASS__, 'duplicate_page_ajaxify' ), 10 );
	}

	/**
	 * Duplicate page
	 */
	public static function duplicate_page_ajaxify() {
		check_ajax_referer('tf_nonce', 'nonce');
		if(!empty($_POST['bid'])){
		    $post_id = (int) $_POST['bid'];
		    $post = get_post($post_id);
		    if( is_object($post) ) {
			    $new_post_id=self::duplicate($post);
			    if($new_post_id>0 && !is_string($new_post_id)){
				unset($post);
				$new_url = !empty($_POST['tb_is_admin']) &&  intval($_POST['tb_is_admin'])===1?get_edit_post_link( $new_post_id,'json' ):get_permalink( $new_post_id );
				wp_send_json_success($new_url);
			    }
			    else{
				if($new_post_id===0){
				    $new_post_id=__('There is an error on duplicating page','themify');
				}
				wp_send_json_error($new_post_id);
			    }
		    }
		}
		wp_die();
	}


	/**
	 * Perform duplicating post/page.
	 * 
	 * @access public
	 * @param object $post
	 * @param string $status
	 * @param string $parent_id
	 * @return int
	 */
	public static function duplicate( $post, $status = '', $parent_id = '' ) {
		// We don't want to clone revisions
		if ( $post->post_type === 'revision' ){
			return;
		}

		$prefix = $suffix = '';

		if ( $post->post_type !== 'attachment' ) {
			$suffix = ' Copy';
		}
		$new_post_author = wp_get_current_user();

		$new_post = array(
			'menu_order' => $post->menu_order,
			'comment_status' => $post->comment_status,
			'ping_status' => $post->ping_status,
			'post_author' => $new_post_author->ID,
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_mime_type' => $post->post_mime_type,
			'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
			'post_password' => $post->post_password,
			'post_status' => $new_post_status = (empty($status))? $post->post_status: $status,
			'post_title' => $prefix.$post->post_title.$suffix,
			'post_type' => $post->post_type
		);

		$new_post_id = wp_insert_post( $new_post );
		if($new_post_id>0 && !is_wp_error($new_post_id)){
		    // apply hook to duplicate action
		    do_action( 'themify_builder_duplicate', $new_post_id, $post );

		    delete_post_meta( $new_post_id, '_themify_builder_dp_original' );
		    add_post_meta( $new_post_id, '_themify_builder_dp_original', $post->ID );

		    // If the copy is published or scheduled, we have to set a proper slug.
		    if ( $new_post_status === 'publish' || $new_post_status === 'future' ) {
			    $post_name = wp_unique_post_slug( $post->post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent );
			    $new_post = array();
			    $new_post['ID'] = $new_post_id;
			    $new_post['post_name'] = $post_name;

			    // Update the post into the database
			    $new_post_id=wp_update_post( $new_post );
			    if(is_wp_error($new_post_id)){
				$new_post_id=$new_post_id->get_error_message();
			    }
		    }
		}
		elseif(is_wp_error($new_post_id)){
		    $new_post_id=$new_post_id->get_error_message();
		}
		return $new_post_id;
	}

	/**
	 * Duplicate custom fields / post meta.
	 * 
	 * @access public
	 * @param int $new_id
	 * @param object $post
	 */
	private static function duplicate_postmeta( $new_id, $post ) {
		$meta_keys = get_post_custom_keys( $post->ID );
		if ( empty( $meta_keys ) ){
			return;
		}
		foreach ( $meta_keys as $meta_key ) {
			if( $meta_key === '_themify_builder_settings_json' ) {
				$builder_data = ThemifyBuilder_Data_Manager::get_data( $post->ID ); // get builder data from original post
				$builder_data=Themify_Builder_Model::removeElementIds((array)$builder_data);
				ThemifyBuilder_Data_Manager::save_data( $builder_data, $new_id ); // save the data for the new post
			} 
			elseif (!self::exclude_postmeta( $meta_key ) ) {
			    $meta_values = get_post_custom_values( $meta_key, $post->ID );
			    foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				update_post_meta( $new_id, $meta_key, $meta_value );
			    }
			}
		}
	}

	/**
	 * Check if a custom field must NOT be copied to duplicated post
	 *
	 * @return bool
	 */
	private static function exclude_postmeta( $meta_key ) {
		/*oEmbed cache data or WP post edit*/
		return substr( $meta_key, 0, 7 ) === '_oembed'  || in_array( $meta_key, array( '_edit_last', '_edit_lock' ),true) ;
	}

	/**
	 * Duplicate categories and custom taxonomies
	 * 
	 * @access public
	 * @param int $new_id
	 * @param object $post
	 */
	private static function duplicate_taxonomies( $new_id, $post ) {
		global $wpdb;
		if ( isset( $wpdb->terms ) ) {
			// Clear default category (added by wp_insert_post)
			wp_set_object_terms( $new_id, NULL, 'category' );

			$taxonomies = get_object_taxonomies( $post->post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'orderby' => 'term_order' ) );
				$terms = array();
				$terms_count = count( $post_terms );
				for ( $i=0; $i < $terms_count; ++$i ) {
					$terms[] = $post_terms[ $i ]->slug;
				}
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}
	}

	/**
	 * Duplicate attachment data entries
	 * 
	 * @access public
	 * Actual files does not copied
	 * @param int $new_id
	 * @param object $post
	 */
	private static function duplicate_attachment( $new_id, $post ) {
		// get children
		$children = get_posts( 
		array( 'post_type' =>get_post_types(), 
		    'numberposts' => -1,
		    'post_status' => 'any', 
		    'post_parent' => $post->ID,
		    'ignore_sticky_posts'=>true,
		    'no_found_rows'=>true,
		    'cache_results'=>false) );
		// clone old attachments
		foreach ( $children as $child ) {
		    if ( $child->post_type !== 'attachment' && $child->post_type!==$post->post_type){
			self::duplicate( $child, '', $new_id );
		    }
		}
	}

	public static function duplicate_data($new_id, $post ){
		self::duplicate_postmeta($new_id, $post);
		self::duplicate_taxonomies($new_id, $post);
		self::duplicate_attachment($new_id, $post);
	}
}

Themify_Builder_Duplicate_Page::init();