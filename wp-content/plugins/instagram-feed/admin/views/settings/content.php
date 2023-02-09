<div class="sbi-fb-full-wrapper sbi-fb-fs">
    <?php
        /**
         * SBI Admin Notices
         *
         * @since 4.0
         */
        do_action('sbi_admin_notices');
    ?>
    <div class="section-header">
        <h1>{{genericText.settings}}</h1>
    </div>

    <div class="sb-tabs-container" id="sb-tabs-container">
        <form action="">
            <div class="sb-tabs">
                <div class="left-buttons">
                    <tab v-bind:section="section" v-bind:index="index" v-for="(section, index) in sections" v-bind:class="{ active : section === currentTab }" v-bind:data-index="index+1" key="index"></tab>
                </div>
                <div class="right-buttons">
                    <button class="sbi-btn sb-btn-orange" @click.prevent="saveSettings" :disabled="btnStatus !== null">
                        <span v-if="pressedBtnName == 'saveChanges'" v-html="saveChangesIcon()"></span>
                        {{genericText.saveSettings}}
                    </button>
                </div>
                <span class="tab-indicator" v-bind:style="getStyle"></span>
            </div>
            <transition :name="chooseDirection">
                <div class="sb-tab-content" v-bind:is="selected">
                    <?php
                        InstagramFeed\SBI_View::render( 'settings.tab.general' );
                        InstagramFeed\SBI_View::render( 'settings.tab.feeds' );
                        InstagramFeed\SBI_View::render( 'settings.tab.advanced' );
                    ?>
                </div>
            </transition>
            <div class="sbi-save-button">
                <button class="sbi-btn sb-btn-orange" @click.prevent="saveSettings" :disabled="btnStatus !== null">
                    <span v-if="pressedBtnName == 'saveChanges'" v-html="saveChangesIcon()"></span>
                    {{genericText.saveSettings}}
                </button>
            </div>
             <div v-if="licenseType == 'free'" class="sbi-settings-cta">
                <?php
                    InstagramFeed\SBI_View::render( 'sections.settings_footer_cta' );
                ?>
            </div>

        </form>
    </div>
</div>
<?php
    include_once SBI_BUILDER_DIR . 'templates/sections/popup/add-source-popup.php';
    include_once SBI_BUILDER_DIR . 'templates/sections/popup/confirm-dialog-popup.php';
    include_once SBI_BUILDER_DIR . 'templates/sections/popup/source-instances.php';
    include_once SBI_BUILDER_DIR . 'templates/sections/popup/personal-account-popup.php';
?>
<div class="sb-notification-ctn" :data-active="notificationElement.shown" :data-type="notificationElement.type">
	<div class="sb-notification-icon" v-html="svgIcons[notificationElement.type+'Notification']"></div>
	<span class="sb-notification-text" v-html="notificationElement.text"></span>
</div>

<sb-add-source-component
:sources-list="sourcesList"
:select-source-screen="selectSourceScreen"
:views-active="viewsActive"
:generic-text="genericText"
:selected-feed="selectedFeed"
:svg-icons="svgIcons"
:links="links"
ref="addSourceRef"
>
</sb-add-source-component>

<sb-confirm-dialog-component
:dialog-box.sync="dialogBox"
:source-to-delete="sourceToDelete"
:svg-icons="svgIcons"
:parent-type="'settings'"
:generic-text="genericText"
></sb-confirm-dialog-component>

<sb-personal-account-component
:generic-text="genericText"
:svg-icons="svgIcons"
ref="personalAccountRef"
>
</sb-personal-account-component>
