<?php
/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/admin/partials
 */
?>
<?php
global $post_type;
$message =  __('Import will overwrite all existing templates. Press OK to continue, Cancel to stop.', 'wpf') ;
$extensions = array(
    array(
        'title' => __('Json file', 'wpf'),
        'extensions' => "json"
    ),
    array(
        'title' => __('Archive file', 'wpf'),
        'extensions' => "zip"
    )
);
// place js config array for plupload
$plupload_init = array(
    'runtimes' => 'html5,silverlight,flash,html4',
    'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
    'container' => 'plupload-upload-ui', // will be adjusted per uploader
    'file_data_name' => 'async-upload', // will be adjusted per uploader
    'multiple_queues' => true,
    'max_file_size' => wp_max_upload_size() . 'b',
    'url' => admin_url('admin-ajax.php'),
    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters' => array(array('title' => __('Allowed Files','wpf'), 'extensions' => '*')),
    'multipart' => true,
    'urlstream_upload' => true,
    'multi_selection' => false, // will be added per uploader
    // additional post data to send to our ajax hook
    'multipart_params' => array(
        '_ajax_nonce' => "", // will be added per uploader
        'action' => 'plupload_action', // the ajax action name
        'imgid' => 0 // will be added per uploader
    )
);
?>
<form method="post" action="" id="<?php echo $this->plugin_name ?>-import-form" enctype="multipart/form-data" data-plupload="<?php echo esc_attr( wp_json_encode( $plupload_init ) ); ?>">
    <input type="hidden" value="wpf_import_file" name="action" />
    <input type="hidden" value="<?php echo wp_create_nonce($this->plugin_name . '_import_file') ?>" name="nonce" />
    <div class="<?php echo $this->plugin_name ?>_wait"></div>
    <div class="<?php echo $this->plugin_name ?>_error"></div>
    <a data-formats='<?php echo wp_json_encode($extensions) ?>' data-name="import" data-confirm="<?php echo $message ?>" id="<?php echo $this->plugin_name ?>-import-btn" class="<?php echo $this->plugin_name ?>-file-btn"><?php _e('Import', 'wpf') ?></a>
</form>