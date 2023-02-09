/**
 * autoTiles module
 */
;
((Themify,win)=>{
    'use strict';
    Themify.on('tf_autotiles_init',(items,callback)=>{
        const reCalculate=(children,smaller)=>{
            let count=0;
            for(let j=children.length;j>-1;--j){
				let cl=children[j]?children[j].classList:null;
                if(cl && (cl.contains('post') || cl.contains('product'))){
                    ++count;
                    let w=children[j].offsetWidth,
						h=children[j].offsetHeight;
						if((w-10)<=smaller){
							if(w===h || (w>h && (w-h)<10) || (h>w && (h-w)<10)){
								cl.add('tiled-square-small');
							}
							else{
								cl.add('tiled-portrait');
							}
						}
						else{
							if(w>h){
								cl.add('tiled-landscape');
							}
							else{
								cl.add('tiled-square-large');
							}
						}
                    }
            }
            return count;
        },
        _init = (items,callback)=>{
            for (let i = 0, len = items.length; i < len; ++i) {
                if(null !== items[i].querySelector('.ptb_loops_wrapper')){
                    items[i].classList.remove('auto_tiles','tf_lazy');
                    continue;
                }
                let children = items[i].children,
                        length=children.length,
                        cl=items[i].classList;
                if(!cl.contains('tf_tiles_more')){
                    if(length===5 || length===6){
                        cl.add('tf_tiles_'+length);
                    }
                    else{
                        cl.add('tf_tiles_more');
                    }
                }
                let count=reCalculate(children,parseInt(win.getComputedStyle(items[i]).getPropertyValue('grid-auto-rows')));
                if(count>0){
                    if(length!==count){
                        if(!cl.contains('tf_tiles_more') || count<6){
                                for(var j=cl.length-1;j>-1;--j){
                                    if(cl[j].indexOf('tf_tiles_')===0){
                                        cl.remove(cl[j]);
                                    }
                                }
                                if(count===5 || count===6){
                                    cl.remove('tb_tiles_more');
                                    cl.add('tf_tiles_'+count);
                                }
                                else{
                                    cl.add('tf_tiles_more');
                                }
                        }
                        reCalculate();
                    }
                    const sub=items[i].previousElementSibling;
                    if (sub && sub.classList.contains('post-filter')) {
                        Themify.trigger('themify_isotop_filter',items[i].previousElementSibling);
                    }
                    cl.add('tf_auto_tiles_init');
                    if ( callback) {
                        callback(items[i]);
                    }
                    cl.remove('tf_lazy');
                }
            }
        };
        if(!Themify.cssLazy.has('tf_grid_auto_tiles')){
            for (let i =items.length-1;i>-1;--i) {
                items[i].classList.add('tf_lazy');
            }
        }
        Themify.loadCss('grids/auto_tiles','tf_grid_auto_tiles').then(()=>{
            _init(items, callback);
       });
    });

})(Themify,window);
