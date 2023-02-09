/**
 *  lax module
 */
;
((Themify, doc, win, vars)=>{
    'use strict';
	let lastY = 0,
		isAdded,
		prevBp,
		isInit;
	const transformFns={opacity:(a,b)=>{a.opacity=b},translate:(a,b)=>{a.transform+=` translate(${b}px, ${b}px)`},"translate-x":(a,b)=>{a.transform+=` translateX(${b}px)`},"translate-y":(a,b)=>{a.transform+=` translateY(${b}px)`},scale:(a,b)=>{a.transform+=` scale(${b})`},"scale-x":(a,b)=>{a.transform+=` scaleX(${b})`},"scale-y":(a,b)=>{a.transform+=` scaleY(${b})`},skew:(a,b)=>{a.transform+=` skew(${b}deg, ${b}deg)`},"skew-x":(a,b)=>{a.transform+=` skewX(${b}deg)`},"skew-y":(a,b)=>{a.transform+=` skewY(${b}deg)`},rotate:(a,b)=>{a.transform+=` rotate(${b}deg)`},"rotate-x":(a,b)=>{a.transform+=` rotateX(${b}deg)`},"rotate-y":(a,b)=>{a.transform+=` rotateY(${b}deg)`},"hue-rotate":(a,b)=>{a.filter+=` hue-rotate(${b}deg)`},blur:(a,b)=>{a.filter+=` blur(${b}px)`}},laxItems=[],intrp=(a,b)=>{let c=0;for(;a[c][0]<=b&&a[c+1]!==void 0;)c+=1;const d=a[c][0],e=a[c-1]===void 0?d:a[c-1][0],f=a[c][1],g=a[c-1]===void 0?f:a[c-1][1],h=Math.min(Math.max((b-e)/(d-e),0),1);return h*(f-g)+g},fnOrVal=a=>"("===a[0]?Function("return "+a)():parseFloat(a),laxAddElement=c=>{const b=c.getBoundingClientRect(),e={el:c,originalStyle:{transform:c.style.transform,filter:c.style.filter},transforms:{}};if(c.attributes["data-lax-anchor-top"]||(e.top=Math.floor(b.top)+win.scrollY,c.removeAttribute("data-lax-anchor-top")),c.attributes["data-lax-optimize"]){const a=c.getBoundingClientRect();c.setAttribute("data-lax-opacity",`${-a.height-1} 0, ${-a.height} 1, ${Themify.h} 1, ${Themify.h+1} 0`)}for(let d=c.attributes.length-1;-1<d;--d){let f=c.attributes[d],a=f.name;if(0===a.indexOf("data-lax")&&(a=a.replace("data-lax-",""),void 0!==transformFns[a])){let c=f.value.replace(/\s+/g," ").replaceAll("vw",Themify.w).replaceAll("vh",Themify.h).replaceAll("elh",b.height).replaceAll("elw",b.width);e.transforms[a]=c.split(",").map(a=>a.trim().split(" ").map(fnOrVal)).sort((b,a)=>b[0]-a[0])}}laxItems.push(e),laxUpdateElement(e)},laxUpdateElement=a=>{const{originalStyle:b,top:c,transforms:d,el:e}=a,f={transform:b.transform,filter:b.filter},g=c?c-lastY:lastY;for(let b in d)transformFns[b](f,intrp(d[b],g));if(0===f.opacity)e.style.opacity=0;else for(let a in f)e.style[a]=f[a]},laxUpdate=()=>{for(let a=laxItems.length-1;-1<a;--a)laxUpdateElement(laxItems[a])};

    Themify.on('tf_lax_init', items=>{
        const inner_h = Themify.h,
                transforms = ['scale', 'rotate', 'blur', 'opacity', 'translate-x', 'translate-y'],
                trLength = transforms.length - 1;

        let top = doc.body.getBoundingClientRect().top,
            resizeObserver=null;
        for (let i = items.length - 1; i > -1; --i) {
            let item = items[i];
            if (!item.hasAttribute('data-lax')) {
                continue;
            }
            item.removeAttribute('data-lax');
            let wrap = item.cloneNode(false),
                    computed = getComputedStyle(item),
                    pos = wrap.dataset.boxPosition,
                    zIndex = computed.getPropertyValue('z-index');
            wrap.className = 'tf_lax_done tf_rel';
            wrap.removeAttribute('style');
            if (zIndex && zIndex !== 'auto') {
                wrap.style.zIndex = zIndex;
            }
            if (pos) {
                wrap.style.transformOrigin = pos;
                item.removeAttribute('data-box-position');
            }
            if (wrap.hasAttribute('data-lax-opacity')) {
                wrap.className += ' tf_opacity';
                item.removeAttribute('data-lax-opacity');
            }

            if (wrap.hasAttribute('data-lax-rotate')) {
                wrap.style.width = computed.getPropertyValue('width');
                if(resizeObserver===null){
                    resizeObserver=new ResizeObserver((entries, observer)=> {
                        for (let i = entries.length - 1; i > -1; --i) {
                            let p=entries[i].target.parentNode,
                                w=parseInt(entries[i].contentRect.width);
                            if(parseInt(p.style.width)!==w){
                                p.style.width=w+'px';
                            }
                        }
                    });
                    resizeObserver.observe(item);
                }
            }
            if (wrap.hasAttribute('data-lax-scale')) {
                let entryContent = item.closest('.entry-content');
                if (entryContent !== null) {
                    entryContent.classList.add('themify-no-overflow-x');
                }
                if (isAdded !== true) {
                    isAdded = true;
                    doc.body.classList.add('themify-no-overflow-x');
                    top = doc.body.getBoundingClientRect().top;
                    doc.tfId('tb_inline_styles').textContent += '.themify-no-overflow-x{overflow-x:hidden}';
                }
            }
            let elTop = item.getBoundingClientRect().top - top;
            if ((elTop + 130) < inner_h) {
                elTop = elTop < 0 ? inner_h : Math.floor(elTop);
                wrap.dataset.laxAnchorTop=1;
                for (let j = trLength; j > -1; --j) {
                    let k = 'data-lax-' + transforms[j],
                            prop = wrap.getAttribute(k);
                    if (prop) {
                        prop = prop.split(',');
                        let end = prop[1].split(' ');
                        wrap.setAttribute(k, end[0] + ' ' + prop[0].split(' ')[1] + ',' + elTop + ' ' + end[1]);
                    }
                }
            }
            item.before(wrap);
            wrap.appendChild(item);
            laxAddElement(wrap);
        }
        if (isInit !== true) {
            isInit = true;
            const update = () => {
                if (Themify.is_builder_active === true) {
                    return;
                }
                const top = win.scrollY;
                if (lastY !== top) {
                    lastY = top;
                    laxUpdate();
                }
                requestAnimationFrame(update);
            },
                    onceScroll = () =>{
                        requestAnimationFrame(update);
                    };
            win.tfOn('scroll', onceScroll, {once: true, passive: true});
            Themify.on('tfsmartresize', e=>{
                const tablet_landscape = vars.breakpoints.tablet_landscape,
                        tablet = vars.breakpoints.tablet,
                        mobile = vars.breakpoints.mobile,
                        w=e.w || Themify.w;
                let bp = 'desktop';
                if (e.w <= mobile) {
                    bp = 'mobile';
                } else if (e.w <= tablet[1]) {
                    bp = 'tablet';
                } else if (e.w <= tablet_landscape[1]) {
                    bp = 'tablet_landscape';
                }
                if (prevBp !== bp) {
                    prevBp = bp;
                    for (let i = laxItems.length - 1; i > -1; --i) {
                        let computed = getComputedStyle(laxItems[i].el.firstChild),
                                zIndex = computed.getPropertyValue('z-index');
                        if (laxItems[i].el.style.zIndex !== zIndex) {
                            laxItems[i].el.style.zIndex = zIndex;
                        }
                        if (laxItems[i].el.hasAttribute('data-lax-rotate')) {
                            let w = computed.getPropertyValue('width');
                            if (laxItems[i].el.style.width !== w) {
                                laxItems[i].el.style.width = w;
                            }
                        }
                    }
                }
                laxUpdate(lastY);
            });
        }
    });

})(Themify, document, window, tbLocalScript);