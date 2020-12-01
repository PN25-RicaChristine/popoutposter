<?php

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\AdminBar;
use Nexcess\MAPPS\Plugin;
use Nexcess\MAPPS\Settings;
use const Nexcess\MAPPS\VENDOR_DIR;

abstract class Integration {

	/**
	 * @var \Nexcess\MAPPS\AdminBar
	 */
	protected $adminBar;

	/**
	 * @var \Nexcess\MAPPS\Settings
	 */
	protected $settings;

	/**
	 * @param \Nexcess\MAPPS\Settings $settings
	 *
	 * @return self
	 */
	public function __construct( Settings $settings, AdminBar $admin_bar ) {
		$this->settings = $settings;
		$this->adminBar = $admin_bar;
	}

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return true;
	}

	/**
	 * Setup steps that should always be run, regardless of whether shouldLoadIntegration()
	 * returns true or false.
	 */
	public function boot() {
		// No-op by default.
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	abstract public function setup();

	/**
	 * Get the underlying Settings object.
	 *
	 * @return \Nexcess\MAPPS\Settings
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Load another plugin's bootstrap file.
	 *
	 * @param string $plugin The plugin file, relative to VENDOR_DIR.
	 */
	protected function loadPlugin( $plugin ) {
		$file = VENDOR_DIR . $plugin;

		if ( is_readable( $file ) ) {
			require_once $file;

			/**
			 * Fires after the Nexcess MAPPS plugin bootstraps another plugin.
			 *
			 * @param string $file The full filepath to the bootstrap file.
			 */
			do_action( 'Nexcess\\MAPPS\\load_plugin:' . $plugin, $file ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error(
				/* Translators: %1$s is the integration filepath. */
				esc_html( sprintf( __( 'Unable to load Nexcess Managed Apps integration from %1$s.', 'nexcess-mapps' ), $file ) ),
				E_USER_WARNING
			);
		}
	}

	/**
	 * Verify that a plugin is installed.
	 *
	 * This is a wrapper around WordPress' get_plugins() function, ensuring the necessary
	 * file is installed before checking.
	 *
	 * @see get_plugins()
	 *
	 * @param string $plugin The directory/file path.
	 *
	 * @return bool
	 */
	protected static function isPluginInstalled( $plugin ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Fetch all the plugins we have installed.
		$all_plugins = get_plugins();

		return array_key_exists( $plugin, $all_plugins );
	}

	/**
	 * Determine whether or not a particular mu-plugin is present.
	 *
	 * Since mu-plugins are loaded in alphabetical order, this is necessary to see if mu-plugins
	 * *after* "nexcess-mapps" will be loaded.
	 *
	 * @param string $text_domain The plugin text-domain to check for.
	 *
	 * @return bool Whether or not the given plugin text-domain is present as an mu-plugin.
	 */
	protected static function isMuPluginInstalled( $text_domain ) {
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( get_mu_plugins() as $plugin ) {
			if ( ! empty( $plugin['TextDomain'] ) && $text_domain === $plugin['TextDomain'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Verify that a plugin is both installed and active.
	 *
	 * This is a wrapper around WordPress' is_plugin_active() function, ensuring the necessary
	 * file is loaded before checking.
	 *
	 * @see is_plugin_active()
	 *
	 * @param string $plugin The directory/file path.
	 *
	 * @return bool
	 */
	protected static function isPluginActive( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin );
	}

	/**
	 * Determine if the current request is related to activating the given plugin.
	 *
	 * If we're forcibly activating a plugin in an integration, we want to make sure that
	 * integration is not being loaded on the activation request, or risk failures due to plugin
	 * classes/functions already being defined.
	 *
	 * @param string $plugin The plugin to check for.
	 *
	 * @return bool
	 */
	protected static function isPluginBeingActivated( $plugin ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return isset( $_SERVER['PHP_SELF'], $_GET['action'], $_GET['plugin'] )
			&& '/wp-admin/plugins.php' === $_SERVER['PHP_SELF']
			&& 'activate' === $_GET['action']
			&& $plugin === $_GET['plugin'];
		// phpcs:enable
	}
}
