//  Import CSS.
import './editor.scss'
import './style.scss'
import { products } from './products'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText} = wp.blockEditor

const attributes = {}
products.forEach(product => {
	attributes[`${product.id}Title`] = {
		type: 'array',
		source: 'children',
		selector: `h3.${product.id}-title`
	}

	attributes[`${product.id}Price`] = {
		type: 'array',
		source: 'children',
		selector: `div.${product.id}-price`
	}
})

/**
 * Register our block
 */
registerBlockType('lw/catalog-display', {
	title: __('Catalog Display'),
	icon: 'shield',
	category: 'common',
	attributes,

	edit: props => {
		const {attributes, setAttributes, className} = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<ul className="grid grid-cols-1 sm:grid-cols-3 row-gap-16 col-gap-8">
						{products.map(({image, id}) => (
							<li key={id} className="text-center">
								<img
									alt=""
									className="mb-4 w-full border-frontend"
									src={image}
								/>
								<RichText
									tagName="h3"
									className={`${id}-title mb-2 leading-none font-thin text-xl`}
									onChange={title => {
										setAttributes({[`${id}Title`]: title})
									}}
									value={attributes[`${id}Title`]}
								/>
								<RichText
									tagName="div"
									className={`${id}-price font-bold mb-2`}
									onChange={price => {
										setAttributes({[`${id}Price`]: price})
									}}
									value={attributes[`${id}Price`]}
								/>
								<a href="#" className="button button-buy">Buy Now</a>
							</li>
						))}
					</ul>
				</div>
			</div>
		)

	},

	save: props => {
		const {attributes} = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<ul className="grid grid-cols-1 sm:grid-cols-3 row-gap-16 col-gap-8">
						{products.map(({image, id}) => (
							<li key={id} className="text-center">
								<img
									alt=""
									className="mb-4 w-full border-frontend"
									src={image}
								/>
								<RichText.Content
									tagName="h3"
									className={`${id}-title mb-2 leading-none font-thin text-xl`}
									value={attributes[`${id}Title`]}
								/>
								<RichText.Content
									tagName="div"
									className={`${id}-price font-bold mb-2`}
									value={attributes[`${id}Price`]}
								/>
								<a href="#" className="button button-buy">Buy Now</a>
							</li>
						))}
					</ul>
				</div>
			</div>
		)
	},
})
