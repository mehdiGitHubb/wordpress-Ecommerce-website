( ( Themify, doc,fwVars)=> {
    'use strict';
    const ThemifyPageOptions = {
        modal:null,
        wrap:null,
        active:false,
        url:false,
        fr:false,
        init(el) {
            this.loader('show');
            this.url = el.tagName==='A'?el.href:el.tfTag('A')[0].href;
            const modal = doc.createElement('div'),
                wrap = doc.createElement('div'),
                toolbar = doc.createElement('div'),
                title = doc.createElement('div'),
                close = doc.createElement('div'),
                save = doc.createElement('div');
            modal.id = 'tf_page_options_modal';
            modal.className = 'tf_scrollbar';
            wrap.className='tf_w tf_h tf_rel';
            toolbar.className='tf_page_options_toolbar';
            title.className='tf_page_options_title';
            title.innerText=fwVars.pg_opt_t;
           
            save.className='tf_page_options_save';
            save.tfOn('click',this.save.bind(this),{passive:true}).innerText=fwVars.pg_opt_updt;
            
            close.tfOn('click',this.close,{once:true,passive:true}).className='tf_page_options_close tf_close';
            toolbar.append(title,save,close);
            wrap.appendChild(toolbar);
            modal.appendChild(wrap);
            this.wrap = wrap;
            this.modal = modal;
            doc.body.appendChild(modal);
            return this.loadIframe();
        },
        loadIframe(){
            return new Promise((resolve,reject)=>{
                if(this.active){
                    resolve();
                    return;
                }
                const fr = doc.createElement('iframe');
                fr.className = 'tf_w tf_h';
                fr.tfOn('load',()=>{
                    this.loader('hide');
                    this.modal.classList.remove('updating');
                    const context=fr.contentDocument,
                        form = context.getElementById('post');
                    form.action = 'post.php?tf-meta-opts=update';
                    context.body.appendChild(form);
                    context.getElementById('wpwrap').remove();
                    doc.body.className += ' tf_page_options_active';
                    fr.contentWindow.document.documentElement.className += ' tf_scrollbar';
                    this.active=true;
                    resolve();
                },{passive:true,once:true})
                .tfOn('error',reject,{passive:true,once:true})
                .src = this.url;
                this.fr = fr;
                this.wrap.appendChild(fr);
            });
        },
        close(){
            window.location.reload();
        },
        save(){
            this.modal.className +=' updating';
            this.loader('show');
            this.fr.contentDocument.querySelector('#post').submit();
        },
        loader(act){
            if('show'===act){
                const loader = doc.createElement('div');
                loader.id = 'tb_alert';
                loader.className = 'tb_busy';
                doc.body.appendChild(loader);
            }else{
                const loader = doc.tfId('tb_alert');
                if(loader){
                    loader.remove();
                }
            }
        }
    };
    Themify.on('tf_page_options_init', el=> {
        return ThemifyPageOptions.init(el);
    },true);
})(Themify, document, themify_vars);
