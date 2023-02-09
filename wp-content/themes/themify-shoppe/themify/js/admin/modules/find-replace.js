let TF_Replace;
((Themify,win) =>{
    'use strict';
    let found=null,
    count = 0,
    timer=100,
    labels;
    const cache=new Map(),
    getNotification=()=>{ 
        return Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
    },
    getPosts=async (nonce,page,maxPage,isRecursive)=>{
        const data=cache.get(page);
        if(data){
            return data;
        }
        const result = await Themify.fetch({
            action: 'tb_get_ajax_builder_posts',
            page:page,
            nonce: nonce
        });
        if(!result.success){
            throw result.data;
        }
        cache.set(page,result.data);
        if(!isRecursive && maxPage>=(page+1)){
            getPosts(nonce,(page+1),maxPage,1);
        }
        return result.data;
    },
    recursiveReplace=(find,replace,data)=>{
        for(let i in data){
            if(i!=='element_id' &&  i!=='mod_name' && data[i]!==true && data[i]!=='px' && data[i]!=='%'  && data[i] && isNaN(data[i])){
                if(Array.isArray(data[i]) || typeof data[i]==='object'){
                    recursiveReplace(find,replace,data[i]);
                }
                else if(typeof data[i]==='string'){
                    let v=data[i].toString().trim();
                    if(v.indexOf(find)!==-1){
                        found=true;
                        data[i]=v.replaceAll(find,replace);
                    }
                }
            }
        }  
        return data;
    },
    convert=async(find,replace,posts,total)=>{
        count+=posts.length;
        await getNotification();
        const foundPosts=[];
        await TF_Notification.show('info',getMessage(labels.searching,find,posts,count,total),1200);
        for(let i=posts.length-1;i>-1;--i){
            found=false;
            let res=recursiveReplace(find,replace,posts[i].data);
            if(found===true){
                foundPosts.push({data:res,title:posts[i].title,id:posts[i].id});
            }
        }
        return foundPosts;
    },
    preventReload=e=>{
        e.preventDefault();
        return e.returnValue='Are you sure';
    },
    getTitles=posts=>{
        const title=[];
        for(let i in posts){
            let t=posts[i].title?posts[i].title:posts[i];
            title.push(t);
        }
        return title.join(', ');
    },
    getMessage=(msg,find,posts,count,total)=>{
        const replace={
            posts:posts?getTitles(posts):'',
            total:total,
            count:count,
            find:find
        };
        for(let k in replace){
            if(replace[k]!==undefined){
                msg=msg.replaceAll('%'+k+'%',replace[k]);
            }
        }
        if(msg.length>140){
            msg=msg.slice(0,140)+'...';
        }
        return msg;
    },
    validateUrls=async url=>{
        try{//check find
            if(url[0]!=='/' && url[1]!=='/'){
                if(url.indexOf('http')!==0){
                    throw '';
                }
                if(url!=='http://' && url!=='https://'){
                    new URL(url);
                }
            }
        }
        catch(e){
            throw labels.wrong_url.replaceAll('%url%',url);
        }
    },
    send=(data,nonce)=>{
        return new Promise((resolve,reject)=>{
            setTimeout(()=>{
                const ajaxData={
                    action :'tb_save_ajax_builder_mutiple_posts',
                    nonce: nonce,
                    data:data
                };
                Themify.fetch(ajaxData).then(res=>{
                    if(!res.success){
                        return reject(res.data);
                    }
                    resolve(res);
                })
                .catch(reject);
            },timer);
        });
    };
    TF_Replace=async(find,replace,nonce)=>{
        await getNotification();
        try{
            win.tfOff('beforeunload',preventReload)
            .tfOn('beforeunload',preventReload);
            count = 0;
            cache.clear();
            const res=await getPosts(nonce,1,0),
                pages=res.pages,
                total=res.total;
                
            labels=res.labels;
            if(find===replace){
                throw labels.same_url;
            } 
            await Promise.all([validateUrls(find),validateUrls(replace)]);
            const savingPosts=await convert(find,replace,res.posts,total);
            for(let i=2;i<=pages;++i){
                try{
                    let response=await getPosts(nonce,i,pages),
                    found=await convert(find,replace,response.posts,total);
                    savingPosts.push(...found);
                }
                catch(e){
                }
            }
            if(savingPosts.length>0){
                if(savingPosts.length>12){
                    timer=150;
                }
                await TF_Notification.show('info',getMessage(labels.found,find,savingPosts,savingPosts.length,total),3000);
       
                const result=[],
                    titles=[],
                    size=5,
                    foundCount=savingPosts.length;
                let hasSuccess=false,
                    summ=0;
                while(savingPosts.length > 0) {
                    let slice=savingPosts.splice(0, size),
                        chunk={},
                        chunkTitle={};
                    for(let i=slice.length-1;i>-1;--i){
                        let id=slice[i].id;
                        chunk[id]=slice[i].data;
                        chunkTitle[id]=slice[i].title;
                    }
                    result.push(chunk);
                    titles.push(chunkTitle);
                }

                for(let i=0,len=result.length;i<len;i++){
                    summ+=Object.keys(titles[i]).length;
                    await TF_Notification.show('info',getMessage(labels.saving,find,titles[i],summ,foundCount));
                    let r;
                    try{
                        r=await send(result[i],nonce);
                        if(!r.success){
                            throw r.data;
                        }
                    }
                    catch(e){
                        try{
                            r=await send(new Blob( [JSON.stringify(result[i])], { type: 'application/json' }),nonce);
                            if(!r.success){
                                throw r.data;
                            }
                        }
                        catch(e){
                            await TF_Notification.showHide('error',e,2000);
                        }
                    }
                    if(r && r.data){
                        r=r.data;
                        let errors=[];
                        for(let k in r){
                            if(r[k]!=1){
                                errors.push(r[k]);
                            }
                            else{
                                hasSuccess=true;
                            }
                        }
                        if(errors.length>0){
                            await TF_Notification.showHide('error',getMessage(labels.no_found,find,errors,errors.length,total),4000);
                        }
                    }
                }
                if(hasSuccess===true){
                    await Themify.fetch({
                        action:'themify_regenerate_css_files_ajax',
                        nonce:nonce
                    });
                }
                await TF_Notification.showHide('done',labels.done);
            }
            else{
                await TF_Notification.showHide('warning',getMessage(labels.no_found,find),3000);
            }
        }
        catch(e){
            await TF_Notification.showHide('error',e);
        }
        win.tfOff('beforeunload',preventReload);
    };
    
})( Themify,window);