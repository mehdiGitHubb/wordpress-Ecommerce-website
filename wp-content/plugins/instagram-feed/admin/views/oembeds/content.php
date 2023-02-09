<div class="sbi-fb-full-wrapper sbi-fb-fs">
	<?php
	/**
	 * SBI Admin Notices
	 *
	 * @since 4.0
	 */
	do_action('sbi_admin_notices');
	?>
    <div class="sbi-oembeds-container">
        <div class="sbi-section-header">
            <h3>{{genericText.title}}</h3>
            <p>{{genericText.description}}</p>
        </div>

        <div class="sbi-oembed-plugin-box-group">
            <div class="sbi-oembed-plugin-box sbi-oembed-instagram">
                <span class="oembed-icon" v-html="images.instaIcon"></span>
                <span class="oembed-text" v-if="instagram.doingOembeds">{{genericText.instagramOEmbedsEnabled}}</span>
                <span class="oembed-text" v-else="instagram.doingOembeds">{{genericText.instagramOEmbeds}}</span>
                <span class="sbi-oembed-btn">

                    <button v-if="instagram.doingOembeds" @click="disableInstaoEmbed()" class="sbi-btn disable-oembed" :class="{loading: instaoEmbedLoader}">
                        <span v-if="instaoEmbedLoader" v-html="loaderSVG"></span>
                        {{genericText.disable}}
                    </button>
                    <a v-else :href="connectionURL" class="sbi-btn-blue sbi-btn" :class="{loading: instaoEmbedLoader}" @click="instaoEmbedLoader = true">
                        <span v-if="instaoEmbedLoader" v-html="loaderSVG"></span>
                        {{genericText.enable}}
                    </a>
                </span>
            </div>
            <div class="sbi-oembed-plugin-box sbi-oembed-facebook">
                <span class="oembed-icon" v-html="images.fbIcon"></span>
                <span class="oembed-text" v-if="facebook.doingOembeds">{{genericText.facebookOEmbedsEnabled}}</span>
                <span class="oembed-text" v-else="facebook.doingOembeds">{{genericText.facebookOEmbeds}}</span>
                <span class="sbi-oembed-btn">

                    <button v-if="facebook.doingOembeds" @click="disableFboEmbed()" class="sbi-btn disable-oembed" :class="{loading: fboEmbedLoader}">
                        <span v-if="fboEmbedLoader" v-html="loaderSVG"></span>
                        {{genericText.disable}}
                    </button>
                    <button v-else @click="FacebookShouldInstallOrEnable()" class="sbi-btn sbi-btn-blue" :class="{loading: fboEmbedLoader}">
                        <span v-if="fboEmbedLoader" v-html="loaderSVG"></span>
                        {{genericText.enable}}
                    </button>
                </span>
            </div>

        </div>

        <div class="sbi-oembed-information">
            <div class="sb-box-header">
                <h3 v-if="isoEmbedsEnabled()">{{genericText.whatElseOembeds}}</h3>
                <h3 v-else>{{genericText.whatAreOembeds}}</h3>
            </div>
			<?php
			InstagramFeed\SBI_View::render( 'oembeds.oembed_features' );
			InstagramFeed\SBI_View::render( 'oembeds.plugin_info' );
			?>
        </div>
    </div>
</div>
<?php
InstagramFeed\SBI_View::render( 'oembeds.modal' );
?>