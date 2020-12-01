<?php
/**
 * Short-circuit the Freemius class to prevent it from taking over WP-Admin.
 */

namespace Nexcess\MAPPS\Stubs;

class Freemius {

	/**
	 * @param string  $name      The method name.
	 * @param mixed[] $arguments Arguments passed to the method.
	 */
	public function __call( $name, $arguments ) {
		// no-op.
	}

	/**
	 * @param string  $name      The method name.
	 * @param mixed[] $arguments Arguments passed to the method.
	 */
	public static function __callStatic( $name, $arguments ) {
		// no-op.
	}

	public function is_free_plan() {
		return true;
	}
}
