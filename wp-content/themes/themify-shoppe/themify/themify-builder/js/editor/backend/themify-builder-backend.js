((Themify,$, doc, api, builderData) => {
    'use strict';

    let hasGuttenberg = !!builderData.is_gutenberg_editor;
    if (doc.querySelector('#page-builder.themify_write_panel') !== null) {
        hasGuttenberg = false;
        doc.body.classList.remove('themify-gutenberg-editor');
    } 
    else if (!hasGuttenberg) {
        return;
    }
    const BackendBuilder={
        init(){
            const canvas = doc.tfId('tb_canvas_block');
            if (hasGuttenberg && !canvas) {
                Themify.on('tb_canvas_loaded', ()=>{this.init();}, true);
                return;
            }
            Themify.is_builder_active=true;
            Promise.all([this.render(),api.jsModuleLoaded()]).then(()=>{
                
                
                if(window.location.hash === '#builder_active'){
                    window.location.hash = '';
                    this.scrollToBuilder();
                }
                let editorPlaceholder = doc.tfClass('tb_wp_editor_holder')[0],
                    swBtn=doc.createElement('a');
                    swBtn.href='#';
                    swBtn.className='button tb_switch_frontend';
                    swBtn.textContent=builderData.i18n.switchToFrontendLabel;
                if (editorPlaceholder) {
                    swBtn = editorPlaceholder.tfTag('a')[0];
                } else {
                    const switchButtonWrap=doc.querySelector('#postdivrich #wp-content-media-buttons');
                    if(switchButtonWrap){
                        switchButtonWrap.appendChild(swBtn);
                    }
                }
                if(swBtn && swBtn.isConnected){
                    swBtn.tfOn(Themify.click, e => {
                        e.preventDefault();
                        Themify.triggerEvent(api.ToolBar.el.tfClass('switch')[0],e.type);
                    });
                }
                api.Registry.trigger(api.Builder.get(), 'tb_init');
                Themify.trigger('themify_builder_ready');
                Themify.on('tb_scroll_to_builder',()=>{
                    this.scrollToBuilder();
                });
                api.is_builder_ready = true;
            });
        },
        scrollToBuilder(){
            const builderTab=doc.tfId('page-buildert'),
                toolbar=api.ToolBar.el;
            if(builderTab!==null){
                builderTab.click();
            }
            if (hasGuttenberg) {
                api.Utils.scrollTo(toolbar.getRootNode().host);
            } else {
                let h=doc.tfId('wpadminbar');
                h=h?h.offsetHeight:0;
                Themify.scrollTo(toolbar.getBoundingClientRect().top - h);
            }
        },
        render(){
            const builderTpl=doc.tfId('tmpl-builder_admin_canvas_block').content;
            if (hasGuttenberg) {
                const canvas=doc.tfId('tb_canvas_block');
                canvas.innerHTML = '';
                canvas.appendChild(builderTpl);
            }
            else{
                doc.tfId('tb_builder_placeholder').replaceWith(builderTpl);
            }
            const data=api.Helper.correctBuilderData(builderData.builder_data);
            return api.FormTemplates.init().then(() => {
                const builderHolder = doc.tfId('tb_row_wrapper').firstElementChild;
                ThemifyStyles.init(api.FormTemplates.getItem(), api.breakpointsReverse, builderHolder.dataset.postid, themifyBuilder.gutters);

                new api.Builder(builderHolder, data, builderData.custom_css);
                
                this.initEvents();
            });
        },
        initEvents(){
			let isSaving=false;
            const saveButtons=doc.querySelectorAll('input#publish,input#save-post,button.editor-post-publish-button__button,.editor-post-save-draft'),
            _click=e=>{
				if(isSaving===false){
					isSaving=true;
					e.stopImmediatePropagation();
					if (!hasGuttenberg) {
						e.preventDefault();
					}
					const clicked=e.currentTarget;
					this.saveCallback(e.currentTarget).finally(()=>{
					  clicked.click();
					  clicked.tfOn(Themify.click,_click,{once:true});
						isSaving=false;
					});
				}
            };
            
            for(let i=saveButtons.length-1;i>-1;--i){
                saveButtons[i].tfOn(Themify.click,_click,{once:true});
            }
            
            Themify.on('tb_switch_frontend',link=>{this.switchFrontend( link );});
        },
        saveCallback(item){
            if (!hasGuttenberg) {
                item.classList.add('disabled');
            }
            else{
                item.setAttribute('aria-disabled','true');
            }
            return api.ToolBar.save().then(() => {
                // Clear undo history
                api.undoManager.reset();
                if (!hasGuttenberg) {
                    item.classList.remove('disabled');
                }
                else {
                    item.setAttribute('aria-disabled','false');
                }
            });
        },
        switchFrontend(link){
            let item,
                status =doc.tfId('original_post_status');
            if(status){
                status=status.value;
            }
            if ('publish' === status) {
                item=hasGuttenberg?doc.tfClass('editor-post-publish-button__button')[0]:doc.tfId('publish');
            }
            else {
                item=hasGuttenberg?doc.tfClass('editor-post-save-draft')[0]:doc.tfId('save-post');
            }
            if(item){
                this.saveCallback(item).then(()=>{
                    if(hasGuttenberg){
                        const publishBtn=doc.tfClass('editor-post-publish-button')[0];
                        if(publishBtn){
                            const observer = new MutationObserver((mutationsList, observer)=>{
                                const btn=mutationsList[0].target;
                                setTimeout(()=>{
                                    if(!btn.classList.contains('is-busy') && !doc.tfClass('editor-post-saved-state')[0]){
                                       window.location.href = link;
                                    }
                                },150);
                            });
                            observer.observe(publishBtn, {attributes: true});
                        }
                    }
                    else{
                        const page_builder = doc.tfId( 'page-builder' );
                        if ( page_builder && !doc.tfId('tb_switch_frontend')) {
                                let input = doc.createElement( 'input' );
                                input.type = 'hidden';
                                input.name = input.id ='tb_switch_frontend';
                                input.value = 'yes';
                                page_builder.appendChild( input );
                        }
                    }
                  Themify.triggerEvent(item,Themify.click);
                });

            }
        }
    };

    // Run on WINDOW load
    const windowLoad = () => {

        BackendBuilder.init();
        // WPML compat
        if (typeof window.icl_copy_from_original === 'function') {
            /**
             * Intercept copy_from_original request and handle Builder content
             */
            $.ajaxPrefilter((options, originalOptions) => {
                if (originalOptions['data'] && typeof originalOptions['data'] === 'string' && originalOptions['data'].includes('icl_ajx_action=copy_from_original')) {
                    const original_callback = options.success, // og success callback from WPML
                            params = new URLSearchParams(originalOptions['data']);
                    options.success = msg => {
                        /* move the Builder block out of the Gutenberg editor, protect it from being modified by WPML */
                        const tb_block = $('#tb_canvas_block');
                        if (tb_block.length) {
                            tb_block.hide().appendTo('body');
                        }

                        original_callback(msg);

                        if (tb_block.length) {
                            /* restore the Builder editor interface */
                            $('#editor [data-type="themify-builder/canvas"]').empty().append(tb_block.show());
                        }
                        /* get the content from original language 
                         * Retrieve Builder content from original language and injects
                         * the new content into the Builder editor.
                         */
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'themify_builder_icl_copy_from_original',
                                source_page_id: params.get('trid'),
                                source_page_lang: params.get('lang')
                            },
                            success(response) {
                                if (response != '-1') {
                                    let data;
                                    try {
                                        data = JSON.parse(response) || {};
                                    } catch (error) {
                                        data = {};
                                    }
                                    api.Builder.get().reLoad(data, builderData.post_ID);
                                }
                            }
                        });
                    };
                }
            });
        }
    };

    if (doc.readyState === 'complete') {
        windowLoad();
    } else {
        window.tfOn('load', windowLoad, {once: true, passive: true});
    }
})(Themify,jQuery, document, tb_app, themifyBuilder);
