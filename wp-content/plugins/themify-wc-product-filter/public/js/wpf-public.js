(function ($) {
    'use strict';
    var in_scroll = false;
	var infinitybuffer = 300; /* how soon wpf will load the next set of products. Higher number means sooner */

    function triggerEvent(a, b) {
        var c;
        document.createEvent ? (c = document.createEvent("HTMLEvents"), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
    }

	/**
	 * Loads a script file if test() parameter passes, then calls callback()
	 *
	 * @param src string
	 * @param test function to check whether script needs to be loaded
	 * @param callback function
	 */
	var load_script = function( src, test, callback ) {
		if (test && test() === true) {
			if (callback) {
				callback();
			}
			return;
		}
		const s = document.createElement('script');
		s.setAttribute('async', 'async');
		s.onload = function () {
			if (callback) {
				callback();
			}
			const key = this.getAttribute('id');
		};
		document.head.appendChild(s);
		s.setAttribute('src', src);
	};

	/**
	 * load multiple script files, then calls callback() when all are loaded
	 *
	 * @param scripts array multidimensional
	 * @param callback function
	 */
	var load_scripts = function( scripts, callback ) {
		if ( scripts.length === 0 ) {
			callback();
			return;
		}

		load_script( scripts[0][0], scripts[0][1], function() {
			scripts.shift();
			load_scripts( scripts, callback );
		} );
	};

	var load_slider = function( callback ) {
		var jquery_path = wpf.includes_url + 'js/jquery/';
		var scripts = [
			[ jquery_path + 'ui/core.min.js', function() { return typeof $.ui === 'object' } ],
			[ jquery_path + 'ui/mouse.min.js', function() { return typeof $.ui.mouse === 'function' } ],
			[ jquery_path + 'jquery.ui.touch-punch.js', function() { return ! ( 'ontouchstart' in window ); } ],
			[ jquery_path + 'ui/slider.min.js', function() { return typeof $.ui.slider === 'function' } ]
		];

		/* compatibility with WP 5.5.3 or lower */
		if ( wpf.load_jquery_ui_widget ) {
			scripts.unshift( [ jquery_path + 'ui/widget.min.js', function() { return typeof $.widget === 'function' } ] );
		}

		load_scripts( scripts, function() {
			callback();
		} );
	};

    var InitSlider = function ( container ) {
		if ( ! $.fn.slider ) {
			return;
		}
		$( '.wpf_slider', container ).each(function () {
			var $wrap = $(this).closest('.wpf_item'),
				$min = $wrap.find('.wpf_price_from'),
				$max = $wrap.find('.wpf_price_to'),
				$min_val = parseInt($(this).data('min')),
				$max_val = parseInt($(this).data('max')),
				step = parseFloat($(this).data('step')),
				$form = $wrap.closest('form'),
				$v1 = parseInt($min.val()),
				$v2 = parseInt($max.val());
			var $label_min = $wrap.find( '.wpf-price-min' ),
				$label_max = $wrap.find( '.wpf-price-max' );
			// check for valid numbers in data-min and data-max
			if ($min_val === parseInt($min_val, 10) && $max_val === parseInt($max_val, 10)) {
				$v1 = $v1 ? $v1 : $min_val;
				$v2 = $v2 ? $v2 : $max_val;
				$(this).slider({
					range: true,
					min: $min_val,
					step: isNaN(step) || step <= 0 ? 1 : step,
					max: $max_val,
					values: [$v1, $v2],
					slide: function (event, ui) {
						$label_min.text( ui.values[ 0 ] );
						$label_max.text( ui.values[ 1 ] );
					},
					stop: function (event, ui) {
						$min.val(ui.values[ 0 ]);
						$max.val(ui.values[ 1 ]);
						if ($form.hasClass('wpf_submit_on_change')) {
							$form.trigger( 'submit' );
						}
					}
				});
				$(this).slider().on('slidechange',function(event,ui){
					$(ui.handle).find('.wpf_tooltip_amount').html(ui.value);
				});
			}
		});
    };

    var InitGroupToggle = function ( container ) {
        $( 'body' ).off( 'click.wpfGroupToggle' ).on( 'click.wpfGroupToggle', '.wpf_grouped_label', function (e) {
            var $wrap = $(this).next('.wpf_items_group'),
				$this = $(this);
			e.preventDefault();
			if ($wrap.is(':visible')) {
				$wrap.slideUp(function () {
					$this.addClass('wpf_grouped_close');
				});
			}
			else {
				$wrap.slideDown(function () {
					$this.removeClass('wpf_grouped_close');
				});
			}
        });

		$( '.wpf_items_grouped:not(.wpf_layout_horizontal) .wpf_item_name', container ).each( function() {
			if ( $( this ).closest( '.wpf_item_onsale, .wpf_item_instock' ).length ) {
				return;
			}
			$( this ).addClass( 'wpf_grouped_label' ).trigger( 'click' );
		} );
    };

	var getProductsContainer = function( context = null ) {
		var $container = $( '.wpf-search-container', context ).first();
		if ( $container.length === 0 ) {
			$container = $( '.wc-products.loops-wrapper', context ).parent(); // Themify Builder WooCommerce
			if ( $container.length === 0 ) {
				$container = $( '.woocommerce ul.products', context ).parent();
				if ( $container.length === 0 ) {
					$container = $( 'ul.products', context ).parent();
					if ( $container.length === 0 ) {
						$container = $( '.post', context ).first();
					}
				}
			}
		}

		return $container.first().addClass( 'wpf-search-container' );
	};

    var InitSubmit = function () {
        var masonryData, isMasonry;

        $( 'body' ).off( 'submit.wpfForm' ).on( 'submit.wpfForm', '.wpf_form', function (e) {
            e.preventDefault();
            var $form = $(this),
				$container = getProductsContainer(),
				data = $form.serializeArray(),
				result = {},
				scroll_to_result = $form.hasClass( 'wpf_form_scroll' );
            for (var i in data) {
                if ( data[i].value.trim() ) {
                    var name = data[i].name.replace('[]', '');
					
                    if (!result[name]) {
                        result[name] = data[i].value;
                    }
                    else {
                        result[name] += ',' + data[i].value;
                    }
                }
            }
            if (in_scroll) {
               result['append'] = 1;
            }
            $form.find('input[name="wpf_page"]').val('');

            // Save isotope data if masonry is enabled
			if ( $container.length && typeof Isotope === 'function' ) {
				const productsWrap = $container[0].getElementsByClassName( 'products' );
				isMasonry = isMasonry || ( productsWrap.length>0 && Isotope.data( productsWrap[0] ) );
			}

			if ( $form.hasClass( 'wpf_form_ajax' ) && ! $container.length ) {
				/**
				 * result is supposed to display on the same page, but there's no loop to display it;
				 * post the data to Shop page
				 */
				$form.removeClass( 'wpf_form_ajax' ).attr( 'action', $form.attr( 'data-shop' ) );
			}

			var currentUrl = new URL( $form.prop( 'action' ) );
			for ( const i in result ) {
				currentUrl.searchParams.set( i, result[ i ] );
			}

            if ( ! $form.hasClass( 'wpf_form_ajax' ) ) {
				window.location = currentUrl.toString();
				return false;
            }
			currentUrl.searchParams.set( 'wpf_ajax', 1 );

            $.ajax({
                url: currentUrl.toString(),
                type: 'GET',
                beforeSend: function () {
                    document.body.classList.add( 'wpf_loading' );
                    $form.addClass( 'wpf-search-submit' );
                    $container.addClass( 'wpf-container-wait' );
                },
                complete: function () {
					document.body.classList.remove( 'wpf_loading' );
                    $form.removeClass( 'wpf-search-submit' );
                    $container.removeClass( 'wpf-container-wait' );
                },
                success: function (resp) {
                    if (resp) {
						currentUrl.searchParams.delete( 'wpf_ajax' );
                        history.replaceState({}, null, currentUrl.toString() );

                        var scrollTo = $container,
							products = null,
                            containerClass = $('.products', $container).attr('class'),
                            $resp = $( resp ),
							$resp_container = getProductsContainer( $resp );
                        $container.data('slug', $form.data('slug'));
                        $.event.trigger( 'wpf_ajax_before_replace' );
                        if ( in_scroll ) {
							products = $resp_container.find( '.product' );
                            products.addClass('wpf_transient_product')
								.removeClass( 'first last' ); // remove grid classes

                            $( '.products', $container ).first().append( products );
							var columns = containerClass.match( /columns-(\d)/ );
							/* add proper "first" & "last" classes to the products */
							if ( columns !== null ) {
								columns = parseInt( columns[1] );
								$( '.products', $container ).first()
									.find( '.product:nth-child(' + columns + 'n+1)' ).addClass( 'first' )
									.end().find( '.product:nth-child(' + columns + 'n)' ).addClass( 'last' );
							}

                            var scroll = $resp.find('.wpf_infinity a');

                            if(scroll.length > 0){
                                $('.wpf_infinity a',$container).data({
                                    current : scroll.data('current'),
                                    max : scroll.data('max')
                                });
                            }

                            $container.removeClass('wpf-infnitiy-scroll');
                            scrollTo = products.first();
                            delete result['append'];
                            setTimeout(function(){
                                in_scroll = false;
                            },200);
                        } else {
							// remove existing pagination links
							$( '.wpf-pagination' ).remove();

							if ( $resp_container.find( '.product' ).length ) {
								$container.empty().append( $resp_container.removeAttr( 'class' ) );
								wpfInit( $container );
							} else {
								// 404, no products matching the selection found
								$container.empty().append( $form.find( '.wpf-no-products-found' ).clone().show() );
							}
                        }

                        if( isMasonry && typeof Themify?.isoTop === 'function' ) {
							Themify.isoTop( $container.find( '.products' ) );
                        } else if ( scroll_to_result ) {
                            ToScroll(scrollTo);
                        }
						if ( products !== null ) {
							products.addClass('wpf_transient_end_product');
						}

                        if ( window.wp !== undefined && window.wp.mediaelement !== undefined ) {
                            window.wp.mediaelement.initialize();
                        }
                        $.event.trigger( 'wpf_ajax_success' );
                        triggerEvent(window, 'resize');
                    }
                }
            });
        });
    };

    var ToScroll = function ($container) {
        if ($container.length > 0) {
            $('html,body').animate({
                scrollTop: $container.offset().top - $('#wpadminbar').outerHeight(true) - 10
            }, 1000);
        }
    };

	var infinityEl = $(); /* element to check scroll off of */
    var infinity = function (e, click) {
		if ( ! infinityEl.length ) {
			infinityEl = getProductsContainer();
		}
		if ( ! in_scroll && (
			click
			|| ( window.scrollY > infinityEl.offset().top + infinityEl.outerHeight() - infinitybuffer ) // scroll past the products container
			|| ( ( window.innerHeight + window.pageYOffset ) >= document.body.offsetHeight ) // reach bottom of the page
		) ) {
            var container = $('.wpf-search-container'),
				scroll = $('.wpf_infinity a', container),
				$form = $('.wpf_form_' + container.data('slug'));
            if ( ! $form.length ) {
				$form = $( '.wpf_form:first' );
			}
            if ( $form.length ) {
                var current = scroll.data('current');
                if (current <= scroll.data('max')) {
                    $form.find('input[name="wpf_page"]').val(current);
                    in_scroll = true;
                    if (!click) {
                        container.addClass('wpf-infnitiy-scroll');
                    }
                    submit_form( $form );
                    if (((current + 1) > scroll.data('max'))) {
                        $('.wpf_infinity').remove();
                        if (!click) {
                            $(this).off('scroll', infinity);
                        }
                    }
                }
            }
        }
    };


    var InitPagination = function () {
        function find_page_number(element) {
            var $page = parseInt(element.text());
            if ( ! $page ) {
                $page = parseInt(element.closest('.woocommerce-pagination,.pagenav').find('.current').text());
                if (element.hasClass('next')) {
                    ++$page;
                } else {
                    --$page;
                }
                var pattern = new RegExp( '(?<=paged=)[^\b\s\=]+' );
                if( ! $page && pattern.test( element.attr( 'href' ) ) ) {
                        $page = element.attr( 'href' ).match( pattern )[0];
                }
            }

            return $page;
        }
        if ($('.wpf_infinity_auto').length > 0) {
            $('#load-more').remove();
            $(window).off('scroll', infinity).on('scroll', infinity);
        }
        else if ($('.wpf_infinity').length > 0) {
			$('.wpf_infinity').closest('.wpf-hide-pagination').removeClass('wpf-hide-pagination');
            $('#load-more').remove();
            $( 'body' ).off( 'click.wpfInfinity' ).on( 'click.wpfInfinity', '.wpf_infinity a', function (e) {
                e.preventDefault();
                e.stopPropagation();
                infinity(e, 1);
            });
        }
        else {
            $( 'body' ).off( 'click.wpfPagination' ).on( 'click.wpfPagination', '.wpf-pagination a,.woocommerce-pagination a', function (e) {
                if("1" == new URL(window.location.href).searchParams.get("wpf")){
                    var $slug = $(this).closest('.wpf-search-container').data('slug'),
                        $form = $('.wpf_form_' + $slug);
                    if ($form.length > 0 && $form.find('input[name="wpf_page"]').length > 0) {
                        e.preventDefault();
                        $form.find('input[name="wpf_page"]').val(find_page_number($(this)));
                        submit_form( $form );
                    }
                }
            });
        }
    };

	/**
	 * Submit a WPF form, disables scrolling before submit
	 */
	function submit_form( $form ) {
		var scroll = $form.hasClass( 'wpf_form_scroll' );
		$form.removeClass( 'wpf_form_scroll' );
		$form.trigger( 'submit' );
		if ( scroll ) {
			$form.addClass( 'wpf_form_scroll' );
		}
	}

	/* decode HTML entities */
	var decodeEntities = function( string ) {
		var textarea = document.createElement( 'textarea' );
		textarea.innerHTML = string;
		return textarea.innerText;
	}

	var isValidUrl = function( string ) {
		try {
			new URL(string);
		} catch (_) {
			return false;  
		}

		return true;
	}

	/**
	 * Loads jQuery UI AutoComplete library and calls callback()
	 */
	var load_autocomplete = function( callback ) {
		var jquery_ui_path = wpf.includes_url + 'js/jquery/ui/';
		var scripts = [
			[ jquery_ui_path + 'core.min.js', function() { return typeof $.ui === 'object' } ],
			[ jquery_ui_path + 'position.min.js', function() { return typeof $.ui.position === 'object' } ],
			[ jquery_ui_path + 'menu.min.js', function() { return typeof $.ui.menu === 'function'; } ],
			[ jquery_ui_path + 'autocomplete.min.js', function() { return typeof $.ui.autocomplete === 'function' } ]
		];

		/* compatibility with WP 5.5.3 or lower */
		if ( wpf.load_jquery_ui_widget ) {
			scripts.unshift( [ jquery_ui_path + 'widget.min.js', function() { return typeof $.widget === 'function' } ] );
		}

		load_scripts( scripts, function() {
			callback();
		} );
	};

    var InitAutoComplete = function ( container ) {
		if ( ! $.fn.autocomplete ) {
			return;
		}
        var cache = [];
        $( '.wpf_autocomplete input', container ).each(function () {
            var $this = $(this),
				$key = $this.closest('.wpf_item_sku').length > 0 ? 'sku' : 'title',
				$spinner = $this.next('.wpf-search-wait'),
				$form = $this.closest('form'),
				$submit = $form.hasClass('wpf_submit_on_change');
            cache[$key] = [];
            $(this).autocomplete({
                minLength: 0,
                classes: {
                    "ui-autocomplete": "highlight"
                },
                source: function (request, response) {
                    var term = $.trim(request.term);
                    if ($submit && term.length === 0 && request.term.length === 0) {
                        $form.trigger( 'submit' );
                    }
                    if (term.length < 1) {
                        return;
                    }
                    request.term = term;
                    term = term.toLowerCase();
                    if (term in cache[$key]) {
                        response(cache[$key][ term ]);
                        return;
                    }
                    $spinner.show();
                    request.key = $key;
                    request.action = 'wpf_autocomplete';
					if ( $this.data( 'variation' ) === 'no' ) {
						request.variation = 'no';
					}

                    $.post(
						wpf.ajaxurl,
						request,
						function (data, status, xhr) {
							$spinner.hide();

							for ( const i in data ) {
								data[ i ]['label'] = decodeEntities( data[ i ]['label'] );
							}

							cache[$key][ term ] = data;
							response(data);
					},'json');

                },
                select: function (event, ui) {
					if ( isValidUrl( ui.item.value ) ) {
						window.location = ui.item.value;
					} else {
						$this.val( ui.item.value );
						if ($submit) {
							$form.trigger( 'submit' );
						}
					}
                    return false;
                }
            })
            .on( 'focus', function () {
                if ( $this.val().trim().length > 0) {
                    $(this).autocomplete("search");
                }

            })
            .autocomplete("widget").addClass("wpf_ui_autocomplete");
            ;
        });
    };

    var InitOrder = function () {
        function Order(val, obj) {
            var $slug = obj.closest('.wpf-search-container').data('slug'),
                    $form = $('.wpf_form_' + $slug);
            if ($form.length > 0 && $form.find('input[name="orderby"]').length > 0) {
                $form.find('input[name="orderby"]').val(val);
                $form.trigger( 'submit' );
            }
        }
        const $container = $('.wpf-search-container');
        $container.on('form.woocommerce-ordering', 'submit', function (e) {
            e.preventDefault();
            Order($(this).find('select').val(), $(this));

        });
        $container.find('select.orderby').on( 'change', function (e) {
            Order($(this).val(), $(this));
        });
        if (!$container.data('slug')) {
			$container.data('slug', $('.wpf_form').last().data('slug'));
        }
    };

    var InitChange = function ( container ) {
        if ( $( '.wpf_submit_on_change', container ).length > 0) {
            $( '.wpf_submit_on_change', container ).each(function () {
                var $form = $(this);
                $form.find('input[type!="text"],select').on( 'change', function (e) {
                    if( $(this).attr("name") == 'price' && $(this).is(":checked") ) {
                        $(".wpf_price_range label").removeClass("active");
						$(this).next("label").addClass("active");
                    }
                    $form.trigger( 'submit' );
                });

                $form.find('.wpf_pa_link').on( 'click', function (e) {
                    e.preventDefault();
                    $(this).find('input').prop('checked', true).trigger('change');
                });
            });
        }
    };

    var InitTabs = function ( container ) {
        var $horizontal = $( '.wpf_layout_horizontal', container );
        if ($horizontal.length > 0) {
            InitTabsWidth($horizontal);
            $horizontal.find('.wpf_item:not(.wpf_item_onsale):not(.wpf_item_instock)').hover(
                function () {
                    $(this).children('.wpf_items_group').stop().fadeIn();
                },
                function () {
                    var $this = $(this);
                    if ($this.closest('.wpf-search-submit').length === 0) {
                        var hover = true;
                        $( '.wpf_ui_autocomplete', container ).each(function () {
                            if ($(this).is(':hover')) {
                                hover = false;
                                return false;
                            }
                        });
                        if (hover) {
                            $this.children('.wpf_items_group').stop().fadeOut();
                        }
                    }
                }
            );
            if(navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/)){
                $horizontal.find('.wpf_item:not(.wpf_item_onsale):not(.wpf_item_instock) .wpf_item_name').click(function(){
                    var $parent = $(this).parent(),
                        isVisible = $parent.children('.wpf_items_group').is(':visible'),
                        touched = $parent.hasClass('wpf_touched');
                    if(isVisible && !touched){
                        $parent.addClass('wpf_touch_tap');
                        $parent.trigger('mouseleave');
                    }else if(!isVisible && !touched){
                        $parent.removeClass('wpf_touch_tap');
                        $parent.trigger('mouseenter');
                        $parent.removeClass('wpf_touched');
                    }else if(touched){
                        $parent.removeClass('wpf_touched');
                    }
                });
            }
            var interval;
            $(window).resize(function (e) {
				clearTimeout(interval);
				interval = setTimeout(function () {
					InitTabsWidth($horizontal);
				}, 500);
            });
        }
    };

    var InitTabsWidth = function ($groups) {
        $groups.each(function () {
			var $group = $( this );
            var $items = $group.find('.wpf_items_group'),
                    $middle = Math.ceil($items.length / 2),
                    last = $items.last().closest('.wpf_item'),
                    max = last.offset().left;
            $items.each(function () {
                var p = $(this).closest('.wpf_item');
                if (max < p.offset().left) {
                    last = p;
                    max = p.offset().left;
                }
            });
            var $firstPos = $items.first().closest('.wpf_item').offset().left - 2,
                    $lastPos = max + last.outerWidth(true);
            last = null;
            max = null;
            $items.each(function (i) {
				var parent_item = $(this).closest('.wpf_item'),
						left = parent_item.offset().left;
				if ( screen.width > 1000 ) {
					$( this ).css( 'left', '' );
					if (i + 1 >= $middle) {
						$(this).removeClass('wpf_left_tab').addClass('wpf_right_tab').outerWidth(Math.round(left + parent_item.width() - $firstPos));
					}
					else {
						$(this).removeClass('wpf_right_tab').addClass('wpf_left_tab').outerWidth(Math.round($lastPos - left));
					}
				} else {
					$(this).removeClass('wpf_right_tab').addClass('wpf_left_tab').outerWidth( Math.round( $group.width() ) ).css( 'left', ( left - $group.offset().left ) * -1 );
				}
            });

        });
    };

	/**
	 * Loads Select2 library and calls callback()
	 */
	var load_select2 = function( callback ) {
		load_script( wpf.url + 'js/select2.min.js', function() {
			return typeof $.fn.select2 === 'function';
		}, function() {
			callback();
		} );
	};

    var initSelect = function( container ) {
       if ( ! $.fn.select2 ) {
		   return;
	   }
        function clear(el,selected){
            var text = el.find('[value="'+selected+'"]').text();
            el.next('.select2').find('[title="'+text+'"]').addClass('wpf_disabled');
        }
        $( '.wpf_form', container ).find('select').each(function(){
            var el = $(this),
                is_multi = el.prop('multiple'),
                selected =  is_multi?el.data('selected'):false;
            el.select2({
                dir: wpf.rtl ? 'rtl' : 'ltr',
                minimumResultsForSearch: 10,
                dropdownCssClass: 'wpf_selectbox',
                allowClear: !selected && is_multi,
                placeholder:is_multi?'':false
            });

            if(selected && is_multi){
                clear(el,selected);
                el.on('change',function(e){
                    clear(el,selected);
                });
            }
        });
    };

    var initReset = function( container ) {
		$( '.wpf_reset_btn', container ).each( function() {
           this.addEventListener( 'click',function (e) {
                e.preventDefault();
                var target = e.target,
                    area = target.closest('.wpf_item');
                area = null === area ? target.closest('.wpf_form'):area;
                var inputs = area.querySelectorAll('input,select');
                for (var k = inputs.length-1; k>=0; k--) {
					if ( inputs[ k ].hasAttribute( 'readonly' ) ) {
						continue;
					}

                    if(inputs[k].tagName === 'INPUT'){
                        switch (inputs[k].type) {
                            case 'text':
                                inputs[k].value = '';
                                break;
                            case 'radio':
                            case 'checkbox':
                                inputs[k].checked = false;
                        }
                    }else{
                        inputs[k].selectedIndex = 0;
                        $(inputs[k]).val(null).trigger('change');
                    }
                }
                $(area).find('.wpf_slider').each(function () {
                    var $slider = $(this),
                        min = $slider.data('min'),
                        max = $slider.data('max');
                    $slider.siblings( ".wpf_price_from" ).val(min);
                    $slider.siblings( ".wpf_price_to" ).val(max);
					// update labels
					$slider.closest( '.wpf_item' ).find( '.wpf-price-min' ).text( min )
						.end().find( '.wpf-price-max' ).text( max );
                    $slider.slider("values", 0, min);
                    $slider.slider("values", 1, max);
                });
                $(target.closest('.wpf_form')).trigger( 'submit' );
            })
		} );
    };

	var InitPagination2 = function( context ) {
		if ( window.location.href.includes( 'wpf=' ) ) { // when WPF filter is applied
			/* remove pagination links not added by WPF */
			const container = context.hasClass( 'wpf-search-container' ) ? context : context.find( '.wpf-search-container' );
			$( '.woocommerce-pagination', container ).each( function() {
				if ( ! $( this ).closest( '.wpf-pagination' ).length ) {
					$( this ).addClass( 'wpf-hide' );
				}
			} );
		}

		$( '.wpf-pagination', context ).each( function() {
			$( this ).insertAfter( $( this ).parent() );
		} );
	}

	var isTouch = function() {
		return !! ( ( 'ontouchstart' in window ) || navigator.msMaxTouchPoints > 0 );
	}

	var wpfInit = function( container ) {
		container = container || $( 'body' );

		$( '.wpf_form', container ).css( 'visibility', 'visible' );
		InitTabs( container );
		InitGroupToggle( container );

		if ( $( '.wpf_form', container ).find( 'select' ).length && ! isTouch() ) {
			load_select2( function() {
				initSelect( container );
			} );
		}
		if ( $( '.wpf_slider', container ).length ) {
			load_slider( function() {
				InitSlider( container );
			} );
		}
		infinitybuffer = $( '.wpf_form' ).data( 'infinitybuffer' );
		InitPagination();
		InitOrder();
		InitChange( container );
		if ( $( '.wpf_autocomplete input', container ).length ) {
			load_autocomplete( function() {
				InitAutoComplete( container );
			} );
		}
		InitSubmit();
		initReset( container );
		InitPagination2( container );
	}

    window.addEventListener('load', function(){

        if ($('.wpf_form').length > 0) {

            $('body').addClass( 'woocommerce' );
            // Check for compatibility with Divi & Elementor
            var grid,
                diviConatainer = document.querySelector('.et_pb_module.et_pb_shop .woocommerce'),
                elementorConatainer = document.querySelector('.elementor-element.elementor-wc-products');
            if(null !== diviConatainer){
                diviConatainer.className += ' wpf-search-container';
                // Set Divi column
				var products = diviConatainer.querySelector( 'ul.products' );
				if ( products ) {
					grid = products.className.match(/columns-(\d+)/);
				}
            }else if(null !== elementorConatainer){
                elementorConatainer.className += ' wpf-search-container';
                // Set elementor column
                grid = elementorConatainer.querySelector('ul.products').className.match(/columns-(\d+)/);
            }else{
                // Try to get wc-products grid
                var container = document.querySelector('.wc-products');
                if(null!==container){
                    grid = container.className.match(/grid(\d+)/);
                }else{
                    container = document.querySelector('.woocommerce > .products');
                    if(null!==container){
                        grid = container.className.match(/columns-(\d+)/);
                        grid = null !== grid ? grid : container.parentElement.className.match(/columns-(\d+)/);
                    }
                }
            }
            if(null !== grid && undefined != grid){
                document.querySelector('[name="wpf_cols"]').value = grid[1];
            }

			wpfInit();
        }
	}, {once:true, passive:true});


}(jQuery));
