<template id="tmpl-tb_locked">
    <style id="module_locked_style">
        <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/locked.css');?>
    </style>
        <div id="tb_builder_restriction" class="lb tf_abs_c">
            <div class="avatar">
                <?php echo get_avatar( $uid, 64, 'mystery' ) ?>
            </div>
            <div class="content">
                <div class="info">
                    <?php
		    $data = get_userdata($uid);
                    printf( __('<strong>%s</strong> is already editing this Builder. Do you want to take over?', 'themify'), $data->display_name);
                    ?>
                </div>
                <div class="buttons">
                    <a class="btn" href="<?php echo admin_url('edit.php?post_type=' . get_post_type($id)) ?>"><?php _e('All Pages', 'themify') ?></a>
		    <?php if(Themify_Builder_Revisions::is_revision_enabled($id)):?>
			<button type="button" class="btn rvs"><?php _e('Save as Revision', 'themify'); ?></button>
		    <?php endif;?>
                    <button type="button" class="btn take"><?php _e('Take over', 'themify'); ?></button>
                </div>
            </div>
            <button type="button" class="tf_close" title="<?php _e('Close Without Saving','themify')?>"></button>
        </div>
</template>