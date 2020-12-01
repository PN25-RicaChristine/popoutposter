<?php
/**
 * Modifications to the WordPress Site Health tool.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\HasWordPressDependencies;

class SiteHealth extends Integration {
	use HasHooks;
	use HasWordPressDependencies;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& $this->siteIsAtLeastWordPressVersion( '5.2' );
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			[ 'site_status_tests', [ $this, 'filterStatusChecks' ], 1 ],
		];
	}

	/**
	 * Modify the Site Health checks based on environment.
	 *
	 * @param array[] $checks Currently-registered checks.
	 *
	 * @return array[] The potentially-filtered list of checks.
	 */
	public function filterStatusChecks( $checks ) {
		/*
		 * If automatic updates are enabled on the Nexcess platform, we will be disabling the
		 * automatic updates processed by WordPress core. However, WordPress itself doesn't know
		 * about this, so the Site Health tool will report an error about updates being disabled.
		 */
		if ( $this->settings->mapps_core_updates_enabled ) {
			unset( $checks['async']['background_updates'] );
		}

		/**
		 * Similarly, if the platform is handling plugin updates, don't warn the user.
		 */
		if ( $this->settings->mapps_plugin_updates_enabled ) {
			unset( $checks['direct']['plugin_theme_auto_updates'] );
		}

		/*
		 * Non-production environments should be *encouraged* to enable WP_DEBUG, not shamed for
		 * doing so.
		 */
		if ( ! $this->settings->is_production_site ) {
			unset( $checks['direct']['debug_enabled'] );
		}

		return $checks;
	}
}
