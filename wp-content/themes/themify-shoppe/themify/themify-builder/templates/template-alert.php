<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Alert
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-alert.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
    'mod_title_alert' => '',
    'appearance_alert' => '',
    'layout_alert' => 'button-right',
    'color_alert' => 'tb_default_color',
    'heading_alert' => '',
    'title_tag' => 'h3',
    'text_alert' => '',
    'alert_button_action' => 'close',
    'alert_message_text' => '',
    'action_btn_link_alert' => '#',
    'open_link_new_tab_alert' => '',
    'lightbox_width' => '',
    'lightbox_height' => '',
    'lightbox_width_unit' => 'px',
    'lightbox_height_unit' => 'px',
    'action_btn_text_alert' => false,
    'action_btn_color_alert' => 'tb_default_color',
    'action_btn_appearance_alert' => '',
    'alert_no_date_limit' => '',
    'alert_start_at' => '',
    'alert_end_at' => '',
    'alert_show_to' => '',
    'alert_limit_count' => '',
    'alert_auto_close' => '',
    'alert_auto_close_delay' => '',
    'css_alert' => '',
    'animation_effect' => ''
);

if (isset($args['mod_settings']['appearance_alert'])) {
	    $args['mod_settings']['appearance_alert'] = self::get_checkbox_data($args['mod_settings']['appearance_alert']);
	    Themify_Builder_Model::load_appearance_css($args['mod_settings']['appearance_alert']);
}
if (isset($args['mod_settings']['action_btn_appearance_alert'])) {
    $args['mod_settings']['action_btn_appearance_alert'] = self::get_checkbox_data($args['mod_settings']['action_btn_appearance_alert']);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
Themify_Builder_Model::load_color_css($fields_args['color_alert']);
Themify_Builder_Model::load_color_css($fields_args['action_btn_color_alert']);
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class =apply_filters('themify_builder_module_classes', array(
    'module ui',
    'module-' . $mod_name, 
    $element_id, 
    $fields_args['layout_alert'], 
    $fields_args['color_alert'], 
    $fields_args['css_alert'],
    $fields_args['appearance_alert']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' =>  implode(' ', $container_class),
    'data-auto-close' => !empty($fields_args['alert_auto_close']) && !empty($fields_args['alert_auto_close_delay']) ? $fields_args['alert_auto_close_delay'] : '',
    'data-module-id' => $element_id,
    'data-alert-limit' => $fields_args['alert_limit_count'],
	)), $fields_args, $mod_name, $element_id);

// Button action
$ui_class=array('ui', 'builder_button', $fields_args['action_btn_appearance_alert'],$fields_args['action_btn_color_alert']);
if('url' !== $fields_args['alert_button_action'] ){
    $ui_class[]='alert-close';
}
$url = $fields_args['alert_button_action'] === 'url' ? esc_url($fields_args['action_btn_link_alert']) : '#';
$button_attr = '';
if ( $fields_args['alert_button_action'] === 'url' ) {
	if ( 'yes' === $fields_args['open_link_new_tab_alert'] ) {
		$button_attr .= ' rel="noopener" target="_blank"';
	} else if ( 'lightbox' === $fields_args['open_link_new_tab_alert'] ) {
		$ui_class[]= 'themify_lightbox';
		if ( $fields_args['lightbox_width'] !== '' || $fields_args['lightbox_height'] !== '' ) {
			$lightbox_settings = array();
			$lightbox_settings[] = $fields_args['lightbox_width'] !== '' ? $fields_args['lightbox_width'] . $fields_args['lightbox_width_unit'] : '';
			$lightbox_settings[] = $fields_args['lightbox_height'] !== '' ? $fields_args['lightbox_height'] . $fields_args['lightbox_height_unit'] : '';
			$button_attr .= sprintf(' data-zoom-config="%s"', implode('|', $lightbox_settings));
			$lightbox_settings=null;
		}
	}
}

if ($fields_args['alert_button_action'] === 'message' && !empty($fields_args['alert_message_text'])) {
    $button_attr = ' data-alert-message="' . esc_attr($fields_args['alert_message_text']) . '"';
}

// Alert visibility
$is_alert_visible = true;
if (Themify_Builder::$frontedit_active===false) {
    if (!empty($fields_args['alert_no_date_limit']) && (!empty($fields_args['alert_start_at']) || !empty($fields_args['alert_end_at']) )) {
		$now = time();
		if (!empty($fields_args['alert_start_at'])) {
			$is_alert_visible = ( strtotime($fields_args['alert_start_at']) - $now ) < 0;
		}
		if ($is_alert_visible===true && !empty($fields_args['alert_end_at'])) {
			$is_alert_visible = ( $now - strtotime($fields_args['alert_end_at']) ) < 0;
		}
    }

    if ($is_alert_visible===true && !empty($fields_args['alert_show_to'])) {
		if ($fields_args['alert_show_to'] === 'guest') {
			$is_alert_visible = !is_user_logged_in();
		} 
		elseif ($fields_args['alert_show_to'] === 'user') {
			$is_alert_visible = is_user_logged_in();
		}
    }

    if ($is_alert_visible===true && !empty($fields_args['alert_limit_count']) && isset($_COOKIE[$element_id])) {
		$user_cookie = (int) $_COOKIE[ $element_id ];
		if ( $user_cookie && $user_cookie >= $fields_args['alert_limit_count'] ) {
				$is_alert_visible = false;
		}
    }
    $container_props['data-lazy']=1;
}
$args=null;
if ($is_alert_visible===true):
    ?>
    <!-- module alert -->
	<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
		<?php $container_props=$container_class=null;
		echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_alert');
		?>
		<div class="alert-inner">
			<div class="alert-content">
				<<?php echo $fields_args['title_tag'];?> class="alert-heading"<?php self::add_inline_edit_fields('heading_alert')?>><?php echo $fields_args['heading_alert'] ?></<?php echo $fields_args['title_tag'];?>>
				<div class="tb_text_wrap"<?php self::add_inline_edit_fields('text_alert')?>>
					<?php echo apply_filters('themify_builder_module_content', $fields_args['text_alert']);?>
				</div>
			</div>
			<!-- /alert-content -->
			<?php if ($fields_args['action_btn_text_alert']) : ?>
				<div class="alert-button">
					<a href="<?php echo $url; ?>" class="<?php echo implode(' ', $ui_class); ?>"<?php echo $button_attr; ?>>
						<span class="tb_alert_text"<?php self::add_inline_edit_fields('action_btn_text_alert')?>><?php echo $fields_args['action_btn_text_alert'] ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<div class="alert-close tf_close"></div>
		<!-- /alert-content -->
	</div>
	<!-- /module alert -->
<?php endif; ?>
