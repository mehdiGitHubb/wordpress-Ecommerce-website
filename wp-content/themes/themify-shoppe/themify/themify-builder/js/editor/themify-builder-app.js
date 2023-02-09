var tb_app;
(($, Themify, win, topWindow, doc,und) => {

    'use strict';
    let jsModulePrms=null;

    tb_app = {
        breakpointsReverse: Object.keys(themifyBuilder.breakpoints).reverse(),
        isGSPage : doc.body.classList.contains('gs_post'),
        mode: topWindow !== win.self ? 'visual' : '',
        activeBreakPoint: 'desktop',
        inlineEditor:topWindow !== win.self,
        activeModel: null,
        isDocked: false,
        zoomMeta: false,
        isPreview: false,
        scrollTo: false,
        is_builder_ready:null,
        isSafari: /^((?!chrome|android).)*safari/i.test(navigator.userAgent),
        Utils: {},
        jsModuleLoaded(){
            if(jsModulePrms===null){
                const baseUrl=doc.currentScript.src.split('?')[0].replace('themify-builder-app','').replace('.min.js','').replace('.js',''),
                    allPromisses = [Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification)],
                    jsModules = ['undomanager', 'gradient','lightbox','constructor','drag', 'drop', 'panel', 'action-bar','toolbar', 'gs','ticks'],
                    moduleUrl = baseUrl + 'modules/';
                    if(!Themify.isTouch){
                        jsModules.unshift('right-click');
                    }
                if(!Themify.builder_url){
                    Themify.builder_url=baseUrl.replace('js/editor/','');
                }
                for (let i = jsModules.length - 1; i > -1; --i) {
                    allPromisses.push(Themify.loadJs(moduleUrl + jsModules[i]));
                }
                if(api.mode==='visual'){
                    allPromisses.push(Themify.loadJs('image-resize',!!win.ThemifyImageResize));
                }
                allPromisses[0].then(()=>{
                    TF_Notification.init().then(root=>{
                        if (api.mode === 'visual') {
                            topWindow.document.body.appendChild(root);
                        }
                    });
                });
                jsModulePrms= Promise.all(allPromisses).then(() => {
                    ThemifyConstructor.init();
                });
            }
            return jsModulePrms;
        }
    };
    const api = tb_app,
	isFullSection = !!themifyBuilder.is_fullSection;
    api.breakpointsReverse.push('desktop');
    Themify.upload_url=themifyBuilder.upload_url;
    api.jsModuleLoaded();
    const Clipboard={
        key: 'tb_clipboard',
        set(type, content) {
            const data = {};
            data[type] = content;
            localStorage.setItem(this.key, JSON.stringify(data));
        },
        get(type) {
            const savedContent = JSON.parse(localStorage.getItem(this.key));
            return savedContent!==null && savedContent[type] !== und ? savedContent[type] : false;
        }
    };
    
    api.template=id=>{ 
        const memorize=topWindow._.memoize(id=> {//we don't need to load 2 js files for this small js
                let compiled,
                        /*
                         * Underscore's default ERB-style templates are incompatible with PHP
                         * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
                         *
                         * @see trac ticket #22344.
                         */
                        options = {
                                evaluate:    /<#([\s\S]+?)#>/g,
                                interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                                escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
                                variable:    'data'
                        };

                return data=>{
                        compiled = compiled || topWindow._.template( doc.tfId( 'tmpl-' + id ).innerHTML,  options );
                        return compiled( data );
                };
        });
        return memorize(id);
    };
    
    api.LocalFetch=(data,type,params)=>{
        data.nonce=themifyBuilder.nonce;
        if(!data.bid){
            const b=api.Builder!==und?api.Builder.get():und;
            data.bid=b!==und && b.id!==und?b.id:themifyBuilder.post_ID;
            if(data.bid===und){
                data.bid='';
            }
        }
        Themify.trigger('tb_filter_fetch',data);
        return Themify.fetch(data,type,params);
    };
    
    api.FormTemplates =  {
        key : 'tb_form_templates_',
        data : null,
        prms : null,
        init() {
            if (this.prms === null) {
                this.prms = new Promise((resolve, reject) => {
                    if (this.data !== null) {
                        resolve();
                        return;
                    }
                    const result = resp => {
                            this.data = resp;
                            resolve();
                        },
                        data = this.get();
                    if (data !== false) { //cache form templates
                        result(data);
                    } else {
                        let forms = {},
                            max = Object.keys(themifyBuilder.modules).length + 3,
                            ajaxData={
                                action:'tb_load_form_templates'
                            };
                        let getForms = page => {
                            ++page;
                            ajaxData.page=page;
                            api.LocalFetch(ajaxData).then(resp => {
                                if (resp) {
                                    forms = Object.assign(forms, resp);
                                    if (max > Object.keys(forms).length) {
                                        getForms(page);
                                    } else {
                                        result(forms);
                                        this.set(forms);
                                        forms = getForms = max = ajaxData = page = null;
                                    }
                                }
                            }).catch(reject);
                        };
                        getForms(0);
                    }
                });
            }
            return this.prms;
        },
        getItem(key) {
            return key === und ? this.data : this.data[key];
        },
        set(value) {
            try {
                Themify.requestIdleCallback(()=>{
                    localStorage.setItem(this.key, JSON.stringify({val: value, h: this.getHash()}));
                },-1,3000);
            } 
            catch (e) {
            }
        },
        getHash(){
            return Themify.hash(Themify.v + Object.keys(themifyBuilder.modules)+(themifyBuilder.cache_data || ''));
        },
        get() {
            if (!themifyBuilder.debug) {
                try {
                    let record = localStorage.getItem(this.key);
                    if (record) {
                        record = JSON.parse(record);
                        if (record.h === this.getHash()) {
                            return record.val;
                        }
                    }
                } 
                catch (e) {
                }
            }
            return false;
        }
    };

    api.FormTemplates.init();

    api.getColClass=()=>{//backward compatibility
        
        return { //deprecated,don't use it need for backward compatibility
            '1': ['col-full'],
            '2': ['col4-2', 'col4-2'],
            '3': ['col3-1', 'col3-1', 'col3-1'],
            '4': ['col4-1', 'col4-1', 'col4-1', 'col4-1'],
            '5': ['col5-1', 'col5-1', 'col5-1', 'col5-1', 'col5-1'],
            '6': ['col6-1', 'col6-1', 'col6-1', 'col6-1', 'col6-1', 'col6-1'],
            '1_2': ['col3-1', 'col3-2'],
            '2_1': ['col3-2', 'col3-1'],
            '1_3': ['col4-1', 'col4-3'],
            '3_1': ['col4-3', 'col4-1'],
            '1_1_2': ['col4-1', 'col4-1', 'col4-2'],
            '1_2_1': ['col4-1', 'col4-2', 'col4-1'],
            '2_1_1': ['col4-2', 'col4-1', 'col4-1']
        };
    };
    api.getColClassValues=()=>{//backward compatibility
        return Array.from(new Set([].concat.apply([], Object.values(api.getColClass()))));;
    };
    
    
    api.Spinner=  {
        el : topWindow.document.tfClass('tb_loader')[0],
        showLoader(mode = 'show') {
            return new Promise(resolve => {
                const l = this.el,
                    cl = l.classList;
                if (mode === 'spinhide') {
                    cl.add('tf_hide');
                    cl.remove('tf_opacity', 'tb_done', 'tb_error','tb_show');
                    resolve();
                } else if (!cl.contains('tb_' + mode)) {
                    cl.remove('tf_hide','tf_opacity','tb_done', 'tb_error','tb_show');
                    if (mode !== 'show') {
                        if (mode !== 'error') {
                            mode = 'done';
                        }
                        cl.add('tb_' + mode);
                        requestAnimationFrame(()=>{
                            if(cl.contains('tb_' + mode)){
                                const end=function(){
                                    cl.remove('tb_' + mode, 'tf_opacity');
                                    cl.add('tf_hide');
                                    this.tfOff('transitioncancel transitionend',end, {passive: true,once: true});
                                    resolve();
                                };
                                l.tfOn('transitionend transitioncancel', end, {
                                    passive: true,
                                    once: true
                                });
                                cl.add('tf_opacity');
                            }
                        });
                    }
                    else {
                        cl.add('tb_show');
                        resolve();
                    }
                } else {
                    resolve();
                }
            });
        }
    };
    
    api.Helper={
        correctBuilderData(rows){
            if(!rows || !Array.isArray(rows)){
                const tmp=[];
                if(rows){
                    for(let i in rows){
                        tmp.push(rows[i]);
                    }
                }
                rows=tmp;
            }
            for(let i=rows.length-1;i>-1;--i){
                if(rows[i]){
                    let cols=rows[i].cols;
                    if(cols){
                        if(!Array.isArray(cols)){
                            let tmp=[];
                            for(let j in cols){
                                tmp.push(cols[j]);
                            }
                            cols=rows[i].cols=tmp;
                        }
                        for(let j=cols.length-1;j>-1;--j){
                            if(cols[j]){
                                if(cols[j].modules){
                                    cols[j].modules=this.correctBuilderData(cols[j].modules);
                                }
                            }
                            else{
                                cols.splice(j, 1);
                            }
                        }
                    }
                }
                else{
                    rows.splice(i, 1);
                }
            }
            return rows;
        },
        cloneDom(el,remove) {
            if (el === null) {
                return el;
            }
            if (el[0] !== und) {
                el = el[0];
            }
            if(el.nodeType===Node.TEXT_NODE){
                return el.cloneNode(true);
            }
            const node = remove===true?el:el.cloneNode(true);
            if (api.mode === 'visual') {
                //after cloning dom the video is playing in bg
                const v = node.tfTag('video');
                if (v.length > 0) {
                    for (let i = v.length - 1; i > -1; --i) {
                        v[i].pause();
                    }
                    for (let items = node.tfClass('big-video-wrap'), i = items.length - 1; i > -1; --i) {
                        if (items[i]) {
                            items[i].remove();
                        }
                    }
                }
                for (let items = node.tfClass('tb_dragger'), i = items.length - 1; i > -1; --i) {
                    items[i].remove();
                }
                for (let items = Themify.selectWithParent('[contenteditable]', node), i = items.length - 1; i > -1; --i) {
                    items[i].setAttribute('contenteditable', 'false');
                    let p = items[i].closest('.tb_editor_on');
                    if (p) {
                        p.classList.remove('tb_editor_on', 'tb_editor_clicked');
                    }
                }
                for (let items = Themify.selectWithParent('[draggable]', node), i = items.length - 1; i > -1; --i) {
                    items[i].setAttribute('draggable', 'true');
                }
            }
            for (let items = node.tfClass('tb_action_wrap'), i = items.length - 1; i > -1; --i) {
                while (items[i].firstChild !== null) {
                    items[i].lastChild.remove();
                }
                items[i].removeAttribute('id');
                items[i].removeAttribute('style');
                items[i].classList.remove('tb_clicked');
            }
            const uiItems=node.querySelectorAll('.tb_clicked,.tb_editor_on,.tb_element_clicked,.tb_selected_img,.tb_editor_clicked,.tb_hide_drag_col_right,.tb_hide_drag_left,.tb_hide_drag_right,.tb_drag_one_column,.tb_drag_side_column,.tb_draggable_item,.tb_column_drag_inner,.tb_active_action_bar,.compact-mode,.tf_dragger_negative');
            for (let i = uiItems.length - 1; i > -1; --i) {
                uiItems[i].classList.remove('tb_clicked', 'tb_element_clicked','tb_editor_on','tb_selected_img','tb_editor_clicked', 'tb_hide_drag_col_right','tb_hide_drag_left','tb_hide_drag_right', 'tb_drag_one_column', 'tb_drag_side_column', 'tb_draggable_item', 'tb_column_drag_inner','tb_active_action_bar','compact-mode','tf_dragger_negative');
            }
            for (let items = node.querySelectorAll('[data-drag-w],[data-pos]'), i = items.length - 1; i > -1; --i) {
                items[i].removeAttribute('data-drag-w');
                items[i].removeAttribute('data-pos');
            }
            node.classList.remove('tb_clicked','tb_selected_img', 'tb_element_clicked','tb_editor_on','tb_editor_clicked', 'tb_hide_drag_col_right','tb_hide_drag_left','tb_hide_drag_right', 'tb_drag_one_column', 'tb_drag_side_column', 'tb_draggable_item', 'tb_column_drag_inner','tb_active_action_bar','compact-mode','tf_dragger_negative');
            node.removeAttribute('data-drag-w');
            node.removeAttribute('data-pos');
            return node;
        },
        cloneObject(obj){
            if(Array.isArray(obj)){
                let l = obj.length;
                const arr = new Array(l);
                while (l--) {
                    if(typeof obj[l]=== 'object'){
                        arr[l] = ThemifyStyles.extend(true,{},obj[l]);
                    }
                    else if(Array.isArray(obj[l])){
                        arr[l] = this.cloneObject(obj[l]);
                    }
                    else{
                        arr[l] = obj[l];
                    }
                }
                return arr;
            }
            else{
                return ThemifyStyles.extend(true,{},obj);
            }
        },
        compareObject(oldSettings,newSetting){
            if(oldSettings && newSetting){
                const size1=oldSettings.hasOwnProperty('length')?oldSettings.length:Object.keys(oldSettings).length,
                    size2=newSetting.hasOwnProperty('length')?newSetting.length:Object.keys(newSetting).length;
                    if(size1===size2){
                        if(size1>0){
                            for(let i in oldSettings){
                                if(newSetting[i]===und){
                                    return true;
                                }
                                if(oldSettings[i]!==null && typeof oldSettings[i] === 'object'){
                                    if(typeof newSetting[i]!=='object' || this.compareObject(oldSettings[i],newSetting[i])){
                                        return true;
                                    }
                                }
                                else if(newSetting[i]!=oldSettings[i] || (typeof newSetting[i]==='object' && typeof oldSettings[i]!=='object')){
                                    return true;
                                }
                            }
                        }
                    }
                    else{
                        return true;
                    }
            }
            else{
                return true;
            }
            return false;
        },
        isImageUrl(link) {
            if (!link) {
                return false;
            }
            const parts = link.split('?')[0].split('.');
            return ['jpg', 'jpeg', 'tiff', 'png', 'gif', 'bmp', 'svg','webp','apng'].indexOf(parts[parts.length - 1]) !== -1;
        },
        loadJsZip(){
          return Themify.loadJs(Themify.url+'js/admin/jszip.min',!!win.JSZip,'3.10.1');
        },
        toRGBA(v){
            return ThemifyStyles.toRGBA(v);
        },
        getIcon(icon, cl) {
            if (typeof themifyBuilder.fontello_prefix !== 'undefined') {
                const fontello_regex = new RegExp(themifyBuilder.fontello_use_suffix ? themifyBuilder.fontello_prefix + '$' : '^' + themifyBuilder.fontello_prefix);
                if (fontello_regex.test(icon)) {
                    const i = doc.createElement('i');
                    i.setAttribute('class', icon);
                    return i;
                }
            }
            icon = 'tf-' + icon.trim().replace(' ', '-');
            const ns = 'http://www.w3.org/2000/svg',
                use = doc.createElementNS(ns, 'use'),
                svg = doc.createElementNS(ns, 'svg');
            let classes = 'tf_fa ' + icon;
            if (cl) {
                classes += ' ' + cl;
            }
            svg.setAttribute('class', classes);
            use.setAttributeNS(null, 'href', '#' + icon);
            svg.appendChild(use);
            return svg;
        },
        getColor(el) {
            let v = el.value;
            if (v !== '') {
                if (el.getAttribute('data-tfminicolors-initialized') !== null) {
                    v = $(el).tfminicolors('rgbaString');
                } else {
                    const opacity = el.dataset.opacity;
                    if (opacity !== '' && opacity !== null) {
                        v = ThemifyStyles.toRGBA(v + '_' + opacity);
                    }
                }
            }
            return v;
        },
        getBreakpointName(bp){
            return api.ToolBar.el.querySelector('.breakpoint-'+bp+' span').textContent;
        },
        generateUniqueID() {
            return (Math.random().toString(36).substr(2, 4) + (new Date().getUTCMilliseconds()).toString()).substr(0, 7);
        },
        clearElementId(data, _new) {
            for (let i in data) {
                if (_new === true) {
                    data[i].element_id = this.generateUniqueID();
                } else {
                    delete data[i].element_id;
                }
                let opt = data[i].styling || data[i].mod_settings;
                if (opt !== und) {
                    if (opt.custom_css_id !== und && opt.custom_css_idcustom_css_id !== '') {
                        let j = 1;
                        while (true) {
                            let id = opt.custom_css_id;
                                if(j!==1){
                                    id+='-'+j.toString();
                                }
                                let el = doc.tfId(id);
                            if (el === null || el.closest('.module_row') === null) {
                                opt.custom_css_id = id;
                                break;
                            }
                            ++j;
                        }
                    }

                    if (opt.builder_content !== und) {
                        let bulder = typeof opt.builder_content === 'string' ? JSON.parse(opt.builder_content) : opt.builder_content;
                        this.clearElementId(bulder, true);
                        opt.builder_content = bulder;
                    }
                }
                if (data[i].cols !== und) {
                    this.clearElementId(data[i].cols, _new);
                } else if (data[i].modules !== und) {
                    this.clearElementId(data[i].modules, _new);
                }
            }
            return data;
        },
        clear(items, is_array) {
            if (is_array === und) {
                is_array = Array.isArray(items);
            }
            const res = is_array === true ? [] : {},
                dcName = win.tbpDynamic || false;
            for (let i in items) {
                if (i === 'null' || items[i]===null || items[i]===und || items[i]==='null' ||items[i]==='undefined' || !items.hasOwnProperty(i)) {
                    continue;
                }
                if (Array.isArray(items[i])) {
                    let data = this.clear(items[i],true);
                    if (data.length > 0) {
                        if (is_array === true) {
                            res.push(data);
                        } else {
                            res[i] = data;
                        }
                    }
                } 
                else if (i === dcName) {
                    if (items[i] === '{}' || items[i] === '') {
                        delete items[i];
                        delete res[i];
                        continue;
                    } else {
                        let tmp = items[i];
                        if (typeof tmp === 'string') {
                            tmp = JSON.parse(tmp);
                        }
                        for (let k in tmp) {
                            if (tmp[k].repeatable !== und) {
                                continue;
                            }
                            if (tmp[k].item === und) {
                                delete tmp[k];
                            } 
                            else if (items[k] !== und) {
                                    delete items[k];
                                    delete res[k];
                            }
                        }
                        items[i] = res[i] = tmp;
                    }
                }
                else if (typeof items[i] === 'object') {
                    let data;
                    if (i === 'breakpoint_mobile' || i === 'breakpoint_tablet' || i === 'breakpoint_tablet_landscape') {
                        data = items[i];
                        for (let j in data) {
                            if (data[j] === und || data[j] === null || data[j] === '') {
                                delete data[j];
                            } else if (j.indexOf('_unit', 2) !== -1) {
                                let id = j.replace('_unit', '');
                                if (data[id] === und || data[id] === '') {
                                    delete data[j];
                                    if (data[id] === '') {
                                        delete data[id];
                                    }
                                }
                            }
                        }

                    } 
                    else {
                        if(!items[i].element_id && ((items[i].cols && Array.isArray(items[i].cols)) || items[i].mod_name || (items[i].modules && Array.isArray(items[i].modules)))){
                            items[i].element_id=api.Helper.generateUniqueID();
                        }
                        data = this.clear(items[i]);
                    }
                    if (data && Object.keys(data).length > 0) {
                        if (is_array === true) {
                            res.push(data);
                        } else {
                            res[i] = data;
                        }
                    }
                    else if(i==='sizes'){
                        res[i] = {};
                    }
                }
                else if (items[i] !== null && items[i] !== und && items[i] !== '' && items[i]!=='tb_default_color' && items[i] !== 'def' && i !== '' && items[i] !== 'pixels' && items[i] !== 'default' && items[i] !== '|') {

                    if ((i === 'hide_anchor' && !items[i]) || (items[i] === 'show' && i.indexOf('visibility_') === 0) || (i === 'unstick_when_condition' && items[i] === 'hits') || (i === 'unstick_when_pos' && items[i] === 'this') || (i === 'unstick_when_element' && items[i] === 'builder_end') || ((i === 'stick_at_pos_val_unit' || i === 'unstick_when_pos_val_unit') && items[i] === 'px')) {
                        continue;
                    } else if (i === 'custom_parallax_scroll_speed' && !items[i]) {
                        delete res.custom_parallax_scroll_reverse;
                        delete res.custom_parallax_scroll_fade;
                        delete res[i];
                        delete items.custom_parallax_scroll_reverse;
                        delete items.custom_parallax_scroll_fade;
                        delete items[i];
                        continue;
                    } else if ((items[i] !== 'unstick_when_check' && (i === 'unstick_when_check' || i === 'unstick_when_check_tl' || i === 'unstick_when_check_t' || i === 'unstick_when_check_m')) || (items[i] === 'builder_end' && (i === 'unstick_when_element' || i === 'unstick_when_element_tl' || i === 'unstick_when_element_t' || i === 'unstick_when_element_m')) || (i === 'stick_at_check' && items[i] !== 'stick_at_check') || ((items[i] == '-1' || !items[i]) && (i === 'stick_at_check_tl' || i === 'stick_at_check_t' || i === 'stick_at_check_m'))) {
                        let postfix = '';
                        if (i === 'unstick_when_element_tl' || i === 'stick_at_check_tl' || i === 'unstick_when_check_tl') {
                            postfix = '_ti';
                        } else if (i === 'unstick_when_element_t' || i === 'stick_at_check_t' || i === 'unstick_when_check_t') {
                            postfix = '_t';
                        } else if (i === 'unstick_when_element_m' || i === 'stick_at_check_m' || i === 'unstick_when_check_m') {
                            postfix = '_m';
                        }
                        delete res['unstick_when_el_row_id' + postfix];
                        delete res['unstick_when_el_mod_id' + postfix];
                        delete res['unstick_when_condition' + postfix];
                        delete items['unstick_when_el_row_id' + postfix];
                        delete items['unstick_when_el_mod_id' + postfix];
                        delete items['unstick_when_condition' + postfix];

                        delete res['unstick_when_pos' + postfix];
                        delete res['unstick_when_pos_val' + postfix];
                        delete res['unstick_when_element' + postfix];
                        delete res['unstick_when_pos_val_unit' + postfix];
                        delete items['unstick_when_pos' + postfix];
                        delete items['unstick_when_pos_val' + postfix];
                        delete items['unstick_when_pos_val_unit' + postfix];
                        delete items['unstick_when_element' + postfix];

                        if (i === 'stick_at_check' || i === 'stick_at_check_tl' || i === 'stick_at_check_t' || i === 'stick_at_check_m') {
                            if (i === 'stick_at_check' || items[i] == '-1') {
                                delete items[i];
                                delete res[i];
                            }
                            delete items['stick_at_position' + postfix];
                            delete res['stick_at_position' + postfix];
                        }
                        continue;
                    } 
                    else if (i==='background_image-css' || i === und || i === null || i === '' || i === false || (i === 'stick_at_position' && items[i] === 'top') || (i === 'resp_no_bg' && items[i] === false) || (i === api.GS.key && items[i].trim() === '') || i === 'background_gradient-css' || i === 'cover_gradient-css' || i === 'cover_gradient_hover-css' || i === 'background_image-type_image' || i === 'custom_parallax_scroll_reverse_reverse' || items[i] === '|single' || items[i] === '|multiple' || ((i === 'custom_parallax_scroll_reverse' || i === 'custom_parallax_scroll_fade' || i === 'visibility_all' || i === 'sticky_visibility' || i === 'background_zoom' || i === 'b_sh_inset' || i === 'background_image-circle-radial') && !items[i])) {
                        delete items[i];
                        delete res[i];
                        continue;
                    } 
                    else if (i === 'builder_content') {
                        if (typeof items[i] === 'string') {
                            items[i] = JSON.parse(items[i]);
                        }
                        items[i] = this.clear(items[i], true);
                    } 
                    else {
                        let opt = [];
                        if (i.indexOf('checkbox_') === 0 && i.indexOf('_apply_all', 6) !== -1) {
                            if (!items[i]) {
                                opt.push(i);
                            } else {
                                res[i] = items[i];
                            }
                            let id = i.replace('_apply_all', '').replace('checkbox_', ''),
                                side = ['top', 'left', 'right', 'bottom'];
                            for (let j = 3; j > -1; --j) {
                                let tmpId = id + '_' + side[j] + '_unit';
                                if (items[tmpId] === 'px') {
                                    opt.push(tmpId);
                                } else if (items[tmpId] !== und && items[tmpId] !== null && items[tmpId] !== '') {
                                    res[tmpId] = items[tmpId];
                                }
                            }
                        } else if (i.indexOf('gradient', 3) !== -1) {
                            if (items[i] == '180' || items[i] === 'linear' || items[i] === $.ThemifyGradient.default || (items[i] === false && i.indexOf('-circle-radial', 3) !== -1)) {
                                opt.push(i);
                            }
                        } else if ((items[i] === 'px' && i.indexOf('_unit', 2) !== -1 && i.indexOf('frame_') === -1) || (items[i] === '%' && i.indexOf('_unit', 2) !== -1 && i.indexOf('frame_') !== -1) || (i === 'background_zoom' && items[i] === '') || (items[i] === 'none' && i.indexOf('frame_layout') !== -1) || items[i] === 'solid' || (items[i] === false && (i.indexOf('_user_role', 3) !== -1 || i.indexOf('_appearance', 3) !== -1)) || ((!items[i] || items[i] === 'false') && (i === 'margin-top_opp_top' || i === 'm_t_h_opp_top' || i.indexOf('padding_opp_') === 0 || i.indexOf('margin_opp_') === 0))) {
                            opt.push(i);
                        }
                        if (opt.length > 0) {
                            for (let j = opt.length - 1; j > -1; --j) {
                                delete res[opt[j]];
                                delete items[opt[j]];
                            }
                            opt.length = 0;
                            opt = [];
                            continue;
                        }
                    }
                    if (is_array === true) {
                        res.push(items[i]);
                    } else {
                        res[i] = items[i];
                    }
                }
            }
            return res;
        },
        async codeMirror(el,mode,conf){
            try{
				if(!conf){
					conf={};
				}
                conf.isDarkMode=api.isDarked;
                await topWindow.Themify.loadJs(Themify.url+'js/admin/modules/codemirror/codemirror',!!topWindow.ThemifyCodeMiror);
                const obj=new topWindow.ThemifyCodeMiror(el,mode,conf);
                await obj.run();
                return obj;
            }
            catch(e){
               return null;
            }
        }
    };


    api.Registry={
        items : new Map(),
        events : new Map(),
        add(item) {
            this.items.set(item.id, item);
            return this;
        },
        get(id) {
            const el=this.items.get(id);
            return el!==und?el:null;
        },
        remove(id,db) {
            const model = this.get(id);
            if (model) {
                model.el.remove();
                if(db===true){
                    this.items.delete(id);
                    this.events.delete(id);
                }
              //  this.items.delete(id); need to keep for undomanager. ToDo
            }
            return this;
        },
        destroy() {
            for(let [id,m] of this.items){
                m.el.remove();
            }
            this.items.clear();
            this.events.clear();
            return this;
        },
        on(id, ev, f) {
            if(f!==und){
                const events=this.events.get(id) || {};
                if(events[ev]===und){
                    events[ev]=[];
                }
                events[ev].push(f);
                this.events.set(id, events);
            }
            return this;
        },
        off(id, ev,f) {
            const events=this.events.get(id);
            if(events!==und){
                if(!ev){
                    this.events.delete(id);
                }
                else if(events[ev]!==und){
                    if(f){
                        for(let i=events[ev].length-1;i>-1;--i){
                            if(events[ev][i]===f){
                                events[ev].splice(i, 1);
                            }
                        }
                        if(events[ev].length===0){
                            delete events[ev];
                        }
                    }
                    else{
                        delete events[ev];
                    }
                    this.events.set(id,events);
                }
            }
            return this;
        },
        trigger(id, ev, ...args) {
                const events = this.events.get(id),
                    proms=[];
                if (events !== und && events[ev]!==und) {
                    let _this = typeof id === 'string' ? this.get(id) : id;

                    if(!_this){
                        _this=id;
                    }
                    for (let i =  events[ev].length-1; i>-1;--i) {
                        let pr=events[ev][i].apply(_this, args);
                        if(pr!==und && pr instanceof Promise){
                            proms.push(pr);
                        }
                    }
                }
                return Promise.all(proms).catch(e=>{});
        }
    };


    api.Base=class  {
        constructor(fields) {
            this.fields = Object.assign({}, this.defaults(), fields);
        }
        initialize(){
            let id = this.fields.element_id;
            if (!id || api.Registry.items.has(id)) {
                this.fields.element_id=id = api.Helper.generateUniqueID();
            }
            this.id = id;
            this.el = doc.createElement('div');
            if (this.type !== 'module') {
                this.el.appendChild(doc.tfId('tmpl-builder_' + this.type + '_item').content.cloneNode(true));
            }
            this.setHtmlAttributes();
            api.Registry.add(this);
            
            api.Registry
                .on(this.id, 'edit', this.edit)
                .on(this.id, 'save', this.save)
                .on(this.id, 'delete', this.delete)
                .on(this.id, 'copy', this.copy)
                .on(this.id, 'paste', this.paste)
                .on(this.id, 'duplicate', this.duplicate)
                .on(this.id, 'import', this.import)
                .on(this.id, 'export', this.export);
            if ('visual' in this) {
                this.visual();
            }
        }
        setData(data,dymmy) {
            return new Promise(async resolve=>{
                api.Helper.clearElementId([data]);
                const className=this.type.charAt(0).toUpperCase() + this.type.slice(1),
                    model=new api[className](data,this.isSubCol),
                    oldId=dymmy.dataset.cid;//need for undo
                if(oldId){
                    model.el.dataset.oldCid=oldId;//will be removed in undomanager.js
                }
                dymmy.replaceWith(model.el);
                if (api.mode === 'visual') {
                    const el=await model.trigger('recreate');
                    resolve(el);
                } else {
                    api.Utils.runJs(model.el);
                    resolve(model.el);
                }
            });
        }
        get(id) {
            const mainId=this.type === 'module' ? 'mod_settings' : 'styling';
            if (id === 'element_id') {
                return this.id;
            } 
            if (id === 'mod_name') {
                return this.type === 'module' ? this.fields[id] : this.type;
            } 
            if (id === 'sizes' || id === 'cols' || id === 'modules' || id === 'mod_settings' || id === 'styling') {
                return id === 'sizes' || id === 'cols'|| id === 'modules'?this.fields[id]:this.fields[mainId];
            } 
            const className=this.type.charAt(0).toUpperCase() + this.type.slice(1);
            if(this.defaults()[id]!==und){
                return this.fields[id];
            }
            if(this.type==='row' || this.type==='subrow' || (this.type==='column' && (id==='grid_class' || id==='grid_width'))){//backward
                if(id==='grid_class' || id==='grid_width' || id === 'gutter' || id === 'column_alignment' || id === 'column_h' || id === 'desktop_dir' || id === 'tablet_landscape_dir' || id==='tablet_dir' || id==='mobile_dir'   || id==='col_tablet_landscape' || id==='col_tablet' || id==='col_mobile'){
                    return this.fields[id];
                }
            }
            return this.fields[mainId][id];
        }
        set(id, value) {
            const mainId=this.type === 'module' ? 'mod_settings' : 'styling',
                className=this.type.charAt(0).toUpperCase() + this.type.slice(1);
            if(id === 'cols' || id === 'modules' || id==='sizes' || id === 'mod_settings' || id === 'styling' || id === 'element_id'){
                if(id!=='sizes' && id !== 'cols' && id !== 'modules'){
                    if(id === 'element_id'){
                        this.id = value;
                    }
                    else{
                        id=mainId;
                    }
                }
                this.fields[id] = value;
            }
            else if(this.defaults()[id]!==und){
                this.fields[id] = value;
            }
            else{
                this.fields[mainId][id] = value;
            }
            return this;
        }
        unset(id) {
            if (id === 'mod_settings' || id === 'styling') {
                id = this.type === 'module' ? 'mod_settings' : 'styling';
            }
            delete this.fields[id];
            return this;
        }
        destroy(db=false){
            api.Registry.remove(this.id,db);
        }
        setHtmlAttributes() {
            const attr = this.attributes();
            attr['data-cid'] = this.id;
            attr.draggable = true;
            attr.class += ' tb_element_cid_' + this.id;
            for (let i in attr) {
                this.el.setAttribute(i, attr[i]);
            }
        }
        trigger(ev,...args) {
            return api.Registry.trigger(this.id, ev,...args);
        }
        getData() {
            let data = {}; 
            switch (this.type) {
                case 'row':
                case 'subrow':
                    data = api.Utils._getRowSettings(this.el, this.type);
                    break;
                case 'module':
                    data = api.Helper.cloneObject(this.fields);
                    break;
                case 'column':
                case 'sub-column':
                   
                    const selectedRow = this.el.closest((this.isSubCol===true? '.active_subrow': '.module_row' )),
                        rowData = api.Utils._getRowSettings(selectedRow, (this.isSubCol===true ? 'subrow' : 'row')),
                        index=Array.from(this.el.parentNode.children).indexOf(this.el);
                    data = rowData.cols[index];
                    break;
            }
            return api.Helper.clear(data);
        }
        fixSafariSrcSet(){
            if (api.isSafari === true && api.mode==='visual') {
                const img = this.el.querySelectorAll('img[srcset]');
                for(let i=img.length-1;i>-1;--i){// Fix Srcset in safari browser
                     img[i].outerHTML = img[i].outerHTML;
                }
            }  
        }
        visibilityLabel() {
            const cid = this.id;
            let styling = api.activeModel !== null && this.id === api.activeModel.id && ThemifyConstructor.clicked === 'visibility' ? api.Forms.serialize('tb_options_visibility') : und;
            if (styling === und) {
                styling = this.get('mod_settings');
            }
            if (styling) {
                const label = this.el.tfClass('tb_visibility_hint')[0],
                    visiblityVars = {
                        visibility_desktop: themifyBuilder.i18n.de,
                        visibility_mobile: themifyBuilder.i18n.mo,
                        visibility_tablet: themifyBuilder.i18n.ta,
                        visibility_tablet_landscape: themifyBuilder.i18n.ta_l,
                        sticky_visibility: themifyBuilder.i18n.s_v
                    };
                if (label !== und) {
                    let txt = '';
                    if ('hide_all' === styling.visibility_all) {
                        txt = themifyBuilder.i18n.h_a;
                    } 
                    else {
                        let prefix;
                        for (let i in visiblityVars) {
                            prefix = '' === txt ? '' : ', ';
                            txt += 'hide' === styling[i] ? prefix + visiblityVars[i] : '';
                        }
                    }
                    if (txt !== '') {
                        if(label.tfTag('svg')[0]===und){
                            label.appendChild(api.Helper.getIcon('ti-eye'));
                        }
                        let t = label.tfTag('span')[0];
                        if (t === und) {
                            t = doc.createElement('span');
                            label.appendChild(t);
                        }
                        t.textContent = txt;
                        label.classList.add('tb_has_visiblity');
                    } else {
                        label.classList.remove('tb_has_visiblity');
                    }
                }
            }
        }
        setBreadCrumbs(el) {
            if(api.isGSPage!==true){
                el=el.tfClass('tb_action_breadcrumb')[0];
                if(el!==und){
                    while (el.firstChild !== null) {
                        el.lastChild.remove();
                    }
                    if(this.el.isConnected && this.type!=='row'){
                        if(api.LightBox.el.contains(el)){
                            el.tfOn(Themify.click,e=>{
                                const id=e.target.dataset.id;
                                if(id){
                                    e.preventDefault();
                                    e.stopPropagation();
                                    api.Registry.trigger(id,'edit');
                                }
                            });
                        }
                        el.appendChild(this.getBreadCrumbs());
                    }
                }
            }
        }
        getBreadCrumbs() {
            let parent=this.el;
            const path=[this.id],
                builder=api.Builder.get().el,//if in layout part edit
                f = doc.createDocumentFragment();
            if(api.isGSPage!==true){
                while(true){
                    parent = parent.parentNode.closest('[data-cid]');
                    if(!parent || !builder.contains(parent)){
                        break;
                    }
                    path.push(parent.dataset.cid);
                }
                for(let i=path.length-1;i>-1;--i){
                    let item = doc.createElement('span'),
                            model = api.Registry.get(path[i]),
                            type = model.get('mod_name');
                        item.textContent = model.isSubCol===true?'Sub-Column': type;
                        item.className = 'tb_bread tb_bread_' + type+' tf_inline_b tf_box tf_rel';
                        if (this.id === path[i]) {
                            item.className += ' tb_active_bc';
                        }
                        item.dataset.id=path[i];
                        f.appendChild(item);
                }
            }
            return f;
        }
        async duplicate(isMultiple) {
            if(api.activeModel && this.el.contains(api.activeModel.el)){
                await api.LightBox.save();
            }
            if(isMultiple!==true){
                api.undoManager.start('duplicate',this.id);
            }
            const data=this.getData(),
                dummy=doc.createElement('div');

            this.el.after(dummy);
            await this.setData(data,dummy);
            api.pageBreakModule.countModules();
            if(isMultiple!==true){
                api.undoManager.end('duplicate');
            }
        }
        async delete(isMultiple) {
            if (api.activeModel && this.el.contains(api.activeModel.el)) {
                await api.LightBox.save();
            }
            if(isMultiple!==true){
                api.undoManager.start('delete',this.id);
            }
            if (this.type !== 'column') {
                this.destroy();
                if (this.type !== 'row') {
                    const r = this.el.closest('.active_subrow');
                    if (r &&  !r.tfClass('active_module')[0]) {
                        r.classList.add('tb_row_empty');
                    }
                }
            }
            else {
                await api.Drop.column(this.el);
                this.destroy();
            }
            api.pageBreakModule.countModules();
            if(isMultiple!==true){
               api.undoManager.end('delete');
            }
        }
        async copy() {
            if (api.activeModel && this.el.contains(api.activeModel.el)) {
                await api.LightBox.save();
            }
            const data = this.getData();
            // Attach used GS to data
            if (Object.keys(api.GS.styles).length) {
                const usedGS = api.GS.findUsedItems(data);
                if (usedGS !== false && usedGS.length) {
                    data.attached_gs = usedGS;
                }
            }
            api.Helper.clearElementId([data]);
            Clipboard.set(this.type, data);
            api.ActionBar.clear();
        }
        paste(is_style,isMultiple) {
            return new Promise(async(resolve,reject)=>{
                await api.LightBox.save();
                let component = this.get('mod_name'),
                    data = Clipboard.get(this.type);
                if (data === false || (is_style && this.type==='module' && component!==data.mod_name)) {
                    TF_Notification.showHide('error',themifyBuilder.i18n.text_alert_wrong_paste);
                    reject();
                    return;
                }
                if(this.type==='column' && data.modules && this.el.closest('.module_subrow')!==null){
                   for(let i=data.modules.length-1;i>-1;--i){
                       if(data.modules[i].cols!==und){
                            TF_Notification.showHide('error',themifyBuilder.i18n.text_alert_sub_in_sub);
                            reject();
                            return false;
                       }
                   } 
                }
                if (is_style === true) {
                    const stOptions = ThemifyStyles.getStyleOptions(component),
                        k =  this.type === 'module' ? 'mod_settings' : 'styling',
                        res = this.getData(),
                        checkIsStyle = i=> {
                            if (i.indexOf('breakpoint_') !== -1 || i.indexOf('_apply_all') !== -1) {
                                return true;
                            }
                            let key = i.indexOf('_color') !== -1 ? 'color' : (i.indexOf('_style') !== -1 ? 'style' : false);
                            if (key !== false) {
                                key = i.replace('_' + key, '_width');
                                if (stOptions[key] !== und && stOptions[key].type === 'border') {
                                    return true;
                                }
                            } else if (i.indexOf('_unit') !== -1) { //unit
                                key = i.replace(/_unit$/ig, '', '');
                                if (stOptions[key] !== und) {
                                    return true;
                                }
                            } else if (i.indexOf('_w') !== -1) { //weight
                                key = i.replace(/_w$/ig, '', '');
                                if (stOptions[key] !== und && stOptions[key].type === 'font_select') {
                                    return true;
                                }
                            } else if (stOptions[i] !== und && stOptions[i].type === 'radio') {
                                return true;
                            }
                            return false;
                        };
                    if (res[k] === und) {
                        res[k] = {};
                    }
                    for (let i in data[k]) {
                        if (stOptions[i] === und && !checkIsStyle(i)) {
                            delete data[k][i];
                        } else {
                            res[k][i] = data[k][i];
                            if (stOptions[i] !== und) {
                                if (stOptions[i].isFontColor === true && data[k][stOptions[i].g + '-gradient'] !== und) {
                                    res[k][stOptions[i].g + '-gradient'] = data[k][stOptions[i].g + '-gradient'];
                                } else {
                                    if (stOptions[i].posId !== und && data[k][stOptions[i].posId] !== und) {
                                        res[k][stOptions[i].posId] = data[k][stOptions[i].posId];
                                    }
                                    if (stOptions[i].repeatId !== und && data[k][stOptions[i].repeatId] !== und) {
                                        res[k][stOptions[i].repeatId] = data[k][stOptions[i].repeatId];
                                    }
                                }
                            }
                        }
                    }
                    if (data.used_gs !== und) {
                        res.used_gs = data.used_gs;
                    }
                    data = res;
                    delete data.element_id;
                }
                else{
                     api.Helper.clearElementId([data]);
                }
                if(isMultiple!==true){
                    api.undoManager.start('paste',this.id);
                }
                const el=this.setData(data,this.el);
                api.pageBreakModule.countModules();
                resolve();
                if(isMultiple!==true){
                    api.undoManager.end('paste');
                }
            });
        }
        save(box) {
            return new Promise((resolve,reject)=>{
                api.LightBox.save().then(()=>{
                    if(!box){
                        box=this.el.querySelector('.tb_'+this.type+'_action').getBoundingClientRect();
                    }
                    const options = {
                            contructor: true,
                            loadMethod: 'html',
                            save: {
                                done: 'save'
                            },
                            data: {
                                ['s' + this.type]:{
                                    options: [{
                                        id: 'item_title_field',
                                        type: 'text',
                                        label: ThemifyConstructor.label.title
                                    }, {
                                        id: 'item_layout_save',
                                        type: 'checkbox',
                                        label: '',
                                        options: [{
                                            name: 'layout_part',
                                            value: ThemifyConstructor.label.slayout_part
                                        }],
                                        after: '',
                                        help: 'Any changes made to a Layout Part are saved and reflected everywhere else they are being used (<a href="https://themify.me/docs/builder#layout-parts" target="_blank">learn more</a>)'
                                    }]
                                }
                            }
                        };
                        api.LightBox.el.classList.add('tb_save_module_lightbox');
                        api.LightBox.setStandAlone(box.left, box.top)
                        .open(options).then(lb=>{

                            const saveAsLibraryItem = e=> {
                                    e.stopPropagation();
                                    if('keydown' === e.type){
                                        if(e.code !== 'Enter'){
                                            return;
                                        }
                                    }
                                    else{
                                        e.preventDefault();
                                    }

                                    api.Spinner.showLoader('show');
                                    let settings;
                                    switch (this.type) {
                                        case 'row':
                                            settings = api.Utils._getRowSettings(this.el,this.type);
                                            api.Helper.clearElementId([settings], true);
                                            break;

                                        case 'module':
                                            settings = {
                                                mod_name: this.get('mod_name'),
                                                element_id: api.Helper.generateUniqueID(),
                                                mod_settings: this.get('mod_settings')
                                            };
                                            break;
                                    }
                                    settings = api.Helper.clear(settings);

                                    const form=api.Forms.serialize(lb),
                                        used_gs = api.GS.findUsedItems(settings),
                                        is_layout = form.item_layout_save,
                                        ajaxData={
                                            action:'tb_save_custom_item',
                                            item_title_field:form.item_title_field,
                                            item:JSON.stringify(settings),
                                            type:this.type
                                        };
                                    if(is_layout){
                                        ajaxData.item_layout_save=1;
                                    }
                                    if (used_gs !== false) {
                                        ajaxData.usedGS=used_gs;
                                    }
                                    api.LocalFetch(ajaxData).then(data=>{
                                        if (data.status === 'success') {

                                            const callback = ()=> {
                                                
                                                    delete data.status;
                                                    api.MainPanel.el.tfClass('panel_search')[0].value='';
                                                    if (is_layout) {
                                                        const args = {
                                                            mod_name: 'layout-part',
                                                            mod_settings: {
                                                                selected_layout_part: data.post_name
                                                            }
                                                        };
                                                        if (ThemifyConstructor.layoutPart.data.length > 0) {
                                                            ThemifyConstructor.layoutPart.data.push(data);
                                                        }

                                                        let model,
                                                            el;
                                                        if (this.type === 'row') {
                                                            const row = new api.Row({
                                                                cols: [{
                                                                    grid_class: 'col-full',
                                                                    element_id: api.Helper.generateUniqueID(),
                                                                    modules: [args]
                                                                }]
                                                            });
                                                            el=row.el;
                                                            model = api.Registry.get(el.tfClass('active_module')[0].dataset.cid);
                                                        } 
                                                        else {
                                                            model = new api.Module(args);
                                                            el=model.el;
                                                        }
                                                       
                                                        this.el.replaceWith(el);
                                                        if (api.mode === 'visual') {
                                                            model.trigger('ajax', model.get('mod_settings'));
                                                        } 
                                                    }
                                                    if (api.Library) {
                                                        let libraryItems = [api.MainPanel.el,api.SmallPanel.el],
                                                            fr=api.Library.create([data]);
                                                        for (let i = libraryItems.length-1; i>-1;--i) {
                                                            let libItem=libraryItems[i].tfClass('library_container')[0];
                                                            if(libItem){
                                                                let selectedTab = libItem.closest('.panel_tab').querySelector('.library_tab .current');
                                                                libItem.appendChild(fr.cloneNode(true));
                                                                if(selectedTab){
                                                                    Themify.triggerEvent(selectedTab,Themify.click);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    api.Spinner.showLoader('done');
                                                    api.LightBox.close();
                                                };
                                                if (is_layout) {
                                                    const oldId = ThemifyStyles.builder_id;
                                                    ThemifyStyles.builder_id = data.id;
                                                    api.Utils.saveCss([settings], '', data.id).then(() => {
                                                        ThemifyStyles.builder_id = oldId;
                                                        callback();
                                                    });
                                                } 
                                                else {
                                                    callback();
                                                }
                                        }
                                        else {
                                            api.LiteLightBox.alert(data.msg);
                                        }
                                    })
                                    .catch(err=>{
                                        api.Spinner.showLoader('error');
                                    });
                                },
                                saveBtn=lb.tfClass('builder_save_button')[0],
                                titleInput=lb.tfTag('input')[0];
                                saveBtn.tfOn(Themify.click,saveAsLibraryItem);
                                titleInput.tfOn('keydown',saveAsLibraryItem,{passive:true});

                            Themify.on('themify_builder_lightbox_close', ()=> {
                                lb.classList.remove('tb_save_module_lightbox');
                                saveBtn.tfOff(Themify.click,saveAsLibraryItem);
                                titleInput.tfOff('keydown',saveAsLibraryItem,{passive:true});
                            },true);
                            
                            resolve();
                        });
                })
                .catch(reject);
            });
        }
        import() {
            return new Promise(async(resolve,reject)=>{
                await api.LightBox.save();
                const box=this.el.querySelector('.tb_'+this.type+'_action').getBoundingClientRect(),
                    component = this.isSubCol===true?'SubColumn':this.get('mod_name'),
                    name = component.charAt(0).toUpperCase() + component.slice(1),
                    label = this.type === 'subrow' ? 'Sub-Row' : (this.isSubCol===true? 'Sub-Column' : name),
                    options = {
                        contructor: true,
                        loadMethod: 'html',
                        data: {
                            component_form: {
                                name: ThemifyConstructor.label.import_tab.replace('%s', name),
                                options: [{
                                    id: 'tb_data_field',
                                    type: 'textarea',
                                    label: ThemifyConstructor.label.import_label.replace('%s', label),
                                    help: ThemifyConstructor.label.import_data.replace('%s', name),
                                    class: 'fullwidth',
                                    rows: 13
                                }]
                            }
                        }
                    };

                api.LightBox.el.classList.add('tb_import_export_lightbox');
                const lb=await api.LightBox.setStandAlone(box.left, box.top).open(options),
                    click=async e=> {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = lb.querySelector('#tb_data_field').value;
                        if (val === '') {
                            reject();
                            api.LightBox.close();
                            return;
                        }
                        let res = JSON.parse(val);
                        if (!res.component_name || res.component_name !== this.type) {
                            api.LiteLightBox.alert(themifyBuilder.i18n.text_alert_wrong_paste);
                            return;
                        }
                        api.undoManager.start('import',this.id);
                        if (res.used_gs !== und) {
                            res=await api.GS.setImport(res.used_gs, res);
                        } 
                        res = api.Helper.clear(res);
                        delete res.component_name;
                        await this.setData(res,this.el);
                        api.pageBreakModule.countModules();
                        api.LightBox.close();
                        api.undoManager.end('import');
                    },
                    savBtn=lb.tfClass('builder_save_button')[0];
                    savBtn.tfOn(Themify.click,click);

                    Themify.on('themify_builder_lightbox_close', lb => {
                        lb.classList.remove('tb_import_export_lightbox');
                        savBtn.tfOff(Themify.click,click);
                        resolve();
                    }, true);
            });
        }
        async export(){
            await api.LightBox.save();
            const box=this.el.querySelector('.tb_'+this.type+'_action').getBoundingClientRect(),
                component = this.isSubCol===true?'SubColumn':this.get('mod_name'),
                name = component.charAt(0).toUpperCase() + component.slice(1),
                label = this.type === 'subrow' ? 'Sub-Row' : (this.isSubCol===true? 'Sub-Column' : name),
                options = {
                    contructor: true,
                    loadMethod: 'html',
                    save:false,
                    data: {
                        component_form: {
                            name: ThemifyConstructor.label.export_tab.replace('%s', name),
                            options: [{
                                id: 'tb_data_field',
                                type: 'textarea',
                                label: ThemifyConstructor.label.import_label.replace('%s', label),
                                help: ThemifyConstructor.label.export_data.replace('%s', name),
                                class: 'fullwidth',
                                rows: 13,
                                readonly:true
                            }]
                        }
                    }
                };
            api.LightBox.el.classList.add('tb_import_export_lightbox');
            const lb=await api.LightBox.setStandAlone(box.left, box.top).open(options),
                    data = this.getData(),
                    used_gs = api.GS.findUsedItems(data),
                    input = lb.querySelector('#tb_data_field'),
                    selectText=function(e) {
                        e.stopImmediatePropagation();
                        this.select();
                    };
                    data.component_name = this.type;
                if (used_gs !== false) {
                    const gsData = {};
                    for (let i = used_gs.length - 1; i > -1; --i) {
                        let gsPost = api.GS.styles[used_gs[i]],
                            styles = api.Helper.cloneObject(gsPost.data[0]);
                        if ('row' === gsPost.type || 'subrow' === gsPost.type) {
                            styles = styles.styling;
                        } else if (styles.cols !== und) {
                            styles = styles.cols[0];
                            if (styles) {
                                if ('column' === gsPost.type) {
                                    styles = styles.styling;
                                } else {
                                    styles = styles.modules !== und ? styles.modules[0].mod_settings : und;
                                }
                            }
                        } else {
                            styles = und;
                        }
                        if (styles !== und && Object.keys(styles).length > 0) {
                            gsData[used_gs[i]] = {
                                title: gsPost.title,
                                type: gsPost.type,
                                data: api.Helper.clear(styles, false)
                            };
                        }
                    }
                    if (Object.keys(gsData).length) {
                        data.used_gs = gsData;
                    }
                }

                input.tfOn(Themify.click, selectText,{passive:true})
                .value=JSON.stringify(data);

                Themify.on('themify_builder_lightbox_close', lb => {
                    lb.classList.remove('tb_import_export_lightbox');
                    input.tfOff(Themify.click, selectText,{passive:true});
                }, true);
        }
        edit(type) {
            return new Promise(async(resolve,reject)=>{
                
                if (api.isPreview || this.isEmpty===true) {
                    reject();
                    return;
                }
                const slug = this.get('mod_name'),
                    isBuilderEdit=type==='editBuilder' && api.mode==='visual';
                if(type==='edit' || type==='swap'){
                    type='';
                }
                
                this.tab=type || 'setting';
                if (api.activeModel !== null) {
                    if (isBuilderEdit===false && api.activeModel.id === this.id) {
                        const clicked = api.LightBox.el.querySelector('a[data-id="tb_options_'+this.tab+'"]');
                        if(clicked){
                            Themify.triggerEvent(clicked,Themify.click);
                        }
                        resolve(api.LightBox.el);
                        delete this.tab;
                        return;
                    } 
                    else {
                        await api.LightBox.save();
                    }
                }
                if(isBuilderEdit===true){
                    this.editLayoutPart();
                }
                else{
                    this.setBreadCrumbs(api.LightBox.el);
                    await api.LightBox.open(slug,this);
                }
                resolve(api.LightBox.el);
                delete this.tab;
            });
        }
        restore(){
            const oldState=api.undoManager.getState('saveLightbox');
            if(oldState){
                const diff = api.undoManager.getDiff('saveLightbox',oldState,api.undoManager.getCurrentState('saveLightbox'));
                if(Object.keys(diff).length>0){
                    if(diff.styles){
                        api.undoManager.styleChanges(diff.styles,'old',!diff.html);
                    }
                    if(diff.html){
                        api.undoManager.domChanges(diff.html,'old');
                    }
                    api.undoManager.clear('saveLightbox');
                    api.ActionBar.clear();
                    api.Utils.runJs(this.el, null, true);
                }
            }
        }
        options(input,type) {
            let handler,
                hasChange=false;
            const event = input.tagName === 'INPUT' && 'hide_anchor' !== type ? 'keyup' : 'change',
                onChange=(input,value,ev)=>{
                    const id=input.closest('.tb_lb_option').id;
                    if(api.activeModel===this){
                        if(!api.LightBox.el.contains(input)){
                            const lightboxInput=api.LightBox.el.querySelector('#'+id);
                            if(lightboxInput){
                                const type=lightboxInput.closest('[data-type]').dataset.type;
                                if(type==='layout'){
                                    for(let items=lightboxInput.children,i=items.length-1;i>-1;--i){
                                        items[i].classList.toggle('selected',items[i].id===value);
                                    }
                                    if(!value){
                                        lightboxInput.children[0].classList.add('selected');
                                    }
                                }
                                else if (type === 'checkbox'){
                                    lightboxInput.tfClass('tb_checkbox')[0].checked=!!value;
                                }
                                else{
                                    lightboxInput.value=value;
                                }
                            }
                        }
                    }
                    else if(this.type==='row' && ev==='change' && !input.parentNode.tfClass('tb_field_error_msg')[0] && api.undoManager.has('rowOptions')){
                        
                        this.set(id,value);
                        api.undoManager.end('rowOptions'); 
                        
                    }
                };
            if(type==='custom_css_id'){
                handler = e=> {
                    const _this=e.currentTarget,
                        id=_this.id,
                        lightboxInput=api.activeModel===this?api.LightBox.el.querySelector('#'+id):null,
                        error2=lightboxInput?lightboxInput.parentNode.tfClass('tb_field_error_msg')[0]:null,
                        error1=_this.parentNode.tfClass('tb_field_error_msg')[0],
                        idText=this.el.tfClass('tb_row_id')[0],
                        validate=api.Forms.getValidator('custom_css_id')(_this),
                        v=_this.value;
                        if(lightboxInput){
                            lightboxInput.value=v;
                        }
                        if(validate===true){
                            if(error1){
                                error1.remove();
                            }
                            if(error2){
                                error2.remove();
                            }
                            if(lightboxInput){
                                lightboxInput.classList.remove('tb_field_error');
                            }
                            if(this.type==='row' && api.activeModel!==this && !api.undoManager.has('rowOptions')){
                                api.undoManager.start('rowOptions',this); 
                            }
                            this.el.id=v;
                            idText.textContent=v;
                            return v;
                        }
                        this.el.removeAttribute('id');
                        idText.textContent=this.get(id);
                        const errorText=validate===false?ThemifyConstructor.label.errorId:validate;
                        if (!error1) {
                            const er = doc.createElement('span');
                            er.className = 'tb_field_error_msg';
                            er.textContent = errorText;
                            _this.after(er);
                            if(lightboxInput && !error2 && !api.LightBox.el.contains(_this)){
                                lightboxInput.classList.add('tb_field_error');
                                lightboxInput.after(er.cloneNode(true));
                            }
                        }
                        else{
                            error1.textContent=errorText;
                            if(error2){
                                error2.textContent=errorText;
                            }
                        }
                        return false;
                   
                };
            }
            else if(type==='custom_css'){
                let prev=input.value;
                handler =  e=> {
                    api.Forms.getValidator('custom_css')(e.currentTarget);
                    const v=e.currentTarget.value.trim();
                    if(this.type==='row' && api.activeModel!==this && !api.undoManager.has('rowOptions')){
                        api.undoManager.start('rowOptions',this); 
                    }
                    if (v && api.mode === 'visual') {
                        const cl=this.el.classList,
                            vCl=v.split(' ');
                        if(prev){
                            const prevCl=prev.split(' ');
                            for(let i=prevCl.length-1;i>-1;--i){
                                prevCl[i]=prevCl[i].trim();
                                if(prevCl[i]){
                                    cl.remove(prevCl[i]);
                                }
                            }
                        }
                        for(let i=0,len=vCl.length;i<len;++i){
                            vCl[i]=vCl[i].trim();
                            if(vCl[i]){
                                cl.add(vCl[i]);
                            }
                        }
                        prev=v;
                    }
                    return v;
                };
            }
            else if(type==='layout'){
                handler = e=> {
                    if(this.type==='row' && api.activeModel!==this){
                        api.undoManager.start('rowOptions',this); 
                    }
                    const layout=e.currentTarget.closest('.tb_lb_option'),
                        v=layout.tfClass('selected')[0].id,
                        id=layout.id;
                    if (api.mode === 'visual') {
                        api.liveStylingInstance.bindRowWidthHeight(id, v, this.el);
                    }
                    else{
                        const cl=this.el.classList;
                        if (id === 'row_height') {
                            cl.toggle('fullheight',v === 'fullheight');
                        } 
                        else {
                            cl.remove('fullwidth','fullwidth_row_container');
                            if (v === 'fullwidth') {
                                cl.add('fullwidth_row_container');
                            } 
                            else if (v === 'fullwidth-content') {
                                cl.add('fullwidth');
                            } 
                        }
                    }
                    return v;
                };
            }
            else if(type==='row_anchor'){
                handler = e=> {
                    if(this.type==='row' && api.activeModel!==this && !api.undoManager.has('rowOptions')){
                        api.undoManager.start('rowOptions',this); 
                    }
                    const v=e.currentTarget.value.trim().replace('#', '');
                    if (api.mode === 'visual') {
                        const cl=this.el.classList,
                            prev=this.el.dataset.anchor;
                        if(prev){
                            cl.remove('tb_section-'+prev,'tb_has_section');
                        }
                        if (v !== '') {
                            cl.add('tb_section-'+v,'tb_has_section');
                            this.el.dataset.anchor=v;
                        }
                        else{
                            this.el.removeAttribute('data-anchor');
                        }
                    }
                    this.el.tfClass('tb_row_anchor')[0].textContent=v;
                    return v;
                };
            }
            else if(type==='hide_anchor'){
                handler = e=> {
                    if(this.type==='row' && api.activeModel!==this){
                          api.undoManager.start('rowOptions',this); 
                    }
                    const target=e.currentTarget,
                        v=target.checked?target.value:null;
                    if (api.mode === 'visual') {
                        this.el.toggleAttribute('data-hide-anchor', v==='1');
                    }
                    return v;
                };
            }
            input.tfOn(event,e=>{
                hasChange=true;
                const v=handler(e);
                if(v!==false){
                    const target=e.currentTarget;
                    onChange(target,v,e.type);
                    if(e.type==='keyup'){
                        target.tfOn('focusout',e=>{
                            onChange(e.currentTarget,e.currentTarget.value,'change');
                            api.undoManager.clear('rowOptions');
                        },{passive:true,once:true});
                    }
                }
            },{passive:true});
        }
    };

    api.Row =class extends api.Base {;
        constructor(fields) {
            super(fields);
            this.convertToGrid(fields);//convert old data to grid
            if(!(this instanceof  api.Subrow )){//hack js isn't real OOP,doesn't recognize child class props/method in constructor
                this.type='row';
                this.initialize();
                this.render();
            }
        }
        initialize(){
            super.initialize();
            
            api.Registry.on(this.id,'gridMenu',this.gridMenu);
            if(!(this instanceof  api.Subrow )){
                api.Registry.on(this.id,'optionsTab',this.optionsTab);
            }
        }
        defaults(){
            return {
                cols: [],
                styling: {}
            };
        }
        attributes() {
            const data = this.get('styling'),
                attr = {
                    class: 'module_row themify_builder_row tf_clearfix tb_' + this.id
                };
            if (data !== null) {
                if (data.custom_css_row !== und && data.custom_css_row !== '') {
                    attr.class += ' ' + data.custom_css_row;
                }
                if (data.row_width === 'fullwidth-content') {
                    attr.class += ' fullwidth';
                }
                if (data.custom_css_id !== und && data.custom_css_id !== '') {
                    attr.id = data.custom_css_id;
                }
            }
            return attr;
        }
        render() {
            let not_empty = false,
                cols = this.get('cols');
            if(this.type==='subrow'){
                this.el.tfClass('module_subrow')[0].classList.add('tb_' + this.id);
            }
            const container = this.el.tfClass(this.type+'_inner')[0],
                fr = doc.createDocumentFragment(),
                len = cols.length;
            if (len > 0) {
                if (len > 1 && this.get('desktop_dir') === 'rtl') { //it's old version data. Should follow dom order in desktop mode
                    cols = cols.reverse();
                }
                for (let i = 0; i < len; ++i) {
                    if (cols[i] !== und && cols[i] !== null) {
                        let c = new api.Column(cols[i],this.type==='subrow');
                        fr.appendChild(c.el);
                        if (not_empty === false) {
                            let m=c.get('modules');
                            not_empty = m!==und && m.length>0;
                        }
                        if(len > 1 && (i===0 || i===(len-1))){
                            c.el.classList.add((i===0?'first':'last'));
                        }
                    }
                }
                const cl=['tb_col_count_' + len];
                if(len > 1){
                    //backward compatibility
                    const sizes = this.get('sizes');
                    if(api.mode!=='visual'){
                        const points = api.breakpointsReverse,
						st=this.get('styling'),
                        data={grid:Object.assign({count:len,model:this},sizes)};
                        if(!data.grid.desktop_size){
                            data.grid.desktop_size=len;
                        }
                        for(let i=points.length-1;i>-1;--i){
                           let vals=ThemifyStyles.fields.grid.call(ThemifyStyles, 'grid', this.type, {}, data, this.id, st, points[i], true);
                           this.setGridCss(vals,points[i]);
                        }
                    }
                    let align = sizes.desktop_align;
                    if(align===und){
                        align=isFullSection===true?'center':'start';
                    }
                    if (align === 'start') {
                        align = 'top';
                    } else {
                        align = align === 'center' ? 'middle' : 'bottom';
                    }
                    cl.push('col_align_' + align);
                    if (sizes.desktop_dir === 'rtl') { 
                        cl.push('direction_rtl');
                    }
                    if (sizes.desktop_gutter==='narrow' || sizes.desktop_gutter === 'none') {//backward
                        cl.push('gutter-' + sizes.desktop_gutter);
                    }
                    if (sizes.desktop_auto_h === 1) { 
                        cl.push('col_auto_height');
                    }
                }
                container.className+=' '+cl.join(' ');
            } 
            else {
                let col=new api.Column({},this.type==='subrow');
                fr.appendChild(col.el);
            }
            if(this.type==='row'){
                const anchor=this.get('row_anchor'),
                    custom_css_id=this.get('custom_css_id');
                if (anchor !== und && anchor!== '') {
                    this.el.tfClass('tb_row_anchor')[0].textContent = anchor;
                }
                if (custom_css_id !== und && custom_css_id!== '') {
                    this.el.tfClass('tb_row_id')[0].textContent = custom_css_id;
                }
            }
            
            if (not_empty === false) {
                this.el.classList.add('tb_row_empty');
            }
            this.visibilityLabel();
            container.appendChild(fr);
            return this;
        }
        convertToGrid(fields){
            const points = api.breakpointsReverse,
                bpLength = points.length,
                cols=fields.cols,
                count=cols!==und?cols.length:0;
            let sizes = fields.sizes;
            
            if(sizes===und){//this key should always exist in the grid version,even if it's empty
                sizes={};
                let align = fields.column_alignment,
                    colh = fields.column_h;
                    
                if (count > 1) {

                    const gutter = fields.gutter;
                    if (gutter && gutter !== 'gutter' && gutter !== 'gutter-default') {
                        sizes.desktop_gutter = gutter.replace('gutter-', '');
                    }
                    const g = sizes.desktop_gutter || 'def',
                        gridClass=[],
                        gridWidth=[];
                    let hasCustomWidth=false;
                    for(let i=0;i<count;++i){
                        let custom_w=cols[i].grid_width;
                        if(custom_w){
                            hasCustomWidth=true;
                            gridWidth.push(custom_w);
                            delete cols[i].grid_width;
                        }
                        if(cols[i].grid_class){
                            gridClass.push(cols[i].grid_class);
                            if (!custom_w) {
                                gridWidth.push(cols[i].grid_class);
                            }
                        }
                    }    
                    let desktop_size,
                        useResizing = hasCustomWidth;

                    if (useResizing === false && g !== 'def' && gridClass.length > 0) {
                        desktop_size = ThemifyStyles.gridBackwardCompatibility(gridClass);
                        //in old version gutter narrow,none have been done wrong for sizes 1_2,2_1,1_1_2,1_2_1 and etc we need to convert them to custom sizes to save the same layout
                        useResizing = desktop_size.indexOf('_') !== -1;
                    }
                    if (useResizing === true) {
                        const colSizes = ThemifyStyles.getOldColsSizes(g);
                        for (let i = gridWidth.length - 1; i > -1; --i) {
                            if (typeof gridWidth[i] === 'string' && gridWidth[i].indexOf('col') !== -1) {
                                let cl = gridWidth[i].split(' ')[0].replace(/tb_3col|tablet_landscape|tablet|mobile|column|first|last/ig, '').trim();
                                if (colSizes[cl] !== und) {
                                    gridWidth[i] = colSizes[cl];
                                } else {
                                    gridWidth.splice(i, 1);
                                }
                            }
                        }
                        const min = Math.min.apply(null, gridWidth);
                        for (let i = gridWidth.length - 1; i > -1; --i) {
                            gridWidth[i] = min === gridWidth[i] ? '1fr' : (parseFloat((gridWidth[i] / min).toFixed(5)).toString() + 'fr');
                        }
                        desktop_size = gridWidth.join(' ');
                    } 
                    else if (!desktop_size && gridClass.length > 0) {
                            desktop_size = ThemifyStyles.gridBackwardCompatibility(gridClass);
                    }
                    if(desktop_size){
                        desktop_size=ThemifyStyles.getColSize(desktop_size,false);
                        if (desktop_size !== '1' && desktop_size !== '2' && desktop_size !== '3' && desktop_size !== '4' && desktop_size !== '5' && desktop_size !== '6') {
							if( fields.desktop_dir==='rtl' && (useResizing===true || desktop_size.toString().indexOf('_')!==-1)){
								desktop_size=useResizing===true?desktop_size.split(' '):desktop_size.split('_');
								desktop_size=desktop_size.reverse();
								desktop_size=useResizing===true?desktop_size.join(' '):desktop_size.join('_');
							}
                            sizes.desktop_size = desktop_size;
                        }
                    }
                    for (let i = bpLength - 1; i > -1; --i) {
                        let bp = points[i],
                            dir = fields[bp + '_dir'] || 'ltr',
                            col = fields['col_' + bp];

                            sizes[bp + '_dir'] = dir === '1' || dir === 1 ? 'rtl' : dir;

                        if (bp !== 'desktop') {
                            //backward compatibility for themify-builder-style.css media-query(by default all cols in mobile should be fullwidth)
                            let c = (col && col !== 'auto' && col !== '-auto') ? ThemifyStyles.gridBackwardCompatibility(col) : 'auto';
                           
                            if (c === 'auto') {
                                if (hasCustomWidth === true) {
                                    c = desktop_size;
                                } 
                                else {
                                    let bpkey = bp[0],
                                        grid;
                                    if (bp.indexOf('_') !== -1) {
                                        bpkey += bp.split('_')[1][0];
                                    }
                                    grid = ThemifyStyles.getAreaValue('--area' + bpkey + count + '_' + c); //check first for area for breakpoint e.g --aream5_3
                                    if (!grid) {
                                        grid = ThemifyStyles.getAreaValue('--area' + count + '_' + c);
                                    }
                                    if (!grid) {
                                        for (let j = i + 1; j < bpLength; ++j) {
                                            grid = sizes[points[j] + '_size'];
                                            if (grid && grid !== 'auto') {
                                                grid = grid.indexOf('fr') !== -1 ? grid : ThemifyStyles.gridBackwardCompatibility(grid);
                                                break;
                                            }
                                        }
                                        c = !grid ? count.toString() : grid;
                                    }
                                }
                            }
                            else if(c.toString().indexOf('_')===-1 && c>0 && c<6 && count<c){
                                c='';
                                for (let j = i + 1; j <bpLength; ++j) {
                                    if (sizes[points[j] + '_size'] !== und) {
                                        c=sizes[points[j] + '_size'];
                                        break;
                                    }
                                }
                            }
                            sizes[bp + '_size'] = c===''?'':ThemifyStyles.getColSize(c,false);
                        }
                        
                        delete fields[bp + '_dir'];
                        delete fields['col_' + bp];
                    }
                }
                if (align) {
                    if (align === 'col_align_top') {
                        align = '';
                    } 
                    else {
                        align = align === 'col_align_middle' ? 'center' : 'end';
                    }
                    if (align !== '') {
                        sizes.desktop_align = align;
                    }
                }
                sizes.desktop_auto_h = colh ? 1 : -1;
                delete fields.column_alignment;
                delete fields.gutter;
                delete fields.column_h;
         
            }
            else if (count > 1) {
                for (let i = 0; i < bpLength - 1; ++i) {
                    if (sizes[points[i] + '_size'] === und) {
                        for (let j = i + 1; j < bpLength - 1; ++j) {
                            if (sizes[points[j] + '_size'] !== und) {
                                sizes[points[i] + '_size'] = sizes[points[j] + '_size'];
                                break;
                            }
                        }
                    }
                }
            }
            this.fields.sizes=sizes;
        }
        getSizes(type,bp){
            if(!bp){
                bp=api.activeBreakPoint;
            }
            const sizes=this.get('sizes'),
                points = api.breakpointsReverse,
                len = points.length;
            let gutter,aligment,auto_h,area,size;
            if(sizes!==und){
                for (let i = points.indexOf(bp); i < len; ++i) {

                    if (gutter===und) {
                        gutter = sizes[points[i] + '_gutter'];
                    }
                    if (aligment===und) {
                        aligment = sizes[points[i] + '_align'];
                    }
                    if (size===und) {
                        size = sizes[points[i] + '_size'];
                    }
                    if (auto_h===und) {
                        auto_h = sizes[points[i] + '_auto_h'];
                    }
                    if (area===und) {
                        area = sizes[points[i] + '_area'];
                    }
                    if (gutter && aligment && size && auto_h && area) {
                        break;
                    }
                }
            }
            if(size){
                size=ThemifyStyles.getColSize(size,false);
            }
            if(area){
                area=ThemifyStyles.getArea(area,false,bp,this.el.tfClass(this.type+'_inner')[0].childElementCount);
            }
			if(aligment===und){
				aligment=isFullSection===true?'center':'start';
			}
            let res={gutter:gutter,align:aligment,size:size,auto_h:auto_h,area:area};
            if(type){
                res=res[type];
            }
            return res;
        }
        setSizes(vals,bp){
            if(!bp){
                bp=api.activeBreakPoint;
            } 
            const sizes=this.get('sizes');
            for(let k in vals){
                if(vals[k]!=='' && vals[k]!==und && vals[k]!==null){
                    if(k==='size'){
                        if(vals[k] && vals[k].indexOf(' ')!==-1){
                            //if there is grid with the same size use it instead of custom size(e.g "2.1fr 1fr" will be become to grid 2_1)
                            vals[k]=ThemifyStyles.getColSize(vals[k],false);
                        }
                    }
                    else if(k==='gutter'){
                        vals[k]=ThemifyStyles.getGutter(vals[k]);
                    }
                    sizes[bp+'_'+k]=vals[k].toString().replace(/  +/g, ' ').trim();
                }
                else if(vals[k]===''){
                    delete sizes[bp+'_'+k];
                }
            }
            this.set('sizes',sizes);
        }
        getGridCss(vals,bp){
            const data={grid:{}};
            for (let k in vals) {
                if(k==='size'){
                    if(vals[k] && vals[k].indexOf(' ')!==-1){
                        vals[k]=ThemifyStyles.getColSize(vals[k],false);
                    }
                }
                else if(k==='gutter'){
                    vals[k]=ThemifyStyles.getGutter(vals[k]);
                }
                if (bp!==k.split('_')[0]) {
                    data.grid[bp + '_' + k] = vals[k];
                } 
                else {
                    data.grid[k] = vals[k];
                }
            }
            return ThemifyStyles.fields.grid.call(ThemifyStyles, 'grid', this.type, {}, data, this.id, null, bp, true);
        }
        setCols(vals,bp,update){
            if(!bp){
                bp=api.activeBreakPoint;
            }
            
            if(vals.gutter!==und){
                vals.gutter = ThemifyStyles.getGutter(vals.gutter);
            }
           
            const res=this.getGridCss(vals,bp); 
            if(res['--align_items']===und && vals.auto_h==='-1'){
                res['--align_items']='';
            }
            if(res['--colG']===und && vals.gutter==='gutter'){
                res['--colG']='';
            }
           if (bp === 'desktop') { //backward
                const oldGutter=this.getSizes('gutter'),
                    cl = this.el.tfClass(this.type + '_inner')[0].classList;
                if(vals.gutter==='none' || vals.gutter==='narrow'){
                    cl.add('gutter-'+vals.gutter);
                }
                if(oldGutter){
                    cl.remove('gutter-'+oldGutter);
                }
            }
            this.setGridCss(res,bp);
            if(update!==false){
                const savedVals=['align','area','size','gutter','auto_h'],
                        newVals={};
                for(let i=savedVals.length-1;i>-1;--i){
                    if(vals[savedVals[i]]!==und){
                        newVals[savedVals[i]]=vals[savedVals[i]];
                    }
                }
                if(bp==='desktop'){
                    newVals.area='';
                }
                if(newVals.auto_h!==und && newVals.auto_h!=='-1'){
                    newVals.align='';
                }
                this.setSizes(newVals,bp);
            }
        }
        
        optionsTab(el) {
            let prevData = null,
                prevType=null;
            const prevModel = api.activeModel || null,
                prevComponent = ThemifyConstructor.component;
                
            if (prevModel !== null) {
                prevType=prevModel.type;
                prevData = api.Helper.cloneObject(prevModel.get('styling'));
            }
            if (el.childElementCount>0) {
                while (el.firstChild!==null) {
                    el.removeChild(el.lastChild);
                }
            }
            ThemifyConstructor.values = prevModel && prevModel.id===this.id&& ThemifyConstructor.clicked==='setting'?api.Forms.serialize('tb_options_setting', true):(api.Helper.cloneObject(this.get('styling')) || {});

            ThemifyConstructor.type=ThemifyConstructor.component = this.type;
            api.activeModel = this;
            const args=api.FormTemplates.getItem('row').setting.options.slice(0, 6);
            args[5]=api.Helper.cloneObject(args[5]);
            args[5].accordion=false;
            el.appendChild(ThemifyConstructor.create(args));
            ThemifyConstructor.values = prevData;
            ThemifyConstructor.component = prevComponent;
            ThemifyConstructor.type=prevType;
            api.activeModel = prevModel;
        }
        grid(target) {
            let grid = target.dataset.grid || '',
                st = {};
            const count = grid ? (grid.indexOf('_') !== -1 ? grid.split('_').length : parseInt(grid)) : '',
                inner = this.el.tfClass(this.type + '_inner')[0],
                bp = api.activeBreakPoint,
                old = this.getSizes('size') || '1',
                cols = inner.children,
                oldCount = cols.length,
                cl = inner.classList,
                wrap = target.closest('#grid'),
                range = wrap.querySelector('#range'),
                slider = wrap.querySelector('#slider'),
                gridCl=wrap.classList,
                changeType=bp==='desktop'?'grid':'style',
                points = bp === 'desktop' ? api.breakpointsReverse : [bp];
            if (grid === 'user') {
                return;
            }
            api.undoManager.start(changeType,this);
            if (bp === 'desktop') {
                // remove unused column
                cl.remove('tb_col_count_' + oldCount);
                cl.add('tb_col_count_' + count);
                gridCl.remove('tb_col_count_' + oldCount);
                gridCl.add('tb_col_count_' + count);
                const fr = doc.createDocumentFragment();
                if (count < cols.length) {
                    for (let i = cols.length - 1; i >= count; --i) {
                        let childs = cols[i].tfClass('tb_holder')[0].children;
                        for (let j = childs.length - 1; j > -1; --j) {
                            if (childs[0]) {
                                fr.appendChild(childs[0]);
                            }
                        }
                        cols[i].remove(); // finally remove it
                    }
                    cols[cols.length - 1].tfClass('tb_holder')[0].appendChild(fr); // relocate active_module
                } else {
                    for (let i = 0; i < count; ++i) {
                        if (!cols[i]) {
                            // Add column
                            let c = new api.Column(cols[i],this.type==='subrow');
                            fr.appendChild(c.el);
                        }
                    }
                    inner.appendChild(fr);
                }
               //backward compatibility
                const _COL_CLASSES=api.getColClass(),
                    _COL_CLASSES_VALUES=api.getColClassValues(),
                    colsCount = cols.length,
                    colsClass = _COL_CLASSES[grid] !== und ? _COL_CLASSES[grid] : _COL_CLASSES[colsCount],
                    len = _COL_CLASSES_VALUES.length - 1;
                for (let i = colsCount - 1; i > -1; --i) {
                    let c=cols[i].classList;
                    for (let j = len; j > -1; --j) {
                        c.remove(_COL_CLASSES_VALUES[j]);
                    }
                    if (colsClass !== und && colsCount < 7) {
                        c.add(colsClass[i]);
                    }
                    c.remove('first','last');
                }
                if(colsCount>1){
                    cols[0].classList.add('first');
                    cols[colsCount-1].classList.add('last');
                }
            } 
            this.setCols({size:grid,area:''});
            if (bp === 'desktop') {
                const changedPoints=[];
                //reset in responsive mode
                for (let i = points.length - 2; i > -1; --i) {
                    //reseting to auto, if breakpoint has auto value select it otherwise the parent value should be applied
                    let bp = points[i],
                    area= this.getGridCss({size:'auto'},bp);
                    
                    if(this.getSizes('size',bp)!=='auto'){
                        changedPoints.push(api.Helper.getBreakpointName(bp));
                    }
                    if(area['--area'] && area['--area'].indexOf(' ')===-1){//apply css and update data
                        this.setCols({size: 'auto'},bp);
                    }
                    else{
                        this.setGridCss({'--area':'','--col':''},bp);
                        this.setSizes({size: 'auto'},bp);// update data
                    }
                    this.setMaxGutter(bp);
                }
                if(changedPoints.length>0){
                   // TF_Notification.showHide('tf',themifyBuilder.i18n.gridChanged.replace('%s',changedPoints.join(', ')),5000);
                }
            }
            else{
                const areaLength = getComputedStyle(inner).getPropertyValue('--area').split('" "')[0].split(' ').length;
                gridCl.toggle('tb_1col_grid', areaLength === 1);
            }
            let gutter =this.setMaxGutter();
            range.max = slider.max = this.getMaxGutter();
            range.value = slider.value = parseFloat(gutter); 
            api.Utils.setCompactMode(cols);
            Themify.trigger('tb_grid_changed', [this.el.closest('.module_row'), inner]);
            api.Utils._onResize(true);
            api.undoManager.end(changeType);
        }
        
        gutter(target) {
            api.undoManager.start('style',this);
            const gutter=target.dataset.value,
                gutterValue = ThemifyStyles.getGutterValue(gutter),
                rangeWrap = target.closest('#grid'),
                range = rangeWrap.querySelector('#range'),
                slider = rangeWrap.querySelector('#slider'),
                gutterVal = parseFloat(parseFloat(gutterValue).toFixed(2).toString()).toString(), //trailing zeros
                unit = gutterValue.replace(gutterVal, '') || '%';
                
            
            range.max = slider.max = this.getMaxGutter( unit);
            range.value = slider.value = gutterVal;
            rangeWrap.querySelector('#range_unit').value = unit;
            this.setCols({gutter:gutter});
            api.Utils._onResize(true);
            api.undoManager.end('style');
        }
        
        autoHeight(target) {
            api.undoManager.start('style',this);
            const value = target.dataset.value;
            if (api.activeBreakPoint === 'desktop') { //backward
                const inner = this.el.tfClass(this.type + '_inner')[0];
                inner.classList.toggle('col_auto_height', value == '1');
            }
            this.setCols({auto_h:value});
            api.undoManager.end('style');
        }
        alignment(target) {
            api.undoManager.start('style',this);
            const value = target.dataset.value;
            if (api.activeBreakPoint === 'desktop') { //backward
                let inner = this.el.tfClass(this.type + '_inner')[0],
                    prev = this.get('sizes').desktop_align,
                    _align = value;
                if (prev) {
                    prev = prev.replace('col_align_', '');
                    if (prev === 'start') {
                        prev = 'top';
                    } else {
                        prev = prev === 'center' ? 'middle' : 'bottom';
                    }
                    inner.classList.remove('col_align_' + prev);
                }
                if (_align === 'start') {
                    _align = 'top';
                } else {
                    _align = _align === 'center' ? 'middle' : 'bottom';
                }
                inner.classList.add('col_align_' + _align);
            }
            this.setCols({align:value});
            api.undoManager.end();
        }
        direction(target) {
            const mode=api.activeBreakPoint==='desktop'?'direction':'style',
                inner = this.el.tfClass(this.type + '_inner')[0];
            
            api.undoManager.start(mode,this);
            if (api.activeBreakPoint === 'desktop') {
                if (!inner.hasAttribute('data-transition')) {
                    inner.dataset.transition=1;
                    
                    let cols = inner.children,
                        self=this,
                        len = cols.length,
                        desktopSizes=this.getSizes('size'),
                        oldcolsAreas={};
                        for(let i=len-1;i>-1;--i){
                            oldcolsAreas[cols[i].dataset.cid]=(i+1);
                        }
                    cols[len - 1].tfOn('transitionend', function(){
                        const fr = doc.createDocumentFragment();
                        for (let i = len - 1; i > -1; --i) {
                            fr.appendChild(cols[i]);
                        }
                        inner.appendChild(fr);
                        this.tfOn('transitionend', ()=> {
                            for (let i = len - 1; i > -1; --i) {
                                cols[i].style.setProperty('transition', '');
                                cols[i].style.setProperty('transition-delay', '');
                                cols[i].style.setProperty('transform', '');
                            }
                            inner.classList.remove('direction_rtl');
                            inner.removeAttribute('data-transition');
                            
                            const newColsAreas={};
                            for(let i=len-1;i>-1;--i){
                                newColsAreas[cols[i].dataset.cid]=(i+1);
                            }
                            //keep the breakpoints cols order
                            for (let points = api.breakpointsReverse, i = points.length - 2; i > -1; --i) {
                                let respArea=self.getSizes('area',points[i]);
                                if(respArea){
                                    if (respArea.indexOf('"') === -1) {//is css variable
                                        respArea = computed.getPropertyValue('--area' + respArea).replace(/\s\s+/g, ' ').trim();
                                    }
                                    for (let cid in newColsAreas) {
                                        if (oldcolsAreas[cid] !== newColsAreas[cid]) {
                                            respArea = respArea
                                                .replaceAll(oldcolsAreas[cid] + ' ', '#' + newColsAreas[cid] + '# ')
                                                .replaceAll(oldcolsAreas[cid] + '"', '#' + newColsAreas[cid] + '#"');
                                        }
                                    }
                                    self.setCols( {area: respArea.replaceAll('#', '')},  points[i]);
                                }
                            }
                            api.undoManager.end(mode);
                            self=len=desktopSizes=cols=null;
                        }, {
                            once: true,
                            passive: true
                        });
                        
                        if(desktopSizes){
                            self.setCols({size:desktopSizes.split(' ').reverse().join(' ')});
                        }
                        setTimeout(() => {
                            for (let i = len - 1; i > -1; --i) {
                                cols[i].style.setProperty('transition-delay', ((len - i) / 10) + 's');
                                cols[i].style.setProperty('transform', 'scale(1)');
                            }
                        }, 60);

                    }, {
                        once: true,
                        passive: true
                    });
                    for (let i = len - 1; i > -1; --i) {
                        cols[i].style.setProperty('transition', 'transform .3s ' + ((i + 1) / 10) + 's');
                        cols[i].style.setProperty('transform', 'scale(0)');
                    }
                }
            }
            else{
                let area=getComputedStyle(inner).getPropertyValue('--area').replace(/  +/g, ' ').trim(),
                    newArea=[],
                    colsSize=area.split('" "')[0].split(' ').length;
               
                area=area.replaceAll('"', '').trim().split(' ');
                area=area.reverse(); 
                const len=area.length;
                for(let i=len-1;i>-1;--i){
                    if(area[i]==='.'){
                        area.push(area.splice(i, 1)[0]);
                    }
                }
                for (let i = 0,len2=(len/colsSize); i < len2; ++i) {
                    newArea.push('"' + area.slice(i*colsSize,(i+1)*colsSize).join(' ') + '"');
                }
                this.setCols( {area: newArea.join(' ')});
                
                api.undoManager.end(mode);
            }
        }
        breakpoint(el){
            api.ToolBar.breakpointSwitcher(el.dataset.id).then(()=>{
                if (api.mode === 'visual') {
                    api.ActionBar.clear();
                    setTimeout(()=>{
                        const scrollTop = this.el.getBoundingClientRect().top+win.scrollY-100;
                        topWindow.scroll(0, scrollTop);
                        win.scroll(0, scrollTop);
                        api.ActionBar.hover({target:this.el.tfClass('tb_'+this.type+'_action')[0]});
                    },50);
                }
            }).catch(e=>{
                
            });
        }
        gridMenu(el) {
            const tpl=doc.tfId('tmpl-builder_grid_list').content.cloneNode(true),
                bp = api.activeBreakPoint,
                inner = this.el.tfClass(this.type + '_inner')[0],
                cl = inner.classList,
                elCl=el.classList,
                count = inner.childElementCount,
                countClass = 'tb_col_count_' + count,
                areaLength = bp !== 'desktop' ? getComputedStyle(inner).getPropertyValue('--area').split('" "')[0].split(' ').length : null,
                range = tpl.querySelector('#range'),
                rangeUnit=tpl.querySelector('#range_unit'),
                slider = tpl.querySelector('#slider');
                
            
            this.setMaxGutter(bp);
            let items = tpl.querySelector('.grid_list').children,
                {gutter,align,size,auto_h,area}=this.getSizes();
            if(!size){
                size = count>6?'user':count;
            }
            else if (typeof size==='string' && size.indexOf(' ') !== -1) {
                size = 'user';
            }
            for (let i = cl.length - 1; i > -1; --i) {
                if (cl[i].indexOf('tb_col_count_') === 0) {
                    if (countClass !== cl[i]) {
                        cl.remove(cl[i]);
                    }
                    break;
                }
            }
            cl.add(countClass);
            elCl.add(countClass,api.activeBreakPoint);
            elCl.toggle('tb_1col_grid', areaLength === 1);
            if(api.activeBreakPoint!=='desktop'){
                elCl.add('tb_responsive_mode');
            }
            
            if(this.el.classList.contains('fullheight')){
               elCl.add('fullheight');
            }
            for (let i = items.length - 1; i > -1; --i) {
                items[i].classList.toggle('selected', items[i].dataset.grid == size);
            }
            if (align) {
                items = tpl.querySelector('.alignment').children;
                for (let i = items.length - 1; i > -1; --i) {
                    items[i].classList.toggle('selected', (items[i].dataset.value === align));
                }
            }

            if (auto_h) {
                items = tpl.querySelector('.auto_height').children;
                for (let i = items.length - 1; i > -1; --i) {
                    items[i].classList.toggle('selected', (items[i].dataset.value == auto_h));
                }
            }

            if (gutter) {
                gutter = ThemifyStyles.getGutter(gutter);
                items = tpl.querySelector('.gutter').children;
                for (let i = items.length - 1; i > -1; --i) {
                    let v = items[i].dataset.value;
                    items[i].classList.toggle('selected', (v === gutter));
                }
            } 
            else {
                gutter = 'gutter';
            }

            gutter = ThemifyStyles.getGutterValue(gutter);

            const gutterVal=parseFloat(gutter),
            gutter_unit = gutter.toString().replace(gutterVal.toString(), '') || '%';
            range.max = slider.max = this.getMaxGutter(gutter_unit);
            range.value = slider.value = parseFloat(gutterVal.toFixed(4)).toString(); //trailing zeros
            rangeUnit.value = gutter_unit;
            
            el.tfOn(Themify.click,e=>{
                e.stopPropagation();
                const selected=e.target.closest('li'),
                    expand=!selected?e.target.closest('.expand'):null;
                if(selected){
                    const colAction=selected.closest('[data-col]');
                    if(colAction){
                        const childs=colAction.children;
                        if(childs.length>1){
                            for (let i = childs.length - 1; i > -1; --i) {
                                childs[i].classList.toggle('selected',selected===childs[i]);
                            }
                        }
                        const action=colAction.dataset.col;
                        this[action](selected);
                    }
                }
                else if(expand){
                    this.trigger(expand.dataset.action);
                }
            },{passive:true})
            .appendChild(tpl);
    
            setTimeout(()=>{
                if(el){
                const holder=el.querySelector('#range_holder'),
                        input = ThemifyConstructor.range.render({
                           id: 'range',
                           control: false,
                           event: 'input',
                           value: range.value,
                           unit: rangeUnit.value,
                           units: {
                               '%': {
                                   min: 0,
                                   increment: .1,
                                   max: this.getMaxGutter('%')
                               },
                               'em': {
                                   min: 0,
                                   max: this.getMaxGutter('em')
                               },
                               px: {
                                   min: 0,
                                   max: this.getMaxGutter( 'px')
                               }
                           }
                    }, ThemifyConstructor);
                   holder.innerHTML='';
                   holder.appendChild(input);
                   let req,
                        started=false,
                        inner,
                        _slider=holder.parentNode.querySelector('#slider'),
                        rangeInput=holder.querySelector('#range'),
                        unit=holder.querySelector('#'+rangeInput.id+'_unit'),
                        gutterRange=e=>{
                            e.stopImmediatePropagation();
                            const isChange=e.type==='change',
                            target=e.currentTarget;
                            if(isChange===false && target===unit){
                                return;
                            }
                            if(started===false){
                                inner=this.el.tfClass(this.type+'_inner')[0];
                                started=true;
                                api.undoManager.start('style',this);
                            }
                            req=requestAnimationFrame(()=>{
                                const unitVal = unit.value;
                                if (target === _slider) {
                                    rangeInput.value = _slider.value;
                                } 
                                else if (target === unit) {
                                    let maxValue = this.getMaxGutter( unitVal);
                                    rangeInput.max = _slider.max = maxValue;
                                    if (unitVal === 'px') {
                                        _slider.step = 1;
                                        rangeInput.value = _slider.value = parseInt(_slider.value);
                                    } else {
                                        _slider.step = .1;
                                    }
                                    if (parseFloat(rangeInput.value) > maxValue) {
                                        rangeInput.value = _slider.value = maxValue;
                                    }
                                   
                                } else {
                                    _slider.value = rangeInput.value;
                                }
                                let gutter = rangeInput.value;
                                if (gutter > 0) {
                                    gutter += unitVal;
                                } 
                                
                                if(isChange===true){
                                    
                                    if(req){
                                        cancelAnimationFrame(req);
                                    }
                                    const sizes = rangeInput.closest('#grid').tfClass('gutter')[0].children;
                                    gutter = ThemifyStyles.getGutter(gutter);
                                    for (let i = sizes.length - 1; i > -1; --i) {
                                        sizes[i].classList.toggle('selected', gutter === sizes[i].dataset.value);
                                    }
                                    this.setCols({'gutter':gutter},'',isChange);
                                    inner.style.setProperty('--colG','');
                                    this.setMaxGutter();
                                    if(started===true){
                                        inner=null;
                                        api.Utils._onResize(true);
                                        api.undoManager.end('style');
                                        started=false;
                                    }
                                    req=null;
                                }
                                else{
                                    inner.style.setProperty('--colG',gutter);
                                }
                            });
                        };
                   _slider.tfOn('input change',gutterRange,{passive:true});
                   rangeInput.tfOn('input change',gutterRange,{passive:true});
                   unit.tfOn('change',gutterRange,{passive:true});
                }
            },150);
        }
        setMaxGutter(bp){
            if (!bp) {
                bp = api.activeBreakPoint;
            }
            let gutter=this.getSizes('gutter',bp) || 'gutter';
                gutter = ThemifyStyles.getGutterValue(gutter);
                
            let gutterVal = parseFloat(gutter), //trailing zeros
                unit= gutter.toString().replace(gutterVal.toString(), '') || '%',
                maxGutter=this.getMaxGutter(unit,bp);
            if (maxGutter < gutterVal) {
                const msg=themifyBuilder.i18n.gutterChanged
                        .replace('%from',gutterVal+unit)
                        .replace('%to',maxGutter+unit)
                        .replace('%bp',api.Helper.getBreakpointName(bp));
               // TF_Notification.showHide('info',msg,3000);
                this.setCols({'gutter':(maxGutter+unit)},bp);
                gutterVal=maxGutter;
            }   
            return gutterVal+unit;
        }
        getMaxGutter(unit, bp) {
            if (!bp) {
                bp = api.activeBreakPoint;
            }
            const inner=this.el.tfClass(this.type + '_inner')[0],
                count = inner.childElementCount;
            if (count <= 1) {
                return 100;
            }
            const _MIN_COL_W = 5,
                allSizes=this.getSizes('',bp),
                w = inner.offsetWidth,
                computed = getComputedStyle(inner),
                fontSize = parseFloat(computed.getPropertyValue('font-size')),
                frSize = computed.getPropertyValue('--col'),
                sizes = frSize && frSize!=='none' && frSize.indexOf('repeat') === -1 ? frSize.replace(/\s\s+/g, ' ').trim().split(' ') : null;
                
            if(!unit){
                let gutter=allSizes.gutter || 'gutter';
                gutter = ThemifyStyles.getGutterValue(gutter);
                let gutterVal=parseFloat(gutter);
                unit= gutter.toString().replace(gutterVal.toString(), '') || '%';
            }
            let areaLength,
                area=allSizes.area;
            if (!area) {
                let cols=allSizes.size;
                if (cols) {
                    cols = cols.toString();
                    if (cols.indexOf(' ') !== -1) {
                        areaLength = cols.replace(/\s\s+/g, ' ').split(' ').length;
                    } 
                    else{
                        area=this.getGridCss({'size':'auto'},bp)['--area'];
                    }
                }
            }
            if (!areaLength) {
                if(area){
                    if(area.indexOf('var')!==-1){
                        area=computed.getPropertyValue(area.replace('var(','').replace(')',''));
                    }
                }
                areaLength = area ? area.replace(/  +/g, ' ').trim().split('" "')[0].split(' ').length : count;
            }

            let max = w,
                summPX = 0,
                summPercent = 0,
                summFr = 0,
                summEm = 0;
            if (sizes !== null) {
                for (let i = sizes.length - 1; i > -1; --i) {
                    let v = parseFloat(sizes[i]);
                    if (sizes[i].indexOf('fr') !== -1) {
                        summFr += v;
                    } else if (sizes[i].indexOf('%') !== -1) {
                        summPercent += v;
                    } else if (sizes[i].indexOf('em') !== -1) {
                        summEm += v;
                    } else {
                        summPX += v;
                    }
                }
                max -= summPX - parseFloat((summPercent * w) / 100) - summEm * fontSize;
            }
            max = parseFloat((max * 100) / w) - _MIN_COL_W * areaLength;
            max = parseFloat(max / (areaLength - 1));

            if (unit === 'px' || unit === 'em') {
                max = (w * max) / 100;
                max = unit === 'em' ? parseFloat(max / fontSize) : parseInt(max);
            }
            return parseFloat(parseFloat(max.toFixed(2)).toString());
        }
        
        setGridCss(css, bp) {
            if (!bp) {
                bp = api.activeBreakPoint;
            }
            const selector = ThemifyStyles.getStyleOptions(this.type).grid.selector;
            if (api.mode === 'visual') {
                const live = api.createStyleInstance();
                live.init(true, false, this);
                live.setMode(bp); 
                if (Object.keys(css).length > 0) {
                    for (let k in css) {
                            live.setLiveStyle(k, css[k], selector);
                    }
                }
            } 
            else {
                let fullSelector;
                const sheet = ThemifyStyles.getSheet(bp),
                    rules = sheet.cssRules,
                    elId = this.id,
                    points = api.breakpointsReverse,
                    index = points.indexOf(bp),
                    _selectors = [],
                    _setStyles = (p, v)=> {
                        const index = api.Utils.findCssRule(rules, fullSelector);
                        if (index === false || !rules[index]) {
                            if(v!=='' && v!==und){
                                sheet.insertRule(fullSelector + '{' + p + ':' + v + ';}', rules.length);
                            }
                        } else {
                            rules[index].style.setProperty(p, v);
                        }
                    };
                for (let i = index; i > -1; --i) {
                    _selectors.push(ThemifyStyles.getBaseSelector(this.type, elId, points[i]) + ' ' + selector);
                    if (bp === 'desktop') {
                        break;
                    }
                }
                fullSelector = _selectors.join(',');
                if (Object.keys(css).length > 0) {
                    for (let k in css) {
                        _setStyles(k, css[k]);
                    }
                } 
            }
        }
    };
    api.Subrow =class extends api.Row {
        constructor(fields) {
            super(fields);
            this.type='subrow';
            super.initialize();
            super.render();
        }
        attributes() {
            return {
                'class': 'active_module active_subrow'
            };
        }
    };

    api.Column=class extends api.Base {

        constructor(fields,isSubCol) {
            super(fields);
            this.type='column';
            if(isSubCol===true){
                this.isSubCol=true;
            }
            this.initialize();
            this.render();
        }
        defaults(){
            return {
                modules: [],
                styling: {}
            };
        }
        attributes() {
            const cl = this.get('grid_class'),
                attr = {
                    class: 'module_column tb_' + this.id
                };
            attr.class += true === this.isSubCol ? ' sub_column' : ' tb-column';
            if (cl) {
                attr.class += ' ' + cl;
            }
            if(this.oldPadding===true){
                attr.class += ' tb_old_padding';
            }
            return attr;
        }
        render() {
            const modules = this.get('modules');
            // check if it has module
            if (modules) {
                const holder = this.el.tfClass('tb_holder')[0],
                    fr = doc.createDocumentFragment();
                for (let i=0,len=modules.length;i<len;++i) {
                    if (modules[i] !== und && modules[i] !== null) {
                        let module = modules[i].cols === und ? (new api.Module(modules[i])) : (new api.Subrow(modules[i]));
                        fr.appendChild(module.el);
                    }
                    else{
                        modules.splice(i,1);
                    }
                }
                if (true === this.isSubCol ) {
                    holder.classList.add('tb_subrow_holder');
                }
                holder.appendChild(fr);
            }
            return this;
        }
    };
    
    api.Module=class extends api.Base {
        constructor(fields) {
            if(fields.mod_settings!==und){
                fields.mod_settings=ThemifyStyles.convertPreset(fields.mod_name,fields.mod_settings);
            }
            super(fields);
            this.type='module';
            this.initialize();
            this.render();
        }
        defaults(){
            return {
                mod_name: '',
                mod_settings: {}
            };
        }
        initialize(){
            super.initialize();
            const slug=this.get('mod_name');
        }
        editLayoutPart(){
            api.ActionBar.disable = true;
            api.Spinner.showLoader();
            Promise.all([
                Themify.loadCss(Themify.builder_url + 'css/editor/modules/layout-part',null,null,doc.tfId('themify-builder-admin-ui-css').nextElementSibling),
                Themify.loadJs(Themify.builder_url + 'js/editor/modules/layout-part')
            ])
            .then(()=>{
                Themify.trigger('tb_layout_edit');
                
                let className=this.get('mod_name').split('-');
                for(let i=0;i<className.length;++i){
                    className[i]=className[i].charAt(0).toUpperCase() + className[i].slice(1);
                }
                className=className.join('');
                const item=new api[className](this.id);
                item.edit().finally(()=>{
                   api.ActionBar.disable = null; 
                });
                api.LayoutPart.item=item;
            }).catch(e=>{
                api.Spinner.showLoader('error');
                api.ActionBar.disable = null; 
            });
        }
        toRenderData() {
            const st=this.get('mod_settings');
            return {
                name: this.getName(),
                slug: this.get('mod_name'),
                element_id: this.id,
                icon: this.getImage(st),
                excerpt: this.getExcerpt(st)
            };
        }
        getExcerpt(data) {
            const setting = data || this.get('mod_settings'),
                excerpt = setting.content_text || setting.content_box || setting.plain_text || '';
            return this.limitString(excerpt, 100);
        }
        getImage(data) {
            const setting = data || this.get('mod_settings'),
                imgUrl=setting.url_image || setting.url_image_a;
            return imgUrl?'<img src="'+imgUrl+'" loading="lazy" decode="async" width="16" height="16" alt="'+this.getName()+'"/>':api.Helper.getIcon('ti-' + this.getIcon()).outerHTML;
        }
        limitString(str, limit) {
            let new_str = '';
            if (str !== '') {
                const tmp = doc.createElement('div');
                tmp.innerHTML = str;
                str = tmp.textContent; // strip html tags
                new_str = str.length > limit ? str.substr(0, limit) : str;
            }
            return new_str;
        }
        backendLivePreview(settings) {
            this.el.tfClass('module_excerpt')[0].textContent=this.getExcerpt(settings);
            this.el.tfClass('tb_img_wrap')[0].innerHTML=this.getImage(settings);
        }
        getIcon(){
            const slug=this.get('mod_name');
            return themifyBuilder.modules[slug]!==und?themifyBuilder.modules[slug].icon:'';
        }
        getName(){
            const slug=this.get('mod_name');
            return themifyBuilder.modules[slug]!==und?themifyBuilder.modules[slug].name:slug;
        }
        getPreviewType(){
            return doc.tfId('tmpl-builder-' + this.get('mod_name'))!==null?'live':'ajax';
        }
        // for instant live preview
        getPreviewSettings() {
            return api.Module.getDefault(this.get('mod_name'));
        }
        attributes() {
            const args = {
                    class: 'active_module'
                },
                data = this.get('mod_settings');
            if (api.mode === 'visual') {
                if ((data.visibility_all === 'hide_all' || data.visibility_desktop === 'hide' || data.visibility_tablet === 'hide' || data.visibility_tablet_landscape === 'hide' || data.visibility_mobile === 'hide')) {
                    args.class += ' tb_visibility_hidden';
                }
                args.class += ' tb_module_front';
            }
            if (data.custom_css_id !== und && data.custom_css_id !== '') {
                args.id = data.custom_css_id;
            }
            return args;
        }
        getDisabledTpl(){
            this.isEmpty=true;
            api.Builder.get().emptyModules.add(this.get('mod_name'));
            const tpl=api.template('builder_module_disabled');
            this.el.innerHTML = tpl(this.toRenderData());
        }
        render() {
            if (api.mode !== 'visual') {
                if(themifyBuilder.modules[this.get('mod_name')]!==und){
                    this.el.innerHTML = api.Module.template(this.toRenderData());
                    this.visibilityLabel();
                }
                else{
                    this.getDisabledTpl();
                }
            }
            return this;
        }
    };
    api.Module.template = api.mode === 'visual' ? null : api.template('builder_module_item');
    api.Module.getDefault=slug=>{
        return api.Helper.cloneObject(themifyBuilder.modules[slug].defaults);
    };
    
    api.Builder=class {
        constructor(el, rows,customCss) {
            this.emptyModules=new Set();
            const cl=el.classList;
            cl.remove('not_editable_builder');
            cl.add('tb_active_builder','tf_rel');
            this.id=el.dataset.postid;
            el.id='themify_builder_content-' + this.id;
            this.el = el;
            this.isSaved=false;
            this.customCss=customCss || '';
            ++api.Builder.index;
            api.Builder.items.push(this);
            const fr = doc.createDocumentFragment();
            for (let i = 0, len = rows.length; i < len; ++i) {
                let r = new api.Row(rows[i]);
                fr.appendChild(r.el);
            }
            if(api.mode==='visual'){
                let css_id='tb_custom_css_'+this.id,
                    builderCss=doc.tfId(css_id);
                if(builderCss===null){
                    builderCss = doc.createElement('style');
                    builderCss.id = css_id;
                    doc.head.appendChild(builderCss);
                }
                builderCss.innerHTML = this.customCss;
            }
            this.el.appendChild(fr);
            api.Registry.on(this, 'tb_init', this.init);
            
        }
        init() {
            if (api.mode === 'visual') {
                setTimeout(() => {
                    api.Utils._onResize(true);
                }, 3000);
            }
            setTimeout(() => {
                api.Utils.setCompactMode(this.el.tfClass('module_column'));
               
                if (api.mode !== 'visual') {
                    api.GS.init();
                }
                this.lastRowBlock();
                this.newRowAvailable();
                this.insertLayoutButton();
                api.pageBreakModule.countModules();
                if(this.emptyModules.size>0){
                    TF_Notification.showHide('warning',themifyBuilder.i18n.empty_modules.replace('%s',Array.from(this.emptyModules).join(', ')),10000);
                }
                this.emptyModules.clear();
                this.emptyModules=null;
            }, 1000);
            
        }
        destroy(){
            if(this.emptyModules!==null){
                this.emptyModules.clear();
                this.emptyModules=null;
            }
            const items = this.el.querySelectorAll('[data-cid]'),
                cl=this.el.classList,
                builderItems=api.Builder.items,
                builderCss=doc.tfId('tb_custom_css_'+this.id);
            api.Registry.off(this);
            for (let i = items.length-1; i>-1; --i) {
                let m=api.Registry.get(items[i].dataset.cid);
                m.destroy(true);
            }
            for(let i=builderItems.length-1;i>-1;--i){
                if(builderItems[i]===this){
                    builderItems[i]=null;
                    builderItems.splice(i,1);
                    --api.Builder.index;
                    break;
                }
            }
            while(this.el.firstChild!==null){
                this.el.lastChild.remove();
            }
            this.el.removeAttribute('id');
            cl.remove('tb_active_builder','tf_rel');
            cl.add('not_editable_builder');
            if(builderCss!==null){
                builderCss.remove();
            }
        }
        toJSON(saving) {
            const option_data = [],
                rows = this.el.children,
                checkNotEmpty=cols=>{
                    for(let i=cols.length-1;i>-1;--i){
                        if(cols[i].styling && Object.keys(cols[i].styling).length>0){
                            return true;
                        }
                        let modules=cols[i].modules;
                        if(modules && modules.length>0){
                           for(let j=modules.length-1;j>-1;--j){
                               if(modules[j].cols){
                                   if(checkNotEmpty(modules[j].cols)){
                                       return true;
                                   }
                               }
                               else{
                                   return true;
                               }
                           }
                        }
                    }
                    return false;
                };
            for (let i = 0, len = rows.length; i < len; ++i) {
                if (rows[i].classList.contains('module_row')) {
                    let data = api.Utils._getRowSettings(rows[i], 'row', saving);
                    if ((data.styling && Object.keys(data.styling).length>0) || (data.cols && checkNotEmpty(data.cols))) {
                        option_data.push(data);
                    }
                }
            }
            return option_data;
        }
        removeLayoutButton() {
            const importBtn = this.el.tfClass('tb_import_layout_button');
            for (let i = importBtn.length - 1; i > -1; --i) {
                importBtn[i].remove();
            }
        }
        insertLayoutButton() {
            if (api.isGSPage !== true) {
                this.removeLayoutButton();
                const row = this.el.tfClass('module_row');
                if (row[0]!== und && row.length < 2 && row[0].classList.contains('tb_row_empty')) {
                    const importBtn = doc.createElement('a');
                    importBtn.className = 'tb_import_layout_button';
                    importBtn.textContent = themifyBuilder.i18n.text_import_layout_button;
                    importBtn.tfOn(Themify.click, e => {
                        e.stopPropagation();
                        Themify.triggerEvent(api.ToolBar.el.tfClass('load_layout')[0],e.type);
                    },{passive:true})
                    .href = 'javascript:;';
                    this.lastRow.getRootNode().host.before(importBtn);
                }  
            } 
        }
        newRowAvailable(ignore) {
            if (api.isGSPage === true) {
                return;
            }   
            const child = this.el.children,
                len = ignore===true?0:child.length;
            let row;
            if (len !== 0) {
                for (let i = len - 1; i > -1; --i) {
                    if (child[i].tfClass('active_module')[0]===und && child[i].classList.contains('module_row')) {
                        row=api.Registry.get(child[i].dataset.cid);
                        break;
                    }
                }
            }
            if (!row) {
                row = new api.Row(api.Utils.grid(1)[0]);
                row.el.className += ' tb_new_row';
                this.lastRow.getRootNode().host.before(row.el);
                api.Utils.setCompactMode(row.el.tfClass('module_column'));
            }
            return row;
        }
        lastRowBlock(){
            if(api.isGSPage !== true && (!this.lastRow || !this.el.contains(this.lastRow.getRootNode().host))){
            
                const root = doc.createElement('div'),
                    tpl=doc.tfId('tmpl-last_row_add_btn');
                    root.id='tb_last_row_add_btn';
                    root.className='tf_w tf_hidden';
                    root.attachShadow({
                        mode:'open'
                    }).appendChild(tpl.content.cloneNode(true));
                    this.el.appendChild(root);
                    this.lastRow = root.shadowRoot.tfId('container');
                    
                Themify.on('tb_toolbar_loaded', ()=>{
                   setTimeout(()=>{
                        const fragment=doc.createDocumentFragment();
                        
                            fragment.append(api.ToolBar.getBaseCss(),api.MainPanel.el.getRootNode().querySelector('#module_drag_grids_style').cloneNode(true));
                            root.shadowRoot.prepend(fragment);
                            root.classList.remove('tf_hidden');
                            
                            this.lastRow.tfOn(Themify.click, function(e) {
                                e.stopPropagation();
                                const target = e.target,
                                    grid = target.closest('.tb_grid');
                                if (grid !== null) {
                                    this.classList.add('hide');
                                    api.MainPanel.newGrid(grid.dataset.slug,false);
                                } 
                                else if (target.closest('.block')) {
                                    const host=this.getRootNode().host;
                                    host.classList.remove('clicked');
                                    this.classList.add('hide');
                                    api.SmallPanel.show(host);
                                } 
                                else if (target.classList.contains('add_btn')) {
                                    this.classList.remove('hide');
                                    if(!this.tfClass('grids')[0]){
                                        this.appendChild(doc.tfId('tmpl-last_row_expand').content);
                                    }
                                }
                            },{passive:true});
                            
                    },1500);

                },true,(api.ToolBar!==und && api.ToolBar.isLoaded===true));
            }
        }
        reLoad(json,merge) {
            return new Promise(async resolve=>{
                await api.LightBox.save();
                const id=this.id,
                    builder=this.el;
                let data = json.builder_data !== und ? json.builder_data : json,
                    customCss=json.custom_css || '',
                    isMainBuilder=api.mode!=='visual' || builder.parentNode.closest('.themify_builder')===null;//is main builder
                    data=api.Helper.correctBuilderData(data);
                if(merge===true){
                    data= this.toJSON().concat(data);
                    customCss=this.customCss+customCss;
                }
                else if(isMainBuilder===true){
                    api.GS.reset();
                }
                
                
                if (json.used_gs !== und) {
                    //gs data in old versions save as nested array
                    for(let i in json.used_gs){
                        let gs=json.used_gs[i];
                        if(gs.data!==und){
                            if(gs.data[0]===und){
                                let type=gs.type,
                                    st=api.Helper.cloneObject(gs.data),
                                    uniqId=api.Helper.generateUniqueID();
                                    gs.data=[{
                                        element_id:'row'+uniqId
                                    }];
                                if(type!=='row' && type!=='subrow'){
                                    gs.data[0].cols=[{
                                        element_id: 'col'+uniqId
                                    }];
                                    if(type==='column' || type==='subcolumn'){
                                        gs.data[0].cols[0].styling=st;
                                    }
                                    else{
                                       gs.data[0].cols[0].modules=[{
                                           mod_name:type,
                                           mod_settings:st
                                       }];
                                    }
                                }
                                else{
                                    gs.data[0].styling=st;
                                }
                            }
                        }
                        else{
                            delete json.used_gs[i];
                        }
                    }
                    api.GS.styles = ThemifyStyles.extend(true, {}, json.used_gs, api.GS.styles);
                }
                
                if (isMainBuilder===true && api.mode==='visual') {
                    api.liveStylingInstance.reset();
                    doc.body.classList.add('sidebar-none', 'full_width');
                    for(let items=doc.querySelectorAll('#sidebar,.page-title'),i=items.length-1;i>-1;--i){
                        items[i].remove();
                    }
                }
                this.destroy();
                const newBuilder = new api.Builder(builder,api.Helper.clear(data),customCss);
                api.undoManager.reset();
                
                let settings;
                if (isMainBuilder===false) {
                    settings = [];
                    const items = newBuilder.el.querySelectorAll('[data-cid]');
                    for (let i = items.length-1; i>-1; --i) {
                        settings.push(items[i].dataset.cid);
                    }
                }
                if (api.mode === 'visual') {
                    await api.bootstrap(settings, json.used_gs);
                    api.setCss(newBuilder.toJSON());
                }
                await api.correctColumnPaddings();
                api.Registry.trigger(newBuilder,'tb_init');
                api.Utils.runJs(newBuilder.el, null, true);
                api.Spinner.showLoader('done');
                resolve();
            });
        }
        async save() {
			const saveBtnCl=api.ToolBar.el.tfClass('save_wrap')[0].classList;
            try{
                if(saveBtnCl.contains('disabled')){
                    throw 'isWorking';
                }
                saveBtnCl.add('disabled');
                await api.LightBox.save();

                api.Spinner.showLoader();
				const allImages=api.Utils.getAllImages();
                try{//if there is an error don't break builder saving
                    await api.Utils.importThemifyImages(allImages.get('themify'));//upload images and change urls in the settings
                }
                catch(e){
                    
                }
                const id = this.id,
                    data = this.toJSON(true),
                    customCss=this.customCss || '',
                    checkOldPadding=data=>{
                        /*In admin part if there is an old padding value,we can't convert it,
                        because we don't know width/height of columns/rows,we need frontend to do it. 
                        That is why if there is an old value we will skip css generation and after saving,css file will be generated automatically(because css file will not exist).
                        */
                        if(api.mode!=='visual'){
                            const check=styling=>{
                                const colPaddingsIds=['padding_top','padding_bottom','padding_left','padding_right','margin-bottom','margin-top'],
                                    paddingLen=colPaddingsIds.length,
                                    points = api.breakpointsReverse,
                                    bpLength = points.length;
                                for(let i=paddingLen-1;i>-1;--i){
                                    if(styling[colPaddingsIds[i]+'_unit']==='%' && styling[colPaddingsIds[i]]!=='' && styling[colPaddingsIds[i]]!==und && styling[colPaddingsIds[i]].toString().indexOf(',')===-1){ 
                                        return true;
                                    }
                                    for (let j = bpLength - 2; j>-1; --j) {
                                        if(styling['breakpoint_'+points[j]]){
                                            let p=styling['breakpoint_'+points[j]][colPaddingsIds[i]];
                                            if(p!=='' && p!==und && p.toString().indexOf(',')===-1 && ThemifyStyles.getStyleVal(colPaddingsIds[i]+'_unit',styling,points[j])==='%'){
                                                return true;
                                            }
                                        }
                                    }
                                }
                            };
                            for (let i=data.length-1;i>-1;--i) {
                                let row = data[i];
                                if (row.cols !== und) {
                                    for (let j in row.cols) {
                                        let col = row.cols[j];
                                        if (col.styling !== und && check(col.styling)) {
                                           return false;
                                        }
                                        if (col.modules !== und) {
                                            for (let m in col.modules) {
                                                let mod = col.modules[m];
                                                if (mod && mod.cols !== und) {
                                                   for(let n in mod.cols){
                                                        let subcol = mod.cols[n];
                                                        if (subcol.styling !== und && check(subcol.styling)) {
                                                           return false;
                                                        }
                                                   }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        return true;
                    };
                let res,
                    localImages=allImages.get('local');
                    localImages=localImages.size>0?JSON.stringify(Array.from(localImages)):'';
                    await api.GS.setImport(api.GS.styles, null, true);
               
                const ajaxData={
                    data:JSON.stringify(api.Helper.clear(data)),
                    sourceEditor:('visual' === api.mode ? 'frontend' : 'backend'),
                    action:'tb_save_data',
                    images:localImages,
                    custom_css:customCss
                };
                try{
                    res=await api.LocalFetch(ajaxData);
                    if (!res.success) {
                        throw res;
                    }
                }
                catch(e){
                    try{
                        /* new attempt: send the Builder data as binary file to server */
                        ajaxData.data=new Blob( [ ajaxData.data ], { type: 'application/json' });
                        res=await api.LocalFetch(ajaxData);
                        if (!res.success) {
                            throw res;
                        }
                    }
                    catch(e){
                        await Promise.all([api.Spinner.showLoader('error'),TF_Notification.showHide('error',themifyBuilder.i18n.errorSaveBuilder,5000)]);
                        throw e;
                    }
                }
                res=res.data;
                const savedCss=await api.Utils.saveCss(data, customCss, id,localImages,!checkOldPadding(data));
                res.css_file=savedCss.css_file;
                api.Spinner.showLoader('done');
                Themify.trigger('themify_builder_save_data', res);
                this.isSaved=true;
                return res;
            }
            catch(e){
               throw e;
            }
			finally{
                saveBtnCl.remove('disabled');
			}
        }
    };
    
    api.Builder.items=[];
    api.Builder.index=-1;
    api.Builder.get=index=>{
        if (!index && index !== 0) {
            index = api.Builder.index;
        }
        return api.Builder.items[index];
    };


    api.pageBreakModule=  {
        countModules() {
            const isVisual = api.mode === 'visual',
                builder=api.Builder.get().el,
                modules = isVisual ? builder.tfClass('module-page-break') : builder.tfClass('tb-page-break');
            for (let i = modules.length - 1; i > -1; --i) {
                if (isVisual === true) {
                    modules[i].tfClass('page-break-order')[0].textContent = (i + 1);
                } else {
                    modules[i].tfClass('page-break-overlay')[0].textContent = 'PAGE BREAK - ' + (i + 1);
                }
            }
        },
        get() {
            return [{
                cols: [{
                    grid_class: 'col-full',
                    modules: [{
                        mod_name: 'page-break'
                    }]
                }],
                styling: {
                    custom_css_row: 'tb-page-break'
                }
            }];
        }
    };

    
    api.Utils = {
        onResizeEvents:new Set(),
        _onResize(trigger) {
            return new Promise(resolve=>{
                let events = $._data(win, 'events');
                if (events) {
                    events = events.resize;
                }
                if (events) {
                    for (let i = 0, len = events.length; i < len; ++i) {
                        if (events[i].handler !== und) {
                            this.onResizeEvents.add(events[i].handler);
                        }
                    }
                }
                $(win).off('resize');
                if (trigger) {
                    const e = $.Event('resize', {
                        type: 'resize',
                        isTrigger: false
                    });
                    for (let handler of this.onResizeEvents) {
                        try {
                            handler.apply(win, [e, $]);
                             
                        }catch (e) {
                            
                        }
                    }
                    Themify.triggerEvent(win,'resize');
                    Themify.trigger('tfsmartresize', {w: Themify.w, h: Themify.h});
                }
                resolve();
            }); 
        },
        _updateDocumentSize() {
            if (api.mode === 'visual') {
                let req,
                    timeout;
                (new ResizeObserver(entries => {
                    if (api.activeBreakPoint !== 'desktop') {
                        if(timeout){
                            clearTimeout(timeout);
                        }
                        timeout=setTimeout(() => {
                            const body=entries[0].target;
                                if (req) {
                                    cancelAnimationFrame(req);
                                }
                                req = requestAnimationFrame(() => {
                                        topWindow.document.body.style.height = body.scrollHeight + 'px';
                                    setTimeout(() => {
                                        this._onResize(true);
                                        topWindow.document.body.style.height = body.scrollHeight + 'px';
                                        timeout=req=null;
                                    }, 220);
                                });
                        }, 60);
                    }
                })).observe(doc.body);
            }
        },
        findCssRule(rules, selector) {
            selector = selector.replace(/\s*>\s*/g, '>').replace(/\,\s/g, ',');
            const isCondition=selector[0]==='@';
            for (let i = rules.length - 1; i > -1; --i) {
                if ((isCondition===true && rules[i].conditionText && rules[i].cssText.replace(/\s*>\s*/g, '>').replace(/\,\s/g, ',').indexOf(selector)!==-1) || (isCondition===false && !rules[i].conditionText && selector === rules[i].selectorText.replace(/\s*>\s*/g, '>').replace(/\,\s/g, ','))) {
                    return i;
                } 
            }
            return false;
        },
        filterClass(cl) {
            const _COL_CLASSES_VALUES=api.getColClassValues();
            for (let i = cl.length - 1; i > -1; --i) {
                if (_COL_CLASSES_VALUES.indexOf(cl[i]) !== -1) {
                    return cl[i];
                }
            }
            return '';
        },
        _getRowSettings(base, type, saving) {
            type = type || 'row';
            saving = !!saving;
            let option_data = {},
                styling;
            const model_r = api.Registry.get(base.dataset.cid);
            if (model_r) {
                const inner = base.tfClass(type + '_inner')[0],
                    count = inner.childElementCount,
                    points = api.breakpointsReverse,
                    bpLength = points.length,
                    cols = [],
                    colPaddingsIds=['padding_top','padding_bottom','padding_left','padding_right','margin-bottom','margin-top'],
                    paddingLen=colPaddingsIds.length;

                // cols
                for (let i = 0, columns = inner.children; i < count; ++i) {
                    if (columns[i].classList.contains('module_column')) {
                        let model_c = api.Registry.get(columns[i].dataset.cid);
                        if (model_c) {
                            let modules = columns[i].tfClass('tb_holder')[0],
                                cl = this.filterClass(columns[i].classList),
                                index = cols.push({
                                    element_id: model_c.id
                                });
                            --index;

                            if (cl !== '') { //backward compatibility
                                cols[index].grid_class = cl;
                            }
                            styling = api.Helper.cloneObject(model_c.get('styling'));
                            if (styling && Object.keys(styling).length > 0) {
                                if(saving===true && api.mode==='visual'){//in admin part we can't use iframe to convert paddings that is why skipping
                                    //we need always save padding/margin units as 2 "value1,value2"(when the unit is %) to detect the data is converted
                                    for(let j=paddingLen-1;j>-1;--j){
                                        let prop=colPaddingsIds[j];
                                        if(styling[prop+'_unit']==='%' && styling[prop]!=='' && styling[prop]!==und && styling[prop].toString().indexOf(',')===-1){
                                            styling[prop]=','+styling[prop];
                                        }
                                        for (let k = bpLength - 2; k>-1; --k) {
                                            if(styling['breakpoint_'+points[k]]!==und){
                                                let p=styling['breakpoint_'+points[k]][prop];
                                                if(p!=='' && p!==und && p.toString().indexOf(',')===-1 && ThemifyStyles.getStyleVal(prop+'_unit',styling,points[k])==='%'){
                                                    styling['breakpoint_'+points[k]][prop]=','+p;
                                                }
                                            }
                                        }
                                    }
                                }
                                cols[index].styling = styling;
                            }
                            if (modules !== und) {
                                modules = modules.children;
                                let items = [];
                                for (let j = 0, clen = modules.length; j < clen; ++j) {
                                    let m = api.Registry.get(modules[j].dataset.cid),
                                        mname=m?m.get('mod_name'):null;
                                    if (mname) {
                                        styling = api.Helper.cloneObject(m.get('mod_settings'));
                                        let k = items.push({
                                            mod_name:mname,
                                            element_id: m.id
                                        });
                                        --k;
                                        if (styling && Object.keys(styling).length > 0) {
                                            delete styling.cid;
                                            items[k].mod_settings = styling;
                                        }
                                        // Sub Rows
                                        if (modules[j].classList.contains('active_subrow')) {
                                            items[k] = this._getRowSettings(modules[j], 'subrow', saving);
                                        }
                                    }
                                }
                                if (items.length > 0) {
                                    cols[index].modules = items;
                                }
                            }
                        }
                    }
                }
                option_data = {
                    element_id: model_r.id,
                    cols: cols
                };
                
                let sizes = Object.assign({},model_r.get('sizes'));
                if (count>1) {
                    for (let i = bpLength-1;i>-1;--i) {//make equal
                        let bp = points[i],
                            size = sizes[bp + '_size'],
                            area = sizes[bp + '_area'],
                            colh = sizes[bp + '_auto_h'];
                        
                        if (size) {
                            size=ThemifyStyles.getColSize(size,false);
                            if (size.indexOf(' ') !== -1) {
                                size = size.replace(/\s\s+/g, ' ').split(' ');
                                for (let j = size.length - 1; j > -1; --j) {
                                    let fr = parseFloat(size[j].trim());
                                    if (fr !== 1) {
                                        size[j] = size[j].replace(fr.toString(), parseFloat(fr.toFixed(5)).toString());
                                    }
                                }
                                size = size.join(' ').replaceAll('0.', '.').trim();
                            }
                            sizes[bp + '_size']=size;
                        }
                        if (area) {
                            if(bp==='desktop'){
                                delete sizes[bp + '_area'];
                            }
                            else{
                                if(area.indexOf(' ')!==-1){
                                    area=area.replaceAll('col', '').replace(/\s\s+/g, ' ').trim();
                                    sizes[bp + '_area']= area;
                                    if(size && size.indexOf(' ') === -1){
                                        let checkArea=model_r.getGridCss({size:size},bp);
                                        if(checkArea['--area'] && checkArea['--area'].replaceAll('col', '').replace(/\s\s+/g, ' ').trim()===area){
                                           delete sizes[bp + '_area'];
                                        }
                                    }
                                }
                                else if(!ThemifyStyles.getAreaValue(area)){
                                    delete sizes[bp + '_area'];
                                }
                            }
                        }
                        if (colh) {
                            sizes[bp + '_auto_h'] = parseInt(colh);
                        }
                        if (saving === true && sizes[bp + '_dir']!==und) {//backward
							delete sizes[bp + '_dir'];
                        } 
                    }
                    for (let i = 0; i < bpLength - 1; ++i) { //clean again duplicates
                        let bp = points[i],
                            gutter = sizes[bp + '_gutter'],
                            colh = sizes[bp + '_auto_h'],
                            size = sizes[bp + '_size'],
                            align = sizes[bp + '_align'];
                            
                        if (gutter || align || colh || size) {
                            for (let j = i + 1; j < bpLength; ++j) {
                                let bp2 = points[j];
                                if (gutter && sizes[bp2 + '_gutter']) {
                                    if (sizes[bp2 + '_gutter'] === gutter) {
                                        delete sizes[bp + '_gutter'];
                                    }
                                    gutter = null;
                                }
                                if (align && sizes[bp2 + '_align']) {
                                    if (sizes[bp2 + '_align'] === align) {
                                        delete sizes[bp + '_align'];
                                    }
                                    align = null;
                                }
                                if (colh && sizes[bp2 + '_auto_h']) {
                                    if (sizes[bp2 + '_auto_h'] === colh) {
                                        delete sizes[bp + '_auto_h'];
                                    }
                                    colh = null;
                                }
                                if (size && sizes[bp2 + '_size']) {
                                    if (saving === true && sizes[bp2 + '_size'] === size) {
                                        delete sizes[bp + '_size'];
                                    }
                                    size = null;
                                }
                                if (!gutter && !align &&  !colh && !size) {
                                    break;
                                }
                            }
                        }
                    }
                    
                    if (sizes.desktop_area) {
                        const area = [];
                        for (let i = 0; i < count; ++i) {
                            area.push(i + 1);
                        }
                        if (area.join(' ') === sizes.desktop_area) {
                            delete sizes.desktop_area;
                        }
                    }
                    //backward
                    if ((sizes.mobile_dir !== und && (!sizes.desktop_dir || sizes.desktop_dir === sizes.tablet_landscape_dir) && sizes.tablet_dir === sizes.mobile_dir && sizes.tablet_landscape_dir === sizes.mobile_dir)) {
                        delete sizes.desktop_dir;
                        delete sizes.tablet_landscape_dir;
                        delete sizes.tablet_dir;
                        delete sizes.mobile_dir;
                    }
                    else if (sizes.desktop_dir === 'ltr') {
                        delete sizes.desktop_dir;
                    }
                    if (sizes.desktop_auto_h === -1) {
                        delete sizes.desktop_auto_h;
                    }
                    if (sizes.desktop_align === 'start' && isFullSection===false) {
                        delete sizes.desktop_align;
                    }
                    if (sizes.desktop_gutter === 'gutter') {
                        delete sizes.desktop_gutter;
                    }
                    for (let i in sizes) {
                        if (sizes[i] === und || sizes[i] === '') {
                            delete sizes[i];
                        }
                    }
                }
                else{
                    sizes={};
                } 
                option_data.sizes = sizes;
                styling = api.Helper.cloneObject(model_r.get('styling'));
                if (styling && Object.keys(styling).length > 0) {
                    delete styling.cid;
                    option_data.styling = styling;
                }
            }
            return option_data;
        },
        getAllImages(type){
            const items=api.Registry.items,
            images=new Map(),
            localImages=new Set(),
            externalImages=new Set(),
            themifyImages=new Set(),
            addImage=url=>{
                if(api.Helper.isImageUrl(url)){
                    if(url.indexOf(Themify.urlHost)!==-1){
                        localImages.add(url);
                    }
                    else if(url.indexOf('/themify.me/')!==-1){
                        themifyImages.add(url);
                    }
                    else{
                        externalImages.add(url);
                    }
                }
            },
            getImages=fields=>{
                for(let i in fields){
                    if(fields[i]){
                        if(Array.isArray(fields[i]) || typeof fields[i]==='object'){
                            getImages(fields[i]);
                        }
                        else{
                            let v=fields[i].toString().trim();
                            if(v){
                                if(v.indexOf('<img ')!==-1){
                                    let tmp=doc.createElement('template');
                                    tmp.innerHTML=v;
                                    let allImages= tmp.content.querySelectorAll('img');
                                    for(let j=allImages.length-1;j>-1;--j){
                                        let src=allImages[j].src,
                                            srcset=allImages[j].srcset;
                                        srcset=srcset?srcset.split(' '):[];
                                        if(src){
                                            srcset.push(src);
                                        }
                                        for(let k=srcset.length-1;k>-1;--k){
                                            if(srcset[k]){
                                                addImage(srcset[k].trim());
                                            }
                                        }
                                    }
                                }
                                else if(v[0]==='[' && v.indexOf('path=')!==-1){
                                    let m=v.match(/\path.*?=.*?[\'"](.+?)[\'"]/igm); 
                                    if(m && m[0]){
                                        m=m[0].split('path=')[1].replaceAll('"','').replace("'",'').split(',');
                                        for(let j=m.length-1;j>-1;--j){
                                            if(m[j]){
                                                addImage(m[j].trim());
                                            }
                                        }
                                    }
                                }
                                else{
                                    addImage(v);
                                }
                            }
                        }
                    }
                }
            };
            if(Themify.urlHost!=='themify.me'){
                for(let [k,v] of items){
                    if(v.el.isConnected){
                        getImages(v.get('styling'));
                    }
                }
                const domImages=api.Builder.get().el.tfTag('img');
                for(let i=domImages.length-1;i>-1;--i){
                    let src=domImages[i].src,
                            srcset=domImages[i].srcset;
                        srcset=srcset?srcset.split(' '):[];
                        if(src){
                            srcset.push(src);
                        }
                        for(let j=srcset.length-1;j>-1;--j){
                            if(srcset[j]){
                                addImage(srcset[j].trim());
                            }
                        }
                }
            }
            images.set('themify',themifyImages);
            images.set('local',localImages);
            images.set('external',externalImages);
            return type?images.get(type):images;
        },
        async importThemifyImages(images){
            if(!images){
                images=this.getAllImages('themify');
            }
            if(images.size>0){
                return new Promise(async (resolve,reject)=>{
                    try{
                        await Themify.loadJs(Themify.url+'js/admin/import/import-images',!!win.TF_ImportImages);
                        const memory=parseInt(themifyBuilder.memory) || 64,
                        chunkSize=memory>=255?4:(memory>=120?3:(memory>60?2:1)),
                        res=await TF_ImportImages.init(images,themifyBuilder.nonce,themifyBuilder.i18n.uploading,chunkSize),
                        breakpoints=api.breakpointsReverse,
                        items=api.Registry.items,
                        setImages=fields=>{
                            for(let i in fields){
                                if(fields[i]===und){
                                    delete fields[i];
                                }
                                else if(fields[i]){
                                    if(Array.isArray(fields[i]) || typeof fields[i]==='object'){
                                        setImages(fields[i]);
                                    }
                                    else{
                                        let v=fields[i].toString().trim();
                                        if(v){
                                            if(v.indexOf('<img ')!==-1){
                                                let tmp=doc.createElement('template');
                                                tmp.innerHTML='<div>'+v+'</div>';
                                                let content=tmp.content.firstChild,
                                                    allImages= content.tfTag('img');
                                                for(let j=allImages.length-1;j>-1;--j){
                                                    let src=allImages[j].src;
                                                    if(src){
                                                        for(let [k,img] of res){
                                                            if(img!==false && src.indexOf(k)!==-1){
                                                                allImages[j].outerHTML=img.html;
                                                            }
                                                        }
                                                    }
                                                }
                                                fields[i]=content.innerHTML;
                                            }
                                            else if(isNaN(v) && (v[0]==='[' || v.indexOf(' ')===-1)){ 
                                                for(let [k,img] of res){
                                                    if(img!==false && v.indexOf(k)!==-1){ 
                                                        fields[i]=v.replaceAll(k,img.src);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        for(let [k,v] of res){
                            if(v!==false){
                                let domImages=doc.querySelectorAll('img[src="'+k+'"]');
                                for(let i=domImages.length-1;i>-1;--i){
                                    domImages[i].src=v.src;
                                    domImages[i].classList.add('wp-image-'+v.id);
                                }
                                if(api.mode==='visual'){
                                    for(let i=breakpoints.length-1;i>-1;--i){
                                        let bp=breakpoints[i],
                                            rules=ThemifyStyles.getSheet(bp).cssRules,
                                            gsRules=ThemifyStyles.getSheet(bp,true).cssRules;
                                        for(let j=rules.length-1;j>-1;--j){ 
                                            rules[j].style.cssText=rules[j].style.cssText.replaceAll(k,v.src);
                                        }
                                        for(let j=gsRules.length-1;j>-1;--j){
                                            rules[j].style.cssText=rules[j].style.cssText.replaceAll(k,v.src);
                                        }
                                    }
                                }
                            }
                        }
                        for(let [k,v] of items){
                            setImages(v.get('styling'));
                        }
                        TF_Notification.showHide('done','',100);
                        resolve(res);
                    }
                    catch(e){
                        reject(e);
                    }
                });
            }
        },
        grid(slug) {
            const cols = [],
            oldGrids=api.getColClass()[slug.toString()];
            let len=oldGrids===und?oldGrids.length:parseInt(slug);
            for (let i = 0; i < len; ++i) {
                let _c = oldGrids===und ? {} : {
                    grid_class:oldGrids[i]
                };
                cols.push(_c);
            }
            return [{
                cols: cols
            }];
        },
        setCompactMode(col) {
            const len = col.length;
            if (len > 0) {
                for (let i = len - 1; i > -1; --i) {
                    if(col[i]){
                        col[i].classList.toggle('compact-mode', col[i].clientWidth < 185);
                    }
                }
                const parentNode=col[0].parentNode,
                    cl = parentNode.classList,
                    realCount=parentNode.childElementCount;
                for (let i = cl.length - 1; i > -1; --i) {
                    if (cl[i].indexOf('tb_col_count_') === 0) {
                        cl.remove(cl[i]);
                        break;
                    }
                }
                cl.add('tb_col_count_' +realCount);
            }
        },
        async saveCss(data, customCss, id,images,deleteFile) {
            let css;
            const ajaxData={
                css:data && !deleteFile?JSON.stringify(api.GS.createCss(data, (data[0] && data[0].mod_name) || null, true)):'',
                action:'tb_save_css',
                custom_css:!deleteFile && customCss?customCss:'',
                bid:id,
                images:images || '',
                delete_css:deleteFile?1:''
            };
            try{
               css=await api.LocalFetch(ajaxData);
            }
            catch(e){
                try{
                    /* new attemp: compile CSS code into binary data and send that to server */
                    ajaxData.css=new Blob( [ ajaxData.css ], { type: 'application/json' });
                    css=await api.LocalFetch(ajaxData); 
                }
                catch(e){
                    throw e;
                }
            }
            return css;
        },
        async runJs(el, type, isAjax) {
            const promises = [];
            if (api.mode === 'visual') {
                if (!type) {
                    if (api.activeModel !== null) {
                        type = api.activeModel.type;
                    } 
                    else if (el) {
                        const m = api.Registry.get(el.dataset.cid);
                        if (m) {
                            type = m.type;
                        }
                    }
                }
                if (type === 'module' && api.is_builder_ready === true) {
                    Themify.fonts(el);
                }
                if (isAjax !== true) {
                    const d = el || doc,
                        images = d.querySelectorAll('img[data-w]:not(.tf_large_img)'),
                        len=images.length,
                        max=Themify.isTouch?4:8,
                        waitMls=Themify.isTouch?20:5;
                    for (let i = len - 1; i > -1; --i) {
                        if (images[i].naturalWidth > 2560 || images[i].naturalHeight > 2560) {
                            images[i].className += ' tf_large_img';
                            Themify.largeImages(images[i]);
                        }
                        else {
                            let w = images[i].getAttribute('width'),
                                h = images[i].getAttribute('height');
                            if (w || h) {
                                if(len>max){
                                    let p=new Promise((resolve,reject)=>{
                                        setTimeout(()=>{
                                            ThemifyImageResize.toBlob(images[i], w, h).then(resolve).catch(reject);
                                        },i*waitMls);
                                    });
                                    promises.push(p);
                                }
                                else{
                                    promises.push(ThemifyImageResize.toBlob(images[i], w, h));
                                }
                            }
                        }
                    }
                }
            }
			try{
				await Promise.all(promises);
			}
			catch(e){
				
			}
			if (el && win.Isotope) {
				const masonry = Themify.selectWithParent('masonry-done', el);
				for (let i = masonry.length - 1; i > -1; --i) {
					let m = Isotope.data(masonry[i]);
					if (m) {
						m.destroy();
					}
					masonry[i].classList.remove('masonry-done');
				}
			}
			return Themify.reRun(el); // load module js ajax
        },
        // get breakpoint width
        getBPWidth(device) {
            const breakpoints = Array.isArray(themifyBuilder.breakpoints[device]) ? themifyBuilder.breakpoints[device] : themifyBuilder.breakpoints[device].toString().split('-');
            return breakpoints[breakpoints.length - 1];
        },
        scrollTo(el,offset,opt) {
            if (!el) {
                return;
            }
            if(!offset){
                el.scrollIntoView(opt);
            }
            else{
                if(!opt){
                    opt={};
                }
                opt.top=el.getBoundingClientRect().top-win.document.body.getBoundingClientRect().top-offset;
                win.scrollTo(opt);
            }
        },
        addViewPortClass(el) {
            el.style.transition = 'none';
            this.removeViewPortClass(el);
            for (let cl = this.isInViewport(el), i = cl.length - 1; i > -1; --i) {
                el.classList.add(cl[i]);
            }
            el.style.transition = '';
        },
        removeViewPortClass(el) {
            const removeCl = ['top', 'left', 'bottom', 'right'];
            for (let i = 4; i > -1; --i) {
                el.classList.remove('tb_touch_' + removeCl[i]);
            }
        },
        isInViewport(el) {
            const offset = el.getBoundingClientRect(),
                cl = [];
            if (offset.left < 0) {
                cl.push('tb_touch_left');
            } else if (offset.right - 1 >= doc.documentElement.clientWidth) {
                cl.push('tb_touch_right');
            }
            if (offset.top < 0) {
                cl.push('tb_touch_top');
            }
            else if(((offset.bottom+ 1) >= doc.documentElement.clientHeight) || ((win.innerHeight + win.scrollY) >= doc.body.offsetHeight && (offset.bottom + 20) >= doc.documentElement.clientHeight)) {
                cl.push('tb_touch_bottom');
            }
            return cl;
        }
    };
    
    
    //hack to detect the row_inner/subro_inner width to convert old padding/margin(in percent only) of cols to new grid padding/margin pergin percent(https://www.w3.org/TR/css3-grid-layout/#item-margins)
    api.correctColumnPaddings=()=>{
        return new Promise(resolve=>{
            if(api.mode!=='visual'){
                resolve();
                return;
            }
            const cols=api.Builder.get().el.tfClass('module_column'),
                len=cols.length;
            if(len>0){
                const colPaddingsIds=['padding_top','padding_bottom','padding_left','padding_right','margin-bottom','margin-top'],
                    points = api.breakpointsReverse,
                    bpLength = points.length,
                    elements={},
                    checkPaddings=(bpStyles,allStyles,bp)=>{
                        const found={};
                        for(let i=colPaddingsIds.length-1;i>-1;--i){
                            let prop=colPaddingsIds[i],
                                p=bpStyles[prop];
                            if(p!=='' && p!==und && p.toString().indexOf(',')===-1 && ThemifyStyles.getStyleVal(prop+'_unit',allStyles,bp)==='%'){
                                found[prop]=p;
                            }
                        }
                        return found;
                    };
                     
                    for(let i=cols.length-1;i>-1;--i){
                        let id=cols[i].dataset.cid,
                            model=api.Registry.get(id);
                        if(model){
                            let st =model.get('styling');
                            if(st!==und){
                                for (let j = bpLength - 1; j>-1; --j) {
                                    let bp=points[j],
                                        vals=bp==='desktop'?st:st['breakpoint_'+bp];
                                    if(vals){
                                        let found=checkPaddings(vals,st,bp);
                                        if(Object.keys(found).length>0){
                                            if(elements[bp]===und){
                                                elements[bp]={};
                                            }
                                            elements[bp][id]=found;
                                        }
                                    } 
                                }
								//duplicate css values in breakpoints,because if we have mobile 5% and tablet 5%, after converting they can be different value,because the row_inner/cols widths are different in mobile and tablet
								for (let j = bpLength - 2; j>-1; --j) {
									let bp=points[j];
									if(elements[bp]===und || elements[bp][id]===und){
										for (let k = j +1; k<bpLength; ++k) {
											if(elements[points[k]]!==und && elements[points[k]][id]!==und){
												if(elements[bp]===und){
													elements[bp]={};
												}
												elements[bp][id]=Object.assign({},elements[points[k]][id]);
												break;
											}
										}
									}
								}
                            }
                        }
                    }
											
                    if(Object.keys(elements).length===0){
                        resolve();
                        return;
                    }
                    //TF_Notification.show('info',themifyBuilder.i18n.convertingOldData);
                    let tmpIframe = doc.createElement('iframe');
                    tmpIframe.id = 'tb_regenerate_css_iframe';
                    tmpIframe.style.setProperty('position', 'fixed','important');
					tmpIframe.style.setProperty('top', '-100000000px','important');
					tmpIframe.style.setProperty('left', '-100000000px','important');
					tmpIframe.style.setProperty('visibility', 'hidden','important');
					tmpIframe.style.setProperty('min-width', 'auto','important');
					tmpIframe.style.setProperty('max-height', 'none','important');
					tmpIframe.style.setProperty('min-height', 'auto','important');
					tmpIframe.style.setProperty('contain', 'none','important');
					tmpIframe.style.setProperty('width', '100%','important');
					tmpIframe.style.setProperty('height', '100%','important');
					tmpIframe.style.setProperty('opacity', '0','important');
                    tmpIframe.src = 'about:blank';
                    tmpIframe.className = 'tb_iframe';
                    topWindow.document.body.appendChild(tmpIframe);
                    let iframeW = tmpIframe.contentWindow.document,
                        clone = doc.documentElement.cloneNode(true);
                    const allScripts = clone.querySelectorAll('script'),
                        breakpoints = api.breakpointsReverse;
                    for (let i = allScripts.length - 1; i > -1; --i) {
                        allScripts[i].remove();
                    }

                    iframeW.open();
                    iframeW.write('<!DOCTYPE html>'+clone.outerHTML);
                    for (let i = breakpoints.length - 1; i > -1; --i) {
                        let stName = ThemifyStyles.styleName + breakpoints[i],
                            rules = ThemifyStyles.getSheet(breakpoints[i]).cssRules,
                            globalRules = ThemifyStyles.getSheet(breakpoints[i], true).cssRules,
                            sheet = tmpIframe.contentWindow.document.querySelector('#' + stName).sheet,
                            globalSheet = tmpIframe.contentWindow.document.querySelector('#' + stName + '_global').sheet;
                        for (let j = 0, len = globalRules.length; j < len; ++j) {
                            globalSheet.insertRule(globalRules[j].cssText);
                        }
                        for (let j = 0, len = rules.length; j < len; ++j) {
                            sheet.insertRule(rules[j].cssText);
                        }
                    }
                    clone = null;
                    const stData = doc.createElement('style');
                    stData.textContent = 'html,body,body *,div,a{transition:none!important;animation:none!important;pointer-events:none!important}';
                    tmpIframe.contentWindow.document.body.appendChild(stData);
                    
                    tmpIframe.tfOn('load', function() {
                        iframeW = null;

                        const windowW = topWindow.innerWidth + 5,
                            cl = this.contentWindow.document.body.classList,
                            bpLength = breakpoints.length;
                        cl.add('tb_start_animate');

                        for (let i = bpLength - 1; i > -1; --i) {
                            let bp = breakpoints[i];
                            if (elements[bp] !== und) {
                                let iframeSt = 'max-width',
                                    width = bp === 'desktop' ? null : 1 * api.Utils.getBPWidth(bp),
                                    sheet=ThemifyStyles.getSheet(bp);
                                if (!width || width >= windowW) {
                                    iframeSt = 'min-width';
                                    if (!width) {
                                        width = api.Utils.getBPWidth('tablet_landscape') + 1;
                                        if (width < windowW) {
                                            width = '';
                                        }
                                    }
                                    if (width === windowW) {
                                        --width;
                                    }
                                }
                                cl.remove('builder-breakpoint-mobile', 'builder-breakpoint-tablet', 'builder-breakpoint-tablet_landscape');
                                if (bp !== 'desktop') {
                                    cl.add('tb_responsive_mode', 'builder-breakpoint-' + bp);
                                } else {
                                    cl.remove('tb_responsive_mode');
                                }
                                this.style.setProperty('max-width', 'none','important');
                                this.style.setProperty('min-width', 'auto','important');
                                if (width) {
                                    this.style.setProperty(iframeSt, width + 'px','important');
                                }

                                for (let elementId in elements[bp]) {
                                    let model=api.Registry.get(elementId),
                                        selector=ThemifyStyles.getBaseSelector(model.type, elementId),
                                        col = tmpIframe.contentWindow.document.querySelector(selector),
                                        styles=model.get('styling'),
                                        index = api.Utils.findCssRule(sheet.cssRules, selector),
                                        position=getComputedStyle(col).getPropertyValue('position');
                                        if(position==='absolute' || position==='fixed'){
                                            continue;
                                        }
                                        col.style.setProperty('padding', '0','important');
                                    let innerW = col.parentNode.getBoundingClientRect().width,
                                        colW = col.getBoundingClientRect().width;
                                    for (let propId in elements[bp][elementId]) {
                                        let value =elements[bp][elementId][propId],
                                            prop=propId.replace('_','-'),
                                            v = parseFloat(((parseFloat(value) / 100) * innerW) / colW) * 100;
                                            v = parseFloat(parseFloat(v.toFixed(2))).toString();
                                            if(bp==='desktop'){
                                                if(styles[propId]!==und && styles[propId].toString().indexOf(',')!==-1){
                                                    continue;
                                                }
												if(styles[propId]===und){
													styles[propId]='';
												}
                                                styles[propId]+=','+v;
                                            }
                                            else{
                                                if(styles['breakpoint_'+bp]!==und && styles['breakpoint_'+bp][propId]!==und && styles['breakpoint_'+bp][propId].toString().indexOf(',')!==-1){
                                                    continue;
                                                }
												if(styles['breakpoint_'+bp]===und){
													styles['breakpoint_'+bp]={};
												}
												if(styles['breakpoint_'+bp][propId]===und){
													styles['breakpoint_'+bp][propId]='';
												}
                                                styles['breakpoint_'+bp][propId]+=','+v;
                                            }
                                            if(propId[0]!=='p'){
                                                if(index!==false && sheet.cssRules[index]!==und){
                                                    sheet.cssRules[index].style.setProperty(prop, v+'%'); 
                                                }
                                                else{
                                                    sheet.insertRule(selector + '{'+prop+':'+ v+ '%}', sheet.cssRules.length);
                                                }
                                            }
                                            else{
                                                col.style.padding = '';
                                                let exist=index!==false && sheet.cssRules[index]!==und,
                                                    isAllChecked=exist===true?sheet.cssRules[index].style.getPropertyValue('padding'):tmpIframe.contentWindow.getComputedStyle(col).getPropertyValue('padding');
                                                col.style.setProperty('padding', '0','important');
                                                
                                                if(isAllChecked && elements[bp][elementId].padding_top!==und && isAllChecked.indexOf(' ')===-1){
                                                    v=elements[bp][elementId].padding_top;
                                                    v = parseFloat(((parseFloat(v) / 100) * innerW) / colW) * 100;
                                                    v = parseFloat(parseFloat(v.toFixed(2))).toString();
                                                    prop='padding';
                                                }
                                                if(exist===true){
                                                    sheet.cssRules[index].style.setProperty(prop, v+'%'); 
                                                }
                                                else{
                                                    sheet.insertRule(selector + '{'+prop+':'+ v+ '%}', sheet.cssRules.length);
                                                }
                                            }
                                            
                                            
                                    }
                                    model.set('styling',styles);
                                }
                            }
                        }
                        this.remove();
                        tmpIframe = null;   
                  //      TF_Notification.showHide('done',themifyBuilder.i18n.oldDataConverted);
                        resolve();
                    }, {
                        passive: true,
                        once: true
                    });
                    iframeW.close();
                    
            }
            else{
                resolve();
            }
        });
    };
    
    win.tfOn('offline', e => {
        setTimeout(()=>{
            if(!win.navigator.onLine && !api.Builder.get().isSaved && api.undoManager.hasUndo()){
                const saveBtnCl=api.ToolBar.el.tfClass('save_wrap')[0].classList,
                msgWrap=doc.createElement('div'),
                msg=themifyBuilder.i18n.offline,
                exportJson=e=>{
                    if(e.composedPath()[0].closest('a')){
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        e.currentTarget.tfOff(e.type,exportJson,{once:true});
                        let builder=api.Builder.get(),
                            json=builder.toJSON(true),
                            customCss=builder.customCss,
                            postTitle=themifyBuilder.post_title,
                            date=new Date(),
                            currentData=date.getFullYear()+'_'+date.getMonth()+'_'+date.getDate(),
                            usedGS=api.GS.findUsedItems(json),
                            donwload=(blob,name)=>{
                                let a=doc.createElement('a');
                                a.download = name;
                                a.rel = 'noopener';
                                a.href = URL.createObjectURL(blob);
                                setTimeout( ()=> { 
                                    URL.revokeObjectURL(a.href); 
                                    a=null;
                                },10000); 
                                a.click();
                                TF_Notification.showHide('warning',msgWrap,300);
                            };
                        json={builder_data:json};
                        if(customCss){
                            json.custom_css=customCss.trim();
                        }
                        if(usedGS){
                            const GS={};
                            for(let i=usedGS.length-1;i>-1;--i){
                                let gsItem=api.Helper.cloneObject(api.GS.styles[usedGS[i]]);
                                delete gsItem.id;
                                delete gsItem.url;
                                GS[usedGS[i]]=gsItem;
                            }
                            json.used_gs=GS;
                        }
                        json=JSON.stringify(json);
                        donwload(new Blob([json], {type: 'application/json'}),postTitle+'_themify_builder_export_'+currentData+'.txt');
                    }
                };
                
                
                msgWrap.innerHTML=msg;
                TF_Notification.show('warning',msgWrap);
                saveBtnCl.add('disabled');
                win.tfOn('online', e => {
                    saveBtnCl.remove('disabled');
                    TF_Notification.showHide('warning',msgWrap,300);
                    TF_Notification.el.tfOff(Themify.click,exportJson,{once:true});
                },{passive:true,once:true});
                
                TF_Notification.el.tfOn(Themify.click,exportJson,{once:true});
            }
        },3000);
    },{passive:true});

    
})(jQuery, Themify, window, window.top, document,undefined);