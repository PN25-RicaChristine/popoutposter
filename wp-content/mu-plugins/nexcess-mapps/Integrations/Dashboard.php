<?php
/**
 * The Nexcess MAPPS dashboard.
 */

namespace Nexcess\MAPPS\Integrations;

use Nexcess\MAPPS\Concerns\HasAdminPages;
use Nexcess\MAPPS\Concerns\HasHooks;
use Nexcess\MAPPS\Plugin;
use Nexcess\MAPPS\Support\Branding;

use const Nexcess\MAPPS\PLUGIN_URL;
use const Nexcess\MAPPS\PLUGIN_VERSION;

class Dashboard extends Integration {
	use HasAdminPages;
	use HasHooks;

	/**
	 * The top-level Nexcess menu page slug.
	 */
	const ADMIN_MENU_SLUG = 'nexcess-mapps';

	/**
	 * Determine whether or not this integration should be loaded.
	 *
	 * @return bool Whether or not this integration should be loaded in this environment.
	 */
	public function shouldLoadIntegration() {
		return $this->settings->is_mapps_site
			&& apply_filters( 'nexcess_mapps_show_dashboard', true );
	}

	/**
	 * Retrieve all actions for the integration.
	 *
	 * @return array[]
	 */
	protected function getActions() {
		// phpcs:disable WordPress.Arrays
		return [
			[ 'admin_menu', [ $this, 'registerMenuPage'         ], 1 ],
			[ 'admin_init', [ $this, 'registerDashboardSection' ], 1 ],
		];
		// phpcs:enable WordPress.Arrays
	}

	/**
	 * Register the "Dashboard" settings section.
	 */
	public function registerDashboardSection() {
		add_settings_section(
			'dashboard',
			_x( 'Dashboard', 'settings section', 'nexcess-mapps' ),
			function () {
				$this->renderTemplate( 'dashboard', [
					'settings' => $this->settings,
				] );
			},
			self::ADMIN_MENU_SLUG
		);
	}

	/**
	 * Register the top-level "Nexcess" menu item.
	 */
	public function registerMenuPage() {
		/*
		 * WordPress uses the svg-painter.js file to re-color SVG files, but this can cause a brief
		 * flash of oddly-colored logos. By setting it to the background color of the admin bar,
		 * the icon remains hidden until it's colored.
		 */
		$icon = Branding::getCompanyIcon( '#23282d' );

		$company_name = Branding::getCompanyName();
		/**
		 * Override the page title for the Nexcess MAPPS dashboard.
		 *
		 * @param string $company_name The company name used as the page title.
		 */
		$page_title = apply_filters( 'nexcess_mapps_branding_dashboard_page_title', $company_name );
		/**
		 * Override the page title for the Nexcess MAPPS dashboard.
		 *
		 * @param string $company_name The company name used as the menu title.
		 */
		$menu_title = apply_filters( 'nexcess_mapps_branding_dashboard_menu_item_title', $company_name );

		// Define the top-level navigation item.
		add_menu_page(
			$page_title,
			$menu_title,
			'manage_options',
			self::ADMIN_MENU_SLUG,
			[ $this, 'renderMenuPage' ],
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'data:image/svg+xml;base64,' . base64_encode( $icon ),
			3
		);

		// Define a submenu with the same page, letting us override the first sub-menu.
		add_submenu_page(
			self::ADMIN_MENU_SLUG,
			$page_title,
			_x( 'Dashboard', 'menu item title', 'nexcess-mapps' ),
			'manage_options',
			self::ADMIN_MENU_SLUG,
			[ $this, 'renderMenuPage' ]
		);
	}

	/**
	 * Render the top-level "Nexcess" admin page.
	 */
	public function renderMenuPage() {

		/**
		 * Allow the admin menu template section to be completely disabled.
		 *
		 * @param boolean $maybe_enabled Passing a "false" will disable this template call completely.
		 */
		$maybe_enabled = apply_filters( 'nexcess_mapps_branding_enable_admin_template', true );

		if ( false === $maybe_enabled ) {
			return;
		}

		wp_enqueue_script( 'nexcess-mapps-admin' );

		$this->renderTemplate( 'admin', [
			'settings' => $this->settings,
		] );
	}
}
