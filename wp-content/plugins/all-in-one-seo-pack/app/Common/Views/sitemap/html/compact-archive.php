<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.ControlStructures.InlineControlStructure.NotAllowed

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="aioseo-html-sitemap">
	<div class="aioseo-html-sitemap-compact-archive">
		<?php if ( empty( $data['dateArchives'] ) ) esc_html_e( 'No date archives could be found.', 'all-in-one-seo-pack' ); ?>

		<?php if ( ! empty( $data['lines'] ) ) : ?>
			<ul>
				<?php echo $data['lines']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</ul>
		<?php endif; ?>

	</div>
</div>