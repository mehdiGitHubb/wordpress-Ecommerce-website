;((Themify,doc)=>{
	'use strict';
	const instance=[];
	let overLay=null;
    class sideMenu{
        constructor(el, options){
            this.element = el;
            const defaults = {
                panel: '#mobile-menu',
                close: '',
                side: 'right',
                hasOverlay:true,
                beforeShow:null,
                afterShow:null,
                beforeHide:null,
                afterHide:null
            },
            replacements = { '#':'', '\.':'', ' ':'-' };
            if(!options.panel && el.hasAttribute('href')){
                options.panel=el.getAttribute('href');
                if(!options.panel || options.panel==='#'){
                    options.panel=defaults.panel;
                }
            }
            this.settings = Object.assign( {}, defaults, options );
            this.panelVisible = false;
            this.panelCleanName = this.settings.panel.replace( /#|\.|\s/g, match=>{
                    return replacements[match]; 
                } 
            );
            this.init();
        }
        init() {
			if(overLay===null && this.settings.hasOverlay=== true){
				overLay = doc.createElement('div');
				overLay.className = 'body-overlay';
				overLay.tfOn('click', ()=>{
					for(let i=instance.length-1;i>-1;--i){
                        instance[i].hidePanel();
					}
				},{passive:true});
				doc.body.appendChild(overLay);
			}
			this.element.tfOn('click',e=>{
				e.preventDefault();
				if ( this.panelVisible ) {
					this.hidePanel();
				} else {
					this.showPanel();
					if(!(e.screenX && e.screenY)){
						const a = doc.querySelector(this.settings.panel+' a');
						if(null!==a){
							a.focus();
						}
					}
				}
			});
			if ( '' !== this.settings.close ) {
				const close = doc.querySelector(this.settings.close);
				if(close!==null){
					close.tfOn('click',e=>{
						e.preventDefault();
						this.hidePanel();
					});
				}
			}
		}
		showPanel() {
			if(this.panelVisible===false){
				Themify.trigger('tf_fixed_header_remove_revelaing');
				const panel=this.settings.panel,
					thisPanel = doc.querySelector(panel);
					thisPanel.style.display='block';
				setTimeout(()=>{
					if(this.settings.beforeShow){
						this.settings.beforeShow.call(this);
					}
					if(this.panelVisible===false){
						if(thisPanel!==null){
							thisPanel.tfOn('transitionend',()=>{
								if(this.settings.afterShow){
									this.settings.afterShow.call(this);
								}
								Themify.trigger('sidemenushow', [panel, this.settings.side,this]);
							},{passive:true,once:true})
                            .classList.remove('sidemenu-off');
							thisPanel.classList.add('sidemenu-on');
						}
						doc.body.classList.add(this.panelCleanName + '-visible','sidemenu-' + this.settings.side);
						if(overLay!==null){
							overLay.classList.add('body-overlay-on');
						}
						this.panelVisible = true;
					}
				},5);
			}
		}
		hidePanel( side ) {
			if(this.panelVisible===true){
				const thisPanel = doc.querySelector(this.settings.panel);
				if(this.settings.beforeHide){
					this.settings.beforeHide.call(this);
				}
				if(thisPanel!==null){
					thisPanel.tfOn('transitionend',function(){
						this.style.display='';
					},{passive:true,once:true})
                    .classList.remove('sidemenu-on');
					thisPanel.classList.add('sidemenu-off');
				}
				doc.body.classList.remove(this.panelCleanName + '-visible');
				if ( side !== this.settings.side ) {
					doc.body.classList.remove('sidemenu-' + this.settings.side);
				}
				if(this.settings.afterHide){
					this.settings.afterHide.call(this);
				}
				Themify.trigger('sidemenuhide.themify', [this.settings.panel]);
				if(overLay!==null){
					overLay.classList.remove('body-overlay-on');
				}
				this.panelVisible = false;
			}
		}
    }
	Themify.on('tf_sidemenu_init',(items,options)=>{
                if(items.length===undefined){
			items=[items];
		}
		for(let i=items.length-1;i>-1;--i){
			instance.push(new sideMenu( items[i], options ));
		}
	})
	.on('tf_side_menu_hide_all',()=>{
		for(let i=instance.length-1;i>-1;--i){
			instance[i].hidePanel();
		}
	});
    
	doc.body.classList.add('sidemenu-active');
	if(typeof themify_vars!=='undefined'){
            Themify.on('tfsmartresize',e=>{
                if (e && e.w>themify_vars.menu_point){
                        Themify.trigger('tf_side_menu_hide_all');
                }
            });
	}

})(Themify,document);