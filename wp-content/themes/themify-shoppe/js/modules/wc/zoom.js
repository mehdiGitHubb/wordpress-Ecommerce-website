/**
 * Module Product Zoom
 */
;
(($,Themify)=>{
    'use strict';
    const _init = (el,e)=>{
        el.classList.add('zoom_progress');
        Themify.imagesLoad(el).then(item=>{
             const $this = $(item);
                $this.zoom({
                    on: 'click',
                    url:item.getAttribute('data-zoom-image'),
                    callback() {
                            $this.trigger('click.zoom',e);
                            item.classList.remove('zoom_progress');
                    },
                    onZoomIn() {
                        item.classList.add('zoomed');
                    },
                    onZoomOut() {
                        item.classList.remove('zoomed');
                    }
                });
        });
    };
    Themify.on('themify_theme_product_zoom',(items,e)=>{
        Themify.loadJs(themify_vars.theme_url + '/js/modules/wc/jquery.zoom.min','undefined' !== typeof $.fn.zoom,'1.7.21').then(()=>{
                _init(items,e);
        });
    });
})(jQuery,Themify);