/**
 * tabs module
 */
;
(($,Themify,doc,win)=>{
    'use strict';
    let isAttached=false;
    const style_url=ThemifyBuilderModuleJs.cssUrl+'tab_styles/',
        init=()=>{
            mobileTab(Themify.w);
            doc.body.tfOn('click',e=>{
                const target =e.target?e.target.closest('.tab-nav a,.tab-nav-current-active'):null;
                if(target){
                    e.preventDefault();
                    e.stopPropagation();
                    const cl=target.classList;
                    if(cl.contains('tab-nav-current-active')){
                        if (cl.contains('clicked')) {
                            cl.remove('clicked');
                        } 
                        else {
                            const $this = $(target),
                                left = $this.position().left,
                                w=$this.closest('.module-tab').width()/2,
                                navCl= $this.next('.tab-nav')[0].classList;

                                navCl.toggle('center-align',(left>0 && left <= w));
                                navCl.toggle('right-align',left> w);
                                cl.add('clicked');
                        }
                    }
                    else{
                        const  current=target.parentNode,
                                tabId=target.getAttribute('href').replace('#',''),
                                p = current.closest('.builder-tabs-wrap'),
                                li=p.tfClass('tab-nav')[0].tfTag('li'),
                                nav = p.tfClass('tab-nav-current-active')[0],
                                contents = p.tfClass('tab-content');
                        for(let i=li.length-1;i>-1;--i){
                                let expanded=li[i]===current?'true':'false';
                                li[i].classList.toggle('current',expanded==='true');
                                li[i].setAttribute('aria-expanded', expanded);
                        }
                        let cont=null;
                        for(let i=contents.length-1;i>-1;--i){
                            if(contents[i].parentNode===p){
                                let expanded='true';
                                if(contents[i].dataset.id===tabId){
                                    cont=contents[i];
                                    expanded='false';
                                }
                                contents[i].setAttribute('aria-hidden', expanded);
                            }
                        }
                        if(true===p.parentNode.hasAttribute('data-hashtag')){
                            win.history.pushState(null, null, '#'+tabId);
                        }
                        nav.tfClass('tb_tab_title')[0].innerText=target.innerText;
                        nav.click();
                        Themify.trigger('tb_tabs_switch', [cont,target, tabId]);
                    }
                }
            });
    },
    hashchange = ()=> {
            const hash = win.location.hash.replace('#','');
            if ( hash !== '' && hash !== '#' ) {
                const acc = doc.querySelector( '.module-tab [data-id="'+hash+'"]' );
                if ( acc ) {
                    const target = doc.querySelector( '.module-tab a[href="#' + hash + '"]' );
                    target.click();
                }
            }
    },
    mobileTab =w=>{
        const items =doc.querySelectorAll('.module-tab[data-tab-breakpoint]'),
            len=items.length;
        if (len> 0) {
            for(let i=len-1;i>-1;--i){
                if (parseInt(items[i].dataset.tabBreakpoint) >= w) {
                    Themify.loadCss(style_url+'responsive').then(()=>{
                         items[i].classList.add('responsive-tab-style');
                    });
                } else {
                    items[i].classList.remove('responsive-tab-style');
                    let nav = items[i].tfClass('tab-nav');
                    for(let j=nav.length-1;j>-1;--j){
                        nav[j].classList.remove('right-align','center-align');
                    }
                }
            }
        }
    };
    Themify.on('tfsmartresize',e=>{
        if(e){
            mobileTab(e.w);
        }
    })
    .on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-tab')){
            return;
        }
        const items = Themify.selectWithParent('module-tab',el);
        for(let i=items.length-1;i>-1;--i){
            let cl=items[i].classList,
                type='';
            if(cl.contains('transparent')){
                Themify.loadCss(style_url+'transparent','tb_tab_transparent');
            }
            if(cl.contains('minimal')){
                type='minimal';
            }
            else if(cl.contains('panel')){
                type='panel';
            }
            else if(cl.contains('vertical')){
                type='vertical';
            }
            if(type!==''){
                Themify.loadCss(style_url+type,'tb_tab_'+type);
            }
        }
        if (isAttached === false ) {
            isAttached = true;
            win.tfOn( 'hashchange', hashchange, { passive : true } );
            Themify.requestIdleCallback(()=>{
                init();
                hashchange();
            },-1,500);
        }
    });
})(jQuery,Themify,document,window);
