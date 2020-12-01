<?php
/**
 * General integration for Managed WooCommerce sites.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;

class WooCommerce extends Integration {
	use HasHooks;

	/**
	 * @var int
	 */
	protected $requestStartTime;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mwch_site;
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			[ 'woocommerce_background_image_regeneration', '__return_false' ],
		];
	}
}
