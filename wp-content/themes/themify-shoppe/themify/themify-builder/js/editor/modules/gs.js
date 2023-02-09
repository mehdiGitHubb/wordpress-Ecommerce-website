((api, Themify, topWindow, doc,und) => {
    'use strict';
    const gsTpl=doc.tfId('tb_global_styles_root').content;
    let searchCache={},
        xhr;
    class GS extends HTMLElement {
        
         init() {
            const form=this.shadowRoot.querySelector('.form');
            if(!this.done){
                this.done=true;
                const items = Object.keys(api.GS.styles);
                this.addItem(items);
                if (api.GS.allLoaded !== true) {
                    if (items.length < 10) {
                        this.loadMore();
                    }
                    this.list.tfOn('scroll',e=>{
                        this.onScroll(e);
                    }, {passive: true});
                    this.reLoad=this.list.tfClass('reload')[0];
                    this.reLoad.tfOn(Themify.click,e=>{
                       e.stopPropagation();
                       e.preventDefault();
                       this.loadMore();
                    });
                }
            }
            this.hideShowNoGsText();
            form.focus();
        }
        addItem(items) {
            if(items.length>0){
                const f = doc.createDocumentFragment(),
                    st = this.field.value.split(' ');
                for (let i = 0, len = items.length; i < len; ++i) {
                    if(this.list.querySelector('[data-id="'+items[i]+'"]')===null){
                        let post = api.GS.styles[items[i]],
                            container = doc.createElement('div'),
                            typeTag = doc.createElement('div'),
                            titleTag = doc.createElement('div');
                        container.className = 'item';
                        container.className += st.indexOf(items[i]) !== -1 ? ' selected' : '';
                        container.dataset.id = items[i];
                        titleTag.className = 'title';
                        titleTag.innerText = post.title;
                        typeTag.className = 'type';
                        typeTag.innerText = post.type;
                        container.append(titleTag,typeTag);
                        f.appendChild(container);
                    }
                }
                this.list.appendChild(f);
                this.checkReload();
            }
        }
        // Insert new global style
        insert(id) {
            if(!this.selectedContainer.querySelector('[data-id="' + id + '"]')){
                // Add selected global style HTML and hide it in drop down
               this.createSelected(id);
                // Add CSS class to global style field
                let st = this.field.value + ' ' + id;
                this.field.value = st = st.trim();
                this.selectedContainer.closest('#container').classList.remove('empty');
                api.GS.setGsStyle(st.split(' '));
            }
        }
        // Delete Global Style from module
        delete(id) {
                const item = this.list.querySelector('[data-id="' + id + '"]'),
                    selected = this.selectedContainer.querySelector('[data-id="' + id + '"]');
                if (item !== null) {
                    item.classList.remove('selected');
                }
                if (selected !== null) {
                    selected.remove();
                }
                // Add CSS class to global style field
                let st = this.field.value.trim().split(' ');
                st.splice(st.indexOf(id), 1);
                st= st.join(' ');
                this.field.value = st;
                if(st===''){
                    this.selectedContainer.closest('#container').classList.add('empty');
                }
                this.checkReload();
                api.GS.setGsStyle(st.split(' '), true);
        }
        // Crete selected GS HTML
        createSelected(id) {
            const post = api.GS.styles[id],
                selectedItem = doc.createElement('div'),
                title = doc.createElement('span'),
                deleteIcon = doc.createElement('span'),
                edit = doc.createElement('span'),
                currentItem=this.list.querySelector('[data-id="' + id + '"]');
            selectedItem.className = 'selected';
            selectedItem.dataset.id = id;
            edit.className = 'edit';
            edit.appendChild(api.Helper.getIcon('ti-pencil'));
            title.className='tf_overflow';
            title.innerText = post.title;
            deleteIcon.className = 'delete tf_close';
            selectedItem.append(edit,title,deleteIcon);
            if(currentItem){
                currentItem.classList.add('selected');
            }
            this.selectedContainer.appendChild(selectedItem);
            this.checkReload();
        }
        loadMore(s) {
            const cl=this.shadowRoot.querySelector('#container').classList;
            if(cl.contains('loading')){
                return Promise.reject();
            }
            if(!s){
                s='';
            }
            else if(searchCache[s]!==und) {
                return Promise.resolve();
            }
            const loaded = [];
            for (let i in api.GS.styles) {
                if (api.GS.styles[i].id !== und) {
                    loaded.push(api.GS.styles[i].id);
                }
            }
            cl.add('loading');
            const ajaxData={
                s: s,
                action: 'tb_get_gs_posts',
                loaded:loaded
            };
            xhr = new AbortController();
            return api.LocalFetch(ajaxData,'json',{signal: xhr.signal}).then(res=>{
                this.hideShowNoGsText(true);
                api.GS.extend(res);
                const keys = Object.keys(res);
                if (!s) {
                    api.GS.allLoaded= keys.length < 10;
                    if(api.GS.allLoaded){
                        this.reLoad.remove();
                        this.reLoad=null;
                    }
                }
                else{
                    searchCache[s]=true;
                }
                this.addItem(keys);
            })
            .catch(e=>{
                
            })
            .finally(()=>{
                cl.remove('loading');
            });
        }
        onScroll(e) {
            if (api.GS.allLoaded=== false) {
                const target = e.target,
                    distToBottom = Math.max(target.scrollHeight - (target.scrollTop + target.offsetHeight), 0);
                if (distToBottom > 0 && distToBottom <= 200) {
                    this.loadMore().catch(e=>{});
                }
            }
        }
        search() {
            this.shadowRoot.querySelector('#search').tfOn('input', e=> {
                const filter = e.target.value.toUpperCase().trim(),
                    items = e.target.closest('.form').tfClass('item'),
                    filterByValue = () => {
                        let found=items.length===0;
                        for (let i = items.length - 1; i > -1; --i) {
                            let title = items[i].tfClass('title')[0];
                            if (title) {
                                let display=filter==='' || title.innerHTML.toUpperCase().indexOf(filter)!== -1 ? '' : 'none';
                                if(found===false && display===''){
                                    found=true;
                                }
                                items[i].style.display =display;
                            }
                        }
                        this.hideShowNoGsText(found);
                    };
                if (xhr) {
                    xhr.abort();
                    xhr = null;
                }
                if (filter!=='' && searchCache[filter]===und && !api.GS.allLoaded) {
                    setTimeout(() => {
                        this.loadMore(filter)
                            .then(filterByValue)
                            .catch(e=>{});
                    }, 100);
                }
                else{
                    filterByValue();
                }
            },{
                passive: true
            });
            this.shadowRoot.querySelector('.clear_search').tfOn(Themify.click, e=> {
                e.stopPropagation();
                const search=this.shadowRoot.querySelector('#search');
                search.value='';
                search.focus();
                Themify.triggerEvent(search,'input');
            },{
                passive: true
            });
        }
        checkReload(){
            if(this.reLoad){
                this.reLoad.classList.toggle('tf_hide',this.list.scrollHeight > this.list.clientHeight);
            }
        }
        hideShowNoGsText(check){
            if(check===und){
                check=this.list.tfClass('item')[0]!==und;
            }
            this.list.tfClass('no_gs')[0].classList.toggle('tf_hide',check);
        }
        // Init Save as global style event
        saveAs() {
            api.LiteLightBox.prompt(themifyBuilder.i18n.enterGlobalStyleName).then(data => {
                    if(data[0]==='yes'){
                        const title=data[1];
                        if (!title) {
                            TF_Notification.showHide('error',themifyBuilder.i18n.enterGlobalStyleName);
                            this.saveAs();
                        } 
                        else {
                            ThemifyConstructor.setStylingValues(api.activeBreakPoint);
                            const styles = api.Helper.clear(ThemifyConstructor.values);
                            delete styles[api.GS.key];
                            const ajaxData={
                                action:'tb_save_as_new_global_style',
                                styles: styles,
                                title: title,
                                type: api.activeModel.get('mod_name')
                            };
                            api.Spinner.showLoader();

                            api.LocalFetch(ajaxData).then(res => {
                                api.Spinner.showLoader('hide');
                                if ('success' === res.status) {
                                    const sucessMsg=res.msg;
                                    res = res.post_data;
                                    api.GS.styles[res.class] = res;
                                    api.Utils.saveCss(api.Helper.clear(res.data), '', res.id).then(()=>{
                                        TF_Notification.showHide('done',sucessMsg);
                                        api.LiteLightBox.confirm({
                                            msg: themifyBuilder.i18n.addSavedGS
                                        }).
                                        then(answer => {
                                            if(answer){
                                                if ('yes' === answer) {
                                                    ThemifyConstructor.resetStyling(api.activeModel);
                                                }
                                                this.addItem([res.class]);
                                                if ('yes' === answer) {
                                                    this.insert(res.class);
                                                }
                                            }
                                        });
                                    });
                                } 
                                else {
                                    api.LiteLightBox.alert(res.msg);
                                }

                            }).catch(() => {
                                api.Spinner.showLoader('error');
                            });
                        }
                }
            });
        }
        disconnectedCallback(){
            if (xhr) {
                xhr.abort();
            }
            this.field=this.list=xhr = this.selectedContainer=this.done=null;
        }
        connectedCallback () {
                    
                let vals=ThemifyConstructor.values[api.GS.key];
                if(!vals){
                    
                }
                const tpl = gsTpl.cloneNode(true),
                    el=tpl.querySelector('#container'),
                    input = ThemifyConstructor.hidden.render({
                        id: api.GS.key,
                        is_responsive: false,
                        value: vals,
                        control: false
                    }, ThemifyConstructor);
                    
                this.selectedContainer=el.tfClass('selected_wrap')[0];
                this.list = el.tfClass('list')[0];
                if (vals) {
                    el.classList.remove('empty');
                    vals = vals.split(' ');
                    let v = '';
                    for (let i = vals.length - 1; i > -1; --i) {
                        if (api.GS.styles[vals[i]] !== und) {
                            v += ' ' + vals[i];
                            this.createSelected(vals[i]);
                        }
                    }
                    input.value = v.trim();
                }
                
                this.field=input;
                
                el.tfClass('actions')[0].tfOn(Themify.click,e=>{
                    e.stopPropagation();
                    const action = e.target.dataset.action;
                    if(action==='insert'){
                        this.init();
                    }
                    else if(action==='save'){
                        this.saveAs();
                    }
                },{passive:true});
                
                this.selectedContainer.tfOn(Themify.click,e=>{
                    let id=e.target.closest('.selected');
                    if(id){
                        id=id.dataset.id;
                        if(id){
                            if(e.target.closest('.delete')){
                                this.delete(id);
                            }
                            else if(e.target.closest('.edit')){
                              api.GS.gsEdit(id);
                            }
                        }
                    }
                });
                
                this.list.tfOn(Themify.click,e=>{
                    const item=e.target.closest('.item');
                    if(item){
                        this.insert(item.dataset.id);
                    }
                });
                tpl.querySelector('.overlay').tfOn(Themify.click, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.remove();
                },{once: true});
                
                this.attachShadow({ mode:'open'}).appendChild(tpl);
                
                this.search();
                this.before(input);
               
        }
    }
    customElements.define('tb-gs', GS);
    api.GS =  {
        styles:{},
        allLoaded : false,
        el : null,
        dropdown : null,
        field : null,
        activeGS : null,
        key : 'global_styles',
        previousId : null,
        liveInstance : null,
        init() {
            Themify.on('tb_toolbar_loaded', () => {
                
                const fr=doc.createDocumentFragment();

                fr.appendChild(api.ToolBar.getBaseCss());
                
                gsTpl.prepend(fr.cloneNode(true));

            }, true,(api.ToolBar!==und && api.ToolBar.isLoaded===true));
                  
            if (api.isGSPage === true) {
                Themify.on('themify_builder_ready',()=>{
                    const callback=()=>{
                        this.openStylingPanel();
                        api.Registry.off(api.Builder.get(),'tb_init',callback);	
                    };
                    if(api.is_builder_ready){
                        callback();
                    }
                    else{
                        api.Registry.on(api.Builder.get(),'tb_init',callback);	
                    }
                },true,api.is_builder_ready);
            } 
            else if (themifyBuilder.globalStyles) {
                this.extend(themifyBuilder.globalStyles);
                themifyBuilder.globalStyles = null;
            }
        },
        // Merge two object
        extend(gs){
            for (let key in gs) {
                if (this.styles[key]===und && gs[key] !== und){
                    this.styles[key] = gs[key];
                }
            }
            return this.styles;
        },
        // Open Styling Panel in GS edit post
        openStylingPanel() {
           if (null === ThemifyConstructor.label) {
				ThemifyConstructor.label = themifyBuilder.i18n.label;
			}
			const type = themifyBuilder.globalStyleData.type;
			let selector;console.log(type);
			switch (type) {
				case 'row':
				case 'column':
					selector = 'module_' + type;
					break;
				case 'subrow':
					selector = 'active_subrow';
					break;
				default:
					selector = 'active_module';
					break;
			}
			const model = api.Registry.get(api.Builder.get().el.tfClass(selector)[0].dataset.cid);console.log(model);
			model.edit('styling');
			api.ToolBar.previewBuilder({item: api.ToolBar.el.tfClass('preview')[0]});
        },
        setCss(data, type, isGlobal) {
            if ('visual' === api.mode) {
                api.liveStylingInstance.setCss(data, type, isGlobal);
            }
        },
        createCss(data, type, saving) {
            ThemifyStyles.GS = {};
            const css = ThemifyStyles.createCss(data, type, saving, this.styles, und, saving);
            if (saving === true && Object.keys(this.styles).length > 0 && css.gs) {
                css.gs.used = '';
                for (let i in this.styles) {
                    css.gs.used += '' === css.gs.used ? '' : ', ';
                    css.gs.used += this.styles[i].title;
                }
            }
            return css;
        },
        // Find used items in builder data
        findUsedItems(data) {
            data = JSON.stringify(data);
            let pattern = /"global_styles":"(.*?)"/mg,
                match,
                used = '';
            while ((match = pattern.exec(data)) !== null) {
                used += ' ' + match[1].trim();
            }
            match = null;
            used = used.trim();
            if (used !== '') {
                used = [...new Set(used.split(' '))];
                const usedItems = [];
                for (let i = used.length - 1; i > -1; --i) {
                    if (this.styles[used[i]] !== und) {
                        usedItems.push(used[i]);
                    }
                }
                return usedItems;
            }
            return false;
        },
        // Build require HTML for Global Style fields and controllers to add it in Styling Tab
        globalStylesHTML() {
            if (api.isGSPage === true || this.activeGS !== null) {
                return false;
            }
            return doc.createElement('tb-gs');
        },
        // Trigger required functions on add/delete a GS
        updated(css, res, values,model) {
            if (api.isGSPage === false && 'visual' === api.mode && model.type !== 'module') {
                this.extraStyle(css, res, values,model);
            }
        },
        async setImport(usedGS, data, force) {
            if (force !== true) {
                for (let i in usedGS) {
                    if (this.styles[i] !== und) {
                        delete usedGS[i];
                    }
                }
            }
            if (Object.keys(usedGS).length > 0) {
                const ajaxData={
                    action:'tb_import_gs_posts_ajax',
                    data: JSON.stringify(usedGS),
                    onlySave: force ? 1 : 0
                };
                return api.LocalFetch(ajaxData).then(res => {
                    if (res) {
                        for (let i in res) {
                            this.styles[i] = res[i];
                        }
                    }
                    return data;
                });

            } else {
                return data;
            }
        },
        setGsStyle(values, isRemove,model) {

            if (api.isGSPage === true || api.mode !== 'visual') {
                return;
            }
            if(!model){
                model=api.activeModel;
            }
            let elType = model.get('mod_name'),
                element_id = model.id,
                res = {
                    styling: ThemifyStyles.generateGSstyles(values, elType, this.styles),
                    element_id: element_id
                };
            ThemifyStyles.disableNestedSel = true;
            if (this.liveInstance === null) {
                this.liveInstance = api.createStyleInstance();
                this.liveInstance.init(true, true,model);
            }
            const css = this.createCss([res], elType),
                live = this.liveInstance,
                fonts = [],
                oldBreakpoint = api.activeBreakPoint,
                prefix = live.prefix,
                re = new RegExp(prefix, 'g');
                
            ThemifyStyles.disableNestedSel = null;
            if (isRemove === true) {
                const points = ThemifyConstructor.breakpointsReverse;
                for (let i = points.length - 1; i > -1; --i) {
                    api.activeBreakPoint = points[i];
                    live.setMode(points[i], true);
                    let stylesheet = live.currentSheet,
                        rules = stylesheet.cssRules || stylesheet.rules;
                    for (let j = rules.length - 1; j > -1; --j) {
                        if (rules[j].selectorText.indexOf(prefix) !== -1) {
                            let sel = rules[j].selectorText.replace(/\,\s+/g, ',').replace(re, '').split(','),
                                st = rules[j].cssText.split('{')[1].split(';');
                            if (sel[0].indexOf('.tb_text_wrap') !== -1) {
                                for (let s = sel.length - 1; s > 0; --s) {
                                    if (sel[s].indexOf('.tb_text_wrap') !== -1) {
                                        sel.splice(s, 1);
                                    }
                                }
                            }
                            for (let k = st.length - 2; k > -1; --k) {
                                live.setLiveStyle(st[k].trim().split(': ')[0].trim(), '', sel);
                            }
                        }
                    }
                }
            }
            delete css.gs;

            for (let i in css) {
                if ('fonts' === i || 'cf_fonts' === i) {
                    for (let f in css[i]) {
                        let v = f;
                        if (css[i][f].length > 0) {
                            v += ':' + css[i][f].join(',');
                        }
                        fonts.push(v);
                    }
                } else {
                    api.activeBreakPoint = i;
                    live.setMode(i, true);

                    for (let j in css[i]) {
                        let sel = j.replace(/\,\s+/g, ',').replace(re, '').split(',');
                        for (let k = 0, len = css[i][j].length; k < len; ++k) {
                            let tmp = css[i][j][k].split(';');
                            for (let k2 = tmp.length - 2; k2 > -1; --k2) {
                                if (tmp[k2] !== '') {
                                    let prop = tmp[k2].split(':')[0],
                                        v = tmp[k2].replace(prop + ':', '').trim();
                                    if (prop === 'background-image' && tmp[k2].indexOf('svg') !== -1 && tmp[k2].indexOf('data:') !== -1) {
                                        v += ';' + tmp[k2 + 1];
                                    }

                                    live.setLiveStyle(prop, v, sel);
                                }
                            }
                        }
                    }
                }
            }
            if (fonts.length > 0) {
                ThemifyConstructor.font_select.loadGoogleFonts(fonts.join('|'));
            }
            api.activeBreakPoint = oldBreakpoint;
            this.updated(css, res, values,model);
            this.liveInstance = null;
        },
        // Live edit GS
        gsEdit(id) {
            let m,
                isRightclick = false;
            if (api.activeModel !== null) {
                this.previousId = api.activeModel.id;
            }
            else if (api.ActionBar.contextMenu !== null && api.ActionBar.contextMenu.contains(this.field)) {
                const clicked = api.Instances.Builder[api.builderIndex].el.tfClass('tb_element_clicked')[0];
                if (clicked === und) {
                    return;
                }
                api.activeModel = api.Registry.get(clicked.dataset.cid);
                this.previousId = api.activeModel.id;
                isRightclick = true;
                api.Utils.scrollTo(clicked);
            } 
            const gsPost = this.styles[id],
                done = ThemifyConstructor.label.done,
                origLive = api.mode === 'visual' ? api.Helper.cloneObject(api.liveStylingInstance) : null,
                args = api.Helper.cloneObject(gsPost.data[0]),
                type = gsPost.type;
            this.activeGS = id;
            if (type === 'row') {
                delete args.cols;
                delete args.styling[this.key];
                m = new api.Row(args);
            } 
            else {
                if (type === 'subrow') {
                    delete args.cols;
                    delete args.styling[this.key];
                    m = new api.SubRow(args);
                } 
                else {
                    delete args.styling;
                    if (type === 'column') {
                        delete args.cols[0].modules;
                        delete args.cols[0].styling[this.key];
                        m = new api.Column(args.cols[0]);
                    } 
                    else {
                        delete args.cols[0].styling;
                        delete args.cols[0].modules[0].mod_settings[this.key];
                        m = new api.Module(args.cols[0].modules[0]);
                    }
                }
            }
            console.log(m);
            api.LightBox.el.className += ' gs_post';
            ThemifyConstructor.label.done = ThemifyConstructor.label.s_s;

            //api.ActionBar.hideContextMenu();
            
            m.edit('styling').then(lb=>{console.log(lb);
                lb.tfClass('current')[0].tfClass('tb_tooltip')[0].textContent = ThemifyConstructor.label.g_s + ' - ' + gsPost.title;
                const revertChange = () => {
                    Themify.off('themify_builder_lightbox_close', revertChange)
                        .off('themify_builder_save_component', saveComponent);
                    lb.classList.remove('gs_post');
                    ThemifyConstructor.label.done = done;
                    if (api.mode!=='visual' && this.previousId !== null && (type === 'row' || type === 'column' || type === 'subrow')) {
                        const tmp_m = api.Registry.get(this.previousId);
                        if (tmp_m && tmp_m.type === 'module') {console.log(api.liveStylingInstance);
                            api.liveStylingInstance.removeBgSlider();
                            api.liveStylingInstance.removeBgVideo();
                            api.liveStylingInstance.removeFrames();
                            const overlay=api.liveStylingInstance.getComponentBgOverlay();
                            if(overlay){
                                overlay.remove();
                            }
                            api.liveStylingInstance.el.classList.remove('builder-zoom-scrolling', 'builder-zooming');
                        }
                    }
                    m.destroy(true);
                    this.reopenPreviousPanel();
                    this.activeGS = this.previousId =null;
                },
                saveComponent=(settings,oldSettings)=>{
                        const id = this.activeGS,
                        gsPost = this.styles[id],
                        prevModel = this.previousId;
                    delete ThemifyConstructor.values.cid;
                    const data = api.Helper.cloneObject(settings),
                        oldModel = api.activeModel;
                    delete data[this.key];
                    if ('row' === type || type === 'subrow') {
                        gsPost.data[0].styling = data;
                        delete gsPost.data[0].cols;
                    } else {
                        delete gsPost.data[0].styling;
                        delete gsPost.data[0].cols[0].grid_class;
                        if ('column' === type) {
                            delete gsPost.data[0].cols[0].modules;
                            gsPost.data[0].cols[0].styling = data;
                        } else {
                            delete gsPost.data[0].cols[0].styling;
                            gsPost.data[0].cols[0].modules[0].mod_settings = data;

                        }
                    }
                    api.Spinner.showLoader();
                    try{
                        api.Utils.saveCss(gsPost.data, '', gsPost.id).then(()=>{
                            this.styles[id].data = gsPost.data;
                            if (api.mode === 'visual') {
                                const items = api.Registry.items;
                                for (let [cid,model] of items) {
                                    let args = model.get('styling');
                                    if (args[this.key] && args[this.key].indexOf(id) !== -1) {
                                        this.setGsStyle(args[this.key].split(' '), true,model);
                                    }
                                }
                                this.liveInstance = null;
                            }

                            const ajaxData={
                                action:'tb_update_global_style',
                                bid: gsPost.id,
                                data: gsPost.data
                            };
                            api.LocalFetch(ajaxData).then(() => {
                                api.Spinner.showLoader('done');
                                revertChange(true);
                            }); 
                        });
                    }
                    catch(e){
                        this.activeGS = this.previousId=null;
                        api.Spinner.showLoader('error');
                    }
                };
                Themify.on('themify_builder_lightbox_close', revertChange,true)
                    .on('themify_builder_save_component', saveComponent,true);
            }).catch(e=>{
                console.log(e);
            });
        },
        // Open prevous module panel
        reopenPreviousPanel(triggerData) {
            if (null !== this.previousId) {
                const model = api.Registry.get(this.previousId);
                if (model !== null) {
                    model.edit('styling');
                }
                this.previousId = null;
            }
        },
        extraStyle(css, res, values,model) {
            let live = this.liveInstance !== null ? this.liveInstance : api.liveStylingInstance,
                prefix = live.prefix,
                start = prefix.length - 1,
                exist = live.getComponentBgOverlay(model.type)!==null,
                el = live.el,
                hasOverlay = exist,
                sides = {
                    top: false,
                    bottom: false,
                    left: false,
                    right: false
                },
                framesCount = 0,
                parallaxClass = 'builder-parallax-scrolling',
                zoomClass = 'builder-zoom-scrolling';
            loop:
                for (let i in css) {
                    if ('fonts' !== i && 'cf_fonts' !== i && 'gs' !== i) {
                        for (let j in css[i]) {
                            if (hasOverlay === false) {
                                hasOverlay = j.indexOf('builder_row_cover', start) !== -1;
                            }
                            if (j.indexOf('tb_row_frame', start) !== -1) {
                                for (let f in sides) {
                                    if (sides[f] === false && j.indexOf('tb_row_frame_' + f, start) !== -1) {
                                        sides[f] = true;
                                        ++framesCount;
                                        break;
                                    }
                                }
                            }
                            if (hasOverlay === true && framesCount === 4) {
                                break loop;
                            }
                        }
                    }
                }
            css = null;
            if (exist === false && hasOverlay === true) {
                live.addOrRemoveComponentOverlay();
            }
            if (framesCount > 0) {
                let fr = doc.createDocumentFragment(),
                  frame_wrap = el.querySelector(':scope>.tb_row_frame_wrap');
                if (!frame_wrap) {
                    frame_wrap = doc.createElement('div');
                    frame_wrap.className = 'tb_row_frame_wrap tf_overflow tf_abs';
                    el.tfClass('tb_'+model.type+'_action')[0].after(frame_wrap);
                }
                for (let f in sides) {
                    if (sides[f] === true && !frame_wrap.tfClass('tb_row_frame_' + f)[0]) {
                        let frame = doc.createElement('div');
                        frame.className = 'tf_abs tf_overflow tf_w tb_row_frame tb_row_frame_' + f;
                        fr.appendChild(frame);
                    }
                }
                frame_wrap.appendChild(fr);
            }
            let bgType = res.styling !== und ? res.styling.background_type : 'none';
            if (!bgType) {
                bgType = 'image';
            }
            if (bgType === 'image' && res.styling.background_repeat === parallaxClass && res.styling.background_image) {
                el.classList.add(parallaxClass);
                Themify.reRun(el, true);
            } else {
                el.classList.remove(parallaxClass);
                el.style.backgroundPosition = '';
                if (bgType === 'image' && res.styling.background_repeat === zoomClass && res.styling.background_image) {
                    el.classList.add(zoomClass);
                    Themify.reRun(el, true);
                } else {
                    el.classList.remove(zoomClass);
                    el.style.backgroundSize = '';
                }
            }
            return;
        },
        reset(){
            this.styles={};
            searchCache={};
            this.allLoaded=false;
        }
    };
    api.GS.init();
})(tb_app, Themify, window.top, document,undefined);