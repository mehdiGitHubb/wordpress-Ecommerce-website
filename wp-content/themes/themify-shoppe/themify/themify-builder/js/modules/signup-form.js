/**
 * signup module
 */
;
(($,Themify)=>{
    'use strict';
    
    Themify.body.on( 'submit.tb_signup', '.tb_signup_form', function ( e ) {
        e.preventDefault();
        const $this = $( this ),
            $btn = $this.find('button'),
            ajaxData={
                nonce: $this.find('input[name="nonce"]').val(),
                action:'tb_signup_process',
                data:$this.serialize()
            };
        $btn.prop('disabled', true);
                $this.find('.tb_signup_errors').removeClass('tb_signup_errors').empty();
                $this.find('.tb_signup_success').hide();
        Themify.fetch(ajaxData).then(resp=>{
                if (resp.err ) {
                const errWrapper = this.tfClass('tb_signup_messages')[0];
                        errWrapper.classList.add('tb_signup_errors');
                    for(let i = resp.err.length-1;i>-1;--i){
                        let err = document.createElement('div');
                        err.innerText = resp.err[i];
                        errWrapper.appendChild(err);
                    }
                } else {
                    $this.find('.tb_signup_success').fadeIn();
                    const redirect = $this.find('input[name="redirect"]');
                    if(redirect[0]){
                        const url = redirect.val(),
                            loc=window.location;
                        if(''!== url){
                            loc.href = url;
                        }else{
                            loc.reload(true);
                        }
                    }
                    else{
                        this.reset();
                    }
                }
                Themify.scrollTo($this.offset().top-100);
        })
        .finally(()=>{
                $btn.prop('disabled', false);
        });
    } );

})(jQuery,Themify);