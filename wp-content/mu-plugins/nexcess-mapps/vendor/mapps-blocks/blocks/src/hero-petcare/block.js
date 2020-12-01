//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload, InspectorControls } = wp.blockEditor;
const { TextControl, PanelBody, Button, ResponsiveWrapper } = wp.components;

/**
 * Register our block
 */
registerBlockType( 'lw/hero-petcare', {
	title: __( 'Hero Petcare' ),
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
			default : 'Cats'
		},
		button1Href: {
			type: 'string',
			default : '#'
		},
		button2Text: {
			type: 'string',
			default : 'Dogs'
		},
		button2Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href, button2Text, button2Href  }, setAttributes } = props;

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
				<div className="bg-hero bg-no-repeat bg-cover bg-center flex justify-center p-4 lg:pt-64 lg:pb-6" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="flex flex-col justify-center items-center">
						<div className="px-16 py-8 mt-48 bg-color-hero">
							<RichText
								tagName="div"
								className="header text-white font-header text-center leading-none lg:text-5xl"
								onChange={(header) => {
									setAttributes({header})
								}}
								value={header}
								placeholder="Add a heading"
							/>

							<RichText
								tagName="div"
								className="subheader text-white lg:text-2xl text-center leading-tight"
								onChange={(subheader) => {
									setAttributes({subheader})
								}}
								value={subheader}
								placeholder="Add a subheading"
							/>

						</div>
						<div className="flex justify-end mt-4 lg:mt-4">
							<a href={button1Href} className="bg-color-1 button text-white">{button1Text}</a>
							<a href={button2Href} className="bg-color-2 button ml-6">{button2Text}</a>
						</div>
					</div>

				</div>
			</div>
		);
	},

	save: props => {
		const { attributes: { header, subheader, backgroundImage, button1Text, button1Href, button2Text, button2Href } } = props;

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-center flex justify-center p-4 lg:pt-64 lg:pb-6" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="flex flex-col justify-center items-center">

					<div className="px-16 py-8 mt-48 bg-color-hero">
						<RichText.Content
							tagName="div"
							className="header text-white font-header text-center leading-none lg:text-5xl"
							value={header}
						/>

						<RichText.Content
							tagName="div"
							className="subheader text-white lg:text-2xl text-center leading-tight"
							value={subheader}
						/>
					</div>
					<div className="flex justify-end mt-4 lg:mt-4">
						<a href={button1Href} className="bg-color-1 button text-white">{button1Text}</a>
						<a href={button2Href} className="bg-color-2 button ml-6">{button2Text}</a>
					</div>
				</div>

			</div>
			);
	},
} );
