<?php

/**
 * Notifications class for various notifications functionality
 *
 * @since      1.1.4
 * @package    Themify_Updater_Notifications
 * @author     Themify
 */
if ( !class_exists('Themify_Updater_Notifications') ) :

class Themify_Updater_Notifications {
    private static $notifications = array();
    private $notification_group = 3;
    private $option = null;
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return Themify_Updater_Notifications class single instance.
     */
    public static function get_instance() {
        return null == self::$instance ? self::$instance = new self : self::$instance;
    }

    function __construct()
    {
        if ( ! has_filter( 'admin_notices', array($this, 'display_notices') ) )
            add_action('admin_notices', array($this, 'display_notices'), 3);
    }

    private function get_user_options () {
        if ( !$this->option ) {
            $this->option = get_user_meta( get_current_user_id(), 'themify_updater_notifications', true);
            if ( empty($this->option) ) $this->option = array();
        }
    }

    private function update_user_options () {
        if ( is_array($this->option) ) {
            update_user_meta( get_current_user_id(), 'themify_updater_notifications', $this->option );
        }
    }

    public function display_notices() {
        foreach ( self::$notifications as $type => $notifications ){
            $this->wrapper( $notifications, $type );
        }
    }

    private function wrapper ( $notifications, $type ) {

        if ( sizeof($notifications, 0) > $this->notification_group && $type === 'warning') {
            $str = '';
            $wrapper = '<div class="notifications"><div class="%s themify-updater notification-group"><span><strong>'. sprintf( __('Themify\'s %d updates are available', 'themify-updater'), count($notifications) ) .'</strong></span><div style="display: none;">%s</div></div></div>';
            foreach ( $notifications as $key => $notification) {
                $str .= $notification['content'];
            }
            $classes = 'update update-nag';
            printf($wrapper, $classes, $str);
        } else {
            $wrapper = '<div class="notifications"><div class="%s"><p>%s</p></div></div>';
            foreach ( $notifications as $key => $notification) {

                if ($notification['dismiss'] && $this->dismiss($notification['dismiss'])) continue;

                $classes = array('notice', 'notice-'. $type);
                $classes = array_merge($classes, $notification['classes']);
                $button = '';
                 if ($notification['dismiss']) {
                     $classes[] = 'is-dismissible';
                     $button = $this->dismiss_button($notification['dismiss']);
                 }
                $classes = implode(' ', $classes);
                printf($wrapper, $classes, $notification['content'] . $button);
            }
        }
    }

    private function dismiss ( $id ) {
        $id = Themify_Updater_utils::get_hash($id);
        $id = 'dismiss_TU_notices_' . $id;

        if ( !$this->option ) $this->get_user_options();
        if ( isset( $this->option[$id] ) ) return true;

        if ( isset( $_GET[$id] ) && check_admin_referer( $id ) ) {

            if ( !isset( $this->option[$id] ) ) {
                $this->option[$id] = 1;
                $this->update_user_options();
            }
            return true;
        }
        return false;
    }

    private function dismiss_button( $id ) {
        $id = Themify_Updater_utils::get_hash($id);
        $id = 'dismiss_TU_notices_' . $id;
        $button = ' <a href="'. esc_url(wp_nonce_url(add_query_arg($id, 'themify_updater'), $id)) .'"
                           class="dismiss-notice themify-updater-dismiss"
                           target="_parent">'. __('Dismiss this notice', 'themify-updater') .'</a>.';
        return $button;
    }

    public function add_notice ($content, $type = 'update', $classes = array(), $dismiss_able = false, $dismissId = false) {

        if ( (bool) $dismiss_able && ! (bool)$dismissId ) return false;

        switch ($type) {
            case 'error': $type = 'error'; break;
            case 'warning': $type = 'warning'; break;
            case 'update': $type = 'warning'; break;
            case 'success': $type = 'success'; break;
            default: $type = 'info';
        }

        $key = $type . '_' . count( self::$notifications, 1);
        $key = Themify_Updater_utils::get_hash($key);

        self::$notifications[$type][$key] = array (
                'type' => $type,
                'classes' => is_array($classes) ? $classes : array(),
                'content' => $content,
                'dismiss' => (bool) $dismiss_able ? $dismissId : false
            );
        return $key;
    }

    /*
     * To re-display notifications which have dismiss button and already dismissed.
     *
     * */
    public function reAdd_notice ($dismissId) {
        $this->get_user_options();
        $dismissId = 'dismiss_TU_notices_' . Themify_Updater_utils::get_hash($dismissId);

        if ( isset($this->option[$dismissId] ) ) {
            unset($this->option[$dismissId]);
            $this->update_user_options();
        }
    }

    public function remove_notice ( $key = '') {
        foreach (self::$notifications as $type => $notifications) {
            if ( isset($notifications[$key]) )
                unset( self::$notifications[$type][$key] );
        }
    }
}
endif;