/* WC Accordion tab*/
;
(($,Themify,win)=>{
	'use strict';
	const _clicked = e=>{
        const el=e.target?e.target.closest('.tf_wc_acc_title'):null;
        if(el){
            e.preventDefault();
            e.stopPropagation();
            const li = el.closest('li'),
                content=li.tfClass('tf_wc_acc_content')[0],
                cl=li.classList;
            if(cl.contains('active')){
                cl.remove('active');
                $(content).slideUp();
            }else{
                const active=li.parentNode.tfClass('active')[0];
                if(active){
                    active.classList.remove('active');
                    $(active.querySelector('.tf_wc_acc_content')).slideUp();
                }
                cl.add('active');
                $(content).slideDown();
            }
                    Themify.trigger('tfsmartresize',{w:Themify.w,h:Themify.h});
            $(win).triggerHandler( 'resize' );
        }
	},
    scrollTo=e=>{
        const hash = win.location.hash.replace('#','');
        if(hash){
            const found = document.querySelector('.tf_wc_acc_content #'+CSS.escape(hash));
            if(found){
                const p=found.closest('.tf_wc_acc_content').closest('li');
                if(!p.classList.contains('active')){
                    Themify.triggerEvent(p.tfClass('tf_wc_acc_title')[0],Themify.click);
                }
                Themify.scrollTo(p.getBoundingClientRect().top);
                win.location.hash='';
                win.history.replaceState('', '', win.location.pathname);
            }
        }
    };
	Themify.on('tf_wc_acc_tabs_init', wrap=>{
        wrap.tfOff(Themify.click,_clicked)
        .tfOn(Themify.click,_clicked);
	});
    win.tfOn('hashchange',scrollTo,{passive:true});
})(jQuery,Themify,window);
