/*code module*/
;
((Themify,doc,win) => {
    'use strict';
    win.Prism = win.Prism || {};
    win.Prism.manual = true;
    const cdnUrl='https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/',
        timer=Themify.is_builder_active?600:1000,
        importCss= (theme,hasLines,hasHighlight)=>{
            const pr=[];
            if(hasLines){
                pr.push(Themify.loadCss(cdnUrl+'plugins/line-numbers/prism-line-numbers.min.css','prism-line-numbers',false));
            }
            if(hasHighlight){
                pr.push(Themify.loadCss(cdnUrl+'plugins/line-highlight/prism-line-highlight.min.css','prism-line-highlight',false,));
            }
            if(theme){
                pr.push(Themify.loadCss(ThemifyBuilderModuleJs.cssUrl+'prism-themes/'+theme+'.min',theme));
            }
            return Promise.all(pr);
        },
        copyCode=async e=>{
            const el=e.currentTarget,
                text=el.closest('.module').tfTag('code')[0].textContent,
            success=()=>{
                el.classList.add('tb_copy_done');
                setTimeout(()=>{
                    el.classList.remove('tb_copy_done');
                },3000);
            },
            error=()=>{
                setTimeout(()=>{
                    alert('Can`t copy Press Ctrl+C to copy');
                },6);
            };
            if(el.dataset.working){
                return;
            }
            el.dataset.working=1;
            try{
                if (navigator.clipboard) {
                    await navigator.clipboard.writeText(text);
                    success();
                } 
                else {
                    throw '';
                }
            }
            catch(e){
                const area = doc.createElement('textarea');
                area.value = text;

                // Avoid scrolling to bottom
                area.style.top = area.style.left = '0';
                area.style.position = 'fixed';

                doc.body.appendChild(area);
                area.focus();
                area.select();

                try {
                    const successful = doc.execCommand('copy');
                    setTimeout(() =>{
                        successful?success():error();
                    }, 1);
                } 
                catch (e) {
                    error();
                }
                area.remove();
            }
            el.dataset.working='';
        },
        init =async item=> {
            const pr=[],
            code=item.tfTag('code')[0],
            hasLines=code.classList.contains('line-numbers'),
            hasHighlight=code.parentNode.dataset.line,
            modules=await Promise.all([
                Themify.loadJs(cdnUrl+'components/prism-core.min.js',!!win.Prism.languages,false),
                importCss(item.dataset.theme,hasLines,hasHighlight)
            ]);
            await import(cdnUrl+'plugins/autoloader/prism-autoloader.min.js');
            Prism.plugins.autoloader.languages_path = cdnUrl+'components/';
            
            pr.push(import(cdnUrl+'plugins/normalize-whitespace/prism-normalize-whitespace.min.js'));
            
            if(hasLines){
                pr.push(import(cdnUrl+'plugins/line-numbers/prism-line-numbers.min.js'));
            }
            if(hasHighlight){
                pr.push(import(cdnUrl+'plugins/line-highlight/prism-line-highlight.min.js'));
            }
            await Promise.all(pr);
            setTimeout(()=>{
                requestAnimationFrame(()=>{
                    Prism.highlightElement(code);
                    const copy=item.tfClass('tb_code_copy')[0],
                        clickEv=!Themify.isTouch?'click':'pointerdown';
                    if(copy){
                        copy.tfOn(clickEv,copyCode,{passive:true});
                    }
                });
            },timer);
        };
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-code')){
           return;
        }
        const items = Themify.selectWithParent('module-code',el);
        for(let i=items.length-1;i>-1;--i){
            init(items[i]);
        }
    });
})(Themify,document,window);
