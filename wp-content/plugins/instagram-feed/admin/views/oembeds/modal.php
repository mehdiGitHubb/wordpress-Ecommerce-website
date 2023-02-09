<div class="sbi-oembed-modal" v-if="openFacebookInstaller">
    <div class="sbi-modal-content">
        <button type="button" class="cancel-btn sbi-btn" v-html="modal.timesIcon" @click="closeModal"></button>
        <div class="modal-icon">
            <img :src="modal.instaIcon" :alt="modal.title">
        </div>
        <h2>{{modal.title}}</h2>
        <p>{{modal.description}}</p>
        <div class="sb-action-buttons">
            <button type="button" class="sbi-btn sbi-install-btn" @click="installFacebook()" :class="installerStatus" :disabled="isFacebookActivated">
                <span v-html="installIcon()"></span>
                <span v-html="facebookInstallBtnText"></span>
            </button>
            <button type="button" class="sbi-btn" @click="closeModal" v-if="!isFacebookActivated">{{modal.cancel}}</button>
        </div>
    </div>
</div>
