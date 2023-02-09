/* Themify Theme Scripts - https://themify.me/ */
;
(($, Themify, win, doc, fwVars, themeVars,und)=> {
    'use strict';
    const ThemifyTheme = {
        bodyCl: doc.body.classList,
        v: fwVars.theme_v,
        headerType: themeVars.headerType,
        url: fwVars.theme_url + '/',
        init() {
            this.jsUrl=this.url+'js/modules/';
            this.darkMode();
            Themify.megaMenu(doc.tfId('main-nav'));
            this.headerRender();
            this.clickableItems();
            this.headerVideo();
            this.fixedHeader();
            this.wc();
            setTimeout(()=>{
                this.loadFilterCss();
                Themify.on('infiniteloaded', ()=>{this.loadFilterCss();});
            }, 800);
            setTimeout(()=>{this.backToTop();}, 2000);
            this.resize();
            this.doInfinite(doc.tfId('loops-wrapper'));
            setTimeout(()=>{this.commentAnimation();}, 3500);
            this.builderActive();
            if (doc.tfId('mc_embed_signup')) {
                Themify.loadCss(this.url + 'styles/modules/mail_chimp', null,this.v);
            }
            this.revealingFooter();
        },
        builderActive() {
            if (Themify.is_builder_active === true) {
                Themify.loadJs(this.jsUrl + 'builderActive', null, this.v);
            }
        },
        fixedHeader() {
            if (this.bodyCl.contains('fixed-header-enabled') && this.headerType !== 'header-bottom' && this.headerType !== 'header-leftpane' && this.headerType !== 'header-minbar' && this.headerType !== 'header-rightpane' && this.headerType !== 'header-slide-down' && this.headerType !== 'header-none' && doc.tfId('headerwrap') !== null) {
                Themify.fixedHeader();
            }
        },
        revealingFooter() {
            if (this.bodyCl.contains('revealing-footer') && doc.tfId('footerwrap') !== null) {
                Themify.loadJs(this.jsUrl + 'revealingFooter', null, this.v);
            }
        },
        doInfinite(container, wpf) {
            Themify.infinity(container, {
                scrollToNewOnLoad: themeVars.scrollToNewOnLoad,
                scrollThreshold: !('auto' !== themeVars.autoInfinite),
                history: wpf || !themeVars.infiniteURL ? false : 'replace'
            });
        },
        loadFilterCss() {
            const filters = ['blur', 'grayscale', 'sepia', 'none'];
            for (let i = filters.length - 1; i > -1; --i) {
                if (doc.querySelector('filter-' + filters[i]+',filter-hover-' + filters[i])!== null) {
                    Themify.loadCss(this.url + 'styles/modules/filters/' + filters[i],null, this.v);
                }
            }
        },
        headerVideo() {
            const header = doc.tfId('headerwrap');
            if (header) {
                const videos = Themify.selectWithParent('[data-fullwidthvideo]', header);
                if (videos.length > 0) {
                    Themify.loadJs(this.jsUrl + 'headerVideo',null,this.v).then(()=>{
                        Themify.trigger('themify_theme_header_video_init', [videos]);
                    });
                }
            }
        },
        wc() {
            if (win.woocommerce_params !== und) {
                Themify.loadJs(this.jsUrl + 'themify.shop',null,this.v).then(()=>{
                    Themify.trigger('themify_theme_shop_init', this);
                });
            }
        },
        resize() {
            if (this.headerType === 'header-menu-split') {
                Themify.on('tfsmartresize',  e=> {
                    if (e && e.w !== Themify.w) {
                        if ($('#menu-icon').is(':visible')) {
                            if ($('.header-bar').find('#site-logo').length === 0) {
                                $('#site-logo').prependTo('.header-bar');
                            }
                        } else if ($('.themify-logo-menu-item').find('#site-logo').length === 0) {
                            $('.themify-logo-menu-item').append($('.header-bar').find('#site-logo'));
                        }
                    }
                });
            }
        },
        clickableItems() {
            const items = doc.tfClass('toggle-sticky-sidebar');
            for (let i = items.length - 1; i > -1; --i) {
                items[i].tfOn('click', function () {
                    const sidebar = doc.tfId('sidebar'),
                        cl=this.classList;
                    if (cl.contains('open-toggle-sticky-sidebar')) {
                        cl.remove('open-toggle-sticky-sidebar');
                        cl.add('close-toggle-sticky-sidebar');
                        sidebar.classList.add('open-mobile-sticky-sidebar','tf_scrollbar');
                    } else {
                        cl.remove('close-toggle-sticky-sidebar');
                        cl.add('open-toggle-sticky-sidebar');
                        sidebar.classList.remove('open-mobile-sticky-sidebar','tf_scrollbar');
                    }
                }, {passive: true});
            }
            setTimeout( () =>{
                Themify.body.on('click', '.post-content', function (e) {
                    if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                        const el = this.closest('.loops-wrapper');
                        if (el !== null) {
                            const cl = el.classList;
                            if ((cl.contains('grid6') || cl.contains('grid5') || cl.contains('grid4') || cl.contains('grid3') || cl.contains('grid2')) && (cl.contains('polaroid') || cl.contains('overlay') || cl.contains('flip'))) {
                                const link = this.closest('.post').querySelector('a[data-post-permalink]');
                                if (link && link.href) {
                                    link.click();
                                }
                            }
                        }
                    }
                });
            }, 1500);
        },
        headerRender(){
            Themify.sideMenu(doc.tfId('menu-icon'), {
                close: '#menu-icon-close',
                side: this.headerType === 'header-minbar-left' || this.headerType === 'header-left-pane' || this.headerType === 'header-slide-left' ? 'left' : 'right'
            });
            const header_top_wdts = doc.tfClass('top-bar-widgets')[0];
            if (und !== fwVars.m_m_expand || header_top_wdts) {
                Themify.on('sidemenushow',  panel_id=> {
                    if ('#mobile-menu' === panel_id) {
                        // Expand Mobile Menus
                        if (und !== fwVars.m_m_expand) {
                            const items = doc.querySelectorAll('#main-nav>li.has-sub-menu');
                            for (let i = items.length - 1; i > -1; i--) {
                                items[i].className += ' toggle-on';
                            }
                        }
                        // Clone Header Top widgets
                        if (header_top_wdts) {
                            const mobile_menu = doc.tfId('main-nav-wrap');
                            mobile_menu.parentNode.insertBefore(header_top_wdts.cloneNode(true), mobile_menu.nextSibling);
                        }
                    }
                }, true);
            }
        },
        backToTop() {
            if (this.headerType === 'header-bottom') {
                const footer_tab = doc.tfClass('footer-tab')[0];
                if (footer_tab !== und) {
                    footer_tab.tfOn('click', function (e) {
                        e.preventDefault();
                        const cl = this.classList,
                            footer=doc.tfId('footerwrap'),
                            closed=cl.contains('tf_close');
                            cl.toggle('ti-angle-down',closed);
                            cl.toggle('tf_close',!closed);
                            if(footer){
                                footer.classList.toggle('expanded',!closed);
                            }
                    });
                }
            }
            const back_top = doc.tfClass('back-top')[0];
            if (back_top !== und) {
                if (back_top.classList.contains('back-top-float')) {
                    const events = ['scroll'],
                            scroll = function () {
                                back_top.classList.toggle('back-top-hide',this.scrollY < 10);
                            };
                    if (Themify.isTouch) {
                        events.push('touchstart');
                        events.push('touchmove');
                    }
                    win.tfOn(events, scroll, {passive: true});
                }
                back_top.tfOn('click', e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    Themify.scrollTo();
                });
            }
        },
        commentAnimation() {
            const form=doc.tfId('commentform');
            if (form) {
                $(form).on('focus', 'input, textarea', function () {
                    $(this).one('blur', function () {
                        if (this.value === '') {
                            $(this).removeClass('filled').closest('#commentform p').removeClass('focused');
                        } else {
                            $(this).addClass('filled');
                        }
                    }).closest('#commentform p').addClass('focused');
                });
            }
        },
		toggleDarkMode( status = true ) {
			let el = doc.querySelector( 'link[href*="dark-mode"]' );
			if ( status ) {
				if ( el ) {
					el.media='all';
				} else {
					Themify.loadCss(this.url + 'styles/modules/dark-mode', 'dark-mode',this.v,doc.body.lastChild);
				}
				this.bodyCl.add( 'tf_darkmode' );
			} else {
				if ( el ) {
					/* disable the stylesheet instead of removing it, might need to re-enable it later */
					el.media='none';
				}
				this.bodyCl.remove( 'tf_darkmode' );
			}
		},
		darkMode(){
			if ( themeVars.darkmode ) {
				if ( themeVars.darkmode!=1 && themeVars.darkmode.start ) {
					/* Scheduled dark mode */
					const current_date = new Date(),
						start_date = new Date(),
						end_date = new Date(),
						start = themeVars.darkmode.start.split(':'),
						end = themeVars.darkmode.end.split(':');
					start_date.setHours(start[0],start[1],0);
					if(parseInt(end[0])<parseInt(start[0])){
						end_date.setDate(end_date.getDate() + 1);
					}
					end_date.setHours(end[0],end[1],0);
					if ( current_date >= start_date && current_date < end_date ) {
						this.toggleDarkMode();
					}
				} else {
					/* by user preference */
					for ( let toggles = doc.tfClass( 'tf_darkmode_toggle' ),i = toggles.length - 1; i > -1; --i ) {
						toggles[ i ].tfOn( 'click', e => {
							e.preventDefault();
							const enabled = ! doc.body.classList.contains( 'tf_darkmode' );
							this.toggleDarkMode( enabled );
							localStorage.setItem( 'tf_darkmode', enabled ? 1 : 0 );
						} );
					}
					if ( parseInt( localStorage.getItem( 'tf_darkmode' ) ) ) {
						this.toggleDarkMode();
					}
				}
			}
		}
    };
    ThemifyTheme.init();
})(jQuery, Themify, window, document, themify_vars, themifyScript,undefined);

