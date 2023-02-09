;
( ($, Themify, win, doc, themeVars)=> {
    'use strict';
    const svgs = new Map(),
            ThemifyShop = {
                init(ThemifyTheme) {
                    this.v = ThemifyTheme.v;
                    this.url = ThemifyTheme.url;
                    this.jsUrl = ThemifyTheme.jsUrl + 'wc/';
                    this.cssUrl = this.url + 'styles/wc/modules/';
                    this.bodyCl = ThemifyTheme.bodyCl;
                    this.sideMenu();
                    this.wcAjaxInit();
                    this.events();
                    this.initProductSlider();
                    Themify.on('infiniteloaded', ()=>{
                        this.initProductSlider();
                    });
                    this.initWishlist();
                    setTimeout(()=>{this.clicks();this.initThemibox();}, 1000);
                    if (this.bodyCl.contains('single-product')) {
                        this.singleProductSlider();
                        setTimeout(()=>{this.plusMinus();}, 800);
                    }
                    this.pluginCompatibility();
                },
                events() {
                    Themify.body
                            .on('keyup', 'input.qty', function () {
                                const el = $(this),
                                        max_qty = parseInt(el.attr('max'), 10);
                                if (el.val() > max_qty) {
                                    el.val(max_qty);
                                }
                            });
                    Themify
                            .on('themify_theme_spark', (item, options)=> {
                                this.clickToSpark(item, options);
                            })
                            .on('themiboxloaded', container=> {
                                if ($.fn.prettyPhoto) {
                                    // Run WooCommerce PrettyPhoto after Themibox is loaded
                                    $(".thumbnails a[data-rel^='prettyPhoto']", container).prettyPhoto({
                                        hook: 'data-rel',
                                        social_tools: false,
                                        theme: 'pp_woocommerce',
                                        horizontal_padding: 20,
                                        opacity: .8,
                                        deeplinking: false
                                    });
                                    Themify.trigger('single_slider_loaded');
                                } else {
									this.singleProductSlider(container).then(()=>{
										Themify.trigger('single_slider_loaded');
										this.plusMinus(container);
									});
                                }
                            });
                },
                clickToSpark(item, options) {
                    if (themeVars.sparkling_color !== undefined) {
                        if (!options) {
                            options = {};
                        }
                        if (!options.text) {
                            options.text = 'ti-shopping-cart';
                        }
                        let isWorking = false;
                        const path = this.url + 'images/' + options.text + '.svg',
                                callback=()=>{
                                    if (svgs.has(path)) {
                                        return 1;
                                    } 
                                    else {
                                        return Themify.fetch(null,'text',{method:'GET',credentials:'omit'},path)
                                        .then(text => {
                                            const color = themeVars.sparkling_color;
                                            if (color !== '#dcaa2e') {
                                                text = text.replace('#dcaa2e', color);
                                            }
                                            svgs.set(path, 'data:image/svg+xml;base64,' + win.btoa(text));
                                        });
                                    }
                                };
                                Promise.all([Themify.loadJs(this.jsUrl + 'clickspark.min',!!win.clickSpark, '1.0'),callback()]).then(()=>{
                                    if (!isWorking) {
                                        isWorking = true;
                                        options = Object.assign({
                                            duration: 300,
                                            count: 30,
                                            speed: 8,
                                            type: 'splash',
                                            rotation: 0,
                                            size: 10
                                        }, options);
                                        clickSpark.setParticleImagePath(svgs.get(path));
                                        clickSpark.setParticleDuration(options.duration);
                                        clickSpark.setParticleCount(options.count);
                                        clickSpark.setParticleSpeed(options.speed);
                                        clickSpark.setAnimationType(options.type);
                                        clickSpark.setParticleRotationSpeed(options.rotation);
                                        clickSpark.setParticleSize(options.size);
                                        clickSpark.fireParticles($(item));
                                    }
                                });
                    }
                },
                initWishlist() {
                    if (themeVars.wishlist !== undefined) {
                        const self = this,
                                getCookie = ()=> {
                                    const cookie = ' ' + doc.cookie,
                                            search = ' ' + themeVars.wishlist.cookie + '=',
                                            setStr = [];
                                    if (cookie.length > 0) {
                                        let offset = cookie.indexOf(search);
                                        if (offset !== -1) {
                                            offset += search.length;
                                            let end = cookie.indexOf(';', offset);
                                            if (end === -1) {
                                                end = cookie.length;
                                            }
                                            const arr = JSON.parse(unescape(cookie.substring(offset, end)));
                                            for (let x in arr) {
                                                setStr.push(arr[x]);
                                            }
                                        }
                                    }
                                    return setStr;
                                };
                        setTimeout(() =>{
                            // Assign/Reassign wishlist buttons based on cookie
                            const wb = doc.tfClass('wishlist-button'),
                                    cookies = getCookie(),
                                    icon = doc.querySelector('.wishlist .icon-menu-count'),
                                    total = cookies.length;
                            for (let k = wb.length - 1; k > -1; --k) {
                                wb[k].classList.toggle('wishlisted', cookies.includes(parseInt(wb[k].dataset.id)));
                            }
                            // Update wishlist count
                            if (icon) {
                                icon.classList.toggle('wishlist_empty', total === 0);
                                icon.textContent = total;
                            }
                        }, 1500);
                        if (self.bodyCl.contains('wishlist-page')) {
                            Themify.fetch({action:'themify_load_wishlist_page'},'html').then(res=>{
								doc.tfClass('page-content')[0].appendChild(res);
							});
                        }
                        Themify.body.on('click.tf_wishlist', '.wishlisted,.wishlist-button', function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            Themify.loadJs(self.jsUrl + 'themify.wishlist',null,self.v).then(()=> { 
                                Themify.body.off('click.tf_wishlist');
                                $(this).click();
                            });
                        });
                    }
                },
                singleProductSlider(container) {
					return new Promise((resolve,reject)=>{
						container = container || doc;
						const items = container.tfClass('woocommerce-product-gallery__wrapper')[0];
						if (items && items.tfClass('tf_swiper-container')[0]) {
							Themify.loadJs(this.jsUrl + 'single-slider',null,this.v).then(()=> {
									Themify.trigger('themify_theme_product_single_slider', items).then(resolve);
							})
							.catch(reject);
						}
						else{
							resolve();
						}
					});
                },
                plusMinus(el) {
                    el = el|| doc.body;
                    const items = el.querySelectorAll('#minus1,#add1');
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].tfOn('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const input = this.closest('form').tfClass('qty')[0];
                            if (input) {
                                let v = (input.value) * 1;
                                const min = parseInt(input.min),
                                        step = input.step > 0 ? parseInt(input.step) : 1,
                                        max = parseInt(input.max);
                                v -= (this.id === 'minus1' ? step : -1 * (step));
                                if (v < min) {
                                    v = min;
                                } else if (!isNaN(max) && v > max) {
                                    v = max;
                                }
                                input.value = v;
                            }
                        });
                    }
                },
                initProductSlider() {
                    if (!this.bodyCl.contains('wishlist-page')) {
                        const items = doc.tfClass('product-slider'),
                                ev = Themify.isTouch ? 'touchstart' : 'mouseover',
                                self = this;
                        for (let i = items.length - 1; i > -1; --i) {
                            if (items[i].hasAttribute('data-product-slider') && !items[i].classList.contains('slider_attached') && !items[i].classList.contains('hovered')) {
                                items[i].tfOn(ev, function () {
                                    if (!this.classList.contains('hovered')) {
                                        this.classList.add('hovered');
                                        Themify.loadJs(self.jsUrl + 'slider',null,self.v).then(()=> {
                                            Themify.trigger('themify_theme_product_slider', [this]);
                                        });
                                    }
                                }, {passive: true, once: true})
                                .className += ' slider_attached';
                            }
                        }
                    }
                },
                initThemibox() {
                    const self=this;
                    Themify.body.one('click', '.themify-lightbox', function(e){
                        e.preventDefault();
                        Themify.loadJs(self.jsUrl + 'themibox',null,self.v).then(()=>{
                            Themify.trigger('themify_theme_themibox_run',[this]);
                        });
                    });
                },
                clicks() {
                    // reply review
                    let items = doc.tfClass('reply-review');
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].tfOn('click', e=> {
                            e.preventDefault();
                            $('#respond').slideToggle('slow');
                        });
                    }
                    // add review
                    items = doc.tfClass('add-reply-js');
                    for (let i = items.length - 1; i > -1; --i) {
                        items[i].tfOn('click', function (e) {
                            e.preventDefault();
                            $(this).hide();
                            $('#respond').slideDown('slow');
                            $('#cancel-comment-reply-link').show();
                        });
                    }
                    items = doc.tfId('cancel-comment-reply-link');
                    if (items !== null) {
                        items.tfOn('click', function (e) {
                            e.preventDefault();
                            $(this).hide();
                            $('#respond').slideUp();
                            $('.add-reply-js').show();
                        });
                    }
                },
                wcAjaxInit() {
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        Themify.loadJs(this.jsUrl + 'ajax_to_cart', null, this.v);
                    }
                },
                sideMenu() {
                    if (null === doc.tfId('slide-cart')) {
                        return;
                    }
                    const self = this;
                    let isLoad = false;
                    Themify.sideMenu(doc.querySelectorAll('#cart-link,#cart-link-mobile-link'), {
                        panel: '#slide-cart',
                        close: '#cart-icon-close',
                        beforeShow() {
                            if (isLoad === false) {
                                if (doc.tfId('cart-wrap')) {
                                    this.panelVisible = true;
                                    Themify.loadCss(self.cssUrl + 'basket',null, self.v).then(()=> {
                                        isLoad = true;
                                        this.panelVisible = false;
                                        this.showPanel();
                                    });
                                }
                            }
                        }
                    });
                },
                pluginCompatibility() {
                    // compatibility with plugins
                    if (doc.querySelector('.loops-wrapper.products')) {
                        const events = {wpf_form: 'wpf_ajax_success', 'yit-wcan-container': 'yith-wcan-ajax-filtered'};
                        for (let k in events) {
                            if (doc.tfClass(k)[0]) {
                                $(doc).on(events[k], ()=> {
                                    this.initProductSlider();
                                });
                            }
                        }
                    }
                }
            };

    //Remove brackets
    for (let items = doc.querySelectorAll('.widget_product_categories .count'), i = items.length - 1; i > -1; --i) {
        items[i].textContent = items[i].textContent.replace('(', '').replace(')', '');
    }
    Themify.on('themify_theme_shop_init', ThemifyTheme=> {
        ThemifyShop.init(ThemifyTheme);
    }, true);


})(jQuery, Themify, window, document, themifyScript);