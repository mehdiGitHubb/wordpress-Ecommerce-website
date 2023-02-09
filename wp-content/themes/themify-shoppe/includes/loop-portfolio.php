<?php 
global $themify;
$categories = wp_get_object_terms( get_the_id(), 'portfolio-category' );
$is_loop = themify_loop_is_singular( 'portfolio' );
$class = '';
if ( ! is_wp_error( $categories ) ) {
	foreach ( $categories as $cat ) {
		if ( is_object( $cat ) ) {
			$class .= ' cat-' . $cat->term_id;
		}
	}
}
?>

<?php themify_post_before(); //hook ?>
<article id="portfolio-<?php the_id(); ?>" <?php echo post_class('post tf_clearfix portfolio-post' . $class); ?>>
	<?php themify_post_start(); //hook ?>

    <?php if($themify->unlink_title!=='yes' || $themify->unlink_image!=='yes'):?>
        <a <?php themify_permalink_attr(); ?> aria-label="<?php the_title_attribute() ?>" data-post-permalink="yes" style="display: none;"><?php _e( 'Post', 'themify' ); ?></a>
    <?php endif;?>

	<?php if ( $is_loop===true ) : ?>

		<?php if ( $themify->hide_meta !== 'yes' ): ?>
			<p class="post-meta entry-meta">
			    <?php themify_meta_taxonomies('','  <span class="separator">/</span> '); ?>
			</p>
		<?php endif; //post meta ?>

		<?php themify_post_title(); ?>

		 <?php get_template_part('includes/portfolio-meta'); ?>

	<?php endif; // is singular portfolio ?>

	<?php if ( $is_loop===false ) : ?>

	    <?php themify_post_media(); ?>

	<?php endif; // not singular portfolio ?>

	<div class="post-content">

		<?php if ( $is_loop===false ) : ?>
			<div class="disp-table">
				<div class="disp-row">
					<div class="disp-cell valignmid">

						<?php if ( $themify->hide_meta !== 'yes' ): ?>
							<p class="post-meta entry-meta">
								<?php the_terms( get_the_id(), get_post_type() . '-category', '<span class="post-category">', ' <span class="separator">/</span> ', ' </span>' ) ?>
							</p>
						<?php endif; //post meta ?>

						<?php themify_post_title(); ?>

		<?php endif; // is singular portfolio ?>

		<?php themify_post_content();?>

		<?php if ( $is_loop===false ) : ?>

					</div>
					<!-- /.disp-cell -->
				</div>
				<!-- /.disp-row -->
			</div>
			<!-- /.disp-table -->
		<?php endif; // is singular portfolio ?>

	</div>
	<!-- /.post-content -->

	<?php themify_post_end(); //hook ?>
</article>
<!-- /.post -->
<?php themify_post_after(); //hook 
