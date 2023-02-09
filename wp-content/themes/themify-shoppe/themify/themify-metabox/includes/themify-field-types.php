<?php

defined( 'ABSPATH' ) || exit;

foreach( array( 'image', 'audio', 'postmeta', 'post_id_info', 'multi', 'date', 'video', 'color', 'dropdown', 'dropdownbutton', 'textbox', 'textarea', 'checkbox', 'separator', 'layout', 'radio', 'gallery_shortcode', 'query_category', 'assignments', 'repeater', 'image_radio', 'hidden' ) as $field_type ) {
	add_action( "themify_metabox/field/{$field_type}", "themify_meta_field_{$field_type}", 10, 1 );
}
add_action( 'wp_ajax_themify_metabox_media_lib_browse', 'themify_metabox_media_lib_browse' );
add_action( 'wp_ajax_themify_plupload', 'themify_wp_ajax_plupload_image' );
add_action( 'wp_ajax_themify_create_inner_popup_page', 'themify_ajax_create_inner_page' );
add_action( 'wp_ajax_themify_create_popup_page_pagination', 'themify_ajax_create_page_pagination' );

function themify_meta_field_image( $args ) {
	extract( $args, EXTR_OVERWRITE );

	/** Parameters for the uploader @var Array */
	$featimg_uploader_args = array(
		'tomedia' => true,
		'topost' => $post_id,
		'medialib' => true,
		'fields' => $meta_box['name']
	);

	?>

	<div id="<?php echo esc_attr( 'remove-' . $meta_box['name'] ); ?>" class="themify_featimg_remove <?php if( $meta_value == '' ) echo 'hide' ?>">
		<a data-attachid="<?php echo esc_attr( get_post_meta( $post_id, '_' . $meta_box['name'] . '_attach_id', true ) ); ?>" href="#"><?php _e( 'Remove image', 'themify' ); ?></a>
	</div>

	<?php
	if( $meta_value ) {
		echo '
		<div class="themify_upload_preview" style="display:block;">
			<a href="' . esc_url( $meta_value ) . '" target="_blank">
				<img src="' . esc_url( $meta_value ) . '" style="width: 40px; height: 40px;" alt="' . esc_attr__( 'Post Image', 'themify' ) . '" />
			</a>
		</div>';
	} else {
		echo '<div class="themify_upload_preview"></div>';
	}
	?>

	<!-- Field storing URL -->
	<input type="hidden" id="<?php echo esc_attr( $meta_box['name'] ); ?>" name="<?php echo esc_attr( $meta_box['name'] ); ?>" value="<?php echo esc_attr( $meta_value ); ?>" size="55" class="themify_input_field themify_upload_field" />

	<div class="themify_upload_buttons">
		<?php themify_uploader($meta_box['name'], $featimg_uploader_args) ?>
	</div>

	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description"><?php echo wp_kses_post( $meta_box['description'] ); ?></span>
	<?php endif; // meta_box description ?>

	<script>
		jQuery(function($){
			var $remove = $('#remove-<?php echo esc_js( $meta_box['name'] ); ?>');
			$remove.find('a').on('click', function(e){
				e.preventDefault();
				$( this ).closest( '.themify_field_row' )
					.find( '.themify_upload_field' ).val( '' )
						.end()
					.find( '.themify_upload_preview' ).fadeOut();
				$remove.addClass( 'hide' );
			});
		});
	</script>
	<?php
}

function themify_meta_field_font( $args ) {
	$meta_box = $args['meta_box'];
	$sanitized_name = sanitize_html_class( $meta_box['name'] );
	extract( $args, EXTR_OVERWRITE );

	/** Parameters for the uploader @var Array */
	$featimg_uploader_args = array(
		'tomedia'	=> true,
		'topost'	=> $post_id,
		'medialib'	=> true,
		'fields'	=> $sanitized_name,
		'formats'	=> $meta_box['ext'],
		'type'		=> $meta_box['mime'],
	);

	$remove_data = array(
		'postid'		=> esc_js( $post_id ),
		'customfield'	=> esc_js( $meta_box['name'] ),
		'nonce'			=> esc_js( $themify_custom_panel_nonce )
	); ?>

    <div
            id="<?php echo esc_attr( 'remove-' . $sanitized_name ); ?>"
            class="themify_featimg_remove themify_video_remove <?php $meta_value == '' && print( 'hide' ); ?>"
            data-audio-remove='<?php echo json_encode( $remove_data ); ?>'>
        <a href="#"><?php _e( 'Remove File', 'themify' ); ?></a>
    </div>

    <!-- Field storing URL -->
    <input
            size="55"
            type="text"
            value="<?php echo esc_attr( $meta_value ); ?>"
            id="<?php echo esc_attr( $sanitized_name ); ?>"
            class="themify_input_field themify_upload_field"
            name="<?php echo esc_attr( $meta_box['name'] ); ?>">

    <div class="themify_upload_buttons">
		<?php themify_uploader( $sanitized_name, $featimg_uploader_args) ?>
    </div>

	<?php if ( isset( $meta_box['description'] ) ) : ?>
        <span class="themify_field_description">
			<?php echo wp_kses_post( $meta_box['description'] ); ?>
		</span>
	<?php endif; // meta_box description

	if( ! empty( $meta_box['after'] ) ) echo $meta_box['after'];
}

function themify_meta_field_audio( $args ) {
	$meta_box = $args['meta_box'];
	$sanitized_name = sanitize_html_class( $meta_box['name'] );
	extract( $args, EXTR_OVERWRITE );

	/** Parameters for the uploader @var Array */
	$featimg_uploader_args = array(
		'tomedia'	=> true,
		'topost'	=> $post_id,
		'medialib'	=> true,
		'fields'	=> $sanitized_name,
		'formats'	=> 'mp3,m4a,ogg,wav,wma',
		'type'		=> 'audio',
	);

	$remove_data = array(
		'postid'		=> esc_js( $post_id ),
		'customfield'	=> esc_js( $meta_box['name'] ),
		'nonce'			=> esc_js( $themify_custom_panel_nonce )
	); ?>

	<div
		id="<?php echo esc_attr( 'remove-' . $sanitized_name ); ?>"
		class="themify_featimg_remove themify_video_remove <?php $meta_value == '' && print( 'hide' ); ?>"
		data-audio-remove='<?php echo json_encode( $remove_data ); ?>'>
			<a href="#"><?php _e( 'Remove Audio', 'themify' ); ?></a>
	</div>

	<!-- Field storing URL -->
	<input
		size="55"
		type="text"
		value="<?php echo esc_attr( $meta_value ); ?>"
		id="<?php echo esc_attr( $sanitized_name ); ?>"
		class="themify_input_field themify_upload_field"
		name="<?php echo esc_attr( $meta_box['name'] ); ?>">

	<div class="themify_upload_buttons">
		<?php themify_uploader( $sanitized_name, $featimg_uploader_args) ?>
	</div>

	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description">
			<?php echo wp_kses_post( $meta_box['description'] ); ?>
		</span>
	<?php endif; // meta_box description

	if( ! empty( $meta_box['after'] ) ) echo $meta_box['after'];
}

function themify_meta_field_postmeta( $args ) {
	extract( $args, EXTR_OVERWRITE );

	foreach($meta_box['meta'] as $meta => $name){
		$metakey = $meta_box['name'].'_'.$meta;
		$meta_class = 'all' != $meta? 'meta-sub meta-'.$meta : 'meta-all';
		echo '
		<label for="' . esc_attr( $metakey ) . '">
			<input type="checkbox" value="no" class="' . esc_attr( $meta_class ) . '" id="' . esc_attr( $metakey ) . '" name="' . esc_attr( $metakey ) . '" /> ' . esc_html( $name ) . '
		</label>';
	}
	?>
	<br/>
	<input type="hidden" class="widefat" value="<?php echo esc_attr( $meta_value ); ?>" id="<?php echo esc_attr( $meta_box['name'] ); ?>" name="<?php echo esc_attr( $meta_box['name'] ); ?>" />

	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description"><?php echo wp_kses_post( $meta_box['description'] ); ?></span>
	<?php endif; // meta_box description
}

function themify_meta_field_post_id_info( $args ) {
	extract( $args, EXTR_OVERWRITE );
	?>

	<span class="themify_field_description themify_field_info"><?php echo sprintf( $meta_box['description'], $post_id ); ?></span>
	<?php
}

/**
 * Generates date picker field for Themify Custom Panel.
 *
 * @param array $args Field settings.
 *
 * @since 1.7.1
 * @return string
 */
function themify_meta_field_date( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);

	if ( isset( $meta_box['meta']['default'] ) && $meta_box['meta']['default'] != '' ) {
		$default = $meta_box['meta']['default'];
	} else {
		$default = '';
	}

	$required = ( isset( $meta_box['meta']['required'] ) && true == $meta_box['meta']['required'] ) ? 'required' : '';

	$pick  = isset( $meta_box['meta']['pick'] ) && '' != $meta_box['meta']['pick'] ? $meta_box['meta']['pick']  : __( 'Pick Date', 'themify' );
	$close = isset( $meta_box['meta']['close'] ) && '' != $meta_box['meta']['close'] ? $meta_box['meta']['close'] : __( 'Done', 'themify' );
	$clear = isset( $meta_box['meta']['clear'] ) && '' != $meta_box['meta']['clear'] ? $meta_box['meta']['clear'] : __( 'Clear', 'themify' );
	$date_format = isset( $meta_box['meta']['date_format'] ) && '' != $meta_box['meta']['date_format'] ? $meta_box['meta']['date_format'] : 'yy-mm-dd';
	$time_format = isset( $meta_box['meta']['time_format'] ) && '' != $meta_box['meta']['time_format'] ? $meta_box['meta']['time_format'] : 'hh:mm tt';
	$timeseparator = isset( $meta_box['meta']['timeseparator'] ) && '' != $meta_box['meta']['timeseparator'] ? $meta_box['meta']['timeseparator'] : ' @ ';

	$html = sprintf( '
		<div class="disableDateField"></div>
		<input %s type="text" id="%s" name="%s" value="%s" data-clear="%s" data-label="%s"
		 data-close="%s"
		 data-dateformat="%s" data-timeformat="%s" data-timeseparator="%s" class="themify_input_field medium themifyDatePicker" data-first-day="%s" />
		<input type="button" id="%s" data-picker="%s" value="%s" class="button themifyClearDate themifyOpacityTransition %s" >',
	$required, esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), esc_attr( $meta_value? $meta_value : $default ), 'clear-'.esc_attr( $meta_box['name'] ), esc_attr( $pick ), esc_attr( $close ), esc_attr( $date_format ), esc_attr( $time_format ), esc_attr( $timeseparator ), get_option( 'start_of_week', '0' ),
	'clear-'.esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), esc_attr( $clear ), $meta_value? 'themifyFadeIn' : '' );

	if(isset($meta_box['label']) && '' != $meta_box['label'])
		$html = sprintf('<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ));

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if ( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if ( isset( $meta_box['after'] ) ) $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates color picker
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_color( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);
	$format = isset( $meta_box['format'] ) ? $meta_box['format'] : 'hex';
	$default = isset( $meta_box['meta']['default'] ) ? $meta_box['meta']['default'] : '';

	$html = sprintf( '
	<input type="text" id="%s" name="%s" value="%s" class="themify_input_field colorSelectInput" data-format="%s" />
	<input type="button" class="button clearColor" value="' . __('&times;', 'themify') . '">',
		esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), esc_attr( $meta_value? $meta_value : $default ), esc_attr( $format ) );

	if(isset($meta_box['label']) && '' != $meta_box['label'])
		$html = sprintf('<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ));

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if ( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if ( isset( $meta_box['after'] ) ) $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates layout field
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_layout( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract( $args, EXTR_OVERWRITE );
	$ops_html = '';

	foreach ( $meta_box['meta'] as $options ) {
		if ( ( '' == $meta_value || !$meta_value || !isset($meta_value)) && ( isset( $options['selected'] ) && $options['selected'] ) ) {
			$meta_value = $options['value'];
		}
		$class = $meta_value == $options['value']?'selected':'';

		if(isset($meta_box['show_title'])){
			$title = isset($options['title'])? $options['title']: ucwords(str_replace('-', ' ', $options['value']));
		} else {
			$title = '';
		}
                $w= isset($option['w'])?$option['w']:'46';
                $h= isset($option['w'])?$option['w']:'35';
		// Check image src whether absolute url or relative url
		$img_src = ( '' != parse_url( $options['img'], PHP_URL_SCHEME) ) ? $options['img'] : get_template_directory_uri() . '/' . $options['img'];

		$ops_html .= sprintf('<a href="#" class="preview-icon %s"><img src="%s" alt="%s" width="'.$w.'" height="'.$h.'" loading="lazy" decoding="async"/><span class="tm-option-title">%s</span></a>',
			$class, 
			esc_url( $img_src ), 
			esc_attr( $options['value'] ),
			$title
		);
	}

	$html = sprintf('%s<input type="hidden" name="%s" value="%s" class="val" />',
		$ops_html, esc_attr( $meta_box['name'] ), esc_attr( $meta_value ));

	if(isset($meta_box['label']) && '' != $meta_box['label'])
		$html = sprintf('<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ));

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates select field as a button
 *
 * @since 1.3.2
 *
 * @param array $args Field settings
 *
 * @return string
 */
function themify_meta_field_dropdownbutton( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	$name = '';
	extract($args, EXTR_OVERWRITE);

	if(isset($meta_box['main']) && $meta_box['main']) {
		$metakey = $meta_box['name'];
		$meta_class = 'ddbtn-all';
	} else {
		$metakey = $meta_box['name'];
		$meta_class = 'ddbtn-sub';
	}

	$additional_classes = isset( $meta_box['class'] ) ? $meta_box['class'] : '';

	$toggle_class = '';
	$ext_attr = '';
	$toggle_enable = false;
	if( isset($meta_box['toggle']) ){
		$toggle_class .= 'themify-toggle ';
		$toggle_class .= (is_array($meta_box['toggle'])) ? implode(' ', $meta_box['toggle']) : $meta_box['toggle'];
	}
	if( isset($meta_box['default_toggle']) && $meta_box['default_toggle'] == 'hidden' ){
		$ext_attr = 'style="display:none;"';
	}
	if( isset($meta_box['enable_toggle']) && $meta_box['enable_toggle'] == true ) {
		$toggle_class .= ' enable_toggle';
		$toggle_enable = true;
	}

	$first = '';
	$others = '';
	$default_icon = '';

	foreach($meta_box['states'] as $state) {
		$state['icon'] = sprintf( $state['icon'], THEMIFY_METABOX_URI . '/img' );
		if($state['value'] == $meta_value) {
			$first = '
			<div class="first-ddbtn">
				<a href="#" data-val="'.esc_attr( $state['value'] ).'" data-name="'.esc_attr( $state['name'] ).'" title="'.esc_attr( $state['title'] ).'">
					<img src="'.esc_attr( $state['icon'] ).'" title="'.esc_attr( $state['title'] ).'" />
					<span class="ddbtn-name">'.esc_attr( $meta_box['title'] ).'</span>
				</a>
			</div>';
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$others .= '
			<div class="ddbtn">
				<a href="#" data-sel="'.$selected.'" data-val="'.esc_attr( $state['value'] ).'" data-name="'.esc_attr( $state['name'] ).'" title="'.esc_attr( $state['title'] ).'">
					<img src="'.esc_attr( $state['icon'] ).'" title="'.esc_attr( $state['title'] ).'" />
					<span class="ddbtn-label">'.esc_attr( $state['name'] ).'</span>
				</a>
			</div>';
		if(isset($state['default']) && $state['default']){
			$default_icon = $state['icon'];
		}
	}

	$html = sprintf('
		<div class="%s %s %s"><div class="dropdownbutton-group multi-ddbtn">
			<div id="%s" class="dropdownbutton-list multi-%s" data-name="%s" data-def-icon="%s">
			%s<div class="dropdownbutton">%s</div>
			</div>
			<input type="hidden" value="%s" class="widefat %s" id="%s" name="%s" />
		</div></div>',
		$additional_classes, esc_attr( $toggle_class ), esc_attr( $ext_attr ),
		esc_attr( $metakey ), esc_attr( $meta_class ), esc_attr( $name ), esc_attr( $default_icon ), // group
		$first, $others, // dropdown
		esc_attr( $meta_value ), esc_attr( $meta_class ), esc_attr( $metakey ), esc_attr( $metakey ) // hidden field
	);

	$html = themify_meta_field_get_label($html, $meta_box);

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates select field
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_dropdown( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);

	$ops_html = '';

	/* dynamic data source, call a function to populate the field */
	if( is_callable( $meta_box['meta'] ) ) {
		$meta_box['meta'] = call_user_func( $meta_box['meta'] );
	}

	foreach($meta_box['meta'] as $option){
		$ops_html .= sprintf('<option value="%s" %s>%s</option>',
			esc_attr( $option['value'] ),
			isset( $meta_value ) && '' != $meta_value? selected( $meta_value, esc_attr( $option['value'] ), false )
				: selected( isset( $option['selected'] )? $option['selected'] : '', true, false ),
			esc_html( $option['name'] )
		);
	}

	$html = sprintf('<select id="%s" name="%s">%s</select>',
		esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), $ops_html);

	$html = themify_meta_field_get_label($html, $meta_box);

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates radio buttons.
 *
 * @param array $args
 *
 * @return string
 */
function themify_meta_field_radio( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);

	$html = '';
	foreach ( $meta_box['meta'] as $k => $option ) {
		$radio_selected = ( isset( $option['selected'] ) && $option['selected'] && '' == $meta_value ) || ( isset( $meta_box['default'] ) && $option['value'] == $meta_box['default'] && '' == $meta_value ) ? 'checked="checked"' : checked( $meta_value, esc_attr( $option['value'] ), false );
		$disabled = isset( $option['disabled'] ) && $option['disabled'] == true ? 'disabled="disabled"' : '';

		$rid = $meta_box['name'] . '-' . esc_attr( $option['value'] );
		$html .= sprintf( '<input type="radio" name="%s" id="%s" value="%s" %s %s /><label for="%s" class="selectit">%s</label>',
			// radio
			esc_attr( $meta_box['name'] ),
			esc_attr( $rid ),
			esc_attr( $option['value'] ),
			$radio_selected,
			$disabled,
			// label
			esc_attr( $rid ),
			esc_html( $option['name'] )
		);
	}
	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates separator
 *
 * @param array $args Field settings
 * @param bool  $call_before_after Whether to output common wrapping markup before and after the field
 * @param bool  $echo Whether to echo or return the field
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_separator( $args ) {
	$meta_box = $args['meta_box'];
	extract($args, EXTR_OVERWRITE);
	$toggle_class = isset( $args['toggle_class'] ) ? $args['toggle_class'] : '';
	$ext_attr = isset( $args['ext_attr'] ) ? $args['ext_attr'] : '';

	$html = isset($meta_box['meta']['html']) && '' != $meta_box['meta']['html']? $meta_box['meta']['html'] : '<hr class="meta_fields_separator" />';

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	$out = '<div class="themify_field_row tf_clearfix ' . esc_attr( $toggle_class ) . '" ' . esc_attr( $ext_attr );
	if ( isset( $args['data_hide'] ) && ! empty( $args['data_hide'] ) ) {
		$out .= ' data-hide="' . esc_attr( $args['data_hide'] ) . '"';
	}
	$out .= '>' . $html . '</div>';

	echo $out;
}

/**
 * Generates checkbox field
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_checkbox( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);

	$checked = $meta_value || ( isset( $meta_box['default'] ) && 'checked' === $meta_box['default'] && 'auto-draft' === get_post_status() ) ? 'checked="checked"' : '';

	$html = sprintf('<input type="checkbox" id="%s" name="%s" %s class="%s" data-val="%s" />',
		esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), $checked, esc_attr( $meta_box['name'] ).'-toggle-control', esc_attr( $meta_box['name'] ));

	if ( isset( $meta_box['label'] ) && '' != $meta_box['label'] ) {
		$html = sprintf( '<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ) );
	}

	if ( isset( $meta_box['description'] ) ) {
		$html .= themify_meta_field_get_description( $meta_box['description'] );
	}

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates text field
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_textbox( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);
	if( isset( $meta_box['default'] ) && empty( $meta_value ) && ! ( $meta_value === 0 || $meta_value === '0' ) ) {
		$meta_value = $meta_box['default'];
	}

	if ( isset( $meta_box['meta']['size'] ) && '' != $meta_box['meta']['size'] ) {
		$class = $meta_box['meta']['size'];
	} else {
		$class = '';
	}

	$html = sprintf('<input type="text" id="%s" name="%s" value="%s" size="55" class="themify_input_field %s" />',
		esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), esc_attr( $meta_value ), $class);

	if ( isset( $meta_box['label'] ) && '' != $meta_box['label'] ) {
		$html = themify_meta_field_get_label($html, $meta_box);
	}

	if ( isset( $meta_box['description'] ) ) {
		$html .= themify_meta_field_get_description( $meta_box['description'] );
	}

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Generates textarea field
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_textarea( $args ) {
	extract( $args, EXTR_OVERWRITE );
	$meta_box = wp_parse_args( $meta_box, array(
		'size' => 55,
		'rows' => 4,
		'class' => ''
	) );
	$meta_value = $args['meta_value'];

	$html = sprintf( '<textarea id="%s" name="%s" size="%s" rows="%s" class="themify_input_field %s">%s</textarea>',
		esc_attr( $meta_box['name'] ), esc_attr( $meta_box['name'] ), $meta_box['size'], $meta_box['rows'], $meta_box['class'], esc_textarea( $meta_value ) );

	$html = themify_meta_field_get_label($html, $meta_box);

	if ( isset( $meta_box['description'] ) ) {
		$html .= themify_meta_field_get_description( $meta_box['description'] );
	}

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	echo $html;
}

function themify_meta_field_video( $args ) {
	global $post;

	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract($args, EXTR_OVERWRITE);

	ob_start();

	/** Parameters for the uploader @var Array */
	$featimg_uploader_args = array(
		'tomedia'  => true,
		'topost'   => $post_id,
		'medialib' => true,
		'fields'   => $meta_box['name'],
		'formats'  => 'mp4,m4v,webm,ogv,wmv,flv',
		'type'     => 'video',
	);
	?>

	<div id="<?php echo esc_attr( 'remove-' . $meta_box['name'] ); ?>" class="themify_featimg_remove themify_video_remove <?php if( $meta_value == '' ) echo 'hide' ?>">
		<a href="#"><?php _e('Remove Video', 'themify'); ?></a>
	</div>

	<!-- Field storing URL -->
	<input type="text" id="<?php echo esc_attr( $meta_box['name'] ); ?>" name="<?php echo $meta_box['name']; ?>" value="<?php echo esc_attr( $meta_value ); ?>" size="55" class="themify_input_field themify_upload_field" />

	<div class="themify_upload_buttons">
		<?php themify_uploader($meta_box['name'], $featimg_uploader_args) ?>
	</div>

	<script type="text/javascript">
		jQuery(function($){
			$('#remove-<?php echo esc_js( $meta_box['name'] ); ?>').find('a').on('click', function(e){
				e.preventDefault();
				var $self = $(this).parent();
				$self.parent().find('.themify_upload_field').val('');
				$self.addClass('hide');

				$.post(
					ajaxurl, {
						'action': 'themify_remove_video',
						'postid': <?php echo esc_js( $post_id ); ?>,
						'customfield' : '<?php echo esc_js( $meta_box['name'] ); ?>',
						'nonce' : '<?php echo esc_js( $args['themify_custom_panel_nonce'] ); ?>'
					},
					function() {
						$self.parent().find('.themify_upload_field').val('');
						$self.addClass('hide');
					}
				);
			});
		});
	</script>
	<?php

	$html = ob_get_contents();
	ob_end_clean();

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) && $meta_box['before'] ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) && $meta_box['after'] )  $html .= $meta_box['after'];

	$html = $html . '<!-- Themify Video END -->';
	echo $html;
}

/**
 * Returns label before or after field
 * @param string $html field markup
 * @param array $meta_box field definition in key => value format
 * @param bool $echo Whether to echo or return the field
 * @return string field markup with label
 * @since 1.3.2
 */
function themify_meta_field_get_label( $html, $meta_box, $echo = false ) {
	$label_for = ( isset( $meta_box['name'] ) && '' != $meta_box['name'] ) ? esc_attr( $meta_box['name'] ) : '';
	if ( isset( $meta_box['label'] ) && '' != $meta_box['label'] ) {
		if ( isset( $meta_box['label_before'] ) && $meta_box['label_before'] ) {
			$html = sprintf( '<label for="%s">%s %s</label>', $label_for, wp_kses_post( $meta_box['label'] ), $html );
		} else {
			$html = sprintf( '<label for="%s">%s %s</label>', $label_for, $html, wp_kses_post( $meta_box['label'] ) );
		}
	}
	if ( $echo ) echo $html;
	return $html;
}

/**
 * Renders description for meta fields.
 *
 * @uses wp_kses_post()
 *
 * @param string $desc
 *
 * @return string
 */
function themify_meta_field_get_description( $desc = '' ) {
	return ( isset( $desc ) && '' != $desc ) ? '<span class="themify_field_description">' . wp_kses_post( $desc ) . '</span>' : '';
}

function themify_ajax_create_page_pagination() {
	$current_page = isset( $_POST['current_page'] ) ? (int) $_POST['current_page'] : 1;
	$num_of_pages = isset( $_POST['num_of_pages'] ) ? (int) $_POST['num_of_pages'] : 0;
	echo themify_create_page_pagination($current_page, $num_of_pages);
	die;
}

/**
 * Render pagination for specific page.
 *
 * @param Integer $current_page The current page that needs to be rendered.
 * @param Integer $num_of_pages The number of all pages.
 *
 * @return String The HTML with pagination.
 */
function themify_create_page_pagination( $current_page, $num_of_pages ) {
	$links_in_the_middle = 4;
	$links_in_the_middle_min_1 = $links_in_the_middle - 1;
	$first_link_in_the_middle   = $current_page - floor( $links_in_the_middle_min_1 / 2 );
	$last_link_in_the_middle    = $current_page + ceil( $links_in_the_middle_min_1 / 2 );
	if ( $first_link_in_the_middle <= 0 ) {
		$first_link_in_the_middle = 1;
	}
	if ( ( $last_link_in_the_middle - $first_link_in_the_middle ) != $links_in_the_middle_min_1 ) {
		$last_link_in_the_middle = $first_link_in_the_middle + $links_in_the_middle_min_1;
	}
	if ( $last_link_in_the_middle > $num_of_pages ) {
		$first_link_in_the_middle = $num_of_pages - $links_in_the_middle_min_1;
		$last_link_in_the_middle  = (int) $num_of_pages;
	}
	if ( $first_link_in_the_middle <= 0 ) {
		$first_link_in_the_middle = 1;
	}
	$pagination = '';
	if ( $current_page != 1 ) {
		$pagination .= '<a href="/page/' . ( $current_page - 1 ) . '" class="prev page-numbers"><span class="page-numbers-icon"></span></a>';
	}
	if ( $first_link_in_the_middle >= 3 && $links_in_the_middle < $num_of_pages ) {
		$pagination .= '<a href="/page/" class="page-numbers">1</a>';

		if ( $first_link_in_the_middle != 2 ) {
			$pagination .= '<span class="page-numbers extend">...</span>';
		}
	}
	for ( $i = $first_link_in_the_middle; $i <= $last_link_in_the_middle; $i ++ ) {
		if ( $i == $current_page ) {
			$pagination .= '<span class="page-numbers current">' . $i . '</span>';
		} else {
			$pagination .= '<a href="/page/' . $i . '" class="page-numbers">' . $i . '</a>';
		}
	}
	if ( $last_link_in_the_middle < $num_of_pages ) {
		if ( $last_link_in_the_middle != ( $num_of_pages - 1 ) ) {
			$pagination .= '<span class="page-numbers extend">...</span>';
		}
		$pagination .= '<a href="/page/' . $num_of_pages . '" class="page-numbers">' . $num_of_pages . '</a>';
	}
	if ( $current_page != $last_link_in_the_middle ) {
		$pagination .= '<a href="/page/' . ( $current_page + $i ) . '" class="next page-numbers"><span class="page-numbers-icon"></span></a>';
	}

	return $pagination;
}

function themify_ajax_create_inner_page() {
    $selected = array();
	if ( isset( $_POST['post_id'] ) ) {
		$post_id = (int) $_POST['post_id'];
		$selected = get_post_meta( $post_id, 'popup_show', TRUE );
	}
	$type= isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'pages';
	echo themify_create_inner_page($type, $selected);
	die;
}

/**
 * Renders pages, posts types and categories items based on current page.
 *
 * @param string $type The type of items to render.
 * @param array $selected The array of all selected options.
 *
 * @return string The HTML to render items as HTML.
 */
function themify_create_inner_page( $type, $selected ) {
	$posts_per_page = 26;
	$output = '';
	switch ($type) {
		case 'page':
			$key = 'page';
			$posts = get_posts( array( 'post_type' => $key, 'posts_per_page' => -1, 'post_status' => 'publish', 'order' => 'ASC', 'orderby' => 'title',  'no_found_rows' => true,'ignore_sticky_posts'=>true) );
			if( ! empty( $posts ) ) {
				$i = 1;
				$page_id = 1;
				$num_of_single_pages = count($posts);
				$num_of_pages = (int) ceil( $num_of_single_pages / $posts_per_page );
				$output .= '<div class="themify-assignment-items-inner" data-items="' . $num_of_single_pages . '" data-pages="' . $num_of_pages . '">';
				$output .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . '">';
				foreach ( $posts as $post ) :
					if ( $post->post_parent > 0 ) {
						$post->post_name = str_replace( home_url(), '', get_permalink( $post->ID ) );
					}
					$checked = isset( $selected['post_type'][ $key ][ $post->post_name ] ) ? checked( $selected['post_type'][ $key ][ $post->post_name ], 'on', false ) : '';
					/* note: slugs are more reliable than IDs, they stay unique after export/import */
					$output .= '<label><input type="checkbox" data-name="popup_show[post_type][' . $key . '][' . $post->post_name . ']"' . $checked . ' />' . $post->post_title . '</label>';
					if ( $i === ($page_id * $posts_per_page) ) {
						$output .= '</div>';
						$page_id++;
						$output .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . ' is-hidden">';
					}
					$i++;
				endforeach;
				$output .= '</div>';
				if ( $num_of_pages > 1 ) {
					$output .= '<div class="themify-assignment-pagination">';
					$output .= themify_create_page_pagination( 1, $num_of_pages );
					$output .= '</div>';
				}
				$output .= '</div>';
			}
			break;

		case 'category_single':
			$key = 'category_single';
			$terms = get_terms( array('taxonomy'=>'category', 'hide_empty' => true ) );
			if ( ! empty( $terms ) ) {
				$i                   = 1;
				$page_id             = 1;
				$num_of_single_pages = count( $terms );
				$num_of_pages        = (int) ceil( $num_of_single_pages / $posts_per_page );
				$output              .= '<div class="themify-assignment-items-inner" data-items="' . $num_of_single_pages . '" data-pages="' . $num_of_pages . '">';
				$output              .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . '">';
				foreach ( $terms as $term ) :
					$checked = isset( $selected['tax'][ $key ][ $term->slug ] ) ? checked( $selected['tax'][ $key ][ $term->slug ], 'on', false ) : '';
					$output  .= '<label><input type="checkbox" data-name="popup_show[tax][' . $key . '][' . $term->slug . ']" ' . $checked . ' />' . $term->name . '</label>';
					if ( $i === ( $page_id * $posts_per_page ) ) {
						$output .= '</div>';
						$page_id ++;
						$output .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . ' is-hidden">';
					}
					$i++;
				endforeach;
				$output .= '</div>';
				if ( $num_of_pages > 1 ) {
					$output .= '<div class="themify-assignment-pagination">';
					$output .= themify_create_page_pagination( 1, $num_of_pages );
					$output .= '</div>';
				}
				$output .= '</div>';
			}
			break;

		case 'category':
			$key = 'category';
			$terms = get_terms( array('taxonomy'=>'category',  'hide_empty' => true ) );
			if ( ! empty( $terms ) ) {
				$i                   = 1;
				$page_id             = 1;
				$num_of_single_pages = count( $terms );
				$num_of_pages        = (int) ceil( $num_of_single_pages / $posts_per_page );
				$output              .= '<div class="themify-assignment-items-inner" data-items="' . $num_of_single_pages . '" data-pages="' . $num_of_pages . '">';
				$output              .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . '">';
				foreach ( $terms as $term ) :
					$checked = isset( $selected['tax'][ $key ][ $term->slug ] ) ? checked( $selected['tax'][ $key ][ $term->slug ], 'on', false ) : '';
					$output  .= '<label><input type="checkbox" data-name="popup_show[tax][' . $key . '][' . $term->slug . ']" ' . $checked . ' />' . $term->name . '</label>';
					if ( $i === ( $page_id * $posts_per_page ) ) {
						$output .= '</div>';
						$page_id ++;
						$output .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . ' is-hidden">';
					}
					$i++;
				endforeach;
				$output .= '</div>';
				if ( $num_of_pages > 1 ) {
					$output .= '<div class="themify-assignment-pagination">';
					$output .= themify_create_page_pagination( 1, $num_of_pages );
					$output .= '</div>';
				}
				$output .= '</div>';
			}
			break;

		default :
			$post_types = get_post_types( array( 'public' => true ) );
			unset( $post_types['page'] );
			$post_types = array_map( 'get_post_type_object', $post_types );
			$post_id = 1;
			foreach ( $post_types as $key => $post_type ) {
				$output .= '<div id="popup_show-assignment-tab-' . $key . '" class="themify-assignment-inner-tab '. ($post_id > 1 ? 'is-hidden' : '') .'">';
				$posts = get_posts( array( 'post_type' => $key, 'posts_per_page' => -1, 'post_status' => 'publish', 'order' => 'ASC', 'orderby' => 'title',  'no_found_rows' => true,'ignore_sticky_posts'=>true ) );
				if ( ! empty( $posts ) ) {
					$i                   = 1;
					$page_id             = 1;
					$num_of_single_pages = count( $posts );
					$num_of_pages        = (int) ceil( $num_of_single_pages / $posts_per_page );
					$output              .= '<div class="themify-assignment-items-inner" data-items="' . $num_of_single_pages . '" data-pages="' . $num_of_pages . '">';
					$output              .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . '">';
					foreach ( $posts as $post ) {
						$checked = isset( $selected['post_type'][ $key ][ $post->post_name ] ) ? checked( $selected['post_type'][ $key ][ $post->post_name ], 'on', false ) : '';
						/* note: slugs are more reliable than IDs, they stay unique after export/import */
						$output .= '<label><input type="checkbox" data-name="' . esc_attr( 'popup_show[post_type][' . $key . '][' . $post->post_name . ']' ) . '" ' . $checked . ' />' . esc_html( $post->post_title ) . '</label>';
						if ( $i === ( $page_id * $posts_per_page ) ) {
							$output .= '</div>';
							$page_id ++;
							$output .= '<div class="themify-assignment-items-page themify-assignment-items-page-' . $page_id . ' is-hidden">';
						}
						$i++;
					}
					$output .= '</div>';
					if ( $num_of_pages > 1 ) {
						$output .= '<div class="themify-assignment-pagination">';
						$output .= themify_create_page_pagination( 1, $num_of_pages );
						$output .= '</div>';
					}
				}
				$output .= '</div></div></div>';
				$post_id++;
			}
			$output .= '</div>';
			break;
	}
	wp_reset_postdata();

	return $output;
}

function themify_meta_field_assignments( $args ) {
	extract( $args );
	$field = $meta_box;
	$pre = $field['name'];
	$selected = $meta_value;
	if( '' == $selected )
		$selected = array();
	$post_types = apply_filters( 'themify_assignments_post_types', get_post_types( array( 'public' => true ) ) );
	unset( $post_types['page'] );
	unset( $post_types['attachment'] );
	if( isset( $field['exclude_post_types'] ) ) {
		foreach( $field['exclude_post_types'] as $type ) {
			unset( $post_types[$type] );
		}
	}
	$post_types = array_map( 'get_post_type_object', $post_types );

	$taxonomies = apply_filters( 'themify_assignments_taxonomies', get_taxonomies( array( 'public' => true ) ) );
	unset( $taxonomies['category'] );
	if( isset( $field['exclude_taxonomies'] ) ) {
		foreach( $field['exclude_taxonomies'] as $tax ) {
			unset( $taxonomies[$tax] );
		}
	}
	$taxonomies = array_map( 'get_taxonomy', $taxonomies );

	$output = '<div id="themify_assignments_'. $pre .'" class="themify-assignments"><ul class="tf_clearfix">';

	/* build the tab links */
	$output .= '<li><a href="#' . $pre . '-assignment-tab-general">' . __( 'General', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-pages" class="themify-popup-visibility-tab" data-type="page">' . __( 'Pages', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-categories-singles" class="themify-popup-visibility-tab" data-type="category_single">' . __( 'In Category', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-categories" class="themify-popup-visibility-tab" data-type="category">' . __( 'Category Archives', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-post-types" class="themify-popup-visibility-tab" data-type="post">' . __( 'Post Types', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-taxonomies">' . __( 'Taxonomies', 'themify' ) . '</a></li>';
	$output .= '<li><a href="#' . $pre . '-assignment-tab-userroles">' . __( 'User Roles', 'themify' ) . '</a></li>';
	$output .= '</ul>';

	/* build the tab items */
	$output .= '<div id="' . $pre . '-assignment-tab-general" class="themify-assignment-options tf_clearfix">';
	$checked = isset($selected['general']['home']) ? checked($selected['general']['home'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][home]" ' . $checked . ' />' . __( 'Home page', 'themify' ) . '</label>';
	$checked = isset($selected['general']['page']) ? checked($selected['general']['page'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][page]" ' . $checked . ' />' . __( 'Page views', 'themify' ) . '</label>';
	$checked = isset($selected['general']['single']) ? checked($selected['general']['single'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][single]" ' . $checked . ' />' . __( 'Single post views', 'themify' ) . '</label>';
	$checked = isset($selected['general']['search']) ? checked($selected['general']['search'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][search]" ' . $checked . ' />' . __( 'Search pages', 'themify' ) . '</label>';
	$checked = isset($selected['general']['category']) ? checked($selected['general']['category'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][category]" ' . $checked . ' />' . __( 'Category archive', 'themify' ) . '</label>';
	$checked = isset($selected['general']['tag']) ? checked($selected['general']['tag'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][tag]" ' . $checked . ' />' . __( 'Tag archive', 'themify' ) . '</label>';
	$checked = isset($selected['general']['author']) ? checked($selected['general']['author'], 'on', false) : '';
	$output .= '<label><input type="checkbox" data-name="' . $pre . '[general][author]" ' . $checked . ' />' . __( 'Author pages', 'themify' ) . '</label>';

	/* General views for CPT */
	foreach ( get_post_types( array( 'public' => true, 'exclude_from_search' => false, '_builtin' => false ) ) as $key => $post_type ) {
		$post_type = get_post_type_object( $key );
		$checked = isset( $selected['general'][$key] ) ? checked( $selected['general'][$key], 'on', false ) : '';
		$output .= '<label><input type="checkbox" data-name="' . esc_attr( $pre . '[general][' . $key . ']' ) . '" ' . $checked . ' />' . sprintf( __( 'Single %s View', 'themify' ), $post_type->labels->singular_name ) . '</label>';
	}

	/* Custom taxonomies archive view */
	foreach ( get_taxonomies( array( 'public' => true, '_builtin' => false ) ) as $key => $tax ) {
		$tax = get_taxonomy( $key );
		$checked = isset( $selected['general'][$key] ) ? checked( $selected['general'][$key], 'on', false ) : '';
		$output .= '<label><input type="checkbox" data-name="' . esc_attr( $pre . '[general][' . $key . ']' ) . '" ' . $checked . ' />' . sprintf( __( '%s Archive View', 'themify' ), $tax->labels->singular_name ) . '</label>';
	}

	$output .= '</div>'; // tab-general
	// Pages tab
    wp_reset_postdata();
	$output .= '<div id="' . $pre . '-assignment-tab-pages" class="themify-assignment-options themify-assignment-type-options tf_clearfix"  data-type="page" data-post-id="' . get_the_ID() . '">';
	$output .= '</div>'; // tab-pages
	// Category Singles tab
	$output .= '<div id="' . $pre . '-assignment-tab-categories-singles" class="themify-assignment-options themify-assignment-type-options tf_clearfix" data-type="category_single">';
	$output .= '</div>';

	// Categories tab
	$output .= '<div id="' . $pre . '-assignment-tab-categories" class="themify-assignment-options themify-assignment-type-options tf_clearfix" data-type="category">';
	$output .= '</div>'; // tab-categories
	// Post types tab
	$output .= '<div id="' . $pre . '-assignment-tab-post-types" class="themify-assignment-options tf_clearfix" data-type="post">';
	$output .= '<div id="' . $pre . '-themify-assignment-post-types-inner-tabs" class="themify-assignment-inner-tabs">';
	$output .= '<ul class="inline-tabs tf_clearfix">';
	foreach ( $post_types as $key => $post_type ) {
		$output .= '<li><a href="#' . $pre . '-assignment-tab-' . $key . '">' . esc_html( $post_type->label ) . '</a></li>';
	}
	$output .= '</ul>';
	$output .= '<div class="themify-assignment-type-options tf_clearfix" data-type="post"></div>';
	$output .= '</div>';
	$output .= '</div>'; // tab-post-types
	// Taxonomies tab
	$output .= '<div id="' . $pre . '-assignment-tab-taxonomies" class="themify-assignment-options tf_clearfix">';
	$output .= '<div id="' . $pre . '-themify-assignment-taxonomies-inner-tabs" class="themify-assignment-inner-tabs">';
	$output .= '<ul class="inline-tabs tf_clearfix">';
	foreach ( $taxonomies as $key => $tax ) {
		$output .= '<li><a href="#' . $pre . '-assignment-tab-' . $key . '">' . esc_html($tax->label) . '</a></li>';
	}
	$output .= '</ul>';
	foreach ( $taxonomies as $key => $tax ) {
		$output .= '<div id="' . $pre . '-assignment-tab-' . $key . '" class="tf_clearfix">';
		$terms = get_terms( array('taxonomy'=>$key,  'hide_empty' => true ) );
		if ( ! empty( $terms ) ) : foreach ( $terms as $term ) :
				$checked = isset( $selected['tax'][$key][$term->slug] ) ? checked( $selected['tax'][$key][$term->slug], 'on', false ) : '';
				$output .= '<label><input type="checkbox" data-name="' . esc_attr( $pre . '[tax][' . $key . '][' . $term->slug . ']' ) . '" ' . $checked . ' />' . esc_html( $term->name ) . '</label>';
			endforeach;
		endif;
		$output .= '</div>';
	}
	$output .= '</div>';
	$output .= '</div>'; // tab-taxonomies
	// User Roles tab
	$output .= '<div id="' . $pre . '-assignment-tab-userroles" class="themify-assignment-options tf_clearfix">';
	foreach ( $GLOBALS['wp_roles']->roles as $key => $role ) {
		$checked = isset( $selected['roles'][$key] ) ? checked( $selected['roles'][$key], 'on', false ) : '';
		$output .= '<label><input type="checkbox" data-name="' . esc_attr( $pre . '[roles][' . $key . ']' ) . '" ' . $checked . ' />' . esc_html( $role['name'] ) . '</label>';
	}
	$output .= '</div>'; // tab-userroles

	$output .= '<div class="values">';
		$output .= themify_array_to_input( $selected, $pre );
	$output .= '</div>';

	$output .= '</div>';

	echo $output;
}


/**
 * Returns a PLUPLOAD instance. If it's a multisite, checks user quota and shows message if it's not enough to continue upload files.
 *
 * @param string $id
 * @param array $params $label = '', $thumbs = false, $filelist = false, $multiple = false, $message = '', $fallback = ''
 *
 * @return string
 */
function themify_get_uploader( $id = '', $args = array() ){

	$defaults = array(
		'label'		=> __('Upload', 'themify'),
		'preset'	=> false,
		'preview'   => false,
		'tomedia'	=> false,
		'topost'	=> '',
		'fields'	=> '',
		'featured'	=> '',
		'message'	=> '',
		'fallback'	=> '',
		'dragfiles' => false,
		'confirm'	=> '',
		'medialib'	=> false,
		'formats'	=> 'jpg,jpeg,gif,png,ico,zip,txt,svg',
		'type'		=> 'image',
		'action'    => 'themify_plupload',
		'button_class' => '',
	);
	// Extract $label, $preset, $thumbs, $filelist, $multiple, $message, $fallback, $confirm
	$args = wp_parse_args($args, $defaults);

	$upload_visible = false;

	if ( is_multisite() && !is_upload_space_available() ) {
		if( '' != $args['message'] ){
			$html = $args['message'];
		} else {
			$html = '<small>' . sprintf( __( 'Sorry, you have filled your %s MB storage quota so uploading has been disabled.', 'themify' ), get_space_allowed() ) . '</small>';
		}
	} else {
		if( '' != $args['fallback'] )
			$html = $args['fallback'];
		else {
			ob_start();
			// $id is the name of form field. File urls will be submitted in $_POST using this key.
			// If $id == "file" then $_POST["file"] will have all the file urls
			?>
			<!--<input type="hidden" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" value="" />-->
			<?php if( $args['dragfiles'] ){ ?>
				<div id="<?php echo esc_attr( $id . 'drag-drop-area' ); ?>" class="plupload-drag-drop-area">
			<?php } ?>
				<?php
				$classes = '';
				if($args['preset']) $classes .= 'add-preset ';
				if($args['preview']) $classes .= 'add-preview ';
				if($args['tomedia']) $classes .= 'add-to-media ';

				$datas = array();
				if('' != $args['topost']) $datas[] = 'data-postid="' . esc_attr( $args['topost'] ) . '"';
				if('' != $args['fields']) $datas[] = 'data-fields="' . esc_attr( $args['fields'] ) . '"';
				if('' != $args['confirm']) $datas[] = 'data-confirm="' . esc_attr( $args['confirm'] ) . '"';
				if('' != $args['featured']) $datas[] = 'data-featured="' . esc_attr( $args['featured'] ) . '"';
				if('' != $args['formats']) $datas[] = 'data-formats="' . esc_attr( $args['formats'] ) . '"';
				$datas[] = 'data-action="' . esc_attr( $args['action'] ) . '"';
				?>
				<div class="plupload-upload-uic hide-if-no-js <?php echo esc_attr( $classes ); ?>" <?php echo implode( ' ', $datas ); ?> id="<?php echo esc_attr( $id ); ?>plupload-upload-ui">

					<input id="<?php echo esc_attr( $id ); ?>plupload-browse-button" type="button" value="<?php echo esc_attr( $args['label'] ); ?>" class="button plupload-button <?php echo esc_attr( 'plupload-' . $id . ' ' . $args['button_class'] ); ?>" />

					<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id . 'themify-plupload'); ?>"></span>

				</div>

			<?php if( $args['dragfiles'] ){ ?>
					<small><?php _e(' or drag files here.', 'themify'); ?></small>
				</div>
			<?php } else { ?>
				<span id="<?php echo esc_attr( $id . 'drag-drop-area' ); ?>" style="display: none;"></span>
			<?php }
			
			$html = ob_get_contents();
			ob_end_clean();
		}
		$upload_visible = true;
	}

	if( $args['medialib'] ) {
		ob_start();
		?>
		<?php
			if ( 'audio' == $args['type'] ) {
				$data_uploader_title = __( 'Browse Audio', 'themify' );
				$medialib_btn_text = __('Insert Audio', 'themify');
			} elseif ( 'video' == $args['type'] ) {
				$data_uploader_title = __( 'Browse Video', 'themify' );
				$medialib_btn_text = __('Insert Video', 'themify');
			} elseif ( in_array($args['formats'],array('woff','woff2','eot','ttf','svg')) ) {
				$data_uploader_title = __( 'Browse Font', 'themify' );
				$medialib_btn_text = __('Insert Font', 'themify');
			}else {
				$data_uploader_title = __( 'Browse Image', 'themify' );
				$medialib_btn_text = __('Insert Image', 'themify');
			}
			$medialib_datas = array(
				'action' => 'themify_metabox_media_lib_browse',
				'media_lib_nonce' => wp_create_nonce( 'media_lib_nonce' ),
				'featured' => ($args['featured'] != '') ? 1 : 0,
				'field_name' => $args['fields'],
				'post_id' => $args['topost']
			);
		?>
		<div class="themify_medialib_wrapper">
			<?php if($upload_visible): echo '&nbsp;<em>' . __( 'or', 'themify' ) . '</em>'; endif; ?>&nbsp;
			<a href="#" class="themify-media-lib-browse" data-submit='<?php echo json_encode( $medialib_datas ); ?>' data-uploader-title="<?php echo esc_attr( $data_uploader_title ) ?>" data-uploader-button-text="<?php echo esc_attr( $medialib_btn_text ); ?>" data-fields="<?php echo esc_attr( $args['fields'] ); ?>" data-type="<?php echo esc_attr( $args['type'] ); ?>"><?php _e( 'Browse Library', 'themify' ) ?></a>
		</div>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();
	}

	return $html;
}

/**
 * Echoes a PLUPLOAD uploader
 *
 * @uses themify_get_uploader
 *
 * @param string $id
 * @param array $args
 *
 */
function themify_uploader($id = '', $args = array()){
	echo themify_get_uploader( $id, $args );
}

function themify_wp_ajax_plupload_image() {
	$imgid = (int) $_POST['imgid'];
	! empty( $_POST[ '_ajax_nonce' ] ) && check_ajax_referer($imgid . 'themify-plupload');
	if( ! current_user_can( 'upload_files' ) ) {
		die;
	}

	/** Decide whether to send this image to Media. @var String */
	$add_to_media_library = isset( $_POST['tomedia'] ) ? sanitize_text_field( $_POST['tomedia'] ) : false;
	/** If post ID is set, uploaded image will be attached to it. @var String */
	$postid = isset( $_POST['topost'] )? (int) $_POST['topost'] : '';
 
	/** Handle file upload storing file|url|type. @var Array */
	$file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_plupload'));
	
	// if $file returns error, return it and exit the function
	if ( isset( $file['error'] ) && ! empty( $file['error'] ) ) {
		echo json_encode($file);
		exit;
	}

	//let's see if it's an image, a zip file or something else
	$ext = explode('/', $file['type']);
	
	//Image Upload routines
	if( 'tomedia' == $add_to_media_library ) {
		
		// Insert into Media Library
		// Set up options array to add this file as an attachment
		$attachment = array(
			'post_mime_type' => sanitize_mime_type($file['type']),
			'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
			'post_status' => 'inherit'
		);
		
		if( $postid ){
			$attach_id = wp_insert_attachment( $attachment, $file['file'], $postid );
		} else {
			$attach_id = wp_insert_attachment( $attachment, $file['file'] );
		}
		$file['id'] = $attach_id;

		// Common attachment procedures
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file['file'] );
		wp_update_attachment_metadata($attach_id, $attach_data);
		
		if( $postid ) {
			
			$full = wp_get_attachment_image_src( $attach_id, 'full' );
			$fields = sanitize_text_field( $_POST['fields'] );
			update_post_meta( $postid, $fields, $full[0] );
			update_post_meta( $postid, '_' . $fields . '_attach_id', $attach_id );
		}

		$thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );

		//Return URL for the image field in meta box
		$file['thumb'] = $thumb[0];
	}
	$file['type'] = $ext[1];
	// send the uploaded file url in response
	echo json_encode( $file );
	exit;
}

/**
 * @todo: remove this
 */
function themify_metabox_media_lib_browse() {
	if ( ! wp_verify_nonce( $_POST['media_lib_nonce'], 'media_lib_nonce' ) ) die(-1);
	if( ! current_user_can( 'upload_files' ) ) {
		die;
	}

	$file = array();
	$postid = (int) $_POST['post_id'];
	$attach_id = (int) $_POST['attach_id'];

	$full = wp_get_attachment_image_src( $attach_id, 'full' );
	update_post_meta($postid, sanitize_text_field( $_POST['field_name'] ), $full[0]);
	update_post_meta($postid, '_' . sanitize_text_field( $_POST['field_name'] ) . '_attach_id', $attach_id);

	$thumb = wp_get_attachment_image_src( $attach_id, 'thumbnail' );

	//Return URL for the image field in meta box
	$file['thumb'] = $thumb[0];

	echo json_encode($file);

	exit();
}

function themify_meta_field_gallery_shortcode( $args ) {
	extract( $args );
	wp_enqueue_script( 'gallery-shortcode' );

	if( isset($meta_box['meta']) && '' != $meta_box['meta']['size'] && 'small' == $meta_box['meta']['size'] ) {
		$class = 'small';
	} else {
		$class = '';
	}
	?>
	<textarea name="<?php echo esc_attr( $meta_box['name'] ); ?>" size="55" rows="4" class="themify_input_field themify-gallery-shortcode-input <?php echo esc_attr( $class ); ?>"><?php echo esc_textarea( $meta_value ); ?></textarea>

	<div class="themify-gallery-shortcode-btn">
		<a href="#" class="themify-gallery-shortcode-btn"><?php _e('Insert Gallery', 'themify') ?></a>
	</div>

	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description"><?php echo wp_kses_post( $meta_box['description'] ); ?></span>
	<?php endif; // meta_box description ?>
	<?php
}

function themify_meta_field_multi( $args, $call_before_after = true, $echo = true ) {
	global $post;
	extract( $args, EXTR_OVERWRITE );

	foreach ($meta_box['meta']['fields'] as $field) {
		if ( is_callable( 'themify_meta_field_'.$field['type'] ) ) {
			// @todo
			if( isset( $field['display_callback'] ) && is_callable( $field['display_callback'] ) ) {
				$show = (bool) call_user_func( $field['display_callback'], $field );
				if( ! $show ) { // if display_callback returns "false",
					continue;  // do not output the field
				}
			}
			$call_args = array(
				'meta_box' => $field,
				'meta_value' => get_post_meta( $post_id, $field['name'], true ),
				'call_before_after' => false,
				'post_id' => $post_id,
				'themify_custom_panel_nonce' => $themify_custom_panel_nonce,
			);

			// Do nested toggle for multi fields
			if ( isset( $field['enable_toggle'] ) ) {
				echo '<div class="enable_toggle ' . $field['enable_toggle'] . '">';
			} elseif ( isset( $field['toggle'] ) ) {
				$field_toggle = is_array( $field['toggle'] ) ? implode( ' ', $field['toggle'] ) : $field['toggle'];
				echo '<div class="themify-toggle ' . $field_toggle . '">';
			}

			// Render the field
			call_user_func('themify_meta_field_'.$field['type'], $call_args);

			// End nested toggle for multi fields
			if ( isset( $field['enable_toggle'] ) ) {
				echo '</div>';
			} elseif ( isset( $field['toggle'] ) ) {
				echo '</div>';
			}
			if ( ! ( $field === end( $meta_box['meta']['fields'] ) ) ) {
				echo isset( $meta_box['meta']['separator'] ) ? $meta_box['meta']['separator'] : '';
			}
		}
	}
	if ( isset( $meta_box['meta']['description'] ) && '' != $meta_box['meta']['description'] ) {
		echo '<span class="themify_field_description">' . $meta_box['meta']['description'] . '</span>';
	}
}

if( ! function_exists( 'themify_meta_field_query_category' ) ) :
/**
 * query_category field type, display an option to select categories for Query Posts feature
 *
 * @since 2.8.8
 */
function themify_meta_field_query_category( $args ) {
	extract( $args );

	$terms_tax = isset($meta_box['meta']['taxonomy'])? $meta_box['meta']['taxonomy']: 'category';

	$terms_options = '';
	$terms_by_tax = get_terms($terms_tax);
	if( ! empty( $terms_by_tax ) && ! is_wp_error( $terms_by_tax ) ) {
		$terms_list = array();
		$terms_list['0'] = array(
			'title' => __( 'All Categories', 'themify' ),
			'slug'	=> '0'
		);
		foreach ($terms_by_tax as $term) {
			$terms_list[$term->term_id] = array(
				'title' => $term->name,
				'slug'	=> $term->slug
			);
		}
		foreach ($terms_list as $term_id => $term) {
			$term_selected = '';
			if(!is_numeric($meta_value)) {
				if($meta_value == $term['slug']) $term_selected = 'selected="selected"';
			} else {
				if($meta_value == $term_id) $term_selected = 'selected="selected"';
			}
			$terms_options .= sprintf(
				'<option value="%s" data-termid="%s" %s>%s</option>',
				$term['slug'],
				$term_id,
				$term_selected,
				$term['title']
			);
		}
		?>
		<select id="<?php echo esc_attr( $meta_box['name'] ); ?>" class="query_category_single">
			<option></option>
			<?php echo !empty( $terms_options ) ? $terms_options : ''; ?>
		</select>
		<?php _e('or', 'themify'); ?>
	<?php } ?> 
	<input type="text" class="query_category" value="<?php echo esc_attr( $meta_value ); ?>">

	<input type="hidden" value="<?php echo esc_attr( $meta_value ); ?>" name="<?php echo esc_attr( $meta_box['name'] ); ?>" class="val">
	<?php if ( isset( $meta_box['description'] ) ) : ?>
		<span class="themify_field_description"><?php echo wp_kses_post( $meta_box['description'] ); ?></span>
	<?php endif; // meta_box description
}
endif;

function themify_meta_field_repeater_template( $meta_box, $data, $id ) {
	echo '<div class="themify-repeater-row" data-id="'. $id .'">',
		'<div class="themify-repeater-remove-row"><a href="#"></a></div>';
	foreach ( $meta_box['fields'] as $field ) {
		if ( is_callable( 'themify_meta_field_'.$field['type'] ) ) {
			echo '<div class="themify_field_row tf_clearfix">';
			! empty( $field['title'] ) && printf( '<div class="themify_field_title">%s</div>', $field['title'] );
			$field_id = $field['name'];
			$field['name'] = $meta_box['name'] . '[' . $id . '][' . $field['name'] . ']';

			$call_args = array(
				'meta_box' => $field,
				'meta_value' => isset( $data[$field_id] ) ? $data[$field_id] : '',
				'call_before_after' => false,
				'post_id' => 0,
				'themify_custom_panel_nonce' => 0,
			);

			// Do nested toggle for multi fields
			if ( isset( $field['enable_toggle'] ) ) {
				echo '<div class="enable_toggle ' . $field['enable_toggle'] . '">';
			} elseif ( isset( $field['toggle'] ) ) {
				$field_toggle = is_array( $field['toggle'] ) ? implode( ' ', $field['toggle'] ) : $field['toggle'];
				echo '<div class="themify-toggle ' . $field_toggle . '">';
			}

			printf( '<div class="themify_field themify_field-%s">', $field['type'] );
				// Render the field
				call_user_func('themify_meta_field_'.$field['type'], $call_args);
			echo '</div>';

			// End nested toggle for multi fields
			if ( isset( $field['enable_toggle'] ) || isset( $field['toggle'] )) {
				echo '</div>';
			}
			if ( ! ( $field === end( $meta_box['fields'] ) ) ) {
				echo isset( $meta_box['meta']['separator'] ) ? $meta_box['meta']['separator'] : '';
			}

			echo '</div>';
		}
	}
	echo '</div>';
}

function themify_meta_field_repeater( $args, $call_before_after = true, $echo = true ) {
	global $post;
	extract( $args, EXTR_OVERWRITE );

	echo '<template class="themify-repeater-template">';
		themify_meta_field_repeater_template( $meta_box, array(), '__i__' );
	echo '</template><!-- .themify-repeater-template-->';
	
	$meta_value = isset($meta_value) ? $meta_value : '';
	$meta_value = !is_array( $meta_value ) && !empty($meta_box['show_first']) && true === $meta_box['show_first']  ? array(array()) : $meta_value ;
	echo '<div class="themify-repeater-rows">';
		if( isset( $meta_value ) && is_array( $meta_value ) ) {
			foreach( $meta_value as $id => $values ) {
				themify_meta_field_repeater_template( $meta_box, $values, $id );
			}
		}
	echo '</div><!-- .themify-repeater-rows -->';

	$label = isset( $meta_box['add_new_label'] ) ? $meta_box['add_new_label'] : __( 'Add New', 'themify' );
	echo '<button class="themify-repeater-add button button-secondary">' , $label , '</button>';

	if ( isset( $meta_box['meta']['description'] ) && '' != $meta_box['meta']['description'] ) {
		echo '<span class="themify_field_description">' , $meta_box['meta']['description'] , '</span>';
	}
}

/**
 * Generates Image Radio
 *
 * @param array $args Field settings
 *
 * @since 1.3.2
 * @return string
 */
function themify_meta_field_image_radio( $args ) {
	$meta_box = $args['meta_box'];
	$meta_value = $args['meta_value'];
	extract( $args, EXTR_OVERWRITE );

	$html = '';
	foreach ( $meta_box['meta'] as $option ) {

		if ( ( '' == $meta_value || ! $meta_value || ! isset( $meta_value ) ) && ( isset( $option['selected'] ) && $option['selected'] ) ) {
			$meta_value = $option['value'];
		}

		if ( $meta_value == $option['value'] ) {
			$class = 'selected';
		} else {
			$class = '';
		}

		if(isset($meta_box['show_title'])){
			$title = isset( $option['title'] )? $option['title']: ucwords( str_replace( '-', ' ', $option['value'] ) );
		} else {
			$title = '';
		}

		// Check image src whether absolute url or relative url
		$img_src = ( '' != parse_url( $option['img'], PHP_URL_SCHEME) ) ? $option['img'] : get_template_directory_uri() . '/' . $option['img'];

		$rid = $meta_box['name'] . '-' . esc_attr( $option['value'] );
		$html .= sprintf('
		<div class="tm-radio-option">
			<input type="radio" name="%s" id="%s" value="%s" %s />
			<label for="%s"><img src="%s" alt="%s"/></label>
			<span class="tm-option-title">%s</span>
		</div>
		',
			$meta_box['name'], $rid, $option['value'], checked( $meta_value, $option['value'], false ),
			$rid,
			$img_src, $title, $title
		);
	}

	if(isset($meta_box['label']) && '' != $meta_box['label'])
		$html = sprintf('<label for="%s">%s %s</label>', esc_attr( $meta_box['name'] ), $html, esc_attr( $meta_box['label'] ));

	$html .= isset( $meta_box['description'] )? themify_meta_field_get_description($meta_box['description']) : '';

	if( isset( $meta_box['before'] ) ) $html = $meta_box['before'] . $html;
	if( isset( $meta_box['after'] ) )  $html .= $meta_box['after'];

	echo $html;
}

/**
 * Display a hidden input. Value is retrieve from DB, or the "default" parameter if provided.
 */
function themify_meta_field_hidden( $args ) {
	$meta_value = $args['meta_value'];
	if ( isset( $args['meta_box']['default'] ) && empty( $meta_value ) && ! ( $meta_value === 0 || $meta_value === '0' ) ) {
		$meta_value = $args['meta_box']['default'];
	}

	$html = sprintf('<input type="hidden" id="%s" name="%s" value="%s" />',
		esc_attr( $args['meta_box']['name'] ), esc_attr( $args['meta_box']['name'] ), esc_attr( $meta_value ) );

	echo $html;
}
