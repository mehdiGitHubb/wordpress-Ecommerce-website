((api, Themify, doc, topWindow) => {
    'use strict';
    api.LayoutPart = class {
        constructor(id) {
            this.id = id;
        }
        edit() {
            const id = this.id,
                self = api.LayoutPart;
            return new Promise(async(resolve, reject) => {
                const model = api.Registry.get(id);
                if (!model) {
                    reject();
                    return;
                }
                await api.LightBox.save();
                api.ActionBar.clear();
                let module = model.el.closest('.active_module'),
                    builder = module.tfClass('themify_builder_content')[0],
                    builderId = builder.dataset.postid,
                    callback = async() => {
                        doc.body.classList.add('tb_layout_part_edit');
                        topWindow.document.body.classList.add('tb_layout_part_edit');
                        api.ToolBar.el.classList.add('tb_layout_part_edit');

                        const oldBuilder = api.Builder.get(),
                                overlay = doc.createElement('div'),
                                modules = module.closest('.row_inner').tfClass('active_module');

                        overlay.className = 'tb_overlay tf_overflow tf_abs tf_w tf_h';
                        oldBuilder.el.prepend(overlay);


                        for (let i = modules.length - 1; i > -1; --i) {
                            if (modules[i] !== module) {
                                modules[i].prepend(overlay.cloneNode());
                            }
                        }
                        ThemifyStyles.builder_id = builderId;
                        const id = 'themify_builder_content-' + builderId,
                                settings = [];

                        for (let allBuilders = doc.tfClass(id), i = allBuilders.length - 1; i > -1; --i) {
                            let m = allBuilders[i].closest('.active_module');
                            if (m) {
                                let css = m.tfClass('themify-builder-generated-css')[0];
                                if (css) {
                                    css.setAttribute('disabled', 'disabled');
                                }
                            }
                        }
                        oldBuilder.el.classList.remove('tb_active_builder');
                        module.classList.add('tb_active_layout_part');
                        module.classList.remove('active_module', 'module');

                        const holder = module.closest('.tb_holder'),
                                height = builder.offsetHeight;
                        holder.classList.add('tb_layout_part_parent');
                        holder.classList.remove('tb_holder');
                        holder.closest('.module_row').classList.add('tb_active_layout_part_row');
                        this.el = module;
                        let newBuilder = new api.Builder(builder, api.Helper.correctBuilderData(self.cache[builderId].builder_data), self.cache[builderId].custom_css);
                        for (let items = newBuilder.el.querySelectorAll('[data-cid]'), i = items.length - 1; i > -1; --i) {
                            settings.push(items[i].dataset.cid);
                        }
                        newBuilder.el.style.height = height + 'px';//avoid page jumping
                        for (let rows = newBuilder.el.children, i = rows.length - 1; i > -1; --i) {
                            if (!rows[i].dataset.cid) {
                                rows[i].remove();
                            }
                        }
                        try{
                            await api.bootstrap(settings);
                            newBuilder.el.style.height = '';
                            try{
                                await api.correctColumnPaddings();
                            }
                            catch(e){

                            }
                            api.Utils.runJs(newBuilder.el);
                            api.Registry.trigger(newBuilder, 'tb_init');
                            await api.Spinner.showLoader('done');
                            this.toolbar.getRootNode().host.classList.remove('tf_hide');
                            api.activeModel = null;
                            this.toolbar.tfOn(Themify.click, e => {
                                e.stopPropagation();
                                const target = e.target;
                                if(target.closest('a')){
                                    e.preventDefault();
                                }
                                if (target.closest('.tf_close')) {
                                    this.close();
                                } else if (target.closest('.save')) {
                                    this.save();
                                } else if (target.closest('.layout')) {
                                    api.ToolBar.initLayout(e);
                                } else if (target.closest('.import')) {
                                    api.ToolBar.initImport(e);
                                } else if (target.closest('.export')) {
                                    api.ToolBar.initExport(e);
                                } else if (target.closest('.undo_redo')) {
                                    api.undoManager.doChange(e);
                                } else if (target.closest('.custom_css')) {
                                    api.ToolBar.addCustomCSS(e);
                                }
                            });
                            const stackCopy = [];
                            for (let stack = api.undoManager.stack, i = 0, len = stack.length; i < len; ++i) {
                                stackCopy[i] = api.Helper.cloneObject(stack[i]);
                            }
                            this.undo = stackCopy;
                            api.undoManager.btnUndo = this.toolbar.tfClass('undo')[0];
                            api.undoManager.btnRedo = this.toolbar.tfClass('redo')[0];
                            api.undoManager.reset();
                            module = newBuilder = builder = builderId = null;

                            if(!Themify.isTouch){
                                overlay.tfOn('dblclick',e=>{
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                    this.save().then(()=>{
                                       this.close(); 
                                    });
                                },{once:true});
                            }
                            resolve();
                        }
                        catch(e){
                            api.Spinner.showLoader('error');
                            this.toolbar.getRootNode().host.remove();
                            this.html = this.toolbar =null;
                            reject();
                        }
                    },
                    createElement = () => {
                        this.html = api.Helper.cloneDom(module).innerHTML;
                        const root = doc.createElement('div'),
                                tpl = doc.tfId('tmpl-small_toolbar'),
                                fragment = doc.createDocumentFragment(),
                                styles = api.ToolBar.el.getRootNode().querySelectorAll('style,#tf_svg');

                        root.id = 'tb_small_toolbar_root';
                        root.className = 'tf_w tf_hide';
                        root.attachShadow({
                            mode: 'open'
                        }).appendChild(tpl.content.cloneNode(true));

                        for (let i = 0, len = styles.length; i < len; ++i) {
                            fragment.appendChild(styles[i].cloneNode(true));
                        }
                        root.shadowRoot.prepend(fragment);

                        this.toolbar = root.shadowRoot.tfId('toolbar');
                        module.prepend(root);

                    };
                if (self.cache[builderId] !== undefined) {
                    createElement();
                    callback();
                    return;
                }
                try{
                    const res=await api.LocalFetch({action: 'tb_layout_part_swap', bid: builderId});
                    if (res.builder_data) {
                        self.cache[builderId] = res;
                        if (res.used_gs) {
                            api.GS.styles = ThemifyStyles.extend(true, {}, res.used_gs, api.GS.styles);
                        }
                        createElement();
                        callback();
                    } else {
                        throw 'Error';
                    }
                }
                catch(e){
                    api.Spinner.showLoader('error');
                    this.toolbar.getRootNode().host.remove();
                    this.html = this.toolbar =null;
                    reject();
                }
            });
        }
        restore() {
            this.toolbar.getRootNode().host.remove();
            api.LightBox.close();
            const holder = this.el.closest('.tb_layout_part_parent'),
                    currentBuilder = api.Builder.get();
            this.el.classList.add('active_module', 'module');
            this.el.classList.remove('tb_active_layout_part');

            holder.classList.add('tb_holder');
            holder.classList.remove('tb_layout_part_parent');

            holder.closest('.module_row').classList.remove('tb_active_layout_part_row');

            currentBuilder.destroy();

            for (let overlay = doc.tfClass('tb_overlay'), i = overlay.length - 1; i > -1; --i) {
                overlay[i].remove();
            }

            api.undoManager.stack = this.undo;
            api.undoManager.index = this.undo.length - 1;
            api.undoManager.btnUndo = api.ToolBar.el.tfClass('undo')[0];
            api.undoManager.btnRedo = api.ToolBar.el.tfClass('redo')[0];
            api.undoManager.updateUndoBtns();
            ThemifyStyles.builder_id = api.Builder.get().id;
            api.ActionBar.clear();
            api.Builder.get().el.classList.add('tb_active_builder', 'tf_rel');

            doc.body.classList.remove('tb_layout_part_edit');
            topWindow.document.body.classList.remove('tb_layout_part_edit');
            api.ToolBar.el.classList.remove('tb_layout_part_edit');
            Themify.trigger('tb_resotre_layout_part');
            this.undo = this.html = this.el =api.activeModel =  this.cssFile=api.LayoutPart.item=null;
        }
        async close() {
            if (api.Builder.get().isSaved === false && api.undoManager.hasUndo()) {
                const answer= await api.LiteLightBox.confirm({
                    msg: themifyBuilder.i18n.layoutEditConfirm
                });
                if (answer !== 'yes') {
                    return false;
                }
            }
            const id = 'themify_builder_content-' + api.Builder.get().id;
            if (this.cssFile) {
                api.Spinner.showLoader();
                const model = api.Registry.get(this.el.dataset.cid);
                try{
                    await model.trigger('ajax', Object.assign({unsetKey: true}, model.get('mod_settings')));
                    const html = model.el.tfClass('themify_builder_content')[0].innerHTML;
                    let link;
                    if (this.cssFile!==true) {
                        link = doc.createElement('link');
                        link.href = this.cssFile;
                        link.rel = 'stylesheet';
                        link.className = 'themify-builder-generated-css';
                    }
                    //Data is saved in DB,that is why user can't undo/redo it, we need to replace html in all undo states
                    for (let i = this.undo.length - 1; i > -1; --i) {
                        let item = this.undo[i].html;
                        if (item) {
                            for (let j = item.length - 1; j > -1; --j) {
                                for (let arr = ['old', 'new'], k = arr.length - 1; k > -1; --k) {
                                    let builder = item[j][arr[k]];
                                    if (builder) {
                                        let layoutPart = builder.tfClass( id)[0];
                                        if (layoutPart) {
                                            layoutPart.innerHTML = html;
                                            let parent = layoutPart.closest('.module');
                                            for (let child = parent.children, j = child.length - 1; j > -1; --j) {
                                                if (child[j].tagName === 'LINK' && child[j].classList.contains('themify-builder-generated-css')) {
                                                    child[j].remove();
                                                }
                                            }
                                            if (link) {
                                                parent.prepend(link.cloneNode(true));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    this.restore();
                    //replace others layouts in the page
                    for (let builder = doc.tfClass( id), i = builder.length - 1; i > -1; --i) {
                        let p = builder[i].closest('.module');
                        for (let child = builder[i].children, j = child.length - 1; j > -1; --j) {
                            if (child[j].tagName === 'LINK' && child[j].classList.contains('themify-builder-generated-css')) {
                                child[j].remove();
                            }
                        }
                        if (link) {
                            p.prepend(link.cloneNode(true));
                        }
                        if (model.id !== p.dataset.cid) {
                            builder[i].innerHTML = html;
                            api.Utils.runJs(builder[i]);
                        }
                    }
                    api.Spinner.showLoader('hide');
                }
                catch(e){
                    api.Spinner.showLoader('error');
                    throw e;
                }
            }
            else{
                if(this.html){
                    this.el.innerHTML = this.html;
                }
                this.restore();
                for (let allBuilders = doc.tfClass(id), i = allBuilders.length - 1; i > -1; --i) {
                    let m = allBuilders[i].closest('.active_module');
                    if (m) {
                        let css = m.tfClass('themify-builder-generated-css')[0];
                        if (css) {
                            css.removeAttribute('disabled');
                        }
                    }
                }

                api.Utils.runJs(this.el);
            }
        }
        async save() {
			const cl=this.toolbar.tfClass('save_wrap')[0].classList;
			try{
				if(cl.contains('disabled')){
					throw 'isWorking';
				}
				cl.add('disabled');
				await api.LightBox.save();
				api.Spinner.showLoader();
				const builder = api.Builder.get(),
					id = builder.id,
					self = api.LayoutPart,
					res=await  builder.save();
					self.cache[id] = {builder_data: res.builder_data};
					if (builder.custom_css) {
						self.cache[id].custom_css = builder.custom_css;
					}
			   //     TF_Notification.showHide('done', themifyBuilder.i18n.layoutPartSaved);
					this.cssFile = res.css_file || true;//once it saved always should get the last html after close
					this.html=null;
			}
			catch(e){
				
			}
			cl.remove('disabled');
        }
    }
    api.LayoutPart.cache = {};
    api.OverlayContent = class extends api.LayoutPart{
        constructor(id) {
            super(id)
        }
    }

})(tb_app, Themify, document, window.top);