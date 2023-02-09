/**
 * wow module
 */
;
(Themify=>{
    'use strict';
    let is_working=false;
	const hoverCallback=function() {
		if (is_working === false) {
                    is_working = true;
                    const st=this.style,
                        animation = st.animationName,
                            hover=this.dataset.tfAnimation_hover;
                    if (animation) {
                            st.animationIterationCount=st.animationDelay=st.animationName = '';
                            this.classList.remove(animation);
                    }
                    this.tfOn('animationend',function(e){
                            this.classList.remove('animated','tb_hover_animate',e.animationName);
                            this.style.animationName = this.style.willChange='';
                            is_working = false;
                    },{passive:true,once:true});
                    
                    st.animationName = hover;
                    this.classList.add('animated','tb_hover_animate',hover);
		}
	},
	hover=el=>{
		const ev=Themify.isTouch?'touchstart':'mouseenter',
		events = [ev,'tf_custom_animate'];
		el.tfOff(events, hoverCallback, {passive: true})
        .tfOn(events, hoverCallback, {passive: true});
	},
	animate=el=> {
		Themify.imagesLoad(el).then(item=>{
			item.style.visibility = 'visible';
			if (item.hasAttribute('data-tf-animation')) {
					if (item.hasAttribute('data-tf-animation_repeat')) {
							item.style.animationIterationCount = item.dataset.tfAnimation_repeat;
					}
					if (item.hasAttribute('data-tf-animation_delay')) {
							item.style.animationDelay = item.dataset.tfAnimation_delay + 's';
					}
					const cl=item.dataset.tfAnimation;
					item.classList.add(cl);
					item.style.animationName = cl;
					item.tfOn('animationend', function () {
						this.style.animationIterationCount=this.style.animationDelay=this.style.willChange='';
						this.classList.remove('animated',cl);
						this.removeAttribute('data-tf-animation');
					}, {passive: true, once: true})
                    .classList.add('animated');
			}
			if (item.classList.contains('hover-wow')) {
				hover(item);
			}
		});
	},
	observer= new IntersectionObserver((entries, _self)=>{
            for (let i = entries.length - 1; i > -1; --i) {
                if (entries[i].isIntersecting === true) {
                    _self.unobserve(entries[i].target);
                    animate(entries[i].target);
                }
            }
	});
    Themify.on('tf_wow_init', items=> {
        Themify.animateCss().then(() =>{
            for (let i = items.length - 1; i > -1; --i) {
                items[i].style.willChange='transform,opacity';
                observer.observe(items[i]);
            }
        });
    });

})(Themify);