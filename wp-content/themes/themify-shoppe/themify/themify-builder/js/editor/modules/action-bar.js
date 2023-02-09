((api,Themify, doc, topWindow,und) => {
    'use strict';
    const row=doc.tfId('tmpl-builder_row_action').content,
        column=doc.tfId('tmpl-builder_column_action').content,
        subrow=doc.tfId('tmpl-builder_subrow_action').content,
        module=doc.tfId('tmpl-builder_module_action').content;
        
        
    class Bar extends HTMLElement {
        connectedCallback () {
            const tpl=this.constructor.template.cloneNode(true),
                menu=tpl.querySelector('.dropdown'),
                showTab=(target,type)=>{
                    let root=target.getRootNode(),
                        tabId = target.dataset.href; 
                    if(tabId==='options'){
                        root.querySelector('#'+tabId).classList.add('selected');
                        tabId=root.querySelector('.row_menu .selected');
                        tabId=tabId!==null?tabId.dataset.href:'grid';
                    }
                    const el=root.querySelector('#'+tabId),
                        grid=root.querySelector('#grid'),
                        cid=root.host.closest('[data-cid]').dataset.cid,
                        hide=tabId === 'grid'?root.querySelector('#row_options'):grid,
                        optionTab=root.querySelector('#options');
                       
                        if(el.childElementCount<2){
                            if (tabId === 'grid') {
                                api.Registry.trigger(cid,'gridMenu',grid);
                            }
                            else{
                                api.Registry.trigger(cid,'optionsTab',el);
                            }
                        }
                        for(let nav=target.parentNode.children,i=nav.length-1;i>-1;--i){
                            nav[i].classList.toggle('selected',nav[i]===target);
                        }
                        el.classList.add('selected');
                        if(hide!==null){
                            hide.classList.remove('selected');
                        }
                        if(type!=='click' && optionTab!==null){
                            api.Utils.addViewPortClass(optionTab);
                        }
                        
                };
                menu.tfOn('pointerover',function(e){
                        e.stopPropagation();
                        const root=this.getRootNode(),
                            bodyCl=doc.body.classList,
                            target=e.target,
                            inner_menu=target.classList.contains('more') || target.classList.contains('inner_more')?target.tfClass('menu')[0]:null;
                        
                        if(this.classList.contains('module')){
                            root.host.closest('.active_module').classList.add('tb_active_action_bar');
                        }
                        if (inner_menu || target.classList.contains('inner_more') || target.classList.contains('menu')) {
                            api.Utils.addViewPortClass((inner_menu?inner_menu:target));
                        }
                        if(target.hasAttribute('data-href')){
                            showTab(target);
                        }
                        else {
                            const tab=root.querySelector('.tab');
                            if(tab!==null){
                                for(let nav=this.children,i=nav.length-1;i>-1;--i){
                                    nav[i].classList.remove('selected');
                                }
                                tab.classList.remove('selected');
                            }
                        }
                        if(!bodyCl.contains('tb_action_bar_hover')){
                            bodyCl.add('tb_action_bar_hover');
                            this.tfOn('pointerleave',e=>{
                                bodyCl.remove('tb_action_bar_hover');
                            },{passive:true,once:true});
                        }
                        
                },{passive:true});
                
                this.attachShadow({ mode:'open'}).appendChild(tpl);
                this.shadowRoot.tfOn(Themify.click,function(e){
                    e.stopPropagation();
                    const action=e.target.closest('[data-action]');
                    if(action){
                        let module=api.Registry.get(this.host.closest('[data-cid]').dataset.cid),
                            actionName=action.dataset.action;
                        if(actionName==='edit' || actionName==='styling' || actionName==='visibility' || actionName==='swap'){
                            if(actionName==='edit' && module.type==='module'){
                                const swap=this.querySelector('.swap');
                                if(swap && swap.offsetParent!==null){
                                    actionName='editBuilder';
                                }
                            }
                            module.edit(actionName);
                        }
                        else if(actionName==='add_col'){
                            const to=module.el,
                                col=new api.Column({},module.type==='subrow');
                            api.undoManager.start('move');
                            api.Drop.column(col.el,to,'right').then(()=>{
                                api.undoManager.end('move');
                            });
                        }
                        else if(actionName==='up' || actionName==='down'){
                            const nextEl= actionName==='up'?module.el.previousElementSibling:module.el.nextElementSibling,
                                offset=parseInt(getComputedStyle(doc.querySelector(':root')).getPropertyValue('--tb_toolbar_h'));
                            api.ActionBar.clear();
                            module.el.classList.add('tb_draggable_item');
                            api.undoManager.start('move');
                            actionName==='up'?nextEl.before(module.el):nextEl.after(module.el);
                            Themify.trigger('tb_' + module.type + '_sort', [module.el]);
                            api.undoManager.end('move');
                            module.el.classList.remove('tb_draggable_item');
                            api.Utils.scrollTo(module.el,offset,{behavior:'smooth'});
                        }
                        else{
                            module.trigger(actionName,(actionName==='paste'?e.target.classList.contains('style'):e.target));
                        }
                    }
                    else if(e.target.hasAttribute('data-href')){
                        showTab(e.target,'click');
                    }
                },{passive:true});
               
        }
        disconnectedCallback(){
            doc.body.classList.remove('tb_action_bar_hover');
        }
    }
    class RowBar extends Bar {
    }
    class ColumnBar extends Bar {
    }
    class SubrowBar extends Bar {
    }
    class ModuleBar extends Bar {
    }
    RowBar.template=row;
    ColumnBar.template=column;
    SubrowBar.template=subrow;
    ModuleBar.template=module;
    customElements.define('tb-row-bar', RowBar);
    customElements.define('tb-column-bar', ColumnBar);
    customElements.define('tb-subrow-bar', SubrowBar);
    customElements.define('tb-module-bar', ModuleBar);
    
    api.ActionBar =  {
        cid:null,
        disable:null,
        breadCrumbs:null,
        disablePosition:null,

        init() {
            const actionBarCss=module.querySelector('style'),//we are rendreing the inline css ONLY in module bar on the page loading
            fr=doc.createDocumentFragment(),
            gridCss=row.querySelector('#module_row_grids_style'),
            formFields=row.querySelector('#module_form_fields_style');

            fr.appendChild(api.ToolBar.getBaseCss());
            module.prepend(fr.cloneNode(true));
            fr.appendChild(actionBarCss.cloneNode(true));

            row.prepend(fr.cloneNode(true));

            column.prepend(fr.cloneNode(true));
            fr.appendChild(formFields.cloneNode(true));

            subrow.prepend(fr,gridCss.cloneNode(true));
            if(api.mode==='visual'){
                topWindow.document.head.prepend(formFields.cloneNode(true));
            }
            else{
                topWindow.document.head.appendChild(formFields.cloneNode(true));
            }
            if (api.isGSPage === true) {
                return;
            }
            this.breadCrumbs = doc.createElement('ul');
            this.breadCrumbs.className = 'tb_action_breadcrumb';

            Themify.on('themify_builder_ready',()=>{
                const builder=api.Builder.get().el;


                builder.tfOn(Themify.click, e=>{

                    const target=e.target;
                    if(api.isDocked && target.classList.contains('tb_dragger')){
                        e.preventDefault();
                        e.stopPropagation();
                        api.EdgeDrag.openLightBox(target);
                        return;
                    }
                    if(target.closest('.tb_visibility_hint,.tb_row_info')){
                        const model=api.Registry.get(target.closest('[data-cid]').dataset.cid);
                        if(target.closest('.tb_visibility_hint')){
                            model.edit('visibility');
                        }
                        else{
                            model.edit().then(lb=>{
                                const cssField=lb.querySelector('.tb_field_group_css .tb_style_toggle.tb_closed');
                                if(cssField){
                                    Themify.triggerEvent(cssField, e.type);
                                }
                            })
                            .catch(()=>{

                            });
                        }
                        return;
                    }
                    this.click(e);

                })
                .tfOn('pointerover', e=>{
                    e.stopPropagation();
                    this.hover(e);
                },{passive: true});
                if(!Themify.isTouch){
                    builder.tfOn('pointerleave', e=>{
                        if(!e.relatedTarget || e.relatedTarget!==builder.ownerDocument.body){
                            this.clear();
                        }
                    },{passive: true})
                    .tfOn('dblclick', e=>{
                        if(e.target.tagName==='DIV' && e.target.classList.contains('tb_dragger')){
                            api.EdgeDrag.openLightBox(e.target);
                        }
                        else if(!api.isDocked){
                            const el = e.target.closest('[data-cid]');
                            if(el){
                                e.preventDefault();
                                const model = api.Registry.get(el.dataset.cid),
                                actionBar=model.el.tfTag('tb-'+model.type+'-bar')[0];
                                if(model.isEmpty!==true){
                                    if(actionBar){
                                        const editBtn=actionBar.shadowRoot.querySelector('.edit');
                                        if(editBtn){
                                            Themify.triggerEvent(editBtn,Themify.click);
                                        }
                                    }
                                    else{
                                        this.click(e);
                                    }
                                }
                            }
                        }
                    });
                    if(!themifyBuilder.disableShortcuts){
                        const canvas = api.mode === 'visual' ? null : doc.tfId('tb_canvas_block');

                        if (canvas === null) {
                            doc.tfOn('keydown', e=>{
                                this.actions(e);
                            });
                            topWindow.document.tfOn('keydown', e=>{
                                this.actions(e);
                            });
                        } else {
                            canvas.tfOn('keydown', e=>{
                                this.actions(e);
                            });
                        }
                    }
                }
            }, true,api.is_builder_ready);
        },
        actions(e) {
            const target = e.target,
                tagName = target.tagName;
                if (tagName !== 'INPUT' && tagName !== 'TEXTAREA' && !doc.activeElement.isContentEditable && !api.LightBox.el.contains(target)  && (api.mode !== 'visual' || !api.liveStylingInstance || !api.liveStylingInstance.el || !api.liveStylingInstance.el.contains(doc.activeElement))) {
                    const code = e.code,
                        items = api.Builder.get().el.tfClass('tb_element_clicked');
                    let len = items.length;
                    if (len > 0) {
                        let act=e.action,
                            pasteStyle=true;
                        if(!act){
                            if (code === 'Delete') {
                                act = 'delete';
                            } 
                            else if (e.ctrlKey === true || e.metaKey === true) {
                                if (code === 'KeyC') {
                                    act = 'copy';
                                } 
                                else if (code === 'KeyD') {
                                    act = 'duplicate';
                                } 
                                else if (code === 'KeyV') {
                                    act = 'paste';
                                }
                            }
                        }
                        if(act){
                            if(act==='copy'){
                                len=1;
                            }
                            else if(act==='paste'){
                                pasteStyle = e.shiftKey === true;
                            }
                            if(typeof e.preventDefault==='function'){
                                e.preventDefault();
                                e.stopPropagation();
                            }
                            const _callback=()=>{
                                const promis=[];
                                api.undoManager.start(act);
                                for (let i = len - 1; i > -1; --i) {
                                    let selected = items[i].closest('[data-cid]');
                                    if(selected!==null){
                                        promis.push(api.Registry.trigger(selected.dataset.cid,act,pasteStyle,true));
                                    }
                                }
                                if (act === 'delete') {
                                    this.clear();
                                }
                                Promise.all(promis).finally(()=>{//can be allSettled,but safari doesn't support it
                                    api.undoManager.end(act);
                                });
                            };
                            if(act === 'delete' || act === 'paste'){
                                api.LightBox.save().then(_callback).catch(e=>{});
                            }
                            else{
                                _callback();
                            }
                        }
                    }
                }
        },
        columnHover(el) {
            let subColumn = el.closest('.sub_column'),
                builder = api.Builder.get().el,
                items = builder.tfClass('tb_action_overlap'),
                col = subColumn === null ? el.closest('.module_column') : subColumn.parentNode.closest('.module_column');

            for (let i = items.length - 1; i > -1; --i) {
                items[i].classList.remove('tb_action_overlap');
            }
            items = builder.tfClass('tb_active_action_bar');
            for (let i = items.length - 1; i > -1; --i) {
                items[i].classList.remove('tb_active_action_bar');
            }
            items = builder.querySelectorAll('.tb_hide_drag_left,.tb_hide_drag_right');
            for (let i = items.length - 1; i > -1; --i) {
                items[i].classList.remove('tb_hide_drag_left', 'tb_hide_drag_right');
            }
            if (col !== null || subColumn !== null) {
                const cols = [col, subColumn];
                for (let i = cols.length - 1; i > -1; --i) {
                    if (cols[i] !== null) {
                        let left = cols[i].offsetLeft;
                        if (left === 0) {
                            cols[i].classList.add('tb_hide_drag_left');
                        }
                        if ((left + cols[i].offsetWidth) >= cols[i].parentNode.offsetWidth) {
                            cols[i].classList.add('tb_hide_drag_right');
                        }
                    }
                }
                if (subColumn !== null) {
                    const action = subColumn.tfClass('tb_column_action')[0];
                    if (action !== und) { 
                        let box1 = action.getBoundingClientRect(),
                            remove = true,
                            column = subColumn.parentNode.closest('.module_column'),
                            r = box1.left < 5 ? column.closest('.module_row').tfClass('tb_row_action')[0] : subColumn.closest('.module_subrow').tfClass('tb_subrow_action')[0];
                        if (r !== und) {
                            const box2 = r.getBoundingClientRect();
                            remove = Math.abs((box1.left - box2.left)) < box1.width ? Math.abs((box2.top - box1.top)) > box1.height : true;
                        }
                        action.classList.toggle('tb_action_overlap', remove === false);
                    }
                }
            }
            if (this.isFullWidth !== true) {
                const column = subColumn !== null ? subColumn.parentNode.closest('.module_column') : el.closest('.module_column');
                if (this.isFullWidth !== false) {
                    const row = column !== null ? column.closest('.module_row') : el.closest('.module_row');
                    if (row !== null) {
                        this.isFullWidth = row.offsetWidth < document.body.clientWidth;
                        document.body.classList.toggle('tb_page_row_fullwidth', !this.isFullWidth);
                    }
                }
                if (column !== null && column.parentNode.parentNode.closest('.fullwidth') !== null) {
                    const columnAction = column.tfClass('tb_column_action')[0];
                    if (columnAction !== und && columnAction.getBoundingClientRect().right >= document.body.clientWidth) {
                        columnAction.classList.add('tb_action_outside');
                    }
                }
            }
        },
        hover(e) {
            if (!doc.body.classList.contains('tb_start_animate') && this.disable === null && e.target.id!=='tb_small_toolbar_root') {
                this.columnHover(e.target);
                if (this.disablePosition === null) {
                    const el=e.target.closest('[data-cid]');
                    if(el!==null && !el.classList.contains('tb_active_layout_part')){
                        const cid=el.dataset.cid;
                        if(this.cid!==cid){
                            this.clear();
                            const model = api.Registry.get(cid),
                                actionBar=model.type==='module'?el.tfClass('tb_'+model.type+'_action')[0]:(e.target.classList.contains('tb_action_wrap')?e.target:und);
                        
                            if (actionBar !== und) {
								this.cid=cid;
								let slug=  model.get('mod_name');
								if(slug==='row' && model.el.classList.contains('tb-page-break')){
									actionBar.className+=' tb_move';
									slug='page_break';
								}
								const t = doc.createElement('tb-'+model.type+'-bar');
								t.className='tb_bar_'+slug;
								if(model.isEmpty===true){
									t.className=' tb_disabled_module';
								}
								else if(model.isSubCol===true){
									t.className+=' tb_bar_sub_column';
								}
                                else if(slug==='row'){
                                    const p=el.parentNode;
                                    if(p.childElementCount>2){
                                        if(el===p.firstElementChild){
                                            t.className+=' tb_row_first';
                                        }
                                        else if(el===p.lastElementChild.previousElementSibling){
                                            t.className+=' tb_row_last';
                                        }
                                    }
                                    else{
                                        t.className+=' tb_row_first tb_row_last';
                                    }
								}
								if(api.activeBreakPoint!=='desktop'){
									t.className+=' tb_bar_responsive_mode';
								}
								actionBar.style.marginTop='';
								actionBar.id='tb_component_bar';
								actionBar.appendChild(t);
								if (model.type === 'module') {
									let rect = el.getBoundingClientRect();
									if (rect.height < 70 || rect.width < 200) {
										const cl = t.shadowRoot.querySelector('.wrap,.dropdown').classList;
									   
										cl.remove('small_top', 'small_bottom');
										const a_top = actionBar.getBoundingClientRect().top;
										if (a_top < 40 || (a_top - api.Builder.get().el.getBoundingClientRect().top) < 40) {
											cl.add('small_top');
										} else {
											cl.add('small_bottom');
										}

										cl.toggle('small_height', rect.height < 70);
										cl.toggle('small_width', rect.width < 200);
										if(rect.width < 200){//move edit item to center
											const menu=t.shadowRoot.querySelector('.dropdown');
											menu.children[parseInt(menu.childElementCount/2)].after(menu.tfClass('edit')[0]);
										}
									}
								}
								actionBar.classList.add('tb_clicked');
							}
							if (api.mode === 'visual') {
								api.EdgeDrag.addEdges(model);
							}
                        }
                    }
                    else{
                        this.clear();
                    }
                }
            }
        },
        click(e) {
            if (api.isPreview || this.disable === true || e.target.closest('.tb_disable_sorting,.tb_grid_drag')!==null) {
                return true;
            }
            const target = e.target,
                el = target.closest('[data-cid]');
            if(!el || target.tagName.indexOf('-')!==-1){
                return;
            }
            e.preventDefault();
            const model = api.Registry.get(el.dataset.cid);
            if(model && model.isEmpty!==true){
                if(e.ctrlKey===true || e.metaKey===true){
                    el.classList.add('tb_element_clicked');
                }
                else{
                    this.clearClicked();
                    if ((e.type==='dblclick' || api.isDocked) && !el.classList.contains('tb_active_layout_part')) {
                        model.edit();
                    }
                }
            }
        },
        clear() {
            const bars = api.Builder.get().el.querySelectorAll('#tb_component_bar');
            for(let i=bars.length-1;i>-1;--i){
                bars[i].style.display='none';
                while (bars[i].firstChild !== null) {
                    bars[i].lastChild.remove();
                }
                bars[i].removeAttribute('id');
                bars[i].classList.remove('tb_active_action_bar');
                bars[i].style.display='';
            }
            this.cid=null;
        },
        clearClicked() {
            const selected = api.Builder.get().el.tfClass('tb_element_clicked');
            for (let i = selected.length - 1; i > -1; --i) {
                selected[i].classList.remove('tb_element_clicked');
            }
        }
    };
    Themify.on('tb_toolbar_loaded', () => {
            api.ActionBar.init();
    }, true,(api.ToolBar!==und && api.ToolBar.isLoaded===true));
    
})(tb_app,Themify, document, window.top,undefined);