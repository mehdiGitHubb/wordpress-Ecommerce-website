/**
 * video module
 */
;
(Themify=>{
	'use strict';
	const css_url=ThemifyBuilderModuleJs.cssUrl+'video_styles/',
		init=(el,clicked)=>{
		const items = Themify.selectWithParent('module-video',el),
			enter=e=>{	
				const el=e.currentTarget,
					video=el.closest('.module-video').tfTag('video')[0]; 
				if(!el.tfClass('tb_video_buffer')[0]){
					const bar=document.createElement('div'),
						progress=function (e) {
							let buffered = this.duration>0 && this.buffered.length>0?(100*(this.buffered.end(0) / this.duration)):0;
								if(buffered<2){
									buffered=0;
								}
							if(this.readyState>2){
								buffered=100;
								el.tfOff('pointerenter',enter,{passive:true});
								this.tfOff('loadstart waiting progress canplay',progress,{passive:true});
								bar.tfOn('transitionend',e=>{
									requestAnimationFrame(()=>{
										this.closest('.tb_hover_play').classList.add('tb_video_loaded');
										this.dataset.forceplay=1;
										Themify.video([this]);
										bar.remove();
									});
								},{passive:true,once:true});
							}
							if(buffered>2){
								bar.style.width=buffered+'%';
							}
						
					};
					Themify.loadCss('video','tf_video');
					Themify.loadJs('video-player');
					bar.className='tb_video_buffer tf_abs_t';
					el.tfTag('img')[0].after(bar);
					video.tfOn('loadstart waiting progress canplay',progress,{passive:true});
				}
				video.load();
		};
		for(let i=items.length-1; i> -1; --i){
			let item=items[i],
				btn=item.tfClass('tb_video_overlay')[0];
				if(item.classList.contains('video-overlay')){
                    Themify.loadCss(css_url+'overlay','tb_video_overlay');
				}
                if(!item.classList.contains('tb_hover_play')){
                    if(btn){
                        Themify.loadCss(css_url+'play_button','tb_video_play_button');
                        btn.tfOn('click',_click,{once:true,passive:true});
                    }
                    else{
                        let iframe=item.tfTag('noscript')[0];
                        if(iframe){
                            let tmp=document.createElement('template');
                            tmp.innerHTML=iframe.textContent.trim() || iframe.innerHTML;
                            iframe.replaceWith(tmp.content);
                        }
                        else if(clicked){
                            let video=item.tfTag('video')[0];
                            video.dataset.autoplay=1;
                            Themify.video([video]);
                        }
                    }
                }
                else if(!Themify.is_builder_active){
                    let overlay_img=item.tfTag('img')[0];
                    if(overlay_img){
                        overlay_img.parentNode.tfOn('pointerenter',enter,{passive:true});
                    }
                }
				item.classList.remove('tf_lazy');
		}
		},
		_click=function(e){
			e.stopPropagation();
			const wrap=this.closest('.module-video');
            wrap.classList.add('tf_lazy');
			this.remove();
			init(wrap,1);
		};
	Themify.on('builder_load_module_partial', (el,isLazy)=>{
            if(isLazy===true && !el.classList.contains('module-video')){
                return;
            }
            init(el);
	});

})(Themify);
