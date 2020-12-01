//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload, InspectorControls } = wp.blockEditor;
const { TextControl, PanelBody, Button, ResponsiveWrapper, Spinner } = wp.components;

/**
 * Register our block
 */
registerBlockType('lw/hero-apparel', {
	title: __('Hero Apparel'),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'div.header',
		},
		subhead: {
			type: 'array',
			source: 'children',
			selector: 'div.subheader'
		},
		backgroundImage: {
			type: 'string',
			default: null, // no image by default!
		},
		bgImageID: {
			type: 'number',
			default:null
		},
		bgImageProps: {
			type:'object',
			default:null
		},
		button1Text: {
			type: 'string',
			default : 'Shop Women'
		},
		button1Href: {
			type: 'string',
			default : '#'
		},
		button2Text: {
			type: 'string',
			default : 'Shop Women'
		},
		button2Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const {attributes: {header, subhead, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href, button2Text, button2Href}, setAttributes} = props

		function onImageSelect(imageObject) {
			setAttributes({
				bgImageID: imageObject.id,
				backgroundImage: imageObject.sizes.full.url,
				bgImageProps: imageObject
			})
		}

		return (
			<div>
				<InspectorControls>
					<PanelBody
						title={ __( 'Background settings', 'mapps-blocks' ) }
						initialOpen={ true }
					>
						<div>
							<MediaUpload
								title={ __( 'Background image', 'mapps-blocks' ) }
								onSelect={onImageSelect}
								type="image"
								value={bgImageID} // make sure you destructured backgroundImage from props.attributes!
								render={ ( { open } ) => (
									<Button
										className={ ! backgroundImage ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview' }
										onClick={ open }>
										{ ! backgroundImage && ( __( 'Set background image', 'mapps-blocks' ) ) }
										{ backgroundImage &&
										<ResponsiveWrapper
											naturalWidth={ bgImageProps.width }
											naturalHeight={ bgImageProps.height }
										>
											<img src={ backgroundImage } alt={ __( 'Background image', 'mapps-blocks' ) } />
										</ResponsiveWrapper>
										}
									</Button>
								) }
							/>
						</div>
					</PanelBody>
					<PanelBody
						title={ __( 'Buttons', 'mapps-blocks' ) }
						initialOpen={ true }
					>
							<TextControl
								label="Button 1 Text"
								value={ button1Text }
								onChange={ ( button1Text ) => setAttributes( { button1Text } ) }
							/>
							<TextControl
								label="Button 1 Link"
								value={ button1Href }
								onChange={ ( button1Href ) => setAttributes( { button1Href } ) }
							/>

							<TextControl
								label="Button 2 Text"
								value={ button2Text }
								onChange={ ( button2Text ) => setAttributes( { button2Text } ) }
							/>
							<TextControl
								label="Button 2 Link"
								value={ button2Href }
								onChange={ ( button2Href ) => setAttributes( { button2Href } ) }
							/>
					</PanelBody>
				</InspectorControls>
				<div className="bg-hero bg-no-repeat bg-cover bg-center flex content-center justify-center p-4 lg:py-48" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="flex flex-col justify-center items-center">
						<div className="border-2 p-1 border-color-smoke-80">
							<div className="px-12 py-6 bg-color-smoke-80">

								<RichText
									tagName="div"
									className="header text-gray-800 font-header text-center leading-none text-4xl lg:text-5xl"
									onChange={(header)=>{
										setAttributes({header})
									}}
									value={header}
									placeholder="Add a heading"
								/>


								<RichText
									tagName="div"
									className="subheader text-gray-800 font-subheader text-xl text-center leading-tight"
									onChange={(subhead) => {
										setAttributes({subhead})
									}}
									value={subhead}
									placeholder="Add a subheading"
								/>
							</div>
						</div>

						<div className="flex justify-around mt-4 lg:mt-12">
							<a href={button1Href}
							   className="button shadow-lg">{button1Text}</a>
							<a href={button2Href}
							   className="button shadow-lg ml-4">{button2Text}</a>
						</div>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const {attributes: {header, subhead, backgroundImage, button1Text, button1Href, button2Text, button2Href}} = props

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-center flex content-center justify-center p-4 lg:py-48" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="flex flex-col justify-center items-center">
					<div className="border-2 p-1 border-color-smoke-80">
						<div className="px-12 py-6 bg-color-smoke-80">
							<RichText.Content
								tagName="div"
								className="header text-gray-800 font-header text-center leading-none text-4xl lg:text-5xl"
								value={header}
							/>

							<RichText.Content
								tagName="div"
								className="subheader text-gray-800 font-subheader text-xl text-center leading-tight"
								value={subhead}
							/>
						</div>
					</div>
					<div className="flex justify-around mt-4 lg:mt-12">
						<a href={button1Href}
						   className="button shadow-lg">{button1Text}</a>
						<a href={button2Href}
						   className="button shadow-lg ml-4">{button2Text}</a>
					</div>
				</div>
			</div>
		)
	},
})
