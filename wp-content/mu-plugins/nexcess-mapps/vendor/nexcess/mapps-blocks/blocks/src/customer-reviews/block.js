//  Import CSS.
import './editor.scss'
import './style.scss'

// Import internal dependencies
import Stars from './stars'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText, InnerBlocks} = wp.blockEditor

const attributes = {
	header: {
		type: `array`,
		source: `children`,
		selector: `h2.review-header`,
	}
}

/**
 * Register our block
 */
registerBlockType('lw/customer-reviews', {
	title: __('Customer Reviews'),
	icon: 'shield',
	category: 'common',
	attributes,

	edit: props => {
		const {attributes, setAttributes} = props

		return (
			<div className="block-row block-row-separator customer-reviews-block-editor">
				<div className="container">
					<RichText
						tagName="h2"
						className="review-header font-header text-3xl lg:text-4xl text-center mb-4"
						onChange={header => {
							setAttributes({header})
						}}
						value={attributes.header}
						placeholder="Add a title"
					/>
					<Stars/>
					<ul className="">
						<InnerBlocks
							allowedBlocks={['lw/review']}
						/>

					</ul>
					<Stars/>
				</div>
			</div>
		)

	},

	save: props => {
		const {attributes} = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<RichText.Content
						tagName="h2"
						className="review-header font-header text-3xl lg:text-4xl text-center mb-4"
						value={attributes.header}
					/>
					<Stars/>
					<ul className="my-8 grid grid-cols-1 md:grid-cols-2 gap-10">
						<InnerBlocks.Content />
					</ul>
					<Stars/>
				</div>
			</div>
		)
	},
})
