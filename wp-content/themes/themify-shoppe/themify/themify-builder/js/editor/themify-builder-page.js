let ThemifyBuilderPage;
( ( Themify,doc,vars ) => {
    'use strict';
    const imgW = 300,
        imgH = 348,
        observer=new IntersectionObserver((entries, _self)=>{
            for (let i = entries.length - 1; i > -1; --i) {
                if (entries[i].isIntersecting === true) {
                    _self.unobserve(entries[i].target);
                    let el=entries[i].target,
                        thumb = new Image(imgW,imgH),
                        loader = doc.createElement('div'),
                        imgPlaceholder=el.tfTag('img')[0],
                        src=imgPlaceholder.dataset.src;
                        loader.className = 'tf_loader tf_abs_c';
                        thumb.decoding = 'async';
                        thumb.src = src;
                        thumb.title = thumb.alt = imgPlaceholder.alt;
                        el.appendChild(loader);
                        thumb.decode()
                        .catch(()=>{})
                        .finally(() => {
                            imgPlaceholder.replaceWith(thumb);
                            loader.remove();
                        });
                }
            }
                    
        }, {
            threshold: .1
        }); 
	ThemifyBuilderPage = {
		el : null,
		layoutsList() {
            return Themify.fetch('', null, {
                credentials: 'omit',
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json'
                }
            }, vars.paths.predesigned.url);
		},
		publish( formData ) {
            formData.set('nonce',vars.nonce);
            formData.set('action','tb_builder_page_publish');
            return Themify.fetch(formData)
            .then(res=>{
                if ( res.success ) {
                    window.location = res.data;
                } else {
                    throw res.error;
                }
            });
		},
        getIcon(ic){
            const ns = 'http://www.w3.org/2000/svg',
                use = doc.createElementNS(ns, 'use'),
                svg = doc.createElementNS(ns, 'svg');
                
            ic = 'tf-' + ic.trim().replace(' ', '-');
            svg.setAttribute('class', 'tf_fa ' + ic);
            use.setAttributeNS(null, 'href', '#' + ic);
            svg.appendChild(use);
            return svg;  
        },
		renderLayouts(data) {
            const webp=doc.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') !== -1,
                ext = webp ? 'webp' : 'png',
                placeholder = '//via.placeholder.com/' + imgW + 'x' + imgH + '.' + ext + '?text=',
                categories=new Set(),
                previewText=vars.i18n.preview,
                icon = this.getIcon('ti-search'),
                fr=doc.createDocumentFragment(),
                catFr=doc.createDocumentFragment(),
                all=doc.createElement('li');
                all.textContent = vars.i18n.all;
                catFr.appendChild(all);
                data.unshift({slug:'blank',title:vars.i18n.blank});
            for (let i = 0, len = data.length; i < len; ++i) {
                let li = doc.createElement('li'),
                    thumbnail = doc.createElement('div'),
                    title = doc.createElement('div'),
                    imgPlaceHolder = new Image(imgW,imgH);
                if(data[i].category){
                    li.dataset.category = data[i].category;
                }
                li.dataset.slug = data[i].slug;
                thumbnail.className = 'thumb tf_rel';

                imgPlaceHolder.loading = 'lazy';
                imgPlaceHolder.decoding = 'async';
                imgPlaceHolder.src = data[i].slug==='blank'?(Themify.builder_url+'img/blank-layout.'+ext):(placeholder + encodeURI(data[i].title));
                imgPlaceHolder.alt = imgPlaceHolder.title = data[i].title;

                if (data[i].thumbnail) {
                    imgPlaceHolder.dataset.src=data[i].thumbnail;
                    observer.observe(thumbnail);
                }
                title.className = 'title';
                title.textContent = data[i].title;
                if (undefined !== data[i].url) {
                    let a = doc.createElement('a');
                    a.className = 'link tf_box';
                    a.href = data[i].url;
                    a.target = '_blank';
                    a.title = previewText;
                    a.appendChild(icon.cloneNode(true));
                    thumbnail.appendChild(a);
                }
                thumbnail.appendChild(imgPlaceHolder);
                li.append(thumbnail, title);
                fr.appendChild(li);
                if (data[i].category) {
                    let cats = data[i].category.toString().split(',');
                    for (let j = 0, len2 = cats.length; j < len2; ++j) {
                        if ('' !== cats[j] && !categories.has(cats[j])) {
                            let category = doc.createElement('li');
                            category.textContent = cats[j];
                            catFr.appendChild(category);
                            categories.add(cats[j]);
                        }
                    }
                }
            }
            return {items:fr,cats:catFr};
		},
		dropDown() {
            return Themify.fetch({action:'tb_builder_page_dropdown',nonce:vars.nonce},'html');
		},
        render(layouts,dropdown){
            
            const form=doc.createElement('form'),
                top=doc.createElement('div'),
                submit=doc.createElement('button'),
                close=doc.createElement('button'),
                closeText=doc.createElement('span'),
                header=doc.createElement('div'),
                title=doc.createElement('input'),
                parent=doc.createElement('div'),
                sticky=doc.createElement('div'),
                category=doc.createElement('div'),
                filterText=doc.createElement('button'),
                filter=doc.createElement('ul'),
                search=doc.createElement('input'),
                wrapper=doc.createElement('div'),
                content=doc.createElement('ul'),
                layoutData=this.renderLayouts(layouts),
                CLICK=Themify.click;
            form.method='POST';
            form.className='tf_h';
            top.className='top';
            wrapper.className='wrapper tf_scrollbar tf_overflow tf_box';
            submit.type='submit';
            filterText.type=close.type='button';  
            submit.className='submit';
            close.className='tf_close';
            search.type=title.type='text';
            header.className='header';
            title.className='post_title';
            title.name='post_title';
            title.required=true;
            parent.className='parent';
            sticky.className='sticky';
            category.className='category tf_rel';
            filterText.className='filter_label tf_rel';
            filter.className='filter tf_scrollbar tf_abs_t tf_overflow tf_opacity tf_box';
            search.className='search';
            content.className='content';
            closeText.textContent=vars.i18n.cancel;
            submit.textContent=vars.i18n.publish;
            filterText.textContent=vars.i18n.all;
            title.placeholder=vars.i18n.title;
            search.placeholder=vars.i18n.search;
            
            close.appendChild(closeText);
            
            form.tfOn('submit',e=>{
                e.preventDefault();
                this.submit(e.currentTarget);
            });
            search.tfOn('input',e=>{
                this.filter(filter.firstElementChild);
                this.search(e.currentTarget.value);
            },{passive:true});
            
            filter.tfOn(CLICK,e=>{
                if (e.target.tagName === 'LI') {
                    this.clearSearch(e.target);
                    this.filter(e.target);
                }
            },{passive:true});
            
            close.tfOn(CLICK,e=>{
                this.close();
            },{passive:true});
            
            content.tfOn(CLICK,e=>{
                const el=e.target.closest('li');
                if (el) {
                    this.select(el);
                }
            },{passive:true});

            top.append(close,submit);
            
            parent.appendChild(dropdown);
            header.append(title,parent);
            filter.appendChild(layoutData.cats);
            category.append(filterText,filter);
            sticky.append(category,search);
            
            content.appendChild(layoutData.items);
            wrapper.append(header,sticky,content);
            form.append(top,wrapper);
            this.el.classList.add('tf_abs_c','tf_overflow','tf_box');
            this.el.appendChild(form);
            setTimeout(()=>{
                title.focus();
            },30);
        },
        submit(form){
            const formData=new FormData(form),
                post_title=formData.get('post_title'),
                parent = formData.get('parent');
            if ( !post_title) {
                return;
            }
            let slug=this.el.tfClass( 'selected' )[0];
            if(slug){
                slug=slug.dataset.slug;
            }
            this.showLoader();
            if ( !slug || slug==='blank') {
                this.publish( formData ).finally(()=>{
                    this.showLoader('hide');
                    this.close();
                });
            } 
            else {
                const file = vars.paths.predesigned.single.replace('{SLUG}', slug);
                Themify.fetch('', null, {
                    credentials: 'omit',
                    method: 'GET',
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }, file).then(res=>{
					const data = { builder_data :res },
                        __callback=res=>{
                            formData.set('layout',JSON.stringify(res));
                            this.publish( formData ).finally(()=>{
                                this.showLoader('hide');
                                this.close();
                            });
                        };
                    if ( JSON.stringify( res ).indexOf( 'global_styles' ) !== -1 ) {
                        Themify.fetch('', null, {
                            credentials: 'omit',
                            method: 'GET',
                            mode: 'cors',
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        }, vars.paths.predesigned.single.replace('{SLUG}', slug + '-gs')).then(res => {
                            data['used_gs']=res;
                        })
                        .finally(() => {
                            __callback(data);
                        });
                    } 
                    else {
                        __callback(data);
                    }
                })
                .catch(() => {
                    this.showLoader('hide');
                    alert(vars.i18n.layout_error.replace('{FILE}', file));
                });
            }
        },
        showLoader(mode='show'){
            this.el.getRootNode().querySelector('.spinner').style.display = mode==='show'?'':'none';
        },
		show() {
            this.el.style.setProperty('transform','translate(-50%, 100%)');
            this.el.style.display = '';
            setTimeout(()=>{
                this.el.style.setProperty('transform','');
            },20);
		},
		close() {
                this.el.tfOn('transitionend',function(){
                    this.style.display = 'none';
                },{passive:true,once:true})
                .style.setProperty('transform','translate(-50%, 100%)');
		},
        filter(el){
            if (!el.classList.contains('current')) {
                const list = this.el.tfClass('content')[0].children,
                    showAll = el.classList.contains('all'),
                    menu=el.parentNode,
                    text = el.textContent,
                    nav = menu.children;
                for (let i = list.length - 1; i > -1; --i) {
                    let show = showAll;
                    if (!show) {
                        let cat = list[i].getAttribute('data-category');
                        if (cat) {
                            show = text === cat || cat.indexOf(text) !== -1;
                        }
                    }
                    list[i].style['display'] = show ? '' : 'none';
                }
                for (let i = nav.length - 1; i > -1; --i) {
                    nav[i].classList.toggle('current', nav[i] === el);
                }
                menu.parentNode.tfClass('filter_label')[0].textContent = text;
            }
        },
        search(s){
            s=s.trim().toUpperCase();
            const list = this.el.tfClass('title');
            for (let i = list.length - 1; i > -1; --i) {
                list[i].parentNode.style['display'] = (s === '' || list[i].textContent.toUpperCase().indexOf(s)===0) ? '' : 'none';
            }  
        },
        clearSearch(){
            const search=this.el.tfClass('search')[0];
            search.value = '';
            Themify.triggerEvent(search, 'input');
        },
        select(el){
            const selected=this.el.tfClass('selected')[0];
            if(selected){
                selected.classList.remove('selected');
            }
            el.classList.add('selected');
        },
		run(el) {
            if(!this.el){
                this.el=el;
                return Promise.all([this.layoutsList(),this.dropDown(),Themify.fonts('ti-search')]).then(res=>{
                    const root=this.el.getRootNode();
                    root.prepend(root.host.ownerDocument.getElementById('tf_svg').cloneNode(true));
                    this.render(res[0],res[1]); 
                    this.showLoader('hide');
                    this.show();
                });
            }
            else if(this.el.style.display){
                this.show();
                return Promise.resolve();
            }
		}

	};
    

} )( Themify,document,tbBuilderPage );