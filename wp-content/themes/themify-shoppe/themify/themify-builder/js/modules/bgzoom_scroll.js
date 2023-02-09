/**
 * backgroundZoomScrolling for row/column/subrow
 */
;
((Themify,win) => {
    'use strict';
    let height = Themify.h,
        prevBp,
        req=null,
        timer=null,
        isInit=null;
    const className='builder-zoom-scrolling',
    workingItems=new Set(),
    pendingItems=new Set(),
    scroll=() =>{
        requestAnimationFrame(()=>{
            for (let el of workingItems){
                if(el.isConnected){
                    if(el.classList.contains(className)){
                        doZoom(el);
                    }
                }
                else{
                    workingItems.delete(el); 
                }
            }
        });
    },
    getcurrentBp=w=>{
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
    },
    isInBreakpoint=el=>{
        return getComputedStyle(el).getPropertyValue('--tbBg')==='zoom';
    },
    resize=e=> {
        if(e){
            height=e.h;
            if(timer){
                clearTimeout(timer);
            }
            timer=setTimeout(()=>{
                if (req) {
                    cancelAnimationFrame(req);
                }
                req = requestAnimationFrame(()=>{
                    const bp=getcurrentBp(e.w);
                    if(bp!==prevBp){
                        prevBp=bp;
                        for (let el of workingItems){
                            if(el.isConnected){
                                if(!isInBreakpoint(el)){
                                    el.classList.remove(className);
                                    if(!el.classList.contains('builder-parallax-scrolling')){
                                        el.style.backgroundSize='';
                                    }
                                    pendingItems.add(el);
                                    workingItems.delete(el);
                                }
                                else{
                                    doZoom(el);
                                }
                            }
                            else{
                                workingItems.delete(el);
                            }
                        }
                        for (let el of pendingItems){
                            if(el.isConnected){
                                if(isInBreakpoint(el)){
                                    el.classList.add(className);
                                    workingItems.add(el);
                                    pendingItems.delete(el);
                                    doZoom(el);
                                }
                            }
                            else{
                                pendingItems.delete(el);
                            }
                        }
                        if(Themify.is_builder_active===false && pendingItems.size===0 && workingItems.size===0){
                            Themify.off('tfsmartresize',resize);
                            win.tfOff('scroll', scroll,{passive:true,capture: true});
                            isInit=null;
                        }
                        req=timer=null;
                    }
                });
                    
            },40);//should be twice higher than parallax
        }
    },
    doZoom=el=> {
        const rect = el.getBoundingClientRect();
        if (rect.bottom >= 0 && rect.top <= height) {
            el.style.backgroundSize= (140 - (rect.top + rect.height) / (height + rect.height) * 40) + '%';
        }
    };
Themify.on('builder_load_module_partial', (el,isLazy)=>{
        let items;
        if(isLazy===true){
             if(!el.hasAttribute('data-zoom-bg')){
                return;
            }
            items=[el];
        }
        else{
            items = Themify.selectWithParent('[data-zoom-bg]',el);
        }
        if(items[0]!==undefined){
            for(let i=items.length-1;i>-1;--i){
                if(Themify.is_builder_active===false){
                    items[i].removeAttribute('data-zoom-bg');
                }
                if(isInBreakpoint(items[i])){
                    items[i].classList.add(className);
                    workingItems.add(items[i]);
                    doZoom(items[i]);
                }
                else{
                    pendingItems.add(items[i]);
                }
            }
            
            if(isInit===null){
                isInit=true;
                win.tfOn('scroll', scroll,{passive:true,capture: true});
                Themify.on('tfsmartresize',resize);
            }
        }
    });  

})(Themify,window);
