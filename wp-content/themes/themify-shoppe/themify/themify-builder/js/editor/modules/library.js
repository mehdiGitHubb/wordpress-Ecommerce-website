((api, doc) => {
    'use strict';
    const rows = new Map();
    api.Library = class {
        constructor(el) {
            this.el = el;
            if (!api.Library.items) {
                const ajaxData={
                    action:'tb_get_library_items',
                    part:'all'
                };
                api.LocalFetch(ajaxData).then(res => {
                    api.Library.items = res;
                    this.init();

                }).catch(e => {
                    api.Spinner.showLoader('error');
                    this.el.innerHTML = '<h3>Failed to load Library Items.</h3>';
                });
            } else {
                this.init();
            }
        }
        init() {
            const loader=this.el.tfClass('tf_loader')[0];
            this.el.tfOn(Themify.click, this.remove.bind(this));
            if(loader){
                loader.remove();
            }
            this.el.appendChild(api.Library.create(api.Library.items));
            Themify.triggerEvent(this.el.previousElementSibling.tfClass('current')[0],Themify.click);
        }
        remove(e) {
            if (e.target.closest('.remove_item_btn')) {
                e.preventDefault();
                e.stopPropagation();
                let elem = e.target.closest('.library_item'),
                    type = elem.dataset.type;
                api.LiteLightBox.confirm({msg:themifyBuilder.i18n[type + 'LibraryDeleteConfirm']}).then(answer => {
                    if (answer === 'yes') {
                        const id = elem.dataset.slug.toString(),
                        ajaxData={
                            action:'tb_remove_library_item',
                            id:id
                        };

                        api.Spinner.showLoader();
                        api.LocalFetch(ajaxData, 'text').then(slug => {
                            api.Spinner.showLoader('done');
                            if (slug) {
                                const panels = [api.MainPanel.el, api.SmallPanel.el];
                                for (let i = panels.length - 1; i > -1; --i) {
                                    let item=panels[i].querySelector('.tb_item_' + type + '[data-slug="' + id + '"]'),
                                        selectedTab = panels[i].querySelector('.library_tab .current');
                                    if(item){
                                        item.remove();
                                        if(selectedTab){
                                            Themify.triggerEvent(selectedTab,e.type);
                                        }
                                    }
                                }
                                if (type === 'part') {
                                    const builders=doc.tfClass('themify_builder_content-' + id),
                                    control = ThemifyConstructor.layoutPart.data;
                                    for(let i=builders.length-1;i>-1;--i){
                                        let builder=builders[i].closest('.active_module');
                                        if(builder){
                                           builder.remove(); 
                                        }
                                        else{
                                            builders[i].remove(); 
                                        }
                                    }
                                    for (let i = control.length - 1; i > -1; --i) {
                                        if (control[i].id.toString() === id) {
                                            ThemifyConstructor.layoutPart.data.splice(i, 1);
                                            break;
                                        }
                                    }
                                }
                            } else {
                                api.Spinner.showLoader('error');
                            }
                        }).
                        catch(e => {
                            api.Spinner.showLoader('error');
                        });
                    }
                });
            }
        }
    };
    
    api.Library.create=items=>{
        const fr = doc.createDocumentFragment(),
            noContent = doc.createElement('span');
        noContent.className = 'tb_no_content tf_hide';
        noContent.textContent = 'No library content found.';
        fr.appendChild(noContent);
        for (let i = 0, len = items.length; i < len; ++i) {
            let type = 'part',
                item = doc.createElement('div'),
                remove = doc.createElement('button');

            if (items[i].post_type.indexOf('_rows', 5) !== -1) {
                type = 'row';
            } else if (items[i].post_type.indexOf('_module', 5) !== -1) {
                type = 'module';
            }
            item.className = 'library_item tf_rel tf_box tb_item_' + type;
            item.draggable = true;
            item.dataset.type=type;
            item.dataset.slug=items[i].id;
            item.textContent = items[i].post_title;
            remove.type='button';
            remove.className = 'add_module_btn remove_item_btn tb_disable_sorting tf_close';
            item.appendChild(remove);
            fr.appendChild(item);
        }

        return fr;
    };
    api.Library.get = async (id, type)=> {
        if (rows.has(id)) {
            return rows.get(id);
        } 
        else {
            const ajaxData={
                action:'tb_get_library_item',
                type: type,
                id:id
            };
            api.Spinner.showLoader();
            return api.LocalFetch(ajaxData).then(data => {
                if (data.content.gs) {
                    api.GS.styles = ThemifyStyles.extend(true, {}, data.content.gs, api.GS.styles);
                    delete data.content.gs;
                }
                if (data.status === 'success') {
                    api.Spinner.showLoader('done');
                    rows.set(id,data.content);
                    return data.content;
                } else {
                    api.Spinner.showLoader('error');
                }
            }).catch(() => {
                api.Spinner.showLoader('error');
            });
        }
    };
})(tb_app, document);