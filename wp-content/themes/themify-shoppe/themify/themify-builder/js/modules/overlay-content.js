/*overlay content module*/
;((Themify,doc)=>{
	'use strict';
	const InitOverlay=el=>{
                el=el.tfClass('sidemenu')[0];
                if(el){
                    const id=el.id, 
                        item=doc.querySelector('a[href="#'+id+'"]');
                    if(item){
                        Themify.sideMenu(item,{
                                panel:'#'+id,
                                close:'#'+id+'_close'
                        });
                        el.style.display='block';
                    }
                }
        },
        InitExpandable=el=>{
                el=el.tfClass('tb_ov_co_icon_wrapper')[0];
                if(el){
                    el.tfOn('click',function(e){
                        e.preventDefault();
                        const container=this.closest('.module-overlay-content'),
                                belowExpand=container.tfClass('tb_oc_expand_below')[0];
                        if(belowExpand){
                            belowExpand.style.minHeight=container.classList.contains('tb_oc_open')?0:belowExpand.scrollHeight+"px";
                        }
                        container.classList.toggle('tb_oc_open');
                    });
                }
        };
	Themify.on('builder_load_module_partial',(el,isLazy)=>{
            if(isLazy===true && !el.classList.contains('module-overlay-content')){
                return;
            }
            const items = Themify.selectWithParent('module-overlay-content',el),
                 bodyOverlay=doc.tfClass('body-overlay')[0],
                ev=Themify.isTouch?'touchend':'click';
            for(let i=items.length-1; i>=0; --i){
                if('overlay'===items[i].dataset.overlay){
                    if(bodyOverlay){
                        items[i].querySelector('.tb_oc_overlay_layer').tfOn(ev,()=>{
                            bodyOverlay.click();
                        },{passive:true});
                    }
                    InitOverlay(items[i]);
                }else{
                    InitExpandable(items[i]);
                }

				if ( items[i].tfClass('tb_active_builder')[0] ) {
					items[i].classList.add( 'has_active_builder' );
				}
            }
	});
})(Themify,document);
