<?php
/**
 * Template for Main Sidebar
 * @package themify
 * @since 1.0.0
 */
?>
<?php themify_sidebar_before(); // hook ?>

<aside id="sidebar" class="tf_box tf_right" itemscope="itemscope" itemtype="https://schema.org/WPSidebar">

	<?php 
		    themify_sidebar_start(); // hook 

	dynamic_sidebar( themify_theme_get_sidebar_type() ); 

	themify_sidebar_end(); // hook ?>

</aside>
<!-- /#sidebar -->
<?php themify_sidebar_after(); // hook 