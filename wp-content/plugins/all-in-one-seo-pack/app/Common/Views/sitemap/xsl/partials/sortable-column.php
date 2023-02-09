<?php
/**
 * XSL sortableColumn partial for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

// Just print out the title for now.
echo esc_html( $data['title'] );

/*$orderBy = 'ascending';
if ( ! empty( $data['parameters']['sitemap-orderby'] ) ) {
	$orderBy = $data['parameters']['sitemap-orderby'];
}

$isOrdering = false;
if ( ! empty( $data['parameters']['sitemap-order'] ) ) {
	$isOrdering = $data['column'] === $data['parameters']['sitemap-order'];
}

$link = add_query_arg( [
	'sitemap-order'   => $data['column'],
	'sitemap-orderby' => 'ascending' === $orderBy ? 'descending' : 'ascending'
], $data['sitemapUrl'] );
?>
<a href="<?php echo esc_url( $link ); ?>" class="sortable <?php echo esc_attr( ( $isOrdering ? 'active' : '' ) . ' ' . $orderBy ); ?>">
	<?php echo esc_html( $data['title'] ); ?>
</a>*/