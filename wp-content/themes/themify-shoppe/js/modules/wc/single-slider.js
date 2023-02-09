/**
 * Module Product Gallery
 */
;
( ($, doc, Themify)=>{
    'use strict';
    let isFormInit = null;
    const cssUrl = themifyScript.wc_css_url,
        ver=themify_vars.theme_v,
        loadCss = ()=> {
            let concateCss=doc.tfId('themify_concate-css');
            if(concateCss){
                concateCss=concateCss.nextElementSibling;
            }
            return Promise.all([
                Themify.loadCss('swiper/swiper', 'tf_swiper',themify_vars.s_v),
                Themify.loadCss(cssUrl + 'single/slider','theme_single_slider_css', ver, concateCss),
                Themify.carousel('load')
            ]);
    },
    zoomInit =  item=> {
        const items = item.tfClass('zoom');
        for (let i = items.length - 1; i > -1; --i) {
            items[i].tfOn('click', function (e) {
                if (!this.hasAttribute('data-zoom-init')) {
                    this.setAttribute('data-zoom-init', true);
                    Themify.loadJs(themify_vars.theme_url + '/js/modules/wc/zoom',null,ver).then(()=>{
                        Themify.trigger('themify_theme_product_zoom', [this, e]);
                    });
                }
            },{passive:true});
        }
    };
    Themify.on('themify_theme_product_single_slider',  item=> {
        return new Promise(resolve=>{
			
			const items = item.tfClass('tf_swiper-container'),
					main = items[0],
					thumbs = items[1];
			setTimeout( ()=> {
				zoomInit(main);
			}, 800);
			
			loadCss().then(() =>{
				if (main.tfClass('tf_swiper-slide').length <= 1) {
					const arr=[main,thumbs];
					for(let i=arr.length-1;i>-1;--i){
						if(arr[i]){
							arr[i].classList.add('tf_swiper-container-initialized');
							arr[i].classList.remove('tf_hidden');
							Themify.lazyScroll(arr[i].querySelectorAll('[data-lazy]'), true);
						}
					}
					resolve();
					return;
					
				}
				Themify.imagesLoad(thumbs).then(el=> {
					Themify.carousel(el, {
						direction: 'vertical',
						visible: 'auto',
						height: 'auto',
						wrapvar: false
					});
				});
				Themify.imagesLoad(main).then(el=> {
					Themify.carousel(el, {
						slider_nav: false,
						pager: false,
						wrapvar: false,
						height: 'auto',
						thumbs: thumbs,
						onInit() {
							/* when using Product Image module in Builder Pro, use the closest Row as the container */
							const container = this.el.closest('.module-product-image') ? this.el.closest('.themify_builder_row') : this.el.closest('.product'),
									form = isFormInit === null ? container.tfClass('variations_form')[0] : null;
							if (form) {
								let isInit = null;
								// Variation zoom carousel fix for Additional Variation Images by WooCommerce Addon
								if (typeof wc_additional_variation_images_local === 'object') {
									isFormInit = true;
									$(form).on('wc_additional_variation_images_frontend_image_swap_callback',  (e, response)=> {
										if (isInit === true) {
											const tmp = doc.createElement('div');
											tmp.innerHTML = response.main_images;
											Themify.loadJs(themify_vars.theme_url + '/js/modules/wc/additional_variations_images',null,ver).then(()=>{
												galleryThumbs.destroy(true, true);
												this.destroy(true, true);
												Themify.trigger('themify_theme_additional_variations_images_init', [tmp.tfClass('woocommerce-product-gallery__image'), main, thumbs]);
											});
										}
									}).on('found_variation hide_variation', function (e) {
										if (e.type === 'hide_variation') {
											if (isInit === true) {
												$(this).off('hide_variation');
											}
										} else {
											isInit = true;
										}
									});
								} else {

									const mainImage = this.el.tfTag('img')[0],
											thumbImage = thumbs.tfTag('img')[0],
											cloneMain = mainImage.cloneNode(false),
											cloneThumb = thumbImage.cloneNode(false),
											zoomUrl = mainImage.parentNode.getAttribute('data-zoom-image');
									$(form).on('found_variation', (e, v)=> {
										Themify.loadCss(cssUrl + 'reset-variations', ver);
										const images = v.image;
										if (typeof images.full_src === 'string') {
											isInit = true;
											const zoomed = mainImage.closest('.zoom');
											if (zoomed) {
												$(zoomed).trigger('zoom.destroy')[0].removeAttribute('data-zoom-init');
												zoomed.setAttribute('data-zoom-image', images.full_src);
											}
											mainImage.setAttribute('src', (images.src ? images.src : images.full_src));
											mainImage.setAttribute('srcset', (images.srcset ? images.srcset : ''));
											thumbImage.setAttribute('src', images.gallery_thumbnail_src);
											this.slideTo(0, this.params.speed);
										}
									})
											.on('hide_variation', ()=> {
												if (isInit === true) {
													mainImage.setAttribute('src', (cloneMain.hasAttribute('src') ? cloneMain.getAttribute('src') : ''));
													mainImage.setAttribute('srcset', (cloneMain.hasAttribute('srcset') ? cloneMain.getAttribute('srcset') : ''));
													thumbImage.setAttribute('src', (cloneThumb.hasAttribute('src') ? cloneThumb.getAttribute('src') : ''));
													const zoomed = mainImage.closest('.zoom');
													if (zoomed) {
														$(zoomed).trigger('zoom.destroy')[0].removeAttribute('data-zoom-init');
														zoomed.setAttribute('data-zoom-image', zoomUrl);
													}
													this.slideTo(0, this.params.speed);
													isInit = null;
												}
											});
										
								}
								Themify.wc(true);
							}
							resolve();
						}
					});
				});
			});
		});
    });

})(jQuery, document, Themify);
