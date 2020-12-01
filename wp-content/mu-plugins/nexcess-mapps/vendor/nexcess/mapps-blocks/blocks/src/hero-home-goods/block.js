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
registerBlockType( 'lw/hero-home-goods', {
	title: __( 'Hero Home Goods' ),
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
			default : 'Shop Linens'
		},
		button1Href: {
			type: 'string',
			default : '#'
		},
		button2Text: {
			type: 'string',
			default : 'Shop Furniture'
		},
		button2Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href, button2Text, button2Href }, setAttributes } = props;

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
											naturalWidth={ bgImageProps.sizes.full.width }
											naturalHeight={ bgImageProps.sizes.full.height }
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
				<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col pt-6 lg:pt-56 pb-6 lg:pb-8" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="container py-0">
						<div className="lg:w-1/2 lg:pr-8 shadow-2xl p-6 bg-color-hero">
							<RichText
								tagName="div"
								className="header text-white font-header leading-none text-left text-4xl lg:text-5xl"
								onChange={(header) => {
									setAttributes({header})
								}}
								value={header}
								placeholder="Add a heading"
							/>

							<RichText
								tagName="div"
								className="subheader text-white font-subheader leading-tight text-left mt-4"
								onChange={(subheader) => {
									setAttributes({subheader})
								}}
								value={subheader}
								placeholder="Add a subheading"
							/>

							<div className="mt-4">
								<a href={button1Href} className="button w-full lg:w-auto">{button1Text}</a>
								<a href={button2Href} className="button w-full lg:w-auto mt-4 lg:ml-4">{button2Text}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	},

	save: props => {
		const { attributes: { header, subheader,  backgroundImage, button1Text, button1Href, button2Text, button2Href } } = props;

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col pt-6 lg:pt-56 pb-6 lg:pb-8" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="container py-0">
					<div className="lg:w-1/2 lg:pr-8 shadow-2xl p-6 bg-color-hero">
						<RichText.Content
							tagName="div"
							className="header text-white font-header leading-none text-left text-4xl lg:text-5xl"
							value={header}
						/>

						<RichText.Content
							tagName="div"
							className="subheader text-white font-subheader leading-tight text-left mt-4"
							value={subheader}
						/>

						<div className="mt-4">
							<a href={button1Href} className="button w-full lg:w-auto">{button1Text}</a>
							<a href={button2Href} className="button w-full lg:w-auto mt-4 lg:ml-4">{button2Text}</a>
						</div>
					</div>
				</div>
			</div>
			);
	},
} );
