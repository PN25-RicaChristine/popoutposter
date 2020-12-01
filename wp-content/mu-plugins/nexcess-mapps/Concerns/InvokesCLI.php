<?php

namespace Nexcess\MAPPS\Concerns;

use Nexcess\MAPPS\Support\ConsoleResponse;

trait InvokesCLI {

	/**
	 * @var string The full system path to the WP-CLI binary.
	 */
	private $wpBinary;

	/**
	 * Invoke a standard console command.
	 *
	 * @param string  $command   The WP-CLI command name.
	 * @param mixed[] $arguments Optional. An array of arguments. Numeric keys will be treated as
	 *                           [positional] arguments, while strings will be treated as options.
	 *                           Default is empty.
	 *
	 * @return \Nexcess\MAPPS\Support\ConsoleResponse A ConsoleResponse object representing the result.
	 */
	protected function cli( $command, $arguments = [] ) {
		array_walk( $arguments, function ( &$value, $key ) {
			if ( is_int( $key ) ) {
				$value = escapeshellarg( $value );
				return;
			}

			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			} elseif ( is_bool( $value ) ) {
				$value = escapeshellarg( $key );
				return;
			}

			$value = escapeshellarg( sprintf( '%s=%s', $key, $value ) );
		} );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		exec( escapeshellcmd( $command . ' ' . implode( ' ', $arguments ) ), $output, $code );

		return new ConsoleResponse( $command, $code, implode( PHP_EOL, $output ) );
	}

	/**
	 * Invoke a WP-CLI command.
	 *
	 * Example:
	 *
	 *     $this->wpCli('plugin install', [
	 *         'jetpack',
	 *         '--activate' => true,
	 *         '--version'  => '7.5',
	 *     ]);
	 *
	 * @param string  $command   The WP-CLI command name.
	 * @param mixed[] $arguments Optional. An array of arguments. Numeric keys will be treated as
	 *                           [positional] arguments, while strings will be treated as options.
	 *                           Default is empty.
	 *
	 * @return \Nexcess\MAPPS\Support\ConsoleResponse A ConsoleResponse object representing the result.
	 */
	protected function wpCli( $command, $arguments = [] ) {
		// Strip off any leading "wp " portions.
		if ( 0 === strpos( $command, 'wp ' ) ) {
			$command = substr( $command, 3 );
		}

		return $this->cli( trim( $this->getWpBinary() . ' ' . $command ), $arguments );
	}

	/**
	 * Retrieve the PHP + WP-CLI binary combination we want to use while running WP-CLI.
	 *
	 * @return string The system path to a PHP binary.
	 */
	public function getWpBinary() {
		if ( ! $this->wpBinary ) {
			/*
			 * Construct an escaped string that expands the current PHP and WP-CLI binary paths.
			 *
			 * Note that we're using the PHP_BINDIR constant and adding "/php" instead of PHP_BINARY,
			 * as the latter will point to PHP-FPM.
			 *
			 * The expected output of this will look something like:
			 *
			 *     /opt/remi/php73/root/usr/bin/php /usr/local/bin/wp
			 */
			$this->wpBinary = sprintf(
				'%1$s %2$s',
				escapeshellarg( PHP_BINDIR . '/php' ),
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
				escapeshellarg( trim( (string) shell_exec( 'command -v wp' ) ) )
			);
		}

		return $this->wpBinary;
	}
}
