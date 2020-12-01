<?php
/**
 * Integrations within the WP Admin area.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Plugin;
use Nexcess\MAPPS\Support\AdminNotice;
use Nexcess\MAPPS\Support\Branding;
use Nexcess\MAPPS\Support\PHPVersions;

use const Nexcess\MAPPS\PLUGIN_URL;
use const Nexcess\MAPPS\PLUGIN_VERSION;

class Admin extends Integration {
	use HasHooks;

	/**
	 * Action hook for dismissing a notification.
	 */
	const HOOK_DISMISSED_NOTICE = 'mapps_dismissed_notice';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration should be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site;
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'admin_notices',         [ $this, 'checkPHPVersion'    ], 1  ],
			[ 'admin_notices',         [ $this, 'renderAdminNotices' ], 10 ],
			[ 'admin_enqueue_scripts', [ $this, 'enqueueScripts'     ], 1  ],
			[ 'admin_footer_text',     [ $this, 'adminFooterText'    ]     ],

			// Ajax callbacks.
			[ 'wp_ajax_' . self::HOOK_DISMISSED_NOTICE, [ $this, 'dismissNotice' ] ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Register and/or enqueue custom scripts and styles.
	 */
	public function enqueueScripts() {
		wp_register_script(
			'nexcess-mapps-admin',
			PLUGIN_URL . '/nexcess-mapps/assets/admin.js',
			[ 'jquery' ],
			PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			'nexcess-mapps-admin',
			PLUGIN_URL . '/nexcess-mapps/assets/admin.css',
			[],
			PLUGIN_VERSION
		);

		// Now we are going to add the inline image CSS.
		$image  = Branding::getCompanyImage();
		$inline = '
				.mapps-wrap .nexcess-page-title {
					background-image: url("' . esc_url( $image ) . '");
				}';
		wp_add_inline_style( 'nexcess-mapps-admin', $inline );
	}

	/**
	 * Check to see if the current PHP version is supported.
	 *
	 * If the site is running a version of PHP that has reached end-of-life (EOL), a notice should
	 * be displayed to administrators.
	 *
	 * @link https://www.php.net/supported-versions.php
	 * @link https://www.php.net/eol.php
	 */
	public function checkPHPVersion() {
		if ( ! current_user_can( 'manage_options' ) || ! PHPVersions::hasReachedEOL( $this->settings->php_version ) ) {
			return;
		}

		$notice = sprintf(
			/* Translators: %1$s is the site's current PHP version, %2$s is its EOL date, %3$s is the kb URL. */
			__(
				'<p><strong>Your site is currently running on an out-of-date version of PHP!</strong></p>
				<p>PHP is the underlying programming language that WordPress and its themes/plugins are written in. Newer releases bring more features, better performance, and regular security fixes.</p>
				<p>Your site is currently running on PHP <strong>%1$s</strong>, which stopped receiving security updates on %2$s!</p>
				<p>For improved performance and security, we recommend <a href="%3$s">upgrading your site\'s PHP version</a> at your earliest convenience.</p>
				',
				'nexcess-mapps'
			),
			$this->settings->php_version,
			PHPVersions::getEOLDate( $this->settings->php_version )->format( get_option( 'date_format', 'F j, Y' ) ),
			'https://help.nexcess.net/74095-wordpress/upgrading-your-php-installation-in-managed-wordpress-and-managed-woocommerce-hosting'
		);

		$this->adminBar->addNotice( new AdminNotice(
			$notice,
			'warning',
			false
		), 'php-version' );
	}

	/**
	 * Render any admin notices we have queued up.
	 */
	public function renderAdminNotices() {
		$notices = $this->adminBar->getNotices();

		if ( empty( $notices ) ) {
			return;
		}

		foreach ( $notices as $notice ) {
			if ( ! $notice->userHasDismissedNotice() ) {
				$notice->output();
			}
		}

		// Enqueue the admin scripting, if it isn't already.
		wp_enqueue_script( 'nexcess-mapps-admin' );
	}

	/**
	 * Ajax callback for dismissed admin notices.
	 */
	public function dismissNotice() {
		if ( empty( $_POST['notice'] ) || empty( $_POST['_wpnonce'] ) ) {
			return wp_send_json_error( 'Required fields missing.', 422 );
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], self::HOOK_DISMISSED_NOTICE ) ) {
			return wp_send_json_error( 'Nonce validation failed.', 403 );
		}

		$dismissed = get_user_meta( get_current_user_id(), AdminNotice::USER_META_DISMISSED_NOTICES, true ) ?: [];

		// Add the new notice.
		$dismissed[ sanitize_text_field( $_POST['notice'] ) ] = time();

		// Update our stored value.
		update_user_meta( get_current_user_id(), AdminNotice::USER_META_DISMISSED_NOTICES, $dismissed );

		return wp_send_json_success();
	}

	/**
	 * Replace the default "Thank you for creating with WordPress" link in the WP-Admin footer.
	 *
	 * @param string $text The content that will be printed.
	 *
	 * @return string The filtered $text.
	 */
	public function adminFooterText( $text ) {
		return sprintf(
			/* translators: %1$s is https://wordpress.org/ */
			__( 'Thank you for creating with <a href="%1$s">WordPress</a> and <a href="https://nexcess.net">Nexcess</a>.', 'nexcess-mapps' ),
			// phpcs:ignore WordPress.WP.I18n.MissingArgDomain
			__( 'https://wordpress.org/' )
		);
	}
}
