let ThemifyBuilderModuleJs;

(($, win,Themify, doc,und,vars)=>{
    'use strict';

    ThemifyBuilderModuleJs = {
        isBpMobile:!Themify.is_builder_active && Themify.w<parseInt(vars.breakpoints.tablet[1]),
        init() {
            this.jsUrl = Themify.builder_url+'js/modules/';
            this.cssUrl = Themify.builder_url+'css/modules/';
            vars.addons=Object.assign(vars.addons,{
                bgzs:{
                    match:'[data-zoom-bg]',
                    js: 'bgzoom_scroll'
                },
                bgzm:{
                    match:'[data-zooming-bg]',
                    js:'bgzoom'
                },
                fwv:{
                    match:'[data-tbfullwidthvideo]',
                    js:'fullwidthvideo'
                },
                bgs:{
                    selector:':scope>.tb_slider',
                    js:'backgroundSlider'
                },
                rd:{
                    selector:'.module-text-more',
                    js:'readMore'
                },
                fr:{
                    match:'.tb_row_frame_wrap',
                    css:'frames'
                },
                bgz:{
                    match:'.themify-bg-zoom',
                    css:'bg-zoom-hover'
                },
                cover:{
                    selector:'.builder_row_cover',
                    css: 'cover'
                },
                color:{
                    selector:'.ui',
                    css:'colors'
                },
                app:{
                    selector:'.embossed,.shadow,.gradient,.rounded,.glossy',
                    css:'appearance'
                },
                image_left:{
                    match:'.module-image.image-left',
                    css:'image_styles/left'
                },
                image_center:{
                    match:'.module-image.image-center',
                    css:'image_styles/center'
                },
                image_right:{
                    match:'.module-image.image-right',
                    css:'image_styles/right'
                },
                image_top:{
                    match:'.module-image.image-top',
                    css:'image_styles/top'
                },
                image_overlay:{
                    match:'.module-image.image-overlay',
                    css:'image_styles/overlay'
                },
                'image_full-overlay':{
                    match:'.image-full-overlay',
                    css:'image_styles/full-overlay'
                },
                'image_card-layout':{
                    match:'.image-card-layout',
                    css:'image_styles/card-layout'
                },
                image_zoom:{
                    selector:'.module-image .zoom',
                    css:'image_styles/zoom'
                },
                buttons_vertical:{
                    match:'.buttons-vertical',
                    css:'buttons_styles/vertical'
                },
                buttons_fullwidth:{
                    match:'.buttons-fullwidth',
                    css:'buttons_styles/fullwidth'
                },
                buttons_outline:{
                    match:'.module-buttons.outline',
                    css:'buttons_styles/outline'
                },
                'service-menu_horizontal':{
                    match:'.module-service-menu.image-horizontal',
                    css:'service-menu_styles/horizontal'
                },
                'service-menu_overlay':{
                    match:'.module-service-menu.image-overlay',
                    css:'service-menu_styles/overlay'
                },
                'service-menu_top':{
                    match:'.module-service-menu.image-top',
                    css:'service-menu_styles/top'
                },
                'service-menu_right':{
                    match:'.module-service-menu.image-right',
                    css:'service-menu_styles/right'
                },
                'service-menu_center':{
                    match:'.module-service-menu.image-center',
                    css:'service-menu_styles/center'
                },
                'service-menu_highlight':{
                    selector:'.module-service-menu .tb-highlight-text',
                    css:'service-menu_styles/highlight'
                },
                'service-menu_price':{
                    selector:'.module-service-menu .tb-menu-price',
                    css:'service-menu_styles/price'
                }
            });
            if(vars.is_parallax===und ||  (this.isBpMobile===true && vars.is_parallax==='m')){
				vars.addons.p={
                    match:'[data-parallax-bg]',
                    js:'parallax'
                };
			}
            if(vars.fullwidth_support){
                vars.addons.fwr={
                    match:'.fullwidth.module_row,.fullwidth_row_container',
                    js:'fullwidthRows'
                };
            }
            if (!Themify.is_builder_active) {
                vars.addons.cl={
                    selector:'[data-tb_link]',
                    js: 'clickableComponent'
                };
                this.initScrollHighlight();
                this.toc();
                const stickyItems=doc.querySelectorAll('[data-sticky-active]');
                if(stickyItems[0]!==und){
                    if(win.pageYOffset>0){
                        this.stickyElementInit(stickyItems);
                    }
                    else{
                        win.tfOn('scroll',()=>{
                                this.stickyElementInit(stickyItems);
                        },{passive:true,once:true});
                    }
                }
            }
            Themify.trigger('themify_builder_loaded');
            Themify.is_builder_loaded = true;
        },
        async wow(el, isLazy) {
            if(el && vars.is_animation!=='' && (this.isBpMobile!==true || vars.is_animation!=='m')){
                let items;
                if(isLazy===true){
                    if(!el.hasAttribute('data-tf-animation') && !el.classList.contains('hover-wow')){
                        if(el.parentNode && (el.parentNode.hasAttribute('data-tf-animation') || el.parentNode.classList.contains('hover-wow'))){
                            items=[el.parentNode];
                        }
                        else{
                            return;
                        }
                    }
                    else{
                        items=[el];
                    }
                }
                else{
                    items=Themify.selectWithParent('.hover-wow,[data-tf-animation]',el);
                }

                if(items[0]!==und){
                    await Themify.wow();
                    Themify.trigger('tf_wow_init',[items]);
                }
            }
        },
        async initScrollHighlight(el,isLazy) {
            if(isLazy===true || Themify.is_builder_active===true || (vars.scrollHighlight && vars.scrollHighlight.scroll==='external')){// can be 'external' so no scroll is done here but by the theme. Example:Fullpane.
                return;
            }
            let hasItems=el?(Themify.selectWithParent('[data-anchor]',el).length>0):(doc.querySelector('[data-anchor]')!==null);
            /* deep link for Tab and Accordion */
            if(hasItems===false){
                const hash= win.location.hash.replace('#','');
                if(hash!=='' && hash!=='#'){
                    hasItems=doc.querySelector('[data-id="'+CSS.escape(hash)+'"]')!==null;
                }
            }
            if(hasItems===true){
                await Themify.loadJs(Themify.builder_url+'js/themify.scroll-highlight');
                Themify.trigger('tb_init_scroll_highlight',[el]);
            }
        },
        async toc() {
            const hash= win.location.hash.replace('#','');
            if(hash!==''){
                const items=doc.tfClass('module-toc');
                for(let i=items.length-1;i>-1;--i){
                    this.addonLoad(items[i],null,true);
                }
            }
        },
        async addonLoad(el, slug,isLazy) {
            if (vars.addons && Object.keys(vars.addons).length > 0) {
                let addons= {};
                if(slug && el &&  el.tfClass('themify_builder')[0]===und){
                    if(!vars.addons[slug]){
                        return;
                    }
                    else {
                        addons[slug] = vars.addons[slug];
                    }
                }
                else{
                    addons = vars.addons;
                }					
                const allPrms=[];
                for (let i in addons) {
                    let found =false,
                    m=addons[i];
                    if(m.selector!==und || m.match!==und){
                        if(isLazy===true){
                            if(m.match!==und){
                                found=el.matches( m.match );
                            }
                            else{
                                found=(el.matches( m.selector ) || el.querySelector( m.selector )!==null);
                            }
                        }
                        else{
                            let sel=m.selector || m.match;
                            found=doc.querySelector(sel.replaceAll(':scope>',''))!==null;
                        }
                    }
                    else{
                        found=isLazy===true?el.classList.contains( 'module-' + i ):doc.tfClass( 'module-' + i )[0]!==und;
                    }
                    if (found===true) {
                        let prms=[],
                            cssKey='tb_'+i,
                            v=m===1?null:m.ver;
                        if (m.css!==und && !Themify.cssLazy.has(cssKey)) {
                            let u=m.css;
                            if(u===1 || typeof u === 'string'){
                                if(u===1 || u.indexOf('http')===-1){
                                    u=this.cssUrl+(u===1?i:m.css);
                                }
                                prms.push(Themify.loadCss(u,cssKey, v));
                            }
                            else{
                                for(let j=m.css.length-1;j>-1;--j){
                                    u=m.css[j];
                                    if(u===1 || u.indexOf('http')===-1){
                                        u=this.cssUrl+(u===1?i:m.css[j]);
                                    }
                                    prms.push(Themify.loadCss(u,cssKey+j, v));
                                }
                            }
                        }
                        if (m.js!==und) {
                            let cl=el?el.classList:null,
                                u=m.js;
                            if(cl && (cl.contains('module') || cl.contains('active_module'))){
                                cl.add('tf_lazy');
                            }
                            if(u===1 || u.indexOf('http')===-1){
                                u=this.jsUrl+(u===1?i:m.js);
                            }
                            prms.push(Themify.loadJs(u,null,v));
                            let prm=Promise.all(prms);
                            prm.then(()=>{
                                delete vars.addons[i];
                                if(cl){
                                    Themify.trigger('builder_load_module_partial', [el,isLazy]);
                                    cl.remove('tf_lazy');
                                }
                            });
                            allPrms.push(prm);
                        }
                        else{
                            delete vars.addons[i];
                        }
                        if (slug) {
                            break;
                        }
                    }
                }
                await Promise.all(allPrms);
                if(!el){
                    Themify.trigger('builder_load_module_partial', [el,isLazy]);
                }
            }
        },
        loadModules(el,lazy){
            const prms=[];
            prms.push(this.touchdropdown(el,lazy));
            if (Themify.is_builder_active===false) {
                prms.push(this.morePagination(el,lazy));
                if(lazy===false){
                    prms.push(this.stickyElementInit(Themify.selectWithParent('[data-sticky-active]',el)));
                }
            }
            prms.push(Themify.trigger('builder_load_module_partial', [el,lazy]));
            prms.push(this.addonLoad(el,null,lazy));
            prms.push(this.wc(el,lazy));
            prms.push(this.wow(el, lazy));
            prms.push(this.initScrollHighlight(el,lazy));
            return Promise.all(prms);
        },
        async wc(el,isLazy){
            if(isLazy!==true && win.wc_single_product_params!==und){
                $( '.wc-tabs-wrapper, .woocommerce-tabs, #rating',el).each(function(){
                        if(!this.hasAttribute('tb_done')){
                                this.dataset.tb_done=1('tb_done',1);
                                if(this.id!=='rating' || this.parentNode.tfClass('stars')[0]){
                                        $(this).trigger( 'init' );
                                }
                        }

                });
                if(typeof $.fn.wc_product_gallery!=='undefined'){
                    const args=win.wc_single_product_params;
                    $( '.woocommerce-product-gallery',el ).each( function() {
                        if(!this.hasAttribute('tb_done')){
                            $( this ).trigger( 'wc-product-gallery-before-init', [ this, args ] )
                                    .wc_product_gallery( args )
                                    .trigger( 'wc-product-gallery-after-init', [ this, args ] )[0].dataset.tb_done=1;
                        }
                    } );
                }
            }
        },
        async touchdropdown(el,isLazy) {
            if (Themify.isTouch) {
                let items=null;
                if(isLazy===true){
                    if(el.classList.contains('module-menu')){
                        items=[el.tfClass('nav')[0]];
                    }
                }
                else{
                    const p=el || doc;
                    items=p.querySelectorAll('.module-menu .nav');
                }
                if(items!==null && items[0]!==und){
                    await Themify.loadCss(this.cssUrl+'menu_styles/sub_arrow');
                    Themify.dropDown(items);
                }
            }
        },
        async stickyElementInit(items) {
            if(!(vars.is_sticky==='' || (this.isBpMobile===true && vars.is_sticky==='m')) && items && items[0]!==und){
                await Themify.loadJs(this.jsUrl+'sticky');
                Themify.trigger('tb_sticky_init',[items]);
            }
        },
        async morePagination(el,isLazy) {
			if(isLazy===true && !el.classList.contains('tb_ajax_pagination')){
				return;
			}
			const items = Themify.selectWithParent('tb_ajax_pagination',el);
            if(items[0]!==und){
                const prms=[];
                for(let i=items.length-1;i>-1;--i){
                    prms.push(Themify.infinity(items[i],{id:'[data-id="'+items[i].dataset.id+'"]',scrollThreshold:false,history:true}));
                }
                return Promise.all(prms);
            }
                
        }
    };
    if(win.loaded===true){
        ThemifyBuilderModuleJs.init();
    }
    else{
        win.tfOn('load', ()=>{
            ThemifyBuilderModuleJs.init();
        }, {once:true, passive:true});
    }

})(jQuery, window,Themify, document,undefined,tbLocalScript);
