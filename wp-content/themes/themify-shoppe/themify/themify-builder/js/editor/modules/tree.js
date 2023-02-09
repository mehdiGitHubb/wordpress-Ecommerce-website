
((api, Themify,doc,win) => {
    'use strict';
    api.Tree = {
        init() {
            const root = doc.tfId('tb_tree_root'),
                fr = root.firstElementChild;
                
            if (fr) { // shadowroot="open" isn't support
                root.attachShadow({
                    mode: fr.getAttribute('shadowroot')
                }).appendChild(fr.content);
                fr.remove();
            }
            if (api.mode === 'visual') {
                win.top.document.body.appendChild(root);
            }
            this.el=root.shadowRoot.querySelector('.wrapper');
            Themify.loadCss(Themify.builder_url +'/css/editor/modules/tree',null,null,this.el);
            Themify.on('themify_builder_ready',()=>{
                const combineCss=api.MainPanel.el.getRootNode().querySelector('#module_combine_style');
                root.shadowRoot.prepend(api.ToolBar.getBaseCss(),combineCss.cloneNode(true),this.getGridCss());
                setTimeout(()=>{
                    this.events();
                    this.draggable();
                    this.resize();
                },200);
            },true,api.is_builder_ready);
        },
        getGridCss(){
            const builderCss=doc.tfId('themify_concate-css') || doc.tfId('themify-builder-style-css'),
                rules = builderCss.sheet.cssRules,
                selectors=[],
                gridCss=[],
                sizes=ThemifyStyles.getColSize(),
                areas=ThemifyStyles.getArea(),
                gutters=ThemifyStyles.getGutter(),
                st=doc.createElement('style');
                for (let prop in gutters ) {
                    gridCss.push('--'+prop+':'+gutters[prop]);
                }
                let gridSel;
                for (let i =rules.length-1; i>-1;--i) {
                    let selText=rules[i].selectorText;
                    if(selText){
                        if ( selText.indexOf('.row_inner') !== -1 && selText.indexOf('.subrow_inner') !== -1 && rules[i].cssText.indexOf('--col')!==-1 && rules[i].cssText.indexOf('grid-template-areas')!==-1) {
                            let css=rules[i].cssText.replace(selText,'').replace('{','').replace('}','').trim().split(';');
                            gridSel=selText;
                            for(let j=css.length-1;j>-1;--j){
                                if(css[j]){
                                    let prop=css[j].split(':')[0].trim();
                                    if(prop && sizes[prop]===undefined && areas[prop]===undefined && gutters[prop]===undefined ){
                                        let v=css[j].replace(';','').trim();
                                        if(gridCss.indexOf(v)===-1){
                                            gridCss.push(v);
                                        }
                                    }
                                }
                            }
                        }
                        else if(selText.indexOf('.module_column')!==-1 && (rules[i].cssText.indexOf('grid-area')!==-1 || rules[i].cssText.indexOf('grid-template-columns')!==-1)){
                            selectors.push(rules[i].cssText.trim()); 
                        }
                    }
                }
                for (let prop in sizes ) {
                    gridCss.push(prop+':'+sizes[prop]);
                }
                for (let prop in areas ) {
                    gridCss.push(prop+':'+ThemifyStyles.normalizeArea(areas[prop]));
                }
                selectors.push(gridSel+'{'+gridCss.join(';')+'}');
                st.id='grid_css';
                st.innerHTML=selectors.join('');
                return st;
        },
        getTree(){
            console.time('TREE');
            const html=doc.createElement('template'),//cloneNode of big dom working 3-5 slower
                plus=doc.createElement('button');
                plus.type='button';
                plus.className='tf_plus_icon tf_rel';
                html.innerHTML=api.Builder.get().el.outerHTML;
                const rows=html.content.firstChild.children,
                    fr=doc.createDocumentFragment(),
                    loop=rows=>{
                        const type=rows[0].classList.contains('module_row')?'row':'subrow';
                        for(let i=rows.length-1;i>-1;--i){
                            if(rows[i].classList.contains('module_'+type)){
                                rows[i].classList.remove('themify_builder_row', 'tf_clearfix');
                                rows[i].removeAttribute('style');
                                let rowChild=rows[i].children;
                                for(let j=rowChild.length-1;j>-1;--j){
                                    let rCl=rowChild[j].classList;
                                    if(rCl.contains(type+'_inner') || rCl.contains('tb_row_info')){
                                        rowChild[j].removeAttribute('style');
                                        let cols=rowChild[j].children;
                                        for(let k=cols.length-1;k>-1;--k){
                                            if(cols[k].classList.contains('module_column')){
                                                cols[k].removeAttribute('style');
                                                let colChild=cols[k].children;
                                                for(let n=colChild.length-1;n>-1;--n){
                                                    let cCl=colChild[n].classList;
                                                    if(cCl.contains('tb_holder')){
                                                        colChild[n].removeAttribute('style');
                                                        let holder=colChild[n].children;
                                                        for(let m=holder.length-1;m>-1;--m){
                                                            let module=holder[m],
                                                                mCl=module.classList;
                                                            if(mCl.contains('active_module')){
                                                                module.removeAttribute('style');
                                                                if(mCl.contains('active_subrow')){
                                                                    let subrows=module.children;
                                                                    for(let v=subrows.length-1;v>-1;--v){
                                                                        if(!subrows[v].classList.contains('module_subrow')){
                                                                            subrows[v].remove();
                                                                        }
                                                                    }
                                                                    loop(subrows);
                                                                }
                                                                else{
                                                                    let name=doc.createElement('div'),
                                                                        preview=doc.createElement('div'),
                                                                        component=api.Registry.get(module.dataset.cid),
                                                                        text=component.getExcerpt();
                                                                        module.innerHTML=component.getImage();
                                                                        name.innerHTML=component.getName();
                                                                        name.className='module_name';
                                                                        preview.className='excerpt';
                                                                        preview.innerHTML=text;
                                                                        module.append(name,preview);
                                                                }
                                                            }
                                                            else{
                                                                module.remove();
                                                            }
                                                        }
                                                        
                                                    }
                                                    else if(!cCl.contains('tb_grid_drag') && !cCl.contains('tb_col_side')){
                                                        colChild[n].remove();
                                                    }
                                                }
                                                let p=plus.cloneNode(true);
                                                p.textContent=type==='row'?'Column':'SubColumn';
                                                cols[k].prepend(p);
                                            }
                                            else{
                                                cols[k].remove();
                                            }
                                        }
                                    }
                                    else{
                                        rowChild[j].remove();
                                    }
                                }
                                let p=plus.cloneNode(true);
                                p.textContent=type;
                                rows[i].prepend(p);
                            }
                            else{
                                rows[i].remove();
                            }
                        }   
                };
                loop(rows);
            console.timeEnd('TREE');
            return html.content;
        },
        open(){
            const content =this.el.tfClass('content')[0],
                tree=this.getTree();
            while (content.firstChild!==null) {
                content.lastChild.remove();
            }
            content.appendChild(tree);
            api.ToolBar.el.tfClass('tree')[0].classList.add('active');
            this.el.style.transform='translate('+Themify.w/2+'px,'+Themify.h/2+'px)';
            this.el.getRootNode().host.classList.remove('tf_hide');
        },
        extend(el){
            const expand=el.closest('.module_subrow,.module_column,.module_row');
            if(expand){
                expand.classList.toggle('expand');
            }
        },
        events(){
            this.el.tfOn(Themify.click,e=>{
                e.stopPropagation();
                const target=e.target;
                if(target.closest('.tf_close')){
                    this.close();
                }
                else if(target.closest('.minimize')){
                    this.minimize();
                }
                else{
                    this.extend(target);
                }
            },{passive:true});  
        },
        close(){
            this.el.getRootNode().host.classList.add('tf_hide');
            api.ToolBar.el.tfClass('tree')[0].classList.remove('active');
            const content =this.el.tfClass('content')[0];
            while (content.firstChild!==null) {
                content.lastChild.remove();
            }
        },
        minimize(){
           this.el.classList.toggle('is_minimized');
        },
        draggable(){
            const self = this;
                this.el.tfClass('header')[0].tfOn('pointerdown', function(e) {
                    if (e.which === 1) {
                        if (!e.target.classList.contains('title') && e.target!==this) {
                            return;
                        }
                        e.stopImmediatePropagation();
                        let timer,
                            el = self.el,
                            owner = this.ownerDocument;
                        const _x = e.clientX,
                            _y = e.clientY,
                            box = el.getBoundingClientRect(),
                            dragX = box.left - _x,
                            dragY = box.top - _y,
                            width = box.width,
                            draggableCallback =e => {
                                e.stopImmediatePropagation();
                                timer = requestAnimationFrame(() => {
                                    const x =e.clientX,
                                        y = e.clientY,
                                        clientX = dragX + x,
                                        clientY = dragY + y;
                                    el.style.transform = 'translate(' + clientX + 'px,' + clientY + 'px)';
                                });
                            },
                            startDrag = e=>{
                                e.stopImmediatePropagation();
                             //   Themify.trigger('tb_panel_drag_start');
                            };
                        el.style.willChange='transform';
                    //    owner.body.classList.add('tb_start_animate', 'tb_drag_lightbox');
                 //       api.ToolBar.el.classList.add('tb_start_animate', 'tb_drag_lightbox');
                  //      api.MainPanel.el.classList.add('tb_start_animate', 'tb_drag_lightbox');
                        this.tfOn('lostpointercapture', function(e) {
                            e.stopImmediatePropagation();
                            if (timer) {
                                cancelAnimationFrame(timer);
                            }
                            this.tfOff('pointermove', startDrag, {passive: true,once: true})
                            .tfOff('pointermove', draggableCallback, {passive: true});
                            el.style.willChange='';
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
                            resizeX = e.clientX,
                            resizeY = e.clientY,
                            _resize = e => {
                                e.stopImmediatePropagation();
                                timer=requestAnimationFrame(() => {
                                    let w;
                                    const clientX =  e.clientX,
                                        clientY =e.clientY,
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

                                    //Themify.trigger('tb_resize_lightbox');
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
        }
    };
    if(!api.isGSPage){
        api.Tree.init();
    }

})(tb_app, Themify,document,window);
