((api, Themify, doc, topWindow) => {
    'use strict';
    let req,
        el,
        timer,
        isLoaded;
    const bind = () => {
        el.tfOn(Themify.click, e => {
            const target = e.target ? e.target.closest('.take,.rvs,.tf_close') : null;
            if (target) {
                e.stopPropagation();
                const host=e.currentTarget.getRootNode().host,
                    _close=()=>{
                        api.ToolBar.preventBeforeMsg=true;
                        if(api.mode==='visual'){
                            topWindow.location.reload();
                        }
                        else{
                            topWindow.location.href=el.querySelector('.buttons a.btn').href;
                        }  
                    };
                if (target.classList.contains('tf_close')) {
                    host.style.zIndex=9999;
                    
                    const pr= api.Builder.get().isSaved===false && api.undoManager.hasUndo()?
                    api.LiteLightBox.confirm({
                        msg: themifyBuilder.i18n.builderClose,
                        buttons:{
                            no:themifyBuilder.i18n.label.save_no,
                            yes:themifyBuilder.i18n.saveRevisionClose
                        }
                    }):Promise.resolve('no');
                    pr.then(answer=>{
                        host.style.zIndex='';
                        if(answer==='yes'){
                            Themify.triggerEvent(el.tfClass('rvs')[0],e.type);
                        }
                        else if (answer === 'no') { 
                            _close();
                        }
                    });
                } 
                else if (target.classList.contains('rvs')) {
                    if(isLoaded!==true){
                        api.Spinner.showLoader();
                    }
                    host.style.zIndex=9999;
                    api.ToolBar.initRevision({target:api.ToolBar.el.tfClass('save_revision')[0]}).then( _close)
                    .catch(e=>{
                        host.style.zIndex='';
                    });
                    isLoaded=true;
                } 
                else {
                    host.classList.add('tf_hide');
                    ajax(true).then(ticks)
                    .catch(e=>{//try again
                        ajax(true).then(ticks);
                    });
                }
            }
        }, {passive: true});
    },
    ajax = (take,count) => {
        return new Promise((resolve,reject)=>{
            Themify.requestIdleCallback(async()=>{
                if(window.navigator.onLine){
                    const ajaxData = {action: 'tb_update_tick'};
                    if (take) {
                        ajaxData.take = 1;
                    }
                    if(count){
                        ajaxData.count = 1;
                    }
                    try{
                        req = new AbortController();
                        const res=await api.LocalFetch(ajaxData, 'html', {signal: req.signal});
                        if (res) {
                            req.abort();
                            clearInterval(timer);
                            if(res.textContent!=='cancel'){
                                if (!el) {
                                    topWindow.document.body.appendChild(res);
                                    const tpl = topWindow.document.tfId('tmpl-tb_locked'),
                                            root = doc.createElement('div'),
                                            toolBarRoot = api.ToolBar.el.getRootNode(),
                                            baseCss = toolBarRoot.querySelector('#tf_base');
                                    root.id = 'tb_locked_root';
                                    root.className = 'tf_abs_t tf_w tf_h tf_hide';
                                    root.attachShadow({
                                        mode: 'open'
                                    }).appendChild(tpl.content);

                                    root.shadowRoot.prepend(baseCss.cloneNode(true));
                                    el = root.shadowRoot.tfId('tb_builder_restriction');
                                    tpl.replaceWith(root);
                                    bind();
                                }
                                el.getRootNode().host.classList.remove('tf_hide');
                            }
                            resolve(false);
                            return;
                        }
                    }
                    catch(e){
                        reject(e);
                        return;
                    }
                    resolve();
                }
            },-1,2000);
        });
    },
    ticks = () => {
        timer = setInterval(ajax, parseInt(themifyBuilder.ticks) * 1000);
    };

    Themify.on('themify_builder_ready', () => {
        setTimeout(()=>{
            ajax(false,true).then(res => {
                if (res !== false) {
                    ticks();
                }
            });
        },1500);
    }, true, api.is_builder_ready);
})(tb_app, Themify, document, window.top);