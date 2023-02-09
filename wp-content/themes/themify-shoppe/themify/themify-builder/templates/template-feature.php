<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Image
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-feature.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'mod_title_feature' => '',
    'title_tag' => 'h3',
    'title_feature' => '',
    'overlap_image_feature' => '',
    'overlap_image_width' => '',
    'overlap_image_height' => '',
    'layout_feature' => 'icon-top',
    'layout_mobile' => '',
    'content_feature' => '',
    'circle_percentage_feature' => '',
    'circle_color_feature' => '',
    'circle_stroke_feature' => 0,
    'icon_type_feature' => 'icon',
    'image_feature' => '',
    'icon_feature' => '',
    'icon_color_feature' => '',
    'icon_bg_feature' => '',
    'circle_size_feature' => 'medium',
    'custom_circle_size_feature' => 120,
    'link_feature' => '',
    'feature_download_link' => '',
    'link_options' => false,
    'lightbox_width' => '',
    'lightbox_height' => '',
    'lightbox_width_unit' => 'px',
    'lightbox_height_unit' => 'px',
	'icon_position' => '',
    'css_feature' => '',
    'animation_effect' => ''
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default = null;
$mod_name = $args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];
if($fields_args['layout_feature']!=='icon-top'){
    Themify_Builder_Model::load_module_self_style($mod_name, str_replace('icon-', '', $fields_args['layout_feature']));
}

$fields_args['circle_percentage_feature'] = str_replace('%', '', $fields_args['circle_percentage_feature']); // remove % if added by user
$w=(int)$fields_args['circle_stroke_feature'];
$isEmpty = empty($fields_args['circle_percentage_feature']) || $w<=0;
if ($isEmpty === true) {
    $chart_class = 'no-chart';
} 
else {
    if($w===1){
	$w=2;
    }
    $chart_class = 'with-chart';
    if ('' !== $fields_args['overlap_image_feature']) {
	Themify_Builder_Model::load_module_self_style($mod_name, 'overlay');
	$chart_class .= ' with-overlay-image';
    }
}
$link_attr = '';
if (!empty($fields_args['link_options']) && '' !== $fields_args['link_feature']) {
    if ($fields_args['link_options'] === 'lightbox') {
	$link_attr = ' class="themify_lightbox"';
	if ($fields_args['lightbox_width'] !== '' || $fields_args['lightbox_height'] !== '') {
	    $lightbox_settings = array();
	    $lightbox_settings[] = $fields_args['lightbox_width'] !== '' ? $fields_args['lightbox_width'] . $fields_args['lightbox_width_unit'] : '';
	    $lightbox_settings[] = $fields_args['lightbox_height'] !== '' ? $fields_args['lightbox_height'] . $fields_args['lightbox_height_unit'] : '';
	    $link_attr .= sprintf(' data-zoom-config="%s"', implode('|', $lightbox_settings));
	    $lightbox_settings=null;
	}
} elseif ($fields_args['link_options'] === 'newtab') {
	$link_attr = ' target="_blank" rel="noopener"';
}
    if ($fields_args['feature_download_link'] === 'yes') {
	$link_attr .= ' download';
    }
}

$container_class = apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id,
    $chart_class,
    'layout-' . $fields_args['layout_feature'],
    'size-' . $fields_args['circle_size_feature'],
    $fields_args['css_feature'],
	), $mod_name, $element_id, $fields_args);

if (!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active === false) {
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args, array(
	    'class' => implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);

if ( $fields_args['layout_mobile'] !== '' ) {
	$container_props['data-layout-mobile'] = $fields_args['layout_mobile'];
	$container_props['data-layout-desktop'] = $fields_args['layout_feature'];
	if ( ! in_array( $fields_args['layout_mobile'], [ $fields_args['layout_feature'], 'icon-top' ],true ) ) {
		Themify_Builder_Model::load_module_self_style( $mod_name, str_replace( 'icon-', '', $fields_args['layout_mobile'] ) );
	}
}

$args = null;
if (Themify_Builder::$frontedit_active === false) {
    $container_props['data-lazy'] = 1;
}

$insetColor =   '';
$circleColor = !empty($fields_args['circle_color_feature']) ? esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['circle_color_feature'])) : '';

if('icon' === $fields_args['icon_type_feature']){
	if($fields_args['icon_bg_feature'] !== ''){
		 $insetColor =  esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['icon_bg_feature']));
	}
}
elseif($fields_args['image_feature']!==''){
	$alt=Themify_Builder_Model::get_alt_by_url($fields_args['image_feature']);
	if(!$alt){
		$alt=$fields_args['title_feature'];
	}
}
$st='';
if($fields_args['circle_size_feature']==='custom' && !empty($fields_args['custom_circle_size_feature'])){
	$st='width:'.$fields_args['custom_circle_size_feature'].'px;height:'.$fields_args['custom_circle_size_feature'].'px;';
}
if ($isEmpty === true && $insetColor !== ''){
	$st.='background-color:'.$insetColor;
}
?>
<!-- module feature -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
    <?php
        $container_props = $container_class = null;
	echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_feature');
	?>
    <div class="module-feature-image tf_textc tf_rel">
		<?php
		if ('' !== $fields_args['overlap_image_feature']) {
			$param_image=array('src'=>$fields_args['overlap_image_feature'],'w'=>$fields_args['overlap_image_width'],'h='=>$fields_args['overlap_image_height']);
			if(Themify_Builder::$frontedit_active===true){
				$param_image['attr']=array('data-w'=>'overlap_image_width', 'data-h'=>'overlap_image_height','data-name'=>'overlap_image_feature');
			}
			echo themify_get_image($param_image);
			unset($param_image);
		}
		?>
		<?php if ('' !== $fields_args['link_feature']) : ?>
			<a href="<?php echo esc_url($fields_args['link_feature']); ?>"<?php echo $link_attr; ?>>
		<?php endif; ?>
			<span class="module-feature-chart-html5 tf_box tf_rel tf_inline_b"<?php if($st!==''):?> style="<?php echo $st?>"<?php endif;?>>
			<?php if ($isEmpty === false): ?>
				<svg class="tf_abs tf_w tf_h">
				<circle class="tb_feature_fill" r="calc(50% - <?php echo number_format( $w / 2, 2 ); ?>px)" cx="50%" cy="50%" stroke-width="<?php echo $w?>"/>
				<circle class="tb_feature_stroke" r="calc(50% - <?php echo number_format( $w / 2, 2 ); ?>px)" cx="50%" cy="50%" stroke="<?php echo $circleColor ?>" stroke-width="<?php echo $w?>" data-progress="<?php echo (int) $fields_args['circle_percentage_feature'] ?>" stroke-dasharray="0,10000"/>
				<?php if($insetColor!=='' && 'icon' === $fields_args['icon_type_feature']):?>
					<circle class="tb_feature_bg" r="calc(50% - <?php echo ($w>1?($w-1):0) ?>px)" cx="50%" cy="50%" stroke-width="<?php echo $w ?>" fill="<?php echo $insetColor ?>" />
				<?php endif;?>
				</svg>
			<?php endif; ?>
				<span class="chart-html5-circle tf_w tf_h">
					<?php if ('icon' === $fields_args['icon_type_feature']) : ?>
						<?php if('' !== $fields_args['icon_feature']):?>
							<em class="module-feature-icon tf_rel"<?php echo $fields_args['icon_color_feature']!==''? ' style="color:' . esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['icon_color_feature'])) . '"' : ''; ?>><?php echo themify_get_icon($fields_args['icon_feature']); ?></em>
						<?php endif;?>
					<?php elseif($fields_args['image_feature'] !== ''):?>
						<img style="width:calc(100% - <?php echo $w*2 ?>px);height:calc(100% - <?php echo $w*2 ?>px)" src="<?php echo esc_url($fields_args['image_feature']) ?>" alt="<?php esc_attr_e($alt)?>">
					<?php endif; ?>
				</span>

				<?php if ( 'both' === $fields_args['icon_type_feature'] ) : ?>
					<span class="module-feature-icon-wrap tf_abs"<?php if ( $fields_args['icon_position'] !=='' ) : ?> style="transform:rotate(<?php echo $fields_args['icon_position'] ?>deg)"<?php endif; ?>>
						<span class="module-feature-icon" style="
							<?php if ( $fields_args['icon_position']!=='' ) : ?>transform:translateY(-50%) rotate(-<?php echo $fields_args['icon_position'] ?>deg); <?php endif; ?>
							<?php if ( $fields_args['icon_color_feature']!=='' ) : ?>color:<?php echo Themify_Builder_Stylesheet::get_rgba_color( $fields_args['icon_color_feature'] ); ?>;<?php endif; ?>
							<?php if ( $fields_args['icon_bg_feature']!=='' ) : ?>background-color:<?php echo Themify_Builder_Stylesheet::get_rgba_color( $fields_args['icon_bg_feature'] ); ?><?php endif; ?>
						"><?php echo themify_get_icon( $fields_args['icon_feature'] ); ?></span>
					</span>
				<?php endif; ?>

			</span>
			<?php if ('' !== $fields_args['link_feature']) : ?>
			</a>
		<?php endif; ?>
    </div>
    <div class="module-feature-content tf_textc">
		<?php
		if ('' !== $fields_args['title_feature']) {
			?>
			<<?php echo $fields_args['title_tag'];?> class="module-feature-title"<?php self::add_inline_edit_fields('title_feature','' === $fields_args['link_feature'])?>>
			<?php if ('' !== $fields_args['link_feature']): ?>
				<a href="<?php echo esc_url($fields_args['link_feature']) ?>"<?php echo $link_attr ?><?php self::add_inline_edit_fields('title_feature')?>><?php echo $fields_args['title_feature'] ?></a>
			<?php else: ?>
				<?php echo $fields_args['title_feature'] ?>
			<?php endif; ?>
			</<?php echo $fields_args['title_tag'];?>>
			<?php
		}
		?>
		<div class="tb_text_wrap"<?php self::add_inline_edit_fields('content_feature',true,true)?>>
			<?php echo $fields_args['content_feature'] !== '' ? apply_filters('themify_builder_module_content', $fields_args['content_feature']) : ''; ?>
		</div>
    </div>
</div>
<!-- /module feature -->
