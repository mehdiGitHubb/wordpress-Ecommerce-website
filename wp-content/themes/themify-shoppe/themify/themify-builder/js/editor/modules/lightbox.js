((api, Themify, topWindow, doc,und) => {
    'use strict';
    
    api.LightBox =  {
        storageKey:api.mode === 'visual' ? 'themify_builder_lightbox_frontend_pos_size' : 'themify_builder_lightbox_backend_pos_size',
        size:null,
        isStandalone:false,
        el:null,
        init() {
            topWindow.document.body.appendChild(doc.tfId('tmpl-builder_lightbox').content);
            this.el = topWindow.document.tfId('tb_lightbox_parent');
            this.firstRun();
            api.Forms.initValidators();
        },
        firstRun() {
            Themify.on('tb_opened_lightbox', () => {
                this.el.tfClass('tb_close_lightbox')[0].tfOn(Themify.click, () => {
                    this.close();
                }, {
                    passive: true
                });
                this.el.tfClass('builder_cancel_docked_mode')[0].tfOn(Themify.click, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    api.Dock.close(false);
                    this.updateStorage();
                    api.MainPanel.updateStorage();
                });
                if(this.isStandalone===false){
                    this.updateStorage();
                    this.setupLightboxSizeClass();
                }
                setTimeout(()=>{
                    this.draggable();
                    this.resize();
                },250);
            }, true);
            

            if (!themifyBuilder.disableShortcuts) {
                const shortcutListener = e => {
                    const active=topWindow.document.activeElement;
                    if(active.tagName !== 'INPUT'  &&  active.tagName !== 'TEXTAREA' && !topWindow.document.fullscreenElement && active.contentEditable!=='true'){
                        if (e.code === 'Escape' && api.activeModel) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.save().then(()=>{
                                this.close();
                            });
                        } 
                        else if ('KeyS' === e.code && (true === e.ctrlKey || true === e.metaKey) ) {
                            // Ctrl + s | Cmd + s - Save Builder
                            e.preventDefault();
                            e.stopPropagation();
                            if(api.activeModel){
                                this.save();
                            }
                            else{
                                api.Builder.get().save();
                            }
                        }
                    }
                };
                doc.tfOn('keydown', shortcutListener);
                if (api.mode === 'visual') {
                    topWindow.document.tfOn('keydown', shortcutListener);
                }
            }
        },
        open(options, model) {
            return new Promise(resolve => {
                const callback = response => {
                    this.el.classList.add('tf_hide');

                    const lightboxContainer = this.el.querySelector('#tb_lightbox_container'),
                        action = this.el.tfClass('tb_lightbox_actions_wrap')[0],
                        save = doc.createElement('button');
            
                    if (typeof response === 'string') {
                        lightboxContainer.innerHTML = response;
                    } else {
                        while (lightboxContainer.firstChild !== null) {
                            lightboxContainer.lastChild.remove();
                        }
                        lightboxContainer.appendChild(response);
                    }
                    while (action.firstChild !== null) {
                        action.lastChild.remove();
                    }
                    if(options.save!==false){
                        save.className = 'builder_button builder_save_button';
                        save.title = themifyBuilder.i18n.label.ctr_save;
                        save.textContent = themifyBuilder.i18n.label.done;
                        if (api.activeModel) {
                            if(api.isDocked){
                                this.el.classList.add('tb_lightbox_small');
                            }
                            if (api.mode === 'visual') {
                                if (api.GS.activeGS !== null) {
                                    api.liveStylingInstance = api.createStyleInstance();
                                    api.liveStylingInstance.init(null, true);
                                } 
                                else {
                                    if (!api.liveStylingInstance) {
                                        api.liveStylingInstance = api.createStyleInstance();
                                    }
                                    api.liveStylingInstance.init();
                                }
                            }
                            const _saveClicked = e => {
                                    e.stopImmediatePropagation();
                                    this.save().then(()=>{
                                        if(api.isGSPage!==true){
                                            save.tfOff(e.type, _saveClicked,{passive:true});
                                            topSave.tfOff(e.type, _saveClicked,{passive:true});
                                        }
                                    }).catch(e=>{
                                        console.log(e);
                                    });
                                },
                                topSave = doc.createElement('a'),
                                li = doc.createElement('li'),
                                span = doc.createElement('span');
                            li.className = 'tb_top_save_btn';
                            topSave.className = 'tb_tooltip';
                            topSave.href='javascript:;';
                            span.textContent = themifyBuilder.i18n.label.done;
                            topSave.append(api.Helper.getIcon('ti-check'), span);
                            li.appendChild(topSave);
                            this.el.tfClass('tb_options_tab')[0].appendChild(li);
                            save.tfOn(Themify.click, _saveClicked,{passive:true});
                            topSave.tfOn(Themify.click, _saveClicked,{passive:true});
                          
                            api.undoManager.start('saveLightbox',api.activeModel);
                        }
                        action.appendChild(save);
                    }
                    if ('html' === options.loadMethod && options.contructor!==true) {
                        const tabs = lightboxContainer.tfClass('tb_tab_nav');
                        for (let i = tabs.length - 1; i > -1; --i) {
                            let a = tabs[i].tfTag('a');
                            for (let j = a.length - 1; j > -1; --j) {
                                a[j].tfOn(Themify.click, ThemifyConstructor.switchTabs);
                            }
                        }
                    }
                    this.el.classList.remove('tf_hide');
                    lightboxContainer.style.scrollBehavior = 'auto';
                    lightboxContainer.scrollTop =0;
                    lightboxContainer.style.scrollBehavior = '';
                    this.responsiveTabs();
                    Themify.trigger('tb_opened_lightbox');
                    api.Spinner.showLoader('spinhide');
                    api.SmallPanel.hide();
                    resolve(this.el);
                };
                
                this.close();
                if(model){
                    api.activeModel=model;
                }
                try{
                    
                if (options.loadMethod === 'html') {
                    if (options.contructor === true) {
                        callback(ThemifyConstructor.run(options.data));
                    } else {
                        callback(options.data);
                    }
                } 
                else {
                    callback(ThemifyConstructor.run(api.FormTemplates.getItem(options)));
                }
                }
                catch(e){
                    console.log(e);
                }
            });
        },
        close() {
            if(!this.el.classList.contains('tf_hide') && api.isGSPage!==true){
                Themify.trigger('themify_builder_lightbox_before_close', this.el);
                this.el.classList.add('tf_hide');

                this.cleanLightBoxContent();
                api.undoManager.updateUndoBtns();
                api.Utils.removeViewPortClass(this.el);
                Themify.trigger('themify_builder_lightbox_close', this.el);
                if (api.mode === 'visual') {
                    // Trigger parent iframe
                    topWindow.Themify.trigger('themify_builder_lightbox_close', this.el);
                }
                if(api.activeModel!==null){
                    if (api.mode === 'visual') {
                        api.liveStylingInstance.clear();
                    }
                    if(api.activeModel.is_new===true){
                        api.activeModel.destroy(true);
                    }
                    else if(!api.activeModel.is_Saved&& api.undoManager.has('saveLightbox')){
                        api.activeModel.restore();
                    }
                    if(!api.activeModel.is_Saved){
                        api.undoManager.clear('saveLightbox');
                    }
                    api.activeModel = null;
                }
            }
            return this;
        },
        setStandAlone(left, top) {
            Themify.on('tb_opened_lightbox', () => {
                this.isStandalone=true;
                topWindow.document.body.classList.add('tb_standalone_lightbox');
                this.el.style.width = this.el.style.height = '';
                const box = api.ToolBar.el.getBoundingClientRect(),
                    computed = getComputedStyle(this.el),
                    w = parseInt(computed.width),
                    h = parseInt(computed.height),
                    topW = topWindow.innerWidth - 10,
                    topH = topWindow.innerHeight + 10;

                if (top < box.bottom) {
                    top = box.bottom;
                } else if ((top + h) > topH) {
                    top -= top + h - topH;
                }
                if (left < 0) {
                    left = 0;
                } else if ((left + w) > topW) {
                    left -= left + w - topW;
                }
                this.el.style.transform = 'translate(' + left + 'px,' + top + 'px)';
                this.el.style.width = w + 'px';
                this.el.style.height = h + 'px';
                this.setupLightboxSizeClass(w);
                Themify.on('themify_builder_lightbox_close', lb => {
                    topWindow.document.body.classList.remove('tb_standalone_lightbox');
                    lb.style.transform = lb.style.width = lb.style.height = '';
                    this.isStandalone=false;
                    this.updateStorage();
                    this.setupLightboxSizeClass(this.getStorage().w);
                }, true);
            }, true);
            return this;
        },
        cleanLightBoxContent() {
            const items = this.el.querySelectorAll('#tb_lightbox_container,.tb_options_tab,.tb_lightbox_actions_wrap,.tb_action_breadcrumb');
            for (let i = items.length - 1; i > -1; --i) {
                while (items[i].firstChild !== null) {
                    items[i].lastChild.remove();
                }
            }
            return this;
        },
        async save() {
            const model = api.activeModel;
            if(!this.el.classList.contains('tf_hide')){
                if (model!== null) {
                    if (api.isGSPage!==true && !api.Forms.isValidate(this.el.querySelector('#tb_options_setting'))) {
                        throw 'invalid';
                    }
                    ThemifyConstructor.setStylingValues(api.activeBreakPoint);//save current breakpoint style tab
                    let oldSettings=model.get('mod_settings'),
                        settings=api.Helper.cloneObject(ThemifyConstructor.values);//styles tab
                    const options = api.Forms.serialize('tb_options_setting', true);
                    for (let i in options) {
                        settings[i] = options[i];
                    }
                    if (model.type !== 'column') {
                        const animation = api.Forms.serialize('tb_options_animation', true),
                            visible = api.Forms.serialize('tb_options_visibility', true);
                        for (let i in animation) {
                            settings[i] = animation[i];
                        }
                        for (let i in visible) {
                            settings[i] = visible[i];
                        }
                        if (api.mode === 'visual') {
                            const hide=visible.visibility_all === 'hide_all' || visible.visibility_desktop === 'hide' || visible.visibility_tablet === 'hide' || visible.visibility_tablet_landscape === 'hide' || visible.visibility_mobile === 'hide';
                            model.el.classList.toggle('tb_visibility_hidden',hide);
                        }
                        if (model.type === 'module') {
                            api.Builder.get().removeLayoutButton();
                        }
                    }
                    if (api.ActionBar.id === model.id) {
                        api.ActionBar.clear();
                    }

                    //check diff
                    settings=api.Helper.clear( settings, false );
                    oldSettings=api.Helper.clear(oldSettings, false );
                    const hasChange=api.Helper.compareObject(oldSettings,settings);

                    if(hasChange===true){
                        Themify.trigger('themify_builder_save_component',[settings,oldSettings]);

                        if (api.mode === 'visual') {
                            // Trigger parent iframe
                            topWindow.Themify.trigger('themify_builder_save_component',[settings,oldSettings]);
                        }
                        model.set('mod_settings',settings);
                    }
                    model.is_new=false;
                    model.is_Saved=true;
                    this.close();
                    if(api.isGSPage!==true && hasChange===true){
                        api.undoManager.end('saveLightbox');
                    }
                    else{
                        api.undoManager.clear('saveLightbox');
                    }
                    delete model.is_new;
                    delete model.is_Saved;
                }
                else if(topWindow.document.body.classList.contains('tb_standalone_lightbox')){
                    await Themify.trigger('tb_save_lb');
                }
                if(api.isGSPage===true){
                    await TF_Notification.showHide('done',themifyBuilder.globalStyleData.save_text,2000);
                }
            }
        },
        resize() {
            const self = this,
                resizeHandler = this.el.tfClass('tb_resizable');

            for (let i = resizeHandler.length - 1; i > -1; --i) {
                resizeHandler[i].tfOn('pointerdown', function(e) {
                    if (e.which === 1) {
                        e.stopImmediatePropagation();
                        let owner = this.ownerDocument,
                            el = self.el,
                            timer;
                        el.style.willChange='transform,width,height';
                        const minWidth = 350,
                            maxWidth = 880,
                            maxHeight = owner.documentElement.clientHeight * .9,
                            minHeight = parseInt(getComputedStyle(el).getPropertyValue('min-height')),
                            axis = this.dataset.axis,
                            startH = parseInt(el.offsetHeight, 10),
                            startW = parseInt(el.offsetWidth, 10),
                            resizeX =e.clientX,
                            resizeY = e.clientY,
                            _resize = e => {
                                e.stopImmediatePropagation();
                                timer=requestAnimationFrame(() => {
                                    let w;
                                    const clientX = e.clientX,
                                        clientY = e.clientY,
                                        matrix = new DOMMatrix(getComputedStyle(el).transform);
                                    if (axis === 'w') {
                                        w = resizeX + startW - clientX;
                                        if (w > maxWidth) {
                                            w = maxWidth;
                                        }
                                        if (w >= minWidth && w <= maxWidth) {
                                            matrix.m41 += parseInt(el.style.width) - w;
                                            el.style.width = w + 'px';
                                            self.setupLightboxSizeClass(w);
                                        }
                                    } else {
                                        const h = axis === '-y' || axis === 'ne' || axis === 'nw' ? (resizeY + startH - clientY) : (startH + clientY - resizeY);
                                        w = axis === 'sw' || axis === 'nw' ? (resizeX + startW - clientX) : (startW + clientX - resizeX);
                                        if (w > maxWidth) {
                                            w = maxWidth;
                                        }
                                        if ((axis === 'se' || axis === 'x' || axis === 'sw' || axis === 'nw' || axis === 'ne') && w >= minWidth && w <= maxWidth) {
                                            if (axis === 'sw' || axis === 'nw') {
                                                matrix.m41 += parseInt(el.style.width) - w;
                                            }
                                            el.style.width = w + 'px';
                                            self.setupLightboxSizeClass(w);
                                        }
                                        if ((axis === 'se' || axis === 'y' || axis === '-y' || axis === 'sw' || axis === 'nw' || axis === 'ne') && h >= minHeight && h <= maxHeight) {
                                            if (axis === '-y' || axis === 'nw' || axis === 'ne') {
                                                matrix.m42 += parseInt(el.style.height) - h;
                                            }
                                            el.style.height = h + 'px';
                                        }
                                    }
                                    el.style.transform = 'translate(' + matrix.m41 + 'px,' + matrix.m42 + 'px)';

                                    Themify.trigger('tb_resize_lightbox');
                                });
                            },
                            _stop = function(e) {
                                e.stopImmediatePropagation();
                                if (timer) {
                                    cancelAnimationFrame(timer);
                                }
                                this.tfOff('pointermove', _resize, {
                                    passive: true
                                });
                                el.style.willChange='';
                                self.updateStorage();
                                owner.body.classList.remove('tb_start_animate');
                                owner = el =timer= null;
                            };
                        this.tfOn('pointermove', _resize, {
                            passive: true
                        })
                        .tfOn('lostpointercapture', _stop, {
                            passive: true,
                            once: true
                        })
                        .setPointerCapture(e.pointerId);
                        owner.body.classList.add('tb_start_animate');
                    }

                }, {
                    passive: true
                });
            }
        },
        draggable() {
            const self = this,
                dragHandler = this.el.querySelectorAll('.tb_lightbox_top_bar,.tb_action_breadcrumb');
            for (let i = dragHandler.length - 1; i > -1; --i) {
                dragHandler[i].tfOn('pointerdown', function(e) {
                    if (e.which === 1) {
                        const targetCl=e.target.classList;
                        if (!targetCl.contains('tb_lightbox_top_bar') && !targetCl.contains('tb_action_breadcrumb')) {
                            return;
                        }
                        e.stopImmediatePropagation();
                        let timer,
                            el = self.el,
                            owner = this.ownerDocument;
                        el.style.willChange='transform';
                        const _x =  e.clientX,
                            _y = e.clientY,
                            box = el.getBoundingClientRect(),
                            dragX = box.left - _x,
                            dragY = box.top - _y,
                            width = box.width,
                            draggableCallback =e => {
                                e.stopImmediatePropagation();
                                timer = requestAnimationFrame(() => {
                                    const x = e.clientX,
                                        y = e.clientY,
                                        clientX = dragX + x,
                                        clientY = dragY + y;
                                    el.style.transform = 'translate(' + clientX + 'px,' + clientY + 'px)';
                                    Themify.trigger('tb_panel_drag', [clientX, width]);
                                });
                            },
                            startDrag = e=>{
                                e.stopImmediatePropagation();
                                Themify.trigger('tb_panel_drag_start');
                                if(this.isStandalone===false){
                                    this.setupLightboxSizeClass();
                                }
                            };
                        owner.body.classList.add('tb_start_animate', 'tb_drag_lightbox');
                        api.ToolBar.el.classList.add('tb_start_animate', 'tb_drag_lightbox');
                        api.MainPanel.el.classList.add('tb_start_animate', 'tb_drag_lightbox');
                        this.tfOn('lostpointercapture', function(e) {
                            e.stopImmediatePropagation();
                            if (timer) {
                                cancelAnimationFrame(timer);
                            }
                            this.tfOff('pointermove', startDrag, {passive: true,once: true})
                            .tfOff('pointermove', draggableCallback, {passive: true});
                            el.style.willChange='';
                            Themify.trigger('tb_panel_drag_end');
                            if(self.isStandalone===false){
                                self.updateStorage();
                                self.setupLightboxSizeClass();
                            }
                            owner.body.classList.remove('tb_start_animate', 'tb_drag_lightbox');
                            api.ToolBar.el.classList.remove('tb_start_animate', 'tb_drag_lightbox');
                            api.MainPanel.el.classList.remove('tb_start_animate', 'tb_drag_lightbox');
                            timer = el = owner = null;
                        }, {
                            passive: true,
                            once: true
                        })
                        .tfOn('pointermove', startDrag, {passive: true,once: true})
                        .tfOn('pointermove', draggableCallback, {passive: true})
                        .setPointerCapture(e.pointerId);
                    }
                }, {
                    passive: true
                });
            }
        },
        responsiveTabs() {
                 let tabs = null, 
                    ul=null, 
                    tabsWidth=0,
                    label = null,
                    callback =  ()=> {
                        const finsih=()=>{
                            if(ul===null){
                                ul=this.el.querySelector('.tb_styling_tab_nav ul');
                                if(ul!==null){
                                    const li=ul.lastElementChild;
                                    tabsWidth=li.offsetLeft+li.offsetWidth;
                                }
                            }
                            if(ul!==null && tabsWidth!==0){
                                if(api.isDocked){
                                    ul.style.display='none';
                                }
                                else{
                                    ul.style.flexDirection ='row';
                                }
                                const parentW=ul.parentNode.offsetWidth;
                                if (parentW <= tabsWidth || api.isDocked) {
                                    ul.style.display='none';
                                    const current=ul.tfClass('current')[0];
                                    if (label === null) {
                                        label = doc.createElement('span');
                                        label.className = 'tb_ui_dropdown_label';
                                        label.tabIndex = '-1';
                                        ul.before(label);
                                    }
                                    if(current){
                                        label.textContent = current.textContent;
                                    }
                                    setTimeout(()=>{//avoid flick
                                        ul.style.display='';
                                    },100);
                                } 
                                else if (label !== null) {
                                    label.remove();
                                    label=null;
                                }
                                ul.style.flexDirection ='';
                                return true;
                            }
                            else if(api.isDocked){
                                return false;
                            }
                        };
                        if(api.isDocked){//avoud flick
                            if(!finsih()){
                                setTimeout(finsih, 0);
                            }
                        }
                        else{
                            setTimeout(finsih, 0);
                        }
                    };
                callback();
                Themify.on('tb_builder_tabsactive',callback)
                        .on('tb_resize_lightbox',callback)
                        .on('themify_builder_lightbox_close',()=>{
                            Themify.off('tb_builder_tabsactive',callback).off('tb_resize_lightbox',callback);
                            tabs = ul = tabsWidth = label = callback=null;
                        },true);
        },
        setupLightboxSizeClass(w) {
            if (!w) {
                if (api.isDocked) {
                    w = parseInt(getComputedStyle(this.el).width);
                } else {
                    w = this.getStorage().width || parseInt(this.el.offsetWidth);
                }
            }
            const cl = this.el.classList;
            cl.toggle('larger-lightbox', w > 750);
            cl.toggle('tb_lightbox_small', w < 540);
        },
        getStorage() {
            if (this.size === null) {
                let storage = localStorage.getItem(this.storageKey);
                storage = storage ? JSON.parse(storage) : {};
                const _default = {
                    top: 100,
                    left: Math.max(0, ((topWindow.innerWidth / 2) - 300)),
                    width: 600,
                    height: 500
                };
                this.size = Object.assign(_default, storage);
            }
            return this.size;
        },
        updateStorage() {
            if(this.isStandalone===false){
                const tr = this.el.style.transform,
                    matrix = tr ? (new DOMMatrix(tr)) : null,
                    box = this.el.tfClass('tb_lightbox_top_bar')[0].getBoundingClientRect(),
                    wH = topWindow.innerHeight - box.height,
                    wW = topWindow.innerWidth,
                    storage = this.getStorage();
                let obj = {
                    width: this.el.style.width,
                    height: this.el.style.height
                };
                if (matrix) {
                    obj.top = matrix.m42;
                    obj.left = matrix.m41;
                }
                if (obj.height <= 0) {
                    delete obj.height;
                } else {
                    obj.height = parseInt(obj.height);
                }
                if (obj.width <= 0) {
                    delete obj.width;
                } else {
                    obj.width = parseInt(obj.width);
                }
                obj = Object.assign({}, storage, obj);
                if (obj.left < 0 || (obj.left+box.width) > wW) {
                    obj.left = (obj.left < 0 ? 0 : (wW - box.width));
                }
                if (obj.top < 0 || obj.top > wH) {
                    obj.top = (obj.top < 0 ? 0 : wH);
                }

                this.el.style.transform = 'translate(' + obj.left + 'px,' + obj.top + 'px)';
                this.el.style.width = obj.width + 'px';
                this.el.style.height = obj.height + 'px';
                if (!api.isDocked && storage !== obj  && Object.entries(obj).toString() !== Object.entries(storage).toString()) {
                    this.size = null;
                    localStorage.setItem(this.storageKey, JSON.stringify(obj));
                }
                return obj;
            }
        }
    };
    api.LiteLightBox =  {
        el:null,
        open(fragment) {
        const modal = doc.createElement('form'),
            close = doc.createElement('span');
            
            if(!this.el){
                const  root = doc.tfId('tb_lite_lightbox_root'),
                    fr = root.firstElementChild,
                    toolBarRoot=api.ToolBar.el.getRootNode(),
                    baseCss=toolBarRoot.querySelector('#tf_base');
                if (fr) { // shadowroot="open" isn't support
                    root.attachShadow({
                        mode: fr.getAttribute('shadowroot')
                    }).appendChild(fr.content);
                    fr.remove();
                }
                root.shadowRoot.prepend(baseCss.cloneNode(true));
                this.el = root.shadowRoot.tfId('wrapper');
                topWindow.document.body.appendChild(root);
                
                this.el.tfOn(Themify.click, e => {
                    if (this.el===e.target) {
                        this.close(e);
                    }
                });
            }
            
            modal.className = 'content tf_abs_c tf_textc tf_box';
            close.tfOn(Themify.click, e => {
                this.close(e);
            }, {
                once: true
            })
            .className = 'tf_close';
            
            modal.append(fragment, close);
            this.el.appendChild(modal);
            this.el.getRootNode().host.classList.remove('tf_hide');
        },
        close(e) {
            if (this.el) {
                if (e) {
                    e.stopPropagation();
                    e.preventDefault();
                }
                api.Registry.trigger(this.el, 'close');
            }
        },
        create(options) {
            const fr = doc.createDocumentFragment();
            
            for (let k in options) {
                if (k === 'buttons') {
                    let btnWrap = doc.createElement('div');
                    btnWrap.className = 'btns';
                    for (let btnKey in options[k]) {
                        let btn = doc.createElement('button');
                        btn.className='tf_inline_b tf_textc';
                        btn.dataset.type=btnKey;
                        btn.tfOn(Themify.click, e => {
                            this.buttonClick(e);
                        })
                        .innerText = options[k][btnKey];
                        btnWrap.appendChild(btn);
                    }
                    fr.appendChild(btnWrap);
                } 
                else if (k === 'msg') {
                    let msg = doc.createElement('div');
                    msg.className = 'msg';
                    msg.innerHTML = options[k];
                    fr.appendChild(msg);
                } 
                else if (k === 'input') {
                    let input = doc.createElement('input');
                    input.className = options[k].class+' tf_w';
                    input.value=options[k].value || '';
                    input.tfOn('keydown', e => {
                        this.keyPress(e);
                    }, {
                        passive: true
                    })
                    .type = options[k].type;
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                    fr.appendChild(input);
                }
            }
            return fr;
        },
        confirm(options) {
            if (!options.buttons) {
                options.buttons = {
                    no: ThemifyConstructor.label.no,
                    yes: ThemifyConstructor.label.y
                };
            }

            this.open(this.create(options));
            return (new Promise(resolve => {
                    api.Registry.on(this.el, 'confirm', type => {
                            let inputValue=this.el.querySelector('.content').tfTag('input')[0];
                            if(inputValue){
                                inputValue=inputValue.value || '';
                                resolve([type,inputValue]);
                            }
                            else{
                                resolve(type);
                            }
                        })
                        .on(this.el, 'close', () => {
                            resolve(null);
                        });
                })
                .finally(() => {
                    api.Registry.off(this.el, 'confirm').off(this.el, 'close').remove(this.el);
                    this.el.getRootNode().host.classList.add('tf_hide');
                    this.el.innerHTML='';
                }));
        },
        alert(message) {
            return this.confirm({
                msg: message,
                buttons: {
                    yes: ThemifyConstructor.label.ok
                }
            });
        },
        prompt(message,value) {
            return this.confirm({
                msg: message,
                input: {
                    type: 'text',
                    class: 'prompt_input',
                    value:value
                },
                buttons: {
                    no: ThemifyConstructor.label.cancel,
                    yes: ThemifyConstructor.label.ok
                }
            });
        },
        buttonClick(e) {
            e.preventDefault();
            e.stopPropagation();
            const type = e.currentTarget.dataset.type;
            if (type === 'cancel') {
                this.close();
            } else {
                api.Registry.trigger(this.el, 'confirm', type);
            }
        },
        keyPress(e) {
            if (e.code === 'Enter') { // on enter
                api.Registry.trigger(this.el, 'confirm','yes', e.currentTarget.value.trim());
            }
        }
    };
    
    
    api.Forms = {
        validators:new Map(),
        parseSettings(item, repeat) {
            const cl = item.classList,
                option_id = repeat ? item.dataset.inputId: item.id;
            if(!option_id){
                return false;
            }
            if (!cl.contains('tb_row_js_wrapper')) {
                let p = item.closest('.tb_field');
                if (p !== null && !p.classList.contains('_tb_hide_binding') && !(p.style.display === 'none' && p.className.indexOf('tb_group_element_') !== -1)) {
                    p = p.parentNode;
                    if (p.classList.contains('tb_multi_fields') && p.parentNode.classList.contains('_tb_hide_binding')) {
                        return false;
                    }
                }
            }
            let value = '';
            if (cl.contains('tb_lb_wp_editor')) {
                if (typeof tinyMCE !== 'undefined') {
                    const tid = item.id,
                        tiny = tinyMCE.get(tid);
                    value = tiny !== null ? (tiny.hidden === false ? tiny.getContent() : switchEditors.wpautop(tinymce.DOM.get(tid).value)) : item.value;
                } else {
                    value = item.value;
                }
            } 
            else if (cl.contains('tb_checkbox_wrap')) {
                const cselected = [],
                    chekboxes = item.tfClass('tb_checkbox'),
                    isSwitch = cl.contains('tb_switcher');
                for (let i = 0, len = chekboxes.length; i < len; ++i) {
                    if ((isSwitch === true || chekboxes[i].checked === true) && chekboxes[i].value !== '') {
                        cselected.push(chekboxes[i].value);
                    }
                }
                value = cselected.length > 0 ? cselected.join('|') : isSwitch ? '' : false;
            } 
            else if (cl.contains('themify-layout-icon')) {
                value = item.tfClass('selected')[0];
                value = value !== und ? value.id : '';
            } 
            else if (cl.contains('tb_search_input')) {
                value = item.dataset.value;

                let parent = item.closest('.tb_input'),
                    multiple_cat = parent.tfClass('query_category_multiple')[0];
                if (multiple_cat) {
                    multiple_cat = multiple_cat === und ? '' : multiple_cat.value.trim();
                    if (multiple_cat !== '') {
                        value = multiple_cat + '|' + (multiple_cat.indexOf(',') !== -1 ? 'multiple' : 'single');
                    } else {
                        value += '|single';
                    }
                }

            } 
            else if (cl.contains('tb_radio_wrap')) {
                const radios = item.tfTag('input');
                let input = null;
                for (let i = radios.length - 1; i > -1; --i) {
                    if (radios[i].checked === true) {
                        input = radios[i];
                        break;
                    }
                }
                if (input !== null && (api.activeBreakPoint === 'desktop' || !input.classList.contains('tb_responsive_disable'))) {
                    value = input.value;
                }
            } 
            else if (cl.contains('tb_search_container')) {
                value = item.previousElementSibling.dataset.value;
            } 
            else if (cl.contains('tb_row_js_wrapper')) {
                value = [];
                const repeats = item.tfClass('tb_repeatable_field_content');
                for (let i = 0, len = repeats.length; i < len; ++i) {
                    let childs = repeats[i].tfClass('tb_lb_option_child');
                    value[i] = {};
                    for (let j = 0, clen = childs.length; j < clen; ++j) {
                        let v = this.parseSettings(childs[j], true);
                        if (v && v.id) {
                            value[i][v.id] = v.v;
                        }
                    }
                }
            } 
            else if (cl.contains('module-widget-form-container')) {
                value = this.themifySerializeObject(item);
            }
            else if (cl.contains('tb_widget_select')) {
                value = item.tfClass('selected')[0];
                value = value !== und ? value.dataset.value : '';
            } 
            else if (cl.contains('tb_sort_fields_parent')) {
                const childs = item.children;
                value = [];
                for (let i = 0, len = childs.length; i < len; ++i) {
                    let type = childs[i].dataset.type;
                    if (type) {
                        let wrap = childs[i].tfClass('tb_sort_field_dropdown')[0],
                            v = {
                                type: type,
                                id: childs[i].dataset.id
                            };
                        if (wrap !== und) {
                            v.val = {};
                            let items = wrap.tfClass('tb_lb_sort_child');
                            for (let j = items.length - 1; j > -1; --j) {
                                let v2 = this.parseSettings(items[j], true);
                                if (v2 && v2.id) {
                                    v.val[v2.id] = v2.v;
                                }
                            }
                        } else {
                            let hidden = childs[i].tfTag('input')[0],
                                temp = hidden.value;
                            if (temp !== '') {
                                v.val = JSON.parse(temp);
                            }
                        }
                        value.push(v);
                    }
                }

                if (value.length === 0) {
                    value = '';
                }
            } 
            else if (cl.contains('tb_accordion_fields')) {
                const childs = item.children;
                value = {};
                for (let i = 0, len = childs.length; i < len; ++i) {
                    let id = childs[i].dataset.id;
                    if (id) {
                        let hidden = childs[i].tfTag('input')[0],
                            wrap = childs[i].tfClass('tb_accordion_fields_options')[0],
                            v = {};
                        if (wrap !== und) {
                            v.val = this.serialize(wrap, null, true);
                        } else {
                            let temp = hidden.value;
                            if (temp !== '') {
                                v.val = JSON.parse(temp);
                            }
                        }
                        value[id] = v;
                    }
                }
            }
            else if (cl.contains('tb_toggleable_fields')) {
                const childs = item.children;
                value = {};
                for (let i = 0, len = childs.length; i < len; ++i) {
                    let id = childs[i].dataset.id;
                    if (id) {
                        let hidden = childs[i].tfTag('input')[0],
                            wrap = childs[i].tfClass('tb_toggleable_fields_options')[0],
                            v = {
                                on: childs[i].tfClass('tb_switcher')[0].tfClass('toggle_switch')[0].value
                            };
                        if (wrap !== und) {
                            v.val = this.serialize(wrap, null, true);
                        } else {
                            let temp = hidden.value;
                            if (temp !== '') {
                                v.val = JSON.parse(temp);
                            }
                        }
                        value[id] = v;
                    }
                }
            } else if ( item.nodeName === 'SELECT' && item.multiple ) {
				value = [];
				for ( let selected=item.selectedOptions,i = 0,len=selected.length; i<len; ++i ) {
					value.push( item.selectedOptions[ i ].value );
				}
			} else {
                value = item.value;
                if (window.tbpDynamic !== und && option_id === tbpDynamic.field_name) {
                    if (value === '') {
                        return false;
                    }
                    if (typeof value === 'string') {
                        value = JSON.parse(value);
                    }
                } 
                else if (option_id === api.GS.key && api.activeBreakPoint !== 'desktop') {
                    return false;
                } 
                else if (value !== '') {
                    
                    if (option_id === 'builder_content') {
                        if (typeof value === 'string') {
                            value = JSON.parse(value);
                        }
                    } 
                    else {
                        if (typeof value === 'string' && value.indexOf(':')!==-1 && value.indexOf('{')!==-1) {
                            try{
                                let v=JSON.parse(value);
                                value=v;
                            }
                            catch(e){
                                
                            }
                        }
                        let opacity = item.dataset.opacity;
                        if (opacity !== null && opacity !== '') {
                            opacity = parseFloat(parseFloat(Number(opacity).toFixed(2)).toString());
                            if (opacity < .99) {
                                value += '_' + opacity;
                            }
                        }
                    }
                }
            }
            if (value === und || value === null) {
                value = '';
            }

            return {
                id: option_id,
                v: value
            };
        },
        serialize(id, empty, repeat) {
            const result = {},
                el = typeof id === 'object' ? id : api.LightBox.el.querySelector('#'+id);
            repeat = repeat || false;
            if (el !== null) {
                const options = el.tfClass((repeat ? 'tb_lb_option_child' : 'tb_lb_option'));
                for (let i = options.length - 1; i > -1; --i) {
                    let v = this.parseSettings(options[i], repeat);
                    if (v !== false && v.id && (empty === true || v.v !== '')) {
                        result[v.id] = v.v;
                    }
                }
            }
            return result;
        },
        initValidators(){
            this.registerValidator('email', item => {
                const v=typeof item==='string'?item:item.value.split(','),
                    pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                for (let i = v.length - 1; i > -1; --i) {
                    if (!pattern.test(v[i])) {
                        return false;
                    }
                }
                return true;
            });

            this.registerValidator('custom_css_id', item => {
                let count=0,
                v=item.value.trim();
                if(v){
                    v=v.replace(/[^a-zA-Z0-9\-\_]/g, '');
                    while(1){
                        if(isNaN(v[0])){
                            break
                        }
                        v = v.substring(1);
                    }
                    if(v){
                        const items = doc.querySelectorAll('#'+v);
                        if (items.length>1) {
                            for(let i=items.length-1;i>-1;--i){
                                if(items[i].closest('.module_row')!==null){
                                    ++count;
                                }
                            }
                        }
                    }
                }
                item.value=v;
                return count<=1;
            });
            
            this.registerValidator('custom_css', item => {
                let v=item.value;
                if(v){
                    v=v.replace(/\s\s+/g, ' ').split(' ');
                    for(let i=v.length-1;i>-1;--i){
                        v[i]=v[i].replace(/[^a-zA-Z0-9\s\-\_]/g, '');
                        if(v[i][0]!==''){
                            while(1){
                                if(isNaN(v[i][0])){
                                    break
                                }
                                v[i] = v[i].substring(1);
                            }
                        }
                    }
                    v=v.join(' ');
                }
                item.value=v;
                return true;
            });
            this.registerValidator('not_empty', item => {
                return item.value.toString().trim()!=='';
            });
        },
        registerValidator(type, fn){
            this.validators.set(type,fn);
        },
        getValidator(type){
            return this.validators.get(type) || this.validators.get('not_empty');
        },
        isValidate(form) {
            const validate = form.tfClass('tb_must_validate'),
                len = validate.length,
                saveBtn=api.LightBox.el.tfClass('builder_save_button')[0],
                checkValidate = (rule, item) => {
                    const validator = this.getValidator(rule);
                    return validator(item);
                };
            if (len === 0) {
                return true;
            }
            let is_valid = true;
            for (let i = len - 1; i > -1; --i) {
                let item = validate[i].tfClass('tb_lb_option')[0],
                    check=checkValidate(validate[i].dataset.validation, item);
                if (check!==true) {
                    if (!item.classList.contains('tb_field_error')) {
                        let el = doc.createElement('span'),
                            after = item.tagName === 'SELECT' ? item.parentNode : item;
                        el.className = 'tb_field_error_msg';
                        el.textContent = check===false?validate[i].dataset.errorMsg:check;
                        item.classList.add('tb_field_error');
                        after.parentNode.insertBefore(el, after.nextSibling);
                    }
                    is_valid = false;
                }
                else {
                    item.classList.remove('tb_field_error');
                    let er = validate[i].tfClass('tb_field_error_msg');
                    for (let j = er.length - 1; j > -1; --j) {
                        er[j].remove();
                    }
                }
            }
            if (is_valid === false) {
                const tab = api.LightBox.el.querySelector('a[data-id="' + form.id + '"]');
                if (!tab.parentNode.classList.contains('current')) {
                    Themify.triggerEvent(tab,Themify.click);
                }
                TF_Notification.showHide('error',themifyBuilder.i18n.lightBoxRequiredFields);
            }
            return is_valid;
        },
        themifySerializeObject(el){
            const o = {},
                items=el.querySelectorAll('input,select,textarea');
            for(let i=items.length-1;i>-1;--i){
                let tag = items[i].tagName,
                    type=items[i].type,
                    value=items[i].value,
                    name=items[i].name?items[i].name:items[i].id;
                if(name && type!=='button' && type!=='submit' && value !== ''){
                    if(items[i].classList.contains('wp-editor-area') && typeof tinyMCE !== 'undefined'){
                        let tiny = tinyMCE.get(items[i].id);
                        if (tiny) {
                            value= tiny.getContent().trim();
                        }
                    }
                    else if (type === 'radio' || type==='checkbox' || tag === 'select') {
                        if (tag === 'select') {
                            if(items[i].hasAttribute('multiple')){
                                let selected=[];
                                for(let j=0,options=items[i].children,len=options.length;j<len;++j){
                                    if(options[j].selected){
                                        selected.push(options[j].value);
                                    }
                                }
                                value=selected;
                            }
                        }
                        else if (!items[i].checked) {
                            continue;
                        }
                    }
                    if(value!==''){
                        if(type==='checkbox'){
                            if(o[name]===und){
                                o[name]=[];
                            } 
                            o[name].push(value);
                        }
                        else{
                            o[name]= value;
                        }
                    }
                }
            }
            return o;
        }
    };
    
    api.LightBox.init();

})(tb_app, Themify, window.top, document,undefined);