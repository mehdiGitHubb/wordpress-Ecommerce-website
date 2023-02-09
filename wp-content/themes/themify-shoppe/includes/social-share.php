<?php
/**
 * Template to display social share buttons.
 * @since 1.0.0
 */

$networks = Themify_Social_Share::get_active_networks();?>
<?php if(!empty($networks)):?>
<?php
	Themify_Enqueue_Assets::loadThemeWCStyleModule('social-share');
?>
<div class="share-wrap">
	<a class="share-button" href="javascript:void(0);"><?php echo themify_get_icon(themify_get('setting-ic-share','ti-export',true),false,true,false,array('aria-label'=>__('Share','themify')))?><span class="screen-reader-text"><?php _e( 'Share', 'themify' ); ?></span></a>
	<div class="social-share">
		<?php foreach($networks as $k=>$n):?>
		    <a onclick="window.open('<?php echo Themify_Social_Share::get_network_url($k)?>','<?php echo $k?>','<?php echo Themify_Social_Share::get_window_params($k)?>')" title="<?php esc_attr_e($n)?>" rel="nofollow" href="javascript:void(0);" class="share">
			<?php 
			if($k==='twitter'){
			    $k.='-alt';
			}
			echo themify_get_icon($k,'ti',true,false,array('aria-label'=>esc_attr($n)))?>
		    </a>
		<?php endforeach;?>
	</div>
</div>
<?php endif;?>
<!-- .post-share -->
