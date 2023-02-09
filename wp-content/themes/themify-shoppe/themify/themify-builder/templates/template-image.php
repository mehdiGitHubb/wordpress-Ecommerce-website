<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Image
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-image.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'mod_title_image' => '',
    'style_image' => '',
    'url_image' => '',
    'appearance_image' => '',
    'caption_on_overlay' => '',
    'image_size_image' => '',
    'width_image' => '',
    'auto_fullwidth' => false,
    'height_image' => '',
    'title_tag' => 'h3',
    'title_image' => '',
    'link_image' => '',
    'param_image' => '',
    'image_zoom_icon' => '',
    'lightbox_width' => '',
    'lightbox_height' => '',
    'lightbox_width_unit' => 'px',
    'lightbox_height_unit' => 'px',
    'alt_image' => '',
    'caption_image' => '',
    'css_image' => '',
    'animation_effect' => ''
);

if (isset($args['mod_settings']['appearance_image'])) {
    $args['mod_settings']['appearance_image'] = self::get_checkbox_data($args['mod_settings']['appearance_image']);
	Themify_Builder_Model::load_appearance_css($args['mod_settings']['appearance_image']);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

$container_class=array(
    'module', 
    'module-' . $mod_name,
    $element_id,
    $fields_args['appearance_image'], 
    $fields_args['css_image']
); 
if($fields_args['style_image']!==''){
    Themify_Builder_Model::load_module_self_style($mod_name,str_replace('image-','',$fields_args['style_image']));
    $container_class[]= $fields_args['style_image'];
}
if (  'yes' === $fields_args['caption_on_overlay']){
    $container_class[]= 'active-caption-hover';
}
if ($fields_args['auto_fullwidth']=='1') {
    $container_class[]='auto_fullwidth';
}
$container_class[]='tf_mw';
$container_class = apply_filters('themify_builder_module_classes', $container_class, $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$newtab =false;
if($fields_args['link_image'] !== ''){
    if($fields_args['param_image'] === 'lightbox'){
		$lightbox_data = $fields_args['lightbox_width']!=='' ||  $fields_args['lightbox_height']!=='' ? sprintf(' data-zoom-config="%s|%s"'
		    , $fields_args['lightbox_width'] . $fields_args['lightbox_width_unit'], $fields_args['lightbox_height'] . $fields_args['lightbox_height_unit']) : false;
    }
    else{
		$newtab=$fields_args['param_image'] === 'newtab';
    }
}
$image_alt = '' !== $fields_args['alt_image'] ? $fields_args['alt_image'] : wp_strip_all_tags($fields_args['caption_image']);
$image_title = $fields_args['title_image'];
if ($image_alt === '') {
    $image_alt = $image_title;
}

$image = '';
if ( ! empty( $fields_args['url_image'] ) ) {
	$preset = $fields_args['image_size_image'] !== '' ? $fields_args['image_size_image'] : themify_builder_get('setting-global_feature_size', 'image_global_size_field');
	$param_image=array('src'=>esc_url($fields_args['url_image']),'w'=>$fields_args['width_image'],'h'=>$fields_args['height_image'],'alt'=>$image_alt,'title'=>$image_title,'image_size'=>$preset);
	if ( Themify_Builder::$frontedit_active === true && ! Themify_Builder_Component_Base::$disable_inline_edit ) {
		$param_image['attr']=array('data-w'=>'width_image', 'data-h'=>'height_image','data-name'=>'url_image');
	}
	$image = themify_get_image($param_image);

	unset($param_image,$preset,$image_alt);
}

$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' => implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
$args=$container_class=null;
?>
<!-- module image -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php $container_props=null; 
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_image');
    ?>
    <div class="image-wrap tf_rel tf_mw">
	<?php if ($fields_args['link_image'] !== ''): ?>
	    <a href="<?php echo esc_url($fields_args['link_image']); ?>"
	       <?php if ($newtab===true): ?> rel="noopener" target="_blank"<?php elseif (isset($lightbox_data)) : ?> class="lightbox-builder themify_lightbox"<?php echo $lightbox_data; ?><?php endif; ?>
	       >
		   <?php if ($fields_args['style_image']!=='image-full-overlay' && $fields_args['image_zoom_icon'] === 'zoom'): ?>
			<?php 
			    Themify_Builder_Model::load_module_self_style($mod_name,'zoom');
				$icon=isset($lightbox_data) ? 'search' : 'new-window';
			?>
			<span class="zoom">
			    <?php echo themify_get_icon($icon,'ti',false,false,array('aria-label'=>__('Open','themify'))); ?>
			</span>
		    <?php endif; ?>
		<?php echo $image; ?>
	    </a>
	<?php else: ?>
	    <?php echo $image; ?>
	<?php endif; ?>

	<?php if ('image-overlay' !== $fields_args['style_image']): ?>
	</div>
	<!-- /image-wrap -->
    <?php endif; ?>

    <?php if ($image_title !== '' || $fields_args['caption_image'] !== ''): ?>
	<div class="image-content<?php echo $fields_args['style_image']==='image-full-overlay'?' tf_overflow':'';?>">
	    <?php if ($image_title !== ''): ?>
			<<?php echo $fields_args['title_tag'];?> class="image-title"<?php self::add_inline_edit_fields('title_image',$fields_args['link_image'] === '')?>>
				<?php if ($fields_args['link_image'] !== ''): ?>
					<a<?php self::add_inline_edit_fields('title_image')?> href="<?php echo esc_url($fields_args['link_image']); ?>" 
					   <?php if (isset($lightbox_data)) : ?> class="lightbox-builder themify_lightbox"<?php echo $lightbox_data; ?><?php endif; ?>
					   <?php if ($newtab===true): ?> rel="noopener" target="_blank"<?php endif; ?>>
						   <?php echo $image_title; ?>
					</a>
				<?php else: ?>
					<?php echo $image_title; ?>
				<?php endif; ?>
			</<?php echo $fields_args['title_tag'];?>>
	    <?php endif; ?>

	    <?php if ($fields_args['caption_image'] !== ''): ?>
		<div class="image-caption tb_text_wrap"<?php self::add_inline_edit_fields('caption_image')?>>
		    <?php echo apply_filters('themify_builder_module_content', $fields_args['caption_image']); ?>
	    </div>
	    <!-- /image-caption -->
	    <?php endif; ?>
	</div>
	<!-- /image-content -->
    <?php endif; ?>
	<?php if ('image-overlay' === $fields_args['style_image']): ?>
		</div>
		<!-- /image-wrap -->
	<?php endif; ?>
</div>
<!-- /module image -->