/**
 * alert module
 */
;
(($,doc,Themify)=> {
    'use strict';
    
    const isNumber = number=>{
        return number && !isNaN(parseFloat(number)) && isFinite(number);
    },
    setCookie = (name, value, days)=>{
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

        doc.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';SameSite=strict;path=/';
    },
    getCookie = name=> {
        name = name + '=';
        const ca = doc.cookie.split(';');
        for (let i = 0, len = ca.length; i < len; ++i) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return '';
    },
    closeAlert = btn=>{
        const speed = 400;
        let buttonMessage,
            alertBox;

        if (btn) {
            buttonMessage = btn.dataset.alertMessage;
            alertBox = buttonMessage ? btn.closest('.alert-inner') : btn.closest('.module-alert');
        } else {
            alertBox = btn;
        }

        $(alertBox).slideUp(speed, function () {
			const $parent = $(this).parent();
            if (buttonMessage && !$parent.find('.alert-message').length) {
                const message = $('<div class="alert-message" />').html(buttonMessage + '<div class="alert-close tf_close" />');
                $parent.html(message);
                message.hide().slideDown(speed);
            }
        });
    };
    Themify.on('builder_load_module_partial',(el,isLazy)=>{
        if(isLazy===true && !el.classList.contains('module-alert')){
            return;
        }
        const items = Themify.selectWithParent('module-alert',el);
        for(let i=items.length-1;i>-1;--i){
            let alertID = items[i].dataset.moduleId,
                alertLimit =items[i].dataset.alertLimit,
                autoClose = items[i].dataset.alertClose;

            if ( isNumber( alertLimit ) ) {
                let currentViews = parseInt(getCookie( alertID )) || 0;
                ++currentViews;
                setCookie( alertID, currentViews, 365 );
            }

            if (isNumber(autoClose)) {
                setTimeout(()=>{
                    closeAlert(items[i]);
                }, autoClose * 1000);
            }
        }
    }).body.on('click.tb_alert', '.module-alert .alert-close', function (e) {
        e.preventDefault();
        closeAlert(this);
    });

})(jQuery,document,Themify);