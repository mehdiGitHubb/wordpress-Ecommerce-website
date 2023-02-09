/**
 * Lightbox Themibox module
 */
;
(($,Themify, doc) =>{
    'use strict';
    let clickedItem,
        mainImg,
        product,
        wrap = doc.createElement('div'),
        container = doc.createElement('div'),
        wrapCl=wrap.classList,
        contCl=container.classList;
    const pattern = doc.createElement('div'),
        ver=themify_vars.theme_v,
        cssUrl=themifyScript.wc_css_url,
        keyUp = (e)=>{
            if (e.keyCode === 27) {
                closeLightBox(e);
            }
        },
        loadCss=()=>{
            let concateCss=doc.tfId('themify_concate-css');
            if(concateCss){
                concateCss=concateCss.nextElementSibling;
            }
            const js=themifyScript.wc_gal,
                prms=[];
            if (!doc.body.classList.contains('product-single')) {
                prms.push(Themify.loadCss(cssUrl + 'single/product','theme_single_product', ver, concateCss));
            } 
            prms.push(Themify.loadCss(cssUrl + 'lightbox','theme_lightbox', ver));

            if (doc.tfClass('woocommerce-breadcrumb').length === 0) {
                prms.push(Themify.loadCss(cssUrl + 'breadcrumb','theme_breadcrumb', ver,concateCss));
            }
            if(js){
                    for(let k in js){
                        let test=false;
                        if(k==='flexslider' || k==='zoom'){
                                test= $[k]!==undefined;
                        }
                        else if(k==='photoswipe'){
                                test= typeof PhotoSwipe!=='undefined';
                        }
                        else if(k==='photoswipe-ui-default'){
                                test= typeof PhotoSwipeUI_Default!=='undefined';
                        }
                        prms.push(Themify.loadJs(js[k].s,test, js[k].v));
                    }
            }
            return Promise.all(prms);
        },
        closeLightBox = function(e) {
            if (e.type !== 'keyup') {
                e.preventDefault();
            }
            if (clickedItem) {
                pattern.tfOn('transitionend', function(e) {
                    const _callback =  ()=>  {
                        wrapCl.add('tf_hide');
                        container.innerHTML = '';
                        Themify.trigger('themiboxclosed');
						doc.body.classList.remove('post-lightbox');
                        doc.tfOff('keyup', keyUp, {
                            passive: true
                        });
                        clickedItem = mainImg = product = null;
                    };
                    doc.body.classList.remove('post-lightbox');
                    if (wrapCl.contains('lightbox-message')) {
                        wrapCl.remove('lightbox-message');
                        wrap.tfOn('transitionend', function(e) {
                            this.style['top'] = '';
                            _callback();
                        }, {
                            passive: true,
                            once: true
                        })
                        .style['top'] = '150%';
                    } else {

                        wrapCl.add('lightbox_closing', 'post-lightbox-prepare');
                        const box = product.getBoundingClientRect();
                        wrap.tfOn('transitionend', function(e) {
                            mainImg.style['display'] = '';
                            contCl.remove('tf_hidden');
                            container.style['transition'] = '';
                            this.tfOn('transitionend', function() {
                                this.classList.remove('post-lightbox-prepare', 'lightbox_closing');
                                _callback();
                            }, {
                                passive: true,
                                once: true
                            })
                            .style['top'] = box.top + (box.height / 2) + 'px';
                            this.style['left'] = box.left + (box.width / 2) + 'px';
                        }, {
                            passive: true,
                            once: true
                        });
                        container.style['transition'] = 'none';
                        contCl.add('tf_hidden');
                        for (let items = container.children, i = items.length - 1; i > -1; --i) {
                            if (mainImg !== items[i]) {
                                items[i].remove();
                            }
                        }

                        wrap.style['width'] = box.width + 'px';
                        wrap.style['height'] = box.height + 'px';
                    }
                    this.classList.add('tf_hide');
                }, {
                    passive: true,
                    once: true
                });
                pattern.classList.add('tf_opacity');
            }
        },
        addToBasket =  ()=>  {
            const wr = doc.createElement('div'),
                header = doc.createElement('h3'),
                close = doc.createElement('a'),
                checkout = doc.createElement('a');
            wr.className = 'tf_textc lightbox-added';
            header.textContent = themifyScript.lng.add_to;
            close.className = 'button outline';
            close.textContent = themifyScript.lng.keep_shop;
            close.tfOn('click', closeLightBox, {
                once: true
            })
            .href = '#';
            checkout.href = themifyScript.checkout_url;
            checkout.className = 'button checkout';
            checkout.textContent = themifyScript.lng.checkout;
            wr.append(header,close,checkout);
            return wr;
        },
        clickLightBox = function(e) {
            e.preventDefault();
            if (clickedItem) {
                return false;
            }
            clickedItem=this;

            Themify.wc(true); //start to load js files
            product=clickedItem.closest('.product,.slide-inner-wrap,.type-product');
            if(product){
                product=product.querySelector('.product-image,.post-image');
            }
            if (!product) {
                return false;
            }
            wrapCl.add('woocommerce','woo_qty_btn', 'post-lightbox-prepare', 'tf_hide');
            let url = clickedItem.href,
                imgUrl = product.tfTag('img')[0];
            if (!themify_vars.wc_js) {
                url = Themify.updateQueryString('load_wc', '1', url);
            }
            imgUrl = imgUrl ? imgUrl.src : clickedItem.dataset.image;
            if (!imgUrl) {
                imgUrl = themifyScript.placeholder;
            }
            mainImg = new Image();
            mainImg.src = imgUrl;
            const box = product.getBoundingClientRect(),
                ajax=Themify.fetch('','html',{method:'GET'},Themify.updateQueryString('post_in_lightbox', '1', url));
            wrap.style['width'] = box.width + 'px';
            wrap.style['height'] = box.height + 'px';
            wrap.style['top'] = box.top + (box.height / 2) + 'px';
            wrap.style['left'] = box.left + (box.width / 2) + 'px';
            
            Promise.all([mainImg.decode(), loadCss()]).then(() => {
				
                    container.appendChild(mainImg);
                    pattern.classList.remove('tf_hide');
                    wrapCl.remove('tf_hide');
                    setTimeout(  ()=>  {
                        wrap.tfOn('transitionend', e=> {
                            doc.body.classList.add('post-lightbox');
                            pattern.classList.remove('tf_opacity');
                            contCl.add('post-lightbox-flip-infinite');
                            ajax.then(resp=> {
                                

                                Themify.on('single_slider_loaded',()=>{
									setTimeout(()=>{
											
											container.tfOn('animationiteration', function() {
													this.classList.remove('post-lightbox-flip-infinite');
													
													wrap.tfOn('transitionend', e=> {
														wrap=wrapClone;
														container=cloneContainer;
														contCl=container.classList;
														wrapCl=wrap.classList;
														wrap.style['top']=wrapClone.style['left']='';
														wrapCl.remove('tf_opacity');
															wrap.previousSibling.remove();
															wrap.style['transition'] ='none';
															wrap.style['width'] = wrap.style['height'] = container.style['transition'] = '';
															if (typeof ThemifyShoppeAjaxCart === 'function') {
                                                                const formCart=container.querySelector('form.cart');
                                                                if(formCart){
                                                                    formCart.tfOn('submit', ThemifyShoppeAjaxCart);
                                                                }
															}
															wrapClone=cloneContainer=null;
															contCl.remove('tf_opacity');
															doc.tfOn('keyup', keyUp, {
																	passive: true
															});
															setTimeout(()=>{
																wrap.style['transition'] ='';
																wrap.lastElementChild.tfOn('click', closeLightBox,{once:true});
															},10);
															
													}, {
															once: true,
															passive: true
													})
                                                    .style['transition'] ='';
													container.style['transition'] = 'none';
													contCl.add('tf_opacity');
													wrapCl.remove('post-lightbox-prepare');
													wrap.style['width'] =wrapClone.clientWidth+'px';
													wrap.style['height'] = wrapClone.clientHeight+'px';
											}, {
													passive: true,
													once: true
											});
									},15);
                                        
                                },true);
                                    
                                    
                                let wrapClone=wrap.cloneNode(true),
                                    cloneContainer=wrapClone.firstChild;
                                    cloneContainer.style['transition'] = wrapClone.style['transition'] ='none';
									cloneContainer.classList.remove('post-lightbox-flip-infinite');
									wrapClone.classList.remove('post-lightbox-prepare');
									wrapClone.classList.add('tf_opacity');
                                    wrapClone.style['top']= wrapClone.style['left']='-1000vh';
									wrapClone.style['width'] = wrapClone.style['height'] = '';
									
									mainImg=cloneContainer.tfTag('img')[0];
									mainImg.style['display'] ='none';
                                    cloneContainer.appendChild(resp);
                                    wrap.after(wrapClone);

                                    Themify.fonts(cloneContainer);
                                    let pswp = doc.tfClass('pswp')[0];
                                    if (!pswp) {
										pswp = cloneContainer.tfClass('pswp')[0];
										if (pswp) {
												doc.body.prepend(pswp);
										}
                                    }
                                    if (cloneContainer.tfClass('share-wrap')[0]) {
                                        let concate=doc.tfId('themify_concate-css');
                                        if(concate){
                                            concate=concate.nextElementSibling;
                                        }
                                        Themify.loadCss(cssUrl + 'social-share','theme_social_share', ver, concate);
                                    }
                                    Themify.trigger('themiboxloaded', cloneContainer);
                                    Themify.wc(true);
                            });
                        }, {
                            once: true,
                            passive: true
                        });
                        wrap.style['height'] = '';
                        wrap.style['width'] = (box.width > 180 ? 180 : box.width) + 'px';
                        wrap.style['top'] = wrap.style['left'] = '';
                    }, 15);
            });
        };
    Themify.on('themify_theme_themibox_run', clicked =>{
        const f = doc.createDocumentFragment(),
            close = doc.createElement('a');
        wrap.id = 'post-lightbox-wrap';
        wrap.className = 'tf_scrollbar tf_box tf_hide';
        pattern.id = 'pattern';
        pattern.tfOn('click', closeLightBox).className = 'tf_opacity tf_hide tf_w tf_h';
        container.id = 'post-lightbox-container';
        container.className = 'tf_box tf_w tf_h tf_overflow';
        close.className = 'close-lightbox tf_close';
        close.href = '#';
        wrap.append(container,close);
        f.append(wrap,pattern);
        Themify.body.on('click', '.themify-lightbox',clickLightBox)
        .on('added_to_cart',   ()=>  {
            if (clickedItem) {
                wrapCl.add('lightbox-message');
                wrap.tfOn('transitionend', function(e) {
                    this.style['padding'] = '';
                    container.appendChild(addToBasket());
                    this.tfOn('transitionend', function(e) {
                        this.style['max-height'] = '';
                    }, {
                        passive: true,
                        once: true
                    });
                    this.style['max-height'] = '100%';
                }, {
                    passive: true,
                    once: true
                });
                container.innerHTML = '';
                wrap.style['padding'] = wrap.style['max-height'] = '0px';
            }
        })[0].appendChild(f);
        clicked.click();
    }, true);
})(jQuery,Themify, document);