( ( $, doc, vars )=> {

	'use strict';

	/**
	 * Themify Global Styles Manager
	 * The resources that manage Global Styles
	 *
	 * @since 4.5.0
	 */
	const themifyGS = function () {

		this.form = doc.tfId( 'tb_admin_new_gs' );
		this.loadAddNewForm();
		this.addNew();
		this.deleteStyle();
		this.restore();
		this.scalePreview();
		this.importFile();
	};

	/**
	 * Handle add new Global Style functionality
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.addNew = function () {

		const addNew = doc.tfClass( 'tb_admin_save_gs' )[0];
		if ( !addNew) {
			return;
		}
		addNew.tfOn( 'click', e=>{
			e.preventDefault();
			if ( !this.validateForm() ) {
				alert( vars.i18n.formValid );
				return;
			}
			e.target.text = vars.i18n.creating;
			$.ajax( {
				type: 'POST',
				url: vars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_save_custom_global_style',
					nonce: vars.nonce,
					form_data: $( this.form ).serialize()
				},
				success( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						e.target.text = vars.i18n.create;
					} else if ( 'success' === resp.status ) {
						window.location = resp.url;
					} else {
						// Something went wrong with save Global Style response
						e.target.text = vars.i18n.create;
					}
				}
			} );
		} );
	};

	/**
	 * Validate add new form
	 *
	 * @since 4.5.0
	 * @returns bool
	 */
	themifyGS.prototype.validateForm = function () {

		let valid = true;
		$.each(  $( this.form ).serializeArray(), function ( i, field ) {
			if ( '' == field.value ) {
				valid = false;
				return false;
			}
		} );
		return valid;
	};

	/**
	 * Load popup for to create new Global Style
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.loadAddNewForm = function () {

		const $addNew = $( '.tb_add_new_gs' );
		if ( $addNew.length === 0 ) {
			return;
		}
		$addNew.magnificPopup( {
			type: 'inline',
			midClick: true,
			callbacks: {
				close() {
					doc.tfId( "tb_admin_new_gs" ).reset();
				}
			}
		} );

		const export_links = doc.tfClass( 'tb_gs_export' );
		for ( let i = export_links.length - 1; i > -1; --i ) {
			export_links[ i ].tfOn( 'click', e => {
				e.preventDefault();
				Themify.fetch( {
					action : 'tb_get_gs_post',
					id : e.target.dataset.id,
					nonce : vars.nonce
				} ).then( ( response ) => {
					this.loadJsZip().then( () => {
						let zip = new JSZip();
						zip.file( 'builder_gs_data_export.txt', JSON.stringify( response ) );
						zip.generateAsync( { type : 'blob' } ).then( blob => {
							const zipName = e.target.dataset.title + '_export_.zip';
							this.donwload( blob, zipName );
						});
					} );
				} );
			} );
		}
	};

	/**
	 * Handle delete Global Style functionality
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.deleteStyle = function () {

		const $removeBtn = $( '.tb_remove_gs' );
		if ( $removeBtn.length === 0 ) {
			return;
		}
		$removeBtn.on('click', function ( e ) {
			e.preventDefault();
			const $this = $( this ),
				pageStatus = $this.parents('.tb_admin_gs_list').data('list'),
				msg = 'publish' === pageStatus ? vars.i18n.deleteConfirm : vars.i18n.deleteConfirm2;
			if ( !confirm( msg ) ) {
				return;
			}
			$this.parents( '.tb_gs_element' ).fadeOut();
			$.ajax( {
				type: 'POST',
				url: vars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_delete_global_style',
					nonce: vars.nonce,
					status: pageStatus,
					id: $this.attr( 'data-id' )
				},
				success( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						$this.parents( '.tb_gs_element' ).fadeIn();
					}
				}
			} );
		} );

	};

	/**
	 * Handle restore Global Style functionality
	 *
	 * @since 4.5.7
	 * @returns void
	 */
	themifyGS.prototype.restore = function () {

		const $restoreBtn = $( '.tb_gs_restore' );
		if ( $restoreBtn.length === 0 ) {
			return;
		}
		$restoreBtn.on('click', function ( e ) {
			e.preventDefault();
			const $this = $( this );
			$this.parents( '.tb_gs_element' ).fadeOut();
			$.ajax( {
				type: 'POST',
				url: vars.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tb_restore_global_style',
					nonce: vars.nonce,
					id: $this.attr( 'data-id' )
				},
				success( resp ) {
					if ( 'failed' === resp.status ) {
						alert( resp.msg );
						$this.parents( '.tb_gs_element' ).fadeIn();
					}
				}
			} );
		} );

	};

	/**
	 * Scale the preview
	 *
	 * @since 4.5.0
	 * @returns void
	 */
	themifyGS.prototype.scalePreview = function () {

		$( ".themify_builder_content" ).each( function () {
			let $el = $( this ),
				$wrapper = $el.parent(),
				scale = Math.min( $wrapper.width() / $el.outerWidth(), $wrapper.height() / $el.outerHeight() );
			$el.css( {
				transform: "translate(-50%, -50%) scale(" + scale + ")"
			} );
		} );
	};

	themifyGS.prototype.importFile = function () {
		const btn = doc.tfClass( 'tb_import_gs' )[0];
		if ( ! btn ) {
			return;
		}

		btn.tfOn( 'click', e => {
			e.preventDefault();
			const fileInput=doc.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.zip,.txt';
            fileInput.tfOn( 'change', function(e){
				const file = this.files[0],
					callback = function( data ) {
						Themify.fetch( {
							action : 'tb_import_gs_posts_ajax',
							data : data,
							nonce : vars.nonce
						} ).then( ( response ) => {
							/* import successful */
							location.reload();
						} );
					};

				if ( file.type === 'text/plain' ) {
					const reader = new FileReader();
					reader.tfOn( 'loadend', function(e) {
						if ( this.readyState === FileReader.DONE ) {
							callback( e.target.result );
						} else {
							throw Error( vars.i18n.invalid_file );
						}
					} , { passive : true, once : true } )
                    .readAsText(file);
				} 
                else if( file.type === 'application/x-zip-compressed' || file.type==='application/zip' ) {
					this.loadJsZip().then( () => {
						const jsZip = new JSZip();
						jsZip.loadAsync( file ).then( zip => {
							const files = zip.files;
							if ( files && files['builder_gs_data_export.txt'] ) {
								zip.file( 'builder_gs_data_export.txt' ).async( 'text' ).then( res => {
									callback( res );
								});
							} else{
								throw Error( vars.i18n.missing_file );
							}
						}).catch( e => {
							throw e;
					   });
					});
				}
			} )
            .click();
		} );
	};

	themifyGS.prototype.loadJsZip = ()=> {
        return Themify.loadJs(Themify.url+'js/admin/jszip.min',!!win.JSZip,'3.10.1');
	};

	themifyGS.prototype.donwload = ( blob, name )=> {
		let a=doc.createElement('a');
		a.download = name;
		a.rel = 'noopener';
		a.href = URL.createObjectURL(blob);
		setTimeout( ()=> { 
			URL.revokeObjectURL(a.href); 
			a=null;
		}, 7000); 
		a.click();
	}

	new themifyGS();

})( jQuery, document, themifyGlobalStylesVars );