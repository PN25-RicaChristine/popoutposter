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
registerBlockType( 'lw/hero-health-beauty', {
	title: __( 'Hero Health & Beauty' ),
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
			default : 'Shop our Products'
		},
		button1Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href }, setAttributes } = props;

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
				<div className="bg-hero bg-no-repeat bg-cover bg-top flex flex-col pb-8 lg:pb-64" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="container py-12">
						<div className="max-w-xl ml-auto">
							<div className="p-1 bg-color-hero">
								<div className="px-16 py-8 border-green-900 border">

									<RichText
										tagName="div"
										className="header text-green-900 font-header text-center leading-none lg:text-3xl font-bold"
										onChange={(header) => {
											setAttributes({header})
										}}
										value={header}
										placeholder="Add a heading"
									/>

									<RichText
										tagName="div"
										className="subheader text-green-900 text-xl text-center leading-tight font-bold"
										onChange={(subheader) => {
											setAttributes({subheader})
										}}
										value={subheader}
										placeholder="Add a subheading"
									/>


									<div className="flex justify-around mt-4 lg:mt-4">
										<a href={button1Href} className="button shadow-lg">{button1Text}</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	},

	save: props => {
		const { attributes: { header, subheader, backgroundImage, button1Text, button1Href } } = props;

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-top flex flex-col pb-8 lg:pb-64" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="container py-12">
					<div className="max-w-xl ml-auto">
						<div className="p-1 bg-color-hero">
							<div className="px-16 py-8 border-green-900 border">
								<RichText.Content
									tagName="div"
									className="header text-green-900 font-header text-center leading-none lg:text-3xl font-bold"
									value={header}
								/>

								<RichText.Content
									tagName="div"
									className="subheader text-green-900 text-xl text-center leading-tight font-bold"
									value={subheader}
								/>
								<div className="flex justify-around mt-4 lg:mt-4">
									<a href={button1Href} className="button shadow-lg">{button1Text}</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			);
	},
} );
