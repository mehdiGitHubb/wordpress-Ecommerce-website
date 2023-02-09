let TF_Skin;
((Themify,win,und,doc) =>{
    'use strict';
    const cache=new Map();
    TF_Skin={
        el:null,
        skinId:null,
        importModule(){
            return Themify.loadJs(Themify.url+'js/admin/import/import',!!win.TF_Import);
        },
        notificationModule(){
            return Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
        },
        getForm(data){
            const labels=data.labels,
                plugins=data.plugins,
                modal=doc.createElement('div'),
                importWrap=doc.createElement('div'),
                processImport=doc.createElement('a'),
                eraseWrap=doc.createElement('div'),
                note=doc.createElement('p'),
                close=doc.createElement('a'),
                closeIcon=doc.createElement('i'),
                fr=doc.createDocumentFragment(),
                fr2=doc.createDocumentFragment(),
                checkboxes=['builder_img'];
                
                modal.className='required-addons themify-modal tf_scrollbar tf_opacity tf_hide';
                processImport.className='proceed-import button big-button themify_button';
                processImport.textContent=labels.proceed_import;
                
                
                importWrap.className='tf_import_wrap';
                eraseWrap.className='tf_erase';
                
                note.className='note';
                note.textContent=labels.note;
                
                close.className='close';
                close.href='#';
                closeIcon.className='tf_close';
                close.appendChild(closeIcon);
                
                if(Object.keys(plugins).length>0){
                    let isAllActive=true,
                        frag=doc.createDocumentFragment();
                    for(let i in plugins){
                        let li=doc.createElement('li'),
                            a=doc.createElement('a'),
                            item;
						if(plugins[i].page){
							a.target='_blank';
							a.rel='noopener';
							a.href=plugins[i].page;
						}
						else{
							a=doc.createElement('span');
						}
						a.textContent=plugins[i].name;
                        if(plugins[i].error || (plugins[i].active && plugins[i].active===1)){
                            if(plugins[i].error){
                                item=doc.createElement('div');
                                li.className='tf_plugin_has_error';
                                item.className='tf_plugin_error';
                                item.innerHTML=plugins[i].error;
                                let links=item.tfTag('a');
                                for(let j=links.length-1;j>-1;--j){
                                    links[j].target='_blank';
                                    links[j].rel='noopener';
                                }
                                isAllActive=false;
                            }
                            else{
                                item=doc.createElement('span');
                                item.className='ti-check';
                            }
                        }
                        else{
                            item=doc.createElement('a');
                            item.href='#';
                            item.className='tf_install_plugin';
                            item.dataset.type=plugins[i].install?'install':'activate';
                            isAllActive=false;
                            let label=labels.activate;
                            if(plugins[i].install){
                                if(plugins[i].install==='buy'){
                                    label=labels.buy;
                                    item.href=plugins[i].page;
                                    item.target='_blank';
                                    item.rel='noopener';
                                    item.className='';
                                    item.dataset.type='buy';
                                }
                                else{
                                    label=labels.install;
                                }
                            }
                            item.innerHTML=label;
                            item.dataset.plugin=i;
                            item.dataset.name=plugins[i].name;
                        }
                        li.appendChild(a);
                        if(!plugins[i].error){
                            li.appendChild(doc.createTextNode('( '));
                        }
                        li.appendChild(item);
                        if(!plugins[i].error){
                            li.appendChild(doc.createTextNode(' )'));
                        }
                        frag.appendChild(li);
                    }
                    if(isAllActive===false){
                        const importWarning=doc.createElement('p'),
                            headMsg=doc.createElement('p'),
                            ul=doc.createElement('ul');
                            headMsg.textContent=labels.head;
                            importWarning.className='themify-import-warning';
                            importWarning.textContent=labels.import_warning;
                            ul.appendChild(frag);
                            fr.append(headMsg,ul,importWarning);
                    }
                }
                if(data.has_demo){
                    checkboxes.push('modify');
                    checkboxes.push('erase');
                }
                for(let i=checkboxes.length-1;i>-1;--i){
                    let label=doc.createElement('label'),
                        ch=doc.createElement('input'),
                        sp=doc.createElement('span');
                    ch.type='checkbox';
                    ch.id=label.htmlFor ='tf_'+checkboxes[i]+'_demo';
                    if(checkboxes[i]!=='builder_img'){
                        ch.checked=true;
                    }
                    sp.textContent=labels[checkboxes[i]];
                    label.appendChild(sp);
                    fr2.append(ch,label);
                }
                eraseWrap.appendChild(fr2);
                importWrap.append(processImport,eraseWrap);
                fr.append(importWrap,note,close);
                modal.appendChild(fr);
                return modal;
        },
        init(data){
            return new Promise(async(resolve,reject)=>{
                try{
                    const el=doc.querySelector('.required-addons.themify-modal');
                    if(!data){
                        data={};
                    }
                    this.skinId=data.skin || 'default';
                    if(!el || el.dataset.skin!==data.skin){
                        if(el){
                            el.remove();
                        }
                        if(this.el){
                            this.el.remove();
                        }
                        data.action='themify_required_plugins_modal';
                        data.nonce=themify_js_vars.nonce;
                        const res=await Themify.fetch(data);
                        if(!res.success){
                            throw res.data;
                        }
                        this.el=this.getForm(res.data);
                        themify_js_vars.labels = res.data.labels;
                        let clickedItems=[],
                            isWorking=false;
                        this.el.tfOn('click',async e=>{
                            const target=e.target?e.target.closest('.proceed-import,.close,.tf_install_plugin'):null;
                            if(target && target.target!=='_blank'){
                                e.preventDefault();
                                const cl=target.classList; 
                                if(!cl.contains('disabled')){
                                    if(cl.contains('proceed-import')){
                                        cl.add('disabled');
                                        const clear_old_imports=!!this.el.querySelector('#tf_erase_demo:checked'),
                                            keep_modify=!!this.el.querySelector('#tf_modify_demo:checked'),
                                            builder_images=!!this.el.querySelector('#tf_builder_img_demo:checked'),
                                            plugins=this.el.tfClass('tf_install_plugin');
                                            for(let i=plugins.length-1;i>-1;--i){
                                                plugins[i].classList.add('disabled');
                                            }
                                        try{
                                            await this.import(clear_old_imports,keep_modify,builder_images);
                                            await this.close();
                                            win.location.reload();
                                        }
                                        catch(e){
                                            for(let i=plugins.length-1;i>-1;--i){
                                                plugins[i].classList.remove('disabled');
                                            }
                                           cl.remove('disabled');  
                                        }
                                    }
                                    else if(cl.contains('tf_install_plugin')){
                                        cl.add('disabled');
                                        const chk = doc.createElement('span'),
                                        inportBtn=this.el.tfClass('proceed-import')[0].classList,
                                        run=async el=>{
                                            try{
                                                await this.installPlugins(el);
                                                el.tfClass('tf_loader')[0].className='ti-check';
                                            }
                                            catch(e){
                                                el.innerHTML=themify_js_vars.labels[el.dataset.type];
                                                el.classList.remove('disabled');
                                            }  
                                        };
                                        inportBtn.add('disabled');
                                        chk.className='tf_loader';
                                        target.innerHTML='';
                                        target.appendChild(chk);
                                        if(isWorking===true){
                                            clickedItems.push(target);
                                            return;
                                        }
                                        isWorking=true;
                                        await run(target);
                                        for(let i=0,len=clickedItems.length;i<len;++i){
                                            await run(clickedItems[i]);
                                        }
                                        clickedItems=[];
                                        isWorking=false;
                                        inportBtn.remove('disabled');
                                    }
                                    else {
                                        this.close();
                                    }
                                }
                            }
                              
                        });
                        doc.tfId('themify').appendChild(this.el);
                    }
                    this.show();
                    resolve(el?'open':'ajax');
                }
                catch(e){
                    reject(e);
                }
            });
        },
        show(){
            return new Promise(resolve=>{
                this.el.classList.remove('tf_hide');
                setTimeout(()=>{
                    this.el.tfOn('transitionend',resolve,{passive:true,once:true}).classList.remove('tf_opacity');
                },100);
            });
        },
        close(){
            return new Promise(resolve=>{
                this.el.tfOn('transitionend',e=>{
                    this.el.classList.add('tf_hide');
                    this.el.remove();
                    resolve();
                },{passive:true,once:true})
                .classList.add('tf_opacity');
            });
        },
        installPlugins(target){
            return new Promise(async(resolve,reject)=>{
                await this.notificationModule();
                const vars=themify_js_vars,
                    type=target.dataset.type,
                    name=target.dataset.name,
                    fetch=async ()=>{
                        const res=await Themify.fetch({
                            action:'themify_activate_plugin',
                            plugin:target.dataset.plugin,
                            nonce:vars.nonce
                        });
                        if(!res.success){
                            let err=res.data;
                            if(err.check_license || err.buy){
                                if(err.check_license){
                                    await Themify.loadJs(Themify.url+'js/admin/modules/license',!!win.TF_License);
                                    await TF_License.init();
                                }
                                const error = Error(err.errorMessage);
                                error.code=err.buy?'buy':'check_license';
                                if(err.buy){
                                    target.dataset.type='buy';
                                    target.target='_blank';
                                    target.rel='noopener';
                                    target.href=err.url;
                                }
                                err=error;
                            }
                            else if(err.errorMessage){
                                err=err.errorMessage+':'+err.errorCode;
                            }
                            throw err;
                        }
                        if(res.data && res.data.install_plugin_url){
                            await Themify.fetch(null,'text',{method:'GET'},res.data.install_plugin_url);
                        }
                        await TF_Notification.showHide('done',vars.labels.plugins[type+'_done'].replaceAll('%plugin%',name),1500);
                        if(type==='install'){
                            target.dataset.type='activate';
                            await this.installPlugins(target);
                        }
                    },
                    installUpdater=async ()=>{
                        await Themify.loadJs(Themify.url+'js/admin/modules/install-updater',!!win.TF_Install_Updater);
                        await TF_Install_Updater(vars.nonce);
                    };

                await TF_Notification.show('info',vars.labels.plugins[type].replaceAll('%plugin%',name));
                try{    
                    try{
                        await fetch();
                    }
                    catch(e){
                        if(e){
                            if(e.code==='check_license' || e.code==='buy'){
                                throw e.message;
                            }
                            if(e.install_updater){
                                await installUpdater();
                                try{
                                    await fetch();
                                    resolve();
                                    return;
                                }
                                catch(e){
                                    if(e.code==='check_license' || e.code==='buy'){
                                        throw e.message;
                                    }
                                }
                            }
                        }
                        await (new Promise((resolve,reject)=>{//try again
                            setTimeout(()=>{
                                fetch().then(resolve).catch(reject);
                            },1000);
                        }));
                    }
                    resolve();
                }
                catch(e){
                    const er=typeof e==='string'?e:'';
                    await TF_Notification.showHide('error',vars.labels.plugins[type+'_fail'].replaceAll('%plugin%',name).replaceAll('%error%',er),4000);
                    reject(e);
                }
            });
        },
        import(clear_old_imports,keep_modify,builder_images ){
            return new Promise(async (resolve,reject)=>{
                const vars=themify_js_vars;
                let url='https://themify.me/public-api/samples/'+vars.theme+'/',
                    skin=this.skinId,
                    skinId=vars.theme+'-'+skin;
                if(skin){
                    url+=skin+'/';
                }
                url+='data.json';
                try{
                    
                    let tmp=cache.get(url),
                        prms=[];
                    if(!tmp){
                        prms.push(Themify.fetch('', null, {
                            credentials: 'omit',
                            method: 'GET',
                            mode: 'cors',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        }, url));
                    }
                    else{
                        prms.push(tmp);
                    }
                    prms.push(this.notificationModule());
                    prms.push(this.importModule());
                    if(clear_old_imports){
                        prms.push(Themify.trigger('themify_erase_content',keep_modify));
                    }
                    const response=await Promise.all(prms),
                        res=response[0],
                        importMessage=vars.import,
                        memory=parseInt(importMessage.memory) || 64,
                        themeData={data:{},id:skinId,nonce:vars.nonce,action:'themify_import_theme_data'},
                        themeKeys=['themify_settings','widgets','homepage'],
                        importThemeData = async themeData=>{
                            try{
                                const response=await Themify.fetch(themeData);
                                if(!response.success){
                                    throw 'error';
                                }
                            }
                            catch(e){
                                try{
                                    themeData.data=new Blob( [JSON.stringify(themeData.data)], { type: 'application/json' });
                                    const response=await Themify.fetch(themeData);
                                    if(!response.success){
                                        throw 'error';
                                    }
                                }
                                catch(e){
                                    throw e;
                                }
                            }
                        };
                    cache.set(url,res);
                    importMessage.images_chunk=memory>=255?6:(memory>=120?5:(memory>60?3:2));
                    TF_Import.importImages();
                    if(res.menu_locations || res.theme_mods || res.product_attribute){//create menus before input menu items
                        await TF_Notification.show('info',importMessage.menu);
                        if(res.menu){
                            themeData.data.menu=res.menu;
                        }
                        if(res.menu_locations){
                            themeData.data.menu_locations=res.menu_locations;
                        }
                        if(res.theme_mods){
                            themeData.data.theme_mods=res.theme_mods;
                        }
                        if(res.product_filter){
                            themeData.data.product_filter=res.product_filter;
                        }
                        if(res.product_attribute){
                            themeData.data.product_attribute=res.product_attribute;
                        }
                        await importThemeData(themeData);
                        
                        delete themeData.data.menu;
                        delete themeData.data.product_filter;
                        delete themeData.data.menu_locations;
                        delete themeData.data.product_attribute;
                        delete themeData.data.theme_mods;
                    }
                    if(!builder_images){
                        importMessage.skip_builder=true;
                    }
                    if(res.terms){
                        importMessage.loading=importMessage.terms;
                        res.terms=TF_Import.sort(res.terms,'parent');
                        await TF_Import.init(res.terms,'themify_import_terms',vars.nonce,importMessage,skinId);
                    }
                    if(res.posts){
                        importMessage.loading=importMessage.posts;
                        res.posts=TF_Import.sort(res.posts,'post_parent');
                        await TF_Import.init(res.posts,'themify_import_posts',vars.nonce,importMessage,skinId);
                    }
                    if(res.menu_items){
                        importMessage.loading=importMessage.menu_items;
                        res.menu_items=TF_Import.sort(res.menu_items,'meta_input','_menu_item_menu_item_parent');
                        await TF_Import.init(res.menu_items,'themify_import_posts',vars.nonce,importMessage,skinId);
                    }
                    for(let i=themeKeys.length-1;i>-1;--i){
                        if(res[themeKeys[i]]!==und){
                           themeData.data[themeKeys[i]]=res[themeKeys[i]];
                        }
                    }
                    if(Object.keys(themeData.data).length>0){
                        await TF_Notification.show('info',importMessage.theme);
                        await importThemeData(themeData);
                    }
                    await TF_Notification.showHide('done',importMessage.done,2000);
                    resolve();
                }
                catch(e){
                    await this.notificationModule();
                    const er=typeof e==='string'?e:'';
                    await TF_Notification.showHide('error',er,2000);
                    reject(e);
                }
             });
        }
    };
    
})( Themify,window,undefined,document);