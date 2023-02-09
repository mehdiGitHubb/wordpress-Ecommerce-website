<?php global $themify;?>
<?php if($themify->hide_meta !== 'yes' && ($themify->hide_meta_tag !== 'yes' || $themify->hide_meta_category !== 'yes')): ?>
	<span class="post-cat-tag-wrap">
		<?php themify_meta_taxonomies(!empty($themify->post_module_tax)?$themify->post_module_tax:'','<span class="post-meta-separator">,</span> ');
		if($themify->hide_meta_tag !== 'yes'){
		    the_terms( get_the_ID(), 'post_tag', ' <span class="post-tag">', '<span class="post-meta-separator">,</span> ', '</span>' );
		}
		?>
	</span>
<?php endif;