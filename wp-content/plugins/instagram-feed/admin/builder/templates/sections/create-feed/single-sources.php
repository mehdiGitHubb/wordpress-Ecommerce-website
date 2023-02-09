<div class="sbi-fb-fs" v-if=" ( checkSingleFeedType('user') || checkSingleFeedType('tagged') )" data-source="active">
	<div class="sbi-fb-slctsrc-content sbi-fb-fs">
		<div class="sbi-fb-sec-heading sbi-fb-fs">
			<h4>{{selectSourceScreen.mainHeading}}</h4>
			<span class="sb-caption sb-lighter">{{selectSourceScreen.description}}</span>
		</div>

		<div class="sbi-fb-sources-empty-ctn sbi-fb-fs" v-if="sourcesList.length == 0">
			<div class="sbi-fb-sources-empty-txt" v-html="selectSourceScreen.emptySourceDescription"></div>
			<div class="sbi-fb-sources-empty-btn-ctn">
				<div class="sb-addsources-btn sb-btn sb-btn-blue" @click.prevent.default="activateView('sourcePopup', 'creationRedirect')">
					 <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
	                    <path d="M9.66634 5.66634H5.66634V9.66634H4.33301V5.66634H0.333008V4.33301H4.33301V0.333008H5.66634V4.33301H9.66634V5.66634Z"/>
	                </svg>
               		<span class="sb-bold">{{genericText.addSource}}</span>
				</div>
			</div>
		</div>

		<div class="sbi-fb-srcslist-ctn sbi-fb-fs" v-if="sourcesList.length > 0">
			<div class="sbi-fb-srcs-item sbi-fb-srcs-new" @click.prevent.default="activateView('sourcePopup', 'creationRedirect')">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.66634 5.66634H5.66634V9.66634H4.33301V5.66634H0.333008V4.33301H4.33301V0.333008H5.66634V4.33301H9.66634V5.66634Z" fill="#0096CC"/>
                </svg>
                <span class="sb-small-p sb-bold">{{genericText.addNew}}</span>
			</div>
			<div class="sbi-fb-srcs-item sbi-fb-onbrd-tltp-parent sbi-fb-onbrd-tltp-center-top sbi-fb-onbrd-tltp-hover" v-for="(source, sourceIndex) in sourcesList" @click.prevent.default="selectSource(source)" :data-type="source.account_type" :data-active="isSourceSelectActive(source)"  :data-disabled="checkSourceDisabled(source)">
				<div class="sbi-fb-onbrd-tltp-elem" v-if="checkSourceDisabled(source)">
					<p v-if="checkSourceDisabled(source)" class="sbi-fb-onbrd-tltp-txt" v-for="perosnalAccountToolTipTxt in selectSourceScreen.perosnalAccountToolTipTxt" v-html="perosnalAccountToolTipTxt.replace(/ /g,' ')"></p>
				</div>

				<div class="sbi-fb-srcs-item-chkbx">
					<div class="sbi-fb-srcs-item-chkbx-ic"></div>
				</div>
                <div class="sbi-fb-srcs-item-avatar" v-if="returnAccountAvatar(source)">
                    <img :src="returnAccountAvatar(source)">
                </div>
				<div class="sbi-fb-srcs-item-inf">
                    <div class="sbi-fb-srcs-item-name sb-small-p sb-bold"><span>{{source.username}}</span></div>
                    <div class="sbi-fb-left-boss">
                        <div class="sbi-fb-srcs-item-type">
                            <div v-html="source.account_type == 'personal' ? svgIcons['user'] : svgIcons['flag']"></div>
                            <span class="sb-small sb-lighter">{{source.account_type}}</span>
                        </div>
						<div v-if="source.error !== '' || source.error_encryption" class="sb-source-error-wrap">
							<svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M6.50008 0.666664C3.28008 0.666664 0.666748 3.28 0.666748 6.5C0.666748 9.72 3.28008 12.3333 6.50008 12.3333C9.72008 12.3333 12.3334 9.72 12.3334 6.5C12.3334 3.28 9.72008 0.666664 6.50008 0.666664ZM7.08342 9.41667H5.91675V8.25H7.08342V9.41667ZM7.08342 7.08333H5.91675V3.58333H7.08342V7.08333Z" fill="#D72C2C"/>
							</svg>
							<span v-html="source.error !== '' ? genericText.errorSource : genericText.errorEncryption"></span><a href="#" @click.prevent.default="activateView('sourcePopupType', 'creationRedirect')" v-html="genericText.reconnect"></a>
						</div>
                    </div>
				</div>
			</div>
		</div>

	</div>
</div>

