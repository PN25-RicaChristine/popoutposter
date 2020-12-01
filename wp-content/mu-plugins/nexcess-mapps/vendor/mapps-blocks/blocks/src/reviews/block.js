//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { MediaUpload } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/reviews', {
	title: __( 'Reviews' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		imgUrl0: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-cnet.svg'
		},
		imgUrl1: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-engadget.svg'
		},
		imgUrl2: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-wired.svg'
		},
		imgUrl3: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-gizmodo.svg'
		},
		imgUrl4: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-people.svg'
		},
		imgUrl5: {
			type: 'string',
			default: '/wp-content/plugins/mapps-blocks/assets/images/logo-gq.svg'

		},
	},

	edit: props => {
		const { attributes: { imgUrl0, imgUrl1, imgUrl2, imgUrl3, imgUrl4, imgUrl5 }, setAttributes } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="flex justify-center items-center">

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl0: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl0}
									onClick={open}
								/>;
							}}
						/>

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl1: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl1}
									onClick={open}
								/>;
							}}
						/>

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl2: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl2}
									onClick={open}
								/>;
							}}
						/>

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl3: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl3}
									onClick={open}
								/>;
							}}
						/>

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl4: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl4}
									onClick={open}
								/>;
							}}
						/>

						<MediaUpload
							onSelect={ (media) => setAttributes({imgUrl5: media.sizes.full.url }) }

							render={ ({open}) => {
								return <img
									className="w-32 px-4 border-none"
									src={imgUrl5}
									onClick={open}
								/>;
							}}
						/>

					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { imgUrl0, imgUrl1, imgUrl2, imgUrl3, imgUrl4, imgUrl5  } } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="flex justify-center items-center">

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl0}
						/>

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl1}
						/>

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl2}
						/>

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl3}
						/>

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl4}
						/>

						<img
							alt=""
							className="w-32 px-4 border-none"
							src={imgUrl5}
						/>

					</div>
				</div>
			</div>
		)
	},
} )
