//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText, InspectorControls, MediaUpload} = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/tech-specs', {
	title: __( 'Tech Specs' ),
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
		label4: {
			type: 'array',
			source: 'children',
			selector: 'div.label4'
		},
		label5: {
			type: 'array',
			source: 'children',
			selector: 'div.label5'
		},
		label6: {
			type: 'array',
			source: 'children',
			selector: 'div.label6'
		},
		data1: {
			type: 'array',
			source: 'children',
			selector: 'div.data1'
		},
		data2: {
			type: 'array',
			source: 'children',
			selector: 'div.data2'
		},
		data3: {
			type: 'array',
			source: 'children',
			selector: 'div.data3'
		},
		data4: {
			type: 'array',
			source: 'children',
			selector: 'div.data4'
		},
		data5: {
			type: 'array',
			source: 'children',
			selector: 'div.data5'
		},
		data6: {
			type: 'array',
			source: 'children',
			selector: 'div.data6'
		},
		imgUrl: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/tech-specs.png'
		}
	},

	edit: props => {
		const { attributes: { header, label1, data1, label2, data2, label3, data3, label4, data4, label5, data5, label6, data6, imgUrl }, setAttributes } = props

		function selectImage(value) {
			setAttributes({
				imgUrl: value.sizes.full.url,
			})
		}


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

					<div className="border-t border-gray-800 mt-8 flex flex-col lg:flex-row justify-between pt-6">

						<div className="lg:w-2/5 mb-8 lg:mb-0">
							<MediaUpload
								onSelect={selectImage}
								render={ ({open}) => {
									return <img
										className="w-100 border-none"
										src={imgUrl}
										onClick={open}
									/>;
								}}
							/>
						</div>

						<div className="lg:w-3/5 lg:pl-20">
							<table className="w-full">
								<tbody>
								<tr>
									<td width="30%">
										<RichText
											tagName="div"
											className="label1 font-bold"
											onChange={( label1 ) => {
												setAttributes( { label1 } )
											}}
											value={label1}
											placeholder="Add title"
										/>
									</td>
									<td width="70%">
										<RichText
											tagName="div"
											className="data1"
											onChange={( data1 ) => {
												setAttributes( { data1 } )
											}}
											value={data1}
											placeholder="Add text"
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText
											tagName="div"
											className="label2 font-bold"
											onChange={( label2 ) => {
												setAttributes( { label2 } )
											}}
											value={label2}
											placeholder="Add title"
										/>
									</td>
									<td>
										<RichText
											tagName="div"
											className="data2"
											onChange={( data2 ) => {
												setAttributes( { data2 } )
											}}
											value={data2}
											placeholder="Add text"
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText
											tagName="div"
											className="label3 font-bold"
											onChange={( label3 ) => {
												setAttributes( { label3 } )
											}}
											value={label3}
											placeholder="Add title"
										/>
									</td>
									<td>
										<RichText
											tagName="div"
											className="data3"
											onChange={( data3 ) => {
												setAttributes( { data3 } )
											}}
											value={data3}
											placeholder="Add text"
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText
											tagName="div"
											className="label4 font-bold"
											onChange={( label4 ) => {
												setAttributes( { label4 } )
											}}
											value={label4}
											placeholder="Add title"
										/>
									</td>
									<td>
										<RichText
											tagName="div"
											className="data4"
											onChange={( data4 ) => {
												setAttributes( { data4 } )
											}}
											value={data4}
											placeholder="Add text"
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText
											tagName="div"
											className="label5 font-bold"
											onChange={( label5 ) => {
												setAttributes( { label5 } )
											}}
											value={label5}
											placeholder="Add title"
										/>
									</td>
									<td>
										<RichText
											tagName="div"
											className="data5"
											onChange={( data5 ) => {
												setAttributes( { data5 } )
											}}
											value={data5}
											placeholder="Add text"
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText
											tagName="div"
											className="label6 font-bold"
											onChange={( label6 ) => {
												setAttributes( { label6 } )
											}}
											value={label6}
											placeholder="Add title"
										/>
									</td>
									<td>
										<RichText
											tagName="div"
											className="data6"
											onChange={( data6 ) => {
												setAttributes( { data6 } )
											}}
											value={data6}
											placeholder="Add text"
										/>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>

				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header, label1, data1, label2, data2, label3, data3, label4, data4, label5, data5, label6, data6, imgUrl } } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">

					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						value={header}
					/>

					<div className="border-t border-gray-800 mt-8 flex flex-col lg:flex-row justify-between pt-6">

						<div className="lg:w-2/5 mb-8 lg:mb-0">
							<img className="w-100 border-none"
								 src={imgUrl} />
						</div>

						<div className="lg:w-3/5 lg:pl-20">
							<table className="w-full border-0">
								<tbody>
								<tr>
									<td width="30%">
										<RichText.Content
											tagName="div"
											className="label1 font-bold"
											value={label1}
										/>
									</td>
									<td width="70%">
										<RichText.Content
											tagName="div"
											className="data1"
											value={data1}
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText.Content
											tagName="div"
											className="label2 font-bold"
											value={label2}
										/>
									</td>
									<td>
										<RichText.Content
											tagName="div"
											className="data2"
											value={data2}
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText.Content
											tagName="div"
											className="label3 font-bold"
											value={label3}
										/>
									</td>
									<td>
										<RichText.Content
											tagName="div"
											className="data3"
											value={data3}
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText.Content
											tagName="div"
											className="label4 font-bold"
											value={label4}
										/>
									</td>
									<td>
										<RichText.Content
											tagName="div"
											className="data4"
											value={data4}
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText.Content
											tagName="div"
											className="label5 font-bold"
											value={label5}
										/>
									</td>
									<td>
										<RichText.Content
											tagName="div"
											className="data5"
											value={data5}
										/>
									</td>
								</tr>
								<tr>
									<td>
										<RichText.Content
											tagName="div"
											className="label6 font-bold"
											value={label6}
										/>
									</td>
									<td>
										<RichText.Content
											tagName="div"
											className="data6"
											value={data6}
										/>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>

				</div>

			</div>
		)
	},
} )
