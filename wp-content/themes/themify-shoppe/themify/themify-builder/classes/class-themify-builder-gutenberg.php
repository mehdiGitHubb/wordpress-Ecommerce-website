<?php
/**
 * Gutenberg compatibility for Themify Builder
 *
 * @package Themify
 * @since 3.5.4
 */
 
if ( ! class_exists( 'Themify_Builder_Gutenberg' ) ) :
final class Themify_Builder_Gutenberg {

	const BLOCK_PATTERNS = '<!-- wp:themify-builder/canvas /-->';

	private static $post_types=array();

	public static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'load'));
		add_theme_support( 'align-wide' );
	}


	public static function load() {
            /* add template for all post types that support the editor */
            self::$post_types = $GLOBALS['ThemifyBuilder']->builder_post_types_support();
            foreach( self::$post_types as $type ) {
                    add_filter( 'rest_prepare_' . $type, array( __CLASS__, 'enable_block_existing_content'), 10, 3 );
                    $post_type_object = get_post_type_object( $type );
                    $post_type_object->template = array(
                            array( 'themify-builder/canvas' )
                    );
            }

            add_action( 'rest_api_init', array( __CLASS__, 'register_builder_content_field' ) );
            if ( is_admin() ) {
                add_action( 'current_screen',array( __CLASS__, 'admin_init' ) );
            } else {
				self::register_block();
			}
	}

	public static function admin_init() {
		if ( Themify_Builder_Model::is_gutenberg_editor() && function_exists( 'register_block_type' ) ) {
			self::register_block();
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_scripts') );
			add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class') );
		}
	}
	
	public static function register_block(){
		wp_register_style(
				'builder-styles',
				themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css'),
				null,
				THEMIFY_VERSION
		);

		register_block_type( 'themify-builder/canvas', array(
				'render_callback' => array( __CLASS__, 'render_builder_block'),
				'editor_style' => 'themify-builder-style',
				'style' => 'themify-builder-style'
		) );
	}

	public static function enqueue_editor_scripts() {
		add_filter( 'themify_defer_js_exclude', array( __CLASS__, 'exclude_defer_script' ) );
		themify_enque_script('themify-builder-gutenberg-block',THEMIFY_BUILDER_URI . '/js/editor/backend/themify-builder-gutenberg.js',THEMIFY_VERSION,array( 'wp-blocks', 'wp-i18n', 'wp-element', 'backbone' ));
	}

	public static function render_builder_block( $attributes ) {
		return self::BLOCK_PATTERNS; // just return custom block tag, let builder the_content filter to render builder output
	}

	public static function exclude_defer_script( $handles ) {
		return array_merge( $handles, array( 'themify-builder-gutenberg-block' ) );
	}

	/**
	 * Enable builder block on existing content data.
	 * 
	 * @param object $data 
	 * @param object $post 
	 * @param array $context 
	 * @return object
	 */
	public static function enable_block_existing_content( $data, $post, $context ) {
		if('edit' === $context['context'] && isset( $data->data['content']['raw'] )){

			if ( self::has_builder_block($data->data['content']['raw'])) {
				// remove static content
				$data->data['content']['raw'] = ThemifyBuilder_Data_Manager::update_static_content_string( '', $data->data['content']['raw'] );
			} 
			else {
				$data->data['content']['raw'] = $data->data['content']['raw'] . ' ' . self::BLOCK_PATTERNS;
			}
		}
		return $data;
	}

	/**
	 * Added body class
	 * @param string $classes 
	 * @return string
	 */
	public static function admin_body_class( $classes ) {
		$classes .= ' themify-gutenberg-editor';
		return $classes;
	}

	/**
	 * Register builder content meta
	 * 
	 * @access public
	 * @return type
	 */
	public static function register_builder_content_field() {
		foreach( self::$post_types as $type ) {
			register_rest_field( $type, 'builder_content', array(
					'get_callback'    => array( __CLASS__, 'get_post_meta_builder'),
					'schema'          => null
				)
			);
		}
	}
	

	/**
	 * Update builder block tag in the string.
	 * 
	 * @param string $replace_string 
	 * @param string $content 
	 * @return string
	 */
	public static function replace_builder_block_tag( $replace_string, $content ) {
		if ( self::has_builder_block( $content ) ) {
			$content = str_replace( self::BLOCK_PATTERNS, $replace_string, $content );
			$content = ThemifyBuilder_Data_Manager::remove_empty_p( $content );
		}
		return $content;
	}
	
	/**
	 * Check if content has builder block
	 * @param string $content 
	 */
	public static function has_builder_block( $content ) {
		return strpos($content,self::BLOCK_PATTERNS)!==false;
	}
	
	/**
	 * Get builder content value.
	 * 
	 * @access public
	 * @param type $object 
	 * @return type
	 */
	public static function get_post_meta_builder( $object ) {
		return ThemifyBuilder_Data_Manager::_get_all_builder_text_content( ThemifyBuilder_Data_Manager::get_data( $object['id'] ) );
	}
}
Themify_Builder_Gutenberg::init();
endif;
