<?php
/**
 * Class CFF_View
 *
 * This class loads view page template files on the admin dashboard area.
 *
 * @since 4.0
 */
namespace InstagramFeed;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SBI_View {

	/**
	 * Base file path of the templates
     * 
     * @since 4.0
	 */
	const BASE_PATH = SBI_PLUGIN_DIR . 'admin/views/';

	public function __construct() {
	}

	/**
	 * Render template
	 *
	 * @param string $file
	 * @param array $data
     * 
     * @since 4.0
	 */
	public static function render( $file, $data = array() ) {
		$file = str_replace( '.', '/', $file );
		$file = self::BASE_PATH . $file . '.php';

		if ( file_exists( $file ) ) {
            if ( $data !== null && ! empty( $data ) ) extract( $data );
			include_once $file;
		}
	}
}