<?php

defined( 'ABSPATH' ) || exit;

if( ! class_exists( 'Themify_User_Meta' ) ) :
/**
 * Manage custom fields for user profiles
 *
 * @package Themify Metabox
 * @since 1.0.1
 */
class Themify_User_Meta {

	private static $instance = null;
	public $fields = null;

	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		add_action( 'show_user_profile', array( $this, 'user_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'user_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_field' ) );
	}

	/**
	 * Retrieves the list of custom fields for user profiles
	 *
	 * @uses apply_filters calls themify_metabox/user/fields filter
	 * @return array
	 */
	public function get_fields() {
		if( $this->fields == null ) {
			$this->fields = apply_filters( 'themify_metabox/user/fields', array() );
		}

		return $this->fields;
	}

	/**
	 * Display the custom fields on user profile page
	 *
	 * @uses get_fields method
	 */
	function user_fields( $user ) {
		$groups = $this->get_fields();
		if( empty( $groups ) ) {
			return;
		}

		foreach( $groups as $id => $group ) : ?>
			<?php if( isset( $group['title'] ) ) : ?><h3><?php echo $group['title']; ?></h3><?php endif; ?>
			<?php if( isset( $group['description'] ) ) : ?><p><?php echo $group['description']; ?></p><?php endif; ?>

			<table class="form-table" id="<?php echo $id; ?>">
			<?php
			$post_id = 0;
			$themify_custom_panel_nonce = wp_create_nonce( 'tf_nonce' );
			foreach( $group['fields'] as $field ) :
				$meta_value = isset( $field['name'] ) ? get_the_author_meta( $field['name'], $user->ID ) : '';
				$toggle_class = '';
				$ext_attr = '';
				if( isset($field['toggle']) ){
					$toggle_class .= 'themify-toggle ';
					$toggle_class .= (is_array($field['toggle'])) ? implode(' ', $field['toggle']) : $field['toggle'];
					if ( is_array( $field['toggle'] ) && in_array( '0-toggle', $field['toggle'] ) ) {
						$toggle_class .= ' default-toggle';
					}
				}
				if ( isset( $field['class'] ) ) {
					$toggle_class .= ' ';
					$toggle_class .= is_array( $field['class'] ) ? implode( ' ', $field['class'] ) : $field['class'];
				}
				$data_hide = '';
				if ( isset( $field['hide'] ) ) {
					$data_hide = is_array( $field['hide'] ) ? implode( ' ', $field['hide'] ) : $field['hide'];
				}
				if( isset($field['default_toggle']) && $field['default_toggle'] == 'hidden' ){
					$ext_attr = 'style="display:none;"';
				}
				if( isset($field['enable_toggle']) && $field['enable_toggle'] == true ) {
					$toggle_class .= ' enable_toggle';
				}
				?>
				<tr class="themify_field_row">
					<th><label for=""><?php echo $field['title']; ?></label></th>
					<td class="themify_field">
						<?php
						do_action( "themify_metabox/field/{$field['type']}", array(
							'meta_box' => $field,
							'meta_value' => $meta_value,
							'toggle_class' => $toggle_class,
							'data_hide' => $data_hide,
							'ext_attr' => $ext_attr,
							'post_id' => $post_id,
							'themify_custom_panel_nonce' => $themify_custom_panel_nonce
						) );

						// backward compatibility: allow custom function calls in the fields array
						if( isset( $field['function'] ) && is_callable( $field['function'] ) ) {
							call_user_func( $field['function'], $field );
						}
						?>
					</td>
				</tr>

				<?php 

			endforeach; ?>
			</table>
		<?php endforeach;
	}

	/**
	 * Save custom fields for user profiles
	 *
	 */
	function save_user_field( $user_id ) {
	    // only saves if the current user can edit user profiles
	    if ( ! current_user_can( 'edit_user', $user_id ) )
	        return false;

		$groups = $this->get_fields();
		if( empty( $groups ) ) {
			return false;
		}

		foreach( $groups as $group ) {
			foreach( $group['fields'] as $field ) {
				if( isset( $_POST[$field['name']] ) ) {
					update_user_meta( $user_id, $field['name'], $_POST[$field['name']] );
				}
			}
		}
	}

	/**
	 * Enqueues Themify Metabox assets on user profile page
	 *
	 * @since 1.0.1
	 */
	function enqueue() {
		global $pagenow;
		if ( in_array( $pagenow, array( 'profile.php', 'user-edit.php' ) ) ) {
			Themify_Metabox::get_instance()->enqueue();
		}
	}
}
endif;
Themify_User_Meta::get_instance();