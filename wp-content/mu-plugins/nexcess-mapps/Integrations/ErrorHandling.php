<?php
/**
 * Display the current environment type in the Admin Bar.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\HasWordPressDependencies;
use Nexcess\MAPPS\Concerns\ManagesDropIns;

class ErrorHandling extends Integration {
	use HasHooks;
	use HasWordPressDependencies;
	use ManagesDropIns;

	/**
	 * The flag set once the error handler has been installed once.
	 */
	const FLAG_NAME = 'fatal-error-handler-installed';

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
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		return [
			[ 'admin_init', [ $this, 'installDropIn' ] ],
		];
	}

	/**
	 * Install the fatal-error-handler.php drop-in.
	 *
	 * Since customers may not always want our custom handler, we'll keep track of whether or not
	 * we've installed it once and, if so, never try to install it again.
	 */
	public function installDropIn() {
		if ( $this->settings->getFlag( self::FLAG_NAME, false ) ) {
			return;
		}

		$this->symlinkDropIn(
			'fatal-error-handler.php',
			dirname( __DIR__ ) . '/DropIns/fatal-error-handler.php'
		);

		// Set the flag so we don't try to re-install this once removed.
		$this->settings->setFlag( self::FLAG_NAME, true );
	}
}
