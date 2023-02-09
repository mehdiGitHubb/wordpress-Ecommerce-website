;((Themify,win,doc)=>{
    'use strict';
    /* loading spinner icon */
    const url=doc.currentScript.src.split('/js/')[0],
		run=()=>{
            const links = doc.querySelectorAll( 'a[href="#tb_builder_page"]' );
            for ( let i = links.length-1;i>-1; --i) {
                    links[ i ].tfOn( 'click',e => {   
                            e.preventDefault();
                            let root= doc.getElementById('tb_builder_page_root');
                            if(!root){
                                root=doc.createElement('div');
                                const el=doc.createElement('div'),
                                spinner = doc.createElement( 'div' ),
                                style = doc.createElement( 'style' );
                                style.innerText = '.spinner{margin:-20px 0 0 -20px;width:62px;height:62px;background-color:rgba(0,0,0,.6);border-radius:50%;box-sizing:border-box;position:fixed;top:50%;left:50%;z-index:100001;line-height:62px}.spinner:before{width:80%;height:80%;border:5px solid transparent;border-top-color:#fff;border-radius:50%;box-sizing:border-box;position:absolute;top:10%;left:10%;content:"";animation:circle-loader 1.4s infinite linear}@keyframes circle-loader{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}';
                                root.id='tb_builder_page_root';
                                el.style['display']='none';
                                el.className='lightbox';
                                spinner.className= 'spinner';
                                root.attachShadow({
                                    mode:'open'
                                }).append(style, el,spinner);
                                doc.body.appendChild( root);
                            }
                            const el=root.shadowRoot.querySelector('.lightbox');
                            Promise.all([
                                Themify.loadCss(Themify.url+'css/base.min','tf_base',null,el),
                                Themify.loadCss(url+'/css/editor/builder-page',null,null,el),
                                Themify.loadJs(url+'/js/editor/themify-builder-page',!!win.ThemifyBuilderPage)
                            ]).then(()=>{
                                ThemifyBuilderPage.run(el);
                            });
                    });
            }
			setTimeout(()=>{
				Themify.on('tf_music_ajax_ready',run);
			},2000);
		};
     if(win.loaded===true){
        run();
    }
    else{
        win.tfOn('load', run, {once:true, passive:true});
    }
} )(Themify,window,document);