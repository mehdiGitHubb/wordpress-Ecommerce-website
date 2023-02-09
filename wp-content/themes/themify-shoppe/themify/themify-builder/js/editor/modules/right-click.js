((api, Themify, doc) => {
    'use strict';
    const contextMenu=e=>{
        api.RightClick.show(e);
    },
    mouseDown=e=>{
        const self=api.RightClick;
        if(e.which===1 && !self.el.contains(e.target) && e.target!==self.el.getRootNode().host){
            self.hide();
        }
    },
    click=e=>{
        api.RightClick.clickEvent(e);
    },
    hover=e=>{
        api.RightClick.hoverEvent(e);
    };
    api.RightClick = {
        init() {
            const  root = doc.tfId('tb_builder_right_click_root'),
                fr = root.firstElementChild;
            if (fr) { // shadowroot="open" isn't support
                root.attachShadow({
                    mode: fr.getAttribute('shadowroot')
                }).appendChild(fr.content);
                fr.remove();
            }
            this.el = root.shadowRoot.tfId('menu');
            const promise = new Promise(resolve => {
                Themify.on('tb_toolbar_loaded', () => {
                    const toolbarRoot = api.ToolBar.el.getRootNode(),
                        fragment = doc.createDocumentFragment();
                    fragment.append(api.ToolBar.getBaseCss(),api.LightBox.el.querySelector('#module_breadcrumbs_style').cloneNode(true));
                    this.el.getRootNode().prepend(fragment);
                    resolve();
                }, true, (api.ToolBar !== undefined && api.ToolBar.isLoaded === true));
            });
            Promise.all([Themify.loadCss(Themify.builder_url + 'css/editor/modules/right-click', null,null, this.el), promise]).then(() => {
                this.bind();
            });
        },
        bind(){
            Themify.on('themify_builder_ready',()=>{
                const builder=api.Builder.get(0).el;
                if (!localStorage.getItem('tb_right_click')) {
                    builder.tfOn('contextmenu',contextMenu);
                    doc.tfOn('pointerdown',mouseDown,{passive:true});
                    if(api.mode==='visual'){
                        window.top.document.tfOn('pointerdown',mouseDown,{passive:true});
                    }
                    this.el.tfOn(Themify.click, click)
                    .tfOn('pointerover', hover, {passive: true});
                }
                else{
                    this.hide();
                    builder.tfOff('contextmenu',contextMenu);
                    doc.tfOff('pointerdown',mouseDown,{passive:true});
                    if(api.mode==='visual'){
                        window.top.document.tfOff('pointerdown',mouseDown,{passive:true});
                    }
                    this.el.tfOff(Themify.click, click)
                    .tfOff('pointerover', hover, {passive: true});
                }
            }, true,api.is_builder_ready);
        },
        show(e) {
            const el=e.target.closest('[data-cid]');
            if (!el || api.isPreview || (api.mode === 'visual' && doc.activeElement.contentEditable && api.liveStylingInstance.el && api.liveStylingInstance.el.contains(doc.activeElement))) {
                this.hide();
                return;
            }
            const model=api.Registry.get(el.dataset.cid);
            if(model.isEmpty===true){
                return;
            }
            
            e.stopImmediatePropagation();
            e.preventDefault();
            this.el.getRootNode().host.classList.remove('tf_hide');
            doc.body.classList.add('tb_right_click_open');
            let left = e.pageX,
                top = e.pageY,
                selected;
                
            const type = model.type,
                scrollY=window.scrollY,
                textEl = this.el.tfClass('name')[0];
            this.el.className='tf_abs_t tf_hidden ' + type;
            if (!api.undoManager.hasUndo()) {
                this.el.classList.add('undo_disabled');
            }
            if (!api.undoManager.hasRedo()) {
                this.el.classList.add('redo_disabled');
            }
            if (type === 'column') {
                this.el.classList.add('visibility_disabled');
            }
            else if (type === 'module') {
                this.el.classList.add('tb_module_' + model.get('mod_name'));
            }
            if (e.ctrlKey === true || e.metaKey === true) {
                el.classList.add('tb_element_clicked');
            } 
            else if (!el.classList.contains('tb_element_clicked')) {
                selected = api.Builder.get().el.tfClass('tb_element_clicked');
                for (let i = selected.length - 1; i > -1; --i) {
                    selected[i].classList.remove('tb_element_clicked');
                }
                el.classList.add('tb_element_clicked');
            }
            selected = api.Builder.get().el.tfClass('tb_element_clicked').length;

            if (selected > 1) {
                this.el.classList.add('is_multiply');
                textEl.textContent = themifyBuilder.i18n.multiSelected;
                this.el.tfClass('tb_action_breadcrumb')[0].innerHTML='';
            } else {
                textEl.textContent = type === 'module' ? model.getName() : type;
                model.setBreadCrumbs(this.el);
            }
            if(left<0){
                left=0;
            }
            else if((left+this.el.offsetWidth)>doc.documentElement.offsetWidth){
                left=doc.documentElement.offsetWidth-this.el.offsetWidth-5;
            }
            if(top<0){
                top=0;
            }
            else if(top<scrollY){
                top=scrollY+5;
            }
            else if((top+this.el.offsetHeight+5)>(scrollY+Themify.h)){
                top=scrollY+Themify.h-this.el.offsetHeight-api.ToolBar.el.offsetHeight-5;
            }
            this.el.style.transform='translate('+left+'px,'+top+'px)';
            this.el.classList.add('tb_show_context');
        },
        hide() {
            const root=this.el.getRootNode(),
                gsInput=root.querySelector('#global_styles');
            root.host.classList.add('tf_hide');
            this.el.style.transform='';
            const gs = root.querySelector('.inline_gs');
            if(gs){
                gs.remove();
            }
            if(gsInput){
                gsInput.remove();
            }
            doc.body.classList.remove('tb_right_click_open');
        },
        clickEvent(e) {
            e.stopPropagation();
            e.preventDefault();
            const bread=e.target.closest('.tb_bread');
            if(bread){
                const model=api.Registry.get(bread.dataset.id);
                Themify.triggerEvent(model.el,'contextmenu',null,true);
                return;
            }
            const item=e.target.closest('[data-action]');
            if(!item){
                return;
            }
            const hasMulti=item.classList.contains('not_multi'),
            action=item.dataset.action;
            
            if(action==='undo' || action==='redo'){
                api.undoManager.changes(action==='undo');
            }
            else{
                const selected = api.Builder.get().el.tfClass('tb_element_clicked');
                if(action==='gs_in' || action==='gs_r'){
                    if(action==='gs_in'){
                        const gs=doc.createElement('tb-gs'),
                            pos=this.el.style.transform;
                        gs.className='inline_gs tf_abs_t';
                        gs.style.transform=pos+' scale(0)';
                        this.el.after(gs);
                        gs.init();
                        this.el.tfOn('transitionend',e=>{
                            gs.style.transform=pos+' scale(1)';
                        },{passive:true,once:true})
                        .style.transform=pos+' scale(0)';
                        return;
                    }
                }
                else{
                    const model=api.Registry.get(selected[0].dataset.cid);
                    if(model){
                        if(action==='reset'){
                            api.undoManager.start('resetStyling');
                            for(let i=selected.length-1;i>-1;--i){
                                let m=api.Registry.get(selected[i].dataset.cid);
                                if(m){
                                    ThemifyConstructor.resetStyling(m);
                                }
                            }
                            api.undoManager.end('resetStyling');
                        }
                        else if(action==='save'){
                            model.save(this.el.getBoundingClientRect());
                        }
                        else if(action==='edit' || action==='styling' || action==='visibility'){
                            model.edit(action);
                        }
                        else{
                           api.ActionBar.actions({action:action,target:this.el,shiftKey:item.classList.contains('style')});  
                        }
                    }
                }
            }
            this.hide();
        },
        hoverEvent(e) {
            e.stopPropagation();
            if (e.target.classList.contains('inner_more')) {
                const innerDropdown = e.target.tfTag('ul')[0];
                if (innerDropdown) {
                    api.Utils.addViewPortClass(innerDropdown);
                }
            }
        }
    };
    api.RightClick.init();
})(tb_app, Themify, document);