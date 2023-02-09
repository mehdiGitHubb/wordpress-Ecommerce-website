<?php

/**
 * The file that enable builder revisions.
 *
 * Themify_Builder_Revisions class provide hooks and filter to WP Revisions API
 * This enable builder being tracked by WP Revisions and able to restore
 * the revision for builder.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Revision class.
 *
 * This is used to handle all revisions operation and method.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
final class Themify_Builder_Revisions {

    const REVISION_LIMIT = 50;

    /**
     * Constructor.
     * 
     * @param object Themify_Builder $builder 
     */
    public static function init() {

        $ajax_events = array(
            'load_revision_lists',
            'save_revision',
            'restore_revision_page',
            'delete_revision'
        );

        foreach ($ajax_events as $ajax_event) {
            add_action('wp_ajax_tb_' . $ajax_event, array(__CLASS__, $ajax_event));
        }

        add_filter('_wp_post_revision_fields', array(__CLASS__, 'post_revision_fields'), 10, 2);

        add_action('wp_restore_post_revision', array(__CLASS__, 'restore_revision'), 10, 2);
    }

    /**
     * Ajax Get all post revisions list.
     * 
     * @access public
     */
    public static function load_revision_lists() {

        check_ajax_referer('tf_nonce', 'nonce');

        $post_id = (int) $_POST['bid'];
        $revisions = wp_get_post_revisions($post_id, array(
            'posts_per_page' => self::REVISION_LIMIT,
        ));
        $can_edit_post = $post_id === 0 ? true : current_user_can('edit_post', $post_id);
        include THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/tmpl-revisions.php';
        wp_die();
    }

    /**
     * Hook themify builder field to revisions fields.
     * 
     * @access public
     * @param array $fields 
     * @return array
     */
    public static function post_revision_fields($fields, $post) {
        if (function_exists('wp_print_revision_templates')) {
            $fields[ThemifyBuilder_Data_Manager::META_KEY] = esc_html__('Themify Builder', 'themify');
            add_filter('_wp_post_revision_field__themify_builder_settings_json', array(__CLASS__, 'post_revision_field'), 10, 4);
        }
        return $fields;
    }

    /**
     * Render the builder output in revision compare slider.
     * 
     * @access public
     * @param string $value 
     * @param string $field 
     * @return string
     */
    public static function post_revision_field($value, $field, $revision, $type) {
        if (is_object($revision)) {
            $builder_data = ThemifyBuilder_Data_Manager::get_data($revision->ID);

            if (!empty($builder_data)) {
                return Themify_Builder_Component_Base::retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $revision->ID), THEMIFY_BUILDER_TEMPLATES_DIR, '', false);
            }
        }
    }

    /**
     * Ajax save revision.
     * 
     * @access public
     */
    public static function save_revision() {

        check_ajax_referer('tf_nonce', 'nonce');
        /* set a default limit for revisions */
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', self::REVISION_LIMIT);
        }
	if ( isset( $_POST['data'] ) ) {
	    $data = stripslashes_deep( $_POST['data'] );
	} 
	elseif ( isset( $_FILES['data'] ) ) {
	    $data = file_get_contents( $_FILES['data']['tmp_name'] );
	}
	if(!empty($data)){
	    $data = json_decode( $data, true );
	    if(!empty($data)){
		$post_id = (int) $_POST['bid'];
		$post = get_post($post_id);
		$rev_comment = !empty($_POST['rev_comment']) ? sanitize_text_field($_POST['rev_comment']) : '';
		if (!current_user_can('edit_post', $post_id))
		    wp_send_json_error(esc_html__('Error. You do not have access to save revision.', 'themify'));

		if (!self::is_revision_enabled($post))
		    wp_send_json_error(esc_html__('Error. The revision is not enable in this post or has been reach the revision post limit.', 'themify'));

		if (is_object($post)) {
		    $post = get_object_vars($post);
		}
		unset($post['post_modified'], $post['post_modified_gmt']);
		$new_revision_id = _wp_put_post_revision($post);
		if (!is_wp_error($new_revision_id)) {
		    if (!empty($rev_comment)) {
			update_metadata('post', $new_revision_id, '_builder_custom_rev_comment', $rev_comment);
		    }
		    ThemifyBuilder_Data_Manager::save_data($data, $new_revision_id, $_POST['sourceEditor']);
		    wp_send_json_success($new_revision_id);
		} else {
		    wp_send_json_error(esc_html__('Cannot save revision, please try again.', 'themify'));
		}
	    }
	    else{
		wp_send_json_success();
	    }
	}
	else{
	    wp_send_json_error(esc_html__('Cannot save revision, please try again.', 'themify'));
	}
        wp_die();
    }

    /**
     * Hook to restore revision.
     * 
     * @access public
     * @param int $post_id 
     * @param int $rev_id 
     */
    public static function restore_revision($post_id, $rev_id) {
        $builder_data = ThemifyBuilder_Data_Manager::get_data($rev_id);
        if (!empty($builder_data)) {
            ThemifyBuilder_Data_Manager::save_data($builder_data, $post_id);
        }
    }

    /**
     * Ajax restore revision.
     * 
     * @access public
     */
    public static function restore_revision_page() {
        check_ajax_referer('tf_nonce', 'nonce');
        $rev_id = (int) $_POST['revid'];
        $revision = wp_get_post_revision($rev_id);

        if (!current_user_can('edit_post', $revision->post_parent)) {
            wp_send_json_error(esc_html__('Error. You do not have access to restore revision.', 'themify'));
            return;
        }

        if ($revision) {
	    wp_send_json(array('builder_data' => ThemifyBuilder_Data_Manager::get_data($rev_id)));
        } else {
            wp_send_json_error(esc_html__('Revision post is not found or invalid ID', 'themify'));
        }
        wp_die();
    }

    /**
     * Ajax delete revision.
     * 
     * @access public
     * @return json
     */
    public static function delete_revision() {
        check_ajax_referer('tf_nonce', 'nonce');
        $rev_id = (int) $_POST['revid'];
        $revision = wp_get_post_revision($rev_id);
        if (!current_user_can('edit_post', $revision->post_parent)) {
            wp_send_json_error(esc_html__('Error. You do not have access to delete revision.', 'themify'));
            return;
        }

        $delete = wp_delete_post_revision($rev_id);
        if (!is_wp_error($delete)) {
            wp_send_json_success($rev_id);
        } else {
            wp_send_json_error(esc_html__('Unable to delete this revision, please try again!', 'themify'));
        }
        wp_die();
    }

    /**
     * create builder revision
     * 
     * @access public
     * @param int $post_id 
     * @param object $post 
     */
    public static function create_revision($post_id, $builder_data, $action) {
        if (!wp_is_post_revision($post_id)) {
            $post = get_post($post_id);
            if (!empty($post) && 'auto-draft' !== $post->post_status && wp_revisions_enabled($post) && post_type_supports($post->post_type, 'revisions')) {
                unset($post->post_modified, $post->post_modified_gmt);
                $rev_id = _wp_put_post_revision($post);
                if (!is_wp_error($rev_id)) {
                    if (empty($builder_data)) {
                        $builder_data = array();
                    }
                    ThemifyBuilder_Data_Manager::save_data($builder_data, $rev_id, $action);
                    return $rev_id;
                }
            }
        }
        return false;
    }

    /**
     * Check if revision has builder data.
     * 
     * @access public
     * @param int $post_id 
     * @return boolean
     */
    public static function check_has_builder($post_id) {
        $builder_data = get_metadata('post', $post_id, ThemifyBuilder_Data_Manager::META_KEY, true);
        return !empty($builder_data);
    }
    
    public static function is_revision_enabled($post){
	$p=get_post($post);
	return wp_revisions_enabled($p) && post_type_supports($p->post_type, 'revisions');
    }
}
