/**
 * accordion module
 */
;
(($,Themify,doc,win) =>{
    'use strict';
    let isAttached=false;
    const style_url=ThemifyBuilderModuleJs.cssUrl+'accordion_styles/',
        init=()=>{
            doc.body.tfOn( 'click', e=> {
                    const target=e.target?e.target.closest('.accordion-title'):null;
                    if(target){
                        e.preventDefault();
                        e.stopPropagation();
                        const $this = $(target),
                            $panel = $this.next(),
                            $item = $this.closest('li'),
                            $parent = $item.parent(),
                            hashtag = $parent[0].parentNode.hasAttribute('data-hashtag'),
                            activeIcon=$item.find('>.accordion-title .accordion-active-icon'),
                            passiveIcon=$item.find('>.accordion-title .accordion-icon'),
                            type = $parent.closest('.module-accordion[data-behavior]').data('behavior'),
                            def = $item.toggleClass('current').siblings().removeClass('current'); /* keep "current" classname for backward compatibility */

                            $parent[0].classList.add('tf-init-accordion');


                        if ('accordion' === type) {
                            def.find('.accordion-content').slideUp().closest('li').removeClass('builder-accordion-active');
                            def.find('.accordion-title > a').attr('aria-expanded', 'false');
                            def.find( '>.accordion-title .accordion-icon' ).removeClass( 'tf_hide' ).end()
                                .find( '>.accordion-title .accordion-active-icon' ).addClass( 'tf_hide' );
                        }
                        if ($item.hasClass('builder-accordion-active')) {

                            activeIcon.addClass('tf_hide');
                            passiveIcon.removeClass('tf_hide');
                            $panel.slideUp();
                            $item.removeClass('builder-accordion-active').find('>.accordion-title > a').attr('aria-expanded', 'false');
                            $panel.attr('aria-hidden', 'true');
                            if (true===hashtag && win.location.hash === e.target.closest('a').getAttribute('href')) {
                                win.history.pushState('', '', win.location.pathname);
                            }
                        } else {
                            activeIcon.removeClass('tf_hide');
                            passiveIcon.addClass('tf_hide');
                            $item.addClass('builder-accordion-active');
                            $panel.slideDown();
                            $item.find('>.accordion-title > a').attr('aria-expanded','true');
                            $panel.attr('aria-hidden', 'false');

                            // Show map marker properly in the center when tab is opened
                            const existing_maps = $panel.hasClass('default-closed') ? $panel.find('.themify_map') : false;
                            if (existing_maps) {
                                for (let i =existing_maps.length-1; i>-1 ;--i) { // use loop for multiple map instances in one tab
                                    let current_map = $(existing_maps[i]).data('gmap_object'); // get the existing map object from saved in node
                                    if (typeof current_map.already_centered !== 'undefined' && !current_map.already_centered)
                                        current_map.already_centered = false;
                                    if (!current_map.already_centered) { // prevent recentering
                                        let currCenter = current_map.getCenter();
                                        google.maps.event.trigger(current_map, 'resize');
                                        current_map.setCenter(currCenter);
                                        current_map.already_centered = true;
                                    }
                                }
                            }
                            if(true===hashtag){
                                win.history.pushState(null, null,  e.target.closest('a').getAttribute('href'));
                            }
                        }
                        Themify.trigger('tb_accordion_switch', [$panel]);
                        $(win).triggerHandler( 'resize' );
                    }
                });
        },
        hashchange = ()=> {
            const hash = win.location.hash.replace('#','');
            if ( hash !== '' && hash !== '#' ) {
                const acc = doc.querySelector( '.module-accordion [data-id="'+hash+'"]' );
                if ( acc ) {
                    const target = doc.querySelector( '.accordion-title a[href="#' + hash + '"]' );
                    target.click();
                }
            }
        };
    Themify.on('builder_load_module_partial',(el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-accordion')){
            return;
        }
        const items = Themify.selectWithParent('module-accordion',el);
        for(let i=items.length-1;i>-1;--i){
            if(items[i].classList.contains('separate')){
                Themify.loadCss(style_url+'separate','tb_accordion_separate');
            }
            if(items[i].classList.contains('transparent')){
                Themify.loadCss(style_url+'transparent','tb_accordion_transparent');
            }
        }
        if(isAttached===false){
            isAttached = true;
            win.tfOn( 'hashchange', hashchange, { passive : true } );
            Themify.requestIdleCallback(()=>{
                init();
                hashchange();
            },-1,500);
        }

    });

})(jQuery,Themify,document,window);
