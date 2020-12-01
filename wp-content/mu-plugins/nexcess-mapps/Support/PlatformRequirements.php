<?php

namespace Nexcess\MAPPS\Support;

use Nexcess\MAPPS\Concerns\HasWordPressDependencies;
use Nexcess\MAPPS\Support\AdminNotice;

class PlatformRequirements {
	use HasWordPressDependencies;

	/**
	 * The minimum supported WordPress version.
	 *
	 * We officially and actively support the latest and previous major release of WordPress, but
	 * this is the absolute minimum version; anything lower will prevent the plugin from loading.
	 */
	const MINIMUM_WP_VERSION = '5.0';

	/**
	 * Verify that the site meets the minimum requirements for the full MAPPS experience.
	 *
	 * @return bool Returns true if all dependencies are met, false otherwise.
	 */
	public function checkDependencies() {
		// Abort if the site is running on an unsupported version of WordPress.
		if ( ! $this->siteIsAtLeastWordPressVersion( self::MINIMUM_WP_VERSION ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Display an admin notice if the site is running an unsupported version of WordPress.
	 */
	public function renderOutdatedWordPressVersionNotice() {
		if ( ! is_admin() ) {
			return;
		}

		$message  = sprintf(
			'<strong>%1$s</strong>',
			__( 'Your site is currently running an outdated version of WordPress!', 'nexcess-mapps' )
		);
		$message .= PHP_EOL . PHP_EOL;
		$message .= sprintf(
			/* Translators: %1$s is the link to update-core.php, %2$s is one of "WordPress" or "WooCommerce". */
			__( 'We recommend <a href="%1$s">upgrading WordPress</a> as soon as possible to get the most out of the Nexcess Managed Applications platform.', 'nexcess-mapps' ),
			admin_url( 'update-core.php' )
		);

		// Assemble an AdminNotice and queue it up.
		$notice = new AdminNotice( $message, 'warning', false, 'unsupported-wp-version' );
		$notice->setCapability( 'update_core' );

		add_action( 'admin_notices', [ $notice, 'output' ] );
	}
}
