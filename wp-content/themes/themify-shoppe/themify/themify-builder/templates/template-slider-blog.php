<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Slider Blog
 *
 * This template can be overridden by copying it to yourtheme/themify-builder/template-slider-blog.php.
 *
 * Access original fields: $args['settings']
 * @author Themify
 */
global $themify;
$type = $args['settings']['layout_display_slider'];
$fields_default = array(
    'post_type' => 'post',
    'taxonomy' => 'category',
    $type . '_category_slider' => '',
    'posts_per_page_slider' => '',
    'offset_slider' => '',
    'order_slider' => 'desc',
    'orderby_slider' => 'date',
    'display_slider' => 'content',
    'excerpt_length' => '',
    'hide_post_title_slider' => 'no',
    'hide_feat_img_slider' => 'no'
);
if (isset($args['settings'][$type . '_category_slider'])) {
    $args['settings'][$type . '_category_slider'] = self::get_param_value($args['settings'][$type . '_category_slider']);
}
$fields_args = wp_parse_args($args['settings'], $fields_default);
$mod_name = $args['mod_name'];
$fields_default=null;
if ($type !== 'blog') {
    $fields_args['post_type'] = $type;
    $fields_args['taxonomy'] = $type . '-category';
}
// The Query
$args = array(
    'post_type' => $fields_args['post_type'],
    'post_status' => 'publish',
    'order' => $fields_args['order_slider'],
    'orderby' => $fields_args['orderby_slider'],
    'cache_results'=>false,
    'suppress_filters' => false
);
if ($fields_args['posts_per_page_slider'] !== '') {
    $args['posts_per_page'] = $fields_args['posts_per_page_slider'];
}
Themify_Builder_Model::parseTermsQuery( $args, $fields_args[$type . '_category_slider'], $fields_args['taxonomy'] );
// add offset posts
if ($fields_args['offset_slider'] !== '') {
    $args['offset'] = $fields_args['offset_slider'];
}

Themify_Builder_Model::parse_query_filter( $fields_args, $args );

$args = apply_filters( 'themify_builder_slider_' . $type . '_query_args', $args, $fields_args );
global $post;
$temp_post = $post;
$posts = get_posts($args);
$args=null;
if (!empty($posts)):

	$themify->post_module_hook = $mod_name;
	Themify_Builder_Model::hook_content_start( $fields_args );

    $param_image=array(
	'w'=>$fields_args['img_w_slider'],
	'h'=>$fields_args['img_h_slider'] ,
	'is_slider'=>true
    );
    $attr_link_target = 'yes' === $fields_args['open_link_new_tab_slider'] ? ' target="_blank" rel="noopener"' : '';
    if ($fields_args['image_size_slider'] !== '') {
		$param_image['image_size']=$fields_args['image_size_slider'];
    }
    $isLoop=$ThemifyBuilder->in_the_loop===true;
    $ThemifyBuilder->in_the_loop=true;
    if($fields_args['display_slider'] === 'excerpt' && !empty($fields_args['excerpt_length'])){
        if(isset($themify) && !empty($posts)){
            $temp_excerpt=$themify->excerpt_length;
            $themify->excerpt_length=$fields_args['excerpt_length'];
            $temp_disp=$themify->display_content;
            $themify->display_content='excerpt';
            add_filter("the_excerpt", "themify_custom_except", 999);
        }
    }
    foreach ($posts as $post): setup_postdata($post);
        ?>
        <div class="tf_swiper-slide">
            <div class="slide-inner-wrap"<?php if ($fields_args['margin'] !== ''): ?> style="<?php echo $fields_args['margin']; ?>"<?php endif; ?>>
                <?php
                if (($ext_link = themify_builder_get('external_link',false,false))) {
                    $ext_link_type = 'external';
                } elseif (($ext_link = themify_builder_get('lightbox_link',false,false))) {
                    $ext_link_type = 'lightbox';
                } else {
                    $ext_link = themify_permalink_attr(array(),false);
					$ext_link=$ext_link['href'];
                    $ext_link_type = false;
                }
                if ($fields_args['hide_feat_img_slider'] !== 'yes') {

                    // Check if there is a video url in the custom field
                    if (($vurl = themify_builder_get('video_url',false,false))) {
                        global $wp_embed;

                        $post_image = $wp_embed->run_shortcode('[embed]' . esc_url($vurl) . '[/embed]');
                    } else {
						$post_image = themify_get_image($param_image);
                    }
                    if ($post_image) {
                        ?>
                        <?php themify_before_post_image(); // Hook ?>
                        <figure class="tf_lazy slide-image">
                            <?php if ($fields_args['unlink_feat_img_slider'] === 'yes'): ?>
                                <?php echo $post_image; ?>
                            <?php else: ?>
                                <a href="<?php echo $ext_link; ?>"
                                   <?php if ('lightbox' !== $ext_link_type && 'yes' === $fields_args['open_link_new_tab_slider']): ?> target="_blank" rel="noopener"<?php endif; ?>
                                   <?php if ('lightbox' === $ext_link_type) : ?> class="themify_lightbox" rel="prettyPhoto[slider]"<?php endif; ?>>
                                   <?php echo $post_image; ?>
                                </a>
                            <?php endif; ?>
                        </figure>
                        <?php themify_after_post_image(); // Hook ?>
                    <?php } ?>
                <?php } ?>

                <?php if ($fields_args['hide_post_title_slider'] !== 'yes' || $fields_args['display_slider'] !== 'none'): ?>
                    <div class="slide-content tb_text_wrap">
                        <?php if ($fields_args['hide_post_title_slider'] !== 'yes'): ?>
							<?php themify_before_post_title(); // Hook ?>
							<h3 class="slide-title">
								<?php if ($fields_args['unlink_post_title_slider'] === 'yes'): ?>
								   <?php the_title(); ?>
								<?php else: ?>
										<a href="<?php echo $ext_link; ?>"  
										   <?php if ('lightbox' !== $ext_link_type && 'yes' === $fields_args['open_link_new_tab_slider']): ?> target="_blank" rel="noopener"<?php endif; ?>
										   <?php if ('lightbox' === $ext_link_type) : ?> class="themify_lightbox" rel="prettyPhoto[slider]"<?php endif; ?>>
										   <?php the_title(); ?>
										</a>
								<?php endif; //unlink post title     ?>
							</h3>
							<?php themify_after_post_title(); // Hook ?>
                        <?php endif; // hide post title  ?>
						<?php if ($fields_args['hide_post_date'] !== 'yes'): ?>
                            <time datetime="<?php the_time('o-m-d') ?>" class="post-date"><?php echo get_the_date(apply_filters('themify_loop_date', '')) ?></time>
						<?php endif; //post date   ?>
                        <?php
                        // fix the issue more link doesn't output
						if ( $fields_args['display_slider'] !== 'none' ) {
							global $more;
							$more = 0;
							themify_before_post_content();
							if ( $fields_args['display_slider'] === 'content' ) {
								the_content();
							} else {
								the_excerpt();
							}
							themify_after_post_content();
						}
                        ?>
                        <?php if ($type === 'testimonial'): ?>
                            <p class="testimonial-author">
                                <?php
                                echo themify_builder_testimonial_author_name($post, 'yes');
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <!-- /slide-content -->
                <?php endif; ?>
            </div>
        </div>
        <?php
    endforeach;
    wp_reset_postdata();
    $post = $temp_post;
    $ThemifyBuilder->in_the_loop=$isLoop;
    if(isset($temp_excerpt)){
        $themify->excerpt_length=$temp_excerpt;
        $themify->display_content=$temp_disp;
        remove_filter("the_excerpt", "themify_custom_except", 999);
    }
    ?>

	<?php Themify_Builder_Model::hook_content_end( $fields_args ); ?>

<?php endif; ?>
<!-- /themify_builder_slider -->
