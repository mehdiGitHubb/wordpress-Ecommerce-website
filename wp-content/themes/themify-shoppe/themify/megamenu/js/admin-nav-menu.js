;(function ($) {
        'use strict';
	/* function to call when menu item is added to the menu */
	function add_item_callback() {
		$( '.spinner', '#themify-widget-section' ).fadeOut(function(){
			$(this).remove();
		});
	}

	$( '#themify-widget-menu-submit' ).click(function(){
		var selected = $('#themify-menu-widgets :checked'),
			$button = $( this );
		if( selected.length > 0 ) {
			$( '<span class="spinner" style="visibility: visible; display: inline-block;"></span>' ).insertBefore( $button );

			/* add menu item to the menu */
			wpNavMenu.addLinkToMenu( '#' + selected.val(), selected.text(), null, add_item_callback );
		}
	});

	/* show and hide Dropdown Width option based on Mega Menu selection */
	$( 'body' ).on( 'change', '.themify_field_tf-mega', function(){
		if( $( this ).val() == 'drop' ) {
			$( this ).closest( '.menu-item' ).find( '.tf-dropdown-columns-field' ).show();
		} else {
			$( this ).closest( '.menu-item' ).find( '.tf-dropdown-columns-field' ).hide();
		}

		if( $( this ).val() == 'columns' ) {
			$( this ).closest( '.menu-item' ).find( '.tf-mega-columns-layout' ).show();
		} else {
			$( this ).closest( '.menu-item' ).find( '.tf-mega-columns-layout' ).hide();
		}
	} );
	$( '#update-nav-menu' ).on('click', '.item-edit', function(){
		$( this ).closest( '.menu-item' ).find( '.themify_field_tf-mega' ).trigger( 'change' );
	})
	/* customize menu item edit screen for widget menu items */
	.on('click', '.item-edit', function(){
		var item = $(this).closest( 'li.menu-item-custom' );
		if( item.length < 1 ) return;

		if( item.find( '.themify-widget-options' ).length > 0 ) { // widget type
			var el = item.find( '.themify-widget-options' );
			el.prevAll().hide();

			/* for top-level menu items, show the Title field */
			if( item.hasClass( 'menu-item-depth-0' ) ) {
				item.find( '.edit-menu-item-title' ).closest( 'p' ).show();
				item.find( '.field-icon' ).show();
			}

			/* init the widgets */
			if( ! el.hasClass( 'initialized' ) ) {
				$( document ).trigger( 'widget-added', [ item.find( '.themify-widget-options' ).addClass( 'open' ) ] );
				el.addClass( 'initialized' );
			}
		}
	})
	.on( 'click', '.tf-mega-columns-layout a', function(e) {
		e.preventDefault();
		var $this = $( this );
		$this.parent().find( '.val' ).val( $this.attr( 'data-value' ) );
		$this.addClass( 'selected' ).siblings().removeClass( 'selected' );
	} );
})(jQuery);