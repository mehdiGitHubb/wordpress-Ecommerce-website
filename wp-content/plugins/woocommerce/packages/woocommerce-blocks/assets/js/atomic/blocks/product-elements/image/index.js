/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { image, Icon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';

import { supports } from './supports';
import { attributes } from './attributes';
import sharedConfig from '../shared/config';

const blockConfig = {
	apiVersion: 2,
	name: 'woocommerce/product-image',
	title: __( 'Product Image', 'woocommerce' ),
	icon: {
		src: (
			<Icon
				icon={ image }
				className="wc-block-editor-components-block-icon"
			/>
		),
	},
	keywords: [ 'WooCommerce' ],
	description: __(
		'Display the main product image.',
		'woocommerce'
	),
	usesContext: [ 'query', 'queryId', 'postId' ],
	parent: [
		'@woocommerce/all-products',
		'@woocommerce/single-product',
		'core/post-template',
	],
	textdomain: 'woocommerce',
	attributes,
	supports,
	edit,
};

registerBlockType( 'woocommerce/product-image', {
	...sharedConfig,
	...blockConfig,
} );
