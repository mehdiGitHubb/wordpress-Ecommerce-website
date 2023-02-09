/**
 * gallery module
 */
;
((Themify,doc)=>{
    'use strict';
    const style_url=ThemifyBuilderModuleJs.cssUrl+'gallery_styles/',
            loaded=new Set();
	let st=doc.tfId('tb_inline_styles');
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-gallery')){
            return;
        }
        const items = Themify.selectWithParent('module-gallery',el),
            masonry = [];
        for(let i=items.length-1;i>-1;--i){
            if(items[i].classList.contains('layout-showcase')){
                Themify.loadCss(style_url+'showcase','tb_gallery_showcase');
            }
            else if(items[i].classList.contains('layout-lightboxed')){
                Themify.loadCss(style_url+'lightboxed','tb_gallery_lightboxed');
            }
            else if(items[i].classList.contains('layout-grid')){
                Themify.loadCss(style_url+'grid','tb_gallery_grid');
                let isMasonry =items[i].tfClass('gallery-masonry')[0];
                if(isMasonry && !isMasonry.classList.contains('gallery-columns-1')){
					
					let styleText='',
						props=window.getComputedStyle(isMasonry),
						cols=props.getPropertyValue('--galN'),
						gutter=parseFloat(props.getPropertyValue('--galG')) || 1.5;
					if(!cols){
						let cl=isMasonry.classList;
						for(let j=cl.length-1;j>-1;--j){
							if(cl[j].indexOf('gallery-columns-')!==-1){
								cols=cl[j].replace('gallery-columns-','');
								break;
							}
						}
					}
					if(cols){
						cols=parseInt(cols);
						if(!loaded.has('block')){
							loaded.add('block');
							styleText='.gallery-masonry.masonry-done{display:block}.gallery-masonry.masonry-done>.gutter-sizer{width:'+gutter+'%}';
						}
						if(!loaded.has(cols)){
							loaded.add(cols);
							let size = parseFloat((100-((cols-1)*gutter))/cols).toFixed(2).replace('.00','');
							styleText+='.gallery-columns-'+cols+'.masonry-done .gallery-item{width:'+size+'%}';
						}
						if(styleText!==''){
							if(st===null){
								st=doc.createElement('style');
								st.textContent=styleText;
								doc.head.prepend(st);
							}
							else{
								st.innerText+=styleText;
							}
						}
						masonry.push(isMasonry);
					}
                }
            } 
        }
        if (masonry.length > 0) {
            Themify.isotop(masonry,{itemSelector: '.gallery-item',columnWidth:false});
        }
    })
    .body.on('click', '.module-gallery .pagenav a', function (e) {
        e.preventDefault();
        const wrap = this.closest('.module-gallery'),
                cl=wrap.classList;
        cl.add('builder_gallery_load');
        Themify.fetch('','html',{method:'GET'},this.getAttribute('href')).then(res=>{
                if (res) {
                    let id = wrap.className.match( /tb_?.[^\s]+/ );
                    id=null !== id && 'undefined' !== typeof id[0]?id[0]:'module-gallery';
                    wrap.innerHTML=res.querySelector('.'+id).innerHTML;
                    Themify.lazyLoading(wrap);
                }
         })
        .finally(()=>{
               cl.remove('builder_gallery_load');
        });
    })
    .on('click', '.layout-showcase.module-gallery a', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const showcase = this.closest('.gallery').tfClass('gallery-showcase-image')[0],
                titleBox = showcase.tfClass('gallery-showcase-title')[0],
                titleText=showcase.tfClass('gallery-showcase-title-text')[0],
                captionText=showcase.tfClass('gallery-showcase-caption')[0],
                mainImg=showcase.tfTag('img')[0],
                src=this.dataset.image,
                image = new Image();
			
		if(titleBox){
            const st=titleBox.style;
            if(!titleBox.innerText.trim()){
                st.opacity=0;
                st.visibility='hidden';
            }
            else{
                st.opacity=st.visibility='';
            }
		}
		showcase.classList.add('tf_lazy');
		image.decoding = 'async';
		image.alt=this.tfTag('img')[0].alt;
		if(titleText){
			titleText.innerHTML=this.title;
		}
		if(captionText){
			captionText.innerHTML=this.dataset.caption;
		}
		image.src= src;
        image.decode()
        .catch(()=>{})
        .finally(()=>{
            mainImg.replaceWith(image);
            showcase.classList.remove('tf_lazy');
        });
    });

})(Themify,document);
