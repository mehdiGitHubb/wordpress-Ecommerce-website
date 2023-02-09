<script type="text/x-template" id="sb-personal-account-component">
	<div class="sbi-fb-source-ctn sbi-personal-account-ctn sb-fs-boss sbi-fb-center-boss" v-if="personalAccountPopup == true">
		<div class="sbi-fb-source-popup sbi-fb-popup-inside sbi-narrower-modal">
			<div class="sbi-fb-popup-cls" @click.prevent.default="personalAccountPopup = false; step = 1;">
	            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
	                <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#141B38"/>
	            </svg>
	        </div>
			<div class="sbi-fb-personal-step1 sbi-fb-fs" v-if="step == 1">
                <div class="sbi-source-account-box sbi-fb-fs">
                	<div class="sbi-pers-account-icon" v-html="svgIcons['camera']"></div>
                </div>
                <div class="sbi-fb-source-top sbi-fb-fs">
                    <h3 v-html="personalAccountModalText.mainHeading1"></h3>
                    <p v-html="personalAccountModalText.mainDescription"></p>
                </div>
                <div class="sb-two-buttons-wrap">
                    <button class="sbi-fb-source-btn sb-btn-blue" v-html="personalAccountModalText.confirmBtn" @click.prevent.default="step = 2"></button>
                    <button class="sbi-fb-source-btn sb-btn-grey" v-html="personalAccountModalText.cancelBtn" @click.prevent.default="cancelMaybeLater"></button>
                </div>
            </div>

            <div class="sbi-fb-personal-step2 sbi-fb-fs" v-if="step == 2">
            	<div class="sbi-fb-personal-form sbi-fb-fs">
            		<h3 v-html="personalAccountModalText.mainHeading3"></h3>
            		<div class="sbi-fb-personal-upload-btn sbi-fb-fs">
                    	<button class="sbi-fb-source-btn sb-btn-grey" @click.prevent.default="openFileChooser">
                    		<span v-html="svgIcons['uploadFile']"></span>
                    		<span v-html="personalAccountModalText.uploadBtn"></span>
                    	</button>
                        <span v-html="personalAccountInfo.fileName"></span>
                        <input id="avatar_file" type="file" value="avatar_file" ref="file" v-on:change="onFileChooserChange" accept="image/png, image/jpeg">
            		</div>
            		<div class="sbi-fb-personal-textarea sbi-fb-fs">
            			<label v-html="personalAccountModalText.bioLabel"></label>
            			<textarea :placeholder="personalAccountModalText.bioPlaceholder" maxlength="140" v-model="personalAccountInfo.bio"></textarea>
            		</div>
            	</div>

                <div class="sb-two-buttons-wrap sbi-fb-fs">
                    <button class="sbi-fb-source-btn sb-btn-blue sbi-fb-fs"  :class="{loading: loading}" @click.prevent.default="addUpdatePersonalSourceInfo">
                        <span v-html="loading ? svgIcons['loaderSVG'] : genericText.add"></span>
                    </button>
                </div>
            </div>
		</div>
	</div>
</script>
