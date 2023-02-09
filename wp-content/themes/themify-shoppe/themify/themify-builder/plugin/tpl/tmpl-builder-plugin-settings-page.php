<div id="tb_panel">
    <template shadowroot="open">
	<link href="<?php echo THEMIFY_URI . '/css/base.min.css'?>?ver=<?php echo THEMIFY_VERSION ?>" rel="stylesheet"/>
    	<link href="<?php echo themify_enque(THEMIFY_BUILDER_URI.'/plugin/css/builder-plugin-settings-page.css')?>?ver=<?php echo THEMIFY_VERSION ?>" rel="stylesheet"/>
	<link href="<?php echo themify_enque(THEMIFY_URI.'/css/admin/form.css')?>?ver=<?php echo THEMIFY_VERSION ?>" rel="stylesheet"/>
	<div class="container">
	    <div class="header">
		<div class="header_right">
		    <div class="title"><?php _e('Themify Builder','themify')?></div>
		    <small class="version"><?php echo THEMIFY_VERSION?></small>
		    <button form="main" class="save"><?php _e('Save','themify')?></button>
		</div>
	    </div>
	    <form method="POST" id="main" class="tf_opacity"></form>
	    <div class="footer">
		<a href="https://themify.me/logs/framework-changelogs/" target="_blank">
		    <?php echo themify_get_icon('ti-themify-logo','ti')?>
		    <small class="version">v<?php echo THEMIFY_VERSION?></small>
		</a>
		<button form="main" class="save"><?php _e('Save','themify')?></button>
	    </div>
	</div>
    </template>
</div>