<?php
/**
 * Display the current environment type in the Admin Bar.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasWordPressDependencies;

class DisplayEnvironment extends Integration {
	use HasWordPressDependencies;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& $this->siteIsAtLeastWordPressVersion( '5.5' )
			&& ! self::isPluginActive( 'display-environment-type/display-environment-type.php' )
			&& ! self::isPluginBeingActivated( 'display-environment-type/display-environment-type.php' )
			&& apply_filters( 'nexcess_mapps_enable_environment_indicator', true );
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->loadPlugin( 'wpackagist-plugin/display-environment-type/display-environment-type.php' );
	}
}
