//  Import CSS.
import './editor.scss'
import './style.scss'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText, MediaUpload, InspectorControls} = wp.blockEditor
const { TextControl, PanelBody, Button, ResponsiveWrapper, Spinner } = wp.components;

/**
 * Register our block
 */
registerBlockType('lw/email-sign-up', {
	title: __('Email Sign Up'),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'div.email-sign-up-header',
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
		block1Href: {
			type: 'string',
			default : '#'
		},
		block1Text: {
			type: 'string',
			default : 'Sign Up'
		},
	},

	edit(props) {
		const {attributes: {header, cta, block1Href, block1Text, backgroundImage, bgImageProps, bgImageID}, setAttributes, className} = props
		console.log(backgroundImage);
		function onImageSelect(imageObject) {
			console.log(imageObject);
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
							label="Sign Up Link Text"
							value={ block1Text }
							onChange={ ( block1Text ) => setAttributes( { block1Text } ) }
						/>
						<TextControl
							label="Sign Up Link"
							value={ block1Href }
							onChange={ ( block1Href ) => setAttributes( { block1Href } ) }
						/>
					</PanelBody>
				</InspectorControls>
				<div
					className="block-row bg-indigo-200 bg-cover"
					style={{
						height: '500px',
						backgroundImage: `url(${backgroundImage})`
					}}
				>
					<div className="container py-0">
						<div className="container h-full flex justify-center items-center">
							<div
								className="px-12 py-6 bg-color-black-25"
							>
								<div className="text-center">
									<RichText
										tagName="div"
										className="email-sign-up-header text-white font-header text-3xl lg:text-4xl mb-8"
										onChange={header => setAttributes({header})}
										value={header}
										placeholder="Heading"
									/>
									<a href={block1Href} className="button button-cta">{block1Text}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		)

	},

	save(props) {
		const {attributes: {header, cta, backgroundImage,  block1Href, block1Text}} = props

		return (
			<div
				className="block-row bg-indigo-200 bg-cover py-0"
				style={{
					height: '500px',
					backgroundImage: `url(${backgroundImage})`
				}}
			>
				<div className="container">
					<div className="container h-full flex justify-center items-center">
						<div
							className="px-12 py-6 bg-color-black-25"
						>
							<div className="text-center">
								<RichText.Content
									tagName="div"
									className="email-sign-up-header text-white font-header text-3xl lg:text-4xl mb-8"
									value={header}
								/>
								<a href={block1Href} className="button button-cta">{block1Text}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		)
	},
})
