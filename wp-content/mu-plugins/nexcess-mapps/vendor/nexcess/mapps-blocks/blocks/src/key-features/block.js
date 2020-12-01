//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText, MediaUpload } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/key-features', {
	title: __( 'Key Features' ),
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
		label1: {
			type: 'array',
			source: 'children',
			selector: 'h3.label1'
		},
		label2: {
			type: 'array',
			source: 'children',
			selector: 'h3.label2'
		},
		label3: {
			type: 'array',
			source: 'children',
			selector: 'h3.label3'
		},
		label4: {
			type: 'array',
			source: 'children',
			selector: 'h3.label4'
		},
		content1: {
			type: 'array',
			source: 'children',
			selector: 'p.content1'
		},
		content2: {
			type: 'array',
			source: 'children',
			selector: 'p.content2'
		},
		content3: {
			type: 'array',
			source: 'children',
			selector: 'p.content3'
		},
		content4: {
			type: 'array',
			source: 'children',
			selector: 'p.content4'
		},
		imgUrl1: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/feature-1.jpg'
		},
		imgUrl2: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/feature-2.jpg'
		},
		imgUrl3: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/feature-3.jpg'
		},
		imgUrl4: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/feature-4.jpg'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, label1, label2, label3, label4, content1, content2, content3, content4, imgUrl1, imgUrl2, imgUrl3, imgUrl4 }, setAttributes, className } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-4"
						onChange={( header ) => {
							setAttributes( { header } )
						}}
						value={header}
						placeholder="Add a heading"
					/>

					<RichText
						tagName="div"
						className="subheader font-subheader text-xl lg:text-2xl text-center leading-tight"
						onChange={( subheader ) => {
							setAttributes( { subheader } )
						}}
						value={subheader}
						placeholder="Add a subheading"
					/>

					<div className="lg:px-12 mt-6 lg:mt-12">
						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl1: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											src={imgUrl1}
											onClick={open}
										/>;
									}}
								/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText
									tagName="h3"
									className="label1 font-idea-header"
									onChange={( label1 ) => {
										setAttributes( { label1 } )
									}}
									value={label1}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="content1"
									onChange={( content1 ) => {
										setAttributes( { content1 } )
									}}
									value={content1}
									placeholder="Add content"
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl2: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											src={imgUrl2}
											onClick={open}
										/>;
									}}
								/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText
									tagName="h3"
									className="label2 font-idea-header"
									onChange={( label2 ) => {
										setAttributes( { label2 } )
									}}
									value={label2}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="content2"
									onChange={( content2 ) => {
										setAttributes( { content2 } )
									}}
									value={content2}
									placeholder="Add content"
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl3: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											src={imgUrl3}
											onClick={open}
										/>;
									}}
								/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText
									tagName="h3"
									className="label3 font-idea-header"
									onChange={( label3 ) => {
										setAttributes( { label3 } )
									}}
									value={label3}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="content3"
									onChange={( content3 ) => {
										setAttributes( { content3 } )
									}}
									value={content3}
									placeholder="Add content"
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<MediaUpload
									onSelect={ (media) => setAttributes({imgUrl4: media.sizes.full.url }) }

									render={ ({open}) => {
										return <img
											src={imgUrl4}
											onClick={open}
										/>;
									}}
								/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText
									tagName="h3"
									className="label4 font-idea-header"
									onChange={( label4 ) => {
										setAttributes( { label4 } )
									}}
									value={label4}
									placeholder="Add a title"
								/>

								<RichText
									tagName="p"
									className="content4"
									onChange={( content4 ) => {
										setAttributes( { content4 } )
									}}
									value={content4}
									placeholder="Add content"
								/>

							</div>

						</div>

					</div>
				</div>
			</div>

		)

	},

	save: props => {
		const { attributes: { header, subheader, label1, label2, label3, label4, content1, content2, content3, content4, imgUrl1, imgUrl2, imgUrl3, imgUrl4 } } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-4"
						value={header}
					/>

					<RichText.Content
						tagName="div"
						className="subheader font-subheader text-xl lg:text-2xl text-center leading-tight"
						value={subheader}
					/>

					<div className="lg:px-12 mt-6 lg:mt-12">
						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<img src={imgUrl1} alt=""/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText.Content
									tagName="h3"
									className="label1 font-idea-header"
									value={label1}
								/>

								<RichText.Content
									tagName="p"
									className="content1"
									value={content1}
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<img src={imgUrl2} alt=""/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText.Content
									tagName="h3"
									className="label2 font-idea-header"
									value={label2}
								/>

								<RichText.Content
									tagName="p"
									className="content2"
									value={content2}
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12 border-b border-gray-500">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<img src={imgUrl3} alt=""/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText.Content
									tagName="h3"
									className="label3 font-idea-header"
									value={label3}
								/>

								<RichText.Content
									tagName="p"
									className="content3"
									value={content3}
								/>

							</div>

						</div>

						<div className="flex flex-col lg:flex-row mb-8 lg:mb-12 pb-6 lg:pb-12">

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16">
								<img src={imgUrl4} alt=""/>
							</div>

							<div className="lg:w-1/2 lg:mx-8 lg:mx-16 mt-6 lg:mt-0">

								<RichText.Content
									tagName="h3"
									className="label4 font-idea-header"
									value={label4}
								/>

								<RichText.Content
									tagName="p"
									className="content4"
									value={content4}
								/>

							</div>

						</div>

					</div>
				</div>
			</div>
		)
	},
} )
