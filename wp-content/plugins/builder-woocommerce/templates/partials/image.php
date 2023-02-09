<figure class="post-image">
    <?php if($args['hide_sales_badge']!== 'yes' ) : ?>
	    <?php woocommerce_show_product_loop_sale_flash(); ?>
    <?php endif; ?>
    <?php
	    $thumbId=get_post_thumbnail_id();
	    $param_image=array(
		    'w'=>$args['img_width_products'],
		    'h'=>$args['img_height_products'],
		    'image_size'=>$args['image_size_products']
	    );
	    if($args['template_products']==='slider'){
		    $param_image['is_slider']=true;
	    }
	    $post_image='';
	    if( $thumbId){
		    $param_image['src']=$thumbId;
		    $post_image =themify_get_image( $param_image );
	    }
	    if ( '' === $post_image ) {
		    $post_image = wc_placeholder_img();
	    }
	    if($args['hover_image']==='yes'){
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            if ( !empty($attachment_ids) ){
                $first_image_url = wp_get_attachment_url( $attachment_ids[0] );
                if(!empty($first_image_url)){
					$pimage=$param_image;
					$pimage['src']= $attachment_ids[0];
					$pimage['class']='tf_abs tf_opacity tf_wc_hover_image';
					$post_image.=themify_get_image($pimage);
                }
            }
	    }
    ?>
    <?php if ( $args['unlink_feat_img_products']  === 'yes' ): ?>
	<?php echo $post_image; ?>
    <?php else: ?>
	<a href="<?php echo the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo $post_image; ?></a>
    <?php endif; ?>
</figure>