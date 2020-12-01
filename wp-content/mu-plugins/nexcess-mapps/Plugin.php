<?php
/**
 * The main Nexcess Managed Apps plugin.
 *
 * This class is responsible for starting up services and loading integrations.
 */

namespace Nexcess\MAPPS;

use Nexcess\MAPPS\Concerns\QueriesWooCommerce;
use Nexcess\MAPPS\Contracts\DefinesConstants;
use Nexcess\MAPPS\Exceptions\IntegrationAlreadyLoadedException;
use Nexcess\MAPPS\Exceptions\IsNotNexcessSiteException;
use Nexcess\MAPPS\Integrations\Integration;
use WP_CLI;

class Plugin {

	/**
	 * The AdminBar instance.
	 *
	 * @var \Nexcess\MAPPS\AdminBar
	 */
	private $adminBar;

	/**
	 * Keep track of our integration instances.
	 *
	 * @var Integration[]
	 */
	private $integrations = [];

	/**
	 * The Settings instance.
	 *
	 * @var \Nexcess\MAPPS\Settings
	 */
	private $settings;

	/**
	 * Instantiate the class.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\IsNotNexcessSiteException if the site settings do not indicate
	 *                                                         a site on the Nexcess Managed Apps platform.
	 *
	 * @param \Nexcess\MAPPS\Settings $settings
	 * @param \Nexcess\MAPPS\AdminBar $admin_bar
	 */
	public function __construct( Settings $settings, AdminBar $admin_bar ) {
		$this->settings = $settings;
		$this->adminBar = $admin_bar;

		// Prevent additional checks if we're running WP-CLI.
		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			return;
		}

		// Abort if this is not an Nexcess Managed Apps site.
		if ( ! $this->settings->is_nexcess_site ) {
			throw new IsNotNexcessSiteException( 'Does not appear to be an Nexcess Managed Apps site.' );
		}
	}

	/**
	 * Add any hooks that need to be globally registered.
	 *
	 * Whenever possible, hooks should be registered within individual integrations.
	 */
	public function addGlobalHooks() {

		// Don't register these hooks if we're not on a MAPPS site.
		if ( ! $this->settings->is_mapps_site ) {
			return;
		}

		// Register the admin bar.
		add_action( 'init', [ $this->adminBar, 'register' ], PHP_INT_MAX );

		/*
		 * Define custom cron schedules.
		 *
		 * We'll hook in as early as possible to avoid potential conflicts with customer
		 * modifications (e.g. "if they define what 'weekly' should look like, use theirs").
		 */
		add_filter( 'cron_schedules', [ $this, 'defineCronSchedules' ], -9999 );

		// Clear caches when orders are created or updated.
		add_action( 'save_post_shop_order', [ QueriesWooCommerce::class, 'saveShopOrder' ], 10, 2 );
	}

	/**
	 * Define constants for legacy integrations.
	 *
	 * Eventually, these constants should be unnecessary and removed.
	 */
	public function defineConstants() {
		defined( 'WP_FAIL2BAN_BLOCK_USER_ENUMERATION' ) || define( 'WP_FAIL2BAN_BLOCK_USER_ENUMERATION', true );
		defined( 'ICONIC_DISABLE_DASH' ) || define( 'ICONIC_DISABLE_DASH', true );

		defined( 'NEXCESS_MAPPS_SITE' ) || define( 'NEXCESS_MAPPS_SITE', $this->settings->is_mapps_site );
		defined( 'NEXCESS_MAPPS_PLAN_NAME' ) || define( 'NEXCESS_MAPPS_PLAN_NAME', $this->settings->plan_name );
		defined( 'NEXCESS_MAPPS_PACKAGE_LABEL' ) || define( 'NEXCESS_MAPPS_PACKAGE_LABEL', $this->settings->package_label );
		defined( 'NEXCESS_MAPPS_ENDPOINT' ) || define( 'NEXCESS_MAPPS_ENDPOINT', $this->settings->managed_apps_endpoint );
		defined( 'NEXCESS_MAPPS_TOKEN' ) || define( 'NEXCESS_MAPPS_TOKEN', $this->settings->managed_apps_token );

		if ( $this->settings->is_mwch_site && ! defined( 'NEXCESS_MAPPS_MWCH_SITE' ) ) {
			define( 'NEXCESS_MAPPS_MWCH_SITE', true );
		}

		if ( $this->settings->is_staging_site && ! defined( 'NEXCESS_MAPPS_STAGING_SITE' ) ) {
			define( 'NEXCESS_MAPPS_STAGING_SITE', true );
		}
	}

	/**
	 * Define any custom cron intervals.
	 *
	 * @param array[] $schedules Registered cron schedules.
	 *
	 * @return array[] The filtered $schedules array.
	 */
	public function defineCronSchedules( $schedules ) {
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = [
				'interval' => WEEK_IN_SECONDS,
				'display'  => _x( 'Once a Week', 'time interval', 'nexcess-mapps' ),
			];
		}

		return $schedules;
	}

	/**
	 * Register a single integration.
	 *
	 * @param string $key   The key to use within $this->integrations.
	 * @param string $class The fully-qualified integration class name.
	 */
	public function registerIntegration( $key, $class ) {
		if ( ! empty( $this->integrations[ $key ] ) ) {
			throw new IntegrationAlreadyLoadedException(
				sprintf(
					/* Translators: %1$s is the integration key. */
					__( 'The "%1$s" integration has already been loaded.', 'nexcess-mapps' ),
					$key
				)
			);
		}

		// Create an instance of the integration, then see if it should be loaded.
		$instance = new $class( $this->settings, $this->adminBar );
		$instance->boot();

		// If we don't need to load it, discard and return.
		if ( ! $instance->shouldLoadIntegration() ) {
			unset( $instance );
			return;
		}

		if ( $instance instanceof DefinesConstants ) {
			$instance->defineConstants();
		}

		$instance->setup();
		$this->integrations[ $key ] = $instance;
	}

	/**
	 * Register multiple integrations at once.
	 *
	 * @param string[] $integrations $key => $class pairings of integrations.
	 */
	public function registerIntegrations( array $integrations ) {
		foreach ( $integrations as $key => $class ) {
			$this->registerIntegration( $key, $class );
		}
	}

	/**
	 * Register a single WP-CLI command.
	 *
	 * @param string          $name     The subcommand name.
	 * @param callable|string $callable The WP-CLI command.
	 */
	public function registerCommand( $name, $callable ) {
		if ( ! defined( 'WP_CLI' ) || ! constant( 'WP_CLI' ) ) {
			return;
		}

		WP_CLI::add_command( $name, $callable );
	}

	/**
	 * Register multiple WP-CLI commands at once.
	 *
	 * @param (string|callable)[] $commands $name => $class pairings of commands.
	 */
	public function registerCommands( array $commands ) {
		foreach ( $commands as $name => $class ) {
			$this->registerCommand( $name, $class );
		}
	}

	/**
	 * Retrieve the <svg> markup for the single-color Nexcess logo.
	 *
	 * @param string $color Optional. The default fill color for the SVG icon.
	 *                      Default is "currentColor".
	 *
	 * @return string An inline SVG version of the single-color Nexcess "N" icon.
	 */
	public static function getNexcessIcon( $color = 'currentColor' ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 53.72 40.17" style="fill: ' . $color . ';"><title>Nexcess</title><path d="M0,8.18A8.18,8.18,0,0,1,13.1,1.64c2.56,1.8,12.73,9.92,12.73,9.92v16L11.67,16.1V39.36H0Z"/><path d="M53.72,32a8.19,8.19,0,0,1-13.1,6.55c-2.56-1.81-12.73-9.92-12.73-9.92v-16L42.05,24.09V.83H53.72Z" /><path d="M38,20.82l1.27-1v-16L27.89,12.63Z"/><path d="M14.4,36.2l11.43-8.68L15.63,19.3l-1.23.93Z"/></svg>';
	}
}
