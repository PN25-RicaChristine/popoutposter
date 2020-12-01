//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/video-embed', {
	title: __( 'Youtube Video Embed' ),
	icon: 'shield',
	category: 'common',
	attributes: {},

	edit: props => {
		const { attributes: {}, setAttributes, className } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="relative h-0" style={{paddingBottom:' 56.25%'}}>
						<iframe
							className="absolute top-0 left-0 w-full h-full"
							width="560"
							height="315"
							src="https://www.youtube.com/embed/wYrnkafl6Es?controls=0"
							frameBorder="0"
							allow="accelerometer; autoplay;encrypted-media; gyroscope; picture-in-picture"
							allowFullScreen
						></iframe>
					</div>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: {} } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<div className="relative h-0" style={{paddingBottom:' 56.25%'}}>
						<iframe
							className="absolute top-0 left-0 w-full h-full"
							width="560"
							height="315"
							src="https://www.youtube.com/embed/wYrnkafl6Es?controls=0"
							frameBorder="0"
							allow="accelerometer; autoplay;encrypted-media; gyroscope; picture-in-picture"
							allowFullScreen
						></iframe>
					</div>
				</div>
			</div>
		)
	},
} )
