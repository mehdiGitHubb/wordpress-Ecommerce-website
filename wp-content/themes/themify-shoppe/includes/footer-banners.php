<?php $socials = themify_get_footer_banners();?>
<?php if(!empty($socials)):?>
	<?php Themify_Enqueue_Assets::loadThemeStyleModule('footer-social-badge');?>
	<div class="footer-social-wrap tf_w tf_overflow">
		<?php 
			$styles ='';
			$key = 'settings-footer_banner_';
		?>
		<?php foreach($socials as $k=>$v):?>
			<?php $input=$key.$k;?>
			<?php if(themify_check($input,true)):?>
				<?php 
					$link=themify_get($key.$k.'_link',false,true);
					$username=themify_get($key.$k.'_username',false,true);
					$image=themify_get($key.$k.'_image',false,true);
					if($image){
						$styles.='.footer-social-badge a.tfb-'.$k.'-alt:not([data-lazy]){background-image:url('.esc_url($image).')}'."\n";
					}
					
				?>
				<div class="footer-social-badge tf_textc tf_vmiddle tf_overflow">
					<a class="tfb-<?php echo $k?>-alt" data-lazy="1" href="<?php echo $link?esc_url($link):''?>">
					    <?php echo themify_get_icon($k,'ti',true); ?>
						<strong><?php echo $v?></strong>
						<span><?php echo $username?></span>
					</a>
				</div>
			<?php endif;?>
		<?php endforeach;?>
		<?php if($styles!==''):?>
			<style>
			    <?php echo $styles?>
			</style>
		<?php endif;?>
	</div>
<?php endif;