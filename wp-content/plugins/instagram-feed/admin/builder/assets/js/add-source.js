var sbiStorage = window.localStorage;
/**
 * Add Source Popup
 *
 * @since 4.0
 */
 Vue.component('sb-add-source-component', {
    name: 'sb-add-source-component',
    template: '#sb-add-source-component',
    props: [
    'genericText',
    'links',
    'svgIcons',
    'viewsActive',
    'selectSourceScreen',
    'selectedFeed',
    'parent'
    ],
    data: function() {
        return{
            sourcesList : sbi_source.sources,
            nonce : sbi_source.nonce,

          //Add New Source
            newSourceData        : sbi_source.newSourceData ? sbi_source.newSourceData : null,
            sourceConnectionURLs : sbi_source.sourceConnectionURLs,
            manualSourcePopupInit : sbi_source.manualSourcePopupInit,
            returnedApiSourcesList : [],
            addNewSource : {
                typeSelected        : 'personal',
                manualSourceID      : null,
                manualSourceToken   : null
            },
            selectedSourcesToConnect : [],
            loadingAjax : false
        }
    },
    computed : {

    },
    mounted : function(){
      var self = this;
      if(self.newSourceData != null){
        self.initAddSourceData();
      }
      if( self.manualSourcePopupInit != undefined && self.manualSourcePopupInit == true){
        self.viewsActive.sourcePopupScreen = 'step_3';
        self.viewsActive.sourcePopup = true;
      }
      self.processIFConnectSuccess();
    },
    methods : {
        /**
         * Return Page/Group Avatar
         *
         * @since 4.0
         *
         * @return string
         */
         returnAccountAvatar : function(source){
             if (typeof source.avatar !== "undefined" && source.avatar !== '') {
                 return source.avatar;
             } else if (typeof this.newSourceData !== 'undefined'
                 && typeof this.newSourceData.matchingExistingAccounts !== 'undefined'
                 && typeof this.newSourceData.matchingExistingAccounts.avatar !== 'undefined') {
                 return this.newSourceData.matchingExistingAccounts.avatar;
             }

            return false;
        },


        /**
         * Add Feed Source Manually
         *
         * @since 4.0
         */
        addSourceManually: function(isEventSource = false){
            var self = this,
            manualSourceData = {
                'action' : 'sbi_source_builder_update',
                'type' : self.addNewSource.typeSelected,
                'id' : self.addNewSource.manualSourceID,
                'access_token' : self.addNewSource.manualSourceToken,
              'nonce' : self.nonce

            };
            if(isEventSource){
                manualSourceData.privilege = 'events';
            }
            var alerts = document.querySelectorAll(".sb-alerts-wrap");
            if (alerts.length) {
                alerts[0].parentNode.removeChild(alerts[0]);
            }

            if(self.$parent.checkNotEmpty(self.addNewSource.manualSourceID) && self.$parent.checkNotEmpty(self.addNewSource.manualSourceToken) ){
                self.loadingAjax = true;
                self.$parent.ajaxPost(manualSourceData, function(_ref){
                    var data = _ref.data;
                    if (typeof data.success !== 'undefined' && data.success === false) {
                        //sbi-if-source-inputs sbi-if-fs
                        var inputs = document.querySelectorAll(".sbi-fb-source-inputs")[0];
                        var div = document.createElement('div');
                        div.innerHTML = data.data.message;

                      while (div.children.length > 0) {
                            inputs.appendChild(div.children[0]);
                        }

                    } else {
                        self.addNewSource = {typeSelected : 'personal', manualSourceID : null,manualSourceToken : null};
                        self.sourcesList = data.data;
                        self.$parent.sourcesList = data.data;
                        self.$parent.viewsActive.sourcePopup = false;
                        if(self.$parent.customizerFeedData){
                            self.$parent.activateView('sourcePopup', 'customizer');
                        }
                    }
                    self.loadingAjax = false;

                });
            }else{
                alert("Token or ID Empty")
            }
        },

      //Check if source are Array
      createSourcesArray : function( element ){
            var self = this;
            if(Array.isArray(element) && element.length == 1 && !self.$parent.checkNotEmpty(element[0]) ){
                return [];
            }
            var arrayResult = Array.isArray(element) ? Array.from(element) : Array.from( element.split(',') );
            return arrayResult.filter(function (el) {
              return el != null && self.$parent.checkNotEmpty(el);
            });
      },

        /**
         * Make sure something entered for manual connections
         *
         * @since 4.0
         */
        checkManualEmpty : function() {
            var self = this;
            return self.$parent.checkNotEmpty(self.addNewSource.manualSourceID) && self.$parent.checkNotEmpty(self.addNewSource.manualSourceToken);
        },

        /**
         * Init Add Source Action
         * Triggered when the connect button is returned
         *
         * @since 4.0
         */
         initAddSourceData : function(){
            var self = this;
            // If a quick update or insert was done, skip step 2
            if ( self.newSourceData.didQuickUpdate) {
                if(self.newSourceData.type !== 'business'){
                    if(self.$parent.customizerFeedData){
                        if(sbiStorage.feedTypeOnSourcePopup != undefined){
                           self.$parent.feedTypeOnSourcePopup = sbiStorage.feedTypeOnSourcePopup;
                           if( self.$parent.feedTypeOnSourcePopup == 'tagged' ){
                                self.$parent.selectedSourcesPopup = self.createSourcesArray(self.$parent.selectedSourcesTagged);
                                self.$parent.selectedSourcesTaggedPopup = self.createSourcesArray(self.$parent.selectedSourcesTagged);
                            }else if( self.$parent.feedTypeOnSourcePopup == 'user' ){
                                self.$parent.selectedSourcesPopup = self.createSourcesArray(self.$parent.selectedSourcesUser);
                                self.$parent.selectedSourcesUserPopup = self.createSourcesArray(self.$parent.selectedSourcesUser);
                            }
                            self.$parent.viewsActive.sourcesListPopup = true;
                        }
                    }
                }
                return;
            }
            self.$parent.viewsActive.sourcePopup = true;
            self.$parent.viewsActive.sourcePopupScreen = 'step_2';
            if(self.newSourceData && !self.newSourceData.error){
                if (self.newSourceData.type === 'business') {
                   self.newSourceData.unconnectedAccounts.forEach(function(singleSource){
                       self.returnedApiSourcesList.push(self.createSourceObject('business',singleSource));
                   });
                } else {
                    self.newSourceData.unconnectedAccounts.forEach(function(singleSource){
                        self.returnedApiSourcesList.push(self.createSourceObject('personal',singleSource));
                    });
                   self.$parent.viewsActive.sourcePopupScreen = 'step_4';
                }
            }
        },

        /**
         * Create Single Source Object
         *
         * @since 4.0
         *
         * @return Object
         */
         createSourceObject : function(type,object){
            return {
                id : object.id,
                account_id : object.id,
                access_token : object.access_token,
                account_type : type,
                type : type,
                avatar : object.avatar,
                info : JSON.stringify(object),
                username : object.username
            }
        },

        /**
         * Select Page/Group to Connect
         *
         * @since 4.0
         */
         selectSourcesToConnect : function(source){
            var self = this;

            if (typeof window.sbiSelected === 'undefined') {
                window.sbiSelected = [];
            }
            if(self.selectedSourcesToConnect.includes(source.account_id)){
                self.selectedSourcesToConnect.splice(self.selectedSourcesToConnect.indexOf(source.account_id), 1);
                window.sbiSelected.splice(self.selectedSourcesToConnect.indexOf(source.admin), 1);
            }else{
                self.selectedSourcesToConnect.push(source.account_id);
                window.sbiSelected.push(source.admin);
            }
        },

        /**
         * Select Page/Group to Connect
         *
         * @since 4.0
         */
         addSourcesOnConnect : function(){
            var self = this,
                isSingleSource = self.returnedApiSourcesList.length === 1;
            if(self.selectedSourcesToConnect.length > 0 || isSingleSource){
                var sourcesListToAdd = [];
                if (self.selectedSourcesToConnect.length > 0) {
                    self.selectedSourcesToConnect.forEach(function(accountID, index){
                        self.returnedApiSourcesList.forEach(function(source){
                            if(source.account_id === accountID) {
                                sourcesListToAdd.push(source);
                            }
                        });
                    });
                } else {
                    self.returnedApiSourcesList.forEach(function(source){
                        sourcesListToAdd.push(source);
                    });
                }

                var connectSourceData = {
                    'action' : 'sbi_source_builder_update_multiple',
                    'type' : self.addNewSource.typeSelected,
                    'sourcesList' : sourcesListToAdd,
                    'nonce' : self.nonce
                };
                self.$parent.ajaxPost(connectSourceData, function(_ref){
                    var data = _ref.data;
                    self.sourcesList = data;
                    self.$parent.sourcesList = data;
                    self.$parent.viewsActive.sourcePopup = false;
                    self.$parent.viewsActive.sourcesListPopup = false;
                    if(self.$parent.customizerFeedData){
                        //self.$parent.activateView('sourcePopup', 'customizer');
                        self.$parent.viewsActive.sourcesListPopup = true;
                    }
                });
            }
        },

        /**
         * Process Connect IF Button
         *
         * @since 4.0
         */
         processIFConnect : function(){
            var self = this,
            ifConnectURL = self.addNewSource.typeSelected === 'personal' ? self.sourceConnectionURLs.personal : self.sourceConnectionURLs.business,
            screenType = (self.$parent.customizerFeedData != undefined) ? 'customizer'  : 'creationProcess',
            appendURL = ( screenType == 'customizer' ) ? self.sourceConnectionURLs.stateURL + ',feed_id='+ self.$parent.customizerFeedData.feed_info.id : self.sourceConnectionURLs.stateURL;
            //if(screenType != 'customizer'){
                self.createLocalStorage(screenType);
            //}
            var finalUrl = ifConnectURL + "{'{url=" + appendURL + "}'}";
            window.location = finalUrl;

        },

        /**
         * Browser Local Storage for IF Connect
         *
         * @since 4.0
         */
         createLocalStorage : function(screenType){
            var self = this;
            switch (screenType) {
                case 'creationProcess':
                    sbiStorage.setItem('selectedFeed', self.$parent.selectedFeed);
                    sbiStorage.setItem('feedTypeOnSourcePopup', self.$parent.feedTypeOnSourcePopup);
                break;
                case 'customizer':
                    sbiStorage.setItem('selectedFeed', self.$parent.selectedFeedPopup);
                    sbiStorage.setItem('feedTypeOnSourcePopup', self.$parent.feedTypeOnSourcePopup);
                    sbiStorage.setItem( 'feed_id', self.$parent.customizerFeedData.feed_info.id );
                break;
            }
            sbiStorage.setItem( 'IFConnect', 'true' );
            sbiStorage.setItem( 'screenType', screenType );
        },


        /**
         * Process IF Connect Success
         *
         * @since 4.0
         */
         processIFConnectSuccess : function(){
            var self = this;
            if( sbiStorage.IFConnect === 'true' && sbiStorage.screenType ){
               if( sbiStorage.screenType == 'creationProcess' && sbiStorage.selectedFeed ){
                   self.$parent.selectedFeed = self.createSourcesArray(sbiStorage.selectedFeed);
                   self.$parent.feedTypeOnSourcePopup = sbiStorage.feedTypeOnSourcePopup;
                   self.$parent.viewsActive.pageScreen = 'selectFeed';
                   self.$parent.viewsActive.selectedFeedSection = 'selectSource';
                   self.$parent.viewsActive.sourcesListPopup = true;
               }
               if( sbiStorage.screenType == 'customizer' && sbiStorage.feed_id){
                   var urlParams = new URLSearchParams(window.location.search);
                   urlParams.set('feed_id', sbiStorage.feed_id);
                   window.location.search = urlParams;
               }
            }
            localStorage.removeItem("IFConnect");
            localStorage.removeItem("screenType");
            localStorage.removeItem("selectedFeed");
            localStorage.removeItem("feedTypeOnSourcePopup");
            localStorage.removeItem("feed_id");
        },

        groupNext : function() {
        },

        checkDisclaimer : function(){
            return typeof window.sbiSelectedFeed !== 'undefined' && window.sbiSelectedFeed.length === 1 && window.sbiSelectedFeed[0] !== 'user';
        },

        printDisclaimer : function(){
            return (typeof window.sbiSelectedFeed !== 'undefined' && window.sbiSelectedFeed.length === 1 && window.sbiSelectedFeed[0] === 'tagged') ? this.selectSourceScreen.modal.disclaimerMentions : this.selectSourceScreen.modal.disclaimerHashtag;
        },


    }
});
