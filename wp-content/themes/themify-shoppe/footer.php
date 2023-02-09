<?php
/**
 * Template for site footer
 * @package themify
 * @since 1.0.0
 */

	 ?>
    <?php themify_layout_after(); // hook ?>
</div>
<!-- /body -->
<?php
$backTop='';
$footer_enable=themify_theme_show_area( 'footer' ) && themify_theme_do_not_exclude_all( 'footer' );
if(themify_theme_show_area( 'footer_back' )){
	$is_float=themify_check( 'setting-use_float_back',true );
	if($is_float || (!$is_float && $footer_enable)){
		Themify_Enqueue_Assets::loadThemeStyleModule('back-top');
		$backTop=sprintf( '<div class="back-top tf_vmiddle tf_inline_b tf_textc tf_clearfix %s"><div class="arrow-up"><a aria-label="%s" rel="nofollow" href="#header"><span class="screen-reader-text">%s</span></a></div></div>'
			, $is_float ? 'back-top-float back-top-hide' : '',
			__('Back to top','themify'),
			__('Back to top','themify')
		);
	}
}
?>
<?php if ( $footer_enable) : ?>
    <?php
	$footer_widgets = themify_theme_show_area( 'footer_widgets' );
	$header_design=  themify_theme_get_header_design();
	 if($footer_widgets){
		$footer_position = themify_get('footer_widget_position');
		if(!$footer_position){
			$footer_position = themify_get('setting-footer_widget_position',false,true);
		}
	}
	else{
		$footer_position = false;
	}

    ?>
    <div id="footerwrap" class="tf_clear tf_box">

	    <?php themify_footer_before(); // hook ?>

	    <?php get_template_part( 'includes/footer-banners' ); ?>


	    <footer id="footer" class="pagewidth tf_box tf_clearfix<?php if($header_design==='header-bottom'):?> tf_scrollbar<?php endif;?>" itemscope="itemscope" itemtype="https://schema.org/WPFooter">

		    <?php themify_footer_start(); // hook ?>

		    <div class="footer-column-wrap tf_clear tf_clearfix">
			    <div class="footer-logo-wrap tf_left">
				    <?php if ( themify_theme_show_area( 'footer_site_logo' ) ) : ?>
					    <?php 
					    Themify_Enqueue_Assets::loadThemeStyleModule('footer-logo');
					    echo themify_logo_image( 'footer_logo', 'footer-logo' ); ?>																	  
					    <!-- /footer-logo -->
				    <?php endif; ?>
				    <?php if ( is_active_sidebar( 'below-logo-widget' ) ) : ?>
					    <div class="below-logo-widget">
						    <?php dynamic_sidebar( 'below-logo-widget' ); ?>
					    </div>
					    <!-- /.below-logo-widget -->
				    <?php endif; ?>
			    </div>


			    <!-- /footer-logo-wrap -->
			    <?php if ($footer_widgets) : ?>

				    <div class="footer-widgets-wrap tf_left"> 
					    <?php get_template_part( 'includes/footer-widgets' ); ?>
				    </div>
				    <!-- /footer-widgets-wrap -->
			    <?php endif;?>

			    <?php if ( themify_theme_show_area( 'footer_menu_navigation' ) ) : ?>
				    <?php Themify_Enqueue_Assets::loadThemeStyleModule('footer-nav');?>
				    <div class="footer-nav-wrap tf_clear tf_textc">
					    <?php themify_menu_nav( array(
						    'theme_location' => 'footer-nav',
						    'fallback_cb' => '',
						    'container'  => '',
						    'menu_id' => 'footer-nav',
						    'menu_class' => 'footer-nav',
					    )); ?>
				    </div>
				    <!-- /.footer-nav-wrap -->
			    <?php endif; // exclude menu navigation ?>

				    <div class="footer-text-outer tf_w">

					    <?php 
						    if ($header_design!=='header-bottom' &&  $backTop!=='' ) {
							echo $backTop;
						    }
					    ?>

						<div class="footer-text tf_vmiddle tf_inline_b tf_clearfix">
							<?php if ( themify_theme_show_area( 'footer_texts' ) ) : ?>
								<?php themify_the_footer_text(); 
								themify_the_footer_text('right'); ?>
							<?php endif; ?>
						</div>
						<!-- /.footer-text -->

				    </div>

		    </div>

		    <?php themify_footer_end(); // hook ?>
	    </footer>
	    <?php if($header_design==='header-bottom'):?>
		<a class="footer-tab tf_box" href="#">
		    <?php echo themify_get_icon('angle-down','ti')?>
		</a>
	    <?php endif;?>
	    <!-- /#footer -->

	    <?php themify_footer_after(); // hook ?>

    </div>
    <!-- /#footerwrap -->
    <?php if ($header_design==='header-bottom' && $backTop!=='') {
	    echo $backTop;
    }
    // exclude footer ?>
<?php
elseif ($backTop!==''):
    echo $backTop;
endif;
?>
</div>
<!-- /#pagewrap -->

<?php themify_body_end(); // hook ?>		
<!-- wp_footer -->
<?php wp_footer(); ?>
	</body>
</html>
