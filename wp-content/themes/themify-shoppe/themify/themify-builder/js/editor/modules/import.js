let TB_Import;
((api, Themify,doc) => {
    'use strict';
    TB_Import ={
        el:null,
        init(item) {
            const type = item.dataset.type;
            if (type) {
                if (type === 'file') {
                    this.file();
                } else {
                    this.post(item);
                }
            }
        },
        file() {
            api.Spinner.showLoader('spinhide');
            const fileInput=doc.createElement('input');
            api.Helper.loadJsZip();
            fileInput.type='file';
            fileInput.accept='.zip,.txt,.json';
            fileInput.tfOn('change',e=>{
                e.currentTarget.remove();
                this.fileImport(e.currentTarget.files[0]);
            },{passive:true,once:true})
            .click();
               
        },
        fileImport(file){
                return new Promise((resolve,reject)=>{
                    const opt = {
                        msg: themifyBuilder.i18n.dialog_import_page_post,
                        buttons: {
                            no: themifyBuilder.i18n.replace_builder,
                            yes: themifyBuilder.i18n.append_builder
                        }
                    };
                    api.LiteLightBox.confirm(opt).then(async answer => {
                        if(!answer){
                            resolve();
                            return;
                        }
                        try{
                            api.Spinner.showLoader();
                            const _callback=(buiderData,gsData)=>{
                                    try{
                                        buiderData=JSON.parse(buiderData);
                                        const json={custom_css:buiderData.custom_css};
                                        delete buiderData.custom_css;
                                        if(gsData){
                                            json.used_gs=JSON.parse(gsData);
                                        }
                                        else if(buiderData.used_gs){
                                            json.used_gs=buiderData.used_gs;
                                            delete buiderData.used_gs;
                                        }
                                        if(buiderData.builder_data){
                                            buiderData=buiderData.builder_data;
                                        }
                                        json.builder_data=buiderData;
                                        api.Builder.get().reLoad(json,answer!=='no').then(resolve).catch(reject);
                                    }
                                    catch(e){
                                        throw e;
                                    }
                                };
                            if(file.type==='text/plain' || file.type==='application/json'){
                                const reader = new FileReader();
                                reader.tfOn('loadend',function(e){
                                    try{
                                        if(this.readyState===FileReader.DONE){
                                            _callback(e.target.result);
                                        }
                                        else{
                                            throw '';
                                        }
                                    } 
                                    catch(e){
                                        api.Spinner.showLoader('error');
                                        TF_Notification.showHide('error',themifyBuilder.i18n.importBuilderNotExist);
                                        reject(e);
                                    }
                                },{passive:true,once:true})
                                .readAsText(file);
                            }
                            else if(file.type==='application/x-zip-compressed' || file.type==='application/zip'){
                                await api.Helper.loadJsZip();
                                const jsZip  = new JSZip(),
                                zip= await jsZip.loadAsync(file),
                                    files=zip.files;
                                if(files){
                                    const builderFileName='builder_data_export.txt',
                                           gsFileName='builder_gs_data_export.txt';
                                    if(files[builderFileName]!==undefined){
                                        const prm=[];
                                        prm.push(zip.file(files[builderFileName].name).async('text'));
                                        if(files[gsFileName]!==undefined){
                                            prm.push(zip.file(files[gsFileName].name).async('text'));
                                        }
                                        const res=await Promise.all(prm);
                                        _callback(res[0],res[1]);
                                    }
                                    else{
                                        throw Error(themifyBuilder.i18n.importBuilderNotExist);
                                    }
                                }
                                else{
                                    throw Error(themifyBuilder.i18n.zipFileEmpty);
                                }
                            }
                            else{
                                throw Error(themifyBuilder.i18n.importWrongFormat);
                            }
                        }
                        catch(e){
                            api.Spinner.showLoader('error');
                            if(e.message){
                                TF_Notification.showHide('error',e.message);
                            }
                            reject(e);
                        }
                    });
                });
        },
        async post(item) {
            try{
                await api.LightBox.save();
                api.LightBox.el.classList.add('tb_import_post_lightbox');
                const res=await api.LocalFetch({
                        action:'builder_import',
                        type: item.dataset.type
                    }
                ),
                box = item.closest('ul').getBoundingClientRect();
                api.LightBox.setStandAlone(box.left, box.top);
                await api.Spinner.showLoader('done');
                const lb=await api.LightBox.open({
                        loadMethod: 'html',
                        contructor: true,
                        save:false,
                        data: res
                    }),
                    submit = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const opt = {
                            msg: themifyBuilder.i18n.dialog_import_page_post,
                            buttons: {
                                no: themifyBuilder.i18n.replace_builder,
                                yes: themifyBuilder.i18n.append_builder
                            }
                        },
                        _ajaxData={
                            action:'builder_import_submit',
                            data:api.Forms.serialize('tb_options_import')
                        };
                        for(let i in _ajaxData.data){
                            if(!_ajaxData.data[i] || _ajaxData.data[i]==='0'){
                                delete _ajaxData.data[i];
                            }
                        }
                        if(Object.keys(_ajaxData.data).length>0){
                            api.LiteLightBox.confirm(opt).then(answer => {
                                if(answer){
                                    api.Spinner.showLoader();
                                    api.LocalFetch(_ajaxData).then(data => {
                                        if (data.builder_data !== undefined) {
                                            api.LightBox.close();
                                            api.Builder.get().reLoad(data,answer!=='no');
                                        } 
                                        else {
                                            api.Spinner.showLoader('error');
                                            TF_Notification.showHide('error',themifyBuilder.i18n.postBuilderNotExist);
                                        }
                                    })
                                    .catch(() => {
                                        api.Spinner.showLoader('error');
                                    });
                                }
                            });
                        }
                        else{
                            TF_Notification.showHide('error',themifyBuilder.i18n.importSelectPost);
                        }
                    };
                let form=lb.querySelector('#tb_submit_import_form');
                if(form){
                    form.tfOn(Themify.click, submit);
                }
                Themify.on('themify_builder_lightbox_close', lb => {
                    if(form){
                        form.tfOff(Themify.click, submit);
                    }
                    lb.classList.remove('tb_import_post_lightbox');
                    form=null;
                }, true);
            }
            catch(e){
                api.Spinner.showLoader('spinhide');
                throw e;
            }
        }
    };
})(tb_app, Themify, document);