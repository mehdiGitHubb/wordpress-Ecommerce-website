/**
 * jQuery.loadScript
 * @link http://marcbuils.github.io/jquery.loadscript/
 */
jQuery(function($){var e=function(e,t,n){var r=document.createElement("script");r.type="text/javascript",r.readyState?r.onreadystatechange=function(){if(r.readyState=="loaded"||r.readyState=="complete")r.onreadystatechange=null,n()}:r.onload=function(){n()};var i=["type","src","htmlFor","event","charset","async","defer","crossOrigin","text","onerror"];if(typeof t=="object"&&!$.isEmptyObject(t))for(var s in t)t.hasOwnProperty(s)&&$.inArray(s,i)&&(r[s]=t[s]);r.src=e,document.getElementsByTagName(t.lazyLoad?"body":"head")[0].appendChild(r)};$.loadScript=function(t,n,r){arguments.length===2&&typeof arguments[1]=="function"&&(r=arguments[1],n={}),n=n||{};var i=$.Deferred();return typeof r=="function"&&i.done(function(){r()}),e(t,n,function(){i.resolve()}),i.promise()}});

window.Themify_Metabox = (function($){

	'use strict';

	var api = {};

	/**
	 * A wrapper for $.loadScript, simplifies loading scripts conditionally
	 */
	api.loadScript = function( url, callback, condition ) {
		if ( condition ) {
			callback();
		} else {
			$.loadScript( url ).done( function() {
				callback();
			} );
		}
	},

	api.init = function(){
		window.addEventListener('load',api.document_ready,{passive:true,once:true});
	};
	api.document_ready = function(){
		api.init_metabox_tabs();
		api.gallery_shortcode();
		api.repeater();

		// revisit these three
		api.enable_toggle();
		api.query_category();
		api.post_meta_checkbox();

		api.init_fields( $( 'body' ) );
		api.pagination( $( 'body' ) );
		api.audioRemoveAction();
		api.page_layout_field();
	},

	// create the tabs in custom meta boxes
	api.init_metabox_tabs = function(){
		// Tabs for Custom Panel
		$( '.themify-meta-box-tabs' ).each(function(){
			var context = $( this ),
				$ilcHTabsLi = $( '.ilc-htabs li', context );
			if( $ilcHTabsLi.length > 1 ) {
				ThemifyTabs( {ilctabs  : '#' + context.attr( 'id' ) } );
			} else {
				$( '.ilc-tab', context ).show();
				$ilcHTabsLi.addClass( 'select' );
			}
		});

		// set tabs cookie
		$('#themify-meta-boxes .ilc-htabs a').on('click', function(){
			api.set_cookie('themify-builder-tabs', $(this).attr('id'));
		});

		// set default visible tab
		let default_active = document.querySelector( '#themify-meta-boxes .default_active' ),
			location=window.location,
            hash=location.hash.trim();
            if(!default_active && hash && hash!=='#'){
                default_active=document.querySelector( '#themify-meta-boxes a#'+CSS.escape(hash.replace('#','')) );
				if(default_active){
					location.hash='';
					window.history.replaceState(null, "", location.href.replace('#','')) ;
					default_active.scrollIntoView();
				}
            }
		if ( default_active ) {
			default_active.click();
		} 
        else if( typeof(api.get_cookie('themify-builder-tabs')) != 'undefined' && api.get_cookie('themify-builder-tabs') !== null ){
			$( '#' + api.get_cookie('themify-builder-tabs')).trigger('click');
		}
	};

	// initialize different field types
	api.init_fields = function( $context ) {

		api.layout( $context );
		api.color_picker( $context );

		if ( $context.find( '.themifyDatePicker' ).length ) {
			api.loadScript( TF_Metabox.includes_url + 'js/jquery/ui/datepicker.min.js', function() {
				api.loadScript( TF_Metabox.includes_url + 'js/jquery/ui/slider.min.js', function() {
					api.loadScript( TF_Metabox.url + 'js/jquery-ui-timepicker.min.js', function() {
						api.date_picker( $context );
					}, typeof $.ui.timepicker !== 'undefined' );
				}, typeof $.fn.slider !== 'undefined' );
			}, typeof $.fn.datepicker !== 'undefined' );
		}

		api.assignments( $context );
		api.dropdownbutton( $context );
		api.togglegroup( $context );

		// custom event trigger
		$( document ).triggerHandler( 'themify_metabox_init_fields', [api] );
	};

	api.repeater = function(){
		$( 'body' ).on( 'click', '.themify-repeater-add', function(e){
			e.preventDefault();
			var $this = $( this ),
				container = $this.closest( '.themify_field_row' ),
				rows = container.find( '.themify-repeater-rows' ),
				template = container.find( '.themify-repeater-template' ).html(),
                                new_id = 1;
			if( rows.find( '> div' ).length ) {
				rows.find( '> div' ).each(function(){
					new_id = Math.max( new_id, $( this ).data( 'id' ) );
				});
				++new_id;
			}

			var $template = $( template.replace( /__i__/g, new_id ) );
			$template.find( '.ajaxnonceplu' ).attr( 'id', '' );
			rows.append( $template );

			if( $template.has( '.plupload-upload-uic' ).length ) {
				$template.find( '.plupload-upload-uic' )
					.each( function () {
						themify_create_pluploader( $( this ) );
					} );
			}
			// init field types for the new row
			api.init_fields( rows.find('.themify-repeater-row:last-child') );
		} )
                .on( 'click', '.themify-repeater-remove-row', function( e ) {
			e.preventDefault();

			$( this ).parent().remove();
		} );
	};

	api.audioRemoveAction = function() {
		$( 'body' ).on( 'click', '[data-audio-remove] a', function( e ) {
			e.preventDefault();

			var $self = $( this ).parent(),
				data = $self.data( 'audio-remove' ),
				callback = function() {
					$self.parent().find( '.themify_upload_field' ).val('');
					$self.addClass( 'hide' );
				};
			
			callback();
			data.action = 'themify_remove_audio';
			$.post( ajaxurl, data, callback );
		} );
	};

	api.color_picker = function( $context ) {
		if ( typeof $.fn.tfminicolors !== 'function' ) {
			return;
		}
		// color picker
		$context.find( '.colorSelectInput' ).each(function(){
			var args = {},
				$this = $( this ),
				format = $this.data( 'format' );
			if( format == 'rgba' ) {
				args.format = 'rgb';
				args.opacity = true;
			} else if( format == 'rgb' ) {
				args.format = 'rgb';
			}
			$( this ).tfminicolors( args );
		});

		// Set or clear color swatch based on input value
		// Clear swatch and input
		$context.find( '.clearColor' ).on( 'click', function() {
			$(this).parent().find('.colorSelectInput').tfminicolors('value', '');
			$(this).parent().find('.colorSelectInput').val('');
		});
	};

	api.date_picker = function( $context ) {
		$context.find( '.themifyDatePicker' ).each(function(){
			var $self = $(this),
				label = $self.data('label'),
				close = $self.data('close' ),
				dateformat = $self.data('dateformat' ),
				timeformat = $self.data('timeformat' ),
				timeseparator = $self.data('timeseparator' );

			$.fn.datetimepicker.call( $self, {
					showOn: 'both',
					showButtonPanel: true,
					closeButton: close,
					buttonText: label,
					dateFormat: dateformat,
					timeFormat: timeformat,
					stepMinute: 5,
					firstDay: $self.data( 'first-day' ),
					separator: timeseparator,
					onClose: function( date ) {
						if ( '' != date ) {
							$( '#' + $self.data('clear') ).addClass('themifyFadeIn');
						}
					},
					beforeShow: function() {
						$('#ui-datepicker-div').addClass( 'themifyDateTimePickerPanel' );
					}
				});
			$self.next().addClass('button');
		});

		$context.find( '.themifyClearDate' ).on( 'click', function(e) {
			e.preventDefault();
			var $self = $(this);
			$( '#' + $self.data('picker') ).val('').trigger( 'change' );
			$self.removeClass('themifyFadeIn');
		});
	};

	api.assignments = function( $context ){
		$context.find( '.themify-assignments, .themify-assignment-inner-tabs' ).tabs();

		$context.on( 'change', '.themify-assignments input[type="checkbox"]', function() {
			var $this = $( this );
			if ( $this.is( ':checked' ) ) {
				$this.closest( '.themify-assignments' ).find( '.values' ).append( '<input type="hidden" name="' + $this.attr( 'data-name' ) + '" value="on" />' );
			} else {
				$this.closest( '.themify-assignments' ).find( '.values input[type="hidden"][name="' + $this.attr( 'data-name' ) + '"]' ).remove();
			}
		} );
	};

	api.layout = function( $context ) {
		$context.find( '.preview-icon' ).each( function() {
			var $self = $(this),
				$parent = $self.parent(),
				$val = $parent.find('.val'),
				$dataHide,
				dataHide = '',
				context = '';

			if ( $self.closest('.group-hide').length > 0 ) {
				context = 'theme-settings';
				$dataHide = $self.closest('.group-hide');
				dataHide = $dataHide.data( 'hide' );
			} else if ( $self.closest('.themify_field_row').length > 0 ) {
				context = 'custom-panel';
				$dataHide = $self.closest('.themify_field_row');
				if ( 'undefined' !== typeof $dataHide.data( 'hide' ) ) {
					dataHide = $dataHide.data( 'hide' );
				}
			}

			$self.on('click',function(e){
				e.preventDefault();

				// Change value
				$parent.find('.selected').removeClass('selected');
				$self.addClass('selected');
				$val.val( $self.find('img').attr('alt') ).trigger('change');

				// There are elements to show/hide so do it
				if ( '' !== dataHide ) {
					if ( 'custom-panel' == context ) {
						// All until next data-hide, minus toggled-off those are nested and handled by toggle code, minus items not in list to hide
						var $list = $dataHide.nextUntil('[data-hide]');
						$list.add( $list.find( '.themify_field .hide-if' ) ).not('.toggled-off').filter( '.' + dataHide.replace( /\s/g, ',.' ) ).show().filter( '.' + $val.val() ).hide();
					} else if ( 'theme-settings' == context ) {
						$dataHide.find('.hide-if').filter( '.' + dataHide.replace( /\s/g, ',.' ) ).show().filter( '.' + $val.val() ).hide();
					}
				}

			});

			// All until next data-hide, minus toggled-off those are nested and handled by toggle code, minus items not in list to hide
			if ( '' !== dataHide ) {
				if ( 'custom-panel' == context ) {
					var $list = $dataHide.nextUntil('[data-hide]');
					$list.add( $list.find( '.themify_field .hide-if' ) ).not('.toggled-off').filter( '.' + dataHide.replace( /\s/g, ',.' ) ).filter( '.' + $val.val() ).hide();
				} else if ( 'theme-settings' == context ) {
					$dataHide.find('.hide-if').filter( '.' + dataHide.replace( /\s/g, ',.' ) ).show().filter( '.' + $val.val() ).hide();
				}
			}

		});

		/**
		 * Map layout icons to values and bind clicks
		 */
		$context.find( ".themify_field .preview-icon" ).on( 'click', function(e){
			e.preventDefault();
			$(this).parent().find(".selected").removeClass("selected");
			$(this).addClass("selected");
			$(this).parent().find(".val").val($(this).find("img").attr("alt")).trigger('change');
		});

		$context.find( '.themify_field_row[data-hide]' ).each( function() {
			var dataHide = $( this ).data( 'hide' ),
				hideValues, $selector;

			if( typeof dataHide === 'string' ) {
				dataHide = dataHide.split( ' ' );

				if( dataHide.length > 1 ) {
					hideValues = dataHide.shift();
					hideValues = hideValues.split( '|' );
					$selector = $( '.' + dataHide.join( ', .' ) );

					$( 'select, input', this ).on( 'change', function() {
						var value = $( this ).val();

						if( ! hideValues.includes( value ) && $selector.is( ':visible' ) ) return;
						$selector.toggle( ! hideValues.includes( value ) );
					} ).trigger( 'change' );
				}
			}
		} );
	};

	api.dropdownbutton = function( $context ) {
		$context.find( '.dropdownbutton-group' ).each(function(){
			var $elf = $(this);
			$elf.on('mouseenter mouseleave', '.dropdownbutton-list', function(event){
				event.preventDefault();
				var $a = $(this);
				if($a.hasClass('disabled')) {
					return false;
				}
				if(event.type === 'mouseenter') {
					if(!$a.children('.dropdownbutton').is(':visible')) {
						$a.children('.dropdownbutton').show();
					}
				}
				else if(event.type === 'mouseleave' && $a.children('.dropdownbutton').is(':visible')) {
                                    $a.children('.dropdownbutton').hide();
				}
			})
                        .on('click', '.first-ddbtn a', function(event){
				event.preventDefault();
			})
                        .on('click', '.ddbtn a', function(event){
				event.preventDefault();
				var ddimgsrc = $(this).find('img').attr('src'),
					val = $(this).data('val'),
					parent = $(this).closest('.dropdownbutton-list'),
					inputID = parent.attr('id');
				$(this).closest('.dropdownbutton-list').find('.first-ddbtn img').attr('src', ddimgsrc);
				$(this).closest('.dropdownbutton').hide();
				$('input#' + inputID).val(val);
				if(parent.next().hasClass('ddbtn-all')) {
					var $ddbtnList, $ddbtnInput;
					if($elf.hasClass('multi-ddbtn')) {
						$ddbtnList = $('.multi-ddbtn-sub', $elf.parent().parent());
						$ddbtnInput = $('.multi-ddbtn-sub + input', $elf.parent().parent());
					} else {
						var inputVal = parent.next();
						$ddbtnList = inputVal.prev().siblings('.dropdownbutton-list');
						$ddbtnInput = inputVal.siblings('input');
					}

					if(parent.next().val() == 'yes') {
						$ddbtnList.addClass('disabled opacity-5');
						$ddbtnList.each(function(){
							var defIcon = $(this).data('def-icon');
							$(this).find('.first-ddbtn img').attr('src', defIcon);
						});
						$ddbtnInput.val(''); // clear value
					} else {
						$ddbtnList.removeClass('disabled opacity-5');
					}

				}
			});
			// disabled other options on dom load
			var selectAll = $elf.find('input.ddbtn-all');
			if( selectAll.val() === 'yes' ) {
				if($elf.hasClass('multi-ddbtn')) {
					$('.multi-ddbtn-sub', $elf.parent().parent()).addClass('disabled opacity-5');
				} else {
					selectAll.prev().siblings('.dropdownbutton-list').addClass('disabled opacity-5');
				}
			}
		});
	};

	api.togglegroup = function ($context) {
		$context.find('.themify_toggle_group_wrapper').each(function () {
			var $this = $(this);
			$this.find('.themify_toggle_group_inner').hide();
			$this.on('click', '.themify_toggle_group_label', function (event) {
				var $a = $(this),
					$icon = $a.find('i'),
					$b = $a.next();
				if ($b.hasClass('is-activated')) {
					$icon.removeClass('ti-minus').addClass('tf_plus_icon');
					$b.hide().removeClass('is-activated');
				} else {
					$b.show().addClass('is-activated');
					$icon.removeClass('tf_plus_icon').addClass('ti-minus');
				}
				event.preventDefault();
			});
		});
	};


	api.gallery_shortcode = function(){
		var clone = wp.media.gallery.shortcode, wpgallery = wp.media.gallery, file_frame, frame;

		$( 'body' ).on( 'click', '.themify-gallery-shortcode-btn', function(event) {
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
	
			if ($.trim(shortcode_val.val()).length == 0) {
				$('.media-menu').find('.media-menu-item').last().trigger('click');
			}
			event.preventDefault();
		});
	};

	api.set_cookie = function (name, value) {
		document.cookie = name+"="+value+";SameSite=strict;path=/";
	};

	api.get_cookie = function (name) {
		name = name + "=";
		let ca = document.cookie.split(';');
		for(var i=0,len=ca.length; i < len; ++i) {
			let c = ca[i];
			while (' ' == c.charAt(0)) c = c.substring(1,c.length);
			if (0 == c.indexOf(name)) return c.substring(name.length,c.length);	}
		return null;
	};

	// @deprecated
	// revision needed
	api.enable_toggle = function(){
		var $enableToggle = $( '.enable_toggle' );
		if($enableToggle.length > 0){
			$enableToggle.each(function(){
				var context = $(this).closest('.themify_write_panel');
				if ( ! context.length ) {
					context = $( this ).closest( 'form' );
				}
				$('.themify-toggle', context).hide().addClass('toggled-off');
			});
		}
		$('.enable_toggle .preview-icon').on('click', function(e){
			// toggle
			var img_alt = $(this).find("img").attr("alt"),
				toggle_class = ($.trim(img_alt) != '') ? '.'+img_alt+'-toggle' : '.default-toggle';
			$(this).closest('.inside').find('.themify-toggle').hide().addClass('toggled-off');
			$(this).closest('.inside').find( toggle_class ).show().removeClass('toggled-off');
			e.preventDefault();
		});
		$('.enable_toggle .preview-icon.selected').each(function(){
			var img_alt = $(this).find("img").attr("alt"),
				toggle_class = (img_alt != '' && img_alt != 'default') ? '.'+img_alt+'-toggle' : '.default-toggle';
			$( toggle_class ).show().removeClass('toggled-off');
		});

		// Toggle Post Format Fields by Radio Button
		$('.enable_toggle input[type=radio]').on('click', function() {
			var val = $(this).val(),
				toggle_class = (val != 0 && val != '') ? '.'+val+'-toggle' : '.default-toggle',
				$siblings = $(this).siblings('input[type=radio]');

			$siblings.each(function(){
				var sib_val = $(this).val();
				if ( sib_val != 0 && sib_val !== '' ) $( '.' + sib_val + '-toggle').hide().addClass('toggled-off');
			});

			$(toggle_class).each(function(){
				$(this).show().removeClass('toggled-off');
				if ( $(this).hasClass('enable_toggle_child') ) {
					var $child_siblings = $(this).find('input[type=radio]:checked').siblings('input[type=radio]');
					$child_siblings.each(function(){
						var sib_val = $(this).val();
						setTimeout(function(){
							if ( sib_val != 0 && sib_val !== '' ) $( '.' + sib_val + '-toggle').hide().addClass('toggled-off');
						}, 500);
					});
				}
			});
		});
		$enableToggle.each(function(){
			var $checked = $(this).find('input[type="radio"]:checked'),
				val = $checked.val(),
				toggle_class = (val != 0 && val !== '') ? '.'+val+'-toggle' : '.default-toggle';
			
			$(toggle_class).each(function(){
				$(this).show().removeClass('toggled-off');
				if ( $(this).hasClass('enable_toggle_child') ) {
					var $child_siblings = $(this).find('input[type=radio]:checked').siblings('input[type=radio]');
					$child_siblings.each(function(){
						var sib_val = $(this).val();
						setTimeout(function(){
							if ( sib_val != 0 && sib_val !== '' ) $( '.' + sib_val + '-toggle').hide().addClass('toggled-off');
						}, 500);
					});
				}
			});
		});

		// Toggle Post Format Fields by Checkbox.
		// Works with single checkbox selection, not yet with combinations.
		$('.enable_toggle input[type="checkbox"]').on('click', function() {
			var val = $(this).data('val'),
				toggle_class = (val != 0 && val != '') ? '.'+val+'-toggle' : '.default-toggle';

			$(this).closest('.inside').find('.themify-toggle').hide().addClass('toggled-off');

			if($(this).prop('checked')){
				$(this).closest('.inside').find( toggle_class ).show().removeClass('toggled-off');
			}
		});
		$('.enable_toggle input[type="checkbox"]:checked').each(function() {
			var val = $(this).data('val'),
				toggle_class = (val != 0 && val !== '') ? '.'+val+'-toggle' : '.default-toggle';
			$( toggle_class ).show().removeClass('toggled-off');
		});
	};

	api.query_category = function(){
		/**
		 * Bind categories select to value field
		 */
		var $themifyField = $('.themify_field'),
			$themifyInfoLink = $('.themify-info-link');

		$themifyField.find('.query_category').on('blur',function(){
                    var $self = $(this), value = $self.val();
                    $(this).parent().find('.val').val( value );
                    toggleQueryCategoryFields( $self, value );
		}).on('keyup',function(){
                    var $self = $(this), value = $self.val();
                    $(this).parent().find('.val').val( value );
                    toggleQueryCategoryFields( $self, value );
		});

		$themifyField.find('.query_category_single').on('change',function() {
			var $self = $(this), value = $self.val();
			$self.parent().find('.query_category, .val').val( value );
			toggleQueryCategoryFields( $self, value );
		}).closest('.themify_field_row').addClass('query-field-visible');
		$themifyInfoLink.closest('.themify_field_row').addClass('query-field-visible');

		$('.query_category_single, .query_category').each(function(){
			var $self = $(this), value = $self.val();
			toggleQueryCategoryFields( $self, value );
		});
		$themifyInfoLink.closest('.themify_field_row').removeClass('query-field-hide');

		function toggleQueryCategoryFields( $obj, value ) {
			if ( '' != value ) {
				$obj.closest('.inside').find('.themify_field_row').removeClass('query-field-hide');
			} else {
				$obj.closest('.inside').find('.themify_field_row').not( $obj.closest( '.themify_field_row' ) ).not('.query-field-visible').addClass('query-field-hide');
			}
		}
	};

	api.post_meta_checkbox = function() {
		$('.post-meta-group').each(function(){
			var $elf = $(this);
			if($('.meta-all', $elf).prop('checked')){
				$('.meta-sub', $elf).prop('disabled', true).parent().addClass('opacity-7');
			}
			$elf.on('click', '.meta-all', function(){
				var $all = $(this);
				if($all.prop('checked')){
					//$all.prop('checked', true);
					$('.meta-sub', $elf).prop('disabled', true).prop('checked', false).parent().addClass('opacity-7');
				} else {
					//$all.prop('checked', false);
					$('.meta-sub', $elf).prop('disabled', false).parent().removeClass('opacity-7');
				}
			});
		});

		/**
		* Post meta checkboxes - Mostly the same than before, but adding hidden field update.
		*/
		$('.custom-post-meta-group').each(function(){
			var $elf = $(this),
				states_str = $('input[type="text"]', $elf).val(),
				states = {},
				state = [],
				states_arr = [];

			// Backwards compatibility
			if('yes' === states_str){
				$('.meta-all', $elf).val('yes').prop('checked', true);
				$('.meta-sub', $elf).val('yes').prop('disabled', true).parent().addClass('opacity-7');
			} else {
				// Parse string
				states_arr = states_str.split('&');
				for (var i = 0; i < states_arr.length; i++) {
					state = states_arr[i].split('=');
					states[state[0]] = state[1];
				}
				for ( var meta in states ) {
					if ( 'yes' === states[meta] ) {
                                            $('#' + meta, $elf).val('yes').prop('checked', true);
					}
				}
				if($('.meta-all', $elf).prop('checked')){
					$('.meta-sub', $elf).prop('disabled', true).prop('checked', false).parent().addClass('opacity-7');
				}
			}
			$elf.on('click', '.meta-all', function(){
				var $all = $(this);
				if($all.prop('checked')){
					$('.meta-sub', $elf).val('yes').prop('disabled', true).prop('checked', false).parent().addClass('opacity-7');
					$all.val('yes');
				} else {
					$('.meta-sub', $elf).val('no').prop('disabled', false).parent().removeClass('opacity-7');
					$all.val('no');
				}
				savePostMetaStates($elf);
			})
                        .on('click', '.meta-sub', function(){
				var $sub = $(this);
				if($sub.prop('checked')){
					$sub.val('yes');
				} else {
					$sub.val('no');
				}
				savePostMetaStates($elf);
			});
		});
	};

	api.pagination = function ($context) {
		$context.on('click', '#themify_assignments_popup_show .themify-popup-visibility-tab', function(e) {
			e.preventDefault();
			var $this = $(this);
			if ($this.data('active')) {
				return;
			}
			var type = $this.data('type'),
                            tab = $this.parents('#themify_assignments_popup_show').find('.themify-assignment-type-options[data-type=' + type + ']'),
                            post_id = $('#popup_show-assignment-tab-pages').data('post-id');
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'themify_create_inner_popup_page',
					type: type,
					post_id: post_id,
				},
				beforeSend: function() {
					// tab.html('<div class="tb_slider_loader"></div>');
				},
				success: function(data){
					tab.html(data);
					$this.data('active', 'on');
				}
			});
		});

		$context.on('click', '.themify-assignment-pagination .page-numbers', function(e) {
			e.preventDefault();
			var $this = $(this),
                            tab = $this.parents('.themify-assignment-options'),
                            items_inner = $this.parents('.themify-assignment-items-inner'),
                            pagination = $('.themify-assignment-pagination', items_inner),
                            current_page = parseFloat($('.themify-assignment-pagination .current', items_inner).text()),
                            inner_item = $('.themify-assignment-items-inner', tab),
			 go_to_page = 1;
			if ($this.hasClass('next')) {
				go_to_page = current_page + 1;
			} else if ($this.hasClass('prev')) {
				go_to_page = current_page - 1;
			} else if($this.hasClass('page-numbers')) {
				go_to_page = parseFloat($this.text());
			}
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'themify_create_popup_page_pagination',
					current_page: go_to_page,
					num_of_pages: items_inner.data('pages'),
				},
				beforeSend: function() {
					$('.tb_slider_loader', tab).remove();
					$('.themify-assignment-items-page', items_inner).addClass('is-hidden');
					pagination.hide();
					inner_item.append('<div class="tb_slider_loader"></div>');
				},
				success: function(data) {
					$('.tb_slider_loader', tab).remove();
					$('.themify-assignment-items-page-' + go_to_page, items_inner).removeClass('is-hidden');
					pagination.html(data).show();
				}
			});
		});
	};

	api.page_layout_field = function () {
		const el = document.getElementsByClassName( 'themify_field-page_layout' );
		if ( el[0] ) {
			const input = el[0].querySelector( 'input[type="hidden"]' ),
				content_width = document.getElementById( 'content_width' ),
				section_scroll = document.getElementById( 'section_full_scrolling' ),
				hide_title = document.getElementById( 'hide_page_title' ),
				section_scroll_settings = document.getElementsByClassName( 'tf_section_scroll_setting' ),
				update_page_layout_options = function( value ) {
					/* toggle section scroll options */
					const display = value === 'section_scroll' ? 'block' : 'none';
					for ( let i = section_scroll_settings.length - 1; i > -1; --i ) {
						section_scroll_settings[ i ].style.display = display;
					}

					if ( section_scroll ) {
						section_scroll.value = value === 'section_scroll' ? 'yes' : 'no';
					}
					if ( content_width ) {
						content_width.value = value === 'section_scroll' || value === 'full_width' ? 'full_width' : '';
					}
					if ( value === 'section_scroll' || value === 'full_width' ) {
						input.value = 'sidebar-none'; /* change the value saved in db, for backward compatibility */
						if ( hide_title ) {
							hide_title.value = 'yes';
						}
					}
				};

			var observer = new MutationObserver(function( mutations, observer ) {
				if ( mutations[0].attributeName == 'value' ) {
					observer.disconnect();
					update_page_layout_options( input.value );
					observer.observe( input, { attributes: true } );
				}
			});
			update_page_layout_options( input.dataset.selected );
			observer.observe( input, { attributes: true } );
		}
	};

	function savePostMetaStates( $et ) {
		let state = '';
		$('input[type="checkbox"]', $et).each(function(){
			state += $(this).attr('id') + '=' + $(this).val() + '&';
		});
		$('input[type="text"]', $et).val(state.slice(0,-1));
	}

	api.init();
	return api;
})(jQuery);
