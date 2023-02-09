/**
 * feature module
 */
;
(Themify => {
    'use strict';
    const style_url=ThemifyBuilderModuleJs.cssUrl+'feature_styles/',
        sizes={small:100,medium:150,large:200},
        init =item=> {
            const p=item.closest('.module-feature');
            if(p){
                const cl = p.classList;
                if(cl.contains('layout-icon-left')){
                    Themify.loadCss(style_url+'left','tb_feature_left');
                }
                else if(cl.contains('layout-icon-right')){
                    Themify.loadCss(style_url+'right','tb_feature_right');
                }
                const svgItem=item.tfClass('tb_feature_stroke')[0],
                    progress = svgItem?svgItem.dataset.progress:null;
                if(progress){
                    if(cl.contains('with-overlay-image')){
                        Themify.loadCss(style_url+'overlay','tb_feature_overlay');
                    }
                    let w=0;
					if(!cl.contains('size-custom')){
						for(let i in sizes){
							if(cl.contains('size-'+i)){
								w=sizes[i];
								break;
							}
						}
					}
					else{
						w=parseInt(item.style.width) || 0;
					}
                    if(w===0){
                        w=item.offsetWidth;
                    }
                    w=parseFloat(w/2)-parseFloat(svgItem.getAttribute('stroke-width')/2);
                    svgItem.setAttribute('stroke-dasharray', (parseFloat((2*Math.PI*w*progress)/100)+',10000'));
                }

				if ( p.hasAttribute( 'data-layout-mobile' ) ) {
					const layout_mobile = p.dataset.layoutMobile,
						layout_desktop = p.dataset.layoutDesktop,
						callback = e=> {
                            const cl=p.classList;
							if ( e.w > tbLocalScript.breakpoints.mobile ) {
								cl.remove( 'layout-' + layout_mobile );
								cl.add( 'layout-' + layout_desktop );
							} else {
								cl.remove( 'layout-' + layout_desktop );
								cl.add( 'layout-' + layout_mobile );
                                const mobile_icon=layout_mobile.replace('icon-','');
                                if(mobile_icon!=='top'){
                                    Themify.loadCss(style_url+mobile_icon,'tb_feature_'+mobile_icon);
                                }
							}
						};
					callback( { w : Themify.w } );
					Themify.on( 'tfsmartresize', callback );
				}
            }
        },
        observer=new IntersectionObserver( (entries, _self)=>{
            for (let i = entries.length - 1; i>-1; --i){
                if (entries[i].isIntersecting=== true){
                    _self.unobserve(entries[i].target);
                    init(entries[i].target);
                }
            }
        },{
            threshold:.9
        });
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-feature')){
           return;
        }
        const items = Themify.selectWithParent('module-feature',el);
        for(let i=items.length-1;i>-1;--i){
            let item=items[i].tfClass('module-feature-chart-html5')[0];
            if(item){
                observer.observe(item);
            }
        }
    });
})(Themify);
