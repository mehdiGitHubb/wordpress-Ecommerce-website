<?php
global $cpt_id;
?>
<form method="post" action="<?php echo add_query_arg(array('action'=>$this->plugin_name . '_ajax_themes_save'),  admin_url('admin-ajax.php')) ?>">
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '_them_ajax'); ?>" name="<?php echo $this->plugin_name ?>_nonce"/>
    <?php $form = new WPF_Form($this->plugin_name,$this->version,$cpt_id); 
          $form->form();
    ?>
    <p class="submit">
        <button id="<?php echo $this->plugin_name ?>_submit" class="button button-primary"><?php _e('Save', 'wpf') ?></button>
    </p>
    <div id="<?php echo $this->plugin_name ?>_success_text" class="updated"></div>
	<div class="<?php echo $this->plugin_name ?>_wait"></div>
</form>


<script type="text/javascript">
    jQuery(function () {
        WPF.init({
            prefix: '<?php echo $this->plugin_name ?>_'
        });
    });
</script>
