<?php
/**
 * Menu Toggle Dropdown feature
 * 
 * Allows toggle dropdown instead of click top open menu items.
 * 
 * @package Themify
 * @since 4.8.6
 */
class Themify_Menu_Toggle_Dropdown {

	/**
	 * Setup menu toggle dropdown functionality
	 */
	public static function init() {
            if( is_admin() ) {
                    add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'wp_nav_menu_item_custom_fields' ), 15, 2 );
                    add_action( 'wp_update_nav_menu_item', array( __CLASS__, 'wp_update_nav_menu_item' ), 10, 3 );
                    add_action( 'delete_post', array( __CLASS__, 'delete_post' ), 1, 3 );
            } else {
                    add_filter( 'nav_menu_css_class', array( __CLASS__, 'nav_menu_css_class' ), 10, 2 );
            }
	}

	/**
	 * Save the toggle dropdown meta for a menu item. Also removes the meta entirely if the field is cleared.
	 */
	public static function wp_update_nav_menu_item( $menu_id, $menu_item_db_id, $args ) {
		$param_key = 'menu-item-tf-toggle-dropdown';
		$meta_key = '_themify_toggle_dropdown';
                $new_meta_value = isset( $_POST[$param_key] , $_POST[$param_key][$menu_item_db_id] ) ? $_POST[$param_key][$menu_item_db_id] : false;
		if ( $new_meta_value ) {
			update_post_meta( $menu_item_db_id, $meta_key, $new_meta_value );
		} else {
			delete_post_meta( $menu_item_db_id, $meta_key );
		}
	}

	/**
	 * Clean up the toggle dropdown meta field when a menu item is deleted
	 *
	 * @param int $post_id
	 */
	public static function delete_post( $post_id ) {
		if( is_nav_menu_item( $post_id ) ) {
			delete_post_meta( $post_id, '_themify_toggle_dropdown' );
		}
	}

	/**
	 * Display the toggle dropdown picker for menu items in the backend
	 */
	public static function wp_nav_menu_item_custom_fields( $item_id, $item ) {
            $saved_meta = get_post_meta( $item_id, '_themify_toggle_dropdown', true );
            ?>
            <p class="field-tf-toggle-dropdown description-wide">
                <label for="edit-menu-item-tf-toggle-dropdown-<?php echo $item_id; ?>">
                    <input type="checkbox" name="menu-item-tf-toggle-dropdown[<?php echo $item_id; ?>]" value="1" <?php echo ($saved_meta ? 'checked="checked"' : ''); ?> id="edit-menu-item-tf-toggle-dropdown-<?php echo $item_id?>" class="edit-menu-item-tf-toggle-dropdown widefat">
                                    <?php _e( 'Use this link as dropdown menu trigger on mobile', 'themify' ) ?><br />
                </label>
            </p>
	<?php }

	/**
	 * Add a css class to toggle dropdown menu items
	 */
	public static function nav_menu_css_class( $classes, $item ) {
		if (in_array('menu-item-has-children', $item->classes,true) && !empty(get_post_meta( $item->ID, '_themify_toggle_dropdown', true ))) {
			$classes[] = 'themify_toggle_dropdown';
		}
		return $classes;
	}

}
Themify_Menu_Toggle_Dropdown::init();