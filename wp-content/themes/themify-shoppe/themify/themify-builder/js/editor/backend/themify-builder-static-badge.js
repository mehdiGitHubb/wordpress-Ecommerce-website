/*globals window, document, $, jQuery, _, Backbone */
((api,$,wp,doc)=> {
	'use strict';
	
	let builderContent = '',
        timeout;
	const placeholder = '<!--themify_builder_static--><!--/themify_builder_static-->',
			patterns = [/\<\!--themify_builder_static--\>([\s\S]*?)<!--\/themify_builder_static--\>/gi, /\&lt;\!--themify_builder_static--\&gt;([\s\S]*?)\&lt;\!--\/themify_builder_static--\&gt;/gi, /\&amp;lt;\!--themify_builder_static--\&amp;gt;([\s\S]*?)\&amp;lt;\!--\/themify_builder_static--\&amp;gt;/gi],
		main_editor = !themifyBuilder.short_badge?'content':'excerpt',
        replaceAll=(str,replace)=>{
            if(str){
                for(let i=0,l=patterns.length;i<l;++i){
                    str=str.replaceAll(patterns[i],replace);
                }
            }
            else{
                str='';
            }
            return str;
        };
		
	
	wp.mce.views.register( 'tb_static_badge', {
		template: wp.media.template( 'tb-static-badge' ),
		bindNode( e, node ) {
            node.addEventListener(Themify.click,e=>{
                const target=e.target.closest('.tb_mce_view_frontend_btn,.tb_mce_view_backend_btn');
                if(target){
                    if(target.classList.contains('tb_mce_view_frontend_btn')){
                        this.goToFront();
                    }
                    else{
                        Themify.trigger('tb_scroll_to_builder');
                    }
                }
            });
		},
		getContent() {
			return this.template({});
		},
		match( content ) {
			const match = wp.mce.views._tb_static_content.isMatch( content );
			if ( match ) {
				return {
					index: match.index,
					content: match[0],
					options: {}
				};
			}
		},
		View: {
			className: 'tb_static_badge',
			template: wp.media.template( 'tb-static-badge' ),
			getHtml() {
				return this.template({});
			}
		},
		edit() {
			this.goToFront();
		},
		goToFront(){
           Themify.triggerEvent(doc.tfClass('tb_switch_frontend')[0],Themify.click);
		},
		contentPlaceholder( content ) {
			builderContent = builderContent || content;

			return placeholder + ( content.length > placeholder.length
				? ' '.repeat( content.length - placeholder.length ) : '' );
		}
	} );

	wp.mce.views._tb_static_content = {
		setContent( editor, content ) {
			if(content){
				content=content.trim();
			}
            const tmc=typeof tinyMCE !== 'undefined'?tinyMCE.get(main_editor):null;
			if( tmc) {
				if( tmc.hidden ) {
                    let el=doc.tfId(main_editor);
                    if(el && el.value.trim()!==content){
                       el.value=content;
                    }
				} 
				else if(content!==tmc.getContent().trim()){
					tmc.setContent( content );
				}
			} 
			else if(content!==editor.value.trim()) {
				editor.value=content;
			}
		},
		isMatch( content ) {
			return patterns[0].exec( content ) || patterns[1].exec( content ) || patterns[2].exec( content );
		}
	};

	$(doc).on('tinymce-editor-init', ( e, editor )=> {
		if (editor.wp && editor.wp._createToolbar) {
			const toolbar = editor.wp._createToolbar([
				'wp_view_edit'
			]);

			if (toolbar) {
				//this creates the toolbar
				editor.on('wptoolbar', e=> {
					if (editor.dom.hasClass(e.element, 'wpview') && 'tb_static_badge' === editor.dom.getAttrib( e.element, 'data-wpview-type')) {
						e.toolbar = toolbar;
					}
				});
			}
		}
		if(editor.id!==main_editor){
			const content = replaceAll(editor.getContent(),'');
		}else{
			editor.setContent( wp.mce.views.setMarkers( editor.getContent() ) );
		}

		editor.on('beforesetcontent', e=> {
			e.content = wp.mce.views.setMarkers( e.content );
		});
	});

	Themify.on('themify_builder_save_data', jqxhr=>{
		if (themifyBuilder.is_gutenberg_editor  || !jqxhr.builder_data || !jqxhr.static_content ){
			return true;
		}

		let editor=typeof tinyMCE !== 'undefined'?tinyMCE.get(main_editor):null,
        content=null;
		if( editor) {
			content = !editor.hidden ? editor.getContent() : tinymce.DOM.get(main_editor).value;
		} 
        else {
			editor = doc.tfId(main_editor);
			if ( ! editor ) {
				return;
			}
			content = editor.value;
		}
        if(content!==null){
            const match = wp.mce.views._tb_static_content.isMatch( content ),
                v=match?replaceAll(content,jqxhr.static_content):(content + jqxhr.static_content);
            wp.mce.views._tb_static_content.setContent( editor, v);
        }
	});

	// YOAST SEO
	const yoastReadBuilder = {
		timeout:null,
		// Initialize
		init() {
			$(window).on('YoastSEO:ready', ()=>{
				yoastReadBuilder.load();
			});
		},
		// Load plugin and add hooks.
		load() {
			// gutenberg post
			if ( themifyBuilder.is_gutenberg_editor ) {
				builderContent = wp.data.select( "core/editor" ).getCurrentPost().builder_content;
			}

			YoastSEO.app.registerPlugin( 'TBuilderReader', {status: 'loading'} );

			YoastSEO.app.pluginReady( 'TBuilderReader' );
			YoastSEO.app.registerModification( main_editor, yoastReadBuilder.readContent, 'TBuilderReader', 5 );

			// Make the Yoast SEO analyzer works for existing content when page loads.
			yoastReadBuilder.update();
		},
		// Read content to Yoast SEO Analyzer.
		readContent( content ) {
			if( builderContent ) {
				if ( themifyBuilder.is_gutenberg_editor ) {
					content+= ' ' + builderContent;
				} else {
					content = content.replace( placeholder, builderContent ).replace( /(\r\n|\n|\r)/gm, '' );
				}
			}

			return content;
		},
		// Update the YoastSEO result. Use debounce technique, which triggers only when keys stop being pressed.
		update() {
			if(timeout ){
				clearTimeout(timeout );
			}
			timeout = setTimeout(  ()=> {
				YoastSEO.app.refresh();
			}, 250 );
		}
	};
	// Run on document ready.
	//$( yoastReadBuilder.init );
	yoastReadBuilder.init();

})(tb_app,jQuery,wp,document);