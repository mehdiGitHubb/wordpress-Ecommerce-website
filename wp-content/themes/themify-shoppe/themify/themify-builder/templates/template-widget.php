<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Widget
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-widget.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
global $wp_widget_factory;
$fields_default = array(
	'mod_title_widget' => '',
	'class_widget' => '',
	'instance_widget' => array(),
	'custom_css_widget' => '',
	'background_repeat' => '',
	'animation_effect' => ''
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

$container_class = apply_filters( 'themify_builder_module_classes', array(
    'module', 
    'module-' . $mod_name, 
    $element_id,
    $fields_args['custom_css_widget'], 
    $fields_args['background_repeat']
), $mod_name, $element_id, $fields_args );

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
		'class' => implode( ' ', $container_class),
)), $fields_args, $mod_name, $element_id);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
?>
<!-- module widget -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php
	$container_props=$container_class=null;
	$tmp = is_numeric($builder_id) && get_post_status ( $builder_id )?get_post( $builder_id, OBJECT ):'';// $builder_id can be generated string from AAP module
	if(!empty($tmp) || $tmp===''){
	    if($tmp!==''){
			global $post;
			$post=$tmp;
			setup_postdata( $post );
	    }
		echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_widget');
	   
	    do_action( 'themify_builder_before_template_content_render' );
		$widget_name = TB_Widget_Module::get_widget_factory_name( $fields_args['class_widget'] );
	
	    if ( $widget_name ) {
		// Backward compatibility with how widget data used to be saved.
		$fields_args['instance_widget'] = TB_Widget_Module::sanitize_widget_instance( $fields_args['instance_widget'] );
                if('WP_Widget_Media_Gallery'===$fields_args['class_widget'] && 'file'===$fields_args['instance_widget']['link_type']){
                    add_filter('wp_get_attachment_link',array('TB_Widget_Module','widget_gallery_lightbox'),10,1);
                }

		//WC gets by widget_id not by widget-id,which is bug of WC
		the_widget( $widget_name, $fields_args['instance_widget'],array('widget_id'=>isset($fields_args['instance_widget']['widget-id'])?$fields_args['instance_widget']['widget-id']: $element_id) );
                if('WP_Widget_Media_Gallery'===$fields_args['class_widget'] && 'file'===$fields_args['instance_widget']['link_type']){
                        remove_filter('wp_get_attachment_link',array('TB_Widget_Module','widget_gallery_lightbox'),10,1);
                }
	    }
	    do_action('themify_builder_after_template_content_render');
	    if($tmp!==''){
			wp_reset_postdata();
	    }
	}
	?>
</div>
<!-- /module widget -->
