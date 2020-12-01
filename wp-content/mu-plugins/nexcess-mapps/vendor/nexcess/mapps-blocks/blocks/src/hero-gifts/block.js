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
registerBlockType( 'lw/hero-gifts', {
	title: __( 'Hero Gifts' ),
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
		content: {
			type: 'array',
			source: 'children',
			selector: 'div.content',
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
			default : 'Shop Gifts'
		},
		button1Href: {
			type: 'string',
			default : '#'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, content, backgroundImage, bgImageProps, bgImageID, button1Text, button1Href }, setAttributes } = props;

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
				<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col py-8 lg:pt-32 lg:pb-56" style={{backgroundImage: `url(${backgroundImage})`}}>
					<div className="container py-0">
						<div className="lg:w-1/2 lg:ml-auto lg:pr-8">
							<RichText
								tagName="div"
								className="header font-header font-medium leading-none text-4xl lg:text-5xl text-center lg:text-right color-hero"
								onChange={(header) => {
									setAttributes({header})
								}}
								value={header}
								placeholder="Add a heading"
							/>

							<RichText
								tagName="div"
								className="subheader font-light leading-none text-2xl lg:text-3xl text-center lg:text-right color-hero"
								onChange={(subheader) => {
									setAttributes({subheader})
								}}
								value={subheader}
								placeholder="Add a subheading"
							/>

							<RichText
								tagName="div"
								className="content text-white font-light leading-tight text-lg lg:text-xl mt-4 lg:mt-8 text-center lg:text-right"
								onChange={(content) => {
									setAttributes({content})
								}}
								value={content}
								placeholder="Add text"
							/>

							<div className="mt-4 lg:mt-12 text-center lg:text-right">
								<a href={button1Href} className="button">{button1Text}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	},

	save: props => {
		const { attributes: { header, subheader, content, backgroundImage, button1Text, button1Href } } = props;

		return (
			<div className="bg-hero bg-no-repeat bg-cover bg-center flex flex-col py-8 lg:pt-32 lg:pb-56" style={{backgroundImage: `url(${backgroundImage})`}}>
				<div className="container py-0">
					<div className="lg:w-1/2 lg:ml-auto lg:pr-8">
						<RichText.Content
							tagName="div"
							className="header font-header font-medium leading-none text-4xl lg:text-5xl text-center lg:text-right color-hero"
							value={header}
						/>

						<RichText.Content
							tagName="div"
							className="subheader font-light leading-none text-2xl lg:text-3xl text-center lg:text-right color-hero"
							value={subheader}
						/>

						<RichText.Content
							tagName="div"
							className="content text-white font-light leading-tight text-lg lg:text-xl mt-4 lg:mt-8 text-center lg:text-right"
							value={content}
						/>
						<div className="mt-4 lg:mt-12 text-center lg:text-right">
							<a href={button1Href} className="button">{button1Text}</a>
						</div>
					</div>
				</div>
			</div>
			);
	},
} );
