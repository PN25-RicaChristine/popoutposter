//  Import CSS.
import './editor.scss'
import './style.scss'

const {__} = wp.i18n // Import __() from wp.i18n
const {registerBlockType} = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText, InspectorControls, MediaUpload} = wp.blockEditor

/**
 * Register our block
 */
registerBlockType('lw/case-studies', {
	title: __('Case Studies'),
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
		imgUrl: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/case-study.jpg'
		}
	},

	edit: props => {
		const {attributes: {header, subheader, imgUrl}, setAttributes} = props

		function selectImage(value) {
			setAttributes({
				imgUrl: value.sizes.full.url,
			})
		}

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="flex flex-col md:flex-row justify-center items-center py-8 md:pb-0">
						<div className="w-full lg:w-1/2 mb-8 md:mb-0 text-center md:text-left">
							<RichText
								tagName="div"
								className="header lg:mb-8 text-3xl lg:text-4xl leading-tight"
								onChange={(header)=>{
									setAttributes({header})
								}}
								value={header}
								placeholder="Add a heading"
							/>

							<RichText
								tagName="div"
								className="subheader text-xl lg:text-2xl leading-tight"
								onChange={(subheader) => {
									setAttributes({subheader})
								}}
								value={subheader}
								placeholder="Add a subheading"
							/>
						</div>
						<div className="w-full lg:w-1/2 text-center md:text-right ">
							<MediaUpload
								onSelect={selectImage}
								render={ ({open}) => {
									return <img
										className="rounded-full inline md:w-3/4 border border-gray-500"
										src={imgUrl}
										onClick={open}
									/>;
								}}
							/>
						</div>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const {attributes: {header, subheader, imgUrl}} = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="flex flex-col md:flex-row justify-center items-center py-8 md:pb-0">
						<div className="w-full lg:w-1/2 mb-8 md:mb-0 text-center md:text-left">
							<RichText.Content
								tagName="div"
								className="header lg:mb-8 text-3xl lg:text-4xl leading-tight"
								value={header}
							/>

							<RichText.Content
								tagName="div"
								className="subheader text-xl lg:text-2xl leading-tight"
								value={subheader}
							/>
						</div>
						<div className="w-full lg:w-1/2 text-center md:text-right ">
							<img className="rounded-full inline md:w-3/4 border border-gray-500"
									 src={imgUrl} />
						</div>
					</div>
				</div>
			</div>
		)
	},
})
