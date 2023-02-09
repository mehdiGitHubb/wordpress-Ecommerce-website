let TF_Import;
((Themify,win,und) =>{
    'use strict';
    TF_Import={
        importImages(){
          return Themify.loadJs(Themify.url+'js/admin/import/import-images',!!win.TF_ImportImages);  
        },
        init(posts,action,nonce,messages,id,imageAction){
            return new Promise(async (resolve,reject)=>{
                let isVisible=true;
                this.importImages();
                await Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
                const len=posts.length,
                    timer=len>12?150:100,
                    skipMsg=messages.import_skip,
                    thumbs=[],
                    chunkSize=messages.limit>0?parseInt(messages.limit):5,
                    imageChunkSize=messages.images_chunk,
                    skipBuilder=!!messages.skip_builder,
                    getTitle=items=>{
                        const title=[];
                        for(let i=0,len=items.length;i<len;++i){
                            let t='';
                            if(items[i].tbp_template_name!==und){
                                t=items[i].tbp_template_name;
                            }
                            else if(items[i].post_title!==und){
                                t=items[i].post_title;
                            }
                            else if(items[i].title!==und){
                                t=items[i].title.rendered!==und?items[i].title.rendered:items[i].title;
                            }
                            else if(items[i].name!==und){
                                t=items[i].name;
                            }
                            else if(items[i].post_name!==und){
                                t=items[i].name;
                            }
                            if(t!==und && t!==''){
                                title.push(t);
                            }
                        }
                        return title.length===0?'':title.join(', ');
                    },
                    send=data=>{
                        return new Promise((resolve,reject)=>{
                            setTimeout(()=>{
                                let ajaxData={
                                    action :action,
                                    id: id || '',
                                    nonce: nonce,
                                    data:data
                                };
                                if(messages.custom_params){
                                    ajaxData=Object.assign(ajaxData,messages.custom_params);
                                }
                                Themify.fetch(ajaxData).then(res=>{
                                    if(!res || !res.success || !res.data){
                                        reject(res);
                                        return;
                                    }
                                    resolve(res);
                                })
                                .catch(reject);
                            },timer);
                        });
                    },
                    visibilitychange=()=>{
                        isVisible=win.document.visibilityState==='visible';
                    },
                    _chunks=(items,size)=>{
                        const res=[]; 
                        while(items.length > 0) {
                            res.push(items.splice(0, size));
                        }
                        return res;
                    },
                    _getBuilderImages=fields=>{
                        const images=new Set(),
                        addImage=url=>{
                            if(url){
                                const parts = url.split('?')[0].split('.');
                                if(['jpg', 'jpeg', 'tiff', 'png', 'gif', 'bmp', 'svg','webp','apng'].indexOf(parts[parts.length - 1]) !== -1){
                                    images.add(url);
                                }
                            }
                        },
                        loop=fields=>{
                            for(let i in fields){
                                if(fields[i]){
                                    if(Array.isArray(fields[i]) || typeof fields[i]==='object'){
                                        loop(fields[i]);
                                    }
                                    else{
                                        let v=fields[i].toString().trim();
                                        if(v){
                                            if(v.indexOf('<img ')!==-1){
                                                let tmp=document.createElement('template');
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
                        loop(fields);
                        return images;
                    },
                    _import=(items,size)=>{
                        return new Promise(async resolve=>{
                            let summ=0,
                                skip=new Set(),
                                arrLen=items.length,
                                msg=messages.loading.replaceAll('%to%',arrLen),
                                posts=_chunks(items,size); 
                            for(let i=0;i<posts.length;++i){
                                let item=posts[i];
                                if(item.length>0){
                                    let postTitle=getTitle(item),
                                        r;
                                        if(arrLen>1 && isVisible===true){
                                            summ+=item.length;
                                            let msgText=msg.replaceAll('%from%',summ).replaceAll('%post%',postTitle);
                                            if(msgText.length>120){
                                                msgText=msgText.slice(0,120)+'...';
                                            }
                                            await TF_Notification.show('info',msgText);
                                        }
                                        try{
                                            r=await send(item);
                                        }
                                        catch(e){
                                            try{
                                                r=await send(new Blob( [JSON.stringify(item)], { type: 'application/json' }));
                                            }
                                            catch(e){

                                            }
                                        }
                                        finally{
                                            if(!r || !r.success || !r.data){
                                                if(isVisible===true){
                                                    await TF_Notification.showHide('error',skipMsg.replaceAll('%post%',postTitle),2000);
                                                }
                                                for(let j=item.length-1;j>-1;--j){//error, add in skip to try again at the end
                                                    skip.add(item[j]);
                                                }
                                            }
                                            else{
                                                /*response values can be
                                                1.true - skip(e.g user post)
                                                2.false - error
                                                3.msg - warning(not error)
                                                4.int - success
                                                */
                                                let isRemoved=false;
                                                const resp = r.data;
                                                for(let id in resp){
                                                    let respItem=resp[id];
                                                    for(let j=item.length-1;j>-1;--j){
                                                        if(item[j]!==und){
                                                            let itemId=item[j].term_id || item[j].ID || item[j].post_id;
                                                            if(id==itemId){
                                                                 if(respItem===false){//error, add in skip to try again at the end
                                                                    skip.add(item[j]);
                                                                }
                                                                else if(typeof respItem==='object' || isNaN(respItem)){
                                                                    let msg=typeof respItem==='object'? respItem.msg:respItem;
                                                                    if(isVisible===true){
                                                                        await TF_Notification.showHide('warning',skipMsg.replaceAll('%post%',getTitle([item[j]]))+':'+msg,2000);
                                                                    }
                                                                    if(typeof respItem==='object' && respItem.skip){
                                                                        for(let k=item.length-1;k>-1;--k){//remove skipped items
                                                                            let type=item[k].post_type || item[k].taxonomy;
                                                                            
                                                                            
                                                                            if(type===respItem.skip){
                                                                                item.splice(k,1);
                                                                            }
                                                                        }
                                                                        for(let k=posts.length-1;k>-1;--k){//remove skipped items
                                                                            for(let m=posts[k].length-1;m>-1;--m){
                                                                                let type=posts[k][m].post_type || posts[k][m].taxonomy;
                                                                                
                                                                                if(type===respItem.skip){
                                                                                    posts[k].splice(m,1);
                                                                                    isRemoved=true;
                                                                                }
                                                                            }
                                                                            if(posts[k].length===0){
                                                                                posts.splice(k,1);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                else if(respItem!==true){//is number=>success
                                                                    if(item[j].term_thumb){
                                                                        thumbs.push({term_id:respItem,url:item[j].term_thumb.trim()});
                                                                    }
                                                                    else if(item[j].thumb){
                                                                        thumbs.push({id:respItem,url:item[j].thumb.trim()});
                                                                    }
                                                                }
                                                                if(item[j]!==und){
                                                                    item.splice(j,1);
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                                if(isRemoved===true){
                                                    posts=posts.flat();
                                                    for(let k=posts.length-1;k>-1;--k){
                                                        if(posts[k].length===0){
                                                            posts.splice(k,1);
                                                        }
                                                    }
                                                    i=-1;
                                                    posts=_chunks(posts,size);
                                                }
                                            }
                                        }
                                }
                            }
                            resolve(skip);
                        });
                    },
                    preventReload=e=>{
                        e.preventDefault();
                        return e.returnValue='Are you sure';
                    },
                    correctBuilderData=rows=>{
                        if(!Array.isArray(rows)){
                            const tmp=[];
                            for(let i in rows){
                                tmp.push(rows[i]);
                            }
                            rows=tmp;
                        }
                        for(let i=rows.length-1;i>-1;--i){
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
                                    if(cols[j].modules){
                                        cols[j].modules=correctBuilderData(cols[j].modules);
                                    }
                                }
                            }
                        }
                        return rows;
                    },
                    checkBuilder=cols=>{
                        for(let i=cols.length-1;i>-1;--i){
                            if(cols[i].styling && Object.keys(cols[i].styling).length>0){
                                return true;
                            }
                            let modules=cols[i].modules;
                            if(modules && modules.length>0){
                                for(let j=modules.length-1;j>-1;--j){
                                    if(modules[j].cols){
                                        if(checkBuilder(modules[j].cols)){
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
                    
                    win.tfOff('beforeunload',preventReload)
                    .tfOn('beforeunload',preventReload)
                    .document.tfOff('visibilitychange', visibilitychange, {passive:true})
                    .tfOn('visibilitychange', visibilitychange, {passive:true});
                    let allImages=new Map();
                    messages.custom_data=id || 'default';
                    messages.stop_webp=1;
                    try{
                        let images = new Set();
                        for(let i=posts.length-1;i>-1;--i){
                            let builder=posts[i].meta_input?posts[i].meta_input._themify_builder_settings_json:posts[i]._themify_builder_settings_json;
                            if(builder){
                                if(typeof builder==='string'){
                                    builder=JSON.parse(builder);
                                }
                                builder=correctBuilderData(builder);
                                let found=false;
                                for (let j = builder.length-1; j>-1; --j) {
                                    if ((builder[j].styling && Object.keys(builder[j].styling).length>0) || (builder[j].cols && checkBuilder(builder[j].cols))) {
                                        found=true;
                                        break;
                                    }
                                }
                                if(found===false){
                                    if(posts[i].meta_input){
                                        delete posts[i].meta_input._themify_builder_settings_json;
                                    }
                                    else{
                                        delete posts[i]._themify_builder_settings_json;
                                    }
                                }
                                else if(skipBuilder===false){
                                    let img=_getBuilderImages(builder);
                                    if(img.size>0){
                                        images=new Set([...images, ...img]);
                                    }
                                }
                            }
                            if(posts[i]._product_image_gallery){
                                let g=posts[i]._product_image_gallery;
                                g=typeof g==='string'?g.split(','):g; 
                                for(let j=g.length-1;j>-1;--j){
                                    images.add(g[j].trim());
                                }
                            }
                        }
                        if(images.size>0){
                                await this.importImages();
                                allImages = await TF_ImportImages.init([...images],nonce,messages,imageChunkSize,imageAction);
                                for(let i=posts.length-1;i>-1;--i){
                                    let builder=posts[i].meta_input?posts[i].meta_input._themify_builder_settings_json:posts[i]._themify_builder_settings_json;
                                    if(builder){
                                        if(typeof builder!=='string'){
                                            builder=JSON.stringify(builder);
                                        }
                                        for(let [url,res] of allImages){
                                            if(res!==false && res.src){
                                                if(builder.indexOf(url)!==-1){
                                                    builder=builder.replaceAll(url,res.src);
                                                }
                                                if(posts[i].post_content && posts[i].post_content.indexOf(url)!==-1){
                                                    posts[i].post_content=posts[i].post_content.replaceAll(url,res.src);
                                                }
                                            }
                                        }
                                        builder=JSON.parse(builder);
                                        if(posts[i].meta_input && posts[i].meta_input._themify_builder_settings_json){
                                            posts[i].meta_input._themify_builder_settings_json=builder;
                                        }
                                        else{
                                            posts[i]._themify_builder_settings_json=builder;
                                        }
                                    }
                                    if(posts[i]._product_image_gallery){
                                        let g=posts[i]._product_image_gallery,
                                        _arr=[];
                                        g=typeof g==='string'?g.split(','):g;
                                        for(let j=0,len=g.length;j<len;++j){
                                            let item=allImages.get(g[j].trim());
                                            if(item){
                                                _arr.push(item.id);  
                                            }
                                        }
                                        if(_arr.length>0){
                                            posts[i]._product_image_gallery=_arr.join(',');
                                        }
                                        else{
                                            delete posts[i]._product_image_gallery;
                                        }
                                    }
                                }
                        }
                    }
                    catch(e){
                    }
                    let skipItems=await _import(posts,chunkSize);
                    if(skipItems.size>0){//reduce chunk and trying again
                        skipItems=await _import([...skipItems],2);
                        if(skipItems.size>0 && isVisible===true){
                            await TF_Notification.showHide('error',messages.import_failed.replaceAll('%post%',getTitle(Array.from(skipItems))),4000);
                        }
                    }
                    if(skipItems.size===len){
                        reject(Array.from(skipItems));
                    }
                    else{
                        if(thumbs.length>0){
                            await this.importImages();
                            try{
                                const resp=await TF_ImportImages.init(thumbs,nonce,messages,imageChunkSize,imageAction);
                                allImages=new Map([...resp, ...allImages]);
                            }
                            catch(e){

                            }
                        }
                        resolve([allImages,skipItems]);
                    }
                    win.tfOff('beforeunload',preventReload)
                    .document.tfOff('visibilitychange', visibilitychange, {passive:true});
            });
        },
        sort(items,k1,k2){//sort from parent to childs
            const res=new Set(),
                recursive=el=>{
                    const pid=parseInt(k2===und?el[k1]:el[k1][k2]),
                        elId=el.term_id>0?'term_id':'ID';
                    for(var i=items.length-1;i>-1;--i){
                        if(items[i][elId]>0 && parseInt(items[i][elId])===pid){
                            let parent=items[i][k1];
                            if(k2!==und){
                                parent=parent[k2];
                            }
                            if(parent>0){
                                recursive(items[i]);
                            }
                            res.add(items[i]);
                        }
                    }
                };
            for(var i=items.length-1;i>-1;--i){//first sort 1 level(the most times will be this only)
                let item=items[i][k1];
                if(k2!==und){
                    item=item[k2];
                }
                if(item===und || item==0){
                    res.add(items[i]);
                    items.splice(i,1);
                }
            }
            for(i=items.length-1;i>-1;--i){//sort others
                let item=items[i][k1];
                if(k2!==und){
                    item=item[k2];
                }
                if(item>0){
                    recursive(items[i]);
                    res.add(items[i]);
                }
            }
            return [...res];
        }
    };
})( Themify,window.top,undefined);