<div v-if="selected === 'app-3'">
    <div class="sb-tab-box sb-ajax-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.ajaxBox.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="ajax-box-settings" class="sbi-checkbox">
                    <input type="checkbox" id="ajax-box-settings" v-model="model.advanced.sbi_ajax">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.ajaxBox.helpText}}
                </span>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-optimize-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.optimizeBox.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="enable-resize" class="sbi-checkbox">
                    <input type="checkbox" name="enable-resize" id="enable-resize" v-model="model.advanced.sbi_enable_resize">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                    <button type="button" class="sbi-btn ml-10 optimize-image-btn" @click="clearImageResizeCache()">
                        <span v-html="clearImageResizeCacheIcon()" :class="optimizeCacheStatus" v-if="optimizeCacheStatus !== null"></span>
                        {{advancedTab.optimizeBox.reset}}
                    </button>
                </label>

                <span class="help-text">
                    {{advancedTab.optimizeBox.helpText}}
                </span>
            </div>
        </div>
    </div>
	<div class="sb-tab-box sb-clear-error-log-box sb-reset-box-style clearfix">
		<div class="tab-label">
			<h3>{{advancedTab.resetErrorBox.title}}</h3>
		</div>
		<div class="sbi-tab-form-field">
			<button type="button" class="sbi-btn" @click="resetErrorLog()">
				<span v-html="resetErrorLogIcon()" :class="clearErrorLogStatus" v-if="clearErrorLogStatus !== null"></span>
				{{advancedTab.resetErrorBox.reset}}
			</button>
			<span class="help-text">
                {{advancedTab.resetErrorBox.helpText}}
            </span>
		</div>
	</div>
    <div class="sb-tab-box sb-usage-box clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.usageBox.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="usage-tracking" class="sbi-checkbox">
                    <input type="checkbox" name="usage-tracking" id="usage-tracking" v-model="model.advanced.usage_tracking">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text" v-html="advancedTab.usageBox.helpText"></span>
            </div>
        </div>
    </div>




    <div class="sb-tab-box sb-load-ajax-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.ajaxInitial.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="sb_ajax_initial" class="sbi-checkbox">
                    <input type="checkbox" name="sb_ajax_initial" id="sb_ajax_initial" v-model="model.advanced.sb_ajax_initial">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.ajaxInitial.helpText}}
                </span>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-enqueue-in-head-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.enqueueHead.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="enqueue_js_in_head" class="sbi-checkbox">
                    <input type="checkbox" name="enqueue_js_in_head" id="enqueue_js_in_head" v-model="model.advanced.sbi_enqueue_js_in_head">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.enqueueHead.helpText}}
                </span>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-enqueue-css-shortcode-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.enqueueShortcode.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="enqueue_css_in_shortcode" class="sbi-checkbox">
                    <input type="checkbox" name="enqueue_css_in_shortcode" id="enqueue_css_in_shortcode" v-model="model.advanced.sbi_enqueue_css_in_shortcode">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.enqueueShortcode.helpText}}
                </span>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-js-images-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.jsImages.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="enable_js_image_loading" class="sbi-checkbox">
                    <input type="checkbox" name="enable_js_image_loading" id="enable_js_image_loading" v-model="model.advanced.sbi_enable_js_image_loading">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.jsImages.helpText}}
                </span>
            </div>
        </div>
    </div>

    <div class="sb-tab-box sb-admin-error-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.adminErrorBox.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="disable-admin-error" class="sbi-checkbox">
                    <input type="checkbox" name="disable-admin-error" id="disable-admin-error" v-model="model.advanced.enable_admin_notice">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <span class="help-text">
                    {{advancedTab.adminErrorBox.helpText}}
                </span>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-feed-issue-box sb-reset-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.feedIssueBox.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <div class="sb-form-field">
                <label for="enable-email-report" class="sbi-checkbox">
                    <input type="checkbox" name="enable-email-report" id="enable-email-report" v-model="model.advanced.enable_email_report">
                    <span class="toggle-track">
                        <div class="toggle-indicator"></div>
                    </span>
                </label>
                <div class="items-center feed-issues-fields" v-if="model.advanced.enable_email_report">
                    <span class="help-text">
                        {{advancedTab.feedIssueBox.sendReport}}
                    </span>
                    <select id="sbi-send-report" class="sbi-select size-sm mr-3" v-model="model.advanced.email_notification">
                        <option v-for="(name, key) in advancedTab.feedIssueBox.weekDays" :value="name.val">{{name.label}}</option>
                    </select>
                    <span class="help-text">
                        {{advancedTab.feedIssueBox.to}}
                    </span>
                    <input type="text" name="report-emails" id="report-emails" class="sbi-form-field" :placeholder="advancedTab.feedIssueBox.placeholder" v-model="model.advanced.email_notification_addresses">
                </div>
                <div class="help-text">
                    <span v-html="advancedTab.feedIssueBox.helpText"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="sb-tab-box sb-optimize-box sb-dpa-clear-box-style clearfix">
        <div class="tab-label">
            <h3>{{advancedTab.dpaClear.title}}</h3>
        </div>

        <div class="sbi-tab-form-field">
            <button type="button" class="sbi-btn" @click="dpaReset()">
                <span v-html="dpaResetStatusIcon()" :class="dpaResetStatus" v-if="dpaResetStatus !== null"></span>
                {{advancedTab.dpaClear.clear}}
            </button>

            <span class="help-text">
                {{advancedTab.dpaClear.helpText}}
            </span>
        </div>
    </div>
</div>
