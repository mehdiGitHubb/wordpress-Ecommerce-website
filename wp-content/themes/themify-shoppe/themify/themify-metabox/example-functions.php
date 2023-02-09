<?php
/**
 * Example of how Themify Metabox plugin can be used in themes and plugins.
 *
 * To use this file, enable the Themify Metabox plugin and then copy the contents of this file to
 * your theme's functions.php file, or "include" it.'
 *
 * @package Themify Metabox
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register a custom meta box to display on Page post type
 *
 * @return array
 */
function themify_metabox_example_meta_box( $meta_boxes ) {
	$meta_boxes['tm-example'] = array(
		'id' => 'tm-example', // later, to add fields to this metabox we'll use "themify_metabox/fields/tm-example" filter hook, see function
		'title' => __( 'Themify Metabox Example', 'themify' ),
		'context' => 'normal',
		'priority' => 'high',
		'screen' => array( 'page' ),
	);

	return $meta_boxes;
}
add_filter( 'themify_metaboxes', 'themify_metabox_example_meta_box' );

/**
 * Setup the custom fields for our Themify Metabox Example meta box, added earlier in the themify_metabox_example_meta_box function
 *
 * @return array
 */
function themify_metabox_example_meta_box_fields( $fields, $post_type ) {
	$first_tab_options = array(
		array(
			'name' => 'text_field',
			'title' => __( 'Text field', 'themify' ),
			'description' => __( 'Field description is displayed below the field.', 'themify' ),
			'type' => 'textbox',
		),
		array(
			'name' => 'textarea_field',
			'title' => __( 'Textarea field', 'themify' ),
			'type' => 'textarea',
			'size' => 55,
			'rows' => 4,
		),
		array(
			'name' => 'image_field',
			'title' => __( 'Image field', 'themify' ),
			'description' => '',
			'type' => 'image',
			'meta' => array()
		),
		array(
			'name' => 'dropdown_field',
			'title' => __( 'Dropdown', 'themify' ),
			'type' => 'dropdown',
			'meta' => array(
				array( 'value' => '', 'name' => '' ),
				array( 'value' => 'yes', 'name' => __( 'Yes', 'themify' ), 'selected' => true ),
				array( 'value' => 'no', 'name' => __( 'No', 'themify' ) ),
			),
			'description' => __( 'You can set which option is selected by default. Cool, eh?', 'themify' ),
			// do not save the custom field when the option is set to Yes
			'default' => 'yes',
		),
		array(
			'name' => 'dropdownbutton_field',
			'title' => __( 'Dropdown Button', 'themify' ),
			'type' => 'dropdownbutton',
			'states' => array(
				array( 'value' => '', 'title' => __( 'Default', 'themify' ), 'icon' => '%s/ddbtn-blank.png', 'name' => __( 'Default', 'themify' ) ),
				array( 'value' => 'yes', 'title' => __( 'Yes', 'themify' ), 'icon' => '%s/ddbtn-check.svg', 'name' => __( 'Yes', 'themify' ) ),
				array( 'value' => 'no', 'title' => __( 'No', 'themify' ), 'icon' => '%s/ddbtn-cross.svg', 'name' => __( 'No', 'themify' ) ),
			),
			'description' => __( 'Similar to "dropdown" field, but allows setting custom icons for each state.', 'themify' ),
		),
		array(
			'name' => 'checkbox_field',
			'title' => __( 'Checkbox', 'themify' ),
			'label' => __( 'Checkbox label', 'themify' ),
			'type' => 'checkbox',
		),
		array(
			'name'        => 'radio_field',
			'title'       => __( 'Radio field', 'themify' ),
			'description' => __( 'You can hide or show option based on how other options are configured', 'themify' ),
			'type'        => 'radio',
			'meta'        => array(
				array( 'value' => 'yes', 'name' => __( 'Yes', 'themify' ), 'selected' => true ),
				array( 'value' => 'no', 'name' => __( 'No', 'themify' ) ),
			),
			'enable_toggle' => true,
			'default' => 'yes',
		),
		array(
			'name' => 'separator_image_size',
			'type' => 'separator',
			//'description' => __( 'Optional text to show after the separator', 'themify'. )
		),
		array(
			'type' => 'multi',
			'name' => 'multi_field',
			'title' => __( 'Multi fields', 'themify' ),
			'meta' => array(
				'fields' => array(
					array(
						'name' => 'image_width',
						'label' => __( 'width', 'themify' ),
						'description' => '',
						'type' => 'textbox',
						'meta' => array( 'size' => 'small' )
					),
					// Image Height
					array(
						'name' => 'image_height',
						'label' => __( 'height', 'themify' ),
						'type' => 'textbox',
						'meta' => array( 'size' => 'small' )
					),
				),
				'description' => __( '"Multi" field type allows displaying multiple fields together.', 'themify'),
				'before' => '',
				'after' => '',
				'separator' => ''
			)
		),
		array(
			'name'        => 'color_field',
			'title'       => __( 'Color', 'themify' ),
			'description' => '',
			'type'        => 'color',
			'meta'        => array( 'default' => null ),
			'class'      => 'yes-toggle'
		),
		array(
			'name'        => 'post_id_info_field',
			'title'       => __( 'Post ID', 'themify' ),
			'description' => __( 'This field type shows text with the ID of the post, which is: <code>%s</code>', 'themify' ),
			'type'        => 'post_id_info',
		),
	);

	$second_tab_options = array(
		array(
			'name' 		=> 'audio_field',
			'title' 	=> __( 'Audio field', 'themify' ),
			'description' => '',
			'type' 		=> 'audio',
			'meta'		=> array(),
		),
		array(
			'name' 		=> 'video_field',
			'title' 	=> __( 'Video field', 'themify' ),
			'description' => '',
			'type' 		=> 'video',
			'meta'		=> array(),
		),
        array(
			'name' => 'gallery_shortcode_field',
			'title' => __( 'Gallery Shortcode field', 'themify' ),
			'description' => __( 'Using this field type you can add a gallery manager.', 'themify' ),
			'type' => 'gallery_shortcode',
        ),
		array(
			'name' => 'date_field',
			'title' => __( 'Date field', 'themify' ),
			'description' => '',
			'type' => 'date',
			'meta' => array(
				'default' => '',
				'pick' => __( 'Pick Date', 'themify' ),
				'close' => __( 'Done', 'themify' ),
				'clear' => __( 'Clear Date', 'themify' ),
				'time_format' => 'HH:mm:ss',
				'date_format' => 'yy-mm-dd',
				'timeseparator' => ' ',
			)
		),
		array(
			'name' => 'repeater_field',
			'title' => __( 'Repeater', 'themify' ),
			'type' => 'repeater',
			'fields' => array(
				array(
					'name' => 'text_1',
					'title' => __( 'Text Field', 'themify' ),
					'type' => 'textbox',
					'class' => 'small'
				),
				array(
					'name'        => 'color_field',
					'title'       => __( 'Color', 'themify' ),
					'description' => '',
					'type'        => 'color',
				),
				array(
					'name' => 'field_3',
					'title' => __( 'Dropdown', 'themify' ),
					'type' => 'dropdown',
					'meta' => array(
						array( 'value' => 'yes', 'name' => __( 'Yes', 'themify' ) ),
						array( 'value' => 'no', 'name' => __( 'No', 'themify' ) ),
					),
				),
			),
			'add_new_label' => __( 'Add new item', 'themify' ),
		),
	);

	$fields[] = array(
		'name' => __( 'First Tab', 'themify' ), // Name displayed in box
		'id' => 'first-tab',
		'options' => $first_tab_options,
	);
	$fields[] = array(
		'name' => __( 'Second Tab', 'themify' ), // Name displayed in box
		'id' => 'second-tab',
		'options' => $second_tab_options,
	);

	return $fields;
}
add_filter( 'themify_metabox/fields/tm-example', 'themify_metabox_example_meta_box_fields', 10, 2 );


/**
 * Add sample fields to the user profile screen
 *
 * @return array
 * @since 1.0.1
 */
function themify_metabox_example_user_fields( $fields ) {
	$fields['themify-metabox-sample'] = array(
		'title' => __( 'Sample fields added by Themify Metabox.', 'themify' ),
		'description' => __( 'Description text about the fields.', 'themify' ),
		'fields' => array(
			array(
				'name' => 'textbox_field',
				'title' => __( 'Text box', 'themify' ),
				'type' => 'textbox',
			),
			array(
				'name' => 'image_field',
				'title' => __( 'Image field', 'themify' ),
				'description' => __( 'This is only to show how field types can be used in user profile pages.', 'themify' ),
				'type' => 'image',
			),
		),
	);

	return $fields;
}
add_filter( 'themify_metabox/user/fields', 'themify_metabox_example_user_fields' );

/**
 * Add a sample Color field to Category taxonomy
 *
 * @since 1.0.3
 * @return array
 */
function themify_metabox_example_category_fields( $fields ) {
	$new_fields = array(
		array(
			'name'        => 'color_field',
			'title'       => __( 'Color', 'themify' ),
			'description' => '',
			'type'        => 'color',
			'meta'        => array( 'default' => null ),
		),
	);

	return array_merge( $fields, $new_fields );
}
add_filter( 'themify_metabox/taxonomy/category/fields', 'themify_metabox_example_category_fields', 10 );
