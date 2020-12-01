<?php
/**
 * Functionality related to Nexcess support.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasAdminPages;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Support\Helpers;

class Support extends Integration {
	use HasAdminPages;
	use HasHooks;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration should be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site;
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		return [
			[ 'admin_init', [ $this, 'registerSupportSection' ], 100 ],
		];
	}

	/**
	 * Register the "Support" settings section.
	 */
	public function registerSupportSection() {

		/**
		 * Allow the support template section to be completely disabled.
		 *
		 * @param boolean $maybe_enabled Passing a "false" will disable this template call completely.
		 */
		$maybe_enabled = apply_filters( 'nexcess_mapps_branding_enable_support_template', true );

		if ( false === $maybe_enabled ) {
			return;
		}

		add_settings_section(
			'support',
			_x( 'Support', 'settings section', 'nexcess-mapps' ),
			function () {
				$this->renderTemplate( 'support', [
					'details'  => $this->getSupportDetails(),
					'settings' => $this->settings,
				] );
			},
			Dashboard::ADMIN_MENU_SLUG
		);
	}

	/**
	 * Retrieve an array of support details.
	 *
	 * @return mixed[] Details that should be provided in the support details section of the
	 *                 Nexcess MAPPS dashboard.
	 */
	protected function getSupportDetails() {
		$details = [
			'Account ID'       => $this->settings->account_id,
			'Package'          => $this->settings->package_label,
			'Plan Name'        => $this->settings->plan_name,
			'Plan Type'        => $this->settings->plan_type,
			'PHP Version'      => $this->settings->php_version,
			'WP_DEBUG enabled' => Helpers::getEnabled( defined( 'WP_DEBUG' ) && WP_DEBUG ),
		];

		/**
		 * Filter the details displayed on the Nexcess MAPPS dashboard.
		 *
		 * @param array<string,mixed> An array of details, keyed by their label.
		 *
		 * phpcs:disable WordPress.NamingConventions.ValidHookName
		 */
		return apply_filters( 'Nexcess\\MAPPS\\support_details', $details );
		// phpcs:enable WordPress.NamingConventions.ValidHookName
	}
}
