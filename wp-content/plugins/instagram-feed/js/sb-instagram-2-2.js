var sbi_js_exists = (typeof sbi_js_exists !== 'undefined') ? true : false;
if(!sbi_js_exists) {
  (function($){

    function sbiAddImgLiquid() {
      /*! imgLiquid v0.9.944 / 03-05-2013 https://github.com/karacas/imgLiquid */
      var sbi_imgLiquid = sbi_imgLiquid || {VER: "0.9.944"};
      sbi_imgLiquid.bgs_Available = !1, sbi_imgLiquid.bgs_CheckRunned = !1, function (i) {
        function t() {
          if (!sbi_imgLiquid.bgs_CheckRunned) {
            sbi_imgLiquid.bgs_CheckRunned = !0;
            var t = i('<span style="background-size:cover" />');
            i("body").append(t), !function () {
              var i = t[0];
              if (i && window.getComputedStyle) {
                var e = window.getComputedStyle(i, null);
                e && e.backgroundSize && (sbi_imgLiquid.bgs_Available = "cover" === e.backgroundSize)
              }
            }(), t.remove()
          }
        }

        i.fn.extend({
          sbi_imgLiquid: function (e) {
            this.defaults = {
              fill: !0,
              verticalAlign: "center",
              horizontalAlign: "center",
              useBackgroundSize: !0,
              useDataHtmlAttr: !0,
              responsive: !0,
              delay: 0,
              fadeInTime: 0,
              removeBoxBackground: !0,
              hardPixels: !0,
              responsiveCheckTime: 500,
              timecheckvisibility: 500,
              onStart: null,
              onFinish: null,
              onItemStart: null,
              onItemFinish: null,
              onItemError: null
            }, t();
            var a = this;
            return this.options = e, this.settings = i.extend({}, this.defaults, this.options), this.settings.onStart && this.settings.onStart(), this.each(function (t) {
              function e() {
                -1 === u.css("background-image").indexOf(encodeURI(c.attr("src"))) && u.css({"background-image": 'url("' + encodeURI(c.attr("src")) + '")'}), u.css({
                  "background-size": g.fill ? "cover" : "contain",
                  "background-position": (g.horizontalAlign + " " + g.verticalAlign).toLowerCase(),
                  "background-repeat": "no-repeat"
                }), i("a:first", u).css({
                  display: "block",
                  width: "100%",
                  height: "100%"
                }), i("img", u).css({display: "none"}), g.onItemFinish && g.onItemFinish(t, u, c), u.addClass("sbi_imgLiquid_bgSize"), u.addClass("sbi_imgLiquid_ready"), l()
              }

              function o() {
                function e() {
                  c.data("sbi_imgLiquid_error") || c.data("sbi_imgLiquid_loaded") || c.data("sbi_imgLiquid_oldProcessed") || (u.is(":visible") && c[0].complete && c[0].width > 0 && c[0].height > 0 ? (c.data("sbi_imgLiquid_loaded", !0), setTimeout(r, t * g.delay)) : setTimeout(e, g.timecheckvisibility))
                }

                if (c.data("oldSrc") && c.data("oldSrc") !== c.attr("src")) {
                  var a = c.clone().removeAttr("style");
                  return a.data("sbi_imgLiquid_settings", c.data("sbi_imgLiquid_settings")), c.parent().prepend(a), c.remove(), c = a, c[0].width = 0, void setTimeout(o, 10)
                }
                return c.data("sbi_imgLiquid_oldProcessed") ? void r() : (c.data("sbi_imgLiquid_oldProcessed", !1), c.data("oldSrc", c.attr("src")), i("img:not(:first)", u).css("display", "none"), u.css({overflow: "hidden"}), c.fadeTo(0, 0).removeAttr("width").removeAttr("height").css({
                  visibility: "visible",
                  "max-width": "none",
                  "max-height": "none",
                  width: "auto",
                  height: "auto",
                  display: "block"
                }), c.on("error", n), c[0].onerror = n, e(), void d())
              }

              function d() {
                (g.responsive || c.data("sbi_imgLiquid_oldProcessed")) && c.data("sbi_imgLiquid_settings") && (g = c.data("sbi_imgLiquid_settings"), u.actualSize = u.get(0).offsetWidth + u.get(0).offsetHeight / 1e4, u.sizeOld && u.actualSize !== u.sizeOld && r(), u.sizeOld = u.actualSize, setTimeout(d, g.responsiveCheckTime))
              }

              function n() {
                c.data("sbi_imgLiquid_error", !0), u.addClass("sbi_imgLiquid_error"), g.onItemError && g.onItemError(t, u, c), l()
              }

              function s() {
                var i = {};
                if (a.settings.useDataHtmlAttr) {
                  var t = u.attr("data-sbi_imgLiquid-fill"),
                    e = u.attr("data-sbi_imgLiquid-horizontalAlign"),
                    o = u.attr("data-sbi_imgLiquid-verticalAlign");
                  ("true" === t || "false" === t) && (i.fill = Boolean("true" === t)), void 0 === e || "left" !== e && "center" !== e && "right" !== e && -1 === e.indexOf("%") || (i.horizontalAlign = e), void 0 === o || "top" !== o && "bottom" !== o && "center" !== o && -1 === o.indexOf("%") || (i.verticalAlign = o)
                }
                return sbi_imgLiquid.isIE && a.settings.ieFadeInDisabled && (i.fadeInTime = 0), i
              }

              function r() {
                var i, e, a, o, d, n, s, r, m = 0, h = 0, f = u.width(), v = u.height();
                void 0 === c.data("owidth") && c.data("owidth", c[0].width), void 0 === c.data("oheight") && c.data("oheight", c[0].height), g.fill === f / v >= c.data("owidth") / c.data("oheight") ? (i = "100%", e = "auto", a = Math.floor(f), o = Math.floor(f * (c.data("oheight") / c.data("owidth")))) : (i = "auto", e = "100%", a = Math.floor(v * (c.data("owidth") / c.data("oheight"))), o = Math.floor(v)), d = g.horizontalAlign.toLowerCase(), s = f - a, "left" === d && (h = 0), "center" === d && (h = .5 * s), "right" === d && (h = s), -1 !== d.indexOf("%") && (d = parseInt(d.replace("%", ""), 10), d > 0 && (h = s * d * .01)), n = g.verticalAlign.toLowerCase(), r = v - o, "left" === n && (m = 0), "center" === n && (m = .5 * r), "bottom" === n && (m = r), -1 !== n.indexOf("%") && (n = parseInt(n.replace("%", ""), 10), n > 0 && (m = r * n * .01)), g.hardPixels && (i = a, e = o), c.css({
                  width: i,
                  height: e,
                  "margin-left": Math.floor(h),
                  "margin-top": Math.floor(m)
                }), c.data("sbi_imgLiquid_oldProcessed") || (c.fadeTo(g.fadeInTime, 1), c.data("sbi_imgLiquid_oldProcessed", !0), g.removeBoxBackground && u.css("background-image", "none"), u.addClass("sbi_imgLiquid_nobgSize"), u.addClass("sbi_imgLiquid_ready")), g.onItemFinish && g.onItemFinish(t, u, c), l()
              }

              function l() {
                t === a.length - 1 && a.settings.onFinish && a.settings.onFinish()
              }

              var g = a.settings, u = i(this), c = i("img:first", u);
              return c.length ? (c.data("sbi_imgLiquid_settings") ? (u.removeClass("sbi_imgLiquid_error").removeClass("sbi_imgLiquid_ready"), g = i.extend({}, c.data("sbi_imgLiquid_settings"), a.options)) : g = i.extend({}, a.settings, s()), c.data("sbi_imgLiquid_settings", g), g.onItemStart && g.onItemStart(t, u, c), void (sbi_imgLiquid.bgs_Available && g.useBackgroundSize ? e() : o())) : void n()
            })
          }
        })
      }(jQuery);

      // Use imagefill to set the images as backgrounds so they can be square
      !function () {
        var css = sbi_imgLiquid.injectCss,
          head = document.getElementsByTagName('head')[0],
          style = document.createElement('style');
        style.type = 'text/css';
        if (style.styleSheet) {
          style.styleSheet.cssText = css;
        } else {
          style.appendChild(document.createTextNode(css));
        }
        head.appendChild(style);
      }();
    }

    function sbiAddVisibilityListener() {
      /* Detect when element becomes visible. Used for when the feed is initially hidden, in a tab for example. https://github.com/shaunbowe/jquery.visibilityChanged */
      !function (i) {
        var n = {
          callback: function () {
          }, runOnLoad: !0, frequency: 100, sbiPreviousVisibility: null
        }, c = {};
        c.sbiCheckVisibility = function (i, n) {
          if (jQuery.contains(document, i[0])) {
            var e = n.sbiPreviousVisibility, t = i.is(":visible");
            n.sbiPreviousVisibility = t, null == e ? n.runOnLoad && n.callback(i, t) : e !== t && n.callback(i, t), setTimeout(function () {
              c.sbiCheckVisibility(i, n)
            }, n.frequency)
          }
        }, i.fn.sbiVisibilityChanged = function (e) {
          var t = i.extend({}, n, e);
          return this.each(function () {
            c.sbiCheckVisibility(i(this), t)
          })
        }
      }(jQuery);
    }

    function Sbi() {
      this.feeds = {};
      this.options = sb_instagram_js_options;
    }

    Sbi.prototype = {
      createPage: function (createFeeds, createFeedsArgs) {
        if (typeof sb_instagram_js_options.ajax_url !== 'undefined' && typeof window.sbiajaxurl === 'undefined') {
          window.sbiajaxurl = sb_instagram_js_options.ajax_url;
        }
        if (typeof window.sbiajaxurl === 'undefined' || window.sbiajaxurl.indexOf(window.location.hostname) === -1) {
          window.sbiajaxurl = location.protocol + '//'+ window.location.hostname + '/wp-admin/admin-ajax.php';
        }

        if ( $('#sbi-builder-app').length && typeof window.sbiresizedImages === 'undefined') {
          if ($('.sbi_resized_image_data').length
            && typeof $('.sbi_resized_image_data').attr('data-resized') !== 'undefined'
            && $('.sbi_resized_image_data').attr('data-resized').indexOf('{"') === 0) {
            window.sbiresizedImages = JSON.parse($('.sbi_resized_image_data').attr('data-resized'));
            $('.sbi_resized_image_data').remove();
          }
        }

        $('.sbi_no_js_error_message').remove();
        $('.sbi_no_js').removeClass('sbi_no_js');
        createFeeds(createFeedsArgs);
      },
      createFeeds: function (args) {
        args.whenFeedsCreated(
          $('.sbi').each(function (index) {
            $(this).attr('data-sbi-index', index + 1);
            var $self = $(this),
              flags = typeof $self.attr('data-sbi-flags') !== 'undefined' ? $self.attr('data-sbi-flags').split(',') : [],
              general = typeof $self.attr('data-options') !== 'undefined' ? JSON.parse($self.attr('data-options')) : {};
            if (flags.indexOf('testAjax') > -1) {
              window.sbi.triggeredTest = true;
              var submitData = {
                  'action' : 'sbi_on_ajax_test_trigger'
                },
                onSuccess = function(data) {
                  console.log('did test');
                };
              sbiAjax(submitData,onSuccess)
            }
            var feedOptions = {
              cols : $self.attr('data-cols'),
              colsmobile : typeof $self.attr('data-colsmobile') !== 'undefined' && $self.attr('data-colsmobile') !== 'same' ? $self.attr('data-colsmobile') : $self.attr('data-cols'),
              colstablet : typeof $self.attr('data-colstablet') !== 'undefined' && $self.attr('data-colstablet') !== 'same' ? $self.attr('data-colstablet') : $self.attr('data-cols'),
              num : $self.attr('data-num'),
              imgRes : $self.attr('data-res'),
              feedID : $self.attr('data-feedid'),
              postID : typeof $self.attr( 'data-postid' ) !== 'undefind' ? $self.attr( 'data-postid' ) : 'unknown',
              shortCodeAtts : $self.attr('data-shortcode-atts'),
              resizingEnabled : (flags.indexOf('resizeDisable') === -1),
              imageLoadEnabled : (flags.indexOf('imageLoadDisable') === -1),
              debugEnabled : (flags.indexOf('debug') > -1),
              favorLocal : (flags.indexOf('favorLocal') > -1),
              ajaxPostLoad : (flags.indexOf('ajaxPostLoad') > -1),
              gdpr : (flags.indexOf('gdpr') > -1),
              overrideBlockCDN : (flags.indexOf('overrideBlockCDN') > -1),
              consentGiven : false,
              locator : (flags.indexOf('locator') > -1),
              autoMinRes : 1,
              general : general
            };

            window.sbi.feeds[index] = sbiGetNewFeed(this, index, feedOptions);
            window.sbi.feeds[index].setResizedImages();
            window.sbi.feeds[index].init();

            var evt = jQuery.Event('sbiafterfeedcreate');
            evt.feed = window.sbi.feeds[index];
            jQuery(window).trigger(evt);

          })
        );
      },
      afterFeedsCreated: function () {
        // enable header hover action
        $('.sb_instagram_header').each(function () {
          var $thisHeader = $(this);
          $thisHeader.find('.sbi_header_link').on('mouseenter mouseleave', function(e) {
            switch(e.type) {
              case 'mouseenter':
                $thisHeader.find('.sbi_header_img_hover').addClass('sbi_fade_in');
                break;
              case 'mouseleave':
                $thisHeader.find('.sbi_header_img_hover').removeClass('sbi_fade_in');
                break;
            }
          });
        });

      },
      encodeHTML: function(raw) {
        // make sure passed variable is defined
        if (typeof raw === 'undefined') {
          return '';
        }
        // replace greater than and less than symbols with html entity to disallow html in comments
        var encoded = raw.replace(/(>)/g,'&gt;'),
          encoded = encoded.replace(/(<)/g,'&lt;');
        encoded = encoded.replace(/(&lt;br\/&gt;)/g,'<br>');
        encoded = encoded.replace(/(&lt;br&gt;)/g,'<br>');

        return encoded;
      },
      urlDetect: function(text) {
        var urlRegex = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
        return text.match(urlRegex);
      }
    };

    function SbiFeed(el, index, settings) {
      this.el = el;
      this.index = index;
      this.settings = settings;
      this.minImageWidth = 0;
      this.imageResolution = 150;
      this.resizedImages = {};
      this.needsResizing = [];
      this.outOfPages = false;
      this.page = 1;
      this.isInitialized = false;
    }

    SbiFeed.prototype = {
      init: function() {
        var feed = this;
        feed.settings.consentGiven = feed.checkConsent();
        if ($(this.el).find('.sbi_photo').parent('p').length) {
          $(this.el).addClass('sbi_no_autop');
        }
        if ($(this.el).find('#sbi_mod_error').length) {
          $(this.el).prepend($(this.el).find('#sbi_mod_error'));
        }
        if (this.settings.ajaxPostLoad) {
          this.getNewPostSet();
        } else {
          this.afterInitialImagesLoaded();
          //Only check the width once the resize event is over
        }
        var sbi_delay = (function () {
          var sbi_timer = 0;
          return function (sbi_callback, sbi_ms) {
            clearTimeout(sbi_timer);
            sbi_timer = setTimeout(sbi_callback, sbi_ms);
          };
        })();
        jQuery(window).on('resize',function () {
          sbi_delay(function () {
            feed.afterResize();
          }, 500);
        });

        $(this.el).find('.sbi_item').each(function() {
          feed.lazyLoadCheck($(this));
        });
      },
      initLayout: function() {

      },
      afterInitialImagesLoaded: function() {
        this.initLayout();
        this.loadMoreButtonInit();
        this.hideExtraImagesForWidth();
        this.beforeNewImagesRevealed();
        this.revealNewImages();
        this.afterNewImagesRevealed();
      },
      afterResize: function() {
        this.setImageHeight();
        this.setImageResolution();
        this.maybeRaiseImageResolution();
        this.setImageSizeClass();
      },
      afterLoadMoreClicked: function($button) {
        $button.find('.sbi_loader').removeClass('sbi_hidden');
        $button.find('.sbi_btn_text').addClass('sbi_hidden');
        $button.closest('.sbi').find('.sbi_num_diff_hide').addClass('sbi_transition').removeClass('sbi_num_diff_hide');
      },
      afterNewImagesLoaded: function() {
        var $self = $(this.el),
          feed = this;
        this.beforeNewImagesRevealed();
        this.revealNewImages();
        this.afterNewImagesRevealed();
        setTimeout(function () {
          //Hide the loader in the load more button
          $self.find('.sbi_loader').addClass('sbi_hidden');
          $self.find('.sbi_btn_text').removeClass('sbi_hidden');
          feed.maybeRaiseImageResolution();
        }, 500);
      },
      beforeNewImagesRevealed: function() {
        this.setImageHeight();
        this.maybeRaiseImageResolution(true);
        this.setImageSizeClass();
      },
      revealNewImages: function() {
        var $self = $(this.el),
          feed = this;

        $self.find('.sbi-screenreader').each(function() {
          $(this).find('img').remove();
        });

        // Call Custom JS if it exists
        if (typeof sbi_custom_js == 'function') setTimeout(function(){ sbi_custom_js(); }, 100);

        this.applyImageLiquid();
        $self.find('.sbi_item').each(function (index) {
          var $self = jQuery(this);

          //Photo links
          $self.find('.sbi_photo').on('mouseenter mouseleave', function(e) {
            switch(e.type) {
              case 'mouseenter':
                jQuery(this).fadeTo(200, 0.85);
                break;
              case 'mouseleave':
                jQuery(this).stop().fadeTo(500, 1);
                break;
            }
          });
        }); //End .sbi_item each

        //Remove the new class after 500ms, once the sorting is done
        setTimeout(function () {
          jQuery('#sbi_images .sbi_item.sbi_new').removeClass('sbi_new');
          //Loop through items and remove class to reveal them
          var time = 10;
          $self.find('.sbi_transition').each(function() {
            var $sbi_item_transition_el = jQuery(this);

            setTimeout( function(){
              $sbi_item_transition_el.removeClass('sbi_transition');
            }, time);
            time += 10;
          });
        }, 500);
      },
      lazyLoadCheck: function($item){
        var feed = this;
        if ($item.find('.sbi_photo').length && !$item.closest('.sbi').hasClass('sbi-no-ll-check')) {
          var imgSrcSet = feed.getImageUrls($item),
            maxResImage = typeof imgSrcSet[640] !== 'undefined' ?  imgSrcSet[640] : $item.find('.sbi_photo').attr('data-full-res');

          if (!feed.settings.consentGiven) {
            if (maxResImage.indexOf('scontent') > -1) {
              return;
            }
          }

          $item.find('.sbi_photo img').each(function() {
            if (maxResImage && typeof $(this).attr('data-src') !== 'undefined') {
              $(this).attr('data-src',maxResImage);
            }
            if (maxResImage && typeof $(this).attr('data-orig-src') !== 'undefined') {
              $(this).attr('data-orig-src',maxResImage);
            }
            $(this).on('load',function() {
              if (!$(this).hasClass('sbi-replaced')
                && $(this).attr('src').indexOf('placeholder') > -1) {
                $(this).addClass('sbi-replaced');
                if (maxResImage) {
                  $(this).attr('src',maxResImage);
                  if ($(this).closest('.sbi_imgLiquid_bgSize').length) {
                    $(this).closest('.sbi_imgLiquid_bgSize').css('background-image', 'url(' + maxResImage + ')');
                  }
                }
              }
            });
          });
        }
      },
      afterNewImagesRevealed: function() {
        this.listenForVisibilityChange();
        this.sendNeedsResizingToServer();
        if (!this.settings.imageLoadEnabled) {
          $('.sbi_no_resraise').removeClass('sbi_no_resraise');
        }

        var evt = $.Event('sbiafterimagesloaded');
        evt.el = $(this.el);
        $(window).trigger(evt);
      },
      setResizedImages: function () {
        if ($(this.el).find('.sbi_resized_image_data').length
          && typeof $(this.el).find('.sbi_resized_image_data').attr('data-resized') !== 'undefined'
          && $(this.el).find('.sbi_resized_image_data').attr('data-resized').indexOf('{"') === 0) {
          this.resizedImages = JSON.parse($(this.el).find('.sbi_resized_image_data').attr('data-resized'));
          $(this.el).find('.sbi_resized_image_data').remove();
        } else if (typeof window.sbiresizedImages !== 'undefined') {
          this.resizedImages = window.sbiresizedImages;
        }
      },
      sendNeedsResizingToServer: function() {
        var feed = this,
          $self = $(this.el);
        if (feed.needsResizing.length > 0 && feed.settings.resizingEnabled) {
          var itemOffset = $(this.el).find('.sbi_item').length,
            cacheAll = typeof feed.settings.general.cache_all !== 'undefined' ? feed.settings.general.cache_all : false;
          var locatorNonce = '';
          if ( typeof $self.attr( 'data-locatornonce' ) !== 'undefined' ) {
            locatorNonce = $self.attr( 'data-locatornonce' );
          }

          if ($('#sbi-builder-app').length) {
            if (typeof window.sbiresizeTriggered !== 'undefined' && window.sbiresizeTriggered) {
              return;
            } else {
              window.sbiresizeTriggered = true;
            }
          }

          var submitData = {
            action: 'sbi_resized_images_submit',
            needs_resizing: feed.needsResizing,
            offset: itemOffset,
            feed_id: feed.settings.feedID,
            atts: feed.settings.shortCodeAtts,
            location: feed.locationGuess(),
            post_id: feed.settings.postID,
            cache_all: cacheAll,
            locator_nonce: locatorNonce
          };
          var onSuccess = function(data) {
            var response = data;

            if (typeof data !== 'object' && data.trim().indexOf('{') === 0) {
              response = JSON.parse(data.trim());
            }
            if (feed.settings.debugEnabled) {
              console.log(response);
            }
            for (var property in response) {
              if (response.hasOwnProperty(property)) {
                feed.resizedImages[property] = response[property];
              }
            }
            feed.maybeRaiseImageResolution();

            setTimeout(function() {
              feed.afterResize();
            },500);
            if ($('#sbi-builder-app').length) {
              window.sbiresizeTriggered = false;
            }
          };
          sbiAjax(submitData,onSuccess);
        } else if (feed.settings.locator) {
          var locatorNonce = '';
          if ( typeof $self.attr( 'data-locatornonce' ) !== 'undefined' ) {
            locatorNonce = $self.attr( 'data-locatornonce' );
          }
          var submitData = {
            action: 'sbi_do_locator',
            feed_id: feed.settings.feedID,
            atts: feed.settings.shortCodeAtts,
            location: feed.locationGuess(),
            post_id: feed.settings.postID,
            locator_nonce: locatorNonce
          };
          var onSuccess = function(data) {

          };
          sbiAjax(submitData,onSuccess);
        }
      },
      loadMoreButtonInit: function () {
        var $self = $(this.el),
          feed = this;
        $self.find('#sbi_load .sbi_load_btn').off().on('click', function () {

          feed.afterLoadMoreClicked(jQuery(this));
          feed.getNewPostSet();

        }); //End click event
      },
      getNewPostSet: function () {
        var $self = $(this.el),
          feed = this;
        feed.page ++;

        var locatorNonce = '';
        if ( typeof $self.attr( 'data-locatornonce' ) !== 'undefined' ) {
          locatorNonce = $self.attr( 'data-locatornonce' );
        }

        var itemOffset = $self.find('.sbi_item').length,
          submitData = {
            action: 'sbi_load_more_clicked',
            offset: itemOffset,
            page: feed.page,
            feed_id: feed.settings.feedID,
            atts: feed.settings.shortCodeAtts,
            location: feed.locationGuess(),
            post_id: feed.settings.postID,
            current_resolution: feed.imageResolution,
            locator_nonce: locatorNonce
          };
        var onSuccess = function (data) {
          var response = data;

          if (typeof data !== 'object' && data.trim().indexOf('{') === 0) {
            response = JSON.parse(data.trim());
          }
          if (feed.settings.debugEnabled) {
            console.log(response);
          }
          feed.appendNewPosts(response.html);
          feed.addResizedImages(response.resizedImages);
          if (feed.settings.ajaxPostLoad) {
            feed.settings.ajaxPostLoad = false;
            feed.afterInitialImagesLoaded();
          } else {
            feed.afterNewImagesLoaded();
          }

          if (!response.feedStatus.shouldPaginate) {
            feed.outOfPages = true;
            $self.find('.sbi_load_btn').hide();
          } else {
            feed.outOfPages = false;
          }
          $('.sbi_no_js').removeClass('sbi_no_js');

        };
        sbiAjax(submitData, onSuccess);
      },
      appendNewPosts: function (newPostsHtml) {
        var $self = $(this.el),
          feed = this;
        if ($self.find('#sbi_images .sbi_item').length) {
          $self.find('#sbi_images .sbi_item').last().after(newPostsHtml);
        } else {
          $self.find('#sbi_images').append(newPostsHtml);
        }
      },
      addResizedImages: function (resizedImagesToAdd) {
        for (var imageID in resizedImagesToAdd) {
          this.resizedImages[imageID] = resizedImagesToAdd[imageID];
        }
      },
      setImageHeight: function() {
        var $self = $(this.el);

        var sbi_photo_width = $self.find('.sbi_photo').eq(0).innerWidth();

        //Figure out number of columns for either desktop or mobile
        var sbi_num_cols = this.getColumnCount();

        //Figure out what the width should be using the number of cols
        //Figure out what the width should be using the number of cols
        var imagesPadding = $self.find('#sbi_images').innerWidth() - $self.find('#sbi_images').width(),
          imagepadding = imagesPadding / 2;
        sbi_photo_width_manual = ( $self.find('#sbi_images').width() / sbi_num_cols ) - imagesPadding;
        //If the width is less than it should be then set it manually
        //if( sbi_photo_width <= (sbi_photo_width_manual) ) sbi_photo_width = sbi_photo_width_manual;

        $self.find('.sbi_photo').css('height', sbi_photo_width);

        //Set the position of the carousel arrows
        if ($self.find('.sbi-owl-nav').length) {
          setTimeout(function(){
            //If there's 2 rows then adjust position
            var sbi_ratio = 2;
            if( $self.find('.sbi_owl2row-item').length ) sbi_ratio = 1;

            var sbi_arrows_top = ($self.find('.sbi_photo').eq(0).innerWidth()/sbi_ratio);
            sbi_arrows_top += parseInt(imagepadding)*(2+(2-sbi_ratio));
            $self.find('.sbi-owl-nav div').css('top', sbi_arrows_top);
          }, 100);
        }

      },
      maybeRaiseSingleImageResolution: function ($item, index, forceChange) {
        var feed = this,
          imgSrcSet = feed.getImageUrls($item),
          currentUrl = $item.find('.sbi_photo img').attr('src'),
          currentRes = 150,
          imagEl = $item.find('img').get(0),
          aspectRatio = currentUrl === window.sbi.options.placeholder ? 1 : imagEl.naturalWidth/imagEl.naturalHeight,
          forceChange = typeof forceChange !== 'undefined' ? forceChange : false;

        if ($item.hasClass('sbi_no_resraise') || $item.hasClass('sbi_had_error') || ($item.find('.sbi_link_area').length && $item.find('.sbi_link_area').hasClass('sbi_had_error'))) {
          return;
        }

        if (imgSrcSet.length < 1) {
          if ($item.find('.sbi_link_area').length) {
            $item.find('.sbi_link_area').attr('href',window.sbi.options.placeholder.replace('placeholder.png','thumb-placeholder.png'))
          }
          return;
        } else if ($item.find('.sbi_link_area').length && $item.find('.sbi_link_area').attr('href') === window.sbi.options.placeholder.replace('placeholder.png','thumb-placeholder.png')
          || !feed.settings.consentGiven) {
          $item.find('.sbi_link_area').attr('href',imgSrcSet[imgSrcSet.length - 1])
        }
        if (typeof imgSrcSet[640] !== 'undefined') {
          $item.find('.sbi_photo').attr('data-full-res',imgSrcSet[640]);
        }

        $.each(imgSrcSet, function (index, value) {
          if (value === currentUrl) {
            currentRes = parseInt(index);
            // If the image has already been changed to an existing real source, don't force the change
            forceChange = false;
          }
        });
        //Image res
        var newRes = 640;
        switch (feed.settings.imgRes) {
          case 'thumb':
            newRes = 150;
            break;
          case 'medium':
            newRes = 320;
            break;
          case 'full':
            newRes = 640;
            break;
          default:
            var minImageWidth = Math.max(feed.settings.autoMinRes,$item.find('.sbi_photo').innerWidth()),
              thisImageReplace = feed.getBestResolutionForAuto(minImageWidth, aspectRatio, $item);
            switch (thisImageReplace) {
              case 320:
                newRes = 320;
                break;
              case 150:
                newRes = 150;
                break;
            }
            break;
        }

        if (newRes > currentRes || currentUrl === window.sbi.options.placeholder || forceChange) {
          if (feed.settings.debugEnabled) {
            var reason = currentUrl === window.sbi.options.placeholder ? 'was placeholder' : 'too small';
            console.log('rais res for ' + currentUrl, reason);
          }
          var newUrl = imgSrcSet[newRes].split("?ig_cache_key")[0];
          if (currentUrl !== newUrl) {
            $item.find('.sbi_photo img').attr('src', newUrl);
            $item.find('.sbi_photo').css('background-image', 'url("' + newUrl + '")');
          }
          currentRes = newRes;

          if (feed.settings.imgRes === 'auto') {
            var checked = false;
            $item.find('.sbi_photo img').on('load', function () {

              var $this_image = $(this);
              var newAspectRatio = ($this_image.get(0).naturalWidth / $this_image.get(0).naturalHeight);

              if ($this_image.get(0).naturalWidth !== 1000 && newAspectRatio > aspectRatio && !checked) {
                if (feed.settings.debugEnabled) {
                  console.log('rais res again for aspect ratio change ' + currentUrl);
                }
                checked = true;
                minImageWidth = $item.find('.sbi_photo').innerWidth();
                thisImageReplace = feed.getBestResolutionForAuto(minImageWidth, newAspectRatio, $item);
                newRes = 640;

                switch (thisImageReplace) {
                  case 320:
                    newRes = 320;
                    break;
                  case 150:
                    newRes = 150;
                    break;
                }

                if (newRes > currentRes) {
                  newUrl = imgSrcSet[newRes].split("?ig_cache_key")[0];
                  $this_image.attr('src', newUrl);
                  $this_image.closest('.sbi_photo').css('background-image', 'url("' + newUrl + '")');
                }
                if (feed.layout === 'masonry' || feed.layout === 'highlight') {
                  $(feed.el).find('#sbi_images').smashotope(feed.isotopeArgs);
                  setTimeout(function() {
                    $(feed.el).find('#sbi_images').smashotope(feed.isotopeArgs);
                  },500)
                }
              } else {
                if (feed.settings.debugEnabled) {
                  var reason = checked ? 'already checked' : 'no aspect ratio change';
                  console.log('not raising res for replacement  ' + currentUrl, reason);
                }
              }
            });
          }


        }

        $item.find('img').on('error', function () {
          if (!$(this).hasClass('sbi_img_error')) {
            $(this).addClass('sbi_img_error');
            var sourceFromAPI = ($(this).attr('src').indexOf('media/?size=') > -1 || $(this).attr('src').indexOf('cdninstagram') > -1 || $(this).attr('src').indexOf('fbcdn') > -1)

            if (!sourceFromAPI && feed.settings.consentGiven) {

              if ($(this).closest('.sbi_photo').attr('data-img-src-set') !== 'undefined') {
                var srcSet = JSON.parse($(this).closest('.sbi_photo').attr('data-img-src-set').replace(/\\\//g, '/'));
                if (typeof srcSet.d !== 'undefined') {
                  $(this).attr('src', srcSet.d);
                  $(this).closest('.sbi_photo').css('background-image', 'url(' + srcSet.d + ')');
                  $(this).closest('.sbi_item').addClass('sbi_had_error').find('.sbi_link_area').attr('href', srcSet[640]).addClass('sbi_had_error');
                }
              }
            } else {
              feed.settings.favorLocal = true;
              var srcSet = feed.getImageUrls($(this).closest('.sbi_item'));
              if (typeof srcSet[640] !== 'undefined') {
                $(this).attr('src', srcSet[640]);
                $(this).closest('.sbi_photo').css('background-image', 'url(' + srcSet[640] + ')');
                $(this).closest('.sbi_item').addClass('sbi_had_error').find('.sbi_link_area').attr('href', srcSet[640]).addClass('sbi_had_error');
              }
            }
            setTimeout(function() {
              feed.afterResize();
            }, 1500)
          } else {
            console.log('unfixed error ' + $(this).attr('src'));
          }
        });
      },
      maybeRaiseImageResolution: function (justNew) {
        var feed = this,
          itemsSelector = typeof justNew !== 'undefined' && justNew === true ? '.sbi_item.sbi_new' : '.sbi_item',
          forceChange = !feed.isInitialized ? true : false;
        $(feed.el).find(itemsSelector).each(function (index) {
          if (!$(this).hasClass('sbi_num_diff_hide')
            && $(this).find('.sbi_photo').length
            && typeof $(this).find('.sbi_photo').attr('data-img-src-set') !== 'undefined') {
            feed.maybeRaiseSingleImageResolution($(this),index,forceChange);
          }
        }); //End .sbi_item each
        feed.isInitialized = true;
      },
      getBestResolutionForAuto: function(colWidth, aspectRatio, $item) {
        if (isNaN(aspectRatio) || aspectRatio < 1) {
          aspectRatio = 1;
        }
        var bestWidth = colWidth * aspectRatio,
          bestWidthRounded = Math.ceil(bestWidth / 10) * 10,
          customSizes = [150, 320, 640];

        if ($item.hasClass('sbi_highlighted')) {
          bestWidthRounded = bestWidthRounded *2;
        }

        if (customSizes.indexOf(parseInt(bestWidthRounded)) === -1) {
          var done = false;
          $.each(customSizes, function (index, item) {
            if (item > parseInt(bestWidthRounded) && !done) {
              bestWidthRounded = item;

              done = true;
            }
          });
        }

        return bestWidthRounded;
      },
      hideExtraImagesForWidth: function() {
        if (this.layout === 'carousel') {
          return;
        }
        var $self = $(this.el),
          num = typeof $self.attr('data-num') !== 'undefined' && $self.attr('data-num') !== '' ? parseInt($self.attr('data-num')) : 1,
          nummobile = typeof $self.attr('data-nummobile') !== 'undefined' && $self.attr('data-nummobile') !== '' ? parseInt($self.attr('data-nummobile')) : num;

        if ($(window).width() < 480 || window.sbi_preview_device === 'mobile') {
          if (nummobile < $self.find('.sbi_item').length) {
            $self.find('.sbi_item').slice(nummobile - $self.find('.sbi_item').length).addClass('sbi_num_diff_hide');
          }
        } else {
          if (num < $self.find('.sbi_item').length) {
            $self.find('.sbi_item').slice(num - $self.find('.sbi_item').length).addClass('sbi_num_diff_hide');
          }
        }
      },
      setImageSizeClass: function () {
        var $self = $(this.el);
        $self.removeClass('sbi_small sbi_medium');
        var feedWidth = $self.innerWidth(),
          photoPadding = parseInt(($self.find('#sbi_images').outerWidth() - $self.find('#sbi_images').width())) / 2,
          cols = this.getColumnCount(),
          feedWidthSansPadding = feedWidth - (photoPadding * (cols+2)),
          colWidth = (feedWidthSansPadding / cols);
        if (colWidth > 120 && colWidth < 240) {
          $self.addClass('sbi_medium');
        } else if (colWidth <= 120) {
          $self.addClass('sbi_small');
        }
      },
      setMinImageWidth: function () {
        if ($(this.el).find('.sbi_item .sbi_photo').first().length) {
          this.minImageWidth = $(this.el).find('.sbi_item .sbi_photo').first().innerWidth();
        } else {
          this.minImageWidth = 150;
        }
      },
      setImageResolution: function () {
        if (this.settings.imgRes === 'auto') {
          this.imageResolution = 'auto';
        } else {
          switch (this.settings.imgRes) {
            case 'thumb':
              this.imageResolution = 150;
              break;
            case 'medium':
              this.imageResolution = 320;
              break;
            default:
              this.imageResolution = 640;
          }
        }
      },
      getImageUrls: function ($item) {
        var srcSet = JSON.parse($item.find('.sbi_photo').attr('data-img-src-set').replace(/\\\//g, '/')),
          id = $item.attr('id').replace('sbi_', '');
        if (!this.settings.consentGiven && !this.settings.overrideBlockCDN) {
          srcSet = [];
        }
        if (typeof this.resizedImages[id] !== 'undefined'
          && this.resizedImages[id] !== 'video'
          && this.resizedImages[id] !== 'pending'
          && this.resizedImages[id].id !== 'error'
          && this.resizedImages[id].id !== 'video'
          && this.resizedImages[id].id !== 'pending') {

          if (typeof this.resizedImages[id]['sizes'] !== 'undefined') {
            var foundSizes = [];
            if (typeof this.resizedImages[id]['sizes']['full'] !== 'undefined') {
              srcSet[640] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'full.jpg';
              foundSizes.push(640);
            }
            if (typeof this.resizedImages[id]['sizes']['low'] !== 'undefined') {
              srcSet[320] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'low.jpg';
              foundSizes.push(320);
            }
            if (typeof this.resizedImages[id]['sizes']['thumb'] !== 'undefined') {
              foundSizes.push(150);
              srcSet[150] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'thumb.jpg';
            }
            if (this.settings.favorLocal) {
              if (foundSizes.indexOf(640) === -1) {
                if (foundSizes.indexOf(320) > -1) {
                  srcSet[640] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'low.jpg';
                }
              }
              if (foundSizes.indexOf(320) === -1) {
                if (foundSizes.indexOf(640) > -1) {
                  srcSet[320] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'full.jpg';
                } else if (foundSizes.indexOf(150) > -1) {
                  srcSet[320] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'thumb.jpg';
                }
              }
              if (foundSizes.indexOf(150) === -1) {
                if (foundSizes.indexOf(320) > -1) {
                  srcSet[150] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'low.jpg';
                } else if (foundSizes.indexOf(640) > -1) {
                  srcSet[150] = sb_instagram_js_options.resized_url + this.resizedImages[id].id + 'full.jpg';
                }
              }
            }
          }
        } else if (typeof this.resizedImages[id] === 'undefined'
          || (typeof this.resizedImages[id]['id'] !== 'undefined' && this.resizedImages[id]['id'] !== 'pending' && this.resizedImages[id]['id'] !== 'error')) {
          this.addToNeedsResizing(id);
        }

        return srcSet;
      },
      getAvatarUrl: function (username,favorType) {
        if (username === '') {
          return '';
        }

        var availableAvatars = this.settings.general.avatars,
          favorType = typeof favorType !== 'undefined' ? favorType : 'local';

        if (favorType === 'local') {
          if (typeof availableAvatars['LCL'+username] !== 'undefined' && parseInt(availableAvatars['LCL'+username]) === 1) {
            return sb_instagram_js_options.resized_url + username + '.jpg';
          } else if (typeof availableAvatars[username] !== 'undefined') {
            return availableAvatars[username];
          } else {
            return '';
          }
        } else {
          if (typeof availableAvatars[username] !== 'undefined') {
            return availableAvatars[username];
          } else if (typeof availableAvatars['LCL'+username] !== 'undefined' && parseInt(availableAvatars['LCL'+username]) === 1)  {
            return sb_instagram_js_options.resized_url + username + '.jpg';
          } else {
            return '';
          }
        }
      },
      addToNeedsResizing: function (id) {
        if (this.needsResizing.indexOf(id) === -1) {
          this.needsResizing.push(id);
        }
      },
      applyImageLiquid: function () {
        var $self = $(this.el),
          feed = this;
        sbiAddImgLiquid();
        if (typeof $self.find(".sbi_photo").sbi_imgLiquid == 'function') {
          $self.find(".sbi_photo").sbi_imgLiquid({fill: true});
        }
      },
      listenForVisibilityChange: function() {
        var feed = this;
        sbiAddVisibilityListener();
        if (typeof $(this.el).filter(':hidden').sbiVisibilityChanged == 'function') {
          //If the feed is initially hidden (in a tab for example) then check for when it becomes visible and set then set the height
          $(this.el).filter(':hidden').sbiVisibilityChanged({
            callback: function (element, visible) {
              feed.afterResize();
            },
            runOnLoad: false
          });
        }
      },
      getColumnCount: function() {
        var $self = $(this.el),
          cols = this.settings.cols,
          colsmobile = this.settings.colsmobile,
          colstablet = this.settings.colstablet,
          returnCols = cols;

        sbiWindowWidth = window.innerWidth;

        if ($self.hasClass('sbi_mob_col_auto')) {
          if (sbiWindowWidth < 640 && (parseInt(cols) > 2 && parseInt(cols) < 7)) returnCols = 2;
          if (sbiWindowWidth < 640 && (parseInt(cols) > 6 && parseInt(cols) < 11)) returnCols = 4;
          if (sbiWindowWidth <= 480 && parseInt(cols) > 2) returnCols = 1;
        } else if (sbiWindowWidth > 480 && sbiWindowWidth <= 800) {
          returnCols = colstablet;
        }else if (sbiWindowWidth <= 480) {
          returnCols = colsmobile;
        }

        return parseInt(returnCols);
      },
      checkConsent: function() {
        if (this.settings.consentGiven || !this.settings.gdpr) {
          return true;
        }
        if (typeof CLI_Cookie !== "undefined") { // GDPR Cookie Consent by WebToffee
          if (CLI_Cookie.read(CLI_ACCEPT_COOKIE_NAME) !== null)  {

            // WebToffee no longer uses this cookie but being left here to maintain backwards compatibility
            if (CLI_Cookie.read('cookielawinfo-checkbox-non-necessary') !== null) {
              this.settings.consentGiven = CLI_Cookie.read('cookielawinfo-checkbox-non-necessary') === 'yes';
            }

            if (CLI_Cookie.read('cookielawinfo-checkbox-necessary') !== null) {
              this.settings.consentGiven = CLI_Cookie.read('cookielawinfo-checkbox-necessary') === 'yes';
            }
          }

        } else if (typeof window.cnArgs !== "undefined") { // Cookie Notice by dFactory
          var value = "; " + document.cookie,
            parts = value.split( '; cookie_notice_accepted=' );

          if ( parts.length === 2 ) {
            var val = parts.pop().split( ';' ).shift();

            this.settings.consentGiven = (val === 'true');
          }
        } else if (typeof window.cookieconsent !== 'undefined') { // Complianz by Really Simple Plugins
          this.settings.consentGiven = sbiCmplzGetCookie('complianz_consent_status') === 'allow';
        } else if (typeof window.Cookiebot !== "undefined") { // Cookiebot by Cybot A/S
          this.settings.consentGiven = Cookiebot.consented;
        } else if (typeof window.BorlabsCookie !== 'undefined') { // Borlabs Cookie by Borlabs
          this.settings.consentGiven = window.BorlabsCookie.checkCookieConsent('instagram');
        }

        var evt = jQuery.Event('sbicheckconsent');
        evt.feed = this;
        jQuery(window).trigger(evt);

        return this.settings.consentGiven; // GDPR not enabled
      },
      afterConsentToggled: function() {
        if (this.checkConsent()) {
          var feed = this;
          feed.maybeRaiseImageResolution();

          setTimeout(function() {
            feed.afterResize();
          },500);
        }
      },
      locationGuess: function() {
        var $feed = $(this.el),
          location = 'content';

        if ($feed.closest('footer').length) {
          location = 'footer';
        } else if ($feed.closest('.header').length
          || $feed.closest('header').length) {
          location = 'header';
        } else if ($feed.closest('.sidebar').length
          || $feed.closest('aside').length) {
          location = 'sidebar';
        }

        return location;
      },
    };

    window.sbi_init = function() {
      window.sbi = new Sbi();
      window.sbi.createPage( window.sbi.createFeeds, {whenFeedsCreated: window.sbi.afterFeedsCreated});
    };

    function sbiGetNewFeed(feed,index,feedOptions) {
      return new SbiFeed(feed,index,feedOptions);
    }

    function sbiAjax(submitData,onSuccess) {
      $.ajax({
        url: sbiajaxurl,
        type: 'post',
        data: submitData,
        success: onSuccess
      });
    }

    function sbiCmplzGetCookie(cname) {
      var name = cname + "="; //Create the cookie name variable with cookie name concatenate with = sign
      var cArr = window.document.cookie.split(';'); //Create cookie array by split the cookie by ';'

      //Loop through the cookies and return the cookie value if it find the cookie name
      for (var i = 0; i < cArr.length; i++) {
        var c = cArr[i].trim();
        //If the name is the cookie string at position 0, we found the cookie and return the cookie value
        if (c.indexOf(name) == 0)
          return c.substring(name.length, c.length);
      }

      return "";
    }

  })(jQuery);

  jQuery(document).ready(function($) {
    if (typeof window.sb_instagram_js_options === 'undefined') {
      window.sb_instagram_js_options = {
        font_method: "svg",
        resized_url: location.protocol + '//' + window.location.hostname + "/wp-content/uploads/sb-instagram-feed-images/",
        placeholder: location.protocol + '//' + window.location.hostname + "/wp-content/plugins/instagram-feed/img/placeholder.png"
      };
    }
    if (typeof window.sb_instagram_js_options.resized_url !== 'undefined' && window.sb_instagram_js_options.resized_url.indexOf(location.protocol) === -1) {
      if (location.protocol === 'http:') {
        window.sb_instagram_js_options.resized_url = window.sb_instagram_js_options.resized_url.replace('https:','http:');
      } else {
        window.sb_instagram_js_options.resized_url = window.sb_instagram_js_options.resized_url.replace('http:','https:');
      }
    }
    sbi_init();

    // Cookie Notice by dFactory
    $('#cookie-notice a').on('click',function() {
      setTimeout(function() {
        $.each(window.sbi.feeds,function(index){
          window.sbi.feeds[ index ].afterConsentToggled();
        });
      },1000);
    });

    // GDPR Cookie Consent by WebToffee
    $('#cookie-law-info-bar a').on('click',function() {
      setTimeout(function() {
        $.each(window.sbi.feeds,function(index){
          window.sbi.feeds[ index ].afterConsentToggled();
        });
      },1000);
    });

    // GDPR Cookie Consent by WebToffee
    $('.cli-user-preference-checkbox').on('click',function(){
      setTimeout(function() {
        $.each(window.sbi.feeds,function(index){
          window.sbi.feeds[ index ].settings.consentGiven = false;
          window.sbi.feeds[ index ].afterConsentToggled();
        });
      },1000);
    });

    // Cookiebot
    $(window).on('CookiebotOnAccept', function (event) {
      $.each(window.sbi.feeds,function(index){
        window.sbi.feeds[ index ].settings.consentGiven = true;
        window.sbi.feeds[ index ].afterConsentToggled();
      });
    });

    // Complianz by Really Simple Plugins
    $(document).on('cmplzAcceptAll', function (event) {
      $.each(window.sbi.feeds,function(index){
        window.sbi.feeds[ index ].settings.consentGiven = true;
        window.sbi.feeds[ index ].afterConsentToggled();
      });
    });

    // Complianz by Really Simple Plugins
    $(document).on('cmplzRevoke', function (event) {
      $.each(window.sbi.feeds,function(index){
        window.sbi.feeds[ index ].settings.consentGiven = false;
        window.sbi.feeds[ index ].afterConsentToggled();
      });
    });

    // Borlabs Cookie by Borlabs
    $(document).on('borlabs-cookie-consent-saved', function (event) {
      $.each(window.sbi.feeds,function(index){
        window.sbi.feeds[ index ].settings.consentGiven = false;
        window.sbi.feeds[ index ].afterConsentToggled();
      });
    });
  });

} // if sbi_js_exists
