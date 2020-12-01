//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/special-coupon', {
	title: __( 'Special Coupon' ),
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
			selector: 'div.subheader'
		},
		coupon_code: {
			type: 'array',
			source: 'children',
			selector: 'div.coupon_code'
		},
	},

	edit: props => {
		const { attributes: { header, subheader, coupon_code }, setAttributes, className } = props

		return (
			<div className="block-row py-0">
				<div className="special-coupon-bg bg-cover bg-bottom pt-8 pb-8 lg:pt-20 lg:pb-48">
					<div className="container py-0">

						<div className="flex flex-col md:flex-row">
							<div className="w-full lg:w-1/3 lg:pt-12 text-center lg:text-left mb-8 lg:mb-0">

								<RichText
									tagName="div"
									className="header font-header text-3xl lg:text-4xl leading-tight text-black"
									onChange={( header ) => {
										setAttributes( { header } )
									}}
									value={header}
									placeholder="Add a heading"
								/>

								<RichText
									tagName="div"
									className="subheader font-header text-xl lg:text-2xl leading-tight mb-4 text-black"
									onChange={( subheader ) => {
										setAttributes( { subheader } )
									}}
									value={subheader}
									placeholder="Add a subheading"
								/>

								<RichText
									tagName="div"
									className="coupon_code font-bold text-black"
									onChange={( coupon_code ) => {
										setAttributes( { coupon_code } )
									}}
									value={coupon_code}
									placeholder="Add a coupon code"
								/>

							</div>
						</div>

					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header, subheader, coupon_code } } = props

		return (
			<div className="block-row py-0">
				<div className="special-coupon-bg bg-cover bg-bottom pt-8 pb-8 lg:pt-20 lg:pb-48">
					<div className="container py-0">

						<div className="flex flex-col md:flex-row">
							<div className="w-full lg:w-1/3 lg:pt-12 text-center lg:text-left mb-8 lg:mb-0">

								<RichText.Content
									tagName="div"
									className="header font-header text-4xl lg:text-5xl leading-tight text-black"
									value={header}
								/>

								<RichText.Content
									tagName="div"
									className="subheader font-header text-2xl lg:text-3xl leading-tight mb-4 text-black"
									value={subheader}
								/>

								<RichText.Content
									tagName="div"
									className="coupon_code font-bold text-black"
									value={coupon_code}
								/>

							</div>
						</div>

					</div>
				</div>
			</div>
		)
	},
} )
