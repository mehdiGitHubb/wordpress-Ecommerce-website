/**
 * option module
 */
;
(($,Themify)=>{
    'use strict';
    const _captcha = el =>{
        const sendForm =async form=>{
                const data = new FormData(form),
                    recaptcha_rsp = form.querySelector('[name="g-recaptcha-response"]');
                //data.append("action", "tb_optin_subscribe");
                if (recaptcha_rsp!==null) {
                    data.set('contact-recaptcha', recaptcha_rsp.value);
                }
                try{
                    const resp=await Themify.fetch(data);
                    if ( resp.success ) {
                        if ( form.dataset.success === 's1' ) {
                            window.location.href = resp.data.redirect;
                        } else {
                            $(form).fadeOut().closest( '.module' ).find( '.tb_optin_success_message' ).fadeIn();
                        }
                    } else {
                        console.log( resp.data.error );
                    }
                }
                catch(e){
                    
                }
                form.classList.remove( 'processing' );
                if ( typeof grecaptcha === 'object' && form.find( '.themify_captcha_field' ).data( 'ver' ) === 'v2' ) {
                    grecaptcha.reset();
                }
            },
            callback = el=>{
                if (!Themify.is_builder_active) {
                    el.tfOn('submit',function(e){
                        e.preventDefault();
                        const form = this;
                        if (form.classList.contains('processing')) {
                            return false;
                        }
                        form.className+=' processing';
                        const cp = el.tfClass('themify_captcha_field')[0];
                        if(cp && 'v3' === cp.dataset.ver && typeof grecaptcha !== 'undefined'){
                            grecaptcha.ready(()=> {
                                grecaptcha.execute(cp.dataset.sitekey, {action: 'captcha'}).then(token=> {
                                    const inp = document.createElement('input');
                                    inp.type='hidden';
                                    inp.name='g-recaptcha-response';
                                    inp.value=token;
                                    form.prepend(inp);
                                    sendForm(form);
                                });
                            });
                        }else{
                            sendForm(form);
                        }
                    });
                }
            },
            cp = el.tfClass('themify_captcha_field')[0];
        if (cp && typeof grecaptcha === 'undefined') {
            const key=cp.dataset.sitekey;
            if(key){
                let url = 'https://www.google.com/recaptcha/api';
                if( 'v3' === cp.dataset.ver){
                    url+='?render='+key;
                }
                Themify.loadJs(url,typeof grecaptcha !== 'undefined',false).then(()=>{
                    callback(el);
                });
            }
        }
        else {
            callback(el);
        }
    };
    Themify.on('builder_load_module_partial', (el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-optin')){
            return;
        }
        const forms = Themify.selectWithParent('tb_optin_form',el);
        if(forms[0]){
            Themify.requestIdleCallback(()=>{
                _captcha(forms[0]);
            },300);
        }
    });
})(jQuery,Themify);
