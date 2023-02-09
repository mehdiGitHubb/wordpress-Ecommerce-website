/**
 * revealingFooter module
 */
;
( ($, Themify, win) =>{
    'use strict';
        const $footer = $('#footerwrap'),
        backToTopButton = $('.back-top.back-top-float'),
        isSticky=$footer.css('position') === 'sticky',
        $footerInner = $footer.find('#footer'),
        $content = $('#body');
    let currentColor='#ffffff', 
        contentParents,
        footerHeight = $footer.innerHeight();
    const resizeCallback =  ()=> {
            footerHeight = $footer.innerHeight();
            !isSticky && $footer.parent().css('padding-bottom', footerHeight);
        },
        scrollCallback =  ()=> {
            const contentPosition = $content.get(0).getBoundingClientRect(),
                    footerVisibility = Themify.h - contentPosition.bottom;

            $footer.toggleClass('active-revealing', contentPosition.top < 0);

            if (footerVisibility >= 0 && footerVisibility <= footerHeight) {
                $footerInner.css('opacity', footerVisibility / footerHeight + 0.2);
            } else if (footerVisibility > footerHeight) {
                $footerInner.css('opacity', 1);
            }
        };

    if (!$content.length)
        return;

    // Check for content background
    contentParents = $content.parents();
    if (contentParents.length) {
        $content.add(contentParents).each(function () {
            let elColor = $(this).css('background-color');
            if (elColor && elColor !== 'transparent' && elColor !== 'rgba(0, 0, 0, 0)') {
                currentColor = elColor;
                return true;
            }
        });
    }
    $content.css('background-color', currentColor);
    // Sticky Check
    if(!isSticky){
        document.body.classList.add('no-css-sticky');
    }
    resizeCallback();
    scrollCallback();
    Themify.on('tfsmartresize',resizeCallback);
    win.tfOn('scroll', scrollCallback,{passive:true});
    if (backToTopButton.length) {
        $('#footerwrap').before(backToTopButton);
    }
    
})(jQuery, Themify, window);