<?php
/**
 * Includes functions related to actions while in the admin area.
 *
 * - All AJAX related features
 * - Enqueueing of JS and CSS files
 * - Settings link on "Plugins" page
 * - Creation of local avatar image files
 * - Connecting accounts on the "Configure" tab
 * - Displaying admin notices
 * - Clearing caches
 * - License renewal
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function sb_instagram_menu() {
	$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';

	$cap = apply_filters( 'sbi_settings_pages_capability', $cap );

	global $sb_instagram_posts_manager;
	$notice = '';
	if ( $sb_instagram_posts_manager->are_critical_errors() ) {
		$notice = ' <span class="update-plugins sbi-error-alert sbi-notice-alert"><span>!</span></span>';
	}

	$notifications = false;
	if ( class_exists( '\SBI_Notifications' ) ) {
		$sbi_notifications = new \SBI_Notifications();
		$notifications = $sbi_notifications->get();
	}

	$notice_bubble = '';
	if ( empty( $notice ) && ! empty( $notifications ) && is_array( $notifications ) ) {
		$notice_bubble = ' <span class="sbi-notice-alert"><span>' . count( $notifications ) . '</span></span>';
	}

	add_menu_page(
		__( 'Instagram Feed', 'instagram-feed' ),
		__( 'Instagram Feed', 'instagram-feed' ). $notice_bubble . $notice,
		$cap,
		'sb-instagram-feed',
		'sb_instagram_settings_page'
	);

	add_submenu_page(
		'sb-instagram-feed',
		__( 'Upgrade to Pro', 'instagram-feed' ),
		__( '<span class="sbi_get_pro">Try the Pro Demo</span>', 'instagram-feed' ),
		$cap,
		'https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=menu-link&utm_medium=upgrade-link',
		''
	);

	//Show a Instagram plugin menu item if it isn't already installed
	if( !is_plugin_active( 'custom-facebook-feed/custom-facebook-feed.php' ) && !is_plugin_active( 'custom-facebook-feed-pro/custom-facebook-feed.php' )  && current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' ) ){
		add_submenu_page(
			'sb-instagram-feed',
			__( 'Facebook Feed', 'instagram-feed' ),
			'<span class="sbi_get_cff">' . __( 'Facebook Feed', 'instagram-feed' ) . '</span>',
			$cap,
			'admin.php?page=cff-builder',
			''
		);
	}

	//Show a Twitter plugin menu item if it isn't already installed
	if( !is_plugin_active( 'custom-twitter-feeds/custom-twitter-feed.php' ) && !is_plugin_active( 'custom-twitter-feeds-pro/custom-twitter-feed.php' )  && current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' )  ){
		add_submenu_page(
			'sb-instagram-feed',
			__( 'Twitter Feed', 'instagram-feed' ),
			'<span class="sbi_get_ctf">' . __( 'Twitter Feed', 'instagram-feed' ) . '</span>',
			$cap,
			'admin.php?page=sb-instagram-feed&tab=more',
			''
		);
	}

	//Show a YouTube plugin menu item if it isn't already installed
	if( !is_plugin_active( 'feeds-for-youtube/youtube-feed.php' ) && !is_plugin_active( 'youtube-feed-pro/youtube-feed.php' ) && current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' )  ){
		add_submenu_page(
			'sb-instagram-feed',
			__( 'YouTube Feed', 'instagram-feed' ),
			'<span class="sbi_get_yt">' . __( 'YouTube Feed', 'instagram-feed' ) . '</span>',
			$cap,
			'admin.php?page=sb-instagram-feed&tab=more',
			''
		);
	}
}
add_action( 'admin_menu', 'sb_instagram_menu' );

function sbi_add_settings_link( $links ) {
	$pro_link = '<a href="https://smashballoon.com/instagram-feed/demo/?utm_campaign=instagram-free&utm_source=plugins-page&utm_medium=upgrade-link" target="_blank" style="font-weight: bold; color: #1da867;">' . __( 'Try the Pro Demo', 'instagram-feed' ) . '</a>';

	$sbi_settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=sbi-settings' ) ) . '">' . esc_html__( 'Settings', 'instagram-feed' ) . '</a>';
	array_unshift( $links, $pro_link, $sbi_settings_link );

	return $links;
}
add_filter( "plugin_action_links_instagram-feed/instagram-feed.php", 'sbi_add_settings_link', 10, 2 );

function sb_instagram_admin_style() {
	wp_register_style( 'sb_instagram_admin_css', SBI_PLUGIN_URL . 'css/sb-instagram-admin.css', array(), SBIVER );
	wp_enqueue_style( 'sb_instagram_admin_css' );
	wp_enqueue_style( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'sb_instagram_admin_style' );

function sb_instagram_admin_scripts() {
	wp_enqueue_script( 'sb_instagram_admin_js', SBI_PLUGIN_URL . 'js/sb-instagram-admin-6.js', array(), SBIVER, true );
	wp_localize_script(
		'sb_instagram_admin_js',
		'sbiA',
		array(
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'sbi_nonce' => wp_create_nonce( 'sbi_nonce' ),
		)
	);
	$strings = array(
		'addon_activate'                  => esc_html__( 'Activate', 'instagram-feed' ),
		'addon_activated'                 => esc_html__( 'Activated', 'instagram-feed' ),
		'addon_active'                    => esc_html__( 'Active', 'instagram-feed' ),
		'addon_deactivate'                => esc_html__( 'Deactivate', 'instagram-feed' ),
		'addon_inactive'                  => esc_html__( 'Inactive', 'instagram-feed' ),
		'addon_install'                   => esc_html__( 'Install Addon', 'instagram-feed' ),
		'addon_error'                     => esc_html__( 'Could not install addon. Please download from wpforms.com and install manually.', 'instagram-feed' ),
		'plugin_error'                    => esc_html__( 'Could not install a plugin. Please download from WordPress.org and install manually.', 'instagram-feed' ),
		'addon_search'                    => esc_html__( 'Searching Addons', 'instagram-feed' ),
		'ajax_url'                        => admin_url( 'admin-ajax.php' ),
		'cancel'                          => esc_html__( 'Cancel', 'instagram-feed' ),
		'close'                           => esc_html__( 'Close', 'instagram-feed' ),
		'nonce'                           => wp_create_nonce( 'sbi-admin' ),
		'almost_done'                     => esc_html__( 'Almost Done', 'instagram-feed' ),
		'oops'                            => esc_html__( 'Oops!', 'instagram-feed' ),
		'ok'                              => esc_html__( 'OK', 'instagram-feed' ),
		'plugin_install_activate_btn'     => esc_html__( 'Install and Activate', 'instagram-feed' ),
		'plugin_install_activate_confirm' => esc_html__( 'needs to be installed and activated to import its forms. Would you like us to install and activate it for you?', 'instagram-feed' ),
		'plugin_activate_btn'             => esc_html__( 'Activate', 'instagram-feed' ),
	);
	$strings = apply_filters( 'sbi_admin_strings', $strings );
	wp_localize_script(
		'sb_instagram_admin_js',
		'sbi_admin',
		$strings
	);
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'sb_instagram_admin_scripts' );

function sbi_formatted_error( $response ) {
	if ( isset( $response['error'] ) ) {
		$response['error']['message'] = str_replace( 'Please read the Graph API documentation at https://developers.facebook.com/docs/graph-api', '', $response['error']['message'] );
		$error  = '<span>' . sprintf( __( 'API error %s:', 'instagram-feed' ), esc_html( $response['error']['code'] ) ) . ' ' . esc_html( $response['error']['message'] ) . '</span>';
		$error .= '<div class="license-action-btns"><p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a></p></div>';

		return $error;
	} else {
		$message = '<span>' . sprintf( __( 'Error connecting to %s.', 'instagram-feed' ), $response['url'] ) . '</span>';
		if ( isset( $response['response'] ) && isset( $response['response']->errors ) ) {
			foreach ( $response['response']->errors as $key => $item ) {
				$message .= '<span>' . esc_html( $key ) . ' - ' . esc_html( $item[0] ) . '</span>';
			}
		}
		$message .= '<div class="license-action-btns"><p class="sbi-error-directions"><a href="https://smashballoon.com/instagram-feed/docs/errors/" target="_blank" rel="noopener">' . __( 'Directions on how to resolve this issue', 'instagram-feed' ) . '</a></p></div>';

		return $message;
	}
}

function sbi_connect_new_account( $access_token, $account_id ) {
	$split_id   = explode( ' ', trim( $account_id ) );
	$account_id = preg_replace( '/[^A-Za-z0-9 ]/', '', $split_id[0] );
	if ( ! empty( $account_id ) ) {
		$split_token  = explode( ' ', trim( $access_token ) );
		$access_token = preg_replace( '/[^A-Za-z0-9 ]/', '', $split_token[0] );
	}

	$account = array(
		'access_token' => $access_token,
		'user_id'      => $account_id,
		'type'         => 'business',
	);

	if ( sbi_code_check( $access_token ) ) {
		$account['type'] = 'basic';
	}

	$connector = new SBI_Account_Connector();

	$response = $connector->fetch( $account );

	if ( isset( $response['access_token'] ) ) {
		$connector->add_account_data( $response );
		$connector->update_stored_account();
		$connector->after_update();
		return $connector->get_account_data();
	} else {
		return $response;
	}
}

add_action( 'sbi_admin_notices', 'sbi_admin_error_notices' );

function sbi_admin_error_notices() {
	global $sb_instagram_posts_manager;


	if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'sbi-settings' ), true) ) {
		$errors = $sb_instagram_posts_manager->get_errors();
		if ( ! empty( $errors ) && (! empty( $errors['database_create'] ) || ! empty( $errors['upload_dir'] )) ) : ?>
			<div class="sbi-admin-notices sbi-critical-error-notice">
					<span class="sb-notice-icon sb-error-icon">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM11 15H9V13H11V15ZM11 11H9V5H11V11Z" fill="#D72C2C"/>
						</svg>
					</span>
				<div class="sbi-notice-body">

					<?php if ( ! empty( $errors['database_create'] ) ) : ?>
						<h3 class="sb-notice-title">
							<?php echo esc_html__( 'Instagram Feed was unable to create new database tables.', 'instagram-feed') ; ?>
						</h3>
						<p><?php echo wp_kses_post( $errors['database_create'] ); ?></p><br><br>
						<p class="sbi-error-directions"><a href="https://smashballoon.com/docs/instagram/" class="sbi-license-btn sbi-btn-blue sbi-notice-btn" target="_blank"><?php esc_html_e(  'Visit our FAQ page for help', 'instagram-feed' ); ?></a> <button class="sbi-retry-db sbi-space-left sbi-btn sbi-notice-btn sbi-btn-grey"><?php esc_html_e(  'Try creating database tables again', 'instagram-feed' ); ?></button></p>
					<?php
					endif;
					?>
					<?php if ( ! empty( $errors['upload_dir'] ) ) : ?>
						<p><?php echo wp_kses_post( $errors['upload_dir'] ); ?></p><br><br>

						<p class="sbi-error-directions"><a href="https://smashballoon.com/docs/instagram/" class="sbi-license-btn sbi-btn-blue sbi-notice-btn" target="_blank"><?php esc_html_e(  'Visit our FAQ page for help', 'instagram-feed' ); ?></a></p>

					<?php endif; ?>
				</div>
			</div>

		<?php endif;

		if ( ! empty( $errors ) && ( ! empty( $errors['unused_feed'] ) ) ) : ?>
            <div class="sbi-admin-notices sbi-critical-error-notice">
				<span class="sb-notice-icon sb-error-icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM11 15H9V13H11V15ZM11 11H9V5H11V11Z"
                              fill="#D72C2C"/>
                    </svg>
				</span>
                <div class="sbi-notice-body">
                    <h3 class="sb-notice-title">
						<?php echo esc_html__( 'Action Required Within 7 Days:', 'instagram-feed' ); ?>
                    </h3>
                    <p><?php echo wp_kses_post( $errors['unused_feed'] ); ?></p>
                    <p><?php echo esc_html__( 'Or you can simply press the "Fix Usage" button to fix this issue.', 'instagram-feed' ); ?></p>
                    <br>
                    <p class="sbi-error-directions">
                        <button class="sbi-reset-unused-feed-usage sbi-space-left sbi-btn sbi-notice-btn sbi-btn-blue"><?php esc_html_e( 'Fix Usage', 'instagram-feed' ); ?></button>
                    </p>
                </div>
            </div>

		<?php endif;

		if ( ! empty( $errors ) && ( ! empty( $errors['platform_data_deleted'] ) ) ) : ?>
            <div class="sbi-admin-notices sbi-critical-error-notice">
				<span class="sb-notice-icon sb-error-icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM11 15H9V13H11V15ZM11 11H9V5H11V11Z"
                              fill="#D72C2C"/>
                    </svg>
				</span>
                <div class="sbi-notice-body">
                    <h3 class="sb-notice-title">
						<?php echo esc_html__( 'All Instagram Data has Been Removed:', 'instagram-feed' ); ?>
                    </h3>
                    <p><?php echo wp_kses_post( $errors['platform_data_deleted'] ); ?></p>
                    <p><?php echo esc_html__( 'To fix your feeds, reconnect all accounts that were in use on the Settings page.', 'instagram-feed' ); ?></p>
                    <br>
                </div>
            </div>

		<?php endif;
		$errors = $sb_instagram_posts_manager->get_critical_errors();
		if ( $sb_instagram_posts_manager->are_critical_errors() ) :
			?>
			<div class="sbi-admin-notices sbi-critical-error-notice">
					<span class="sb-notice-icon sb-error-icon">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM11 15H9V13H11V15ZM11 11H9V5H11V11Z" fill="#D72C2C"/>
						</svg>
					</span>
				<div class="sbi-notice-body">
					<h3 class="sb-notice-title">
						<?php echo esc_html__( 'Instagram Feed is encountering an error and your feeds may not be updating due to the following reasons:', 'instagram-feed') ; ?>
					</h3>

					<p><?php echo wp_kses_post( $errors ); ?></p>
				</div>
			</div>
		<?php
		endif;
	}
}

function sbi_reset_log() {
	check_ajax_referer( 'sbi_nonce', 'sbi_nonce' );

	if ( ! sbi_current_user_can( 'manage_instagram_feed_options' ) ) {
		wp_send_json_error();
	}

	global $sb_instagram_posts_manager;

	$sb_instagram_posts_manager->remove_all_errors();
	sbi_clear_caches();
	wp_send_json_success( '1' );
}
add_action( 'wp_ajax_sbi_reset_log', 'sbi_reset_log' );

function sb_instagram_settings_page() {
	$link = admin_url( 'admin.php?page=sbi-settings' );
	?>
	<div id="sbi_admin">
		<div class="sbi_notice">
			<strong><?php esc_html_e( 'The Instagram Feed Settings page has moved!', 'instagram-feed' ); ?></strong>
			<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Click here to go to the new page.', 'instagram-feed' ); ?></a>
		</div>
	</div>
	<?php
}
