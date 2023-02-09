;
var Themify;
((win, doc, und, $,vars)=>{
    'use strict';
    const OnOf=(on,el,ev,f,p)=>{
        ev=typeof ev==='string'?ev.split(' '):ev;
        for(let i=ev.length-1;i>-1;--i){
            on===true?el.addEventListener(ev[i],f,p):el.removeEventListener(ev[i],f,p);
        }
        return el;
    };
    Node.prototype.tfClass=function(sel){
        return this.getElementsByClassName(sel);
    };
    Node.prototype.tfTag=function(sel){
        return this.getElementsByTagName(sel);
    };
    Node.prototype.tfId=function(id){
        return this.getElementById(id);
    };
    EventTarget.prototype.tfOn=function(ev,f,p){
        return OnOf(true,this,ev,f,p);
    };
    EventTarget.prototype.tfOff=function(ev,f,p){
        return OnOf(null,this,ev,f,p);
    };       
    Themify = {
        events:new Map(),
        cssLazy:new Map(),
        jsLazy:new Map(),
        fontsQueue:new Set(),
        device: 'desktop',
        lazyScrolling: null,
        observer: null,
        triggerEvent(el, type, params,isNotCustom) {
            let ev;
            if (isNotCustom===true || type === 'click' || type === 'submit' || type === 'input' || type==='resize' || (type === 'change' && !params) || type.indexOf('pointer') === 0 || type.indexOf('touch') === 0 || type.indexOf('mouse') === 0) {
                if (!params) {
                    params = {};
                }
                if (params.bubbles === und) {
                    params.bubbles = true;
                }
                if (params.cancelable === und) {
                    params.cancelable = true;
                }
                ev = ((type==='click' && win.PointerEvent) || type.indexOf('pointer') === 0)?new PointerEvent(type, params):(type==='click' || type.indexOf('mouse') === 0?new MouseEvent(type, params):new Event(type, params));
				Object.defineProperty(ev, 'target', {value: params.target || el, enumerable: true});
            } else {
                ev = new win.CustomEvent(type, {detail: params});
            }
            el.dispatchEvent(ev);
            return this;
        },
        on(ev, f, once,check) {
            if(check===true){
                f();
                if(once===true){
                    return this;
                }
            }
            ev = ev.split(' ');
            const len = ev.length;
            for (let i = len-1; i>-1;--i) {
                let events=this.events.get(ev[i]) || new Map();
                events.set(f,!!once);
                this.events.set(ev[i],events);
            }
            return this;
        },
        off(ev, f) {
            const items=this.events.get(ev);
            if(items!==und){
                if(f){
                    items.delete(f);
                    if(items.size===0){
                        this.events.delete(ev);
                    }
                }
                else{
                    this.events.delete(ev);
                }
            }
            return this;
        },
        trigger(ev, args) {
            const items = this.events.get(ev),
                proms=[];
                if (items !== und) {
                    if(args!==und){
                        if(!Array.isArray(args)){
                            args=[args];
                        }
                    }
                    for(let [f,once] of items){
                        try{
                            let pr=f.apply(null, args);
                            if(pr!==und && pr instanceof Promise){
                                proms.push(pr);
                            }
                        }
                        catch(e){
                            console.error(e);
                        }
                        if(once===true){
                            items.delete(f);
                        }
                    }
                    if(items.size===0){
                        this.events.delete(ev);
                    }
                }
                if(proms.length===0 && args!==und){
                    proms.push(Promise.resolve(args));
                }
                return Promise.all(proms);
        },
        requestIdleCallback(callback, timeout,timer2) {
            if (win.requestIdleCallback) {
                win.requestIdleCallback(callback, {timeout: timeout});
            } else {
                timeout=timer2>0?timer2:(timeout<0?2500:timeout);
                setTimeout(callback, timeout);
            }
        },        
        parseVideo(url) {
            const m = url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?\/?([A-Za-z0-9._%-]*)(\&\S+)?/i),
                attrs = {
                    type: m !== null ? (m[3].indexOf('youtu') > -1 ? 'youtube' : (m[3].indexOf('vimeo') > -1 ? 'vimeo' : false)) : false,
                    id: m !== null ? m[6] : false
                };
            if('vimeo' === attrs.type && m[8]){
                attrs.h = m[8];
            }
            return attrs;
        },
        hash(s) {
            let hash = 0;
            for (let i = s.length - 1; i > -1; --i) {
                hash = ((hash << 5) - hash) + s.charCodeAt(i);
                hash = hash & hash; // Convert to 32bit integer
            }
            return hash;
        },
        scrollTo(val, speed, complete, progress) {
            if (!speed) {
                speed = 800;
            }
            if (!val) {
                val = 0;
            }
            const doc = $('html,body'),
                    hasScroll = doc.css('scroll-behavior') === 'smooth';
            if (hasScroll) {
                doc.css('scroll-behavior', 'auto');
            }
            doc.stop().animate({
                scrollTop: val
            }, {
                progress: progress,
                duration: speed,
                done() {
                    if (hasScroll) {
                        doc.css('scroll-behavior', '');
                    }
                    if (complete) {
                        complete();
                    }
                }
            });
        },
        imagesLoad(items) {
            return new Promise(resolve=>{
                if (items !== null) {
                    if (items.length === und) {
                        items = [items];
                    }
                    const prms=[];
                    for(let i=items.length-1;i>-1;--i){
                        let images=items[i].tagName==='IMG'?[items[i]]:items[i].tfTag('img');
                        for(let j=images.legnth-1;j>-1;--j){
                            if(!images[j].complete){
                                let elem=images[j];
                                prms.push(new Promise((resolve, reject) => {
                                    elem.onload = resolve;
                                    elem.onerror = reject;
                                    elem=null;
                                }));
                            }
                        }
                    }
                    Promise.all(prms).finally(()=>{
                        resolve(items[0]); 
                    });
                }
                else{
                    resolve();
                }
            });
        },
        updateQueryString(d,a,b){
            b||(b=win.location.href);const e=new URL(b,win.location),f=e.searchParams;null===a?f.delete(d):f.set(d,a);let g=f.toString();return''!==g&&(g='?'+g),b.split('?')[0]+g+e.hash;
        },
        selectWithParent(selector, el) {
            let items = null;
            const isCl = selector.indexOf('.') === -1 && selector.indexOf('[') === -1,
                    isTag = isCl === true && (selector === 'video' || selector === 'audio' || selector === 'img');
            if (el && el[0] !== und) {
                el = el[0];
            }
            if (el) {
                items = isCl === false ? el.querySelectorAll(selector) : (isTag === true ? el.tfTag(selector) : el.tfClass(selector));
                if ((isCl === true && el.classList.contains(selector)) || (isCl === false && el.matches(selector)) || (isTag === true && el.tagName.toLowerCase() === selector)) {
                    items = this.convert(items, el);
                }
            } else {
                items = isCl === false ? doc.querySelectorAll(selector) : (isTag === true ? doc.tfTag(selector) : doc.tfClass(selector));
            }
            return items;
        },
        convert(items, el) {
            let l = items.length;
            const arr = new Array(l);
            while (l--) {
                arr[l] = items[l];
            }
            if (el) {
                arr.push(el);
            }
            return arr;
        },
        init() {
            this.is_builder_active = doc.body.classList.contains('themify_builder_active');
            this.body = $('body');
            const windowLoad = ()=>{
                        this.w = win.innerWidth;
                        this.h = win.innerHeight;
                        this.isRTL = doc.body.classList.contains('rtl');
                        this.isTouch = !!(('ontouchstart' in win) || navigator.msMaxTouchPoints > 0);
                        this.lazyDisable = this.is_builder_active === true || doc.body.classList.contains('tf_lazy_disable');
                        this.click=this.isTouch?'pointerdown':'click';
                        if (this.isTouch) {
                            const ori = screen.orientation !== und &&  screen.orientation.angle!==und? screen.orientation.angle : win.orientation,
                                    w = ori === 90 || ori === -90 ? this.h : this.w;
                            if (w < 769) {
                                this.device = w < 681 ? 'mobile' : 'tablet';
                            }
                        }
                        requestAnimationFrame(()=>{
                            if(this.urlArgs!==null){
                                this.urlArgs='&'+(new URLSearchParams({media:this.urlArgs})).toString();
                            }
                            this.cssUrl = this.url + 'css/modules/';
                            this.builder_url=vars.theme_v?(this.url+'themify-builder/'):this.url.substring(0,this.url.slice(0, -1).lastIndexOf('/') + 1);
                     
                            if (vars.done !== und) {
                                this.cssLazy = new Map(Object.entries(vars.done));
                            }
                            this.requestIdleCallback(()=> {this.mobileMenu();},40);
                            this.trigger('tf_init');
                            win.loaded = true;
                            if (!vars.is_admin) {
                                if (vars.theme_v) {
                                    vars.theme_url=this.url.split('/').slice(0,-2).join('/');
                                    this.loadJs(vars.theme_url+'/js/themify.script', null, vars.theme_v);
                                }
                                if (this.is_builder_active === false) {
                                    const prms=win.tbLocalScript && doc.tfClass('module_row')[0]?this.loadJs(this.builder_url+'js/themify.builder.script'):Promise.resolve();
                                    prms.then(()=>{
                                        this.lazyLoading();
                                    });
                                    this.requestIdleCallback(()=> {this.commonJs();},-1);
                                    this.requestIdleCallback(()=> {this.tooltips();},110);
                                }
                                this.requestIdleCallback(()=> {this.wc();},50);
                                this.requestIdleCallback(()=> {this.touchDropDown();},60);
                                setTimeout(()=>{
                                    this.requestIdleCallback(()=> {this.gallery();},100);
                                    this.googleAnalytics();
                                }, 800);
                            }
                            this.requestIdleCallback(()=> {this.resizer();},-1,2000);
                        });
                    };  
            const sc=doc.currentScript,
            url=new URL(sc.src,win.location.origin);
            this.is_min = url.href.indexOf('.min.js')!==-1;
            this.v=url.searchParams.get('ver') || sc.dataset.v;
            this.urlArgs=url.searchParams.get('media') || null;//need for cdn
            this.urlHost=url.hostname;
            this.url = url.href.split('js/main.')[0].trim();
            this.cdnPlugin=sc.dataset.pl.split('?')[0].replace('/fake.css','');
            if (doc.readyState === 'complete' || this.is_builder_active === true) {
                this.requestIdleCallback(windowLoad,50);
            } else {
                win.tfOn('load', windowLoad, {once: true, passive: true});
            }
        },
        async initComponents(el, isLazy) {
            if (isLazy === true && el.tagName === 'IMG') {
                return;
            }
            let items;
            const loading={VIDEO:'video',AUDIO:'audio',auto_tiles:'autoTiles',tf_carousel:'carousel',themify_map:'map','[data-lax]':'lax',masonry:'isotop',tf_search_form:'ajaxSearch',tf_sticky_form_wrap:'stickyBuy'},
                    prms=[];
            for(let cl in loading){
                items=null;
                if (isLazy === true) {
                    if(cl==='tf_sticky_form_wrap'){
                        if(el.id===cl){
                            items = [el];
                        }
                    }
                    else if(cl==='[data-lax]'){
                        if(el.hasAttribute('data-lax')){
                            items = [el];
                        }
                    }
                    else if (el.tagName === cl || el.classList.contains(cl) || (cl==='tf_search_form' && el.classList.contains('tf_search_icon'))) {
                        items = [el];
                    }
                } else {
                    items = this.selectWithParent(cl.toLowerCase(), el);
                }
                if (items !== null && items.length > 0) {
                    prms.push(this[loading[cl]](items));
                }
            }
            items=null;
            if (isLazy === true) {
                if (el.classList.contains('wp-embedded-content')) {
                    items = [el];
                } else {
                    prms.push(this.wpEmbed(el.tfClass('wp-embedded-content')));
                }
            } else {
                items = this.selectWithParent('wp-embedded-content', el);
            }
            if (items !== null && items.length > 0) {
                prms.push(this.wpEmbed(items));
            }
            items=null;
            this.largeImages(el);
            return Promise.all(prms);
        },
        fixedHeader(options) {
            if (!this.is_builder_active) {
                return new Promise((resolve,reject)=>{
                    this.loadJs('fixedheader').then(()=>{
                        this.requestIdleCallback(()=> {
                            this.trigger('tf_fixed_header_init', options);
                            resolve();
                        },50);
                    })
                    .catch(reject);
                });
            }
        },
        async lax(items, is_live) {
            if ((is_live === true || !this.is_builder_active) && items.length>0) {
                await this.loadJs('lax');
                this.trigger('tf_lax_init', [items]);
            }
        },
        async video(items) {
            if (items && items.length>0) {
                for(let i=items.length-1;i>-1;--i){
                    let src=items[i].dataset.poster;
                    if(src){
                        let img=new Image();
                        img.src=src;
                        img.decode()
                        .catch(()=>{})
                        .finally(()=>{
                            items[i].poster=src;
                        });
                        items[i].removeAttribute('data-poster');
                    }
                }
                await this.loadJs('video-player');
                this.trigger('tf_video_init', [items]);
            }
        },
        async audio(items, options) {
            if (items && items.length>0) {
                await Promise.all([this.loadCss('audio','tf_audio'),this.loadJs('audio-player')]);
                this.trigger('tf_audio_init', [items, options]);
            }
        },
        async sideMenu(items, options) {
            if (items && (items.length>0 || items.length===und)) {
                await this.loadJs('themify.sidemenu');
                this.trigger('tf_sidemenu_init', [items, options]);
            }
        },
        async edgeMenu(menu) {
            if(doc.tfClass('sub-menu')[0]!==und){
                await this.loadJs('edge.Menu');
                this.trigger('tf_edge_init', menu);
            }
        },
        async sharer(type, url, title) {
            await this.loadJs('sharer');
            this.trigger('tf_sharer_init', [type, url, title]);
        },
        async autoTiles(items) {
            await this.loadJs('autoTiles');
            this.trigger('tf_autotiles_init', [items]);
        },
        async map(items) {
            await this.loadJs('map');
            this.trigger('tf_map_init', [items]);
        },
        async carousel(items, options) {
            if (items) {
                await this.loadJs('themify.carousel');
                this.trigger('tf_carousel_init', [items, options]);
            }
        },
        async infinity(container, options) {
                if (!container || container.length === 0 || this.is_builder_active === true || (!options.button && options.hasOwnProperty('button')) || (options.path && typeof options.path === 'string' && doc.querySelector(options.path) === null)) {
                    return;
                }
                // there are no elements to apply the Infinite effect on
                if (options.append && doc.querySelector(options.append)===null) {
                    // show the Load More button, just in case.
                    if (options.button) {
                        options.button.style.display = 'block';
                    }
                    return;
                }
                await this.loadJs('infinite');
                this.trigger('tf_infinite_init', [container, options]);
        },
        async isotop(items, options) {
            if (items.length === und) {
                items = [items];
            }
            const res = [];
            for (let i = items.length - 1; i > -1; --i) {
                let cl = items[i].classList;
                if (!cl.contains('masonry-done') && (!cl.contains('auto_tiles') || !cl.contains('list-post') || !items[i].previousElementSibling || items[i].previousElementSibling.classList.contains('post-filter'))) {
                    res.push(items[i]);
                }
            }
            if (res.length > 0) {
                await Promise.all([
                    this.loadJs('jquery.isotope.min',typeof $.fn.packery !== 'undefined','3.0.6'), 
                    this.loadJs('isotop')
                ]);
                this.trigger('tf_isotop_init', [res, options]);
            }
        },
        fonts(icons) {
            return new Promise((resolve,reject)=>{
                if (icons) {
                    if (typeof icons === 'string') {
                        icons = [icons];
                    } else if (!Array.isArray(icons)) {
                        if (icons instanceof jQuery) {
                            icons = icons[0];
                        }
                        icons = this.selectWithParent('tf_fa', icons);
                    }
                } else {
                    icons = doc.tfClass('tf_fa');
                }
                const Loaded = new Set(),
                        needToLoad = [],
                        parents = [],
                        svg = doc.tfId('tf_svg').firstChild,
                        loadedIcons = svg.tfTag('symbol');
                for (let i = loadedIcons.length - 1; i > -1; --i) {
                    Loaded.add(loadedIcons[i].id);
                    Loaded.add(loadedIcons[i].id.replace('tf-',''));
                }
                for (let i = icons.length - 1; i > -1; --i) {
                    if(icons[i].tagName!==und && icons[i].tagName!=='svg'){
                        continue;
                    }
                    let id = icons[i].classList ? icons[i].classList[1] : icons[i];
                    if (id && !Loaded.has(id)) {
                        if (!this.fontsQueue.has(id)) {
                            this.fontsQueue.add(id);
                            let tmp = id.replace('tf-', ''),
                                    tmp2 = tmp.split('-');
                            if (tmp2[0] === 'fas' || tmp2[0] === 'far' || tmp2[0] === 'fab') {
                                let pre = tmp2[0];
                                tmp2.shift();
                                tmp = pre + ' ' + tmp2.join('-');
                            }
                            needToLoad.push(tmp);
                        }
                        if (icons[i].classList) {
                            let p = icons[i].parentNode;
                            p.classList.add('tf_lazy');
                            parents.push(p);
                        }
                    }
                }
                if (needToLoad.length > 0) {
                    const time = this.is_builder_active ? 5 : 2000;
                    setTimeout( ()=>{
                        this.fetch({action: 'tf_load_icons',icons: JSON.stringify(needToLoad)},null,{credentials:'omit'}).then(res => {
                                    const fr = doc.createDocumentFragment(),
                                            ns = 'http://www.w3.org/2000/svg',
                                            st = [];
                                    for (let i in res) {
                                        let s = doc.createElementNS(ns, 'symbol'),
                                                p = doc.createElementNS(ns, 'path'),
                                                k = 'tf-' + i.replace(' ', '-'),
                                                viewBox = '0 0 ';
                                        viewBox += res[i].vw!==und && res[i].vw!==''?res[i].vw:'32';
                                        viewBox +=' ';
                                        viewBox +=res[i].vh!==und && res[i].vh!==''?res[i].vh:'32';
                                        s.id = k;
                                        s.setAttributeNS(null, 'viewBox', viewBox);
                                        p.setAttributeNS(null, 'd', res[i].p);
                                        s.appendChild(p);
                                        fr.appendChild(s);
                                        if (res[i].w) {
                                            st.push('.tf_fa.' + k + '{width:' + res[i].w + 'em}');
                                        }
                                    }
                                    svg.appendChild(fr);
                                    if (st.length > 0) {
                                        let css = doc.tfId('tf_fonts_style');
                                        if (css === null) {
                                            css = doc.createElement('style');
                                            css.id = 'tf_fonts_style';
                                            svg.appendChild(css);
                                        }
                                        css.textContent += st.join('');
                                    }
                                    this.fontsQueue.clear();
                                    for (let i = parents.length - 1; i > -1; --i) {
                                        if (parents[i]) {
                                            parents[i].classList.remove('tf_lazy');
                                        }
                                    }
                                    resolve();
                                }).catch(reject);
                    }, time);
                }
                else{
                    resolve();
                }
            });
        },
        commonJs() {
            return new Promise((resolve,reject)=>{
                if(doc.tfTag('tf-lottie')[0]){
                    this.loadJs('https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.10.0/lottie_light.min.js',!!win.lottie,false);
                   // this.importJs('lottie');
                }
                this.requestIdleCallback(()=> {
                    this.fonts().then(resolve).catch(reject);
                }, 200);
                if (vars.commentUrl) {
                    this.requestIdleCallback(()=> {
                        if (!win.addComment && vars.commentUrl && doc.tfId('cancel-comment-reply-link')) {
                            this.loadJs('comments');
                        }
                    }, -1,3000);
                }
                if (vars.wp_emoji) {
                    this.requestIdleCallback(()=> {
                        const emoji = doc.createElement('script');
                        emoji.text = vars.wp_emoji;
                        requestAnimationFrame(()=>{
                            doc.head.appendChild(emoji);
                            win._wpemojiSettings.DOMReady = true;
                        });
                        vars.wp_emoji = null;
                    }, -1,4000);
                }
            });
        },
        loadJs(src, test, version, async) {
            const origSrc=src;
            let pr=this.jsLazy.get(origSrc);
            if(pr===und){
                pr=new Promise((resolve,reject)=>{
                    if(test===true){
                        requestAnimationFrame(resolve);
                        return;
                    }
                    if(vars.plugin_url!==this.cdnPlugin && src.indexOf(vars.plugin_url)===0){
                        src=src.replace(vars.plugin_url,this.cdnPlugin);
                    }
                    const isLocal=src.indexOf(this.urlHost) !== -1,
                        s = doc.createElement('script');
                    if(isLocal===true || (src.indexOf('http')===-1 && src.indexOf('//')!==0)){
                        if(src.indexOf('.js')===-1){
                            src+='.js';
                        }
                        if (version !== false){
                            if(isLocal===false){
                                src=this.url+'js/modules/'+src;
                            }
                            if (this.is_min === true && src.indexOf('.min.js') === -1) {
                                src = src.replace('.js', '.min.js');
                            }
                            if (src.indexOf('ver=')===-1) {
                                if(!version){
                                    version = this.v;
                                }
                                src+='?ver=' + version;
                            }
                            if(this.urlArgs!==null){
                                src+=this.urlArgs;
                            }
                        }   
                    }
                    s.async=async===false?false:true;
                    s.tfOn('load', e=> {
                        requestAnimationFrame(resolve);
                    }, {passive: true, once: true})
                    .tfOn('error', reject, {passive: true, once: true});
                    s.src=src;
                    requestAnimationFrame(()=>{
                        doc.head.appendChild(s);
                    });
                });
                this.jsLazy.set(origSrc,pr);
            }
            return pr;
        },
        loadCss(href,id, version, before, media) {
            if(!id){
                id = 'tf_'+this.hash(href);
            }
            let prms=this.cssLazy.get(id);
            if (prms===und) {
                prms=new Promise((resolve,reject)=>{
                    const d=before?before.getRootNode():doc,
                        el = d.tfId(id);
                        if(el!==null && el.media!== 'print'){
                            resolve();
                            return;
                        }
                    if(vars.plugin_url!==this.cdnPlugin && href.indexOf(vars.plugin_url)===0){
                        href=href.replace(vars.plugin_url,this.cdnPlugin);
                    }
                const ss = doc.createElement('link'),
                        self = this,
                        onload = function () {
                            if (!media) {
								media = 'all';
							}
                            this.media=media;
                            const key = this.id,
                                    checkApply = ()=>{
                                        const sheets = this.getRootNode().styleSheets;
                                        let found = false;
                                        for (let i = sheets.length - 1; i > -1; --i) {
                                            if (sheets[i].ownerNode!==null && sheets[i].ownerNode.id === key) {
                                                found = true;
                                                break;
                                            }
                                        }
                                        if (found === true) {
                                            resolve();
                                        }
                                        else {
                                            requestAnimationFrame(()=>{
                                                checkApply();
                                            });
                                        }
                                    };
                                requestAnimationFrame(()=>{
                                    checkApply();
                                });
                        },
                        isLocal=href.indexOf(this.urlHost) !== -1;
                        if(isLocal===true || (href.indexOf('http')===-1 && href.indexOf('//')!==0)){
                            if(href.indexOf('.css')===-1){
                                href+='.css';
                            }
                            if(version !== false){
                                if(isLocal===false){
                                    href=this.url+'css/modules/'+href;
                                }
                                if (this.is_min === true && href.indexOf('.min.css') === -1) {
                                    href = href.replace('.css', '.min.css');
                                }
                                if (href.indexOf('ver=')===-1) {
                                    if(!version){
                                        version = this.v;
                                    }
                                    href+='?ver=' + version;
                                }
                                if(this.urlArgs!==null){
                                    href+=this.urlArgs;
                                }
                            }
                        }
                        ss.rel='stylesheet';
                        ss.media='print';
                        ss.id=id;
                        ss.href=href;
                        ss.setAttribute('fetchpriority', 'low');
                        if ('isApplicationInstalled' in navigator) {
                            ss.onloadcssdefined(onload);
                        } else {
                            ss.tfOn('load', onload, {passive: true, once: true});
                        }
                        ss.tfOn('error', reject, {passive: true, once: true});
                        let ref = before;
                        requestAnimationFrame(()=>{
                                if (!ref || !ref.parentNode) {
                                    const critical_st = doc.tfId('tf_lazy_common');
                                    ref = critical_st ? critical_st.nextSibling : doc.head.firstElementChild;
                                }
                                ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
                        });
                    });
                    this.cssLazy.set(id,prms);
            }
            else if(prms===true){
                prms=Promise.resolve();
                this.cssLazy.set(id,prms);
            }
            else if(before){//maybe it's shadow root,need to recheck
                const el=before.getRootNode().tfId(id);
                if(el===null){
                    this.cssLazy.delete(id);
                    return this.loadCss(href,id, version, before, media);
                }
            }
            return prms;
        },
        gallery() {
            const lbox = this.is_builder_active === false && vars.lightbox ? vars.lightbox : false;
            if (lbox !== false && lbox.lightboxOn !== false && !this.jsLazy.has('tf_gal')) {
                this.jsLazy.set('tf_gal',true);
                const self = this,
                        hash = win.location.hash.replace('#', ''),
                        args = {
                            extraLightboxArgs: vars.extraLightboxArgs,
                            lightboxSelector: lbox.lightboxSelector || '.themify_lightbox',
                            gallerySelector: lbox.gallerySelector || '.gallery-item a',
                            contentImagesAreas: lbox.contentImagesAreas,
                            i18n: lbox.i18n || [],
                            disableSharing:lbox.disable_sharing
                        };
                let isWorking = false;
                const isImg =  url=>{
                    return url?url.match(/\.(gif|jpg|jpeg|tiff|png|webp|apng)(\?fit=\d+(,|%2C)\d+)?(\&ssl=\d+)?$/i):null;
                },
                openLightbox = el=> {
                    if (isWorking === true) {
                        return;
                    }
                    doc.tfOff('click',globalClick);
                    isWorking = true;
                    const link = el.getAttribute('href'),
                        loaderP = doc.createElement('div'),
                        loaderC = doc.createElement('div');
                    loaderP.className = 'tf_lazy_lightbox tf_w tf_h';
                    if (isImg(link)) {
                        loaderP.textContent = 'Loading...';
                        const img = new Image();
                        img.decoding = 'async';
                        img.src = link;
                        img.decode();
                    } else {
                        loaderC.className = 'tf_lazy tf_w tf_h';
                        loaderP.appendChild(loaderC);
                    }
                    doc.body.appendChild(loaderP);
                    
                    Promise.all([
                        self.loadCss('lightbox','tf_lightbox'),
                        self.loadJs('lightbox.min','undefined' !== typeof $.fn.magnificPopup),
                        self.loadJs('themify.gallery')
                    ])
                    .then(()=>{
                        self.trigger('tf_gallery_init', args);
                        el.click();
                    }).
                    finally(()=>{
                        loaderP.remove();
                    });
                };
                const globalClick=e=>{
                    const el=e.target?e.target.closest('a'):null;
                    if(el){
                        const galSel=args.gallerySelector,
                        contentSel=args.contentImagesAreas,
                        lbSel=args.lightboxSelector;
                        if(el.closest(lbSel) || (isImg(el.getAttribute('href')) && ((contentSel && el.closest(contentSel)) || (galSel && (el.matches(galSel) || el.closest(galSel)) && !el.closest('.module-gallery'))))){
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            openLightbox(el);
                        }
                    }
                };
                doc.tfOn('click',globalClick);
                if (hash && hash !== '#') {
                    const h=decodeURI(hash);
                    let item = doc.querySelector('img[alt="' + h + '"],img[title="' + h + '"]');
                    if (item) {
                        item = item.closest('.themify_lightbox');
                        if (item) {
                            openLightbox(item);
                        }
                    }
                }
            }
        },
        lazyLoading(parent) {
            if (this.lazyDisable === true) {
                return;
            }
            if (!parent) {
                parent = doc;
            }
            const items = (parent instanceof HTMLDocument || parent instanceof HTMLElement) ? parent.querySelectorAll('[data-lazy]') : parent,
                    len = items.length;
            if (len > 0) {
                const lazy =  (entries, _self, init)=>{
                            for (let i = entries.length - 1; i > -1; --i) {
                                if (this.lazyScrolling === null && entries[i].isIntersecting === true) {
                                    _self.unobserve(entries[i].target);
                                    requestAnimationFrame(()=> {
                                        this.lazyScroll([entries[i].target], init);
                                    });
                                }
                            }
                        };
                let observerInit;
                if (this.observer === null) {
                        observerInit = new IntersectionObserver((entries, _self)=>{
                            lazy(entries, _self, true);
                            _self.disconnect();
                            let intersect2 = false;
                            const ev = this.isTouch ? 'touchstart' : 'mousemove',
                                    oneScroll = ()=> {//pre cache after one scroll/mousemove
                                        if (intersect2) {
                                            intersect2.disconnect();
                                        }
                                        intersect2 = null;
                                        win.tfOff('scroll '+ev, oneScroll, {once: true, passive: true});
                                        this.observer = new IntersectionObserver((entries, _self)=> {
                                            lazy(entries, _self);
                                        }, {
                                            rootMargin: '300px 0px'
                                        });
                                        let j = 0;
                                        const prefetched = new Set();
                                        for (let i = 0; i < len; ++i) {
                                            if (items[i].hasAttribute('data-lazy') && !items[i].hasAttribute('data-tf-not-load')) {
                                                this.observer.observe(items[i]);
                                                if (j < 10 && items[i].hasAttribute('data-tf-src') && items[i].hasAttribute('data-lazy')) {
                                                    let src = items[i].getAttribute('data-tf-src');
                                                    if (src && !prefetched.has(src)) {
                                                        prefetched.add(src);
                                                        let img = new Image(),
                                                        srcset=items[i].getAttribute('data-tf-srcset');
                                                        img.decoding = 'async';
                                                        if(srcset){
                                                            img.srcset = srcset;
                                                        }
                                                        img.src = src;
                                                        img.decode();
                                                        ++j;
                                                    }
                                                }
                                            }
                                        }
                                        if (doc.tfClass('wow')[0]) {
                                            this.requestIdleCallback(()=> {
                                                this.wow();
                                            }, 1500);
                                        } 
                                        prefetched.clear();
                                    };
                            win.tfOn('beforeprint', ()=> {
                                this.lazyScroll(doc.querySelectorAll('[data-lazy]'), true);
                            }, {passive: true})
                            .tfOn('scroll '+ev, oneScroll, {once: true, passive: true});
                    
                            setTimeout(()=>{
                                if (intersect2 === false) {
                                    intersect2 = new IntersectionObserver((entries, _self)=>{
                                        if (intersect2 !== null) {
                                            lazy(entries, _self, true);
                                        }
                                        _self.disconnect();
                                    });
                                    const len2 = len > 15 ? 15 : len;
                                    for (let i = 0; i < len2; ++i) {
                                        if (items[i] && items[i].hasAttribute('data-lazy') && !items[i].hasAttribute('data-tf-not-load')) {
                                            intersect2.observe(items[i]);
                                        }
                                    }
                                }
                            }, 1600);
                        });
                } else {
                    observerInit = this.observer;
                }
                if (observerInit) {
                    for (let i = 0; i < len; ++i) {
                        if (!items[i].hasAttribute('data-tf-not-load')) {
                            observerInit.observe(items[i]);
                        }
                    }
                }
            }
        },
        async lazyScroll(items, init) {
            let len = 0;
            if (items) {
                len = items.length;
                if (len === und) {
                    items = [items];
                    len = 1;
                } 
                else if (len === 0) {
                    return;
                }
            }
            const svg_callback = function () {
                this.classList.remove('tf_svg_lazy_loaded', 'tf_svg_lazy');
            },
            prms=[];
            for (let i = len - 1; i > -1; --i) {
                let el = items[i],
                        tagName = el.tagName;
                if (!el || !el.hasAttribute('data-lazy')) {
                    if (el) {
                        el.removeAttribute('data-lazy');
                    }
                } else {
                    el.removeAttribute('data-lazy');
                    if (tagName !== 'IMG' && (tagName === 'DIV' || !el.hasAttribute('data-tf-src'))) {
                        try {
                            el.classList.remove('tf_lazy');
                            prms.push(this.reRun(el, true));
                            prms.push(this.trigger('tf_lazy', el));
                        } catch (e) {
                            console.log(e);
                        }
                    } 
                    else if (tagName !== 'svg') {
                        let src = el.getAttribute('data-tf-src'),
                            srcset = el.getAttribute('data-tf-srcset'),
                            sizes = srcset?el.getAttribute('data-tf-sizes'):null;
                        if(src || srcset){
                            if(tagName==='IMG'){
                                let img=new Image(),
                                    attr=el.attributes;
                                    for(let j=attr.length-1;j>-1;--j){
                                        let n=attr[j].name;
                                        if(n!=='src' && n!=='srcset' && n!=='sizes' && n!=='loading' && n.indexOf('data-tf')===-1){
                                            img.setAttribute(n,attr[j].value);
                                        }
                                    }
                                    img.decoding='async';
                                    if (srcset) {
                                        if (sizes) {
                                            img.setAttribute('sizes', sizes);
                                        }
                                        img.srcset=srcset;
                                    }
                                    if (src) {
                                        img.src=src;
                                    }
									let p= new Promise(resolve=>{
									   img.decode()
										.catch(()=>{})//need for svg
										.finally(()=>{
											requestAnimationFrame(()=>{
												el.replaceWith(img);
												if(img.classList.contains('tf_svg_lazy')){
													img.tfOn('transitionend', svg_callback, {once: true, passive: true});
													requestAnimationFrame(()=>{
													   img.classList.add('tf_svg_lazy_loaded');
													});
												}
												resolve();
											});
										});
									});
                                    prms.push(p);
                            }
                            else{
                                if (src) {
                                    el.src=src;
                                    el.removeAttribute('data-tf-src');
                                }
                                el.removeAttribute('loading');
                                if(init !== true && el.parentNode !== doc.body){
                                    el.parentNode.classList.add('tf_lazy');
                                    let p=this.imagesLoad(el).then(item=>{
                                        item.parentNode.classList.remove('tf_lazy');
                                    });
                                    prms.push(p);
                                }
                                this.largeImages();
                            }
                        }
                    }
                }
                if (this.observer !== null && el) {
                    this.observer.unobserve(el);
                }
            }
            return Promise.all(prms).catch(e=>{});
        },
        async reRun(el, isLazy) {
            if (isLazy !== true) {
                this.commonJs();
            }
            if(vars && !vars.is_admin){
                const isBuilder=this.is_builder_loaded===true || typeof ThemifyBuilderModuleJs !== 'undefined',
                    pr=[];
                if(isBuilder===true || (win.tbLocalScript && doc.tfClass('module_row')[0]!==und)){
                    if(isBuilder===false){
                       await this.loadJs(this.builder_url+'js/themify.builder.script',typeof ThemifyBuilderModuleJs !== 'undefined'); 
                    }
                    pr.push(ThemifyBuilderModuleJs.loadModules(el, isLazy));
                }
                pr.push(this.initComponents(el, isLazy));
                return Promise.all(pr);
            }
        },
        animateCss() {
            return this.loadCss('animate.min','animate');
        },
        wow() {
            return Promise.all([this.animateCss(),this.loadJs('tf_wow')]);
        },
        async dropDown(items, load_stylesheet) {
            if (items && items.length>0) {
                const prms=[];
                if (load_stylesheet !== false) {
                    prms.push(this.loadCss('dropdown','tf_dropdown'));
                }
                prms.push(this.loadJs('themify.dropdown'));
                await Promise.all(prms);
                this.trigger('tf_dropdown_init', [items]);
            }
        },
        resizer() {
            let running = false,
                    timeout,
                    timer;
            const ev = 'onorientationchange' in win ? 'orientationchange' : 'resize';
            win.tfOn(ev, ()=> {
                if (running) {
                    return;
                }
                running = true;
                if (timeout) {
                    clearTimeout(timeout);
                }
                timeout = setTimeout(()=>{
                    if (timer) {
                        cancelAnimationFrame(timer);
                    }
                    timer = requestAnimationFrame(()=>{
                        const w = win.innerWidth,
                                h = win.innerHeight;
                        if (h !== this.h || w !== this.w) {
                            this.trigger('tfsmartresize', {w: w, h: h});
                            this.w = w;
                            this.h = h;
                        }
                        running = false;
                        timer = timeout = null;
                    });
                }, 150);
            }, {passive: true});
        },
        mobileMenu() {//deprecated
            if (vars.menu_point) {
                const w = parseInt(vars.menu_point),
                        _init = e=> {
                            const cl = doc.body.classList;
                            if ((!e && this.w <= w) || (e && e.w <= w)) {
                                cl.add('mobile_menu_active');
                            } else if (e !== und) {
                                cl.remove('mobile_menu_active');
                            }
                        };
                _init();
                this.on('tfsmartresize', _init);
            }
        },
        async wc(force) {
            if (vars.wc_js) {
                if (!vars.wc_js_normal) {
                    setTimeout(()=>{
                        doc.tfOn((this.isTouch ? 'touchstart' : 'mousemove'), ()=>{
                            const fr = doc.createDocumentFragment();
                            for (let i in vars.wc_js) {
                                let link = doc.createElement('link'),
                                        href = vars.wc_js[i];
                                if (href.indexOf('ver', 12) === -1) {
                                    href += '?ver=' + vars.wc_version;
                                }
                                link.as = 'script';
                                link.rel = 'prefetch';
                                link.href = href;
                                fr.appendChild(link);
                            }
                            doc.head.appendChild(fr);
                        }, {once: true, passive: true});
                    }, 1800);
                }
                await this.loadJs('wc');
                this.trigger('tf_wc_init', force);
            }
        },
        megaMenu(menu) {
            if (menu && !menu.dataset.init) {
                menu.dataset.init = true;
                const self = this,
					maxW = 1 * vars.menu_point + 1,
					removeDisplay = function (e) {
						const el = e instanceof jQuery ? e : this,
								w = e instanceof jQuery ? self.w : e.w;
						if (w > maxW) {
							el.css('display', '');
						} else {
							self.on('tfsmartresize', removeDisplay.bind(el), true);
						}
					},
					closeDropdown = function (e) {
						const el = e instanceof jQuery ? e : this;
						if (e.target && !el[0].parentNode.contains(e.target)) {
							el.css('display', '')
							[0].parentNode.classList.remove('toggle-on');
						} else {
							doc.tfOn('touchstart', closeDropdown.bind(el), {once: true});
						}
					};
				if (!this.isTouch) {
					if(this.cssLazy.has('tf_megamenu') && menu.tfClass('mega-link')[0]){
						Promise.all([
                            this.loadCss(this.url+'megamenu/css/megamenu', 'tf_megamenu',null, null, 'screen and (min-width:' + maxW + 'px)'),
                            this.loadJs(this.url+'megamenu/js/themify.mega-menu')]
                        ).then(()=>{
                            this.trigger('tf_mega_menu', [menu, maxW]);
                        });
					}
					else{
						this.requestIdleCallback( ()=>  {
                            this.edgeMenu();
                        },-1,2000);
					}
				}
                menu.tfOn('click', function (e) {
                    const target=e.target;
                    if (!target.closest('.with-sub-arrow') && (target.classList.contains('child-arrow') || (target.tagName === 'A' && (!target.href || target.getAttribute('href') === '#' || target.parentNode.classList.contains('themify_toggle_dropdown'))))) {
                        let el = $(e.target);
                        if (el[0].tagName === 'A') {
                            if (!el.find('.child-arrow')[0]) {
                                return;
                            }
                        } else {
                            el = el.parent();
                        }
                        e.preventDefault();
                        e.stopPropagation();
                        const li = el.parent();
                        let els = null,
                                is_toggle = und !== vars.m_m_toggle && !li.hasClass('toggle-on') && self.w < maxW;
                        if (is_toggle) {
                            els = li.siblings('.toggle-on');
                            is_toggle = els.length > 0;
                        }
                        if (self.w < maxW || e.target.classList.contains('child-arrow') || el.find('.child-arrow:visible').length > 0) {
                            const items = el.next('div, ul'),
                                    ist = items[0].style,
                                    headerwrap = doc.tfId('headerwrap');
                            if (self.w < maxW && (ist === null || ist === '')) {
                                removeDisplay(items);
                            }
                            if (self.isTouch && !li.hasClass('toggle-on') && !doc.body.classList.contains('mobile-menu-visible') && (null === headerwrap || (headerwrap.offsetWidth > 400))) {
                                closeDropdown(items);
                                li.siblings('.toggle-on').removeClass('toggle-on');
                            }
                            items.toggle('fast');
                            if (is_toggle) {
                                const slbs = els.find('>div,>ul'),
                                        sst = slbs[0].style;
                                if (self.w < maxW && (sst === null || sst === '')) {
                                    removeDisplay(slbs);
                                }
                                slbs.toggle('fast');
                            }
                        }
                        if (is_toggle) {
                            els.removeClass('toggle-on');
                        }
                        li.toggleClass('toggle-on');
                    }
                });
            }
        },
        touchDropDown() {
            const menus = doc.querySelectorAll('ul:not(.sub-menu)>.menu-item:first-child');
            for (let i = menus.length - 1; i > -1; --i) {
                let m = menus[i].parentNode,
                        p = m.parentNode;
                if (p.tagName !== 'LI' && !p.classList.contains('sub-menu')) {
                    this.megaMenu(m);
                }
            }
        },
        ajaxSearch(items) {
            if(this.is_builder_active===false){
                const __callback=e=>{
                            const el=e.currentTarget,
                                    isOverlay=e.type==='click',
                                    type=isOverlay?'overlay':'dropdown',
                                    css=['search_form','search_form_ajax','search_form_' + type];
                            if(isOverlay){
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                            }
                    if(isOverlay && el.classList.contains('tf_search_icon')){
                        css.push('searchform_overlay'); 
                    }
                    const prms=[this.loadJs('ajax-search')];
                    for(let i=css.length-1;i>-1;--i){
                        let url='',
                            v=null;
                            if(css[i]==='searchform_overlay'){
                                v=vars.theme_v;
                                url=vars.theme_url+'/styles/modules/';
                            }
                        prms.push(this.loadCss(url+css[i].replaceAll('_','-'),null, v));
                    }
                    Promise.all(prms).finally(()=>{
                        this.trigger('themify_overlay_search_init', [el]);
                        this.triggerEvent(el, e.type);
                    });
                };
                for(let i=items.length-1;i>-1;--i){
                    if(items[i].hasAttribute('data-ajax') && items[i].dataset.ajax===''){
                        continue;
                    }
                    let isIcon=items[i].classList.contains('tf_search_icon'),
                        isOverlay= isIcon || items[i].classList.contains('tf_search_overlay'),
                        el,
                        ev;
                    if(isOverlay===false){
                        ev='focus';
                        el=items[i].querySelector('input[name="s"]');
                        el.autocomplete = 'off';
                    }
                    else{
                        ev='click';
                        el=isIcon?items[i]:items[i].tfClass('tf_search_icon')[0];
                    }
                    if(el){
                        el.tfOn(ev,__callback, {once: true,passive:!isOverlay});
                    }
                }
            }
        },
        async stickyBuy(el) {
            await Promise.all([this.loadCss('sticky-buy'),this.loadJs('sticky-buy')]);
            this.trigger('tf_sticky_buy_init', el);
        },
        async wpEmbed(items) {
            if (items.length === und) {
                items = [items];
            }
            if (items[0] !== und) {
                const embeds = [];
                for (let i = items.length - 1; i > -1; --i) {
                    if (items[i].tagName === 'IFRAME' && !items[i].dataset.done) {
                        items[i].dataset.done=1;
                        embeds.push(items[i]);
                    }
                }
                if (embeds[0] !== und) {
                    await this.loadJs(vars.includesURL+'js/wp-embed.min.js',('undefined' !== typeof win.wp && 'undefined' !== typeof win.wp.receiveEmbedMessage),vars.wp);
                    for (let i = embeds.length - 1; i > -1; --i) {
                        let secret = embeds[i].getAttribute('data-secret');
                        if (!secret) {
                            secret = Math.random().toString(36).substr(2, 10);
                            embeds[i].setAttribute('data-secret', secret);
                        }
                        if (!embeds[i].hasAttribute('src')) {
                            embeds[i].src=embeds[i].getAttribute('data-tf-src');
                        }
                        win.wp.receiveEmbedMessage({data: {message: 'height', value: this.h, secret: secret}, source: embeds[i].contentWindow});
                    }
                }
            }
        },
        largeImages(el){
            return new Promise( resolve=>{
                if((vars.lgi!==und || this.is_builder_active===true) && doc.querySelector('.tf_large_img:not(.tf_large_img_done)')){
                    this.requestIdleCallback(async()=>{
                        await this.loadJs('large-image-alert.min');
                        this.trigger('tf_large_images_init', el);
                        resolve();
                    },-1,1000);
                }
                else{
                    resolve();
                }
            });
        },
        async googleAnalytics() {
            if(vars.g_m_id!==und){
                const gtag=()=>{
                    win.dataLayer.push(arguments);
                },
                g_m_id=vars.g_m_id;
                await this.loadJs('https://www.googletagmanager.com/gtag/js?id='+g_m_id,!!win.google_tag_manager,false); 
                win.dataLayer = win.dataLayer || [];
                gtag('js', new Date());
                gtag('config', g_m_id);
                gtag('event', 'page_view');
                delete vars.g_m_id;
                win.tfOn('pageshow', e => {
                    if (e.persisted === true) {
                      gtag('event', 'page_view');
                    }
                },{passive:true});
            }
        },
        async tooltips() {
            return vars.menu_tooltips.length || vars.builder_tooltips?this.loadJs( 'tooltip'):1;
        },
        fetch(data,type,params,url){
            url=url || vars.ajax_url;
            params=Object.assign({
                credentials:'same-origin',
                method:'POST',
                headers:{}
            }, params);
            
            if(params.mode===und && url.indexOf(location.origin)===-1){
                params.mode='cors';
            }
            else if(params.mode!=='cors'){
                params.headers['X-Requested-With']='XMLHttpRequest';
            }
            if(!type){
                type='json';
            }
            if(type==='json'){
                params.headers.accept='application/json, text/javascript, */*; q=0.01';
            }
            if(data){
                let body;
                if(data instanceof FormData){
                    body=data;
                }
                else{
                    body=new FormData();
                    for(let k in data){
                        if(typeof data[k]==='object' && !(data[k] instanceof Blob)){
                            body.set(k,JSON.stringify(data[k]));
                        }
                        else{
                            body.set(k,data[k]);
                        }
                    }
                }
                if(params.method==='POST'){
					if(params.headers['Content-type']==='application/x-www-form-urlencoded'){
						body=new URLSearchParams(body);
					}
					params.body=body;
				}
				else{
					url = new URL(url,win.location);
                    for(let pair of body.entries()) {
                        url.searchParams.set(pair[0],pair[1]);
                    }
				}
            }
            return fetch(url,params).then(res=>{
                if(!res.ok){
                    throw res;
                }
                if(type==='json'){
                    return res.json();
                }
                if(type==='blob'){
                    return res.blob();
                }
                return res.text();
                
            }).
            then(res=>{
                if(res && (type==='html' || type==='text')){
                    res=res.trim();
                    if(type==='html' && res){
                        const tmp=doc.createElement('template');
                        tmp.innerHTML=res;
                        res=tmp.content;
                    }
                }
                return res;
            });
        }
    };
    Themify.init();
﻿
})(window, document, undefined, jQuery,themify_vars);
