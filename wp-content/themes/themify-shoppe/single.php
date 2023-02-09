<?php
/**
 * Template for single post view
 * @package themify
 * @since 1.0.0
 */
get_header();
?>
<!-- layout-container -->
<div id="layout" class="pagewidth tf_box tf_clearfix">
    <?php
    if (have_posts()) {
	the_post();

	themify_content_before(); // hook  
	?>
        <!-- content -->
        <main id="content" class="tf_left tf_box tf_clearfix">
	    <?php
	    themify_content_start(); // hook 

	    get_template_part('includes/loop', 'single');

	    get_template_part('includes/author-box', 'single');

	    wp_link_pages(array('before' => '<p class="post-pagination"><strong>' . __('Pages:', 'themify') . ' </strong>', 'after' => '</p>', 'next_or_number' => 'number',));

	    get_template_part('includes/post-nav');

	    themify_comments_template();

	    themify_content_end(); // hook
	    ?>
        </main>
        <!-- /content -->
	<?php
	themify_content_after(); // hook 
    }
    themify_get_sidebar();
    ?>
</div>
<!-- /layout-container -->
<?php
get_footer();
