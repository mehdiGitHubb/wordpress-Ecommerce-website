<div id="pagewrap" class="full_width sidebar-none tf_box tf_h">
	<div id="body" class="tf_clear tf_box tf_mw tf_h tf_clearfix">
		<div id="layout" class="pagewidth tf_box tf_clearfix">
			<div id="content" class="tf_h tf_box tf_clearfix">
				<div class="tf_box product-lightbox">
					<?php if (have_posts()){ 
							themify_disable_other_lazy();
							the_post();
							wc_get_template_part('content', 'single-product'); 
						} 
					 ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
if(themify_get_gallery_type()==='default'){
	woocommerce_photoswipe();
}