/*
 * Themify Mega Menu Plugin
 */
;
( ($, Themify, document)=> {
    'use strict';
	let maxW;
    const cacheMenu = {},
		 ev = Themify.isTouch?'click':'mouseover',
            init = function(e){
				if (Themify.w < maxW || e.target.closest('.mega-menu-posts')) {
					return;
				}
				let el=e.type==='mouseenter' || e.type==='focus'?this.tfClass('mega-link')[0]:e.target;
				if(el.classList.contains('child-arrow')){
					el=el.closest('.has-mega-sub-menu').tfClass('mega-link')[0];
				}
				el = el.classList.contains('mega-link')?el:el.closest('.tf_mega_taxes .mega-link');
				if(!el){
					return;
				}
				if(e.type==='click'){
					e.preventDefault();
				}
				const $self = $(el),
					termid = $self.data('termid'),
					tax = $self.data('tax'),
					cl=el.tfTag('a')[0].classList,
					wrapper=$self.closest('.mega-sub-menu');
				let megaMenuPosts = $self[0].tfClass('mega-menu-posts')[0];
				if(!megaMenuPosts){
					megaMenuPosts = document.createElement('div');
					megaMenuPosts.className = 'mega-menu-posts tf_left tf_box';
					$self[0].appendChild(megaMenuPosts);
				}
				wrapper.find('.tf_mega_selected').removeClass('tf_mega_selected');
				$self.addClass('tf_mega_selected');
				if (cacheMenu[termid] !== undefined) {
					megaMenuPosts.innerHTML=cacheMenu[termid];
				} 
				else if (!cl.contains('tf_loader')) {
					cl.add('tf_loader');
					$.ajax({
						url: themify_vars.ajax_url,
						type: 'POST',
						data: {
							action: 'themify_theme_mega_posts',
							termid: termid,
							tax: tax
						},
						complete() {
							cl.remove('tf_loader');
						},
						success(response) {
							cacheMenu[termid] = response;
							megaMenuPosts.innerHTML=cacheMenu[termid];
						}
					});
				}
            };
   
    Themify.on('tf_mega_menu',  (menu,mob_point)=> {
		const items=menu.tfClass('tf_mega_taxes');
		maxW=mob_point;
		for(let i=items.length-1;i>-1;--i){
			items[i].tfOn(ev,init);
			if(ev==='mouseover'){
				items[i].tfOn('focusin',init,{passive:true});
			}
			let parent=items[i].closest('.has-mega-sub-menu');
			if(parent){
				if(ev==='mouseover'){
					parent.tfOn('mouseenter',init,{passive:true});
					parent.querySelector('a').tfOn('focus',init.bind(parent),{passive:true});
				}else{
					parent.tfClass('child-arrow')[0].tfOn('click',init);
				}
			}
		}
    },true);
    if(!Themify.isTouch){
		setTimeout(()=>{
			Themify.edgeMenu();
		},1500);
	}
})(jQuery, Themify, document);
