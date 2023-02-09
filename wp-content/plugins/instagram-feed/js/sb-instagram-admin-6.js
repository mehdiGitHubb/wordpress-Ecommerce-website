jQuery(document).ready(function($) {

  // Social Wall Menu Workaround
  //toplevel_page_sbsw #adminmenu a[href="admin.php?page=sb-instagram-feed"]
  $('.toplevel_page_sbsw a[href="admin.php?page=sb-instagram-feed"]').css('display','block').attr('href','admin.php?page=sbi-feed-builder');
  $('a[href="admin.php?page=sb-instagram-feed"].menu-top').css('display','block').attr('href','admin.php?page=sbi-feed-builder');

  $(document).on('click', '#renew-modal-btn', function() {
    $('.sbi-sb-modal').show();
  });

  $(document).on('click', '#sbi-sb-close-modal', function() {
    $('.sbi-sb-modal').hide();
  });

  /**
   * Recheck the licensey key by sending AJAX request to the server
   *
   * @since 4.0
   */
  $(document).on('click', "#recheck-license-key", function() {
    $(this).find('.spinner-icon').show();
    let cffLicenseNotice = $('#sbi-license-notice');
    $.ajax({
      url: ajaxurl,
      data: {
        action: 'sbi_check_license',
        sbi_nonce: sbiA.sbi_nonce
      },
      success: function(result){
        $(this).find('.spinner-icon').hide();

        if ( cffLicenseNotice ) {
          if ( result.success == true ) {
            cffLicenseNotice.removeClass('sbi-license-expired-notice').addClass('sbi-license-renewed-notice');
          }
          cffLicenseNotice.html( result.data.content );
        }
      }
    });
  });

  /**
   * Dismiss the renewed license notice
   *
   * @since 4.0
   */
  $(document).on('click', "#sbi-hide-notice", function() {
    let cffLicenseNotice = $('#sbi-license-notice');
    let cffLicenseModal = $('.sbi-sb-modal');
    cffLicenseNotice.remove();
    cffLicenseModal.remove();
  });

  $(document).on('click', '#sbi-dismiss-header-notice', function() {
    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : 'sbi_dismiss_upgrade_notice',
        sbi_nonce: sbiA.sbi_nonce
      },
      success : function(data) {
        if ( data.success == true ) {
          $('#sbi-notice-bar').slideUp();
          $("#sbi-builder-app").removeClass('sbi-builder-app-lite-dismiss');
        }
      },
      error : function(e)  {
        console.log(e);
      }
    });
  });

  $('.sbi-retry-db').on('click', function(event) {
    event.preventDefault();
    var $btn = $(this);
    $btn.prop( 'disabled', true ).addClass( 'loading' ).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : 'sbi_retry_db',
        sbi_nonce : sbiA.sbi_nonce,
      },
      success : function(data) {
        if (typeof data.data.message !== 'undefined') {
          $btn.closest('p').after(data.data.message);
          $btn.remove();
        }
      },
      error : function(data)  {
      }
    }); // ajax call
  });

  $('.sbi-reset-unused-feed-usage').on('click', function (event) {
    event.preventDefault();
    const $btn = $(this);
    $btn.prop('disabled', true).addClass('loading').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
    $.ajax({
      url: sbiA.ajax_url,
      type: 'post',
      data: {
        action: 'sbi_reset_unused_feed_usage',
        sbi_nonce: sbiA.sbi_nonce,
      },
      success: function (data) {
        if (typeof data.data.message !== 'undefined') {
          $btn.closest('p').after(data.data.message);
          $btn.remove();
        }
      },
      error: function (data) {}
    });
  });

  $('.sbi-clear-errors-visit-page').on('click', function(event) {
    event.preventDefault();
    var $btn = $(this);
    $btn.prop( 'disabled', true ).addClass( 'loading' ).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : 'sbi_reset_log',
        sbi_nonce : sbiA.sbi_nonce,
      },
      success : function(data) {
        window.location.href = $btn.attr('data-url');
      },
      error : function(data)  {
        window.location.href = $btn.attr('data-url');
      }
    }); // ajax call
  });

  jQuery('body').on('click', '#sbi_review_consent_yes', function(e) {
    let reviewStep1 = jQuery('.sbi_review_notice_step_1, .sbi_review_step1_notice');
    let reviewStep2 = jQuery('.sbi_notice.sbi_review_notice, .rn_step_2');

    reviewStep1.hide();
    reviewStep2.show();

    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : 'sbi_review_notice_consent_update',
        consent : 'yes',
        sbi_nonce: sbiA.sbi_nonce
      },
      success : function(data) {
      }
    }); // ajax call

  });

  jQuery('body').on('click', '#sbi_review_consent_no', function(e) {
    let reviewStep1 = jQuery('.sbi_review_notice_step_1, #sbi-notifications');
    reviewStep1.hide();

    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : 'sbi_review_notice_consent_update',
        consent : 'no',
        sbi_nonce: sbiA.sbi_nonce
      },
      success : function(data) {
      }
    }); // ajax call

  });

  $(document).on('click', '#sbi_install_op_btn', function() {
    let self = $(this);
    let pluginAtts = self.data('plugin-atts');
    if ( pluginAtts.step == 'install' ) {
      pluginAtts.plugin = pluginAtts.download_plugin
    }
    let loader = '<span class="sbi-btn-spinner"><svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve"><path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite"></animateTransform></path></svg></span>';
    self.prepend(loader);

    // send the ajax request to install or activate the plugin
    $.ajax({
      url : sbiA.ajax_url,
      type : 'post',
      data : {
        action : pluginAtts.action,
        nonce : pluginAtts.nonce,
        plugin : pluginAtts.plugin,
        download_plugin : pluginAtts.download_plugin,
        type : 'plugin',
      },
      success : function(data) {
        if ( data.success == true ) {
          self.find('.sbi-btn-spinner').remove();
          self.attr('disabled', 'disabled');

          if ( pluginAtts.step == 'install' ) {
            self.html( data.data.msg );
          } else {
            self.html( data.data );
          }
        }
      },
      error : function(e)  {
        console.log(e);
      }
    });
  });
});


/* global smash_admin, jconfirm, wpCookies, Choices, List */

(function($) {

  'use strict';

  // Global settings access.
  var s;

  // Admin object.
  var SmashAdmin = {

    // Settings.
    settings: {
      iconActivate: '<i class="fa fa-toggle-on fa-flip-horizontal" aria-hidden="true"></i>',
      iconDeactivate: '<i class="fa fa-toggle-on" aria-hidden="true"></i>',
      iconInstall: '<i class="fa fa-cloud-download" aria-hidden="true"></i>',
      iconSpinner: '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>',
      mediaFrame: false
    },

    /**
     * Start the engine.
     *
     * @since 1.3.9
     */
    init: function() {

      // Settings shortcut.
      s = this.settings;

      // Document ready.
      $( document ).ready( SmashAdmin.ready );

      // Addons List.
      SmashAdmin.initAddons();
    },

    /**
     * Document ready.
     *
     * @since 1.3.9
     */
    ready: function() {

      // Action available for each binding.
      $( document ).trigger( 'smashReady' );
    },

    //--------------------------------------------------------------------//
    // Addons List.
    //--------------------------------------------------------------------//

    /**
     * Element bindings for Addons List page.
     *
     * @since 1.3.9
     */
    initAddons: function() {

      // Some actions have to be delayed to document.ready.
      $( document ).on( 'smashReady', function() {

        // Only run on the addons page.
        if ( ! $( '#sbi-admin-addons' ).length ) {
          return;
        }

        // Display all addon boxes as the same height.
        $( '.addon-item .details' ).matchHeight( { byrow: false, property: 'height' } );

        // Addons searching.
        if ( $('#sbi-admin-addons-list').length ) {
          var addonSearch = new List( 'sbi-admin-addons-list', {
            valueNames: [ 'addon-name' ]
          } );

          $( '#sbi-admin-addons-search' ).on( 'keyup', function () {
            var searchTerm = $( this ).val(),
              $heading = $( '#addons-heading' );

            if ( searchTerm ) {
              $heading.text( sbi_admin.addon_search );
            }
            else {
              $heading.text( $heading.data( 'text' ) );
            }

            addonSearch.search( searchTerm );
          } );
        }
      });

      // Toggle an addon state.
      $( document ).on( 'click', '#sbi-admin-addons .addon-item button', function( event ) {

        event.preventDefault();

        if ( $( this ).hasClass( 'disabled' ) ) {
          return false;
        }

        SmashAdmin.addonToggle( $( this ) );
      });

      //Close the modal if clicking anywhere outside it
      jQuery('body').on('click', '#sbi-op-modals', function(e){
        if (e.target !== this) return;
        jQuery('#sbi-op-modals').remove();
      });
      jQuery('body').on('click', '.sbi-fb-popup-cls', function(e){
        jQuery('#sbi-op-modals').remove();
      });

      //Add class to Pro menu item
      $('.sbi_get_pro').parent().attr({'class':'sbi_get_pro_highlight', 'target':'_blank'});

      //Click event for other plugins in menu
      $('.sbi_get_cff, .sbi_get_sbi, .sbi_get_ctf, .sbi_get_yt').parent().on('click', function(e) {
        e.preventDefault();

        // remove the already opened modal
        jQuery('#sbi-op-modals').remove();

        // prepend the modal wrapper
        $('#wpbody-content').prepend('<div class="sbi-fb-source-ctn sb-fs-boss sbi-fb-center-boss" id="sbi-op-modals"><i class="fa fa-spinner fa-spin sbi-loader" aria-hidden="true"></i></div>');

        // determine the plugin name
        var $self = $(this).find('span'),
          sb_get_plugin = 'twitter';

        if ($self.hasClass('sbi_get_cff')) {
          sb_get_plugin = 'facebook';
        } else if ($self.hasClass('sbi_get_sbi')) {
          sb_get_plugin = 'instagram';
        } else if ($self.hasClass('sbi_get_yt')) {
          sb_get_plugin = 'youtube';
        }

        // send the ajax request to load plugin name and others data
        $.ajax({
          url: sbiA.ajax_url,
          type: 'post',
          data: {
            action: 'sbi_other_plugins_modal',
            plugin: sb_get_plugin,
            sbi_nonce: sbiA.sbi_nonce,

          },
          success: function (data) {
            if (data.success == true) {
              $('#sbi-op-modals').html(data.data.output);
            }
          },
          error: function (e) {
            console.log(e);
          }
        });
      });
    },

    /**
     * Toggle addon state.
     *
     * @since 1.3.9
     */
    addonToggle: function( $btn ) {

      var $addon = $btn.closest( '.addon-item' ),
        plugin = $btn.attr( 'data-plugin' ),
        plugin_type = $btn.attr( 'data-type' ),
        action,
        cssClass,
        statusText,
        buttonText,
        errorText,
        successText;

      if ( $btn.hasClass( 'status-go-to-url' ) ) {
        // Open url in new tab.
        window.open( $btn.attr('data-plugin'), '_blank' );
        return;
      }

      $btn.prop( 'disabled', true ).addClass( 'loading' );
      $btn.html( s.iconSpinner );

      if ( $btn.hasClass( 'status-active' ) ) {
        // Deactivate.
        action     = 'sbi_deactivate_addon';
        cssClass   = 'status-inactive';
        if ( plugin_type === 'plugin' ) {
          cssClass += ' button button-secondary';
        }
        statusText = sbi_admin.addon_inactive;
        buttonText = sbi_admin.addon_activate;
        if ( plugin_type === 'addon' ) {
          buttonText = s.iconActivate + buttonText;
        }
        errorText  = s.iconDeactivate + sbi_admin.addon_deactivate;

      } else if ( $btn.hasClass( 'status-inactive' ) ) {
        // Activate.
        action     = 'sbi_activate_addon';
        cssClass   = 'status-active';
        if ( plugin_type === 'plugin' ) {
          cssClass += ' button button-secondary disabled';
        }
        statusText = sbi_admin.addon_active;
        buttonText = sbi_admin.addon_deactivate;
        if ( plugin_type === 'addon' ) {
          buttonText = s.iconDeactivate + buttonText;
        } else if ( plugin_type === 'plugin' ) {
          buttonText = sbi_admin.addon_activated;
        }
        errorText  = s.iconActivate + sbi_admin.addon_activate;

      } else if ( $btn.hasClass( 'status-download' ) ) {
        // Install & Activate.
        action   = 'sbi_install_addon';
        cssClass = 'status-active';
        if ( plugin_type === 'plugin' ) {
          cssClass += ' button disabled';
        }
        statusText = sbi_admin.addon_active;
        buttonText = sbi_admin.addon_activated;
        if ( plugin_type === 'addon' ) {
          buttonText = s.iconActivate + sbi_admin.addon_deactivate;
        }
        errorText = s.iconInstall + sbi_admin.addon_activate;

      } else {
        return;
      }

      var data = {
        action: action,
        nonce : sbi_admin.nonce,
        plugin: plugin,
        type  : plugin_type
      };
      $.post( sbi_admin.ajax_url, data, function( res ) {

        if ( res.success ) {
          if ( 'sbi_install_addon' === action ) {
            $btn.attr( 'data-plugin', res.data.basename );
            successText = res.data.msg;
            if ( ! res.data.is_activated ) {
              cssClass = 'status-inactive';
              if ( plugin_type === 'plugin' ) {
                cssClass = 'button';
              }
              statusText = sbi_admin.addon_inactive;
              buttonText = s.iconActivate + sbi_admin.addon_activate;
            }
          } else {
            successText = res.data;
          }
          $addon.find( '.actions' ).append( '<div class="msg success">'+successText+'</div>' );
          $addon.find( 'span.status-label' )
            .removeClass( 'status-active status-inactive status-download' )
            .addClass( cssClass )
            .removeClass( 'button button-primary button-secondary disabled' )
            .text( statusText );
          $btn
            .removeClass( 'status-active status-inactive status-download' )
            .removeClass( 'button button-primary button-secondary disabled' )
            .addClass( cssClass ).html( buttonText );
        } else {
          if ( 'download_failed' === res.data[0].code ) {
            if ( plugin_type === 'addon' ) {
              $addon.find( '.actions' ).append( '<div class="msg error">'+sbi_admin.addon_error+'</div>' );
            } else {
              $addon.find( '.actions' ).append( '<div class="msg error">'+sbi_admin.plugin_error+'</div>' );
            }
          } else {
            $addon.find( '.actions' ).append( '<div class="msg error">'+res.data+'</div>' );
          }
          $btn.html( errorText );
        }

        $btn.prop( 'disabled', false ).removeClass( 'loading' );

        // Automatically clear addon messages after 3 seconds.
        setTimeout( function() {
          $( '.addon-item .msg' ).remove();
        }, 3000 );

      }).fail( function( xhr ) {
        console.log( xhr.responseText );
      });
    },

  };

  SmashAdmin.init();

  window.SmashAdmin = SmashAdmin;

})( jQuery );
