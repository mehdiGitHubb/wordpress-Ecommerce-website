<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

class Themify_Builder_Plugin_Compat_facetwp {

	static function init() {
		add_action( 'themify_builder_admin_enqueue', [ __CLASS__, 'admin_enqueue' ] );
		add_action( 'themify_builder_frontend_enqueue', [ __CLASS__, 'admin_enqueue' ] );
		add_action( 'themify_builder_module_classes', [ __CLASS__, 'themify_builder_module_classes' ], 1, 4 );
	}

	public static function admin_enqueue() {
		wp_enqueue_script( 'tb-facetwp-admin', themify_enque( THEMIFY_BUILDER_URI .'/includes/plugin-compat/js/facetwp-admin.js' ), [ 'themify-builder-app-js' ], THEMIFY_VERSION, true );
		wp_localize_script( 'tb-facetwp-admin', 'tbFacet', [
			'label' => __( 'FacetWP', 'themify' ),
			'desc' => __( 'Enable integration with FacetWP plugin, the posts display in this module can be filtered.', 'themify' ),
		] );
	}

	public static function themify_builder_module_classes( $classes, $mod_name, $element_id, $options ) {
		if ( empty( $options['facetwp'] ) ) {
			return $classes;
		}

		$classes[] = 'facetwp-template';
		add_action( 'pre_get_posts', [ __CLASS__, 'pre_get_posts' ] );
		add_action( 'facetwp_assets', [ __CLASS__, 'facetwp_assets' ] );

		return $classes;
	}

	/**
	 * Enable filtering the wp_query by facet
	 */
	static function pre_get_posts( $query ) {
		remove_action( 'pre_get_posts', [ __CLASS__, 'pre_get_posts' ] );
		$query->set( 'facetwp', true );
	}

	/**
	 * Load frontend scripts for fixing display of posts
	 */
	static function facetwp_assets( $assets ) {
		$assets['tb-facetwp-front'] = themify_enque( THEMIFY_BUILDER_URI .'/includes/plugin-compat/js/facetwp-front.js' );
		return $assets;
	}
}