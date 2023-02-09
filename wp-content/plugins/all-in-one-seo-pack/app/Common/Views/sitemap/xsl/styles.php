<?php
/**
 * Styles for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>
<style type="text/css">
	body {
		margin: 0;
		font-family: Helvetica, Arial, sans-serif;
		font-size: 68.5%;
	}
	#content-head {
		background-color: #141B38;
		padding: 20px 40px;
	}
	#content-head h1,
	#content-head p,
	#content-head a {
		color: #fff;
		font-size: 1.2em;
	}
	#content-head h1 {
		font-size: 2em;
	}
	table {
		margin: 20px 40px;
		border: none;
		border-collapse: collapse;
		font-size: 1em;
		width: 75%;
	}
	th {
		border-bottom: 1px solid #ccc;
		text-align: left;
		padding: 15px 5px;
		font-size: 14px;
	}
	td {
		padding: 10px 5px;
		border-left: 3px solid #fff;
	}
	tr.stripe {
		background-color: #f7f7f7;
	}
	table td a:not(.localized) {
		display: block;
	}
	table td a img {
		max-height: 30px;
		margin: 6px 3px;
	}
	.empty-sitemap {
		margin: 20px 40px;
		width: 75%;
	}
	.empty-sitemap__title {
		font-size: 18px;
		line-height: 125%;
		margin: 12px 0;
	}
	.empty-sitemap svg {
		width: 140px;
		height: 140px;
	}
	.empty-sitemap__buttons {
		margin-bottom: 30px;
	}
	.empty-sitemap__buttons .button {
		margin-right: 5px;
	}
	.breadcrumb {
		margin: 20px 40px;
		width: 75%;

		display: flex;
		align-items: center;
		font-size: 12px;
		font-weight: 600;
	}
	.breadcrumb a {
		color: #141B38;
		text-decoration: none;
	}
	.breadcrumb svg {
		margin: 0 10px;
	}
	@media (max-width: 1023px) {
		.breadcrumb svg:not(.back),
		.breadcrumb a:not(:last-of-type),
		.breadcrumb span {
			display: none;
		}
		.breadcrumb a:last-of-type::before {
			content: '<?php _e( 'Back', 'all-in-one-seo-pack' ); ?>'
		}
	}
	@media (min-width: 1024px) {
		.breadcrumb {
			font-size: 14px;
		}
		.breadcrumb a {
			font-weight: 400;
		}
		.breadcrumb svg.back {
			display: none;
		}
	}
</style>
