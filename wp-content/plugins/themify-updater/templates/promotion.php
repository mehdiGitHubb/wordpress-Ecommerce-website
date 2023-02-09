<?php

if ( ! defined('THEMIFY_UPDATER_MENU_PAGE') ) die();

$current_theme = wp_get_theme();
$batch_btn = '<div class="themify-updater-batch-install" style="display:none">
        <label><input type="checkbox" class="batch-install-enable">'.__('Batch Install','themify-updater').'</label>
        <label class="batch-install-all"><input type="checkbox">'.__('Select All','themify-updater').'</label>
        <div class="themify-updater-batch-install-btn themify-updater-button" data-update="'.self_admin_url( 'update.php' ).'" data-type="'.$this->type.'">'.__('Install Selected','themify-updater').'</div>
    </div>';
?>
<?php if('theme'===$this->type):?>
<div class="promote-themes themify-updater-wrapper" style="display:none;">
    <?php echo $batch_btn; ?>
    <div class="container"></div>
</div>
<script type="text/html" id="tmpl-themify-featured-theme-item">
    <ol class="grid3 theme-list tf_clearfix">
        <# var extra = data.extra, themify_promotion = window.themify_promotion; #>
        <# jQuery.each( data, function( i, e ) { #>
        <li class="theme-post">
            <figure class="theme-image">
                <a href="{{{e.url}}}" target="_blank">
                    <img src="https://themify.me/wp-content/product-img/{{{e.slug}}}-thumb.jpg" alt="{{{e.title}}}">
                </a>
            </figure>
            <div class="theme-info">
                <div class="theme-title">
                    <h3><a href="{{{e.url}}}" target="_blank">{{{e.title}}}</a></h3>
                    <a class="tag-button lightbox" target="_blank" href="https://themify.me/demo/themes/{{{e.slug}}}"><?php _e( 'demo', 'themify-updater' ); ?></a>
                </div>
                <!-- /theme-title -->
                <div class="theme-excerpt">
                    <p>{{{e.description}}}</p>
                    <#
                    if ( typeof themify_promotion !== 'undefined' && !jQuery.isEmptyObject( themify_promotion )) {
                    #>
                    <# for ( promotion in themify_promotion['install'] ) {
                    if ( themify_promotion['install'][promotion]['promo'] == e.slug ){ #>
                    <a class="themify-updater-button lightbox" href="#" data-title="{{{e.title}}} {{{themify_promotion['install'][promotion]['ver']}}}" onclick="themify_updater_install( event , '{{{themify_promotion['install'][promotion]['name']}}}' , '{{{extra.type}}}' , '{{{promotion}}}' )"><?php _e( 'Install', 'themify-updater' ); ?></a>
                    <div class="themify-updater-batch-wrap">
                        <label><input data-title="{{{e.title}}} {{{themify_promotion['install'][promotion]['ver']}}}" data-slug="{{{themify_promotion['install'][promotion]['name']}}}" data-nonce="{{{promotion}}}" data-action="install" type="checkbox" class="themify-updater-batch-checkbox"/><?php _e( 'Install', 'themify-updater' ); ?></label>
                    </div>
                    <#		temp=true;
                    break;
                    }
                    }
                    for ( promotion in themify_promotion['buy'] ) {
                    if ( themify_promotion['buy'][promotion]['promo'] == e.slug ){ #>
                    <a class="themify-updater-button lightbox" href="{{{e.url}}}" target="_blank" ><?php _e( 'Buy', 'themify-updater' ); ?></a>
                    <#		temp=true;
                    break;
                    }
                    }
                    for ( promotion in themify_promotion['installed'] ) {
                    if ( themify_promotion['installed'][promotion]['promo'] == e.slug ){
                    #>
                    <label class="themify-updater-dropdown" tabindex="1">
                        <div class="themify-updater-dd-button themify-updater-button"><?php _e( 'Re-install', 'themify-updater'); ?></div>
                        <input type="checkbox" class="themify-updater-dd-input" >
                        <ul class="themify-updater-dd-menu" data-title="{{{e.title}}}" onclick="themify_updater_previous_reinstall(event, '{{{themify_promotion['installed'][promotion]['name']}}}' , 'theme', '{{{promotion}}}' )">{{{ themify_promotion['installed'][promotion]['old_version'] }}}</ul>
                    </label>
                    <div class="themify-updater-batch-wrap">
                        <label><input data-title="{{{e.title}}}" data-slug="{{{themify_promotion['installed'][promotion]['name']}}}" data-nonce="{{{promotion}}}" data-action="upgrade" type="checkbox" class="themify-updater-batch-checkbox"/></label>
                        <select>{{{ themify_promotion['installed'][promotion]['options'] }}}</select>
                    </div>
                    <#		temp=true;
                    break;
                    }
                    }
                    #>
                    <# } #>
                </div>
                <!-- /theme-excerpt -->
            </div>
            <!-- /theme-info -->
        </li>
        <# } ) #>
    </ol>
</script>
<?php else: ?>
<div class="promote-plugins themify-updater-wrapper" style="display:none;">
	<ul class="plugin-category">
		<li class="active" data-type="promo-plugins"><a href="#">Plugins</a></li>
		<li data-type="promo-builder-addons"><a href="#">Builder Addons</a></li>
		<li data-type="promo-ptb-addons"><a href="#">PTB Addons</a></li>
	</ul>
    <?php echo $batch_btn; ?>
	<div class="container"></div>
</div>
<script type="text/html" id="tmpl-themify-featured-plugin-item">
<ol class="grid3 theme-list tf_clearfix">
    <# var extra = data.extra, demolink = 'https://themify.me/demo/themes/', themify_promotion = window.themify_promotion;
    jQuery.each( data, function( i, e ) {
		if (e.category === 'promo-builder-addons') {
			e.demolink =  demolink + 'addon-' + e.slug;
		} else if (e.category === 'promo-plugins') {
			e.demolink =  demolink + e.slug;
			switch (e.slug) {
				case 'shopify-buy-button': e.demolink = demolink + 'simple'; break;
				case 'themify-product-filter': e.demolink = demolink + 'wc-product-filter'; break;
				case 'post-type-builder': e.demolink = demolink + 'ptb-bundle'; break;
				case 'themify-icons': e.demolink = e.url; break;
				case 'event-post': e.demolink = demolink + 'events-post'; break;
			}
		} else {
			e.demolink =  demolink + 'ptb-addon-' + e.slug;
			switch (e.slug) {
				case 'relation': e.demolink = demolink + 'ptb-bundle/celebrity-relation/'; break;
				case 'map-view': e.demolink = demolink + 'ptb-bundle/map-view/'; break;
				case 'search': e.demolink = demolink + 'ptb-bundle/properties/'; break;
			}
		}
	#>
		<li class="theme-post {{{e.category}}}">
			<figure class="theme-image">
				<a href="{{{e.url}}}" target="_blank">
                    <img src="https://themify.me/wp-content/product-img/{{{ e.url.replace('https:\/\/themify.me\/', '') }}}.jpg" alt="{{{e.title}}}">
				</a>
			</figure>
			<div class="theme-info">
				<div class="theme-title">
					<h3><a href="{{{e.url}}}" target="_blank">{{{e.title}}}</a></h3>
				    	<a class="tag-button lightbox" target="_blank" href="{{{e.demolink}}}"><?php _e( 'demo', 'themify-updater' ); ?></a>
                </div>
				<!-- /theme-title -->
				<div class="theme-excerpt" tabindex="1">
					<p>{{{e.description}}}</p>
                    <# if ( typeof themify_promotion !== 'undefined' && !jQuery.isEmptyObject( themify_promotion ) ) { #>
                    <# for ( promotion in themify_promotion['install'] ) {
							if ( themify_promotion['install'][promotion]['promo'] == e.slug ){ #>
                        <a class="themify-updater-button lightbox" href="#" data-title="{{{e.title}}} {{{themify_promotion['install'][promotion]['ver']}}}" onclick="themify_updater_install( event , '{{{themify_promotion['install'][promotion]['name'].replace('-plugin','')}}}' , '{{{extra.type}}}' , '{{{promotion}}}' )"><?php _e( 'Install', 'themify-updater' ); ?></a>
                        <div class="themify-updater-batch-wrap">
                            <label><input data-title="{{{e.title}}} {{{themify_promotion['install'][promotion]['ver']}}}" data-slug="{{{themify_promotion['install'][promotion]['name'].replace('-plugin','')}}}" data-nonce="{{{promotion}}}" data-action="install" type="checkbox" class="themify-updater-batch-checkbox"/><?php _e( 'Install', 'themify-updater' ); ?></label>
                        </div>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['buy'] ) { 
							if ( themify_promotion['buy'][promotion]['promo'] == e.slug ){ #>
                        <a class="themify-updater-button lightbox" href="{{{e.url}}}" target="_blank" ><?php _e( 'Buy', 'themify-updater' ); ?></a>
                    <#		temp=true;
							break;
							}
						}
					for ( promotion in themify_promotion['installed'] ) { 
							if ( themify_promotion['installed'][promotion]['promo'] == e.slug ){
                    #>
                    <label class="themify-updater-dropdown" tabindex="1">
                        <div class="themify-updater-dd-button themify-updater-button"><?php _e( 'Re-install', 'themify-updater'); ?></div>
                        <input type="checkbox" class="themify-updater-dd-input" >
                        <ul class="themify-updater-dd-menu" data-title="{{{e.title}}}" onclick="themify_updater_previous_reinstall(event, '{{{themify_promotion['installed'][promotion]['name']}}}' , 'plugin', '{{{promotion}}}' )">{{{ themify_promotion['installed'][promotion]['old_version'] }}}</ul>
                    </label>
                    <div class="themify-updater-batch-wrap">
                        <label><input data-title="{{{e.title}}}" data-slug="{{{themify_promotion['installed'][promotion]['name']}}}" data-nonce="{{{promotion}}}" type="checkbox" data-action="upgrade" class="themify-updater-batch-checkbox"/></label>
                        <select>{{{ themify_promotion['installed'][promotion]['options'] }}}</select>
                    </div>
                    <#	temp=true;
							break;
							}
						}
					#>
                    <# } #>
                </div>
				<!-- /theme-excerpt -->
			</div>
			<!-- /theme-info -->	
		</li>
	<# } ) #>
</ol>
</script>
<?php endif; ?>
<script>

	jQuery(function($) {
		
		
		var promo_data = false,
		 type = "<?php echo $this->type; ?>",
		 container = $('.promote-'+ type +'s .container');
		
		$(document).on('themify_update_promo', function () {
			
			container.parent().show();

			if (!promo_data) {
				container.text('Loading...');
				$.getJSON( 'https://themify.me/public-api/featured-'+ type +'s/index.json' )
				.done(function( data ){
					data.currentThemeURI = "<?php echo $current_theme->display( 'ThemeURI' ); ?>";
					data.installLink = "<?php echo esc_url( wp_nonce_url( add_query_arg('install', '%themify_updater%'), 'install_product_' . $_GET['promotion'] ) ); ?>";
					data.extra = {'type': type};

					promo_data = data;
					$(document).trigger('themify_update_promo');
				}).fail(function( jqxhr, textStatus, error ){
					container.html( '<p><?php _e( 'Something went wrong while fetching the Featured Themes. Please try again later.', 'themify-updater' ); ?></p>' );
				});
				
				if (type == 'plugin') {
					$('.promote-plugins ul.plugin-category a').on('click', themify_plugin_change_cat);
				}
			}
			
			var template = wp.template( 'themify-featured-'+ type +'-item' );
			container.html( template( promo_data ) );
			$(document).trigger('themify_updater_init_batch');

            $( "select.themify_updater_reinstall_select" ).on('select',function() {
                console.log( "Handler for .select() called." );
            });
			if (type == 'plugin') {
				$('.promote-plugins ul.plugin-category li.active a').click();
			}
			
		}).ready( function () {
			$(document).trigger('themify_update_promo');
		});
		
		function themify_plugin_change_cat (e) {

			e.preventDefault();
			e.stopPropagation();
			
			$th = $(e.target).parent();
			$th.addClass('active').siblings().removeClass('active');
			const selectAll = document.querySelector('.batch-install-all input');
			if(selectAll){
				selectAll.checked = $th[0].hasAttribute('data-checked');
            }
			$item = $('.theme-post.'+ $th.data('type')).show();
			$siblings = $item.siblings('li:not(.'+ $th.data('type') +')');

			$siblings.hide();
			
			$item.parent().append($siblings);
		}
	
	}(jQuery));
	
	function themify_updater_install (e , slug, product_type, nonce, action, version,title ) {
		if (e) e.preventDefault();

		if (!confirm(themify_upgrader.installation_message)) return;

		title = e ? e.target.dataset.title : title;
		if ( typeof version === 'undefined' ) {
			version = '';
		}
		else if(version && typeof themify_vars!=='undefined' && themify_vars.theme_v !== undefined ){
			/* message display when downgrading below version 7 */
			const vv=parseInt(version[0].trim()),
                currentV=parseInt(themify_vars.theme_v[0].trim());
			if(vv<7 && currentV>=7 && !confirm(themify_upgrader.v7_message)){
				return;
			}
		}
		adminLink = "<?php echo self_admin_url( 'update.php' ); ?>";
		let url;
		if ( typeof action !== 'undefined' && action === 'upgrade') {
			url = adminLink + "?action=upgrade-" + product_type + "&" + product_type + "=" + slug + "&_wpnonce=" + nonce + "&themify_theme_downgrade=1";
			if ( version ) {
				url += '&version=' + version;
			}
		} else {
			url = adminLink + "?action=install-" + product_type + "&" + product_type + "=" + slug + "&_wpnonce=" + nonce;
		}
		ThemifyUpdater.bulkInstall({[title]:url},ThemifyUpdater.initModal(true));
	}

	function themify_updater_previous_reinstall (e, slug, product_type, nonce) {
		const title = e.target.parentNode.dataset.title + ' ' + e.target.dataset.version,
			version = e.target.dataset.latest ? undefined : e.target.dataset.version;
            themify_updater_install(null, slug, product_type, nonce, 'upgrade', version, title );
        }

</script>
