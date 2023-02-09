<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * WP Courseware
 * @link https://flyplugins.com/wp-courseware/
 */
class Themify_Builder_Plugin_Compat_wpcourseware {

    static function init() {
        add_action( 'wpcw_unit_after_single_content', array( __CLASS__, 'disbale_builder_content' ) );
    }

    public static function disbale_builder_content() {
        global $ThemifyBuilder;
        remove_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
        add_action('themify_after_post_content',array( __CLASS__, 'enable_builder_content' ),1);
    }

    public static function enable_builder_content() {
        remove_action('themify_after_post_content',array( __CLASS__, 'enable_builder_content' ),1);
        global $ThemifyBuilder;
        add_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
    }
}