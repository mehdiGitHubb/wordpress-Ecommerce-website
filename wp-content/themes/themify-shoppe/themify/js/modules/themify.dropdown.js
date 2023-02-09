;(($,doc, Themify )=>{
	'use strict';
        
	const _trigger = (el,show) =>{
		const item=el.find( '.sub-menu, .children' ).first(),
			   sub= el.find( '> a .child-arrow' );
		let ev='open';
		if(show===true){
			item.css( 'visibility', 'visible' ).slideDown();
			el.addClass( 'dropdown-open toggle-on' );
			sub.removeClass( 'closed' ).addClass( 'open' );
		}
		else{
			item.slideUp( () => {
				$( this ).css( 'visibility', 'hidden' );
			} );
			el.removeClass( 'dropdown-open toggle-on' );
			sub.removeClass( 'open' ).addClass( 'closed' );
			ev='close';
		}
		el.trigger( 'dropdown_'+ev );
	};
	
	Themify.on('tf_dropdown_init',items=>{
        if(items.length===undefined){
			items=[items];
		}
		for(let i=items.length-1;i>-1;--i){
			if(!items[i].classList.contains('with-sub-arrow')){
				items[i].className+=' with-sub-arrow';
			}
		}
	});
    doc.body.tfOn(Themify.click,e=>{
        const clicked=e.target?e.target.closest('.child-arrow,.with-sub-arrow a'):null;
		if(clicked && clicked.closest('.with-sub-arrow')){	
			const href=clicked.classList.contains('child-arrow')?'':clicked.getAttribute('href');
			if(!href || href==='#'){
				e.stopPropagation();
				e.preventDefault();
				const el=$( clicked ),
                    menu_item = el.closest( 'li' ),
					active_tree = el.parents( '.dropdown-open' );
				el.closest( '.with-sub-arrow' ) // get the menu container
					.find( 'li.dropdown-open' ).not( active_tree ) // find open (if any) dropdowns
					.each(function(){
						_trigger( $( this ),false );
					});

				_trigger( menu_item, ! menu_item.hasClass( 'dropdown-open' ) );
			}
		}
    },{passive:false});
})( jQuery,document, Themify );