<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BreadcrumbList graph class.
 *
 * @since 4.0.0
 */
class BreadcrumbList extends Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 *
	 * @return array $data The graph data.
	 */
	public function get() {
		$breadcrumbs = isset( aioseo()->schema->context['breadcrumb'] ) ? aioseo()->schema->context['breadcrumb'] : '';
		if ( ! $breadcrumbs || ! count( $breadcrumbs ) ) {
			return [];
		}

		$data = [
			'@type'           => 'BreadcrumbList',
			'@id'             => aioseo()->schema->context['url'] . '#breadcrumblist',
			'itemListElement' => []
		];

		$trailLength = count( $breadcrumbs );
		foreach ( $breadcrumbs as $breadcrumb ) {
			$listItem = [
				'@type'    => 'ListItem',
				'@id'      => $breadcrumb['url'] . '#listItem',
				'position' => $breadcrumb['position'],
				'item'     => [
					'@type'       => 'WebPage',
					'@id'         => $breadcrumb['url'],
					'name'        => ! empty( $breadcrumb['name'] ) ? $breadcrumb['name'] : '',
					'description' => ! empty( $breadcrumb['description'] ) ? $breadcrumb['description'] : '',
					'url'         => $breadcrumb['url'],
				]
			];

			if ( 1 === $trailLength ) {
				$data['itemListElement'][] = $listItem;
				continue;
			}

			if ( $trailLength > $breadcrumb['position'] ) {
				$listItem['nextItem'] = $breadcrumbs[ $breadcrumb['position'] ]['url'] . '#listItem';
			}

			if ( 1 < $breadcrumb['position'] ) {
				$listItem['previousItem'] = $breadcrumbs[ $breadcrumb['position'] - 2 ]['url'] . '#listItem';
			}

			$data['itemListElement'][] = $listItem;
		}

		return $data;
	}
}