<?php

namespace Nexcess\MAPPS\Support;

use WP_Error;
use WP_Fatal_Error_Handler;

class FatalErrorHandler extends WP_Fatal_Error_Handler {

	/**
	 * Runs the shutdown handler.
	 *
	 * This method is registered via `register_shutdown_function()`.
	 */
	public function handle() {
		if ( defined( 'WP_SANDBOX_SCRAPING' ) && WP_SANDBOX_SCRAPING ) {
			return;
		}

		// Do not trigger the fatal error handler while updates are being installed.
		if ( function_exists( 'wp_is_maintenance_mode' ) && wp_is_maintenance_mode() ) {
			return;
		}

		try {
			// Bail if no error found.
			$error = $this->detect_error();
			if ( ! $error ) {
				return;
			}

			if ( ! isset( $GLOBALS['wp_locale'] ) && function_exists( 'load_default_textdomain' ) ) {
				load_default_textdomain();
			}

			$handled = false;

			if ( ! is_multisite() && wp_recovery_mode()->is_initialized() ) {
				$handled = wp_recovery_mode()->handle_error( $error );
			}

			// If WordPress' default recovery didn't do the trick, try our custom handler.
			if ( ! $handled || is_wp_error( $handled ) ) {
				$handled = $this->mappsHandler( $error, $handled );
			}

			// Display the PHP error template if headers not sent.
			if ( is_admin() || ! headers_sent() ) {
				$this->display_error_template( $error, $handled );
			}
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Catch exceptions and remain silent.
		}
	}

	/**
	 * MAPPS-specific error handling.
	 *
	 * If WordPress can't solve the issue on its own, we fall back to this to see if we can fix it.
	 *
	 * @param mixed[]        $error   Error details from {@see error_get_last()}
	 * @param WP_Error|bool $handled Either false or a WP_Error object explaining why WordPress
	 *                                was unable to resolve the error.
	 *
	 * @return true|WP_Error True if the error was handled, or a WP_Error object explaining why it wasn't.
	 */
	protected function mappsHandler( array $error, $handled ) {
		$include_regex = '/(?:require|include)(?:_once)?\(\)\:.+(' . preg_quote( ABSPATH, '/' ) . '\S+)[\'"*]/';

		if ( E_COMPILE_ERROR === $error['type'] && preg_match( $include_regex, $error['message'], $matches ) ) {
			return $this->handleFileIncludeError( $error, $matches[1] );
		}

		return is_wp_error( $handled )
			? $handled
			: new WP_Error( 'unhandled_error', 'Unhandled error', $error );
	}

	/**
	 * Handle an error that results from trying to include/require a file that doesn't exist.
	 *
	 * @param mixed[] $error The error array from error_get_last().
	 * @param string  $file  The file that was attempting to be included.
	 *
	 * @return true|WP_Error True if the error was resolved, a WP_Error object otherwise.
	 */
	protected function handleFileIncludeError( array $error, $file ) {
		$drop_ins = [
			'advanced-cache.php',
			'maintenance.php',
			'object-cache.php',
		];

		// If the source of the file is a common drop-in, we can safely move it aside.
		if ( in_array( basename( $error['file'] ), $drop_ins, true ) ) {
			try {
				if ( ! rename( $error['file'], $error['file'] . '.broken' ) ) {
					throw new \RuntimeException( sprintf(
						'Unable to move %1$s to %2$s',
						$error['file'],
						$error['file'] . '.broken'
					) );
				}

				if ( $this->isDebugModeEnabled() ) {
					add_filter( 'wp_php_error_message', function () use ( $error, $file ) {
						$message = sprintf(
							// Intentionally not translated so as to not load more of WordPress.
							'The <code>%1$s</code> drop-in was attempting to include <code>%2$s</code>, which does not exist. The drop-in has been moved to <code>%1$s.backup</code> for your inspection, and refreshing this page should see your site functioning normally.',
							basename( $error['file'] ),
							$file
						);

						return sprintf( '<p>%1$s</p>', $message );
					} );
				}

				return true;
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Use the default return value.
			}
		}

		return new \WP_Error( 'missing_file_include', sprintf(
			'%1$s includes %2$s, which does not exist.',
			$error['file'],
			$file
		), $error );
	}

	/**
	 * Determine whether or not the site is in debug mode.
	 *
	 * @return bool True if WP_DEBUG is enabled, false otherwise.
	 */
	protected function isDebugModeEnabled() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}
}
