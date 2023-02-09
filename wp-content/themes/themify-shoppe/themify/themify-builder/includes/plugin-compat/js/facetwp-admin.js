( function ( $, api ) {

	$( document ).on( 'tb_editing_module', function( e ) {
		let container = ThemifyBuilderCommon.Lightbox.$lightbox[0],
			has_query_posts = container.querySelector( '.tb_field[data-type="query_posts"]:not(.tbp_disable_dynamic_query)' ); /* assume that if DQ feature in Pro is disabled on a query_posts field, it should also not have filtering enabled */
		if ( ! has_query_posts ) {
			return;
		}

		let options = ThemifyConstructor.create( [
			{ type : 'separator', 'html' : '<div><hr><h4>' + tbFacet.label + '</h4></div>' },
			{ type : 'toggle_switch', id : 'facetwp', label : tbFacet.label, help : tbFacet.desc , options : {
				on : { name : 'y', value : 'en' },
				off : { name : '', value : 'dis' }
			} }
		] );
		container.querySelector( '.tb_options_tab_content' ).appendChild( options );
	} );

})( jQuery, tb_app );