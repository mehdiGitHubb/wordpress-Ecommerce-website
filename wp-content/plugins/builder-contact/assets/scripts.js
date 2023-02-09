(($,Themify)=>{
    'use strict';
        const init = async el=>{
            const sendForm = async (form,token)=>{
                    const data = new FormData(form[0]);
                    data.set('action', 'builder_contact_send');
                    data.set( 'post_id', form.data( 'post-id' ) );
                    data.set( 'orig_id', form.data( 'orig-id' ) );
                    data.set( 'element_id', form.data( 'element-id' ) );
                    if (token) {
                        data.set('g-recaptcha-response', token);
                    }
                    try{
                        const resp=await Themify.fetch(data,'json'),
                            res=resp.data,
                            msg=form.find('.contact-message');
                        if ( resp.success ) {
                            msg.html( '<p class="ui light-green contact-success">' + res.msg + '</p>' ).fadeIn();
                            Themify.trigger( 'builder_contact_message_sent', [ form, res.msg ] );
                            if ( res.redirect_url !== '' ) {
                                if(res.nw){
                                    setTimeout(()=>{
                                        window.open(res.redirect_url, '_blank');
                                    },2000);
                                }
                                else{
                                    window.location = res.redirect_url;
                                }
                            }
                            form[0].reset();
                        } 
                        else {
                            msg.html( '<p class="ui red contact-error">' + res.error + '</p>' ).fadeIn();
                            Themify.trigger( 'builder_contact_message_failed', [ form, res.error ] );

                        }
                    }
                    catch(e){
                        
                    }
                    $('html').stop().animate({scrollTop: form.offset().top - 100}, 500, 'swing');
                    if ( typeof grecaptcha === 'object' && form.find( '.themify_captcha_field' ).data( 'ver' ) === 'v2' ) {
                        grecaptcha.reset();
                    }
                    form.removeClass('sending');
            },
            cp = el.tfClass('themify_captcha_field')[0];
            if (cp && typeof grecaptcha === 'undefined') {
                const key=cp.dataset.sitekey;
                if(key){
                    let url = 'https://www.google.com/recaptcha/api.js';
                    if( 'v3' === cp.dataset.ver){
                        url+='?render='+key;
                    }
                    await Themify.loadJs(url,null,false);
                }
            }
            if (!Themify.is_builder_active) {
                el.tfOn('submit',function(e){
                    e.preventDefault();
                    const form =$(this),
                        cl=this.classList,
                        cp = el.tfClass('themify_captcha_field')[0];
                    if (cl.contains('sending')) {
                        return false;
                    }
                    cl.add('sending');
                    form.find('.contact-message').fadeOut();
                    if( cp && 'v3' === cp.dataset.ver && typeof grecaptcha !== 'undefined'){
                        grecaptcha.ready(async()=>{
                            const token=await grecaptcha.execute(cp.dataset.sitekey, {action: 'captcha'});
                            sendForm(form,token);
                        });
                    }
                    else{
                        sendForm(form);
                    }
                });
            }
        };
        Themify.on('builder_load_module_partial', (el,isLazy)=>{
            if(isLazy===true && !el.classList.contains('module-contact')){
                return;
            }
            const forms = Themify.selectWithParent('builder-contact',el); 
            for(let i=forms.length-1;i>-1;--i){
                Themify.requestIdleCallback(()=>{
                    init(forms[i]);
                },300);
            }
        });
})(jQuery,Themify);
