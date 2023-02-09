<?php

defined( 'ABSPATH' ) || exit;


/**
 * Template Gallery Slider
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-gallery-slider.php.
 *
 * Access original fields: $args['settings']
 * @author Themify
 */
$settings=$args['settings'];
if( $settings['layout_gallery'] === 'slider' ) :
	$margins = '';
	$element_id = $args['module_ID'];
	if( $settings['left_margin_slider'] !== '' ) {
		$margins .= 'margin-left:' . $settings['left_margin_slider'] . 'px;';
	}
	$element_id=$element_id.'_thumbs';

	if ($settings['right_margin_slider'] !== '') {
		$margins .= 'margin-right:' . $settings['right_margin_slider'] . 'px';
	}
	$image_attr=array(
		'lazy_load'=>false,
		'image_size'=>'full'
	);
	foreach( array( 'slider', 'thumbs' ) as $mode ) :
		$is_slider = $mode === 'slider';
		if($is_slider===false && 'yes' === $settings['slider_thumbs']){
			continue;
		}
		$image_attr['is_slider']=$is_slider;
		$image_attr['w']=$is_slider===false ? $settings['thumb_w_gallery'] : $settings['s_image_w_gallery'];
		$image_attr['h']=$is_slider===false ? $settings['thumb_h_gallery'] : $settings['s_image_h_gallery'];
		if(Themify_Builder::$frontedit_active===true && false){
			$image_attr['attr']=$is_slider===false?array('data-w'=>'thumb_w_gallery', 'data-h'=>'thumb_h_gallery','data-repeat'=>''):array('data-w'=>'s_image_w_gallery', 'data-h'=>'s_image_h_gallery');
		}
		$hasNav=$mode === ( ($is_slider===true && 'yes' === $settings['slider_thumbs']) || $settings['show_arrow_buttons_vertical'] ? 'slider' : 'thumbs' ) ? ($settings['show_arrow_slider']==='yes'?'1':'0') : '0';
?>
    <?php if($hasNav==='1'): ?>
        <div class="themify_builder_slider_vertical tf_rel">
    <?php endif; ?>
<div class="tf_swiper-container tf_carousel themify_builder_slider<?php if($hasNav==='1'):?> themify_builder_slider_vertical<?php endif;?><?php if($is_slider===false):?> <?php echo $element_id?><?php endif;?> tf_rel tf_overflow"
	data-pager="<?php echo (($is_slider==false || 'yes' === $settings['slider_thumbs'])) && $settings['show_nav_slider']=== 'yes'?'1':'0' ?>"
	<?php if(Themify_Builder::$frontedit_active===false):?> data-lazy="1"<?php endif;?>
	<?php if($is_slider===true):?>
		data-thumbs="<?php echo $element_id?>"
		data-effect="<?php echo $settings['effect_slider'] ?>" 
		data-css_url="<?php echo THEMIFY_BUILDER_CSS_MODULES ?>sliders/carousel.css,<?php echo THEMIFY_BUILDER_CSS_MODULES ?>sliders/<?php echo $args['mod_name']?>.css"
	<?php else:?>
		<?php $set = themify_get_breakpoints('tablet_landscape');?>
        data-thumbs-id="<?php echo $element_id?>"
		data-visible="<?php echo $settings['visible_opt_slider']?>" 
		data-tbreakpoints="<?php echo $set[1]?>"
		data-mbreakpoints="<?php echo themify_get_breakpoints('mobile')?>"
		data-tab-visible="<?php echo $settings['tab_visible_opt_slider']?>"
		data-mob-visible="<?php echo $settings['mob_visible_opt_slider']?>"
	<?php endif?>
	<?php if($settings['auto_scroll_opt_slider']!=='off'):?>
		data-auto="<?php echo $settings['auto_scroll_opt_slider']?>"
		data-pause_hover="<?php echo $settings['pause_on_hover_slider']==='resume'?'1':'0' ?>"
		<?php if($is_slider===false || 'yes' === $settings['slider_thumbs']):?>
			data-controller="<?php echo $settings['play_pause_control']=== 'yes'?'1':'0' ?>"
		<?php endif;?>
	<?php endif;?>
	data-speed="<?php echo $settings['speed_opt_slider'] === 'slow' ? 4 : ($settings['speed_opt_slider'] === 'fast' ? '.5' : 1) ?>"
	data-wrapvar="<?php echo $settings['wrap_slider']==='yes'?'1':'0' ?>"
	data-slider_nav="<?php echo $hasNav ?>"
	data-height="<?php echo isset($settings['horizontal']) && $settings['horizontal'] === 'yes' ? 'variable' : $settings['height_slider'] ?>"
	<?php if ( isset( $settings['touch_swipe'] ) && $settings['touch_swipe'] !== '' ) : ?>data-touch_swipe="<?php echo $settings['touch_swipe']; ?>" <?php endif; ?>
	>
	<div class="tf_swiper-wrapper tf_lazy tf_rel tf_w tf_h tf_textc">
		<?php foreach( $settings['gallery_images'] as $image ) : ?>
			<div class="tf_swiper-slide">
			<div class="slide-inner-wrap"<?php $margins!=='' && printf( ' style="%s"', $margins ) ?>>
				<div class="tf_lazy slide-image gallery-icon"><?php
					$image_attr['alt']= get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
					$image_attr['src']= wp_get_attachment_image_url( $image->ID, 'full' );
					$image_html =themify_get_image( $image_attr);

					$lightbox = '';
					$link=null;
					if( $is_slider===true){
						if( $settings['link_opt'] === 'file' ) {
							$link = wp_get_attachment_image_src( $image->ID, $settings['link_image_size'] );
							$link = $link[0];
							if ( $settings['lightbox']===true ) {
								$lightbox = ' class="themify_lightbox"';
							}
						} 
						elseif( 'none' !== $settings['link_opt'] ) {
							$link = get_attachment_link( $image->ID );
						}
					}
					if($is_slider===true && ! empty( $link )) {
						printf( '<a href="%s"%s>%s</a>', esc_url( $link ), $lightbox, $image_html );
					} else {
						echo $image_html;
					}
				?>
				</div>
				<?php if( $is_slider===true && (( $settings['gallery_image_title'] && $image->post_title) || (! $settings['gallery_exclude_caption'] && $image->post_excerpt ))) : ?>
					<div class="slide-content tf_opacity tf_texl tf_abs">
						<?php if($settings['gallery_image_title'] && ! empty( $image->post_title )):?>
								<h3 class="slide-title"><?php echo wp_kses_post( $image->post_title ) ?></h3>
						<?php endif;?>

						<?php if ( ! $settings['gallery_exclude_caption'] && ! empty( $image->post_excerpt ) ) : ?>
							<p><?php echo apply_filters( 'themify_builder_module_content', $image->post_excerpt )?></p>
						<?php endif;?>
					</div><!-- /slide-content -->
				<?php endif; ?>
			</div></div>
		<?php endforeach; ?>
	</div>
</div>
    <?php if($hasNav==='1'): ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php endif; 