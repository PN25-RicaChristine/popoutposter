<?php
/**
 * Collect feedback from customers.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasAdminPages;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Integrations\Dashboard;
use Nexcess\MAPPS\Support\AdminNotice;

class Feedback extends Integration {
	use HasAdminPages;
	use HasHooks;

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& $this->settings->canny_board_token;
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'admin_init', [ $this, 'registerFeedbackPage' ], 200 ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Register the feedback page.
	 */
	public function registerFeedbackPage() {

		/**
		 * Allow the feedback template section to be completely disabled.
		 *
		 * @param boolean $maybe_enabled Passing a "false" will disable this template call completely.
		 */
		$maybe_enabled = apply_filters( 'nexcess_mapps_branding_enable_feedback_template', true );

		if ( false === $maybe_enabled ) {
			return;
		}

		add_settings_section(
			'feedback',
			_x( 'Beta Feedback', 'settings section', 'nexcess-mapps' ),
			function () {
				$this->renderTemplate( 'feedback', [
					'settings' => $this->settings,
				] );
			},
			Dashboard::ADMIN_MENU_SLUG
		);
	}
}
