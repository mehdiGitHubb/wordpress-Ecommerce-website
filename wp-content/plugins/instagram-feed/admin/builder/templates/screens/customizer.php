<div class="sb-customizer-ctn sbi-fb-fs" v-if="iscustomizerScreen">
	<?php include_once SBI_BUILDER_DIR . 'templates/sections/customizer/sidebar.php'; ?>
	<?php include_once SBI_BUILDER_DIR . 'templates/sections/customizer/preview.php'; ?>
</div>
<div v-html="feedStyleOutput != false ? feedStyleOutput : ''"></div>
<script type="text/x-template" id="sbi-colorpicker-component">
	<input type="text" v-bind:value="color" placeholder="Select">
</script>