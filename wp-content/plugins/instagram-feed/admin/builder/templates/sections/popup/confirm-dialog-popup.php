<script type="text/x-template" id="sb-confirm-dialog-component">
	<div class="sb-dialog-ctn sb-fs-boss sbi-fb-center-boss" v-if="dialogBoxElement.active">
		<div class="sb-dialog-popup sbi-fb-popup-inside">
			<div class="sbi-fb-popup-cls" @click.prevent.default="closeConfirmDialog"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#141B38"/>
            </svg></div>
			<div class="sb-dialog-remove-source sbi-fb-fs" v-if="dialogBoxElement.type == 'deleteSourceCustomizer'">
				<div class="sbi-fb-srcs-item" :data-type="sourceToDelete.account_type">
					<div class="sbi-fb-srcs-item-avatar" v-if="returnAccountAvatar(sourceToDelete)">
						<img :src="returnAccountAvatar(sourceToDelete)">
					</div>
					<div class="sbi-fb-srcs-item-inf">
                        <div class="sbi-fb-srcs-item-name"><span>{{sourceToDelete.username}}</span></div>
						<div class="sbi-fb-srcs-item-type">
							<div v-html="sourceToDelete.account_type == 'group' ?  svgIcons['users'] : svgIcons['flag']"></div>
							<span v-html="sourceToDelete.account_type"></span>
						</div>
					</div>
					<div class="sbi-fb-srcs-item-remove" v-html="svgIcons['delete']"></div>
				</div>
			</div>
			<div class="sb-dialog-popup-content sbi-fb-fs">
				<strong v-html="dialogBoxElement.heading"></strong>
				<span v-html="dialogBoxElement.description"></span>
				<div class="sb-dialog-popup-actions sbi-fb-fs">
					<button class="sb-btn " :class="getButtonBackground('confirm',dialogBoxElement)" @click.prevent.default="confirmDialogAction" v-html="getButtonText('confirm',dialogBoxElement)"></button>
					<button class="sb-btn " :class="getButtonBackground('cancel',dialogBoxElement)" @click.prevent.default="closeConfirmDialog" v-html="getButtonText('cancel',dialogBoxElement)"></button>
				</div>
			</div>
		</div>
	</div>
</script>
