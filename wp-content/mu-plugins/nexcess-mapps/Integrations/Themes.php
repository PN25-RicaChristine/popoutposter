<?php
/**
 * Customizations to themes.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;

class Themes extends Integration {
	use HasHooks;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site;
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			[ 'bsf_white_label_options', [ $this, 'whiteLabelAstra' ] ],
		];
	}

	/**
	 * Flag Astra as white-labeled.
	 *
	 * If any of the bsf_white_label_options array is TRUE, Astra will disable its usage tracking
	 * and related nags.
	 *
	 * @param mixed[] $options White-label options for Astra.
	 */
	public function whiteLabelAstra( $options ) {
		return array_merge( [
			'mapps' => true,
		], $options );
	}
}
