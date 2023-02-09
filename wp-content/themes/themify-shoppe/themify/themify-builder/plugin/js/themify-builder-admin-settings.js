((Themify,win,doc,vars) =>{
    'use strict';
    const init =async()=>{
        Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
        await Themify.loadJs(Themify.url+'js/admin/panel',!!win.ThemifyPanel);
        const el=doc.tfId('tb_panel'),
        fr = el.firstElementChild,
        fragment=doc.createDocumentFragment(),
        svg=doc.tfId('tf_svg').cloneNode(true);
        fragment.appendChild(svg);
        if (fr) { // shadowroot="open" isn't support
            el.attachShadow({
                mode: fr.getAttribute('shadowroot')
            }).appendChild(fr.content);
            fr.remove();
        }
        el.shadowRoot.prepend(fragment);
        const panel = new ThemifyPanel(el.shadowRoot.querySelector('.container'),vars.nonce,vars.data,vars.options,vars.labels);
        
        panel.el.querySelector('#main').tfOn('submit',async e=>{
            e.preventDefault();
            e.stopPropagation();
            const form = Array.from((new FormData(e.currentTarget))),
                data={};
            await Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
            for(let i=form.length-1;i>-1;--i){
                if(form[i][1]!=='' && form[i][1]!=='default'){
                    data[form[i][0]]=form[i][1];
                }
            }
            const ajaxData={
                action:'themify_builder_settings_save',
                nonce:vars.nonce,
                data:JSON.stringify(data)
            };
            try{
                await  TF_Notification.show('info','Saving');
                const res=await Themify.fetch(ajaxData);
                if (!res.success) {
                    throw res;
                }
            }
            catch(e){
                try{
                    /* new attempt: send the data as binary file to server */
                    ajaxData.data=new Blob( [ ajaxData.data ], { type: 'application/json' });
                    const res=await Themify.fetch(ajaxData);
                    if (!res.success) {
                        throw res;
                    }
                }
                catch(e){
                    throw e;
                }
            }
            await TF_Notification.showHide('done');
        });
    };
    if(win.loaded===true){
        init();
    }
    else{
        win.tfOn('load',init, {once:true, passive:true});
    }
    
})( Themify,window,document,tb_settings);