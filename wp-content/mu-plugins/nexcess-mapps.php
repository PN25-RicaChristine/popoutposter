<?php
/**
 * Plugin Name: Nexcess Managed Apps
 * Plugin URI:  https://www.nexcess.net
 * Description: Functionality to support the Nexcess Managed Apps WordPress and WooCommerce platforms.
 * Version:     1.15.1
 * Author:      Nexcess
 * Author URI:  https://www.nexcess.net
 * Text Domain: nexcess-mapps
 *
 * For details on how to customize the MU plugin behavior, please see nexcess-mapps/README.md.
 */

namespace Nexcess\MAPPS;

use Nexcess\MAPPS\Support\PlatformRequirements;

// At this time, the MU plugin doesn't need to do anything if WordPress is currently installing.
if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
	return;
}

// The version of the Nexcess Managed Apps plugin.
define( __NAMESPACE__ . '\PLUGIN_VERSION', '1.15.1' );
define( __NAMESPACE__ . '\PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( __NAMESPACE__ . '\VENDOR_DIR', __DIR__ . '/nexcess-mapps/vendor/' );

// Initialize the plugin.
try {
	require_once VENDOR_DIR . 'autoload.php';

	// Check for anything that might prevent the plugin from loading.
	$requirements = new PlatformRequirements();

	if ( ! $requirements->checkDependencies() ) {
		return $requirements->renderOutdatedWordPressVersionNotice();
	}

	// Finish loading files that should be explicitly required.
	require_once __DIR__ . '/nexcess-mapps/Support/Compat.php';
	require_once __DIR__ . '/nexcess-mapps/vendor/stevegrunwell/wp-admin-tabbed-settings-pages/wp-admin-tabbed-settings-pages.php';

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$plugin = new Plugin( new Settings(), new AdminBar() );
	$plugin->defineConstants();
	$plugin->addGlobalHooks();
	$plugin->registerIntegrations( [
		'admin'              => Integrations\Admin::class,
		'cache'              => Integrations\Cache::class,
		'compatibility'      => Integrations\PHPCompatibility::class,
		'dashboard'          => Integrations\Dashboard::class,
		'debug'              => Integrations\Debug::class,
		'displayEnvironment' => Integrations\DisplayEnvironment::class,
		'errorHandling'      => Integrations\ErrorHandling::class,
		'fail2ban'           => Integrations\Fail2Ban::class,
		'feedback'           => Integrations\Feedback::class,
		'installer'          => Integrations\PluginInstaller::class,
		'jetpack'            => Integrations\Jetpack::class,
		'maintenance'        => Integrations\Maintenance::class,
		'objectCache'        => Integrations\ObjectCache::class,
		'opcache'            => Integrations\OPcache::class,
		'recapture'          => Integrations\Recapture::class,
		'regressionSites'    => Integrations\RegressionSites::class,
		'siteHealth'         => Integrations\SiteHealth::class,
		'stagingSites'       => Integrations\StagingSites::class,
		'support'            => Integrations\Support::class,
		'supportUsers'       => Integrations\SupportUsers::class,
		'telemetry'          => Integrations\Telemetry::class,
		'themes'             => Integrations\Themes::class,
		'updates'            => Integrations\Updates::class,
		'varnish'            => Integrations\Varnish::class,
		'visualComparision'  => Integrations\VisualComparison::class,
		'wcUpperLimits'      => Integrations\WooCommerceUpperLimits::class,
		'woocommerce'        => Integrations\WooCommerce::class,
	] );
	$plugin->registerCommands( [
		'nxmapps'                   => Commands\Support::class,
		'nxmapps affiliatewp'       => Commands\AffiliateWP::class,
		'nxmapps brainstormforce'   => Commands\BrainstormForce::class,
		'nxmapps cache'             => Commands\Cache::class,
		'nxmapps config'            => Commands\Config::class,
		'nxmapps dokan'             => Commands\Dokan::class,
		'nxmapps ithemes'           => Commands\iThemes::class,
		'nxmapps qubely'            => Commands\Qubely::class,
		'nxmapps setup'             => [ Commands\Setup::class, 'setup' ],
		'nxmapps setup:pre-install' => [ Commands\Setup::class, 'preInstallPlugins' ],
		'nxmapps setup:woocommerce' => [ Commands\Setup::class, 'woocommerce' ],
		'nxmapps vc'                => Commands\VisualComparison::class,
		'nxmapps wp-all-import-pro' => Commands\WPAllImportPro::class,
	] );
} catch ( Exceptions\IsNotNexcessSiteException $e ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
	trigger_error( 'The plugin may only be loaded on the Nexcess Managed Apps platform.', E_USER_NOTICE );
} catch ( \Exception $e ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
	trigger_error( 'Nexcess Managed Apps Error: ' . esc_html( $e->getMessage() ), E_USER_WARNING );
}
