<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    WPF
 * @subpackage WPF/admin/partials
 */

$cptListTable = new WPF_List_Table($this->plugin_name, $this->version);
$cptListTable->prepare_items();
?>
<div class="wrap">

    <h2>
        <?php  _e('WooCommerce Product Filters', 'wpf'); ?>
        <a onclick="javascript:void(0);" title="<?php _e('Edit Product Filter Form','wpf')?>" class="add-new-h2 wpf_lightbox" href="<?php echo add_query_arg(array('action'=>'wpf_add','nonce'=>wp_create_nonce($this->plugin_name . '_edit')),admin_url('admin-ajax.php'))?>"><?php _e('Add new', 'wpf')?></a>
        <a onclick="javascript:void(0);" title="<?php _e('Import Product Filter Form','wpf')?>" class="add-new-h2 wpf_lightbox" href="<?php echo add_query_arg(array('action'=>'wpf_import','nonce'=>wp_create_nonce($this->plugin_name . '_import')),admin_url('admin-ajax.php'))?>"><?php _e('Import', 'wpf')?></a>
    </h2>

    <?php settings_errors($this->plugin_name . '_notices'); ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="wpf-filter" method="post">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>"/>
        <!-- Now we can render the completed list table -->
        <?php $cptListTable->display() ?>
    </form>

</div>
