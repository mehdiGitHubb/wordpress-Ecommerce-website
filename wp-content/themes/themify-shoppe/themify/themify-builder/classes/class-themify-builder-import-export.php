<?php

/**
 * This file provide a class for Builder Import Export.
 *
 * a class to perform builder import/export operation
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Import Export class.
 *
 * This is used to provide a hook and ajax action to perform Import Export for Builder.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
final class Themify_Builder_Import_Export {

    private static $builder;

    /**
     * Class constructor.
     * 
     * @access public
     */
    public static function init($builder) {
        self::$builder = $builder;
        if (defined('DOING_AJAX')) {

            add_action('wp_ajax_builder_import_submit', array(__CLASS__, 'builder_import_submit_ajaxify'), 10);
            add_action('wp_ajax_builder_import', array(__CLASS__, 'builder_import_ajaxify'), 10);
            add_action('wp_ajax_builder_prepare_export', array(__CLASS__, 'builder_export_ajaxify'), 10);
        }
    }

    /**
     * Prepare Builder data for export
     *
     * @access public
     */
    public static function prepare_builder_data($builder_data) {
        if (!empty($builder_data) && is_array($builder_data)) {
            foreach ($builder_data as &$row) {
                if (isset($row['styling']) && !empty($row['styling']['background_slider'])) {
                    $row['styling']['background_slider'] = self::replace_with_image_path($row['styling']['background_slider']);
                }
                if (!empty($row['cols'])) {
                    foreach ($row['cols'] as &$col) {
                        if (isset($col['styling']) && !empty($col['styling']['background_slider'])) {
                            $col['styling']['background_slider'] = self::replace_with_image_path($col['styling']['background_slider']);
                        }
                        if (!empty($col['modules'])) {
                            foreach ($col['modules'] as &$mod) {
                                if (isset($mod['mod_name']) && $mod['mod_name'] === 'gallery' && !empty($mod['mod_settings']['shortcode_gallery'])) {
                                    $mod['mod_settings']['shortcode_gallery'] = self::replace_with_image_path($mod['mod_settings']['shortcode_gallery']);
                                }
                                // Check for Sub-rows
                                if (!empty($mod['cols'])) {
                                    foreach ($mod['cols'] as &$sub_col) {
                                        if (isset($sub_col['styling']) && !empty($sub_col['styling']['background_slider'])) {
                                            $sub_col['styling']['background_slider'] = self::replace_with_image_path($sub_col['styling']['background_slider']);
                                        }
                                        if (!empty($sub_col['modules'])) {
                                            foreach ($sub_col['modules'] as &$sub_module) {
                                                if (isset($sub_module['mod_name']) && $sub_module['mod_name'] === 'gallery' && !empty($sub_module['mod_settings']['shortcode_gallery'])) {
                                                    $sub_module['mod_settings']['shortcode_gallery'] = self::replace_with_image_path($sub_module['mod_settings']['shortcode_gallery']);
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
     * Perform export file.
     * 
     * @access public
     */
    
    public static function builder_export_ajaxify(){
        check_ajax_referer('tf_nonce', 'nonce');
        if(!empty($_POST['data'])){
	    $shortcodes = json_decode( stripslashes_deep( $_POST['data'] ), true );
	    $res=array();
	    foreach($shortcodes as $k=>$sh){
		$res[$k]=self::replace_with_image_path($sh);
	    }
	    wp_send_json($res);
        }
        wp_die();
    }

    public static function replace_export($builder_data, $post_id) {//deprecated will be removed
        foreach ($builder_data as &$row) {
            if (!empty($row['styling']['background_slider'])) {
                $row['styling']['background_slider'] = self::replace_ids_image_path($row['styling']['background_slider'], $post_id);
            }
            if (!empty($row['cols'])) {
                foreach ($row['cols'] as &$col) {
                    if (!empty($col['styling']['background_slider'])) {
                        $col['styling']['background_slider'] = self::replace_ids_image_path($col['styling']['background_slider'], $post_id);
                    }
                    if (!empty($col['modules'])) {
                        foreach ($col['modules'] as &$mod) {
                            if (isset($mod['mod_name']) && $mod['mod_name'] === 'gallery' && !empty($mod['mod_settings']['shortcode_gallery'])) {
                                $mod['mod_settings']['shortcode_gallery'] = self::replace_ids_image_path($mod['mod_settings']['shortcode_gallery'], $post_id);
                            }
                            // Check for Sub-rows
                            if (!empty($mod['cols'])) {
                                foreach ($mod['cols'] as &$sub_col) {
                                    if (!empty($sub_col['styling']['background_slider'])) {
                                        $sub_col['styling']['background_slider'] = self::replace_ids_image_path($sub_col['styling']['background_slider'], $post_id);
                                    }
                                    if (!empty($sub_col['modules'])) {
                                        foreach ($sub_col['modules'] as &$sub_module) {
                                            if (isset($sub_module['mod_name']) && $sub_module['mod_name'] === 'gallery' && !empty($sub_module['mod_settings']['shortcode_gallery'])) {
                                                $sub_module['mod_settings']['shortcode_gallery'] = self::replace_ids_image_path($sub_module['mod_settings']['shortcode_gallery'], $post_id);
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
     * Replace shortcode gallery with image path
     * 
     * @access public
     * @param string $shortcode
     * @return string
     */
    private static function replace_with_image_path($shortcode) {
        $images = themify_get_gallery_shortcode($shortcode);
        if (!empty($images)) {
            preg_match('/\[gallery.*ids=.(.*).\]/im', $shortcode, $ids);
            $ids = trim($ids[1], '\\');
            $ids = trim($ids, '"');
            $path = array();
            foreach ($images as $img) {
                $path[] = wp_get_attachment_image_url($img->ID, 'full');
            }
            if (!empty($path)) {
                $path = implode(',', $path);
                $shortcode = str_replace('[gallery', '[gallery path="' . $path . '" ', $shortcode);
            }
        }
        return $shortcode;
    }

    /**
     * Get attachment ID by URL.
     * 
     * @access public
     * @param string $url 
     * @return string
     */
    private static function get_attachment_id_by_url($url) {
        // Split the $url into two parts with the wp-content directory as the separator
        $parsed_url = explode(parse_url(WP_CONTENT_URL, PHP_URL_PATH), $url);
        // Get the host of the current site and the host of the $url, ignoring www
        $this_host = str_ireplace('www.', '', parse_url(home_url(), PHP_URL_HOST));
        $file_host = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
        // Return nothing if there aren't any $url parts or if the current host and $url host do not match

        if (!isset($parsed_url[1]) || empty($parsed_url[1]) || ( $this_host != $file_host )) {
            return false;
        }
        // Now we're going to quickly search the DB for any attachment GUID with a partial path match
        // Example: /uploads/2013/05/test-image.jpg
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND guid RLIKE '%s' LIMIT 1", $parsed_url[1]));
        return $attachment ? $attachment[0] : false;
    }

    /**
     * Replace image path if it doesn't exist and replace with the new ids
     * 
     * @access public
     * @param string $shortcode 
     * @param int $post_id 
     * @return string
     */
    private static function replace_ids_image_path($shortcode, $post_id = false) {//deprecated will be removed

        preg_match('/\[gallery.*path.*?=.*?[\'"](.+?)[\'"].*?\]/im', $shortcode, $path);
        if (!empty($path[1])) {
            $path = trim($path[1], '\\');
            $path = trim($path, '"');
            $image_path = explode(',', $path);
            if (!empty($image_path)) {
                $attachment_id = array();
                $wp_upload_dir = themify_upload_dir();
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                foreach ($image_path as $img) {

                    $img_id = self::get_attachment_id_by_url($img);
                    if (!$img_id) {
                        // extract the file name and extension from the url
                        $file_name = basename($img);

                        // get placeholder file in the upload dir with a unique, sanitized filename
                        $upload = wp_upload_bits($file_name, NULL, '');
                        if ($upload['error']) {
                            continue;
                        }
                        // fetch the remote url and write it to the placeholder file
                        $request = new WP_Http;
                        $response = $request->request($img, array('sslverify' => false));

                        // request failed and make sure the fetch was successful
                        if (!$response || is_wp_error($response) || wp_remote_retrieve_response_code($response) != '200') {
                            continue;
                        }

                        $access_type = get_filesystem_method();

                        if ($access_type === 'direct') {
                            $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());

                            if (!WP_Filesystem($creds)) {
                                continue;
                            }

                            global $wp_filesystem;
                            $wp_filesystem->put_contents($upload['file'], wp_remote_retrieve_body($response));
                        } else {
                            continue;
                        }

                        clearstatcache();
                        $filetype = wp_check_filetype($file_name, null);
                        $attachment = array(
                            'guid' => $wp_upload_dir['url'] . '/' . $file_name,
                            'post_mime_type' => $filetype['type'],
                            'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        $img_id = wp_insert_attachment($attachment, $upload['file'], 369);
                        if ($img_id) {
                            $attach_data = wp_generate_attachment_metadata($img_id, $upload['file']);
                            wp_update_attachment_metadata($img_id, $attach_data);
                        }
                    }
                    if ($img_id) {
                        $attachment_id[] = $img_id;
                    }
                }
            }
            $shortcode = str_replace('path="' . $path . '"', '', $shortcode);
            if (!empty($attachment_id)) {
                $attachment_id = implode(',', $attachment_id);
                preg_match('/\[gallery.*ids.*?=.*?[\'"](.+?)[\'"].*?\]/i', $shortcode, $ids);
                $ids = trim($ids[1], '\\');
                $ids = trim($ids, '"');
                $shortcode = str_replace('ids="' . $ids . '"', 'ids="' . $attachment_id . '"', $shortcode);
            }
        }
        return $shortcode;
    }

    /**
     * Builder Import Lightbox
     */
    public static function builder_import_ajaxify() {
        check_ajax_referer('tf_nonce', 'nonce');
        if (!empty($_POST['bid'])) {
            $type = $_POST['type'];
            $id = (int) $_POST['bid'];
            if ('post' === $type || 'page' === $type) {
                $data = array();
                if ('post' === $type) {
                    $max = 15;
                    $post_types = get_post_types(array('_builtin' => false, 'public' => true));
                    $post_types = array('post' => array()) + $post_types;
                    unset($post_types[Themify_Global_Styles::SLUG]);
                } else {
                    $max = 50;
                    $post_types = array($type => array());
                }
                $args = array(
                    'fields' => 'ids',
                    'post_status' => 'publish',
                    'posts_per_page' => $max,
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'cache_results' => false,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'lazy_load_term_meta' => false,
                    'orderby' => 'none',
                    'order' => 'ASC',
                    'meta_query' => array(
                        array(
                            'key' => ThemifyBuilder_Data_Manager::META_KEY,
                            'value' => '',
                            'compare' => '!='
                        )
                    )
                );
                $count = count($post_types);
                foreach ($post_types as $post_type => $v) {
                    $query = new WP_Query();
                    $args['post_type'] = $post_type;
                    $postIds = $query->query($args);
                    if (!empty($postIds)) {
                        $result = array();
                        foreach ($postIds as $post_id) {
                            if ($id !== $post_id) {
                                $result[$post_id] = get_the_title($post_id);
                            }
                        }
                        if (!empty($result)) {
                            $labels = get_post_type_object($post_type);
                            if (!empty($result)) {
                                $result[0] = '';
                            }
                            $data[] = array(
                                'label' => $labels->labels->name,
                                'id' => $post_type,
                                'type' => 'selectSearch',
                                'options' => $result
                            );
                        }
                    }
                    if ($count > 4) {
                        usleep(300);
                    }
                }
                if (!empty($data)) {
                    $data[] = array(
                        'name' => __('Import', 'themify'),
                        'id' => 'tb_submit_import_form',
                        'type' => 'button'
                    );
                } else {
                    $data[] = array(
                        'comment' => sprintf(__('You don`t have %s with Builder content', 'themify'), $type),
                        'type' => 'message'
                    );
                }
                $result = array('import' => array('name' => __('Import From', 'themify'), 'options' => $data));
                wp_send_json($result);
            }
        }
        wp_die();
    }

    /**
     * Process import builder
     */
    public static function builder_import_submit_ajaxify() {
        check_ajax_referer('tf_nonce', 'nonce');
        $response = array();
        if (!empty($_POST['data'])) {
            $imports = json_decode(stripslashes($_POST['data']), true);
            if (!empty($imports) && is_array($imports)) {
                $custom_css = '';
                $builderData = $used_gs = array();

                foreach ($imports as $post_id) {
                    if (!empty($post_id)) {
                        $data = ThemifyBuilder_Data_Manager::get_data($post_id);
                        if (!empty($data)) {
                            $builderData[] = $data;
                            // Used GS
                            $used_gs = array_merge($used_gs, Themify_Global_Styles::used_global_styles($post_id));
                            $custom_css.= get_post_meta($post_id, 'tbp_custom_css', true);
                        }
                    }
                }
                if (!empty($builderData)) {
                    $result = array();
                    foreach ($builderData as $meta) {
                        $result = array_merge($result, (array) $meta);
                    }
                    $response['builder_data'] = $result;
                    if (!empty($used_gs)) {
                        $response['used_gs'] = array_unique($used_gs);
                    }
                    if (!empty($custom_css)) {
                        $response['custom_css'] = $custom_css;
                    }
                }
            }
        }
        wp_send_json($response);
        wp_die();
    }

}
