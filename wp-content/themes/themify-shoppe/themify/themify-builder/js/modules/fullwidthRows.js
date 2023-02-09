/**
 * FullwidthRows for row
 */
;
(($,Themify,doc,vars) =>{
    'use strict';
    let resize=false;
    const isActive=Themify.is_builder_active,
        
        getCurrentValue = (el,type,prop)=>{
            let val = el.getAttribute('data-'+type + '-' + prop);
            const arr = ['mobile', 'tablet', 'tablet_landscape', 'desktop'];
            if (!val) {
                if (type !== 'desktop') {
                    for (let i = arr.indexOf(type) + 1; i < 4; ++i) {
                                            val = el.getAttribute('data-'+arr[i] + '-' + prop);
                                            if (val) {
                                                    el.setAttribute('data-'+type + '-' + prop, val);
                                                    break;
                                            }
                    }
                }
            }
            return val ? val.split(',') : [];
    },
    init=(items,isTrigger)=>{
        const container =Themify.body,
            outherWith = container.outerWidth(),
            outherLeft = container.offset().left;
        if (outherWith === 0) {
            return;
        }
       const points=vars.breakpoints,
            width = doc.documentElement.clientWidth,
            f=doc.createDocumentFragment();
            let type = 'desktop';
            if (width <= points.mobile) {
                type = 'mobile';
            }
            else if (width <= points.tablet[1]) {
                type = 'tablet';
            }
            else if (width <= points.tablet_landscape[1]) {
                type = 'tablet_landscape';
            }
            for (let i =items.length-1; i >-1; --i) {
                    if(!isTrigger && !isActive && resize===false){
                            if(!items[i].hasAttribute('data-fullwidth-done')){
								items[i].dataset.fullwidthDone=1;
                            }
                            else{
								continue;
                            }
                    }
				
                let row = items[i].closest('.themify_builder_content');
                    if(row===null || row.closest('.slide-content')!==null){
                            continue;
                    }
                let left = $(row).offset().left - outherLeft,
                    right = outherWith - left - row.offsetWidth,
                    styleId,
                    style = '';
                if(isActive){
                    // set to zero when zoom is enabled
                    if (row.classList.contains('tb_zooming_50') || row.classList.contains('tb_zooming_75')) {
                        left = 0;
                        right = 0;
                    }
                }
                else{
                    let index = items[i].getAttribute('data-css_id');
                        if(!index){
							let m=items[i].className.match(/module_row_(\d+)/ig);
							if(m && m[0]){
									index=m[0].trim();
							}
							else{
									continue;
							}
                        }
						else{
							index='tb_'+index;
						}
                        styleId ='tb-fulllwidth-' + index;
                        style += '.themify_builder.themify_builder_content>.' + index + '.module_row{';
					
                }
                if (items[i].classList.contains('fullwidth')) {
                    let margin = getCurrentValue(items[i],type,'margin'),
                            sum = '';
                    if (margin[0]) {
                        sum = margin[0];
                        style += 'margin-left:calc(' + margin[0] + ' - ' + Math.abs(left) + 'px);';
                    }
                    else {
                        style += 'margin-left:' + (-left) + 'px;';
                    }
                    if (margin[1]) {
                        if (sum !== '') {
                            sum += ' + ';
                        }
                        sum += margin[1];
                        style += 'margin-right:calc(' + margin[1] + ' - ' + Math.abs(right) + 'px);';
                    }
                    else {
                        style += 'margin-right:' + (-right) + 'px;';
                    }
                    style += sum !== '' ? 'width:calc(' + outherWith + 'px - (' + sum + '));' : 'width:' + outherWith + 'px;';
                }
                else {
                    style += 'margin-left:' + (-left) + 'px;margin-right:' + (-right) + 'px;width:' + outherWith + 'px;';
                    if (left || right) {
                        let padding = getCurrentValue(items[i],type,'padding'),
                                sign = '+';
                        if (left) {
                            if (padding[0]) {
                                if (left < 0) {
                                    sign = '-';
                                }
                                style += 'padding-left:calc(' + padding[0] + ' ' + sign + ' ' + Math.abs(left) + 'px);';
                            }
                            else {
                                style += 'padding-left:' + Math.abs(left) + 'px;';
                            }
                        }
                        if (right) {
                            if (padding[1]) {
                                sign = right > 0 ? '+' : '-';
                                style += 'padding-right:calc(' + padding[1] + ' ' + sign + ' ' + Math.abs(right) + 'px);';
                            }
                            else {
                                style += 'padding-right:' + Math.abs(right) + 'px;';
                            }
                        }
                    }
                }

                if (isActive) {
                    items[i].style.paddingRight = items[i].style.paddingLeft = items[i].style.marginRight = items[i].style.marginLeft = '';
                    items[i].style.cssText += style;
                }
                else {
                    style += '}';
                    $('#' + styleId).remove();
                    let st = doc.createElement('style');
                    st.setAttribute('id',styleId);
                    st.textContent=style;
                    f.appendChild(st);
                }
            }
            if (!isActive) {
                doc.head.appendChild(f);
                if(isTrigger!==true){
                    Themify.trigger('tfsmartresize', {w:Themify.w, h:Themify.h});
                }
            }
    };
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        let items;
        if(isLazy===true){
            if((!el.classList.contains('fullwidth') && !el.classList.contains('fullwidth_row_container')) || el.closest( '.tb_overlay_content_lp' )){
                return;
            }
            items=[el];
        }
        else{
            items = Themify.selectWithParent('.fullwidth.module_row,.fullwidth_row_container.module_row',el);
        }
        if(items[0]!==undefined){
            init(items,false);
        }
    })
    .on('tfsmartresize',e=>{
        if(resize===false && e && e.w!==Themify.w){
            resize=true;
            init(doc.querySelectorAll('.fullwidth.module_row,.fullwidth_row_container.module_row'));
            resize=false;
        }
    });

})(jQuery,Themify,document,tbLocalScript);
