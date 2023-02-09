<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Video
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-video.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */

$fields_default = array(
    'mod_title_video' => '',
    'style_video' => '',
    'url_video' => '',
    'ext_start' => '',
    'ext_end' => '',
    'ext_hide_ctrls' => 'no',
    'ext_privacy' => '',
    'ext_branding' => '',
    'dl_btn' => '',
    'autoplay_video' => '',
    'mute_video' => 'no',
    'loop' => 'no',
    'width_video' => '',
    'unit_video' => 'px',
    'title_tag' => 'h3',
    'title_video' => '',
    'title_link_video' => false,
    'caption_video' => '',
    'css_video' => '',
    'animation_effect' => '',
    'o_i_c'=>'',
    'o_i'=>'',
    'o_w'=>'',
    'o_h' => '',
    'hover_play' => '',
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$fields_default=null;
$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = $args['module_ID'];

if($fields_args['o_i_c']!==''){
    $fields_args['o_i_c'] = self::get_checkbox_data($fields_args['o_i_c']);
}

$video_maxwidth = $fields_args['width_video'] !== '' ? $fields_args['width_video'] . $fields_args['unit_video'] : '';
$video_autoplay_css = $fields_args['autoplay_video'] === 'yes' ? 'video-autoplay' : '';
if($fields_args['style_video']==='video-overlay'){
    Themify_Builder_Model::load_module_self_style($mod_name,'overlay');
}
$container_class = apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $mod_name,
    $element_id, 
    $fields_args['style_video'], 
    $fields_args['css_video'], 
    $video_autoplay_css
), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect($fields_args,array(
    'class' => implode(' ', $container_class),
	)), $fields_args, $mod_name, $element_id);
$args=null;
if(Themify_Builder::$frontedit_active===false){
    $container_props['data-lazy']=1;
}

$url = esc_url( $fields_args['url_video'] );
$isOverlay=$fields_args['o_i']!=='' && $fields_args['o_i_c']==='1';
if ( ! empty( $url ) ) {
	$video_args=array(
	    'privacy'=>$fields_args['ext_privacy'],
	    'loop'=>$fields_args['loop']==='yes',
	    'branding'=>$fields_args['ext_branding']==='yes',
	    'autoplay'=>$isOverlay===true || $fields_args['autoplay_video'] === 'yes',
	    'muted'=>$fields_args['mute_video'] === 'yes',
	    'hide_controls'=>$fields_args['ext_hide_ctrls'] === 'yes',
	    'start'=>$fields_args['ext_start'],
	    'end'=>$fields_args['ext_end'],
	    'disable_lazy'=>true
	);
	$iframe=themify_get_embed($url,$video_args);
	$isLocal=$iframe==='';
	if($isLocal===true){
	    if($video_args['hide_controls'] !== true){
		themify_get_icon('fas volume-mute','fa');
		themify_get_icon('fas volume-up','fa');
		themify_get_icon('fas external-link-alt','fa');
		themify_get_icon('fas airplay','fa');
		themify_get_icon('fas expand','fa');
	    }

	    if ($fields_args['hover_play'] === 'yes' ) {
		    $container_props['class'] .= ' tb_hover_play';
	    }
	}
	unset($video_args);
}
?>
<!-- module video -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
    <?php
	$container_props=$container_class=null;
	if ( !empty( $url ) ):?>
		<?php 
			echo Themify_Builder_Component_Module::get_module_title($fields_args,'mod_title_video');
		?>
		<div class="video-wrap-outer"<?php echo '' !== $video_maxwidth ? ' style="max-width:' . $video_maxwidth . ';"' : ''; ?>>
			<div class="video-wrap tf_rel tf_overflow<?php echo $isLocal===true?' tf_local_video':''; ?>">
				<?php
				if($isOverlay===true){
				    $image_args=array(
					'src'=>$fields_args['o_i'],
					'w' => $fields_args['o_w'],
					'h' => $fields_args['o_h'],
					'class'=>$isLocal===true && $fields_args['hover_play']==='yes'?'tb_video_poster tf_abs_t':'',
					'alt'=>$fields_args['title_video'],
					'attr'=>Themify_Builder::$frontedit_active===false?array():array('data-w'=>'o_w', 'data-h'=>'o_h','data-name'=>'o_i')
				    );
				    $image_args['style']='width:100%;height:100%;object-fit:cover';
				}
				if($isLocal===true){
					$video=wp_video_shortcode(array(
					    'src'=>$url,
					    'muted'=>$isOverlay===false &&  $fields_args['mute_video'] === 'yes',
					    'loop'=>$fields_args['loop'] === 'yes',
					    'autoplay'=>$fields_args['autoplay_video'] === 'yes',
					));
					$r=$fields_args['hover_play'] === 'yes'?'data-hover-play="1"':'';
					if(!empty($fields_args['dl_btn'])){
					    themify_get_icon('fas download','fa');
					    $r.=' data-download';
					}
					if($fields_args['ext_hide_ctrls'] === 'yes'){
					    $r.=' data-hide-controls';
					}
					if($isOverlay===true){
					    $r.=' data-no-script';
					}
					?>
					<?php if($isOverlay===true):?>
					    <div class="tf_vd_lazy tf_w tf_box tf_rel">
					<?php endif;?>
					<?php 
					    echo str_replace(' preload=', $r.' preload=', $video);
					    $r=null;
					?>
					<?php if($isOverlay===true):?>
					    </div>
					<?php endif;?>
					<?php 
				}
				else{
				    ?>
					<noscript><?php echo $iframe?></noscript>
				    <?php
				    
				}
				if($isOverlay===true){
					?>
					<?php if($fields_args['hover_play']!=='yes'):?>
					    <div class="tb_video_overlay tf_abs_t tf_w tf_h">
						<div class="tb_video_play tf_abs_c"></div>
					<?php endif;?>  
					    <?php echo themify_get_image($image_args);?>
					<?php if($fields_args['hover_play']!=='yes'):?>
					    </div>
					<?php endif;?>  
					<?php
				}
				?>
			</div>
			<!-- /video-wrap -->
		</div>
		<!-- /video-wrap-outer -->
		<?php if ('' !== $fields_args['title_video'] || '' !== $fields_args['caption_video']): ?>
			<div class="video-content">
				<?php if ('' !== $fields_args['title_video']): ?>
					<<?php echo $fields_args['title_tag'];?> class="video-title"<?php self::add_inline_edit_fields('title_video',!$fields_args['title_link_video'])?>>
						<?php if ($fields_args['title_link_video']) : ?>
							<a href="<?php echo esc_url($fields_args['title_link_video']); ?>"<?php self::add_inline_edit_fields('title_video')?>><?php echo $fields_args['title_video']; ?></a>
						<?php else: ?>
							<?php echo $fields_args['title_video']; ?>
						<?php endif; ?>
					</<?php echo $fields_args['title_tag'];?>>
				<?php endif; ?>

				<?php if ('' !== $fields_args['caption_video']): ?>
					<div class="video-caption tb_text_wrap"<?php self::add_inline_edit_fields('caption_video')?>>
						<?php echo apply_filters('themify_builder_module_content', $fields_args['caption_video']); ?>
					</div>
					<!-- /video-caption -->
				<?php endif; ?>
			</div>
			<!-- /video-content -->
		<?php endif; ?>
	<?php endif;?>
</div>
