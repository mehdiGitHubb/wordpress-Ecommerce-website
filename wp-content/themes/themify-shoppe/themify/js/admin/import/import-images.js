let TF_ImportImages;
((Themify,doc,win,und) =>{
    'use strict';
    TF_ImportImages={
        downloaded:new Map(),
        _getImage(url,timer){
            return new Promise((resolve,reject)=>{
                setTimeout(()=>{
                    this._download(url)
                    .then(resolve)
                    .catch(reject);
                },timer);
            });
        },
        async _download(url){
            const blob=this.downloaded.get(url);
            if(blob!==und){
                return blob;
            }
            if(url.indexOf('themify.me')===-1){
                return 1;
            }
            try{
                return await Themify.fetch('', 'blob', {
                    credentials:'omit',
                    method:'GET',
                    mode:'cors'
                }, url);
            }
            catch(e){
                if(e.status===404){
                    return false;
                }
                else{
                    return 1;//1 means try to download in the backend
                }
            }
        },
        async _batchDownload(images,timer){
            const batch=()=>{
                    const prms=[];
                    for(let i=0,len=images.length;i<len;++i){
                       prms.push(this._getImage(images[i],timer));
                    }
                    return Promise.all(prms);
                };
            
            let res;
            try{
                res=await batch();
            }
            catch(e){
                try{
                    res=await batch();//try again
                }
                catch(e){
                    res=Array(images.length).fill(1);//try to download in the backend
                }
            }
            return res;
        },
        async _batchUpload(formData,timer){
            let response;
            try{
                response=await this._upload(formData,timer);
            }
            catch(e){
                try{
                    response=await this._upload(formData,timer);//try again
                }
                catch(e){
                    try{
                        let arr=formData.getAll('blob');
                        for (let j=arr.length-1;j>-1;--j) {
                            delete arr[j].blob;
                        }
                        formData.set('blob',arr);
                        response=await this._upload(formData,timer);//try again without blob in backend
                    }
                    catch(e){

                    }
                }
            }
            return response;
        },
        _upload(formData,timer){
            return new Promise((resolve,reject)=>{
                setTimeout(()=>{
                    Themify.fetch(formData).then(res=>{
                        if(!res || !res.success){
                            reject(res.data);
                        }
                        else{
                            resolve(res);
                        }
                    })
                    .catch(reject);
                },timer);
            });
        },
        init(images,nonce,messages,chunkSize,action){
            return new Promise(async resolve=>{
                if(!Array.isArray(images)){
                    images=[...images];
                }
                let isVisible=true,
                    summ=0,
                    isSend1=false,
                    isSend2=false;
                if(!chunkSize){
                    chunkSize=2;
                }
                if(!action){
                    action='themify_upload_image';
                }
                await Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
                const len=images.length,
                    downLoadMsg=messages.download_images?messages.download_images.replaceAll('%to%',len):'',
                    upLoadMsg=messages.upload_images?messages.upload_images.replaceAll('%to%',len):'',
                    download_fail=messages.download_fail || '',
                    upload_fail=messages.upload_fail || '',
                    customData=messages.custom_data || '',
                    stop_webp=messages.stop_webp || '',
                    blobs=new Map(),
                    resp=new Map(),
                    timer=len>12?150:100,
                    prms=[],
                    _getImagesUrls=images=>{
                        const arr=[];
                        for(let i=0,len=images.length;i<len;++i){
                           let url=typeof images[i]==='string'?images[i].trim():images[i].url.trim();
                           if(url.indexOf('themes/ultra-agency-2/')!==-1){
                               url=url.replace('/themes/ultra-agency-2/files/','/themes/ultra-agency2/files/');
                           }
                           arr.push(url);
                        }
                        return arr;
                    },
                    downLoadNext=images=>{
                        images=_getImagesUrls(images);
                        for(let i=images.length-1;i>-1;--i){
                           
                            this._download(images[i]).then(b=>{
                                this.downloaded.set(images[i],b); 
                            });
                        }
                    },
                    preventReload=e=>{
                        e.preventDefault();
                        return e.returnValue='Are you sure';
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
                    _prepareUpload=(images,arrBlob)=>{
                        const formData = new FormData(),
                        postsData={},
                        imgArr=_getImagesUrls(images),
                        skip=new Set();
                        for(let i=arrBlob.length-1;i>-1;--i){
                            if(images[i]!==und){
                                let item=images[i],
                                    isString=typeof item==='string',
                                    url=imgArr[i],
                                    blob=arrBlob[i];
                                if(blob!==false && blob!=='skip'){
                                    formData.set(i,blob);
                                    postsData[i]={
                                        thumb:url
                                    };
                                    if(isString===false){
                                        if(item.id){
                                            postsData[i].post_id=item.id;
                                        }
                                        if(item.term_id){
                                            postsData[i].term_id=item.term_id;
                                        }
                                        if(item.data){
                                            postsData[i].data=item.data;
                                        }
                                    }
                                }
                                else{
                                    skip.add(url);
                                    resp.set(url,false);  
                                }
                            }
                        }
                        formData.set('action',action);
                        formData.set('nonce',nonce);
                        formData.set('postData',JSON.stringify(postsData));
                        if(customData){
                            formData.set('save_id',customData);
                        }
                        if(stop_webp){
                            formData.set('stop_webp',1);
                        }
                        return {formData:formData,skip:skip};
                    },
                    _uploadFinall=(r,images)=>{
                        if(!r || !r.success){
                            if(!r){
                                r='';
                            }
                            else if(r.data){
                                r=r.data;
                            }
                        }
                        else if(r.data){
                            r=r.data;
                        }
                        const result=new Map();
                        for(let i=images.length-1;i>-1;--i){
                            let v=this.downloaded.get(images[i]);
                            if(v!==false && v!=='skip'){
                                this.downloaded.delete(images[i]);
                            }
                            if(v!==false && v!=='skip' && (r && r[images[i]] && r[images[i]].id)){
                                resp.set(images[i],r[images[i]]);
                            }
                            else{
                                resp.set(images[i],false);
                                let msg= r && r[images[i]]?r[images[i]]:'';
                                result.set(images[i],msg);
                            }
                        }
                        return result;
                        
                    };
                    win.tfOn('beforeunload',preventReload)
                    .document.tfOn('visibilitychange', visibilitychange, {passive:true});
            
                    images=_chunks(images,chunkSize); 
                    if(wp && wp.heartbeat){
                        wp.heartbeat.interval(120);
                    }
                    prms[0]=new Promise(async resolve =>{
                        for(let i=0,arrLen=images.length;i<arrLen;++i){
                            try{
                                let res=[],
                                    imgArr=_getImagesUrls(images[i]),
                                    found=false;
                                    summ+=imgArr.length;
                                    
                                for(let j=imgArr.length-1;j>-1;--j){
                                    let data=resp.get(imgArr[j]);
                                    if(!data){
                                        found=true;
                                        break;
                                    }
                                    else{
                                        res[j]=data;
                                    }
                                }
                                if(found===true){
                                    isSend1=false;
                                    if(isVisible===true){
                                        if(summ>len){
                                            summ=len;
                                        }
                                        let f=doc.createDocumentFragment(),
                                            msg=doc.createElement('div'),
                                            img_wrap=doc.createElement('div');
                                            img_wrap.className='img_wrap';
                                            msg.className='msg_text';
                                            msg.innerHTML=downLoadMsg.replaceAll('%from%',summ);
                                        for(let j=imgArr.length-1;j>-1;--j){
                                            let img=new Image(50,50);
                                            img.className='tf_box';
                                            img.decoding='async';
                                            img.src=imgArr[j];
                                            
                                            try{
                                                await img.decode();
                                            }
                                            catch(e){
                                            }
                                            img_wrap.appendChild(img); 
                                            
                                        }
                                        f.append(msg,img_wrap);
                                        await TF_Notification.show('info',f);
                                    }
                                    if(images[i+1]!==und){
                                        try{
                                            downLoadNext(images[i+1]);
                                        }
                                        catch(e){

                                        }
                                    }
                                    try{
                                        res=await this._batchDownload(imgArr,timer);
                                    }
                                    catch(e){

                                    }
                                }
                                
                                let uploadData= _prepareUpload(images[i],res);
                                if(isVisible===true && uploadData.skip.size>0){
                                    let msg=[...uploadData.skip].join(', ');
                                    await TF_Notification.showHide('error',download_fail.replaceAll('%post%',msg),2000);
                                }
                                if(found===true && res.length>0){
                                    delete uploadData.skip;
                                    let r;
                                    if(isVisible===true && upLoadMsg!==''){
                                        let msg=TF_Notification.el.shadowRoot.querySelector('.msg_text');
                                        if(msg){
                                            if(summ>len){
                                                summ=len;
                                            }
                                            msg.innerHTML=upLoadMsg.replaceAll('%from%',summ);
                                        }
                                    }
                                    try{
                                        isSend1=true;
                                        await (new Promise(resolve=>{
                                            setTimeout(async()=>{
                                                r=await this._batchUpload(uploadData.formData,timer);
                                                resolve();
                                            },(isSend2===true?1000:10));
                                        }));
                                    }
                                    catch(e){

                                    }
                                    let result=_uploadFinall(r,imgArr);
                                    if(isVisible===true && result.size>0){
                                        for(let [url,msg] of result){
                                            await TF_Notification.showHide('error',upload_fail.replaceAll('%post%',url).replaceAll('%msg%',msg),2000);
                                        }
                                    }
                                }
                            }
                            catch(e){
                                
                            }
                        }
                        resolve();
                    });
                    if(images.length>1){
                        prms[1]=new Promise(async resolve =>{
                            for(let i=images.length-1;i>-1;--i){
                                try{
                                let res=[],
                                    found=false,
                                    imgArr=_getImagesUrls(images[i]);
                                    for(let j=imgArr.length-1;j>-1;--j){
                                        if(!resp.has(imgArr[j])){
                                            found=true;
                                            break;
                                        }
                                    }
                                    if(found===true){
                                        isSend2=false;
                                        if(images[i-1]!==und){
                                            try{
                                                downLoadNext(images[i-1]);
                                            }
                                            catch(e){

                                            }
                                        }
                                        try{
                                            res=await this._batchDownload(imgArr,timer);
                                        }
                                        catch(e){

                                        }
                                        isSend2=true;
                                        await (new Promise(resolve=>{
                                            setTimeout(async()=>{
                                                let uploadData=_prepareUpload(images[i],res),
                                                    r;

                                                if(res.length>0){
                                                    try{
                                                        r=await this._batchUpload(uploadData.formData,timer);
                                                    }
                                                    catch(e){

                                                    }
                                                    _uploadFinall(r,imgArr);
                                                }
                                                summ+=imgArr.length;
                                                
                                                resolve();
                                            },(isSend1===true?1300:10));
                                        }));
                                    }
                                }
                                catch(e){

                                }
                            }
                            resolve();
                        });
                    }
                    this.downloaded.clear();
                    win.tfOff('beforeunload',preventReload)
                    .document.tfOff('visibilitychange', visibilitychange, {passive:true});
                    await Promise.all(prms);
                    resolve(resp);
            });
        }
    };
})( Themify,document,window.top,undefined);