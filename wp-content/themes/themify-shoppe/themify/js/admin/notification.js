let TF_Notification;
((doc, Themify)=> {
    'use strict';
    let isHover=false,
        isVisible=true;
    const visibilitychange=()=>{
        isVisible=window.document.visibilityState==='visible';
        if(isVisible===true){
            isHover=false;
        }
    },
    hover=function(){
        if(isHover!==true && isVisible===true){
            isHover=true;
            this.classList.add('show');
            this.tfOn('pointerleave',e=>{
                isHover=false;
            },{passive:true,once:true});
        }
    },
    getIcon=ic=>{
        const ns = 'http://www.w3.org/2000/svg',
            use = doc.createElementNS(ns, 'use'),
            svg = doc.createElementNS(ns, 'svg');
        ic = 'tf-' + ic.trim().replace(' ', '-');
        svg.setAttribute('class', 'tf_fa ' + ic);
        use.setAttributeNS(null, 'href', '#' + ic);
        svg.appendChild(use);
        return svg;  
    };
    TF_Notification={
        el:null,
        async init(){
            let root=window.top.document.tfId('tf_notification_root'),
                prms=[];
            if(root===null){
                window.document.tfOff('visibilitychange', visibilitychange, {passive:true})
                .tfOn('visibilitychange', visibilitychange, {passive:true});
                root=doc.createElement('div');
                const types={info:'info',error:'tf_close',warning:'alert',done:'check'},
                    f=doc.createDocumentFragment();
                    f.appendChild(doc.querySelector('#tf_svg').cloneNode(true));
                root.id='tf_notification_root';
                root.className='tf_hide tf_w';
                for(let k in types){
                    let item=doc.createElement('div'),
                        iconWrap=doc.createElement('div'),
                        icon,
                        v=types[k];
                    if(v==='tf_close' || v==='tf'){
                        icon=doc.createElement('div');
                        icon.className=v;
                        if(v==='tf_close'){
                            icon.className+=' tf_w tf_h';
                        }
                    }
                    else{
                        icon=getIcon('ti-'+v);
                    }
                    item.className='notify '+k+' tf_abs_t tf_opacity';
                    iconWrap.className='icon tf_hide';
                    iconWrap.appendChild(icon);
                    item.appendChild(iconWrap);
                    f.appendChild(item);
                }
                root.attachShadow({
                    mode:'open'
                }).appendChild(f);
                prms.push(Themify.loadCss(Themify.url + 'css/base.min',null,null,root.shadowRoot.querySelector('.notify')));
                prms.push(Themify.loadCss(Themify.url + 'css/notification','tf_notify',null,root.shadowRoot.querySelector('.notify')));
                root.classList.add('tf_hidden');
                root.classList.remove('tf_hide');
                this.el=root;
                root.classList.remove('tf_hidden');
                doc.body.appendChild(root);
            }
            await Promise.all(prms);
            return root;
        },
        async show(type,text,duration){
            if(this.el===null){
                await this.init();
                return this.show(type,text,duration);
            }
            return new Promise(async resolve=>{
                const notify=this.el.shadowRoot.querySelector('.'+type),
                    setMsg=msg=>{
                        if(text instanceof DocumentFragment || text instanceof HTMLElement || text instanceof HTMLDocument){
                            msg.innerHTML='';
                            msg.appendChild(text);
                        }
                        else{
                            msg.innerHTML=text || '';
                        }
                    };
                if(notify===null || notify.classList.contains('show')){
                    if(notify){
                        setMsg(notify.tfClass('msg')[0]);
                    }
                    if(isVisible===true && duration>0){
                        setTimeout(resolve,duration);
                    }
                    else{
                        resolve();
                    }
                    return;
                }
                
                await this.showHide();
                const msg=doc.createElement('div');
                msg.className='msg';
                setMsg(msg);
                notify.appendChild(msg);
                if(isVisible===true){
                    const end=function(e){
                        this.tfOff('transitioncancel transitionend',end, {passive: true,once: true});
                        if(isVisible===true && duration>0){
                            setTimeout(resolve,duration);
                        }
                        else{
                            resolve();
                        }
                    };
                    notify
                        .tfOff('pointerenter',hover,{passive:true})
                        .tfOn('pointerenter',hover,{passive:true})
                    .tfOn('transitionend transitioncancel', end, {
                        passive: true,
                        once: true
                    })
                    .classList.add('show');
                }
                else{
                    notify.classList.add('show');
                    resolve();
                }
            });
        },
        async showHide(type,text,duration){
            if(this.el===null){
                await this.init();
                return this.showHide(type,text,duration);
            }
            return new Promise(async resolve=>{
                const notify=this.el.shadowRoot.querySelector('.show');
                if(type){
                    if(notify!==null && !notify.classList.contains(type)){
                        await this.showHide();
                    }
                    duration=duration || 3000;
                    await this.show(type,text);
                    if(isVisible===true){
                        setTimeout(()=>{
                            this.showHide().then(resolve);
                        },duration);
                    }
                    else{
                        await this.showHide();
                        resolve();
                    }
                    return;
                }
                if(notify===null){
                    resolve();
                    return;
                }
                const end=function(){
                    if(isHover===false){
                        this.tfOff('pointerenter',hover,{passive:true})
                        .tfOff('pointerleave',leave,{passive:true})
                        .tfOff('transitioncancel transitionend',end, {passive: true,once: true});
                        const msg=this.tfClass('msg')[0];
                        if(msg){
                            msg.remove();
                        }
                        resolve();
                    }
                },
                leave=function(){
                    if(isHover===true){
                        isHover=false; 
                        this.tfOff('transitioncancel transitionend',end, {passive: true,once: true})
                        .tfOn('transitionend transitioncancel', end, {
                            passive: true,
                            once: true
                        })
                        .classList.remove('show');
                    }
                };
                if(isVisible===true){
                    notify.tfOn('pointerleave',leave,{passive:true});
                    if(isHover===false){
                        notify.tfOn('transitionend transitioncancel', end, {
                            passive: true,
                            once: true
                        }).classList.remove('show');
                    }
                }
                else{
                    notify.classList.remove('show');
                    end.call(notify);
                }
            });
        }
    };
    
})(document, Themify);