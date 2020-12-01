//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const {RichText, InspectorControls, MediaUpload} = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/return-policy', {
	title: __( 'Return Policy' ),
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
			selector: 'p.subheader'
		},
		imgUrl: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/guarantee.png'
		}
	},

	edit: props => {
		const { attributes: { header, subheader, imgUrl }, setAttributes, className } = props

		function selectImage(value) {
			setAttributes({
				imgUrl: value.sizes.full.url,
			})
		}

		return (
			<div className="block-row block-row-separator">
				<div class="container text-center">
					<div class="flex justify-center">
						<MediaUpload
							onSelect={selectImage}
							render={ ({open}) => {
								return <img
									class="w-32 border-none"
									src={imgUrl}
									onClick={open}
								/>;
							}}
						/>
					</div>

					<RichText
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mt-4 mb-8"
						onChange={( header ) => {
							setAttributes( { header } )
						}}
						value={header}
						placeholder="Add a heading"
					/>

					<RichText
						tagName="p"
						className="subheader"
						onChange={( subheader ) => {
							setAttributes( { subheader } )
						}}
						value={subheader}
						placeholder="Add a subheading"
					/>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header, subheader, imgUrl } } = props

		return (
			<div className="block-row block-row-separator">
				<div class="container text-center">
					<div class="flex justify-center">
						<img
							alt=""
							class="w-32 border-none"
							src={imgUrl} />
					</div>

					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mt-4 mb-8"
						value={header}
					/>

					<RichText.Content
						tagName="p"
						className="subheader"
						value={subheader}
					/>
				</div>
			</div>
		)
	},
} )
