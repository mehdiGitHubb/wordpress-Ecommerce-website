/**
 * sharer module
 */
;
((Themify, doc, win)=>{
    'use strict';
    Themify.on('tf_sharer_init', (type, url, title)=>{
        if (!title) {
            title = '';
        } else {
            // Strip HTML
            const tmp = doc.createElement('div');
            tmp.innerHTML = title;
            title = tmp.textContent || tmp.innerText || '';
            title = title.trim();
        }
        const width = 550,
            height = 300,
            leftPosition = (win.screen.width / 2) - ((width / 2) + 10),
            topPosition = (win.screen.height / 2) - ((height / 2) + 50),
            features = 'status=no,height=' + height + ',width=' + width + ',resizable=yes,left=' + leftPosition + ',top=' + topPosition + ',screenX=#{left},screenY=#{top},toolbar=no,menubar=no,scrollbars=no,location=no,directories=no';
        if ('facebook' === type) {
            url = 'https://www.facebook.com/sharer.php?u=' + url;
        } else if ('twitter' === type) {
            url = 'http://twitter.com/share?url=' + url + '&text=' + title;
        } else if ('linkedin' === type) {
            url = 'https://www.linkedin.com/shareArticle?mini=true&url=' + url;
        } else if ('pinterest' === type) {
            url = '//pinterest.com/pin/create/button/?url=' + url + '&description=' + title;
        } else if ('email' === type) {
            title=''===title?doc.tfTag('title')[0].textContent:title;
            url = 'mailto:?subject=' + (title === '' ? themify_vars.emailSub : title) + '&body=' + url;
        }
        win.open(encodeURI(url), 'sharer', features).moveTo(leftPosition, topPosition);
    });
})(Themify, document, window);
