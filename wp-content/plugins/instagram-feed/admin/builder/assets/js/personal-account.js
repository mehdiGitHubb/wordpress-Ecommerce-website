/**
 * Personal Account Popup
 *
 * @since 6.1
 */
Vue.component('sb-personal-account-component', {
    name: 'sb-personal-account-component',
    template: '#sb-personal-account-component',
    props: [
    	'genericText',
    	'svgIcons',
    	'parentType',
    	'parent'
    ],
    data: function() {
        return{
        	personalAccountModalText : sbi_personal_account.personalAccountScreen,
        	nonce : sbi_personal_account.nonce,
        	ajaxHandler : sbi_personal_account.ajaxHandler,
        	step : 1,
        	personalAccountPopup : false,
        	personalAccountInfo : {
        		id 		: null,
        		username : null,
        		avatar 	: null,
        		bio 	: '',
        		fileName : ''
        	},
            loading : false
        }
    },
    mounted : function(){
    },
    computed : {

    },
    methods : {

    	/*
		 * Click Cancel Button
		 *
    	*/
    	cancelMaybeLater : function(){
    		let self = this;
    		self.personalAccountPopup = false;
            self.$parent.cancelPersonalAccountUpdate();
    	},


    	/*
		 * Open File Chooser
		 * When clicking the upload button
		 *
    	*/
    	openFileChooser : function(){
            document.getElementById("avatar_file").click();
    	},


    	/*
		 * On File chooser change
		 * Update the file name
		 *
    	*/
    	onFileChooserChange : function(){
    		let self = this;
    		self.personalAccountInfo.fileName = self.$refs.file.files[0].name;
    	},


    	/*
		 * Update Personal account info
		 * send ajax info
		 *
    	*/
		addUpdatePersonalSourceInfo: function(){
    		let self = this,
            	formData = new FormData();
            formData.append( 'action', 'sbi_update_personal_account' );
            formData.append( 'id', 		self.personalAccountInfo.id );
            formData.append( 'bio', 	self.personalAccountInfo.bio );
            formData.append( 'username', 	self.personalAccountInfo.username );

            formData.append( 'nonce',   self.nonce );
            if( self.$refs.file.files[0] ){
            	formData.append( 'avatar', 	self.$refs.file.files[0] );
            }
            self.loading = true;

            fetch(self.ajaxHandler, {
                method: "POST",
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if( data.success ) {
                    self.$parent.sourcesList = data.sourcesList;
                    self.personalAccountPopup = false;
                    self.$parent.successPersonalAccountUpdate();
                }
                self.loading = false;
            });


		},

    }
});