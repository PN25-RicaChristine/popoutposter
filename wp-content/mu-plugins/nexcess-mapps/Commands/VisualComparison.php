<?php

namespace Nexcess\MAPPS\Commands;

use Nexcess\MAPPS\Integrations\VisualComparison as Integration;

use function cli\out;

/**
 * WP-CLI sub-commands for integrating with Visual Comparison.
 */
class VisualComparison {

	/**
	 * Retrieve a list of URLs to process during Visual Comparison.
	 *
	 * URLs will be returned as a flat object, with a key corresponding
	 * to the ID of the check.
	 *
	 * For example:
	 *
	 *     {
	 *       "homepage": "\/",
	 *       "single-page": "\/some-page\/"
	 *       "single-post": "\/blog\/some-post-slug\/",
	 *       "category-archive": "\/cat\/some-category\/"
	 *     }
	 *
	 * Returned URLs will be relative to the site root.
	 *
	 * ## EXAMPLES
	 *
	 *   wp nxmapps vc urls
	 *
	 * @subcommand urls
	 */
	public function getRegressionUrls() {
		$urls = [];

		foreach ( Integration::getUrls() as $url ) {
			$urls[ $url->getId() ] = $url->getPath();
		}

		// Filter URLs that return a response code other than 200.
		$urls = $this->filter_urls( array_unique( array_filter( $urls ) ), 200 );

		// Use out() to prevent --quiet from silencing output.
		out( wp_json_encode( $urls, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Filter URLs that do not match the response code.
	 *
	 * @param  string[]  $urls           URLs to filter.
	 * @param  int       $response_code  Response Code filter the list
	 *
	 * @return string[] An array of URLs
	 */
	protected function filter_urls( $urls, $response_code = 200 ) {
		return array_filter( $urls, function( $url ) use ( $response_code ) {
			$response = wp_remote_head( site_url( $url ), [
				'redirection' => 0,
			] );

			// phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
			return $response_code === wp_remote_retrieve_response_code( $response );
		});
	}
}
