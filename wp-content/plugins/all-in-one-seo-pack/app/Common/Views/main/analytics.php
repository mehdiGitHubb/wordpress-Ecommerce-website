<?php
/**
 * This is the output for google analytics on the page.
 *
 * @since 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

$googleAnalyticsId = aioseo()->options->deprecated->webmasterTools->googleAnalytics->id;

if ( empty( $googleAnalyticsId ) ) {
	return;
}

$options = $this->analytics->getOptions();
?>
		<script type="text/javascript"<?php echo $this->analytics->getScriptAttributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			ga('create', <?php echo wp_json_encode( $googleAnalyticsId ); ?><?php echo wp_kses_post( $options['domain'] ); ?><?php echo wp_kses_post( $options['jsOptions'] ); ?>);
		<?php
		foreach ( $options['options'] as $option ) :
			$string = 'ga(';
			foreach ( $option as $o ) :
				$string .= is_bool( $o )
					? $o
					: (
						is_array( $o )
							? '[\'' . sanitize_text_field( implode( '\', \'', $o ) ) . '\']'
							: '\'' . sanitize_text_field( $o ) . '\', '
					);
			endforeach;
			$string = rtrim( trim( $string ), ',' ) . ");\n";
			?>
	<?php echo $string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endforeach; ?>
	ga('send', 'pageview');
		</script>
		<script async src="https://www.google-analytics.com/analytics.js"></script>
<?php if ( $this->analytics->autoTrack() ) : ?>
		<script async src="<?php echo esc_url( add_query_arg( 'ver', AIOSEO_VERSION, $this->analytics->autoTrackUrl() ) ); ?>"></script>
<?php endif; ?>