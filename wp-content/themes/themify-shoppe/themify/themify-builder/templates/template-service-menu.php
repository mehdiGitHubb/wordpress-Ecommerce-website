<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Service Menu
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-service-menu.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
	'image_size_image' => '',
	'title_tag' => 'h4',
	'title_service_menu' => '',
	'style_service_menu' => 'image-left',
	'description_service_menu' => '',
	'price_service_menu' => '',
	'image_service_menu' => '',
	'appearance_image_service_menu' => '',
	'image_size_service_menu' => '',
	'width_service_menu' => '',
	'height_service_menu' => '',
	'link_service_menu' => '',
	'link_options' => '',
	'image_zoom_icon' => '',
	'lightbox_width' => '',
	'lightbox_height' => '',
	'lightbox_size_unit_width' => 'pixels',
	'lightbox_size_unit_height' => 'pixels',
	'param_service_menu' => array(),
	'highlight_service_menu' => array(),
	'highlight_text_service_menu' => '',
	'highlight_color_service_menu' => 'tb_default_color',
	'css_service_menu' => '',
	'animation_effect' => '',
	'add_price_check' => 'no',
	'price_fields_holder' => array()
);

$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

if ( isset( $args['mod_settings']['appearance_image_service_menu'] ) ) {
	$args['mod_settings']['appearance_image_service_menu'] = self::get_checkbox_data( $args['mod_settings']['appearance_image_service_menu'] );
	Themify_Builder_Model::load_appearance_css($args['mod_settings']['appearance_image_service_menu']);
}
if ( isset( $args['mod_settings']['param_service_menu'] ) ) {
	$args['mod_settings']['param_service_menu'] = explode( '|', $args['mod_settings']['param_service_menu'] );
}
$highlight = false;
if ( isset( $args['mod_settings']['highlight_service_menu'] ) ) {
	$args['mod_settings']['highlight_service_menu'] = explode( '|', $args['mod_settings']['highlight_service_menu'] );
	if ( in_array( 'highlight', $args['mod_settings']['highlight_service_menu'], true ) ) {
		$highlight = true;
	}
}
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
unset( $args['mod_settings'] );
$fields_default=null;
if($fields_args['style_service_menu']!=='image-left'){
    Themify_Builder_Model::load_module_self_style($mod_name,str_replace('image-','',$fields_args['style_service_menu']));
}
$container_class = array( 'module',
	'module-' . $mod_name,
	$element_id,
	$fields_args['appearance_image_service_menu'],
	$fields_args['style_service_menu'],
	$fields_args['css_service_menu']);
if ( $highlight === true ) {
	$container_class[] = 'has-highlight';
	$container_class[] = $fields_args['highlight_color_service_menu'];
	Themify_Builder_Model::load_color_css($fields_args['highlight_color_service_menu']);
} else {
	$container_class[] = 'no-highlight';
}
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
}
$container_class[] = 'tf_mw';
$container_class = implode( ' ', apply_filters( 'themify_builder_module_classes', $container_class, $mod_name, $element_id, $fields_args ) );

$lightbox =false;
$link_attr = '';
$newtab = $fields_args['link_options'] === 'newtab';
if ($newtab===false && $fields_args['link_options'] === 'lightbox' ) {
	$lightbox = true;
	$units = array(
		'pixels' => 'px',
		'percents' => '%'
	);
	if ( $fields_args['lightbox_width'] !== '' || $fields_args['lightbox_height'] !== '' ) {
		$lightbox_settings = array();
		$lightbox_settings[] = $fields_args['lightbox_width'] !== '' ? $fields_args['lightbox_width'] . $units[ $fields_args['lightbox_size_unit_width'] ] : '';
		$lightbox_settings[] = $fields_args['lightbox_height'] !== '' ? $fields_args['lightbox_height'] . $units[ $fields_args['lightbox_size_unit_height'] ] : '';
		$link_attr = sprintf( 'data-zoom-config="%s"', implode( '|', $lightbox_settings ) );
		$lightbox_settings=$units=null;
	}
}
if (!empty($fields_args['image_service_menu'])) {
	$image = themify_get_image(
			array(
				'src' => esc_url($fields_args['image_service_menu']),
				'w' => $fields_args['width_service_menu'],
				'h' => $fields_args['height_service_menu'],
				'alt' => '' !== $fields_args['title_service_menu'] ? esc_attr($fields_args['title_service_menu']) : wp_strip_all_tags($fields_args['description_service_menu']),
				'image_size' => $fields_args['image_size_image'] !== '' ? $fields_args['image_size_image'] : themify_builder_get('setting-global_feature_size', 'image_global_size_field'),
				'attr' => Themify_Builder::$frontedit_active === false ? array() : array('data-w' => 'width_service_menu', 'data-h' => 'height_service_menu'),
				'class' => 'tb_menu_image'
			)
	);
}


$container_props = apply_filters( 'themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
			'class' => $container_class
		)), $fields_args, $mod_name, $element_id);
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}
$args = null;
?>
<!-- module service menu -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props, $fields_args)); ?>>
	<?php
	$container_props = $container_class = null;
	?>
	<?php if ($highlight === true && $fields_args['highlight_text_service_menu'] !== '') : ?>
		<?php Themify_Builder_Model::load_module_self_style($mod_name, 'highlight'); ?>
		<div class="tb-highlight-text"<?php self::add_inline_edit_fields('highlight_text_service_menu')?>>
			<?php echo $fields_args['highlight_text_service_menu']; ?>
		</div>
	<?php endif; ?>
	<?php if (!empty($image)) : ?>
		<div class="tb-image-wrap tf_left">
			<?php if ($fields_args['link_service_menu'] !== '') : ?>
				<a href="<?php echo esc_url($fields_args['link_service_menu']); ?>"<?php
				if ($newtab === true) : echo ' rel="noopener" target="_blank"';
				elseif ($lightbox === true) : echo ' class="lightbox-builder themify_lightbox"';
				endif;
				?> <?php echo $link_attr; ?>>
					   <?php if ($fields_args['image_zoom_icon'] === 'zoom' && $fields_args['link_options'] !== 'regular') : ?>
						<span class="zoom"><?php echo themify_get_icon(($newtab === true ? 'fa-external-link' : 'fa-search'), 'fa', false, false, array('aria-label' => __('Open', 'themify'))); ?></span>
					<?php endif; ?>
					<?php echo $image; ?>
				</a>
			<?php else : ?>
				<?php echo $image; ?>
			<?php endif; ?>
		</div><!-- .tb-image-wrap -->
	<?php endif; ?>

    <div class="tb-image-content tf_overflow">

		<div class="tb-menu-title-wrap">
			<?php if ($fields_args['title_service_menu'] !== '') : ?>
				<<?php echo $fields_args['title_tag'];?><?php self::add_inline_edit_fields('title_service_menu')?> class='tb-menu-title'><?php echo $fields_args['title_service_menu']; ?></<?php echo $fields_args['title_tag'];?>>
			<?php endif; ?>

			<?php if ($fields_args['description_service_menu'] !== '') : ?>
				<div class="tb-menu-description"<?php self::add_inline_edit_fields('description_service_menu')?>>
					<?php echo $fields_args['description_service_menu']; ?>
				</div>
			<?php endif; ?>
		</div>
		<!-- /tb-menu-title-wrap -->

		<?php if ($fields_args['price_service_menu'] !== '' || isset($fields_args['add_price_check'])): ?>
			<?php Themify_Builder_Model::load_module_self_style($mod_name, 'price'); ?>
			<div class="tb-menu-price"<?php self::add_inline_edit_fields('price_service_menu',$fields_args['price_service_menu'] !== '' && $fields_args['add_price_check'] !== 'yes')?>>
				<?php
				if ($fields_args['price_service_menu'] !== '' && $fields_args['add_price_check'] !== 'yes') {
					echo $fields_args['price_service_menu'];
					if (isset($fields_args['_render_plain_content']) && true === $fields_args['_render_plain_content']):
						?>
						<br/>
					<?php
					endif;
				} elseif (isset($fields_args['add_price_check']) && $fields_args['add_price_check'] === 'yes') {
					?>
					<?php
					$price_content = array_filter($fields_args['price_fields_holder']);

					foreach ($price_content as $k=>$content):
						$content = wp_parse_args($content, array(
							'label' => '',
							'price' => '',
						));
						?>
						<div class="tb-price-item">
							<?php if ($content['label'] !== ''): ?>
								<div class="tb-price-title"<?php self::add_inline_edit_fields('label',true,false,'price_fields_holder', $k)?>><?php echo $content['label']; ?></div>
							<?php endif; ?>

							<?php if ($content['price'] !== ''): ?>
								<div class="tb-price-value"<?php self::add_inline_edit_fields('price',true,false,'price_fields_holder', $k)?>><?php echo $content['price']; ?></div>
						<?php endif; ?>
						</div>
					<?php endforeach; ?>
			<?php } ?>
			</div>
<?php endif; ?>

    </div>
    <!-- /tb-image-content -->
</div>
<!-- /module service menu -->
