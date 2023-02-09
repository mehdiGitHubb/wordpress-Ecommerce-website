/* Themify Scroll to element based on its class and highlight it when a menu item is clicked.*/
(($,Themify, win, doc)=>{
	'use strict';
        
	let isScrolling=false,
            observer=null,
            isFirst=true,
            isInit=false,
            isBind=false,
            currentUrl=null;
        const options=Object.assign({
			speed: 0,
			element:'module_row',
			offset:null,
			navigation:'#main-nav,.module-menu .nav',
			updateHash:true
        },tbLocalScript.scrollHighlight),
		_OFFSET=parseInt(options.offset),
        w=win.top.Themify?win.top:win,
		header=doc.tfId('headerwrap'),
        isFixedHeader=(header!==null && doc.body.classList.contains('fixed-header-enabled')) || _OFFSET>0,
        TB_ScrollHighlight={
            init(el){
                if(isInit===false){
                    const hash = w.location.hash.replace('#','');
                    if(hash && hash!=='#'){
                            let item=getCurrentViewItem(doc.querySelectorAll('.'+options.element+'[data-anchor="'+hash+'"]'));
							if ( ! item ) {
								// deep linking to content in Builder
								let deep_link = doc.querySelector('.module [data-id="'+hash+'"]');
								if ( deep_link ) {
									item = deep_link.closest( '.module' );
								}
							}
                            if(item){
                                    this.scrollTo(item,hash);
                            }
                    }
                    isInit=true;
                }
                this.createObserver(el);
            },
            changeHash(){
                if(isScrolling===false){	
                        const hash = w.location.hash.replace('#',''),
                        menus =  doc.querySelectorAll(options.navigation);	
                        for(let i=menus.length-1;i>-1;--i){
							let selected=menus[i].tfClass('current-menu-item');
							for(let j=selected.length-1;j>-1;--j){
								if(currentUrl===null){
									currentUrl=selected[j].tfTag('a')[0].getAttribute('href');
								}
								selected[j].classList.remove('current_page_item','current-menu-item');
							}
							selected=hash!=='' && hash!=='#'?menus[i].querySelectorAll('a[href*="#' + CSS.escape(hash) + '"]'):null;
							if(!selected || selected.length===0){
								selected =menus[i].querySelectorAll('a[href="' + currentUrl + '"]');
							}
							for(let j=selected.length-1;j>-1;--j){
								let p =selected[j].parentNode;
								p.classList.add('current-menu-item');
								if(p.classList.contains('menu-item-object-page')){
									p.classList.add('current_page_item');
								}
							}
                        }
						if(currentUrl===null){
							currentUrl=w.location.href.split('#')[0];
						}
                }
            },
            calculatePosition(item){
                let offset=$(item).offset().top+2;
                if(isFixedHeader===true){
                    if(_OFFSET){
                            offset-=_OFFSET-2;
                    }
                    else if(header.classList.contains('fixed-header')){
                            const bottom=header.getBoundingClientRect().bottom+2;
                            if(offset>=bottom){
                                  offset-=bottom;
                            }
                    }
                }
              return offset;
            },
            scrollTo(item,hash){
                    isScrolling=true;
					Themify.lazyScroll(Themify.convert(Themify.selectWithParent('[data-lazy]',item)).reverse(),true).finally(()=>{
                        const isDisabled=Themify.lazyScrolling,
                            _isInit=isInit===false,
                            complete=()=>{
                                //browsers bug intersection sometimes doesn't work after page scrolling on the prev/next row
                                const type=getCurrentView(),
                                        items=doc.tfClass(options.element),
                                    obs2=new IntersectionObserver((entries, _self)=>{
                                        for (let i = entries.length-1; i>-1;--i) {
                                            if (entries[i].isIntersecting === true) {
                                                Themify.lazyScroll(Themify.convert(Themify.selectWithParent('[data-lazy]',entries[i].target)).reverse(),true);
                                            }
                                        }
                                       _self.disconnect();
                                    },{
                                        rootMargin:'300px 0px 300px 0px',
                                        threshold:.01
                                    });
                                for(let i=items.length-1;i>-1;--i){
                                    if(items[i].hasAttribute('data-lazy') && !items[i].classList.contains('hide-'+type)){
                                        obs2.observe(items[i]);
                                    }
                                }
                                if(isFixedHeader===true && (_OFFSET || header.classList.contains('fixed-header'))){
                                    Themify.scrollTo(this.calculatePosition(item), options.speed);  
                                }
                                Themify.lazyScrolling=isDisabled;
                                isScrolling=false;
                                if(_isInit===false){
                                    hash=item.hasAttribute('data-hide-anchor')?'':('#' + hash.replace('#',''));
                                    w.history.replaceState(null, null,hash);
                                }
                                changeHash();
                        },
                        progress=isFixedHeader===true && (_OFFSET || !header.classList.contains('fixed-header'))?
                        ()=>{
                            if(_OFFSET || header.classList.contains('fixed-header')){
                                Themify.scrollTo(this.calculatePosition(item), options.speed,complete);  
                            }
                        }:null;
                        Themify.lazyScrolling=true;
                        Themify.scrollTo(this.calculatePosition(item), options.speed,complete,progress);
                        doc.activeElement.blur();
                    });
            },
            createObserver(el){
                if (options.updateHash) {
                    if(observer===null){
                        observer=new IntersectionObserver((entries, _self)=> {
                            if(isScrolling===false){
                                    let intersect=false;
                                    for (let i = 0,len=entries.length; i<len;++i) {
                                        if (entries[i].isIntersecting === true) {
                                            intersect=entries[i].target.dataset.anchor;
                                        }
                                    }
                                    if(intersect===false){	
                                            if(isFirst===false){
                                                w.history.replaceState(null, null, ' ');
												changeHash();
                                            }
                                            else{
                                                isFirst=false;
                                            }
                                    }
                                    else{
										w.history.replaceState(null, null, '#' + intersect);
										changeHash();
                                    }
                            }
                        }, {
                            rootMargin:'0px 0px -100%',
                            thresholds:[0,1]
                        });
                    }
                    const items=Themify.selectWithParent(options.element,el);
                    for(let i=items.length-1;i>-1;--i){
                        if(!items[i].hasAttribute('data-hide-anchor')){
                            let hash=items[i].dataset.anchor;
                            if(hash && hash!=='#'){
                                observer.observe(items[i]);
                            }
                        }
                    }
                }
            }
        },
		getCurrentView=()=>{
			const w = Themify.w,
				bp=tbLocalScript.breakpoints;
			for(let k in bp){
				if(Array.isArray(bp[k])){
					if(w>=bp[k][0] && w<=bp[k][1]){
						return k;
					}
				}
				else if(w<=bp[k]){
					return k;
				}
			}
			return 'desktop';
		},
		getCurrentViewItem=items=>{
			if(!items[1]){
				return items[0]?items[0]:null;
			}
			let type=getCurrentView();
			for(let i=0,l=items.length;i<l;++i){
				if(!items[i].classList.contains('hide-'+type)){
					return items[i];
				}
			}
			return null;
		},
        changeHash=()=>{
                TB_ScrollHighlight.changeHash();
        };
        Themify.on('tb_scroll_highlight_enable',()=>{
            if(isBind===false){
                isBind=true;
                w.tfOn('hashchange',changeHash,{passive:true});
                Themify.body.on('click.tb_scroll_highlight','[href*="#"]',function(e){
                     let href = this.getAttribute('href');
                     if(href!=='' && href!==null && href!=='#'){
                         const parseUrl=new URL(href,w.location);
                         if(parseUrl.hostname===w.location.hostname && parseUrl.hash && parseUrl.pathname===w.location.pathname){
                             const hash = parseUrl.hash;
                             if(hash!=='' && hash!=='#'){
                                 const item=getCurrentViewItem(doc.querySelectorAll('.'+options.element+'[data-anchor="'+hash.replace('#','')+'"]'));
                                 if(item || getCurrentViewItem(doc.querySelectorAll(hash+'.module,'+hash+'.module_row'))){
                                     Themify.trigger('tf_side_menu_hide_all');
                                     if(item){
                                         e.preventDefault();
                                         e.stopPropagation();
                                         TB_ScrollHighlight.scrollTo(item,hash);
                                     }
                                 }
                             }
                         }
                     }
                 });
            }
        })
        .on('tb_scroll_highlight_disable',()=>{
            if(observer){
                observer.disconnect();
                observer=null;
            }
            isBind=false;
            w.tfOff('hashchange',changeHash,{passive:true});
            Themify.body.off('click.tb_scroll_highlight');
        })
        .on('tb_init_scroll_highlight',el=>{
			TB_ScrollHighlight.init(el);
			Themify.trigger( 'tb_scroll_highlight_enable' );
        });
        
        
})(jQuery,Themify, window, document);
