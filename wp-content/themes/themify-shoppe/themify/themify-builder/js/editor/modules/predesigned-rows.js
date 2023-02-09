((api,Themify, doc) => {
    'use strict';
    let allRows;
    const rows=new Map(),
        imgW = 500,
        imgH = 300,
        masonry=el=> {
            const p=el.parentNode;
            let rowGap = p.dataset.gap;
            if(!rowGap){
                const computed=getComputedStyle(p);
                rowGap=computed.getPropertyValue('grid-template-columns').trim().indexOf(' ')!==-1?parseInt(computed.getPropertyValue('grid-row-gap')):-1;
                p.dataset.gap=rowGap;
            }
            else{
                rowGap=parseInt(rowGap);
            }
            if(rowGap>0){
                const itemHeight = el.tfClass('predesigned_image')[0].getBoundingClientRect().height + el.tfClass('predesigned_title')[0].getBoundingClientRect().height;
                el.style.gridRowEnd = 'span ' + Math.ceil((itemHeight + rowGap) / rowGap);
            }
        },
        observer=new IntersectionObserver((entries, _self)=>{
            for (let i = entries.length - 1; i > -1; --i) {
                if (entries[i].isIntersecting === true) {
                    _self.unobserve(entries[i].target);
                    let el=entries[i].target,
                        p=el.parentNode,
                        thumb = new Image(imgW,imgH),
                        loader = doc.createElement('div'),
                        imgPlaceholder=el.tfTag('img')[0],
                        src=imgPlaceholder.dataset.src;
                        loader.className = 'tf_loader tf_abs_c';
                        thumb.decoding = 'async';
                        thumb.src = src;
                        thumb.title = thumb.alt = imgPlaceholder.alt;
                        el.appendChild(loader);
                        masonry(p);
                        thumb.decode()
                        .catch(()=>{})
                        .finally(() => {
                            imgPlaceholder.replaceWith(thumb);
                            loader.remove();
                            requestAnimationFrame(()=>{
                                masonry(p);
                            });
                        });
                }
            }
                    
        }, {
            threshold: .1
        });
    api.preDesignedRows = class {
        constructor(el) {
            el.removeAttribute('data-gap');
            this.el = el;
            if (!allRows) {
                Themify.fetch('', null, {
                    credentials: 'omit',
                    method: 'GET',
                    mode: 'cors',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }, themifyBuilder.paths.rows_index).then(data => {
                    allRows=data;
                    this.init();
                }, e => {
                    
                    api.Spinner.showLoader('error');
                    this.el.innerHTML = '<h3 class="tf_textc">' + ThemifyConstructor.label.rows_fetch_error + '</h3>';
                });
            } else {
                this.init();
            }
        }
        init() {
            
            const ext = doc.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') !== -1 ? 'webp' : 'png',
                placeholder = '//via.placeholder.com/' + imgW + 'x' + imgH + '.' + ext + '?text=',
                fr = doc.createDocumentFragment(),
                catFr=doc.createDocumentFragment(),
                filter = this.el.closest('.panel_tab').querySelector('.dropdown_wrap ul'),
                categories=[];

            for (let i = 0,len=allRows.length; i < len; ++i) {
                let row = allRows[i],
                    catHash = [];
                if (row.category) {
                    let cats = row.category.split(',');
                    for (let j = 0, len2 = cats.length; j < len2; ++j) {
                        if('' !== cats[j]){
                            if (categories.indexOf(cats[j])===-1) {
                                categories.push(cats[j]);
                            }
                            catHash.push('tb'+Themify.hash(cats[j]));
                        }
                    }
                }
                let item = doc.createElement('div'),
                    figure = doc.createElement('figure'),
                    title = doc.createElement('div'),
                    imgPlaceHolder = new Image(imgW,imgH),
                    add = doc.createElement('button');
                item.className = 'predesigned_row tf_w';
                if(catHash.length>0){
                    item.className+=' '+catHash.join(' ');
                }
                item.draggable = true;
                item.dataset.slug=row.slug;
                
                figure.className = 'predesigned_image tf_rel';
                title.className = 'predesigned_title';
                title.textContent = imgPlaceHolder.alt = imgPlaceHolder.title = row.title;
                imgPlaceHolder.loading = 'lazy';
                imgPlaceHolder.decoding = 'async';
                imgPlaceHolder.src = placeholder + encodeURI(row.title);
                if (row.thumbnail) {
                    imgPlaceHolder.dataset.src=row.thumbnail;
                    observer.observe(figure);
                }
                add.type = 'button';
                add.className = 'tf_plus_icon add_module_btn tb_disable_sorting tf_rel';
                add.dataset.type = 'predesigned';
                figure.append(imgPlaceHolder, add);
                item.append(figure, title);
                fr.appendChild(item);
            }
            categories.sort();
            for(let i=0,len=categories.length;i<len;++i){
                let cat = doc.createElement('li');
                cat.textContent = categories[i];
                cat.dataset.slug='tb'+Themify.hash(categories[i]);
                catFr.appendChild(cat);
            }
            this.el.appendChild(fr);
            filter.appendChild(catFr);
            
            Themify.imagesLoad(this.el).then(el => {
                filter.tfOn(Themify.click, e => {
                    this.filter(e);
                })
                .parentNode.classList.remove('tf_hidden');
                const loader=this.el.tfClass('tf_loader')[0];
                if(loader){
                    loader.remove();
                }
            });
        }
        filter(e) {
            e.preventDefault();
            e.stopPropagation();
            const el = e.target;
            if(!el.classList.contains('current')){
                const slug = el.dataset.slug,
                    parent = el.closest('.panel_tab'),
                    active = parent.tfClass('dropdown_label')[0],
                    cl = slug ? slug : 0;
                active.innerText = el.textContent;
                active.dataset.active = cl;
                if(e.isTrusted){
                    parent.closest('.panel_container').tfClass('panel_search')[0].value = '';
                }
                for (let navItems = el.parentNode.children, i = navItems.length - 1; i > -1; --i) {
                    navItems[i].classList.toggle('current', navItems[i] === el);
                }
                for (let r = parent.tfClass('predesigned_row'), i = r.length - 1; i > -1; --i) {
                    r[i].style.display = !cl || r[i].classList.contains(cl) ? '' : 'none';
                }
            }
        }
    };
    api.preDesignedRows.get = async slug=>{
         api.Spinner.showLoader();
            if (rows.has(slug)) {
                return rows.get(slug);
            }
            try {
                const data = await Themify.fetch('', null, {
                    credentials: 'omit'
                }, themifyBuilder.paths.row_template.replace('{SLUG}', slug));
                api.Helper.clearElementId(data);
                rows.set(slug,data);
                // Import GS
                if (JSON.stringify(data).indexOf(api.GS.key) !== -1) {
                    try {
                        const res = await Themify.fetch('', null, {
                                credentials: 'omit'
                            }, themifyBuilder.paths.row_template.replace('{SLUG}', slug + '-gs')),
                            convert = {};
                        for (let i in res) {
                            if (res[i].class !== undefined) {
                                convert[res[i].class] = res[i];
                            } else {
                                convert[i] = res[i];
                            }
                        }
                        
                        
                        return api.GS.setImport(convert, data);
                    } catch (err) {
                        return data;
                    };
                } else {
                    return data;
                }
            } 
            catch (err) {
                api.Spinner.showLoader('error');
                api.LiteLightBox.alert(ThemifyConstructor.label.row_fetch_error);
            }
    };
})(tb_app,Themify,document);