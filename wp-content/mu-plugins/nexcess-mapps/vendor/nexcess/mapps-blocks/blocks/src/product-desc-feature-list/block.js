//  Import CSS.
import './editor.scss'
import './style.scss'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText} = wp.blockEditor

/**
 * Register our block
 */
registerBlockType('lw/product-desc-feature-list', {
	title: __('Product Description Feature List'),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'h2.header',
		},
		content: {
			type: 'array',
			source: 'children',
			selector: 'div.content'
		},
		secondaryContent: {
			type: 'array',
			source: 'children',
			selector: 'div.secondary-content'
		},
	},

	edit (props) {
		const {attributes: {header, content, secondaryContent}, setAttributes, className} = props

		return (
			<div className={`${className} block-row block-row-separator`}>
				<div className="container">

					<RichText
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						onChange={header => setAttributes({header})}
						value={header}
						placeholder="Add a subheader"
					/>

					<div className="flex flex-col md:flex-row">
						<RichText
							tagName="div"
							className="content w-full lg:w-2/3 lg:mr-16 mb-8 lg:mb-0"
							onChange={content => setAttributes({content})}
							value={content}
							placeholder="Add content"
						/>
						<RichText
							tagName="div"
							className="secondary-content w-full lg:w-1/3"
							onChange={secondaryContent => setAttributes({secondaryContent})}
							value={secondaryContent}
							placeholder="Add secondary content"
						/>
					</div>
				</div>
			</div>
		)

	},

	save ({attributes}) {
		const {header, content, secondaryContent} = attributes

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						value={header}
					/>

					<div className="flex flex-col md:flex-row">
						<RichText.Content
							tagName="div"
							className="content w-full lg:w-2/3 lg:mr-16 mb-8 lg:mb-0"
							value={content}
						/>
						<RichText.Content
							tagName="div"
							className="secondary-content w-full lg:w-1/3"
							value={secondaryContent}
						/>
					</div>
				</div>
			</div>
		)
	},
})
