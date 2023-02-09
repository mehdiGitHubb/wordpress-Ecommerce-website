<?php
$plugin_name=WPF::get_instance()->get_plugin_name();
$plugin_version=WPF::get_instance()->get_version();
$form = new WPF_Form($plugin_name,$plugin_version,sanitize_key($_REQUEST['slug']));
$layout = $data = array();
$option = WPF_Options::get_option($plugin_name, $plugin_version);
$forms = $option->get();
if (!empty($forms[$_REQUEST['slug']])) {
    $layout = $forms[$_REQUEST['slug']];
    $data = $layout['data'];
    $layout = $layout['layout'];
}
$categories = get_terms($_REQUEST['tax'], array('hide_empty' => false));
$languages=WPF_Utils::get_all_languages();
$type = str_replace('product_','',$_REQUEST['tax']);
$module = empty($layout) || !is_array($layout['wpf_'.$type])?array():$layout['wpf_'.$type];
?>
<?php foreach ($categories as $cat): ?>
    <li class="tf_clearfix">
        <div class="wpf_color_wrapper">
            <label class="wpf_color_name" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color"><?php echo $cat->name ?></label>
        </div>
        <div class="wpf_color_options_wrap">
            <label class="wpf_color_wrap" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_bg">
                <span><?php _e('Background', 'wpf') ?></span>
                <input class="wpf_color_picker" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_bg" name="[<?php echo $type ?>][color_bg_<?php echo $cat->term_id ?>]" <?php if (!empty($module['color_bg_' . $cat->term_id])): ?>data-value="<?php echo $module['color_bg_' . $cat->term_id] ?>"<?php endif; ?> />
            </label>
            <label class="wpf_color_wrap" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_text">
                <span><?php _e('Text Color', 'wpf') ?></span>
                <input class="wpf_color_picker" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_color_text" name="[<?php echo $type ?>][color_text_<?php echo $cat->term_id ?>]" <?php if (!empty($module['color_text_' . $cat->term_id])): ?>data-value="<?php echo $module['color_text_' . $cat->term_id] ?>"<?php endif; ?> />
            </label>
            <label class="wpf_color_wrap wpf_color_text" for="wpf_<?php echo $type ?>_text_<?php echo $cat->term_id ?>">
                <span><?php _e('Icon Text', 'wpf') ?></span>
                <?php WPF_Utils::module_language_tabs($type, $module, $languages, 'text_' . $cat->term_id); ?>
            </label>
            <label class="wpf_color_wrap wpf_background_image <?php if (!empty($module['image_bg_' . $cat->term_id])): ?> has-image <?php endif;?>" for="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg">
                <span><?php _e('Background Image', 'wpf') ?></span>
                <input class="" type="text" id="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg"
                       name="[<?php echo $type ?>][image_bg_<?php echo $cat->term_id ?>]"
                       name="wpf_<?php echo $type ?>_<?php echo $cat->term_id ?>_image_bg"
                       <?php if (!empty($module['image_bg_' . $cat->term_id])): ?>value="<?php echo $module['image_bg_' . $cat->term_id] ?>"<?php endif; ?> />
                <button class="open_media_uploader_image button-link "><?php esc_attr_e( 'Select Image', 'wpf' ); ?></button>
                <div class="image-area">
                    <img class="preview-image-wraper" src="<?php echo $module['image_bg_' . $cat->term_id] ?>" alt="">
                    <i class="remove-background ti-close"></i>
                </div>
            </label>
        </div>
    </li>
<?php endforeach; ?>