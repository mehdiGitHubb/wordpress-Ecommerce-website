<?php
/**
 * Template for displaying posts inside mega menus
 *
 * To override this template copy it to <theme_root>/includes/ directory.
 *
 * @package themify
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post,$product;
$dimensions = apply_filters( 'themify_mega_menu_image_dimensions', array(
	'w'  => themify_get( 'setting-mega_menu_image_width', 180,true ),
	'h' => themify_get( 'setting-mega_menu_image_height', 120,true ),
	'disable_lazy'=>true,
    'src'=>themify_has_post_video()?themify_fetch_video_image(themify_get( 'video_url' ),true ):''
) );
$link=themify_permalink_attr(array(),false);
$cl=$link['cl']!==''?' class="'.$link['cl'].'"':'';
$img=themify_get_image($dimensions);
?>
<article class="post type-<?php echo get_post_type(); ?>">
    <a href="<?php echo $link['href']; ?>"<?php echo $cl?>>
        <?php if(!empty($img)): ?>
        <figure class="post-image">
            <?php echo $img; ?>
        </figure>
        <?php endif; ?>
        <p class="post-title">
            <?php the_title_attribute( 'post='.$post->ID ); ?>
        </p>
        <?php if(isset($product)){
            echo $product->get_price_html();
        }?>
    </a>
</article>
<!-- /.post -->
