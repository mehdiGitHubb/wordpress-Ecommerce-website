var ThemifyConstructor;
(($,api, Themify, topWindow, doc, und) => {
    'use strict';
    ThemifyConstructor = {
        data: null,
        styles: {},
        settings: {},
        editors: null,
        afterRun: [],
        radioChange: [],
        bindings: [],
        stylesData: null,
        values: {},
        clicked: null,
        type: null,
        static: null,
        label: null,
        is_repeat: null,
        is_sort: null,
        is_new: null,
        is_ajax: null,
        breakpointsReverse: null,
        init() {
            this.breakpointsReverse = Object.keys(themifyBuilder.breakpoints).reverse();
            this.breakpointsReverse.push('desktop');
            this.static = themifyBuilder.i18n.options;
            this.label = themifyBuilder.i18n.label;
    
            let fonts = themifyBuilder.safe;
            for (let i = 0, len = fonts.length; i < len; ++i) {
                if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                    this.font_select.safe[fonts[i].value] = fonts[i].name;
                }
            }
            fonts = themifyBuilder.google;
            if(Array.isArray(fonts)){
                for (let i = 0, len = fonts.length; i < len; ++i) {
                    if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                        let variants=[...fonts[i].variant];
                        for(let j=variants.length-1;j>-1;--j){
                            variants[j]=typeof variants[j] === 'string' ? variants[j].replace('i', 'italic').replace('r', 'regular') : variants[j].toString();
                        }
                        this.font_select.google[fonts[i].value] = {n: fonts[i].name,v:variants};
                    }
                }
            }
            else{
                for(let k in fonts){
                    this.font_select.google[k]={n:fonts[k].n,v:[...fonts[k].v]};
                    for(let variants=this.font_select.google[k].v,j=variants.length-1;j>-1;--j){
                        variants[j]=typeof variants[j] === 'string' ? variants[j].replace('i', 'italic').replace('r', 'regular') : variants[j].toString();
                    }
                }
            }
            fonts = themifyBuilder.cf;
            if(Array.isArray(fonts)){
                for (let i = 0, len = fonts.length; i < len; ++i) {
                    if ('' !== fonts[i].value && 'default' !== fonts[i].value) {
                        let variants=[...fonts[i].variant];
                        for(let j=variants.length-1;j>-1;--j){
                            variants[j]=typeof variants[j] === 'string' ? variants[j].replace('i', 'italic').replace('r', 'regular') : variants[j].toString();
                        }
                        this.font_select.cf[fonts[i].value] = {n: fonts[i].name, v: variants};
                    }
                }
            }
            else{
                for(let k in fonts){
                    this.font_select.cf[k]={n:fonts[k].n,v:[...fonts[k].v]};
                    for(let variants=this.font_select.cf[k].v,j=variants.length-1;j>-1;--j){
                        variants[j]=typeof variants[j] === 'string' ? variants[j].replace('i', 'italic').replace('r', 'regular') : variants[j].toString();
                    }
                }
            }
            fonts = themifyBuilder.i18n.options = null;
        },
        /**
         * Get an option's DOM element by its ID
         */
        getEl(id) {
            return api.LightBox.el.querySelector('#' + id);
        },
        getOptions(data) {
            if (data.options !== und) {
                return data.options;
            }
            for (let i in this.static) {
                if (data[i] === true) {
                    return this.static[i];
                }
            }
            return false;
        },
        getTitle(data) {
            if (data.type === 'custom_css') {
                return this.label.custom_css;
            }
            if (data.type === 'title') {
                return this.label.m_t;
            }
            return data.label !== und ? (this.label[data.label] !== und ? this.label[data.label] : data.label) : false;
        },
        getSwitcher() {
            const sw = doc.createElement('div'),
                    breakpoints = this.breakpointsReverse,
                    self = this;
            sw.className = 'tb_lightbox_switcher tf_clearfix';
            for (let i = breakpoints.length - 1; i > -1; --i) {
                let b = breakpoints[i],
                        btn = doc.createElement('button');
                btn.dataset.href = '#' + b;
                btn.className = 'tab_' + b;
                btn.title = b === 'tablet_landscape' ? this.label.table_landscape : (b.charAt(0).toUpperCase() + b.substr(1));
                btn.appendChild(api.Helper.getIcon('ti-' + (b === 'tablet_landscape' ? 'tablet' : b)));
                sw.appendChild(btn);
            }
            return sw.tfOn(Themify.click, function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (e.target !== this) {
                    const a = e.target.closest('button');
                    if (a !== null) {
                        self.lightboxSwitch(a.dataset.href);
                    }
                }
            });
        },
        lightboxSwitch(bp) {
            const id = bp.replace('#', '');
            if (id === api.activeBreakPoint) {
                return;
            }
            api.ToolBar.breakpointSwitcher(id);
        },
        binding(_this, data, val, context) {
            let logic = false;
            const binding = data.binding,
                    type = data.type;
            if (type === 'select' && val == 0) {
                val = '';
            }
            if ('video' === type) {
                if (val == '') {
                    logic = binding.empty;
                } else {
                    const provider = Themify.parseVideo(val);
                    if (provider.type === 'youtube' || provider.type === 'vimeo') {
                        logic = binding.external;
                    } else {
                        let url;
                        try {
                            url = new URL(val);
                        } catch (_) {
                            url = false;
                        }
                        logic = false !== url && window.top.location.hostname === url.hostname ? binding.local : binding.empty;
                    }
                }
            } else if (!val && binding.empty !== und) {
                logic = binding.empty;
            } else if (val && binding[val] !== und) {
                if (type === 'radio' || type === 'icon_radio') {
                    logic = _this.checked === true ? binding[val] : false;
                } else {
                    logic = binding[val];
                }
            } else if (val && binding.not_empty !== und) {
                logic = binding.not_empty;
            } else if (binding.select !== und && val !== binding.select.value) {
                logic = binding.select;
            } else if (binding.checked !== und && _this.checked === true) {
                logic = binding.checked;
            } else if (binding.not_checked !== und && _this.checked === false) {
                logic = binding.not_checked;
            }
            if (logic) {

                if (context === und || context === null || context.length === 0) {
                    context = _this.closest('.tb_tab,.tb_expanded_opttions') || topWindow.document.tfId('tb_lightbox_container');
                }
                const hasHide = logic.hide !== und,
                        hasShow = logic.show !== und,
                        relatedBindings = [],
                        options=this.data[this.clicked].options,
                        getData = (opt,key) => {
                            let value;
                            if(und !== key && opt!==und){
                                for (let i =opt.length-1; i>-1; --i) {
                                    if (opt[i].id === key) {
                                        value = opt[i];
                                        break;
                                    }
                                    if (opt[i].options!==und && Array.isArray(opt[i].options)) {
                                        value = getData(opt[i].options, key);
                                        if (value !== und) {
                                            break;
                                        }
                                    }
                                }
                            }
                            return value;
                        },
                        relatedBinding = (el, data, context) => {
                            const type = el.dataset.type;
                            let  _this;
                            if ('radio' === type) {
                                _this = el.querySelector('input:checked') || el.tfTag('input')[0];
                            } 
                            else if ('checkbox' === type || 'toggle_switch' === type) {
                                _this = el.tfTag('input')[0];
                            } 
                            else if ('select' === type) {
                                _this = el.tfTag('select')[0];
                            }
                            if (_this) {
                                this.binding(_this, data, _this.value, context);
                            }
                        };
                if (hasShow === true && !Array.isArray(logic.show)) {
                    logic.show = [logic.show];
                }
                if (hasHide === true && !Array.isArray(logic.hide)) {
                    logic.hide = [logic.hide];
                }
                let items = hasShow === true ? logic.show : [];
                if (hasHide === true) {
                    items = items.concat(logic.hide);
                }
                for (let i = 0, len = items.length; i < len; ++i) {
                    if (hasHide === true && logic.hide[i] !== und) {
                        let sel=logic.hide[i].indexOf('#')===0?logic.hide[i]:'.'+logic.hide[i],
                            hides = context.querySelectorAll(sel);
                        for (let j = hides.length - 1; j > -1; --j) {
                            hides[j].classList.add('_tb_hide_binding');
                            if (null !== this.component) {
                                let relatedBindData = getData(options,logic.hide[i]);
                                if ('object' === typeof relatedBindData && 'object' === typeof relatedBindData.binding) {
                                    relatedBindings.push({el: hides[j], data: relatedBindData});
                                }
                            }
                        }
                    }
                    if (hasShow === true && logic.show[i] !== und) {
                        let sel=logic.show[i].indexOf('#')===0?logic.show[i]:'.'+logic.show[i],
                            shows = context.querySelectorAll(sel);
                        for (let j = shows.length - 1; j > -1; --j) {
                            // Just temp solution - We need to improve binding logic to have more complex bindings
                            if ('post_filter' === data.id && 'ajax_filter' === logic.show[i] && context.querySelector('#auto_tiles.selected') !== null) {
                                continue;
                            }
                            shows[j].classList.remove('_tb_hide_binding');
                            if (null !== this.component) {
                                let relatedBindData = getData(options,logic.show[i]);
                                if ('object' === typeof relatedBindData && 'object' === typeof relatedBindData.binding) {
                                    relatedBindings.push({el: shows[j], data: relatedBindData});
                                }
                            }
                        }
                    }
                }
                for (let i = relatedBindings.length - 1; i > -1; --i) {
                    relatedBinding(relatedBindings[i].el, relatedBindings[i].data, context);
                }
                if (logic.responsive !== und && logic.responsive.disabled !== und) {
                    if (!Array.isArray(logic.responsive.disabled)) {
                        logic.responsive.disabled = [logic.responsive.disabled];
                    }
                    const items_disabled = logic.responsive.disabled,
                            is_responsive = 'desktop' !== api.activeBreakPoint;
                    for (let i = items_disabled.length - 1; i > -1; --i) {
                        if (logic.responsive.disabled[i] !== und) {
                            let sel=logic.responsive.disabled[i].indexOf('#')===0?logic.responsive.disabled[i]:'.'+logic.responsive.disabled[i],
                                resp = context.querySelectorAll(sel);
                            for (let j = resp.length - 1; j > -1; --j) {
                                resp[i].classList.toggle('tb_responsive_disable', is_responsive);
                            }
                        }
                    }
                }
            }
        },
        control: {
            init(el, type, args) {
                args.name = type;
                this[type].call(this, el, args);
            },
            preview(el, val, args) {
                const self = ThemifyConstructor;
                if(!el || !el.isConnected){
                    return;
                }
                const repeater = args.repeat === true?el.closest('.tb_toggleable_fields,.tb_sort_fields_parent,.tb_row_js_wrapper'):null;
                if (repeater !== null) {
                    self.settings[ repeater.id ] = api.Helper.clear(api.Forms.parseSettings(repeater).v);
                } else {
                    self.settings[ args.id ] = val;
                }
                if (api.mode === 'visual') {
                    let	selector = null;
                    if (args.selector !== und && val) {
                        selector = api.activeModel.el.querySelectorAll(args.selector);
                        if (selector.length === 0) {
                            selector = null;
                        } else if (repeater !== null) {
                            const item = el.closest('.tb_repeatable_field');
                            let items = repeater.children,
                                    index = null;
                            for (let i = 0, len = items.length - 1; i < len; ++i) {
                                if (items[i] === item) {
                                    index = i;
                                    break;
                                }
                            }
                            if (args.rep !== und) {
                                selector = api.activeModel.el.querySelectorAll(args.rep);
                                if (selector[index] !== und) {
                                    items = selector[index].querySelectorAll(args.selector);
                                    if (items.length === 0) {
                                        items = null;
                                    }
                                }
                            } else {
                                items = index !== null && selector[index] !== und ? [selector[index]] : null;
                            }
                            if (items !== null && items !== und) {
                                selector = [];
                                for (let i = items.length - 1; i > -1; --i) {
                                    let sw = items[i].closest('.tf_swiper-slide');
                                    if (sw !== null) {
                                        let wrapper = sw.closest('.tf_swiper-wrapper');
                                        if (wrapper !== null) {
                                            let swItems = wrapper.querySelectorAll('.tf_swiper-slide[data-swiper-slide-index="' + index + '"]'),
                                                    len = swItems.length;
                                            if (len !== 0) {
                                                for (let j = len - 1; j > -1; --j) {
                                                    for (let found = swItems[j].querySelectorAll(args.selector), k = found.length - 1; k > -1; --k) {
                                                        selector.push(found[k]);
                                                    }
                                                }
                                            } else {
                                                selector.push(items[i]);
                                            }
                                        }
                                    } else {
                                        selector.push(items[i]);
                                    }
                                }

                            } else {
                                selector = null;
                            }
                        }
                    }
                    if ('refresh' === args.type || self.is_ajax === true) {
                        api.activeModel.trigger('ajax', self.settings, selector, val);
                    } else {
                        api.activeModel.trigger('live', self.settings, args.name === 'wp_editor' || el.tagName === 'TEXTAREA', null, selector, val);
                    }
                } else if(self.clicked === 'setting') {
                    api.activeModel.backendLivePreview(self.settings);
                }
            },
            change(el, args) {
                const event=args.event || 'change';
                let timout;
                el.tfOn(event, e=>{
                    let timer = 50;
                    if (e.type === 'change') {
                        timer = 1;
                    } else if('refresh' === args.type && args.selector === und){
                        timer = 1000;
                    }
                    if(timout){
                        clearTimeout(timout);
                    }
                    timout=setTimeout(()=>{
                        const target = e.target;
                        let value=target.value;
                        if ('keyup' === e.type) {
                            if (value === target.dataset.oldValue) {
                                return;
                            } 
                            else {
                                target.dataset.oldValue = value;
                            }
                        }
                        if(target.nodeName==='SELECT' && target.multiple){
                            value = [];
                            for ( let selected=target.selectedOptions,i = 0,len=selected.length; i<len; ++i ) {
                                value.push( target.selectedOptions[ i ].value );
                            }
                        }
                        this.preview(target, value, args);
                        timout=null;
                    }, timer);
                }, {passive: true});
            },
            wp_editor(el, args) {
                let that = this,
                        id = el.id,
                        previous = false,
                        interval,
                        is_widget = false,
                        callback = function (e) {
                            const timer = 'refresh' === args.type && args.selector === und ? 1000 : 50,
                                    content = typeof this.getContent==='function'?this.getContent():this.value;
                            if(interval){
                                clearTimeout(interval);
                            }
                            if (api.activeModel === null || previous === content) {
                                return;
                            }
                            previous = content;
                            if (is_widget !== false) {
                                if( !args.id){
                                    const _id=this.id.split('_');
                                    if(_id[1]){
                                        const widgetText=api.LightBox.el.querySelector('textarea.sync-input[name="'+_id[1]+'"]');
                                        if(widgetText){
                                            args.id=_id[1];
                                        }
                                    }
                                }
                                if(args.id){
                                    const widgetText=api.LightBox.el.querySelector('textarea.sync-input[name="'+args.id+'"]');
                                    if(widgetText && widgetText!==this){
                                        widgetText.value=content;
                                        Themify.triggerEvent(widgetText,'change');
                                        return;
                                    }
                                }
                            }
                            if (!this.targetElm) {
                                if (typeof tinyMCE !== 'undefined') {
                                    const t=tinymce.get(this.id);
                                    if(t){
                                        t.setContent(content);
                                    }
                                }
                                that.preview(this, content, args);
                            } 
                            else {
                                that.preview(this.targetElm, content, args);
                            }
                            interval=null;
                        },
                        messages=api.LightBox.el.querySelectorAll('.wrap > #message');
                
                /* change the ID on WP's #message element.
                 * Patches an issue with Optin due to similar IDs
                 */
                for(let i=messages.length-1;i>-1;--i){
                    messages[i].id='wp-message';
                }
                // add quicktags
                if (typeof topWindow.QTags === 'function') {
                    topWindow.quicktags({
                        id: id
                    });
                    topWindow.QTags._buttonsInit();
                }
                if (typeof tinyMCE !== 'undefined') {
                    if (tinymce.editors[ id ] !== und) { // clear the prev editor
                        tinyMCE.execCommand('mceRemoveEditor', true, id);
                    }
                    const settings = tinyMCEPreInit.mceInit.tb_lb_hidden_editor;
                    settings.target = ThemifyConstructor.getEl(id.replace('#',''));
                    // Creates a new editor instance
                    const ed = new tinyMCE.Editor(id, settings, tinyMCE.EditorManager);
                    is_widget = el.classList.contains('wp-editor-area') ?( el.closest('#instance_widget')!==null) : false;
                    ed.render();
                    ed.on('change', callback);
                    if(!is_widget){
                        ed.on('keyup', callback);
                    }
                }
                el.tfOn('change keyup', callback,{passive:true});
            },
            layout(el, args) {
                if ('visual' === api.mode) {
                    const that = this;
                    el.tfOn('change', function (e) {
                        let selectedLayout = e.detail.val.toString();
                        if (selectedLayout.indexOf('grid-') === 0) {
                            selectedLayout = selectedLayout.replace('grid-', 'grid');
                        }
						else if (!isNaN(selectedLayout)) {
                            selectedLayout ='grid' + selectedLayout;
                        }
                        if (args.classSelector !== und && selectedLayout !== 'auto_tiles') {
                            let id = args.id,
                                apllyTo = args.classSelector !== '' ? api.liveStylingInstance.el.querySelector(args.classSelector) : api.liveStylingInstance.el.tfClass('module')[0],
                                prevLayout = ThemifyConstructor.settings[id];
                            if (prevLayout) {
                                prevLayout=prevLayout.toString();
                                if (prevLayout.indexOf('grid-') === 0) {
                                    prevLayout = prevLayout.replace('grid-', 'grid');
                                } else if (!isNaN(prevLayout)) {
                                    prevLayout = 'grid' + prevLayout;
                                }
                            }
                            ThemifyConstructor.settings[id] = selectedLayout;
                            if (apllyTo) {
                                apllyTo=apllyTo.classList;
                                if(prevLayout){
									if(prevLayout==='grid1'){
										prevLayout='list-post';
									}
                                    apllyTo.remove(prevLayout);
                                }
                                if(selectedLayout){
									if(selectedLayout==='grid1'){
										selectedLayout='list-post';
									}
                                    apllyTo.add(selectedLayout);
                                }
                                api.Utils.runJs(api.liveStylingInstance.el, 'module');
                            } else {
                                that.preview(this, selectedLayout, args);
                            }

                        } else {
                            that.preview(this, selectedLayout, args);
                        }
                    }, {passive: true});
                }
            },
            icon(el, args) {
                const that = this;
                el.tfOn('change', function (e) {
                    const v = e.target.value,
                            prev = this.closest('.tb_field').tfClass('themify_fa_toggle')[0];
                    if (prev !== und) {
                        if (prev.firstChild) {
                            prev.firstChild.remove();
                        }
                        if (v) {
                            prev.appendChild(api.Helper.getIcon(v));
                        }
                    }
                    that.preview(e.target, v, args);
                }, {passive: true});
            },
            checkbox(el, args) {
                if (api.mode === 'visual') {
                    const that = this;
                    el.tfOn('change', function () {
                        const checked = [],
                                checkbox = this.closest('.tb_checkbox_wrap').tfClass('tb_checkbox');
                        for (let i = 0, len = checkbox.length; i < len; ++i) {
                            if (checkbox[i].checked) {
                                checked.push(checkbox[i].value);
                            }
                        }
                        that.preview(this, checked.join('|'), args);
                    }, {passive: true});
                }
            },
            color(el, args) {
                if (api.mode === 'visual') {
                    const that = this;
                    el.tfOn('themify_builder_color_picker_change', function (e) {
                        that.preview(this, e.detail.val, args);
                    }, {passive: true});
                }
            },
            widget_select(el, args) {
                this.preview(el, api.Forms.themifySerializeObject(el), args);
            },
            queryPosts(el, args) {
                if (api.mode === 'visual') {
                    const that = this;
                    el.tfOn('queryPosts', function (e) {
                        args.id = this.id;
                        ThemifyConstructor.settings = api.Helper.clear(api.Forms.serialize('tb_options_setting'));
                        that.preview(this, ThemifyConstructor.settings[args.id], args);
                    }, {passive: true});
                }
            }
        },
        initControl(el, data) {
            if (api.activeModel !== null) {
                if (this.clicked === 'setting' && data.type !== 'custom_css') {
                    if (data.control !== false && this.component === 'module') {
                        const args = data.control || {};
                        let type = data.type;
                        if (args.repeat === true) {
                            args.id = el.dataset.inputId;
                        } else {
                            if (this.is_repeat === true) {
                                args.repeat = true;
                                args.id = el.dataset.inputId;
                            } else {
                                args.id = data.id;
                            }
                        }

                        if (args.control_type === und) {
                            if (type === und || type === 'text' || type === 'number' || type==='taxonomy' || type === 'url' || type === 'autocomplete' || type === 'range' || type === 'radio' || type === 'icon_radio' || type === 'select' || type === 'gallery' || type === 'textarea' || type === 'address' || type === 'image' || type === 'file' || type==='lottie'|| type === 'date' || type === 'audio' || type === 'video'  || type === 'widgetized_select' || type === 'layoutPart' || type === 'selectSearch' || type === 'hidden' || type === 'toggle_switch' || type === 'slider_range') {
                                if (args.event === und && (type === 'text' || type === 'textarea')) {
                                    args.event = 'keyup';
                                }
                                type = 'change';
                            }
                        } else {
                            type = args.control_type;
                        }
                        this.control.init(el, type, args);
                    }
                } else if (api.mode === 'visual' && this.clicked === 'styling') {
                    api.liveStylingInstance.bindEvents(el, data);
                }
                if (data.binding !== und) {
                    const is_repeat = this.is_repeat === true,
                            callback =  (_this, v)=> {
                                const context = is_repeat?el.closest('.tb_sort_field_dropdown,.tb_toggleable_fields_options,.tb_repeatable_field_content'):und;
                                this.binding(_this, data, v, context);
                            };
                    if (data.type === 'layout' || data.type === 'frame') {
                        el.tfOn(Themify.click, function (e) {
                            const t = e.target.closest('.tfl-icon');
                            if (t !== null) {
                                callback(this, t.id);
                            }
                        }, {passive: true});
                    } else {
                        el.tfOn('change', function (e) {
                            callback(this, this.value);
                        }, {passive: true});
                    }
                    this.bindings.push({el: el, data: data, repeat: is_repeat});
                }
            }
            return el;
        },
        callbacks() {
            let len;
            if (this.afterRun !== null) {
                len = this.afterRun.length;
                if (len > 0) {
                    for (let i = 0; i < this.afterRun.length; ++i) {
                        this.afterRun[i].call();
                    }
                    this.afterRun = [];
                }
            }
            if (this.radioChange !== null) {
                len = this.radioChange.length;
                if (len > 0) {
                    for (let i = 0; i < this.radioChange.length; ++i) {
                        this.radioChange[i].call();
                    }
                    this.radioChange = [];
                }
            }
            if (this.bindings !== null) {
                len = this.bindings.length;
                if (len > 0) {
                    for (let i = len - 1; i > -1; --i) {
                        if(this.bindings[i].data!==und){
                            let el = this.bindings[i].el,
                                    context = this.bindings[i].repeat === true ? el.closest('.tb_sort_field_dropdown,.tb_toggleable_fields_options,.tb_repeatable_field_content') : und,
                                    v = this.bindings[i].data.type === 'layout' || this.bindings[i].data.type === 'frame' ? el.tfClass('selected')[0].id : el.value;
                            this.binding(el, this.bindings[i].data, v, context);
                        }
                    }
                    this.bindings = [];
                }
            }
        },
        setUpEditors() {
            for (let i = this.editors.length - 1; i > -1; --i) {
                this.initControl(this.editors[i].el, this.editors[i].data);
            }
            this.editors = [];
        },
        switchTabs(e) {
            const id = '#'+this.dataset.id,
                    li = this.parentNode,
                    p = li.parentNode,
                    tabs = p.parentNode;

            let container = tabs.querySelector(id);
            if (!container || container.parentNode !== tabs) {
                container = this.getRootNode().querySelector(id);
            }
            if (!container || li.classList.contains('current')) {
                return;
            }
            const children = p.children,
                    containerChildren = container.parentNode.children;
            for (let i = children.length - 1; i > -1; --i) {
                children[i].classList.remove('current');
            }
            li.classList.add('current');
            for (let i = containerChildren.length - 1; i > -1; --i) {
                if (containerChildren[i].classList.contains('tb_tab')) {
                    containerChildren[i].style.display = 'none';
                }
            }
            container.style.display = 'block';
            const tabId=id.replace('#tb_options_', '');
            Themify.trigger('tb_builder_tabsactive', [tabId, container]);
            Themify.triggerEvent(container, 'tb_builder_tabsactive', {id: tabId});

        },
        run(options) {
            this.styles = {};
            this.settings = {};
            this.editors = [];
            this.afterRun = [];
            this.radioChange = [];
            this.bindings = [];
            this.stylesData = {};
            this.is_repeat = null;
            this.is_sort = null;
            this.component = null;
            this.is_new = null;
            this.is_ajax = null;
            this.type=null;
            this.data=options;
            let defaultTab,
                model = api.activeModel;
            if (model !== null) {
                this.type=model.get('mod_name');
                this.component = model.type;
                if (this.component === 'module') {
                    this.is_ajax = model.getPreviewType()=== 'ajax';
                    this.is_new = !!model.is_new;
                }
                this.values = api.Helper.cloneObject(model.get('mod_settings'));
                defaultTab=model.tab || 'setting';
                delete model.tab;
                if (this.data.visibility === und) {
                    this.data.visibility = true;
                }
                if (this.data.animation === und) {
                    this.data.animation = true;
                }
            } 
            else {
                this.values = {};
                this.component = null;
            }
            const top_bar = doc.createDocumentFragment(),
                container = doc.createDocumentFragment(),
                self = this,
                tabIcons = {styling: 'ti-brush', animation: 'ti-layers-alt', visibility: 'ti-eye'},
                createTab = (index, options) => {

                    const fr = doc.createDocumentFragment();
                    if (index === 'visibility' || index === 'animation') {
                        options = this.static[index];
                    }
                    else if (index === 'styling' && api.LightBox.el.tfClass('tb_styling_tab_header')[0]===und) {
                        const div = doc.createElement('div'),
                            globalStylesHTML = api.GS.globalStylesHTML();
                        div.className = 'tb_styling_tab_header';
                        div.appendChild(this.getSwitcher());
                        if (globalStylesHTML) {
                            div.appendChild(globalStylesHTML);
                        }
                        fr.appendChild(div);
                    }
                    // generate html output from the options
                    fr.appendChild(this.create(options));
                    if (index === 'styling') {
                        const reset = doc.createElement('a'),
                                icon = doc.createElement('i');
                        reset.href = '#';
                        reset.className = 'reset-styling';
                        icon.className = 'tf_close';
                        reset.append(icon,doc.createTextNode(this.label.reset_style));
                        reset.tfOn(Themify.click, e=>{
                            e.stopPropagation();
                            e.preventDefault();
                            this.resetStyling(api.activeModel);
                        });
                        fr.appendChild(reset);
                        if (api.mode === 'visual' && model) {
                            setTimeout(() => {
                                api.liveStylingInstance.module_rules = this.styles;//by reference,will be fill when the option has been viewed
                            }, 600);
                        }
                    }
                    options = null;
                    return fr;
                },
                tabSwitch=function (e) {
                    const index = e.detail.id.replace('#tb_options_', '');
                    self.clicked = index;
                    if (this.dataset.done === und) {
                        this.dataset.done = true;
                        this.appendChild(createTab(index, self.data[index].options));
                        self.callbacks();
                        if (index === 'setting') {
                            self.setUpEditors();
                        }
                        
                        Themify.trigger('tb_editing_' + self.type+'_'+index,api.LightBox.el);
                    }
                };

                this.clicked = null;
                for (let k in this.data) {
                    if (this.data[k] === false) {
                        continue;
                    }
                    //meneu
                    let li = doc.createElement('li'),
                        a = doc.createElement('a'),
                        tooltip = doc.createElement('span'),
                        wrapper = doc.createElement('div'),
                        label = this.data[k].name !== und ? this.data[k].name : this.label[k],
                        tab_id = 'tb_options_' + k;
                    a.href ='javascript:;';
                    a.dataset.id= tab_id;
                    a.textContent = label;
                    if (k !== 'setting') {
                        a.className = 'tb_tooltip';
                        tooltip.textContent = label;
                        if (tabIcons[k]) {
                            a.appendChild(api.Helper.getIcon(tabIcons[k]));
                        }
                        a.appendChild(tooltip);
                    }
                    wrapper.id = tab_id;
                    wrapper.className = 'tb_tab tb_options_tab_wrapper tf_rel tf_box tf_w tf_hide';
                    if (defaultTab===k || defaultTab===und) {
                        li.className = 'current';
                        this.clicked = k;
                        if (this.data[k].html !== und) {
                            wrapper.innerHTML=this.data[k].html;
                        } else {
                            wrapper.appendChild(createTab(k, this.data[k].options));
                        }

                        wrapper.style.display = 'block';
                        wrapper.dataset.done = true;
                    }
                    wrapper.tfOn('tb_builder_tabsactive',tabSwitch, {passive: true});
                    a.tfOn(Themify.click, this.switchTabs,{passive:true});
                    container.appendChild(wrapper);
                    li.appendChild(a);
                    top_bar.appendChild(li);
                }
            const top = api.LightBox.el.tfClass('tb_options_tab')[0],
                changeMode=(prevbreakpoint, breakpoint)=>{
                    this.updateStyles(prevbreakpoint, breakpoint);
                };
            if (top) {
                while (top.firstChild !== null) {
                    top.lastChild.remove();
                }
                top.appendChild(top_bar);
            }
            Themify.on('themify_builder_lightbox_close', () => {
                this.radioChange = this.afterRun = this.bindings = this.editors = [];
                this.stylesData = this.settings = this.styles = {};
                this.is_ajax = this.is_repeat = this.is_sort = this.clicked = null;
                if (typeof tinyMCE !== 'undefined') {
                    for (let i = tinymce.editors.length - 1; i > -1; --i) {
                        if (tinymce.editors[i].id !== 'content') {
                            tinyMCE.execCommand('mceRemoveEditor', true, tinymce.editors[i].id);
                        }
                    }
                }
                Themify.off('themify_builder_change_mode',changeMode);
                model=this.type = this.component = this.is_new =this.data= null;
                this.values = {};
                this.tabs.click = 0;
                this.tabs.styleClicked=false; 
            }, true)
            .on('themify_builder_change_mode', changeMode);

            setTimeout(() => {
                if (this.clicked === 'setting') {
                    this.setUpEditors();
                }
                this.callbacks();
                Themify.trigger('tb_editing_' + this.type+'_'+this.clicked,api.LightBox.el);
                /**
                 * self.type is the module slug, trigger a separate event for all modules regardless of their slug
                 */
                Themify.trigger('tb_editing_'+this.component,api.LightBox.el);
            }, 5);
            
            return container;
        },
        getStyleVal(id, bp, vals) {
            let v=und;
            if (id!==und && id !== '' && api.activeModel !== null) {
                if (vals === und) {
                    vals = this.values;
                }
                if (bp === und) {
                    bp = api.activeBreakPoint;
                }
                if (bp === 'desktop' || this.clicked !== 'styling') {
                    if(vals !== null && vals[id] !== ''){
                        v=vals[id];
                    }
                }
                else{
                    if (vals['breakpoint_' + bp] !== und && vals['breakpoint_' + bp][id] !== und && vals['breakpoint_' + bp][id] !== '') {
                        v =vals['breakpoint_' + bp][id];
                    }
                    else{
                        const points = this.breakpointsReverse;
                        for (let i = points.indexOf(bp) + 1, len = points.length; i < len; ++i) {
                            if (points[i] !== 'desktop') {
                                if (vals['breakpoint_' + points[i]] !== und && vals['breakpoint_' + points[i]][id] !== und && vals['breakpoint_' + points[i]][id] !== '') {
                                    v=vals['breakpoint_' + points[i]][id];
                                    break;
                                }
                            } 
                            else if (vals[id] !== und && vals[id] !== '') {
                                // Check for responsive disable
                                const binding_data = this.stylesData && und !== this.stylesData[id] ? this.stylesData[id].binding : und;
                                if (und !== binding_data && und !== binding_data[vals[id]] && und !== binding_data[vals[id]].responsive && und !== binding_data[vals[id]].responsive.disabled && -1 !== binding_data[vals[id]].responsive.disabled.indexOf(id)) {
                                    v= und;
                                }
                                else{
                                    v=vals[id];
                                }
                                break;
                            }
                        }
                    }
                }
                if ((v===und || v==='') && id.endsWith('_unit')&& id.indexOf('frame_') === -1) {//because in very old version px wasn't saved and we can't detect after removing it was px value or not
                    v= 'px';
                }
                //for columns it can be "value1,value2" where "value1" is value for v5, "value2" is for v7
                else if ( v !== und && v !== ''  && api.activeModel.type === 'column' && v.toString().indexOf(',') !== -1 && (id.indexOf('padding') === 0 || id.indexOf('margin') !== -1 ) ) {
                    v=v.trim().split(',');
                    if(v[1]!==und && v[1]!==''){
                        v=v[1];
                    }
                    else{
                        v=v[0];
                    }
                    v=v.trim();
                }
            }
            return v;
        },
        updateStyles(prevbreakpoint, breakpoint) {
            this.setStylingValues(prevbreakpoint);
            const old_tab = this.clicked;
            this.clicked = 'styling';
            for (let k in this.stylesData) {
                let type = this.stylesData[k].type;
                if (type && type !== 'video' && type !== 'gallery' && type !== 'autocomplete' && type !== 'custom_css' && type !== 'builder' && this.stylesData[k].is_responsive !== false) {
                    if (type === 'icon_radio') {
                        type = 'radio';
                    } else if (type === 'icon_checkbox') {
                        type = 'checkbox';
                    } else if (type === 'textarea' || type === 'icon' || type === 'hidden' || type === 'number') {
                        type = 'text';
                    } else if (type === 'image') {
                        type = 'mediaFile';
                    } else if (type === 'padding' || type === 'border_radius') {
                        type = 'margin';
                    } else if (type === 'frame') {
                        type = 'layout';
                    }
                    let v = this.getStyleVal(k);
                    this[type].update(k, v, this);
                    if (this.stylesData[k].binding !== und) {
                        let items = this.getEl(k),
                                res = [];
                        if (type === 'layout') {
                            res = items.tfClass('tfl-icon');
                        } else if (type === 'radio' || type === 'checkbox') {
                            res = items.tfTag('input');
                        } else {
                            res = [items];
                        }
                        let data = this.stylesData[k];
                        for (let i = 0, len = res.length; i < len; ++i) {
                            this.binding(res[i], data, v);
                        }
                    }
                }
            }
            //Disable responsive disable options
            const disabled_options = topWindow.document.querySelectorAll('#tb_options_styling option.tb_responsive_disable');
            for (let j = disabled_options.length - 1; j >= 0; j--) {
                disabled_options[j].disabled = 'desktop' !== breakpoint;
            }
            this.clicked = old_tab;
        },
        setStylingValues(breakpoint) {
            const data = api.Forms.serialize('tb_options_styling', true),
                    isDesktop = breakpoint === 'desktop';
            if (isDesktop === false && this.values['breakpoint_' + breakpoint] === und) {
                this.values['breakpoint_' + breakpoint] = {};
            }
            for (let i in data) {
                if (isDesktop === true) {
                    this.values[i] = data[i];
                } else {
                    this.values['breakpoint_' + breakpoint][i] = data[i];
                }
            }
        },
        resetStyling(model) {
            const type = model.get('mod_name');
            // Reset GS
            if (api.isGSPage === false && api.GS.activeGS === null) {
                
                if(model===api.activeModel){
                    const field=this.getEl(api.GS.key);
                    if(field && field.value){
                        const vals=field.value.split(' '),
                                bar=field.parentNode.querySelector('tb-gs');
                        for(let i=vals.length-1;i>-1;--i){
                            bar.delete(vals[i]);
                        }
                    }
                }
                else{
                    api.GS.setGsStyle([],true,model);
                }
                
            }
            if (api.mode === 'visual') {
                let live=api.liveStylingInstance;
                if(!live || live.el!==model.el){
                    live=api.createStyleInstance();
                    live.init(true, false,model);
                }
                const prefix = live.prefix,
                        points = this.breakpointsReverse;
                for (let i = points.length - 1; i > -1; --i) {
                    let stylesheet = ThemifyStyles.getSheet(points[i], api.GS.activeGS !== null),
                        rules = stylesheet.cssRules || stylesheet.rules;
                    if (rules.length > 0) {
                        for (let j = rules.length - 1; j > -1; --j) {
                            if (rules[j].selectorText.indexOf(prefix) !== -1) {
                                let css = rules[j].cssText.split('{')[1].split(';');

                                for (let k = css.length - 2; k > -1; --k) {
                                    let prop = css[k].trim().split(': ')[0].trim();
                                    if (rules[j].style[prop] !== und) {
                                        rules[j].style[prop] = '';
                                    }
                                }
                            }
                        }
                    }
                }
                if (model.type !== 'module') {
                    live.removeBgSlider();
                    live.removeBgVideo();
                    live.removeFrames();
                    live.bindBackgroundMode('repeat', 'background_repeat');
                    live.bindBackgroundMode('repeat', 'b_r_h');
                    live.el.removeAttribute('data-tb_slider_videos');
                    const video=live.el.querySelector(':scope>.tb_slider_videos'),
                        overlay=live.getComponentBgOverlay(type);
              
                    if(video){
                        video.remove();
                    }
                    if(overlay){
                        overlay.remove();
                    }
                }
                live=null;
            }
                
            const styleFields = ThemifyStyles.getStyleOptions(type),
                values=model===api.activeModel?this.values:model.get('styling');

            for (let i in values) {
                let key = i.indexOf('_color') !== -1 ? 'color' : (i.indexOf('_style') !== -1 ? 'style' : false),
                        remove = null;
                if (i.indexOf('breakpoint_') === 0 || i === api.GS.key || styleFields[i] !== und || i.indexOf('_apply_all') !== -1) {
                    remove = true;
                } 
                else if (i.indexOf('_unit') !== -1) {//unit
                    key = i.replace(/_unit$/ig, '', '');
                    if (styleFields[key] !== und) {
                        remove = true;
                    }
                }
                else if (i.indexOf('_w') !== -1) {//weight
                    key = i.replace(/_w$/ig, '', '');
                    if (styleFields[key] !== und && styleFields[key].type === 'font_select') {
                        remove = true;
                    }
                } 
                else if (key !== false) {
                    key = i.replace('_' + key, '_width');
                    if (styleFields[key] !== und && styleFields[key].type === 'border') {
                        remove = true;
                    }
                }
                if (remove === true) {
                    delete values[i];
                }
            }
            if(model===api.activeModel){
                const tabId='styling',
                container=api.LightBox.el.querySelector('#tb_options_'+tabId);
                if(container){
                    for(let childs=container.children,i=childs.length-1;i>-1;--i){
                        if(!childs[i].classList.contains('tb_styling_tab_header')){
                            childs[i].remove();
                        }
                    }
                    container.removeAttribute('data-done');
                    Themify.triggerEvent(container, 'tb_builder_tabsactive', {id: tabId});
                }
            }
        },
        create(data) {
            const content = doc.createDocumentFragment();
            if (data === und || data.length === 0) {
                const info = doc.createElement('div'),
                        infoText = doc.createElement('p');
                infoText.textContent = themifyBuilder.i18n.no_op_module;
                info.appendChild(infoText);
                content.appendChild(info);
                return content;
            }
            if (data.type === 'tabs') {
                content.appendChild(this.tabs.render(data, this));
            } else {
                for (let i in data) {
                    if (data[i].hide === true || data[i].type === und || ('visibility' === this.clicked && 'row' === this.component && 'sticky_visibility' === data[i].id)) {
                        continue;
                    }
                    let type = data[i].type,
                            res = this[type].render(data[i], this);
                    if (type !== 'separator' && type !== 'expand' && type !== 'group') {
                        let id = data[i].id ? data[i].id : data[i].topId;
                        if (type !== 'tabs' && type !== 'multi' && type !== 'margin_opposity') {
                            if (id) {
                                if (this.clicked === 'styling') {
                                    if (api.mode === 'visual' && data[i].prop !== und) {
                                        this.styles[id] = api.Helper.cloneObject(data[i]);
                                    }
                                    this.stylesData[id] = api.Helper.cloneObject(data[i]);
                                } else if ( this.clicked === 'setting' && this.values !== null && this.values[id] !== und && this.is_repeat !== true) {
                                    this.settings[id] = this.values[data[i].id];
                                    if (data[i].units !== und && this.values[id + '_unit'] !== und) {
                                        this.settings[id + '_unit'] = this.values[id + '_unit'];
                                    }
                                }
                            }
                        }
                        if (type !== 'hook_content' && type !== 'slider' && type !== 'builder' && type !== 'tooltip' && type !== 'custom_css_id') {
                            let field = doc.createElement('div');
                            field.className = 'tb_field';
                            if (data[i].dc !== und && !data[i].dc) {
                                field.className += ' tb_disable_dc';
                            }
                            field.dataset.type=type;
                            if (id !== und) {
                                field.className += ' ' + id;
                            }
                            if (data[i].wrap_class !== und) {
                                field.className += ' ' + data[i].wrap_class;
                            }
                            if (type === 'toggle_switch') {
                                field.className += ' switch-wrapper';
                            } else if (type === 'slider') {
                                field.className += ' tb_slider_options';
                            } else if (type === 'message' && data[i].hideIf !== und && new Function('return ' + data[i].hideIf)) {
                                field.className += ' tb_hide_option';
                            } else if (data[i].required !== und && this.clicked === 'setting') {// validation rules
                                field.dataset.validation=data[i].required.rule || 'not_empty';
                                field.dataset.errorMsg=data[i].required.message !== und ? data[i].required.message : themifyBuilder.i18n.not_empty;
                                field.className += ' tb_must_validate';
                            }
                            if (this.clicked === 'styling' && data[i].is_responsive === false) {
                                field.className += ' tb_responsive_disable';
                            }

                            let txt = this.getTitle(data[i]);
                            if (txt !== false) {
                                txt = txt.trim();
                                let label = doc.createElement('div');
                                label.className = 'tb_label';
                                if (txt === '') {
                                    label.className += ' tb_empty_label';
                                }
                                else{
                                    label.textContent = txt;
                                }

                                if (data[i].help !== und && data[i].label !== '') {
                                    label.classList.add('contains-help');
                                    label.appendChild(this.help(data[i].help));
                                }
                                field.appendChild(label);
                                if (type !== 'multi') {
                                    let input = doc.createElement('div');
                                    input.className = 'tb_input';
                                    input.appendChild(res);
                                    field.appendChild(input);
                                } else {
                                    field.appendChild(res);
                                }
                            } else {
                                field.appendChild(res);
                            }
                            content.appendChild(field);
                        } else {
                            content.appendChild(res);
                        }
                    } else {
                        content.appendChild(res);
                    }
                }
            }
            data = null;
            return content;
        },
        tabs: {
            click: 0,
            styleClicked:false,
            render(data, self) {
                const items = data.options,
                        tabs_container = doc.createDocumentFragment(),
                        nav = doc.createElement('ul'),
                        stickyWraper = (self.clicked === 'styling' && this.styleClicked === false && self.component === 'module') ? doc.createElement('div') : null,
                        tabs = doc.createElement('div'),
                        isRadio = data.isRadio !== und;
                let v = null,
                        first = null;
                tabs.className = 'tb_tabs tf_rel tf_w';
                nav.className = 'tb_tab_nav tf_scrollbar';
                if(data.class){
                    tabs.className+=' '+data.class;
                }
                if(self.clicked === 'styling' ){
                    this.styleClicked=true;;
                }
                ++this.click;


                if (isRadio === true) {
                    if (self.values[data.id] !== und && self.values[data.id] !== '') {
                        first = true;
                        v = self.values[data.id];
                    }
                    nav.className += ' tb_radio_wrap';
                    if (self.is_repeat === true) {
                        nav.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                        nav.dataset.inputId = data.id;
                    } else {
                        nav.className += ' tb_lb_option';
                        nav.id = data.id;
                    }
                }
                for (let k in items) {
                    let li = doc.createElement('li'),
                            a = isRadio === true ? doc.createElement('label') : doc.createElement('a'),
                            div = doc.createElement('div'),
                            id = items[k].href !== und ? items[k].href : ('tb_' + this.click + '_' + k),
                            opt = items[k].options;

                    div.id = id;
                    div.className = 'tb_tab tf_hide';
                    if (items[k].label !== '') {
                        a.textContent = items[k].label !== und ? items[k].label : self.label[k];
                    }
                    if (items[k].icon !== und) {
                        a.appendChild(api.Helper.getIcon(items[k].icon));
                    }
                    if (items[k].title !== und) {
                        a.title = items[k].title;
                    }
                    if (items[k].class !== und && items[k].class !== '') {
                        a.className = items[k].class;
                    }
                    a.dataset.id= id;
                    if (isRadio === true) {
                        let input = doc.createElement('input');
                        input.type = 'radio';
                        input.className = 'tb_radio_tab_input';
                        input.name = data.id;
                        if (v === k || v === 'tb_' + k) {
                            input.checked = true;
                        }
                        input.value = k;
                        a.className = 'tb_radio_tab_label';
                        a.appendChild(input);
                        a.tfOn('change', self.switchTabs,{passive:true});
                    } else {
                        a.href ='javascript:;';
                        a.tfOn(Themify.click, self.switchTabs,{passive:true});
                    }
                    if (first === null || v === k || v === 'tb_' + k) {
                        first = true;
                        li.className = 'current';
                        div.appendChild(self.create(opt));
                        opt = null;
                        div.style.display = 'block';

                    } else {
                        div.tfOn('tb_builder_tabsactive', function () {
                            this.appendChild(self.create(opt));
                            if (self.clicked === 'setting') {
                                self.setUpEditors();
                            }
                            self.callbacks();
                            opt = null;
                        }, {once: true, passive: true});
                    }
                   
                    li.appendChild(a);
                    nav.appendChild(li);
                    tabs_container.appendChild(div);
                }
                if (stickyWraper !== null) {
                    stickyWraper.className = 'tb_styling_tab_nav';
                    stickyWraper.appendChild(nav);
                    tabs.appendChild(stickyWraper);
                } else {
                    tabs.appendChild(nav);
                }
                tabs.appendChild(tabs_container);
                setTimeout(self.callbacks.bind(self), 5);
                return tabs;
            }
        },
        group: {
            render(data, self) {
                const wr = doc.createElement('div'),
                        options = self.create(data.options);
                if (data.wrap_class !== und) {
                    wr.className = data.wrap_class;
                }
                wr.classList.add('tb_field_group');
				if ( data.id ) {
					wr.classList.add( data.id );
				}
                if (data.display === 'accordion') {
                    wr.classList.add('tb_field_group_acc');
                    const label = doc.createElement('div'),
                            content = doc.createElement('div'),
                            icon = api.Helper.getIcon('ti-angle-up');
                    label.textContent = data.label;
                    label.className = 'tb_style_toggle tb_closed';
                    label.appendChild(icon);
                    content.className = 'tf_hide tb_field_group_content';
                    content.appendChild(options);
                    label.tfOn(Themify.click,  () =>{
                        $(content).slideToggle();
                        label.classList.toggle('tb_closed');
                    });
                    wr.append(label, content);
                } else {
                    wr.appendChild(options);
                }
                return wr;
            }
        },
        builder: {
            render(data, self) {
                self.is_repeat = true;
                const fr = doc.createDocumentFragment(),
                        wrapper = doc.createElement('div'),
                        add_new = doc.createElement('a'),
                        _this = this;
                wrapper.className = 'tb_row_js_wrapper tf_rel tb_lb_option';
                if (data.wrap_class !== und) {
                    wrapper.className += ' ' + data.wrap_class;
                }
                wrapper.id = data.id;
                add_new.className = 'add_new tf_plus_icon tf_icon_btn tf_rel';
                add_new.href = '#';
                add_new.textContent = data.new_row || self.label.new_row;
                if (self.values[data.id] !== und) {
                    const values = self.values[data.id].slice(),
                            orig = api.Helper.cloneObject(self.values);
                    for (let i = 0, len = values.length; i < len; ++i) {
                        self.values = values[i] || {};
                        wrapper.appendChild(this.builderFields(data, self));
                    }
                    self.values = orig;
                } else {
                    wrapper.appendChild(this.builderFields(data, self));
                }
                wrapper.appendChild(add_new);
                fr.appendChild(wrapper);
                setTimeout(() => {
                    _this.sortable(wrapper, self);
                    add_new.tfOn(Themify.click, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        self.is_repeat = true;
                        const item = _this.builderFields(data, self),
                                container = this.previousElementSibling.parentNode;
                        container.insertBefore(item, add_new);
                        setTimeout(() => {
                            if (self.clicked === 'setting') {
                                self.setUpEditors();
                            }
                            self.callbacks();
                            Themify.triggerEvent(doc, 'tb_repeatable_add_new', [item]);
                        }, 5);
                        self.control.preview(container, null, {repeat: true});
                        self.is_repeat = null;
                    });
                }, 2000);
                self.is_repeat = null;
                return fr;
            },
            builderFields(data, self) {
                const repeat = doc.createElement('div'),
                        top = doc.createElement('div'),
                        menu = doc.createElement('div'),
                        icon = doc.createElement('div'),
                        ul = doc.createElement('ul'),
                        _duplicate = doc.createElement('li'),
                        _delete = doc.createElement('li'),
                        toggle = doc.createElement('div'),
                        content = doc.createElement('div'),
                        up=doc.createElement('div'),
                        down=doc.createElement('div'),
                        _this = this;

                repeat.className = 'tb_repeatable_field tf_clearfix';
                top.className = 'tb_repeatable_field_top tf_rel tf_box tf_textl';
                menu.className = 'row_menu';
                icon.tabIndex = '-1';
                icon.className = 'menu_icon';
                ul.className = 'tb_down';
                _duplicate.className = 'tb_duplicate_row';
                _delete.className = 'tb_delete_row tf_close';
                _duplicate.textContent = self.label.duplicate;
                _delete.textContent = self.label.delete;
                toggle.className = 'tb_arrow tb_toggle_row';
                content.className = 'tb_repeatable_field_content';
                up.className='tb_arrow tb_up_row';
                down.className='tb_arrow tb_down_row';
                up.title=self.label.up;
                down.title=self.label.down;
                content.appendChild(self.create(data.options));
                ul.append(_duplicate, _delete);
                menu.append(icon, ul);
                top.append(menu,up,down, toggle);
                repeat.append(top, content);
                top.tfOn(Themify.click, function (e) {
                    const cl = e.target.classList,
                            repeatContainer = this.parentNode;
                    if (cl.contains('tb_delete_row')) {
                        api.LiteLightBox.confirm({msg:themifyBuilder.i18n.repeatRowDeleteConfirm}).then(answer=>{
                            if ('yes' === answer) {
                                const  p = e.target.closest('.tb_row_js_wrapper');
                               _this.delete(e.target);
                               self.control.preview(p, null, {repeat: true});
                               Themify.triggerEvent(p, 'delete');
                           }
                        });
                    } 
                    else if (cl.contains('tb_duplicate_row')) {
                        self.is_repeat = true;
                        const orig = api.Helper.cloneObject(self.values);
                        self.values = api.Forms.serialize(repeatContainer, true, true);
                        const item = _this.builderFields(data, self);
                        repeatContainer.parentNode.insertBefore(item, repeatContainer.nextElementSibling);
                        self.values = orig;
                        setTimeout(() => {
                            if (self.clicked === 'setting') {
                                self.setUpEditors();
                            }
                            self.callbacks();
                            Themify.triggerEvent(repeatContainer.parentNode, 'duplicate');
                            Themify.triggerEvent(doc, 'tb_repeatable_duplicate', [item]);
                        }, 5);
                        self.control.preview(repeatContainer, null, {repeat: true});
                        self.is_repeat = null;
                    }
                    else if (cl.contains('tb_toggle_row')) {
                        _this.toggle(e.target);
                    }
                    else if (cl.contains('tb_arrow')) {
                        _this.move(e.target,self);
                    }
                },{passive:true});

                return repeat;
            },
            sortable(el, self) {
                el.tfOn('pointerdown', function (e) {console.log(e.target);
                    if (e.which === 1 && e.target.classList.contains('tb_repeatable_field_top')) {
                        e.stopImmediatePropagation();
                        let timer,
                                timeout,
                                theLast,
                                dir,
                                toggleCollapse,
                                prevY = 0,
                                holder,
                                holderHeight,
                                scrollbar,
                                editors = {},
                                doc = this.ownerDocument,
                                item = e.target.closest('.tb_repeatable_field'),
                                viewMin,
                                viewMax,
                                parentHeight,
                                isWorking = false,
                                parentNode;
                        const draggableCallback = e => {

                            e.stopImmediatePropagation();
                            timer = requestAnimationFrame(() => {
                                if (!doc) {
                                    return;
                                }
                                const x =  e.clientX,
                                        y = e.clientY,
                                        moveTo = e.type === 'mousemove' ? e.target : doc.elementFromPoint(x, y),
                                        clientY = y - holderHeight - parentNode.getBoundingClientRect().top;
                                if (clientY > 0 && clientY < parentHeight) {
                                    holder.style.transform = 'translateY(' + clientY + 'px)';
                                    scrollDrag(y);
                                    if (y >= viewMax || y <= viewMin) {
                                        return;
                                    }
                                    if (moveTo && moveTo !== item && moveTo.classList.contains('tb_repeatable_field')) {
                                        const side = y > prevY ? 'bottom' : 'top';
                                        if (dir !== side || theLast !== moveTo) {
                                            side === 'bottom' ? moveTo.after(item) : moveTo.before(item);
                                        }
                                        theLast = moveTo;
                                        dir = side;
                                    }
                                    prevY = y;
                                } else {
                                    scrollDrag(y);
                                }
                            });
                        },
                        scrollDrag = y=> {
                            if (!scrollbar) {
                                return;
                            }
                            if (y >= viewMax || y <= viewMin) {
                                if (isWorking === false) {
                                    isWorking = true;
                                    const k = parseInt(viewMax / 10);
                                    scrollbar.scrollTop += y <= viewMin ? (-1) * k : k;
                                    if (timeout) {
                                        clearTimeout(timeout);
                                    }
                                    timeout = setTimeout(() => {
                                        requestAnimationFrame(() => {
                                            if (isWorking) {
                                                isWorking = false;
                                                scrollDrag(y);
                                            }
                                        });
                                    }, k * 2);
                                }
                            } else {
                                if (timeout) {
                                    clearTimeout(timeout);
                                }
                                isWorking = false;
                            }
                        },
                        startDrag = e=>{
                            doc.body.classList.add('tb_start_animate', 'tb_move_drag');
                            parentNode = item.parentNode;
                            parentNode.classList.add('tb_sort_start');
                            if (typeof tinyMCE !== 'undefined') {
                                const items = parentNode.tfClass('tb_lb_wp_editor');
                                for (let i = items.length - 1; i > -1; --i) {
                                    let id = items[i].id;
                                    editors[id] = tinymce.get(id).getContent();
                                    tinyMCE.execCommand('mceRemoveEditor', false, id);
                                }
                            }
                            if (!item.classList.contains('collapsed')) {
                                item.tfClass('tb_repeatable_field_content')[0].style.display = 'none';
                                item.classList.add('collapsed');
                                toggleCollapse = true;
                            }
                            holder = item.cloneNode(true);
                            holder.tfClass('tb_repeatable_field_content')[0].remove();
                            scrollbar = item.closest('.tf_scrollbar');
                            holder.className += ' tb_sort_handler';
                            item.classList.add('tb_current_sort');
                            item.after(holder);
                            holderHeight = holder.getBoundingClientRect().height / 2;
                            parentHeight = parentNode.offsetHeight;
                            const box = scrollbar.getBoundingClientRect();
                            viewMin = box.top;
                            viewMax = box.bottom - 40;
                            draggableCallback(e);
                        };
                        this.tfOn('lostpointercapture', function (e) {
                            this.tfOff('pointermove', startDrag, {passive: true, once: true})
                            .tfOff('pointermove', draggableCallback, {passive: true});

                            if (parentNode) {
                                e.stopImmediatePropagation();
                                if (timer) {
                                    cancelAnimationFrame(timer);
                                }
                                if (timeout) {
                                    clearTimeout(timeout);
                                }
                                if (holder) {
                                    holder.remove();
                                }
                                if (typeof tinyMCE !== 'undefined') {
                                    for (let id in editors) {
                                        tinyMCE.execCommand('mceAddEditor', false, id);
                                        tinymce.get(id).setContent(editors[id]);
                                    }
                                }
                                item.classList.remove('tb_current_sort');
                                if (toggleCollapse) {
                                    item.classList.remove('collapsed');
                                    item.tfClass('tb_repeatable_field_content')[0].style.display = '';
                                }
                                requestAnimationFrame(() => {
                                    self.control.preview(parentNode, null, {repeat: true});
                                    Themify.triggerEvent(parentNode, 'sortable');
                                    parentNode.classList.remove('tb_sort_start');
                                    parentNode = null;
                                });
                            }
                            doc.body.classList.remove('tb_start_animate', 'tb_move_drag');
                            theLast = holder = toggleCollapse = dir = prevY = editors = scrollbar = doc = parentHeight = holderHeight = isWorking = viewMin = viewMax = item = timer = timeout = null;
                        }, {passive: true, once: true})
                        .tfOn('pointermove', startDrag, {passive: true, once: true})
                        .tfOn('pointermove', draggableCallback, {passive: true})
                        .setPointerCapture(e.pointerId);
                    }
                }, {passive: true});
            },
            move(el,self){
                const r=el.closest('.tb_repeatable_field'),
                    dir=el.classList.contains('tb_up_row')?'up':'down',
                    next = dir==='down'?r.nextElementSibling:r.previousElementSibling,
                    p=r.parentNode;
                    dir==='down'?next.after(r):next.before(r);
                    requestAnimationFrame(() => {
                        p.closest('.tf_scrollbar').scroll({
                            top:r.offsetTop+p.offsetTop+10
                        });
                        self.control.preview(p, null, {repeat: true});
                        Themify.triggerEvent(p, 'sortable');
                    });
            },
            toggle(el) {
                $(el).closest('.tb_repeatable_field').toggleClass('collapsed').find('.tb_repeatable_field_content').slideToggle();
            },
            delete(el) {
                const item = el.closest('.tb_repeatable_field');
                Themify.triggerEvent(doc, 'tb_repeatable_delete', [item]);
                item.remove();
            }
        },
        accordion: {
            expand(item, data, self) {
                item.tfOn(Themify.click, function (e) {
                    let wrap = this.tfClass('tb_accordion_fields_options')[0];
                    if (wrap === und) {
                        wrap = doc.createElement('div');
                        wrap.style.display = 'none';
                        wrap.className = 'tb_toggleable_fields_options tb_accordion_fields_options tf_w';
                        self.is_repeat = true;
                        let orig = null;
                        const pid = this.parentNode.closest('.tb_accordion_fields').id,
                                id = this.dataset.id;
                        if (self.values[pid] !== und && self.values[pid][id] !== und && self.values[pid][id].val !== und) {
                            orig = api.Helper.cloneObject(self.values);
                            self.values = self.values[pid][id].val;
                        }
                        wrap.appendChild(self.create(data.options));
                        this.appendChild(wrap);
                        if (self.clicked === 'setting') {
                            self.setUpEditors();
                        }
                        self.callbacks();
                        if (orig !== null) {
                            self.values = orig;
                        }
                        self.is_repeat = orig = null;
                    } else if (wrap.contains(e.target)) {
                        return;
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    if (this.classList.contains('tb_closed')) {
                        $(wrap).slideDown(function () {
                            this.parentNode.classList.remove('tb_closed');
                        });
                    } else {
                        $(wrap).slideUp(function () {
                            this.parentNode.classList.add('tb_closed');
                        });
                    }
                });
            },
            resetMotion(item, self) {
                if (self.values.motion_effects !== und) {
                    for (let prop in self.values.motion_effects) {
                        self.values.motion_effects[prop].val = {[prop + '_dir']:''};
                    }
                }
                const tab = item.parentNode.querySelector('#motion_effects'),
                        select = tab.tfTag('select'),
                        boxes = tab.tfClass('tb_position_box_wrapper'),
                        sliders = tab.tfClass('tb_slider_wrapper'),
                        ranges=tab.tfClass('tb_range');
                for (let i = select.length - 1; i > -1; --i) {
                    select[i].selectedIndex = 0;
                    Themify.triggerEvent(select[i],'change');
                }
                for (let i = ranges.length - 1; i > -1; --i) {
                    ranges[i].value = ranges[i].min!==und?ranges[i].min:'';
                }
                for (let i = boxes.length - 1; i > -1; --i) {
                    boxes[i].tfTag('input')[0].value = '50,50';
                    boxes[i].tfClass('tb_position_box_handle')[0].removeAttribute('style');
                }
                for (let i = sliders.length - 1; i > -1; --i) {
                    let input = sliders[i].querySelector('input[type="hidden"]'),
                        range=sliders[i].querySelectorAll('input[type="range"]');
                        if(range[1]){
                            range[0].value=range[0].max;
                            range[1].value=range[0].min;
                            Themify.triggerEvent(range[1],'input');
                        }
                        else{
                            range[0].value=range[0].min;
                        }
                        Themify.triggerEvent(range[0],'input');
                        requestAnimationFrame(()=>{
                            input.value='';
                        });
                }
            },
            render(data, self) {
                const ul = doc.createElement('ul'),
                        fr = doc.createDocumentFragment(),
                        resetEf = doc.createElement('a');
                ul.id = data.id;
                ul.className = 'tb_toggleable_fields tb_accordion_fields tb_lb_option';
                if (data.id === 'motion_effects' && self.values) {
                    if (self.values.hasOwnProperty('custom_parallax_scroll_speed')) {
                        if (!self.values.hasOwnProperty('motion_effects')) {
                            self.values.motion_effects = {
                                v: {
                                    val: {
                                        v_speed: self.values.custom_parallax_scroll_speed,
                                        v_dir: ''
                                    }
                                },
                                h: {val: {}},
                                t: {val: {
                                        t_speed: ''
                                    }},
                                r: {val: {}},
                                s: {val: {}}
                            };
                            delete self.values.custom_parallax_scroll_speed;
                        }
                        if (self.values.hasOwnProperty('custom_parallax_scroll_reverse')) {
                            self.values.motion_effects.v.val.v_dir= 'down';
                            delete self.values.custom_parallax_scroll_reverse;
                        } else {
                            self.values.motion_effects.v.val.v_dir = 'up';
                        }
                        if (self.values.hasOwnProperty('custom_parallax_scroll_fade')) {
                            self.values.motion_effects.t.val.t_speed = self.values.custom_parallax_scroll_speed;
                            delete self.values.custom_parallax_scroll_fade;
                        }
                    }
                }
                const opt = self.values[data.id],
                        create =  (id, item)=> {
                            const li = doc.createElement('li'),
                                    input = doc.createElement('input'),
                                    title = doc.createElement('div');
                            title.textContent = data.options[id].label;
                            title.className = 'tb_toggleable_fields_title tb_accordion_fields_title tf_plus_icon tf_rel';
                            input.type = 'hidden';
                            input.value = '';
                            li.className = 'tb_closed';
                            li.dataset.id=id;
                            if (item.val !== und) {
                                input.value = JSON.stringify(item.val);
                            }
                            li.append(input, title);
                            this.expand(li, data.options[id], self);
                            ul.appendChild(li);
                        };
                if (opt !== und) {
                    for (let id in opt) {
                        if (data.options[id] !== und) {
                            create(id, opt[id]);
                        }
                    }
                }
                for (let id in data.options) {
                    if (opt === und || opt[id] === und) {
                        create(id, data.options[id]);
                    }
                }

                fr.appendChild(ul);
                if (data.id === 'motion_effects') {
                    resetEf.href = '#';
                    resetEf.className = 'tb_motion_reset_link';
                    resetEf.textContent = self.label.reset_effect;
                    resetEf.tfOn(Themify.click, e=> {
                        e.preventDefault();
                        e.stopPropagation();
                        this.resetMotion(e.currentTarget, self);
                    });
                    fr.appendChild(resetEf);
                }
                return fr;
            }
        },
        toggleable_fields: {
            sort(el, self) {
                if(el.childElementCount<2){
                    return;
                }
                el.tfOn('pointerdown', function (e) {
                    if (e.which === 1 && (e.target.parentNode === this || e.target.parentNode.parentNode === this)) {
                        e.stopImmediatePropagation();
                        let timeout,
                            theLast,
                            dir,
                            toggleCollapse,
                            prevY = 0,
                            holder,
                            holderHeight,
                            scrollbar,
                            editors = {},
                            doc = this.ownerDocument,
                            item = e.target.closest('.tb_toggleable_item'),
                            viewMin,
                            viewMax,
                            parentHeight,
                            isWorking = false,
                            clone,
                            parentNode = item.parentNode;
                        const scrollDrag = y=> {
                                if (!scrollbar) {
                                    return;
                                }
                                if (y >= viewMax || y <= viewMin) {
                                    if (isWorking === false) {
                                        isWorking = true;
                                        const k = parseInt(viewMax / 10);
                                        scrollbar.scrollTop += y <= viewMin ? (-1) * k : k;
                                        if (timeout) {
                                            clearTimeout(timeout);
                                        }
                                        timeout = setTimeout(() => {
                                            requestAnimationFrame(() => {
                                                if (isWorking) {
                                                    isWorking = false;
                                                    scrollDrag(y);
                                                }
                                            });
                                        }, k * 2);
                                    }
                                } else {
                                    if (timeout) {
                                        clearTimeout(timeout);
                                    }
                                    isWorking = false;
                                }
                            },
                            draggableCallback = e=>{
                                e.stopImmediatePropagation();
                                if (!doc) {
                                    return;
                                }
                                const x = e.clientX,
                                        y =e.clientY,
                                        moveTo = doc.elementFromPoint(x, y),
                                        clientY = y - holderHeight - parentNode.getBoundingClientRect().top;
                                if (clientY > 0 && clientY < parentHeight) {
                                    holder.style.transform = 'translateY(' + clientY + 'px)';
                                    scrollDrag(y);
                                    if (y >= viewMax || y <= viewMin) {
                                        return;
                                    }
                                    if (moveTo && moveTo !== item && moveTo.classList.contains('tb_toggleable_item')) {
                                        const side = y > prevY ? 'bottom' : 'top';
                                        if (dir !== side || theLast !== moveTo) {
                                            side === 'bottom' ? moveTo.after(clone) : moveTo.before(clone);
                                        }
                                        theLast = moveTo;
                                        dir = side;
                                    }
                                    prevY = y;
                                } else {
                                    scrollDrag(y);
                                }
                        },
                        startDrag = function(e){
                            doc.body.classList.add('tb_start_animate', 'tb_move_drag');
                            
                            parentNode.classList.add('tb_sort_start');
                            if (typeof tinyMCE !== 'undefined') {
                                const items = parentNode.tfClass('tb_lb_wp_editor');
                                for (let i = items.length - 1; i > -1; --i) {
                                    let id = items[i].id;
                                    editors[id] = tinymce.get(id).getContent();
                                    tinyMCE.execCommand('mceRemoveEditor', false, id);
                                }
                            }
                            if (!this.classList.contains('tb_closed')) {
                                const opt = this.tfClass('tb_toggleable_fields_options')[0];
                                if (opt) {
                                    opt.style.display = 'none';
                                }
                                this.classList.add('tb_closed');
                                toggleCollapse = true;
                            }
                            holder = this.cloneNode(true);
                            const opt = holder.tfClass('tb_toggleable_fields_options')[0];
                            if (opt) {
                                opt.remove();
                            }
                            clone = this.cloneNode(true);
                            scrollbar = this.closest('.tf_scrollbar');
                            holder.className += ' tb_sort_handler';
                            clone.classList.add('tb_current_sort');
                            this.style.display='none';
                            this.after(clone,holder);
                            holderHeight = holder.getBoundingClientRect().height / 2;
                            parentHeight = parentNode.offsetHeight;
                            const box = scrollbar.getBoundingClientRect();
                            viewMin = box.top;
                            viewMax = box.bottom - 40;
                            draggableCallback(e);
                        };
                        item.tfOn('lostpointercapture', function (e) {
                            this.tfOff('pointermove', startDrag, {passive: true, once: true})
                            .tfOff('pointermove', draggableCallback, {passive: true});

                            if (clone) {
                                e.stopImmediatePropagation();
                                if (timeout) {
                                    clearTimeout(timeout);
                                }
                                if (holder) {
                                    holder.remove();
                                }
                                clone.style.display='none';
                                clone.replaceWith(this);
                                this.style.display='';
                                if (typeof tinyMCE !== 'undefined') {
                                    for (let id in editors) {
                                        tinyMCE.execCommand('mceAddEditor', false, id);
                                        tinymce.get(id).setContent(editors[id]);
                                    }
                                }
                                this.classList.remove('tb_current_sort');
                                if (toggleCollapse) {
                                    const opt = this.tfClass('tb_toggleable_fields_options')[0];
                                    if (opt) {
                                        opt.style.display = '';
                                    }
                                    this.classList.remove('tb_closed');
                                }
                                    self.control.preview(parentNode, null, {repeat: true});
                                    Themify.triggerEvent(parentNode, 'sortable');
                                    parentNode.classList.remove('tb_sort_start');
                                    parentNode = null;
                            }
                            doc.body.classList.remove('tb_start_animate', 'tb_move_drag');
                            theLast = holder = toggleCollapse = dir = prevY = editors = scrollbar = doc = parentHeight = clone=isWorking = holderHeight = viewMin = viewMax = item =  timeout = null;
                        }, {passive: true, once: true})
                        .tfOn('pointermove', startDrag, {passive: true, once: true})
                        .tfOn('pointermove', draggableCallback, {passive: true})
                        .setPointerCapture(e.pointerId);
                    }
                }, {passive: true});
            },
            expand(item, data, self) {
                item.tfOn(Themify.click, function (e) {
                    if (!this.classList.contains('tb_toggleable_field_disabled') && !e.target.closest('.switch-wrapper')) {
                        let wrap = this.tfClass('tb_toggleable_fields_options')[0];
                        if (!wrap) {
                            wrap = doc.createElement('div');
                            wrap.style.display = 'none';
                            wrap.className = 'tb_toggleable_fields_options tf_box tf_w';
                            this.appendChild(wrap);
                            self.is_repeat = true;
                            let pid = this.closest('.tb_toggleable_fields').id,
                                    orig = null,
                                    id = this.dataset.id;
                            if (self.values[pid] !== und && self.values[pid][id] !== und && self.values[pid][id].val !== und) {
                                orig = api.Helper.cloneObject(self.values);
                                self.values = self.values[pid][id].val;
                            }

                            wrap.appendChild(self.create(data.options));
                            if (self.clicked === 'setting') {
                                self.setUpEditors();
                            }
                            self.callbacks();
                            if (orig !== null) {
                                self.values = orig;
                            }
                            self.is_repeat = null;
                        } else if (wrap.contains(e.target)) {
                            return;
                        }
                        e.stopPropagation();
                        e.preventDefault();
                        if (this.classList.contains('tb_closed')) {
                            $(wrap).slideDown(function () {
                                this.parentNode.classList.remove('tb_closed');
                            });
                        } else {
                            $(wrap).slideUp(function () {
                                this.parentNode.classList.add('tb_closed');
                            });
                        }
                    } else if (!e.target.closest('.tb_toggleable_fields_options')) {
                        const wrap = this.tfClass('tb_toggleable_fields_options')[0];
                        $(wrap).slideUp(function () {
                            this.parentNode.classList.add('tb_closed');
                        });
                    }
                });
            },
            disable(el, self) {
                const item = el.closest('li'),
                        cl = item.classList;
                if (!el.checked) {
                    cl.add('tb_toggleable_field_disabled', 'tb_closed');
                } else {
                    cl.remove('tb_toggleable_field_disabled');
                }
                self.control.preview(item.parentNode, null, {repeat: true});
            },
            render(data, self) {
                const _this = this,
                        ul = doc.createElement('ul');
                ul.className = 'tb_toggleable_fields tf_w tf_rel';
                if (self.is_repeat === true) {
                    ul.dataset.inputId = data.id;
                    ul.className += ' tb_lb_option_child';
                } else {
                    ul.id = data.id;
                    ul.className += ' tb_lb_option';
                }
                const oldRepeat = self.is_repeat,
                    opt = self.values[data.id],
                create =  (id, item) =>{
                    const itemOpt=data.options[id],
                        toogleSwitch = {
                        type: 'toggle_switch',
                        id: '',
                        options: {
                            on: {
                                name: '1',
                                value:itemOpt.toggle && itemOpt.toggle.on?itemOpt.toggle.on:self.label.s
                            },
                            off: {
                                name: '0',
                                value:itemOpt.toggle && itemOpt.toggle.off?itemOpt.toggle.off:self.label.hi
                            }
                        },
                        default: item.on === '1' ? 'on' : 'off',
                        control: false
                    },
                            li = doc.createElement('li'),
                            input = doc.createElement('input'),
                            title = doc.createElement('div'),
                            switcher = self.create([toogleSwitch]);
                    title.innerHTML += itemOpt.label;
                    title.className = 'tb_toggleable_fields_title tf_plus_icon tf_rel';
                    input.type = 'hidden';
                    input.value = '';
                    li.className = 'tb_toggleable_item tb_closed';
                    if (toogleSwitch.default === 'off') {
                        li.className += ' tb_toggleable_field_disabled';
                    }
                    if(itemOpt.class){
                         li.className += ' '+itemOpt.class;
                    }
                    li.dataset.id=id;
                    if (item.val !== und) {
                        input.value = JSON.stringify(item.val);
                    }
                    switcher.querySelector('.toggle_switch').tfOn('change', function (e) {
                        e.stopPropagation();
                        _this.disable(this, self);
                    }, {passive: true});
                    li.append(input, title, switcher);
                    _this.expand(li, itemOpt, self);
                    ul.appendChild(li);
                };
                self.is_repeat = true;
                if (opt !== und) {
                    for (let id in opt) {
                        if (data.options[id] !== und) {
                            create(id, opt[id]);
                        }
                    }
                }

                for (let id in data.options) {
                    if (opt === und || opt[id] === und) {
                        create(id, data.options[id]);
                    }
                }
                if(data.sort!==false){
                    _this.sort(ul, self);
                }
                self.is_repeat = oldRepeat;
                return ul;
            }
        },
        sortable_fields: {
            /* options shared across all types in the sortable */
            getGlobalOptions(self) {
                return [
                    {   id: 'icon',
                        type: 'icon',
                        label: self.label.icon
                    },
                    {
                        id: 'before',
                        type: 'text',
                        label: self.label.b_t
                    },
                    {
                        id: 'after',
                        type: 'text',
                        label: self.label.a_t
                    }
                ];
            },
            getDefaults(type, self) {
                const _defaults = {
                    date: [
                        {
                            id: 'format',
                            type: 'select',
                            label: self.label.d_f,
                            default: 'def',
                            options: {
                                'F j, Y': self.label.F_j_Y,
                                'Y-m-d': self.label.Y_m_d,
                                'm/d/Y': self.label.m_d_Y,
                                'd/m/Y': self.label.d_m_Y,
                                def: self.label.def,
                                custom: self.label.cus
                            },
                            binding: {
                                not_empty: {hide: 'custom'},
                                custom: {show: 'custom'}
                            }
                        },
                        {
                            id: 'custom',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.cus_f,
                            help: self.label.cus_fd_h
                        }
                    ],
                    time: [
                        {
                            id: 'format',
                            type: 'select',
                            label: self.label.t_f,
                            default: 'def',
                            options: {
                                'g:i a': self.label.g_i_a,
                                'g:i A': self.label.g_i_A,
                                'H:i': self.label.H_i,
                                def: self.label.def,
                                custom: self.label.cus
                            },
                            binding: {
                                not_empty: {hide: 'custom'},
                                custom: {show: 'custom'}
                            }
                        },
                        {
                            id: 'custom',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.cus_f,
                            help: self.label.cus_ft_h
                        }
                    ],
                    author: [
                        {
                            id: 'l',
                            type: 'toggle_switch',
                            label: self.label.l,
                            options: 'simple'
                        },
                        {
                            id: 'a_p',
                            type: 'toggle_switch',
                            label: self.label.a_p,
                            binding: {
                                checked: {show: 'p_s'},
                                not_checked: {hide: 'p_s'}
                            },
                            options: 'simple'
                        },
                        {
                            id: 'p_s',
                            type: 'range',
                            label: self.label.p_s,
                            class: 'xsmall',
                            units: {
                                px: {
                                    max: 96
                                }
                            },
                            control: {
                                event: 'change'
                            }
                        }
                    ],
                    comments: [
                        {
                            id: 'l',
                            type: 'toggle_switch',
                            label: self.label.l,
                            options: 'simple'
                        },
                        {
                            id: 'no',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.no_c
                        },
                        {
                            id: 'one',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.one_c
                        },
                        {
                            id: 'comments',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.comments
                        }
                    ],
                    terms: [
                        {
                            id: 'post_type',
                            type: 'query_posts',
                            tax_id: 'taxonomy'
                        },
                        {
                            id: 'l',
                            type: 'toggle_switch',
                            label: self.label.l,
                            options: 'simple'
                        },
                        {
                            id: 'sep',
                            type: 'text',
                            control: {event: 'change'},
                            label: self.label.sep
                        }
                    ],
                    text: [
                        {
                            id: 't',
                            type: 'textarea',
                            class: 'fullwidth'
                        }
                    ]
                };
                return _defaults[type];
            },
            create(self, data, type, id, vals, isRemovable) {
                const li = doc.createElement('li'),
					arrow = doc.createElement('span'),
					opt = data.options[type];
                    
                li.textContent = opt.label;
                li.dataset.type=type;
                li.className = 'tb_sort_fields_item';
				arrow.className = 'tb_sort_field_dropdown_pointer';
                if (isRemovable === true) {

                    let key = false;
                    if (!id) {
                        if (vals !== und) {
                            key = this.find(vals, type, true);
                        }
                        li.dataset.new=true;
                        const wrap = api.LightBox.el.tfClass(data.id)[0];
                        let i = 1;
                        id = type + '_' + i;
                        if (wrap !== und) {
                            while (true) {
                                if (wrap.querySelector('[data-id="' + id + '"]') === null) {
                                    break;
                                }
                                ++i;
                                id = type + '_' + i;
                            }
                        }
                    } else if (vals !== und) {
                        key = this.find(vals, id);
                    }
                    li.dataset.id=id;
                    const remove = doc.createElement('span'),
                            input = doc.createElement('input');
                    if (key !== false && vals[key].val !== und) {
                        input.value = JSON.stringify(vals[key].val);
                    }
                    input.type = 'hidden';
                    remove.className = 'tb_sort_fields_remove tf_close';
                    remove.title = self.label.delete;
                    li.append(api.Helper.getIcon('ti-pencil'),arrow, input, remove);
                }
                return li;
            },
            sort(el, self) {
                el.tfOn('pointerdown', function (e) {
                    if (e.which === 1 && e.target.parentNode === this) {
                        let theLast,
                            dir,
                            prevY = 0,
                            prevX = 0,
                            holder,
                            holderWidth,
                            holderHeight,
                            editors = {},
                            doc = this.ownerDocument,
                            item = e.target,
                            clone,
                            box,
                            parentNode=item.parentNode;
                        const draggableCallback = e=>{
                            e.stopImmediatePropagation();
                            if (!doc) {
                                return;
                            }
                            let x =e.clientX,
                                    y = e.clientY;
                            if (x < box.left) {
                                x = box.left;
                            } else if (x > box.right) {
                                x = box.right;
                            }
                            if (y < box.top) {
                                y = box.top;
                            } else if (y > box.bottom) {
                                y = box.bottom;
                            }
                            const moveTo =doc.elementFromPoint(x, y),
                                    clientX = x - holderWidth - box.left,
                                    clientY = y - holderHeight - box.top;

                            holder.style.transform = 'translate(' + clientX + 'px,' + clientY + 'px)';
                            if (moveTo && moveTo !== item && moveTo.classList.contains('tb_sort_fields_item')) {
                                const side = y > prevY || x > prevX ? 'bottom' : 'top';
                                if (dir !== side || theLast !== moveTo) {
                                    side === 'bottom' ? moveTo.after(clone) : moveTo.before(clone);
                                }
                                theLast = moveTo;
                                dir = side;
                            }
                            prevY = y;
                            prevX = x;
                        },
                        startDrag = function(e){
                            e.stopImmediatePropagation();
                            doc.body.classList.add('tb_start_animate', 'tb_move_drag');
                            parentNode.classList.add('tb_sort_start');
                            if (typeof tinyMCE !== 'undefined') {
                                const items = parentNode.tfClass('tb_lb_wp_editor');
                                for (let i = items.length - 1; i > -1; --i) {
                                    let id = items[i].id;
                                    editors[id] = tinymce.get(id).getContent();
                                    tinyMCE.execCommand('mceRemoveEditor', false, id);
                                }
                            }
                            holder = this.cloneNode(true);
                            clone=item.cloneNode(true);
                            const opt = holder.tfClass('tb_sort_field_dropdown')[0];
                            if (opt) {
                                opt.remove();
                            }
                            holder.className += ' tb_sort_handler';
                            clone.classList.add('tb_current_sort');
                            this.style.display='none';
                            this.after(clone,holder);
                            const b = holder.getBoundingClientRect();
                            box = parentNode.getBoundingClientRect();
                            holderHeight = (b.height / 2) - parentNode.offsetTop;
                            holderWidth = (b.width / 2) - parentNode.offsetLeft;
                            draggableCallback(e);
                        };
                        item.tfOn('lostpointercapture', function (e) {
                            this.tfOff('pointermove', startDrag, {passive: true, once: true})
                            .tfOff('pointermove', draggableCallback, {passive: true});
                            if (clone) {
                                if (holder) {
                                    holder.remove();
                                }
                                clone.style.display='none';
                                clone.replaceWith(this);
                                this.style.display='';
                                if (typeof tinyMCE !== 'undefined') {
                                    for (let id in editors) {
                                        tinyMCE.execCommand('mceAddEditor', false, id);
                                        tinymce.get(id).setContent(editors[id]);
                                    }
                                }
                                self.control.preview(parentNode, null, {repeat: true});
                                Themify.triggerEvent(parentNode, 'sortable');
                                parentNode.classList.remove('tb_sort_start');
                                parentNode = null;
                            }
                            doc.body.classList.remove('tb_start_animate', 'tb_move_drag');
                            theLast = holder = dir = prevY = prevX = editors = doc = holderHeight = holderWidth = box = item = clone= null;
                        }, {passive: true, once: true})
                        .tfOn('pointermove', startDrag, {passive: true, once: true})
                        .tfOn('pointermove', draggableCallback, {passive: true})
                        .setPointerCapture(e.pointerId);
                    }
                }, {passive: true});
            },
            find(values, id, byType) {
                for (let i = values.length - 1; i > -1; --i) {
                    if (values[i].id === id || (byType === true && id === values[i].type)) {
                        return i;
                    }
                }
                return false;
            },
            edit(self, data, vals, el) {
                const type = el.dataset.type;
                let wrap = el.tfClass('tb_sort_field_dropdown')[0];
                if (!wrap) {
                    wrap = doc.createElement('div');
                    wrap.className = 'tb_sort_field_dropdown tb_sort_field_dropdown_' + type;
                    let id = el.dataset.id,
						orig = null,
						options = data.options[type].options;
                    if (options === und) {
                        options = this.getDefaults(type, self);
                        if (type !== 'text') {
                            options = options.concat(this.getGlobalOptions(self));
                        }
                    }
                    if (data.options[ type ].has_global_options) {
                        options = options.concat(this.getGlobalOptions(self));
                    }

                    self.is_repeat = self.is_sort = true;
                    if (vals !== und) {
                        const isNew = el.dataset.new ? true : false,
                                by = isNew === true ? type : id,
                                key = this.find(vals, by, isNew);
                        if (key !== false && vals[key].val !== und) {
                            orig = api.Helper.cloneObject(self.values);
                            self.values = vals[key].val;
                        }
                    }
                    wrap.appendChild(self.create(options));
                    el.appendChild(wrap);
                    self.callbacks();
                    if (orig !== null) {
                        self.values = orig;
                    }
                    self.is_sort = self.is_repeat = orig = null;
                }

                if (!el.classList.contains('current')) {
                    el.classList.add('current');
                    const _close = function (e) {
                        if (e.which === 1) {
                            if ( el.contains(e.target) || (Themify_Icons.target && el.contains(Themify_Icons.target[0]) && this.tfId('themify_lightbox_fa').style.display === 'block')) {
                                el.classList.add('current');
                            } else {
                                el.classList.remove('current');
                                this.tfOff(e.type, _close, {passive: true});
                            }
                        }
                    };
                    topWindow.document.tfOn('pointerdown', _close, {passive: true});
                }
            },
            remove(self, el) {
                el = el.closest('li');
                const p = el.parentNode;
                el.parentNode.removeChild(el);
                self.control.preview(p, null, {repeat: true});
                Themify.triggerEvent(p, 'delete');
            },
            render(data, self) {
                const wrapper = doc.createElement('div'),
                        plus = doc.createElement('div'),
                        plusWrap = doc.createElement('div'),
                        ul = doc.createElement('ul'),
                        item = doc.createElement('ul'),
                        values = self.values[data.id] ? self.values[data.id].slice(0) : [];
                wrapper.className = 'tb_sort_fields_wrap tf_box tf_rel tf_w';
                item.className = 'tb_sort_fields_parent';
                if (self.is_repeat === true) {
                    item.dataset.inputId = data.id;
                    item.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                } else {
                    item.id = data.id;
                    item.className += ' tb_lb_option';
                }

                ul.className = 'tb_ui_dropdown_items tf_scrollbar';
                plus.className = 'tb_ui_dropdown_label tb_sort_fields_plus tf_plus_icon';
                plus.tabIndex = '-1';
                plusWrap.className = 'tb_sort_fields_plus_wrap';
                for (let i in data.options) {
                    ul.appendChild(this.create(self, data, i));
                }
                for (let i = 0, len = values.length; i < len; ++i) {
                    if (self.is_new !== true || values[i].show === true) {
                        item.appendChild(this.create(self, data, values[i].type, values[i].id, values, true));
                    }
                }

                wrapper.tfOn(Themify.click, e=> {
                    const li=e.target.tagName==='LI'?e.target:e.target.parentNode;
                    if(li.tagName==='LI'){
                        e.stopPropagation();
                        if(e.target.classList.contains('tb_sort_fields_remove')){
                            this.remove(self, e.target);
                        }
                        else{
                            if (li.closest('.tb_sort_fields_plus_wrap')){
                                item.appendChild(this.create(self, data, e.target.dataset.type, null, values, true));
                                self.control.preview(item, null, {repeat: true});
                            }
                            else{
                                this.edit(self, data, values, li); 
                            }
                        }
                    }
                },{passive:true});
                plusWrap.append(plus, ul);
                wrapper.append(item, plusWrap);
                setTimeout(() => {
                    this.sort(item, self);
                }, 800);
                if (self.is_new === true) {
                    self.afterRun.push(() => {
                        self.control.preview(item, null, {repeat: true});
                    });
                }
                return wrapper;
            }
        },
        multi: {
            render(data, self) {
                const wrapper = doc.createElement('div');
                wrapper.className = 'tb_multi_fields tb_fields_count_' + data.options.length;
                wrapper.appendChild(self.create(data.options));
                return wrapper;
            }
        },
        color: {
            is_typing: null,
            controlChange(el, btn_opacity, data) {
                const that = this,
                        $el = $(el),
                        id = data.id;
                $el.tfminicolors({
                    opacity: data.opacity === und ? true : Boolean(data.opacity),
                    swatches: themifyColorManager.toColorsArray(),
                    changeDelay: 10,
                    beforeShow() {
                        const box = api.LightBox.el.getBoundingClientRect(),
                                p = $el.closest('.tfminicolors'),
                                panel = p.find('.tfminicolors-panel');
                        panel.css('visibility', 'hidden').show();//get offset
                        p[0].classList.toggle('tfminicolors_right', ((box.left + box.width) <= panel.offset().left + panel.width()));
                        panel.css('visibility', '').hide();
                    },
                    show() {
                        themifyColorManager.initColorPicker(this);
                        if (api.mode === 'visual') {
                            Themify.triggerEvent(this, 'themify_builder_color_picker_show', {id: id});
                        }
                    },
                    hide() {
                        if (api.mode === 'visual') {
                            Themify.triggerEvent(this, 'themify_builder_color_picker_hide', {id: id});
                        }
                    },
                    change(hex, opacity) {
                        if (!hex) {
                            opacity = hex = '';
                        } else if (opacity) {
                            if ('0.99' == opacity) {
                                opacity = 1;
                            } else if (0 >= parseFloat(opacity)) {
                                opacity = 0;
                            }
                        }
                        if (!that.is_typing && opacity !== doc.activeElement) {
                            btn_opacity.value = opacity;
                        }
                        if (hex && 0 >= parseFloat($(this).tfminicolors('opacity'))) {
                            $(this).tfminicolors('opacity', 0);
                        }

                        if (api.mode === 'visual') {
                            if (hex) {
                                hex = hex.indexOf('--') === 0 ? 'var(' + hex + ')' : $(this).tfminicolors('rgbaString');
                            }
                            Themify.triggerEvent(this, 'themify_builder_color_picker_change', {id: id, val: hex});
                        }
                    }
                }).tfminicolors('show');
                //opacity
                const callback = function (e) {
                    let opacity = parseFloat(this.value.trim().replace(',', '.'));
                    if (opacity > 1 || isNaN(opacity) || opacity === '' || opacity < 0) {
                        opacity = !el.value ? '' : 1;
                    }
                    if (e.type === 'blur') {
                        this.value = opacity;
                    }
                    that.is_typing = 'keyup' === e.type;
                    $el.tfminicolors('opacity', opacity);
                };
                btn_opacity.tfOn('blur keyup', callback, {passive: true});
                el.setAttribute('data-tfminicolors-initialized', true);
            },
            setColor(input, swatch, opacityItem, val) {                
                let color = val,
                    opacity = '',
                    isVar=false;
                if(val==='transparent'){
                    color=val='#000';
                    opacity=0;
                }
                else if (val !== '') {
                    isVar=val.indexOf('--') === 0;
                    if(isVar===false){
                        if (val.indexOf('_') !== -1) {
                            color = ThemifyStyles.toRGBA(val);
                            val = val.split('_');
                            opacity = val[1];
                            if (!opacity) {
                                opacity = 1;
                            } else if (0 >= parseFloat(opacity)) {
                                opacity = 0;
                            }
                            color = val[0];
                        } else {
                            color = val;
                            opacity = 1;
                        }
                        if (color.indexOf('#') === -1) {
                            color = '#' + color;
                        }
                    }
                }
                input.parentNode.classList.toggle('tfminicolors-var-input',isVar);
                input.value = color;
                if(isVar===true){
                    color=opacity='';
                }
                swatch.style.background = color;
                input.dataset.opacity=swatch.style.opacity = opacityItem.value = opacity;
            },
            update(id, v, self) {
                const input = self.getEl(id);
                if (input !== null) {
                    const p = input.parentNode;
                    if (v === und) {
                        v = '';
                    }
                    this.setColor(input, p.tfClass('tfminicolors-swatch-color')[0], p.nextElementSibling, v);
                }
            },
            render(data, self) {
                const f = doc.createDocumentFragment(),
                        wrapper = doc.createElement('div'),
                        tfminicolors = doc.createElement('div'),
                        input = doc.createElement('input'),
                        opacity = doc.createElement('input'),
                        swatch = doc.createElement('span'),
                        span = doc.createElement('span'),
                        that = this;

                let v = self.getStyleVal(data.id);
                wrapper.className = 'tfminicolors_wrapper';
                tfminicolors.className = 'tfminicolors tfminicolors-theme-default';

                input.type = 'text';
                input.className = 'tfminicolors-input';
                input.autocomplete = 'off';
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                if (self.is_repeat === true) {
                    input.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.id = data.id;
                    input.className += ' tb_lb_option';
                }
                swatch.className = 'tfminicolors-swatch tfminicolors-sprite tfminicolors-input-swatch';
                span.className = 'tfminicolors-swatch-color tf_abs';

                opacity.type = 'number';
                opacity.step=.1;
                opacity.min=0;
                opacity.max=1;
                opacity.className = 'color_opacity';
                swatch.appendChild(span);
                tfminicolors.append(input, swatch);
                wrapper.append(tfminicolors, opacity);

                self.initControl(input, data);
                swatch.tfOn(Themify.click,  () =>{
                    wrapper.insertAdjacentElement('afterbegin', input);
                    tfminicolors.parentNode.removeChild(tfminicolors);
                    that.controlChange(input, opacity, data);
                }, {once: true, passive: true});
                input.tfOn('focusin',  () => {
                    swatch.click();
                }, {once: true, passive: true});
                opacity.tfOn('focusin', function (e) {
                    if (!input.dataset.tfminicolorsInitialized) {
                        input.dataset.opacity = this.value;
                        swatch.click();
                    } else {
                        $(input).tfminicolors('show');
                    }
                }, {passive: true});

                if (!v && data.default) {
                    v = data.default;
                }

                if (v !== und) {
                    this.setColor(input, span, opacity, v);
                }
                f.appendChild(wrapper);
                if (data.after !== und) {
                    f.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    f.appendChild(self.description(data.description));
                }
                if (data.tooltip !== und) {
                    f.appendChild(self.hint(data.tooltip));
                }
                return f;
            }
        },
        tooltip: {
            render(data, self) {
                const options = [
                    {
                        type: 'textarea',
                        label: self.label.tt,
                        id: '_tooltip',
                        class: 'fullwidth',
                        control: false /* disable live preview refresh */
                    },
                    {
                        type: 'color',
                        label: self.label.bg_c,
                        id: '_tooltip_bg',
                        control: false
                    },
                    {
                        type: 'color',
                        label: self.label.f_c,
                        id: '_tooltip_c',
                        control: false
                    },
                    {
                        type: 'range',
                        label: self.label.ma_wd,
                        id: '_tooltip_w',
                        control: false,
                        units: {
                            px: {
                                min: -2000,
                                max: 2000
                            },
                            em: {
                                min: -20,
                                max: 20
                            }
                        }
                    }
                ];
                let f = self.create([{
                        type: 'group',
                        label: self.label.t,
                        display: 'accordion',
                        options: options
                    }]);

                if ('visual' === api.mode) {
                    f = this.bindEvents(f);
                }
                return f;
            },
            /* setup live preview events */
            bindEvents(el) {
                const self = this,
                        _tooltip = el.querySelector('#_tooltip'),
                        color_fields = [el.querySelector('#_tooltip_bg'), el.querySelector('#_tooltip_c')],
                        events = ['focus', 'keyup', 'blur', 'change'],
                        tooltip_w = el.querySelector('#_tooltip_w');
                for (let i = events.length - 1; i > -1; --i) {
                    if (events[i] !== 'change') {
                        _tooltip.tfOn(events[i], e => {
                            self.addOrRemoveTooltip(e.type !== 'blur');
                        }, {passive: true});
                    }
                }
                tooltip_w.tfOn(events, e => {
                    self.addOrRemoveTooltip(e.type !== 'blur');
                }, {passive: true});
                
                for (let i = color_fields.length - 1; i > -1; --i) {
                    color_fields[i].tfOn('themify_builder_color_picker_show', function () {
                        self.addOrRemoveTooltip(true);
                        this.tfOn('themify_builder_color_picker_hide', () => {
                            self.addOrRemoveTooltip(false);
                        }, {once: true, passive: true});
                    }, {passive: true});
                    color_fields[i].tfOn('themify_builder_color_picker_change', () => {
                        self.addOrRemoveTooltip(true);
                    }, {passive: true});
                }
                return el;
            },
            /* creates tooltip preview element */
            addOrRemoveTooltip(show) {
                let el = api.liveStylingInstance.el,
                        tooltip = el.querySelector(':scope > .tb_tooltip');
                if (!show && tooltip) {
                    tooltip.remove();
                    return;
                }

                Themify.loadCss('tooltip');

                const val = topWindow.document.tfId('_tooltip').value;

                if (val !== '') {
                    if (!tooltip) {
                        tooltip = doc.createElement('div');
                        tooltip.className = 'tb_tooltip';
                        el.appendChild(tooltip);
                    }
                    let width = topWindow.document.tfId('_tooltip_w').value;
                    tooltip.classList.add('tooltip_preview');
                    tooltip.innerHTML = val;
                    tooltip.style.background = api.Helper.getColor(topWindow.document.tfId('_tooltip_bg'));
                    tooltip.style.color = api.Helper.getColor(topWindow.document.tfId('_tooltip_c'));
                    if (width !== '') {
                        width += topWindow.document.tfId('_tooltip_w_unit').value;
                    }
                    tooltip.style.width = width;
                    tooltip.classList.remove('tf_hide');
                } else if (tooltip) {
                    tooltip.remove();
                }
            }
        },
        text: {
            update(id, v, self) {
                const item = self.getEl(id);
                if (item !== null) {
                    item.value = v !== und ? v : '';
                }
            },
            render(data, self) {
                const f = doc.createDocumentFragment(),
                        input = doc.createElement('input'),
                        v = self.getStyleVal(data.id);
                input.type = data.input_type || 'text'; // custom input types
                if (self.is_repeat === true) {
                    input.className = self.is_sort === true ? 'tb_lb_sort_child' : 'tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.className = 'tb_lb_option';
                    input.id = data.id;
                }
                if (data.placeholder !== und) {
                    input.placeholder = data.placeholder;
                }
                if (data.custom_args !== und) {
                    for (let i in data.custom_args) {
                        input.setAttribute(i, data.custom_args[i]);
                    }
                }
                if (v !== und) {
                    input.value = v;
                }
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                f.appendChild(self.initControl(input, data));
                if (data.unit !== und) {
                    f.appendChild(self.select.render(data.unit, self));
                }
                if (data.after !== und) {
                    f.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    f.appendChild(self.description(data.description));
                }
                if (data.tooltip !== und) {
                    f.appendChild(self.hint(data.tooltip));
                }
                return f;
            }
        },
        number: {
            render(data, self) {
                data.input_type = 'number';
                if (data.custom_args === und) {
                    data.custom_args = {min: data.min || 0};
                    if(data.max!==und){
                        data.custom_args.max = data.max;
                    }
                    if (data.step !== und) {
                        data.custom_args.step = data.step;
                    }
                }
                return self.text.render(data, self);
            }
        },
        angle: {
            render(data, self) {
                data.input_type = 'number';
                data.custom_args = {min: 0, max: 360};
                const wrap = doc.createElement('div'),
                        css_class = 'xsmall tb_lb_option tb_angle_input';
                wrap.tabIndex = -1;
                wrap.className = 'tb_angle_container tf_rel';
                data.class = data.class !== und ? data.class + ' ' + css_class : css_class;
                const res = true === data.deg ? self.range.render(data, self) : self.text.render(data, self);
                wrap.appendChild(res);
                const angle = wrap.querySelector('#' + data.id);
                if (true === data.deg) {
                    angle.dataset.deg = true;
                }
                angle.tfOn('pointerdown', function (e) {
                    e.stopImmediatePropagation();
                    let _circle = this.parentNode.querySelector('.tb_angle_circle');
                    if (!_circle) {
                        let v = this.value;
                        const tmp1 = doc.createElement('div'),
                                tmp2 = doc.createElement('div');
                        _circle = doc.createElement('div');
                        if (v !== '') {
                            tmp1.style.transform = 'rotate(' + v + 'deg)';
                        }
                        tmp1.className = 'tb_angle_dot';
                        _circle.className = 'tb_angle_circle';
                        tmp2.className = 'tb_angle_circle_wrapper';
                        _circle.appendChild(tmp1);
                        tmp2.appendChild(_circle);
                        this.parentNode.appendChild(tmp2);
                    }
                    _circle.tfOn(e.type, function (e) {
                        if (e.which === 1) {
                            let box = this.getBoundingClientRect(),
                                    center_x = (this.offsetWidth / 2) + box.left,
                                    center_y = (this.offsetHeight / 2) + box.top,
                                    timer,
                                    _wrapper = this.parentNode.querySelector('.tb_angle_circle_wrapper'),
                                    _dot = this.parentNode.querySelector('.tb_angle_dot');
                            const PI = 180 / Math.PI,
                                    doc = this.ownerDocument,
                                    _start = e=> {
                                        e.stopImmediatePropagation();
                                        doc.body.classList.add('tb_start_animate');
                                    },
                                    _move = e=> {
                                        e.stopImmediatePropagation();
                                        timer = requestAnimationFrame(() => {
                                            let delta_y = center_y - e.clientY,
                                                    delta_x = center_x -e.clientX,
                                                    ang = Math.atan2(delta_y, delta_x) * PI; // Calculate Angle between circle center and mouse pos
                                            ang -= 90;
                                            if (ang < 0) {
                                                ang += 360; // Always show angle positive
                                            }
                                            ang = Math.round(ang);
                                            _dot.style.transform = 'rotate(' + ang + 'deg)';
                                            angle.value = ang;
                                            Themify.triggerEvent(angle, 'change');
                                        });
                                    };

                            this.tfOn('lostpointercapture', function (e) {
                                if (timer) {
                                    cancelAnimationFrame(timer);
                                }
                                this.tfOff('pointermove', _start, {passive: true, once: true})
                                .tfOff('pointermove', _move, {passive: true});
                                doc.body.classList.remove('tb_start_animate');
                                requestAnimationFrame(() => {
                                    _wrapper = _dot = center_x = timer = center_y = null;
                                });

                            }, {passive: true, once: true})
                            .tfOn('pointermove', _start, {passive: true, once: true})
                            .tfOn('pointermove', _move, {passive: true})
                            .setPointerCapture(e.pointerId);
                            _move(e);
                        }

                    }, {passive: true});
                    
                }, {passive: true, once: true});
                return wrap;
            }
        },
        autocomplete: {
            cache:new Map(),
            render(data, self) {
                const d = self.text.render(data, self);
                if (data.dataset === und) {
                    return d;
                }
                const input = d.querySelector('input'),
                        _this = this,
                        container = doc.createElement('div');
                input.autocomplete = 'off';
                container.className = 'tb_autocomplete_container';
                d.appendChild(container);
                let controller=null;
                input.tfOn('input', async function () {
                    // remove all elements in container
                    const wrapper = this.nextElementSibling;
                    wrapper.style.display = 'none';
                    while (wrapper.firstChild !== null) {
                        wrapper.lastChild.remove();
                    }
                    wrapper.style.display = '';
                    const value = this.value,
                        type = data.dataset,
                        k=type+value;
                    if (value !== '') {
                        if (controller !== null) {
                            controller.abort();
                        }
                        let resp=_this.cache.get(k);
                        if(!resp){
                            const parent = this.parentNode;
                            try{
                                controller = new AbortController();
                                parent.classList.add('tb_autocomplete_loading', 'tf_loader');
                                resp=await api.LocalFetch({action: 'tb_get_ajax_data',mode:'autocomplete',dataset: type,value: value},false,{signal: controller.signal});
                                resp=resp.success?resp.data:'';
                                _this.cache.set(k,resp);
                            }
                            catch(e){

                            }
                            parent.classList.remove('tb_autocomplete_loading', 'tf_loader');
                        }
                        if(resp){
                            const d = doc.createDocumentFragment();
                            for (let i in resp) {
                                let item = doc.createElement('div');
                                item.className = 'tb_autocomplete_item';
                                item.dataset.value=i;
                                item.innerText = resp[i];
                                d.appendChild(item);
                            }
                                wrapper.classList.add('tf_scrollbar');
                                wrapper.appendChild(d);
                        }
                        controller= null;
                    }
                }, {passive: true});
                container.tfOn('pointerdown', function (e) {
                    if (e.which === 1 && e.target.classList.contains('tb_autocomplete_item')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const field = this.previousElementSibling;
                        field.value = e.target.dataset.value;
                        field.blur();
                        Themify.triggerEvent(field, 'change');
                    }
                });
                return d;
            }
        },
        mediaFile: {
            _frames: {},
            clicked: null,
            browse(uploader, input, self, type) {
                uploader.tfOn(Themify.click, e=> {
                    e.preventDefault();
                    e.stopPropagation();
                    let file_frame;
                    if (this._frames[type] !== und) {
                        file_frame = this._frames[type];
                    } else {
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: self.label.upload_image,
                            library: {
                                type: type
                            },
                            button: {
                                text: self.label.insert_image
                            },
                            multiple: false
                        });
                        this._frames[type] = file_frame;
                    }
                    file_frame.off('select').on('select', ()=> {
                        api.ActionBar.disable = true;
                        const attachment = file_frame.state().get('selection').first().toJSON();
                        input.value = attachment.url;
                        Themify.triggerEvent(input, 'change');
                        $(input).trigger('change');
                        const attach_id = input.getRootNode().querySelector('#' + input.id + '_id');
                        if (attach_id) {
                            attach_id.value = attachment.id;
                        }
                    });
                    file_frame.on('close', ()=> {
                        api.ActionBar.disable = true;
                        setTimeout(() => {
                            api.ActionBar.disable = null;
                        }, 5);
                    });
                    // Finally, open the modal
                    file_frame.open();
                    file_frame.content.mode('browse');
                });
                if (type === 'image') {
                    input.tfOn('change', e=>{
                        this.setImage(uploader, e.currentTarget.value.trim());
                    }, {passive: true});
                }
            },
            setImage(prev, url) {
                while (prev.firstChild) {
                    prev.lastChild.remove();
                }
                if (url) {
                    const w = 40,
                        h = 40,
                        img = new Image(w,h),
                        placeholder=new Image(w,h);
                    placeholder.decoding = 'async';
                    placeholder.src = '//via.placeholder.com/'+w+'x'+h+'.png';
                    img.src = url;
                    img.decoding = 'async';
                    img.decode()
                    .catch(()=>{})
                    .finally(() => {
                        placeholder.replaceWith(img);
                    });
                    prev.appendChild(placeholder);
                }
            },
            update(id, v, self) {
                const item = self.getEl(id);
                if (item !== null) {
                    if (v === und) {
                        v = '';
                    }
                    item.value = v;
                    this.setImage(item.parentNode.tfClass('thumb_preview')[0], v);
                }
            },
            render(type, data, self) {
                const wr = doc.createElement('div'),
                        input = doc.createElement('input'),
                        upload_btn = doc.createElement('a'),
                        btn_delete = doc.createElement('span'),
                        reg = /.*\S.*/,
                        v = self.getStyleVal(data.id);
                let id;
                input.type = 'text';
                input.className = 'tb_uploader_input';
                input.required = true;
                input.setAttribute('pattern', reg.source);
                input.setAttribute('autocomplete', 'off');
                if (self.is_repeat === true) {
                    input.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    id = 'tb_'+Math.random().toString(36).substr(2, 7);
                    input.dataset.inputId = data.id;
                } else {
                    input.className += ' tb_lb_option';
                    id = data.id;
                }
                input.id = id;

                if (v !== und) {
                    input.value = v;
                }
                btn_delete.className = 'tb_clear_input tf_close';

                upload_btn.className = 'tb_media_uploader tb_upload_btn thumb_preview tf_plus_icon tf_rel';
                upload_btn.href = '#';
                upload_btn.dataset.libraryType = type;
                upload_btn.title = self.label.browse_image;
                wr.className = 'tb_uploader_wrapper tf_rel';
                btn_delete.tfOn(Themify.click,e=>{
                    e.stopPropagation();
                    input.value='';
                    Themify.triggerEvent(input, 'change');
                },{passive:true});
                wr.append(self.initControl(input, data),btn_delete,upload_btn);
                if (type === 'image') {
                    this.setImage(upload_btn, v);
                }
                this.browse(upload_btn, input, self, type);
                if (data.after !== und) {
                    wr.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    wr.appendChild(self.description(data.description));
                }
                if (data.tooltip !== und) {
                    wr.appendChild(self.hint(data.tooltip));
                }
                if (this.clicked === null && self.is_new === true && self.clicked === 'setting' && (self.type === 'image' || self.type === 'pro-image')) {
                    this.clicked = true;
                    const _this = this;
                    self.afterRun.push(() => {
                        Themify.triggerEvent(upload_btn,Themify.click);
                        _this.clicked = null;
                    });
                }
                return wr;
            }
        },
        file:{
            render(data, self) {
                return self.mediaFile.render(data.ext, data, self);
            }  
        },
        image: {
            render(data, self) {
                return self.mediaFile.render('image', data, self);
            }
        },
        video: {
            render(data, self) {
                return self.mediaFile.render('video', data, self);
            }
        },
        audio: {
            render(data, self) {
                return self.mediaFile.render('audio', data, self);
            }
        },
        lottie:{
            render(data, self) {
                const f= doc.createDocumentFragment(),
                    browse=doc.createElement('button');
                    browse.type='button';
                    browse.textContent=self.label.browse_image;
                    browse.tfOn(Themify.click,async e=>{
                        api.Spinner.showLoader();
                        const {LottieBrowse}=await Themify.importJs(Themify.url+'js/admin/modules/lottie-browse');
                        await LottieBrowse.run();
                        api.Spinner.showLoader('done');
                        
                    },{passive:true});
                    f.append(self.file.render(data, self),browse);
                    return f;
            }  
        },
        icon_radio: {
            controlChange(wrap) {
                wrap.tfOn(Themify.click, function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (e.target !== wrap) {
                        const input = e.target.closest('label').tfTag('input')[0];
                        if (input.checked === true) {
                            input.checked = false;
                            input.value = und;
                        } else {
                            input.checked = true;
                            input.value = input.dataset.value;
                        }
                        Themify.triggerEvent(input, 'change');
                    }
                });
            },
            render(data, self) {
                return self.radioGenerate('icon_radio', data);
            }
        },
        radio: {
            controlChange(item) {
                const context = item.classList.contains('tb_radio_dnd')?item.closest('.tb_repeatable_field_content'):(item.closest('.tb_tab,.tb_expanded_opttions') || api.LightBox.el),
                    elements = item.parentNode.parentNode.tfTag('input'),
                    selected = item.value,
                    groups = context.tfClass('tb_group_element_' + selected);
                for (let i = elements.length - 1; i > -1; --i) {
                    let v = elements[i].value;
                    if (selected !== v) {
                        let g = context.tfClass('tb_group_element_' + v);
                        for (let j = g.length - 1; j > -1; --j) {
                            g[j].style.display = 'none';
                        }
                    }
                }
                for (let j = groups.length - 1; j > -1; --j) {
                    groups[j].style.display = '';
                }
            },
            update(id, v, self) {
                const wrap = self.getEl(id);
                if (wrap !== null) {
                    const items = wrap.tfTag('input'),
                            is_icon = wrap.classList.contains('tb_icon_radio');
                    let found = null;
                    for (let i = items.length - 1; i > -1; --i) {
                        if (items[i].value === v) {
                            found = items[i];
                            break;
                        }
                    }
                    if (found === null) {
                        const def = wrap.dataset.default;
                        if (def !== und) {
                            found = wrap.querySelector('[value="' + def + '"]');
                        }
                        if (is_icon === false && found === null) {
                            found = items[0];
                        }
                    }

                    if (found !== null) {
                        found.checked = true;
                        if (is_icon === false && wrap.classList.contains('tb_option_radio_enable')) {
                            this.controlChange(found);
                        }
                    } else if (is_icon === true) {
                        for (let i = items.length - 1; i > -1; --i) {
                            items[i].checked = false;
                        }
                    }
                }
            },
            render(data, self) {
                return self.radioGenerate('radio', data);
            }
        },
        icon_checkbox: {
            render(data, self) {
                return self.checkboxGenerate('icon_checkbox', data);
            }
        },
        checkbox: {
            update(id, v, self) {
                const wrap = self.getEl(id);
                if (wrap !== null) {
                    const items = wrap.tfTag('input'),
                            js_wrap = wrap.classList.contains('tb_option_checkbox_enable');
                    v = v ? v.split('|') : [];
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].checked = v.indexOf(items[i].value) !== -1;
                        if (js_wrap === true) {
                            this.controlChange(items[i]);
                        }
                    }
                }
            },
            controlChange(item) {
                const el = item.classList.contains('tb_radio_dnd') ? item.closest('.tb_repeatable_field_content') : api.LightBox.el,
                        parent = item.parentNode.parentNode,
                        items = parent.tfTag('input'),
                        is_revert = parent.classList.contains('tb_option_checkbox_revert');
                for (let i = items.length - 1; i > -1; --i) {
                    let ch = el.tfClass('tb_checkbox_element_' + items[i].value),
                            is_checked = items[i].checked;
                    for (let j = ch.length - 1; j > -1; --j) {
                        ch[j].classList.toggle('_tb_hide_binding', !((is_revert === true && is_checked === false) || (is_revert === false && is_checked === true)));
                    }
                }
            },
            render(data, self) {
                return self.checkboxGenerate('checkbox', data);
            }
        },
        radioGenerate(type, data) {
            const d = doc.createDocumentFragment(),
                    wrapper = doc.createElement('div'),
                    is_icon = 'icon_radio' === type,
                    options = this.getOptions(data),
                    v = this.getStyleVal(data.id),
                    js_wrap = data.option_js === true,
                    checked = [],
                    self = this,
                    len=options.length;
            let toggle = null,
                    _default = data.default !== und ? data.default : false,
                    id;
            wrapper.className = 'tb_radio_wrap';
            wrapper.tabIndex= '-1';
            if(len>1){
                if (data.new_line !== und) {
                    wrapper.className+= ' tb_new_line';
                }
                wrapper.className+= ' tb_count_'+len;
            }
            if (js_wrap === true) {
                wrapper.className += ' tb_option_radio_enable';
            }
            if (is_icon === true) {
                wrapper.className += ' tb_icon_radio';
                toggle = data.no_toggle === und;
            }
            if (this.is_repeat === true) {
                wrapper.className += this.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                id = 'tb_'+Math.random().toString(36).substr(2, 7);
                wrapper.dataset.inputId = data.id;
            } else {
                wrapper.className += ' tb_lb_option';
                wrapper.id = id = data.id;
            }
            if (_default !== false) {
                wrapper.dataset.default = _default;
            } else if (is_icon === false && v === und) {
                _default = options[0].value;
            }
            if (data.before !== und) {
                d.appendChild(doc.createTextNode(data.before));
            }
            for (let i = 0; i < len; ++i) {
                let label = doc.createElement('label'),
                        ch = doc.createElement('input'),
                        cl=[];
                ch.type = 'radio';
                ch.name = id;
                ch.value = options[i].value;
                if (is_icon === true) {
                    ch.dataset.value=options[i].value;
                }
                if (this.is_repeat === true) {
                    cl.push( 'tb_radio_dnd');
                }
                if (data.class !== und) {
                    cl.push( data.class);
                }
                if (options[i].class !== und) {
                    label.className=options[i].class;
                }
                if(cl.length>0){
                    ch.className =cl.join(' ');
                }
                if (options[i].disable === true) {
                    ch.disabled = true;
                }
                if (v === options[i].value || (v === und && _default === options[i].value)) {
                    ch.checked = true;
                    if (js_wrap === true) {
                        checked.push(ch);
                    }
                }
                label.appendChild(ch);
                if (js_wrap === true) {
                    ch.tfOn('change', function () {
                        this.parentNode.parentNode.blur();
                        self.radio.controlChange(this);
                    }, {passive: true});
                }
                if (is_icon === true) {
                    if (options[i].icon !== und) {
                        let icon_wrap = doc.createElement('span');
                        icon_wrap.className = 'tb_icon_wrapper';
                        icon_wrap.innerHTML = options[i].icon;
                        label.insertAdjacentElement('beforeend', icon_wrap);
                    }
                    if (options[i].label_class !== und) {
                        label.className += options[i].label_class;
                    }
                    if (options[i].name !== und) {
                        let tooltip = doc.createElement('span');
                        tooltip.className = 'themify_tooltip';
                        tooltip.textContent = options[i].name;
                        label.appendChild(tooltip);
                    }

                } else if (options[i].name !== und) {
                    let label_text = doc.createElement('span');
                    label_text.textContent = options[i].name;
                    label.appendChild(label_text);
                }
                wrapper.appendChild(label);
                this.initControl(ch, data);
            }
            wrapper.tfOn(Themify.click, function (e) {
                if ('LABEL' === e.target.parentNode.tagName) {
                    this.blur();
                }
            }, {passive: true});
            d.appendChild(wrapper);
            if (data.after !== und) {
                d.appendChild(self.after(data));
            }
            if (data.description !== und) {
                d.appendChild(self.description(data.description));
            }
            if (is_icon === true && toggle === true) {
                self.icon_radio.controlChange(wrapper);
            }
            if (js_wrap === true) {
                this.radioChange.push(() => {
                    for (let i = 0, len = checked.length; i < len; ++i) {
                        self.radio.controlChange(checked[i]);
                    }
                });
            }
            return d;
        },
        checkboxGenerate(type, data) {
            const d = doc.createDocumentFragment(),
                    wrapper = doc.createElement('div'),
                    options = this.getOptions(data),
                    is_icon = 'icon_checkbox' === type,
                    js_wrap = data.option_js === true,
                    self = this,
                    chekboxes = [],
                    len = options.length;
            let v = this.getStyleVal(data.id),
                    _default = null,
                    is_array = null;
            wrapper.className = 'tb_checkbox_wrap';
            
            if(len>1){
                if(data.new_line===false){
                    wrapper.className+= ' tb_one_row';
                }
                wrapper.className+= ' tb_count_'+len;
            }
            if (js_wrap === true) {
                wrapper.className += ' tb_option_checkbox_enable';
                if (data.reverse !== und) {
                    wrapper.className += ' tb_option_checkbox_revert';
                }
            }
            if (this.is_repeat === true) {
                wrapper.className += this.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                wrapper.dataset.inputId = data.id;
            } else {
                wrapper.className += ' tb_lb_option';
                wrapper.id = data.id;
            }
            if (data.wrap_checkbox !== und) {
                wrapper.className += ' ' + data.wrap_checkbox;
            }
            if (v === und) {
                if (data.default !== und) {
                    _default = data.default;
                    is_array = Array.isArray(_default);
                }
            } else if (v !== false) {
                v = v.split('|');
            }
            if (is_icon === true) {
                wrapper.className += ' tb_icon_checkbox';
            }
            if (data.before !== und) {
                d.appendChild(doc.createTextNode(data.before));
            }
            for (let i = 0; i < len; ++i) {
                let label = doc.createElement('label'),
                        ch = doc.createElement('input');
                ch.type = 'checkbox';
                ch.className = 'tb_checkbox';
                ch.value = options[i].name;
                if (data.class !== und) {
                    ch.className += ' ' + data.class;
                }
                if ((v !== und && v !== false && v.indexOf(options[i].name) !== -1) || (_default === options[i].name || (is_array === true && _default.indexOf(options[i].name) !== -1))) {
                    ch.checked = true;
                }
                if (js_wrap === true) {
                    ch.tfOn('change', function () {
                        self.checkbox.controlChange(this);
                    }, {passive: true});
                    chekboxes.push(ch);
                }
                if (data.id === 'hide_anchor') {
                    api.activeModel.options(ch, 'hide_anchor');
                }
                label.appendChild(ch);
                if (is_icon === true) {
                    label.insertAdjacentHTML('beforeend', options[i].icon);
                    if (options[i].value !== und) {
                        let tooltip = doc.createElement('span');
                        tooltip.className = 'themify_tooltip';
                        tooltip.textContent = options[i].value;
                        label.appendChild(tooltip);
                    }
                } else if (options[i].value !== und) {
                    label.appendChild(doc.createTextNode(options[i].value));
                }
                if (options[i].help !== und) {
                    let hasHelp=doc.createElement('div');
                    hasHelp.className='tb_checkbox_help';
                    hasHelp.append(label,this.help(options[i].help));
                    label=hasHelp;
                }
                wrapper.appendChild(label);
                this.initControl(ch, data);
            }
            if (data.id === 'hide_anchor') {
                wrapper.tfOn(Themify.click, e=> {
                    e.stopPropagation();
                });
            }
            d.appendChild(wrapper);
            if (data.after !== und) {
                if ((data.label === und || data.label === '') && (data.help !== und && data.help !== '')) {
                    wrapper.className += ' contains-help';
                    wrapper.appendChild(this.after(data));
                } else {
                    d.appendChild(this.after(data));
                }
            }
            if (data.description !== und) {
                d.appendChild(this.description(data.description));
            }
            if (js_wrap === true) {
                this.afterRun.push(() => {
                    for (let i = 0, len = chekboxes.length; i < len; ++i) {
                        self.checkbox.controlChange(chekboxes[i]);
                    }
                });
            }
            return d;
        },
        date: {
            loaded: null,
            render(data, self) {
                const f = doc.createDocumentFragment(),
                        input = doc.createElement('input'),
                        clear = doc.createElement('button'),
                        get_datePicker = () => {
                    return topWindow.jQuery.fn.themifyDatetimepicker
                            ? topWindow.jQuery.fn.themifyDatetimepicker
                            : topWindow.jQuery.fn.datetimepicker;
                },
                        hide_picker = () => {
                    get_datePicker().call($(input), 'hide');
                },
                        callback = () => {
                    const datePicker = get_datePicker();

                    if (!datePicker)
                        return;

                    const pickerData = data.picker !== und ? data.picker : {};
                    clear.tfOn(Themify.click, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        input.value = '';
                        input.dispatchEvent(new Event('change'));
                        this.style.display = 'none';
                    });
                    datePicker.call($(input), {
                        showTimepicker: (data.timepicker === und || data.timepicker) ? true : false,
                        showButtonPanel: true,
                        changeYear: true,
                        dateFormat: pickerData.dateformat || 'yy-mm-dd',
                        timeFormat: pickerData.timeformat || 'HH:mm:ss',
                        stepMinute: pickerData.stepMinute || 5,
                        stepSecond: pickerData.stepSecond || 5,
                        controlType: pickerData.timecontrol || 'select',
                        oneLine: true,
                        separator: pickerData.timeseparator || ' ',
                        onSelect(v) {
                            clear.style.display = v === '' ? 'none' : 'block';
                            input.dispatchEvent(new Event('change'));
                        },
                        beforeShow(input, instance) {
                            instance.dpDiv.addClass('themify-datepicket-panel');
                            const r = input.getBoundingClientRect();
                            setTimeout(() => {
                                instance.dpDiv.css({
                                    top: r.top + input.offsetHeight,
                                    left: r.left
                                });
                            }, 10);
                            if (api.mode === 'visual') {
                                doc.body.tfOn(Themify.click, hide_picker, {once: true});
                            }
                        },
                        onClose() {
                            if (api.mode === 'visual') {
                                doc.body.tfOff(Themify.click, hide_picker, {once: true});
                            }
                        }
                    });
                };
                input.type = 'text';
                input.autocomplete = 'off';
                input.className = 'themify-datepicker fullwidth';
                if (self.is_repeat === true) {
                    input.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.className += ' tb_lb_option';
                    input.id = data.id;
                }
                input.readonly = true;

                clear.className = 'themify-datepicker-clear tf_close';

                if (self.values[data.id] !== und) {
                    input.value = self.values[data.id];
                }
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                if (!input.value) {
                    clear.style.display = 'none';
                }
                f.append(self.initControl(input, data),clear);
                if (data.after !== und) {
                    f.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    f.appendChild(self.description(data.description));
                }
                if (this.loaded === null) {
                    const init = () => {
                        topWindow.Themify.loadCss(Themify.url + 'themify-metabox/css/jquery-ui-timepicker.min');
                        topWindow.Themify.loadJs(themifyBuilder.includes_url + 'js/jquery/ui/datepicker.min',(topWindow.jQuery.fn.datepicker !== und),themify_vars.wp).then(()=>{
                            topWindow.Themify.loadJs(Themify.url + 'themify-metabox/js/jquery-ui-timepicker.min',(topWindow.jQuery.fn.themifyDatetimepicker !== und || topWindow.jQuery.fn.datetimepicker !== und),'1.6.3').then(()=>{
                                this.loaded = true;
                                setTimeout(callback, 10);
                            });
                        });
                    };
                    self.afterRun.push(init);
                } else {
                    self.afterRun.push(callback);
                }
                return f;
            }
        },
        gradient: {
            controlChange(self, gradient, input, clear, type, angle, circle, text, update) {
                let angleV = self.getStyleVal(angle.id);
                if (angleV === und || angleV === '') {
                    angleV = 180;
                }
                let is_removed = false,
                        $gradient = $(gradient),
                        id = input.id,
                        value = self.getStyleVal(id),
                        args = {
                            angle: angleV,
                            onChange(stringGradient, cssGradient) {
                                if (is_removed) {
                                    stringGradient = cssGradient = '';
                                }
                                input.value = stringGradient;
                                if ('visual' === api.mode) {
                                    Themify.triggerEvent(input, 'themify_builder_gradient_change', {val: cssGradient});
                                }
                            }
                        };
                if (value) {
                    args.gradient = value;
                    input.value = value;
                }
                angle.value = angleV;

                let typeV = self.getStyleVal(type.id);
                if (typeV === und || typeV === '') {
                    typeV = 'linear';
                }
                type.value = typeV;
                args.type = typeV;
                if (circle.checked) {
                    args.circle = true;
                }
                if (!update) {
                    $gradient.ThemifyGradient(args);
                }
                const instance = $gradient.data('themifyGradient'),
                        callback = val=> {
                            let p = angle.parentNode;
                            if (!p.classList.contains('tb_angle_container')) {
                                p = angle;
                            }
                            
                            text.style.display = p.style.display = val === 'radial'?'none':'';
                            circle.parentNode.style.display = val === 'radial'?'':'none';
                        };
                if (update) {
                    instance.settings = Object.assign({}, instance.settings, args);
                    instance.settings.type = typeV;
                    instance.settings.circle = circle.checked;
                    instance.isInit = false;
                    instance.update();
                    instance.isInit = true;
                } else {
                    clear.tfOn(Themify.click, e=>{
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        is_removed = true;
                        instance.settings.gradient = $.ThemifyGradient.default;
                        instance.update();
                        is_removed = false;
                    });
    
                    type.tfOn('change', function (e) {
                        const v = this.value;
                        instance.setType(v);
                        callback(v);
                    }, {passive: true});

                    circle.tfOn('change', function () {
                        instance.setRadialCircle(this.checked);
                    }, {passive: true});

                    angle.tfOn('change', function () {
                        let val = parseInt(this.value);
                        if (!val) {
                            val = 0;
                        }
                        instance.setAngle(val);
                    }, {passive: true});
                    
                    gradient.appendChild(clear);
                }
                callback(self.getStyleVal(type.id));
            },
            update(id, v, self) {
                const nid = id + '-gradient',
                        input = self.getEl(nid);
                if (input !== null) {
                    const angle = self.getEl(nid + '-angle'),
                            type = self.getEl(nid + '-type'),
                            circle = self.getEl(id + '-circle-radial'),
                            gradient = input.previousElementSibling,
                            text = circle.previousElementSibling;
                    this.controlChange(self, gradient, input, null, type, angle, circle.tfClass('tb_checkbox')[0], text, true);
                }
            },
            render(data, self) {
                const wrap = doc.createElement('div'),
                        text = doc.createElement('span'),
                        gradient = doc.createElement('div'),
                        input = doc.createElement('input'),
                        clear = doc.createElement('button'),
                        select=self.select.render({
                            options:{
                                linear:self.label.linear,
                                radial:self.label.radial
                            },
                            class:'themify-gradient-type',
                            id:data.id + '-gradient-type',
                            control:false
                        },self);
                wrap.className = 'themify-gradient-field tf_w tf_rel';
                if (data.option_js !== und) {
                    wrap.className += ' tb_group_element_gradient';
                }
                
                text.textContent = self.label.rotation;
                gradient.className = 'tb_gradient_container tf_w';
                input.type = 'hidden';
                input.className = 'themify-gradient tb_lb_option';
                input.dataset.id = data.id;
                input.id = data.id + '-gradient';
                clear.className = 'tb_clear_gradient tf_close';
                clear.type = 'button';
                let tooltip = doc.createElement('span');
                tooltip.className = 'themify_tooltip';
                tooltip.innerText = self.label.clear_gradient;
                clear.appendChild(tooltip);

                const angleData = api.Helper.cloneObject(data);
                angleData.id = data.id + '-gradient-angle';
                const angleWarp = self.angle.render(angleData, self);
                wrap.append(select,angleWarp, text, self.checkboxGenerate('checkbox',
                        {
                            id: data.id + '-circle-radial',
                            options: [{name: '1', value: self.label.circle_radial}]
                        }
                ), gradient, input);

                self.initControl(input, data);
                self.afterRun.push( ()=> {
                    this.controlChange(self, gradient, input, clear, wrap.querySelector('.themify-gradient-type'), angleWarp.tfClass('tb_angle_input')[0], wrap.tfClass('tb_checkbox')[0], text);
                });
                return wrap;
            }
        },
        fontColor: {
            update(id, v, self) {
                self.radio.update(id, self.getStyleVal(id), self);
            },
            render(data, self) {
                data.isFontColor = true;
                const roptions = {
                    id: data.id,
                    type: 'radio',
                    option_js: true,
                    isFontColor: true,
                    options: [
                        {value: data.s + '_solid', name: self.label.solid},
                        {value: data.g + '_gradient', name: self.label.gradient}
                    ]
                },
                        radioWrap = self.radioGenerate('radio', roptions),
                        radio = radioWrap.querySelector('.tb_lb_option'),
                        colorData = api.Helper.cloneObject(data);
                colorData.label = '';
                colorData.type = 'color';
                colorData.id = data.s;
                colorData.prop = 'color';
                colorData.wrap_class = 'tb_group_element_' + data.s + '_solid';

                const color = self.create([colorData]);

                colorData.id = data.g;
                colorData.wrap_class = 'tb_group_element_' + data.g + '_gradient';
                colorData.type = 'gradient';
                colorData.prop = 'background-image';

                const gradient = self.create([colorData]);
                self.afterRun.push(() => {
                    const field = radio.parentNode.closest('.tb_field');
                    field.parentNode.insertBefore(color, field.nextElementSibling);
                    field.parentNode.insertBefore(gradient, field.nextElementSibling);
                });
                return radioWrap;
            }
        },
        imageGradient: {
            update(id, v, self) {
                self.radio.update(id + '-type', self.getStyleVal(id + '-type'), self);
                self.mediaFile.update(id, v, self);
                self.gradient.update(id, v, self);
                const el = self.getEl(id);
                if (el !== null) {
                    let p = el.closest('.tb_tab'),
                            imageOpt = p.tfClass('tb_image_options'),
                            eid = p.tfClass('tb_gradient_image_color')[0].tfClass('tfminicolors-input')[0].id;
                    self.color.update(eid, self.getStyleVal(eid), self);
                    for (let i = 0; i < imageOpt.length; ++i) {
                        eid = imageOpt[i].tfClass('tb_lb_option')[0].id;
                        self.select.update(eid, self.getStyleVal(eid), self);
                    }
                }
            },
            render(data, self) {
                const wrap = doc.createElement('div'),
                        imageWrap = doc.createElement('div');
                wrap.className = 'tb_image_gradient_field';
                imageWrap.className = 'tb_group_element_image tf_w tf_rel';
                wrap.appendChild(self.radioGenerate('radio',
                        {type: data.type,
                            id: data.id + '-type',
                            options: [
                                {name: self.label.image, value: 'image'},
                                {name: self.label.gradient, value: 'gradient'}
                            ],
                            option_js: true
                        }
                ));
                const extend = api.Helper.cloneObject(data);
                extend.type = 'image';
                //image
                imageWrap.appendChild(self.mediaFile.render('image', api.Helper.cloneObject(extend), self));
                wrap.appendChild(imageWrap);
                //gradient
                extend.type = 'gradient';
                delete extend.class;
                delete extend.binding;
                wrap.appendChild(self.gradient.render(extend, self));
                self.afterRun.push(() => {
                    const group = {
                        wrap_class: 'tb_group_element_image tf_w tf_rel',
                        type: 'group',
                        options: []
                    };
                    //color
                    extend.prop = 'background-color';
                    extend.wrap_class = 'tb_gradient_image_color';
                    extend.label = self.label.bg_c;
                    extend.type = 'color';
                    extend.id = extend.colorId;
                    group.options.push(api.Helper.cloneObject(extend));

                    //repeat
                    extend.prop = 'background-mode';
                    extend.wrap_class = 'tb_image_options';
                    extend.label = self.label.b_r;
                    extend.repeat = true;
                    extend.type = 'select';
                    extend.id = extend.repeatId;
                    group.options.push(api.Helper.cloneObject(extend));

                    //position
                    extend.prop = 'background-position';
                    extend.wrap_class = 'tb_image_options';
                    extend.label = self.label.b_p;
                    extend.position = true;
                    extend.type = 'position_box';
                    extend.id = extend.posId;
                    delete extend.repeat;
                    group.options.push(api.Helper.cloneObject(extend));

                    const field = imageWrap.parentNode.closest('.tb_field');
                    field.parentNode.insertBefore(self.create([group]), field.nextElementSibling);
                });
                return wrap;
            }
        },
        layout: {
            controlChange(el) {
                el.tfOn(Themify.click, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.target !== el) {
                        const selected = e.target.closest('a');
                        if (selected !== null) {
                            const items = this.tfClass('tfl-icon');
                            for (let i = items.length - 1; i > -1; --i) {
                                items[i].classList.remove('selected');
                            }
                            selected.classList.add('selected');
                            Themify.triggerEvent(this, 'change', {val: selected.id});
                        }
                    }
                });
            },
            update(id, v, self) {
                const input = self.getEl(id);
                if (input !== null) {
                    const items = input.tfClass('tfl-icon');
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].classList.toggle('selected', v === items[i].id);
                    }
                    if (v === und) {
                        let def = input.dataset.default;
                        def = def === und ? items[0] : def.querySelector('#' + def);
                        def.classList.add('selected');
                    }
                }
            },
            render(data, self) {
                const p = doc.createElement('div');
                let options = self.getOptions(data),
                        v = self.getStyleVal(data.id);

                if (data.color === true && data.transparent === true) {
                    options = options.slice();
                    options.push({img: 'transparent', value: 'transparent', label: self.label.transparent});
                }
                p.className = 'themify-layout-icon';
                if (self.is_repeat === true) {
                    p.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    p.dataset.inputId = data.id;
                } else {
                    p.className += ' tb_lb_option';
                    p.id = data.id;
                }
                if (data.class !== und) {
                    p.className += ' ' + data.class;
                }

                if (v === und) {
                    const def = api.activeModel.type==='module'?api.activeModel.getPreviewSettings():null;
                    v = def && def[data.id]!==und?def[data.id]:options[0].value;
                }
                v=v.toString();

                for (let i = 0, len = options.length; i < len; ++i) {
                    let a = doc.createElement('a'),
                            tooltip = doc.createElement('span'),
                            sprite;
                    a.href = '#';
                    a.className = 'tfl-icon';
                    a.id = options[i].value;
                    if (v === options[i].value.toString()) {
                        a.className += ' selected';
                    }
                    tooltip.className = 'themify_tooltip';
                    tooltip.textContent = options[i].label;

                    if (data.mode === 'sprite' && options[i].img.indexOf('.png') === -1) {
                        sprite = doc.createElement('span');
                        sprite.className = 'tb_sprite';
                        if (options[i].img.indexOf('http') !== -1) {
                            sprite.style.backgroundImage = 'url(' + options[i].img + ')';
                        } else {
                            sprite.className += ' tb_' + options[i].img;
                        }
                    } else {
                        sprite = doc.createElement('img');
                        sprite.alt = options[i].label;
                        sprite.src = options[i].img.indexOf('http') !== -1 ? options[i].img : Themify.builder_url + 'editor/img/' + options[i].img;
                    }

                    a.append(sprite, tooltip);
                    p.appendChild(a);
                }
                this.controlChange(p);
                if (self.component === 'row' && (data.id === 'row_width' || data.id === 'row_height')) {
                    api.activeModel.options(p, data.type);
                } else {
                    self.initControl(p, data);
                }
                return p;
            }
        },
        layoutPart: {
            data: [],
            get() {
                return new Promise((resolve,reject)=>{
                        if(this.data.length !== 0){
                            resolve();
                        }
                        else{
                            api.Spinner.showLoader();
                            api.LocalFetch({action:'tb_get_library_items'}).then(data=>{
                                api.Spinner.showLoader('done');
                                this.data=data;
                                resolve();
                                
                            }).catch(err=>{
                                api.Spinner.showLoader('error');
                            });
                        }
                });
            },
            render(data, self) {
                data.setOptions=false;
                const s = self.values[data.id],
                    d = doc.createDocumentFragment(),
                    selectWrap = self.select.render(data, self),
                    edit = doc.createElement('a'),
                    add = doc.createElement('a'),
                    select = selectWrap.querySelector('select');
             
                this.get().then(()=>{
                    const currentLayoutId = api.LayoutPart && api.LayoutPart.id? api.LayoutPart.id.toString() : null;
                    select.appendChild(doc.createElement('option'));
                    for (let i = 0, len = this.data.length; i < len; ++i) {
                        if (currentLayoutId !== this.data[i].id.toString()) {
                            let opt = doc.createElement('option');
                            opt.value = this.data[i].post_name;
                            opt.textContent = this.data[i].post_title;
                            if (s === this.data[i].post_name) {
                                opt.selected = true;
                            }
                            select.appendChild(opt);
                        }
                    }
                });
                edit.target = add.target = '_blank';
                edit.className = 'tb_icon_btn';
                edit.href = data.edit_url;
                add.href = data.add_url;
                add.className = 'add_new tf_plus_icon tb_icon_btn tf_rel';
                edit.append(api.Helper.getIcon('ti-folder'), doc.createTextNode(self.label.mlayout));

                add.appendChild(doc.createTextNode(self.label.nlayout));
                d.append(selectWrap, doc.createElement('br'), add, edit);
                return d;
            }
        },
        separator: {
            render(data, self) {
                let seperator;
                const txt = self.label[data.label] !== und ? self.label[data.label] : data.label;
                if (txt !== und) {
                    seperator = data.wrap_class !== und ? doc.createElement('div') : doc.createDocumentFragment();
                    const h4 = doc.createElement('h4');
                    h4.textContent = txt;
                    seperator.append(doc.createElement('hr'), h4);
                    if (data.wrap_class !== und) {
                        seperator.className = data.wrap_class;
                    }
                } else if (data.html !== und) {
                    const tmp = doc.createElement('div');
                    tmp.innerHTML = data.html;
                    seperator = tmp.firstChild;
                    if (data.wrap_class !== und) {
                        seperator.className = data.wrap_class;
                    }
                } else {
                    seperator = doc.createElement('hr');
                    if (data.wrap_class !== und) {
                        seperator.className = data.wrap_class;
                    }
                }
                return seperator;
            }
        },
        multiColumns: {
            update(id, v, self) {
                const item = self.getEl(id);
                if (item !== null) {
                    if (v !== und) {
                        item.value = v;
                    } else if (item[0] !== und) {
                        item[0].selected = true;
                    }
                }
            },
            render(data, self) {
                const opt = [],
                        columnOptions = [
                            {
                                id: data.id + '_gap',
                                label: self.label.c_g,
                                type: 'range',
                                prop: 'column-gap',
                                selector: data.selector,
                                wrap_class: 'tb_multi_columns_wrap',
                                units: {
                                    px: {
                                        max: 500
                                    }
                                }
                            },
                            {   type: 'multi',
                                wrap_class: 'tb_multi_columns_wrap',
                                label: self.label.c_d,
                                options: [
                                    {
                                        type: 'color',
                                        id: data.id + '_divider_color',
                                        prop: 'column-rule-color',
                                        selector: data.selector
                                    },
                                    {
                                        type: 'range',
                                        id: data.id + '_divider_width',
                                        class: 'tb_multi_columns_width',
                                        prop: 'column-rule-width',
                                        selector: data.selector,
                                        units: {
                                            px: {
                                                max: 500
                                            }
                                        }
                                    },
                                    {
                                        type: 'select',
                                        id: data.id + '_divider_style',
                                        options: self.static.border,
                                        prop: 'column-rule-style',
                                        selector: data.selector
                                    }
                                ]
                            }


                        ];
                for (let i = 0; i < 7; ++i) {
                    opt[i] = i === 0 ? '' : i;
                }
                data.options = opt;
                const ndata = api.Helper.cloneObject(data);
                ndata.type = 'select';
                const wrap = self.select.render(ndata, self),
                        select = wrap.querySelector('select');
                self.afterRun.push(() => {
                    const field = select.closest('.tb_field');
                    field.parentNode.insertBefore(self.create(columnOptions), field.nextElementSibling);

                });
                return wrap;
            }
        },
        expand: {
            render(data, self) {
                const wrap = doc.createElement('fieldset'),
                        expand = doc.createElement('div'),
                        toggle = doc.createElement('div'),
                        txt = self.label[data.label] !== und ? self.label[data.label] : data.label;
                wrap.className = 'tb_expand_wrap';
                toggle.className = 'tb_style_toggle tb_closed';
                expand.className = 'tb_expanded_opttions';
                toggle.append(doc.createTextNode(txt), api.Helper.getIcon('ti-angle-up'));

                wrap.append(toggle, expand);
                toggle.tfOn(Themify.click, function (e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    if (this.dataset.done === und) {
                        this.dataset.done = true;
                        expand.appendChild(self.create(data.options));
                        if (self.clicked === 'setting') {
                            self.setUpEditors();
                        }
                        self.callbacks();
						Themify.trigger('tb_options_expand',expand);
                    }
                    this.classList.toggle('tb_closed');
                });
				self.afterRun.push(() => {
					Themify.trigger('tb_options_expand',expand);
				});
                return wrap;
            }
        },
        gallery: {
            file_frame: null,
            cache:new Map(),
            async init(btn, input) {
                let timer;
                const  openMediaLibrary = e=> {
                    e.stopPropagation();
                    if (this.file_frame === null) {
                        // Create the media frame.
                        this.file_frame = wp.media.frames.file_frame = wp.media({
                            frame: 'post',
                            state: 'gallery-library',
                            library: {
                                type: 'image'
                            },
                            title: wp.media.view.l10n.editGalleryTitle,
                            editing: true,
                            multiple: true,
                            selection: false
                        });
                        this.file_frame.el.classList.add('themify_gallery_settings');
                    } else {
                        this.file_frame.options.selection.reset();
                    }
                    wp.media.gallery.shortcode = attachments=> {
                        const props = attachments.props.toJSON(),
                                attrs = {};
                        if(props.order){
                            attrs.order=props.order;
                        }
                        if(props.orderby){
                            attrs.orderby=props.orderby;
                        }
                        if (attachments.gallery) {
                            Object.assign(attrs, attachments.gallery.toJSON());
                        }
                        attrs.ids = attachments.pluck('id');
                        // Copy the `uploadedTo` post ID.
                        if (props.uploadedTo) {
                            attrs.id = props.uploadedTo;
                        }
                        // Check if the gallery is randomly ordered.
                        if (attrs._orderbyRandom) {
                            attrs.orderby = 'rand';
                            delete attrs._orderbyRandom;
                        }
                        // If the `ids` attribute is set and `orderby` attribute
                        // is the default value, clear it for cleaner output.
                        if (attrs.ids && 'post__in' === attrs.orderby) {
                            delete attrs.orderby;
                        }
                        // Remove default attributes from the shortcode.
                        for (let key in wp.media.gallery.defaults) {
                            if (wp.media.gallery.defaults[key] === attrs[key]) {
                                delete attrs[key];
                            }
                        }
                        delete attrs._orderByField;
                        const shortcode = new topWindow.wp.shortcode({
                            tag: 'gallery',
                            attrs: attrs,
                            type: 'single'
                        });
                        wp.media.gallery.shortcode = clone;
                        return shortcode;
                    };

                    const v = input.value.trim(),
                    setShortcode = selection => {console.log(selection);
                        const v = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                        input.value = '[' + v + ']';
                        preview(selection.models);
                        Themify.triggerEvent(input, 'change');
                    };
                    if (v.length > 0) {
                        this.file_frame = wp.media.gallery.edit(v);
                        this.file_frame.state('gallery-edit');
                    } else {
                        this.file_frame.state('gallery-library');
                        this.file_frame.open();
                        this.file_frame.$el.find('.media-menu .media-menu-item').last().trigger('click');
                    }
                    this.file_frame.off('update', setShortcode).on('update', setShortcode);
                },
                clone = wp.media.gallery.shortcode,
                val = input.value.trim(),
                parseIds=shortcode=>{
                    const tmp=shortcode.replace(/  +/g, ' ').match(/ids.*?=.(.+?)["']/gi);
                    return tmp && tmp[0]?tmp[0].replace('ids','').replace('=','').replaceAll(' ','').replace(/["']/g,'').trim().split(','):null;
                },
                replaceShortcode=(oldV,newV)=>{
                    return oldV.replace(/  +/g, ' ').replace(/ids.*?=.(.+?)["']/ig,'ids="'+newV+'"');
                },
                removeItem=e=>{
                    const el=e.target?e.target.closest('.tf_close[data-id]'):null;
                    if(el){
                        e.stopPropagation();
                        const textarea=e.currentTarget.parentNode.tfTag('textarea')[0],
                            value=textarea.value,
                            shortcode=parseIds(value),
                            index=shortcode.indexOf(el.dataset.id);
                        if(index!==-1){
                            shortcode.splice(index, 1);
                            el.parentNode.remove();
                            textarea.value=shortcode.length>0?replaceShortcode(value,shortcode.join(',')):'';
                            if(timer){
                                clearTimeout(timer);
                            }
                            timer=setTimeout(()=>{
                                Themify.triggerEvent(textarea,'change');
                                timer=null;
                            },400);
                        }
                    }
                },
                sort=e=>{
                    if (e.which === 1) {
                        const el=e.target && !e.target.classList.contains('tf_close')?e.target.closest('.tb_gal_item'):null;
                        if(el){
                            e.stopImmediatePropagation();
                            let timer,
                                clone,
                                box,
                                holderHeight,
                                holderWidth,
                                dir,
                                prevY,
                                prevX,
                                theLast;
                            const doc = el.ownerDocument,
                                    dragX = el.offsetLeft - e.clientX,
                                    dragY = el.offsetTop - e.clientY,
                                    _startDrag = e=> {
                                        e.stopImmediatePropagation();
                                        doc.body.classList.add('tb_start_animate','tb_sort_start');
                                        const _this=e.currentTarget,
                                            b = _this.getBoundingClientRect(),
                                            parentNode=_this.parentNode;
                                        clone=el.cloneNode(true);
                                        clone.classList.add('tb_gal_clone');
                                        _this.after(clone);
                                        _this.classList.add('tb_sort_handler');
                                        box = parentNode.getBoundingClientRect();
                                        holderHeight = (b.height / 2) - parentNode.offsetTop;
                                        holderWidth = (b.width / 2) - parentNode.offsetLeft;
                                        _move(e);
                                    },
                                    _move = e=> {
                                        e.stopImmediatePropagation();
                                        let x =e.clientX,
                                                y = e.clientY;
                                        if (x < box.left) {
                                            x = box.left;
                                        } else if (x > box.right) {
                                            x = box.right;
                                        }
                                        if (y < box.top) {
                                            y = box.top;
                                        } else if (y > box.bottom) {
                                            y = box.bottom;
                                        }
                                        const moveTo =doc.elementFromPoint(x, y),
                                                clientX = x - holderWidth - box.left,
                                                clientY = y - holderHeight - box.top;

                                        e.currentTarget.style.transform = 'translate(' + clientX + 'px,' + clientY + 'px)';
                                     
                                        if (moveTo && moveTo !== e.currentTarget && moveTo.classList.contains('tb_gal_item')) {
                                            const side = y > prevY || x > prevX ? 'bottom' : 'top';
                                            if (dir !== side || theLast !== moveTo) {
                                                side === 'bottom' ? moveTo.after(clone) : moveTo.before(clone);
                                            }
                                            theLast = moveTo;
                                            dir = side;
                                        }
                                        prevY = y;
                                        prevX = x;
                                    };
                            el.tfOn('lostpointercapture', function (e) {
                                e.stopImmediatePropagation();
                                if(timer){
                                    cancelAnimationFrame(timer);
                                }
                                this.tfOff('pointermove', _startDrag, {passive: true, once: true})
                                .tfOff('pointermove', _move, {passive: true});
                                doc.body.classList.remove('tb_start_animate','tb_sort_start');
                                if(clone){
                                    const wr=this.closest('.tb_shortcode_preview'),
                                        items=wr.tfClass('tf_close'),
                                        textarea=wr.parentNode.tfTag('textarea')[0],
                                        shortcode=[];
                                    this.remove();
                                    clone.classList.remove('tb_gal_clone');
                                    for(let i=0,len=items.length;i<len;++i){
                                        shortcode.push(items[i].dataset.id);
                                    }
                                    textarea.value=replaceShortcode(textarea.value,shortcode.join(','));
                                    Themify.triggerEvent(textarea,'change');
                                }
                                timer=clone=prevY=prevX=dir=theLast=holderWidth=holderHeight=box=null;
                            }, {passive: true, once: true})
                            .tfOn('pointermove', _startDrag, {passive: true, once: true})
                            .tfOn('pointermove', _move, {passive: true})
                            .setPointerCapture(e.pointerId);
                        }
                    }
                },
                preview = images => {
                    const w = 40,
                        h = 40,
                        fr=doc.createDocumentFragment();
                    let prewiew_wrap=input.parentNode.tfClass('tb_shortcode_preview')[0];console.log(images);
                    for (let i = 0, len = images.length; i < len; ++i) {
                        if(images[i]){
                            let img = isNaN(images[i])?new Image(w,h):null,
                                wr=doc.createElement('div'),
                                remove=doc.createElement('button');
                            wr.className='tb_gal_item tf_loader tf_w tf_h tf_box tf_rel';
                            remove.type='button';
                            remove.className='tf_close';
                            remove.dataset.id=images[i].id || '';
                            try{
                                if(img!==null){
                                    img.decoding = 'async';
                                    if (!images[i].attributes) {
                                        img.src = images[i].url;
                                    } else {
                                        let attachment = images[i].attributes;
                                        img.src = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                                    }
                                    img.decode()
                                    .catch(()=>{})
                                    .finally(()=>{
                                        wr.append(remove,img);
                                        wr.classList.remove('tf_loader','tf_w','tf_h');
                                    });
                                }
                                fr.appendChild(wr);
                            }
                            catch(e){
                                console.log(e);
                            }
                        }
                    }
                    if (prewiew_wrap === und) {
                        prewiew_wrap=doc.createElement('div');
                        prewiew_wrap.className = 'tb_shortcode_preview tf_scrollbar';
                        prewiew_wrap.tfOn(Themify.click,removeItem,{passive:true})
                        .tfOn('pointerdown',sort,{passive:true})
                        input.after(prewiew_wrap);
                    }
                    else{
                        prewiew_wrap.innerHTML='';
                    }
                    prewiew_wrap.appendChild(fr);
                };
                if (val.length > 0) {
                    let res=this.cache.get(val);
                    if (!res) {
                        const tmp=parseIds(val);
                        if(tmp){
                            preview(tmp);
                        }
                        try{
                            res = await api.LocalFetch({
                                action:'tb_get_ajax_data',
                                dataset: 'gallery_shortcode',
                                val:val
                            });
                            if(!res.success){
                                throw '';
                            }
                            res=res.data;
                            this.cache.set(val,res);
                        } 
                        catch(e){  
                            api.Spinner.showLoader('error');
                            throw e;
                        }
                    }
                    preview(res);
                }
                btn.tfOn(Themify.click, openMediaLibrary,{passive:true});
            },
            render(data, self) {
                const d = doc.createDocumentFragment(),
                        btn = doc.createElement('button');
                let cl = data.class || '';
                cl += ' tb_shortcode_input';
                btn.type='button';
                btn.className = 'builder_button tb_gallery_btn';
                btn.textContent = self.label.add_gallery;
                data.class = cl;
                d.append(self.textarea.render(data, self), btn);

                self.afterRun.push(() => {
                    this.init(btn, btn.previousElementSibling);
                    if (self.is_new === true && self.type === 'gallery' && self.clicked === 'setting') {
                        Themify.triggerEvent(btn,Themify.click);
                    }
                });
                return d;
            }
        },
        textarea: {
            render(data, self) {
                const f = doc.createDocumentFragment(),
                        area = doc.createElement('textarea'),
                        v = self.getStyleVal(data.id),
                        ev=data.control && data.control.event?data.control.event:'keyup';
                if (self.is_repeat === true) {
                    area.className = self.is_sort === true ? 'tb_lb_sort_child' : 'tb_lb_option_child';
                    area.dataset.inputId = data.id;
                } else {
                    area.className = 'tb_lb_option';
                    area.id = data.id;
                }
                if(data.class !== und){
                    area.className += ' '+data.class;
                }
                if (v !== und) {
                    area.value = v;
                }
                if (data.rows !== und) {
                    area.rows = data.rows;
                }
                if (data.readonly !== und && data.readonly) {
                    area.readonly=1;
                }
                f.appendChild(self.initControl(area, data));
                if (data.codeeditor !== und) {
                    api.Helper.codeMirror(area,data.codeeditor ).then(obj => {
                        if(obj){
                            obj.editor.on('change', cm=> {
                               Themify.triggerEvent(area, ev);
                            });
                        }
                    });
                }
                if (data.after !== und) {
                    f.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    f.appendChild(self.description(data.description));
                }
                return f;
            }
        },
        address: {
            render(data, self) {
                return self.textarea.render(data, self);
            }
        },
        wp_editor: {
            render(data, self) {
                const wrapper = doc.createElement('div'),
                        tools = doc.createElement('div'),
                        media_buttons = doc.createElement('div'),
                        add_media = doc.createElement('button'),
                        icon = doc.createElement('span'),
                        tabs = doc.createElement('div'),
                        switch_tmce = doc.createElement('button'),
                        switch_html = doc.createElement('button'),
                        container = doc.createElement('div'),
                        quicktags = doc.createElement('div'),
                        textarea = doc.createElement('textarea');
                let id;

                wrapper.className = 'wp-core-ui wp-editor-wrap tmce-active';
                textarea.className = 'tb_lb_wp_editor fullwidth';
                if (self.is_repeat === true) {
                    id = 'tb_'+Math.random().toString(36).substr(2, 7);
                    textarea.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    textarea.dataset.inputId = data.id;
                    if (data.control !== false) {
                        if (data.control === und) {
                            data.control = {};
                        }
                        data.control.repeat = true;
                    }
                } else {
                    textarea.className += ' tb_lb_option';
                    id = data.id;
                }
                textarea.id = id;
                wrapper.id = 'wp-' + id + '-wrap';
                tools.id = 'wp-' + id + '-editor-tools';
                tools.className = 'wp-editor-tools';

                media_buttons.id = 'wp-' + id + '-media-buttons';
                media_buttons.className = 'wp-media-buttons';

                add_media.type = 'button';
                add_media.className = 'button insert-media add_media';
                // add_media.dataset.editor = id;
                icon.className = 'wp-media-buttons-icon';

                tabs.className = 'wp-editor-tabs';

                switch_tmce.type = 'button';
                switch_tmce.className = 'wp-switch-editor switch-tmce';
                switch_tmce.id = id + '-tmce';
                switch_tmce.dataset.wpEditorId = id;
                switch_tmce.textContent = self.label.visual;

                switch_html.type = 'button';
                switch_html.className = 'wp-switch-editor switch-html';
                switch_html.id = id + '-html';
                switch_html.dataset.wpEditorId = id;
                switch_html.textContent = self.label.text;

                container.id = 'wp-' + id + '-editor-container';
                container.className = 'wp-editor-container';

                quicktags.id = 'qt_' + id + '_toolbar';
                quicktags.className = 'quicktags-toolbar';

                if (data.class !== und) {
                    textarea.className += ' ' + data.class;
                }
                if (self.values[data.id] !== und) {
                    textarea.value = self.values[data.id];
                }
                textarea.rows = 12;
                textarea.cols = 40;
                container.append(textarea, quicktags);

                tabs.append(switch_tmce, switch_html);

                add_media.append(icon, doc.createTextNode(self.label.add_media));

                media_buttons.appendChild(add_media);
                tools.append(media_buttons, tabs);
                wrapper.append(tools, container);
                self.editors.push({el: textarea, data: data});
                return wrapper;
            }
        },
		/* Order By field in Post module, provides backward compatibility */
		orderby_post : {
			render( data, self ) {
				let v = self.getStyleVal( data.id );
				if ( v === 'meta_value_num' ) {
					self.values[ data.id ] = 'meta_value';
					self.values.meta_key_type = 'NUMERIC';
				}
				data.type = 'select';
				return self.select.render( data, self );
			}
		},
        select: {
            cache:new Map(),
            update(id, v, self) {
                const item = self.getEl(id);
                if (item !== null) {
                    if (v !== und) {
                        item.value = v;
                    } else if (item[0] !== und) {
                        item[0].selected = true;
                    }
                }
            },
            populate(data,select,self,v){
                if (data.optgroup) {
                    const optgroups = self.getOptions(data);
                    for (let k in optgroups) {
                        if (optgroups[k].label !== und) {
                            let o = doc.createElement('optgroup');
                            o.setAttribute('label', optgroups[k].label);
                            o.appendChild(this.make_options(optgroups[k], v, self));
                            select.appendChild(o);
                        } else {
                            select.appendChild(this.make_options(optgroups[k], v, self));
                        }
                    }
                } else {
                    select.appendChild(this.make_options(data, v, self));
                }
            },
            make_options(data, v, self) {
                const d = doc.createDocumentFragment(),
                        options = self.getOptions(data) || data;
                for (let k in options) {
                    let opt = doc.createElement('option');
                    opt.value = k;
                    opt.text = options[k];
                    // Check for responsive disable
                    if (und !== data.binding && und !== data.binding[k] && und !== data.binding[k].responsive && und !== data.binding[k].responsive.disabled && -1 !== data.binding[k].responsive.disabled.indexOf(data.id)) {
                        opt.className = 'tb_responsive_disable';
                    }
                    if ( ( Array.isArray( v ) && v.includes( k ) ) || v === k || (v === und && k === data.default)) {
                        opt.selected = true;
                    }
                    d.appendChild(opt);
                }
                return d;
            },
            render(data, self) {
                const select_wrap = doc.createElement('div'),
                        select = doc.createElement('select'),
                        d = doc.createDocumentFragment(),
                        v = self.getStyleVal(data.id),
                        key=data.dataset;
                select_wrap.className = 'selectwrapper tf_inline_b tf_vmiddle tf_rel';
				if ( data.multiple ) {
					select.setAttribute( 'multiple', true );
					select_wrap.className += ' multi';
				}
                if (self.is_repeat === true) {
                    select.className = self.is_sort === true ? 'tb_lb_sort_child' : 'tb_lb_option_child';
                    select.dataset.inputId = data.id;
                } else {
                    select.className = 'tb_lb_option';
                    select.id = data.id;
                }
                select.className += ' tf_scrollbar';
                if (data.class !== und) {
                    select.className += ' ' + data.class;
                }
                if(data.setOptions!==false){
                    if (key!== und) {
                        if (this.cache.has(key)) {
                            this.populate(this.cache.get(key),select,self,v);
                        } else {
                            const ajaxData={
                                action: 'tb_get_ajax_data',
                                dataset: key
                            };
							/* additional parameters to send to tb_get_ajax_data */
							if ( data.dataset_args ) {
								ajaxData.args = data.dataset_args;
							}
                            select_wrap.className+=' tf_lazy';
                            api.LocalFetch(ajaxData).then(res=>{
                                if (res.success) {
                                    this.cache.set(key,res.data);
                                    this.populate(res.data,select,self,v);
                                }
                                else{
                                    throw '';
                                }
                            })
                            .catch(e=>{
                                api.Spinner.showLoader('error');
                            }).finally(()=>{
                                select_wrap.classList.remove('tf_lazy');
                            });
                        }
                    } else {
                        this.populate(data,select,self,v);
                    }
                }
                select_wrap.appendChild(self.initControl(select, data));
                d.appendChild(select_wrap);
                if (data.after !== und) {
                    d.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    d.appendChild(self.description(data.description));
                }
                if (data.tooltip !== und) {
                    d.appendChild(self.hint(data.tooltip));
                }
                return d;
            }
        },
        font_select: {
            loaded_fonts: [],
            fonts: {},
            safe: {},
            google:{},
            cf: {},
            updateFontVariant(value, weight, self, type) {
                if (!weight) {
                    return;
                }
                type = '' === type || type === und ? und !== this.google[value] ? 'google' : 'cf' : type;
                type = 'webfont' === type ? 'fonts' : type;
                const variants = (this[type][value] && this[type][value].v) ? this[type][value].v : null;
                if (!variants || variants.length === 0) {
                    weight.closest('.tb_field').classList.add('_tb_hide_binding');
                    return;
                }

                let selected = self.getStyleVal(weight.id);
                if (und === selected) {
                    selected = 'google' === type ? 'regular' : 'normal';
                }
                weight.dataset.selected = value;
                while (weight.firstChild !== null) {
                    weight.removeChild(weight.lastChild);
                }
                weight.closest('.tb_field').classList.remove('_tb_hide_binding');
                for (let i = 0, len = variants.length; i < len; ++i) {
                    let opt = doc.createElement('option');
                    opt.value = opt.textContent = variants[i];
                    if (variants[i] === selected) {
                        opt.selected = true;
                    }
                    weight.appendChild(opt);
                }
            },
            loadGoogleFonts(fontFamilies) {
                fontFamilies = [...new Set((fontFamilies.split('|')))];
                const result = {google: [], cf: []},
                        loaded = [],
                        loading = ()=>{
                            for (let i = loaded.length - 1; i > -1; --i) {
                                if (this.loaded_fonts.indexOf(loaded[i]) === -1) {
                                    this.loaded_fonts.push(loaded[i]);
                                }
                            }
                            Themify.trigger('tb_font_stylesheet_loaded', [this, loaded]);
                        };
                for (let i = fontFamilies.length - 1; i > -1; --i) {
                    if (fontFamilies[i] && this.loaded_fonts.indexOf(fontFamilies[i]) === -1 && (result.google.indexOf(fontFamilies[i]) === -1 || result.cf.indexOf(fontFamilies[i]) === -1)) {
                        let req = fontFamilies[i].split(':'),
                                weight = ('regular' === req[1] || 'normal' === req[1] || 'italic' === req[1] || parseInt(req[1])) ? req[1] : '400,700',
                                f = req[0].split(' ').join('+') + ':' + weight;
                        if (this.loaded_fonts.indexOf(f) === -1) {
                            let type = this.cf[req[0]] !== und ? 'cf' : 'google';
                            f += 'google' === type ? ':latin,latin-ext' : '';
                            result[type].push(f);
                            loaded.push(f);
                        }
                    }
                }
                if (result.google.length > 0) {
                    const url = window.location.protocol + '//fonts.googleapis.com/css?family=' + encodeURI(result.google.join('|')) + '&display=swap';
                    Themify.loadCss(url,null, false).then(loading);
                    if (api.mode === 'visual') {
                        topWindow.Themify.loadCss(url,null, false);
                    }
                }
                if (result.cf.length > 0) {
                    const url = themifyBuilder.cf_api_url + encodeURI(result.cf.join('|'));
                    Themify.loadCss(url,null, false).then(loading);
                    if (api.mode === 'visual') {
                        topWindow.Themify.loadCss(url,null, false);
                    }
                }
            },
            controlChange(select, preview, pw, self) {
                const _this = this,
                    $combo = $(select).comboSelect({
                    comboClass: 'themify-combo-select',
                    comboArrowClass: 'themify-combo-arrow',
                    comboDropDownClass: 'themify-combo-dropdown tf_scrollbar',
                    inputClass: 'themify-combo-input',
                    disabledClass: 'themify-combo-disabled',
                    hoverClass: 'themify-combo-hover',
                    selectedClass: 'themify-combo-selected',
                    markerClass: 'themify-combo-marker'
                }).parent('div');
                $combo[0].tfOn(Themify.click, function (e) {
                    const target = e.target;
                    if (target.classList.contains('themify-combo-item')) {
                        const value = target.dataset.value,
                                tab = select.closest('.tb_tab');
                        let type = this.querySelector('option[value="' + value + '"]');
                        if (type) {
                            type = type.dataset.type;
                        }
                        if ('webfont' !== type && value && _this.loaded_fonts.indexOf(value) === -1) {
                            _this.loadGoogleFonts(value);
                        }
                        if (tab) {
                            _this.updateFontVariant(value, tab.tfClass('font-weight-select')[0], self, type);
                        }
                        setTimeout(() => {
                            Themify.triggerEvent(select, 'change');
                        }, 10);
                    }
                }, {passive: true})
                .tfOn('pointerover', function (e) {
                    const target = e.target;
                    if (target.classList.contains('themify-combo-item')) {
                        let value = target.dataset.value;
                        if (value) {
                            if (!$(target).is(':visible')) {
                                return;
                            }
                            if (value === 'default') {
                                value = 'inherit';
                            }
                            preview.style.top = target.offsetTop - target.parentNode.scrollTop + 30 + 'px';
                            preview.style.fontFamily = value;
                            preview.style.display = 'block';

                            if (value !== 'inherit' && !target.classList.contains('tb_font_loaded')) {
                                target.classList.add('tb_font_loaded');
                                const mode = target.ownerDocument === topWindow.document ? 'top' : 'bottom';
                                if (_this.fonts[mode] === und) {
                                    _this.fonts[mode] = [];
                                }
                                if (_this.fonts[mode].indexOf(value) === -1) {
                                    const callback = value=> {
                                        _this.fonts[mode].push(value);
                                        pw.classList.remove('themify_show_wait');
                                    };
                                    let type = this.querySelector('option[value="' + value + '"]');
                                    if (type) {
                                        type = type.dataset.type;
                                    }
                                    if (type && type !== 'webfont') {
                                        let url = '';
                                        if ('google' === type) {
                                            url = window.location.protocol + '//fonts.googleapis.com/css?family=' + encodeURI(value) + '&display=swap';
                                        } else if ('cf' === type) {
                                            url = themifyBuilder.cf_api_url + encodeURI(value);
                                        }
                                        if (url !== '') {
                                            pw.classList.add('themify_show_wait');
                                            const tf = mode === 'top' ? topWindow.Themify : Themify;
                                            tf.loadCss(url, null,false).then(callback);
                                        }
                                    } else {
                                        callback(value);
                                    }
                                }
                                target.style.fontFamily = value;
                            }
                        }
                    }
                }, {passive: true});
                $combo.trigger('comboselect:open')
                        .on('comboselect:close', ()=> {
                            preview.style.display = 'none';
                        });
                $combo[0].tfClass('themify-combo-arrow')[0].tfOn(Themify.click,  ()=> {
                    preview.style.display = 'none';
                }, {passive: true});
            },
            update(id, v, self) {
                const select = self.getEl(id);
                if (select !== null) {
                    if (v === und) {
                        v = '';
                    }
                    select.value = v;
                    this.updateFontVariant(v, select.closest('.tb_tab').tfClass('font-weight-select')[0], self);
                    if (select.dataset.init === und) {
                        const groups = select.tfTag('optgroup'),
                                opt = doc.createElement('option');
                        while (groups[0].firstChild) {
                            groups[0].removeChild(groups[0].lastChild);
                        }
                        while (groups[1].firstChild) {
                            groups[1].removeChild(groups[1].lastChild);
                        }
                        opt.value = v;
                        opt.selected = true;
                        if (this.safe[v] !== und) {
                            opt.textContent = this.safe[v];
                            groups[0].appendChild(opt);
                        } else if (this.google[v] !== und) {
                            opt.textContent = this.google[v].n;
                            groups[1].appendChild(opt);
                        } else if (this.cf[v] !== und) {
                            opt.textContent = this.cf[v].n;
                            groups[2].appendChild(opt);
                        } else {
                            opt.textContent = v;
                            groups[0].appendChild(opt);
                        }
                    } else {
                        select.parentNode.tfClass('themify-combo-input')[0].value = v;
                    }
                }
            },
            render(data, self) {
                const wrapper = doc.createElement('div'),
                        select = doc.createElement('select'),
                        preview = doc.createElement('span'),
                        pw = doc.createElement('span'),
                        d = doc.createDocumentFragment(),
                        empty = doc.createElement('option'),
                        v = self.getStyleVal(data.id),
                        _this = this,
                        group = {safe: self.label.safe_fonts, google: self.label.google_fonts},
                        cfEmpty = Object.keys(this.cf).length < 1;
                if (false === cfEmpty) {
                    group.cf = self.label.cf_fonts;
                }
                wrapper.className = 'tb_font_preview_wrapper selectwrapper';
                select.className = 'tb_lb_option font-family-select tf_scrollbar';
                select.id = data.id;
                preview.className = 'tb_font_preview';
                pw.textContent = self.label.font_preview;
                empty.value = '';
                empty.textContent = '---';
                d.appendChild(empty);
                if (data.class !== und) {
                    select.className += ' ' + data.class;
                }
                const groupKeys = ['google', 'safe'];
                if (false === cfEmpty) {
                    groupKeys.push('cf');
                }
                for (let i = groupKeys.length - 1; i > -1; --i) {
                    let optgroup = doc.createElement('optgroup');
                    optgroup.label = group[groupKeys[i]];
                    if (v !== und) {
                        let opt = doc.createElement('option'),
                                txt;
                        opt.value = v;
                        opt.selected = true;
                        if ('cf' === groupKeys[i] && this.cf[v] !== und) {
                            txt = this.cf[v].n;
                        } else if ('safe' === groupKeys[i] && this.safe[v] !== und) {
                            txt = this.safe[v];
                        } else if ('google' === groupKeys[i] && this.google[v] !== und) {
                            txt = this.google[v].n;
                        } else {
                            txt = und !== this.cf[v] ? this.cf[v].n : v;
                        }
                        opt.textContent = txt;
                        optgroup.appendChild(opt);
                    }
                    d.appendChild(optgroup);
                }
                const focusIn = function () {
                    this.tfOff('focusin tf_init', focusIn, {once: true, passive: true});
                    const fonts = _this.safe,
                            f = doc.createDocumentFragment(),
                            sel = this.querySelector('select'),
                            groups = sel.tfTag('optgroup');
                    sel.dataset.init = true;
                    if (v !== und) {
                        for (let h = groups.length - 1; h > -1; --h) {
                            while (groups[h].firstChild) {
                                groups[h].removeChild(groups[h].lastChild);
                            }
                        }
                    }
                    for (let i in fonts) {
                        let opt = doc.createElement('option');
                        opt.value = i;
                        opt.textContent = fonts[i];
                        opt.dataset.type = 'webfont';
                        if (v === i) {
                            opt.selected = true;
                        }
                        f.appendChild(opt);
                    }
                    groups[cfEmpty ? 0 : 1].appendChild(f);
                    const extGroups = ['google'];
                    if (false === cfEmpty) {
                        extGroups.unshift('cf');
                    }
                    for (let g = extGroups.length - 1; g > -1; --g) {
                        let ff = _this[extGroups[g]],
                                fr = doc.createDocumentFragment();
                        for (let i in ff) {
                            let opt = doc.createElement('option');
                            opt.value = i;
                            opt.dataset.type = extGroups[g];
                            opt.textContent = ff[i].n;
                            if (v === i) {
                                opt.selected = true;
                            }
                            fr.appendChild(opt);
                        }
                        groups['cf' === extGroups[g] ? 0 : cfEmpty ? 1 : 2].appendChild(fr);
                    }
                    _this.controlChange(sel, preview, pw, self);
                };
                wrapper.tfOn('focusin tf_init', focusIn, {once: true, passive: true});

                select.appendChild(d);
                preview.appendChild(pw);
                wrapper.append(self.initControl(select, data), preview);
                self.afterRun.push(() => {
                    const weight = self.create([{type: 'select', label: 'f_w', selector: data.selector, class: 'font-weight-select', id: data.id + '_w', prop: 'font-weight'}]),
                            field = wrapper.closest('.tb_field'),
                            weightParent = weight.querySelector('.tb_field');
                    field.parentNode.insertBefore(weight, field.nextElementSibling);
                    _this.updateFontVariant(v, weightParent.querySelector('.font-weight-select'), self);
                });

                return wrapper;
            }
        },
        animation_select: {
            render(data, self) {
                const select_wrap = doc.createElement('div'),
                        select = doc.createElement('select'),
                        options = self.static.preset_animation,
                        v = self.values[data.id];
                select_wrap.className = 'selectwrapper tf_inline_b tf_vmiddle tf_rel';
                select.className = 'tb_lb_option tf_scrollbar';
                select.id = data.id;
                select.appendChild(doc.createElement('option'));
                for (let k in options) {
                    let group = doc.createElement('optgroup');
                    group.label = k;
                    for (let i in options[k]) {
                        let opt = doc.createElement('option');
                        opt.value = i;
                        opt.text = options[k][i];
                        if (v === i) {
                            opt.selected = true;
                        }
                        group.appendChild(opt);
                    }
                    select.appendChild(group);
                }
                select_wrap.appendChild(select);
                return select_wrap;
            }
        },
        sticky: {
            render(data, self) {
                const unstickOption = {},
                        selectedUID = api.activeModel.id,
                        _data = api.Helper.cloneObject(data),
                        type = data.key,
                        items = api.Registry.items;
                for (let v of items) {
                    let el=v[1];
                    if (type === el.type && el.id !== selectedUID) {
                        let uidText,
                            st = el.get('styling');
                        if ('row' === type && st && (st.custom_css_id || st.row_anchor)) {
                            uidText = st.custom_css_id ? '#' + st.custom_css_id : '#' + st.row_anchor;
                        } else if ('module' === type && st && st.custom_css_id) {
                            uidText = '#' + st.custom_css_id;
                        } else {
                            uidText = 'row' === type ? 'Row #' + el.id : el.get('mod_name') + ' #' + el.id;
                        }
                        unstickOption[el.id] = uidText;
                    }
                }
                _data.options = unstickOption;
                return self.select.render(_data, self);
            }
        },
        selectSearch: {
            update(val, search, options, self) {
                const f = doc.createDocumentFragment();
                let first = null;
                search.removeAttribute('data-value');
                search.value = '';
                if (options !== und) {
                    for (let k in options) {
                        let item = doc.createElement('div');
                        if (first === null) {
                            first = k;
                        }
                        item.dataset.value = k;
                        item.className = 'tb_search_item';
                        item.textContent = options[k];
                        if (val === k) {
                            item.className += ' selected';
                            search.dataset.value=k;
                            search.value = options[k];
                        }
                        f.appendChild(item);
                    }

                    if (search.value === '' && first !== null) {
                        search.value = options[first];
                        search.dataset.value=first;
                    }
                }
                return f;
            },
            events(search, container) {
                search.tfOn('keyup', function (e) {
                    const items = container.tfClass('tb_search_item'),
                            val = this.value.trim(),
                            r = new RegExp(val, 'i');
                    for (let i = 0, len = items.length; i < len; ++i) {
                        items[i].style.display = (val === '' || r.test(items[i].textContent)) ? 'block' : 'none';
                    }
                }, {passive: true});
                container.tfOn('mousedown', function (e) {
                    if (e.which === 1 && e.target.classList.contains('tb_search_item')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const all_items = this.tfClass('tb_search_item'),
                                _this = e.target;
                        for (let i = all_items.length - 1; i > -1; --i) {
                            all_items[i].classList.remove('selected');
                        }
                        _this.classList.add('selected');
                        const v = _this.dataset.value;
                        search.value = _this.textContent;
                        search.dataset.value = v;
                        search.blur();
                        search.previousElementSibling.blur();
                        Themify.triggerEvent(search, 'selectElement', {val: v});
                    }
                });
            },
            render(data, self) {
                const container = doc.createElement('div'),
                        arrow = doc.createElement('div'),
                        search = doc.createElement('input'),
                        loader = doc.createElement('span'),
                        search_container = doc.createElement('div');
                container.className = 'tb_search_wrapper';
                search.className = 'tb_search_input';
                search.autocomplete = 'off';
                search_container.className = 'tb_search_container tf_scrollbar';
                if (self.is_repeat === true) {
                    search.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    search.dataset.inputId = data.id;
                } else {
                    search.className += ' tb_lb_option';
                    search.id = data.id;
                }
                if (data.class !== und) {
                    search.className += ' ' + data.class;
                }
                arrow.tabIndex = 1;
                arrow.className = 'tb_search_arrow';
                loader.className = 'tf_loader';
                search_container.tabIndex = 1;
                search.type = 'text';
                search.placeholder = (data.placeholder !== und ? data.placeholder : data.label) + '...';
                search_container.appendChild(this.update(self.values[data.id], search, data.options, self));
                arrow.appendChild(loader);
                container.append(arrow, self.initControl(search, data), search_container);
                if (data.after !== und) {
                    container.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    container.appendChild(self.description(data.description));
                }
                if (data.tooltip !== und) {
                    container.appendChild(self.hint(data.tooltip));
                }
                this.events(search, search_container);

                return container;
            }
        },
        optin_provider: {
            cache: null,
            render(data, self) {
                const el = doc.createElement('div'),
                    callback = ()=>{
                        const setAfter=()=>{
                            el.parentNode.closest('.tb_field').after(self.create([this.cache[1],this.cache[2]]));
                            el.replaceWith(self.create([this.cache[0]]));
                        };
                        if(el.isConnected){
                            setAfter();
                        }
                        else{
                            self.afterRun.push(setAfter);
                        }
                    };
                if (this.cache === null) {
                    el.className='tf_loader';
                    api.Spinner.showLoader();
                    api.LocalFetch({action: 'tb_optin_get_settings'}).then(res=>{
                        api.Spinner.showLoader('spinhide');
                        this.cache = res;
                        callback();
                        self.callbacks();
                    })
                    .catch(e=>{
                        api.Spinner.showLoader('error');
                    });
                } else {
                    callback();
                }
                return el;
            }
        },
        check_map_api: {
            render(data, self) {
                if (!themifyBuilder[data.map + '_api']) {
                    const errData = {
                        type: 'separator',
                        html: '<span>' + themifyBuilder[data.map + '_api_err'] + '</span>',
                        wrap_class: 'tb_group_element_' + data.map
                    };
                    return self.separator.render(errData, self);
                } else {
                    return doc.createElement('span');
                }
            }
        },
        query_posts: {
            cacheTypes: null,
            cacheTerms:new Map(),
            render(data, self) {
                let tmp_el,
                    controller;
                const _this = this,
                        desc = data.description,
                        after = data.after,
                        values = self.values,
                        formatData =  options=> {
                            const result = {};
                            for (let k in options) {
                                result[k] = options[k].name;
                            }
                            return result;
                        },
                        update =  (item, val, options)=>{
                            const container = item.nextElementSibling;
                            while (container.firstChild!==null) {
                                container.lastChild.remove();
                            }
                            container.appendChild(self.selectSearch.update(val, item, options, self));
                        },
                        get =  (wr, val, type, s)=> {
                            if(controller){
                                controller.abort();
                            }
                            wr.classList.add('tb_search_wait');
                            controller = new AbortController();
                            return api.LocalFetch({
                                action: 'tb_get_post_types',
                                type: type,
                                v: val,
                                s: s || '',
                                all: data.all || ''
                            
                                },false,{signal: controller.signal})
                            .finally(()=>{
                                controller= null;
                                wr.classList.remove('tb_search_wait');
                            });
                            
                        };
                    let _data = api.Helper.cloneObject(data),
                            timeout = null;
                    tmp_el = doc.createElement('div');
                    tmp_el.id = data.id ? data.id : data.term_id;

                    self.afterRun.push( () =>{
                        let opt = ['id', 'tax_id', 'term_id', 'tag_id'],
                                fr = doc.createDocumentFragment(),
                                isInit = null,
                                getTerms = async (search, val, s,isType)=> {
                                    try{
                                        const cache_key = s ? val + '_' + s : val;
                                        let res=_this.cacheTerms.get(cache_key);
                                        if(!res){
                                            res=await get(search.parentNode, val, 'terms', s);
                                            _this.cacheTerms.set(cache_key,res);
                                        }

                                        if (data.term_id === und && data.tag_id === und) {
                                            return;
                                        }
                                        if (data.all) {
                                            const wrap = search.closest('.' + data.id),
                                                    is_all = wrap.querySelector('#' + data.id).value === 'All',
                                                    els = wrap.querySelectorAll('.' + data.tax_id + ',.' + data.term_id + ',.' + data.tag_id);
                                            for (let k = els.length - 1; k > -1; --k) {
                                                els[k].classList.toggle('tf_hide', is_all);
                                            }
                                        }
                                        const term_id = data.tag_id === und ? data.term_id.replace('#tmp_id#', val) : data.tag_id,
                                                parent = search.closest('.tb_input');
                                        let term_val;
                                        search.id = term_id;
                                        if (isInit === null && values[term_id] !== und) {
                                            term_val = values[term_id].split('|')[0];
                                        }
                                        if (!term_val) {
                                            term_val = 0;
                                        }
                                        update(search, term_val, res);
                                        if (s || s === '') {
                                            search.value = s;
                                        }
                                        if (isInit === null) {
                                            const multiply = doc.createElement('input'),
                                                    or = doc.createElement('span'),
                                                    wr = doc.createElement('div');
                                            or.innerHTML = self.label.or;
                                            multiply.type = 'text';
                                            multiply.className = 'query_category_multiple';
                                            wr.className = 'tb_query_multiple_wrap';
                                            wr.append(or, multiply);
                                            parent.insertBefore(wr, parent.nextSibling);
                                            if (after !== und) {
                                                parent.appendChild(self.after(after));
                                            }
                                            if (desc !== und) {
                                                parent.appendChild(self.description(desc));
                                            }
                                            if (data.slug_id !== und) {
                                                const referenceNode = parent.parentNode,
                                                        query_by = self.create([{
                                                                type: 'radio',
                                                                id: 'term_type',
                                                                label: self.label.query_by,
                                                                default: values.term_type === und && values[data.tax_id] === 'post_slug' ? 'post_slug' : 'category', //backward compatibility
                                                                option_js: true,
                                                                options: [
                                                                    {value: 'all', name: self.label.all_posts},
                                                                    {value: 'category', name: self.label.query_term_id},
                                                                    {value: 'post_slug', name: self.label.slug_label}
                                                                ]
                                                            }]),
                                                        slug = self.create([{
                                                                id: data.slug_id,
                                                                type: 'text',
                                                                class: 'large',
                                                                wrap_class: 'tb_group_element_post_slug',
                                                                help: self.label.slug_desc,
                                                                label: self.label.slug_label
                                                            }]);
                                                referenceNode.parentNode.insertBefore(query_by, referenceNode);
                                                referenceNode.parentNode.appendChild(slug);

                                            }
                                            if (data.sticky_id !== und) {
                                                const sticky = self.create([{
                                                        type: 'toggle_switch',
                                                        label: self.label.sticky_first,
                                                        id: data.sticky_id,
                                                        options: 'simple',
                                                        wrap_class: 'tb_group_element_all'
                                                    }]);
                                                parent.parentNode.parentNode.appendChild(sticky);
                                            }
                                            multiply.tfOn('change', ()=> {
                                                Themify.triggerEvent(search, 'queryPosts', {val: term_val});
                                            }, {passive: true});
                                            if (timeout !== null) {
                                                clearTimeout(timeout);
                                            }
                                            timeout = setTimeout(() => {
                                                self.callbacks();
                                            }, 2);
                                        }
                                        if(!isType){
                                            parent.tfClass('query_category_multiple')[0].value = term_val;

                                            if (isInit === true || self.is_new) {
                                                Themify.triggerEvent(search, 'queryPosts', {val: term_val});
                                            } else {
                                                ThemifyConstructor.settings = api.Helper.clear(api.Forms.serialize('tb_options_setting'));
                                            }
                                        }
                                        isInit = true;
                                    }
                                    catch(e){

                                    }
                                };
                        for (let i = 0, len = opt.length; i < len; ++i) {
                            if (!_data[opt[i]]) {
                                continue;
                            }
                            _data.id = _data[opt[i]];
                            _data.label = self.label['query_' + opt[i]];
                            _data.type = 'selectSearch';
                            if (opt[i] === 'term_id') {
                                _data.wrap_class = 'tb_search_term_wrap tb_group_element_category';
                                _data.class = 'query_category_single';
                                _data.help = self.label.query_desc;
                                _data.control = {control_type: 'queryPosts'};
                            } else if ((opt[i] === 'tax_id' || opt[i] === 'tag_id') && _data.term_id === und) {
                                _data.control = {control_type: 'queryPosts'};
                            }
                            delete _data.description;
                            delete _data.after;
                            let res = self.create([_data]);
                            if (true === data.just_current) {
                                delete _data.wrap_class;
                            }
                                let is_post = opt[i] === 'id',
                                        is_term = opt[i] === 'term_id' || opt[i] === 'tag_id',
                                        v = is_term ? '' : values[_data.id],
                                        search = res.querySelector('.tb_search_input');
                                search.tfOn('selectElement', function (e) {
                                    let val = e.detail.val,
                                            nextsearch = this.closest('.tb_field');
                                    if (!is_term) {
                                        if (nextsearch.nextElementSibling !== null) {
                                            nextsearch = nextsearch.nextElementSibling;
                                            if (!is_post && isInit === true && data.slug_id !== und) {
                                                nextsearch = nextsearch.nextElementSibling;
                                            }
                                            if (nextsearch !== null) {
                                                nextsearch = nextsearch.tfClass('tb_search_input')[0];
                                                if (is_post) {
                                                    if (_this.cacheTypes[val] !== und) {
                                                        if (true === data.just_current && 'tag' === values[data.tax_id]) {
                                                            values[data.tax_id] = 'post_tag';
                                                        }
                                                        update(nextsearch, values[data.tax_id], formatData(_this.cacheTypes[val].options));
                                                        Themify.triggerEvent(nextsearch, 'selectElement', {val: nextsearch.dataset.value});
                                                    }
                                                } else {
                                                    getTerms(nextsearch, val);
                                                    nextsearch.tfOn('input', function (e) {
                                                        setTimeout(() => {
                                                           getTerms(nextsearch, val, this.value.trim(),true);
                                                        }, 500);
                                                    }, {passive: true});
                                                }
                                            }
                                        } else if (!is_post) {
                                            Themify.triggerEvent(this, 'queryPosts', {val: this.dataset.value});
                                        }
                                    } else {
                                        nextsearch.tfClass('query_category_multiple')[0].value = val;
                                        Themify.triggerEvent(this, 'queryPosts', {val: val});
                                    }
                                }, {passive: true});
                                if (is_post) {
                                    const callback = () => {
                                        if (!v) {
                                            v = 'post';
                                        }
                                        update(search, v, formatData(_this.cacheTypes));
                                        Themify.triggerEvent(search, 'selectElement', {val: v});
                                        search = null;
                                    };
                                    if (_this.cacheTypes === null) {
                                        get(search.parentNode, null, 'post_types').then(res=> {
                                            _this.cacheTypes = res;
                                            if (data.just_current === true && und == v) {
                                                v = Object.keys(res);
                                            }
                                            callback();
                                        });
                                    } else {
                                        setTimeout(callback, 10);
                                    }
                                } 
                                else if (is_term && !data.id && data.taxonomy !== und) {
                                    getTerms(search, data.taxonomy);
                                    search.tfOn('input', function (e) {
                                        setTimeout(() => {
                                            getTerms(search, data.taxonomy, this.value.trim(),true);
                                        }, 500);
                                    }, {passive: true});
                                }
                            fr.appendChild(res);
                        }
                        if (_data.query_filter) {
                            let query_filter = self.create([
                                {
                                    type: 'multi',
                                    label: self.label.aq[0],
                                    id: 'query_date',
                                    options: [
                                        {
                                            id: 'query_date_from',
                                            label: self.label.aq[1],
                                            type: 'date',
                                            timepicker: false
                                        },
                                        {
                                            id: 'query_date_to',
                                            label: self.label.aq[2],
                                            type: 'date',
                                            timepicker: false
                                        }
                                    ],
                                    wrap_class: 'tb_query_filter' + (self.values.query_date_from || self.values.query_date_to ? '' : ' tf_hide')
                                },
                                {
                                    type: 'autocomplete',
                                    dataset: 'authors',
                                    id: 'query_authors',
                                    label: self.label.aq[7],
                                    wrap_class: 'tb_query_filter' + (self.values.query_authors ? '' : ' tf_hide'),
                                    help: self.label.aq[9]
                                },
                                {
                                    type: 'multi',
                                    label: self.label.aq[3],
                                    id: 'query_cf',
                                    options: [
                                        {
                                            type: 'autocomplete',
                                            dataset: 'custom_fields',
                                            id: 'query_cf_key',
                                            label: self.label.aq[4]
                                        },
                                        {
                                            id: 'query_cf_value',
                                            label: self.label.aq[5],
                                            type: 'text'
                                        },
                                        {
                                            id: 'query_cf_c',
                                            label: self.label.aq[6],
                                            type: 'select',
                                            options: {
                                                '': 'LIKE',
                                                'NOT LIKE': 'NOT LIKE',
												'EXISTS' : 'EXISTS',
                                                'NOT EXISTS': 'NOT EXISTS',
                                                '=': '=',
                                                '!=': '!=',
                                                '>': '>',
                                                '>=': '>=',
                                                '<': '<',
                                                '<=': '<='
                                            }
                                        }
                                    ],
                                    wrap_class: 'tb_query_filter' + (self.values.query_cf_key ? '' : ' tf_hide')
                                }
                            ]);
                            fr.appendChild(query_filter);

                            let hide_add_new = self.values.query_cf_key && self.values.query_authors && (self.values.query_date_from || self.values.query_date_to); /* if all advanced query fields have value, don't need to show the Add New button */
                            if (!hide_add_new) {
                                let tb_field = doc.createElement('div'),
                                        plusWrap = doc.createElement('div'),
                                        plus = doc.createElement('div'),
                                        ul = doc.createElement('ul'),
                                        label = doc.createElement('div'),
                                        span = doc.createElement('span');
                                if (!self.values.query_date_from && !self.values.query_date_to) {
                                    let li = doc.createElement('li');
                                    li.innerText = self.label.aq[0];
                                    li.dataset.id='query_date';
                                    ul.appendChild(li);
                                }
                                if (!self.values.query_authors) {
                                    let li = doc.createElement('li');
                                    li.innerText = self.label.aq[7];
                                    li.dataset.id='query_authors';
                                    ul.appendChild(li);
                                }
                                if (!self.values.query_cf_key) {
                                    let li = doc.createElement('li');
                                    li.innerText = self.label.aq[3];
                                    li.dataset.id='query_cf';
                                    ul.appendChild(li);
                                }
                                tb_field.className = 'tb_field';
                                plusWrap.className = 'tb_input tf_rel';
                                label.className = 'tb_label tb_empty_label';
                                plus.tabIndex='-1';
                                plus.className = 'tb_ui_dropdown_label tb_sort_fields_plus tf_plus_icon';
                                ul.className = 'tb_ui_dropdown_items tf_scrollbar';
                                span.innerText = self.label.aq[8];
                                plus.appendChild(span);
                                tb_field.append(label, plusWrap);
                                plusWrap.append(plus, ul);
                                fr.appendChild(tb_field);

                                ul.tfOn(Themify.click, function (e) {
                                    if (e.target.tagName === 'LI') {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        let id = e.target.dataset.id;
                                        this.closest('.tb_field[data-type="query_posts"], .tb_field[data-type="advanced_posts_query"]').querySelector('.' + id).classList.remove('tf_hide');
                                        e.target.style.display = 'none';
                                        e.target.removeAttribute('data-id');
                                        if (!this.querySelector('[data-id]')) {
                                            tb_field.classList.add('tf_hide');
                                        }
                                    }
                                });
                            }
                        }
                        tmp_el.parentNode.replaceChild(fr, tmp_el);
                        _data = tmp_el = null;
                    });
                return tmp_el;
            }
        },
        hook_content: {
            render(data, self) {
                return self.create([{
                        type: 'group',
                        label: self.label.hc[0],
                        display: 'accordion',
                        options: [
                            {
                                id: 'hook_content',
                                type: 'builder',
                                options: [
                                    {
                                        type: 'select',
                                        id: 'h',
                                        options: data.options,
                                        after: self.label.hc[1]
                                    },
                                    {
                                        type: 'textarea',
                                        id: 'c',
                                        wrap_class: 'tb_disable_dc',
                                        class: 'fullwidth'
                                    }
                                ]
                            }
                        ]
                    }]);
            }
        },
        position_box: {
            w: null,
            h: null,
            update(id, v, self) {
                let input = api.LightBox.el.querySelector('#' + id);
                if (input === null) {
                    input = api.LightBox.el.querySelector('[data-input-id="' + id + '"]');
                }
                if (input !== null) {
                    const wrap = input.closest('.tb_position_box_wrapper'),
                            handler = wrap.tfClass('tb_position_box_handle')[0],
                            label = wrap.tfClass('tb_position_box_label')[0],
                            positions = this.getPreDefinedPositions();
                    if (v) {
                        if (positions[v] !== und) {
                            v = positions[v];
                        }
                    } else {
                        v = '50,50';
                    }
                    input.value = v;
                    label.textContent = this.getLabel(v);
                    v = v.split(',');
                    handler.style.left = Math.ceil((v[0] * this.w) / 100) + 'px';
                    handler.style.top = Math.ceil((v[1] * this.h) / 100) + 'px';
                }
            },
            getLabel(val) {
                let pos;
                switch (val) {
                    case '0,0':
                        pos = 'Top Left';
                        break;
                    case '50,0':
                        pos = 'Top Center';
                        break;
                    case '100,0':
                        pos = 'Top Right';
                        break;
                    case '0,50':
                        pos = 'Center Left';
                        break;
                    case '50,50':
                        pos = 'Center Center';
                        break;
                    case '100,50':
                        pos = 'Center Right';
                        break;
                    case '0,100':
                        pos = 'Bottom Left';
                        break;
                    case '50,100':
                        pos = 'Bottom Center';
                        break;
                    case '100,100':
                        pos = 'Bottom Right';
                        break;
                    default:
                        const values = val.split(',');
                        pos = values[0] === '' ? 'Center Center' : 'X:' + values[0] + '% Y:' + values[1] + '%';
                        break;
                }
                return pos;
            },
            getPreDefinedPositions() {
                return {
                    'right-top': '100,0',
                    'right-center': '100,50',
                    'right-bottom': '100,100',
                    'left-top': '0,0',
                    'left-center': '0,50',
                    'left-bottom': '0,100',
                    'center-top': '50,0',
                    'center-center': '50,50',
                    'center-bottom': '50,100'
                };
            },
            click(e) {
                e.stopPropagation();
                let left,
                    top,
                    el = e.currentTarget.previousElementSibling,
                    item=e.target.closest('.tb_position_item');
                if (item) {
                    const pos = item.dataset.pos.split(',');
                    left = pos[0],
                    top = pos[1];
                    if (left === '50') {
                        left = this.w / 2;
                    } else if (left === '100') {
                        left = this.w;
                    }
                    if (top === '50') {
                        top = this.h / 2;
                    } else if (top === '100') {
                        top = this.h;
                    }
                } else {
                    left = e.offsetX;
                    top = e.offsetY;
                }
                el.style.left = left + 'px';
                el.style.top = top + 'px';
                this.changeUpdate(el, left, top);
            },
            changeUpdate(helper, left, top) {
                const l = +((left / this.w) * 100).toFixed(2),
                        t = +((top / this.h) * 100).toFixed(2),
                        label = helper.closest('.tb_position_box_wrapper').tfClass('tb_position_box_label')[0],
                        input = label.nextElementSibling;
                input.value = l + ',' + t;
                label.textContent = this.getLabel(l + ',' + t);
                Themify.triggerEvent(input, 'change');
            },
            render(data, self) {
                const _this = this,
                        positions = this.getPreDefinedPositions(),
                        v = self.getStyleVal(data.id),
                        wrapper = doc.createElement('div'),
                        boxWrap = doc.createElement('div'),
                        box = doc.createElement('div'),
                        handler = doc.createElement('div'),
                        label = doc.createElement('div'),
                        input = self.hidden.render(data, self);
                wrapper.className = 'tb_position_box_wrapper';
                boxWrap.className = 'tb_position_box_container tf_rel';
                box.className = 'tb_position_box tf_rel';
                handler.className = 'tb_position_box_handle';
                label.className = 'tb_position_box_label';
                for (let i in positions) {
                    let pos = doc.createElement('div'),
                            tooltip = doc.createElement('span'),
                            span = doc.createElement('span'),
                            vals = positions[i].split(',');
                    tooltip.className = 'themify_tooltip';
                    span.textContent = i.replace('-', ' ');
                    pos.className = 'tb_position_item';
                    pos.dataset.pos=positions[i];
                    pos.style.left = vals[0] + '%';
                    pos.style.top = vals[1] + '%';
                    tooltip.appendChild(span);
                    pos.appendChild(tooltip);
                    box.appendChild(pos);
                }
                handler.tfOn('pointerdown', function (e) {
                    if (e.which === 1) {
                        e.stopImmediatePropagation();
                        let timer;
                        const doc = this.ownerDocument,
                                el = this,
                                dragX = this.offsetLeft - e.clientX,
                                dragY = this.offsetTop - e.clientY,
                                maxW = _this.w,
                                maxH = _this.h,
                                _startDrag = e=> {
                                    doc.body.classList.add('tb_start_animate');
                                },
                                _move = e=> {
                                    e.stopImmediatePropagation();
                                    if(timer){
                                        cancelAnimationFrame(timer);
                                    }
                                    timer=requestAnimationFrame(()=>{
                                        let clientX = dragX + e.clientX,
                                                clientY = dragY + e.clientY;
                                        if (clientX > maxW) {
                                            clientX = maxW;
                                        } else if (clientX < 0) {
                                            clientX = 0;
                                        }
                                        if (clientY > maxH) {
                                            clientY = maxH;
                                        } else if (clientY < 0) {
                                            clientY = 0;
                                        }
                                        el.style.left = clientX + 'px';
                                        el.style.top = clientY + 'px';
                                        _this.changeUpdate(el, clientX, clientY);
                                    });
                                };
                        this.tfOn('lostpointercapture', function (e) {
                            e.stopImmediatePropagation();
                            if(timer){
                                cancelAnimationFrame(timer);
                            }
                            this.tfOff('pointermove', _startDrag, {passive: true, once: true})
                            .tfOff('pointermove', _move, {passive: true});
                            doc.body.classList.remove('tb_start_animate');
                            timer=null;
                        }, {passive: true, once: true})
                        .tfOn('pointermove', _startDrag, {passive: true, once: true})
                        .tfOn('pointermove', _move, {passive: true})
                        .setPointerCapture(e.pointerId);
                    }

                }, {passive: true});

                box.tfOn(Themify.click, e=>{
                    this.click(e);
                }, {passive: true});
                boxWrap.append(handler, box);
                wrapper.appendChild(boxWrap);
                if (data.after !== und) {
                    wrapper.appendChild(self.after(data));
                }
                wrapper.append(label, input);
                self.afterRun.push(() => {
                    setTimeout(() => {
                        const css = getComputedStyle(box);
                        _this.w = parseInt(css.getPropertyValue('width'));
                        _this.h = parseInt(css.getPropertyValue('height'));
                        _this.update(data.id, v, self);
                    }, 700);
                });
                return wrapper;
            }
        },
        slider_range: {
            render(data, self) {
                const container=doc.createElement('div'),
                    wrapper = doc.createElement('div'),
                    up=doc.createElement('input'),
                    low=doc.createElement('input'),
                    outputUp = doc.createElement('output'),
                    outputLow = doc.createElement('output'),
                    input = self.hidden.render(data, self),
                    fr=doc.createDocumentFragment(),
                    val = input.value;
                let min = 0,
                    max = 100,
                    u = '%',
                    isMulti = true,
                    def = 1,
                    st='--tb_slider_before:',
                    step=1,
                    inputRange=false;
                if (data.options !== und) {
                    if (data.options.min !== und) {
                        min = data.options.min;
                    }
                    if (data.options.max !== und) {
                        max = data.options.max;
                    }
                    if (data.options.unit !== und) {
                        u = data.options.unit;
                    }
                    if (data.options.range !== und) {
                        isMulti = false;
                    }
                    if (data.options.default !== und) {
                        def = data.options.default;
                    }
                    if (data.options.step !== und) {
                        step = data.options.step;
                    }
                    inputRange = !!data.options.inputRange;
                }
                
                container.className='tb_slider_container tf_w';
                min = parseFloat(min);
                max = parseFloat(max);
                up.type=low.type='range';
                up.min=low.min=min;
                up.step=low.step=step;
                up.max=low.max=max;
                outputUp.className=outputLow.className='tb_slider_output';
                outputUp.className+=' tb_slider_output_high';
                if(u){
                    outputUp.dataset.unit=outputLow.dataset.unit=u;
                }
                wrapper.tfOn('input',e => {
                    const t = e.target,
                    st=t===up?'before':'after',
                    lowVal=low.isConnected?parseFloat(low.value):'',
                    wr=e.currentTarget,
                    ouput=st==='before'?outputUp:outputLow,
                    upVal=parseFloat(up.value),
                    inputV=st==='before'?upVal:lowVal;
                    requestAnimationFrame(()=>{
                        let v=upVal;
                        if(lowVal!==''){
                            v=upVal>lowVal?(lowVal+','+upVal):(upVal+','+lowVal);
                        }
                        input.value=v;
                        if(inputRange!==false){
                            inputRange.value=v;
                        }
                        ouput.dataset['slider_'+st]=inputV;
                        wr.style.setProperty('--tb_slider_'+st, inputV);
                        Themify.triggerEvent(input,'change');
                    });
                },
                {passive:true}).className = 'tb_slider_wrapper tf_rel tf_w';
                wrapper.tabIndex='-1';
                
                if (isMulti) {
                    const v = !val ? [min, max] : val.split(',');
                    v[0]=parseFloat(v[0]);
                    v[0]=v[0]>max?max:(v[0]<min?min:v[0]);
                    if(v[1]===und){
                        v[1]=v[0];
                    }
                    else{
                        v[1]=parseFloat(v[1]);
                        v[1]=v[1]>max?max:(v[1]<min?min:v[1]);
                    }
                    outputLow.dataset.slider_after=low.value=v[0];
                    outputUp.dataset.slider_before=up.value=v[1];
                    outputLow.className+=' tb_slider_output_low';
                    
                    st+=v[1]+';--tb_slider_after:'+v[0];
                    wrapper.append(low,outputLow);
                } 
                else {
                    let v=val || def;
                    if(v.toString().indexOf(',')!==-1){
                        v=v.split(',')[0];
                    }
                    v=parseFloat(v);
                    v=v>max?max:(v<min?min:v);
                    outputUp.dataset.slider_before= up.value =v;
                    st+=v;
                    wrapper.className+=' tb_slider_wrapper_single';
                }
                if (data.wrap_class !== und) {
                    wrapper.className+= ' ' + data.wrap_class;
                }
                
                if(min>0){
                    st+=';--tb_slider_min:'+min;
                }
                if(max!==100){
                    st+=';--tb_slider_max:'+max;
                }
                wrapper.style=st;
                wrapper.append(up,outputUp,input);
                fr.appendChild(wrapper);
                if(inputRange===true){
                    const clone=api.Helper.cloneObject(data),
                        options=clone.options;
                    Object.assign(clone,options);
                    clone.id=clone.class=clone.options='';
                    clone.increment=clone.step;
                    inputRange=self.range.render(clone, self).querySelector('.tb_range');
                    inputRange.tfOn('change',e=>{
                        up.value=e.currentTarget.value;
                        Themify.triggerEvent(up,'input');
                    },{passive:true});
                    inputRange.value=up.value;
                    fr.appendChild(inputRange);
                }
                container.appendChild(fr);
                return container;

            }
        },
        range: {
            update(id, v, self) {
                const range = self.getEl(id);
                if (range !== null) {
                    range.value = v !== und ? v : '';
                    const unit_id = id + '_unit',
                            unit = api.LightBox.el.querySelector('#'+unit_id);
                    if (unit !== null && unit.tagName === 'SELECT') {
                        let v = self.getStyleVal(unit_id);
                        if (v === und) {
                            v = unit[0].value;
                        }
                        unit.value = v;
                        this.setData(range, (unit.selectedIndex !== -1 ? unit[unit.selectedIndex] : unit[0]));
                    }
                }
            },
            setMinMax(range,min,max,step){
                let v = parseFloat(range.value.trim());
                if (v > parseFloat(max) || v < parseFloat(min)) {
                    v = v > parseFloat(max) ? max : min;
                    range.value = step % 1 !== 0 ? parseFloat(v).toFixed(1).toString() : parseInt(v);
                }
            },
            setData(range, item) {
                const min = item.dataset.min,
                        max = item.dataset.max,
                        step = item.dataset.increment;
                range.min=min;
                range.max=max;
                range.step=step;
                this.setMinMax(range,min,max,step);
            },
            controlChange(range, unit, event) {
                const is_select = unit !== und && unit.tagName === 'SELECT',
                        _this = this;

                range.tfOn('pointerdown', function (e) {
                    if (e.which === 1) {
                        if (!(this.classList.contains('tb_angle_input') && !this.parentNode.tfClass('tb_angle_circle')[0])) {
                            e.stopImmediatePropagation();
                        }
                        let lastY = e.clientY,
                                timer;
                        const doc = this.ownerDocument,
                                that = this,
                                old_v = this.value,
                                max = parseFloat(this.max),
                                min = parseFloat(this.min),
                                step = this.step || 1,
                                is_increment = step % 1 !== 0,
                                increment = !is_increment ? parseInt(step) : parseFloat(step),
                                angle = this.parentNode.tfClass('tb_angle_dot')[0],
                                changeValue = condition=> {
                                    const cval = this.value || 0,
                                            val = !is_increment ? parseInt(cval) : parseFloat(cval);
                                    let v = 0;
                                    if ('increase' === condition) {
                                        v = val >= max ? max : (val + increment);
                                    } else {
                                        v = val <= min ? min : (val - increment);
                                    }
                                    this.value = +v.toFixed(2);
                                    if (angle) {
                                        angle.style.transform = 'rotate(' + v + 'deg)';
                                    }
                                },
                                _move = e=> {
                                    e.stopImmediatePropagation();
                                    if (timer) {
                                cancelAnimationFrame(timer);
                            }
                                    timer = requestAnimationFrame(() => {
                                        const y = e.clientY;
                                        if (y < lastY) {
                                            changeValue('increase');
                                        } else if (y > lastY) {
                                            changeValue('decrease');
                                        }
                                        lastY = y;
                                        Themify.triggerEvent(this, event);
                                    });
                                };
                        this.tfOn('lostpointercapture', function (e) {
                            e.stopImmediatePropagation();
                            this.tfOff('pointermove', _move, {passive: true});
                            if (timer) {
                                cancelAnimationFrame(timer);
                            }
                            timer = lastY = null;
                            if (that.value !== old_v) {
                                Themify.triggerEvent(that, event);
                                if(event!=='change'){//to detect finish
                                    Themify.triggerEvent(that, 'change');
                                }
                            }
                            doc.body.classList.remove('tb_start_animate','tb_move_drag');
                        }, {once: true, passive: true});
                        doc.body.classList.add('tb_start_animate','tb_move_drag');
                        this.tfOn('pointermove', _move, {passive: true});
                        if (this.classList.contains('tb_angle_input')) {
                            changeValue();
                        }
                        this.setPointerCapture(e.pointerId);
                    }
                }, {passive: true});

                if (is_select === true) {
                    unit.tfOn('change', function (e) {
                        _this.setData(range, this.options[ this.selectedIndex ]);
                        Themify.triggerEvent(range, event);
                    }, {passive: true});
                }
                if (unit !== und) {
                    this.setData(range, is_select ? (unit.selectedIndex !== -1 ? unit[unit.selectedIndex] : unit[0]) : unit);
                }
                range.tfOn('change', e=> {
                    this.setMinMax(range,range.min,range.max,range.step);
                }, {passive: true});
            },
            render(data, self) {
                const wrapper = doc.createElement('div'),
                        range_wrap = doc.createElement('div'),
                        input = doc.createElement('input');
                let v = data.value!==und?data.value:self.getStyleVal(data.id),
                        select;
                if (v === und) {
                    v = '';
                } else {
                    v = parseFloat(v);
                }
                wrapper.className = 'tb_tooltip_container tf_rel';
                if (data.wrap_class !== und) {
                    wrapper.className = ' ' + data.wrap_class;
                }
                range_wrap.className = 'tb_range_input tf_inline_b tf_rel';
                input.autocomplete = 'off';
                input.type = 'number';
                input.className = 'tb_range';
                if (v !== und) {
                    input.value = v;
                }
                if (data.placeholder !== und) {
                    input.placeholder = data.placeholder;
                }
                if (self.is_repeat === true) {
                    input.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.className += ' tb_lb_option';
                    input.id = data.id;
                }
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                range_wrap.appendChild(input);
                if (data.tooltip !== und) {
                    range_wrap.appendChild(self.hint(data.tooltip));
                }
                wrapper.appendChild(range_wrap);
                if (data.deg === true || data.units === und) {
                    input.min = data.min || 0;
                    input.max = data.deg === true ? 360 : (data.max || 1500);
                    input.step = data.increment || 1;
                } else {
                    const select_wrap = doc.createElement('div'),
                            keys = Object.keys(data.units);
                    select_wrap.className = 'selectwrapper noborder';
                    if (keys.length > 1) {
                        const uv =data.unit!==und?data.unit:self.getStyleVal(data.id + '_unit'),
                                select_id = data.id + '_unit';
                        select = doc.createElement('select');
                        select.className = 'tb_unit';

                        if (self.is_repeat === true) {
                            select.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                            select.dataset.inputId = select_id;
                        } else {
                            select.className += ' tb_lb_option';
                            select.id = select_id;
                        }
                        if (data.select_class !== und) {
                            select.className += ' ' + data.select_class;
                        }
                        for (let i in data.units) {
                            let opt = doc.createElement('option');

                            if (!data.units[i]) {
                                data.units[i] = {};
                            }
                            if (!data.units[i].min) {
                                data.units[i].min = 0;
                            }
                            if (!data.units[i].max) {
                                data.units[i].max = 100;
                            }
                            input.min=parseInt(data.units[i].min);
                            input.max=parseInt(data.units[i].max);
                            opt.value = i;
                            opt.textContent = i;
                            opt.dataset.min = data.units[i].min;
                            opt.dataset.max = data.units[i].max;
                            opt.dataset.increment = data.units[i].increment !== und ? data.units[i].increment : (i === 'em' || i === 'em' ? .1 : 1);
                            if (uv === i) {
                                opt.selected = true;
                            }
                            select.appendChild(opt);
                        }
                        self.initControl(select, {type: 'select', id: select_id, control: data.control});
                    } else {
                        const unit = keys[0];
                        if (!data.units[unit]) {
                            data.units[unit] = {};
                        }
                        if (!data.units[unit].min) {
                            data.units[unit].min = 0;
                        }
                        if (!data.units[unit].max) {
                            data.units[unit].max = 100;
                        }
                        input.min=parseFloat(data.units[unit].min);
                        input.max=parseFloat(data.units[unit].max);
                        input.step=data.units[unit].increment !== und ? parseFloat(data.units[unit].increment) : '1';
                        if (v < parseFloat(data.units[unit].min)) {
                            input.value = data.units[unit].min;
                        } else if (v > parseFloat(data.units[unit].max)) {
                            input.value = data.units[unit].max;
                        }
                        select = doc.createElement('span');
                        select.className = 'tb_unit';
                        select.id = data.id + '_unit';
                        select.dataset.min = data.units[unit].min;
                        select.dataset.max = data.units[unit].max;
                        select.dataset.increment = data.units[unit].increment !== und ? data.units[unit].increment : (unit === 'em' || unit === 'em' ? .1 : 1);
                        select.textContent = unit;
                    }
                    select_wrap.appendChild(select);
                    range_wrap.appendChild(select_wrap);
                }
                if (data.after !== und) {
                    wrapper.appendChild(self.after(data));
                }
                if (data.description !== und) {
                    wrapper.appendChild(self.description(data.description));
                }
                const event = data.event!==und?data.event:(self.clicked === 'styling' ? 'keyup' : 'change');
                if (data.opposite === true) {
                    select.tfOn('change', function (e) {
                        e.stopPropagation();
                        self.margin.changeUnit(this);
                    }, {passive: true});
                }
                this.controlChange(input, select, event);
                const ndata = api.Helper.cloneObject(data);
                if (data.opposite === true) {
                    input.tfOn(event, function (e) {
                        e.stopPropagation();
                        self.margin.changeOppositive(this);
                    }, {passive: true});
                }
                ndata.type = 'range';
                self.initControl(input, ndata);
                return wrapper;
            }
        },
        icon: {
            render(data, self) {
                const wr = doc.createElement('div'),
                        input = doc.createElement('input'),
                        preview = doc.createElement('span'),
                        clear = doc.createElement('span'),
                        v = self.getStyleVal(data.id);
                input.type = 'text';
                input.className = 'themify_field_icon';
                preview.className = 'tf_plus_icon themify_fa_toggle';
                wr.className = 'tb_icon_wrap';
                clear.className = 'tb_clear_input tf_close';
                if (self.is_repeat === true) {
                    input.className += self.is_sort === true ? ' tb_lb_sort_child' : ' tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.className += ' tb_lb_option';
                    input.id = data.id;
                }
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                if (v !== und) {
                    input.value = v;
                    preview.appendChild(api.Helper.getIcon(v));
                    self.afterRun.push(() => {
                        setTimeout(() => {
                            topWindow.Themify.fonts(v);
                        }, 100);
                    });
                } else {
                    preview.className += ' default_icon';
                }
                clear.tfOn(Themify.click,e=>{
                    e.stopPropagation();
                    input.value='';
                    Themify.triggerEvent(input, 'change');
                },{passive:true});
                wr.append(self.initControl(input, data), preview, clear);
                return wr;
            }
        },
        createMarginPadding(type, data) {
            const options = data.options !== und ? data.options : [
                {id: 'top', label: this.label.top},
                {id: 'bottom', label: this.label.bottom},
                {id: 'left', label: this.label.left},
                {id: 'right', label: this.label.right}
            ],
                    ul = doc.createElement('ul'),
                    self = this,
                    id = data.id,
                    isBorderRadius = type === 'border_radius',
                    range = api.Helper.cloneObject(data);
            range.units = data.units !== und ? data.units : {
                px: {
                    min: (type === 'margin' ? -1500 : 0),
                    max: 1500
                },
                em: {
                    min: (type === 'margin' ? -10 : 0),
                    max: 10
                },
                '%': {
                    min: (type === 'margin' ? -100 : 0)
                }
            };
            range.prop = null;
            range.opposite = true;
            ul.className = 'tb_seperate_items tb_has_opposite';
            if (isBorderRadius === true) {
                ul.dataset.toptext=options[0].label;
            }
            let len = options.length,
                    d = doc.createDocumentFragment(),
                    uncheck_all = false;
            for (let i = 0; i < len; ++i) {
                let li = doc.createElement('li'),
                        prop_id = id + '_' + options[i].id;

                range.id = prop_id;
                range.tooltip = options[i].label;
                range.class = data.class === und ? '' : data.class;
                range.class += ' tb_multi_field tb_range_' + options[i].id;
                if (isBorderRadius === true) {
                    range.class += ' tb_is_border_radius';
                }
                let rangeEl = this.range.render(range, this);
                if (i !== 0 && i !== 3) {
                    let opposite = doc.createElement('li'),
                            oppId = options[i].id === 'right' ? 'top' : options[i].id;
                    opposite.className = 'tb_seperate_opposite tb_opposite_' + (oppId === 'bottom' ? 'top' : oppId);
                    opposite.appendChild(this.checkboxGenerate('checkbox', {
                        id: id + '_opp_' + oppId,
                        class: 'style_apply_oppositive',
                        options: [
                            {name: '1', value: ''}
                        ]
                    }
                    ));
                    let ch_op = opposite.querySelector('.style_apply_oppositive'),
                            state = doc.createElement('div');
                    state.className = 'tb_oppositive_state';
                    ch_op.parentNode.insertBefore(state, ch_op.nextSibling);

                    ch_op.tfOn('change', function (e) {
                        e.stopPropagation();
                        self.margin.bindingOppositive(this, true);
                    }, {passive: true});
                    if (ch_op.checked === true) {
                        self.afterRun.push(() => {
                            self.margin.bindingOppositive(ch_op);
                        });
                    }

                    d.appendChild(opposite);
                }
                li.appendChild(rangeEl);
                d.appendChild(li);
                let prop;
                if (isBorderRadius === true) {
                    prop = 'border-';
                    if (options[i].id === 'top') {
                        prop += 'top-left-radius';
                    } else if (options[i].id === 'right') {
                        prop += 'top-right-radius';
                    } else if (options[i].id === 'left') {
                        prop += 'bottom-left-radius';
                    } else if (options[i].id === 'bottom') {
                        prop += 'bottom-right-radius';
                    }
                } else if ('transform' === data.type) {
                    prop = data.prop;
                } else {
                    prop = data.prop + '-' + options[i].id;
                    if (this.is_new === true && !uncheck_all && this.values[prop_id]) {
                        uncheck_all = true;
                    }
                }
                this.styles[prop_id] = {prop: prop, selector: data.selector};
            }
            ul.appendChild(d);
            d = doc.createDocumentFragment();
            d.appendChild(ul);
            if (len === 4) {
                d.appendChild(self.checkboxGenerate('icon_checkbox',
                        {
                            id: 'checkbox_' + id + '_apply_all',
                            class: 'style_apply_all',
                            options: [
                                {name: '1', value: self.label.all, icon: '<span class="apply_all_checkbox_icon">' + api.Helper.getIcon('ti-link').outerHTML + '</span>'}
                            ],
                            default: (this.component === 'module' && this.is_new === true && !uncheck_all) || Object.keys(this.values).length === 0 ? '1' : false
                        }
                ));
                const apply_all = d.querySelector('.style_apply_all');
                apply_all.tfOn('change', function (e) {
                    self.margin.apply_all(this, true);
                }, {passive: true});
                if (apply_all.checked === true) {
                    self.afterRun.push(()=> {
                        self.margin.apply_all(apply_all);
                    });
                }
            }
            return d;
        },
        margin_opposity: {
            update(id, v, self) {
                self.range.update(id, v, self);
                self.checkbox.update(id + '_opp_top', self.getStyleVal(id + '_opp_top'), self);
                self.range.update(self.stylesData[id].bottomId, self.getStyleVal(self.stylesData[id].bottomId), self);
            },
            render(data, self) {
                const items = ['topId', 'bottomId'],
                        ul = doc.createElement('ul'),
                        range = api.Helper.cloneObject(data);
                ul.className = 'tb_seperate_items tf_inline_b tb_has_opposite';
                for (let i = 0; i < 2; ++i) {
                    let  li = doc.createElement('li');

                    range.id = data[items[i]];
                    range.prop = items[i] === 'topId' ? 'margin-top' : 'margin-bottom';
                    range.class = 'tb_multi_field tb_range_' + (items[i] === 'topId' ? 'top' : 'bottom');
                    range.opposite = true;
                    range.tooltip = items[i] === 'topId' ? self.label.top : self.label.bottom;
                    li.appendChild(self.range.render(range, self));
                    ul.appendChild(li);
                    if (i === 0) {
                        let opposite = doc.createElement('li');
                        opposite.className = 'tb_seperate_opposite tb_opposite_top';
                        opposite.appendChild(self.checkboxGenerate('checkbox',
                                {
                                    id: range.id + '_opp_top',
                                    class: 'style_apply_oppositive',
                                    options: [
                                        {name: '1', value: ''}
                                    ]
                                }
                        ));
                        let ch_op = opposite.querySelector('.style_apply_oppositive'),
                                state = doc.createElement('div');
                        state.className = 'tb_oppositive_state';
                        ch_op.parentNode.insertBefore(state, ch_op.nextSibling);
                        ch_op.tfOn('change', function (e) {
                            e.stopPropagation();
                            self.margin.bindingOppositive(this, true);
                        }, {passive: true});

                        ul.appendChild(opposite);
                    }
                    self.stylesData[data[items[i]]] = self.styles[data[items[i]]] = {id: data[items[i]], type: data.type, prop: (items[i] === 'topId' ? 'margin-top' : 'margin-bottom'), selector: data.selector};
                }
                return ul;
            }
        },
        margin: {
            bindingOppositive(el, init) {
                const li = el.closest('.tb_seperate_opposite'),
                        p = li.parentNode,
                        isLeft = li.classList.contains('tb_opposite_left'),
                        firstItem = isLeft === true ? li.nextElementSibling : li.previousElementSibling,
                        isChecked = el.checked === true,
                        dir = this.getOppositiveDir(firstItem.tfClass('tb_range')[0]),
                        field = p.tfClass('tb_range_' + dir)[0],
                        u = field.closest('li').tfClass('tb_unit')[0];
                if (isChecked === true) {
                    field.dataset.v=field.value;
                    u.dataset.u=u.value;
                    if (init === true) {
                        const firstInput = firstItem.tfClass('tb_range')[0],
                                v = firstInput.value,
                                v2 = field.value;
                        if (v !== '' || v2 === '') {
                            field.value = v;
                            u.value = firstItem.tfClass('tb_unit')[0].value;
                        } else {
                            firstInput.value = v2;
                            firstItem.tfClass('tb_unit')[0].value = u.value;
                        }

                    }
                } else {
                    const v = field.dataset.v;
                    field.value = v === und || v === null ? '' : v;
                    u.value = u.dataset.u;
                }
                if (init === true) {
                    Themify.triggerEvent(field, 'keyup');
                }
            },
            changeUnit(el) {
                const p = el.closest('.tb_has_opposite');
                if (!p.hasAttribute('data-checked')) {
                    const input = topWindow.document.tfId(el.id.replace(/_unit$/ig, '')),
                            dir = this.getOppositiveDir(input),
                            isBorder = input.classList.contains('tb_is_border_radius'),
                            chClass = dir === 'top' || (isBorder === true && dir === 'right') || (isBorder === false && dir === 'bottom') ? 'top' : 'left';
                    if (p.tfClass('tb_opposite_' + chClass)[0].tfClass('style_apply_oppositive')[0].checked === true) {
                        p.tfClass('tb_range_' + dir)[0].closest('li').tfClass('tb_unit')[0].value = el.value;
                    }
                }
            },
            getOppositiveDir(el) {
                const cl = el.classList;
                let opp = cl.contains('tb_range_top') ? 'bottom' : (cl.contains('tb_range_bottom') ? 'top' : (cl.contains('tb_range_left') ? 'right' : 'left'));
                if (cl.contains('tb_is_border_radius')) {
                    if (opp === 'bottom') {
                        opp = 'right';
                    } else if (opp === 'top') {
                        opp = 'left';
                    } else if (opp === 'left') {
                        opp = 'top';
                    } else {
                        opp = 'bottom';
                    }
                }
                return opp;
            },
            changeOppositive(el) {
                const li = el.closest('li'),
                        p = li.parentNode;
                if (!p.hasAttribute('data-checked')) {
                    const dir = this.getOppositiveDir(el),
                            isBorder = el.classList.contains('tb_is_border_radius'),
                            ch = dir === 'top' || (isBorder === true && dir === 'right') || (isBorder === false && dir === 'bottom') ? p.tfClass('tb_opposite_top')[0] : p.tfClass('tb_opposite_left')[0];
                    if (ch.tfClass('style_apply_oppositive')[0].checked === true) {
                        p.tfClass('tb_range_' + dir)[0].value = el.value;
                    }
                }
            },
            apply_all(item, trigger) {
                const ul = item.closest('.tb_input').tfClass('tb_seperate_items')[0],
                        first = ul.tfTag('li')[0],
                        isChecked = item.checked === true;
                let text;
                if (isChecked === true) {
                    ul.dataset.checked=1;
                    text = ThemifyConstructor.label.all;

                } else {
                    ul.removeAttribute('data-checked');
                    text = ul.dataset.toptext;
                    if (!text) {
                        text = ThemifyConstructor.label.top;
                    }
                }
                if (trigger === true) {
                    Themify.triggerEvent(first.tfClass('tb_multi_field')[0], 'keyup');
                }
                first.tfClass('tb_tooltip_up')[0].textContent = text;
            },
            update(id, v, self) {
                const options = ['top', 'right', 'bottom', 'left'],
                        checkbox_id = 'checkbox_' + id + '_apply_all',
                        apply_all = self.getEl(checkbox_id).tfClass('style_apply_all')[0];
                for (let i = 3; i > -1; --i) {
                    let nid = id + '_' + options[i],
                            el = topWindow.document.tfId(nid);
                    if (el !== null) {
                        self.range.update(nid, self.getStyleVal(nid), self);
                        if (apply_all.checked !== true) {
                            let oppositiveId = id + '_opp_' + options[i],
                                    ch_oppositive = topWindow.document.tfId(oppositiveId);
                            if (ch_oppositive !== null) {
                                ch_oppositive.tfClass('style_apply_oppositive')[0].checked = self.getStyleVal(oppositiveId) ? true : false;
                            }
                        }
                    }

                }
                self.checkbox.update(checkbox_id, self.getStyleVal(checkbox_id), self);
                this.apply_all(apply_all);
            },
            render(data, self) {
                return self.createMarginPadding(data.type, data);
            }
        },
        padding: {
            render(data, self) {
                return self.createMarginPadding(data.type, data);
            }
        },
        box_shadow: {
            update(id, v, self) {
                const options = ['hOffset', 'vOffset', 'blur', 'spread'],
                        color_id = id + '_color',
                        checkbox_id = id + '_inset';
                for (let i = 3; i > -1; --i) {
                    let nid = id + '_' + options[i],
                            el = topWindow.document.tfId(nid);
                    if (el !== null) {
                        self.range.update(nid, self.getStyleVal(nid), self);
                    }
                }
                self.color.update(color_id, self.getStyleVal(color_id), self);
                self.checkbox.update(checkbox_id, self.getStyleVal(checkbox_id), self);
            },
            render(data, self) {
                const ranges = {
                    hOffset: {
                        label: self.label.h_o,
                        units: {px: {min: -200, max: 200}, em: {max: 10}}
                    },
                    vOffset: {
                        label: self.label.v_o,
                        units: {px: {min: -200, max: 200}, em: {max: 10}}
                    },
                    blur: {
                        label: self.label.bl,
                        units: {px: {max: 300}, em: {max: 10}}
                    },
                    spread: {
                        label: self.label.spr,
                        units: {px: { min: -200, max: 200 }, em: { min: -10, max: 10 }}
                    }
                },
                ul = doc.createElement('ul'),
                id = data.id,
                range = api.Helper.cloneObject(data),
                f=doc.createDocumentFragment();
                range.class = 'tb_shadow_field';
                range.prop = null;
                ul.className = 'tb_seperate_items tb_shadow_inputs';
                for (let rangeField in ranges) {
                    if (ranges[rangeField] !== und) {
                        let rField = ranges[rangeField],
                                li = doc.createElement('li'),
                                prop_id = id + '_' + rangeField;
                        range.id = prop_id;
                        range.tooltip = rField.label;
                        range.units = rField.units;
                        range.selector = data.selector;
                        li.appendChild(self.range.render(range, self));
                        ul.appendChild(li);
                        self.styles[prop_id] = {prop: data.prop, selector: data.selector};
                    }
                }
                // Add color field
                let prop_id = id + '_color';
                const li = doc.createElement('li'),
                        color = {id: prop_id, type: 'color', class: range.class, selector: data.selector};
                li.className = 'tb_shadow_color';
                self.styles[prop_id] = {prop: data.prop, selector: data.selector, type: 'color'};
                li.appendChild(self.color.render(color, self));
                ul.appendChild(li);
                // Add inset checkbox
                prop_id = id + '_inset';
                const coptions = {
                            id: prop_id,
                            origID: id,
                            type: 'checkbox',
                            class: range.class,
                            isBoxShadow: true,
                            prop: data.prop,
                            options: [
                                {value: self.label.in_sh, name: 'inset'}
                            ]
                        };
                self.styles[prop_id] = {prop: data.prop, selector: data.selector};
               
                f.append(ul,self.checkboxGenerate('checkbox', coptions));
                return f;
            }
        },
        text_shadow: {
            update(id, v, self) {
                const options = [self.label.h_sh, self.label.v_sh, self.label.bl],
                        color_id = id + '_color';
                for (let i = 2; i > -1; --i) {
                    let nid = id + '_' + options[i],
                            el = self.getEl(nid);
                    if (el !== null) {
                        self.range.update(nid, self.getStyleVal(nid), self);
                    }
                }
                self.color.update(color_id, self.getStyleVal(color_id), self);
            },
            render(data, self) {
                const ranges = {
                    hShadow: {
                        label: self.label.h_sh,
                        units: {px: {min: -200, max: 200}, em: {max: 10}}
                    },
                    vShadow: {
                        label: self.label.v_sh,
                        units: {px: {min: -200, max: 200}, em: {max: 10}}
                    },
                    blur: {
                        label: self.label.bl,
                        units: {px: {max: 300}, em: {max: 10}}
                    }
                },
                        ul = doc.createElement('ul'),
                        id = data.id,
                        range = api.Helper.cloneObject( data);
                range.class = 'tb_shadow_field';
                range.prop = null;
                ul.className = 'tb_seperate_items tb_shadow_inputs';
                for (let rangeField in ranges) {
                    if (!ranges.hasOwnProperty(rangeField))
                        continue;

                    let rField = ranges[rangeField],
                            li = doc.createElement('li'),
                            prop_id = id + '_' + rangeField;
                    range.id = prop_id;
                    range.tooltip = rField.label;
                    range.units = rField.units;
                    li.appendChild(self.range.render(range, self));
                    ul.appendChild(li);
                    self.styles[prop_id] = {prop: data.prop, selector: data.selector};
                }
                // Add color field
                const li = doc.createElement('li'),
                        prop_id = id + '_color',
                        color = {id: prop_id, type: 'color', class: range.class, selector: data.selector};
                li.className = 'tb_shadow_color';
                self.styles[prop_id] = {prop: data.prop, selector: data.selector, type: 'color'};
                li.appendChild(self.color.render(color, self));
                ul.appendChild(li);
                return ul;
            }
        },
        border_radius: {
            render(data, self) {
                data.options = self.getOptions(data);
                return self.createMarginPadding(data.type, data);
            }
        },
        outline: {
            render(data, self) {
                self.styles[ data.id + '-c' ] = data.selector;
                self.styles[ data.id + '-w' ] = data.selector;
                self.styles[ data.id + '-s' ] = data.selector;
                return self.create([
                    {
                        type: 'multi',
                        options: [
                            {
                                type: 'color',
                                id: data.id + '-c',
                                class: 'outline_color'
                            },
                            {
                                type: 'range',
                                id: data.id + '-w',
                                units: {px: {max: 300}},
                                class: 'outline_width'
                            },
                            {
                                type: 'select',
                                id: data.id + '-s',
                                options: self.static.border,
                                class: 'outline_style'
                            }
                        ]
                    }
                ]);
            }
        },
        border: {
            changeControl(item) {
                const p = item.parentNode,
                        v = item.value,
                        items = p.parentNode.children;
                for (let i = items.length - 1; i > -1; --i) {
                    if (items[i] !== p) {
                        items[i].classList.toggle('_tb_hide_binding', v === 'none');
                    }
                }
            },
            apply_all(border, item) {
                const items = item.tfTag('input'),
                        disable = function (is_all, event) {
                            for (let i = items.length - 1; i > -1; --i) {
                                items[i].parentNode.classList.toggle('_tb_disable', is_all && items[i].value !== 'all');
                            }
                            if (is_all === true) {
                                border.dataset.checked = 1;
                            } else {
                                border.removeAttribute('data-checked');
                            }
                            if (event === true) {
                                Themify.triggerEvent(border.children[0].tfTag('select')[0], 'change');
                            }
                        };
                for (let i = items.length - 1; i > -1; --i) {
                    items[i].tfOn('change', function () {
                        disable(this.value === 'all', true);
                    }, {passive: true});
                    if (items[i].checked === true && items[i].value === 'all') {
                        disable(true, null);
                    }
                }
            },
            update(id, v, self) {
                const options = ['top', 'right', 'bottom', 'left'],
                        radio_id = id + '-type';
                for (let i = 0; i < 4; ++i) {
                    let nid = id + '_' + options[i],
                            color_id = nid + '_color',
                            style_id = nid + '_style',
                            range_id = nid + '_width';
                    self.color.update(color_id, self.getStyleVal(color_id), self);
                    self.select.update(style_id, self.getStyleVal(style_id), self);
                    this.changeControl(self.getEl(style_id));
                    self.range.update(range_id, self.getStyleVal(range_id), self);
                }
                self.radio.update(radio_id, self.getStyleVal(radio_id), self);
            },
            render(data, self) {
                const options = ['top', 'right', 'bottom', 'left'],
                        ul = doc.createElement('ul'),
                        orig_id = data.id,
                        _this = this,
                        select = api.Helper.cloneObject( data),
                        radio = api.Helper.cloneObject( data);
                radio.options = [
                    {value: 'all', name: self.label.all, class: 'style_apply_all ', icon: '<i class="tic-border-all"></i>', label_class: 'tb_radio_label_borders'}
                ];
                radio.option_js = true;
                radio.id = orig_id + '-type';
                radio.no_toggle = true;
                radio.default = 'top';
                radio.prop = null;

                select.options = self.static.border;
                select.prop = null;

                ul.className = 'tb_seperate_items tb_borders tb_group_element_border';
                for (let  i = 0; i < 4; ++i) {
                    let li = doc.createElement('li'),
                            id = orig_id + '_' + options[i];
                    radio.options.push({value: options[i], name: self.label[options[i]], icon: '<i class="tic-border-' + options[i] + '"></i>', label_class: 'tb_radio_label_borders'});

                    li.className = 'tb_group_element_' + options[i];
                    if (options[i] === 'top') {
                        li.className += ' tb_group_element_all';
                    }
                    self.styles[id + '_color'] = {prop: 'border-' + options[i], selector: data.selector};
                    select.id = id + '_color';
                    select.type = 'color';
                    select.class = 'border_color';
                    li.appendChild(self.color.render(select, self));

                    self.styles[id + '_width'] = {prop: 'border-' + options[i], selector: data.selector};
                    select.id = id + '_width';
                    select.type = 'range';
                    select.class = 'border_width';
                    select.units = {px: {max: 300}};
                    li.appendChild(self.range.render(select, self));

                    self.styles[id + '_style'] = {prop: 'border-' + options[i], selector: data.selector};
                    select.id = id + '_style';
                    select.type = 'select';
                    select.class = 'border_style tb_multi_field';
                    let border_select = self.select.render(select, self),
                            select_item = border_select.querySelector('select');
                    li.appendChild(border_select);
                    ul.appendChild(li);
                    select_item.tfOn('change', function (e) {
                        _this.changeControl(this);
                    }, {passive: true});
                    if (select_item.value === 'none') {
                        _this.changeControl(select_item);
                    }
                }
                const d = doc.createDocumentFragment();
                d.appendChild(self.radioGenerate('icon_radio', radio, self));
                _this.apply_all(ul, d.querySelector('#' + radio.id));
                d.appendChild(ul);
                return d;
            }
        },
        slider: {
            render(data, self) {
                const label = data.label;
                delete data.label;
                // Backward compatibility #9463
                if (['crossfade', 'cover-fade', 'uncover-fade'].includes(self.values.effect_slider)) {
                    self.values.effect_slider = 'fade';
                }
                return self.create([{
                        type: 'group',
                        label: label,
                        display: 'accordion',
                        options: self.getOptions(data),
                        wrap_class: data.wrap_class
                    }]);
            }
        },
        custom_css: {
            render(data, self) {
                data.class = 'large';
                data.control = false;
                data.help = self.label.custom_css_help;
                const el = self.text.render(data, self);
                api.activeModel.options(el.querySelector('#' + data.id), data.type);
                return el;
            }
        },
        custom_css_id: {
            render(data, self) {
                let el,
                    inputArgs={
                        id: 'custom_css_id',
                        required:{rule:'custom_css_id',message:self.label.errorId},
                        type: 'text',
                        label: self.label.id_name,
                        help: self.label.id_help,
                        control: false,
                        class: 'large'
                    };
                if (data.accordion!==false) {
                    const options = [];
                    if (data.custom_css) {
                        options.push({
                            id: data.custom_css,
                            type: 'custom_css'
                        });
                    }
                    options.push(inputArgs);
                    el = self.create([{
                            type: 'group',
                            label: self.label.cc,
                            display: 'accordion',
                            options: options,
                            wrap_class: 'tb_field_group_css'
                    }], self);
                }
                else{
                    el=self.create([inputArgs],self);
                }
                if(self.component === 'row' ){
                    api.activeModel.options(el.querySelector('#custom_css_id'), data.type);
                }
                return el;
            }
        },
        hidden: {
            render(data, self) {
                const input = doc.createElement('input'),
                        v = self.getStyleVal(data.id);
                let value=v!==und?v:data.value;
                if(value!==und ){
                    if (typeof value === 'object') {
                        try{
                            const tmp=JSON.stringify(value);  
                            value=tmp;
                        }
                        catch(e){
                        }
                    }
                }
                else{
                    value='';
                }
                input.type = 'hidden';
                if (self.is_repeat === true) {
                    input.className = self.is_sort === true ? 'tb_lb_sort_child' : 'tb_lb_option_child';
                    input.dataset.inputId = data.id;
                } else {
                    input.className = 'tb_lb_option';
                    input.id = data.id;
                }
                if (data.class !== und) {
                    input.className += ' ' + data.class;
                }
                input.value = value;
                return self.initControl(input, data);
            }
        },
        frame: {
            render(data, self) {
                data.options = self.static.frame;
                data.class = 'tb_frame tf_scrollbar';
                data.binding = {
                    not_empty: {show: ['tb_frame_multi_wrap', 'tb_frame_color']},
                    empty: {hide: ['tb_frame_multi_wrap', 'tb_frame_color']}
                };
                return self.layout.render(data, self);
            }
        },
        title: {
            render(data, self) {
                data.control = {event: 'keyup', control_type: 'change', selector: '.module-title'};
                return self.text.render(data, self);
            }
        },
        url: {
            render(data, self) {
                data.input_type = 'url';
                return self.text.render(data, self);
            }
        },
        advacned_link: {
            render(data, self) {
                const opt = [
                    {
                        id: 'link',
                        type: 'radio',
                        label: 'l',
                        wrap_class: ' tb_compact_radios',
                        link_to: true,
                        binding: {
                            permalink: {show: 'open_link', hide: 'custom_link'},
                            custom: {show: 'custom_link', hide: 'open_link'},
                            none: {hide: ['custom_link', 'open_link', 'no_follow']}
                        }
                    },
                    {
                        id: 'custom_link',
                        type: 'url',
                        label: 'cl'
                    },
                    {
                        id: 'open_link',
                        type: 'radio',
                        label: 'o_l',
                        link_type: true,
                        control: false,
                        wrap_class: ' tb_compact_radios',
                        binding: {
                            lightbox: {show: 'tb_t_m_lightbox'},
                            regular: {hide: 'tb_t_m_lightbox'},
                            newtab: {hide: 'tb_t_m_lightbox'}
                        }
                    },
                    {
                        type: 'multi',
                        wrap_class: 'tb_t_m_lightbox',
                        label: 'lg',
                        options: [
                            {
                                id: 'lightbox_w',
                                type: 'range',
                                label: 'w',
                                control: false,
                                units: {
                                    px: {
                                        max: 1000
                                    },
                                    '%': ''
                                }

                            },
                            {
                                id: 'lightbox_h',
                                type: 'range',
                                label: 'ht',
                                control: false,
                                units: {
                                    px: {
                                        max: 1000
                                    },
                                    '%': ''
                                }
                            }
                        ]
                    }
                ];
                return self.create(opt);
            }
        },
        button: {
            render(data, self) {
                const btn = doc.createElement('button');
                btn.className = 'builder_button';
                btn.id = data.id;
                if (data.class !== und) {
                    btn.className += ' ' + data.class;
                }
                btn.textContent = data.name;
                return self.initControl(btn, data);
            }
        },
        row_anchor: {
            render(data, self) {
                data.control = false;
                const el = self.text.render(data, self);
                api.activeModel.options(el.querySelector('#' + data.id), data.type);
                return el;
            }
        },
        widget_form: {
            render(data, self) {
                const container = doc.createElement('div');
                container.id = data.id;
                container.className = 'module-widget-form-container wp-core-ui tb_lb_option';
                return container;
            }
        },
        widget_select: {
            data: null,
            el: null,
            cache:new Map(),
            mediaInit: null,
            textInit: null,
            render(data, self) {
                const d = doc.createDocumentFragment(),
                        filter = doc.createElement('div'),
                        loader = doc.createElement('i'),
                        search = doc.createElement('input'),
                        available = doc.createElement('div'),
                        select = doc.createElement('div');

                filter.id = 'available-widgets-filter';
                filter.className = 'selectwrapper tf_inline_b tf_vmiddle tf_rel';
                loader.className = 'tb_loading_widgets tf_loader';
                search.type = 'text';
                search.id = 'widgets-search';
                search.dataset.validation = 'not_empty';
                search.dataset.errorMsg = self.label.widget_validate;
                search.autocomplete = 'off';
                search.placeholder = self.label.search_widget;

                available.id = 'available-widgets';
                available.className = 'tf_scrollbar';
                available.tabIndex = 1;

                select.id = data.id;
                select.className = 'tb_lb_option tb_widget_select';

                this.el = select;
                filter.append(loader, search);
                available.appendChild(select);
                d.append(filter, available);

                const val = self.values[data.id],
                    callback = () => {
                        const all_items = [],
                            select_widget = (item, instance_widget) => {
                                for (let i = all_items.length - 1; i > -1; --i) {
                                    all_items[i].classList.remove('selected');
                                }
                                item.classList.add('selected');
                                const v = item.dataset.value;
                                search.value = item.tfClass('widget-title')[0].textContent;
                                available.style.display = 'none';

                                this.select(v, this.data[v].b, instance_widget, data);
                            };
                        for (let i in this.data) {
                            let w = doc.createElement('div'),
                                    title = doc.createElement('div'),
                                    h3 = doc.createElement('h3');
                            w.className = 'widget-tpl ' + this.data[i].b;
                            w.dataset.value = i;
                            title.className = 'widget-title';
                            h3.textContent = this.data[i].n;
                            title.appendChild(h3);
                            w.appendChild(title);
                            w.tfOn(Themify.click, function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                self.settings[data.id] = this.dataset.value;
                                select_widget(this, null);
                            });
                            all_items.push(w);
                            if (this.data[i].d !== und) {
                                let desc = doc.createElement('div');
                                desc.className = 'widget-description';
                                desc.innerHTML = this.data[i].d;
                                w.appendChild(desc);
                            }
                            select.appendChild(w);
                            if (val === i) {
                                select_widget(w, self.values.instance_widget);
                            }
                        }
                        this.search(search, available);
                        loader.parentNode.removeChild(loader);
                };
                if (this.data === null) {
                    api.LocalFetch({action: 'tb_get_widget_items'}).then(data=>{
                        this.data = data;
                        callback();
                    })
                    .catch(e=>{
                        api.Spinner.showLoader('error');
                    });

                    for (let i in themifyBuilder.widget_css) {
                        topWindow.Themify.loadCss(themifyBuilder.widget_css[i],null, themify_vars.version);
                    }
                    themifyBuilder.widget_css = null;

                } else {
                    setTimeout(callback, 5);
                }
                return d;
            },
            search(search, available) {
                const _this = this;
                search.tfOn('focus', this.show.bind(this), {passive: true})
                .tfOn('blur', e=> {
                    if (!e.relatedTarget || e.relatedTarget.id !== 'available-widgets') {
                        available.style.display = 'none';
                    }
                }, {passive: true})
                .tfOn('keyup', function (e) {
                    _this.show();
                    const val = this.value.trim(),
                            r = new RegExp(val, 'i'),
                            items = _this.el.tfClass('widget-tpl');
                    for (let i = 0, len = items.length; i < len; ++i) {
                        if (val === '') {
                            items[i].style.display = 'block';
                        } else {
                            let title = items[i].tfTag('h3')[0];
                            title = title.textContent || title.innerText;
                            items[i].style.display = r.test(title)?'':'none';
                        }
                    }

                }, {passive: true});
            },
            show() {
                //this.$el.next('.tb_field_error_msg').remove();
                this.el.closest('#available-widgets').style.display = 'block';
            },
            hide() {
                this.el.closest('#available-widgets').style.display = 'none';
            },
            select(val, base, settings_instance, args) {
                const instance = $('#instance_widget', api.LightBox.el),
                    callback = data => {
                        const initjJS = () => {
                            const form = $(data.form);
                            instance.addClass('open').html(form.html());
                            if (settings_instance) {
                                for (let i in settings_instance) {
                                    instance.find('[name="' + i + '"]').val(settings_instance[i]);
                                }
                            }
                            if (base === 'text') {
                                if (topWindow.wp.textWidgets) {
                                    if (!this.textInit) {
                                        if(api.mode==='visual'){
                                            topWindow.wp.textWidgets.init();
                                        }
                                        this.textInit = true;
                                    }
                                    if (settings_instance) {
                                        delete topWindow.wp.textWidgets.widgetControls[settings_instance['widget-id']];
                                    }
                                }

                            } else if (topWindow.wp.mediaWidgets) {
                                if (!this.mediaInit) {
                                    topWindow.wp.mediaWidgets.init();
                                    this.mediaInit = true;
                                }
                                if (settings_instance) {
                                    delete topWindow.wp.mediaWidgets.widgetControls[settings_instance['widget-id']];
                                }
                            }
                            
                            $(doc).trigger('widget-added', [instance]);
                            base === 'text' && ThemifyConstructor.initControl(instance.find('.wp-editor-area')[0], {control: {control_type: 'wp_editor', type: 'refresh'}});
                            if (api.mode === 'visual') {
                                const settings = api.Helper.cloneObject( args);
                                settings.id = instance[0].id;
                                instance.on('change',function(){
                                    if (api.is_ajax_call === null) {
                                            ThemifyConstructor.control.widget_select(this, settings);
                                       
                                    }
                                });
                                if (val) {
                                    ThemifyConstructor.control.widget_select(instance[0], settings);
                                }
                            }
                            instance.removeClass('tb_loading_widgets_form').find('select').wrap('<span class="selectwrapper tf_inline_b tf_vmiddle tf_rel"/>');
                        },
                        extra = data => {
                            let str = '';
                            if (typeof data === 'object') {
                                for (let i in data) {
                                    if (data[i]) {
                                        str += data[i];
                                    }
                                }
                            }
                            if (str !== '') {
                                const s = doc.createElement('script'),
                                        t = doc.tfTag('script')[0];
                                s.text = str;
                                t.before(s);
                            }
                        },
                        recurisveLoader = (js, i) => {
                            const len = js.length;
                            Themify.loadJs(js[i].src, null, data.v).then(() => {
                                if (js[i].extra && js[i].extra.after) {
                                    extra(js[i].extra.after);
                                }
                                ++i;
                                i < len ? recurisveLoader(js, i) : initjJS();
                            });
                        };

                        if (!this.cache.has(base)) {
                            if(data.template){
                                topWindow.document.body.insertAdjacentHTML('beforeend', data.template);
                                if(api.mode==='visual'){
                                    doc.body.insertAdjacentHTML('beforeend', data.template);
                                }
                            }
                            data.src.length > 0 ? recurisveLoader(data.src, 0) : initjJS();
                        } else {
                            initjJS();
                        }
                };
                instance.addClass('tb_loading_widgets_form').html('<div class="tf_loader"></div>');

                // backward compatibility with how Widget module used to save data
                if (settings_instance) {
                    for(let i in settings_instance){
                        let old_pattern = i.match(/.*\[\d\]\[(.*)\]/);
                        if (Array.isArray(old_pattern) && old_pattern[1] !== und) {
                            delete settings_instance[ i ];
                            settings_instance[ old_pattern[1] ] = v;
                        }
                    }
                }
                const ajaxData={
                        action: 'module_widget_get_form',
                        load_class: val,
                        tpl_loaded: this.cache.has(base)? 1 : 0,
                        id_base: base,
                        widget_instance: settings_instance
                    };
                    api.LocalFetch(ajaxData).then(data=>{
                        if (data && data.form) {
                            callback(data);
                            this.cache.set(base,1);
                        }
                    })
                    .catch(e=>{
                        api.Spinner.showLoader('error');
                    });
            }
        },
        message: {
            render(data, self) {
                const d = doc.createElement('div');
                if (data.class !== und) {
                    d.className += data.class;
                }
                d.innerHTML = data.comment;
                return d;
            }
        },
        filters: {
            render(data, self) {
                const ranges = {
                    hue: {
                        label: self.label.hue,
                        units: {deg: {max: 360}},
                        prop: 'hue-rotate'
                    },
                    saturation: {
                        label: self.label.sat,
                        units: {'%': {max: 200}},
                        prop: 'saturate'
                    },
                    brightness: {
                        label: self.label.bri,
                        units: {'%': {max: 200}},
                        prop: 'brightness'
                    },
                    contrast: {
                        label: self.label.con,
                        units: {'%': {max: 200}},
                        prop: 'contrast'
                    },
                    invert: {
                        label: self.label.inv,
                        units: {'%': ''},
                        prop: 'invert'
                    },
                    sepia: {
                        label: self.label.se,
                        units: {'%': ''},
                        prop: 'sepia'
                    },
                    opacity: {
                        label: self.label.op,
                        units: {'%': ''},
                        prop: 'opacity'
                    },
                    blur: {
                        label: self.label.bl,
                        units: {px: {max: 50}},
                        prop: 'blur'
                    }
                },
                        ul = doc.createElement('ul'),
                        id = data.id,
                        range = api.Helper.cloneObject( data);
                range.class = 'tb_filters_field';
                range.prop = null;
                ul.className = 'tb_seperate_items tb_filters_fields';
                for (let rangeField in ranges) {
                    if (ranges[rangeField] !== und) {
                        let rField = ranges[rangeField],
                                li = doc.createElement('li'),
                                label = doc.createElement('div'),
                                prop_id = id + '_' + rangeField;
                        range.id = prop_id;
                        range.units = rField.units;
                        range.selector = data.selector;
                        label.className = 'tb_label';
                        label.textContent = rField.label;
                        li.appendChild(label);
                        if ('hue' === rangeField) {
                            range.deg = true;
                            li.appendChild(self.angle.render(range, self));
                            delete range.deg;
                        } else {
                            li.appendChild(self.range.render(range, self));
                        }
                        ul.appendChild(li);
                        self.styles[prop_id] = {prop: rField.prop, selector: data.selector};
                    }
                }
                return ul;
            }
        },
        help(text) {
            const help = doc.createElement('div'),
                    helpContent = doc.createElement('div'),
                    icon = doc.createElement('i');
            help.className = 'tb_help tf_rel';
            helpContent.className = 'tb_help_content tf_hide tf_box';
            icon.tabIndex = -1;
            icon.className = 'icon';
            helpContent.innerHTML = text;
            icon.appendChild(api.Helper.getIcon('ti-help'));
            help.append(icon, helpContent);
            return help;
        },
        hint(text) {
            const tooltip = doc.createElement('span');
            tooltip.className = 'tb_tooltip_up';
            tooltip.textContent = text;
            return tooltip;
        },
        description(text) {
            const d = doc.createElement('small');
            d.innerHTML = text;
            return d;
        },
        after(data) {
            const afterElem = doc.createElement('span');
            afterElem.className = 'tb_input_after';
            afterElem.textContent = data.after;
            if ((data.label === und || data.label === '')
                    && (data.help !== und && data.help !== '')) {
                afterElem.appendChild(this.help(data.help));
            }
            return afterElem;
        },
        height: {
            update(id, v, self) {
                self.checkbox.update(id, self.getStyleVal(id), self);
            },
            render(data, self) {
                data.isHeight = true;
                const d = doc.createDocumentFragment(),
                        heightData = api.Helper.cloneObject( data);
                delete heightData.label;
                heightData.type = 'range';
                heightData.id = data.id;
                heightData.prop = 'height';
                heightData.wrap_class = 'tb_group_element_' + data.id + '_height tf_inline_b';
                heightData.units = {
                    px: {
                        min:0,
                        max: 1200
                    },
                    vh: '',
                    '%': '',
                    em: {
                        min:0,
                        max: 200
                    }
                };
                self.styles[data.id] = {prop: 'height', selector: data.selector};
                self.styles[data.id + '_auto_height'] = {prop: 'height', selector: data.selector};
                d.append(
                    self.create([heightData]),
                    self.checkboxGenerate('checkbox', {
                        id: data.id + '_auto_height',
                        heightID: data.id,
                        type: 'checkbox',
                        isHeight: true,
                        prop: 'height',
                        binding: {
                            checked: {hide: 'tb_group_element_ht_height'},
                            not_checked: {show: 'tb_group_element_ht_height'}
                        },
                        options: [
                            {value: self.label.a_ht, name: 'auto'}
                        ]
                }));
                return d;
            }
        },
        toggle_switch: {
            update(id, v, self) {
                self.checkbox.update(id, self.getStyleVal(id), self);
            },
            controlChange(el, args) {
                el.tfOn('change', function () {
                    this.value = this.checked === true ? args.on.name : (args.off !== und ? (args.off.name || ''):'');
                    if ('visibility' === ThemifyConstructor.clicked && null !== api.activeModel) {
                        api.activeModel.visibilityLabel();
                    }
                }, {passive: true});
            },
            render(data, self) {
                const clone = api.Helper.cloneObject( data),
                        orig = {},
                        label = doc.createElement('div');
                let state = 'off',
                        v = self.getStyleVal(data.id);
                clone.control = false;
                if (clone.class === und) {
                    clone.class = 'toggle_switch';
                } else {
                    clone.class += ' toggle_switch';
                }
                let options = clone.options;
                if (options === und || options === 'simple') {
                    if (options === 'simple') {
                        options = {
                            on: {
                                name: 'yes',
                                value: self.label.y
                            },
                            off: {
                                name: 'no',
                                value: self.label.no
                            }
                        };
                    } else {
                        options = {
                            on: {
                                name: 'no',
                                value: self.label.s
                            },
                            off: {
                                name: 'yes',
                                value: self.label.hi
                            }
                        };
                        if (clone.default === und) {
                            clone.default = 'on';
                        }
                    }
                }  
                if (v === und) {
                    if (clone.default === 'on') {
                        state = 'on';
                    }
                    v = state === 'on' ? options.on.name : (options.off !== und?(options.off.name || ''):'');
                } else {
                    if (v === false) {
                        v = '';
                    }
                    state = options.on.name === v ? 'on' : 'off';
                }
                
                for (let i in options) {
                    if (clone.after === und && options[i].value !== und) {
                        label.dataset[i]=self.label[options[i].value] !== und ? self.label[options[i].value] : options[i].value;
                    }
                    orig[i] = options[i];
                }
                const k = Object.keys(options)[0];
                delete clone.binding;
                delete options[k].value;
                delete clone.default;
                clone.options = [options[k]];
                if (clone.wrap_checkbox === und) {
                    clone.wrap_checkbox = '';
                }
                clone.wrap_checkbox += ' tb_switcher';
                label.className = 'switch_label';
                const checkBox = self.checkboxGenerate('checkbox', clone),
                        sw = checkBox.querySelector('.toggle_switch');
                sw.value = v;
                sw.checked = state === 'on';
                this.controlChange(sw, orig);
                sw.parentNode.appendChild(label);
                self.initControl(sw, data);
                return checkBox;
            }
        },
        width: {
            update(id, v, self) {
                self.checkbox.update(id, self.getStyleVal(id), self);
            },
            render(data, self) {
                data.isWidth = true;
                const coptions = {
                    id: data.id + '_auto_width',
                    widthID: data.id,
                    type: 'checkbox',
                    isWidth: true,
                    prop: 'width',
                    options: [
                        {value: self.label.a_wd, name: 'auto'}
                    ]
                };
                self.styles[data.id + '_auto_width'] = {prop: 'width', selector: data.selector};
                const checkboxWrap = self.checkboxGenerate('checkbox', coptions),
                        checkbox = checkboxWrap.querySelector('.tb_lb_option'),
                        checkboxInput = checkboxWrap.querySelector('input'),
                        widthData = api.Helper.cloneObject( data);
                checkboxInput.tfOn('change', function (e) {
                    const widthField = topWindow.document.tfClass('tb_group_element_' + data.id + '_width')[0];
                    if (widthField) {
                        widthField.classList.toggle('hide-auto-height', e.target.checked);
                    }
                }, {passive: true});
                widthData.label = 'w';
                widthData.type = 'range';
                widthData.id = data.id;
                widthData.prop = 'width';
                widthData.wrap_class = 'tb_group_element_' + data.id + '_width';
                if ('auto' === self.values[data.id + '_auto_width']) {
                    widthData.wrap_class += ' hide-auto-height';
                }
                widthData.units = {
                    px: {
                        min:0,
                        max: 2000
                    },
                    '%': '',
                    em: {
                        min:0,
                        max: 20
                    }
                };

                const width = self.create([widthData]);
                //min width
                widthData.wrap_class = '';
                widthData.label = 'mi_wd';
                widthData.id = 'min_' + data.id;
                widthData.prop = 'min-width';
                const minWidth = self.create([widthData]);
                self.styles[widthData.id] = {prop: widthData.prop, selector: data.selector};
                //max width
                widthData.label = 'ma_wd';
                widthData.id = 'max_' + data.id;
                widthData.prop = 'max-width';
                const maxWidth = self.create([widthData]);
                self.styles[widthData.id] = {prop: widthData.prop, selector: data.selector};
                self.afterRun.push(() => {
                    const field = checkbox.parentNode.closest('.tb_field');
                    field.parentNode.insertBefore(width, field);
                    field.parentNode.insertBefore(maxWidth, field.nextSibling);
                    field.parentNode.insertBefore(minWidth, field.nextSibling);
                });
                return checkboxWrap;
            }
        },
        position: {
            render(data, self) {
                const options = ['top','right','bottom', 'left'],
                        ul = doc.createElement('ul'),
                        li = doc.createElement('li'),
                        f = doc.createDocumentFragment(),
                        d = doc.createDocumentFragment(),
                        prop=data.prop,
                        orig_id = data.id,
                        select = api.Helper.cloneObject(data),
                        radio = api.Helper.cloneObject(data);
                radio.options = [];
                radio.option_js = true;
                radio.id = orig_id + '-type';
                radio.no_toggle = true;
                radio.default = 'top';
                radio.prop = null;
                ul.className = 'tb_seperate_items tb_group_element_position';
                for (let i=0;i<4;++i) {
                    let child = doc.createElement('li'),
                        k=options[i],
                            id = orig_id + '_' + k;
                    radio.options.push({value: k, name: self.label[options[i]], icon: '<i class="tic-border-' + i + '"></i>', label_class: 'tb_radio_label_borders'});
                    child.className = 'tb_group_element_' + k;
                    self.styles[id] = {prop: k, selector: data.selector};
                    select.id = id;
                    select.type = 'range';
                    select.prop = prop;
                    select.wrap_class = 'range_wrapper tf_inline_b ' + id;
                    select.units = {px: {min: -2000, max: 2000}, '%': ''};
                    child.appendChild(self.range.render(select, self));
                    self.styles[id + '_auto'] = {prop: prop, selector: data.selector};
                    child.appendChild(self.checkboxGenerate('checkbox',
                            {
                                id: id + '_auto',
                                is_position: true,
                                posId: id,
                                prop: k,
                                type: 'checkbox',
                                selector: data.selector,
                                options: [
                                    {name: 'auto', value: self.label.auto}
                                ],
                                wrap_checkbox: 'tf_inline_b',
                                binding: {
                                    checked: {hide: id},
                                    not_checked: {show: id}
                                }
                            }
                    ));
                    f.appendChild(child);
                }
                li.appendChild(self.radioGenerate('icon_radio', radio, self));
                ul.insertBefore(li, ul.childNodes[0]);
                ul.appendChild(f);
                self.styles[orig_id] = {prop: prop, selector: data.selector};
                select.id = orig_id;
                select.binding = {
                    empty: {hide: 'tb_group_element_position'},
                    relative: {hide: 'tb_group_element_position'},
                    static: {hide: 'tb_group_element_position'}
                };
                select.type = 'select';
                select.prop = prop;
                select.options = {'': '', relative: self.label.re, static: self.label.st};
                if(self.component!=='column'){
                    select.binding.absolute={show: 'tb_group_element_position'};
                    select.binding.fixed={show: 'tb_group_element_position'};
                    select.options.absolute=self.label.abs;
                    select.options.fixed=self.label.fi;
                }
                select.class = 'tb_position_field tb_multi_field';
                d.append(self.select.render(select, self), ul);
                return d;
            }
        },
        transform: {
            _label(l) {
                const label = doc.createElement('div');
                label.className = 'tb_label';
                label.textContent = l;
                return label;
            },
            _xy(prop, unit, data, self, label) {
                const li = doc.createElement('li'),
                        args = api.Helper.cloneObject(data);
                li.appendChild(this._label(label));
                args.prop = prop;
                args.orig_id = data.id;
                args.options = [
                    {id: 'top', label: 'X'},
                    {id: 'bottom', label: 'Y'}
                ];
                args.units = unit;
                args.id = data.id + '_' + prop;
                li.appendChild(self.createMarginPadding(data.type, args));
                return li;
            },
            _rotate(data, self) {
                const inputs = ['x', 'y', 'z'],
                        ul = doc.createElement('ul'),
                        li = doc.createElement('li');
                ul.className = 'tb_seperate_items tb_tr_rotate';
                li.appendChild(this._label(self.label.ro));
                data.orig_id = data.id;
                const args = api.Helper.cloneObject(data);
                for (let input in inputs) {
                    if (inputs.hasOwnProperty(input)) {
                        let li_r = doc.createElement('li');
                        args.id = data.id + '_rotate_' + inputs[input];
                        args.prop = 'rotate';
                        args.deg = true;
                        args.tooltip = inputs[input];
                        li_r.appendChild(self.angle.render(args, self));
                        ul.appendChild(li_r);
                    }
                }
                li.appendChild(ul);
                return li;
            },
            render(data, self) {
                const ul = doc.createElement('ul');
                ul.className = 'tb_seperate_items tb_transform_fields';
                // Scale
                ul.appendChild(this._xy('scale', {x: {min: -100, max: 1000, increment:.1}}, data, self, self.label.sc));
                // Translate
                ul.appendChild(this._xy('translate', {px: {min: -2000, max: 2000}, '%': {min: -100, max: 100}, em: {min: 0, max: 100}}, data, self, self.label.tl));
                // Rotate
                ul.appendChild(this._rotate(data, self));
                // Skew
                ul.appendChild(this._xy('skew', {deg: {min: -180, max: 180}}, data, self, self.label.sk));
                return ul;
            }
        },
        code:{
            render(data,self){
                const lngOpt={
                        markup:"Markup(markup, html, xml, svg, mathml, ssml, atom, rss)",
                        css:"CSS",
                        javascript:"JavaScript",
                        abap:"ABAP",
                        abnf:"ABNF",
                        actionscript:"ActionScript",
                        ada:"Ada",
                        agda:"Agda",
                        al:"AL",
                        antlr4:"ANTLR4",
                        apacheconf:"Apache Configuration",
                        apex:"Apex",
                        apl:"APL",
                        applescript:"AppleScript",
                        aql:"AQL",
                        arduino:"Arduino",
                        arff:"ARFF",
                        armasm:"ARM Assembly",
                        arturo:"Arturo",
                        asciidoc:"AsciiDoc",
                        aspnet:"ASP.NET (C#)",
                        asm6502:"6502 Assembly",
                        asmatmel:"Atmel AVR Assembly",
                        autohotkey:"AutoHotkey",
                        autoit:"AutoIt",
                        avisynth:"AviSynth",
                        'avro-idl':"Avro IDL",
                        awk:"AWK",
                        bash:"Bash",
                        basic:"BASIC",
                        batch:"Batch",
                        bbcode:"BBcode",
                        bbj:"BBj",
                        bicep:"Bicep",
                        birb:"Birb",
                        bison:"Bison",
                        bnf:"BNF",
                        bqn:"BQN",
                        brainfuck:"Brainfuck",
                        brightscript:"BrightScript",
                        bro:"Bro",
                        bsl:"BSL (1C:Enterprise)",
                        c:"C",
                        csharp:"C#",
                        cpp:"C++",
                        cfscript:"CFScript",
                        chaiscript:"ChaiScript",
                        cil:"CIL",
                        cilkc:"Cilk/C",
                        cilkcpp:"Cilk/C++",
                        clojure:"Clojure",
                        cmake:"CMake",
                        cobol:"COBOL",
                        coffeescript:"CoffeeScript",
                        concurnas:"Concurnas",
                        csp:"Content-Security-Policy",
                        cooklang:"Cooklang",
                        coq:"Coq",
                        crystal:"Crystal",
                        csv:"CSV",
                        cue:"CUE",
                        cypher:"Cypher",
                        d:"D",
                        dart:"Dart",
                        dataweave:"DataWeave",
                        dax:"DAX",
                        dhall:"Dhall",
                        diff:"Diff",
                        django:"Django/Jinja2",
                        'dns-zone-file':"DNS zone file",
                        docker:"Docker",
                        dot:"DOT (Graphviz)",
                        ebnf:"EBNF",
                        editorconfig:"EditorConfig",
                        eiffel:"Eiffel",
                        ejs:"EJS",
                        elixir:"Elixir",
                        elm:"Elm",
                        etlua:"Embedded Lua templating",
                        erb:"ERB",
                        erlang:"Erlang",
                        'excel-formula':"Excel Formula",
                        fsharp:"F#",
                        factor:"Factor",
                        flow:"Flow",
                        fortran:"Fortran",
                        ftl:"FreeMarker Template Language",
                        gml:"GameMaker Language",
                        gap:"GAP (CAS)",
                        gcode:"G-code",
                        gdscript:"GDScript",
                        gedcom:"GEDCOM",
                        gettext:"gettext",
                        gherkin:"Gherkin",
                        git:"Git",
                        glsl:"GLSL",
                        gn:"GN",
                        'linker-script':"GNU Linker Script",
                        go:"Go",
                        'go-module':"Go module",
                        gradle:"Gradle",
                        graphql:"GraphQL",
                        groovy:"Groovy",
                        haml:"Haml",
                        handlebars:"Handlebars",
                        haskell:"Haskell",
                        haxe:"Haxe",
                        hcl:"HCL",
                        hlsl:"HLSL",
                        hoon:"Hoon",
                        http:"HTTP",
                        hpkp:"HTTP Public-Key-Pins",
                        hsts:"HTTP Strict-Transport-Security",
                        ichigojam:"IchigoJam",
                        icon:"Icon",
                        'icu-message-format':"ICU Message Format",
                        idris:"Idris",
                        ignore:".ignore",
                        inform7:"Inform 7",
                        ini:"Ini",
                        io:"Io",
                        j:"J",
                        java:"Java",
                        javadoclike:"JavaDoc-like",
                        javastacktrace:"Java stack trace",
                        jexl:"Jexl",
                        jolie:"Jolie",
                        jq:"JQ",
                        jsdoc:"JSDoc",
                        json:"JSON",
                        json5:"JSON5",
                        jsonp:"JSONP",
                        jsstacktrace:"JS stack trace",
                        julia:"Julia",
                        keepalived:"Keepalived Configure",
                        keyman:"Keyman",
                        kotlin:"Kotlin",
                        kumir:"KuMir (КуМир)",
                        kusto:"Kusto",
                        latex:"LaTeX",
                        latte:"Latte",
                        less:"Less",
                        lilypond:"LilyPond",
                        liquid:"Liquid",
                        lisp:"Lisp",
                        livescript:"LiveScript",
                        llvm:"LLVM IR",
                        log:"Log file",
                        lolcode:"LOLCODE",
                        lua:"Lua",
                        magma:"Magma (CAS)",
                        makefile:"Makefile",
                        markdown:"Markdown",
                        mata:"Mata",
                        matlab:"MATLAB",
                        maxscript:"MAXScript",
                        mel:"MEL",
                        mermaid:"Mermaid",
                        metafont:"METAFONT",
                        mizar:"Mizar",
                        mongodb:"MongoDB",
                        monkey:"Monkey",
                        moonscript:"MoonScript",
                        n1ql:"N1QL",
                        n4js:"N4JS",
                        'nand2tetris-hdl':"Nand To Tetris HDL",
                        naniscript:"Naninovel Script",
                        nasm:"NASM",
                        neon:"NEON",
                        nevod:"Nevod",
                        nginx:"Nginx",
                        nim:"Nim",
                        nix:"Nix",
                        nsis:"NSIS",
                        objectivec:"Objective-C",
                        ocaml:"OCaml",
                        odin:"Odin",
                        opencl:"OpenCL",
                        openqasm:"OpenQasm",
                        oz:"Oz",
                        parigp:"PARI/GP",
                        parser:"Parser",
                        pascal:"Pascal",
                        pascaligo:"Pascaligo",
                        psl:"PATROL Scripting Language",
                        pcaxis:"PC-Axis",
                        peoplecode:"PeopleCode",
                        perl:"Perl",
                        php:"PHP",
                        phpdoc:"PHPDoc",
                        'plant-uml':"PlantUML",
                        plsql:"PL/SQL",
                        powerquery:"PowerQuery",
                        powershell:"PowerShell",
                        processing:"Processing",
                        prolog:"Prolog",
                        promql:"PromQL",
                        properties:".properties",
                        protobuf:"Protocol Buffers",
                        pug:"Pug",
                        puppet:"Puppet",
                        pure:"Pure",
                        purebasic:"PureBasic",
                        purescript:"PureScript",
                        python:"Python",
                        qsharp:"Q#",
                        q:"Q (kdb+ database)",
                        qml:"QML",
                        qore:"Qore",
                        r:"R",
                        racket:"Racket",
                        cshtml:"Razor C#",
                        jsx:"React JSX",
                        tsx:"React TSX",
                        reason:"Reason",
                        regex:"Regex",
                        rego:"Rego",
                        renpy:"Ren'py",
                        rescript:"ReScript",
                        rest:"reST (reStructuredText)",
                        rip:"Rip",
                        roboconf:"Roboconf",
                        robotframework:"Robot Framework",
                        ruby:"Ruby",
                        rust:"Rust",
                        sas:"SAS",
                        sass:"Sass (Sass)",
                        scss:"Sass (SCSS)",
                        scala:"Scala",
                        scheme:"Scheme",
                        'shell-session':"Shell session",
                        smali:"Smali",
                        smalltalk:"Smalltalk",
                        smarty:"Smarty",
                        sml:"SML",
                        solidity:"Solidity (Ethereum)",
                        'solution-file':"Solution file",
                        soy:"Soy (Closure Template)",
                        sparql:"SPARQL",
                        'splunk-spl':"Splunk SPL",
                        sqf:"SQF:Status Quo Function (Arma 3)",
                        sql:"SQL",
                        squirrel:"Squirrel",
                        stan:"Stan",
                        stata:"Stata Ado",
                        iecst:"Structured Text (IEC 61131-3)",
                        stylus:"Stylus",
                        supercollider:"SuperCollider",
                        swift:"Swift",
                        systemd:"Systemd configuration file",
                        't4-templating':"T4 templating",
                        't4-cs':"T4 Text Templates (C#)",
                        't4-vb':"T4 Text Templates (VB)",
                        tap:"TAP",
                        tcl:"Tcl",
                        tt2:"Template Toolkit 2",
                        textile:"Textile",
                        toml:"TOML",
                        tremor:"Tremor",
                        turtle:"Turtle",
                        twig:"Twig",
                        typescript:"TypeScript",
                        typoscript:"TypoScript",
                        unrealscript:"UnrealScript",
                        uorazor:"UO Razor Script",
                        uri:"URI",
                        v:"V",
                        vala:"Vala",
                        vbnet:"VB.Net",
                        velocity:"Velocity",
                        verilog:"Verilog",
                        vhdl:"VHDL",
                        vim:"vim",
                        'visual-basic':"Visual Basic",
                        warpscript:"WarpScript",
                        wasm:"WebAssembly",
                        'web-idl':"Web IDL",
                        wgsl:"WGSL",
                        wiki:"Wiki markup",
                        wolfram:"Wolfram language",
                        wren:"Wren",
                        xeora:"Xeora",
                        'xml-doc':"XML doc (.net)",
                        xojo:"Xojo (REALbasic)",
                        xquery:"XQuery",
                        yaml:"YAML",
                        yang:"YANG",
                        zig:"Zig"
                    },
                themeOpt={
                    '':'Default',
                    a11y_d:'A11y Dark',
                    atom_d:'Atom Dark',
                    cb:'CB',
                    cld_c:'Coldark Cold',
                    cld_d:'Coldark Dark',
                    coy:'Coy',
                    dark:'Dark',
                    dracula:'Dracula',
                    du_d:'Duotone Dark',
                    du_f:'Duotone Forest',
                    gr_d:'Gruvbox Dark',
                    gr_l:'Gruvbox Light',
                    lucario:'Lucario',
                    mt_l:'Material Light',
                    night_owl:'Night Owl',
                    okaidia:'Okaidia',
                    tmr:'Tomorrow Night',
                    twilight:'Twilight',
                    vs:'VS',
                    vs_d_p:'VS Dark Plus'
                },
                options=[
                    {
                        id:'lng',
                        label:data.options.lng,
                        type:'select',
                        options:lngOpt
                    },
                    {
                        id:'theme',
                        label:data.options.theme,
                        type:'select',
                        options:themeOpt
                    },
                    {
                        id:'code',
                        label:data.options.code,
                        type:'textarea',
                        codeeditor:self.getStyleVal('lng') || 'javascript',
                        control:{event:'change'}
                    }
                ],
                fr=self.create(options),
                lng=fr.querySelector('#lng'),
                theme=fr.querySelector('#theme'),
                code=fr.querySelector('#code');
                lng.tfOn('change',e=>{
                    const mirror=code.tf_mirror;
                    if(mirror){
                        mirror.save();
                        mirror.destroy();
                    }
                    api.Helper.codeMirror(code,e.currentTarget.value).then(obj => {
                        if(obj){
                            obj.editor.on('change', cm=> {
                               Themify.triggerEvent(obj.el, 'change');
                            });
                        }
                    });
                },{passive:true});
                return fr;
            }
        }
    };
    
            
})(jQuery,tb_app, Themify, window.top, document, undefined);
