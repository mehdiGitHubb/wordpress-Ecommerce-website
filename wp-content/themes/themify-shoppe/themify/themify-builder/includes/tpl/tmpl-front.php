<template id="tmpl-small_toolbar">
    <?php do_action('tb_small_toolbar_styles') ?>
    <div id="toolbar" class="tb_disable_sorting flex tf_w">
        <ul class="menu flex">
            <li class="menu_undo">
                <a href="javascript:;" tabindex="-1" class="tb_tooltip compact btn compact_undo disabled tf_hide"><?php echo themify_get_icon('back-left', 'ti') ?><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></a>
                <ul class="flex">
                    <li><button type="button" type="button" class="tb_tooltip undo_redo btn undo disabled"><?php echo themify_get_icon('back-left', 'ti') ?><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></button></li>
                    <li><button type="button" class="tb_tooltip undo_redo btn redo disabled"><?php echo themify_get_icon('back-right', 'ti') ?><span><?php _e('Redo (CTRL+SHIFT+Z)', 'themify'); ?></span></button></li>
                </ul>
            </li>
            <li class="divider"></li>
            <li class="import">
                <a href="javascript:;" tabindex="-1" class="compact tb_tooltip btn tf_hide"><?php echo themify_get_icon('import', 'ti') ?><span><?php _e('Import', 'themify'); ?></span></a>
                <ul class="flex">
                    <li>
                        <a href="javascript:;" class="tb_tooltip btn" tabindex="-1"><?php echo themify_get_icon('import', 'ti') ?><span><?php _e('Import', 'themify'); ?></span></a>
                        <ul class="submenu tf_abs_t tf_hide">
                            <li><button type="button" data-type="file" class="btn"><?php _e('Import From File', 'themify'); ?></button></li>
                            <li><button type="button" data-type="page" class="btn"><?php _e('Import From Page', 'themify'); ?></button></li>
                            <li><button type="button" data-type="post" class="btn"><?php _e('Import From Post', 'themify'); ?></button></li>
                        </ul>
                    </li>
                </ul>
            </li>
	    <li class="divider"></li>
	    <li class="export">
		<button type="button" class="tb_tooltip btn">
		    <?php echo themify_get_icon('export', 'ti') ?>
		    <span><?php _e('Export', 'themify'); ?></span>
		</button>
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
            <li class="css"><button type="button" class="tb_tooltip custom_css btn"><span><?php _e('Custom CSS', 'themify'); ?></span><?php _e('CSS', 'themify'); ?></button></li>
        </ul>
        <div class="save_wrap flex">
            <button type="button" class="tb_tooltip tf_close" title="<?php _e('ESC', 'themify') ?>"><span><?php _e('Close', 'themify'); ?></span></button>
            <div class="save_btn_wrap flex tf_rel">
                <button type="button" class="save save_btn" title="<?php _e('Ctrl + S', 'themify') ?>"><?php _e('Save', 'themify'); ?></button>
            </div>
        </div>
    </div>
</template>

<div id="tb_inline_editor_root" class="tf_abs_t tf_hide">
    <template shadowroot="open">
	<?php $min = themify_is_minify_enabled() ? '.min' : '';
	foreach (array(THEMIFY_METABOX_URI . 'css/themify.minicolors' . $min . '.css', THEMIFY_BUILDER_URI . '/css/editor/themify.combobox' . $min . '.css') as $s):
	    ?>
	    <?php $css = Themify_Enqueue_Assets::addPreLoadCss($s); ?>
    	<link href="<?php echo $css['s'] ?>?ver=<?php echo $css['v'] ?>" rel="stylesheet"/>
<?php endforeach; ?>
        <style id="module_inline_editor_style">
<?php echo file_get_contents(THEMIFY_BUILDER_DIR . '/css/editor/modules/inline-editor' . $min . '.css'); ?>
        </style>
        <div id="toolbar" class="center tf_abs_t tf_hide tf_opacity">
            <div id="edit_link" class="center tf_w tf_hide">
                <button type="button" id="link_btn" class="action tf_rel tf_textl" data-type="link">
                    <span class="tf_maxw tf_overflow tf_inline_b"></span>
                    <span class="themify_tooltip"><?php _e('Click To Edit', 'themify') ?></span>
                </button>
                <a href="javascript:;" class="tf_rel" target="_blank"><?php echo themify_get_icon('new-window', 'ti') ?><span class="themify_tooltip"><?php _e('Open in a new tab', 'themify') ?></span></a>
            </div>
            <form class="link_form tf_rel tf_hide">
                <div class="link_wrap grid center">
                    <div class="tf_rel">
                        <div class="selectwrapper tf_inline_b tf_vmiddle tf_rel">
                            <select id="link_type">
                                <option><?php _e('Same Window', 'themify') ?></option>
                                <option value="_blank"><?php _e('New Window', 'themify') ?></option>
                                <option value="lightbox"><?php _e('Lightbox', 'themify') ?></option>
                            </select>
                        </div>
                        <span class="themify_tooltip"><?php _e('Open Link In', 'themify') ?></span>
                    </div>
                    <input class="link_input" placeholder="<?php _e('URL', 'themify') ?>" type="text" required="1">
                    <button type="button" class="action unlink tf_abs_t tf_hide" data-type="unlinkBack"><?php echo themify_get_icon('unlink', 'ti') ?><span class="themify_tooltip"><?php _e('Unlink', 'themify') ?></span></button>
                    <button type="submit" class="tf_hide"></button>
                </div>
                <fieldset class="lightbox center tf_box tf_width tf_hide">
                    <legend><?php _e('Lightbox Options', 'themify') ?></legend>
                    <div class="lb_field grid center lb_width">
                        <label for="lb_w"><?php _e('Width', 'themify') ?></label>
                        <div id="lb_w_holder"></div>
                    </div>
                    <div class="lb_field grid center lb_height">
                        <label for="lb_h"><?php _e('Height', 'themify') ?></label>
                        <div id="lb_h_holder"></div>
                    </div>
                </fieldset>
            </form>
            <div id="dialog" class="grid center"></div>
            <ul id="menu" class="grid center tf_box">
		<li class="disabled">
                    <span class="themify_tooltip"><?php _e('Undo', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-type="undo"><?php echo themify_get_icon('back-left', 'ti') ?></a>
                </li>
		<li class="disabled">
                    <span class="themify_tooltip"><?php _e('Redo', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-type="redo"><?php echo themify_get_icon('back-right', 'ti') ?></a>
                </li>
                <li>
                    <a href="javascript:;" tabindex="0" class="action unlink tf_abs_t tf_hide" data-type="unlink"><?php echo themify_get_icon('unlink', 'ti') ?><span class="themify_tooltip"><?php _e('Unlink', 'themify') ?></span></a>
                    <a href="javascript:;" tabindex="0" class="action" data-type="link"><span class="themify_tooltip"><?php _e('Link', 'themify') ?></span><?php echo themify_get_icon('link', 'ti') ?></a>
                </li>
                <li data-type="formatBlock">
                    <span class="themify_tooltip"><?php _e('Paragraph', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-action="p"><?php echo themify_get_icon('paragraph', 'ti') ?></a>
                    <ul class="submenu center tf_abs_t tf_box tf_hide">
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="p">P</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h1">H1</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h2">H2</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h3">H3</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h4">H4</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h5">H5</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="h6">H6</a></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="pre"><?php echo themify_get_icon('fas code', 'fa') ?></a><span class="themify_tooltip"><?php _e('Preformatted', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="blockquote"><?php echo themify_get_icon('fas quote-left', 'fa') ?></a><span class="themify_tooltip"><?php _e('Quote', 'themify') ?></span></li>
                    </ul>
                </li>
                <li data-type="text_align">
                    <span class="themify_tooltip"><?php _e('Text Align', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-action="justifyLeft"><?php echo themify_get_icon('align-left', 'ti') ?></a>
                    <ul class="submenu center tf_abs_t tf_box tf_hide">
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="justifyLeft"><?php echo themify_get_icon('align-left', 'ti') ?></a><span class="themify_tooltip"><?php _e('Left', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="justifyCenter"><?php echo themify_get_icon('align-center', 'ti') ?></a><span class="themify_tooltip"><?php _e('Center', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="justifyRight"><?php echo themify_get_icon('align-right', 'ti') ?></a><span class="themify_tooltip"><?php _e('Right', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="justifyFull"><?php echo themify_get_icon('align-justify', 'ti') ?></a><span class="themify_tooltip"><?php _e('Justify', 'themify') ?></span></li>
                    </ul>
                </li>
                <li>
                    <span class="themify_tooltip"><?php _e('Bold', 'themify') ?></span>
                    <a class="action bold" href="javascript:;" tabindex="0" data-type="bold">B</a>
		    <ul class="submenu center tf_abs_t tf_box tf_hide">
			<li><a href="javascript:;" tabindex="0" class="action bold" data-type="bold">B</a><span class="themify_tooltip"><?php _e('Bold', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-type="italic"><?php echo themify_get_icon('Italic', 'ti') ?></a><span class="themify_tooltip"><?php _e('Italic', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-type="underline"><?php echo themify_get_icon('underline', 'ti') ?></a><span class="themify_tooltip"><?php _e('Text Decoration', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action strike" data-type="strikethrough">S</a><span class="themify_tooltip"><?php _e('Strikethrough', 'themify') ?></span></li>
                    </ul>
                </li>
                <li data-type="list">
                    <span class="themify_tooltip"><?php _e('List Settings', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-action="insertUnorderedList"><?php echo themify_get_icon('list', 'ti') ?></a>
                    <ul class="submenu center tf_abs_t tf_box tf_hide">
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="insertUnorderedList"><?php echo themify_get_icon('ti-list', 'ti') ?></a><span class="themify_tooltip"><?php _e('Unordered List', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-action="insertOrderedList"><?php echo themify_get_icon('ti-list-ol', 'ti') ?></a><span class="themify_tooltip"><?php _e('Ordered List', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-type="Indent"><?php echo themify_get_icon('control-skip-forward', 'ti') ?></a><span class="themify_tooltip"><?php _e('Increase Indent', 'themify') ?></span></li>
                        <li><a href="javascript:;" tabindex="0" class="action" data-type="Outdent"><?php echo themify_get_icon('control-skip-backward', 'ti') ?></a><span class="themify_tooltip"><?php _e('Decrease Indent', 'themify') ?></span></li>
                    </ul>
                </li>					
		<li>
		  <span class="themify_tooltip"><?php _e('Insert Image', 'themify') ?></span>
		  <button type="button" class="action" data-type="image"><?php echo themify_get_icon('image','ti')?></button>
		</li>
                <li>
                    <span class="themify_tooltip"><?php _e('Text Color', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-type="color"><?php echo themify_get_icon('paint-bucket', 'ti') ?></a>
                </li>
                <li>
                    <span class="themify_tooltip"><?php _e('Fonts', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action" data-type="font"><?php echo themify_get_icon('text', 'ti') ?></a>
                </li>
                <li>
                    <span class="themify_tooltip"><?php _e('Expand', 'themify') ?></span>
                    <a href="javascript:;" tabindex="0" class="action expand" data-type="expand"><?php echo themify_get_icon('new-window', 'ti') ?></a>
                </li>
            </ul>
        </div>
    </template>
</div>
<div id="tb_pallete_root" class="tf_abs_t tf_hide">
    <template shadowroot="open">
	<form id="pallete" class="tf_h tb_field">
	    <div class="header tf_box">
		<button type="button" class="back tf_close"></button>
	    </div>
	    <ul class="menu tf_box tf_scrollbar">
		<?php $menu = array('brightness', 'saturation', 'contrast', 'negative', 'hue', 'desaturate', 'desaturateLuminance', 'brownie', 'sepia', 'vintagePinhole', 'kodachrome', 'technicolor', 'detectEdges', 'sharpen', 'emboss', 'polaroid', 'shiftToBGR', 'pixelate'); ?>
		<?php foreach ($menu as $m): ?>               
		<li>
		    <div class="label">
			<label for="<?php echo $m?>"><?php printf(__('%s','themify'), ucfirst($m))?></label>
		    </div>
		    <input type="number" min="0" max="100" value="0" id="<?php echo $m?>" data-id="<?php echo $m?>">
		    <input class="slider" type="range" min="0" max="100" data-id="<?php echo $m?>">
		</li>
		<?php endforeach; ?>
	    </ul>
	    <div class="footer tf_box">
		<button type="reset" class="reset tf_block"><?php _e('Reset','themify')?></button>
	    </div>
	</form>
    </template>
</div>