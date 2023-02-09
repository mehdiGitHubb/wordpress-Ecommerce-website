<?php
namespace AIOSEO\Plugin\Common\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The base graph class.
 *
 * @since 4.0.0
 */
abstract class Graph {
	use Traits\Image;
	use Traits\SocialProfiles;

	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.0
	 */
	abstract public function get();

	/**
	 * Iterates over a list of functions and sets the results as graph data.
	 *
	 * @since 4.0.13
	 *
	 * @param  array $data          The graph data to add to.
	 * @param  array $dataFunctions List of functions to loop over, associated with a graph property.
	 * @return array $data          The graph data with the results added.
	 */
	protected function getData( $data, $dataFunctions ) {
		foreach ( $dataFunctions as $k => $f ) {
			if ( ! method_exists( $this, $f ) ) {
				continue;
			}

			$value = $this->$f();
			if ( $value || in_array( $k, aioseo()->schema->nullableFields, true ) ) {
				$data[ $k ] = $value;
			}
		}

		return $data;
	}
}