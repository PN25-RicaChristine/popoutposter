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
registerBlockType('lw/hero-electronics', {
	title: __('Hero Electronics'),
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
			selector: 'div.subheader',
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
			default : 'Shop Electronics'
		},
		button1Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const {attributes: {header, subheader, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href}, setAttributes} = props

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
							label="Button Text"
							value={ button1Text }
							onChange={ ( button1Text ) => setAttributes( { button1Text } ) }
						/>
						<TextControl
							label="Button Link"
							value={ button1Href }
							onChange={ ( button1Href ) => setAttributes( { button1Href } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col lg:pt-2 lg:pb-48" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="container">
						<div className="lg:w-1/2 lg:pr-8">
							<RichText
								tagName="div"
								className="header font-header leading-none text-left text-5xl lg:text-6xl color-hero"
								onChange={(header) => {
									setAttributes({header})
								}}
								value={header}
								placeholder="Add a heading"
							/>
							<RichText
								tagName="div"
								className="subheader font-subheader leading-tight text-left mt-4 text-lg lg:text-xl color-hero"
								onChange={(subheader) => {
									setAttributes({subheader})
								}}
								value={subheader}
								placeholder="Add a subheading"
							/>
							<div className="text-left mt-10">
								<a href={button1Href} className="button text-2xl">{button1Text}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	},

	save: props => {
		const {attributes: {header, subheader, backgroundImage, button1Text, button1Href}} = props

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col lg:pt-2 lg:pb-48" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="container">
					<div className="lg:w-1/2 lg:pr-8">
						<RichText.Content
							tagName="div"
							className="header font-header leading-none text-left text-5xl lg:text-6xl color-hero"
							value={header}
						/>
						<RichText.Content
							tagName="div"
							className="subheader font-subheader leading-tight text-left mt-4 text-lg lg:text-xl color-hero"
							value={subheader}
						/>
						<div className="text-left mt-10">
							<a href={button1Href} className="button text-2xl">{button1Text}</a>
						</div>
					</div>
				</div>
			</div>
		)
	},
})
