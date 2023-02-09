<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Plain Text
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-plain-text.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'plain_text' => '',
    'add_css_text' => '',
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
    $fields_args['add_css_text']
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
<!-- module plain text -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
    ?>
    <div class="tb_text_wrap"<?php self::add_inline_edit_fields('plain_text',true,true)?>>
	<?php
	if ( $fields_args['plain_text'] !== '' ) {
		if ( empty( $fields_args['formatting'] ) ) {
			$fields_args['plain_text'] = apply_filters( 'themify_builder_module_content', $fields_args['plain_text'] );
		}
		echo $fields_args['plain_text'];
	}
	?>
    </div>
</div>
<!-- /module plain text -->