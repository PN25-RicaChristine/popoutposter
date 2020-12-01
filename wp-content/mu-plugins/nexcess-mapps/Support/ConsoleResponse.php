<?php

namespace Nexcess\MAPPS\Support;

use Nexcess\MAPPS\Exceptions\ConsoleException;

class ConsoleResponse {

	/**
	 * @var string The resolved command that was invoked.
	 */
	protected $command;

	/**
	 * @var int The exit code from the command.
	 */
	protected $exit_code;

	/**
	 * @var string Any and all output from the command.
	 */
	protected $output;

	/**
	 * Construct a new ConsoleResponse object.
	 *
	 * @param string $command   The resolved command that was invoked.
	 * @param int    $exit_code The exit code from the command.
	 * @param string $output    Optional. Any and all output from the command. Default is empty.
	 */
	public function __construct( $command, $exit_code, $output = '' ) {
		$this->command   = (string) $command;
		$this->exit_code = (int) $exit_code;
		$this->output    = (string) $output;
	}

	/**
	 * Retrieve the exit code.
	 *
	 * @return int The exit code from the command.
	 */
	public function getExitCode() {
		return $this->exit_code;
	}

	/**
	 * Retrieve the command output.
	 *
	 * @return string Output from the command.
	 */
	public function getOutput() {
		return $this->output;
	}

	/**
	 * Determine whether or not the command was successful.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ConsoleException if $throw is true and the command exited
	 *                                                    with a non-zero exit code.
	 *
	 * @param bool $throw Optional. If true, a ConsoleException will be thrown if the command was
	 *                    unsuccessful. Default is false.
	 *
	 * @return bool True if the command was successful (a zero exit code) or false if something
	 *              went wrong.
	 */
	public function wasSuccessful( $throw = false ) {
		$successful = 0 === $this->exit_code;

		if ( $throw && ! $successful ) {
			throw new ConsoleException( sprintf(
				/* Translators: %1$d is the command's exit code. */
				__( 'Received a non-zero exit code: %1$d', 'nexcess-mapps' ),
				$this->exit_code
			), $this->exit_code );
		}

		return $successful;
	}
}
