/**
 * backgroundZooming for row/column/subrow
 */
;
(Themify =>{
    'use strict';
    let prevBp,
        req=null,
        timer=null,
        observer=null;
    const zoomingClass = 'active-zooming',
        workingItems=new Set(),
        pendingItems=new Set(),
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
            return getComputedStyle(el).getPropertyValue('--tbBg')==='zooming';
        },
        intersect=(entries, _self)=>{
            const trEnd=e=>{
                    const el=e.currentTarget;
                    el.style.transition='';
                    el.tfOff('transitionend transitioncancel',trEnd,{passive:true,once:true});
            };
            for(let i=entries.length-1;i>-1;--i){
                let st=entries[i].target.style;
                if(entries[i].target.isConnected){
                    if(entries[i].isIntersecting){
                        entries[i].target.tfOn('transitionend transitioncancel',trEnd,{passive:true,once:true});
                        st.transition='background-size 1.5s ease-in';
                        st.backgroundSize='100%';
                        _self.unobserve(entries[i].target);
                    }
                }
                else{
                    st.transition=st.backgroundSize='';
                    workingItems.delete(entries[i].target);
                    pendingItems.delete(entries[i].target);
                    _self.unobserve(entries[i].target);
                }
            }
            if(Themify.is_builder_active===false && pendingItems.size===0 && workingItems.size===0){
                Themify.off('tfsmartresize',resize);
                observer.disconnect();
                observer=null;
            }
        },
        resize=e=> {
           if(e){
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
                                        if(!el.classList.contains('builder-parallax-scrolling') && !el.classList.contains('builder-zoom-scrolling')){
                                            el.style.backgroundSize='';
                                        }
                                        observer.unobserve(el);
                                        pendingItems.add(el);
                                        workingItems.delete(el);
                                    } 
                                    else{
                                        el.style.backgroundSize='';
                                        observer.observe(el);
                                    }
                                }
                                else{
                                    workingItems.delete(el);
                                }
                            }
                            for (let el of pendingItems){
                                if(el.isConnected){
                                    if(isInBreakpoint(el)){
                                        el.style.backgroundSize='';
                                        workingItems.add(el);
                                        pendingItems.delete(el);
                                        observer.observe(el);
                                    } 
                                }
                                else{
                                    pendingItems.delete(el);
                                }
                            }
                            if(Themify.is_builder_active===false && pendingItems.size===0 && workingItems.size===0){
                                Themify.off('tfsmartresize',resize);
                                observer.disconnect();
                                observer=null;
                            }
                            req=timer=null;
                        }
                    });
                    
                },80);//should be twice higher than bgzoom_scroll
            }
        };
Themify.on('builder_load_module_partial', (el,isLazy)=>{
        let items;
        if(isLazy===true){
            if(!el.hasAttribute('data-zooming-bg')){
                return;
            }
            items=[el];
        }
        else{
            items = Themify.selectWithParent('[data-zooming-bg]',el);
        }
        if(observer===null){
            observer=new IntersectionObserver(intersect, {
                threshold: .3
            });
                Themify.on('tfsmartresize',resize);
            }
        for(let i=items.length-1;i>-1;--i){
            if(Themify.is_builder_active===false){
                items[i].removeAttribute('data-zooming-bg');
            }
            if(isInBreakpoint(items[i])){
                workingItems.add(items[i]);
                observer.observe(items[i]);
            }
            else{
                pendingItems.add(items[i]);
            }
        }
    });

})(Themify);
