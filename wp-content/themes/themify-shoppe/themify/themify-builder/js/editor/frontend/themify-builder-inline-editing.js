((api,Themify, $, win, doc,  topWindow,und)=> {
    'use strict';
    let activeEl,
        model,
        modelClass,
        width,
        height,
        timer,
        timer2,
        bodyCL,
        host=doc.tfId('tb_inline_editor_root'),
        toolbar = null,
        toolbarItems = null,
        linkHolder = null,
        dialog = null,
        imageToolbar = null,
        selection = null,
        isImageEdit = false,
        isChanging = false,
        isClicked = false,
        swiper,
        isImageSelecting = false,
        selectionEndTimeout,
        isChanged = false,
        is_editable = false,
        isTyping=false,
        pallete,
        imageObj,
        filterImage,
        stackIndex =- 1,
        undoIsworking=null,
        firstVal,
        stack=[];
    const getPos=el=>{
                let pos=el.getBoundingClientRect(),
                    top = 0, 
                    left = 0,
                    diffTop=pos.top+win.scrollY,
                    diffLeft=pos.left+win.scrollX;
                while (el !== null) {
                    top += el.offsetTop;
                    left += el.offsetLeft;
                    el = el.offsetParent;
                }
                diffTop-=top;
                diffLeft-=left;
                return {top:top, left:(left+diffLeft),width:pos.width,height:pos.height,diffTop:diffTop,diffLeft:diffLeft};
            },
            pushUndo=v=>{
                if(undoIsworking===null){
                    const sel=win.getSelection(),
                        item={el:v};
                    stack.splice(stackIndex + 1, stack.length - stackIndex);
                    if(sel.rangeCount!== 0){
                        const r=sel.getRangeAt(0).cloneRange();
                        item.r={
                                    startOffset:r.startOffset,
                                    endOffset:r.endOffset,
                                    collapsed:r.collapsed,
                                    startContainer:api.Helper.cloneDom(r.startContainer),
                                    endContainer:api.Helper.cloneDom(r.endContainer)
                        };
                    }
                    stack.push(item);
                    stackIndex = stack.length - 1;
                }
            },
            exec = (command,...args)=> {
                const value = args[0] !== und ? args[0] : null;
                doc.execCommand('styleWithCSS', false, true);
                doc.execCommand(command, false, value);
            },
            state = command=>{
                const st = doc.queryCommandState(command);
                return st === -1 ? false : st;
            },
            menu = {
                undo:{
                    result(el,type) {
                        undoIsworking=true;
                        if(!type){
                            type='undo';
                        }
                        const index = type==='undo' ? 0 : 1,
                            i=stackIndex+index,
                            st = stack[i];
                            if (type==='undo') {
                                --stackIndex;
                            } 
                            else {
                                ++stackIndex;
                            }
                            if (st !== und) {
                                if(st.el===activeEl.innerHTML){
                                    menu[type].result(el);
                                    return;
                                }
                                let r=st.r;
                                activeEl.innerHTML=st.el;
                                if(r){
                                    let startOffset=r.startOffset,
                                        endOffset=r.endOffset,
                                        collapsed=r.collapsed,
                                        startContainer=r.startContainer,
                                        endContainer=r.endContainer;
                                    let start,
                                    end,
                                    firstText;
                                    const sel = win.getSelection(),
                                    range=new Range(),
                                    isStartText=startContainer.nodeType===Node.TEXT_NODE?startContainer.textContent:null,
                                    isEndText=endContainer.nodeType===Node.TEXT_NODE?endContainer.textContent:null,
                                    getAllChildren=el=>{
                                        if(start && end){
                                            return;
                                        }
                                        const childs=el.childNodes;
                                        for(let i=childs.length-1;i>-1;--i){
                                            if(start && end){
                                                break;
                                            }
                                            if(!firstText && childs[i].nodeType===Node.TEXT_NODE){
                                                firstText=childs[i];
                                            }
                                            if(!start && (childs[i].isEqualNode(startContainer) || (isStartText!==null && childs[i].nodeType===Node.TEXT_NODE && childs[i].textContent===isStartText))){
                                                start=childs[i];
                                            }
                                            if(!end && (childs[i].isEqualNode(endContainer) || (isEndText!==null && childs[i].nodeType===Node.TEXT_NODE && childs[i].textContent===isEndText))){
                                                end=childs[i];
                                            }
                                            getAllChildren(childs[i]);
                                        }
                                    };
                                    
                                    getAllChildren(activeEl);
                                    sel.removeAllRanges();
                                    activeEl.focus();
                                    if(!start && !end){
                                        start=end=firstText;
                                    }
                                    if(start && end){
                                        range.collapse(collapsed);
                                        range.setStart(start,startOffset);
                                        range.setEnd(end, endOffset);
                                        sel.addRange(range);
                                    }
                                }
                                setCarret();
                            }
                            undoIsworking=null;
                    },
                    state(el,type){
                        const enabled= type==='redo'?(stackIndex < (stack.length - 1)):stackIndex>-1;
                        if(el){
                            el.toggleClass('disabled',!enabled);
                        }
                        return enabled;
                    }
                },
                redo:{
                    result(el) {
                        menu.undo.result(el,'redo');
                    },
                    state(el){
                        return menu.undo.state(el,'redo');
                    }
                },
                formatBlock: {
                    result(el) {
                        const action = el.parentNode.classList.contains('selected') ? 'p' : el.dataset.action;
                        exec('formatBlock', '<' + action + '>');
                    },
                    state(el) {
                        const actions = el.tfClass('submenu')[0].tfClass('action'),
                                current = doc.queryCommandValue('formatBlock');
                        for (let i = actions.length - 1; i > -1; --i) {
                            let cl = actions[i].parentNode.classList;
                            if (current === actions[i].dataset.action) {
                                if (!cl.contains('selected')) {
                                    cl.add('selected');
                                    setSelectedBtnAndParent(actions[i]);
                                }
                            } else {
                                cl.remove('selected');
                            }
                        }
                    }
                },
                text_align: {
                    result(el) {
                        if (el.parentNode.classList.contains('selected')) {
                            if (selection) {
                                if (selection.startContainer) {
                                    selection.startContainer.parentNode.style.textAlign = '';
                                    const p = selection.startContainer.parentNode.closest('[style*=text-align]');
                                    if (p) {
                                        p.style.textAlign = '';
                                    }
                                }
                                if (selection.commonAncestorContainer) {
                                    if (selection.commonAncestorContainer.nodeType !== Node.TEXT_NODE) {
                                        selection.commonAncestorContainer.style.textAlign = '';
                                    }
                                    const p = selection.startContainer.parentNode.closest('[style*=text-align]');
                                    if (p) {
                                        p.style.textAlign = '';
                                    }
                                }
                                onChange();
                            }
                        } else {
                            exec(el.dataset.action);
                        }
                    },
                    state(el) {
                        const actions = el.tfClass('submenu')[0].tfClass('action');
                        let hasSelected = false;
                        for (let i = actions.length - 1; i > -1; --i) {
                            let cl = actions[i].parentNode.classList;
                            if (actions[i].hasAttribute('data-action') && state(actions[i].dataset.action)) {
                                if (!cl.contains('selected')) {
                                    setSelectedBtnAndParent(actions[i]);
                                }
                                hasSelected = true;
                            } else {
                                cl.remove('selected');
                            }
                        }
                        if (hasSelected === false) {
                            el.classList.remove('selected');
                        }
                    }
                },
                list: {
                    result(el) {
                        exec(el.dataset.action);
                    },
                    state(el) {
                        menu.text_align.state(el);
                    }
                },
                image: {
                    state(el) {

                    },
                    result(el) {
                        const uploader = doc.createElement('div'),
                                input = doc.createElement('hidden');
                        isImageSelecting = true;
                        let file_frame;
                        input.tfOn('change', async e=> {
                            e.stopImmediatePropagation();

                            const attachment = file_frame.state().get('selection').first().toJSON(),
                                    id = attachment.id,
                                    title = attachment.title || '',
                                    alt = attachment.alt || title;
                            let img=activeEl;
                            
                            if(!isImageEdit){
                                restoreSelection();
                                const imgHtml='<img src="'+attachment.url+'" class="tb_inserted_image">';
                                exec('insertHTML', imgHtml);
                                img=activeEl.tfClass('tb_inserted_image')[0];
                                img.classList.remove('tb_inserted_image');
                                img.removeAttribute('style');
                            }
                            const isInline = img.closest('[data-hasEditor]') !== null,
                                classes = img.classList;
                                
                            if (isImageEdit || isInline) {
                                const loader = doc.createElement('div'),
                                        isResizable = img.hasAttribute('data-w') || img.hasAttribute('data-h'),
                                        w = img.hasAttribute('data-w') ? img.getAttribute('width') : false,
                                        h = img.hasAttribute('data-h') ? img.getAttribute('height') : false,
                                        __callback =  async()=> {
                                            isChanged = true;
                                            saveData(!isInline);
                                            if (isImageEdit) {
                                                const module = img.closest('.active_module');
                                                await Themify.trigger('tb_image_resize', [activeEl,model,w,h]);
                                                updateCarousel();
                                                if (module) {
                                                    await api.Utils.runJs(module, 'module');
                                                }
                                                resizeImageEditor();
                                                Themify.requestIdleCallback( ()=> {
                                                    setTimeout( ()=> {
                                                        requestAnimationFrame(resizeImageEditor);
                                                    }, 25);
                                                }, 500);//after modules updates
                                            }
                                            else{
                                                img.click();
                                            }
                                            loader.remove();
                                            
                                        };
                                if(isImageEdit){
                                    loader.className = 'tf_loader tf_abs_c';
                                    imageToolbar.appendChild(loader);
                                    imageToolbar.classList.add('tb_image_editor_loading');
                                }
                                img.removeAttribute('srcset');
                                if (!isInline && isResizable) {
                                    img.dataset.orig=attachment.url;
                                } 
                                else {
                                    img.removeAttribute('data-orig');
                                    if (isInline) {
                                        img.alt = alt;
                                        img.title = title;
                                    }
                                }
                                if (!isResizable || (isInline && !w && !h)) {
                                    img.width = attachment.width;
                                    img.height = attachment.height;
                                }
                                if ((!w && !h) || isInline) {
                                    disable();
                                    img.tfOn('load', __callback, {passive: true, once: true})
                                    .src=attachment.url;
                                } 
                                else {
                                    ThemifyImageResize.toBlob(img, w, h).finally(__callback);
                                }
                            }
                            for (let i = classes.length - 1; i > -1; --i) {
                                if (classes[i].indexOf('wp-image-') === 0) {
                                    classes.remove(classes[i]);
                                    break;
                                }
                            }
                            classes.add('wp-image-' + id);
                            input.remove();
                            uploader.remove();
                            isImageSelecting = false;
                        }, {passive: true, once: true});

                        ThemifyConstructor.mediaFile.browse(uploader, input, ThemifyConstructor, 'image');
                        uploader.click();
                        file_frame = ThemifyConstructor.mediaFile._frames.image;
                        const _close =  ()=> {
                            file_frame.off('close', _close);
                            isImageSelecting = false;
                            setTimeout( ()=> {
                                file_frame = null;
                            }, 50);
                        };
                        file_frame.on('close', _close);
                    }
                },
                link: {
                    state(el) {
                        if (selection) {
                            const link = selection.startContainer.parentNode.closest('a'),
                                    cl = toolbar.classList;
                            if (link && link === selection.endContainer.parentNode.closest('a')) {
                                linkHolder.firstChild.textContent = linkHolder.nextElementSibling.href = link.getAttribute('href');
                                cl.add('show_link');
                                calculateSize();
                                setCarret(true);
                            } else if (toolbar.classList.contains('show_link')) {
                                linkHolder.firstChild.textContent = linkHolder.nextElementSibling.href = '';
                                cl.remove('show_link');
                                calculateSize();
                                setCarret(true);
                            }
                        }
                    },
                    result(el) {
                        const linkForm = toolbar.tfClass('link_form')[0].cloneNode(true),
                                linkInput = linkForm.tfClass('link_input')[0],
                                linkType = linkForm.querySelector('#link_type'),
                                link = selection.startContainer.parentNode.closest('a'),
                                constuct = ThemifyConstructor,
                                units = {
                                    px: {
                                        min: 1,
                                        max: 50000
                                    },
                                    '%': {
                                        min: 1,
                                        max: 500
                                    }
                                };
                        linkForm.querySelector('#lb_w_holder').replaceWith(constuct.range.render({
                            id: 'lb_w',
                            control: false,
                            units: units
                        }, constuct));
                        linkForm.querySelector('#lb_h_holder').replaceWith(constuct.range.render({
                            id: 'lb_h',
                            control: false,
                            units: units
                        }, constuct));

                        const lbw = linkForm.querySelector('#lb_w'),
                                lbh = linkForm.querySelector('#lb_h'),
                                lbwUnit = linkForm.querySelector('#lb_w_unit'),
                                lbhUnit = linkForm.querySelector('#lb_h_unit');

                        linkInput.value = lbw.value = lbh.value = '';
                        linkType.selectedIndex = lbwUnit.selectedIndex = lbhUnit.selectedIndex = 0;

                        if (link && link === selection.endContainer.parentNode.closest('a')) {
                            let type = link.target;
                            linkInput.value = link.getAttribute('href');
                            if (type !== '_blank' && link.hasAttribute('data-zoom-config')) {
                                type = 'lightbox';
                            }
                            if (type) {
                                if (linkType.value !== type) {
                                    linkType.value = type;
                                }
                                if (type === 'lightbox') {
                                    const config = link.dataset.zoomConfig.split('|'),
                                            currentUW = config[0].indexOf('%') !== -1 ? '%' : 'px',
                                            currentUH = (config[1] && config[1].indexOf('%') !== -1) ? '%' : 'px';
                                    if (parseInt(config[0]) !== parseInt(lbw.value)) {
                                        lbw.value = parseInt(config[0]);
                                    }
                                    if (parseInt(config[1]) !== parseInt(lbh.value)) {
                                        lbh.value = parseInt(config[1]);
                                    }
                                    if (lbwUnit.value !== currentUW) {
                                        lbwUnit.value = currentUW;
                                    }
                                    if (lbhUnit.value !== currentUH) {
                                        lbhUnit.value = currentUH;
                                    }

                                    linkForm.querySelector('.lightbox').classList.remove('tf_hide');
                                }
                            }
                        }
                        linkForm
                            .tfOn('focusout change', submitLink,{passive:true})
                            .tfOn('submit', submitLink)
                        .querySelector('#link_type')
                        .tfOn('change', e=> {
                            const _this=e.currentTarget,
                            lb = _this.closest('form').querySelector('.lightbox');
                            lb.classList.toggle('tf_hide', _this.value !== 'lightbox');
                            restoreSelection();
                            calculateSize();
                            setCarret(true);

                        }, {passive: true});

                        dialog.append(createCloseBtn(),linkForm);
                        toolbar.classList.add('tf_hide', 'dialog_open', 'dialog_link', 'tf_opacity');
                        linkForm.classList.remove('tf_hide');

                        restoreSelection();
                        calculateSize();
                        setCarret(true);
                        setTimeout( ()=> {
                            linkInput.focus();
                        }, 100);
                    }
                },
                font: {
                    state(el) {
                        const value = doc.queryCommandValue('fontName');
                    },
                    result(el) {
                        const tooltip = doc.createElement('span');
                        let fonts = doc.createDocumentFragment(),
                                value = doc.queryCommandValue('fontName');

                        tooltip.className = 'themify_tooltip';
                        tooltip.textContent = 'themify_tooltip';

                        fonts.appendChild(ThemifyConstructor.font_select.render({id: '', control: false}, ThemifyConstructor));

                        fonts = fonts.firstChild;
                        toolbar.classList.add('tf_hide', 'dialog_open', 'dialog_font', 'tf_opacity');
                        createDialog(fonts);
                        const select = fonts.tfClass('font-family-select')[0];
                        Themify.triggerEvent(select.closest('.tb_font_preview_wrapper'), 'tf_init');
                        select.tfOn('change', function (e) {
                            e.stopPropagation();
                            isClicked = true;
                            restoreSelection();
                            exec('fontName', (this.value || 'inherit'));
                            setTimeout( ()=> {
                                saveSelection();
                                isClicked = false;
                            }, 10);
                        }, {passive: true});

                        restoreSelection();
                        calculateSize();
                        setCarret(true);
                        if (value) {
                            value = value.replace(/["']/g, '');
                            if (select.querySelector('[value="' + CSS.escape(value) + '"]')) {
                                select.value = value;
                                fonts.tfClass('themify-combo-input')[0].value = value;
                                const dropDown = fonts.tfClass('themify-combo-dropdown')[0],
                                        selected = dropDown.querySelector('[data-value="' + CSS.escape(value) + '"]');
                                if (selected) {
                                    selected.classList.add('themify-combo-selected');
                                    dropDown.style.scrollBehavior = 'auto';
                                    dropDown.scrollTop = selected.offsetTop - selected.offsetHeight;
                                    dropDown.style.scrollBehavior = '';
                                }
                            }
                        }

                    }
                },
                unlink: {
                    result(el) {
                        restoreSelection();
                        const node = selection ? selection.startContainer.parentNode.closest('a') : null;
                        if (node && node === selection.endContainer.parentNode.closest('a')) {
                            const range = doc.createRange(),
                                    anchor = win.getSelection();
                            range.selectNodeContents(node);
                            anchor.removeAllRanges();
                            anchor.addRange(range);
                        }
                        exec('unlink');
                        saveSelection();
                    }
                },
                unlinkBack: {
                    result(el) {
                        menu.unlink.result(el);
                        restoreToolbar();
                    }
                },
                color: {
                    result(el) {
                        const tooltip = doc.createElement('span'),
                                colorChange =  v=>{
                                    isClicked = true;
                                    restoreSelection();
                                    if(v===''){
                                       v='inherit';
                                    }
                                    exec('foreColor', v);
                                    if(v==='inherit' && selection && selection.commonAncestorContainer){
                                        const spans=selection.commonAncestorContainer.querySelectorAll('span[style]');
                                        for(let i=spans.length-1;i>-1;--i){
                                            if(spans[i].style.color==='inherit'){
                                                spans[i].style.color='';
                                                if(!spans[i].style){
                                                    spans[i].outerHTML = spans[i].innerHTML;
                                                }
                                            }
                                        }
                                        onChange();
                                    }
                                    setTimeout( ()=>{
                                        saveSelection();
                                        isClicked = false;
                                    }, 10);
                                };
                        let colorInput = doc.createDocumentFragment(),
                                value = doc.queryCommandValue('foreColor');
                        tooltip.className = 'themify_tooltip';
                        tooltip.textContent = 'themify_tooltip';
                        colorInput.appendChild(ThemifyConstructor.color.render({id: '', control: false}, ThemifyConstructor));

                        colorInput = colorInput.firstChild;
                        toolbar.classList.add('tf_hide', 'dialog_open', 'dialog_color', 'tf_opacity');
                        createDialog(colorInput);

                        const input = colorInput.tfClass('tfminicolors-input')[0],
                                swatch = colorInput.tfClass('tfminicolors-swatch')[0],
                                opacityInput = colorInput.tfClass('color_opacity')[0];

                        if (value) {
                            let opacity = 1;
                            if (value.indexOf('rgb') > -1) {
                                opacity = value.indexOf('rgba') > -1 ? parseFloat(value.split(',').slice(-1).pop()) : 1;
                                const rgb = value.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
                                value = (rgb && rgb.length === 4) ? ("#" +
                                        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                                        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                                        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2)) : '';
                            }
                            input.value = value;
                            opacityInput.value = opacity;

                        }
                        swatch.click();
                        input.tfOn('themify_builder_color_picker_change',e=> {
                            e.stopPropagation();
                            const v=e.detail.val;
                            if (doc.activeElement.id === 'dialog_content' || !dialog.contains(doc.activeElement) || v==='') {
                                colorChange(v);
                            }
                        }, {passive: true})
                        .tfOn('change', function (e) {
                            e.stopPropagation();
                            colorChange($(this).tfminicolors('rgbaString'));
                        }, {passive: true});

                        opacityInput.tfOn('change',  e=> {
                            isChanged = true;
                            e.stopPropagation();
                            Themify.triggerEvent(input, 'change');
                        }, {passive: true});

                        calculateSize();
                        setCarret(true);
                    }
                }
            },
            saveData =changeSrc=>{
                if (activeEl && isChanged) {
                    if (!model) {
                        return;
                    }
                    const  isOpen = api.activeModel===model;
                    let index = false,
                        editImage = !changeSrc && isImageEdit,
                        editElement = activeEl,
                        data=new Map();
                    isTyping=true;
                    if (editImage) {
                        const wName = editElement.dataset.w,
                        hName = editElement.dataset.h;
                        if (!wName && !hName) {
                            editElement = editElement.closest('[data-hasEditor]');
                            if (!editElement) {
                                return;
                            }
                            editImage = false;
                            is_editable = true;
                        }
                        if (editImage) {
                            if (wName) {
                                data.set(wName,editElement.getAttribute('width'));
                            }
                            if (hName) {
                                data.set(hName,editElement.getAttribute('height'));
                            }
                        }
                    }
                    if(!editImage){
                        const name=editElement.dataset.name;
                        if(name){
                            let val=editElement.tagName === 'IMG' ? (editElement.dataset.orig|| editElement.src) : (is_editable ? editElement.innerHTML : editElement.innerText);
                            if(modelClass){
                                val=modelClass.save(model,activeEl,name,val,data,!isOpen);
                            }
                            if(val!==false && val!==null){
                                data.set(name,val);
                            }
                        }
                    }
                    if (data.size===0) {
                        return;
                    }
                    let repeat = editElement.dataset.repeat;
                    if (repeat && !isNaN(repeat)) {//should be string,otherwise it it's different data-repeat
                        repeat = false;
                    }
                    if (repeat) {
                        index = editElement.hasAttribute('data-index') ? parseInt(editElement.dataset.index) : false;
                        if (index === false) {
                            let el = editElement.closest('.tf_swiper-slide');
                            if (!el) {
                                el = editElement.closest('li');
                                if (!el) {
                                    el = editElement.closest('.module-' + model.get('mod_name') + '-item');
                                }
                            }
                            if (el) {
                                const childs = el.parentNode.children;
                                for (let i = childs.length - 1; i > -1; --i) {
                                    if (childs[i] === el) {
                                        index = i;
                                        break;
                                    }
                                }
                            }
                        }
                        if (index === false) {
                            return;
                        }
                    }
                    
                    if (isOpen) {
                        const lb = api.LightBox.el,
                           // args = {el: '', val: val, data: settings, cid: cid, activeEl: activeEl},
                            p=repeat?lb.querySelector('#' + repeat):null;
                        let r;
                        if (repeat) {
                            if (ThemifyConstructor.settings[ repeat ] === und) {
                                ThemifyConstructor.settings[ repeat ] = [];
                            }
                            if (ThemifyConstructor.settings[ repeat ][index] === und) {
                                ThemifyConstructor.settings[ repeat ][index] = {};
                            }
                            r=ThemifyConstructor.settings[ repeat ][index];
                        } 
                        else {
                            r=ThemifyConstructor.settings;
                        }
                        for(let [k,v] of data){
                            r[k] = v;
                            let item;
                            if (!item) {
                                item = p!==null ? p.querySelectorAll('[data-input-id="' + k + '"]')[index] : lb.querySelector('.tb_lb_option#' + k);
                            }
                            if (item && item.value != v) {
                                item.value = v;
                                let bg = item.closest('.tb_uploader_wrapper');
                                if (bg) {
                                    bg = bg.querySelector('.tb_media_uploader img');
                                    if (bg) {
                                        bg.src = v;
                                    }
                                }
                                else if (is_editable && typeof tinyMCE !== 'undefined' && tinyMCE) {
                                    const el = tinyMCE.get(item.id);
                                    if (el) {
                                        el.setContent(v);
                                    }
                                }
                            }
                        }
                    } 
                    else {
                        const settings = model.get('mod_settings') || {};
                        if (repeat) {
                            if (settings[repeat] === und) {
                                settings[repeat]=[];
                            }
                            if (settings[repeat][index] === und) {
                                settings[repeat][index] = {};
                            }
                            for(let [k,v] of data){
                                settings[repeat][index][k]=v;
                            }
                        }
                        else{
                            for(let [k,v] of data){
                                settings[k]=v;
                            }
                        }
                        model.set('mod_settings',settings);
                    }
                    updateCarousel();
                    if(!editImage){
                        if(stackIndex===-1 && firstVal){
                            pushUndo(firstVal);
                            firstVal=null;
                        }
                        pushUndo(activeEl.innerHTML);
                    }
                }
            },
            preventDefault = e=> {
                e.preventDefault();
            },
            dblclick = e=> {
                e.stopImmediatePropagation();
                if (isImageEdit && activeEl && (activeEl.hasAttribute('data-name') || activeEl.closest('[data-hasEditor]')) && imageToolbar.contains(e.target) && !e.target.closest('.image_menu')){
                    menu.image.result();
                }
            },
            keypres=e=>{
                if(!model || !activeEl || !is_editable || isImageEdit){
                    topWindow.tfOff('keydown',keypres);
                    doc.tfOff('keydown',keypres);
                    return;
                }
                if ((true === e.ctrlKey || true === e.metaKey)){
                    const activeTag = doc.activeElement.tagName,
                            topActiveTag = topWindow.activeElement.tagName,
                            key = e.code;
                    if (activeTag !== 'INPUT' && activeTag !== 'TEXTAREA' && topActiveTag !== 'INPUT' && topActiveTag !== 'TEXTAREA') {
                        if ('KeyY' === key || ('KeyZ' === key && true === e.shiftKey)) {// Redo
                            e.preventDefault();
                            if (menu.redo.state()) {
                                menu.redo.result();
                            }
                        } 
                        else if ('KeyZ' === key) { // UNDO
                            e.preventDefault();
                            if (menu.undo.state()) {
                               menu.undo.result();
                            }
                        }
                    }
                }
            },
            init = ()=> {
                let isEnabled=false;
                api.Builder.get().el.tfOn('pointerdown', e=>{
                    isEnabled=isTyping=false;
                    if (api.isPreview===false && api.inlineEditor === true && e.which === 1 && !e.ctrlKey && !e.metaKey) {
                        isImageEdit = e.target.tagName === 'IMG';
                        const item = isImageEdit ? e.target : e.target.closest('[contenteditable]');
                        if (item && !item.closest('.tb_disable_sorting') && (isImageEdit || item.contentEditable === false || item.contentEditable === 'false')) {
                            selectionEnd();
                            win.getSelection().removeAllRanges();

                            const activeModule = item.closest('.active_module');
                            if (activeModule) {
                                const m = api.Registry.get(activeModule.dataset.cid);
                                if (!m || (m.get('mod_settings')['__dc__'] !== und && m.get('mod_settings')['__dc__'][item.dataset.name] !== und)) {
                                    disable();
                                    return;
                                }
                                activeModule.classList.add('tb_editor_clicked');
                            }
                            if (!isImageEdit) {
                                item.contentEditable = true;
                            }
                            let draggables = [],
                                    _item = item.closest('[draggable]');
                            while (_item !== null) {
                                draggables.push(_item);
                                if(!_item.parentNode!==null){
                                    _item = _item.parentNode.closest('[draggable]');
                                }
                            }
                            if (draggables.length > 0) {
                                const _up = e=> {
                                    doc.tfOff('pointermove', _drag, {passive: true, once: true});
                                    for (let i = draggables.length - 1; i > -1; --i) {
                                        draggables[i].tfOff('drag', _drag, {passive: true, once: true}).draggable=false
                                    }
                                },
                                _drag = e=> {
                                    doc.tfOff('pointermove', _drag, {passive: true, once: true});
                                    item.tfOff('pointerup', _up, {passive: true, once: true}).setAttribute('contenteditable', false);
                                    if (activeModule) {
                                        activeModule.classList.remove('tb_editor_clicked');
                                    }
                                    for (let i = draggables.length - 1; i > -1; --i) {
                                        draggables[i].tfOff('drag', _drag, {passive: true, once: true});
                                    }
                                    disableImageEditor();
                                    if (e.type === 'drag') {
                                        disable();
                                    }
                                };
                                item.tfOn('pointerup', _up, {passive: true, once: true});
                                doc.tfOn('pointermove', _drag, {passive: true, once: true});
                                for (let i = draggables.length - 1; i > -1; --i) {
                                    draggables[i].tfOn('drag', _drag, {passive: true, once: true});
                                }
                            }
                            isEnabled=true;
                        }
                    }
                    else{
                        disable();
                    }
                }, {passive: true})
                .tfOn(Themify.click, e=> {
                    if (!isEnabled){
                        return;
                    }
                    let target = e.target;
                        if (target.hasAttribute('data-target')) {
                            let targetItem = target.closest('.active_module');
                            if (targetItem) {
                                targetItem = targetItem.querySelector(target.dataset.target);
                                if (targetItem) {
                                    target = targetItem;
                                }
                            }
                        }
                        isImageEdit = target.tagName === 'IMG';
                        const item = isImageEdit ? target : target.closest('[contentEditable]');

                        if (!item) {
                            disable();
                            return;
                        }
                        if (item.closest('label,a,button')) {
                            e.preventDefault();
                        }
                        if (activeEl === item) {
                            return;
                        }
                        if (e.which !== 1 || (isImageEdit && !item.dataset.w&& !item.dataset.h && !item.dataset.name && !item.closest('[data-hasEditor]'))) {
                            disable();
                            return;
                        }
                        const activeModule = item.closest('.active_module');
                        if(!activeModule){
                            disable();
                            return;
                        }
                        disable();
                        model= api.Registry.get(activeModule.dataset.cid);
                        if(!isImageEdit){
                            let slug=model.get('mod_name').split('-'),
                            clName=slug[0];
                            for(let i=1,l=slug.length;i<l;++i){
                                clName+=slug[i].charAt(0).toUpperCase() + slug[i].slice(1);
                            }
                            modelClass=api.BaseInLineEdit.items.get(clName);
                        }
                        if(!api.activeModel || model.id!==api.activeModel.el){
                            api.undoManager.start('inline',activeModule);
                        }
                        api.undoManager.disable();
                        isChanged = false;
                        activeEl = item;
                        bodyCL = doc.body.classList;
                        selectionEnd();
                        host.classList.add('tb_editor_active');
                        bodyCL.add('tb_editor_active');
                        topWindow.body.classList.add('tb_editor_active');
                        is_editable = !isImageEdit && item.hasAttribute('data-hasEditor');
                      
                        activeModule.classList.add('tb_editor_on');
                        activeModule.classList.remove('tb_editor_clicked');
                        let wow = activeEl,
                                form = item.closest('form'),
                                _item = item.closest('[draggable]');

                        if (form) {
                            const formInputs = form.tfOn('submit', preventDefault).querySelectorAll('[required]');
                            for (let i = formInputs.length - 1; i > -1; --i) {
                                formInputs[i].removeAttribute('required');
                                formInputs[i].dataset.required=1;
                            }
                        }
                        while (wow && wow !== activeModule) {
                            if (wow.style.animationName) {
                                wow.dataset.tmpAnimation=wow.style.animationName;
                                wow.style.animationName = 'none';
                            }
                            wow = wow.parentNode;
                        }
                        while (_item !== null) {
                            _item.draggable=false;
                            _item = _item.parentNode.closest('[draggable]');
                        }
                        wow = _item = form = null;
                        swiper = activeEl.closest('.tf_swiper-container');
                        swiper = (swiper && swiper.swiper) ? swiper.swiper : null;
                        if (swiper && swiper.params && swiper.params.autoplay && swiper.params.autoplay.enabled) {
                            swiper.el.dataset.stopped = true;
                            swiper.autoplay.stop();
                            if (swiper.params.thumbs && swiper.params.thumbs.swiper && swiper.params.thumbs.swiper.autoplay && swiper.params.thumbs.swiper.autoplay.enabled) {
                                swiper.params.thumbs.swiper.el.dataset.stopped = true;
                                swiper.params.thumbs.swiper.autoplay.stop();
                            }
                        }
                        if (!isImageEdit) {
                            activeEl.contentEditable = true;
                            firstVal=activeEl.innerHTML;
                            activeEl.tfOn('input', onChange, {passive: true}).focus();
                            if (!Themify.isTouch) {
                                setTimeout( ()=> {
                                    if (activeModule) {
                                        activeModule.tfOff('dblclick', dblclick, {passive: true})
                                        .tfOn('dblclick', dblclick, {passive: true});
                                
                                        if (!themifyBuilder.disableShortcuts) {
                                            topWindow.tfOn('keydown',keypres);
                                            doc.tfOn('keydown',keypres);
                                        }
                                    }
                                }, 800);
                            }
                        } 
                        else {
                            bodyCL.add('tb_editor_image_active');
                            topWindow.body.classList.add('tb_editor_image_active');
                            host.classList.add('tb_editor_image_active');
                            Themify.on('tfsmartresize', resizeImageEditor)
                                    .on('tbresizeImageEditor', resizeImageEditor);
                        }
                        Themify.on('tbDisableInline', disable);
                        doc.tfOn('pointerdown', disable, {passive: true});
                        topWindow.tfOn('pointerdown', disable, {passive: true});


                        if (is_editable) {
                            doc.tfOn('selectionchange', selectionStart, {passive: true});
                            setToolbar();
                            calculateSize();
                            toolbar.tfOn(Themify.click, toolbarActions)
                            .tfOn('pointerdown', toolbarMouseDown);
                            linkHolder = toolbar.querySelector('#link_btn');
                            dialog = toolbar.querySelector('#dialog');
                            setTimeout(() => {
                                requestAnimationFrame(setCarret);
                                toolbar.querySelector('.expand').classList.toggle('tf_hide', api.activeModel===model);
                            },30);
                        } 
                        else if (isImageEdit) {
                            if (item.closest('.masonry')) {
                                Themify.requestIdleCallback(() => {
                                    setTimeout(() => {
                                        requestAnimationFrame(() => {
                                            imageEditing(item);
                                        });
                                    }, 25);
                                }, 500);
                            } else {
                                imageEditing(item);
                            }
                        }
                });
            },
            calculateSize =  ()=>  {
                const className = toolbar.className,
                        cl = toolbar.classList;
                cl.add('tf_hidden');
                cl.remove('tf_hide');
                const rect = toolbar.getBoundingClientRect();
                width = rect.width + 40;
                height = rect.height + 30;
                toolbar.className = className;
            },
            saveSelection =  ()=>  {
                const sel = win.getSelection();
                selection = sel.isCollapsed === false ? sel.getRangeAt(0).cloneRange() : null;
            },
            restoreSelection =  ()=> {
                if (isChanging === false && selection) {
                    isChanging = true;
                    activeEl.focus();
                    const sel = win.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(selection);
                    isChanging = false;
                }
            },
            setToolbar =  ()=> {
                if (toolbar === null) {
                    if(host.classList.contains('tf_hide')){
                        const fr = host.firstElementChild,
                        fragment=doc.createDocumentFragment(),
                        combineCss=api.MainPanel.el.getRootNode().querySelector('#module_combine_style').cloneNode(true),
                        formCss=doc.tfId('tmpl-builder_row_action').content.querySelector('#module_form_fields_style').cloneNode(true);
                        fragment.append(api.ToolBar.getBaseCss(),combineCss,formCss); 
                        if (fr) { // shadowroot="open" isn't support
                            host.attachShadow({
                                mode: fr.getAttribute('shadowroot')
                            }).appendChild(fr.content);
                            fr.remove();
                        }
                        host.shadowRoot.prepend(fragment);
                        host.classList.remove('tf_hide');
                    }
                    toolbar = host.shadowRoot.tfId('toolbar');
                    toolbarItems = toolbar.querySelectorAll('[data-type]');
                }
                
                return toolbar;
            },
            toolbarActions = e=>{
                const item = e.target.closest('.action');
                e.stopPropagation();
                if (!item || item.type === 'submit') {
                    return;
                }
                e.preventDefault();
                isClicked = true;
                const type = item.closest('[data-type]').dataset.type;
                if (type !== 'expand') {
                    if(stackIndex===-1 && firstVal){
                        pushUndo(firstVal);
                        firstVal=null;
                    }
                    if (menu[type] !== und && menu[type].result !== und) {
                        menu[type].result(item);
                    } else {
                        restoreSelection();
                        exec(type);
                    }
                    setSelectedButtons();
                } else if (!item.classList.contains('disable')) {
                    win.getSelection().removeAllRanges();
                    model.edit();
                    disable();
                }
                isClicked = false;
            },
            toolbarMouseDown=e=>{
                const item=e.composedPath()[0];
                if(item.tagName!=='BUTTON' && item.tagName!=='SELECT' && item.tagName!=='INPUT'){
                    e.stopPropagation();
                    e.preventDefault();
                }
            },
            onChange = e=>{
                if (e) {
                    e.stopPropagation();
                }
                if (activeEl) {
                    isChanged = true;
                    saveData();
                }
            },
            submitLink = function (e) {
                e.stopPropagation();
                if (e.type==='submit' || (e.type === 'focusout' && !e.target.classList.contains('tb_range')) || (e.type === 'change' && e.target.classList.contains('tb_range'))) {
                    if(e.type==='submit'){
                        e.preventDefault();
                    }
                    return;
                }
         
                const linkInput = this.tfClass('link_input')[0],
                        link = linkInput.value.trim();
                if (link) {
                    isChanged = true;
                    restoreSelection();
                    let anchor = win.getSelection();
                    const node = selection ? selection.startContainer.parentNode.closest('a') : null,
                            _this = this,
                            type = _this.querySelector('#link_type').value;
                    if (node && node === selection.endContainer.parentNode.closest('a')) {
                        const range = doc.createRange();
                        range.selectNodeContents(node);
                        anchor.removeAllRanges();
                        anchor.addRange(range);
                    }
                    exec('createLink', link);
                    saveSelection();
                    if (anchor.anchorNode && anchor.anchorNode.parentNode) {
                        const targetA = anchor.anchorNode.parentNode.closest('a');
                        if (targetA) {
                            targetA.removeAttribute('target');
                            targetA.removeAttribute('data-zoom-config');
                            targetA.classList.remove('themify_lightbox');
                            if (type === '_blank') {
                                targetA.target=type;
                            } else if (type === 'lightbox') {
                                targetA.classList.add('themify_lightbox');
                                const w = _this.querySelector('#lb_w').value,
                                        h = _this.querySelector('#lb_h').value,
                                        uW = _this.querySelector('#lb_w_unit').value || 'px',
                                        uH = _this.querySelector('#lb_h_unit').value || 'px';
                                if (w > 0 || h > 0) {
                                    let config = '|';
                                    if (w) {
                                        config = w + uW + config;
                                    }
                                    if (h) {
                                        config += h + uH;
                                    }
                                    targetA.dataset.zoomConfig=config;
                                }
                            }
                        }
                    }
                }
            },
            setSelectedBtnAndParent =  item=> {
                const li = item.parentNode,
                        parent = li.parentNode.closest('li');
                li.classList.add('selected');

                if (parent) {
                    parent.classList.add('selected');
                    const button = parent.tfClass('action')[0];
                    if (button !== und) {
                        button.replaceWith(item.cloneNode(true));
                    }
                }
            },
            setSelectedButtons =  ()=>{
                for (let i = toolbarItems.length - 1; i > -1; --i) {
                    let item = toolbarItems[i],
                            type = item.dataset.type,
                            li = item.tagName === 'LI' ? item : item.parentNode;
                    if (type!=='expand' && type!=='undo' &&  type!=='image' && type!=='redo' && !li.classList.contains('disabled')) {
                        if (menu[type] !== und && menu[type].state !== und) {
                            menu[type].state(li);
                        } else {
                            li.classList.toggle('selected', state(type));
                        }
                    }
                }
            },
            setCarret = onlycarret=>{
                if (toolbar === null) {
                    return;
                }
                const sel = win.getSelection(),
                    cl = toolbar.classList,
                    isCollapsed=sel.isCollapsed;
                if(toolbarItems){
                    for(let i=toolbarItems.length-1;i>-1;--i){
                        let item=toolbarItems[i],
                            type=item.dataset.type;
                        if(type!=='image' && type!=='expand'){
                            let li = item.tagName === 'LI' ? item : item.parentNode,
                                enabled=type!=='undo' && type!=='redo'?isCollapsed:!menu[type].state();
                            li.classList.toggle('disabled',enabled);
                        }
                    }
                }
                if (cl.contains('tf_hide')) {
                    calculateSize();
                    cl.remove('tf_hide');
                    requestAnimationFrame( ()=> {
                        cl.remove('tf_opacity');
                    });
                }
                let range;
                if (isCollapsed === false) {
                    saveSelection();
                    range=selection;
                    if (onlycarret === und) {
                        setSelectedButtons();
                    }
                    if (bodyCL) {
                        if (!bodyCL.contains('tb_editor_start_select')) {
                            host.classList.add('tb_editor_start_select');
                            bodyCL.add('tb_editor_start_select');
                            topWindow.body.classList.add('tb_editor_start_select');
                        }
                        if (selectionEndTimeout) {
                            clearTimeout(selectionEndTimeout);
                        }
                        selectionEndTimeout = setTimeout(selectionEnd, 500);
                    }
                } else {
                    selection = null;
                    restoreToolbar();
                    if(sel.rangeCount === 0){
                        return;
                    }
                    range=sel.getRangeAt(0).cloneRange();
                }
                
                let box = range.getBoundingClientRect();
                    if(box.left===0 && box.top===0){
                        box=activeEl.getBoundingClientRect();
                    }
                let diff=getPos(range.startContainer.parentNode),
                    left = box.left + (box.width - width) / 2,
                    top = box.top - height - 10-diff.diffTop,
                    changePos=isCollapsed===false || isTyping===false;
                    if ((left + width) >= Themify.w) {
                        left -= width + Math.ceil(left) + win.pageXOffset - Themify.w - 1;
                    }
                if(isTyping===true && changePos===false){
                    const tTop=toolbar.getBoundingClientRect().top;
                    changePos=tTop<=0 || Math.abs(tTop-top)>160;
                }
                if(changePos===true){
                    
                    cl.toggle('top_viewport', top <= 0);
                    if (top <= 0) {
                        top = box.bottom + 10;
                    }
                    if (left <= 0) {
                        left = 15;
                    }
                    top += win.pageYOffset;
                    toolbar.style.transform = 'translate(' + left + 'px,' + top + 'px)';
                }
            },
            selectionStart =  ()=> {
                if (activeEl !== null && isChanging === false && isClicked === false && doc.activeElement === activeEl) {
                    if (timer2) {
                        clearTimeout(timer2);
                    }
                    timer = requestAnimationFrame( ()=> {
                        timer2 = setTimeout( ()=> {
                            if (activeEl !== null && isChanging === false && isClicked === false && doc.activeElement === activeEl) {
                                setCarret();
                            }
                        }, 20);
                    });
                }
            },
            selectionEnd = ()=> {
                if (bodyCL && bodyCL.contains('tb_editor_start_select')) {
                    topWindow.body.classList.remove('tb_editor_start_select');
                    bodyCL.remove('tb_editor_start_select');
                    host.classList.remove('tb_editor_start_select');
                }
                if (selectionEndTimeout) {
                    clearTimeout(selectionEndTimeout);
                }
                selectionEndTimeout = null;
            },
            createCloseBtn =  ()=> {
                const btn = doc.createElement('button');
                btn.type = 'button';
                btn.className = 'tf_close';
                btn.title = 'Back';
                btn.tfOn(Themify.click, e=> {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    restoreToolbar();
                    toolbar.classList.add('tf_hide', 'tf_opacity');
                    restoreSelection();
                    calculateSize();
                    setCarret();

                }, {once: true});
                return btn;
            },
            createDialog = content=> {
                const dialogHeader = doc.createElement('div'),
                        dialogContent = doc.createElement('div');

                dialogHeader.id = 'dialog_header';
                dialogContent.id = 'dialog_content';
                dialogContent.tabIndex = '-1';
                dialogHeader.appendChild(createCloseBtn());
                dialogContent.appendChild(content);

                dialog.append(dialogHeader,dialogContent);
            },
            restoreToolbar =  ()=> {
                if (toolbar) {
                    toolbar.style.display = 'none';
                    if (dialog && dialog.firstChild) {
                        while (dialog.firstChild) {
                            dialog.removeChild(dialog.lastChild);
                        }
                    }
                    toolbar.classList.remove('dialog_open', 'dialog_color', 'dialog_font', 'dialog_link', 'show_link');
                    toolbar.style.display = '';
                    calculateSize();
                }
            },
            imageEditing = item=> {
                disableImageEditor();
                if(toolbar===null){
                    setToolbar();
                }
                if(imageToolbar===null){
                    imageToolbar = doc.createElement('div');
                    const rect = getPos(item),
                        src = activeEl.dataset.orig || activeEl.currentSrc.trim(),
                        help = src.indexOf(Themify.upload_url) === -1 && src.indexOf('blob:')===-1? themifyBuilder.i18n.img_help : (activeEl.closest('[data-hasEditor]') !== null ? themifyBuilder.i18n.img_help2 : false),
                        handlers = ['w', 's', 'e', 'n'],
                        imageNavs={hflip:'ti-split-h',vflip:'ti-split-v',rotate:'ti-reload',pallete:'ti-palette',undo:'ti-back-left',redo:'ti-back-right'},
                        imageMenu=doc.createElement('div'),
                        f=doc.createDocumentFragment(),
                        navF=doc.createDocumentFragment();
                    imageToolbar.id = 'image_editor';
                    imageToolbar.className='tf_abs_t';
                    imageToolbar.style.transform = 'translate('+rect.left+'px,'+rect.top + 'px)';
                    imageToolbar.style.width = rect.width + 'px';
                    imageToolbar.style.height = rect.height + 'px';
                    imageMenu.className='image_menu';
                    for (let i = handlers.length - 1; i > -1; --i) {
                        let handler = doc.createElement('div'),
                                border = doc.createElement('div'),
                                tooltip = doc.createElement('div');
                        handler.className = 'image_handler tf_abs_t tb_editor_' + handlers[i];
                        border.className = 'image_border tf_abs_t border_' + handlers[i];
                        tooltip.className = 'image_tooltip tf_opacity tf_textc tf_abs_t image_tooltip_' + handlers[i];
                        border.dataset.axis=handlers[i];
                        handler.dataset.axis=handlers[i];
                        f.append(border,tooltip,handler);
                    }
                    if (help !== false) {
                        f.appendChild(ThemifyConstructor.help(help));
                    }
                    else if(false){//temp disable
                        for(let k in imageNavs){
                            let item = doc.createElement('button'),
                                tooltip=doc.createElement('span');
                            item.dataset.action=k;
                            item.type='button';
                            item.className='tf_rel';
                            tooltip.className='themify_tooltip';
                            tooltip.textContent=themifyBuilder.i18n.img_menu[k];
                            item.append(tooltip,api.Helper.getIcon(imageNavs[k]));
                            navF.appendChild(item);
                        }
                        imageMenu.appendChild(navF);   
                        imageMenu.tfOn(Themify.click, imageMenuActions, {passive: true});
                        f.appendChild(imageMenu);
                    }
                    imageToolbar.tfOn('pointerdown', imageResizing, {passive: true});
                    if (!Themify.isTouch) {
                        imageToolbar.tfOn('dblclick', dblclick, {passive: true});
                    }
                    activeEl.classList.add('tb_selected_img');
                    imageToolbar.appendChild(f);
                    host.shadowRoot.appendChild(imageToolbar);
                }
            },
            imageResizing = async function (e) {
                if (e.which !== 1) {
                    return;
                }
                const target = e.target;
                e.stopPropagation();
                if (activeEl && (activeEl.hasAttribute('data-w') || activeEl.hasAttribute('data-h') || activeEl.closest('[data-hasEditor]')) && (target.classList.contains('image_handler') || target.classList.contains('image_border'))) {
                    this.style.willChange = 'width,height,transform';
                    activeEl.parentNode.style.willChange = 'contents';
                    activeEl.style.willChange = 'width,height';
                    let frame,
                            timeout,
                            clientX,
                            clientY,
                            prevW,
                            prevH,
                            parentMaxWidthEl = activeEl.dataset.maxw,
                            parentNode= null,
                            isMoved = false;
                    if(!imageObj){
                        imageObj=new ThemifyImageResize(activeEl);
                    }
                    if (parentMaxWidthEl) {
                        parentMaxWidthEl = activeEl.closest(parentMaxWidthEl);
                    }
                    if (!parentMaxWidthEl) {
                        parentMaxWidthEl = activeEl.closest('[data-hasEditor]');
                        if (!parentMaxWidthEl) {
                            parentMaxWidthEl = activeEl.parentNode;
                        }
                    }
                    
                    const axis = target.dataset.axis,
                            keepRatio = target.classList.contains('image_handler'),
                            el = this,
                            _MINWIDTH_ = 40,
                            _MINHEIGHT_ = 40,
                            _MAXWIDTH_ = Number.MAX_VALUE, 
                            rect = activeEl.getBoundingClientRect(),
                            startW = parseInt(rect.width),
                            startH = parseInt(rect.height),
                            aspectRatio = parseFloat(startW / startH),
                            resizeX = e.clientX,
                            resizeY = e.clientY,
                            tooltips = el.tfClass('image_tooltip'),
                            isLocal = imageObj.isLocal,
                            loader = doc.createElement('div'),
                            move =  e=> {
                                e.stopPropagation();
                                clientX=e.clientX;
                                clientY=e.clientY;
                                if (isMoved === false) {
                                    isMoved = true;
                                }
                                
                                if (frame) {
                                    cancelAnimationFrame(frame);
                                }
                                frame = requestAnimationFrame( ()=>{

                                    if (activeEl === null) {
                                        disable();
                                        return;
                                    }
                                    let w,
                                            h;
                                    if (keepRatio === true) {
                                        w = axis === 'n' || axis === 's' ? (resizeX + startW - clientX) : (startW + clientX - resizeX);
                                        h = parseInt(w / aspectRatio);
                                    } else {
                                        if (axis === 's' || axis === 'n') {
                                            h = axis === 's' ? (startH + clientY - resizeY) : (resizeY + startH - clientY);
                                            w = startW;
                                        } else {
                                            w = axis === 'w' ? (startW + clientX - resizeX) : (resizeX + startW - clientX);
                                            h = startH;
                                        }
                                    }
                                    if (w < _MINWIDTH_) {
                                        w = _MINWIDTH_;
                                    } else if (w > _MAXWIDTH_) {
                                        w = _MAXWIDTH_;
                                    }
                                    if (h !== und && h < _MINHEIGHT_) {
                                        h = _MINHEIGHT_;
                                    }
                                    if (prevW !== w || prevH !== h) {
                                        prevW = w;
                                        prevH = h;
                                        imageObj.resize(w,h).then(params=>{
                                            const box=getPos(activeEl); 
                                            el.style.transform='translate('+box.left+'px,'+box.top + 'px)';
                                            el.style.width = box.width + 'px';
                                            el.style.height = box.height + 'px';
                                            tooltips[0].textContent = tooltips[3].textContent = parseInt(params[1]) + 'px';
                                            tooltips[1].textContent = tooltips[2].textContent = parseInt(params[2]) + 'px';
                                            if (parentNode !== null) {
                                                if (parentNode.style.width) {
                                                    parentNode.style.width = params[1] + 'px';
                                                }
                                                if (parentNode.style.height) {
                                                    parentNode.style.height = params[2] + 'px';
                                                }
                                            }
                                            updateCarousel();
                                        });
                                    }
                                });
                            },
                            mouseup = async function (e) {
                                if (e) {
                                    e.stopPropagation();
                                }
                                if (frame) {
                                    cancelAnimationFrame(frame);
                                }
                                if (timeout) {
                                    clearTimeout(timeout);
                                }
                                this.tfOff('pointermove', move, {passive: true})
                                    .tfOff('lostpointercapture', mouseup, {passive: true, once: true});
                                host.classList.remove('tb_image_editor_resizing');
                                const bodyClasses = bodyCL || doc.body.classList;
                                bodyClasses.remove('tb_start_animate', 'tb_image_editor_resizing');
                                el.style.willChange = activeEl.style.willChange = activeEl.parentNode.style.willChange = '';
                                if (isMoved === true) {
                                    isChanged = true;
                                    let module = activeEl.closest('.active_module,.module_column');
                                    saveData();
                                     const w = activeEl.getAttribute('width'),
                                        h = activeEl.getAttribute('height'),
                                        update = () =>{
											return new Promise(async resolve=>{
													await Themify.trigger('tb_image_resize', [activeEl,model,w,h]);
													updateCarousel();
													if (module) {
														await api.Utils.runJs(module, 'module');
													}
													resizeImageEditor();
													setTimeout( () =>{
														requestAnimationFrame(resizeImageEditor);
														if(api.undoManager.has('inline')){
															api.undoManager.end('inline');
														}
														resolve();
													}, 25);
												});
                                    };
                                    if (module) {
                                        const items = module.querySelectorAll('img[data-w="' + activeEl.dataset.w + '"]');
                                        
                                        if (items.length > 1) {
                                            const promises = [];
                                            if (isLocal) {
                                                module.classList.add('tf_image_editor_working');
                                            }
                                            for (let i = items.length - 1; i > -1; --i) {
                                                if (items[i] !== activeEl) {
                                                    try{
                                                        let tmp=new ThemifyImageResize(items[i]);
                                                        promises.push(tmp.highQuality(w,h));
                                                    }
                                                    catch(e){

                                                    }
                                                }
                                            }
                                            try{
                                                await Promise.all(promises);
                                                await update();
                                            }
                                            catch(e){
                                                
                                            }
                                            module.classList.remove('tf_image_editor_working');
                                        } 
                                        else {
                                            imageObj.highQuality(w,h);
                                            update();
                                        }
                                    } 
                                    else {
                                        update();
                                    }
                                }
                                isMoved = false;
                                frame = timeout = prevW = prevH= filterImage=null;
                            };
                        
                    host.classList.add('tb_image_editor_resizing');
                    bodyCL.add('tb_start_animate', 'tb_image_editor_resizing');
                    loader.className = 'tf_loader tf_abs_c';
                    this.appendChild(loader);
                    this.classList.add('image_loading');
                    if(imageObj.isLocal===false){
                        const msg=activeEl.closest('[data-hasEditor]')?'img_help2':'img_help';
                        TF_Notification.showHide('warning',themifyBuilder.i18n[msg],4000);
                    }
                    else{
                        parentNode = activeEl.parentNode;
                        if(parentNode === null || parentNode.tagName === 'FIGURE'){
                            parentNode=null;
                        }
                    }
                    imageObj.lowQuality().then(()=>{
                        if (imageObj.isBig===true) {
                            TF_Notification.showHide('warning',themifyBuilder.i18n.img_big.replace("%w",activeEl.naturalWidth).replace("%h",activeEl.naturalHeight),4000);
                        }
                        target.tfOn('pointermove', move, {passive: true});
                        el.classList.remove('image_loading');
                        loader.remove();
                        target.setPointerCapture(e.pointerId);
                    });
                    target.tfOn('lostpointercapture', mouseup, {passive: true, once: true});
                }
            },
            imageMenuActions=async e=>{
                e.stopPropagation();
                const btn=e.target?e.target.closest('button'):null;
                if(btn){
                    const action = btn.dataset.action;
                    if(action){
                        const obj=new ThemifyImageResize(activeEl,1,1),
                                parent=e.currentTarget;
                        obj.prms.then(()=>{
                            if (obj.isBig===true) {
                                TF_Notification.showHide('warning',themifyBuilder.i18n.img_big.replace("%w",activeEl.naturalWidth).replace("%h",activeEl.naturalHeight),4000);
                                return;
                            }
                            let prms;
                            if(action==='hflip' || action==='vflip'){
                                prms=obj.flip(action==='vflip');
                                filterImage=null;
                            }
                            else if(action==='rotate'){

                            }
                            else if(action==='pallete'){
                                let root = doc.tfId('tb_pallete_root');
                                if(root){
                                    const fr = root.firstElementChild,
                                    fragment=doc.createDocumentFragment();
                                    if (fr) { // shadowroot="open" isn't support
                                        root.attachShadow({
                                            mode: fr.getAttribute('shadowroot')
                                        }).appendChild(fr.content);
                                        fr.remove();
                                    }
                                    fragment.append(api.ToolBar.getBaseCss(), api.ToolBar.el.getRootNode().querySelector('#module_combine_style').cloneNode(true),toolbar.getRootNode().querySelector('#module_form_fields_style').cloneNode(true));
                                    root.shadowRoot.prepend(fragment);
                                    topWindow.body.appendChild(root);
                                    prms=Themify.loadCss(Themify.builder_url + 'css/editor/modules/pallete', null,null, root.shadowRoot.lastElementChild);
                                }
                                else{
                                    prms=Promise.resolve();
                                }
                                root=topWindow.tfId('tb_pallete_root');
                                prms.then(()=>{
                                    root.classList.remove('tf_hide');
                                    pallete=root.shadowRoot.tfId('pallete');
                                    pallete.tfOn('input reset '+Themify.click,imageEffects,{passive:true});
                                });
                            }
                            if(imageObj && prms){
                                prms.then(()=>{
                                    imageObj.updateEffects();
                                });
                            }
                        });
                    }
                }
            },
            imageEffects=e=>{
                e.stopPropagation();
                if(e.type==='input'){
                        const target =e.target,
                            v=Math.trunc(target.value),
                            input=target.type==='range'?target.previousElementSibling:target.nextElementSibling,
                            eff=target.dataset.id;
                    
                    requestAnimationFrame(async ()=>{
                        input.value=v;
                        if(!filterImage){
                            filterImage=new ThemifyImageResize(activeEl,1,1);
                            target.tfOn('change',e=>{
                                filterImage.save(1, filterImage.ext);
                            },{passive:true,once:true});
                        }
                        await filterImage.prms;
                        filterImage.filter(eff,v);
                    });
                }
            },
            resizeImageEditor =  e=> {
                if (imageToolbar && !bodyCL.contains('tb_image_editor_resizing')) {
                    requestAnimationFrame( () =>{
                        if (imageToolbar && activeEl && !bodyCL.contains('tb_image_editor_resizing')) {
                            imageEditing(activeEl);
                        }
                    });
                }
            },
            updateCarousel =  ()=>  {
                if (swiper !== null) {
                    swiper.update();
                }
            },
            disableImageEditor =  ()=> {
                if (imageToolbar) {
                    imageToolbar
                        .tfOff('pointerdown', imageResizing, {passive: true})
                        .tfOff('dblclick', dblclick, {passive: true})
                        .remove();
                    imageToolbar=filterImage=null;
                }
            },
            disable = e => {
                if (isImageSelecting || (e && e.which !== 1)) {
                    return;
                }
                let target = e?e.target:null;
              
                if (!target || !toolbar || (e.which === 1 && target.id!=='tb_inline_editor_root' && !toolbar.contains(target) && (!pallete || !pallete.getRootNode().host.contains(target)))) {
                    restoreToolbar();
                    if (!target || !model ||!activeEl || !activeEl.contains(target)) {
                        if(timer){
                            cancelAnimationFrame(timer);
                        }
                        if (timer2) {
                            clearTimeout(timer2);
                        }
                        doc.tfOff('pointerdown', disable, {passive: true})
                        .tfOff('selectionchange', selectionStart, {passive: true})
                        .tfOff('pointerup', selectionEnd, {passive: true, once: true})
                        .tfOff('keydown',keypres);
                        topWindow.tfOff('pointerdown', disable, {passive: true})
                                .tfOff('keydown',keypres);
                        selectionEnd();
                        if (activeEl) {
                            activeEl.tfOff('input', onChange, {passive: true});
                            if (activeEl.tagName !== 'IMG') {
                                activeEl.setAttribute('contenteditable', 'false');
                            }

                            activeEl.classList.remove('tb_selected_img');
                            const form = activeEl.closest('form');
                            let _item = activeEl.closest('[draggable]');
                            while (_item !== null) {
                                _item.draggable=true;
                                _item = _item.parentNode.closest('[draggable]');
                            }
                            if (form) {
                                const formInputs = form.tfOff('submit', preventDefault).querySelectorAll('[data-required]');
                                for (let i = formInputs.length - 1; i > -1; --i) {
                                    formInputs[i].removeAttribute('data-required');
                                    formInputs[i].setAttribute('required', 'required');
                                }
                            }
                            let wow = activeEl;
                            while (wow && wow.classList && !wow.classList.contains('active_module')) {
                                if (wow.dataset.tmpAnimation) {
                                    wow.style.animationName = wow.dataset.tmpAnimation;
                                    wow.removeAttribute('data-tmp-animation');
                                }
                                wow = wow.parentNode;
                            }
                        }
                        if (swiper && swiper.params && swiper.params.autoplay && swiper.params.autoplay.enabled) {
                            swiper.el.dataset.stopped = false;
                            swiper.autoplay.start();
                            if (swiper.params.thumbs && swiper.params.thumbs.swiper.autoplay && swiper.params.thumbs.swiper.autoplay.enabled) {
                                swiper.params.thumbs.swiper.el.dataset.stopped = false;
                                swiper.params.thumbs.swiper.autoplay.start();
                            }
                        }
                        if (toolbar) {
                            toolbar.classList.add('tf_hide', 'tf_opacity');
                            toolbar.tfOff(Themify.click, toolbarActions)
                            .tfOff('pointerdown', toolbarMouseDown)
                            .classList.remove('top_viewport', 'show_link');
                        }
                        if (e) {
                            win.getSelection().removeAllRanges();
                        }
                        Themify.off('tfsmartresize', resizeImageEditor)
                                .off('tbresizeImageEditor', resizeImageEditor)
                                .off('tbDisableInline', disable);
                        disableImageEditor();
                        host.classList.remove('tb_editor_active', 'tb_editor_image_active', 'tb_editor_start_select');
                        if (bodyCL) {
                            bodyCL.remove('tb_editor_active', 'tb_editor_image_active', 'tb_editor_start_select');
                        }
                        topWindow.body.classList.remove('tb_editor_active', 'tb_editor_image_active', 'tb_editor_start_select');
                        if(pallete){
                            pallete.getRootNode().host.classList.add('tf_hide');
                            pallete.tfOff('input reset '+Themify.click,imageEffects,{passive:true});
                        }
                        if (activeEl) {
                            const activeModule = activeEl.closest('.active_module');
                            if (activeModule) {
                                activeModule.tfOff('dblclick', dblclick, {passive: true})
                                        .classList.remove('tb_editor_on', 'tb_editor_clicked');
                                if (isChanged) {
                                    saveData();
                                    for(let cm=api.LightBox.el.tfClass('CodeMirror'),i=cm.length-1;i>-1;--i){
                                        let codeMiror=cm[i].CodeMirror;
                                        if(codeMiror){
                                            let input=cm[i].previousElementSibling;
                                            if(input){
                                                codeMiror.setValue(input.value);
                                                setTimeout(()=>{
                                                    codeMiror.refresh();
                                                },10);
                                            }
                                        }
                                    }
                                    if (!activeEl.hasAttribute('data-no-update')) {
                                        api.Utils.runJs(activeModule, 'module');
                                    }
                                }
                            }
                        }
                        Themify.trigger('inlineEditorDisable',[model,activeEl]);
                        if(activeEl && api.undoManager.has('inline')){
                            api.undoManager.end('inline');
                        }
                        api.undoManager.clear('inline');
                        api.undoManager.enable();
                        stack=[];
                        stackIndex=-1;
                        toolbar = undoIsworking=firstVal=pallete=imageObj=bodyCL = timer2=timer=is_editable =modelClass=selection = width = height = timer = toolbarItems = dialog = linkHolder = model =  swiper = activeEl = null;

                        
                    } else if (e && activeEl && win.getSelection().isCollapsed === false) {
                        if (!bodyCL.contains('tb_editor_start_select')) {
                            host.classList.add('tb_editor_start_select');
                            bodyCL.add('tb_editor_start_select');
                            topWindow.body.classList.add('tb_editor_start_select');
                        }
                        this.tfOn('pointerup', selectionEnd, {passive: true, once: true});
                    }
                }

    };
    Themify.on('themify_builder_ready', init, true,api.is_builder_ready)
    .on('disableInline', disable);
    
    api.BaseInLineEdit=class{
        constructor() {
            api.BaseInLineEdit.items.set(this.constructor.name,this);
        }
    }
    api.BaseInLineEdit.items=new Map();
    
})(tb_app,Themify, jQuery, window, document, window.top.document,undefined);