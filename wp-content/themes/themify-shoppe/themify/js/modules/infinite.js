/**
 * infinite module
 */
;
(($, Themify, win, doc,und)=>{
    'use strict';
    let isSafari = null,
        historyObserver = null;
    const Prefetched = new Set(),
            _init = options=> {
                return new IntersectionObserver((entries, _self)=> {
                    for (let i = entries.length - 1; i > -1; --i) {
                        if (entries[i].isIntersecting === true) {
                            if (options.button === null) {
                                _self.disconnect();
                            } else {
                                _Load(options);
                            }
                        }
                    }
                }, {
                    threshold:.1
                });
            },
            _addHistoryPosition = (item, path)=> {
                if (historyObserver === null) {
                    historyObserver = new IntersectionObserver( (entries, _self)=> {
                        for (let i = entries.length - 1; i > -1; --i) {
                            if (entries[i].isIntersecting === true) {
                                win.history.replaceState(null, null, entries[i].target.dataset.tfHistory);
                            }
                        }
                    }, {
                        rootMargin:'100% 0px -100% 0px'
                    });
                }
                item.dataset.tfHistory=_removeQueryString(path);
                historyObserver.observe(item);
            },
            _removeQueryString = path=> {
                return Themify.updateQueryString('tf-scroll',null,path);
            },
            _addQueryString = path=> {
                return Themify.updateQueryString('tf-scroll', 1, path);
            },
            _beforeLoad = (element, _doc, ajax_filter)=> {
                Themify.lazyScroll(Themify.selectWithParent('[data-lazy]',element), true);
                if(!ajax_filter){
                    if (win.Isotope !== und) {
                        const isotop = win.Isotope.data(element);
                        if (isotop) {
                            const postFilter = element.previousElementSibling;
                            if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                const active = postFilter.querySelector('.cat-item.active:not(.cat-item-all)');
                                if (active !==null) {
                                    $(active).trigger('click.tf_isotop_filter');
                                }
                            }
                        }
                    }
                }
                Themify.triggerEvent(element,'infinitebeforeloaded',{d:_doc})
                        .trigger('infinitebeforeloaded', [element, _doc]);
            },
            _afterLoad = (items, container, opt) =>{
                const len = items.length,
                        isotop = win.Isotope !== und ? win.Isotope.data(container) : null;
                if (isSafari === null) {
                    isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
                }
                items[0].className += ' tf_firstitem';
                var k = 0;
                for (let i = 0; i < len; ++i) {
                    items[i].style.opacity = 0;
                    Themify.imagesLoad(items[i]).then(el=> {
                        // Fix Srcset in safari browser
                        if (isSafari) {
                            const imgSrcset = el.querySelector('img[srcset]');
                            if (null !== imgSrcset) {
                                imgSrcset.outerHTML = imgSrcset.outerHTML;
                            }
                        }
                        ++k;
                        if (isotop) {
                            isotop.appended(el);
                        }
                        el.style.opacity = '';
                        if (k === len) {
                            if (isotop || container.classList.contains('auto_tiles')) {
                                if(!opt.ajax_loading){
                                    const postFilter = container.previousElementSibling;
                                    if (postFilter !== null && postFilter.classList.contains('post-filter')) {
                                        // If new elements with new categories were added enable them in filter bar
                                        Themify.trigger('themify_isotop_filter', [postFilter]);
                                    }
                                }
                                if (container.classList.contains('auto_tiles')) {
                                    Themify.autoTiles(container);
                                }
                            }
                            for (let i = 0; i < len; ++i) {
                                    Themify.lazyScroll(Themify.convert(Themify.selectWithParent('[data-lazy]',items[i])).reverse(),true);
                            }
                            Themify.triggerEvent(container,'infiniteloaded',{items:items})
                                    .trigger('infiniteloaded', [container, items]);
                            if ('scroll' === opt.scrollToNewOnLoad) {
                                let first = container.tfClass('tf_firstitem');
                                first = first[first.length - 1];
                                let to = $(first).offset().top;
                                const speed = to >= 800 ? (800 + Math.abs((to / 1000) * 100)) : 800,
                                        header = doc.tfId('headerwrap');
                                if (header !== null && (header.classList.contains('fixed-header') || doc.body.classList.contains('fixed-header'))) {
                                    to -= $(header).outerHeight(true);
                                }
                                if (opt.scrollThreshold === false || (to - doc.docElement.scrollTop) > opt.scrollThreshold) {
                                    Themify.scrollTo(to, speed);
                                }
                            }
                            Themify.fonts();
                            Themify.wpEmbed(doc.tfClass('wp-embedded-content'));
                            Themify.largeImages();
                        }
                    });
                }
            },
            _Load =opt=> {
                if (opt.isWorking === true) {
                    return;
                }
                opt.isWorking = true;
                opt.status.classList.add('tf_scroll_request');
                let url,
                    method='GET',
                    ajaxData;
                if(opt.filter){
                    const ajax_sort = opt.filter.hasAttribute('data-sort'),
                        active = opt.filter.querySelector('.cat-item.active');
                    if(active){
                        opt.ajax_loading=active;
                        ajaxData = {
                            action:'themify_ajax_load_more',
                            module:opt.filter.dataset.el,
                            id:opt.filter.dataset.id,
                            page:active.dataset.p
                        };
                        if(!active.classList.contains('cat-item-all')){
                            const tax=active.className.replace(/(current-cat)|(cat-item)|(-)|(active)/g, '').replace(' ', '');
                            ajaxData.tax=tax.trim();;
                        }
                        if(ajax_sort){
                            const order = opt.filter.querySelector('.tf_ajax_sort_order.active'),
                                orderby = opt.filter.querySelector('.tf_ajax_sort_order_by .active');
                            if(order){
                                ajaxData.order=order.dataset.type;
                            }
                            if(orderby){
                                ajaxData.orderby=orderby.dataset.orderBy;
                            }
                        }
                        method='POST';
                        url=themify_vars.ajax_url;
                    }
                }
                if(!opt.ajax_loading){
                    url=_addQueryString(opt.button.href);
                }
                Themify.fetch(ajaxData,'html',{method:method},url).then(d=> {
                            const container = d.querySelector(opt.id),
                                    currentPath = _removeQueryString(opt.button.href),
                                    element = opt.container;
									
                            let btn = null;
                            if (container !== null) {
                                _beforeLoad(element, d,!!opt.ajax_loading);
                                const fr = doc.createDocumentFragment(),
                                        childs = Themify.convert(container.children);
                                btn = container.tfClass('load-more-button')[0] || container.nextElementSibling;
                                if (btn) {
                                    if (!btn.classList.contains('load-more-button')) {
                                        btn = btn.children[0];
                                    }
                                    if (!btn || !btn.classList.contains('load-more-button')) {
                                        btn = null;
                                    }
                                }
                                if(btn && btn.tagName!=='A'){
                                        btn = btn.children[0];
                                        if (!btn || btn.tagName!=='A') {
                                                btn = null;
                                        }
                                }
                                if (childs[0] !== und) {
                                    for (let j = 0, len = childs.length; j < len; ++j) {
                                        fr.appendChild(childs[j]);
                                    }
                                    element.appendChild(fr);
                                    if (opt.history) {
                                        _addHistoryPosition(childs[0], currentPath);
                                    }
                                    _afterLoad(childs, element, opt);
                                } else {
                                    btn = null;
                                }
                                if(opt.ajax_loading && null===btn){
                                    opt.ajax_loading.dataset.done=true;
                                    opt.filter.parentNode.classList.add('tb_hide_loadmore');
                                }
                            }
                            if(!opt.ajax_loading){
                                if (btn === null) {
                                    opt.button.remove();
                                    opt.button = null;
                                } else {
                                    const nextHref = _addQueryString(btn.href);
                                    if (opt.prefetchBtn !== und && !Prefetched.has(nextHref)) {
                                        Prefetched.add(nextHref);
                                        opt.prefetchBtn.setAttribute('href', nextHref);
                                    }
                                    opt.button.href = nextHref;
                                    win.tfOn('scroll',e =>{
                                        opt.isWorking = null;
                                    }, {passive: true, once: true});
                                }
                                /*Google Analytics*/
                                if (win.ga !== und) {
                                    const link = doc.createElement('a');
                                    link.href = currentPath;
                                    ga('set', 'page', link.pathname);
                                    ga('send', 'pageview');
                                }
                                if (opt.history) {
                                    win.history.replaceState(null, null, currentPath);
                                }
                            }else{
                                opt.ajax_loading.dataset.p = parseInt(opt.ajax_loading.dataset.p)+1;
                                opt.isWorking = null;
                            }
                            opt.status.classList.remove('tf_scroll_request');
                            return container;

                        }).catch(err=> {
                    console.warn('InfiniteScroll error.', err);
                });
            };
    Themify.loadCss(Themify.url + '/css/modules/infinite','tf_infinite');
    Themify.on('tf_infinite_init', (items, opt)=>{
        const containers=items.length!==und?items:[items];
        for(let i=containers.length-1;i>-1;--i){
            let el=containers[i],
                btn = el.tfClass('load-more-button')[0],
                loaderWrap = doc.createElement('div');
            if(!btn){
                btn = el.nextElementSibling;
            }
            if (btn) {
                let btn_wrap = btn;
                if (!btn.classList.contains('load-more-button')) {
                    btn = btn.children[0];
                    if (!btn || !btn.classList.contains('load-more-button')) {
                        continue;
                    }
                }
                if(btn.tagName!=='A'){
                    btn = btn.children[0];
                    if (!btn || btn.tagName!=='A') {
                        continue;
                    }
                }
                if (!opt.id) {
                    opt.id = el.id;
                    opt.id = !opt.id?('.'+el.className.split(' ').join('.')):('#' + opt.id);
                }
                loaderWrap.className = 'tf_load_status tf_loader tf_clear tf_hide';
                el.after(loaderWrap);
                opt.status = loaderWrap;
                opt.button = btn;
                opt.container = el;
                if(el.classList.contains('tb_ajax_pagination')){
                    const filter = el.previousElementSibling;
                    if(filter && filter.classList.contains('post-filter')){
                        opt.filter = filter;
                    }
                }
                if (opt.scrollThreshold !== false) {
                    win.tfOn('scroll', ()=>{
                        const prefetch = doc.createElement('link'),
                                nextHref = _addQueryString(opt.button.getAttribute('href'));
                        prefetch.setAttribute('as', 'document');
                        prefetch.rel='prefetch';
                        prefetch.href=nextHref;
                        opt.button.after(prefetch);
                        opt.prefetchBtn = prefetch;
                        Prefetched.add(nextHref);
                        _addHistoryPosition(opt.container.children[0], win.location.href);
                        _init(opt).observe(btn_wrap);
                    }, {passive: true, once: true});
                } 
                else {
                    _addHistoryPosition(el.children[0], win.location.href);
                    btn.tfOn('click', e=>{
                        e.preventDefault();
                        delete opt.ajax_loading;
                        _Load(opt);
                    })
                    .style.display = 'inline-block';
                }
            }
        }
    });

})(jQuery, Themify, window, document,undefined);