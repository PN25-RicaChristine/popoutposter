//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload, InspectorControls } = wp.blockEditor;
const { TextControl, PanelBody } = wp.components;
/**
 * Register our block
 */
registerBlockType( 'lw/end-of-page-cta', {
	title: __( 'End of Page CTA' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		label1: {
			type: 'array',
			source: 'children',
			selector: 'div.label1',
		},
		label2: {
			type: 'array',
			source: 'children',
			selector: 'div.label2',
		},
		label3: {
			type: 'array',
			source: 'children',
			selector: 'div.label3',
		},
		text1: {
			type: 'array',
			source: 'children',
			selector: 'p.text1',
		},
		text2: {
			type: 'array',
			source: 'children',
			selector: 'p.text2',
		},
		text3: {
			type: 'array',
			source: 'children',
			selector: 'p.text3',
		},
		imgUrl1: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/custom.svg'
		},
		imgUrl2: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/help.svg'
		},
		imgUrl3: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/guarantee.svg'
		},
		block1Href: {
			type: 'string',
			default : '#'
		},
		block2Href: {
			type: 'string',
			default : '#'
		},
		block3Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { label1, label2, label3, text1, text2, text3, imgUrl1, imgUrl2, imgUrl3, block1Href, block2Href, block3Href }, setAttributes, className } = props

		return (
			<div>
				<InspectorControls>
					<PanelBody
						title={ __( 'Buttons', 'mapps-blocks' ) }
						initialOpen={ true }
					>
						<TextControl
							label="Block 1 Link"
							value={ block1Href }
							onChange={ ( block1Href ) => setAttributes( { block1Href } ) }
						/>
						<TextControl
							label="Block 2 Link"
							value={ block2Href }
							onChange={ ( block2Href ) => setAttributes( { block2Href } ) }
						/>
						<TextControl
							label="Block 3 Link"
							value={ block3Href }
							onChange={ ( block3Href ) => setAttributes( { block3Href } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<div className="block-row block-row-separator">
					<div className="container">
						<div
							className="flex flex-col md:flex-row justify-center items-center md:items-center md:justify-center">

							<div
								className="w-full lg:w-1/3 p-16 text-center relative border-r">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl1: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											className="inline mb-8 w-16 border-none"
											src={imgUrl1}
											onClick={open}
										/>;
									}}
								/>

								<RichText
									tagName="div"
									className="label1 mb-2 font-idea-header"
									onChange={( label1 ) => {
										setAttributes( { label1 } )
									}}
									value={label1}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="text1 mb-2 px-8"
									onChange={( text1 ) => {
										setAttributes( { text1 } )
									}}
									value={text1}
									placeholder="Add text"
								/>

							</div>

							<div
								className="w-full lg:w-1/3 p-16 text-center relative border-r">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl2: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											className="inline mb-8 w-16 border-none"
											src={imgUrl2}
											onClick={open}
										/>;
									}}
								/>

								<RichText
									tagName="div"
									className="label2 mb-2 font-idea-header"
									onChange={( label2 ) => {
										setAttributes( { label2 } )
									}}
									value={label2}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="text2 mb-2 px-8"
									onChange={( text2 ) => {
										setAttributes( { text2 } )
									}}
									value={text2}
									placeholder="Add text"
								/>

							</div>

							<div
								className="w-full lg:w-1/3 p-16 text-center relative">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl3: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											className="inline mb-8 w-16 border-none"
											src={imgUrl3}
											onClick={open}
										/>;
									}}
								/>

								<RichText
									tagName="div"
									className="label3 mb-2 font-idea-header"
									onChange={( label3 ) => {
										setAttributes( { label3 } )
									}}
									value={label3}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="text3 mb-2 px-8"
									onChange={( text3 ) => {
										setAttributes( { text3 } )
									}}
									value={text3}
									placeholder="Add text"
								/>

							</div>

						</div>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { label1, label2, label3, text1, text2, text3, imgUrl1, imgUrl2, imgUrl3, block1Href, block2Href, block3Href} } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div
						className="flex flex-col md:flex-row justify-center items-center md:items-center md:justify-center">

						<div
							className="w-full lg:w-1/3 p-16 text-center relative border-r">
							<a href={block1Href} className="absolute w-full h-full inset-0"></a>
							<img
								alt=""
								className="inline mb-8 w-16 border-none"
								src={imgUrl1}
							/>

							<RichText.Content
								tagName="div"
								className="label1 mb-2 font-idea-header"
								value={label1}
							/>

							<RichText.Content
								tagName="p"
								className="text1 mb-2 px-8"
								value={text1}
							/>

						</div>

						<div
							className="w-full lg:w-1/3 p-16 text-center relative border-r">
							<a href={block2Href}  className="absolute w-full h-full inset-0"></a>
							<img
								alt=""
								className="inline mb-8 w-16 border-none"
								src={imgUrl2}
							/>

							<RichText.Content
								tagName="div"
								className="label2 mb-2 font-idea-header"
								value={label2}
							/>

							<RichText.Content
								tagName="p"
								className="text2 mb-2 px-8"
								value={text2}
							/>

						</div>

						<div
							className="w-full lg:w-1/3 p-16 text-center relative">
							<a href={block3Href}  className="absolute w-full h-full inset-0"></a>
							<img
								alt=""
								className="inline mb-8 w-16 border-none"
								src={imgUrl3}
							/>

							<RichText.Content
								tagName="div"
								className="label3 mb-2 font-idea-header"
								value={label3}
							/>

							<RichText.Content
								tagName="p"
								className="text3 mb-2 px-8"
								value={text3}
							/>


						</div>

					</div>
				</div>
			</div>
		)
	},
} )
