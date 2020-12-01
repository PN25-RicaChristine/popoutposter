//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { RichText } = wp.blockEditor;

/**
 * Register our block
 */
registerBlockType( 'lw/hero', {
	title: __( 'Hero' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		content: {
			type: 'array',
			source: 'children',
			selector: 'h2',
		},
	},

	edit: props => {
		const { attributes: { content }, setAttributes, className } = props;

		const onChangeContent = ( newContent ) => {
			setAttributes( { content: newContent } );
		};

		return (
			<div className="bg-color-main-500 text-white text-center p-10 lg:p-20">
				<RichText
					tagName="h2"
					className={`${className} text-white my-0 text-2xl font-header`}
					onChange={ onChangeContent }
					value={ content }
				/>
			</div>
		);
	},

	save: props => {
		const { attributes: { content } } = props;

		return (
			<div className="bg-color-main-500 text-white text-center p-10 lg:p-20 ">
				<RichText.Content
					className="text-white bg-color-main-500 p-10 text-center text-2xl font-header"
					tagName="h2"
					value={ content }
				/>
			</div>
			);
	},
} );
