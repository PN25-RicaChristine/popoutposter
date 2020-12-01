<?php

namespace Nexcess\MAPPS\Commands;

use Nexcess\MAPPS\Exceptions\InstallationException;
use Nexcess\MAPPS\Integrations\Cache as CacheIntegration;
use Nexcess\MAPPS\Integrations\Telemetry;
use Nexcess\MAPPS\Services\Installer;
use Nexcess\MAPPS\Settings;
use WC_Install;
use WC_Shipping_Zone;
use WC_Shipping_Zones;
use WP_CLI;

/**
 * Commands for preparing a new Nexcess Managed Apps site.
 */
class Setup {

	/**
	 * @var \Nexcess\MAPPS\Settings
	 */
	private $settings;

	public function __construct( Settings $settings = null ) {
		$this->settings = $settings ?: new Settings();

		// Abort if this is running on a non-MAPPS site.
		if ( ! $this->settings->is_mapps_site ) {
			WP_CLI::line( WP_CLI::colorize( '%yThis does not appear to be a MAPPS site, aborting.%n' ) );
			exit;
		}
	}

	/**
	 * Set up a new Nexcess Managed Apps site.
	 *
	 * This command should automatically detect the type of Nexcess Managed Apps environment and apply the
	 * appropriate setup commands.
	 */
	public function setup() {
		// Ensure that all themes and plugins are up-to-date.
		$update_options = [
			'launch'     => false,
			'exit_error' => false,
		];

		WP_CLI::line( WP_CLI::colorize( '%cEnsuring all existing plugins and themes are up-to-date...%n' ) );
		WP_CLI::runcommand( 'plugin update --all --format=summary', $update_options );
		WP_CLI::runcommand( 'theme update --all --format=summary', $update_options );

		// Pre-install plugins, based on the current site's plan.
		$this->preInstallPlugins();

		/*
		 * Cache Enabler requires a non-default permalink structure.
		 *
		 * The default value for the "permalink_structure" is an empty string; if that's the case
		 * (or the option doesn't exist), use "/%postname%/".
		 */
		if ( empty( get_option( 'permalink_structure', '' ) ) ) {
			WP_CLI::line( '- Setting default permalink structure' );
			update_option( 'permalink_structure', '/%postname%/' );
		}

		// Configure Cache Enabler.
		WP_CLI::line( WP_CLI::colorize( '%cConfiguring Cache Enabler...%n' ) );
		update_option( 'cache-enabler', $this->getCacheEnablerSettings() );

		if ( ! CacheIntegration::injectCacheEnablerRewriteRules() ) {
			return WP_CLI::error( 'Unable to write Htaccess file!' );
		}

		if ( $this->settings->is_mwch_site ) {
			$this->woocommerce();
		}

		// Send initial telemetry data.
		do_action( Telemetry::REPORT_CRON_ACTION );
	}

	/**
	 * Set up a new Managed WooCommerce site.
	 */
	public function woocommerce() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return WP_CLI::warning( 'Unable to configure WooCommerce, as WooCommerce is not active on this site.' );
		}

		WP_CLI::line( WP_CLI::colorize( '%cSetting defaults for WooCommerce...%n' ) );

		// Set default options.
		$options = [
			'woocommerce_default_country'              => 'US:AL',
			'woocommerce_currency'                     => 'USD',
			'woocommerce_weight_unit'                  => 'lbs',
			'woocommerce_dimension_unit'               => 'in',
			'woocommerce_allow_tracking'               => 'no',
			'woocommerce_admin_notices'                => [],
			'woocommerce_product_type'                 => 'physical',
			'woocommerce_show_marketplace_suggestions' => 'no',
			'woocommerce_api_enabled'                  => 'yes',
		];

		WP_CLI::line( '- Setting default options:' );
		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}

		WP_CLI::line( '- Creating default pages' );
		WC_Install::create_pages();

		// Set default user meta.
		WP_CLI::line( '- Setting default user meta' );
		add_user_meta( 1, 'is_disable_paypal_marketing_solutions_notice', true );

		// Create default shipping zones.
		if ( empty( WC_Shipping_Zones::get_zones() ) ) {
			WP_CLI::line( '- Creating default shipping zones' );
			$this->createDefaultShippingZones();
		}

		WP_CLI::line();
		WP_CLI::success( 'Default WooCommerce configurations have been applied.' );
	}

	/**
	 * Pre-install and license plugins that should be present based on the site's plan.
	 */
	public function preInstallPlugins() {
		WP_CLI::line( WP_CLI::colorize( '%cPre-installing plugins for the site\'s plan...%n' ) );
		$installer = new Installer( $this->settings );
		$plugins   = $installer->getPreinstallPlugins();
		$errors    = false;

		foreach ( $plugins as $plugin ) {
			try {
				WP_CLI::line( sprintf( PHP_EOL . 'Installing %1$s...', $plugin->identity ) );
				$installer->install( $plugin->id );

				if ( 'none' !== $plugin->license_type ) {
					WP_CLI::line( sprintf(
						'Licensing %1$s (%2$s)',
						$plugin->identity,
						$plugin->license_type
					) );
					$installer->license( $plugin->id );
				}
			} catch ( InstallationException $e ) {
				WP_CLI::warning( $e->getMessage() );
				$errors = true;
			}
		}

		if ( $errors ) {
			WP_CLI::line();
			WP_CLI::error( 'One or more plugins could not be pre-installed.', false );
		}
	}

	/**
	 * Retrieve our default Cache Enabler settings.
	 *
	 * These settings correspond to the Cache_Enabler::settings_page() method.
	 *
	 * @link https://www.keycdn.com/support/wordpress-cache-enabler-plugin
	 *
	 * @return mixed[]
	 */
	protected function getCacheEnablerSettings() {
		/*
		 * A collection of paths that should be excluded.
		 *
		 * When adding a path here, it's essentially excluding the following pattern from the
		 * full-page cache:
		 *
		 *   /some-path*
		 */
		$excluded_paths = [
			'account',
			'addons',
			'administrator',
			'affiliate-area.php',
			'cart',
			'checkout',
			'lock.php',
			'login',
			'mepr',
			'my-account',
			'page/ref',
			'ref',
			'register',
			'resetpass',
			'store',
			'thank-you',
			'wp-cron.php',
			'wp-includes',
			'wp-json',
			'xmlrpc.php',
		];

		/*
		 * A collection of cookie prefixes that, if present, should cause the cache to be bypassed.
		 */
		$excluded_cookie_prefixes = [
			'comment_author_',
			'wordpress_',
			'wp-postpass_',
			'wp-settings-',
			'wp-resetpass-',
			'wp_woocommerce_session',
			'woocommerce_',
			'mplk',
			'mp3pi141592pw',
		];

		/*
		 * Query string parameters that should be ignored by the cache.
		 *
		 * By default, Cache Enabler will bypass the cache if any query string parameters are
		 * present. Including a key here will mean the cache will *not* be bypassed if one or more
		 * of these keys are the only query string arguments present.
		 *
		 * Note that unlike paths and cookies, these are exact query argument keys/names!
		 */
		$excluded_query_params = [
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_term',
			'utm_content',
		];

		// Escape anything that might break regular expressions.
		$excluded_cookie_prefixes = array_map( 'preg_quote', $excluded_cookie_prefixes );
		$excluded_query_params    = array_map( 'preg_quote', $excluded_query_params );
		$excluded_paths           = array_map( function ( $path ) {
			return preg_quote( $path, '/' );
		}, $excluded_paths );

		return [
			// Cache expiry in hours. An expiry time of 0 means that the cache never expires.
			'expires'          => 2,

			// Clear the complete cache if a new post has been published (instead of only the home page cache).
			'new_post'         => 0,

			// Clear the complete cache if a new comment has been posted (instead of only the page specific cache).
			'new_comment'      => 0,

			// Create an additional cached version for WebP image support.
			'webp'             => 0,

			// Clear the complete cache if any plugin has been upgraded.
			'clear_on_upgrade' => 0,

			// Pre-compression of cached pages. Needs to be disabled if the decoding fails in the web browser.
			'compress'         => 0,

			// Post or Pages IDs separated by a %s that should not be cached.
			'excl_ids'         => '',

			// Regexp matching page paths that should not be cached.
			'excl_regexp'      => sprintf( '/^\/(%1$s)\/?/', implode( '|', $excluded_paths ) ),

			// Regexp matching cookies that should cause the cache to be bypassed.
			'excl_cookies'     => sprintf( '/^(?!wordpress_test_cookie)(%1$s).*/i', implode( '|', $excluded_cookie_prefixes ) ),

			// Regexp matching campaign tracking GET attributes that should not cause the cache to be bypassed.
			'incl_attributes'  => ! empty( $excluded_query_params )
				? sprintf( '/^(%1$s)$/', implode( '|', $excluded_query_params ) )
				: '',

			// Cache minification.
			'minify_html'      => '',
		];
	}

	/**
	 * Create default shipping zones for WooCommerce.
	 */
	protected function createDefaultShippingZones() {
		$domestic_zone = new WC_Shipping_Zone( null );
		$domestic_zone->set_zone_order( 0 );
		$domestic_zone->add_location( WC()->countries->get_base_country(), 'country' );
		$domestic_zone->add_shipping_method( 'free_shipping' );
		$domestic_zone->save();

		// Set a fallback for anything outside the shop's country.
		$fallback_zone = new WC_Shipping_Zone( 0 );
		$fallback_zone->add_shipping_method( 'free_shipping' );
		$fallback_zone->save();
	}
}
