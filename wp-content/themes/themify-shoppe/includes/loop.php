<?php
/**
 * Template for generic post display.
 * @package themify
 * @since 1.0.0
 */
global $themify; 
?>

<?php themify_post_before(); // hook ?>
<article id="post-<?php the_id(); ?>" <?php post_class( 'post tf_clearfix' ); ?>>
	<?php themify_post_start(); // hook ?>

	<?php if('below' !== $themify->media_position) themify_post_media(); ?>

	<div class="post-content">
        <?php if($themify->unlink_image !== 'yes' && $themify->post_layout==='auto_tiles'):?>
            <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute() ?>" class="tiled_overlay_link"><span class="tf_hide"><?php _e( 'Post', 'themify' ); ?></span></a>
        <?php endif;?>
		<div class="post-content-inner-wrapper">
			<div class="post-content-inner">
	
			<?php get_template_part( 'includes/post-cats')?>
	
			
			<?php themify_post_title(); ?>
			
			<?php if($themify->hide_meta !== 'yes'): ?>
				<p class="post-meta entry-meta">
					<?php if($themify->hide_meta_author !== 'yes'): ?>
						<span class="post-author"><?php echo themify_get_author_link(); ?></span>
					<?php endif; ?>
					
					<?php themify_comments_popup_link(array('zero'=>__( '0 Comments', 'themify' ),'one'=>__( '1 Comment', 'themify' ),'more'=>__( '% Comments', 'themify' )));?>
					
					<?php get_template_part( 'includes/post-date')?>
				</p>
			<?php endif; //post meta ?>

			<?php if('below' === $themify->media_position) themify_post_media(); ?>
			
			<?php themify_post_content();?>
			</div>
		</div>
	</div>
	<!-- /.post-content -->
	<?php themify_post_end(); // hook ?>

</article>
<!-- /.post -->
<?php themify_post_after(); // hook 
