/*Edge menu module*/
;
((Themify, doc)=>{
    const mouseEnter=function () {
			const target = this.tagName === 'A' ? this.parentNode : this,
                ul=target.tfTag('ul')[0],
				cl=target.classList;
				cl.remove('edge');
                cl.toggle('edge',(ul.getBoundingClientRect().right> Themify.w));
        },
        init=menu=>{
            if(menu===null || menu.dataset.edge){
                return;
            }
            menu.dataset.edge=true;
            const items=menu.tfTag('li');
            for(let i=items.length-1;i>-1;--i){
                if(items[i].tfTag('ul')[0]){
                    items[i].tfOn('mouseenter',mouseEnter,{passive:true});
                    /* tab keyboard menu nav */
                    let link = items[i].firstChild;
                    if('A'===link.tagName){
                        link.tfOn('focus',mouseEnter,{passive:true});
                    }
                }
            }
        };
    Themify.on('tf_edge_init',el=> {
        if(el===undefined){
            init(doc.tfId('main-nav'));
            init(doc.tfId('footer-nav'));
        }else{
            init(el);
        }
    });
})(Themify,document);