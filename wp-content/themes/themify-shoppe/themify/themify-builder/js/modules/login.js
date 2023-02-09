/**
 * login module
 */
;
(($,Themify)=>{
    'use strict';
    Themify.body.on('click.tb_login', '.module-login .tb_login_links a', function (e) {
        e.preventDefault();
        $(this).closest('.module').find('.tb_lostpassword_username input').val($(this).closest('.module').find('.tb_login_username input').val());
        $(this).closest('form').slideUp().siblings().slideDown();
    });

})(jQuery,Themify);