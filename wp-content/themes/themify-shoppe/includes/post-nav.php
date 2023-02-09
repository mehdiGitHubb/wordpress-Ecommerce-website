<?php 
/**
 * Post Navigation Template
 * @package themify
 * @since 1.0.0
 */

if ( ! themify_check( 'setting-post_nav_disable',true ) ) :

	$in_same_cat = themify_check('setting-post_nav_same_cat',true) ?>

	<!-- post-nav -->
	<div class="post-nav tf_box tf_clearfix">

		<?php
		    Themify_Enqueue_Assets::loadThemeStyleModule('post-nav');
		    $nextPost = get_next_post($in_same_cat);
		    $prevPost = get_previous_post($in_same_cat);
		    $css = '';
		    if($nextPost){
			    $nextthumb = wp_get_attachment_image_src(get_post_thumbnail_id( $nextPost->ID,'thumbnail' ),'thumbnail');
			    if($nextthumb){
				    $css = '.post-nav .next .featimg:not([data-lazy]) {background-image:url('.themify_generateWebp($nextthumb[0]).')}';
			    }
		    }
		    if($prevPost){
			    $prevthumb = wp_get_attachment_image_src(get_post_thumbnail_id( $prevPost->ID,'thumbnail'),'thumbnail');
			    if($prevthumb){
				    $css.= '.post-nav .prev .featimg:not([data-lazy]) {background-image:url('.themify_generateWebp($prevthumb[0]).')}';
			    }
		    }
		?>
		<?php if($css):?>
			<style>
				<?php echo $css?>
			</style>
		<?php endif;?>
		<?php if($prevPost):?>
			<?php previous_post_link('<span class="prev">%link</span>', '<span class="arrow"><span data-lazy="1" class="featimg"></span></span> %title', $in_same_cat) ?>
		<?php endif;?>
		<?php if($nextPost):?>
			<?php next_post_link('<span class="next">%link</span>', '<span class="arrow"><span data-lazy="1" class="featimg"></span></span> %title', $in_same_cat) ?>
		<?php endif;?>

	</div>
	<!-- /post-nav -->

<?php endif; 