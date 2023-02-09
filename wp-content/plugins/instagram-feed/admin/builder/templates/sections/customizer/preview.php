<div class="sb-customizer-preview" :data-preview-device="customizerScreens.previewScreen">
	<?php
		/**
		 * CFF Admin Notices
		 *
		 * @since 4.0
		 */
		do_action('sbi_admin_notices');

		$feed_id = ! empty( $_GET['feed_id'] ) ? (int)$_GET['feed_id'] : 0;
	?>
	<div class="sb-preview-ctn sb-tr-2">
		<div class="sb-preview-top-chooser sbi-fb-fs">
			<strong v-html="genericText.preview"></strong>
			<div class="sb-preview-chooser">
				<button class="sb-preview-chooser-btn" v-for="device in previewScreens" v-bind:class="'sb-' + device" v-html="svgIcons[device]" @click.prevent.default="switchCustomizerPreviewDevice(device)" :data-active="customizerScreens.previewScreen == device"></button>
			</div>
		</div>

		<div class="sbi-preview-ctn sbi-fb-fs">
			<div>
				<component :is="{template}"></component>
			</div>
		</div>

	</div>
</div>


