let LottieBrowse;
((doc, Themify,und)=> {
    'use strict';
    let observer,
        zip,
        holders=new WeakMap(),
        timer;
    const initObserver=async root=>{
        if(!observer){
            const {TF_Lottie}=await Themify.importJs('lottie');
            observer=new IntersectionObserver((entries, _self)=>{
                for (let i = 0,len=entries.length;i<len;++i) {
                    let target=entries[i].target,
                        anim=holders.get(target);
                    if(anim!==1){
                        if (entries[i].intersectionRatio>=.49) {
                            if(!anim){
                                holders.set(target,1);
                                zip.file(target.dataset.id+'.json').async('string').then(async res=>{
                                    const lottie = target.tfClass('lottie')[0],
                                    obj=new TF_Lottie(lottie,{
                                        actions:[{count:-1,state:'autoplay',tr:'autoplay',data:JSON.parse(res)}]
                                    });
                                    await obj.run();
                                    holders.set(target,obj);
                                    lottie.classList.remove('tf_loader');
                                });
                            }
                            else if(anim.player.isPaused) {
                                anim.player.play();
                            }
                        }
                        else if(anim && !anim.player.isPaused){
                            anim.player.pause();
                        }
                    }
                }

            }, {
                threshold:.5,
                root:root
            });
        }
    };
    LottieBrowse={ 
        el:null,
        run(){
            return new Promise(async(resolve,reject)=>{
                await this.show();
                resolve();
            });
        },
        async render(){
            if(this.el===null){
                        
                        const _CLICK_=Themify.click,
                            close=doc.createElement('button'),
                            search=doc.createElement('input'),
                            clearSearch=doc.createElement('button'),
                            menuWrap=doc.createElement('div'),
                            menuIcon=doc.createElement('button'),
                            menu=doc.createElement('ul'),
                            container=doc.createElement('div'),
                            overlay=doc.createElement('div'),
                            root=doc.createElement('div'),
                            category=doc.createElement('div'),
                            categoryFr=doc.createDocumentFragment(),
                            containerFr=doc.createDocumentFragment(),
                            getZipData=()=>{
                                if(!zip){
                                    return Themify.fetch('', 'blob', {
                                        credentials: 'omit',
                                        method: 'GET',
                                        mode: 'cors',
                                        headers: {
                                            'Content-Type': 'application/zip'
                                        }
                                    }, 'https://themify.me//public-api/lottie/lottie.zip');
                                }
                                return null;
                            },
                            prms=[
                                getZipData(),
                                Themify.importJs(Themify.url+'js/admin/jszip.min','3.10.1'),
                                Themify.loadJs('https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.0/lottie_light.min.js',!!window.lottie,false),
                                Themify.importJs('lottie')
                            ];
                        this.el=doc.createElement('div');
                        this.el.className='lightbox tf_hide';
                        
                        menuIcon.type='button';
                        menuIcon.className='menu_icon tf_rel';
                        menu.className='menu tf_box tf_hidden tf_opacity tf_scrollbar';
                        
                        //title.textContent=tfIconPicker.title;
                        search.type='text';
                        search.className='search tf_box';
                        search.required=true;
                        search.setAttribute('inputmode','search');
                        search.setAttribute('pattern','.*\\S.*');
                        //search.placeholder=tfIconPicker.search;
                        
                        close.className='tf_close';
                        clearSearch.type=close.type='button';
                        clearSearch.className='clear tf_close';
                        
                        menuWrap.className='menu_wrap flex tf_rel';
                        container.className='container tf_scrollbar';
                        overlay.className='overlay tf_abs_t tf_opacity tf_w tf_h tf_opacity tf_hide';
                        root.id='tf_lottie_root';
                        root.style.display='none';
                        
                        root.attachShadow({
                            mode:'open'
                        }).append( this.el,overlay);
                        
                        doc.body.appendChild(root);
                        prms.push(Themify.loadCss(Themify.url+'css/base.min','tf_base-css',false,this.el));
                        prms.push(Themify.loadCss(Themify.url+'css/admin/lottie-browse',null,false,this.el));
                        initObserver(container);
                        
                        const res=await Promise.all(prms),
                            jsZip = new JSZip();
                            if(!zip){
                                zip= await jsZip.loadAsync(res[0]);
                            }
                            const files=zip.files;
                            for(let f in files){
                                if(files[f].dir===true){
                                    let li=doc.createElement('li');
                                    li.textContent=f.replace('/','');
                                    categoryFr.appendChild(li);
                                }
                                else{
                                    let item=doc.createElement('div'),
                                    svg=doc.createElement('div'),
                                    title=doc.createElement('div'),
                                    full=f.replace('.json','');
                                    svg.className='tf_loader lottie';
                                    title.textContent=full.split('/')[1];
                                    title.className='title';
                                    item.dataset.id=full;
                                    item.className='item';
                                    item.append(svg,title);
                                    containerFr.appendChild(item);
                                    observer.observe(item);
                                }
                            }
                            
                        container.appendChild(containerFr);
                        menu.appendChild(categoryFr);
                        menuWrap.append(menuIcon,menu,search);
                        this.el.append(menuWrap,container,close);
                        
                        root.style.display='';
                        overlay.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.close();
                        },{passive:true});
                               
                        close.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.close();
                        },{passive:true});
                        
                        clearSearch.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.clearSearch();
                        },{passive:true});
                        return;
                        group.appendChild(navFr);
                        container.appendChild(tabFr);
                        
                        container.tfOn(_CLICK_,e=>{
                            const item=e.target.closest('button');
                            if(item){
                                e.stopPropagation();
                                if(item.closest('.cat_menu')){
                                    this.clearSearch();
                                    this.filter(item);
                                }
                                else if(item.hasAttribute('data-icon')){
                                    this.setIcon(item.dataset.icon,item.closest('section').dataset.id,item.closest('.group').id);
                                    this.close();
                                }
                            }
                        },{passive:true});
                 
                        
                     
                        
                        
                        search.tfOn('input',e=>{
                            e.stopPropagation();
                            this.filter(this.el.querySelector('#'+this.el.querySelector('input:checked').value+' .cat_menu .selected'));
                            this.search(e.currentTarget.value);
                        },{passive:true});
                        
                        titleWrap.append(title,close);
                        menuWrap.append(group,search,clearSearch);
                        this.el.append(titleWrap,menuWrap,container);
                        
                        
            }
        },
        destroy(){
            const items=this.el.tfClass('item');
            for(let i=items.length-1;i>-1;--i){
                let anim=holders.get(items[i]);
                if(anim){
                    anim.destroy();
                    holders.delete(anim);
                }
            }
            observer.disconnect();
            this.el.remove();
            holders=new WeakMap();
            if(Themify.isTouch){
                zip=null;
            }
            else{
                timer=setTimeout(()=>{
                    zip=timer=null;
                },60000);
            }
            observer=this.el=null;
        },
        show(selected){
            return new Promise(async resolve=>{
                if(!this.el){
                    await this.render(selected);
                }
                if(timer){
                    clearTimeout(timer);
                    timer=null;
                }
                const overlay=this.el.getRootNode().querySelector('.overlay');
                    overlay.classList.remove('tf_hide');
                    this.el.classList.remove('tf_hide');
                    requestAnimationFrame(()=>{
                        this.el.tfOn('transitionend',()=>{console.log('aaa');
                            resolve();
                        },{passive:true,once:true})
                        .style.top=0;
                        overlay.classList.remove('tf_opacity');
                    });
            });
        },
        close(){
            return new Promise(resolve=>{
                const overlay=this.el.getRootNode().querySelector('.overlay');
                this.el.tfOn('transitionend',e=>{
                    e.currentTarget.classList.add('tf_hide');
                    this.destroy();
                    resolve();
                },{passive:true,once:true});
                overlay.tfOn('transitionend',function(){
                    this.classList.add('tf_hide');
                    this.classList.remove('tf_opacity');
                },{passive:true,once:true})
                .classList.add('tf_opacity');
                this.el.style.top='';
            });
        }
    };
    
})(window.top.document, Themify,undefined);
export{
    LottieBrowse
};