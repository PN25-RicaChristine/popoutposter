//  Import CSS.
import './editor.scss'
import './style.scss'

const { __ } = wp.i18n // Import __() from wp.i18n
const { registerBlockType } = wp.blocks // Import registerBlockType() from wp.blocks
const { RichText } = wp.blockEditor

/**
 * Register our block
 */
registerBlockType( 'lw/tweets', {
	title: __( 'Tweets' ),
	icon: 'shield',
	category: 'common',
	attributes: {
		header: {
			type: 'array',
			source: 'children',
			selector: 'h2.header',
		},
	},

	edit: props => {
		const { attributes: { header }, setAttributes, className } = props

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
					/>

					<ul className="grid grid-cols-1 md:grid-cols-3 gap-8">
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">Moving a site from GoDaddy
								Managed WP to <a href="https://twitter.com/LiquidWeb?ref_src=twsrc%5Etfw">@LiquidWeb</a>.
								The migration tool is super easy.</p>&mdash; Angie Meeker (@angiemeeker) <a
								href="https://twitter.com/angiemeeker/status/1230303055322796032?ref_src=twsrc%5Etfw">February
								20, 2020</a></blockquote>
						</li>
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">It seems I&#39;m giving <a
								href="https://twitter.com/LiquidWeb?ref_src=twsrc%5Etfw">@LiquidWeb</a> Kudos every
								day! So special mention to Chris K. I needed help with a new server build, so LW had a
								tech
								answer my questions to make it work. Really, above and beyond you don&#39;t see with any
								other hosting provider.</p>&mdash; Web242 (@Web242ca) <a
								href="https://twitter.com/Web242ca/status/1224515173290954752?ref_src=twsrc%5Etfw">February
								4, 2020</a></blockquote>
						</li>
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">The <a
								href="https://twitter.com/BeaverBuilder?ref_src=twsrc%5Etfw">@BeaverBuilder</a>
								team rolled out an update to <a
									href="https://twitter.com/hashtag/BeaverThemer?src=hash&amp;ref_src=twsrc%5Etfw">#BeaverThemer</a>
								and every time I use it I love it. It makes it easy for me to do a little
								bit of work and delight customers a lot!</p>&mdash; Chris Lema (@chrislema)
								<a href="https://twitter.com/chrislema/status/1230262539335847936?ref_src=twsrc%5Etfw">February
									19, 2020</a></blockquote>
						</li>
					</ul>
				</div>
			</div>
		)

	},

	save: props => {
		const { attributes: { header } } = props

		return (
			<div className="block-row block-row-separator">
				<div className="container">
					<RichText.Content
						tagName="h2"
						className="header font-header text-3xl lg:text-4xl text-center mb-8"
						value={header}
					/>

					<ul className="grid grid-cols-1 md:grid-cols-3 gap-8">
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">Moving a site from GoDaddy
								Managed WP to <a href="https://twitter.com/LiquidWeb?ref_src=twsrc%5Etfw">@LiquidWeb</a>.
								The migration tool is super easy.</p>&mdash; Angie Meeker (@angiemeeker) <a
								href="https://twitter.com/angiemeeker/status/1230303055322796032?ref_src=twsrc%5Etfw">February
								20, 2020</a></blockquote>
						</li>
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">It seems I&#39;m giving <a
								href="https://twitter.com/LiquidWeb?ref_src=twsrc%5Etfw">@LiquidWeb</a> Kudos every
								day! So special mention to Chris K. I needed help with a new server build, so LW had a
								tech
								answer my questions to make it work. Really, above and beyond you don&#39;t see with any
								other hosting provider.</p>&mdash; Web242 (@Web242ca) <a
								href="https://twitter.com/Web242ca/status/1224515173290954752?ref_src=twsrc%5Etfw">February
								4, 2020</a></blockquote>
						</li>
						<li>
							<blockquote className="twitter-tweet"><p lang="en" dir="ltr">The <a
								href="https://twitter.com/BeaverBuilder?ref_src=twsrc%5Etfw">@BeaverBuilder</a>
								team rolled out an update to <a
									href="https://twitter.com/hashtag/BeaverThemer?src=hash&amp;ref_src=twsrc%5Etfw">#BeaverThemer</a>
								and every time I use it I love it. It makes it easy for me to do a little
								bit of work and delight customers a lot!</p>&mdash; Chris Lema (@chrislema)
								<a href="https://twitter.com/chrislema/status/1230262539335847936?ref_src=twsrc%5Etfw">February
									19, 2020</a></blockquote>
						</li>
					</ul>
				</div>
			</div>
		)
	},
} )
