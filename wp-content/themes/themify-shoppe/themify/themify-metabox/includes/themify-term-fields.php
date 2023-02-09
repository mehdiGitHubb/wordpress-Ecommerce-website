<?php

defined( 'ABSPATH' ) || exit;

if( ! class_exists( 'Themify_Term_Meta' ) ) :
/**
 * Manage custom fields for taxonomy terms
 *
 * @package Themify Metabox
 * @since 1.0.3
 */
class Themify_Term_Meta {

	private static $instance = null;
	public $fields = array();

	public static function get_instance() {
		return null == self::$instance ? self::$instance = new self : self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function init() {
		$taxonomies = get_taxonomies();
		if ( empty( $taxonomies ) )
			return;

		foreach( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'add_form_fields' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'edit_form_fields' ), 10, 2 );
		}
		add_action( "created_term", array( $this, 'save_fields' ), 10, 3 );
		add_action( "edited_term", array( $this, 'save_fields' ), 10, 3 );
	}

	function add_form_fields( $taxonomy ) {
		$fields = $this->get_fields( $taxonomy );
		if ( empty( $fields ) )
			return;

		foreach ( $fields as $field ) :
			$meta_value = '';
			$toggle_class = '';
			$ext_attr = '';
			if( isset($field['toggle']) ) {
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
			<div class="themify_field_row form-field <?php echo $field['name']; ?>">
				<?php if ( isset( $field['title'] ) ) : ?><label for=""><?php echo $field['title']; ?></label><?php endif; ?>
				<div class="themify_field">
					<?php
					do_action( "themify_metabox/field/{$field['type']}", array(
						'meta_box' => $field,
						'meta_value' => $meta_value,
						'toggle_class' => $toggle_class,
						'data_hide' => $data_hide,
						'ext_attr' => $ext_attr,
						'post_id' => 0,
						'themify_custom_panel_nonce' => wp_create_nonce( 'tf_nonce' ),
					) );

					// backward compatibility: allow custom function calls in the fields array
					if( isset( $field['function'] ) && is_callable( $field['function'] ) ) {
						call_user_func( $field['function'], $field );
					}
					?>
				</div>
			</div><!-- .form-field -->
			<?php

		endforeach;
	}

	function edit_form_fields( $tag, $taxonomy ) {
		$fields = $this->get_fields( $taxonomy );
		if( empty( $fields ) )
			return;

		foreach( $fields as $field ) :
			$meta_value = isset( $field['name'] ) ? get_term_meta( $tag->term_id, $field['name'], true ) : '';
			$toggle_class = '';
			$ext_attr = '';
			if( isset($field['toggle']) ) {
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
			<tr class="form-field <?php echo $field['name']; ?> themify_field_row">
				<th scope="row" valign="top">
					<?php if ( isset( $field['title'] ) ) : ?><label for=""><?php echo $field['title']; ?></label><?php endif; ?>
				</th>
				<td class="themify_field">
				<?php
				do_action( "themify_metabox/field/{$field['type']}", array(
					'meta_box' => $field,
					'meta_value' => $meta_value,
					'toggle_class' => $toggle_class,
					'data_hide' => $data_hide,
					'ext_attr' => $ext_attr,
					'post_id' => 0,
					'themify_custom_panel_nonce' => wp_create_nonce( 'tf_nonce' ),
				) );

				// backward compatibility: allow custom function calls in the fields array
				if( isset( $field['function'] ) && is_callable( $field['function'] ) ) {
					call_user_func( $field['function'], $field );
				}
				?>
				</td>
			</tr><!-- .form-field -->
			<?php

		endforeach;
	}

	/**
	 * Retrieves the list of custom fields for a given taxonomy term
	 *
	 * @uses apply_filters calls themify_metabox/user/fields filter
	 * @return array
	 */
	public function get_fields( $taxonomy ) {
		if ( empty( $this->fields[ $taxonomy ] ) ) {
			$this->fields[ $taxonomy ] = apply_filters( "themify_metabox/taxonomy/{$taxonomy}/fields", array() );
		}

		return $this->fields[ $taxonomy ];
	}

	/**
	 * Save custom fields when a term is edited
	 */
	function save_fields( $term_id, $taxonomy_term_id, $taxonomy ) {
		$fields = $this->get_fields( $taxonomy );
		if ( empty( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field['name'] ] ) ) {
				$new_meta = isset( $field['name'] ) && isset( $_POST[ $field['name'] ] ) ? $_POST[ $field['name'] ] : '';
				$old_meta = get_term_meta( $term_id, $field['name'], true );

				// when a default value is set for the field and it's the same as $new_meta, do not bother with saving the field
				if( isset( $field['default'] ) && $new_meta == $field['default'] ) {
					$new_meta = '';
				}

				// remove empty meta fields from database
				if ( '' == $new_meta && metadata_exists( 'term', $term_id, $field['name'] ) ) {
					delete_term_meta( $term_id, $field['name'] );
				}

				if ( $new_meta !== '' && $new_meta != $old_meta ) {
					update_term_meta( $term_id, $field['name'], $new_meta );
				}
			}
		}
	}

	/**
	 * Enqueues Themify Metabox assets on term edit pages
	 *
	 * @since 1.0.1
	 */
	function enqueue() {
		if ( in_array( get_current_screen()->base, array( 'term', 'edit-tags' ),true ) ) {
			Themify_Metabox::get_instance()->admin_enqueue_scripts();
			Themify_Metabox::get_instance()->enqueue();
		}
	}
}
endif;
Themify_Term_Meta::get_instance();