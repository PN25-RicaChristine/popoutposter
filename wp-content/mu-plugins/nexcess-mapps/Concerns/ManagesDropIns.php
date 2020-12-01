<?php

namespace Nexcess\MAPPS\Concerns;

use Nexcess\MAPPS\Exceptions\InvalidDropInException;
use Nexcess\MAPPS\Support\Helpers;

trait ManagesDropIns {

	/**
	 * Symlink a drop-in file from another source.
	 *
	 * @param string $drop_in The name of the drop-in file (e.g. "object-cache.php").
	 * @param string $source  The source file to be symlinked to WP_CONTENT_DIR/$dropIn.
	 *
	 * @return bool Whether or not the drop-in was installed successfully. Will return true if the
	 *              requested symlink already exists.
	 */
	public function symlinkDropIn( $drop_in, $source ) {
		try {
			$target = $this->validateDropIn( $drop_in );
		} catch ( InvalidDropInException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( esc_html( $e->getMessage() ), E_USER_WARNING );
			return false;
		}

		// Ensure the $source file exists.
		if ( ! file_exists( $source ) ) {
			return false;
		}

		// Verify the target isn't already present.
		if ( file_exists( $target ) ) {
			if ( is_link( $target ) ) {
				return readlink( $target ) === $source;
			}

			return false;
		}

		// If it's a broken symlink, clean it up.
		if ( Helpers::isBrokenSymlink( $target ) ) {
			unlink( $target );
		}

		return symlink( $source, $target );
	}

	/**
	 * Remove the given drop-in file.
	 *
	 * @param string $drop_in The drop-in file.
	 * @param string $source  Optional. If provided, the symlink will only be removed if it
	 *                        points to this absolute path. Default is empty.
	 *
	 * @return bool Whether or not the drop-in was removed successfully. Will return true if the
	 *              specified drop-in didn't exist to begin with.
	 */
	public function removeDropIn( $drop_in, $source = '' ) {
		try {
			$target = $this->validateDropIn( $drop_in );
		} catch ( InvalidDropInException $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( esc_html( $e->getMessage() ), E_USER_WARNING );
			return false;
		}

		// The target doesn't exist, so there's nothing to do.
		if ( ! file_exists( $target ) && ! Helpers::isBrokenSymlink( $target ) ) {
			return true;
		}

		// Don't remove normal files.
		if ( file_exists( $target ) && ! is_link( $target ) ) {
			return false;
		}

		// If a $source is provided, validate the linked file.
		if ( ! empty( $source ) && readlink( $target ) !== $source ) {
			return false;
		}

		return unlink( $target );
	}

	/**
	 * Validate known drop-in files.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\InvalidDropInException if the given $drop_in is unrecognized.
	 *
	 * @param string $drop_in The drop-in being referenced. Can accept the file with or without the
	 *                        ".php" file extension.
	 *
	 * @return string The full system path to the given drop-in file.
	 */
	private function validateDropIn( $drop_in ) {
		// Ensure we have the ".php" suffix.
		$drop_in       = basename( $drop_in, '.php' ) . '.php';
		$valid_dropins = _get_dropins();

		if ( ! isset( $valid_dropins[ $drop_in ] ) ) {
			throw new InvalidDropInException( sprintf(
				'%1$s is not a valid WordPress drop-in.',
				$drop_in
			) );
		}

		return sprintf( '%s/%s', WP_CONTENT_DIR, $drop_in );
	}
}
