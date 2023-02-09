<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Code
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-code.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'm_t' => '',
    'code' => '',
    'lng'=>'',
    'theme'=>'',
    'numbers'=>'yes',
    'copy'=>'yes',
    'highlight'=>'',
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
if($fields_args['theme']!==''){
    $container_class[]='tb_prism_'.$fields_args['theme'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
'class' => implode(' ',$container_class),
    )), $fields_args, $mod_name, $element_id);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
if($fields_args['theme']!==''){
    $container_props['data-theme']=$fields_args['theme'];
}
?>
<!-- module code -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
    echo Themify_Builder_Component_Module::get_module_title($fields_args,'m_t');
    ?>
    <pre class="tf_rel tf_scrollbar tf_textl"<?php if($fields_args['highlight']!==''):?> data-line="<?php esc_attr_e($fields_args['highlight'])?>"<?php endif;?>>
	<code class="language-<?php echo $fields_args['lng']?><?php if($fields_args['numbers']==='yes'):?> line-numbers<?php endif;?>"><?php echo htmlspecialchars($fields_args['code'])?></code>	
	<?php if($fields_args['copy']==='yes'):?>
		<em class="tb_code_copy tf_opacity">
		    <?php echo themify_get_icon('clipboard','ti',true);?>
		</em>
	<?php endif;?>
    </pre>
</div>
<!-- /module code -->