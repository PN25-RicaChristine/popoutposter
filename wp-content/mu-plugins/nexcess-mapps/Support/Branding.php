<?php
/**
 * Allow for agencies to override the Nexcess branding.
 */

namespace Nexcess\MAPPS\Support;

use Nexcess\MAPPS\Plugin;

use const Nexcess\MAPPS\PLUGIN_URL;

class Branding {

	/**
	 * Override the company name used throughout the Nexcess MAPPS platform's branding.
	 *
	 * @return string The company name, with Nexcess as the default.
	 */
	public static function getCompanyName() {

		/**
		 * Override the company name for the Nexcess MAPPS dashboard.
		 *
		 * @param string $company_name The company name used by the agency.
		 */
		$company_name = apply_filters( 'nexcess_mapps_branding_company_name', '' );

		// Make sure it's a valid working string.
		if ( ! is_string( $company_name ) || empty( $company_name ) ) {
			$company_name = _x( 'Nexcess', 'company name', 'nexcess-mapps' );
		}

		// Return it without whitespace.
		return trim( $company_name );
	}

	/**
	 * Retrieve the <svg> markup for the a single-color logo.
	 *
	 * @param string $color Optional. The default fill color for the SVG icon.
	 *                      Default is "currentColor".
	 *
	 * @return string An inline SVG icon.
	 */
	public static function getCompanyIcon( $color = 'currentColor' ) {

		/**
		 * Override the company SVG icon for the Nexcess MAPPS dashboard.
		 *
		 * @param string $company_svg The company SVG icon used by the agency.
		 */
		$company_svg = apply_filters( 'nexcess_mapps_branding_company_icon_svg', '' );

		// Make sure it's a valid working string.
		if ( ! is_string( $company_svg ) || empty( $company_svg ) ) {
			$company_svg = Plugin::getNexcessIcon( $color );
		}

		// Return the SVG markup we created.
		return $company_svg;
	}

	/**
	 * Allow for a company logo to be used in leui of the Nexcess one.
	 *
	 * @return string The URL of the logo.
	 */
	public static function getCompanyImage() {

		/**
		 * Override the company logo image file for the Nexcess MAPPS dashboard.
		 *
		 * @param string $company_image The company image used by the agency.
		 */
		$company_image = apply_filters( 'nexcess_mapps_branding_company_image', '' );

		// Make sure it's a valid working string.
		if ( ! is_string( $company_image ) || empty( $company_image ) ) {
			$company_image = PLUGIN_URL . '/nexcess-mapps/assets/img/nexcess-logo.svg';
		}

		// Return the URL we have.
		return $company_image;
	}

}
