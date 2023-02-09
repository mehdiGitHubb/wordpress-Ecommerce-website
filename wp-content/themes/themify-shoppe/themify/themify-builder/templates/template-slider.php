<?php

defined( 'ABSPATH' ) || exit;

/**
 * Slider module template
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-slider.php.
 *
 * Access original fields: $args['settings']
 * @author Themify
 */
$template_name = isset($args['mod_settings']['layout_display_slider']) ? $args['mod_settings']['layout_display_slider'] : 'blog';
$isBlog = $template_name!=='image' && $template_name!=='video' && $template_name!=='text' && $template_name!=='content';
if($isBlog===true){
    $template_name = 'blog';
}
$slider_default = array(
    'layout_display_slider' => 'blog',
    'open_link_new_tab_slider' => 'no',
    'hide_post_date' => 'yes',
    'mod_title_slider' => '',
    'layout_slider' => '',
    'img_h_slider' => '',
    'img_w_slider' => '',
    'img_fullwidth_slider' => '',
    'image_size_slider' => '',
    'visible_opt_slider' => '',
    'tab_visible_opt_slider' => '',
    'mob_visible_opt_slider' => '',
    'auto_scroll_opt_slider' => 'off',
    'scroll_opt_slider' => '',
    'speed_opt_slider' => '',
    'effect_slider' => 'scroll',
    'pause_on_hover_slider' => 'resume',
    'play_pause_control' => 'no',
    'wrap_slider' => 'yes',
    'show_nav_slider' => 'yes',
    'show_arrow_slider' => 'yes',
    'show_arrow_buttons_vertical' => '',
    'unlink_feat_img_slider'=>'no',
    'unlink_post_title_slider'=>'no',
    'left_margin_slider' => '',
    'right_margin_slider' => '',
    'css_slider' => '',
    'animation_effect' => '',
    'touch_swipe' => '',
    'height_slider' => 'variable'
);

$fields_args = wp_parse_args($args['mod_settings'], $slider_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

$arrow_vertical = $fields_args['show_arrow_slider'] === 'yes' && $fields_args['show_arrow_buttons_vertical'] === 'vertical' ? 'themify_builder_slider_vertical' : '';
$fullwidth_image = $fields_args['img_fullwidth_slider'] === 'fullwidth' ? 'slide-image-fullwidth' : '';

$container_class =apply_filters('themify_builder_module_classes', array(
    'module themify_builder_slider_wrap tf_clearfix', 
    'module-' . $mod_name, 
    $element_id,
    $fields_args['css_slider'], 
    $fields_args['layout_slider'],
    $arrow_vertical, 
    $fullwidth_image
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' =>  implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
$args=null;
$margins = '';
if ($fields_args['left_margin_slider'] !== '') {
    $margins.='margin-left:' . $fields_args['left_margin_slider'] . 'px;';
}
if ($fields_args['right_margin_slider'] !== '') {
    $margins.='margin-right:' . $fields_args['right_margin_slider'] . 'px;';
}
$fields_args['margin'] = $margins;
$speed = $fields_args['speed_opt_slider'] === 'slow' ? 4 : ($fields_args['speed_opt_slider'] === 'fast' ? '.5' : 1);
if(Themify_Builder::$frontedit_active===false){
	    $container_props['data-lazy']=1;
}
?>
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=$container_class=null;
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_slider');
    ?>
    <div class="themify_builder_slider tf_carousel tf_swiper-container tf_rel tf_overflow"<?php if(Themify_Builder::$frontedit_active===false):?> data-lazy="1"<?php endif;?>
		data-tbreakpoints="<?php echo themify_get_breakpoints('tablet_landscape')[1]?>"
		data-mbreakpoints="<?php echo themify_get_breakpoints('mobile')?>"
		data-visible="<?php echo isset($fields_args['horizontal']) && $fields_args['horizontal'] === 'yes' ? '4' : $fields_args['visible_opt_slider'] ?>"
		data-tab-visible="<?php echo $fields_args['tab_visible_opt_slider'] ?>"
		data-mob-visible="<?php echo $fields_args['mob_visible_opt_slider'] ?>"
		data-scroll="<?php echo $fields_args['scroll_opt_slider']; ?>"
		<?php if($arrow_vertical!==''):?>
			data-nav_out="1"
		<?php endif;?>
		<?php if($fields_args['auto_scroll_opt_slider']!=='off'):?>
			data-auto="<?php echo $fields_args['auto_scroll_opt_slider']*1000 ?>"
			data-controller="<?php echo $fields_args['play_pause_control']=== 'yes'?'1':'0' ?>"
			data-pause_hover="<?php echo $fields_args['pause_on_hover_slider']=== 'resume'?'1':'0' ?>"
		<?php endif;?>
		data-speed="<?php echo $speed ?>"
		data-wrapvar="<?php echo $fields_args['wrap_slider']!== 'no'?'1':'0' ?>"
		data-slider_nav="<?php echo $fields_args['show_arrow_slider']=== 'yes'?'1':'0' ?>"
		data-pager="<?php echo $fields_args['show_nav_slider']=== 'yes'?'1':'0' ?>"
		data-effect="<?php echo $fields_args['effect_slider'] ?>" 
		data-height="<?php echo isset($fields_args['horizontal']) && $fields_args['horizontal'] === 'yes' ? 'variable' : $fields_args['height_slider'] ?>"
		data-horizontal="<?php echo isset($fields_args['horizontal']) &&  $fields_args['horizontal']=== 'yes'?'1':'0'?>"
		data-css_url="<?php echo THEMIFY_BUILDER_CSS_MODULES ?>sliders/carousel.css,<?php echo THEMIFY_BUILDER_CSS_MODULES ?>sliders/<?php echo $mod_name?>.css"
		<?php if ( $fields_args['touch_swipe'] !== '' ) : ?>data-touch_swipe="<?php echo $fields_args['touch_swipe']; ?>" <?php endif; ?>
	<?php if ($template_name === 'video'): ?>data-type="video"<?php endif; ?>>
		<div class="tf_swiper-wrapper tf_lazy tf_rel tf_w tf_h tf_textc">
	    <?php
	    self::retrieve_template('template-' . $mod_name . '-' . $template_name . '.php', array(
		'module_ID' => $element_id,
		'mod_name' => $mod_name,
		'settings' => $fields_args
		    ), __DIR__);
	    ?>
	    </div>
    </div>
</div>
