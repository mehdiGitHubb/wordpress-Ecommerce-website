<?php

defined( 'ABSPATH' ) || exit;
/**
 * Add Page Options for selective themes, only in Builder plugin version
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */
class Themify_Builder_Builder_Page_Options {

	static function init() {
		if ( is_admin() ) {
			add_filter( 'themify_metabox/fields/themify-meta-boxes', [ __CLASS__, 'metaboxes' ], 10, 2 );
		} else {
			add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
			add_filter( 'themify_builder_fullwidth_layout_support', [ __CLASS__, 'themify_builder_fullwidth_layout_support' ] );
			add_filter( 'template_include', [ __CLASS__, 'template_include' ] );
			add_action( 'wp_footer', [ __CLASS__, 'wp_footer' ], 18 );
		}
	}

	static function metaboxes( $meta_boxes = array(), $post_type = 'all' ) {
		return array_merge( array(
			array(
				'name' => __( 'Page Options', 'themify' ),
				'id' => 'builder-plugin-page-options',
				'options' => self::options(),
				'pages' => [ 'page' ],
			)
		), $meta_boxes );

	}

	static function options() {
		$options = [
			[
				'name' => '_tb_template',
				'title' => __( 'Header/Footer', 'themify' ),
				'description' => '',
				'type' => 'dropdown',
				'meta' => [
					[ 'value' => '', 'name' => __( 'Default', 'themify' ) ],
					[ 'value' => 'blank', 'name' => __( 'No header/footer', 'themify' ) ],
				],
				'hide' => 'blank tb_template_none',
			],
		];

		if ( current_theme_supports( 'themify-page-options' ) ) {
			$options[] = array(
				'name' => 'content_width',
				'title' => __('Content Width', 'themify'),
				'description' => '',
				'type' => 'layout',
				'show_title' => true,
				'meta' => array(
					array(
						'value' => 'default_width',
						'img' => THEMIFY_URI . '/img/default.svg',
						'selected' => true,
						'title' => __('Default', 'themify')
					),
					array(
						'value' => 'full_width',
						'img' => THEMIFY_URI . '/img/fullwidth.svg',
						'title' => __('Fullwidth', 'themify')
					)
				),
				'default' => 'default_width',
				'class' => 'tb_template_none',
				
			);
			$options[] = array(
				'name' => 'hide_post_title',
				'title' => __('Hide Title', 'themify'),
				'description' => '',
				'type' => 'dropdown',
				'meta' => array(
					array('value' => 'default', 'name' => '', 'selected' => true),
					array('value' => 'yes', 'name' => __('Yes', 'themify')),
				),
				'default' => 'default',
				'class' => 'tb_template_none',
			);
		}

		return $options;
	}

	/**
	 * Body classes
	 *
	 * @return array
	 */
	static function body_class( $classes ) {
		if ( is_page() ) {
			if ( themify_get( 'content_width' ) === 'full_width' ) {
				$classes[] = 'tb_fullwidth';
			}
			if ( themify_get( 'hide_post_title' ) === 'yes' ) {
				$classes[] = 'tb_hide_title';
			}
		}

		return $classes;
	}

	/**
	 * Disable the fullwidthRows.js if page is fullwidth (this is handled by CSS)
	 *
	 * @return bool
	 */
	static function themify_builder_fullwidth_layout_support( $support ) {
		if ( is_page() && themify_get( 'content_width' ) === 'full_width' ) {
			$support = true;
		}

		return $support;
	}

	static function template_include( $template ) {
		if ( is_page() && themify_get( '_tb_template' ) === 'blank' ) {
			$template = THEMIFY_BUILDER_TEMPLATES_DIR . '/page-template-blank.php';
		}

		return $template;
	}

	static function wp_footer() {
            if ( current_theme_supports( 'themify-page-options' ) ) {
		$template = get_template();
		themify_enque_style( "tb_{$template}", THEMIFY_BUILDER_URI . '/theme-compat/' . $template . '.css', null, THEMIFY_VERSION );
            }
	}
}
add_action( 'init', [ 'Themify_Builder_Builder_Page_Options', 'init' ] );