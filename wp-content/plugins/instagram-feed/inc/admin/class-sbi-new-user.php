<?php
/**
 * SBI_New_User.
 *
 * @since 2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SBI_New_User extends SBI_Notifications {

	/**
	 * Source of notifications content.
	 *
	 * @since 2.6
	 *
	 * @var string
	 */
	const SOURCE_URL = 'https://plugin.smashballoon.com/newuser.json';

	/**
	 * @var string
	 */
	const OPTION_NAME = 'sbi_newuser_notifications';

	/**
	 * Register hooks.
	 *
	 * @since 2.6
	 */
	public function hooks() {
		add_action( 'admin_notices', array( $this, 'output' ), 8 );

		add_action( 'admin_init', array( $this, 'dismiss' ) );
		add_action( 'wp_ajax_sbi_review_notice_consent_update', array( $this, 'review_notice_consent' ) );
	}

	public function option_name() {
		return self::OPTION_NAME;
	}

	public function source_url() {
		return self::SOURCE_URL;
	}

	/**
	 * Verify notification data before it is saved.
	 *
	 * @param array $notifications Array of notifications items to verify.
	 *
	 * @return array
	 *
	 * @since 2.6
	 */
	public function verify( $notifications ) {
		$data = array();

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return $data;
		}

		$option = $this->get_option();

		foreach ( $notifications as $key => $notification ) {

			// The message should never be empty, if they are, ignore.
			if ( empty( $notification['content'] ) ) {
				continue;
			}

			// Ignore if notification has already been dismissed.
			if ( ! empty( $option['dismissed'] ) && in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}

			$data[ $key ] = $notification;
		}

		return $data;
	}

	/**
	 * Verify saved notification data for active notifications.
	 *
	 * @since 2.6
	 *
	 * @param array $notifications Array of notifications items to verify.
	 *
	 * @return array
	 */
	public function verify_active( $notifications ) {
		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return array();
		}

		$sbi_statuses_option = get_option( 'sbi_statuses', array() );
		$current_time = sbi_get_current_time();

		// rating notice logic
		$sbi_rating_notice_option = get_option( 'sbi_rating_notice', false );
		$sbi_rating_notice_waiting = get_transient( 'instagram_feed_rating_notice_waiting' );
		$should_show_rating_notice = ($sbi_rating_notice_waiting !== 'waiting' && $sbi_rating_notice_option !== 'dismissed');

		// new user discount logic
		$in_new_user_month_range = true;
		$should_show_new_user_discount = false;
		$has_been_one_month_since_rating_dismissal = isset( $sbi_statuses_option['rating_notice_dismissed'] ) ? ((int)$sbi_statuses_option['rating_notice_dismissed'] + ((int)$notifications['review']['wait'] * DAY_IN_SECONDS)) < $current_time + 1: true;

		if ( isset( $sbi_statuses_option['first_install'] ) && $sbi_statuses_option['first_install'] === 'from_update' ) {
			global $current_user;
			$user_id = $current_user->ID;
			$ignore_new_user_sale_notice_meta = get_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice' );
			$ignore_new_user_sale_notice_meta = isset( $ignore_new_user_sale_notice_meta[0] ) ? $ignore_new_user_sale_notice_meta[0] : '';
			if ( $ignore_new_user_sale_notice_meta !== 'always' ) {
				$should_show_new_user_discount = true;
			}
		} elseif ( $in_new_user_month_range && $has_been_one_month_since_rating_dismissal && $sbi_rating_notice_waiting !== 'waiting' ) {
			global $current_user;
			$user_id = $current_user->ID;
			$ignore_new_user_sale_notice_meta = get_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice' );
			$ignore_new_user_sale_notice_meta = isset( $ignore_new_user_sale_notice_meta[0] ) ? $ignore_new_user_sale_notice_meta[0] : '';

			if ( $ignore_new_user_sale_notice_meta !== 'always'
				 && isset( $sbi_statuses_option['first_install'] )
				 && $current_time > (int)$sbi_statuses_option['first_install'] + ((int)$notifications['discount']['wait'] * DAY_IN_SECONDS) ) {
				$should_show_new_user_discount = true;
			}
		}

		if ( sbi_is_pro_version() ) {
			$should_show_new_user_discount = false;
		}

		if ( isset( $notifications['review'] ) && $should_show_rating_notice ) {
			return array( $notifications['review'] );
		} elseif ( isset( $notifications['discount'] ) && $should_show_new_user_discount ) {
			return array( $notifications['discount'] );
		}

		return array();
	}

	/**
	 * Get notification data.
	 *
	 * @since 2.6
	 *
	 * @return array
	 */
	public function get() {
		if ( ! $this->has_access() ) {
			return array();
		}

		$option = $this->get_option();

		// Only update if does not exist.
		if ( empty( $option['update'] ) ) {
			$this->update();
		}

		$events = ! empty( $option['events'] ) ? $this->verify_active( $option['events'] ) : array();
		$feed   = ! empty( $option['feed'] ) ? $this->verify_active( $option['feed'] ) : array();

		return array_merge( $events, $feed );
	}

	/**
	 * Add a manual notification event.
	 *
	 * @since 2.6
	 *
	 * @param array $notification Notification data.
	 */
	public function add( $notification ) {
		if ( empty( $notification['id'] ) ) {
			return;
		}

		$option = $this->get_option();

		if ( in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return;
		}

		foreach ( $option['events'] as $item ) {
			if ( $item['id'] === $notification['id'] ) {
				return;
			}
		}

		$notification = $this->verify( array( $notification ) );

		update_option(
			$this->option_name(),
			array(
				'update'    => $option['update'],
				'feed'      => $option['feed'],
				'events'    => array_merge( $notification, $option['events'] ),
				'dismissed' => $option['dismissed'],
			)
		);
	}

	/**
	 * Update notification data from feed.
	 *
	 * @since 2.6
	 */
	public function update() {
		$feed   = $this->fetch_feed();
		$option = $this->get_option();

		update_option(
			$this->option_name(),
			array(
				'update'    => time(),
				'feed'      => $feed,
				'events'    => $option['events'],
				'dismissed' => $option['dismissed'],
			)
		);
	}

	/**
	 * Do not enqueue anything extra.
	 *
	 * @since 2.6
	 */
	public function enqueues() {

	}

	public function review_notice_consent() {
		//Security Checks
		check_ajax_referer( 'sbi_nonce', 'sbi_nonce' );
		$cap = current_user_can( 'manage_instagram_feed_options' ) ? 'manage_instagram_feed_options' : 'manage_options';

		$cap = apply_filters( 'sbi_settings_pages_capability', $cap );
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(); // This auto-dies.
		}

		$consent = isset( $_POST[ 'consent' ] ) ? sanitize_text_field( $_POST[ 'consent' ] ) : '';

		update_option( 'sbi_review_consent', $consent );

		if ( $consent == 'no' ) {
			$sbi_statuses_option = get_option( 'sbi_statuses', array() );
			update_option( 'sbi_rating_notice', 'dismissed', false );
			$sbi_statuses_option['rating_notice_dismissed'] = sbi_get_current_time();
			update_option( 'sbi_statuses', $sbi_statuses_option, false );
		}
		wp_die();
	}

	/**
	 * Output notifications on Form Overview admin area.
	 *
	 * @since 2.6
	 */
	public function output() {
		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		// new user notices included in regular settings page notifications so this
		// checks to see if user is one of those pages
		if ( ! empty( $_GET['page'] )
			 && strpos( $_GET['page'], 'sbi' ) !== false ) {
			return;
		}

		$content_allowed_tags = array(
			'em'     => array(),
			'strong' => array(),
			'span'   => array(
				'style' => array(),
			),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);
		$image_overlay = '';

		$plugin_type = sbi_is_pro_version() ? 'pro' : 'free';

		foreach ( $notifications as $notification ) {
			$img_src = SBI_PLUGIN_URL . 'admin/assets/img/' . sanitize_text_field( $notification['image'] );
			$type = sanitize_text_field( $notification['id'] );
			// check if this is a review notice
			if( $type == 'review' ) {
				$review_consent = get_option( 'sbi_review_consent' );
				$sbi_open_feedback_url = 'https://smashballoon.com/feedback/?plugin=instagram-' . $plugin_type;
				// step #1 for the review notice
				if ( ! $review_consent ) {
					?>
					<div class="sbi_notice sbi_review_notice_step_1">
						<div class="sbi_thumb">
							<img src="<?php echo esc_url( $img_src ); ?>" alt="notice">
						</div>
						<div class="sbi-notice-text">
							<p class="sbi-notice-text-p"><?php echo __( 'Are you enjoying the Instagram Feed Plugin?', 'instagram-feed' ); ?></p>
						</div>
						<div class="sbi-notice-consent-btns">
							<?php
							printf(
								'<button class="sbi-btn-link" id="sbi_review_consent_yes">%s</button>',
								__( 'Yes', 'instagram-feed' )
							);

							printf(
								'<a href="%s" target="_blank" class="sbi-btn-link"  id="sbi_review_consent_no">%s</a>',
								$sbi_open_feedback_url,
								__( 'No', 'instagram-feed' )
							);
							?>
						</div>
					</div>
					<?php
				}
			}
			$close_href = wp_nonce_url( add_query_arg( array( 'sbi_dismiss' => $type ) ), 'sbi-' . $type, 'sbi_nonce' );

			$title = $this->get_notice_title( $notification );
			$content = $this->get_notice_content( $notification, $content_allowed_tags );

			$buttons = array();
			if ( ! empty( $notification['btns'] ) && is_array( $notification['btns'] ) ) {
				foreach ( $notification['btns'] as $btn_type => $btn ) {
					if ( ! is_array( $btn['url'] ) ) {
						$buttons[ $btn_type ]['url'] = $this->replace_merge_fields( $btn['url'], $notification );
					} elseif ( is_array( $btn['url'] ) ) {
						$buttons[ $btn_type ]['url'] = wp_nonce_url( add_query_arg( $btn['url'] ), 'sbi-' . $type, 'sbi_nonce' );
						$close_href                  = $buttons[ $btn_type ]['url'];
					}

					$buttons[ $btn_type ]['attr'] = '';
					if ( ! empty( $btn['attr'] ) ) {
						$buttons[ $btn_type ]['attr'] = ' target="_blank" rel="noopener noreferrer"';
					}

					$buttons[ $btn_type ]['class'] = '';
					if ( ! empty( $btn['class'] ) ) {
						$buttons[ $btn_type ]['class'] = ' ' . $btn['class'];
					}

					$buttons[ $btn_type ]['text'] = '';
					if ( ! empty( $btn['text'] ) ) {
						$buttons[ $btn_type ]['text'] = wp_kses( $btn['text'], $content_allowed_tags );
					}
				}
			}
		}

		$review_consent = get_option( 'sbi_review_consent' );
		$review_step2_style = '';
		if ( $type == 'review' && ! $review_consent ) {
			$review_step2_style = 'style="display: none;"';
		}
		?>

		<div class="sbi_notice_op sbi_notice sbi_<?php echo esc_attr( $type ); ?>_notice" <?php echo !empty( $review_step2_style ) ? $review_step2_style : ''; ?>>
			<div class="sbi_thumb">
				<img src="<?php echo esc_url( $img_src ); ?>" alt="notice">
				<?php echo $image_overlay; ?>
			</div>
			<div class="sbi-notice-text">
				<div class="sbi-notice-text-inner">
					<h3 class="sbi-notice-text-header"><?php echo $title; ?></h3>
					<p class="sbi-notice-text-p"><?php echo $content; ?></p>
				</div>
				<div class="sbi-notice-btns-wrap">
					<p class="sbi-notice-links">
						<?php
						foreach ( $buttons as $type => $button ) :
							$btn_classes = array('sbi-btn');
							$btn_classes[] = esc_attr( $button['class'] );
							if ( $type == 'primary' ) {
								$btn_classes[] = 'sbi-btn-blue';
							} else {
								$btn_classes[] = 'sbi-btn-grey';
							}
							?>
							<a class="<?php echo implode(' ', $btn_classes); ?>" href="<?php echo esc_attr( $button['url'] ); ?>"<?php echo $button['attr']; ?>><?php echo $button['text']; ?></a>
						<?php endforeach; ?>
					</p>
				</div>
			</div>
			<div class="sbi-notice-dismiss">
				<a href="<?php echo esc_url( $close_href ); ?>">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#141B38"></path>
					</svg>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * SBI Get Notice Title depending on the notice type
	 *
	 * @since 6.0
	 *
	 * @param array $notification
	 *
	 * @return string $title
	 */
	public function get_notice_title( $notification ) {
		$type = $notification['id'];
		$title = '';

		// Notice title depending on notice type
		if ( $type == 'review' ) {
			$title = __( 'Glad to hear you are enjoying it. Would you consider leaving a positive review?', 'instagram-feed' );
		} else if ( $type == 'discount' ) {
			$title =  __( 'Exclusive offer - 60% off!', 'instagram-feed' );
		} else {
			$title = $this->replace_merge_fields( $notification['title'], $notification );
		}

		return $title;
	}

	/**
	 * SBI Get Notice Content depending on the notice type
	 *
	 * @since 6.0
	 *
	 * @param array $notification
	 * @param array $content_allowed_tags
	 *
	 * @return string $content
	 */
	public function get_notice_content( $notification, $content_allowed_tags ) {
		$type = $notification['id'];
		$content = '';

		// Notice content depending on notice type
		if ( $type == 'review' ) {
			$content = __( 'It really helps to support the plugin and help others to discover it too!', 'instagram-feed' );
		} else if ( $type == 'discount' ) {
			$content =  __( 'We don’t run promotions very often, but for a limited time we’re offering 60% Off our Pro version to all users of our free Instagram Feed.', 'instagram-feed' );
		} else {
			if ( ! empty( $notification['content'] ) ) {
				$content = wp_kses( $this->replace_merge_fields( $notification['content'], $notification ), $content_allowed_tags );
			}
		}
		return $content;
	}

	/**
	 * SBI Get Notice Title depending on the notice type
	 *
	 * @since 6.0
	 *
	 * @param array $notification
	 *
	 * @return string $title
	 */
	public function dismiss() {
		global $current_user;
		$user_id             = $current_user->ID;
		$sbi_statuses_option = get_option( 'sbi_statuses', array() );

		if ( isset( $_GET['sbi_ignore_rating_notice_nag'] ) ) {
			$rating_ignore = false;
			if ( isset( $_GET['sbi_nonce'] ) && wp_verify_nonce( $_GET['sbi_nonce'], 'sbi-review' ) ) {
				$rating_ignore = isset( $_GET['sbi_ignore_rating_notice_nag'] ) ? sanitize_text_field( $_GET['sbi_ignore_rating_notice_nag'] ) : false;
			}
			if ( 1 === (int) $rating_ignore ) {
				update_option( 'sbi_rating_notice', 'dismissed', false );
				$sbi_statuses_option['rating_notice_dismissed'] = sbi_get_current_time();
				update_option( 'sbi_statuses', $sbi_statuses_option, false );

			} elseif ( 'later' === $rating_ignore ) {
				set_transient( 'instagram_feed_rating_notice_waiting', 'waiting', 2 * WEEK_IN_SECONDS );
				delete_option( 'sbi_review_consent' );
				update_option( 'sbi_rating_notice', 'pending', false );
			}
		}

		if ( isset( $_GET['sbi_ignore_new_user_sale_notice'] ) ) {
			$new_user_ignore = false;
			if ( isset( $_GET['sbi_nonce'] ) && wp_verify_nonce( $_GET['sbi_nonce'], 'sbi-discount' ) ) {
				$new_user_ignore = isset( $_GET['sbi_ignore_new_user_sale_notice'] ) ? sanitize_text_field( $_GET['sbi_ignore_new_user_sale_notice'] ) : false;
			}
			if ( 'always' === $new_user_ignore ) {
				update_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice', 'always' );

				$current_month_number  = (int) date( 'n', sbi_get_current_time() );
				$not_early_in_the_year = ( $current_month_number > 5 );

				if ( $not_early_in_the_year ) {
					update_user_meta( $user_id, 'sbi_ignore_bfcm_sale_notice', date( 'Y', sbi_get_current_time() ) );
				}
			}
		}

		if ( isset( $_GET['sbi_ignore_bfcm_sale_notice'] ) ) {
			$bfcm_ignore = false;
			if ( isset( $_GET['sbi_nonce'] ) && wp_verify_nonce( $_GET['sbi_nonce'], 'sbi-bfcm' ) ) {
				$bfcm_ignore = isset( $_GET['sbi_ignore_bfcm_sale_notice'] ) ? sanitize_text_field( $_GET['sbi_ignore_bfcm_sale_notice'] ) : false;
			}
			if ( 'always' === $bfcm_ignore ) {
				update_user_meta( $user_id, 'sbi_ignore_bfcm_sale_notice', 'always' );
			} elseif ( date( 'Y', sbi_get_current_time() ) === $bfcm_ignore ) {
				update_user_meta( $user_id, 'sbi_ignore_bfcm_sale_notice', date( 'Y', sbi_get_current_time() ) );
			}
			update_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice', 'always' );
		}

		if ( isset( $_GET['sbi_dismiss'] ) ) {
			$notice_dismiss = false;
			if ( isset( $_GET['sbi_nonce'] ) && wp_verify_nonce( $_GET['sbi_nonce'], 'sbi-notice-dismiss' ) ) {
				$notice_dismiss = sanitize_text_field( $_GET['sbi_dismiss'] );
			}
			if ( 'review' === $notice_dismiss ) {
				update_option( 'sbi_rating_notice', 'dismissed', false );
				$sbi_statuses_option['rating_notice_dismissed'] = sbi_get_current_time();
				update_option( 'sbi_statuses', $sbi_statuses_option, false );

				update_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice', 'always' );
			} elseif ( 'discount' === $notice_dismiss ) {
				update_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice', 'always' );

				$current_month_number  = (int) date( 'n', sbi_get_current_time() );
				$not_early_in_the_year = ( $current_month_number > 5 );

				if ( $not_early_in_the_year ) {
					update_user_meta( $user_id, 'sbi_ignore_bfcm_sale_notice', date( 'Y', sbi_get_current_time() ) );
				}

				update_user_meta( $user_id, 'sbi_ignore_new_user_sale_notice', 'always' );
			}
		}
	}
}
