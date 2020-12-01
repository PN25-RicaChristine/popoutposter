<?php
/**
 * Modify site behavior for staging sites.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasDashboardNotices;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\ManagesWpConfig;
use Nexcess\MAPPS\Support\AdminNotice;

use const Nexcess\MAPPS\PLUGIN_VERSION;

class StagingSites extends Integration {
	use HasDashboardNotices;
	use HasHooks;
	use ManagesWpConfig;

	/**
	 * Used to indicate that the wp-config.php file has already been updated.
	 *
	 * @var string
	 */
	const WP_CONFIG_UPDATED = 'NEXCESS_MAPPS_STAGING_SITE';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_staging_site;
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->addHooks();

		$this->addDashboardNotice( new AdminNotice(
			__( 'You are currently in a staging environment.', 'nexcess-mapps' ),
			'warning',
			false,
			'staging-notice'
		), 1 );
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			// Block robots, regardless of the blog_public option.
			[ 'pre_option_blog_public', '__return_zero' ],

			// Don't send an email when the admin email changes.
			[ 'send_email_change_email', '__return_false' ],
		];
	}

	/**
	 * Update the wp-config.php file to prevent collisions with real sites.
	 */
	public function updateWpConfig() {
		// Only update the config once per release.
		if (
			defined( self::WP_CONFIG_UPDATED )
			&& version_compare( constant( self::WP_CONFIG_UPDATED ), PLUGIN_VERSION, '>=' )
		) {
			return;
		}

		// Explicitly override constants in the wp-config.php file.
		$this->setConfigConstant( 'WP_ENVIRONMENT_TYPE', 'staging' );
		$this->setConfigConstant( 'JETPACK_STAGING_MODE', true );
		$this->setConfigConstant( 'WP_CACHE_KEY_SALT', uniqid( 'staging-site-' ) );

		/*
		 * Finally, flag the file as having been updated to prevent this method from running on
		 * subsequent requests.
		 */
		$this->setConfigConstant( self::WP_CONFIG_UPDATED, PLUGIN_VERSION );
	}
}
