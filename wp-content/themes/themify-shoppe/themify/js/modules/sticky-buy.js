/**
 * Sticky Buy Button
 */
;
((Themify, $,doc)=>{
	'use strict';
	const _init = (pr_wrap,wrap)=>{
			const container = doc.createElement('div'),
				product = doc.createElement('div'),
				img_wrap = doc.createElement('div'),
				summary = doc.createElement('div'),
				pr_form = pr_wrap.querySelector('form.cart'),
				pr_title = !pr_wrap.classList.contains('tbp_template')?pr_wrap.querySelector('.product_title'):pr_wrap.querySelector('.module-product-title .tbp_title'),
				pr_price = pr_wrap.tfClass('price')[0],
				pr_image = pr_wrap.tfClass('woocommerce-product-gallery__image')[0],
				ind = doc.tfId('tf_sticky_form_wrap');
			container.className = 'tf_box pagewidth clearfix';
			product.id = pr_wrap.id;
			product.className = pr_wrap.classList;
			//wrap image
			img_wrap.className = 'tf_sticky_prod_img';
			// Image
			if(pr_image!==undefined){
				const gallery = doc.createElement('div');
				gallery.className = 'images';
				gallery.appendChild(pr_image.cloneNode(true));
				img_wrap.appendChild(gallery);
			}
			summary.className = 'summary entry-summary';
			// Title
			if(pr_title!==null){
				const t = doc.createElement('span');
				t.className = pr_title.className;
				t.innerHTML = pr_title.innerHTML;
				summary.appendChild(t);
			}
			// Price
			if(pr_price!==undefined){
				summary.appendChild(pr_price.cloneNode(true));
			}
			img_wrap.appendChild(summary);
			product.appendChild(img_wrap);
			// Form
			ind.style.height = pr_form.getBoundingClientRect().height+'px';
			product.appendChild(pr_form);
			container.appendChild(product);
			wrap.appendChild(container);
			_pw_padding(pr_wrap.classList.contains('tbp_template')?pr_wrap:doc.tfId('pagewrap'),wrap,'show');
		},
		_pw_padding = (wrap,el,act)=>{
			wrap.style.paddingBottom = act==='show'?el.getBoundingClientRect().height + 'px':'';
		},
		_move_form = (wrap,el, act)=>{
			const obs_el = doc.tfId('tf_sticky_form_wrap'),
				form = 'hide' === act ? el.querySelector('form.cart') : doc.querySelector('form.cart'),
				$var_form = $('.variations_form');
			if(!form){
				return;
			}
			if('hide' === act){
				obs_el.appendChild(form);
				obs_el.style.height = '';
			}else{
				obs_el.style.height = form.getBoundingClientRect().height+'px';
				el.tfClass('product')[0].appendChild(form);
			}
			if($var_form.length>0){
				$var_form.trigger( 'check_variations' );
			}
			_pw_padding(wrap,el,act);
		};
	Themify.on('tf_sticky_buy_init',el=>{
		const pr_wrap = doc.querySelector('#content .product,.tbp_template.product'),
            wrap=pr_wrap.classList.contains('tbp_template')?pr_wrap:doc.tfId('pagewrap'),
            st_buy=doc.createElement('div');
            st_buy.id='tf_sticky_buy';
            st_buy.className='tf_opacity tf_abs tf_w';
            pr_wrap.after(st_buy);
		Themify.on('tfsmartresize', () =>{
			_pw_padding(wrap,st_buy,(st_buy.classList.contains('tf_st_show')?'show':'hide'));
		});
		_init(pr_wrap,st_buy);
		const observer = new IntersectionObserver(entries=>{
			if (!entries[0].isIntersecting && entries[0].boundingClientRect.top<0) {
				_move_form(wrap,st_buy,'show');
				st_buy.classList.add('tf_st_show');
			} else {
				_move_form(wrap,st_buy,'hide');
				st_buy.classList.remove('tf_st_show');
			}
		});
		observer.observe(el);
	},true);
})(Themify, jQuery,document);