<?php
/**
 * Security-related enhancements.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Stubs\Freemius;

class Security extends Integration {
	use HasHooks;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& ! self::isPluginActive( 'wp-fail2ban/wp-fail2ban.php' )
			&& ! self::isPluginBeingActivated( 'wp-fail2ban/wp-fail2ban.php' )
			&& ! self::isMuPluginInstalled( 'wp-fail2ban' );
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 *
	 * @global $wf_fs
	 */
	public function setup() {
		global $wf_fs;

		// Short-circuit the Freemius integration.
		$wf_fs = new Freemius();

		$this->loadPlugin( 'wpackagist-plugin/wp-fail2ban/wp-fail2ban.php' );
		$this->addHooks();
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'admin_menu',                 [ $this, 'removeAdminMenu'       ], 100 ],
			[ 'wp_dashboard_setup',         [ $this, 'removeDashboardWidget' ], 1   ],
			[ 'wp_network_dashboard_setup', [ $this, 'removeDashboardWidget' ], 1   ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Remove the WP-fail2ban menu.
	 */
	public function removeAdminMenu() {
		remove_menu_page( 'wp-fail2ban' );

		// Changed in 4.3: https://plugins.trac.wordpress.org/changeset/2285182/
		remove_menu_page( 'wp-fail2ban-menu' );
	}

	/**
	 * Remove the WP-fail2ban dashboard widget, introduced in 4.3.
	 */
	public function removeDashboardWidget() {
		remove_action( current_action(), 'org\lecklider\charles\wordpress\wp_fail2ban\wp_dashboard_setup' );
	}
}
