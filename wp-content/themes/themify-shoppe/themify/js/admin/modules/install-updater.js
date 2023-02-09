let TF_Install_Updater;
(Themify =>{
    'use strict';
    TF_Install_Updater=async nonce=>{
        const fetch=async ()=>{
          const res=await Themify.fetch('', 'blob', {
                credentials: 'omit',
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/zip'
                }
            }, 'https://themify.me/files/themify-updater/themify-updater.zip'),
            data = new FormData();
            data.set('action','themify_activate_plugin');
            data.set('nonce',nonce);
            data.set('plugin','themify-updater');
            data.set('data',res);
            const html=await Themify.fetch(data,'html'),  //wp response html there is no way to disable it
            tmp=document.createElement('div');
            tmp.appendChild(html);
            for(let ch=tmp.children,i=ch.length-1;i>-1;--i){
                if(ch[i].nodeName!==Node.TEXT_NODE){
                    ch[i].remove();
                }
            }
            const response=JSON.parse(tmp.textContent);
            if(!response.success){
                const err=response.data || response;
                throw err;
            }
        };
        try{
            await fetch();
        }
        catch(e){
            try{
                await fetch();
            }
            catch(e){
                throw e;
            }
        }
    };
    
})( Themify);