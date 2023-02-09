(function($){

	'use strict';

	var themifyGalleryShortcode = {
		init: function(){
			this.galleryShortcode();
		},
		galleryShortcode: function(){
			var clone = wp.media.gallery.shortcode, wpgallery = wp.media.gallery, file_frame, frame;

			$('.themify-gallery-shortcode-btn').on('click', function(e) {
				var shortcode_val = $(this).closest('.themify_field').find('.themify-gallery-shortcode-input');
		
				if(shortcode_val.html()){
					shortcode_val.val(shortcode_val.html());
					shortcode_val.html('');
					shortcode_val.text('');
				}
		
				if (file_frame) {
					file_frame.open();
				} else {
					if ($.trim(shortcode_val.val()).length > 0) {
						file_frame = wpgallery.edit($.trim(shortcode_val.val()));
					} else {
						file_frame = wp.media.frames.file_frame = wp.media({
							frame : 'post',
							state : 'gallery-edit',
							title : wp.media.view.l10n.editGalleryTitle,
							editing : true,
							multiple : true,
							selection : false
						});
					}
				}
		
				wp.media.gallery.shortcode = function(attachments) {
					var props = attachments.props.toJSON(), attrs = _.pick(props, 'orderby', 'order');
		
					if (attachments.gallery)
						_.extend(attrs, attachments.gallery.toJSON());
		
					attrs.ids = attachments.pluck('id');
		
					// Copy the `uploadedTo` post ID.
					if (props.uploadedTo)
						attrs.id = props.uploadedTo;
		
					// Check if the gallery is randomly ordered.
					if (attrs._orderbyRandom)
						attrs.orderby = 'rand';
					delete attrs._orderbyRandom;
		
					// If the `ids` attribute is set and `orderby` attribute
					// is the default value, clear it for cleaner output.
					if (attrs.ids && 'post__in' === attrs.orderby)
						delete attrs.orderby;
		
					// Remove default attributes from the shortcode.
					_.each(wp.media.gallery.defaults, function(value, key) {
						if (value === attrs[key])
							delete attrs[key];
					});
		
					var shortcode = new wp.shortcode({
						tag : 'gallery',
						attrs : attrs,
						type : 'single'
					});
		
					shortcode_val.val(shortcode.string());
		
					wp.media.gallery.shortcode = clone;
					return shortcode;
				};
		
				file_frame.on('update', function(selection) {
					var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
					shortcode_val.val('[' + shortcode + ']');
				});
		
				if ($.trim(shortcode_val.val()).length === 0) {
					$('.media-menu').find('.media-menu-item').last().trigger('click');
				}
				e.preventDefault();
			});
		}
	};
	$(themifyGalleryShortcode.init);
})(jQuery); 