<?php
/**
 * Integration with WooCommerce Upper Limits for WooCommerce beginner plans.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Settings;

class WooCommerceUpperLimits extends Integration {

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_beginner_plan
			&& self::isPluginActive( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->loadPlugin( 'liquidweb/woocommerce-upper-limits/woocommerce-upper-limits.php' );
	}
}
