(function ($) {

    'use strict';
    
   window.addEventListener('load', function(){
        $('body').on('click', 'a.wpf_lightbox', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $self = $(this);
            $.ajax({
                url: this,
                success: function (data) {
                    if (data) {
                        openLightBox(e, $self.attr('title'), data,$self.data('class'),$self.data('top'));
                    }
                }
            });
        }).on('click', '.wpf_delete',function (e) {
            if (confirm(wpf_js.template_delete)) {
                var $this = $(this),
                    $table = $this.closest('table');
                $.ajax({
                    url: this,
                    dataType:'json',
                    beforeSend: function () {
                       $table.addClass('wpf_save');
                    },
                    complete: function () {
                        $table.removeClass('wpf_save');
                    },
                    success: function (res) {
                        if (res && res.status=='1') {
                            $this.closest('tr').remove();
                        }
                    }
                });
            }
            e.preventDefault();            
        }).on('click','.open_media_uploader_image',function(e){

            var parent = $(this).closest('.wpf_background_image'),
                media_uploader = wp.media({
                    frame:    "post",
                    state:    "insert",
                    multiple: false
                });

                media_uploader.open();

                media_uploader.on("insert", function(){
                    var json = media_uploader.state().get("selection").first().toJSON();
                    parent.find('input').val(json.url);
                    parent.find('img').attr("src",json.url);
                    parent.addClass('has-image');
            });
            e.preventDefault();

        }).on('click', '.remove-background', function(e){
            var parent = $(this).closest('.wpf_background_image'),
                answer = confirm('Remove background image?');
             if (answer == true) {
                 parent.find('input').val('');
                 parent.find('img').attr("src",'');
                 parent.removeClass('has-image');
            }
            e.preventDefault();
        });
    }, {once:true, passive:true});
    
    var getDocHeight = function () {
        var D = document;
        return Math.max(
                Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
                Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
                Math.max(D.body.clientHeight, D.documentElement.clientHeight)
                );
    };

    var lightboxCloseKeyListener = function (e) {
        if (e.keyCode === 27) {
            e.preventDefault();
            closeLightBox(e);
        }
    };

    var openLightBox = function (e, title, content, $class, $top) {
        e.preventDefault();
        $(document).on('keyup', lightboxCloseKeyListener);
        var $uniqid = 'wpf_' + Math.random().toString(36).substr(2, 9);
        var $lightbox = '<div id="' + $uniqid + '" class="wpf_admin_lightbox wpf_interface">' +
                '<div class="wpf_lightbox_title">' + title + '</div>' +
                '<a href="#" class="wpf_close_lightbox">×</a>' +
                '<div id="wpf_lightbox_container">' +
                '<div class="wpf_lightbox_inner">' + content + '</div>' +
                '</div>' +
                '</div>' +
                '<div class="wpf_overlay"></div>';
        $('body').append($lightbox);
        if(!$top){
            $top = 100;
        }
        if($class){
            $('#' + $uniqid).addClass($class);
        }
        $.event.trigger("WPF.openlightbox",[e,$uniqid]);
        $('#' + $uniqid).nextAll('.wpf_overlay').show();
        $('#' + $uniqid).show().css('top', getDocHeight()).animate({
            top: $top
        }, 800);
        $('#' + $uniqid).find('.wpf_close_lightbox').on( 'click', closeLightBox );

		let $import_button = $( '#wpf-import-btn' );
		if ( $import_button.length ) {
			wpf_import( $import_button );
		}
    };

    var closeLightBox = function (e) {
        e.preventDefault();
        $(document).off('keyup', lightboxCloseKeyListener);
        var $container = $(this).closest('.wpf_admin_lightbox');
        $.event.trigger('WPF.close_lightbox', this);
        $container.animate({
            top: getDocHeight()
        }, 800, function () {
            $container.next('.wpf_overlay').remove();
            $container.remove();
        });
    };
    
})(jQuery);

function wpf_import( $this ) {
    'use strict';
    var id1 = $this.attr("id"),
        imgId = id1.replace("plupload-upload-ui", ""),
        $ = jQuery;
    var pconfig = [],
        $form = $this.closest('form'),
        $error = $form.find('.wpf_error');
    pconfig = $form.data( 'plupload' );
    pconfig["browse_button"] = imgId;
    pconfig["container"] = $this.closest('form').attr('id');
    pconfig["file_data_name"] = $this.data('name') ? $this.data('name') : pconfig["file_data_name"];
    pconfig["multipart_params"] = {'nonce': '', 'action': ''};
    if ($this.data('formats')) {
        pconfig['filters'] = $this.data('formats');
    }
    var uploader = new plupload.Uploader(pconfig);
	uploader.init();
	uploader.bind('FilesAdded', function (up, files) {
		if ($this.data('confirm') && !confirm($this.data('confirm'))) {
			return;
		}
		uploader.settings.multipart_params.action = $form.find('input[name="action"]').val();
		uploader.settings.multipart_params.nonce = $form.find('input[name="nonce"]').val();
		up.refresh();
		up.start();
		$form.addClass('wpf_save');
	});
	uploader.bind('Error', function (up, error) {
		$form.removeClass('wpf_save');
		if (error.message) {
			$error.text(error.message);
		}
		return;
	});
	uploader.bind('FileUploaded', function (up, file, response) {
		if (response) {
			var json = JSON.parse(response['response']);
			if (json.error) {
				$form.removeClass('wpf_save');
				$error.text(json.error);
			}
			else if (json.success) {
				setTimeout(function(){
					$form.removeClass('wpf_save');
				},1500);
				window.location.reload();
			}
		}
		else{
			$form.removeClass('wpf_save');
		}
	});
};

