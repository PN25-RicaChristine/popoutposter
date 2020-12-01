<?php

namespace Nexcess\MAPPS\Concerns;

use Nexcess\MAPPS\Exceptions\ConfigException;
use Nexcess\Vendor\WPConfigTransformer;

trait ManagesWpConfig {

	/**
	 * @var \Nexcess\Vendor\WPConfigTransformer
	 */
	private $wpConfigTransformer;

	/**
	 * Add (or update) the given constant in wp-config.php.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ConfigException if the configuration cannot be written.
	 *
	 * @param string $constant The constant name.
	 * @param mixed  $value    The constant value.
	 */
	public function setConfigConstant( $constant, $value ) {
		$options = [
			'add'       => true,
			'raw'       => false,
			'normalize' => true,
		];

		if ( is_bool( $value ) ) {
			$options['raw'] = true;
			$value          = $value ? 'true' : 'false';
		}

		try {
			$this->getWpConfigTransformer()->update( 'constant', $constant, (string) $value, $options );
		} catch ( \Exception $e ) {
			throw new ConfigException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Remove the given constant from wp-config.php.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ConfigException if the configuration cannot be written.
	 *
	 * @param string $constant The constant name.
	 */
	public function removeConfigConstant( $constant ) {
		try {
			$this->getWpConfigTransformer()->remove( 'constant', $constant );
		} catch ( \Exception $e ) {
			throw new ConfigException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Determine whether or not the given constant exists in wp-config.php.
	 *
	 * @param string $constant The constant name.
	 *
	 * @return bool True if the constant is defined, false otherwise.
	 */
	public function hasConfigConstant( $constant ) {
		try {
			$exists = $this->getWpConfigTransformer()->exists( 'constant', $constant );
		} catch ( \Exception $e ) {
			$exists = false;
		}

		return $exists;
	}

	/**
	 * Add (or update) the given variable in wp-config.php.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ConfigException if the configuration cannot be written.
	 *
	 * @param string $variable The variable name.
	 * @param mixed  $value    The variable value.
	 */
	public function setConfigVariable( $variable, $value ) {
		$options = [
			'add'       => true,
			'raw'       => false,
			'normalize' => true,
		];

		if ( is_bool( $value ) ) {
			$options['raw'] = true;
			$value          = $value ? 'true' : 'false';
		} elseif ( is_array( $value ) ) {
			$options['raw'] = true;
			$value          = var_export( $value, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		}

		try {
			$this->getWpConfigTransformer()->update( 'variable', $variable, (string) $value, $options );
		} catch ( \Exception $e ) {
			throw new ConfigException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Remove the given variable from wp-config.php.
	 *
	 * @throws \Nexcess\MAPPS\Exceptions\ConfigException if the configuration cannot be written.
	 *
	 * @param string $variable The variable name.
	 */
	public function removeConfigVariable( $variable ) {
		try {
			$this->getWpConfigTransformer()->remove( 'variable', $variable );
		} catch ( \Exception $e ) {
			throw new ConfigException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Determine whether or not the given variable exists in wp-config.php.
	 *
	 * @param string $variable The variable name.
	 *
	 * @return bool True if the variable is defined, false otherwise.
	 */
	public function hasConfigVariable( $variable ) {
		try {
			$exists = $this->getWpConfigTransformer()->exists( 'variable', $variable );
		} catch ( \Exception $e ) {
			$exists = false;
		}

		return $exists;
	}

	/**
	 * Get the WPConfigTransformer instance.
	 *
	 * @return \Nexcess\Vendor\WPConfigTransformer
	 */
	private function getWpConfigTransformer() {
		if ( empty( $this->wpConfigTransformer ) ) {
			$this->wpConfigTransformer = new WPConfigTransformer( ABSPATH . 'wp-config.php' );
		}

		return $this->wpConfigTransformer;
	}
}
