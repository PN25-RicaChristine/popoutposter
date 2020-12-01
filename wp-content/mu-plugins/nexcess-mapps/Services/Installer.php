<?php

namespace Nexcess\MAPPS\Services;

use Nexcess\MAPPS\Concerns\InvokesCLI;
use Nexcess\MAPPS\Concerns\MakesHttpRequests;
use Nexcess\MAPPS\Concerns\QueriesMAPPS;
use Nexcess\MAPPS\Exceptions\ConsoleException;
use Nexcess\MAPPS\Exceptions\InstallationException;
use Nexcess\MAPPS\Exceptions\LicensingException;
use Nexcess\MAPPS\Exceptions\WPErrorException;
use Nexcess\MAPPS\Settings;
use WP_Error;

class Installer {
	use InvokesCLI;
	use MakesHttpRequests;
	use QueriesMAPPS;

	/**
	 * @var \Nexcess\MAPPS\Settings
	 */
	private $settings;

	/**
	 * The cache key used for retrieving available plugins.
	 */
	const AVAILABLE_PLUGINS_CACHE_KEY = 'nexcess-mapps-installer-plugins';

	/**
	 * Construct the Installer instance.
	 *
	 * @param \Nexcess\MAPPS\Settings $settings The Settings object for this site.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Retrieve a list of installable plugins.
	 *
	 * @return object[] An array of installable plugins.
	 */
	public function getAvailablePlugins() {
		try {
			$body = remember_transient( self::AVAILABLE_PLUGINS_CACHE_KEY, function () {
				return $this->validateHttpResponse( $this->mappsApi( 'v1/app-plugin' ), 200 );
			}, 5 * MINUTE_IN_SECONDS );
		} catch ( \Exception $e ) {
			$this->logException( $e );
			return [];
		}

		return json_decode( $body, false ) ?: [];
	}

	/**
	 * Retrieve a list of plugins that should be pre-installed on the site.
	 *
	 * The API endpoint will use the MAPPS API token, and the results will change based on the API
	 * token used.
	 *
	 * @return object[] An array of plugins that should be pre-installed. This may be empty if no
	 *                  plugins should be pre-configured.
	 */
	public function getPreinstallPlugins() {
		try {
			$body = $this->validateHttpResponse( $this->mappsApi( 'v1/app-plugin/setup' ), 200 );
		} catch ( \Exception $e ) {
			$this->logException( $e );
			return [];
		}

		return json_decode( $body, false ) ?: [];
	}

	/**
	 * Get details about one of the installable plugins.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\WPErrorException if details could not be retrieved.
	 *
	 * @param int $id The plugin ID, derived from $this->getAvailablePlugins().
	 *
	 * @return object
	 */
	public function getPluginDetails( $id ) {
		$response = $this->mappsApi( sprintf( 'v1/app-plugin/%d/install', $id ) );
		$body     = $this->validateHttpResponse( $response, 200 );

		return json_decode( $body, false ) ?: (object) [];
	}

	/**
	 * Get licensing instructions for one of the installable plugins.
	 *
	 * @param int $id The plugin ID, derived from $this->getAvailablePlugins().
	 *
	 * @return object
	 */
	public function getPluginLicensing( $id ) {
		$response = $this->mappsApi( sprintf( 'v1/app-plugin/%d/license', $id ), [
			'timeout' => 60, // Licensing often depends on outside services.
		] );
		$body     = $this->validateHttpResponse( $response, 200 );

		return json_decode( $body, false ) ?: (object) [];
	}

	/**
	 * Install a single plugin.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\InstallationException if the installation request fails.
	 *
	 * @param int $id The plugin/theme ID to install.
	 *
	 * @return bool Will return true if nothing went wrong during installation.
	 */
	public function install( $id ) {
		$install_steps = [
			'pre_install_script',
			'install',
			'post_install_script',
		];

		try {
			$details = $this->getPluginDetails( $id );

			foreach ( $install_steps as $step ) {
				if ( empty( $details->install_script->plugin->{$step} ) ) {
					continue;
				}

				$this->handleInstallationStep( $details->install_script->plugin->{$step} );
			}
		} catch ( \Exception $e ) {
			throw new InstallationException( sprintf(
				'Unable to install asset with ID %1$d: %2$s',
				$id,
				$e->getMessage()
			), $e->getCode(), $e );
		}

		return true;
	}

	/**
	 * License a single plugin.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\LicensingException if the licensing request fails.
	 *
	 * @param int $id The plugin/theme ID to install.
	 *
	 * @return bool Will return true if nothing went wrong during licensing.
	 */
	public function license( $id ) {
		$licensing_steps = [
			'pre_licensing_script',
			'licensing_script',
			'post_licensing_script',
		];

		try {
			$details = $this->getPluginLicensing( $id );

			foreach ( $licensing_steps as $step ) {
				if ( empty( $details->licensing_script->plugin->{$step} ) ) {
					continue;
				}

				$this->handleLicensingStep( $details->licensing_script->plugin->{$step} );
			}
		} catch ( \Exception $e ) {
			throw new LicensingException( sprintf(
				'Unable to license asset with ID %1$d: %2$s',
				$id,
				$e->getMessage()
			), $e->getCode(), $e );
		}

		return true;
	}

	/**
	 * Handle a single installation step.
	 *
	 * This includes pre- and post-install commands, as well as the primary installation method.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\InstallationException if the installation step fails.
	 *
	 * @param object $instructions The instructions to execute.
	 */
	protected function handleInstallationStep( $instructions ) {
		// Install from WordPress.org via WP-CLI.
		if ( ! empty( $instructions->wp_package ) ) {
			$this->installPluginViaWPCLI( $instructions->wp_package );
		}

		// Install local packages.
		if ( ! empty( $instructions->source ) ) {
			$this->installPluginViaWPCLI( $instructions->source );
		}
	}

	/**
	 * Handle a single licensing step.
	 *
	 * @param object $instructions The instructions to execute.
	 */
	protected function handleLicensingStep( $instructions ) {
		// Run a WP-CLI command.
		if ( ! empty( $instructions->wp_cli ) ) {
			try {
				// The API currently returns the wrong command for licensing Brainstorm Force plugins.
				$command = str_replace(
					'brainstormforce license',
					'nxmapps brainstormforce',
					$instructions->wp_cli
				);

				$this->wpCli( $command )
					->wasSuccessful( true );
			} catch ( ConsoleException $e ) {
				throw new LicensingException( sprintf(
					/* Translators: %1$s is the error message. */
					__( 'Unable to license plugin: %1$s', 'nexcess-mapps' ),
					$e->getMessage()
				), $e->getCode(), $e );
			}
		}

		// Set option(s).
		if ( ! empty( $instructions->wp_option ) ) {
			foreach ( (array) $instructions->wp_option as $key => $value ) {
				update_option( $key, $value );
			}
		}
	}

	/**
	 * Log an exception that may have come up.
	 *
	 * @param \Exception $e An Exception object.
	 *
	 * @todo Actually do something with the exception.
	 */
	protected function logException( \Exception $e ) {
		// Nothing yet.
	}

	/**
	 * Install a plugin via WP-CLI.
	 *
	 * Since `wp plugin install <plugin>` can accept a plugin name or a URL, this lets us write
	 * the logic once.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\InstallationException if the installation step fails.
	 *
	 * @param string $plugin The plugin to install.
	 */
	private function installPluginViaWPCLI( $plugin ) {
		try {
			$this->wpCli( 'plugin install', [
				$plugin,
				'--activate',
			] )->wasSuccessful( true );
		} catch ( ConsoleException $e ) {
			throw new InstallationException( sprintf(
				/* Translators: %1$s is the plugin name, %2$s is the previous exception message. */
				__( 'Unable to install %1$s: %2$s', 'nexcess-mapps' ),
				$plugin,
				$e->getMessage()
			), $e->getCode(), $e );
		}
	}
}
