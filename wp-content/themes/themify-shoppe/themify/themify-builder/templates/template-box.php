<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Box
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-box.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
    'mod_title_box' => '',
    'content_box' => '',
    'appearance_box' => '',
    'color_box' => 'tb_default_color',
    'icon' => '',
    'icon_color' => '',
    'icon_size' => 's',
    'add_css_box' => '',
    'animation_effect' => ''
);

if (isset($args['mod_settings']['appearance_box'])) {
    $args['mod_settings']['appearance_box'] = self::get_checkbox_data($args['mod_settings']['appearance_box']);
	    Themify_Builder_Model::load_appearance_css($args['mod_settings']['appearance_box']);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class =apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name, 
    $element_id, 
    $fields_args['add_css_box']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
if($fields_args['color_box']==='default'){
$fields_args['color_box']='tb_default_color';
}
if ( $fields_args['icon']!=='' ) {
	Themify_Builder_Model::load_module_self_style( $mod_name, 'box-icon' );
	$icon_color = $fields_args['icon_color']!==''?' style="color: ' . Themify_Builder_Stylesheet::get_rgba_color( $fields_args['icon_color'] ) . '"':'';
}

$inner_container_classes = implode(' ', apply_filters('themify_builder_module_inner_classes', array(
    'module-' . $mod_name . '-content ui',  $fields_args['appearance_box'], $fields_args['color_box']
	))
); 
Themify_Builder_Model::load_color_css($fields_args['color_box']);
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' =>  implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
$args=null;
?>
<!-- module box -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null; 
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_box');
    ?>
    <div class="<?php echo $inner_container_classes; ?>">
		<?php if ( $fields_args['icon']!=='' ) : ?>
			<span class="tb_box_icon tb_size_<?php echo $fields_args['icon_size']; ?>"<?php echo $icon_color; ?>>
				<em><?php echo themify_get_icon( $fields_args['icon'] ); ?></em>
			</span>
		<?php endif; ?>
		<div class="tb_text_wrap"<?php self::add_inline_edit_fields('content_box',true,true)?>><?php echo apply_filters('themify_builder_module_content', $fields_args['content_box']); ?></div>
    </div>
</div>
<!-- /module box -->
