<?php

/**
 * The Builder Visibility Controls class.
 * This is used to show the visibility controls on all rows and modules.
 *
 * @package	Themify_Builder
 * @subpackage Themify_Builder/classes
 */
final class Themify_Builder_Visibility_Controls {

    
    public static function init(){
	
	add_filter('themify_builder_row_classes', array(__CLASS__, 'row_classes'), 10, 3);
	add_filter('themify_builder_subrow_classes', array(__CLASS__, 'subrow_classes'), 10, 4);
	add_filter('themify_builder_module_classes', array(__CLASS__, 'module_classes'), 10, 5);
    }

    /**
    * Append visibility controls CSS classes to rows.
    *
    * @param	array $classes
    * @param	array $row
    * @param	string $builder_id
    * @access 	public
    * @return 	array
    */
    public static function row_classes($classes, $row, $builder_id) {
            return !empty($row['styling'])?self::get_classes($row['styling'], $classes, 'row'):$classes;
    }

    /**
     * Append visibility controls CSS classes to subrows.
     *
     * @param	array $classes
     * @param	array $subrow
     * @param	string $builder_id
     * @access 	public
     * @return 	array
     */
    public static function subrow_classes($classes, $subrow, $builder_id) {
            return !empty($subrow['styling'])?self::get_classes($subrow['styling'], $classes, 'row'):$classes;
    }

    /**
     * Append visibility controls CSS classes to modules.
     * 
     * @param	array $classes
     * @param	string $mod_name
     * @param	string $module_ID
     * @param	array $args
     * @access 	public
     * @return 	array
     */
    public static function module_classes($classes, $mod_name = null, $module_ID = null, $args = array()) {
        return self::get_classes($args, $classes, 'module');
    }

    private static function get_classes($args, $classes, $type) {
        
        $hide_all = isset($args['visibility_all']) && $args['visibility_all'] === 'hide_all';
		$is_active=Themify_Builder::$frontedit_active===true || Themify_Builder_Model::is_front_builder_activate();
		if($is_active===false || $type === 'row'){
			$elements = array('desktop', 'tablet', 'tablet_landscape', 'mobile');
			foreach ($elements as $e) {
				if ( $hide_all===true || (isset($args['visibility_' . $e]) && $args['visibility_' . $e] === 'hide')) {
					if ($is_active===false) {
						$classes[] = 'hide-' . $e;
					} 
					else {
						$classes[] = 'tb_visibility_hidden';
						break;
					}
				}
			}
		}
        if( $hide_all===true  || ( isset( $args['sticky_visibility'] ) && $args['sticky_visibility'] === 'hide')){
			$classes[] = 'hide-on-stick';
        }
        return $classes;
    }

}
