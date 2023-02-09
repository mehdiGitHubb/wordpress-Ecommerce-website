/**
 * option module
 */
;
((Themify,doc,und)=>{
    'use strict';
    let isScrolled=false;
    const obServer= new IntersectionObserver((entity,self)=>{
            for(var i=entity.length-1;i>-1;--i){
                let id=entity[i].target.id,
                    items=doc.querySelectorAll('.module-toc a[href="#'+CSS.escape(encodeURI(id))+'"]'),
                    intersect=entity[i].isIntersecting===true;
                for(var j=items.length-1;j>-1;--j){
                    items[j].parentNode.classList.toggle('tb_toc_active',intersect);
                }
            }
        },
        {
            threshold:[0,1]
        }
    ),
    getcurrentBp=w=>{
        if(tbLocalScript){
            const points=tbLocalScript.breakpoints;
            let bp = 'desktop';
            if (w <= points.mobile) {
                bp = 'mobile';
            } else if (w <= points.tablet[1]) {
                bp = 'tablet';
            } else if (w <= points.tablet_landscape[1]) {
                bp = 'tablet_landscape';
            }
            return bp;
        }
    },
    isCollapsed=el=>{
        let bp=el.dataset.bp;
        if(bp && bp!=='n'){
            bp=bp==='tl'?'tablet_landscape':(bp==='t'?'tablet':'mobile');
            bp=tbLocalScript.breakpoints[bp];
            if(Array.isArray(bp)){
                bp=bp[1];
            }
            return bp>=Themify.w;
        }  
        return false;
    },
    click=e=>{
        let target = e.target?e.target.closest('.tf_fa,a'):null;
        if(target){
            if(target.tagName==='A'){
                const id=target.getAttribute('href').replace('#',''),
                item=doc.querySelector('#'+CSS.escape(decodeURI(id))+'.tb_toc_el');
                if(item){
                    e.preventDefault();
					if(Themify.is_builder_active===false){
						requestAnimationFrame(()=>{
							Themify.lazyScroll(Themify.convert(Themify.selectWithParent('[data-lazy]',item.closest('.module_row'))).reverse(),true).finally(()=>{
								const box=item.getBoundingClientRect(),
									scroll=window.scrollY;
								Themify.scrollTo(box.top+scroll-10,null,()=>{
									let header=doc.tfId('headerwrap'),
									offset=10;
									if(header && header.classList.contains('fixed-header')){
										offset+=header.getBoundingClientRect().bottom;
									}
									Themify.scrollTo(box.top+scroll-offset); 
									window.location.hash =id; 
								}); 
							});
						});
					}
                }
            }
            else{
                target=target.closest('.tb_toc_head,.tb_toc_cic_close,.tb_toc_cic');
                if(target){
                    const el = target.parentNode,
                    ul=el.tfTag('ul')[0],
                    isClosed=getComputedStyle(ul).getPropertyValue('max-height')==='0px',
                    cl=target.classList.contains('tb_toc_head')?'tb_tc_close':'tb_tc_sub_close';
                    ul.style.maxHeight = 'none';
                    const h=ul.clientHeight,
                        max=isClosed?h:0,
                        min=!isClosed?h:0;
                    ul.style.maxHeight = min+'px';
                    ul.tfOn('transitionend',e=>{
                        el.classList.toggle(cl,!isClosed);
                        if(max>0){
                            ul.style.maxHeight ='';
                        }
						ul.style.padding=max>0?'':'0px';
                    },{passive:true,once:true});
                    requestAnimationFrame(() => {
                        ul.style.maxHeight = max+'px';
						ul.style.padding=max>0?'':'0px';
                    });
                }
            }
        }
    },
    init = el =>{
        let in_tags=el.dataset.tags,
            ex_tags=el.dataset.excl,
            cont=el.dataset.cont || 'b',
            custom=el.dataset.sel;
        if(in_tags){
            let container;
            if(cont==='cust'){
                if(custom){
                    custom=custom.trim();
                    const last=custom.slice(-1);
                    if(last===',' || last==='.'  || last==='#'){
                        custom=custom.slice(0, -1);
                    }
                    if(custom){
                        try{
                            container=doc.querySelectorAll(custom);
                        }
                        catch(e){

                        }
                    }
                }
            }
            else{
                if(cont==='b' || cont==='b_c'){
                    container=el.closest('.themify_builder');
                    if(cont==='b_c'){
                        let parent=container;
                        while(1){
                            parent=parent.parentNode.closest('.themify_builder');
                            if(parent===null){
                                break;
                            }
                        }
                        if(parent===null){
                            parent=container.parentNode;
                        }
                        while(parent!==null && parent.nextElementSibling===null && parent.previousElementSibling===null){
                            parent=parent.parentNode;
                        }
                        if(parent!==null){
                            container=parent;
                        }
                    }
                }
                else if(cont==='r' || cont==='c'){
                    container=el.closest('.module_column');
                    if(cont==='r'){
                        container=container.closest('.module_row');
                    }
                }
                else if(cont==='doc'){
                    container=doc;
                }
                if(container){
                    container=[container];
                }
            }
            if(container && container.length>0){
                in_tags=in_tags.replace(/\|/g,',');
                let exclude='#comments,.tf_carousel,.loops-wrapper,.module-accordion,.widget-title,.module-tab,.module-toc,.module-gallery,.module-image,.nav,.module-service-menu';
                if(el.dataset.ex_m){
                    exclude+=',.module-title';
                }
                const allTags=new Set(),
                    isTree=el.classList.contains('tb_toc_tree'),
                    arr=[],
                    f=doc.createDocumentFragment(),
                    icon=el.tfClass('tpl_toc_ic')[0],
                    colapseIcon=el.tfClass('tpl_toc_cic')[0],
                    colapseIconMin=el.tfClass('tpl_toc_cic_close')[0],
                    min=el.dataset.min>1?parseInt(el.dataset.min):2,
                    maxLength=parseInt(el.dataset.maxh) || 32,
                    maxt=parseInt(el.dataset.maxt) || 0,
                    draw=(items,level)=>{
                        const fr=doc.createDocumentFragment(),
                            ul=doc.createElement('ul');
                            ul.className='tb_toc_lv_'+level+' tf_w tf_box';
                        if(level===1 && isCollapsed(el)){
                            ul.style.maxHeight ='0';
                            el.classList.add('tb_tc_close');
                        }
                        for(var i=0,len=items.length;i<len;++i){
                            let li=doc.createElement('li'),
                                a=doc.createElement('a'),
                                f2=doc.createDocumentFragment();
                            a.href='#'+items[i].id;
                            a.textContent=items[i].text;
                            if(icon!==und){
                                f2.appendChild(icon.content.cloneNode(true));
                            }
                            f2.appendChild(a);
                            if(items[i].childs.length>0){
                                ++level;
                                if(colapseIcon!==und){
                                    f2.appendChild(colapseIcon.content.cloneNode(true));
                                }
                                if(colapseIconMin!==und){
                                    f2.appendChild(colapseIconMin.content.cloneNode(true));
                                }
                                f2.appendChild(draw(items[i].childs,level));
                            }
                            li.appendChild(f2);
                            fr.appendChild(li);
                        }
                        ul.appendChild(fr);
                        return ul;
                    };
                if(ex_tags){
                    ex_tags=ex_tags.trim();
                    const last=ex_tags.slice(-1);
                    if(last===',' || last==='.'  || last==='#'){
                        ex_tags=ex_tags.slice(0, -1);
                    }
                }
                for(var i=0,len=container.length;i<len;++i){
                    let tags=container[i].querySelectorAll(in_tags);
                    for(var j=0,len2=tags.length;j<len2;++j){
                        if(!allTags.has(tags[j]) && tags[j].closest(exclude)===null && (!ex_tags || !tags[j].matches(ex_tags) || !tags[j].closest(ex_tags))){
                            allTags.add(tags[j]);
                            if(tags[j].offsetWidth>0 && tags[j].offsetHeight>0 && tags[j].getClientRects().length>0 ){
                                let textContent=tags[j].textContent.trim();
                                if(textContent){
                                    if(maxt>0 && textContent.length>maxt){
                                        textContent=textContent.substr(0,maxt);
                                    }
                                    let hasId=tags[j].dataset.isSet?null:tags[j].id,
                                        id=hasId || textContent.toLowerCase();
                                    id=id.replace(/(\r\n|\n|\r)/gm,' ').replace(/[`’~!@#$%^&*()|+\=?;:'",.<>\{\}\[\]\\\/]/gi, '').replace(/\s\s+/gu, ' ');
                                    if(!hasId){
                                        if(Themify.is_builder_active===true){
                                            tags[j].dataset.isSet=true;
                                        }
                                        id=id.replace(/\s/gu, '-');
                                        if(id.length>maxLength){
                                            id=id.split('-');
                                            if(id.length>1){
                                                for(var k=id.length-1;k>-1;--k){
                                                    id.splice(k,1);
                                                    if(k===1 || id.join('-').length<=maxLength){
                                                        break;
                                                    }
                                                }
                                            }
                                            id=id.join('-');
                                            if(id.length>maxLength){
                                                id=id.substring(0, maxLength);
                                            }
                                        }
                                        if(isNaN(id[0])===false){
                                           id='tb-'+id; 
                                        }
                                        let index=2,
                                            origId=id;
                                        while(doc.tfId(id)!==null){
                                           id=origId+'-'+index;
                                           ++index;
                                        }
                                    }
                                    tags[j].id=id;
                                    tags[j].classList.add('tb_toc_el');
                                    arr.push({
                                       id:encodeURIComponent(id),
                                       text:textContent,
                                       h:+tags[j].nodeName.replace('H',''),
                                       childs:[]
                                    });
                                    if(Themify.is_builder_active!==true){
                                        obServer.observe(tags[j]);
                                    }
                                }
                            }
                        }
                    }
                }
                allTags.clear();
                if(arr.length>min){
                    if(el.classList.contains('tb_toc_tree')){
                        for(i=1,len=arr.length;i<len;++i){
                            if(arr[i].h!==1){
                                if(arr[i-1].h<arr[i].h){
                                    arr[i-1].childs.push(arr[i]);
                                    arr[i].delete=true;
                                }
                                else{
                                    for(j=i-1;j>-1;--j){
                                        if(arr[j].h<arr[i].h){
                                            arr[j].childs.push(arr[i]);
                                            arr[i].delete=true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        for(i=len-1;i>-1;--i){
                            if(arr[i].delete===true){
                                arr.splice(i, 1); 
                            }
                        }
                    }
                    requestAnimationFrame(()=>{
                        const ul=el.tfClass('tb_toc_lv_1')[0],
                            head=el.tfClass('tb_toc_head')[0],
                            hash=isScrolled===false?window.location.hash:'';
                        isScrolled=true;
                        if(ul){
                          ul.remove();
                        }
                        el.appendChild(draw(arr,1));
						el.tfOn('click',click).classList.remove('tf_hide');
                        if(Themify.is_builder_active===false){
                            if(hash !== '' && hash!=='#'){
                                const scrollTo=el.querySelector('a[href="#'+hash.replace('#','')+'"]');
                                if(scrollTo){
                                    scrollTo.click();
                                }
                            }
                        }
                    });
                }
                else{
                    requestAnimationFrame(()=>{
                        el.classList.add('tf_hide');
                    });
                }
            }
        }
    };
    let prevBp=getcurrentBp(Themify.w);
    Themify.on('builder_load_module_partial tb_toc', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-toc')){
            return;
        }
        const items = Themify.selectWithParent('module-toc',el);
        for(let i=items.length-1;i>-1;--i){
            init(items[i]);
        }
    }).on('infiniteloaded',()=>{
        Themify.trigger('tb_toc');
    })
    .on('tfsmartresize',e=>{
        requestAnimationFrame(()=>{
            const bp=getcurrentBp(e.w);
            if(bp!==prevBp){
                prevBp=bp;
                const toc=doc.tfClass('tb_toc_lv_1');
                for(let i=toc.length-1;i>-1;--i){
                    let el=toc[i].parentNode,
                        isCollasped=isCollapsed(el);
                    toc[i].style.maxHeight =isCollasped?'0':'';
                    el.classList.toggle('tb_tc_close',isCollasped);
                }
            }
        });
    });
})(Themify,document,undefined);
