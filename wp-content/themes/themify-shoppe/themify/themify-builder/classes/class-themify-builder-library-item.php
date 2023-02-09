<?php

/**
 * This file defines Builder Library Items designs and parts.
 *
 * Themify_Builder_Row class register post type for Library Items designs and Parts
 * Custom metabox, and load Library Items designs / parts.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Library Items class.
 *
 * This class register post type for Library Items designs and Parts
 * Custom metabox, and load Library Items designs / parts
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Library_Items {

    private static $post_type_name = array('row' => 'library_rows', 'module' => 'library_modules', 'part' => 'tbuilder_layout_part');

    /**
     * Constructor
     * 
     * @access public
     */
    public static function init() {
        if (themify_is_ajax()) {
            // Ajax Hooks
            add_action('wp_ajax_tb_save_custom_item', array(__CLASS__, 'save_custom_item_ajaxify'));
            add_action('wp_ajax_tb_get_library_items', array(__CLASS__, 'list_library_items_ajax'));
            add_action('wp_ajax_tb_get_library_item', array(__CLASS__, 'get_item'));
            add_action('wp_ajax_tb_remove_library_item', array(__CLASS__, 'remove_library_item_ajax'));
            add_action('wp_ajax_tb_layout_part_swap', array(__CLASS__, 'layout_part_edit'));
        }
    }

    /**
     * Save as Row
     * 
     * @access public
     */
    public static function save_custom_item_ajaxify() {

		check_ajax_referer( 'tf_nonce', 'nonce' );
		$response = array(
			'status' => 'failed',
			'msg' => __( 'Something went wrong', 'themify' )
		);
		if (!empty( $_POST['item'] ) ) {
		    
			$is_layout_part = !empty( $_POST['item_layout_save'] ) && $_POST['item_layout_save'] !== 'false';
			$data = array(
				'type' => $_POST['type'],
				'item' => $is_layout_part ? json_decode( stripslashes_deep( $_POST['item'] ), true ) : $_POST['item']
			);
			$user = get_current_user_id();
			if ( !empty( $_POST['item_title_field'] ) ) {
				$title = sanitize_text_field( $_POST['item_title_field'] );
			} else {
				$title = $is_layout_part ? __( 'Saved Item Layout Part', 'themify' ) : $user . ' Saved-' . ucwords( sanitize_text_field( $data['type'] ) );
			}
			$post_type = $is_layout_part ? self::$post_type_name['part'] : self::$post_type_name[ $data['type'] ];
			$new_id = wp_insert_post( array(
				'post_status' => 'publish',
				'post_type' => $post_type,
				'post_author' => $user,
				'post_title' => $title,
				'post_content' => $is_layout_part ? '' : $data['item']
			) );
			if ( $new_id ) {
				if ( $is_layout_part ) {
					$response = self::save_as_layout_part( $data, $new_id );
				} else {
					$response['status'] = 'success';
					unset( $response['msg'] );
				}
				if(!empty($_POST['usedGS'])){
					update_post_meta( $new_id, 'themify_used_global_styles', $_POST['usedGS'] );
				}
				$response['post_type'] = $post_type;
				$response['id'] = $new_id;
				$response['post_title'] = $title;
			}
		}
		wp_send_json( $response );
    }

    /**
     * Save the item as Layout Part.
     * 
     * @access public
     * Return Array
     */
    protected static function save_as_layout_part($data, $new_id) {

        if ($data['type'] === 'module') {
            $row = array('cols' => array(0 => array('grid_class' => 'col-full',  'modules' => array($data['item']))));
            ThemifyBuilder_Data_Manager::save_data(array($row), $new_id);
        } else {
            ThemifyBuilder_Data_Manager::save_data(array($data['item']), $new_id);
        }
	$post = get_post($new_id);
        return array(
            'status' => 'success',
            'post_name' => $post->post_name
        );
    }

    /**
     * Get layout part module settings.
     * 
     * @access Private
     * Retrun Array
     */
    protected static function get_layout_part_model($post, $type) {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        $output = array(
            'mod_name' => 'layout-part',
            'mod_settings' => array(
                'selected_layout_part' => $post->post_name
            )
        );
        if ($type === 'row') {
            $output = array( 'cols' => array(0 => array('grid_class' => 'col-full', 'modules' => array($output))));
        }
        return $output;
    }

    /**
     * Get list of Saved Layout Parts in library.
     * 
     * @access Private
     * Retrun Array or String
     */
    protected static function get_list($type = 'all') {
        global $wpdb;
        $vals = $type === 'all' ? self::$post_type_name : array(self::$post_type_name[$type]);
        $post_type = array();
        foreach ($vals as $v) {
            $post_type[] = "'" . esc_sql($v) . "'";
        }
        $post_type = implode(',', $post_type);
        return $wpdb->get_results("SELECT post_name,post_title,post_type,id FROM {$wpdb->posts} WHERE post_type IN(" . $post_type . ") and post_status = 'publish' ORDER BY post_title ASC ", ARRAY_A);
    }

    /**
     * Get list of Saved Rows & Modules in library.
     * 
     * @access public
     */
    public static function list_library_items_ajax() {
        check_ajax_referer('tf_nonce', 'nonce');
        $part = !empty($_POST['part']) ? $_POST['part'] : 'part';
        if ($part !== 'all' && !isset(self::$post_type_name[$part])) {
            wp_die();
        }
        wp_send_json(self::get_list($part));
    }

    public static function remove_library_item_ajax() {
        check_ajax_referer('tf_nonce', 'nonce');

        $id = (int) $_POST['id'];
        $post = get_post($id);
        $status = 0;
        if (in_array($post->post_type, self::$post_type_name, true)) {
            $status = self::$post_type_name['part'] === $post->post_type ? wp_trash_post($id) : wp_delete_post($id);
            $status = is_wp_error($status) ? 0 :$post->post_name;
        }
        die("$status");
    }

    public static function get_item() {
        check_ajax_referer('tf_nonce', 'nonce');
        $id = (int) $_POST['id'];
        $post = get_post($id);
        $msg = array('status' => false);
        if (in_array($post->post_type, self::$post_type_name, true)) {
            setup_postdata($post);
            $msg['status'] = 'success';
            $msg['content'] = $post->post_type === self::$post_type_name['part'] ? self::get_layout_part_model($post, $_POST['type']) : json_decode($post->post_content, true);
            if($post->post_type !== self::$post_type_name['part']){
		$usedGS = Themify_Global_Styles::used_global_styles($id);
		if(!empty($usedGS)){
		    $msg['content']['gs'] = $usedGS;
		}
	    }
        }
        wp_send_json($msg);
    }

    public static function layout_part_edit() {
        check_ajax_referer('tf_nonce', 'nonce');
        if (!empty($_POST['bid'])) {
            $id=(int)$_POST['bid'];
            $response['builder_data'] =  ThemifyBuilder_Data_Manager::get_data($id);
	    // Return used gs if used
	    $usedGS = Themify_Global_Styles::used_global_styles($id);
	    if(!empty($usedGS)){
		    $response['used_gs'] = $usedGS;
	    }
            $custom_css = get_post_meta($id, 'tbp_custom_css', true);
            if(!empty($custom_css)){
                $response['custom_css'] = $custom_css;
            }
            echo wp_json_encode($response);
        }
        wp_die();
    }

}
