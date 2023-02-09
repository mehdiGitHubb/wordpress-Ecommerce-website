<?php global $themify;
if($themify->hide_date !== 'yes'): ?>
	<time datetime="<?php the_time('o-m-d') ?>" class="post-date entry-date updated"><?php the_time( apply_filters( 'themify_loop_date', get_option( 'date_format' ) ) ) ?></time>
<?php endif; //post date 