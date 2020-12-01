//  Import CSS.
import './editor.scss'
import './style.scss'

import Posts from './posts'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText} = wp.blockEditor

/**
 * Register our block
 */
registerBlockType('lw/instagram', {
	title: __('Instagram'),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'h2.header',
		},
		subheader: {
			type: 'array',
			source: 'children',
			selector: 'div.subheader'
		},
	},

	edit (props) {
		const {attributes: {header, subheader}, setAttributes, className} = props

		return (
			<div className={`${className} block-row block-row-separator`}>
				<div className="container">
					<RichText
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center"
						onChange={header => setAttributes({header})}
						value={header}
						placeholder="Add a heading"
					/>
					<RichText
						tagName="div"
						className="subheader font-subheader text-xl lg:text-2xl text-center leading-tight mb-8"
						onChange={subheader => setAttributes({subheader})}
						value={subheader}
						placeholder="Add a subheading"
					/>
					<Posts/>
				</div>
			</div>
		)

	},

	save ({attributes}) {

		return (
			<div className={`block-row block-row-separator`}>
				<div className="container">
					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center"
						value={attributes.header}
					/>
					<RichText.Content
						tagName="div"
						className="subheader font-subheader text-xl lg:text-2xl text-center leading-tight mb-8"
						value={attributes.subheader}
					/>
					<Posts/>
				</div>
			</div>
		)
	},
})
