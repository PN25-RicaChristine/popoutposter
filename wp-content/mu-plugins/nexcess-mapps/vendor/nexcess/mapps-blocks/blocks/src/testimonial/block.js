//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { Button } = wp.components;

/**
 * Register our block
 */
registerBlockType( 'lw/testimonial', {
	title: __( 'Testimonial' ),
	icon: 'format-quote',
	category: 'common',
	attributes: {
		content: {
			type: 'array',
			source: 'children',
			selector: 'div.testimonial',
		},
		name: {
			type: 'array',
			source: 'children',
			selector: 'div.name',
		},
		description: {
			type: 'array',
			source: 'children',
			selector: 'div.description',
		},
		mediaID: {
			type: 'number'
		},
		mediaURL: {
			type: 'string',
			source: 'attribute',
			selector: 'img',
			attribute: 'src'
		},
		imgUrl: {
			type: 'string',
			default: 'https://randomuser.me/api/portraits/med/men/67.jpg'
		}
	},

	edit: props => {
		const { attributes: { content, name, description, imgUrl  }, setAttributes, className } = props;

		const onChangeContent = ( newContent ) => {
			setAttributes( { content: newContent } );
		};

		function selectImage(value) {
			setAttributes({
				imgUrl: value.sizes.full.url,
			})
		}


		return (
			<div className="block-row block-row-separator">
				<div className="container text-center">
					<RichText
						tagName="div"
						className={`${className} testimonial max-w-xl mx-auto mb-4 color-main-500 text-2xl font-header`}
						onChange={( content ) => {
							setAttributes( { content } )
						}}
						value={content}
						placeholder="Add testimony"
					/>
					<MediaUpload
						onSelect={selectImage}
						render={ ({open}) => {
							return <img
								className="rounded-full mx-auto mb-2 w-32"
								src={imgUrl}
								onClick={open}
							/>;
						}}
					/>
					<RichText
						tagName="div"
						className="name uppercase font-bold"
						onChange={( name ) => {
							setAttributes( { name } )
						}}
						value={name}
						placeholder="John Doe"
					/>

					<RichText
						tagName="div"
						className="description"
						onChange={( description ) => {
							setAttributes( { description } )
						}}
						value={description}
						placeholder="WordPress Developer"
					/>
				</div>
			</div>
		);
	},

	save: props => {
		const { attributes: { content, name, description, imgUrl  } } = props;

		return (
			<div className="block-row block-row-separator">
				<div className="container text-center">
					<RichText.Content
						className="testimonial max-w-xl mx-auto mb-4 color-main-500 text-2xl font-header"
						tagName="div"
						value={content}
					/>
					<img
						alt=""
						className="rounded-full mx-auto mb-2 shadow w-32"
						 src={imgUrl} />
					<RichText.Content
						className="name uppercase font-bold"
						tagName="div"
						value={name}
					/>
					<RichText.Content
						className="description"
						tagName="div"
						value={description}
					/>
				</div>
			</div>
		);
	},
} );
