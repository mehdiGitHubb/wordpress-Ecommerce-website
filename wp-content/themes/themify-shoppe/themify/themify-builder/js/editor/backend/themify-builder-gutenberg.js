( wp=> {
    let isInit=false;
	wp.blocks.registerBlockType( 'themify-builder/canvas', {
		title:  'Themify Builder',
		icon: 'layout',
		category: 'layout',
		useOnce: true,
        supports: {
            multiple:false,
            reusable: false,
            inserter: false,
            html: false
        },
		edit(props) {
			setTimeout(()=>{
                if (isInit===false){
                    isInit=true; 
                    document.getElementById('block-'+props.clientId).removeAttribute('tabIndex');
                    Themify.trigger('tb_canvas_loaded');
                }
			}, 800);
			return wp.element.createElement('div',{ id: 'tb_canvas_block',className:'tf_rel'}, 'placeholder builder' );
		},
		save() {
			return null; // render with PHP
		}
	} );

} )(wp);
