($=>{
    'use strict';
   window.tfOn('load', function(){

        $('#query-posts input[name="layout"],#query-products input[name="product_layout"]').on('change',function (e) {
            var $val = $(this).val(),
                    $post_type = $(this).parents('#query-posts').length > 0 ? '' : 'product_',
                    $media = $post_type == '' ? $('#media_position').closest('.themify_field_row') : $(),
                    $masonary = $post_type == '' ? $('#post_masonry') : $('#product_masonry'),
                    $masonary = $masonary.closest('.themify_field_row'),
                    $content_layout = $post_type == '' ? $('#post_content_layout') : $('#product_content_layout'),
                    $content_layout = $content_layout.closest('.themify_field_row'),
                    $category = $('#' + $post_type + 'query_category').val();
            // SlideUp/animation doesn't work when element is hidden
            if (!$category) {
                $masonary.hide();
                $media.hide();
                $content_layout.hide();
                return;
            }
            if ($val === 'list-post' || $val === 'list-large-image' || $val === 'auto_tiles') {
                $masonary.slideUp();
                if ($val === 'list-post') {
                    $media.slideDown();
                }
                else {
                    $media.slideUp();

                }
                if ($val === 'auto_tiles'  || $val === 'list-large-image') {
                    $content_layout.slideUp()
                }
                else {
                    $content_layout.slideDown();
                }
            }
            else {
                $content_layout.slideDown();
                $masonary.slideDown();
                $media.slideDown();
            }
        });
        $('#query_category,#product_query_category').on('change',function () {
            var $post_type = $(this).closest('#query_category').length > 0 ? '' : 'product_';
            $('input[name="' + $post_type + 'layout"],#' + $post_type + 'more_posts').trigger('change');
        });

        $('#product_more_posts, #more_posts').on('change',function (e) {
            var $val = $(this).val(),
                    $post_type = $(this).parents('#query-posts').length > 0 ? '' : 'product_',
                    $pagination = $('#' + $post_type + 'hide_navigation'),
                    $category = $('#' + $post_type + 'query_category').val();

            $pagination = $pagination.closest('.themify_field_row');
            if (!$category) {
                $pagination.hide();
                return;
            }
            if ($val === 'infinite' || !$('#' + $post_type + 'query_category').val()) {
                $pagination.slideUp();
            }
            else {
                $pagination.slideDown();
            }
        }).trigger('change');
        $('#query_category,#product_query_category').trigger('change');
        $('body').on('#themify_builder_lightbox_parent #layout_post a, #themify_builder_lightbox_parent #layout_products a', 'click', function () {
            var $masonary = $('#themify_builder_lightbox_parent .masonry_post'),
                    $content_layout = $('#themify_builder_lightbox_parent .content_layout'),
                    $val = $(this).prop('id');
            if ($val === 'grid3' || $val === 'grid2' || $val === 'grid4' || $val==='grid5' || $val==='grid6') {
                $masonary.show();
                $content_layout.show();
            }
            else {
                $masonary.hide();
                if ($val === 'auto_tiles') {
                    $content_layout.hide();
                }
                else {
                    $content_layout.show();
                }
            }

        });
        $('input[name="header_wrap"]').on('change',function () {
            var bg_field = $('#background_color').closest('.themify_field_row');
            if ($(this).val() === 'slider') {
                $('#background_wrap').closest('.themify_field_row').after(bg_field);
            }
            else {
                $('#background_position').closest('.themify_field_row').after(bg_field);
            }
        }).trigger('change');
        var CartStyle = function (val) {
            var $cart_style = $('#cart_style-dropdown').closest('.themify_field_row');
            if (val === 'yes') {
                $cart_style.hide();
            }
            else {
                $cart_style.show();
            }
        };

        $('#exclude_cart .ddbtn a').on('click',function () {
            CartStyle($(this).data('val'));
        });
        CartStyle($('input#exclude_cart').val());
        $('body').on('editing_module_option', function (e, settings) {

            if ($('#themify_builder_lightbox_parent .masonry_post').length > 0) {
                setTimeout(function () {
                    var $cl = $('#themify_builder_lightbox_parent #layout_post').length > 0 ? '#layout_post' : '#layout_products',
                            $layout = $('#themify_builder_lightbox_parent ' + $cl + ' a.selected');

                    if ($layout.length === 0) {
                        $layout = $('#themify_builder_lightbox_parent ' + $cl + ' a').first();
                    }
                    $layout.trigger('click');
                }, 600);
            }
        });

		$( '#exclude_menu_navigation .dropdownbutton a' ).on( 'click', function() {
			if ( $( this ).data( 'val' ) == 'yes' ) {
				$( '#mobile_menu_styles' ).closest( '.themify_field_row' ).hide();
			} else {
				$( '#mobile_menu_styles' ).closest( '.themify_field_row' ).show();
			}
		} );

        // Search Post Type dependency
        var $searchPostType = $('input[name="setting-search_post_type"]');
        if($searchPostType.length){
            $('#themify_search_settings .themify_panel_fieldset_wrap p:not(.themify_search_post_type)').each(function(){
                $(this).attr({
                    'data-show-if-element':'[name=setting-search_post_type]',
                    'data-show-if-value':'all'
                });
            });
        }

	}, {once:true, passive:true});

})(jQuery);
