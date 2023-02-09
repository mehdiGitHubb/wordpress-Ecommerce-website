let TB_Export;
((api, Themify,doc) => {
    'use strict';
    TB_Export={
        async init(){
            let json=api.Builder.get().toJSON(true),
                shortcodes={},
                ajaxPrms=Promise.resolve(),
                fields=['background_slider','shortcode_gallery'],
                findReplaceShortcode=(el,replace)=>{
                    const st = el.styling || el.mod_settings;
                    if(st){
                        for(let i=fields.length-1;i>-1;--i){
                            if(st[fields[i]] && st[fields[i]].indexOf('path=')===-1){
                                let k=Themify.hash(st[fields[i]]).toString();
                                if(replace){
                                    if(replace[k]===undefined){
                                        return;
                                    }
                                    let r=replace[k].split(' ids');
                                    for(let j=r.length-1;j>-1;--j){
                                        r[j]=r[j].trim();
                                        if(r[j][0]==='='){
                                            r.splice(j,1);
                                        }
                                    }
                                    r=r.join(' ');
                                    if(r.slice(-1)!==']'){
                                        r+=']';
                                    }
                                    st[fields[i]]=replace[k]=r;
                                }
                                else{
                                    shortcodes[k]=st[fields[i]];
                                }
                            }
                        }
                    }
                },
                recursive=(json,replace)=>{
                    if(json){
                        for(let i=json.length-1;i>-1;--i){
                            findReplaceShortcode(json[i],replace); 
                            if(json[i].cols){
                               for(let j=json[i].cols.length -1;j>-1;--j){
                                   findReplaceShortcode(json[i].cols[j],replace);
                                   recursive(json[i].cols[j].modules,replace);
                               }
                            }
                       }
                   }
                };
                if(window.navigator.onLine){
                    recursive(json);
                    if(Object.keys(shortcodes).length>0){
          //              TF_Notification.show('info',themifyBuilder.i18n.convertExportUrls);
                        ajaxPrms=await api.LocalFetch({
                            data:shortcodes,
                            action:'builder_prepare_export'
                        });
                    }
                }
                const res=await ajaxPrms;
                let customCss=api.Builder.get().customCss,
                    postTitle=themifyBuilder.post_title,
                    date=new Date(),
                    currentData=date.getFullYear()+'_'+date.getMonth()+'_'+date.getDate(),
                    zip = new JSZip(),
                    usedGS=api.GS.findUsedItems(json),
                    GS=null,
                    donwload=(blob,name)=>{
                        let a=doc.createElement('a');
                        a.download = name;
                        a.rel = 'noopener';
                        a.href = URL.createObjectURL(blob);
                        setTimeout( ()=> { 
                            URL.revokeObjectURL(a.href); 
                            a=null;
                        },7000); 
                        a.click();
                    };
                if(res){
                    recursive(json,res);
                }

                json={builder_data:json};
                if(customCss){
                    json.custom_css=customCss.trim();
                }
                if(usedGS){
                    GS={};
                    for(let i=usedGS.length-1;i>-1;--i){
                        let gsItem=api.Helper.cloneObject(api.GS.styles[usedGS[i]]);
                        delete gsItem.id;
                        delete gsItem.url;
                        GS[usedGS[i]]=gsItem;
                    }
                    zip.file('builder_gs_data_export.txt', JSON.stringify(GS));
                }
                zip.file('builder_data_export.txt', JSON.stringify(json));
                try{
                    const blob=await zip.generateAsync({type:'blob'}),
                        zipName=postTitle + '_themify_builder_export_' +currentData+ '.zip';
                    donwload(blob,zipName);
                }
                catch(e){
                    if(GS){
                        json.used_gs=GS;
                    }
                    json=JSON.stringify(json);
                    donwload(new Blob([json], {type: 'application/json'}),postTitle+'_themify_builder_export_'+currentData+'.txt');
                }
                api.Spinner.showLoader('done');
        }  
    };
    
})(tb_app, Themify, document);