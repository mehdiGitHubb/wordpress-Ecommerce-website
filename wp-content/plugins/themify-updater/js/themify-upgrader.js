;
var ThemifyUpdater;
(function($, window, document){

	'use strict';
	ThemifyUpdater = {
		showAlert:function(){
			$(".themify_updater_alert").addClass("busy").fadeIn(800);
		},
		hideAlert:function(status){
			status = status === 'error' ? 'error' : 'done';
			$(".themify_updater_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function(){
				$(this).removeClass(status);
			});
		},
		events:function(){
			$('#themify-updater-search .clear-search').on('click', function(){
				$(this).prev().val('');
				$(document).trigger('themify_update_promo');
			});

			$('#themify-updater-search .promo-search').on('input', function(e){

				var $this = $(this),
					searchText = $this.val().toLowerCase(),
					$clear = $this.siblings('.clear-search');

				if(searchText != '' && $('.theme-list li').length > 0){

					$('.theme-list li').filter(function(){
						var $th = $(this);
						if($th.is(":visible") == true && $th.find('.theme-title h3').text().toLowerCase().indexOf(searchText) > -1){
							$th.show();
						}else{
							$th.hide().parent().append($th);
						}

					});

				}

				if(searchText == ''){
					$clear.click().hide();
				}else{
					$clear.show();
				}
			});

			ThemifyUpdater.updateThemeBtn();

			//
			// Upgrade Theme and Plugins
			//

			$('#wpbody').on('click', 'a.themify-updater', function(e){
				e.preventDefault();

				if ( $(this).hasClass('themify-updater-stop') ) {
					$(this).closest('.notifications').after('<div id="themifyUpdateRrror" class="notice notice-error is-dismissible">'+ themify_upgrader_license.error_message +'</div>');
					return;
				}

				if (!confirm(themify_upgrader.check_backup)) return;

				var $this = $(this),
					$parent =$this.closest('.notifications'),
					action = $this.data('update_type'),
					data = {
						slug: $this.data('plugin'),
						action: action.substring(0, action.length - 1),
						_ajax_nonce: $this.data('nonce'),
						_fs_nonce: '',
						username: '',
						password: '',
						connection_type: '',
						public_key: '',
						private_key: ''
					};
				if ( action === 'update-plugins' ) {
					data.plugin = $this.data('base');
				}
				$.ajax({
					url:ajaxurl,
					type:'POST',
					data:data,
					beforeSend: function(){
						ThemifyUpdater.showAlert();
					},
					success: function(response) {
						response = typeof response === 'string' ? JSON.parse(response) : response;
						if (response.success) {
							ThemifyUpdater.hideAlert();
							setTimeout( function(){ location.reload(); },1500);
						} else {
							ThemifyUpdater.hideAlert('error');
							if ( $parent.siblings("#themifyUpdateRrror").length > 0) {
								$parent.siblings("#themifyUpdateRrror").remove();
							}
							$parent.after('<div id="themifyUpdateRrror" class="notice notice-error is-dismissible">'+ response.data.errorMessage +'</div>');
						}
					},
					error: function() {
						ThemifyUpdater.hideAlert('error');
					}
				});
			});

			$('.themify_updater_changelogs').on('click', function(e){
				e.preventDefault();
				var $self = $(this),
					url = $self.data('changelog');
				$('.themify-updater-promt-box .show-error').hide();
				$('.themify_updater_alert').addClass('busy').fadeIn(300);
				$('.themify_updater_promt_overlay,.themify-updater-promt-box').fadeIn(300);

				$('<iframe src="' + url + '" />').on('load', function(){
					$('.themify_updater_alert').removeClass('busy').fadeOut(300);
				}).prependTo('.themify-updater-promt-box');
				$('.themify-updater-promt-box').addClass('show-changelog');

				$('.themify_updater_promt_overlay').one('click', function(e){
					$(this).fadeOut(300);
					$('.themify-updater-promt-box').fadeOut(300).find('iframe').remove();
				});

			});

			$('.notifications .notification-group span:first-child').on('click', function(e){
				e.preventDefault();
				$(this).siblings().slideToggle();
			});

			// Batch Install
			$(document).on('themify_updater_init_batch', function () {
				if(document.getElementsByClassName('themify-updater-batch-wrap').length>0){
					ThemifyUpdater.initBatchInstaller();
				}
			});
		},
		initBatchInstaller:function(){
			const batch_toolbar = document.querySelector('.themify-updater-batch-install'),
				bulkInstallCheckbox = batch_toolbar.querySelector('.themify-updater-batch-install .batch-install-enable');
			batch_toolbar.style.display='block';
			bulkInstallCheckbox.addEventListener('change',function(){
				document.body.classList.toggle('themify-updater-batch-mode');
			});
			batch_toolbar.querySelector('.themify-updater-batch-install-btn').addEventListener('click',function(){
				const marked = document.querySelectorAll('.themify-updater-batch-checkbox:checked'),
					len = marked.length;
				if(len===0){
					alert('Please select at least on item to install.');
					return;
				}
				let bulkList = {};
				for(let i=len-1;i>-1;i--){
					let url = this.dataset.update + "?action="+marked[i].dataset.action+"-" + this.dataset.type + "&" + this.dataset.type + "=" + marked[i].dataset.slug + "&_wpnonce=" + marked[i].dataset.nonce;
					if ( marked[i].dataset.action === 'upgrade') {
						let select = marked[i].parentNode.nextElementSibling;
						const version = select && select.value ? select.value : '';
						url += "&themify_theme_downgrade=1";
						if ( !select.selectedOptions[0].hasAttribute('data-latest') ) {
							url += '&version=' + version;
						}
						if(''!==version){
							marked[i].dataset.title = marked[i].dataset.title + ' ' + version;
						}
					}
					bulkList[marked[i].dataset.title]=url;
				}
				ThemifyUpdater.bulkInstall(bulkList,ThemifyUpdater.initModal());
			});
			const selectAll = document.querySelector('.batch-install-all input'),
				checkboxes = document.querySelectorAll('.theme-post:not([style^=display]) .themify-updater-batch-checkbox'),
				checked = function(){
					this.closest('.themify-updater-batch-wrap').classList.toggle('batch-checked');
				};
			for(let i=checkboxes.length-1;i>-1;i--){
				checkboxes[i].addEventListener('change',checked);
			}
			selectAll.addEventListener('change',function(){
				const checked = this.checked,
					checkboxes = document.querySelectorAll('.theme-post:not([style^=display]) .themify-updater-batch-checkbox');
				for(let i=checkboxes.length-1;i>-1;i--){
					checkboxes[i].checked=checked;
					const cl = checkboxes[i].closest('.themify-updater-batch-wrap').classList;
					if(checked){
						cl.add('batch-checked');
					}else{
						cl.remove('batch-checked');
					}
				}
				const plugins_tab = document.querySelector('.plugin-category .active');
				if(plugins_tab){
					if(checked){
						plugins_tab.dataset.checked = checked;
					}else if(plugins_tab.dataset.checked){
						delete plugins_tab.dataset.checked;
					}
				}
			});
		},
		bulkInstall:function(list,modal){
			const keys = Object.keys(list),
				li = document.createElement('li'),
				item = document.createElement('span');
			li.appendChild(document.createTextNode(themify_upgrader.installing));
			item.className='themify-updater-batch-name';
			item.innerText=keys[0]+' ';
			li.appendChild(item);
			li.className='themify-updater-batch-installing';
			modal.appendChild(li);
			fetch(list[keys[0]], {headers:new Headers({'X-Requested-With': 'XMLHttpRequest'})})
				.then(res=>res.text())
				.then(function (html) {
					const doc = (new DOMParser()).parseFromString(html, 'text/html'),
						activate_link = doc.querySelector('.wrap a[href*=activate]');
					li.innerHTML='';
					li.appendChild(item);
					if(!modal.dataset.activate && activate_link){
						const lnk = document.createElement('a');
						lnk.href=activate_link.href;
						lnk.className='themify-updater-batch-activate';
						lnk.innerText=themify_upgrader.activate_lnk;
						li.appendChild(lnk);
					}else{
						li.appendChild(document.createTextNode(themify_upgrader.installed));
					}
					li.classList.remove('themify-updater-batch-installing');
					li.classList.add('themify-updater-batch-installed');
					delete list[keys[0]];
					if(keys.length>1){
						ThemifyUpdater.bulkInstall(list,modal);
					}else{
						modal.parentNode.querySelector('.themify-updater-modal-loading').remove();
						const wrap = modal.nextElementSibling;
						if(modal.dataset.activate && activate_link){
							const activate = document.createElement('a');
							activate.className='themify-updater-modal-activate themify-updater-button';
							activate.href = activate_link.href;
							activate.innerText=themify_upgrader.activate;
							wrap.appendChild(activate);
						}
						const done = document.createElement('div');
						done.className='themify-updater-modal-done themify-updater-button';
						done.setAttribute('onclick','location.reload()');
						done.innerText=themify_upgrader.done;
						wrap.appendChild(done);
					}
				}).catch(function (err) {
				console.warn('Update error.', err);
			});
		},
		initModal:function(activate){
			document.getElementById('wpbody-content').style.pointerEvents='none';
			document.body.style.overflow='hidden';
			// Create and open modal
			const modal = document.createElement('div');
			modal.className='themify-updater-modal tf_scrollbar';
			const list = document.createElement('ul');
			list.className = 'themify-updater-bulk-list tf_scrollbar';
			if(true === activate){
				list.dataset.activate = true;
			}
			modal.appendChild(list);
			const loader = document.createElement('div');
			loader.className='themify-updater-modal-loading';
			modal.appendChild(loader);
			const buttons = document.createElement('div');
			buttons.className = 'themify-updater-modal-btns';
			modal.appendChild(buttons);
			document.body.appendChild(modal);
			modal.addEventListener('click',function(e){
				const target = e.target;
				if(target.tagName==='A' && target.classList.contains('themify-updater-batch-activate')){
					e.preventDefault();
					e.stopPropagation();
					if ( target.dataset.loading ) {
						return;
					}
					ThemifyUpdater.showAlert();
					fetch(target.href, {headers:new Headers({'X-Requested-With': 'XMLHttpRequest'})})
						.then(res=>res.text())
						.then(function (html) {
							ThemifyUpdater.hideAlert();
							delete target.dataset.loading;
							target.parentNode.replaceChild(document.createTextNode(themify_upgrader.activated), target);
						}).catch(function (err) {
						ThemifyUpdater.hideAlert('error');
						delete target.dataset.loading;
					});
				}
			});
			return list;
		},
		updateThemeBtn:function(){
			$('.upgrade-theme-button').on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				const link=this,
					versionEl = document.getElementById( 'themeversiontoreinstall' );
				if ( link.dataset.loading ) {
					return;
				}
				var data = JSON.parse(atob( $(e.target).data('install') )),
					version = versionEl.value,
					url = data.url + '?',
					vv=version?parseInt(version[0].toString().trim()):null;
				if(vv!==null && vv<7 && typeof themify_vars!=='undefined'){
					const currentV = themify_vars.theme_v ? parseInt(themify_vars.theme_v[0].toString().trim()) : 0;
					if(currentV>=7 && !confirm(themify_upgrader.v7_message)){
						return;
					}
				}
				delete data.url;

				for ( var i in data) {
					url += i + '=' + data[i] + '&';
				}
				url += 'themify-theme=1';

				/* first option is always "Latest" version, skip version number */
				if ( versionEl.selectedIndex !== 0 ) {
					url += '&version=' + version;
				}

				ThemifyUpdater.showAlert();
				fetch(url, {headers:new Headers({'X-Requested-With': 'XMLHttpRequest'})})
					.then(res=>res.text())
					.then(function (html) {
						ThemifyUpdater.hideAlert();
						delete link.dataset.loading;
						window.location.reload();
					}).catch(function (err) {
					ThemifyUpdater.hideAlert('error');
					delete link.dataset.loading;
				});
			});
		}
	};

	ThemifyUpdater.events();

}(jQuery, window, document));
