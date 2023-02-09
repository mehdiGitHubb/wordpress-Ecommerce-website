((Themify, win, topWindow, doc, api,und) => {
    'use strict';
    const tb_shorcodes = new Map(),
            module_cache = new Map();

    api.is_ajax_call = null;
    let timeout,
            _jqueryXhr,
        isWorking=false,
            _shortcodeXhr;

    
    const Base = {
        visual() {
            api.Registry
                    .on(this.id, 'create', this.createEl)
                    .on(this.id, 'recreate', this.recCeate)
                    .on(this.id, 'preview', this.previewVisibility);

        },
        recCeate() {
            const preview = doc.createElement('span'),
                    batch = Themify.convert(this.el.querySelectorAll('[data-cid]')),
                    constructData = [];

            preview.className = 'tf_lazy tb_preview_component';

            this.el.prepend(preview);
            batch.unshift(this.el);
            for (let i = batch.length - 1; i > -1; --i) {
                let model = api.Registry.get(batch[i].getAttribute('data-cid'));
                if (model) {
                    constructData.push(model.id);
                }
            }
            return new Promise(resolve => {
                api.bootstrap(constructData).then(() => {
                    preview.remove();
                    api.Utils.setCompactMode(this.el.tfClass('module_column'));
                    api.Utils.runJs(this.el);
                    resolve(this.el);
                });
            });
        },
        createEl(markup) {
            const type = this.type,
                    temp = doc.createElement('template');
            temp.innerHTML = markup;
            const item = temp.content.querySelector('.module_' + type),
                    cl = item.classList,
                    attr = item.attributes,
                    el = type === 'subrow' ? this.el.tfClass('module_subrow')[0] : this.el;
            for (let i = cl.length - 1; i > -1; --i) {
                el.classList.add(cl[i]);
            }
            for (let i = attr.length - 1; i > -1; --i) {
                let n = attr[i].name;
                if (n !== 'class') {
                    el.setAttribute(n, attr[i].value);
                }
            }
            const cover = item.tfClass('builder_row_cover')[0],
                    dc = item.tfClass('tbp_dc_styles')[0],
                    slider = item.tfClass(type + '-slider')[0],
                    frames = item.tfClass('tb_row_frame_wrap')[0],
                    fr = doc.createDocumentFragment();
            if (cover !== und && cover.parentNode === item) {
                const _cover = el.tfClass('builder_row_cover')[0];
                if (_cover !== und) {
                    _cover.replaceWith(cover);
                } else {
                    fr.appendChild(cover);
                }
            }
            if (frames !== und && frames.parentNode === item) {
                const _frames = el.tfClass('tb_row_frame_wrap')[0];
                if (_frames !== und) {
                    _frames.replaceWith(frames);
                } else {
                    fr.appendChild(frames);
                }
            }
            if (dc !== und && dc.parentNode === item) {
                const _dc = el.tfClass('tbp_dc_styles')[0];
                if (_dc !== und) {
                    _dc.replaceWith(dc);
                } else {
                    el.appendChild(dc);
                }
            }
            if (slider !== und && slider.parentNode === item) {
                const _slider = el.tfClass(type + '-slider')[0];
                if (_slider !== und) {
                    _slider.replaceWith(slider);
                } else {
                    fr.appendChild(slider);
                }
            }
            el.prepend(fr);
        },
        previewVisibility() {
            const el = this.el,
                    cl = el.classList,
                    visible = this.get('mod_settings');

            if (api.isPreview) {
                if ('hide_all' === visible.visibility_all) {
                    cl.add('hide-all');
                } else {
                    if ('hide' === visible.visibility_desktop) {
                        cl.add('hide-desktop');
                    }

                    if ('hide' === visible.visibility_tablet) {
                        cl.add('hide-tablet');
                    }

                    if ('hide' === visible.visibility_tablet_landscape) {
                        cl.add('hide-tablet_landscape');
                    }

                    if ('hide' === visible.visibility_mobile) {
                        cl.add('hide-mobile');
                    }
                }

                if (visible.custom_parallax_scroll_reverse) {
                    el.dataset.parallaxElementReverse = true;
                }

                if (visible.custom_parallax_scroll_fade) {
                    el.dataset.parallaxFade = true;
                }
                if (visible.custom_parallax_scroll_speed) {
                    el.dataset.parallaxElementSpeed = parseInt(visible.custom_parallax_scroll_speed);
                }

            } else {
                cl.remove('hide-desktop', 'hide-tablet', 'hide-tablet_landscape', 'hide-mobile', 'hide-all');
            }
        }
    },
            Module = {
                templateVisual(settings) {
                    const tpl = api.template('builder-' + this.get('mod_name'));
                    return tpl(settings);
                },
                visual() {
                    Base.visual.call(this);
                    api.Registry
                            .on(this.id, 'live', this.previewLive)
                            .on(this.id, 'ajax', this.previewReload);
                },
                createEl(markup) {
                    const temp = doc.createElement('template'),
                            fr = doc.createDocumentFragment(),
                            mod_name = doc.createElement('div'),
                            actionBtn = doc.createElement('div'),
                            actionWrap = doc.createElement('div'),
                            visibilityLabel = doc.createElement('div'),
                            slug = this.get('mod_name');
                    temp.innerHTML = markup;
                    const module = temp.content.querySelector('.module'),
                            css = temp.content.querySelector('#tb_module_styles'),
                            prefetch=temp.content.querySelectorAll('link[rel="prefetch"]');
                    if (module === und) {
                        if (!api.is_ajax_call) {
                            api.Registry.remove(this.id);
                        }
                        return false;
                    }
                    for(let i=prefetch.length-1;i>-1;--i){
                        prefetch[i].remove();
                    }
                    if (css !== null) {
                        const cssList = JSON.parse(css.innerText);
                        for (let i in cssList) {
                            if (cssList[i].s) {
                                Themify.loadCss(cssList[i].s, i, cssList[i].v);
                            }
                        }
                    }

                    while (this.el.firstChild !== null) {
                        this.el.lastChild.remove();
                    }
                    visibilityLabel.className = 'tb_visibility_hint tf_overflow tf_abs_t tf_hide';
                    mod_name.className = 'tb_data_mod_name tf_overflow tf_textc tf_abs_t tf_hide';
                    mod_name.innerHTML = this.getName();
                    actionBtn.className = 'tf_plus_icon tb_column_btn_plus tb_module_btn_plus tb_disable_sorting tf_rel';
                    actionWrap.className = 'tb_action_wrap tb_module_action tf_abs_t tf_box tf_hide';
                    module.classList.add('module-' + slug, 'tb_' + this.id);
                    module.appendChild(mod_name);
                    fr.append(module, actionWrap, visibilityLabel, actionBtn);
                    this.el.appendChild(fr);
                    this.fixSafariSrcSet();
                    this.visibilityLabel();
                },
                shortcodeToHTML(content) {
                    const shorcodes = [],
                            shorcode_list = themifyBuilder.available_shortcodes;
                    let is_shortcode = false;
                    for (let i = 0, len = shorcode_list.length; i < len; ++i) {
                        content = wp.shortcode.replace(shorcode_list[i], content, atts => {
                            let sc_string = wp.shortcode.string(atts),
                                    k = Themify.hash(sc_string),
                                    replace = '';
                            if (!tb_shorcodes.has(k)) {
                                shorcodes.push(sc_string);
                                replace = '<span class="tmp' + k + '">[loading shortcode...]</span>';
                            } else {
                                replace = tb_shorcodes.get(k);
                            }
                            is_shortcode = true;
                            return replace;
                        });
                    }
                    if (is_shortcode && shorcodes.length > 0) {
						if (_shortcodeXhr && api.is_builder_ready === true) {
							_shortcodeXhr.abort();
						}
                        _shortcodeXhr = new AbortController();
                        const ajaxData = {
                            action: 'tb_render_element_shortcode',
                            shortcode_data: JSON.stringify(shorcodes)
                        };
                        api.LocalFetch(ajaxData, 'json', {signal: _shortcodeXhr.signal}).then(data => {
                            if (data.success) {
                                const shortcodes = data.data.shortcodes,
                                        styles = data.data.styles;
                                if (styles) {
                                    for (let i = 0, len = styles.length; i < len; ++i) {
                                        Themify.loadCss(styles[i].s, null, styles[i].v, null, styles[i].m);
                                    }
                                }
                                for (let i = 0, len = shortcodes.length; i < len; ++i) {
                                    let k = Themify.hash(shortcodes[i].key),
                                            tmp = doc.createElement('template');
                                    tmp.innerHTML = shortcodes[i].html;
                                    this.el.tfClass('tmp' + k)[0].replaceWith(tmp.content);
                                    tb_shorcodes.set(k,shortcodes[i].html);
                                    if (api.is_builder_ready) {
                                        api.Utils.runJs(this.el, 'module', true);
                                    }
                                }
                            }
                        });
                    }
                    return  {content: content, found: is_shortcode};
                },
                previewLive(data, is_shortcode, cid, selector, value) {
                    return new Promise(resolve => {
                        api.is_ajax_call = false;
                        if (_jqueryXhr&& api.is_builder_ready === true) {
                            _jqueryXhr.abort();
                        }
                        if (!data) {
                            data = {};
                        }
                        data.cid = this.id;
                        let is_selector = api.activeModel !== null && selector,
                                tmpl,
                                timer = 300;
                        if (!is_selector || is_shortcode === true) {
                            tmpl = this.templateVisual(data);
                            if (api.is_ajax_call) {//if previewReload is calling from visual template 
                                resolve();
                                return;
                            }
                            if (is_shortcode === true) {
                                const shr = this.shortcodeToHTML(tmpl);
                                if (shr.found) {
                                    timer = 1000;
                                    tmpl = shr.content;
                                    is_selector = null;
                                }
                            }
                        }
                        Themify.trigger('tbDisableInline');
                        if (is_selector) {
                            const len = selector.length;
                            if (len === und) {
                                selector.innerHTML = value;
                            } else {
                                for (let i = len - 1; i > -1; --i) {
                                    selector[i].innerHTML = value;
                                }
                            }
                            resolve();
                        } else {
                            this.createEl(tmpl);
                            if (isWorking!==true && api.is_builder_ready === true) {
                                if (!cid) {
                                    api.liveStylingInstance.el = this.el;
                                    if (timeout) {
                                        clearTimeout(timeout);
                                    }
                                    timeout = setTimeout(() => {
                                        timeout = null;
                                        api.Utils.runJs(this.el, 'module');
                                        resolve();
                                    }, timer);
                                } else {
                                    api.Utils.runJs(this.el, 'module');
                                    resolve();
                                }
                            } else {
                                resolve();
                            }
                        }
                    }).catch(e => {
                        console.log(e, this);
                    });
                },
                previewReload(settings, selector, value) {
                    return new Promise((resolve, reject) => {
                        if (selector && value && api.activeModel) {
                            const len = selector.length;
                            if (len === und) {
                                selector.innerHTML = value;
                            } else {
                                for (let i = 0; i < len; ++i) {
                                    selector[i].innerHTML = value;
                                }
                            }
                            resolve();
                            return;
                        }


                        if (_jqueryXhr&& api.is_builder_ready === true) {
                            _jqueryXhr.abort();
                        }
                        const preview = doc.createElement('span'),
                                callback = data => {
                                    this.createEl(data);
                                    if (isWorking!==true && api.is_builder_ready === true) {
                                        api.liveStylingInstance.el = this.el;
                                        api.Utils.runJs(this.el, 'module', true);
                                    }
                                    preview.remove();
                                    resolve();
                                },
                                name = this.get('mod_name'),
                                reload = settings.unsetKey;
                        Themify.trigger('tbDisableInline');

                        
                        delete settings.unsetKey;
                        delete settings.element_id;

                        settings = api.Helper.clear(settings);
                        settings['module_' + name + '_slug'] = 1; //unique settings
                        settings = JSON.stringify(settings);
                        const key = Themify.hash(settings),
                            res=reload?false:module_cache.get(key);
                        if (res) {
                            callback(res.html.replaceAll(res.id,this.id));
                            return;
                        }
                        preview.className = 'tb_preview_component tf_lazy';
                        this.el.prepend(preview);
                        api.is_ajax_call = true;
                        _jqueryXhr = new AbortController();
                        const ajaxData = {
                            action: 'tb_load_module_partial',
                            element_id: this.id,
                            tb_module_slug: name,
                            tb_module_data: settings
                        };
                        api.LocalFetch(ajaxData, 'text', {signal: _jqueryXhr.signal}).then(res => {
                            module_cache.set(key,{html:res,id:this.id});
                            callback(res);
                            api.is_ajax_call = _jqueryXhr = null;
                        })
                        .catch(() => {
                            this.el.classList.remove('tb_preview_loading');
                            reject();
                        });
                    }).catch(e => {
                        console.log(e, this);
                    });
                }
            };


    Object.assign(api.Base.prototype, Base);
    Object.assign(api.Module.prototype, Module);


    const initBuilder = () => {
        if (!api.Builder.get()) {
            const items = doc.tfClass('themify_builder_content'),
				tmp = api.Helper.correctBuilderData(themifyBuilder.builder_data),
				index=0,//themifyBuilder.builderIndex || 0,//need to detect which layout part is clicked(the same layout part can be twice in a page)
                data = [];
            for (let i=0,len = items.length; i<len;++i) {
                items[i].classList.add('not_editable_builder');
            }
            for (let i = 0, len = tmp.length; i < len; ++i) {
                if (tmp[i] && Object.keys(tmp[i]).length > 0) {
                    data.push(tmp[i]);
                }
            }
            new api.Builder( doc.tfClass('themify_builder_content-'+ themifyBuilder.post_ID)[index], data, themifyBuilder.custom_css);
        }
    },
    render_element = (constructData, gsData) => {
        return new Promise((resolve, reject) => {
            const ajaxData = {
                action: 'tb_render_element',
                batch: JSON.stringify(constructData)
            };
            if (gsData) {
                ajaxData.tmpGS = JSON.stringify(gsData);
            }
            api.LocalFetch(ajaxData).then(res => {
                const prm = [];
                for (let cid in res) {
                    if (cid !== 'tb_module_styles' && cid !== 'gs') {
                        api.Registry.trigger(cid, 'create', res[cid]);
                    }
                }
                if (res.tb_module_styles) {
                    const cssList = res.tb_module_styles;
                    for (let i in cssList) {
                        if (cssList[i].s) {
                            prm.push(Themify.loadCss(cssList[i].s, i, cssList[i].v));
                        }
                    }
                }
                if (api.is_builder_ready === true) {
                    Promise.all(prm).finally(resolve);//can be allSettled,but safari doesn't support it
                } else {
                    resolve();
                }
            })
            .catch(e => {
                console.log(e);
                reject();
            });
        });
    },
            batch_rendering = async (jobs, current, size, gsData) => {
        if (current >= jobs.length) {
            // load callback
            Themify.trigger('tb_css_visual_modules_load');

            return 1;
        } else {
            const smallerJobs = jobs.slice(current, current + size);
            await render_element(smallerJobs, gsData);
            batch_rendering(jobs, current += size, size);
        }
    },
    get_visual_templates=async()=>{
        //form templates are loaded,some server can't handle parallel ajax reqeusts 
        try{
            await api.FormTemplates.init();
        }
        catch(e){
            
        }
        const name = 'tb_visual_templates',
            key=Themify.hash(Themify.v + Object.keys(themifyBuilder.modules)+(themifyBuilder.cache_data || ''));
        let data,
            tmp = '';
        if (!themifyBuilder.debug) {
            try {
                let record = localStorage.getItem(name);
                if (record) {
                    record = JSON.parse(record);
                    if (record.h === key) {
                        data= record.val;
                    }
                }
            } 
            catch (e) {
            }
        }
        if (!data) {//cache visual templates)
            data=await api.LocalFetch({action: 'tb_load_visual_templates'});
            try {
                Themify.requestIdleCallback(()=>{
                    localStorage.setItem(name, JSON.stringify({val: data, h: key}));
                },-1,3000);
            } 
            catch (e) {
                
            }
        }
        for (let i in data) {
            tmp += data[i];
        }
        doc.body.insertAdjacentHTML('beforeend', tmp);
    };

    api.bootstrap = async (settings, gsData) => {
        // collect all jobs
        const jobs = [];
        isWorking=true;
        let set_rules = true;
        if (!settings) {
            set_rules = false;
            initBuilder();
            settings = api.Registry.items.keys();
        }
        for (let cid of settings) {
            let model = api.Registry.get(cid),
                    type = model.type,
                    data = model.fields,
                    styles = model.get('styling'),
                    sizes = type === 'row' || type === 'subrow' ? model.get('sizes') : null;
            if (type === 'module') {
                if(themifyBuilder.modules[data.mod_name] === und){
                    model.getDisabledTpl();
                    continue;
                }
            }
            if ((styles && Object.keys(styles).length > 0) || (sizes && Object.keys(sizes).length > 0)) {
                if (set_rules === true) {
                    api.setCss([data], (type === 'module' ? data.mod_name : type));
                }
            } else if ('module' !== type) {
                continue;
            }
            if ('module' === type && 'tile' !== data.mod_name && data.mod_settings['__dc__'] === und && model.getPreviewType() !== 'ajax') {
                const is_shortcode = 'accordion' === data.mod_name || 'box' === data.mod_name || 'feature' === data.mod_name || 'tab' === data.mod_name || 'text' === data.mod_name || 'plain-text' === data.mod_name || 'pointers' === data.mod_name || 'pro-image' === data.mod_name || 'countdown' === data.mod_name || 'button' === data.mod_name || 'pro-slider' === data.mod_name || 'timeline' === data.mod_name;
                model.trigger('live', data.mod_settings, is_shortcode, cid);
                continue;
            }
            if ('column' === type) {
                delete data.modules;
            } else if ('row' === type || 'module' === type || type === 'subrow') {
                if (type === 'row' && styles.custom_css_row === 'tb-page-break') {
                    continue;
                }
                delete data.cols;
            }
            data.elType = type;
            jobs.push(data);

        }
        settings = null;
        await batch_rendering(jobs, 0, 360, gsData);
        isWorking=false;
        return 1;
    };

    api.render = () => {
        get_visual_templates().then(() => {
            api.bootstrap().then(() => {
                ThemifyStyles.init(api.FormTemplates.getItem(), api.breakpointsReverse, api.Builder.get().id, themifyBuilder.gutters);
                api.jsModuleLoaded().then(() => {
                    api.setCss(api.Builder.get().toJSON());
                    Themify.trigger('themify_builder_ready');
                    topWindow.Themify.trigger('themify_builder_ready');
                    api.Utils.runJs();
                    api.liveStylingInstance = new ThemifyLiveStyling();
                    api.Registry.trigger(api.Builder.get(), 'tb_init');
                    api.is_builder_ready = true;
                    setTimeout(() => {
                        Themify.fonts();
                        api.EdgeDrag.init();
                        verticalResponsiveBars();
                    }, 1500);
                });
            });
        });
    };


    api.setCss = (data, type, isGlobal) => {
        const css = api.GS.createCss(data, type, und),
                fonts = [];
        for (let p in  css) {
            if ('fonts' === p || 'cf_fonts' === p) {
                for (let f in css[p]) {
                    let v = f;
                    if (css[p][f].length > 0) {
                        v += ':' + css[p][f].join(',');
                    }
                    fonts.push(v);
                }
            } else if ('gs' === p) {
                let st = css[p];
                for (let bp in st) {
                    let sheet = ThemifyStyles.getSheet(bp, true),
                            rules = sheet.cssRules;
                    for (let k in st[bp]) {
                        if (api.Utils.findCssRule(rules, k) === false) {
                            sheet.insertRule(k + '{' + st[bp][k].join('') + ';}', rules.length);
                        }
                    }
                }
            } else if (p !== 'bg') {
                let sheet = ThemifyStyles.getSheet(p, isGlobal),
                        rules = sheet.cssRules;
                for (let k in css[p]) {
                    if (api.Utils.findCssRule(rules, k) === false) {
                        sheet.insertRule(k + '{' + css[p][k].join('') + ';}', rules.length);
                    }
                }
            }
        }
        ThemifyConstructor.font_select.loadGoogleFonts(fonts.join('|'));
        return css;
    };
    // Initialize Builder
    Themify.on('builderiframeloaded', () => {
        Themify.w = win.innerWidth;
        Themify.h = win.innerHeight;
        setTimeout(() => {
            Themify.animateCss();
        }, 3000);
        //use top iframe js files
        topWindow.api = api;
        if (!win._) {
            win._ = topWindow._;
        }
        win.wp = topWindow.wp || {};
        if (!win.Backbone) {
            win.Backbone = topWindow.Backbone;
        }
        if (!win.wp.Backbone) {
            win.wp.Backbone = topWindow.wp.Backbone;
        }
        if (!win.wp.template) {
            win.wp.template = topWindow.wp.template;
        }
        if (!win.wp.shortcode) {
            win.wp.shortcode = topWindow.wp.shortcode;
        }
        if (!win.wp.media) {
            win.wp.media = topWindow.wp.media;
        }
        if (!win.MediaElementPlayer) {
            win.MediaElementPlayer = topWindow.MediaElementPlayer;
        }
        if (!jQuery.fn.mediaelementplayer) {
            jQuery.fn.mediaelementplayer = topWindow.jQuery(win.top.document).mediaelementplayer;
        }
        if (!win.wp.mediaelement) {
            win.wp.mediaelement = topWindow.wp.mediaelement;
        }
        if (!win.tinyMCE) {
            win.tinyMCE = topWindow.tinyMCE;
            win.tinymce = topWindow.tinymce;
            win.tinyMCEPreInit = topWindow.tinyMCEPreInit;
            win.switchEditors = topWindow.switchEditors;
        }

        // Used in WP widgets
        if (!win.wpApiSettings) {
            win.wpApiSettings = topWindow.wpApiSettings;
        }
        api.render();

        // Disable Links and Submit forms in live builder
        doc.tfOn('submit', e => {
            e.preventDefault();
        })
        .tfOn('click', e => {
            const target = e.target,
                el = target.closest('a');
            if (el && el.target !== '_blank' && 'javascript:;' !== el.href && ('#' === el.href || !el.href || el.href.replace(new URL(el.href).hash, '') !== win.top.location.href.replace(location.hash, ''))) {
                e.preventDefault();
            }
        });
    }, true)
    .on('tb_undo_add undo',type=>{
        if(typeof type!=='string' || type==='move' || type==='delete' || type==='duplicate' || type==='paste' || type==='import' || type==='saveLightbox' || type==='inline'){
            Themify.trigger('tb_toc');
        }
    });

    const ThemifyLiveStyling = function () {
        this.context = null;
        this.el = null;
        this.module_rules = {};
        this.rulesCache = {};
        this.currentStyleObj = {};
    };
    ThemifyLiveStyling.prototype.init = function (isInline, isGlobal, model, bp) {
        this.context = api.LightBox.el;
        this.model = model || api.activeModel;
        this.bp = bp || api.activeBreakPoint;
        this.group = this.model.get('mod_name');
        let type,
                elId = this.model.id;
        if (isGlobal === true && api.GS.previousId !== null) {
            elId = api.GS.previousId;
            type = api.Registry.get(elId).get('mod_name');

        } else {
            type = this.group;
        }
        this.prefix = ThemifyStyles.getBaseSelector(type, elId);
        this.el = isGlobal ? api.Builder.get().el.querySelector(this.prefix) : this.model.el;
        this.currentStyleObj = {};
        if (this.rulesCache[this.bp] === und) {
            this.rulesCache[this.bp] = {};
        }
        this.currentSheet = ThemifyStyles.getSheet(this.bp, isGlobal);
        if (isInline !== true) {
            if (this.model.type !== 'column' && this.model.type !== 'subrow') {
                this.bindAnimation();
            }
            this.bindTabsSwitch();
            this.initModChange();
        }
    };

    /**
     * Apply CSS rules to the live styled element.
     *
     * @param {string} containing CSS rules for the live styled element.
     * @param {mixed) 
     * @param {Array} selectors List of selectors to apply the newStyleObj to (e.g., ['', 'h1', 'h2']).
     */
    ThemifyLiveStyling.prototype.setLiveStyle = function (prop, val, selectors) {
        if (!selectors) {
            selectors = [''];
        } else if (typeof selectors === 'string') {
            selectors = [selectors];
        }
        selectors = ThemifyStyles.getNestedSelector(selectors);
        let fullSelector = '';

        const rules = this.currentSheet.cssRules;
        for (let i = 0, len = selectors.length; i < len; ++i) {
            let isPseudo = this.styleTabId === 'h' ? selectors[i].endsWith(':after') || selectors[i].endsWith(':before') : true;
            if (isPseudo === false && selectors[i].indexOf(':hover') === -1) {
                selectors[i] += ':hover';
            }
            fullSelector += this.prefix + selectors[i];
            if (isPseudo === false) {
                fullSelector += ',' + this.prefix + selectors[i].replace(':hover', '.tb_visual_hover');
            }
            if (i !== (len - 1)) {
                fullSelector += ',';
            }
        }
        if (this.isChanged === true) {
            let hover_items;
            if (this.styleTabId === 'h') {
                const hover_selectors = fullSelector.split(','),
                        builder = api.Builder.get().el;
                for (let i = hover_selectors.length - 1; i > -1; --i) {
                    if (hover_selectors[i].indexOf('tb_visual_hover') === -1) {
                        hover_items = builder.querySelectorAll(hover_selectors[i].split(':hover')[0]);
                        for (let j = hover_items.length - 1; j > -1; --j) {
                            hover_items[j].classList.add('tb_visual_hover');
                        }
                    }
                }
            } else {
                this.el.classList.remove('tb_visual_hover');
                hover_items = this.el.tfClass('tb_visual_hover');
                for (let i = hover_items.length - 1; i > -1; --i) {
                    hover_items[i].classList.remove('tb_visual_hover');
                }
            }
        }
        fullSelector = fullSelector.replace(/\s{2,}/g, ' ').replace(/\s*>\s*/g, '>').replace(/\,\s/g, ',');
        const hkey = Themify.hash(fullSelector),
                orig_v = val;
        let index = this.rulesCache[this.bp][hkey] !== und ? this.rulesCache[this.bp][hkey] : api.Utils.findCssRule(rules, fullSelector);
        if (val === false) {
            val = '';
        }
        if (index === false || !rules[index]) {
            if (val === '') {
                return;
            }
            index = rules.length;
            this.currentSheet.insertRule(fullSelector + '{' + prop + ':' + val + ';}', index);
        } else {
            const priority = val !== '' && val.indexOf('!important') !== -1 ? 'important' : '';
            if (priority !== '') {
                val = val.replace('!important', '');
            }
            rules[index].style.setProperty(prop, val, priority);

        }
        this.rulesCache[this.bp][hkey] = index;
        Themify.trigger('tb_' + this.model.type + '_styling', [this.group, prop, val, orig_v, this.el]);
    };


    ThemifyLiveStyling.prototype.initModChange = function (off) {
        if (off === true) {
            Themify.off('themify_builder_change_mode', this.modChange);
            this.modChange = null;
            return;
        }
        if (!this.modChange) {
            this.modChange = (prevbreakpoint, breakpoint) => {
                this.setMode(breakpoint, api.GS.activeGS !== null);
            };
        }
        Themify.on('themify_builder_change_mode', this.modChange);
    };

    ThemifyLiveStyling.prototype.setMode = function (breakpoint, isGlobal) {
        this.bp = breakpoint;
        if (this.rulesCache[breakpoint] === und) {
            this.rulesCache[breakpoint] = {};
        }
        this.currentSheet = ThemifyStyles.getSheet(breakpoint, isGlobal);
    };



    ThemifyLiveStyling.prototype.reset = function () {
        this.rulesCache = {};
        const points = api.breakpointsReverse;
        for (let i = points.length - 1; i > -1; --i) {
            let sheet = ThemifyStyles.getSheet(points[i]),
                    rules = sheet.cssRules;
            for (let j = rules.length - 1; j > -1; --j) {
                sheet.deleteRule(j);
            }
            sheet = ThemifyStyles.getSheet(points[i], true);
            rules = sheet.cssRules;
            for (let j = rules.length - 1; j > -1; --j) {
                sheet.deleteRule(j);
            }
        }
    };


    //closing lightbox
    ThemifyLiveStyling.prototype.clear = function () {
        const el = this.el;
        if (el) {
            el.classList.remove('animated', 'hover-wow', 'tb_visual_hover');
        }
        this.module_rules = {};
        this.styleTab = this.styleTabId = this.currentField = this.isChanged = null;
        const hover_items = el.tfClass('tb_visual_hover');
        for (let i = hover_items.length - 1; i > -1; --i) {
            hover_items[i].classList.remove('tb_visual_hover');
        }
        this.bindAnimation(true);
        this.bindTabsSwitch(true);
        this.initModChange(true);
        this.el = this.currentStyleObj = this.currentSheet = this.bp = this.model = this.context = null;
    };
    ThemifyLiveStyling.prototype.addOrRemoveFrame = function (_this, settings) {
        if (this.model.type === 'module') {
            return;
        }
       
        let  el = this.model.type === 'subrow' ? this.el.querySelector(':scope>.module_subrow') : this.el,
                isLive = typeof _this === 'string',
                side = isLive ? _this : _this.closest('.tb_tab').id.split('_').pop(),
                selector,
                frame_wrap = el.querySelector(':scope>.tb_row_frame_wrap');
        
        if (!frame_wrap) {
            frame_wrap = doc.createElement('div');
            frame_wrap.className = 'tb_row_frame_wrap tf_overflow tf_abs';
            el.prepend(frame_wrap);
        }
        ThemifyBuilderModuleJs.addonLoad(el,'fr',false);
        let frame = frame_wrap.tfClass('tb_row_frame_' + side)[0];
        if (und === settings) {
            settings = {};
            selector = this.getValue(side + '-frame_type').selector;
            const options = ['custom', 'location', 'width', 'height', 'width_unit', 'height_unit', 'repeat', 'type', 'layout', 'color', 'sh_x', 'sh_y', 'sh_b', 'sh_c', 'ani_dur', 'ani_rev'];
            for (let i = 0, len = options.length; i < len; ++i) {
                let item = api.LightBox.el.querySelector('#'+side + '-frame_' + options[i]),
                        v;
                if (options[i] === 'type' ) {
                    v = item.querySelector('input:checked').value;
                } else if ( options[i] === 'ani_rev' ) {
					v = item.querySelector('input').value;
				} else if (options[i] === 'layout') {
                    v = item.tfClass('selected')[0].id;
                } else if (options[i] === 'color' || options[i] === 'sh_c') {
                    v = api.Helper.getColor(item);
                    if (v === '') {
                        continue;
                    }
                } else {
                    v = item.value;
                }
                settings[options[i]] = v;
            }
        }
		
		const animated = parseFloat( settings.ani_dur ) > 0;
        if (settings.type === side + '-presets' || settings.type === side + '-custom') {
            if ((settings.type === side + '-presets' && (!settings.layout || settings.layout === 'none')) || (settings.type === side + '-custom' && !settings.custom)) {
                if (this.bp === 'desktop') {
                    if (!isLive) {
                        this.setLiveStyle('background-image', '', selector);
                    }
                } else if (settings.layout === 'none') {
                    this.setLiveStyle('background-image', 'none', selector);
                }
                return;
            }
            if (!frame) {
                frame = doc.createElement('div');
                frame.className = 'tf_abs tf_overflow tf_w tb_row_frame tb_row_frame_' + side;
                if (settings.location !== und) {
                    frame.className += ' ' + settings.location;
                }
                frame_wrap.appendChild(frame);
            } else {
                frame.classList.remove('in_bellow', 'in_front');
                if (settings.location !== und) {
                    frame.classList.add(settings.location);
                }
            }
        }
        if (!isLive) {
            if (settings.type === side + '-presets') {
                const layout = (side === 'left' || side === 'right') ? settings.layout + '-l' : settings.layout,
                        key = Themify.hash(layout);
                if (!ThemifyStyles.fields.frameCache[key]) {
                    const frame_tmpl = doc.tfId('tmpl-frame_' + layout);
                    if (frame_tmpl !== null) {
                        ThemifyStyles.fields.frameCache[key] = frame_tmpl.textContent.trim();
                    }
                }
                if (ThemifyStyles.fields.frameCache[key]) {
                    let svg = ThemifyStyles.fields.frameCache[key];
                    if (settings.color) {
                        svg = svg.replace(/\#D3D3D3/ig, settings.color);
                    }
                    this.setLiveStyle('background-image', 'url("data:image/svg+xml;utf8,' + encodeURIComponent(svg) + '")', selector);
                }

            } else {
                this.setLiveStyle('background-image', 'url("' + settings.custom + '")', selector);
            }
			/* override some user settings when using animation */
			if ( animated ) {
				if ( side === 'left' || side === 'right' ) {
					settings.height = 200;
					settings.height_unit = '%';
				} else {
					settings.width = 200;
					settings.width_unit = '%';
				}
				settings.repeat = settings.repeat === '' ? 2 : parseInt( settings.repeat ) * 2;
			}
            this.setLiveStyle('width', (settings.width ? (settings.width + settings.width_unit) : ''), selector);
            this.setLiveStyle('height', (settings.height ? (settings.height + settings.height_unit) : ''), selector);
            if (settings.repeat) {
				let background_size = 100 / settings.repeat;
				if (side === 'left' || side === 'right') {
                    this.setLiveStyle('background-size', '100% ' + background_size + '%', selector);
                } else {
                    this.setLiveStyle('background-size', background_size + '% 100%', selector);
                }
            } else {
                this.setLiveStyle('background-size', '', selector);
            }

			if ( animated ) {
				this.setLiveStyle('animation-name', 'tb_frame_' + ( side === 'left' || side === 'right' ? 'vertical' : 'horizontal' ), selector);
				this.setLiveStyle('animation-iteration-count', 'infinite', selector);
				this.setLiveStyle('animation-timing-function', 'linear', selector);
				this.setLiveStyle('animation-duration', settings.ani_dur + 's', selector);
				this.setLiveStyle('animation-direction', settings.ani_rev === '1' ? 'reverse' : '', selector);
			} else {
				this.setLiveStyle('animation-name', '', selector);
			}

            /* frame shadow */
            if (settings.sh_b && settings.sh_c) {
                const shadow = [
                    settings.sh_x ? settings.sh_x + 'px' : 0, // horizontal offset
                    settings.sh_y ? settings.sh_y + 'px' : 0, // vertical offset
                    settings.sh_b + 'px', // blur
                    settings.sh_c //  color
                ];
                this.setLiveStyle('filter', 'drop-shadow(' + shadow.join(' ') + ')', selector);
            } else {
                this.setLiveStyle('filter', '', selector);
            }
        }
    };


    ThemifyLiveStyling.prototype.overlayType = function (val) {
        if (this.model.type === 'module') {
            return;
        }
        const is_color = val === 'color' || val === 'hover_color',
                cl = is_color ? 'tfminicolors-input' : 'themify-gradient-type',
                el = this.styleTab.tfClass('tb_group_element_' + val)[0].tfClass(cl)[0];
        if (is_color) {
            let v = el.value;
            if (v) {
                v = api.Helper.getColor(el);
            }
            Themify.triggerEvent(el, 'themify_builder_color_picker_change', {val: v});
        } else {
            Themify.triggerEvent(el, 'change');

        }
    };

    ThemifyLiveStyling.prototype.addOrRemoveComponentOverlay = function (type, id, v) {
        if (this.model.type === 'module') {
            return;
        }
        let overlayElmt = this.getComponentBgOverlay(this.model.type);
        const data = this.getValue(id),
                selector = data.selector;
        this.el.classList.toggle('tb_visual_hover', this.styleTabId === 'h');
        if (v === '' && id) {
            this.setLiveStyle('background-image', '', selector);
            this.setLiveStyle('background-color', '', selector);
        } else {
            if (!overlayElmt) {
                overlayElmt = doc.createElement('div');
                overlayElmt.className = 'builder_row_cover tf_abs';
                this.el.tfClass('tb_' + this.model.type + '_action')[0].before(overlayElmt);
            }
            ThemifyBuilderModuleJs.addonLoad(overlayElmt,'cover',false);
            // To prevent error if runs from GS
            if (!data) {
                return;
            }
            if (type === 'color') {
                this.setLiveStyle('background-image', 'none', selector);
            } else {
                this.setLiveStyle('background-color', false, selector);
            }
            this.setLiveStyle(data.prop, v, selector);
        }
    };

    ThemifyLiveStyling.prototype.outline = function (el) {
        const selector = this.getValue(el.id),
                container = el.closest('.tb_multi_fields'),
                color = api.Helper.getColor(container.tfClass('outline_color')[0]),
                width = parseFloat(container.tfClass('outline_width')[0].value),
                style = container.tfClass('outline_style')[0].value;

        if (style === 'none') {
            this.setLiveStyle('outline', 'none', selector);
        } else if (!isNaN(width) && width !== '' && color !== '') {
            this.setLiveStyle('outline', width + 'px ' + style + ' ' + color, selector);
        }
    };

    ThemifyLiveStyling.prototype.bindMultiFields = function (_this, data) {
        data = this.getValue(_this.id);
        if (data) {
            let parent = _this.closest('.tb_seperate_items'),
                    prop = data.prop.split('-'),
                    is_border_radius = prop[3] !== und,
                    is_border = is_border_radius === false && prop[0] === 'border',
                    is_checked = parent.hasAttribute('data-checked'),
                    getCssValue = el=> {
                        let v = '';
                        if (is_border === true) {
                            const p = el.closest('li'),
                                    width = parseFloat(p.tfClass('border_width')[0].value.trim()),
                                    style = p.tfClass('border_style')[0].value,
                                    color_val = api.Helper.getColor(p.tfClass('tfminicolors-input')[0]);
                            if (style === 'none') {
                                v = style;
                            } else if (!isNaN(width) && width !== '' && color_val !== '') {
                                v = width + 'px ' + style + ' ' + color_val;
                            }
                        } else {
                            v = el.value.trim();
                            if (v !== '') {
                                v = parseFloat(v);
                                if (isNaN(v)) {
                                    v = '';
                                } else {
                                    v += el.closest('.tb_input').querySelector('#' + el.id + '_unit').value;
                                }
                            }
                        }
                        return v;
                    },
                    setFullWidth = (val, prop) => {
                        if (is_border === false && is_border_radius === false) {
                            if (this.model.type === 'row' && tbLocalScript.fullwidth_support !== und && ((is_checked && (prop === 'padding' || prop === 'margin')) || prop === 'padding-left' || prop === 'padding-right' || prop === 'margin-left' || prop === 'margin-right')) {
                                const type = prop.split('-'),
                                        k = this.bp + '-' + type[0];
                                if (is_checked) {
                                    val = val + ',' + val;
                                } else {
                                    let old_val = this.el.dataset[k];
                                    if (!old_val) {
                                        old_val = [];
                                    } else {
                                        old_val = old_val.split(',');
                                    }
                                    if (type[1] === 'left') {
                                        old_val[0] = val;
                                    } else {
                                        old_val[1] = val;
                                    }
                                    val = old_val.join(',');
                                }
                                this.el.setAttribute('data-' + k, val);
                                Themify.reRun(this.el, true);
                            }
                            if ((is_checked && prop === 'padding') || prop.indexOf('padding') === 0) {
                                setTimeout(()=> {
                                    Themify.trigger('tfsmartresize', {w:Themify.w, h:Themify.h});
                                }, 600);
                            }
                        }
                    },    
                    val = is_checked === true ? getCssValue(_this) : null;
            prop = prop[0];
            for (let items=parent.tfClass('tb_multi_field'),i = items.length - 1; i > -1; --i) {
                if (is_checked === false) {
                    val = getCssValue(items[i]);
                }
                prop = this.getValue(items[i].id).prop;
                this.setLiveStyle(prop, val, data.selector);
                setFullWidth(val, prop);
            }
        }
    };

    ThemifyLiveStyling.prototype.bindRowWidthHeight = function (id, val, el) {
        if (!el) {
            el = this.el;
        }
        const cl = el.classList;
        if (id === 'row_height') {
            cl.toggle('fullheight', val === 'fullheight');
        } else {
            cl.remove('fullwidth', 'fullwidth_row_container');
            if (val === 'fullwidth') {
                cl.add('fullwidth_row_container');
                Themify.reRun(el, true);
            } else if (val === 'fullwidth-content') {
                cl.add('fullwidth');
                Themify.reRun(el, true);
            } else {
                const st=el.style;
                st.marginLeft = st.marginRight = st.paddingLeft = st.paddingRight = st.width = '';
            }
        }
        Themify.trigger('tfsmartresize', {w:Themify.w, h:Themify.h});
    };
    ThemifyLiveStyling.prototype.bindAnimation = function (off) {
        if (off === true) {
            if (this.animateEvent) {
                this.context.tfOff('change', this.animateEvent, {passive: true});
                this.animateEvent = null;
            }
            return;
        }
        if (!this.animateEvent) {
            const self = this;
            this.animateEvent = e => {
                const id = e.target.id;
                if (id === 'animation_effect' || id === 'animation_effect_delay' || id === 'animation_effect_repeat' || id === 'hover_animation_effect') {
                    const is_hover = id === 'hover_animation_effect',
                            key = is_hover ? 'hover_animation_effect' : 'animation_effect',
                            effect = is_hover ? e.target.value : self.context.querySelector('#animation_effect').value,
                            animationEffect = self.currentStyleObj[key] !== und ? self.currentStyleObj[key] : ThemifyConstructor.values[key],
                            cl = self.el.classList,
                            st = self.el.style;
                    if (animationEffect) {
                        cl.remove(animationEffect, 'wow');
                        st.setProperty('animation-name', '');
                        st.setProperty('animation-delay', '');
                        st.setProperty('animation-iteration-count', '');
                    }
                    cl.remove('animated', 'tb_hover_animate');
                    self.currentStyleObj[key] = effect;
                    if (effect) {
                        const delay = is_hover ? '' : parseFloat(self.context.querySelector('#animation_effect_delay').value),
                                repeat = is_hover ? '' : parseInt(self.context.querySelector('#animation_effect_repeat').value),
                                saveOld = tbLocalScript.is_animation;
                        self.el.dataset.tfAnimation=effect;
                        st.setProperty('animation-delay', ((delay > 0 && !isNaN(delay)) ? delay + 's' : ''));
                        st.setProperty('animation-iteration-count', ((repeat > 0 && !isNaN(repeat)) ? repeat : ''));
                        cl.add('wow');
                        tbLocalScript.is_animation = true;
                        ThemifyBuilderModuleJs.wow(self.el, false);
                        tbLocalScript.is_animation = saveOld;
                    }
                }
            };
        }
        this.context.tfOn('change', this.animateEvent, {passive: true});
    };


    ThemifyLiveStyling.prototype.getStylingVal = function (stylingKey) {
        return this.currentStyleObj[stylingKey] !== und ? this.currentStyleObj[stylingKey] : '';
    };

    ThemifyLiveStyling.prototype.setStylingVal = function (stylingKey, val) {
        this.currentStyleObj[stylingKey] = val;
    };

    ThemifyLiveStyling.prototype.bindBackgroundMode = function (val, id) {

        const bgValues = {
            'repeat': 'repeat',
            'repeat-x': 'repeat-x',
            'repeat-y': 'repeat-y',
            'repeat-none': 'no-repeat',
            'no-repeat': 'no-repeat',
            'fullcover': 'cover',
            'best-fit-image': 'contain',
            'builder-parallax-scrolling': 'cover',
            'builder-zoom-scrolling': '100%',
            'builder-zooming': '100%'
        },
        el = this.el;
        if (bgValues[val] !== und) {
            const oldBp = ThemifyStyles.breakpoint;
            ThemifyStyles.breakpoint = this.bp;
            const propCSS = {
                'background-repeat': '',
                'background-size': '',
                'background-position': '',
                'background-attachment': ''
            },
            modName = this.model.get('mod_name'),
            getInputValue = (id, sel) => {
                let input;
                if (api.activeModel && api.activeModel.id === this.model.id) {
                    input = this.context.querySelector(sel);
                    if (input) {
                        input = input.value.trim();
                    }
                } else {
                    input = ThemifyStyles.getStyleVal(id, this.model.get('styling'));
                }
                return input;
            },
            args = ThemifyStyles.getStyleOptions(modName)[id],
            data=this.getValue(id),
            selector=args?args.selector:data.selector,
            type = getInputValue('background_type', '#background_type input:checked'),
            item = (type && type !== 'image') || getInputValue('resp_no_bg', '#resp_no_bg input:checked') ? null :(args?getInputValue(args.origId, '#' + args.origId):null);

            if (item && item === '') {
                val = null;
            } else if(args){
                const posId = 'background_position',
                        pos = getInputValue(posId, '#' + posId),
                        allData = {};
                if (this.bp === 'desktop') {
                    allData[id] = val;
                    allData[posId] = pos;
                } else {
                    allData['breakpoint_' + this.bp] = {[id]:val,[posId]:pos};
                }
                const css = ThemifyStyles.fields[args.type].call(ThemifyStyles, id, modName, args, {[args.origId]: true}, this.id, allData);
                if (css) {
                    const tmp = css.split('#@#');
                    for (let i = tmp.length - 1; i > -1; --i) {
                        if (tmp[i]) {
                            let [k, v] = tmp[i].split(':');
                            propCSS[k] = v;
                        }
                    }
                }
                if(api.activeBreakPoint!=='desktop'){
                    if(propCSS['background-attachment']===''){
                        propCSS['background-attachment']=getInputValue('background_attachment', '#background_attachment') || '';
                    }
                    if(propCSS['background-position']===''){
                        propCSS['background-position']=getInputValue('background_position', '#background_position') || '';
                    }
                }
            }
            else{
                if (val.indexOf('repeat') !== -1) {
                    propCSS['background-repeat'] = bgValues[val];
                    propCSS['background-size'] = 'auto';
                } else {
                    propCSS['background-size'] = bgValues[val];
                    propCSS['background-repeat'] = 'no-repeat';
                }
            }
            for (let jsBg = ['parallax', 'zooming', 'zoom'], i = jsBg.length - 1; i > -1; --i) {
                if (el.getAttribute('data-' + jsBg[i] + '-bg') === this.bp) {
                    el.removeAttribute('data-' + jsBg[i] + '-bg');
                }
            }
            el.classList.remove('builder-parallax-scrolling', 'builder-zooming', 'builder-zoom-scrolling', 'active-zooming');
            el.style.backgroundSize = el.style.backgroundPosition = '';
            ThemifyStyles.breakpoint = oldBp;
            if (this.model.type === 'module' && (val === 'builder-parallax-scrolling' || val === 'builder-zooming' || val === 'builder-zoom-scrolling' || val === 'best-fit-image')) {
                return;
            }

            for (let key in propCSS) {
                this.setLiveStyle(key, propCSS[key], selector);
            }
            if (val === 'builder-zoom-scrolling' || val === 'builder-zooming' || val === 'builder-parallax-scrolling') {
                el.setAttribute('data-' + val.split('-')[1] + '-bg', this.bp);
                Themify.reRun(el, true);
            }
        }
    };

    ThemifyLiveStyling.prototype.position = function (val, id) {
        if (val && val.length > 0) {
            const data = this.getValue(id);
            if (data) {
                requestAnimationFrame(()=>{
                    const v2 = val.split(',');
                    this.setLiveStyle(data.prop, v2[0] + '% ' + v2[1] + '%', data.selector);
                    if(this.el.classList.contains('builder-parallax-scrolling')){
                        this.el.style.setProperty('background-position-y','');
                        Themify.reRun(this.el, true);
                    }
                });
            }
        }
    };

    ThemifyLiveStyling.prototype.bindBackgroundSlider = function (data) {
        if (this.model.type === 'module') {
            return;
        }
        const images = this.context.querySelector('#' + data.id).value.trim();
        this.removeBgSlider();

        if (images) {
            if (this.cache === und) {
                this.cache = {};
            }
            const options = {
                shortcode: encodeURIComponent(images),
                mode: this.context.querySelector('#background_slider_mode').value,
                speed: this.context.querySelector('#background_slider_speed').value,
                size: this.context.querySelector('#background_slider_size').value
            },
            callback = slider => {
                const tmp = doc.createElement('template');
                tmp.innerHTML = slider;
                const bgCover = this.getComponentBgOverlay(this.model.type),
                        bgSlider = tmp.content;
                if (bgCover) {
                    bgCover.after(bgSlider);
                } else {
                    this.el.prepend(bgSlider);
                }
                Themify.reRun(this.el, true);
            };
            let hkey = '';

            for (let i in options) {
                hkey += Themify.hash(i + options[i]);
            }
            if (this.cache[hkey] !== und) {
                callback(this.cache[hkey]);
                return;
            }
            options.type = this.model.type;
            const ajaxData = {
                action: 'tb_slider_live_styling',
                tb_background_slider_data: JSON.stringify(options)
            };
            api.LocalFetch(ajaxData, 'text').then(slider => {
                if (slider.length < 10) {
                    return;
                }
                this.cache[hkey] = slider;
                callback(slider);
            });
        }
    };
    ThemifyLiveStyling.prototype.videoOptions = function (item, val) {
        if (this.model.type === 'module') {
            return;
        }
        let video = this.el.tfClass('big-video-wrap')[0],
                el = '',
                is_checked = item.checked === true,
                type = '';
        if (video === und) {
            return;
        }
        if (video.classList.contains('themify_ytb_wrapper')) {
            el = this.el;
            type = 'ytb';
        } else if (video.classList.contains('themify-video-vmieo')) {
            el = $f(video.children('iframe')[0]);
            if (el) {
                type = 'vimeo';
            }
        } else {
            el = this.el.dataset.plugin_ThemifyBgVideo;
            type = 'local';
        }

        if (val === 'mute') {
            if (is_checked) {
                if (type === 'ytb') {
                    el.ThemifyYTBMute();
                } else if (type === 'vimeo') {
                    el.api('setVolume', 0);
                } else if (type === 'local') {
                    el.muted(true);
                }
                this.el.dataset.mutevideo = 'mute';
            } else {
                if (type === 'ytb') {
                    el.ThemifyYTBUnmute();
                } else if (type === 'vimeo') {
                    el.api('setVolume', 1);
                } else if (type === 'local') {
                    el.muted(false);
                }
                this.el.removeAttribute('data-mutevideo');
            }
        } else if (val === 'unloop') {
            if (is_checked) {
                if (type === 'vimeo') {
                    el.api('setLoop', 0);
                } else if (type === 'local') {
                    el.loop(false);
                }
                this.el.removeAttribute('data-unloopvideo');
            } else {
                if (type === 'vimeo') {
                    el.api('setLoop', 1);
                } else if (type === 'local') {
                    el.loop(true);
                }
                this.el.dataset.unloopvideo = 'loop';

            }
        }
    };
    ThemifyLiveStyling.prototype.bindBackgroundTypeRadio = function (bgType) {
        let id = 'tb_uploader_input';
        if (this.model.type !== 'module') {
            if (bgType !== 'slider') {
                if (this.styleTabId === 'n') {
                    this.removeBgSlider();
                }
            } else {
                id = 'tb_shortcode_input';
            }
            if (bgType !== 'video' && this.styleTabId === 'n') {
                this.removeBgVideo();
            }
        }
        if (bgType !== 'gradient') {
            this.setLiveStyle('background-image', 'none');
        } else {
            id = 'themify-gradient-type';
        }
        const group = this.styleTab.querySelector('.tb_group_element_' + bgType+':not(.background_color)');
        if(group){
            Themify.triggerEvent(group.tfClass(id)[0], 'change');
            if (bgType === 'image' && this.model.type === 'module') {
                const el = group.tfClass('tfminicolors-input')[0];
                if (el) {
                    Themify.triggerEvent(el, 'themify_builder_color_picker_change', {val: el.value});
                }
            }
        }
    };

    ThemifyLiveStyling.prototype.bindFontColorType = function (v, id, type) {
        if (type === 'radio') {
            const is_color = v.indexOf('_solid') !== -1,
                    uid = is_color === true ? v.replace(/_solid$/ig, '') : v.replace(/_gradient$/ig, '-gradient-type'),
                    el = topWindow.document.tfId(uid);
            if (is_color === true) {
                let v = api.Helper.getColor(el);
                if (v === und || v === '') {
                    v = '';
                }
                Themify.triggerEvent(el, 'themify_builder_color_picker_change', {val: v});
            } else {
                Themify.triggerEvent(el, 'change');
            }
            return;
        }
        let prop = type,
                selector = this.getValue(id).selector;
        if (prop === 'color') {


            if (v === und || v === '') {
                v = '';
                this.setLiveStyle('-webkit-background-clip', '', selector);
                this.setLiveStyle('background-clip', '', selector);
                this.setLiveStyle('background-image', '', selector);
            } else {
                this.setLiveStyle('-webkit-background-clip', '', selector);
                this.setLiveStyle('background-clip', 'border-box', selector);
                this.setLiveStyle('background-image', 'none', selector);
            }
        } else if (v !== '') {
            prop = 'background-image';
            this.setLiveStyle('color', 'transparent', selector);
            this.setLiveStyle('-webkit-background-clip', 'text', selector);
            this.setLiveStyle('background-clip', 'text', selector);
        }
        if (v !== '' || prop === 'color'){
            this.setLiveStyle(prop, v, selector);
        }
    };

    ThemifyLiveStyling.prototype.shadow = function (el, id, prop) {
        const data = this.getValue(id);
        if (data) {
            let items = el.closest('.tb_seperate_items').tfClass('tb_shadow_field'),
                    inset = '',
                    allisEmpty = true,
                    val = '';
            for (let i = 0, len = items.length; i < len; ++i) {
                if (items[i].classList.contains('tb_checkbox')) {
                    inset = items[i].checked ? 'inset ' : '';
                } else {
                    let v = items[i].value.trim();
                    if (ThemifyConstructor.styles[items[i].id].type === 'color') {
                        v = api.Helper.getColor(items[i]);
                    } else {
                        if (v === '') {
                            v = 0;
                        } else {
                            allisEmpty = false;
                            v += items[i].closest('.tb_input').querySelector('#' + items[i].id + '_unit').value;
                        }
                    }
                    val += v + ' ';
                }
            }
            val = allisEmpty === true ? '' : inset + val;
            this.setLiveStyle(data.prop, val, data.selector);
        }
    };
    ThemifyLiveStyling.prototype.filters = function (el, id) {
        let items = el.closest('.tb_filters_fields').tfClass('tb_filters_field'),
                val = '',
                data;
        for (let i = 0, len = items.length; i < len; ++i) {
            let v = items[i].value.trim();
            if ('' === v) {
                continue;
            }
            data = this.getValue(items[i].id);
            v += 'hue-rotate' === data.prop ? 'deg' : items[i].closest('.tb_seperate_items').querySelector('#' + items[i].id + '_unit').textContent;
            v = data.prop + '(' + v + ')';
            val += v + ' ';
        }
        data = this.getValue(id);
        this.setLiveStyle('filter', val, data.selector);
    };
    ThemifyLiveStyling.prototype.transform = function (el, id) {
        let css = '';
        const wrap = el.closest('.tb_transform_fields'),
                data = this.getValue(id),
                options = ['scale', 'translate', 'rotate', 'skew'],
                orig_id = id.split('_')[0];
        for (let i = 0, len = options.length; i < len; ++i) {
            let type = options[i];
            switch (type) {
                case 'scale':
                case 'translate':
                case 'skew':
                    let x = wrap.querySelector('#' + orig_id + '_' + type + '_top').value.trim(),
                            y = wrap.querySelector('#' + orig_id + '_' + type + '_bottom').value.trim(),
                            unit;
                    if ('translate' === type) {
                        unit = {
                            x: wrap.querySelector('#' + orig_id + '_' + type + '_top_unit').value,
                            y: wrap.querySelector('#' + orig_id + '_' + type + '_bottom_unit').value
                        };
                    } else {
                        unit = 'skew' === type ? 'deg' : '';
                    }
                    if ('' !== x || '' !== y) {
                        if ('' !== x && wrap.querySelector('#' + orig_id + '_' + type + '_opp_bottom .style_apply_oppositive').checked) {
                            css += type + '(' + x + ('translate' === type ? unit.x : unit) + ') ';
                        } else if ('' !== x && '' !== y) {
                            css += type + '(' + x + ('translate' === type ? unit.x : unit) + ',' + y + ('translate' === type ? unit.y : unit) + ') ';
                        } else {
                            css += '' !== x ? type + 'X(' + x + ('translate' === type ? unit.x : unit) + ') ' : type + 'Y(' + y + ('translate' === type ? unit.y : unit) + ') ';
                        }
                    }
                    break;
                case 'rotate':
                    const inputs = ['z', 'y', 'x'];
                    for (let j = inputs.length - 1; j > -1; --j) {
                        let v = wrap.querySelector('#' + orig_id + '_' + type + '_' + inputs[j]).value.trim();
                        if ('' !== v) {
                            css += type + inputs[j].toUpperCase() + '(' + v + 'deg) ';
                        }
                    }
                    break;
            }
        }
        this.setLiveStyle('transform', css.trim(), data.selector);
    };

    ThemifyLiveStyling.prototype.setData = function (id, prop, val) {
        const data = this.getValue(id);

        if (data) {
            if (prop === '') {
                prop = data.prop;
            }
            this.setLiveStyle(prop, val, data.selector);
        }
    };

    ThemifyLiveStyling.prototype.bindEvents = function (el, data) {
        if (el.classList.contains('style_apply_all')) {
            return;
        }

        const self = this,
                getTab = el => {
                    if (this.currentField !== el.id || '' === this.currentField) {
                        this.currentField = el.type === 'radio' ? false : el.id;
                        this.isChanged = true;
                        this.styleTab = null;
                        this.styleTabId = 'n';
                        let tab = el.closest('.tb_tab');
                        if (tab === null) {
                            tab = el.closest('.tb_expanded_opttions');
                            if (tab === null) {
                                tab = this.context.querySelector('#tb_options_styling');
                            }
                        } else {
                            this.styleTabId = tab.id.split('_').pop();
                        }
                        this.styleTab = tab;
                    } else {
                        this.isChanged = false;
                    }
                };
        let event,
                type = data.type,
                prop = data.prop,
                id = data.id;
        if (type === 'color') {
            event = 'themify_builder_color_picker_change';
        } else if (type === 'gradient') {
            event = 'themify_builder_gradient_change';
        } else {
            event = type === 'text' || type === 'range' || type === 'textarea' ? 'keyup' : 'change';
        }
        el.tfOn(event, function (e) {
            let cl = this.classList,
                    val,
                    is_select = this.tagName === 'SELECT',
                    is_radio = !is_select && this.type === 'radio';
            getTab(this);
            if (e.detail && e.detail.val) {
                val = e.detail.val;
            } else if (type === 'frame') {
                val = this.id;
            } else {
                val = this.value;
            }
            val = val !== und && val !== 'undefined' ? val.trim() : '';
            if (cl.contains('outline_color') || cl.contains('outline_width') || cl.contains('outline_style')) {
                self.outline(this, data);
                return;
            }
            if (cl.contains('tb_transform_field')) {
                self.transform(this, id);
                return;
            } else if ((type === 'color' && cl.contains('border_color')) || (is_select === true && cl.contains('border_style')) || (event === 'keyup' && (cl.contains('border_width') || cl.contains('tb_multi_field')))) {
                self.bindMultiFields(this);
                return;
            } else if (prop === 'frame-custom' || type === 'frame' || cl.contains('tb_frame') || ( cl.contains( 'toggle_switch' ) && this.closest( '.tb_field.tb_frame' ) ) ) {
                if (self.model.type !== 'module') {
                    self.addOrRemoveFrame(this);

                }
                return;
            } else if (cl.contains('tb_shadow_field')) {
                self.shadow(this, id);
                return;
            } else if (cl.contains('tb_filters_field')) {
                self.filters(this, id);
                return;
            }
            if (event === 'keyup') {
                if (val !== '') {
                    if (prop === 'column-rule-width') {
                        val += 'px';
                        const bid = id.replace('_width', '_style'),
                                border = self.context.querySelector('#' + bid);
                        if (border !== null) {
                            self.setData(bid, '', border.value);
                        }
                    }else {
                        const unit = self.context.querySelector('#' + id + '_unit');
                        val += unit!==null && unit.value ? unit.value : 'px';
                    }
                }
                self.setData(id, '', val);
                return;
            }
            if (data.isFontColor === true) {
                self.bindFontColorType(val, id, type);
                return;
            }
            if (is_select === true) {
                if (prop === 'font-weight') {
                    // load the font variant
                    const font = this.getAttribute('data-selected'),
                            wrap = self.styleTab.tfClass('tb_multi_fonts')[0]; // if the fontWeight has "italic" style, toggle the font_style option
                    if (font !== null && font !== '' && font !== 'default' && ThemifyConstructor.font_select.safe[font] === und) {
                        ThemifyConstructor.font_select.loadGoogleFonts(font + ':' + val);
                    }
                    if (wrap) {
                        let el;
                        if (val.indexOf('italic') !== -1) {
                            val = parseInt(val.replace('italic', '')).toString();
                            el = wrap.querySelector('[data-value="italic"]');
                        } else {
                            el = wrap.querySelector('[data-value="normal"]');
                        }
                        if (el.checked === false) {
                            el.parentNode.click();
                        }
                    }
                } else if (type === 'font_select') {
                    if (val !== '' && val !== 'default' && ThemifyConstructor.font_select.safe[val] === und) {
                        let weight = this.closest('.tb_tab').tfClass('font-weight-select')[0],
                                request;

                        request = val;
                        if (weight !== und) {
                            request += ':' + weight.value;
                        } else {
                            self.setLiveStyle('font-weight', '', data.selector);
                        }
                        ThemifyConstructor.font_select.loadGoogleFonts(request);
                    } else if (val === 'default') {
                        val = '';
                    }
                    if (val !== '') {
                        val = ThemifyStyles.parseFontName(val);
                    }
                } else if (cl.contains('tb_unit')) {
                    Themify.triggerEvent(self.context.querySelector('#' + id.replace('_unit', '')), 'keyup');
                    return;
                } else if (prop === 'background-mode') {
                    self.bindBackgroundMode(val, id);
                    return;
                } else if (prop === 'column-count' && val == 0) {
                    val = '';
                } else if (cl.contains('tb_position_field')) {
                    const pos = ['top', 'right', 'bottom', 'left'],
                            wrap = this.closest('.tb_input');

                    for (let i = pos.length - 1; i > -1; --i) {
                        let posVal = '';
                        if ('absolute' === val || 'fixed' === val) {
                            let selector = '#' + data.id + '_' + pos[i];
                            posVal = 'auto';
                            if (!wrap.querySelector(selector + '_auto input').checked) {
                                posVal = (wrap.querySelector(selector).value.trim() + wrap.querySelector(selector + '_unit').value.trim());
                            }
                        }
                        self.setLiveStyle(pos[i], posVal, data.selector);
                    }
                } else if (prop === 'display') {
                    if ('none' === val) {
                        return false;
                    } else {
                        self.setLiveStyle('width', ('inline-block' === val ? 'auto' : '100%'), data.selector);
                    }
                } else if (prop === 'vertical-align') {
                    if ('' !== val) {
                        let flexVal;
                        if ('top' === val) {
                            flexVal = 'flex-start';
                        } else if ('middle' === val) {
                            flexVal = 'center';
                        } else {
                            flexVal = 'flex-end';
                        }
                        self.setLiveStyle('align-self', flexVal, data.selector);
                    }
                }
            } else if (type === 'gallery' && self.model.type !== 'module') {
                self.bindBackgroundSlider(data);
                return;
            } else if (is_radio === true) {
                id = this.closest('.tb_lb_option').id;
                if (this.checked === false) {
                    val = '';
                }
                if (type === 'imageGradient' || data.is_background === true) {
                    self.bindBackgroundTypeRadio(val);
                    return;
                } else if (data.is_overlay === true) {
                    if (self.model.type !== 'module') {
                        self.overlayType(val);
                    }
                    return;
                }
            } else if (type === 'color' || type === 'gradient') {
                if (type === 'gradient') {
                    id = this.dataset.id;
                }

                if (data.is_overlay === true) {
                    if (self.model.type !== 'module') {
                        self.addOrRemoveComponentOverlay(type, id, val);
                    }
                    return;
                }
                if (type === 'color') {
                    let image = null;
                    //for modules
                    if (self.model.type === 'module' && data.colorId !== und && data.origId !== und) {
                        image = self.context.querySelector('#' + data.origId);
                        if (image !== null && image.closest('.tb_input').querySelector('input:checked').value !== 'image') {
                            image = null;
                        }
                    }//for rows/column
                    else if (self.model.type !== 'module' && self.styleTabId === 'h') {
                        image = self.styleTab.tfClass('tb_uploader_input')[0];
                    }
                    if (image && image.value.trim() === '') {
                        self.setLiveStyle('background-image', (val !== '' ? 'none' : ''), data.selector);
                    }
                }

            } else if (type === 'image' || type === 'video') {
                if (type === 'video') {
                    if (val.length > 0) {
                        if (self.model.type !== 'module') {
                            self.el.dataset.tbfullwidthvideo= val;
                            if (!self.el.dataset.mutevideo && self.context.querySelector('#background_video_options [value="mute"]').checked) {
                                self.el.dataset.mutevideo='mute';
                            } else {
                                self.el.removeAttribute('data-mutevideo');
                            }
                            Themify.reRun(self.el, true);
                        }
                    } else {
                        self.removeBgVideo();
                    }
                    return false;
                } else {
                    if (val) {
                        val = 'url(' + val + ')';
                    } else {
                        val = '';
                        if (data.colorId !== und && self.styleTabId === 'h') {
                            const color = self.context.querySelector('#' + data.colorId);
                            if (color !== null && color.value.trim() !== '') {
                                val = 'none';
                            }
                        }
                    }
                    const group = self.styleTab.tfClass('tb_image_options');
                    for (let i = group.length - 1; i > -1; --i) {
                        let opt = group[i].tfClass('tb_lb_option');
                        for (let j = opt.length - 1; j > -1; --j) {
                            Themify.triggerEvent(opt[j], 'change');
                        }
                    }
                }
            } else if (type === 'position_box') {
                self.position(val, id);
                return;
            } 
            else if (type === 'checkbox') {
                if (this.closest('#background_video_options') !== null) {
                    self.videoOptions(this, val);
                    return;
                } else if (('height' === prop && id.indexOf('_auto_height') !== -1) || ('width' === prop && id.indexOf('_auto_width') !== -1)) {
                    const mainID = 'height' === prop ? data.heightID : data.widthID;
                    if (this.checked) {
                        self.setData(mainID, prop, 'auto');
                    } else {
                        const mainValue = self.styleTab.querySelector('#' + mainID).value.trim();
                        if (mainValue !== '') {
                            self.setData(mainID, prop, mainValue + self.styleTab.querySelector('#' + mainID + '_unit').value);
                        } else {
                            self.setData(mainID, prop, '');
                        }
                    }
                    return;
                } else if (true === data.is_position) {
                    const selector = '#' + data.posId,
                            wrap = this.closest('.tb_input');
                    if (this.checked) {
                        val = 'auto';
                    } else {
                        val = wrap.querySelector(selector).value.trim();
                        val = '' !== val && !isNaN(val) ? val + wrap.querySelector(selector + '_unit').value : '';
                    }
                    self.setLiveStyle(data.prop, val, data.selector);
                    return;
                } else if ('background-image' === prop) {
                    if (!this.checked) {
                        val = false;
                    }
                    self.setLiveStyle(data.prop, val, data.selector);
                    return;
                }
            }
            self.setData(id, '', val);
        }, {passive: true});
    };

    ThemifyLiveStyling.prototype.getValue = function (id) {
        return this.module_rules[id] !== und ? this.module_rules[id] : false;

    };

    ThemifyLiveStyling.prototype.bindTabsSwitch = function (off) {

        if (off === true) {
            if (this.tabsHover) {
                Themify.off('tb_builder_tabsactive', this.tabsHover);
                this.tabsHover = null;
            }
            return;
        }
        if (!this.tabsHover) {
            this.tabsHover = (id, container) => {
                if (ThemifyConstructor.clicked === 'styling') {
                    let hover_items;
                    if (id.split('_').pop() !== 'h') {
                        this.el.classList.remove('tb_visual_hover');
                        hover_items = this.el.tfClass('tb_visual_hover');
                        for (let i = hover_items.length - 1; i > -1; --i) {
                            hover_items[i].classList.remove('tb_visual_hover');
                        }
                    } else {
                        if (this.model.type !== 'module') {
                            let radio = container.previousElementSibling.tfClass('background_type')[0];
                            if (radio) {
                                radio = radio.querySelector('input:checked').value;
                                container.classList.toggle('tb_disable_hover', (radio !== 'image' && radio !== 'gradient'));
                            }
                        }
                        setTimeout(() => {
                            hover_items = container.tfClass('tb_lb_option');
                            let selectors = [];
                            for (let i = hover_items.length - 1; i > -1; --i) {
                                let elId = hover_items[i].id,
                                        is_gradient = hover_items[i].classList.contains('themify-gradient');
                                if (is_gradient === true) {
                                    elId = hover_items[i].dataset.id;
                                }
                                if (this.module_rules[elId] !== und && (is_gradient || hover_items[i].offsetParent !== null)) {
                                    if (this.module_rules[elId].is_overlay !== und) {
                                        this.el.classList.add('tb_visual_hover');
                                    }
                                    let select = Array.isArray(this.module_rules[elId].selector) ? this.module_rules[elId].selector : [this.module_rules[elId].selector];
                                    for (let j = select.length - 1; j > -1; --j) {
                                        let k = select[j].split(':hover')[0];
                                        selectors[k] = 1;
                                    }
                                }
                            }
                            selectors = Object.keys(selectors);
                            if (selectors.length > 0) {
                                for (let i = selectors.length - 1; i > -1; --i) {
                                    hover_items = doc.querySelectorAll(this.prefix + selectors[i]);
                                    for (let j = hover_items.length - 1; j > -1; --j) {
                                        hover_items[j].classList.add('tb_visual_hover');
                                    }
                                }
                            }

                        }, 10);

                    }
                }
            };
        }

        Themify.on('tb_builder_tabsactive', this.tabsHover);
    };


    /**
     * Returns component's background cover element wrapped in jQuery.
     */
    ThemifyLiveStyling.prototype.getComponentBgOverlay = function (type) {
        if (!type) {
            type = this.model.type;
        }
        const parent = type === 'subrow' ? this.el.querySelector(':scope>.module_subrow') : this.el;
        return parent.querySelector(':scope>.builder_row_cover');
    };

    /**
     * Returns component's background slider element wrapped in jQuery.
     */
    ThemifyLiveStyling.prototype.getComponentBgSlider = function () {
        const type = this.model.isSubCol === true ? 'sub-col' : (this.model.type === 'colum' ? 'col' : this.model.type);
        return this.el.querySelectorAll(':scope>.' + type + '-slider');
    };

    /**
     * Removes background slider if there is any in component.
     */
    ThemifyLiveStyling.prototype.removeBgSlider = function () {
        let sliders = this.getComponentBgSlider();
        for (let i = sliders.length - 1; i > -1; --i) {
            sliders[i].remove();
        }
        sliders = this.el.querySelectorAll(':scope>.tb_backstretch');
        for (let i = sliders.length - 1; i > -1; --i) {
            sliders[i].remove();
        }
        this.el.style.position = this.el.style.background = this.el.style.zIndex = '';
    };

    /**
     * Removes background slider if there is any in component.
     */
    ThemifyLiveStyling.prototype.removeFrames = function () {
        const frame = this.el.querySelector(':scope>.tb_row_frame_wrap');
        if (frame !== null) {
            frame.remove();
        }
    };


    /**
     * Removes background video if there is any in component.
     */
    ThemifyLiveStyling.prototype.removeBgVideo = function () {
        this.el.removeAttribute('data-tbfullwidthvideo');
        const video = this.el.querySelector(':scope>.big-video-wrap');
        if (video !== null) {
            video.remove();
        }
    };



    function verticalResponsiveBars() {
        const items = topWindow.document.tfClass('tb_middle_bar'),
                resizeBarMousedownHandler = function (e) {
                    if (e.which === 1) {
                        e.stopPropagation();
                        let start_x = e.clientX,
                                timer,
                                iframe = topWindow.document.tfId('tb_iframe'),
                                bar = this.id === 'tb_right_bar' ? 'right' : 'left',
                                breakpoints = tbLocalScript.breakpoints,
                                max_width = api.ToolBar.el.offsetWidth,
                                start_with = iframe.offsetWidth,
                                tooltip = topWindow.document.tfClass('tb_vertical_change_tooltip')[0],
                                vertical_bars = topWindow.document.tfClass('tb_vertical_bars')[0];
                        iframe.style.transition = 'none';
                        if (tooltip) {
                            tooltip.remove();
                        }
                        tooltip = doc.createElement('div');
                        tooltip.className = 'tb_vertical_change_tooltip';
                        this.appendChild(tooltip);
                        vertical_bars.className += ' tb_resizing_start';
                        iframe.classList.add('tb_resizing_start');
                        const _move = e => {
                            e.stopPropagation();
                            if (timer) {
                                cancelAnimationFrame(timer);
                            }
                            timer = requestAnimationFrame(() => {
                                let diff = e.clientX - start_x;
                                diff *= 2;
                                if (bar === 'left') {
                                    diff = -diff;
                                }
                                let min_width = 320,
                                        breakpoint,
                                        w = (start_with + diff) < min_width ? min_width : (start_with + diff);

                                if (w <= breakpoints.mobile)
                                    breakpoint = 'mobile';
                                else if (w <= breakpoints.tablet[1])
                                    breakpoint = 'tablet';
                                else if (w <= breakpoints.tablet_landscape[1])
                                    breakpoint = 'tablet_landscape';
                                else {
                                    breakpoint = 'desktop';
                                    if (w > (max_width - 17)) {
                                        w = max_width;
                                    }
                                }
                                tooltip.textContent = iframe.style.width = w + 'px';
                                if (api.activeBreakPoint !== breakpoint) {
                                    api.ToolBar.switchToBreakpoint(w);
                                }
                            });
                        };

                        this.tfOn('lostpointercapture', function (e) {
                            e.stopPropagation();
                            if (timer) {
                                cancelAnimationFrame(timer);
                            }
                            this.tfOff('pointermove', _move, {passive: true});

                            tooltip.remove();
                            iframe.style.transition = '';
                            iframe.classList.remove('tb_resizing_start');
                            vertical_bars.classList.remove('tb_resizing_start');
                            doc.body.classList.remove('tb_start_animate', 'tb_start_change_mode');
                            api.Utils._onResize(true);
                            vertical_bars = tooltip = start_with = breakpoints = bar = start_x = max_width = timer = iframe = null;

                        }, {once: true, passive: true})
                        .tfOn('pointermove', _move, {passive: true});
                        this.setPointerCapture(e.pointerId);
                        doc.body.classList.add('tb_start_animate', 'tb_start_change_mode');
                    }
                };

        for (let i = items.length - 1; i > -1; --i) {
            items[i].tfOn('pointerdown', resizeBarMousedownHandler, {passive: true});
        }
    }

    api.EdgeDrag = {
        _onDrag: null,
        _mouseMove:null,
        _timers:new Map(),
        _coords:new Set(),
        init() {
            const builder = api.Builder.get().el,
                    checkbox = api.ToolBar.el.tfClass('padding_dragging_mode')[0],
                    cl = doc.body.classList;
            if (null === this._onDrag) {
                this._onDrag = e => {
                    this.drag(e);
                };
                this._mouseMove=e=>{
                    if (!doc.body.classList.contains('tb_start_animate') && api.ActionBar.disable === null && e.target.id!=='tb_small_toolbar_root') {
                        const x=e.clientX,
                            y=e.clientY;
                        requestAnimationFrame(()=>{
                            this.clearNegative(x,y);
                        });
                    }
                };
            }
            if (!localStorage.getItem('tb_disable_padding_dragging')) {
                builder.tfOn('pointerdown', this._onDrag, {passive: true})
                        .tfOn('pointermove',this._mouseMove,{passive:true});
                cl.remove('tb_disable_padding_dragging');
                checkbox.checked = true;
            } else {
                builder.tfOff('pointerdown', this._onDrag, {passive: true})
                .tfOn('pointermove',this._mouseMove,{passive:true});
                cl.add('tb_disable_padding_dragging');
                checkbox.checked = false;
            }
        },
        addEdgesOptions(item) {
            const el = item.closest('.tb_dragger');
            if (!el.tfClass('tb_dragger_lightbox')[0]) {
                const type = el.classList.contains('tb_dragger_margin') ? 'margin' : 'padding',
                        model = api.Registry.get(el.closest('[data-cid]').getAttribute('data-cid')),
                        elType = model.type,
                        hide_apply_all = type === 'margin' && (elType === 'column' || elType === 'row'),
                        units = ['%', 'em', 'px'],
                        applyTypes = hide_apply_all ? ['opposite'] : ['all', 'opposite'],
                        dir = el.classList.contains('tb_dragger_top') || el.classList.contains('tb_dragger_bottom') ? 's' : 'e',
                        id = hide_apply_all ? 'margin-top_opp_top' : 'checkbox_#id#_apply_all',
                        isAllChecked = this.getCurrentStyling(id, model, type) === '1',
                        u = el.dataset.u || 'px',
                        wrap = doc.createElement('div'),
                        apply = doc.createElement('ul'),
                        ul = doc.createElement('ul');
                wrap.className = 'tb_dragger_lightbox';
                ul.className = 'tb_dragger_units';
                apply.className = 'tb_dragger_types';
                for (let j = units.length - 1; j > -1; --j) {
                    let li = doc.createElement('li');
                    li.textContent = units[j];
                    if (units[j] === u) {
                        li.className = 'current';
                    }
                    ul.appendChild(li);
                }
                for (let j = applyTypes.length - 1; j > -1; --j) {
                    let li = doc.createElement('li'),
                            span = doc.createElement('span'),
                            isChecked = false;
                    li.className = 'tb_apply tf_box tf_rel tf_block tb_apply_' + applyTypes[j];
                    if (!hide_apply_all && applyTypes[j] === 'opposite') {
                        if (!isAllChecked) {
                            let checkId = '#id#_opp_';
                            checkId += dir === 's' ? 'top' : 'left';
                            isChecked = this.getCurrentStyling(checkId, model, type) == '1';
                        }
                    } else {
                        isChecked = isAllChecked;
                    }
                    if (isChecked) {
                        li.className += ' current';
                    }
                    li.appendChild(span);
                    apply.appendChild(li);
                }
                wrap.append(ul, apply);
                el.tfClass('tb_dragger_options')[0].appendChild(wrap);

            }
        },
        openLightBox(target) {
            if (target.hasAttribute('data-v') || target.getAttribute('data-v') === '') {
                const module = target.closest('[data-cid]');
                if (module) {
                    const model = api.Registry.get(module.dataset.cid);
                    model.edit('styling').then(lb => {
                        const origLabel = target.classList.contains('tb_dragger_padding') ? 'p' : 'm',
                                label = ThemifyConstructor.label[origLabel],
                                expands = lb.tfClass('tb_style_toggle');
                        for (let i = expands.length - 1; i > -1; --i) {
                            if (expands[i].textContent === label) {
                                if (expands[i].classList.contains('tb_closed')) {
                                    Themify.triggerEvent(expands[i], Themify.click);
                                }
                                setTimeout(() => {
                                    expands[i].closest('.tf_scrollbar').scrollTop = expands[i].offsetTop;
                                }, 10);
                                break;
                            }
                        }
                    })
                    .catch(e => {

                    });
                }
            }
        },
        addEdges(model) {
            const el = model.el,
                    slug = model.get('mod_name');
            if (model.isEmpty===true || slug === 'divider' || (slug === 'row' && el.classList.contains('tb-page-break'))) {
                return;
            }
            const types = ['padding', 'margin'],
                    edge = ['right', 'bottom', 'left', 'top'],
                    elType = model.type,
                    items = [],
                    len = edge.length;
            for (let i = types.length - 1; i > -1; --i) {

                let f = doc.createDocumentFragment(),
                        type = types[i],
                        childs = elType === 'module' && type === 'padding' ? el.tfClass('module')[0] : el,
                        len2,
                        hide_apply_all = (type === 'margin' && (slug === 'column' || slug === 'row')),
                        v,
                        u;
                if (elType === 'subrow' && type === 'padding') {
                    childs = el.tfClass('module_subrow')[0];
                }
                if (childs) {
                    childs = childs.children;
                    len2 = childs.length - 1;
                }
                if (hide_apply_all) {
                    if (this.getCurrentStyling('margin-top_opp_top', model, type) == '1') {
                        v = this.getCurrentStyling('margin-top', model, type);
                        u = this.getCurrentStyling('margin-top_unit', model, type) || 'px';
                    }
                } else if (this.getCurrentStyling('checkbox_#id#_apply_all', model, type) == '1') {
                    v = this.getCurrentStyling('#id#_top', model, type);
                    u = this.getCurrentStyling('#id#_top_unit', model, type) || 'px';
                }
                for (let j = len - 1; j > -1; --j) {
                    if (type === 'margin' && (slug === 'column' || slug === 'row') && (edge[j] === 'right' || edge[j] === 'left')) {
                        continue;
                    }

                    let ed,
                        unit,
                        u2 = u,
                        v2 = v;
                    if (childs) {
                        for (let k = len2; k > -1; --k) {
                            if (childs[k] && childs[k].classList.contains('tb_dragger_' + edge[j]) && childs[k].classList.contains('tb_dragger_' + type)) {
                                ed = childs[k];
                                break;
                            }
                        }
                    }
                    if (!ed) {
                        ed = doc.createElement('div');
                        unit = doc.createElement('span');
                        ed.className = 'tb_dragger tf_opacity tf_box tf_abs_t tf_h tb_dragger_' + edge[j] + ' tb_dragger_' + type;
                        unit.className = 'tb_dragger_value';
                        let edgeOptions = doc.createElement('div'),
                                arrow = doc.createElement('span');
                        arrow.className = 'tb_dragger_arrow tf_inline_b tf_vmiddle tf_box';
                        edgeOptions.className = 'tb_dragger_options tf_abs_c';
                        edgeOptions.tabIndex = -1;
                        edgeOptions.append(unit, arrow);
                        ed.appendChild(edgeOptions);
                        f.appendChild(ed);
                    } else {
                        unit = ed.tfClass('tb_dragger_value')[0];
                    }
                    if (!u2) {
                        let id = hide_apply_all ? '#id#-' + edge[j] : '#id#_' + edge[j];
                        v2 = this.getCurrentStyling(id, model, type);
                        u2 = this.getCurrentStyling(id + '_unit', model, type) || 'px';
                    }
                    if (v2 !== und && v2 !== null && v2 !== '') {
                        let old_u = ed.dataset.u || 'px';
                        if (old_u !== u2 || ed.dataset.v != v2) {
                            ed.dataset.u = u2;
                            ed.dataset.v = v2;
                            unit.textContent = v2 + u2;
                            if (type !== 'padding') {
                                items.push(ed);
                            }
                        }
                        if( type==='margin'){
                            if(!ed.classList.contains('tf_dragger_negative')){
                                let timer=this._timers.get(ed);
                                if(timer){
                                    clearTimeout(timer);
                                    this._timers.delete(ed);
                                    timer=null;
                                }
                                if(v2<0){
                                    timer=setTimeout(()=>{
                                        if(ed.isConnected  && !ed.style.willChange && !doc.body.classList.contains('tb_dragger_options_open') && ed.matches(':hover')){
                                            this._coords.add(ed);
                                            ed.classList.add('tf_dragger_negative');
                                        }
                                        this._timers.delete(ed);
                                        timer=null;
                                    },1200);
                                    this._timers.set(ed,timer);
                                }
                            }
                            else if(v2>=0){
                                ed.classList.remove('tf_dragger_negative');
                            }
                        }
                    }
                }
                if (type === 'margin' || (elType !== 'module' && elType !== 'subrow')) {
                    el.appendChild(f);
                } else {
                    const sel = elType === 'subrow' ? 'module_subrow' : 'module',
                            m = el.tfClass(sel)[0];
                    if (m) {
                        m.appendChild(f);
                    }
                }
                if (type !== 'padding') {
                    for (let i = items.length - 1; i > -1; --i) {
                        this.setValueByType(items[i], slug, items[i].dataset.v, items[i].dataset.u);
                    }
                }
                if (elType !== 'row') {
                    let parent = elType === 'module' || elType === 'subrow' ? 'column' : (el.classList.contains('sub_column') ? 'subrow' : 'row'),
                            parentItem = el.closest('.module_' + parent);
                    if (parent === 'subrow') {
                        parentItem = parentItem.parentNode;
                    } else if (elType === 'module') {
                        let dragger = el.querySelector('.tb_dragger_top.tb_dragger_padding');
                        if (dragger !== null) {
                            api.EdgeDrag.setModulePosition(dragger);
                        }
                    }
                    this.addEdges(api.Registry.get(parentItem.dataset.cid));
                }
            }
        },
        clearNegative(x,y){
            for(let ed of this._coords){
                if(ed.isConnected && ed.classList.contains('tf_dragger_negative')){
                    let v=ed.getBoundingClientRect();
                    ed.classList.toggle('tf_dragger_negative',(x>=v.left && x<=v.right && v.top<=y && v.bottom>=y));
                }
                else{
                    this._coords.delete(ed);
                }
            }
        },
        optionsClick(e) {
            e.stopPropagation();
            api.ActionBar.disable = true;
            this.addEdgesOptions(e.target.closest('.tb_dragger_options'));
            const target = e.target.nodeName === 'LI' ? e.target : e.target.parentNode;
            if (target.nodeName === 'LI') {
                const isUnit = !target.classList.contains('current') && target.parentNode.classList.contains('tb_dragger_units'),
                        isType = !isUnit && target.parentNode.classList.contains('tb_dragger_types');
                if (isUnit || isType) {
                    api.undoManager.start('style');
                    if (isUnit) {
                        this.changeUnit(target).then(() => {
                            api.undoManager.end('style');
                        });
                    } else {
                        this.changeApply(target).then(() => {
                            api.undoManager.end('style');
                        });
                    }

                }
            }
            api.ActionBar.disable = null;
        },
        changeUnit(el) {
            return new Promise(resolve => {

                const lightbox = el.closest('.tb_dragger_lightbox'),
                        edge = lightbox.closest('.tb_dragger'),
                        baseProp = edge.classList.contains('tb_dragger_padding') ? 'padding' : 'margin',
                        dir = edge.classList.contains('tb_dragger_top') || edge.classList.contains('tb_dragger_bottom') ? 's' : 'e',
                        u = el.textContent || 'px',
                        v = edge.dataset.v,
                        prevValue = edge.dataset.u || 'px',
                        baseEl = edge.parentNode,
                        currentSheet = ThemifyStyles.getSheet(api.activeBreakPoint),
                        cssRules = currentSheet.cssRules;

                let apply = edge.tfClass('tb_dragger_types')[0],
                        items = baseEl.children,
                        item = baseEl.closest('[data-cid]'),
                        index,
                        res = v !== '' ? this.convert(edge, prevValue, u, v) : '',
                        model = api.Registry.get(item.getAttribute('data-cid')),
                        elType = model.get('mod_name');
                if (apply) {
                    apply = apply.tfClass('current')[0];
                    if (apply) {
                        apply = apply.classList.contains('tb_apply_all') ? 'all' : 'opposite';
                    }
                }
                const selector = ThemifyStyles.getBaseSelector(elType, model.get('element_id'));
                index = api.Utils.findCssRule(cssRules, selector);
                doc.body.classList.add('tb_edge_drag_start');
                for (let i = items.length - 1; i > -1; --i) {
                    let cl = items[i].classList;
                    if (cl.contains('tb_dragger_' + baseProp)) {
                        let itemDir = 'left';
                        if (cl.contains('tb_dragger_bottom')) {
                            itemDir = 'bottom';
                        } else if (cl.contains('tb_dragger_right')) {
                            itemDir = 'right';
                        } else if (cl.contains('tb_dragger_top')) {
                            itemDir = 'top';
                        }
                        if (items[i] !== edge && (!apply || (apply === 'opposite' && ((dir === 's' && (itemDir === 'left' || itemDir === 'right')) || (dir === 'e' && (itemDir === 'top' || itemDir === 'bottom')))))) {
                            continue;
                        }
                        let units = items[i].tfClass('tb_dragger_units')[0],
                                prop = baseProp + '-' + itemDir,
                                value = res + u,
                                id = (baseProp === 'margin' && (elType === 'column' || elType === 'row')) ? ('#id#-' + itemDir) : ('#id#_' + itemDir);
                        if (index === false || !cssRules[index]) {
                            index = cssRules.length;
                            currentSheet.insertRule(selector + '{' + prop + ':' + value + ';}', index);
                        } else {
                            cssRules[index].style.setProperty(prop, value);
                        }
                        if (units) {
                            units = units.children;
                            for (let j = units.length - 1; j > -1; --j) {
                                units[j].classList.toggle('current', units[j].textContent === u);
                            }
                        }
                        if (u !== 'em' && res !== '') {
                            res = Math.round(res);
                        }
                        if (baseProp !== 'padding') {
                            this.setValueByType(items[i], elType, res, u);
                        }
                        items[i].tfClass('tb_dragger_value')[0].textContent = res === '' ? '' : value;
                        items[i].dataset.v = res;
                        items[i].dataset.u = u;
                        cl.add('tb_dragger_dragged');
                        this.setData(model, this.getFieldId(id, model, baseProp), res, u);
                    }
                }
                setTimeout(() => {
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].classList.remove('tb_dragger_dragged');
                    }
                    doc.body.classList.remove('tb_edge_drag_start');
                    items = null;
                    resolve();
                }, 500);
            });
        },
        changeApply(el) {
            return new Promise(resolve => {
                const edge = el.closest('.tb_dragger'),
                        base = edge.parentNode,
                        type = edge.classList.contains('tb_dragger_padding') ? 'padding' : 'margin',
                        dir = edge.classList.contains('tb_dragger_top') || edge.classList.contains('tb_dragger_bottom') ? 's' : 'e',
                        item = base.closest('[data-cid]'),
                        model = api.Registry.get(item.getAttribute('data-cid')),
                        remove = el.classList.contains('current') ? '' : '1',
                        next = el.nextSibling ? el.nextElementSibling : el.previousElementSibling;

                let id1 = 'margin-top_opp_top',
                    id2;
                if (!(type === 'margin' && (model.type === 'column' || model.type === 'row'))) {
                    id2 = 'checkbox_#id#_apply_all';
                    id1 = '#id#_opp_';
                    id1 += dir === 's' ? 'top' : 'left';
                    if (el.classList.contains('tb_apply_all')) {
                        const tmp = id1;
                        id1 = id2;
                        id2 = tmp;
                    }
                }
                if (next) {
                    next.classList.remove('current');
                }
                el.classList.toggle('current', remove);
                this.setData(model, this.getFieldId(id1, model, type), remove);
                if (id2) {
                    this.setData(model, this.getFieldId(id2, model, type), '');
                }
                this.changeUnit(edge.tfClass('tb_dragger_units')[0].tfClass('current')[0]).then(() => {
                    this.onChange();
                    resolve();
                });
            });
        },
        convert(el, prevU, u, v) {
            if (!v) {
                return 0;
            }
            if (!prevU) {
                prevU = 'px';
            }
            if (!u) {
                u = 'px';
            }
            if (prevU === u) {
                return v;
            }
            let res,
                    p = el.parentNode;
            if (p.classList.contains('active_module')) {
                const sel = p.classList.contains('active_subrow') ? 'module_subrow' : 'module';
                p = p.tfClass(sel)[0];
            }
            const emSize = u === 'em' || prevU === 'em' ? parseFloat(getComputedStyle(p).fontSize) : null,
                    pWidth = u === '%' || prevU === '%' ? p.parentNode.offsetWidth : null;
            if (prevU === 'px') {
                if (u === 'em') {
                    res = +(parseFloat(v / emSize)).toFixed(2);
                } else if (u === '%') {
                    res = parseFloat((v * 100) / pWidth);
                }
            } else if (prevU === '%') {
                res = parseFloat((v * pWidth) / 100);
                res = u === 'em' ? (+(parseFloat(res / emSize)).toFixed(2)) : parseFloat(res);
            } else {
                res = parseFloat(v * emSize);
                if (u === '%') {
                    res = parseFloat((res * 100) / pWidth);
                }
                res = parseFloat(res);
            }
            return Number(res.toFixed(2));
        },
        setValueByType(el, slug, v, u) {
            let prop = 'margin';
            if (!u) {
                u = 'px';
            }
            const cl = el.classList;
            for (let i = cl.length - 1; i > -1; --i) {
                if (cl[i] === 'tb_dragger_top' || cl[i] === 'tb_dragger_bottom' || cl[i] === 'tb_dragger_left' || cl[i] === 'tb_dragger_right') {
                    prop += '-' + cl[i].replace('tb_dragger_', '');
                    break;
                }
            }
            if (u !== 'px' && v !== '') {
                v = this.convert(el, u, 'px', v);
                u = 'px';
            }
            if (v === und || v === null) {
                v = '';
            }
            const p = prop === 'margin-top' || prop === 'margin-bottom' ? 'height' : 'width';
            el.style[p] = v === '' ? '' : ((v > 0 ? v : (-v)) + u);
            if (slug === 'row' || slug === 'column') {
                el.style[prop] = v === '' ? '' : ((-v) + u);
            }
        },
        getFieldId(id, model, type) {
            const slug = model.type,
                    options = ThemifyStyles.getStyleOptions(model.get('mod_name'));
            if (!(type === 'margin' && (slug === 'row' || slug === 'column')) && (options[type + '_top'] === und || options[type + '_top'].type !== type)) {//the id in general tab can be padding or p, margin or m
                type = type[0];
            }
            return id.replace('#id#', type);
        },
        getCurrentStyling(id, model, type) {
            const st = model.get('styling');
            id = this.getFieldId(id, model, type);
            if (api.activeModel !== null && api.activeModel.id === model.id) {
                let field = api.LightBox.el.querySelector('#' + id);
                if (field !== null) {
                    if (field.classList.contains('tb_checkbox_wrap')) {
                        field = field.tfClass('tb_checkbox')[0];
                        if (field) {
                            return field.checked ? field.value : false;
                        }
                    } else {
                        return field.value;
                    }
                }
            }
            let v = ThemifyStyles.getStyleVal(id, st, api.activeBreakPoint);
            //for columns it can be "value1,value2" where "value1" is value for v5, "value2" is for v7
            if (v !== und && v !== '' && model.type === 'column' && v.toString().indexOf(',') !== -1 && (id.indexOf('padding') === 0 || id.indexOf('margin'))) {
                v = v.trim().split(',');
                if (v[1] !== und && v[1] !== '') {
                    v = v[1];
                } else {
                    v = v[0];
                }
                v = v.trim();
            }
            return v;
        },
        setData(model, id, v, u) {
            if (v && u && u !== 'em') {
                v = Number(v);
            }
            if (api.activeModel !== null && model.id === api.activeModel.id) {
                let field = api.LightBox.el.querySelector('#' + id);
                if (field !== null) {
                    if (field.classList.contains('tb_checkbox_wrap')) {
                        field = field.tfClass('tb_checkbox')[0];
                        if (field) {
                            field.checked = !!v;
                        }
                    } else {
                        field.value = v;
                        field = api.LightBox.el.querySelector('#' + id + '_unit');
                        if (field) {
                            field.value = u;
                        }
                    }
                    if (field) {
                        Themify.triggerEvent(field, 'change');
                    }
                    return;
                } else {
                    const values=ThemifyConstructor.values;
                    if (api.activeBreakPoint === 'desktop') {
                        values[id] = v;
                        if (u) {
                            values[id + '_unit'] = u;
                        }
                    } else {
                        if (!values['breakpoint_' + api.activeBreakPoint]) {
                            values['breakpoint_' + api.activeBreakPoint] = {};
                        }
                        values['breakpoint_' + api.activeBreakPoint][id] = v;
                        if (u) {
                            values['breakpoint_' + api.activeBreakPoint][id + '_unit'] = u;
                        }
                    }
                }
            }
            const st = api.Helper.cloneObject(model.get('mod_settings'));
            if (api.activeBreakPoint !== 'desktop') {
                if (!st['breakpoint_' + api.activeBreakPoint]) {
                    st['breakpoint_' + api.activeBreakPoint] = {};
                }
                st['breakpoint_' + api.activeBreakPoint][id] = v;
                if (u) {
                    st['breakpoint_' + api.activeBreakPoint][id + '_unit'] = u;
                }
            } else {
                if (!v) {
                    delete st[id];
                } else {
                    st[id] = v;
                }
                if (u) {
                    if (u === 'px') {
                        delete st[id + '_unit'];
                    } else {
                        st[id + '_unit'] = u;
                    }
                }
            }
            model.set('mod_settings', st);
        },
        onChange() {
            setTimeout(() => {
                api.Utils._onResize(true);
            }, 1500);
        },
        setModulePosition(dragger) {
            let expand = doc.tfId('tb_component_bar');
            if (expand !== null) {
                const dragVal = dragger.tfClass('tb_dragger_value')[0];
                expand.style.marginTop = '';
                if (dragVal && dragVal.firstChild) {
                    const drOffset = dragVal.getBoundingClientRect(),
                            expandOffset = expand.getBoundingClientRect();
                    if (expandOffset.bottom >= drOffset.top) {
                        let topPos=expand.offsetTop || 0;
						topPos=topPos<0?(-1)*topPos:0;
                        expand.style.marginTop = (dragger.offsetHeight / 2) + drOffset.height+topPos + 'px';
                    }
                }
            }
        },
        drag(e) {
            if (e.which === 1) {
                const el = e.target;
                if (el.closest('.tb_dragger_options')) {
                    const self = this,
                            t = e.target;
                    doc.tfOn('pointerup', function (e) {
                        if (t === e.target) {
                            if (e.target.classList.contains('tb_dragger_arrow')) {
                                this.body.classList.add('tb_dragger_options_open');
                                let dragger = t.closest('.tb_dragger');
                                const _blur = e => {
                                    if (e.type === 'pointerleave' || !e.target.closest('.tb_dragger_options')) {

                                        this.tfOff(Themify.click, _blur, {passive: true});
                                        topWindow.document.tfOff(Themify.click, _blur, {passive: true});
                                        dragger.tfOff('pointerleave', _blur, {once: true, passive: true});
                                        if (dragger.classList.contains('tb_dragger_padding') && dragger.classList.contains('tb_dragger_top')) {
                                            self.setModulePosition(dragger);
                                        }
                                        this.body.classList.remove('tb_dragger_options_open');
                                        const lb = dragger.tfClass('tb_dragger_lightbox')[0];
                                        if (lb) {
                                            lb.remove();
                                        }
                                        dragger = null;
                                    }
                                };
                                dragger.tfOn('pointerleave', _blur, {once: true, passive: true});
                                this.tfOn(Themify.click, _blur, {passive: true});
                                topWindow.document.tfOn(Themify.click, _blur, {passive: true});
                            }
                            self.optionsClick(e);
                        }
                    }, {once: true,passive:true});
                    return;
                }
                if (!el.classList.contains('tb_dragger')) {
                    return;
                }
                e.stopImmediatePropagation();
                const self = this,
                        baseEl = el.closest('[data-cid]'),
                        model = api.Registry.get(baseEl.getAttribute('data-cid'));

                if (model) {
                    let items = [],
                            module,
                            timer,
                            prevY = e.clientX,
                            prevX = e.clientY,
                            current,
                            apply,
                            isSame,
                            timestamp = 0,
                            speedX = 0,
                            speedY = 0;

                    const elType = model.type,
                            baseProp = el.classList.contains('tb_dragger_padding') ? 'padding' : 'margin',
                            dir = el.classList.contains('tb_dragger_top') || el.classList.contains('tb_dragger_bottom') ? 's' : 'e',
                            type = dir === 's' ? (el.classList.contains('tb_dragger_top') ? 'top' : 'bottom') : (el.classList.contains('tb_dragger_left') ? 'left' : 'right'),
                            u = el.dataset.u || 'px',
                            getSpeed = (x, y) => {
                        let now = Date.now(),
                                dt = now - timestamp,
                                distanceX = Math.abs(x - speedX),
                                distanceY = Math.abs(y - speedY),
                                distance = Math.max(distanceX, distanceY),
                                speed = dt !== 0 && u === 'px' ? ((Math.round(distance / dt * 1000)) % 4) : 0;
                        if (speed <= 0) {
                            speed = u === 'px' || u === '%' ? 1 : .1;
                        } else if (u === 'em') {
                            speed /= 10;
                        }
                        speedX = x;
                        speedY = y;
                        timestamp = now;

                        return speed;
                    },
                            _start = e => {
                                e.stopImmediatePropagation();
                                doc.body.classList.add('tb_start_animate', 'tb_edge_drag_start');
                                topWindow.document.body.classList.add('tb_start_animate', 'tb_edge_drag_start');
                                api.SmallPanel.hide();
                                api.ActionBar.clear();
                                api.ActionBar.disable = true;
                                const lb = el.tfClass('tb_dragger_lightbox')[0];
                                if (lb) {
                                    lb.remove();
                                }
                                self.addEdgesOptions(el);
                                current = parseFloat(el.dataset.v) || 0;
                                current = u !== 'em' ? parseInt(current) : Number(current.toFixed(2));
                                apply = el.tfClass('tb_dragger_types')[0];
                                if (apply) {
                                    apply = apply.tfClass('current')[0];
                                    if (apply) {
                                        apply = apply.classList.contains('tb_apply_all') ? 'all' : 'opposite';
                                    }
                                }
                                isSame = api.activeModel !== null && api.activeModel.id === model.id;
                                if (isSame === false) {
                                    api.undoManager.start('style');
                                }

                                module = elType === 'module' ? baseEl.tfClass('module')[0] : (elType === 'subrow' ? baseEl.tfClass('module_subrow')[0] : baseEl);

                                const tmp = el.parentNode.children;
                                for (let i = tmp.length - 1; i > -1; --i) {
                                    let cl = tmp[i].classList;
                                    if (cl.contains('tb_dragger_' + baseProp)) {
                                        let itemDir = 'left';
                                        if (cl.contains('tb_dragger_bottom')) {
                                            itemDir = 'bottom';
                                        } else if (cl.contains('tb_dragger_right')) {
                                            itemDir = 'right';
                                        } else if (cl.contains('tb_dragger_top')) {
                                            itemDir = 'top';
                                        }
                                        if (type !== itemDir && (!apply || (apply === 'opposite' && ((dir === 's' && (itemDir === 'left' || itemDir === 'right')) || (dir === 'e' && (itemDir === 'top' || itemDir === 'bottom')))))) {
                                            continue;
                                        }
                                        tmp[i].classList.add('tb_dragger_dragged');
                                        items.push({el: tmp[i], prop: (baseProp + '-' + itemDir), text: tmp[i].tfClass('tb_dragger_value')[0]});
                                    }
                                }
                                baseEl.classList.add('tb_element_clicked');
                            },
                            _move = e => {
                                e.stopImmediatePropagation();
                                if (timer) {
                                    cancelAnimationFrame(timer);
                                }
                                timer = requestAnimationFrame(() => {
                                    const x = e.clientX,
                                            y = e.clientY,
                                            koef = getSpeed(x, y);
                                    if (dir === 'e') {
                                        if (x !== prevX) {
                                            if (x > prevX) {
                                                if (type === 'left') {
                                                    current += koef;
                                                } else {
                                                    current -= koef;
                                                }
                                            } else {
                                                if (type === 'left') {
                                                    current -= koef;
                                                } else {
                                                    current += koef;
                                                }
                                            }
                                        }
                                    } else if (y !== prevY) {
                                        if (y > prevY) {
                                            current += koef;
                                        } else {
                                            current -= koef;
                                        }
                                    }
                                    prevX = x;
                                    prevY = y;
                                    if (current < 0 && baseProp === 'padding') {
                                        current = 0;
                                    } else if (current % 1 !== 0) {
                                        current = parseFloat(current.toFixed(1));
                                    }
                                    const v = current + u;
                                    for (let i = items.length - 1; i > -1; --i) {
                                        let prop = items[i].prop,
                                                item = items[i].el,
                                                text = items[i].text;
                                        module.style[prop] = v;
                                        if (baseProp === 'margin') {
                                            let p = item.classList.contains('tb_dragger_top') || item.classList.contains('tb_dragger_bottom') ? 'height' : 'width',
                                                    v2 = current,
                                                    u2 = u;
                                            if (u2 !== 'px') {
                                                v2 = self.convert(item, u2, 'px', v2);
                                                u2 = 'px';
                                            }
                                            if (elType === 'row' || elType === 'column') {
                                                item.style[prop] = v === '' ? '' : ((-v2) + u2);
                                            }
                                            item.style[p] = current < 0 ? ((-v2) + u2) : (v2 + u2);
                                        }
                                        text.textContent = current === 0 ? '' : v;
                                    }
                                });
                            };
                    baseEl.style.willChange = baseProp;
                    el.style.willChange = baseProp === 'margin' ? ((type === 'top' || type === 'bottom') ? 'height' : 'width') : baseProp;
                    el.tfOn('lostpointercapture', function (e) {
                        if (timer) {
                            cancelAnimationFrame(timer);
                        }
                        this.style.willChange = baseEl.style.willChange = '';
                        this.tfOff('pointermove', _start, {passive: true, once: true})
                        .tfOff('pointermove', _move, {passive: true});
                        Themify.trigger('tbDisableInline');
                        if (doc.body.classList.contains('tb_edge_drag_start')) {
                            e.stopImmediatePropagation();
                            doc.body.classList.remove('tb_start_animate', 'tb_edge_drag_start');
                            topWindow.document.body.classList.remove('tb_start_animate', 'tb_edge_drag_start');
                            Themify.trigger('tbresizeImageEditor');
                            requestAnimationFrame(() => {
                                baseEl.classList.remove('tb_element_clicked');
                                const selector = ThemifyStyles.getBaseSelector(model.get('mod_name'), model.get('element_id')),
                                        currentSheet = ThemifyStyles.getSheet(api.activeBreakPoint),
                                        cssRules = currentSheet.cssRules,
                                        index = api.Utils.findCssRule(cssRules, selector);
                                for (let i = items.length - 1; i > -1; --i) {
                                    let item = items[i].el,
                                            prop = items[i].prop,
                                            v = module.style[prop],
                                            edge = prop.replace(baseProp + '-', ''),
                                            id = (baseProp === 'margin' && (elType === 'column' || elType === 'row')) ? ('#id#-' + edge) : ('#id#_' + edge),
                                            lb = item.tfClass('tb_dragger_lightbox')[0];
                                    if (current === 0 && baseProp === 'margin') {
                                        item.style.setProperty(prop, '');
                                        item.style.width = item.style.height = '';
                                    }
                                    if (lb) {
                                        lb.remove();
                                    }
                                    if (index === false || !cssRules[index]) {
                                        currentSheet.insertRule(selector + '{' + prop + ':' + v + ';}', cssRules.length);
                                    } else {
                                        cssRules[index].style.setProperty(prop, v);
                                    }
                                    module.style[prop] = '';

                                    item.dataset.v = current;
                                    item.dataset.u = u;
                                    item.classList.remove('tb_dragger_dragged');
                                    self.setData(model, self.getFieldId(id, model, baseProp), current, u);
                                }

                                if (baseProp === 'padding' && type === 'top' && elType === 'module') {
                                    self.setModulePosition(items[0].el);
                                }
                                self.onChange();
                                api.Utils.runJs(baseEl, null, true);
                                if (isSame === false) {
                                    api.undoManager.end('style');
                                }
                                api.ActionBar.disable = apply = module = timer = isSame = items = current = prevY = prevX = null;
                                self.addEdges(model);
                            });
                        }
                        else{
                            self.addEdges(model);
                        }
                    }, {once: true, passive: true})
                    .tfOn('pointermove', _start, {passive: true, once: true})
                    .tfOn('pointermove', _move, {passive: true});
                    el.setPointerCapture(e.pointerId);
                }
            }
        }
    };
    api.createStyleInstance = () => {
        return new ThemifyLiveStyling();
    };


})(Themify, window, window.top, document, tb_app,undefined);