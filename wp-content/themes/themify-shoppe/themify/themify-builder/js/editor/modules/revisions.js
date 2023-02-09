let TB_Revisions;
((api, Themify) => {
    'use strict';
    TB_Revisions = {
        async init(item) {
            const cl = item.classList;
            if (cl.contains('save_revision') || cl.contains('load_revision')) {
                try{
                    await api.LightBox.save();
                    if (cl.contains('save_revision')) {
                        await this.saveRevision();
                    } else {
                        await this.loadRevision(item);
                    }
                }
                catch(e){
                    api.Spinner.showLoader('spinhide');
                    throw e;
                }
            }
        },
        async loadRevision(item) {  
            const box = item.getBoundingClientRect(),
            data=await this.ajax({
                action: 'tb_load_revision_lists'
            }, 'text');
            
            api.LightBox.el.classList.add('tb_revision_lightbox');
            api.LightBox.setStandAlone(box.left, box.top);
            await api.Spinner.showLoader('done');
            const lb=await api.LightBox.open({
                contructor: true,
                loadMethod: 'html',
                save:false,
                data: {
                    revision: {
                        html: data.trim()
                    }
                }
            }),
            list=lb.tfClass('tb_revision_lists')[0],
            _click=e=>{
                const item=e.target.closest('.js-builder-restore-revision-btn,.js-builder-delete-revision-btn');
                if(item){
                    e.preventDefault();
                    e.stopPropagation();
                    if(item.classList.contains('js-builder-restore-revision-btn')){
                        this.restore(item.dataset.id);
                    }
                    else{
                        this.delete(item);
                    }
                }
            };
            if(list){
                list.tfOn(Themify.click,_click);
            }
            Themify.on('themify_builder_lightbox_close', lb => {
                if(list){
                    list.tfOff(Themify.click,_click);
                }
                lb.classList.remove('tb_revision_lightbox');
            }, true);
        },
        ajax(data, type) {
            const _default = {
                sourceEditor: 'visual' === api.mode ? 'frontend' : 'backend'
            };
            api.Spinner.showLoader();
            
            return api.LocalFetch(Object.assign({}, _default, data), type);
        },
        async saveRevision() {
            api.Spinner.showLoader('spinhide');
            try{
                const data=await api.LiteLightBox.prompt(themifyBuilder.i18n.enterRevComment);
                if(!data || data[0]!=='yes'){
                    throw 'canceled';
                }
                await api.LightBox.save();
                const ajaxData={
                        action: 'tb_save_revision',
                        rev_comment: data[1] || '',
                        data: JSON.stringify(api.Helper.clear(api.Builder.get().toJSON(true)))
                    },
                    revMsg=themifyBuilder.i18n.revSaved.replaceAll('%rev_title%',ajaxData.rev_comment);
                try{
                    const res=await this.ajax(ajaxData);
                    if(!res.success){
                        throw res.data;
                    }
                }
                catch(e){
                    try{
                        /* new attempt: send the Builder data as binary file to server */
                        ajaxData.data=new Blob( [ ajaxData.data ], { type: 'application/json' });
                        const res=await this.ajax(ajaxData);
                        if (!res.success) {
                            throw e;
                        }
                    }
                    catch(e){
                        throw e;
                    }
                }
                await Promise.all([api.Spinner.showLoader('done'),TF_Notification.showHide('done',revMsg.replaceAll('%post_title%',themifyBuilder.post_title),2500)]);
            }
            catch(e){
                if(e && e!=='canceled'){
                    await api.Spinner.showLoader('error');
                    api.LiteLightBox.alert(e);
                }
                throw e;
            }
        },
        restore(revID) {
            const restoreIt = () => {
                    this.ajax({
                        action: 'tb_restore_revision_page',
                        revid: revID
                    })
                    .then(data => {
                        if (data.builder_data) {
                            api.LightBox.close();
                            api.Builder.get().reLoad(data);
                        } else {
                            api.Spinner.showLoader('error');
                            api.LiteLightBox.alert(data.data);
                        }
                    }).catch(e => {
                        api.Spinner.showLoader('error');
                    });
                };

            api.LiteLightBox.confirm({
                msg: themifyBuilder.i18n.confirmRestoreRev,
                buttons: {
                    no: ThemifyConstructor.label.save_no,
                    yes: ThemifyConstructor.label.save
                }
            }).then(answer => {
                if ('yes' === answer) {
                    this.saveRevision().then(() => {
                        restoreIt();
                    })
                    .catch(e => {
                        
                    });
                } 
                else{
                    restoreIt();
                }
            });
        },
        delete(item) {
            api.LiteLightBox.confirm({
                msg: themifyBuilder.i18n.confirmDeleteRev
            })
            .then(answer => {
                if (answer === 'yes') {
                    api.Spinner.showLoader();
                    this.ajax({
                        action: 'tb_delete_revision',
                        revid: item.dataset.id
                    }).then(res => {
                        if (!res.success) {
                            api.Spinner.showLoader('error').then(()=>{
                                api.LiteLightBox.alert(res.data);
                            });
                        } else {
                            api.Spinner.showLoader('done').then(()=>{
                                item.closest('li').remove();
                            });
                        }
                    }).catch(() => {
                        api.Spinner.showLoader('error');
                    });
                }
            });
        }
    };

})(tb_app, Themify);