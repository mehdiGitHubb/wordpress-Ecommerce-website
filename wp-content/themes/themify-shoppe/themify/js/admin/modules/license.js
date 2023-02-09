let TF_License;
((Themify,win,doc) =>{
    'use strict';
    TF_License={
        el:null,
        init(){
            if(!this.el){
                this.el=doc.tfId('license-modal');
            }
            if(!this.el){
                this.el=doc.createElement('div');

                const vars=themify_js_vars.license,
                    title=doc.createElement('h2'),
                    pLink=doc.createElement('p'),
                    p2=doc.createElement('p'),
                    p3=doc.createElement('p'),
                    p4=doc.createElement('p'),
                    nameLabel=doc.createElement('span'),
                    keyLabel=doc.createElement('span'),
                    nameInput=doc.createElement('input'),
                    keyInput=doc.createElement('input'),
                    buttonUpdate=doc.createElement('a'),
                    close=doc.createElement('a'),
                    closeIcon=doc.createElement('i'),
                    form=doc.tfId('themify') || doc.body;


                this.el.className='themify-modal tf_opacity tf_hide';    
                this.el.id='license-modal';

                title.innerHTML=vars.title;
                pLink.innerHTML=vars.link;
                nameLabel.textContent=vars.labels.name;
                keyLabel.textContent=vars.labels.key;
                buttonUpdate.textContent=vars.labels.update;


                keyInput.value=vars.key || '';
                nameInput.value=vars.username || '';

                nameInput.name='themify_username';
                keyInput.name='updater_licence';

                nameInput.type=keyInput.type='text';
                nameInput.autocomplete=keyInput.autocomplete='off';

                keyLabel.className=nameLabel.className='label';

                close.href=buttonUpdate.href='#';
                buttonUpdate.className='update-license button big-button themify_button';
                close.className='close';

                closeIcon.className='tf_close';

                p2.append(nameLabel,nameInput);
                p3.append(keyLabel,keyInput);
                p4.appendChild(buttonUpdate);
                close.appendChild(closeIcon);


                this.el.append(title,pLink,p2,p3,p4,close);

                close.tfOn('click',e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    this.close();
                });
                form.appendChild(this.el);
            }
            return this.show();
        },
        show(){
            return new Promise(resolve=>{
                if(this.el.classList.contains('tf_opacity')){
                    this.el.classList.remove('tf_hide');
                    requestAnimationFrame(()=>{
                        this.el.tfOn('transitionend',resolve,{passive:true,once:true})
                        .classList.remove('tf_opacity');
                    });
                }
                else{
                    resolve();
                }
            });
        },
        close(){
            return new Promise(resolve=>{
                this.el.tfOn('transitionend',e=>{
                    this.el.classList.add('tf_hide');
                    resolve();
                },{passive:true,once:true})
                .classList.add('tf_opacity');
            });
        },
        async update(){
            await Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
            try{
                const u = this.el.querySelector('[name="themify_username"]').value,
                    l = this.el.querySelector('[name="updater_licence"]').value,
                    data={
                        action:'themify_update_license',
                        nonce:themify_js_vars.nonce,
                        themify_username:u,
                        updater_licence:l
                    };
                if(u==='' || l===''){
                    throw Error(themify_js_vars.empty_li);
                }
                const res=await Themify.fetch(data);
                if(!res.success){
                    throw Error(res.data);
                }
                await this.close();
            }
            catch(e){
                TF_Notification.showHide('error',e,3000);
                throw e;
            }
        }
    };
    
})( Themify,window,document);
