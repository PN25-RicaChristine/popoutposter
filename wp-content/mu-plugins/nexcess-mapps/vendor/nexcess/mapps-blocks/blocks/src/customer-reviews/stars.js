/**
 * Simple star component
 */
const Stars = () => (
	<ul className="flex justify-center">
		{
			[...Array(4)].map(index => (
				<li key={`star-${index}`} className="review-star mx-3">
				</li>
			))
		}
	</ul>
)

export default Stars
