<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains URL helper methods.
 *
 * @since 4.2.5
 */
trait Url {
	/**
	 * Removes a query string parameter from a URL.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $url        The url.
	 * @param  array  $parameters The parameter keys to remove.
	 * @return string             The url without the parameters removed.
	 */
	public function urlRemoveQueryParameter( $url, $parameters ) {
		$url = wp_parse_url( $url );
		if ( ! empty( $url['query'] ) ) {
			// Take the query string apart.
			parse_str( $url['query'], $queryStringArray );

			// Remove parameters.
			foreach ( $parameters as $parameter ) {
				if ( isset( $queryStringArray[ $parameter ] ) ) {
					unset( $queryStringArray[ $parameter ] );
				}
			}

			// Rebuild the query string.
			$url['query'] = build_query( $queryStringArray );

			// Rebuild the URL from parse_url.
			$url = $this->buildUrl( $url );
		}

		return $url;
	}

	/**
	 * Builds a URL from a parse_url array.
	 *
	 * @since 4.2.5
	 *
	 * @param  array  $params  The params array.
	 * @param  array  $include The keys to include [scheme, user, pass, host, port, path, query, fragment].
	 * @param  array  $exclude The keys to exclude [scheme, user, pass, host, port, path, query, fragment].
	 * @return string          The built url.
	 */
	public function buildUrl( $params, $include = [], $exclude = [] ) {
		if ( ! is_array( $params ) ) {
			return $params;
		}

		if ( ! empty( $include ) ) {
			foreach ( array_keys( $params ) as $includeKey ) {
				if ( ! in_array( $includeKey, $include, true ) ) {
					unset( $params[ $includeKey ] );
				}
			}
		}

		if ( ! empty( $exclude ) ) {
			foreach ( array_keys( $params ) as $excludeKey ) {
				if ( in_array( $excludeKey, $exclude, true ) ) {
					unset( $params[ $excludeKey ] );
				}
			}
		}

		$url = '';
		if ( ! empty( $params['scheme'] ) ) {
			$url .= $params['scheme'] . '://';
		}
		if ( ! empty( $params['user'] ) ) {
			$url .= $params['user'];

			if ( isset( $params['pass'] ) ) {
				$url .= ':' . $params['pass'];
			}

			$url .= '@';
		}

		if ( ! empty( $params['host'] ) ) {
			$url .= $params['host'];
		}

		if ( ! empty( $params['port'] ) ) {
			$url .= ':' . $params['port'];
		}

		if ( ! empty( $params['path'] ) ) {
			$url .= $params['path'];
		}

		if ( ! empty( $params['query'] ) ) {
			$url .= '?' . $params['query'];
		}

		if ( ! empty( $params['fragment'] ) ) {
			$url .= '#' . $params['fragment'];
		}

		return $url;
	}
}