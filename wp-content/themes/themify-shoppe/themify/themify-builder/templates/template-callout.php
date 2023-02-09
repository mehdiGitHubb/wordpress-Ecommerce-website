<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Callout
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-callout.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
    'mod_title_callout' => '',
    'appearance_callout' => '',
    'layout_callout' => '',
    'color_callout' => 'tb_default_color',
    'heading_callout' => '',
    'title_tag' => 'h3',
    'text_callout' => '',
    'action_btn_link_callout' => '#',
    'open_link_new_tab_callout' => '',
    'action_btn_text_callout' => '',
    'action_btn_color_callout' => 'tb_default_color',
    'action_btn_appearance_callout' => '',
    'css_callout' => '',
    'animation_effect' => ''
);

if (isset($args['mod_settings']['appearance_callout'])) {
    $args['mod_settings']['appearance_callout'] = self::get_checkbox_data($args['mod_settings']['appearance_callout']);
    Themify_Builder_Model::load_appearance_css($args['mod_settings']['appearance_callout']);
}
if (isset($args['mod_settings']['action_btn_appearance_callout'])) {
    $args['mod_settings']['action_btn_appearance_callout'] = self::get_checkbox_data($args['mod_settings']['action_btn_appearance_callout']);
    Themify_Builder_Model::load_appearance_css($args['mod_settings']['action_btn_appearance_callout']);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
Themify_Builder_Model::load_color_css($fields_args['color_callout']);
Themify_Builder_Model::load_color_css($fields_args['action_btn_color_callout']);
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class =apply_filters('themify_builder_module_classes', array(
    'module ui',
    'module-' . $mod_name,
    $element_id,
    $fields_args['layout_callout'],
    $fields_args['color_callout'],
    $fields_args['css_callout'], 
    $fields_args['appearance_callout']
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' =>  implode(' ', $container_class),
)), $fields_args, $mod_name, $element_id);
$ui_class = array($fields_args['action_btn_appearance_callout'],$fields_args['action_btn_color_callout']);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
?>
<!-- module callout -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_callout');
    ?>

    <div class="callout-inner">
	<div class="callout-content tf_left">
	    <<?php echo $fields_args['title_tag'];?> class="callout-heading"<?php self::add_inline_edit_fields('heading_callout')?>><?php echo $fields_args['heading_callout'] ?></<?php echo $fields_args['title_tag'];?>>
	    <div class="tb_text_wrap"<?php self::add_inline_edit_fields('text_callout')?>>
			<?php echo apply_filters('themify_builder_module_content', $fields_args['text_callout']);?>
	    </div>
	</div>
	<!-- /callout-content -->
	<?php if ($fields_args['action_btn_text_callout']!=='') : ?>
	    <div class="callout-button tf_right tf_textr">
		    <a href="<?php echo esc_url($fields_args['action_btn_link_callout']); ?>" class="ui builder_button <?php echo implode(' ', $ui_class); ?>"<?php echo 'yes' === $fields_args['open_link_new_tab_callout'] ? ' rel="noopener" target="_blank"' : ''; ?>>
				<span class="tb_callout_text"<?php self::add_inline_edit_fields('action_btn_text_callout')?>><?php echo $fields_args['action_btn_text_callout'] ?></span>
		    </a>
		</div>
	    <?php endif; ?>
    </div>
    <!-- /callout-content -->
</div>
<!-- /module callout -->
