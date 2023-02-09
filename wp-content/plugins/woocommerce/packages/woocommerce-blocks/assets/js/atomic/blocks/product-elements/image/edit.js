/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { createInterpolateElement, useEffect } from '@wordpress/element';
import { getAdminLink, getSettingWithCoercion } from '@woocommerce/settings';
import { isBoolean } from '@woocommerce/types';
import { Disabled, PanelBody, ToggleControl } from '@wordpress/components';
import ToggleButtonControl from '@woocommerce/editor-components/toggle-button-control';

/**
 * Internal dependencies
 */
import Block from './block';

const Edit = ( { attributes, setAttributes, context } ) => {
	const { showProductLink, imageSizing, showSaleBadge, saleBadgeAlign } =
		attributes;

	const blockProps = useBlockProps();

	const isDescendentOfQueryLoop = Number.isFinite( context.queryId );

	useEffect(
		() => setAttributes( { isDescendentOfQueryLoop } ),
		[ setAttributes, isDescendentOfQueryLoop ]
	);

	const isBlockThemeEnabled = getSettingWithCoercion(
		'is_block_theme_enabled',
		false,
		isBoolean
	);

	useEffect( () => {
		if ( isBlockThemeEnabled && attributes.imageSizing !== 'full-size' ) {
			setAttributes( { imageSizing: 'full-size' } );
		}
	}, [ attributes.imageSizing, isBlockThemeEnabled, setAttributes ] );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Content', 'woocommerce' ) }
				>
					<ToggleControl
						label={ __(
							'Link to Product Page',
							'woocommerce'
						) }
						help={ __(
							'Links the image to the single product listing.',
							'woocommerce'
						) }
						checked={ showProductLink }
						onChange={ () =>
							setAttributes( {
								showProductLink: ! showProductLink,
							} )
						}
					/>
					<ToggleControl
						label={ __(
							'Show On-Sale Badge',
							'woocommerce'
						) }
						help={ __(
							'Display a “sale” badge if the product is on-sale.',
							'woocommerce'
						) }
						checked={ showSaleBadge }
						onChange={ () =>
							setAttributes( {
								showSaleBadge: ! showSaleBadge,
							} )
						}
					/>
					{ showSaleBadge && (
						<ToggleButtonControl
							label={ __(
								'Sale Badge Alignment',
								'woocommerce'
							) }
							value={ saleBadgeAlign }
							options={ [
								{
									label: __(
										'Left',
										'woocommerce'
									),
									value: 'left',
								},
								{
									label: __(
										'Center',
										'woocommerce'
									),
									value: 'center',
								},
								{
									label: __(
										'Right',
										'woocommerce'
									),
									value: 'right',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { saleBadgeAlign: value } )
							}
						/>
					) }
					{ ! isBlockThemeEnabled && (
						<ToggleButtonControl
							label={ __(
								'Image Sizing',
								'woocommerce'
							) }
							help={ createInterpolateElement(
								__(
									'Product image cropping can be modified in the <a>Customizer</a>.',
									'woocommerce'
								),
								{
									a: (
										// eslint-disable-next-line jsx-a11y/anchor-has-content
										<a
											href={ `${ getAdminLink(
												'customize.php'
											) }?autofocus[panel]=woocommerce&autofocus[section]=woocommerce_product_images` }
											target="_blank"
											rel="noopener noreferrer"
										/>
									),
								}
							) }
							value={ imageSizing }
							onChange={ ( value ) =>
								setAttributes( { imageSizing: value } )
							}
							options={ [
								{
									label: __(
										'Full Size',
										'woocommerce'
									),
									value: 'full-size',
								},
								{
									label: __(
										'Cropped',
										'woocommerce'
									),
									value: 'cropped',
								},
							] }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<Block { ...{ ...attributes, ...context } } />
			</Disabled>
		</div>
	);
};

export default Edit;
