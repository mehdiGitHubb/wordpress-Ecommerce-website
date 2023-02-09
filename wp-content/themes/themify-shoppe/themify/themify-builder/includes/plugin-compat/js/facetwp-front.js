(  ()=> {

	FWP.hooks.addAction( 'facetwp/loaded', function() {
		// don't run on FWP's first load
		if ( ! FWP.loaded ) {
			return;
		}

		let modules = document.querySelectorAll( '.themify_builder_content .module.facetwp-template' );
		for ( let i = 0; i < modules.length; i++ ) {
			Themify.lazyScroll( Themify.convert( modules[ i ].querySelectorAll('[data-lazy]') ).reverse(), true );
		}
	} );

})();