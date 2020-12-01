//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText, InnerBlocks, Editable } = wp.blockEditor;
const { Button } = wp.components;

/**
 * Register our block
 */
registerBlockType( 'lw/products', {
	title: __( 'Products' ),
	icon: 'cart',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'h2.header',
		},
	},

	edit: props => {
		const { attributes: { header }, setAttributes, className } = props;

		return (
			<div className={`${className} container py-10 font-body`}>
				<RichText
					tagName="h2"
					className="header mb-6 text-2xl lg:text-4xl font-bold font-header text-center"
					onChange={( header ) => {
						setAttributes( { header } )
					}}
					value={header}
					placeholder="Add a heading"
				/>
				<ul>
					<InnerBlocks
						allowedBlocks={['lw/product']}
					/>
				</ul>
			</div>
		);
	},

	save: props => {
		const { attributes: { header } } = props;

		return (
			<div className="container py-10 font-body">
				<RichText.Content
					tagName="h2"
					className="header mb-6 text-2xl lg:text-4xl font-bold font-header text-center"
					value={header}
				/>
				<ul className="grid gap-10 grid-cols-1 md:grid-cols-3">
					<InnerBlocks.Content />
				</ul>
			</div>
		);
	},
} );
