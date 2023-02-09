let TB_Layouts;
((api, Themify, doc) => {
    'use strict';
    let layoutIsSet=false,
    observer;
    const webp=doc.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') !== -1,
    initObserver=root=>{
        if(!observer){
            observer=new IntersectionObserver((entries, _self)=>{
                for (let i = entries.length - 1; i > -1; --i) {
                    if (entries[i].isIntersecting === true) {
                        _self.unobserve(entries[i].target);
                        let el=entries[i].target;
                        if(el && el.isConnected){
                            let imgPlaceholder=el.tfTag('img')[0],
                                src=imgPlaceholder.dataset.src,
                                thumb = new Image(imgPlaceholder.getAttribute('width'),imgPlaceholder.getAttribute('height')),
                                loader = doc.createElement('div');
                                loader.className = 'tf_loader tf_abs_c';
                                thumb.decoding = 'async';
                                thumb.src = src;
                                thumb.title = thumb.alt = imgPlaceholder.alt;
                                el.appendChild(loader);
                                thumb.decode()
                                .catch(()=>{})
                                .finally(() => {
                                    imgPlaceholder.replaceWith(thumb);
                                    loader.remove();
                                });
                        }
                    }
                }

            }, {
                root:root || null,
                threshold: .1
            }); 
        }
    };
    TB_Layouts ={
        el:null,
        init(target) {
            const cl=target.classList;
            if (cl.contains('load_layout') || cl.contains('save_layout')) {
                api.LightBox.save().then(()=>{
                    if (target.classList.contains('load_layout')) {
                        this.loadLayout(target);
                    } 
                    else {
                        this.saveLayout(target);
                    }
                })
                .catch(e=>{
                    api.Spinner.showLoader('spinhide');
                });
            }
        },
        initForm(tabs){
            const content=doc.createElement('div'),
                form=doc.createElement('form'),
                addNew=doc.createElement('a'),
                edit=doc.createElement('a');
            
            content.className='tb_options_tab_content';
            
            form.id='tb_load_template_form';
            form.method='POST';
            
            
            addNew.href=themifyBuilder.i18n.layoutAddUrl;
            edit.target=addNew.target='_blank';
            addNew.className='add_new tf_plus_icon tb_icon_btn tf_rel';
            addNew.textContent=themifyBuilder.i18n.layoutAddText;
            
            edit.className='tb_icon_btn';
            edit.href=themifyBuilder.i18n.layoutEditUrl;
            edit.textContent=themifyBuilder.i18n.layoutEditText;
            edit.prepend(api.Helper.getIcon('ti-folder'));
            
            
            form.append(tabs,addNew,edit);
            content.appendChild(form);
            return content;
        },
        loadLayout(target) {
            api.Spinner.showLoader();
            const box = target.closest('ul').getBoundingClientRect(),
                promises=[];
            for(let [i,item] of LayoutList.instanes){
                let data=item.data || item.getList();
                promises.push(data);
            }
            Promise.all(promises).then(res=>{
                let j=0,
                    fr=doc.createDocumentFragment(),
                    navFr=doc.createDocumentFragment(),
                    tabs=doc.createElement('div'),
                    tabNav=doc.createElement('div');
                    
                    tabs.className='tb_tabs';
                    tabNav.className='tb_tab_nav tf_clearfix';
                initObserver(api.LightBox.el.tfClass('tf_scrollbar')[0]);
                for(let [id,item] of LayoutList.instanes){
                    let menuItem=doc.createElement('li'),
                        a=doc.createElement('a'),
                        tabContent=doc.createElement('div'),
                        tabId='tb_tabs_'+id;
                    menuItem.className='title';
                    a.href='javascript:;';
                    a.dataset.id=tabId;
                    a.textContent=item.title;
                    tabContent.className='tb_tab tf_clear';
                    if(j===0){
                        menuItem.className+=' current';
                    }
                    else{
                        tabContent.className+=' tf_hide';
                    }
                    tabContent.id=tabId;
                    menuItem.appendChild(a);
                    item.data=res[j];
                    tabContent.appendChild(item.getHtml());
                    navFr.appendChild(menuItem);
                    fr.appendChild(tabContent);
                    ++j;
                }
                tabNav.appendChild(navFr);
                tabs.append(tabNav,fr);
                this.el = this.initForm(tabs);
            })
            .catch(e => {
                this.el = null;
            })
            .finally(() => {
                const type=this.el?'spinhide':'error';
                api.LightBox.el.classList.add('tb_predesigned_lightbox');
                api.Spinner.showLoader(type).then(() => {

                    const data = this.el || themifyBuilder.i18n.layoutError;
                    api.LightBox.setStandAlone(box.left, box.top);
                 
                    api.LightBox.open({
                        loadMethod: 'html',
                        save:false,
                        data: data
                    }).then(lb => {
                        if(this.el){
                            this.reInitJs(this.el);
                            if(!Themify.isTouch){
                                setTimeout(()=>{
                                    this.el.querySelector('#'+this.el.querySelector('.title.current a').dataset.id).tfClass('tb_layout_search')[0].focus();
                                    this.el = null;
                                },125);
                            }
                        }
                        Themify.on('themify_builder_lightbox_close',lb => {
                            lb.classList.remove('tb_predesigned_lightbox');
                            if(observer){
                                observer.disconnect();
                                observer=null;
                            }
                        }, true);
                 
                    });

                });
            });
        },
        saveLayout(item) {
            api.Spinner.showLoader('spinhide');
            const options = {
                    contructor: true,
                    loadMethod: 'html',
                    save: {},
                    data: {
                        save_as_layout: {
                            options: [{
                                    id: 'layout_title_field',
                                    type: 'text',
                                    label: ThemifyConstructor.label.title
                                },
                                {
                                    id: 'layout_img_field',
                                    type: 'image',
                                    label: ThemifyConstructor.label.image_preview
                                },
                                {
                                    id: 'layout_img_field_id',
                                    type: 'hidden'
                                },
                                {
                                    id: 'postid',
                                    type: 'hidden',
                                    value:  api.Builder.get().id
                                }
                            ]
                        }
                    }
                },
                box = item.closest('ul').getBoundingClientRect();
            api.LightBox.el.classList.add('tb_savead_lightbox');
            api.LightBox.setStandAlone(box.left, box.top);
            api.LightBox.open(options).then(lb => {
                const saveBtn=lb.tfClass('builder_save_button')[0],
                    save=e=>{
                    e.stopPropagation();
                    e.preventDefault();
                    const ajaxData=Object.assign({
                        action:'tb_save_custom_layout'
                    },api.Forms.serialize(api.LightBox.el));
                    api.Spinner.showLoader();
                    api.LocalFetch(ajaxData).then(res => {
                        if (res.status === 'success') {
                            api.Spinner.showLoader('done');
                            api.LightBox.close();
                            this.el = this.json = null;
                        } else {
                            api.Spinner.showLoader('error');
                            api.LiteLightBox.alert(res.msg);
                        }
                    }).catch(() => {
                        api.Spinner.showLoader('error');
                    });
                };
                saveBtn.tfOn(Themify.click,save);
                Themify.on('themify_builder_lightbox_close', lb => {
                    lb.classList.remove('tb_savead_lightbox');
                    saveBtn.tfOff(Themify.click,save);
                }, true);

            });
        },
        reInitJs(wrapper) {
            wrapper.querySelector('#tb_load_template_form')
            .tfOn(Themify.click, e => {
                const el = e.target?e.target.closest('.tb_layout_thumb'):null;
                if (!el || (e.target.closest('a') && e.target.closest('a').target==='_blank')) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();
                const layout = el.closest('li'),
                    slug = layout.dataset.slug,
                    opt = {
                        msg: themifyBuilder.i18n.confirm_template_selected,
                        buttons: {
                            no: themifyBuilder.i18n.layout_replace,
                            yes: themifyBuilder.i18n.layout_append
                        }
                    };

                api.LiteLightBox.confirm(opt).then(answer => {
                        if(answer){
                            const group = layout.closest('ul').dataset.group,
                                done = () => {
                                    const data=api.Helper.cloneObject(api.layouts_selected[slug].builder_data),
                                        customCss=data.custom_css;
                                    if (layoutIsSet !== true) {
                                        const ajaxData={
                                            action:'set_layout_action',
                                            mode:('no' !== answer ? 1 : 0)
                                        };
                                        api.LocalFetch(ajaxData, 'text').then(() => {
                                            layoutIsSet = true;
                                        });
                                    }
                                    delete data.custom_css;
                                    api.Builder.get().reLoad({builder_data:data,used_gs:api.layouts_selected[slug].used_gs,custom_css:customCss},answer!=='no');
                                    api.LightBox.close();
                                };
                            if (!api.layouts_selected) {
                                api.layouts_selected = {};
                            } 
                            if (api.layouts_selected[slug]) {
                                done();
                                return;
                            }
                            api.Spinner.showLoader();
                            LayoutList.instanes.get(group).getItem(slug).then(res=>{
                                res.builder_data=api.Helper.clearElementId(res.builder_data);
                                api.layouts_selected[slug] = res;
                                done();
                            })
                            .catch(e=>{
                                api.Spinner.showLoader('error');
                                api.LiteLightBox.alert(e);
                                api.LightBox.close();
                            });
                        }
                    });
            })
            .tfOn('submit',e=>{
               e.preventDefault();
               e.stopPropagation();
            });
        }
    };
    class LayoutList{
        constructor(id){
            LayoutList.instanes.set(id,this);
        }
        search(el){
            const s = el.value.trim().toUpperCase(),
                list = el.closest('.tb_tab').tfClass('tb_layout_title');
            for (let i = list.length - 1; i > -1; --i) {
                list[i].closest('li').style.display = (s === '' || list[i].textContent.toUpperCase().indexOf(s)!==-1) ? '' : 'none';
            }
        }
        clearSearch(el){
            const search=el.closest('.tb_tab').tfClass('tb_layout_search')[0];
            search.value = '';
            Themify.triggerEvent(search, 'input');
        }
        filter(el){
            if (!el.classList.contains('current')) {
                const menu=el.closest('ul'),
                    list = menu.closest('.tb_tab').tfClass('tb_layout_lists')[0].children,
                    showAll = el.classList.contains('all'),
                    selector = '' !== themifyBuilder.paths.layouts_index ? '*' : null,
                    text = el.textContent,
                    nav = menu.children;
                for (let i = list.length - 1; i > -1; --i) {
                    let show = showAll;
                    if (!show) {
                        let cat = list[i].dataset.category;
                        if (cat) {
                            show = text === cat || (selector === '*' && cat.indexOf(text) !== -1);
                        }
                    }
                    list[i].style.display = show ? '' : 'none';
                }
                for (let i = nav.length - 1; i > -1; --i) {
                    nav[i].classList.toggle('current', nav[i] === el);
                }
                menu.parentNode.tfClass('tb_ui_dropdown_label')[0].textContent = text;
            }
        }
        createItems(items,selectedCat){
            const ext = webp ? 'webp' : 'png',
                imgW=this.imgW?this.imgW:300,
                imgH=this.imgH?this.imgH:348,
                placeholder = '//via.placeholder.com/' + imgW + 'x' + imgH + '.' + ext + '?text=',
                categories=new Set(),
                icon = api.Helper.getIcon('ti-search'),
                fr=doc.createDocumentFragment(),
                catFr=doc.createDocumentFragment();
            for (let i = 0, len = items.length; i < len; ++i) {
                let li = doc.createElement('li'),
                    thumbnail = doc.createElement('div'),
                    title = doc.createElement('div'),
                    imgPlaceHolder = new Image(imgW,imgH);
               
                if(items[i].id){
                    li.dataset.id = items[i].id;
                }
                if(items[i].slug){
                    li.dataset.slug = items[i].slug;
                }
                thumbnail.className = 'tb_layout_thumb tf_rel';

                imgPlaceHolder.loading = 'lazy';
                imgPlaceHolder.decoding = 'async';
                imgPlaceHolder.src = placeholder + encodeURI(items[i].title);
                imgPlaceHolder.alt = imgPlaceHolder.title = items[i].title;

                if (items[i].thumbnail) {
                    imgPlaceHolder.dataset.src=items[i].thumbnail;
                    observer.observe(thumbnail);
                }
                title.className = 'tb_layout_title tf_inline_b';
                title.innerHTML = items[i].title;
                if (undefined !== items[i].url) {
                    let a = doc.createElement('a');
                    a.className = 'tb_layout_link';
                    a.href = items[i].url;
                    a.target = '_blank';
                    a.title = themifyBuilder.i18n.preview;
                    a.appendChild(icon.cloneNode(true));
                    thumbnail.appendChild(a);
                }
                thumbnail.appendChild(imgPlaceHolder);
                if (items[i].category) {
                    li.dataset.category = items[i].category;
                    let cats = items[i].category.toString().split(',');
                    for (let j = 0, len2 = cats.length; j < len2; ++j) {
                        if ('' !== cats[j] && !categories.has(cats[j])) {
                            let category = doc.createElement('li');
                            category.textContent = cats[j];
                            if (cats[j] === selectedCat) {
                                category.className = 'tb_selected_cat';
                            }
                            catFr.appendChild(category);
                            categories.add(cats[j]);
                        }
                    }
                }
                li.append(thumbnail, title);
                fr.appendChild(li);
            }
            return {items:fr,cats:catFr};
        }
        getHtml(){
            const fr=doc.createDocumentFragment(),
                sticky=doc.createElement('div'),
                searchContainer=doc.createElement('div'),
                inputSearch=doc.createElement('input'),
                clearSearch=doc.createElement('span'),
                filterWrap=doc.createElement('div'),
                filterLabel=doc.createElement('span'),
                filterUl=doc.createElement('ul'),
                allLi=doc.createElement('li'),
                container=doc.createElement('ul'),
                res=this.createItems(this.data);
                
                sticky.className='tb_layout_sticky tf_clearfix';
                
                searchContainer.className='tb-layout-search-container';
                
                inputSearch.type='text';
                inputSearch.placeHolder=themifyBuilder.i18n.label.search;
                inputSearch.className='tb_layout_search';
                inputSearch.required=true;
                
                clearSearch.className='tb_clear_input tf_close';
                
                searchContainer.append(inputSearch,clearSearch);
                sticky.appendChild(searchContainer);
                if(res.cats.querySelector('li')){
                    filterWrap.className='tb_ui_dropdown tf_rel';
                    filterLabel.className='tb_ui_dropdown_label tf_rel';
                    filterLabel.tabIndex='-1';
                    allLi.textContent=filterLabel.textContent=themifyBuilder.i18n.label.all;

                    filterUl.className='tb_ui_dropdown_items tf_scrollbar';
                    allLi.className='all';
                    filterUl.tfOn(Themify.click, e=>{
                        if (e.target.tagName === 'LI') {
                            e.preventDefault();
                            e.stopPropagation();
                            this.clearSearch(e.target);
                            this.filter(e.target);
                        }
                    })
                    .append(allLi,res.cats);
                    filterWrap.append(filterLabel,filterUl);
                    sticky.appendChild(filterWrap);
                }
                inputSearch.tfOn('input',e=>{
                    if(allLi.classList.contains('all')){
                        this.filter(allLi);
                    }
                    this.search(e.target);
                },{passive:true});
                clearSearch.tfOn(Themify.click,e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    this.clearSearch(e.target);
                });
                container.className='tb_layout_lists';
                container.dataset.group=this.id;
                container.appendChild(res.items);
                fr.append(sticky,container);
                return fr;
        }
    } 
    
    LayoutList.instanes=new Map();
    
    class Predesigned extends LayoutList{
        constructor(){
            const id='predesigned';
            super(id);
            this.id=id;
            this.title=themifyBuilder.paths[this.id].title;
        }
        getList(){
            const url=themifyBuilder.paths[this.id].url;
            return Themify.fetch('', null, {
                credentials: 'omit',
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json'
                }
            }, url);
        }
        async getItem(slug){
            const file = themifyBuilder.paths[this.id].single.replace('{SLUG}', slug);
            try{
                let res=await Themify.fetch('', null, {
                       credentials: 'omit',
                       method: 'GET',
                       mode: 'cors',
                       headers: {
                           'Content-Type': 'application/json'
                       }
                    }, file),
                    data = JSON.stringify( res );
                if ( data.indexOf( api.GS.key ) !== -1 ) {
                    data = { builder_data : JSON.parse( data ) };
                    try{
                        data.used_gs=await Themify.fetch('', null, {
                            credentials: 'omit',
                            method: 'GET',
                            mode: 'cors',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        }, themifyBuilder.paths[this.id].single.replace('{SLUG}', slug + '-gs'));
                    }
                    catch(e){
                        
                    }
                } 
                else {
                    data = { builder_data : JSON.parse( data ) };
                }
                return data;
            }
            catch(e){
                reject(themifyBuilder.i18n.layout_error.replace('{FILE}', file));
            }
        }
    }
    
    
    class Custom extends LayoutList{
        constructor(){
            const id='custom';
            super(id);
            this.id=id;
            this.title=themifyBuilder.i18n.savedLayoutTitle;
        }
        getList(slug){
            return api.LocalFetch({
                slug:slug || '',
                action:'tb_get_save_custom_layout'
            });
        }
        getItem(slug){
            return this.getList(slug).then(res=>{
                return {builder_data:res};
            });
        }
    }
    
    new Predesigned();
    new Custom();
    
    if(themifyBuilder.paths.theme){
        class ThemeLayout extends LayoutList{
            constructor(){
                const id='theme';
                super(id);
                this.id=id;
                this.title=themifyBuilder.paths[this.id].title;
            }
            getList(){
                const zipArr=themifyBuilder.paths[this.id].data,
                    data=[],
                    imgUrl=themify_vars.theme_url+'/builder-layouts/',
                    ext=webp?'.webp':'.jpg',
                    upperCase=str=>{
                        const arr = str.replace(/  +/g, ' ').split(' ');
                        for (let i = 0,len=arr.length; i < len; ++i) {
                            arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1);
                        }
                        return arr.join(' ');
                    };
                for(let i=0,len=zipArr.length;i<len;++i){
                    let slug=zipArr[i].replace('.zip','');
                    data[i]={
                        title:upperCase(slug.replaceAll('-',' ')),
                        slug: slug,
                        thumbnail:imgUrl+slug+ext
                    };
                }
                return Promise.resolve(data);
            }
            getItem(slug){
                return new Promise((resolve,reject)=>{
                    const promises=[api.Helper.loadJsZip()],
                       zipUrl=themify_vars.theme_url+'/builder-layouts/';

                       promises.push(Themify.fetch('', 'blob',{
                           credentials: 'omit'
                       },zipUrl+slug+'.zip'));
                       
                    Promise.all(promises).then(res=>{
                       JSZip.loadAsync(res[1]).then(zip=>{
                           const files=zip.files;
                           if(files){
                               const builderFileName='builder_data_export.txt',
                                      gsFileName='builder_gs_data_export.txt';
                               if(files[builderFileName]!==undefined){
                                   const prm=[];
                                   prm.push(zip.file(files[builderFileName].name).async('text'));
                                   if(files[gsFileName]!==undefined){
                                       prm.push(zip.file(files[gsFileName].name).async('text'));
                                   }

                                   Promise.all(prm).then(res=>{
                                       const data={builder_data:JSON.parse(res[0])};
                                       if(res[1]){
                                           data.used_gs=JSON.parse(res[1]);
                                       }
                                       resolve(data);
                                   });
                               }
                               else{
                                   reject(themifyBuilder.i18n.importBuilderNotExist);
                               }
                           }
                           else{
                               reject(themifyBuilder.i18n.zipFileEmpty);
                           }
                       });

                   });
                });
            }
        }
        new ThemeLayout();
    }
    
    Themify.trigger('tb_layout_loaded',[LayoutList,webp]);
    
    
})(tb_app, Themify, document);