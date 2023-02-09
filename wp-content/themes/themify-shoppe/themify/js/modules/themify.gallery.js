// Themify Lightbox//
(($, Themify, doc, win)=>{

    'use strict';
    const ThemifyGallery = {
        origHash: null,
        config: {},
        init(config) {
            this.config = config;
            this.initLightbox();
            this.openAnchor();
        },
        lightboxSelector(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if ($('.mfp-wrap.mfp-gallery').length)
                return;
            const $self= $(this);

			/* make lightbox work if the parent element of the link has "themify_lightbox" class */
            let link = $self.find( '> a' );
            if ( link.length === 0 ) {
                link = $self.attr( 'href' );
            } else {
				link = link.attr( 'href' );
			}

            const self=ThemifyGallery, 
                type = self.getFileType(link),
                isVideo = self.isVideo(link),
                provider = Themify.parseVideo(link),
                patterns = {},
                $swiper_slider=$self.parents('.tf_swiper-container');
            let $groupItems;
            if($swiper_slider.length>0){
                $groupItems = type === 'inline' || type === 'iframe' ? [] : ($self.data('rel') ? $('a[data-rel="' + $self.data('rel') + '"]') : $swiper_slider.find('.tf_swiper-slide:not(.tf_swiper-slide-duplicate) .themify_lightbox img').parents('.themify_lightbox'));
            }else{
                $groupItems = type === 'inline' || type === 'iframe' ? [] : ($self.data('rel') ? $('a[data-rel="' + $self.data('rel') + '"]') : $self.closest('.themify_builder_content>.module_row, .loops-wrapper, .gallery-wrapper').find('.themify_lightbox img').parents('.themify_lightbox'));
            }
            $.uniqueSort($groupItems);
            let targetItems,
                index = $swiper_slider.length>0 && $swiper_slider[0].swiper?$swiper_slider[0].swiper.realIndex:($groupItems.length > 1 ? $groupItems.index(this) : 0),
                iframeWidth = isVideo ? '100%' : (self.getParam('width', link)) ? self.getParam('width', link) : '94%',
                iframeHeight = isVideo ? '100%' : (self.getParam('height', link)) ? self.getParam('height', link) : '100%';
                
            if (iframeWidth.indexOf('%') === -1)
                iframeWidth += 'px';
            if (iframeHeight.indexOf('%') === -1)
                iframeHeight += 'px';

            if (isVideo === true && (provider.type === 'youtube' || provider.type === 'vimeo')) {
                const params = self.getCustomParams(link);
                if (params) {
                    if (provider.type === 'youtube') {
                        // YouTube URL pattern
                        patterns.youtube = {
                            id: 'v=',
                            index: 'youtube.com/',
                            src: '//www.youtube.com/embed/%id%' + params
                        };

                        // YouTube sanitize the URL properly
                        link = self.getYoutubePath(link);
                    } else {
                        // Vimeo URL pattern
                        patterns.vimeo = {
                            id: '/',
                            index: 'vimeo.com/',
                            src: '//player.vimeo.com/video/%id%' + params
                        };
                        link = link.split('?')[0];
                    }
                }
            }
            if ($groupItems.length > 1 && index !== -1) {
                targetItems = [];
                $groupItems.each(function (i, el) {
                    targetItems.push({
                        src: self.getiFrameLink($(el).prop('href')),
                        title: self.getTitle( el ),
                        type: self.getFileType($(el).prop('href'))
                    });
                });
                // remove duplicate items (same "src" attr) from the lightbox group
                targetItems = targetItems.reduce(function (memo, e1) {
                    const matches = memo.filter(function (e2) {
                        return e1.src === e2.src;
                    });
                    if (matches.length === 0) {
                        // reset the index to current item
                        if (e1.src === link) {
                            index = memo.length;
                        }
                        memo.push(e1);
                    }
                    return memo;
                }, []);

            } else {
                index = 0; // ensure index is set to 0 so the proper popup shows
                targetItems = {
                    src: self.getiFrameLink(link),
                    title: self.getTitle( this )
                };
            }

            const iOSScrolling = Themify.isTouch && !win.MSStream && /iPad|iPhone|iPod/.test(navigator.userAgent) ? 'scrolling="no" ' : '',
                    args = {
                        items: targetItems,
                        type: type,
                        image: {
                            markup: self.getImageMarkup(this)
                        },
                        iframe: {
                            markup: '<div class="mfp-iframe-scaler" style="max-width: ' + iframeWidth + ' !important; height: ' + iframeHeight + ';">' +
                                    '<div role="button" tabindex="0" class="tf_close mfp-close"></div>' +
                                    '<div class="mfp-iframe-wrapper">' +
                                    '<iframe class="mfp-iframe" ' + 'noresize="noresize" frameborder="0" allowfullscreen></iframe>' +
                                    '</div>' +
                                    self.getSocialMarkup() +
                                    '</div>',
                            patterns: patterns
                        },
                        callbacks: {
                            beforeOpen() {
                                doc.body.classList.add('themify_mp_opened');
                            },
                            open() {
                                self.updateHash('open', this);
                                self.openSharing(this);
                                let zoomConfig = $self.data('zoom-config'),
                                        cssRules = {};
                                if (!zoomConfig) {
                                    return;
                                }
                                zoomConfig = zoomConfig.split('|');

                                if (zoomConfig[0]) {
                                    cssRules.width = zoomConfig[0];
                                }

                                if (typeof zoomConfig[1] !== 'undefined') {
                                    cssRules.height = zoomConfig[1];
                                }

                                $(this.content).parent().css(cssRules);

                            },
                            change() {
                                self.updateHash('open', this);
                            },
                            close() {
                                self.updateHash('close');
                            },
                            afterClose() {
                                doc.body.classList.remove('themify_mp_opened');
                            }
                        }
                    };

            if ($groupItems.length > 1) {
                $.extend(args, {
                    gallery: {
                        enabled: true,
						tCounter: self.config.i18n ? self.config.i18n.tCounter : ''
                    }
                });
            }

            if ($self.find('img').length > 0) {
                $.extend(args, {
                    mainClass: 'mfp-with-zoom',
                    zoom: {
                        enabled: !Themify.isTouch,
                        duration: 300,
                        easing: 'ease-in-out',
                        opener() {
                            return $self.find('img');
                        }
                    }
                });
            }
            args['mainClass'] = args['mainClass']?args['mainClass']:'';
            args['mainClass'] += isVideo ? ' video-frame' : ' standard-frame';
            args['fixedContentPos'] = true;
            if (self.isInIframe()) {
                win.parent.jQuery.magnificPopup.open(args);
            } else {
                $.magnificPopup.open(args, index);
            }
        },
        gallerySelector(e){
            const self=ThemifyGallery;
            if ('image' !== self.getFileType($(this).prop('href'))) {
                return;
            }
            const $gallery = $(self.config.gallerySelector, $(this).closest('.module, .gallery, .gallery-wrapper').not( '.module-gallery' ) );
			if ( ! $gallery.length ) {
				return;
			}
            e.preventDefault();
            e.stopImmediatePropagation();
            const images = [];
            $gallery.each(function () {
                let description = $(this).prop('title');
                description = '' !== description ? description : (typeof $(this).children('img').prop('alt') !== 'undefined') ? $(this).children('img').prop('alt') : '';
                if ($(this).parent().next('.gallery-caption').length > 0) {
                    // If there's a caption set for the image, use it
                    description = $(this).parent().next('.wp-caption-text').html();
                } else if ($(this).find('.gallery-caption').find('.entry-content').length > 0) {
                    description = $(this).find('.gallery-caption').find('.entry-content').text();
                }
                images.push({src: $(this).prop('href'), title: description, type: 'image'});
            });
            const args = {
                gallery: {
                    enabled: true
                },
                image: {
                    markup: self.getImageMarkup(this)
                },
                items: images,
                mainClass: 'mfp-with-zoom',
                zoom: {
                    enabled: !Themify.isTouch,
                    duration: 300,
                    easing: 'ease-in-out',
                    opener(openerElement) {
                        var imageEl = $($gallery[openerElement.index]);
                        return imageEl.is('img') ? imageEl : imageEl.find('img');
                    }
                },
                callbacks: {
                    open() {
                        self.updateHash('open', this);
                        self.openSharing(this);
                    },
                    change() {
                        self.updateHash('open', this);
                    },
                    close() {
                        self.updateHash('close');
                    }
                }
            };
            if (self.isInIframe()) {
                win.parent.jQuery.magnificPopup.open(args, $gallery.index(this));
            } else {
                $.magnificPopup.open(args, $gallery.index(this));
            }
        },
        contentImagesAreas(e){
            const self=ThemifyGallery;
            if(self.getFileType(this.getAttribute('href'))==='image' &&  $(this).closest(self.config.contentImagesAreas)){
                e.preventDefault();
                e.stopImmediatePropagation();
                const $this = $(this),
                        args = {
                            items: {
                                src: $this.prop('href'),
                                title: $this.next('.wp-caption-text').length > 0 ? $this.next('.wp-caption-text').html() : $this.children('img').prop('alt')
                            },
                            image: {
                                markup: self.getImageMarkup(this)
                            },
                            type: 'image',
                            callbacks: {
                                open() {
                                    self.updateHash('open', this);
                                    self.openSharing(this);
                                },
                                change() {
                                    self.updateHash('open', this);
                                },
                                close() {
                                    self.updateHash('close');
                                }
                            }
                        };
                if ($this.find('img').length > 0) {
                    $.extend(args, {
                        mainClass: 'mfp-with-zoom',
                        zoom: {
                            enabled: !Themify.isTouch,
                            duration: 300,
                            easing: 'ease-in-out',
                            opener() {
                                return $this.find('img');
                            }
                        }
                    });
                }
                if (self.isInIframe()) {
                    win.parent.jQuery.magnificPopup.open(args);
                } else {
                    $.magnificPopup.open(args);
                }
            }
        },
        initLightbox() {
            // Lightbox Link
            Themify.body.off('click', this.config.lightboxSelector, this.lightboxSelector).on('click', this.config.lightboxSelector, this.lightboxSelector);
            if (this.config.gallerySelector) {
                // Images in WP Gallery
                Themify.body.off('click', this.config.gallerySelector,this.gallerySelector)
                        .on('click', this.config.gallerySelector,this.gallerySelector);
            }
            // Images in post content
            if (this.config.contentImagesAreas) {
                Themify.body.off('click', '.post-content a,.page-content a',this.contentImagesAreas).on('click', '.post-content a,.page-content a',this.contentImagesAreas);
            }
        },
        isInIframe() {
            return this.config['extraLightboxArgs'] && this.config['extraLightboxArgs']['displayIframeContentsInParent'];
        },
        getFileType(itemSrc) {
            let url;
            try {
                url = new URL(itemSrc);
            } catch (_) {
                url = itemSrc;
            }
            const pureURL = url && typeof url === 'object'?itemSrc.replace(url.search,''):itemSrc;
            if (pureURL.match(/\.(gif|jpg|jpeg|tiff|png|webp|apng)$/i)) {
                return 'image';
            } else if (itemSrc.match(/\bajax=true\b/i)) {
                return 'ajax';
            } else if (itemSrc.substr(0, 1) === '#') {
                return 'inline';
            } else {
                return 'iframe';
            }
        },
        isVideo(itemSrc) {
            return this.isYoutube(itemSrc)
                    || this.isVimeo(itemSrc) || itemSrc.match(/\b.mov\b/i)
                    || itemSrc.match(/\b.swf\b/i);
        },
        isYoutube(itemSrc) {
            return Themify.parseVideo(itemSrc).type === 'youtube';
        },
        isVimeo(itemSrc) {
            return Themify.parseVideo(itemSrc).type === 'vimeo';
        },
        getYoutubePath(url) {
            let ret = '//youtube.com/watch?v=';
            ret += url.match(/youtu\.be/i) ? url.match(/youtu\.be\/([^\?]*)/i)[1] : this.getParam('v', url);
            return ret;
        },
        /**
         * Add ?iframe=true to the URL if the lightbox is showing external page
         * this enables us to detect the page is in an iframe in the server
         */
        getiFrameLink(link) {
            if (this.getFileType(link) === 'iframe' && this.isVideo(link) === null) {
                Themify.parseVideo(link)
                link = Themify.updateQueryString('iframe', 'true', link)
            }
            return link;
        },
        getParam(name, url) {
            name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
            const regexS = "[\\?&]" + name + "=([^&#]*)",
                    regex = new RegExp(regexS),
                    results = regex.exec(url);
            return results == null ? '' : results[1];
        },
        getCustomParams(url) {
            let params = url.split('?')[1];
            params = params ? '&' + params.replace(/[\\?&]?(v|autoplay)=[^&#]*/g, '').replace(/^&/g, '') : '';

            return '?autoplay=1' + params;
        },
        openSharing(self) {
            if(this.config.disableSharing){
                return;
            }
            const el = self.content[0].tfClass('tf_social_sharing')[0];
            if (el) {
                el.tfOn('click', function (e) {
                    e.preventDefault();
                    Themify.sharer(e.target.dataset.type, self.currItem.data.src.replace('?iframe=true', ''), self.currItem.data.title);
                });
            }
        },
        updateHash(action, instance) {
            if ('open' === action) {
                // cache the current location.hash
                if (this.origHash === null) {
                    this.origHash = win.location.hash;
                }
                let hash = instance.currItem.data.title,
                        // Escape HTML
                        div = doc.createElement('div');
                div.innerHTML = hash;
                hash = div.textContent.trim();
                if ('' !== hash) {
                    this._updateHash(hash);
                }
            } else {
                // when closing the lightbox, restore the cached hashtag
                this._updateHash(this.origHash);
                this.origHash = null;
            }
        },
        /**
         * Backwards-compatible function to change the hashtag in browser's address bar
         * Note: this does not trigger 'hashchange' event.
         */
        _updateHash(newhash) {
            if (('' + newhash).charAt(0) !== '#')
                newhash = '#' + newhash;
            history.replaceState('', '', newhash);
        },
        openAnchor() {
            if ('' !== win.location.hash) {
                let hash = decodeURI(win.location.hash.substring(1)),
                        el = doc.querySelector('[alt="' + hash + '"]');
                el = null === el ? doc.querySelector('[title="' + hash + '"]') : el;
                if (null !== el) {
                    el.click();
                }
            }
        },
		getIcon(icon){
			icon='tf-'+icon.trim().replace(' ','-');
			const ns='http://www.w3.org/2000/svg',
				use=doc.createElementNS(ns,'use'),
				svg=doc.createElementNS(ns,'svg');
			svg.setAttribute('class','tf_fa '+icon);
			use.setAttributeNS(null, 'href','#'+icon);
			svg.appendChild(use);
			return svg;
		},
        getSocialMarkup() {
			if(this.config.disableSharing){
			    return '';
            }
            Themify.fonts(['ti-facebook','ti-twitter-alt','ti-pinterest','ti-email']);
            return '<div class="tf_social_sharing">' +
                    '<a href="#" data-type="facebook">'+this.getIcon('ti-facebook').outerHTML+'</a>' +
                    '<a href="#" data-type="twitter">'+this.getIcon('ti-twitter-alt').outerHTML+'</a>' +
                    '<a href="#" data-type="pinterest">'+this.getIcon('ti-pinterest').outerHTML+'</a>' +
                    '<a href="#" data-type="email">'+this.getIcon('ti-email').outerHTML+'</a>' +
                    '</div>';
        },
		getTitle( el ) {
			const img = el.querySelector( 'img' );
			let title = el.dataset.t;
			if ( ! title && img ) {
				title = img.title || img.alt;
			}
			if ( ! title ) {
				title = el.title;
			}
			return title;
		},
        getImageMarkup(el) {
            const titleWrap = 'no' !== el.dataset.title ? '<div class="mfp-title"></div>' : '';
            return '<div class="mfp-figure">' +
                    '<div role="button" tabindex="0" class="tf_close mfp-close"></div>' +
                    '<div class="mfp-counter"></div>' +
                    '<div class="mfp-img"></div>' +
                    '<div class="mfp-bottom-bar">' +
                    titleWrap +
                    this.getSocialMarkup() +
                    '</div>' +
                    '</div>';
        }
    };


    Themify.on('tf_gallery_init', options=> {
        ThemifyGallery.init(options);
    });

})(jQuery, Themify, document, window);
