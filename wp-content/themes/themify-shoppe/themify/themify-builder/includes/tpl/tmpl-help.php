<?php
defined('ABSPATH') || exit;
?>
<template id="tmpl-help_lightbox">
    <style id="module_help_style">
        <?php echo file_get_contents(THEMIFY_BUILDER_DIR.'/css/editor/modules/help.css');?>
    </style>
    <div id="lightbox" class="video tf_abs_t tf_opacity tf_box tf_w tf_h">
        <div class="wrapper tf_scrollbar tf_abs_c tf_box">
            <ul class="nav">
                <li class="tf_rel active" data-type="video"><?php _e('Videos'); ?></li>
                <li class="tf_rel" data-type="shortcut"><?php _e('Shortcuts'); ?></li>
            </ul>
            <div class="menu_content menu_video tf_hide" data-type="videos">
                <div class="video_wrapper">
                    <div class="player tf_overflow tf_rel tf_hide current" id="tb_help_builder_basics">
                        <a href="//youtube.com/embed/FPPce2D8pYI">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/FPPce2D8pYI/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720">
                        </a>
                    </div>
                    <div class="player tf_overflow tf_rel tf_hide" id="tb_help_responsive_styling">
                        <a href="//youtube.com/embed/0He9P2Sp-WY">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/0He9P2Sp-WY/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720">
                        </a>
                    </div>
                    <div class="player tf_overflow tf_rel tf_hide" id="tb_help_revisions">
                        <a href="//youtube.com/embed/Su48Y-hXTR4">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/Su48Y-hXTR4/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720">
                        </a>
                    </div>
                    <div class="player tf_overflow tf_rel tf_hide" id="tb_help_builder_library">
                        <a href="//youtube.com/embed/At-B1O8VOyE">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/At-B1O8VOyE/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720"/>
                        </a>
                    </div>
                    <div class="player tf_overflow tf_rel tf_hide" id="tb_help_scrollto_row">
                        <a href="//youtube.com/embed/KtFHwH6N30o">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/KtFHwH6N30o/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720">
                        </a>
                    </div>
                    <div class="player tf_overflow tf_rel tf_hide" id="tb_help_row_frame">
                        <a href="//youtube.com/embed/yKFrn76x8nw">
                            <span class="player_btn tf_abs_c"></span>
                            <img decoding="async" loading="lazy" src="//img.youtube.com/vi/yKFrn76x8nw/maxresdefault.jpg" class="tf_h tf_w tf_abs_t" width="1280" height="720">
                        </a>
                    </div>
                </div>
                <ul class="menu">
                    <li class="tf_rel tf_box current"><a href="#tb_help_builder_basics"><?php _e('Builder Basics', 'themify') ?></a></li>
                    <li class="tf_rel tf_box"><a href="#tb_help_responsive_styling"><?php _e('Responsive Styling', 'themify') ?></a></li>
                    <li class="tf_rel tf_box"><a href="#tb_help_revisions"><?php _e('Revisions', 'themify') ?></a></li>
                    <li class="tf_rel tf_box"><a href="#tb_help_builder_library"><?php _e('Builder Library', 'themify') ?></a></li>
                    <li class="tf_rel tf_box"><a href="#tb_help_scrollto_row"><?php _e('ScrollTo Row', 'themify') ?></a></li>
                    <li class="tf_rel tf_box"><a href="#tb_help_row_frame"><?php _e('Row/Column Frame', 'themify') ?></a></li>
                </ul>
            </div>
            <div class="menu_content menu_shortcut tf_hide" data-type="shortcuts">
                <table class="tf_w" cellspacing="0">
                    <tr>
                        <th width="20%"></th>
                        <th><?php _e('Mac', 'themify') ?></th>
                        <th><?php _e('Windows', 'themify') ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Save', 'themify') ?></td>
                        <td><?php _e('Cmd + S', 'themify') ?></td>
                        <td><?php _e('Ctrl + S', 'themify') ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Undo', 'themify') ?></td>
                        <td><?php _e('Cmd + Z', 'themify') ?></td>
                        <td><?php _e('Ctrl + Z', 'themify') ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Redo', 'themify') ?></td>
                        <td><?php _e('Cmd + Shift + Z', 'themify') ?></td>
                        <td><?php _e('Ctrl + Shift + Z', 'themify') ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Duplicate', 'themify') ?></td>
                        <td><?php _e('Cmd + D', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + D', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Delete', 'themify') ?></td>
                        <td><?php _e('Cmd + Delete', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + Delete', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Copy', 'themify') ?></td>
                        <td><?php _e('Cmd + C', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + C', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Paste', 'themify') ?></td>
                        <td><?php _e('Cmd + V', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + V', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Paste Styling', 'themify') ?></td>
                        <td><?php _e('Cmd + Shift + V', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + Shift + V', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Move Up', 'themify') ?></td>
                        <td><?php _e('Cmd + Up Arrow', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + Up Arrow', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                    <tr>
                        <td><?php _e('Move Down', 'themify') ?></td>
                        <td><?php _e('Cmd + Down Arrow', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                        <td><?php _e('Ctrl + Down Arrow', 'themify') ?><span><?php _e('(With a module selected)', 'themify') ?></span></td>
                    </tr>
                </table>
            </div>
            <div class="tf_close"></div>
        </div>
    </div>
</template>