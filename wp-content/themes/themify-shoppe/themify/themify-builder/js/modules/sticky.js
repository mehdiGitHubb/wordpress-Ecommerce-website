/**
 * sticky js
 */
;
((Themify,win)=>{
    'use strict';
	let t1,
	isDisable=false,
	vWidth=Themify.w,
	vHeight=Themify.h,
	isAdded=false;
	const map = new Map(),
		tablet=tbLocalScript.is_sticky==='m'?parseInt(tbLocalScript.breakpoints.tablet[1]):false,
		_scroller=item=>{
			if(isDisable===true){
				return;
			}
			requestAnimationFrame(()=>{
				const offset=win.pageYOffset,
					items=item?item:map;
				for (let entry of items) {
					let el =entry[0],
						opt=entry[1],
						isFixed=el.classList.contains('tb_sticky_scroll_active');
					if(opt==='disable'){
						continue;
					}
					if((opt.isBottom===true && ((offset+vHeight)>=opt.space)) || (opt.isBottom!==true &&offset > opt.space)){
						if(isFixed===false){
							el.parentNode.style.height= opt.h+'px';
							el.style.position= 'fixed';
							el.style.top= opt.value;
							el.style.width= opt.w+'px';
							el.classList.add('tb_sticky_scroll_active');
						}
						if(opt.unstick && opt.unstick.item){
							let unstick=opt.unstick,
								v=parseInt(opt.value),
								b=unstick.item.getBoundingClientRect(),
								newTop;
							if(unstick.type==='builder'){
								newTop=b.bottom - opt.h - v;
							}
							else{
								if(unstick.r==='passes'){
									newTop=b.bottom - v;
								}
								else{
									newTop=b.top-opt.h- v;
								}
								if(unstick.cur==='top' || unstick.cur==='bottom'){
									newTop+=unstick.v;
									if(unstick.cur==='bottom'){
										newTop-=vHeight;
									}
								}
							}
							newTop=newTop < 0?(newTop+v+'px'):opt.value;
							if(opt.currentTop!==newTop){
								opt.currentTop=newTop;
								map.set(el,opt);
								el.style.top=newTop;
							}
						}
					}
					else if(isFixed===true){
						_unsticky(el);
					}
				}
			});
	},
	_unsticky=el=>{
        const st=el.style;
		st.width=st.top=st.bottom=st.position=el.parentNode.style.height= '';
		el.classList.remove('tb_sticky_scroll_active');
	},
    _resize =  e=> {
		if ( ! e ) {
			return;
		}
		vWidth=e.w;
		vHeight=e.h;
		isDisable = !!(tablet && tablet>=vWidth);
		for (let entry of map) {
			if(isDisable===true){
				_unsticky(entry[0]);
			}
			else{
				_init(entry[0],null,true);
			}
		}
		if(isDisable===false){
			_scroller();
		}
    },
	getCurrentBreakpointValues=(vals)=>{
		let found=false;
		const bp=tbLocalScript.breakpoints,
			items=Object.keys(bp);
		for(let i=items.length-1;i>-1;--i){
			let p=items[i],
				k=p==='tablet_landscape'?'tl':p[0];
			if(vals[k]!==undefined){
				let v=p!=='mobile'?bp[p][1]:bp[p];
				if(v>=vWidth){
					found=vals[k];
					break;
				}
			}
		}
		if(found===false){
			found=vals.d;
		}
		return found;
	},
	mutationObserver = new MutationObserver(mut=> {
		if (mut[0]) {
			let t=mut[0].target.closest('[data-sticky-active]');
			if(t){
				if(t1){
					cancelAnimationFrame(t1);
				}
				t1=requestAnimationFrame(()=>{
					Themify.imagesLoad(t).then(st=>{
						const tmp = new Map();
						_unsticky(st);
						_init(st);
						tmp.set(st,map.get(st));
						_scroller(tmp);
						t1=null;
					});
				});
			}
		}
	}),
	_init=(el,box,recreate)=>{
		const isFixed=el.classList.contains('tb_sticky_scroll_active');
		if(isFixed===false || recreate===true){
			if(!map.has(el) || recreate===true){
				const opt=getCurrentBreakpointValues(JSON.parse(el.getAttribute('data-sticky-active')));
				if(!opt){
					map.set(el,'disable');
					_unsticky(el);
					return;
				}
				const stick=opt.stick || {},
					stickVal=stick.v?parseInt(stick.v):0,
				unstick=opt.unstick,
				u=stick.u || 'px';
				if(u!=='px'){
					opt.u=u;
				}
				else{
					opt.value= (stickVal+u);
				}
				opt.v= stickVal;
				if(stick.p==='bottom'){
					opt.isBottom= true;
				}
				if(unstick){
					let unstickItem,
					builder=el.closest('.themify_builder_content');
                    if('builder'===unstick.type){
						let tmp=builder.closest('#tbp_header');
                        if(tmp){
                            tmp=document.tfId('tbp_content');
                            tmp=tmp!==null?tmp.tfClass('themify_builder_content')[0]:document.tfClass('themify_builder_content')[1];
                            if(tmp){
                                builder=tmp;
                            }
                        }
						unstickItem = builder;
					}
					else{
						if('row'===unstick.type){
							unstickItem=builder.tfClass('tb_'+unstick.el)[0];
						}
						if(!unstickItem){
							unstickItem=builder.tfClass('tb_'+unstick.el)[0];
						}
						if(unstickItem){
							unstick.v=parseInt(unstick.v);
						}
					}
					if(unstickItem){
						unstick.item=unstickItem;
					}
				}
				if(!el.parentNode.classList.contains('tb_sticky_wrapper')){
					const wrapper=document.createElement('div');
					wrapper.className='tb_sticky_wrapper';
					el.before(wrapper);
					wrapper.appendChild(el);
				}
				map.set(el,opt);
			}
			const vals=map.get(el);
			if(vals==='disable'){
				return;
			}
			if(isFixed===true){
				el.style.position='';
			}
			if(!box){
				el.style.width='';
				box=el.getBoundingClientRect();
			}
			vals.w= box.width>0?box.width:el.offsetWidth;
			vals.h= box.height>0?box.height:el.offsetHeight;
			
			let v=vals.v;
			if(vals.u ==='%' && v!==0){
				v=(v/100)*vHeight;
			}
			if( vals.isBottom===true){
				v=vHeight-v-vals.h;
			}
			vals.value= v+'px';
			vals.space= vals.isBottom!==undefined?(box.bottom+win.pageYOffset+v):(box.top+win.pageYOffset-v);
			vals.t=box.top;
			if(el.parentNode.style.height!==(vals.h+'px')){
				el.parentNode.style.height=vals.h+'px';
			}
			if(isFixed===true){
				el.style.position='fixed';
			}
			map.set(el,vals);
		}
	},
	observer = new IntersectionObserver((entries, _self)=> {//only need for recalculate the positions,width/height and etc, will be replaced with ResizeObserver in the future
		for (let i = entries.length - 1; i > -1; --i) {
			if (entries[i].isIntersecting === true) {
				_init(entries[i].target,entries[i].boundingClientRect);
			}
		}
	}, {
		 threshold:[.3,.4,.5,.6,.7,.8,.9,1]
	});
    Themify.on('tb_sticky_init',items=>{
        for (let i = items.length - 1; i > -1; --i) {
                observer.observe(items[i]);
                mutationObserver.observe(items[i], {subtree:true,childList:true});
        }
        if(isAdded===false){
                isAdded=true;
                if(win.pageYOffset>0){
                        for (let i = items.length - 1; i > -1; --i) {
                                _init(items[i],items[i].getBoundingClientRect());
                        }
                        _scroller();
                }
                win.tfOn('scroll',e=>{
                    _scroller();
                },{passive:true});
        }
    })
    .on('tfsmartresize',_resize);

})(Themify,window);
