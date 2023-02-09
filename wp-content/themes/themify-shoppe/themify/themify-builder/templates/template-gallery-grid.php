<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Gallery Grid
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-gallery-grid.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
Themify_Builder_Model::load_module_self_style($args['mod_name'],'grid');
$settings=$args['settings'];
$pagination = $settings['gallery_pagination'] && $settings['gallery_per_page'] > 0;

if ($pagination===true) {
    $total = count($settings['gallery_images']);
    if ($total <= $settings['gallery_per_page']) {
        $pagination = false;
    } else {
        $is_current_gallery = !empty($_GET['tb_gallery']) ? $args['module_ID'] === $_GET['tb_gallery'] : true;
        $current = isset($_GET['builder_gallery']) && $is_current_gallery ? $_GET['builder_gallery'] : 1;
        $offset = $settings['gallery_per_page'] * ( $current - 1 );
        $settings['gallery_images'] = array_slice($settings['gallery_images'], $offset, $settings['gallery_per_page'], true);
    }
}
$responsive_cols = !empty($settings['t_columns']) ? ' gallery-t-columns-'.$settings['t_columns']:'';
$responsive_cols .= !empty($settings['m_columns']) ? ' gallery-m-columns-'.$settings['m_columns']:'';
$image_attr=array('w'=>$settings['thumb_w_gallery'],'h'=>$settings['thumb_h_gallery'],'image_size'=>$settings['image_size_gallery']);
if(Themify_Builder::$frontedit_active===true && false){
	$image_attr['attr']=array('data-w'=>'thumb_w_gallery', 'data-h'=>'thumb_h_gallery');
}
?>
<div class="module-gallery-grid gallery-columns-<?php echo $args['columns'],$responsive_cols;?><?php if($settings['layout_masonry'] === 'masonry'):?> gallery-masonry<?php endif;?> tf_clear">
	<?php foreach ($settings['gallery_images'] as $image) :
		$caption = !empty($image->post_excerpt) ? $image->post_excerpt : '';
		$title = $image->post_title;
		?>
		<dl class="gallery-item">
			<dt class="gallery-icon">
			<?php
			if ($settings['link_opt'] === 'file') {
				$link = wp_get_attachment_image_src($image->ID, $settings['link_image_size']);
				$link = $link[0];
			}
			elseif ('none' === $settings['link_opt']) {
				$link = '';
			} 
			else {
				$link = get_attachment_link($image->ID);
			}
			$link_before = '' !== $link ? sprintf(
				'<a data-title="%s" title="%s" href="%s" data-rel="%s" %s>',
				esc_attr( $settings['lightbox_title'] ),
				esc_attr( $caption ),
				esc_url( $link ),
				$args['module_ID'],
				( $settings['lightbox']===true ? 'class="themify_lightbox"' : '' )
			) : '';
			$link_before = apply_filters('themify_builder_image_link_before', $link_before, $image, $settings);
			$link_after = '' !== $link ? '</a>' : '';
			$image_attr['src']=$image->ID;
			$img = themify_get_image($image_attr);
			
			if(!empty($img) ){
				echo $link_before, $img , $link_after;
			}
			?>
			</dt>
			<dd<?php if (($settings['gallery_image_title'] === 'yes' && $title!=='' ) || ( $settings['gallery_exclude_caption'] !== 'yes' && $caption!=='' )) : ?> class="wp-caption-text gallery-caption"<?php endif; ?>>
				<?php if ($settings['gallery_image_title'] === 'yes' && $title!=='') : ?>
					<strong class="themify_image_title tf_block"><?php echo $title ?></strong>
				<?php endif; ?>
				<?php if ($settings['gallery_exclude_caption'] !== 'yes' && $caption!=='') : ?>
					<span class="themify_image_caption"><?php echo $caption ?></span>
				<?php endif; ?>
			</dd>
		</dl>
	<?php endforeach; // end loop  ?>
</div>
<?php
if ($pagination===true) :
    echo Themify_Builder_Component_Base::get_pagination('','','tb_gallery='.$args['module_ID'].'&builder_gallery',0,ceil($total / $settings['gallery_per_page']),$current);
endif;