/* Themify Theme Builder Active Module*/
;
( (Themify,doc,fwVars)=> {
    'use strict';
	const modules = ['contact','product-categories','image','optin','pro-slider','fancy-heading','products'],
		st_url=fwVars.theme_url+'/styles/modules/builder/',
		v=fwVars.theme_v,
		mainCss=doc.tfId('themify_concate-css'),
		init=()=>{
			for(let i=modules.length-1;i>-1;--i){
				if((modules[i]==='products' && doc.tfClass('wc-products')[0])|| (doc.tfClass('module-'+modules[i])[0])){
                                    if(modules[i]==='products' && doc.querySelector('.wc-products.list-thumb-image,.wc-products.grid2-thumb')===null ){
                                        continue;
                                    }
                                    Themify.loadCss(st_url+modules[i],null,v,mainCss);
                                    modules.splice(i,1);
				}
			}
		};
	Themify.on('builder_load_module_partial',init);
	init();
	
})(Themify,document,themify_vars);