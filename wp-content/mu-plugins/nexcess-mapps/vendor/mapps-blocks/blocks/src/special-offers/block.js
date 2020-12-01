//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText, InspectorControls } = wp.blockEditor
const { TextControl, PanelBody } = wp.components;

/**
 * Register our block
 */
registerBlockType( 'lw/special-offers', {
	title: __( 'Special Offers' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'div.header',
		},
		subheader: {
			type: 'array',
			source: 'children',
			selector: 'div.subheader'
		},
		button1Text: {
			type: 'string',
			default : 'See our Products'
		},
		button1Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, button1Text, button1Href }, setAttributes, className } = props

		return (
			<div>
				<InspectorControls>
					<PanelBody
						title={ __( 'Buttons', 'mapps-blocks' ) }
						initialOpen={ true }
					>
						<TextControl
							label="Block 1 Link"
							value={ button1Text }
							onChange={ ( button1Text ) => setAttributes( { button1Text } ) }
						/>
						<TextControl
							label="Block 2 Link"
							value={ button1Href }
							onChange={ ( button1Href ) => setAttributes( { button1Href } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<div className="block-row">
					<div className="special-offers-bg bg-cover lg:pt-20 lg:pb-48">
						<div className="container py-0">
							<div
								className="flex flex-col md:flex-row justify-center items-center md:items-end md:justify-start py-8 md:pb-0">
								<div className="text-center md:text-left m-6">
									<RichText
										tagName="div"
										className="header lg:mb-0 text-2xl lg:text-4xl leading-tight"
										onChange={( header ) => {
											setAttributes( { header } )
										}}
										value={header}
										placeholder="Add a heading"
									/>

									<RichText
										tagName="div"
										className="subheader lg:text-lg leading-tight"
										onChange={( subheader ) => {
											setAttributes( { subheader } )
										}}
										value={subheader}
										placeholder="Add a subheading"
									/>

									<a href={button1Href} className="button mt-8">{button1Text}</a>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header, subheader, button1Text, button1Href } } = props

		return (
			<div className="block-row">
				<div className="special-offers-bg bg-cover lg:pt-20 lg:pb-48">
					<div className="container py-0">
						<div
							className="flex flex-col md:flex-row justify-center items-center md:items-end md:justify-start py-8 md:pb-0">
							<div className="text-center md:text-left m-6">
								<RichText.Content
									tagName="div"
									className="header lg:mb-0 text-2xl lg:text-4xl leading-tight"
									value={header}
								/>

								<RichText.Content
									tagName="div"
									className="subheader lg:text-lg leading-tight"
									value={subheader}
								/>
								<a href={button1Href} className="button mt-8">{button1Text}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	},
} )
