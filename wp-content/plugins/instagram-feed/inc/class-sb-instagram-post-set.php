<?php
/**
 * Class SB_Instagram_Post_Set
 *
 * Useful for iterating over an array of posts and resizing images for
 * them or updating data in the custom database tables.
 *
 * @since 2.0/4.0
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class SB_Instagram_Post_Set {
	/**
	 * @var array
	 */
	private $post_data;

	/**
	 * @var array
	 */
	private $resized_image_data_for_set;

	/**
	 * @var string|null
	 */
	private $upload_dir;

	/**
	 * @var string|null
	 */
	private $upload_url;

	/**
	 * @var bool
	 */
	private $transient_name;

	/**
	 * @var null
	 */
	private $fill_in_timestamp;

	/**
	 * @var
	 */
	private $first_post_top_time_stamp;

	/**
	 * SB_Instagram_Post_Set constructor.
	 *
	 * @param array $post_data
	 * @param bool $transient_name
	 * @param null $fill_in_timestamp (optional)
	 * @param array $image_sizes (optional) sizes to create for personal and business account
	 *  currently thumb, low, and full can be set
	 * @param null $upload_dir (optional)
	 * @param null $upload_url (optional)
	 *
	 * @since 2.0/4.0
	 */
	public function __construct( $post_data, $transient_name = false, $fill_in_timestamp = null, $image_sizes = array(
		'personal' => array(
			'full'  => 640,
			'low'   => 320,
			'thumb' => 150,
		),
		'business' => array(
			'full'  => 640,
			'low'   => 320,
			'thumb' => 150,
		),
	), $upload_dir = null, $upload_url = null ) {
		$this->post_data = $post_data;

		$this->image_sizes = $image_sizes;

		if ( ! isset( $upload_dir ) || ! isset( $upload_url ) ) {
			$upload     = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir = trailingslashit( $upload_dir ) . SBI_UPLOADS_NAME;

			$upload_url = trailingslashit( $upload['baseurl'] ) . SBI_UPLOADS_NAME;
		}

		$this->upload_dir = $upload_dir;

		$this->upload_url = $upload_url;

		$this->transient_name = $transient_name;

		$this->fill_in_timestamp = $fill_in_timestamp;
	}

	/**
	 * @return array
	 *
	 * @since 2.0/4.0
	 */
	public function get_post_data() {
		if ( is_array( $this->post_data ) ) {
			return $this->post_data;
		} else {
			return array();
		}
	}

	/**
	 * @return array
	 *
	 * @since 2.0/4.0
	 */
	public function get_resized_image_data_for_set() {
		return $this->resized_image_data_for_set;
	}

	/**
	 * Loop through set of posts and update or create resized images based on
	 * whether or not they have been created and whether or not a record has been
	 * saved for this feed id
	 *
	 * @since 2.0/4.0
	 */
	public function maybe_save_update_and_resize_images_for_posts() {
		global $sb_instagram_posts_manager;

		$posts_iterated_through     = 0;
		$number_resized             = 0;
		$number_updated             = 0;
		$resized_image_data_for_set = array();
		$resizing_disabled          = $sb_instagram_posts_manager->image_resizing_disabled( $this->transient_name ) || $sb_instagram_posts_manager->max_resizing_per_time_period_reached();
		$is_top_post_feed           = ( substr( $this->transient_name, 4, 1 ) === '+' );

		foreach ( $this->post_data as $single_instagram_post_data ) {

			if ( isset( $single_instagram_post_data['id'] ) && $posts_iterated_through < 100 ) {
				$single_post = new SB_Instagram_Post( $single_instagram_post_data['id'] );
				$single_post->set_instagram_api_data( $single_instagram_post_data );
				$resized_image_data_for_set[ $single_instagram_post_data['id'] ] = array();

				if ( $is_top_post_feed ) {
					if ( empty( $this->first_post_top_time_stamp ) ) {
						$this_post_top_time_stamp = $single_post->get_top_time_stamp();
						if ( empty( $this_post_top_time_stamp ) ) {
							$this->first_post_top_time_stamp = $this->fill_in_timestamp;
						} else {
							$this->first_post_top_time_stamp = $single_post->get_top_time_stamp();
						}
					}
				}

				if ( ! $resizing_disabled ) {
					if ( ( ! $single_post->exists_in_posts_table() || ! $single_post->images_done_resizing() ) && $number_resized < 30 ) {

						if ( $sb_instagram_posts_manager->max_total_records_reached() ) {
							$sb_instagram_posts_manager->delete_least_used_image();
						}

						if ( ! $single_post->images_done_resizing() && $single_post->exists_in_posts_table() ) {
							$single_post->resize_and_save_image( $this->image_sizes, $this->upload_dir, $this->upload_url );
						} else {
							if ( $is_top_post_feed ) {
								if ( $single_post->save_in_db( $this->transient_name, date( 'Y-m-d H:i:s', strtotime( $this->first_post_top_time_stamp ) - ( 120 * $posts_iterated_through ) - 1 ) ) ) {
									$single_post->resize_and_save_image( $this->image_sizes, $this->upload_dir, $this->upload_url );
								}
							} else {
								if ( $single_post->save_in_db( $this->transient_name, date( 'Y-m-d H:i:s', strtotime( $this->fill_in_timestamp ) - ( 120 * $posts_iterated_through ) ) ) ) {
									$single_post->resize_and_save_image( $this->image_sizes, $this->upload_dir, $this->upload_url );
								}
							}
						}

						$number_resized++;
					} else {
						if ( $is_top_post_feed ) {
							$single_post->update_db_data( true, $this->transient_name, $this->image_sizes, $this->upload_dir, $this->upload_url, date( 'Y-m-d H:i:s', strtotime( $this->first_post_top_time_stamp ) - ( 120 * $posts_iterated_through ) ) );
						} else {
							$single_post->update_db_data( true, $this->transient_name, $this->image_sizes, $this->upload_dir, $this->upload_url );
						}
						if ( ! $single_post->exists_in_feeds_posts_table( $this->transient_name ) ) {
							$single_post->insert_sbi_instagram_feeds_posts( $this->transient_name );
						}
						$number_updated++;
					}

					$resized_image_data_for_set[ $single_instagram_post_data['id'] ] = $single_post->get_resized_image_array();
				}
			}

			$posts_iterated_through++;
		}

		$this->resized_image_data_for_set = $resized_image_data_for_set;
	}

}
