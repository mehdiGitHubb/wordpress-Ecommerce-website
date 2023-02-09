/**
 * carousel module
 */
;
((Themify, doc,und) => {
    'use strict';
    const loadedImages = new Set(),
            v = themify_vars.s_v,
            cssUrl = 'swiper/',
            jsUrl =  'swiper/',
        removeLoader = (item, remove) => {
                if(item.isConnected){
                    const p = item.parentNode,
                            slide = p.closest('.tf_swiper-slide');
                    if (remove !== false) {
                        item.classList.remove('tf_svg_lazy');
                    }
                    p.classList.remove('tf_lazy');
                    p.parentNode.classList.remove('tf_lazy');
                    slide.classList.remove('tf_lazy');
                    slide.classList.add('tf_swiper_lazy_done');
                }
            },
            createVideo = url => {
                const attr = Themify.parseVideo(url);
                let iframe,
                        src = url;
                if (attr.type === 'youtube' || attr.type === 'vimeo') {
                    let allow = '',
                            params = new URL(src);
                    params = params.search ? params.searchParams : null;
                    iframe = doc.createElement('iframe');
                    if (attr.type === 'youtube') {
                        src = 'https://www.youtube.com/embed/' + attr.id + '?autohide=1&border=0&wmode=opaque&playsinline=1';
                        if (params) {
                            const t = params.get('t'),
                                    list = params.get('list');
                            if (t) {
                                src += '&start=' + t;
                            }
                            if (list) {
                                src += '&list=' + list;
                            }
                        }
                        allow = 'accelerometer;encrypted-media;gyroscope;picture-in-picture;autoplay';
                    } else {
                        src = '//player.vimeo.com/video/' + attr.id + '?portrait=0&title=0&badge=0';
                        allow = 'fullscreen;autoplay';
                    }
                    src += params && params.get('autoplay') ? '&autoplay=1' : '';
                    iframe.className = 'tf_abs';
                    iframe.setAttribute('allow', allow);
                } else {
                    iframe = doc.createElement('video');
                    iframe.setAttribute('webkit-playsinline', '1');
                    iframe.setAttribute('playsinline', '1');
                    iframe.controls = true;
                }
                iframe.className += ' tf_w tf_h';
                iframe.src = src;
                return iframe;
            },
            transitionStart = function () {
                Themify.trigger('tf_swiper_transition_start_begin', [this, this.activeIndex]);
                if (this.activeIndex > this.params.slidesPerView && this.params.slidesPerView !== 'auto') {
                    const max = this.activeIndex + this.params.slidesPerView,
                            im = new Image();
                    for (let i = this.activeIndex; i < max; ++i) {
                        if (this.slides[i]) {// cache
                            Themify.trigger('tf_swiper_start_slide_begin', [this, this.slides[i]]);
                            let _images = this.slides[i].tfClass('tf_svg_lazy');
                            for (let j = _images.length - 1; j > -1; --j) {
                                if (_images[j].hasAttribute('data-tf-src')) {
                                    let src = _images[j].dataset.tfSrc;
                                    if (!loadedImages.has(_images[j])) {
                                        loadedImages.add(_images[j]);
                                        _images[j].src = src;
                                    }
                                } else {
                                    removeLoader(_images[j]);
                                }
                            }
                            Themify.trigger('tf_swiper_start_slide_end', [this, this.slides[i]]);
                        }
                    }
                }
                Themify.trigger('tf_swiper_transition_start_end', [this, this.activeIndex]);
            },
            transitionEnd = function () {
                Themify.trigger('tf_swiper_transition_end_begin', [this, this.activeIndex]);
                const max = this.activeIndex + (this.params.slidesPerView !== 'auto' ? this.params.slidesPerView : this.el.tfClass('tf_swiper-slide-visible').length),
                        auto = this.params.autoplay && this.params.autoplay.enabled,
                        hasLazy = this.params.lazy !== false,
                        thumb = this.params.thumbs && this.params.thumbs.swiper && this.params.thumbs.swiper.params.autoplay ? this.params.thumbs.swiper.autoplay : false,
                        isStopped = !!this.el.dataset.stopped,
                        limit = this.params.effect === 'flip' ? this.slides.length : max;
                for (let i = (this.params.effect === 'flip' ? 0 : this.activeIndex); i < limit; ++i) {
                    if (this.slides[i]) {
                        Themify.trigger('tf_swiper_end_slide_begin', [this, this.slides[i], hasLazy]);
                        if (hasLazy === true) {
                            let _images = this.slides[i].tfClass('tf_svg_lazy'),
                                    len = _images.length;
                            if (len > 0) {
                                if (isStopped === false) {
                                    if (auto) {
                                        this.autoplay.stop();
                                    }
                                    if (thumb !== false) {
                                        thumb.stop();
                                    }
                                }
                                for (let j = len - 1; j > -1; --j) {
                                    let imgEl=_images[j];
                                    if (imgEl) {
                                        if (imgEl.hasAttribute('data-lazy')) {
                                            Themify.lazyScroll(imgEl, true)
                                            .finally(() => {
                                                removeLoader(imgEl, false);
                                                Themify.trigger('tf_swiper_lazy_start', [this, this.slides[i], imgEl]);
                                                if (j === 0) {
                                                    if (this.params.autoHeight) {
                                                        this.updateAutoHeight(this.params.speed, false);
                                                    }
                                                    this.updateSize();
                                                    this.updateSlides();
                                                    if (isStopped === false) {
                                                        if (auto) {
                                                            this.autoplay.start();
                                                        }
                                                        if (thumb !== false) {
                                                            thumb.start();
                                                        }
                                                    }
                                                }
                                                Themify.trigger('tf_swiper_lazy_end', [this, this.slides[i], imgEl]);
                                            });
                                        } else {
                                            removeLoader(imgEl);
                                            if (j === 0 && isStopped === false) {
                                                if (auto) {
                                                    this.autoplay.start();
                                                }
                                                if (thumb !== false) {
                                                    thumb.start();
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                this.slides[i].classList.remove('tf_lazy');
                                let lazy = this.slides[i].tfClass('tf_lazy')[0];
                                if (lazy) {
                                    lazy.classList.remove('tf_lazy');
                                }
                            }
                        }
                        let wrap = this.slides[i].querySelectorAll('.video-wrap[data-url]'),
                                len = wrap.length;
                        if (len > 0) {
                            if (isStopped === false) {
                                if (auto) {
                                    this.autoplay.stop();
                                }
                                if (thumb !== false) {
                                    thumb.stop();
                                }
                            }
                            for (let j = len - 1; j > -1; --j) {
                                wrap[j].appendChild(createVideo(wrap[j].dataset.url));
                                wrap[j].removeAttribute('data-url');
                                wrap[j].classList.remove('tf_lazy');
                            }
                            if (this.params.autoHeight) {
                                this.updateAutoHeight(this.params.speed, false);
                            }
                            this.updateSize();
                            this.updateSlides();
                            if (isStopped === false) {
                                if (auto) {
                                    this.autoplay.start();
                                }
                                if (thumb !== false) {
                                    thumb.start();
                                }
                            }
                        }
                        Themify.trigger('tf_swiper_end_slide_end', [this, this.slides[i], hasLazy]);
                    }
                }
                Themify.trigger('tf_swiper_transition_end', [this, this.activeIndex]);
            },
            onInit = function () {
                transitionEnd.call(this);
                if (this.params.autoplay && this.params.autoplay.enabled && this.params.autoplay.disableOnInteraction === true) {
                    const self = this,
                            thumb = this.params.thumbs && this.params.thumbs.swiper ? this.params.thumbs.swiper : false;
                    this.el.tfOn('mouseenter', function () {
                        self.autoplay.stop();
                        if (thumb !== false && thumb.params.autoplay) {
                            self.params.thumbs.swiper.autoplay.stop();
                        }
                        if (!this.dataset.stopped) {
                            this.tfOn('mouseleave', () => {
                                self.autoplay.start();
                                if (thumb !== false && thumb.params.autoplay) {
                                    self.params.thumbs.swiper.autoplay.start();
                                }
                            }, {passive: true, once: true});
                        }
                    }, {passive: true});
                    if (thumb !== false) {
                        thumb.el.tfOn('mouseenter', function () {
                            self.autoplay.stop();
                            this.tfOn('mouseleave', () => {
                                self.autoplay.start();
                            }, {passive: true, once: true});
                        }, {passive: true});
                    }
                }
                this.el.classList.remove('tf_lazy');
                this.el.parentNode.classList.remove('tf_lazy');
                if (this.params.onInit) {
                    this.params.onInit.call(this);
                }
                Themify.triggerEvent(this.el, 'tf_carousel_init', this);
                Themify.trigger('tf_swiper_init', this);
            },
            _init = (items, options) => {
        for (let i = items.length - 1; i > -1; --i) {
            let el=items[i];
            if (!el.classList.contains('tf_swiper-process') && !el.classList.contains('tf_swiper-container-initialized')) {
                el.classList.add('tf_swiper-process');
                if (Themify.isRTL) {
                    el.setAttribute('dir', 'rtl');
                }
                let pr=themify_vars.lz?Themify.lazyScroll(el.querySelectorAll('[data-lazy]'), true):Themify.imagesLoad(el);
                pr.finally(() => {
                    let maxLen = el.tfClass('tf_swiper-slide').length,
                            hasthumb = false,
                            keys = [],
                            prms=[],
                    loadCssModules = cssItems=>{
                        if (cssItems) {
                            for (let css=cssItems.split(','),i = css.length - 1; i > -1; --i) {
                                prms.push(Themify.loadCss(css[i]));
                            }
                        }
                    };
                    let speed = el.dataset.speed;
                    if (speed === 'slow') {
                        speed = 4;
                    } else if (speed === 'fast') {
                        speed = .5;
                    } else if (speed === 'normal') {
                        speed = 1;
                    }
                    const data = options ? options : {
                        wrapvar: el.dataset.wrapvar ? parseInt(el.dataset.wrapvar) !== 0 : true,
                        pause_hover: el.dataset.pause_hover ? parseInt(el.dataset.pause_hover) !== 0 : true,
                        auto: el.dataset.auto,
                        autoRevers: el.dataset.autoReverse,
                        scroll: el.dataset.scroll,
                        space: el.dataset.space || 0,
                        effect: el.dataset.effect,
                        speed: speed,
                        height: el.dataset.height,
                        horizontal: el.dataset.horizontal ? parseInt(el.dataset.horizontal) !== 0 : false,
                        visible: el.dataset.visible,
                        pager: el.dataset.pager ? parseInt(el.dataset.pager) !== 0 : true,
                        slider_nav: el.dataset.slider_nav ? parseInt(el.dataset.slider_nav) !== 0 : true,
                        nav_out: el.dataset.nav_out,
                        controllers: parseInt(el.dataset.controller) === 1,
                        direction: el.dataset.direction,
                        keyboard: el.dataset.keyboard,
                        parallax: el.dataset.parallax,
                        mousewheel: el.dataset.mousewheel,
                        scrollbar: el.dataset.scrollbar,
                        freeMode: el.dataset.freeMode,
                        thumbs: el.dataset.thumbs,
                        page_type: el.dataset.page_type,
                        center: el.dataset.center,
                        mob_visible: el.dataset.mobVisible,
                        tab_visible: el.dataset.tabVisible,
                        breakpoints_mobile: el.dataset.mbreakpoints,
                        breakpoints_tablet: el.dataset.tbreakpoints,
                        lazy: parseInt(el.dataset.lazyScroll) !== 0,
                        has_timer: el.dataset.timer,
                        css_url: el.dataset.css_url,
                        centered: el.dataset.centered
                    },
                    args = Object.assign({
                        direction: data.direction || 'horizontal',
                        mousewheel: data.horizontal ? true : !!data.mousewheel,
                        keyboard: !!data.keyboard,
                        parallax: !!data.parallax,
                        freeMode: !!data.freeMode,
                        loop: data.wrapvar !== false,
                        autoHeight: !!(!data.height || data.height === 'variable'),
                        slidesPerView: data.visible === 'auto' ? data.visible : (data.visible > 0 ? parseInt(data.visible) : 1),
                        slidesPerGroup: data.scroll > 0 ? parseInt(data.scroll) : 1,
                        speed: data.speed ? (parseFloat(data.speed) >= 10 ? 1 * data.speed : 1000 * data.speed) : 500,
                        centeredSlides: data.centered === 'true',
                        watchSlidesProgress: true,
                        on: {}
                    }, options);
                    
                    loadCssModules(el.dataset.css_url);
                    
                    if (args.on !== null && args.on.init === und) {
                        args.on.init = onInit;
                    }
                    if (Themify.is_builder_active) {
                        args.simulateTouch = false;
                    }
                    
                    
                    if (maxLen <= 1) {
                        Themify.lazyScroll(el.querySelectorAll('[data-lazy]'), true).finally(()=>{
                            const cleanLazy = el.querySelectorAll('.tf_lazy,.tf_svg_lazy'),
                                    video = el.querySelector('.video-wrap[data-url]');
                            if (video) {
                                video.appendChild(createVideo(video.dataset.url));
                                video.removeAttribute('data-url');
                            }
                            for (let k = cleanLazy.length - 1; k > -1; --k) {
                                cleanLazy[k].classList.remove('tf_lazy', 'tf_svg_lazy');
                            }
                            el.classList.add('tf_swiper-container-initialized');
                            if (options && options.onInit) {
                                options.onInit.call();
                            }
                        });
                        return;
                    }
                    if (themify_vars.lz || data.lazy !== false) {
                        const cleanLazy = el.querySelectorAll('.tf_lazy:not(.tf_swiper-wrapper)');
                        for (let k = cleanLazy.length - 1; k > -1; --k) {
                            cleanLazy[k].classList.remove('tf_lazy');
                        }
                        args.preloadImages = false;
                        args.on.transitionStart = transitionStart;
                    } else {
                        args.lazy = false;
                        Themify.lazyScroll(el.querySelectorAll('[data-lazy]'), true);
                    }
                    if (data.onInit) {
                        args.onInit = data.onInit;
                    }
                    args.on.transitionEnd = transitionEnd;

                    let effect = data.effect || '';
                    // Backward compatibility #9463
                    effect = effect && (effect === 'crossfade' || effect === 'cover-fade' || effect === 'uncover-fade') ? 'fade' : effect;
                    if (effect && effect !== 'scroll' && (effect === 'fade' || effect === 'flip' || effect === 'cube' || effect === 'coverflow')) {
                        args.effect = effect;
                        if (effect === 'fade' || effect === 'flip' || effect === 'cube') {
                            args.slidesPerView = args.slidesPerGroup = 1;
                            data.mob_visible = data.tab_visible = 0;
                        } 
                        else if (effect === 'coverflow' && args.slidesPerView < 2) {
                            args.slidesPerView = 2;
                        }
                        if (effect !== 'fade') {
                            args[effect + 'Effect'] = {
                                slideShadows: false
                            };
                            if ('cube' === effect) {
                                args[effect + 'Effect'].shadow = false;
                            }
                        }
                        keys.push(args.effect);
                    } 
                    else {
                        args.effect = '';
                    }

                    if (args.slidesPerView === 'auto') {
                        if (data.lazy !== false) {
                            args.watchSlidesVisibility = true;
                        }
                    } else {
                        if (args.slidesPerView > maxLen) {
                            args.slidesPerView = maxLen;
                        }
                        if (args.slidesPerGroup > args.slidesPerView) {
                            args.slidesPerGroup = args.slidesPerView;
                        }
                        let count = parseInt(args.slidesPerView),
                                tabVisible = parseInt(data.tab_visible),
                                tab_breakpoints = parseInt(data.breakpoints_tablet),
                                mobVisible = parseInt(data.mob_visible),
                                mob_breakpoints = parseInt(data.breakpoints_mobile);
                        if (count > 2) {
                            if (!mobVisible) {
                                mobVisible = 2;
                                mob_breakpoints = 680;
                            }
                            if (!tabVisible) {
                                tab_breakpoints = 1024;
                                tabVisible = count <= 4 ? 2 : 3;
                            }
                        }
                        if (mobVisible > 0 || tabVisible > 0) {
                            args.breakpoints = {};
                            if (mobVisible > 0 && tabVisible !== mobVisible) {
                                args.breakpoints[mob_breakpoints] = {
                                    slidesPerView: mobVisible,
                                    slidesPerGroup: mobVisible
                                };
                            }
                            if (tabVisible > 0 && tabVisible !== count) {
                                args.breakpoints[tab_breakpoints] = {
                                    slidesPerView: tabVisible,
                                    slidesPerGroup: tabVisible
                                };
                            }
                        }
                    }
                    if (data.space > 0 && (args.slidesPerView > 1 || args.slidesPerView === 'auto')) {
                        args.spaceBetween = parseFloat(data.space);
                    }
                    if (data.horizontal === true) {
                        args.swipe = {
                            onMouse: true,
                            onTouch: true
                        };
                        args.scroll = {
                            items: 2,
                            duration: 1000,
                            timeoutDuration: 0,
                            easing: 'linear'
                        };
                        args.auto = false;
                        args.circular = false;
                        args.infinite = false;
                    } 
                    if ('continuously' === data.effect || (data.auto && data.auto !== '0' && data.auto !== 'off')) {
                        const auto = 'continuously' === data.effect ? .5 : parseFloat(data.auto);
                        args.autoplay = {
                            reverseDirection: !!data.autoRevers,
                            delay: auto >= 1000 ? auto : 1000 * auto, // in some modules (eg. Tiles) the value is in milliseconds
                            disableOnInteraction: !!(data.pause_hover || data.pause_hover === und)
                        };
                        if (data.has_timer) {
                            const timer = doc.createElement('div'),
                                    delay = args.autoplay.delay;
                            timer.className = 'tf_sw_timer tf_abs';
                            el.appendChild(timer);
                            args.on.slideChangeTransitionStart = p => {
                                let i = 0;
                                const progress = () => {
                                    if (i < 100) {
                                        requestAnimationFrame(progress);
                                        timer.style.width = i + '%';
                                        ++i;
                                    }
                                };
                                progress();
                            };
                        }
                        keys.push('autoplay');
                    }
                    if(!Themify.isTouch){
                        if (args.mousewheel === true) {
                            keys.push('mousewheel');
                        }
                        if (args.keyboard === true) {
                            keys.push('keyboard');
                        }
                    }
                    if (args.parallax === true) {
                        keys.push('parallax');
                    }
                    if (data.thumbs) {
                        if (typeof data.thumbs !== 'string' || (data.thumbs === 'next' && el.nextElementSibling !== null && el.nextElementSibling.classList.contains('tf_swiper-container')) || (data.thumbs !== 'next' && doc.tfClass(data.thumbs)[0])) {
                            keys.push('thumbs');
                            hasthumb = data.thumbs;
                        } else {
                            delete data.thumbs;
                        }
                    }
                    if (data.scrollbar) {
                        keys.push('scrollbar');
                        args.scrollbar = {
                            draggable: true,
                            hide: true,
                            el: data.scrollbar
                        };
                    }
                    if (args.direction === 'vertical') {
                        prms.push(Themify.loadCss(cssUrl + 'vertical','tf_sw_vertical'));
                    }
                    if (args.effect !== '' && args.effect !== 'scroll' && args.effect !== 'coverflow') {
                        prms.push(Themify.loadCss(cssUrl + 'effects/' + args.effect ,'tf_sw_'+args.effect));
                    }
                    for (let i = keys.length - 1; i > -1; --i) {
                        let url = keys[i] === args.effect ? jsUrl + 'effects/' : jsUrl + 'modules/';
                        prms.push(Themify.loadJs(url + keys[i] + '.min',null, v));
                    }
                    Promise.all(prms).then(()=>{
                        Themify.requestIdleCallback(()=>{
                            if (!el.classList.contains('tf_swiper-container-initialized')) {
                                const playBtn = data.controllers ? doc.createElement('a') : false;
                                if (data.slider_nav !== false || data.pager !== false || playBtn !== false) {
                                    const navWrap = doc.createElement('div');
                                    navWrap.className = 'tf_carousel_nav_wrap carousel-nav-wrap tf_w';
                                    if (data.slider_nav !== false) {
                                        const prev = doc.createElement('a'),
                                                next = doc.createElement('a');
                                        prev.className = 'tf_box tf_overflow carousel-prev';
                                        next.className = 'tf_box tf_overflow carousel-next';
                                        prev.href = '#';
                                        next.href = '#';
                                        navWrap.append(prev,next);

                                        args.navigation = {
                                            disabledClass: 'disabled',
                                            nextEl: next,
                                            prevEl: prev
                                        };
                                    }
                                    if (data.pager !== false) {
                                        const pager = doc.createElement('div');
                                        pager.className = 'tf_clear tf_textc carousel-pager';
                                        navWrap.appendChild(pager);
                                        args.pagination = {
                                            el: pager,
                                            type: data.page_type && data.page_type !== 'image_pagination' ? data.page_type : 'bullets',
                                            bulletElement: 'a',
                                            modifierClass: 'tf_carousel_',
                                            bulletClass: 'tf_carousel_bullet',
                                            currentClass: 'selected',
                                            bulletActiveClass: 'selected',
                                            clickable: true
                                        };
                                        if (data.page_type === 'image_pagination' || '1' === el.dataset.pageNum) {
                                            const paginationThumbs = data.page_type === 'image_pagination' ? el.querySelectorAll('[data-swiper-thumb]') : null;
                                            args.pagination.renderBullet = (index, className) => {
                                                let value = '';
                                                if (paginationThumbs !== null) {
                                                    if (paginationThumbs[index]) {
                                                        value = '<img importance="low" decoding="async" src="' + paginationThumbs[index].dataset.swiperThumb + '">';
                                                    }
                                                } else {
                                                    value = (index + 1);
                                                }
                                                return '<a class="' + className + '">' + value + '</a>';
                                            };
                                        }
                                    }
                                    if (playBtn !== false) {
                                        playBtn.className = 'tf_slider_controller tf_box tf_rel';
                                        playBtn.tfOn('click', function (e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            const elm = el.swiper.parent ? el.swiper.parent : el,
                                                    sw = elm.swiper.autoplay,
                                                    thumbs = elm.swiper.thumbs && elm.swiper.thumbs.swiper ? elm.swiper.thumbs.swiper : null;
                                            if (this.classList.contains('paused')) {
                                                this.classList.remove('paused');
                                                elm.dataset.stopped = false;
                                                sw.start();
                                                if (thumbs) {
                                                    thumbs.$el[0].dataset.stopped = false;
                                                    thumbs.autoplay.start();
                                                }
                                            } else {
                                                this.classList.add('paused');
                                                elm.dataset.stopped = true;
                                                sw.stop();
                                                if (thumbs) {
                                                    thumbs.$el[0].dataset.stopped = true;
                                                    thumbs.autoplay.stop();
                                                }
                                            }

                                        })
                                        .href = '#';
                                        navWrap.appendChild(playBtn);
                                    }
                                    if (!data.nav_out) {
                                        el.parentNode.appendChild(navWrap);
                                    } else {
                                        const next = el.nextElementSibling;
                                        if (next === null || !next.classList.contains('tf_lazy')) {
                                            el.after(navWrap);
                                        } else {
                                            next.after(navWrap);
                                        }
                                    }
                                }
                                if ( ( el.dataset.touch_swipe === 'touch' && ! Themify.isTouch ) || el.dataset.touch_swipe === 'no' ) {
                                    args.allowTouchMove = false;
                                }
                                if (hasthumb) {
                                    const thumb = typeof hasthumb === 'string' ? (hasthumb === 'next' ? el.nextElementSibling : doc.tfClass(hasthumb)[0]) : hasthumb;
                                    if (thumb) {
                                        if (thumb.swiper) {
                                            args.thumbs = {
                                                swiper: thumb.swiper,
                                                multipleActiveThumbs: false
                                            };
                                            thumb.swiper.parent = el;
                                        } else {
                                            thumb.tfOn('tf_carousel_init', function (e) {
                                                if (!el.classList.contains('tf_swiper-container-initialized')) {
                                                    args.thumbs = {
                                                        swiper: this.swiper,
                                                        multipleActiveThumbs: false
                                                    };
                                                    thumb.swiper.parent = el;
                                                    el.firstElementChild.classList.remove('tf_lazy');
                                                    new TF_Swiper(el, args);
                                                    el.classList.remove('tf_swiper-process');
                                                }
                                            }, {once: true, passive: true});
                                            if (thumb.hasAttribute('data-lazy')) {
                                                Themify.lazyScroll([thumb], true);
                                            }
                                            return false;
                                        }
                                    }
                                }
                                el.firstElementChild.classList.remove('tf_lazy');
                                new TF_Swiper(el, args);
                                el.classList.remove('tf_swiper-process');
                            }
                        },-1,1000);
                    });
                });
            }
            else {
                let sw = el.swiper;
                if (sw && sw.params.autoHeight) {
                    sw.updateAutoHeight(sw.params.speed, false);
                }
            }
        }
    };

    Themify.on('tf_carousel_init', (items, options) => {
        Promise.all(
                [
                Themify.loadCss('swiper/swiper', 'tf_swiper',v),
                Themify.loadJs('swiper/swiper.min',('undefined' !== typeof TF_Swiper && 'undefined' !== typeof TF_Swiper.Utils),v)
                ]
            )
        .then(()=>{
            if (items === 'load') {
                return;
            }
            if (items.length === und) {
                items = [items];
            }
            if (typeof options !== 'object') {
                options = false;
            }
            _init(items, options);
        });
    });

})(Themify, document,undefined);
