<?php
defined('ABSPATH') || exit;

/**
 * Template Part
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-overlay-content.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
global $ThemifyBuilder;
$fields_default = array(
    'selected_layout_part' => '',
    'style' => 'overlay',
    'add_css_layout_part' => '',
    'icon' => '',
    'icon_title' => '',
    'overlay_type' => 'overlay',
    'expand_mode' => 'overlap',
    'overlay_width' => '100',
    'overlay_height' => '100',
    'overlay_width_unit' => '%',
    'overlay_height_unit' => '%',
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default = null;
$container_class = apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $args['mod_name'], $args['module_ID'], $fields_args['add_css_layout_part']
	), $args['mod_name'], $args['module_ID'], $fields_args);

$container_props = apply_filters('themify_builder_module_container_props', array(
    'class' => implode(' ', $container_class),
    'data-overlay' => $fields_args['overlay_type'],
	), $fields_args, $args['mod_name'], $args['module_ID']);
$overlay_id = 'tb_oc_' . $args['module_ID'];
$args = null;
$isLoop = $ThemifyBuilder->in_the_loop === true;
$ThemifyBuilder->in_the_loop = true;
$layoutPart = $fields_args['selected_layout_part'] !== '' ? do_shortcode('[themify_layout_part slug="' . $fields_args['selected_layout_part'] . '"]') : '';
$is_overlay = 'overlay' === $fields_args['overlay_type'];
$overlay_style = 'width:' . $fields_args['overlay_width'] . $fields_args['overlay_width_unit'] . ';height:' . $fields_args['overlay_height'] . $fields_args['overlay_height_unit'] . ';';
if ($is_overlay === true && 'overlay' !== $fields_args['style'] && ( '100%' !== $fields_args['overlay_width'] . $fields_args['overlay_width_unit'] || '100%' !== $fields_args['overlay_height'] . $fields_args['overlay_height_unit'] )) {
    if ('100' !== $fields_args['overlay_height'] && 'px' !== $fields_args['overlay_height_unit'] && ('slide_down' === $fields_args['style'] || 'slide_up' === $fields_args['style'])) {
	$overlay_style .= 'slide_down' === $fields_args['style'] ? 'bottom' : 'top';
	$overlay_style .= ':' . (( 100 - $fields_args['overlay_height'] ) / 2) . $fields_args['overlay_height_unit'] . ';';
    }
    if ('100' !== $fields_args['overlay_width'] && 'px' !== $fields_args['overlay_width_unit'] && ('slide_left' === $fields_args['style'] || 'slide_right' === $fields_args['style'])) {
	$overlay_style .= 'slide_left' === $fields_args['style'] ? 'left' : 'right';
	$overlay_style .= ':' . (( 100 - $fields_args['overlay_width'] ) / 2) . $fields_args['overlay_width_unit'];
    }
}
$icon = !empty($fields_args['icon']) ? sprintf('<em>%s</em>', themify_get_icon($fields_args['icon'])) : '';
if (Themify_Builder::$frontedit_active === false) {
    $container_props['data-lazy'] = 1;
}
?>
<!-- module overlay-content -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
    <?php $container_props = $container_class = null; ?>
    <a href="<?php echo $is_overlay === true ? '#' . $overlay_id : '#'; ?>" rel="nofollow" class="tb_ov_co_icon_wrapper tf_inline_b<?php echo empty($icon) ? ' tb_ov_no_icon' : ''; ?>">
        <span class="tb_ov_co_icon_outer tf_inline_b"><span class="tb_ov_co_icon tf_box tf_rel tf_inline_b<?php echo empty($icon) ? ' tf_vmiddle' : ''; ?>"><?php echo $icon; ?></span></span>
	<?php echo!empty($fields_args['icon_title']) ? '<span class="tb_ov_co_icon_title tf_vmiddle">' . $fields_args['icon_title'] . '</span>' : ''; ?>
    </a>
    <?php echo $is_overlay === false ? '<div class="tb_oc_expand_container tb_oc_expand_' . $fields_args['expand_mode'] . '">' : ''; ?>
    <div id="<?php echo $overlay_id; ?>" style="<?php echo $overlay_style; ?>" class="tb_oc_overlay tf_scrollbar tf_box<?php echo $is_overlay === true ? ' sidemenu sidemenu-off tb_content_overlay_' . $fields_args['style'] : ' tb_oc_expandable tf_abs tf_w tf_h'; ?>">
	<?php if ($is_overlay === true): ?>
    	<a id="<?php echo $overlay_id; ?>_close" href="javascript:;" rel="nofollow" class="tb_ov_close tf_box"><span class="tb_ov_close_inner tf_close tf_rel"><span class="screen-reader-text">Close Overlay</span></span></a>
	<?php endif; ?>
        <div class="tb_overlay_content_lp"><?php echo $layoutPart; ?></div>
    </div>
    <?php if (true === $is_overlay): ?>
        <div class="tb_oc_overlay_layer" data-id="<?php echo $overlay_id; ?>"></div>
    <?php endif; ?>
    <?php echo $is_overlay === false ? '</div>' : ''; ?>

    <?php if ($is_overlay === true && in_array($fields_args['style'], array('slide_left', 'slide_right'), true)) : ?>
        <style>
    	.<?php echo $element_id; ?> .tb_content_overlay_<?php echo $fields_args['style']; ?>{
    	    transform:translateX(<?php echo ( $fields_args['style'] === 'slide_left' ? '-' : '' ), $fields_args['overlay_width'], $fields_args['overlay_width_unit']; ?>)
    	}
        </style>
    <?php endif; ?>
</div><!-- /module overlay-content -->
<?php
$ThemifyBuilder->in_the_loop = $isLoop;
