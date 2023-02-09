<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Slider Image
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-slider-image.php.
 *
 * Access original fields: $args['settings']
 * @author Themify
 */
$settings=$args['settings'];
if (!empty($settings['img_content_slider'])):
    $image_w = $settings['img_w_slider'];
    $image_h = $settings['img_h_slider'];
	$image_size = $settings['image_size_slider'] !== '' ? $settings['image_size_slider'] : themify_builder_get('setting-global_feature_size', 'image_global_size_field');
	$param_image = array('w' => $image_w, 'h' => $image_h,'is_slider'=>true,'image_size'=>$image_size);
    ?>
    <!-- module slider image -->

    <?php foreach ($settings['img_content_slider'] as $content): ?>
        <?php $image_title = isset($content['img_title_slider']) ? trim($content['img_title_slider']) : '';
		$isAlt=false;
		$attr = '';
		if (isset($content['img_link_params'])) {
			$attr = $content['img_link_params'] === 'lightbox' ? ' data-rel="' . $args['module_ID'] . '" class="themify_lightbox"' : ($content['img_link_params'] === 'newtab' ? ' target="_blank" rel="noopener"' : '');
		}
	?>
         <div class="tf_swiper-slide">
            <div class="slide-inner-wrap"<?php if ($settings['margin'] !== ''): ?> style="<?php echo $settings['margin']; ?>"<?php endif; ?>>
                <?php if ( ! empty( $content['img_url_slider'] ) ) : ?>
                    <div class="tf_rel tf_lazy slide-image">
                        <?php
						if ( $image_title===''  ) {
							$image_title = Themify_Builder_Model::get_alt_by_url( $content['img_url_slider'] );
							$isAlt=true;
						}
						$param_image['src'] = $content['img_url_slider'];
						$param_image['alt'] = $image_title;
						$image = themify_get_image($param_image);
                        ?>
                        <?php if (!empty($content['img_link_slider'])): ?>
                            <a href="<?php echo esc_url(trim($content['img_link_slider'])); ?>" <?php echo $attr; ?>>
                                <?php echo $image; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $image; ?>
                        <?php endif; ?>
                    </div><!-- /slide-image -->
                <?php endif; ?>

                <?php if (($isAlt===false && $image_title !== '') || isset($content['img_caption_slider'])): ?>
                    <div class="slide-content tb_text_wrap">

                        <?php if ($isAlt===false && $image_title !== ''): ?>
                            <?php $title_tag = !empty($content['img_title_tag']) ? $content['img_title_tag'] : 'h3'; ?>
                            <<?php echo $title_tag;?> class="slide-title"<?php self::add_inline_edit_fields('img_title_slider',empty($content['img_link_slider']),false,'img_content_slider')?>>
                                <?php if (!empty($content['img_link_slider'])): ?>
                                    <a href="<?php echo esc_url($content['img_link_slider']); ?>"<?php echo $attr; ?><?php self::add_inline_edit_fields('img_title_slider',true,false,'img_content_slider')?>><?php echo wp_kses_post($image_title); ?></a>
                                <?php else: ?>
                                    <?php echo $image_title; ?>
                                <?php endif; ?>
                            </<?php echo $title_tag;?>>
                        <?php endif; ?>

                        <?php if (isset($content['img_caption_slider'])) :?>
							<div<?php self::add_inline_edit_fields('img_caption_slider',true,false,'img_content_slider')?>><?php echo apply_filters('themify_builder_module_content', $content['img_caption_slider']);?></div>
                        <?php endif; ?>
                    </div><!-- /slide-content -->
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; 
