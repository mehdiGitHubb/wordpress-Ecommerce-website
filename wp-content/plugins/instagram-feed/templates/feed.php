<?php
/**
 * Smash Balloon Instagram Feed Main Template
 * Creates the wrapping HTML and adds settings as attributes
 *
 * @version 2.9 Instagram Feed by Smash Balloon
 *
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
$feed_styles     = SB_Instagram_Display_Elements::get_feed_style( $settings ); // already escaped
$feed_atts       = SB_Instagram_Display_Elements::get_feed_container_data_attributes( $settings );
$sb_images_style = SB_Instagram_Display_Elements::get_sbi_images_style( $settings ); // already escaped
$feed_classes    = SB_Instagram_Display_Elements::get_feed_container_css_classes( $settings, $additional_classes ); // already escaped

/**
 * Add HTML or execute code before the feed displays.
 * sbi_after_feed works the same way but executes
 * after the feed
 *
 * @param array $posts Instagram posts in feed
 * @param array $settings settings specific to this feed
 *
 * @since 2.2
 */
do_action( 'sbi_before_feed', $posts, $settings );

sbi_header_html( $settings, $header_data, 'outside' );
?>

<div id="sb_instagram" <?php echo $feed_classes . $feed_styles; ?> data-feedid="<?php echo esc_attr( $feed_id ); ?>" <?php echo $feed_atts; ?> data-shortcode-atts="<?php echo esc_attr( $shortcode_atts ); ?>" <?php echo $other_atts; ?>>
	<?php sbi_header_html( $settings, $header_data ); ?>

    <div id="sbi_images" <?php echo $sb_images_style; ?>>
		<?php
		if ( ! in_array( 'ajaxPostLoad', $flags, true ) ) {
			$this->posts_loop( $posts, $settings );
		}
		?>
    </div>

	<?php include sbi_get_feed_template_part( 'footer', $settings ); ?>

	<?php
	/**
	 * Things to add before the closing "div" tag for the main feed element. Several
	 * features rely on this hook such as local images and some error messages
	 *
	 * @param object SB_Instagram_Feed
	 * @param string $feed_id
	 *
	 * @since 2.1/5.2
	 */
	do_action( 'sbi_before_feed_end', $this, $feed_id ); ?>
</div>

<?php do_action( 'sbi_after_feed', $posts, $settings );?>
