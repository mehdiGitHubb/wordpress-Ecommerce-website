/**
 * Ajax To cart module
 */
;
let ThemifyShoppeAjaxCart;
( ($, Themify,doc, themifyScript)=> {
    'use strict';
    // Ajax add to cart   
    let isWorking=false;
	const icons = doc.querySelectorAll('#header .icon-shopping-cart'),
    removeloaders=()=>{
        for(let i=icons.length-1;i>-1;--i){
			icons[i].classList.remove('tf_loader');
		}
		const shopping_cart = doc.querySelector('.cart .icon-shopping-cart');
		if ( shopping_cart ) {
			shopping_cart.classList.remove('tf_loader');
		}
   };
    Themify.body.on('adding_to_cart',  (e, button, data)=> {
        Themify.trigger('themify_theme_spark', button);
        for(let i=icons.length-1;i>-1;--i){
        	icons[i].className+=' tf_loader';
        }
    })
    .on('wc_fragments_loaded',  function(e, fragments, cart_hash){
        const cartButton = doc.tfId('cart-icon-count');
        if(cartButton!==null){
            this.classList.toggle('wc-cart-empty',cartButton.tfClass('cart_empty')[0]!==undefined);
        }
    })
	.on('added_to_cart',  function(e){
        removeloaders();
		if(themifyScript.ajaxCartSeconds && isWorking===false && !this.classList.contains('post-lightbox')){
			isWorking=true;
			let seconds=parseInt(themifyScript.ajaxCartSeconds);
			if(!doc.body.classList.contains('cart-style-dropdown')){
				const id=Themify.isTouch?'cart-link-mobile-link':'cart-link',
				el=doc.tfId(id);
				if(el!==null){
					const panelId=el.getAttribute('href'),
						panel=doc.tfId(panelId.replace('#',''));
					if(panel!==null){
						Themify.on('sidemenushow', (panel_id, side,_this)=>{
							if(panelId===panel_id){
								setTimeout( ()=> {
									if($(panel).is(':hover')){
										panel.tfOn('mouseleave',function(){
											_this.hidePanel();
											doc.body.classList.remove('tf_auto_cart_open');
										},{once:true,passive:true});
									}else{
										_this.hidePanel();
										doc.body.classList.remove('tf_auto_cart_open');
									}
									isWorking=false;
								},seconds);
							}
						},true);
						doc.body.classList.add('tf_auto_cart_open');
						setTimeout(()=>{
							el.click();
						},100);
					}
				}
			}
			else{
				const items=doc.tfClass('shopdock');
				for(let i=items.length-1;i>-1;--i){
					items[i].parentNode.classList.add('show_cart');
					setTimeout(()=> {
						items[i].parentNode.classList.remove('show_cart');
						isWorking=false;
					},seconds);
				}
			}
		
		}
	});
    // remove item ajax
	if ( typeof wc_add_to_cart_params !== 'undefined' ) {
		Themify.body.on('click', '.remove_from_cart_button', function(e){
			e.preventDefault();
			this.classList.remove('tf_close');
			this.classList.add('tf_loader');
		});
	}
	ThemifyShoppeAjaxCart = async function(e){
        // // WC Simple Auction, WooCommerce Subscriptions plugin compatibility
		if (this.classList.contains( 'auction_form' ) || window.location.search.indexOf('switch-subscription') > -1 || this.closest('.product-type-external')!==null) {
			return;
		}

		e.preventDefault();

		const data = new FormData(this),
			btn = this.tfClass('single_add_to_cart_button')[0],
            btnCL=btn?btn.classList:null,
			add_to_cart = this.querySelector('[name="add-to-cart"]');
		if ( ! add_to_cart || (btnCL && btnCL.contains('loading'))) {
			return;
		}
		if(add_to_cart.tagName!=='INPUT'){
			data.set('add-to-cart', add_to_cart.value);
		}
		if (btnCL) {
			btnCL.remove('added');
			btnCL.add('loading');
		}
		Themify.body.triggerHandler('adding_to_cart', [this.querySelector('[type="submit"]'), data]);
        
        try{
            const resp=await Themify.fetch(data,null,null,woocommerce_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'theme_add_to_cart' ));
            if (!resp) {
                throw 'error';
            }
            if(!resp.fragments && !resp.success){
                throw resp.data;
            }
            const fragments = resp.fragments,
                cart_hash = resp.cart_hash;
            // Block fragments class
            if (fragments) {
                const keys = Object.keys(fragments);
                let els = null;
                for(let i = keys.length-1;i>-1;i--){
                    els = doc.querySelectorAll(keys[i]);
                    for(let k = els.length-1;k>-1;k--){
                        els[k].className += ' updating';
                        els[k].outerHTML = fragments[keys[i]];
                    }
                }
            }
            if(btnCL){
                btnCL.add('added');
            }
            // Trigger event so themes can refresh other areas
            Themify.body.triggerHandler('added_to_cart', [fragments, cart_hash]);
            if (themifyScript.redirect) {
                window.location.href = themifyScript.redirect;
            }
        }
        catch(err){
            const fr=doc.createDocumentFragment(),
                wr=doc.createElement('div');
            await Themify.loadJs(Themify.url+'js/admin/notification',!!window.TF_Notification);
            if(!Array.isArray(err)){
                err=[err];
            }
            for(let i=0,len=err.length;i<len;++i){
                let tmp=doc.createElement('template');
                tmp.innerHTML=err[i];
                fr.appendChild(tmp.content);
            }
            wr.className='wc_errors';
            wr.appendChild(fr);
            await TF_Notification.showHide('error',wr,3000);
        }
        if(btnCL){
            btnCL.remove('loading');
        }
        removeloaders();
	};
	// Ajax add to cart in single page
	if (themifyScript.ajaxSingleCart) {
		const form = doc.querySelector('form.cart');
		if(form){
			form.tfOn('submit', ThemifyShoppeAjaxCart);
		}
	}

})(jQuery, Themify,document, themifyScript);
