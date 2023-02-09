/* Themify Admin Warning Removal
 * Dismiss warnings shown in WP admin and sets a WP option so they're never shown again.
 */

(function($){
	'use strict';
	window.addEventListener('load', function(){

        $('body').on('click', '.themify-close-warning', function(){
            const $self = $(this),
                data = {
                    action: 'themify_dismiss_warning',
                    nonce: $self.data('nonce'),
                    warning: $self.data('warning')
                };
            $.post(ajaxurl, data, function(response) {
                if ( response ) {
                    $self.parent().fadeOut().remove();
                }
            });
        });

   }, {once:true, passive:true});

})(jQuery);