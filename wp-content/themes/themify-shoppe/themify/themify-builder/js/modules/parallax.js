/**
 * builderParallax for row/column/subrow
 */
;
((Themify,win) => {
    'use strict';
    let prevBp,
            observer = null,
            isResizeInit = null,
            req,
			req2,
            timer;
    const className = 'builder-parallax-scrolling',
            speedFactor = .1,
            workingItems = new Map(),
            pendingItems = new Set(),
			isFixed=!!win.Notification,
            getcurrentBp = w => {
                const points = tbLocalScript.breakpoints;
                let bp = 'desktop';
                if (w <= points.mobile) {
                    bp = 'mobile';
                } else if (w <= points.tablet[1]) {
                    bp = 'tablet';
                } else if (w <= points.tablet_landscape[1]) {
                    bp = 'tablet_landscape';
                }
                return bp;
            },
			scroll = () => {
					if(req2){
						 cancelAnimationFrame(req2);
					}
					req2 = requestAnimationFrame(() => {
						for (let [el,obj] of workingItems) {
							if (obj && el && el.isConnected) {
								if ((obj.visible===true || isFixed===false ) && el.classList.contains(className)) {
									obj.update();
								}
							} 
							else if(obj){
								obj.destroy();
							}
						}
					});
			},
            resize = e => {
                if (e) {
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(() => {
                        if (req) {
                            cancelAnimationFrame(req);
                        }
                        req = requestAnimationFrame(() => {
                            const bp = getcurrentBp(e.w);
                            for (let [el,obj] of workingItems) {
                                if (obj && el && el.isConnected) {
                                    if (prevBp !== bp) {
                                        if (!obj.isInBreakpoint()) {
                                            pendingItems.add(obj);
                                            if (obj) {
                                                obj.destroy();
                                            }
                                            continue;
                                        }
                                        obj.getImage().catch(e => {
                                            if (obj) {
                                                obj.destroy();
                                            }
                                        });
                                    }
                                } else if (obj) {
                                    obj.destroy();
                                }
                            }
                            if (prevBp !== bp) {
                                prevBp = bp;
                                for (let obj of pendingItems) {
                                    if (obj.el.isConnected) {
                                        if (obj.isInBreakpoint()) {
                                            obj.init();
                                            pendingItems.delete(obj);
                                        }
                                    } else {
                                        pendingItems.delete(obj);
                                    }
                                }
                            }
                            req = timer = null;
                        });

                    }, 20);
                }
            };
    class Parallax {
        constructor(el) {
            if (!Themify.is_builder_active) {
                el.removeAttribute('data-parallax-bg');
            }
            else{
                const old = workingItems.get(el);
                if(old){
                    old.destroy();
                }
            }
            this.el = el;
            if (this.isInBreakpoint()) {
                this.init();
            } else {
                el.classList.remove(className);
                pendingItems.add(this);
            }
            if (isResizeInit === null) {
                isResizeInit = true;
                Themify.on('tfsmartresize', resize);
            }
        }
        init() {
            const el=this.el;
            this.pos=null;
            if (observer === null) {
                observer = new IntersectionObserver((entries, _self) => {
                    for (let i = entries.length - 1; i > -1; --i) {
                        let el=entries[i].target,
                            visible=entries[i].isIntersecting===true && el.classList.contains(className),
                            obj=workingItems.get(el);
                        if(visible===true && !obj.pos){
                            obj.setPos();
                            obj.update();
                        }
						obj.visible=visible;
                        
                    }
                }, {
                    rootMargin:'10px 0px 10px 0px'
                });
				win.tfOn('scroll', scroll, {passive: true});
            }
            this.getImage().then(() => {
                el.classList.add(className);
                workingItems.set(el,this);
				if(isFixed===true){
					observer.observe(el);
				}
            }).catch(e => {
            });
        }
        setPos(){
            this.pos=getComputedStyle(this.el).getPropertyValue('background-position-y') || '50%';
        }
        isInBreakpoint() {
            return getComputedStyle(this.el).getPropertyValue('--tbBg') === 'parallax';
        }
        getImage() {
            return new Promise((resolve, reject) => {
                let src = getComputedStyle(this.el).getPropertyValue('background-image');
                if (src && src !== 'none' && src !== 'initial' && src !== 'unset') {
                    src = src.replace(/(url\(|\)|")/g, '');
                    if (/\.(jpg|jpeg|png|webp|avif|gif|svg|apng)$/.test(src)) {
                        const image = new Image();
                        image.src = src;
                        image.decode()
                                .then(() => {
									if(!isFixed){
                                        this.setPos();
										const h=this.el.offsetHeight,
											max=h>1000?70:(h>800?60:50),
                                            size=100+parseInt(this.pos);
									// image is the exact height as the row, this will cause gap when backgroundPositionY changes; enlarge the image
										this.el.style.backgroundSize = 'auto calc('+size+'% + '+max+'px)';
                                        this.update();
									}
                                    else if(this.visible){
                                        this.setPos();
                                    }
                                    resolve();
                                })
                                .catch(e => {
                                    console.error('Parrallax(' + src + '): ' + e);
                                    reject();
                                });
                    }

                } else {
                    reject();
                }
            });
        }
        destroy() {
            const el=this.el;
            this.pos=null;
            workingItems.delete(el);
            el.classList.remove(className);
            el.style.backgroundSize = el.style.backgroundPositionY = '';
            observer.unobserve(el);
            if (workingItems.size === 0) {
				observer.disconnect();
				win.tfOff('scroll', scroll, {passive: true});
                if (pendingItems.size === 0) {
                    Themify.off('tfsmartresize', resize);
                    isResizeInit = null;
                }
				observer = null;
            }
        }
        update() {
            requestAnimationFrame(()=>{
                this.el.style.backgroundPositionY = 'calc('+this.pos+' + ' + ((this.el.getBoundingClientRect().top * speedFactor + .5) << 0) + 'px)';
            });
        }
    }
    prevBp = getcurrentBp(Themify.w);
    Themify.on('builder_load_module_partial', (el, isLazy) => {
        let items;
        if (isLazy === true) {
            if (!el.hasAttribute('data-parallax-bg')) {
                return;
            }
            items = [el];
        } else {
            items = Themify.selectWithParent('[data-parallax-bg]', el);
        }

        for (let i = items.length - 1; i > -1; --i) {
            new Parallax(items[i]);
        }
    });

})(Themify,window);
