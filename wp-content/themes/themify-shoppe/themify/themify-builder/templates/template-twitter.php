<?php

defined( 'ABSPATH' ) || exit;

/**
 * Template Twitter
 * 
 * This template can be overridden by copying it to yourtheme/themify-builder/template-twitter.php.
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_args = wp_parse_args( $args['mod_settings'], [
	'title' => '',
	'username' => '',
	'limit' => '',
	'time' => '1',
	'show_follow' => '0',
	'follow' => __( '&rarr; Follow me', 'themify' ),
	'custom_class' => '',
	'animation_effect' => '',
] );

unset( $args['mod_settings'] );

$container_class =apply_filters('themify_builder_module_classes', array(
    'module',
    'module-' . $args['mod_name'],
    $args['module_ID'],
	$fields_args['custom_class'],
), $args['mod_name'], $args['module_ID'], $fields_args );

if ( ! empty( $fields_args['global_styles'] ) && Themify_Builder::$frontedit_active === false ) {
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', self::parse_animation_effect( $fields_args, array(
    'class' => implode(' ', $container_class),
) ), $fields_args, $args['mod_name'], $args['module_ID'] );

if ( Themify_Builder::$frontedit_active === false ) {
    $container_props['data-lazy'] = 1;
}
?>
<!-- module twitter -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>

	<?php

	echo Themify_Builder_Component_Module::get_module_title( $fields_args, 'title' );

	if ( ! class_exists( 'Themify_Twitter_Api' ) ) {
		require THEMIFY_DIR . '/class-themify-twitter-api.php';
	}
	$twitterConnection = new Themify_Twitter_Api();
	$tweets = $twitterConnection->query( [
		'username' => $fields_args['username'],
		'limit' => (int) $fields_args['limit'],
		'include_retweets' => true,
		'exclude_replies' => false,
	], [
		'disable_cache' => Themify_Builder::$frontedit_active === true,
		'cache_duration' => themify_builder_get( 'setting-twitter_settings_cache', 'builder_settings_twitter_cache_duration' ),
	] );
	if ( is_wp_error( $tweets ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			echo $tweets->get_error_message();
		}
		echo '</div>';
		return;
	}
	?>

	<ul class="tb_twitter">
		<?php foreach( $tweets as $tweet ) : ?>
			<li class="tb_twitter_item">
				<?php echo Themify_Twitter_Api::make_clickable( $tweet ); ?>

				<?php if ( $fields_args['time'] ) : ?>
					<br /><em class="tb_twitter_timestamp tf_text_dec"><small><?php echo sprintf( __( '%s ago', 'themify' ), human_time_diff( strtotime( $tweet->created_at ) ) ); ?></small></em>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( $fields_args['show_follow'] ) : ?>
		<a class="tb_twitter_follow tf_inline_b" href="<?php echo esc_url( '//twitter.com/' .  $fields_args['username'] ); ?>"><?php echo esc_html( $fields_args['follow'] ); ?></a>
	<?php endif; ?>

</div><!-- /module twitter -->