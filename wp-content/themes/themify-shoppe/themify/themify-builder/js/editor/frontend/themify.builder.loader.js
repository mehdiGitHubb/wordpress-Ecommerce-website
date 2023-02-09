/*! Themify Builder - Asynchronous Script and Styles Loader */
((Themify, win, doc) => {
    'use strict';
    let $;
    const wpEditor = () => {
        const remove_tinymce = () => {
            if (win.tinymce && tinyMCE) {
                const editor=tinyMCEPreInit.mceInit.tb_lb_hidden_editor;
                editor.schema = 'html5';
                editor.element_format = 'html';
                editor.wp_autoresize_on = editor.ie7_compat = false;
                editor.plugins = 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wpdialogs,wptextpattern,wpview,wplink';
                editor.indent = 'simple';
                editor.root_name = 'div';
                editor.relative_urls = true;
                tinyMCE.execCommand('mceRemoveEditor', true, 'tb_lb_hidden_editor');
                const r=doc.querySelectorAll('#wp-tb_lb_hidden_editor-editor-container,#wp-tb_lb_hidden_editor-editor-tools');
                for(let i=r.length-1;i>-1;--i){
                    r[i].remove();
                }
                if(!wp.editor.getDefaultSettings){
                    wp.editor.getDefaultSettings=()=>{
                        return {
                            tinymce:tinyMCEPreInit.mceInit.tb_lb_hidden_editor,
                            quicktags: {
                                buttons: 'strong,em,link,ul,ol,li,code'
                            }
                        };
                    };
                }
            }
        };
        Themify.fetch({action:'tb_load_editor'}, 'text').then(res => {
            const resp = doc.createElement('div'),
                    loaded = {},
                    needToLoad = {},
                    fr = doc.createDocumentFragment(),
                    scriptsFr = doc.createDocumentFragment(),
                    body = doc.body,
                    jsLoadCallback = function () {
                        loaded[this.src] = true;
                        this.tfOff('load error', jsLoadCallback, {once: true, passive: true});
                        for (let i in needToLoad) {
                            if (loaded[i] !== true) {
                                return false;
                            }
                        }
                        const fr = doc.createDocumentFragment();
                        for (let i = 0, len = final.length; i < len; ++i) {
                            fr.appendChild(final[i]);
                        }
                        body.appendChild(fr);
                        remove_tinymce();
                        if (Themify.editorLoaded!==true && $.ui && $.fn.mouse && $.fn.sortable) {
                            Themify.editorLoaded=true;
                            Themify.trigger('tb_load_iframe');
                        }
                        
                    };
            resp.innerHTML = res;
            const items = resp.querySelector('#tb_tinymce_wrap').children,
                    final = [];
            for (let i = 0, len = items.length; i < len; ++i) {

                if (items[0].tagName !== 'SCRIPT' || (items[0].type && items[0].type !== 'text/javascript')) {
                    fr.appendChild(items[0]);
                } else {
                    let s = doc.createElement('script');
                    for (let attr = items[0].attributes, j = attr.length - 1; j > -1; --j) {
                        s.setAttribute(attr[j].name, attr[j].value);
                    }
                    let src = items[0].src;
                    if (!src) {
                        let id = items[0].id;
                        if (!id || id.indexOf('themify-main-script') === -1) {
                            let html = items[0].innerHTML;
                            s.innerHTML = html;
                            if (html.indexOf('tinyMCEPreInit.') === -1 && html.indexOf('.addI18n') === -1 && html.indexOf('.i18n') === -1 && html.indexOf('wp.editor') === -1) {
                                fr.appendChild(s);
                            } else {
                                final.push(s);
                            }
                        }
                    } else if (needToLoad[src] === undefined && src.indexOf('themify/js/main') === -1 && doc.querySelector('script[src="' + src + '"]') === null) {
                        s.async = false;
                        needToLoad[src] = true;
                        s.tfOn('load error', jsLoadCallback, {once: true, passive: true});
                        fr.appendChild(s);
                    }
                    items[0].remove();
                }
            }
            try {
                body.appendChild(fr);
            } catch (e) {
                console.log(e);
            } 
            
        });
    },
    windowLoad = () => {
        let pageId,
            responsiveSrc = win.location.href.indexOf('?') > 0 ? '&' : '?';
        $ = jQuery;
        responsiveSrc = win.location.href.replace(win.location.hash, '').replace('#', '') + responsiveSrc + 'tb-preview=1&ver=' + Themify.v;
        if (!win.wp || !wp.customize) {
            let builder = doc.tfClass('themify_builder_content'),
                    toggle = doc.tfClass('toggle_tb_builder')[0],
                    found = false;
                pageId = toggle ? toggle.tfClass('tb_front_icon')[0].dataset.id: null;
                for (let i = builder.length - 1; i > -1; --i) {
                    let bid = builder[i].dataset.postid,
                            a = doc.createElement('a'),
                            span = doc.createElement('span'),
                            span2 = doc.createElement('span'),
                            edit_label = builder[i].dataset.label || builder[i].parentNode.dataset.label;
                    if(edit_label!=='disabled'){
                        a.href = 'javascript:;';
                        a.className = 'tb_turn_on js-turn-on-builder';
                        span.className = 'dashicons dashicons-edit';
                        span.dataset.id= bid;
                        span2.innerHTML = edit_label || tbLoaderVars.turnOnBuilder;
                        a.append(span,span2);
						let edit_btn=doc.tfClass('tf_edit_post_'+bid)[0],
							next=edit_btn?edit_btn.nextElementSibling:null; 
						if(edit_btn && (!next || !next.classList.contains('tb_turn_on'))){
							edit_btn.before(a);	
						}
						else{
							builder[i].insertAdjacentElement(edit_label ? 'beforeBegin' : 'afterEnd', a);
						}
                        if (bid === pageId) {
                            found = true;
                        }
                    }
                }
                if (found === false) {
                    pageId = null;
                }
                if (toggle) {
					if ( toggle.tfClass( 'tbp_admin_bar_templates' )[0] ) {
						/* Builder Pro templates are present */
						found = true;
					}
					toggle.classList.toggle('tb_disabled_turn_on',!found);
                }
        }
        
		if(doc.body.dataset.tbAttached){
			return;
		}
        const run=e=>{
            const clicked=e.target,
                target=clicked && (clicked.getAttribute('href')==='#' || !clicked.href)?clicked.closest('.js-turn-on-builder,.toggle_tb_builder'):null;
            if(target && !target.closest('.tb_disabled_turn_on') && (clicked.closest('.js-turn-on-builder') || !target.classList.contains('toggle_tb_builder')|| !clicked.href || clicked.parentNode.classList.contains('toggle_tb_builder'))){
                e.preventDefault();
                e.stopPropagation();
                if(typeof ThemifyStyles!=='undefined' && ThemifyStyles.isWorking===true){
                    return;
                }
                const post_id = !target.classList.contains('js-turn-on-builder') ? pageId : target.childNodes[0].dataset.id;
                if (!post_id) {
                    return;
                }
                doc.body.tfOff(e.type,run);
                Themify.lazyDisable = Themify.lazyScrolling = true;
                if (Themify.observer !== null) {
                    Themify.observer.disconnect();
                }
                Themify.events.clear();
                Themify.cssLazy.clear();
                //remove unused the css/js to make faster switch mode/window resize
                let cssItems = {},
                    cssLoaded=[],
                    scrollPos =win.scrollY,
                    css = Themify.convert(doc.tfTag('link')).concat(Themify.convert(doc.tfTag('style'))),
                    tfBaseCss;
                const   $children = Themify.body.children(),
                        builderLoader=doc.createElement('div'),
                        fixed = doc.createElement('div'),
                        workspace = doc.createElement('div'),
                        bar = doc.createElement('div'),
                        leftBar = doc.createElement('div'),
                        rightBar = doc.createElement('div'),
                        iframe = doc.createElement('iframe'),
                        body=doc.body,
                        cookieData = new Date();


                if (tbLoaderVars.styles !== null) {
                    for (let i  in tbLoaderVars.styles) {
                        if (tbLoaderVars.styles[i] !== '') {
                            let cssId=i.indexOf('base.min.css')!==-1?'tf_base-css':(i.indexOf('lightbox.css')!==-1?'themify-builder-lightbox-css':null),
                                pr=Themify.loadCss(i,cssId, tbLoaderVars.styles[i]),
                                link=doc.querySelector('link#'+cssId);
                            if(cssId){
                                tfBaseCss=pr;
                            }
                            if(!link){
                                cssItems[i + '?ver=' + tbLoaderVars.styles[i]] = 1;
                            }
                            else{
                                cssItems[link.href] = 1;
                            }
                            cssLoaded.push(pr);
                        }
                    }
                }
                wpEditor();
                workspace.className = 'tb_workspace_container tf_w tf_h tf_hidden tf_opacity';
                bar.className = 'tb_vertical_bars tf_rel tf_h tf_w';
                leftBar.id = 'tb_left_bar';
                rightBar.id = 'tb_right_bar';
                leftBar.className = rightBar.className = 'tb_middle_bar tf_h tf_rel tf_hide';
                iframe.className = 'tb_iframe tf_w tf_h';
                iframe.id = iframe.name = 'tb_iframe';
                iframe.scrolling = 'yes';
                iframe.src = responsiveSrc + '&tb-id=' + post_id;
                Themify.off('builder_load_module_partial tf_music_ajax_ready');
                $(doc).off('ajaxComplete');

                builderLoader.className = 'tb_loader tf_loader tf_abs_c tf_box';
                fixed.id = 'tb_fixed_bottom_scroll';
                fixed.className = 'tb_fixed_scroll';

                body.prepend(fixed);

                bar.append(leftBar, iframe, rightBar);
                workspace.appendChild(bar);

                iframe.tfOn('load', function () {
                    let contentWindow = this.contentWindow,
                    b;
                    Themify.on('themify_builder_ready', () => {
                        Promise.all(cssLoaded).then(()=>{
                            builderLoader.classList.remove('tf_hide');
                            builderLoader.tfOn('transitionend', function () {
                                this.classList.remove('tf_opacity');
                                this.classList.add('tf_hide');
                            }, {passive: true, once: true})
                            .classList.add('tf_opacity');
                            const bodyCl = body.classList,
                                    isArchive = bodyCl.contains('archive');
                            let cl = 'themify_builder_active builder-breakpoint-desktop';
                            if (isArchive) {
                                // "archive" classname signifies whether current page being edited is a WP archive page
                                cl += ' archive';
                            }
                            if ('1' === tbLoaderVars.isGlobalStylePost) {
                                cl += ' gs_post';
                            }
                            if (bodyCl.contains('tb_preview_only')) {
                                cl += ' tb_preview_only';
                            }
                            if (bodyCl.contains('tb_panel_docked')) {
                                cl += ' tb_panel_docked';
                                cl += bodyCl.contains('tb_panel_left_dock') ? ' tb_panel_left_dock' : ' tb_panel_right_dock';
                            }
                            body.className = cl;
                            body.removeAttribute('style');
                            workspace.classList.remove('tf_hidden','tf_opacity');
                            const activeBuilderPost = contentWindow.tb_app.Builder.get().el.offsetTop;
                            if (activeBuilderPost > scrollPos) {
                                scrollPos = activeBuilderPost;
                            }
                            contentWindow.scrollTo(0, scrollPos);
                            Themify.is_builder_active = true;
                            setTimeout(() => {
                                $children.hide();
                                for (let i = css.length - 1; i > -1; --i) {
                                    if (css[i] && css[i].parentNode) {
                                        let href = css[i].href,
                                                id = css[i].id;
                                        if (href) {
                                            if (!cssItems[href] && href.indexOf('themify.builder.loader')===-1 && href.indexOf('wp-includes') === -1 && href.indexOf('admin-bar') === -1 && href.indexOf('themify-builder/css/editor/modules')===-1) {
                                                css[i].remove();
                                            }
                                        } else if (id !== 'tf_fonts_style' && id !== 'tf_lazy_common') {
                                            css[i].remove();
                                        }
                                    }
                                }
                                css = cssItems = cssLoaded=tbLoaderVars = null;
                                $('.themify_builder_content,#wpadminbar,header').remove();
                                $children.filter('ul,a,video,audio').filter(':not(:has(link))').remove();
                                const events = ['scroll', 'tfsmartresize', 'debouncedresize', 'throttledresize', 'resize', 'mouseenter', 'keydown', 'keyup', 'mousedown', 'mouseup', 'mouseleave', 'mousemove','assignVideo'],
                                        $win = $(win),
                                        $doc = $(doc),
                                        $body=$(body);
                                for (let i = events.length - 1; i > -1; --i) {
                                    $win.off(events[i]);
                                    $doc.off(events[i]);
                                    $body.off(events[i]);
                                }
                                doc.documentElement.removeAttribute('style');
                                doc.documentElement.removeAttribute('class');
                                let contentDoc = contentWindow.document;
                                setTimeout(() => {
                                    setTimeout(() => {
                                        const globals = ['ThemifyBuilderModuleJs', 'c', '_wpemojiSettings', 'twemoji', 'themifyScript', 'tbLocalScript', 'tbScrollHighlight', 'google', 'ThemifyGallery', 'Animation', '$f', 'Froogaloop', 'SliderProSlide', 'SliderProUtils', 'ThemifySlider', 'FixedHeader', 'LayoutAndFilter', 'WOW', 'Waypoint', '$slidernav', 'google', 'Microsoft', 'Rellax', 'module$contents$MapsEvent_MapsEvent', 'module$contents$mapsapi$overlay$OverlayView_OverlayView', 'wc_add_to_cart_params', 'woocommerce_params', 'wc_cart_fragments_params', 'wc_single_product_params', 'tf_mobile_menu_trigger_point', 'themifyMobileMenuTrigger'];

                                        for (let i = globals.length - 1; i > -1; --i) {
                                            if (win[globals[i]]) {
                                                win[globals[i]] = null;
                                            }
                                        }
                                        win.wp.emoji = null;
                                        win.ajaxurl = themify_vars.ajax_url; // required for Ajax requests sent by WP

                                        for (let lazy = contentDoc.querySelectorAll('[data-lazy]'), i = lazy.length - 1; i > -1; --i) {
                                            lazy[i].removeAttribute('data-lazy');
                                        }
                                        contentWindow = b = contentDoc =Themify.editorLoaded= null;
                                    }, 5000);
                                },1000);
                            }, 800);
                        });
                    },true);
                    Themify.on('tb_load_iframe', () => {
                        const __callback = () => {
                            contentWindow.themifyBuilder.post_ID = post_id;
							const currentBuilder=target.parentNode.tfClass('themify_builder_content-'+ post_id)[0];
							if(currentBuilder){
								const builders=doc.tfClass('themify_builder_content-'+ post_id);
								if(builders.length>1){
									for(let i=builders.length-1;i>-1;--i){
										if(builders[i]===currentBuilder){
											contentWindow.themifyBuilder.builderIndex=i;
											break;
										}
									}
								}
							}
                            b = contentWindow.Themify;
                            b.trigger('builderiframeloaded', this);
                        };
                        // Cloudflare compatibility fix
                        if ('__rocketLoaderLoadProgressSimulator' in contentWindow) {
                            const rocketCheck = setInterval(() => {
                                if (contentWindow.__rocketLoaderLoadProgressSimulator.simulatedReadyState === 'complete') {
                                    clearInterval(rocketCheck);
                                    __callback();
                                }
                            }, 10);
                        } 
                        else {
                            __callback();
                        }
                    }, true,Themify.editorLoaded);

                }, {once: true, passive: true});

                body.appendChild(builderLoader);
                tfBaseCss.then(()=>{
                    cookieData.setTime(cookieData.getTime() + 3000);//3s
                    doc.cookie ='tb_active='+post_id+';expires='+cookieData.toUTCString() + ';SameSite=strict;path='+responsiveSrc;//some servers cut query string 
                    body.appendChild(workspace);
                    setTimeout(()=>{
                        doc.cookie = 'tb_active=;path='+responsiveSrc+';Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                    },600);
                });
            }
        };
		doc.body.dataset.tbAttached=1;
		doc.body.tfOn(Themify.click,run);
		if (!doc.body.classList.contains('tb_restriction') && win.location.href.indexOf('tb-id') === -1) {
			if (win.location.hash === '#builder_active') {
				const first=doc.querySelector('.toggle_tb_builder > a');
				if(first){
					Themify.triggerEvent(first,Themify.click);
				}
				win.location.hash = '';
			}
		}
    };
    Themify.on('tf_init', windowLoad, true,win.loaded)
	.on('tf_music_ajax_ready',windowLoad);
})(Themify, window, document);
