/**
 * FixedHeader module
 */
;
let ThemifyFixedHeader;
((Themify,doc,win)=>{
    'use strict';
    let isWorking=false,
        imageLoading=false;
    const addStickyImage=()=>{
        return new Promise(resolve=>{
                if (typeof themifyScript!=='undefined' && themifyScript.sticky_header && themifyScript.sticky_header.src) {
                    const img = new Image();
                    let logo = doc.tfId('site-logo');
                    if(logo){
                        const a=logo.tfTag('a')[0];
                        if(a){
                            logo=a;
                        }
                    }
                    if(logo){
						/* original logo image */
						const og_image = logo.tfClass( 'site-logo-image' )[0];
                        let alt;
                        if ( og_image ) {
                            alt=og_image.alt;
                        }
                        else{
                            alt=logo.tfTag( 'span' )[0];
                            alt=alt?alt.textContent:'';
                        }
                        img.src = themifyScript.sticky_header.src;
						img.alt = alt;
                        img.className = 'tf_sticky_logo';
						if ( themifyScript.sticky_header.imgwidth ) {
							img.width = themifyScript.sticky_header.imgwidth;
						} else if ( og_image ) {
							/* no explicit width set, presume it's the same size as the regular logo image */
							img.width = og_image.width;
						}
						if ( themifyScript.sticky_header.imgheight ) {
							img.height = themifyScript.sticky_header.imgheight;
						} else if ( og_image ) {
							img.height = og_image.height;
						}
                        themifyScript.sticky_header=null;
                        img.decode().finally(()=>{
                            logo.prepend(img);
                            resolve();
                        });
                    }
                }
                else{
                    resolve();
                }
            });
    };
ThemifyFixedHeader = {
        active:false,
        isTransparent:false,
        headerWrap:null,
        type:'sticky',
        normalHeight:0,
        fixedHeight:0,
        transitionDuration:0,
        init(options) {
            if(typeof options!=='object'){
                options={};
            }
            this.headerWrap = options.headerWrap || doc.tfId('headerwrap');
            if (this.headerWrap === null || this.headerWrap.length === 0) {
                return;
            }

            if(options.hasHeaderRevealing || doc.body.classList.contains('revealing-header')){
                this.headerRevealing(options.revealingInvert);
            }
			if(!options.disableWatch){
				const header=this.headerWrap.querySelector('#header'),
				dummy=doc.createElement('div');
				this.type=getComputedStyle(this.headerWrap).getPropertyValue('position');
                                if(!this.type){
                                    this.type='static';
                                }
				dummy.className='tf_hidden tf_w';
				dummy.style.height='0';
				dummy.style.contain='strict';
				this.isTransparent=options.isTransparent?true:(this.type==='fixed' || doc.body.classList.contains('transparent-header'));
			
				if(this.isTransparent){
					dummy.className+=' tf_rel';
					this.calculateTop(dummy);
				}
				this.headerWrap.after(dummy);
				if(header!==null){
					this.transitionDuration=parseFloat(getComputedStyle(header).getPropertyValue('transition-duration'));
					if(this.transitionDuration<10){
						this.transitionDuration*=1000;
					}
				}
				(new IntersectionObserver((records, observer) => {
						const targetInfo = records[0].boundingClientRect,
							rootBoundsInfo = records[0].rootBounds;
						if(rootBoundsInfo){
							if (this.active===false && targetInfo.bottom < rootBoundsInfo.top) {
							  this.enable();
							}
							else if (this.active===true && targetInfo.bottom < rootBoundsInfo.bottom) {
							  this.disable();
							}
						}
						else{
							observer.disconnect();
						}
				},{
                                    threshold:[0,1]
				})).observe(dummy);
				
				if(this.type!=='sticky' && this.type!=='-webkit-sticky'){
					(new MutationObserver((mutations, observer) => {
						if(Themify.is_builder_active){
							observer.disconnect();
							return;
						}
						setTimeout(()=>{
							this.calculateTop(dummy);
						},300);
					}))
					.observe(this.headerWrap, {
							subtree:true,
							childList:true, 
							characterData:true
					});
					Themify.on('tfsmartresize', ()=> {
						setTimeout(()=> {
                            this.calculateTop(dummy);
						}, 400);
					});
					win.tfOn('scroll', ()=>{
                        this.calculateTop(dummy);
					}, {passive:true,once:true});
				}
			}
            Themify.trigger('tf_fixed_header_ready',this.headerWrap);
        },
        setPadding(){
                if(this.active && this.normalHeight>0 && (this.type==='relative' || this.type==='static')){
                    this.headerWrap.parentNode.style.paddingTop=this.normalHeight+'px';
                }
        },
        calculateTop(dummy,force){
            return new Promise(async resolve => {
                const calculate=force===true || (this.active===true && (this.type==='relative' || this.type==='static')),
                    res=[this.headerWrap,this.normalHeight,this.fixedHeight];
                if(isWorking===true && calculate){
                    return res;
                }
                if(calculate){
                    isWorking=true;
                }
				requestAnimationFrame(async()=>{
                let headerWrap=this.headerWrap;
                if(calculate){
                    headerWrap=headerWrap.cloneNode(true);
                    const header=headerWrap.querySelector('#header');
                    if(doc.tfId('tf_fixed_header_st')===null){
                        const st=doc.createElement('style');
                        st.id='tf_fixed_header_st';
                        st.textContent='.tf_disabled_transition,.tf_disabled_transition *{transition:none!important;animation:none!important}';
                        doc.head.appendChild(st);
                    }
                    headerWrap.classList.remove('fixed-header');
                    headerWrap.classList.add('tf_hidden','tf_opacity','tf_disabled_transition');
                    headerWrap.style.position='fixed';
                    headerWrap.style.top='-1000%';
                    headerWrap.style.contain='style paint layout';
                    if(!header.previousElementSibling){
                        header.style.marginTop=0;
                    }
                    if(!header.nextElementSibling){
                        header.style.marginBottom=0;
                    }
                }
                
                    if(calculate){
                        this.headerWrap.before(headerWrap);
                    }
                    await Themify.imagesLoad(headerWrap);
                    const box=headerWrap.getBoundingClientRect();
                          this.normalHeight=box.height;
                    if(this.isTransparent && dummy){
                            let bottom=box.bottom,
                                wp_admin=doc.tfId('wpadminbar');
                            if(wp_admin){
                                bottom-=wp_admin.offsetHeight;
                            }
                          dummy.style.top=bottom+'px';
                    }
                    if(headerWrap.classList.contains('tf_disabled_transition')){
                          headerWrap.classList.add('fixed-header');
                          this.fixedHeight=headerWrap.getBoundingClientRect().height;
                          Themify.trigger('tf_fixed_header_calculate',[this.headerWrap,this.normalHeight,this.fixedHeight]);
                    }
                    if(calculate){
                        headerWrap.remove();
                        this.setPadding();
                    }
                    isWorking=false;
                    headerWrap=null;
                    resolve(res);
                });
            });
        },
        headerRevealing(invert) {
            let previousY = 0;
                const self = this,
                events = ['scroll'],
				bodyCl=doc.body.classList,
                onScroll = function () {
                    if (self.active===false || previousY === this.scrollY) {
                        return;
                    }
                    const dir = invert?(previousY<this.scrollY):(previousY>=this.scrollY);
                    previousY = this.scrollY;
                    if (dir || 0 === previousY || bodyCl.contains('mobile-menu-visible') || bodyCl.contains('slide-cart-visible')) {
                        self.headerWrap.classList.remove('header_hidden');
                    } else if (0 < previousY && !self.headerWrap.classList.contains('header_hidden')) {
                        self.headerWrap.classList.add('header_hidden');
                    }
                };
            if (Themify.isTouch) {
                events.push('touchstart');
                events.push('touchmove');
            }
            win.tfOn(events, onScroll, {passive:true});
            onScroll.call(win);
        },
        enable(){
            if(this.active===false && imageLoading===false){
                imageLoading=true;
                addStickyImage().finally(()=>{
                    imageLoading=false;
                    this.active=true;
                    doc.body.classList.add('fixed-header-on');
                    this.headerWrap.classList.add('fixed-header');
                    this.setPadding();
                    if(this.transitionDuration===0){
                        const header=this.headerWrap.querySelector('#header');
                        if(header!==null){
                            this.transitionDuration=parseFloat(win.getComputedStyle(header).getPropertyValue('transition-duration'));
                            if(this.transitionDuration<10){
                                    this.transitionDuration*=1000;
                            }
                        }
                        if(this.transitionDuration===0){
                            this.transitionDuration=null;
                        }
                    }
                });
            }
        },
        disable(){
            if(this.active===true){	
                if(this.transitionDuration===0 || this.transitionDuration===null){
                        this.active=false;
                }
                else{
                    const header=this.headerWrap.querySelector('#header'),
                    __callback=()=>{
                            header.tfOff('transitionend',__callback,{passive:true,once:true});
                            clearTimeout(timer);
                            this.active=false;
                    },
                    timer=setTimeout(__callback,this.transitionDuration+10);
                    header.tfOn('transitionend',__callback,{passive:true,once:true});
                }

                doc.body.classList.remove('fixed-header-on');
                this.headerWrap.classList.remove('fixed-header');
                if(this.normalHeight>0 && (this.type==='relative' || this.type==='static')){
                        this.headerWrap.parentNode.style.paddingTop='';
                }

				/* when fixed header resets, sliders inside the header can break due to the height changes,
				 * force transition to another slide to fix it
				 */
				const header_sliders = this.headerWrap.tfClass( 'tf_carousel' );
				for ( let i = header_sliders.length - 1; i > -1; --i ) {
					const swiper_instance = header_sliders[ i ].swiper;
					if ( swiper_instance ) {
						setTimeout( () => {
							swiper_instance.autoplay.running && swiper_instance.autoplay.pause();
							swiper_instance.slideToClosest( 100 );
							swiper_instance.autoplay.running && swiper_instance.autoplay.run();
						}, 50 );
					}
				}
            }
        }
    };
	
    Themify.on('tf_fixed_header_init', options=>{
        if(Themify.is_builder_active===false){
            ThemifyFixedHeader.init(options);
        }
    })
    .on('tf_fixed_header_enable',()=>{
        if(Themify.is_builder_active===false){
            ThemifyFixedHeader.enable();
        }
    })
    .on('tf_fixed_header_disable',()=>{
        ThemifyFixedHeader.disable();
    })
    .on('tf_fixed_header_remove_revelaing',()=>{
        if ( ThemifyFixedHeader.headerWrap !== null ) {
            ThemifyFixedHeader.headerWrap.classList.remove('header_hidden');
        }
    });
	
})(Themify,document,window);
