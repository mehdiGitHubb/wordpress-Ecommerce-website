<?php
/**
 * Template to load top bar widgets.
 * @package themify
 * @since 1.0.0
 */
Themify_Enqueue_Assets::loadThemeStyleModule('top-bar-widgets');
?>
<div class="top-bar-widgets tf_box">
	<div class="top-bar-widget-inner pagewidth tf_box tf_clearfix">
		<div class="top-bar-left tf_left tf_textl">
			<?php dynamic_sidebar( 'top-bar-left'); ?>
		</div>
		<div class="top-bar-right tf_right tf_textr">
			<?php dynamic_sidebar( 'top-bar-right'); ?>
		</div>
		<!-- /.top-bar-widget-inner -->
	</div>
</div>
<!-- /.top-bar-widget -->