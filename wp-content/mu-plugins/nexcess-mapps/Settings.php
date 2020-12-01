<?php

namespace Nexcess\MAPPS;

use Nexcess\MAPPS\Concerns\HasFlags;
use Nexcess\MAPPS\Exceptions\ImmutableValueException;
use Nexcess\MAPPS\Exceptions\SiteWorxException;

/**
 * @property-read int    $account_id                   The Nexcess cloud account ID.
 * @property-read bool   $autoscaling_enabled          TRUE if autoscaling is enabled for this site.
 * @property-read string $canny_board_token            The Canny board token used for the collecting customer feedback.
 * @property-read bool   $customer_jetpack             TRUE if the customer is using their own Jetpack subscription.
 * @property-read string $environment                  The current environment. One of "production", "staging",
 *                                                     "regression", or "development".
 * @property-read bool   $is_beginner_plan             TRUE if this site is on the WooCommerce beginner plan.
 * @property-read bool   $is_beta_tester               TRUE if this account is part of our beta testing program.
 * @property-read bool   $is_development_site          TRUE if this is a development environment.
 * @property-read bool   $is_mapps_site                TRUE if this is a Managed Applications (MAPPS) site.
 * @property-read bool   $is_mwch_site                 TRUE if this is a Managed WooCommerce hosting site.
 * @property-read bool   $is_nexcess_site              TRUE if this is running on the Nexcess platform.
 * @property-read bool   $is_production_site           TRUE if this is a production environment.
 * @property-read bool   $is_regression_site           TRUE if this is a regression environment.
 * @property-read bool   $is_staging_site              TRUE if this is a staging environment.
 * @property-read bool   $is_temp_domain               TRUE if the site is currently running on its temporary domain.
 * @property-read bool   $mapps_core_updates_enabled   TRUE if MAPPS is responsible for automatic core updates,
 *                                                     FALSE if the responsibility falls to WordPress core.
 * @property-read bool   $mapps_plugin_updates_enabled TRUE if MAPPS is responsible for automatic plugin updates,
 *                                                     FALSE if the responsibility falls to WordPress core.
 * @property-read string $managed_apps_endpoint        The MAPPS API endpoint.
 * @property-read string $managed_apps_token           The MAPPS API token.
 * @property-read string $package_label                The platform package label.
 * @property-read string $php_version                  The current MAJOR.MINOR PHP version.
 * @property-read string $plan_name                    The (legacy) plan code, based on the $package_label.
 * @property-read string $plan_type                    The plan type ("wordpress", "woocommerce", etc.).
 * @property-read string $redis_host                   The Redis server host.
 * @property-read int    $redis_port                   The Redis server port.
 * @property-read string $telemetry_key                API key for the plugin reporter (telemetry).
 */
final class Settings {
	use HasFlags;

	/**
	 * An array of Account Configuration Details provided by SiteWorx.
	 *
	 * @var mixed[]
	 */
	private $config;

	/**
	 * Parsed settings, which are immutable outside of this object.
	 *
	 * @var mixed[]
	 */
	private $settings;

	/**
	 * Plan names mapped to package labels.
	 *
	 * Every defined plan should have a corresponding class constant, and these constants should
	 * be the only thing used for conditionals throughout the codebase.
	 */

	/**
	 * Plans available prior to January 24, 2020
	 */
	const PLAN_BASIC        = 'woo.basic';
	const PLAN_BEGINNER     = 'woo.beginner';
	const PLAN_BUSINESS     = 'woo.business';
	const PLAN_FREELANCE    = 'wp.freelance';
	const PLAN_PERSONAL     = 'wp.personal';
	const PLAN_PLUS         = 'woo.plus';
	const PLAN_PRO          = 'woo.pro';
	const PLAN_PROFESSIONAL = 'wp.professional';
	const PLAN_STANDARD     = 'woo.standard';

	/**
	 * Plans available after January 24, 2020
	 */
	const PLAN_MWP_SPARK      = 'mwp.spark';
	const PLAN_MWP_MAKER      = 'mwp.maker';
	const PLAN_MWP_BUILDER    = 'mwp.builder';
	const PLAN_MWP_PRODUCER   = 'mwp.producer';
	const PLAN_MWP_EXECUTIVE  = 'mwp.executive';
	const PLAN_MWP_ENTERPRISE = 'mwp.enterprise';
	const PLAN_MWC_STARTER    = 'mwc.starter';
	const PLAN_MWC_CREATOR    = 'mwc.creator';
	const PLAN_MWC_STANDARD   = 'mwc.standard';
	const PLAN_MWC_GROWTH     = 'mwc.growth';
	const PLAN_MWC_ENTERPRISE = 'mwc.enterprise';

	/**
	 * Initialize the settings instance.
	 *
	 * @param string[] $settings Optional. Settings to merge into what's parsed from the
	 *                           environment. Default is empty.
	 */
	public function __construct( $settings = [] ) {
		$this->settings = array_merge( $this->loadEnvironmentVariables(), $settings );
	}

	/**
	 * Retrieve a setting as a property.
	 *
	 * This is merely a wrapper around getSetting(), but enables us to do things like:
	 *
	 *     $settings->is_mwch_site
	 *
	 * @param string $setting The setting name.
	 *
	 * @return mixed
	 */
	public function __get( $setting ) {
		return $this->getSetting( $setting );
	}

	/**
	 * Don't permit properties to be overridden on the class.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ImmutableValueException
	 *
	 * @param string $property The property name.
	 * @param mixed  $value    The value being assigned.
	 */
	public function __set( $property, $value ) {
		throw new ImmutableValueException(
			sprintf(
				/* Translators: %1$s is the property name. */
				__( 'Setting "%1$s" may not be modified.', 'nexcess-mapps' ),
				esc_html( $property )
			)
		);
	}

	/**
	 * Retrieve a setting.
	 *
	 * If the setting is callable, the callback will be executed and cached, enabling lazy-loading
	 * of more complicated settings. The callback itself will retrieve the current instance of the
	 * Settings object.
	 *
	 * @param string $setting The setting name.
	 * @param mixed  $default Optional. The default value, if the setting is not present.
	 *                        Default is null.
	 *
	 * @return mixed
	 */
	public function getSetting( $setting, $default = null ) {
		if ( ! isset( $this->settings[ $setting ] ) ) {
			return $default;
		}

		// Lazy-load the setting if given a callable.
		if ( is_callable( $this->settings[ $setting ] ) ) {
			$this->settings[ $setting ] = call_user_func_array( $this->settings[ $setting ], [ $this ] );
		}

		return null !== $this->settings[ $setting ] ? $this->settings[ $setting ] : $default;
	}

	/**
	 * Retrieve all registered settings.
	 *
	 * @return mixed[]
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * Read and parse all environment variables.
	 *
	 * @return mixed[]
	 */
	private function loadEnvironmentVariables() {
		/*
		 * If the user has specified an environment type, we should respect that.
		 *
		 * The environment type may be set in two ways:
		 * 1. Via the WP_ENVIRONMENT_TYPE environment variable.
		 * 2. By defining the WP_ENVIRONMENT_TYPE constant.
		 */
		$environment_type = ! empty( getenv( 'WP_ENVIRONMENT_TYPE' ) ) || defined( 'WP_ENVIRONMENT_TYPE' )
			? wp_get_environment_type()
			: $this->getConfig( 'app_environment', 'production' );

		// Assemble the most basic values.
		$environment = [
			'account_id'                   => (int) $this->getConfig( 'account_id' ),
			'autoscaling_enabled'          => (bool) $this->getConfig( 'autoscale_enabled', false ),
			'customer_jetpack'             => (bool) $this->getConfig( 'customer_owns_jetpack', false ),
			'environment'                  => $environment_type,
			'package_label'                => $this->getConfig( 'package_label', false ),
			'plan_type'                    => $this->getConfig( 'app_type', 'unknown' ),
			'managed_apps_endpoint'        => $this->getConfig( 'mapp_endpoint', false ),
			'managed_apps_token'           => $this->getConfig( 'mapp_token', false ),
			'mapps_core_updates_enabled'   => (bool) $this->getConfig( 'app_updates_core', true ),
			'mapps_plugin_updates_enabled' => (bool) $this->getConfig( 'app_updates_plugin', true ),
			'plan_name'                    => $this->getConfig( 'package_name', false ),
			'redis_host'                   => $this->getConfig( 'redis_host', '' ),
			'redis_port'                   => (int) $this->getConfig( 'redis_port', 0 ),
			'is_temp_domain'               => $this->getConfig( 'temp_domain' ) === wp_parse_url( site_url(), PHP_URL_HOST ),
			'is_beta_tester'               => defined( 'NEXCESS_MAPPS_BETA_TESTER' )
				? (bool) constant( 'NEXCESS_MAPPS_BETA_TESTER' )
				: (bool) $this->getConfig( 'beta_client', false ),
		];

		// Merge in any calculated values.
		$settings = array_merge( $environment, [
			'is_nexcess_site'     => 'unknown' !== $environment['plan_type'],
			'is_mapps_site'       => ! in_array( $environment['plan_type'], [ 'generic', 'unknown' ], true )
										&& ! empty( $environment['package_label'] ),
			'is_mwch_site'        => 'woocommerce' === $environment['plan_type'],
			'is_production_site'  => 'production' === $environment['environment'],
			'is_regression_site'  => 'regression' === $environment['environment'],
			'is_staging_site'     => 'staging' === $environment['environment'],
			'is_development_site' => 'development' === $environment['environment'],
			'is_beginner_plan'    => self::PLAN_BEGINNER === $environment['package_label'],
			'php_version'         => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
		] );

		// Finally, include any extra settings.
		return array_merge( $settings, [
			'canny_board_token' => [ $this, 'getCannyBoardToken' ],
			'telemetry_key'     => 'ZTuhNKgzgmAAtZNNjRyqVuzQbv9NyWNJMf7',
		] );
	}

	/**
	 * Based on the current settings, determine which Canny board token to offer.
	 *
	 * @param self $settings The settings, parsed and calculated from the environment.
	 *
	 * @return string Either a valid Canny board token or an empty string.
	 */
	private function getCannyBoardToken( Settings $settings ) {
		if ( $settings->is_beta_tester ) {
			return '1cdf6de0-9706-7444-68f9-cf2c141bcb3e';
		}

		return '';
	}

	/**
	 * Retrieve a value from SiteWorx, falling back to a default value if the given configuration
	 * variable does not exist.
	 *
	 * @param string $name    The configuration name.
	 * @param mixed  $default The default value if the given $name is not set. Default is null.
	 *
	 * @return mixed
	 */
	private function getConfig( $name, $default = null ) {
		if ( empty( $this->config ) ) {
			try {
				$this->config = $this->loadSiteWorxEnvironment();
			} catch ( SiteWorxException $e ) {
				/*
				 * If the `siteworx` command is not available, fallback to environment variables.
				 *
				 * This allows us to test locally and only allow overrides when the site is not
				 * on the Nexcess platform.
				 */
				$value = getenv( $name, true );

				return false === $value ? $default : $value;
			}
		}

		return isset( $this->config[ $name ] ) ? $this->config[ $name ] : $default;
	}

	/**
	 * Retrieve and parse environment details from SiteWorx.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\SiteWorxException if invalid data is returned from SiteWorx.
	 *
	 * @return mixed[]
	 */
	private function loadSiteWorxEnvironment() {
		$cache_key = 'nexcess-mapps-siteworx-environment';
		$cached    = get_site_transient( $cache_key );

		// Return from the object cache, if available.
		if ( ! empty( $cached ) ) {
			return $cached;
		}

		try {
			$output    = [];
			$exit_code = 0;

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
			exec( 'siteworx -u -o json -n -c Overview -a listAccountConfig 2>&1', $output, $exit_code );

			// Received a non-zero exit code.
			if ( 0 !== $exit_code ) {
				throw new SiteWorxException( 'Unexpected exit code ' . $exit_code, $exit_code );
			}

			// Received an empty response from siteworx.
			if ( empty( $output ) ) {
				throw new SiteWorxException( 'Received an empty response' );
			}

			$return = json_decode( implode( '', $output ), true );

			if ( null === $return ) {
				throw new SiteWorxException( 'Unable to decode JSON response' );
			}
		} catch ( \Throwable $e ) {
			throw new SiteWorxException( 'An error occurred querying SiteWorx', 500, $e );
		}

		// Cache the results as a transient.
		set_site_transient( $cache_key, $return, 5 * MINUTE_IN_SECONDS );

		return $return;
	}
}
