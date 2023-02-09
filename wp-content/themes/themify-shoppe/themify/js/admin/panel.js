let ThemifyPanel;
((Themify,und,doc) =>{
    'use strict';
    ThemifyPanel=class{
        constructor(el,nonce,vals,options,labels){
            this.el=el;
            this.vals=vals;
            this.options=options;
            this.labels=labels;
            this.nonce=nonce;
            this.bindings=new Map();
            this.topTabs();
            const observer=new IntersectionObserver(async(entries,_self)=>{
                for(let i=entries.length-1;i>-1;--i){
                    if(entries[i].isIntersecting===true){
                        _self.unobserve(entries[i].target);
                        const el=entries[i].target,
                            loader=doc.createElement('div');
                            loader.className='tf_loader';
                        try{
                            if(el.classList.contains('ajax_select')){
                                el.parentNode.after(loader);
                                const res=await Themify.fetch({
                                    action:el.dataset.ajaxAction,
                                    nonce : this.nonce
                                },'html');
                                if(el.firstChild){
                                    el.firstChild.remove();
                                }
                                el.appendChild(res);
                            }
                            else if(el.hasAttribute('data-codeditor')){
                                await Themify.loadJs(Themify.url+'js/admin/modules/codemirror/codemirror',!!window.ThemifyCodeMiror);
                                let obj=new ThemifyCodeMiror(el,el.dataset.codeditor);
                                await obj.run();
                            }
                        }
                        catch(e){
                        }; 
                        loader.remove();
                    }
                }
                if(entries.length===0){
                    _self.disconnect();
                }
            }),
            ajaxSelects=el.querySelectorAll('.ajax_select,[data-codeditor]');
            for(let i=ajaxSelects.length-1;i>-1;--i){
                observer.observe(ajaxSelects[i]);
            }
        }
        getIcon(icon, cl) {
            icon = 'tf-' + icon.trim().replace(' ', '-');
            const ns = 'http://www.w3.org/2000/svg',
                use = doc.createElementNS(ns, 'use'),
                svg = doc.createElementNS(ns, 'svg');
            let classes = 'tf_fa ' + icon;
            if (cl) {
                classes += ' ' + cl;
            }
            svg.setAttribute('class', classes);
            use.setAttributeNS(null, 'href', '#' + icon);
            svg.appendChild(use);
            return svg;
        }
        after(text){
            const t=doc.createElement('div');
            t.innerHTML=text;
            t.className='after';
            return t;
        }
        getHelp(text){
            const h=doc.createElement('div'),
                ic=doc.createElement('i'),
                content=doc.createElement('div');
            ic.appendChild(this.getIcon('ti-help'));
            ic.tabIndex='-1';
            content.className='help_content tf_hide tf_box';
            content.innerHTML=text;
            h.className='help tf_rel';
            h.append(ic,content);
            return h;
        }
        getDisabledMsg(msg){
            const warning=doc.createElement('div');
            if(msg!=='disabled'){
                warning.className='warning';
                warning.innerHTML=msg;
            }
            return warning;
        }
        binding(){
            if(this.bindings.size>0){
                for(let [id,bind] of this.bindings){
                    let el=this.el.querySelector('#'+id);
                    if(el){
                        let f=function(){
                            let items={},
                                main=this.closest('#main'),
                                show,
                                hide;
                            if(this.tagName==='SELECT'){
                                items=bind[this.value] || bind.any;
                            }
                            else if(this.type==='checkbox'){
                                items=this.checked? bind.checked:bind.not_checked;
                            }
                            show=items.show;
                            hide=items.hide;
                            if(show){
                                if(!Array.isArray(show)){
                                    show=[show];
                                }
                                for(let i=show.length-1;i>-1;--i){
                                    let item= main.querySelector('.'+show[i]);
                                    if(item){
                                       item.style.display='';
                                    }
                                }
                            }
                            if(hide){
                                if(!Array.isArray(hide)){
                                    hide=[hide];
                                }
                                for(let i=hide.length-1;i>-1;--i){
                                    let item= main.querySelector('.'+hide[i]);
                                    if(item){
                                       item.style.display='none';
                                    }
                                }
                            }
                        };
                        f.call(el);
                        el.tfOn('change',f,{passive:true});
                    }
                }
            }
            this.bindings.clear();
        }
        topTabs(){
            const liTabs=doc.createDocumentFragment(),
            sectionTabs=doc.createDocumentFragment(),
            radioTabs=doc.createDocumentFragment(),
            main=this.el.querySelector('#main'),
            tabs=doc.createElement('ul');
            let currentTab=new URL(window.location.href).searchParams.get('tab') || '';
            if(this.options[currentTab]===und){
                currentTab=Object.keys(this.options)[0];
            }
            tabs.className='top_tabs tf_overflow';
            for(let k in this.options){
                let li=doc.createElement('li'),
                section=doc.createElement('section'),
                radio=doc.createElement('input'),
                label=doc.createElement('label');
                radio.type='radio';
                radio.name='tab';
                radio.className='tab tf_hide';
                radio.id=label.htmlFor='tab-'+k;
                label.append(this.getIcon(this.options[k].icon),doc.createTextNode(this.options[k].label));
                section.id='tab-'+k+'-content';
                section.className='content tf_hide';
                if(k===currentTab){
                    radio.checked=true;
                }
                section.appendChild(this.render(this.options[k].options));
                li.appendChild(label);
                sectionTabs.appendChild(section);
                radioTabs.appendChild(radio);
                liTabs.appendChild(li);
            }
            this.el.tfOn('change',e=>{
                const target=e.target && e.target.type==='radio' && e.target.classList.contains('tab')? e.target:null;
                if(target){
                    const params=new URL(window.location.href);
                    params.searchParams.set('tab',target.id.replace('tab-',''));
                   history.replaceState(null, null, params);
                }
            },{passive:true});
            tabs.appendChild(liTabs);
            this.el.prepend(radioTabs);
            this.el.tfClass('header')[0].prepend(tabs);
            main.classList.add('tf_opacity');
            main.appendChild(sectionTabs);
            this.binding();
            main.classList.remove('tf_opacity');
        }
        render(options){
            const fr=doc.createDocumentFragment();
            for(let i=0,len=options.length;i<len;++i){
                let field=doc.createElement('div'),
                    fieldInput=doc.createElement('div'),
                    id=options[i].id,
                    type=options[i].type,
                    hasLabel=options[i].label!==und,
                    v=id!==und && this.vals[id]!==und?this.vals[id]:options[i].def;
                    field.className='field';
                    fieldInput.className='field_input field_'+type;
                if(hasLabel){
                    let size=type==='checkbox'?Object.keys(options[i].options).length:0;
                    if(id && type==='radio'){
                        id+='_'+Object.keys(options[i].options)[0];
                    }
                    let l=id?'label':'span',
                        label=doc.createElement(l),
                        labelDiv=l==='label'?doc.createElement('div'):null;
                    label.textContent=options[i].label;
                    if(id){
                        label.htmlFor=id;
                    }
                    if(type==='group' || type==='radio' || size>1){
                        field.className+=' alig_top';
                    }
                    if(labelDiv){
                        labelDiv.className='label';
                        labelDiv.appendChild(label);
                        label=labelDiv;
                    }
                    if(options[i].help){
                        label.appendChild(this.getHelp(options[i].help));
                        if(!labelDiv){
                            label.className='label';
                        }
                    }
                    field.appendChild(label);
                }
                try{
                    let f=type==='text' || type==='number' || type==='email' || type==='url' || type==='hidden'?'input':type,
                    res=this[f](options[i],v,type);
                    if(hasLabel || type!=='group'){
                        fieldInput.appendChild(res);
                    }
                    else{
                        field.className+=' field_group';
                        field.appendChild(res); 
                    }
                }
                catch(e){
                    console.log(type,e);
                }
                if(hasLabel ||type!=='group'){
                    if(!options[i].disabled && options[i].desc){
                        field.classList.add('alig_top');
                        let desc=doc.createElement('div');
                        desc.className='description';
                        desc.innerHTML=options[i].desc;
                        fieldInput.appendChild(desc);
                    }
                    field.appendChild(fieldInput);
                }
                if(options[i].disabled){
                    field.classList.add('alig_top','has_error');
                    fieldInput.appendChild(this.getDisabledMsg(options[i].disabled));
                }
                fieldInput.className+=' count_'+fieldInput.childElementCount;
                if(options[i].wrap_class){
                    field.className+=' '+options[i].wrap_class;
                }
                field.className+=' count_'+field.childElementCount;
                fr.appendChild(field);
                if(options[i].bind && id){
                    this.bindings.set(id,options[i].bind);
                }
            }
            return fr;
        }
        select(data,v){
            const select_wrap = doc.createElement('div'),
                   select = doc.createElement('select'),
                   d = doc.createDocumentFragment(),
                   fr = doc.createDocumentFragment();
               select_wrap.className = 'selectwrapper tf_inline_b tf_vmiddle tf_rel';
               select.className= 'tf_scrollbar';
                if (data.class !== und) {
                   select.className += ' ' + data.class;
                }
                if(data.id){
                   select.name=select.id=data.id;
                }
                if(data.disabled){
                    select.disabled=true;
                }
                else if(v && data.ajax){
                    data.options={[v]:''};
                }
                if(data.options){
                    for(let k in data.options){
                        let opt = doc.createElement('option');
                        opt.value = k;
                        opt.text = data.options[k];
                        if (k===v) {
                            opt.selected = true;
                        }
                        fr.appendChild(opt);
                    }
                    select.appendChild(fr);
                }
                select_wrap.appendChild(select);
                d.appendChild(select_wrap);
                if(data.after){
                    d.appendChild(this.after(data.after));
                }
                if(data.ajax){
                    select.className+=' ajax_select';
                    select.dataset.ajaxAction=data.ajax;
                }
               return d;
        }
        expand(data){
            const expand=doc.createElement('div');
            expand.className='expand';
            expand.appendChild(this.render(data.options));
            return expand;
        }
        group(data){
            return this.render(data.options);
        }
        radio(data,v){
            const fr = doc.createDocumentFragment(),
                options=data.options;
                for(let k in options){
                    let input = doc.createElement('input'),
                        label=doc.createElement('label');
                    input.type='radio';
                    input.value = k;
                    input.name=data.id;
                    label.textContent=options[k];
                    label.htmlFor=input.id=data.id+'_'+k;
                    if(data.disabled){
                        input.disabled=true;
                    }
                    else if (k===v) {
                        input.checked = true;
                    }
                    fr.append(input,label);
                }
                if(data.after){
                    f.appendChild(this.after(data.after));
                }
                return fr;
        }
        checkbox(data,v){
            const f = doc.createDocumentFragment(),
                options=data.options;
                for(let k in options){
                    let input = doc.createElement('input'),
                        label=doc.createElement('label');
                    input.type='checkbox';
                    input.value = 1;
                    input.name=k;
                    label.textContent=options[k];
                    label.htmlFor=input.id=k+'_checkbox';
                    if(data.disabled){
                        input.disabled=true;
                    }
                    else if (k===v) {
                        input.checked = true;
                    }
                    f.appendChild(input);
                    if(options[k]){
                        f.appendChild(label);
                    }
                }
                if(data.after){
                    f.appendChild(this.after(data.after));
                }
                return f;
        }
        input(data,v,type){
            const f=doc.createDocumentFragment(),
                input=doc.createElement('input');
            if(data.disabled){
                input.disabled=true;
            }
            else{
                input.value=v!==undefined &&v!==null?v:'';
            }
            input.type=type || 'text';
            if(data.id){
                input.name=data.id;
            }
            if(type!=='hidden'){
                if(data.id){
                    input.id=data.id;
                }
                if(data.min || type==='number'  || type==='range'){
                    input.min=parseInt(data.min) || 0;
                }
                else{
                    input.className='tf_w';
                }
                if(data.max){
                    input.max=parseInt(data.max);
                    input.defaultValue=input.value=v;
                }
                if(data.step){
                    input.step=data.step;
                }
                if(data.placeholder){
                    input.placeholder=data.placeholder;
                }
            }
            if(data.class){
                input.className+=' '+data.class;
            }
            f.appendChild(input);
            if(data.after){
                 f.appendChild(this.after(data.after));
            }
            return f;
        }
        textarea(data,v){
            const f=doc.createDocumentFragment(),
                text=doc.createElement('textarea');
            text.id=text.name=data.id;
            text.className='tf_w';
            if(data.class){
                text.className+=' '+data.class;
            }
            if(data.disabled){
                text.disabled=true;
            }
            else{
                text.value=v || '';
            }
            f.appendChild(text);
            if(data.after){
                f.appendChild(this.after(data.after));
            }
            if(data.codeditor){
                text.dataset.codeditor=data.codeditor;
            }
            return f;
        }
        slider(data,v){
            const f=doc.createDocumentFragment(),
                obj=Object.assign(data);
                if(!v && !data.def){
                    v=data.max;
                }
                delete obj.after;
                const number=this.input(obj,v,'number');
                delete obj.id;
                const slider=this.input(obj,v,'range');
                f.append(slider,number);
                if(data.after){
                    f.appendChild(this.after(data.after));
                }
                for(let inputs=f.querySelectorAll('input'),i=inputs.length-1;i>-1;--i){
                    inputs[i].tfOn('input change',e=>{
                        let el=e.currentTarget,
                            v=parseInt(el.value),
                            min=parseInt(el.min),
                            max=parseInt(el.max),
                            nextInput=el.nextElementSibling || el.previousElementSibling;
                        if(e.type==='change' && (v>max || v<min)){
                            v=v>max?max:min;
                            el.value=v;
                        }
                        nextInput.value=v;
                    },{passive:true});
                }
                return f;
        }
        toggle(data,v){
            const label=doc.createElement('label'),
            ch=doc.createElement('input'),
            toggle=doc.createElement('div'),
            val=data.value || 1;
            ch.className='toggle_switch';
            ch.type='checkbox';
            ch.id=ch.name=data.id;
            ch.value=val;
            if(data.disabled){
                ch.disabled=true;
            }
            else{
                if(data.opp){
                    ch.className+=' opposite';
                }
                if(v==val){
                    ch.checked=true;
                }
            }
            toggle.className='switch_label tf_rel tf_box';
            toggle.dataset.on=data.show || this.labels.en;
            toggle.dataset.off=data.hide || this.labels.dis;
            if(data.class){
                toggle.className+=' '+data.class;
            }
            label.append(ch,toggle);
            return label;
        }
        replace_url(data,v){
            const f=doc.createDocumentFragment(),
                findField=this.render([data.find]),
                replaceField=this.render([data.replace]),
                btn=doc.createElement('button'),
                span=doc.createElement('span'),
                confirmText=data.confirm;
                span.textContent=data.text;
                btn.type='button';
                btn.className='clear_cache find_replace';
                if(data.class){
                    btn.className+=' '+data.class;
                }
                btn.dataset.done=data.done;
                btn.dataset.clearing=data.clearing;
                btn.append(this.getIcon('ti-eraser'),span);
                btn.tfOn('click',async e=>{
                    if(confirm(confirmText)){
                        const bt=e.currentTarget;
                        if(!bt.disabled){
                            bt.disabled=true;
                            Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
                            const sp=bt.tfTag('span')[0],
                            defaultText=sp.textContent;
                            sp.textContent=bt.dataset.clearing;
                            await Themify.loadJs(Themify.url+'js/admin/modules/find-replace',!!win.TF_Replace);
                            input=bt.closest('.field_replace_url').tfTag('input'),
                            find=input[0].value.trim(),
                            replace=input[1].value.trim();
                            try{
                                await TF_Replace(find,replace,this.nonce);
                            }
                            catch(e){

                            }
                            sp.textContent=defaultText;
                            bt.disabled=false;
                        }
                    }
                },{passive:true});
                f.append(findField,replaceField,btn);
                return f;
        }
        clear_cache(data,v){
            const f=doc.createDocumentFragment(),
                btn=doc.createElement('button'),
                span=doc.createElement('span');
                span.textContent=data.text;
                btn.className='clear_cache';
                btn.type='button';
                if(data.class){
                    btn.className+=' '+data.class;
                }
                btn.dataset.action=data.action;
                btn.dataset.done=data.done;
                btn.dataset.clearing=data.clearing;
                btn.append(this.getIcon('ti-eraser'),span);
                f.appendChild(btn);
                if(data.network){
                    const div=doc.createElement('div');
                    div.className='clear_cache_network';
                    div.appendChild(this.checkbox({options:data.network}));
                    f.appendChild(div);
                }
                if(data.after){
                    f.appendChild(this.after(data.after));
                }
                btn.tfOn('click',async e=>{
                    const bt=e.currentTarget,
                        sp=bt.tfTag('span')[0],
                        defaultText=sp.textContent;
                    if(!bt.disabled){
                        sp.textContent=bt.dataset.clearing;
                        bt.disabled=true;
                        await Themify.loadJs(Themify.url+'js/admin/notification',!!win.TF_Notification);
                        TF_Notification.show('info',bt.dataset.clearing);
                        try{
                            const res=await Themify.fetch({
                                nonce: this.nonce,
                                action: bt.dataset.action,
                                all:btn.parentNode.querySelector('input:checked')?1:0
                            });
                            if(!res.success){
                                throw res;
                            }
                            await TF_Notification.showHide('done',bt.dataset.done,1500);
                        }
                        catch(e){
                            const msg=e.data?e.data:e;
                            await TF_Notification.showHide('error',msg);
                        }
                        sp.textContent=defaultText;
                        bt.disabled=false;
                    }
                    
                },{passive:true});
                return f;
        }
    }
    
})(Themify,undefined,document);