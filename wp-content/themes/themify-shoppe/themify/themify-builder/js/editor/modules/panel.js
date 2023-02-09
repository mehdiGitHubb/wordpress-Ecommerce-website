((api, doc, Themify, topWindow,und) => {
    'use strict';
   
    api.MainPanel = {
        el : null,
        size : null,
        isClosed : false,
        storageKey : 'tb_module_panel',
        initialize() {
            const containers = {},
                root = doc.tfId('tb_main_panel_root'),
                fr = root.firstElementChild,
                fragment=doc.createDocumentFragment();
                
             fragment.append(api.ToolBar.getBaseCss(), api.ToolBar.el.getRootNode().querySelector('#module_combine_style').cloneNode(true));
            if (fr) { // shadowroot="open" isn't support
                root.attachShadow({
                    mode: fr.getAttribute('shadowroot')
                }).appendChild(fr.content);
                fr.remove();
            }
            root.shadowRoot.prepend(fragment);
            this.el = root.shadowRoot.tfId('main_panel');
            let j = 1,
                favs=themifyBuilder.favorite,
                modules=themifyBuilder.modules,
                arr=Object.keys(modules).sort();
            for (let i=0,len=arr.length-1;i<len;++i) {
                let slug=arr[i],
                    module = doc.createElement('div'),
                    favorite = doc.createElement('span'),
                    name = doc.createElement('span'),
                    add = doc.createElement('button'),
                    icon = modules[slug].icon,
                    isFavorited=favs!==und && favs.indexOf(slug)!==-1;
                module.className = 'tb_module tb-module-' + slug;
                module.dataset.categories = modules[slug].category;
                if (isFavorited===true) {
                    module.className += ' favorited';
                }
                favorite.className = 'tb_favorite tb_disable_sorting';
                name.className = 'module_name';
                name.textContent = modules[slug].name;
                add.type = 'button';
                add.className = 'tf_plus_icon add_module_btn tb_disable_sorting tf_rel';
                add.dataset.type = 'module';
                add.title = themifyBuilder.i18n.add_module;
                module.dataset.slug = slug;
                module.dataset.index = j++;
                module.draggable = true;
                favorite.appendChild(api.Helper.getIcon('ti-star'));
                if (icon) {
                    module.appendChild(api.Helper.getIcon('ti-' + icon));
                }
                module.append(favorite, name, add);
                let categories = isFavorited===true ? ['favorite'] : modules[slug].category;
                for (let k = 0, len = categories.length; k < len; ++k) {
                    if (containers[categories[k]] === und) {
                        containers[categories[k]] = doc.createDocumentFragment();
                    }
                    containers[categories[k]].appendChild(module.cloneNode(true));
                }
            }

            let categories = this.el.tfClass('panel_category');
            for (let i = categories.length - 1; i > -1; --i) {
                let c = categories[i].dataset.category;
                if (c) {
                    if (und !== containers[c]) {
                        categories[i].appendChild(containers[c]);
                    } else {
                        categories[i].parentNode.style.display = 'none';
                    }
                }
            }
            if (api.mode === 'visual') {
                topWindow.document.body.appendChild(root);
            }
        },
        init(){
            this.updateStorage();
            if(api.isDocked){
                api.Dock.setDocked(false);
            }
            else if (localStorage.getItem('tb_panel_closed') === 'true') {
                this.closeFloat();
            }

            this.draggable();
            this.initClick();
            this.initSearch();
            api.jsModuleLoaded().then(()=>{
                setTimeout(()=>{
                    api.Drag(this.el);
                },1000);
            });
            this.el.getRootNode().host.classList.remove('tf_hide');
        },
        tabs(elm) {
            const target = elm.dataset.target;
            if (target) {
                const p = elm.closest('ul'),
                    parent = elm.closest('.panel'),
                    hideTabs = parent.tfClass(elm.dataset.hide),
                    showTabs = parent.tfClass(target),
                    notFound = parent.tfClass('tb_no_content')[0],
                    search = parent.tfClass('panel_search')[0],
                    current = elm.closest('li'),
                    nav = current.parentNode,
                    menu = nav.children,
                    dropdownLabel = nav.parentNode.tfClass('dropdown_label')[0];
                for (let i = hideTabs.length - 1; i > -1; --i) {
                    hideTabs[i].style.display = 'none';
                }
                for (let i = showTabs.length - 1; i > -1; --i) {
                    showTabs[i].style.display = '';
                    showTabs[i].classList.remove('tf_hide');
                }
                if (notFound) {
                    notFound.classList.toggle('tf_hide', showTabs.length > 0);
                }
                for (let i = menu.length - 1; i > -1; --i) {
                    menu[i].classList.toggle('current', menu[i] === current);
                }
                if (search) {
                    search.value = '';
                }

                if (dropdownLabel) {
                    dropdownLabel.innerText = elm.innerText;
                }
                Themify.triggerEvent(this.el, 'tb_panel_tab_' + target);
                Themify.trigger('tb_panel_tab_' + target, parent);
                //api.Utils.hideOnClick(p);
            }
        },
        initClick(e) {
            const dockMin=this.el.getRootNode().querySelector('.docked_min');
            this.el.tfOn(Themify.click, e => {
                const events = {
                    '.add_module_btn': 'addComponent',
                    '.panel_close': 'closeFloat',
                    '.minimize': 'minimize',
                    '.nav_tab': 'tabs',
                    '.tb_favorite': 'toggleFavoriteModule',
                    '.panel_title': 'toggleAccordion'
                };
                for (let sel in events) {
                    if (e.target.closest(sel)) {
                        e.preventDefault();
                        e.stopPropagation();
                        this[events[sel]](e.target);
                        break;
                    }
                }
            });
            if(dockMin!==null){
                dockMin.tfOn(Themify.click,e=>{
                    e.stopPropagation();
                    this.dockMinimize();
                },{passive:true});
            }
            this.el.tfOn('tb_panel_tab_panel_rows', () => {
                this.rowPanel();
            }, {
                once: true,
                passive: true
            });
            this.el.tfOn('tb_panel_tab_panel_library', () => {
                this.libraryPanel();
            }, {
                once: true,
                passive: true
            });
        },
        async rowPanel() {
            const link = this.el.getRootNode().querySelectorAll('style');
            await Promise.all([Themify.loadJs(Themify.builder_url + 'js/editor/modules/predesigned-rows',!!api.preDesignedRows), Themify.loadCss(Themify.builder_url + 'css/editor/modules/predesigned-rows', null,null, link[link.length - 3].nextElementSibling)]);
            new api.preDesignedRows(this.el.tfClass('predesigned_container')[0]);
        },
        async libraryPanel() {
            const link = this.el.getRootNode().querySelectorAll('style');
            await Promise.all([Themify.loadJs(Themify.builder_url + 'js/editor/modules/library',!!api.Library), Themify.loadCss(Themify.builder_url + 'css/editor/modules/library', null,null, link[link.length - 3].nextElementSibling)]);
            new api.Library(this.el.tfClass('library_container')[0]);
        },
        toggleAccordion(item) {
            item.closest('.panel_acc').classList.toggle('tb_collapsed');
        },
        toggleFavoriteModule(el) {
            const _this = this,
                module = el.closest('.tb_module'),
                slug = module.dataset.slug,

                trEnd = function(e) {
                        this.classList.toggle('favorited');
                        const categories = this.dataset.categories.split(','),
                            parent = this.closest('.panel_modules_wrap'),
                            fav = parent.querySelector('[data-category="favorite"]');
                        if (this.classList.contains('favorited')) {
                            for (let i = categories.length - 1; i > -1; --i) {
                                let cat = parent.querySelector('[data-category="' + categories[i] + '"]');
                                if(cat){
                                    let items=cat.tfClass('tb-module-' + slug);
                                    for(let j=items.length-1;j>-1;--j){
                                        if(this!==items[j]){
                                            items[j].remove();
                                            if (cat.childElementCount===0) {
                                                cat.parentNode.style.display='none';
                                            }
                                        }
                                        else{
                                            fav.appendChild(this); 
                                            if (cat.childElementCount===0) {
                                                cat.parentNode.style.display='none';
                                            }
                                            requestAnimationFrame(()=>{
                                                fav.parentNode.style.display='';
                                                this.style.transform=this.style.opacity='';
                                            });
                                        }
                                    }
                                }
                            }
                        } 
                        else {
                                for (let i = categories.length - 1; i > -1; --i) {
                                    let cat = parent.querySelector('[data-category="' + categories[i] + '"]'),
                                        clone=this.cloneNode(true),
                                        p = parseInt(clone.dataset.index),
                                        place = null;
                                    if(cat){
                                        while (--p !== 0) {
                                            place = cat.querySelector('[data-index="' + p + '"]');
                                            if (place!==null) {
                                                break;
                                            }
                                        }
                                        if (place) {
                                            place.after(clone);
                                        } else {
                                            cat.prepend(clone);
                                        }
                                        cat.parentNode.style.display='';
                                        requestAnimationFrame(()=>{
                                            clone.style.transform=clone.style.opacity='';
                                         },5);
                                     }
                                }
                                this.remove();
                                if (fav.tfClass('tb_module').length===0) {
                                    fav.parentNode.style.display='none';
                                }
                        }
                    },
                ajaxData={
                    action:'tb_module_favorite',
                    module_name: slug,
                    module_state:module.classList.contains('favorited')?0:1
                };
                
                module.tfOn('transitionend',trEnd, {
                    passive: true,
                    once: true
                })
                .style.opacity = 0;
                module.style.transform = 'scale(.5)';

            api.LocalFetch(ajaxData, 'text');
        },
        dockMinimize() {
            const workspace = topWindow.document.tfClass('tb_workspace_container')[0],
                items=[topWindow.document.body,this.el];
            if(api.activeModel){
                items.push(api.LightBox.el);
            }
            workspace.tfOn('transitionend', function() {
                this.style.transition ='';
                Themify.trigger('tb_resize_lightbox');
                api.Utils._onResize(true);
            },{passive:true,once:true})
            .style.transition = 'width .3s';
            
            for(let i=items.length-1;i>-1;--i){
                items[i].classList.toggle('tb_dock_minimized');
            }
        },
        minimize(e) {
            const cl = this.el.classList;
            if (cl.contains('is_minimized')) {
                const storage = this.getStorage();
                this.el.style.height = storage.height ? (storage.height + 'px') : '';
            } 
            cl.toggle('is_minimized');
        },
        openFloat(e) {
            if (e) {
                localStorage.removeItem('tb_panel_closed');
            }
            this.el.style.display = '';
            api.ToolBar.el.classList.remove('tb_panel_closed');
            this.el.classList.remove('tb_panel_closed');
            api.SmallPanel.hide();
            requestAnimationFrame(()=>{
                this.el.tfClass('panel_search')[0].focus();
            });
        },
        closeFloat(e) {
            if (e) {
                localStorage.setItem('tb_panel_closed', true);
                api.Dock.set(false);
            }
            this.el.style.display = 'none';
            this.el.classList.add('tb_panel_closed');
            api.ToolBar.el.classList.add('tb_panel_closed');
        },
        addComponent(target) {
            let type = target.dataset.type,
                slug=target.closest('[data-slug]');
                slug=slug?slug.dataset.slug:'';
                if ('module' === type) {
                    this.newModule(slug);
                } 
                else if ('page_break' === type) {
                    this.newPageBreak();
                } 
                else if ('row' === type) {
                    this.newGrid(slug);
                } 
                else if ('predesigned' === type) {
                    this.newPredesign(slug);
                }
        },
        newModule(slug,scrollTo) {
            const builder=api.Builder.get(),
                dummy=doc.createElement('div'),
                holder=builder.el.querySelector('.tb_column_btn_plus.clicked');
            if(holder){
                let subHolder=holder.parentNode;
                if(subHolder.classList.contains('active_module')){
                    subHolder.after(dummy);
                }
                else{
                    subHolder.tfClass('tb_holder')[0].appendChild(dummy);
                }
            }
            else{
               builder.newRowAvailable(true).el.tfClass('tb_holder')[0].appendChild(dummy);
            } 
            api.SmallPanel.hide();
            api.Drop.module(dummy, false,slug,scrollTo);
        },
        newPageBreak(scrollTo) {
            api.undoManager.start('move');
            let builder=api.Builder.get(),
                dummy=doc.createElement('div'),
                holder=builder.el.querySelector('.tb_column_btn_plus.clicked');
            if(holder){
                holder=holder.closest('.module_row');
            }
            else{
                const rows=builder.el.tfClass('module_row');
                holder=rows[rows.length-1];
            }
            holder.after(dummy);
            api.SmallPanel.hide();
            api.Drop.row(dummy,'pagebreak',null,scrollTo).then(()=>{
                api.undoManager.end('move');
            });
        },
        newGrid(slug,scrollTo) {
            api.undoManager.start('move');
            let builder=api.Builder.get(),
                dummy=doc.createElement('div'),
                holder=builder.el.querySelector('.tb_column_btn_plus.clicked');
            if(holder){
                let subHolder=holder.parentNode;
                if(subHolder.classList.contains('active_module')){
                    subHolder.after(dummy);
                }
                else{
                    subHolder.tfClass('tb_holder')[0].appendChild(dummy);
                }
            }
            else{
                const rows=builder.el.children;
                for(let i=rows.length-1;i>-1;--i){
                    if(rows[i].classList.contains('module_row')){
                        holder=rows[i];
                        break;
                    }
                }
				if ( holder ) {
					holder.after(dummy);
				} else {
					builder.el.prepend( dummy );
				}
            }
            api.SmallPanel.hide();
            api.Drop.row(dummy,'grid',slug,scrollTo).then(()=>{
                api.undoManager.end('move');
            });
        },
        newPredesign(slug,scrollTo){
            api.undoManager.start('move');
            
            let builder=api.Builder.get(),
                dummy=doc.createElement('div'),
                holder=builder.el.querySelector('.tb_column_btn_plus.clicked');
            if(holder){
                holder=holder.closest('.module_row');
            }
            else{
                const rows=builder.el.tfClass('module_row');
                holder=rows[rows.length-1];
            }
            holder.after(dummy);
            api.SmallPanel.hide();
            api.Drop.row(dummy,'predesign',slug,scrollTo).then(()=>{
                api.undoManager.end('move');
            });
        },
        setResponsiveTabs(cl) {
            if (!api.isDocked) {
                if (!cl) {
                    cl = this.getPanelClass(this.getStorage().width);
                }
                this.el.classList.add(cl);
            }
        },
        getPanelClass(w) {
            let cl = 'tb_float_large';
            if (w <= 195) {
                cl = 'tb_float_xsmall';
            } else if (w <= 270) {
                cl = 'tb_float_small';
            }
            return cl;
        },
        draggable() {
            const self = this,
                handle = this.el.tfClass('drag_handle')[0];
            if (!api.isDocked) {
                this.setResponsiveTabs();
            }
            handle.tfOn('pointerdown', function(e) {
                if (e.which === 1) {
                    e.stopImmediatePropagation();
                    let timer,
                        el = self.el,
                        owner = this.ownerDocument;
                    el.style.willChange='transform';
                    const _x = e.clientX,
                        _y = e.clientY,
                        box = el.getBoundingClientRect(),
                        dragX = box.left - _x,
                        dragY = box.top - _y,
                        width = box.width,
                        draggableCallback = e => {
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
                            api.SmallPanel.hide();
                            Themify.trigger('tb_panel_drag_start');
                        };
                    owner.body.classList.add('tb_start_animate');
                    api.ToolBar.el.classList.add('tb_start_animate');
                    el.classList.add('tb_start_animate');
                    this.tfOn('lostpointercapture', function(e) {
                        e.stopImmediatePropagation();
                        if (timer) {
                            cancelAnimationFrame(timer);
                        }
                        this.tfOff('pointermove', startDrag, {
                            passive: true,
                            once: true
                        })
                        .tfOff('pointermove', draggableCallback, {
                            passive: true
                        });
                        el.style.willChange='';
                        Themify.trigger('tb_panel_drag_end');
                        self.updateStorage();
                        owner.body.classList.remove('tb_start_animate');
                        api.ToolBar.el.classList.remove('tb_start_animate');
                        el.classList.remove('tb_start_animate');
                        timer = el = owner = null;

                    }, {
                        passive: true,
                        once: true
                    })
                    .tfOn('pointermove', startDrag, {
                        passive: true,
                        once: true
                    })
                    .tfOn('pointermove', draggableCallback, {
                        passive: true
                    })
                    .setPointerCapture(e.pointerId);
                }

            }, {
                passive: true
            });

            this.resize();
        },
        resize() {
            const self = this,
                items = this.el.tfClass('tb_resizable');
            for (let i = items.length - 1; i > -1; --i) {
                items[i].tfOn('pointerdown', function(e) {
                    if (e.which === 1) {
                        e.stopImmediatePropagation();
                        let activeCl, timer,
                            owner = this.ownerDocument,
                            el = self.el;
                        el.style.willChange='transform,width,height';
                        const maxHeight = owner.documentElement.clientHeight * .9,
                            minHeight = 50,
                            computed = getComputedStyle(el),
                            minWidth = parseInt(computed.getPropertyValue('min-width')),
                            maxWidth = parseInt(computed.getPropertyValue('max-width')),
                            axis = this.dataset.axis,
                            startH = parseInt(el.offsetHeight, 10),
                            startW = parseInt(el.offsetWidth, 10),
                            resizeX = e.clientX,
                            resizeY =e.clientY,
                            _resize = e => {
                                e.stopImmediatePropagation();
                                timer = requestAnimationFrame(() => {
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
                                        }
                                        if ((axis === 'se' || axis === 'y' || axis === '-y' || axis === 'sw' || axis === 'nw' || axis === 'ne') && h >= minHeight && h <= maxHeight) {
                                            if (axis === '-y' || axis === 'nw' || axis === 'ne') {
                                                matrix.m42 += parseInt(el.style.height) - h;

                                            }
                                            el.style.height = h + 'px';
                                        }
                                    }
                                    el.style.transform = 'translate(' + matrix.m41 + 'px,' + matrix.m42 + 'px)';
                                    if (axis !== 'y' && axis !== '-y') {
                                        const current = self.getPanelClass(w);
                                        if (activeCl !== current) {
                                            if (activeCl) {
                                                el.classList.remove(activeCl);
                                            }
                                            activeCl = current;
                                            self.setResponsiveTabs(current);
                                        }
                                    }
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
                                api.ToolBar.el.classList.remove('tb_start_animate');
                                el.classList.remove('tb_start_animate');
                                timer = activeCl = owner = el = null;
                            };
                        this.tfOn('pointermove', _resize, {
                            passive: true
                        })
                        .tfOn('lostpointercapture', _stop, {
                            passive: true,
                            once: true
                        })
                        .setPointerCapture(e.pointerId);
                        api.ToolBar.el.classList.add('tb_start_animate');
                        el.classList.add('tb_start_animate');
                        owner.body.classList.add('tb_start_animate');
                    }
                }, {
                    passive: true
                });
            }
        },
        initSearch() {
            const input = this.el.tfClass('panel_search')[0];
            if (input) {
                input.value = '';
                const search = function(e) {

                    const el = this.tfClass('panel_search')[0],
                        parent = this.closest('.panel'),
                        target = parent.querySelector('.nav_tab .current').dataset.target,
                        s = e.type === 'reset' ? '' : el.value.trim();
                    let items,
                        filter,
                        isModule,
                        isLibrary;
                    if (target === 'panel_modules_wrap') {
                        items = parent.tfClass('tb_module');
                        isModule = true;
                    } 
                    else if (target === 'panel_rows' && api.preDesignedRows) {
                        items = parent.tfClass('predesigned_row');
                        const dropdown=items[0].closest('.panel_tab').tfClass('dropdown_label')[0];
                        if(dropdown.dataset.active){
                            Themify.triggerEvent(dropdown.nextElementSibling.firstElementChild,Themify.click);
                        }
                    } 
                    else if (target === 'panel_library' && api.Library) {
                        items = parent.tfClass('library_item');
                        filter = items[0].closest('.panel_tab').querySelector('.library_tab .current').dataset.target;
                        isLibrary = true;
                    }
                    if (items) {
                        const is_empty = s === '',
                            reg = !is_empty ? new RegExp(s, 'i') : false,
                            selector = isModule ? '.module_name' : (isLibrary ? '' : '.predesigned_title'),
                            cats = new Set();

                        for (let i = items.length - 1; i > -1; --i) {
                            let elm = selector === '' ? items[i] : items[i].querySelector(selector),
                                display = is_empty || reg.test(elm.textContent) ? '' : 'none';
                            if(filter && !items[i].classList.contains(filter)){
                                display='none';
                            }
                            if (display === '') {
                                let parent = items[i].closest('.panel_category');
                                if (parent) {
                                    parent.parentNode.style.display = '';
                                }
                            }
                            items[i].style.display = display;
                            if (isModule===true && display==='') {
                                cats.add(items[i].parentNode);
                            }
                        }
                        // hide other accordions
                        parent.classList.toggle('panel_searching', !is_empty);
                        // Hide empty module accordions
                        if (isModule) {
                            items = parent.tfClass('panel_category');
                            for (let i = items.length - 1; i > -1; --i) {
                                items[i].parentNode.style.display=cats.has(items[i])?'':'none';
                            }
                        } 
                    }

                    if (s === '' && !Themify.isTouch) {
                        setTimeout(() => {
                            el.focus();
                        }, 50);
                    }
                };
                input.parentNode.tfOn('input reset', search, {
                    passive: true
                });
            }
        },
        getStorage() {
            if (this.size === null) {
                let storage = localStorage.getItem(this.storageKey);
                storage = storage ? JSON.parse(storage) : {};
                const _default = {
                    top: 50,
                    left: 10,
                    width: 140,
                    height: 600
                };
                this.size = Object.assign(_default, storage);
            }
            return this.size;
        },
        updateStorage() {
            const tr = this.el.style.transform,
                matrix = tr ? (new DOMMatrix(tr)) : null,
                box = this.el.tfClass('panel_top')[0].getBoundingClientRect(),
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
            if (obj.width) {
                this.el.style.width = obj.width + 'px';
            }
            if (obj.height) {
                this.el.style.height = obj.height + 'px';
            }
            if (!api.isDocked && storage !== obj && Object.entries(obj).toString() !== Object.entries(storage).toString()) {
                this.size = null;
                localStorage.setItem(this.storageKey, JSON.stringify(obj));
            }
            return obj;
        }
    };

    api.SmallPanel = {
        el: null,
        init() {
            const root = doc.tfId('tb_small_panel_root'),
                fr = root.firstElementChild,
                styles = api.MainPanel.el.getRootNode().querySelectorAll('style,#tf_svg'),
                fragment=doc.createDocumentFragment();
                
            if (fr) { // shadowroot="open" isn't support
                root.attachShadow({
                    mode: fr.getAttribute('shadowroot')
                }).appendChild(fr.content);
                fr.remove();
            }
            
            for(let i=0,len=styles.length;i<len;++i){
                if(styles[i].id!=='module_main_panel_style' && styles[i].id!=='tf_fonts_style'){
                    fragment.appendChild(styles[i].cloneNode(true));
                }
            }
            root.shadowRoot.prepend(fragment);
            
            const self = this;
            if (api.mode !== 'visual' && doc.querySelector('.edit-post-layout__content') !== null) {
                doc.tfClass('.edit-post-layout__content').appendChild(root);
            } else {
                doc.body.appendChild(root);
            }
            
            this.el = root.shadowRoot.tfId('small_panel');
            doc.tfOn(Themify.click,e=>{
                if(!this.el.contains(e.target) && !this.el.getRootNode().host.contains(e.target)){
                    if(e.target.closest('.tb_column_btn_plus')){
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        this.show(e.target);
                    }
                    else{
                        this.hide();
                    }
                }
            });
            this.initClick();
        },
        show(item) {
            if (topWindow.document.body.classList.contains('tb_standalone_lightbox')) {
                api.LightBox.close();
            }
            if(item.classList.contains('clicked')){
                return;
            }
            doc.body.style.willChange='scroll-position';
            if(this.el.childElementCount===0){
                const menu = api.MainPanel.el.tfClass('nav_tab')[0].cloneNode(true),
                    container = api.MainPanel.el.tfClass('panel_container')[0].cloneNode(true),
                    fr = doc.createDocumentFragment();
                fr.append(menu, container);
                
                this.el.appendChild(fr);
                const modules = container.tfClass('modules'),
                    predesign=container.tfClass('predesigned_row'),
                    tabs = this.el.tfClass('tb_compact_tabs'),
                    nav=this.el.tfClass('nav_tab');
                for (let i = predesign.length - 1; i > -1; --i) {
                    predesign[i].remove();
                }
                for (let i = modules.length - 1; i > -1; --i) {
                    modules[i].style.display = '';
                }
                for (let i = tabs.length - 1; i > -1; --i) {
                    tabs[i].classList.remove('tb_compact_tabs');
                }
                
                for (let i = nav.length - 1; i > -1; --i) {
                     Themify.triggerEvent(nav[i].firstElementChild ,Themify.click);
                }
                this.initSearch();
                api.Drag(this.el);
                if(api.mode==='visual'){
                    topWindow.document.tfOn(Themify.click,(e)=>{
                        if(!this.el.contains(e.target) && !this.el.getRootNode().host.contains(e.target)){
                            this.hide();
                        }
                    });
                }
                Themify.on('tfsmartresize',()=>{
                    this.position();
                });
            }
            else{
                const search=this.el.tfClass('panel_search')[0];
                if (search) {
                    search.value = '';
                    if(!Themify.isTouch){
                        setTimeout(()=>{
                            search.focus();
                        },200);
                    }
                }
            }
            const host=this.el.getRootNode().host;
            this.el.classList.toggle('tb_subrow_open', item.parentNode.closest('.sub_column') !== null);
            
            this.clear();
            item.classList.add('clicked');
            api.ToolBar.el.classList.add('tb_panel_dropdown_openend');
            api.MainPanel.el.classList.add('tb_panel_dropdown_openend');
            
            host.classList.add('tf_hidden');
            host.classList.remove('tf_hide');
            this.position(item);
            host.classList.remove('tf_hidden');

            Themify.trigger('disableInline');
            doc.body.style.willChange='';
           
        },
        hide(force) {
			if(this.el){
				const host=this.el.getRootNode().host;
				if(!host.classList.contains('tf_hide')){
					host.classList.add('tf_hide');
					api.ToolBar.el.classList.remove('tb_panel_dropdown_openend');
					api.MainPanel.el.classList.remove('tb_panel_dropdown_openend');
					this.clear();
				}
			}
        },
        position(item){
            const clicked=item?item:api.Builder.get().el.querySelector('.clicked.tb_column_btn_plus');
            if(clicked!==null){
                clicked.style.display='block';
                const w = this.el.offsetWidth,
                    h = this.el.offsetHeight,
                    box = clicked.getBoundingClientRect(),
                    gutenContainer = api.mode !== 'visual' ? doc.tfClass('edit-post-layout__content')[0] : null,
                    winW=doc.documentElement.clientWidth;
                let left = box.left + (box.width / 2),
                    top = box.top + window.scrollY;
                if (gutenContainer) {
                    top += gutenContainer.scrollTop - 70;
                    left = (gutenContainer.clientWidth / 2);
                }
                left -= (w / 2);
                if (left < 0) {
                    left = 5;
                }
                else if((w+left)>winW){
                    left=winW-w-5;
                }
                this.el.style.transform = 'translate(' + left + 'px,' + top + 'px)';
                api.Utils.addViewPortClass(this.el);
                if(this.el.classList.contains('tb_touch_bottom')){
                    this.el.style.transform = 'translate(' + left + 'px,' + (top-h) + 'px)';
                }
                clicked.style.display='';
            }
        },
        clear(){
          const clicked=api.Builder.get().el.querySelectorAll('.clicked.tb_column_btn_plus');
            for(let i=clicked.length-1;i>-1;--i){
                clicked[i].classList.remove('clicked');
            }  
        }

    };

    api.Dock =  {
        key : 'themify_builder_docked',
        init() {
            if (api.mode === 'visual') {
                api.isDocked = localStorage.getItem(this.key);
                if (api.isDocked === 'true') {
                    api.isDocked = localStorage.getItem('themify_builder_docked_left') === 'true' ? 'left' : 'right';
                    localStorage.removeItem('themify_builder_docked_left');
                    this.set(api.isDocked);
                }
                else if(api.isDocked==='0'){
                    api.isDocked=false;
                }
                else if(!api.isDocked){
                    api.isDocked=Themify.isRTL?'right':'left';
                }
            }
            Themify.on('tb_panel_drag_start', () => {
                if(!topWindow.document.body.classList.contains('tb_standalone_lightbox')){
                    this.close();
                    let drag = (x, w) => {
                        if (api.mode === 'visual') {
                            this.drag(x, w);
                        }
                    };
                    Themify.on('tb_panel_drag', drag)
                        .on('tb_panel_drag_end', () => {
                            Themify.off('tb_panel_drag', drag);
                            drag = null;
                            if (api.mode === 'visual') {
                                const cl = topWindow.document.body.classList;
                                if (cl.contains('tb_dock_highlight')) {
                                    const dir = cl.contains('tb_dock_left_highlight') ? 'left' : 'right';
                                    cl.remove('tb_dock_highlight', 'tb_dock_left_highlight');
                                    this.set(dir);
                                    this.setDocked();
                                } else {
                                    this.set(null);
                                }
                            }
                        }, true);
                }
            });
        },
        set(isDocked) {
            if (api.mode === 'visual') {
                api.isDocked = isDocked;
                if (isDocked) {
                    localStorage.setItem(this.key, isDocked);
                    api.MainPanel.openFloat(true);
                } else {
                    localStorage.setItem(this.key, 0);
                    localStorage.removeItem('themify_builder_docked_left');
                }
            }
        },
        setDocked(animate) {
            if (api.isDocked) {
                const cl = topWindow.document.body.classList;
                if (!cl.contains('tb_panel_docked')) {
                    const toolbarCl = api.ToolBar.el.classList,
                        panel = api.MainPanel.el,
                        panelCl=panel.classList,
                        lb = api.LightBox.el,
                        workspace = topWindow.document.tfClass('tb_workspace_container')[0],
                        classes=[panelCl,toolbarCl,cl];
                    panelCl.remove('is_minimized');
                    if (animate !== false) {
                        const trEnd = function() {
                            this.style.transition = '';
                        };
                        workspace.tfOn('transitionend', function() {
                            trEnd.call(this);
                            api.Utils._onResize(true);
                        }, {
                            passive: true,
                            once: true
                        })
                        .style.setProperty('transition', 'width .3s', 'important');
                        if (panel.offsetHeight !== 0) {
                            panel.tfOn('transitionend', trEnd, {
                                passive: true,
                                once: true
                            })
                            .style.setProperty('transition', 'height .3s', 'important');
                        }
                        if (lb.offsetHeight !== 0) {
                            lb.tfOn('transitionend', function(){
                                trEnd.call(this);
                                api.LightBox.setupLightboxSizeClass();
                            }, {
                                passive: true,
                                once: true
                            })
                            .style.setProperty('transition', 'height .3s', 'important');
                        }
                    }
                    for(let i=classes.length-1;i>-1;--i){
                        classes[i].remove('tb_panel_right_dock','tb_panel_left_dock');
                        classes[i].add('tb_panel_docked','tb_panel_'+api.isDocked+'_dock');
                    }
                    panelCl.remove('tb_float_xsmall', 'tb_float_small', 'tb_float_large');
                    lb.classList.remove('tb_float_xsmall', 'tb_float_small', 'tb_float_large');
                    api.MainPanel.setResponsiveTabs();
                    Themify.trigger('tb_resize_lightbox');
                    return true;
                }
            }
            return false;
        },
        drag(clientX, width) {
            if (!api.isDocked) {
                const cl = topWindow.document.body.classList;
                if (!cl.contains('tb_standalone_lightbox')) {
                    if (clientX < 0 || (clientX + 20 + width) > topWindow.innerWidth) {
                        const dir = clientX < 0 ? 'left' : 'right';
                        cl.add('tb_dock_highlight');
                        cl.toggle('tb_dock_left_highlight', dir === 'left');
                        return dir;
                    } else {
                        cl.remove('tb_dock_highlight', 'tb_dock_left_highlight');
                    }
                }
            }
            return false;
        },
        close(animate=false) {
            if (api.isDocked) {
                if (animate!==false && api.isDocked === 'right') {
                    const workspace = topWindow.document.tfClass('tb_workspace_container')[0];
                    workspace.tfOn('transitionend', function() {
                        this.style.transition = '';
                        api.Utils._onResize(true);
                    },{passive:true,once:true})
                    .style.transition = 'width .3s';
                }
                api.ToolBar.el.classList.remove('tb_panel_docked', 'tb_panel_left_dock', 'tb_panel_right_dock');
                api.MainPanel.el.classList.remove('tb_panel_docked', 'tb_panel_left_dock', 'tb_panel_right_dock');
                topWindow.document.body.classList.remove('tb_panel_docked', 'tb_panel_left_dock', 'tb_panel_right_dock');
                if (animate===false || api.isDocked !== 'right') {
                    api.Utils._onResize(true);
                }
                this.set(null);
                Themify.trigger('tb_resize_lightbox');
                api.MainPanel.setResponsiveTabs();

            }
        }
    };
    api.SmallPanel=Object.assign({},api.MainPanel,api.SmallPanel );
    
    Themify.on('tb_toolbar_style_ready',()=>{
        api.MainPanel.initialize();
        api.Dock.init();
    },true,!!(api.ToolBar!==und && api.ToolBar.el!==null))
    .on('tb_toolbar_loaded', ()=>{
        api.MainPanel.init();
        api.SmallPanel.init();  
    },true,!!(api.ToolBar!==und && api.ToolBar.isLoaded===true));

})(tb_app, document, Themify, window.top,undefined);