<?php
/**
 * Enable using a custom page as 404 page
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */
class Themify_Custom_404 {

	public static function init() {
		if ( is_404() && self::get_page() ) {

			/* do redirect_canonical early, handles all redirects needed for WP before changing the query */
			redirect_canonical();
			remove_filter( 'template_redirect', 'redirect_canonical' );

			/* replace the 404 query with a custom WP_Query that displays the chosen page */
			global $wp_query, $wp_the_query;
			$wp_query = $wp_the_query = self::get_404_query();
			$posts = $wp_query->posts;
			$wp_query->rewind_posts();

			global $themify;
			$themify->is_custom_404 = true;

			self::set_headers();

			add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		}
	}
        
	/**
	 * Get the 404 page
	 *
	 * @return WP_Post|false
	 */
	public static function get_page() {
		static $id = null;
		if ( $id === null ) {
			$id = (int) themify_get( 'setting-page_404', false, true );
			if ( $id !== 0 ) {
				$id = themify_maybe_translate_object_id( $id );
				$id = ! empty( $id ) ? get_post( $id ) : false;
			}
		}

		return $id;
	}

	private static function get_404_query() {
		$query = new WP_Query();
		$query->query( array(
			'page_id' => self::get_page()->ID,
			'suppress_filters' => true,
		) );
		$query->the_post();

		return $query;
	}

	private static function set_headers() {
		status_header( 404 );
		nocache_headers();
	}

	public static function body_class( $classes ) {
		$classes[] = 'error404';

		return $classes;
	}

	/**
	 * Helper function, returns true only if is_404 and is using custom page
	 *
	 * @return bool
	 */
	public static function is_custom_404() {
		global $themify;
		return isset( $themify->is_custom_404 ) && $themify->is_custom_404 === true;
	}
}
add_action( 'template_redirect', [ 'Themify_Custom_404', 'init' ], 0 );