<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Map
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-map.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
*/

$fields_default = array(
    'mod_title_map' => '',
    'address_map' => '',
    'latlong_map' => '',
    'zoom_map' => 8,
    'w_map' => '100',
    'w_map_static' => 500,
    'w_map_unit' => '%',
    'h_map' => '300',
    'h_map_unit' => 'px',
    'b_style_map' => 'solid',
    'b_width_map' => '',
    'b_color_map' => '',
    'type_map' => 'ROADMAP',
    'bing_type_map'=>'aerial',
    'scrollwheel_map' => 'disable',
    'draggable_map' => 'enable',
    'map_control' => 'no',
    'draggable_disable_mobile_map' => 'yes',
    'info_window_map' => '',
    'map_provider' => 'google',
    'map_display_type' => 'dynamic',
    'css_map' => '',
    'animation_effect' => ''
);
if (!empty($args['mod_settings']['address_map'])) {
    $args['mod_settings']['address_map'] = preg_replace('/\s+/', ' ', trim($args['mod_settings']['address_map']));
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
if($fields_args['w_map']>100 || (isset($fields_args['unit_w']) && !isset($args['mod_settings']['w_map_unit']) && $fields_args['unit_w']==-1)){
    $fields_args['w_map_unit'] ='px';
}
if(isset($fields_args['unit_h']) && !isset($args['mod_settings']['w_map_unit']) && $fields_args['unit_h']==-1){
    $fields_args['h_map_unit'] ='px';
}
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

$info_window_map = $fields_args['info_window_map'] === '' ? sprintf('<b>%s</b><br/><p>%s</p>', __('Address', 'themify'), $fields_args['address_map']) : $fields_args['info_window_map'];

$container_class = apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id, 
    $fields_args['css_map']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' => implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
$args=null;
$style = '';

// specify border
if ($fields_args['b_width_map'] !== '') {
    $style = 'border: ' . $fields_args['b_style_map'] . ' ' . $fields_args['b_width_map'] . 'px';
    if ($fields_args['b_color_map'] !== '') {
	$style.=' ' . Themify_Builder_Stylesheet::get_rgba_color($fields_args['b_color_map']);
    }
    $style .= ';';
}
$notice='';
if ( current_user_can( 'manage_options' ) ) {
	$map_key = themify_builder_get( 'setting-' . $fields_args['map_provider'] . '_map_key', 'builder_settings_' . $fields_args['map_provider'] . '_map_key' );
	if ( empty( $map_key ) ) {
		$notice = sprintf( __( 'Missing <a href="%s">API Key</a>', 'themify' ),
			themify_is_themify_theme() ? admin_url( 'admin.php?page=themify#setting-integration-api' ) : admin_url( 'admin.php?page=themify-builder&tab=builder_settings' )
		);
    }
}
?>
<!-- module map -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null; 
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_map'),$notice;
    
	if ( $fields_args['map_provider'] === 'google' && $fields_args['map_display_type'] === 'static'): ?>
	    <?php
	    $attr = 'key='.Themify_Builder_Model::getMapKey();
	    if ($fields_args['address_map'] !== '') {
		$attr .= '&center=' . $fields_args['address_map'];
	    } elseif ($fields_args['latlong_map'] !== '') {
		$attr .= '&center=' . $fields_args['latlong_map'];
	    }
	    $attr .= '&zoom=' . $fields_args['zoom_map'];
	    $attr .= '&maptype=' . strtolower($fields_args['type_map']);
	    $attr .= '&size=' . preg_replace('/[^0-9]/', '', $fields_args['w_map_static']) . 'x' . preg_replace('/[^0-9]/', '', $fields_args['h_map']);
	    echo '<img style="'.esc_attr($style).'" src="https://maps.googleapis.com/maps/api/staticmap?'.$attr.'">';

	elseif ($fields_args['address_map'] !== '' || $fields_args['latlong_map'] !== ''):

	$style .= 'width:' . $fields_args['w_map'] . $fields_args['w_map_unit'] . ';';
	$style .= 'height:' . $fields_args['h_map'] . $fields_args['h_map_unit'] . ';';
	static $mapConnect=false;
	?>
	<?php if($mapConnect===false):?>
	    <?php $mapConnect=true;?>
	    <link rel="preconnect" href="https://maps.googleapis.com" crossorigin/>
	<?php endif;?>
	<div
	    <?php if(Themify_Builder::$frontedit_active===false):?>data-lazy="1" <?php endif;?>
	    data-map-provider="<?php echo $fields_args['map_provider'] ?>"
	    data-address="<?php esc_attr_e( $fields_args['address_map'] !== '' ? $fields_args['address_map'] : $fields_args['latlong_map'] ) ?>"
	    data-zoom="<?php echo $fields_args['zoom_map']; ?>"
	    data-type="<?php echo $fields_args['map_provider'] === 'google'?$fields_args['type_map']:$fields_args['bing_type_map']; ?>"
	    data-scroll="<?php echo $fields_args['scrollwheel_map'] === 'enable'; ?>"
	    data-drag="<?php echo $fields_args['draggable_map'] === 'enable'; ?>"
	    data-mdrag="<?php echo $fields_args['draggable_disable_mobile_map'] === 'yes'; ?>"
	    data-control="<?php echo $fields_args['map_control'] === 'no'; ?>"
	    class="<?php if(Themify_Builder::$frontedit_active===false):?>tf_lazy <?php endif;?>themify_map<?php echo $fields_args['map_provider'] !== 'google'?' themify_bing_map':''?>"
	    style="<?php  echo $style; ?>"
	    data-info-window="<?php  esc_attr_e($info_window_map); ?>"
	    data-reverse-geocoding="<?php echo empty($fields_args['address_map']) && !empty($fields_args['latlong_map']) ?>">
	</div>
    <?php endif; ?>
</div>
<!-- /module map -->
