(( Themify, doc)=> {
    'use strict';
    const init= ()=>{
        const el = doc.tfId('wp-admin-bar-themify-page-options');
        if(el){
            el.tfOn('click',e=>{
                e.preventDefault();
                let spinner=doc.tfId('tf_page_options_spinner');
                if(!spinner){
                    spinner = doc.createElement( 'div' );
                    spinner.id='tf_page_options_spinner';
                    spinner.className='tf_loader tf_abs_c';
                    spinner.style.fontSize='55px';
                    spinner.style.position='fixed';
                    doc.body.appendChild(spinner);
                }
                Promise.all([
                    Themify.loadCss(Themify.url+'themify-metabox/css/page-options'),
                    Themify.loadJs(Themify.url+'themify-metabox/js/page-options-modal')
                ])
                .then(()=>{
                    Themify.trigger('tf_page_options_init',[el]).finally(()=>{
                        spinner.remove(); 
                    });
                })
                .catch(()=>{
                    spinner.remove(); 
                });
            },{once:true});
            setTimeout(()=>{
                Themify.on('tf_music_ajax_ready',init);
            });
        }
    };
    if (doc.readyState === 'complete') {
        init();
    } else {
        window.tfOn('load', init, {once:true, passive:true});
    }
})(Themify, document);
