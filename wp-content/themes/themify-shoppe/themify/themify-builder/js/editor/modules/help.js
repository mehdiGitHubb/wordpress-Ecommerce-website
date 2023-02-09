let TB_Help;
((api, Themify, topWindow, doc) => {
    'use strict';
    TB_Help={
        el:null,
        async init() {
            try{
                const res=await api.LocalFetch({action:'tb_help'},'html');
                topWindow.document.body.appendChild(res);

                const tpl=topWindow.document.tfId('tmpl-help_lightbox'),
                    root=doc.createElement('div'),
                    toolBarRoot=api.ToolBar.el.getRootNode(),
                    baseCss=toolBarRoot.querySelector('#tf_base');
                    root.id='tb_help_lightbox_root';
                    root.className='tf_abs_t tf_w tf_h tf_hide';
                    root.attachShadow({
                        mode:'open'
                    }).appendChild(tpl.content);

                root.shadowRoot.prepend(baseCss.cloneNode(true));
                this.el=root.shadowRoot.tfId('lightbox');
                this.el.tfOn(Themify.click,e=>{
                    const target=e.target.closest('.nav,.player_btn,.tf_close,.menu');
                    if(target){
                        e.preventDefault();
                        e.stopPropagation();
                        const cl=target.classList;
                        if(target.closest('.nav')){
                            this.mainTabs(e);
                        }
                        else if(cl.contains('player_btn')){
                            this.play(e);
                        }
                        else if(cl.contains('tf_close')){
                            this.close(e);
                        }
                        else if(target.closest('.menu') && e.target.tagName==='A'){
                            this.tabs(e);
                        }
                    }
                    else if(e.target===e.currentTarget){
                        this.close(e);
                    }
                });
                tpl.replaceWith(root);
                root.classList.remove('tf_hide');
                requestAnimationFrame(()=>{
                    this.el.style.maxHeight='100%';
                    this.el.classList.remove('tf_opacity');
                });
            }
            catch(e){
                await api.Spinner.showLoader('error');
            }
            api.Spinner.showLoader('spinhide');
	},
	play(e) {
		const a = e.target.closest('a'),
                    href = a.getAttribute('href'),
                iframe = doc.createElement('iframe');
                iframe.className='tf_h tf_w tf_abs_t';
		iframe.setAttribute('frameborder', '0');
		iframe.setAttribute('allow', 'autoplay; fullscreen');
		iframe.src=href + '?rel=0&showinfo=0&autoplay=1&enablejsapi=1&html5=1&version=3';
		a.replaceWith(iframe);
	},
	tabs(e) {
            const target = e.target,
                wrapper = this.el.tfClass('video_wrapper')[0],
                active = wrapper.querySelector(target.getAttribute('href')),
                currentTab=wrapper.tfClass('current')[0],
                li=target.closest('li'),
                
                activePlayer = active.tfClass('player_btn')[0];
                
                currentTab.classList.remove('current');
                
                active.classList.add('current');
                li.parentNode.tfClass('current')[0].classList.remove('current');
                li.classList.add('current');
                
                this.stopPlay();
                if (activePlayer) {
                    Themify.triggerEvent(activePlayer,e.type);
                } else {
                    this.startPlay();
                }
	},
	execute(iframe, param) {
            iframe.contentWindow.postMessage('{"event":"command","func":"' + param + '","args":""}', '*');
	},
	stopPlay() {
            for(let items=this.el.tfClass('player'),i=items.length-1;i>-1;--i){
                if(!items[i].classList.contains('current')){
                    let iframe = items[i].querySelector('iframe');
                    if (iframe) {
                        this.execute(iframe, 'pauseVideo');
                    }
                }
            }
	},
	startPlay() {
            const iframe = this.el.querySelector('.player.current ');
            iframe && this.execute(iframe, 'playVideo');
	},
	close(e) {
            this.el.tfOn('transitionend',e=>{
                this.el.getRootNode().host.remove();
                this.el=null;
            },{passive:true,once:true})
            .style.maxHeight=0;
            this.el.classList.add('tf_opacity');
	},
	mainTabs(e) {
            if (e.target.classList.contains('active')) {
                    return;
            }
            const currentType=e.target.dataset.type;
            for(let menu=e.target.parentNode.children,i=menu.length-1;i>-1;--i){
                let type=menu[i].dataset.type;
                this.el.classList.remove(type);
                menu[i].classList.toggle('active',type===currentType);
            }
            this.el.classList.add(currentType);
	}
    };
    
})(tb_app, Themify, window.top, document);