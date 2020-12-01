<?php

/**
 * Helper methods for Nexcess MAPPS.
 */

namespace Nexcess\MAPPS;

class Helpers {

	/**
	 * Print either "Enabled" or "Disabled" based on the value of $is_enabled.
	 *
	 * @param bool $is_enabled Whether or not a particular flag is enabled.
	 */
	public static function enabled( $is_enabled ) {
		echo esc_html( self::getEnabled( $is_enabled ) );
	}

	/**
	 * Return either "Enabled" or "Disabled" based on the value of $is_enabled.
	 *
	 * @param bool $is_enabled Whether or not a particular flag is enabled.
	 *
	 * @return string One of "Enabled" or "Disabled".
	 */
	public static function getEnabled( $is_enabled ) {
		return $is_enabled
			? _x( 'Enabled', 'setting state', 'nexcess-mapps' )
			: _x( 'Disabled', 'setting state', 'nexcess-mapps' );
	}

	/**
	 * Get the MAPPS portal URL for the given account ID.
	 *
	 * @param int $account_id The account ID.
	 *
	 * @return string The absolute URL to the Nexcess portal URL.
	 */
	public static function getPortalUrl( $account_id ) {
		return sprintf( 'https://portal.nexcess.net/cloud-account/%d', $account_id );
	}
}
