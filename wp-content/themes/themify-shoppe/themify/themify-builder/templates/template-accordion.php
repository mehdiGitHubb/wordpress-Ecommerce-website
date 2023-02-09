<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Accordion
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-accordion.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
    'mod_title_accordion' => '',
    'layout_accordion' => 'plus-icon-button',
    'expand_collapse_accordion' => 'toggle',
    'color_accordion' => 'tb_default_color',
    'accordion_appearance_accordion' => '',
    'content_accordion' => array(),
    'animation_effect' => '',
    'icon_accordion' => '',
    'hashtag' => '',
    'icon_active_accordion' => '',
    'css_accordion' => '',
    'schema' => '',
);

if (isset($args['mod_settings']['accordion_appearance_accordion'])) {
    $args['mod_settings']['accordion_appearance_accordion'] = self::get_checkbox_data($args['mod_settings']['accordion_appearance_accordion']);
	Themify_Builder_Model::load_appearance_css($args['mod_settings']['accordion_appearance_accordion']);
}

$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
$container_class = apply_filters('themify_builder_module_classes', array(
	    'module', 
	    'module-' . $mod_name, 
	    $element_id, 
	    $fields_args['css_accordion']
), $mod_name, $element_id, $fields_args);
if($fields_args['layout_accordion']==='separate'){
    Themify_Builder_Model::load_module_self_style($mod_name,$fields_args['layout_accordion']);
}
if($fields_args['color_accordion']==='transparent'){
    Themify_Builder_Model::load_module_self_style($mod_name,$fields_args['color_accordion']);
}
else{
    Themify_Builder_Model::load_color_css($fields_args['color_accordion']);
}
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' => implode(' ', $container_class),
    'data-behavior' => $fields_args['expand_collapse_accordion']
)), $fields_args, $mod_name, $element_id);

$ui_class = implode(' ', array('ui', 'module-' . $mod_name, $fields_args['layout_accordion'], $fields_args['accordion_appearance_accordion'],$fields_args['color_accordion']));

$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
    if('yes'===$fields_args['hashtag']){
        $container_props['data-hashtag']=1;
    }
}
$tab_id = str_replace('tb_','',$element_id);

$schema = $fields_args['schema'] === 'yes';

?>
<!-- module accordion -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php 
    $container_props=$container_class=null; 
    echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_accordion');
    ?>

    <ul class="<?php echo $ui_class ?>"<?php if ( $schema===true ) : ?> itemscope itemtype="https://schema.org/FAQPage"<?php endif; ?>>
	<?php
	unset($ui_class);
	$content_accordion = array_filter($fields_args['content_accordion']);
	foreach ($content_accordion as $i=>$content):
	    $content = wp_parse_args($content, array(
			'title_accordion' => '',
			'default_accordion' =>false,
			'text_accordion' => '',
	    ));
	    $isOpen = $content['default_accordion'] === 'open';
	    ?>
	    <li<?php if ($isOpen===true):?> class="builder-accordion-active"<?php endif;?><?php if ( $schema===true ) : ?> itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"<?php endif; ?>>
			<div class="accordion-title tf_rel"<?php if ( $schema===true ) : ?> itemprop="name"<?php endif; ?>>
				<a href="#acc-<?php echo $tab_id . '-' . $i; ?>" aria-controls="acc-<?php echo $tab_id . '-' . $i; ?>-content" aria-expanded="<?php echo $isOpen===true? 'true' : 'false' ; ?>">
					<?php if ($fields_args['icon_accordion'] !== '') : ?><i class="accordion-icon<?php if ($isOpen===true):?> tf_hide<?php endif;?>"><?php echo themify_get_icon($fields_args['icon_accordion']); ?></i><?php endif; ?>
					<?php if ($fields_args['icon_active_accordion'] !== '') : ?><i class="accordion-active-icon<?php if ($isOpen===false):?> tf_hide<?php endif;?>"><?php echo themify_get_icon($fields_args['icon_active_accordion']); ?></i><?php endif; ?>
					<span class="tb_title_accordion tf_w"<?php self::add_inline_edit_fields('title_accordion',true,false,'content_accordion')?>><?php echo $content['title_accordion']; ?></span>
				</a>
			</div><!-- .accordion-title -->

			<div id="acc-<?php echo $tab_id . '-' . $i; ?>-content" data-id="acc-<?php echo $tab_id . '-' . $i; ?>" aria-hidden="<?php echo $isOpen===true? 'false' : 'true' ; ?>" class="accordion-content<?php if ($isOpen===false): ?> tf_hide<?php endif; ?> tf_clearfix"<?php if ( $schema ) : ?> itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"<?php endif; ?>>

				<?php if ( $content['text_accordion'] !== '' ) : ?>
					<div<?php if ( $schema===true ) : ?> itemprop="text"<?php endif; ?>
						class="tb_text_wrap"
						<?php self::add_inline_edit_fields('text_accordion',true,true,'content_accordion')?>>
						<?php echo apply_filters('themify_builder_module_content', $content['text_accordion']);?>
					</div>
				<?php endif;?>

			</div><!-- .accordion-content -->
	    </li>
	<?php endforeach; ?>
    </ul>

</div><!-- /module accordion -->