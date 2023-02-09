<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template lottie
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-lottie.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'm_t' => '',
    'loop'=>false,
    'actions' => array(
    ),
    'css' => '',
    'animation_effect' => '',
);

$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class =  apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $fields_args['css']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
'class' => implode(' ',$container_class),
    )), $fields_args, $mod_name, $element_id);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
?>
<!-- module lottie -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'m_t');
	$json=array('actions'=>$fields_args['actions']);
	if(!empty($fields_args['loop'])){
	    $json['loop']=1;
	}
	unset($fields_args);
    ?>
    <tf-lottie data-lazy="1" class="tf_w">
	<template><?php echo json_encode($json);unset($json);?></template>
    </tf-lottie>
</div>
<!-- /module lottie -->