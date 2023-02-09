<?php 
	global $product, $post,$themify;
	$attachment_ids = $product->get_gallery_image_ids();
	$isZoom=themify_get_gallery_type()!=='disable-zoom';
	$id=$product->get_image_id();
	if(!empty($id)){
		array_unshift($attachment_ids,$id);
	}
	$width=$themify->width;
	$height=$themify->height;
	$size = wc_get_image_size(Themify_WC::$thumbImageSize );
	$thumbW=$size['width'];
	$thumbH=$size['height'];
	$Images=array();
	$isFirst=false;
?>
<div class="tf_swiper-container tf_carousel product-images-carousel tf_overflow tf_rel tf_right tf_w tf_opacity">
	<div class="tf_swiper-wrapper tf_rel tf_w tf_h">
		<?php foreach ( $attachment_ids as $attach ):?>
			<?php 
				if(has_post_thumbnail()){
					$props = wc_get_product_attachment_props( $attach, $post );
					if(empty($props['url'])){
						continue;
					}
					$Images[]=$props['url'];
					$image = themify_get_image( array('src'=>$props['url'],'w'=>$width,'h'=>$height,'lazy_load'=>$isFirst,'is_slider'=>true,'image_meta'=>false,'image_size'=>Themify_WC::$singleImageSize) );
					$hasZoom=$isZoom;
					$isFirst=true;
				}
				else{
					$src=wc_placeholder_img_src();
					$Images[]=$src;
					$image=sprintf( '<img src="%s" width="%s" height="%s" alt="%s" class="wp-post-image">'
					,$src
					,$width
					,$height
					,esc_html__( 'Awaiting product image', 'themify' ) );
					$hasZoom=false;
					$image=themify_make_lazy($image,false);
				}
			?>
			<div <?php if($hasZoom===true ):?>data-zoom-image="<?php echo $props['url']?>" <?php endif;?> class="tf_swiper-slide woocommerce-main-image woocommerce-product-gallery__image post-image<?php if($hasZoom===true ):?> zoom<?php endif;?>">
				<?php echo $image;?>
			</div>
		<?php endforeach;?>
	</div>
</div>
<div class="tf_loader"></div>
<?php if(count($Images)>1): ?>
<div class="tf_swiper-container tf_carousel product-thumbnails-carousel tf_rel tf_left tf_opacity">
	<div class="tf_swiper-wrapper tf_rel tf_w tf_h tf_inline_b">
	<?php foreach ( $Images as $url ):?>
		<div class="tf_swiper-slide post-image">
			<?php echo themify_get_image( array('src'=>$url,'w'=>$thumbW,'h'=>$thumbH,'lazy_load'=>false,'image_meta'=>false,'image_size'=>Themify_WC::$thumbImageSize,'disable_responsive'=>true) ); ?>
		</div>
	<?php  endforeach;?>
	</div>
</div>
<?php endif; ?>