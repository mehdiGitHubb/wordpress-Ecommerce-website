((Themify,doc,vars)=>{
    const comments = doc.tfId('cancel-comment-reply-link').closest('#comments');
    if (comments) {
        const ev=Themify.isTouch ? 'touchstart' : 'mouseenter',
        load = function () {
            this.tfOff('focusin '+ev, load, {once: true, passive: true});
            Themify.loadJs(vars.commentUrl,!!window.addComment,vars.wp);
        };
        comments.tfOn('focusin '+ev, load, {once: true, passive: true});;
    }

})(Themify,document, themify_vars);