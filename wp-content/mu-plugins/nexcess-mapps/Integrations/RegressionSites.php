<?php
/**
 * Modify site behavior for regression sites.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Concerns\HasPluggables;
use Nexcess\MAPPS\Concerns\ManagesWpConfig;
use WP_Query;

class RegressionSites extends Integration {
	use HasHooks;
	use HasPluggables;
	use ManagesWpConfig;

	/**
	 * Used to indicate that the wp-config.php file has already been updated.
	 *
	 * @var string
	 */
	const WP_CONFIG_UPDATED = 'NEXCESS_MAPPS_REGRESSION_SITE';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_regression_site;
	}

	/**
	 * Perform any necessary setup for the integration.
	 *
	 * This method is automatically called as part of Plugin::registerIntegration(), and is the
	 * entry-point for all integrations.
	 */
	public function setup() {
		$this->addHooks();
		$this->seedRandomNumberGenerator();

		// If the site is running Sucuri, ensure scans aren't being run.
		remove_all_actions( 'sucuriscan_scheduled_scan' );
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'muplugins_loaded', [ $this, 'updateWpConfig' ] ],
			[ 'plugins_loaded',    [ $this, 'loadPluggables' ] ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Retrieve all filters for the integration.
	 *
	 * @return array[]
	 */
	protected function getFilters() {
		return [
			// Block robots, regardless of the blog_public option.
			[ 'pre_option_blog_public', '__return_zero' ],

			// Reduce randomness to prevent false negatives.
			[ 'posts_orderby', [ $this, 'preventRandomQueryResults' ], 100, 2 ],

			// Fallback to prevent emails from being sent to customers.
			[ 'wp_mail', [ $this, 'rerouteEmails' ], PHP_INT_MAX ],
		];
	}

	/**
	 * Prevent queries from using "ORDER BY RAND()"
	 *
	 * Since displaying content in random orders can cause the regression tool to think something
	 * has changed, replace any "ORDER BY RAND()" clauses going through $wpdb to use predictable
	 * values (e.g. the ID).
	 *
	 * @param string   $orderby The ORDER BY clause of the query.
	 * @param WP_Query $query   The WP_Query instance (passed by reference).
	 */
	public function preventRandomQueryResults( $orderby, WP_Query $query ) {
		return false === stripos( $orderby, 'RAND()' ) ? $orderby : '';
	}

	/**
	 * Route all emails to a dummy email address.
	 *
	 * If we're unable to replace wp_mail() for some reason, this ensures that emails get routed to
	 * an email address that discards all messages.
	 *
	 * @param mixed[] $args A compacted array of wp_mail() arguments, including the "to" email,
	 *                      subject, message, headers, and attachments values.
	 *
	 * @return mixed[] The $args array with 'to' changed to a dummy email address.
	 */
	public function rerouteEmails( $args ) {
		$args['to'] = 'devnull@nexcess.net';

		return $args;
	}

	/**
	 * Seed PHP's random number generator.
	 *
	 * While we'd never want to do this in production environments, seeding the generators will
	 * produce more predictable results between runs on regression sites.
	 *
	 * @global $rnd_value
	 */
	public function seedRandomNumberGenerator() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand
		mt_srand( 1 );
	}

	/**
	 * Update the wp-config.php file to prevent collisions with real sites.
	 */
	public function updateWpConfig() {
		if ( defined( self::WP_CONFIG_UPDATED ) || $this->hasConfigConstant( self::WP_CONFIG_UPDATED ) ) {
			return;
		}

		// Explicitly override constants in the wp-config.php file.
		$this->setConfigConstant( 'WP_ENVIRONMENT_TYPE', 'staging' );
		$this->setConfigConstant( 'JETPACK_STAGING_MODE', true );
		$this->setConfigConstant( 'WP_CACHE_KEY_SALT', uniqid( 'regression-site-' ) );
		$this->setConfigConstant( 'WP_REDIS_DISABLED', true );

		/*
		 * Finally, flag the file as having been updated to prevent this method from running on
		 * subsequent requests.
		 */
		$this->setConfigConstant( self::WP_CONFIG_UPDATED, true );
	}
}
