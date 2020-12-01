<?php

/**
 * Helper methods for Nexcess MAPPS.
 */

namespace Nexcess\MAPPS\Support;

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

	/**
	 * Determine whether or not a file is a broken symlink.
	 *
	 * @param string $filepath The filepath to inspect.
	 *
	 * @return bool True if the file is a symlink with a missing target, false otherwise.
	 */
	public static function isBrokenSymlink( $filepath ) {
		if ( ! is_link( $filepath ) ) {
			return false;
		}

		return ! file_exists( (string) readlink( $filepath ) );
	}

	/**
	 * Truncate a string, showing only the first $before and $after characters.
	 *
	 * @param string $string    The string to be truncated.
	 * @param int    $before    The number of characters from the beginning of the string to show.
	 * @param int    $after     The number of characters from the end of the string to show.
	 * @param string $separator Optional. The string to indicate truncation. Default is "…".
	 */
	public static function truncate( $string, $before, $after, $separator = '…' ) {
		$length = mb_strlen( $string );

		// We've asked for the entire string.
		if ( $before + $after >= $length ) {
			return $string;
		}

		$beginning = $before > 0 ? mb_substr( $string, 0, $before ) : '';
		$remaining = mb_substr( $string, mb_strlen( $beginning ) );
		$ending    = $after > 0 ? mb_substr( $remaining, -1 * $after ) : '';

		// Only truncate if the resulting string will be shorter than $length.
		return mb_strlen( $beginning ) + mb_strlen( $ending ) + mb_strlen( $separator ) < $length
			? $beginning . $separator . $ending
			: $string;
	}
}
