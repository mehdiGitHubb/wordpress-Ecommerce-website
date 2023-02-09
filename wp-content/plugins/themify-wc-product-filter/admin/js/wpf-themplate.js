;
var WPF;
(function ($, window, document, undefined) {

    'use strict';
    // Builder Function
    WPF = {
        prefix: 'wpf_',
        template_type: false,
        init: function ($options) {
            $options = $.extend({prefix: this.prefix,
                template_type: this.template_type},
            $options);
            this.prefix = $options.prefix;
            this.template_type = $options.template_type;
            this.Undelegate();
            this.bindEvents();
        },
        Undelegate: function () {
            $(document).off('change click');
        },
        bindEvents: function () {
            this.SetupColors();
            this.InitDraggable();
            this.InitSortable();
            this.ShowHide();
            this.Unique($('#'+this.prefix+'module_content .'+this.prefix+'back_active_module_content'));
            this.Open();
            this.Delete();
            this.Save();
            this.ShowThemplates();
            this.AddItem();
            this.RemmoveItem();
            this.multiLanguages();
            $.event.trigger("WPF.template_load", this.template_type);
        },
        PlaceHoldDragger: function () {
            $('.' + WPF.prefix + 'module_holder').each(function () {
                var $empty = $(this).find('.' + WPF.prefix + 'empty_holder_text');
                if ($(this).find('.' + WPF.prefix + 'active_module').length === 0) {
                    $empty.show();
                }
                else {
                    $empty.hide();
                }
            });
        },
        InitDraggable: function () {
            var $this = this;
            $('.' + $this.prefix + 'back_module_panel .' + $this.prefix + "back_module").draggable({
                appendTo: "body",
                helper: "clone",
                revert: 'invalid',
                snapMode: "inner",
                connectToSortable: '.' + $this.prefix + "module_holder",
                stop: function (event, ui) {
                    var $item = $(ui.helper[0]),
                            $type = $item.data('type');
                    if ($('#' + $this.prefix + 'module_content').find('[data-type="' + $type + '"]').length > 0) {
                        $('#' + $this.prefix + 'cmb_' + $type).hide();
                        $.event.trigger("WPF.template_drag_start", [$item, ui, $this.template_type]);
                        $this.Unique($item);
                        var $color_picker = $item.find('.' + $this.prefix + 'color_picker');
                        $this.DestroyInputColor($color_picker);
                        $this.SetInputColor($color_picker);
                        $item.find('.' + $this.prefix + 'toggle_module').trigger('click');
                        $.event.trigger("WPF.template_drag_end", [$item, ui, $this.template_type]);
                    }
                }
            });
        },
        InitSortable: function () {
            var $this = this;
            $('.' + $this.prefix + "module_holder").sortable({
                placeholder: $this.prefix + 'ui_state_highlight',
                items: '.' + $this.prefix + 'back_module',
                connectWith: '.' + $this.prefix + "module_holder",
                cursor: 'move',
                revert: 100,
                axis: 'y',
                cancel:'.wpf_active_module',
                sort: function (event, ui) {
                    var placeholder_h = ui.item.outerHeight();
                    $('.' + $this.prefix + 'module_holder ' + '.' + $this.prefix + 'ui_state_highlight').height(placeholder_h);

                },
                receive: function (event, ui) {
                    $this.PlaceHoldDragger();
                    $(this).parent().find('.' + $this.prefix + 'empty_holder_text').hide();
                    $(ui.item).removeClass('dragged');
                },
                start: function (event, ui) {
                    $(ui.item).removeClass($this.prefix + 'dragged');
                },
                stop: function (event, ui) {
                    var $item = $(ui.item);
                    $item.addClass($this.prefix + 'dragged');

                }
            });
            $('.' + $this.prefix + "back_active_module_add").sortable({
                placeholder: $this.prefix + 'ui_state_highlight',
                items: 'li',
                connectWith: 'parent',
                cursor: 'move',
                revert: 100,
                axis: 'y'
            });

        },
        ShowHide: function () {
            $( '#' + WPF.prefix + 'lightbox_container' ).on( 'change', '.' + WPF.prefix + 'changed input,.' + WPF.prefix + 'changed select', function () {
				var $container, slide = true;
				if ($(this).closest('.' + WPF.prefix + 'show_icons').length && !$(this).closest('.' + WPF.prefix + 'items_container' ).length ){
                    $container = $(this).closest('.'+WPF.prefix +'show_icons').find('.'+WPF.prefix +'items_container');
                    slide = $(this).is(':checked');
                }
                else if($(this).closest('.'+WPF.prefix +'result_page_wrapper').length>0 && ( this.id == 'wpf_diff_page' || this.id == 'wpf_same_page' ) ){
                    $container = $(this).closest('.'+WPF.prefix +'result_page_wrapper').find('.'+WPF.prefix +'result_page_select');
                    slide = $(this).val()==='diff_page';
                }
                else if($(this).closest('.'+WPF.prefix +'grid').length>0){
                    $container = $('#'+WPF.prefix +'group_fields').closest('.'+WPF.prefix +'lightbox_row');
                    slide = $(this).val()==='vertical';
                }
                else if($(this).closest('.'+WPF.prefix +'order').length>0){
                    $container = $(this).closest('.'+WPF.prefix+'order').next('.'+WPF.prefix +'orderby');
                    slide = $(this).val()!=='term_order';
                }
                else if($(this).closest('.'+WPF.prefix +'show_range').length>0){
                    var _this = $(this).closest('.'+WPF.prefix+'back_active_module_row'),
                    group = _this.next('.'+WPF.prefix +'group'),
                    slider = _this.nextAll('.'+WPF.prefix +'slider');
                    if($(this).val() === 'group'){
                        slider.slideUp();
                        group.slideDown();
                    }else{
                        group.slideUp();
                        slider.slideDown();
                    }
                }
                else if($(this).closest('.'+WPF.prefix +'display_as').length>0){
                    $container = $(this).closest('.'+WPF.prefix+'back_active_module_content').find('.'+WPF.prefix +'icons_block');
                    var val = $(this).val();
					slide = val === 'checkbox' || val === 'radio';

					if ( val === 'dropdown' || val === 'radio' ) {
						$(this).closest( '.' + WPF.prefix + 'back_active_module_content' ).find( '.' + WPF.prefix + 'show_all_block' ).show();
					} else {
						$(this).closest( '.' + WPF.prefix + 'back_active_module_content' ).find( '.' + WPF.prefix + 'show_all_block' ).hide();
					}
                }
                else if($(this).prop('id')==='wpf_pagination_fields'){
                    $container = $('.wpf_infinity');
                    slide = !$(this).is(':checked');
                } else if ( $( this ).prop( 'name' ) === 'pagination_type' ) {
					if ( $( this ).val() === 'infinity_auto' ) {
						$( '.wpf_lightbox_row.wpf_infinity_buffer' ).show();
					} else {
						$( '.wpf_lightbox_row.wpf_infinity_buffer' ).hide();
					}
				}

                if ( typeof $container !== 'undefined' ) {
                    if( slide ) $container.slideDown();
                    else $container.slideUp();
                }
             });
             $('.'+WPF.prefix +'changed input:checked,.'+WPF.prefix +'changed option:selected').trigger('change');
        },
        Open: function () {
            var $this = this;
            $(document).on('click', '.' + $this.prefix + 'toggle_module', function (e) {
                var $container = $(this).closest('.' + $this.prefix + 'back_module').find('.' + $this.prefix + 'back_active_module_content');
                if ($(this).hasClass($this.prefix + 'opened') || $container.is(':visible')) {
                    $(this).removeClass($this.prefix + 'opened');
                    $container.slideUp();
                }
                else {
                    $(this).addClass($this.prefix + 'opened');
                    $container.slideDown();
                    const type = $container.data('type');
                    if('wpf_cat'===type || 'wpf_tag'===type){
                        const wrap=$container[0].getElementsByClassName('wpf_tax_items');
                        if(wrap[0] && wrap[0].dataset['url']){
                            const ckb=$container[0].querySelector('input[name="['+type+'][color]"]');
                            if(ckb.checked){
                                WPF.getTax(wrap[0]);
                            }else{
                                ckb.addEventListener('change',function(){
                                    WPF.getTax(wrap[0]);
                                },{once:true});
                            }
                        }
                    }
                }
                e.preventDefault();
            });
        },
        Delete: function () {
            $(document).on('click', '.' + WPF.prefix + 'delete_module', function (e) {
                e.preventDefault();
                if (confirm(wpf_js.module_delete)) {
                    var $container = $(this).closest('.' + WPF.prefix + 'back_module');
                    $('#'+WPF.prefix +'cmb_' + $container.data('type')).show();
                    $container.remove();
                    WPF.PlaceHoldDragger();

                }
            });
        },
        Save: function () {
            var self = this;
            $('#' + self.prefix + 'submit').on( 'click', function (event) {

                var $form = $(this).closest('form'),
                        $inputs = $('.' + self.prefix + 'back_builder').find('input,select,textarea');
                $inputs.prop('disabled', true);//this data no need

                setTimeout(function () {
                    var $data = self.ParseData();
                    $.event.trigger("WPF.before_template_save", $data);
                    var $data = JSON.stringify($data);
                    $('#' + self.prefix + 'layout').val($data);
                    $.ajax({
                        url: $form.prop('action'),
                        method: 'POST',
                        dataType: 'json',
                        data: $form.serialize(),
                        beforeSend: function () {
                            $form.removeClass(self.prefix + 'done').addClass(self.prefix + 'save');
                        },
                        complete: function () {
                            $inputs.prop('disabled', false);
                            $form.removeClass(self.prefix + 'save').addClass(self.prefix + 'done');
                        },
                        success: function (res) {
                            if (res && res.status == '1') {
                                $form.find('#' + self.prefix + 'themplate_id').val(res.id);
                                $('#' + self.prefix + 'success_text').html('<p><strong>' + res.text + '</strong></p>').show();
                                setTimeout(function () {
                                    //  $('.' + WPF.prefix + 'close_lightbox').trigger('click');
                                    $('#' + self.prefix + 'success_text').html('').hide();
                                }, 2000);
                                $.event.trigger("WPF.after_template_save");
                            }
                        }
                    });
                }, 100);
                event.preventDefault();
            });
        },
        ParseData: function () {
            var $wrapper = $('#' + WPF.prefix + 'module_content'),
                    $data = {},
                    $modules = $wrapper.find('.' + WPF.prefix + 'back_active_module_content');
            $modules.each(function () {//each module in colum
                var $type = $(this).data('type');
                $data[$type] = {};
                var $inputs = $(this).find('input:checked,input[type="text"],input[type="number"],input[type="hidden"],textarea,select');
                $inputs.each(function () {//all input in module
                    var $name = $(this).attr('name');
                    if ($name) {
                        var $tmp_match = $name.split(']');
                        if ($tmp_match) {
                            $tmp_match.pop();
                            var $match = [],
                                    $arr = false;
                            for (var $m in $tmp_match) {
                                var $vals = $tmp_match[$m].split('[');
                                if ($vals[1]) {
                                    $match[$m] = $vals[1];
                                }
                            }
                            $arr = $match[2] == 'arr';
                            if (!$arr && (!$data[$type][$match[1]] || $match[2])) {//for multiple items e.g checkboxes
                                if ($match[2]) {
                                    var $lng = $match[2];
                                    if (typeof $data[$type][$match[1]] != 'object') {
                                        $data[$type][$match[1]] = {};
                                    }
                                    $data[$type][$match[1]][$lng] = $(this).val();
                                }
                                else {
                                    var $val = false;
                                    if ($(this).hasClass(WPF.prefix + 'color_picker')) {
                                        $val = $(this).minicolors('rgbaString');
                                        $val = $val == 'rgba(0, 0, 0, 1)' && !$(this).val()? false : $val;
                                    }
                                    else {
                                        $val = $(this).val() == 'on' ? true : $(this).val();
                                    }
                                    $data[$type][$match[1]] = $val;
                                }
                            }
                            else {

                                if (!$arr) {
                                    if (typeof $data[$type][$match[0]] != 'object' && typeof $data[$type][$match[1]] != 'object') {
                                        var $first_val = $data[$type][$match[1]];
                                        $data[$type][$match[1]] = [];
                                        $data[$type][$match[1]][0] = $first_val;
                                    }
                                    $data[$type][$match[1]].push($(this).val());
                                }
                                else {

                                    $data[$type][$match[1]] = $(this).val();
                                    if (!$data[$type][$match[1]]) {
                                        $data[$type][$match[1]] = [];
                                    }
                                }
                            }
                        }
                    }
                });
            });

            return $data;
        },
        Unique: function ($module) {
            $module.each(function () {
                var $m = $(this);
                var $labels = $m.find('label');
                $labels.each(function () {
                    var $id = $(this).attr('for');
                    if ($id) {
                        $id = WPF.Escape($(this).attr('for'));
                        if ($('#' + $id).length > 0) {
                            var $uniqud = WPF.GenerateUnique();
                            $m.find('#' + $id).attr('id', $uniqud);
                            $(this).attr('for', $uniqud);
                        }
                    }
                });
                var $reg = /.*?\[(.+?)\]/ig;
                var $input = $m.find('input[type="radio"]');
                var $radios = {};
                $input.each(function ($i) {
                    var $name = $(this).attr('name');
                    if ($name) {
                        $radios[$name] = 1;
                    }
                });
                for (var $name in $radios) {
                    var $match = $name.match($reg);
                    if ($match) {
                        var $uniqeuname = WPF.GenerateUnique();
                        var $radio = $m.find('input:radio[name="' + $name + '"]');//if there are several groups radio
                        var $new_name = $uniqeuname + $match[0] + $match[1];
                        $radio.attr('name', $new_name);
                        if ($m.find('input:radio[name!="' + $name + '"]')) {//if empty
                            $m.find('input:radio[name="' + $new_name + '"][checked]').prop('checked', true);//to display checked;
                        }
                    }
                }

            });
        },
        GenerateUnique: function () {
            return WPF.prefix + Math.random().toString(36).substr(2, 9);
        },
        Escape: function ($selector) {
            return $selector.replace(/(:|\.|\[|\]|,)/g, "\\$1");
        },
        SetupColors: function () {

            var $colors = $('#' + WPF.prefix + 'module_content').find('.' + WPF.prefix + 'color_picker');
            $colors.each(function () {
                var $color = WPF.RgbaToHex($(this).data('value'));
                if ($color && $color.indexOf('@') !== -1) {
                    $color = $color.split('@');
                    $(this).val($color[0]);
                    $(this).attr('data-opacity', $color[1]);
                }
            });
            this.SetInputColor();
        },
        SetInputColor: function ($el) {

            if (!$el) {
                $el = $('.' + WPF.prefix + 'color_picker');
            }
            $el.minicolors({
                opacity: true,
                position: 'top right',
                theme: 'default',
                show: function () {
                    $('.' + WPF.prefix + "module_holder").sortable('disable');
                },
                hide: function () {
                    $('.' + WPF.prefix + "module_holder").sortable('enable');
                },
                create: function ($e) {
                }
            });
        },
        DestroyInputColor: function ($el) {
            if (!$el) {
                $el = $('#' + WPF.prefix + 'module_content').find('.' + WPF.prefix + 'color_picker');
            }
            $el.minicolors('destroy');
        },
        RgbaToHex: function (rgb) {
            if (!rgb) {
                return false;
            }
            rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(.*)[\s+]?\)/i);
            var $hex = (rgb && rgb.length >= 3) ? "#" +
                    ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                    ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
            if ($hex && rgb[4]) {
                $hex += '@' + rgb[4];
            }
            return $hex;
        },
        ShowThemplates: function () {
            var preffix = this.prefix;
            $(document).on('WPF.close_lightbox', function (e, item) {
                if ($(item).closest('.wpf_admin_lightbox').find('#' + preffix + 'themplate_id').val()) {
                    var $table = $('#the-list');
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {'action': 'wpf_get_list'},
                        beforeSend: function () {
                            $table.addClass(preffix + 'wait');
                        },
                        complete: function () {
                            $table.removeClass(preffix + 'wait');
                        },
                        success: function (res) {
                            if (res) {
                                $table.replaceWith($(res).find('#the-list'));
                            }
                        }
                    });
                }
            });
        },
        AddItem:function(){
            $('#'+WPF.prefix +'module_content').on('click','.'+WPF.prefix +'add_item',function(e){
                e.preventDefault();
                var $el = $(this).prev('ul').children('li').first().clone();
                    $el.hide().find('input').val('');
                    $(this).prev('ul').append($el);
                    $el.slideDown();
            });   
        },
        RemmoveItem:function(){
            $('#'+WPF.prefix +'module_content').on('click','.'+WPF.prefix +'remove_item',function(e){
                e.preventDefault();
                $(this).closest('li').slideUp(function(){
                    $(this).remove();
                });
            });
        },
        multiLanguages:function(){
			$('body').on('click', '.wpf_language_tabs li', function (e) {
                e.preventDefault();
                var $this = $(this);
                if($this.hasClass('wpf_active_tab_lng')){
                    return;
                }
                $this.siblings('.wpf_active_tab_lng').removeClass('wpf_active_tab_lng');
                $this.addClass('wpf_active_tab_lng');
                var tabs =  $this.parents('.wpf_language_tabs').parent().find('.wpf_language_fields');
               tabs.find('li').removeClass('wpf_active_lng');
               tabs.find('li[data-lng="'+$this.children('a').attr('class')+'"]').addClass('wpf_active_lng');
            });

        },
        getTax:function(wrap){
            $.ajax({
                url: wrap.dataset['url'],
                success: function (data) {
                    if (data) {
                        wrap.innerHTML=data;
                        wrap.dataset.url='';
                        WPF.multiLanguages();
                        WPF.SetupColors();
                    }
                }
            });
        }
    };

}(jQuery, window, document));
