<?php
/**
 * Themify Maintenance Mode
 *
 * @package    Themify
 */
class Themify_Maintenance_Mode {

	public static function init() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_themify_load_maintenance_pages', [ __CLASS__, 'wp_ajax_themify_load_maintenance_pages' ] );
		}
		if ( self::is_enabled() ) {
			if ( ! is_admin() ) {
				/* Priority = 11 that is *after* WP default filter `redirect_canonical` in order to avoid redirection loop. */
				add_action( 'template_redirect', [ __CLASS__, 'template_redirect' ], 11 );
			}
			if ( current_user_can( 'manage_options' ) ) {
				if ( isset( $_GET['tf_disable_maintenance'], $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'tf_disable_maintenance' ) ) {
					self::disable();
				} else {
					add_action( 'admin_bar_menu', [ __CLASS__, 'admin_bar_menu' ], 500 );
				}
			}
		}
	}

	public static function template_redirect() {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		$enabled = self::is_enabled();
		if ( 'on' === $enabled ) {
			$selected_value = themify_builder_get( 'setting-page_builder_maintenance_page', 'tools_maintenance_page' );
			$selected_page = empty( $selected_value ) ? '' : get_page_by_path($selected_value, OBJECT, 'page');
			if ( ! empty( $selected_page ) && ! is_page( $selected_value ) ) {
				exit( wp_redirect( get_page_link( $selected_page->ID ) ) );
			}
		} elseif ( 'message' === $enabled ) {
			$message = themify_builder_get( 'setting-maintenance_message', 'tools_maintenance_message' );
			wp_die( $message );
		}
	}

	public static function admin_bar_menu( $admin_bar ) {
		$admin_bar->add_menu( [
			'id'    => 'tf_maintenance_mode',
			'title' => '<span class="tf_admin_bar_tooltip">' . __( 'Warning: Maintenance Mode is enabled, website is disabled for public visitors. Be sure to deactivate this once your website is ready.', 'themify' ) . '</span>' . __( 'Maintenance Mode', 'themify' ),
			'meta' => [
				'class' => 'tf_admin_bar_alert',
			],
		] );
		$admin_bar->add_menu( [
			'id'    => 'tf_maintenance_mode_disable',
			'title' => __( 'Disable Maintenance Mode', 'themify' ),
			'href' => add_query_arg( [
				'tf_disable_maintenance' => 1,
				'_wpnonce' => wp_create_nonce( 'tf_disable_maintenance' ),
			] ),
			'parent' => 'tf_maintenance_mode',
		] );
	}

	/**
	 * Load pages for maintenance page dropdown
	 */
	public static function wp_ajax_themify_load_maintenance_pages() {
		check_ajax_referer( 'tf_nonce', 'nonce' );
		$pages = get_pages();
		$selected = themify_builder_get( 'setting-page_builder_maintenance_page', 'tools_maintenance_page' );
		$output = '<option></option>';
		foreach ( $pages as $page ) {
			$val = $page->post_name;
			$post_parent = $page->post_parent;
			while ( $post_parent !== 0 ) {
				$post_aux = get_post( $post_parent );
				$val = $post_aux->post_name.'/'.$val;
				$post_parent = $post_aux->post_parent;
			}
			$output .= sprintf( '<option value="%s"%s>%s</option>',
				$val,
				selected( $val, $selected,false ),
				$page->post_title
			);
		}
		echo $output;
		wp_die();
	}

	/**
	 * Returns true if maintenance mode is enabled
	 *
	 * @return string|false
	 */
	public static function is_enabled() {
		static $is = null;
		if ( $is === null ) {
			$is = themify_builder_get( 'setting-page_builder_maintenance_mode', 'tools_maintenance_mode' );
			if ( ! in_array( $is, [ 'on', 'message' ], true ) ) {
				$is = false;
			}
		}

		return $is;
	}

	/**
	 * Disable the Maintenance mode
	 */
	public static function disable() {
		if ( themify_is_themify_theme() ) {
			$data = themify_get_data();
			unset( $data['setting-page_builder_maintenance_mode'] );
			themify_set_data( $data );
		} else {
			$data = get_option( 'themify_builder_setting', [] );
			unset( $data['tools_maintenance_mode'] );
			update_option( 'themify_builder_setting', $data );
		}
	}
}
add_action( 'init', [ 'Themify_Maintenance_Mode', 'init' ] );