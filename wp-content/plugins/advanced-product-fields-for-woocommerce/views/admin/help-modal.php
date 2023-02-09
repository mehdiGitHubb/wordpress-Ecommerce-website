<?php
/* @var $model array */
$class = 'wapf--' . uniqid();
?>
<a style="padding-top:15px;display: inline-block;" href="javascript:jQuery('.<?php echo $class;?>').show();">
	<?php
	if(empty($model['button']))
		_e('View help','advanced-product-fields-for-woocommerce');
	else echo $model['button'];
	?>
</a>
<div class="wapf_modal_overlay <?php echo $class; ?>">
	<div class="wapf_modal">
		<a class="wapf_close" href="javascript:jQuery('.<?php echo $class ?>').hide();">&times;</a>
		<?php
		if(!empty($model['title']))
			echo '<h3>' . $model['title'] . '</h3>';
		?>
		<div>
			<?php echo $model['content']; ?>
		</div>
	</div>
</div>