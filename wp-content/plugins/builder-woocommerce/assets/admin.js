((Themify, api,doc)=>{
    'use strict';
    const init=()=>{
        
        ThemifyConstructor.products_query = {
            render( data, self ) {
                            /* migration routine: convert old options to new */
                            if ( self.values.query_type ) {
                                    if ( self.values.query_type === 'tag' ) {
                                            self.values.query_type = 'product_tag';
                                            self.values.product_tag_terms = self.values.tag_products;
                                    } else if ( self.values.query_type === 'category' ) {
                                            self.values.query_type = 'product_cat';
                                            self.values.product_cat_terms = self.values.category_products;
                                    }
                            }

                            self.values.post_type = 'product';
                            let result = self.create( [ {
                                    type    : 'query_posts',
                                    id      : 'post_type',
                                    tax_id  : 'query_type',
                                    term_id : '#tmp_id#_terms'
                            } ] );

                            return result;
                    }
            };

            ThemifyConstructor.product_categories = {
                data:null,
                render(data,self){
                    data.optgroup=1;
                    const clone=Object.assign({},data);
                        clone.type='select';
                    const wr = self.select.render(clone,self),
                        select = wr.querySelector('select'),
                        val = self.values[data.id]!==undefined?self.values[data.id]:'0',
                        loader=doc.createElement('div'),
                        callback = ()=>{
                            const f = doc.createDocumentFragment();
                            for(let i=0,len=this.data.length;i<len;++i){
                                f.appendChild(this.data[i]);
                            }
                            select.lastChild.appendChild(f);
                            select.selectedIndex = 0;
                            const options=select.tfTag('option');
                            for(let i=options.length-1;i>-1;--i){
                                if(options[i].value===val){
                                    options[i].selected=true;
                                }
                            }
                        };
                        if(this.data===null){
                            loader.className='tf_loader tf_abs_c';
                            select.classList.add('tb_search_wait');
                            select.parentNode.appendChild(loader);
                            api.LocalFetch({
                                action: 'builder_wc_get_terms'
                            },'html')
                            .then(res=>{
                                this.data = Themify.convert(res.querySelector('select').children);
                                callback();
                            })
                            .catch(e=>{
                                api.Spinner.showLoader('error');
                            })
                            .finally(()=>{
                                loader.remove();
                                select.classList.remove('tb_search_wait'); 
                            });
                        }
                        else{
                           callback();
                        }
                        return wr;
                }
            };
    };
    
    Themify.on('themify_builder_ready',()=>{
        Themify.requestIdleCallback(()=>{
            window.top.Themify.loadCss( builderWc.css, null );
        },800);
    },true,api.is_builder_ready);
    
    api.jsModuleLoaded().then(init);
    
})(Themify, tb_app,document);