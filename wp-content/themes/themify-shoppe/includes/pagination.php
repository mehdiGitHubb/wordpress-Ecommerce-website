<?php
/**
 * Partial template for pagination.
 * Creates numbered pagination or displays button for infinite scroll based on user selection
 *
 * @since 1.0.0
 */
if (  'infinite' === themify_get('setting-more_posts',false,true))  {
	global $wp_query, $total_pages;
	if(!isset($total_pages)){
		$total_pages=$wp_query->max_num_pages;
	}
	$current_page = get_query_var( 'paged' );
	if(empty($current_page)){
	    $current_page=get_query_var( 'page',1 );
	}
    $current_page=$current_page<1?1:$current_page;
	if ( $total_pages > $current_page ) {
		Themify_Enqueue_Assets::loadinfiniteCss();
		echo '<p id="load-more" class="tf_textc tf_clear"><a class="load-more-button" href="' . next_posts( $total_pages, false ) . '" class="load-more-button">' . __( 'Load More', 'themify' ) . '</a></p>';
	}
	$total_pages=null;
}
else {
	if ( 'post' === get_post_type() && 'prevnext' === themify_get( 'setting-entries_nav',false,true )) { 
	    Themify_Enqueue_Assets::loadThemeStyleModule('post-nav');
	?>
		<div class="post-nav tf_box tf_clearfix">
			<span class="prev"><?php next_posts_link( __( '&laquo; Older Entries', 'themify' ) ) ?></span>
			<span class="next"><?php previous_posts_link( __( 'Newer Entries &raquo;', 'themify' ) ) ?></span>
		</div>
	<?php 
	} 
	else {
	    global $themify;
	    themify_pagenav( '', '', $themify->query );
	}
}