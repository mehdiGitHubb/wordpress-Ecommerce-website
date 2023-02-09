<?php
/**
 * Template to load footer widgets.
 * @package themify
 * @since 1.0.0
 */
 
$footer_widget_option = themify_get('setting-footer_widgets','footerwidget-4col',true) ;

if ( $footer_widget_option !== 'none' ) : ?>
    
	<div class="footer-widgets tf_clear tf_clearfix">
		<?php
		$columns = array('footerwidget-4col' => array('col4-1','col4-1','col4-1','col4-1'),
						 'footerwidget-3col' => array('col3-1','col3-1','col3-1'),
						 'footerwidget-2col' => array('col4-2','col4-2'),
						 'footerwidget-1col' => array(''),
						 'none_widget' => array()
				);
		foreach($columns[$footer_widget_option] as $k=>$col): ?>
			<?php $class=$k===0?' first':'';?>
			<div class="<?php echo  $col, $class; ?>">
				<?php dynamic_sidebar( 'footer-widget-' . ($k+1) ); ?>
			</div>
		<?php endforeach; ?>

	</div>
	<!-- /.footer-widgets -->

<?php endif; // end footer widget option ?>