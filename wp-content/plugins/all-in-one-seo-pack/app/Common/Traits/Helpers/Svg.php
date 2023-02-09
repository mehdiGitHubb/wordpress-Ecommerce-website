<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains SVG specific helper methods.
 *
 * @since 4.1.4
 */
trait Svg {
	/**
	 * Sanitizes a SVG string.
	 *
	 * @since 4.1.4
	 *
	 * @param  string $svgString The SVG to check.
	 * @return string            The sanitized SVG.
	 */
	public function escSvg( $svgString ) {
		if ( ! is_string( $svgString ) ) {
			return false;
		}

		$ksesDefaults = wp_kses_allowed_html( 'post' );

		$svgArgs = [
			'svg'   => [
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true, // <= Must be lower case!
			],
			'g'     => [ 'fill' => true ],
			'title' => [ 'title' => true ],
			'path'  => [
				'd'    => true,
				'fill' => true,
			]
		];

		return wp_kses( $svgString, array_merge( $ksesDefaults, $svgArgs ) );
	}
}