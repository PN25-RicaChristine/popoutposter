<?php
/**
 * Perform regular maintenance.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasCronEvents;

class Maintenance extends Integration {
	use HasCronEvents;

	const DAILY_MAINTENANCE_CRON_ACTION  = 'nexcess_mapps_daily_maintenance';
	const WEEKLY_MAINTENANCE_CRON_ACTION = 'nexcess_mapps_weekly_maintenance';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site;
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->registerCronEvent( self::DAILY_MAINTENANCE_CRON_ACTION, 'daily' );
		$this->registerCronEvent( self::WEEKLY_MAINTENANCE_CRON_ACTION, 'weekly' );
	}
}
