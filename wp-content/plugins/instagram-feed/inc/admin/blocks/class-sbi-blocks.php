<?php
/**
 * Instagram Feed block with live preview.
 *
 * @since 2.3
 */
class SB_Instagram_Blocks {

	/**
	 * Indicates if current integration is allowed to load.
	 *
	 * @since 1.8
	 *
	 * @return bool
	 */
	public function allow_load() {
		return function_exists( 'register_block_type' );
	}

	/**
	 * Loads an integration.
	 *
	 * @since 2.3
	 */
	public function load() {
		$this->hooks();
	}

	/**
	 * Integration hooks.
	 *
	 * @since 2.3
	 */
	protected function hooks() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register Instagram Feed Gutenberg block on the backend.
	 *
	 * @since 2.3
	 */
	public function register_block() {

		wp_register_style(
			'sbi-blocks-styles',
			trailingslashit( SBI_PLUGIN_URL ) . 'css/sb-blocks.css',
			array( 'wp-edit-blocks' ),
			SBIVER
		);

		$attributes = array(
			'shortcodeSettings' => array(
				'type' => 'string',
			),
			'noNewChanges' => array(
				'type' => 'boolean',
			)
		);

		register_block_type(
			'sbi/sbi-feed-block',
			array(
				'attributes'      => $attributes,
				'render_callback' => array( $this, 'get_feed_html' ),
			)
		);
	}

	/**
	 * Load Instagram Feed Gutenberg block scripts.
	 *
	 * @since 2.3
	 */
	public function enqueue_block_editor_assets() {
		$db = sbi_get_database_settings();

		sb_instagram_scripts_enqueue();

		wp_enqueue_style( 'sbi-blocks-styles' );
		wp_enqueue_script(
			'sbi-feed-block',
			trailingslashit( SBI_PLUGIN_URL ) . 'js/sb-blocks.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			SBIVER,
			true
		);

		$shortcodeSettings = '';

		$i18n = array(
			'addSettings'         => esc_html__( 'Add Settings', 'instagram-feed' ),
			'shortcodeSettings'   => esc_html__( 'Shortcode Settings', 'instagram-feed' ),
			'example'             => esc_html__( 'Example', 'instagram-feed' ),
			'preview'             => esc_html__( 'Apply Changes', 'instagram-feed' ),

		);

		wp_localize_script(
			'sbi-feed-block',
			'sbi_block_editor',
			array(
				'wpnonce'  => wp_create_nonce( 'sb-instagram-blocks' ),
				'canShowFeed' => ! empty( $db['connected_accounts'] ),
				'configureLink' => admin_url( 'admin.php?page=sbi-settings' ),
				'shortcodeSettings'    => $shortcodeSettings,
				'i18n'     => $i18n,
			)
		);
	}

	/**
	 * Get form HTML to display in a Instagram Feed Gutenberg block.
	 *
	 * @param array $attr Attributes passed by Instagram Feed Gutenberg block.
	 *
	 * @since 2.3
	 *
	 * @return string
	 */
	public function get_feed_html( $attr ) {

		$return = '';

		$shortcode_settings = isset( $attr['shortcodeSettings'] ) ? $attr['shortcodeSettings'] : '';

		$shortcode_settings = str_replace(array( '[instagram-feed', ']' ), '', $shortcode_settings );

		$return .= do_shortcode( '[instagram-feed '.$shortcode_settings.']' );

		return $return;

	}

	/**
	 * Checking if is Gutenberg REST API call.
	 *
	 * @since 2.3
	 *
	 * @return bool True if is Gutenberg REST API call.
	 */
	public static function is_gb_editor() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context']; // phpcs:ignore
	}

}
