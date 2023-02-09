(($, api, Themify, topWindow, doc) => {
    'use strict';
    api.Drag = context => {
        if (api.isGSPage === true) {
            return;
        }
        let clicked;
        context.tfOn('pointerdown', e => {
            if (e.which === 1 && !api.isPreview && !e.target.closest('.tb_dragger,.tb_disable_sorting,.tb_editor_on')) {
                clicked = e.target;
                if (clicked.classList.contains('tb_grid_drag')) {
                    clicked=null;
                    api.columnResize(e);
                }
                else if(clicked.tagName.indexOf('TB-')===0 && e.composedPath()[0].classList.contains('tb_move')){//is our shadow dom event
                    clicked=e.composedPath()[0];
                }
            } else {
                clicked = null;
            }
        }, {
            passive: true
        })
        .tfOn('dragstart', function (e) {
            if(!clicked || e.target.nodeType === Node.TEXT_NODE){
                e.preventDefault();
                clicked = null;
                return;
            }
            let target =  e.target.closest('[draggable]'); 
         
            if(!target || (api.activeBreakPoint !== 'desktop' && target.classList.contains('active_module'))){
                e.preventDefault();
                clicked = null;
                return;
            }
            let  targetCl = target.classList,
                isRow = targetCl.contains('module_row'),
                isRowSort = isRow,
                isColumnMove = !isRow && targetCl.contains('module_column');
            
            if(isRowSort && !clicked.classList.contains('tb_move')){
                e.preventDefault();
                clicked = null;
                return;
            }
           
            if (!isRow && !isColumnMove) {
                isRow = targetCl.contains('page_break_module') || targetCl.contains('predesigned_row') || targetCl.contains('tb_item_row');
            }
            clicked = null;
            e.stopImmediatePropagation();

            let ghostClone,
                    ghostCloneH,
                    holder = null,
                    type = isRow ? 'row' : (isColumnMove ? 'column_move' : (targetCl.contains('tb_grid') ? 'column' : 'module')),
                    cl = [type],
                    body = doc.body,
                    y = 0,
                    x = 0,
                    prevItem,
                    isDropped,
                    scrollInterval,
                    scrollEl = null,
                    isScrolling = null,
                    scrollKoef,
                    scrollStep,
                    topScroll = [api.ToolBar.el.getRootNode().host, topWindow.document.tfId('tb_fixed_bottom_scroll')],
                    builder = api.Builder.get().el,
                    classItems=[topWindow.document.body],
                    ghost = doc.createElement('div');

            const _FRAME_ = 10,
                    onDragScroll = id => {
                        if (isScrolling === null && scrollEl) {
                            isScrolling = true;
                            let scroll = id === 'tb_main_toolbar_root' || id === 'wpadminbar' ? '-' : '+';
                            scroll += '=' + (scrollStep * scrollKoef) + 'px';
                            scrollEl.stop().animate({
                                scrollTop: scroll
                            }, {
                                duration: 10,
                                complete() {
                                    if (isScrolling === true) {
                                        isScrolling = null;
                                        onDragScroll(id);
                                    }
                                }
                            });
                        }
                    },
                    dragScrollEnter=function () {
                        if (scrollInterval) {
                            clearInterval(scrollInterval);
                        }
                        scrollKoef = 5;
                        scrollInterval = setInterval(() => {
                            if (scrollKoef < 51) {
                                scrollKoef += 5;
                            } else {
                                clearInterval(scrollInterval);
                                scrollInterval = null;
                            }
                        }, 1200);
                        prevItem = null;

                        if (holder !== null) {
                            holder.style.display = 'none';
                        }
                        for (let items = builder.querySelectorAll('[data-pos]'), i = items.length - 1; i > -1; --i) {
                            items[i].removeAttribute('data-pos');
                        }
                        onDragScroll(this.id);
                    },
                    dragScrollLeave=()=>{
                        if (scrollInterval) {
                            clearInterval(scrollInterval);
                        }
                        isScrolling = scrollInterval = null;
                        scrollEl.stop();
                    },
                    dragScroll = off => {
                        if (scrollInterval) {
                            clearInterval(scrollInterval);
                        }
                        if (off === true) {
                            for (let i = topScroll.length - 1; i > -1; --i) {
                                topScroll[i].tfOff('dragenter', dragScrollEnter, {passive: true})
                                .tfOff('dragleave', dragScrollLeave, {passive: true});
                            }
                            if (isRow && api.mode === 'visual') {
                                api.ToolBar.zoom({item: api.ToolBar.el.querySelector('[data-zoom="100"]')});
                            }
                            topScroll = isScrolling = scrollInterval =scrollKoef= scrollEl =scrollStep= null;
                            return;
                        }
                        scrollStep=1;
                        if (api.mode !== 'visual') {
                            scrollEl = api.ToolBar.el.getRootNode().host.closest('.interface-interface-skeleton__content');
                            if (!scrollEl) {
                                scrollEl = doc.tfClass('edit-post-layout__content')[0];
                            }
                            if (!scrollEl) {
                                scrollEl = null;
                            } else {
                                scrollStep /= 2;
                            }
                        }
                        if (scrollEl === null) {
                            scrollEl = api.activeBreakPoint === 'desktop' ? $('body,html') : $('body,html', topWindow.document);
                        }
                        else{
                            scrollEl=$(scrollEl);
                        }

                        if (isRow && api.mode === 'visual') {
                            api.ToolBar.zoom({item: api.ToolBar.el.querySelector('[data-zoom="50"]')});
                        }
                        if (scrollStep > 0) {
                            for (let i = topScroll.length - 1; i > -1; --i) {
                                topScroll[i].tfOn('dragenter', dragScrollEnter, {passive: true})
                                        .tfOn('dragleave', dragScrollLeave, {passive: true});
                            }
                        }
                    },
                    reject = e => {
                        e.dataTransfer.dropEffect = e.dataTransfer.effectAllowed = 'none';
                        if (prevItem) {
                            prevItem.removeAttribute('data-pos');
                        }
                        if (holder !== null) {
                            holder.style.display = 'none';
                        }
                    },
                    onDragOver =  e=> {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (ghostClone) {
                            ghostClone.style.top = (e.clientY - ghostCloneH) + 'px';
                        }
                        if (!e.target || isScrolling !== null || e.target === body || e.target === target || (type === 'module' && e.target.classList.contains('module_row'))) {
                            reject(e);
                            return;
                        }

                        if (holder !== null && (e.target === holder || e.target.classList.contains('tb_sortable_placeholder'))) {
                            return;
                        }
                        e.dataTransfer.effectAllowed = 'move';
                        if (y === 0 || x === 0 || (e.clientY - y) > _FRAME_ || (y - e.clientY) > _FRAME_ || (e.clientX - x) > _FRAME_ || (x - e.clientX) > _FRAME_ || e.target !== prevItem) {
                            y = e.clientY;
                            x = e.clientX;
                            let item = e.target;
                            const rect = item.getBoundingClientRect();
                            let side = (((y - rect.top) / rect.height) > .5) ? 'bottom' : 'top';

                            if (isColumnMove === true) {
                                let inner,
                                        cl=item.classList;
                                if(cl.contains('row_inner') || cl.contains('row_inner')){
                                    inner=item;
                                    item=inner.tfClass('module_column')[0];
                                    side=null;
                                }
                                else if (cl.contains('tb_col_side')) {
                                    side = cl[1].replace('tb_col_side_', '');
                                    item = item.parentNode;
                                    inner = item.parentNode;
                                } else if (cl.contains('module_column')) {
                                    side = (((x - rect.left) / rect.width) > .5) ? 'right' : 'left';
                                    inner = item.parentNode;
                                } else {
                                    return;
                                }
                                let w = inner.dataset.dragW,
                                        _area = item.dataset.dragArea;
                                if (!w || !_area) {
                                    const computed = getComputedStyle(inner);
                                    if (!w) {
                                        const c=inner.closest('[data-cid]');
                                        if(!c){
                                            return;
                                        }
                                        const inner_w = inner.offsetWidth;
                                        let gutter=api.Registry.get(c.dataset.cid).getSizes('gutter');
                                            gutter=gutter?ThemifyStyles.getGutterValue(gutter):'';
                                        if (!gutter) {
                                            gutter = computed.getPropertyValue('--colG');
                                        }
                                        let gutterUnit = gutter.replace(parseFloat(gutter).toString(), '') || '%';
                                        gutter = parseFloat(gutter);
                                        if (gutter === 0) {
                                            w = 'none';
                                        } else {
                                            if (gutterUnit === '%') {
                                                w = parseFloat(parseFloat((inner_w * gutter) / 100).toFixed(2)).toString();
                                            } else if (gutterUnit === 'em') {
                                                w = gutter * parseFloat(computed.getPropertyValue('font-size'));
                                            } else {
                                                w = gutter;
                                            }
                                        }
                                        inner.dataset.dragW=w;
                                        for (let cols = inner.children, i = cols.length - 1; i > -1; --i) {
                                            for (let sides = cols[i].children, j = sides.length - 1; j > -1; --j) {
                                                if (sides[j].classList.contains('tb_col_side')) {
                                                    sides[j].style.marginLeft=sides[j].style.marginRight='';
                                                    let p = sides[j].classList.contains('tb_col_side_right') ? '50' : '-50';
                                                    sides[j].style.width = w === 'none' ? '' : (w + 'px');
                                                    sides[j].style.transform = w === 'none' ? 'translateX(' + p + '%)' : '';
                                                 
                                                    let left=sides[j].getBoundingClientRect().left;
                                                    if(left<0){
                                                        sides[j].style.marginLeft='10px';
                                                    }
                                                    else if(left>=doc.documentElement.clientWidth){
                                                        sides[j].style.marginRight='10px';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (!_area) {
                                        _area = computed.getPropertyValue('--area');
                                        if (!_area) {
                                            _area = '1';
                                        } else {
                                            _area = _area.split('" "');
                                            for (let i = _area.length - 1; i > -1; --i) {
                                                let r = _area[i].replaceAll('"', '').split(' ');
                                                r = Array.from(new Set(r));
                                                _area[i] = r.join(' ');
                                            }
                                            _area = '"' + _area.join('" "') + '"';
                                        }
                                        inner.dataset.dragArea= _area;
                                    }
                                }
                                if(side===null){
                                    return;
                                }
                                if (_area !== '1' && !item.classList.contains('tb_drag_side_column') && !item.classList.contains('tb_drag_one_column')) {
                                    const colArea = getComputedStyle(item).getPropertyValue('grid-area').split('/')[0].replace('"', '').trim();
                                    if (_area.indexOf(colArea + ' ') === -1 && _area.indexOf(' ' + colArea) === -1) {
                                        item.classList.add('tb_drag_one_column');
                                    } else {
                                        item.classList.add('tb_drag_side_column');
                                    }
                                }
                                if (item.classList.contains('tb_drag_one_column')) {
                                    side = (((y - rect.top) / rect.height) > .5) ? 'right' : 'left';
                                }
                            }
                            if (item !== topScroll[0] && item !== topScroll[1]) {
                                if (!ghostClone && isColumnMove === false) {
                                    if (item.classList.contains('module_column')) {
                                        item = item.tfClass('tb_holder')[0];
                                        if (!item) {
                                            reject(e);
                                            return;
                                        }
                                    } else if (item.classList.contains('tb_dragger')) {
                                        item = item.parentNode;
                                    }
                                    if (item.classList.contains('tb_holder') && item.childElementCount > 0) {
                                        item = side === 'top' && item.firstChild !== target ? item.firstChild : item.lastChild;
                                        if (item === target) {
                                            reject(e);
                                            return;
                                        }
                                    }
                                }
                                if (prevItem && prevItem !== item) {
                                    if (isColumnMove === false) {
                                        const sibling = side === 'top' ? item.previousSibling : item.nextElementSibling;
                                        if (sibling === prevItem) {
                                            const prevPos = sibling.dataset.pos;
                                            if ((side === 'top' && prevPos === 'bottom') || (side === 'bottom' && prevPos === 'top')) {
                                                return;
                                            }
                                        }
                                    }
                                    prevItem.removeAttribute('data-pos');
                                }
                                if (item.dataset.pos !== side) {
                                    item.dataset.pos=side;
                                    if (holder !== null) {
                                        holder.style.display = '';
                                        if (item.classList.contains('tb_holder')) {
                                            item.appendChild(holder);
                                        } else {
                                            side === 'top' ? item.before(holder) : item.after(holder);
                                        }
                                    }
                                }
                                prevItem = item;
                            }
                        }
                    },
                    onDrag = function (e) {
                        e.stopImmediatePropagation();
                        classItems.push(api.MainPanel.el);
                        classItems.push(api.ToolBar.el.getRootNode().host);
                        for(let i=classItems.length-1;i>-1;--i){
                            classItems[i].classList.add('tb_start_animate', 'tb_drag_start', 'tb_drag_' + cl[0]);
                            if(cl[1]){
                                classItems[i].classList.add(cl[1]);
                            }
                        }
                        api.SmallPanel.hide();
                        api.ActionBar.clear();
                        // api.toolbar.common.hide(true);
                        this.classList.add('tb_draggable_item');
                        if (isColumnMove === true) {
                            this.parentNode.classList.add('tb_column_drag_inner');
                            const innsers = builder.querySelectorAll('.row_inner,.subrow_inner');
                            for (let i = innsers.length - 1; i > -1; --i) {
                                let childs = innsers[i].children,
                                        w = innsers[i].getBoundingClientRect().width - 5;

                                for (let j = childs.length - 1; j > -1; --j) {
                                    if ((childs[j].offsetWidth + childs[j].offsetLeft) < w) {
                                        childs[j].classList.add('tb_hide_drag_col_right');
                                    }
                                }
                            }
                        }
                        dragScroll();
                        if (ghostClone) {
                            ghostClone.style.top = e.clientY + 'px';
                        }
                    },
                    mouseEvent = e => {
                        e.stopImmediatePropagation();
                    },
                    onDrop =  e=> {
                        if (e.target) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                        }
                        isDropped = true;
                        let dropped = e.target ? e.target : e;
                        if (dropped.classList.contains('module_column') || dropped.classList.contains('tb_col_side')) {
                            dropped = isColumnMove ? dropped.closest('.module_column') : dropped.querySelector('[data-pos]');
                        } 
                        else if (isColumnMove) {
                            dropped = null;
                            for (let items = doc.tfClass('tb_column_drag_inner'), i = items.length - 1; i > -1; --i) {
                                items[i].classList.remove('tb_column_drag_inner');
                            }
                        } 
                        else if (dropped.classList.contains('tb_dragger')) {
                            dropped = dropped.closest('[draggable]');
                        }
                        if (dropped === holder || dropped.classList.contains('tb_sortable_placeholder')) {
                            dropped = dropped.closest('.tb_active_builder').querySelector('[data-pos]');
                            if (dropped.classList.contains('tb_sortable_placeholder')) {
                                dropped = dropped.closest('.tb_holder');
                            }
                        }
                        if (!dropped || (type === 'module' && dropped.classList.contains('module_row'))) {
                            target.classList.remove('tb_draggable_item');
                            return;
                        }
                        api.ActionBar.clear();
                        const draggedRow=target.closest('.module_row'),
                            isSort= draggedRow!==null,
                            dragged =  isSort?target:target.cloneNode(true),
                            side = dropped.dataset.pos,
                            countRowModules=el=>{
                                const r=el.closest('.module_row');
                                if(r){
                                    r.classList.toggle('tb_row_empty',r.tfClass('active_module')[0]===undefined);
                                }
                            };
                             
                        
                        if (holder) {
                            holder.remove();
                        }
                        api.undoManager.start('move');
                        target.classList.remove('tb_draggable_item');//don't change position,need for undo   
                        if(!isSort){
                            dragged.style.display='none';
                        }     
                        if (!dropped.classList.contains('tb_holder')) {
                            if (!isColumnMove) {
                                side === 'top' ?dropped.before(dragged):dropped.after(dragged);
                            } 
                        } 
                        else {
                            dropped.appendChild(dragged);
                        }  
                        if(isColumnMove || isSort){
                            const prm=isColumnMove?api.Drop.column(dragged, dropped, side):Promise.resolve();
                            prm.then(()=>{
                                Themify.trigger('tb_' + type + '_sort', [dragged]);
                                api.Utils._onResize(true);
                                countRowModules(draggedRow);
                                countRowModules(dropped);
                                api.undoManager.end('move');
                            });
                        }
                        else{//new element: dragged element will be replaced
                            //
                            //row,module
                            let dropType=targetCl.contains('tb_grid')?'grid':(targetCl.contains('page_break_module')?'pagebreak':'predesign'),
                                slug=target.dataset.slug,
                                dropHandler=dropType==='grid'?'row':type;
                            if(target.dataset.type){
                                dropType=target.dataset.type;
                                if(dropHandler==='row' && targetCl.contains('library_item')){
                                    dropType='library';
                                }
                            }
                            api.Drop[dropHandler](dragged,dropType,slug).then(()=>{
                                Themify.trigger('tb_' + type + '_sort', [dragged]);
                                countRowModules(dragged);
                                if(dropHandler==='module' && dropType !== 'part' && dropType !== 'module'){
                                    api.undoManager.clear('move');
                                }
                                else{
                                    api.undoManager.end('move');
                                }
                            }).catch(()=>{
                                api.undoManager.clear('move');
                            });
                        }
                    };
            ghost.className = 'tb_sortable_helper tf_box tf_overflow';
           
            if (targetCl.contains('active_subrow')) {
                cl.push('tb_drag_subrow');
            }
            if (isRowSort) {
                doc.body.classList.add('tb_drag_row');
                if (api.mode !== 'visual') {
                    ghostClone = ghost.cloneNode();
                    ghost.style.opacity = 0;
                    const b = target.getBoundingClientRect();
                    ghostClone.style.width = b.width + 'px';
                    ghostClone.style.left = b.left + 'px';
                    doc.body.appendChild(ghostClone);
                    ghostCloneH = ghostClone.offsetHeight / 2;
                }
            } 
            else if (type === 'module' || isColumnMove) {
                if (isColumnMove || targetCl.contains('active_subrow')) {
                    ghost.innerHTML = isColumnMove ? 'Column' : 'Subrow';
                }
                else {
                    let slug = target.dataset.slug || api.Registry.get(target.dataset.cid).get('mod_name');
                    if (slug && themifyBuilder.modules[slug]) {
                        const m=themifyBuilder.modules[slug],
                            icon = m.icon,
                            name = doc.createElement('span');
                        name.textContent = m.name;
                        name.className = 'tf_vmiddle';
                        if (icon) {
                            ghost.appendChild(api.Helper.getIcon('ti-' + icon));
                        }
                        ghost.appendChild(name);
                    }
                }
            } else if (type === 'column') {
                ghost.className+=' '+target.className;
                ghost.innerHTML=target.innerHTML;
            } else if (type === 'row' && (targetCl.contains('page_break_module') || targetCl.contains('predesigned_row'))) {
                const tmpCl = targetCl.contains('page_break_module') ? 'page_break_title' : 'predesigned_title';
                ghost.textContent = target.tfClass(tmpCl)[0].textContent;
            }
            if (!isColumnMove && (isRow || api.mode!=='visual')) {
                holder = doc.createElement('div');
                holder.className = 'tb_sortable_placeholder tf_rel tf_w';
            }
            const addBtn=ghost.querySelector('.add_module_btn');
            if(addBtn!==null){
                addBtn.remove();
            }
            doc.body.appendChild(ghost);
			
			e.dataTransfer.effectAllowed='move';
			e.dataTransfer.setData('Text', 'id');//required for touch dnd
            e.dataTransfer.setDragImage(ghost, (ghost.offsetWidth / 2) + 2, (ghost.offsetHeight / 2));

            target.tfOn('dragend', function (e) {
                e.stopImmediatePropagation();
                doc.body.tfOff('dragover', onDragOver);
                builder.tfOff(['dragenter', 'dragleave','pointermove','pointerover','pointerout','pointerenter','pointerleave','mousemove','mouseover','mouseout','mouseenter','mouseleave'], mouseEvent, {
                    passive: true
                })
                .tfOff('drop', onDrop, {
                    once: true
                });
                this.tfOff('drag', onDrag, {
                    once: true,
                    passive: true
                });
                if (!isDropped) {
                    const dropped = builder.querySelector('[data-pos]');
                    if (dropped) {
                        onDrop(dropped);
                    }
                }
                ghost.remove();
                if (ghostClone) {
                    ghostClone.remove();
                }
                if (holder) {
                    holder.remove();
                }
                for (let items = doc.querySelectorAll('[data-drag-w],[data-drag-area],[data-pos]'), i = items.length - 1; i > -1; --i) {
                    items[i].removeAttribute('data-pos');
                    items[i].removeAttribute('data-drag-w');
                    items[i].removeAttribute('data-drag-area');
                }
                for (let items = doc.querySelectorAll('.tb_hide_drag_col_right,.tb_drag_one_column,.tb_drag_side_column,.tb_column_drag_inner'), i = items.length - 1; i > -1; --i) {
                    items[i].classList.remove('tb_hide_drag_col_right', 'tb_drag_one_column', 'tb_drag_side_column', 'tb_column_drag_inner');
                }
                this.classList.remove('tb_draggable_item', 'tb_drag_one_column', 'tb_drag_side_column');
                dragScroll(true);
                if (api.mode !== 'visual') {
                    for(let drops=doc.tfClass('is-drop-target'),i=drops.length-1;i>-1;--i){
                        drops[i].classList.remove('is-drop-target');
                    }
                }
                for(let i=classItems.length-1;i>-1;--i){
                    classItems[i].classList.remove('tb_start_animate', 'tb_drag_start','tb_drag_start_'+cl[0], 'tb_drag_' + cl[0]);
                    if(cl[1]){
                        classItems[i].classList.remove(cl[1]);
                    }
                }
                holder = isDropped = clicked = target = targetCl = prevItem = ghost = ghostClone = ghostCloneH = body = builder = x = y = cl = type  = isColumnMove = classItems = null;
                api.ActionBar.clear();
            }, {
                once: true,
                passive: true
            });

            if (api.mode === 'visual') {
                classItems.push(body);
            }
            
            for(let i=classItems.length-1;i>-1;--i){
                classItems[i].classList.add('tb_drag_start_'+cl[0]);
                if(cl[1]){
                    classItems[i].classList.add(cl[1]);
                }
            }
            target.tfOn('drag', onDrag, {
                once: true,
                passive: true
            });
            doc.body.tfOn('dragover', onDragOver);
            builder.tfOn('dragenter dragleave', mouseEvent, {
                passive: true
            })
            .tfOn('drop', onDrop, {
                once: true
            });
            if(!Themify.isTouch){
                builder.tfOn(['pointermove','pointerover','pointerout','pointerenter','pointerleave','mousemove','mouseover','mouseout','mouseenter','mouseleave'], mouseEvent, {
                    passive: true
                });
            }
        });
    };
    
    
    api.columnResize=e=> {
            e.stopPropagation();
            const bodyCl = doc.body.classList,
                target = e.target,
                dragIndexes = [],
                dragNextIndexes = [],
                el = target.parentNode,
                row_inner = el.parentNode,
                computed = getComputedStyle(row_inner),
                childCount = row_inner.childElementCount,
                row_w = row_inner.offsetWidth,
                dir = target.classList.contains('tb_drag_right') ? 'w' : 'e',
                tooltip1 = doc.createElement('div'),
                tooltip2 = doc.createElement('div'),
                dragColName = getComputedStyle(el).getPropertyValue('grid-area').split('/')[0].replace('"', '').trim(),
                area = dragColName && dragColName !== 'auto' && dragColName !== 'initial' && dragColName !== 'none'? getComputedStyle(row_inner).getPropertyValue('--area').replace(/  +/g, ' ').trim().split('" "') : '',
                gutterVal = computed.getPropertyValue('column-gap'),
                gutter = parseFloat(gutterVal) || 0;
            let cols = getComputedStyle(row_inner).getPropertyValue('--col').replace(/\s\s+/g, ' ').trim(),
                cell,
                timer,
                isDragged = false,
                startX = e.clientX,
                percent = 100,
                summFr = 0,
                summEM = 0,
                summPx = 0;
            Themify.trigger('disableInline');
			if(cols==='none'){
				cols='';
			}
            else if (cols.indexOf('repeat') !== -1) {
                if (cols.indexOf('auto-fit') === -1 && cols.indexOf('auto-fill') === -1) {
                    let tmp = '',
                        repeat = cols.replace(/\s\,\s|\s\,|\,\s/g, ',').replace(/\s\(\s|\s\(|\(\s/g, '(').replaceAll(' )', ')').trim().split(' ');
                    for (let i = 0, len = repeat.length; i < len; ++i) {
                        if (repeat[i].indexOf('repeat') !== -1) {
                            let item = repeat[i].split('(')[1].replace(')', '').split(','),
                                count = parseInt(item[0]),
                                unit = item[1].trim();
                            if (isNaN(count)) {
                                unit = '1fr';
                                count = childCount;
                            }
                            tmp += ' ' + (' ' + unit).repeat(count);
                        } else {
                            tmp += ' ' + repeat[i];
                        }
                    }
                    cols = tmp.trim();
                } else {
                    cols = '';
                }
            }
            if (area) {
                let row = 0;
                for (let i = area.length - 1; i > -1; --i) {
                    if (area[i].indexOf(dragColName) !== -1) {
                        let arr = area[i].replace(/\s\s+/g, ' ').replaceAll('"', '').trim().split(' ');
                        for (let j = arr.length - 1; j > -1; --j) {
                            if (arr[j] === dragColName) {
                                dragIndexes.push(j);
                                row = i;
                            }
                        }
                        if (dragIndexes.length > 0) {
                            break;
                        }
                    }
                }
                if (dragIndexes.length > 0) {
                    const min = Math.min.apply(null, dragIndexes) - 1,
                        max = Math.max.apply(null, dragIndexes) + 1,
                        childs = row_inner.children,
                        dragNextAreaIndex = dir === 'w' ? max : min,
                        dragNextColName = area[row].replace(/\s\s+/g, ' ').replaceAll('"', '').trim().split(' ')[dragNextAreaIndex]?.trim();
                        if(!dragNextColName){
                            return;
                        }
                    for (let i = area.length - 1; i > -1; --i) {
                        if (area[i].indexOf(dragNextColName) !== -1) {
                            let arr = area[i].replace(/\s\s+/g, ' ').replaceAll('"', '').trim().split(' ');
                            for (let j = arr.length - 1; j > -1; --j) {
                                if (arr[j] === dragNextColName) {
                                    dragNextIndexes.push(j);
                                }
                            }
                            if (dragNextIndexes.length > 0) {
                                break;
                            }
                        }
                    }
                    for (let i = childs.length - 1; i > -1; --i) {
                        if (el !== childs[i] && dragNextColName === getComputedStyle(childs[i]).getPropertyValue('grid-area').split('/')[0].replace('"', '').trim()) {
                            cell = childs[i];
                            break;
                        }
                    }
                }
                const areaLength = area[0].trim().split(' ').length;
                if (!cols) {
                    cols = ('1fr '.repeat(areaLength)).trim();
                } else {
                    const diffLength = areaLength - cols.split(' ').length;
                    if (diffLength > 0) {
                        cols += ' 1fr'.repeat(diffLength);
                    }
                }
            } else {
                if (dir === 'w') {
                    cell = el.previousElementSibling || el.nextElementSibling;
                } else {
                    cell = el.nextElementSibling ||  el.previousElementSibling;
                }
                if (!cols) {
                    const gridWidth = [],
                        tmpChildren = row_inner.children;
                    for (let i = 0, len = tmpChildren.length; i < len; ++i) {
                        gridWidth.push(tmpChildren[i].getBoundingClientRect().width);
                    }
                    const min = Math.min.apply(null, gridWidth).toFixed(2).toString();
                    for (let i = gridWidth.length - 1; i > -1; --i) {
                        gridWidth[i] = min === gridWidth[i].toFixed(2).toString() ? '1fr' : ((gridWidth[i] / min).toFixed(2).toString().replace('0.', '.') + 'fr');
                    }
                    cols = gridWidth.join(' ');
                }
                const childs = Themify.convert(row_inner.children);
                dragIndexes.push(childs.indexOf(el));
                dragNextIndexes.push(childs.indexOf(cell));
            }
            cols = cols.split(' ');
            if (!cols[dragIndexes[0]]) {
                dragIndexes[0] = dragIndexes[0] % cols.length;
            }
            if (!cols[dragNextIndexes[0]]) {
                dragNextIndexes[0] = dragNextIndexes[0] % cols.length;
            }
            const colsLen = cols.length;
            if (gutterVal) {
                if (gutterVal.indexOf('px') !== -1) {
                    summPx = (colsLen - 1) * gutter;
                } else if (gutterVal.indexOf('em') !== -1) {
                    summEM = (colsLen - 1) * gutter;
                }
            } else {
                percent -= ((colsLen - 1) * gutter);
            }
            //find 1fr in px
            for (let i = colsLen - 1; i > -1; --i) {
                let v = cols[i];
                if (v.indexOf('fr') !== -1) {
                    summFr += parseFloat(v);
                } else if (v.indexOf('%') !== -1) {
                    percent -= parseFloat(v);
                } else if (v.indexOf('em') !== -1) {
                    summEM += parseFloat(v);
                } else if (v.indexOf('px') !== -1) {
                    summPx += parseFloat(v);
                }
            }
            if (summEM !== 0) {
                summEM = parseFloat(computed.getPropertyValue('font-size')) * summEM;
            }
            const dragLen = dragIndexes.length,
                dragNextLen = dragNextIndexes.length,
                fr1 = parseFloat((parseFloat((row_w * percent) / 100) - summPx - summEM) / summFr);

            tooltip1.className = tooltip2.className = 'tb_grid_drag_tooltip';

            target.classList.add('tb_drag_grid_current');
            el.classList.add('tb_element_clicked');
            if (dir === 'w') {
                tooltip1.className += ' tb_grid_drag_right_tooltip';
                tooltip2.className += ' tb_grid_drag_left_tooltip';
            } else {
                tooltip1.className += ' tb_grid_drag_left_tooltip';
                tooltip2.className += ' tb_grid_drag_right_tooltip';
            }
            el.style.willChange='width';
            if(cell){
                cell.style.willChange='width';
            }
            const onDrag = e=> {
                e.stopImmediatePropagation();
                timer = requestAnimationFrame(() => {
                    if (isDragged === false) {
                        isDragged = true;
                        api.ActionBar.clear();
                        api.undoManager.start('style');
                        target.append(tooltip1,tooltip2);
                    }
                    let diff = parseInt(e.clientX) - startX;
                    startX = e.clientX;
                    if (diff !== 0) {
                        if (dir === 'e') {
                            diff *= -1;
                        }
                        let fr = parseFloat(diff / fr1);
                        if (fr !== 0) {
                            fr = parseFloat(fr / (dragLen + dragNextLen));
                            let v1 = (parseFloat(cols[dragIndexes[0]]) + fr),
                                v2 = (parseFloat(cols[dragNextIndexes[0]]) - fr);
                            if (v1 > 0.001 && v2 > 0.001) {
                                    v1 = v1.toFixed(3) + 'fr';
                                    v2 = v2.toFixed(3) + 'fr';
                                    for (let i = dragLen - 1; i > -1; --i) {
                                        cols[dragIndexes[i]] = v1;
                                    }
                                    for (let i = dragNextLen - 1; i > -1; --i) {
                                        cols[dragNextIndexes[i]] = v2;
                                    }
                                    row_inner.style.setProperty('--col', cols.join(' '));
                                    tooltip1.textContent = v1 + ' / ' + el.offsetWidth + 'px';
                                    if(cell){
                                        tooltip2.textContent = v2 + ' / ' + cell.clientWidth + 'px';
                                    }
                            }
                        }
                    }
                });
            };
            target.tfOn('lostpointercapture', function(e) {
                e.stopImmediatePropagation();
                this.tfOff('pointermove', onDrag, {
                    passive: true
                });
                if (timer) {
                    cancelAnimationFrame(timer);
                }
                requestAnimationFrame(()=> {
                    tooltip1.remove();
                    tooltip2.remove();
                    api.ActionBar.clear();
                    el.classList.remove('tb_element_clicked');
                    this.classList.remove('tb_drag_grid_current');
                    bodyCl.remove('tb_start_animate', 'tb_drag_grid_start');
                    el.style.willChange='';
                    if(cell){
                        cell.style.willChange='';
                    }
                    if (isDragged) {
                        const row = row_inner.closest('[data-cid]');
                        for (let i = cols.length - 1; i > -1; --i) {
                            cols[i] = parseFloat(parseFloat(cols[i]).toFixed(3).replace('0.', '.')).toString() + 'fr';
                        }
                        api.Registry.get(row.dataset.cid).setCols({size:cols.join(' ')});
                        row_inner.style.setProperty('--col', '');
                        api.Utils.setCompactMode([el, cell]);
                        Themify.trigger('tb_grid_changed',row);
                        api.Utils._onResize(true);
                        api.undoManager.end('style');
                    }
                    isDragged = timer = cell = null;
                });

            }, {
                once: true,
                passive: true
            })
            .tfOn('pointermove', onDrag, {
                passive: true
            })
            .setPointerCapture(e.pointerId);
            bodyCl.add('tb_start_animate', 'tb_drag_grid_start');
        };
    if(!Themify.isTouch && false){
        const DragFile=()=>{
            const allowedArchives=['application/x-zip-compressed','application/zip','text/plain','application/json'],
                allowedImages=['image/webp','image/png','image/jpeg','image/gif','image/bmp'],
            pasteData=async dataTransfer=>{
                let html=dataTransfer.getData('text/html') || '',
                files=dataTransfer.files;
                if(html){
                    html=html.trim();
                }
                if(html || (files && files.length>0)){
                    let images=new Map(),
                        archive,
                        module='gallery',
                        files=dataTransfer.files;
                    if(files && files.length>0){
                        for (let i=files.length-1; i>-1;--i) {
                            let type=files[i].type;
                            if (type) {
                                if(allowedArchives.indexOf(type)!==-1){
                                    if(!archive){
                                        archive=files[i];
                                    }
                                }
                                else if(allowedImages.indexOf(type)!==-1){
                                    images.add(files[i]);
                                }
                            }
                        }
                    }
                    else{
                        const tmp=doc.createElement('template');
                        tmp.innerHTML=html;
                        html=tmp.content;
                        const allImages=html.querySelectorAll('img');   
                        for (let i=allImages.length-1; i>-1;--i) {
                            let src=allImages[i].src || allImages[i].dataset.src;
                            if(src){
                                images.set(src,1);
                            }
                        }
                        for (let childs=html.children,i=childs.length-1; i>-1;--i) {
                            if(childs[i].tagName==='IMG'){
                                
                            }
                            else{
                                module='text';
                            }
                        }
                    }
                    if(images.size>0){
                        const uploadMsg=themifyBuilder.i18n.upload_images.replaceAll('%to%',images.size);
                        let index=1;
                        for(let [url,v] of images){
                            await TF_Notification.show('info',uploadMsg.replaceAll('%from%',index).replaceAll('%post%',url));
                            let res=await api.LocalFetch({
                                action:'tb_download_image',
                                url:url
                            });
                            images.set(url,res.data.new);
                            ++index;
                        }
                    }
                    if(archive){
                        api.ToolBar.initImport({target:doc.createElement('div')}).then(()=>{
                           TB_Import.fileImport(archive);
                        });
                    }
                }
            };
            let isBind=false;
            doc.tfOn('dragenter', e=>{
                if (e.dataTransfer.types && e.target && e.target.id!=='tb_drop_zone') {
                    let found=false;
                    for (let types=e.dataTransfer.types,i=types.length-1; i>-1;--i) {
                        let kind=e.dataTransfer.items[i].kind;
                        if(types[i] === 'Files' && kind==='file'){
                            let type=e.dataTransfer.items[i].type;
                            if( allowedArchives.indexOf(type)!==-1 || allowedImages.indexOf(type)!==-1){
                                found=true;
                                break;
                            }
                        }
                        else if(types[i] === 'text/html' && kind==='string'){
                            found=true;
                            break;
                        }
                    }
                    if(found===true){
                        e.stopPropagation();
                        const dropZone=doc.tfId('tb_drop_zone'),
                            leave=e=>{
                                e.stopPropagation();
                                doc.body.classList.remove('tb_drop_file');  
                            };
                        if(isBind===false){
                            isBind=true;
                            const dragOver=e=>{
                                e.stopPropagation();
                                e.preventDefault();
                                e.dataTransfer.dropEffect = 'copy';
                            },
                            dragEnd=e=>{
                                e.stopPropagation();
                                dropZone.tfOff('drop',drop,  {once:true})
                                .tfOff('dragleave',leave,  {passive:true,once:true})
                                .tfOff('dragover',dragOver);
                                doc.tfOff('dragend',dragEnd,  {passive:true,once:true});
                                leave(e);
                                isBind=false;
                            },
                            drop=e=>{
                                e.stopPropagation();
                                e.preventDefault();
                                dragEnd(e);
                                pasteData(e.dataTransfer);
                            };
                            dropZone.tfOn('dragover',dragOver)
                                .tfOn('drop',drop,{once:true});
                            doc.tfOn('dragend',dragEnd,  {passive:true,once:true});
                        }
                        dropZone.tfOn('dragleave',leave,  {passive:true,once:true});
                        doc.body.classList.add('tb_drop_file');
                    }
                }
            }, {passive:true});
            doc.tfOn('paste', e=> {
                if(!e.target.isContentEditable && e.target.tagName!=='INPUT'&& e.target.tagName!=='TEXTAREA'){
                    pasteData(e.clipboardData);
                }
            },{passive:true});

        };
        DragFile();
    }
})(jQuery, tb_app, Themify, window.top, document);