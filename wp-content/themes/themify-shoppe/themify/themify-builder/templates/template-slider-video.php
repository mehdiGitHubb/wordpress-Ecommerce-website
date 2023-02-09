<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Slider Video
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-slider-video.php.
 *
 * Access original fields: $args['settings']
 * @author Themify
 */
$settings=$args['settings'];
if (!empty($settings['video_content_slider'])):?>
	
    <?php foreach ($settings['video_content_slider'] as $video): ?>
        <div class="tf_swiper-slide">
            <div class="slide-inner-wrap"<?php if ($settings['margin'] !== ''): ?> style="<?php echo $settings['margin']; ?>"<?php endif; ?>>
                <?php 
			$title_tag = !empty($video['video_title_tag']) ? $video['video_title_tag'] : 'h3';
			if (!empty($video['video_url_slider'])): 
			$video_url = parse_url($video['video_url_slider']);
			$isLocal=isset($video_url['host']) && $video_url['host'] !== 'www.youtube.com'
			     && $video_url['host'] !== 'youtube.com'
			     && $video_url['host'] !== 'youtu.be'
			     && $video_url['host'] !== 'www.vimeo.com'
			     && $video_url['host'] !== 'vimeo.com'
			     && $video_url['host'] !== 'player.vimeo.com';
		    ?>
                    <div class="slide-image tf_rel tf_lazy tf_overflow"<?php echo !empty($video['video_width_slider'])? 'style="max-width:' . $video['video_width_slider']. 'px"' : ''; ?>>
	                <?php 
	                echo $isLocal===true?wp_video_shortcode(array('src' => $video['video_url_slider'],'preload'=>'none')): '<div class="video-wrap" data-url="'.$video['video_url_slider'].'"></div>';
					?>		
                    </div><!-- /video-wrap -->
                <?php endif; ?>

                <div class="slide-content tb_text_wrap">
                    <?php if(!empty($video['video_title_link_slider']) || !empty($video['video_title_slider'])): ?>
                    <<?php echo $title_tag;?> class="slide-title"<?php self::add_inline_edit_fields('video_title_slider',empty($video['video_title_link_slider']),false,'video_content_slider')?>>
                        <?php if (!empty($video['video_title_link_slider'])): ?>
                            <a href="<?php echo esc_url($video['video_title_link_slider']); ?>"<?php if('yes' === $settings['open_link_new_tab_slider']):?> target="_blank" rel="noopener"<?php endif;?><?php self::add_inline_edit_fields('video_title_slider',true,false,'video_content_slider')?>><?php echo $video['video_title_slider']; ?></a>
                        <?php elseif (!empty($video['video_title_slider'])) : ?>
                            <?php echo $video['video_title_slider']; ?>
                        <?php endif; ?>
                    </<?php echo $title_tag;?>>
                    <?php endif; ?>
                    <div class="video-caption"<?php self::add_inline_edit_fields('video_caption_slider',true,false,'video_content_slider')?>>
                        <?php
                        if (isset($video['video_caption_slider'])) {
                            echo apply_filters('themify_builder_module_content', $video['video_caption_slider']);
                        }
                        ?>
                    </div>
                    <!-- /video-caption -->
                </div><!-- /video-content -->
            </div>
        </div>
    <?php endforeach; // end loop video  ?>
<?php endif; 
