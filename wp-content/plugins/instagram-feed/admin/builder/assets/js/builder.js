var sbiBuilder,
	sbiStorage = window.localStorage,
	sketch = VueColor.Sketch;





/**
 * VueJS Global App Builder
 *
 * @since 4.0
 */
sbiBuilder = new Vue({
	el: '#sbi-builder-app',
	http: {
		emulateJSON: true,
		emulateHTTP: true
	},
	components: {
		'sketch-picker': sketch,
	},
	mixins: [VueClickaway.mixin],
	data: {
		nonce: sbi_builder.nonce,

		template: sbi_builder.feedInitOutput,
		templateRender: false,
		updatedTimeStamp: new Date().getTime(),
		feedSettingsDomOptions: null,

		$parent: this,
		plugins: sbi_builder.installPluginsPopup,
		dismissLite: sbi_builder.instagram_feed_dismiss_lite,
		supportPageUrl: sbi_builder.supportPageUrl,
		builderUrl: sbi_builder.builderUrl,
		pluginType: sbi_builder.pluginType,
		genericText: sbi_builder.genericText,
		ajaxHandler: sbi_builder.ajax_handler,
		adminPostURL: sbi_builder.adminPostURL,
		widgetsPageURL: sbi_builder.widgetsPageURL,
		translatedText: sbi_builder.translatedText,
		socialShareLink: sbi_builder.socialShareLink,
		licenseType: sbi_builder.licenseType,
		freeCtaShowFeatures: false,
		upgradeUrl: sbi_builder.upgradeUrl,
		pluginUrl: sbi_builder.pluginUrl,

		welcomeScreen: sbi_builder.welcomeScreen,
		allFeedsScreen: sbi_builder.allFeedsScreen,
		extensionsPopup: sbi_builder.extensionsPopup,
		mainFooterScreen: sbi_builder.mainFooterScreen,
		embedPopupScreen: sbi_builder.embedPopupScreen,

		selectSourceScreen: sbi_builder.selectSourceScreen,
		customizeScreensText: sbi_builder.customizeScreens,
		dialogBoxPopupScreen: sbi_builder.dialogBoxPopupScreen,
		selectFeedTypeScreen: sbi_builder.selectFeedTypeScreen,
		addFeaturedPostScreen: sbi_builder.addFeaturedPostScreen,
		addFeaturedAlbumScreen: sbi_builder.addFeaturedAlbumScreen,
		addVideosPostScreen: sbi_builder.addVideosPostScreen,
		dummyLightBoxData: sbi_builder.dummyLightBoxData,

		svgIcons: sbi_builder.svgIcons,
		feedsList: sbi_builder.feeds,
		manualSourcePopupInit: sbi_builder.manualSourcePopupInit,
		feedTypes: sbi_builder.feedTypes,
		socialInfo: sbi_builder.socialInfo,
		sourcesList: sbi_builder.sources,
		links: sbi_builder.links,
		legacyFeedsList: sbi_builder.legacyFeeds,
		activeExtensions: sbi_builder.activeExtensions,
		advancedFeedTypes: sbi_builder.advancedFeedTypes,



		//Selected Feed type => User Hashtag Tagged
		selectedFeed: ['user'],
		selectedFeedPopup: [],

		selectedSources: [],
		selectedSourcesPopup: [],
		selectedSourcesTagged: [],
		selectedSourcesTaggedPopup: [],
		selectedSourcesUser: [],
		selectedSourcesUserPopup: [],
		selectedHastags: [],
		selectedHastagsPopup: [],
		hashtagInputText: '',
		hashtagOrderBy: 'recent',

		viewsActive: {
			//Screens where the footer widget is disabled
			footerDiabledScreens: [
				'welcome',
				'selectFeed'
			],
			footerWidget: false,

			// welcome, selectFeed
			pageScreen: 'welcome',

			// feedsType, selectSource, feedsTypeGetProcess
			selectedFeedSection: 'feedsType',

			sourcePopup: false,
			feedtypesPopup: false,
			feedtypesCustomizerPopup: false,
			sourcesListPopup: false,
			// step_1 [Add New Source] , step_2 [Connect to a user pages/groups], step_3 [Add Manually]
			sourcePopupScreen: 'redirect_1',

			// creation or customizer
			sourcePopupType: 'creation',
			extensionsPopupElement: false,
			feedTypeElement: null,
			instanceFeedActive: null,
			clipboardCopiedNotif: false,
			legacyFeedsShown: false,
			editName: false,
			embedPopup: false,
			embedPopupScreen: 'step_1',
			embedPopupSelectedPage: null,

			moderationMode: false,

			// onboarding
			onboardingPopup: sbi_builder.allFeedsScreen.onboarding.active,
			onboardingStep: 1,

			// customizer onboarding
			onboardingCustomizerPopup: sbi_builder.customizeScreens.onboarding.active,

			// plugin install popup
			installPluginPopup: false,
			installPluginModal: 'facebook'
		},

		//Feeds Pagination
		feedPagination: {
			feedsCount: sbi_builder.feedsCount != undefined ? sbi_builder.feedsCount : null,
			pagesNumber: 1,
			currentPage: 1,
			itemsPerPage: sbi_builder.itemsPerPage != undefined ? sbi_builder.itemsPerPage : null,
		},

		//Add New Source
		newSourceData: sbi_builder.newSourceData ? sbi_builder.newSourceData : null,
		sourceConnectionURLs: sbi_builder.sourceConnectionURLs,
		returnedApiSourcesList: [],
		addNewSource: {
			typeSelected: 'page',
			manualSourceID: null,
			manualSourceToken: null
		},
		selectedSourcesToConnect: [],

		//Feeds Types Get Info
		extraProcessFeedsTypes: [
			//'events',
			'singlealbum',
			'featuredpost',
			'videos'
		],
		isCreateProcessGood: false,
		feedCreationInfoUrl: null,
		feedTypeOnSourcePopup: 'user',

		feedsSelected: [],
		selectedBulkAction: false,
		singleAlbumFeedInfo: {
			url: '',
			info: {},
			success: false,
			isError: false
		},
		featuredPostFeedInfo: {
			url: '',
			info: {},
			success: false,
			isError: false
		},
		videosTypeInfo: {
			type: 'all',
			info: {},
			playListUrl: null,
			success: false,
			playListUrlError: false
		},

		customizerFeedDataInitial: null,
		customizerFeedData: sbi_builder.customizerFeedData,
		wordpressPageLists: sbi_builder.wordpressPageLists,
		iscustomizerScreen: (sbi_builder.customizerFeedData != undefined && sbi_builder.customizerFeedData != false),

		customizerSidebarBuilder: sbi_builder.customizerSidebarBuilder,
		customizerScreens: {
			activeTab: 'customize',
			printedType: {},
			activeSection: null,
			previewScreen: 'desktop',
			sourceExpanded: null,
			sourcesChoosed: [],
			inputNameWidth: '0px',
			activeSectionData: null,
			parentActiveSection: null, //For nested Setions
			parentActiveSectionData: null, //For nested Setions
			activeColorPicker: null,
			popupBackButton: ['hashtag', 'tagged', 'socialwall', 'feedLayout', 'headerLayout', 'postStyling', 'lightbox', 'filtermoderation', 'shoppablefeed']
		},
		previewScreens: [
			'desktop',
			'tablet',
			'mobile'
		],

		nestedStylingSection: [

		],
		expandedCaptions: [],

		sourceToDelete: {},
		feedToDelete: {},
		dialogBox: {
			active: false,
			type: null, //deleteSourceCustomizer
			heading: null,
			description: null,
			customButtons: undefined
		},

		feedStyle: '',
		expandedPostText: [],
		showedSocialShareTooltip: null,
		showedCommentSection: [],

		//LightBox Object
		lightBox: {
			visibility: 'hidden',
			type: null,
			post: null,
			activeImage: null,
			albumIndex: 0,
			videoSource: null
		},
		highLightedSection: 'all',

		shoppableFeed: {
			postId: null,
			postMedia: null,
			postCaption: null,
			postShoppableUrl: ''
		},

		moderationSettings: {
			list_type_selected: null,
			allow_list: [],
			block_list: []
		},
		customBlockModerationlistTemp: '',
		tooltip: {
			text: '',
			hover: false,
			hoverType: 'outside'
		},
		//Loading Bar
		fullScreenLoader: false,
		appLoaded: false,
		previewLoaded: false,
		loadingBar: true,
		notificationElement: {
			type: 'success', // success, error, warning, message
			text: '',
			shown: null
		},

		//Moderation & Shoppable Mode
		moderationShoppableMode: false,
		moderationShoppableModeAjaxDone: false,
		moderationShoppableModeOffset: 0


	},
	watch: {
		feedPreviewOutput: function () {
			return this.feedPreviewMaker()
		},
	},
	computed: {

		feedStyleOutput: function () {
			return this.customizerStyleMaker();
		},
		singleHolderData: function () {
			return this.singleHolderParams();
		},
		getModerationShoppableMode: function () {
			return false;
		}

	},
	updated: function () {
		if (this.customizerFeedData) {
			this.setShortcodeGlobalSettings(true);
		}
	},
	created: function () {
		var self = this;
		this.$parent = self;
		if (self.customizerFeedData) {
			self.template = String("<div>" + self.template + "</div>");
			self.setShortcodeGlobalSettings(true);

			self.feedSettingsDomOptions = self.jsonParse(jQuery("html").find("#sb_instagram").attr('data-options'));

			self.selectedSources = self.customizerFeedData.settings.id;
			self.selectedSourcesUser = self.customizerFeedData.settings.id;
			self.selectedSourcesTagged = self.customizerFeedData.settings.tagged;
			self.selectedHastags = self.customizerFeedData.settings.hashtag;
			self.selectedFeed = self.getCustomizerSelectedFeedsType();
			self.selectedFeedPopup = self.getCustomizerSelectedFeedsType();

			self.customizerFeedData.settings.shoppablelist = self.jsonParse(self.customizerFeedData.settings.shoppablelist) ? self.jsonParse(self.customizerFeedData.settings.shoppablelist) : [];
			self.customizerFeedData.settings.moderationlist = self.jsonParse(self.customizerFeedData.settings.moderationlist) ? self.jsonParse(self.customizerFeedData.settings.moderationlist) : self.moderationSettings;
			Object.assign(self.moderationSettings, self.customizerFeedData.settings.moderationlist);

			self.customBlockModerationlistTemp = `${self.customizerFeedData.settings.customBlockModerationlist}`;

			self.customizerFeedDataInitial = JSON.parse(JSON.stringify(self.customizerFeedData));

			self.updatedTimeStamp = new Date().getTime();
		}

		if (self.customizerFeedData == undefined) {
			self.feedPagination.pagesNumber = self.feedPagination.feedsCount != null ? Math.ceil(self.feedPagination.feedsCount / self.feedPagination.itemsPerPage) : 1
		}

		window.addEventListener('beforeunload', (event) => {
			if (self.customizerFeedData) {
				self.leaveWindowHandler(event);
			}
		});


		self.loadingBar = false;
		/* Onboarding - move elements so the position is in context */
		self.positionOnboarding();
		setTimeout(function () {
			self.positionOnboarding();
		}, 500);

		self.appLoaded = true;
	},
	methods: {
		updateColorValue: function (id) {
			var self = this;
			self.customizerFeedData.settings[id] = (self.customizerFeedData.settings[id].a == 1) ? self.customizerFeedData.settings[id].hex : self.customizerFeedData.settings[id].hex8;
		},


		/**
		 * Leave Window Handler
		 *
		 * @since 6.0
		 */
		leaveWindowHandler: function (ev) {
			var self = this,
				updateFeedData = {
					action: 'sbi_feed_saver_manager_recache_feed',
					feedID: self.customizerFeedData.feed_info.id,
				};
			self.ajaxPost(updateFeedData, function (_ref) {
				var data = _ref.data;
			});
		},

		/**
		 * Show & Hide View
		 *
		 * @since 6.0
		 */
		activateView: function (viewName, sourcePopupType = 'creation', ajaxAction = false) {
			var self = this;
			if (viewName === 'extensionsPopupElement' && self.customizerFeedData !== undefined && (self.viewsActive.extensionsPopupElement == 'tagged' || self.viewsActive.extensionsPopupElement == 'hashtag')) {
				self.activateView('feedtypesPopup');
			}

			self.viewsActive[viewName] = (self.viewsActive[viewName] == false) ? true : false;
			if (viewName === 'sourcePopup') {
				self.viewsActive.sourcePopupType = sourcePopupType;
				if (self.customizerFeedData != undefined && sourcePopupType != 'updateCustomizer') {
					Object.assign(self.customizerScreens.sourcesChoosed, self.customizerFeedData.settings.sources);
				}
				if (self.customizerFeedData != undefined && sourcePopupType == 'updateCustomizer') {
					//self.viewsActive.sourcesListPopup = true;
					//self.viewsActive.sourcePopupType = 'customizer';
					//self.viewsActive.sourcePopup = true;

					//self.customizerFeedData.settings.sources = self.customizerScreens.sourcesChoosed;
				}

				if (ajaxAction !== false) {
					self.customizerControlAjaxAction(ajaxAction);
				}
			}
			if (viewName === 'feedtypesPopup') {
				self.viewsActive.feedTypeElement = null;
			}

			if (viewName == 'editName') {
				document.getElementById("sbi-csz-hd-input").focus();
			}
			if (viewName == 'embedPopup' && ajaxAction == true) {
				self.saveFeedSettings();
			}

			if ((viewName == 'sourcePopup' || viewName == 'sourcePopupType') && sourcePopupType == 'creationRedirect') {
				self.viewsActive.sourcePopupScreen = 'redirect_1';
				setTimeout(function () {
					self.$refs.addSourceRef.processIFConnect()
				}, 3500);
			}
			sbiBuilder.$forceUpdate();
			self.movePopUp();
		},

		/**
		 * Show/Hide View or Redirect to plugin dashboard page
		 *
		 * @since 4.0
		 */
		activateViewOrRedirect: function (viewName, pluginName, plugin) {
			var self = this;
			if (plugin.installed && plugin.activated) {
				window.location = plugin.dashboard_permalink;
				return;
			}

			self.viewsActive[viewName] = (self.viewsActive[viewName] == false) ? true : false;

			if (viewName == 'installPluginPopup') {
				self.viewsActive.installPluginModal = pluginName;
			}

			self.movePopUp();
			sbiBuilder.$forceUpdate();
		},

		movePopUp: function () {
			var overlay = document.querySelectorAll("sb-fs-boss");
			if (overlay.length > 0) {
				document.getElementById("wpbody-content").prepend(overlay[0]);
			}
		},

		/**
		 * Check if View is Active
		 *
		 * @since 4.0
		 *
		 * @return boolean
		 */
		checkActiveView: function (viewName) {
			return this.viewsActive[viewName];
		},

		/**
		 * Switch & Change Feed Screens
		 *
		 * @since 4.0
		 */
		switchScreen: function (screenType, screenName) {
			this.viewsActive[screenType] = screenName;
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Check if Value is Empty
		 *
		 * @since 4.0
		 *
		 * @return boolean
		 */
		checkNotEmpty: function (value) {
			return value != null && value.replace(/ /gi, '') != '';
		},

		/**
		 * Check if Value exists in Array Object
		 *
		 * @since 4.0
		 *
		 * @return boolean
		 */
		checkObjectArrayElement: function (objectArray, object, byWhat) {
			var objectResult = objectArray.filter(function (elem) {
				return elem[byWhat] == object[byWhat];
			});
			return (objectResult.length > 0) ? true : false;
		},

		/**
		 * Check if Data Setting is Enabled
		 *
		 * @since 4.0
		 *
		 * @return boolean
		 */
		valueIsEnabled: function (value) {
			return value == 1 || value == true || value == 'true' || value == 'on';
		},


		/**
		 * Parse JSON
		 *
		 * @since 4.0
		 *
		 * @return jsonObject / Boolean
		 */
		jsonParse: function (jsonString) {
			try {
				return JSON.parse(jsonString);
			} catch (e) {
				return false;
			}
		},


		/**
		 * Ajax Post Action
		 *
		 * @since 4.0
		 */
		ajaxPost: function (data, callback) {
			var self = this;
			data['nonce'] = this.nonce;
			self.$http.post(self.ajaxHandler, data).then(callback);
		},

		/**
		 * Check if Object has Nested Property
		 *
		 * @since 4.0
		 *
		 * @return boolean
		 */
		hasOwnNestedProperty: function (obj, propertyPath) {
			if (!propertyPath) { return false; } var properties = propertyPath.split('.');
			for (var i = 0; i < properties.length; i++) {
				var prop = properties[i];
				if (!obj || !obj.hasOwnProperty(prop)) {
					return false;
				} else {
					obj = obj[prop];
				}
			}
			return true;
		},


		/**
		 * Feed List Pagination
		 *
		 * @since 4.0
		 */
		feedListPagination: function (type) {
			var self = this,
				currentPage = self.feedPagination.currentPage,
				pagesNumber = self.feedPagination.pagesNumber;
			self.loadingBar = true;
			if ((currentPage != 1 && type == 'prev') || (currentPage < pagesNumber && type == 'next')) {
				self.feedPagination.currentPage = (type == 'next') ?
					(currentPage < pagesNumber ? (parseInt(currentPage) + 1) : pagesNumber) :
					(currentPage > 1 ? (parseInt(currentPage) - 1) : 1);

				var postData = {
					action: 'sbi_feed_saver_manager_get_feed_list_page',
					page: self.feedPagination.currentPage
				};
				self.ajaxPost(postData, function (_ref) {
					var data = _ref.data;
					if (data) {
						self.feedsList = data;
					}
					self.loadingBar = false;
				});
				sbiBuilder.$forceUpdate();
			}
		},

		/**
		 * Choose Feed Type
		 *
		 * @since 6.0
		 */
		chooseFeedType: function (feedTypeEl, iscustomizerPopup = false) {
			var self = this;
			if (feedTypeEl.type == 'user') {
				self.selectedFeed = 'user';
			} else {
				self.viewsActive.extensionsPopupElement = feedTypeEl.type;
				if (self.customizerFeedData !== undefined) {
					self.viewsActive['feedtypesPopup'] = false;
				}
			}
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Choose Feed Type
		 *
		 * @since 6.0
		 */
		selectFeedTypePopup: function (feedTypeEl) {
			var self = this;
			if (feedTypeEl.type != 'socialwall') {
				if (!self.selectedFeedPopup.includes(feedTypeEl.type) && !self.selectedFeed.includes(feedTypeEl.type)) {
					self.selectedFeedPopup.push(feedTypeEl.type);
				} else {
					self.selectedFeedPopup.splice(self.selectedFeedPopup.indexOf(feedTypeEl.type), 1);
				}
			}
		},

		/**
		 * Check Selected Feed Type
		 *
		 * @since 6.0
		 */
		checkFeedTypeSelect: function (feedTypeEl) {
			var self = this;
			if (self.customizerFeedData) {
				return self.selectedFeedPopup.includes(feedTypeEl.type) && feedTypeEl.type != 'socialwall'
			}
			return self.selectedFeed.includes(feedTypeEl.type) && feedTypeEl.type != 'socialwall'
		},

		/**
		 * Confirm Add Feed Type Poup
		 *
		 * @since 6.0
		 */
		addFeedTypePopup: function () {
			var self = this;
			self.selectedFeed = self.selectedFeedPopup.concat(self.selectedFeed);
			self.activateView('feedtypesPopup');
			if (self.customizerFeedData) {
				self.activateView('feedtypesCustomizerPopup');
			}
		},

		/**
		 * Returns The Selected Feeds Type
		 * For Customizer PopUp
		 *
		 * @since 6.0
		 */
		getCustomizerSelectedFeedsType: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;

			switch (customizerSettings.type) {
				case 'user':
					return ['user'];
					break;
				case 'hashtag':
					return ['hashtag'];
					break;
				case 'tagged':
					return ['tagged'];
					break;
				case 'mixed':
					var feedTypes = [];
					if (customizerSettings.id.length > 0) {
						feedTypes.push('user');
					}
					if (customizerSettings.hashtag.length > 0) {
						feedTypes.push('hashtag');
					}
					if (customizerSettings.tagged.length > 0) {
						feedTypes.push('tagged');
					}
					return feedTypes;
					break;
			}

		},

		/**
		 * Choose Feed Type
		 *
		 * @since 6.0
		 */
		checkMultipleFeedType: function () {
			return this.selectedFeed.length > 1;
		},

		/**
		 * Check if Feed Type Source is Active
		 *
		 * @since 6.0
		 */
		checkMultipleFeedTypeActive: function (feedTypeID) {
			return this.selectedFeed.length >= 1 && this.selectedFeed.includes(feedTypeID);
		},

		/**
		 * Customizer
		 * Check if Feed Type Source is Active
		 *
		 * @since 6.0
		 */
		checkMultipleFeedTypeActiveCustomizer: function (feedTypeID) {
			return this.customizerFeedData.settings.type == feedTypeID || (this.customizerFeedData.settings.type == 'mixed' && this.checkFeedTypeHasSources(feedTypeID));
		},

		/**
		 * Customizer
		 * Check if Feed Type Has Sources
		 *
		 * @since 6.0
		 */
		checkFeedTypeHasSources: function (feedTypeID) {
			var self = this;
			switch (feedTypeID) {
				case 'user':
					return self.createSourcesArray(self.customizerFeedData.settings.id).length > 0;
					break;
				case 'hashtag':
					return self.createSourcesArray(self.customizerFeedData.settings.hashtag).length > 0;
					break;
				case 'tagged':
					return self.createSourcesArray(self.customizerFeedData.settings.tagged).length > 0;
					break;
			}
			return false;
		},

		/**
		 * Customizer
		 * Toggle the Feed Types in Popup
		 *
		 * @since 6.0
		 */
		openFeedTypesPopupCustomizer: function () {
			var self = this;
			self.selectedSourcesUserPopup = self.createSourcesArray(self.selectedSourcesUser);
			self.selectedSourcesTaggedPopup = self.createSourcesArray(self.selectedSourcesTagged);
			self.selectedHastagsPopup = self.createSourcesArray(self.selectedHastags);
			self.activateView('feedtypesCustomizerPopup')
		},

		/**
		 * Customizer
		 * Toggle the Feed Types in Popup
		 *
		 * @since 6.0
		 */
		toggleFeedTypesChooserPopup: function () {
			var self = this;
			self.activateView('feedtypesCustomizerPopup');
			self.activateView('feedtypesPopup');
		},

		/**
		 * Customizer
		 * Toggle the Feed Types With Sources Popup
		 *
		 * @since 6.0
		 */
		toggleFeedTypesSourcesPopup: function () {
			var self = this;
			self.activateView('sourcesListPopup');
			if (self.customizerFeedData) {
				self.activateView('feedtypesCustomizerPopup');
			}
		},

		/**
		 * Customizer
		 * Update Feed Type
		 * & Sources/Hashtags
		 * @since 6.0
		 */
		updateFeedTypeAndSourcesCustomizer: function () {
			var self = this;
			self.selectedSourcesUser = JSON.parse(JSON.stringify(self.createSourcesArray(self.selectedSourcesUserPopup)));
			self.selectedSourcesTagged = JSON.parse(JSON.stringify(self.createSourcesArray(self.selectedSourcesTaggedPopup)));
			self.selectedHastags = JSON.parse(JSON.stringify(self.createSourcesArray(self.getFeedHashtagsSaverPopup())));



			self.customizerFeedData.settings.type = self.getFeedTypeSaver();
			self.customizerFeedData.settings.id = self.getFeedIdSourcesSaver();
			self.customizerFeedData.settings.tagged = self.getFeedIdSourcesTaggedSaver();
			self.customizerFeedData.settings.hashtag = self.getFeedHashtagsSaver();


			/**/
			self.customizerControlAjaxAction('feedFlyPreview');
			self.activateView('feedtypesCustomizerPopup');

		},

		/**
		 * Customizer
		 * Cancel Feed Types
		 * & Sources/Hashtags
		 * @since 6.0
		 */
		cancelFeedTypeAndSourcesCustomizer: function () {
			var self = this;
			if (
				JSON.stringify(self.createSourcesArray(self.selectedSourcesUser)) === JSON.stringify(self.createSourcesArray(self.selectedSourcesUserPopup)) &&
				JSON.stringify(self.createSourcesArray(self.selectedSourcesTagged)) === JSON.stringify(self.createSourcesArray(self.selectedSourcesTaggedPopup)) &&
				JSON.stringify(self.createSourcesArray(self.selectedHastags)) === JSON.stringify(self.createSourcesArray(self.getFeedHashtagsSaverPopup())) &&
				JSON.stringify(self.selectedFeedPopup) === JSON.stringify(self.selectedFeed)
			) {
				self.viewsActive['feedtypesPopup'] = false;
				self.viewsActive['feedtypesCustomizerPopup'] = false;
			} else {
				self.openDialogBox('unsavedFeedSources');
			}

		},


		/**
		 * Customizer
		 * Update Feed Type
		 * & Sources/Hashtags
		 * @since 6.0
		 */
		getFeedHashtagsSaverPopup: function () {
			var self = this;
			if (self.checkNotEmpty(self.hashtagInputText)) {
				self.hashtagWriteDetectPopup(true);
			}
			return self.selectedHastagsPopup;
		},



		/**
		 * If max number of source types are added (3)
		 *
		 * @since 6.0
		 */
		maxTypesAdded: function () {
			return this.selectedFeed.length >= 3;
		},

		/**
		 * Check if Feed Type Source is Active
		 *
		 * @since 6.0
		 */
		removeFeedTypeSource: function (feedTypeID) {
			var self = this;
			self.selectedFeed.splice(self.selectedFeed.indexOf(feedTypeID), 1);
			if (feedTypeID == 'user') {
				self.selectedSourcesUser = [];
			}
			else if (feedTypeID == 'tagged') {
				self.selectedSourcesTagged = [];
			}
			else if (feedTypeID == 'hashtag') {
				self.selectedHastags = [];
			}
		},

		/**
		 * Choose Feed Type
		 *
		 * @since 6.0
		 */
		checkSingleFeedType: function (feedType) {
			return this.selectedFeed.length == 1 && this.selectedFeed[0] == feedType;
		},


		//Check Feed Creation Process Sources & Hashtags
		creationProcessCheckSourcesHashtags: function () {
			var self = this;
			if (self.selectedFeed.length > 1) {
				var number = 0;
				if (self.selectedFeed.includes('user') && self.selectedSourcesUser.length >= 1) {
					number += 1;
				}
				if (self.selectedFeed.includes('tagged') && self.selectedSourcesTagged.length >= 1) {
					number += 1;
				}
				if (self.selectedFeed.includes('hashtag') && self.selectedHastags.length >= 1) {
					number += 1;
				}
				return (number > 0);
			} else {
				if (self.selectedFeed.length == 1 && self.selectedFeed[0] == 'hashtag') {
					return (self.selectedHastags.length >= 1 || self.checkNotEmpty(self.hashtagInputText))
				}
			}
			return self.selectedSources.length > 0 ? true : false;
		},

		/*
			Feed Creation Process
		*/
		creationProcessCheckAction: function () {
			var self = this, checkBtnNext = false;
			switch (self.viewsActive.selectedFeedSection) {
				case 'feedsType':
					checkBtnNext = self.selectedFeed != null ? true : false;
					window.sbiSelectedFeed = self.selectedFeed;
					break;
				case 'selectSource':
					checkBtnNext = self.creationProcessCheckSourcesHashtags();
					break;
				case 'feedsTypeGetProcess':

					break;
			}
			return checkBtnNext;
		},
		//Next Click in the Creation Process
		creationProcessNext: function () {
			var self = this;
			switch (self.viewsActive.selectedFeedSection) {
				case 'feedsType':
					if (self.selectedFeed !== null) {
						if (self.selectedFeed === 'socialwall') {
							window.location.href = sbi_builder.pluginsInfo.social_wall.settingsPage;
							return;
						}
						self.switchScreen('selectedFeedSection', 'selectSource');
					}
					break;
				case 'selectSource':
					if (self.selectedSources.length > 0 || self.creationProcessCheckSourcesHashtags()) {
						if (self.checkPeronalAccount()) {
							self.hashtagWriteDetect(true);
							self.isCreateProcessGood = self.creationProcessCheckSourcesHashtags();
						} else {
							self.$refs.personalAccountRef.personalAccountPopup = true;
						}
					}
					break;
				case 'feedsTypeGetProcess':
					break;
			}
			if (self.isCreateProcessGood) {
				self.submitNewFeed();
			}

		},
		changeVideoSource: function (videoSource) {
			this.videosTypeInfo.type = videoSource;
			sbiBuilder.$forceUpdate();
		},

		//Next Click in the Onboarding Process
		onboardingNext: function () {
			this.viewsActive.onboardingStep++;
			this.onboardingHideShow();
			sbiBuilder.$forceUpdate();
		},
		//Previous Click in the Onboarding Process
		onboardingPrev: function () {
			this.viewsActive.onboardingStep--;
			this.onboardingHideShow();
			sbiBuilder.$forceUpdate();
		},
		onboardingHideShow: function () {
			var tooltips = document.querySelectorAll(".sb-onboarding-tooltip");
			for (var i = 0; i < tooltips.length; i++) {
				tooltips[i].style.display = "none";
			}
			document.querySelectorAll(".sb-onboarding-tooltip-" + this.viewsActive.onboardingStep)[0].style.display = "block";

			if (this.viewsActive.onboardingCustomizerPopup) {
				if (this.viewsActive.onboardingStep === 2) {
					this.switchCustomizerTab('customize');
				} else if (this.viewsActive.onboardingStep === 3) {
					this.switchCustomizerTab('settings');
				}
			}

		},
		//Close Click in the Onboarding Process
		onboardingClose: function () {
			var self = this,
				wasActive = self.viewsActive.onboardingPopup ? 'newuser' : 'customizer';

			document.getElementById("sbi-builder-app").classList.remove('sb-onboarding-active');

			self.viewsActive.onboardingPopup = false;
			self.viewsActive.onboardingCustomizerPopup = false;

			self.viewsActive.onboardingStep = 0;
			var postData = {
				action: 'sbi_dismiss_onboarding',
				was_active: wasActive
			};
			self.ajaxPost(postData, function (_ref) {
				var data = _ref.data;
			});
			sbiBuilder.$forceUpdate();
		},
		positionOnboarding: function () {
			var self = this,
				onboardingElem = document.querySelectorAll(".sb-onboarding-overlay")[0],
				wrapElem = document.getElementById("sbi-builder-app");

			if (onboardingElem === null || typeof onboardingElem === 'undefined') {
				return;
			}

			if (self.viewsActive.onboardingCustomizerPopup && self.iscustomizerScreen) {
				if (document.getElementById("sb-onboarding-tooltip-customizer-1") !== null) {
					wrapElem.classList.add('sb-onboarding-active');

					var step1El = document.querySelectorAll(".sbi-csz-header")[0];
					step1El.appendChild(document.getElementById("sb-onboarding-tooltip-customizer-1"));

					var step2El = document.querySelectorAll(".sb-customizer-sidebar-sec1")[0];
					step2El.appendChild(document.getElementById("sb-onboarding-tooltip-customizer-2"));

					var step3El = document.querySelectorAll(".sb-customizer-sidebar-sec1")[0];
					step3El.appendChild(document.getElementById("sb-onboarding-tooltip-customizer-3"));

					self.onboardingHideShow();
				}
			} else if (self.viewsActive.onboardingPopup && !self.iscustomizerScreen) {
				if (sbi_builder.allFeedsScreen.onboarding.type === 'single') {
					if (document.getElementById("sb-onboarding-tooltip-single-1") !== null) {
						wrapElem.classList.add('sb-onboarding-active');

						var step1El = document.querySelectorAll(".sbi-fb-wlcm-header .sb-positioning-wrap")[0];
						step1El.appendChild(document.getElementById("sb-onboarding-tooltip-single-1"));

						var step2El = document.querySelectorAll(".sbi-table-wrap")[0];
						step2El.appendChild(document.getElementById("sb-onboarding-tooltip-single-2"));
						self.onboardingHideShow();
					}
				} else {
					if (document.getElementById("sb-onboarding-tooltip-multiple-1") !== null) {
						wrapElem.classList.add('sb-onboarding-active');

						var step1El = document.querySelectorAll(".sbi-fb-wlcm-header .sb-positioning-wrap")[0];
						step1El.appendChild(document.getElementById("sb-onboarding-tooltip-multiple-1"));

						var step2El = document.querySelectorAll(".sbi-fb-lgc-ctn")[0];
						step2El.appendChild(document.getElementById("sb-onboarding-tooltip-multiple-2"));

						var step3El = document.querySelectorAll(".sbi-legacy-table-wrap")[0];
						step3El.appendChild(document.getElementById("sb-onboarding-tooltip-multiple-3"));

						self.activateView('legacyFeedsShown');
						self.onboardingHideShow();
					}
				}

			}
		},
		//Back Click in the Creation Process
		creationProcessBack: function () {
			var self = this;
			switch (self.viewsActive.selectedFeedSection) {
				case 'feedsType':
					self.switchScreen('pageScreen', 'welcome');
					break;
				case 'selectSource':
					self.switchScreen('selectedFeedSection', 'feedsType');
					break;
				case 'feedsTypeGetProcess':
					self.switchScreen('selectedFeedSection', 'selectSource');
					break;
			}
			sbiBuilder.$forceUpdate();
		},
		getSelectedSourceName: function (sourceID) {
			var self = this;
			var sourceInfo = self.sourcesList.filter(function (source) {
				return source.account_id == sourceID;
			});
			return (sourceInfo.length > 0) ? sourceInfo[0].username : '';
		},

		getSourceIdSelected: function () {
			var self = this;
			if (self.selectedFeed.length == 1 && self.selectedFeed[0] != 'hashtag') {
				return self.selectedSources[0];
			} else if (self.selectedSourcesUser.length >= 1 && self.selectedFeed.length > 1 && self.selectedFeed.includes('user')) {
				return self.selectedSourcesUser[0];
			} else if (self.selectedSourcesTagged.length >= 1 && self.selectedFeed.length > 1 && self.selectedFeed.includes('tagged')) {
				return self.selectedSourcesTagged[0];
			}
			return 'Instagram Feed';

		},


		//Return Feed Type
		getFeedTypeSaver: function () {
			var self = this;
			if (self.selectedFeed.length > 1) {
				return 'mixed';
			}
			return self.selectedFeed[0];
		},

		//Return Sources ID,
		getFeedIdSourcesSaver: function () {
			var self = this;
			if ((self.selectedFeed.length > 1 && self.selectedFeed.includes('user')) || self.customizerFeedData) {
				return self.selectedSourcesUser;
			}
			return (self.selectedFeed.length == 1 && self.selectedFeed.includes('user')) ? self.selectedSources : "";
		},

		//Return Sources ID
		getFeedIdSourcesTaggedSaver: function () {
			var self = this;
			if ((self.selectedFeed.length > 1 && self.selectedFeed.includes('tagged')) || self.customizerFeedData) {
				return self.selectedSourcesTagged;
			}
			return (self.selectedFeed.length == 1 && self.selectedFeed.includes('tagged')) ? self.selectedSources : "";
		},

		//Return Hashtag Saver
		getFeedHashtagsSaver: function () {
			var self = this;
			if (self.selectedFeed.length == 1 && self.selectedFeed[0] == 'hashtag' && self.checkNotEmpty(self.hashtagInputText)) {
				self.hashtagWriteDetect(true);
			}
			if ((self.selectedFeed.length > 1 && self.selectedFeed.includes('hashtag')) || (self.selectedFeed.length == 1 && self.selectedFeed[0] == 'hashtag')) {
				return self.selectedHastags;
			}
			return [];
		},

		//Create & Submit New Feed
		submitNewFeed: function () {
			var self = this,
				newFeedData = {
					action: 'sbi_feed_saver_manager_builder_update',
					sources: self.getFeedIdSourcesSaver(),
					tagged: self.getFeedIdSourcesTaggedSaver(),
					hashtag: self.getFeedHashtagsSaver(),
					order: self.hashtagOrderBy,
					new_insert: 'true',
					sourcename: self.getSelectedSourceName(self.getSourceIdSelected()),
					//feedtype : self.selectedFeed,
					type: self.getFeedTypeSaver()
				};

			self.fullScreenLoader = true;
			self.ajaxPost(newFeedData, function (_ref) {
				var data = _ref.data;
				if (data.feed_id && data.success) {
					window.location = self.builderUrl + '&feed_id=' + data.feed_id;
				}
			});
		},

		//Select Sources
		selectSource: function (source) {
			var self = this;
			if ((source.account_type != 'personal' && self.selectedFeed[0] == 'tagged') || self.selectedFeed[0] == 'user') {
				if (self.selectedSources.includes(source.account_id)) {
					self.selectedSources.splice(self.selectedSources.indexOf(source.account_id), 1);
				} else {
					self.selectedSources.push(source.account_id);
				}
			}
		},

		//Source Ative
		isSourceSelectActive: function (source) {
			var self = this;
			if (self.selectedSources.includes(source.account_id)) {
				return (source.account_type != 'personal' && self.selectedFeed[0] == 'tagged') || self.selectedFeed[0] == 'user';
			}
			return false;
		},

		//Check if source is Disabled
		checkSourceDisabled: function (source) {
			var self = this;
			return (source.account_type == 'personal' && self.selectedFeed[0] == 'tagged');
		},


		//Open Add Source List Popup
		openSourceListPopup: function (feedTypeID) {
			var self = this;
			self.feedTypeOnSourcePopup = feedTypeID;
			if (self.feedTypeOnSourcePopup == 'tagged') {
				self.selectedSourcesPopup = self.createSourcesArray(self.selectedSourcesTagged);
			} else if (self.feedTypeOnSourcePopup == 'user') {
				self.selectedSourcesPopup = self.createSourcesArray(self.selectedSourcesUser);
			}
			self.activateView('sourcesListPopup');
			if (self.customizerFeedData) {
				self.activateView('feedtypesCustomizerPopup');
			}
		},

		//Check if source is Disabled POPUP
		checkSourceDisabledPopup: function (source) {
			var self = this;
			return (source.account_type == 'personal' && self.feedTypeOnSourcePopup == 'tagged');
		},

		//Source Active POPUP
		isSourceSelectActivePopup: function (source) {
			var self = this;
			if (self.selectedSourcesPopup.includes(source.account_id)) {
				return (source.account_type != 'personal' && self.feedTypeOnSourcePopup == 'tagged') || self.feedTypeOnSourcePopup == 'user';
			}
			return false;
		},

		//Select Sources POPUP
		selectSourcePopup: function (source) {
			var self = this;
			if ((source.account_type != 'personal' && self.feedTypeOnSourcePopup == 'tagged') || self.feedTypeOnSourcePopup == 'user') {
				if (self.selectedSourcesPopup.includes(source.account_id)) {
					self.selectedSourcesPopup.splice(self.selectedSourcesPopup.indexOf(source.account_id), 1);
				} else {
					self.selectedSourcesPopup.push(source.account_id);
				}
			}
		},

		//Return Choosed Feed Type
		returnSelectedSourcesByType: function (feedType) {
			var self = this,
				sourcesListByType = [];
			if (feedType == 'user') {
				sourcesListByType = self.sourcesList.filter(function (source) {
					return (self.customizerFeedData) ? self.selectedSourcesUserPopup.includes(source.account_id) : self.selectedSourcesUser.includes(source.account_id);
				});
			} else if (feedType == 'tagged') {
				sourcesListByType = self.sourcesList.filter(function (source) {
					return (self.customizerFeedData) ? self.selectedSourcesTaggedPopup.includes(source.account_id) : self.selectedSourcesTagged.includes(source.account_id);
				});
			}
			return sourcesListByType;
		},

		//Remove Source From Feed Type
		removeSourceFromFeedType: function (source, feedType) {
			var self = this;
			if (feedType == 'user') {
				if (self.customizerFeedData) {
					self.selectedSourcesUserPopup.splice(self.selectedSourcesUserPopup.indexOf(source.account_id), 1)
				} else {
					self.selectedSourcesUser.splice(self.selectedSourcesUser.indexOf(source.account_id), 1)
				}
			} else if (feedType == 'tagged') {
				if (self.customizerFeedData) {
					self.selectedSourcesTaggedPopup.splice(self.selectedSourcesTaggedPopup.indexOf(source.account_id), 1)
				} else {
					self.selectedSourcesTagged.splice(self.selectedSourcesTagged.indexOf(source.account_id), 1)
				}
			}
		},

		/*
			Return Selected Sources / Hashtags
			on The Customizer Control
		*/
		returnSelectedSourcesByTypeCustomizer: function (feedType) {
			var self = this,
				sourcesListNameByType = [];
			if (feedType == 'user') {
				sourcesListNameByType = self.sourcesList.filter(function (source) {
					return self.customizerFeedData.settings.id.includes(source.account_id);

				});
			}
			if (feedType == 'tagged') {
				sourcesListNameByType = self.sourcesList.filter(function (source) {
					return self.customizerFeedData.settings.tagged.includes(source.account_id);
				});
			}
			if (feedType == 'hashtag') {
				sourcesListNameByType = Array.isArray(self.customizerFeedData.settings.hashtag) ? self.customizerFeedData.settings.hashtag : self.customizerFeedData.settings.hashtag.split(',');
			}
			return sourcesListNameByType;
		},

		//Check if source are Array
		createSourcesArray: function (element) {
			var self = this;
			if (Array.isArray(element) && element.length == 1 && !this.checkNotEmpty(element[0])) {
				return [];
			}
			var arrayResult = Array.isArray(element) ? Array.from(element) : Array.from(element.split(','));
			return arrayResult.filter(function (el) {
				return el != null && self.checkNotEmpty(el);
			});
		},

		// Add Source to Feed Type
		addSourceToFeedType: function () {
			var self = this;
			if (self.feedTypeOnSourcePopup == 'tagged') {
				if (!self.customizerFeedData) {
					self.selectedSourcesTagged = self.createSourcesArray(self.selectedSourcesPopup);
					self.selectedSourcesTaggedPopup = self.createSourcesArray(self.selectedSourcesTagged);
				} else {
					self.selectedSourcesTaggedPopup = self.createSourcesArray(self.selectedSourcesPopup);
				}
			} else if (self.feedTypeOnSourcePopup == 'user') {
				if (!self.customizerFeedData) {
					self.selectedSourcesUser = self.createSourcesArray(self.selectedSourcesPopup);
					self.selectedSourcesUserPopup = self.createSourcesArray(self.selectedSourcesUser);
				} else {
					self.selectedSourcesUserPopup = self.createSourcesArray(self.selectedSourcesPopup);
				}
			}
			self.activateView('sourcesListPopup');
			if (self.customizerFeedData) {
				self.activateView('feedtypesCustomizerPopup');
			}
		},

		//Detect Hashtag Writing
		hashtagWriteDetectPopup: function (isProcess = false) {
			var self = this,
				target = window.event;
			if (target.keyCode == 188 || isProcess == true) {
				self.hashtagInputText = self.hashtagInputText.replace(',', '');
				if (self.checkNotEmpty(self.hashtagInputText)) {
					if (self.hashtagInputText[0] !== '#') {
						self.hashtagInputText = '#' + self.hashtagInputText;
					}
					self.selectedHastagsPopup = self.createSourcesArray(self.selectedHastagsPopup);
					self.selectedHastagsPopup.push(self.hashtagInputText);
				}
				self.hashtagInputText = '';
			}
		},

		//Detect Hashtag Writing
		hashtagWriteDetect: function (isProcess = false) {
			var self = this,
				target = window.event;
			if (target.keyCode == 188 || isProcess == true) {
				self.hashtagInputText = self.hashtagInputText.replace(',', '');
				if (self.checkNotEmpty(self.hashtagInputText)) {
					if (self.hashtagInputText[0] !== '#') {
						self.hashtagInputText = '#' + self.hashtagInputText;
					}
					self.selectedHastags = self.createSourcesArray(self.selectedHastags);
					self.selectedHastags.push(self.hashtagInputText);
					self.selectedHastagsPopup = self.createSourcesArray(self.selectedHastags);
				}
				self.hashtagInputText = '';
			}
		},

		//Remove Hashtag from List
		removeHashtag: function (hashtag) {
			var self = this;
			if (self.customizerFeedData) {
				self.selectedHastagsPopup.splice(self.selectedHastagsPopup.indexOf(hashtag), 1);
			} else {
				self.selectedHastags.splice(self.selectedHastags.indexOf(hashtag), 1);
			}
		},



		processDomList: function (selector, attributes) {
			document.querySelectorAll(selector).forEach(function (element) {
				attributes.map(function (attrName) {
					element.setAttribute(attrName[0], attrName[1]);
				});
			});
		},
		openTooltipBig: function () {
			var self = this, elem = window.event.currentTarget;
			self.processDomList('.sbi-fb-onbrd-tltp-elem', [['data-active', 'false']]);
			elem.querySelector('.sbi-fb-onbrd-tltp-elem').setAttribute('data-active', 'true');
			sbiBuilder.$forceUpdate();
		},
		closeTooltipBig: function () {
			var self = this;
			self.processDomList('.sbi-fb-onbrd-tltp-elem', [['data-active', 'false']]);
			window.event.stopPropagation();
			sbiBuilder.$forceUpdate();
		},

		/*
			FEEDS List Actions
		*/

		/**
		 * Switch Bulk Action
		 *
		 * @since 4.0
		 */
		bulkActionClick: function () {
			var self = this;
			switch (self.selectedBulkAction) {
				case 'delete':
					if (self.feedsSelected.length > 0) {
						self.openDialogBox('deleteMultipleFeeds')
					}
					break;
			}
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Duplicate Feed
		 *
		 * @since 4.0
		 */
		feedActionDuplicate: function (feed) {
			var self = this,
				feedsDuplicateData = {
					action: 'sbi_feed_saver_manager_duplicate_feed',
					feed_id: feed.id
				};
			self.ajaxPost(feedsDuplicateData, function (_ref) {
				var data = _ref.data;
				self.feedsList = Object.values(Object.assign({}, data));
				//self.feedsList = data;
			});
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Delete Feed
		 *
		 * @since 4.0
		 */
		feedActionDelete: function (feeds_ids) {
			var self = this,
				feedsDeleteData = {
					action: 'sbi_feed_saver_manager_delete_feeds',
					feeds_ids: feeds_ids
				};
			self.ajaxPost(feedsDeleteData, function (_ref) {
				var data = _ref.data;
				self.feedsList = Object.values(Object.assign({}, data));
				self.feedsSelected = [];
			});
		},

		/**
		 * View Feed Instances
		 *
		 * @since 4.0
		 */
		viewFeedInstances: function (feed) {
			var self = this;
			self.viewsActive.instanceFeedActive = feed;
			self.movePopUp();
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Select All Feeds in List
		 *
		 * @since 4.0
		 */
		selectAllFeedCheckBox: function () {
			var self = this;
			if (!self.checkAllFeedsActive()) {
				self.feedsSelected = [];
				self.feedsList.forEach(function (feed) {
					self.feedsSelected.push(feed.id);
				});
			} else {
				self.feedsSelected = [];
			}

		},

		/**
		 * Select Single Feed in List
		 *
		 * @since 4.0
		 */
		selectFeedCheckBox: function (feedID) {
			if (this.feedsSelected.includes(feedID)) {
				this.feedsSelected.splice(this.feedsSelected.indexOf(feedID), 1);
			} else {
				this.feedsSelected.push(feedID);
			}
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Check if All Feeds are Selected
		 *
		 * @since 4.0
		 */
		checkAllFeedsActive: function () {
			var self = this,
				result = true;
			self.feedsList.forEach(function (feed) {
				if (!self.feedsSelected.includes(feed.id)) {
					result = false;
				}
			});

			return result;
		},


		/**
		 * Copy text to clipboard
		 *
		 * @since 4.0
		 */
		copyToClipBoard: function (value) {
			var self = this;
			const el = document.createElement('textarea');
			el.className = 'sbi-fb-cp-clpboard';
			el.value = value;
			document.body.appendChild(el);
			el.select();
			document.execCommand('copy');
			document.body.removeChild(el);
			self.notificationElement = {
				type: 'success',
				text: this.genericText.copiedClipboard,
				shown: "shown"
			};
			setTimeout(function () {
				self.notificationElement.shown = "hidden";
			}, 3000);
			sbiBuilder.$forceUpdate();
		},

		/*-------------------------------------------
			CUSTOMIZER FUNCTIONS
		-------------------------------------------*/
		/**
		 * HighLight Section
		 *
		 * @since 4.0
		 */
		isSectionHighLighted: function (sectionName) {
			var self = this;
			return (self.highLightedSection === sectionName || self.highLightedSection === 'all')
		},

		/**
		 * Enable Highlight Section
		 *
		 * @since 4.0
		 */
		enableHighLightSection: function (sectionId) {
			var self = this,
				listPostSection = ['customize_feedlayout', 'customize_colorschemes', 'customize_posts', 'post_style', 'individual_elements'],
				headerSection = ['customize_header'],
				followButtonSection = ['customize_followbutton'],
				loadeMoreSection = ['customize_loadmorebutton'],
				lightBoxSection = ['customize_lightbox'],
				domBody = document.getElementsByTagName("body")[0];

			//self.dummyLightBoxData.visibility = 'hidden';
			domBody.classList.remove("no-overflow");

			if (listPostSection.includes(sectionId)) {
				self.highLightedSection = 'postList';
				self.scrollToHighLightedSection("sbi_images");
			} else if (headerSection.includes(sectionId)) {
				self.highLightedSection = 'header';
				self.scrollToHighLightedSection("sb_instagram_header");
			} else if (followButtonSection.includes(sectionId)) {
				self.highLightedSection = 'followButton';
				self.scrollToHighLightedSection("sbi_load");
			} else if (loadeMoreSection.includes(sectionId)) {
				self.highLightedSection = 'loadMore';
				self.scrollToHighLightedSection("sbi_load");
			} else {
				self.highLightedSection = 'all';
				domBody.classList.remove("no-overflow");
			}
		},


		/**
		 * Scroll to Highlighted Section
		 *
		 * @since 4.0
		 */
		scrollToHighLightedSection: function (sectionId) {
			const element = document.getElementById(sectionId) !== undefined && document.getElementById(sectionId) !== null ?
				document.getElementById(sectionId) :
				(document.getElementsByClassName(sectionId)[0] !== undefined && document.getElementsByClassName(sectionId)[0] !== null ? document.getElementsByClassName(sectionId)[0] : null);


			if (element != undefined && element != null) {
				const y = element.getBoundingClientRect().top - 120 + window.pageYOffset - 10;
				window.scrollTo({ top: y, behavior: 'smooth' });
			}
		},

		/**
		 * Enable & Show Color Picker
		 *
		 * @since 4.0
		 */
		showColorPickerPospup: function (controlId) {
			this.customizerScreens.activeColorPicker = controlId;
		},

		/**
		 * Hide Color Picker
		 *
		 * @since 4.0
		 */
		hideColorPickerPospup: function () {
			this.customizerScreens.activeColorPicker = null;
		},

		switchCustomizerPreviewDevice: function (previewScreen) {
			var self = this;
			self.customizerScreens.previewScreen = previewScreen;
			self.loadingBar = true;
			window.sbi_preview_device = previewScreen;
			setTimeout(function () {
				self.setShortcodeGlobalSettings(true);
				self.loadingBar = false;
			}, 200);
			sbiBuilder.$forceUpdate();
		},
		switchCustomizerTab: function (tabId) {
			var self = this,
				domBody = document.getElementsByTagName("body")[0];
			self.customizerScreens.activeTab = tabId;
			self.customizerScreens.activeSection = null;
			self.customizerScreens.activeSectionData = null;
			self.highLightedSection = 'all';

			//self.dummyLightBoxData.visibility = 'hidden';
			domBody.classList.remove("no-overflow");

			if (self.moderationShoppableModeAjaxDone && self.getModerationShoppableMode == false) {
				self.customizerControlAjaxAction('feedFlyPreview');
			}

			sbiBuilder.$forceUpdate();
		},
		switchCustomizerSection: function (sectionId, section, isNested = false, isBackElements) {
			var self = this;
			self.customizerScreens.parentActiveSection = null;
			self.customizerScreens.parentActiveSectionData = null;
			if (isNested) {
				self.customizerScreens.parentActiveSection = self.customizerScreens.activeSection;
				self.customizerScreens.parentActiveSectionData = self.customizerScreens.activeSectionData;
			}
			self.customizerScreens.activeSection = sectionId;
			self.customizerScreens.activeSectionData = section;
			self.enableHighLightSection(sectionId);
			if (sectionId === 'settings_filters_moderation') {
				self.viewsActive['moderationMode'] = false;
			}


			sbiBuilder.$forceUpdate();
		},
		switchNestedSection: function (sectionId, section) {
			var self = this;
			if (section !== null) {
				self.customizerScreens.activeSection = sectionId;
				self.customizerScreens.activeSectionData = section;
			} else {
				var sectionArray = sectionId['sections'];
				var elementSectionData = self.customizerSidebarBuilder;

				sectionArray.map(function (elm, index) {
					elementSectionData = (elementSectionData[elm] != undefined && elementSectionData[elm] != null) ? elementSectionData[elm] : null;
				});
				if (elementSectionData != null) {
					self.customizerScreens.activeSection = sectionId['id'];
					self.customizerScreens.activeSectionData = elementSectionData;
				}
			}
			sbiBuilder.$forceUpdate();
		},
		backToPostElements: function () {
			var self = this,
				individual_elements = self.customizerSidebarBuilder['customize'].sections.customize_posts.nested_sections.individual_elements;
			self.customizerScreens.activeSection = 'customize_posts';
			self.customizerScreens.activeSectionData = self.customizerSidebarBuilder['customize'].sections.customize_posts;
			self.switchCustomizerSection('individual_elements', individual_elements, true, true);
			sbiBuilder.$forceUpdate();
		},

		changeSettingValue: function (settingID, value, doProcess = true, ajaxAction = false) {
			var self = this;
			if (settingID == 'layout' && value !== 'grid') {
				self.viewsActive.extensionsPopupElement = 'feedLayout';
			} else if (settingID == 'headerstyle' && value !== 'standard') {
				self.viewsActive.extensionsPopupElement = 'headerLayout';
			} else if (settingID == 'sortby' && value == 'likes') {
				window.open('https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=customizer&utm_medium=load-more', '_blank');
			}
			else {
				if (doProcess) {
					self.customizerFeedData.settings[settingID] = value;
				}
				if (ajaxAction !== false) {
					self.customizerControlAjaxAction(ajaxAction, settingID);
				}
				self.regenerateLayout(settingID);
			}

		},

		//Shortcode Global Layout Settings
		regenerateLayout: function (settingID) {
			var self = this,
				regenerateFeedHTML = [
					'layout'
				],
				relayoutFeed = [
					'layout',
					'carouselarrows',
					'carouselpag',
					'carouselautoplay',
					'carouseltime',
					'carouselloop',
					'carouselrows',
					'cols',
					'colstablet',
					'colsmobile',
					'highlighttype',
					'highlightoffset',
					'highlightpattern',
					'highlightids',
					'highlighthashtag',
					'imagepadding'
				];
			if (relayoutFeed.includes(settingID)) {
				setTimeout(function () {
					self.setShortcodeGlobalSettings(true);
				}, 200)
			}

		},


		//Get Number of Columns depending on the Preview Screen
		getColsPreviewScreen: function () {
			var self = this;
			if (self.getModerationShoppableMode) {
				return 4;
			}
			switch (self.customizerScreens.previewScreen) {
				case 'mobile':
					return self.customizerFeedData.settings.colsmobile
					break;
				case 'tablet':
					return self.customizerFeedData.settings.colstablet
					break;
				default:
					return self.customizerFeedData.settings.cols
					break;
			}
		},

		//Get Post Number depending on the Preview Screen
		getPostNumberPreviewScreen: function () {
			var self = this;
			switch (self.customizerScreens.previewScreen) {
				case 'mobile':
					return self.customizerFeedData.settings.nummobile
					break;
				case 'tablet':
					return self.customizerFeedData.settings.nummobile
					break;
				default:
					return self.customizerFeedData.settings.num
					break;
			}
		},

		//Get Customizer Additional CSS Classes
		getAdditionalCustomizerClasses: function () {
			var self = this,
				additionalCssClasses = '';
			if (self.getModerationShoppableMode) {
				additionalCssClasses += ' sbi-customizer-ms-modes ';
			}
			return additionalCssClasses;
		},

		//Shortcode Global Layout Settings
		setShortcodeGlobalSettings: function (flyPreview = false) {
			var self = this,
				instagramFeed = jQuery("html").find("#sb_instagram"),
				feedSettings = self.jsonParse(instagramFeed.attr('data-options')),
				customizerSettings = self.customizerFeedData.settings;
			if (JSON.stringify(self.feedSettingsDomOptions) !== JSON.stringify(feedSettings) || flyPreview == true) {
				if (customizerSettings.layout == 'grid' || self.getModerationShoppableMode) {
					feedSettings = self.gridShortcodeSettings(feedSettings, instagramFeed);
				}
				else if (customizerSettings.layout == 'carousel') {
					feedSettings = self.carouselShortcodeSettings(feedSettings, instagramFeed, customizerSettings);
				}
				else if (customizerSettings.layout == 'masonry') {
					feedSettings = self.masonryShortcodeSettings(feedSettings, instagramFeed, customizerSettings);
				}
				else if (customizerSettings.layout == 'highlight') {
					feedSettings = self.highlightShortcodeSettings(feedSettings, instagramFeed, customizerSettings);
				}

				if (flyPreview === true) {
					if (customizerSettings['id'][0] !== undefined) {
						var headerSourceId = customizerSettings['id'][0];
						newHeaderData = null;
						var newHeaderDataMap = self.sourcesList.map(function (source) {
							if (source.account_id === headerSourceId) {
								newHeaderData = source != undefined ? source : null;
							}
						});
						if (newHeaderData !== null && newHeaderData.header_data !== null) {
							self.customizerFeedData.header = newHeaderData;
							self.customizerFeedData.headerData = newHeaderData.header_data;
						}
					}
				}
				instagramFeed.attr("data-options", JSON.stringify(feedSettings));
				//setTimeout(function(){
				window.sbi_init()
				//},200)
				self.feedSettingsDomOptions = feedSettings;
			}
			jQuery('body').find('#sbi_load .sbi_load_btn').unbind('click')
		},

		//Grid Shortcode Settings
		gridShortcodeSettings: function (feedSettings, instagramFeed) {
			var self = this;
			feedSettings['grid'] = true;
			self.destroyHighlightLayout(instagramFeed);
			self.destroyMasonryLayout(instagramFeed);
			self.destoryOwl(instagramFeed);
			delete feedSettings['carousel'];
			delete feedSettings['masonry'];
			delete feedSettings['highlight'];
			return feedSettings;
		},

		//Masonry Shortcode Settings
		masonryShortcodeSettings: function (feedSettings, instagramFeed) {
			var self = this;
			feedSettings['masonry'] = true;
			self.destroyHighlightLayout(instagramFeed);
			self.destoryOwl(instagramFeed);
			delete feedSettings['grid'];
			delete feedSettings['carousel'];
			delete feedSettings['highlight'];
			jQuery('.sbi_photo img').show();
			return feedSettings;
		},

		//Carousel Shortcode Settings
		carouselShortcodeSettings: function (feedSettings, instagramFeed, customizerSettings) {
			var self = this,
				arrows = self.valueIsEnabled(customizerSettings['carouselarrows']),
				pag = self.valueIsEnabled(customizerSettings['carouselpag']),
				autoplay = self.valueIsEnabled(customizerSettings['carouselautoplay']),
				time = autoplay ? parseInt(customizerSettings['carouseltime']) : false,
				loop = self.checkNotEmpty(customizerSettings['carouselloop']) && customizerSettings['carouselloop'] !== 'rewind' ? false : true,
				rows = customizerSettings['carouselrows'] ? Math.min(parseInt(customizerSettings['carouselrows']), 2) : 1;

			feedSettings['carousel'] = [arrows, pag, autoplay, time, loop, rows];
			self.destoryOwl(instagramFeed);
			self.destroyHighlightLayout(instagramFeed);
			self.destroyMasonryLayout(instagramFeed);
			delete feedSettings['grid'];
			delete feedSettings['masonry'];
			delete feedSettings['highlight'];
			return feedSettings;
		},

		//Highlight Shortcode Settings
		highlightShortcodeSettings: function (feedSettings, instagramFeed, customizerSettings) {
			var self = this,
				type = customizerSettings['highlighttype'].trim();
			pattern = customizerSettings['highlightpattern'].trim();
			offset = parseInt(customizerSettings['highlightoffset']),
				hashtag = customizerSettings['highlighthashtag'].replace(',', '|').replace('#', '').replace(' ', '').trim(),
				ids = customizerSettings['highlightids'].replace(',', '|').replace('sbi_', '').replace(' ', '').trim();
			feedSettings['highlight'] = [type, pattern, offset, hashtag, ids];

			self.destroyHighlightLayout(instagramFeed);
			self.destroyMasonryLayout(instagramFeed);
			self.destoryOwl(instagramFeed);
			delete feedSettings['carousel'];
			delete feedSettings['masonry'];
			delete feedSettings['grid'];
			return feedSettings;
		},



		//destroy Owl
		destoryOwl: function (instagramFeed) {
			var self = this;
			var owlCarouselCtn = instagramFeed.find('.sbi_carousel');
			if (instagramFeed.find('#sbi_images').hasClass('sbi_carousel')) {
				//self.customizerControlAjaxAction("feedFlyPreview");
				/*
				if( instagramFeed.hasClass('sbi_carousel_2_row') ){
					owlCarouselCtn.find('.sbi_owl2row-item').each( function(index, element) {
						if(jQuery(element).children().length == 0){
							jQuery(element).remove()
						}else{
							jQuery("#sbi_images").append(jQuery(element).html())
						}
						jQuery(element).parents('.sbi-owl-item').remove();
					});
				}else{
					owlCarouselCtn.find('.sbi-owl-item').each( function(index, element) {
						if(jQuery(element).children().length == 0){
							jQuery(element).remove()
						}else{
							jQuery("#sbi_images").append(jQuery(element).html())
						}
						jQuery(element).parents('.sbi-owl-item').remove();
					});
				}
				owlCarouselCtn.find('.sbi-owl-item,.sbi-owl-stage,.sbi-owl-stage-outer,.sbi-owl-nav,.sbi-owl-nav').remove();
				owlCarouselCtn.removeClass('sbi_carousel');
				instagramFeed.removeClass('sbi_carousel_2_row');
				jQuery("#sb_instagram").removeClass('2rows');
				owlCarouselCtn.sbiOwlCarousel('destroy');
				*/

			}
		},

		//Destroy Masonry Layout
		destroyMasonryLayout: function (instagramFeed) {
			var self = this;
			if (instagramFeed.hasClass('sbi_masonry')) {
				instagramFeed.find('#sbi_images').css({ 'height': 'unset' });
				instagramFeed.find('.sbi_item').each(function () {
					jQuery(this).attr({ 'style': '' });
				});
				jQuery("#sbi_images").smashotope('destroy');
				instagramFeed.removeClass('sbi_masonry')
			}
		},


		//Destroy Highlight Layout
		destroyHighlightLayout: function (instagramFeed) {
			var self = this;
			if (instagramFeed.hasClass('sbi_highlight')) {
				instagramFeed.find('#sbi_images').css({ 'height': 'unset' });
				instagramFeed.find('.sbi_item').each(function () {
					jQuery(this).attr({ 'style': '' });
				});
				jQuery("#sbi_images").smashotope('destroy');
				instagramFeed.removeClass('sbi_highlight')
			}
		},

		//Tablet Cols Classes
		getTabletColsClass: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;

			return ' sbi_tab_col_' + parseInt(customizerSettings.colstablet);
		},

		//Mobile Cols Classes
		getMobileColsClass: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				disableMobile = self.valueIsEnabled(customizerSettings.disablemobile);

			if (disableMobile === 'false') disableMobile = '';

			if (disableMobile !== ' sbi_disable_mobile' && customizerSettings.colsmobile !== 'same') {
				var colsmobile = parseInt(customizerSettings.colsmobile) > 0 ? parseInt(customizerSettings.colsmobile) : 'auto';
				return ' sbi_mob_col_' + colsmobile;
			} else {
				var colsmobile = parseInt(customizerSettings.cols) > 0 ? parseInt(customizerSettings.cols) : 4;
				return ' sbi_disable_mobile sbi_mob_col_' + parseInt(customizerSettings.cols);

			}
		},

		//Header Classes
		getHeaderClass: function (headerType) {
			//return ' header';

			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				headerClasses = 'sb_instagram_header ';


			headerClasses += 'sbi_feed_type_user';
			headerClasses += customizerSettings['headerstyle'] === 'centered' && headerType === 'normal' ? ' sbi_centered' : '';
			headerClasses += ['medium', 'large'].includes(customizerSettings['headersize']) ? ' sbi_' + customizerSettings['headersize'] : '';
			headerClasses += self.getHeaderAvatar() === false ? ' sbi_no_avatar' : '';
			headerClasses += self.getPaletteClass('_header');
			if (customizerSettings.headeroutside) {
				headerClasses += ' sbi_header_outside';
			}
			return headerClasses;
		},

		//Header Name
		getHeaderName: function () {
			var self = this,
				headerData = self.customizerFeedData.headerData;
			if (self.hasOwnNestedProperty(headerData, 'name') && self.checkNotEmpty(headerData['name'])) {
				return headerData['name'];
			} else if (self.hasOwnNestedProperty(headerData, 'data.full_name')) {
				return headerData['data']['full_name'];
			}
			return self.getHeaderUserName();
		},

		//Header User Name
		getHeaderUserName: function () {
			var self = this,
				headerData = self.customizerFeedData.headerData;
			if (self.hasOwnNestedProperty(headerData, 'username') && self.checkNotEmpty(headerData['username'])) {
				return headerData['username'];
			} else if (self.hasOwnNestedProperty(headerData, 'user.username')) {
				return headerData['user']['username'];
			} else if (self.hasOwnNestedProperty(headerData, 'data.username')) {
				return headerData['data']['username'];
			}
			return '';
		},

		//Header Media Count
		getHeaderMediaCount: function () {
			var self = this,
				headerData = self.customizerFeedData.headerData;
			if (self.hasOwnNestedProperty(headerData, 'data.counts.media')) {
				return headerData['data']['counts']['media'];
			} else if (self.hasOwnNestedProperty(headerData, 'counts.media')) {
				return headerData['counts']['media'];
			} else if (self.hasOwnNestedProperty(headerData, 'media_count')) {
				return headerData['media_count'];
			}
			return '';
		},

		//Header Followers Count
		getHeaderFollowersCount: function () {
			var self = this,
				headerData = self.customizerFeedData.headerData;
			if (self.hasOwnNestedProperty(headerData, 'data.counts.followed_by')) {
				return headerData['data']['counts']['followed_by'];
			} else if (self.hasOwnNestedProperty(headerData, 'counts.followed_by')) {
				return headerData['counts']['followed_by'];
			} else if (self.hasOwnNestedProperty(headerData, 'followers_count')) {
				return headerData['followers_count'];
			}
			return '';
		},

		//Header Avatar
		getHeaderAvatar: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				headerData = self.customizerFeedData.headerData,
				header = self.customizerFeedData.header;
			if (self.checkNotEmpty(customizerSettings['customavatar'])) {
				return customizerSettings['customavatar'];
			} else if (header['local_avatar_url'] != false && self.checkNotEmpty(header['local_avatar_url'])) {
				return header['local_avatar_url'];
			} else {
				if (self.hasOwnNestedProperty(headerData, 'profile_picture')) {
					return headerData['profile_picture'];
				} else if (self.hasOwnNestedProperty(headerData, 'profile_picture_url')) {
					return headerData['profile_picture_url'];
				} else if (self.hasOwnNestedProperty(headerData, 'user.profile_picture')) {
					return headerData['user']['profile_picture'];
				} else if (self.hasOwnNestedProperty(headerData, 'data.profile_picture')) {
					return headerData['data']['profile_picture'];
				}
			}
			return self.pluginUrl + 'img/thumb-placeholder.png';
		},

		//Header Bio
		getHeaderBio: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				headerData = self.customizerFeedData.headerData;

			if (self.checkNotEmpty(customizerSettings['custombio'])) {
				return customizerSettings['custombio'];
			} else if (self.hasOwnNestedProperty(headerData, 'data.bio')) {
				return headerData['data']['bio'];
			} else if (self.hasOwnNestedProperty(headerData, 'bio')) {
				return headerData['bio'];
			} else if (self.hasOwnNestedProperty(headerData, 'biography')) {
				return headerData['biography'];
			}
			return '';
		},


		//Header Text Class
		getTextHeaderClass: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				headerData = self.customizerFeedData.headerData,
				headerClass = 'sbi_header_text ',
				shouldShowBio = self.checkNotEmpty(self.getHeaderBio()) ? self.valueIsEnabled(customizerSettings['showbio']) : false,
				shouldShowInfo = shouldShowBio || self.valueIsEnabled(customizerSettings['showfollowers']);
			headerClass += !shouldShowBio ? 'sbi_no_bio ' : '',
				headerClass += !shouldShowInfo ? 'sbi_no_info' : '';

			return headerClass;
		},

		//Get Story Delays
		getStoryDelays: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;
			return self.checkNotEmpty(customizerSettings['storiestime']) ? Math.max(500, parseInt(customizerSettings['storiestime'])) : 5000;
		},

		//Get Story Data
		getStoryData: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings,
				headerData = self.customizerFeedData.headerData;
			if (self.hasOwnNestedProperty(headerData, 'stories') && headerData.stories.length > 0 && self.valueIsEnabled(customizerSettings['stories'])) {
				return headerData['stories'];
			}
			return false;
		},


		//Image Chooser
		imageChooser: function (settingID) {
			var self = this;
			var uploader = wp.media({
				frame: 'post',
				title: 'Media Uploader',
				button: { text: 'Choose Media' },
				library: { type: 'image' },
				multiple: false
			}).on('close', function () {
				var selection = uploader.state().get('selection');
				if (selection.length != 0) {
					attachment = selection.first().toJSON();
					self.customizerFeedData.settings[settingID] = attachment.url;
				}
			}).open();
		},

		//Change Switcher Settings
		changeSwitcherSettingValue: function (settingID, onValue, offValue, ajaxAction = false) {
			var self = this;
			self.customizerFeedData.settings[settingID] = self.customizerFeedData.settings[settingID] == onValue ? offValue : onValue;
			if (ajaxAction !== false) {
				self.customizerControlAjaxAction(ajaxAction);
			}

			if (settingID == 'disablelightbox' || settingID == 'shoppablefeed') {
				if (self.valueIsEnabled(self.customizerFeedData.settings['disablelightbox']) || self.valueIsEnabled(self.customizerFeedData.settings['shoppablefeed'])) {
					jQuery('body').find('.sbi_link').addClass('sbi_disable_lightbox');
				} else {
					jQuery('body').find('.sbi_link').removeClass('sbi_disable_lightbox');
				}
			}

			self.regenerateLayout(settingID);
		},

		//Checkbox List
		changeCheckboxListValue: function (settingID, value, ajaxAction = false) {
			var self = this,
				settingValue = self.customizerFeedData.settings[settingID].split(',');
			if (!Array.isArray(settingValue)) {
				settingValue = [settingValue];
			}
			if (settingValue.includes(value)) {
				settingValue.splice(settingValue.indexOf(value), 1);
			} else {
				settingValue.push(value);
			}
			self.customizerFeedData.settings[settingID] = settingValue.join(',');
		},


		//Section Checkbox
		changeCheckboxSectionValue: function (settingID, value, ajaxAction = false) {
			var self = this;
			var settingValue = self.customizerFeedData.settings[settingID];
			if (!Array.isArray(settingValue) && settingID == 'type') {
				settingValue = [settingValue];
			}
			if (settingValue.includes(value)) {
				settingValue.splice(settingValue.indexOf(value), 1);
			} else {
				settingValue.push(value);
			}
			if (settingID == 'type') {
				self.processFeedTypesSources(settingValue);
			}
			//settingValue = (settingValue.length == 1 && settingID == 'type') ? settingValue[0] : settingValue;
			self.customizerFeedData.settings[settingID] = settingValue;
			if (ajaxAction !== false) {
				self.customizerControlAjaxAction(ajaxAction);
			}
			event.stopPropagation()

		},
		checkboxSectionValueExists: function (settingID, value) {
			var self = this;
			var settingValue = self.customizerFeedData.settings[settingID];
			return settingValue.includes(value) ? true : false;
		},

		/**
		 * Check Control Condition
		 *
		 * @since 4.0
		*/
		checkControlCondition: function (conditionsArray = [], checkExtensionActive = false, checkExtensionActiveDimmed = false) {
			var self = this,
				isConditionTrue = 0;
			Object.keys(conditionsArray).map(function (condition, index) {
				if (conditionsArray[condition].indexOf(self.customizerFeedData.settings[condition]) !== -1)
					isConditionTrue += 1
			});
			var extensionCondition = checkExtensionActive != undefined && checkExtensionActive != false ? self.checkExtensionActive(checkExtensionActive) : true,
				extensionCondition = checkExtensionActiveDimmed != undefined && checkExtensionActiveDimmed != false && !self.checkExtensionActive(checkExtensionActiveDimmed) ? false : extensionCondition;

			return (isConditionTrue == Object.keys(conditionsArray).length) ? (extensionCondition) : false;
		},

		/**
		 * Check Color Override Condition
		 *
		 * @since 4.0
		*/
		checkControlOverrideColor: function (overrideConditionsArray = []) {
			var self = this,
				isConditionTrue = 0;
			overrideConditionsArray.map(function (condition, index) {
				if (self.checkNotEmpty(self.customizerFeedData.settings[condition]) && self.customizerFeedData.settings[condition].replace(/ /gi, '') != '#') {
					isConditionTrue += 1
				}
			});
			return (isConditionTrue >= 1) ? true : false;
		},

		/**
		 * Show Control
		 *
		 * @since 4.0
		*/
		isControlShown: function (control) {
			var self = this;
			if (control.checkViewDisabled != undefined) {
				return !self.viewsActive[control.checkViewDisabled];
			}
			if (control.checkView != undefined) {
				return !self.viewsActive[control.checkView];
			}

			if (control.checkExtension != undefined && control.checkExtension != false && !self.checkExtensionActive(control.checkExtension)) {
				return self.checkExtensionActive(control.checkExtension);
			}

			if (control.conditionDimmed != undefined && self.checkControlCondition(control.conditionDimmed))
				return self.checkControlCondition(control.conditionDimmed);
			if (control.overrideColorCondition != undefined) {
				return self.checkControlOverrideColor(control.overrideColorCondition);
			}

			return (control.conditionHide != undefined && control.condition != undefined || control.checkExtension != undefined)
				? self.checkControlCondition(control.condition, control.checkExtension)
				: true;
		},

		checkExtensionActive: function (extension) {
			var self = this;
			return self.activeExtensions[extension];
		},

		expandSourceInfo: function (sourceId) {
			var self = this;
			self.customizerScreens.sourceExpanded = (self.customizerScreens.sourceExpanded === sourceId) ? null : sourceId;
			window.event.stopPropagation()
		},

		resetColor: function (controlId) {
			this.customizerFeedData.settings[controlId] = '';
		},

		//Source Active Customizer
		isSourceActiveCustomizer: function (source) {
			var self = this;
			return (
				Array.isArray(self.customizerFeedData.settings.sources.map) ||
				self.customizerFeedData.settings.sources instanceof Object
			) &&
				self.customizerScreens.sourcesChoosed.map(s => s.account_id).includes(source.account_id);
			//self.customizerFeedData.settings.sources.map(s => s.account_id).includes(source.account_id);
		},
		//Choose Source From Customizer
		selectSourceCustomizer: function (source, isRemove = false) {
			var self = this,
				isMultifeed = (self.activeExtensions['multifeed'] !== undefined && self.activeExtensions['multifeed'] == true),
				sourcesListMap = Array.isArray(self.customizerFeedData.settings.sources) || self.customizerFeedData.settings.sources instanceof Object ? self.customizerFeedData.settings.sources.map(s => s.account_id) : [];
			if (isMultifeed) {
				if (self.customizerScreens.sourcesChoosed.map(s => s.account_id).includes(source.account_id)) {
					var indexToRemove = self.customizerScreens.sourcesChoosed.findIndex(src => src.account_id === source.account_id);
					self.customizerScreens.sourcesChoosed.splice(indexToRemove, 1);
					if (isRemove) {
						self.customizerFeedData.settings.sources.splice(indexToRemove, 1);
					}
				} else {
					self.customizerScreens.sourcesChoosed.push(source);
				}
			} else {
				self.customizerScreens.sourcesChoosed = (sourcesListMap.includes(source)) ? [] : [source];
			}
			sbiBuilder.$forceUpdate();
		},
		closeSourceCustomizer: function () {
			var self = this;
			self.viewsActive['sourcePopup'] = false;
			//self.customizerFeedData.settings.sources = self.customizerScreens.sourcesChoosed;
			sbiBuilder.$forceUpdate();
		},
		customizerFeedTypePrint: function () {
			var self = this,
				combinedTypes = self.feedTypes.concat(self.advancedFeedTypes);
			result = combinedTypes.filter(function (tp) {
				return tp.type === self.customizerFeedData.settings.feedtype
			});
			self.customizerScreens.printedType = result.length > 0 ? result[0] : [];
			return result.length > 0 ? true : false;
		},
		choosedFeedTypeCustomizer: function (feedType) {
			var self = this, result = false;
			if (
				(self.viewsActive.feedTypeElement === null && self.customizerFeedData.settings.feedtype === feedType) ||
				(self.viewsActive.feedTypeElement !== null && self.viewsActive.feedTypeElement == feedType)
			) {
				result = true;
			}
			return result;
		},
		updateFeedTypeCustomizer: function () {
			var self = this;
			if (self.viewsActive.feedTypeElement === 'socialwall') {
				window.location.href = sbi_builder.pluginsInfo.social_wall.settingsPage;
				return;
			}
			self.setType(self.viewsActive.feedTypeElement);

			self.customizerFeedData.settings.feedtype = self.viewsActive.feedTypeElement;
			self.viewsActive.feedTypeElement = null;
			self.viewsActive.feedtypesPopup = false;
			self.customizerControlAjaxAction('feedFlyPreview');
			sbiBuilder.$forceUpdate();
		},
		updateInputWidth: function () {
			this.customizerScreens.inputNameWidth = ((document.getElementById("sbi-csz-hd-input").value.length + 6) * 8) + 'px';
		},

		feedPreviewMaker: function () {
			var self = this;
			return self.template;
			//return self.template == null ? null : "<div>" + self.template + "</div>";
		},

		customizerStyleMaker: function () {
			var self = this;
			if (self.customizerSidebarBuilder) {
				self.feedStyle = '';
				Object.values(self.customizerSidebarBuilder).map(function (tab) {
					self.customizerSectionStyle(tab.sections);
				});
				return '<style type="text/css">' + self.feedStyle + '</style>';
			}
			return false;
		},

		escapeHTML: function (text) {
			return text.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		},

		/**
		 * Get Feed Preview Global CSS Class
		 *
		 * @since 4.0
		 * @return String
		 */
		getPaletteClass: function (context = '') {
			var self = this,
				colorPalette = self.customizerFeedData.settings.colorpalette;

			if (self.checkNotEmpty(colorPalette)) {
				var feedID = colorPalette === 'custom' ? ('_' + self.customizerFeedData.feed_info.id) : '';
				return colorPalette !== 'inherit' ? ' sbi' + context + '_palette_' + colorPalette + feedID : '';
			}
			return '';
		},

		customizerSectionStyle: function (sections) {
			var self = this;
			Object.values(sections).map(function (section) {
				if (section.controls) {
					Object.values(section.controls).map(function (control) {
						self.returnControlStyle(control);
					});
				}
				if (section.nested_sections) {
					self.customizerSectionStyle(section.nested_sections);
					Object.values(section.nested_sections).map(function (nestedSections) {
						Object.values(nestedSections.controls).map(function (nestedControl) {
							if (nestedControl.section) {
								self.customizerSectionStyle(nestedControl);
							}
						});
					});
				}
			});
		},
		returnControlStyle: function (control) {
			var self = this;
			if (control.style) {
				Object.entries(control.style).map(function (css) {
					var condition = control.condition != undefined || control.checkExtension != undefined ? self.checkControlCondition(control.condition, control.checkExtension) : true;
					if (condition) {
						self.feedStyle +=
							css[0] + '{' +
							css[1].replace("{{value}}", self.customizerFeedData.settings[control.id]) +
							'}';
					}
				});
			}
		},




		/**
		 * Customizer Control Ajax
		 * Some of the customizer controls need to perform Ajax
		 * Calls in order to update the preview
		 *
		 * @since 6.0
		 */
		customizerControlAjaxAction: function (actionType, settingID = false) {
			var self = this;
			switch (actionType) {
				case 'feedFlyPreview':
					self.loadingBar = true;
					self.templateRender = false;
					var previewFeedData = {
						action: 'sbi_feed_saver_manager_fly_preview',
						feedID: self.customizerFeedData.feed_info.id,
						previewSettings: self.customizerFeedData.settings,
						feedName: self.customizerFeedData.feed_info.feed_name,
					};
					if (self.getModerationShoppableMode) {
						previewFeedData['moderationShoppableMode'] = true;
						previewFeedData['offset'] = self.moderationShoppableModeOffset;
					}


					self.ajaxPost(previewFeedData, function (_ref) {
						var data = _ref.data;
						if (data !== false) {
							self.updatedTimeStamp = new Date().getTime();
							self.template = String("<div>" + data + "</div>");
							self.moderationShoppableModeAjaxDone = self.getModerationShoppableMode ? true : false;
							self.processNotification("previewUpdated");
						} else {
							self.processNotification("unkownError");
						}
						jQuery('body').find('#sbi_load .sbi_load_btn').unbind('click')
					});
					break;
				case 'feedPreviewRender':
					setTimeout(function () {
					}, 150);
					break;
			}
		},


		/**
		 * Ajax Action : Save Feed Settings
		 *
		 * @since 4.0
		 */
		saveFeedSettings: function (leavePage = false) {
			var self = this,
				sources = [],
				updateFeedData = {
					action: 'sbi_feed_saver_manager_builder_update',
					update_feed: 'true',
					feed_id: self.customizerFeedData.feed_info.id,
					feed_name: self.customizerFeedData.feed_info.feed_name,
					settings: self.customizerFeedData.settings,
					sources: self.getFeedIdSourcesSaver(),
					tagged: self.getFeedIdSourcesTaggedSaver(),
					hashtag: self.getFeedHashtagsSaver(),
					type: self.getFeedTypeSaver(),
					shoppablelist: self.customizerFeedData.settings.shoppablelist,
					moderationlist: self.customizerFeedData.settings.moderationlist
				};
			self.loadingBar = true;
			self.ajaxPost(updateFeedData, function (_ref) {
				var data = _ref.data;
				if (data && data.success === true) {
					self.processNotification('feedSaved');
					self.customizerFeedDataInitial = self.customizerFeedData;
					if (leavePage === true) {
						setTimeout(function () {
							window.location.href = self.builderUrl;
						}, 1500)
					}
				} else {
					self.processNotification('feedSavedError');
				}
			});
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Ajax Action : Clear Single Feed Cache
		 * Update Feed Preview Too
		 * @since 4.0
		 */
		clearSingleFeedCache: function () {
			var self = this,
				sources = [],
				clearFeedData = {
					action: 'sbi_feed_saver_manager_clear_single_feed_cache',
					feedID: self.customizerFeedData.feed_info.id,
					previewSettings: self.customizerFeedData.settings,
					feedName: self.customizerFeedData.feed_info.feed_name,
				};
			self.loadingBar = true;
			self.ajaxPost(clearFeedData, function (_ref) {
				var data = _ref.data;
				if (data !== false) {

					self.processNotification('cacheCleared');
				} else {
					self.processNotification("unkownError");
				}
			})
			sbiBuilder.$forceUpdate();
		},

		/**
		 * Clear & Reset Color Override
		 *
		 * @since 4.0
		*/
		resetColorOverride: function (settingID) {
			this.customizerFeedData.settings[settingID] = '';
		},

		/**
		 * Moderation & Shoppable Mode Pagination
		 *
		 * @since 4.0
		*/
		moderationModePagination: function (type) {
			var self = this;
			if (type == 'next') {
				self.moderationShoppableModeOffset = self.moderationShoppableModeOffset + 1;
			}
			if (type == 'previous') {
				self.moderationShoppableModeOffset = self.moderationShoppableModeOffset > 0 ? (self.moderationShoppableModeOffset - 1) : 0;
			}

			self.customizerControlAjaxAction('feedFlyPreview');
		},


		/**
		 * Remove Source Form List Multifeed
		 *
		 * @since 4.0
		 */
		removeSourceCustomizer: function (type, args = []) {
			var self = this;
			Object.assign(self.customizerScreens.sourcesChoosed, self.customizerFeedData.settings.sources);
			self.selectSourceCustomizer(args, true);
			sbiBuilder.$forceUpdate();
			window.event.stopPropagation();
		},

		/**
		 * Custom Flied CLick
		 * Action
		 * @since 6.0
		 */
		fieldCustomClickAction: function (clickAction) {
			var self = this;
			switch (clickAction) {
				case 'clearCommentCache':
					self.clearCommentCache();
					break;
			}
		},

		/**
		 * Clear Comment Cache
		 * Action
		 * @since 6.0
		 */
		clearCommentCache: function () {
			var self = this;
			self.loadingBar = true;
			var clearCommentCacheData = {
				action: 'sbi_feed_saver_manager_clear_comments_cache',
			};
			self.ajaxPost(clearCommentCacheData, function (_ref) {
				var data = _ref.data;
				if (data === 'success') {
					self.processNotification("commentCacheCleared");
				} else {
					self.processNotification("unkownError");
				}
			});
		},


		/**
		 * Open Dialog Box
		 *
		 * @since 4.0
		 */
		openDialogBox: function (type, args = []) {
			var self = this,
				heading = self.dialogBoxPopupScreen[type].heading,
				description = self.dialogBoxPopupScreen[type].description,
				customButtons = self.dialogBoxPopupScreen[type].customButtons;
			switch (type) {
				case "deleteSourceCustomizer":
					self.sourceToDelete = args;
					heading = heading.replace("#", self.sourceToDelete.username);
					break;
				case "deleteSingleFeed":
					self.feedToDelete = args;
					heading = heading.replace("#", self.feedToDelete.feed_name);
					break;
			}
			self.dialogBox = {
				active: true,
				type: type,
				heading: heading,
				description: description,
				customButtons: customButtons
			};
			window.event.stopPropagation();
		},

		/**
		 * Confirm Dialog Box Actions
		 *
		 * @since 4.0
		 */
		confirmDialogAction: function () {
			var self = this;
			switch (self.dialogBox.type) {
				case 'deleteSourceCustomizer':
					self.selectSourceCustomizer(self.sourceToDelete, true);
					self.customizerControlAjaxAction('feedFlyPreview');
					break;
				case 'deleteSingleFeed':
					self.feedActionDelete([self.feedToDelete.id]);
					break;
				case 'deleteMultipleFeeds':
					self.feedActionDelete(self.feedsSelected);
					break;
				case 'backAllToFeed':
					//Save & Exist;
					self.saveFeedSettings(true);
					break;
				case 'unsavedFeedSources':
					self.updateFeedTypeAndSourcesCustomizer();
					break;
			}
		},

		/*
		closeConfirmDialog : function(){
			this.sourceToDelete = {};
			this.feedToDelete = {};
			this.dialogBox = {
				active : false,
				type : null,
				heading : null,
				description : null
			};
		},
		*/

		/**
		 * Show Tooltip on Hover
		 *
		 * @since 4.0
		 */
		toggleElementTooltip: function (tooltipText, type, align = 'center') {
			var self = this,
				target = window.event.currentTarget,
				tooltip = (target != undefined && target != null) ? document.querySelector('.sb-control-elem-tltp-content') : null;
			if (tooltip != null && type == 'show') {
				self.tooltip.text = tooltipText;
				var position = target.getBoundingClientRect(),
					left = position.left + 10,
					top = position.top - 10;
				tooltip.style.left = left + 'px';
				tooltip.style.top = top + 'px';
				tooltip.style.textAlign = align;
				self.tooltip.hover = true;
			}
			if (type == 'hide') {
				setTimeout(function () {
					if (self.tooltip.hoverType != 'inside') {
						self.tooltip.hover = false;
					}
				}, 200)
			}
		},

		/**
		 * Hover Tooltip
		 *
		 * @since 4.0
		 */
		hoverTooltip: function (type, hoverType) {
			this.tooltip.hover = type;
			this.tooltip.hoverType = hoverType;
		},

		/**
		 * Loading Bar & Notification
		 *
		 * @since 4.0
		 */
		processNotification: function (notificationType) {
			var self = this,
				notification = self.genericText.notification[notificationType];
			self.loadingBar = false;
			self.notificationElement = {
				type: notification.type,
				text: notification.text,
				shown: "shown"
			};
			setTimeout(function () {
				self.notificationElement.shown = "hidden";
			}, 5000);
		},

		/**
		 * Return Account Avatar
		 *
		 * @since 4.0
		 *
		 * @return string
		 */
		returnAccountAvatar: function (source) {
			if (typeof source.local_avatar_url !== "undefined" && source.local_avatar_url !== '') {
				return source.local_avatar_url;
			}
			if (typeof source.avatar_url !== "undefined" && source.avatar_url !== '') {
				return source.avatar_url;
			}

			return false;
		},




		/**
		 * Returns the caption for posts
		 *
		 * @since 6.0
		 *
		 * @return string
		 */
		getPostCaption: function (caption, postID) {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;
			caption = caption.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/&lt;br&gt;|&lt;br \/&gt;/g, '<br>');
			if (self.checkNotEmpty(customizerSettings.captionlength)) {
				return '<span class="sbi_caption">' +
					(self.expandedCaptions.includes(postID) ? caption : caption.substring(0, parseInt(customizerSettings.captionlength))) +
					'</span>' +
					(caption.length > parseInt(customizerSettings.captionlength) ? '<span class="sbi_expand" style="display:inline-block;" onclick="sbiBuilderToggleCaption(' + postID + ')"> <a><span class="sbi_more">...</span></a></span>' : '');
			}
			var captionLength = !self.checkNotEmpty(customizerSettings.captionlength) ? 50 : parseInt(customizerSettings.captionlength);
			return '<span class="sbi_caption">' + caption.substring(0, captionLength) + '</span>';
		},

		/**
		 * Check if Post Is a Shoppable Post
		 *
		 * @since 6.0
		 *
		 * @return boolean
		 */
		checkPostShoppableFeed: function (postId) {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;
			return typeof self.customizerFeedData.settings.shoppablelist === 'object' && customizerSettings.shoppablelist[postId] !== undefined;
		},

		/**
		 * Open Shoppable Control for adding new Post
		 *
		 * @since 6.0
		 */
		openPostShoppableFeed: function (postId, media, caption = '') {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;
			self.shoppableFeed.postId = postId;
			self.shoppableFeed.postShoppableUrl = (typeof self.customizerFeedData.settings.shoppablelist === 'object' && customizerSettings.shoppablelist[postId] !== undefined) ? customizerSettings.shoppablelist[postId] : '';
			self.shoppableFeed.postMedia = media;
			self.shoppableFeed.postCaption = caption;

		},

		/**
		 * Save Post Shoppable Feed
		 *
		 * @since 6.0
		 */
		addPostShoppableFeed: function () {
			var self = this,
				customizerSettings = self.customizerFeedData.settings;
			if (self.checkNotEmpty(self.shoppableFeed.postShoppableUrl)) {
				self.customizerFeedData.settings.shoppablelist = (typeof self.customizerFeedData.settings.shoppablelist === 'object') ? self.customizerFeedData.settings.shoppablelist : {};
				self.customizerFeedData.settings.shoppablelist[self.shoppableFeed.postId] = self.shoppableFeed.postShoppableUrl;
				self.shoppableFeed = {
					postId: null,
					postMedia: null,
					postCaption: null,
					postShoppableUrl: ''
				};
			} else {
				delete self.customizerFeedData.settings.shoppablelist[self.shoppableFeed.postId];
			}
		},

		/**
		 * Cancel Post Shoppable Feed
		 *
		 * @since 6.0
		 */
		cancelPostShoppableFeed: function () {
			var self = this;
			if (!self.checkNotEmpty(self.shoppableFeed.postShoppableUrl)) {
				delete self.customizerFeedData.settings.shoppablelist[self.shoppableFeed.postId];
			}

			self.shoppableFeed = {
				postId: null,
				postMedia: null,
				postCaption: null,
				postShoppableUrl: ''
			};
		},

		/**
		 * Open Moderation Mode
		 *
		 * @since 6.0
		 */
		openModerationMode: function () {
			var self = this;
			Object.assign(self.moderationSettings, self.customizerFeedData.settings.moderationlist);
			self.customBlockModerationlistTemp = `${self.customizerFeedData.settings.customBlockModerationlist}`;
			self.activateView('moderationMode');
		},

		/**
		 * Switch Moderation List Type
		 *
		 * @since 6.0
		 */
		switchModerationListType: function (moderationlistType) {
			var self = this;
			self.moderationSettings.list_type_selected = moderationlistType;
		},

		/**
		 * Switch Moderation List Type
		 *
		 * @since 6.0
		 */
		saveModerationSettings: function () {
			var self = this;
			Object.assign(self.customizerFeedData.settings.moderationlist, self.moderationSettings);
			self.customizerFeedData.settings.customBlockModerationlist = `${self.customBlockModerationlistTemp}`;
			self.activateView('moderationMode');
		},

		/**
		 * Check Post in Moderation Mode
		 *
		 * @since 6.0
		 */
		checkPostModertationMode: function (postID) {
			var self = this;
			if (self.moderationSettings.list_type_selected == "allow") {
				if (self.moderationSettings.allow_list.includes(postID) || self.moderationSettings.allow_list.includes(postID.toString())) {
					return 'active';
				} else {
					return 'inactive';
				}
			}
			if (self.moderationSettings.list_type_selected == "block") {
				var combinedBlockedList = Array.from(self.moderationSettings.block_list.concat(self.customBlockModerationlistTemp.split(',')));
				if (combinedBlockedList.includes(postID) || combinedBlockedList.includes(postID.toString())) {
					return 'inactive';
				} else {
					return 'active';
				}
			}
		},

		checkPostModertationModeAttribute: function (postID) {
			return '';
		},





		/**
		 * Add Post To Moderation List
		 * Depending on
		 *
		 * @since 6.0
		 */
		addPostToModerationList: function (postID) {
			var self = this;
			if (self.moderationSettings.list_type_selected == "allow") {
				if (self.moderationSettings.allow_list.includes(postID)) {
					self.moderationSettings.allow_list.push(postID);
					self.moderationSettings.allow_list.splice(self.moderationSettings.allow_list.indexOf(postID), 1);
					self.moderationSettings.allow_list.splice(self.moderationSettings.allow_list.indexOf(postID.toString()), 1);
				} else {
					self.moderationSettings.allow_list.push(postID);
				}
			}

			if (self.moderationSettings.list_type_selected == "block") {
				if (self.moderationSettings.block_list.includes(postID)) {
					self.moderationSettings.block_list.push(postID);
					self.moderationSettings.block_list.splice(self.moderationSettings.block_list.indexOf(postID), 1);
					self.moderationSettings.block_list.splice(self.moderationSettings.block_list.indexOf(postID.toString()), 1);
				} else {
					self.moderationSettings.block_list.push(postID);
				}
			}

		},


		/**
		 * Choose Hashtag Order By
		 *
		 * @since 6.0
		 */
		selectedHastagOrderBy: function (orderBy) {
			if (this.customizerFeedData != undefined) {
				this.customizerFeedData.settings.order = orderBy;
			} else {
				this.hashtagOrderBy = orderBy;
			}
		},

		ctaToggleFeatures: function () {
			this.freeCtaShowFeatures = !this.freeCtaShowFeatures;
		},

		/**
		 * Check Personal Account Info
		 *
		 * @since 6.0.8
		*/
		checkPeronalAccount: function () {
			let self = this;
			if (self.selectedSources.length > 0) {
				let sourceInfo = self.sourcesList.filter(function (source) {
					return source.account_id == self.selectedSources[0];
				});
				sourceInfo = sourceInfo[0] ? sourceInfo[0] : [];
				if (sourceInfo?.header_data?.account_type &&
					sourceInfo?.header_data?.account_type.toLowerCase() === 'personal' &&
					self.checkSinglePersonalData(sourceInfo?.header_data?.biography) &&
					self.checkSinglePersonalData(sourceInfo?.local_avatar)
				) {
					self.$refs.personalAccountRef.personalAccountInfo.id = sourceInfo.account_id;
					self.$refs.personalAccountRef.personalAccountInfo.username = sourceInfo.username;
					return false
				}
			}
			return true;
		},

		checkSinglePersonalData: function (data) {
			return data === false || data === undefined || (data !== undefined && data !== false && !this.checkNotEmpty(data));
		},

		/**
		 * Cancel Personal Account
		 *
		 * @since 6.0.8
		*/
		cancelPersonalAccountUpdate: function () {
			let self = this;
			self.submitNewFeed();
		},

		/**
		 * Triggered When updating Personal Account info
		 *
		 * @since 6.0.8
		*/
		successPersonalAccountUpdate: function () {
			let self = this;
			self.processNotification('personalAccountUpdated');
			this.creationProcessNext();
		}


	}

});

function sbiBuilderToggleCaption(postID) {
	if (sbiBuilder.expandedCaptions.includes(postID)) {
		sbiBuilder.expandedCaptions.splice(sbiBuilder.expandedCaptions.indexOf(postID), 1);
	} else {
		sbiBuilder.expandedCaptions.push(postID);
	}
}

jQuery(document).ready(function () {
	jQuery('body').find('#sbi_load .sbi_load_btn').unbind('click')
})
