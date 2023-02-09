/* Routines to manage font icons in theme settings and custom panel. */
;
let Themify_Icons;
((Themify,doc)=>{
	'use strict';
    let observer;
    const instanes=new Map(),
            _CLICK_=!Themify.isTouch?'click':(window.PointerEvent?'pointerdown':'touchstart'),
            cssUrl=document.currentScript.src.replace('script.','styles.').replace('.js','.css'),
            ns = 'http://www.w3.org/2000/svg',
            groupsCache=new Set(),
            initObserver=root=>{
                if(!observer){
                    observer=new IntersectionObserver((entries, _self)=>{
                        let spriteFr=doc.createDocumentFragment(),
                            root=Themify_Icons.el.getRootNode(),
                            svgSprite=root.querySelector('#svg'),
                            st=[];
                        for (let i = entries.length - 1; i > -1; --i) {
                            if (entries[i].isIntersecting === true) {
                                _self.unobserve(entries[i].target);
                                 let use = doc.createElementNS(ns, 'use'),
                                    svg = doc.createElementNS(ns, 'svg'),
                                    s = doc.createElementNS(ns, 'symbol'),
                                    p = doc.createElementNS(ns, 'path'),
                                    icon=entries[i].target,
                                    id=icon.dataset.icon,
                                    group=icon.closest('.group').id,
                                    cat=icon.closest('section').dataset.id,
                                    fullIcon='tf-'+instanes.get(group).getFullIcon(id,cat).replaceAll(' ','-'),
                                    w=icon.dataset.w,
                                    vw=icon.dataset.vw,
                                    vh=icon.dataset.vh,
                                    viewBox = '0 0 ';
                                    viewBox += vw!==undefined && vw!==''?vw:'32';
                                    viewBox +=' ';
                                    viewBox += vh!==undefined && vh!==''?vh:'32';

                                    s.id = fullIcon;
                                    s.setAttributeNS(null, 'viewBox', viewBox);
                                    p.setAttributeNS(null, 'd', icon.dataset.p);
                                    s.appendChild(p);
                                    spriteFr.appendChild(s);

                                    if (w) {
                                        st.push('.' + fullIcon + '{width:' + w + 'em}');
                                    }
                                    svg.setAttribute('class',fullIcon);
                                    use.setAttributeNS(null, 'href', '#' + fullIcon);
                                    svg.appendChild(use);
                                    icon.append(svg,doc.createTextNode(id));
                                    icon.removeAttribute('data-p');
                                    icon.removeAttribute('data-w');
                                    icon.removeAttribute('data-vw');
                            }
                        }
                        if (st.length > 0) {
                            let css = root.querySelector('#icon_style');
                            if (css === null) {
                                css = doc.createElement('style');
                                css.id = 'icon_style';
                            }
                            css.textContent += st.join('');
                            root.prepend(css);
                        }
                        if(svgSprite===null){
                            const defs=doc.createElementNS(ns, 'defs');
                            svgSprite=doc.createElementNS(ns, 'svg');
                            svgSprite.id='svg';
                            svgSprite.appendChild(defs);
                            root.prepend(svgSprite);
                        }
                        svgSprite.firstChild.appendChild(spriteFr);

                    }, {
                        threshold: .2,
                        root:root
                    });
                }
            };
        Themify_Icons = {
            el:null,
            input:null,
            init(){
                doc.tfOn(_CLICK_,e=>{
                    const target=e.target.closest('.themify_fa_toggle');
                    if(target){
                        e.preventDefault();
                        e.stopPropagation();
                        this.input=target.hasAttribute('data-target')?e.currentTarget.querySelector(target.getAttribute('data-target')):target.previousElementSibling;
                        this.show(this.input.value);
                    }
                });
            },
            renderForm(groupId){
                return new Promise((resolve,reject)=>{
                    if(this.el===null){
                        const titleWrap=doc.createElement('div'),
                            title=doc.createElement('h3'),
                            search=doc.createElement('input'),
                            clearSearch=doc.createElement('button'),
                            close=doc.createElement('button'),
                            menuWrap=doc.createElement('div'),
                            group=doc.createElement('div'),
                            container=doc.createElement('div'),
                            overlay=doc.createElement('div'),
                            navFr=doc.createDocumentFragment(),
                            tabFr=doc.createDocumentFragment(),
                            root=doc.createElement('div'),
                            prms=[];

                        this.el=doc.createElement('div');
                        this.el.className='lightbox tf_hide';
                        
                        titleWrap.className='top flex';
                        title.className='title';
                        title.textContent=tfIconPicker.title;
                        search.type='text';
                        search.className='search tf_box';
                        search.required=true;
                        search.setAttribute('inputmode','search');
                        search.setAttribute('pattern','.*\\S.*');
                        search.placeholder=tfIconPicker.search;
                        
                        close.className='tf_close';
                        clearSearch.type=close.type='button';
                        clearSearch.className='clear tf_close';
                        
                        menuWrap.className='menu_wrap flex tf_rel';
                        group.className='menu flex';
                        container.className='container tf_overflow tf_scrollbar';
                        overlay.className='overlay tf_abs_t tf_opacity tf_w tf_h tf_opacity tf_hide';
                        root.id='tf_icons_root';
                        root.style.display='none';
                        root.attachShadow({
                            mode:'open'
                        }).append( this.el,overlay);
                        initObserver(container);
                        for(let [id,item] of instanes){
                            let label=doc.createElement('label'),
                                input=doc.createElement('input'),
                                tab=doc.createElement('div');

                            input.type='radio';
                            input.name='icon-font-group';
                            input.value=id;
                            tab.className='group';
                            label.className='flex';
                            tab.id=id;
                            tab.style.display='none';
                            if(groupId===id || !groupId){
                                if(!groupId){
                                    groupId=true;
                                }
                                input.checked=true;
                                prms.push(this.getGroup(id));
                            }
                            label.append(input,doc.createTextNode(item.getTitle()));
                            navFr.appendChild(label);
                            tabFr.appendChild(tab);
                        }
                        group.appendChild(navFr);
                        container.appendChild(tabFr);
                        
                        container.tfOn(_CLICK_,e=>{
                            const item=e.target.closest('button');
                            if(item){
                                e.stopPropagation();
                                if(item.closest('.cat_menu')){
                                    this.clearSearch();
                                    this.filter(item);
                                }
                                else if(item.hasAttribute('data-icon')){
                                    this.setIcon(item.dataset.icon,item.closest('section').dataset.id,item.closest('.group').id);
                                    this.close();
                                }
                            }
                        },{passive:true});
                        
                        close.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.close();
                        },{passive:true});
                        
                        overlay.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.close();
                        },{passive:true});
                        
                        clearSearch.tfOn(_CLICK_,e=>{
                            e.stopPropagation();
                            this.clearSearch();
                        },{passive:true});
                        
                        group.tfOn('change',e=>{
                            e.stopPropagation();
                            this.getGroup(e.target.value);
                        },{passive:true});
                        
                        search.tfOn('input',e=>{
                            e.stopPropagation();
                            this.filter(this.el.querySelector('#'+this.el.querySelector('input:checked').value+' .cat_menu .selected'));
                            this.search(e.currentTarget.value);
                        },{passive:true});
                        
                        titleWrap.append(title,close);
                        menuWrap.append(group,search,clearSearch);
                        this.el.append(titleWrap,menuWrap,container);
                        
                        prms.push(Themify.loadCss(Themify.url+'css/base.min','tf_base-css',false,this.el));
                        prms.push(Themify.loadCss(cssUrl,null,false,this.el));
                        doc.body.appendChild(root);
                        Promise.all(prms).then(()=>{
                            root.style.display='';
                            resolve();
                        }).
                        catch(reject);
                    }
                    else{
                        if(groupId){
                            this.el.querySelector('[value="'+groupId+'"]').checked=true;
                            this.getGroup(groupId)
                            .then(resolve).
                            catch(reject);
                        }
                        else{
                            resolve();
                        }
                    }
                });
            },
            show(selected){
                const exist = this.el===null;
                let group;
                if(selected){
                    for(let [id,item] of instanes){
                        if(item.isValid(selected)){
                            group=id;
                            break;
                        }
                    }
                }
                
                if(exist){
                    this.showLoader();
                }
                return new Promise((resolve,reject)=>{
                    this.renderForm(group).then(()=>{
                        const overlay=this.el.getRootNode().querySelector('.overlay');
                        for(let items=this.el.tfClass('selected'),i=items.length-1;i>-1;--i){
                            items[i].classList.remove('selected');
                        }
                        overlay.classList.remove('tf_hide');
                        this.el.classList.remove('tf_hide');
                        requestAnimationFrame(()=>{
                            this.el.tfOn('transitionend',()=>{
                                if(selected ) {
                                    selected=this.el.querySelector('#'+group+' [data-icon="'+instanes.get(group).getSelectedIcon(selected)+'"]');
                                    let cat=selected.closest('.group').tfClass('cat_menu')[0],
                                        offset=10;
                                    if(cat){
                                        offset+=cat.getBoundingClientRect().height;
                                    }
                                    this.scrollTo(selected,offset);
                                    selected.classList.add('selected');
                                }
                                resolve();
                            },{passive:true,once:true})
                            .style.top=0;
                            overlay.classList.remove('tf_opacity');
                            if(exist){
                                this.showLoader('done');
                            }
                        });
                    }).
                    catch(reject);
                });
            },
            getGroup(group){
                let prms=Promise.resolve();
                if(!groupsCache.has(group)){
                    this.showLoader();
                    prms= Themify.fetch({ action : 'tf_icon_get_by_type',type:group}).then(res=>{
                        groupsCache.add(group);
                        this.el.querySelector('#'+group).appendChild( instanes.get(group).createList(res));
                    })
                    .finally(()=>{
                        this.showLoader('done');
                    });
                }
                prms.then(()=>{
                    const groups=this.el.tfClass('group');
                    for(let i=groups.length-1;i>-1;--i){
                        groups[i].style.display=group===groups[i].id?'':'none';
                    }
                });
                return prms;
            },
            close(){
                return new Promise(resolve=>{
                    const overlay=this.el.getRootNode().querySelector('.overlay');
                    this.el.tfOn('transitionend',function(){
                        this.classList.add('tf_hide');
                        this.input=null;
                        resolve();
                    },{passive:true,once:true});
                    overlay.tfOn('transitionend',function(){
                        this.classList.add('tf_hide');
                        this.classList.remove('tf_opacity');
                    },{passive:true,once:true})
                    .classList.add('tf_opacity');
                    this.el.style.top='';
                });
            },
            filter(cat){
                if(cat){
                    const id=cat.dataset.id,
                        isSelected=cat.classList.contains('selected'),
                        group=cat.closest('.group');
                    for(let cats=group.tfTag('section'),i=0,len=cats.length;i<len;++i){
                        let cl=cats[i].classList;
                        cats[i].tfOn('transitionend',function(){
                            this.classList.toggle('tf_hide',this.classList.contains('tf_opacity'));
                        },{passive:true,once:true});
                        if(isSelected || cats[i].dataset.id===id || !cl.contains('tf_opacity')){
                            cl.remove('tf_hide');
                            setTimeout(()=>{
                                cl.toggle('tf_opacity',!isSelected && cats[i].dataset.id!==id);
                            },10);
                        }
                    }
                    for(let items=cat.parentNode.children,i=items.length-1;i>-1;--i){
                        items[i].classList.toggle('selected',!isSelected && items[i]===cat);
                    }
                }
            },
            clearSearch(){
                this.el.tfClass('search')[0].value='';
                this.search('');
            },
            search(value){
                const s = value.trim(),
                    sections=this.el.tfTag('section');
                for(let i=0,len=sections.length;i<len;++i){
                    let btn=sections[i].tfTag('button'),
                        found=false;
                    for(let len=btn.length,j=0;j<len;++j){
                        if(btn[j].dataset.icon.indexOf(s)===0){
                            found=true;
                            btn[j].style.display='';
                        }
                        else{
                            btn[j].style.display='none';
                        }
                    }
                    sections[i].style.display=found===false?'none':'';
                }
            },
            showLoader(mode='show'){
                if(typeof tb_app !== 'undefined'){
                    return tb_app.Spinner.showLoader(mode);
                }
                let loader=doc.tfClass('tf_loader')[0];
                if(!loader){
                    this.setCss();
                    loader=doc.createElement('div');
                    loader.className='tf_loader tf_hide tf_abs_c';
                    doc.body.appendChild(loader);
                }
                if (mode === 'error') {
                    loader.classList.add('tf_loader_error');
                }
                else{
                    loader.classList.remove('tf_loader_error');
                    loader.classList.toggle('tf_hide',mode !== 'show');
                }
            },
            setCss(){
                let id='tf_select_icons_st',
                    st=doc.tfId(id);
                    if(!st){
                        st=doc.createElement('style');
                        st.id=id;
                        st.textContent='.tf_loader{width:62px;height:62px;background-color:rgba(0,0,0,.6);border-radius:50%;position:fixed;z-index:99999999;pointer-events:none;contain:strict}.tf_loader:before{border-color:transparent;border-top-color:#fff;border-width:5px}';
                        doc.body.appendChild(st);
                    }
            },
            setIcon(ic,cat,group,d) {
                if(!d){
                    d=doc;
                }
                let root=this.el.getRootNode(),
                    topSvg=d.getElementById('tf_svg'),
                    iconName=instanes.get(group).getFullIcon(ic,cat).trim(),
                    fullIcon='tf-'+iconName.replaceAll(' ','-'),
                    iframe=d.tfClass('tb_iframe')[0];
                    this.input.value=iconName;
                    if(!topSvg){
                        const defs=d.createElementNS(ns, 'defs');
                        topSvg=d.createElementNS(ns, 'svg');
                        topSvg.id='tf_svg';
                        topSvg.appendChild(defs);
                        d.head.appendChild(topSvg);
                    }
                    if(!topSvg.querySelector('#'+fullIcon)){
                        const symbol=root.querySelector('symbol#'+fullIcon).cloneNode(true),
                            st=root.querySelector('#icon_style');
                        if(st){
                            let cssText;
                            for(let rules=st.sheet.cssRules,i=rules.length-1;i>-1;--i){
                                if(rules[i].selectorText===('.'+fullIcon)){
                                   cssText='.tf_fa'+rules[i].cssText;
                                   break;
                                }
                            }
                            if(cssText){
                                let css=d.getElementById('tf_fonts_style');
                                if (css === null) {
                                    css = d.createElement('style');
                                    css.id = 'tf_fonts_style';
                                    topSvg.after(css);
                                }
                                if(!css.textContent || css.textContent.indexOf(fullIcon)===-1){
                                    css.textContent += cssText;
                                }
                            }
                        }
                        topSvg.firstChild.appendChild(symbol);
                    }
                    if(iframe && iframe.contentDocument){
                        this.setIcon(ic,cat,group,iframe.contentDocument);
                    }
                    Themify.triggerEvent(this.input,'change');
            },
            scrollTo(el){
                el.scrollIntoView();
                const srollBar=this.el.tfClass('tf_scrollbar')[0];
                srollBar.scrollTop-= parseInt(srollBar.offsetHeight/2);
            }
        }
        
    class Fonts{
        constructor(id){
            instanes.set(id,this);
        }
        clearSearch(el){
            const search=el.closest('.tb_tab').tfClass('tb_layout_search')[0];
            search.value = '';
            Themify.triggerEvent(search, 'input');
        }
        getTitle(){
            return tfIconPicker.group[this.id];
        }
        getList(type){
            if(!type){
                type=this.id;
            }
            return Themify.fetch({ action : 'tf_icon_get_by_type',type:type});
        }
        createList(data){
            const cats=data.cats,
                icons=data.icons,
                fr=doc.createDocumentFragment();
            if(icons && icons.EMPTY===undefined){
                let catLen=0;
                if(cats){
                    const keys=Object.keys(cats);
                    catLen=keys.length;
                    if(catLen>1){
                        const catWrap=doc.createElement('div'),
                        catFr=doc.createDocumentFragment();
                        for(let i=0,len=catLen;i<len;++i){
                            let cat=doc.createElement('button'),
                            span=doc.createElement('span');
                            cat.setAttribute('data-id',keys[i]);
                            cat.type='button';
                            span.textContent=cats[keys[i]];
                            cat.appendChild(span);
                            catFr.appendChild(cat);
                        }
                        catWrap.className='cat_menu flex';
                        catWrap.appendChild(catFr);
                        fr.appendChild(catWrap);
                    }
                }
                for(let c in icons){
                    let sec=doc.createElement('section'),
                        secTitle=doc.createElement('h2'),
                        group=doc.createElement('div'),
                        secF=doc.createDocumentFragment(),
                        sectionIcons=icons[c];
                    if(catLen>1){
                        sec.setAttribute('data-id',c);
                    }
                    group.className='icons';
                    secTitle.className='cat_title tf_textc';
                    secTitle.textContent=cats[c];
                    for(let k in sectionIcons){
                        let icon=sectionIcons[k],
                            btn=doc.createElement('button');
                            
                            btn.className='flex tf_overflow tf_box';
                            btn.type='button';
                            btn.setAttribute('data-icon',k);
                        if(icon.p){
                            btn.setAttribute('data-p',icon.p);
                            if(icon.vw!=='' && icon.vw!==undefined){
                                btn.setAttribute('data-vw',icon.vw);
                            }
                            if(icon.vh!=='' && icon.vh!==undefined){
                                btn.setAttribute('data-vh',icon.vh);
                            }
                            if(icon.w){
                                btn.setAttribute('data-w',icon.w);
                            }
                        }
                        else{
                            btn.setAttribute('data-p',icon);
                        }
                        observer.observe(btn);
                        secF.appendChild(btn);
                    }
                    group.appendChild(secF);
                    sec.append(secTitle,group);
                    fr.appendChild(sec);
                }
                
            }
            else if(icons){
                const tmp=doc.createElement('template');
                tmp.innerHTML='<div class="empty">'+icons.EMPTY+'</div>';
                fr.appendChild(tmp.content);
            }
            return fr;
        }
        isValid(ic){
            return ic.indexOf(this.id+'-')===0;
        }
        getSelectedIcon(ic){
            return ic.replace(this.id+'-','');
        }
        getFullIcon(ic,cat) {
            return this.id+'-'+ic;
        }
    }
    
    class Themify_Icon extends Fonts{
        constructor(){
            const id='ti';
            super(id);
            this.id=id;
        }
    }
    
    class FontAwesome extends Fonts{
        constructor(){
            const id='fa';
            super(id);
            this.id=id;
        }
        isValid(ic){
            const prefix=['fas ', 'far ', 'fab '];
            return ic.indexOf(this.id+'-')===0 || ic.indexOf(this.id+' ')===0 || prefix.indexOf(ic.substr(0,4))!==-1;
        }
        getSelectedIcon(ic){
            ic=ic.replace('fas ','').replace('far ','').replace('fab ','');
            if(ic.indexOf(this.id+' ')===0){
                return ic.replace(this.id+' ','');
            }
            return super.getSelectedIcon(ic);
        }
        getFullIcon(ic,cat) {
            return cat+' '+ic;
        }
    }
    
    
    class Fontello extends Fonts{
        constructor(){
            const id='fontello';
            super(id);
            this.id=id;
        }
        isValid(ic){
            return ic.substr(0,9) === 'fontello-' || ic.substr(0,5) === 'icon-' || ic.substr(0,12) === 'tf_fontello-';
        }
        getSelectedIcon(ic){
            if(ic.substr(0,9) === 'fontello-'){
                return ic.replace('fontello-','');
            }
            if(ic.substr(0,5) === 'icon-'){
                return ic.replace('icon-','');
            }
            if(ic.substr(0,12) === 'fontello-'){
                return ic.replace('tf_fontello-','');
            }
            return super.getSelectedIcon(ic);
        }
    }
    
    class LineAwesome extends Fonts{
        constructor(){
            const id='la';
            super(id);
            this.id=id;
        }
    }
    
    new Themify_Icon();
    new FontAwesome();
    new Fontello();
    new LineAwesome();
    Themify_Icons.init();
    
})(Themify,window.top.document);