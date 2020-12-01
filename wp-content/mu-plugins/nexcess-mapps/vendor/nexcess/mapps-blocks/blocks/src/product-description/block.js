//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/product-description', {
	title: __( 'Product Description' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'h2.header',
		},
		label1: {
			type: 'array',
			source: 'children',
			selector: 'div.label1'
		},
		label2: {
			type: 'array',
			source: 'children',
			selector: 'div.label2'
		},
		label3: {
			type: 'array',
			source: 'children',
			selector: 'div.label3'
		},
		data1: {
			type: 'array',
			source: 'children',
			selector: 'p.data1'
		},
		data2: {
			type: 'array',
			source: 'children',
			selector: 'p.data2'
		},
		data3: {
			type: 'array',
			source: 'children',
			selector: 'p.data3'
		},
		imgUrl1: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/product-features.jpg'
		}
	},

	edit: props => {
		const { attributes: { header, label1, data1, label2, data2, label3, data3, imgUrl1}, setAttributes, className } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						onChange={( header ) => {
							setAttributes( { header } )
						}}
						value={header}
						placeholder="Add a heading"
					/>

					<div className="flex flex-col lg:flex-row justify-center items-center">

						<div className="w-full md:w-1/2 lg:pr-8 mb-8 lg:mb-0">
							<MediaUpload
								onSelect={ (media) => setAttributes({imgUrl1: media.sizes.full.url }) }
								render={ ({open}) => {
									return <img
										className="border-frontend"
										src={imgUrl1}
										onClick={open}
									/>;
								}}
							/>
						</div>

						<div className="w-full md:w-1/2 lg:pl-8">

							<div className="mb-8 md:mb-12 lg:mb-16">

								<RichText
									tagName="div"
									className="label1 font-idea-header"
									onChange={( label1 ) => {
										setAttributes( { label1 } )
									}}
									value={label1}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="data1 mt-2 mb-0"
									onChange={( data1 ) => {
										setAttributes( { data1 } )
									}}
									value={data1}
									placeholder="Add text"
								/>

							</div>

							<div className="mb-8 md:mb-12 lg:mb-16">

								<RichText
									tagName="div"
									className="label2 font-idea-header"
									onChange={( label2 ) => {
										setAttributes( { label2 } )
									}}
									value={label2}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="data2 mt-2 mb-0"
									onChange={( data2 ) => {
										setAttributes( { data2 } )
									}}
									value={data2}
									placeholder="Add text"
								/>

							</div>

							<div>

								<RichText
									tagName="div"
									className="label3 font-idea-header"
									onChange={( label3 ) => {
										setAttributes( { label3 } )
									}}
									value={label3}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="data3 mt-2 mb-0"
									onChange={( data3 ) => {
										setAttributes( { data3 } )
									}}
									value={data3}
									placeholder="Add text"
								/>

							</div>

						</div>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header, label1, data1, label2, data2, label3, data3, imgUrl1 } } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						value={header}
					/>

					<div className="flex flex-col lg:flex-row justify-center items-center">

						<div className="w-full md:w-1/2 lg:pr-8 mb-8 lg:mb-0">
							<img className="border-frontend"
							     src={imgUrl1} />
						</div>

						<div className="w-full md:w-1/2 lg:pl-8">

							<div className="mb-8 md:mb-12 lg:mb-16">

								<RichText.Content
									tagName="div"
									className="label1 font-idea-header"
									value={label1}
								/>

								<RichText.Content
									tagName="p"
									className="data1 mt-2 mb-0"
									value={data1}
								/>

							</div>

							<div className="mb-8 md:mb-12 lg:mb-16">

								<RichText.Content
									tagName="div"
									className="label2 font-idea-header"
									value={label2}
								/>

								<RichText.Content
									tagName="p"
									className="data2 mt-2 mb-0"
									value={data2}
								/>

							</div>

							<div>

								<RichText.Content
									tagName="div"
									className="label3 font-idea-header"
									value={label3}
								/>

								<RichText.Content
									tagName="p"
									className="data3 mt-2 mb-0"
									value={data3}
								/>

							</div>

						</div>
					</div>
				</div>
			</div>
		)
	},
} )
