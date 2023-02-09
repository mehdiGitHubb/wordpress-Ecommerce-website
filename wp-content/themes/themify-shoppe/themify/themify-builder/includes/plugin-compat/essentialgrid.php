<?php
/**
 * Builder Plugin Compatibility Code
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * @link https://www.essential-grid.com/
 */
class Themify_Builder_Plugin_Compat_EssentialGrid {

	/**
	 * Fix plugin's editor scripts breaking frontend editor
	 */
	static function init() {
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'tb_load_editor' ) {
			remove_action( 'print_media_templates', array( Essential_Grid_Admin::get_instance(), 'ess_grid_addon_media_form' ) );
		}
	}

}