<?php   
    $post_id = Themify_Builder::$builder_active_id;
    $is_admin = is_admin();
    $post_id = empty($post_id) && true === $is_admin && !empty($_GET['post']) ? $_GET['post'] : $post_id;
    $eye= themify_get_icon('eye','ti');
    $edit=themify_get_icon('pencil','ti');
    $clipboard=themify_get_icon('clipboard','ti');
    $files=themify_get_icon('files','ti');
    $brush=themify_get_icon('brush','ti');
    $settings=themify_get_icon('settings','ti');
    $import=themify_get_icon('import','ti');
    $export=themify_get_icon('export','ti');
    $window=themify_get_icon('new-window','ti');
    $move=themify_get_icon('move','ti');
    $more=themify_get_icon('more','ti');
    $save=themify_get_icon('save','ti');
    $duplicate=themify_get_icon('layers','ti');
    $gs=themify_get_icon('brush-alt','ti');
    $min= themify_is_minify_enabled()?'.min':'';
    $module_categories = array(
        'general' => array(
            'label' => __( 'General', 'themify' ),
            'active' => true
        ),
        'addon' => array(
            'label' => __( 'Addons', 'themify' ),
            'active' => true
        ),
        'site' => array(
            'label' => __( 'Site', 'themify' ),
            'active' => true
        )
    );

$usedIcons = array('link',  'angle-up', 'layers-alt', 'check', 'star', 'folder','alert','info','split-h','split-v','palette');
foreach ($usedIcons as $icon) {
    themify_get_icon($icon, 'ti'); //used icons
}
$usedIcons = null;
$module_categories = apply_filters( 'themify_module_categories', $module_categories );

$breakpoints = array('desktop' => '') + themify_get_breakpoints();
$isGsPost=Themify_Global_Styles::$isGlobalEditPage===true?' gs_post':'';
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/gs'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/toolbar'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/action-bar'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/panel'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/drop'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/drag'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/lightbox'.$min.'.js');
Themify_Enqueue_Assets::addPreLoadJs(THEMIFY_BUILDER_URI . '/js/editor/modules/undomanager'.$min.'.js');
?>
<div id="tb_main_toolbar_root" class="tf_w tf_hide">
    <template shadowroot="open">
	<style id="tf_base">
	    <?php echo file_get_contents(THEMIFY_DIR . '/css/base.min.css'); ?>
	</style>
        <style id="module_combine_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR . '/css/editor/modules/combine'.$min.'.css'); ?>
        </style>
        <style id="module_toolbar_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR . '/css/editor/modules/toolbar'.$min.'.css'); ?>
        </style>
        <?php if($is_admin):?>
            <style id="backend_module_toolbar_style">
                <?php echo file_get_contents(THEMIFY_BUILDER_DIR . '/css/editor/backend/modules/toolbar'.$min.'.css'); ?>
            </style>
        <?php endif;?>
        <?php do_action('tb_toolbar_styles')?>
        <div id="toolbar" class="builder-breakpoint-desktop flex<?php echo $isGsPost?> tf_w">
            <button type="button" class="plus tf_plus_icon tf_rel"></button>
            <ul class="menu flex">
                <?php if ($is_admin === false): ?>
                    <li class="zoom_menu">
                        <a href="javascript:;" class="zoom zoom_toggle tb_tooltip btn" data-zoom="100" tabindex="-1">
                            <?php echo themify_get_icon('zoom-in', 'ti') ?>
                            <span><?php _e('Zoom', 'themify'); ?></span>
                        </a>
                        <ul class="submenu tf_abs_t tf_hide">
                            <li><button type="button" class="zoom btn" data-zoom="50"><?php _e('50%', 'themify'); ?></button></li>
                            <li><button type="button" class="zoom btn" data-zoom="75"><?php _e('75%', 'themify'); ?></button></li>
                            <li><button type="button" class="zoom btn" data-zoom="100"><?php _e('100%', 'themify'); ?></button></li>
                        </ul>
                    </li>
		    <li class="divider"></li>
		    <li><button type="button" class="tb_tooltip preview btn"><?php echo themify_get_icon('layout-media-center-alt', 'ti') ?><span><?php _e('Preview', 'themify'); ?></span></button></li>
		    <li class="divider"></li>
		<?php endif;?>
                <?php
                $breakpoints['tablet'][0] = $breakpoints['tablet_landscape'][1];
                $popular_devices = Themify_Builder_Model::get_popular_devices();
                $cus_css = get_post_meta($post_id, 'tbp_custom_css', true);
                ?>
                <li class="breakpoint_switcher">
                    <a href="javascript:;" class="tb_tooltip compact compact_switcher breakpoint-desktop btn tf_hide" tabindex="-1"><?php echo themify_get_icon('desktop', 'ti') ?>
                        <span><?php _e('Desktop', 'themify'); ?></span>
                    </a>
                    <ul class="flex" tabindex="-1">
                        <?php foreach ($breakpoints as $b => $v): ?>
                            <li class="<?php echo strtolower($b); ?>">
                                <a href="javascript:;" class="tb_tooltip breakpoint_switch btn breakpoint-<?php echo $b ?>" tabindex="-1"><?php echo themify_get_icon(($b === 'tablet_landscape' ? 'tablet' : $b), 'ti') ?>
                                    <span><?php echo $b === 'tablet_landscape' ? __('Tablet Landscape', 'themify') : ($b === 'tablet' ? __('Tablet Portrait', 'themify') : ucfirst($b)); ?></span>
                                </a>
                                <?php if ($is_admin === false && 'desktop' !== $b): ?>
                                    <ul class="submenu devices tf_hide tf_abs_t" tabindex="-1">
                                        <li data-width="<?php echo 'mobile' === $b ? $breakpoints[$b] : $breakpoints[$b][1]?>" data-height="<?php echo 'mobile' === $b ? '' : $breakpoints[$b][0]?>">
                                            <?php  _e('Breakpoint Settings', 'themify')?>
                                        </li>
                                        <?php $devices = 'mobile' === $b ? $popular_devices['mobile'] : $popular_devices['tablet']; ?>
                                        <?php foreach ($devices as $device => $size): ?>
                                            <?php $size = 'tablet_landscape' === $b ? array_reverse($size) : $size; ?>
                                            <li data-width="<?php echo $size[0]?>" data-height="<?php echo $size[1]?>">
                                                <?php echo $device?> (<?php echo $size[0],'X',$size[1]?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="divider"></li>
                <li class="menu_undo">
                    <a href="javascript:;" class="tb_tooltip compact compact_undo disabled tf_hide btn" tabindex="-1"><?php echo themify_get_icon('back-left', 'ti') ?><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></a>
                    <ul class="flex" tabindex="-1">
                        <li><button type="button" class="tb_tooltip undo_redo undo disabled btn"><?php echo themify_get_icon('back-left', 'ti') ?><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></button></li>
                        <li><button type="button" class="tb_tooltip undo_redo redo disabled btn"><?php echo themify_get_icon('back-right', 'ti') ?><span><?php _e('Redo (CTRL+SHIFT+Z)', 'themify'); ?></span></button></li>
                    </ul>
                </li>
                <li class="divider"></li>
                <li class="import">
                    <a href="javascript:;" class="compact tb_tooltip btn tf_hide" tabindex="-1"><?php echo $import ?><span><?php _e('Import', 'themify'); ?></span></a>
                    <ul class="flex">
                        <li>
                            <a href="javascript:;" class="tb_tooltip btn" tabindex="-1"><?php echo $import ?><span><?php _e('Import', 'themify'); ?></span></a>
                            <ul class="submenu tf_abs_t tf_hide">
                                <li><button type="button" data-type="file" class="btn"><?php _e('Import From File', 'themify'); ?></button></li>
                                <li><button type="button" data-type="page" class="btn"><?php _e('Import From Page', 'themify'); ?></button></li>
                                <li><button type="button" data-type="post" class="btn"><?php _e('Import From Post', 'themify'); ?></button></li>
                            </ul>
                        </li>
                        <li class="export">
                            <button type="button" class="tb_tooltip btn">
                                <?php echo $export ?>
                                <span><?php _e('Export', 'themify'); ?></span>
                            </button>
                        </li>
                    </ul>
                </li>
                <li class="divider"></li>
                <li class="layout">
                    <a href="javascript:;" class="tb_tooltip btn" tabindex="-1"><?php echo themify_get_icon('layout', 'ti') ?><span><?php _e('Layouts', 'themify'); ?></span></a>
                    <ul class="submenu tf_abs_t tf_hide">
                        <li><button type="button" class="load_layout btn"><?php _e('Load Layout', 'themify'); ?></button></li>
                        <li><button type="button" class="save_layout btn"><?php _e('Save as Layout', 'themify'); ?></button></li>
                    </ul>
                </li>
                <li class="divider"></li>
                <li><button type="button" class="tb_tooltip duplicate btn"><?php echo $duplicate?><span><?php _e('Duplicate this page', 'themify'); ?></span></button></li>
                <li class="divider"></li>
                <li><button type="button" class="tb_tooltip custom_css btn<?php echo trim($cus_css) !== '' ? ' active' : ''; ?>"><span><?php _e('Custom CSS', 'themify'); ?></span><?php _e('CSS', 'themify'); ?></button></li>
                <li class="divider"></li>
                <li class="mode">
                    <a href="javascript:;" class="tb_tooltip btn" tabindex="-1"><?php echo themify_get_icon('panel', 'ti') ?><span><?php _e('Interface Options', 'themify'); ?></span></a>
                    <ul class="submenu tf_abs_t tf_hide">
                        <li class="switch-wrapper right_click_wrap">
                            <div class="tb_switcher">
                                <label>
                                    <input type="checkbox" class="tb_checkbox toggle_switch right_click_mode tf_hide" checked="checked">
                                    <div data-on="<?php _e('Right Click', 'themify') ?>" data-off="<?php _e('Right Click', 'themify') ?>" class="switch_label"></div>
                                </label>
                            </div>
                        </li>
                        <?php if ($is_admin===false): ?>
                            <li class="switch-wrapper">
                                <div class="tb_switcher">
                                    <label>
                                        <input type="checkbox" class="tb_checkbox toggle_switch padding_dragging_mode tf_hide" checked="checked">
                                        <div data-on="<?php _e('Padding Dragging', 'themify') ?>" data-off="<?php _e('Padding Dragging', 'themify') ?>" class="switch_label"></div>
                                    </label>
                                </div>
                            </li>
                            <li class="switch-wrapper">
                                <div class="tb_switcher">
                                    <label>
                                        <input type="checkbox" class="tb_checkbox toggle_switch inline_editor_mode tf_hide" checked="checked">
                                        <div data-on="<?php _e('Inline Editor', 'themify') ?>" data-off="<?php _e('Inline Editor', 'themify') ?>" class="switch_label"></div>
                                    </label>
                                </div>
                            </li>
                        <?php endif; ?>
                        <li class="switch-wrapper">
                            <div class="tb_switcher">
                                <label>
                                    <input type="checkbox" class="tb_checkbox toggle_switch dark_mode tf_hide">
                                    <div data-on="<?php _e('Dark Mode', 'themify') ?>" data-off="<?php _e('Dark Mode', 'themify') ?>" class="switch_label"></div>
                                </label>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="divider"></li>
		<?php if($isGsPost==='' && false):?>
		    <li><button type="button" class="tb_tooltip tree btn"><?php echo themify_get_icon('view-list-alt', 'ti') ?><span><?php _e('Tree View','themify')?></span></button></li>
		    <li class="divider"></li>
		<?php endif; ?>
                <li><button type="button" class="tb_tooltip help btn"><?php echo themify_get_icon('help', 'ti') ?><span><?php _e('Help', 'themify'); ?></span></button></li>
            </ul>

            <div class="save_wrap">
                <?php if (get_post_status($post_id) !== 'auto-draft'): ?>
                    <?php if ($is_admin === true): ?>
                        <a href="<?php echo get_permalink($post_id) ?>#builder_active" id="frontend" class="switch"><?php echo themify_get_icon('arrow-right', 'ti') ?><span><?php _e('Frontend', 'themify'); ?></span></a>
                    <?php else: ?>
                        <a href="<?php echo get_edit_post_link($post_id); ?>#builder_active" id="backend" class="switch"><?php echo themify_get_icon('arrow-left', 'ti') ?><span><?php esc_html_e('Backend', 'themify'); ?></span></a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (false === $is_admin): ?>
                    <button type="button" class="tb_tooltip tf_close" title="<?php _e('ESC', 'themify') ?>"><span><?php _e('Close', 'themify'); ?></span></button>
                <?php endif; ?>
                <div class="save_btn_wrap tf_rel">
                    <button type="button" class="save save_btn" title="<?php _e('Ctrl + S', 'themify') ?>"><?php _e('Save', 'themify'); ?></button>
                    <div tabindex="-1" class="revision_btn">
                        <?php echo themify_get_icon('angle-down', 'ti') ?>
                        <ul class="submenu tf_abs_t tf_hide">
			    <?php if(Themify_Builder_Revisions::is_revision_enabled($post_id)):?>
				<li><button type="button" class="save_revision btn"><?php _e('Save as Revision', 'themify'); ?></button></li>
			    <?php endif;?>
                            <li><button type="button" class="load_revision btn"><?php _e('Load Revision', 'themify'); ?></button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Global Styles breadcrumb -->
            <?php 
		unset($cus_css,$popular_devices);
		if ($is_admin === false) {
		    Themify_Global_Styles::breadcrumb();
		}
            ?>
            <!-- /Global Styles breadcrumb -->
        </div>
    </template>
</div>

<template id="tmpl-builder_lightbox">
    <?php //create fix overlay on top iframe,mouse position will be always on top iframe on resizing  ?>
    <div id="tb_lightbox_parent" class="themify_builder builder-lightbox <?php echo themify_is_themify_theme()?'is-themify-theme':'is-not-themify-theme'?> <?php echo $isGsPost?> tf_text_dec tf_box tf_hide">
        <style id="module_breadcrumbs_style">
             <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/breadcrumbs'.$min.'.css');?>
        </style>
        <div class="tb_action_breadcrumb tf_rel tf_left"></div>
        <div class="tb_lightbox_top_bar tf_clearfix">
            <ul class="tb_options_tab tf_clearfix"></ul>
            <div class="tb_lightbox_actions">
                <button type="button" class="builder_cancel_docked_mode tf_hide"><?php echo $window?></button>
                <div class="tb_close_lightbox tf_close"><span><?php _e('Cancel', 'themify') ?></span></div>
                <span class="tb_lightbox_actions_wrap"></span>	
            </div>
        </div>
        <div id="tb_lightbox_container" class="tf_scrollbar tf_overflow tf_box"></div>
        <div class="tb_resizable tb_resizable_st" data-axis="-y"></div>
        <div class="tb_resizable tb_resizable_e" data-axis="x"></div>
        <div class="tb_resizable tb_resizable_s" data-axis="y"></div>
        <div class="tb_resizable tb_resizable_w" data-axis="w"></div>
        <div class="tb_resizable tb_resizable_se" data-axis="se"></div>
        <div class="tb_resizable tb_resizable_we" data-axis="sw"></div>
        <div class="tb_resizable tb_resizable_nw" data-axis="nw"></div>
        <div class="tb_resizable tb_resizable_ne" data-axis="ne"></div>
    </div>
</template>
<div id="tb_lite_lightbox_root" class="tf_hide tf_w tf_h" role="dialog">
    <template shadowroot="open">
        <style id="module_lite_lightbox_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/lite-lightbox'.$min.'.css');?>
        </style>
        <div id="wrapper" class="tf_w tf_h<?php echo $isGsPost?>"></div>
    </template>
</div>
<template id="tmpl-builder_row_item">
    <?php if($is_admin===true):?>
        <div class="page-break-overlay"></div>
    <?php endif;?>
    <div class="tb_visibility_hint tf_overflow tf_abs_t tf_hide"><?php echo $eye;?></div>
    <div class="tb_row_info tf_overflow tf_abs_t">
        <span class="tb_row_id"></span>
        <span class="tb_row_anchor"></span>
    </div>
    <div class="row_inner tf_box tf_w tf_rel"></div>
    <div class="tb_action_wrap tb_row_action tf_abs_t tf_box tf_clear tf_hide"></div>
</template>
<template id="tmpl-builder_subrow_item">
	<div class="module_subrow themify_builder_sub_row tf_w tf_clearfix">
		<div class="tb_visibility_hint tf_overflow tf_abs_t tf_hide"><?php echo $eye;?></div>
		<div class="subrow_inner tf_box tf_w"></div>
		<div class="tb_action_wrap tb_subrow_action tf_abs_t tf_box tf_hide"></div>
	</div>
</template>
<template id="tmpl-builder_column_item">
    <div class="tb_col_side tb_col_side_left tf_abs_t tf_box tf_hide"></div>
    <div class="tb_col_side tb_col_side_right tf_abs_t tf_box tf_hide"></div>
    <div class="tb_action_wrap tb_column_action tf_abs_t tf_box tf_hide"></div>
    <div class="tb_grid_drag tb_drag_right tf_abs_t tf_h tf_hide" draggable></div>
    <div class="tb_grid_drag tb_drag_left tf_abs_t tf_h tf_hide" draggable></div>
    <div class="tb_holder tf_box tf_rel tf_w"></div>
    <div class="tf_plus_icon tb_column_btn_plus tb_disable_sorting tf_rel"></div>
</template>
<template id="tmpl-builder_row_action">
    <style id="module_form_fields_style">
        <?php echo str_replace('img/',THEMIFY_BUILDER_URI.'/css/editor/img/',file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/form-fields'.$min.'.css'))?>
    </style>
    <style id="module_row_grids_style">
        <?php echo str_replace('img/',THEMIFY_BUILDER_URI.'/css/editor/img/',file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/row-grids'.$min.'.css'));?>
    </style>
    <div class="wrap<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?>">
        <ul class="dropdown row tf_box tf_clear">
            <li data-action="move" class="tb_move">
		<?php echo $move?>
		<div class="themify_tooltip"><?php _e('Move Row', 'themify') ?></div>
	    </li>
	    <li class="up_down tf_hide">
		<ul class="tf_h">
		    <li data-action="up" class="arr up tf_h">
			<div class="themify_tooltip"><?php _e('Move Up', 'themify') ?></div>
		    </li>
		    <li data-action="down" class="arr down tf_h">
			<div class="themify_tooltip"><?php _e('Move Down', 'themify') ?></div>
		    </li>
		</ul>
	    </li>
            <li class="tb_row_settings" data-href="options" data-action="edit">
                <?php echo $settings; ?>
                <div class="themify_tooltip"><?php _e('Options', 'themify') ?></div>
            </li>
            <li data-action="styling">
                <?php echo $brush; ?>
                <div class="themify_tooltip"><?php _e('Styling', 'themify') ?></div>
            </li>
            <li data-action="duplicate">
                <?php echo $duplicate; ?>
                <div class="themify_tooltip"><?php _e('Duplicate', 'themify') ?></div>
            </li>
            <li data-action="delete" class="tf_close">
                <div class="themify_tooltip"><?php _e('Delete', 'themify') ?></div>
            </li>
            <li class="more">
                <?php echo $more; ?>
                <ul class="menu more_menu tf_box tf_clear tf_hide">
                    <li data-action="save"><?php echo $save, __('Save', 'themify') ?></li>
                    <li data-action="export"><?php echo $export,__('Export', 'themify') ?></li>
                    <li data-action="import"><?php echo $import,__('Import', 'themify') ?></li>
                    <li data-action="copy"><?php echo $files,__('Copy', 'themify') ?></li>
                    <li class="inner_more">
                        <?php echo $clipboard,__('Paste', 'themify') ?>
                        <ul class="menu inner_menu tf_box tf_clear tf_hide">
                            <li data-action="paste"><?php _e('Paste', 'themify') ?></li>
                            <li data-action="paste" class="style"><?php _e('Paste Styling', 'themify') ?></li>
                        </ul>
                    </li>
                    <li data-action="visibility">
                        <?php echo $eye,__('Visibility', 'themify') ?>
                    </li>
                </ul>
            </li>
        </ul> 
        <div id="options" class="tab tf_abs_t tf_box tf_hide">
            <span class="expand" data-action="edit">
                <?php echo $window?>
		<span class="themify_tooltip"><?php _e('Edit Row', 'themify') ?></span>
            </span>
            <ul class="row_menu grid_layout">
                <li class="selected" data-href="grid"><?php _e('Grid', 'themify') ?></li>
                <li data-href="row_options"><?php _e('Row Options', 'themify') ?></li>
            </ul>
            <div id="grid" class="tf_hide selected"></div>
            <div id="row_options" class="tf_hide"></div>
        </div>
    </div>
</template>
<template id="tmpl-builder_column_action">
    <ul class="dropdown column<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?> tf_box tf_clear">
        <li class="more">
            <?php echo $more; ?>
            <ul class="menu more_menu tf_box tf_clear tf_hide">
                <li data-action="export">
                    <?php echo $export, __('Export', 'themify'); ?>
                </li>
                <li data-action="import">
                    <?php echo $import, __('Import', 'themify'); ?>
                </li>
                <li data-action="copy">
                    <?php echo $files, __('Copy', 'themify'); ?>
                </li>
                <li class="inner_more">
                    <?php echo $clipboard, __('Paste', 'themify'); ?>
                    <ul class="menu inner_menu tf_box tf_clear tf_hide">
                        <li data-action="paste"><?php _e('Paste', 'themify') ?></li>
                        <li data-action="paste" class="style"><?php _e('Paste Styling', 'themify') ?></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li data-action="delete" class="tf_close">
            <div class="themify_tooltip"><?php _e('Delete', 'themify') ?></div>
        </li>
        <li data-action="styling">
            <?php echo $brush; ?>
            <div class="themify_tooltip"><?php _e('Styling', 'themify') ?></div>
        </li>
	<li class="edit" data-action="edit">
            <?php echo $settings; ?>
            <div class="themify_tooltip"><?php _e('Edit', 'themify') ?></div>
        </li>
	<li data-action="add_col" class="plus">
	    <div class="themify_tooltip"><?php _e('Add Column', 'themify') ?></div>
	</li>
        <li data-action="move" class="tb_move">
            <?php echo $move?>
	    <div class="themify_tooltip"><?php _e('Move Column', 'themify') ?></div>
        </li>
    </ul>
</template>
<template id="tmpl-builder_subrow_action">
    <div class="wrap<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?>">
        <ul class="dropdown subrow tf_box tf_clear">
            <li data-action="move" class="tb_move">
		<?php echo $move; ?>
		<div class="themify_tooltip"><?php _e('Move Subrow', 'themify') ?></div>
	    </li>
            <li class="tb_row_settings" data-href="grid" data-action="edit">
                <?php echo $settings; ?>
                <div class="themify_tooltip"><?php _e('Options', 'themify') ?></div>
            </li>
            <li data-action="styling">
                <?php echo $brush; ?>
                <div class="themify_tooltip"><?php _e('Styling', 'themify') ?></div>
            </li>
            <li data-action="duplicate">
                <?php echo $duplicate; ?>
                <div class="themify_tooltip"><?php _e('Duplicate', 'themify') ?></div>
            </li>
            <li data-action="delete" class="tf_close">
                <div class="themify_tooltip"><?php _e('Delete', 'themify') ?></div>
            </li>
            <li class="more">
                <?php echo $more; ?>
                <ul class="menu more_menu tf_box tf_clear tf_hide">
                    <li data-action="export">
                        <?php echo $export, __('Export', 'themify'); ?>
                    </li>
                    <li data-action="import">
                        <?php echo $import, __('Import', 'themify'); ?>
                    </li>
                    <li data-action="copy">
                        <?php echo $files, __('Copy', 'themify'); ?>
                    </li>
                    <li class="inner_more">
                        <?php echo $clipboard, __('Paste', 'themify'); ?>
                        <ul class="menu inner_menu tf_box tf_clear tf_hide">
                            <li data-action="paste"><?php _e('Paste', 'themify') ?></li>
                            <li data-action="paste" class="style"><?php _e('Paste Styling', 'themify') ?></li>
                        </ul>
                    </li>
                    <li data-action="visibility">
                        <?php echo $eye, __('Visibility', 'themify'); ?>
                    </li>
                </ul>
            </li>
        </ul>
        <div id="grid" class="tab tf_abs_t tf_box tf_hide">
            <span class="expand" data-action="edit">
                <?php echo $window?>
		<span class="themify_tooltip"><?php _e('Edit Subrow', 'themify') ?></span>
            </span>
        </div>
    </div>
</template>
<template id="tmpl-builder_module_action">
    <style id="action_bar_style">
        <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/action-bar'.$min.'.css');?>
    </style>
    <?php do_action('tb_bar_styles')?>
    <ul class="dropdown module<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?> tf_box tf_clear">
        <li class="edit" data-action="edit">
            <?php echo $edit; ?>
            <div class="themify_tooltip"><?php _e('Edit', 'themify') ?></div>
        </li>
	<?php if($is_admin===false):?>
	    <li class="swap tf_hide" data-action="swap">
		<?php echo $settings; ?>
		<div class="themify_tooltip"><?php _e('Options', 'themify') ?></div>
	    </li>
	<?php endif;?>
        <li data-action="styling">
            <?php echo $brush; ?>
            <div class="themify_tooltip"><?php _e('Styling', 'themify') ?></div>
        </li>
        <li data-action="duplicate">
            <?php echo $duplicate; ?>
            <div class="themify_tooltip"><?php _e('Duplicate', 'themify') ?></div>
        </li>
        <li data-action="delete" class="tf_close">
            <div class="themify_tooltip"><?php _e('Delete', 'themify') ?></div>
        </li>
        <li class="more">
            <?php echo $more; ?>
            <ul class="menu more_menu tf_box tf_clear tf_hide">
                <li data-action="save"><?php echo $save,__('Save', 'themify') ?></li>
                <li data-action="export">
                    <?php echo $export,__('Export', 'themify') ?>
                </li>
                <li data-action="import">
                    <?php echo $import,__('Import', 'themify') ?>
                </li>
                <li data-action="copy">
                    <?php echo $files,__('Copy', 'themify') ?>
                </li>
                <li class="inner_more">
                    <?php echo $clipboard,__('Paste', 'themify') ?>
                    <ul class="menu inner_menu tf_box tf_clear tf_hide">
                        <li data-action="paste"><?php _e('Paste', 'themify') ?></li>
                        <li data-action="paste" class="style"><?php _e('Paste Styling', 'themify') ?></li>
                    </ul>
                </li>
                <li data-action="visibility">
                    <?php echo $eye,__('Visibility', 'themify') ?>
                </li>
            </ul>
        </li>
    </ul>
</template>
<template id="tmpl-builder_grid_list">
    <?php
    $gridSettings=Themify_Builder_Model::get_grid_settings();
    $is_fullpage=function_exists('themify_theme_is_fullpage_scroll') && themify_theme_is_fullpage_scroll()?'center':'start';
    ?>
    <ul class="breakpoints grid_layout" data-col="breakpoint">
        <?php foreach ($breakpoints as $b => $v): ?>
            <li data-id="<?php echo $b ?>" class="tab_<?php echo $b ?>"><?php echo themify_get_icon(($b === 'tablet_landscape' ? 'tablet' : $b), 'ti') ?>
                <div class="themify_tooltip"><?php echo $b === 'tablet_landscape' ? __('Tablet Landscape', 'themify') : ucfirst($b); ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
    <ul class="grid_list" data-col="grid">
        <?php if ($b !== 'desktop'): ?>
            <li class="grid cauto selected tf_hide" data-grid="auto">
                <div class="themify_tooltip"><?php _e('Use default css', 'themify') ?></div>
            </li>
        <?php endif; ?>
        <?php foreach ($gridSettings['grid'] as $li): ?>
            <li class="tb<?php echo is_string($li['grid']) ? (substr_count($li['grid'], '_') + 1) : $li['grid']; ?> grid c<?php echo $li['grid'] ?>" data-grid="<?php echo $li['grid']; ?>">

                <div class="themify_tooltip"><?php echo $li['name']; ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="aligment_wrap grid_layout">
        <div class="left">
            <ul class="alignment grid_layout" data-col="alignment">
                <?php foreach ($gridSettings['alignment'] as $v): ?>
                    <li class="<?php echo $v['value'] ?><?php if ($v['value'] === $is_fullpage):?> selected<?php endif;?>" data-value="<?php echo $v['value'] ?>">
                        <div class="themify_tooltip"><?php echo $v['name']; ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
	    <div class="auto_dir grid_layout">
		<ul class="auto_height grid_layout" data-col="autoHeight">
		    <?php foreach ($gridSettings['height'] as $v): ?>
			<li class="<?php echo $v['img'] ?><?php if ($v['value'] === -1):?> selected<?php endif;?>" data-value="<?php echo $v['value']; ?>">
			    <div class="themify_tooltip"><?php echo $v['name']; ?></div>
			</li>
		    <?php endforeach; ?>
		</ul>
		<ul class="direction" data-col="direction">
		    <li class="reverse">
			<div class="themify_tooltip"><?php _e('Reverse', 'themify') ?></div>
		    </li>
		</ul>
	    </div>
        </div>
        <div class="right tb_field">
            <ul class="gutter grid_layout" data-col="gutter">
                <?php foreach ($gridSettings['gutter'] as $v): ?>
                    <li class="<?php echo $v['value'] ?><?php if ($v['value'] === 'gutter'):?> selected<?php endif;?>" data-value="<?php echo $v['value']; ?>">
                        <div class="themify_tooltip"><?php echo $v['name']; ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="range_wrap grid_layout">
                <input type="range" id="slider" min="0" step=".1">
                <div id="range_holder" class="tf_rel">
                    <input type="number" id="range" class="tb_range" min="0">
                    <div class="selectwrapper noborder tf_inline_b tf_vmiddle tf_rel">
                        <select id="range_unit">
                            <option value="%">%</option>
                            <option value="em">em</option>
                            <option value="px">px</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php unset($gridSettings,$is_fullpage,$breakpoints);?>
</template>

<template id="tmpl-last_row_add_btn">
        <style id="module_last_row_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/last-row'.$min.'.css');?>
        </style>
        <div id="container" tabindex="-1" class="tf_overflow tf_rel tf_box tf_w<?php echo $isGsPost?>">
            <a href="javascript:;" class="add_btn tf_textc tf_text_dec">+</a>
        </div>
</template>
<template id="tmpl-last_row_expand">
    <div class="grids tf_w tf_opacity tf_hidden" tabindex="-1">
        <?php for($i=1;$i<7;++$i):?>
            <div class="tb_grid tf_box" data-slug="<?php echo $i?>" title="<?php printf(_n('%s Col','%s Cols',$i,'themify'),$i)?>">
                <div class="tb_grid_title tb_grid_<?php echo $i?> tf_w tf_h">
                    <?php for($j=0;$j<$i;++$j):?>
                        <span></span>
                    <?php endfor;?>
                </div>
            </div>
        <?php endfor;?>
        <div class="block">
           <span class="plus"><?php _e('Blocks','themify'); ?></span>
        </div>
    </div>
</template>
<template id="tb_global_styles_root">
        <style id="module_gs_form_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/gs-form'.$min.'.css');?>
        </style>
        <div id="container" class="empty tf_w<?php echo $isGsPost?>" tabindex="-1">
            <div class="selected_wrap tf_scrollbar tf_hide tf_box" tabindex="-1"></div>
            <div class="icon_wrap tf_w" tabindex="-1">
                <div class="icon tf_textc" tabindex="-1">
                    <?php echo $gs?>
                    <span class="tooltip tf_box tf_hide"><?php _e('Global Styles','themify') ?></span>
                </div>
                <ul class="actions dropdown tf_opacity tf_hidden" tabindex="-1">
                    <li data-action="insert" tabindex="-1">
                        <?php _e('Insert Global Style','themify') ?>
                    </li>
                    <li data-action="save"><?php _e('Save as Global Style','themify') ?></li>
                </ul>
                <div class="form dropdown tf_opacity" tabindex="-1">
                    <div class="tf_loader tf_abs_c"></div>
                    <div class="header">
                        <label class="tf_rel" for="search">
                            <?php echo themify_get_icon('search','ti') ?>
                            <input type="text" id="search" class="tf_box" autocomplete="off" required pattern=".*\S.*" inputmode="search">
                            <button class="clear_search tf_close" type="button"></button>
                        </label>
                        <a class="link" href="<?php echo esc_url(admin_url( 'admin.php?page=themify-global-styles')); ?>" target="_blank">
                            <?php echo $window,__('Manage Styles', 'themify') ?>
                        </a>
                    </div>
                    <div class="list tf_scrollbar tf_overflow tf_rel tf_w">
                        <div class="no_gs tf_textc"><?php _e('No Global Styles found.', 'themify') ?></div>
                        <div class="reload tf_abs_c tf_hide" title="<?php _e('Load More','themify')?>"><?php echo themify_get_icon('reload','ti') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overlay tf_abs_t tf_textc tf_box tf_w tf_h">
            <p class="tf_box"><?php  _e('This module is using a Global Style. Adding styling to this module will override the Global Style. Click here to add styling.', 'themify')?></p>
        </div>
</template>
<div id="tb_builder_right_click_root" class="tf_hide tf_abs_t">
    <template shadowroot="open">
        <div id="menu"<?php if($is_admin===true || $isGsPost!==''):?> class="<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?>"<?php endif;?>>
            <div id="tb_inline_gs"></div>
            <div class="tb_action_breadcrumb tf_rel tf_left"></div>
            <ul class="tf_box">
                <li class="name"></li>
                <li data-action="undo" class="undo  not_multi">
                    <?php echo themify_get_icon('back-left','ti')?>
                    <span><?php echo _e('Undo','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+Z','themify')?></span>
                </li>
                <li data-action="redo" class="redo not_multi">
                    <?php echo themify_get_icon('back-right','ti')?>
                    <span><?php echo _e('Redo','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+Shift+Z','themify')?></span>
                </li>
                <li data-action="edit" class="not_multi">
                    <?php echo $edit?>
                    <span><?php echo _e('Edit','themify')?></span>
                </li>
                <li data-action="styling" class="not_multi">
                    <?php echo $brush?>
                    <span><?php echo _e('Style','themify')?></span>
                </li>
                <li data-action="save" class="not_multi">
                    <?php echo $save?>
                    <span><?php echo _e('Save','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+S','themify')?></span>
                </li>
                <li data-action="duplicate">
                    <?php echo $duplicate?>
                    <span><?php echo _e('Duplicate','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+D','themify')?></span>
                </li>
                <li data-action="copy" class="not_multi">
                    <?php echo $files?>
                    <span><?php echo _e('Copy','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+C','themify')?></span>
                </li>
                <li class="inner_more">
                    <?php echo $clipboard?>
                    <span><?php echo _e('Paste','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+V','themify')?></span>
                    <ul class="tf_box tf_clear tf_hide">
                        <li data-action="paste"><?php _e('Paste','themify')?></li>
                        <li data-action="paste" class="style"><?php _e('Paste Styling','themify')?></li>
                    </ul>
                </li>
                <li data-action="delete">
                    <span class="tf_close"></span>
                    <span><?php echo _e('Delete','themify')?></span>
                    <span class="help tf_right"><?php _e('Cmd+Delete','themify')?></span>
                </li>
		<?php /* temprorary disable, maybe will be removed in the future
                <li class="inner_more">
                    <?php echo $gs?>
                    <span><?php echo _e('Global Style','themify')?></span>
                    <ul class="tf_box tf_clear tf_hide">
                        <li data-action="gs_in"><?php _e('Insert','themify')?></li>
                        <li data-action="gs_r"><?php _e('Remove','themify')?></li>
                    </ul>
                </li>
		 */ ?>
                <li data-action="visibility" class="visibility not_multi">
                    <?php echo $eye?>
                    <span><?php echo _e('Visibility','themify')?></span>
                </li>
                <li data-action="reset">
                    <span class="tf_close"></span>
                    <span><?php echo _e('Reset Styling','themify')?></span>
                </li>
            </ul>
        </div>
    </template>
</div>
<div id="tb_main_panel_root" class="tf_hide">
    <template shadowroot="open">
        <style id="module_drag_grids_style">
            <?php echo str_replace('img/',THEMIFY_BUILDER_URI.'/css/editor/img/',file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/drag-grids'.$min.'.css'));?>
        </style>
        <style id="module_panel_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/panel'.$min.'.css');?>
        </style>
        <style id="module_main_panel_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/main-panel'.$min.'.css');?>
        </style>
        <?php do_action('tb_main_panel_styles')?>
        <div id="main_panel" class="panel<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?> tf_box">
            <div class="tb_resizable tb_resizable_e" data-axis="x"></div>
            <div class="tb_resizable tb_resizable_s" data-axis="y"></div>
            <div class="tb_resizable tb_resizable_st" data-axis="-y"></div>
            <div class="tb_resizable tb_resizable_w" data-axis="w"></div>
            <div class="tb_resizable tb_resizable_se" data-axis="se"></div>
            <div class="tb_resizable tb_resizable_we" data-axis="sw"></div>
            <div class="tb_resizable tb_resizable_nw" data-axis="nw"></div>
            <div class="tb_resizable tb_resizable_ne" data-axis="ne"></div>
            <div class="panel_top tf_rel">
                <button type="button" class="drag_handle tf_w tf_h tf_abs"></button>
                <button type="button" class="panel_close tf_close"></button>
                <div class="minimize"></div>
		<span class="dropdown_label tf_rel tf_box tf_hide" tabindex="-1"><?php _e('Modules', 'themify') ?></span>
		<ul class="nav_tab">
		    <li class="current" data-hide="panel_tab" data-target="panel_modules_wrap"><?php _e('Modules', 'themify') ?></li>
		    <li data-hide="panel_tab" data-target="panel_rows"><?php _e('Blocks', 'themify') ?></li>
		    <li data-hide="panel_tab" data-target="panel_library"><?php _e('Saved', 'themify') ?></li>
		</ul>
            </div>
            <div class="panel_container<?php echo apply_filters('tb_toolbar_module', '') ?> tf_h">
                <form class="panel_search_form tf_rel">
                    <input type="text" class="panel_search tf_box tf_w" inputmode="search" required pattern=".*\S.*"/>
                    <button class="clear_search tf_close" type="reset"></button>
                    <?php echo themify_get_icon('search', 'ti') ?>
                </form>
                <div class="panel_tab panel_modules_wrap tf_scrollbar tf_overflow tf_rel tf_box tf_h tf_clear tf_clearfix">
                    <div class="panel_acc tb_cat_grid tf_w">
                        <div class="panel_title"><h4><?php _e('Rows', 'themify'); ?></h4></div>
                        <div class="panel_content">
                            <ul class="grids tf_clear"> 
                                <?php for ($i = 1; $i < 7; ++$i): ?>
                                    <li>
                                        <div class="tb_grid tf_rel tf_w tf_box" data-slug="<?php echo $i ?>" draggable="true" title="<?php printf(_n('%s Col', '%s Cols', $i, 'themify'), $i) ?>">
                                            <div class="tb_grid_title tb_grid_<?php echo $i ?> tf_w tf_h">
                                                <?php for ($j = 0; $j < $i; ++$j): ?>
                                                    <span></span>
                                                <?php endfor; ?>
                                            </div>
                                            <button type="button" data-type="row" class="tf_plus_icon add_module_btn tb_disable_sorting tf_rel"></button>
                                        </div>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="page_break_module tf_w tf_rel tf_box tf_clear" draggable="true">
                                <div class="page_break_title"><?php _e('Page Break', 'themify'); ?></div>
                                <button type="button" data-type="page_break" class="tf_plus_icon add_module_btn tb_disable_sorting tf_rel"></button>
                            </div>
                        </div>
                    </div>
                    <div class="panel_acc tf_w">
                        <div class="panel_content panel_category" data-category="favorite"></div>
                    </div>
                    <?php foreach ($module_categories as $class => $category) : ?>
                        <div class="panel_acc tb_cat_<?php echo $class ?> tf_w" data-active="<?php echo $category['active'] ? 1 : 0; ?>">
                            <div class="panel_title"><h4><?php echo $category['label']; ?></h4></div>
                            <div class="panel_content panel_category" data-category="<?php echo $class; ?>"></div>
                        </div>
                    <?php endforeach;?>

                </div>
                <!-- /panel_modules_wrap -->
                <div class="panel_tab panel_rows tf_scrollbar tf_rel tf_box tf_h tf_clear tf_overflow tf_hide">
                    <div class="dropdown_wrap tf_abs_t tf_hidden tb_float_xsmall">
                        <span class="dropdown_label tf_rel tf_box" tabindex="-1"><?php _e('All', 'themify') ?></span>
                        <!-- /tb_row_cat_filter_active -->
                        <ul class="nav_tab tf_scrollbar tf_hidden">
                            <li><?php _e('All', 'themify') ?></li>
                        </ul>
                        <!-- /tb_row_cat_filter -->
                    </div>
                    <!-- /tb_row_cat_filter_wrap -->
                    <div class="predesigned_container tf_box tf_rel tf_h tf_clear">
                        <span class="tf_loader tf_abs_c"></span>
                    </div>
                    <!-- /predesigned_container -->
                </div>
                <div class="panel_tab panel_library tf_scrollbar tf_rel tf_box tf_h tf_clear tf_overflow tf_hide">
                    <span class="dropdown_label tf_rel tf_box tf_hide" tabindex="-1"><?php _e('Rows', 'themify') ?></span>
                    <ul class="nav_tab library_tab">
                        <li class="current" data-type="row" data-hide="library_item" data-target="tb_item_row"><?php _e('Rows', 'themify') ?></li>
                        <li data-type="module" data-hide="library_item" data-target="tb_item_module"><?php _e('Modules', 'themify') ?></li>
                        <li data-type="part" data-hide="library_item" data-target="tb_item_part"><?php _e('Layout Parts', 'themify') ?></li>
                    </ul>
                    <!-- /library_tab -->
                    <div class="library_container tf_textc tf_box tf_clear">
                        <span class="tf_loader tf_abs_c"></span>
                    </div>
                    <!-- /library_container -->
                </div>
                <!-- /panel_library -->
            </div>
        </div>
        <button type="button" class="docked_min tf_box tf_hide"></button>
    </template>
</div>
<div id="tb_small_panel_root" class="tf_abs_t tf_hide tf_hidden">
    <template shadowroot="open">
        <style id="module_small_panel_style">
            <?php echo file_get_contents(THEMIFY_BUILDER_DIR . '/css/editor/modules/small-panel'.$min.'.css'); ?>
        </style>
        <?php do_action('tb_small_panel_styles')?>
        <div id="small_panel" class="panel<?php if($is_admin===true):?> backend<?php endif;?><?php echo $isGsPost?> tf_textc tf_box"></div>
    </template>
</div>
<template id="tmpl-builder_module_disabled">
    <div class="tb_action_wrap tb_module_action tf_abs_t tf_box tf_hide"></div>
    <div class="tb_disabled_module module module-{{ data.slug }} tb_{{ data.element_id }}">
	<?php if($is_admin===true):?>
	    <div class="module_label tf_overflow tf_h">
		<div class="tb_img_wrap"><?php echo themify_get_icon('na','ti')?></div>
		<span class="module_name">{{ data.name }}</span>
		<em class="module_excerpt">{{ data.excerpt }}</em>
		<span class="tb_empty_msg tf_textc"><?php _e('Module doesn&rsquo;t exist','themify')?></span>
	    </div>
	<?php else:?>
	    <span class="tb_data_mod_name tf_overflow tf_textc tf_abs_t tf_hide">{{ data.slug }}</span>
	    <span class="tb_empty_msg tf_textc"><?php _e('Module doesn&rsquo;t exist','themify')?></span>
	<?php endif;?>
    </div>
</template>
<?php if($isGsPost===''):?>
    <div id="tb_tree_root" class="tf_abs_t tf_hide">
	<template shadowroot="open">
	    <div class="wrapper">
		<div class="header">
		    <button typ="button" class="minimize tf_rel"></button>
		    <span class="title"><?php _e('Tree View','themify')?></span>
		    <button typ="button" class="tf_close"></button>
		</div>
		<div class="content<?php if($is_admin===true):?> backend<?php endif;?> tf_scrollbar tf_box"></div>
		<div class="tb_resizable tb_resizable_st" data-axis="-y"></div>
		<div class="tb_resizable tb_resizable_e" data-axis="x"></div>
		<div class="tb_resizable tb_resizable_s" data-axis="y"></div>
		<div class="tb_resizable tb_resizable_w" data-axis="w"></div>
		<div class="tb_resizable tb_resizable_se" data-axis="se"></div>
		<div class="tb_resizable tb_resizable_we" data-axis="sw"></div>
		<div class="tb_resizable tb_resizable_nw" data-axis="nw"></div>
		<div class="tb_resizable tb_resizable_ne" data-axis="ne"></div>
	    </div>
	</template>
    </div>
<?php endif;?>
<div id="tb_drop_zone" class="tf_abs tf_w tf_h tf_hide">
    <div class="tb_drop_file_wrap tf_abs_c tf_box">
	<?php _e('Drop Files','themify')?>
    </div>
</div>
<?php 
$eye= $clipboard=$files=$settings=$brush=$import=$export=$window=$move=$more=$edit=$save=$gs=$module_categories=null;
$base=THEMIFY_BUILDER_DIR . '/img/row-frame/';
if(is_readable($base)){
    $frames=Themify_Builder_Model::get_frame_layout();
    foreach($frames as $fr){
    ?>
    <?php if($fr['value']!==''):?>
        <?php  
            $path=pathinfo($fr['img']);
            if($path['extension']!=='svg'){
                continue;
            }
            $f=$base.$path['filename'];
        ?>
        <script type="text/template" id="tmpl-frame_<?php echo $fr['value']?>">
            <?php echo file_get_contents($f.'.'.$path['extension']);?>
        </script>
        <script type="text/template" id="tmpl-frame_<?php echo $fr['value']?>-l">
            <?php echo file_get_contents($f.'-l.'.$path['extension']);?>
        </script>
    <?php endif;?>
    <?php 
    }
    $frames=null;
}
$base=null;